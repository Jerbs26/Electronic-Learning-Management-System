<?php
// ============================================================
//  Arandia College eLMS — Admin: add / edit a subject
//  File: api/admin_subjects.php
//  Used by admin/modules.php (Add subject / Edit subject buttons).
//  Needs the Manage Subjects permission (Head Admin always).
//
//  POST ?action=save    id (0 = new), course_code, course_name,
//                       description, code_auto (1 = the code was made
//                       from the name: a taken code moves to the next
//                       free number, e.g. HS7-ICT -> HS7-ICT2)
//                       School year and semester are not sent: a new
//                       subject gets the ones in Admin -> Settings, and
//                       editing keeps the subject's own.
//  POST ?action=usage   id → can it be deleted? (and why not)
//  POST ?action=delete  id → deletes the subject and its modules
//
//  Deleting is only allowed while nobody uses the subject: no section
//  has it, no student is enrolled, and there are no assignments,
//  quizzes, grades, attendance, awards, announcements or module
//  progress for it. (The database would otherwise remove all of
//  those with it.) Its modules and their files go with it.
//
//  The subject code decides where a subject is listed:
//    HS<grade>-…  → that grade (Modules page, and listed first when
//                   adding subjects to a "Grade N" section)
//    SHS-, STEM-, ABM-, HUMSS-, GAS- → the Senior High groups
//  Every change writes a "subject.changed" live-update event, so open
//  Admin, Teacher and Student pages refresh by themselves.
// ============================================================
session_start();
require_once '../config/conn.php';
require_once '../config/permissions.php';
require_once '../includes/settings.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['Admin', 'Head Admin'], true)) {
    http_response_code(empty($_SESSION['user_id']) ? 401 : 403);
    echo json_encode(['success' => false, 'message' => empty($_SESSION['user_id']) ? 'Your session has ended. Please sign in again.' : 'You are not allowed to do this.']);
    exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Use POST.']);
    exit;
}
requireApiPermission($conn, 'manage_subjects');

$actorId = (int) $_SESSION['user_id'];
$action  = $_GET['action'] ?? '';

function subjOut(bool $ok, string $msg, array $extra = []): void
{
    echo json_encode(array_merge(['success' => $ok, 'message' => $msg], $extra));
    exit;
}

/**
 * What still uses this subject. Empty array = safe to delete.
 * Each entry: [count, label]. Tables that may not exist on older
 * databases are skipped.
 */
function subjUsage(mysqli $conn, int $courseId): array
{
    $checks = [
        ['teacher_assignments', 'course_id = ?', 'section', 'sections'],
        ['enrollments', "course_id = ? AND status = 'Enrolled'", 'enrolled student', 'enrolled students'],
        ['assignments', 'course_id = ?', 'assignment', 'assignments'],
        ['quizzes', 'course_id = ?', 'quiz', 'quizzes'],
        ['grades', 'course_id = ?', 'grade record', 'grade records'],
        ['gradebook_items', 'course_id = ?', 'gradebook column', 'gradebook columns'],
        ['grade_items', 'course_id = ?', 'grade item', 'grade items'],
        ['attendance', 'course_id = ?', 'attendance record', 'attendance records'],
        ['student_awards', 'course_id = ?', 'award', 'awards'],
        ['announcements', 'course_id = ?', 'announcement', 'announcements'],
        ['module_progress', 'course_id = ?', 'module progress record', 'module progress records'],
        ['module_views', 'module_id IN (SELECT id FROM modules WHERE course_id = ?)', 'module opened by students', 'module opens by students'],
    ];
    $used = [];
    foreach ($checks as [$table, $where, $one, $many]) {
        try {
            if (!$conn->query("SHOW TABLES LIKE '$table'")->num_rows) continue;
            $st = $conn->prepare("SELECT COUNT(*) FROM `$table` WHERE $where");
            $st->bind_param('i', $courseId);
            $st->execute();
            $n = (int) $st->get_result()->fetch_row()[0];
            $st->close();
            if ($n > 0) $used[] = $n . ' ' . ($n === 1 ? $one : $many);
        } catch (Throwable $e) {
            // a check that cannot run must not allow a delete
            $used[] = 'data that could not be checked (' . $table . ')';
        }
    }
    return $used;
}

