<?php
// ============================================================
//  Arandia College eLMS — Admin Panel
//  File: admin/subjects-enrollment.php   (sidebar: "Sections")
//
//  Section-based subjects and enrollment:
//    subjects-enrollment.php                  → section cards
//    subjects-enrollment.php?section=Grade 10 → one section, with tabs
//        &tab=subjects  Subjects and teachers   (assign_teachers to edit)
//        &tab=students  Students and enrollment (enroll_students to edit)
//  Section setup (add / rename / deactivate) needs manage_classes.
//  All changes go through api/sections.php.
// ============================================================
require_once __DIR__ . '/../includes/settings.php';   // current school year / semester (Admin → Settings)
if (session_status() === PHP_SESSION_NONE) session_start();
require_once '../config/conn.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['Admin', 'Head Admin'], true)) {
  header('Location: ../login.php');
  exit;
}
require_once '../includes/auto_enroll.php';
aeBackfillOnce($conn);   // one time: students added before automatic enrollment
require_once '../config/permissions.php';
require_once '../includes/sections.php';

$isHeadAdmin = isHeadAdmin();
$can = function (...$keys) use ($conn): bool {
  if (isHeadAdmin()) return true;
  foreach ($keys as $k) if (adminHasPermission($conn, $k)) return true;
  return false;
};
if (!$can('view_subjects', 'assign_teachers', 'enroll_students', 'view_enrollment', 'view_classes', 'manage_classes')) {
  header('Location: dashboard.php?toast=' . urlencode('Access denied — you do not have permission to view this page.') . '&error=1');
  exit;
}
$canAssign   = $can('assign_teachers');
$canEnroll   = $can('enroll_students');
$hasTable    = sectionsTableExists($conn);
$canSections = $hasTable && $can('manage_classes');

$first_name = $_SESSION['first_name'];
$last_name  = $_SESSION['last_name'];
$toast   = $_GET['toast'] ?? '';
$isError = $_GET['error'] ?? '';
[$shsSections, $hsSections] = loadSectionLists($conn);

$levelLabel  = ['SHS' => 'Senior High School', 'HS' => 'High School'];
$sectionName = trim($_GET['section'] ?? '');

// A subject "has a teacher" when it points at an existing, active Teacher account
$teacherJoin = "LEFT JOIN users u ON u.id = ta.teacher_id AND u.role = 'Teacher' AND u.status = 'Active'";

// Junior High core subjects, in the order the school lists them (Filipino 10, English 10, ...)
$hsOrder = ['FIL', 'ENGLISH', 'MATH', 'SCI', 'AP', 'ESP', 'MAPEH', 'TLE'];
$hsOrderSql = "'" . implode("','", $hsOrder) . "'";
$hsRank = function (string $code) use ($hsOrder): int {
  $i = array_search(substr(strrchr($code, '-') ?: '', 1), $hsOrder, true);
  return $i === false ? 99 : $i;
};

