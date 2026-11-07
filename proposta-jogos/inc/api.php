<?php
/**
 * inc/api.php — base dos endpoints JSON em api/.
 * Garante: usuário logado (401), token CSRF em POST (403), corpo JSON ou formulário,
 * e converte qualquer exceção em resposta JSON em português.
 */
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');

set_exception_handler(function (Throwable $ex): void {
    error_log('[proposta-jogos] ' . $ex->getMessage());
    $msg = $ex instanceof PDOException
        ? 'Não foi possível acessar o banco de dados. Confira o config.php com o teste-conexao.php.'
        : 'Erro inesperado no servidor.';
    if (APP_ENV === 'dev') {
        $msg .= ' (' . $ex->getMessage() . ')';
    }
    json_response(false, null, $msg, 500);
});

$API_USUARIO = usuario_logado();
if ($API_USUARIO === null) {
    json_response(false, null, 'Sua sessão expirou. Recarregue a página e entre de novo.', 401);
}
if (!empty($API_USUARIO['trocar_senha'])) {
    json_response(false, null, 'Você precisa definir uma senha nova antes de continuar.', 403);
}

/** Dados da requisição: JSON (fetch) ou formulário clássico. */
function api_entrada(): array
{
    static $dados = null;
    if ($dados !== null) {
        return $dados;
    }
    $tipo = $_SERVER['CONTENT_TYPE'] ?? '';
    if (str_contains($tipo, 'application/json')) {
        $bruto = file_get_contents('php://input') ?: '';
        $dados = json_decode($bruto, true);
        if (!is_array($dados)) {
            $dados = [];
        }
    } else {
        $dados = $_POST;
    }
    return $dados;
}

/** Exige método POST e token CSRF válido (cabeçalho X-CSRF-Token ou campo csrf). */
function api_exigir_post(): array
{
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
        json_response(false, null, 'Método não permitido.', 405);
    }
    $dados = api_entrada();
    $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? ($dados['csrf'] ?? null);
    if (!csrf_validar(is_string($token) ? $token : null)) {
        json_response(false, null, 'Sessão inválida (token de segurança). Recarregue a página e tente de novo.', 403);
    }
    return $dados;
}

/** Monta a resposta padrão de um jogo após qualquer alteração. */
function api_resposta_jogo(array $jogo, array $usuario, string $mensagem, ?array $extra = null): never
{
    $obs = observacoes_do_jogo((int) $jogo['id'], $usuario);
    $ultimo = ultimo_parecer($obs);
    $data = [
        'jogo'                  => $jogo['slug'],
        'observacoes'           => $obs,
        'total'                 => count($obs),
        'ultimo_parecer'        => $ultimo,
        'ultimo_parecer_rotulo' => $ultimo ? parecer_rotulo($ultimo) : 'Sem parecer ainda',
    ];
    if ($extra) {
        $data += $extra;
    }
    json_response(true, $data, $mensagem);
}
