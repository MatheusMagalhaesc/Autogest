<?php
require_once __DIR__ . '/includes/auth.php';
protegerPorPerfil(['administrador', 'recepcionista']);
require_once __DIR__ . '/includes/repo.php';

$filtroStatus = trim($_GET['status'] ?? '');
$filtroData = trim($_GET['data'] ?? '');

$filtros = [];
if ($filtroStatus) $filtros['status'] = $filtroStatus;
if ($filtroData) $filtros['data'] = $filtroData;

$agendamentos = listarAgendamentos($filtros);
$tituloPagina = 'Agenda de Serviços';
require __DIR__ . '/includes/header.php';
?>

<?php if (isset($_GET['ok'])): ?><div class="alert alert-sucesso"><i data-lucide="check-circle" class="icon"></i> <?= htmlspecialchars($_GET['ok']) ?></div><?php endif; ?>

<div class="tabs">
    <a href="agendamentos.php" class="<?= $filtroStatus === '' ? 'active' : '' ?>">Todos</a>
    <?php foreach (STATUS_AGENDAMENTO as $chave => $label): ?>
        <a href="agendamentos.php?status=<?= $chave ?>" class="<?= $filtroStatus === $chave ? 'active' : '' ?>"><?= $label ?>s</a>
    <?php endforeach; ?>
</div>

<div class="toolbar">
    <form method="GET" action="agendamentos.php" style="display:flex; gap:8px; align-items:center;">
        <input type="hidden" name="status" value="<?= htmlspecialchars($filtroStatus) ?>">
        <input type="date" name="data" value="<?= htmlspecialchars($filtroData) ?>">
        <button type="submit" class="btn btn-outline">Filtrar por data</button>
        <?php if ($filtroData): ?><a href="agendamentos.php?status=<?= $filtroStatus ?>" class="btn btn-outline">Limpar data</a><?php endif; ?>
    </form>
    <a href="agendamento_form.php" class="btn btn-primary" style="width:auto; padding:10px 20px;"><i data-lucide="plus" class="icon"></i> Novo Agendamento</a>
</div>

<div class="card">
    <?php if (empty($agendamentos)): ?>
        <div class="empty-state">Nenhum agendamento encontrado.</div>
    <?php else: ?>
        <div style="overflow-x:auto;">
            <table class="data-table">
                <thead><tr><th>Data/Hora</th><th>Cliente</th><th>Veículo</th><th>Serviço</th><th>Mecânico</th><th>Status</th><th>Ações</th></tr></thead>
                <tbody>
                    <?php foreach ($agendamentos as $a): ?>
                        <tr>
                            <td><?= date('d/m/Y', strtotime($a['data_agendamento'])) ?> <?= substr($a['hora_agendamento'], 0, 5) ?></td>
                            <td><?= htmlspecialchars($a['cliente_nome']) ?></td>
                            <td><?= htmlspecialchars($a['veiculo_modelo']) ?> (<?= htmlspecialchars($a['veiculo_placa']) ?>)</td>
                            <td><?= htmlspecialchars($a['servico_nome']) ?></td>
                            <td><?= htmlspecialchars($a['mecanico_nome'] ?: 'A definir') ?></td>
                            <td><span class="badge badge-status-<?= $a['status'] === 'agendado' ? 'agendado' : ($a['status'] === 'concluido' ? 'concluido' : 'cancelado') ?>"><?= STATUS_AGENDAMENTO[$a['status']] ?></span></td>
                            <td>
                                <div class="actions-cell">
                                    <?php if ($a['status'] === 'agendado'): ?>
                                        <a href="agendamento_form.php?id=<?= $a['id'] ?>" class="btn btn-sm btn-yellow"><i data-lucide="edit-2" class="icon"></i> Editar</a>
                                        <a href="os_form.php?agendamento_id=<?= $a['id'] ?>" class="btn btn-sm btn-blue"><i data-lucide="clipboard-list" class="icon"></i> Abrir OS</a>
                                        <form method="POST" action="agendamento_cancelar.php" onsubmit="return confirm('Cancelar este agendamento?');" style="display:inline;">
                                            <input type="hidden" name="id" value="<?= $a['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-red"><i data-lucide="x-circle" class="icon"></i> Cancelar</button>
                                        </form>
                                    <?php else: ?>
                                        <span style="color:#999; font-size:12px;">—</span>
                                    <?php endif; ?>
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
