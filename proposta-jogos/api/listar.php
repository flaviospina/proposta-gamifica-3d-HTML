<?php
/**
 * api/listar.php — lista as observações de um jogo (GET ?jogo=slug).
 * Saída: { ok, data: { jogo, observacoes[], total, ultimo_parecer, ultimo_parecer_rotulo }, mensagem }
 */
declare(strict_types=1);

require_once dirname(__DIR__) . '/inc/api.php';

$slug = trim((string) ($_GET['jogo'] ?? ''));
$jogo = $slug !== '' ? jogo_por_slug($slug) : null;
if ($jogo === null) {
    json_response(false, null, 'Jogo não encontrado.', 404);
}

api_resposta_jogo($jogo, $API_USUARIO, '');
