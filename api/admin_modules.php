<?php
// ============================================================
//  Arandia College eLMS — Admin module management API
//  File: api/admin_modules.php
//  Modules belong to a subject (course) and are shared by every
//  section that takes it. Only admins with Manage Modules (or the
//  Head Admin) can change them. Every module must have lesson text
//  or an attached file, so students always have something to read.
//
//  POST (multipart/form-data):
//    ?action=save    id (0 = new), course_id, title, description, content, file, remove_file
//    ?action=delete  id
//    ?action=move    id, dir (up|down)
// ============================================================
session_start();
require_once '../config/conn.php';
require_once __DIR__ . '/../includes/upload_names.php';   // keep the original file name
require_once '../config/permissions.php';
require_once '../includes/module_content.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['Admin', 'Head Admin'], true)) {
    http_response_code(403);
    http_response_code(empty($_SESSION['user_id']) ? 401 : 403); echo json_encode(['success' => false, 'message' => empty($_SESSION['user_id']) ? 'Your session has ended. Please sign in again.' : 'You are not allowed to do this.']);
    exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Use POST.']);
    exit;
}
requireApiPermission($conn, 'manage_modules');

$actorId = (int) $_SESSION['user_id'];
$action  = $_GET['action'] ?? '';

function out(bool $ok, string $msg, array $extra = []): void
{
    echo json_encode(array_merge(['success' => $ok, 'message' => $msg], $extra));
    exit;
}
function getModule(mysqli $conn, int $id): ?array
{
    $st = $conn->prepare("SELECT m.*, c.course_code FROM modules m JOIN courses c ON c.id = m.course_id WHERE m.id = ?");
    $st->bind_param('i', $id);
    $st->execute();
    $r = $st->get_result()->fetch_assoc();
    $st->close();
    return $r ?: null;
}
function deleteFile(?string $path): void
{
    if ($path && strpos($path, 'uploads/modules/') === 0 && is_file('../' . $path)) unlink('../' . $path);
}

