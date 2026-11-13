<?php
// ============================================================
//  Arandia College eLMS — Student pages: shared setup
//  File: student/_init.php
//
//  Replaces the ~200-line block that was copied (with small differences)
//  into every student page. Each copy disagreed a little — some showed
//  dropped subjects, one listed draft quizzes, some read the old `grades`
//  table — so the same student saw different numbers on different pages.
//  Everything below follows includes/scope.php, so students see exactly
//  what their own section's teacher published:
//    $mySubjects        enrolled subjects + the teacher for MY section
//    $myModules         published modules, numbered
//    $myQuizzes         published quizzes from my section's teacher
//    $myAssignments     assignments from my section's teacher + my submission
//    $announcements     school posts for students + my teachers' posts
//    $myProgress / $myOpened, stats, and the *ByCourse groupings
//  Pages then set $currentPage / $pageTitle and include the header.
// ============================================================
session_start();
require_once __DIR__ . '/../config/conn.php';
require_once __DIR__ . '/../includes/role_guard.php';
require_once __DIR__ . '/../includes/student_data.php';
requireStudentPage();   // login / role / still-active check
// Students get their section's subjects automatically (includes/auto_enroll.php);
// the first load after the update also enrolls students added before that.
require_once __DIR__ . '/../includes/auto_enroll.php';
aeBackfillOnce($conn);
// Online quizzes cannot be resumed: one left open (window closed, connection lost)
// is submitted now with the answers saved so far (includes/quiz_attempts.php).
// An open quiz pings the server every 20 s, so a quiz being taken is never touched.
require_once __DIR__ . '/../includes/quiz_attempts.php';
qaCloseAbandoned($conn, (int) $_SESSION['user_id']);

$first_name = $_SESSION['first_name'];
$last_name = $_SESSION['last_name'];
$user_id = (int) $_SESSION['user_id'];

$uInfo = $conn->query("SELECT section_dept FROM users WHERE id = $user_id LIMIT 1")->fetch_assoc();
$section = $uInfo['section_dept'] ?? '';

$level = 'SHS / HS';
if (str_contains($section, 'Grade 11') || str_contains($section, 'Grade 12'))
    $level = 'Senior High School';
elseif (preg_match('/Grade (7|8|9|10)\b/', $section))
    $level = 'High School';

// ── Enrolled subjects (with MY section's teacher) ────────────
$mySubjects = studentCourses($conn, $user_id);
$cids = $mySubjects ? implode(',', array_map('intval', array_column($mySubjects, 'id'))) : '0';

// ── Modules ──────────────────────────────────────────────────
$hasModNum = (bool) $conn->query("SHOW COLUMNS FROM modules LIKE 'module_number'")->num_rows;
$modNumCol = $hasModNum ? 'm.module_number' : 'NULL';
$modOrder = $hasModNum ? 'm.module_number' : 'm.week_number, m.created_at';
$myModules = [];
$res = $conn->query(
    "SELECT m.id, m.course_id, m.title, m.description, m.file_path, m.week_number, $modNumCol AS module_number,
            DATE_FORMAT(m.created_at,'%b %d, %Y') AS posted_at, c.course_name, c.course_code
     FROM modules m JOIN courses c ON c.id = m.course_id
     WHERE m.course_id IN ($cids) AND m.published = 1
     ORDER BY c.course_name ASC, $modOrder, m.id ASC"
);
while ($res && $r = $res->fetch_assoc()) $myModules[] = $r;

// ── Which module each quiz / assignment belongs to (includes/module_links.php) ──
require_once __DIR__ . '/../includes/module_links.php';
$mlCol = fn(string $alias) => mlEnsure($conn) ? "$alias.module_id" : 'NULL AS module_id';

// ── Quizzes (published, from my section's teacher) ───────────
$myQuizzes = [];
$res = $conn->query(
    "SELECT q.id, q.course_id, q.title, q.description, q.time_limit, q.max_score, q.passing_score, q.created_at AS posted_on,
            q.open_at, q.close_at, q.quiz_type, q.file_path, c.course_name, c.course_code, " . $mlCol('q') . ",
            (SELECT COUNT(*) FROM quiz_questions WHERE quiz_id = q.id) AS question_count,
            (SELECT status FROM quiz_attempts WHERE quiz_id = q.id AND student_id = $user_id
             ORDER BY FIELD(status,'Graded','Submitted','In Progress') ASC, started_at DESC LIMIT 1) AS attempt_status,
            (SELECT score FROM quiz_attempts WHERE quiz_id = q.id AND student_id = $user_id
             ORDER BY FIELD(status,'Graded','Submitted','In Progress') ASC, started_at DESC LIMIT 1) AS my_score
     FROM quizzes q JOIN courses c ON c.id = q.course_id
     WHERE q.course_id IN ($cids) AND q.status = 'Published' AND " . scopeStudentOwned($conn, 'q', 'quizzes', $user_id) . "
     ORDER BY q.created_at DESC"
);
while ($res && $r = $res->fetch_assoc()) $myQuizzes[] = $r;

// ── Assignments (from my section's teacher) + my submission ──
$myAssignments = [];
$res = $conn->query(
    "SELECT a.id, a.course_id, a.title, a.instructions, a.description, a.due_date, a.max_score, a.created_at AS posted_on,
            a.max_attempts, a.file_path, c.course_code, c.course_name, " . $mlCol('a') . ",
            s.id AS submission_id, s.file_path AS submission_file, s.status AS submission_status,
            s.score AS submission_score, s.remarks AS submission_remarks,
            DATE_FORMAT(s.submitted_at,'%b %d, %Y %h:%i %p') AS submitted_at,
            COALESCE(s.attempt_count, 0) AS attempt_count
     FROM assignments a
     JOIN courses c ON c.id = a.course_id
     LEFT JOIN submissions s ON s.assignment_id = a.id AND s.student_id = $user_id
     WHERE a.course_id IN ($cids) AND " . scopeStudentOwned($conn, 'a', 'assignments', $user_id) . "
     ORDER BY (a.due_date IS NULL), a.due_date ASC, a.created_at DESC"
);
while ($res && $r = $res->fetch_assoc()) $myAssignments[] = $r;

