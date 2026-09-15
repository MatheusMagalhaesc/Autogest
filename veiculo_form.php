<?php
require_once __DIR__ . '/includes/auth.php';
protegerPorPerfil(['administrador', 'recepcionista']);
require_once __DIR__ . '/includes/repo.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : null;
$veiculo = $id ? buscarVeiculo($id) : null;
if ($id && !$veiculo) { header('Location: veiculos.php'); exit; }

$clienteIdPre = (int) ($_GET['cliente_id'] ?? ($veiculo['cliente_id'] ?? 0));
$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $clienteId = (int) ($_POST['cliente_id'] ?? 0);
    $placa = trim($_POST['placa'] ?? '');
    $marca = trim($_POST['marca'] ?? '');
    $modelo = trim($_POST['modelo'] ?? '');
    $ano = trim($_POST['ano'] ?? '');
    $km = trim($_POST['km'] ?? '0');
    $cor = trim($_POST['cor'] ?? '');
    $combustivel = trim($_POST['combustivel'] ?? 'flex');

    if ($id) {
        $resultado = atualizarVeiculo($id, $placa, $marca, $modelo, $ano, $km, $cor, $combustivel);
        $msg = 'Veículo atualizado com sucesso!';
    } else {
        $resultado = criarVeiculo($clienteId, $placa, $marca, $modelo, $ano, $km, $cor, $combustivel);
        $msg = 'Veículo cadastrado com sucesso!';
    }

    if ($resultado === true || is_int($resultado)) {
        if ($id) {
            header('Location: veiculos.php?ok=' . urlencode($msg));
        } elseif ($clienteId) {
            header('Location: cliente_ver.php?id=' . $clienteId . '&ok=' . urlencode($msg));
        } else {
            header('Location: veiculos.php?ok=' . urlencode($msg));
        }
        exit;
    }
    $erro = $resultado;
    $clienteIdPre = $clienteId;
    $veiculo = ['id' => $id, 'placa' => $placa, 'marca' => $marca, 'modelo' => $modelo, 'ano' => $ano, 'km' => $km, 'cor' => $cor, 'combustivel' => $combustivel];
}

$clientes = listarClientes();
$tituloPagina = $id ? 'Editar Veículo' : 'Novo Veículo';
require __DIR__ . '/includes/header.php';
?>

<div class="form-card">
    <h3><?php if ($id): ?><i data-lucide="edit-2"></i> Editar veículo<?php else: ?><i data-lucide="car"></i> Novo veículo<?php endif; ?></h3>

    <?php if ($erro): ?><div class="alert alert-erro"><i data-lucide="alert-triangle" class="icon"></i> <?= htmlspecialchars($erro) ?></div><?php endif; ?>

    <?php if (empty($clientes)): ?>
        <div class="alert alert-erro"><i data-lucide="alert-triangle" class="icon"></i> Cadastre um cliente antes de adicionar um veículo. <a href="cliente_form.php">Cadastrar cliente</a></div>
    <?php else: ?>
    <form method="POST" action="veiculo_form.php<?= $id ? '?id=' . $id : '' ?>">
        <div class="form-group">
            <label for="cliente_id">Cliente (proprietário) *</label>
            <select id="cliente_id" name="cliente_id" required <?= $id ? 'disabled' : '' ?>>
                <option value="">Selecione o cliente...</option>
                <?php foreach ($clientes as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= $clienteIdPre == $c['id'] ? 'selected' : '' ?>><?= htmlspecialchars($c['nome']) ?></option>
                <?php endforeach; ?>
            </select>
            <?php if ($id): ?><input type="hidden" name="cliente_id" value="<?= $clienteIdPre ?>"><?php endif; ?>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="placa">Placa *</label>
                <input type="text" id="placa" name="placa" required maxlength="8" placeholder="ABC1D23" value="<?= htmlspecialchars($veiculo['placa'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="marca">Marca</label>
                <input type="text" id="marca" name="marca" placeholder="Ex: Toyota" value="<?= htmlspecialchars($veiculo['marca'] ?? '') ?>">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="modelo">Modelo *</label>
                <input type="text" id="modelo" name="modelo" required placeholder="Ex: Corolla" value="<?= htmlspecialchars($veiculo['modelo'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="ano">Ano</label>
                <input type="text" id="ano" name="ano" maxlength="4" placeholder="2020" value="<?= htmlspecialchars($veiculo['ano'] ?? '') ?>">
            </div>
        </div>

        <div class="form-row-3">
            <div class="form-group">
                <label for="km">Quilometragem</label>
                <input type="number" id="km" name="km" min="0" placeholder="0" value="<?= htmlspecialchars((string) ($veiculo['km'] ?? '0')) ?>">
            </div>
            <div class="form-group">
                <label for="cor">Cor</label>
                <input type="text" id="cor" name="cor" placeholder="Ex: Prata" value="<?= htmlspecialchars($veiculo['cor'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="combustivel">Combustível</label>
                <select id="combustivel" name="combustivel">
                    <?php foreach (COMBUSTIVEIS as $chave => $label): ?>
                        <option value="<?= $chave ?>" <?= ($veiculo['combustivel'] ?? 'flex') === $chave ? 'selected' : '' ?>><?= $label ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div style="display:flex; gap:10px; margin-top:8px;">
            <button type="submit" class="btn btn-primary" style="width:auto; padding:11px 26px;"><i data-lucide="save" class="icon"></i> <?= $id ? 'Salvar alterações' : 'Cadastrar veículo' ?></button>
            <a href="veiculos.php" class="btn btn-outline" style="padding:11px 20px;"><i data-lucide="x" class="icon"></i> Cancelar</a>
        </div>
    </form>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
