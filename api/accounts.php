<?php
// ============================================================
//  Arandia College eLMS — Accounts API
//  File: api/accounts.php

session_start();
require_once '../config/conn.php';
require_once '../config/permissions.php';
require_once '../includes/auto_enroll.php';
require_once '../includes/schema_guard.php';

header('Content-Type: application/json');

// Auth check — Admin, Head Admin, or Admission (Admission: create Student accounts only, see below)
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['Admin', 'Head Admin', 'Admission'], true)) {
    http_response_code(empty($_SESSION['user_id']) ? 401 : 403); echo json_encode(['success' => false, 'message' => empty($_SESSION['user_id']) ? 'Your session has ended. Please sign in again.' : 'You are not allowed to do this.']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';
$actorId = (int) $_SESSION['user_id'];

// Admission officers can only enroll students: the one thing they may call
// here is "create" (and the create handler only lets them make Student accounts).
if (isAdmission() && !($method === 'POST' && $action === 'create')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Admission accounts can only enroll students.']);
    exit;
}

// Make sure users.role can hold 'Admission' (even if the migration was not imported)
elmsEnsureRoles($conn);

/** Roles a Head Admin can create from the Create account form. */
const STAFF_ROLES = ['Admin'];

/** Permission needed to manage (create/edit/delete) accounts of a given role. */
function permissionForRole(string $role): ?string
{
    switch ($role) {
        case 'Student': return 'manage_students';
        case 'Teacher': return 'manage_teachers';
        case 'Admin':
        case 'Admission':
        case 'Head Admin':
            return 'manage_admin_accounts';
        default: return null;
    }
}

/**
 * Runs an INSERT without letting a database error crash the page (PHP 8.1+ throws
 * on errors by default). $dup becomes true when a UNIQUE key was hit (error 1062),
 * e.g. two people saving the same LRN at the same moment.
 */
function insertAccountRow(mysqli_stmt $stmt, ?bool &$dup = null): bool
{
    $dup = false;
    try {
        return $stmt->execute();
    } catch (mysqli_sql_exception $e) {
        $dup = ((int) $e->getCode() === 1062);
        error_log('[accounts.php] ' . $e->getMessage());
        return false;
    }
}

/**
 * Names are saved with a capital first letter on every word ("juan dela cruz" →
 * "Juan Dela Cruz"). Only the first letter is raised — the rest is left as typed,
 * so "McDonald" or "De la Cruz" are not damaged. Multibyte safe (ñ → Ñ).
 */
function capitalizeName($value): string
{
    $v = trim(preg_replace('/\s+/u', ' ', (string) ($value ?? '')));
    if ($v === '') return '';
    $out = preg_replace_callback('/(^|[\s\-\'’.])(\p{Ll})/u', function ($m) {
        return $m[1] . mb_strtoupper($m[2], 'UTF-8');
    }, $v);
    return $out === null ? $v : $out;   // null only on invalid UTF-8: keep what was typed
}

/** Capitalizes first / middle / last name inside the request data. */
function capitalizeAccountNames(array &$data): void
{
    foreach (['first_name', 'last_name', 'middle_name'] as $k) {
        if (array_key_exists($k, $data) && $data[$k] !== null) $data[$k] = capitalizeName($data[$k]);
    }
    if (isset($data['middle_name']) && $data['middle_name'] === '') $data['middle_name'] = null;
}

/**
 * Extra rules for NEW accounts only (editing old accounts is not affected, so
 * existing records without a contact number can still be updated).
 * - Contact number is required for Student, Teacher and Admin: 11 digits, 09XXXXXXXXX.
 * - A student's LRN must be exactly 12 digits.
 * Returns an error message or null.
 */
function validateNewAccountRules(array $data, string $role): ?string
{
    if (in_array($role, ['Student', 'Teacher', 'Admin'], true)) {
        $contact = trim((string) ($data['contact'] ?? ''));
        if ($contact === '') return 'Contact number is required.';
        if (!preg_match('/^09\d{9}$/', $contact)) return 'Enter a valid 11-digit contact number (e.g. 09XXXXXXXXX).';
    }
    if ($role === 'Student') {
        if (!preg_match('/^\d{12}$/', trim((string) ($data['school_id'] ?? '')))) return 'LRN must be exactly 12 digits.';
    }
    return null;
}

/**
 * Field checks shared by create and update. Returns an error message or null.
 * A student's section must be one of the sections on the Sections page,
 * otherwise no teacher would ever see them (classes are matched by section name).
 */
function validateAccountFields(mysqli $conn, array $data, string $role): ?string
{
    if (!in_array($role, ['Student', 'Teacher', 'Admission', 'Admin'], true)) return 'Choose a valid account type.';
    foreach (['first_name' => 'First name', 'last_name' => 'Last name', 'school_id' => 'School ID'] as $k => $label) {
        if (trim((string) ($data[$k] ?? '')) === '') return "$label is required.";
        if (mb_strlen((string) $data[$k]) > 100) return "$label is too long.";
    }
    $email = trim((string) ($data['email'] ?? ''));
    // Everyone signs in with a code sent to this email (login.php), so it is required for every role.
    if ($email === '') return 'Email is required — the sign-in code is sent there.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) return 'Enter a valid email address.';
    if ($role === 'Student') {
        $sect = trim((string) ($data['section_dept'] ?? ''));
        if ($sect === '') return 'Choose the student\'s section.';
        if ($conn->query("SHOW TABLES LIKE 'sections'")->num_rows > 0) {
            $st = $conn->prepare("SELECT 1 FROM sections WHERE name = ? LIMIT 1");
            $st->bind_param('s', $sect);
            $st->execute();
            if (!$st->get_result()->fetch_row()) return 'That section does not exist. Add it on the Sections page first.';
        }
    }
    return null;
}

/**
 * Section for an account: '' means none (NULL). A named section must exist —
 * users.section_dept is linked to sections.name (database/migration_link_tables.sql).
 * Returns [value, error].
 */
function accountSection(mysqli $conn, $raw): array
{
    $sect = trim((string) ($raw ?? ''));
    if ($sect === '') return [null, null];
    if ($conn->query("SHOW TABLES LIKE 'sections'")->num_rows > 0) {
        $st = $conn->prepare("SELECT 1 FROM sections WHERE name = ? LIMIT 1");
        $st->bind_param('s', $sect);
        $st->execute();
        $ok = (bool) $st->get_result()->fetch_row();
        $st->close();
        if (!$ok) return [null, 'That section does not exist. Add it on the Sections page first.'];
    }
    return [$sect, null];
}

/**
 * Usernames are no longer typed by the admin (people sign in with their email or
 * LRN / School ID). The users.username column is still required and unique, so a
 * hidden one is made from the email (or the name) — e.g. juan.delacruz, juan.delacruz2.
 */
function accountUsername(mysqli $conn, array $data): string
{
    $email = strtolower(trim((string) ($data['email'] ?? '')));
    $base = $email !== '' ? strstr($email, '@', true) : '';
    if ($base === '' || $base === false) {
        $base = strtolower(substr(trim((string) ($data['first_name'] ?? '')), 0, 1) . trim((string) ($data['last_name'] ?? '')));
    }
    $base = substr(preg_replace('/[^a-z0-9._]/', '', (string) $base), 0, 50) ?: 'user';
    $name = $base;
    $st = $conn->prepare("SELECT 1 FROM users WHERE username = ? LIMIT 1");
    for ($i = 2; $i < 10000; $i++) {
        $st->bind_param('s', $name);
        $st->execute();
        if (!$st->get_result()->fetch_row()) break;
        $name = $base . $i;
    }
    $st->close();
    return $name;
}

/**
 * The email must be a real one: valid format AND a domain that can receive mail
 * (MX / A / AAAA record). Blocks fake addresses like juan@asdfgh.xyz.
 * If this server has no DNS / internet access (offline localhost) the domain check
 * is skipped, so it never blocks every account. Returns an error message or null.
 */
function accountEmailExists(?string $email): ?string
{
    $email = trim((string) $email);
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) return 'Enter a valid email address — it is used to sign in.';
    if (!function_exists('checkdnsrr')) return null;
    $domain = strtolower((string) substr((string) strrchr($email, '@'), 1));
    if ($domain === '' || strpos($domain, '.') === false) return 'That email address does not exist. Enter the account holder\'s real email.';
    if (function_exists('idn_to_ascii')) { $ascii = @idn_to_ascii($domain); if (is_string($ascii) && $ascii !== '') $domain = $ascii; }
    $has = static function (string $d): bool {
        return @checkdnsrr($d . '.', 'MX') || @checkdnsrr($d . '.', 'A') || @checkdnsrr($d . '.', 'AAAA');
    };
    if ($has($domain)) return null;
    if (!$has('gmail.com')) return null;   // DNS itself is unreachable, so do not trust the miss
    return 'That email address does not exist (its domain cannot receive mail). Enter the account holder\'s real email.';
}

/** Email is how people sign in, so two accounts cannot share one. Returns an error or null. */
function accountEmailTaken(mysqli $conn, ?string $email, int $exceptId = 0): ?string
{
    $email = trim((string) $email);
    if ($email === '') return null;
    $st = $conn->prepare("SELECT 1 FROM users WHERE LOWER(email) = LOWER(?) AND id <> ? LIMIT 1");
    $st->bind_param('si', $email, $exceptId);
    $st->execute();
    $taken = (bool) $st->get_result()->fetch_row();
    $st->close();
    return $taken ? 'Another account already uses this email. Each account needs its own email (it is used to sign in).' : null;
}

/** Save a checkbox map of perm_key => bool for an admin (Head Admin only calls this). */
function savePermissionGrants(mysqli $conn, int $adminId, array $grants): void
{
    $stmt = $conn->prepare(
        "INSERT INTO admin_permissions (admin_id, perm_key, granted) VALUES (?, ?, ?)
         ON DUPLICATE KEY UPDATE granted = VALUES(granted)"
    );
    foreach ($grants as $permKey => $granted) {
        $g = $granted ? 1 : 0;
        $stmt->bind_param('isi', $adminId, $permKey, $g);
        $stmt->execute();
    }
    $stmt->close();
}

/**
 * Automatic password for Student / Teacher / Admission accounts: surname + LRN / school ID,
 * lowercase, no spaces or accents  ->  "Macarubbo" + "1233431" = macarubbo1233431.
 * (Keep in sync with autoPw() in admin/partials/accounts-create.body.php and admission/enroll.php.)
 */
function accountDefaultPassword(string $lastName, string $schoolId): string
{
    $s = mb_strtolower(trim($lastName), 'UTF-8');
    if (class_exists('Normalizer')) {
        $n = Normalizer::normalize($s, Normalizer::FORM_D);
        if ($n !== false) $s = (string) preg_replace('/\p{Mn}+/u', '', $n);
    } else {
        $s = strtr($s, ['á' => 'a', 'à' => 'a', 'â' => 'a', 'ä' => 'a', 'ã' => 'a', 'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e',
                        'í' => 'i', 'ì' => 'i', 'î' => 'i', 'ï' => 'i', 'ó' => 'o', 'ò' => 'o', 'ô' => 'o', 'ö' => 'o', 'õ' => 'o',
                        'ú' => 'u', 'ù' => 'u', 'û' => 'u', 'ü' => 'u', 'ñ' => 'n', 'ç' => 'c']);
    }
    return preg_replace('/[^a-z0-9]/', '', $s) . trim($schoolId);
}

// ── POST: Create account ──────────────────────────────────
if ($method === 'POST' && $action === 'create') {
    $data = json_decode(file_get_contents('php://input'), true);
    if (!is_array($data)) $data = [];   // empty / broken request body
    capitalizeAccountNames($data);
    if (isset($data['school_id'])) $data['school_id'] = trim((string) $data['school_id']);
    if (isset($data['contact'])) $data['contact'] = trim((string) $data['contact']);

    // The password is automatic (surname + LRN / school ID); anything typed in the form is ignored.
    $data['password'] = accountDefaultPassword((string) ($data['last_name'] ?? ''), (string) ($data['school_id'] ?? ''));
    $data['confirm_password'] = $data['password'];

    $required = ['first_name', 'last_name', 'role', 'school_id', 'password'];
    foreach ($required as $f) {
        if (empty($data[$f])) {
            $label = ['first_name' => 'First name', 'last_name' => 'Last name', 'role' => 'Account type', 'school_id' => 'LRN / School ID', 'email' => 'Email', 'password' => 'Password'][$f] ?? $f;
            echo json_encode(['success' => false, 'message' => "$label is required."]);
            exit;
        }
    }

    // Head Admin accounts are never created through this form.
    if ($data['role'] === 'Head Admin') {
        echo json_encode(['success' => false, 'message' => 'Head Admin accounts cannot be created here.']);
        exit;
    }

    // Admission officers: Student accounts only (that is their whole job).
    // Admission accounts can no longer be created (existing ones keep working)
    if ($data['role'] === 'Admission') {
        echo json_encode(['success' => false, 'message' => 'Admission accounts can no longer be created.']);
        exit;
    }
    if (isAdmission() && $data['role'] !== 'Student') {
        echo json_encode(['success' => false, 'message' => 'Admission accounts can only enroll students.']);
        exit;
    }

    // A Regular Admin creating an Admin / Admission account, or lacking permission
    // for the target role entirely, is rejected — Head Admin always allowed.
    if (in_array($data['role'], STAFF_ROLES, true) && !isHeadAdmin()) {
        echo json_encode(['success' => false, 'message' => 'Only the Head Admin can create ' . $data['role'] . ' accounts.']);
        exit;
    }
    $needed = permissionForRole($data['role']);
    if ($needed && !isAdmission() && !adminHasPermission($conn, $needed)) {
        echo json_encode(['success' => false, 'message' => 'You do not have permission to create this type of account.']);
        exit;
    }

    if ($data['password'] !== ($data['confirm_password'] ?? '')) {
        echo json_encode(['success' => false, 'message' => 'Passwords do not match.']);
        exit;
    }

    if (strlen($data['password']) < 6) {
        echo json_encode(['success' => false, 'message' => 'The automatic password (surname + LRN) must be at least 6 characters. Check the surname and LRN.']);
        exit;
    }

    if ($err = validateAccountFields($conn, $data, (string) $data['role'])) {
        echo json_encode(['success' => false, 'message' => $err]);
        exit;
    }
    if ($err = validateNewAccountRules($data, (string) $data['role'])) {
        echo json_encode(['success' => false, 'message' => $err]);
        exit;
    }

    // School ID / LRN and email must be unique (both are used to sign in)
    $chk = $conn->prepare("SELECT id FROM users WHERE school_id = ? LIMIT 1");
    $chk->bind_param('s', $data['school_id']);
    $chk->execute();
    $chk->store_result();
    if ($chk->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => ($data['role'] === 'Student' ? 'This LRN is already registered. An account with this LRN already exists.' : 'This Employee ID is already registered. An account with this ID already exists.')]);
        $chk->close();
        exit;
    }
    $chk->close();
    // The email must exist (real domain) before any account is created
    if ($err = accountEmailExists($data['email'] ?? null)) {
        echo json_encode(['success' => false, 'message' => $err]);
        exit;
    }
    if ($err = accountEmailTaken($conn, $data['email'] ?? null)) {
        echo json_encode(['success' => false, 'message' => $err]);
        exit;
    }
    $data['username'] = accountUsername($conn, $data);   // hidden, made automatically

    $middle = $data['middle_name'] ?? null;
    $email = $data['email'] ?? null;
    $contact = $data['contact'] ?? null;
    [$sect, $sectErr] = accountSection($conn, $data['role'] === 'Admission' ? null : ($data['section_dept'] ?? null));
    if ($sectErr) {
        echo json_encode(['success' => false, 'message' => $sectErr]);
        exit;
    }
    $status = in_array($data['status'] ?? '', ['Active', 'Inactive']) ? $data['status'] : 'Active';

    // Hash the password before storing
    $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);

    $stmt = $conn->prepare(
        "INSERT INTO users (school_id, username, password, role, first_name, last_name,
         middle_name, email, contact, section_dept, status)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );
    $stmt->bind_param(
        'sssssssssss',
        $data['school_id'],
        $data['username'],
        $hashedPassword,
        $data['role'],
        $data['first_name'],
        $data['last_name'],
        $middle,
        $email,
        $contact,
        $sect,
        $status
    );

    if (insertAccountRow($stmt, $dupKey)) {
        $newId = $conn->insert_id;
        $stmt->close();

        // New account: the user must set their own password at first sign-in
        require_once __DIR__ . '/../includes/force_password.php';
        fpRequireChange($conn, (int) $newId);

        // The database must have kept the account type. (Old databases without the
        // Admission role would silently save it empty — that account could not sign in.)
        $chkRole = $conn->query("SELECT role FROM users WHERE id = " . (int) $newId)->fetch_row()[0] ?? '';
        if ($chkRole !== $data['role']) {
            $conn->query("DELETE FROM users WHERE id = " . (int) $newId);
            error_log('[accounts.php] role not saved (' . $data['role'] . ' → "' . $chkRole . '") — import database/migration_admission_role.sql');
            echo json_encode(['success' => false, 'message' => 'The database could not save this account type yet. Import database/migration_admission_role.sql in phpMyAdmin, then try again.']);
            exit;
        }

        // If a Regular Admin account was just created, save whatever permission
        // grants the Head Admin selected in the create form (default: none granted).
        if ($data['role'] === 'Admin' && isHeadAdmin() && !empty($data['permissions']) && is_array($data['permissions'])) {
            savePermissionGrants($conn, $newId, $data['permissions']);
        }

        // Students: enrolled right away in all the subjects of their section / grade level
        $enrolled = 0;
        if ($data['role'] === 'Student' && $sect) [$enrolled] = aeSyncStudent($conn, (int) $newId, false);

        $who = trim($data['first_name'] . ' ' . $data['last_name']);
        logAdminActivity($conn, $actorId, 'create_account', 'user', $newId, "Created {$data['role']} account '$who' ({$data['school_id']})." . ($enrolled ? " Enrolled in $enrolled subject(s) of $sect." : ''));
        echo json_encode(['success' => true, 'message' => 'Account created successfully.' . ($enrolled ? " Enrolled in $enrolled subject" . ($enrolled === 1 ? '' : 's') . " of $sect." : ''), 'id' => $newId]);
    } else {
        error_log('[accounts.php] ' . $conn->error);
        echo json_encode(['success' => false, 'message' => $dupKey ? 'This LRN / School ID, email or username is already registered.' : 'Could not save. Please try again.']);
    }
    exit;
}

