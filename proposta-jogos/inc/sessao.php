<?php
/**
 * inc/sessao.php — sessão PHP, login por senha compartilhada e token CSRF.
 */
declare(strict_types=1);

/** Inicia a sessão com cookie HttpOnly / SameSite e duração definida no config. */
function sessao_iniciar(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');

    session_name(SESSAO_NOME);
    session_set_cookie_params([
        'lifetime' => SESSAO_DURACAO,
        'path'     => (app_base() ?: '') . '/',
        'secure'   => $https,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    ini_set('session.gc_maxlifetime', (string) SESSAO_DURACAO);
    ini_set('session.use_strict_mode', '1');
    session_start();

    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
}

/** Nome do usuário logado ou null. */
function usuario_logado(): ?string
{
    sessao_iniciar();
    $nome = $_SESSION['nome'] ?? null;
    return is_string($nome) && $nome !== '' ? $nome : null;
}

/** Em páginas HTML: manda para o login se não estiver logado. */
function exigir_login(): string
{
    $nome = usuario_logado();
    if ($nome === null) {
        redirecionar(app_base() . '/login.php');
    }
    return $nome;
}

/**
 * Tenta entrar com nome + senha compartilhada.
 * Retorna '' em caso de sucesso ou a mensagem de erro em português.
 */
function login_tentar(string $nome, string $senha): string
{
    sessao_iniciar();
    $nome = trim(preg_replace('/\s+/u', ' ', $nome) ?? '');

    // freio contra tentativas repetidas: após 5 erros, espera 30 s
    $falhas = (int) ($_SESSION['login_falhas'] ?? 0);
    $ultima = (int) ($_SESSION['login_ultima'] ?? 0);
    if ($falhas >= 5 && (time() - $ultima) < 30) {
        return 'Muitas tentativas seguidas. Aguarde 30 segundos e tente de novo.';
    }

    if (mb_strlen($nome) < 2 || mb_strlen($nome) > 60) {
        return 'Informe seu nome (entre 2 e 60 caracteres).';
    }
    if ($senha === '') {
        return 'Informe a senha de acesso.';
    }
    if (!password_verify($senha, ACESSO_SENHA_HASH)) {
        $_SESSION['login_falhas'] = $falhas + 1;
        $_SESSION['login_ultima'] = time();
        return 'Senha de acesso incorreta.';
    }

    session_regenerate_id(true);
    $_SESSION['nome'] = $nome;
    $_SESSION['entrou_em'] = time();
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
    unset($_SESSION['login_falhas'], $_SESSION['login_ultima']);
    return '';
}

/** Encerra a sessão. */
function logout(): void
{
    sessao_iniciar();
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', [
            'expires' => time() - 42000, 'path' => $p['path'], 'domain' => $p['domain'],
            'secure' => $p['secure'], 'httponly' => $p['httponly'], 'samesite' => $p['samesite'],
        ]);
    }
    session_destroy();
}

/** Token CSRF da sessão atual. */
function csrf_token(): string
{
    sessao_iniciar();
    return (string) $_SESSION['csrf'];
}

/** Confere um token CSRF recebido (campo de formulário ou cabeçalho X-CSRF-Token). */
function csrf_validar(?string $token): bool
{
    sessao_iniciar();
    return is_string($token) && $token !== '' && hash_equals((string) $_SESSION['csrf'], $token);
}
