<?php
require_once __DIR__ . '/includes/auth.php';
protegerPorPerfil(['administrador', 'recepcionista']);
require_once __DIR__ . '/includes/repo.php';

$id = (int) ($_GET['id'] ?? 0);
$cliente = buscarCliente($id);
if (!$cliente) { header('Location: clientes.php'); exit; }

$veiculos = listarVeiculosDoCliente($id);
$historicoOS = historicoOSDoCliente($id);
$tituloPagina = 'Ficha do Cliente';
require __DIR__ . '/includes/header.php';
?>

<div class="cards-grid" style="grid-template-columns: 1fr; gap: 22px;">

    <?php if (isset($_GET['ok'])): ?><div class="alert alert-sucesso"><i data-lucide="check-circle" class="icon"></i> <?= htmlspecialchars($_GET['ok']) ?></div><?php endif; ?>

    <div class="card">
        <div class="card-icon-wrap"><i data-lucide="user"></i></div>
        <h3><?= htmlspecialchars($cliente['nome']) ?></h3>
        <div class="form-row" style="margin-top:14px;">
            <div><strong style="font-size:12px; color:#888;">CPF/CNPJ</strong><p style="margin:2px 0 0;"><?= htmlspecialchars($cliente['cpf_cnpj'] ?: '—') ?></p></div>
            <div><strong style="font-size:12px; color:#888;">TELEFONE</strong><p style="margin:2px 0 0;"><?= htmlspecialchars($cliente['telefone']) ?></p></div>
        </div>
        <div class="form-row" style="margin-top:8px;">
            <div><strong style="font-size:12px; color:#888;">E-MAIL</strong><p style="margin:2px 0 0;"><?= htmlspecialchars($cliente['email'] ?: '—') ?></p></div>
            <div><strong style="font-size:12px; color:#888;">ENDEREÇO</strong><p style="margin:2px 0 0;"><?= htmlspecialchars($cliente['endereco'] ?: '—') ?></p></div>
        </div>
        <div style="margin-top:16px; display:flex; gap:10px; flex-wrap:wrap;">
            <a href="cliente_form.php?id=<?= $cliente['id'] ?>" class="btn btn-yellow btn-sm" style="padding:8px 18px;"><i data-lucide="edit-2" class="icon"></i> Editar</a>
            <a href="veiculo_form.php?cliente_id=<?= $cliente['id'] ?>" class="btn btn-blue btn-sm" style="padding:8px 18px;"><i data-lucide="plus" class="icon"></i> Novo veículo</a>
            <?php if (!empty($veiculos)): ?>
                <a href="agendamento_form.php?cliente_id=<?= $cliente['id'] ?>" class="btn btn-success btn-sm" style="padding:8px 18px;"><i data-lucide="plus" class="icon"></i> Novo agendamento</a>
            <?php endif; ?>
            <a href="clientes.php" class="btn btn-outline btn-sm" style="padding:8px 18px;">← Voltar</a>
        </div>
    </div>

    <div class="card">
        <div class="card-icon-wrap laranja"><i data-lucide="car"></i></div>
        <h3>Veículos (<?= count($veiculos) ?>)</h3>
        <?php if (empty($veiculos)): ?>
            <div class="empty-state">Nenhum veículo cadastrado para este cliente.</div>
        <?php else: ?>
            <div style="overflow-x:auto; margin-top:10px;">
                <table class="data-table">
                    <thead><tr><th>Placa</th><th>Marca/Modelo</th><th>Ano</th><th>KM</th><th>Ações</th></tr></thead>
                    <tbody>
                    <?php foreach ($veiculos as $v): ?>
                        <tr>
                            <td><?= htmlspecialchars($v['placa']) ?></td>
                            <td><?= htmlspecialchars($v['marca'] ?: '') ?> <?= htmlspecialchars($v['modelo']) ?></td>
                            <td><?= htmlspecialchars($v['ano'] ?: '—') ?></td>
                            <td><?= number_format((int) $v['km'], 0, ',', '.') ?> km</td>
                            <td>
                                <a href="veiculo_ver.php?id=<?= $v['id'] ?>" class="btn btn-sm btn-gray"><i data-lucide="eye" class="icon"></i> Ver</a>
                                <a href="veiculo_form.php?id=<?= $v['id'] ?>" class="btn btn-sm btn-yellow"><i data-lucide="edit-2" class="icon"></i> Editar</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <div class="card">
        <div class="card-icon-wrap"><i data-lucide="history"></i></div>
        <h3>Histórico de serviços (<?= count($historicoOS) ?>)</h3>
        <p>Ordens de serviço já realizadas ou em andamento para este cliente.</p>
        <?php if (empty($historicoOS)): ?>
            <div class="empty-state">Nenhuma ordem de serviço registrada ainda.</div>
        <?php else: ?>
            <div style="overflow-x:auto; margin-top:10px;">
                <table class="data-table">
                    <thead><tr><th>OS</th><th>Data</th><th>Veículo</th><th>Mecânico</th><th>Status</th><th></th></tr></thead>
                    <tbody>
                    <?php foreach ($historicoOS as $os): ?>
                        <tr>
                            <td>#<?= str_pad($os['id'], 4, '0', STR_PAD_LEFT) ?></td>
                            <td><?= date('d/m/Y', strtotime($os['criado_em'])) ?></td>
                            <td><?= htmlspecialchars($os['veiculo_modelo']) ?> (<?= htmlspecialchars($os['veiculo_placa']) ?>)</td>
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
