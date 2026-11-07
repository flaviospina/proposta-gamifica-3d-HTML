<?php
/**
 * inc/metatags.php — metatags de compartilhamento (Open Graph, Twitter/X, WhatsApp, LinkedIn)
 * e ícones do site. Já está incluído no <head> de index.php, login.php, trocar-senha.php e usuarios.php.
 *
 * Para incluir em uma página nova, dentro do <head>:
 *     <?php require __DIR__ . '/inc/metatags.php'; ?>
 *
 * Antes do require, a página pode personalizar (opcional):
 *     $mt_titulo    = 'Título desta página';
 *     $mt_descricao = 'Descrição desta página';
 *     $mt_url       = APP_URL . '/pagina.php';
 *
 * Observações:
 *  - A página é privada: os robôs do WhatsApp/Facebook/LinkedIn enxergam a tela de acesso
 *    (login.php). Por isso as metatags também estão lá, com o título e a descrição da proposta.
 *  - As imagens precisam ser JPG/PNG (SVG não funciona) e ter endereço absoluto (https).
 *    Estão em img/compartilhar/: og-1200x630.jpg (prévia de links) e quadrado-1080.jpg (1:1).
 *    As versões .png das mesmas artes servem para postar em redes sociais ou enviar no WhatsApp.
 *  - APP_URL (config.php) é o endereço público, sem barra no final.
 *  - Se trocar a imagem, o WhatsApp e o Facebook guardam a prévia antiga por um tempo. Para forçar
 *    a atualização, mude o nome do arquivo ou use https://developers.facebook.com/tools/debug/.
 */
declare(strict_types=1);

$mt_base      = defined('APP_URL') && APP_URL !== ''
    ? rtrim(APP_URL, '/')
    : ((($_SERVER['HTTPS'] ?? 'off') !== 'off' ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . app_base());
$mt_titulo    = $mt_titulo    ?? APP_NOME;
$mt_descricao = $mt_descricao ?? (defined('APP_DESCRICAO') ? APP_DESCRICAO : 'Proposta de sete jogos educativos em 3D para o Gamifica.');
$mt_url       = $mt_url       ?? $mt_base . '/';
$mt_imagem    = $mt_base . '/img/compartilhar/og-1200x630.jpg';
$mt_imagem_q  = $mt_base . '/img/compartilhar/quadrado-1080.jpg';
$mt_site      = 'Gamifica 3D · IT Thrive';
?>
  <!-- Metatags de compartilhamento (inc/metatags.php) -->
  <meta name="description" content="<?= e($mt_descricao) ?>">
  <meta name="author" content="IT Thrive">
  <meta name="theme-color" content="#161F38">
  <link rel="canonical" href="<?= e($mt_url) ?>">

  <!-- Open Graph: WhatsApp, Facebook, LinkedIn, Telegram -->
  <meta property="og:type" content="website">
  <meta property="og:locale" content="pt_BR">
  <meta property="og:site_name" content="<?= e($mt_site) ?>">
  <meta property="og:title" content="<?= e($mt_titulo) ?>">
  <meta property="og:description" content="<?= e($mt_descricao) ?>">
  <meta property="og:url" content="<?= e($mt_url) ?>">
  <meta property="og:image" content="<?= e($mt_imagem) ?>">
  <meta property="og:image:secure_url" content="<?= e($mt_imagem) ?>">
  <meta property="og:image:type" content="image/jpeg">
  <meta property="og:image:width" content="1200">
  <meta property="og:image:height" content="630">
  <meta property="og:image:alt" content="Gamifica 3D — proposta de sete jogos educativos em 3D">
  <!-- segunda imagem (quadrada) para apps que preferem 1:1 -->
  <meta property="og:image" content="<?= e($mt_imagem_q) ?>">
  <meta property="og:image:type" content="image/jpeg">
  <meta property="og:image:width" content="1080">
  <meta property="og:image:height" content="1080">

  <!-- Twitter / X -->
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="<?= e($mt_titulo) ?>">
  <meta name="twitter:description" content="<?= e($mt_descricao) ?>">
  <meta name="twitter:image" content="<?= e($mt_imagem) ?>">
  <meta name="twitter:image:alt" content="Gamifica 3D — proposta de sete jogos educativos em 3D">

  <!-- Ícones -->
  <link rel="icon" href="img/icones/favicon.svg" type="image/svg+xml">
  <link rel="icon" href="img/icones/favicon-32.png" sizes="32x32" type="image/png">
  <link rel="icon" href="img/icones/favicon-192.png" sizes="192x192" type="image/png">
  <link rel="apple-touch-icon" href="img/icones/apple-touch-icon.png" sizes="180x180">