if ($sectionName === '') {
  // ── Section cards ────────────────────────────────────────
  $view = 'grid';
  $sections = fetchSections($conn, false);

  $studentCounts = [];
  $res = $conn->query("SELECT section_dept, COUNT(*) AS n FROM users WHERE role = 'Student' AND status = 'Active' GROUP BY section_dept");
  while ($res && $r = $res->fetch_assoc()) $studentCounts[$r['section_dept']] = (int) $r['n'];

  $subjectCounts = [];
  $res = $conn->query("SELECT ta.section, COUNT(*) AS total, SUM(CASE WHEN u.id IS NULL THEN 1 ELSE 0 END) AS missing
                       FROM teacher_assignments ta $teacherJoin GROUP BY ta.section");
  while ($res && $r = $res->fetch_assoc()) $subjectCounts[$r['section']] = ['total' => (int) $r['total'], 'missing' => (int) $r['missing']];

  $groups = ['SHS' => [], 'HS' => [], 'Inactive' => []];
  foreach ($sections as $s) {
    $groups[$s['status'] === 'Inactive' ? 'Inactive' : $s['level']][] = $s;
  }
  $pageTitle = 'Sections';
} else {
  // ── One section ──────────────────────────────────────────
  $view = 'detail';
  $section = null;
  foreach (fetchSections($conn, false) as $s) {
    if ($s['name'] === $sectionName) { $section = $s; break; }
  }
  if (!$section) {
    header('Location: subjects-enrollment.php?toast=' . urlencode('That section no longer exists.') . '&error=1');
    exit;
  }
  $tab = ($_GET['tab'] ?? '') === 'students' ? 'students' : 'subjects';

  // Subjects (one row per subject + term) with their teacher
  $subjects = [];
  $st = $conn->prepare(
    "SELECT ta.id, ta.course_id, ta.school_year, ta.semester, c.course_code, c.course_name,
            u.id AS valid_teacher_id, CONCAT(u.last_name, ', ', u.first_name) AS teacher_name
     FROM teacher_assignments ta
     JOIN courses c ON c.id = ta.course_id
     $teacherJoin
     WHERE ta.section = ?
     ORDER BY ta.school_year DESC, FIELD(ta.semester, '1st', '2nd', 'Summer'),
              FIELD(SUBSTRING_INDEX(c.course_code, '-', -1), $hsOrderSql) = 0,
              FIELD(SUBSTRING_INDEX(c.course_code, '-', -1), $hsOrderSql), ta.id"
  );
  $st->bind_param('s', $sectionName);
  $st->execute();
  $res = $st->get_result();
  while ($r = $res->fetch_assoc()) $subjects[] = $r;
  $st->close();

  $missing = count(array_filter($subjects, fn($s) => !$s['valid_teacher_id']));
  $sectionCourses = [];
  foreach ($subjects as $s) $sectionCourses[(int) $s['course_id']] = $s['course_name'] . ' (' . $s['course_code'] . ')';
  $sectionCourseIds = array_keys($sectionCourses);

  // Students in this section + which of the section's subjects they're enrolled in
  $students = [];
  $st = $conn->prepare("SELECT id, school_id, first_name, last_name, status FROM users
                        WHERE role = 'Student' AND section_dept = ? ORDER BY status = 'Inactive', last_name, first_name");
  $st->bind_param('s', $sectionName);
  $st->execute();
  $res = $st->get_result();
  while ($r = $res->fetch_assoc()) { $r['courses'] = []; $students[(int) $r['id']] = $r; }
  $st->close();
  if ($students && $sectionCourseIds) {
    $sIds = implode(',', array_keys($students));       // ints from the DB
    $cIds = implode(',', $sectionCourseIds);           // ints from the DB
    $res = $conn->query("SELECT student_id, course_id FROM enrollments
                         WHERE status = 'Enrolled' AND student_id IN ($sIds) AND course_id IN ($cIds)");
    while ($res && $r = $res->fetch_assoc()) $students[(int) $r['student_id']]['courses'][] = (int) $r['course_id'];
  }
  $activeStudents = count(array_filter($students, fn($s) => $s['status'] === 'Active'));
  $notFullyEnrolled = count(array_filter($students, fn($s) => $s['status'] === 'Active' && count($s['courses']) < count($sectionCourseIds)));

  $teachers = [];
  $res = $conn->query("SELECT id, CONCAT(last_name, ', ', first_name) AS name FROM users WHERE role = 'Teacher' AND status = 'Active' ORDER BY last_name, first_name");
  while ($res && $r = $res->fetch_assoc()) $teachers[] = $r;

  // Subject picker: this grade's own subjects first (HS<grade>-…), everything else after
  $courseGroups = ['own' => [], 'other' => []];
  $gradePrefix = preg_match('/^Grade (\d+)(?: - |$)/', $sectionName, $m) ? 'HS' . $m[1] . '-' : null;   // "Grade 7" and "Grade 7 - Rizal"
  if ($canAssign) {
    $res = $conn->query("SELECT id, course_code, course_name FROM courses WHERE status = 'Active' ORDER BY id");
    while ($res && $r = $res->fetch_assoc()) {
      $own = $gradePrefix && strpos($r['course_code'], $gradePrefix) === 0;
      $courseGroups[$own ? 'own' : 'other'][] = $r;
    }
    usort($courseGroups['own'], fn($a, $b) => $hsRank($a['course_code']) <=> $hsRank($b['course_code']));
    usort($courseGroups['other'], fn($a, $b) => strcmp($a['course_code'], $b['course_code']));
  }
  // Defaults for new classes: the school year / semester set in Admin → Settings
  require_once __DIR__ . '/../includes/settings.php';
  $defaultSY  = elmsSetting('school_year');
  $defaultSem = elmsSetting('semester');

  $pageTitle = $sectionName;
  $breadcrumb = [['Sections', 'subjects-enrollment.php'], [$sectionName]];
}

$panel = 'subjects';
$currentPage = 'subjects-enrollment.php';
require __DIR__ . '/../includes/header.php';

function sectionUrl(string $name, string $tab = ''): string
{
  return 'subjects-enrollment.php?section=' . urlencode($name) . ($tab ? '&tab=' . $tab : '');
}
?>
<style>
  .sx-head { display: flex; align-items: flex-end; justify-content: space-between; gap: 1rem; flex-wrap: wrap; margin-bottom: 1.25rem; }
  .sx-head .page-title { margin: 0; display: flex; align-items: center; gap: .5rem; }
  .sx-sub { font-size: .84rem; color: var(--text-secondary); margin-top: .35rem; }
  .sx-actions { display: flex; gap: .5rem; align-items: center; flex-wrap: wrap; }
  .sx-group { font-size: .8rem; font-weight: 700; color: var(--text-secondary); margin: 1.75rem 0 .7rem; }
  .sx-group.first { margin-top: .25rem; }
  .sx-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(230px, 1fr)); gap: .85rem; }
  .sx-card { display: block; background: var(--surface); border: 1px solid var(--border); border-radius: 14px; padding: 1rem 1.15rem;
             text-decoration: none; color: inherit; transition: border-color .15s, box-shadow .15s; }
  .sx-card:hover { border-color: var(--primary); box-shadow: 0 4px 18px rgba(0, 48, 135, .08); }
  .sx-card:focus-visible { outline: 2px solid var(--primary); outline-offset: 2px; }
  .sx-card.inactive { opacity: .6; }
  .sx-name { font-weight: 700; font-size: .95rem; color: var(--text-primary); }
  .sx-meta { display: flex; gap: .9rem; font-size: .8rem; color: var(--text-secondary); margin-top: .4rem; }
  .sx-pill { display: inline-block; font-size: .72rem; font-weight: 600; border-radius: 100px; padding: .18rem .65rem; margin-top: .8rem; }
  .sx-pill.warn { background: var(--warning-light); color: var(--warning); }
  .sx-pill.ok { background: var(--success-light); color: var(--success); }
  .sx-pill.muted { background: var(--border-muted); color: var(--text-secondary); }
  .sx-back { display: inline-flex; align-items: center; gap: .45rem; font-size: .95rem; font-weight: 600; color: var(--text-secondary); text-decoration: none;
             padding: .45rem .75rem; margin: 0 0 .6rem -.75rem; border-radius: 10px; min-height: 40px; transition: background .15s, color .15s; }
  .sec-row { display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); gap: .75rem; }
  .sec-hint { font-size: .76rem; color: var(--text-muted); }
  @media (max-width: 560px) { .sec-row { grid-template-columns: minmax(0, 1fr); } }
  .sx-back .back-arrow { font-size: 1.35em; line-height: 1; margin-top: -.1em; }
  .sx-back:hover { color: var(--primary); background: var(--surface-hover); }
  .sx-back:focus-visible { outline: 2px solid var(--primary); outline-offset: 2px; }
  .adm-tabs { display: flex; gap: .25rem; border-bottom: 1px solid var(--border); margin-bottom: 1.25rem; overflow-x: auto; overflow-y: hidden; scrollbar-width: none; }
  .adm-tabs::-webkit-scrollbar { display: none; }
  .adm-tab { padding: .65rem 1.1rem; font-size: .86rem; font-weight: 600; color: var(--text-secondary); text-decoration: none;
             border-bottom: 2px solid transparent; margin-bottom: -1px; white-space: nowrap; }
  .adm-tab:hover { color: var(--text-primary); }
  .adm-tab.on { color: var(--primary); border-bottom-color: var(--primary); }
  .adm-tab:focus-visible { outline: 2px solid var(--primary); outline-offset: 2px; border-radius: 4px; }
  .adm-count { display: inline-block; font-size: .7rem; background: var(--primary-light); color: var(--primary); border-radius: 100px; padding: .05rem .5rem; margin-left: .35rem; }
  .adm-count.warn { background: var(--warning-light); color: var(--warning); }
  .sx-table td { vertical-align: middle; }
  .sx-code { font-weight: 700; color: var(--text-primary); }
  .sx-cname { color: var(--text-secondary); font-size: .82rem; }
  .sx-term { font-size: .8rem; color: var(--text-secondary); white-space: nowrap; }
  .sx-teacher { min-width: 220px; max-width: 290px; height: 38px; padding: .35rem .65rem; font-size: .85rem; }
  .sx-teacher.none { border-color: var(--warning); background: var(--warning-light); color: var(--warning); font-weight: 600; }
  .sx-none { color: var(--warning); font-weight: 600; font-size: .85rem; }
  .sx-progress { font-weight: 700; font-size: .85rem; }
  .sx-progress.full { color: var(--success); }
  .sx-progress.part { color: var(--warning); }
  .sx-bar { display: flex; align-items: center; justify-content: space-between; gap: 1rem; flex-wrap: wrap;
            background: var(--primary-light); border-radius: 12px; padding: .85rem 1.1rem; margin-bottom: 1rem; font-size: .86rem; color: var(--text-primary); }
  .sx-bar.done { background: var(--success-light); }
  .sx-empty { text-align: center; padding: 3rem 1rem; color: var(--text-secondary); font-size: .9rem; line-height: 1.6; }
  .sx-empty strong { display: block; color: var(--text-primary); font-size: 1rem; margin-bottom: .3rem; }
  .sx-empty a { color: var(--primary); font-weight: 600; }
  .sx-notice { background: var(--warning-light); color: var(--warning); border-radius: 12px; padding: .85rem 1.1rem; font-size: .85rem; margin-bottom: 1rem; }
  .sx-checklist { max-height: 300px; overflow-y: auto; border: 1px solid var(--border); border-radius: 10px; padding: .35rem; }
  .sx-checklist label { display: flex; gap: .6rem; align-items: center; padding: .45rem .55rem; border-radius: 8px; font-size: .85rem; cursor: pointer; }
  .sx-checklist label:hover { background: var(--surface-hover); }
  .sx-checklist label.taken { opacity: .5; cursor: default; }
  .sx-search { width: 100%; margin-bottom: .6rem; }
  .sx-list-head { font-size: .74rem; font-weight: 700; color: var(--text-muted); padding: .55rem .55rem .25rem; }
  .sx-link { color: var(--primary); font-weight: 600; text-decoration: none; }
  .sx-link:hover { text-decoration: underline; }
  .sx-tag { font-size: .7rem; font-weight: 600; color: var(--text-muted); border: 1px solid var(--border); border-radius: 100px; padding: .05rem .55rem; }
  .sx-icon-btn { padding: .4rem .55rem; }
  .sx-icon-btn .ico { width: 15px; height: 15px; }
  @media (max-width: 700px) { .sx-teacher { min-width: 170px; } }

  .sx-scroll { overflow-x: auto; }
  .sx-head > div:first-child { min-width: 0; }
  .sx-head .page-title { flex-wrap: wrap; overflow-wrap: anywhere; }

  /* ── Tablets: tighter cells ── */
  @media (max-width: 1100px) {
    .sx-table th, .sx-table td { padding-left: .75rem; padding-right: .75rem; }
    .sx-teacher { min-width: 180px; }
  }

  /* ── Phones + small tablets: rows become cards ── */
  @media (max-width: 760px) {
    .sx-head { align-items: stretch; margin-bottom: 1rem; }
    .sx-head > div:first-child { width: 100%; }
    .sx-head .page-title { font-size: 1.35rem; }
    .sx-actions { width: 100%; display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: .5rem; }
    .sx-actions > .btn { justify-content: center; height: 40px; white-space: nowrap; min-width: 0; }
    .sx-actions > button.btn:only-of-type { grid-column: 1 / -1; }   /* a lone button (e.g. Add section) spans the full width */
    .sx-actions > .search-input { grid-column: 1 / -1; width: 100%; height: 40px; }
    .adm-tab { padding: .6rem .8rem; font-size: .84rem; }
    /* section cards: two per row on phones */
    .sx-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: .65rem; }
    .sx-card { padding: .8rem .85rem; border-radius: 12px; min-width: 0; }
    .sx-name { font-size: .88rem; overflow-wrap: anywhere; }
    .sx-meta { flex-direction: column; gap: .1rem; font-size: .76rem; margin-top: .3rem; }
    .sx-pill { font-size: .68rem; padding: .18rem .55rem; margin-top: .6rem; line-height: 1.3; }

    .sx-bar { flex-direction: column; align-items: stretch; gap: .65rem; padding: .85rem 1rem; }
    .sx-bar .btn { justify-content: center; padding-top: .55rem; padding-bottom: .55rem; }

    .sx-scroll, .sx-scroll .table-scroll { overflow: visible; }
    .sx-table, .sx-table tbody { display: block; width: 100%; min-width: 0 !important; }
    .sx-table thead { display: none; }
    .sx-table tbody { display: grid; gap: .65rem; padding: .75rem; }
    .sx-table tr {
      position: relative; display: grid; grid-template-columns: minmax(0, 1fr); gap: .1rem;
      border: 1px solid var(--border); border-radius: 12px; padding: .8rem .9rem; background: var(--surface);
    }
    .sx-table tr:hover td { background: transparent !important; }
    .sx-table td { display: block; padding: 0; border: 0; min-width: 0; }
    .sx-table td[data-label] { display: grid; grid-template-columns: 6.5rem minmax(0, 1fr); gap: .5rem; align-items: center; padding: .3rem 0; font-size: .83rem; }
    .sx-table td[data-label]::before {
      content: attr(data-label); font-size: .68rem; font-weight: 600; letter-spacing: .05em;
      text-transform: uppercase; color: var(--text-muted);
    }

    /* Subjects card: name on top, delete button in the corner, teacher picker full width */
    .sx-subjects td.sx-subj { padding: 0 0 .5rem; margin-bottom: .2rem; border-bottom: 1px solid var(--border-muted); }
    .sx-subjects tr:has(td.sx-del) td.sx-subj { padding-right: 2.75rem; }
    .sx-subjects .sx-code { font-size: .92rem; line-height: 1.35; overflow-wrap: anywhere; }
    .sx-subjects td.sx-term { white-space: normal; }
    .sx-subjects td.sx-tcell { grid-template-columns: minmax(0, 1fr); gap: .3rem; padding-top: .45rem; }
    .sx-teacher { width: 100%; min-width: 0; max-width: none; height: 42px; font-size: .88rem; }
    .sx-subjects td.sx-del { position: absolute; top: .7rem; right: .75rem; }
    .sx-subjects td.sx-del .sx-icon-btn { width: 34px; height: 34px; padding: 0; justify-content: center; }

    /* Students card */
    .sx-students td.sx-stu { padding: 0 0 .5rem; margin-bottom: .2rem; border-bottom: 1px solid var(--border-muted); font-size: .92rem; overflow-wrap: anywhere; }
    .sx-students td.sx-act:empty { display: none; }
    .sx-students td.sx-act { padding-top: .55rem; }
    .sx-students td.sx-act .btn { width: 100%; justify-content: center; padding-top: .5rem; padding-bottom: .5rem; }

    .sx-notice { font-size: .82rem; }
    .sx-notice .btn { margin-top: .25rem; }
  }

  @media (min-width: 560px) and (max-width: 760px) {
    .sx-table tbody { grid-template-columns: repeat(2, minmax(0, 1fr)); align-items: start; }
  }

  html[data-theme="dark"] .sx-table tr { background: transparent; }
