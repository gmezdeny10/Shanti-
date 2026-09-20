<?php
require_once __DIR__ . '/_layout.php';
require_once __DIR__ . '/../includes/uploads.php';

$notice = '';

/* ---------- acciones ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    sla_check_csrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'guardar') {
        $id = !empty($_POST['id']) ? (int) $_POST['id'] : null;

        // Si se adjuntó una imagen desde el equipo, tiene prioridad sobre el desplegable.
        [$subida, $errorSubida] = sla_handle_upload('imagen_archivo');
        $imagen = $subida ?? trim($_POST['image'] ?? '');

        // Galería / carrusel: se parte de las imágenes que ya tenía el evento,
        // se quitan las marcadas para eliminar y se agregan las nuevas subidas.
        $galeriaActual = $id ? sla_event_gallery(sla_find_event($id) ?? []) : [];
        $quitar        = array_map('strval', $_POST['quitar_galeria'] ?? []);
        $galeriaActual = array_values(array_diff($galeriaActual, $quitar));
        [$nuevasFotos, $erroresGaleria] = sla_handle_uploads('galeria_archivos');
        $galeria = array_values(array_unique(array_merge($galeriaActual, $nuevasFotos)));

        $data = [
            'title'        => trim($_POST['title'] ?? ''),
            'event_date'   => trim($_POST['event_date'] ?? ''),
            'kind'         => trim($_POST['kind'] ?? 'Encuentro'),
            'location'     => trim($_POST['location'] ?? ''),
            'modality'     => trim($_POST['modality'] ?? 'Presencial'),
            'organizer'    => trim($_POST['organizer'] ?? ''),
            'capacity'     => (int) ($_POST['capacity'] ?? 0),
            'price_note'   => trim($_POST['price_note'] ?? ''),
            'description'  => trim($_POST['description'] ?? ''),
            'image'        => $imagen,
            'images'       => json_encode($galeria, JSON_UNESCAPED_SLASHES),
            'is_published' => isset($_POST['is_published']) ? 1 : 0,
        ];

        if ($errorSubida) {
            $notice = $errorSubida;
        } elseif ($erroresGaleria) {
            $notice = implode(' ', $erroresGaleria);
        } elseif ($data['title'] === '' || $data['event_date'] === '') {
            $notice = 'El título y la fecha son obligatorios.';
        } else {
            sla_save_event($data, $id);
            header('Location: eventos.php?ok=' . ($id ? 'actualizado' : 'creado'));
            exit;
        }
    }

    if ($action === 'borrar' && !empty($_POST['id'])) {
        sla_delete_event((int) $_POST['id']);
        header('Location: eventos.php?ok=borrado');
        exit;
    }
}

$editing = null;
if (!empty($_GET['editar'])) {
    $editing = sla_find_event((int) $_GET['editar']);
}
$showForm = $editing || isset($_GET['nuevo']);

$events = sla_all_events();
$counts = sla_registration_counts();

$okMessages = ['creado' => 'Evento creado.', 'actualizado' => 'Cambios guardados.', 'borrado' => 'Evento eliminado.'];
$ok = $okMessages[$_GET['ok'] ?? ''] ?? '';

/* imágenes disponibles: las del sitio y las que se han subido */
$imageOptions = sla_available_images();

