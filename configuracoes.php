<?php
require_once __DIR__ . '/includes/auth.php';
protegerPagina();

$erro = '';
$sucesso = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'trocar_senha') {
    $senhaAtual = $_POST['senha_atual'] ?? '';
    $novaSenha = $_POST['nova_senha'] ?? '';
    $confirmar = $_POST['confirmar_senha'] ?? '';

    $resultado = alterarSenhaUsuario((int) $_SESSION['user_id'], $senhaAtual, $novaSenha, $confirmar);

    if ($resultado === true) {
        $sucesso = 'Senha alterada com sucesso!';
    } else {
        $erro = $resultado;
    }
}

$tituloPagina = 'Configurações';
require __DIR__ . '/includes/header.php';
?>

<div class="cards-grid" style="grid-template-columns: 1fr; gap: 22px;">

    <div class="card">
        <div class="card-icon-wrap"><i data-lucide="user"></i></div>
        <h3>Minha conta</h3>
        <div class="form-row" style="margin-top:12px;">
            <div><strong style="font-size:12px; color:#888;">NOME</strong><p style="margin:2px 0 0;"><?= htmlspecialchars($_SESSION['user_nome']) ?></p></div>
            <div><strong style="font-size:12px; color:#888;">USUÁRIO</strong><p style="margin:2px 0 0;">@<?= htmlspecialchars($_SESSION['user_usuario']) ?></p></div>
        </div>
        <div style="margin-top:8px;"><strong style="font-size:12px; color:#888;">PERFIL</strong><p style="margin:2px 0 0;"><?= htmlspecialchars(nomePerfil($_SESSION['user_perfil'])) ?></p></div>
    </div>

    <div class="card" style="max-width:480px;">
        <div class="card-icon-wrap laranja"><i data-lucide="lock"></i></div>
        <h3>Alterar senha</h3>

        <?php if ($erro): ?><div class="alert alert-erro"><i data-lucide="alert-triangle" class="icon"></i> <?= htmlspecialchars($erro) ?></div><?php endif; ?>
        <?php if ($sucesso): ?><div class="alert alert-sucesso"><i data-lucide="check-circle" class="icon"></i> <?= htmlspecialchars($sucesso) ?></div><?php endif; ?>

        <form method="POST" action="configuracoes.php">
            <input type="hidden" name="acao" value="trocar_senha">
            <div class="form-group">
                <label for="senha_atual">Senha atual</label>
                <input type="password" id="senha_atual" name="senha_atual" required>
            </div>
            <div class="form-group">
                <label for="nova_senha">Nova senha (mín. 6 caracteres)</label>
                <input type="password" id="nova_senha" name="nova_senha" required minlength="6">
            </div>
            <div class="form-group">
                <label for="confirmar_senha">Confirmar nova senha</label>
                <input type="password" id="confirmar_senha" name="confirmar_senha" required minlength="6">
            </div>
            <button type="submit" class="btn btn-primary" style="width:auto; padding:11px 26px;"><i data-lucide="save" class="icon"></i> Salvar nova senha</button>
        </form>
    </div>

    <div class="card">
        <div class="card-icon-wrap"><i data-lucide="info"></i></div>
        <h3>Sobre o sistema</h3>
        <p>AutoGest — Sistema de Gestão Mecânica.</p>
    </div>

</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