</style>

<div class="section-panel active">
<?php if ($view === 'grid'): ?>

  <div class="sx-head">
    <div>
      <div class="page-title">Sections</div>
    </div>
    <div class="sx-actions">
      <input class="search-input" type="text" id="sx-filter" placeholder="Search section..." oninput="sxFilter()" aria-label="Search section" autocomplete="off">
      <?php if ($canSections): ?>
        <button class="btn btn-primary" onclick="sxOpenSection()"><?= a_icon('plus') ?> Add section</button>
      <?php endif; ?>
    </div>
  </div>

  <?php
    // Students enrolled in a subject their section doesn't take (no teacher handles it for them)
    $outside = [];
    if ($canEnroll || $can('view_enrollment')) {
      $res = $conn->query(
        "SELECT CONCAT(u.last_name, ', ', u.first_name) AS student, u.section_dept, GROUP_CONCAT(c.course_code ORDER BY c.course_code SEPARATOR ', ') AS subjects
         FROM enrollments e JOIN users u ON u.id = e.student_id JOIN courses c ON c.id = e.course_id
         WHERE e.status = 'Enrolled' AND u.role = 'Student'
           AND NOT EXISTS (SELECT 1 FROM teacher_assignments ta WHERE ta.course_id = e.course_id AND ta.section = u.section_dept)
         GROUP BY u.id ORDER BY u.last_name, u.first_name"
      );
      while ($res && $r = $res->fetch_assoc()) $outside[] = $r;
    }
  ?>
  <?php if ($outside): ?>
    <div class="sx-notice" id="outside" style="border-color:#f5c26b;">
      <strong><?= count($outside) ?> student<?= count($outside) === 1 ? '' : 's' ?> enrolled in subjects their section doesn't take.</strong>
      No teacher handles these subjects for them, so the student sees a subject nobody teaches and no teacher sees the student.
      <ul style="margin:.5rem 0 .6rem 1.1rem;padding:0;">
        <?php foreach ($outside as $o): ?>
          <li><?= htmlspecialchars($o['student']) ?> (<?= htmlspecialchars($o['section_dept'] ?: 'no section') ?>): <?= htmlspecialchars($o['subjects']) ?></li>
        <?php endforeach; ?>
      </ul>
      <?php if ($canEnroll): ?>
        <button class="btn btn-primary" type="button" id="sx-drop-outside" onclick="sxDropOutside(this)">Mark these enrollments Dropped</button>
        <span style="font-size:.78rem;color:var(--text-light, #64748b);margin-left:.5rem;">Kept for the record (not deleted); hidden from the student.</span>
      <?php endif; ?>
    </div>
  <?php endif; ?>

  <?php if (!$hasTable && $isHeadAdmin): ?>
    <div class="sx-notice">Sections can't be added or renamed yet. Import <strong>database/migration_sections_subjects.sql</strong> in phpMyAdmin to turn this on.</div>
  <?php endif; ?>

  <?php $first = true; foreach (['SHS' => 'Senior High School', 'HS' => 'High School', 'Inactive' => 'Inactive sections'] as $key => $label): ?>
    <?php if (!$groups[$key]) continue; ?>
    <div class="sx-group<?= $first ? ' first' : '' ?>"><?= $label ?></div>
    <?php $first = false; ?>
    <div class="sx-grid" data-grid>
      <?php foreach ($groups[$key] as $s):
        $n  = $studentCounts[$s['name']] ?? 0;
        $sc = $subjectCounts[$s['name']] ?? ['total' => 0, 'missing' => 0]; ?>
        <a class="sx-card<?= $s['status'] === 'Inactive' ? ' inactive' : '' ?>" href="<?= htmlspecialchars(sectionUrl($s['name'])) ?>" data-name="<?= htmlspecialchars(strtolower($s['name'])) ?>">
          <div class="sx-name"><?= htmlspecialchars($s['name']) ?></div>
          <div class="sx-meta">
            <span><?= $n ?> student<?= $n === 1 ? '' : 's' ?></span>
            <span><?= $sc['total'] ?> subject<?= $sc['total'] === 1 ? '' : 's' ?></span>
          </div>
          <?php if ($sc['total'] === 0): ?>
            <span class="sx-pill muted">No subjects yet</span>
          <?php elseif ($sc['missing'] > 0): ?>
            <span class="sx-pill warn"><?= $sc['missing'] ?> need<?= $sc['missing'] === 1 ? 's' : '' ?> a teacher</span>
          <?php else: ?>
            <span class="sx-pill ok">All subjects have teachers</span>
          <?php endif; ?>
        </a>
      <?php endforeach; ?>
    </div>
  <?php endforeach; ?>

