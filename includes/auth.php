<?php
// Este archivo es interno: no debe abrirse directamente desde el navegador.
if (!defined('SLA_INTERNO') && PHP_SAPI !== 'cli') {
    if (basename($_SERVER['SCRIPT_FILENAME'] ?? '') === basename(__FILE__)) {
        http_response_code(403);
        exit('Acceso denegado.');
    }
}
/**
 * Sesiones, autenticación del administrador y protección CSRF.
 */

require_once __DIR__ . '/db.php';

function sla_session_start(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    session_set_cookie_params([
        'httponly' => true,
        'samesite' => 'Lax',
        'path'     => '/',
    ]);
    session_start();
}

function sla_admin_exists(): bool
{
    return (int) sla_db()->query('SELECT COUNT(*) FROM admins')->fetchColumn() > 0;
}

function sla_create_admin(string $username, string $password): void
{
    $stmt = sla_db()->prepare('INSERT INTO admins (username, password_hash) VALUES (?, ?)');
    $stmt->execute([$username, password_hash($password, PASSWORD_DEFAULT)]);
}

function sla_admins(): array
{
    return sla_db()->query('SELECT id, username, created_at FROM admins ORDER BY id ASC')->fetchAll();
}

function sla_admin_username_taken(string $username): bool
{
    $stmt = sla_db()->prepare('SELECT COUNT(*) FROM admins WHERE lower(username) = lower(?)');
    $stmt->execute([$username]);
    return (int) $stmt->fetchColumn() > 0;
}

function sla_delete_admin(int $id): void
{
    $stmt = sla_db()->prepare('DELETE FROM admins WHERE id = ?');
    $stmt->execute([$id]);
}

function sla_change_password(int $id, string $newPassword): void
{
    $stmt = sla_db()->prepare('UPDATE admins SET password_hash = ? WHERE id = ?');
    $stmt->execute([password_hash($newPassword, PASSWORD_DEFAULT), $id]);
}

function sla_verify_password(int $id, string $password): bool
{
    $stmt = sla_db()->prepare('SELECT password_hash FROM admins WHERE id = ?');
    $stmt->execute([$id]);
    $hash = $stmt->fetchColumn();
    return $hash && password_verify($password, $hash);
}

function sla_current_admin_id(): ?int
{
    sla_session_start();
    return isset($_SESSION['admin_id']) ? (int) $_SESSION['admin_id'] : null;
}

/* ---------- Protección contra intentos de adivinar la contraseña ---------- */

const SLA_MAX_INTENTOS = 5;    // intentos fallidos permitidos
const SLA_BLOQUEO_MIN  = 15;   // minutos de bloqueo

function sla_client_ip(): string
{
    return (string) ($_SERVER['REMOTE_ADDR'] ?? 'desconocida');
}

/** Intentos fallidos desde esta IP dentro de la ventana de bloqueo. */
function sla_failed_attempts(): int
{
    $stmt = sla_db()->prepare(
        "SELECT COUNT(*) FROM login_attempts
         WHERE ip = ? AND attempted_at > datetime('now', ?)"
    );
    $stmt->execute([sla_client_ip(), '-' . SLA_BLOQUEO_MIN . ' minutes']);
    return (int) $stmt->fetchColumn();
}

function sla_is_locked_out(): bool
{
    return sla_failed_attempts() >= SLA_MAX_INTENTOS;
}

/** Minutos que faltan para poder intentar de nuevo. */
function sla_lockout_minutes_left(): int
{
    $stmt = sla_db()->prepare(
        "SELECT attempted_at FROM login_attempts
         WHERE ip = ? AND attempted_at > datetime('now', ?)
         ORDER BY attempted_at ASC LIMIT 1"
    );
    $stmt->execute([sla_client_ip(), '-' . SLA_BLOQUEO_MIN . ' minutes']);
    $primero = $stmt->fetchColumn();
    if (!$primero) {
        return 0;
    }
    $libre = strtotime($primero . ' UTC') + SLA_BLOQUEO_MIN * 60;
    return max(1, (int) ceil(($libre - time()) / 60));
}