function subjFind(mysqli $conn, int $id): ?array
{
    $st = $conn->prepare("SELECT id, course_code, course_name FROM courses WHERE id = ?");
    $st->bind_param('i', $id);
    $st->execute();
    $r = $st->get_result()->fetch_assoc();
    $st->close();
    return $r ?: null;
}

if ($action === 'usage' || $action === 'delete') {
    $id = (int) ($_POST['id'] ?? 0);
    $course = $id > 0 ? subjFind($conn, $id) : null;
    if (!$course) subjOut(false, 'That subject no longer exists. Refresh the page.');

    $st = $conn->prepare("SELECT id, file_path FROM modules WHERE course_id = ?");
    $st->bind_param('i', $id);
    $st->execute();
    $mods = $st->get_result()->fetch_all(MYSQLI_ASSOC);
    $st->close();

    $used = subjUsage($conn, $id);
    $reason = $used ? 'This subject is still in use (' . implode(', ', $used) . '). Remove it from its sections on the Sections page first, or keep it.' : '';

    if ($action === 'usage') {
        subjOut(true, '', ['can_delete' => !$used, 'reason' => $reason, 'modules' => count($mods),
                           'name' => $course['course_name'], 'code' => $course['course_code']]);
    }

    // ── Delete ──────────────────────────────────────────────
    if ($used) subjOut(false, $reason);
    $conn->begin_transaction();
    try {
        $st = $conn->prepare("DELETE FROM modules WHERE course_id = ?");
        $st->bind_param('i', $id);
        $st->execute();
        $st->close();
        $st = $conn->prepare("DELETE FROM courses WHERE id = ?");
        $st->bind_param('i', $id);
        $st->execute();
        $gone = $st->affected_rows;
        $st->close();
        if ($gone < 1) throw new RuntimeException('not deleted');
        $conn->commit();
    } catch (Throwable $e) {
        $conn->rollback();
        error_log('[eLMS subjects] delete failed: ' . $e->getMessage());
        subjOut(false, 'The subject could not be deleted. Please try again.');
    }
    // Module files: only after the database change went through
    foreach ($mods as $m) {
        $f = (string) ($m['file_path'] ?? '');
        if ($f !== '' && strpos($f, 'uploads/modules/') === 0 && strpos($f, '..') === false && is_file('../' . $f)) @unlink('../' . $f);
    }
    logAdminActivity($conn, $actorId, 'delete_subject', 'course', $id,
        $course['course_code'] . ' · ' . $course['course_name'] . (count($mods) ? ' · with ' . count($mods) . ' module' . (count($mods) === 1 ? '' : 's') : ''));
    // Live update for other admin pages (the subject's own id is gone, so no course link)
    try {
        if ($conn->query("SHOW TABLES LIKE 'sync_events'")->num_rows) {
            $t = mb_substr($course['course_name'], 0, 255);
            $st = $conn->prepare("INSERT INTO sync_events (kind, course_id, audience, title) VALUES ('subject.changed', NULL, 'admins', ?)");
            $st->bind_param('s', $t);
            $st->execute();
            $st->close();
        }
    } catch (Throwable $e) { }
    subjOut(true, 'Subject deleted.');
}

if ($action !== 'save') {
    http_response_code(400);
    subjOut(false, 'Unknown action.');
}

$id          = (int) ($_POST['id'] ?? 0);
$code        = strtoupper(preg_replace('/\s+/', '', (string) ($_POST['course_code'] ?? '')));
$name        = trim(preg_replace('/\s+/', ' ', (string) ($_POST['course_name'] ?? '')));
$description = trim((string) ($_POST['description'] ?? ''));
$codeAuto    = ($_POST['code_auto'] ?? '') === '1';

// ── Check the input ─────────────────────────────────────────
if ($name === '' || mb_strlen($name) > 150) subjOut(false, 'Enter the subject name (up to 150 characters).');
if ($code === '' || strlen($code) > 20 || !preg_match('/^[A-Z0-9]+(-[A-Z0-9]+)*$/', $code)) {
    subjOut(false, 'Enter a subject code using letters, numbers and dashes only (up to 20 characters), e.g. HS7-ICT.');
}
if (mb_strlen($description) > 2000) subjOut(false, 'Keep the description under 2,000 characters.');