<?php else: ?>

  <a class="sx-back" href="subjects-enrollment.php"><span class="back-arrow" aria-hidden="true">&larr;</span> All sections</a>
  <div class="sx-head">
    <div>
      <div class="page-title"><?= htmlspecialchars($sectionName) ?>
        <?php if ($section['status'] === 'Inactive'): ?><span class="sx-tag">Inactive</span><?php endif; ?>
      </div>
    </div>
    <div class="sx-actions">
      <?php if ($canSections && $section['id']): ?>
        <button class="btn btn-reset-form" onclick="sxOpenSection(<?= htmlspecialchars(json_encode($section), ENT_QUOTES) ?>)"><?= a_icon('edit') ?> Edit section</button>
      <?php endif; ?>
      <?php if ($tab === 'subjects' && $canAssign): ?>
        <button class="btn btn-primary" onclick="sxOpenAdd()"><?= a_icon('plus') ?> Add subjects</button>
      <?php endif; ?>
    </div>
  </div>

  <nav class="adm-tabs" aria-label="<?= htmlspecialchars($sectionName) ?>">
    <a class="adm-tab<?= $tab === 'subjects' ? ' on' : '' ?>" href="<?= htmlspecialchars(sectionUrl($sectionName, 'subjects')) ?>" <?= $tab === 'subjects' ? 'aria-current="page"' : '' ?>>
      Subjects and teachers<span class="adm-count<?= $missing ? ' warn' : '' ?>"><?= count($subjects) ?></span></a>
    <a class="adm-tab<?= $tab === 'students' ? ' on' : '' ?>" href="<?= htmlspecialchars(sectionUrl($sectionName, 'students')) ?>" <?= $tab === 'students' ? 'aria-current="page"' : '' ?>>
      Students<span class="adm-count<?= $notFullyEnrolled ? ' warn' : '' ?>"><?= $activeStudents ?></span></a>
  </nav>

  <?php if ($tab === 'subjects'): ?>
    <div class="table-card">
      <?php if (!$subjects): ?>
        <div class="sx-empty">
          <strong>No subjects in this section yet</strong>
          <?= $canAssign ? 'Add the subjects this section takes, then pick a teacher for each.' : 'An admin with the Assign Teachers permission can add them.' ?>
        </div>
      <?php else: ?>
        <div class="sx-scroll">
          <table class="sx-table sx-subjects">
            <thead><tr><th>Subject</th><th>Term</th><th>Teacher</th><?php if ($canAssign): ?><th style="width:1%"></th><?php endif; ?></tr></thead>
            <tbody>
              <?php foreach ($subjects as $s): $tid = $s['valid_teacher_id'] ? (int) $s['valid_teacher_id'] : 0; ?>
                <tr>
                  <td class="sx-subj"><div class="sx-code"><?= htmlspecialchars($s['course_name']) ?></div><div class="sx-cname"><?= htmlspecialchars($s['course_code']) ?></div></td>
                  <td class="sx-term" data-label="Term"><?= elmsSetSpan('term_short') /* current term from Admin → Settings */ ?></td>
                  <td class="sx-tcell" data-label="Teacher">
                    <?php if ($canAssign): ?>
                      <select class="form-input sx-teacher<?= $tid ? '' : ' none' ?>" data-prev="<?= $tid ?: '' ?>" onchange="sxSetTeacher(this, <?= (int) $s['id'] ?>)" aria-label="Teacher for <?= htmlspecialchars($s['course_code']) ?>">
                        <option value="">Needs a teacher</option>
                        <?php foreach ($teachers as $t): ?>
                          <option value="<?= (int) $t['id'] ?>" <?= $tid === (int) $t['id'] ? 'selected' : '' ?>><?= htmlspecialchars($t['name']) ?></option>
                        <?php endforeach; ?>
                      </select>
                    <?php elseif ($tid): ?>
                      <?= htmlspecialchars($s['teacher_name']) ?>
                    <?php else: ?>
                      <span class="sx-none">Needs a teacher</span>
                    <?php endif; ?>
                  </td>
                  <?php if ($canAssign): ?>
                    <td class="sx-del"><button class="btn btn-danger btn-sm sx-icon-btn" title="Remove from section" aria-label="Remove <?= htmlspecialchars($s['course_code']) ?>"
                                onclick="sxRemoveSubject(<?= (int) $s['id'] ?>, <?= htmlspecialchars(json_encode($s['course_code']), ENT_QUOTES) ?>)"><?= a_icon('trash') ?></button></td>
                  <?php endif; ?>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>

  <?php else: ?>
    <?php if ($canEnroll && $activeStudents && $sectionCourseIds): ?>
      <div class="sx-bar<?= $notFullyEnrolled ? '' : ' done' ?>">
        <span><?= $notFullyEnrolled
          ? "$notFullyEnrolled of $activeStudents student" . ($activeStudents === 1 ? ' isn’t' : 's aren’t') . ' enrolled in every subject of this section.'
          : 'Every student is enrolled in all ' . count($sectionCourseIds) . ' subjects of this section.' ?></span>
        <?php if ($notFullyEnrolled): ?>
          <button class="btn btn-primary btn-sm" onclick="sxEnrollAll(this)"><?= a_icon('check') ?> Enroll all in all subjects</button>
        <?php endif; ?>
      </div>
    <?php endif; ?>

    <div class="table-card">
      <?php if (!$students): ?>
        <div class="sx-empty">
          <strong>No students in this section yet</strong>
          Students show up here once their section is set to <?= htmlspecialchars($sectionName) ?> in <a href="accounts.php">Accounts</a>.
        </div>
      <?php else: ?>
        <div class="sx-scroll">
          <table class="sx-table sx-students">
            <thead><tr><th>Student</th><th>LRN</th><th>Enrolled subjects</th><?php if ($canEnroll): ?><th style="width:1%"></th><?php endif; ?></tr></thead>
            <tbody>
              <?php foreach ($students as $s):
                $have = count($s['courses']); $total = count($sectionCourseIds); ?>
                <tr>
                  <td class="sx-stu"><strong><?= htmlspecialchars($s['last_name'] . ', ' . $s['first_name']) ?></strong>
                    <?php if ($s['status'] === 'Inactive'): ?> <span class="sx-tag">Inactive</span><?php endif; ?></td>
                  <td class="sx-lrn" data-label="LRN"><?= htmlspecialchars($s['school_id']) ?></td>
                  <td class="sx-prog" data-label="Enrolled subjects">
                    <?php if (!$total): ?><span class="sx-cname">No subjects yet</span>
                    <?php else: ?><span class="sx-progress <?= $have >= $total ? 'full' : 'part' ?>"><?= $have ?> of <?= $total ?></span><?php endif; ?>
                  </td>
                  <?php if ($canEnroll): ?>
                    <td class="sx-act"><?php if ($total): ?>
                      <button class="btn btn-edit btn-sm" data-student="<?= htmlspecialchars(json_encode(['id' => (int) $s['id'], 'name' => $s['last_name'] . ', ' . $s['first_name'], 'courses' => $s['courses']]), ENT_QUOTES) ?>"
                              onclick="sxOpenStudent(JSON.parse(this.dataset.student))">Manage</button>
                    <?php endif; ?></td>
                  <?php endif; ?>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>
  <?php endif; ?>

