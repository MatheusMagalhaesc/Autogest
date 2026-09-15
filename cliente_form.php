<?php
require_once __DIR__ . '/includes/auth.php';
protegerPorPerfil(['administrador', 'recepcionista']);
require_once __DIR__ . '/includes/repo.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : null;
$cliente = $id ? buscarCliente($id) : null;
if ($id && !$cliente) { header('Location: clientes.php'); exit; }

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $cpfCnpj = trim($_POST['cpf_cnpj'] ?? '');
    $telefone = trim($_POST['telefone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $endereco = trim($_POST['endereco'] ?? '');

    if ($id) {
        $resultado = atualizarCliente($id, $nome, $cpfCnpj, $telefone, $email, $endereco);
        $msg = 'Cliente atualizado com sucesso!';
    } else {
        $resultado = criarCliente($nome, $cpfCnpj, $telefone, $email, $endereco);
        $msg = 'Cliente cadastrado com sucesso!';
    }

    if ($resultado === true || is_int($resultado)) {
        header('Location: clientes.php?ok=' . urlencode($msg));
        exit;
    }
    $erro = $resultado;
    $cliente = ['id' => $id, 'nome' => $nome, 'cpf_cnpj' => $cpfCnpj, 'telefone' => $telefone, 'email' => $email, 'endereco' => $endereco];
}

$tituloPagina = $id ? 'Editar Cliente' : 'Novo Cliente';
require __DIR__ . '/includes/header.php';
?>

<div class="form-card">
    <h3><?php if ($id): ?><i data-lucide="edit-2"></i> Editar cliente<?php else: ?><i data-lucide="user-plus"></i> Novo cliente<?php endif; ?></h3>

    <?php if ($erro): ?><div class="alert alert-erro"><i data-lucide="alert-triangle" class="icon"></i> <?= htmlspecialchars($erro) ?></div><?php endif; ?>

    <form method="POST" action="cliente_form.php<?= $id ? '?id=' . $id : '' ?>">
        <div class="form-row">
            <div class="form-group">
                <label for="nome">Nome completo *</label>
                <input type="text" id="nome" name="nome" required value="<?= htmlspecialchars($cliente['nome'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="cpf_cnpj">CPF/CNPJ</label>
                <input type="text" id="cpf_cnpj" name="cpf_cnpj" placeholder="000.000.000-00" value="<?= htmlspecialchars($cliente['cpf_cnpj'] ?? '') ?>">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="telefone">Telefone *</label>
                <input type="text" id="telefone" name="telefone" required placeholder="(00) 00000-0000" value="<?= htmlspecialchars($cliente['telefone'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="email">E-mail</label>
                <input type="email" id="email" name="email" value="<?= htmlspecialchars($cliente['email'] ?? '') ?>">
            </div>
        </div>

        <div class="form-group">
            <label for="endereco">Endereço</label>
            <input type="text" id="endereco" name="endereco" placeholder="Rua, número, bairro - cidade" value="<?= htmlspecialchars($cliente['endereco'] ?? '') ?>">
        </div>

        <div style="display:flex; gap:10px; margin-top:8px;">
            <button type="submit" class="btn btn-primary" style="width:auto; padding:11px 26px;"><i data-lucide="save" class="icon"></i> <?= $id ? 'Salvar alterações' : 'Cadastrar cliente' ?></button>
            <a href="clientes.php" class="btn btn-outline" style="padding:11px 20px;"><i data-lucide="x" class="icon"></i> Cancelar</a>
        </div>
    </form>

    <?php if ($id): ?>
        <?php $veiculos = listarVeiculosDoCliente($id); ?>
        <div style="margin-top:24px; padding-top:20px; border-top:1px solid #eee;">
            <p style="font-size:12px; font-weight:700; color:#5b655f; text-transform:uppercase; margin:0 0 10px;">Veículos deste cliente</p>
            <?php if (empty($veiculos)): ?>
                <p style="font-size:13px; color:#888;">Nenhum veículo cadastrado ainda.</p>
            <?php else: ?>
                <ul style="padding-left:18px; font-size:13.5px;">
                    <?php foreach ($veiculos as $v): ?>
                        <li><?= htmlspecialchars($v['modelo']) ?> — <?= htmlspecialchars($v['placa']) ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
            <a href="veiculo_form.php?cliente_id=<?= $id ?>" class="btn btn-outline btn-sm" style="padding:7px 16px;"><i data-lucide="plus" class="icon"></i> Adicionar veículo</a>
        </div>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