// ── PUT: Update account ───────────────────────────────────
if ($method === 'PUT' && $action === 'update') {
    $data = json_decode(file_get_contents('php://input'), true);
    if (!is_array($data)) $data = [];   // empty / broken request body
    $id = (int) ($data['id'] ?? 0);
    if (!$id) {
        echo json_encode(['success' => false, 'message' => 'Invalid ID.']);
        exit;
    }

    // Look up the target's current role to decide which permission applies,
    // and to block editing the Head Admin or re-assigning that role.
    $tRes = $conn->prepare("SELECT role, section_dept, email FROM users WHERE id = ? LIMIT 1");
    $tRes->bind_param('i', $id);
    $tRes->execute();
    $target = $tRes->get_result()->fetch_assoc();
    $tRes->close();
    if (!$target) {
        echo json_encode(['success' => false, 'message' => 'Account not found.']);
        exit;
    }
    if ($target['role'] === 'Head Admin' && !isHeadAdmin()) {
        echo json_encode(['success' => false, 'message' => 'Only the Head Admin can edit this account.']);
        exit;
    }
    // The Head Admin keeps their role and stays Active — they cannot demote or
    // lock themselves out, which would leave the school with no Head Admin.
    if ($target['role'] === 'Head Admin') {
        $data['role'] = 'Head Admin';
        $data['status'] = 'Active';
    } elseif (($data['role'] ?? $target['role']) === 'Head Admin') {
        echo json_encode(['success' => false, 'message' => 'The Head Admin role cannot be assigned here.']);
        exit;
    }
    $data['role'] = $data['role'] ?? $target['role'];
    // Only these account types exist (the Admission role was removed)
    if (!in_array($data['role'], ['Head Admin', 'Admin', 'Teacher', 'Student'], true)) {
        echo json_encode(['success' => false, 'message' => 'Choose a valid account type.']);
        exit;
    }
    if ($target['role'] !== 'Head Admin' && ($err = validateAccountFields($conn, $data, (string) $data['role']))) {
        echo json_encode(['success' => false, 'message' => $err]);
        exit;
    }
    if (in_array($target['role'], STAFF_ROLES, true) && !isHeadAdmin()) {
        echo json_encode(['success' => false, 'message' => 'Only the Head Admin can edit ' . $target['role'] . ' accounts.']);
        exit;
    }
    $needed = permissionForRole($target['role']);
    if ($needed && !in_array($target['role'], STAFF_ROLES, true) && !adminHasPermission($conn, $needed)) {
        echo json_encode(['success' => false, 'message' => 'You do not have permission to edit this account.']);
        exit;
    }
    // Non-Head-Admin cannot promote someone to Admin / Admission via edit either.
    if (in_array($data['role'] ?? '', STAFF_ROLES, true) && $data['role'] !== $target['role'] && !isHeadAdmin()) {
        echo json_encode(['success' => false, 'message' => 'Only the Head Admin can grant ' . $data['role'] . ' access.']);
        exit;
    }
    // Changing the role also needs permission for the NEW role (e.g. Student → Teacher
    // needs Manage Teachers, not just Manage Students).
    if ($data['role'] !== $target['role']) {
        $neededNew = permissionForRole($data['role']);
        if ($neededNew && !in_array($data['role'], STAFF_ROLES, true) && !adminHasPermission($conn, $neededNew)) {
            echo json_encode(['success' => false, 'message' => 'You do not have permission to make this account a ' . $data['role'] . '.']);
            exit;
        }
        // A role change would leave classes or schoolwork attached to the wrong kind of account.
        $links = $conn->query(
            "SELECT (SELECT COUNT(*) FROM teacher_assignments WHERE teacher_id = $id) AS classes,
                    (SELECT COUNT(*) FROM enrollments WHERE student_id = $id) AS enrollments,
                    (SELECT COUNT(*) FROM submissions WHERE student_id = $id) AS submissions"
        )->fetch_assoc();
        if ($target['role'] === 'Teacher' && (int) $links['classes'] > 0) {
            echo json_encode(['success' => false, 'message' => 'This teacher still has ' . (int) $links['classes'] . ' class(es). Give them to another teacher on the Sections page before changing the role.']);
            exit;
        }
        if ($target['role'] === 'Student' && ((int) $links['enrollments'] > 0 || (int) $links['submissions'] > 0)) {
            echo json_encode(['success' => false, 'message' => 'This student still has enrollments or submitted work, so the role cannot be changed. Create a separate account instead.']);
            exit;
        }
    }
    // School ID must stay unique
    $dupChk = $conn->prepare("SELECT id FROM users WHERE school_id = ? AND id <> ? LIMIT 1");
    $dupChk->bind_param('si', $data['school_id'], $id);
    $dupChk->execute();
    if ($dupChk->get_result()->fetch_assoc()) {
        echo json_encode(['success' => false, 'message' => 'Another account already uses this School ID.']);
        exit;
    }

    // A changed email must be a real one too (unchanged emails are skipped, so old accounts can still be edited)
    $emailChanged = strcasecmp(trim((string) ($data['email'] ?? '')), trim((string) ($target['email'] ?? ''))) !== 0;
    if ($emailChanged && ($err = accountEmailExists($data['email'] ?? null))) {
        echo json_encode(['success' => false, 'message' => $err]);
        exit;
    }

    // (only checked when the email changes, so older accounts that share one can still be edited)
    if (strcasecmp(trim((string) ($data['email'] ?? '')), trim((string) ($target['email'] ?? ''))) !== 0
        && ($err = accountEmailTaken($conn, $data['email'] ?? null, $id))) {
        echo json_encode(['success' => false, 'message' => $err]);
        exit;
    }

    $middle = $data['middle_name'] ?? null;
    $email = $data['email'] ?? null;
    $contact = $data['contact'] ?? null;
    [$sect, $sectErr] = accountSection($conn, $data['role'] === 'Admission' ? null : ($data['section_dept'] ?? null));   // Admission staff have no section
    if ($sectErr) {
        echo json_encode(['success' => false, 'message' => $sectErr]);
        exit;
    }
    $status = in_array($data['status'] ?? '', ['Active', 'Inactive']) ? $data['status'] : 'Active';

    if (!empty($data['password'])) {
        if (strlen($data['password']) < 6) {
            echo json_encode(['success' => false, 'message' => 'New password must be at least 6 characters.']);
            exit;
        }
        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
        // New password: any "Remember me" device of this account must sign in again
        require_once __DIR__ . '/../includes/remember_me.php';
        rmForgetUser($conn, (int) $id);
        $stmt = $conn->prepare(
            "UPDATE users SET first_name=?, last_name=?, middle_name=?, role=?, school_id=?,
             email=?, contact=?, section_dept=?, status=?, password=? WHERE id=?"
        );
        $stmt->bind_param(
            'ssssssssssi',
            $data['first_name'],
            $data['last_name'],
            $middle,
            $data['role'],
            $data['school_id'],
            $email,
            $contact,
            $sect,
            $status,
            $hashedPassword,
            $id
        );
    } else {
        $stmt = $conn->prepare(
            "UPDATE users SET first_name=?, last_name=?, middle_name=?, role=?, school_id=?,
             email=?, contact=?, section_dept=?, status=? WHERE id=?"
        );
        $stmt->bind_param(
            'sssssssssi',
            $data['first_name'],
            $data['last_name'],
            $middle,
            $data['role'],
            $data['school_id'],
            $email,
            $contact,
            $sect,
            $status,
            $id
        );
    }

    if ($stmt->execute()) {
        $stmt->close();

        // Head Admin can update a Regular Admin's permission grants in the same call.
        if ($target['role'] === 'Admin' && isHeadAdmin() && isset($data['permissions']) && is_array($data['permissions'])) {
            savePermissionGrants($conn, $id, $data['permissions']);
        }
        // No longer a Regular Admin (e.g. changed to Admission): drop the old grants
        // so they can never come back if the role is changed again later.
        if ($target['role'] === 'Admin' && $data['role'] !== 'Admin') {
            $dp = $conn->prepare("DELETE FROM admin_permissions WHERE admin_id = ?");
            if ($dp) { $dp->bind_param('i', $id); $dp->execute(); $dp->close(); }
        }

        // Student moved to another section: their subjects follow the new section
        $enrollNote = '';
        if ($data['role'] === 'Student' && (string) $sect !== (string) ($target['section_dept'] ?? '')) {
            [$added, $dropped] = aeSyncStudent($conn, $id, true);
            if ($added || $dropped) $enrollNote = " Subjects updated for $sect (+$added / -$dropped).";
        }

        logAdminActivity($conn, $actorId, 'update_account', 'user', $id, "Updated account (role: {$data['role']})." . $enrollNote);
        echo json_encode(['success' => true, 'message' => 'Account updated successfully.' . $enrollNote]);
    } else {
        error_log('[accounts.php] ' . $conn->error); echo json_encode(['success' => false, 'message' => 'Could not save. Please try again.']);
    }
    exit;
}

