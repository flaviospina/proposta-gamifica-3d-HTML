<?php
/**
 * index.php — página da proposta Gamifica 3D (privada: exige login).
 * Conteúdo estático dos jogos + blocos de observações gravados no MySQL.
 */
declare(strict_types=1);

require_once __DIR__ . '/inc/bootstrap.php';

$usuario = exigir_login();
$csrf = csrf_token();

// Carrega jogos e observações; se o banco falhar, mostra uma tela de erro amigável.
try {
    $jogos = [];
    foreach (jogos_todos() as $j) {
        $jogos[$j['slug']] = $j;
    }
    $obs = observacoes_por_jogo($usuario);
    $resumo = resumo_jogos();
} catch (PDOException $ex) {
    http_response_code(500);
    $explicacao = explicar_erro_pdo($ex);
    require __DIR__ . '/inc/erro-banco.php';
    exit;
}

/** Bloco de observações de um jogo (ou aviso se o slug não existir em pj_jogos). */
$bloco = function (string $slug, string $titulo = 'Observações do sócio', string $placeholder = 'Escreva aqui a sua observação sobre este jogo…') use ($jogos, $obs): string {
    if (!isset($jogos[$slug])) {
        return '<section class="obs"><p class="obs__vazio">O item "' . e($slug) . '" não está cadastrado em pj_jogos. Rode o sql/01-criar-tabelas.sql.</p></section>';
    }
    return bloco_observacoes_html($jogos[$slug], $obs[$slug] ?? [], $titulo, $placeholder);
};
/** Selo com o último parecer. */
$selo = fn(string $slug): string => selo_html(ultimo_parecer($obs[$slug] ?? []), $slug);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex, nofollow">
  <meta name="csrf" content="<?= e($csrf) ?>">
  <title><?= e(APP_NOME) ?> · Proposta</title>
  <meta name="description" content="Proposta de sete novos jogos educativos em 3D para o Gamifica, para discussão entre os sócios.">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,500;0,9..144,600;0,9..144,700;1,9..144,500&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/estilo.css">
</head>
<body>

<!-- ============================== BARRA SUPERIOR ============================== -->
<header class="topo">
  <div class="container topo__inner">
    <a class="marca" href="#capa"><span class="marca__logo">G</span>Gamifica&nbsp;<span class="marca__3d">3D</span></a>
    <nav class="nav" id="nav" aria-label="Seções da proposta">
      <a href="#visao">A visão</a>
      <a href="#principios">Princípios</a>
      <a href="#jogos">Os 7 jogos</a>
      <a href="#roteiro">Roteiro</a>
      <a href="#geral">Observações gerais</a>
    </nav>
    <button class="btn btn--ouro" type="button" id="abrir-resumo" aria-haspopup="dialog" aria-controls="resumo">Resumo</button>
    <div class="topo__usuario">
      <a class="topo__nome" href="trocar-senha.php" title="Trocar minha senha"><?= e($usuario['nome']) ?></a>
      <?php if (usuario_e_admin($usuario)): ?><a class="topo__link" href="usuarios.php">Usuários</a><?php endif; ?>
      <a class="topo__sair" href="sair.php">Sair</a>
    </div>
    <button class="nav-toggle" type="button" aria-label="Abrir menu" aria-expanded="false" aria-controls="nav">☰</button>
  </div>
</header>

<main>

<!-- ============================== 1. CAPA ============================== -->
<section class="capa" id="capa">
  <div class="container capa__inner">
    <p class="capa__kicker">Gamifica · Linha de jogos 3D</p>
    <h1>Gamifica 3D — <em>Nova linha de jogos</em></h1>
    <p class="capa__sub">Sete jogos educativos com personagem navegável em cenários 3D, construídos a partir da experiência da cruzadinha <strong>Ilha das Palavras</strong>.</p>
    <div class="capa__meta">
      <span><?= e(APP_DATA) ?></span>
      <span>Proposta para discussão entre os sócios</span>
      <span>IT Thrive · CECAPE</span>
    </div>
    <div class="capa__acoes">
      <a class="btn btn--ouro" href="#jogos">Ver os 7 jogos</a>
      <a class="btn btn--fantasma" href="#visao">Começar pela visão</a>
    </div>
  </div>

  <!-- Decoração isométrica (low-poly) -->
  <svg class="capa__deco" viewBox="0 0 600 420" aria-hidden="true">
    <g opacity=".95">
      <polygon points="300,60 420,120 300,180 180,120" fill="#E0A537"/>
      <polygon points="180,120 300,180 300,300 180,240" fill="#B8831F"/>
      <polygon points="300,180 420,120 420,240 300,300" fill="#C9902A"/>
      <polygon points="120,200 200,240 120,280 40,240" fill="#286456"/>
      <polygon points="40,240 120,280 120,360 40,320" fill="#1F4F44"/>
      <polygon points="120,280 200,240 200,320 120,360" fill="#2E7363"/>
      <polygon points="460,210 540,250 460,290 380,250" fill="#3B6FB6"/>
      <polygon points="380,250 460,290 460,370 380,330" fill="#2E5790"/>
      <polygon points="460,290 540,250 540,330 460,370" fill="#4A82CB"/>
      <polygon points="300,300 380,340 300,380 220,340" fill="#FBF7EF" opacity=".9"/>
      <polygon points="220,340 300,380 300,420 220,380" fill="#D9D2C2" opacity=".9"/>
      <polygon points="300,380 380,340 380,380 300,420" fill="#EDE6D6" opacity=".9"/>
    </g>
  </svg>
  <p class="capa__rodape">Role para ler a proposta</p>
</section>

