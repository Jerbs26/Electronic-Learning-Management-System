<?php
// ============================================================
//  Arandia College eLMS — Automatic subject enrollment
//  File: includes/auto_enroll.php
//
//  A student takes the subjects of their section / grade level (the
//  subjects listed for that section on the Subjects page — table
//  teacher_assignments). So the admin no longer enrolls students one
//  subject at a time:
//
//    • New student account with a section → enrolled in all of that
//      section's subjects (api/accounts.php).
//    • Student moved to another section → enrolled in the new section's
//      subjects; subjects the new section does not take are marked
//      Dropped (kept for the record, never deleted) (api/accounts.php).
//    • Subject added to a section → every active student of that section
//      is enrolled in it (api/sections.php add_subject).
//    • Subject removed from a section → that section's students are
//      marked Dropped in it (api/sections.php remove_subject).
//
//  The admin can still untick a subject for one student on the Subjects
//  page; that choice is kept (nothing here re-adds it unless the student
//  changes section or the subject is added to the section again).
//  Every insert/update fires the existing enrollment triggers, so open
//  pages refresh by themselves (live updates).
// ============================================================

if (!function_exists('aeSectionCourses')) {

    /** Subject ids taught to $section (teacher_assignments). */
    function aeSectionCourses(mysqli $conn, string $section): array
    {
        if ($section === '') return [];
        $st = $conn->prepare("SELECT DISTINCT course_id FROM teacher_assignments WHERE section = ?");
        $st->bind_param('s', $section);
        $st->execute();
        $ids = array_map('intval', array_column($st->get_result()->fetch_all(MYSQLI_ASSOC), 'course_id'));
        $st->close();
        return $ids;
    }

    /**
     * Enroll one student in their section's subjects. Call after the account is
     * created, or after its section changed ($sectionChanged = true also drops
     * subjects the new section does not take). Returns [added, dropped].
     * Never throws: the account save itself must not fail because of this.
     */
    function aeSyncStudent(mysqli $conn, int $studentId, bool $sectionChanged = false): array
    {
        $added = $dropped = 0;
        try {
            $st = $conn->prepare("SELECT role, status, section_dept FROM users WHERE id = ? LIMIT 1");
            $st->bind_param('i', $studentId);
            $st->execute();
            $u = $st->get_result()->fetch_assoc();
            $st->close();
            if (!$u || $u['role'] !== 'Student') return [0, 0];

            $courses = aeSectionCourses($conn, (string) ($u['section_dept'] ?? ''));
            $conn->begin_transaction();
            if ($courses) {
                $ins = $conn->prepare("INSERT INTO enrollments (student_id, course_id, status) VALUES (?, ?, 'Enrolled')
                                       ON DUPLICATE KEY UPDATE status = IF(status = 'Dropped', 'Enrolled', status)");
                foreach ($courses as $cid) {
                    $ins->bind_param('ii', $studentId, $cid);
                    $ins->execute();
                    if ($ins->affected_rows > 0) $added++;   // 1 = new row, 2 = re-enrolled
                }
                $ins->close();
            }
            if ($sectionChanged) {
                $in = $courses ? implode(',', $courses) : '0';
                $st = $conn->prepare("UPDATE enrollments SET status = 'Dropped'
                                      WHERE student_id = ? AND status = 'Enrolled' AND course_id NOT IN ($in)");
                $st->bind_param('i', $studentId);
                $st->execute();
                $dropped = $st->affected_rows;
                $st->close();
            }
            $conn->commit();
        } catch (Throwable $e) {
            try { $conn->rollback(); } catch (Throwable $e2) { }
            error_log('[eLMS auto-enroll] student ' . $studentId . ': ' . $e->getMessage());
            return [0, 0];
        }
        return [$added, $dropped];
    }

    /**
     * One time only (the first page load after this update): enroll every active
     * student in all the subjects of their section that they are missing, so
     * students added before automatic enrollment get their subjects too.
     * Remembered in table app_settings (created here) so it never runs twice.
     */
    function aeBackfillOnce(mysqli $conn): void
    {
        static $done = false;
        if ($done) return;
        $done = true;
        try {
            $conn->query("CREATE TABLE IF NOT EXISTS app_settings (
                            name VARCHAR(64) NOT NULL PRIMARY KEY,
                            value VARCHAR(255) NULL,
                            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                          ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
            // Claim the run first (INSERT IGNORE): two requests at once cannot both run it
            $conn->query("INSERT IGNORE INTO app_settings (name, value) VALUES ('auto_enroll_backfill', 'running')");
            if ($conn->affected_rows < 1) return;
            $conn->query(
                "INSERT INTO enrollments (student_id, course_id, status)
                 SELECT DISTINCT u.id, ta.course_id, 'Enrolled'
                 FROM users u JOIN teacher_assignments ta ON ta.section = u.section_dept
                 WHERE u.role = 'Student' AND u.status = 'Active'
                 ON DUPLICATE KEY UPDATE status = enrollments.status"
            );
            $n = max(0, $conn->affected_rows);
            $conn->query("UPDATE app_settings SET value = 'done: $n added' WHERE name = 'auto_enroll_backfill'");
        } catch (Throwable $e) {
            error_log('[eLMS auto-enroll] backfill: ' . $e->getMessage());
        }
    }

    /** A subject was added to $section: enroll its active students. Returns how many were enrolled. */
    function aeSubjectAdded(mysqli $conn, string $section, int $courseId): int
    {
        try {
            $st = $conn->prepare(
                "INSERT INTO enrollments (student_id, course_id, status)
                 SELECT u.id, ?, 'Enrolled' FROM users u
                 WHERE u.section_dept = ? AND u.role = 'Student' AND u.status = 'Active'
                 ON DUPLICATE KEY UPDATE status = IF(enrollments.status = 'Dropped', 'Enrolled', enrollments.status)"
            );
            $st->bind_param('is', $courseId, $section);
            $st->execute();
            $n = $st->affected_rows;
            $st->close();
            return max(0, $n);
        } catch (Throwable $e) {
            error_log('[eLMS auto-enroll] add ' . $section . '/' . $courseId . ': ' . $e->getMessage());
            return 0;
        }
    }

    /** A subject was removed from $section: its students are marked Dropped (unless the section still has it). */
    function aeSubjectRemoved(mysqli $conn, string $section, int $courseId): int
    {
        try {
            if (in_array($courseId, aeSectionCourses($conn, $section), true)) return 0;   // still taught (another term / teacher)
            $st = $conn->prepare(
                "UPDATE enrollments e JOIN users u ON u.id = e.student_id
                 SET e.status = 'Dropped'
                 WHERE e.course_id = ? AND e.status = 'Enrolled' AND u.section_dept = ? AND u.role = 'Student'"
            );
            $st->bind_param('is', $courseId, $section);
            $st->execute();
            $n = $st->affected_rows;
            $st->close();
            return max(0, $n);
        } catch (Throwable $e) {
            error_log('[eLMS auto-enroll] remove ' . $section . '/' . $courseId . ': ' . $e->getMessage());
            return 0;
        }
    }
}
