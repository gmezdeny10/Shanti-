<?php
// Este archivo es interno: no debe abrirse directamente desde el navegador.
if (!defined('SLA_INTERNO') && PHP_SAPI !== 'cli') {
    if (basename($_SERVER['SCRIPT_FILENAME'] ?? '') === basename(__FILE__)) {
        http_response_code(403);
        exit('Acceso denegado.');
    }
}
/**
 * Consultas de eventos, inscripciones y mensajes.
 */

require_once __DIR__ . '/db.php';

/** Próximos eventos publicados (para la portada y la franja de novedades). */
function sla_upcoming_events(int $limit = 3): array
{
    $stmt = sla_db()->prepare("
        SELECT * FROM events
        WHERE is_published = 1 AND date(event_date) >= date('now')
        ORDER BY date(event_date) ASC
        LIMIT :limit
    ");
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

/** Todos los eventos publicados de un mes (YYYY-MM) para el calendario. */
function sla_events_in_month(string $ym): array
{
    $stmt = sla_db()->prepare("
        SELECT * FROM events
        WHERE is_published = 1 AND strftime('%Y-%m', event_date) = ?
        ORDER BY date(event_date) ASC
    ");
    $stmt->execute([$ym]);

    $byDay = [];
    foreach ($stmt->fetchAll() as $event) {
        $day = (int) date('j', strtotime($event['event_date']));
        $byDay[$day][] = $event;
    }
    return $byDay;
}

function sla_all_events(): array
{
    return sla_db()->query('SELECT * FROM events ORDER BY date(event_date) DESC')->fetchAll();
}

function sla_find_event(int $id): ?array
{
    $stmt = sla_db()->prepare('SELECT * FROM events WHERE id = ?');
    $stmt->execute([$id]);
    return $stmt->fetch() ?: null;
}

function sla_save_event(array $data, ?int $id = null): int
{
    $fields = ['title', 'event_date', 'kind', 'location', 'modality', 'organizer',
               'capacity', 'price_note', 'description', 'image', 'images', 'is_published'];

    $values = [];
    foreach ($fields as $f) {
        $values[] = $data[$f] ?? '';
    }

    if ($id) {
        $set  = implode(' = ?, ', $fields) . ' = ?';
        $stmt = sla_db()->prepare("UPDATE events SET $set WHERE id = ?");
        $values[] = $id;
        $stmt->execute($values);
        return $id;
    }

    $cols   = implode(', ', $fields);
    $marks  = implode(', ', array_fill(0, count($fields), '?'));
    $stmt   = sla_db()->prepare("INSERT INTO events ($cols) VALUES ($marks)");
    $stmt->execute($values);
    return (int) sla_db()->lastInsertId();
}

function sla_delete_event(int $id): void
{
    $stmt = sla_db()->prepare('DELETE FROM events WHERE id = ?');
    $stmt->execute([$id]);
}

/**
 * Galería de fotos de un evento, para mostrarla como carrusel en su ficha.
 * Si el evento tiene varias imágenes guardadas (columna `images`, en JSON) se
 * usan esas; si no, se cae de vuelta a la imagen de portada (`image`), para
 * que los eventos antiguos con una sola foto sigan funcionando igual.
 */
function sla_event_gallery(array $evento): array
{
    $lista = json_decode($evento['images'] ?? '', true);
    if (is_array($lista) && $lista) {
        return array_values(array_filter(array_map('strval', $lista)));
    }
    return $evento['image'] ? [$evento['image']] : [];
}

/* ---------- inscripciones ---------- */

function sla_add_registration(int $eventId, array $data): void
{
    $stmt = sla_db()->prepare("
        INSERT INTO registrations (event_id, name, email, phone, people, notes)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $eventId,
        $data['name'] ?? '',
        $data['email'] ?? '',
        $data['phone'] ?? '',
        max(1, (int) ($data['people'] ?? 1)),
        $data['notes'] ?? '',
    ]);
}

function sla_registrations(?int $eventId = null): array
{
    if ($eventId) {
        $stmt = sla_db()->prepare("
            SELECT r.*, e.title AS event_title, e.event_date
            FROM registrations r JOIN events e ON e.id = r.event_id
            WHERE r.event_id = ? ORDER BY r.created_at DESC
        ");
        $stmt->execute([$eventId]);
        return $stmt->fetchAll();
    }

    return sla_db()->query("
        SELECT r.*, e.title AS event_title, e.event_date
        FROM registrations r JOIN events e ON e.id = r.event_id
        ORDER BY r.created_at DESC
    ")->fetchAll();
}

function sla_registration_counts(): array
{
    $rows = sla_db()->query('SELECT event_id, SUM(people) AS total FROM registrations GROUP BY event_id')->fetchAll();
    $out  = [];
    foreach ($rows as $r) {
        $out[(int) $r['event_id']] = (int) $r['total'];
    }
    return $out;
}

function sla_set_registration_status(int $id, string $status): void
{
    $allowed = ['pendiente', 'confirmada', 'cancelada'];
    if (!in_array($status, $allowed, true)) {
        return;
    }
    $stmt = sla_db()->prepare('UPDATE registrations SET status = ? WHERE id = ?');
    $stmt->execute([$status, $id]);
}

function sla_delete_registration(int $id): void
{
    $stmt = sla_db()->prepare('DELETE FROM registrations WHERE id = ?');
    $stmt->execute([$id]);
}

/* ---------- mensajes ---------- */

function sla_add_message(array $data): void
{
    $stmt = sla_db()->prepare('INSERT INTO messages (name, email, phone, body) VALUES (?, ?, ?, ?)');
    $stmt->execute([
        $data['name'] ?? '',
        $data['email'] ?? '',
        $data['phone'] ?? '',
        $data['body'] ?? '',
    ]);
}

function sla_messages(): array
{
    return sla_db()->query('SELECT * FROM messages ORDER BY created_at DESC')->fetchAll();
}

function sla_mark_message_read(int $id, int $read = 1): void
{
    $stmt = sla_db()->prepare('UPDATE messages SET is_read = ? WHERE id = ?');
    $stmt->execute([$read ? 1 : 0, $id]);
}

function sla_save_reply(int $id, string $reply): void
{
    $stmt = sla_db()->prepare("UPDATE messages SET reply = ?, replied_at = datetime('now'), is_read = 1 WHERE id = ?");
    $stmt->execute([$reply, $id]);
}

/** Número listo para wa.me: solo dígitos, con código de país de México si falta. */
function sla_whatsapp_number(string $phone): ?string
{
    $d = preg_replace('/\D+/', '', $phone);
    if (strlen($d) < 10) {
        return null;
    }
    if (strlen($d) === 10) {
        $d = '52' . $d;          // número mexicano sin lada internacional
    } elseif (str_starts_with($d, '521') && strlen($d) === 13) {
        $d = '52' . substr($d, 3); // formato viejo 52-1-XXXXXXXXXX
    }
    return $d;
}

function sla_delete_message(int $id): void
{
    $stmt = sla_db()->prepare('DELETE FROM messages WHERE id = ?');
    $stmt->execute([$id]);
}

function sla_unread_count(): int
{
    return (int) sla_db()->query('SELECT COUNT(*) FROM messages WHERE is_read = 0')->fetchColumn();
}
