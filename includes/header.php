<?php
/**
 * Inclui no topo de toda página interna (pós-login).
 */
$tituloPagina = $tituloPagina ?? 'Painel';
$paginaAtual = basename($_SERVER['PHP_SELF']);
$perfilAtual = $_SESSION['user_perfil'];

$iniciais = '';
foreach (explode(' ', $_SESSION['user_nome']) as $parte) {
    $iniciais .= mb_strtoupper(mb_substr($parte, 0, 1));
}
$iniciais = mb_substr($iniciais, 0, 2);

$podeGerenciarOficina = in_array($perfilAtual, ['administrador', 'recepcionista'], true);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($tituloPagina) ?> — AutoGest</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="assets/js/chart.min.js"></script>
    <script src="assets/js/lucide.min.js"></script>
</head>
<body>
<div class="app-shell">

    <aside class="sidebar">
        <div class="sidebar__brand">
            <img src="assets/img/logo-icon.png" alt="AutoGest">
            <div>
                <strong>Auto<span>Gest</span></strong>
                <small>GESTÃO MECÂNICA</small>
            </div>
        </div>
        <nav class="sidebar__nav">
            <a href="dashboard.php" class="<?= $paginaAtual === 'dashboard.php' ? 'active' : '' ?>"><i data-lucide="layout-dashboard"></i> Início</a>

            <?php if ($podeGerenciarOficina): ?>
            <a href="clientes.php" class="<?= in_array($paginaAtual, ['clientes.php','cliente_form.php','cliente_ver.php']) ? 'active' : '' ?>"><i data-lucide="users"></i> Clientes</a>
            <a href="veiculos.php" class="<?= in_array($paginaAtual, ['veiculos.php','veiculo_form.php','veiculo_ver.php']) ? 'active' : '' ?>"><i data-lucide="car"></i> Veículos</a>
            <a href="servicos.php" class="<?= in_array($paginaAtual, ['servicos.php','servico_form.php']) ? 'active' : '' ?>"><i data-lucide="wrench"></i> Serviços</a>
            <a href="agendamentos.php" class="<?= in_array($paginaAtual, ['agendamentos.php','agendamento_form.php']) ? 'active' : '' ?>"><i data-lucide="calendar-check"></i> Agendamentos</a>
            <?php endif; ?>

            <a href="ordens_servico.php" class="<?= in_array($paginaAtual, ['ordens_servico.php','os_form.php','os_ver.php']) ? 'active' : '' ?>"><i data-lucide="clipboard-list"></i> Ordens de Serviço</a>

            <?php if ($perfilAtual === 'administrador'): ?>
            <a href="usuarios.php" class="<?= $paginaAtual === 'usuarios.php' ? 'active' : '' ?>"><i data-lucide="user-cog"></i> Usuários</a>
            <span class="nav-disabled"><i data-lucide="dollar-sign"></i> Financeiro</span>
            <span class="nav-disabled"><i data-lucide="bar-chart-3"></i> Relatórios</span>
            <?php endif; ?>

            <a href="configuracoes.php" class="<?= $paginaAtual === 'configuracoes.php' ? 'active' : '' ?>"><i data-lucide="settings"></i> Configurações</a>
        </nav>
        <div class="sidebar__footer">
            <a href="logout.php" class="btn btn-outline" style="width:100%; text-align:center; background:transparent; border-color:rgba(255,255,255,0.25); color:#eee;">
                <i data-lucide="log-out" class="icon"></i> Sair
            </a>
        </div>
    </aside>

    <div class="main">
        <header class="topbar">
            <h1><?= htmlspecialchars($tituloPagina) ?></h1>
            <div class="user-pill">
                <div class="avatar"><?= htmlspecialchars($iniciais ?: '?') ?></div>
                <div class="info">
                    <strong><?= htmlspecialchars($_SESSION['user_nome']) ?></strong>
                    <span><?= htmlspecialchars(nomePerfil($perfilAtual)) ?></span>
                </div>
            </div>
        </header>
        <main class="content">
