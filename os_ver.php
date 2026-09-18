<?php
require_once __DIR__ . '/includes/auth.php';
protegerPagina();
require_once __DIR__ . '/includes/repo.php';

$id = (int) ($_GET['id'] ?? 0);
$erro = '';
$sucesso = '';

// ---- Ações via POST ----
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';

    if ($acao === 'diagnostico') {
        $diagnostico = trim($_POST['diagnostico'] ?? '');
        $mecanicoId = (int) ($_POST['mecanico_id'] ?? 0);
        atualizarDiagnosticoOS($id, $diagnostico, $mecanicoId ?: null);
        $sucesso = 'Diagnóstico atualizado.';
    }

    if ($acao === 'status') {
        $novoStatus = $_POST['novo_status'] ?? '';
        $resultado = atualizarStatusOS($id, $novoStatus);
        if ($resultado === true) {
            $sucesso = 'Status atualizado para "' . STATUS_OS[$novoStatus] . '".';
        } else {
            $erro = $resultado;
        }
    }

    if ($acao === 'add_item') {
        $tipo = $_POST['tipo'] ?? 'servico';
        $servicoId = (int) ($_POST['servico_id'] ?? 0);
        $descricao = trim($_POST['descricao'] ?? '');
        $quantidade = trim($_POST['quantidade'] ?? '1');
        $valorUnitario = trim($_POST['valor_unitario'] ?? '0');

        // Se escolheu um serviço do catálogo, usa nome/preço dele como sugestão (caso descrição/valor vazios)
        if ($tipo === 'servico' && $servicoId) {
            $servicoRef = buscarServico($servicoId);
            if ($servicoRef) {
                if ($descricao === '') $descricao = $servicoRef['nome'];
                if ($valorUnitario === '' || $valorUnitario === '0') $valorUnitario = $servicoRef['preco'];
            }
        }

        $resultado = adicionarItemOS($id, $tipo, $servicoId ?: null, $descricao, $quantidade, $valorUnitario);
        if ($resultado === true) {
            $sucesso = 'Item adicionado à OS.';
        } else {
            $erro = $resultado;
        }
    }

    if ($acao === 'remove_item') {
        excluirItemOS((int) ($_POST['item_id'] ?? 0));
        $sucesso = 'Item removido.';
    }

    if ($acao === 'upload_foto' && !empty($_FILES['foto']['name'])) {
        if (!is_dir(UPLOAD_OS_DIR)) {
            mkdir(UPLOAD_OS_DIR, 0777, true);
        }
        $extensoesPermitidas = ['jpg', 'jpeg', 'png', 'webp'];
        $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, $extensoesPermitidas, true)) {
            $erro = 'Formato de imagem não suportado. Use JPG, PNG ou WEBP.';
        } elseif ($_FILES['foto']['size'] > 5 * 1024 * 1024) {
            $erro = 'A imagem deve ter no máximo 5MB.';
        } else {
            $nomeArquivo = 'os' . $id . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
            $destino = UPLOAD_OS_DIR . '/' . $nomeArquivo;
            if (move_uploaded_file($_FILES['foto']['tmp_name'], $destino)) {
                adicionarFotoOS($id, $nomeArquivo);
                $sucesso = 'Foto enviada com sucesso.';
            } else {
                $erro = 'Não foi possível salvar a imagem enviada.';
            }
        }
    }

    if ($acao === 'remove_foto') {
        excluirFotoOS((int) ($_POST['foto_id'] ?? 0));
        $sucesso = 'Foto removida.';
    }
}

$os = buscarOS($id);
if (!$os) { header('Location: ordens_servico.php'); exit; }

$mecanicos = listarMecanicos();
$servicosCatalogo = listarServicos(true);
$podeGerenciar = in_array($_SESSION['user_perfil'], ['administrador', 'recepcionista'], true);
$podeAtualizarTecnico = in_array($_SESSION['user_perfil'], ['administrador', 'recepcionista', 'mecanico'], true);

// Passos da timeline de andamento (status real armazenado só guarda o atual;
// aqui reconstruímos uma linha do tempo simples com o que temos: abertura + status atual)
$ordemStatus = ['agendado', 'em_atendimento', 'concluido'];
$indiceAtual = array_search($os['status'], $ordemStatus, true);
if ($indiceAtual === false) $indiceAtual = -1; // cancelado

$tituloPagina = 'OS #' . str_pad($os['id'], 4, '0', STR_PAD_LEFT);
require __DIR__ . '/includes/header.php';
?>

