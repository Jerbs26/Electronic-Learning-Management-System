<?php
// ============================================================
//  Arandia College eLMS — Teacher top-bar search
//  File: api/teacher_search.php?q=...
//  Searches only the teacher's own students, subjects, assignments, and quizzes.
// ============================================================
session_start();
require_once '../config/conn.php';
require_once '../includes/scope.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'Teacher') {
  http_response_code(403);
  echo json_encode(['success' => false, 'results' => []]);
  exit;
}
$tid = (int) $_SESSION['user_id'];
$q = trim($_GET['q'] ?? '');
if (strlen($q) < 2) { echo json_encode(['success' => true, 'results' => []]); exit; }
$like = '%' . addcslashes($q, '%_\\') . '%';
$results = [];

$run = function (string $sql, string $types, array $params) use ($conn): array {
  $st = $conn->prepare($sql);
  $st->bind_param($types, ...$params);
  $st->execute();
  $rows = $st->get_result()->fetch_all(MYSQLI_ASSOC);
  $st->close();
  return $rows;
};

// Students in this teacher's sections who take one of the teacher's subjects
foreach ($run(
  "SELECT DISTINCT u.first_name, u.last_name, u.school_id, u.section_dept
   FROM users u
   JOIN enrollments e ON e.student_id = u.id AND e.status = 'Enrolled'
   JOIN teacher_assignments ta ON ta.course_id = e.course_id AND ta.section = u.section_dept AND ta.teacher_id = ?
   WHERE u.role = 'Student' AND (CONCAT(u.first_name, ' ', u.last_name) LIKE ? OR u.school_id LIKE ?)
   ORDER BY u.last_name LIMIT 6", 'iss', [$tid, $like, $like]) as $r) {
  $name = $r['first_name'] . ' ' . $r['last_name'];
  $results[] = ['group' => 'Students', 'title' => $name, 'sub' => trim(($r['section_dept'] ?? '') . ' · LRN ' . $r['school_id'], ' ·'),
                'url' => 'my-students.php?search=' . rawurlencode($name)];
}
foreach ($run(
  "SELECT ta.id, ta.section, c.course_name, c.course_code FROM courses c JOIN teacher_assignments ta ON ta.course_id = c.id AND ta.teacher_id = ?
   WHERE c.course_name LIKE ? OR c.course_code LIKE ? OR ta.section LIKE ? LIMIT 6", 'isss', [$tid, $like, $like, $like]) as $r) {
  $results[] = ['group' => 'Classes', 'title' => $r['course_name'] . ' · ' . $r['section'], 'sub' => $r['course_code'], 'url' => 'classes.php?class=' . (int) $r['id']];
}
foreach ($run(
  "SELECT a.title, c.course_name FROM assignments a JOIN courses c ON c.id = a.course_id
   WHERE a.course_id IN (SELECT course_id FROM teacher_assignments WHERE teacher_id = ?) AND a.title LIKE ? AND " . scopeTeacherOwned($conn, 'a', 'assignments', $tid) . "
   ORDER BY a.created_at DESC LIMIT 5", 'is', [$tid, $like]) as $r) {
  $results[] = ['group' => 'Assignments', 'title' => $r['title'], 'sub' => $r['course_name'], 'url' => 'assignments.php'];
}
foreach ($run(
  "SELECT q.title, c.course_name FROM quizzes q JOIN courses c ON c.id = q.course_id
   WHERE q.course_id IN (SELECT course_id FROM teacher_assignments WHERE teacher_id = ?) AND q.title LIKE ? AND " . scopeTeacherOwned($conn, 'q', 'quizzes', $tid) . "
   ORDER BY q.created_at DESC LIMIT 5", 'is', [$tid, $like]) as $r) {
  $results[] = ['group' => 'Quizzes', 'title' => $r['title'], 'sub' => $r['course_name'], 'url' => 'assignments.php#quizzes'];
}
echo json_encode(['success' => true, 'results' => $results]);
