# -*- coding: utf-8 -*-
"""
gerar-imagens.py — gera as 9 imagens modelo (SVG) da proposta Gamifica 3D
em proposta-jogos/img/. Rode com:  python3 ferramentas/gerar-imagens.py

As imagens são ilustrativas: cenário isométrico low-poly, o mesmo personagem
em todas e um HUD de exemplo. Nenhum recurso de terceiros.
"""
import os
import sys
import random

sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))
from iso import *  # noqa

RAIZ = os.path.join(os.path.dirname(os.path.dirname(os.path.abspath(__file__))), "proposta-jogos", "img")
os.makedirs(RAIZ, exist_ok=True)


def salvar(nome, svg):
    caminho = os.path.join(RAIZ, nome)
    with open(caminho, "w", encoding="utf-8") as f:
        f.write(svg)
    print(f"  {nome}  ({len(svg.encode('utf-8')) / 1024:.1f} KB)")


def carimbo(sc, texto="Imagem modelo · ilustrativa", cor=NAVY):
    sc.fg.append(f'<text x="{sc.w - 24}" y="{sc.h - 18}" text-anchor="end" font-family="{FONT}" font-size="18" fill="{cor}" opacity=".55">{esc(texto)}</text>')


# =====================================================================
# 1. Ilha das Palavras (referência)
# =====================================================================
def ilha_das_palavras():
    sc = Scene(ox=800, oy=350, u=60)
    sc.sky("#BFE3F0", "#E7F4F9")
    sc.clouds([(260, 120, 1.1), (1250, 90, 1.3), (1480, 230, .8)])
    # mar
    sc.bg.append(f'<rect x="0" y="420" width="1600" height="480" fill="{AZUL_CLARO}"/>')
    sc.bg.append(f'<rect x="0" y="420" width="1600" height="480" fill="url(#sea)"/>')
    sc.defs.append(f'<linearGradient id="sea" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#7FB4E6"/><stop offset="1" stop-color="{AZUL}"/></linearGradient>')
    for i, (x, y) in enumerate([(120, 560), (300, 700), (1300, 640), (1450, 780), (900, 840), (60, 830)]):
        sc.bg.append(f'<path d="M{x},{y} q20,-10 40,0 q20,10 40,0" stroke="{PAPEL}" stroke-width="4" fill="none" opacity=".6" stroke-linecap="round"/>')
    # ilha: areia + grama
    sc.tile(-5.2, -5.2, 10.4, 10.4, AREIA, key=-100)
    sc.box(-5.2, -5.2, -0.5, 10.4, 10.4, 0.5, AREIA, key=-101, top=AREIA)
    sc.tile(-4.4, -4.4, 8.8, 8.8, GRAMA, key=-99)
    # grade da cruzadinha (ladrilhos brancos): ESCOLA (h), SALA (v), LIVRO (v), PROVA (h)
    celulas = {}
    def palavra(p, r, c, dr, dc, cheia):
        for i, ch in enumerate(p):
            celulas[(r + i * dr, c + i * dc)] = (ch, cheia if isinstance(cheia, bool) else cheia(i))
    palavra("ESCOLA", 0, 0, 0, 1, True)
    palavra("SALA", 0, 1, 1, 0, True)
    palavra("LIVRO", 0, 4, 1, 0, True)
    palavra("PROVA", 3, 3, 0, 1, True)
    palavra("ALUNO", 3, 7, 1, 0, lambda i: i <= 2)   # N e O ainda caindo do céu
    palavra("GIZ", 5, 0, 0, 1, False)                 # ainda por resolver
    ox0, oy0 = -3.2, -2.8
    for (r, c), (ch, cheia) in celulas.items():
        x, y = ox0 + c * 0.8, oy0 + r * 0.8
        sc.box(x, y, 0, 0.72, 0.72, 0.18, PAPEL, top=BRANCO, stroke=mix(PAPEL, NAVY, .25), sw=1)
        if cheia:
            sc.text_at(x + 0.36, y + 0.36, 0.2, ch, size=26, color=NAVY, weight=800, key=(x + y + 0.9))
    # totens numerados
    for (x, y, n) in [(-1.6, -4.0, "1"), (-3.8, 1.0, "2"), (3.2, -2.4, "3")]:
        sc.box(x, y, 0, 0.5, 0.5, 1.4, MADEIRA, stroke=MADEIRA_ESC, sw=1)
        sc.box(x - 0.05, y - 0.05, 1.4, 0.6, 0.6, 0.25, OURO)
        sc.text_at(x + 0.25, y + 0.25, 1.68, n, size=24, color=NAVY, weight=800, key=(x + y + 2))
    # letras caindo do céu (cubos no ar)
    for (x, y, z, ch) in [(ox0 + 7 * 0.8, oy0 + 6 * 0.8, 1.1, "N"), (ox0 + 7 * 0.8, oy0 + 7 * 0.8, 1.9, "O")]:
        sc.box(x + 0.1, y + 0.1, z, 0.55, 0.55, 0.55, OURO, key=(x + y + 5))
        sc.text_at(x + 0.38, y + 0.38, z + 0.6, ch, size=24, color=NAVY, weight=800, key=(x + y + 5.1))
        sx, sy = sc.P(x + 0.38, y + 0.38, z + 1.2)
        sc.add(f'<path d="M{sx:.0f},{sy:.0f} v-70 M{sx - 18:.0f},{sy - 10:.0f} v-45 M{sx + 18:.0f},{sy - 10:.0f} v-45" stroke="{OURO}" stroke-width="4" stroke-linecap="round" opacity=".6"/>', x + y + 5.2)
    # cenário
    sc.tree(-3.8, 3.6, 1.0)
    sc.tree(3.6, -3.9, 1.1)
    sc.tree(4.3, 1.4, 0.9, color=VERDE_CLARO)
    sc.tree(-1.2, 4.0, .8, color=VERDE2)
    sc.bush(1.6, 4.2)
    sc.bush(-4.2, -1.0)
    sc.rock(3.8, 0.6, .45)
    # caminho que se abre (ladrilhos de pedra)
    for i in range(4):
        sc.tile(2.8 + i * 0.5, 2.0 + i * 0.1, 0.42, 0.42, mix(CINZA, PAPEL, .4), z=0.02, key=(2.8 + i * .5 + 2.0 + i * .1 + .3))
    # personagem na grade, perto do totem 2
    sc.character(-1.4, 0.9, 0.0, scale=0.85)
    # HUD
    sc.hud_pill(28, 24, "1.240 pontos", icon="★")
    sc.hud_pill(28, 92, "Palavras: 3 / 8", fill=VERDE)
    sc.hud_banner(560, 22, 560, 74, "A sala onde se aprende (6 letras)", sub="DICA 2")
    sc.hud_hint(1540, 70)
    sc.hud_joystick(130, 760)
    carimbo(sc)
    return sc.render("Ilha das Palavras — palavra cruzada 3D (imagem modelo)")


