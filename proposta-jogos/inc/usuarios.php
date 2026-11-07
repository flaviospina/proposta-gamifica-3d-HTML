<?php
/**
 * inc/usuarios.php — contas de acesso (tabela pj_usuarios).
 * Todas as consultas usam prepared statements.
 */
declare(strict_types=1);

const PERFIS = ['admin' => 'Administrador', 'socio' => 'Sócio'];

/** Normaliza um login: minúsculas, sem espaços nas pontas. */
function login_normalizar(string $login): string
{
    return mb_strtolower(trim($login));
}

/** Valida um login. Devolve mensagem de erro ou ''. */
function login_validar(string $login): string
{
    if (!preg_match('/^[a-z0-9][a-z0-9._-]{2,29}$/', $login)) {
        return 'O usuário deve ter de 3 a 30 caracteres: letras minúsculas, números, ponto, hífen ou sublinhado, sem espaços.';
    }
    return '';
}

/** Valida uma senha nova. Devolve mensagem de erro ou ''. */
function senha_validar(string $senha, string $confirmacao): string
{
    if (strlen($senha) < SENHA_MINIMO) {
        return 'A senha precisa ter pelo menos ' . SENHA_MINIMO . ' caracteres.';
    }
    if (strlen($senha) > 200) {
        return 'A senha é longa demais.';
    }
    if ($senha !== $confirmacao) {
        return 'As duas senhas não conferem. Digite de novo.';
    }
    return '';
}

/** Gera uma senha temporária legível (sem caracteres ambíguos). */
function senha_temporaria(int $tamanho = 10): string
{
    $alf = 'abcdefghjkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    $s = '';
    for ($i = 0; $i < $tamanho; $i++) {
        $s .= $alf[random_int(0, strlen($alf) - 1)];
    }
    return $s;
}

/** Busca por login (inclusive inativos). */
function usuario_por_login(string $login): ?array
{
    $st = db()->prepare('SELECT * FROM pj_usuarios WHERE login = ? LIMIT 1');
    $st->execute([login_normalizar($login)]);
    $u = $st->fetch();
    return $u ?: null;
}

/** Busca por id. */
function usuario_por_id(int $id): ?array
{
    $st = db()->prepare('SELECT * FROM pj_usuarios WHERE id = ? LIMIT 1');
    $st->execute([$id]);
    $u = $st->fetch();
    return $u ?: null;
}

/** Lista todas as contas, admins primeiro, depois por nome. */
function usuarios_todos(): array
{
    return db()->query(
        'SELECT u.id, u.login, u.nome, u.perfil, u.trocar_senha, u.ativo, u.criado_em, u.ultimo_acesso_em,
                (SELECT COUNT(*) FROM pj_observacoes o WHERE o.usuario_id = u.id AND o.excluido_em IS NULL) AS total_obs
           FROM pj_usuarios u
          ORDER BY (u.perfil = \'admin\') DESC, u.nome'
    )->fetchAll();
}

/** Cria uma conta com senha temporária (trocar_senha = 1). Devolve o id. */
function usuario_criar(string $login, string $nome, string $senhaTemporaria, string $perfil, ?int $criadoPor): int
{
    $st = db()->prepare(
        'INSERT INTO pj_usuarios (login, nome, senha_hash, perfil, trocar_senha, ativo, criado_por)
         VALUES (?, ?, ?, ?, 1, 1, ?)'
    );
    $st->execute([login_normalizar($login), trim($nome), password_hash($senhaTemporaria, PASSWORD_BCRYPT), $perfil, $criadoPor]);
    return (int) db()->lastInsertId();
}

/** Define uma senha nova. $obrigarTroca = true quando é temporária (redefinida pelo admin). */
function usuario_definir_senha(int $id, string $senha, bool $obrigarTroca): void
{
    $st = db()->prepare('UPDATE pj_usuarios SET senha_hash = ?, trocar_senha = ? WHERE id = ?');
    $st->execute([password_hash($senha, PASSWORD_BCRYPT), $obrigarTroca ? 1 : 0, $id]);
}

/** Ativa ou desativa. */
function usuario_definir_ativo(int $id, bool $ativo): void
{
    $st = db()->prepare('UPDATE pj_usuarios SET ativo = ? WHERE id = ?');
    $st->execute([$ativo ? 1 : 0, $id]);
}

/** Altera nome e perfil. */
function usuario_atualizar(int $id, string $nome, string $perfil): void
{
    $st = db()->prepare('UPDATE pj_usuarios SET nome = ?, perfil = ? WHERE id = ?');
    $st->execute([trim($nome), $perfil, $id]);
}

/** Registra o último acesso. */
function usuario_marcar_acesso(int $id): void
{
    db()->prepare('UPDATE pj_usuarios SET ultimo_acesso_em = NOW() WHERE id = ?')->execute([$id]);
}

/** Quantidade de administradores ativos (para não deixar o sistema sem nenhum). */
function usuarios_admins_ativos(): int
{
    return (int) db()->query("SELECT COUNT(*) FROM pj_usuarios WHERE perfil = 'admin' AND ativo = 1")->fetchColumn();
}

/** Campos públicos do usuário para a sessão. */
function usuario_para_sessao(array $u): array
{
    return [
        'id'           => (int) $u['id'],
        'login'        => (string) $u['login'],
        'nome'         => (string) $u['nome'],
        'perfil'       => (string) $u['perfil'],
        'trocar_senha' => (bool) $u['trocar_senha'],
    ];
}
