<?php
require_once __DIR__ . '/includes/auth.php';
protegerPorPerfil(['administrador', 'recepcionista']);
require_once __DIR__ . '/includes/repo.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int) ($_POST['id'] ?? 0);
    if ($id) {
        cancelarAgendamento($id);
    }
}

header('Location: agendamentos.php?ok=' . urlencode('Agendamento cancelado.'));
exit;
