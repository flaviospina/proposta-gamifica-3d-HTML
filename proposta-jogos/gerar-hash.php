<?php
/**
 * gerar-hash.php — USO ÚNICO. Gera o hash bcrypt da senha de acesso para colar no config.php.
 * Abra no navegador, digite a senha escolhida, copie o hash e APAGUE este arquivo do servidor.
 * A senha digitada não é gravada em lugar nenhum.
 */
declare(strict_types=1);

$hash = '';
$erro = '';
$confere = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $senha = (string) ($_POST['senha'] ?? '');
    $senha2 = (string) ($_POST['senha2'] ?? '');
    if (strlen($senha) < 6) {
        $erro = 'Use uma senha com pelo menos 6 caracteres.';
    } elseif ($senha !== $senha2) {
        $erro = 'As duas senhas não conferem. Digite de novo.';
    } else {
        $hash = password_hash($senha, PASSWORD_BCRYPT, ['cost' => 10]);
        $confere = password_verify($senha, $hash);
    }
}
$h = fn(string $s): string => htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex, nofollow">
  <title>Gerar hash da senha de acesso</title>
  <style>
    body{margin:0;font-family:Inter,system-ui,-apple-system,"Segoe UI",Roboto,sans-serif;background:#FBF7EF;color:#1C2237;line-height:1.5}
    main{max-width:640px;margin:40px auto;padding:0 20px}
    h1{font-family:Fraunces,Georgia,serif;color:#161F38;margin:0 0 6px}
    .card{background:#fff;border:1px solid #E6DFD0;border-radius:16px;padding:24px}
    label{display:block;font-weight:600;margin:14px 0 6px}
    input{width:100%;box-sizing:border-box;padding:12px 14px;border:1.5px solid #E6DFD0;border-radius:10px;font:inherit}
    button{margin-top:18px;background:#E0A537;color:#161F38;border:0;border-radius:999px;padding:12px 22px;font:inherit;font-weight:700;cursor:pointer}
    .erro{background:#F8E4E2;color:#B4443C;padding:12px 14px;border-radius:10px;margin-top:14px}
    .ok{background:#E4EFEA;color:#286456;padding:12px 14px;border-radius:10px;margin-top:14px}
    textarea{width:100%;box-sizing:border-box;font-family:ui-monospace,Menlo,Consolas,monospace;font-size:.95rem;padding:12px;border:1.5px solid #286456;border-radius:10px;margin-top:8px}
    ol{padding-left:20px}
    .aviso{margin-top:18px;background:#FBF0D8;color:#7A5A12;padding:12px 14px;border-radius:10px;font-size:.95rem}
    code{background:#F3EDE0;padding:2px 6px;border-radius:6px}
  </style>
</head>
<body>
<main>
  <h1>Gerar hash da senha de acesso</h1>
  <p>Escolha a senha que os sócios usarão para abrir a proposta. O hash gerado vai no <code>config.php</code>.</p>
  <div class="card">
    <?php if ($hash === ''): ?>
      <form method="post" action="gerar-hash.php" autocomplete="off">
        <label for="s1">Senha de acesso</label>
        <input id="s1" type="password" name="senha" required minlength="6" autocomplete="new-password">
        <label for="s2">Repita a senha</label>
        <input id="s2" type="password" name="senha2" required minlength="6" autocomplete="new-password">
        <?php if ($erro !== ''): ?><div class="erro"><?= $h($erro) ?></div><?php endif; ?>
        <button type="submit">Gerar hash</button>
      </form>
    <?php else: ?>
      <div class="ok">Hash gerado<?= $confere ? ' e conferido' : '' ?>. Copie a linha abaixo inteira:</div>
      <textarea rows="2" readonly onclick="this.select()"><?= $h($hash) ?></textarea>
      <ol>
        <li>Abra o <code>config.php</code> no Gerenciador de Arquivos do cPanel (botão "Editar").</li>
        <li>Localize a linha <code>define('ACESSO_SENHA_HASH', '...');</code> e cole o hash entre as aspas simples, substituindo o texto PREENCHER.</li>
        <li>Salve o arquivo e abra o <a href="teste-conexao.php">teste-conexao.php</a> para conferir.</li>
        <li><strong>Apague este arquivo (gerar-hash.php) do servidor.</strong></li>
      </ol>
      <p><a href="gerar-hash.php">Gerar outro</a></p>
    <?php endif; ?>
  </div>
  <div class="aviso">Este arquivo é de uso único. Enquanto estiver no servidor, qualquer pessoa pode abri-lo; ele não revela nada, mas não deve ficar publicado.</div>
</main>
</body>
</html>
