<?php
// ============================================================
//  Arandia College eLMS — Student data (one source for every student page)
//  File: includes/student_data.php
//
//  Used by student/_init.php, includes/progress.php (dashboard live
//  refresh) and api/sync.php, so a student's subjects, teachers,
//  assignments, quizzes and grades are computed the same way everywhere,
//  and match what the teacher sees (includes/scope.php rules,
//  includes/gradebook.php totals).
// ============================================================
require_once __DIR__ . '/scope.php';
require_once __DIR__ . '/gradebook.php';

if (!function_exists('studentCourses')) {

    /** Enrolled subjects, each with the teacher who handles it for the student's section. */
    function studentCourses(mysqli $conn, int $uid): array
    {
        $rows = [];
        $res = $conn->query(
            "SELECT c.id, c.course_code, c.course_name, c.description, c.school_year, c.semester,
                    e.status AS enroll_status, ta.id AS class_id,
                    IF(tu.id IS NULL, NULL, CONCAT(tu.last_name, ', ', tu.first_name)) AS teacher_name,
                    tu.id AS teacher_id
             FROM enrollments e
             JOIN courses c ON c.id = e.course_id
             " . scopeStudentTeacherJoin('c.id', $uid) . "
             WHERE e.student_id = $uid AND e.status = 'Enrolled'
             GROUP BY c.id
             ORDER BY c.course_code"
        );
        while ($res && $r = $res->fetch_assoc()) $rows[] = $r;
        return $rows;
    }

    /**
     * The student's own gradebook per subject — the same cells and totals the
     * teacher sees for this student on teacher/grades.php.
     * @return array<int, array{code:string,name:string,teacher:?string,items:array,cells:array,total:array}>
     */
    function studentGradebook(mysqli $conn, int $uid, array $courses): array
    {
        $out = [];
        $scope = scopeStudentOwned($conn, 'gi', 'gradebook_items', $uid);
        foreach ($courses as $c) {
            $cid = (int) $c['id'];
            $gb = gradebookBuild($conn, $cid, [$uid], $scope);
            $out[$cid] = [
                'code' => $c['course_code'], 'name' => $c['course_name'], 'teacher' => $c['teacher_name'] ?? null,
                'items' => $gb['items'], 'cells' => $gb['cells'][$uid] ?? [], 'total' => $gb['totals'][$uid] ?? null,
            ];
        }
        return $out;
    }

    /** Average of the subject totals that have a percent (null when nothing is graded yet). */
    function studentGeneralAverage(array $gradebook): ?float
    {
        $p = [];
        foreach ($gradebook as $g) if ($g['total'] && $g['total']['percent'] !== null) $p[] = (float) $g['total']['percent'];
        return $p ? round(array_sum($p) / count($p), 1) : null;
    }
}
