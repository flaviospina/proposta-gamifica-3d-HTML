# Gamifica 3D — Proposta da nova linha de jogos

Página de proposta (HTML + PHP 8.2 + MySQL 8.0) para discussão entre os sócios da IT Thrive,
com imagens modelo de cada jogo e blocos de observações gravados em banco de dados.

Publicada em: `https://itthrive.com.br/proposta-jogos/`

Sem etapa de build: basta copiar a pasta para o servidor pelo cPanel.

---

## Estrutura da pasta

```
proposta-jogos/
  index.php              página da proposta (exige login)
  login.php / sair.php   tela de acesso e saída
  config.php             credenciais do banco e hash da senha — PREENCHER
  teste-conexao.php      diagnóstico em português (pode apagar depois)
  gerar-hash.php         gera o hash da senha — USAR UMA VEZ E APAGAR
  exportar-csv.php       baixa todas as observações em CSV
  api/                   endpoints JSON (salvar, editar, excluir, listar, resumo)
  inc/                   conexão, sessão, CSRF, funções e trechos de HTML (bloqueada no navegador)
  css/  js/  img/        estilo, scripts e as 9 imagens modelo em SVG
  sql/01-criar-tabelas.sql
  .htaccess              bloqueia config, SQL, README e pastas internas
  README.md              este arquivo
```

---

## Publicação pelo cPanel — passo a passo

Faça **uma etapa por vez** e confira antes de seguir.

### Etapa 1 — Criar o banco de dados e o usuário

1. No cPanel, abra **Bancos de dados MySQL**.
2. Em **Criar novo banco de dados**, digite um nome (ex.: `proposta`) e clique em **Criar banco de dados**.
   O cPanel acrescenta o prefixo da conta: o nome final fica algo como `itthri12_proposta`. **Anote o nome completo.**
3. Em **Usuários MySQL → Adicionar novo usuário**, crie um usuário (ex.: `pjuser`) com uma senha forte
   (use o gerador de senha e **anote a senha**). O nome final fica `itthri12_pjuser`.
4. Em **Adicionar usuário ao banco de dados**, escolha o usuário e o banco recém-criados e clique em **Adicionar**.
5. Na tela de privilégios, marque **TODOS OS PRIVILÉGIOS** e clique em **Fazer alterações**.

Você precisa ter anotado: **nome do banco**, **usuário** e **senha**.

### Etapa 2 — Criar as tabelas no phpMyAdmin

1. No cPanel, abra o **phpMyAdmin**.
2. No menu da esquerda, **clique no nome do banco** criado na Etapa 1 (ele precisa ficar selecionado).
3. Clique na aba **SQL**.
4. Abra o arquivo `sql/01-criar-tabelas.sql` no seu computador (Bloco de notas serve), copie **todo** o conteúdo e cole na caixa.
5. Clique em **Executar**.
6. Confira: na aba **Estrutura** devem aparecer as tabelas `pj_jogos` (9 linhas) e `pj_observacoes` (vazia).

Se precisar rodar de novo, pode: o script não duplica nada.

### Etapa 3 — Enviar a pasta para o servidor

1. No seu computador, compacte a pasta `proposta-jogos` em um arquivo `.zip`.
2. No cPanel, abra o **Gerenciador de Arquivos** e entre em `public_html`.
3. Clique em **Carregar**, envie o `.zip` e volte para `public_html`.
4. Clique com o botão direito no `.zip` → **Extract** (Extrair). Deve surgir a pasta `public_html/proposta-jogos`.
5. Apague o `.zip`.

### Etapa 4 — Preencher o `config.php`

1. No Gerenciador de Arquivos, entre em `proposta-jogos`, clique com o botão direito em `config.php` → **Edit** (Editar).
2. Substitua os valores marcados com `PREENCHER`:
   - `DB_NAME` → nome completo do banco (ex.: `itthri12_proposta`)
   - `DB_USER` → usuário completo (ex.: `itthri12_pjuser`)
   - `DB_PASS` → a senha do usuário
   - `DB_HOST` fica `localhost` (padrão da HostGator)
3. Deixe `ACESSO_SENHA_HASH` para a próxima etapa.
4. Clique em **Salvar alterações**.

### Etapa 5 — Gerar o hash da senha de acesso

1. No navegador, abra `https://itthrive.com.br/proposta-jogos/gerar-hash.php`.
2. Digite a senha que os sócios usarão para abrir a proposta (duas vezes) e clique em **Gerar hash**.
3. Copie a linha gerada (começa com `$2y$10$`).
4. Volte ao `config.php` no Gerenciador de Arquivos e cole o hash em
   `define('ACESSO_SENHA_HASH', '...');`, substituindo o texto `PREENCHER...` entre as aspas simples.
5. Salve.

### Etapa 6 — Testar a conexão

1. Abra `https://itthrive.com.br/proposta-jogos/teste-conexao.php`.
2. Todos os itens devem ficar **verdes**. Se algum ficar vermelho, a própria tela explica o que corrigir
   (usuário, senha, nome do banco, host, tabelas ou extensão do PHP).
3. Corrija no `config.php`, salve e recarregue a página de teste até ficar tudo certo.

Se aparecer erro de versão do PHP, no cPanel abra **Select PHP Version** e escolha **8.2**
(com as extensões `pdo_mysql` e `mbstring` marcadas).

