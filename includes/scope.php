<?php
// ============================================================
//  Arandia College eLMS — Who can see what (shared rules)
//  File: includes/scope.php
//
//  One place for the visibility rules every role uses, so Admin,
//  Teacher and Student pages, APIs and the live-update feed agree:
//
//  CLASS        = a subject taught to one section by one teacher
//                 (teacher_assignments: course_id + section + teacher_id).
//  MY STUDENTS  = (teacher) active students enrolled in the subject AND in a
//                 section the teacher handles it for.
//  OWNED ITEMS  = assignments, quizzes and gradebook items carry teacher_id
//                 (database/migration_integration.sql). A student sees an
//                 item when it has no owner (old/shared) or its owner teaches
//                 the subject to the student's section. A teacher sees and
//                 edits their own items, plus unowned ones in their subjects.
//  ANNOUNCEMENTS: school posts (author Admin/Head Admin) follow
//                 target_audience; teacher posts reach the students of the
//                 author's sections in that subject.
//
//  All helpers return SQL fragments built only from integers and column
//  names, so they are safe to embed in queries.
// ============================================================

if (!function_exists('scopeHasOwner')) {

    /** True once migration_integration.sql added the owner column. */
    function scopeHasOwner(mysqli $conn, string $table): bool
    {
        static $cache = [];
        if (!isset($cache[$table])) {
            $t = $conn->real_escape_string($table);
            $r = $conn->query("SHOW COLUMNS FROM `$t` LIKE 'teacher_id'");
            $cache[$table] = $r && $r->num_rows > 0;
        }
        return $cache[$table];
    }

    /**
     * Student side: rows of $alias (assignments / quizzes / gradebook_items,
     * with course_id + teacher_id) that student $sid may see.
     */
    function scopeStudentOwned(mysqli $conn, string $alias, string $table, int $sid): string
    {
        if (!scopeHasOwner($conn, $table)) return '1=1';
        return "($alias.teacher_id IS NULL OR EXISTS (
                    SELECT 1 FROM teacher_assignments ta_v JOIN users su_v ON su_v.id = $sid
                    WHERE ta_v.teacher_id = $alias.teacher_id AND ta_v.course_id = $alias.course_id
                      AND ta_v.section = su_v.section_dept))";
    }

    /** Teacher side: rows of $alias that teacher $tid sees (own + unowned). */
    function scopeTeacherOwned(mysqli $conn, string $alias, string $table, int $tid): string
    {
        if (!scopeHasOwner($conn, $table)) return '1=1';
        return "($alias.teacher_id IS NULL OR $alias.teacher_id = $tid)";
    }

    /**
     * Student side: announcements (alias $a, author joined as $u) that
     * student $sid may read.
     */
    function scopeStudentAnnouncements(string $a, string $u, int $sid): string
    {
        return "($a.target_audience IN ('Everyone','Students') AND (
                    ($a.course_id IS NULL AND $u.role IN ('Admin','Head Admin'))
                 OR ($a.course_id IS NOT NULL
                     AND EXISTS (SELECT 1 FROM enrollments e_v WHERE e_v.student_id = $sid AND e_v.course_id = $a.course_id AND e_v.status = 'Enrolled')
                     AND ($u.role <> 'Teacher' OR EXISTS (
                          SELECT 1 FROM teacher_assignments ta_v JOIN users su_v ON su_v.id = $sid
                          WHERE ta_v.teacher_id = $a.author_id AND ta_v.course_id = $a.course_id AND ta_v.section = su_v.section_dept)))))";
    }

    /** Teacher side: announcements a teacher reads (own + school posts for teachers). */
    function scopeTeacherAnnouncements(string $a, string $u, int $tid): string
    {
        return "($a.author_id = $tid OR ($u.role IN ('Admin','Head Admin') AND $a.target_audience IN ('Everyone','Teachers')))";
    }

    /** The student's teacher for a subject = whoever handles it for the student's section. */
    function scopeStudentTeacherJoin(string $courseCol, int $sid, string $taAlias = 'ta', string $uAlias = 'tu'): string
    {
        return "LEFT JOIN teacher_assignments $taAlias ON $taAlias.course_id = $courseCol
                    AND $taAlias.section = (SELECT section_dept FROM users WHERE id = $sid)
                LEFT JOIN users $uAlias ON $uAlias.id = $taAlias.teacher_id AND $uAlias.role = 'Teacher'";
    }

    /** May teacher $tid change this owned row? (own, or unowned in a subject they teach) */
    function scopeTeacherCanEdit(?int $ownerId, int $tid): bool
    {
        return $ownerId === null || $ownerId === $tid;
    }
}
