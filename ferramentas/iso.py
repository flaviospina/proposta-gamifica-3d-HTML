# -*- coding: utf-8 -*-
"""
iso.py — mini-biblioteca para desenhar cenas isométricas low-poly em SVG.
Usada por gerar-imagens.py para produzir as imagens modelo da proposta.
Não faz parte da pasta publicada (proposta-jogos/).

Projeção: ponto do mundo (x, y, z) -> tela:
  sx = ox + (x - y) * u * 0.8660
  sy = oy + (x + y) * u * 0.5 - z * u
A câmera olha do canto (+x, +y): as faces verticais visíveis são x = máx (direita)
e y = máx (esquerda).
"""
from __future__ import annotations
import math
from dataclasses import dataclass, field

# ---------- Paleta da marca + complementares ----------
NAVY = "#161F38"
NAVY2 = "#223055"
NAVY3 = "#0F1628"
PAPEL = "#FBF7EF"
PAPEL2 = "#F3EDE0"
VERDE = "#286456"
VERDE2 = "#1F4F44"
VERDE_CLARO = "#4F9A82"
OURO = "#E0A537"
OURO_ESC = "#B8831F"
OURO_CLARO = "#F6D386"
AZUL = "#3B6FB6"
AZUL_CLARO = "#8FB8EA"
CEU = "#CFE6F2"
AREIA = "#E8D7A8"
MADEIRA = "#A9743F"
MADEIRA_ESC = "#7E5429"
CINZA = "#9AA0AE"
VERMELHO = "#D1574C"
ROSA = "#E88AA4"
ROXO = "#7B5EA7"
BRANCO = "#FFFFFF"
GRAMA = "#5FAE6B"
GRAMA2 = "#3F8A4C"

FONT = "Inter, 'Segoe UI', Arial, Helvetica, sans-serif"
FONT_T = "Fraunces, Georgia, 'Times New Roman', serif"


def _hex(c: str):
    c = c.lstrip("#")
    return tuple(int(c[i:i + 2], 16) for i in (0, 2, 4))


def mix(c: str, alvo: str, t: float) -> str:
    a, b = _hex(c), _hex(alvo)
    r = [round(a[i] + (b[i] - a[i]) * t) for i in range(3)]
    return "#%02X%02X%02X" % tuple(r)


def lighten(c, t=0.18):
    return mix(c, "#FFFFFF", t)


def darken(c, t=0.22):
    return mix(c, "#000000", t)


def esc(s: str) -> str:
    return (str(s).replace("&", "&amp;").replace("<", "&lt;").replace(">", "&gt;").replace('"', "&quot;"))


