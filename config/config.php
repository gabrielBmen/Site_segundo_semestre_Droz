<?php

// =========================================================
// CONSTANTES DA APLICAÇÃO
// =========================================================

require_once __DIR__ . '/constantes.php';


// =========================================================
// CONFIGURAÇÕES DO SITE
// =========================================================

define('BASE_URL', 'http://drozrobotica.local:8080');

define('ASSETS_URL', BASE_URL . '/assets');


// =========================================================
// CONFIGURAÇÕES DO BANCO DE DADOS
// =========================================================

define('DB_HOST', 'localhost');

define('DB_PORT', '3306');

define('DB_NAME', 'droz_robotica');

define('DB_USER', 'root');

define('DB_PASSWORD', '');

define('DB_CHARSET', 'utf8mb4');