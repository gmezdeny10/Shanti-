<?php
// Este archivo es interno: no debe abrirse directamente desde el navegador.
if (!defined('SLA_INTERNO') && PHP_SAPI !== 'cli') {
    if (basename($_SERVER['SCRIPT_FILENAME'] ?? '') === basename(__FILE__)) {
        http_response_code(403);
        exit('Acceso denegado.');
    }
}
/**
 * Conexión a la base de datos (SQLite) y creación automática del esquema.
 */

const SLA_DB_DIR  = __DIR__ . '/../data';
const SLA_DB_FILE = SLA_DB_DIR . '/shanti.sqlite';

function sla_db(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    if (!is_dir(SLA_DB_DIR)) {
        mkdir(SLA_DB_DIR, 0775, true);
    }

    $pdo = new PDO('sqlite:' . SLA_DB_FILE, null, null, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
    $pdo->exec('PRAGMA foreign_keys = ON');

    sla_migrate($pdo);

    return $pdo;
}

function sla_migrate(PDO $pdo): void
{
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS admins (
            id            INTEGER PRIMARY KEY AUTOINCREMENT,
            username      TEXT NOT NULL UNIQUE,
            password_hash TEXT NOT NULL,
            created_at    TEXT NOT NULL DEFAULT (datetime('now'))
        )
    ");

    // Intentos de acceso, para frenar ataques de fuerza bruta al panel.
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS login_attempts (
            id           INTEGER PRIMARY KEY AUTOINCREMENT,
            username     TEXT NOT NULL DEFAULT '',
            ip           TEXT NOT NULL DEFAULT '',
            attempted_at TEXT NOT NULL DEFAULT (datetime('now'))
        )
    ");
    $pdo->exec('CREATE INDEX IF NOT EXISTS idx_attempts ON login_attempts (ip, attempted_at)');

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS events (
            id           INTEGER PRIMARY KEY AUTOINCREMENT,
            title        TEXT NOT NULL,
            event_date   TEXT NOT NULL,
            kind         TEXT NOT NULL DEFAULT 'Encuentro',
            location     TEXT NOT NULL DEFAULT '',
            modality     TEXT NOT NULL DEFAULT 'Presencial',
            organizer    TEXT NOT NULL DEFAULT 'Shanti Lanka Ashram',
            capacity     INTEGER NOT NULL DEFAULT 0,
            price_note   TEXT NOT NULL DEFAULT '',
            description  TEXT NOT NULL DEFAULT '',
            image        TEXT NOT NULL DEFAULT '',
            is_published INTEGER NOT NULL DEFAULT 1,
            created_at   TEXT NOT NULL DEFAULT (datetime('now'))
        )
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS messages (
            id         INTEGER PRIMARY KEY AUTOINCREMENT,
            name       TEXT NOT NULL,
            email      TEXT NOT NULL DEFAULT '',
            phone      TEXT NOT NULL DEFAULT '',
            body       TEXT NOT NULL,
            is_read    INTEGER NOT NULL DEFAULT 0,
            reply      TEXT NOT NULL DEFAULT '',
            replied_at TEXT,
            created_at TEXT NOT NULL DEFAULT (datetime('now'))
        )
    ");

    // Bases creadas antes de que existiera la respuesta en el panel.
    $cols = array_column($pdo->query('PRAGMA table_info(messages)')->fetchAll(), 'name');
    if (!in_array('reply', $cols, true)) {
        $pdo->exec("ALTER TABLE messages ADD COLUMN reply TEXT NOT NULL DEFAULT ''");
        $pdo->exec("ALTER TABLE messages ADD COLUMN replied_at TEXT");
    }

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS registrations (
            id         INTEGER PRIMARY KEY AUTOINCREMENT,
            event_id   INTEGER NOT NULL REFERENCES events(id) ON DELETE CASCADE,
            name       TEXT NOT NULL,
            email      TEXT NOT NULL DEFAULT '',
            phone      TEXT NOT NULL DEFAULT '',
            people     INTEGER NOT NULL DEFAULT 1,
            notes      TEXT NOT NULL DEFAULT '',
            status     TEXT NOT NULL DEFAULT 'pendiente',
            created_at TEXT NOT NULL DEFAULT (datetime('now'))
        )
    ");

    // Contenido inicial de ejemplo, solo la primera vez.
    $count = (int) $pdo->query('SELECT COUNT(*) FROM events')->fetchColumn();
    if ($count === 0) {
        $seed = $pdo->prepare("
            INSERT INTO events (title, event_date, kind, location, modality, organizer, capacity, price_note, description, image)
            VALUES (?,?,?,?,?,?,?,?,?,?)
        ");
        $seed->execute(['Retiro de silencio junto al océano', '2026-09-12', 'Retiro', 'Matara, Sri Lanka', 'Presencial', 'Ananda Vihara', 18, 'Aporte sugerido 120 USD', 'Días de práctica en silencio frente al mar, con meditación guiada al amanecer y al atardecer.', 'images/practica-9794.jpg']);
        $seed->execute(['Círculo de tejido y palabra', '2026-09-27', 'Encuentro cultural', 'Valle Sagrado, Perú', 'Presencial', 'Tejido de Raíces', 25, 'Aporte libre', 'Un círculo para tejer, escuchar historias y compartir el conocimiento textil de las familias del valle.', 'images/meditacion-facilitador.jpg']);
        $seed->execute(['Baño de sonido y respiración', '2026-10-05', 'Taller', 'Oaxaca, México', 'Presencial y virtual', 'Círculo de Agua', 30, 'Tarifa escalonada', 'Una sesión de sonoterapia con cuencos y handpan, acompañada de ejercicios de respiración consciente.', 'images/instalaciones-yoga-2.jpg']);
    }
}
