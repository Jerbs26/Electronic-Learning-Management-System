<?php
// ============================================================
//  Arandia College eLMS — Student Attendance API (read-only)
//  File: api/student_attendance.php
//
//  Returns the LOGGED-IN student's attendance as JSON for the
//  live refresh on student/attendance.php. The student id always
//  comes from the session — no request parameter can change whose
//  records are returned.
// ============================================================
session_start();

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate');

function sa_out(array $payload, int $code = 200): void
{
    http_response_code($code);
    echo json_encode($payload);
    exit;
}

if (!isset($_SESSION['user_id'], $_SESSION['role'])) {
    sa_out(['success' => false, 'message' => 'Your session has expired. Please sign in again.'], 401);
}
if ($_SESSION['role'] !== 'Student') {
    sa_out(['success' => false, 'message' => 'You are not authorized to perform this action.'], 403);
}
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    sa_out(['success' => false, 'message' => 'Method not allowed.'], 405);
}

$studentId = (int) $_SESSION['user_id'];
// Release the session lock right away so polling never blocks other
// requests from the same student (e.g. navigating to another page).
session_write_close();

try {
    require_once '../config/conn.php';
    require_once '../includes/student_attendance.php';
    $data = getStudentAttendance($conn, $studentId);
} catch (Throwable $e) {
    error_log('[api/student_attendance.php] ' . $e->getMessage());
    sa_out(['success' => false, 'message' => 'Unable to load attendance right now.'], 500);
}

if (!$data['ok']) {
    sa_out(['success' => false, 'message' => 'Unable to load attendance right now.'], 500);
}

// If the page already has this exact version, skip sending everything again.
$known = (string) ($_GET['since'] ?? '');
if ($known !== '' && hash_equals($data['signature'], $known)) {
    sa_out(['success' => true, 'changed' => false, 'signature' => $data['signature']]);
}

sa_out(['success' => true, 'changed' => true, 'signature' => $data['signature'], 'data' => $data]);