sla_admin_header('Eventos', 'eventos.php');
?>
<div class="admin-container">
  <div class="page-head">
    <h1>Eventos</h1>
    <?php if (!$showForm): ?>
      <a class="btn btn-primary" href="eventos.php?nuevo=1">+ Nuevo evento</a>
    <?php endif; ?>
  </div>

  <?php if ($ok): ?><p class="alert alert-ok"><?= e($ok) ?></p><?php endif; ?>
  <?php if ($notice): ?><p class="alert alert-error"><?= e($notice) ?></p><?php endif; ?>

  <?php if ($showForm): ?>
    <section class="panel">
      <h2><?= $editing ? 'Editar evento' : 'Nuevo evento' ?></h2>
      <form method="post" class="form-grid" enctype="multipart/form-data">
        <?= sla_csrf_field() ?>
        <input type="hidden" name="action" value="guardar">
        <?php if ($editing): ?><input type="hidden" name="id" value="<?= (int) $editing['id'] ?>"><?php endif; ?>

        <label class="col-2">Título del evento *
          <input type="text" name="title" required value="<?= e($editing['title'] ?? '') ?>">
        </label>

        <label>Fecha *
          <input type="date" name="event_date" required value="<?= e($editing['event_date'] ?? '') ?>">
        </label>

        <label>Tipo
          <input type="text" name="kind" list="kinds" value="<?= e($editing['kind'] ?? 'Encuentro') ?>">
          <datalist id="kinds">
            <option value="Retiro"><option value="Taller"><option value="Encuentro cultural">
            <option value="Ceremonia"><option value="Clase"><option value="Voluntariado">
          </datalist>
        </label>

        <label>Lugar
          <input type="text" name="location" value="<?= e($editing['location'] ?? '') ?>" placeholder="Lagunas de Chacahua, Oaxaca">
        </label>

        <label>Modalidad
          <select name="modality">
            <?php foreach (['Presencial', 'Virtual', 'Presencial y virtual'] as $m): ?>
              <option value="<?= e($m) ?>" <?= ($editing['modality'] ?? '') === $m ? 'selected' : '' ?>><?= e($m) ?></option>
            <?php endforeach; ?>
          </select>
        </label>

        <label>Organiza
          <input type="text" name="organizer" value="<?= e($editing['organizer'] ?? 'Shanti Lanka Ashram') ?>">
        </label>

        <label>Cupos (0 = sin límite)
          <input type="number" name="capacity" min="0" value="<?= (int) ($editing['capacity'] ?? 0) ?>">
        </label>

        <label>Aporte / costo
          <input type="text" name="price_note" value="<?= e($editing['price_note'] ?? '') ?>" placeholder="Aporte sugerido 120 USD">
        </label>

        <label class="col-2">Imagen del evento
          <span class="field-hint">Sube una foto desde tu computadora o elige una de las que ya están en el sitio.</span>

          <input type="file" name="imagen_archivo" accept="image/jpeg,image/png,image/webp" class="file-input">

          <select name="image" class="mt-8">
            <option value="">— sin imagen —</option>
            <?php foreach ($imageOptions as $val => $label): ?>
              <option value="<?= e($val) ?>" <?= ($editing['image'] ?? '') === $val ? 'selected' : '' ?>><?= e($label) ?></option>
            <?php endforeach; ?>
          </select>

          <?php if (!empty($editing['image'])): ?>
            <span class="current-image">
              Imagen actual:
              <img src="../<?= e($editing['image']) ?>" alt="Imagen actual del evento">
            </span>
          <?php endif; ?>
        </label>

        <label class="col-2">Galería / carrusel del evento
          <span class="field-hint">Sube varias fotos para que se muestren como carrusel en la ficha del evento. La imagen de portada de arriba es la que aparece en la lista de eventos y en el calendario.</span>

          <input type="file" name="galeria_archivos[]" accept="image/jpeg,image/png,image/webp" class="file-input" multiple>

          <?php $galeriaActual = $editing ? sla_event_gallery($editing) : []; ?>
          <?php if ($galeriaActual): ?>
            <div class="gallery-manager mt-8">
              <?php foreach ($galeriaActual as $foto): ?>
                <label class="gallery-thumb">
                  <img src="../<?= e($foto) ?>" alt="Foto de la galería">
                  <span><input type="checkbox" name="quitar_galeria[]" value="<?= e($foto) ?>"> Quitar</span>
                </label>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </label>

        <label class="col-2">Descripción
          <textarea name="description" rows="4"><?= e($editing['description'] ?? '') ?></textarea>
        </label>

        <label class="checkbox col-2">
          <input type="checkbox" name="is_published" value="1" <?= (!$editing || $editing['is_published']) ? 'checked' : '' ?>>
          Publicado (visible en el sitio)
        </label>

        <div class="form-actions col-2">
          <button type="submit" class="btn btn-primary">Guardar</button>
          <a class="btn btn-ghost" href="eventos.php">Cancelar</a>
        </div>
      </form>
    </section>
  <?php endif; ?>

  <section class="panel">
    <h2>Todos los eventos</h2>
    <?php if (!$events): ?>
      <p class="empty">No hay eventos todavía.</p>
    <?php else: ?>
      <table class="admin-table">
        <thead><tr><th>Fecha</th><th>Evento</th><th>Lugar</th><th>Inscritos</th><th>Estado</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($events as $ev): ?>
          <tr>
            <td class="nowrap"><?= e(sla_date_long($ev['event_date'])) ?></td>
            <td>
              <strong><?= e($ev['title']) ?></strong>
              <span class="muted block"><?= e($ev['kind']) ?></span>
            </td>
            <td><?= e($ev['location']) ?></td>
            <td><?= (int) ($counts[$ev['id']] ?? 0) ?><?= $ev['capacity'] ? ' / ' . (int) $ev['capacity'] : '' ?></td>
            <td>
              <span class="tag-state <?= $ev['is_published'] ? 'on' : 'off' ?>">
                <?= $ev['is_published'] ? 'Publicado' : 'Oculto' ?>
              </span>
            </td>
            <td class="nowrap">
              <a class="link" href="eventos.php?editar=<?= (int) $ev['id'] ?>">Editar</a>
              <form method="post" class="inline" onsubmit="return confirm('¿Eliminar este evento y sus inscripciones?');">
                <?= sla_csrf_field() ?>
                <input type="hidden" name="action" value="borrar">
                <input type="hidden" name="id" value="<?= (int) $ev['id'] ?>">
                <button type="submit" class="link danger">Borrar</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </section>
</div>
<?php sla_admin_footer(); ?>
