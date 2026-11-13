<?php
// ============================================================
//  Arandia College eLMS — "Remember me" on the sign-in page
//  File: includes/remember_me.php
//
//  When "Remember me" is ticked and the sign-in finishes, this device
//  gets a random sign-in key (cookie "remember_device", 30 days). Next
//  time, login.php shows the username and a filled password box; pressing
//  Sign In uses the key instead of the typed password. Teachers and
//  students still get the e-mailed sign-in code, as before.
//
//  The real password is NEVER stored — not in the cookie and not in the
//  database. Only a SHA-256 hash of the key is saved (table remember_tokens),
//  so a copied database cannot be used to sign in.
//
//  The key stops working when: 30 days pass, the user signs in with
//  "Remember me" unticked, the password is changed or reset, or the
//  account is deactivated.
// ============================================================

if (!defined('ELMS_REMEMBER_DAYS')) {
    define('ELMS_REMEMBER_DAYS', 30);
    define('ELMS_REMEMBER_COOKIE', 'remember_device');
}

/** Creates the table the first time it is needed (same as database/migration_remember_me.sql). */
function rmEnsureTable(mysqli $conn): void
{
    static $done = false;
    if ($done) return;
    $conn->query(
        "CREATE TABLE IF NOT EXISTS remember_tokens (
            id          INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            user_id     INT NOT NULL,
            selector    CHAR(24) NOT NULL,
            token_hash  CHAR(64) NOT NULL,
            expires_at  DATETIME NOT NULL,
            created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY uq_selector (selector),
            KEY idx_user (user_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    );
    $done = true;
}

function rmCookieOptions(int $expires): array
{
    $https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    return ['expires' => $expires, 'path' => '/', 'secure' => $https, 'httponly' => true, 'samesite' => 'Lax'];
}

/** "selector:validator" from the cookie, or null. */
function rmReadCookie(): ?array
{
    $raw = (string) ($_COOKIE[ELMS_REMEMBER_COOKIE] ?? '');
    if (!preg_match('/^([a-f0-9]{24}):([a-f0-9]{64})$/', $raw, $m)) return null;
    return [$m[1], $m[2]];
}

/**
 * The account this device is remembered for, or null.
 * Returns ['id','username','email','role','first_name','last_name','status'].
 */
function rmCheck(mysqli $conn): ?array
{
    $c = rmReadCookie();
    if (!$c) return null;
    [$selector, $validator] = $c;
    try {
        rmEnsureTable($conn);
        $st = $conn->prepare(
            "SELECT t.user_id, t.token_hash, t.expires_at,
                    u.id, u.username, u.email, u.school_id, u.role, u.first_name, u.last_name, u.status
               FROM remember_tokens t JOIN users u ON u.id = t.user_id
              WHERE t.selector = ? LIMIT 1"
        );
        $st->bind_param('s', $selector);
        $st->execute();
        $row = $st->get_result()->fetch_assoc();
        $st->close();
    } catch (Throwable $e) {
        return null;
    }
    if (!$row
        || !hash_equals($row['token_hash'], hash('sha256', $validator))
        || strtotime($row['expires_at']) < time()
        || $row['status'] !== 'Active') {
        rmForgetDevice($conn);
        return null;
    }
    return $row;
}

/** Remember this device for $userId (replaces this device's old key). */
function rmRemember(mysqli $conn, int $userId): void
{
    try {
        rmEnsureTable($conn);
        rmForgetDevice($conn, false);
        $selector  = bin2hex(random_bytes(12));
        $validator = bin2hex(random_bytes(32));
        $hash      = hash('sha256', $validator);
        $days      = ELMS_REMEMBER_DAYS;
        $st = $conn->prepare(
            "INSERT INTO remember_tokens (user_id, selector, token_hash, expires_at)
             VALUES (?, ?, ?, DATE_ADD(NOW(), INTERVAL ? DAY))"
        );
        $st->bind_param('issi', $userId, $selector, $hash, $days);
        $st->execute();
        $st->close();
        // tidy up: expired keys of everyone
        $conn->query("DELETE FROM remember_tokens WHERE expires_at < NOW()");
        setcookie(ELMS_REMEMBER_COOKIE, $selector . ':' . $validator, rmCookieOptions(time() + $days * 86400));
        $_COOKIE[ELMS_REMEMBER_COOKIE] = $selector . ':' . $validator;
    } catch (Throwable $e) {
        error_log('[eLMS remember-me] ' . $e->getMessage());
    }
}

/** Forget this device (its key is deleted and the cookie removed). */
function rmForgetDevice(mysqli $conn, bool $clearCookie = true): void
{
    $c = rmReadCookie();
    if ($c) {
        try {
            rmEnsureTable($conn);
            $st = $conn->prepare("DELETE FROM remember_tokens WHERE selector = ?");
            $st->bind_param('s', $c[0]);
            $st->execute();
            $st->close();
        } catch (Throwable $e) {
        }
    }
    if ($clearCookie && isset($_COOKIE[ELMS_REMEMBER_COOKIE])) {
        setcookie(ELMS_REMEMBER_COOKIE, '', rmCookieOptions(time() - 3600));
        unset($_COOKIE[ELMS_REMEMBER_COOKIE]);
    }
}

/** Forget every device of a user (call after a password change or reset). */
function rmForgetUser(mysqli $conn, int $userId): void
{
    try {
        rmEnsureTable($conn);
        $st = $conn->prepare("DELETE FROM remember_tokens WHERE user_id = ?");
        $st->bind_param('i', $userId);
        $st->execute();
        $st->close();
    } catch (Throwable $e) {
    }
}
