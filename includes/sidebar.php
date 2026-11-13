<?php
// ============================================================
//  Arandia College eLMS
//  File: includes/sidebar.php  (do not rename or move this file)
// ============================================================
?>
<?php
require_once __DIR__ . '/class_nav.php';
require_once __DIR__ . '/student_subject_nav.php';
$__role = $_SESSION['role'] ?? '';
$__isHead = $__role === 'Head Admin';
$__can = function (...$keys) use ($conn, $__isHead) {
    if ($__isHead) return true;
    foreach ($keys as $k) { if (adminHasPermission($conn, $k)) return true; }
    return false;
};
?>
<?php if (in_array($__role, ['Admin', 'Head Admin', 'Admission', 'Teacher', 'Student'], true)): ?>
<!-- Phones/tablets: dims the page behind the open sidebar drawer; tap to close (assets/js/elms-nav.js) -->
<div class="nav-backdrop" data-nav-close hidden></div>
<?php endif; ?>
<?php if ($__role === 'Admin' || $__role === 'Head Admin'): ?>
<aside class="sidebar" id="appSidebar" aria-label="Main menu">
    <div class="sidebar-header">
      <button type="button" class="nav-close" data-nav-close aria-label="Close menu"><svg viewBox="0 0 24 24" aria-hidden="true"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>
      <a class="sidebar-brand" href="dashboard.php" aria-label="Go to Dashboard" title="Dashboard">
        <img src="../picture/logo.png" alt="Logo" class="sidebar-logo" onerror="this.style.display='none'">
        <div class="sidebar-brand-text">
          <strong>Arandia College</strong>
        </div>
      </a>
    </div>

    <nav class="sidebar-nav">
      <?php navlink('dashboard.php', a_icon('home'), 'Dashboard', $currentPage); ?>
      <?php if ($__can('view_students', 'view_teachers', 'manage_admin_accounts', 'manage_students', 'manage_teachers')): ?>
        <?php navlink('accounts.php', a_icon('users'), 'Accounts', $currentPage); ?>
      <?php endif; ?>
      <?php if ($__can('assign_teachers', 'view_subjects', 'enroll_students', 'view_enrollment', 'view_classes', 'manage_classes')): ?>
        <?php navlink('subjects-enrollment.php', a_icon('book'), 'Sections', $currentPage); ?>
      <?php endif; ?>
      <?php if ($__can('manage_modules')): ?>
        <?php navlink('modules.php', a_icon('folder'), 'Subjects', $currentPage); ?>
      <?php endif; ?>
      <?php navlink('announcements.php', a_icon('bell'), 'Announcements', $currentPage); ?>
      <?php
        // Contact Us inbox — badge shows how many messages no admin has opened yet
        require_once __DIR__ . '/contact_messages.php';
        $__msgNew = countNewContactMessages($conn);
        navlink('messages.php', a_icon('inbox'),
          'Messages<span data-msg-badge style="margin-left:auto;min-width:20px;height:20px;padding:0 6px;border-radius:999px;background:#FFC53D;color:#0B2545;font-size:.7rem;font-weight:700;display:' . ($__msgNew ? 'inline-flex' : 'none') . ';align-items:center;justify-content:center;">' . $__msgNew . '</span>',
          $currentPage);
      ?>
      <?php if ($__can('view_awards', 'manage_awards')): ?>
        <?php navlink('awards.php', a_icon('award'), 'Awards', $currentPage); ?>
      <?php endif; ?>
      <?php if ($__isHead): ?>
        <?php navlink('admins.php', a_icon('shield'), 'Admins and Logs', $currentPage); ?>
      <?php endif; ?>
      <?php navlink('settings.php', a_icon('settings'), 'Settings', $currentPage); ?>
      <?php // Sign out, Dark mode and the account name are in the top-bar profile menu (includes/topbar.php) ?>
    </nav>
  </aside>
<?php elseif ($__role === 'Admission'): ?>
<aside class="sidebar" id="appSidebar" aria-label="Main menu">
    <div class="sidebar-header">
      <button type="button" class="nav-close" data-nav-close aria-label="Close menu"><svg viewBox="0 0 24 24" aria-hidden="true"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>
      <a class="sidebar-brand" href="dashboard.php" aria-label="Go to Dashboard" title="Dashboard">
        <img src="../picture/logo.png" alt="Logo" class="sidebar-logo" onerror="this.style.display='none'">
        <div class="sidebar-brand-text">
          <strong>Arandia College</strong>
        </div>
      </a>
    </div>

    <nav class="sidebar-nav">
      <?php // Admission office: enroll students only (admission/ folder) ?>
      <?php navlink('dashboard.php', a_icon('home'), 'Dashboard', $currentPage); ?>
      <?php navlink('enroll.php', a_icon('user-plus'), 'Enroll Student', $currentPage); ?>
      <?php navlink('students.php', a_icon('graduation'), 'Students', $currentPage); ?>
    </nav>
  </aside>
