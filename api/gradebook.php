<?php
// ============================================================
//  Arandia College eLMS — Gradebook API (teacher)
//  File: api/gradebook.php
//    GET  ?action=list&course_id=        items, students, cells, totals
//    GET  ?action=export&course_id=      CSV download
//    POST ?action=save_score             {item_id, student_id, score}    manual item
//  Adding, editing and deleting manual items were removed from the Grades page;
//  items added before stay in the table and their scores can still be typed.
//  Assignment scores are saved through api/assignments.php?action=grade_one,
//  quiz scores come from the quiz itself — nothing is entered twice.
// ============================================================
session_start();
require_once '../config/conn.php';
require_once '../includes/gradebook.php';
require_once '../includes/scope.php';
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'Teacher') {
  http_response_code(403);
  header('Content-Type: application/json');
  http_response_code(empty($_SESSION['user_id']) ? 401 : 403); echo json_encode(['success' => false, 'message' => empty($_SESSION['user_id']) ? 'Your session has ended. Please sign in again.' : 'You are not allowed to do this.']);
  exit;
}
$tid = (int) $_SESSION['user_id'];
$action = $_GET['action'] ?? '';
$body = json_decode(file_get_contents('php://input'), true) ?: [];

function out(bool $ok, string $msg = '', array $extra = []): void
{
  header('Content-Type: application/json');
  echo json_encode(array_merge(['success' => $ok, 'message' => $msg], $extra));
  exit;
}
function ownsCourse(mysqli $conn, int $tid, int $cid): bool
{
  $st = $conn->prepare("SELECT 1 FROM teacher_assignments WHERE teacher_id = ? AND course_id = ? LIMIT 1");
  $st->bind_param('ii', $tid, $cid);
  $st->execute();
  return (bool) $st->get_result()->fetch_row();
}
/** Students in the sections where this teacher handles the subject. */
function classStudents(mysqli $conn, int $tid, int $cid, string $section = ''): array
{
  $st = $conn->prepare(
    "SELECT DISTINCT u.id, u.first_name, u.last_name, u.school_id, u.section_dept
     FROM users u
     JOIN enrollments e ON e.student_id = u.id AND e.course_id = ? AND e.status = 'Enrolled'
     JOIN teacher_assignments ta ON ta.course_id = e.course_id AND ta.section = u.section_dept AND ta.teacher_id = ?
     WHERE u.role = 'Student' AND u.status = 'Active' AND (? = '' OR u.section_dept = ?)
     ORDER BY u.section_dept, u.last_name, u.first_name"
  );
  $st->bind_param('iiss', $cid, $tid, $section, $section);
  $st->execute();
  return $st->get_result()->fetch_all(MYSQLI_ASSOC);
}
/** A manual item that belongs to one of this teacher's subjects. */
function manualItem(mysqli $conn, int $tid, int $itemId): ?array
{
  $own = scopeTeacherOwned($conn, 'gi', 'gradebook_items', $tid);
  $st = $conn->prepare("SELECT gi.* FROM gradebook_items gi
                        WHERE gi.id = ? AND gi.source_type = 'manual' AND $own
                          AND EXISTS (SELECT 1 FROM teacher_assignments ta WHERE ta.course_id = gi.course_id AND ta.teacher_id = ?)");
  $st->bind_param('ii', $itemId, $tid);
  $st->execute();
  return $st->get_result()->fetch_assoc() ?: null;
}
/** A text or whole-number field from the JSON body; anything else (array, true/false, null) is "". */
function bodyStr($v): string
{
  return is_string($v) || is_int($v) ? trim((string) $v) : '';
}

if ($action === 'list' || $action === 'export') {
  $cid = (int) ($_GET['course_id'] ?? 0);
  if (!ownsCourse($conn, $tid, $cid)) out(false, 'Not your subject.');
  $students = classStudents($conn, $tid, $cid, is_string($_GET['section'] ?? null) ? trim($_GET['section']) : '');
  $gb = gradebookBuild($conn, $cid, array_map('intval', array_column($students, 'id')), scopeTeacherOwned($conn, 'gi', 'gradebook_items', $tid));
  $hasTable = (bool) $conn->query("SHOW TABLES LIKE 'gradebook_items'")->num_rows;

  if ($action === 'list') {
    out(true, '', [
      'items' => $gb['items'],
      'students' => array_map(fn($s) => ['id' => (int) $s['id'], 'name' => $s['last_name'] . ', ' . $s['first_name'], 'lrn' => $s['school_id'], 'section' => $s['section_dept']], $students),
      'cells' => $gb['cells'],
      'totals' => $gb['totals'],
      'manual_enabled' => $hasTable,
    ]);
  }

  // CSV export
  $course = $conn->query("SELECT course_code FROM courses WHERE id = $cid")->fetch_assoc();
  header('Content-Type: text/csv; charset=utf-8');
  header('Content-Disposition: attachment; filename="gradebook_' . preg_replace('/[^A-Za-z0-9_-]/', '_', $course['course_code'] ?? 'class') . '_' . date('Ymd') . '.csv"');
  $f = fopen('php://output', 'w');
  fwrite($f, "\xEF\xBB\xBF");
  $head = ['Student', 'LRN', 'Section'];
  foreach ($gb['items'] as $it) $head[] = $it['title'] . ' (' . $it['max'] . ')';
  array_push($head, 'Total', 'Percent');
  fputcsv($f, $head);
  foreach ($students as $s) {
    $sid = (int) $s['id'];
    $row = [$s['last_name'] . ', ' . $s['first_name'], $s['school_id'], $s['section_dept']];
    foreach ($gb['items'] as $it) {
      $c = $gb['cells'][$sid][$it['id']];
      $row[] = $c['state'] === 'graded' ? $c['score'] : ($c['state'] === 'pending' ? 'Pending' : ($c['state'] === 'missing' ? 'Missing' : ''));
    }
    $t = $gb['totals'][$sid];
    $row[] = $t['pending'] ? '' : $t['score'] . ' / ' . $t['max'];
    $row[] = $t['pending'] || $t['percent'] === null ? '' : $t['percent'] . '%';
    fputcsv($f, $row);
  }
  fclose($f);
  exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') out(false, 'Use POST.');
if (!$conn->query("SHOW TABLES LIKE 'gradebook_items'")->num_rows) out(false, 'Import database/migration_gradebook.sql first.');

// Manual items can no longer be added, edited or deleted (the Grades page has no buttons for it).
// Items added before stay, and their scores can still be typed (save_score below).
if (in_array($action, ['add_item', 'update_item', 'delete_item'], true)) {
  out(false, 'Adding, editing and deleting Gradebook items is no longer available.');
}

if ($action === 'save_score') {
  $item = manualItem($conn, $tid, (int) ($body['item_id'] ?? 0));
  if (!$item) out(false, 'Item not found.');
  $sid = (int) ($body['student_id'] ?? 0);
  if (!in_array($sid, array_map('intval', array_column(classStudents($conn, $tid, (int) $item['course_id']), 'id')), true)) out(false, 'This student is not in your class.');
  $in = $body['score'] ?? null;
  if (!($in === null || is_string($in) || is_int($in))) out(false, 'Enter a whole number from 0 to ' . gbNum($item['max_score']) . '.');
  $raw = bodyStr($in);
  if ($raw === '') {                                   // an empty box clears the score
    $st = $conn->prepare("DELETE FROM gradebook_scores WHERE item_id = ? AND student_id = ?");
    $st->bind_param('ii', $item['id'], $sid);
    $st->execute();
    out(true, 'Score cleared.');
  }
  // Whole numbers only, the same rule as the Grades page and the speed grader
  if (!preg_match('/^\d{1,5}\z/', $raw) || (int) $raw > (float) $item['max_score']) out(false, 'Enter a whole number from 0 to ' . gbNum($item['max_score']) . '.');
  $score = (float) (int) $raw;
  $st = $conn->prepare("INSERT INTO gradebook_scores (item_id, student_id, score) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE score = VALUES(score)");
  $st->bind_param('iid', $item['id'], $sid, $score);
  $st->execute();
  out(true, 'Score saved.');
}

out(false, 'Invalid request.');