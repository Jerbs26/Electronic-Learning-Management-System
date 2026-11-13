<?php
// ============================================================
//  Arandia College eLMS — Teacher: My Classes
//  File: teacher/classes.php
//    classes.php           → one card per class (subject + section)
//    classes.php?class=ID  → that class: Students (or &tab=modules → Modules).
//                            The class menu beside the main sidebar
//                            (includes/class_nav.php) opens Assignments,
//                            Quizzes, Grades, and Attendance for it.
// ============================================================
require_once __DIR__ . '/../includes/settings.php';   // current school year / semester (Admin → Settings)
require __DIR__ . '/_init.php';
$currentPage = 'classes.php';
$pageTitle = 'My Classes';

$tid = (int) $user_id;
$hasModNum   = (bool) $conn->query("SHOW COLUMNS FROM modules LIKE 'module_number'")->num_rows;
$hasViews    = (bool) $conn->query("SHOW TABLES LIKE 'module_views'")->num_rows;
$hasPresence = (bool) $conn->query("SHOW COLUMNS FROM users LIKE 'last_seen_at'")->num_rows;

/** Active students in $section who are enrolled in $courseId. */
function classStudents(mysqli $conn, int $courseId, string $section, bool $withSeen): array
{
  $st = $conn->prepare(
    "SELECT u.id, u.first_name, u.last_name, u.school_id" . ($withSeen ? ", u.last_seen_at" : ", NULL AS last_seen_at") . "
     FROM users u JOIN enrollments e ON e.student_id = u.id AND e.course_id = ? AND e.status = 'Enrolled'
     WHERE u.role = 'Student' AND u.status = 'Active' AND u.section_dept = ?
     ORDER BY u.last_name, u.first_name"
  );
  $st->bind_param('is', $courseId, $section);
  $st->execute();
  $rows = $st->get_result()->fetch_all(MYSQLI_ASSOC);
  $st->close();
  return $rows;
}
function idList(array $ids): string { return $ids ? implode(',', array_map('intval', $ids)) : '0'; }
function cAgo(?string $dt): string
{
  if (!$dt) return 'Never';
  $s = time() - strtotime($dt);
  if ($s < 300) return 'Online';
  if ($s < 3600) return floor($s / 60) . ' min ago';
  if ($s < 86400) return floor($s / 3600) . ' hr' . (floor($s / 3600) == 1 ? '' : 's') . ' ago';
  if ($s < 604800) return floor($s / 86400) . ' day' . (floor($s / 86400) == 1 ? '' : 's') . ' ago';
  return date('M j', strtotime($dt));
}

$classId = (int) ($_GET['class'] ?? 0);
$class = null;
foreach ($mySubjects as $s) if ((int) $s['assign_id'] === $classId) { $class = $s; break; }

