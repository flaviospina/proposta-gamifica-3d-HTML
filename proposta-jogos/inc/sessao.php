<?php
/**
 * inc/sessao.php — sessão PHP, login por conta (pj_usuarios) e token CSRF.
 *
 * Na sessão fica $_SESSION['usuario'] = [id, login, nome, perfil, trocar_senha].
 * Quando trocar_senha = true, exigir_login() manda para trocar-senha.php antes de
 * liberar qualquer outra página.
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

/** Usuário logado (array com id, login, nome, perfil, trocar_senha) ou null. */
function usuario_logado(): ?array
{
    sessao_iniciar();
    $u = $_SESSION['usuario'] ?? null;
    return is_array($u) && !empty($u['id']) ? $u : null;
}

/** Verdadeiro se o usuário logado é administrador. */
function usuario_e_admin(?array $u = null): bool
{
    $u = $u ?? usuario_logado();
    return $u !== null && ($u['perfil'] ?? '') === 'admin';
}

/**
 * Em páginas HTML: manda para o login se não estiver logado e para a troca
 * de senha se ela for obrigatória. Devolve o usuário.
 */
function exigir_login(bool $permitirTrocaPendente = false): array
{
    $u = usuario_logado();
    if ($u === null) {
        $destino = basename((string) ($_SERVER['SCRIPT_NAME'] ?? ''));
        redirecionar(app_base() . '/login.php');
    }
    if (!empty($u['trocar_senha']) && !$permitirTrocaPendente) {
        redirecionar(app_base() . '/trocar-senha.php');
    }
    return $u;
}

/** Em páginas HTML: exige administrador (403 para os demais). */
function exigir_admin(): array
{
    $u = exigir_login();
    if (!usuario_e_admin($u)) {
        http_response_code(403);
        echo '<!DOCTYPE html><meta charset="utf-8"><title>Acesso restrito</title><p style="font-family:sans-serif;padding:40px">Esta página é só para administradores. <a href="' . e(app_base()) . '/index.php">Voltar</a></p>';
        exit;
    }
    return $u;
}

/**
 * Tenta entrar com login + senha.
 * Retorna '' em caso de sucesso ou a mensagem de erro em português.
 */
function login_tentar(string $login, string $senha): string
{
    sessao_iniciar();
    $login = login_normalizar($login);

    // freio contra tentativas repetidas: após 5 erros, espera 30 s
    $falhas = (int) ($_SESSION['login_falhas'] ?? 0);
    $ultima = (int) ($_SESSION['login_ultima'] ?? 0);
    if ($falhas >= 5 && (time() - $ultima) < 30) {
        return 'Muitas tentativas seguidas. Aguarde 30 segundos e tente de novo.';
    }
    if ($login === '' || $senha === '') {
        return 'Informe o usuário e a senha.';
    }

    $u = usuario_por_login($login);
    $hashParaConferir = $u ? (string) $u['senha_hash'] : '$2y$10$invalidoinvalidoinvalidoinvalidoinvalidoinvalidoinvalid';
    $confere = password_verify($senha, $hashParaConferir);

    if ($u === null || !$confere) {
        $_SESSION['login_falhas'] = $falhas + 1;
        $_SESSION['login_ultima'] = time();
        return 'Usuário ou senha incorretos.';
    }
    if (!(int) $u['ativo']) {
        return 'Esta conta está desativada. Fale com o administrador.';
    }

    // bcrypt com custo antigo? reaproveita o login para atualizar o hash
    if (password_needs_rehash((string) $u['senha_hash'], PASSWORD_BCRYPT)) {
        usuario_definir_senha((int) $u['id'], $senha, (bool) $u['trocar_senha']);
    }

    session_regenerate_id(true);
    $_SESSION['usuario'] = usuario_para_sessao($u);
    $_SESSION['entrou_em'] = time();
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
    unset($_SESSION['login_falhas'], $_SESSION['login_ultima']);
    usuario_marcar_acesso((int) $u['id']);
    return '';
}

/** Recarrega os dados do usuário logado a partir do banco (após trocar senha, nome etc.). */
function sessao_atualizar_usuario(): void
{
    $u = usuario_logado();
    if ($u === null) {
        return;
    }
    $novo = usuario_por_id((int) $u['id']);
    if ($novo === null || !(int) $novo['ativo']) {
        logout();
        return;
    }
    $_SESSION['usuario'] = usuario_para_sessao($novo);
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