// ── DELETE: Remove account ────────────────────────────────
if ($method === 'DELETE' && $action === 'delete') {
    $id = (int) ($_GET['id'] ?? 0);
    if (!$id) {
        echo json_encode(['success' => false, 'message' => 'Invalid ID.']);
        exit;
    }

    // Prevent self-deletion
    if ($id === (int) $_SESSION['user_id']) {
        echo json_encode(['success' => false, 'message' => 'You cannot delete your own account.']);
        exit;
    }

    $tRes = $conn->prepare("SELECT role, username, first_name, last_name, school_id FROM users WHERE id = ? LIMIT 1");
    $tRes->bind_param('i', $id);
    $tRes->execute();
    $target = $tRes->get_result()->fetch_assoc();
    $tRes->close();
    if (!$target) {
        echo json_encode(['success' => false, 'message' => 'Account not found.']);
        exit;
    }
    if ($target['role'] === 'Head Admin') {
        echo json_encode(['success' => false, 'message' => 'The Head Admin account cannot be deleted.']);
        exit;
    }
    if (in_array($target['role'], STAFF_ROLES, true) && !isHeadAdmin()) {
        echo json_encode(['success' => false, 'message' => 'Only the Head Admin can delete ' . $target['role'] . ' accounts.']);
        exit;
    }
    $needed = permissionForRole($target['role']);
    if ($needed && !in_array($target['role'], STAFF_ROLES, true) && !adminHasPermission($conn, $needed)) {
        echo json_encode(['success' => false, 'message' => 'You do not have permission to delete this account.']);
        exit;
    }

    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param('i', $id);
    if ($stmt->execute()) {
        $stmt->close();
        logAdminActivity($conn, $actorId, 'delete_account', 'user', $id, "Deleted {$target['role']} account '" . trim($target['first_name'] . ' ' . $target['last_name']) . "' ({$target['school_id']}).");
        echo json_encode(['success' => true, 'message' => 'Account deleted.']);
    } else {
        error_log('[accounts.php] ' . $conn->error); echo json_encode(['success' => false, 'message' => 'Could not save. Please try again.']);
    }
    exit;
}

