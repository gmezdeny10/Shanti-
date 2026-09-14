# Cómo publicar el sitio en internet

Guía para poner **Shanti Lanka Ashram** en línea con su propia dirección,
funcionando las 24 horas aunque tu computadora esté apagada.

---

## 1. Qué necesitas contratar

Un **hosting con PHP 8** y un **dominio**. Cualquiera de estos sirve y son de
los más usados en México:

| Servicio | Precio aproximado | Nota |
|---|---|---|
| Hostinger | 3–5 USD al mes | El más barato, suele incluir el dominio el primer año |
| Namecheap | 3–6 USD al mes | Bueno si compras ahí el dominio |
| SiteGround | 8–15 USD al mes | Más caro, mejor soporte |
| Banahosting / Hostgator MX | 4–8 USD al mes | Soporte en español |

**Al contratar, verifica que incluya:**
- PHP versión 8.0 o superior
- Extensión **SQLite** (casi todos la traen; si no, pídesela al soporte)
- Certificado SSL / HTTPS gratis (Let's Encrypt)

> No necesitas contratar base de datos MySQL: el sitio usa SQLite, que es un
> archivo y ya viene incluido.

**Dominio sugerido:** `shantilankaashram.org` o `shantilankaashram.com`
(unos 10–15 USD al año).

---

## 2. Qué subir

Sube **toda la carpeta `shanti-lanka-ashram`** al servidor, dentro de la
carpeta pública (normalmente se llama `public_html`, `www` o `htdocs`).

Puedes subirla de dos formas:
- **Administrador de archivos** del hosting: comprime la carpeta en .zip,
  la subes y la descomprimes ahí mismo. Es lo más sencillo.
- **FileZilla** (programa gratuito de FTP), con los datos que te dé el hosting.

### Archivos que NO debes subir
Son solo para que funcione en tu computadora:

- `INICIAR SITIO.bat`
- `php-local.ini`
- `router.php`
- `serve.py`
- Este archivo (`COMO PUBLICAR EL SITIO.md`)

### Archivos que SÍ o SÍ deben subirse
Son los que protegen tu información. Ojo: **empiezan con punto y algunos
programas los ocultan**, activa "ver archivos ocultos" al subir:

- `.htaccess` (en la carpeta principal)
- `data/.htaccess`
- `includes/.htaccess`
- `partials/.htaccess`

---

## 3. Sobre tu información actual

La carpeta `data/` contiene la base de datos con tus eventos, mensajes,
inscripciones y tu cuenta de acceso.

- **Si quieres empezar limpio en internet:** no subas el archivo
  `data/shanti.sqlite`. El sitio creará uno nuevo y vacío solo, y la primera
  vez te pedirá crear tu usuario en `tudominio.com/admin/instalar.php`.
- **Si quieres conservar lo que ya tienes:** súbelo tal cual y entra con tu
  usuario `China` y tu contraseña de siempre.

---

## 4. Permisos de la carpeta `data`

Para que el sitio pueda guardar mensajes e inscripciones, esa carpeta necesita
permiso de escritura. En el administrador de archivos del hosting:

1. Clic derecho sobre la carpeta `data`
2. "Permisos" o "CHMOD"
3. Ponle **755** (si no funciona, prueba **775**)

Lo mismo para la carpeta `images/subidas`.

---

## 5. Después de subirlo, revisa

- [ ] Abre `tudominio.com` — se ve la portada
- [ ] Abre `tudominio.com/data/shanti.sqlite` — **debe decir Prohibido o error 403**
      (si te descarga un archivo, avísame: falta activar el `.htaccess`)
- [ ] Entra al panel en `tudominio.com/admin/login.php`
- [ ] Manda un mensaje de prueba desde el formulario y revisa que te llegue
- [ ] Verifica que la dirección empiece con **https://** (con candado)

---

## 6. Recomendaciones de seguridad

- Usa una **contraseña larga** para el panel (mínimo 12 caracteres). Una vez
  publicado, cualquiera puede llegar a la pantalla de acceso.
- Ya está puesto un **bloqueo de 15 minutos tras 5 intentos fallidos**, para
  que nadie pueda adivinar la contraseña a la fuerza.
- **Respalda** de vez en cuando el archivo `data/shanti.sqlite`: ahí está todo
  (eventos, mensajes, inscripciones). Basta con descargarlo y guardarlo.
- Da acceso a otras personas desde **Panel → Usuarios**, nunca compartiendo tu
  contraseña.

---

## 7. Si algo sale mal

| Problema | Causa más común |
|---|---|
| Página en blanco | Falta la extensión SQLite. Pídesela al soporte del hosting. |
| "No se pudo guardar" | Faltan permisos de escritura en `data/` (paso 4). |
| Se ve sin diseño | No se subió `style.css` o la carpeta `images`. |
| Descarga la base de datos | No se subió `data/.htaccess` (archivo oculto). |

Cuando lo tengas contratado, dime qué hosting elegiste y te ayudo con el resto.
