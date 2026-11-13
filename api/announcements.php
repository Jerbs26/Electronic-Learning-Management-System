<?php
// ============================================================
//  Arandia College eLMS — Announcements API
//  File: api/announcements.php
// ============================================================

// Catch ALL errors and return them as JSON (never let PHP output raw HTML)
error_reporting(E_ALL);
ini_set('display_errors', 0);
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => "PHP Error [$errno]: $errstr in $errfile on line $errline"
    ]);
    exit;
});
set_exception_handler(function($e) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => "Exception: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine()
    ]);
    exit;
});

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../config/conn.php';
require_once '../config/permissions.php';
require_once '../includes/scope.php';

header('Content-Type: application/json');

// ── Auth guard ────────────────────────────────────────────────────────────
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Your session has ended. Please sign in again.']);
    exit;
}

$user_id = (int) $_SESSION['user_id'];
$role    = $_SESSION['role'] ?? '';
$action  = $_GET['action'] ?? '';

// ── Helper ────────────────────────────────────────────────────────────────
function respond(bool $ok, $data = null, string $msg = ''): void
{
    if ($ok) {
        $payload = ['success' => true];
        if ($data !== null) {
            is_int($data) ? $payload['id'] = $data : $payload['data'] = $data;
        }
        if ($msg) $payload['message'] = $msg;
    } else {
        $payload = ['success' => false, 'message' => $msg ?: 'An error occurred.'];
    }
    echo json_encode($payload);
    exit;
}

// ── Route ─────────────────────────────────────────────────────────────────
if      ($action === 'create') handleCreate();
elseif  ($action === 'list')   handleList();
elseif  ($action === 'update') handleUpdate();
elseif  ($action === 'delete') handleDelete();
else    respond(false, null, 'Unknown action.');

