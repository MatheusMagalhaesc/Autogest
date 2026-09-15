<?php
/**
 * Funções de autenticação, sessão, controle de acesso por perfil
 * e recuperação de senha (Sprint 1 — Acesso ao Sistema).
 */

require_once __DIR__ . '/../config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function autenticarUsuario(string $usuario, string $senha)
{
    $pdo = Database::getConnection();
    $stmt = $pdo->prepare('SELECT * FROM usuarios WHERE usuario = ? AND ativo = 1 LIMIT 1');
    $stmt->execute([$usuario]);
    $u = $stmt->fetch();

    if ($u && password_verify($senha, $u['senha'])) {
        return $u;
    }
    return false;
}

function iniciarSessaoUsuario(array $usuario, bool $lembrarMe = false): void
{
    $_SESSION['user_id']      = $usuario['id'];
    $_SESSION['user_nome']    = $usuario['nome'];
    $_SESSION['user_usuario'] = $usuario['usuario'];
    $_SESSION['user_perfil']  = $usuario['perfil'];
    $_SESSION['login_em']     = time();

    if ($lembrarMe) {
        // Estende a duração do cookie de sessão para 30 dias
        $params = session_get_cookie_params();
        setcookie(session_name(), session_id(), time() + 60 * 60 * 24 * 30, $params['path']);
    }
}

function usuarioLogado(): bool
{
    return isset($_SESSION['user_id']);
}

function protegerPagina(): void
{
    if (!usuarioLogado()) {
        header('Location: login.php');
        exit;
    }
}

function protegerPorPerfil(array $perfisPermitidos): void
{
    protegerPagina();
    if (!in_array($_SESSION['user_perfil'], $perfisPermitidos, true)) {
        http_response_code(403);
        die('Acesso negado: seu perfil (' . htmlspecialchars(nomePerfil($_SESSION['user_perfil'])) . ') não tem permissão para acessar esta página.');
    }
}

function encerrarSessao(): void
{
    $_SESSION = [];
    session_destroy();
}

function nomePerfil(string $perfil): string
{
    $nomes = [
        'administrador'  => 'Administrador',
        'recepcionista'  => 'Recepcionista',
        'mecanico'       => 'Mecânico',
    ];
    return $nomes[$perfil] ?? $perfil;
}

// ============================ Gestão de contas ============================

function usuarioJaCadastrado(string $usuario): bool
{
    $pdo = Database::getConnection();
    $stmt = $pdo->prepare('SELECT id FROM usuarios WHERE usuario = ? LIMIT 1');
    $stmt->execute([$usuario]);
    return (bool) $stmt->fetch();
}

function criarUsuario(string $nome, string $usuario, string $email, string $senha, string $perfil, string $especialidade = '')
{
    $perfisValidos = ['administrador', 'recepcionista', 'mecanico'];

    if ($nome === '' || $usuario === '' || $senha === '') {
        return 'Preencha nome, usuário e senha.';
    }
    if (!preg_match('/^[a-zA-Z0-9._-]{3,30}$/', $usuario)) {
        return 'O nome de usuário deve ter entre 3 e 30 caracteres (letras, números, ponto, traço).';
    }
    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return 'Informe um e-mail válido (ou deixe em branco).';
    }
    if (strlen($senha) < 6) {
        return 'A senha deve ter pelo menos 6 caracteres.';
    }
    if (!in_array($perfil, $perfisValidos, true)) {
        return 'Perfil de acesso inválido.';
    }
    if (usuarioJaCadastrado($usuario)) {
        return 'Já existe um usuário com esse nome de usuário.';
    }

    $pdo = Database::getConnection();
    $stmt = $pdo->prepare(
        'INSERT INTO usuarios (nome, usuario, email, senha, perfil, especialidade, ativo) VALUES (?, ?, ?, ?, ?, ?, 1)'
    );
    $stmt->execute([$nome, strtolower($usuario), $email ?: null, password_hash($senha, PASSWORD_DEFAULT), $perfil, $especialidade ?: null]);
    return true;
}

function listarUsuarios(): array
{
    $pdo = Database::getConnection();
    return $pdo->query('SELECT id, nome, usuario, email, perfil, especialidade, ativo, criado_em FROM usuarios ORDER BY id DESC')->fetchAll();
}

