<?php
// ============================================================
//  Arandia College eLMS — Awards & Recognition API
//  File: api/awards.php
//
//  Teacher actions : give, update, revoke
//  Admin actions   : type_save, type_status   (needs manage_awards)
//  Students have no write access here — every student request is
//  rejected. Students read their awards through server-rendered pages
//  that always use the logged-in student's own id.
//
//  Nothing in the request is trusted for identity: the actor always
//  comes from $_SESSION, and every id in the body is re-validated and
//  re-authorized against the database before anything is written.
// ============================================================

session_start();
require_once '../config/conn.php';
require_once '../config/permissions.php';
require_once '../includes/awards.php';

header('Content-Type: application/json');
header('Cache-Control: no-store');

// Throw on DB errors inside this endpoint so every failure lands in the
// catch-all below (logged server-side, generic message to the user).
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

function aw_out(array $payload, int $code = 200): void
{
    http_response_code($code);
    echo json_encode($payload);
    exit;
}
function aw_fail(string $msg, int $code = 400): void
{
    aw_out(['success' => false, 'message' => $msg], $code);
}
function aw_pos_int($v): int
{
    $n = filter_var($v, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    return $n === false ? 0 : (int) $n;
}
function aw_len(string $s): int
{
    return function_exists('mb_strlen') ? mb_strlen($s) : strlen($s);
}

const AW_NOT_AUTHORIZED = 'You are not authorized to perform this action.';
const AW_SAVE_FAILED = 'Unable to save the award. Please try again.';

// ── Auth ────────────────────────────────────────────────────────────────
if (!isset($_SESSION['user_id'], $_SESSION['role'])) {
    aw_fail('Your session has expired. Please sign in again.', 401);
}
$actorId = (int) $_SESSION['user_id'];
$role = (string) $_SESSION['role'];
$action = (string) ($_GET['action'] ?? '');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    aw_fail('Method not allowed.', 405);
}

// CSRF — the token is printed into the awards pages and sent back as a header.
$sentToken = (string) ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
if (empty($_SESSION['awards_csrf']) || !hash_equals((string) $_SESSION['awards_csrf'], $sentToken)) {
    aw_fail('Your session has expired. Please refresh the page and try again.', 419);
}

if (!awards_tables_ready($conn)) {
    aw_fail('The awards feature is not set up yet. Please ask the administrator to run database/migration_awards.sql.', 503);
}

$body = json_decode(file_get_contents('php://input') ?: '', true);
if (!is_array($body)) $body = [];

$teacherActions = ['give', 'update', 'revoke'];
$adminActions = ['type_save', 'type_status'];

if (in_array($action, $teacherActions, true) && $role !== 'Teacher') {
    aw_fail(AW_NOT_AUTHORIZED, 403);
}
if (in_array($action, $adminActions, true)) {
    if (!in_array($role, ['Admin', 'Head Admin'], true)) aw_fail(AW_NOT_AUTHORIZED, 403);
    if (!adminHasPermission($conn, 'manage_awards')) aw_fail(AW_NOT_AUTHORIZED, 403);
}
if (!in_array($action, array_merge($teacherActions, $adminActions), true)) {
    aw_fail('Unknown action.', 400);
}