### Etapa 7 — Apagar os arquivos de uso único

No Gerenciador de Arquivos, dentro de `proposta-jogos`, **apague**:

- `gerar-hash.php` (obrigatório);
- `teste-conexao.php` (recomendado depois que tudo estiver verde; ele não mostra senhas, mas não precisa ficar publicado).

### Etapa 8 — Enviar o acesso ao sócio

Mande ao sócio, por um canal seguro (de preferência por voz ou em mensagens separadas):

- o endereço: `https://itthrive.com.br/proposta-jogos/`
- a senha de acesso definida na Etapa 5.

Peça que ele entre com o **mesmo nome sempre** (ex.: "Marcos"): é o nome que identifica as observações
dele e que permite editar ou excluir só as próprias.

---

## Como usar a página

- **Observações por jogo:** em cada seção, escolha um parecer (Aprovado · Aprovado com ajustes · Rever · Descartar),
  escreva o texto e clique em **Salvar observação**. Salva sem recarregar a página. O parecer é opcional
  (dá para registrar só um comentário). Ctrl+Enter também salva.
- **Histórico:** fica abaixo do formulário, com autor, data/hora e parecer, mais recentes primeiro.
  Cada um edita ou exclui apenas as próprias observações. A exclusão é lógica (a linha continua no banco com `excluido_em` preenchido).
- **Selo:** o topo de cada seção mostra o último parecer dado ao jogo.
- **Resumo:** o botão **Resumo** na barra superior abre a tabela com cada jogo, último parecer, quantidade de observações,
  data da última e link para a seção. Ali também estão **Exportar observações (CSV)** e **Imprimir / salvar em PDF**.
- **Imagens:** clicar numa imagem modelo abre em tela cheia (Esc fecha).
- **Impressão:** Ctrl+P (ou o botão no Resumo) imprime a proposta completa, com as observações e a tabela-resumo no fim.
  Marque "Gráficos de fundo" nas opções de impressão para manter as cores.
- **Sair:** link **Sair** na barra superior.

### Observação sobre a identificação

A senha é compartilhada e a identificação é pelo **nome digitado no login**. Quem digitar o nome do outro
sócio conseguiria editar as observações dele. Para dois sócios de confiança isso é suficiente; se a página
for aberta a mais pessoas, troque a senha compartilhada por usuários individuais.

---

## Como incluir um jogo novo na proposta

1. **Banco:** no phpMyAdmin, aba SQL do banco, rode (ajustando slug, nome e ordem):

   ```sql
   INSERT INTO pj_jogos (slug, nome, ordem) VALUES ('labirinto-das-fracoes', 'Labirinto das Frações', 8)
   ON DUPLICATE KEY UPDATE nome = VALUES(nome), ordem = VALUES(ordem);
   ```

   O `slug` só pode ter letras minúsculas, números e hífen. A `ordem` define a posição no painel resumo
   (o item `geral` usa 8; coloque o novo jogo antes dele e, se quiser, suba o `geral` para 9).

2. **Imagem modelo:** crie `img/labirinto-das-fracoes.svg` (16:9, viewBox 1600×900) no mesmo estilo das outras.
   Pode partir de um SVG existente e alterar.

3. **Seção na página:** no `index.php`, copie um bloco inteiro `<article class="jogo" ...>…</article>`
   (por exemplo o de Constelação), cole antes do fechamento `</div></section>` da seção `#jogos` e ajuste:
   - `id="labirinto-das-fracoes"` e `data-slug="labirinto-das-fracoes"` no `<article>`;
   - o número (`jogo__num`), o tipo, o nome e os textos de cada bloco;
   - o `src` e o `alt` da `<img>`;
   - as duas chamadas PHP: `<?= $selo('labirinto-das-fracoes') ?>` e `<?= $bloco('labirinto-das-fracoes') ?>`.

4. **Índice e roteiro:** acrescente o link no índice (`<nav class="indice">`) e, se quiser, o jogo na linha do tempo.

5. Envie o `index.php` e o SVG pelo Gerenciador de Arquivos. Pronto: o jogo aparece com bloco de observações,
   selo, entrada no painel resumo e no CSV.

---

## Segurança (o que já está aplicado)

- PDO com prepared statements em todas as consultas; escape de saída em tudo que vem do banco.
- Token CSRF em todos os formulários e chamadas da API; sessão com cookie HttpOnly e SameSite.
- Senha de acesso guardada só como hash bcrypt no `config.php`; freio de tentativas no login.
- `.htaccess` bloqueia `config.php`, `README.md`, `*.sql` e as pastas `inc/` e `sql/` no navegador.
- Endpoints respondem 401 sem sessão e 403 ao tentar editar/excluir observação de outro autor.

## Se algo der errado

- **"Banco de dados indisponível"** ao abrir a página: abra `teste-conexao.php` (reenvie-o se já tiver apagado) e siga a explicação.
- **"A página ainda não está configurada"** no login: ainda há `PREENCHER` no `config.php`.
- **Tela em branco:** no `config.php`, troque `APP_ENV` para `'dev'` para ver o erro; volte para `'producao'` depois.
- **Esqueci a senha de acesso:** reenvie `gerar-hash.php`, gere um hash novo, cole no `config.php` e apague o arquivo.
