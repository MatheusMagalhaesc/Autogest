<?php
require_once __DIR__ . '/includes/auth.php';
protegerPorPerfil(['administrador', 'recepcionista']);
require_once __DIR__ . '/includes/repo.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : null;
$servico = $id ? buscarServico($id) : null;
if ($id && !$servico) { header('Location: servicos.php'); exit; }

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $descricao = trim($_POST['descricao'] ?? '');
    $preco = trim($_POST['preco'] ?? '0');

    if ($id) {
        $resultado = atualizarServico($id, $nome, $descricao, $preco);
        $msg = 'Serviço atualizado com sucesso!';
    } else {
        $resultado = criarServico($nome, $descricao, $preco);
        $msg = 'Serviço cadastrado com sucesso!';
    }

    if ($resultado === true || is_int($resultado)) {
        header('Location: servicos.php?ok=' . urlencode($msg));
        exit;
    }
    $erro = $resultado;
    $servico = ['id' => $id, 'nome' => $nome, 'descricao' => $descricao, 'preco' => $preco];
}

$tituloPagina = $id ? 'Editar Serviço' : 'Novo Serviço';
require __DIR__ . '/includes/header.php';
?>

<div class="form-card">
    <h3><?php if ($id): ?><i data-lucide="edit-2"></i> Editar serviço<?php else: ?><i data-lucide="wrench"></i> Novo serviço<?php endif; ?></h3>

    <?php if ($erro): ?><div class="alert alert-erro"><i data-lucide="alert-triangle" class="icon"></i> <?= htmlspecialchars($erro) ?></div><?php endif; ?>

    <form method="POST" action="servico_form.php<?= $id ? '?id=' . $id : '' ?>">
        <div class="form-group">
            <label for="nome">Nome do serviço *</label>
            <input type="text" id="nome" name="nome" required placeholder="Ex: Troca de óleo" value="<?= htmlspecialchars($servico['nome'] ?? '') ?>">
        </div>

        <div class="form-group">
            <label for="descricao">Descrição</label>
            <input type="text" id="descricao" name="descricao" placeholder="Ex: Troca de óleo e filtro" value="<?= htmlspecialchars($servico['descricao'] ?? '') ?>">
        </div>

        <div class="form-group">
            <label for="preco">Preço (R$) *</label>
            <input type="text" id="preco" name="preco" required placeholder="Ex: 120,00" value="<?= htmlspecialchars((string) ($servico['preco'] ?? '')) ?>">
        </div>

        <div style="display:flex; gap:10px; margin-top:8px;">
            <button type="submit" class="btn btn-primary" style="width:auto; padding:11px 26px;"><i data-lucide="save" class="icon"></i> <?= $id ? 'Salvar alterações' : 'Cadastrar serviço' ?></button>
            <a href="servicos.php" class="btn btn-outline" style="padding:11px 20px;"><i data-lucide="x" class="icon"></i> Cancelar</a>
        </div>
    </form>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
