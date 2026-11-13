<?php
// ============================================================
//  Arandia College eLMS — Teacher Assignments API
//  File: api/teacher_assignments.php
// ============================================================
session_start();
require_once '../config/conn.php';
require_once '../config/permissions.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['Admin', 'Head Admin'], true)) {
    http_response_code(empty($_SESSION['user_id']) ? 401 : 403); echo json_encode(['success' => false, 'message' => empty($_SESSION['user_id']) ? 'Your session has ended. Please sign in again.' : 'You are not allowed to do this.']);
    exit;
}

$action = $_GET['action'] ?? '';
$actorId = (int) $_SESSION['user_id'];

if (in_array($action, ['assign', 'remove'], true)) {
    requireApiPermission($conn, 'assign_teachers');
} else {
    requireApiPermission($conn, ['assign_teachers', 'view_subjects', 'view_teachers', 'view_classes', 'manage_classes']);
}

// ── GET: list all assignments (optionally filtered by teacher) ────────────
if ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'list') {
    $teacher_id = isset($_GET['teacher_id']) ? (int) $_GET['teacher_id'] : 0;
    $where = $teacher_id ? "WHERE ta.teacher_id = $teacher_id" : '';
    $rows = [];
    $res = $conn->query(
        "SELECT ta.id, ta.teacher_id, ta.course_id, ta.section, ta.school_year, ta.semester,
                CONCAT(u.last_name, ', ', u.first_name) AS teacher_name,
                c.course_code, c.course_name
         FROM teacher_assignments ta
         JOIN users u ON u.id = ta.teacher_id
         JOIN courses c ON c.id = ta.course_id
         $where
         ORDER BY u.last_name, c.course_name, ta.section"
    );
    while ($r = $res->fetch_assoc())
        $rows[] = $r;
    echo json_encode(['success' => true, 'data' => $rows]);
    exit;
}

// ── POST: assign ──────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'assign') {
    $body = json_decode(file_get_contents('php://input'), true);
    $teacher_id = (int) ($body['teacher_id'] ?? 0);
    $course_id = (int) ($body['course_id'] ?? 0);
    $section = trim($body['section'] ?? '');
    // Not sent: the school year / semester set in Admin → Settings
    require_once __DIR__ . '/../includes/settings.php';
    $school_year = trim((string) ($body['school_year'] ?? '')) ?: elmsSetting('school_year');
    $semester = trim((string) ($body['semester'] ?? '')) ?: elmsSetting('semester');

    if (!$teacher_id || !$course_id || !$section) {
        echo json_encode(['success' => false, 'message' => 'Teacher, subject, and section are required.']);
        exit;
    }
    // The section must exist (teacher_assignments.section is linked to sections.name)
    if ($conn->query("SHOW TABLES LIKE 'sections'")->num_rows > 0) {
        $sc = $conn->prepare("SELECT 1 FROM sections WHERE name = ? LIMIT 1");
        $sc->bind_param('s', $section);
        $sc->execute();
        $secOk = (bool) $sc->get_result()->fetch_row();
        $sc->close();
        if (!$secOk) {
            echo json_encode(['success' => false, 'message' => 'Section not found. Add it on the Sections page first.']);
            exit;
        }
    }
    // Verify teacher exists and is actually a Teacher
    $chk = $conn->prepare("SELECT id FROM users WHERE id = ? AND role = 'Teacher'");
    $chk->bind_param('i', $teacher_id);
    $chk->execute();
    if (!$chk->get_result()->fetch_assoc()) {
        echo json_encode(['success' => false, 'message' => 'Invalid teacher.']);
        exit;
    }
    // Check duplicate
    $dup = $conn->prepare(
        "SELECT id FROM teacher_assignments
         WHERE teacher_id=? AND course_id=? AND section=? AND school_year=? AND semester=?"
    );
    $dup->bind_param('iisss', $teacher_id, $course_id, $section, $school_year, $semester);
    $dup->execute();
    if ($dup->get_result()->fetch_assoc()) {
        echo json_encode(['success' => false, 'message' => 'This assignment already exists.']);
        exit;
    }
    $stmt = $conn->prepare(
        "INSERT INTO teacher_assignments (teacher_id, course_id, section, school_year, semester)
         VALUES (?, ?, ?, ?, ?)"
    );
    $stmt->bind_param('iisss', $teacher_id, $course_id, $section, $school_year, $semester);
    if ($stmt->execute()) {
        logAdminActivity($conn, $actorId, 'assign_teacher', 'teacher_assignment', $conn->insert_id, "Assigned teacher #$teacher_id to course #$course_id ($section).");
        echo json_encode(['success' => true, 'message' => 'Assignment saved.']);
    } else {
        error_log('[teacher_assignments.php] ' . $conn->error); echo json_encode(['success' => false, 'message' => 'Could not save. Please try again.']);
    }
    exit;
}

// ── DELETE: remove assignment ─────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'DELETE' && $action === 'remove') {
    $id = (int) ($_GET['id'] ?? 0);
    if (!$id) {
        echo json_encode(['success' => false, 'message' => 'Invalid ID.']);
        exit;
    }
    $stmt = $conn->prepare("DELETE FROM teacher_assignments WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $ok = $stmt->affected_rows > 0;
    if ($ok) logAdminActivity($conn, $actorId, 'remove_teacher_assignment', 'teacher_assignment', $id, null);
    echo json_encode(['success' => $ok, 'message' => $ok ? 'Removed.' : 'Not found.']);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid request.']);