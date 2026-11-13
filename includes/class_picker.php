<?php
// ============================================================
//  Arandia College eLMS — Teacher class picker
//  File: includes/class_picker.php
//
//  A small widget next to the page title. Clicking it opens a mini page:
//    Step 1 — choose a section   ·   Step 2 — choose a subject
//  It drives the page's existing (now hidden) <select id="…"> by setting its
//  value and firing "change", so each page's own loader keeps working.
//  The chosen section is available to page scripts as window.classSection().
//
//  Usage (after $mySubjects is loaded by teacher/_init.php):
//    renderClassPicker($conn, $mySubjects, (int) $user_id, 'assign-course-select', 'asg');
//  Modes (decide the badge): asg = to grade · grd = to grade · quiz = to check · att = taken today
// ============================================================

if (!function_exists('renderClassBack')) {
  /**
   * "← My Classes" link at the top of the class work pages (Assignments, Quizzes,
   * Grades, Attendance). These pages are opened from a class's tabs, so the link
   * goes back to that class when the URL names one of the teacher's classes
   * (?course=ID&section=NAME), otherwise to the My Classes list.
   */
  function renderClassBack(array $mySubjects): void
  {
    // The class sidebar (includes/class_nav.php) already leads back to the class
    if (!empty($GLOBALS['__classNavRendered'])) return;
    $cid = (int) ($_GET['course'] ?? 0);
    $sec = trim((string) ($_GET['section'] ?? ''));
    $href = 'classes.php';
    $label = 'My Classes';
    foreach ($mySubjects as $s) {
      if ((int) $s['course_id'] === $cid && ($sec === '' || $s['section'] === $sec)) {
        $href = 'classes.php?class=' . (int) $s['assign_id'];
        $label = $s['course_name'] . ' · ' . $s['section'];
        break;
      }
    }
    echo '<a class="cp-classback" href="' . htmlspecialchars($href) . '">&larr; ' . htmlspecialchars($label) . '</a>';
  }
}