// ── GET: List accounts (optional) ────────────────────────
if ($method === 'GET' && $action === 'list') {
    requireApiPermission($conn, ['view_students', 'view_teachers', 'manage_students', 'manage_teachers', 'manage_admin_accounts']);
    $accounts = [];
    $res = $conn->query(
        "SELECT id, school_id, username, role, first_name, last_name, middle_name,
         email, contact, section_dept, status,
         DATE_FORMAT(created_at,'%b %d, %Y') AS created_at
         FROM users ORDER BY created_at DESC"
    );
    while ($r = $res->fetch_assoc()) {
        $accounts[] = $r;
    }
    echo json_encode(['success' => true, 'data' => $accounts]);
    exit;
}

// ── POST: Create a Regular Admin account (Head Admin only, no LRN/Section) ─
if ($method === 'POST' && $action === 'create_admin') {
    if (!isHeadAdmin()) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Only the Head Admin can create Admin accounts.']);
        exit;
    }

    $data = json_decode(file_get_contents('php://input'), true);
    if (!is_array($data)) $data = [];   // empty / broken request body
    capitalizeAccountNames($data);
    if (isset($data['contact'])) $data['contact'] = trim((string) $data['contact']);
    $required = ['first_name', 'last_name', 'email', 'password'];
    foreach ($required as $f) {
        if (empty($data[$f])) {
            $label = ['first_name' => 'First name', 'last_name' => 'Last name', 'role' => 'Account type', 'school_id' => 'LRN / School ID', 'email' => 'Email', 'password' => 'Password'][$f] ?? $f;
            echo json_encode(['success' => false, 'message' => "$label is required."]);
            exit;
        }
    }
    if ($data['password'] !== ($data['confirm_password'] ?? '')) {
        echo json_encode(['success' => false, 'message' => 'Passwords do not match.']);
        exit;
    }
    if (strlen($data['password']) < 6) {
        echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters.']);
        exit;
    }

    if ($err = validateNewAccountRules($data, 'Admin')) {
        echo json_encode(['success' => false, 'message' => $err]);
        exit;
    }

    // Admins sign in with their email, so it is required, must exist, and must be their own
    if ($err = accountEmailExists($data['email'])) {
        echo json_encode(['success' => false, 'message' => $err]);
        exit;
    }
    if ($err = accountEmailTaken($conn, $data['email'])) {
        echo json_encode(['success' => false, 'message' => $err]);
        exit;
    }
    $data['username'] = accountUsername($conn, $data);   // hidden, made automatically

    // Admin accounts don't have an LRN/Employee ID in the UI — generate a
    // unique internal identifier for the (NOT NULL, UNIQUE) school_id column.
    do {
        $schoolId = 'ADM-' . strtoupper(bin2hex(random_bytes(4)));
        $dup = $conn->prepare("SELECT id FROM users WHERE school_id = ? LIMIT 1");
        $dup->bind_param('s', $schoolId);
        $dup->execute();
        $dup->store_result();
        $isDup = $dup->num_rows > 0;
        $dup->close();
    } while ($isDup);

    $middle = $data['middle_name'] ?? null;
    $email = $data['email'] ?? null;
    $contact = $data['contact'] ?? null;
    $status = in_array($data['status'] ?? '', ['Active', 'Inactive']) ? $data['status'] : 'Active';
    $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
    $role = 'Admin';

    $stmt = $conn->prepare(
        "INSERT INTO users (school_id, username, password, role, first_name, last_name,
         middle_name, email, contact, section_dept, status)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NULL, ?)"
    );
    $stmt->bind_param(
        'ssssssssss',
        $schoolId,
        $data['username'],
        $hashedPassword,
        $role,
        $data['first_name'],
        $data['last_name'],
        $middle,
        $email,
        $contact,
        $status
    );

    if (insertAccountRow($stmt, $dupKey)) {
        $newId = $conn->insert_id;
        $stmt->close();

        // New account: the user must set their own password at first sign-in
        require_once __DIR__ . '/../includes/force_password.php';
        fpRequireChange($conn, (int) $newId);

        if (!empty($data['permissions']) && is_array($data['permissions'])) {
            savePermissionGrants($conn, $newId, $data['permissions']);
        }

        logAdminActivity($conn, $actorId, 'create_account', 'user', $newId, "Created Admin account '" . trim($data['first_name'] . ' ' . $data['last_name']) . "' ({$data['email']}).");
        echo json_encode(['success' => true, 'message' => 'Admin account created successfully.', 'id' => $newId]);
    } else {
        error_log('[accounts.php] ' . $conn->error);
        echo json_encode(['success' => false, 'message' => $dupKey ? 'This email is already registered.' : 'Could not save. Please try again.']);
    }
    exit;
}