<?php if (isset($_GET['ok'])): ?><div class="alert alert-sucesso"><i data-lucide="check-circle" class="icon"></i> <?= htmlspecialchars($_GET['ok']) ?></div><?php endif; ?>
<?php if ($sucesso): ?><div class="alert alert-sucesso"><i data-lucide="check-circle" class="icon"></i> <?= htmlspecialchars($sucesso) ?></div><?php endif; ?>
<?php if ($erro): ?><div class="alert alert-erro"><i data-lucide="alert-triangle" class="icon"></i> <?= htmlspecialchars($erro) ?></div><?php endif; ?>

<!-- Cabeçalho da OS -->
<div class="detail-header">
    <div>
        <div class="breadcrumb">
            <a href="ordens_servico.php" style="color:inherit;">Ordens de Serviço</a>
            <i data-lucide="chevron-right"></i>
            <span>OS #<?= str_pad($os['id'], 4, '0', STR_PAD_LEFT) ?></span>
        </div>
        <div class="detail-header__main">
            <div class="detail-header__icon"><i data-lucide="car"></i></div>
            <div>
                <h2><?= htmlspecialchars($os['cliente_nome']) ?> · <?= htmlspecialchars($os['marca'] ?: '') ?> <?= htmlspecialchars($os['veiculo_modelo']) ?></h2>
                <p><?= htmlspecialchars($os['veiculo_placa']) ?> · <?= number_format((int) $os['km_atual'], 0, ',', '.') ?> km · aberta em <?= date('d/m/Y', strtotime($os['criado_em'])) ?></p>
            </div>
        </div>
    </div>

    <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
        <span class="badge badge-status-<?= $os['status'] ?>" style="font-size:11.5px; padding:6px 13px;"><?= STATUS_OS[$os['status']] ?></span>
        <?php if ($os['status'] !== 'cancelado' && $os['status'] !== 'concluido'): ?>
        <form method="POST" action="os_ver.php?id=<?= $os['id'] ?>" style="display:flex; gap:7px; align-items:center;">
            <input type="hidden" name="acao" value="status">
            <select name="novo_status" style="padding:8px 10px; border:1.5px solid var(--cinza-claro); border-radius:8px; font-size:12.5px;">
                <?php foreach (STATUS_OS as $chave => $label): ?>
                    <option value="<?= $chave ?>" <?= $os['status'] === $chave ? 'selected' : '' ?>><?= $label ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn btn-secondary btn-sm"><i data-lucide="refresh-cw" class="icon"></i> Atualizar</button>
        </form>
        <?php endif; ?>
    </div>
</div>

