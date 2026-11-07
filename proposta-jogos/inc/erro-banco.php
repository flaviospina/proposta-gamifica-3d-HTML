<?php
/**
 * inc/erro-banco.php — tela amigável quando o index.php não consegue acessar o banco.
 * Espera a variável $explicacao (texto em português) definida pelo chamador.
 */
declare(strict_types=1);
$explicacao = $explicacao ?? 'Erro de conexão com o banco de dados.';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex, nofollow">
  <title>Banco de dados indisponível · <?= e(APP_NOME) ?></title>
  <link rel="stylesheet" href="css/estilo.css">
</head>
<body class="pagina-login">
  <main class="login">
    <div class="login__card">
      <div class="login__marca"><span class="marca__logo">G</span><span>Gamifica <b>3D</b></span></div>
      <h1>Banco de dados indisponível</h1>
      <div class="alerta alerta--erro"><?= e($explicacao) ?></div>
      <p class="login__nota">Confira o <code>config.php</code> e abra o <a href="teste-conexao.php">teste-conexao.php</a> para ver o diagnóstico completo. Se o banco está correto, rode o <code>sql/01-criar-tabelas.sql</code> no phpMyAdmin.</p>
      <p><a class="btn btn--contorno" href="index.php">Tentar de novo</a></p>
    </div>
  </main>
</body>
</html>
