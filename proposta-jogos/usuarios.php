<?php
/**
 * usuarios.php — gerenciamento de contas (só administradores).
 * Cria contas com senha temporária, redefine senhas, ativa/desativa e altera nome/perfil.
 */
declare(strict_types=1);

require_once __DIR__ . '/inc/bootstrap.php';

$admin = exigir_admin();
$erro = '';
$sucesso = '';
$senhaGerada = null;   // mostrada uma única vez após criar/redefinir
$form = ['login' => '', 'nome' => '', 'perfil' => 'socio'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_validar($_POST['csrf'] ?? null)) {
        $erro = 'Sessão inválida. Recarregue a página e tente de novo.';
    } else {
        $acao = (string) ($_POST['acao'] ?? '');
        $id   = (int) ($_POST['id'] ?? 0);
        try {
            switch ($acao) {
                case 'criar':
                    $form = [
                        'login'  => login_normalizar((string) ($_POST['login'] ?? '')),
                        'nome'   => trim((string) ($_POST['nome'] ?? '')),
                        'perfil' => (string) ($_POST['perfil'] ?? 'socio'),
                    ];
                    $senhaInformada = trim((string) ($_POST['senha'] ?? ''));
                    if (($erro = login_validar($form['login'])) !== '') {
                        break;
                    }
                    if (mb_strlen($form['nome']) < 2 || mb_strlen($form['nome']) > 60) {
                        $erro = 'Informe o nome (entre 2 e 60 caracteres).';
                        break;
                    }
                    if (!isset(PERFIS[$form['perfil']])) {
                        $erro = 'Perfil inválido.';
                        break;
                    }
                    if (usuario_por_login($form['login']) !== null) {
                        $erro = 'Já existe uma conta com o usuário "' . $form['login'] . '".';
                        break;
                    }
                    if ($senhaInformada !== '' && ($erro = senha_validar($senhaInformada, $senhaInformada)) !== '') {
                        break;
                    }
                    $senhaGerada = $senhaInformada !== '' ? $senhaInformada : senha_temporaria();
                    usuario_criar($form['login'], $form['nome'], $senhaGerada, $form['perfil'], (int) $admin['id']);
                    $sucesso = 'Conta "' . $form['login'] . '" criada. Envie a senha temporária abaixo para ' . $form['nome'] . '; no primeiro acesso a página pedirá uma senha nova.';
                    $form = ['login' => '', 'nome' => '', 'perfil' => 'socio'];
                    break;

                case 'redefinir':
                    $alvo = usuario_por_id($id);
                    if ($alvo === null) {
                        $erro = 'Conta não encontrada.';
                        break;
                    }
                    $senhaGerada = senha_temporaria();
                    usuario_definir_senha($id, $senhaGerada, true);
                    $sucesso = 'Senha de "' . $alvo['login'] . '" redefinida. Envie a senha temporária abaixo; a página pedirá uma senha nova no próximo acesso.';
                    break;

                case 'desativar':
                case 'ativar':
                    $alvo = usuario_por_id($id);
                    if ($alvo === null) {
                        $erro = 'Conta não encontrada.';
                        break;
                    }
                    if ($acao === 'desativar' && $id === (int) $admin['id']) {
                        $erro = 'Você não pode desativar a sua própria conta.';
                        break;
                    }
                    if ($acao === 'desativar' && $alvo['perfil'] === 'admin' && usuarios_admins_ativos() <= 1) {
                        $erro = 'Não é possível desativar o único administrador ativo.';
                        break;
                    }
                    usuario_definir_ativo($id, $acao === 'ativar');
                    $sucesso = 'Conta "' . $alvo['login'] . '" ' . ($acao === 'ativar' ? 'ativada' : 'desativada') . '.';
                    break;

                case 'editar':
                    $alvo = usuario_por_id($id);
                    $nome = trim((string) ($_POST['nome'] ?? ''));
                    $perfil = (string) ($_POST['perfil'] ?? 'socio');
                    if ($alvo === null) {
                        $erro = 'Conta não encontrada.';
                        break;
                    }
                    if (mb_strlen($nome) < 2 || mb_strlen($nome) > 60 || !isset(PERFIS[$perfil])) {
                        $erro = 'Nome ou perfil inválido.';
                        break;
                    }
                    if ($alvo['perfil'] === 'admin' && $perfil !== 'admin' && (int) $alvo['ativo'] === 1 && usuarios_admins_ativos() <= 1) {
                        $erro = 'Não é possível rebaixar o único administrador ativo.';
                        break;
                    }
                    usuario_atualizar($id, $nome, $perfil);
                    if ($id === (int) $admin['id']) {
                        sessao_atualizar_usuario();
                        $admin = usuario_logado() ?? $admin;
                    }
                    $sucesso = 'Conta "' . $alvo['login'] . '" atualizada.';
                    break;

                default:
                    $erro = 'Ação desconhecida.';
            }
        } catch (PDOException $ex) {
            $erro = 'Erro no banco de dados: ' . explicar_erro_pdo($ex);
        }
    }
}