// ── One subject open? (?course=ID on the subject pages) ──────
// The subject sidebar (includes/student_subject_nav.php) opens next to the
// main one, and the page shows only that subject's work. Only the student's
// own enrolled subjects match; anything else shows all subjects as before.
require_once __DIR__ . '/../includes/student_subject_nav.php';
$allSubjects = $mySubjects;
$subjectCounts = studentSubjectCounts($myAssignments, $myQuizzes);
$activeSubject = null;
if (isset(studentSubjectPages()[basename((string) ($_SERVER['SCRIPT_NAME'] ?? ''))])) {
    $activeSubject = studentSubjectResolve($mySubjects);
}
// ── URL guard: a hand-edited ?course= must never work ────────
//  · subject page + ?course that is not one of MY enrolled subjects
//    (wrong id, text, array, empty) → back to My Subjects
//  · any other page (Awards, Dashboard, Announcements, Profile, My Subjects)
//    + a stray ?course → same page with ?course removed (other filters such
//    as ?subject= / ?year= are kept)
//  Only plain page loads (GET) are redirected, so forms and API calls are
//  never interrupted, and neither target carries ?course, so it can't loop.
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'GET' && array_key_exists('course', $_GET)) {
    $__guardPage = basename((string) ($_SERVER['SCRIPT_NAME'] ?? ''));
    if (isset(studentSubjectPages()[$__guardPage])) {
        if (!$activeSubject) {
            header('Location: my-subjects.php', true, 302);
            exit;
        }
    } elseif ($__guardPage !== '') {
        $__rest = $_GET;
        unset($__rest['course']);
        header('Location: ' . $__guardPage . ($__rest ? '?' . http_build_query($__rest) : ''), true, 302);
        exit;
    }
}
if ($activeSubject) {
    $activeCid = (int) $activeSubject['id'];
    $mySubjects = [$activeSubject];
    $myModules = array_values(array_filter($myModules, fn($m) => (int) $m['course_id'] === $activeCid));
    $myQuizzes = array_values(array_filter($myQuizzes, fn($q) => (int) $q['course_id'] === $activeCid));
    $myAssignments = array_values(array_filter($myAssignments, fn($a) => (int) $a['course_id'] === $activeCid));
}

// ── Announcements (school posts for students + my teachers' posts) ──
$announcements = [];
$res = $conn->query(
    "SELECT a.id, a.title, a.body, a.course_id, a.posted_at AS posted_at_raw,
            DATE_FORMAT(a.posted_at,'%b %d, %Y %h:%i %p') AS posted_at,
            CONCAT(u.first_name,' ',u.last_name) AS author, u.role AS author_role,
            c.course_code, c.course_name
     FROM announcements a
     JOIN users u ON u.id = a.author_id
     LEFT JOIN courses c ON c.id = a.course_id
     WHERE " . scopeStudentAnnouncements('a', 'u', $user_id) . "
     ORDER BY a.posted_at DESC LIMIT 30"
);
while ($res && $r = $res->fetch_assoc()) $announcements[] = $r;

// ── Module progress ──────────────────────────────────────────
$myProgress = $myOpened = [];
if ($myModules) {
    $res = $conn->query("SELECT module_id, DATE_FORMAT(completed_at,'%b %d, %Y') AS completed_at FROM module_progress WHERE student_id = $user_id");
    while ($res && $r = $res->fetch_assoc()) $myProgress[$r['module_id']] = $r['completed_at'];
    if ($conn->query("SHOW TABLES LIKE 'module_views'")->num_rows) {
        $res = $conn->query("SELECT module_id FROM module_views WHERE student_id = $user_id");
        while ($res && $r = $res->fetch_assoc()) $myOpened[(int) $r['module_id']] = true;
    }
}

// ── Stats ────────────────────────────────────────────────────
$statSubjects = count($mySubjects);
$statModules = count($myModules);
$statModulesDone = count(array_filter(array_column($myModules, 'id'), fn($id) => isset($myProgress[$id])));
$statQuizDue = count(array_filter($myQuizzes, fn($q) =>
    !in_array($q['attempt_status'], ['Submitted', 'Graded'], true) && (!$q['close_at'] || strtotime($q['close_at']) > time())));
$statDone = count(array_filter($myQuizzes, fn($q) => in_array($q['attempt_status'], ['Submitted', 'Graded'], true)));

// ── Groupings ────────────────────────────────────────────────
$modulesByCourse = $quizzesByCourse = $assignmentsByCourse = [];
foreach ($myModules as $m) $modulesByCourse[$m['course_code']][] = $m;
foreach ($myQuizzes as $q) $quizzesByCourse[$q['course_code']][] = $q;
foreach ($myAssignments as $a) $assignmentsByCourse[$a['course_code']][] = $a;

$thumbGrads = [
    'linear-gradient(135deg,#003087,#0056d6)',
    'linear-gradient(135deg,#00875a,#00c97f)',
    'linear-gradient(135deg,#b38600,#ffd000)',
    'linear-gradient(135deg,#7b2d8b,#c850c0)',
    'linear-gradient(135deg,#c0392b,#e74c3c)',
    'linear-gradient(135deg,#0097a7,#26c6da)',
];