function listarMecanicos(): array
{
    $pdo = Database::getConnection();
    return $pdo->query("SELECT id, nome, especialidade FROM usuarios WHERE perfil = 'mecanico' AND ativo = 1 ORDER BY nome")->fetchAll();
}

function alternarStatusUsuario(int $id): bool
{
    if ($id === (int) ($_SESSION['user_id'] ?? 0)) {
        return false;
    }
    $pdo = Database::getConnection();
    $stmt = $pdo->prepare('UPDATE usuarios SET ativo = NOT ativo WHERE id = ?');
    return $stmt->execute([$id]);
}

function alterarSenhaUsuario(int $id, string $senhaAtual, string $novaSenha, string $confirmarSenha)
{
    if ($senhaAtual === '' || $novaSenha === '' || $confirmarSenha === '') {
        return 'Preencha todos os campos.';
    }
    if (strlen($novaSenha) < 6) {
        return 'A nova senha deve ter pelo menos 6 caracteres.';
    }
    if ($novaSenha !== $confirmarSenha) {
        return 'A confirmação não corresponde à nova senha.';
    }

    $pdo = Database::getConnection();
    $stmt = $pdo->prepare('SELECT senha FROM usuarios WHERE id = ?');
    $stmt->execute([$id]);
    $u = $stmt->fetch();

    if (!$u || !password_verify($senhaAtual, $u['senha'])) {
        return 'Senha atual incorreta.';
    }

    $stmt = $pdo->prepare('UPDATE usuarios SET senha = ? WHERE id = ?');
    $stmt->execute([password_hash($novaSenha, PASSWORD_DEFAULT), $id]);
    return true;
}

// ============================ Recuperação de senha (HU3 - Sprint 1) ============================

/**
 * Gera um token de recuperação de senha para o usuário informado (por nome de usuário ou e-mail).
 * Retorna o token gerado (para exibir/enviar) ou false se o usuário não for encontrado.
 */
function gerarTokenRecuperacao(string $usuarioOuEmail)
{
    $pdo = Database::getConnection();
    $stmt = $pdo->prepare('SELECT * FROM usuarios WHERE (usuario = ? OR email = ?) AND ativo = 1 LIMIT 1');
    $stmt->execute([$usuarioOuEmail, $usuarioOuEmail]);
    $u = $stmt->fetch();

    if (!$u) {
        return false;
    }

    $token = bin2hex(random_bytes(24));
    $expira = date('Y-m-d H:i:s', time() + 3600); // 1 hora de validade

    $stmt = $pdo->prepare('INSERT INTO senha_resets (usuario_id, token, expira_em) VALUES (?, ?, ?)');
    $stmt->execute([$u['id'], $token, $expira]);

    return ['token' => $token, 'nome' => $u['nome']];
}

/**
 * Valida um token de recuperação de senha (ainda não usado e não expirado).
 */
function validarTokenRecuperacao(string $token)
{
    $pdo = Database::getConnection();
    $stmt = $pdo->prepare(
        "SELECT sr.*, u.nome FROM senha_resets sr
         JOIN usuarios u ON u.id = sr.usuario_id
         WHERE sr.token = ? AND sr.usado = 0 AND sr.expira_em >= ?"
    );
    $stmt->execute([$token, date('Y-m-d H:i:s')]);
    return $stmt->fetch();
}

/**
 * Redefine a senha usando um token válido.
 */
function redefinirSenhaComToken(string $token, string $novaSenha, string $confirmarSenha)
{
    if (strlen($novaSenha) < 6) {
        return 'A nova senha deve ter pelo menos 6 caracteres.';
    }
    if ($novaSenha !== $confirmarSenha) {
        return 'A confirmação não corresponde à nova senha.';
    }

    $reset = validarTokenRecuperacao($token);
    if (!$reset) {
        return 'Link inválido ou expirado. Solicite a recuperação novamente.';
    }

    $pdo = Database::getConnection();
    $pdo->prepare('UPDATE usuarios SET senha = ? WHERE id = ?')
        ->execute([password_hash($novaSenha, PASSWORD_DEFAULT), $reset['usuario_id']]);
    $pdo->prepare('UPDATE senha_resets SET usado = 1 WHERE id = ?')
        ->execute([$reset['id']]);

    return true;
}
