<?php
/**
 * inc/funcoes.php — funções utilitárias (escape, JSON, datas, pareceres).
 */
declare(strict_types=1);

/** Rótulos dos pareceres (valor no banco => texto exibido). */
const PARECERES = [
    'aprovado'  => 'Aprovado',
    'ajustes'   => 'Aprovado com ajustes',
    'rever'     => 'Rever',
    'descartar' => 'Descartar',
];

/** Escapa qualquer texto para HTML (proteção contra XSS). */
function e(?string $s): string
{
    return htmlspecialchars((string) $s, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8');
}

/** Resposta JSON padronizada { ok, data, mensagem } e encerra o script. */
function json_response(bool $ok, mixed $data = null, string $mensagem = '', int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    echo json_encode(['ok' => $ok, 'data' => $data, 'mensagem' => $mensagem], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

/** Formata DATETIME do MySQL ("2026-11-07 14:32:10") como "07/11/2026 14:32". */
function data_br(?string $datetime): string
{
    if (!$datetime) {
        return '';
    }
    $ts = strtotime($datetime);
    return $ts ? date('d/m/Y H:i', $ts) : '';
}

/** Rótulo de um parecer; "Comentário" quando não há parecer. */
function parecer_rotulo(?string $parecer): string
{
    return $parecer && isset(PARECERES[$parecer]) ? PARECERES[$parecer] : 'Comentário';
}

/** Verdadeiro se o valor é um parecer válido. */
function parecer_valido(?string $parecer): bool
{
    return $parecer === null || $parecer === '' || isset(PARECERES[$parecer]);
}

/**
 * Lista o que ainda está com valor de exemplo no config.php.
 * Retorna um array de mensagens; vazio quando está tudo preenchido.
 */
function config_pendencias(): array
{
    $p = [];
    foreach (['DB_NAME' => 'nome do banco', 'DB_USER' => 'usuário do banco', 'DB_PASS' => 'senha do banco'] as $c => $rot) {
        $v = defined($c) ? (string) constant($c) : '';
        if ($v === '' || str_starts_with($v, 'PREENCHER')) {
            $p[] = "O $rot ($c) ainda não foi preenchido no config.php.";
        }
    }
    $h = defined('ACESSO_SENHA_HASH') ? (string) ACESSO_SENHA_HASH : '';
    if ($h === '' || str_starts_with($h, 'PREENCHER')) {
        $p[] = 'A senha de acesso (ACESSO_SENHA_HASH) ainda não foi definida no config.php. Use o gerar-hash.php.';
    } elseif (!preg_match('/^\$2[aby]\$\d{2}\$.{53}$/', $h)) {
        $p[] = 'O valor de ACESSO_SENHA_HASH não parece um hash bcrypt válido (deve começar com $2y$ e ter 60 caracteres).';
    }
    return $p;
}

/** Caminho base da aplicação na URL (ex.: "/proposta-jogos"), para links absolutos. */
function app_base(): string
{
    static $base = null;
    if ($base !== null) {
        return $base;
    }
    $raiz = str_replace('\\', '/', realpath(APP_RAIZ) ?: APP_RAIZ);
    $doc = str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT'] ?? '') ?: ($_SERVER['DOCUMENT_ROOT'] ?? ''));
    if ($doc !== '' && str_starts_with($raiz, $doc)) {
        $base = rtrim(substr($raiz, strlen($doc)), '/');
    } else {
        // fallback: deduz pelo script atual (funciona para index.php, login.php, api/*.php)
        $script = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/'));
        $base = rtrim(preg_replace('#/api$#', '', $script) ?? '', '/');
    }
    return $base;
}

/** Redireciona e encerra. */
function redirecionar(string $destino): never
{
    header('Location: ' . $destino);
    exit;
}
