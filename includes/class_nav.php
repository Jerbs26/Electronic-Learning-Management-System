<?php
// ============================================================
//  Arandia College eLMS — Teacher class sidebar ("double sidebar")
//  File: includes/class_nav.php
//
//  When a teacher is inside a class, a second sidebar opens next to the
//  main one with everything for that class: Students, Modules,
//  Assignments, Quizzes, Grades, Attendance. The main sidebar shrinks to
//  an icon rail so the page keeps its width. On phones both become
//  sideways strips above the page.
//
//  Used by includes/sidebar.php (Teacher). Pages can help it find the
//  class by setting, before header.php:
//    $classNavCourse  — course id (grade.php: the assignment's course)
//    $classNavSection — section name
//  Otherwise it reads ?class=ASSIGN_ID or ?course=ID&section=NAME.
//  The class picker (includes/class_picker.php) fires "classpicker:change";
//  this sidebar follows it without a reload.
// ============================================================
require_once __DIR__ . '/settings.php';   // current school year / semester (Admin → Settings)

if (!function_exists('classNavPages')) {
  /** Pages that belong to a class, and which class-sidebar item they light up. */
  function classNavPages(): array
  {
    return ['classes.php' => 'students', 'modules.php' => 'modules', 'assignments.php' => 'assignments',
            'grade.php' => 'assignments', 'quizzes.php' => 'assignments', 'grades.php' => 'grades',
            'attendance.php' => 'attendance'];
  }

  /**
   * The teacher's class this page is about, or null. Only classes from
   * $mySubjects (the teacher's own teacher_assignments) can match, so a
   * changed URL can never open someone else's class.
   */
  function classNavResolve(array $mySubjects, ?int $course = null, ?string $section = null): ?array
  {
    $assign = (int) ($_GET['class'] ?? 0);
    $course = $course ?: (int) ($_GET['course'] ?? 0);
    $section = trim((string) ($section ?? ($_GET['section'] ?? '')));

    if ($assign) {
      foreach ($mySubjects as $s) {
        if ((int) $s['assign_id'] === $assign && (!$course || (int) $s['course_id'] === $course)) return $s;
      }
    }
    if (!$course) return null;
    $hits = [];
    foreach ($mySubjects as $s) {
      if ((int) $s['course_id'] !== $course) continue;
      if ($section !== '' && $s['section'] !== $section) continue;
      $hits[$s['section']] = $s;
    }
    // Several sections teach this subject and none was named: don't guess.
    return count($hits) === 1 ? reset($hits) : null;
  }

  /** Badge counts per "course|section" for the teacher's classes. Never throws. */
  function classNavCounts(mysqli $conn, array $mySubjects): array
  {
    $out = [];
    $courses = array_values(array_unique(array_map(fn($s) => (int) $s['course_id'], $mySubjects)));
    if (!$courses) return $out;
    $in = implode(',', $courses);
    $today = date('Y-m-d');
    $queries = [
      'grade' => "SELECT a.course_id, u.section_dept AS section, COUNT(*) AS n
                  FROM submissions s JOIN assignments a ON a.id = s.assignment_id JOIN users u ON u.id = s.student_id
                  WHERE a.course_id IN ($in) AND s.score IS NULL %ATT%
                  GROUP BY a.course_id, u.section_dept",
      'check' => "SELECT q.course_id, u.section_dept AS section, COUNT(*) AS n
                  FROM quiz_attempts qa JOIN quizzes q ON q.id = qa.quiz_id JOIN users u ON u.id = qa.student_id
                  WHERE q.course_id IN ($in) AND qa.status = 'Submitted' AND qa.score IS NULL
                  GROUP BY q.course_id, u.section_dept",
      'today' => "SELECT a.course_id, u.section_dept AS section, COUNT(*) AS n
                  FROM attendance a JOIN users u ON u.id = a.student_id
                  WHERE a.course_id IN ($in) AND a.`date` = '$today'
                  GROUP BY a.course_id, u.section_dept",
    ];
    try {
      $hasAtt = (bool) $conn->query("SHOW COLUMNS FROM submissions LIKE 'attempt_count'")->num_rows;
      $queries['grade'] = str_replace('%ATT%', $hasAtt ? 'AND s.attempt_count > 0' : '', $queries['grade']);
    } catch (Throwable $e) {
      $queries['grade'] = str_replace('%ATT%', '', $queries['grade']);
    }
    foreach ($queries as $key => $sql) {
      try {
        $res = $conn->query($sql);
        while ($res && $r = $res->fetch_assoc()) $out[$r['course_id'] . '|' . $r['section']][$key] = (int) $r['n'];
      } catch (Throwable $e) {
        error_log('[class_nav] ' . $key . ': ' . $e->getMessage());
      }
    }
    return $out;
  }

  function classNavShort(string $code): string
  {
    $tail = strrchr($code, '-');
    $short = strtoupper(substr($tail !== false ? substr($tail, 1) : $code, 0, 4));
    return $short !== '' ? $short : 'SUB';
  }

  /**
   * Prints the class sidebar. $class may be null on the picker pages: the
   * sidebar is then printed hidden and opens once a class is picked.
   * Returns true when it is visible.
   */
  function renderClassNav(mysqli $conn, array $mySubjects, ?array $class, string $currentPage): bool
  {
    $pages = classNavPages();
    $active = $pages[$currentPage] ?? '';
    $counts = classNavCounts($conn, $mySubjects);

    $list = [];
    foreach ($mySubjects as $s) {
      $k = $s['course_id'] . '|' . $s['section'];
      $list[] = ['id' => (int) $s['assign_id'], 'course' => (int) $s['course_id'], 'section' => (string) $s['section'],
                 'name' => (string) $s['course_name'], 'code' => (string) $s['course_code'], 'sy' => elmsSetting('school_year'),   // current school year (Admin → Settings)
                 'short' => classNavShort((string) $s['course_code']),
                 'grade' => $counts[$k]['grade'] ?? 0, 'check' => $counts[$k]['check'] ?? 0, 'today' => $counts[$k]['today'] ?? 0,
                 'work' => ($counts[$k]['grade'] ?? 0) + ($counts[$k]['check'] ?? 0)];   // Assignments badge: to grade + quizzes to check
    }
    usort($list, fn($a, $b) => strnatcasecmp($a['section'], $b['section']) ?: strcmp($a['code'], $b['code']));

    $cur = null;
    if ($class) foreach ($list as $c) if ($c['id'] === (int) $class['assign_id']) { $cur = $c; break; }
    $GLOBALS['__classNavRendered'] = true;

    $e = fn($s) => htmlspecialchars((string) $s, ENT_QUOTES);
    $q = $cur ? '?course=' . $cur['course'] . '&section=' . rawurlencode($cur['section']) : '';
    $items = [
      ['students',    'users',        'Students',    $cur ? 'classes.php?class=' . $cur['id'] : '#',              'tpl' => 'classes.php?class={id}'],
      ['modules',     'folder',       'Modules',     $cur ? 'classes.php?class=' . $cur['id'] . '&tab=modules' : '#', 'tpl' => 'classes.php?class={id}&tab=modules'],
      ['assignments', 'clipboard',    'Assignments', 'assignments.php' . $q,                                   'tpl' => 'assignments.php?course={course}&section={section}', 'badge' => 'work'],
      ['grades',      'chart',        'Grades',      'grades.php' . $q,                                        'tpl' => 'grades.php?course={course}&section={section}'],
      ['attendance',  'check-square', 'Attendance',  'attendance.php' . $q,                                    'tpl' => 'attendance.php?course={course}&section={section}'],
    ];
    // classes.php has two items: the Modules tab lights "modules"
    if ($currentPage === 'classes.php' && ($_GET['tab'] ?? '') === 'modules') $active = 'modules';
    ?>
<aside class="cnav" id="cnav" aria-label="Class menu"<?= $cur ? '' : ' hidden' ?>>
  <a class="cnav-all" href="classes.php"><?= t_icon('layers') ?><span>All classes</span></a>

  <div class="cnav-card">
    <button type="button" class="cnav-switch" id="cnav-switch" aria-haspopup="true" aria-expanded="false" aria-controls="cnav-menu" title="Switch class">
      <span class="cnav-chip" id="cnav-chip"><?= $e($cur['short'] ?? '') ?></span>
      <span class="cnav-who">
        <span class="cnav-name" id="cnav-name"><?= $e($cur['name'] ?? '') ?></span>
        <span class="cnav-meta" id="cnav-meta"><?= $cur ? $e($cur['section'] . ($cur['sy'] !== '' ? ' · ' . $cur['sy'] : '')) : '' ?></span>
      </span>
      <span class="cnav-caret" aria-hidden="true">&#8645;</span>
    </button>
    <div class="cnav-menu" id="cnav-menu" role="menu" hidden>
      <div class="cnav-menu-h">Switch class</div>
      <?php foreach ($list as $c): ?>
        <a role="menuitem" class="cnav-opt<?= $cur && $cur['id'] === $c['id'] ? ' on' : '' ?>" data-id="<?= $c['id'] ?>" href="#">
          <span class="cnav-chip sm"><?= $e($c['short']) ?></span>
          <span><b><?= $e($c['name']) ?></b><small><?= $e($c['section']) ?></small></span>
        </a>
      <?php endforeach; ?>
    </div>
  </div>

  <nav class="cnav-links">
      <?php foreach ($items as $it):
        $n = isset($it['badge']) && $cur ? (int) $cur[$it['badge']] : 0; ?>
        <a class="cnav-link<?= $active === $it[0] ? ' on' : '' ?>" href="<?= $e($it[3]) ?>" data-tpl="<?= $e($it['tpl']) ?>"<?= isset($it['badge']) ? ' data-badge="' . $e($it['badge']) . '"' : '' ?><?= $active === $it[0] ? ' aria-current="page"' : '' ?>>
          <span class="cnav-ic"><?= t_icon($it[1]) ?></span>
          <span class="cnav-tx"><?= $e($it[2]) ?></span>
          <?php if (isset($it['badge'])): ?><span class="cnav-badge"<?= $n ? '' : ' hidden' ?>><?= $n ?></span><?php endif; ?>
          <?php if ($it[0] === 'attendance'): ?><span class="cnav-dot<?= $cur && $cur['today'] ? ' ok' : '' ?>" title="<?= $cur && $cur['today'] ? 'Taken today' : 'Not taken today' ?>"></span><?php endif; ?>
        </a>
      <?php endforeach; ?>
  </nav>
</aside>
<script>
(() => {
  const CLASSES = <?= json_encode($list, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
  const PAGE = <?= json_encode($currentPage) ?>;
  const nav = document.getElementById('cnav');
  if (!nav) return;
  const rail = nav.previousElementSibling && nav.previousElementSibling.classList.contains('sidebar') ? nav.previousElementSibling : document.querySelector('.sidebar');
  const btn = document.getElementById('cnav-switch'), menu = document.getElementById('cnav-menu');
  let current = CLASSES.find(c => c.id === <?= $cur ? (int) $cur['id'] : 0 ?>) || null;

  function fill(c) {
    current = c;
    document.getElementById('cnav-chip').textContent = c.short;
    document.getElementById('cnav-name').textContent = c.name;
    const sy = (window.ElmsSettings && ElmsSettings.get().school_year) || c.sy;   // live value after a Settings change
    document.getElementById('cnav-meta').textContent = c.section + (sy ? ' · ' + sy : '');
    nav.querySelectorAll('.cnav-link').forEach(a => {
      a.href = a.dataset.tpl.replace('{id}', c.id).replace('{course}', c.course).replace('{section}', encodeURIComponent(c.section));
      const b = a.querySelector('.cnav-badge');
      if (b) { const n = c[a.dataset.badge] || 0; b.textContent = n; b.hidden = !n; }
      const d = a.querySelector('.cnav-dot');
      if (d) { d.classList.toggle('ok', !!c.today); d.title = c.today ? 'Taken today' : 'Not taken today'; }
    });
    menu.querySelectorAll('.cnav-opt').forEach(o => o.classList.toggle('on', Number(o.dataset.id) === c.id));
    nav.hidden = false;
    if (rail) rail.classList.add('sidebar--rail');
  }

  // Switch class: same page, other class
  function go(c) {
    const here = nav.querySelector('.cnav-link.on');
    if (PAGE === 'modules.php' || PAGE === 'grade.php' || !here) {
      location.href = 'classes.php?class=' + c.id; return;
    }
    if (window.classPickerChoose && /^(assignments|quizzes|grades|attendance)\.php$/.test(PAGE)) {
      window.classPickerChoose({ course: c.course, section: c.section, name: c.name, short: c.short, code: c.code, badge: 0 }); return;
    }
    location.href = here.dataset.tpl.replace('{id}', c.id).replace('{course}', c.course).replace('{section}', encodeURIComponent(c.section));
  }
  function openMenu(show) { menu.hidden = !show; nav.classList.toggle('is-open', show); btn.setAttribute('aria-expanded', show ? 'true' : 'false'); }
  btn.addEventListener('click', e => { e.stopPropagation(); openMenu(menu.hidden); });
  menu.addEventListener('click', e => {
    const o = e.target.closest('.cnav-opt'); if (!o) return;
    e.preventDefault(); openMenu(false);
    const c = CLASSES.find(x => x.id === Number(o.dataset.id));
    if (c && (!current || c.id !== current.id)) go(c);
  });
  document.addEventListener('click', e => { if (!menu.hidden && !menu.contains(e.target)) openMenu(false); });
  document.addEventListener('keydown', e => { if (e.key === 'Escape' && !menu.hidden) { openMenu(false); btn.focus(); } });

  // Phones: bring the open item into view in the sideways strip
  const onLink = nav.querySelector('.cnav-link.on');
  if (onLink && window.matchMedia('(max-width: 700px)').matches) {
    const strip = onLink.parentElement;
    strip.scrollLeft = onLink.offsetLeft - (strip.clientWidth - onLink.offsetWidth) / 2;
  }

  // Picker pages: follow the class picker and keep the address bar in step
  document.addEventListener('classpicker:change', e => {
    const d = e.detail || {};
    const c = CLASSES.find(x => x.course === Number(d.course) && x.section === d.section);
    if (!c) return;
    fill(c);
    try {
      const u = new URL(location.href);
      u.searchParams.set('course', c.course); u.searchParams.set('section', c.section);
      history.replaceState(history.state, '', u.pathname + u.search + u.hash);
    } catch (err) { }
  });
})();
</script>
    <?php
    return (bool) $cur;
  }
}