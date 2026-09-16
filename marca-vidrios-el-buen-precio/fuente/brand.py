# -*- coding: utf-8 -*-
"""Constructor del sistema de marca 'Vidrios el buen precio'."""
import os
from fontTools.ttLib import TTFont
from fontTools.pens.svgPathPen import SVGPathPen
from fontTools.pens.transformPen import TransformPen
from fontTools.misc.transform import Transform

HERE = os.path.dirname(os.path.abspath(__file__))
FONTS = os.path.join(os.path.dirname(HERE), "fonts")
_cache = {}

def _font(name):
    if name not in _cache:
        f = TTFont(os.path.join(FONTS, name + ".ttf"))
        _cache[name] = (f, f.getGlyphSet(), f.getBestCmap(), f["head"].unitsPerEm)
    return _cache[name]

def text_path(name, text, size, tracking=0.0, x=0.0, y=0.0):
    """Devuelve (path_d, ancho). tracking en em."""
    font, gs, cmap, upem = _font(name)
    scale = size / upem
    tr = tracking * size
    cur = x
    parts = []
    for ch in text:
        gid = cmap.get(ord(ch))
        if gid is None:
            cur += size * 0.3 + tr
            continue
        pen = SVGPathPen(gs)
        gs[gid].draw(TransformPen(pen, Transform(scale, 0, 0, -scale, cur, y)))
        d = pen.getCommands()
        if d:
            parts.append(d)
        cur += gs[gid].width * scale + tr
    return " ".join(parts), (cur - tr - x)

def text_width(name, text, size, tracking=0.0):
    return text_path(name, text, size, tracking)[1]

# ---------------------------------------------------------------- paleta
NAVY      = "#8E1015"   # tinta / contorno (rojo profundo)
NAVY_DEEP = "#6B0A0E"   # fondo oscuro
GLASS_A1  = "#FFE98A"   # cara iluminada
GLASS_A2  = "#FFC61A"
GLASS_B1  = "#FFB300"   # cara en sombra
GLASS_B2  = "#E08A00"
RED       = "#D7262C"   # acento (tiradores, linea)
YELLOW    = "#FFC61A"
WHITE     = "#FFFFFF"
CREAM     = "#FFF7E8"

# --------------------------------------------------- geometria del icono
# vertice central (cercano al observador) y aristas exteriores
CX, TOP_C, BOT_C = 500.0, 248.0, 762.0
LX, RX           = 168.0, 832.0
TOP_O, BOT_O     = 332.0, 688.0

LEFT  = [(CX, TOP_C), (LX, TOP_O), (LX, BOT_O), (CX, BOT_C)]   # interior->exterior
RIGHT = [(CX, TOP_C), (RX, TOP_O), (RX, BOT_O), (CX, BOT_C)]

def bilinear(quad, u, v):
    """quad = [sup-interior, sup-exterior, inf-exterior, inf-interior]"""
    (x0, y0), (x1, y1), (x2, y2), (x3, y3) = quad
    tx = x0 + (x1 - x0) * u
    ty = y0 + (y1 - y0) * u
    bx = x3 + (x2 - x3) * u
    by = y3 + (y2 - y3) * u
    return (tx + (bx - tx) * v, ty + (by - ty) * v)

def poly(pts, **attrs):
    d = " ".join("%.2f,%.2f" % p for p in pts)
    a = " ".join('%s="%s"' % (k.replace("_", "-"), v) for k, v in attrs.items())
    return '<polygon points="%s" %s/>' % (d, a)

def quad_poly(quad, u0, u1, v0, v1, **attrs):
    pts = [bilinear(quad, u0, v0), bilinear(quad, u1, v0),
           bilinear(quad, u1, v1), bilinear(quad, u0, v1)]
    return poly(pts, **attrs)

def panes(quad, fill, cols=2, rows=2, margin=0.085, gap=0.038):
    """Rejilla de vidrios dentro de una cara."""
    out = []
    span_u = 1 - 2 * margin
    span_v = 1 - 2 * margin
    wu = (span_u - gap * (cols - 1)) / cols
    hv = (span_v - gap * (rows - 1)) / rows
    for c in range(cols):
        for r in range(rows):
            u0 = margin + c * (wu + gap)
            v0 = margin + r * (hv + gap)
            out.append(quad_poly(quad, u0, u0 + wu, v0, v0 + hv, fill=fill))
    return "".join(out)

