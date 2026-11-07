<?php
/**
 * api/resumo.php — resumo de todos os jogos (GET): último parecer, total e data da última observação.
 * Saída: { ok, data: { jogos: [ {slug, nome, ordem, total, ultima_em, ultimo_parecer, ultimo_parecer_rotulo} ] }, mensagem }
 */
declare(strict_types=1);

require_once dirname(__DIR__) . '/inc/api.php';

json_response(true, ['jogos' => resumo_jogos()], '');