try {

    // ════════════════════════════════════════════════════════════════════
    //  TEACHER: give an award
    // ════════════════════════════════════════════════════════════════════
    if ($action === 'give') {
        $studentId = aw_pos_int($body['student_id'] ?? null);
        $courseId = aw_pos_int($body['course_id'] ?? null);
        $typeId = aw_pos_int($body['award_type_id'] ?? null);
        $reason = trim((string) ($body['reason'] ?? ''));
        $token = (string) ($body['request_token'] ?? '');

        if (!$studentId) aw_fail('Please select a student.');
        if (!$courseId) aw_fail('Please select a subject.');
        if (!$typeId) aw_fail('Please select an award.');
        if ($reason === '') aw_fail('Please enter a reason for this award.');
        if (aw_len($reason) < 5) aw_fail('The reason is too short. Please describe why the student earned this award.');
        if (aw_len($reason) > 500) aw_fail('The reason must be 500 characters or fewer.');
        if (!preg_match('/^[A-Za-z0-9-]{16,64}$/', $token)) aw_fail('Please refresh the page and try again.');

        // Same form submitted twice (double-click, retry) → return the first result.
        $prev = awards_select($conn, "SELECT id, teacher_id FROM student_awards WHERE request_token = ? LIMIT 1", 's', [$token]);
        if ($prev) {
            if ((int) $prev[0]['teacher_id'] !== $actorId) aw_fail(AW_NOT_AUTHORIZED, 403);
            aw_out(['success' => true, 'duplicate' => true, 'id' => (int) $prev[0]['id'], 'message' => 'This award was already saved.']);
        }

        $stu = awards_select($conn, "SELECT id FROM users WHERE id = ? AND role = 'Student' LIMIT 1", 'i', [$studentId]);
        if (!$stu) aw_fail('Student not found.', 404);

        $crs = awards_select($conn, "SELECT id FROM courses WHERE id = ? LIMIT 1", 'i', [$courseId]);
        if (!$crs) aw_fail('Invalid subject selected.', 404);

        $type = awards_select($conn, "SELECT id FROM award_types WHERE id = ? AND status = 'Active' LIMIT 1", 'i', [$typeId]);
        if (!$type) aw_fail('Invalid award selected.');

        if (!awards_teacher_can_award($conn, $actorId, $studentId, $courseId)) {
            aw_fail(AW_NOT_AUTHORIZED, 403);
        }

        // Guard against an accidental second submission of a fresh form.
        $recent = awards_select(
            $conn,
            "SELECT id FROM student_awards
             WHERE teacher_id = ? AND student_id = ? AND course_id = ? AND award_type_id = ?
               AND status = 'Active' AND awarded_at >= ?
             LIMIT 1",
            'iiiis',
            [$actorId, $studentId, $courseId, $typeId, date('Y-m-d H:i:s', time() - 60)]
        );
        if ($recent) {
            aw_fail('This award was just given to this student for the same subject. Please check the list before giving it again.', 409);
        }

        $schoolYear = awards_school_year_for($conn, $actorId, $courseId);

        try {
            $stmt = $conn->prepare(
                "INSERT INTO student_awards (student_id, teacher_id, course_id, award_type_id, reason, school_year, status, awarded_at, request_token)
                 VALUES (?, ?, ?, ?, ?, ?, 'Active', ?, ?)"
            );
            // App timezone (config/conn.php sets Asia/Manila) so the saved date
            // always matches the date shown to teachers and students.
            $awardedAt = date('Y-m-d H:i:s');
            $stmt->bind_param('iiiissss', $studentId, $actorId, $courseId, $typeId, $reason, $schoolYear, $awardedAt, $token);
            $stmt->execute();
            $newId = (int) $conn->insert_id;
            $stmt->close();
        } catch (mysqli_sql_exception $e) {
            if ((int) $e->getCode() === 1062) { // same request_token raced in
                $prev = awards_select($conn, "SELECT id FROM student_awards WHERE request_token = ? AND teacher_id = ? LIMIT 1", 'si', [$token, $actorId]);
                aw_out(['success' => true, 'duplicate' => true, 'id' => (int) ($prev[0]['id'] ?? 0), 'message' => 'This award was already saved.']);
            }
            throw $e;
        }

        aw_out(['success' => true, 'id' => $newId, 'message' => 'Award given successfully.']);
    }

    // ════════════════════════════════════════════════════════════════════
    //  TEACHER: edit an award they gave (award type + reason only;
    //  student and subject stay fixed so the record can't be moved)
    // ════════════════════════════════════════════════════════════════════
    if ($action === 'update') {
        $id = aw_pos_int($body['id'] ?? null);
        $typeId = aw_pos_int($body['award_type_id'] ?? null);
        $reason = trim((string) ($body['reason'] ?? ''));
        if (!$id) aw_fail('Award not found.', 404);
        if (!$typeId) aw_fail('Please select an award.');
        if ($reason === '' || aw_len($reason) < 5) aw_fail('Please enter a reason for this award.');
        if (aw_len($reason) > 500) aw_fail('The reason must be 500 characters or fewer.');

        $row = awards_select($conn, "SELECT id, teacher_id, course_id, award_type_id, status FROM student_awards WHERE id = ? LIMIT 1", 'i', [$id]);
        if (!$row) aw_fail('Award not found.', 404);
        $row = $row[0];
        if ((int) $row['teacher_id'] !== $actorId) aw_fail(AW_NOT_AUTHORIZED, 403);
        if ($row['status'] !== 'Active') aw_fail('A revoked award can no longer be edited.');

        $stillAssigned = awards_select($conn, "SELECT 1 AS ok FROM teacher_assignments WHERE teacher_id = ? AND course_id = ? LIMIT 1", 'ii', [$actorId, (int) $row['course_id']]);
        if (!$stillAssigned) aw_fail(AW_NOT_AUTHORIZED, 403);

        // Keeping the current (possibly now-inactive) type is allowed; switching needs an active one.
        if ($typeId !== (int) $row['award_type_id']) {
            $type = awards_select($conn, "SELECT id FROM award_types WHERE id = ? AND status = 'Active' LIMIT 1", 'i', [$typeId]);
            if (!$type) aw_fail('Invalid award selected.');
        }

        $stmt = $conn->prepare("UPDATE student_awards SET award_type_id = ?, reason = ? WHERE id = ? AND teacher_id = ? AND status = 'Active'");
        $stmt->bind_param('isii', $typeId, $reason, $id, $actorId);
        $stmt->execute();
        $stmt->close();
        aw_out(['success' => true, 'message' => 'Award updated.']);
    }

    // ════════════════════════════════════════════════════════════════════
    //  TEACHER: revoke (soft) — keeps the record for the audit trail and
    //  removes it from the student's active awards.
    // ════════════════════════════════════════════════════════════════════
    if ($action === 'revoke') {
        $id = aw_pos_int($body['id'] ?? null);
        if (!$id) aw_fail('Award not found.', 404);
        $row = awards_select($conn, "SELECT id, teacher_id, status FROM student_awards WHERE id = ? LIMIT 1", 'i', [$id]);
        if (!$row) aw_fail('Award not found.', 404);
        if ((int) $row[0]['teacher_id'] !== $actorId) aw_fail(AW_NOT_AUTHORIZED, 403);
        if ($row[0]['status'] === 'Revoked') aw_out(['success' => true, 'message' => 'This award was already revoked.']);

        $stmt = $conn->prepare("UPDATE student_awards SET status = 'Revoked', revoked_at = ?, revoked_by = ? WHERE id = ? AND teacher_id = ? AND status = 'Active'");
        $revokedAt = date('Y-m-d H:i:s');
        $stmt->bind_param('siii', $revokedAt, $actorId, $id, $actorId);
        $stmt->execute();
        $stmt->close();
        aw_out(['success' => true, 'message' => 'Award revoked.']);
    }

    // ════════════════════════════════════════════════════════════════════
    //  ADMIN: create / edit an award type
    // ════════════════════════════════════════════════════════════════════
    if ($action === 'type_save') {
        $id = aw_pos_int($body['id'] ?? null);
        $name = trim((string) ($body['name'] ?? ''));
        $description = trim((string) ($body['description'] ?? ''));
        $category = trim((string) ($body['category'] ?? ''));
        $icon = (string) ($body['icon'] ?? '');
        $tone = (string) ($body['tone'] ?? '');
        $status = (string) ($body['status'] ?? 'Active');

        if ($name === '' || aw_len($name) < 3) aw_fail('Please enter an award name (at least 3 characters).');
        if (aw_len($name) > 120) aw_fail('The award name must be 120 characters or fewer.');
        if (aw_len($description) > 500) aw_fail('The description must be 500 characters or fewer.');
        if ($category === '') aw_fail('Please choose a category.');
        if (aw_len($category) > 60) aw_fail('The category must be 60 characters or fewer.');
        if (!array_key_exists($icon, awards_icon_paths())) aw_fail('Please choose a valid icon.');
        if (!array_key_exists($tone, awards_tones())) aw_fail('Please choose a valid colour.');
        if (!in_array($status, ['Active', 'Inactive'], true)) aw_fail('Invalid status.');
        $descOrNull = $description !== '' ? $description : null;

        try {
            if ($id) {
                $exists = awards_select($conn, "SELECT id FROM award_types WHERE id = ? LIMIT 1", 'i', [$id]);
                if (!$exists) aw_fail('Award type not found.', 404);
                $stmt = $conn->prepare("UPDATE award_types SET name = ?, description = ?, category = ?, icon = ?, tone = ?, status = ? WHERE id = ?");
                $stmt->bind_param('ssssssi', $name, $descOrNull, $category, $icon, $tone, $status, $id);
                $stmt->execute();
                $stmt->close();
                logAdminActivity($conn, $actorId, 'update_award_type', 'award_type', $id, $name);
                aw_out(['success' => true, 'id' => $id, 'message' => 'Award type updated.']);
            }
            $stmt = $conn->prepare("INSERT INTO award_types (name, description, category, icon, tone, status, created_by) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param('ssssssi', $name, $descOrNull, $category, $icon, $tone, $status, $actorId);
            $stmt->execute();
            $newId = (int) $conn->insert_id;
            $stmt->close();
            logAdminActivity($conn, $actorId, 'create_award_type', 'award_type', $newId, $name);
            aw_out(['success' => true, 'id' => $newId, 'message' => 'Award type added.']);
        } catch (mysqli_sql_exception $e) {
            if ((int) $e->getCode() === 1062) aw_fail('An award type with this name already exists.', 409);
            throw $e;
        }
    }

    // ════════════════════════════════════════════════════════════════════
    //  ADMIN: activate / deactivate an award type
    // ════════════════════════════════════════════════════════════════════
    if ($action === 'type_status') {
        $id = aw_pos_int($body['id'] ?? null);
        $status = (string) ($body['status'] ?? '');
        if (!$id) aw_fail('Award type not found.', 404);
        if (!in_array($status, ['Active', 'Inactive'], true)) aw_fail('Invalid status.');
        $exists = awards_select($conn, "SELECT id, name FROM award_types WHERE id = ? LIMIT 1", 'i', [$id]);
        if (!$exists) aw_fail('Award type not found.', 404);
        $stmt = $conn->prepare("UPDATE award_types SET status = ? WHERE id = ?");
        $stmt->bind_param('si', $status, $id);
        $stmt->execute();
        $stmt->close();
        logAdminActivity($conn, $actorId, 'update_award_type_status', 'award_type', $id, $exists[0]['name'] . ' set to ' . $status);
        aw_out(['success' => true, 'message' => $status === 'Active' ? 'Award type activated.' : 'Award type deactivated.']);
    }

} catch (Throwable $e) {
    error_log('[api/awards.php] ' . $action . ' failed: ' . $e->getMessage());
    aw_fail($action === 'give' || $action === 'update' || $action === 'revoke' ? AW_SAVE_FAILED : 'Unable to save your changes. Please try again.', 500);
}

aw_fail('Unknown action.', 400);
