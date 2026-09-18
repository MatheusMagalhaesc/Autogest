<?php
require_once __DIR__ . '/includes/auth.php';
protegerPorPerfil(['administrador', 'recepcionista']);
require_once __DIR__ . '/includes/repo.php';

$agendamentoId = (int) ($_GET['agendamento_id'] ?? 0);
$agendamentoOrigem = $agendamentoId ? buscarAgendamento($agendamentoId) : null;

$clienteIdPre = (int) ($_GET['cliente_id'] ?? ($agendamentoOrigem['cliente_id'] ?? 0));
$veiculoIdPre = (int) ($_GET['veiculo_id'] ?? ($agendamentoOrigem['veiculo_id'] ?? 0));
$mecanicoIdPre = (int) ($agendamentoOrigem['mecanico_id'] ?? 0);
$kmPre = '';
$problemaPre = $agendamentoOrigem['observacoes'] ?? '';

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $clienteId = (int) ($_POST['cliente_id'] ?? 0);
    $veiculoId = (int) ($_POST['veiculo_id'] ?? 0);
    $mecanicoId = (int) ($_POST['mecanico_id'] ?? 0);
    $km = trim($_POST['km_atual'] ?? '0');
    $problema = trim($_POST['problema_relatado'] ?? '');
    $agendamentoIdPost = (int) ($_POST['agendamento_id'] ?? 0);

    $resultado = criarOS($clienteId, $veiculoId, $mecanicoId ?: null, $km, $problema, $agendamentoIdPost ?: null);

    if (is_int($resultado)) {
        // Se veio de um agendamento, marca o agendamento como concluído (atendimento iniciado via OS)
        if ($agendamentoIdPost) {
            atualizarStatusAgendamento($agendamentoIdPost, 'concluido');
        }
        header('Location: os_ver.php?id=' . $resultado . '&ok=' . urlencode('Ordem de serviço aberta com sucesso!'));
        exit;
    }
    $erro = $resultado;
    $clienteIdPre = $clienteId;
    $veiculoIdPre = $veiculoId;
    $mecanicoIdPre = $mecanicoId;
    $kmPre = $km;
    $problemaPre = $problema;
}

$clientes = listarClientes();
$veiculosTodos = listarVeiculos();
$mecanicos = listarMecanicos();

$tituloPagina = 'Nova Ordem de Serviço';
require __DIR__ . '/includes/header.php';
?>

<div class="form-card" style="max-width:640px;">
    <h3><i data-lucide="clipboard-list"></i> Abrir Ordem de Serviço</h3>

    <?php if ($agendamentoOrigem): ?>
        <div class="alert alert-info">
            <i data-lucide="info" class="icon"></i>
            OS aberta a partir do agendamento de <?= date('d/m/Y', strtotime($agendamentoOrigem['data_agendamento'])) ?> às <?= substr($agendamentoOrigem['hora_agendamento'], 0, 5) ?> (<?= htmlspecialchars($agendamentoOrigem['servico_nome']) ?>).
        </div>
    <?php endif; ?>

    <?php if ($erro): ?><div class="alert alert-erro"><i data-lucide="alert-triangle" class="icon"></i> <?= htmlspecialchars($erro) ?></div><?php endif; ?>

    <?php if (empty($clientes) || empty($veiculosTodos)): ?>
        <div class="alert alert-erro"><i data-lucide="alert-triangle" class="icon"></i> Cadastre um cliente e um veículo antes de abrir uma OS.</div>
    <?php else: ?>

    <form method="POST" action="os_form.php<?= $agendamentoId ? '?agendamento_id=' . $agendamentoId : '' ?>">
        <input type="hidden" name="agendamento_id" value="<?= $agendamentoId ?>">

        <div class="form-row">
            <div class="form-group">
                <label for="cliente_id">Cliente *</label>
                <select id="cliente_id" name="cliente_id" required onchange="filtrarVeiculos()" <?= $agendamentoId ? 'disabled' : '' ?>>
                    <option value="">Selecione...</option>
                    <?php foreach ($clientes as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= $clienteIdPre == $c['id'] ? 'selected' : '' ?>><?= htmlspecialchars($c['nome']) ?></option>
                    <?php endforeach; ?>
                </select>
                <?php if ($agendamentoId): ?><input type="hidden" name="cliente_id" value="<?= $clienteIdPre ?>"><?php endif; ?>
            </div>
            <div class="form-group">
                <label for="veiculo_id">Veículo *</label>
                <select id="veiculo_id" name="veiculo_id" required <?= $agendamentoId ? 'disabled' : '' ?>>
                    <option value="">Selecione o cliente primeiro...</option>
                    <?php foreach ($veiculosTodos as $v): ?>
                        <option value="<?= $v['id'] ?>" data-cliente="<?= $v['cliente_id'] ?>" <?= $veiculoIdPre == $v['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($v['modelo']) ?> — <?= htmlspecialchars($v['placa']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if ($agendamentoId): ?><input type="hidden" name="veiculo_id" value="<?= $veiculoIdPre ?>"><?php endif; ?>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="mecanico_id">Mecânico responsável</label>
                <select id="mecanico_id" name="mecanico_id">
                    <option value="">A definir</option>
                    <?php foreach ($mecanicos as $m): ?>
                        <option value="<?= $m['id'] ?>" <?= $mecanicoIdPre == $m['id'] ? 'selected' : '' ?>><?= htmlspecialchars($m['nome']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="km_atual">Quilometragem atual</label>
                <input type="number" id="km_atual" name="km_atual" min="0" value="<?= htmlspecialchars($kmPre) ?>">
            </div>
        </div>

        <div class="form-group">
            <label for="problema_relatado">Problema relatado pelo cliente</label>
            <textarea id="problema_relatado" name="problema_relatado" rows="3" placeholder="Descreva o que o cliente relatou..."><?= htmlspecialchars($problemaPre) ?></textarea>
        </div>

        <div style="display:flex; gap:10px; margin-top:8px;">
            <button type="submit" class="btn btn-primary" style="width:auto; padding:11px 26px;"><i data-lucide="save" class="icon"></i> Abrir OS</button>
            <a href="ordens_servico.php" class="btn btn-outline" style="padding:11px 20px;"><i data-lucide="x" class="icon"></i> Cancelar</a>
        </div>
    </form>
    <?php endif; ?>
</div>

<script>
function filtrarVeiculos() {
    const clienteId = document.getElementById('cliente_id').value;
    const select = document.getElementById('veiculo_id');
    for (const opt of select.options) {
        if (!opt.value) { opt.hidden = false; continue; }
        opt.hidden = opt.getAttribute('data-cliente') !== clienteId;
    }
    if (!select.options[select.selectedIndex] || select.options[select.selectedIndex].hidden) {
        select.value = '';
    }
}
document.addEventListener('DOMContentLoaded', filtrarVeiculos);
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>
