# Vidrios el buen precio — kit de marca

El dibujo original, tal cual: el ventanal de cuatro paños en perspectiva, el
remate rojo del techo, la chapa roja del ángulo, los tiradores de las puertas,
el reflejo en diagonal y el nombre en itálica con su eslogan.

Lo único que cambió es el color, más la calidad del trazado: ahora está hecho en
vectores, así que se puede ampliar sin que se pixele.

> Nota: esta carpeta es solo material de marca. No forma parte del sitio web que
> vive en el resto del repositorio.

## Dos paletas para elegir

| Carpeta | Vidrio | Para qué sirve |
|---|---|---|
| `rojo-amarillo/` | amarillo y ámbar | La que pediste: rojo y amarillo. Más cálida y más visible en el feed. |
| `celeste/` | celeste y turquesa | La paleta original, limpia: mismos colores de siempre, mejor resueltos. |

En las dos, el trazo sigue siendo negro, el marco blanco y el remate rojo, igual
que en el logo original.

### Paleta rojo y amarillo

| Uso | HEX |
|---|---|
| Trazo | `#16161A` |
| Marco | `#FFFFFF` |
| Vidrio de las puertas | `#FFE889` → `#FFC21A` |
| Vidrio de los laterales | `#FFD04A` → `#EDA200` |
| Remate y chapa | `#E01B24` |
| Eslogan | `#D7262C` |
| Fondo oscuro | `#C4171F` → `#6B0A0E` |
| Fondo claro | `#FFF7E8` |

### Paleta celeste

| Uso | HEX |
|---|---|
| Vidrio de las puertas | `#A8ECF5` → `#4FC3DF` |
| Vidrio de los laterales | `#7BD8EA` → `#2FA3C4` |
| Reflejo | `#FFE535` |
| Fondo oscuro | `#123B4E` → `#0A1F2B` |
| Fondo claro | `#F1FAFC` |

## Qué mejoró del color

1. El vidrio dejó de ser un plano liso: tiene degradado, y los paños laterales
   son un tono más oscuro que los del frente. Eso es lo que da profundidad.
2. El reflejo amarillo quedó recortado dentro del vidrio; antes se desbordaba
   sobre el marco.
3. El rojo del remate y el de la chapa son ahora el mismo rojo.
4. El eslogan va en rojo: separa las dos líneas sin agregar otro color.
5. Sobre fondo oscuro, el nombre va en blanco y el eslogan en amarillo.

## Qué archivo usar

| Dónde | Archivo | Medida |
|---|---|---|
| Foto de perfil (Instagram, Facebook, TikTok) | `avatar-perfil.png` | 1080 × 1080 |
| Foto de perfil sobre fondo claro | `avatar-perfil-claro.png` | 1080 × 1080 |
| Foto de perfil chica (WhatsApp, comentarios) | `avatar-solo-ventanal.png` | 1080 × 1080 |
| Logo completo, fondo claro | `logo-principal.png` | 1800 px |
| Logo completo, fondo oscuro o foto | `logo-fondo-oscuro.png` | 1800 px |
| Membrete, presupuesto, sitio web | `logo-horizontal.png` | 2400 px |
| Sobre fotos oscuras | `logo-horizontal-blanco.png` | 2400 px |
| Marca de agua en fotos de trabajos | `logo-monocromo-blanco.png` | 1800 px |
| Sello, vinilo, factura a un color | `logo-monocromo-negro.png` | 1800 px |
| Portada de página de Facebook | `portada-facebook.png` | 1640 × 624 |
| Solo el ventanal | `ventanal-solo.png` | 1024 × 1024 |

Los `.svg` de cada carpeta son los originales: se escalan sin perder calidad y
son los que hay que darle a una imprenta o a un cartelero.

## Reglas simples de uso

1. Dejar alrededor del logo un margen libre igual a la altura de la "V".
2. No estirar ni deformar: escalar siempre proporcional.
3. Sobre foto o fondo de color, usar la versión de fondo oscuro o la blanca.
4. En la foto de perfil chica conviene `avatar-solo-ventanal`: el nombre no se
   llega a leer abajo de 100 px y ya aparece al lado del avatar en cada red.
5. Tamaño mínimo del logo completo: 30 mm impreso o 200 px en pantalla.

## Tipografía

**Archivo Bold Italic** para el nombre y el eslogan (itálica de palo seco, la más
parecida a la del logo original). Es gratuita:
https://fonts.google.com/specimen/Archivo

## Regenerar los archivos

```bash
pip install cairosvg fonttools
# en ../fonts/: Archivo-BoldItalic.ttf (instanciada de Archivo-Italic[wdth,wght])
python3 fuente/make3.py
```