// ── CREATE ────────────────────────────────────────────────────────────────
function handleCreate(): void
{
    global $conn, $user_id, $role;

    if (!in_array($role, ['Teacher', 'Admin', 'Head Admin'])) {
        respond(false, null, 'Only teachers and admins can post announcements.');
    }
    if ($role === 'Admin' && !adminHasPermission($conn, 'publish_announcements')) {
        respond(false, null, 'You do not have permission to publish announcements.');
    }

    // The Admin composer sends multipart/form-data (so it can attach an
    // optional image); the Teacher composer still sends plain JSON. Support both.
    $isMultipart = str_starts_with($_SERVER['CONTENT_TYPE'] ?? '', 'multipart/form-data');
    if ($isMultipart) {
        $body = $_POST;
    } else {
        $raw  = file_get_contents('php://input');
        $body = json_decode($raw, true);
        if (!is_array($body)) {
            respond(false, null, 'The request could not be read. Please try again.');
        }
    }

    $course_id_raw = $body['course_id'] ?? null;
    $course_id     = ($course_id_raw !== null && $course_id_raw !== '' && (int)$course_id_raw > 0)
                     ? (int) $course_id_raw
                     : null;

    $title   = trim($body['title'] ?? '');
    $message = trim($body['body']  ?? '');
    $audience = in_array($body['target_audience'] ?? '', ['Everyone', 'Students', 'Teachers'], true)
                ? $body['target_audience'] : 'Everyone';

    if ($title   === '') respond(false, null, 'Title is required.');
    if ($message === '') respond(false, null, 'Message body is required.');
    if (mb_strlen($title) > 200) respond(false, null, 'Title is too long (200 characters max).');
    if (mb_strlen($message) > 5000) respond(false, null, 'Message is too long (5,000 characters max).');

    // Teachers post to the students of a subject they teach — never school-wide,
    // and always to Everyone in that subject (students + the teacher).
    if ($role === 'Teacher') {
        if ($course_id === null) respond(false, null, 'Choose one of your subjects.');
        $audience = 'Everyone';
    }

    // Same post sent twice (double click / retry) within 30 seconds → keep the first
    $dupSql = "SELECT id FROM announcements WHERE author_id = ? AND title = ? AND body = ? AND posted_at >= NOW() - INTERVAL 30 SECOND AND "
            . ($course_id === null ? 'course_id IS NULL' : 'course_id = ' . (int) $course_id) . " LIMIT 1";
    $dup = $conn->prepare($dupSql);
    $dup->bind_param('iss', $user_id, $title, $message);
    $dup->execute();
    if ($d = $dup->get_result()->fetch_assoc()) respond(true, (int) $d['id'], 'Announcement already posted.');

    // Teachers must be assigned to the course
    if ($role === 'Teacher' && $course_id !== null) {
        $chk = $conn->prepare(
            "SELECT id FROM teacher_assignments WHERE teacher_id = ? AND course_id = ? LIMIT 1"
        );
        if (!$chk) { error_log('[announcements] ' . $conn->error); respond(false, null, 'Could not save. Please try again.'); };
        $chk->bind_param('ii', $user_id, $course_id);
        $chk->execute();
        $found = $chk->get_result()->fetch_assoc();
        $chk->close();
        if (!$found) respond(false, null, 'You are not assigned to that subject.');
    }

    // Use NULL literal in SQL when course_id is null to avoid bind_param type issues
    if ($course_id === null) {
        $stmt = $conn->prepare(
            "INSERT INTO announcements (author_id, course_id, title, body, target_audience, posted_at)
             VALUES (?, NULL, ?, ?, ?, NOW())"
        );
        if (!$stmt) { error_log('[announcements] ' . $conn->error); respond(false, null, 'Could not save. Please try again.'); };
        $stmt->bind_param('isss', $user_id, $title, $message, $audience);
    } else {
        $stmt = $conn->prepare(
            "INSERT INTO announcements (author_id, course_id, title, body, target_audience, posted_at)
             VALUES (?, ?, ?, ?, ?, NOW())"
        );
        if (!$stmt) { error_log('[announcements] ' . $conn->error); respond(false, null, 'Could not save. Please try again.'); };
        $stmt->bind_param('iisss', $user_id, $course_id, $title, $message, $audience);
    }

    if (!$stmt->execute()) {
        $err = $stmt->error;
        $stmt->close();
        { error_log('[announcements] ' . $err); respond(false, null, 'Could not save. Please try again.'); };
    }

    $new_id = (int) $conn->insert_id;
    $stmt->close();

    if (in_array($role, ['Admin', 'Head Admin'], true)) {
        logAdminActivity($conn, $user_id, 'publish_announcement', 'announcement', $new_id, "Posted '$title' to $audience.");
    }

    respond(true, $new_id, 'Announcement posted successfully.');
}