// ── POST: Quick Active/Inactive toggle (Head Admin only, Admin targets) ──
if ($method === 'POST' && $action === 'toggle_status') {
    if (!isHeadAdmin()) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Only the Head Admin can do this.']);
        exit;
    }
    $data = json_decode(file_get_contents('php://input'), true);
    if (!is_array($data)) $data = [];   // empty / broken request body
    $id = (int) ($data['id'] ?? 0);
    if (!$id) {
        echo json_encode(['success' => false, 'message' => 'Invalid ID.']);
        exit;
    }
    $tRes = $conn->prepare("SELECT role, status FROM users WHERE id = ? LIMIT 1");
    $tRes->bind_param('i', $id);
    $tRes->execute();
    $target = $tRes->get_result()->fetch_assoc();
    $tRes->close();
    if (!$target || !in_array($target['role'], STAFF_ROLES, true)) {
        echo json_encode(['success' => false, 'message' => 'Admin / Admission account not found.']);
        exit;
    }
    $newStatus = $target['status'] === 'Active' ? 'Inactive' : 'Active';
    $stmt = $conn->prepare("UPDATE users SET status = ? WHERE id = ?");
    $stmt->bind_param('si', $newStatus, $id);
    if ($stmt->execute()) {
        $stmt->close();
        logAdminActivity($conn, $actorId, 'toggle_status', 'user', $id, "Status changed to $newStatus.");
        echo json_encode(['success' => true, 'message' => "Status set to $newStatus.", 'status' => $newStatus]);
    } else {
        error_log('[accounts.php] ' . $conn->error); echo json_encode(['success' => false, 'message' => 'Could not save. Please try again.']);
    }
    exit;
}

