<?php
// ============================================================
//  Arandia College eLMS — Modules API
//  File: api/modules.php
// ============================================================
session_start();
require_once '../config/conn.php';
require_once __DIR__ . '/../includes/upload_names.php';   // keep the original file name

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Teacher') {
    http_response_code(empty($_SESSION['user_id']) ? 401 : 403); echo json_encode(['success' => false, 'message' => empty($_SESSION['user_id']) ? 'Your session has ended. Please sign in again.' : 'You are not allowed to do this.']);
    exit;
}

$teacher_id = (int) $_SESSION['user_id'];
$action = $_GET['action'] ?? '';

// ── Helper: verify teacher owns the course via teacher_assignments ─────────
function teacherOwnsCourse($conn, $teacher_id, $course_id)
{
    $s = $conn->prepare("SELECT id FROM teacher_assignments WHERE teacher_id=? AND course_id=? LIMIT 1");
    $s->bind_param('ii', $teacher_id, $course_id);
    $s->execute();
    return (bool) $s->get_result()->fetch_assoc();
}

// ── GET: list modules for a course ────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'list') {
    $course_id = (int) ($_GET['course_id'] ?? 0);
    if (!$course_id) {
        echo json_encode(['success' => false, 'message' => 'Course required.']);
        exit;
    }
    if (!teacherOwnsCourse($conn, $teacher_id, $course_id)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Not assigned to this course.']);
        exit;
    }
    $rows = [];
    $res = $conn->query(
        "SELECT id, course_id, title, description, file_path, week_number, published,
                DATE_FORMAT(created_at,'%b %d, %Y') AS created_at
         FROM modules WHERE course_id = $course_id ORDER BY week_number, created_at DESC"
    );
    while ($r = $res->fetch_assoc())
        $rows[] = $r;
    echo json_encode(['success' => true, 'data' => $rows]);
    exit;
}

// ── POST: create module (multipart form with optional file) ───────────────
// Modules are now created and edited by the admin (admin/modules.php, api/admin_modules.php).
// Teachers can still list them, but every write action here is closed.
if (in_array($action, ['create', 'update', 'toggle', 'delete'], true)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Modules are managed by the admin.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'create') {
    $course_id = (int) ($_POST['course_id'] ?? 0);
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $week = (int) ($_POST['week_number'] ?? 0) ?: null;
    $published = isset($_POST['published']) ? 1 : 0;

    if (!$course_id || !$title) {
        echo json_encode(['success' => false, 'message' => 'Course and title are required.']);
        exit;
    }
    if (!teacherOwnsCourse($conn, $teacher_id, $course_id)) {
        echo json_encode(['success' => false, 'message' => 'You are not assigned to this course.']);
        exit;
    }

    $file_path = null;
    if (!empty($_FILES['file']['name'])) {
        $upload_dir = '../uploads/modules/';
        if (!is_dir($upload_dir))
            mkdir($upload_dir, 0775, true);
        $err = elmsCheckModuleFile($_FILES['file'], $ext);
        if ($err) {
            echo json_encode(['success' => false, 'message' => $err]);
            exit;
        }
        $filename = elmsUploadName('mod_', $_FILES['file']['name'], $ext);
        $dest = $upload_dir . $filename;
        if (!move_uploaded_file($_FILES['file']['tmp_name'], $dest)) {
            echo json_encode(['success' => false, 'message' => 'File upload failed.']);
            exit;
        }
        $file_path = 'uploads/modules/' . $filename;
    }

    $stmt = $conn->prepare(
        "INSERT INTO modules (course_id, title, description, file_path, week_number, published)
         VALUES (?, ?, ?, ?, ?, ?)"
    );
    $stmt->bind_param('isssii', $course_id, $title, $description, $file_path, $week, $published);
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Module created.', 'id' => $conn->insert_id]);
    } else {
        error_log('[modules.php] ' . $conn->error); echo json_encode(['success' => false, 'message' => 'Could not save. Please try again.']);
    }
    exit;
}

// ── POST: toggle publish ──────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'toggle') {
    $id = (int) ($_POST['id'] ?? 0);
    $conn->query("UPDATE modules SET published = 1 - published WHERE id = $id");
    echo json_encode(['success' => true]);
    exit;
}

// ── DELETE: remove module ─────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'DELETE' && $action === 'delete') {
    $id = (int) ($_GET['id'] ?? 0);
    $r = $conn->query("SELECT file_path FROM modules WHERE id = $id")->fetch_assoc();
    if ($r && $r['file_path'] && file_exists('../' . $r['file_path'])) {
        unlink('../' . $r['file_path']);
    }
    $conn->query("DELETE FROM modules WHERE id = $id");
    echo json_encode(['success' => true, 'message' => 'Module deleted.']);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid request.']);