// ── UPDATE (edit a posted announcement) ─────────────────────────────────
function handleUpdate(): void
{
    global $conn, $user_id, $role;

    if (!in_array($role, ['Teacher', 'Admin', 'Head Admin'], true)) {
        respond(false, null, 'Only teachers and admins can edit announcements.');
    }
    if ($role === 'Admin' && !adminHasPermission($conn, 'publish_announcements')) {
        respond(false, null, 'You do not have permission to edit announcements.');
    }

    $body = json_decode(file_get_contents('php://input'), true);
    if (!is_array($body)) {
        respond(false, null, 'The request could not be read. Please try again.');
    }

    $id      = (int) ($body['id'] ?? 0);
    $title   = is_string($body['title'] ?? null) ? trim($body['title']) : '';
    $message = is_string($body['body'] ?? null) ? trim($body['body']) : '';

    if ($id <= 0)      respond(false, null, 'Invalid announcement ID.');
    if ($title === '')   respond(false, null, 'Title is required.');
    if ($message === '') respond(false, null, 'Message body is required.');
    if (mb_strlen($title) > 200) respond(false, null, 'Title is too long (200 characters max).');
    if (mb_strlen($message) > 5000) respond(false, null, 'Message is too long (5,000 characters max).');

    // The post must still exist; teachers can only edit their own
    $find = $conn->prepare(
        "SELECT a.author_id, a.course_id, a.target_audience, u.role AS author_role
         FROM announcements a JOIN users u ON u.id = a.author_id
         WHERE a.id = ? LIMIT 1"
    );
    if (!$find) { error_log('[announcements] ' . $conn->error); respond(false, null, 'Could not save. Please try again.'); }
    $find->bind_param('i', $id);
    $find->execute();
    $row = $find->get_result()->fetch_assoc();
    $find->close();
    if (!$row) respond(false, null, 'This announcement no longer exists. It may have been deleted.');
    if ($role === 'Teacher' && (int) $row['author_id'] !== $user_id) {
        respond(false, null, 'You can only edit your own announcements.');
    }

    $oldAudience = in_array($row['target_audience'] ?? '', ['Everyone', 'Students', 'Teachers'], true) ? $row['target_audience'] : 'Everyone';

    // A teacher's post (edited by the teacher or by an admin) only changes its text —
    // the subject and audience are what make sure the right students see it.
    $textOnly = ($role === 'Teacher' || ($row['author_role'] ?? '') === 'Teacher');

    if ($textOnly) {
        $audience = $oldAudience;
        $stmt = $conn->prepare("UPDATE announcements SET title = ?, body = ? WHERE id = ?");
        if (!$stmt) { error_log('[announcements] ' . $conn->error); respond(false, null, 'Could not save. Please try again.'); }
        $stmt->bind_param('ssi', $title, $message, $id);
    } else {
        // Admin / Head Admin editing an admin post: subject and audience can change too
        if (array_key_exists('course_id', $body)) {
            $raw = $body['course_id'];
            $course_id = (is_scalar($raw) && $raw !== '' && (int) $raw > 0) ? (int) $raw : null;
        } else {
            $course_id = $row['course_id'] !== null ? (int) $row['course_id'] : null;
        }
        $audience = in_array($body['target_audience'] ?? '', ['Everyone', 'Students', 'Teachers'], true)
                    ? $body['target_audience'] : $oldAudience;

        if ($course_id !== null && $course_id !== (int) ($row['course_id'] ?? 0)) {
            $cchk = $conn->prepare("SELECT id FROM courses WHERE id = ? LIMIT 1");
            if (!$cchk) { error_log('[announcements] ' . $conn->error); respond(false, null, 'Could not save. Please try again.'); }
            $cchk->bind_param('i', $course_id);
            $cchk->execute();
            $cfound = $cchk->get_result()->fetch_assoc();
            $cchk->close();
            if (!$cfound) respond(false, null, 'That subject no longer exists.');
        }

        // NULL literal when school-wide (avoids bind_param type issues, same as create)
        if ($course_id === null) {
            $stmt = $conn->prepare("UPDATE announcements SET title = ?, body = ?, course_id = NULL, target_audience = ? WHERE id = ?");
            if (!$stmt) { error_log('[announcements] ' . $conn->error); respond(false, null, 'Could not save. Please try again.'); }
            $stmt->bind_param('sssi', $title, $message, $audience, $id);
        } else {
            $stmt = $conn->prepare("UPDATE announcements SET title = ?, body = ?, course_id = ?, target_audience = ? WHERE id = ?");
            if (!$stmt) { error_log('[announcements] ' . $conn->error); respond(false, null, 'Could not save. Please try again.'); }
            $stmt->bind_param('ssisi', $title, $message, $course_id, $audience, $id);
        }
    }

    // Note: MySQL reports 0 affected rows when nothing actually changed — that is still a success.
    if (!$stmt->execute()) {
        $err = $stmt->error;
        $stmt->close();
        error_log('[announcements] ' . $err);
        respond(false, null, 'Could not save. Please try again.');
    }
    $stmt->close();

    if (in_array($role, ['Admin', 'Head Admin'], true)) {
        logAdminActivity($conn, $user_id, 'update_announcement', 'announcement', $id, "Edited '$title' ($audience).");
    }

    respond(true, $id, 'Announcement updated.');
}