// ── GET: Fetch a Regular Admin's permission grants (Head Admin only) ─────
if ($method === 'GET' && $action === 'get_permissions') {
    if (!isHeadAdmin()) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Only the Head Admin can view permissions.']);
        exit;
    }
    $adminId = (int) ($_GET['admin_id'] ?? 0);
    echo json_encode([
        'success' => true,
        'catalog' => fetchPermissionCatalog($conn),
        'granted' => fetchGrantedPermissions($conn, $adminId),
    ]);
    exit;
}

// ── POST: Save a Regular Admin's permission grants (Head Admin only) ─────
if ($method === 'POST' && $action === 'save_permissions') {
    if (!isHeadAdmin()) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Only the Head Admin can change permissions.']);
        exit;
    }
    $data = json_decode(file_get_contents('php://input'), true);
    if (!is_array($data)) $data = [];   // empty / broken request body
    $adminId = (int) ($data['admin_id'] ?? 0);
    $grants = $data['permissions'] ?? [];
    if (!$adminId || !is_array($grants)) {
        echo json_encode(['success' => false, 'message' => 'Invalid request.']);
        exit;
    }
    savePermissionGrants($conn, $adminId, $grants);
    logAdminActivity($conn, $actorId, 'update_permissions', 'user', $adminId, 'Updated permission grants.');
    echo json_encode(['success' => true, 'message' => 'Permissions updated.']);
    exit;
}

