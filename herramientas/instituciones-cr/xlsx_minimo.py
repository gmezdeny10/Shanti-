#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Lectura y escritura de archivos .xlsx usando solo la biblioteca estándar.

Existe para que generar_excel.py funcione en cualquier computadora con Python,
sin pedir `pip install openpyxl`. Cubre lo que esta herramienta necesita:
celdas de texto y números, encabezado con formato, fila congelada, filtros,
ancho de columnas y varias hojas.
"""
from __future__ import annotations

import re
import zipfile
from pathlib import Path
from xml.etree import ElementTree

NS = "http://schemas.openxmlformats.org/spreadsheetml/2006/main"
NS_REL = "http://schemas.openxmlformats.org/officeDocument/2006/relationships"
NS_PAQ = "http://schemas.openxmlformats.org/package/2006/relationships"


# --------------------------------------------------------------------------
# Lectura
# --------------------------------------------------------------------------

def _sin_ns(etiqueta: str) -> str:
    return etiqueta.split("}", 1)[-1]


def _texto_de_si(elemento) -> str:
    """Texto de una entrada de sharedStrings, uniendo los fragmentos de formato."""
    return "".join(t.text or "" for t in elemento.iter(f"{{{NS}}}t"))


def _columna_a_indice(referencia: str) -> int:
    """'A1' -> 0, 'B2' -> 1, 'AA3' -> 26."""
    letras = re.match(r"([A-Z]+)", referencia or "")
    if not letras:
        return -1
    indice = 0
    for caracter in letras.group(1):
        indice = indice * 26 + (ord(caracter) - 64)
    return indice - 1


def _numero_a_texto(valor: str) -> str:
    try:
        numero = float(valor)
    except (TypeError, ValueError):
        return valor or ""
    return str(int(numero)) if numero.is_integer() else str(numero)


def leer(ruta: Path) -> list[list[list[str]]]:
    """Devuelve una lista de hojas; cada hoja es una lista de filas de texto."""
    return [filas for _, filas in leer_con_nombres(ruta)]


def leer_con_nombres(ruta: Path) -> list[tuple[str, list[list[str]]]]:
    """Igual que leer(), pero cada hoja viene acompañada de su nombre."""
    with zipfile.ZipFile(ruta) as archivo:
        nombres = set(archivo.namelist())

        cadenas: list[str] = []
        if "xl/sharedStrings.xml" in nombres:
            raiz = ElementTree.fromstring(archivo.read("xl/sharedStrings.xml"))
            cadenas = [_texto_de_si(si) for si in raiz.iter(f"{{{NS}}}si")]

        relaciones: dict[str, str] = {}
        if "xl/_rels/workbook.xml.rels" in nombres:
            raiz = ElementTree.fromstring(archivo.read("xl/_rels/workbook.xml.rels"))
            for relacion in raiz:
                destino = relacion.get("Target", "")
                if destino.startswith("/"):
                    destino = destino[1:]
                elif not destino.startswith("xl/"):
                    destino = "xl/" + destino.lstrip("./")
                relaciones[relacion.get("Id", "")] = destino

        partes: list[tuple[str, str]] = []
        if "xl/workbook.xml" in nombres:
            raiz = ElementTree.fromstring(archivo.read("xl/workbook.xml"))
            for hoja in raiz.iter(f"{{{NS}}}sheet"):
                parte = relaciones.get(hoja.get(f"{{{NS_REL}}}id", ""))
                if parte in nombres:
                    partes.append((hoja.get("name", parte), parte))
        if not partes:
            partes = [(n, n) for n in sorted(nombres)
                      if n.startswith("xl/worksheets/") and n.endswith(".xml")]

        hojas = []
        for nombre, parte in partes:
            filas = _leer_hoja(archivo.read(parte), cadenas)
            if filas:
                hojas.append((nombre, filas))
        return hojas


def _leer_hoja(datos: bytes, cadenas: list[str]) -> list[list[str]]:
    filas: list[list[str]] = []
    raiz = ElementTree.fromstring(datos)
    for fila in raiz.iter(f"{{{NS}}}row"):
        celdas: list[str] = []
        for celda in fila:
            if _sin_ns(celda.tag) != "c":
                continue
            indice = _columna_a_indice(celda.get("r", ""))
            if indice < 0:
                indice = len(celdas)
            while len(celdas) < indice:
                celdas.append("")

            tipo = celda.get("t", "n")
            valor = ""
            if tipo == "inlineStr":
                elemento_is = celda.find(f"{{{NS}}}is")
                valor = _texto_de_si(elemento_is) if elemento_is is not None else ""
            else:
                elemento_v = celda.find(f"{{{NS}}}v")
                bruto = elemento_v.text if elemento_v is not None else ""
                if tipo == "s":
                    try:
                        valor = cadenas[int(bruto)]
                    except (TypeError, ValueError, IndexError):
                        valor = ""
                elif tipo in ("str", "e"):
                    valor = bruto or ""
                elif tipo == "b":
                    valor = "VERDADERO" if bruto == "1" else "FALSO"
                else:
                    valor = _numero_a_texto(bruto)
            celdas.append((valor or "").strip())
        if celdas:
            filas.append(celdas)
    return filas


# --------------------------------------------------------------------------
# Escritura
# --------------------------------------------------------------------------

CONTROL = re.compile(r"[\x00-\x08\x0b\x0c\x0e-\x1f]")


def _escapar(texto: str) -> str:
    texto = CONTROL.sub("", str(texto))
    return texto.replace("&", "&amp;").replace("<", "&lt;").replace(">", "&gt;")


def _indice_a_columna(indice: int) -> str:
    letras = ""
    indice += 1
    while indice:
        indice, resto = divmod(indice - 1, 26)
        letras = chr(65 + resto) + letras
    return letras


ESTILOS = f"""<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<styleSheet xmlns="{NS}">
<numFmts count="0"/>
<fonts count="3">
  <font><sz val="11"/><name val="Calibri"/></font>
  <font><b/><color rgb="FFFFFFFF"/><sz val="11"/><name val="Calibri"/></font>
  <font><b/><sz val="14"/><name val="Calibri"/></font>
