<?php
/**
 * Configurações gerais do sistema.
 */

// Driver do banco: 'sqlite' (padrão — não exige instalar nada) ou 'mysql' (XAMPP)
define('DB_DRIVER', 'sqlite');

// Configuração para SQLite (usado quando DB_DRIVER = 'sqlite')
// O arquivo abaixo é criado AUTOMATICAMENTE (com as tabelas e o usuário de teste)
// na primeira vez que o sistema for acessado — não precisa rodar nenhum comando.
define('DB_SQLITE_PATH', __DIR__ . '/../database/autogest.sqlite');

// Configuração para MySQL (usado apenas se DB_DRIVER = 'mysql', ex: rodando no XAMPP)
define('DB_HOST', 'localhost');
define('DB_NAME', 'autogest');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Nome do sistema (usado nas telas)
define('APP_NAME', 'AutoGest - Sistema de Gestão Mecânica');

// Pasta de upload de fotos das Ordens de Serviço
define('UPLOAD_OS_DIR', __DIR__ . '/../uploads/os');
define('UPLOAD_OS_URL', 'uploads/os');

// Fuso horário
date_default_timezone_set('America/Sao_Paulo');
