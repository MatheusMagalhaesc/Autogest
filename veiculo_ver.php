<?php
require_once __DIR__ . '/includes/auth.php';
protegerPagina(); // Mecânico também pode consultar (HU3 Sprint 3)
require_once __DIR__ . '/includes/repo.php';

$id = (int) ($_GET['id'] ?? 0);
$veiculo = buscarVeiculo($id);
if (!$veiculo) { header('Location: veiculos.php'); exit; }

$historico = historicoOSDoVeiculo($id);
$tituloPagina = 'Ficha do Veículo';
require __DIR__ . '/includes/header.php';
?>

<div class="cards-grid" style="grid-template-columns: 1fr; gap: 22px;">

    <div class="card">
        <div class="card-icon-wrap laranja"><i data-lucide="car"></i></div>
        <h3><?= htmlspecialchars($veiculo['marca'] ?: '') ?> <?= htmlspecialchars($veiculo['modelo']) ?> — <?= htmlspecialchars($veiculo['placa']) ?></h3>
        <div class="form-row" style="margin-top:14px;">
            <div><strong style="font-size:12px; color:#888;">CLIENTE</strong><p style="margin:2px 0 0;"><a href="cliente_ver.php?id=<?= $veiculo['cliente_id'] ?>"><?= htmlspecialchars($veiculo['cliente_nome']) ?></a></p></div>
            <div><strong style="font-size:12px; color:#888;">ANO</strong><p style="margin:2px 0 0;"><?= htmlspecialchars($veiculo['ano'] ?: '—') ?></p></div>
        </div>
        <div class="form-row" style="margin-top:8px;">
            <div><strong style="font-size:12px; color:#888;">KM ATUAL</strong><p style="margin:2px 0 0;"><?= number_format((int) $veiculo['km'], 0, ',', '.') ?> km</p></div>
            <div><strong style="font-size:12px; color:#888;">COR / COMBUSTÍVEL</strong><p style="margin:2px 0 0;"><?= htmlspecialchars($veiculo['cor'] ?: '—') ?> · <?= COMBUSTIVEIS[$veiculo['combustivel']] ?? $veiculo['combustivel'] ?></p></div>
        </div>
        <div style="margin-top:16px; display:flex; gap:10px; flex-wrap:wrap;">
            <?php if (in_array($_SESSION['user_perfil'], ['administrador', 'recepcionista'], true)): ?>
                <a href="veiculo_form.php?id=<?= $veiculo['id'] ?>" class="btn btn-yellow btn-sm" style="padding:8px 18px;"><i data-lucide="edit-2" class="icon"></i> Editar</a>
                <a href="agendamento_form.php?cliente_id=<?= $veiculo['cliente_id'] ?>&veiculo_id=<?= $veiculo['id'] ?>" class="btn btn-success btn-sm" style="padding:8px 18px;"><i data-lucide="calendar-plus" class="icon"></i> Agendar serviço</a>
                <a href="os_form.php?veiculo_id=<?= $veiculo['id'] ?>" class="btn btn-secondary btn-sm" style="padding:8px 18px;"><i data-lucide="clipboard-list" class="icon"></i> Abrir OS</a>
            <?php endif; ?>
            <a href="veiculos.php" class="btn btn-outline btn-sm" style="padding:8px 18px;">← Voltar</a>
        </div>
    </div>

    <div class="card">
        <div class="card-icon-wrap"><i data-lucide="history"></i></div>
        <h3>Histórico de manutenções (<?= count($historico) ?>)</h3>
        <p>Todas as ordens de serviço já realizadas neste veículo — útil para o mecânico entender o que já foi feito antes de um novo diagnóstico.</p>
        <?php if (empty($historico)): ?>
            <div class="empty-state">Nenhuma manutenção registrada ainda para este veículo.</div>
        <?php else: ?>
            <div style="overflow-x:auto; margin-top:10px;">
                <table class="data-table">
                    <thead><tr><th>OS</th><th>Data</th><th>KM na época</th><th>Problema relatado</th><th>Mecânico</th><th>Status</th><th></th></tr></thead>
                    <tbody>
                    <?php foreach ($historico as $os): ?>
                        <tr>
                            <td>#<?= str_pad($os['id'], 4, '0', STR_PAD_LEFT) ?></td>
                            <td><?= date('d/m/Y', strtotime($os['criado_em'])) ?></td>
                            <td><?= number_format((int) $os['km_atual'], 0, ',', '.') ?> km</td>
                            <td><?= htmlspecialchars(mb_strimwidth($os['problema_relatado'] ?: '—', 0, 40, '…')) ?></td>
                            <td><?= htmlspecialchars($os['mecanico_nome'] ?: '—') ?></td>
                            <td><span class="badge badge-status-<?= $os['status'] ?>"><?= STATUS_OS[$os['status']] ?></span></td>
                            <td><a href="os_ver.php?id=<?= $os['id'] ?>" class="btn btn-sm btn-gray">Ver</a></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
