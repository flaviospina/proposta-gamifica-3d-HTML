-- =====================================================================
--  sql/02-migracao-usuarios.sql — SÓ para quem já tinha rodado a versão
--  anterior do 01-criar-tabelas.sql (sem a tabela pj_usuarios).
--  Quem está instalando do zero NÃO precisa deste arquivo: o 01 já cria tudo.
--
--  Rode no phpMyAdmin com o banco selecionado, uma única vez.
--  (o ALTER TABLE dá erro "Duplicate column" se for executado duas vezes — pode ignorar)
-- =====================================================================
SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS pj_usuarios (
  id               INT UNSIGNED NOT NULL AUTO_INCREMENT,
  login            VARCHAR(30)  NOT NULL,
  nome             VARCHAR(60)  NOT NULL,
  senha_hash       VARCHAR(255) NOT NULL,
  perfil           ENUM('admin','socio') NOT NULL DEFAULT 'socio',
  trocar_senha     TINYINT(1) NOT NULL DEFAULT 1,
  ativo            TINYINT(1) NOT NULL DEFAULT 1,
  criado_por       INT UNSIGNED NULL DEFAULT NULL,
  criado_em        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  ultimo_acesso_em DATETIME NULL DEFAULT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_pj_usuarios_login (login)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO pj_usuarios (login, nome, senha_hash, perfil, trocar_senha, ativo)
SELECT 'admin', 'Administrador', '$2y$10$HMRQizQaHb8QUNVgrTKpp.cYd0qg.4V0dAtiZQI4Dqu3dxGuFfTxC', 'admin', 1, 1
 WHERE NOT EXISTS (SELECT 1 FROM pj_usuarios WHERE login = 'admin');

ALTER TABLE pj_observacoes
  ADD COLUMN usuario_id INT UNSIGNED NULL DEFAULT NULL AFTER jogo_id,
  ADD KEY ix_pj_obs_usuario (usuario_id),
  ADD CONSTRAINT fk_pj_obs_usuario FOREIGN KEY (usuario_id) REFERENCES pj_usuarios (id)
    ON UPDATE CASCADE ON DELETE SET NULL;

-- Observações antigas (sem usuario_id) continuam visíveis; a edição delas passa a ser
-- permitida ao usuário cujo "nome" for igual ao campo "autor".