# =====================================================================
# 2. Trilha dos Portais
# =====================================================================
def trilha_dos_portais():
    sc = Scene(ox=800, oy=420, u=58)
    sc.sky("#C9E4D3", "#EEF6EF")
    sc.clouds([(200, 100, 1), (1350, 120, 1.2)])
    sc.tile(-7, -7, 14, 14, GRAMA2, key=-100)
    sc.tile(-4.6, -4.6, 9.2, 9.2, GRAMA, key=-99)  # clareira
    # trilha de entrada (terra) vindo da frente
    for i in range(5):
        sc.tile(1.6 + i * .5, 1.6 + i * .5, .7, .7, mix(MADEIRA, PAPEL, .45), z=.01, key=-98 + i * .01)
    # árvores ao redor
    rnd = random.Random(3)
    for (x, y) in [(-6, -5.5), (-5.5, -3), (-6.2, 0), (-5.6, 2.5), (-4.5, 4.8), (-2, 5.6), (1.0, 6.2),
                   (5.8, 2.2), (6.2, -0.5), (5.6, -3), (4.6, -5.6), (1.8, -6.2), (-1.5, -6.0), (-4, -6.4), (-0.5, 6.4), (5.6, 5.4)]:
        sc.tree(x, y, rnd.uniform(.85, 1.25), color=rnd.choice([VERDE, VERDE2, VERDE_CLARO]))
    sc.bush(-3.6, 3.4); sc.bush(3.8, -3.8, color=VERDE_CLARO); sc.rock(3.2, 3.6, .5)
    # 4 portais em arco (fundo da clareira)
    portais = [(-4.2, -0.6, "A", "Marte"), (-2.6, -2.4, "B", "Júpiter"), (-0.4, -3.4, "C", "Vênus"), (2.0, -3.4, "D", "Saturno")]
    for (x, y, letra, alt) in portais:
        cor = VERDE if letra != "B" else OURO
        sc.box(x, y, 0, .45, .45, 2.2, mix(CINZA, PAPEL, .3))
        sc.box(x + 1.3, y, 0, .45, .45, 2.2, mix(CINZA, PAPEL, .3))
        sc.box(x - .1, y - .05, 2.2, 1.95, .55, .4, cor, key=(x + y + 3))
        # "névoa" do portal
        p1 = sc.P(x + .45, y + .25, 0); p2 = sc.P(x + 1.3, y + .25, 0); p3 = sc.P(x + 1.3, y + .25, 2.2); p4 = sc.P(x + .45, y + .25, 2.2)
        sc.add(f'<polygon points="{p1[0]:.0f},{p1[1]:.0f} {p2[0]:.0f},{p2[1]:.0f} {p3[0]:.0f},{p3[1]:.0f} {p4[0]:.0f},{p4[1]:.0f}" fill="{lighten(cor, .55)}" opacity=".75"/>', x + y + .5)
        sc.text_at(x + .88, y + .22, 2.42, letra, size=30, color=NAVY if cor == OURO else PAPEL, weight=800, key=(x + y + 3.2))
        # placa com a alternativa, à frente do portal
        sc.box(x + .35, y + .7, 0, 1.1, .3, .55, PAPEL, top=BRANCO, stroke=mix(PAPEL, NAVY, .3), sw=1, key=(x + y + 4))
        sc.text_at(x + .9, y + .85, .62, alt, size=20, color=NAVY, weight=700, key=(x + y + 4.2))
    # personagem no centro, olhando os portais
    sc.character(-0.3, 0.9, 0, scale=.9)
    # HUD
    sc.hud_banner(380, 20, 840, 90, "Qual é o maior planeta do Sistema Solar?", sub="QUESTÃO 3 DE 10 · CIÊNCIAS", size=32)
    sc.hud_pill(28, 24, "320 pontos", icon="★")
    sc.hud_pill(28, 92, "Acertos: 2", fill=VERDE)
    sc.hud_hint(1540, 70)
    sc.hud_progress(28, 860, 300, .3, label="Trilha")
    carimbo(sc)
    return sc.render("Trilha dos Portais — simulado temático (imagem modelo)")


# =====================================================================
# 3. Campo das Letras
# =====================================================================
def campo_das_letras():
    sc = Scene(ox=760, oy=250, u=58)
    sc.sky("#CDE6CF", "#F1F7EE")
    sc.clouds([(240, 90, .9), (1200, 70, 1.1)])
    sc.tile(-9, -9, 18, 18, GRAMA2, key=-100)
    sc.tile(-6, -6, 12, 12, GRAMA, key=-99)
    rnd = random.Random(11)
    letras = "ABCDEFGHIJLMNOPRSTUV"
    cols, rows = 10, 8
    grade = [[rnd.choice(letras) for _ in range(cols)] for _ in range(rows)]
    # palavras escondidas
    def poe(palavra, r, c, dr, dc):
        for i, ch in enumerate(palavra):
            grade[r + i * dr][c + i * dc] = ch
    poe("PLANETA", 2, 1, 0, 1)      # horizontal (acesa)
    poe("LUA", 0, 8, 1, 0)          # vertical
    poe("SOL", 5, 2, 0, 1)
    poe("ESTRELA", 1, 2, 1, 1)      # diagonal
    ox0, oy0, t = -4.0, -3.2, 0.8
    acesa = {(2, c) for c in range(1, 5)}   # PLAN já pisados
    encontrada = {(1 + i, 2 + i) for i in range(7)}  # ESTRELA já encontrada
    for r in range(rows):
        for c in range(cols):
            x, y = ox0 + c * t, oy0 + r * t
            if (r, c) in acesa:
                cor, tcor, h = OURO, NAVY, .22
            elif (r, c) in encontrada:
                cor, tcor, h = VERDE, PAPEL, .12
            else:
                cor, tcor, h = PAPEL, NAVY, .12
            sc.box(x, y, 0, .72, .72, h, cor, top=lighten(cor, .25) if cor != PAPEL else BRANCO, stroke=mix(cor, NAVY, .3), sw=1)
            sc.text_at(x + .36, y + .36, h + .02, grade[r][c], size=26, color=tcor, weight=800, key=(x + y + .9))
    # árvores / detalhes
    sc.tree(-5.4, 4.6, 1.1); sc.tree(4.9, -5.4, 1.0, color=VERDE_CLARO); sc.tree(5.4, 3.2, .9, color=VERDE2)
    sc.bush(-5.2, -4.6); sc.rock(4.8, 4.4, .5)
    # personagem sobre a letra "E" de PLANETA (próxima casa), correndo
    sc.character(ox0 + 4.8 * t - .2, oy0 + 2 * t - .02, .22, scale=.72, run=True)
    # painel lateral de palavras
    painel = f'<g><rect x="1210" y="140" width="360" height="330" rx="18" fill="{NAVY}" opacity=".94"/>'
    painel += f'<text x="1240" y="186" font-family="{FONT}" font-weight="700" font-size="20" fill="{OURO}" letter-spacing="2">PALAVRAS · 1 / 4</text>'
    itens = [("ESTRELA", True), ("PLANETA", False), ("LUA", False), ("SOL", False)]
    for i, (p, ok) in enumerate(itens):
        yy = 232 + i * 54
        cor = VERDE_CLARO if ok else PAPEL
        painel += f'<circle cx="1250" cy="{yy - 8}" r="10" fill="{VERDE_CLARO if ok else "none"}" stroke="{PAPEL}" stroke-width="2"/>'
        painel += f'<text x="1276" y="{yy}" font-family="{FONT}" font-weight="600" font-size="26" fill="{cor}"{" text-decoration=\"line-through\"" if ok else ""}>{p}</text>'
    painel += "</g>"
    sc.fg.append(painel)
    sc.hud_pill(28, 24, "02:14", icon="◷")
    sc.hud_pill(28, 92, "Recorde: 01:48", fill=VERDE)
    sc.hud_hint(1540, 70)
    sc.hud_joystick(130, 760)
    carimbo(sc)
    return sc.render("Campo das Letras — caça-palavras 3D (imagem modelo)")


