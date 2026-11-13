<?php
// ============================================================
//  Arandia College eLMS — Teacher: one assignment or quiz of a class
//  File: teacher/work.php?type=assignment|quiz&id=ID&class=ASSIGN_ID[&tab=submissions]
//  Opened from My Classes → Modules (click a row).
//  Same layout as the student's page (student/work.php): one column —
//    Top:    Previous / Next link (the module's lesson + work, in order)
//    Title · summary strip (due, points, attempts, class score) · small print
//    Card:   Instructions + the teacher's file, class submission summary, Grade / Edit buttons
//    Card:   Submissions of every student in the class
// ============================================================
require __DIR__ . '/_init.php';
require_once __DIR__ . '/../includes/upload_names.php';   // show original file names
require_once __DIR__ . '/../includes/module_links.php';
$currentPage = 'modules.php';   // lights "Modules" in the class menu

$tid = (int) $user_id;
$type = ($_GET['type'] ?? '') === 'quiz' ? 'quiz' : 'assignment';
$isQuiz = $type === 'quiz';
$id = (int) ($_GET['id'] ?? 0);
$classId = (int) ($_GET['class'] ?? 0);

$class = null;
foreach ($mySubjects as $s) if ((int) $s['assign_id'] === $classId) { $class = $s; break; }
if (!$class) { header('Location: classes.php'); exit; }
$cid = (int) $class['course_id'];
$back = 'classes.php?class=' . $classId . '&tab=modules';

$mlOn = mlEnsure($conn);
$mCol = $mlOn ? 'module_id' : 'NULL';