// ── Save (create or update) ─────────────────────────────────
if ($action === 'save') {
    $id          = (int) ($_POST['id'] ?? 0);
    $courseId    = (int) ($_POST['course_id'] ?? 0);
    $title       = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $content     = sanitizeModuleHtml($_POST['content'] ?? '');
    $removeFile  = !empty($_POST['remove_file']);

    $existing = null;
    if ($id) {
        $existing = getModule($conn, $id);
        if (!$existing) out(false, 'Module not found.');
        $courseId = (int) $existing['course_id'];
    } else {
        $st = $conn->prepare("SELECT id FROM courses WHERE id = ?");
        $st->bind_param('i', $courseId);
        $st->execute();
        $ok = $st->get_result()->fetch_assoc();
        $st->close();
        if (!$ok) out(false, 'Subject not found.');
    }
    if ($title === '' || strlen($title) > 200) out(false, 'Enter a module title (up to 200 characters).');
    if (strlen($description) > 500) out(false, 'Keep the short description under 500 characters.');

    // Optional file
    $filePath = $existing['file_path'] ?? null;
    $newFile = null;
    if (!empty($_FILES['file']['name'])) {
        $err = elmsCheckModuleFile($_FILES['file'], $ext);
        if ($err) out(false, $err);
        $dir = '../uploads/modules/';
        if (!is_dir($dir)) mkdir($dir, 0775, true);
        $name = elmsUploadName('mod_', $_FILES['file']['name'], $ext);
        if (!move_uploaded_file($_FILES['file']['tmp_name'], $dir . $name)) out(false, 'The file could not be saved. Try again.');
        $newFile = 'uploads/modules/' . $name;
    }
    $finalFile = $newFile ?? ($removeFile ? null : $filePath);

    if (!moduleHasText($content) && !$finalFile) {
        if ($newFile) deleteFile($newFile);
        out(false, 'Add lesson content or attach a file — students need something to read.');
    }
    $contentOrNull = moduleHasText($content) ? $content : null;

    if ($id) {
        $st = $conn->prepare("UPDATE modules SET title = ?, description = ?, content = ?, file_path = ?, published = 1, updated_by = ? WHERE id = ?");
        $st->bind_param('ssssii', $title, $description, $contentOrNull, $finalFile, $actorId, $id);
        $st->execute();
        $st->close();
        if ($finalFile !== $filePath) deleteFile($filePath);
        logAdminActivity($conn, $actorId, 'update_module', 'module', $id, "{$existing['course_code']} · Module {$existing['module_number']}: $title");
        out(true, 'Module saved.');
    }

    $conn->begin_transaction();
    try {
        $st = $conn->prepare("SELECT COALESCE(MAX(module_number), 0) + 1 AS n FROM modules WHERE course_id = ? FOR UPDATE");
        $st->bind_param('i', $courseId);
        $st->execute();
        $n = (int) $st->get_result()->fetch_assoc()['n'];
        $st->close();
        $st = $conn->prepare("INSERT INTO modules (course_id, module_number, title, description, content, file_path, published, updated_by)
                              VALUES (?, ?, ?, ?, ?, ?, 1, ?)");
        $st->bind_param('iissssi', $courseId, $n, $title, $description, $contentOrNull, $finalFile, $actorId);
        $st->execute();
        $newId = $st->insert_id;
        $st->close();
        $conn->commit();
    } catch (Throwable $e) {
        $conn->rollback();
        if ($newFile) deleteFile($newFile);
        error_log('[admin_modules.php] create: ' . $e->getMessage());
        out(false, 'Could not save the module. Try again.');
    }
    logAdminActivity($conn, $actorId, 'create_module', 'module', $newId, "Course #$courseId · Module $n: $title");
    out(true, "Module $n added.", ['id' => $newId]);
}

// ── Delete (later modules move up one number) ───────────────
if ($action === 'delete') {
    $id = (int) ($_POST['id'] ?? 0);
    $m = getModule($conn, $id);
    if (!$m) out(false, 'Module not found.');
    $conn->begin_transaction();
    try {
        $st = $conn->prepare("DELETE FROM modules WHERE id = ?");
        $st->bind_param('i', $id);
        $st->execute();
        $st->close();
        $cid = (int) $m['course_id'];
        $num = (int) $m['module_number'];
        $st = $conn->prepare("UPDATE modules SET module_number = module_number - 1 WHERE course_id = ? AND module_number > ? ORDER BY module_number ASC");
        $st->bind_param('ii', $cid, $num);
        $st->execute();
        $st->close();
        $conn->commit();
    } catch (Throwable $e) {
        $conn->rollback();
        error_log('[admin_modules.php] delete: ' . $e->getMessage());
        out(false, 'Could not delete the module. Try again.');
    }
    deleteFile($m['file_path']);
    logAdminActivity($conn, $actorId, 'delete_module', 'module', $id, "{$m['course_code']} · Module {$m['module_number']}: {$m['title']}");
    out(true, 'Module deleted.');
}

// ── Move up / down (swap numbers with the neighbour) ────────
if ($action === 'move') {
    $id  = (int) ($_POST['id'] ?? 0);
    $dir = ($_POST['dir'] ?? '') === 'up' ? -1 : 1;
    $m = getModule($conn, $id);
    if (!$m) out(false, 'Module not found.');
    $cid = (int) $m['course_id'];
    $from = (int) $m['module_number'];
    $to = $from + $dir;
    $st = $conn->prepare("SELECT id FROM modules WHERE course_id = ? AND module_number = ?");
    $st->bind_param('ii', $cid, $to);
    $st->execute();
    $other = $st->get_result()->fetch_assoc();
    $st->close();
    if (!$other) out(false, 'This module is already at the ' . ($dir < 0 ? 'top' : 'bottom') . '.');

    $conn->begin_transaction();
    try {
        $tmp = 65535;
        $otherId = (int) $other['id'];
        foreach ([[$tmp, $id], [$from, $otherId], [$to, $id]] as [$num, $mid]) {
            $st = $conn->prepare("UPDATE modules SET module_number = ? WHERE id = ?");
            $st->bind_param('ii', $num, $mid);
            $st->execute();
            $st->close();
        }
        $conn->commit();
    } catch (Throwable $e) {
        $conn->rollback();
        error_log('[admin_modules.php] move: ' . $e->getMessage());
        out(false, 'Could not move the module. Try again.');
    }
    logAdminActivity($conn, $actorId, 'reorder_module', 'module', $id, "{$m['course_code']}: Module $from → $to");
    out(true, 'Order updated.');
}

out(false, 'Invalid request.');