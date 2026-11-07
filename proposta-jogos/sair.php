<?php
/**
 * sair.php — encerra a sessão e volta para a tela de acesso.
 */
declare(strict_types=1);

require_once __DIR__ . '/inc/bootstrap.php';

logout();
redirecionar(app_base() . '/login.php');
