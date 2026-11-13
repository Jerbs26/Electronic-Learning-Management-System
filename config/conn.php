<?php
// ── Hand-edited URLs: bad / unused parameters are removed (includes/url_guard.php) ──
require_once __DIR__ . '/../includes/url_guard.php';

// ── API requests (files in /api/) always answer with JSON ────────────────
// XAMPP shows PHP warnings on the page by default; inside an API response that
// breaks the JSON and the page only sees "Network error". Log them instead, and
// turn an unexpected crash into a JSON error. On localhost the real cause is
// included so it is easy to fix; on a live server it stays in the error log.
if (strpos(str_replace('\\', '/', $_SERVER['SCRIPT_FILENAME'] ?? ''), '/api/') !== false) {
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
    set_exception_handler(function (Throwable $e) {
        error_log('[eLMS API] ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
        if (!headers_sent()) { http_response_code(500); header('Content-Type: application/json'); }
        $local = in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1'], true);
        echo json_encode(['success' => false, 'message' => 'Server error' . ($local ? ': ' . $e->getMessage() : '. Please try again.')]);
    });
}

// ── Pages are never kept in the browser cache ────────────────────────────
// Without this, pressing Back after Sign out (or after the OTP step) shows the
// old page from the browser's memory without asking the server, so it looks
// like you are still signed in / can still continue. With no-store the browser
// asks the server again, and the server's login checks decide.
if (PHP_SAPI !== 'cli' && !headers_sent()
    && strpos(str_replace('\\', '/', $_SERVER['SCRIPT_FILENAME'] ?? ''), '/api/') === false) {
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');
    header('Expires: 0');
}

// Without this, PHP's date()/time()/strtotime() default to UTC (or whatever
// the server's php.ini happens to have), which silently disagrees with local
// wall-clock time — the cause of deadline checks like the assignment
// due-date comparison being off by several hours.
date_default_timezone_set('Asia/Manila');

$conn = new mysqli('localhost', 'u961483968_elmsdb', 'u961483968_Kelmsdb', 'u961483968_elmsdb');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->set_charset('utf8mb4');
// Keep MySQL's clock (NOW(), CURRENT_TIMESTAMP) on the same Manila time as PHP
$conn->query("SET time_zone = '+08:00'");

// ── Private settings: mail account + AI key ──────────────────────────────
// Kept out of the code in config/secrets.php (not shared; see
// config/secrets.example.php). Environment variables win when set.
$__secrets = file_exists(__DIR__ . '/secrets.php') ? (array) require __DIR__ . '/secrets.php' : [];
$__secret = function (string $key) use ($__secrets): string {
    $env = getenv(strtoupper($key));
    return (string) ($env !== false && $env !== '' ? $env : ($__secrets[$key] ?? ''));
};
// Older setups kept the Groq key in config/groq_key.php — still read if present.
$groqKey = $__secret('groq_api_key');
if ($groqKey === '' && file_exists(__DIR__ . '/groq_key.php')) {
    $groqKey = (string) require __DIR__ . '/groq_key.php';
}
if (!defined('GROQ_API_KEY')) define('GROQ_API_KEY', $groqKey);
if (!defined('MAIL_USERNAME')) {
    define('MAIL_USERNAME', $__secret('mail_username'));
    define('MAIL_PASSWORD', $__secret('mail_password'));
    define('MAIL_FROM', $__secret('mail_from') ?: $__secret('mail_username'));
    define('MAIL_FROM_NAME', $__secret('mail_from_name') ?: 'Arandia College eLMS');
    // Optional: another mail server (defaults: smtp.gmail.com, port 465, ssl)
    define('MAIL_HOST', $__secret('mail_host'));
    define('MAIL_PORT', (int) $__secret('mail_port'));
    define('MAIL_SECURE', $__secret('mail_secure') ?: 'ssl');
    // Optional: public address of this site for e-mailed links (e.g. https://elms.school.edu/capstone1)
    define('APP_URL', $__secret('app_url'));
}
unset($__secrets, $__secret);

// ── Signed-in account still valid? ────────────────────────────────────────
// If an admin deactivates or deletes an account, or changes its role, while
// that person is signed in, end their session on their very next request
// (pages AND APIs), so a stale session can't keep reading or changing data.
if (session_status() === PHP_SESSION_ACTIVE && !empty($_SESSION['user_id'])) {
    try {
        $__st = $conn->prepare('SELECT role, status FROM users WHERE id = ? LIMIT 1');
        $__uid = (int) $_SESSION['user_id'];
        $__st->bind_param('i', $__uid);
        $__st->execute();
        $__u = $__st->get_result()->fetch_assoc();
        $__st->close();
        if (!$__u || $__u['status'] !== 'Active' || $__u['role'] !== ($_SESSION['role'] ?? '')) {
            $_SESSION = [];
            session_destroy();
            if (strpos(str_replace('\\', '/', $_SERVER['SCRIPT_FILENAME'] ?? ''), '/api/') !== false) {
                http_response_code(401);
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Your account is no longer active. Please sign in again.', 'auth' => false]);
                exit;
            }
            // pages: the page's own login check now sends them to the login screen
        }
    } catch (Throwable $e) {
        // users table not reachable — leave the session alone
    }
}

// ── One device per account ───────────────────────────────────────────────
// Signing in on another device signs this one out (includes/single_session.php).
if (session_status() === PHP_SESSION_ACTIVE && !empty($_SESSION['user_id'])) {
    require_once __DIR__ . '/../includes/single_session.php';
    ssEnforce($conn);
}

// ── New accounts: must set their own password first ──────────────────────
// Until they do, every page except change_password.php is blocked
// (includes/force_password.php).
if (session_status() === PHP_SESSION_ACTIVE && !empty($_SESSION['must_change_password'])) {
    require_once __DIR__ . '/../includes/force_password.php';
    fpEnforce();
}

// ── Presence: remember when a logged-in user was last active ─────────────
// Feeds "Online users" on the admin dashboard. Written at most once a minute
// per session, and silently skipped until database/migration_presence.sql
// has been imported.
if (session_status() === PHP_SESSION_ACTIVE && !empty($_SESSION['user_id'])) {
    $__now = time();
    if (($_SESSION['_seen_at'] ?? 0) < $__now - 60) {
        $_SESSION['_seen_at'] = $__now;
        try {
            $__st = $conn->prepare('UPDATE users SET last_seen_at = ? WHERE id = ?');
            $__ts = date('Y-m-d H:i:s', $__now);
            $__uid = (int) $_SESSION['user_id'];
            $__st->bind_param('si', $__ts, $__uid);
            $__st->execute();
            $__st->close();
        } catch (Throwable $e) {
            // last_seen_at column not there yet — ignore
        }
    }
}
?>