// ── LIST ──────────────────────────────────────────────────────────────────
function handleList(): void
{
    global $conn, $user_id, $role;

    $course_id = isset($_GET['course_id']) && (int)$_GET['course_id'] > 0
                 ? (int) $_GET['course_id']
                 : null;

    $where = '';

    if (in_array($role, ['Admin', 'Head Admin'], true)) {
        $where = $course_id ? "WHERE a.course_id = $course_id" : '';
    } elseif ($role === 'Teacher') {
        // Teachers see their own posts + Admin announcements targeted at Everyone/Teachers
        $audienceClause = "u.role IN ('Admin','Head Admin') AND a.target_audience IN ('Everyone','Teachers')";
        $where = $course_id
            ? "WHERE a.course_id = $course_id AND (a.author_id = $user_id OR ($audienceClause))"
            : "WHERE (a.author_id = $user_id OR ($audienceClause))";
    } elseif ($role === 'Student') {
        // Same rule as the student pages (includes/scope.php)
        $where = "WHERE " . scopeStudentAnnouncements('a', 'u', $user_id) . ($course_id ? " AND a.course_id = $course_id" : '');
    } else {
        respond(false, null, 'You are not allowed to read announcements here.');
    }

    $sql = "SELECT a.id, a.title, a.body, a.course_id, a.target_audience,
                DATE_FORMAT(a.posted_at,'%b %d, %Y %h:%i %p') AS posted_at,
                CONCAT(u.first_name,' ',u.last_name) AS author,
                u.role AS author_role, c.course_code, c.course_name
            FROM announcements a
            JOIN users u ON u.id = a.author_id
            LEFT JOIN courses c ON c.id = a.course_id
            $where
            ORDER BY a.posted_at DESC LIMIT 50";

    $res = $conn->query($sql);
    if (!$res) { error_log('[announcements list] ' . $conn->error); respond(false, null, 'Could not load announcements. Please try again.'); }

    $rows = [];
    while ($r = $res->fetch_assoc()) $rows[] = $r;

    respond(true, $rows);
}

// ── DELETE ────────────────────────────────────────────────────────────────
function handleDelete(): void
{
    global $conn, $user_id, $role;

    $id = (int) ($_GET['id'] ?? 0);
    if ($id <= 0) respond(false, null, 'Invalid announcement ID.');

    if (in_array($role, ['Admin', 'Head Admin'], true)) {
        if ($role === 'Admin' && !adminHasPermission($conn, 'publish_announcements')) {
            respond(false, null, 'You do not have permission to delete announcements.');
        }
        $stmt = $conn->prepare("DELETE FROM announcements WHERE id = ?");
        if (!$stmt) { error_log('[announcements] ' . $conn->error); respond(false, null, 'Could not save. Please try again.'); };
        $stmt->bind_param('i', $id);
    } elseif ($role === 'Teacher') {
        $stmt = $conn->prepare("DELETE FROM announcements WHERE id = ? AND author_id = ?");
        if (!$stmt) { error_log('[announcements] ' . $conn->error); respond(false, null, 'Could not save. Please try again.'); };
        $stmt->bind_param('ii', $id, $user_id);
    } else {
        respond(false, null, 'Permission denied.');
        return;
    }

    $stmt->execute();
    $affected = $stmt->affected_rows;
    $stmt->close();

    if ($affected === 0) respond(false, null, 'Not found or permission denied.');
    if (in_array($role, ['Admin', 'Head Admin'], true)) {
        logAdminActivity($conn, $user_id, 'delete_announcement', 'announcement', $id, null);
    }
    respond(true, null, 'Announcement deleted.');
}