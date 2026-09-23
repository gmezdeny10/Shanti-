# Shanti Lanka Ashram

Sitio web del **Shanti Lanka Ashram**, en Lagunas de Chacahua, Oaxaca, México.

*Muchos caminos. Una sola intención: amar.*

---

## Qué incluye

**Sitio público**
- Portada con las prácticas, instalaciones, ubicación y la historia del ashram
- Franja de novedades con los próximos encuentros
- Calendario de eventos navegable por mes
- Ficha de cada evento con formulario de inscripción y control de cupos
- Formulario de contacto
- Sección sobre Shri Shivabalayogi Maharaj

**Panel de administración** (`/admin`)
- Acceso con usuario y contraseña
- Alta, edición y publicación de eventos, con subida de imágenes
- Inscripciones con estados (pendiente / confirmada / cancelada)
- Bandeja de mensajes con respuesta guardada y envío por WhatsApp o correo
- Gestión de personas con acceso al panel

---

## Tecnología

Sin frameworks ni dependencias externas.

- **PHP 8** (sin Composer)
- **SQLite** como base de datos (un solo archivo, sin servidor aparte)
- HTML, CSS y JavaScript nativos

---

## Cómo ejecutarlo

Necesitas PHP 8 o superior con las extensiones `pdo_sqlite` y `mbstring`.

```bash
php -S 0.0.0.0:8422 -t . router.php
```

Luego abre <http://localhost:8422>.

La primera vez, entra a `/admin/instalar.php` para crear la cuenta de
administración. La base de datos se crea sola en `data/`.

### Con Docker

Si prefieres no instalar PHP:

```bash
docker compose up
```

Luego abre <http://localhost:8422>. Para pararlo, `Ctrl+C`.

Esta vía usa Apache, así que el `.htaccess` sí se aplica —igual que en el
hosting— y puedes comprobar las reglas de seguridad antes de publicar. El
código se monta desde tu carpeta: guardas un archivo y el cambio se ve al
recargar, sin reconstruir nada.

La base de datos y las imágenes subidas viven en volúmenes de Docker, así que
sobreviven a `docker compose down`. Para borrarlas y empezar de cero:

```bash
docker compose down -v
```

---

## Seguridad

- Contraseñas cifradas con `password_hash()`
- Protección CSRF en todos los formularios
- Consultas preparadas en todas las operaciones de base de datos
- Bloqueo temporal tras 5 intentos fallidos de acceso
- Validación real de imágenes subidas (se rechaza cualquier archivo que no sea
  una imagen, aunque tenga extensión de imagen)
- La base de datos y los archivos internos no son accesibles desde el navegador
  (`.htaccess` en Apache y guardias en PHP)

---

## Publicación

Ver **COMO PUBLICAR EL SITIO.md** para la guía de subida a un hosting.

> La carpeta `data/` no está en este repositorio: contiene datos personales
> (mensajes, inscripciones y cuentas). Se genera automáticamente al ejecutar
> el sitio por primera vez.