<!-- ============================== 2. A VISÃO ============================== -->
<section class="secao" id="visao">
  <div class="container">
    <div class="secao__cab">
      <span class="kicker">A visão</span>
      <h2>Jogos 3D com personagem navegável, nascidos da cruzadinha</h2>
    </div>
    <div class="visao">
      <div class="visao__texto">
        <p class="lead">A <strong>Ilha das Palavras</strong> mostrou que um personagem andando por um cenário 3D transforma uma atividade conhecida, a palavra cruzada, em uma experiência que o aluno quer repetir. A proposta é usar essa mesma base para criar uma <strong>linha de jogos educativos em 3D</strong>: cada jogo com seu cenário, sua mecânica e seu conteúdo cadastrado pelo professor, mas todos com o mesmo personagem, a mesma câmera, os mesmos controles e a mesma integração com o Gamifica.</p>
        <p>Ao reaproveitar o que já funciona na cruzadinha (personagem, câmera, joystick no celular, sons, salvamento de progresso e envio de pontuação), cada jogo novo custa menos para construir do que o primeiro. O que muda em cada um é a mecânica central e o cenário, e é isso que faz cada jogo parecer novo para o aluno.</p>
        <p>Este documento apresenta <strong>sete jogos</strong> para discutirmos, com imagem modelo, jogabilidade, regras sem punição, modos de jogo, conteúdo que o professor cadastra e indicadores de complexidade e prioridade. Cada seção tem um bloco de observações para registrar o parecer sobre cada jogo.</p>
      </div>

      <!-- Card de referência: Ilha das Palavras -->
      <article class="card-ref" id="ilha-das-palavras">
        <figure class="modelo">
          <img src="img/ilha-das-palavras.svg" alt="Ilha das Palavras: personagem sobre a grade da cruzadinha 3D numa ilha, totens numerados com dicas e letras caindo do céu" width="1600" height="900" loading="lazy" class="modelo__img" data-lightbox><figcaption>Imagem modelo — ilustrativa</figcaption>
        </figure>
        <div class="card-ref__corpo">
          <span class="card-ref__tag">Referência · já existe</span>
          <h3>Palavra cruzada 3D — "Ilha das Palavras"</h3>
          <p>O aluno controla um personagem que anda sobre a grade da cruzadinha, se aproxima de totens numerados para ler a dica e, quando acerta, as letras caem do céu e se encaixam nas casas. Cada palavra resolvida abre um caminho novo no mapa.</p>
          <div class="card-ref__lista">
            <span>Personagem navegável</span>
            <span>Totens de dica</span>
            <span>Letras que caem do céu</span>
            <span>Mapa que se abre</span>
            <span>Joystick no celular</span>
          </div>
        </div>
        <?= $bloco('ilha-das-palavras', 'Observações sobre a referência', 'O que manter, mudar ou evitar nos próximos jogos…') ?>
      </article>
    </div>
  </div>
</section>

<!-- ============================== 3. PRINCÍPIOS COMUNS ============================== -->
<section class="secao secao--papel2" id="principios">
  <div class="container">
    <div class="secao__cab">
      <span class="kicker">Princípios comuns</span>
      <h2>O que vale para todos os jogos da linha</h2>
      <p>Seis regras que definem a linha Gamifica 3D e valem para cada um dos sete jogos, sem exceção.</p>
    </div>
    <div class="principios">

      <article class="principio">
        <div class="principio__ico">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3 4 6v6c0 4.5 3.4 7.8 8 9 4.6-1.2 8-4.5 8-9V6l-8-3Z"/><path d="m8.5 12 2.3 2.3L15.5 9.5"/></svg>
        </div>
        <h3>Sem punição</h3>
        <p>Tentativas ilimitadas. Nenhum erro tira ponto já conquistado e a <strong>melhor pontuação</strong> é a que conta. Errar faz parte do caminho, e o jogo só mostra o que fazer diferente.</p>
      </article>

      <article class="principio principio--ouro">
        <div class="principio__ico">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/></svg>
        </div>
        <h3>Cada jogo é acessado separadamente</h3>
        <p>Cada jogo tem <strong>endereço próprio, entrada própria</strong> e pode ser usado sozinho em sala. Por baixo, todos compartilham uma biblioteca comum (personagem, câmera, joystick no celular, sons, salvamento de progresso e envio de pontuação), o que reduz o custo de desenvolvimento.</p>
        <p class="nota"><strong>Não é um mundo único.</strong> São jogos independentes que apenas dividem a mesma base técnica.</p>
      </article>

      <article class="principio">
        <div class="principio__ico">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="2" y="4" width="14" height="10" rx="1.5"/><path d="M5 18h8"/><rect x="17" y="8" width="5" height="10" rx="1.2"/></svg>
        </div>
        <h3>Funciona em Chromebook e celular</h3>
        <p>Pensado para a realidade da escola: roda no navegador do Chromebook e do celular, com <strong>seletor de qualidade gráfica</strong> e uma <strong>alternativa 2D</strong> para quem não se adapta ao 3D.</p>
      </article>

      <article class="principio">
        <div class="principio__ico">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="4" y="3" width="16" height="18" rx="2"/><path d="M8 8h8M8 12h8M8 16h5"/></svg>
        </div>
        <h3>Conteúdo cadastrado pelo professor</h3>
        <p>Palavras, questões, frases, enigmas e categorias são cadastrados em <strong>formulário</strong>, sem mexer em código. O mesmo jogo serve a qualquer disciplina e a qualquer ano.</p>
      </article>

      <article class="principio">
        <div class="principio__ico">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="m10 8 5 4-5 4V8Z" fill="currentColor"/></svg>
        </div>
        <h3>Modo demonstração</h3>
        <p>Cada jogo abre em <strong>modo demonstração sem login</strong>, com conteúdo de exemplo, para apresentações comerciais e para o professor experimentar antes de cadastrar o seu.</p>
      </article>

      <article class="principio principio--ouro">
        <div class="principio__ico">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m12 3 2.7 5.6 6.1.9-4.4 4.3 1 6.1L12 17l-5.4 2.9 1-6.1L3.2 9.5l6.1-.9L12 3Z"/></svg>
        </div>
        <h3>Integração com o Gamifica</h3>
        <p>A pontuação de cada jogo vai para o <strong>XP do aluno</strong> no Gamifica. O professor vê o progresso da turma no painel que já usa, sem sistema paralelo.</p>
      </article>

    </div>
  </div>
</section>

