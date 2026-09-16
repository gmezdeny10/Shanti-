# Vidrios el buen precio — kit de marca

Rediseño del logo original manteniendo su identidad (el ventanal en perspectiva,
el vidrio celeste, el acento rojo y el nombre completo con su eslogan), pero
reconstruido en vectores y con proporciones pensadas para redes sociales.

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
| Sin versiones alternativas | Claro, oscuro, monocromo, horizontal, vertical, portadas |

## Paleta

| Uso | Color | HEX |
|---|---|---|
| Tinta / contorno | azul noche | `#0E1B2A` |
| Fondo oscuro | azul profundo | `#0A1523` |
| Vidrio claro | celeste | `#8FE9F7` → `#31B4D9` |
| Vidrio en sombra | azul | `#2FA5CD` → `#1A7CA6` |
| Acento (tiradores, línea) | rojo | `#E63946` |
| Marco | blanco | `#FFFFFF` |
| Fondo claro | gris azulado | `#F3F7FA` |

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
| Sello, factura, vinilo de un color | `logo-monocromo-negro.png` | 2400 px |
| Flyer, cartel, camiseta | `logo-vertical.png` | 1600 px |
| Solo la marca gráfica | `icono.png` / `icono.svg` | 1024×1024 |

Los `.svg` de `svg/` son los originales: se escalan sin perder calidad y son los
que hay que darle a una imprenta o a quien haga un cartel.

## Reglas simples de uso

1. Dejar alrededor del logo un margen libre igual a la altura de la letra "V".
2. No estirar ni deformar: escalar siempre proporcional.
3. No cambiar los colores ni poner el logo a color sobre fondos saturados; para
   esos casos están las versiones blanca y monocromo.
4. En avatares no agregar texto extra: el círculo recorta las esquinas.
5. Tamaño mínimo del logo horizontal: 25 mm impreso / 160 px en pantalla. Más
   chico que eso, usar solo el ícono.

## Regenerar los archivos

```bash
pip install cairosvg fonttools
# descargar Poppins-ExtraBold/Bold/SemiBold/Medium .ttf en ../fonts/
python3 fuente/make.py
```
