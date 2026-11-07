<?php
/**
 * inc/observacoes.php — acesso às tabelas pj_jogos e pj_observacoes.
 * Todas as consultas usam prepared statements.
 */
declare(strict_types=1);

/** Todos os jogos, na ordem de exibição. */
function jogos_todos(): array
{
    return db()->query('SELECT id, slug, nome, ordem FROM pj_jogos ORDER BY ordem, id')->fetchAll();
}

/** Um jogo pelo slug, ou null. */
function jogo_por_slug(string $slug): ?array
{
    $st = db()->prepare('SELECT id, slug, nome, ordem FROM pj_jogos WHERE slug = ? LIMIT 1');
    $st->execute([$slug]);
    $j = $st->fetch();
    return $j ?: null;
}

/**
 * Observações ativas (não excluídas) de um jogo, mais recentes primeiro.
 * @return array<int, array> linhas prontas para exibição (ver observacao_formatar)
 */
function observacoes_do_jogo(int $jogoId, array $usuario): array
{
    $st = db()->prepare(
        'SELECT id, jogo_id, usuario_id, autor, parecer, texto, criado_em, atualizado_em
           FROM pj_observacoes
          WHERE jogo_id = ? AND excluido_em IS NULL
          ORDER BY criado_em DESC, id DESC'
    );
    $st->execute([$jogoId]);
    return array_map(fn($o) => observacao_formatar($o, $usuario), $st->fetchAll());
}

/**
 * Todas as observações ativas, agrupadas por slug do jogo (usado pelo index.php
 * para montar a página com uma única consulta).
 * @return array<string, array<int, array>>
 */
function observacoes_por_jogo(array $usuario): array
{
    $st = db()->query(
        'SELECT o.id, o.jogo_id, o.usuario_id, j.slug, o.autor, o.parecer, o.texto, o.criado_em, o.atualizado_em
           FROM pj_observacoes o
           JOIN pj_jogos j ON j.id = o.jogo_id
          WHERE o.excluido_em IS NULL
          ORDER BY o.criado_em DESC, o.id DESC'
    );
    $grupos = [];
    foreach ($st->fetchAll() as $o) {
        $grupos[$o['slug']][] = observacao_formatar($o, $usuario);
    }
    return $grupos;
}

/** Uma observação ativa pelo id, ou null. */
function observacao_por_id(int $id): ?array
{
    $st = db()->prepare(
        'SELECT o.id, o.jogo_id, o.usuario_id, j.slug, o.autor, o.parecer, o.texto, o.criado_em, o.atualizado_em
           FROM pj_observacoes o JOIN pj_jogos j ON j.id = o.jogo_id
          WHERE o.id = ? AND o.excluido_em IS NULL LIMIT 1'
    );
    $st->execute([$id]);
    $o = $st->fetch();
    return $o ?: null;
}

/** Insere e devolve o id. */
function observacao_inserir(int $jogoId, array $usuario, ?string $parecer, string $texto): int
{
    $st = db()->prepare('INSERT INTO pj_observacoes (jogo_id, usuario_id, autor, parecer, texto) VALUES (?, ?, ?, ?, ?)');
    $st->execute([$jogoId, (int) $usuario['id'], (string) $usuario['nome'], $parecer ?: null, $texto]);
    return (int) db()->lastInsertId();
}

/** Atualiza parecer e texto (só o chamador garante que é o autor). */
function observacao_atualizar(int $id, ?string $parecer, string $texto): void
{
    $st = db()->prepare('UPDATE pj_observacoes SET parecer = ?, texto = ?, atualizado_em = NOW() WHERE id = ? AND excluido_em IS NULL');
    $st->execute([$parecer ?: null, $texto, $id]);
}

/** Exclusão lógica. */
function observacao_excluir(int $id): void
{
    $st = db()->prepare('UPDATE pj_observacoes SET excluido_em = NOW() WHERE id = ? AND excluido_em IS NULL');
    $st->execute([$id]);
}

/**
 * Verdadeiro se a observação pertence ao usuário: pela conta (usuario_id) ou,
 * em observações antigas sem conta, pelo nome do autor.
 */
function observacao_e_do_usuario(array $obs, array $usuario): bool
{
    if (!empty($obs['usuario_id'])) {
        return (int) $obs['usuario_id'] === (int) $usuario['id'];
    }
    return mb_strtolower(trim((string) $obs['autor'])) === mb_strtolower(trim((string) $usuario['nome']));
}

/** Campos prontos para o HTML e para o JSON. */
function observacao_formatar(array $o, array $usuario): array
{
    return [
        'id'             => (int) $o['id'],
        'jogo_id'        => (int) $o['jogo_id'],
        'autor'          => (string) $o['autor'],
        'parecer'        => $o['parecer'] ?: null,
        'parecer_rotulo' => parecer_rotulo($o['parecer'] ?: null),
        'texto'          => (string) $o['texto'],
        'criado_em'      => data_br($o['criado_em']),
        'atualizado_em'  => $o['atualizado_em'] ? data_br($o['atualizado_em']) : null,
        'propria'        => observacao_e_do_usuario($o, $usuario),
    ];
}

/** Último parecer (não nulo) de uma lista já ordenada da mais recente para a mais antiga. */
function ultimo_parecer(array $observacoes): ?string
{
    foreach ($observacoes as $o) {
        if (!empty($o['parecer'])) {
            return $o['parecer'];
        }
    }
    return null;
}

/**
 * Resumo por jogo: último parecer, quantidade e data da última observação.
 * @return array<int, array{slug:string,nome:string,ordem:int,total:int,ultima_em:?string,ultimo_parecer:?string,ultimo_parecer_rotulo:string}>
 */
function resumo_jogos(): array
{
    $st = db()->query(
        'SELECT j.slug, j.nome, j.ordem,
                COUNT(o.id)       AS total,
                MAX(o.criado_em)  AS ultima_em,
                (SELECT p.parecer FROM pj_observacoes p
                  WHERE p.jogo_id = j.id AND p.excluido_em IS NULL AND p.parecer IS NOT NULL
                  ORDER BY p.criado_em DESC, p.id DESC LIMIT 1) AS ultimo_parecer
           FROM pj_jogos j
           LEFT JOIN pj_observacoes o ON o.jogo_id = j.id AND o.excluido_em IS NULL
          GROUP BY j.id, j.slug, j.nome, j.ordem
          ORDER BY j.ordem, j.id'
    );
    return array_map(fn($r) => [
        'slug'                  => $r['slug'],
        'nome'                  => $r['nome'],
        'ordem'                 => (int) $r['ordem'],
        'total'                 => (int) $r['total'],
        'ultima_em'             => $r['ultima_em'] ? data_br($r['ultima_em']) : null,
        'ultimo_parecer'        => $r['ultimo_parecer'] ?: null,
        'ultimo_parecer_rotulo' => $r['ultimo_parecer'] ? parecer_rotulo($r['ultimo_parecer']) : 'Sem parecer',
    ], $st->fetchAll());
}

/**
 * Valida parecer + texto vindos do formulário. Devolve mensagem de erro ou ''.
 */
function observacao_validar(?string $parecer, string $texto): string
{
    if (!parecer_valido($parecer)) {
        return 'Parecer inválido.';
    }
    $texto = trim($texto);
    if ($texto === '' && !$parecer) {
        return 'Escolha um parecer ou escreva uma observação.';
    }
    if (mb_strlen($texto) > OBS_TEXTO_MAX) {
        return 'A observação é longa demais (máximo de ' . OBS_TEXTO_MAX . ' caracteres).';
    }
    return '';
}
