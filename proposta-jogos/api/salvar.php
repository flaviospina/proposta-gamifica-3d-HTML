<?php
/**
 * api/salvar.php — cria uma observação (POST JSON).
 * Entrada:  { jogo: "slug", parecer: "aprovado|ajustes|rever|descartar|", texto: "..." }
 * Saída:    { ok, data: { jogo, observacoes[], total, ultimo_parecer, ultimo_parecer_rotulo, id }, mensagem }
 */
declare(strict_types=1);

require_once dirname(__DIR__) . '/inc/api.php';

$dados = api_exigir_post();

$slug    = trim((string) ($dados['jogo'] ?? ''));
$parecer = trim((string) ($dados['parecer'] ?? '')) ?: null;
$texto   = trim((string) ($dados['texto'] ?? ''));

$jogo = $slug !== '' ? jogo_por_slug($slug) : null;
if ($jogo === null) {
    json_response(false, null, 'Jogo não encontrado.', 404);
}

$erro = observacao_validar($parecer, $texto);
if ($erro !== '') {
    json_response(false, null, $erro, 422);
}

$id = observacao_inserir((int) $jogo['id'], $API_USUARIO, $parecer, $texto);

api_resposta_jogo($jogo, $API_USUARIO, 'Salvo ✓', ['id' => $id]);
