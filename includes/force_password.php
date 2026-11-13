<?php
// ============================================================
//  Arandia College eLMS — New accounts must change their password
//  File: includes/force_password.php
//
//  A new account starts with an automatic password (surname + LRN / school
//  ID) or one the admin typed — both known to someone else. So every account
//  created from Accounts (api/accounts.php) is marked
//  users.must_change_password = 1. After signing in (verify_otp.php) that
//  user can only open change_password.php until a new password is saved:
//    · pages → sent to change_password.php
//    · APIs  → 403 JSON {must_change_password:true}
//  Accounts that existed before this update are NOT affected (default 0).
//
//  The column is added automatically the first time it is needed
//  (same as database/migration_force_password.sql).
// ============================================================

/** Adds users.must_change_password if it is missing. */
function fpEnsureColumn(mysqli $conn): void
{
    static $done = false;
    if ($done) return;
    $done = true;
    try {
        $r = $conn->query("SHOW COLUMNS FROM users LIKE 'must_change_password'");
        if ($r && $r->num_rows === 0) {
            $conn->query("ALTER TABLE users ADD COLUMN must_change_password TINYINT(1) NOT NULL DEFAULT 0");
        }
    } catch (Throwable $e) {
        error_log('[eLMS force password] ' . $e->getMessage());
    }
}

/** Mark an account: must choose a new password at its next sign-in. */
function fpRequireChange(mysqli $conn, int $userId): void
{
    fpEnsureColumn($conn);
    try {
        $st = $conn->prepare('UPDATE users SET must_change_password = 1 WHERE id = ?');
        $st->bind_param('i', $userId);
        $st->execute();
        $st->close();
    } catch (Throwable $e) {
        error_log('[eLMS force password] ' . $e->getMessage());
    }
}

/** Does this account still have to change its password? */
function fpMustChange(mysqli $conn, int $userId): bool
{
    fpEnsureColumn($conn);
    try {
        $st = $conn->prepare('SELECT must_change_password FROM users WHERE id = ? LIMIT 1');
        $st->bind_param('i', $userId);
        $st->execute();
        $row = $st->get_result()->fetch_assoc();
        $st->close();
        return !empty($row['must_change_password']);
    } catch (Throwable $e) {
        return false;
    }
}

/** Adds users.terms_accepted_at if it is missing (when the user accepted the Terms and Conditions). */
function fpEnsureTermsColumn(mysqli $conn): void
{
    static $done = false;
    if ($done) return;
    $done = true;
    try {
        $r = $conn->query("SHOW COLUMNS FROM users LIKE 'terms_accepted_at'");
        if ($r && $r->num_rows === 0) {
            $conn->query("ALTER TABLE users ADD COLUMN terms_accepted_at DATETIME NULL DEFAULT NULL");
        }
    } catch (Throwable $e) {
        error_log('[eLMS terms] ' . $e->getMessage());
    }
}

/** Has this account NOT accepted the Terms and Conditions yet? (Only asked on a first sign-in.) */
function fpTermsNeeded(mysqli $conn, int $userId): bool
{
    fpEnsureTermsColumn($conn);
    try {
        $st = $conn->prepare('SELECT terms_accepted_at FROM users WHERE id = ? LIMIT 1');
        $st->bind_param('i', $userId);
        $st->execute();
        $row = $st->get_result()->fetch_assoc();
        $st->close();
        return $row ? empty($row['terms_accepted_at']) : false;
    } catch (Throwable $e) {
        return false;
    }
}

/** Save that this account accepted the Terms and Conditions now. */
function fpAcceptTerms(mysqli $conn, int $userId): bool
{
    fpEnsureTermsColumn($conn);
    try {
        $st = $conn->prepare('UPDATE users SET terms_accepted_at = NOW() WHERE id = ?');
        $st->bind_param('i', $userId);
        $ok = $st->execute();
        $st->close();
        return (bool) $ok;
    } catch (Throwable $e) {
        error_log('[eLMS terms] ' . $e->getMessage());
        return false;
    }
}

/** Home page of a role, relative to the site root. */
function fpRoleHome(string $role): string
{
    switch ($role) {
        case 'Teacher':    return 'teacher/dashboard.php';
        case 'Student':    return 'student/dashboard.php';
        case 'Admission':  return 'admission/dashboard.php';
        case 'Admin':
        case 'Head Admin': return 'admin/dashboard.php';
        default:           return 'login.php';
    }
}

/** "../" for each folder level the running script is below the site root. */
function fpRootPrefix(): string
{
    $root = realpath(__DIR__ . '/..');
    $script = realpath($_SERVER['SCRIPT_FILENAME'] ?? '') ?: '';
    if ($root && $script && strpos($script, $root) === 0) {
        $rel = ltrim(str_replace('\\', '/', substr($script, strlen($root))), '/');
        return str_repeat('../', substr_count($rel, '/'));
    }
    return '';
}

/**
 * Called on every request (config/conn.php). While the signed-in account
 * still has to change its password, everything except the change-password
 * page (and signing in / out) is blocked.
 */
function fpEnforce(): void
{
    if (session_status() !== PHP_SESSION_ACTIVE || empty($_SESSION['user_id'])) return;
    if (empty($_SESSION['must_change_password'])) return;

    $self = basename($_SERVER['SCRIPT_FILENAME'] ?? '');
    // First sign-in: the Terms and Conditions come before the password change.
    // change_password.php only opens once they are accepted (terms.php clears this flag).
    $termsPending = !empty($_SESSION['terms_pending']);
    $allowed = ['terms.php', 'logout.php', 'login.php', 'verify_otp.php'];
    if (!$termsPending) $allowed[] = 'change_password.php';
    if (in_array($self, $allowed, true)) return;
    if (headers_sent()) return;

    $script = str_replace('\\', '/', $_SERVER['SCRIPT_FILENAME'] ?? '');
    $isApi = strpos($script, '/api/') !== false || !empty($_SERVER['HTTP_X_ELMS_LIVE'])
        || strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest';
    if ($isApi) {
        http_response_code(403);
        header('Content-Type: application/json');
        header('Cache-Control: no-store');
        echo json_encode(['success' => false, 'message' => $termsPending ? 'Please accept the Terms and Conditions first.' : 'Please change your password first.', 'must_change_password' => true]);
        exit;
    }
    header('Location: ' . fpRootPrefix() . ($termsPending ? 'terms.php' : 'change_password.php'));
    exit;
}