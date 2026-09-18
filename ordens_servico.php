<?php
require_once __DIR__ . '/includes/auth.php';
protegerPagina(); // Mecânico também acessa (atualiza status/diagnóstico das suas OS)
require_once __DIR__ . '/includes/repo.php';

$filtroStatus = trim($_GET['status'] ?? '');
$busca = trim($_GET['busca'] ?? '');

$filtros = [];
if ($filtroStatus) $filtros['status'] = $filtroStatus;
if ($busca) $filtros['busca'] = $busca;

$ordens = listarOS($filtros);
$tituloPagina = 'Ordens de Serviço';
require __DIR__ . '/includes/header.php';
?>

<?php if (isset($_GET['ok'])): ?><div class="alert alert-sucesso"><i data-lucide="check-circle" class="icon"></i> <?= htmlspecialchars($_GET['ok']) ?></div><?php endif; ?>

<div class="tabs">
    <a href="ordens_servico.php" class="<?= $filtroStatus === '' ? 'active' : '' ?>">Todas</a>
    <?php foreach (STATUS_OS as $chave => $label): ?>
        <a href="ordens_servico.php?status=<?= $chave ?>" class="<?= $filtroStatus === $chave ? 'active' : '' ?>"><?= $label ?></a>
    <?php endforeach; ?>
</div>

<div class="toolbar">
    <form method="GET" action="ordens_servico.php" class="search-box">
        <input type="hidden" name="status" value="<?= htmlspecialchars($filtroStatus) ?>">
        <input type="text" name="busca" placeholder="Buscar por cliente ou placa..." value="<?= htmlspecialchars($busca) ?>" style="width:280px;">
        <button type="submit" class="btn btn-outline"><i data-lucide="search" class="icon"></i> Buscar</button>
    </form>
    <?php if (in_array($_SESSION['user_perfil'], ['administrador', 'recepcionista'], true)): ?>
        <a href="os_form.php" class="btn btn-primary" style="width:auto; padding:10px 20px;"><i data-lucide="plus" class="icon"></i> Nova OS</a>
    <?php endif; ?>
</div>

<div class="card">
    <?php if (empty($ordens)): ?>
        <div class="empty-state">Nenhuma ordem de serviço encontrada.</div>
    <?php else: ?>
        <div style="overflow-x:auto;">
            <table class="data-table">
                <thead><tr><th>OS</th><th>Data</th><th>Cliente</th><th>Veículo</th><th>Mecânico</th><th>Status</th><th>Ações</th></tr></thead>
                <tbody>
                    <?php foreach ($ordens as $os): ?>
                        <tr>
                            <td>#<?= str_pad($os['id'], 4, '0', STR_PAD_LEFT) ?></td>
                            <td><?= date('d/m/Y', strtotime($os['criado_em'])) ?></td>
                            <td><?= htmlspecialchars($os['cliente_nome']) ?></td>
                            <td><?= htmlspecialchars($os['veiculo_modelo']) ?> (<?= htmlspecialchars($os['veiculo_placa']) ?>)</td>
                            <td><?= htmlspecialchars($os['mecanico_nome'] ?: 'A definir') ?></td>
                            <td><span class="badge badge-status-<?= $os['status'] ?>"><?= STATUS_OS[$os['status']] ?></span></td>
                            <td><a href="os_ver.php?id=<?= $os['id'] ?>" class="btn btn-sm btn-gray"><i data-lucide="eye" class="icon"></i> Ver</a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
