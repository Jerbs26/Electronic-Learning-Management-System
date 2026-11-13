<?php
// ============================================================
//  Arandia College eLMS — "Needs attention" items for admins
//  File: includes/admin_attention.php
//  Shared by the dashboard card and the top-bar notification bell.
//  Each item: [title, subtitle, link, tone ('warn' | 'muted')]
// ============================================================

if (!function_exists('adminAttentionItems')) {
  function adminAttentionItems(mysqli $conn, callable $can): array
  {
    $one = function (string $sql) use ($conn): int {
      try {
        $r = $conn->query($sql);
        return $r ? (int) ($r->fetch_row()[0] ?? 0) : 0;
      } catch (Throwable $e) {
        error_log('[attention] ' . $e->getMessage());
        return 0;
      }
    };
    $items = [];

    if ($can('assign_teachers', 'view_subjects', 'enroll_students', 'view_enrollment', 'view_classes', 'manage_classes')) {
      try {
        $r = $conn->query(
          "SELECT COUNT(*) AS n, COUNT(DISTINCT ta.section) AS s
           FROM teacher_assignments ta
           LEFT JOIN users u ON u.id = ta.teacher_id AND u.role = 'Teacher' AND u.status = 'Active'
           WHERE u.id IS NULL"
        )->fetch_assoc();
      } catch (Throwable $e) {
        $r = ['n' => 0, 's' => 0];
      }
      if ((int) $r['n'] > 0) {
        $items[] = [$r['n'] . ' subject' . ($r['n'] == 1 ? '' : 's') . ' need a teacher',
                    'In ' . $r['s'] . ' section' . ($r['s'] == 1 ? '' : 's'), 'subjects-enrollment.php', 'warn'];
      }
      $n = $one("SELECT COUNT(DISTINCT ta.section) FROM teacher_assignments ta
                 WHERE NOT EXISTS (SELECT 1 FROM users u WHERE u.role = 'Student' AND u.status = 'Active' AND u.section_dept = ta.section)");
      if ($n > 0) {
        $items[] = [$n . ' section' . ($n == 1 ? ' has' : 's have') . ' subjects but no students',
                    "Set students' sections in Accounts", 'subjects-enrollment.php', 'muted'];
      }
      $n = $one("SELECT COUNT(*) FROM (
                   SELECT u.id,
                     (SELECT COUNT(DISTINCT ta.course_id) FROM teacher_assignments ta WHERE ta.section = u.section_dept) AS total,
                     (SELECT COUNT(DISTINCT e.course_id) FROM enrollments e
                        JOIN teacher_assignments ta ON ta.course_id = e.course_id AND ta.section = u.section_dept
                       WHERE e.student_id = u.id AND e.status = 'Enrolled') AS have
                   FROM users u WHERE u.role = 'Student' AND u.status = 'Active'
                 ) x WHERE total > have");
      if ($n > 0) {
        $items[] = [$n . ' student' . ($n == 1 ? ' is' : 's are') . ' not enrolled in every subject',
                    'Use "Enroll all" in the section', 'subjects-enrollment.php', 'warn'];
      }
      // Enrolled in a subject their section doesn't take → no teacher can see them there
      $n = $one("SELECT COUNT(*) FROM enrollments e JOIN users u ON u.id = e.student_id
                 WHERE e.status = 'Enrolled' AND u.role = 'Student'
                   AND NOT EXISTS (SELECT 1 FROM teacher_assignments ta WHERE ta.course_id = e.course_id AND ta.section = u.section_dept)");
      if ($n > 0) {
        $items[] = [$n . ' enrollment' . ($n == 1 ? ' is' : 's are') . " outside the student's section",
                    'No teacher sees these students — review on Sections', 'subjects-enrollment.php#outside', 'warn'];
      }
    }

    if ($can('manage_modules')) {
      $hasMod = $conn->query("SHOW COLUMNS FROM modules LIKE 'module_number'");
      if ($hasMod && $hasMod->num_rows) {
        $n = $one("SELECT COUNT(*) FROM courses c WHERE c.status = 'Active'
                     AND (SELECT COUNT(*) FROM modules m WHERE m.course_id = c.id) < 2");
        if ($n > 0) {
          $items[] = [$n . ' subject' . ($n == 1 ? ' has' : 's have') . ' fewer than 2 modules',
                      'Add lessons in Subjects', 'modules.php', 'muted'];
        }
      }
    }
    return $items;
  }
}