# =====================================================================
# 4. Sala Secreta
# =====================================================================
def sala_secreta():
    sc = Scene(ox=800, oy=300, u=70)
    sc.bg.append(f'<rect width="1600" height="900" fill="{NAVY3}"/>')
    W = 7.0
    # piso
    sc.tile(0, 0, W, W, mix(MADEIRA, NAVY, .35), key=-100)
    for i in range(7):
        for j in range(7):
            if (i + j) % 2 == 0:
                sc.tile(i, j, 1, 1, mix(MADEIRA, NAVY, .25), z=.001, key=-99)
    # paredes (y = 0 ao fundo-esquerda; x = 0 ao fundo-direita)
    pa = sc.pts([(0, 0, 0), (W, 0, 0), (W, 0, 4), (0, 0, 4)])
    pb = sc.pts([(0, 0, 0), (0, W, 0), (0, W, 4), (0, 0, 4)])
    sc.add(f'<polygon points="{pa}" fill="{mix(VERDE2, NAVY, .35)}"/>', -98)
    sc.add(f'<polygon points="{pb}" fill="{mix(VERDE2, NAVY, .5)}"/>', -98)
    # rodapé
    sc.add(f'<polygon points="{sc.pts([(0, 0, 0), (W, 0, 0), (W, 0, .25), (0, 0, .25)])}" fill="{MADEIRA_ESC}"/>', -97)
    sc.add(f'<polygon points="{sc.pts([(0, 0, 0), (0, W, 0), (0, W, .25), (0, 0, .25)])}" fill="{MADEIRA_ESC}"/>', -97)
    # porta na parede do fundo (parede y=0), trancada
    sc.add(f'<polygon points="{sc.pts([(4.3, 0, 0), (5.6, 0, 0), (5.6, 0, 2.6), (4.3, 0, 2.6)])}" fill="{MADEIRA_ESC}"/>', -96)
    sc.add(f'<polygon points="{sc.pts([(4.45, 0, .15), (5.45, 0, .15), (5.45, 0, 2.45), (4.45, 0, 2.45)])}" fill="{MADEIRA}"/>', -96)
    lx_, ly_ = sc.P(4.95, 0, 1.3)
    sc.add(f'<g><path d="M{lx_ - 11:.0f},{ly_ - 6:.0f} v-12 a11,11 0 0 1 22,0 v12" fill="none" stroke="{OURO}" stroke-width="5"/>'
           f'<rect x="{lx_ - 16:.0f}" y="{ly_ - 8:.0f}" width="32" height="26" rx="5" fill="{OURO}"/><circle cx="{lx_:.0f}" cy="{ly_ + 5:.0f}" r="4" fill="{NAVY}"/></g>', -95)
    # pista na parede (quadro)
    sc.add(f'<polygon points="{sc.pts([(0.8, 0, 2.1), (3.0, 0, 2.1), (3.0, 0, 3.4), (0.8, 0, 3.4)])}" fill="{PAPEL}" stroke="{OURO}" stroke-width="6"/>', -96)
    px, py = sc.P(1.9, 0, 2.95)
    sc.add(f'<text x="{px:.0f}" y="{py - 6:.0f}" text-anchor="middle" font-family="{FONT}" font-weight="800" font-size="30" fill="{NAVY}">3 · 7 · ?</text>'
           f'<text x="{px:.0f}" y="{py + 24:.0f}" text-anchor="middle" font-family="{FONT}" font-size="15" fill="{VERDE}">some os lados</text>', -95)
    # cadeado de código na parede x = 0
    sc.add(f'<polygon points="{sc.pts([(0, 2.2, 1.4), (0, 3.4, 1.4), (0, 3.4, 2.3), (0, 2.2, 2.3)])}" fill="{NAVY2}" stroke="{OURO}" stroke-width="4"/>', -96)
    for i, d in enumerate(["3", "7", "_"]):
        cx, cy = sc.P(0, 2.45 + (2 - i) * .35, 1.85)
        sc.add(f'<rect x="{cx - 14:.0f}" y="{cy - 18:.0f}" width="28" height="36" rx="4" fill="{PAPEL}"/>'
               f'<text x="{cx:.0f}" y="{cy + 9:.0f}" text-anchor="middle" font-family="{FONT}" font-weight="800" font-size="24" fill="{NAVY}">{d}</text>', -95)
    # estante (encostada na parede x=0)
    sc.box(.1, 4.2, 0, .6, 2.2, 3.0, MADEIRA_ESC)
    for k in range(3):
        sc.box(.15, 4.3, .5 + k * .9, .5, 2.0, .12, MADEIRA, key=(.4 + 5.3 + .5 + k))
        for j, col in enumerate([VERMELHO, AZUL, OURO, VERDE_CLARO, ROXO, PAPEL][: 5 + (k % 2)]):
            sc.box(.2, 4.4 + j * .36, .62 + k * .9, .42, .28, .55 + (j % 3) * .1, col, key=(.4 + 5.3 + .62 + k + .01 * j))
    # baú com cadeado
    sc.box(3.6, 3.0, 0, 1.4, 1.0, .7, MADEIRA, stroke=MADEIRA_ESC, sw=1.5)
    sc.box(3.55, 2.95, .7, 1.5, 1.1, .35, MADEIRA_ESC, top=MADEIRA)
    sc.box(4.15, 3.95, .45, .3, .12, .4, OURO, key=(3.6 + 3.0 + 1.5 + 2))
    # mesa com lupa/livro
    sc.box(1.6, 5.2, 0, 1.3, .9, .8, mix(MADEIRA, PAPEL, .2))
    sc.box(1.9, 5.4, .8, .6, .45, .1, VERMELHO, key=(1.6 + 5.2 + 1.2))
    # personagem examinando o baú
    sc.character(2.5, 4.3, 0, scale=.9, key=9)
    # luz da lanterna (círculo suave)
    lx, ly = sc.P(3.9, 3.4, .5)
    sc.defs.append(f'<radialGradient id="luz" cx="50%" cy="50%" r="50%"><stop offset="0" stop-color="{OURO}" stop-opacity=".35"/><stop offset="1" stop-color="{OURO}" stop-opacity="0"/></radialGradient>')
    sc.fg.append(f'<ellipse cx="{lx:.0f}" cy="{ly:.0f}" rx="230" ry="150" fill="url(#luz)"/>')
    # HUD
    sc.hud_pill(28, 24, "04:12", icon="◷")
    sc.hud_pill(28, 92, "Recorde: 06:40", fill=VERDE)
    sc.hud_banner(480, 20, 640, 80, "Cadeados abertos: 1 de 3", sub="LABORATÓRIO DO PROF. ÍCARO", size=28)
    sc.hud_hint(1540, 70, label_color=PAPEL)
    # dica flutuante sobre o baú
    bx, by = sc.P(4.3, 3.5, 2.3)
    sc.fg.append(f'<g><rect x="{bx - 120:.0f}" y="{by - 70:.0f}" width="240" height="48" rx="12" fill="{PAPEL}" stroke="{NAVY}" stroke-width="2"/>'
                 f'<text x="{bx:.0f}" y="{by - 39:.0f}" text-anchor="middle" font-family="{FONT}" font-weight="600" font-size="20" fill="{NAVY}">Examinar o baú [E]</text></g>')
    carimbo(sc, cor=PAPEL)
    return sc.render("Sala Secreta — escape room (imagem modelo)")


