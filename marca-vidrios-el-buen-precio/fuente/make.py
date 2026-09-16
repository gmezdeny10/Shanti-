# -*- coding: utf-8 -*-
import os, cairosvg
from brand import *

ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
OUT = os.path.join(ROOT, "out")
PREV = os.path.join(ROOT, "preview")
os.makedirs(OUT, exist_ok=True); os.makedirs(PREV, exist_ok=True)

EB, BD, SB, MD = "Poppins-ExtraBold", "Poppins-Bold", "Poppins-SemiBold", "Poppins-Medium"
CAP = 0.70  # altura de caja alta de Poppins en em

def justified(font, text, size, target, y=0, fill="#000"):
    nat = text_width(font, text, size)
    tr = (target - nat) / max(len(text) - 1, 1) / size
    d, w = text_path(font, text, size, tracking=tr, y=y)
    return '<path d="%s" fill="%s"/>' % (d, fill), w

def line(font, text, size, tr=0.0, y=0, fill="#000"):
    d, w = text_path(font, text, size, tracking=tr, y=y)
    return '<path d="%s" fill="%s"/>' % (d, fill), w

def svg(w, h, body, bg=None):
    return ('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 %d %d" width="%d" height="%d">'
            '%s%s%s</svg>') % (w, h, w, h, defs(), bg or "", body)

def write(name, markup, png_w=None, folder=OUT):
    p = os.path.join(folder, name + ".svg")
    open(p, "w").write(markup)
    if png_w:
        cairosvg.svg2png(url=p, write_to=os.path.join(folder, name + ".png"), output_width=png_w)
    return p

# ------------------------------------------------------ bloque tipografico
def wordmark(size, ink=NAVY, accent=RED, tagline=True):
    """Devuelve (markup, ancho, alto). Origen arriba-izquierda del bloque."""
    top = -CAP * size
    l1, w = line(EB, "VIDRIOS", size, tr=0.005, fill=ink)
    parts = [l1]
    y2 = size * 0.52
    l2, _ = justified(BD, "EL BUEN PRECIO", size * 0.40, w, y=y2, fill=ink)
    parts.append(l2)
    bottom = y2
    if tagline:
        ry = size * 0.72
        parts.append('<rect x="0" y="%.1f" width="%.1f" height="%.1f" rx="%.1f" fill="%s"/>'
                     % (ry, w, size * 0.030, size * 0.015, accent))
        y3 = ry + size * 0.29
        l3, _ = justified(MD, "LOS MEJORES PRECIOS DEL MERCADO", size * 0.175, w, y=y3, fill=ink)
        parts.append(l3)
        bottom = y3
    return ('<g transform="translate(0,%.2f)">%s</g>' % (-top, "".join(parts)), w, bottom - top)

# --------------------------------------------------------------- avatares
def bg_dark(w, h):
    return ('<rect width="%d" height="%d" fill="url(#bgDark)"/>'
            '<rect width="%d" height="%d" fill="url(#bgGlow)"/>') % (w, h, w, h)

def bg_light(w, h):
    return '<rect width="%d" height="%d" fill="#F3F7FA"/>' % (w, h)

def avatar(dark=True, with_text=True, transparent=False):
    ink = WHITE if dark else NAVY
    sub = "#7FDCF2" if dark else RED
    bg = None if transparent else (bg_dark(1000, 1000) if dark else bg_light(1000, 1000))
    body = []
    if with_text:
        ih = 400.0
        body.append(icon_fit((1000 - ih * BBOX_W / BBOX_H) / 2, 205, height=ih))
        t, w = line(EB, "VIDRIOS", 104, tr=0.015, fill=ink)
        body.append('<g transform="translate(%.1f,740)">%s</g>' % (500 - w / 2, t))
        t2, _ = justified(SB, "EL BUEN PRECIO", 46, w, fill=sub)
        body.append('<g transform="translate(%.1f,812)">%s</g>' % (500 - w / 2, t2))
    else:
        ih = 505.0
        body.append(icon_fit((1000 - ih * BBOX_W / BBOX_H) / 2, (1000 - ih) / 2, height=ih))
    return svg(1000, 1000, "".join(body), bg)