// ── The item (must be this class's subject and this teacher's / shared) ──
if ($isQuiz) {
  $st = $conn->prepare("SELECT q.*, q.close_at AS due_date, $mCol AS mod_id FROM quizzes q
                        WHERE q.id = ? AND q.course_id = ? AND " . scopeTeacherOwned($conn, 'q', 'quizzes', $tid));
} else {
  $st = $conn->prepare("SELECT a.*, $mCol AS mod_id FROM assignments a
                        WHERE a.id = ? AND a.course_id = ? AND " . scopeTeacherOwned($conn, 'a', 'assignments', $tid));
}
$st->bind_param('ii', $id, $cid);
$st->execute();
$item = $st->get_result()->fetch_assoc();
$st->close();
if (!$item) { header('Location: ' . $back); exit; }

$num = fn($n) => rtrim(rtrim(number_format((float) $n, 2, '.', ''), '0'), '.');
$when = fn(?string $d) => $d ? date('M j, g:i a', strtotime($d)) : null;
$max = (float) $item['max_score'];

// ── Module + Previous / Continue ──
$hasModNum = (bool) $conn->query("SHOW COLUMNS FROM modules LIKE 'module_number'")->num_rows;
$module = null;
$modId = (int) ($item['mod_id'] ?? 0);
if ($modId) {
  $st = $conn->prepare("SELECT id, " . ($hasModNum ? 'module_number' : 'NULL') . " AS num, title FROM modules WHERE id = ? AND course_id = ? AND published = 1");
  $st->bind_param('ii', $modId, $cid);
  $st->execute();
  $module = $st->get_result()->fetch_assoc();
  $st->close();
}
$modKey = $module ? (int) $module['id'] : 0;
$seq = [];   // [kind label, href, title]
if ($module) $seq[] = ['Lesson', 'modules.php?course=' . $cid . '&class=' . $classId . '&module=' . $modKey, (string) $module['title']];
$modWhere = $mlOn
  ? ($module ? "module_id = $modKey" : "(module_id IS NULL OR module_id NOT IN (SELECT id FROM modules WHERE course_id = $cid AND published = 1))")
  : '1=1';
$work = [];
$res = $conn->query("SELECT 'assignment' AS t, id, title, due_date FROM assignments a WHERE course_id = $cid AND $modWhere AND " . scopeTeacherOwned($conn, 'a', 'assignments', $tid));
while ($res && $r = $res->fetch_assoc()) $work[] = $r;
$res = $conn->query("SELECT 'quiz' AS t, id, title, close_at AS due_date FROM quizzes q WHERE course_id = $cid AND $modWhere AND " . scopeTeacherOwned($conn, 'q', 'quizzes', $tid));
while ($res && $r = $res->fetch_assoc()) $work[] = $r;
// Same order as the Modules tab: due date (none last), assignments before quizzes, then id
usort($work, fn($x, $y) => [$x['due_date'] === null, (string) $x['due_date'], $x['t'], (int) $x['id']] <=> [$y['due_date'] === null, (string) $y['due_date'], $y['t'], (int) $y['id']]);
$pos = null;
foreach ($work as $w) {
  if ($w['t'] === $type && (int) $w['id'] === $id) $pos = count($seq);
  $seq[] = [$w['t'] === 'quiz' ? 'Quiz' : 'Assignment', 'work.php?type=' . $w['t'] . '&id=' . (int) $w['id'] . '&class=' . $classId, (string) $w['title']];
}
$prevItem = $pos !== null && $pos > 0 ? $seq[$pos - 1] : null;
$nextItem = $pos !== null && $pos < count($seq) - 1 ? $seq[$pos + 1] : null;
$prev = $prevItem[1] ?? null;
$next = $nextItem[1] ?? null;

// ── Every student in this class, with their submission / latest attempt ──
$st = $conn->prepare(
  "SELECT u.id, u.first_name, u.last_name, u.school_id
   FROM users u JOIN enrollments e ON e.student_id = u.id AND e.course_id = ? AND e.status = 'Enrolled'
   WHERE u.role = 'Student' AND u.status = 'Active' AND u.section_dept = ?
   GROUP BY u.id ORDER BY u.last_name, u.first_name"
);
$st->bind_param('is', $cid, $class['section']);
$st->execute();
$students = $st->get_result()->fetch_all(MYSQLI_ASSOC);
$st->close();
$ids = $students ? implode(',', array_map(fn($s) => (int) $s['id'], $students)) : '0';

$subs = [];   // student_id => row
if ($isQuiz) {
  // Latest finished attempt per student (best score if several are graded)
  $res = $conn->query("SELECT student_id, score, status, file_path, submitted_at, COUNT(*) OVER (PARTITION BY student_id) AS tries
                       FROM quiz_attempts WHERE quiz_id = $id AND student_id IN ($ids) AND status IN ('Submitted','Graded')
                       ORDER BY (score IS NULL), score DESC, submitted_at DESC");
  if (!$res) {   // older MySQL without window functions
    $res = $conn->query("SELECT student_id, score, status, file_path, submitted_at, 1 AS tries
                         FROM quiz_attempts WHERE quiz_id = $id AND student_id IN ($ids) AND status IN ('Submitted','Graded')
                         ORDER BY (score IS NULL), score DESC, submitted_at DESC");
  }
  while ($res && $r = $res->fetch_assoc()) if (!isset($subs[(int) $r['student_id']])) $subs[(int) $r['student_id']] = $r;
} else {
  $res = $conn->query("SELECT student_id, score, status, file_path, submitted_at, graded_at, attempt_count AS tries
                       FROM submissions WHERE assignment_id = $id AND student_id IN ($ids) AND (attempt_count > 0 OR score IS NOT NULL)");
  // (a score given without a submission still counts as graded, same as grade.php)
  while ($res && $r = $res->fetch_assoc()) $subs[(int) $r['student_id']] = $r;
}
$nStu = count($students);
$nSub = count(array_filter($subs, fn($x) => (int) ($x['tries'] ?? 0) > 0));   // real submissions only
$scores = array_values(array_filter(array_map(fn($s) => $s['score'], $subs), fn($v) => $v !== null));
$nGraded = count($scores);
$avg = $nGraded ? array_sum(array_map('floatval', $scores)) / $nGraded : null;
$pct = ($avg !== null && $max > 0) ? max(0, min(100, $avg / $max * 100)) : 0;
$toGrade = count(array_filter($subs, fn($x) => $x['score'] === null));

// ── Teacher's view: what needs my attention + the roster ──
$due = $item['due_date'];
$isPast = $due && strtotime($due) < time();
$draft = $isQuiz && ($item['status'] ?? '') !== 'Published';
$file = $item['file_path'] ?? '';
$fileExt = $file ? strtoupper(pathinfo($file, PATHINFO_EXTENSION) ?: 'FILE') : '';
$instr = $isQuiz ? ($item['description'] ?? '') : (($item['instructions'] ?? '') !== '' ? $item['instructions'] : ($item['description'] ?? ''));
$hasInstr = trim((string) $instr) !== '';
$maxAtt = !$isQuiz && !empty($item['max_attempts']) ? (int) $item['max_attempts'] : null;   // empty = unlimited
$verb = $isQuiz ? 'check' : 'grade';

// Roster rows + counts for the filter chips
$rows = [];
$cnt = ['all' => 0, 'todo' => 0, 'graded' => 0, 'none' => 0];
foreach ($students as $s) {
  $sid = (int) $s['id']; $sub = $subs[$sid] ?? null;
  $late = $sub && (int) ($sub['tries'] ?? 1) > 0 && $due && !empty($sub['submitted_at']) && strtotime($sub['submitted_at']) > strtotime($due);
  $st = !$sub ? 'none' : ($sub['score'] !== null ? 'graded' : 'todo');
  $cnt['all']++; $cnt[$st]++;
  $rows[] = ['s' => $s, 'sid' => $sid, 'sub' => $sub, 'late' => $late, 'st' => $st];
}
$subPct = $nStu > 0 ? round($nSub / $nStu * 100) : 0;

// Small print: the facts that are not in the strip
$fine = [$num($max) . ' points'];
if ($isQuiz) {
  $qCount = (int) $conn->query("SELECT COUNT(*) FROM quiz_questions WHERE quiz_id = $id")->fetch_row()[0];
  $fine[] = ($item['quiz_type'] ?? '') === 'file' ? 'File upload quiz' : 'Online quiz · ' . $qCount . ' question' . ($qCount === 1 ? '' : 's');
  $fine[] = !empty($item['time_limit']) ? (int) $item['time_limit'] . ' min limit' : 'No time limit';
  if ($item['passing_score'] !== null) $fine[] = 'Passing ' . $num($item['passing_score']) . '%';
  $fine[] = 'Opens ' . ($when($item['open_at']) ?? 'right away');
} else {
  $fine[] = 'File upload (dropbox)';
  $fine[] = $maxAtt !== null ? $maxAtt . ' attempt' . ($maxAtt === 1 ? '' : 's') . ' per student' : 'Unlimited attempts';
  if (!empty($item['created_at'])) $fine[] = 'Posted ' . $when($item['created_at']);
}

$I = [
  'cal'   => '<rect x="3.5" y="5" width="17" height="15" rx="2"/><path d="M3.5 10h17M8 3v4M16 3v4"/>',
  'inbox' => '<path d="M3 13h5l1.5 3h5L16 13h5"/><path d="M5.5 5h13L21 13v5a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-5z"/>',
  'check' => '<circle cx="12" cy="12" r="8.5"/><path d="m8.5 12.3 2.4 2.4 4.6-5"/>',
  'left'  => '<path d="m15 6-6 6 6 6"/>', 'right' => '<path d="m9 6 6 6-6 6"/>',
];
$i = fn(string $k) => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $I[$k] . '</svg>';

$classNavCourse = $cid;
$classNavSection = $class['section'];
$pageTitle = $item['title'] . ' · ' . $class['course_name'];
require __DIR__ . '/../includes/header.php';

// Editing payload for the Edit button
$editAsg = htmlspecialchars(json_encode([
  'id' => $id, 'course_id' => $cid, 'module_id' => $mlOn ? ($item['mod_id'] !== null ? (int) $item['mod_id'] : null) : null,
  'title' => $item['title'], 'description' => $item['description'] ?? '', 'due_date' => $item['due_date'],
  'max_score' => $item['max_score'], 'max_attempts' => $item['max_attempts'] ?? null, 'file_path' => $item['file_path'] ?? null,
]), ENT_QUOTES);
?>
<style>
  /* Teacher activity page — built for grading: what needs me, then the class list. Scoped to .tw */
  .tw { --tw-blue: #1e56c7; --tw-line: rgba(15, 23, 42, .09); --tw-card: var(--ui-surface, #fff); --tw-soft: #f5f7fb; --tw-text: var(--text-dark, #0f172a); --tw-muted: var(--text-light, #64748b);
        --tw-ok: #0f8a5f; --tw-ok-bg: #e4f5ee; --tw-wait: #a16207; --tw-wait-bg: #fdf3dc; --tw-miss: #b42318; --tw-miss-bg: #fdecea; --tw-track: #e6ebf3; }
  html[data-theme="dark"] .tw { --tw-blue: #8fb0ff; --tw-line: rgba(255, 255, 255, .09); --tw-soft: rgba(255, 255, 255, .04); --tw-track: rgba(255, 255, 255, .12);
        --tw-ok: #4ade80; --tw-ok-bg: rgba(74, 222, 128, .12); --tw-wait: #fbbf24; --tw-wait-bg: rgba(251, 191, 36, .12); --tw-miss: #f87171; --tw-miss-bg: rgba(248, 113, 113, .12); }
  .tw-wrap { width: 100%; }
  .tw-pn { display: flex; justify-content: space-between; gap: 1rem; margin-bottom: .35rem; min-height: 40px; }
  .tw-pn a { display: inline-flex; align-items: center; gap: .35rem; min-height: 40px; max-width: 48%; font-size: .88rem; font-weight: 700; color: var(--tw-blue); text-decoration: none; }
  .tw-pn a span { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
  .tw-pn a:hover span { text-decoration: underline; }
  .tw-pn svg { width: 16px; height: 16px; flex-shrink: 0; }
  .tw-pn .nx { margin-left: auto; }
  .tw a:focus-visible, .tw button:focus-visible { outline: 2px solid var(--tw-blue); outline-offset: 2px; }

  /* Title + actions */
  .tw-head { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: .75rem 1.25rem; margin: .2rem 0 1rem; }
  .tw-titlebox { display: flex; align-items: center; flex-wrap: wrap; gap: .4rem .75rem; min-width: 0; }
  .tw-title { margin: 0; font-family: 'Nunito', sans-serif; font-size: 1.85rem; font-weight: 900; line-height: 1.2; color: var(--tw-text); overflow-wrap: anywhere; }
  .tw-tag { display: inline-flex; align-items: center; gap: .35rem; font-size: .74rem; font-weight: 800; padding: .25rem .7rem; border-radius: 100px; white-space: nowrap; }
  .tw-tag::before { content: ''; width: 7px; height: 7px; border-radius: 50%; background: currentColor; }
  .tw-tag.open { background: var(--tw-ok-bg); color: var(--tw-ok); } .tw-tag.closed { background: var(--tw-soft); color: var(--tw-muted); } .tw-tag.draft { background: var(--tw-wait-bg); color: var(--tw-wait); }
  .tw-actions { display: flex; flex-wrap: wrap; align-items: center; gap: .55rem; }
  .tw-do { display: inline-flex; align-items: center; justify-content: center; min-height: 42px; padding: 0 1.3rem; border-radius: 100px; border: 0; cursor: pointer; font: inherit; font-size: .9rem; font-weight: 800;
           background: var(--tw-blue); color: #fff; text-decoration: none; white-space: nowrap; }
  html[data-theme="dark"] .tw-do { background: #3b6fe0; }
  .tw-do:hover { filter: brightness(1.08); color: #fff; }
  .tw-do.alt, html[data-theme="dark"] .tw-do.alt { background: var(--tw-card); color: var(--tw-text); border: 1px solid var(--tw-line); }
  .tw-do.alt:hover { color: var(--tw-text); background: var(--tw-soft); filter: none; }

  /* Summary strip */
  .tw-strip { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); background: var(--tw-card); border: 1px solid var(--tw-line); border-radius: 14px; overflow: hidden; }
  .tw-cell { display: flex; gap: .7rem; align-items: center; padding: .85rem 1rem; border-left: 1px solid var(--tw-line); min-width: 0; }
  .tw-cell:first-child { border-left: 0; }
  .tw-cell-ico { width: 34px; height: 34px; flex-shrink: 0; border-radius: 10px; display: grid; place-items: center; background: var(--tw-soft); color: var(--tw-blue); }
  .tw-cell-ico svg { width: 17px; height: 17px; }
  .tw-cell > div { min-width: 0; flex: 1; }
  .tw-cell small { display: block; font-size: .68rem; font-weight: 800; letter-spacing: .06em; text-transform: uppercase; color: var(--tw-muted); }
  .tw-cell b { display: block; font-size: .95rem; color: var(--tw-text); overflow-wrap: anywhere; }
  .tw-cell b.ok { color: var(--tw-ok); } .tw-cell b.wait { color: var(--tw-wait); } .tw-cell b.miss { color: var(--tw-miss); }
  .tw-cell i { display: block; font-style: normal; font-size: .74rem; color: var(--tw-muted); margin-top: .1rem; }
  .tw-bar { height: 5px; border-radius: 99px; background: var(--tw-track); margin-top: .35rem; overflow: hidden; }
  .tw-bar span { display: block; height: 100%; border-radius: 99px; background: var(--tw-blue); }
  .tw-fine { margin: .55rem .2rem 0; font-size: .8rem; color: var(--tw-muted); }

  /* Cards */
  .tw-doc { margin-top: 1rem; background: var(--tw-card); border: 1px solid var(--tw-line); border-radius: 14px; padding: 1.4rem 1.6rem; }
  .tw-h { margin: 0 0 .65rem; font-size: .74rem; font-weight: 800; letter-spacing: .07em; text-transform: uppercase; color: var(--tw-muted); }
  .tw-body { font-size: .95rem; line-height: 1.75; color: var(--tw-text); overflow-wrap: anywhere; }
  .tw-body p { margin: 0 0 .8rem; } .tw-body p:last-child { margin-bottom: 0; }
  .tw-file { display: inline-flex; align-items: center; gap: .8rem; padding: .6rem .9rem .6rem .6rem; border: 1px solid var(--tw-line); border-radius: 12px; text-decoration: none; color: inherit; max-width: 100%; }
  .tw-body + .tw-file { margin-top: 1rem; }
  .tw-file:hover { border-color: var(--tw-blue); background: var(--tw-soft); }
  .tw-ext { width: 38px; height: 44px; flex-shrink: 0; border-radius: 8px; display: grid; place-items: center; font-size: .6rem; font-weight: 900; color: #fff; background: #b42318; }
  .tw-file-n { min-width: 0; font-size: .88rem; font-weight: 700; color: var(--tw-text); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
  .tw-file-n small { display: block; font-size: .74rem; font-weight: 500; color: var(--tw-muted); }

  /* Roster */
  .tw-bar-top { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: .6rem 1rem; margin-bottom: .9rem; }
  .tw-bar-top .tw-h { margin: 0; }
  .tw-chips { display: flex; flex-wrap: wrap; gap: .4rem; }
  .tw-chip { border: 1px solid var(--tw-line); background: transparent; color: var(--tw-text); font: inherit; font-size: .8rem; font-weight: 700; padding: .35rem .85rem; border-radius: 100px; cursor: pointer; }
  .tw-chip b { font-weight: 800; color: var(--tw-muted); margin-left: .25rem; }
  .tw-chip:hover { background: var(--tw-soft); }
  .tw-chip.on { background: var(--tw-blue); border-color: var(--tw-blue); color: #fff; }
  html[data-theme="dark"] .tw-chip.on { background: #3b6fe0; border-color: #3b6fe0; }
  .tw-chip.on b { color: rgba(255, 255, 255, .85); }
  .tw-tablewrap { overflow-x: auto; }
  .tw-subs { width: 100%; border-collapse: collapse; font-size: .86rem; }
  .tw-subs th { text-align: left; font-size: .7rem; font-weight: 800; letter-spacing: .05em; text-transform: uppercase; color: var(--tw-muted); padding: .55rem .75rem; background: var(--tw-soft); white-space: nowrap; }
  .tw-subs th:first-child { border-radius: 8px 0 0 8px; } .tw-subs th:last-child { border-radius: 0 8px 8px 0; }
  .tw-subs td { padding: .7rem .75rem; border-bottom: 1px solid var(--tw-line); color: var(--tw-text); vertical-align: middle; }
  .tw-subs tr:last-child td { border-bottom: 0; }
  .tw-subs tr[hidden] { display: none; }
  .tw-subs .lrn { display: block; font-size: .74rem; color: var(--tw-muted); }
  .tw-subs .act { text-align: right; white-space: nowrap; }
  .tw-pill { display: inline-block; font-size: .72rem; font-weight: 800; padding: .16rem .6rem; border-radius: 100px; white-space: nowrap; }
  .tw-p-ok { background: var(--tw-ok-bg); color: var(--tw-ok); }
  .tw-p-warn { background: var(--tw-wait-bg); color: var(--tw-wait); }
  .tw-p-late { background: var(--tw-miss-bg); color: var(--tw-miss); margin-left: .4rem; }
  .tw-p-none { background: var(--tw-soft); color: var(--tw-muted); }
  .tw-btn { display: inline-flex; align-items: center; min-height: 32px; padding: 0 .95rem; border-radius: 100px; font-size: .8rem; font-weight: 800; text-decoration: none; white-space: nowrap; background: var(--tw-blue); color: #fff; }
  html[data-theme="dark"] .tw-btn { background: #3b6fe0; }
  .tw-btn:hover { filter: brightness(1.08); color: #fff; }
  .tw-btn.ghost, html[data-theme="dark"] .tw-btn.ghost { background: transparent; color: var(--tw-blue); border: 1px solid var(--tw-line); }
  .tw-btn.ghost:hover { background: var(--tw-soft); color: var(--tw-blue); filter: none; }
  .tw-dim { color: var(--tw-muted); }
  .tw-empty { color: var(--tw-muted); font-size: .92rem; padding: .4rem 0; }

  @media (max-width: 640px) {
    .tw-strip { grid-template-columns: 1fr; }
    .tw-cell { border-left: 0; }
    .tw-cell + .tw-cell { border-top: 1px solid var(--tw-line); }
    .tw-subs thead { display: none; }
    .tw-subs, .tw-subs tbody, .tw-subs tr, .tw-subs td { display: block; width: 100%; }
    .tw-subs tr { padding: .65rem 0; border-bottom: 1px solid var(--tw-line); }
    .tw-subs td { border: 0; padding: .12rem 0; }
    .tw-subs td[data-l] { display: flex; justify-content: space-between; gap: .75rem; font-size: .82rem; }
    .tw-subs td[data-l]::before { content: attr(data-l); color: var(--tw-muted); font-weight: 600; }
    .tw-subs .act { text-align: left; padding-top: .45rem; }
  }
  @media (max-width: 480px) {
    .tw-title { font-size: 1.4rem; }
    .tw-doc { padding: 1.1rem; }
    .tw-pn a { font-size: .82rem; }
    .tw-actions { width: 100%; } .tw-actions .tw-do { flex: 1; }
  }
</style>

<div class="section-panel active tw">
  <div class="tw-wrap">
    <?php if ($prevItem || $nextItem): ?>
      <nav class="tw-pn" aria-label="Previous and next in this module">
        <?php if ($prevItem): ?><a href="<?= htmlspecialchars($prevItem[1]) ?>"><?= $i('left') ?><span><?= htmlspecialchars($prevItem[0] . ': ' . $prevItem[2]) ?></span></a><?php endif; ?>
        <?php if ($nextItem): ?><a class="nx" href="<?= htmlspecialchars($nextItem[1]) ?>"><span>Next: <?= htmlspecialchars($nextItem[2]) ?></span><?= $i('right') ?></a><?php endif; ?>
      </nav>
    <?php endif; ?>

    <div class="tw-head">
      <div class="tw-titlebox">
        <h1 class="tw-title"><?= htmlspecialchars($item['title']) ?></h1>
        <?php if ($draft): ?><span class="tw-tag draft">Draft</span>
        <?php elseif ($isPast): ?><span class="tw-tag closed">Closed</span>
        <?php else: ?><span class="tw-tag open">Open</span><?php endif; ?>
      </div>
      <div class="tw-actions">
        <?php if ($isQuiz): ?>
          <button type="button" class="tw-do" onclick="viewResults(<?= $id ?>, <?= htmlspecialchars(json_encode($item['title']), ENT_QUOTES) ?>, <?= $num($max) ?>, <?= htmlspecialchars(json_encode($item['quiz_type']), ENT_QUOTES) ?>)"><?= ($item['quiz_type'] ?? '') === 'file' ? 'Grade submissions' : 'View results' ?><?= $toGrade > 0 ? ' (' . $toGrade . ')' : '' ?></button>
          <button type="button" class="tw-do alt" onclick="editQuiz(<?= $id ?>)">Edit quiz</button>
        <?php else: ?>
          <a class="tw-do" href="grade.php?id=<?= $id ?>&section=<?= rawurlencode($class['section']) ?>">Grade submissions<?= $toGrade > 0 ? ' (' . $toGrade . ')' : '' ?></a>
          <button type="button" class="tw-do alt" onclick='editAssignment(<?= $editAsg ?>)'>Edit assignment</button>
        <?php endif; ?>
      </div>
    </div>

    <div class="tw-strip">
      <div class="tw-cell"><span class="tw-cell-ico"><?= $i('cal') ?></span><div>
        <small><?= $isQuiz ? 'Closes' : 'Due' ?></small><b><?= htmlspecialchars($when($due) ?? ($isQuiz ? 'No closing date' : 'No due date')) ?></b>
      </div></div>
      <div class="tw-cell"><span class="tw-cell-ico"><?= $i('inbox') ?></span><div>
        <small>Submitted</small><b><?= $nSub ?> of <?= $nStu ?></b>
        <div class="tw-bar" role="img" aria-label="<?= $subPct ?> percent submitted"><span style="width:<?= $subPct ?>%"></span></div>
      </div></div>
      <div class="tw-cell"><span class="tw-cell-ico"><?= $i('check') ?></span><div>
        <small>To <?= $verb ?></small>
        <?php if ($toGrade > 0): ?><b class="wait"><?= $toGrade ?> waiting</b>
        <?php elseif ($nSub > 0): ?><b class="ok">All <?= $verb === 'grade' ? 'graded' : 'checked' ?></b>
        <?php else: ?><b>Nothing yet</b><?php endif; ?>
        <i><?= $nGraded ?> <?= $verb === 'grade' ? 'graded' : 'checked' ?></i>
      </div></div>
    </div>
    <div class="tw-fine"><?= htmlspecialchars(implode(' · ', $fine)) ?></div>

    <?php if ($hasInstr || $file): ?>
    <section class="tw-doc" id="wk-instructions" aria-labelledby="tw-todo">
      <h2 class="tw-h" id="tw-todo">Instructions for students</h2>
      <?php if ($hasInstr): ?>
        <div class="tw-body"><?php foreach (preg_split('/\R{2,}/', trim($instr)) as $para): ?><p><?= nl2br(htmlspecialchars($para)) ?></p><?php endforeach; ?></div>
      <?php endif; ?>
      <?php if ($file): ?>
        <a class="tw-file" href="../<?= htmlspecialchars($file) ?>" target="_blank" rel="noopener">
          <span class="tw-ext"<?= in_array($fileExt, ['DOC', 'DOCX'], true) ? ' style="background:#1d4ed8"' : (in_array($fileExt, ['PPT', 'PPTX'], true) ? ' style="background:#c2410c"' : (in_array($fileExt, ['XLS', 'XLSX', 'CSV'], true) ? ' style="background:#15803d"' : (in_array($fileExt, ['PNG', 'JPG', 'JPEG', 'GIF', 'WEBP'], true) ? ' style="background:#6d28d9"' : ''))) ?>><?= htmlspecialchars(substr($fileExt, 0, 4)) ?></span>
          <span class="tw-file-n"><?= htmlspecialchars(elmsFileLabel($file)) ?><small>Attached file · open</small></span>
        </a>
      <?php endif; ?>
    </section>
    <?php endif; ?>

    <section class="tw-doc" id="wk-submissions" aria-labelledby="tw-subs-h">
      <div class="tw-bar-top">
        <h2 class="tw-h" id="tw-subs-h">Students · <?= htmlspecialchars($class['section']) ?></h2>
        <?php if ($students): ?>
          <div class="tw-chips" role="group" aria-label="Filter students">
            <button type="button" class="tw-chip on" data-f="all" aria-pressed="true">All<b><?= $cnt['all'] ?></b></button>
            <button type="button" class="tw-chip" data-f="todo" aria-pressed="false">To <?= $verb ?><b><?= $cnt['todo'] ?></b></button>
            <button type="button" class="tw-chip" data-f="graded" aria-pressed="false"><?= $verb === 'grade' ? 'Graded' : 'Checked' ?><b><?= $cnt['graded'] ?></b></button>
            <button type="button" class="tw-chip" data-f="none" aria-pressed="false">Not submitted<b><?= $cnt['none'] ?></b></button>
          </div>
        <?php endif; ?>
      </div>
      <?php if (!$students): ?>
        <div class="tw-empty">No students are enrolled in this class yet.</div>
      <?php else: ?>
        <div class="tw-tablewrap">
        <table class="tw-subs">
          <thead><tr><th>Student</th><th>Status</th><th>Submitted</th><th>Score</th><th></th></tr></thead>
          <tbody>
            <?php foreach ($rows as $r):
              $s = $r['s']; $sid = $r['sid']; $sub = $r['sub'];
              $pill = $r['st'] === 'none' ? ['Not submitted', 'tw-p-none'] : ($r['st'] === 'graded' ? [$isQuiz ? 'Checked' : 'Graded', 'tw-p-ok'] : ['To ' . $verb, 'tw-p-warn']);
            ?>
              <tr data-st="<?= $r['st'] ?>">
                <td><strong><?= htmlspecialchars($s['last_name'] . ', ' . $s['first_name']) ?></strong><span class="lrn"><?= htmlspecialchars((string) $s['school_id']) ?></span></td>
                <td data-l="Status"><span class="tw-pill <?= $pill[1] ?>"><?= $pill[0] ?></span></td>
                <td data-l="Submitted"><?php
                  // No real submission (score given by the teacher): show when the grade was last edited
                  $shownAt = ($sub && (int) ($sub['tries'] ?? 1) === 0) ? ($sub['graded_at'] ?? $sub['submitted_at'] ?? null) : ($sub['submitted_at'] ?? null);
                  if ($sub && !empty($shownAt)): ?><span<?= (int) ($sub['tries'] ?? 1) === 0 ? ' title="Last edited"' : '' ?>><?= htmlspecialchars(date('M j, g:i a', strtotime($shownAt))) ?></span><?= $r['late'] ? '<span class="tw-pill tw-p-late">Late</span>' : '' ?><?php else: ?><span class="tw-dim">—</span><?php endif; ?></td>
                <td data-l="Score"><?= $sub && $sub['score'] !== null ? '<strong>' . $num($sub['score']) . '</strong><span class="tw-dim"> / ' . $num($max) . '</span>' : '<span class="tw-dim">— / ' . $num($max) . '</span>' ?></td>
                <td class="act">
                  <?php if ($sub && !$isQuiz): ?>
                    <a class="tw-btn<?= $sub['score'] !== null ? ' ghost' : '' ?>" href="grade.php?id=<?= $id ?>&student=<?= $sid ?>&section=<?= rawurlencode($class['section']) ?>"><?= $sub['score'] !== null ? 'View' : 'Grade' ?></a>
                  <?php elseif ($sub && !empty($sub['file_path'])): ?>
                    <a class="tw-btn ghost" href="../<?= htmlspecialchars($sub['file_path']) ?>" target="_blank" rel="noopener">Open file</a>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
        </div>
        <div class="tw-empty" id="tw-none" hidden>No students in this filter.</div>
      <?php endif; ?>
    </section>
  </div>
</div>

<script>
  (function () {
    // Filter the student list: All / To grade / Graded / Not submitted
    const chips = document.querySelectorAll('.tw-chip');
    const rows = document.querySelectorAll('.tw-subs tbody tr');
    const none = document.getElementById('tw-none');
    chips.forEach(c => c.addEventListener('click', () => {
      const f = c.dataset.f; let shown = 0;
      chips.forEach(x => { const on = x === c; x.classList.toggle('on', on); x.setAttribute('aria-pressed', on ? 'true' : 'false'); });
      rows.forEach(r => { const ok = f === 'all' || r.dataset.st === f; r.hidden = !ok; if (ok) shown++; });
      if (none) none.hidden = shown > 0;
    }));
    // After saving in the edit form (footer.php → mlAfterSave), reload to show the changes
    window.mlcRefresh = () => location.reload();
  })();
</script>

<?php require __DIR__ . '/../includes/footer.php'; ?>