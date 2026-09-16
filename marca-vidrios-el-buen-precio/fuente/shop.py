# -*- coding: utf-8 -*-
"""Dibujo fiel del ventanal original (4 paños, remate rojo, chapa y brillo)."""
from brand import bilinear, poly

# ------------------------------------------------------------------ paletas
PALETAS = {
    "rojo-amarillo": dict(
        ink="#16161A",
        frame="#FFFFFF",
        glass_front=("#FFE889", "#FFC21A"),
        glass_side=("#FFD04A", "#EDA200"),
        shine="#FFFCEC", shine_op=(0.92, 0.6),
        trim="#E01B24", tab="#E01B24", handle="#16161A",
        bg_dark=("#C4171F", "#6B0A0E"), bg_light="#FFF7E8",
        title_dark="#FFFFFF", tag_dark="#FFD23F",
        title_light="#16161A", tag_light="#D7262C",
    ),
    "celeste": dict(
        ink="#16161A",
        frame="#FFFFFF",
        glass_front=("#A8ECF5", "#4FC3DF"),
        glass_side=("#7BD8EA", "#2FA3C4"),
        shine="#FFE535", shine_op=(0.95, 0.7),
        trim="#E01B24", tab="#E01B24", handle="#16161A",
        bg_dark=("#123B4E", "#0A1F2B"), bg_light="#F1FAFC",
        title_dark="#FFFFFF", tag_dark="#FFE84D",
        title_light="#16161A", tag_light="#D7262C",
    ),
}

# --------------------------------------------------------------- geometria
# cada paño: [sup-interior, sup-exterior, inf-exterior, inf-interior]
CX = 500.0
PUERTA_IZQ  = [(497, 168), (263, 214), (263, 812), (497, 860)]
VENTANA_IZQ = [(263, 208), (108, 264), (108, 740), (263, 806)]
def espejo(q):
    return [(2 * CX - x, y) for (x, y) in q]
PUERTA_DER  = espejo(PUERTA_IZQ)
VENTANA_DER = espejo(VENTANA_IZQ)

SW = 19          # grosor de linea
BBOX = (108 - 40, 168 - 46, 892 + 40, 860 + 14)
BBOX_W = BBOX[2] - BBOX[0]
BBOX_H = BBOX[3] - BBOX[1]


def rejilla(quad, cols, rows, fill, m_top=0.052, m_bot=0.072, m_side=0.062, gap=0.026):
    """Vidrios dentro de un paño."""
    out = []
    span_u = 1 - 2 * m_side
    span_v = 1 - m_top - m_bot
    wu = (span_u - gap * (cols - 1)) / cols
    hv = (span_v - gap * (rows - 1)) / rows
    for c in range(cols):
        for r in range(rows):
            u0 = m_side + c * (wu + gap)
            v0 = m_top + r * (hv + gap)
            pts = [bilinear(quad, u0, v0), bilinear(quad, u0 + wu, v0),
                   bilinear(quad, u0 + wu, v0 + hv), bilinear(quad, u0, v0 + hv)]
            out.append(poly(pts, fill=fill))
    return "".join(out)


def pano(quad, cols, rows, fill, ink, frame, mono=None):
    g = poly(quad, fill=frame if mono is None else "none", stroke=ink,
             stroke_width=SW, stroke_linejoin="round")
    g += rejilla(quad, cols, rows, fill)
    return g


def brillo(quad, color, ops):
    """Franjas diagonales de reflejo sobre el vidrio de la puerta izquierda."""
    tiras = []
    for (a, b), op in zip(((0.12, 0.40), (0.50, 0.63)), ops):
        p = [bilinear(quad, 0.02 + a, 0.02), bilinear(quad, 0.02 + b, 0.02),
             bilinear(quad, b - 0.18, 0.98), bilinear(quad, a - 0.18, 0.98)]
        tiras.append(poly(p, fill=color, opacity="%.2f" % op))
    return '<g clip-path="url(#clipPuerta)">%s</g>' % "".join(tiras)


def clip_puerta():
    """El reflejo solo vive dentro de los vidrios de la puerta izquierda."""
    return '<clipPath id="clipPuerta">%s</clipPath>' % rejilla(PUERTA_IZQ, 2, 4, "#000")


