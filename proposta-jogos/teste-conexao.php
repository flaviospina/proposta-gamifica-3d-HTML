<?php
/**
 * teste-conexao.php — confere o config.php e a conexão com o MySQL, em português.
 * Abra no navegador depois de preencher o config.php. Pode apagar depois que tudo estiver OK.
 * Não mostra senhas nem credenciais na tela.
 */
declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/inc/funcoes.php';
require_once __DIR__ . '/inc/db.php';

ini_set('display_errors', '0');
error_reporting(E_ALL);

$itens = []; // [ 'ok'|'erro'|'aviso', 'título', 'detalhe' ]

// 1) versão do PHP e extensões
$itens[] = version_compare(PHP_VERSION, '8.1.0', '>=')
    ? ['ok', 'PHP ' . PHP_VERSION, 'Versão adequada (8.1 ou superior).']
    : ['erro', 'PHP ' . PHP_VERSION, 'Versão antiga. No cPanel → "Select PHP Version", escolha 8.2.'];
$itens[] = extension_loaded('pdo_mysql')
    ? ['ok', 'Extensão PDO MySQL', 'Ativa.']
    : ['erro', 'Extensão PDO MySQL', 'Não encontrada. No cPanel → "Select PHP Version", marque pdo_mysql.'];
$itens[] = extension_loaded('mbstring')
    ? ['ok', 'Extensão mbstring', 'Ativa.']
    : ['erro', 'Extensão mbstring', 'Não encontrada. No cPanel → "Select PHP Version", marque mbstring.'];

// 2) config.php preenchido?
$pend = config_pendencias();
$pendBanco = array_filter($pend, fn($m) => !str_contains($m, 'ACESSO_SENHA_HASH'));
$pendHash  = array_filter($pend, fn($m) => str_contains($m, 'ACESSO_SENHA_HASH'));
$itens[] = $pendBanco
    ? ['erro', 'Credenciais do banco no config.php', implode(' ', $pendBanco)]
    : ['ok', 'Credenciais do banco no config.php', 'Preenchidas (host: ' . e(DB_HOST) . ', banco: ' . e(DB_NAME) . ', usuário: ' . e(DB_USER) . ').'];
$itens[] = $pendHash
    ? ['aviso', 'Senha de acesso (hash)', implode(' ', $pendHash)]
    : ['ok', 'Senha de acesso (hash)', 'Hash bcrypt definido. Lembre de apagar o gerar-hash.php.'];

// 3) conexão
$conectou = false;
if (!$pendBanco) {
    try {
        $pdo = db();
        $versao = (string) $pdo->query('SELECT VERSION()')->fetchColumn();
        $itens[] = ['ok', 'Conexão com o MySQL', 'Conectado. Versão do servidor: ' . e($versao) . '.'];
        $conectou = true;
    } catch (PDOException $ex) {
        $itens[] = ['erro', 'Conexão com o MySQL', explicar_erro_pdo($ex)];
    }
}

// 4) tabelas e dados
if ($conectou) {
    try {
        $tabelas = $pdo->query("SHOW TABLES LIKE 'pj\\_%'")->fetchAll(PDO::FETCH_COLUMN);
        $faltam = array_diff(['pj_jogos', 'pj_observacoes'], $tabelas);
        if ($faltam) {
            $itens[] = ['erro', 'Tabelas pj_*', 'Faltam: ' . e(implode(', ', $faltam)) . '. Rode o sql/01-criar-tabelas.sql no phpMyAdmin com o banco selecionado.'];
        } else {
            $qtd = (int) $pdo->query('SELECT COUNT(*) FROM pj_jogos')->fetchColumn();
            $obs = (int) $pdo->query('SELECT COUNT(*) FROM pj_observacoes WHERE excluido_em IS NULL')->fetchColumn();
            $itens[] = $qtd >= 9
                ? ['ok', 'Tabelas pj_*', "pj_jogos com $qtd itens e pj_observacoes com $obs observação(ões) ativa(s)."]
                : ['aviso', 'Tabelas pj_*', "pj_jogos tem só $qtd itens (esperado 9). Rode o sql/01-criar-tabelas.sql de novo; ele não duplica."];
        }
        $charset = $pdo->query("SELECT @@character_set_database")->fetchColumn();
        $itens[] = str_starts_with((string) $charset, 'utf8mb4')
            ? ['ok', 'Codificação do banco', 'utf8mb4 (acentos e emojis OK).']
            : ['aviso', 'Codificação do banco', 'O banco usa ' . e((string) $charset) . '. Funciona, mas o ideal é utf8mb4_unicode_ci (defina ao criar o banco).'];
    } catch (PDOException $ex) {
        $itens[] = ['erro', 'Tabelas pj_*', 'Erro ao consultar: ' . e($ex->getMessage())];
    }
}