if (!$class) {
  // ── Class cards ────────────────────────────────────────────
  $view = 'grid';
  $cards = [];
  foreach ($mySubjects as $s) {
    $cid = (int) $s['course_id'];
    $stu = classStudents($conn, $cid, $s['section'], false);
    $ids = idList(array_column($stu, 'id'));
    $modCount = (int) $conn->query("SELECT COUNT(*) FROM modules WHERE course_id = $cid")->fetch_row()[0];
    $done = (int) $conn->query("SELECT COUNT(*) FROM module_progress mp JOIN modules m ON m.id = mp.module_id
                                WHERE m.course_id = $cid AND mp.student_id IN ($ids)")->fetch_row()[0];
    $toGrade = (int) $conn->query("SELECT COUNT(*) FROM submissions s JOIN assignments a ON a.id = s.assignment_id
                                   WHERE a.course_id = $cid AND s.student_id IN ($ids) AND s.score IS NULL")->fetch_row()[0];
    $pct = ($stu && $modCount) ? round($done / (count($stu) * $modCount) * 100) : 0;
    $cards[] = $s + ['students' => count($stu), 'modules' => $modCount, 'pct' => $pct, 'to_grade' => $toGrade];
  }
  // School order for Junior High subjects; everything else by section, then code
  $rank = function (string $code): int {
    $i = array_search(substr(strrchr($code, '-') ?: '', 1), ['FIL', 'ENGLISH', 'MATH', 'SCI', 'AP', 'ESP', 'MAPEH', 'TLE'], true);
    return $i === false ? 99 : $i;
  };
  usort($cards, fn($a, $b) => [$a['section'], $rank($a['course_code']), $a['course_code']] <=> [$b['section'], $rank($b['course_code']), $b['course_code']]);
} else {
  // ── One class ──────────────────────────────────────────────
  $view = 'detail';
  $cid = (int) $class['course_id'];
  $tab = ($_GET['tab'] ?? '') === 'modules' ? 'modules' : 'students';
  $students = classStudents($conn, $cid, $class['section'], $hasPresence);
  $ids = idList(array_column($students, 'id'));

  $modules = [];
  $order = $hasModNum ? 'module_number' : 'week_number, created_at';
  $res = $conn->query("SELECT id, " . ($hasModNum ? 'module_number' : 'NULL') . " AS num, title, description, "
                       . ($hasModNum ? 'content' : "'' AS content") . ", file_path
                       FROM modules WHERE course_id = $cid AND published = 1 ORDER BY $order, id");
  while ($res && $r = $res->fetch_assoc()) { $r['opened'] = 0; $r['done'] = 0; $modules[(int) $r['id']] = $r; }

  $doneBy = $toGradeBy = [];
  if ($modules) {
    $mIds = idList(array_keys($modules));
    $res = $conn->query("SELECT student_id, module_id FROM module_progress WHERE module_id IN ($mIds) AND student_id IN ($ids)");
    while ($res && $r = $res->fetch_assoc()) { $modules[(int) $r['module_id']]['done']++; $doneBy[(int) $r['student_id']] = ($doneBy[(int) $r['student_id']] ?? 0) + 1; }
    if ($hasViews) {
      $res = $conn->query("SELECT module_id, COUNT(*) AS n FROM module_views WHERE module_id IN ($mIds) AND student_id IN ($ids) GROUP BY module_id");
      while ($res && $r = $res->fetch_assoc()) $modules[(int) $r['module_id']]['opened'] = (int) $r['n'];
    }
  }
  $res = $conn->query("SELECT s.student_id, COUNT(*) AS n FROM submissions s JOIN assignments a ON a.id = s.assignment_id
                       WHERE a.course_id = $cid AND s.student_id IN ($ids) AND s.score IS NULL GROUP BY s.student_id");
  while ($res && $r = $res->fetch_assoc()) $toGradeBy[(int) $r['student_id']] = (int) $r['n'];

  // ── Modules tab: each module's assignments and quizzes (includes/module_links.php) ──
  // Work without a module (or whose module was removed) is listed under "Other work".
  $modWork = [];        // module_id|0 => [['type'=>'assignment'|'quiz', ...], ...]
  $curModule = null;    // the module new work goes to automatically
  if ($tab === 'modules') {
    require_once __DIR__ . '/../includes/module_links.php';
    $mlOn = mlEnsure($conn);
    $nStuAll = count($students);
    try {
      $curModule = $modules ? mlAutoModule($conn, $cid, $tid) : null;
      $mCol = $mlOn ? 'a.module_id' : 'NULL';
      $res = $conn->query("SELECT a.id, a.title, a.due_date, a.max_score, $mCol AS module_id,
                                  (SELECT COUNT(DISTINCT s.student_id) FROM submissions s WHERE s.assignment_id = a.id AND s.student_id IN ($ids)) AS submitted,
                                  (SELECT COUNT(DISTINCT s.student_id) FROM submissions s WHERE s.assignment_id = a.id AND s.student_id IN ($ids) AND s.score IS NOT NULL) AS graded,
                                  (SELECT AVG(s.score) FROM submissions s WHERE s.assignment_id = a.id AND s.student_id IN ($ids) AND s.score IS NOT NULL) AS avg_score
                           FROM assignments a
                           WHERE a.course_id = $cid AND " . scopeTeacherOwned($conn, 'a', 'assignments', $tid) . "
                           ORDER BY (a.due_date IS NULL), a.due_date, a.id");
      while ($res && $r = $res->fetch_assoc()) {
        $mid = isset($modules[(int) $r['module_id']]) ? (int) $r['module_id'] : 0;
        $modWork[$mid][] = ['type' => 'assignment'] + $r;
      }
      $mCol = $mlOn ? 'q.module_id' : 'NULL';
      $res = $conn->query("SELECT q.id, q.title, q.close_at AS due_date, q.max_score, q.status, $mCol AS module_id,
                                  (SELECT COUNT(DISTINCT qa.student_id) FROM quiz_attempts qa WHERE qa.quiz_id = q.id AND qa.student_id IN ($ids) AND qa.status IN ('Submitted','Graded')) AS submitted,
                                  (SELECT COUNT(DISTINCT qa.student_id) FROM quiz_attempts qa WHERE qa.quiz_id = q.id AND qa.student_id IN ($ids) AND qa.status IN ('Submitted','Graded') AND qa.score IS NOT NULL) AS graded,
                                  (SELECT AVG(qa.score) FROM quiz_attempts qa WHERE qa.quiz_id = q.id AND qa.student_id IN ($ids) AND qa.status IN ('Submitted','Graded') AND qa.score IS NOT NULL) AS avg_score
                           FROM quizzes q
                           WHERE q.course_id = $cid AND " . scopeTeacherOwned($conn, 'q', 'quizzes', $tid) . "
                           ORDER BY (q.close_at IS NULL), q.close_at, q.id");
      while ($res && $r = $res->fetch_assoc()) {
        $mid = isset($modules[(int) $r['module_id']]) ? (int) $r['module_id'] : 0;
        $modWork[$mid][] = ['type' => 'quiz'] + $r;
      }
      // Within a module: by due date (no due date last), assignments before quizzes on ties
      foreach ($modWork as &$list) {
        usort($list, fn($x, $y) => [$x['due_date'] === null, (string) $x['due_date'], $x['type']] <=> [$y['due_date'] === null, (string) $y['due_date'], $y['type']]);
      }
      unset($list);
    } catch (Throwable $e) {
      error_log('[eLMS classes] module work: ' . $e->getMessage());
      $modWork = [];
    }
  }
  $pageTitle = $class['course_name'] . ' · ' . $class['section'];
}

require __DIR__ . '/../includes/header.php';
?>
<style>
  .section-panel { --c-soft: #eef3ff; --c-strong: #003087; --c-line: rgba(15, 23, 42, .07); }
  html[data-theme="dark"] .section-panel { --c-soft: rgba(143, 176, 255, .12); --c-strong: #c7d7ff; --c-line: rgba(255, 255, 255, .07); }
  .cl-head { display: flex; justify-content: space-between; align-items: flex-end; gap: 1rem; flex-wrap: wrap; margin-bottom: 1.25rem; }
  .cl-title { font-family: 'Nunito', sans-serif; font-size: 1.6rem; font-weight: 900; color: var(--text-dark); line-height: 1.2; }
  .cl-sub { font-size: .88rem; color: var(--text-light); margin-top: .3rem; }
  .cl-link { font-size: .84rem; font-weight: 700; color: var(--c-strong); text-decoration: none; }
  .cl-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1rem; }
  .cl-card { display: block; background: var(--white); border-radius: 16px; box-shadow: 0 2px 14px rgba(0, 0, 0, .06); padding: 1.2rem 1.3rem;
             text-decoration: none; color: inherit; border: 1px solid transparent; transition: border-color .15s, transform .15s; }
  .cl-card:hover { border-color: var(--c-strong); transform: translateY(-1px); }
  .cl-card:focus-visible { outline: 2px solid var(--c-strong); outline-offset: 2px; }
  .cl-code { font-size: .72rem; font-weight: 800; letter-spacing: .04em; color: var(--c-strong); text-transform: uppercase; }
  .cl-name { font-family: 'Nunito', sans-serif; font-size: 1.1rem; font-weight: 800; color: var(--text-dark); margin-top: .2rem; }
  .cl-sec { font-size: .82rem; color: var(--text-light); margin-top: .1rem; }
  .cl-meta { display: flex; gap: 1.1rem; margin-top: 1rem; font-size: .8rem; color: var(--text-light); }
  .cl-meta b { display: block; font-size: 1.05rem; color: var(--text-dark); font-family: 'Nunito', sans-serif; }
  .cl-bar { height: 6px; border-radius: 100px; background: var(--c-soft); margin-top: .9rem; overflow: hidden; }
  .cl-bar span { display: block; height: 100%; background: #003087; border-radius: 100px; }
  html[data-theme="dark"] .cl-bar span { background: #8fb0ff; }
  .cl-bar-l { display: flex; justify-content: space-between; font-size: .72rem; color: var(--text-light); margin-top: .35rem; }
  .cl-warn { color: #b38600; font-weight: 700; }
  .cl-panel { background: var(--white); border-radius: 16px; box-shadow: 0 2px 14px rgba(0, 0, 0, .06); overflow: hidden; }
  .cl-panel table { width: 100%; border-collapse: collapse; font-size: .86rem; }
  .cl-panel th { text-align: left; font-size: .68rem; font-weight: 800; letter-spacing: .06em; text-transform: uppercase; color: var(--text-light); padding: .7rem 1.25rem; background: var(--bg-light); }
  .cl-panel td { padding: .8rem 1.25rem; border-top: 1px solid var(--c-line); color: var(--text-dark); vertical-align: middle; }
  .cl-av { width: 34px; height: 34px; border-radius: 50%; background: var(--c-soft); color: var(--c-strong); font-weight: 800; font-size: .74rem; display: inline-flex; align-items: center; justify-content: center; margin-right: .6rem; vertical-align: middle; }
  .cl-online { color: #00875a; font-weight: 700; }
  .cl-pill { font-size: .72rem; font-weight: 700; padding: .18rem .6rem; border-radius: 100px; background: rgba(179, 134, 0, .14); color: #b38600; }
  /* Students table on phones: each row becomes a small card (no sideways scroll) */
  @media (max-width: 640px) {
    .cl-stu thead { display: none; }
    .cl-stu, .cl-stu tbody, .cl-stu tr, .cl-stu td { display: block; width: 100%; }
    .cl-stu tr { padding: .75rem .25rem; border-bottom: 1px solid var(--c-line, rgba(0,0,0,.08)); }
    .cl-stu tbody tr:last-child { border-bottom: 0; }
    .cl-stu td { border: 0 !important; padding: .14rem 0; text-align: left; }
    .cl-stu td:first-child { display: flex; align-items: center; margin-bottom: .35rem; }
    .cl-stu td[data-l] { display: flex; justify-content: space-between; gap: .75rem; font-size: .82rem; }
    .cl-stu td[data-l]::before { content: attr(data-l); color: var(--text-light); font-weight: 600; }
    /* card shows only name, LRN, last active - tap for the full details */
    .cl-stu td[data-l="Modules done"], .cl-stu td[data-l="To grade"] { display: none; }
    .cl-stu tbody tr { cursor: pointer; -webkit-tap-highlight-color: transparent; }
    .cl-stu tbody tr:active { background: var(--c-soft); }
  }
  .cl-sm { position: fixed; inset: 0; z-index: 2000; display: none; align-items: flex-end; justify-content: center; background: rgba(15, 23, 42, .45); padding: 1rem; }
  .cl-sm.open { display: flex; }
  .cl-sm-box { width: 100%; max-width: 420px; background: var(--white); border-radius: 18px; padding: 1.2rem 1.25rem 1.1rem; box-shadow: 0 10px 40px rgba(0, 0, 0, .25); margin-bottom: env(safe-area-inset-bottom, 0px); }
  .cl-sm-head { display: flex; align-items: center; gap: .7rem; padding-bottom: .8rem; margin-bottom: .5rem; border-bottom: 1px solid var(--c-line); }
  .cl-sm-head .cl-av { margin-right: 0; width: 42px; height: 42px; flex: none; }
  .cl-sm-name { font-family: 'Nunito', sans-serif; font-weight: 800; font-size: 1.05rem; color: var(--text-dark); line-height: 1.25; }
  .cl-sm-row { display: flex; justify-content: space-between; gap: 1rem; padding: .5rem 0; font-size: .88rem; border-bottom: 1px solid var(--c-line); }
  .cl-sm-row:last-of-type { border-bottom: 0; }
  .cl-sm-row span:first-child { color: var(--text-light); font-weight: 600; }
  .cl-sm-row span:last-child { color: var(--text-dark); font-weight: 700; text-align: right; }
  .cl-sm-close { width: 100%; margin-top: .6rem; padding: .65rem; border: 0; border-radius: 10px; background: var(--c-soft); color: var(--c-strong); font-weight: 700; font-size: .9rem; cursor: pointer; }
  .cl-num { width: 36px; height: 36px; border-radius: 50%; background: var(--c-soft); color: var(--c-strong); font-weight: 800; display: inline-flex; align-items: center; justify-content: center; }
  .cl-empty { text-align: center; padding: 3rem 1rem; color: var(--text-light); }
  .cl-empty strong { display: block; color: var(--text-dark); margin-bottom: .25rem; }
  .cl-btn { font-size: .78rem; font-weight: 700; padding: .4rem .8rem; border-radius: 8px; background: var(--c-soft); color: var(--c-strong); text-decoration: none; white-space: nowrap; }
  /* Modules tab: module cards (lesson + assignments + quizzes under each module) */
  .mlc-tools { display: flex; justify-content: space-between; align-items: center; gap: .75rem; flex-wrap: wrap; margin-bottom: .85rem; }
  .mlc-note { font-size: .82rem; color: var(--text-light); flex: 1 1 320px; }
  .mlc-note b { color: var(--text-dark); }
  .mlc-tool-btns { display: flex; gap: .4rem; }
  .mlc-tool-btns .cl-btn, .mlc-add .cl-btn { border: 0; cursor: pointer; font-family: inherit; }
  .mlc-list { display: flex; flex-direction: column; gap: .75rem; }
  .mlc { background: var(--white); border-radius: 14px; box-shadow: 0 2px 14px rgba(0, 0, 0, .06); overflow: hidden; border: 1px solid var(--c-line); }
  .mlc-head { width: 100%; display: flex; align-items: center; gap: .85rem; padding: .95rem 1.15rem; background: transparent; border: 0; cursor: pointer; text-align: left; font-family: inherit; color: inherit; }
  .mlc-head:hover { background: var(--c-soft); }
  .mlc-head:focus-visible { outline: 2px solid var(--c-strong); outline-offset: -2px; }
  .mlc-h { flex: 1; min-width: 0; display: flex; flex-direction: column; }
  .mlc-title { font-family: 'Nunito', sans-serif; font-weight: 800; font-size: .98rem; color: var(--text-dark); overflow-wrap: anywhere; }
  .mlc-meta { font-size: .78rem; color: var(--text-light); margin-top: .15rem; }
  .mlc-chev { flex-shrink: 0; color: var(--text-light); transition: transform .2s; }
  .mlc.open .mlc-chev { transform: rotate(180deg); }
  .mlc-body { display: none; padding: 0 1rem .35rem; }
  .mlc.open .mlc-body { display: block; }
  .mlc-sec { flex-shrink: 0; font-size: .8rem; font-weight: 700; color: var(--text-light); white-space: nowrap; }
  /* Lesson + work rows: Section · Submitted · Score · Due · Status */
  .mlc-table { width: 100%; border-collapse: separate; border-spacing: 0; table-layout: fixed; font-size: .86rem; }
  .mlc-table col.c-sub { width: 108px; } .mlc-table col.c-score { width: 108px; } .mlc-table col.c-due { width: 92px; } .mlc-table col.c-st { width: 104px; }
  .mlc-table th { font-size: .74rem; font-weight: 700; color: var(--text-dark); padding: .55rem .8rem; background: var(--bg-light); text-align: center; white-space: nowrap; }
  .mlc-table th:first-child { text-align: left; border-radius: 8px 0 0 8px; }
  .mlc-table th:last-child { border-radius: 0 8px 8px 0; }
  .mlc-table td { padding: .8rem; border-bottom: 1px solid var(--c-line); color: var(--text-dark); vertical-align: middle; text-align: center; }
  .mlc-table tbody tr:last-child td { border-bottom: 0; }
  .mlc-table tr.mlc-row { cursor: pointer; transition: background .12s; }
  .mlc-table tr.mlc-row:hover td { background: var(--c-soft); }
  .mlc-table td:first-child { text-align: left; }
  .mlc-item { display: flex; align-items: center; gap: .75rem; color: var(--text-dark); text-decoration: none; font-weight: 600; overflow-wrap: anywhere; }
  .mlc-item:focus-visible { outline: 2px solid var(--c-strong); outline-offset: 2px; border-radius: 4px; }
  .mlc-ico { flex-shrink: 0; width: 20px; height: 20px; color: var(--text-dark); }
  .mlc-sub { display: block; font-size: .74rem; font-weight: 500; color: var(--text-light); margin-top: .1rem; }
  .mlc-none { text-align: center !important; color: var(--text-light) !important; font-size: .82rem; padding: 1.2rem .8rem !important; }
  .mlc-badge { display: inline-block; font-size: .72rem; font-weight: 700; padding: .18rem .6rem; border-radius: 100px; white-space: nowrap; flex-shrink: 0; }
  .mlc-ok { background: rgba(0, 135, 90, .13); color: #00875a; }
  .mlc-cur { background: var(--c-soft); color: var(--c-strong); }
  .mlc-prog, .mlc-warn { background: rgba(179, 134, 0, .14); color: #b38600; }
  .mlc-new, .mlc-dimb { background: rgba(100, 116, 139, .14); color: #64748b; }
  .mlc-st-ok { color: #00a36c; } .mlc-st-go { color: var(--text-dark); }
  .mlc-st-ok svg, .mlc-st-go svg { width: 18px; height: 18px; vertical-align: middle; }
  .mlc-dim { color: var(--text-light); }
  .mlc-past { color: #c0392b; }
  .mlc-add { display: flex; gap: .5rem; flex-wrap: wrap; padding: .75rem 1.15rem; border-top: 1px solid var(--c-line); background: var(--bg-light); }
  /* Lesson reader: opens over the accordion so the page never changes */
  .mlr-overlay { position: fixed; inset: 0; background: rgba(15, 23, 42, .55); z-index: 600; display: none; align-items: center; justify-content: center; padding: 1rem; }
  .mlr-overlay.show { display: flex; }
  .mlr { background: var(--white); width: 100%; max-width: 760px; max-height: 90vh; border-radius: 16px; display: flex; flex-direction: column; overflow: hidden; }
  .mlr-head { padding: 1.1rem 1.4rem; border-bottom: 1px solid var(--c-line, rgba(15,23,42,.07)); display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem; }
  .mlr-kicker { font-size: .75rem; font-weight: 700; color: var(--c-strong, #003087); }
  .mlr-title { font-size: 1.15rem; font-weight: 800; color: var(--text-dark); }
  .mlr-body { padding: 1.25rem 1.4rem; overflow-y: auto; overscroll-behavior: contain; }
  .lesson { color: var(--text-dark); line-height: 1.75; font-size: .95rem; }
  .lesson h3 { font-size: 1rem; font-weight: 800; margin: 1.1rem 0 .4rem; }
  .lesson h4 { font-size: .92rem; font-weight: 700; margin: .9rem 0 .3rem; }
  .lesson p { margin: 0 0 .75rem; }
  .lesson ul, .lesson ol { margin: 0 0 .85rem 1.3rem; padding: 0; }
  .lesson li { margin-bottom: .3rem; }
  .lesson table { border-collapse: collapse; margin: .4rem 0 .9rem; font-size: .88rem; max-width: 100%; display: block; overflow-x: auto; }
  .lesson th, .lesson td { border: 1px solid var(--c-line, rgba(15,23,42,.07)); padding: .4rem .75rem; text-align: left; }
  .lesson th { background: var(--bg-light, #f5f7fa); font-weight: 700; }
  .lesson blockquote { background: var(--bg-light, #f5f7fa); border-left: 3px solid var(--c-strong, #003087); border-radius: 0 8px 8px 0; margin: .5rem 0 .9rem; padding: .6rem 1rem; color: inherit; }
  .lesson blockquote p:last-child { margin-bottom: 0; }
  .lesson details { background: var(--bg-light, #f5f7fa); border-radius: 10px; padding: .6rem 1rem; margin: .5rem 0 .9rem; }
  .lesson summary { cursor: pointer; font-weight: 700; color: var(--c-strong, #003087); }
  .lesson details[open] summary { margin-bottom: .5rem; }
  html[data-theme="dark"] .mlc-ok { color: #4ade80; background: rgba(74, 222, 128, .14); }
  html[data-theme="dark"] .mlc-st-ok { color: #4ade80; }
  html[data-theme="dark"] .mlc-prog, html[data-theme="dark"] .mlc-warn { color: #fbbf24; background: rgba(251, 191, 36, .14); }
  html[data-theme="dark"] .mlc-new, html[data-theme="dark"] .mlc-dimb { color: #cbd5e1; background: rgba(203, 213, 225, .12); }
  html[data-theme="dark"] .mlc-past { color: #f87171; }
  @media (max-width: 640px) {
    .mlc-head { padding: .85rem .9rem; gap: .65rem; flex-wrap: wrap; }
    .mlc-h { flex-basis: calc(100% - 90px); }
    .mlc-head > .mlc-badge { margin-left: calc(36px + .65rem); }
    .mlc-head > .mlc-chev { margin-left: auto; }
    /* rows become small cards */
    .mlc-body { padding: 0 .4rem .3rem; }
    .mlc-sec { display: none; }
    .mlc-table thead, .mlc-table colgroup { display: none; }
    .mlc-table, .mlc-table tbody, .mlc-table tr, .mlc-table td { display: block; width: 100%; }
    .mlc-table tr { padding: .7rem .5rem; border-bottom: 1px solid var(--c-line); }
    .mlc-table tbody tr:last-child { border-bottom: 0; }
    .mlc-table td { border: 0 !important; padding: .12rem 0; text-align: left; }
    .mlc-table td:first-child { margin-bottom: .3rem; }
    .mlc-table td[data-l] { display: flex; justify-content: space-between; gap: .75rem; font-size: .82rem; padding-left: calc(20px + .75rem); }
    .mlc-table td[data-l]::before { content: attr(data-l); color: var(--text-light); font-weight: 600; }
    .mlc-table td[data-l]:empty { display: none; }
    .mlc-add { padding: .7rem .9rem; }
  }
</style>

<div class="section-panel active">
<?php if ($view === 'grid'): ?>
  <div class="cl-head">
    <div>
      <div class="cl-title">My Classes</div>
    </div>
  </div>

  <?php if (!$cards): ?>
    <div class="cl-panel"><div class="cl-empty"><strong>No classes yet</strong>Your classes appear here once the admin assigns you a subject.</div></div>
  <?php else: ?>
    <div class="cl-grid">
      <?php foreach ($cards as $c): ?>
        <a class="cl-card" href="classes.php?class=<?= (int) $c['assign_id'] ?>">
          <div class="cl-code"><?= htmlspecialchars($c['course_code']) ?></div>
          <div class="cl-name"><?= htmlspecialchars($c['course_name']) ?></div>
          <div class="cl-sec"><?= htmlspecialchars($c['section']) ?> · <?= elmsSetSpan('school_year') /* Admin → Settings */ ?></div>
          <div class="cl-meta">
            <div><b><?= $c['students'] ?></b>student<?= $c['students'] == 1 ? '' : 's' ?></div>
            <div><b><?= $c['modules'] ?></b>module<?= $c['modules'] == 1 ? '' : 's' ?></div>
            <div><b class="<?= $c['to_grade'] ? 'cl-warn' : '' ?>"><?= $c['to_grade'] ?></b>to grade</div>
          </div>
          <div class="cl-bar"><span style="width:<?= $c['pct'] ?>%"></span></div>
          <div class="cl-bar-l"><span>Module progress</span><span><?= $c['pct'] ?>%</span></div>
        </a>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

<?php else: ?>
  <?php
    // Same page header as Assignments, Quizzes, Grades, and Attendance (.cp-row in elms-teacher.css)
    $nStu = count($students); $nMod = count($modules);
    $clSub = $tab === 'modules'
      ? $nMod . ' module' . ($nMod == 1 ? '' : 's') . ' · ' . $class['course_name'] . ' · ' . $class['section']
      : $nStu . ' student' . ($nStu == 1 ? '' : 's') . ' · ' . $class['course_name'] . ' · ' . $class['section'];
  ?>
  <div class="cp-row">
    <div class="cp-row-left">
      <div><div class="cp-row-title"><?= $tab === 'modules' ? 'Modules' : 'Students' ?></div></div>
    </div>
    <?php if ($tab === 'modules' && ($nMod > 0 || !empty($modWork[0]))): ?>
    <div class="cp-row-actions mlc-tool-btns">
      <button type="button" class="cl-btn" onclick="mlcAll(true)">Expand all</button>
      <button type="button" class="cl-btn" onclick="mlcAll(false)">Collapse all</button>
    </div>
    <?php endif; ?>
  </div>
  <?php if ($tab === 'students'): ?>
  <div class="cl-panel">
    <?php if (!$students): ?>
      <div class="cl-empty"><strong>No students yet</strong>Students show up here once the admin enrolls them in this subject.</div>
    <?php else: ?>
      <div style="overflow-x:auto;">
        <table class="cl-stu">
          <thead><tr><th>Student</th><th>LRN</th><th>Modules done</th><th>To grade</th><?php if ($hasPresence): ?><th>Last active</th><?php endif; ?></tr></thead>
          <tbody>
            <?php foreach ($students as $s): $sid = (int) $s['id']; $ago = cAgo($s['last_seen_at']); ?>
              <tr>
                <td><span class="cl-av"><?= htmlspecialchars(strtoupper(substr($s['first_name'], 0, 1) . substr($s['last_name'], 0, 1))) ?></span><strong><?= htmlspecialchars($s['last_name'] . ', ' . $s['first_name']) ?></strong></td>
                <td data-l="LRN"><?= htmlspecialchars($s['school_id']) ?></td>
                <td data-l="Modules done"><?= $doneBy[$sid] ?? 0 ?> / <?= count($modules) ?></td>
                <td data-l="To grade"><?= !empty($toGradeBy[$sid]) ? '<span class="cl-pill">' . $toGradeBy[$sid] . '</span>' : '—' ?></td>
                <?php if ($hasPresence): ?><td data-l="Last active" class="<?= $ago === 'Online' ? 'cl-online' : '' ?>"><?= htmlspecialchars($ago) ?></td><?php endif; ?>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <div class="cl-sm" id="clSm" aria-hidden="true"><div class="cl-sm-box" role="dialog" aria-modal="true" aria-labelledby="clSmName">
        <div class="cl-sm-head"><span class="cl-av" id="clSmAv"></span><div class="cl-sm-name" id="clSmName"></div></div>
        <div id="clSmRows"></div>
        <button type="button" class="cl-sm-close" id="clSmClose">Close</button>
      </div></div>
      <script>
      (function () {
        var m = document.getElementById('clSm'), mq = window.matchMedia('(max-width: 640px)');
        function close() { m.classList.remove('open'); m.setAttribute('aria-hidden', 'true'); document.body.style.overflow = ''; }
        function open(tr) {
          var c = tr.cells[0];
          document.getElementById('clSmAv').textContent = c.querySelector('.cl-av').textContent;
          document.getElementById('clSmName').textContent = c.querySelector('strong').textContent;
          var box = document.getElementById('clSmRows'); box.textContent = '';
          tr.querySelectorAll('td[data-l]').forEach(function (td) {
            var r = document.createElement('div'), l = document.createElement('span'), v = document.createElement('span');
            r.className = 'cl-sm-row'; l.textContent = td.getAttribute('data-l'); v.textContent = td.textContent.trim();
            r.appendChild(l); r.appendChild(v); box.appendChild(r);
          });
          m.classList.add('open'); m.setAttribute('aria-hidden', 'false'); document.body.style.overflow = 'hidden';
        }
        document.querySelectorAll('.cl-stu tbody tr').forEach(function (tr) {
          tr.tabIndex = 0;
          tr.addEventListener('click', function () { if (mq.matches) open(tr); });
          tr.addEventListener('keydown', function (e) { if (mq.matches && (e.key === 'Enter' || e.key === ' ')) { e.preventDefault(); open(tr); } });
        });
        m.addEventListener('click', function (e) { if (e.target === m) close(); });
        document.getElementById('clSmClose').addEventListener('click', close);
        document.addEventListener('keydown', function (e) { if (e.key === 'Escape') close(); });
        mq.addEventListener('change', close);
      })();
      </script>
    <?php endif; ?>
  </div>
  <?php else:
    // ── Modules tab: one card per module with its lesson + the work under it ──
    $secQ = '&section=' . rawurlencode($class['section']);
    $fmtDue = fn(?string $d) => $d ? date(date('Y', strtotime($d)) === date('Y') ? 'M j' : 'M j, Y', strtotime($d)) : '—';
    $svg = fn(string $paths) => '<svg class="mlc-ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $paths . '</svg>';
    $icoLesson = $svg('<path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8z"/><path d="M14 3v5h5"/><path d="M9 13h6M9 17h6"/>');
    $icoAsg    = $svg('<path d="M4 11v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8"/><path d="M12 15V3"/><path d="m8 7 4-4 4 4"/>');
    $icoQuiz   = $svg('<rect x="3.5" y="3.5" width="17" height="17" rx="2.5"/><path d="M9.6 9.3a2.5 2.5 0 0 1 4.8.9c0 1.7-2.4 2.2-2.4 3.6"/><path d="M12 17h.01"/>');
    $icoCheck  = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>';
    $icoNext   = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m9 6 6 6-6 6"/></svg>';
    $fmtNum = fn($n) => rtrim(rtrim(number_format((float) $n, 2, '.', ''), '0'), '.');
    $groups = [];
    foreach ($modules as $mid => $m) $groups[] = ['id' => (int) $mid, 'm' => $m, 'work' => $modWork[$mid] ?? []];
    if (!empty($modWork[0])) $groups[] = ['id' => 0, 'm' => null, 'work' => $modWork[0]];
  ?>
    <?php if (!$groups): ?>
      <div class="cl-panel"><div class="cl-empty"><strong>No modules yet</strong>The admin adds the modules for this subject.</div></div>
    <?php else: ?>
      <div class="mlc-list" id="mlc-list" data-class="<?= $classId ?>" data-current="<?= (int) $curModule ?>">
      <?php foreach ($groups as $g):
        $mid = $g['id']; $m = $g['m']; $work = $g['work'];
        $nA = count(array_filter($work, fn($w) => $w['type'] === 'assignment'));
        $nQ = count($work) - $nA;
        if ($m) {
          $complete = $nStuAll > 0 && $m['done'] >= $nStuAll;
          $status = $complete ? ['Completed', 'mlc-ok'] : ($mid === $curModule ? ['Current', 'mlc-cur'] : ($m['opened'] > 0 || $m['done'] > 0 ? ['In progress', 'mlc-prog'] : ['Not started', 'mlc-new']));
        } else {
          $status = null;
        }
      ?>
        <div class="mlc" id="mlc-<?= $mid ?>" data-key="mlc-<?= $mid ?>">
          <button type="button" class="mlc-head" onclick="mlcToggle(<?= $mid ?>)" aria-controls="mlc-body-<?= $mid ?>">
            <span class="cl-num"><?= $m ? ($m['num'] ? (int) $m['num'] : '•') : '+' ?></span>
            <span class="mlc-h">
              <span class="mlc-title"><?= $m ? htmlspecialchars($m['title']) : 'Other work' ?></span>
              <span class="mlc-meta">
                <?= implode(' · ', array_filter([
                  $m ? (int) $m['done'] . ' / ' . $nStuAll . ' finished the lesson' : '',
                  $nA ? $nA . ' assignment' . ($nA == 1 ? '' : 's') : '',
                  $nQ ? $nQ . ' quiz' . ($nQ == 1 ? '' : 'zes') : '',
                ])) ?>
              </span>
            </span>
            <?php if ($status): ?><span class="mlc-badge <?= $status[1] ?>"><?= $status[0] ?><?= $status[1] === 'mlc-ok' ? ' ✓' : '' ?></span><?php endif; ?>
            <?php $nSec = ($m ? 1 : 0) + count($work); ?>
            <span class="mlc-sec"><?= $nSec ?> section<?= $nSec == 1 ? '' : 's' ?></span>
            <svg class="mlc-chev" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><polyline points="6 9 12 15 18 9"></polyline></svg>
          </button>
          <div class="mlc-body" id="mlc-body-<?= $mid ?>">
            <table class="mlc-table">
              <colgroup><col><col class="c-sub"><col class="c-score"><col class="c-due"><col class="c-st"></colgroup>
              <thead><tr><th>Section</th><th>Submitted</th><th>Score</th><th>Due</th><th>Status</th></tr></thead>
              <tbody>
                <?php if ($m):
                  $href = 'modules.php?course=' . $cid . '&class=' . $classId . '&module=' . $mid;
                ?>
                  <tr class="mlc-row" onclick="mlcGo(event, this)">
                    <td><a class="mlc-item" data-read="<?= $mid ?>" href="<?= htmlspecialchars($href) ?>"><?= $icoLesson ?><span><?= htmlspecialchars($m['title']) ?><span class="mlc-sub"><?= (int) $m['done'] ?> / <?= $nStuAll ?> finished · <?= (int) $m['opened'] ?> opened</span></span></a></td>
                    <td data-l="Submitted"></td>
                    <td data-l="Score"></td>
                    <td data-l="Due"></td>
                    <td data-l="Status"><?= $status[1] === 'mlc-ok' ? '<span class="mlc-st-ok" title="Everyone finished">' . $icoCheck . '</span>' : '<span class="mlc-st-go" title="' . $status[0] . '">' . $icoNext . '</span>' ?></td>
                  </tr>
                <?php endif; ?>
                <?php foreach ($work as $w):
                  $isQuiz = $w['type'] === 'quiz';
                  $past = $w['due_date'] && strtotime($w['due_date']) < time();
                  $sub = (int) $w['submitted']; $gr = (int) $w['graded'];
                  $toDo = $sub - $gr;
                  if ($isQuiz && $w['status'] !== 'Published') $st = '<span class="mlc-badge mlc-new">Draft</span>';
                  elseif ($toDo > 0) $st = '<span class="mlc-badge mlc-warn" title="' . $toDo . ' to ' . ($isQuiz ? 'check' : 'grade') . '">' . $toDo . ' to ' . ($isQuiz ? 'check' : 'grade') . '</span>';
                  elseif ($nStuAll > 0 && $gr >= $nStuAll) $st = '<span class="mlc-st-ok" title="All graded">' . $icoCheck . '</span>';
                  elseif ($past) $st = '<span class="mlc-badge mlc-dimb">Closed</span>';
                  else $st = '<span class="mlc-st-go" title="Open">' . $icoNext . '</span>';
                  $href = 'work.php?type=' . ($isQuiz ? 'quiz' : 'assignment') . '&id=' . (int) $w['id'] . '&class=' . $classId;
                  $max = $w['max_score'] !== null ? $fmtNum($w['max_score']) : null;
                  $avg = $w['avg_score'] !== null ? $fmtNum(round((float) $w['avg_score'], 1)) : null;
                ?>
                  <tr class="mlc-row" onclick="mlcGo(event, this)">
                    <td><a class="mlc-item" href="<?= htmlspecialchars($href) ?>"><?= $isQuiz ? $icoQuiz : $icoAsg ?><span><?= htmlspecialchars($w['title']) ?></span></a></td>
                    <td data-l="Submitted"><?= $sub ?>/<?= $nStuAll ?></td>
                    <td data-l="Score"><?= $max === null ? '—' : ($avg === null ? '<span class="mlc-dim">—/' . $max . '</span>' : '<span title="Class average">' . $avg . '/' . $max . '</span>') ?></td>
                    <td data-l="Due"><?= $w['due_date'] ? htmlspecialchars($fmtDue($w['due_date'])) : '<span class="mlc-dim">—</span>' ?></td>
                    <td data-l="Status"><?= $st ?></td>
                  </tr>
                <?php endforeach; ?>
                <?php if (!$work): ?>
                  <tr><td colspan="5" class="mlc-none">No assignments or quizzes under this module yet.</td></tr>
                <?php endif; ?>
              </tbody>
            </table>
            <?php if ($m): ?>
              <div class="mlc-add">
                <button type="button" class="cl-btn" onclick="mlcNew('assign', <?= $cid ?>, <?= $mid ?>)">+ Assignment</button>
                <button type="button" class="cl-btn" onclick="mlcNew('quiz', <?= $cid ?>, <?= $mid ?>)">+ Quiz</button>
              </div>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
      </div>
      <div class="mlr-overlay" id="mlr" role="dialog" aria-modal="true" aria-labelledby="mlr-title" onclick="if (event.target === this) mlrClose()">
        <div class="mlr">
          <div class="mlr-head">
            <div>
              <div class="mlr-kicker" id="mlr-kicker"></div>
              <div class="mlr-title" id="mlr-title"></div>
            </div>
            <button type="button" class="cl-btn" id="mlr-close" onclick="mlrClose()">Close</button>
          </div>
          <div class="mlr-body">
            <div class="lesson" id="mlr-content"></div>
            <div id="mlr-file" style="margin-top:1rem;"></div>
          </div>
        </div>
      </div>
      <script>
        const MLR_MODULES = <?= json_encode(array_map(fn($m) => [
          'num' => $m['num'] ? (int) $m['num'] : null, 'title' => $m['title'],
          'description' => $m['description'] ?? '', 'content' => $m['content'] ?? '', 'file' => $m['file_path'] ?? '',
        ], $modules), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_INVALID_UTF8_SUBSTITUTE) ?: '{}' ?>;
        const MLR_COURSE = <?= json_encode($class['course_name'], JSON_HEX_TAG | JSON_HEX_AMP | JSON_INVALID_UTF8_SUBSTITUTE) ?: '""' ?>;
      </script>
      <script>
        // Open / closed modules are remembered for this class while the tab stays open.
        // New work is created right here (the same form as on Assignments / Quizzes) and the
        // cards refresh in place — no page change. Live updates refresh them the same way.
        (function () {
          const KEY = 'elms_mlc_open_' + document.getElementById('mlc-list').dataset.class;
          const list = () => document.getElementById('mlc-list');
          const read = () => { try { const v = JSON.parse(sessionStorage.getItem(KEY)); return Array.isArray(v) ? v : null; } catch (e) { return null; } };
          const save = () => { try { sessionStorage.setItem(KEY, JSON.stringify([...list().querySelectorAll('.mlc.open')].map(c => c.id))); } catch (e) { } };
          const setOpen = (card, on) => { card.classList.toggle('open', on); card.querySelector('.mlc-head').setAttribute('aria-expanded', on ? 'true' : 'false'); };
          function apply() {
            const l = list(); if (!l) return;
            let open = read();
            if (!open) open = ['mlc-' + (l.dataset.current || '0')];
            l.querySelectorAll('.mlc').forEach(c => setOpen(c, open.includes(c.id)));
            if (!l.querySelector('.mlc.open') && !read()) { const f = l.querySelector('.mlc'); if (f) setOpen(f, true); }
          }
          window.mlcToggle = id => { const c = document.getElementById('mlc-' + id); if (c) { setOpen(c, !c.classList.contains('open')); save(); } };
          window.mlrClose = () => document.getElementById('mlr').classList.remove('show');
          window.mlrOpen = id => {
            const m = MLR_MODULES[id]; if (!m) return false;
            const esc = t => { const d = document.createElement('div'); d.textContent = t ?? ''; return d.innerHTML; };
            document.getElementById('mlr-kicker').textContent = MLR_COURSE + (m.num ? ' · Module ' + m.num : '');
            document.getElementById('mlr-title').textContent = m.title;
            document.getElementById('mlr-content').innerHTML = m.content || (m.description ? '<p>' + esc(m.description) + '</p>' : '');
            document.getElementById('mlr-file').innerHTML = m.file ? '<a class="cl-btn" target="_blank" rel="noopener" href="../' + encodeURI(m.file) + '">Open attached file</a>' : '';
            document.getElementById('mlr').classList.add('show');
            document.getElementById('mlr-close').focus();
            return true;
          };
          // The page behind the reader must not scroll. Done with events only (no overflow/position changes),
          // so the sticky sidebar / topbar layout stays untouched.
          const ov = document.getElementById('mlr'), rb = ov.querySelector('.mlr-body');
          const stop = e => { if (!e.target.closest('.mlr-body')) e.preventDefault(); };
          ov.addEventListener('wheel', stop, { passive: false });
          ov.addEventListener('touchmove', stop, { passive: false });
          document.addEventListener('keydown', e => {
            if (!ov.classList.contains('show')) return;
            if (e.key === 'Escape') { mlrClose(); return; }
            // arrow / page / space keys scroll the lesson, not the page behind
            const step = { ArrowDown: 60, ArrowUp: -60, PageDown: rb.clientHeight * .9, PageUp: -rb.clientHeight * .9 }[e.key];
            if (step !== undefined) { e.preventDefault(); rb.scrollBy({ top: step }); }
            else if (e.key === 'Home') { e.preventDefault(); rb.scrollTo({ top: 0 }); }
            else if (e.key === 'End') { e.preventDefault(); rb.scrollTo({ top: rb.scrollHeight }); }
            else if (e.key === ' ' && !e.target.closest('button, a')) { e.preventDefault(); rb.scrollBy({ top: e.shiftKey ? -rb.clientHeight * .9 : rb.clientHeight * .9 }); }
          });
          // Lesson rows open the reader in place (ctrl/cmd/middle-click still use the link); work rows navigate as before.
          list().addEventListener('click', e => {
            const a = e.target.closest('a[data-read]');
            if (!a || e.ctrlKey || e.metaKey || e.shiftKey || e.button) return;
            e.preventDefault();
            mlrOpen(a.dataset.read);
          });
          window.mlcGo = (e, row) => {
            if (e.target.closest('a, button')) return;
            const a = row.querySelector('a.mlc-item'); if (!a) return;
            if (a.dataset.read && mlrOpen(a.dataset.read)) return;
            location.href = a.href;
          };
          window.mlcAll = on => { list().querySelectorAll('.mlc').forEach(c => setOpen(c, on)); save(); };
          window.mlcNew = (kind, course, module) => {
            const card = document.getElementById('mlc-' + module);
            if (card && !card.classList.contains('open')) { setOpen(card, true); save(); }
            if (typeof mlCtx === 'undefined') return;
            mlCtx = { course, module };
            if (kind === 'assign' && typeof openAssignModal === 'function') openAssignModal();
            if (kind === 'quiz' && typeof openQuizModal === 'function') openQuizModal();
          };
          let busy = false, again = false;
          window.mlcRefresh = async () => {
            if (busy) { again = true; return; }
            busy = true;
            try {
              const res = await fetch(location.href, { cache: 'no-store', credentials: 'same-origin' });
              if (!res.ok) throw new Error('HTTP ' + res.status);
              const doc = new DOMParser().parseFromString(await res.text(), 'text/html');
              const fresh = doc.getElementById('mlc-list');
              if (!fresh) { location.reload(); return; }   // the class changed shape: load it fresh
              const cur = list();
              cur.dataset.current = fresh.dataset.current || '';
              cur.innerHTML = fresh.innerHTML;
              const note = doc.querySelector('.mlc-note'), myNote = document.querySelector('.mlc-note');
              if (note && myNote) myNote.innerHTML = note.innerHTML;
              apply();
            } catch (e) {
              console.warn('[modules] refresh failed', e);
            } finally {
              busy = false;
              if (again) { again = false; window.mlcRefresh(); }
            }
          };
          apply();
        })();
      </script>
    <?php endif; ?>
  <?php endif; ?>
<?php endif; ?>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>