<div class="detail-grid">

    <!-- Coluna principal: abas -->
    <div>
        <div class="tab-card">
            <div class="tab-nav">
                <button type="button" class="active" onclick="mudarAba('diagnostico', this)">Diagnóstico</button>
                <button type="button" onclick="mudarAba('itens', this)">Peças e serviços<?= !empty($os['itens']) ? ' (' . count($os['itens']) . ')' : '' ?></button>
                <button type="button" onclick="mudarAba('fotos', this)">Fotos<?= !empty($os['fotos']) ? ' (' . count($os['fotos']) . ')' : '' ?></button>
            </div>

            <!-- Aba: Diagnóstico -->
            <div class="tab-panel active" id="painel-diagnostico">
                <p class="field-label">Relato do cliente</p>
                <div class="field-value-box" style="margin-bottom:16px;"><?= nl2br(htmlspecialchars($os['problema_relatado'] ?: 'Nenhum relato registrado.')) ?></div>

                <p class="field-label">Diagnóstico técnico</p>
                <?php if ($podeAtualizarTecnico): ?>
                <form method="POST" action="os_ver.php?id=<?= $os['id'] ?>">
                    <input type="hidden" name="acao" value="diagnostico">
                    <div class="form-group">
                        <textarea name="diagnostico" rows="4" placeholder="Descreva o diagnóstico técnico..."><?= htmlspecialchars($os['diagnostico'] ?? '') ?></textarea>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="mecanico_id">Mecânico responsável</label>
                            <select id="mecanico_id" name="mecanico_id">
                                <option value="">A definir</option>
                                <?php foreach ($mecanicos as $m): ?>
                                    <option value="<?= $m['id'] ?>" <?= $os['mecanico_id'] == $m['id'] ? 'selected' : '' ?>><?= htmlspecialchars($m['nome']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div style="display:flex; align-items:end;">
                            <button type="submit" class="btn btn-secondary btn-sm" style="width:auto; padding:10px 20px;"><i data-lucide="save" class="icon"></i> Salvar diagnóstico</button>
                        </div>
                    </div>
                </form>
                <?php else: ?>
                    <div class="field-value-box"><?= nl2br(htmlspecialchars($os['diagnostico'] ?: 'Nenhum diagnóstico registrado ainda.')) ?></div>
                <?php endif; ?>
            </div>

            <!-- Aba: Itens (peças e serviços) -->
            <div class="tab-panel" id="painel-itens">
                <?php if (empty($os['itens'])): ?>
                    <div class="empty-state">Nenhum item registrado ainda.</div>
                <?php else: ?>
                <table class="os-itens-table">
                    <thead><tr><th>Tipo</th><th>Descrição</th><th>Qtd</th><th>Valor unit.</th><th>Subtotal</th><th></th></tr></thead>
                    <tbody>
                        <?php foreach ($os['itens'] as $item): ?>
                            <tr>
                                <td><span class="badge <?= $item['tipo'] === 'peca' ? 'badge-soon' : 'badge-ok' ?>"><?= $item['tipo'] === 'peca' ? 'Peça' : 'Serviço' ?></span></td>
                                <td><?= htmlspecialchars($item['descricao']) ?></td>
                                <td><?= (int) $item['quantidade'] ?></td>
                                <td>R$ <?= number_format((float) $item['valor_unitario'], 2, ',', '.') ?></td>
                                <td>R$ <?= number_format($item['quantidade'] * $item['valor_unitario'], 2, ',', '.') ?></td>
                                <td>
                                    <form method="POST" action="os_ver.php?id=<?= $os['id'] ?>" onsubmit="return confirm('Remover este item?');">
                                        <input type="hidden" name="acao" value="remove_item">
                                        <input type="hidden" name="item_id" value="<?= $item['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-red"><i data-lucide="trash-2" class="icon"></i></button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <tr class="os-total-row">
                            <td colspan="4" style="text-align:right;">Total da OS</td>
                            <td colspan="2">R$ <?= number_format($os['total'], 2, ',', '.') ?></td>
                        </tr>
                    </tbody>
                </table>
                <?php endif; ?>

                <?php if ($podeAtualizarTecnico): ?>
                <form method="POST" action="os_ver.php?id=<?= $os['id'] ?>" style="margin-top:18px; padding-top:16px; border-top:1px solid #eee;">
                    <input type="hidden" name="acao" value="add_item">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="tipo">Tipo</label>
                            <select id="tipo" name="tipo" onchange="document.getElementById('grupo-servico-catalogo').style.display = this.value==='servico' ? 'block' : 'none';">
                                <option value="servico">Serviço (do catálogo)</option>
                                <option value="peca">Peça (avulsa)</option>
                            </select>
                        </div>
                        <div class="form-group" id="grupo-servico-catalogo">
                            <label for="servico_id">Serviço do catálogo (opcional)</label>
                            <select id="servico_id" name="servico_id">
                                <option value="">Nenhum / descrição livre</option>
                                <?php foreach ($servicosCatalogo as $s): ?>
                                    <option value="<?= $s['id'] ?>">
                                        <?= htmlspecialchars($s['nome']) ?> — R$ <?= number_format((float) $s['preco'], 2, ',', '.') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-row-3">
                        <div class="form-group">
                            <label for="descricao">Descrição *</label>
                            <input type="text" id="descricao" name="descricao" placeholder="Ex: Pastilha de freio dianteira">
                        </div>
                        <div class="form-group">
                            <label for="quantidade">Quantidade</label>
                            <input type="number" id="quantidade" name="quantidade" value="1" min="1">
                        </div>
                        <div class="form-group">
                            <label for="valor_unitario">Valor unitário (R$) *</label>
                            <input type="text" id="valor_unitario" name="valor_unitario" placeholder="0,00">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-success btn-sm" style="padding:9px 18px;"><i data-lucide="plus" class="icon"></i> Adicionar item</button>
                </form>
                <?php endif; ?>
            </div>

            <!-- Aba: Fotos -->
            <div class="tab-panel" id="painel-fotos">
                <?php if (empty($os['fotos'])): ?>
                    <div class="empty-state">Nenhuma foto enviada ainda.</div>
                <?php else: ?>
                    <div class="foto-grid">
                        <?php foreach ($os['fotos'] as $foto): ?>
                            <div class="foto-item">
                                <img src="<?= UPLOAD_OS_URL ?>/<?= htmlspecialchars($foto['caminho_arquivo']) ?>" alt="Foto da OS">
                                <?php if ($podeAtualizarTecnico): ?>
                                <form method="POST" action="os_ver.php?id=<?= $os['id'] ?>" onsubmit="return confirm('Remover esta foto?');">
                                    <input type="hidden" name="acao" value="remove_foto">
                                    <input type="hidden" name="foto_id" value="<?= $foto['id'] ?>">
                                    <button type="submit" class="foto-remove"><i data-lucide="x" style="width:12px;height:12px;"></i></button>
                                </form>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php if ($podeAtualizarTecnico): ?>
                <form method="POST" action="os_ver.php?id=<?= $os['id'] ?>" enctype="multipart/form-data" style="margin-top:16px;">
                    <input type="hidden" name="acao" value="upload_foto">
                    <label class="upload-box" for="foto-input" style="display:block;">
                        <i data-lucide="upload" class="icon"></i> Clique para escolher uma foto (JPG, PNG ou WEBP, até 5MB)
                        <input type="file" id="foto-input" name="foto" accept="image/jpeg,image/png,image/webp" style="display:none;" onchange="this.form.submit()">
                    </label>
                </form>
                <?php endif; ?>
            </div>
        </div>

        <a href="ordens_servico.php" class="btn btn-outline btn-sm" style="width:fit-content; padding:8px 18px;">← Voltar para Ordens de Serviço</a>
    </div>

    <!-- Coluna lateral: resumo -->
    <div>
        <div class="summary-card">
            <p class="summary-label">Total da OS</p>
            <p class="summary-value">R$ <?= number_format($os['total'], 2, ',', '.') ?></p>
            <div class="summary-actions">
                <span class="btn btn-outline btn-sm" style="background:rgba(255,255,255,0.06); border-color:#35496B; color:#8FA3BF; cursor:not-allowed;" title="Chega na Sprint 6 — Orçamento e Aprovação">
                    <i data-lucide="file-text" class="icon"></i> Gerar orçamento
                </span>
                <button type="button" class="btn btn-outline btn-sm" style="background:transparent; border-color:#35496B; color:#C7D2E2; width:auto; padding:8px 12px;" onclick="window.print()" title="Imprimir OS">
                    <i data-lucide="printer" class="icon"></i>
                </button>
            </div>
        </div>

        <div class="card" style="margin-bottom:14px;">
            <h3 style="margin-bottom:14px;">Andamento</h3>
            <div class="timeline">
                <div class="timeline-item">
                    <div class="dot-col"><span class="dot done"></span><span class="line"></span></div>
                    <div class="content">
                        <p class="title">OS aberta</p>
                        <p class="meta"><?= date('d/m/Y \à\s H:i', strtotime($os['criado_em'])) ?></p>
                    </div>
                </div>

                <?php if ($os['status'] === 'cancelado'): ?>
                    <div class="timeline-item">
                        <div class="dot-col"><span class="dot" style="background:var(--vermelho-erro);"></span></div>
                        <div class="content">
                            <p class="title">Cancelada</p>
                            <p class="meta">Atualizado em <?= date('d/m/Y \à\s H:i', strtotime($os['atualizado_em'])) ?></p>
                        </div>
                    </div>
                <?php else: ?>
                    <?php foreach ($ordemStatus as $i => $chaveStatus): ?>
                        <?php
                            $classeItem = '';
                            if ($i < $indiceAtual) $dotClasse = 'done';
                            elseif ($i === $indiceAtual) $dotClasse = 'current';
                            else { $dotClasse = ''; $classeItem = 'futuro'; }
                        ?>
                        <div class="timeline-item <?= $classeItem ?>">
                            <div class="dot-col"><span class="dot <?= $dotClasse ?>"></span><span class="line"></span></div>
                            <div class="content">
                                <p class="title"><?= STATUS_OS[$chaveStatus] ?></p>
                                <?php if ($i === $indiceAtual): ?>
                                    <p class="meta"><?= htmlspecialchars($os['mecanico_nome'] ?: 'Sem mecânico definido') ?> · <?= date('d/m/Y \à\s H:i', strtotime($os['atualizado_em'])) ?></p>
                                <?php else: ?>
                                    <p class="meta">&nbsp;</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <div class="card">
            <h3 style="margin-bottom:10px;">Cliente</h3>
            <p style="font-size:12.5px; color:var(--cinza-escuro); margin:0 0 4px;"><?= htmlspecialchars($os['cliente_nome']) ?></p>
            <p style="font-size:12px; color:var(--cinza-medio); margin:0;"><?= htmlspecialchars($os['cliente_telefone']) ?></p>
        </div>
    </div>

</div>

<script>
function mudarAba(nome, btn) {
    document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
    document.querySelectorAll('.tab-nav button').forEach(b => b.classList.remove('active'));
    document.getElementById('painel-' + nome).classList.add('active');
    btn.classList.add('active');
}
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>