<?php elseif ($__role === 'Teacher'): ?>
<?php
  // Inside a class the main menu becomes an icon rail and the class menu opens
  // beside it (includes/class_nav.php). The picker pages always print the class
  // menu; it stays hidden until a class is picked.
  $__cnavClass = null;
  $__cnavOn = false;
  if (isset(classNavPages()[$currentPage]) && !empty($mySubjects) && is_array($mySubjects)) {
    $__cnavClass = classNavResolve($mySubjects, isset($classNavCourse) ? (int) $classNavCourse : null,
                                   isset($classNavSection) ? (string) $classNavSection : null);
    $__cnavOn = $__cnavClass || in_array($currentPage, ['assignments.php', 'quizzes.php', 'grades.php', 'attendance.php'], true);
  }
?>
<aside class="sidebar<?= $__cnavClass ? ' sidebar--rail' : '' ?>" id="appSidebar" aria-label="Main menu">
    <div class="sidebar-header">
      <button type="button" class="nav-close" data-nav-close aria-label="Close menu"><svg viewBox="0 0 24 24" aria-hidden="true"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>
      <a class="sidebar-brand" href="dashboard.php" aria-label="Go to Dashboard" title="Dashboard">
        <img src="../picture/logo.png" alt="Logo" class="sidebar-logo" onerror="this.style.display='none'">
        <div class="sidebar-brand-text">
          <strong>Arandia College</strong>
        </div>
      </a>
    </div>

    <nav class="sidebar-nav">
      <?php
        // Everything that belongs to a class lives under "My Classes": the class page and its
        // tabs (Modules, Assignments + grader, Quizzes, Grades, Attendance) and All students.
        $__tPage = in_array($currentPage, ['classes.php', 'my-subjects.php', 'my-students.php', 'modules.php',
                                           'assignments.php', 'grade.php', 'quizzes.php', 'grades.php', 'attendance.php'], true)
                   ? 'classes.php' : $currentPage;
      ?>
      <?php navlink('dashboard.php', t_icon('home'), 'Dashboard', $__tPage); ?>
      <?php navlink('classes.php', t_icon('book'), 'My Classes', $__tPage); ?>
      <?php navlink('announcements.php', t_icon('bell'), 'Announcements', $__tPage); ?>
      <?php navlink('awards.php', t_icon('award'), 'Awards', $__tPage); ?>
    </nav>
  </aside>
<?php if ($__cnavOn) renderClassNav($conn, $mySubjects, $__cnavClass, $currentPage); ?>
<?php elseif ($__role === 'Student'): ?>
<?php $svgAttendance = $svgAttendance ?? '<svg class="icon" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line><polyline points="9 15 11 17 15 13"></polyline></svg>'; ?>
<?php
  // Same layout as the Teacher side: inside one subject the main menu becomes
  // an icon rail and the subject menu opens beside it
  // (includes/student_subject_nav.php). Subject pages light up "My Subjects".
  $__sSubject = (isset($activeSubject) && is_array($activeSubject)) ? $activeSubject : null;
  $__sPage = isset(studentSubjectPages()[$currentPage]) ? 'my-subjects.php' : $currentPage;
?>
<aside class="sidebar<?= $__sSubject ? ' sidebar--rail' : '' ?>" id="appSidebar" aria-label="Main menu">
        <div class="sidebar-header">
      <button type="button" class="nav-close" data-nav-close aria-label="Close menu"><svg viewBox="0 0 24 24" aria-hidden="true"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>
            <a class="sidebar-brand" href="dashboard.php" aria-label="Go to Dashboard" title="Dashboard">
            <img src="../picture/logo.png" alt="Logo" class="sidebar-logo" onerror="this.style.display='none'">
            <div class="brand-text">
                <h1>Arandia College</h1>
            </div>
            </a>
        </div>

        <nav class="nav-menu">
            <?php
              // Modules, Assignments, Quizzes, Grades and Attendance live inside each
              // subject (My Subjects → a subject → the subject menu). Profile opens
              // from the picture in the top bar (My Profile), same as the Teacher side.
            ?>
            <?php navlink2('dashboard.php', $svgHome, 'Dashboard', $__sPage); ?>
            <?php navlink2('my-subjects.php', $svgBook, 'My Subjects', $__sPage); ?>
            <?php navlink2('announcements.php', $svgAnn, 'Announcements', $__sPage); ?>
            <?php navlink2('awards.php', $svgAward, 'Awards', $__sPage); ?>
        </nav>
    </aside>
<?php if ($__sSubject) renderStudentSubjectNav($allSubjects ?? [$__sSubject], $__sSubject, $currentPage, $subjectCounts ?? [], [
    'all' => '<svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><polygon points="12 2 2 7 12 12 22 7 12 2"></polygon><polyline points="2 17 12 22 22 17"></polyline><polyline points="2 12 12 17 22 12"></polyline></svg>',
    'modules' => $svgFolder, 'assignments' => $svgAssign, 'quizzes' => $svgQuiz, 'grades' => $svgGrades, 'attendance' => $svgAttendance,
]); ?>
<?php endif; ?>