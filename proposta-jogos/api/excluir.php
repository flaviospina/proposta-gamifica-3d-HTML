<?php
/**
 * api/excluir.php — exclusão lógica de uma observação do próprio autor (POST JSON).
 * Entrada:  { id: 12 }
 * Saída:    { ok, data: { jogo, observacoes[], total, ultimo_parecer, ultimo_parecer_rotulo }, mensagem }
 */
declare(strict_types=1);

require_once dirname(__DIR__) . '/inc/api.php';

$dados = api_exigir_post();

$id  = (int) ($dados['id'] ?? 0);
$obs = $id > 0 ? observacao_por_id($id) : null;
if ($obs === null) {
    json_response(false, null, 'Observação não encontrada (talvez já tenha sido excluída).', 404);
}
if (!observacao_e_do_usuario($obs, $API_USUARIO)) {
    json_response(false, null, 'Você só pode excluir as suas próprias observações.', 403);
}

observacao_excluir($id);

$jogo = jogo_por_slug((string) $obs['slug']);
api_resposta_jogo($jogo, $API_USUARIO, 'Observação excluída.');