# =====================================================================
# 5. Ponte das Frases
# =====================================================================
def ponte_das_frases():
    sc = Scene(ox=800, oy=270, u=62)
    sc.sky("#CFE7EE", "#EEF6F3")
    sc.clouds([(300, 110, 1), (1300, 100, 1.2)])
    # margens
    sc.tile(-8, -8, 16, 16, GRAMA2, key=-100)
    sc.tile(-7, -7, 14, 14, GRAMA, key=-99)
    # rio (faixa diagonal em y)
    sc.tile(-8, -1.8, 16, 3.6, AZUL, key=-98)
    sc.tile(-8, -1.5, 16, 3.0, mix(AZUL, AZUL_CLARO, .4), key=-97)
    for i in range(6):
        x = -6 + i * 2.1
        p = sc.P(x, -.2 + (i % 2) * .6, 0)
        sc.add(f'<path d="M{p[0]:.0f},{p[1]:.0f} q18,-8 36,0 q18,8 36,0" stroke="{PAPEL}" stroke-width="3" fill="none" opacity=".7"/>', -96)
    # ponte: tábuas ao longo de y (atravessa o rio) em x≈0
    palavras = ["O", "gato", "subiu", "no", None, "e", "miou"]
    y0, passo, prof = -3.3, .95, .8
    for i, p in enumerate(palavras):
        y = y0 + i * passo
        if p is None:
            continue  # buraco
        sc.box(-1.1, y, .35, 2.2, prof, .14, MADEIRA, top=lighten(MADEIRA, .15), stroke=MADEIRA_ESC, sw=1)
        sc.text_at(0, y + prof / 2, .5, p, size=26, color=PAPEL, weight=700, key=(y + prof / 2 + .6))
    # cabos / corrimão
    for xx in (-1.2, 1.2):
        a = sc.P(xx, y0 - .2, 1.0); b = sc.P(xx, y0 + 7 * passo, 1.0)
        sc.add(f'<line x1="{a[0]:.0f}" y1="{a[1]:.0f}" x2="{b[0]:.0f}" y2="{b[1]:.0f}" stroke="{MADEIRA_ESC}" stroke-width="5"/>', 10)
        for yy in (y0 - .2, y0 + 7 * passo):
            sc.box(xx - .08, yy - .08, 0, .16, .16, 1.0, MADEIRA_ESC, key=(xx + yy + 2))
    # contorno tracejado no buraco
    hy = y0 + 4 * passo
    hp = sc.P(0, hy + prof / 2, .4)
    sc.add(f'<polygon points="{sc.pts([(-1.1, hy, .36), (1.1, hy, .36), (1.1, hy + prof, .36), (-1.1, hy + prof, .36)])}" fill="none" stroke="{OURO}" stroke-width="4" stroke-dasharray="10 8"/>', 11)
    sc.add(f'<text x="{hp[0]:.0f}" y="{hp[1] + 8:.0f}" text-anchor="middle" font-family="{FONT}" font-weight="800" font-size="30" fill="{OURO}">?</text>', 11.1)
    # blocos de palavras espalhados na margem de cá (y > 1.5)
    blocos = [(3.0, 2.6, "telhado", OURO), (-3.4, 3.0, "cadeira", AZUL), (4.0, 4.4, "rio", ROXO), (-1.8, 4.8, "sapato", VERMELHO)]
    for (x, y, p, col) in blocos:
        sc.box(x, y, 0, 1.0, 1.0, .7, col)
        sc.text_at(x + .5, y + .5, .72, p, size=22, color=NAVY if col == OURO else PAPEL, weight=700, key=(x + y + 1.2))
    # personagem carregando "muro" rumo ao buraco
    sc.character(1.9, 3.3, 0, scale=.9, carrying=("muro", VERDE))
    # árvores
    sc.tree(-5.5, -5.5, 1.1); sc.tree(4.5, -5.8, .95, color=VERDE2); sc.tree(-5.8, 4.2, 1.0, color=VERDE_CLARO); sc.tree(6.4, 2.4, 1.1)
    sc.bush(4.6, -3.6); sc.rock(-4.6, -3.2, .5); sc.bush(-3.0, -4.4, color=VERDE)
    # HUD
    sc.hud_banner(380, 20, 840, 90, "O gato subiu no ____ e miou.", sub="FRASE 2 DE 6 · PORTUGUÊS", size=34)
    sc.hud_pill(28, 24, "180 pontos", icon="★")
    sc.hud_pill(28, 92, "Pontes: 1 / 6", fill=VERDE)
    sc.hud_hint(1540, 70)
    carimbo(sc)
    return sc.render("Ponte das Frases — complete a frase (imagem modelo)")


