<?php
// ============================================================
//  Arandia College eLMS — Admin Permission Engine
//  File: config/permissions.php
//
//  Include AFTER config/conn.php and AFTER session_start().
//  Covers the Head Admin / Regular Admin hierarchy:
//    - Head Admin: full access, bypasses every check below.
//    - Regular Admin ("Admin" role): only what's explicitly
//      granted in `admin_permissions`.
//    - Admission ("Admission" role): not an admin. Has no permission
//      keys; can only enroll students (see admission/ and api/accounts.php).
// ============================================================

/** True if the logged-in user is the Head Admin. */
function isHeadAdmin(): bool
{
    return ($_SESSION['role'] ?? '') === 'Head Admin';
}

/**
 * True if the logged-in user is an Admission officer. Admission accounts can
 * only enroll students (create Student accounts + view the student list) —
 * they never get admin permissions and cannot open the admin/ pages.
 */
function isAdmission(): bool
{
    return ($_SESSION['role'] ?? '') === 'Admission';
}

/** True if the logged-in user is any kind of admin (Head or Regular). */
function isAnyAdmin(): bool
{
    return in_array($_SESSION['role'] ?? '', ['Head Admin', 'Admin'], true);
}

/**
 * Fetch every permission granted (granted=1) to a given admin, as a
 * flat array of perm_key strings. Empty for a Regular Admin who has
 * nothing granted yet. Head Admin is NOT looked up here — callers
 * should check isHeadAdmin() first and skip this entirely.
 */
function fetchGrantedPermissions(mysqli $conn, int $adminId): array
{
    $out = [];
    $stmt = $conn->prepare("SELECT perm_key FROM admin_permissions WHERE admin_id = ? AND granted = 1");
    if (!$stmt) {
        // Table missing / migration not run yet — fail safe to "no permissions"
        // instead of a fatal error, so the rest of the app keeps working.
        error_log('[permissions.php] admin_permissions lookup failed: ' . $conn->error . ' — did you run database/migration_head_admin_permissions.sql?');
        return $out;
    }
    $stmt->bind_param('i', $adminId);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $out[] = $row['perm_key'];
    }
    $stmt->close();
    return $out;
}

/**
 * The actual permission check. Head Admin always passes. A Regular
 * Admin passes only if the perm_key is currently granted in the
 * database — read live, every request, so a change the Head Admin
 * makes takes effect on the Regular Admin's very next click, not on
 * their next login. (This deliberately does NOT cache in $_SESSION:
 * an old session would then keep an old, possibly-revoked, permission
 * set for as long as that session lives.)
 *
 * Within a single request, repeated calls reuse one DB fetch instead
 * of re-querying per permission — that's what the static cache below
 * is for. It resets on every new request, so it never goes stale
 * across requests the way a session cache would.
 */
function adminHasPermission(mysqli $conn, string $permKey): bool
{
    if (isHeadAdmin()) {
        return true;
    }
    if (($_SESSION['role'] ?? '') !== 'Admin') {
        return false;
    }
    static $cache = null;
    static $cachedForUserId = null;
    $userId = (int) ($_SESSION['user_id'] ?? 0);
    if ($cache === null || $cachedForUserId !== $userId) {
        $cache = array_fill_keys(fetchGrantedPermissions($conn, $userId), true);
        $cachedForUserId = $userId;
    }
    return !empty($cache[$permKey]);
}

/**
 * Page-level guard. Put this at the top of an admin/*.php page,
 * right after the existing login/role check. Redirects (with a
 * toast) if the current admin lacks ANY of the given permissions.
 * Pass a single perm_key, or an array to mean "any one of these".
 */
function requireAdminPermission(mysqli $conn, $permKeyOrKeys, string $redirectTo = 'dashboard.php'): void
{
    if (isHeadAdmin()) {
        return;
    }
    $keys = is_array($permKeyOrKeys) ? $permKeyOrKeys : [$permKeyOrKeys];
    foreach ($keys as $k) {
        if (adminHasPermission($conn, $k)) {
            return;
        }
    }
    header('Location: ' . $redirectTo . '?toast=' . urlencode('Access denied — you do not have permission to view this page.') . '&error=1');
    exit;
}

/**
 * API-level guard. Put this at the top of an api/*.php endpoint,
 * right after the existing "Auth check — Admin only" block. Sends a
 * 403 JSON response and stops execution if the permission is missing.
 * This is the check that matters most — the UI hiding a button is
 * just a courtesy, this is what actually blocks a direct API call.
 */
function requireApiPermission(mysqli $conn, $permKeyOrKeys): void
{
    if (isHeadAdmin()) {
        return;
    }
    $keys = is_array($permKeyOrKeys) ? $permKeyOrKeys : [$permKeyOrKeys];
    foreach ($keys as $k) {
        if (adminHasPermission($conn, $k)) {
            return;
        }
    }
    http_response_code(403);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Access denied — you do not have permission to do this.']);
    exit;
}

/** Record an entry in admin_activity_log. Never throws — logging failure shouldn't break the request. */
function logAdminActivity(mysqli $conn, int $adminId, string $action, ?string $targetType = null, ?int $targetId = null, ?string $details = null): void
{
    try {
        $stmt = $conn->prepare(
            "INSERT INTO admin_activity_log (admin_id, action, target_type, target_id, details) VALUES (?, ?, ?, ?, ?)"
        );
        $actor = $adminId > 0 ? $adminId : null;   // admin_id is linked to users.id; no account → empty
        $stmt->bind_param('issis', $actor, $action, $targetType, $targetId, $details);
        $stmt->execute();
        $stmt->close();
    } catch (Throwable $e) {
        // swallow — audit logging must never break the admin action itself
    }
}

/** All permission catalog rows, grouped by module — used to render the permission checkbox grid. */
function fetchPermissionCatalog(mysqli $conn): array
{
    $grouped = [];
    $res = $conn->query("SELECT perm_key, label, type, module FROM permissions ORDER BY sort_order");
    if (!$res) {
        error_log('[permissions.php] permissions catalog lookup failed: ' . $conn->error . ' — did you run database/migration_head_admin_permissions.sql?');
        return $grouped;
    }
    while ($row = $res->fetch_assoc()) {
        $grouped[$row['module']][] = $row;
    }
    return $grouped;
}
