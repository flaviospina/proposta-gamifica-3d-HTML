<?php
/**
 * inc/bootstrap.php — carrega a configuração e as bibliotecas internas.
 * Toda página e todo endpoint começam com: require __DIR__ . '/inc/bootstrap.php';
 */
declare(strict_types=1);

define('APP_RAIZ', dirname(__DIR__));

require_once APP_RAIZ . '/config.php';

mb_internal_encoding('UTF-8');

if (defined('APP_ENV') && APP_ENV === 'dev') {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    error_reporting(E_ALL);
    ini_set('log_errors', '1');
}

require_once __DIR__ . '/funcoes.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/sessao.php';
require_once __DIR__ . '/observacoes.php';
require_once __DIR__ . '/render.php';
