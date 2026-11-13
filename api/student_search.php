<?php
// ============================================================
//  Arandia College eLMS — Student top-bar search
//  File: api/student_search.php?q=...
//  Searches only what this student can see: their enrolled subjects,
//  published modules, assignments, published quizzes and announcements
//  (same visibility rules as student/_init.php, via includes/scope.php).
// ============================================================
session_start();
require_once __DIR__ . '/../config/conn.php';
require_once __DIR__ . '/../includes/scope.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'Student') {
  http_response_code(403);
  echo json_encode(['success' => false, 'results' => []]);
  exit;
}
$sid = (int) $_SESSION['user_id'];
$q = trim((string) ($_GET['q'] ?? ''));
if (strlen($q) < 2) { echo json_encode(['success' => true, 'results' => []]); exit; }
if (strlen($q) > 80) $q = function_exists('mb_substr') ? mb_substr($q, 0, 80, 'UTF-8') : substr($q, 0, 80);
$like = '%' . addcslashes($q, '%_\\') . '%';
$results = [];

// Runs a prepared SELECT. A failure in one group (for example a table that
// does not exist yet on an older database) returns no rows for that group
// instead of breaking the whole search.
$run = function (string $sql, string $types, array $params) use ($conn): array {
  try {
    $st = $conn->prepare($sql);
    if (!$st) return [];
    $st->bind_param($types, ...$params);
    $st->execute();
    $rows = $st->get_result()->fetch_all(MYSQLI_ASSOC);
    $st->close();
    return $rows;
  } catch (Throwable $e) {
    error_log('[eLMS student_search] ' . $e->getMessage());
    return [];
  }
};

// Subjects the student is enrolled in
foreach ($run(
  "SELECT c.id, c.course_code, c.course_name
   FROM enrollments e JOIN courses c ON c.id = e.course_id
   WHERE e.student_id = ? AND e.status = 'Enrolled' AND (c.course_name LIKE ? OR c.course_code LIKE ?)
   ORDER BY c.course_name LIMIT 5", 'iss', [$sid, $like, $like]) as $r) {
  $results[] = ['group' => 'Subjects', 'title' => $r['course_name'], 'sub' => $r['course_code'], 'url' => 'modules.php?course=' . (int) $r['id']];
}

// Published modules of those subjects
foreach ($run(
  "SELECT m.title, c.id AS course_id, c.course_name
   FROM modules m
   JOIN courses c ON c.id = m.course_id
   JOIN enrollments e ON e.course_id = m.course_id AND e.student_id = ? AND e.status = 'Enrolled'
   WHERE m.published = 1 AND m.title LIKE ?
   ORDER BY c.course_name, m.id LIMIT 5", 'is', [$sid, $like]) as $r) {
  $results[] = ['group' => 'Modules', 'title' => $r['title'], 'sub' => $r['course_name'], 'url' => 'modules.php?course=' . (int) $r['course_id']];
}

// Assignments from the student's own section teacher
foreach ($run(
  "SELECT a.title, c.id AS course_id, c.course_name
   FROM assignments a
   JOIN courses c ON c.id = a.course_id
   JOIN enrollments e ON e.course_id = a.course_id AND e.student_id = ? AND e.status = 'Enrolled'
   WHERE a.title LIKE ? AND " . scopeStudentOwned($conn, 'a', 'assignments', $sid) . "
   ORDER BY a.created_at DESC LIMIT 5", 'is', [$sid, $like]) as $r) {
  $results[] = ['group' => 'Assignments', 'title' => $r['title'], 'sub' => $r['course_name'], 'url' => 'assignments.php?course=' . (int) $r['course_id']];
}

// Published quizzes
foreach ($run(
  "SELECT q.title, c.id AS course_id, c.course_name
   FROM quizzes q
   JOIN courses c ON c.id = q.course_id
   JOIN enrollments e ON e.course_id = q.course_id AND e.student_id = ? AND e.status = 'Enrolled'
   WHERE q.status = 'Published' AND q.title LIKE ? AND " . scopeStudentOwned($conn, 'q', 'quizzes', $sid) . "
   ORDER BY q.created_at DESC LIMIT 5", 'is', [$sid, $like]) as $r) {
  $results[] = ['group' => 'Quizzes', 'title' => $r['title'], 'sub' => $r['course_name'], 'url' => 'assignments.php?course=' . (int) $r['course_id'] . '#quizzes'];
}

// Announcements meant for this student
foreach ($run(
  "SELECT a.title, CONCAT(u.first_name, ' ', u.last_name) AS author
   FROM announcements a JOIN users u ON u.id = a.author_id
   WHERE a.title LIKE ? AND " . scopeStudentAnnouncements('a', 'u', $sid) . "
   ORDER BY a.posted_at DESC LIMIT 5", 's', [$like]) as $r) {
  $results[] = ['group' => 'Announcements', 'title' => $r['title'], 'sub' => 'Posted by ' . $r['author'], 'url' => 'announcements.php'];
}

echo json_encode(['success' => true, 'results' => $results], JSON_INVALID_UTF8_SUBSTITUTE);
