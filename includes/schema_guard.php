<?php
// ============================================================
//  Arandia College eLMS — Database self-check for account roles
//  File: includes/schema_guard.php
//
//  elmsEnsureRoles($conn) makes sure users.role can hold 'Admission'
//  (the same change as database/migration_admission_role.sql), so the
//  Admission role works even if that SQL file was never imported.
//
//  Why: without it, MySQL/MariaDB in XAMPP's default (non-strict) mode
//  silently saves an Admission account with an EMPTY role. That account
//  then gets its sign-in code but is sent back to the login page after
//  entering it. This also repairs accounts saved that way (it knows they
//  were Admission accounts from the "Created Admission account" entry in
//  the activity log).
//
//  Cheap: one SHOW COLUMNS per request, only on the pages that call it
//  (login, OTP, accounts API, admin account pages, admission pages).
//  Never throws — a failure is logged and the page carries on.
// ============================================================

if (!defined('ELMS_ROLES')) define('ELMS_ROLES', ['Head Admin', 'Admin', 'Admission', 'Teacher', 'Student']);

if (!function_exists('elmsEnsureRoles')) {
    function elmsEnsureRoles(mysqli $conn): bool
    {
        static $ok = null;
        if ($ok !== null) return $ok;
        $ok = true;
        try {
            $res = $conn->query("SHOW COLUMNS FROM users LIKE 'role'");
            $col = $res ? $res->fetch_assoc() : null;
            if (!$col) return $ok;   // no users table yet

            if (stripos((string) $col['Type'], "'Admission'") === false) {
                // Rows with an empty role would make a strict ALTER fail, so run it non-strict
                $mode = (string) ($conn->query("SELECT @@SESSION.sql_mode")->fetch_row()[0] ?? '');
                $conn->query("SET SESSION sql_mode = ''");
                $done = $conn->query(
                    "ALTER TABLE users MODIFY role ENUM('Head Admin','Admin','Admission','Teacher','Student') NOT NULL DEFAULT 'Student'"
                );
                $st = $conn->prepare("SET SESSION sql_mode = ?");
                $st->bind_param('s', $mode);
                $st->execute();
                $st->close();
                if (!$done) {
                    $ok = false;
                    error_log('[schema_guard] could not add the Admission role: ' . $conn->error . ' — import database/migration_admission_role.sql');
                    return $ok;
                }
            }

            // Repair Admission accounts that were saved with an empty role
            $empty = $conn->query("SELECT 1 FROM users WHERE role = '' LIMIT 1");
            if ($empty && $empty->num_rows) {
                $conn->query(
                    "UPDATE users u
                     JOIN admin_activity_log l
                       ON l.target_id = u.id AND l.target_type = 'user' AND l.action = 'create_account'
                      AND l.details LIKE 'Created Admission account%'
                     SET u.role = 'Admission'
                     WHERE u.role = ''"
                );
            }
        } catch (Throwable $e) {
            $ok = false;
            error_log('[schema_guard] ' . $e->getMessage());
        }
        return $ok;
    }
}
