<?php
// Este archivo es interno: no debe abrirse directamente desde el navegador.
if (!defined('SLA_INTERNO') && PHP_SAPI !== 'cli') {
    if (basename($_SERVER['SCRIPT_FILENAME'] ?? '') === basename(__FILE__)) {
        http_response_code(403);
        exit('Acceso denegado.');
    }
}
/**
 * Subida de imágenes desde el equipo, con validación estricta.
 */

const SLA_UPLOAD_DIR = __DIR__ . '/../images/subidas';
const SLA_UPLOAD_URL = 'images/subidas';
const SLA_MAX_UPLOAD = 8388608; // 8 MB

/**
 * Procesa $_FILES['campo'] y devuelve la ruta relativa de la imagen guardada.
 *
 * @return array{0: ?string, 1: ?string}  [rutaRelativa, mensajeDeError]
 */
function sla_handle_upload(string $field): array
{
    if (empty($_FILES[$field]) || ($_FILES[$field]['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return [null, null]; // no se subió nada: no es un error
    }

    $file = $_FILES[$field];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $motivos = [
            UPLOAD_ERR_INI_SIZE   => 'La imagen supera el tamaño permitido por el servidor.',
            UPLOAD_ERR_FORM_SIZE  => 'La imagen es demasiado grande.',
            UPLOAD_ERR_PARTIAL    => 'La imagen se subió incompleta, inténtalo de nuevo.',
            UPLOAD_ERR_NO_TMP_DIR => 'Falta la carpeta temporal del servidor.',
            UPLOAD_ERR_CANT_WRITE => 'No se pudo escribir la imagen en el disco.',
        ];
        return [null, $motivos[$file['error']] ?? 'No se pudo subir la imagen.'];
    }

    if ($file['size'] > SLA_MAX_UPLOAD) {
        return [null, 'La imagen pesa más de 8 MB. Usa una más ligera.'];
    }

    if (!is_uploaded_file($file['tmp_name'])) {
        return [null, 'Archivo inválido.'];
    }

    // El archivo debe ser una imagen real, no solo tener extensión de imagen.
    $info = @getimagesize($file['tmp_name']);
    if ($info === false) {
        return [null, 'El archivo no es una imagen válida.'];
    }

    $permitidos = [
        IMAGETYPE_JPEG => 'jpg',
        IMAGETYPE_PNG  => 'png',
        IMAGETYPE_WEBP => 'webp',
    ];
    if (!isset($permitidos[$info[2]])) {
        return [null, 'Formato no permitido. Usa JPG, PNG o WEBP.'];
    }

    $ext = $permitidos[$info[2]];

    if (!is_dir(SLA_UPLOAD_DIR) && !mkdir(SLA_UPLOAD_DIR, 0775, true) && !is_dir(SLA_UPLOAD_DIR)) {
        return [null, 'No se pudo crear la carpeta de imágenes.'];
    }

    // Nombre propio: nunca se usa el nombre original del archivo.
    $base   = preg_replace('/[^a-z0-9]+/i', '-', pathinfo($file['name'], PATHINFO_FILENAME));
    $base   = trim(strtolower(substr($base, 0, 40)), '-') ?: 'imagen';
    $nombre = $base . '-' . bin2hex(random_bytes(4)) . '.' . $ext;

    if (!move_uploaded_file($file['tmp_name'], SLA_UPLOAD_DIR . '/' . $nombre)) {
        return [null, 'No se pudo guardar la imagen.'];
    }

    return [SLA_UPLOAD_URL . '/' . $nombre, null];
}

/** Imágenes disponibles: las del sitio y las subidas desde el panel. */
function sla_available_images(): array
{
    $out = [];

    foreach (['images' => '', 'images/subidas' => 'subidas/'] as $dir => $prefix) {
        $ruta = __DIR__ . '/../' . $dir;
        if (!is_dir($ruta)) {
            continue;
        }
        foreach (scandir($ruta) ?: [] as $f) {
            if (preg_match('/\.(jpe?g|png|webp)$/i', $f)) {
                $out['images/' . $prefix . $f] = ($prefix ? 'Subida: ' : '') . $f;
            }
        }
    }

    return $out;
}