// ── POST: Send a test e-mail to an account (Head Admin only) ─────────────
//  Confirms the account's address really receives the sign-in code e-mails.
if ($method === 'POST' && $action === 'test_email') {
    if (!isHeadAdmin()) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Only the Head Admin can do this.']);
        exit;
    }
    $data = json_decode(file_get_contents('php://input'), true);
    if (!is_array($data)) $data = [];
    $id = (int) ($data['id'] ?? 0);
    $st = $conn->prepare("SELECT first_name, email, role FROM users WHERE id = ? LIMIT 1");
    $st->bind_param('i', $id);
    $st->execute();
    $u = $st->get_result()->fetch_assoc();
    $st->close();
    if (!$u) {
        echo json_encode(['success' => false, 'message' => 'Account not found.']);
        exit;
    }
    $email = trim((string) ($u['email'] ?? ''));
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'This account has no valid email address. Click Edit and add the correct email — the sign-in code is sent there.']);
        exit;
    }
    require_once __DIR__ . '/../includes/mailer.php';
    require_once __DIR__ . '/../includes/settings.php';
    $sysName = elmsSetting('system_name');   // Admin → Settings
    $name = htmlspecialchars((string) $u['first_name']);
    $roleTxt = htmlspecialchars((string) $u['role']);
    $html = '<div style="font-family:Arial,sans-serif;max-width:560px;margin:0 auto;padding:20px;color:#333">'
          . '<div style="background:#003087;color:#fff;padding:24px;border-radius:12px 12px 0 0;text-align:center"><h2 style="margin:0">Email check</h2>'
          . '<p style="margin:6px 0 0;font-size:13px;opacity:.85">' . htmlspecialchars($sysName) . '</p></div>'
          . '<div style="background:#f9faff;border:1px solid #e0e8ff;border-top:none;padding:24px;border-radius:0 0 12px 12px">'
          . '<p>Hi ' . $name . ',</p><p>This is a test from the Head Admin. Your <strong>' . $roleTxt . '</strong> account will receive its sign-in codes at this address.</p>'
          . '<p style="font-size:12px;color:#888">No action needed.</p></div></div>';
    $text = "Hi {$u['first_name']},\n\nThis is a test from the Head Admin. Your account will receive its sign-in codes at this address.\n\n— {$sysName}";
    $err = elmsSendMail($email, $sysName . ' — Email check', $html, $text);
    if ($err === '') {
        logAdminActivity($conn, $actorId, 'test_email', 'user', $id, "Sent a test email to $email.");
        echo json_encode(['success' => true, 'message' => "Test email sent to $email. Ask them to check the inbox (and Spam)."]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Could not send to ' . $email . '. ' . (elmsIsLocalRequest() ? $err : 'Check the mail settings in config/secrets.php.')]);
    }
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid request.']);