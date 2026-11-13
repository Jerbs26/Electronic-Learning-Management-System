<?php
// ============================================================
//  Arandia College eLMS — Teacher pages: shared setup
//  File: teacher/_init.php
//  Login check + the data every teacher page (and the top bar / bell)
//  needs: $mySubjects, $myStudents, $studentsBySection, stats,
//  $myAnnouncements, $pendingSubmissions, etc. Each page then sets its
//  own $currentPage and $pageTitle.
// ============================================================
session_start();
require_once '../config/conn.php';
require_once __DIR__ . '/../includes/role_guard.php';
require_once __DIR__ . '/../includes/scope.php';
requireTeacherPage();   // login / role / still-active check

$first_name = $_SESSION['first_name'];
$last_name = $_SESSION['last_name'];
$user_id = (int) $_SESSION['user_id'];

$uInfo = $conn->query("SELECT section_dept FROM users WHERE id = $user_id LIMIT 1")->fetch_assoc();
$dept = $uInfo['section_dept'] ?? '';

//  My Assigned Subjects 
$mySubjects = [];
$res = $conn->query(
    "SELECT ta.id AS assign_id, ta.section, ta.school_year, ta.semester,
            c.id AS course_id, c.course_code, c.course_name, c.description
    FROM teacher_assignments ta
    JOIN courses c ON c.id = ta.course_id
    WHERE ta.teacher_id = $user_id
    ORDER BY ta.school_year DESC, c.course_code, ta.section"
);
while ($r = $res->fetch_assoc())
    $mySubjects[] = $r;

//  URL guard: a hand-edited ?class= / ?course= / ?section= must be MY class
// Class pages only open a class (or subject + section) the teacher is assigned to.
// Anything else (someone else's class, a subject that is not mine, a section I do
// not teach for it) goes back to My Classes instead of showing another page's data.
// Plain page loads only (GET); classes.php itself is the target, so it cannot loop.
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'GET'
    && in_array(basename((string) ($_SERVER['SCRIPT_NAME'] ?? '')), ['classes.php', 'modules.php', 'assignments.php', 'quizzes.php', 'attendance.php', 'grades.php'], true)
    && (isset($_GET['class']) || isset($_GET['course']) || isset($_GET['section']))) {
    $__gClass = (int) ($_GET['class'] ?? 0);
    $__gCourse = (int) ($_GET['course'] ?? 0);
    $__gSection = is_string($_GET['section'] ?? null) ? trim($_GET['section']) : '';
    $__gOk = true;
    if (isset($_GET['class']) && $__gClass < 1) $__gOk = false;
    if ($__gOk && $__gClass) {
        $__gOk = false;
        foreach ($mySubjects as $s) {
            if ((int) $s['assign_id'] === $__gClass
                && (!$__gCourse || (int) $s['course_id'] === $__gCourse)
                && ($__gSection === '' || $s['section'] === $__gSection)) { $__gOk = true; break; }
        }
    } elseif ($__gOk && ($__gCourse || $__gSection !== '')) {
        $__gOk = false;
        foreach ($mySubjects as $s) {
            if ((!$__gCourse || (int) $s['course_id'] === $__gCourse)
                && ($__gSection === '' || $s['section'] === $__gSection)) { $__gOk = true; break; }
        }
    }
    if (!$__gOk) {
        header('Location: classes.php', true, 302);
        exit;
    }
}

//  My Students
// A teacher's students = active students enrolled in one of the teacher's
// subjects AND in a section the teacher handles that subject for. Same rule
// as My Classes, Grades, Attendance and the dashboard, so every count matches.
// (Two teachers can share a subject in different sections.)
$myStudents = [];
if (!empty($mySubjects)) {
    $res = $conn->query(
        "SELECT DISTINCT u.id, u.school_id, u.first_name, u.last_name, u.section_dept,
                c.id AS course_id, c.course_name, c.course_code, ta.id AS assign_id
        FROM teacher_assignments ta
        JOIN enrollments e ON e.course_id = ta.course_id AND e.status = 'Enrolled'
        JOIN users u ON u.id = e.student_id AND u.section_dept = ta.section
        JOIN courses c ON c.id = e.course_id
        WHERE ta.teacher_id = $user_id AND u.role = 'Student' AND u.status = 'Active'
        ORDER BY u.section_dept, u.last_name, u.first_name, c.course_code"
    );
    while ($r = $res->fetch_assoc())
        $myStudents[] = $r;
}

// Build per-student map
$studentMap = [];
foreach ($myStudents as $row) {
    $sid = $row['id'];
    if (!isset($studentMap[$sid])) {
        $studentMap[$sid] = [
            'id' => $row['id'],
            'school_id' => $row['school_id'],
            'first_name' => $row['first_name'],
            'last_name' => $row['last_name'],
            'section_dept' => $row['section_dept'],
            'subjects' => [],
            'classes' => [],
        ];
    }
    $studentMap[$sid]['subjects'][] = $row['course_code'];
    $studentMap[$sid]['classes'][] = ['assign_id' => (int) $row['assign_id'], 'name' => $row['course_name']];
}

// Group students by section
$studentsBySection = [];
foreach ($studentMap as $st) {
    $sec = $st['section_dept'] ?: 'Unassigned';
    $studentsBySection[$sec][] = $st;
}
ksort($studentsBySection);

//  Stats 
$statSubjects = count(array_unique(array_column($mySubjects, 'course_id')));
$statSections = count($mySubjects);
$statStudents = count($studentMap);

