<?php
require_once __DIR__ . '/includes/auth.php';

if (usuarioLogado()) {
    header('Location: dashboard.php');
    exit;
}

$erro = '';
$linkGerado = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuarioOuEmail = trim($_POST['usuario_ou_email'] ?? '');

    if ($usuarioOuEmail === '') {
        $erro = 'Informe seu usuário ou e-mail cadastrado.';
    } else {
        $resultado = gerarTokenRecuperacao($usuarioOuEmail);
        if ($resultado) {
            $linkGerado = 'redefinir_senha.php?token=' . $resultado['token'];
        } else {
            // Por segurança, não revelamos se o usuário existe ou não
            $erro = 'Se o usuário/e-mail existir em nosso sistema, um link de recuperação seria enviado.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar senha — AutoGest</title>
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
            <h2>Esqueci minha senha</h2>
            <p class="subtitle">Informe seu usuário ou e-mail para receber o link de recuperação</p>

            <?php if ($erro): ?>
                <div class="alert alert-erro"><i data-lucide="alert-circle" class="icon"></i> <?= htmlspecialchars($erro) ?></div>
            <?php endif; ?>

            <?php if ($linkGerado): ?>
                <div class="alert alert-sucesso"><i data-lucide="check-circle" class="icon"></i> Link de recuperação gerado com sucesso!</div>
                <div class="alert alert-info" style="flex-direction:column; align-items:flex-start; gap:6px;">
                    <strong>Modo de teste (sem servidor de e-mail configurado):</strong>
                    <span>Em produção, este link seria enviado por e-mail. Por enquanto, clique abaixo para continuar:</span>
                    <a href="<?= htmlspecialchars($linkGerado) ?>" class="btn btn-secondary btn-sm" style="margin-top:6px;">
                        <i data-lucide="key-round" class="icon"></i> Redefinir minha senha agora
                    </a>
                </div>
            <?php else: ?>
                <form method="POST" action="esqueci_senha.php">
                    <div class="form-group">
                        <label for="usuario_ou_email">Usuário ou e-mail</label>
                        <input type="text" id="usuario_ou_email" name="usuario_ou_email" placeholder="Digite seu usuário ou e-mail" required
                               value="<?= htmlspecialchars($_POST['usuario_ou_email'] ?? '') ?>">
                    </div>
                    <button type="submit" class="btn btn-primary"><i data-lucide="send" class="icon"></i> Enviar link de recuperação</button>
                </form>
            <?php endif; ?>

            <div class="login-hint">
                <a href="login.php" style="color:var(--azul-principal); font-weight:600;">← Voltar ao login</a>
            </div>
        </div>
    </div>

    <script>if (window.lucide) { lucide.createIcons(); }</script>
</body>
</html>
