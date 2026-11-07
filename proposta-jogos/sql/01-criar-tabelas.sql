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
--  pj_observacoes — cada observação escrita pelos sócios.
--    parecer     → aprovado | ajustes | rever | descartar | NULL (só comentário)
--    excluido_em → exclusão lógica: quando preenchido, a observação some da
--                  página, mas continua no banco para auditoria.
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS pj_observacoes (
  id             INT UNSIGNED NOT NULL AUTO_INCREMENT,
  jogo_id        INT UNSIGNED NOT NULL,
  autor          VARCHAR(60)  NOT NULL COMMENT 'nome informado na tela de acesso',
  parecer        ENUM('aprovado','ajustes','rever','descartar') NULL DEFAULT NULL,
  texto          TEXT NOT NULL,
  criado_em      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  atualizado_em  DATETIME NULL DEFAULT NULL COMMENT 'preenchido quando o autor edita',
  excluido_em    DATETIME NULL DEFAULT NULL COMMENT 'preenchido na exclusão lógica',
  PRIMARY KEY (id),
  KEY ix_pj_obs_jogo (jogo_id, excluido_em, criado_em),
  KEY ix_pj_obs_autor (autor),
  CONSTRAINT fk_pj_obs_jogo FOREIGN KEY (jogo_id) REFERENCES pj_jogos (id)
    ON UPDATE CASCADE ON DELETE RESTRICT
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

-- Fim do script. Confira: a aba "Estrutura" do banco deve listar pj_jogos e pj_observacoes,
-- e pj_jogos deve ter 9 linhas.
