<?php
require_once __DIR__ . '/includes/auth.php';
protegerPorPerfil(['administrador', 'recepcionista']);
require_once __DIR__ . '/includes/repo.php';

$busca = trim($_GET['busca'] ?? '');
$veiculos = listarVeiculos($busca);
$tituloPagina = 'Veículos';
require __DIR__ . '/includes/header.php';
?>

<?php if (isset($_GET['ok'])): ?><div class="alert alert-sucesso"><i data-lucide="check-circle" class="icon"></i> <?= htmlspecialchars($_GET['ok']) ?></div><?php endif; ?>

<div class="toolbar">
    <form method="GET" action="veiculos.php" class="search-box">
        <input type="text" name="busca" placeholder="Buscar por placa, modelo ou cliente..." value="<?= htmlspecialchars($busca) ?>" style="width:300px;">
        <button type="submit" class="btn btn-outline"><i data-lucide="search" class="icon"></i> Buscar</button>
        <?php if ($busca): ?><a href="veiculos.php" class="btn btn-outline">Limpar</a><?php endif; ?>
    </form>
    <a href="veiculo_form.php" class="btn btn-primary" style="width:auto; padding:10px 20px;"><i data-lucide="plus" class="icon"></i> Novo Veículo</a>
</div>

<div class="card">
    <?php if (empty($veiculos)): ?>
        <div class="empty-state"><?= $busca ? 'Nenhum veículo encontrado.' : 'Nenhum veículo cadastrado ainda. Cadastre um cliente primeiro.' ?></div>
    <?php else: ?>
        <div style="overflow-x:auto;">
            <table class="data-table">
                <thead><tr><th>Placa</th><th>Marca/Modelo</th><th>Ano</th><th>Cor</th><th>Cliente</th><th>Status</th><th>Ações</th></tr></thead>
                <tbody>
                    <?php foreach ($veiculos as $v): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($v['placa']) ?></strong></td>
                            <td><?= htmlspecialchars($v['marca'] ?: '') ?> <?= htmlspecialchars($v['modelo']) ?></td>
                            <td><?= htmlspecialchars($v['ano'] ?: '—') ?></td>
                            <td><?= htmlspecialchars($v['cor'] ?: '—') ?></td>
                            <td><a href="cliente_ver.php?id=<?= $v['cliente_id'] ?>"><?= htmlspecialchars($v['cliente_nome']) ?></a></td>
                            <td><span class="badge badge-ok">Ativo</span></td>
                            <td>
                                <div class="actions-cell">
                                    <a href="veiculo_ver.php?id=<?= $v['id'] ?>" class="btn btn-sm btn-gray"><i data-lucide="eye" class="icon"></i> Ver</a>
                                    <a href="veiculo_form.php?id=<?= $v['id'] ?>" class="btn btn-sm btn-yellow"><i data-lucide="edit-2" class="icon"></i> Editar</a>
                                    <form method="POST" action="veiculo_excluir.php" onsubmit="return confirm('Excluir este veículo?');" style="display:inline;">
                                        <input type="hidden" name="id" value="<?= $v['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-red"><i data-lucide="trash-2" class="icon"></i></button>
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