# ------------------------------------------------------------- lockups
def horizontal(dark=False, tagline=True, mono=None, transparent=True):
    ink = mono or (WHITE if dark else NAVY)
    size = 170
    wm, w, h = wordmark(size, ink=ink, accent=RED if not mono else ink, tagline=tagline)
    ih = 330.0
    iw = ih * BBOX_W / BBOX_H
    gap, pad = 78, 70
    W = int(pad * 2 + iw + gap + w)
    H = int(pad * 2 + max(ih, h))
    body = (icon_fit(pad, (H - ih) / 2, height=ih, mono=mono) +
            '<g transform="translate(%.1f,%.1f)">%s</g>' % (pad + iw + gap, (H - h) / 2, wm))
    bg = None if transparent else (bg_dark(W, H) if dark else bg_light(W, H))
    return svg(W, H, body, bg)

def vertical(dark=False, transparent=True):
    ink = WHITE if dark else NAVY
    size = 150
    wm, w, h = wordmark(size, ink=ink)
    ih = 420.0
    iw = ih * BBOX_W / BBOX_H
    pad, gap = 70, 56
    W = int(max(w, iw) + pad * 2)
    H = int(pad * 2 + ih + gap + h)
    body = (icon_fit((W - iw) / 2, pad, height=ih) +
            '<g transform="translate(%.1f,%.1f)">%s</g>' % ((W - w) / 2, pad + ih + gap, wm))
    bg = None if transparent else (bg_dark(W, H) if dark else bg_light(W, H))
    return svg(W, H, body, bg)

# ------------------------------------------------------------- portadas
def cover(W, H, size=120, ih=330.0):
    iw = ih * BBOX_W / BBOX_H
    wm, w, h = wordmark(size, ink=WHITE)
    total = iw + 64 + w
    x0 = (W - total) / 2
    body = [icon_fit(x0, (H - ih) / 2, height=ih),
            '<g transform="translate(%.1f,%.1f)">%s</g>' % (x0 + iw + 64, (H - h) / 2, wm)]
    return svg(W, H, "".join(body), bg_dark(W, H))

def icon_only(transparent=True, mono=None):
    ih = 860.0
    iw = ih * BBOX_W / BBOX_H
    body = icon_fit((1000 - iw) / 2, (1000 - ih) / 2, height=ih, mono=mono)
    return svg(1000, 1000, body, None if transparent else bg_dark(1000, 1000))

# ------------------------------------------------------------------ build
files = [
    ("avatar-oscuro",              avatar(True),                        1080),
    ("avatar-claro",               avatar(False),                       1080),
    ("avatar-solo-icono-oscuro",   avatar(True, with_text=False),       1080),
    ("avatar-solo-icono-claro",    avatar(False, with_text=False),      1080),
    ("icono",                      icon_only(),                         1024),
    ("logo-horizontal",            horizontal(False),                   2400),
    ("logo-horizontal-blanco",     horizontal(True),                    2400),
    ("logo-horizontal-simple",     horizontal(False, tagline=False),    2400),
    ("logo-vertical",              vertical(False),                     1600),
    ("logo-vertical-blanco",       vertical(True),                      1600),
    ("logo-monocromo-negro",       horizontal(False, mono=NAVY),        2400),
    ("logo-monocromo-blanco",      horizontal(True, mono=WHITE),        2400),
    ("portada-facebook",           cover(1640, 624),                    1640),
    ("portada-linkedin-youtube",   cover(2048, 1152, size=150, ih=420),  2048),
]
for name, markup, w in files:
    write(name, markup, w)

# pruebas de legibilidad
for size in (64, 128):
    cairosvg.svg2png(url=os.path.join(OUT, "avatar-oscuro.svg"),
                     write_to=os.path.join(PREV, "prueba-%dpx.png" % size), output_width=size)
    cairosvg.svg2png(url=os.path.join(OUT, "avatar-solo-icono-oscuro.svg"),
                     write_to=os.path.join(PREV, "prueba-icono-%dpx.png" % size), output_width=size)
print("OK", len(files), "piezas")
