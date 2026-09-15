<?php
require_once __DIR__ . '/includes/auth.php';
protegerPorPerfil(['administrador']);

$erro = '';
$sucesso = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'criar') {
    $nome    = trim($_POST['nome'] ?? '');
    $usuario = trim($_POST['usuario'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $senha   = $_POST['senha'] ?? '';
    $perfil  = $_POST['perfil'] ?? '';
    $especialidade = trim($_POST['especialidade'] ?? '');

    $resultado = criarUsuario($nome, $usuario, $email, $senha, $perfil, $especialidade);

    if ($resultado === true) {
        $sucesso = 'Usuário "' . htmlspecialchars($nome) . '" cadastrado com sucesso!';
    } else {
        $erro = $resultado;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'alternar_status') {
    if (!alternarStatusUsuario((int) ($_POST['id'] ?? 0))) {
        $erro = 'Não é possível desativar o seu próprio usuário.';
    }
}

$usuarios = listarUsuarios();
$tituloPagina = 'Usuários e Perfis de Acesso';
require __DIR__ . '/includes/header.php';
?>

<div class="cards-grid" style="grid-template-columns: 1fr; gap: 24px;">

    <div class="card">
        <div class="card-icon-wrap laranja"><i data-lucide="user-plus"></i></div>
        <h3>Novo usuário</h3>
        <p>Cadastre Administradores, Recepcionistas ou Mecânicos. Cada perfil enxerga apenas as telas relevantes à sua função.</p>

        <?php if ($erro): ?><div class="alert alert-erro"><i data-lucide="alert-triangle" class="icon"></i> <?= htmlspecialchars($erro) ?></div><?php endif; ?>
        <?php if ($sucesso): ?><div class="alert alert-sucesso"><i data-lucide="check-circle" class="icon"></i> <?= $sucesso ?></div><?php endif; ?>

        <form method="POST" action="usuarios.php" style="max-width:520px;">
            <input type="hidden" name="acao" value="criar">

            <div class="form-row">
                <div class="form-group">
                    <label for="nome">Nome completo</label>
                    <input type="text" id="nome" name="nome" required value="<?= htmlspecialchars($_POST['nome'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="usuario">Nome de usuário</label>
                    <input type="text" id="usuario" name="usuario" required placeholder="ex: joao" value="<?= htmlspecialchars($_POST['usuario'] ?? '') ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="email">E-mail (usado na recuperação de senha)</label>
                    <input type="email" id="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="senha">Senha (mín. 6 caracteres)</label>
                    <input type="password" id="senha" name="senha" required minlength="6">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="perfil">Perfil de acesso</label>
                    <select id="perfil" name="perfil" required onchange="document.getElementById('grupo-especialidade').style.display = this.value==='mecanico' ? 'block' : 'none';">
                        <option value="">Selecione...</option>
                        <option value="administrador">Administrador</option>
                        <option value="recepcionista">Recepcionista</option>
                        <option value="mecanico">Mecânico</option>
                    </select>
                </div>
                <div class="form-group" id="grupo-especialidade" style="display:none;">
                    <label for="especialidade">Especialidade (mecânico)</label>
                    <input type="text" id="especialidade" name="especialidade" placeholder="Ex: Motor, Elétrica, Suspensão">
                </div>
            </div>

            <button type="submit" class="btn btn-primary" style="width:auto; padding:11px 26px;"><i data-lucide="user-plus" class="icon"></i> Cadastrar usuário</button>
        </form>
    </div>

    <div class="card">
        <div class="card-icon-wrap"><i data-lucide="list"></i></div>
        <h3>Usuários cadastrados (<?= count($usuarios) ?>)</h3>
        <div style="overflow-x:auto; margin-top:12px;">
            <table class="data-table">
                <thead><tr><th>Nome</th><th>Usuário</th><th>Perfil</th><th>Especialidade</th><th>Status</th><th>Ações</th></tr></thead>
                <tbody>
                    <?php foreach ($usuarios as $u): ?>
                        <tr>
                            <td><?= htmlspecialchars($u['nome']) ?></td>
                            <td>@<?= htmlspecialchars($u['usuario']) ?></td>
                            <td><?= htmlspecialchars(nomePerfil($u['perfil'])) ?></td>
                            <td><?= htmlspecialchars($u['especialidade'] ?: '—') ?></td>
                            <td>
                                <?php if ($u['ativo']): ?><span class="badge badge-ok">Ativo</span>
                                <?php else: ?><span class="badge" style="background:#fdecea; color:#EF4444;">Inativo</span><?php endif; ?>
                            </td>
                            <td>
                                <?php if ((int) $u['id'] !== (int) $_SESSION['user_id']): ?>
                                    <form method="POST" action="usuarios.php" style="display:inline;">
                                        <input type="hidden" name="acao" value="alternar_status">
                                        <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-gray"><i data-lucide="<?= $u['ativo'] ? 'user-x' : 'user-check' ?>" class="icon"></i> <?= $u['ativo'] ? 'Desativar' : 'Ativar' ?></button>
                                    </form>
                                <?php else: ?>
                                    <span style="color:#999; font-size:12px;">Você</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