$statPending = 0;
if (!empty($mySubjects)) {
    $courseIds = implode(',', array_unique(array_map('intval', array_column($mySubjects, 'course_id'))));
    $pRes = $conn->query(
        "SELECT COUNT(*) AS cnt FROM submissions s
        JOIN assignments a ON a.id = s.assignment_id
        JOIN users u ON u.id = s.student_id
        JOIN teacher_assignments ta ON ta.course_id = a.course_id AND ta.section = u.section_dept AND ta.teacher_id = $user_id
        WHERE s.score IS NULL AND s.attempt_count > 0"
    );
    if ($pRes)
        $statPending = (int) $pRes->fetch_assoc()['cnt'];
}

//  Module count for teacher 
$statModules = 0;
if (!empty($mySubjects)) {
    $courseIds = implode(',', array_unique(array_map('intval', array_column($mySubjects, 'course_id'))));
    $mRes = $conn->query("SELECT COUNT(*) AS cnt FROM modules WHERE course_id IN ($courseIds)");
    if ($mRes)
        $statModules = (int) $mRes->fetch_assoc()['cnt'];
}

//  Quiz count 
$statQuizzes = 0;
if (!empty($mySubjects)) {
    $courseIds = implode(',', array_unique(array_map('intval', array_column($mySubjects, 'course_id'))));
    $qRes = $conn->query("SELECT COUNT(*) AS cnt FROM quizzes q WHERE q.course_id IN ($courseIds) AND " . scopeTeacherOwned($conn, 'q', 'quizzes', $user_id));
    if ($qRes)
        $statQuizzes = (int) $qRes->fetch_assoc()['cnt'];
}

//  My Announcements 
// Feeds the bell and the Announcements page. Runs even with no assigned
// subjects so school posts still appear.
$myAnnouncements = [];
$courseIds = !empty($mySubjects)
    ? implode(',', array_unique(array_map('intval', array_column($mySubjects, 'course_id'))))
    : '';
// What a teacher should see: their own posts, plus posts from the school
// (Admin / Head Admin) meant for Everyone or Teachers. Other teachers' posts
// and student-only school posts are not for this teacher.
$courseFilter = "(a.author_id = $user_id OR (au.role IN ('Admin','Head Admin') AND a.target_audience IN ('Everyone','Teachers')))";
$aRes = $conn->query(
    "SELECT a.id, a.title, a.body, a.course_id, a.author_id, a.posted_at AS posted_at_raw,
            DATE_FORMAT(a.posted_at,'%b %d, %Y %h:%i %p') AS posted_at,
            CONCAT(u.first_name,' ',u.last_name) AS author,
            c.course_name, c.course_code
     FROM announcements a
     LEFT JOIN courses c ON c.id = a.course_id
     LEFT JOIN users u ON u.id = a.author_id
     LEFT JOIN users au ON au.id = a.author_id
     WHERE $courseFilter
     ORDER BY a.posted_at DESC
     LIMIT 20"
);
if ($aRes) {
    while ($r = $aRes->fetch_assoc())
        $myAnnouncements[] = $r;
}

//  Pending Submissions (ungraded) 
// Submitted work with no score yet from this teacher's own students
// (same rule as above) — surfaced in the notification bell.
$pendingSubmissions = [];
if (!empty($mySubjects)) {
    $courseIds = implode(',', array_unique(array_map('intval', array_column($mySubjects, 'course_id'))));
    $sRes = $conn->query(
        "SELECT s.id AS submission_id, s.submitted_at, a.id AS assignment_id, a.title, a.course_id,
                u.first_name, u.last_name, c.course_code
         FROM submissions s
         JOIN assignments a ON a.id = s.assignment_id
         JOIN users u ON u.id = s.student_id
         JOIN courses c ON c.id = a.course_id
         WHERE a.course_id IN ($courseIds) AND s.score IS NULL AND s.attempt_count > 0
           AND EXISTS (SELECT 1 FROM teacher_assignments ta WHERE ta.teacher_id = $user_id AND ta.course_id = a.course_id AND ta.section = u.section_dept)
         ORDER BY s.submitted_at DESC
         LIMIT 20"
    );
    if ($sRes) {
        while ($r = $sRes->fetch_assoc())
            $pendingSubmissions[] = $r;
    }
}

//  Group subjects by school year 
$subjectsByYear = [];
foreach ($mySubjects as $s) {
    $subjectsByYear[$s['school_year']][] = $s;
}

//  Design tokens 
$thumbGrads = [
    'linear-gradient(135deg,#003087,var(--primary-light))',
    'linear-gradient(135deg,#00875a,#00c97f)',
    'linear-gradient(135deg,#b38600,#ffd000)',
    'linear-gradient(135deg,#7b2d8b,#c850c0)',
    'linear-gradient(135deg,#c0392b,#e74c3c)',
    'linear-gradient(135deg,#0097a7,#26c6da)',
];

function sectionColor(string $sec): array
{
    $s = strtolower($sec);
    if (str_contains($s, '12'))
        return ['#7b2d8b', '#f3e8ff'];
    if (str_contains($s, '11'))
        return ['#0097a7', '#e0f7fa'];
    if (str_contains($s, '10'))
        return ['#003087', '#e8f0ff'];
    if (str_contains($s, '9'))
        return ['#00875a', '#e6fff4'];
    if (str_contains($s, '8'))
        return ['#b38600', '#fffbe6'];
    if (str_contains($s, '7'))
        return ['#c0392b', '#fff0ec'];
    return ['#555555', '#f5f5f5'];
}
