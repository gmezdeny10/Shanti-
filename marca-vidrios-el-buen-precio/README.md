# Vidrios el buen precio — kit de marca

Rediseño del logo original manteniendo su identidad (el ventanal en perspectiva,
el nombre completo y su eslogan), reconstruido en vectores, con la paleta de la
marca en rojo y amarillo y proporciones pensadas para redes sociales.

> Nota: esta carpeta es solo material de marca. No forma parte del sitio web que
> vive en el resto del repositorio.

## Qué cambió y por qué

| Antes | Ahora |
|---|---|
| Imagen rasterizada (JPG con fondo gris) | Vectores SVG: nítido en cualquier tamaño, fondo transparente disponible |
| Demasiadas divisiones de vidrio | 4 paños por cara: se sigue leyendo a 40 px |
| Texto largo debajo del dibujo, se cortaba en el recorte circular | Versión de avatar con el texto dentro del círculo seguro, y versión solo ícono |
| Trazos y brillos irregulares | Trazo uniforme, esquinas redondeadas, brillo limpio dentro del vidrio |
| Tipografía itálica genérica | Poppins (geométrica, moderna), con el eslogan justificado al ancho del nombre |
| Celeste, rojo, amarillo y negro mezclados | Paleta cerrada: rojo, amarillo y blanco |
| Sin versiones alternativas | Claro, oscuro, monocromo, horizontal, vertical, portadas |

## Paleta

| Uso | Color | HEX |
|---|---|---|
| Tinta / contorno | rojo profundo | `#8E1015` |
| Fondo oscuro | rojo oscuro | `#C4171F` → `#6B0A0E` |
| Vidrio iluminado | amarillo | `#FFE98A` → `#FFC61A` |
| Vidrio en sombra | ámbar | `#FFB300` → `#E08A00` |
| Acento (tiradores, línea) | rojo vivo | `#D7262C` |
| Marco | blanco | `#FFFFFF` |
| Fondo claro | crema | `#FFF7E8` |

Sobre fondo rojo el contorno del ícono va en blanco, para que la silueta no se
pierda contra el fondo. Sobre fondo claro va en rojo profundo.

## Tipografía

**Poppins** — ExtraBold para "VIDRIOS", Bold para "EL BUEN PRECIO", Medium para
el eslogan. Es gratuita: https://fonts.google.com/specimen/Poppins

## Qué archivo usar en cada lugar

| Dónde | Archivo | Medida |
|---|---|---|
| Foto de perfil Instagram / Facebook / TikTok / WhatsApp | `avatar-oscuro.png` | 1080×1080 |
| Perfil sobre fondo claro | `avatar-claro.png` | 1080×1080 |
| Perfil muy chico (favicon, WhatsApp, comentarios) | `avatar-solo-icono-oscuro.png` | 1080×1080 |
| Portada de página de Facebook | `portada-facebook.png` | 1640×624 |
| Portada YouTube / LinkedIn | `portada-linkedin-youtube.png` | 2048×1152 |
| Membrete, presupuestos, sitio web | `logo-horizontal.png` | 2400 px de ancho |
| Sobre fotos o fondos oscuros | `logo-horizontal-blanco.png` | 2400 px |
| Marca de agua en fotos de trabajos | `logo-monocromo-blanco.png` | 2400 px |
| Sello, factura, vinilo de un color | `logo-monocromo-rojo.png` | 2400 px |
| Fotocopia, fax, grabado | `logo-monocromo-negro.png` | 2400 px |
| Flyer, cartel, camiseta | `logo-vertical.png` | 1600 px |
| Solo la marca gráfica | `icono.png` / `icono.svg` | 1024×1024 |

Los `.svg` de `svg/` son los originales: se escalan sin perder calidad y son los
que hay que darle a una imprenta o a quien haga un cartel.

## Reglas simples de uso

1. Dejar alrededor del logo un margen libre igual a la altura de la letra "V".
2. No estirar ni deformar: escalar siempre proporcional.
3. No cambiar los colores ni poner el logo a color sobre fondos saturados; para
   esos casos están las versiones blanca y monocroma.
4. En avatares no agregar texto extra: el círculo recorta las esquinas.
5. Tamaño mínimo del logo horizontal: 25 mm impreso / 160 px en pantalla. Más
   chico que eso, usar solo el ícono.

## Regenerar los archivos

```bash
pip install cairosvg fonttools
# descargar Poppins-ExtraBold/Bold/SemiBold/Medium .ttf en ../fonts/
python3 fuente/make.py
```