</fonts>
<fills count="3">
  <fill><patternFill patternType="none"/></fill>
  <fill><patternFill patternType="gray125"/></fill>
  <fill><patternFill patternType="solid"><fgColor rgb="FF1F3864"/><bgColor indexed="64"/></patternFill></fill>
</fills>
<borders count="1"><border/></borders>
<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>
<cellXfs count="4">
  <xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>
  <xf numFmtId="0" fontId="1" fillId="2" borderId="0" xfId="0" applyFont="1" applyFill="1" applyAlignment="1">
    <alignment vertical="center" wrapText="1"/></xf>
  <xf numFmtId="49" fontId="0" fillId="0" borderId="0" xfId="0" applyNumberFormat="1"/>
  <xf numFmtId="0" fontId="2" fillId="0" borderId="0" xfId="0" applyFont="1"/>
</cellXfs>
<cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles>
</styleSheet>"""

ESTILO_NORMAL, ESTILO_ENCABEZADO, ESTILO_TEXTO, ESTILO_TITULO = 0, 1, 2, 3


def escribir(hojas: list[dict], destino: Path) -> None:
    """Escribe el libro. Cada hoja es un diccionario con:

    titulo   : nombre de la hoja
    filas    : lista de filas; cada celda es str, int, float o una tupla
               (valor, estilo) con uno de los ESTILO_* de este módulo
    encabezado: True si la primera fila es encabezado (se congela y se filtra)
    columnas_texto: índices de columnas que van con formato de texto
    anchos   : lista opcional de anchos de columna
    """
    destino.parent.mkdir(parents=True, exist_ok=True)
    with zipfile.ZipFile(destino, "w", zipfile.ZIP_DEFLATED) as archivo:
        partes = []
        for i, hoja in enumerate(hojas, start=1):
            parte = f"xl/worksheets/sheet{i}.xml"
            partes.append(parte)
            archivo.writestr(parte, _hoja_xml(hoja))

        tipos = ['<?xml version="1.0" encoding="UTF-8" standalone="yes"?>',
                 '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">',
                 '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>',
                 '<Default Extension="xml" ContentType="application/xml"/>',
                 '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-'
                 'officedocument.spreadsheetml.sheet.main+xml"/>',
                 '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-'
                 'officedocument.spreadsheetml.styles+xml"/>']
        for parte in partes:
            tipos.append(f'<Override PartName="/{parte}" ContentType="application/vnd.openxmlformats-'
                         'officedocument.spreadsheetml.worksheet+xml"/>')
        tipos.append("</Types>")
        archivo.writestr("[Content_Types].xml", "".join(tipos))

        archivo.writestr("_rels/.rels",
                         '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
                         f'<Relationships xmlns="{NS_PAQ}">'
                         '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/'
                         'relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>')

        sheets = "".join(
            f'<sheet name="{_escapar(_titulo_valido(h["titulo"]))}" sheetId="{i}" r:id="rId{i}"/>'
            for i, h in enumerate(hojas, start=1))
        archivo.writestr("xl/workbook.xml",
                         '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
                         f'<workbook xmlns="{NS}" xmlns:r="{NS_REL}"><sheets>{sheets}</sheets></workbook>')

        relaciones = "".join(
            f'<Relationship Id="rId{i}" Type="http://schemas.openxmlformats.org/officeDocument/2006/'
            f'relationships/worksheet" Target="worksheets/sheet{i}.xml"/>' for i in range(1, len(hojas) + 1))
        relaciones += (f'<Relationship Id="rId{len(hojas) + 1}" Type="http://schemas.openxmlformats.org/'
                       'officeDocument/2006/relationships/styles" Target="styles.xml"/>')
        archivo.writestr("xl/_rels/workbook.xml.rels",
                         '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
                         f'<Relationships xmlns="{NS_PAQ}">{relaciones}</Relationships>')

        archivo.writestr("xl/styles.xml", ESTILOS)


def _titulo_valido(titulo: str) -> str:
    """Excel prohíbe : \\ / ? * [ ] y más de 31 caracteres en el nombre de hoja."""
    return re.sub(r"[:\\/?*\[\]]", "-", str(titulo))[:31] or "Hoja"


def _hoja_xml(hoja: dict) -> str:
    filas = hoja.get("filas", [])
    columnas_texto = set(hoja.get("columnas_texto", ()))
    anchos = hoja.get("anchos") or []
    con_encabezado = bool(hoja.get("encabezado")) and bool(filas)

    partes = ['<?xml version="1.0" encoding="UTF-8" standalone="yes"?>', f'<worksheet xmlns="{NS}">']
    partes.append('<sheetViews><sheetView workbookViewId="0">')
    if con_encabezado:
        partes.append('<pane ySplit="1" topLeftCell="A2" activePane="bottomLeft" state="frozen"/>')
    partes.append("</sheetView></sheetViews>")

    if anchos:
        partes.append("<cols>")
        for i, ancho in enumerate(anchos, start=1):
            partes.append(f'<col min="{i}" max="{i}" width="{ancho:.1f}" customWidth="1"/>')
        partes.append("</cols>")

    partes.append("<sheetData>")
    ancho_maximo = 0
    for numero, fila in enumerate(filas, start=1):
        partes.append(f'<row r="{numero}">')
        ancho_maximo = max(ancho_maximo, len(fila))
        for columna, celda in enumerate(fila):
            estilo = None
            if isinstance(celda, tuple):
                celda, estilo = celda
            if celda is None or celda == "":
                continue
            referencia = f"{_indice_a_columna(columna)}{numero}"
            if estilo is None:
                if numero == 1 and con_encabezado:
                    estilo = ESTILO_ENCABEZADO
                elif columna in columnas_texto:
                    estilo = ESTILO_TEXTO
                else:
                    estilo = ESTILO_NORMAL
            atributo_estilo = f' s="{estilo}"' if estilo else ""
            es_numero = isinstance(celda, (int, float)) and not isinstance(celda, bool)
            if es_numero and columna not in columnas_texto:
                partes.append(f'<c r="{referencia}"{atributo_estilo}><v>{celda}</v></c>')
            else:
                partes.append(f'<c r="{referencia}"{atributo_estilo} t="inlineStr">'
                              f"<is><t>{_escapar(celda)}</t></is></c>")
        partes.append("</row>")
    partes.append("</sheetData>")

    if con_encabezado and len(filas) > 1 and ancho_maximo:
        partes.append(f'<autoFilter ref="A1:{_indice_a_columna(ancho_maximo - 1)}{len(filas)}"/>')
    partes.append("</worksheet>")
    return "".join(partes)
