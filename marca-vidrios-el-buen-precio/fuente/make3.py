# -*- coding: utf-8 -*-
"""Kit de marca sobre el dibujo original, en dos paletas."""
import os, sys, cairosvg
sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))
from brand import text_path, text_width
from shop import PALETAS, gradientes, ventanal_fit, BBOX_W, BBOX_H

ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
TITLE, TAG = "Archivo-BoldItalic", "Archivo-BoldItalic"
CAP = 0.72

def fit(font, text, size, max_w):
    w = text_width(font, text, size)
    return (size * max_w / w, max_w) if w > max_w else (size, w)

def line(font, text, size, x=0, y=0, fill="#000", tr=0.0):
    d, w = text_path(font, text, size, tracking=tr, x=x, y=y)
    return '<path d="%s" fill="%s"/>' % (d, fill), w

def trim_dark(pal):
    """Sobre fondo rojo el remate pasa a amarillo; sobre azul sigue rojo."""
    return "#FFD23F" if pal["bg_dark"][0].upper().startswith("#C4") else None

def svg(w, h, pal, body, bg=None):
    return ('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 %d %d" width="%d" height="%d">'
            '%s%s%s</svg>') % (w, h, w, h, gradientes(pal), bg or "", body)

def write(folder, name, markup, png_w):
    os.makedirs(os.path.join(folder, "svg"), exist_ok=True)
    os.makedirs(os.path.join(folder, "png"), exist_ok=True)
    p = os.path.join(folder, "svg", name + ".svg")
    open(p, "w").write(markup)
    cairosvg.svg2png(url=p, write_to=os.path.join(folder, "png", name + ".png"), output_width=png_w)

def bloque_texto(pal, size, dark, max_w, mono=None):
    """Las dos lineas en italica, centradas. Devuelve (markup, ancho, alto)."""
    t_ink = mono or (pal["title_dark"] if dark else pal["title_light"])
    g_ink = mono or (pal["tag_dark"] if dark else pal["tag_light"])
    s1, w1 = fit(TITLE, "Vidrios el buen precio", size, max_w)
    s2, w2 = fit(TAG, "¡Los mejores precios del mercado!", s1 * 0.545, max_w * 0.94)
    w = max(w1, w2)
    l1, _ = line(TITLE, "Vidrios el buen precio", s1, x=(w - w1) / 2, fill=t_ink)
    y2 = s1 * 0.90
    l2, _ = line(TAG, "¡Los mejores precios del mercado!", s2, x=(w - w2) / 2, y=y2, fill=g_ink)
    top = -CAP * s1
    return ('<g transform="translate(0,%.2f)">%s%s</g>' % (-top, l1, l2), w, y2 - top + s2 * 0.24)

# ------------------------------------------------------------------ piezas
def avatar(pal, dark):
    bg = ('<rect width="1000" height="1000" fill="url(#bgDark)"/>' if dark
          else '<rect width="1000" height="1000" fill="%s"/>' % pal["bg_light"])
    ih = 430.0
    iw = ih * BBOX_W / BBOX_H
    body = [ventanal_fit((1000 - iw) / 2, 178, height=ih, pal=pal,
)]
    txt, w, h = bloque_texto(pal, 66, dark, 660)
    body.append('<g transform="translate(%.1f,%.1f)">%s</g>' % ((1000 - w) / 2, 668, txt))
    return svg(1000, 1000, pal, "".join(body), bg)

def principal(pal, dark=False, mono=None, transparent=True):
    ih = 560.0
    iw = ih * BBOX_W / BBOX_H
    pad, gap = 60, 40
    txt, w, h = bloque_texto(pal, 118, dark, iw * 1.12, mono=mono)
    W = int(max(iw, w) + pad * 2)
    H = int(pad * 2 + ih + gap + h)
    body = [ventanal_fit((W - iw) / 2, pad, height=ih, pal=pal, mono=mono),
            '<g transform="translate(%.1f,%.1f)">%s</g>' % ((W - w) / 2, pad + ih + gap, txt)]
    bg = None if transparent else ('<rect width="%d" height="%d" fill="url(#bgDark)"/>' % (W, H)
                                   if dark else '<rect width="%d" height="%d" fill="%s"/>' % (W, H, pal["bg_light"]))
    return svg(W, H, pal, "".join(body), bg)

def horizontal(pal, dark=False, mono=None):
    ih = 440.0
    iw = ih * BBOX_W / BBOX_H
    pad, gap = 56, 64
    txt, w, h = bloque_texto(pal, 132, dark, 1080, mono=mono)
    W = int(pad * 2 + iw + gap + w)
    H = int(pad * 2 + max(ih, h))
    body = [ventanal_fit(pad, (H - ih) / 2, height=ih, pal=pal, mono=mono),
            '<g transform="translate(%.1f,%.1f)">%s</g>' % (pad + iw + gap, (H - h) / 2, txt)]
    return svg(W, H, pal, "".join(body))

def portada(pal, W=1640, H=624):
    ih = 430.0
    iw = ih * BBOX_W / BBOX_H
    txt, w, h = bloque_texto(pal, 118, True, 900)
    total = iw + 60 + w
    x0 = (W - total) / 2
    body = [ventanal_fit(x0, (H - ih) / 2, height=ih, pal=pal),
            '<g transform="translate(%.1f,%.1f)">%s</g>' % (x0 + iw + 60, (H - h) / 2, txt)]
    return svg(W, H, pal, "".join(body), '<rect width="%d" height="%d" fill="url(#bgDark)"/>' % (W, H))

def avatar_ventanal(pal, dark=True):
    bg = ('<rect width="1000" height="1000" fill="url(#bgDark)"/>' if dark
          else '<rect width="1000" height="1000" fill="%s"/>' % pal["bg_light"])
    ih = 660.0
    iw = ih * BBOX_W / BBOX_H
    return svg(1000, 1000, pal,
               ventanal_fit((1000 - iw) / 2, (1000 - ih) / 2, height=ih, pal=pal,
), bg)

def solo_ventanal(pal, mono=None):
    ih = 860.0
    iw = ih * BBOX_W / BBOX_H
    return svg(1000, 1000, pal, ventanal_fit((1000 - iw) / 2, (1000 - ih) / 2,
                                             height=ih, pal=pal, mono=mono))

# ------------------------------------------------------------------- build
for nombre, pal in PALETAS.items():
    f = os.path.join(ROOT, "kit", nombre)
    write(f, "avatar-perfil",          avatar(pal, True),              1080)
    write(f, "avatar-perfil-claro",    avatar(pal, False),             1080)
    write(f, "logo-principal",         principal(pal),                 1800)
    write(f, "logo-fondo-oscuro",      principal(pal, dark=True),      1800)
    write(f, "logo-horizontal",        horizontal(pal),                2400)
    write(f, "logo-horizontal-blanco", horizontal(pal, dark=True),     2400)
    write(f, "logo-monocromo-negro",   principal(pal, mono="#16161A"), 1800)
    write(f, "logo-monocromo-blanco",  principal(pal, mono="#FFFFFF"),  1800)
    write(f, "portada-facebook",       portada(pal),                   1640)
    write(f, "avatar-solo-ventanal",   avatar_ventanal(pal),           1080)
    write(f, "ventanal-solo",          solo_ventanal(pal),             1024)
    print("kit:", nombre)