<?php endif; ?>
</div>

<?php if ($canSections): ?>
<div class="modal-overlay" id="sectionModal">
  <div class="modal">
    <div class="modal-title" id="sec-title">Add section</div>
    <input type="hidden" id="sec-id">
    <div class="form-error" id="sec-error"></div>
    <div class="sec-row" style="margin-bottom:.35rem;">
      <div class="form-group">
        <label class="form-label" for="sec-grade">Grade level *</label>
        <select class="form-input" id="sec-grade" onchange="sxSecPreview()">
          <optgroup label="High School">
            <option value="7">Grade 7</option>
            <option value="8">Grade 8</option>
            <option value="9">Grade 9</option>
            <option value="10">Grade 10</option>
          </optgroup>
          <optgroup label="Senior High School">
            <option value="11">Grade 11</option>
            <option value="12">Grade 12</option>
          </optgroup>
          <option value="other" id="sec-grade-other" hidden disabled>Other (keep the name as typed)</option>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label" for="sec-part" id="sec-part-label">Section *</label>
        <input class="form-input" id="sec-part" placeholder="e.g. STEM-A" maxlength="60" autocomplete="off" oninput="sxSecPreview()">
      </div>
    </div>
    <div class="sec-hint" id="sec-hint" style="margin-bottom:1rem;"></div>
    <div class="form-group" id="sec-status-wrap">
      <label class="form-label" for="sec-status">Status</label>
      <select class="form-input" id="sec-status">
        <option value="Active">Active</option>
        <option value="Inactive">Inactive — hidden from section dropdowns</option>
      </select>
    </div>
    <div class="modal-actions" style="justify-content:space-between;">
      <button class="btn btn-danger" id="sec-delete" onclick="sxDeleteSection(this)">Delete</button>
      <div style="display:flex;gap:.65rem;margin-left:auto;">
        <button class="btn btn-reset-form" onclick="closeModal('sectionModal')">Cancel</button>
        <button class="btn btn-primary" onclick="sxSaveSection(this)">Save</button>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>

