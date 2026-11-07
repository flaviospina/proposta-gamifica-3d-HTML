<?php
/**
 * inc/db.php — conexão PDO única (singleton) com o MySQL.
 * Sempre use prepared statements: db()->prepare('... ?')->execute([...]).
 */
declare(strict_types=1);

/**
 * Devolve a conexão PDO, criando-a na primeira chamada.
 * @throws PDOException quando as credenciais do config.php estão erradas.
 */
function db(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4', DB_HOST, DB_PORT, DB_NAME);
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
        $pdo->exec("SET time_zone = '-03:00'");
    }
    return $pdo;
}

/**
 * Traduz um erro de conexão do PDO para uma explicação em português.
 * Usado por teste-conexao.php e pelas telas de erro.
 */
function explicar_erro_pdo(PDOException $e): string
{
    $msg = $e->getMessage();
    $cod = (int) ($e->errorInfo[1] ?? 0);
    if ($cod === 0 && preg_match('/\[(\d{4})\]/', $msg, $m)) {
        $cod = (int) $m[1];
    }
    if ($cod === 0 && preg_match('/SQLSTATE\[HY000\] \[(\d+)\]/', $msg, $m)) {
        $cod = (int) $m[1];
    }

    return match (true) {
        $cod === 1045                               => 'Usuário ou senha do banco incorretos (DB_USER / DB_PASS). Confira no cPanel → Bancos de dados MySQL; lembre que o usuário leva o prefixo da conta (ex.: itthri12_...).',
        $cod === 1049                               => 'O banco de dados informado em DB_NAME não existe. Confira o nome exato no cPanel (com o prefixo da conta).',
        $cod === 1044                               => 'Acesso negado ao banco informado em DB_NAME: ou o nome do banco está errado (confira o prefixo da conta), ou o usuário ainda não foi adicionado a ele. No cPanel → Bancos de dados MySQL, em "Adicionar usuário ao banco", escolha o usuário e o banco e marque "Todos os privilégios".',
        $cod === 2002 || $cod === 2003 || $cod === 2005 => 'Não foi possível encontrar o servidor MySQL (DB_HOST). Na HostGator use "localhost". Se o problema persistir, confira a porta (DB_PORT = 3306).',
        $cod === 1042                               => 'O servidor MySQL não respondeu (DB_HOST). Na HostGator o host é "localhost".',
        str_contains($msg, 'could not find driver')  => 'A extensão PDO MySQL não está ativa no PHP. No cPanel → "Select PHP Version", marque pdo_mysql.',
        default                                     => 'Erro de conexão não identificado: ' . $msg,
    };
}