def gradientes(pal):
    (f1, f2), (s1, s2) = pal["glass_front"], pal["glass_side"]
    (b1, b2) = pal["bg_dark"]
    return ('<defs>'
            '<linearGradient id="gFront" x1="0" y1="0" x2="0.35" y2="1">'
            '<stop offset="0" stop-color="%s"/><stop offset="1" stop-color="%s"/></linearGradient>'
            '<linearGradient id="gSide" x1="0" y1="0" x2="0.35" y2="1">'
            '<stop offset="0" stop-color="%s"/><stop offset="1" stop-color="%s"/></linearGradient>'
            '<linearGradient id="bgDark" x1="0" y1="0" x2="1" y2="1">'
            '<stop offset="0" stop-color="%s"/><stop offset="1" stop-color="%s"/></linearGradient>'
            '%s</defs>') % (f1, f2, s1, s2, b1, b2, clip_puerta())


def remate(pal, ink):
    """Linea roja que bordea el techo y baja por el costado derecho."""
    top = [(108, 264), (263, 208), (497, 168), (503, 168), (737, 208), (892, 264)]
    off = [(x, y - 24) for (x, y) in top]
    d = "M %.1f,%.1f " % off[0] + " ".join("L %.1f,%.1f" % p for p in off[1:])
    d += " L %.1f,%.1f" % (892 + 24, 264 - 24 + 24)
    d += " L %.1f,%.1f" % (892 + 24, 706)
    g = '<path d="%s" fill="none" stroke="%s" stroke-width="%d" stroke-linejoin="round" ' \
        'stroke-linecap="round"/>' % (d, ink, SW + 14)
    g += '<path d="%s" fill="none" stroke="%s" stroke-width="%d" stroke-linejoin="round" ' \
         'stroke-linecap="round"/>' % (d, pal["trim"], SW)
    return g


def chapa(pal, ink):
    """Chapa roja del angulo superior izquierdo."""
    p = [(168, 132), (215, 115), (215, 196), (168, 216)]
    g = poly(p, fill=pal["tab"], stroke=ink, stroke_width=14, stroke_linejoin="round")
    inner = [(181, 147), (203, 139), (203, 182), (181, 191)]
    g += poly(inner, fill="none", stroke=ink, stroke_width=7, stroke_linejoin="round")
    return g


def ventanal(pal, mono=None, ink_override=None, trim_override=None):
    """Marca grafica completa, sobre viewBox 0 0 1000 1000."""
    ink = mono or pal["ink"]
    edge = ink_override or ink
    frame = pal["frame"] if mono is None else "none"
    gf = "url(#gFront)" if mono is None else mono
    gs = "url(#gSide)" if mono is None else mono
    op_f = "" if mono is None else ' opacity="0.42"'
    op_s = "" if mono is None else ' opacity="0.26"'

    if trim_override:
        pal = dict(pal, trim=trim_override, tab=trim_override)
    g = [remate(pal, edge) if mono is None else "", chapa(pal, edge) if mono is None else ""]
    for q in (VENTANA_IZQ, VENTANA_DER):
        g.append(poly(q, fill=frame if mono is None else "none", stroke=edge,
                      stroke_width=SW, stroke_linejoin="round"))
        g.append('<g%s>%s</g>' % (op_s, rejilla(q, 2, 5, gs)))
    for q in (PUERTA_IZQ, PUERTA_DER):
        g.append(poly(q, fill=frame if mono is None else "none", stroke=edge,
                      stroke_width=SW, stroke_linejoin="round"))
        g.append('<g%s>%s</g>' % (op_f, rejilla(q, 2, 4, gf)))
    if mono is None:
        g.append(brillo(PUERTA_IZQ, pal["shine"], pal["shine_op"]))
    # tiradores
    for q, sgn in ((PUERTA_IZQ, 1), (PUERTA_DER, 1)):
        p0 = bilinear(q, 0.115, 0.44)
        p1 = bilinear(q, 0.115, 0.60)
        g.append('<line x1="%.1f" y1="%.1f" x2="%.1f" y2="%.1f" stroke="%s" stroke-width="15" '
                 'stroke-linecap="round"/>' % (p0[0], p0[1], p1[0], p1[1],
                                               pal["handle"] if mono is None else mono))
    return "".join(g)


def ventanal_fit(x, y, height=None, width=None, **kw):
    s = (height / BBOX_H) if height is not None else (width / BBOX_W)
    return '<g transform="translate(%.3f,%.3f) scale(%.5f) translate(%.2f,%.2f)">%s</g>' % (
        x, y, s, -BBOX[0], -BBOX[1], ventanal(**kw))
