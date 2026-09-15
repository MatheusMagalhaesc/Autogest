<?php
require_once __DIR__ . '/includes/auth.php';

$token = trim($_GET['token'] ?? $_POST['token'] ?? '');
$resetInfo = $token ? validarTokenRecuperacao($token) : false;

$erro = '';
$sucesso = false;

if (!$token || !$resetInfo) {
    $erro = 'Link inválido ou expirado. Solicite a recuperação novamente.';
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $novaSenha = $_POST['nova_senha'] ?? '';
    $confirmar = $_POST['confirmar_senha'] ?? '';

    $resultado = redefinirSenhaComToken($token, $novaSenha, $confirmar);
    if ($resultado === true) {
        header('Location: login.php?senha_redefinida=1');
        exit;
    }
    $erro = $resultado;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redefinir senha — AutoGest</title>
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
            <h2>Redefinir senha</h2>
            <p class="subtitle"><?= $resetInfo ? 'Olá, ' . htmlspecialchars($resetInfo['nome']) . '! Defina sua nova senha.' : 'Não foi possível continuar.' ?></p>

            <?php if ($erro): ?>
                <div class="alert alert-erro"><i data-lucide="alert-circle" class="icon"></i> <?= htmlspecialchars($erro) ?></div>
            <?php endif; ?>

            <?php if ($resetInfo): ?>
                <form method="POST" action="redefinir_senha.php">
                    <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                    <div class="form-group">
                        <label for="nova_senha">Nova senha (mín. 6 caracteres)</label>
                        <input type="password" id="nova_senha" name="nova_senha" required minlength="6">
                    </div>
                    <div class="form-group">
                        <label for="confirmar_senha">Confirmar nova senha</label>
                        <input type="password" id="confirmar_senha" name="confirmar_senha" required minlength="6">
                    </div>
                    <button type="submit" class="btn btn-primary"><i data-lucide="check" class="icon"></i> Redefinir senha</button>
                </form>
            <?php else: ?>
                <a href="esqueci_senha.php" class="btn btn-primary"><i data-lucide="rotate-ccw" class="icon"></i> Solicitar novo link</a>
            <?php endif; ?>

            <div class="login-hint">
                <a href="login.php" style="color:var(--azul-principal); font-weight:600;">← Voltar ao login</a>
            </div>
        </div>
    </div>

    <script>if (window.lucide) { lucide.createIcons(); }</script>
</body>
</html>
