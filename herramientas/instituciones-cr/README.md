# Instituciones educativas de Costa Rica → Excel

Genera un archivo Excel con **todas las instituciones educativas de Costa Rica**,
públicas y privadas, a partir de los directorios oficiales. Incluye los niveles
en los que el estudiantado tiene carné: primaria, secundaria (académica y
técnica), educación de adultos, educación especial, parauniversitaria y
universitaria. Preescolar queda fuera por omisión y se agrega con `--niveles todos`.

## Requisitos

- Python 3.9 o superior. **Nada más**: el Excel se escribe con la biblioteca
  estándar (`xlsx_minimo.py`). Si tenés `openpyxl` instalado se aprovecha, pero
  es opcional; `--motor estandar` fuerza el modo sin dependencias.
- Una computadora **con acceso a internet** (los sitios del MEP no son
  alcanzables desde el entorno donde se escribió esta herramienta).

## Uso

```bash
# 1. Genera el Excel descargando las fuentes oficiales
python3 generar_excel.py

# 2. Variantes útiles
python3 generar_excel.py --niveles todos          # agrega preescolar
python3 generar_excel.py --csv                    # genera también un .csv
python3 generar_excel.py --salida instituciones.xlsx
python3 generar_excel.py --autoprueba             # verifica el script sin internet
```

El archivo se llama `instituciones_educativas_costa_rica.xlsx` por omisión y los
archivos oficiales descargados quedan en `descargas/` para que puedas revisarlos.

## Qué contiene el Excel

Seis hojas:

| Hoja | Contenido |
| --- | --- |
| Todas las instituciones | El listado completo, ordenado por provincia, cantón y nombre |
| Públicas | Solo las de dependencia pública |
| Privadas | Privadas y privadas subvencionadas |
| Educación superior | Universidades y parauniversitarias |
| Resumen | Totales por sector, por categoría educativa y por provincia |
| Fuentes | Cada fuente usada, su URL, cuántos registros aportó y si se leyó bien |

Columnas: Código, Nombre de la institución, Nivel / Oferta, Categoría, Sector,
Dependencia (texto original), Dirección Regional, Circuito, Provincia, Cantón,
Distrito, Dirección exacta, Zona, Teléfono, Correo electrónico, Sitio web,
Fuente, Archivo de origen y Fecha de extracción.

La hoja **Fuentes** es la que permite comprobar que no falta nada: si una fuente
aparece con error o con cero registros, ese bloque de instituciones no entró.

## Fuentes oficiales

| Institución | Qué aporta | Dónde |
| --- | --- | --- |
| MEP – Análisis Estadístico | Nómina de centros educativos (pública y privada, todos los niveles) | <https://www.mep.go.cr/acerca-del-mep/analisis-estadistico/estadisticas-educativas> |
| MEP – Gestión del Talento Humano | Directorio con teléfonos, Dirección Regional y circuito | <https://dgth.mep.go.cr/directorio-de-centros-educativos/> |
| MEP – Supervisión de centros educativos | Listados de centros privados acreditados | <https://www.mep.go.cr/supervision-centros-educativos/documentos> |
| CONESUP | Universidades privadas autorizadas | <https://conesup.mep.go.cr/lista_universidades> |
| CONARE | Universidades estatales (UCR, TEC, UNA, UNED, UTN) | <https://www.conare.ac.cr> |
| Consejo Superior de Educación | Instituciones parauniversitarias autorizadas | <http://cse.go.cr/actas/instituciones-parauniversitarias> |

El script primero lee las páginas índice del MEP y **descubre solo los enlaces a
`.xlsx`, `.xls` y `.csv`** relacionados con centros educativos, de modo que sigue
funcionando cuando el MEP publica la nómina de un año nuevo. Además intenta dos
archivos conocidos por si la página índice cambia de estructura.

## Si las descargas fallan

Los sitios del MEP a veces bloquean descargas automáticas o cambian de ruta.
En ese caso descarga los archivos a mano y pásaselos al script:

1. Entra a <https://www.mep.go.cr/acerca-del-mep/analisis-estadistico/estadisticas-educativas>
   y baja la **«Nómina de Centros Educativos»** del año más reciente.
2. Entra a <https://dgth.mep.go.cr/directorio-de-centros-educativos/> y baja el
   directorio con información telefónica.
3. Para universidades privadas, abre <https://conesup.mep.go.cr/lista_universidades>,
   copia la tabla a una hoja de cálculo y guárdala como `.csv` o `.xlsx`.
4. Corre:

```bash
python3 generar_excel.py --entrada nomina-2026.xlsx directorio-dgth.xlsx universidades-conesup.csv
```

El script reconoce los encabezados aunque vengan con títulos arriba, con tildes o
con nombres distintos (`Código presupuestario`, `Nombre del centro educativo`,
`Dependencia`, `Dirección Regional`…), unifica los duplicados entre fuentes y
completa los campos que a una fuente le faltan con los de otra.

## Verificación

`python3 generar_excel.py --autoprueba` arma datos ficticios y comprueba, sin
internet, que el script lee `.xlsx`, `.csv` y tablas HTML, clasifica nivel y
sector, descarta filas de totales, unifica duplicados, aplica el filtro de
niveles y escribe el Excel con sus seis hojas. Corre con los dos motores
(openpyxl y el estándar) y relee el archivo generado para confirmar que las
hojas, los encabezados y los códigos quedaron completos.

## Archivos

| Archivo | Para qué |
| --- | --- |
| `generar_excel.py` | La herramienta: descarga, normaliza, consolida y escribe el Excel |
| `xlsx_minimo.py` | Lectura y escritura de `.xlsx` con solo la biblioteca estándar |

## Limitaciones

- Los datos son exactamente los que publica cada fuente oficial: si el MEP no
  publica el correo de un centro, la columna queda vacía.
- Las instituciones privadas sin acreditación vigente pueden no aparecer en los
  directorios del MEP.
- El script no inventa registros: cualquier vacío sale reportado en el informe
  de integridad que se imprime al final.
