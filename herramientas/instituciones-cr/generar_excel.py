#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Genera un archivo Excel con TODAS las instituciones educativas de Costa Rica
—públicas, privadas y privadas subvencionadas— a partir de las fuentes
oficiales: MEP (nómina de centros educativos), CONESUP (universidades
privadas), CONARE (universidades estatales) y Consejo Superior de Educación
(instituciones parauniversitarias).

Uso:

    pip install openpyxl
    python3 generar_excel.py                    # descarga las fuentes y genera el Excel
    python3 generar_excel.py --niveles todos    # incluye también preescolar
    python3 generar_excel.py --entrada a.xlsx b.csv   # usa archivos ya descargados
    python3 generar_excel.py --autoprueba       # verifica el procesamiento sin internet

El detalle de las fuentes y el modo manual están en README.md.
"""
from __future__ import annotations

import argparse
import csv
import datetime as dt
import html as html_mod
import re
import sys
import time
import unicodedata
import urllib.error
import urllib.parse
import urllib.request
from html.parser import HTMLParser
from pathlib import Path

AGENTE = "Mozilla/5.0 (compatible; generador-excel-instituciones-cr/1.0)"
TIEMPO_ESPERA = 120
REINTENTOS = 4

# --------------------------------------------------------------------------
# Fuentes oficiales
# --------------------------------------------------------------------------

# Páginas índice: se leen y de ahí se descubren los enlaces a .xlsx / .xls / .csv
FUENTES_INDICE = [
    {
        "nombre": "MEP – Estadísticas educativas (nómina de centros educativos)",
        "url": "https://www.mep.go.cr/acerca-del-mep/analisis-estadistico/estadisticas-educativas",
        "claves": ("nomina", "centro", "directorio", "institucion", "educativo"),
    },
    {
        "nombre": "MEP/DGTH – Directorio de centros educativos",
        "url": "https://dgth.mep.go.cr/directorio-de-centros-educativos/",
        "claves": ("nomina", "centro", "directorio", "telefon"),
    },
    {
        "nombre": "MEP – Documentos de supervisión de centros educativos",
        "url": "https://www.mep.go.cr/supervision-centros-educativos/documentos",
        "claves": ("nomina", "centro", "directorio", "privado"),
    },
]

# Archivos conocidos que se intentan directamente (por si cambia la página índice)
ARCHIVOS_DIRECTOS = [
    {
        "nombre": "MEP/DGTH – Información telefónica de los centros educativos",
        "url": "https://dgth.mep.go.cr/wp-content/uploads/2020/03/centros-educativos-nomina.xlsx",
    },
    {
        "nombre": "MEP – Matriz de reportes por Dirección Regional y centro educativo",
        "url": "https://www.mep.go.cr/sites/default/files/2023-03/matriz-reportes-DRE-centros-educativos.xlsx",
    },
]

# Páginas cuyo listado viene en una tabla HTML
PAGINAS_TABLA = [
    {
        "nombre": "CONESUP – Universidades privadas autorizadas",
        "url": "https://conesup.mep.go.cr/lista_universidades",
        "nivel": "Universitaria",
        "sector": "Privado",
    },
    {
        "nombre": "CSE – Instituciones parauniversitarias autorizadas",
        "url": "http://cse.go.cr/actas/instituciones-parauniversitarias",
        "nivel": "Parauniversitaria",
        "sector": "",
    },
]

# Universidades públicas (CONARE). Son cinco y no cambian; van fijas para que
# el archivo nunca quede sin la educación superior estatal.
UNIVERSIDADES_ESTATALES = [
    ("UCR", "Universidad de Costa Rica", "San José", "Montes de Oca", "https://www.ucr.ac.cr"),
    ("TEC", "Instituto Tecnológico de Costa Rica", "Cartago", "Cartago", "https://www.tec.ac.cr"),
    ("UNA", "Universidad Nacional", "Heredia", "Heredia", "https://www.una.ac.cr"),
    ("UNED", "Universidad Estatal a Distancia", "San José", "Montes de Oca", "https://www.uned.ac.cr"),
    ("UTN", "Universidad Técnica Nacional", "Alajuela", "Alajuela", "https://www.utn.ac.cr"),
]

# --------------------------------------------------------------------------
# Esquema de salida
# --------------------------------------------------------------------------

COLUMNAS = [
    "Código",
    "Nombre de la institución",
    "Nivel / Oferta",
    "Categoría",
    "Sector",
    "Dependencia (texto original)",
    "Dirección Regional",
    "Circuito",
    "Provincia",
    "Cantón",
    "Distrito",
    "Dirección exacta",
    "Zona",
    "Teléfono",
    "Correo electrónico",
    "Sitio web",
    "Fuente",
    "Archivo de origen",
    "Fecha de extracción",
]

# El orden importa: el primer patrón que calce se queda con la columna, así que
# lo específico ("dirección regional") va antes que lo general ("dirección").
MAPA_COLUMNAS = [
    ("Dirección Regional", ("direccion regional", "dir regional", "region educativa", "dre")),
    ("Circuito", ("circuito",)),
    ("Código", ("codigo presupuestario", "codigo del centro", "codigo mep", "cod presupuestario", "codigo", "cedula")),
    ("Nombre de la institución", ("nombre del centro", "nombre de la institucion", "nombre institucion",
                                  "centro educativo", "institucion", "nombre", "universidad")),
    ("Nivel / Oferta", ("nivel", "oferta", "modalidad", "tipo de centro", "ciclo", "servicio")),
    ("Dependencia (texto original)", ("dependencia", "sector", "naturaleza", "tipo de institucion")),
    ("Provincia", ("provincia",)),
    ("Cantón", ("canton",)),
    ("Distrito", ("distrito",)),
    ("Zona", ("zona",)),
    ("Dirección exacta", ("direccion exacta", "senas", "direccion", "ubicacion", "domicilio")),
    ("Teléfono", ("telefono", "tel.", "numero telefonico", "celular")),
    ("Correo electrónico", ("correo", "email", "e-mail")),
    ("Sitio web", ("sitio web", "pagina web", "web", "url")),
]

CATEGORIAS = {
    "preescolar": "Preescolar",
    "primaria": "Primaria",
    "secundaria": "Secundaria",
    "adultos": "Educación de adultos",
    "especial": "Educación especial",
    "parauniversitaria": "Parauniversitaria",
    "universitaria": "Universitaria",
    "tecnica": "Formación técnica",
    "otros": "Otros / sin clasificar",
}

# Niveles con carné estudiantil: todo menos preescolar.
NIVELES_POR_OMISION = ("primaria", "secundaria", "adultos", "especial",
                       "parauniversitaria", "universitaria", "tecnica", "otros")


def normalizar(texto) -> str:
    """Minúsculas, sin tildes y con espacios colapsados."""
    if texto is None:
        return ""
    texto = str(texto)
    texto = unicodedata.normalize("NFKD", texto)
    texto = "".join(c for c in texto if not unicodedata.combining(c))
    return re.sub(r"\s+", " ", texto).strip().lower()


def clasificar_categoria(*textos) -> str:
    """Deduce la categoría educativa a partir del nivel y/o del nombre."""
    t = " ".join(normalizar(x) for x in textos if x)
    reglas = [
        ("universitaria", ("universidad", "universitaria de", "uned", "itcr", "tecnologico de costa rica")),
        ("parauniversitaria", ("parauniversitari", "colegio universitario", "escuela centroamericana de ganaderia")),
        ("adultos", ("cindea", "ipec", "educacion de adultos", "escuela nocturna", "colegio nocturno",
                     "plan nacional", "educacion abierta")),
        ("especial", ("educacion especial", "ensenanza especial", "centro de atencion integral")),
        ("tecnica", ("instituto nacional de aprendizaje", "capacitacion", "formacion profesional")),
        ("secundaria", ("secundaria", "liceo", "colegio", "ctp", "tecnico profesional", "bachillerato",
                        "iii ciclo", "educacion diversificada", "unidad pedagogica")),
        ("primaria", ("primaria", "escuela", "i y ii ciclo", "i ciclo", "ii ciclo")),
        ("preescolar", ("preescolar", "jardin de ninos", "materno", "transicion", "kinder", "cen cinai", "cen-cinai")),
    ]
    for clave, patrones in reglas:
        if any(p in t for p in patrones):
            return clave
    return "otros"


def clasificar_sector(*textos) -> str:
    """Público / Privado / Privado subvencionado según la dependencia."""
    t = " ".join(normalizar(x) for x in textos if x)
    if "subvencion" in t:
        return "Privado subvencionado"
    if any(p in t for p in ("privad", "particular")):
        return "Privado"
    if any(p in t for p in ("public", "estatal", "oficial", "dependencia publica", "mep")):
        return "Público"
    return "Sin determinar"


# --------------------------------------------------------------------------
# Descarga
# --------------------------------------------------------------------------

def descargar(url: str, destino: Path | None = None) -> bytes:
    """Descarga con reintentos y espera exponencial (2s, 4s, 8s, 16s)."""
    espera = 2
    ultimo_error = None
    for intento in range(1, REINTENTOS + 1):
        try:
            peticion = urllib.request.Request(url, headers={"User-Agent": AGENTE})
            with urllib.request.urlopen(peticion, timeout=TIEMPO_ESPERA) as respuesta:
                datos = respuesta.read()
            if destino:
                destino.parent.mkdir(parents=True, exist_ok=True)
                destino.write_bytes(datos)
            return datos
        except Exception as error:  # red, TLS, 403, 404...
            ultimo_error = error
            if intento < REINTENTOS:
                print(f"    intento {intento} falló ({error}); reintentando en {espera}s")
                time.sleep(espera)
                espera *= 2
    raise RuntimeError(f"no se pudo descargar {url}: {ultimo_error}")


def descubrir_enlaces(html_texto: str, url_base: str, claves) -> list[str]:
    """Busca enlaces a hojas de cálculo relacionadas con centros educativos."""
    encontrados = []
    for href in re.findall(r'href=["\']([^"\']+)["\']', html_texto, flags=re.I):
        href = html_mod.unescape(href)
        absoluto = urllib.parse.urljoin(url_base, href)
        ruta = normalizar(urllib.parse.urlparse(absoluto).path)
        if not ruta.endswith((".xlsx", ".xls", ".csv")):
            continue
        if claves and not any(clave in ruta for clave in claves):
            continue
        if absoluto not in encontrados:
            encontrados.append(absoluto)
    return encontrados


class LectorTablas(HTMLParser):
    """Extrae las tablas de una página como listas de filas de texto."""

    def __init__(self):
        super().__init__(convert_charrefs=True)
        self.tablas: list[list[list[str]]] = []
        self._tabla = None
        self._fila = None
        self._celda = None

    def handle_starttag(self, etiqueta, atributos):
        if etiqueta == "table":
            self._tabla = []
        elif etiqueta == "tr" and self._tabla is not None:
            self._fila = []
        elif etiqueta in ("td", "th") and self._fila is not None:
            self._celda = []

    def handle_endtag(self, etiqueta):
        if etiqueta in ("td", "th") and self._celda is not None:
            self._fila.append(re.sub(r"\s+", " ", "".join(self._celda)).strip())
            self._celda = None
        elif etiqueta == "tr" and self._fila is not None:
            if any(c for c in self._fila):
                self._tabla.append(self._fila)
            self._fila = None
        elif etiqueta == "table" and self._tabla is not None:
            if self._tabla:
                self.tablas.append(self._tabla)
            self._tabla = None

    def handle_data(self, datos):
        if self._celda is not None:
            self._celda.append(datos)


# --------------------------------------------------------------------------
# Lectura de hojas de cálculo
# --------------------------------------------------------------------------

def _cargar_openpyxl():
    try:
        import openpyxl  # noqa: F401
    except ImportError:
        sys.exit("Falta openpyxl. Instálalo con:  pip install openpyxl")
    return __import__("openpyxl")


def leer_tablas_xlsx(ruta: Path) -> list[list[list[str]]]:
    openpyxl = _cargar_openpyxl()
    libro = openpyxl.load_workbook(ruta, read_only=True, data_only=True)
    tablas = []
    for hoja in libro.worksheets:
        filas = []
        for fila in hoja.iter_rows(values_only=True):
            filas.append(["" if c is None else str(c).strip() for c in fila])
        if filas:
            tablas.append(filas)
    libro.close()
    return tablas


def leer_tablas_csv(ruta: Path) -> list[list[list[str]]]:
    for codificacion in ("utf-8-sig", "latin-1"):
        try:
            texto = ruta.read_text(encoding=codificacion)
            break
        except UnicodeDecodeError:
            continue
    else:
        return []
    try:
        delimitador = csv.Sniffer().sniff(texto[:8192], delimiters=",;\t|").delimiter
    except csv.Error:
        delimitador = ","
    filas = [[c.strip() for c in fila] for fila in csv.reader(texto.splitlines(), delimiter=delimitador)]
    return [filas] if filas else []


def leer_tablas(ruta: Path) -> list[list[list[str]]]:
    sufijo = ruta.suffix.lower()
    if sufijo == ".xlsx":
        return leer_tablas_xlsx(ruta)
    if sufijo in (".csv", ".txt"):
        return leer_tablas_csv(ruta)
    if sufijo == ".xls":
        print(f"    aviso: {ruta.name} está en formato .xls antiguo; ábrelo y guárdalo como .xlsx")
        return []
    print(f"    aviso: formato no soportado: {ruta.name}")
    return []


def mapear_columnas(encabezados: list[str]) -> dict[str, int]:
    """Asocia cada campo del esquema con la columna del archivo de origen."""
    mapa: dict[str, int] = {}
    usados: set[int] = set()
    normalizados = [normalizar(e) for e in encabezados]
    for campo, patrones in MAPA_COLUMNAS:
        for patron in patrones:
            for i, encabezado in enumerate(normalizados):
                if i in usados or not encabezado:
                    continue
                if patron in encabezado:
                    mapa[campo] = i
                    usados.add(i)
                    break
            if campo in mapa:
                break
    return mapa


def localizar_encabezado(filas: list[list[str]]) -> tuple[int, dict[str, int]] | None:
    """Encuentra la fila de encabezados (las nóminas del MEP traen títulos arriba)."""
    for i, fila in enumerate(filas[:25]):
        if sum(1 for c in fila if c) < 2:
            continue
        mapa = mapear_columnas(fila)
        if "Nombre de la institución" in mapa and len(mapa) >= 2:
            return i, mapa
    return None


def procesar_tabla(filas, fuente: str, archivo: str, fecha: str,
                   nivel_fijo: str = "", sector_fijo: str = "") -> list[dict]:
    """Convierte una tabla cruda en registros del esquema de salida."""
    ubicado = localizar_encabezado(filas)
    if not ubicado:
        return []
    inicio, mapa = ubicado
    registros = []
    for fila in filas[inicio + 1:]:
        if not any(c for c in fila):
            continue
        registro = {columna: "" for columna in COLUMNAS}
        for campo, indice in mapa.items():
            if indice < len(fila):
                registro[campo] = fila[indice]
        nombre = registro["Nombre de la institución"]
        if not nombre or len(normalizar(nombre)) < 3:
            continue
        # Descarta filas de totales o de encabezado repetido.
        if normalizar(nombre) in ("total", "totales", "nombre", "centro educativo", "institucion"):
            continue
        if nivel_fijo and not registro["Nivel / Oferta"]:
            registro["Nivel / Oferta"] = nivel_fijo
        registro["Categoría"] = CATEGORIAS[clasificar_categoria(
            registro["Nivel / Oferta"], nombre, registro["Dependencia (texto original)"])]
        registro["Sector"] = sector_fijo or clasificar_sector(
            registro["Dependencia (texto original)"], registro["Nivel / Oferta"], nombre)
        registro["Fuente"] = fuente
        registro["Archivo de origen"] = archivo
        registro["Fecha de extracción"] = fecha
        registros.append(registro)
    return registros


# --------------------------------------------------------------------------
# Consolidación
# --------------------------------------------------------------------------

# Al unificar duplicados gana el dato más específico: "Privado subvencionado"
# describe mejor que "Privado", y cualquiera de los dos mejor que "Sin determinar".
PRECEDENCIA_SECTOR = {"Sin determinar": 0, "Público": 1, "Privado": 1, "Privado subvencionado": 2}


def clave_registro(registro: dict) -> str:
    codigo = re.sub(r"\W", "", normalizar(registro["Código"]))
    if codigo and codigo not in ("0", "na", "nd"):
        return "c:" + codigo
    return "n:" + normalizar(registro["Nombre de la institución"]) + "|" + normalizar(registro["Cantón"])


def consolidar(registros: list[dict]) -> tuple[list[dict], int]:
    """Une duplicados completando los campos vacíos con los de la otra fuente."""
    unicos: dict[str, dict] = {}
    for registro in registros:
        clave = clave_registro(registro)
        if clave not in unicos:
            unicos[clave] = registro
            continue
        base = unicos[clave]
        for columna in COLUMNAS:
            if not base[columna] and registro[columna]:
                base[columna] = registro[columna]
        if registro["Fuente"] not in base["Fuente"]:
            base["Fuente"] += " / " + registro["Fuente"]
        if PRECEDENCIA_SECTOR.get(registro["Sector"], 0) > PRECEDENCIA_SECTOR.get(base["Sector"], 0):
            base["Sector"] = registro["Sector"]
            if registro["Dependencia (texto original)"]:
                base["Dependencia (texto original)"] = registro["Dependencia (texto original)"]
    duplicados = len(registros) - len(unicos)
    return list(unicos.values()), duplicados


def ordenar(registros: list[dict]) -> list[dict]:
    return sorted(registros, key=lambda r: (normalizar(r["Provincia"]), normalizar(r["Cantón"]),
                                            normalizar(r["Nombre de la institución"])))


# --------------------------------------------------------------------------
# Escritura del Excel
# --------------------------------------------------------------------------

def escribir_excel(registros: list[dict], bitacora: list[dict], destino: Path) -> None:
    openpyxl = _cargar_openpyxl()
    from openpyxl.styles import Alignment, Font, PatternFill
    from openpyxl.utils import get_column_letter

    libro = openpyxl.Workbook()
    relleno = PatternFill("solid", fgColor="1F3864")
    fuente_encabezado = Font(bold=True, color="FFFFFF")

    def hoja_datos(titulo: str, filas: list[dict]):
        hoja = libro.create_sheet(titulo[:31])
        hoja.append(COLUMNAS)
        for celda in hoja[1]:
            celda.fill = relleno
            celda.font = fuente_encabezado
            celda.alignment = Alignment(vertical="center", wrap_text=True)
        for registro in filas:
            hoja.append([registro[columna] for columna in COLUMNAS])
        for i, columna in enumerate(COLUMNAS, start=1):
            letra = get_column_letter(i)
            ancho = max(len(columna), *(len(str(r[columna])) for r in filas[:2000])) if filas else len(columna)
            hoja.column_dimensions[letra].width = min(max(ancho + 2, 10), 48)
            if columna in ("Código", "Teléfono"):
                for celda in hoja[letra][1:]:
                    celda.number_format = "@"
        hoja.freeze_panes = "A2"
        if filas:
            hoja.auto_filter.ref = f"A1:{get_column_letter(len(COLUMNAS))}{len(filas) + 1}"
        return hoja

    hoja_datos("Todas las instituciones", registros)
    hoja_datos("Públicas", [r for r in registros if r["Sector"] == "Público"])
    hoja_datos("Privadas", [r for r in registros if r["Sector"].startswith("Privado")])
    hoja_datos("Educación superior",
               [r for r in registros if r["Categoría"] in ("Universitaria", "Parauniversitaria")])

    resumen = libro.create_sheet("Resumen")
    resumen.append(["Resumen de instituciones educativas de Costa Rica"])
    resumen["A1"].font = Font(bold=True, size=14)
    resumen.append([])
    resumen.append(["Total de instituciones", len(registros)])

    def bloque(titulo: str, campo: str):
        resumen.append([])
        resumen.append([titulo, "Cantidad"])
        for celda in resumen[resumen.max_row]:
            celda.fill = relleno
            celda.font = fuente_encabezado
        conteo: dict[str, int] = {}
        for registro in registros:
            clave = registro[campo] or "(sin dato)"
            conteo[clave] = conteo.get(clave, 0) + 1
        for clave, cantidad in sorted(conteo.items(), key=lambda x: -x[1]):
            resumen.append([clave, cantidad])

    bloque("Por sector", "Sector")
    bloque("Por categoría educativa", "Categoría")
    bloque("Por provincia", "Provincia")
    resumen.column_dimensions["A"].width = 46
    resumen.column_dimensions["B"].width = 14

    hoja_fuentes = libro.create_sheet("Fuentes")
    encabezados = ["Fuente", "URL", "Archivo", "Registros aportados", "Estado", "Fecha"]
    hoja_fuentes.append(encabezados)
    for celda in hoja_fuentes[1]:
        celda.fill = relleno
        celda.font = fuente_encabezado
    for entrada in bitacora:
        hoja_fuentes.append([entrada.get("nombre", ""), entrada.get("url", ""), entrada.get("archivo", ""),
                             entrada.get("registros", 0), entrada.get("estado", ""), entrada.get("fecha", "")])
    for i, encabezado in enumerate(encabezados, start=1):
        hoja_fuentes.column_dimensions[get_column_letter(i)].width = max(len(encabezado) + 2, 22)
    hoja_fuentes.freeze_panes = "A2"

    del libro["Sheet"]
    destino.parent.mkdir(parents=True, exist_ok=True)
    libro.save(destino)


def escribir_csv(registros: list[dict], destino: Path) -> None:
    with destino.open("w", encoding="utf-8-sig", newline="") as archivo:
        escritor = csv.DictWriter(archivo, fieldnames=COLUMNAS)
        escritor.writeheader()
        escritor.writerows(registros)


# --------------------------------------------------------------------------
# Recolección
# --------------------------------------------------------------------------

def registros_universidades_estatales(fecha: str) -> list[dict]:
    registros = []
    for sigla, nombre, provincia, canton, web in UNIVERSIDADES_ESTATALES:
        registro = {columna: "" for columna in COLUMNAS}
        registro.update({
            "Código": sigla,
            "Nombre de la institución": nombre,
            "Nivel / Oferta": "Educación superior universitaria",
            "Categoría": CATEGORIAS["universitaria"],
            "Sector": "Público",
            "Dependencia (texto original)": "Universidad estatal (CONARE)",
            "Provincia": provincia,
            "Cantón": canton,
            "Sitio web": web,
            "Fuente": "CONARE – universidades estatales",
            "Archivo de origen": "lista fija en el script",
            "Fecha de extracción": fecha,
        })
        registros.append(registro)
    return registros


def recolectar_en_linea(carpeta: Path, fecha: str, bitacora: list[dict]) -> list[dict]:
    """Descarga las fuentes oficiales y devuelve los registros encontrados."""
    registros: list[dict] = []
    pendientes: list[tuple[str, str]] = [(f["nombre"], f["url"]) for f in ARCHIVOS_DIRECTOS]

    print("\n== Páginas índice del MEP ==")
    for indice in FUENTES_INDICE:
        print(f"  {indice['url']}")
        try:
            pagina = descargar(indice["url"]).decode("utf-8", errors="replace")
        except RuntimeError as error:
            print(f"    no disponible: {error}")
            bitacora.append({"nombre": indice["nombre"], "url": indice["url"], "estado": f"error: {error}",
                             "registros": 0, "fecha": fecha})
            continue
        enlaces = descubrir_enlaces(pagina, indice["url"], indice["claves"])
        print(f"    {len(enlaces)} archivo(s) encontrados")
        for enlace in enlaces:
            pendientes.append((indice["nombre"], enlace))

    print("\n== Hojas de cálculo ==")
    vistos = set()
    for nombre, url in pendientes:
        if url in vistos:
            continue
        vistos.add(url)
        archivo = carpeta / Path(urllib.parse.urlparse(url).path).name
        print(f"  {url}")
        try:
            descargar(url, archivo)
        except RuntimeError as error:
            print(f"    no disponible: {error}")
            bitacora.append({"nombre": nombre, "url": url, "archivo": archivo.name,
                             "estado": f"error: {error}", "registros": 0, "fecha": fecha})
            continue
        aportados = 0
        for tabla in leer_tablas(archivo):
            nuevos = procesar_tabla(tabla, nombre, archivo.name, fecha)
            registros.extend(nuevos)
            aportados += len(nuevos)
        print(f"    {aportados} registros")
        bitacora.append({"nombre": nombre, "url": url, "archivo": archivo.name,
                         "estado": "descargado" if aportados else "descargado (sin filas reconocidas)",
                         "registros": aportados, "fecha": fecha})

    print("\n== Listados en página web ==")
    for pagina_info in PAGINAS_TABLA:
        print(f"  {pagina_info['url']}")
        try:
            pagina = descargar(pagina_info["url"]).decode("utf-8", errors="replace")
        except RuntimeError as error:
            print(f"    no disponible: {error}")
            bitacora.append({"nombre": pagina_info["nombre"], "url": pagina_info["url"],
                             "estado": f"error: {error}", "registros": 0, "fecha": fecha})
            continue
        lector = LectorTablas()
        lector.feed(pagina)
        aportados = 0
        for tabla in lector.tablas:
            nuevos = procesar_tabla(tabla, pagina_info["nombre"], pagina_info["url"], fecha,
                                    nivel_fijo=pagina_info["nivel"], sector_fijo=pagina_info["sector"])
            registros.extend(nuevos)
            aportados += len(nuevos)
        print(f"    {aportados} registros")
        bitacora.append({"nombre": pagina_info["nombre"], "url": pagina_info["url"],
                         "estado": "leído" if aportados else "leído (sin tabla reconocida)",
                         "registros": aportados, "fecha": fecha})

    estatales = registros_universidades_estatales(fecha)
    registros.extend(estatales)
    bitacora.append({"nombre": "CONARE – universidades estatales", "url": "https://www.conare.ac.cr",
                     "archivo": "lista fija en el script", "estado": "incluido",
                     "registros": len(estatales), "fecha": fecha})
    return registros


def recolectar_archivos_locales(rutas: list[str], fecha: str, bitacora: list[dict]) -> list[dict]:
    registros: list[dict] = []
    for ruta_texto in rutas:
        ruta = Path(ruta_texto)
        print(f"  {ruta}")
        if not ruta.exists():
            print("    no existe")
            continue
        aportados = 0
        for tabla in leer_tablas(ruta):
            nuevos = procesar_tabla(tabla, f"Archivo local: {ruta.name}", ruta.name, fecha)
            registros.extend(nuevos)
            aportados += len(nuevos)
        print(f"    {aportados} registros")
        bitacora.append({"nombre": f"Archivo local: {ruta.name}", "url": str(ruta.resolve()),
                         "archivo": ruta.name, "estado": "procesado", "registros": aportados, "fecha": fecha})
    return registros


def informe(registros: list[dict], duplicados: int, descartados: int) -> None:
    print("\n== Informe de integridad ==")
    print(f"  Instituciones en el archivo final : {len(registros)}")
    print(f"  Duplicados unificados             : {duplicados}")
    print(f"  Filas fuera de los niveles pedidos: {descartados}")
    for columna in ("Código", "Provincia", "Cantón", "Teléfono", "Sector"):
        vacios = sum(1 for r in registros if not r[columna])
        print(f"  Sin {columna:<22}: {vacios}")
    sin_sector = sum(1 for r in registros if r["Sector"] == "Sin determinar")
    if sin_sector:
        print(f"  Aviso: {sin_sector} institución(es) sin público/privado determinado; "
              f"revisa la columna 'Dependencia (texto original)'.")


# --------------------------------------------------------------------------
# Autoprueba (sin internet)
# --------------------------------------------------------------------------

def autoprueba() -> int:
    """Genera datos ficticios, corre todo el flujo y verifica el resultado."""
    openpyxl = _cargar_openpyxl()
    import tempfile

    fecha = dt.date.today().isoformat()
    with tempfile.TemporaryDirectory() as temporal:
        carpeta = Path(temporal)

        libro = openpyxl.Workbook()
        hoja = libro.active
        hoja.append(["NÓMINA DE CENTROS EDUCATIVOS (archivo de prueba)"])
        hoja.append([])
        hoja.append(["Código presupuestario", "Nombre del centro educativo", "Dirección Regional",
                     "Circuito", "Provincia", "Cantón", "Distrito", "Nivel", "Dependencia",
                     "Zona", "Teléfono", "Correo"])
        hoja.append(["0001", "ESCUELA DE PRUEBA UNO", "SAN JOSÉ CENTRAL", "01", "San José", "Central",
                     "Carmen", "I y II Ciclo", "Pública", "Urbana", "2222-0001", "uno@prueba.cr"])
        hoja.append(["0002", "LICEO DE PRUEBA DOS", "CARTAGO", "03", "Cartago", "Cartago", "Oriental",
                     "III Ciclo y Educación Diversificada", "Privado", "Urbana", "2222-0002", ""])
        hoja.append(["0003", "JARDÍN DE NIÑOS DE PRUEBA", "HEREDIA", "02", "Heredia", "Heredia", "Mercedes",
                     "Preescolar", "Pública", "Urbana", "", ""])
        hoja.append(["Total", "", "", "", "", "", "", "", "", "", "", ""])
        archivo_xlsx = carpeta / "nomina-prueba.xlsx"
        libro.save(archivo_xlsx)

        archivo_csv = carpeta / "privados-prueba.csv"
        archivo_csv.write_text(
            "Codigo;Nombre de la institucion;Provincia;Canton;Nivel;Dependencia;Telefono\n"
            "0002;LICEO DE PRUEBA DOS;Cartago;Cartago;Secundaria;Privado subvencionado;2222-0002\n"
            "0004;COLEGIO PRIVADO DE PRUEBA;Alajuela;Alajuela;Secundaria;Privado;2222-0004\n",
            encoding="utf-8")

        pagina = ("<table><tr><th>Nombre de la universidad</th><th>Provincia</th></tr>"
                  "<tr><td>UNIVERSIDAD DE PRUEBA</td><td>San José</td></tr></table>")

        bitacora: list[dict] = []
        registros = recolectar_archivos_locales([str(archivo_xlsx), str(archivo_csv)], fecha, bitacora)
        lector = LectorTablas()
        lector.feed(pagina)
        for tabla in lector.tablas:
            registros.extend(procesar_tabla(tabla, "Página de prueba", "prueba.html", fecha,
                                            nivel_fijo="Universitaria", sector_fijo="Privado"))
        registros.extend(registros_universidades_estatales(fecha))

        fallos = []
        nombres = {r["Nombre de la institución"] for r in registros}
        if "ESCUELA DE PRUEBA UNO" not in nombres:
            fallos.append("no se leyó el .xlsx con encabezado desplazado")
        if "COLEGIO PRIVADO DE PRUEBA" not in nombres:
            fallos.append("no se leyó el .csv con punto y coma")
        if "UNIVERSIDAD DE PRUEBA" not in nombres:
            fallos.append("no se leyó la tabla HTML")
        if any(normalizar(n) == "total" for n in nombres):
            fallos.append("no se descartó la fila de totales")

        antes = len(registros)
        registros, duplicados = consolidar(registros)
        if duplicados != 1:
            fallos.append(f"se esperaba 1 duplicado unificado y hubo {duplicados} (de {antes} filas)")
        liceo = next((r for r in registros if r["Código"] == "0002"), None)
        if not liceo or liceo["Sector"] != "Privado subvencionado":
            fallos.append("no se completó el sector del registro duplicado")

        categorias = {r["Nombre de la institución"]: r["Categoría"] for r in registros}
        esperadas = {
            "ESCUELA DE PRUEBA UNO": "Primaria",
            "LICEO DE PRUEBA DOS": "Secundaria",
            "JARDÍN DE NIÑOS DE PRUEBA": "Preescolar",
            "UNIVERSIDAD DE PRUEBA": "Universitaria",
            "Universidad de Costa Rica": "Universitaria",
        }
        for nombre, categoria in esperadas.items():
            if categorias.get(nombre) != categoria:
                fallos.append(f"categoría incorrecta para {nombre}: {categorias.get(nombre)}")

        filtrados = [r for r in registros if r["Categoría"] != "Preescolar"]
        if len(filtrados) != len(registros) - 1:
            fallos.append("el filtro por niveles no descartó preescolar")

        destino = carpeta / "prueba.xlsx"
        escribir_excel(ordenar(filtrados), bitacora, destino)
        if not destino.exists() or destino.stat().st_size < 4000:
            fallos.append("el Excel no se generó correctamente")
        else:
            comprobacion = openpyxl.load_workbook(destino)
            if [h for h in comprobacion.sheetnames] != ["Todas las instituciones", "Públicas", "Privadas",
                                                        "Educación superior", "Resumen", "Fuentes"]:
                fallos.append(f"hojas inesperadas: {comprobacion.sheetnames}")
            hoja_todas = comprobacion["Todas las instituciones"]
            if hoja_todas.max_row != len(filtrados) + 1:
                fallos.append(f"filas escritas: {hoja_todas.max_row - 1}, esperadas {len(filtrados)}")
            comprobacion.close()

    if fallos:
        print("AUTOPRUEBA FALLIDA:")
        for fallo in fallos:
            print("  -", fallo)
        return 1
    print("Autoprueba correcta: lectura de .xlsx, .csv y HTML, clasificación, "
          "unificación de duplicados, filtro por nivel y escritura del Excel.")
    return 0


# --------------------------------------------------------------------------
# Programa principal
# --------------------------------------------------------------------------

def main(argv: list[str] | None = None) -> int:
    analizador = argparse.ArgumentParser(
        description="Genera un Excel con todas las instituciones educativas de Costa Rica.")
    analizador.add_argument("--salida", default="instituciones_educativas_costa_rica.xlsx",
                            help="archivo Excel a generar")
    analizador.add_argument("--entrada", nargs="*", default=None,
                            help="procesa archivos ya descargados (.xlsx o .csv) en vez de descargar")
    analizador.add_argument("--descargas", default="descargas",
                            help="carpeta donde guardar los archivos oficiales descargados")
    analizador.add_argument("--niveles", default=",".join(NIVELES_POR_OMISION),
                            help="niveles a incluir: " + ", ".join(CATEGORIAS) + ", o 'todos'")
    analizador.add_argument("--csv", action="store_true", help="genera también un .csv con los mismos datos")
    analizador.add_argument("--autoprueba", action="store_true",
                            help="verifica el procesamiento con datos ficticios, sin internet")
    args = analizador.parse_args(argv)

    if args.autoprueba:
        return autoprueba()

    fecha = dt.date.today().isoformat()
    bitacora: list[dict] = []

    if args.entrada:
        print("== Archivos locales ==")
        registros = recolectar_archivos_locales(args.entrada, fecha, bitacora)
        registros.extend(registros_universidades_estatales(fecha))
    else:
        registros = recolectar_en_linea(Path(args.descargas), fecha, bitacora)

    fallidas = [e for e in bitacora if str(e.get("estado", "")).startswith("error")]
    if fallidas:
        print("\n== Fuentes que no se pudieron leer ==")
        for entrada in fallidas:
            print(f"  - {entrada['nombre']}\n    {entrada['url']}")

    # Las universidades estatales van fijas en el script, así que no cuentan como
    # prueba de que los directorios oficiales se leyeron.
    de_directorios = sum(e.get("registros", 0) for e in bitacora
                         if "lista fija" not in str(e.get("archivo", "")))
    if de_directorios == 0:
        print("\nNo se pudo leer ningún directorio oficial, así que no se genera un Excel "
              "incompleto. Descarga los archivos a mano (ver README.md, sección "
              "«Si las descargas fallan») y vuelve a correr:\n"
              "  python3 generar_excel.py --entrada nomina.xlsx universidades.csv")
        return 1
    if de_directorios < 1000 and not args.entrada:
        print(f"\nADVERTENCIA: solo se leyeron {de_directorios} registros de los directorios oficiales. "
              "El directorio nacional de Costa Rica ronda las miles de instituciones, así que es muy "
              "probable que falten fuentes: revisa la hoja «Fuentes» del Excel y la sección "
              "«Si las descargas fallan» del README.")

    if args.niveles.strip().lower() == "todos":
        permitidas = set(CATEGORIAS.values())
    else:
        claves = [c.strip().lower() for c in args.niveles.split(",") if c.strip()]
        desconocidas = [c for c in claves if c not in CATEGORIAS]
        if desconocidas:
            analizador.error("niveles desconocidos: " + ", ".join(desconocidas))
        permitidas = {CATEGORIAS[c] for c in claves}

    antes = len(registros)
    registros = [r for r in registros if r["Categoría"] in permitidas]
    descartados = antes - len(registros)

    registros, duplicados = consolidar(registros)
    registros = ordenar(registros)

    destino = Path(args.salida)
    escribir_excel(registros, bitacora, destino)
    print(f"\nExcel generado: {destino.resolve()}")
    if args.csv:
        destino_csv = destino.with_suffix(".csv")
        escribir_csv(registros, destino_csv)
        print(f"CSV generado  : {destino_csv.resolve()}")

    informe(registros, duplicados, descartados)
    return 0


if __name__ == "__main__":
    sys.exit(main())