<!-- ============================== 4. OS 7 JOGOS ============================== -->
<section class="secao jogos" id="jogos">
  <div class="container">
    <div class="secao__cab">
      <span class="kicker">Os 7 jogos</span>
      <h2>Sete jogos, um mesmo jeito de jogar</h2>
      <p>Cada jogo abaixo segue o mesmo roteiro de apresentação: conceito, jogabilidade em 3D, regra sem punição, modos de jogo, o que o professor cadastra, indicadores e espaço para observações. Os nomes criativos são provisórios.</p>
      <nav class="indice" aria-label="Ir para um jogo">
        <a href="#trilha-dos-portais"><span class="indice__num">1</span><span>Trilha dos Portais<small>Simulados temáticos</small></span></a>
        <a href="#campo-das-letras"><span class="indice__num">2</span><span>Campo das Letras<small>Caça-palavras</small></span></a>
        <a href="#sala-secreta"><span class="indice__num">3</span><span>Sala Secreta<small>Escape room</small></span></a>
        <a href="#ponte-das-frases"><span class="indice__num">4</span><span>Ponte das Frases<small>Complete a frase</small></span></a>
        <a href="#arena-60"><span class="indice__num">5</span><span>Arena 60<small>Nada além de um minuto</small></span></a>
        <a href="#roleta-do-alfabeto"><span class="indice__num">6</span><span>Roleta do Alfabeto<small>Jogo do alfabeto (Stop)</small></span></a>
        <a href="#constelacao"><span class="indice__num">7</span><span>Constelação<small>Liga pontos</small></span></a>
      </nav>
    </div>

    <!-- ---------------- JOGO 1 ---------------- -->
    <article class="jogo" id="trilha-dos-portais" data-slug="trilha-dos-portais">
      <header class="jogo__topo">
        <span class="jogo__num">01</span>
        <div>
          <p class="jogo__tipo">Simulados on-line temáticos</p>
          <h3 class="jogo__nome">Trilha dos Portais <span class="jogo__prov">nome provisório</span></h3>
        </div>
        <div class="jogo__selos"><?= $selo('trilha-dos-portais') ?></div>
      </header>
      <div class="jogo__corpo">
        <figure class="modelo">
          <img src="img/trilha-dos-portais.svg" alt="Trilha dos Portais: clareira na floresta com quatro portais A, B, C e D e a pergunta no topo da tela" width="1600" height="900" loading="lazy" class="modelo__img" data-lightbox><figcaption>Imagem modelo — ilustrativa</figcaption>
        </figure>

        <div class="jogo__grid">
          <div class="bloco bloco--conceito bloco--largo">
            <h4>Conceito</h4>
            <p>Um simulado que vira uma caminhada. Cada questão é uma <strong>clareira com quatro portais</strong> (A, B, C e D) e o aluno responde andando até o portal da alternativa escolhida. O cenário muda conforme o tema: floresta para Ciências, cidade antiga para História, e assim por diante.</p>
          </div>
          <div class="bloco bloco--funciona">
            <h4>Como funciona no 3D</h4>
            <ol>
              <li>O aluno chega a uma clareira e a questão aparece no topo da tela, com as quatro alternativas gravadas nos portais.</li>
              <li>Ele caminha até o portal que acha correto e atravessa.</li>
              <li>Se acertou, o portal se ilumina, a trilha avança e a próxima clareira surge adiante.</li>
              <li>Se errou, o portal mostra a explicação e a trilha faz uma volta que leva de novo à mesma questão.</li>
              <li>No fim da trilha, um marco mostra o resultado e os pontos enviados ao Gamifica.</li>
            </ol>
          </div>
          <div class="bloco bloco--sp">
            <h4>Como fica sem punição</h4>
            <p>O portal errado <strong>nunca fecha o caminho</strong>: ele exibe a explicação da alternativa e devolve o aluno à clareira para tentar de novo. Nenhum ponto é perdido. A pontuação cresce com os acertos, e vale a melhor trilha completada.</p>
          </div>
          <div class="bloco">
            <h4>Modos de jogo</h4>
            <ul>
              <li><strong>Trilha completa:</strong> todas as questões do simulado em sequência.</li>
              <li><strong>Revisão:</strong> só as questões que o aluno errou na última trilha.</li>
              <li><strong>Trilha rápida:</strong> um recorte de 10 questões sorteadas, para aquecimento em sala.</li>
            </ul>
          </div>
          <div class="bloco">
            <h4>O que o professor cadastra</h4>
            <ul>
              <li>Tema do simulado (define o cenário).</li>
              <li>Questões com enunciado.</li>
              <li>As quatro alternativas e qual é a correta.</li>
              <li>Explicação de cada alternativa (mostrada no portal errado).</li>
            </ul>
          </div>
          <div class="bloco">
            <h4>Indicadores</h4>
            <div class="indicadores">
              <div class="indicador">
                <span class="indicador__rotulo">Complexidade de desenvolvimento</span>
                <span class="indicador__valor"><span class="barras barras--2"><i></i><i></i><i></i></span> Média</span>
                <small>Mecânica simples; o trabalho está nos cenários por tema.</small>
              </div>
              <div class="indicador">
                <span class="indicador__rotulo">Prioridade sugerida</span>
                <span class="prioridade"><b>2ª</b> onda</span>
                <small>Junto com Ponte das Frases.</small>
              </div>
            </div>
          </div>
        </div>
      </div>
      <?= $bloco('trilha-dos-portais') ?>
    </article>

    <!-- ---------------- JOGO 2 ---------------- -->
    <article class="jogo" id="campo-das-letras" data-slug="campo-das-letras">
      <header class="jogo__topo">
        <span class="jogo__num">02</span>
        <div>
          <p class="jogo__tipo">Caça-palavras</p>
          <h3 class="jogo__nome">Campo das Letras <span class="jogo__prov">nome provisório</span></h3>
        </div>
        <div class="jogo__selos"><?= $selo('campo-das-letras') ?></div>
      </header>
      <div class="jogo__corpo">
        <figure class="modelo">
          <img src="img/campo-das-letras.svg" alt="Campo das Letras: chão de ladrilhos com letras, a palavra PLANETA acesa sob o personagem e a lista de palavras ao lado" width="1600" height="900" loading="lazy" class="modelo__img" data-lightbox><figcaption>Imagem modelo — ilustrativa</figcaption>
        </figure>

        <div class="jogo__grid">
          <div class="bloco bloco--conceito bloco--largo">
            <h4>Conceito</h4>
            <p>O caça-palavras vira um campo para caminhar. As letras da grade ficam <strong>no chão, como ladrilhos</strong>, e o aluno encontra as palavras andando por cima delas em linha reta, na horizontal, na vertical ou na diagonal.</p>
          </div>
          <div class="bloco bloco--funciona">
            <h4>Como funciona no 3D</h4>
            <ol>
              <li>A lista de palavras aparece em um painel lateral e a grade de ladrilhos se estende pelo campo.</li>
              <li>O aluno pisa na primeira letra e segue em linha reta; cada casa pisada vai acendendo.</li>
              <li>Quando a sequência forma uma palavra da lista, as letras sobem do chão e voam para a lista de encontradas.</li>
              <li>A palavra encontrada fica marcada no campo com uma trilha de luz.</li>
              <li>Ao achar todas, o campo inteiro floresce e a pontuação é enviada ao Gamifica.</li>
            </ol>
          </div>
          <div class="bloco bloco--sp">
            <h4>Como fica sem punição</h4>
            <p>Se o aluno passar por uma sequência que não forma palavra, as casas <strong>simplesmente apagam</strong> quando ele sai da linha. Nada é perdido e não há contador de erros: só o tempo do recorde e a quantidade de palavras encontradas contam.</p>
          </div>
          <div class="bloco">
            <h4>Modos de jogo</h4>
            <ul>
              <li><strong>Lista visível:</strong> as palavras a encontrar ficam à mostra.</li>
              <li><strong>Só dicas:</strong> em vez da palavra, aparece a definição ou uma imagem.</li>
              <li><strong>Contra o relógio:</strong> mesmo campo, com cronômetro para bater o recorde pessoal.</li>
            </ul>
          </div>
          <div class="bloco">
            <h4>O que o professor cadastra</h4>
            <ul>
              <li>As palavras (e, opcionalmente, a dica de cada uma).</li>
              <li>O tamanho da grade (por exemplo 8×8, 10×10 ou 12×12).</li>
              <li>A grade é gerada automaticamente, com as letras de preenchimento.</li>
            </ul>
          </div>
          <div class="bloco">
            <h4>Indicadores</h4>
            <div class="indicadores">
              <div class="indicador">
                <span class="indicador__rotulo">Complexidade de desenvolvimento</span>
                <span class="indicador__valor"><span class="barras barras--1"><i></i><i></i><i></i></span> Baixa</span>
                <small>Grade e detecção de linha reaproveitam a lógica da cruzadinha.</small>
              </div>
              <div class="indicador">
                <span class="indicador__rotulo">Prioridade sugerida</span>
                <span class="prioridade"><b>1ª</b> onda</span>
                <small>Junto com Constelação.</small>
              </div>
            </div>
          </div>
        </div>
      </div>
      <?= $bloco('campo-das-letras') ?>
    </article>

    <!-- ---------------- JOGO 3 ---------------- -->
    <article class="jogo" id="sala-secreta" data-slug="sala-secreta">
      <header class="jogo__topo">
        <span class="jogo__num">03</span>
        <div>
          <p class="jogo__tipo">Escape room</p>
          <h3 class="jogo__nome">Sala Secreta <span class="jogo__prov">nome provisório</span></h3>
        </div>
        <div class="jogo__selos"><?= $selo('sala-secreta') ?></div>
      </header>
      <div class="jogo__corpo">
        <figure class="modelo">
          <img src="img/sala-secreta.svg" alt="Sala Secreta: sala com baú, cadeado de código na parede, estante de livros e pista emoldurada" width="1600" height="900" loading="lazy" class="modelo__img" data-lightbox><figcaption>Imagem modelo — ilustrativa</figcaption>
        </figure>

        <div class="jogo__grid">
          <div class="bloco bloco--conceito bloco--largo">
            <h4>Conceito</h4>
            <p>Uma <strong>sala 3D temática</strong> (laboratório, biblioteca, museu) cheia de objetos para examinar. Cada cadeado se abre resolvendo um desafio: uma pergunta, um código, um enigma ou um mini-desafio emprestado dos outros jogos da linha. A história amarra tudo.</p>
          </div>
          <div class="bloco bloco--funciona">
            <h4>Como funciona no 3D</h4>
            <ol>
              <li>O aluno entra na sala e lê a história de abertura (por que está preso ali e o que precisa encontrar).</li>
              <li>Ele anda pela sala e se aproxima de objetos destacados: baú, estante, quadro, mesa.</li>
              <li>Cada objeto revela uma pista ou um cadeado com um desafio.</li>
              <li>Resolver o desafio abre o cadeado, que libera uma nova pista ou parte da sala.</li>
              <li>Ao abrir o último cadeado, a porta se abre e o cronômetro registra o tempo como recorde.</li>
            </ol>
          </div>
          <div class="bloco bloco--sp">
            <h4>Como fica sem punição</h4>
            <p>O cronômetro <strong>só registra o recorde e nunca causa derrota</strong>: não existe tempo esgotado. Um cadeado com resposta errada apenas balança e dá uma dica a mais a cada tentativa. O aluno pode sair e voltar com o progresso salvo.</p>
          </div>
          <div class="bloco">
            <h4>Modos de jogo</h4>
            <ul>
              <li><strong>Exploração livre:</strong> sem cronômetro visível, para a primeira vez.</li>
              <li><strong>Recorde:</strong> com cronômetro, para bater o próprio tempo.</li>
              <li><strong>Em grupo na sala:</strong> a turma dividida em equipes, cada uma no seu Chromebook, com a mesma sala e o placar na lousa.</li>
            </ul>
          </div>
          <div class="bloco">
            <h4>O que o professor cadastra</h4>
            <ul>
              <li>A história de abertura e o tema da sala (define o cenário).</li>
              <li>Os enigmas, em ordem ou livres, e o tipo de cada um (pergunta, código, enigma, mini-desafio).</li>
              <li>As respostas aceitas e as dicas progressivas.</li>
            </ul>
          </div>
          <div class="bloco">
            <h4>Indicadores</h4>
            <div class="indicadores">
              <div class="indicador">
                <span class="indicador__rotulo">Complexidade de desenvolvimento</span>
                <span class="indicador__valor"><span class="barras barras--3"><i></i><i></i><i></i></span> Alta</span>
                <small>Interação com objetos, história encadeada e vários tipos de desafio.</small>
              </div>
              <div class="indicador">
                <span class="indicador__rotulo">Prioridade sugerida</span>
                <span class="prioridade"><b>4ª</b> onda</span>
                <small>Por último: reaproveita os mini-desafios dos outros jogos.</small>
              </div>
            </div>
          </div>
        </div>
      </div>
      <?= $bloco('sala-secreta') ?>
    </article>

    <!-- ---------------- JOGO 4 ---------------- -->
    <article class="jogo" id="ponte-das-frases" data-slug="ponte-das-frases">
      <header class="jogo__topo">
        <span class="jogo__num">04</span>
        <div>
          <p class="jogo__tipo">Complete a frase</p>
          <h3 class="jogo__nome">Ponte das Frases <span class="jogo__prov">nome provisório</span></h3>
        </div>
        <div class="jogo__selos"><?= $selo('ponte-das-frases') ?></div>
      </header>
      <div class="jogo__corpo">
        <figure class="modelo">
          <img src="img/ponte-das-frases.svg" alt="Ponte das Frases: ponte com uma tábua faltando sobre o rio e blocos de palavras espalhados na margem" width="1600" height="900" loading="lazy" class="modelo__img" data-lightbox><figcaption>Imagem modelo — ilustrativa</figcaption>
        </figure>

        <div class="jogo__grid">
          <div class="bloco bloco--conceito bloco--largo">
            <h4>Conceito</h4>
            <p>A frase é uma <strong>ponte com uma tábua faltando</strong>. Blocos com palavras ficam espalhados pelo cenário, e o aluno escolhe um, carrega até o buraco e completa a travessia. Serve para gramática, vocabulário, ortografia, idiomas e conceitos de qualquer disciplina.</p>
          </div>
          <div class="bloco bloco--funciona">
            <h4>Como funciona no 3D</h4>
            <ol>
              <li>O aluno chega à beira de um rio; a frase está escrita nas tábuas da ponte, com uma tábua faltando.</li>
              <li>Blocos de palavras (a correta e as distratoras) estão espalhados pela margem.</li>
              <li>Ele se aproxima de um bloco, pega e leva até o buraco da ponte.</li>
              <li>Se a palavra estiver certa, a tábua se encaixa, a frase se ilumina e o caminho avança para a próxima ponte.</li>
              <li>No fim do percurso, o aluno chega à outra margem com a pontuação enviada ao Gamifica.</li>
            </ol>
          </div>
          <div class="bloco bloco--sp">
            <h4>Como fica sem punição</h4>
            <p>Se a palavra estiver errada, o bloco <strong>volta flutuando para o lugar</strong> e a ponte mostra, discretamente, por que aquela palavra não cabe ali. O aluno tenta quantas vezes quiser. Não há queda no rio, nem perda de pontos.</p>
          </div>
          <div class="bloco">
            <h4>Modos de jogo</h4>
            <ul>
              <li><strong>Travessia:</strong> uma sequência de pontes, uma frase por ponte.</li>
              <li><strong>Várias lacunas:</strong> uma ponte com duas ou três tábuas faltando.</li>
              <li><strong>Ortografia:</strong> os blocos trazem a mesma palavra em grafias diferentes e só uma está certa.</li>
            </ul>
          </div>
          <div class="bloco">
            <h4>O que o professor cadastra</h4>
            <ul>
              <li>As frases, marcando onde fica a lacuna.</li>
              <li>A palavra correta de cada lacuna.</li>
              <li>As palavras distratoras (de duas a cinco por frase) e, se quiser, a explicação de cada uma.</li>
            </ul>
          </div>
          <div class="bloco">
            <h4>Indicadores</h4>
            <div class="indicadores">
              <div class="indicador">
                <span class="indicador__rotulo">Complexidade de desenvolvimento</span>
                <span class="indicador__valor"><span class="barras barras--2"><i></i><i></i><i></i></span> Média</span>
                <small>Pegar e carregar objetos é uma mecânica nova na biblioteca.</small>
              </div>
              <div class="indicador">
                <span class="indicador__rotulo">Prioridade sugerida</span>
                <span class="prioridade"><b>2ª</b> onda</span>
                <small>Junto com Trilha dos Portais.</small>
              </div>
            </div>
          </div>
        </div>
      </div>
      <?= $bloco('ponte-das-frases') ?>
    </article>

    <!-- ---------------- JOGO 5 ---------------- -->
    <article class="jogo" id="arena-60" data-slug="arena-60">
      <header class="jogo__topo">
        <span class="jogo__num">05</span>
        <div>
          <p class="jogo__tipo">Nada além de um minuto</p>
          <h3 class="jogo__nome">Arena 60 <span class="jogo__prov">nome provisório</span></h3>
        </div>
        <div class="jogo__selos"><?= $selo('arena-60') ?></div>
      </header>
      <div class="jogo__corpo">
        <figure class="modelo">
          <img src="img/arena-60.svg" alt="Arena 60: arena circular com esferas de palavras caindo e um cronômetro grande" width="1600" height="900" loading="lazy" class="modelo__img" data-lightbox><figcaption>Imagem modelo — ilustrativa</figcaption>
        </figure>

        <div class="jogo__grid">
          <div class="bloco bloco--conceito bloco--largo">
            <h4>Conceito</h4>
            <p>Uma <strong>arena circular</strong>, um tema anunciado e <strong>60 segundos</strong> no relógio. Caem do céu esferas com palavras, e o aluno corre para pegar as que pertencem ao tema. É o jogo de energia da linha: rápido, repetível e perfeito para os últimos minutos da aula.</p>
          </div>
          <div class="bloco bloco--funciona">
            <h4>Como funciona no 3D</h4>
            <ol>
              <li>O tema aparece em letras grandes no centro da arena (por exemplo "Mamíferos" ou "Substantivos próprios").</li>
              <li>Uma contagem de 3 segundos e o cronômetro de 60 s começa.</li>
              <li>Esferas com palavras caem em pontos aleatórios da arena e rolam pelo chão.</li>
              <li>O aluno corre e encosta nas esferas certas, que estouram em confete e somam pontos.</li>
              <li>Ao zerar o relógio, o placar mostra a rodada e o recorde, e convida para a próxima.</li>
            </ol>
          </div>
          <div class="bloco bloco--sp">
            <h4>Como fica sem punição</h4>
            <p>Esferas erradas <strong>só não contam ponto</strong>: elas somem com um som neutro e a palavra fica na lista de revisão do final. Rodadas são ilimitadas e <strong>vale o recorde</strong>, então uma rodada ruim nunca apaga uma boa.</p>
          </div>
          <div class="bloco">
            <h4>Modos de jogo</h4>
            <ul>
              <li><strong>Rodada única:</strong> 60 segundos, um tema.</li>
              <li><strong>Três rodadas:</strong> três temas em sequência, soma dos três.</li>
              <li><strong>Desafio da turma:</strong> o professor fixa um tema e a turma disputa o recorde da semana.</li>
            </ul>
          </div>
          <div class="bloco">
            <h4>O que o professor cadastra</h4>
            <ul>
              <li>O tema da rodada.</li>
              <li>As palavras corretas (as que pertencem ao tema).</li>
              <li>As palavras distratoras.</li>
            </ul>
          </div>
          <div class="bloco">
            <h4>Indicadores</h4>
            <div class="indicadores">
              <div class="indicador">
                <span class="indicador__rotulo">Complexidade de desenvolvimento</span>
                <span class="indicador__valor"><span class="barras barras--2"><i></i><i></i><i></i></span> Média</span>
                <small>Física das esferas e desempenho no Chromebook pedem cuidado.</small>
              </div>
              <div class="indicador">
                <span class="indicador__rotulo">Prioridade sugerida</span>
                <span class="prioridade"><b>3ª</b> onda</span>
                <small>Junto com a Roleta do Alfabeto.</small>
              </div>
            </div>
          </div>
        </div>
      </div>
      <?= $bloco('arena-60') ?>
    </article>

    <!-- ---------------- JOGO 6 (DESTAQUE) ---------------- -->
    <article class="jogo jogo--destaque" id="roleta-do-alfabeto" data-slug="roleta-do-alfabeto">
      <header class="jogo__topo">
        <span class="jogo__num">06</span>
        <div>
          <p class="jogo__tipo">Jogo do alfabeto (estilo Stop)</p>
          <h3 class="jogo__nome">Roleta do Alfabeto <span class="jogo__prov">nome provisório</span></h3>
        </div>
        <div class="jogo__selos">
          <span class="destaque-faixa">★ Dois modos: Solo e Turma</span>
          <?= $selo('roleta-do-alfabeto') ?>
        </div>
      </header>
      <div class="jogo__corpo">

        <!-- Duas imagens modelo -->
        <div class="modos-duo">
          <figure class="modelo">
            <img src="img/roleta-solo.svg" alt="Roleta do Alfabeto, Modo Solo: roleta gigante com a letra M sorteada e totens de categorias ao redor" width="1600" height="900" loading="lazy" class="modelo__img" data-lightbox><figcaption>Imagem modelo — ilustrativa</figcaption>
          </figure>
          <figure class="modelo">
            <img src="img/roleta-turma-professor.svg" alt="Painel do professor no Modo Turma: letra sorteada, cronômetro, código da sala com QR Code, alunos conectados, respostas por categoria e ranking" width="1600" height="900" loading="lazy" class="modelo__img" data-lightbox><figcaption>Imagem modelo — ilustrativa</figcaption>
          </figure>
        </div>

        <div class="jogo__grid">
          <div class="bloco bloco--conceito bloco--largo">
            <h4>Conceito</h4>
            <p>O clássico <strong>Stop</strong> (Adedonha) em 3D. Uma <strong>roleta gigante</strong> sorteia a letra e cada categoria (Nome, Animal, Fruta, Cidade, Cor, Objeto…) é um <strong>totem no cenário</strong>. O aluno corre até cada totem e escreve a resposta. Pode ser jogado sozinho ou com a <strong>turma inteira ao mesmo tempo</strong>, com o painel do professor projetado na lousa.</p>
          </div>
          <div class="bloco bloco--funciona">
            <h4>Como funciona no 3D</h4>
            <ol>
              <li>A roleta gira no centro do cenário e para em uma letra, que acende em letras gigantes.</li>
              <li>O cronômetro da rodada começa e os totens de categoria se iluminam ao redor.</li>
              <li>O aluno corre até um totem, digita a palavra (teclado na tela no celular) e o totem confirma.</li>
              <li>Ele segue para os outros totens na ordem que quiser, até completar ou o tempo acabar.</li>
              <li>Ao fim, o placar mostra as respostas aceitas, as que foram para revisão e os pontos.</li>
            </ol>
          </div>
          <div class="bloco bloco--sp">
            <h4>Como fica sem punição</h4>
            <p>As respostas são conferidas contra uma lista de palavras aceitas. Uma palavra que <strong>não está na lista não conta como erro</strong>: ela vai para a revisão do professor, que pode aprová-la com um clique. Resposta em branco vale zero, mas a pontuação <strong>nunca fica negativa</strong>.</p>
          </div>
          <div class="bloco">
            <h4>Modos de jogo</h4>
            <ul>
              <li><strong>Modo Solo:</strong> o aluno escolhe o tempo e o número de rodadas e joga no próprio ritmo.</li>
              <li><strong>Modo Turma:</strong> o professor cria uma sala, a letra é a mesma para todos e o tempo corre junto.</li>
            </ul>
            <p class="texto-mudo" style="font-size:.85rem;margin-top:.6em">Os dois modos estão detalhados abaixo.</p>
          </div>
          <div class="bloco">
            <h4>O que o professor cadastra</h4>
            <ul>
              <li>As categorias (padrão ou personalizadas por disciplina, como "Elemento químico" ou "Verbo").</li>
              <li>A lista de palavras aceitas por categoria e letra (com a possibilidade de ampliar a partir das revisões).</li>
              <li>No Modo Turma: tempo, rodadas e a regra de "STOP!".</li>
            </ul>
          </div>
          <div class="bloco">
            <h4>Indicadores</h4>
            <div class="indicadores">
              <div class="indicador">
                <span class="indicador__rotulo">Complexidade de desenvolvimento</span>
                <span class="indicador__valor"><span class="barras barras--3"><i></i><i></i><i></i></span> Alta</span>
                <small>O Modo Solo é médio; o Modo Turma (sala, sincronização, painel) eleva para alta.</small>
              </div>
              <div class="indicador">
                <span class="indicador__rotulo">Prioridade sugerida</span>
                <span class="prioridade"><b>3ª</b> onda</span>
                <small>Primeiro o Solo, depois o Turma. Junto com Arena 60.</small>
              </div>
            </div>
          </div>
        </div>

        <!-- Os dois modos em detalhe -->
        <div class="modos-duo">
          <section class="modo modo--solo" aria-labelledby="modo-solo-t">
            <div class="modo__cab"><h4 id="modo-solo-t">Modo Solo</h4><span>Um aluno</span></div>
            <div class="modo__corpo">
              <p>O aluno joga no próprio ritmo. Antes de começar, escolhe o <strong>tempo da rodada</strong> e o <strong>número de rodadas</strong>.</p>
              <div>
                <p style="font-size:.82rem;color:var(--texto-2);margin-bottom:.4em;font-weight:600">Tempo da rodada (escolha do aluno)</p>
                <div class="tempos"><span>30 s</span><span class="sel">60 s</span><span>90 s</span><span>2 min</span></div>
              </div>
              <ol class="passos">
                <li>A roleta 3D gigante sorteia a letra.</li>
                <li>Cada categoria é um totem no cenário; o aluno vai até cada um e escreve a resposta.</li>
                <li>As respostas são conferidas contra a lista de palavras aceitas.</li>
                <li>Palavra fora da lista <strong>não conta como erro</strong>: vai para a revisão do professor.</li>
                <li>Ao fim das rodadas, vale a melhor pontuação, enviada ao Gamifica.</li>
              </ol>
            </div>
          </section>

          <section class="modo modo--turma" aria-labelledby="modo-turma-t">
            <div class="modo__cab"><h4 id="modo-turma-t">Modo Turma</h4><span>Todos os alunos da sala</span></div>
            <div class="modo__corpo">
              <p>O professor conduz a partida da lousa e todos os alunos jogam ao mesmo tempo, cada um no seu celular ou Chromebook.</p>
              <ol class="passos">
                <li>O professor cria uma sala, define categorias, tempo e rodadas, e recebe um <strong>código de sala</strong> e um <strong>QR Code</strong> para mostrar na lousa.</li>
                <li>Os alunos entram pelo celular ou Chromebook com o código.</li>
                <li>A letra sorteada é <strong>a mesma para todos</strong> e o tempo corre junto.</li>
                <li><strong>Opção "STOP!":</strong> quando o primeiro aluno completa todas as categorias, a rodada se encerra para todos. O professor liga ou desliga essa regra.</li>
                <li>No fim da rodada, o professor vê <strong>as respostas da turma por categoria</strong> na lousa e valida as duvidosas com um clique.</li>
                <li>Ranking da rodada e ranking geral projetados na lousa.</li>
              </ol>
              <div>
                <p style="font-size:.82rem;color:var(--texto-2);margin-bottom:.5em;font-weight:600">Pontuação sugerida</p>
                <div class="pontuacao">
                  <div class="mais"><b>+10</b><small>Resposta única na turma</small></div>
                  <div><b>+5</b><small>Resposta repetida</small></div>
                  <div class="zero"><b>0</b><small>Em branco — nunca negativo</small></div>
                </div>
              </div>
            </div>
          </section>
        </div>

        <div class="nota-tecnica">
          <span class="nota-tecnica__ico">i</span>
          <p><b>Nota técnica.</b> Na hospedagem compartilhada, a sincronização do Modo Turma em tempo real será feita por <b>consulta periódica ao servidor</b> (<em>polling</em> a cada 1–2 segundos), sem servidor de WebSocket. É suficiente para uma turma e não exige infraestrutura adicional.</p>
        </div>
      </div>
      <?= $bloco('roleta-do-alfabeto') ?>
    </article>

    <!-- ---------------- JOGO 7 ---------------- -->
    <article class="jogo" id="constelacao" data-slug="constelacao">
      <header class="jogo__topo">
        <span class="jogo__num">07</span>
        <div>
          <p class="jogo__tipo">Liga pontos</p>
          <h3 class="jogo__nome">Constelação <span class="jogo__prov">nome provisório</span></h3>
        </div>
        <div class="jogo__selos"><?= $selo('constelacao') ?></div>
      </header>
      <div class="jogo__corpo">
        <figure class="modelo">
          <img src="img/constelacao.svg" alt="Constelação: pilares de luz numerados ligados por um rastro luminoso, revelando a figura do Cruzeiro do Sul" width="1600" height="900" loading="lazy" class="modelo__img" data-lightbox><figcaption>Imagem modelo — ilustrativa</figcaption>
        </figure>

        <div class="jogo__grid">
          <div class="bloco bloco--conceito bloco--largo">
            <h4>Conceito</h4>
            <p>O liga-pontos em escala de cenário. Os pontos numerados são <strong>pilares de luz no chão</strong>, e o aluno corre de um ao seguinte deixando um <strong>rastro luminoso</strong>. No final, a figura se revela: mapa do Brasil, constelação, molécula, animal. Há uma variação pedagógica para <strong>ligar pares de conceitos</strong>, como estado e capital ou palavra e significado.</p>
          </div>
          <div class="bloco bloco--funciona">
            <h4>Como funciona no 3D</h4>
            <ol>
              <li>Os pilares de luz se acendem no chão com seus números (ou com conceitos, no modo de pares).</li>
              <li>O aluno corre até o pilar 1; ele pulsa e aponta a direção do próximo.</li>
              <li>A cada pilar alcançado na ordem certa, um rastro luminoso se desenha no chão atrás do personagem.</li>
              <li>Ao tocar o último pilar, a câmera sobe e a figura completa se revela, com nome e uma curiosidade.</li>
              <li>A pontuação (figura completa e tempo de recorde) é enviada ao Gamifica.</li>
            </ol>
          </div>
          <div class="bloco bloco--sp">
            <h4>Como fica sem punição</h4>
            <p>Se o aluno tocar um pilar fora de ordem, ele <strong>apenas pisca em amarelo</strong> e o pilar certo brilha mais forte para orientar. O rastro já desenhado não se apaga e não há contagem de erros. No modo de pares, um par errado se solta suavemente e volta a ficar disponível.</p>
          </div>
          <div class="bloco">
            <h4>Modos de jogo</h4>
            <ul>
              <li><strong>Figura:</strong> ligar os pilares em sequência numérica para revelar a figura.</li>
              <li><strong>Pares de conceitos:</strong> ligar cada pilar ao seu par (estado e capital, palavra e significado, pergunta e resposta).</li>
              <li><strong>Figura surpresa:</strong> sem o nome da figura; o aluno descobre o que é ao terminar.</li>
            </ul>
          </div>
          <div class="bloco">
            <h4>O que o professor cadastra</h4>
            <ul>
              <li>Escolhe uma <strong>figura pronta</strong> da galeria (mapas, constelações, moléculas, animais) ou</li>
              <li>cadastra os <strong>pares de conceitos</strong> (texto de cada lado do par).</li>
              <li>Opcional: nome da figura e curiosidade exibida no final.</li>
            </ul>
          </div>
          <div class="bloco">
            <h4>Indicadores</h4>
            <div class="indicadores">
              <div class="indicador">
                <span class="indicador__rotulo">Complexidade de desenvolvimento</span>
                <span class="indicador__valor"><span class="barras barras--1"><i></i><i></i><i></i></span> Baixa</span>
                <small>Mecânica mais simples da linha: ideal para estrear a biblioteca comum.</small>
              </div>
              <div class="indicador">
                <span class="indicador__rotulo">Prioridade sugerida</span>
                <span class="prioridade"><b>1ª</b> onda</span>
                <small>Junto com Campo das Letras.</small>
              </div>
            </div>
          </div>
        </div>
      </div>
      <?= $bloco('constelacao') ?>
    </article>

  </div>
