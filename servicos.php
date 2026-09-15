<?php
require_once __DIR__ . '/includes/auth.php';
protegerPorPerfil(['administrador', 'recepcionista']);
require_once __DIR__ . '/includes/repo.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'alternar_status') {
    alternarStatusServico((int) ($_POST['id'] ?? 0));
    header('Location: servicos.php');
    exit;
}

$servicos = listarServicos();
$tituloPagina = 'Serviços';
require __DIR__ . '/includes/header.php';
?>

<?php if (isset($_GET['ok'])): ?><div class="alert alert-sucesso"><i data-lucide="check-circle" class="icon"></i> <?= htmlspecialchars($_GET['ok']) ?></div><?php endif; ?>

<div class="alert alert-info">
    <i data-lucide="info" class="icon"></i>
    Catálogo simplificado para permitir Agendamentos e Ordens de Serviço agora. Categoria, tempo estimado e mecânicos especializados chegam na <strong>Sprint 7</strong>.
</div>

<div class="toolbar">
    <p style="margin:0; color:#555; font-size:13.5px;">Serviços oferecidos pela oficina.</p>
    <a href="servico_form.php" class="btn btn-primary" style="width:auto; padding:10px 20px;"><i data-lucide="plus" class="icon"></i> Novo Serviço</a>
</div>

<div class="card">
    <?php if (empty($servicos)): ?>
        <div class="empty-state">Nenhum serviço cadastrado ainda.</div>
    <?php else: ?>
        <div style="overflow-x:auto;">
            <table class="data-table">
                <thead><tr><th>Serviço</th><th>Descrição</th><th>Preço</th><th>Status</th><th>Ações</th></tr></thead>
                <tbody>
                    <?php foreach ($servicos as $s): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($s['nome']) ?></strong></td>
                            <td><?= htmlspecialchars($s['descricao'] ?: '—') ?></td>
                            <td>R$ <?= number_format((float) $s['preco'], 2, ',', '.') ?></td>
                            <td>
                                <?php if ($s['ativo']): ?><span class="badge badge-ok">Ativo</span>
                                <?php else: ?><span class="badge" style="background:#fdecea; color:#EF4444;">Inativo</span><?php endif; ?>
                            </td>
                            <td>
                                <div class="actions-cell">
                                    <a href="servico_form.php?id=<?= $s['id'] ?>" class="btn btn-sm btn-yellow"><i data-lucide="edit-2" class="icon"></i> Editar</a>
                                    <form method="POST" action="servicos.php" style="display:inline;">
                                        <input type="hidden" name="acao" value="alternar_status">
                                        <input type="hidden" name="id" value="<?= $s['id'] ?>">
                                        <button type="submit" class="btn btn-sm <?= $s['ativo'] ? 'btn-red' : 'btn-green' ?>"><i data-lucide="<?= $s['ativo'] ? 'eye-off' : 'eye' ?>" class="icon"></i> <?= $s['ativo'] ? 'Desativar' : 'Ativar' ?></button>
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