<?php if ($view === 'detail' && $canAssign): ?>
<div class="modal-overlay" id="addModal">
  <div class="modal" style="max-width:560px;">
    <div class="modal-title">Add subjects to <?= htmlspecialchars($sectionName) ?></div>
    <div class="form-error" id="add-error"></div>
    <label class="form-label">Subjects *</label>
    <input class="form-input sx-search" placeholder="Search subjects..." oninput="sxFilterList(this, 'add-list')" aria-label="Search subjects" autocomplete="off">
    <div class="sx-checklist" id="add-list">
      <?php foreach (['own' => $sectionName . ' subjects', 'other' => 'Other subjects'] as $gk => $glabel):
        if (!$courseGroups[$gk]) continue; ?>
        <?php if ($courseGroups['own']): ?><div class="sx-list-head" data-head><?= htmlspecialchars($glabel) ?></div><?php endif; ?>
        <?php foreach ($courseGroups[$gk] as $c): $taken = in_array((int) $c['id'], $sectionCourseIds, true); ?>
          <label class="<?= $taken ? 'taken' : '' ?>" data-text="<?= htmlspecialchars(strtolower($c['course_code'] . ' ' . $c['course_name'])) ?>">
            <input type="checkbox" value="<?= (int) $c['id'] ?>" <?= $taken ? 'disabled checked' : '' ?>>
            <span><strong><?= htmlspecialchars($c['course_name']) ?></strong> <span class="sx-cname"><?= htmlspecialchars($c['course_code']) ?></span><?= $taken ? ' <span class="sx-cname">· already added</span>' : '' ?></span>
          </label>
        <?php endforeach; ?>
      <?php endforeach; ?>
    </div>
    <div class="form-grid" style="margin-top:1rem;">
      <div class="form-group full">
        <label class="form-label" for="add-teacher">Teacher <span style="color:var(--ui-text-3, #aaa);font-weight:400;">(optional — you can pick one later)</span></label>
        <select class="form-input" id="add-teacher">
          <option value="">Pick later</option>
          <?php foreach ($teachers as $t): ?><option value="<?= (int) $t['id'] ?>"><?= htmlspecialchars($t['name']) ?></option><?php endforeach; ?>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label" for="add-sy">School year</label>
        <input class="form-input" id="add-sy" data-elms-default="school_year" value="<?= htmlspecialchars($defaultSY) ?>">
      </div>
      <div class="form-group">
        <label class="form-label" for="add-sem">Semester</label>
        <select class="form-input" id="add-sem" data-elms-default="semester"><?php foreach (['1st' => '1st semester', '2nd' => '2nd semester', 'Summer' => 'Summer'] as $__v => $__l): ?><option value="<?= $__v ?>"<?= $defaultSem === $__v ? ' selected' : '' ?>><?= $__l ?></option><?php endforeach; ?></select>
      </div>
    </div>
    <div class="modal-actions">
      <button class="btn btn-reset-form" onclick="closeModal('addModal')">Cancel</button>
      <button class="btn btn-primary" onclick="sxAddSubjects(this)">Add subjects</button>
    </div>
  </div>
</div>
<?php endif; ?>

