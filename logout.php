<?php
require_once __DIR__ . '/includes/auth.php';
encerrarSessao();
header('Location: login.php?logout=1');
exit;