# =====================================================================
# 6. Arena 60
# =====================================================================
def arena_60():
    sc = Scene(ox=800, oy=400, u=62)
    sc.sky("#F3DDB8", "#FBF1DF")
    sc.clouds([(220, 120, 1), (1380, 140, 1.1)])
    sc.tile(-8, -8, 16, 16, mix(AREIA, GRAMA, .35), key=-100)
    # arena circular: arquibancada (anéis) + piso
    sc.disc(0, 0, 0, 5.2, mix(CINZA, PAPEL, .3), key=-99)
    sc.disc(0, 0, 0, 4.6, mix(VERDE2, PAPEL, .1), key=-98)
    sc.disc(0, 0, 0, 4.0, GRAMA, key=-97)
    sc.disc(0, 0, 0, 1.2, mix(GRAMA, PAPEL, .3), key=-96)
    # muretas (blocos ao redor)
    import math as m
    for i in range(14):
        a = 2 * m.pi * i / 14
        x, y = 4.9 * m.cos(a), 4.9 * m.sin(a)
        sc.box(x - .3, y - .3, 0, .6, .6, .6 if i % 2 else .9, mix(CINZA, PAPEL, .15))
    # bandeiras
    for (x, y, col) in [(-4.6, -1.5, OURO), (1.5, -4.6, VERMELHO), (4.4, 1.8, AZUL)]:
        sc.box(x, y, 0, .12, .12, 2.6, MADEIRA_ESC)
        p = sc.P(x + .06, y + .06, 2.6); q = sc.P(x + .06, y + .06, 2.1)
        sc.add(f'<polygon points="{p[0]:.0f},{p[1]:.0f} {p[0] + 70:.0f},{p[1] + 15:.0f} {q[0]:.0f},{q[1]:.0f}" fill="{col}"/>', x + y + 3)
    # esferas caindo com palavras
    esferas = [(-2.4, -0.6, 2.0, "baleia", VERDE), (-0.8, -3.2, 2.0, "cobra", ROXO), (2.6, 1.2, 1.4, "cão", VERDE),
               (-0.2, 2.6, 2.6, "sapo", VERMELHO), (-3.0, 1.6, 1.0, "gato", VERDE), (2.2, -1.2, 3.2, "tubarão", AZUL), (0.8, 0.6, 1.0, "vaca", VERDE)]
    for (x, y, z, p, col) in esferas:
        sc.shadow(x, y, .4, opacity=.15)
        sc.sphere(x, y, z, .5, col, label=p, fs=18)
        sx, sy = sc.P(x, y, z + .7)
        sc.add(f'<path d="M{sx - 12:.0f},{sy - 20:.0f} v-40 M{sx + 12:.0f},{sy - 20:.0f} v-40" stroke="{col}" stroke-width="3" opacity=".5" stroke-linecap="round"/>', x + y + 20)
    # estouro (confete) de uma esfera pega
    ex, ey = sc.P(0.6, 1.8, .9)
    conf = "".join(f'<circle cx="{ex + dx}" cy="{ey + dy}" r="{r}" fill="{c}"/>' for (dx, dy, r, c) in
                   [(-40, -30, 6, OURO), (35, -45, 5, VERDE), (50, 10, 7, VERMELHO), (-55, 15, 5, AZUL), (10, -60, 6, ROXO), (-20, 30, 4, OURO)])
    sc.add(f'<g>{conf}<text x="{ex:.0f}" y="{ey - 70:.0f}" text-anchor="middle" font-family="{FONT}" font-weight="800" font-size="28" fill="{VERDE}">+10</text></g>', 30)
    # personagem correndo
    sc.character(-1.2, 0.8, 0, scale=.95, run=True)
    # HUD: cronômetro grande
    sc.fg.append(f'<g><circle cx="800" cy="92" r="70" fill="{NAVY}" opacity=".94"/><circle cx="800" cy="92" r="70" fill="none" stroke="{OURO}" stroke-width="6" stroke-dasharray="308 440" transform="rotate(-90 800 92)"/>'
                 f'<text x="800" y="106" text-anchor="middle" font-family="{FONT}" font-weight="800" font-size="44" fill="{PAPEL}">0:42</text></g>')
    sc.hud_banner(1030, 30, 400, 80, "Mamíferos", sub="TEMA DA RODADA", size=30)
    sc.hud_pill(28, 24, "70 pontos", icon="★")
    sc.hud_pill(28, 92, "Recorde: 110", fill=VERDE)
    sc.hud_pill(28, 160, "Rodada 2", fill=OURO, color=NAVY)
    sc.hud_hint(1540, 70)
    carimbo(sc)
    return sc.render("Arena 60 — nada além de um minuto (imagem modelo)")


# =====================================================================
# 7. Roleta do Alfabeto — Modo Solo (tela do aluno)
# =====================================================================
def roleta_solo():
    sc = Scene(ox=800, oy=360, u=62)
    sc.sky("#DCD3EE", "#F4F0FA")
    sc.clouds([(260, 110, 1), (1340, 130, 1.1)])
    sc.tile(-8, -8, 16, 16, mix(GRAMA2, ROXO, .15), key=-100)
    sc.tile(-5.5, -5.5, 11, 11, mix(GRAMA, ROXO, .08), key=-99)
    # roleta gigante (disco vertical de frente, ao fundo) — desenhada em espaço de tela
    cx, cy, R = 800, 250, 170
    import math as m
    letras = list("ABCDEFGHIJLMNOPQRSTUVZ")
    n = len(letras)
    rol = f'<g><ellipse cx="{cx}" cy="{cy + R + 110}" rx="150" ry="40" fill="{NAVY}" opacity=".2"/>'
    rol += f'<rect x="{cx - 26}" y="{cy}" width="52" height="{R + 100}" fill="{MADEIRA_ESC}"/>'
    rol += f'<circle cx="{cx}" cy="{cy}" r="{R + 14}" fill="{NAVY}"/>'
    for i, L in enumerate(letras):
        a0 = 2 * m.pi * i / n - m.pi / 2 - m.pi / n
        a1 = a0 + 2 * m.pi / n
        x0, y0 = cx + R * m.cos(a0), cy + R * m.sin(a0)
        x1, y1 = cx + R * m.cos(a1), cy + R * m.sin(a1)
        col = [OURO, PAPEL, VERDE, PAPEL][i % 4] if L != "M" else OURO_CLARO
        rol += f'<path d="M{cx},{cy} L{x0:.1f},{y0:.1f} A{R},{R} 0 0 1 {x1:.1f},{y1:.1f} z" fill="{col}" stroke="{NAVY}" stroke-width="2"/>'
        am = (a0 + a1) / 2
        tx, ty = cx + R * .8 * m.cos(am), cy + R * .8 * m.sin(am)
        rol += f'<text x="{tx:.1f}" y="{ty + 7:.1f}" text-anchor="middle" font-family="{FONT}" font-weight="800" font-size="20" fill="{NAVY if col != VERDE else PAPEL}">{L}</text>'
    rol += f'<circle cx="{cx}" cy="{cy}" r="52" fill="{NAVY}" stroke="{OURO}" stroke-width="6"/>'
    rol += f'<text x="{cx}" y="{cy + 22}" text-anchor="middle" font-family="{FONT_T}" font-weight="700" font-size="60" fill="{OURO}">M</text>'
    # ponteiro
    rol += f'<polygon points="{cx - 22},{cy - R - 40} {cx + 22},{cy - R - 40} {cx},{cy - R + 10}" fill="{VERMELHO}" stroke="{NAVY}" stroke-width="3"/></g>'
    sc.add(rol, -90)
    # totens de categoria ao redor
    cats = [(-4.2, -.2, "Nome", OURO), (-3.0, 2.6, "Animal", VERDE), (0.2, 3.8, "Fruta", VERMELHO),
            (3.6, 2.2, "Cidade", AZUL), (4.4, -1.0, "Cor", ROXO), (3.2, -3.4, "Objeto", VERDE_CLARO)]
    for (x, y, nome, col) in cats:
        sc.box(x, y, 0, .7, .7, 1.6, mix(CINZA, PAPEL, .2))
        sc.box(x - .1, y - .1, 1.6, .9, .9, .5, col)
        sc.text_at(x + .35, y + .35, 2.14, nome, size=22, color=NAVY if col in (OURO, VERDE_CLARO) else PAPEL, weight=800, key=(x + y + 2.5))
        # ícone de "respondido" para alguns
        if nome in ("Nome", "Animal"):
            px, py = sc.P(x + .35, y + .35, 2.7)
            sc.add(f'<circle cx="{px:.0f}" cy="{py:.0f}" r="18" fill="{VERDE}" stroke="{PAPEL}" stroke-width="3"/><text x="{px:.0f}" y="{py + 7:.0f}" text-anchor="middle" font-family="{FONT}" font-weight="800" font-size="20" fill="{PAPEL}">✓</text>', x + y + 3)
    sc.bush(-4.6, 3.6); sc.bush(4.6, 3.8, color=VERDE); sc.rock(4.4, 3.0, .5)
    sc.tree(-5.2, -5.0, .9, color=VERDE2); sc.tree(5.3, -5.0, .95, color=VERDE)
    # personagem indo ao totem "Fruta"
    sc.character(-.8, 2.2, 0, scale=.9, run=True)
    # caixa de resposta do totem Fruta (balão à direita do totem)
    bx, by = sc.P(1.3, 4.0, 2.2)
    sc.fg.append(f'<g><rect x="{bx + 20:.0f}" y="{by - 32:.0f}" width="300" height="64" rx="14" fill="{PAPEL}" stroke="{NAVY}" stroke-width="2"/>'
                 f'<polygon points="{bx + 20:.0f},{by - 6:.0f} {bx + 20:.0f},{by + 14:.0f} {bx + 2:.0f},{by + 4:.0f}" fill="{PAPEL}" stroke="{NAVY}" stroke-width="2"/>'
                 f'<text x="{bx + 40:.0f}" y="{by + 10:.0f}" font-family="{FONT}" font-weight="600" font-size="26" fill="{NAVY}">Fruta: Ma|</text>'
                 f'<text x="{bx + 300:.0f}" y="{by + 8:.0f}" text-anchor="end" font-family="{FONT}" font-weight="700" font-size="18" fill="{VERDE}">OK ↵</text></g>')
    # HUD
    sc.hud_pill(28, 24, "00:47", icon="◷")
    sc.hud_pill(28, 92, "Rodada 1 de 3 · 60 s", fill=VERDE)
    sc.hud_pill(28, 160, "2 / 6 categorias", fill=OURO, color=NAVY)
    sc.hud_banner(1190, 24, 380, 72, "Letra sorteada: M", sub="MODO SOLO", size=28)
    sc.hud_hint(1540, 170)
    carimbo(sc)
    return sc.render("Roleta do Alfabeto — Modo Solo, tela do aluno (imagem modelo)")


