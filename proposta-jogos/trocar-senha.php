<?php
/**
 * trocar-senha.php — define uma senha nova.
 * Obrigatória no primeiro acesso e após o admin redefinir a senha (trocar_senha = 1);
 * nos demais casos, pede a senha atual.
 */
declare(strict_types=1);

require_once __DIR__ . '/inc/bootstrap.php';

$usuario = exigir_login(true);
$obrigatoria = !empty($usuario['trocar_senha']);
$erro = '';
$ok = false;
$destino = (string) ($_SESSION['destino_pos_troca'] ?? '');
$destino = preg_match('/^#[a-z0-9-]+$/', $destino) ? $destino : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_validar($_POST['csrf'] ?? null)) {
        $erro = 'Sessão inválida. Recarregue a página e tente de novo.';
    } else {
        $atual = (string) ($_POST['senha_atual'] ?? '');
        $nova  = (string) ($_POST['senha'] ?? '');
        $conf  = (string) ($_POST['senha2'] ?? '');
        $conta = usuario_por_id((int) $usuario['id']);
        if ($conta === null) {
            $erro = 'Conta não encontrada.';
        } elseif (!$obrigatoria && !password_verify($atual, (string) $conta['senha_hash'])) {
            $erro = 'A senha atual não confere.';
        } elseif (($erro = senha_validar($nova, $conf)) === '') {
            if (password_verify($nova, (string) $conta['senha_hash'])) {
                $erro = 'A senha nova precisa ser diferente da atual.';
            } else {
                usuario_definir_senha((int) $usuario['id'], $nova, false);
                sessao_atualizar_usuario();
                session_regenerate_id(true);
                $ok = true;
                if ($obrigatoria) {
                    unset($_SESSION['destino_pos_troca']);
                    redirecionar(app_base() . '/index.php' . $destino);
                }
            }
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
  <title>Trocar senha · <?= e(APP_NOME) ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,500;0,9..144,600;0,9..144,700;1,9..144,500&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/estilo.css">
</head>
<body class="pagina-acesso pagina-acesso--simples">
<main class="acesso acesso--centro">
  <section class="acesso__form">
    <div class="acesso__card">
      <a class="marca marca--escura" href="index.php"><span class="marca__logo">G</span>Gamifica&nbsp;<span class="marca__3d">3D</span></a>
      <span class="kicker"><?= $obrigatoria ? 'Primeiro acesso' : 'Minha conta' ?></span>
      <h2><?= $obrigatoria ? 'Defina a sua senha' : 'Trocar senha' ?></h2>
      <p class="acesso__texto">
        <?php if ($obrigatoria): ?>
          Olá, <strong><?= e($usuario['nome']) ?></strong>. A senha que você recebeu é temporária. Escolha uma senha só sua para continuar.
        <?php else: ?>
          Conectado como <strong><?= e($usuario['nome']) ?></strong> (<?= e($usuario['login']) ?>).
        <?php endif; ?>
      </p>

      <?php if ($ok): ?>
        <div class="alerta alerta--ok" role="status">Senha alterada com sucesso.</div>
        <p><a class="btn btn--primario" href="index.php">Voltar à proposta</a></p>
      <?php else: ?>
        <?php if ($erro !== ''): ?><div class="alerta alerta--erro" role="alert"><?= e($erro) ?></div><?php endif; ?>
        <form method="post" action="trocar-senha.php" class="acesso__campos" autocomplete="on">
          <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
          <input type="hidden" name="login" value="<?= e($usuario['login']) ?>" autocomplete="username">
          <?php if (!$obrigatoria): ?>
            <label class="campo"><span>Senha atual</span><input type="password" name="senha_atual" required autocomplete="current-password"></label>
          <?php endif; ?>
          <label class="campo"><span>Senha nova</span><input type="password" name="senha" required minlength="<?= (int) SENHA_MINIMO ?>" autocomplete="new-password" autofocus placeholder="mínimo de <?= (int) SENHA_MINIMO ?> caracteres"></label>
          <label class="campo"><span>Repita a senha nova</span><input type="password" name="senha2" required minlength="<?= (int) SENHA_MINIMO ?>" autocomplete="new-password"></label>
          <button class="btn btn--ouro acesso__btn" type="submit"><?= $obrigatoria ? 'Salvar e continuar' : 'Salvar senha nova' ?></button>
        </form>
        <p class="acesso__nota">
          <?php if ($obrigatoria): ?>
            Dica: use uma frase curta com números, fácil de lembrar e difícil de adivinhar. <a href="sair.php">Sair</a>
          <?php else: ?>
            <a href="index.php">Voltar à proposta</a> · <a href="sair.php">Sair</a>
          <?php endif; ?>
        </p>
      <?php endif; ?>
    </div>
  </section>
</main>
</body>
</html>