/** The subject that already uses $code (other than $exceptId), or null. */
function subjCodeOwner(mysqli $conn, string $code, int $exceptId): ?array
{
    $st = $conn->prepare("SELECT id, course_name FROM courses WHERE UPPER(course_code) = ? AND id <> ? LIMIT 1");
    $st->bind_param('si', $code, $exceptId);
    $st->execute();
    $row = $st->get_result()->fetch_assoc();
    $st->close();
    return $row ?: null;
}

// Same code twice would mix two subjects up everywhere (Subjects, Sections, search)
$dup = subjCodeOwner($conn, $code, $id);
if ($dup && $codeAuto) {
    // Made from the name and someone took it meanwhile: next free number (HS7-ICT2, HS7-ICT3 ...)
    $free = null;
    for ($n = 2; $n < 1000 && $free === null; $n++) {
        $tail = (preg_match('/\d$/', $code) ? '-' : '') . $n;   // BIO1 -> BIO1-2, not BIO12
        $try = rtrim(substr($code, 0, 20 - strlen($tail)), '-') . $tail;
        if (preg_match('/^[A-Z0-9]+(-[A-Z0-9]+)*$/', $try) && !subjCodeOwner($conn, $try, $id)) $free = $try;
    }
    if ($free !== null) { $code = $free; $dup = null; }
}
if ($dup) subjOut(false, "The code $code is already used by \"{$dup['course_name']}\". Use a different code.");

/** Live-update event: open pages of admins, and of the subject's teachers and students, refresh. */
function subjSyncEvent(mysqli $conn, int $courseId, string $title): void
{
    try {
        if (!$conn->query("SHOW TABLES LIKE 'sync_events'")->num_rows) return;
        $st = $conn->prepare("INSERT INTO sync_events (kind, course_id, audience, title) VALUES ('subject.changed', ?, 'all', ?)");
        $title = mb_substr($title, 0, 255);
        $st->bind_param('is', $courseId, $title);
        $st->execute();
        $st->close();
    } catch (Throwable $e) {
        error_log('[eLMS subjects] live update not sent: ' . $e->getMessage());   // the save itself still counts
    }
}

$desc = $description === '' ? null : $description;

if ($id) {
    // ── Edit ────────────────────────────────────────────────
    $st = $conn->prepare("SELECT id, course_code, course_name FROM courses WHERE id = ?");
    $st->bind_param('i', $id);
    $st->execute();
    $old = $st->get_result()->fetch_assoc();
    $st->close();
    if (!$old) subjOut(false, 'That subject no longer exists. Refresh the page.');

    // School year and semester stay as they are (they are not on the form any more)
    $st = $conn->prepare("UPDATE courses SET course_code = ?, course_name = ?, description = ? WHERE id = ?");
    $st->bind_param('sssi', $code, $name, $desc, $id);
    $st->execute();
    $st->close();

    $changes = [];
    if ($old['course_code'] !== $code) $changes[] = "code {$old['course_code']} → $code";
    if ($old['course_name'] !== $name) $changes[] = "name \"{$old['course_name']}\" → \"$name\"";
    logAdminActivity($conn, $actorId, 'update_subject', 'course', $id, $code . ($changes ? ' · ' . implode(', ', $changes) : ' · details updated'));
    subjSyncEvent($conn, $id, $name);
    subjOut(true, 'Subject updated.', ['id' => $id]);
}

// ── Add ─────────────────────────────────────────────────────
// School year and semester: the current ones in Admin -> Settings
$term       = elmsSettings($conn);
$schoolYear = $term['school_year'];
$semester   = $term['semester'];
$st = $conn->prepare("INSERT INTO courses (course_code, course_name, description, school_year, semester, status) VALUES (?, ?, ?, ?, ?, 'Active')");
$st->bind_param('sssss', $code, $name, $desc, $schoolYear, $semester);
$st->execute();
$newId = (int) $conn->insert_id;
$st->close();
if ($newId <= 0) subjOut(false, 'The subject could not be saved. Please try again.');

logAdminActivity($conn, $actorId, 'create_subject', 'course', $newId, "$code · $name");
subjSyncEvent($conn, $newId, $name);
subjOut(true, 'Subject added (' . $code . ').', ['id' => $newId, 'code' => $code]);
