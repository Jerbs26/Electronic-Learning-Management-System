<?php
require_once __DIR__ . '/../includes/upload_names.php';   // show original file names
// ============================================================
//  Arandia College eLMS — Teacher: Speed grader
//  File: teacher/grade.php?id=ASSIGNMENT_ID[&student=ID]
//  Left: every student in the class · Middle: their submission ·
//  Right: score + feedback with Save & next. Saves via
//  api/assignments.php?action=grade_one.
// ============================================================
require __DIR__ . '/_init.php';
$currentPage = 'assignments.php';

$tid = (int) $user_id;
$aid = (int) ($_GET['id'] ?? 0);
$st = $conn->prepare(
  "SELECT a.id, a.course_id, a.title, a.description, a.due_date, a.max_score, a.file_path, c.course_name, c.course_code
   FROM assignments a JOIN courses c ON c.id = a.course_id
   WHERE a.id = ? AND EXISTS (SELECT 1 FROM teacher_assignments ta WHERE ta.course_id = a.course_id AND ta.teacher_id = ?)
     AND " . scopeTeacherOwned($conn, 'a', 'assignments', $tid)
);
$st->bind_param('ii', $aid, $tid);
$st->execute();
$asg = $st->get_result()->fetch_assoc();
$st->close();
if (!$asg) {
  header('Location: assignments.php');
  exit;
}
$cid = (int) $asg['course_id'];
$max = (float) $asg['max_score'];
$maxLabel = rtrim(rtrim(number_format($max, 2, '.', ''), '0'), '.');

// Every student in this teacher's sections for the subject, with their submission (if any)
$st = $conn->prepare(
  "SELECT u.id, u.first_name, u.last_name, u.school_id, u.section_dept,
          s.id AS sub_id, s.file_path, s.remarks, s.score, s.submitted_at, s.attempt_count
   FROM users u
   JOIN enrollments e ON e.student_id = u.id AND e.course_id = ? AND e.status = 'Enrolled'
   LEFT JOIN submissions s ON s.assignment_id = ? AND s.student_id = u.id
   WHERE u.role = 'Student' AND u.status = 'Active'
     AND u.section_dept IN (SELECT section FROM teacher_assignments WHERE teacher_id = ? AND course_id = ?)
     AND (? = '' OR u.section_dept = ?)
   GROUP BY u.id
   ORDER BY u.section_dept, u.last_name, u.first_name"
);
$secFilter = trim($_GET['section'] ?? '');
$st->bind_param('iiiiss', $cid, $aid, $tid, $cid, $secFilter, $secFilter);
$st->execute();
$rows = $st->get_result()->fetch_all(MYSQLI_ASSOC);
$st->close();

$students = [];
foreach ($rows as $r) {
  $submitted = $r['sub_id'] && (int) $r['attempt_count'] > 0;
  $graded = $r['score'] !== null;
  $ext = $r['file_path'] ? strtolower(pathinfo($r['file_path'], PATHINFO_EXTENSION)) : '';
  $students[] = [
    'id' => (int) $r['id'],
    'name' => $r['last_name'] . ', ' . $r['first_name'],
    'initials' => strtoupper(substr($r['first_name'], 0, 1) . substr($r['last_name'], 0, 1)),
    'section' => $r['section_dept'],
    'lrn' => $r['school_id'],
    'submitted' => $submitted,
    'submitted_at' => $submitted ? date('M j, g:i A', strtotime($r['submitted_at'])) : null,
    'late' => $submitted && $asg['due_date'] && strtotime($r['submitted_at']) > strtotime($asg['due_date']),
    'attempts' => (int) $r['attempt_count'],
    'file' => $r['file_path'],
    'missing' => $r['file_path'] ? !is_file(__DIR__ . '/../' . $r['file_path']) : false,   // saved in the database but not in the uploads folder
    'file_name' => $r['file_path'] ? elmsFileLabel($r['file_path']) : null,
    'kind' => in_array($ext, ['pdf'], true) ? 'pdf' : (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true) ? 'image' : ($ext ? 'file' : '')),
    'score' => $graded ? rtrim(rtrim(number_format((float) $r['score'], 2, '.', ''), '0'), '.') : '',
    'feedback' => $r['remarks'] ?? '',
  ];
}
$sections = array_values(array_unique(array_column($students, 'section')));
$startId = (int) ($_GET['student'] ?? 0);