</section>

<!-- ============================== 5. ROTEIRO SUGERIDO ============================== -->
<section class="secao secao--navy" id="roteiro">
  <div class="container">
    <div class="secao__cab">
      <span class="kicker">Roteiro sugerido</span>
      <h2>Ordem de construção, em etapas</h2>
      <p>Começamos pelo que já existe, seguimos pelos jogos mais simples e terminamos pelo que reaproveita tudo. São etapas, sem datas.</p>
    </div>
    <ol class="timeline">
      <li>
        <span class="timeline__num">1</span>
        <div class="timeline__card">
          <h3>Biblioteca comum</h3>
          <p>Extraída da cruzadinha: personagem, câmera, joystick no celular, sons, salvamento de progresso e envio de pontuação ao Gamifica.</p>
          <div class="timeline__tags"><span>Base de tudo</span></div>
        </div>
      </li>
      <li>
        <span class="timeline__num">2</span>
        <div class="timeline__card">
          <h3>Liga pontos + Caça-palavras</h3>
          <p>Os dois jogos de complexidade baixa, para validar a biblioteca com conteúdo real em sala.</p>
          <div class="timeline__tags"><span>Constelação</span><span>Campo das Letras</span></div>
        </div>
      </li>
      <li>
        <span class="timeline__num">3</span>
        <div class="timeline__card">
          <h3>Complete a frase + Simulados</h3>
          <p>Introduzem carregar objetos e cenários por tema, as duas mecânicas novas de complexidade média.</p>
          <div class="timeline__tags"><span>Ponte das Frases</span><span>Trilha dos Portais</span></div>
        </div>
      </li>
      <li>
        <span class="timeline__num">4</span>
        <div class="timeline__card">
          <h3>Arena 60 + Roleta do Alfabeto</h3>
          <p>Os jogos de tempo. A Roleta sai primeiro no Modo Solo e depois ganha o Modo Turma com painel na lousa.</p>
          <div class="timeline__tags"><span>Arena 60</span><span>Roleta Solo</span><span>Roleta Turma</span></div>
        </div>
      </li>
      <li>
        <span class="timeline__num">5</span>
        <div class="timeline__card">
          <h3>Sala Secreta</h3>
          <p>Por último, porque reaproveita os mini-desafios de todos os outros jogos dentro de uma história.</p>
          <div class="timeline__tags"><span>Escape room</span></div>
        </div>
      </li>
    </ol>
    <p class="timeline-nota">A ordem é uma sugestão para discussão. Os pareceres registrados em cada jogo podem reorganizar as etapas.</p>
  </div>
