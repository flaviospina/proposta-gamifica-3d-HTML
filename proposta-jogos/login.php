<?php
/**
 * login.php — tela de acesso: nome + senha compartilhada (hash no config.php).
 */
declare(strict_types=1);

require_once __DIR__ . '/inc/bootstrap.php';

$pendencias = config_pendencias();
$erro = '';
$nome = '';
$destino = '';

if (usuario_logado() !== null) {
    redirecionar(app_base() . '/index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$pendencias) {
    if (!csrf_validar($_POST['csrf'] ?? null)) {
        $erro = 'Sessão inválida. Recarregue a página e tente de novo.';
    } else {
        $nome = (string) ($_POST['nome'] ?? '');
        $erro = login_tentar($nome, (string) ($_POST['senha'] ?? ''));
        // só aceita destino interno (âncora da própria página, ex.: #arena-60)
        $destino = (string) ($_POST['destino'] ?? '');
        $destino = preg_match('/^#[a-z0-9-]+$/', $destino) ? $destino : '';
        if ($erro === '') {
            redirecionar(app_base() . '/index.php' . $destino);
        }
    }
}
$csrf = csrf_token();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex, nofollow">
  <title>Acesso · <?= e(APP_NOME) ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,500;0,9..144,600;0,9..144,700;1,9..144,500&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/estilo.css">
</head>
<body class="pagina-login">
  <main class="login">
    <div class="login__card">
      <div class="login__marca"><span class="marca__logo">G</span><span>Gamifica <b>3D</b></span></div>
      <h1>Proposta da nova linha de jogos</h1>
      <p class="login__sub">Página privada para discussão entre os sócios. Informe seu nome e a senha de acesso.</p>

      <?php if ($pendencias): ?>
        <div class="alerta alerta--erro">
          <strong>A página ainda não está configurada.</strong>
          <ul><?php foreach ($pendencias as $p): ?><li><?= e($p) ?></li><?php endforeach; ?></ul>
          <p>Siga o README.md e confira com o <a href="teste-conexao.php">teste-conexao.php</a>.</p>
        </div>
      <?php else: ?>
        <?php if ($erro !== ''): ?>
          <div class="alerta alerta--erro" role="alert"><?= e($erro) ?></div>
        <?php endif; ?>
        <form method="post" action="login.php" class="login__form" autocomplete="on">
          <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
          <input type="hidden" name="destino" id="destino" value="<?= e($destino) ?>">
          <label class="campo">
            <span>Seu nome</span>
            <input type="text" name="nome" value="<?= e($nome) ?>" required minlength="2" maxlength="60" autocomplete="name" autofocus placeholder="Como vai aparecer nas observações">
          </label>
          <label class="campo">
            <span>Senha de acesso</span>
            <input type="password" name="senha" required autocomplete="current-password" placeholder="Senha combinada entre os sócios">
          </label>
          <button class="btn btn--ouro login__btn" type="submit">Entrar</button>
        </form>
        <p class="login__nota">O nome informado identifica suas observações. Use sempre o mesmo nome para poder editá-las depois.</p>
      <?php endif; ?>
    </div>
    <p class="login__rodape">IT Thrive · itthrive.com.br</p>
  </main>
  <script>
    // guarda a âncora (#jogo) para voltar à seção depois de entrar
    var d = document.getElementById('destino'); if (d && location.hash && !d.value) { d.value = location.hash; }
  </script>
</body>
</html>
