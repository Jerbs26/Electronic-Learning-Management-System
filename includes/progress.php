<?php
// ============================================================
//  Arandia College eLMS — Shared "Learning Progress" calculator
//  File: includes/progress.php
//
//  Used by BOTH student/dashboard.php (first render) and
//  api/dashboard_progress.php (AJAX refresh for the polling /
//  real-time update on the dashboard), so the two can never
//  drift out of sync — fix the numbers here once.
// ============================================================
require_once __DIR__ . '/student_data.php';

/**
 * @param mysqli $conn
 * @param int    $user_id Logged-in student's id.
 * @return array{
 *   overall_progress:int,
 *   modules_done:int, modules_total:int,
 *   assignments_submitted:int, assignments_total:int,
 *   quizzes_done:int, quizzes_total:int,
 *   subject_grades: array<int, array{course_name:string, course_code:string, percent:int}>
 * }
 */
function getStudentLearningProgress(mysqli $conn, int $user_id): array
{
    $empty = [
        'overall_progress' => 0,
        'modules_done' => 0, 'modules_total' => 0,
        'assignments_submitted' => 0, 'assignments_total' => 0,
        'quizzes_done' => 0, 'quizzes_total' => 0,
        'subject_grades' => [],
    ];

    // Enrolled course ids
    $courseIds = [];
    $res = $conn->query("SELECT course_id FROM enrollments WHERE student_id = $user_id AND status = 'Enrolled'");
    if ($res) {
        while ($r = $res->fetch_assoc()) $courseIds[] = (int) $r['course_id'];
    }
    if (empty($courseIds)) return $empty;
    $cids = implode(',', $courseIds);

    // ── Modules ──────────────────────────────────────────────
    $modulesTotal = 0;
    $res = $conn->query("SELECT COUNT(*) AS c FROM modules WHERE course_id IN ($cids) AND published = 1");
    if ($res) $modulesTotal = (int) ($res->fetch_assoc()['c'] ?? 0);

    $modulesDone = 0;
    $res = $conn->query(
        "SELECT COUNT(*) AS c FROM module_progress mp
         JOIN modules m ON m.id = mp.module_id
         WHERE mp.student_id = $user_id AND m.course_id IN ($cids)"
    );
    if ($res) $modulesDone = (int) ($res->fetch_assoc()['c'] ?? 0);

    // ── Assignments (submitted = a row exists, graded or not) ──
    $assignmentsTotal = 0;
    // Only work from the student's own section teacher (includes/scope.php)
    $res = $conn->query("SELECT COUNT(*) AS c FROM assignments a WHERE a.course_id IN ($cids) AND " . scopeStudentOwned($conn, 'a', 'assignments', $user_id));
    if ($res) $assignmentsTotal = (int) ($res->fetch_assoc()['c'] ?? 0);

    $assignmentsSubmitted = 0;
    $res = $conn->query(
        "SELECT COUNT(DISTINCT s.assignment_id) AS c
         FROM submissions s JOIN assignments a ON a.id = s.assignment_id
         WHERE s.student_id = $user_id AND a.course_id IN ($cids) AND s.attempt_count > 0
           AND " . scopeStudentOwned($conn, 'a', 'assignments', $user_id)
    );
    if ($res) $assignmentsSubmitted = (int) ($res->fetch_assoc()['c'] ?? 0);

    // ── Quizzes (published only — matches what the student can see) ──
    $quizzesTotal = 0;
    $res = $conn->query("SELECT COUNT(*) AS c FROM quizzes q WHERE q.course_id IN ($cids) AND q.status = 'Published' AND " . scopeStudentOwned($conn, 'q', 'quizzes', $user_id));
    if ($res) $quizzesTotal = (int) ($res->fetch_assoc()['c'] ?? 0);

    $quizzesDone = 0;
    $res = $conn->query(
        "SELECT COUNT(DISTINCT qa.quiz_id) AS c
         FROM quiz_attempts qa JOIN quizzes q ON q.id = qa.quiz_id
         WHERE qa.student_id = $user_id AND q.course_id IN ($cids)
           AND qa.status IN ('Submitted','Graded') AND q.status = 'Published'
           AND " . scopeStudentOwned($conn, 'q', 'quizzes', $user_id) . ""
    );
    if ($res) $quizzesDone = (int) ($res->fetch_assoc()['c'] ?? 0);

    // Overall = average of whichever ratios actually have something to
    // complete (skip a 0-total component instead of counting it as 0%).
    $parts = [];
    if ($modulesTotal > 0) $parts[] = $modulesDone / $modulesTotal * 100;
    if ($assignmentsTotal > 0) $parts[] = $assignmentsSubmitted / $assignmentsTotal * 100;
    if ($quizzesTotal > 0) $parts[] = $quizzesDone / $quizzesTotal * 100;
    $overall = !empty($parts) ? (int) round(array_sum($parts) / count($parts)) : 0;

    // ── Recent Grades — one total % per subject, from the gradebook
    //    (same numbers as the student Grades page and the teacher Gradebook)
    $subjectGrades = [];
    foreach (studentGradebook($conn, $user_id, studentCourses($conn, $user_id)) as $g) {
        if ($g['total'] && $g['total']['percent'] !== null) {
            $subjectGrades[] = ['course_name' => $g['name'], 'course_code' => $g['code'], 'percent' => (int) round($g['total']['percent'])];
        }
    }

    return [
        'overall_progress' => $overall,
        'modules_done' => $modulesDone, 'modules_total' => $modulesTotal,
        'assignments_submitted' => $assignmentsSubmitted, 'assignments_total' => $assignmentsTotal,
        'quizzes_done' => $quizzesDone, 'quizzes_total' => $quizzesTotal,
        'subject_grades' => $subjectGrades,
    ];
}
