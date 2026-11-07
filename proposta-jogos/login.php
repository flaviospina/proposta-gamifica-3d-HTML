<?php
/**
 * login.php — tela de acesso por conta individual (tabela pj_usuarios).
 */
declare(strict_types=1);

require_once __DIR__ . '/inc/bootstrap.php';

$pendencias = config_pendencias();
$erro = '';
$login = '';
$destino = '';

if (usuario_logado() !== null) {
    redirecionar(app_base() . '/index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$pendencias) {
    // só aceita destino interno (âncora da própria página, ex.: #arena-60)
    $destino = (string) ($_POST['destino'] ?? '');
    $destino = preg_match('/^#[a-z0-9-]+$/', $destino) ? $destino : '';
    $login = (string) ($_POST['login'] ?? '');
    if (!csrf_validar($_POST['csrf'] ?? null)) {
        $erro = 'Sessão inválida. Recarregue a página e tente de novo.';
    } else {
        try {
            $erro = login_tentar($login, (string) ($_POST['senha'] ?? ''));
        } catch (PDOException $ex) {
            $erro = 'Não foi possível acessar o banco de dados. ' . explicar_erro_pdo($ex);
        }
        if ($erro === '') {
            $u = usuario_logado();
            if (!empty($u['trocar_senha'])) {
                $_SESSION['destino_pos_troca'] = $destino; // volta à seção depois de definir a senha
                redirecionar(app_base() . '/trocar-senha.php');
            }
            redirecionar(app_base() . '/index.php' . $destino);
        }
    }
}
$csrf = csrf_token();

$jogos = [
    ['01', 'Trilha dos Portais', 'Simulados temáticos'],
    ['02', 'Campo das Letras', 'Caça-palavras'],
    ['03', 'Sala Secreta', 'Escape room'],
    ['04', 'Ponte das Frases', 'Complete a frase'],
    ['05', 'Arena 60', 'Nada além de um minuto'],
    ['06', 'Roleta do Alfabeto', 'Stop · Solo e Turma'],
    ['07', 'Constelação', 'Liga pontos'],
];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex, nofollow">
  <title>Entrar · <?= e(APP_NOME) ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,500;0,9..144,600;0,9..144,700;1,9..144,500&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/estilo.css">
</head>
<body class="pagina-acesso">
<main class="acesso">

  <!-- Painel de apresentação (esquerda) -->
  <section class="acesso__painel" aria-label="Sobre a proposta">
    <div class="acesso__painel-inner">
      <a class="marca" href="login.php"><span class="marca__logo">G</span>Gamifica&nbsp;<span class="marca__3d">3D</span></a>
      <p class="capa__kicker">Proposta · nova linha de jogos</p>
      <h1>Sete jogos educativos em 3D, <em>prontos para discutir</em>.</h1>
      <p class="acesso__sub">Personagem navegável, cenários próprios e conteúdo cadastrado pelo professor, construídos sobre a experiência da cruzadinha <strong>Ilha das Palavras</strong>.</p>

      <ol class="acesso__jogos" aria-label="Os sete jogos">
        <?php foreach ($jogos as [$n, $nome, $tipo]): ?>
          <li><span class="acesso__num"><?= e($n) ?></span><span><strong><?= e($nome) ?></strong><small><?= e($tipo) ?></small></span></li>
        <?php endforeach; ?>
      </ol>

      <div class="acesso__galeria" aria-hidden="true">
        <img src="img/roleta-solo.svg" alt="" width="1600" height="900" loading="lazy">
        <img src="img/trilha-dos-portais.svg" alt="" width="1600" height="900" loading="lazy">
        <img src="img/constelacao.svg" alt="" width="1600" height="900" loading="lazy">
      </div>

      <p class="acesso__rodape">IT Thrive · itthrive.com.br · <?= e(APP_DATA) ?></p>
    </div>
    <svg class="acesso__deco" viewBox="0 0 600 420" aria-hidden="true">
      <g opacity=".9">
        <polygon points="300,60 420,120 300,180 180,120" fill="#E0A537"/>
        <polygon points="180,120 300,180 300,300 180,240" fill="#B8831F"/>
        <polygon points="300,180 420,120 420,240 300,300" fill="#C9902A"/>
        <polygon points="120,200 200,240 120,280 40,240" fill="#286456"/>
        <polygon points="40,240 120,280 120,360 40,320" fill="#1F4F44"/>
        <polygon points="120,280 200,240 200,320 120,360" fill="#2E7363"/>
        <polygon points="460,210 540,250 460,290 380,250" fill="#3B6FB6"/>
        <polygon points="380,250 460,290 460,370 380,330" fill="#2E5790"/>
        <polygon points="460,290 540,250 540,330 460,370" fill="#4A82CB"/>
      </g>
    </svg>
  </section>

  <!-- Formulário (direita) -->
  <section class="acesso__form" aria-label="Entrar">
    <div class="acesso__card">
      <span class="kicker">Área dos sócios</span>
      <h2>Entrar na proposta</h2>
      <p class="acesso__texto">Use o usuário e a senha que você recebeu. No primeiro acesso, você vai definir uma senha nova.</p>

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
        <form method="post" action="login.php" class="acesso__campos" autocomplete="on">
          <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
          <input type="hidden" name="destino" id="destino" value="<?= e($destino) ?>">
          <label class="campo">
            <span>Usuário</span>
            <input type="text" name="login" value="<?= e($login) ?>" required maxlength="30" autocomplete="username" autocapitalize="none" spellcheck="false" autofocus placeholder="ex.: flavio">
          </label>
          <label class="campo campo--senha">
            <span>Senha</span>
            <span class="campo__senha">
              <input type="password" name="senha" id="senha" required autocomplete="current-password" placeholder="Sua senha">
              <button type="button" class="campo__olho" id="mostrar-senha" aria-label="Mostrar senha" aria-pressed="false">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg>
              </button>
            </span>
          </label>
          <button class="btn btn--ouro acesso__btn" type="submit">Entrar</button>
        </form>
        <p class="acesso__nota">Esqueceu a senha? Peça ao administrador para redefinir. A página é privada e não aparece em buscadores.</p>
      <?php endif; ?>
    </div>
  </section>
</main>
<script>
  // guarda a âncora (#jogo) para voltar à seção depois de entrar
  var d = document.getElementById('destino'); if (d && location.hash && !d.value) { d.value = location.hash; }
  // mostrar / ocultar senha
  var olho = document.getElementById('mostrar-senha'), senha = document.getElementById('senha');
  if (olho && senha) olho.addEventListener('click', function () {
    var mostrar = senha.type === 'password';
    senha.type = mostrar ? 'text' : 'password';
    olho.setAttribute('aria-pressed', String(mostrar));
    olho.setAttribute('aria-label', mostrar ? 'Ocultar senha' : 'Mostrar senha');
    olho.classList.toggle('ativo', mostrar);
  });
</script>
</body>
</html>