// Class sidebar (includes/class_nav.php): this assignment's subject, and the
// section when it is clear (named in the URL, only one, or the opened student's)
$classNavCourse = $cid;
$classNavSection = $secFilter;
if ($classNavSection === '' && count($sections) === 1) $classNavSection = (string) $sections[0];
if ($classNavSection === '' && $startId) foreach ($students as $s) if ($s['id'] === $startId) { $classNavSection = (string) $s['section']; break; }

$pageTitle = 'Grade · ' . $asg['title'];
require __DIR__ . '/../includes/header.php';
?>
<style>
  /* Speed grader: scoped under .sg, tokens first so dark mode only overrides variables */
  .sg { --g-brand: #003087; --g-brand-hover: #0a43b3; --g-soft: #eef3ff; --g-line: #e4e8ef; --g-panel: var(--white);
        --g-ok: #0f7b53; --g-warn: #a16207; --g-bad: #c0392b; --g-radius: 12px; padding-bottom: 5rem; }
  html[data-theme="dark"] .sg { --g-brand: #c7d7ff; --g-brand-hover: #e2eaff; --g-soft: rgba(143, 176, 255, .12); --g-line: rgba(255, 255, 255, .09);
                                --g-ok: #4cc38f; --g-warn: #e0b341; --g-bad: #ff8a7a; }

  /* Header */
  .sg-top { display: flex; align-items: flex-end; justify-content: space-between; gap: 1.5rem; flex-wrap: wrap; margin-bottom: .9rem; }
  .sg-back { display: inline-block; font-size: .8rem; font-weight: 600; color: var(--text-light); text-decoration: none; margin-bottom: .35rem; }
  .sg-back:hover { color: var(--g-brand); }
  .sg-title { font-family: 'Nunito', sans-serif; font-size: 1.4rem; font-weight: 800; letter-spacing: -.01em; color: var(--text-dark); line-height: 1.2; }
  .sg-meta { display: flex; flex-wrap: wrap; align-items: center; font-size: .8rem; color: var(--text-light); margin-top: .3rem; }
  .sg-meta span + span::before { content: ''; display: inline-block; width: 3px; height: 3px; border-radius: 50%; background: currentColor; opacity: .5; margin: 0 .55rem; vertical-align: middle; }
  .sg-progress { min-width: 170px; font-size: .8rem; color: var(--text-light); }
  .sg-progress b { color: var(--text-dark); font-weight: 800; font-variant-numeric: tabular-nums; }
  .sg-bar { height: 4px; border-radius: 100px; background: var(--g-line); overflow: hidden; margin-top: .4rem; }
  .sg-bar span { display: block; height: 100%; background: var(--g-ok); transition: width .25s ease; }

  /* Summary strip */
  .sg-strip { display: flex; flex-wrap: wrap; align-items: stretch; background: var(--g-panel); border: 1px solid var(--g-line); border-radius: var(--g-radius); margin-bottom: 1rem; }
  .sg-kpi { display: flex; align-items: baseline; gap: .45rem; padding: .6rem 1.1rem; border-right: 1px solid var(--g-line); }
  .sg-kpi b { font-family: 'Nunito', sans-serif; font-size: 1.05rem; font-weight: 800; color: var(--text-dark); font-variant-numeric: tabular-nums; }
  .sg-kpi span { font-size: .76rem; font-weight: 600; color: var(--text-light); }
  .sg-kpi.warn b { color: var(--g-warn); } .sg-kpi.ok b { color: var(--g-ok); }
  .sg-instr { margin-left: auto; display: flex; flex-direction: column; justify-content: center; padding: .6rem 1.1rem; font-size: .84rem; color: var(--text-dark); }
  .sg-instr summary { cursor: pointer; font-size: .8rem; font-weight: 700; color: var(--g-brand); list-style: none; }
  .sg-instr summary::-webkit-details-marker { display: none; }
  .sg-instr summary::before { content: '▸'; display: inline-block; margin-right: .35rem; transition: transform .15s; }
  .sg-instr[open] { flex: 1 0 100%; margin-left: 0; border-top: 1px solid var(--g-line); }
  .sg-instr[open] summary::before { transform: rotate(90deg); }
  .sg-instr div { margin-top: .5rem; line-height: 1.6; max-width: 80ch; color: var(--text-light); }

  /* Layout */
  .sg-grid { display: grid; grid-template-columns: 292px minmax(0, 1fr) 296px; gap: 1rem; align-items: start; }
  .sg-card { background: var(--g-panel); border: 1px solid var(--g-line); border-radius: var(--g-radius); overflow: hidden; }

  /* Student list */
  .sg-list { position: sticky; top: 80px; max-height: calc(100vh - 110px); display: flex; flex-direction: column; }
  .sg-chips { display: flex; gap: .15rem; padding: .55rem; border-bottom: 1px solid var(--g-line); }
  .sg-chip { flex: 1 1 auto; border: 0; background: none; border-radius: 8px; padding: .32rem .35rem; font: inherit; font-size: .74rem; font-weight: 600; color: var(--text-light); cursor: pointer; white-space: nowrap; }
  .sg-chip span { font-variant-numeric: tabular-nums; opacity: .7; margin-left: .1rem; }
  .sg-chip:hover { background: var(--bg-light); color: var(--text-dark); }
  .sg-chip.on { background: var(--g-soft); color: var(--g-brand); }
  .sg-students { overflow-y: auto; }
  .sg-stu { display: flex; align-items: center; gap: .65rem; width: 100%; padding: .6rem .85rem; background: none; border: 0; border-bottom: 1px solid var(--g-line); border-left: 3px solid transparent;
            text-align: left; cursor: pointer; font: inherit; color: inherit; }
  .sg-stu:last-child { border-bottom: 0; }
  .sg-stu:hover { background: var(--bg-light); }
  .sg-stu.on { background: var(--g-soft); border-left-color: var(--g-brand); }
  .sg-stu:focus-visible { outline: 2px solid var(--g-brand); outline-offset: -2px; }
  .sg-av { width: 30px; height: 30px; border-radius: 50%; background: var(--g-soft); color: var(--g-brand); font-weight: 700; font-size: .7rem; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
  .sg-stu-n { font-size: .84rem; font-weight: 600; color: var(--text-dark); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
  .sg-stu.on .sg-stu-n { font-weight: 700; }
  .sg-stu-s { font-size: .72rem; color: var(--text-light); font-variant-numeric: tabular-nums; }
  .sg-stu-s.done { color: var(--g-ok); } .sg-stu-s.todo { color: var(--g-warn); }
  .sg-dot { width: 8px; height: 8px; border-radius: 50%; margin-left: auto; flex-shrink: 0; background: #b6beca; }
  .sg-dot.todo { background: var(--g-warn); } .sg-dot.done { background: var(--g-ok); }
  .sg-sec { font-size: .7rem; font-weight: 700; color: var(--text-light); padding: .7rem .85rem .3rem; background: var(--bg-light); border-bottom: 1px solid var(--g-line); }

  /* Submission viewer */
  .sg-doc-h { display: flex; justify-content: space-between; align-items: center; gap: 1rem; padding: .75rem 1.1rem; border-bottom: 1px solid var(--g-line); flex-wrap: wrap; }
  .sg-doc-n { font-size: .95rem; font-weight: 700; color: var(--text-dark); }
  .sg-doc-m { display: flex; align-items: center; gap: .6rem; font-size: .76rem; color: var(--text-light); margin-top: .25rem; }
  .sg-tag { font-size: .68rem; font-weight: 700; padding: .08rem .5rem; border-radius: 100px; }
  .sg-tag.ok { color: var(--g-ok); background: color-mix(in srgb, var(--g-ok) 12%, transparent); }
  .sg-tag.late { color: var(--g-bad); background: color-mix(in srgb, var(--g-bad) 12%, transparent); }
  .sg-late { color: var(--g-bad); font-weight: 700; }
  .sg-doc-a { display: flex; align-items: center; gap: .6rem; min-width: 0; }
  .sg-file { max-width: 220px; font-size: .76rem; color: var(--text-light); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
  .sg-view { height: calc(100vh - 200px); min-height: 480px; background: var(--bg-light); display: flex; align-items: center; justify-content: center; }
  .sg-view iframe { width: 100%; height: 100%; border: 0; background: var(--ui-surface, #fff); }
  .sg-view img { max-width: 100%; max-height: 100%; object-fit: contain; }
  .sg-empty { text-align: center; color: var(--text-light); font-size: .88rem; line-height: 1.55; padding: 2rem; max-width: 360px; }
  .sg-empty strong { display: block; color: var(--text-dark); font-size: .98rem; margin-bottom: .3rem; }

  /* Buttons */
  .sg-btn { display: inline-flex; align-items: center; justify-content: center; gap: .35rem; padding: .5rem .9rem; border-radius: 8px; border: 1px solid var(--g-line);
            background: var(--g-panel); color: var(--text-dark); font: inherit; font-size: .82rem; font-weight: 600; text-decoration: none; cursor: pointer; transition: background .12s, border-color .12s; }
  .sg-btn:hover { background: var(--bg-light); border-color: #cbd3df; }
  .sg-btn:focus-visible, .sg-chip:focus-visible, .sg-quick button:focus-visible { outline: 2px solid var(--g-brand); outline-offset: 2px; }
  .sg-btn.primary { background: var(--g-brand); border-color: var(--g-brand); color: #fff; }
  html[data-theme="dark"] .sg-btn.primary { color: #0b1b4a; }
  .sg-btn.primary:hover { background: var(--g-brand-hover); border-color: var(--g-brand-hover); }
  .sg-btn.ghost { border-color: transparent; background: none; color: var(--text-light); }
  .sg-btn.ghost:hover { background: var(--bg-light); color: var(--text-dark); }
  .sg-btn:disabled { opacity: .45; cursor: default; pointer-events: none; }

  /* Grading panel */
  .sg-side { position: sticky; top: 80px; padding: 1.1rem; display: flex; flex-direction: column; gap: 1.1rem; }
  .sg-lbl { display: flex; align-items: baseline; justify-content: space-between; gap: .5rem; font-size: .78rem; font-weight: 700; color: var(--text-dark); margin-bottom: .4rem; }
  .sg-hint { font-weight: 500; color: var(--text-light); font-size: .72rem; }
  .sg-score { display: flex; align-items: baseline; gap: .5rem; }
  .sg-score input { width: 88px; height: 44px; text-align: center; font-size: 1.35rem; font-weight: 800; border: 1px solid var(--g-line); border-radius: 8px;
                    background: var(--g-panel); color: var(--text-dark); font-family: 'Nunito', sans-serif; font-variant-numeric: tabular-nums; }
  .sg-score input:focus, .sg-fb:focus { outline: none; border-color: var(--g-brand); box-shadow: 0 0 0 3px var(--g-soft); }
  .sg-of { font-size: .95rem; font-weight: 600; color: var(--text-light); }
  .sg-quick { display: flex; gap: .35rem; flex-wrap: wrap; margin-top: .55rem; }
  .sg-quick button { border: 1px solid var(--g-line); background: none; border-radius: 6px; padding: .18rem .55rem; font: inherit; font-size: .74rem; font-weight: 600; color: var(--text-light); cursor: pointer; font-variant-numeric: tabular-nums; }
  .sg-quick button:hover { color: var(--g-brand); border-color: var(--g-brand); }
  .sg-fb { width: 100%; min-height: 130px; resize: vertical; border: 1px solid var(--g-line); border-radius: 8px; padding: .65rem .75rem; font: inherit; font-size: .86rem; line-height: 1.5;
           background: var(--g-panel); color: var(--text-dark); box-sizing: border-box; }
  .sg-err { display: none; font-size: .8rem; color: var(--g-bad); font-weight: 600; }
  .sg-actions { display: flex; flex-direction: column; gap: .5rem; }
  .sg-actions .sg-btn { width: 100%; padding: .6rem .9rem; }
  .sg-nav { display: flex; justify-content: space-between; }
  .sg-nav .sg-btn { width: auto; padding: .35rem .6rem; font-size: .78rem; }
  .sg-saved { font-size: .78rem; color: var(--g-ok); font-weight: 600; min-height: 1em; margin-top: -.4rem; }
  .sg-kb { display: flex; flex-direction: column; gap: .3rem; font-size: .72rem; color: var(--text-light); border-top: 1px solid var(--g-line); padding-top: .85rem; }
  .sg-kb kbd { border: 1px solid var(--g-line); border-bottom-width: 2px; border-radius: 4px; padding: 0 .3rem; font: inherit; font-size: .68rem; background: var(--bg-light); }
  .sg-ref { font-size: .8rem; }
  .sg-ref a { color: var(--g-brand); font-weight: 600; }

  @media (max-width: 1250px) { .sg-grid { grid-template-columns: 260px minmax(0, 1fr); } .sg-side-wrap { grid-column: 1 / -1; } .sg-side { position: static; } }
  @media (max-width: 760px) { .sg-grid { grid-template-columns: minmax(0, 1fr); } .sg-list { position: static; max-height: 280px; } .sg-view { height: 60vh; min-height: 360px; } .sg-kpi { flex: 1 1 33%; border-right: 0; } .sg-instr { margin-left: 0; flex: 1 0 100%; border-top: 1px solid var(--g-line); } }
</style>

<div class="section-panel active sg">
  <div class="sg-top">
    <div>
      <a class="sg-back" href="assignments.php?course=<?= $cid ?><?= $secFilter !== '' ? '&section=' . urlencode($secFilter) : '' ?>">← Assignments</a>
      <div class="sg-title"><?= htmlspecialchars($asg['title']) ?></div>
      <div class="sg-meta">
        <span><?= htmlspecialchars($asg['course_name']) ?></span>
        <span><?= htmlspecialchars(implode(', ', $sections) ?: 'No students yet') ?></span>
        <span><?= $maxLabel ?> points</span>
        <span><?= $asg['due_date'] ? 'Due ' . date('D, M j, g:i A', strtotime($asg['due_date'])) : 'No due date' ?></span>
      </div>
    </div>
    <div class="sg-progress"><b id="sg-graded">0</b> of <?= count($students) ?> graded<div class="sg-bar"><span id="sg-bar" style="width:0"></span></div></div>
  </div>

  <div class="sg-strip">
    <div class="sg-kpi"><b id="st-sub">0</b><span>Submitted</span></div>
    <div class="sg-kpi warn"><b id="st-pend">0</b><span>To grade</span></div>
    <div class="sg-kpi ok"><b id="st-grad">0</b><span>Graded</span></div>
    <?php if ($asg['description']): ?>
      <details class="sg-instr"><summary>Instructions</summary><div><?= nl2br(htmlspecialchars($asg['description'])) ?></div></details>
    <?php endif; ?>
  </div>

  <?php if (!$students): ?>
    <div class="sg-card"><div class="sg-empty" style="margin:0 auto;"><strong>No students in this class yet</strong>Students appear here once they are enrolled in <?= htmlspecialchars($asg['course_name']) ?>.</div></div>
  <?php else: ?>
  <div class="sg-grid">
    <div class="sg-card sg-list">
      <div class="sg-chips" role="tablist" aria-label="Filter students">
        <button class="sg-chip on" data-f="all">All<span id="c-all"></span></button>
        <button class="sg-chip" data-f="todo">To grade<span id="c-todo"></span></button>
        <button class="sg-chip" data-f="missing">Missing<span id="c-missing"></span></button>
        <button class="sg-chip" data-f="done">Graded<span id="c-done"></span></button>
      </div>
      <div class="sg-students" id="sg-students"></div>
    </div>

    <div class="sg-card">
      <div class="sg-doc-h">
        <div><div class="sg-doc-n" id="sg-name"></div><div class="sg-doc-m" id="sg-meta"></div></div>
        <div class="sg-doc-a"><span class="sg-file" id="sg-file"></span><a class="sg-btn" id="sg-download" target="_blank" rel="noopener">Download</a></div>
      </div>
      <div class="sg-view" id="sg-view"></div>
    </div>

    <div class="sg-side-wrap">
      <div class="sg-card sg-side">
        <div>
          <label class="sg-lbl" for="sg-score">Score</label>
          <div class="sg-score"><input id="sg-score" inputmode="numeric" autocomplete="off" placeholder="—"><span class="sg-of">/ <?= $maxLabel ?></span></div>
          <div class="sg-quick" id="sg-quick" aria-label="Quick scores"></div>
        </div>
        <div>
          <label class="sg-lbl" for="sg-fb">Feedback <span class="sg-hint">Visible to the student</span></label>
          <textarea class="sg-fb" id="sg-fb" maxlength="2000" placeholder="What did they do well? What can they improve?"></textarea>
        </div>
        <div class="sg-err" id="sg-err" role="alert"></div>
        <div class="sg-actions">
          <button class="sg-btn primary" id="sg-save-next">Save and next</button>
          <button class="sg-btn" id="sg-save">Save grade</button>
          <div class="sg-nav"><button class="sg-btn ghost" id="sg-prev">← Previous</button><button class="sg-btn ghost" id="sg-skip">Skip →</button></div>
        </div>
        <div class="sg-saved" id="sg-saved" aria-live="polite"></div>
        <div class="sg-kb">
          <span><kbd>Enter</kbd> in the score box or <kbd>Ctrl</kbd> <kbd>Enter</kbd> to save and go next</span>
          <span><kbd>Alt</kbd> <kbd>↑</kbd> <kbd>↓</kbd> to switch student</span>
        </div>
        <?php if ($asg['file_path']): ?>
          <div class="sg-ref"><a href="../<?= htmlspecialchars($asg['file_path']) ?>" target="_blank" rel="noopener">Open assignment instructions file</a></div>
        <?php endif; ?>
      </div>
    </div>
  </div>
  <?php endif; ?>
</div>

<script>
(() => {
  const AID = <?= $aid ?>, MAX = <?= json_encode($max) ?>;
  const S = <?= json_encode($students, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
  const MULTI = <?= count($sections) > 1 ? 'true' : 'false' ?>;
  const SEC = <?= json_encode($secFilter) ?>;
  if (!S.length) return;
  let filter = 'all', cur = 0, dirty = false;
  let sgLast = '', sgHintTimer = null;   // score box: last accepted value (see the score box rules below)
  const $ = id => document.getElementById(id);
  const esc = s => { const d = document.createElement('div'); d.textContent = s ?? ''; return d.innerHTML; };
  const status = s => s.score !== '' ? 'done' : (s.submitted ? 'todo' : 'missing');
  const statusText = s => s.score !== '' ? `Graded · ${s.score}/${MAX % 1 ? MAX : Math.round(MAX)}` : (s.submitted ? 'To grade' : 'Missing');

  // Quick scores: 100%, 90%, 80%, 75%, 0
  const qs = [1, .9, .8, .75, 0].map(p => Math.min(Math.floor(MAX), Math.round(MAX * p)));
  $('sg-quick').innerHTML = [...new Set(qs)].map(v => `<button type="button" data-v="${v}">${v}</button>`).join('');
  $('sg-quick').addEventListener('click', e => { const b = e.target.closest('button'); if (b) { $('sg-score').value = b.dataset.v; sgLast = b.dataset.v; dirty = true; $('sg-err').style.display = 'none'; $('sg-score').focus(); } });

  function visible() { return S.map((s, i) => i).filter(i => filter === 'all' || status(S[i]) === filter); }
  function renderList() {
    const counts = { all: S.length, todo: 0, missing: 0, done: 0 };
    S.forEach(s => counts[status(s)]++);
    Object.keys(counts).forEach(k => $('c-' + k).textContent = counts[k]);
    $('sg-graded').textContent = counts.done;
    $('st-sub').textContent = S.filter(s => s.submitted).length;
    $('st-pend').textContent = counts.todo;
    $('st-grad').textContent = counts.done;
    $('sg-bar').style.width = (counts.done / S.length * 100) + '%';
    let html = '', sec = null;
    visible().forEach(i => {
      const s = S[i];
      if (MULTI && s.section !== sec) { sec = s.section; html += `<div class="sg-sec">${esc(sec)}</div>`; }
      html += `<button type="button" class="sg-stu${i === cur ? ' on' : ''}" data-i="${i}">
        <span class="sg-av">${esc(s.initials)}</span>
        <span style="min-width:0;"><span class="sg-stu-n" style="display:block;">${esc(s.name)}</span><span class="sg-stu-s ${status(s)}">${esc(statusText(s))}</span></span>
        <span class="sg-dot ${status(s) === 'missing' ? '' : status(s)}"></span></button>`;
    });
    $('sg-students').innerHTML = html || '<div class="sg-empty" style="padding:1.5rem 1rem;font-size:.82rem;">No students here.</div>';
  }
  function show(i) {
    cur = i; dirty = false;
    const s = S[i];
    $('sg-name').textContent = s.name;
    $('sg-meta').innerHTML = s.submitted
      ? `<span>Submitted ${esc(s.submitted_at)}</span><span class="sg-tag ${s.late ? 'late' : 'ok'}">${s.late ? 'Late' : 'On time'}</span>${s.attempts > 1 ? `<span>${s.attempts} attempts</span>` : ''}`
      : `<span>${s.score !== '' ? 'Graded without a submission' : 'Not submitted yet'}</span>`;
    const fl = $('sg-file'); fl.textContent = s.file_name || ''; fl.title = s.file_name || ''; fl.style.display = s.file_name ? '' : 'none';
    const dl = $('sg-download');
    if (s.file && !s.missing) { dl.href = '../' + s.file; dl.download = s.file_name || ''; dl.style.display = ''; } else { dl.removeAttribute('download'); dl.style.display = 'none'; }   // saved with the student's own file name
    const v = $('sg-view');
    if (s.file && s.missing) v.innerHTML = `<div class="sg-empty"><strong>The submitted file can't be found on the server</strong>${esc(s.file_name)} was recorded for this submission, but it is not in the <b>uploads/submissions</b> folder. Ask the student to submit again.</div>`;
    else if (s.file && s.kind === 'pdf') v.innerHTML = `<iframe src="../${encodeURI(s.file)}#view=FitH" title="Submission from ${esc(s.name)}"></iframe>`;
    else if (s.file && s.kind === 'image') v.innerHTML = `<img src="../${encodeURI(s.file)}" alt="Submission from ${esc(s.name)}">`;
    else if (s.file) v.innerHTML = `<div class="sg-empty"><strong>${esc(s.file_name)}</strong>This file type can't be shown here. Use <b>Download</b> to open it.</div>`;
    else v.innerHTML = `<div class="sg-empty"><strong>No submission yet</strong>You can still give a score, for example 0 for a missing requirement, or leave feedback.</div>`;
    $('sg-score').value = s.score;
    sgLast = s.score;                       // what the box goes back to when a key is refused
    $('sg-fb').value = s.feedback;
    $('sg-err').style.display = 'none';
    $('sg-saved').textContent = '';
    $('sg-prev').disabled = !prevIndex();
    renderList();
    const on = document.querySelector('.sg-stu.on'); if (on) on.scrollIntoView({ block: 'nearest' });
    history.replaceState(null, '', `grade.php?id=${AID}&student=${s.id}` + (SEC ? '&section=' + encodeURIComponent(SEC) : ''));
  }
  const order = () => S.map((s, i) => i);
  function nextIndex(from, onlyTodo) {
    const o = order(), start = o.indexOf(from);
    for (let k = 1; k < o.length; k++) { const i = o[(start + k) % o.length]; if (!onlyTodo || status(S[i]) === 'todo') return i; }
    return null;
  }
  function prevIndex() { return cur > 0 ? String(cur - 1) : null; }

  async function save(goNext) {
    const s = S[cur], fb = $('sg-fb').value.trim();
    let raw = cleanScore($('sg-score').value.trim());
    const err = $('sg-err');
    if (raw !== '' && raw !== s.score) {   // a saved score the teacher did not touch is left as it is
      const msg = scoreError(raw);
      if (msg) { err.textContent = msg; err.style.display = 'block'; $('sg-score').focus(); return; }
      raw = String(Number(raw));
    }
    if (raw === s.score && fb === s.feedback) { if (goNext) advance(); else $('sg-saved').textContent = 'No changes to save.'; return; }
    const btn = $(goNext ? 'sg-save-next' : 'sg-save'); btn.disabled = true;
    try {
      const res = await fetch('../api/assignments.php?action=grade_one', {
        method: 'POST', headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ assignment_id: AID, student_id: s.id, score: raw, feedback: fb })
      });
      const d = await res.json();
      if (!d.success) { err.textContent = d.message || 'Could not save.'; err.style.display = 'block'; return; }
      s.score = raw === '' ? '' : String(Number(raw)); s.feedback = fb; dirty = false;
      $('sg-saved').textContent = `${String(d.message || 'Saved').replace(/\.+$/, '')} for ${s.name.split(',')[0]}. Gradebook updated.`;
      // Tell an open Grades tab to refresh now (teacher/grades.php live analytics)
      try { if ('BroadcastChannel' in window) { const ch = new BroadcastChannel('elms_grades'); ch.postMessage({ t: 'graded', at: Date.now() }); ch.close(); } } catch (e) { }
      renderList();
      if (goNext) advance();
    } catch (e) { err.textContent = 'Network error. Try again.'; err.style.display = 'block'; }
    finally { btn.disabled = false; }
  }
  function advance() {
    const n = nextIndex(cur, true) ?? nextIndex(cur, false);
    if (n === null || n === cur) { $('sg-saved').textContent = 'Everyone is graded.'; return; }
    const msg = $('sg-saved').textContent; show(n); $('sg-saved').textContent = msg;
    if (S[n].submitted || S[n].score === '') $('sg-score').focus();
  }
  function go(i) {
    if (dirty && !confirm('You have unsaved changes for this student. Leave without saving?')) return;
    show(i);
  }

  $('sg-students').addEventListener('click', e => { const b = e.target.closest('.sg-stu'); if (b) go(Number(b.dataset.i)); });
  document.querySelectorAll('.sg-chip').forEach(c => c.addEventListener('click', () => {
    document.querySelectorAll('.sg-chip').forEach(x => x.classList.toggle('on', x === c));
    filter = c.dataset.f; renderList();
  }));
  // Score box: whole numbers from 0 up to the max score only. A key that would make it
  // anything else (a decimal point, a letter, a number above the max) is simply not taken:
  // the box keeps what it had and a short hint says why. Typed or pasted, same rule.
  const MAXLBL = MAX % 1 ? MAX : Math.round(MAX);
  const MAXINT = Math.max(0, Math.floor(MAX));
  $('sg-score').maxLength = Math.max(1, String(MAXINT).length);
  function cleanScore(v) { return String(v).replace(/[^\d.,]/g, ''); }
  function sgHint(msg) {
    const err = $('sg-err');
    err.textContent = msg; err.style.display = 'block';
    clearTimeout(sgHintTimer);
    sgHintTimer = setTimeout(() => { if (err.textContent === msg) err.style.display = 'none'; }, 2500);
  }
  function scoreError(raw) {
    if (raw === '') return '';
    if (!/^\d+$/.test(raw)) return 'Whole numbers only, no decimals (e.g. 87).';
    if (Number(raw) > MAX) return `Enter a score from 0 to ${MAXLBL}.`;
    return '';
  }
  // Decimal point, comma, minus, plus and "e" never go into the box
  $('sg-score').addEventListener('keydown', e => {
    if (e.ctrlKey || e.metaKey || e.altKey || e.key.length !== 1) return;   // shortcuts, arrows, Backspace …
    if (!/\d/.test(e.key)) { e.preventDefault(); sgHint(/[.,]/.test(e.key) ? 'Whole numbers only — no decimals.' : 'Numbers only.'); }
  });
  $('sg-score').addEventListener('input', () => {
    const el = $('sg-score'), v = el.value, err = $('sg-err');
    const saved = S[cur] ? S[cur].score : '';
    if (v === saved) { sgLast = v; err.style.display = 'none'; return; }   // back to the saved score (even an older decimal one)
    if (/[.,]/.test(v)) { el.value = sgLast; sgHint('Whole numbers only — no decimals.'); return; }   // e.g. a pasted "85.5"
    let c = v.replace(/\D+/g, '');
    if (c.length > 1) c = c.replace(/^0+(?=\d)/, '');                     // "007" -> "7"
    if (c !== '' && Number(c) > MAXINT) { el.value = sgLast; sgHint(`The highest score is ${MAXINT}.`); return; }
    if (c !== v) el.value = c;
    sgLast = c;
    dirty = true;
    err.style.display = 'none';
  });
  $('sg-fb').addEventListener('input', () => dirty = true);
  $('sg-save').addEventListener('click', () => save(false));
  $('sg-save-next').addEventListener('click', () => save(true));
  $('sg-skip').addEventListener('click', () => { const n = nextIndex(cur, false); if (n !== null) go(n); });
  $('sg-prev').addEventListener('click', () => { if (cur > 0) go(cur - 1); });
  $('sg-score').addEventListener('keydown', e => { if (e.key === 'Enter') { e.preventDefault(); save(true); } });
  document.addEventListener('keydown', e => {
    if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') { e.preventDefault(); save(true); }
    if (e.altKey && e.key === 'ArrowDown') { e.preventDefault(); const n = nextIndex(cur, false); if (n !== null) go(n); }
    if (e.altKey && e.key === 'ArrowUp') { e.preventDefault(); if (cur > 0) go(cur - 1); }
  });
  window.addEventListener('beforeunload', e => { if (dirty) { e.preventDefault(); e.returnValue = ''; } });

  const startId = <?= $startId ?>;
  let first = S.findIndex(s => s.id === startId);
  if (first < 0) first = S.findIndex(s => status(s) === 'todo');
  show(first < 0 ? 0 : first);
})();
</script>

<?php require __DIR__ . '/../includes/footer.php'; ?>