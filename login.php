<?php
require_once __DIR__ . '/includes/auth.php';

if (usuarioLogado()) {
    header('Location: dashboard.php');
    exit;
}

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = trim($_POST['usuario'] ?? '');
    $senha = $_POST['senha'] ?? '';
    $lembrar = isset($_POST['lembrar']);

    if ($usuario === '' || $senha === '') {
        $erro = 'Preencha usuário/e-mail e senha para continuar.';
    } else {
        $u = autenticarUsuario($usuario, $senha);
        if ($u) {
            iniciarSessaoUsuario($u, $lembrar);
            header('Location: dashboard.php');
            exit;
        } else {
            $erro = 'Usuário ou senha inválidos. Tente novamente.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — AutoGest</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="assets/js/lucide.min.js"></script>
</head>
<body class="login-page">

    <div class="login-card">
        <div class="login-card__brand">
            <img src="assets/img/logo-full.png" alt="AutoGest — Sistema de Gestão Mecânica">
            <p>Mais organização, mais tempo para o que realmente importa.</p>
        </div>

        <div class="login-card__form">
            <h2>Bem-vindo de volta!</h2>
            <p class="subtitle">Acesse sua conta para continuar</p>

            <?php if ($erro): ?>
                <div class="alert alert-erro"><i data-lucide="alert-circle" class="icon"></i> <?= htmlspecialchars($erro) ?></div>
            <?php endif; ?>
            <?php if (isset($_GET['logout'])): ?>
                <div class="alert alert-sucesso"><i data-lucide="check-circle" class="icon"></i> Sessão encerrada com sucesso.</div>
            <?php endif; ?>
            <?php if (isset($_GET['senha_redefinida'])): ?>
                <div class="alert alert-sucesso"><i data-lucide="check-circle" class="icon"></i> Senha redefinida! Faça login com sua nova senha.</div>
            <?php endif; ?>

            <form method="POST" action="login.php" autocomplete="off">
                <div class="form-group">
                    <label for="usuario">Usuário ou e-mail</label>
                    <input type="text" id="usuario" name="usuario" placeholder="Digite seu usuário ou e-mail" required
                           value="<?= htmlspecialchars($_POST['usuario'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="senha">Senha</label>
                    <input type="password" id="senha" name="senha" placeholder="Digite sua senha" required>
                    <button type="button" class="password-toggle" onclick="alternarSenha()">
                        <i data-lucide="eye" class="icon" id="icone-olho"></i>
                    </button>
                </div>

                <div class="form-extra-row">
                    <label class="form-check">
                        <input type="checkbox" name="lembrar"> Lembrar-me
                    </label>
                    <a href="esqueci_senha.php">Esqueceu sua senha?</a>
                </div>

                <button type="submit" class="btn btn-primary"><i data-lucide="log-in" class="icon"></i> Entrar</button>
            </form>

            <div class="login-hint">
                Acesso de teste: <strong>admin</strong> / <strong>admin123</strong><br>
                <span style="opacity:.7;">Versão 1.0.0</span>
            </div>
        </div>
    </div>

    <script>
        if (window.lucide) { lucide.createIcons(); }

        function alternarSenha() {
            const campo = document.getElementById('senha');
            const icone = document.getElementById('icone-olho');
            const mostrar = campo.type === 'password';
            campo.type = mostrar ? 'text' : 'password';
            icone.setAttribute('data-lucide', mostrar ? 'eye-off' : 'eye');
            lucide.createIcons();
        }
    </script>
</body>
</html>
