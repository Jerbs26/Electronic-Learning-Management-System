<?php
// ============================================================
//  Arandia College eLMS — One device per account
//  File: includes/single_session.php
//
//  An account can be signed in on ONE device at a time. Every successful
//  sign-in (verify_otp.php) makes a new random key, saves it on the account
//  (users.session_token) and in that device's session. On every request
//  (config/conn.php) the two are compared: if the account has since signed
//  in somewhere else, the keys differ and THIS device is signed out —
//    · pages        → a "Signed in on another device" notice with an OK button
//                     (ssRenderNotice); OK opens login.php?kicked=1
//    · APIs / live  → 401 JSON {auth:false, kicked:true} + header X-Elms-Kicked;
//                     includes/live.php shows the same notice on the open page
//  The device is marked as kicked in its own session (elms_kicked) and the
//  session is only cleared when the person presses OK (login.php), so every
//  request in between — all roles — gets the same notice, never a silent logout.
//
//  The users.session_token column is added automatically the first time
//  (same as database/migration_single_session.sql).
// ============================================================

require_once __DIR__ . '/session_safe.php';

/** Adds users.session_token if it is missing. */
function ssEnsureColumn(mysqli $conn): void
{
    static $done = false;
    if ($done) return;
    $done = true;
    try {
        $r = $conn->query("SHOW COLUMNS FROM users LIKE 'session_token'");
        if ($r && $r->num_rows === 0) {
            $conn->query("ALTER TABLE users ADD COLUMN session_token CHAR(64) NULL DEFAULT NULL");
        }
    } catch (Throwable $e) {
        error_log('[eLMS single session] ' . $e->getMessage());
    }
}

/** The key saved on the account, or null (no key yet / column missing). */
function ssAccountToken(mysqli $conn, int $userId, bool $retry = true): ?string
{
    try {
        $st = $conn->prepare('SELECT session_token FROM users WHERE id = ? LIMIT 1');
        if (!$st) throw new RuntimeException($conn->error);
        $st->bind_param('i', $userId);
        $st->execute();
        $row = $st->get_result()->fetch_assoc();
        $st->close();
        $t = $row['session_token'] ?? null;
        return ($t === null || $t === '') ? null : (string) $t;
    } catch (Throwable $e) {
        // column not there yet: add it once and read again
        if ($retry) {
            ssEnsureColumn($conn);
            return ssAccountToken($conn, $userId, false);
        }
        return null;
    }
}

/**
 * Call right after a successful sign-in: this device becomes the only one.
 * Any other device signed in to the account is signed out on its next request.
 */
function ssClaimDevice(mysqli $conn, int $userId): void
{
    ssEnsureColumn($conn);
    $token = bin2hex(random_bytes(32));
    try {
        $st = $conn->prepare('UPDATE users SET session_token = ? WHERE id = ?');
        $st->bind_param('si', $token, $userId);
        $st->execute();
        $st->close();
        $_SESSION['session_token'] = $token;
        unset($_SESSION['elms_kicked']);
    } catch (Throwable $e) {
        error_log('[eLMS single session] ' . $e->getMessage());
    }
}

/** Call on Sign out: frees the account (only if this device is the current one). */
function ssReleaseDevice(mysqli $conn): void
{
    $uid = (int) ($_SESSION['user_id'] ?? 0);
    $mine = (string) ($_SESSION['session_token'] ?? '');
    if (!$uid || $mine === '') return;
    try {
        $st = $conn->prepare('UPDATE users SET session_token = NULL WHERE id = ? AND session_token = ?');
        $st->bind_param('is', $uid, $mine);
        $st->execute();
        $st->close();
    } catch (Throwable $e) {
    }
}

/** Is this request a background/JSON request (API, live refresh, fetch)? */
function ssIsBackgroundRequest(): bool
{
    $script = str_replace('\\', '/', $_SERVER['SCRIPT_FILENAME'] ?? '');
    if (strpos($script, '/api/') !== false) return true;
    if (!empty($_SERVER['HTTP_X_ELMS_LIVE'])) return true;
    if (strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest') return true;
    $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
    return stripos($accept, 'application/json') !== false && stripos($accept, 'text/html') === false;
}

/** URL of login.php relative to the running script (works in any sub-folder). */
function ssLoginUrl(): string
{
    $root = realpath(__DIR__ . '/..');
    $script = realpath($_SERVER['SCRIPT_FILENAME'] ?? '') ?: '';
    $prefix = '';
    if ($root && $script && strpos($script, $root) === 0) {
        $rel = ltrim(str_replace('\\', '/', substr($script, strlen($root))), '/');
        $prefix = str_repeat('../', substr_count($rel, '/'));
    } else {
        // path could not be matched (symlinked folder): go by the folder name instead
        $dir = basename(dirname(str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '')));
        if (in_array($dir, ['teacher', 'student', 'admin', 'admission', 'api'], true)) $prefix = '../';
    }
    return $prefix . 'login.php?kicked=1';
}

