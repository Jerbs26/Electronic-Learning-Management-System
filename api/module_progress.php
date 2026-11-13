<?php
// ============================================================
//  Arandia College eLMS — Module Progress API
//  File: api/module_progress.php
// ============================================================
session_start();
require_once '../config/conn.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(empty($_SESSION['user_id']) ? 401 : 403); echo json_encode(['success' => false, 'message' => empty($_SESSION['user_id']) ? 'Your session has ended. Please sign in again.' : 'You are not allowed to do this.']);
    exit;
}

$user_id = (int) $_SESSION['user_id'];
$role = $_SESSION['role'];
$action = $_GET['action'] ?? '';

// ── POST: open a module (students) ────────────────────────────────────────
// Returns the lesson and records that the student opened it ("Opened").
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'open') {
    if ($role !== 'Student') {
        echo json_encode(['success' => false, 'message' => 'Students only.']);
        exit;
    }
    $body = json_decode(file_get_contents('php://input'), true);
    $module_id = (int) ($body['module_id'] ?? 0);

    $st = $conn->prepare(
        "SELECT m.id, m.course_id, m.module_number, m.title, m.description, m.content, m.file_path,
                c.course_name, c.course_code
         FROM modules m
         JOIN courses c ON c.id = m.course_id
         JOIN enrollments e ON e.course_id = m.course_id AND e.student_id = ? AND e.status = 'Enrolled'
         WHERE m.id = ? LIMIT 1"
    );
    $st->bind_param('ii', $user_id, $module_id);
    $st->execute();
    $m = $st->get_result()->fetch_assoc();
    $st->close();
    if (!$m) {
        echo json_encode(['success' => false, 'message' => 'You are not enrolled in this subject.']);
        exit;
    }

    $hasViews = $conn->query("SHOW TABLES LIKE 'module_views'");
    if ($hasViews && $hasViews->num_rows) {
        $st = $conn->prepare(
            "INSERT INTO module_views (student_id, module_id) VALUES (?, ?)
             ON DUPLICATE KEY UPDATE last_opened_at = NOW(), open_count = open_count + 1"
        );
        $st->bind_param('ii', $user_id, $module_id);
        $st->execute();
        $st->close();
    }

    $st = $conn->prepare("SELECT DATE_FORMAT(completed_at, '%b %d, %Y') AS d FROM module_progress WHERE student_id = ? AND module_id = ?");
    $st->bind_param('ii', $user_id, $module_id);
    $st->execute();
    $done = $st->get_result()->fetch_assoc();
    $st->close();

    $cid = (int) $m['course_id'];
    $num = (int) $m['module_number'];
    $st = $conn->prepare("SELECT id FROM modules WHERE course_id = ? AND module_number > ? ORDER BY module_number LIMIT 1");
    $st->bind_param('ii', $cid, $num);
    $st->execute();
    $next = $st->get_result()->fetch_assoc();
    $st->close();

    echo json_encode(['success' => true, 'data' => [
        'id' => (int) $m['id'], 'course_id' => $cid, 'number' => $num,
        'title' => $m['title'], 'description' => $m['description'],
        'content' => $m['content'] ?? '', 'file_path' => $m['file_path'],
        'course_name' => $m['course_name'], 'course_code' => $m['course_code'],
        'done_on' => $done['d'] ?? null, 'next_id' => $next ? (int) $next['id'] : null,
    ]]);
    exit;
}

