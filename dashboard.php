<?php
require_once __DIR__ . '/includes/auth.php';
protegerPagina();
require_once __DIR__ . '/includes/repo.php';

$tituloPagina = 'Início';
$stats = statsDashboard();
$proximos = agendamentosProximos(2);
require __DIR__ . '/includes/header.php';
?>

<div class="welcome-banner">
    <h2>Olá, <?= htmlspecialchars($_SESSION['user_nome']) ?>! 👋</h2>
    <p>Você está logado como <strong><?= htmlspecialchars(nomePerfil($_SESSION['user_perfil'])) ?></strong>. Aqui está o resumo da oficina.</p>
</div>

<div class="cards-grid" style="margin-bottom:24px;">
    <div class="stat-card">
        <div class="stat-icon-wrap"><i data-lucide="users"></i></div>
        <div class="stat-num"><?= $stats['clientes'] ?></div>
        <div class="stat-label">Clientes cadastrados</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon-wrap laranja"><i data-lucide="car"></i></div>
        <div class="stat-num"><?= $stats['veiculos'] ?></div>
        <div class="stat-label">Veículos cadastrados</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon-wrap"><i data-lucide="calendar-check"></i></div>
        <div class="stat-num"><?= $stats['agendamentos_hoje'] ?></div>
        <div class="stat-label">Agendamentos hoje</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon-wrap erro"><i data-lucide="clipboard-list"></i></div>
        <div class="stat-num"><?= $stats['os_abertas'] ?></div>
        <div class="stat-label">Ordens de Serviço abertas</div>
    </div>
</div>

<div class="chart-card" style="margin-bottom:24px;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px;">
        <h3 style="margin:0;"><i data-lucide="bell" class="icon"></i> Lembretes — próximos agendamentos (48h)</h3>
        <a href="agendamentos.php" class="btn btn-outline btn-sm" style="padding:6px 14px;">Ver agenda completa</a>
    </div>
    <?php if (empty($proximos)): ?>
        <div class="empty-state">Nenhum agendamento nas próximas 48 horas.</div>
    <?php else: ?>
        <table class="data-table">
            <thead><tr><th>Data/Hora</th><th>Cliente</th><th>Veículo</th><th>Serviço</th><th>Mecânico</th></tr></thead>
            <tbody>
                <?php foreach ($proximos as $a): ?>
                    <tr>
                        <td><?= date('d/m/Y', strtotime($a['data_agendamento'])) ?> às <?= substr($a['hora_agendamento'], 0, 5) ?></td>
                        <td><?= htmlspecialchars($a['cliente_nome']) ?></td>
                        <td><?= htmlspecialchars($a['veiculo_modelo']) ?> (<?= htmlspecialchars($a['veiculo_placa']) ?>)</td>
                        <td><?= htmlspecialchars($a['servico_nome']) ?></td>
                        <td><?= htmlspecialchars($a['mecanico_nome'] ?: 'A definir') ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php if (in_array($_SESSION['user_perfil'], ['administrador', 'recepcionista'], true)): ?>
<div class="cards-grid">
    <div class="card"><div class="card-icon-wrap"><i data-lucide="users"></i></div><h3>Clientes</h3><p>Cadastro, busca e histórico de clientes.</p><a href="clientes.php" class="btn btn-outline btn-sm" style="padding:6px 14px;">Acessar →</a></div>
    <div class="card"><div class="card-icon-wrap laranja"><i data-lucide="car"></i></div><h3>Veículos</h3><p>Veículos vinculados aos clientes.</p><a href="veiculos.php" class="btn btn-outline btn-sm" style="padding:6px 14px;">Acessar →</a></div>
    <div class="card"><div class="card-icon-wrap"><i data-lucide="calendar-check"></i></div><h3>Agendamentos</h3><p>Organize a agenda da oficina.</p><a href="agendamentos.php" class="btn btn-outline btn-sm" style="padding:6px 14px;">Acessar →</a></div>
    <div class="card"><div class="card-icon-wrap erro"><i data-lucide="clipboard-list"></i></div><h3>Ordens de Serviço</h3><p>Abertura, diagnóstico e status da OS.</p><a href="ordens_servico.php" class="btn btn-outline btn-sm" style="padding:6px 14px;">Acessar →</a></div>
</div>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