/**
 * The "Signed in on another device" notice as a small stand-alone page.
 * Same look and wording as the pop-up in includes/live.php. Pressing OK (or
 * Enter / Esc) opens the sign-in page.
 */
function ssRenderNotice(): void
{
    $url = htmlspecialchars(ssLoginUrl(), ENT_QUOTES, 'UTF-8');
    header('Content-Type: text/html; charset=utf-8');
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    echo '<!DOCTYPE html><html lang="en"><head><meta charset="utf-8">'
        . '<meta name="viewport" content="width=device-width, initial-scale=1">'
        . '<meta name="robots" content="noindex">'
        . '<title>Signed in on another device</title>'
        . '<style>'
        . '*{box-sizing:border-box}'
        . 'html,body{height:100%;margin:0}'
        . 'body{background:#f1f5f9;font-family:Inter,system-ui,-apple-system,"Segoe UI",Roboto,sans-serif}'
        . '.ov{position:fixed;inset:0;background:rgba(15,23,42,.6);display:flex;align-items:center;justify-content:center;padding:1rem}'
        . '.box{background:#fff;color:#1a1a2e;border-radius:16px;max-width:420px;width:100%;padding:1.6rem 1.5rem 1.4rem;text-align:center;box-shadow:0 20px 60px rgba(0,0,0,.35)}'
        . '.ic{width:46px;height:46px;border-radius:50%;background:#FFF4E0;color:#B7791F;display:flex;align-items:center;justify-content:center;margin:0 auto .8rem}'
        . 'h1{font-size:1.15rem;font-weight:800;margin:0 0 .5rem}'
        . 'p{margin:0 0 1.2rem;font-size:.92rem;line-height:1.55;color:#475569}'
        . 'a.ok{display:block;width:100%;padding:.8rem 1rem;border-radius:12px;background:#0B2545;color:#fff;font-weight:700;font-size:.95rem;text-decoration:none}'
        . 'a.ok:focus{outline:3px solid #93b4ff;outline-offset:2px}'
        . '</style></head><body>'
        . '<div class="ov"><div class="box" role="alertdialog" aria-modal="true" aria-labelledby="t">'
        . '<div class="ic"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 9v4"/><path d="M12 17h.01"/><path d="M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0z"/></svg></div>'
        . '<h1 id="t">Signed in on another device</h1>'
        . '<p>Your account was just signed in on another device, so you will be signed out here.</p>'
        . '<a class="ok" id="ok" href="' . $url . '">OK</a>'
        . '</div></div>'
        . '<script>(function(){var ok=document.getElementById("ok");if(!ok)return;ok.focus();'
        . 'document.addEventListener("keydown",function(e){'
        . 'if(e.key==="Escape"||e.key==="Enter"){e.preventDefault();location.href=ok.getAttribute("href");}'
        . 'else if(e.key==="Tab"){e.preventDefault();ok.focus();}},true);})();</script>'
        . '</body></html>';
}

/**
 * Called on every request (config/conn.php). If the account has signed in on
 * another device since, this device is told so — it is NOT silently logged out:
 *   · background / JSON requests → 401 JSON + X-Elms-Kicked (live.php shows the notice)
 *   · page requests              → the notice page (OK → login.php?kicked=1)
 *   · login.php                  → the leftover session is cleared, message shown
 * The device stays marked as kicked (elms_kicked) until it reaches login.php, so
 * it can never come back to life, not even if the other device signs out first.
 */
function ssEnforce(mysqli $conn): void
{
    if (session_status() !== PHP_SESSION_ACTIVE || empty($_SESSION['user_id'])) return;

    $self = basename($_SERVER['SCRIPT_FILENAME'] ?? '');
    if ($self === 'logout.php') return;                  // signing out anyway

    if (empty($_SESSION['elms_kicked'])) {
        $current = ssAccountToken($conn, (int) $_SESSION['user_id']);
        if ($current === null) return;                   // nobody claimed the account yet
        $mine = (string) ($_SESSION['session_token'] ?? '');
        if ($mine !== '' && hash_equals($current, $mine)) return;   // this IS the current device
        $_SESSION['elms_kicked'] = 1;                    // signed in somewhere else
    }

    // Already on the sign-in page: clear the leftover session, show the message there
    if ($self === 'login.php') {
        $_SESSION = [];
        if (!headers_sent()) @session_regenerate_id(true);
        $_GET['kicked'] = '1';
        return;
    }

    if (headers_sent()) return;
    header('X-Elms-Kicked: 1');
    if (ssIsBackgroundRequest()) {
        $msg = 'Your account was signed in on another device, so you were signed out here.';
        http_response_code(401);
        header('Content-Type: application/json');
        header('Cache-Control: no-store');
        echo json_encode(['success' => false, 'message' => $msg, 'auth' => false, 'kicked' => true]);
        exit;
    }
    ssRenderNotice();
    exit;
}
