<?php
// ============================================================
//  Arandia College eLMS — Modules for the assignment / quiz forms
//  File: api/module_links.php
//  GET ?course_id=ID → the subject's modules + the one picked
//  automatically (includes/module_links.php). Teachers only, and
//  only for a subject they teach.
// ============================================================
session_start();
require_once '../config/conn.php';
require_once '../includes/module_links.php';
header('Content-Type: application/json');
header('Cache-Control: no-store');

if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'Teacher') {
    http_response_code(empty($_SESSION['user_id']) ? 401 : 403);
    echo json_encode(['success' => false, 'message' => empty($_SESSION['user_id']) ? 'Your session has ended. Please sign in again.' : 'You are not allowed to do this.']);
    exit;
}
$tid = (int) $_SESSION['user_id'];
session_write_close();

$courseId = (int) ($_GET['course_id'] ?? 0);
$st = $conn->prepare("SELECT 1 FROM teacher_assignments WHERE teacher_id = ? AND course_id = ? LIMIT 1");
$st->bind_param('ii', $tid, $courseId);
$st->execute();
$mine = (bool) $st->get_result()->fetch_row();
$st->close();
if (!$courseId || !$mine) {
    echo json_encode(['success' => false, 'message' => 'Not your subject.']);
    exit;
}

echo json_encode([
    'success' => true,
    'enabled' => mlEnsure($conn),
    'modules' => array_values(mlModules($conn, $courseId)),
    'auto'    => mlAutoModule($conn, $courseId, $tid),
]);