# =====================================================================
# 8. Roleta do Alfabeto — painel do professor (Modo Turma) — tela de interface
# =====================================================================
def roleta_turma_professor():
    W, H = 1600, 900
    o = [f'<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 {W} {H}" width="{W}" height="{H}" role="img" aria-label="Painel do professor — Roleta do Alfabeto, Modo Turma (imagem modelo)">',
         '<title>Painel do professor — Roleta do Alfabeto, Modo Turma (imagem modelo)</title>',
         f'<rect width="{W}" height="{H}" fill="{NAVY}"/>',
         f'<rect width="{W}" height="{H}" fill="url(#bgp)"/>',
         f'<defs><radialGradient id="bgp" cx="20%" cy="0%" r="90%"><stop offset="0" stop-color="{NAVY2}"/><stop offset="1" stop-color="{NAVY}" stop-opacity="0"/></radialGradient></defs>']
    def txt(x, y, s, size=20, fill=PAPEL, w=600, anchor="start", font=FONT, extra=""):
        o.append(f'<text x="{x}" y="{y}" text-anchor="{anchor}" font-family="{font}" font-weight="{w}" font-size="{size}" fill="{fill}"{extra}>{esc(s)}</text>')
    def card(x, y, w, h, fill=PAPEL, r=18, op=1):
        o.append(f'<rect x="{x}" y="{y}" width="{w}" height="{h}" rx="{r}" fill="{fill}" opacity="{op}"/>')
    # cabeçalho
    o.append(f'<rect x="40" y="30" width="44" height="44" rx="12" fill="{OURO}"/>')
    txt(62, 61, "G", 24, NAVY, 800, "middle", FONT_T)
    txt(100, 61, "Roleta do Alfabeto", 30, PAPEL, 600, font=FONT_T)
    txt(372, 61, "· Modo Turma · 7º ano B · Rodada 2 de 4", 22, mix(PAPEL, NAVY, .35), 500)
    # chip STOP
    o.append(f'<rect x="1060" y="30" width="240" height="44" rx="22" fill="{VERDE}"/>')
    o.append(f'<circle cx="1086" cy="52" r="9" fill="{OURO_CLARO}"/>')
    txt(1104, 59, "Regra STOP! ligada", 18, PAPEL, 600)
    o.append(f'<rect x="1320" y="30" width="240" height="44" rx="22" fill="{OURO}"/>')
    txt(1440, 59, "Encerrar rodada", 20, NAVY, 700, "middle")
    # coluna esquerda: letra + cronômetro + código/QR
    card(40, 100, 380, 300, NAVY2)
    txt(230, 140, "LETRA SORTEADA", 16, OURO, 700, "middle", extra=' letter-spacing="3"')
    o.append(f'<circle cx="150" cy="265" r="92" fill="{OURO}"/>')
    txt(150, 300, "M", 110, NAVY, 700, "middle", FONT_T)
    # cronômetro circular
    o.append(f'<circle cx="320" cy="265" r="70" fill="{NAVY}"/><circle cx="320" cy="265" r="70" fill="none" stroke="{VERDE_CLARO}" stroke-width="8" stroke-dasharray="235 440" transform="rotate(-90 320 265)"/>')
    txt(320, 278, "0:32", 38, PAPEL, 800, "middle")
    txt(320, 360, "de 60 s", 16, mix(PAPEL, NAVY, .35), 500, "middle")
    # código + QR
    card(40, 420, 380, 220, PAPEL)
    txt(64, 455, "CÓDIGO DA SALA", 14, VERDE, 700, extra=' letter-spacing="3"')
    txt(64, 515, "KX7P", 58, NAVY, 700, font=FONT_T)
    txt(64, 548, "gamifica.app/roleta", 18, mix(NAVY, PAPEL, .4), 500)
    txt(64, 600, "Entre pelo celular", 16, NAVY, 600)
    txt(64, 622, "ou Chromebook", 16, NAVY, 600)
    # QR falso (padrão determinístico)
    rnd = random.Random(42)
    qx, qy, cell, nq = 250, 440, 7, 25
    o.append(f'<rect x="{qx - 8}" y="{qy - 8}" width="{nq * cell + 16}" height="{nq * cell + 16}" fill="{BRANCO}" rx="6"/>')
    cells = []
    def finder(fx, fy):
        for i in range(7):
            for j in range(7):
                borda = i in (0, 6) or j in (0, 6)
                centro = 2 <= i <= 4 and 2 <= j <= 4
                if borda or centro:
                    cells.append((fx + j, fy + i))
    finder(0, 0); finder(nq - 7, 0); finder(0, nq - 7)
    reservado = set(cells)
    for i in range(nq):
        for j in range(nq):
            if (j, i) in reservado:
                continue
            if (i < 8 and j < 8) or (i < 8 and j >= nq - 8) or (i >= nq - 8 and j < 8):
                continue
            if rnd.random() < .45:
                cells.append((j, i))
    o.append(f'<path d="{" ".join(f"M{qx + c * cell},{qy + r * cell}h{cell}v{cell}h-{cell}z" for (c, r) in cells)}" fill="{NAVY}"/>')
    # alunos conectados
    card(40, 660, 380, 210, NAVY2)
    txt(64, 695, "ALUNOS CONECTADOS · 24 / 27", 14, OURO, 700, extra=' letter-spacing="2"')
    nomes = ["Ana", "Bruno", "Caio", "Duda", "Enzo", "Flávia", "Gabi", "Heitor", "Isa", "João", "Kaique", "Lara", "Miguel", "Nina", "Otto"]
    for i, nm in enumerate(nomes):
        col, row = i % 3, i // 3
        x, y = 64 + col * 120, 728 + row * 24
        pronto = i not in (4, 11, 16)
        o.append(f'<circle cx="{x}" cy="{y - 5}" r="5" fill="{VERDE_CLARO if pronto else OURO}"/>')
        txt(x + 12, y, nm, 15, PAPEL, 500)
    txt(64, 858, "+ 9 alunos   ·   ● respondeu   ● respondendo", 13, mix(PAPEL, NAVY, .4), 500)
    # painel central: respostas por categoria
    card(450, 100, 820, 770, PAPEL)
    txt(476, 140, "RESPOSTAS DA TURMA POR CATEGORIA", 14, VERDE, 700, extra=' letter-spacing="3"')
    txt(1244, 140, "clique para validar as duvidosas", 14, mix(NAVY, PAPEL, .45), 500, "end")
    cats = ["Nome", "Animal", "Fruta", "Cidade", "Cor"]
    colx = [476, 640, 800, 960, 1120]
    o.append(f'<rect x="466" y="158" width="788" height="40" rx="10" fill="{NAVY}"/>')
    for cx_, c in zip(colx, cats):
        txt(cx_ + 10, 185, c, 17, OURO, 700)
    linhas = [
        ("Ana", ["Marina", "Macaco", "Maçã", "Maceió", "Marrom"], [1, 0, 0, 1, 1]),
        ("Bruno", ["Mateus", "Morcego", "Manga", "Manaus", "Magenta"], [0, 1, 0, 1, 2]),
        ("Caio", ["Miguel", "Macaco", "Melancia", "Marília", "Marrom"], [0, 0, 1, 1, 1]),
        ("Duda", ["Manuela", "Mariposa", "Morango", "Mogi", "Mostarda"], [1, 1, 0, 2, 2]),
        ("Enzo", ["Marcos", "—", "Maracujá", "Maceió", "Marrom"], [1, 3, 1, 1, 1]),
        ("Flávia", ["Maria", "Mico", "Mexerica", "Marabá", "Malva"], [0, 1, 1, 1, 2]),
        ("Gabi", ["Murilo", "Mula", "Manga", "Mauá", "Marrom"], [1, 1, 0, 1, 1]),
        ("Heitor", ["Mateus", "Minhoca", "Mamão", "Manaus", "Mel"], [0, 1, 1, 1, 2]),
        ("Isa", ["Melissa", "Macaco", "Maçã", "Montes Claros", "Marrom"], [1, 0, 0, 1, 1]),
        ("João", ["Marina", "Mosquito", "Mirtilo", "Maringá", "Marfim"], [1, 1, 1, 1, 2]),
    ]
    # 0 = repetida (+5), 1 = única (+10), 2 = duvidosa (?), 3 = em branco (0)
    for r, (aluno, resp, st) in enumerate(linhas):
        y = 232 + r * 58
        if r % 2 == 0:
            o.append(f'<rect x="466" y="{y - 30}" width="788" height="52" rx="8" fill="{PAPEL2}"/>')
        txt(476, y, aluno, 17, NAVY, 700)
        for cx_, rr, s in zip(colx, resp, st):
            if cx_ == 476:
                xx = cx_ + 90
            else:
                xx = cx_ + 10
            if s == 3:
                txt(xx, y, "—", 17, mix(NAVY, PAPEL, .5), 500)
                continue
            fill = {0: mix(OURO_CLARO, PAPEL, .3), 1: VERDE_CLARO, 2: mix(AZUL_CLARO, PAPEL, .3)}[s]
            tc = {0: NAVY, 1: PAPEL, 2: NAVY}[s]
            w = 14 + len(rr) * 9.2
            o.append(f'<rect x="{xx - 6}" y="{y - 20}" width="{w:.0f}" height="30" rx="8" fill="{fill}"/>')
            txt(xx + 2, y + 1, rr, 15, tc, 600)
            if s == 2:
                o.append(f'<circle cx="{xx + w + 4:.0f}" cy="{y - 5}" r="10" fill="{AZUL}"/>')
                txt(xx + w + 4, y, "?", 14, PAPEL, 800, "middle")
    # legenda
    ly = 835
    for i, (fill, tc, lab) in enumerate([(VERDE_CLARO, PAPEL, "única · +10"), (mix(OURO_CLARO, PAPEL, .3), NAVY, "repetida · +5"), (mix(AZUL_CLARO, PAPEL, .3), NAVY, "duvidosa · validar"), (PAPEL2, NAVY, "em branco · 0")]):
        x = 476 + i * 196
        o.append(f'<rect x="{x}" y="{ly - 18}" width="26" height="22" rx="6" fill="{fill}" stroke="{mix(NAVY, PAPEL, .6)}" stroke-width="1"/>')
        txt(x + 34, ly, lab, 14, NAVY, 500)
    # coluna direita: ranking
    card(1300, 100, 260, 380, NAVY2)
    txt(1324, 140, "RANKING DA RODADA", 14, OURO, 700, extra=' letter-spacing="2"')
    for i, (nm, pts) in enumerate([("João", 50), ("Duda", 45), ("Flávia", 40), ("Enzo", 35), ("Ana", 35), ("Gabi", 35)]):
        y = 184 + i * 46
        o.append(f'<circle cx="1340" cy="{y - 7}" r="14" fill="{OURO if i < 3 else NAVY}" stroke="{OURO}" stroke-width="2"/>')
        txt(1340, y - 1, str(i + 1), 15, NAVY if i < 3 else PAPEL, 800, "middle")
        txt(1366, y, nm, 18, PAPEL, 600)
        txt(1536, y, str(pts), 18, OURO, 700, "end")
    card(1300, 500, 260, 370, NAVY2)
    txt(1324, 540, "RANKING GERAL", 14, OURO, 700, extra=' letter-spacing="2"')
    for i, (nm, pts) in enumerate([("Duda", 125), ("João", 120), ("Ana", 110), ("Heitor", 95), ("Flávia", 90), ("Caio", 85)]):
        y = 584 + i * 46
        o.append(f'<circle cx="1340" cy="{y - 7}" r="14" fill="{OURO if i < 3 else NAVY}" stroke="{OURO}" stroke-width="2"/>')
        txt(1340, y - 1, str(i + 1), 15, NAVY if i < 3 else PAPEL, 800, "middle")
        txt(1366, y, nm, 18, PAPEL, 600)
        txt(1536, y, str(pts), 18, OURO, 700, "end")
    o.append(f'<text x="{W - 24}" y="{H - 14}" text-anchor="end" font-family="{FONT}" font-size="16" fill="{PAPEL}" opacity=".5">Imagem modelo · ilustrativa · tela projetada na lousa</text>')
    o.append("</svg>\n")
    return "\n".join(o)


