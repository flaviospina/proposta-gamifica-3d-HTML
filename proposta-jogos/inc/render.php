<?php
/**
 * inc/render.php — trechos de HTML reutilizados pelo index.php
 * (selo de parecer, item do histórico e bloco de observações).
 * O JavaScript (js/observacoes.js) gera a mesma estrutura ao atualizar sem recarregar.
 */
declare(strict_types=1);

/** Selo com o último parecer do jogo (ou "Sem parecer ainda"). */
function selo_html(?string $parecer, string $slug): string
{
    $classe = $parecer ? 'selo--' . e($parecer) : 'selo--vazio';
    $rotulo = $parecer ? parecer_rotulo($parecer) : 'Sem parecer ainda';
    return '<span class="selo ' . $classe . '" data-selo="' . e($slug) . '">' . e($rotulo) . '</span>';
}

/** Um item do histórico. */
function obs_item_html(array $o): string
{
    $selo = $o['parecer']
        ? '<span class="selo selo--' . e($o['parecer']) . '">' . e($o['parecer_rotulo']) . '</span>'
        : '<span class="selo selo--vazio">Comentário</span>';
    $editada = $o['atualizado_em'] ? '<span class="obs-item__editada" title="Editada em ' . e($o['atualizado_em']) . '">editada</span>' : '';
    $acoes = $o['propria']
        ? '<div class="obs-item__acoes"><button type="button" class="editar">Editar</button><button type="button" class="excluir">Excluir</button></div>'
        : '';
    $texto = $o['texto'] !== '' ? '<p class="obs-item__texto">' . e($o['texto']) . '</p>' : '';
    return '<article class="obs-item" data-id="' . (int) $o['id'] . '" data-parecer="' . e((string) $o['parecer']) . '">'
        . '<div class="obs-item__meta"><span class="obs-item__autor">' . e($o['autor']) . '</span>' . $selo
        . '<span class="obs-item__data">' . e($o['criado_em']) . '</span>' . $editada . '</div>'
        . $texto . $acoes . '</article>';
}

/** Lista do histórico (ou aviso de vazio). */
function obs_historico_html(array $observacoes): string
{
    if (!$observacoes) {
        return '<p class="obs__vazio">Nenhuma observação ainda. O histórico aparecerá aqui, com autor, data/hora e parecer.</p>';
    }
    return implode('', array_map('obs_item_html', $observacoes));
}

/**
 * Bloco completo de observações de um jogo.
 * @param array  $jogo        linha de pj_jogos
 * @param array  $observacoes lista formatada (mais recentes primeiro)
 * @param string $titulo      título do bloco
 * @param string $placeholder texto do campo
 */
function bloco_observacoes_html(array $jogo, array $observacoes, string $titulo = 'Observações do sócio', string $placeholder = 'Escreva aqui a sua observação sobre este jogo…'): string
{
    $slug = e($jogo['slug']);
    $h = '<section class="obs" data-jogo="' . $slug . '" aria-label="' . e($titulo) . ' — ' . e($jogo['nome']) . '">';
    $h .= '<div class="obs__cab"><h4><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 12a8 8 0 0 1-8 8H6l-3 3V12a8 8 0 0 1 8-8h2a8 8 0 0 1 8 8Z"/></svg>' . e($titulo) . '</h4>';
    $h .= '<span class="obs__contagem" data-contagem="' . $slug . '">' . count($observacoes) . ' ' . (count($observacoes) === 1 ? 'observação' : 'observações') . '</span></div>';
    $h .= '<form class="obs__form" method="post" action="api/salvar.php" data-jogo="' . $slug . '" novalidate>';
    $h .= '<input type="hidden" name="id" value="">';
    $h .= '<fieldset class="parecer"><legend>Parecer</legend>';
    foreach (PARECERES as $valor => $rotulo) {
        $h .= '<label data-v="' . e($valor) . '"><input type="radio" name="parecer" value="' . e($valor) . '">' . e($rotulo) . '</label>';
    }
    $h .= '</fieldset>';
    $h .= '<textarea name="texto" maxlength="' . (int) OBS_TEXTO_MAX . '" placeholder="' . e($placeholder) . '" aria-label="Observação"></textarea>';
    $h .= '<div class="obs__acoes"><button class="btn btn--primario" type="submit">Salvar observação</button>';
    $h .= '<button class="btn btn--contorno obs__cancelar" type="button" hidden>Cancelar edição</button>';
    $h .= '<span class="obs__status" role="status" aria-live="polite"></span></div></form>';
    $h .= '<div class="obs__historico" data-historico="' . $slug . '">' . obs_historico_html($observacoes) . '</div>';
    $h .= '</section>';
    return $h;
}

/** Uma linha da tabela do painel resumo. */
function resumo_linha_html(array $r): string
{
    $selo = $r['ultimo_parecer']
        ? '<span class="selo selo--' . e($r['ultimo_parecer']) . '">' . e($r['ultimo_parecer_rotulo']) . '</span>'
        : '<span class="selo selo--vazio">Sem parecer</span>';
    return '<tr data-slug="' . e($r['slug']) . '">'
        . '<th scope="row">' . e($r['nome']) . '</th>'
        . '<td>' . $selo . '</td>'
        . '<td class="num">' . (int) $r['total'] . '</td>'
        . '<td>' . ($r['ultima_em'] ? e($r['ultima_em']) : '<span class="texto-mudo">—</span>') . '</td>'
        . '<td class="acao"><a href="#' . e($r['slug']) . '" class="resumo__ir">Ir à seção</a></td>'
        . '</tr>';
}

/** Tabela do painel resumo (usada no diálogo e na versão de impressão). */
function resumo_tabela_html(array $resumo, string $id = ''): string
{
    $total = array_sum(array_column($resumo, 'total'));
    $h = '<table class="resumo__tabela"' . ($id ? ' id="' . e($id) . '"' : '') . '>';
    $h .= '<thead><tr><th scope="col">Jogo</th><th scope="col">Último parecer</th><th scope="col" class="num">Observações</th><th scope="col">Última</th><th scope="col"></th></tr></thead>';
    $h .= '<tbody data-resumo-linhas>' . implode('', array_map('resumo_linha_html', $resumo)) . '</tbody>';
    $h .= '<tfoot><tr><td colspan="2">Total</td><td class="num" data-resumo-total>' . (int) $total . '</td><td colspan="2"></td></tr></tfoot>';
    return $h . '</table>';
}
