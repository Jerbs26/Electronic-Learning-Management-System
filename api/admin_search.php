<?php
// ============================================================
//  Arandia College eLMS — Admin global search (top bar)
//  File: api/admin_search.php?q=...
//  Returns people, sections, and subjects the admin is allowed to see.
// ============================================================
session_start();
require_once '../config/conn.php';
require_once '../config/permissions.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['Admin', 'Head Admin'], true)) {
  http_response_code(403);
  echo json_encode(['success' => false, 'results' => []]);
  exit;
}
$q = trim($_GET['q'] ?? '');
if (mb_strlen_safe($q) < 2) { echo json_encode(['success' => true, 'results' => []]); exit; }

function mb_strlen_safe(string $s): int { return function_exists('mb_strlen') ? mb_strlen($s) : strlen($s); }
$can = function (...$keys) use ($conn): bool {
  if (isHeadAdmin()) return true;
  foreach ($keys as $k) if (adminHasPermission($conn, $k)) return true;
  return false;
};
$like = '%' . addcslashes($q, '%_\\') . '%';
$results = [];

if ($can('view_students', 'view_teachers', 'manage_admin_accounts')) {
  $st = $conn->prepare(
    "SELECT first_name, last_name, role, school_id, section_dept FROM users
     WHERE CONCAT(first_name, ' ', last_name) LIKE ? OR username LIKE ? OR school_id LIKE ? OR email LIKE ?
     ORDER BY last_name, first_name LIMIT 6"
  );
  $st->bind_param('ssss', $like, $like, $like, $like);
  $st->execute();
  $res = $st->get_result();
  while ($r = $res->fetch_assoc()) {
    $name = $r['first_name'] . ' ' . $r['last_name'];
    $results[] = ['group' => 'People', 'title' => $name,
      'sub' => $r['role'] . ($r['section_dept'] ? ' · ' . $r['section_dept'] : ''),
      'url' => 'accounts.php?tab=all&search=' . rawurlencode($name)];
  }
  $st->close();
}

if ($can('assign_teachers', 'view_subjects', 'enroll_students', 'view_enrollment', 'view_classes', 'manage_classes')) {
  $hasSections = $conn->query("SHOW TABLES LIKE 'sections'")->num_rows > 0;
  if ($hasSections) {
    $st = $conn->prepare("SELECT name, level FROM sections WHERE name LIKE ? ORDER BY sort_order LIMIT 5");
    $st->bind_param('s', $like);
    $st->execute();
    $res = $st->get_result();
    while ($r = $res->fetch_assoc()) {
      $results[] = ['group' => 'Sections', 'title' => $r['name'],
        'sub' => $r['level'] === 'SHS' ? 'Senior High School' : 'High School',
        'url' => 'subjects-enrollment.php?section=' . rawurlencode($r['name'])];
    }
    $st->close();
  }
}

$canModules = $can('manage_modules');
if ($canModules || $can('assign_teachers', 'view_subjects')) {
  $st = $conn->prepare("SELECT id, course_code, course_name FROM courses
                        WHERE status = 'Active' AND (course_name LIKE ? OR course_code LIKE ?)
                        ORDER BY course_code LIMIT 6");
  $st->bind_param('ss', $like, $like);
  $st->execute();
  $res = $st->get_result();
  while ($r = $res->fetch_assoc()) {
    $results[] = ['group' => 'Subjects', 'title' => $r['course_name'], 'sub' => $r['course_code'] . ($canModules ? ' · Modules' : ''),
      'url' => $canModules ? 'modules.php?course=' . (int) $r['id'] : 'subjects-enrollment.php'];
  }
  $st->close();
}

echo json_encode(['success' => true, 'results' => $results]);