# =====================================================================
# 9. Constelação
# =====================================================================
def constelacao():
    sc = Scene(ox=800, oy=330, u=64)
    sc.bg.append(f'<rect width="1600" height="900" fill="{NAVY3}"/>')
    sc.defs.append(f'<linearGradient id="noite" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="{NAVY3}"/><stop offset="1" stop-color="{NAVY2}"/></linearGradient>')
    sc.bg.append('<rect width="1600" height="900" fill="url(#noite)"/>')
    sc.stars(120)
    # lua
    sc.bg.append(f'<circle cx="440" cy="110" r="44" fill="{PAPEL}" opacity=".95"/>')
    # chão
    sc.tile(-8, -8, 16, 16, mix(VERDE2, NAVY, .55), key=-100)
    sc.tile(-5.5, -5.5, 11, 11, mix(VERDE2, NAVY, .4), key=-99)
    # colinas/árvores escuras
    for (x, y, s) in [(-5.6, -4.6, 1.0), (-4.2, -5.6, .8), (5.2, -5.4, 1.1), (6.0, -3.6, .9), (-6.0, 3.4, 1.0), (5.4, 4.4, .9)]:
        sc.tree(x, y, s, color=mix(VERDE2, NAVY, .35), trunk=NAVY2)
    # pilares: figura "Cruzeiro do Sul" (5 estrelas) em coordenadas do chão
    pontos = [(-2.6, 1.6), (0.2, -3.2), (2.8, -0.4), (0.6, 2.6), (1.4, 1.2)]
    ordem_ligada = [0, 1, 2, 3]  # 1-2-3-4 já ligados; 5 pendente
    # rastro luminoso
    def tr(i, j):
        a = sc.P(*pontos[i], 0.05); b = sc.P(*pontos[j], 0.05)
        return a, b
    rastro = ""
    for i, j in [(0, 1), (1, 2), (2, 3)]:
        a, b = tr(i, j)
        rastro += f'<line x1="{a[0]:.0f}" y1="{a[1]:.0f}" x2="{b[0]:.0f}" y2="{b[1]:.0f}" stroke="{OURO}" stroke-width="22" stroke-linecap="round" opacity=".25"/>'
        rastro += f'<line x1="{a[0]:.0f}" y1="{a[1]:.0f}" x2="{b[0]:.0f}" y2="{b[1]:.0f}" stroke="{OURO_CLARO}" stroke-width="7" stroke-linecap="round"/>'
    # próximo trecho (pontilhado)
    a, b = tr(3, 0)
    rastro += f'<line x1="{a[0]:.0f}" y1="{a[1]:.0f}" x2="{b[0]:.0f}" y2="{b[1]:.0f}" stroke="{PAPEL}" stroke-width="4" stroke-dasharray="12 12" opacity=".5"/>'
    sc.add(rastro, -50)
    for i, (x, y) in enumerate(pontos):
        col = OURO if i in ordem_ligada else AZUL_CLARO
        sc.pillar_light(x, y, color=col, h=3.2 if i != 4 else 2.4, r=.22, label=str(i + 1))
    # personagem correndo do pilar 4 em direção ao 1 (fecha a figura)
    sc.character(-1.2, 2.0, 0, scale=.9, run=True)
    # figura revelada "fantasma" no céu (as 5 estrelas da Cruzeiro do Sul)
    ceu = [(1170, 140), (1100, 230), (1210, 290), (1280, 205), (1245, 245)]
    fig = f'<g opacity=".9">'
    for (i, j) in [(0, 2), (1, 3)]:
        fig += f'<line x1="{ceu[i][0]}" y1="{ceu[i][1]}" x2="{ceu[j][0]}" y2="{ceu[j][1]}" stroke="{OURO}" stroke-width="2" stroke-dasharray="6 6" opacity=".6"/>'
    for (x, y) in ceu:
        fig += f'<circle cx="{x}" cy="{y}" r="7" fill="{OURO_CLARO}"/><circle cx="{x}" cy="{y}" r="16" fill="{OURO}" opacity=".25"/>'
    fig += f'<text x="1190" y="340" text-anchor="middle" font-family="{FONT}" font-size="15" fill="{PAPEL}" opacity=".6">figura se revelando…</text></g>'
    sc.add(fig, -98)
    # HUD
    sc.hud_pill(28, 24, "4 / 5 pilares", icon="★", fill=NAVY2)
    sc.hud_pill(28, 92, "01:05 · recorde 00:58", fill=VERDE)
    sc.hud_banner(1140, 24, 430, 72, "Figura: ? ? ?", sub="MODO FIGURA SURPRESA", size=28, fill=NAVY2)
    sc.hud_hint(1540, 170, label_color=PAPEL)
    carimbo(sc, cor=PAPEL)
    return sc.render("Constelação — liga pontos (imagem modelo)")


# =====================================================================
if __name__ == "__main__":
    print("Gerando imagens em", RAIZ)
    salvar("ilha-das-palavras.svg", ilha_das_palavras())
    salvar("trilha-dos-portais.svg", trilha_dos_portais())
    salvar("campo-das-letras.svg", campo_das_letras())
    salvar("sala-secreta.svg", sala_secreta())
    salvar("ponte-das-frases.svg", ponte_das_frases())
    salvar("arena-60.svg", arena_60())
    salvar("roleta-solo.svg", roleta_solo())
    salvar("roleta-turma-professor.svg", roleta_turma_professor())
    salvar("constelacao.svg", constelacao())
    print("Pronto.")