<?php if ($view === 'detail' && $canEnroll): ?>
<div class="modal-overlay" id="studentModal">
  <div class="modal" style="max-width:520px;">
    <div class="modal-title" id="stu-title">Subjects</div>
    <div class="form-error" id="stu-error"></div>
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:.5rem;">
      <span class="form-label" style="margin:0;">Enrolled in</span>
      <span style="display:flex;gap:.75rem;font-size:.8rem;">
        <a href="#" class="sx-link" onclick="sxCheckAll(true);return false;">Select all</a>
        <a href="#" class="sx-link" onclick="sxCheckAll(false);return false;">Clear</a>
      </span>
    </div>
    <div class="sx-checklist" id="stu-list">
      <?php foreach ($sectionCourses as $cid => $label): ?>
        <label><input type="checkbox" value="<?= (int) $cid ?>"> <span><?= htmlspecialchars($label) ?></span></label>
      <?php endforeach; ?>
    </div>
    <div class="modal-actions">
      <button class="btn btn-reset-form" onclick="closeModal('studentModal')">Cancel</button>
      <button class="btn btn-primary" onclick="sxSaveStudent(this)">Save</button>
    </div>
  </div>
</div>
<?php endif; ?>

<script>
  const SX_SECTION = <?= json_encode($sectionName) ?>;

  async function sxPost(action, body) {
    const res = await fetch('../api/sections.php?action=' + action, { method: 'POST', body: JSON.stringify(body) });
    return res.json();
  }
  function sxOpen(id) { document.getElementById(id).classList.add('show'); }
  function sxErr(id, msg) { const e = document.getElementById(id); e.textContent = msg; e.style.display = msg ? 'block' : 'none'; }
  function sxReload(msg) { showToast(msg); setTimeout(() => location.reload(), 700); }
  async function sxDropOutside(btn) {
    if (!confirm('Mark these enrollments as Dropped? Students will no longer see those subjects. Nothing is deleted.')) return;
    btn.disabled = true;
    try {
      const d = await sxPost('drop_outside_enrollments', {});
      if (d.success) sxReload(d.message); else { showToast(d.message || 'Could not save.', true); btn.disabled = false; }
    } catch (e) { showToast('Network error. Try again.', true); btn.disabled = false; }
  }

  // ── Section cards ──
  function sxFilter() {
    const q = (document.getElementById('sx-filter')?.value || '').toLowerCase().trim();
    document.querySelectorAll('[data-grid]').forEach(grid => {
      let shown = 0;
      grid.querySelectorAll('.sx-card').forEach(c => { const m = c.dataset.name.includes(q); c.style.display = m ? '' : 'none'; if (m) shown++; });
      grid.style.display = shown ? '' : 'none';
      grid.previousElementSibling.style.display = shown ? '' : 'none';
    });
  }

  // ── Section add / edit / delete ──
  // Section names are "Grade N" or "Grade N - <section>": Grade 7 to 10 = High School, Grade 11 and 12 = Senior High
  // (the server applies the same rules and works out the level from the grade).
  let sxSecOldLevel = 'SHS';   // level of a section whose name is not "Grade N …" (only when editing one)
  const sxSecHS = g => Number(g) >= 7 && Number(g) <= 10;
  function sxSecPart() { return document.getElementById('sec-part').value.replace(/\s+/g, ' ').trim(); }
  function sxSecName() {
    const g = document.getElementById('sec-grade').value, part = sxSecPart();
    if (g === 'other') return part;
    return 'Grade ' + g + (part ? ' - ' + part : '');
  }
  function sxSecLevel() {
    const g = document.getElementById('sec-grade').value;
    return g === 'other' ? sxSecOldLevel : (sxSecHS(g) ? 'HS' : 'SHS');
  }
  function sxSecPreview() {
    const g = document.getElementById('sec-grade').value, hs = sxSecHS(g), other = g === 'other';
    document.getElementById('sec-part-label').innerHTML = other ? 'Section name *'
      : (hs ? 'Section <span style="color:var(--ui-text-3, #aaa);font-weight:400;">(optional)</span>' : 'Strand and section *');
    document.getElementById('sec-part').placeholder = other ? 'Full section name' : (hs ? 'e.g. Rizal' : 'e.g. STEM-A');
    const name = sxSecName();
    document.getElementById('sec-hint').textContent = other
      ? 'This section\'s name does not start with a grade. Pick a grade to rename it, or keep it as it is.'
      : `Full name: ${name} · listed under ${hs ? 'High School' : 'Senior High School'}`;
  }
  function sxOpenSection(sec) {
    sxErr('sec-error', '');
    const editing = !!sec;
    document.getElementById('sec-title').textContent = editing ? 'Edit section' : 'Add section';
    document.getElementById('sec-id').value = editing ? sec.id : '';
    const other = document.getElementById('sec-grade-other');
    other.hidden = true; other.disabled = true;
    let grade = '11', part = '';
    if (editing) {
      const m = String(sec.name).match(/^Grade (\d+)(?: - (.+))?$/);
      if (m && Number(m[1]) >= 7 && Number(m[1]) <= 12) { grade = m[1]; part = m[2] || ''; }
      else { other.hidden = false; other.disabled = false; grade = 'other'; part = sec.name; sxSecOldLevel = sec.level === 'HS' ? 'HS' : 'SHS'; }
    }
    document.getElementById('sec-grade').value = grade;
    document.getElementById('sec-part').value = part;
    document.getElementById('sec-status').value = editing ? sec.status : 'Active';
    document.getElementById('sec-status-wrap').style.display = editing ? '' : 'none';
    document.getElementById('sec-delete').style.display = editing ? '' : 'none';
    sxSecPreview();
    sxOpen('sectionModal');
    document.getElementById(editing ? 'sec-part' : 'sec-grade').focus();
  }
  async function sxSaveSection(btn) {
    const id = Number(document.getElementById('sec-id').value) || 0;
    const body = {
      id,
      name: sxSecName(),
      level: sxSecLevel(),
      status: document.getElementById('sec-status').value,
    };
    const g = document.getElementById('sec-grade').value, part = sxSecPart();
    if (g === 'other' && !part) return sxErr('sec-error', 'Enter a section name.');
    if (g !== 'other' && !sxSecHS(g) && !part) return sxErr('sec-error', 'Enter the strand and section, e.g. STEM-A.');
    if (part && !/^[\p{L}\p{N}][\p{L}\p{N} .'&()\/-]*$/u.test(part)) return sxErr('sec-error', 'Use letters, numbers, spaces and - . \' & ( ) / only in the section.');
    if (body.name.length > 100) return sxErr('sec-error', 'The section name is too long (up to 100 characters).');
    setBtnLoading(btn, 'Saving...');
    try {
      const d = await sxPost(id ? 'update_section' : 'create_section', body);
      if (!d.success) { clearBtnLoading(btn); return sxErr('sec-error', d.message); }
      showToast(d.message);
      setTimeout(() => location.href = 'subjects-enrollment.php?section=' + encodeURIComponent(body.name), 700);
    } catch (e) { clearBtnLoading(btn); sxErr('sec-error', 'Network error. Try again.'); }
  }
  async function sxDeleteSection(btn) {
    if (!await confirmAction('Delete this section? This only works if no accounts or subjects use it.', { okText: 'Delete section' })) return;
    setBtnLoading(btn, 'Deleting...');
    try {
      const d = await sxPost('delete_section', { id: Number(document.getElementById('sec-id').value) });
      if (!d.success) { clearBtnLoading(btn); return sxErr('sec-error', d.message); }
      showToast(d.message);
      setTimeout(() => location.href = 'subjects-enrollment.php', 700);
    } catch (e) { clearBtnLoading(btn); sxErr('sec-error', 'Network error. Try again.'); }
  }

  // ── Subjects and teachers ──
  function sxOpenAdd() {
    sxErr('add-error', '');
    document.querySelectorAll('#add-list input:not(:disabled)').forEach(c => c.checked = false);
    sxOpen('addModal');
  }
  function sxFilterList(input, listId) {
    const q = input.value.toLowerCase();
    document.querySelectorAll('#' + listId + ' label').forEach(l => l.style.display = l.dataset.text.includes(q) ? '' : 'none');
    document.querySelectorAll('#' + listId + ' [data-head]').forEach(h => h.style.display = q ? 'none' : '');
  }
  async function sxAddSubjects(btn) {
    const ids = [...document.querySelectorAll('#add-list input:checked:not(:disabled)')].map(c => Number(c.value));
    if (!ids.length) return sxErr('add-error', 'Pick at least one subject.');
    setBtnLoading(btn, 'Adding...');
    try {
      const d = await sxPost('add_subject', {
        section: SX_SECTION, course_ids: ids,
        teacher_id: document.getElementById('add-teacher').value || null,
        school_year: document.getElementById('add-sy').value.trim(),
        semester: document.getElementById('add-sem').value,
      });
      if (!d.success) { clearBtnLoading(btn); return sxErr('add-error', d.message); }
      sxReload(d.message);
    } catch (e) { clearBtnLoading(btn); sxErr('add-error', 'Network error. Try again.'); }
  }
  async function sxSetTeacher(sel, id) {
    // Ask first, so a teacher isn't assigned / changed / removed by an accidental click
    const row = sel.closest('tr');
    const subj = row && row.querySelector('.sx-code') ? row.querySelector('.sx-code').textContent.trim() : 'this subject';
    const label = o => (o && o.value) ? o.text.trim() : '';
    const newName = label(sel.options[sel.selectedIndex]);
    const prevOpt = Array.from(sel.options).find(o => o.value === (sel.dataset.prev || ''));
    const prevName = label(prevOpt);
    let msg, title, okText, danger = false;
    if (!newName) { msg = `Remove ${prevName || 'the teacher'} from ${subj} (${SX_SECTION})? The subject will have no teacher.`; title = 'Remove teacher'; okText = 'Remove teacher'; danger = true; }
    else if (prevName) { msg = `Change the teacher of ${subj} (${SX_SECTION}) from ${prevName} to ${newName}?`; title = 'Change teacher'; okText = 'Yes, change'; }
    else { msg = `Assign ${newName} as the teacher of ${subj} (${SX_SECTION})?`; title = 'Assign teacher'; okText = 'Yes, assign'; }
    if (!await confirmAction(msg, { title, okText, danger })) { sel.value = sel.dataset.prev || ''; return; }
    sel.disabled = true;
    try {
      const d = await sxPost('set_teacher', { id, teacher_id: sel.value || null });
      if (!d.success) { sel.value = sel.dataset.prev; showToast(d.message, true); }
      else { sel.dataset.prev = sel.value; sel.classList.toggle('none', !sel.value); showToast(d.message); }
    } catch (e) { sel.value = sel.dataset.prev; showToast('Network error. Try again.', true); }
    sel.disabled = false;
  }
  async function sxRemoveSubject(id, code) {
    if (!await confirmAction(`Remove ${code} from ${SX_SECTION}? Students keep their enrollment records.`, { okText: 'Remove subject' })) return;
    try {
      const d = await sxPost('remove_subject', { id });
      d.success ? sxReload(d.message) : showToast(d.message, true);
    } catch (e) { showToast('Network error. Try again.', true); }
  }

  // ── Students and enrollment ──
  async function sxEnrollAll(btn) {
    if (!await confirmAction(`Enroll every active student in ${SX_SECTION} in all of this section's subjects?`, { okText: 'Enroll all', danger: false })) return;
    setBtnLoading(btn, 'Enrolling...');
    try {
      const d = await sxPost('enroll_all', { section: SX_SECTION });
      if (!d.success) { clearBtnLoading(btn); return showToast(d.message, true); }
      sxReload(d.message);
    } catch (e) { clearBtnLoading(btn); showToast('Network error. Try again.', true); }
  }
  let sxStudentId = 0;
  function sxOpenStudent(s) {
    sxStudentId = s.id;
    sxErr('stu-error', '');
    document.getElementById('stu-title').textContent = s.name;
    document.querySelectorAll('#stu-list input').forEach(c => c.checked = s.courses.includes(Number(c.value)));
    sxOpen('studentModal');
  }
  function sxCheckAll(on) { document.querySelectorAll('#stu-list input').forEach(c => c.checked = on); }
  async function sxSaveStudent(btn) {
    const ids = [...document.querySelectorAll('#stu-list input:checked')].map(c => Number(c.value));
    setBtnLoading(btn, 'Saving...');
    try {
      const d = await sxPost('set_student_subjects', { student_id: sxStudentId, section: SX_SECTION, course_ids: ids });
      if (!d.success) { clearBtnLoading(btn); return sxErr('stu-error', d.message); }
      sxReload(d.message);
    } catch (e) { clearBtnLoading(btn); sxErr('stu-error', 'Network error. Try again.'); }
  }
</script>

<?php require __DIR__ . '/../includes/footer.php'; ?>