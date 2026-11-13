<?php
// ============================================================
//  Arandia College eLMS — Safe session start / end
//  File: includes/session_safe.php
//
//  Why: on Windows (XAMPP) a session file that another request still has
//  open (a page's background refresh, a second tab) cannot be opened or
//  removed — PHP then prints "session_start(): open(...sess_xxx) failed:
//  Permission denied". That happened right after Sign out, because the
//  browser kept sending the old session cookie to the sign-in page.
//
//  elmsSessionStart()  — starts the session; if the old session file is
//                        unusable it carries on with a fresh session instead
//                        of showing warnings or crashing.
//  elmsSessionEnd()    — signs out completely AND removes the cookie, so the
//                        next page never tries to reopen the old file.
// ============================================================

function elmsSessionStart(): bool
{
    if (session_status() === PHP_SESSION_ACTIVE) return true;
    if (@session_start()) return true;

    // Old id could not be opened/read: forget it and start clean.
    if (session_status() === PHP_SESSION_ACTIVE) return true;
    if (!headers_sent()) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', [
            'expires' => time() - 42000, 'path' => $p['path'] ?: '/', 'domain' => $p['domain'],
            'secure' => (bool) $p['secure'], 'httponly' => true, 'samesite' => 'Lax',
        ]);
        session_id(session_create_id());
        if (@session_start()) return true;
    }
    // Last resort: the page still works for this request, just without a saved session.
    $_SESSION = [];
    return false;
}

/** Session keys that hold a sign-in that is waiting for its e-mailed OTP. */
const ELMS_OTP_PENDING_KEYS = [
    'otp_pending_user_id',
    'otp_pending_username',
    'otp_pending_role',
    'otp_pending_first_name',
    'otp_pending_last_name',
    'otp_pending_email_hint',
    'otp_pending_remember',
    'otp_pending_fails',
    'otp_last_sent_at',
    'otp_flash',
];

/**
 * Cancels a sign-in that is still waiting for its OTP (user went Back to the
 * login page, pressed "Back to Login", or typed too many wrong codes).
 * The code already e-mailed stops working too, so going Forward / reopening
 * verify_otp.php can no longer finish that sign-in.
 */
function elmsCancelPendingOtp(?mysqli $conn): void
{
    $uid = (int) ($_SESSION['otp_pending_user_id'] ?? 0);
    if ($uid > 0 && $conn instanceof mysqli) {
        try {
            $st = $conn->prepare('UPDATE login_otps SET used = 1 WHERE user_id = ?');
            $st->bind_param('i', $uid);
            $st->execute();
            $st->close();
        } catch (Throwable $e) {
            // table missing / DB hiccup: the session part below still cancels it
        }
    }
    foreach (ELMS_OTP_PENDING_KEYS as $k) {
        unset($_SESSION[$k]);
    }
}

function elmsSessionEnd(): void
{
    $_SESSION = [];
    if (!headers_sent() && ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', [
            'expires' => time() - 42000, 'path' => $p['path'] ?: '/', 'domain' => $p['domain'],
            'secure' => (bool) $p['secure'], 'httponly' => (bool) $p['httponly'],
            'samesite' => $p['samesite'] ?: 'Lax',
        ]);
    }
    if (session_status() === PHP_SESSION_ACTIVE) {
        @session_destroy();
    }
}