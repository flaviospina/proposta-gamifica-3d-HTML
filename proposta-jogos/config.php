<?php
/**
 * config.php — ÚNICO arquivo com credenciais. Preencha os valores marcados com PREENCHER.
 *
 * Este arquivo fica protegido pelo .htaccess (não pode ser aberto pelo navegador),
 * mas mesmo assim nunca deixe credenciais de exemplo aqui depois de publicar.
 *
 * Depois de preencher, abra teste-conexao.php no navegador para conferir.
 */
declare(strict_types=1);

/* ---------------------------------------------------------------------
 * 1) BANCO DE DADOS  (cPanel → "Bancos de dados MySQL")
 *    Na HostGator o nome do banco e o usuário recebem o prefixo da conta,
 *    por exemplo: itthri12_proposta  /  itthri12_pjuser
 * ------------------------------------------------------------------- */
define('DB_HOST', 'localhost');                 // na HostGator é "localhost" (não troque, salvo orientação do suporte)
define('DB_NAME', 'PREENCHER_nome_do_banco');   // ex.: itthri12_proposta
define('DB_USER', 'PREENCHER_usuario_do_banco');// ex.: itthri12_pjuser
define('DB_PASS', 'PREENCHER_senha_do_banco');  // a senha definida ao criar o usuário no cPanel
define('DB_PORT', 3306);                        // porta padrão do MySQL; normalmente não muda

/* ---------------------------------------------------------------------
 * 2) SENHA DE ACESSO À PÁGINA (compartilhada entre os sócios)
 *    NÃO coloque a senha em texto puro. Abra gerar-hash.php no navegador,
 *    digite a senha escolhida, copie o hash gerado e cole entre as aspas abaixo.
 *    Depois APAGUE o gerar-hash.php do servidor.
 *    O hash começa com "$2y$10$" e tem 60 caracteres.
 * ------------------------------------------------------------------- */
define('ACESSO_SENHA_HASH', 'PREENCHER_hash_bcrypt_gerado_por_gerar-hash.php');

/* ---------------------------------------------------------------------
 * 3) AJUSTES GERAIS (normalmente não precisa mexer)
 * ------------------------------------------------------------------- */
define('APP_NOME', 'Gamifica 3D — Nova linha de jogos');
define('APP_DATA', '7 de novembro de 2026');     // data exibida na capa e no rodapé
define('APP_ENV', 'producao');                   // 'producao' esconde erros do PHP; 'dev' mostra na tela
define('SESSAO_NOME', 'pj_sessao');              // nome do cookie de sessão
define('SESSAO_DURACAO', 60 * 60 * 24 * 14);     // 14 dias logado
define('OBS_TEXTO_MAX', 4000);                   // tamanho máximo de uma observação (caracteres)

date_default_timezone_set('America/Sao_Paulo');