// 5) sessão / pasta gravável
$itens[] = (function (): array {
    $dir = session_save_path() ?: sys_get_temp_dir();
    return is_writable($dir)
        ? ['ok', 'Sessões PHP', 'Pasta de sessões gravável.']
        : ['aviso', 'Sessões PHP', 'A pasta de sessões (' . e($dir) . ') não é gravável; o login pode falhar. Peça ao suporte da hospedagem.'];
})();

// 6) arquivos de uso único ainda presentes?
$itens[] = is_file(__DIR__ . '/gerar-hash.php')
    ? ['aviso', 'gerar-hash.php', 'Ainda está no servidor. Depois de colar o hash no config.php, apague-o.']
    : ['ok', 'gerar-hash.php', 'Já foi removido.'];

$temErro = (bool) array_filter($itens, fn($i) => $i[0] === 'erro');
$cor = ['ok' => '#286456', 'erro' => '#B4443C', 'aviso' => '#B8831F'];
$ico = ['ok' => '✓', 'erro' => '✕', 'aviso' => '!'];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex, nofollow">
  <title>Teste de conexão · <?= e(APP_NOME) ?></title>
  <style>
    body{margin:0;font-family:Inter,system-ui,-apple-system,"Segoe UI",Roboto,sans-serif;background:#FBF7EF;color:#1C2237;line-height:1.5}
    main{max-width:760px;margin:40px auto;padding:0 20px}
    h1{font-family:Fraunces,Georgia,serif;color:#161F38;margin:0 0 6px}
    .sub{color:#4B5366;margin:0 0 24px}
    .resumo{padding:16px 20px;border-radius:14px;font-weight:600;margin-bottom:20px;color:#fff}
    ul{list-style:none;padding:0;margin:0;display:grid;gap:10px}
    li{background:#fff;border:1px solid #E6DFD0;border-radius:12px;padding:14px 16px;display:grid;grid-template-columns:34px 1fr;gap:12px;align-items:start}
    .i{width:30px;height:30px;border-radius:50%;display:grid;place-items:center;color:#fff;font-weight:800}
    .t{font-weight:700;color:#161F38}.d{color:#4B5366;font-size:.95rem}
    .prox{margin-top:24px;padding:16px 20px;background:#E4EFEA;border-radius:12px;font-size:.95rem}
    a{color:#286456}
  </style>
</head>
<body>
<main>
  <h1>Teste de conexão</h1>
  <p class="sub"><?= e(APP_NOME) ?> · este teste não mostra senhas.</p>
  <div class="resumo" style="background:<?= $temErro ? '#B4443C' : '#286456' ?>">
    <?= $temErro ? 'Há problemas a corrigir. Veja os itens em vermelho abaixo.' : 'Tudo certo! A página está pronta para uso.' ?>
  </div>
  <ul>
    <?php foreach ($itens as [$st, $titulo, $detalhe]): ?>
      <li><span class="i" style="background:<?= $cor[$st] ?>"><?= $ico[$st] ?></span><div><div class="t"><?= $titulo ?></div><div class="d"><?= $detalhe ?></div></div></li>
    <?php endforeach; ?>
  </ul>
  <div class="prox">
    <strong>Próximo passo:</strong>
    <?php if ($temErro): ?>
      corrija o item em vermelho, salve o config.php pelo Gerenciador de Arquivos e recarregue esta página.
    <?php else: ?>
      abra a <a href="login.php">tela de acesso</a>. Depois, apague <code>gerar-hash.php</code> e, se quiser, este <code>teste-conexao.php</code>.
    <?php endif; ?>
  </div>
</main>
</body>
</html>
