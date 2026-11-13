<?php
// ============================================================
//  Arandia College eLMS — Student Dashboard Progress API
//  File: api/dashboard_progress.php
//
//  Returns the logged-in student's current Learning Progress
//  and Recent Grades numbers as JSON, for the dashboard's
//  polling / instant-refresh JS. Same calculation used on first
//  page load — see includes/progress.php.
// ============================================================
session_start();
require_once '../config/conn.php';
require_once '../includes/progress.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'Student') {
    http_response_code(empty($_SESSION['user_id']) ? 401 : 403); echo json_encode(['success' => false, 'message' => empty($_SESSION['user_id']) ? 'Your session has ended. Please sign in again.' : 'You are not allowed to do this.']);
    exit;
}

$user_id = (int) $_SESSION['user_id'];
$data = getStudentLearningProgress($conn, $user_id);
echo json_encode(['success' => true, 'data' => $data]);
