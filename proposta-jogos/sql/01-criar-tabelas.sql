-- =====================================================================
--  Gamifica 3D — Proposta da nova linha de jogos
--  sql/01-criar-tabelas.sql
--
--  COMO USAR (phpMyAdmin, cPanel):
--    1. Crie o banco de dados e o usuário em "Bancos de dados MySQL" no cPanel
--       e dê ao usuário "TODOS OS PRIVILÉGIOS" sobre o banco.
--    2. No phpMyAdmin, clique no banco recém-criado no menu da esquerda
--       (o banco precisa estar SELECIONADO antes de rodar este script).
--    3. Abra a aba "SQL", cole todo o conteúdo deste arquivo e clique em "Executar".
--
--  O script é idempotente: pode ser executado mais de uma vez sem duplicar nada
--  (CREATE TABLE IF NOT EXISTS + INSERT ... ON DUPLICATE KEY UPDATE).
--  Todas as tabelas usam o prefixo pj_ (proposta de jogos).
-- =====================================================================

SET NAMES utf8mb4;
SET time_zone = '-03:00';

-- ---------------------------------------------------------------------
--  pj_jogos — a lista de itens que recebem observações.
--  Além dos 7 jogos propostos há dois itens especiais:
--    "ilha-das-palavras"  → a cruzadinha de referência (já existe)
--    "geral"              → observações sobre a proposta como um todo
--  A coluna "ordem" define a sequência de exibição no painel resumo.
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS pj_jogos (
  id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  slug        VARCHAR(60)  NOT NULL COMMENT 'identificador usado na URL/âncora, ex.: trilha-dos-portais',
  nome        VARCHAR(120) NOT NULL COMMENT 'nome exibido, ex.: Trilha dos Portais',
  ordem       TINYINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'posição na lista (0 = primeiro)',
  criado_em   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_pj_jogos_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Jogos (e itens especiais) da proposta Gamifica 3D';

-- ---------------------------------------------------------------------
--  pj_usuarios — contas de acesso à página.
--    perfil        → admin (cria e gerencia contas) | socio (lê e escreve observações)
--    trocar_senha  → 1 obriga a definir uma senha nova no próximo acesso
--                    (todo usuário novo e toda senha redefinida pelo admin nascem assim)
--    ativo         → 0 bloqueia o acesso sem apagar o histórico
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS pj_usuarios (
  id               INT UNSIGNED NOT NULL AUTO_INCREMENT,
  login            VARCHAR(30)  NOT NULL COMMENT 'nome de usuário para entrar (minúsculas, sem espaços)',
  nome             VARCHAR(60)  NOT NULL COMMENT 'nome exibido nas observações',
  senha_hash       VARCHAR(255) NOT NULL COMMENT 'bcrypt (password_hash)',
  perfil           ENUM('admin','socio') NOT NULL DEFAULT 'socio',
  trocar_senha     TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1 = precisa trocar a senha no próximo acesso',
  ativo            TINYINT(1) NOT NULL DEFAULT 1,
  criado_por       INT UNSIGNED NULL DEFAULT NULL,
  criado_em        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  ultimo_acesso_em DATETIME NULL DEFAULT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_pj_usuarios_login (login)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Contas de acesso à proposta';

-- Conta inicial do administrador.
--   usuário: admin     senha temporária: admin1234
--   No primeiro acesso a página OBRIGA a trocar essa senha.
--   (se precisar redefinir depois pelo phpMyAdmin, gere um hash novo com gerar-hash.php
--    e faça: UPDATE pj_usuarios SET senha_hash='<hash>', trocar_senha=1 WHERE login='admin';)
INSERT INTO pj_usuarios (login, nome, senha_hash, perfil, trocar_senha, ativo)
SELECT 'admin', 'Administrador', '$2y$10$HMRQizQaHb8QUNVgrTKpp.cYd0qg.4V0dAtiZQI4Dqu3dxGuFfTxC', 'admin', 1, 1
 WHERE NOT EXISTS (SELECT 1 FROM pj_usuarios WHERE login = 'admin');

-- ---------------------------------------------------------------------
--  pj_observacoes — cada observação escrita pelos sócios.
--    usuario_id  → conta que escreveu (define quem pode editar/excluir)
--    autor       → nome do usuário no momento da gravação (exibição)
--    parecer     → aprovado | ajustes | rever | descartar | NULL (só comentário)
--    excluido_em → exclusão lógica: quando preenchido, a observação some da
--                  página, mas continua no banco para auditoria.
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS pj_observacoes (
  id             INT UNSIGNED NOT NULL AUTO_INCREMENT,
  jogo_id        INT UNSIGNED NOT NULL,
  usuario_id     INT UNSIGNED NULL DEFAULT NULL,
  autor          VARCHAR(60)  NOT NULL COMMENT 'nome do usuário quando escreveu',
  parecer        ENUM('aprovado','ajustes','rever','descartar') NULL DEFAULT NULL,
  texto          TEXT NOT NULL,
  criado_em      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  atualizado_em  DATETIME NULL DEFAULT NULL COMMENT 'preenchido quando o autor edita',
  excluido_em    DATETIME NULL DEFAULT NULL COMMENT 'preenchido na exclusão lógica',
  PRIMARY KEY (id),
  KEY ix_pj_obs_jogo (jogo_id, excluido_em, criado_em),
  KEY ix_pj_obs_autor (autor),
  KEY ix_pj_obs_usuario (usuario_id),
  CONSTRAINT fk_pj_obs_jogo FOREIGN KEY (jogo_id) REFERENCES pj_jogos (id)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT fk_pj_obs_usuario FOREIGN KEY (usuario_id) REFERENCES pj_usuarios (id)
    ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Observações dos sócios sobre cada jogo da proposta';

-- ---------------------------------------------------------------------
--  Dados iniciais: os 7 jogos + referência + geral.
--  Para incluir um jogo novo na proposta, acrescente uma linha aqui
--  (ou rode um INSERT igual no phpMyAdmin) e crie a seção correspondente
--  no index.php — veja o README.md.
-- ---------------------------------------------------------------------
INSERT INTO pj_jogos (slug, nome, ordem) VALUES
  ('ilha-das-palavras',  'Ilha das Palavras (referência)', 0),
  ('trilha-dos-portais', 'Trilha dos Portais',             1),
  ('campo-das-letras',   'Campo das Letras',               2),
  ('sala-secreta',       'Sala Secreta',                   3),
  ('ponte-das-frases',   'Ponte das Frases',               4),
  ('arena-60',           'Arena 60',                       5),
  ('roleta-do-alfabeto', 'Roleta do Alfabeto',             6),
  ('constelacao',        'Constelação',                    7),
  ('geral',              'Observações gerais',             8)
ON DUPLICATE KEY UPDATE nome = VALUES(nome), ordem = VALUES(ordem);

-- Fim do script. Confira: a aba "Estrutura" do banco deve listar pj_usuarios, pj_jogos e
-- pj_observacoes; pj_jogos deve ter 9 linhas e pj_usuarios 1 linha (admin).