@dataclass
class Scene:
    w: int = 1600
    h: int = 900
    ox: float = 800
    oy: float = 420
    u: float = 60
    defs: list = field(default_factory=list)
    bg: list = field(default_factory=list)      # desenhado primeiro
    items: list = field(default_factory=list)   # (chave, svg) ordenado por profundidade
    fg: list = field(default_factory=list)      # desenhado após a cena (HUD)
    _grad_ids: set = field(default_factory=set)

    # ---------- projeção ----------
    def P(self, x, y, z=0.0):
        return (self.ox + (x - y) * self.u * 0.8660254,
                self.oy + (x + y) * self.u * 0.5 - z * self.u)

    def pts(self, lista):
        return " ".join("%.1f,%.1f" % self.P(*p) for p in lista)

    # ---------- primitivas ----------
    def add(self, svg: str, key: float):
        self.items.append((key, svg))

    def tile(self, x, y, w, d, color, z=0.0, key=None, stroke=None, sw=1.0, extra=""):
        pol = self.pts([(x, y, z), (x + w, y, z), (x + w, y + d, z), (x, y + d, z)])
        st = f' stroke="{stroke}" stroke-width="{sw}" stroke-linejoin="round"' if stroke else ""
        svg = f'<polygon points="{pol}" fill="{color}"{st}{extra}/>'
        k = key if key is not None else (x + w / 2 + y + d / 2 + z * 0.01)
        self.add(svg, k)
        return svg

    def box(self, x, y, z, w, d, h, color, key=None, top=None, left=None, right=None,
            stroke=None, sw=1.0, opacity=None):
        top = top or lighten(color, 0.2)
        left = left or color
        right = right or darken(color, 0.25)
        st = f' stroke="{stroke}" stroke-width="{sw}" stroke-linejoin="round"' if stroke else ""
        op = f' opacity="{opacity}"' if opacity is not None else ""
        t = self.pts([(x, y, z + h), (x + w, y, z + h), (x + w, y + d, z + h), (x, y + d, z + h)])
        l = self.pts([(x, y + d, z), (x + w, y + d, z), (x + w, y + d, z + h), (x, y + d, z + h)])
        r = self.pts([(x + w, y, z), (x + w, y + d, z), (x + w, y + d, z + h), (x + w, y, z + h)])
        svg = (f'<g{op}><polygon points="{l}" fill="{left}"{st}/>'
               f'<polygon points="{r}" fill="{right}"{st}/>'
               f'<polygon points="{t}" fill="{top}"{st}/></g>')
        k = key if key is not None else (x + w / 2 + y + d / 2 + z * 0.01 + h * 0.001)
        self.add(svg, k)
        return svg

    def cylinder(self, x, y, z, r, h, color, key=None, segs=0, top=None):
        """Cilindro vertical centrado em (x, y)."""
        cx, cy = self.P(x, y, z)
        rx = r * self.u * 1.2247
        ry = r * self.u * 0.7071
        hp = h * self.u
        gid = self._grad(f"cyl_{color.lstrip('#')}", lighten(color, .15), darken(color, .35), horiz=True)
        top = top or lighten(color, 0.25)
        svg = (f'<g><path d="M{cx - rx:.1f},{cy:.1f} a{rx:.1f},{ry:.1f} 0 0 0 {2 * rx:.1f},0 '
               f'v{-hp:.1f} a{rx:.1f},{ry:.1f} 0 0 1 {-2 * rx:.1f},0 z" fill="url(#{gid})"/>'
               f'<ellipse cx="{cx:.1f}" cy="{cy - hp:.1f}" rx="{rx:.1f}" ry="{ry:.1f}" fill="{top}"/></g>')
        k = key if key is not None else (x + y + z * 0.01)
        self.add(svg, k)

    def disc(self, x, y, z, r, color, key=None, stroke=None, sw=1, opacity=None):
        cx, cy = self.P(x, y, z)
        rx = r * self.u * 1.2247
        ry = r * self.u * 0.7071
        st = f' stroke="{stroke}" stroke-width="{sw}"' if stroke else ""
        op = f' opacity="{opacity}"' if opacity is not None else ""
        svg = f'<ellipse cx="{cx:.1f}" cy="{cy:.1f}" rx="{rx:.1f}" ry="{ry:.1f}" fill="{color}"{st}{op}/>'
        self.add(svg, key if key is not None else (x + y + z * 0.01 - 0.5))

    def sphere(self, x, y, z, r, color, key=None, label=None, fs=None, lcolor=BRANCO):
        cx, cy = self.P(x, y, z)
        rp = r * self.u
        gid = self._grad(f"sph_{color.lstrip('#')}", lighten(color, .45), darken(color, .3), radial=True)
        svg = f'<circle cx="{cx:.1f}" cy="{cy:.1f}" r="{rp:.1f}" fill="url(#{gid})"/>'
        if label:
            fs = fs or rp * 0.55
            svg += (f'<text x="{cx:.1f}" y="{cy + fs * 0.36:.1f}" text-anchor="middle" font-family="{FONT}" '
                    f'font-weight="700" font-size="{fs:.0f}" fill="{lcolor}">{esc(label)}</text>')
        self.add(f"<g>{svg}</g>", key if key is not None else (x + y + z * 0.01))

    def shadow(self, x, y, r, key=None, opacity=0.18):
        self.disc(x, y, 0.01, r, NAVY, key=key if key is not None else (x + y - 0.3), opacity=opacity)

    def text_at(self, x, y, z, txt, size=20, color=NAVY, weight=600, anchor="middle", key=None, font=FONT, dy=0, extra=""):
        sx, sy = self.P(x, y, z)
        svg = (f'<text x="{sx:.1f}" y="{sy + dy + size * 0.36:.1f}" text-anchor="{anchor}" font-family="{font}" '
               f'font-weight="{weight}" font-size="{size}" fill="{color}"{extra}>{esc(txt)}</text>')
        self.add(svg, key if key is not None else (x + y + z * 0.01 + 0.05))

    def tree(self, x, y, size=1.0, color=VERDE, key=None, trunk=MADEIRA_ESC):
        """Árvore low-poly: tronco + copa em cone (duas faces)."""
        bx, by = self.P(x, y, 0)
        s = size * self.u
        tw = 0.12 * s
        svg = f'<rect x="{bx - tw:.1f}" y="{by - 0.5 * s:.1f}" width="{2 * tw:.1f}" height="{0.5 * s:.1f}" fill="{trunk}"/>'
        apex = (bx, by - 2.2 * s)
        base_l = (bx - 0.7 * s, by - 0.45 * s)
        base_r = (bx + 0.7 * s, by - 0.45 * s)
        base_m = (bx, by - 0.25 * s)
        svg += f'<polygon points="{apex[0]:.1f},{apex[1]:.1f} {base_l[0]:.1f},{base_l[1]:.1f} {base_m[0]:.1f},{base_m[1]:.1f}" fill="{lighten(color, .12)}"/>'
        svg += f'<polygon points="{apex[0]:.1f},{apex[1]:.1f} {base_m[0]:.1f},{base_m[1]:.1f} {base_r[0]:.1f},{base_r[1]:.1f}" fill="{darken(color, .2)}"/>'
        # segunda camada menor
        apex2 = (bx, by - 2.6 * s)
        svg += f'<polygon points="{apex2[0]:.1f},{apex2[1]:.1f} {bx - 0.5 * s:.1f},{by - 1.3 * s:.1f} {bx:.1f},{by - 1.15 * s:.1f}" fill="{lighten(color, .22)}"/>'
        svg += f'<polygon points="{apex2[0]:.1f},{apex2[1]:.1f} {bx:.1f},{by - 1.15 * s:.1f} {bx + 0.5 * s:.1f},{by - 1.3 * s:.1f}" fill="{darken(color, .08)}"/>'
        self.shadow(x, y, 0.5 * size, key=(x + y - 0.4))
        self.add(f"<g>{svg}</g>", key if key is not None else (x + y))

    def bush(self, x, y, size=0.6, color=GRAMA2, key=None):
        bx, by = self.P(x, y, 0)
        s = size * self.u
        svg = (f'<ellipse cx="{bx:.1f}" cy="{by - 0.25 * s:.1f}" rx="{0.6 * s:.1f}" ry="{0.4 * s:.1f}" fill="{color}"/>'
               f'<ellipse cx="{bx - 0.25 * s:.1f}" cy="{by - 0.4 * s:.1f}" rx="{0.35 * s:.1f}" ry="{0.3 * s:.1f}" fill="{lighten(color, .15)}"/>')
        self.add(svg, key if key is not None else (x + y))

    def rock(self, x, y, size=0.5, color=CINZA, key=None):
        self.box(x, y, 0, size, size * 0.8, size * 0.5, color, key=key)

    def pillar_light(self, x, y, color=OURO, h=3.2, r=0.28, key=None, label=None):
        """Pilar de luz: base + feixe com gradiente que some para cima."""
        cx, cy = self.P(x, y, 0)
        rx = r * self.u * 1.2247
        ry = r * self.u * 0.7071
        hp = h * self.u
        gid = self._grad(f"beam_{color.lstrip('#')}", color, color, vert_fade=True)
        svg = (f'<ellipse cx="{cx:.1f}" cy="{cy:.1f}" rx="{rx * 2.2:.1f}" ry="{ry * 2.2:.1f}" fill="{color}" opacity=".18"/>'
               f'<ellipse cx="{cx:.1f}" cy="{cy:.1f}" rx="{rx:.1f}" ry="{ry:.1f}" fill="{lighten(color, .5)}"/>'
               f'<rect x="{cx - rx:.1f}" y="{cy - hp:.1f}" width="{2 * rx:.1f}" height="{hp:.1f}" fill="url(#{gid})" opacity=".85"/>')
        if label is not None:
            svg += (f'<circle cx="{cx:.1f}" cy="{cy - hp * 0.62:.1f}" r="{rx * 0.95:.1f}" fill="{NAVY}" stroke="{lighten(color, .4)}" stroke-width="3"/>'
                    f'<text x="{cx:.1f}" y="{cy - hp * 0.62 + rx * 0.38:.1f}" text-anchor="middle" font-family="{FONT}" font-weight="800" font-size="{rx * 1.05:.0f}" fill="{PAPEL}">{esc(label)}</text>')
        self.add(f"<g>{svg}</g>", key if key is not None else (x + y))

    # ---------- personagem (o mesmo em todas as imagens) ----------
    def character(self, x, y, z=0.0, key=None, scale=1.0, carrying=None, run=False):
        """Explorador da linha Gamifica 3D: corpo navy, rosto papel, boné dourado, mochila verde."""
        s = scale
        parts = []
        k = key if key is not None else (x + y + 0.6 + z * 0.01)
        # sombra
        parts.append(self._disc_svg(x + 0.35 * s, y + 0.35 * s, 0.01, 0.42 * s, NAVY, opacity=0.2))
        # pernas
        lh = 0.32 * s
        if run:
            parts.append(self._box_svg(x + 0.08 * s, y + 0.1 * s, z, 0.22 * s, 0.22 * s, lh, NAVY2))
            parts.append(self._box_svg(x + 0.42 * s, y + 0.38 * s, z + 0.12 * s, 0.22 * s, 0.22 * s, lh * .8, NAVY2))
        else:
            parts.append(self._box_svg(x + 0.1 * s, y + 0.28 * s, z, 0.22 * s, 0.22 * s, lh, NAVY2))
            parts.append(self._box_svg(x + 0.4 * s, y + 0.28 * s, z, 0.22 * s, 0.22 * s, lh, NAVY2))
        # mochila (atrás = menor x, menor y)
        parts.append(self._box_svg(x - 0.12 * s, y + 0.1 * s, z + lh + 0.08 * s, 0.22 * s, 0.5 * s, 0.5 * s, VERDE))
        # corpo
        parts.append(self._box_svg(x, y, z + lh, 0.7 * s, 0.7 * s, 0.7 * s, NAVY))
        # peito (detalhe na face direita x=max)
        px, py = self.P(x + 0.7 * s, y + 0.35 * s, z + lh + 0.35 * s)
        parts.append(f'<ellipse cx="{px:.1f}" cy="{py:.1f}" rx="{0.09 * s * self.u:.1f}" ry="{0.11 * s * self.u:.1f}" fill="{OURO}"/>')
        # braços
        parts.append(self._box_svg(x + 0.72 * s, y + 0.2 * s, z + lh + 0.15 * s, 0.18 * s, 0.3 * s, 0.5 * s, NAVY2))
        parts.append(self._box_svg(x + 0.2 * s, y + 0.72 * s, z + lh + 0.15 * s, 0.3 * s, 0.18 * s, 0.5 * s, NAVY2))
        # cabeça
        hz = z + lh + 0.7 * s
        parts.append(self._box_svg(x - 0.05 * s, y - 0.05 * s, hz, 0.8 * s, 0.8 * s, 0.7 * s, PAPEL,
                                   top=lighten(PAPEL, .3), left=PAPEL2, right=mix(PAPEL, "#C9BFA6", .35)))
        # olhos (na face direita, x = máx) e sorriso
        for yy in (0.2, 0.5):
            ex, ey = self.P(x + 0.75 * s, y + yy * s, hz + 0.42 * s)
            parts.append(f'<ellipse cx="{ex:.1f}" cy="{ey:.1f}" rx="{0.05 * s * self.u:.1f}" ry="{0.07 * s * self.u:.1f}" fill="{NAVY}"/>')
        mx1, my1 = self.P(x + 0.75 * s, y + 0.22 * s, hz + 0.22 * s)
        mx2, my2 = self.P(x + 0.75 * s, y + 0.5 * s, hz + 0.22 * s)
        parts.append(f'<path d="M{mx1:.1f},{my1:.1f} Q{(mx1 + mx2) / 2:.1f},{(my1 + my2) / 2 + 0.07 * s * self.u:.1f} {mx2:.1f},{my2:.1f}" stroke="{NAVY}" stroke-width="{max(2, 2.5 * s):.1f}" fill="none" stroke-linecap="round"/>')
        # boné dourado com aba
        parts.append(self._box_svg(x - 0.1 * s, y - 0.1 * s, hz + 0.68 * s, 0.9 * s, 0.9 * s, 0.26 * s, OURO))
        parts.append(self._box_svg(x + 0.55 * s, y - 0.1 * s, hz + 0.68 * s, 0.55 * s, 0.9 * s, 0.07 * s, OURO_ESC, top=OURO))
        # objeto carregado (caixa à frente)
        if carrying:
            txt, col = carrying
            parts.append(self._box_svg(x + 0.9 * s, y + 0.25 * s, z + lh + 0.1 * s, 0.6 * s, 0.6 * s, 0.6 * s, col))
            tx, ty = self.P(x + 1.5 * s, y + 0.55 * s, z + lh + 0.4 * s)
            parts.append(f'<text x="{tx:.1f}" y="{ty:.1f}" text-anchor="middle" font-family="{FONT}" font-weight="700" font-size="{0.22 * s * self.u:.0f}" fill="{PAPEL}" transform="rotate(30 {tx:.1f} {ty:.1f})">{esc(txt)}</text>')
        self.add("<g>" + "".join(parts) + "</g>", k)

    # svg-only helpers (sem adicionar à lista)
    def _box_svg(self, x, y, z, w, d, h, color, top=None, left=None, right=None):
        top = top or lighten(color, 0.2)
        left = left or color
        right = right or darken(color, 0.25)
        t = self.pts([(x, y, z + h), (x + w, y, z + h), (x + w, y + d, z + h), (x, y + d, z + h)])
        l = self.pts([(x, y + d, z), (x + w, y + d, z), (x + w, y + d, z + h), (x, y + d, z + h)])
        r = self.pts([(x + w, y, z), (x + w, y + d, z), (x + w, y + d, z + h), (x + w, y, z + h)])
        return (f'<polygon points="{l}" fill="{left}"/><polygon points="{r}" fill="{right}"/>'
                f'<polygon points="{t}" fill="{top}"/>')

    def _disc_svg(self, x, y, z, r, color, opacity=None):
        cx, cy = self.P(x, y, z)
        op = f' opacity="{opacity}"' if opacity is not None else ""
        return f'<ellipse cx="{cx:.1f}" cy="{cy:.1f}" rx="{r * self.u * 1.2247:.1f}" ry="{r * self.u * 0.7071:.1f}" fill="{color}"{op}/>'

    # ---------- gradientes ----------
    def _grad(self, gid, c1, c2, horiz=False, radial=False, vert_fade=False):
        if gid in self._grad_ids:
            return gid
        self._grad_ids.add(gid)
        if radial:
            self.defs.append(f'<radialGradient id="{gid}" cx="35%" cy="30%" r="75%"><stop offset="0" stop-color="{c1}"/><stop offset="1" stop-color="{c2}"/></radialGradient>')
        elif vert_fade:
            self.defs.append(f'<linearGradient id="{gid}" x1="0" y1="1" x2="0" y2="0"><stop offset="0" stop-color="{c1}" stop-opacity=".95"/><stop offset="1" stop-color="{c2}" stop-opacity="0"/></linearGradient>')
        elif horiz:
            self.defs.append(f'<linearGradient id="{gid}" x1="0" y1="0" x2="1" y2="0"><stop offset="0" stop-color="{c1}"/><stop offset="1" stop-color="{c2}"/></linearGradient>')
        else:
            self.defs.append(f'<linearGradient id="{gid}" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="{c1}"/><stop offset="1" stop-color="{c2}"/></linearGradient>')
        return gid

    def sky(self, c1=CEU, c2="#EAF3F7", extra_defs=""):
        gid = self._grad("sky", c1, c2)
        self.bg.append(f'<rect width="{self.w}" height="{self.h}" fill="url(#{gid})"/>')

    def clouds(self, lista):
        for (cx, cy, s) in lista:
            self.bg.append(
                f'<g opacity=".9" fill="{BRANCO}"><ellipse cx="{cx}" cy="{cy}" rx="{70 * s:.0f}" ry="{26 * s:.0f}"/>'
                f'<ellipse cx="{cx - 35 * s:.0f}" cy="{cy - 10 * s:.0f}" rx="{40 * s:.0f}" ry="{26 * s:.0f}"/>'
                f'<ellipse cx="{cx + 30 * s:.0f}" cy="{cy - 14 * s:.0f}" rx="{48 * s:.0f}" ry="{30 * s:.0f}"/></g>')

    def stars(self, n=90, seed=7):
        import random
        rnd = random.Random(seed)
        out = []
        for _ in range(n):
            x = rnd.uniform(0, self.w)
            y = rnd.uniform(0, self.h * 0.6)
            r = rnd.choice([1, 1, 1.5, 2, 2.5])
            out.append(f'<circle cx="{x:.0f}" cy="{y:.0f}" r="{r}" fill="{PAPEL}" opacity="{rnd.uniform(.4, .95):.2f}"/>')
        self.bg.append("<g>" + "".join(out) + "</g>")

    # ---------- HUD (espaço de tela) ----------
    def hud_pill(self, x, y, txt, w=None, h=56, fill=NAVY, color=PAPEL, size=24, icon=None, opacity=.92, bold=700, align="left"):
        if w is None:
            w = 36 + len(txt) * size * 0.58 + (h if icon else 0)
        tx = x + (h if icon else 22)
        svg = f'<g opacity="{opacity}"><rect x="{x}" y="{y}" width="{w:.0f}" height="{h}" rx="{h / 2}" fill="{fill}"/>'
        if icon:
            svg += f'<circle cx="{x + h / 2}" cy="{y + h / 2}" r="{h * .3:.0f}" fill="{OURO}"/><text x="{x + h / 2}" y="{y + h / 2 + size * .3:.0f}" text-anchor="middle" font-family="{FONT}" font-weight="800" font-size="{size * .8:.0f}" fill="{NAVY}">{esc(icon)}</text>'
        svg += f'<text x="{tx:.0f}" y="{y + h / 2 + size * .36:.0f}" font-family="{FONT}" font-weight="{bold}" font-size="{size}" fill="{color}">{esc(txt)}</text></g>'
        self.fg.append(svg)
        return w

    def hud_hint(self, x, y, r=36, label_color=NAVY):
        self.fg.append(f'<g><circle cx="{x}" cy="{y}" r="{r}" fill="{OURO}" stroke="{NAVY}" stroke-width="4"/>'
                       f'<text x="{x}" y="{y + r * .38:.0f}" text-anchor="middle" font-family="{FONT_T}" font-weight="700" font-size="{r * 1.1:.0f}" fill="{NAVY}">?</text>'
                       f'<text x="{x}" y="{y + r + 26}" text-anchor="middle" font-family="{FONT}" font-weight="600" font-size="18" fill="{label_color}">Dica</text></g>')

    def hud_joystick(self, x, y, r=80):
        self.fg.append(f'<g><circle cx="{x}" cy="{y}" r="{r}" fill="{NAVY}" opacity=".25"/>'
                       f'<circle cx="{x}" cy="{y}" r="{r}" fill="none" stroke="{PAPEL}" stroke-width="3" opacity=".8"/>'
                       f'<circle cx="{x + r * .3:.0f}" cy="{y - r * .2:.0f}" r="{r * .42:.0f}" fill="{PAPEL}" opacity=".9"/></g>')

    def hud_banner(self, x, y, w, h, title, sub=None, fill=NAVY, color=PAPEL, size=30, subsize=16, subcolor=OURO):
        svg = f'<g><rect x="{x}" y="{y}" width="{w}" height="{h}" rx="16" fill="{fill}" opacity=".94"/>'
        if sub:
            svg += f'<text x="{x + w / 2}" y="{y + 26}" text-anchor="middle" font-family="{FONT}" font-weight="700" font-size="{subsize}" fill="{subcolor}" letter-spacing="2">{esc(sub)}</text>'
            svg += f'<text x="{x + w / 2}" y="{y + h - 18}" text-anchor="middle" font-family="{FONT}" font-weight="600" font-size="{size}" fill="{color}">{esc(title)}</text>'
        else:
            svg += f'<text x="{x + w / 2}" y="{y + h / 2 + size * .36:.0f}" text-anchor="middle" font-family="{FONT}" font-weight="600" font-size="{size}" fill="{color}">{esc(title)}</text>'
        self.fg.append(svg + "</g>")

    def hud_progress(self, x, y, w, frac, label=None, h=14):
        svg = (f'<g><rect x="{x}" y="{y}" width="{w}" height="{h}" rx="{h / 2}" fill="{NAVY}" opacity=".25"/>'
               f'<rect x="{x}" y="{y}" width="{w * frac:.0f}" height="{h}" rx="{h / 2}" fill="{OURO}"/>')
        if label:
            svg += f'<text x="{x}" y="{y - 8}" font-family="{FONT}" font-weight="600" font-size="18" fill="{NAVY}">{esc(label)}</text>'
        self.fg.append(svg + "</g>")

    # ---------- saída ----------
    def render(self, title: str) -> str:
        self.items.sort(key=lambda t: t[0])
        body = "".join(s for _, s in self.items)
        defs = "".join(self.defs)
        return (f'<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 {self.w} {self.h}" width="{self.w}" height="{self.h}" role="img" aria-label="{esc(title)}">\n'
                f'<title>{esc(title)}</title>\n<defs>{defs}</defs>\n'
                f'<g id="fundo">{"".join(self.bg)}</g>\n<g id="cena">{body}</g>\n<g id="hud">{"".join(self.fg)}</g>\n</svg>\n')