$csrf = csrf_token();
$usuarios = usuarios_todos();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex, nofollow">
  <title>Usuários · <?= e(APP_NOME) ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,500;0,9..144,600;0,9..144,700;1,9..144,500&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/estilo.css">
</head>
<body class="pagina-admin">
<header class="topo">
  <div class="container topo__inner">
    <a class="marca" href="index.php"><span class="marca__logo">G</span>Gamifica&nbsp;<span class="marca__3d">3D</span></a>
    <nav class="nav" aria-label="Navegação"><a href="index.php">← Voltar à proposta</a></nav>
    <div class="topo__usuario">
      <a class="topo__nome" href="trocar-senha.php" title="Trocar minha senha"><?= e($admin['nome']) ?></a>
      <a class="topo__sair" href="sair.php">Sair</a>
    </div>
  </div>
</header>

<main class="secao admin">
  <div class="container">
    <div class="secao__cab">
      <span class="kicker">Administração</span>
      <h2>Contas de acesso</h2>
      <p>Crie uma conta para cada pessoa. Ela recebe uma senha temporária e, no primeiro acesso, define a senha definitiva.</p>
    </div>

    <?php if ($erro !== ''): ?><div class="alerta alerta--erro" role="alert"><?= e($erro) ?></div><?php endif; ?>
    <?php if ($sucesso !== ''): ?>
      <div class="alerta alerta--ok" role="status">
        <?= e($sucesso) ?>
        <?php if ($senhaGerada !== null): ?>
          <div class="senha-temp">
            <span>Senha temporária</span>
            <code id="senha-temp"><?= e($senhaGerada) ?></code>
            <button type="button" class="btn btn--contorno btn--mini" id="copiar-senha">Copiar</button>
            <small>Ela aparece só agora. Se perder, redefina de novo.</small>
          </div>
        <?php endif; ?>
      </div>
    <?php endif; ?>

    <div class="admin__grade">
      <!-- Nova conta -->
      <section class="admin__card" aria-labelledby="nova-conta">
        <h3 id="nova-conta">Nova conta</h3>
        <form method="post" action="usuarios.php" class="acesso__campos" autocomplete="off">
          <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
          <input type="hidden" name="acao" value="criar">
          <label class="campo"><span>Nome (aparece nas observações)</span><input type="text" name="nome" value="<?= e($form['nome']) ?>" required minlength="2" maxlength="60" placeholder="ex.: Marcos Silva"></label>
          <label class="campo"><span>Usuário (para entrar)</span><input type="text" name="login" value="<?= e($form['login']) ?>" required maxlength="30" pattern="[a-z0-9][a-z0-9._-]{2,29}" autocapitalize="none" spellcheck="false" placeholder="ex.: marcos"><small>Minúsculas, números, ponto, hífen ou sublinhado. Sem espaços.</small></label>
          <label class="campo"><span>Perfil</span>
            <select name="perfil">
              <?php foreach (PERFIS as $v => $r): ?><option value="<?= e($v) ?>"<?= $form['perfil'] === $v ? ' selected' : '' ?>><?= e($r) ?></option><?php endforeach; ?>
            </select>
            <small>Administrador também gerencia contas. Sócio lê a proposta e escreve observações.</small>
          </label>
          <label class="campo"><span>Senha temporária (opcional)</span><input type="text" name="senha" minlength="<?= (int) SENHA_MINIMO ?>" maxlength="200" autocomplete="new-password" placeholder="deixe em branco para gerar"><small>Em branco: a página gera uma senha e mostra uma vez. A pessoa troca no primeiro acesso.</small></label>
          <button class="btn btn--primario" type="submit">Criar conta</button>
        </form>
      </section>

      <!-- Lista -->
      <section class="admin__card admin__card--lista" aria-labelledby="lista-contas">
        <h3 id="lista-contas">Contas cadastradas <span class="texto-mudo">(<?= count($usuarios) ?>)</span></h3>
        <div class="admin__rolagem">
          <table class="admin__tabela">
            <thead><tr><th>Nome</th><th>Usuário</th><th>Perfil</th><th>Situação</th><th>Último acesso</th><th class="num">Obs.</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($usuarios as $u): $euMesmo = (int) $u['id'] === (int) $admin['id']; ?>
              <tr class="<?= (int) $u['ativo'] ? '' : 'inativo' ?>">
                <td>
                  <form method="post" action="usuarios.php" class="admin__inline">
                    <input type="hidden" name="csrf" value="<?= e($csrf) ?>"><input type="hidden" name="acao" value="editar"><input type="hidden" name="id" value="<?= (int) $u['id'] ?>">
                    <input type="text" name="nome" value="<?= e($u['nome']) ?>" required minlength="2" maxlength="60" aria-label="Nome">
                </td>
                <td><code><?= e($u['login']) ?></code><?= $euMesmo ? ' <span class="texto-mudo">(você)</span>' : '' ?></td>
                <td>
                    <select name="perfil" aria-label="Perfil"><?php foreach (PERFIS as $v => $r): ?><option value="<?= e($v) ?>"<?= $u['perfil'] === $v ? ' selected' : '' ?>><?= e($r) ?></option><?php endforeach; ?></select>
                    <button class="btn btn--contorno btn--mini" type="submit">Salvar</button>
                  </form>
                </td>
                <td>
                  <?php if (!(int) $u['ativo']): ?><span class="selo selo--descartar">Desativada</span>
                  <?php elseif ((int) $u['trocar_senha']): ?><span class="selo selo--ajustes">Senha temporária</span>
                  <?php else: ?><span class="selo selo--aprovado">Ativa</span><?php endif; ?>
                </td>
                <td><?= $u['ultimo_acesso_em'] ? e(data_br($u['ultimo_acesso_em'])) : '<span class="texto-mudo">nunca</span>' ?></td>
                <td class="num"><?= (int) $u['total_obs'] ?></td>
                <td class="acoes">
                  <form method="post" action="usuarios.php" onsubmit="return confirm('Redefinir a senha de <?= e($u['login']) ?>? A senha atual deixa de valer.');">
                    <input type="hidden" name="csrf" value="<?= e($csrf) ?>"><input type="hidden" name="acao" value="redefinir"><input type="hidden" name="id" value="<?= (int) $u['id'] ?>">
                    <button class="link" type="submit">Redefinir senha</button>
                  </form>
                  <?php if (!$euMesmo): ?>
                    <form method="post" action="usuarios.php" onsubmit="return confirm('<?= (int) $u['ativo'] ? 'Desativar' : 'Ativar' ?> a conta <?= e($u['login']) ?>?');">
                      <input type="hidden" name="csrf" value="<?= e($csrf) ?>"><input type="hidden" name="acao" value="<?= (int) $u['ativo'] ? 'desativar' : 'ativar' ?>"><input type="hidden" name="id" value="<?= (int) $u['id'] ?>">
                      <button class="link <?= (int) $u['ativo'] ? 'link--perigo' : '' ?>" type="submit"><?= (int) $u['ativo'] ? 'Desativar' : 'Ativar' ?></button>
                    </form>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <p class="texto-mudo admin__nota">Desativar bloqueia o acesso sem apagar as observações da pessoa. Não é possível desativar a própria conta nem o único administrador.</p>
      </section>
    </div>
  </div>
</main>

<footer class="rodape">
  <div class="container rodape__inner">
    <div><strong>IT Thrive</strong> · <a href="https://itthrive.com.br" rel="noopener">itthrive.com.br</a></div>
    <div><?= e(APP_NOME) ?> · Administração de contas</div>
  </div>
</footer>
<script>
  var b = document.getElementById('copiar-senha'), c = document.getElementById('senha-temp');
  if (b && c && navigator.clipboard) b.addEventListener('click', function () {
    navigator.clipboard.writeText(c.textContent).then(function () { b.textContent = 'Copiada ✓'; setTimeout(function () { b.textContent = 'Copiar'; }, 2000); });
  });
</script>
</body>
</html>
