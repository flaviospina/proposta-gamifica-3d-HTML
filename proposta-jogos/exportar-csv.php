<?php
/**
 * exportar-csv.php — baixa todas as observações ativas em CSV (exige login).
 * Separador ";" e BOM UTF-8 para abrir direto no Excel em português.
 * Colunas: Jogo; Autor; Parecer; Observação; Criada em; Editada em
 */
declare(strict_types=1);

require_once __DIR__ . '/inc/bootstrap.php';

exigir_login();

try {
    $st = db()->query(
        'SELECT j.nome AS jogo, o.autor, o.parecer, o.texto, o.criado_em, o.atualizado_em
           FROM pj_observacoes o
           JOIN pj_jogos j ON j.id = o.jogo_id
          WHERE o.excluido_em IS NULL
          ORDER BY j.ordem, j.id, o.criado_em, o.id'
    );
    $linhas = $st->fetchAll();
} catch (PDOException $ex) {
    http_response_code(500);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Não foi possível ler o banco: ' . explicar_erro_pdo($ex);
    exit;
}

$arquivo = 'observacoes-gamifica-3d-' . date('Y-m-d-Hi') . '.csv';
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $arquivo . '"');
header('Cache-Control: no-store');

$saida = fopen('php://output', 'w');
fwrite($saida, "\xEF\xBB\xBF"); // BOM: o Excel reconhece UTF-8 (acentos corretos)
fputcsv($saida, ['Jogo', 'Autor', 'Parecer', 'Observação', 'Criada em', 'Editada em'], ';', '"', '\\');
foreach ($linhas as $l) {
    fputcsv($saida, [
        $l['jogo'],
        $l['autor'],
        $l['parecer'] ? parecer_rotulo($l['parecer']) : '',
        $l['texto'],
        data_br($l['criado_em']),
        $l['atualizado_em'] ? data_br($l['atualizado_em']) : '',
    ], ';', '"', '\\');
}
fclose($saida);