</section>

<!-- ============================== 6. OBSERVAÇÕES GERAIS ============================== -->
<section class="secao geral" id="geral">
  <div class="container">
    <div class="secao__cab">
      <span class="kicker">Observações gerais</span>
      <h2>Sobre a proposta como um todo</h2>
      <p>Preço, prioridade, parcerias, nomes, o que faltou: este bloco é para a conversa sobre a linha inteira, não sobre um jogo específico.</p>
    </div>
    <article class="jogo" data-slug="geral">
      <header class="jogo__topo">
        <span class="jogo__num">★</span>
        <div>
          <p class="jogo__tipo">Proposta completa</p>
          <h3 class="jogo__nome">Gamifica 3D — Nova linha de jogos</h3>
        </div>
        <div class="jogo__selos"><?= $selo('geral') ?></div>
      </header>
      <?= $bloco('geral', 'Observações gerais', 'Preço, prioridade, parcerias, o que for…') ?>
    </article>
  </div>
</section>

</main>

<!-- ============================== 7. RODAPÉ ============================== -->
<footer class="rodape">
  <div class="container rodape__inner">
    <div><strong>IT Thrive</strong> · <a href="https://itthrive.com.br" rel="noopener">itthrive.com.br</a></div>
    <div>Gamifica 3D — Nova linha de jogos · Proposta para discussão entre os sócios · <?= e(APP_DATA) ?></div>
  </div>
