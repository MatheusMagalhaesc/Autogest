<?php
require_once __DIR__ . '/includes/auth.php';
protegerPorPerfil(['administrador', 'recepcionista']);
require_once __DIR__ . '/includes/repo.php';

$busca = trim($_GET['busca'] ?? '');
$clientes = listarClientes($busca);
$tituloPagina = 'Clientes';
require __DIR__ . '/includes/header.php';
?>

<?php if (isset($_GET['ok'])): ?><div class="alert alert-sucesso"><i data-lucide="check-circle" class="icon"></i> <?= htmlspecialchars($_GET['ok']) ?></div><?php endif; ?>

<div class="toolbar">
    <form method="GET" action="clientes.php" class="search-box">
        <input type="text" name="busca" placeholder="Buscar por nome, telefone ou CPF/CNPJ..." value="<?= htmlspecialchars($busca) ?>" style="width:300px;">
        <button type="submit" class="btn btn-outline"><i data-lucide="search" class="icon"></i> Buscar</button>
        <?php if ($busca): ?><a href="clientes.php" class="btn btn-outline">Limpar</a><?php endif; ?>
    </form>
    <a href="cliente_form.php" class="btn btn-primary" style="width:auto; padding:10px 20px;"><i data-lucide="plus" class="icon"></i> Novo Cliente</a>
</div>

<div class="card">
    <?php if (empty($clientes)): ?>
        <div class="empty-state"><?= $busca ? 'Nenhum cliente encontrado.' : 'Nenhum cliente cadastrado ainda.' ?></div>
    <?php else: ?>
        <div style="overflow-x:auto;">
            <table class="data-table">
                <thead><tr><th>Nome</th><th>CPF/CNPJ</th><th>Telefone</th><th>E-mail</th><th>Ações</th></tr></thead>
                <tbody>
                    <?php foreach ($clientes as $c): ?>
                        <tr>
                            <td><?= htmlspecialchars($c['nome']) ?></td>
                            <td><?= htmlspecialchars($c['cpf_cnpj'] ?: '—') ?></td>
                            <td><?= htmlspecialchars($c['telefone']) ?></td>
                            <td><?= htmlspecialchars($c['email'] ?: '—') ?></td>
                            <td>
                                <div class="actions-cell">
                                    <a href="cliente_ver.php?id=<?= $c['id'] ?>" class="btn btn-sm btn-gray"><i data-lucide="eye" class="icon"></i> Ver</a>
                                    <a href="cliente_form.php?id=<?= $c['id'] ?>" class="btn btn-sm btn-yellow"><i data-lucide="edit-2" class="icon"></i> Editar</a>
                                    <form method="POST" action="cliente_excluir.php" onsubmit="return confirm('Excluir este cliente? Isso também removerá seus veículos.');" style="display:inline;">
                                        <input type="hidden" name="id" value="<?= $c['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-red"><i data-lucide="trash-2" class="icon"></i> Excluir</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