if (!function_exists('renderClassPicker')) {
  function renderClassPicker(mysqli $conn, array $mySubjects, int $teacherId, string $selectId, string $mode): void
  {
    // One entry per subject + section the teacher handles
    $classes = [];
    foreach ($mySubjects as $s) {
      $key = $s['course_id'] . '|' . $s['section'];
      if (isset($classes[$key])) continue;
      $code = (string) $s['course_code'];
      $short = strtoupper(substr(strrchr($code, '-') ?: $code, strrchr($code, '-') ? 1 : 0, 4));
      $classes[$key] = ['course' => (int) $s['course_id'], 'section' => $s['section'], 'code' => $code,
                        'name' => $s['course_name'], 'short' => $short ?: 'SUB', 'badge' => 0];
    }

    // Badge counts, per subject + section
    if ($classes) {
      $cIn = implode(',', array_unique(array_map(fn($c) => $c['course'], $classes)));
      $sql = null;
      if ($mode === 'asg' || $mode === 'grd') {
        $hasAtt = (bool) $conn->query("SHOW COLUMNS FROM submissions LIKE 'attempt_count'")->num_rows;
        $sql = "SELECT a.course_id, u.section_dept AS section, COUNT(*) AS n
                FROM submissions s JOIN assignments a ON a.id = s.assignment_id JOIN users u ON u.id = s.student_id
                WHERE a.course_id IN ($cIn) AND s.score IS NULL" . ($hasAtt ? " AND s.attempt_count > 0" : "") . "
                GROUP BY a.course_id, u.section_dept";
      } elseif ($mode === 'quiz') {
        $sql = "SELECT q.course_id, u.section_dept AS section, COUNT(*) AS n
                FROM quiz_attempts qa JOIN quizzes q ON q.id = qa.quiz_id JOIN users u ON u.id = qa.student_id
                WHERE q.course_id IN ($cIn) AND qa.status = 'Submitted' AND qa.score IS NULL
                GROUP BY q.course_id, u.section_dept";
      } elseif ($mode === 'att') {
        $today = date('Y-m-d');
        $sql = "SELECT a.course_id, u.section_dept AS section, COUNT(*) AS n
                FROM attendance a JOIN users u ON u.id = a.student_id
                WHERE a.course_id IN ($cIn) AND a.`date` = '$today'
                GROUP BY a.course_id, u.section_dept";
      }
      try {
        $res = $sql ? $conn->query($sql) : false;
        while ($res && $r = $res->fetch_assoc()) {
          $k = $r['course_id'] . '|' . $r['section'];
          if (isset($classes[$k])) $classes[$k]['badge'] = (int) $r['n'];
        }
      } catch (Throwable $e) {
        error_log('[class_picker] ' . $e->getMessage());
      }
    }
    $list = array_values($classes);
    $uid = $teacherId;
    ?>
<style>
  .cp-btn { display: inline-flex; align-items: center; gap: .65rem; padding: .45rem .8rem .45rem .45rem; border-radius: 14px; border: 1px solid rgba(15, 23, 42, .12);
            background: var(--white); cursor: pointer; font: inherit; text-align: left; color: var(--text-dark); transition: border-color .15s, box-shadow .15s; }
  .cp-btn:hover { border-color: #003087; box-shadow: 0 2px 10px rgba(0, 48, 135, .08); }
  .cp-btn:focus-visible { outline: 2px solid #003087; outline-offset: 2px; }
  .cp-btn[hidden] { display: none !important; }
  .cp-btn.empty { border-style: dashed; border-color: #003087; }
  html[data-theme="dark"] .cp-btn { border-color: rgba(255, 255, 255, .14); }
  .cp-ic { width: 38px; height: 38px; border-radius: 10px; background: #eef3ff; color: #003087; font-weight: 900; font-size: .72rem; letter-spacing: .02em;
           display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
  html[data-theme="dark"] .cp-ic { background: rgba(143, 176, 255, .14); color: #c7d7ff; }
  .cp-name { font-weight: 800; font-size: .92rem; line-height: 1.2; }
  .cp-sub { font-size: .74rem; color: var(--text-light); }
  .cp-caret { color: var(--text-light); margin-left: .2rem; font-size: .8rem; }
  .cp-ov { position: fixed; inset: 0; background: rgba(15, 23, 42, .45); display: none; align-items: flex-start; justify-content: center; padding: 10vh 1rem 1rem; z-index: 700; }
  .cp-ov.show { display: flex; }
  .cp-modal { width: 100%; max-width: 560px; background: var(--white); border-radius: 18px; box-shadow: 0 24px 60px rgba(15, 23, 42, .25); overflow: hidden; }
  .cp-head { display: flex; align-items: center; gap: .75rem; padding: 1rem 1.2rem; border-bottom: 1px solid rgba(15, 23, 42, .08); }
  .cp-back, .cp-close { width: 34px; height: 34px; border-radius: 10px; border: 1px solid rgba(15, 23, 42, .1); background: var(--white); color: var(--text-dark);
                        display: flex; align-items: center; justify-content: center; cursor: pointer; font-size: 1rem; }
  .cp-close { margin-left: auto; border-color: transparent; color: var(--text-light); }
  .cp-title { font-family: 'Nunito', sans-serif; font-weight: 900; font-size: 1.05rem; color: var(--text-dark); }
  .cp-step { font-size: .76rem; color: var(--text-light); }
  .cp-steps { display: flex; gap: .4rem; padding: .8rem 1.2rem 0; }
  .cp-steps span { flex: 1; height: 4px; border-radius: 4px; background: rgba(15, 23, 42, .08); }
  .cp-steps span.on { background: #003087; }
  .cp-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: .7rem; padding: 1rem 1.2rem 1.2rem; max-height: 60vh; overflow-y: auto; }
  .cp-card { display: flex; align-items: center; gap: .7rem; padding: .8rem; border-radius: 14px; border: 1px solid rgba(15, 23, 42, .1); background: var(--white);
             cursor: pointer; text-align: left; font: inherit; color: var(--text-dark); }
  .cp-card:hover { border-color: #003087; background: var(--bg-light); }
  .cp-card:focus-visible { outline: 2px solid #003087; outline-offset: 2px; }
  .cp-card.on { border-color: #003087; background: #eef3ff; }
  html[data-theme="dark"] .cp-card.on { background: rgba(143, 176, 255, .12); }
  .cp-card b { display: block; font-size: .9rem; font-weight: 800; }
  .cp-pill { display: inline-block; margin-top: .2rem; font-size: .7rem; font-weight: 800; padding: .1rem .55rem; border-radius: 100px; background: #fff5d9; color: #b38600; }
  .cp-pill.ok { background: #e6f6ef; color: #00875a; }
  .cp-muted { font-size: .74rem; color: var(--text-light); }
  .cp-hidden-select { position: absolute !important; width: 1px; height: 1px; overflow: hidden; clip: rect(0 0 0 0); white-space: nowrap; border: 0; }
  @media (max-width: 560px) { .cp-grid { grid-template-columns: minmax(0, 1fr); } }
</style>

<!-- The class is chosen from the class sidebar (includes/class_nav.php), so this button stays hidden.
     It still keeps the page's class in step with the sidebar. -->
<button type="button" class="cp-btn empty" id="cp-btn" aria-haspopup="dialog" hidden>
  <span class="cp-ic" id="cp-ic">+</span>
  <span><span class="cp-name" id="cp-name" style="display:block;">Choose a class</span><span class="cp-sub" id="cp-sub">Section and subject</span></span>
  <span class="cp-caret">▾</span>
</button>

<div class="cp-ov" id="cp-ov">
  <div class="cp-modal" role="dialog" aria-modal="true" aria-labelledby="cp-title">
    <div class="cp-head">
      <button type="button" class="cp-back" id="cp-back" aria-label="Back to sections">‹</button>
      <div><div class="cp-title" id="cp-title">Choose a section</div><div class="cp-step" id="cp-step">Step 1 of 2</div></div>
      <button type="button" class="cp-close" id="cp-close" aria-label="Close">✕</button>
    </div>
    <div class="cp-steps"><span class="on"></span><span id="cp-s2"></span></div>
    <div class="cp-grid" id="cp-grid"></div>
  </div>
</div>

<script>
(() => {
  const CLASSES = <?= json_encode($list, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
  const MODE = <?= json_encode($mode) ?>, SELECT_ID = <?= json_encode($selectId) ?>;
  const KEY = 'elms_teacher_class_<?= $uid ?>', OLD_KEY = 'elms_teacher_course_<?= $uid ?>';
  const $ = id => document.getElementById(id);
  const esc = s => { const d = document.createElement('div'); d.textContent = s ?? ''; return d.innerHTML; };
  const gradeNo = s => { const m = /(\d+)/.exec(s); return m ? Number(m[1]) : 99; };
  const sections = [...new Set(CLASSES.map(c => c.section))].sort((a, b) => gradeNo(a) - gradeNo(b) || a.localeCompare(b));
  let current = null, step = 1, viewSection = null;

  window.classSection = () => current ? current.section : '';
  const secIcon = s => { const m = /(\d+)/.exec(s); return m ? m[1] : s.slice(0, 2).toUpperCase(); };

  function badge(c) {
    if (MODE === 'att') return c.badge ? '<span class="cp-pill ok">Taken today</span>' : '<span class="cp-pill">Not taken today</span>';
    if (!c.badge) return '<span class="cp-muted">All caught up</span>';
    return `<span class="cp-pill">${c.badge} ${MODE === 'quiz' ? 'to check' : 'to grade'}</span>`;
  }
  function sectionBadge(sec) {
    const list = CLASSES.filter(c => c.section === sec);
    if (MODE === 'att') { const left = list.filter(c => !c.badge).length; return left ? `<span class="cp-pill">${left} not taken</span>` : '<span class="cp-pill ok">All taken</span>'; }
    const n = list.reduce((a, c) => a + c.badge, 0);
    return n ? `<span class="cp-pill">${n} ${MODE === 'quiz' ? 'to check' : 'to grade'}</span>` : '';
  }
  function paintButton() {
    const b = $('cp-btn');
    if (!current) { b.classList.add('empty'); $('cp-ic').textContent = '+'; $('cp-name').textContent = 'Choose a class'; $('cp-sub').textContent = 'Section and subject'; return; }
    b.classList.remove('empty');
    $('cp-ic').textContent = current.short; $('cp-name').textContent = current.name; $('cp-sub').textContent = current.section;
  }
  function draw() {
    const twoSteps = sections.length > 1;
    $('cp-back').style.visibility = step === 2 && twoSteps ? 'visible' : 'hidden';
    $('cp-s2').className = step === 2 ? 'on' : '';
    document.querySelector('.cp-steps').style.display = twoSteps ? '' : 'none';
    if (step === 1) {
      $('cp-title').textContent = 'Choose a section'; $('cp-step').textContent = 'Step 1 of 2';
      $('cp-grid').innerHTML = sections.map(sec => {
        const n = CLASSES.filter(c => c.section === sec).length;
        return `<button type="button" class="cp-card${current && current.section === sec ? ' on' : ''}" data-sec="${esc(sec)}">
          <span class="cp-ic" style="font-size:1rem;">${esc(secIcon(sec))}</span>
          <span><b>${esc(sec)}</b><span class="cp-muted">${n} subject${n === 1 ? '' : 's'}</span><br>${sectionBadge(sec)}</span></button>`;
      }).join('');
    } else {
      $('cp-title').textContent = twoSteps ? viewSection : 'Choose a class';
      $('cp-step').textContent = twoSteps ? 'Step 2 of 2 · choose a subject' : viewSection;
      $('cp-grid').innerHTML = CLASSES.filter(c => c.section === viewSection).map(c => {
        const on = current && current.course === c.course && current.section === c.section;
        return `<button type="button" class="cp-card${on ? ' on' : ''}" data-course="${c.course}" data-sec="${esc(c.section)}">
          <span class="cp-ic">${esc(c.short)}</span><span><b>${esc(c.name)}</b><span class="cp-muted">${esc(c.code)}</span><br>${badge(c)}</span></button>`;
      }).join('');
    }
    const first = $('cp-grid').querySelector('.cp-card.on') || $('cp-grid').querySelector('.cp-card');
    if (first) first.focus();
  }
  function open() {
    if (!CLASSES.length) return;
    viewSection = current ? current.section : (sections.length === 1 ? sections[0] : null);
    step = viewSection ? 2 : 1;
    $('cp-ov').classList.add('show'); draw();
  }
  function close() { $('cp-ov').classList.remove('show'); $('cp-btn').focus(); }
  function choose(c, fire = true) {
    current = c; paintButton();
    try { localStorage.setItem(KEY, JSON.stringify({ course: c.course, section: c.section })); localStorage.setItem(OLD_KEY, String(c.course)); } catch (e) { }
    tell(c);
    const sel = $(SELECT_ID);
    if (sel) { sel.value = String(c.course); if (fire) sel.dispatchEvent(new Event('change')); }
  }
  window.classPickerChoose = choose;
  // Lets the class sidebar (includes/class_nav.php) follow the chosen class
  function tell(c) {
    try { document.dispatchEvent(new CustomEvent('classpicker:change', { detail: { course: c.course, section: c.section } })); } catch (e) { }
  }

  $('cp-btn').addEventListener('click', open);
  $('cp-close').addEventListener('click', close);
  $('cp-back').addEventListener('click', () => { step = 1; draw(); });
  $('cp-ov').addEventListener('click', e => { if (e.target === $('cp-ov')) close(); });
  document.addEventListener('keydown', e => { if (e.key === 'Escape' && $('cp-ov').classList.contains('show')) close(); });
  $('cp-grid').addEventListener('click', e => {
    const card = e.target.closest('.cp-card'); if (!card) return;
    if (step === 1) { viewSection = card.dataset.sec; step = 2; draw(); return; }
    const c = CLASSES.find(x => x.course === Number(card.dataset.course) && x.section === card.dataset.sec);
    if (c) { close(); choose(c); }
  });

  // Pick the starting class before the page's own "remember" logic runs:
  // ?course=&section= (from My Classes) → last class used → the only class
  const p = new URLSearchParams(location.search);
  let start = null;
  const uc = Number(p.get('course')), us = p.get('section');
  if (uc && us) start = CLASSES.find(c => c.course === uc && c.section === us) || null;
  if (!start && uc) {
    // URL named a subject but no (valid) section: prefer the remembered section of that same subject
    try { const s = JSON.parse(localStorage.getItem(KEY) || 'null'); if (s && s.course === uc) start = CLASSES.find(c => c.course === uc && c.section === s.section) || null; } catch (e) { }
    if (!start) start = CLASSES.find(c => c.course === uc) || null;
  }
  if (!start) { try { const s = JSON.parse(localStorage.getItem(KEY) || 'null'); if (s) start = CLASSES.find(c => c.course === s.course && c.section === s.section) || null; } catch (e) { } }
  if (!start) { try { const oc = Number(localStorage.getItem(OLD_KEY)); if (oc) start = CLASSES.find(c => c.course === oc) || null; } catch (e) { } }
  // Nothing remembered yet: open the first class (same order as the class sidebar)
  if (!start && CLASSES.length) {
    start = CLASSES.slice().sort((a, b) => a.section.localeCompare(b.section, undefined, { numeric: true, sensitivity: 'base' }) || a.code.localeCompare(b.code))[0];
  }
  if (start) { current = start; paintButton(); tell(start); }
  // The page's <select> comes after this widget: set it once the page is parsed
  // (this listener is registered before the shared "remember" script, so it runs first)
  document.addEventListener('DOMContentLoaded', () => {
    const sel = $(SELECT_ID);
    if (!sel) return;
    sel.classList.add('cp-hidden-select');
    if (current) { sel.value = String(current.course); sel.dispatchEvent(new Event('change')); }
  });
})();
</script>
    <?php
  }
}