</footer>

<!-- ============================== PAINEL RESUMO ============================== -->
<dialog class="resumo" id="resumo" aria-labelledby="resumo-titulo">
  <div class="resumo__cab">
    <div>
      <span class="kicker">Painel resumo</span>
      <h2 id="resumo-titulo">Pareceres por jogo</h2>
    </div>
    <button type="button" class="resumo__fechar" aria-label="Fechar">✕</button>
  </div>
  <div class="resumo__rolagem">
    <?= resumo_tabela_html($resumo) ?>
  </div>
  <div class="resumo__acoes">
    <a class="btn btn--primario" href="exportar-csv.php">Exportar observações (CSV)</a>
    <button type="button" class="btn btn--contorno" id="imprimir">Imprimir / salvar em PDF</button>
    <span class="texto-mudo resumo__nota">Atualiza sozinho a cada observação salva.</span>
  </div>
</dialog>

<!-- Resumo só para impressão (o diálogo não é impresso) -->
<section class="resumo-impressao" aria-hidden="true">
  <h2>Resumo dos pareceres</h2>
  <?= resumo_tabela_html($resumo, 'resumo-impressao-tabela') ?>
</section>

<!-- ============================== LIGHTBOX ============================== -->
<div class="lightbox" id="lightbox" role="dialog" aria-modal="true" aria-label="Imagem ampliada" hidden>
  <button type="button" class="lightbox__fechar" aria-label="Fechar">✕</button>
  <figure class="lightbox__figura">
    <img src="" alt="" class="lightbox__img">
    <figcaption class="lightbox__legenda"></figcaption>
  </figure>
</div>

<div class="toast" id="toast" role="status" aria-live="polite"></div>

<script>
  window.PJ = {
    csrf: document.querySelector('meta[name="csrf"]').content,
    usuario: <?= json_encode(['id' => $usuario['id'], 'nome' => $usuario['nome'], 'perfil' => $usuario['perfil']], JSON_UNESCAPED_UNICODE) ?>,
    api: 'api/',
    pareceres: <?= json_encode(PARECERES, JSON_UNESCAPED_UNICODE) ?>
  };
</script>
<script src="js/app.js"></script>
<script src="js/observacoes.js"></script>
<script src="js/resumo.js"></script>
</body>
</html>
