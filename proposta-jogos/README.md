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
  trocar-senha.php       troca de senha (obrigatória no primeiro acesso)
  usuarios.php           administração de contas (só admin)
  config.php             credenciais do banco — PREENCHER
  teste-conexao.php      diagnóstico em português (pode apagar depois)
  gerar-hash.php         só para emergência (redefinir senha pelo phpMyAdmin) — apagar
  exportar-csv.php       baixa todas as observações em CSV
  api/                   endpoints JSON (salvar, editar, excluir, listar, resumo)
  inc/                   conexão, sessão, CSRF, funções e trechos de HTML (bloqueada no navegador)
  css/  js/  img/        estilo, scripts e as 9 imagens modelo em SVG
  img/compartilhar/      artes para WhatsApp e redes sociais (1200×630 e 1080×1080, JPG e PNG)
  img/icones/            favicon e ícone para celular
  inc/metatags.php       metatags de compartilhamento (Open Graph / Twitter) e ícones
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
6. Confira: na aba **Estrutura** devem aparecer as tabelas `pj_usuarios` (1 linha: admin), `pj_jogos` (9 linhas) e `pj_observacoes` (vazia).

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
3. Clique em **Salvar alterações**. Não há senha de acesso neste arquivo: as contas ficam no banco.

### Etapa 5 — Testar a conexão

1. Abra `https://itthrive.com.br/proposta-jogos/teste-conexao.php`.
2. Todos os itens devem ficar **verdes** (o aviso amarelo sobre a senha temporária do admin é esperado neste momento).
   Se algum ficar vermelho, a própria tela explica o que corrigir (usuário, senha, nome do banco, host, tabelas ou extensão do PHP).
3. Corrija no `config.php`, salve e recarregue a página de teste até ficar tudo certo.

Se aparecer erro de versão do PHP, no cPanel abra **Select PHP Version** e escolha **8.2**
(com as extensões `pdo_mysql` e `mbstring` marcadas).

### Etapa 6 — Primeiro acesso do administrador

1. Abra `https://itthrive.com.br/proposta-jogos/` e entre com:
   - usuário: `admin`
   - senha temporária: `admin1234`
2. A página **obriga** a definir uma senha nova (mínimo de 8 caracteres). Guarde-a: ela é a sua senha de administrador.
3. Se quiser, em **Usuários** → linha do admin, troque o nome "Administrador" pelo seu nome (é o nome que aparece nas observações).

### Etapa 7 — Criar a conta do sócio

1. Com o admin logado, clique em **Usuários** na barra superior.
2. Em **Nova conta**, informe o nome (ex.: "Marcos Silva"), o usuário (ex.: `marcos`) e o perfil **Sócio**.
   Deixe a senha temporária em branco para a página gerar uma.
3. Clique em **Criar conta**. A senha temporária aparece **uma única vez**; copie-a.
4. Envie ao sócio o endereço, o usuário e a senha temporária. No primeiro acesso ele define a senha dele.

Perfis: **Administrador** gerencia contas (criar, redefinir senha, desativar) além de tudo o que o sócio faz;
**Sócio** lê a proposta e escreve observações.

### Etapa 8 — Apagar os arquivos de uso único

No Gerenciador de Arquivos, dentro de `proposta-jogos`, **apague**:

- `gerar-hash.php` (só serve para redefinir uma senha direto no phpMyAdmin; não deve ficar publicado);
- `teste-conexao.php` (recomendado depois que tudo estiver verde).

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
- **Minha senha:** clique no seu nome na barra superior para trocar a senha (pede a senha atual).
- **Usuários (admin):** criar contas, redefinir senhas (gera uma temporária e obriga a troca no próximo acesso),
  ativar/desativar contas e alterar nome ou perfil. Não dá para desativar a própria conta nem o único administrador.
- **Sair:** link **Sair** na barra superior.

### Como funciona a identificação

Cada pessoa tem a própria conta. As observações ficam ligadas à conta que as escreveu, e só ela pode
editá-las ou excluí-las (nem o administrador). O nome exibido é o nome cadastrado na conta.

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

## Compartilhamento (WhatsApp, redes sociais) e metatags

- As metatags ficam em **um único arquivo**, `inc/metatags.php`, já incluído no `<head>` de `index.php`,
  `login.php`, `trocar-senha.php` e `usuarios.php`. Para uma página nova, coloque dentro do `<head>`:
  `<?php require __DIR__ . '/inc/metatags.php'; ?>`
- O endereço público usado nas tags está em `config.php` → `APP_URL` (sem barra no final). Se publicar em
  outra pasta ou domínio, ajuste ali. Título e descrição vêm de `APP_NOME` e `APP_DESCRICAO`.
- Como a página exige login, o robô do WhatsApp/Facebook/LinkedIn enxerga a tela de acesso; por isso as
  metatags também estão no `login.php`, com o título e a descrição da proposta.
- Artes prontas em `img/compartilhar/`:
  - `og-1200x630.jpg` → prévia automática dos links (WhatsApp, Facebook, LinkedIn, Telegram, X);
  - `quadrado-1080.jpg` → prévia 1:1 em apps que preferem quadrado;
  - as versões `.png` das mesmas artes servem para **postar** em redes sociais ou enviar como imagem no WhatsApp.
- Para testar a prévia: cole o link num chat do WhatsApp ou use `https://developers.facebook.com/tools/debug/`.
  Se trocar a arte e a prévia antiga continuar aparecendo, é cache: mude o nome do arquivo no `inc/metatags.php`
  ou peça "Scrape again" na ferramenta do Facebook.
- As artes foram geradas a partir de `ferramentas/compartilhar/` (fora da pasta publicada); para refazer com
  outro texto, edite os `.html` de lá e rode `node ferramentas/compartilhar/renderizar.js`.

---

## Segurança (o que já está aplicado)

- PDO com prepared statements em todas as consultas; escape de saída em tudo que vem do banco.
- Token CSRF em todos os formulários e chamadas da API; sessão com cookie HttpOnly e SameSite.
- Senhas guardadas só como hash bcrypt em `pj_usuarios`; freio de tentativas no login; troca obrigatória de senha temporária.
- `.htaccess` bloqueia `config.php`, `README.md`, `*.sql` e as pastas `inc/` e `sql/` no navegador.
- Endpoints respondem 401 sem sessão e 403 ao tentar editar/excluir observação de outro autor.

## Se algo der errado

- **"Banco de dados indisponível"** ao abrir a página: abra `teste-conexao.php` (reenvie-o se já tiver apagado) e siga a explicação.
- **"A página ainda não está configurada"** no login: ainda há `PREENCHER` no `config.php`.
- **"Esta conta está desativada"**: o admin precisa ativá-la de novo em **Usuários**.
- **Tela em branco:** no `config.php`, troque `APP_ENV` para `'dev'` para ver o erro; volte para `'producao'` depois.
- **Um sócio esqueceu a senha:** o admin abre **Usuários** → **Redefinir senha** e envia a temporária.
- **O admin esqueceu a própria senha:** reenvie `gerar-hash.php`, gere um hash novo e rode no phpMyAdmin
  `UPDATE pj_usuarios SET senha_hash='<hash>', trocar_senha=1 WHERE login='admin';`. Depois apague o arquivo.
- **Já tinha instalado a versão anterior (sem contas):** rode `sql/02-migracao-usuarios.sql` uma vez no phpMyAdmin.