// ── POST: mark module as done ─────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'mark') {
    if ($role !== 'Student') {
        echo json_encode(['success' => false, 'message' => 'Students only.']);
        exit;
    }
    $body = json_decode(file_get_contents('php://input'), true);
    $module_id = (int) ($body['module_id'] ?? 0);

    if (!$module_id) {
        echo json_encode(['success' => false, 'message' => 'Module required.']);
        exit;
    }

    // The subject comes from the module itself (never from the request), and the
    // student must be Enrolled in it and the module published.
    $chk = $conn->query(
        "SELECT m.course_id FROM modules m
         JOIN enrollments e ON e.course_id = m.course_id AND e.student_id = $user_id AND e.status = 'Enrolled'
         WHERE m.id = $module_id AND m.published = 1 LIMIT 1"
    )->fetch_assoc();
    if (!$chk) {
        echo json_encode(['success' => false, 'message' => 'Not enrolled in this course.']);
        exit;
    }
    $course_id = (int) $chk['course_id'];

    // Insert or ignore if already done
    $conn->query(
        "INSERT IGNORE INTO module_progress (student_id, module_id, course_id)
         VALUES ($user_id, $module_id, $course_id)"
    );
    echo json_encode(['success' => true, 'message' => 'Module marked as done!']);
    exit;
}

// ── POST: unmark module ───────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'unmark') {
    if ($role !== 'Student') {
        echo json_encode(['success' => false, 'message' => 'Students only.']);
        exit;
    }
    $body = json_decode(file_get_contents('php://input'), true);
    $module_id = (int) ($body['module_id'] ?? 0);
    $conn->query("DELETE FROM module_progress WHERE student_id=$user_id AND module_id=$module_id");
    echo json_encode(['success' => true, 'message' => 'Unmarked.']);
    exit;
}

// ── GET: get progress for a student (per course) ──────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'my_progress') {
    $course_id = (int) ($_GET['course_id'] ?? 0);
    $rows = [];
    $where = $course_id ? "AND mp.course_id = $course_id" : '';
    $res = $conn->query(
        "SELECT mp.module_id, mp.course_id,
                DATE_FORMAT(mp.completed_at,'%b %d, %Y') AS completed_at
         FROM module_progress mp
         WHERE mp.student_id = $user_id $where"
    );
    while ($r = $res->fetch_assoc())
        $rows[$r['module_id']] = $r;
    echo json_encode(['success' => true, 'data' => $rows]);
    exit;
}

// ── GET: teacher view — progress per course ───────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'course_progress') {
    if ($role !== 'Teacher') {
        echo json_encode(['success' => false, 'message' => 'Teachers only.']);
        exit;
    }
    $course_id = (int) ($_GET['course_id'] ?? 0);
    if (!$course_id) {
        echo json_encode(['success' => false, 'message' => 'Course required.']);
        exit;
    }
    $own = $conn->prepare("SELECT 1 FROM teacher_assignments WHERE teacher_id = ? AND course_id = ? LIMIT 1");
    $own->bind_param('ii', $user_id, $course_id);
    $own->execute();
    if (!$own->get_result()->fetch_row()) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Not assigned to this course.']);
        exit;
    }

    // Total published modules in course
    $total = (int) $conn->query("SELECT COUNT(*) AS c FROM modules WHERE course_id=$course_id AND published=1")->fetch_assoc()['c'];

    // Per-student progress
    $rows = [];
    $res = $conn->query(
        "SELECT u.id, u.school_id, u.first_name, u.last_name, u.section_dept,
                COUNT(mp.id) AS done_count
         FROM enrollments e
         JOIN users u ON u.id = e.student_id
         JOIN teacher_assignments ta ON ta.course_id = e.course_id AND ta.section = u.section_dept AND ta.teacher_id = $user_id
         LEFT JOIN module_progress mp ON mp.student_id = u.id AND mp.course_id = $course_id
         WHERE e.course_id = $course_id AND e.status = 'Enrolled' AND u.role = 'Student' AND u.status = 'Active'
         GROUP BY u.id
         ORDER BY u.last_name, u.first_name"
    );
    while ($r = $res->fetch_assoc()) {
        $r['total'] = $total;
        $r['percent'] = $total > 0 ? round(($r['done_count'] / $total) * 100) : 0;
        $rows[] = $r;
    }
    echo json_encode(['success' => true, 'data' => $rows, 'total_modules' => $total]);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid request.']);