<?php
require_once __DIR__ . '/includes/auth.php';
protegerPorPerfil(['administrador', 'recepcionista']);
require_once __DIR__ . '/includes/repo.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : null;
$agendamento = $id ? buscarAgendamento($id) : null;
if ($id && !$agendamento) { header('Location: agendamentos.php'); exit; }

$clienteIdPre = (int) ($_GET['cliente_id'] ?? ($agendamento['cliente_id'] ?? 0));
$veiculoIdPre = (int) ($_GET['veiculo_id'] ?? ($agendamento['veiculo_id'] ?? 0));
$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $clienteId = (int) ($_POST['cliente_id'] ?? 0);
    $veiculoId = (int) ($_POST['veiculo_id'] ?? 0);
    $servicoId = (int) ($_POST['servico_id'] ?? 0);
    $mecanicoId = (int) ($_POST['mecanico_id'] ?? 0);
    $data = trim($_POST['data_agendamento'] ?? '');
    $hora = trim($_POST['hora_agendamento'] ?? '');
    $observacoes = trim($_POST['observacoes'] ?? '');

    if ($id) {
        $resultado = atualizarAgendamento($id, $veiculoId, $servicoId, $mecanicoId ?: null, $data, $hora, $observacoes);
        $msg = 'Agendamento atualizado com sucesso!';
    } else {
        $resultado = criarAgendamento($clienteId, $veiculoId, $servicoId, $mecanicoId ?: null, $data, $hora, $observacoes);
        $msg = 'Agendamento criado com sucesso!';
    }

    if ($resultado === true || is_int($resultado)) {
        header('Location: agendamentos.php?ok=' . urlencode($msg));
        exit;
    }
    $erro = $resultado;
    $clienteIdPre = $clienteId;
    $veiculoIdPre = $veiculoId;
    $agendamento = $agendamento ?: [];
    $agendamento['servico_id'] = $servicoId;
    $agendamento['mecanico_id'] = $mecanicoId;
    $agendamento['data_agendamento'] = $data;
    $agendamento['hora_agendamento'] = $hora;
    $agendamento['observacoes'] = $observacoes;
}

$clientes = listarClientes();
$veiculosTodos = listarVeiculos();
$servicos = listarServicos(true);
$mecanicos = listarMecanicos();

$tituloPagina = $id ? 'Editar Agendamento' : 'Novo Agendamento';
require __DIR__ . '/includes/header.php';
?>

<div class="form-card" style="max-width:640px;">
    <h3><?php if ($id): ?><i data-lucide="edit-2"></i> Editar agendamento<?php else: ?><i data-lucide="calendar-plus"></i> Novo agendamento<?php endif; ?></h3>

    <?php if ($erro): ?><div class="alert alert-erro"><i data-lucide="alert-triangle" class="icon"></i> <?= htmlspecialchars($erro) ?></div><?php endif; ?>

    <?php if (empty($clientes)): ?>
        <div class="alert alert-erro"><i data-lucide="alert-triangle" class="icon"></i> Cadastre um cliente e um veículo antes de agendar. <a href="cliente_form.php">Cadastrar cliente</a></div>
    <?php elseif (empty($veiculosTodos)): ?>
        <div class="alert alert-erro"><i data-lucide="alert-triangle" class="icon"></i> Cadastre um veículo antes de agendar. <a href="veiculo_form.php">Cadastrar veículo</a></div>
    <?php elseif (empty($servicos)): ?>
        <div class="alert alert-erro"><i data-lucide="alert-triangle" class="icon"></i> Cadastre um serviço ativo antes de agendar. <a href="servico_form.php">Cadastrar serviço</a></div>
    <?php else: ?>

    <form method="POST" action="agendamento_form.php<?= $id ? '?id=' . $id : '' ?>">
        <div class="form-row">
            <div class="form-group">
                <label for="cliente_id">Cliente *</label>
                <select id="cliente_id" name="cliente_id" required onchange="filtrarVeiculos()" <?= $id ? 'disabled' : '' ?>>
                    <option value="">Selecione...</option>
                    <?php foreach ($clientes as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= $clienteIdPre == $c['id'] ? 'selected' : '' ?>><?= htmlspecialchars($c['nome']) ?></option>
                    <?php endforeach; ?>
                </select>
                <?php if ($id): ?><input type="hidden" name="cliente_id" value="<?= $clienteIdPre ?>"><?php endif; ?>
            </div>
            <div class="form-group">
                <label for="veiculo_id">Veículo *</label>
                <select id="veiculo_id" name="veiculo_id" required>
                    <option value="">Selecione o cliente primeiro...</option>
                    <?php foreach ($veiculosTodos as $v): ?>
                        <option value="<?= $v['id'] ?>" data-cliente="<?= $v['cliente_id'] ?>" <?= $veiculoIdPre == $v['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($v['modelo']) ?> — <?= htmlspecialchars($v['placa']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="servico_id">Serviço *</label>
                <select id="servico_id" name="servico_id" required>
                    <option value="">Selecione...</option>
                    <?php foreach ($servicos as $s): ?>
                        <option value="<?= $s['id'] ?>" <?= ($agendamento['servico_id'] ?? null) == $s['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($s['nome']) ?> — R$ <?= number_format((float) $s['preco'], 2, ',', '.') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="mecanico_id">Mecânico responsável</label>
                <select id="mecanico_id" name="mecanico_id">
                    <option value="">A definir</option>
                    <?php foreach ($mecanicos as $m): ?>
                        <option value="<?= $m['id'] ?>" <?= ($agendamento['mecanico_id'] ?? null) == $m['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($m['nome']) ?><?= $m['especialidade'] ? ' — ' . htmlspecialchars($m['especialidade']) : '' ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (empty($mecanicos)): ?><small>Nenhum mecânico cadastrado ainda — cadastre em Usuários.</small><?php endif; ?>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="data_agendamento">Data *</label>
                <input type="date" id="data_agendamento" name="data_agendamento" required value="<?= htmlspecialchars($agendamento['data_agendamento'] ?? date('Y-m-d')) ?>">
            </div>
            <div class="form-group">
                <label for="hora_agendamento">Horário *</label>
                <input type="time" id="hora_agendamento" name="hora_agendamento" required value="<?= htmlspecialchars(substr($agendamento['hora_agendamento'] ?? '', 0, 5)) ?>">
                <small>Não é possível agendar o mesmo mecânico duas vezes no mesmo horário.</small>
            </div>
        </div>

        <div class="form-group">
            <label for="observacoes">Observações</label>
            <input type="text" id="observacoes" name="observacoes" placeholder="Ex: Cliente relatou barulho no motor" value="<?= htmlspecialchars($agendamento['observacoes'] ?? '') ?>">
        </div>

        <div style="display:flex; gap:10px; margin-top:8px;">
            <button type="submit" class="btn btn-primary" style="width:auto; padding:11px 26px;"><i data-lucide="save" class="icon"></i> <?= $id ? 'Salvar alterações' : 'Agendar' ?></button>
            <a href="agendamentos.php" class="btn btn-outline" style="padding:11px 20px;"><i data-lucide="x" class="icon"></i> Cancelar</a>
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