function sla_record_failed_attempt(string $username): void
{
    $stmt = sla_db()->prepare('INSERT INTO login_attempts (username, ip) VALUES (?, ?)');
    $stmt->execute([mb_substr($username, 0, 60), sla_client_ip()]);

    // Limpieza de registros viejos para que la tabla no crezca sin límite.
    sla_db()->exec("DELETE FROM login_attempts WHERE attempted_at < datetime('now', '-1 day')");
}

function sla_clear_attempts(): void
{
    $stmt = sla_db()->prepare('DELETE FROM login_attempts WHERE ip = ?');
    $stmt->execute([sla_client_ip()]);
}

function sla_attempt_login(string $username, string $password): bool
{
    if (sla_is_locked_out()) {
        return false;
    }

    $stmt = sla_db()->prepare('SELECT id, username, password_hash FROM admins WHERE username = ?');
    $stmt->execute([$username]);
    $admin = $stmt->fetch();

    if (!$admin || !password_verify($password, $admin['password_hash'])) {
        sla_record_failed_attempt($username);
        return false;
    }

    sla_clear_attempts();
    sla_session_start();
    session_regenerate_id(true);
    $_SESSION['admin_id']       = (int) $admin['id'];
    $_SESSION['admin_username'] = $admin['username'];

    return true;
}

function sla_logout(): void
{
    sla_session_start();
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'] ?? '', $p['secure'] ?? false, $p['httponly'] ?? true);
    }
    session_destroy();
}

function sla_is_logged_in(): bool
{
    sla_session_start();
    if (empty($_SESSION['admin_id'])) {
        return false;
    }

    // La cuenta debe seguir existiendo: si se eliminó, la sesión deja de valer.
    static $verified = null;
    if ($verified === null) {
        $stmt = sla_db()->prepare('SELECT COUNT(*) FROM admins WHERE id = ?');
        $stmt->execute([(int) $_SESSION['admin_id']]);
        $verified = (int) $stmt->fetchColumn() > 0;
        if (!$verified) {
            $_SESSION = [];
        }
    }

    return $verified;
}

function sla_current_admin(): ?string
{
    sla_session_start();
    return $_SESSION['admin_username'] ?? null;
}

/** Corta la ejecución si no hay sesión de administrador. */
function sla_require_login(): void
{
    if (!sla_is_logged_in()) {
        header('Location: login.php');
        exit;
    }
}

/* ---------- CSRF ---------- */

function sla_csrf_token(): string
{
    sla_session_start();
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function sla_csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . htmlspecialchars(sla_csrf_token(), ENT_QUOTES) . '">';
}

function sla_check_csrf(): void
{
    sla_session_start();
    $sent = $_POST['csrf'] ?? '';
    if (!is_string($sent) || empty($_SESSION['csrf']) || !hash_equals($_SESSION['csrf'], $sent)) {
        http_response_code(400);
        exit('Solicitud inválida (token CSRF).');
    }
}

/* ---------- utilidades ---------- */

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

// La sesión debe iniciarse antes de imprimir nada; todas las páginas
// incluyen este archivo como primera instrucción.
sla_session_start();

/** "2026-09-12" => ["12", "SEP"] */
function sla_date_parts(string $isoDate): array
{
    $meses = ['01' => 'ENE', '02' => 'FEB', '03' => 'MAR', '04' => 'ABR', '05' => 'MAY', '06' => 'JUN',
              '07' => 'JUL', '08' => 'AGO', '09' => 'SEP', '10' => 'OCT', '11' => 'NOV', '12' => 'DIC'];
    $ts = strtotime($isoDate);
    if (!$ts) {
        return ['--', '---'];
    }
    return [date('d', $ts), $meses[date('m', $ts)] ?? '---'];
}

function sla_date_long(string $isoDate): string
{
    $meses = [1 => 'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio',
              'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
    $ts = strtotime($isoDate);
    if (!$ts) {
        return $isoDate;
    }
    return date('j', $ts) . ' de ' . ($meses[(int) date('n', $ts)] ?? '') . ' de ' . date('Y', $ts);
}
