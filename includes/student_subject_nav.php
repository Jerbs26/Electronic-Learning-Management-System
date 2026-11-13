<?php
// ============================================================
//  Arandia College eLMS — Student subject sidebar ("double sidebar")
//  File: includes/student_subject_nav.php
//
//  Same layout as the Teacher side (includes/class_nav.php): when a
//  student opens one subject, a second sidebar opens next to the main
//  one with that subject's Modules, Assignments, Quizzes, Grades and
//  Attendance, and the main sidebar shrinks to an icon rail. On phones
//  both become sideways strips above the page.
//
//  A subject is opened with ?course=COURSE_ID on modules.php,
//  assignments.php, quizzes.php, grades.php or attendance.php. Only the
//  student's own enrolled subjects can match (student/_init.php), so a
//  changed URL can never show another subject's work. Without ?course
//  those pages show all subjects; with an unknown / not-enrolled one,
//  _init.php sends the student back to My Subjects. A stray ?course on
//  any other student page (Awards, Dashboard...) is removed the same way.
// ============================================================

if (!function_exists('studentSubjectPages')) {
  /** Pages that belong to one subject, and which item they light up. */
  function studentSubjectPages(): array
  {
    // Quizzes are listed on the Assignments page (quizzes.php only forwards there)
    return ['modules.php' => 'modules', 'work.php' => 'modules', 'assignments.php' => 'assignments', 'quizzes.php' => 'assignments',
            'grades.php' => 'grades', 'attendance.php' => 'attendance'];
  }

  /** The enrolled subject named by ?course=ID, or null. */
  function studentSubjectResolve(array $subjects): ?array
  {
    $cid = filter_var($_GET['course'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if (!$cid) return null;
    foreach ($subjects as $s) if ((int) $s['id'] === $cid) return $s;
    return null;
  }

  /**
   * Badge counts per course id: assignments still to submit (not past due)
   * and quizzes still to take (open, not closed) — same rules as My Subjects
   * and the dashboard.
   */
  function studentSubjectCounts(array $assignments, array $quizzes): array
  {
    $out = [];
    $now = time();
    foreach ($assignments as $a) {
      $cid = (int) $a['course_id'];
      $out[$cid]['asg'] = $out[$cid]['asg'] ?? 0;
      if ((int) $a['attempt_count'] === 0 && (!$a['due_date'] || strtotime($a['due_date']) > $now)) $out[$cid]['asg']++;
    }
    foreach ($quizzes as $q) {
      $cid = (int) $q['course_id'];
      $out[$cid]['quiz'] = $out[$cid]['quiz'] ?? 0;
      $done = in_array($q['attempt_status'], ['Submitted', 'Graded'], true);
      $closed = $q['close_at'] && strtotime($q['close_at']) <= $now;
      if (!$done && !$closed) $out[$cid]['quiz']++;
    }
    // Assignments page badge: assignments to submit + quizzes to take
    foreach ($out as $cid => $c) $out[$cid]['work'] = ($c['asg'] ?? 0) + ($c['quiz'] ?? 0);
    return $out;
  }

  function studentSubjectShort(string $code): string
  {
    $tail = strrchr($code, '-');
    $short = strtoupper(substr($tail !== false ? substr($tail, 1) : $code, 0, 4));
    return $short !== '' ? $short : 'SUB';
  }

  /**
   * Prints the subject sidebar for $active (one of $subjects).
   * $icons: ['modules' => svg, 'assignments' => svg, ...]
   */
  function renderStudentSubjectNav(array $subjects, array $active, string $currentPage, array $counts, array $icons): void
  {
    $e = fn($s) => htmlspecialchars((string) $s, ENT_QUOTES);
    $pages = studentSubjectPages();
    $on = $pages[$currentPage] ?? 'modules';
    $cid = (int) $active['id'];
    $page = isset($pages[$currentPage]) ? $currentPage : 'modules.php';
    $items = [
      ['modules',     'modules.php',     'Modules',     null],
      ['assignments', 'assignments.php', 'Assignments', 'work'],
      ['grades',      'grades.php',      'Grades',      null],
      ['attendance',  'attendance.php',  'Attendance',  null],
    ];
    $meta = trim((string) ($active['course_code'] ?? ''));
    if (!empty($active['teacher_name'])) $meta .= ($meta !== '' ? ' · ' : '') . $active['teacher_name'];

    usort($subjects, fn($a, $b) => strnatcasecmp((string) $a['course_name'], (string) $b['course_name']));
    ?>
<aside class="cnav" id="cnav" aria-label="Subject menu">
  <a class="cnav-all" href="my-subjects.php"><?= $icons['all'] ?? '' ?><span>All subjects</span></a>

  <div class="cnav-card">
    <button type="button" class="cnav-switch" id="cnav-switch" aria-haspopup="true" aria-expanded="false" aria-controls="cnav-menu" title="Switch subject">
      <span class="cnav-chip"><?= $e(studentSubjectShort((string) $active['course_code'])) ?></span>
      <span class="cnav-who">
        <span class="cnav-name"><?= $e($active['course_name']) ?></span>
        <span class="cnav-meta"><?= $e($meta) ?></span>
      </span>
      <span class="cnav-caret" aria-hidden="true">&#8645;</span>
    </button>
    <div class="cnav-menu" id="cnav-menu" role="menu" hidden>
      <div class="cnav-menu-h">Switch subject</div>
      <?php foreach ($subjects as $s): $sid = (int) $s['id']; ?>
        <a role="menuitem" class="cnav-opt<?= $sid === $cid ? ' on' : '' ?>" href="<?= $e($page . '?course=' . $sid) ?>">
          <span class="cnav-chip sm"><?= $e(studentSubjectShort((string) $s['course_code'])) ?></span>
          <span><b><?= $e($s['course_name']) ?></b><small><?= $e($s['course_code']) ?></small></span>
        </a>
      <?php endforeach; ?>
    </div>
  </div>

  <nav class="cnav-links">
    <?php foreach ($items as [$key, $file, $label, $badge]):
      $n = $badge ? (int) ($counts[$cid][$badge] ?? 0) : 0; ?>
      <a class="cnav-link<?= $on === $key ? ' on' : '' ?>" href="<?= $e($file . '?course=' . $cid) ?>"<?= $on === $key ? ' aria-current="page"' : '' ?>>
        <span class="cnav-ic"><?= $icons[$key] ?? '' ?></span>
        <span class="cnav-tx"><?= $e($label) ?></span>
        <?php if ($n): ?><span class="cnav-badge" title="To do"><?= $n ?></span><?php endif; ?>
      </a>
    <?php endforeach; ?>
  </nav>
</aside>
<script>
(() => {
  const nav = document.getElementById('cnav');
  const btn = document.getElementById('cnav-switch'), menu = document.getElementById('cnav-menu');
  if (!nav || !btn || !menu) return;
  function openMenu(show) { menu.hidden = !show; nav.classList.toggle('is-open', show); btn.setAttribute('aria-expanded', show ? 'true' : 'false'); }
  btn.addEventListener('click', e => { e.stopPropagation(); openMenu(menu.hidden); });
  document.addEventListener('click', e => { if (!menu.hidden && !menu.contains(e.target)) openMenu(false); });
  document.addEventListener('keydown', e => { if (e.key === 'Escape' && !menu.hidden) { openMenu(false); btn.focus(); } });

  // Phones: bring the open item into view in the sideways strip
  const onLink = nav.querySelector('.cnav-link.on');
  if (onLink && window.matchMedia('(max-width: 768px)').matches) {
    const strip = onLink.parentElement;
    strip.scrollLeft = onLink.offsetLeft - (strip.clientWidth - onLink.offsetWidth) / 2;
  }

  // One subject open: its list starts expanded (pages group work by subject)
  document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.section-panel .accordion .acc-body[id]').forEach(b => {
      if (!b.classList.contains('open') && typeof window.toggleBlock === 'function') {
        try { window.toggleBlock(b.id); } catch (err) { b.classList.add('open'); }
      }
    });
  });
})();
</script>
    <?php
  }
}