def icon(dark_bg=True, mono=None, outline=None):
    """Marca grafica sobre viewBox 0 0 1000 1000."""
    ink = NAVY if mono is None else mono
    edge = outline or ink   # contorno exterior (blanco sobre fondos rojos)
    frame = WHITE if mono is None else "none"
    gl_a = "url(#glassA)" if mono is None else mono
    gl_b = "url(#glassB)" if mono is None else mono
    op_a = "" if mono is None else ' opacity="0.55"'
    op_b = "" if mono is None else ' opacity="0.3"'
    sw = 26

    sil = [(CX, TOP_C), (RX, TOP_O), (RX, BOT_O), (CX, BOT_C), (LX, BOT_O), (LX, TOP_O)]
    g = []
    # cuerpo blanco + contorno
    g.append(poly(sil, fill=frame if frame != "none" else "none", stroke=edge,
                  stroke_width=sw, stroke_linejoin="round"))
    # vidrios
    g.append('<g%s>%s</g>' % (op_a, panes(LEFT, gl_a)))
    g.append('<g%s>%s</g>' % (op_b, panes(RIGHT, gl_b)))
    # brillo diagonal sobre la cara iluminada
    if mono is None:
        g.append('<g clip-path="url(#clipGlass)">'
                 '<polygon points="200,780 330,230 415,230 285,780" fill="#FFFFFF" opacity="0.30"/>'
                 '<polygon points="352,780 400,230 432,230 384,780" fill="#FFFFFF" opacity="0.20"/>'
                 '</g>')
    # arista central
    g.append('<line x1="%.1f" y1="%.1f" x2="%.1f" y2="%.1f" stroke="%s" stroke-width="%d" '
             'stroke-linecap="round"/>' % (CX, TOP_C, CX, BOT_C, ink, sw))
    # tiradores (acento rojo)
    hcol = RED if mono is None else ink
    for quad, u in ((LEFT, 0.155), (RIGHT, 0.155)):
        p0 = bilinear(quad, u, 0.44)
        p1 = bilinear(quad, u, 0.66)
        g.append('<line x1="%.1f" y1="%.1f" x2="%.1f" y2="%.1f" stroke="%s" stroke-width="20" '
                 'stroke-linecap="round"/>' % (p0[0], p0[1], p1[0], p1[1], hcol))
    return "".join(g)

def clip_panes():
    """Recorte = union de los vidrios de la cara iluminada (el marco queda limpio)."""
    return '<clipPath id="clipGlass">%s</clipPath>' % panes(LEFT, "#000")

def defs(dark_bg=True):
    return '''<defs>
<linearGradient id="glassA" x1="0" y1="0" x2="0.4" y2="1">
  <stop offset="0" stop-color="%s"/><stop offset="1" stop-color="%s"/></linearGradient>
<linearGradient id="glassB" x1="1" y1="0" x2="0.4" y2="1">
  <stop offset="0" stop-color="%s"/><stop offset="1" stop-color="%s"/></linearGradient>
<linearGradient id="bgDark" x1="0" y1="0" x2="1" y2="1">
  <stop offset="0" stop-color="#C4171F"/><stop offset="1" stop-color="%s"/></linearGradient>
<radialGradient id="bgGlow" cx="0.5" cy="0.42" r="0.62">
  <stop offset="0" stop-color="#FF7A2F" stop-opacity="0.45"/>
  <stop offset="1" stop-color="#FF7A2F" stop-opacity="0"/></radialGradient>
%s
</defs>''' % (GLASS_A1, GLASS_A2, GLASS_B1, GLASS_B2, NAVY_DEEP,
              clip_panes())


# caja util del dibujo, incluyendo el trazo exterior
BBOX = (LX - 14, TOP_C - 14, RX + 14, BOT_C + 14)
BBOX_W = BBOX[2] - BBOX[0]
BBOX_H = BBOX[3] - BBOX[1]

def icon_fit(x, y, height=None, width=None, mono=None, outline=None):
    """Coloca el icono con su altura (o ancho) visual real, origen arriba-izq."""
    if height is not None:
        s = height / BBOX_H
    else:
        s = width / BBOX_W
    return '<g transform="translate(%.3f,%.3f) scale(%.5f) translate(%.2f,%.2f)">%s</g>' % (
        x, y, s, -BBOX[0], -BBOX[1], icon(mono=mono, outline=outline))
