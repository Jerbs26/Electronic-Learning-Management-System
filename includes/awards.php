<?php
// ============================================================
//  Arandia College eLMS — Awards & Recognition helpers
//  File: includes/awards.php
//
//  Shared by student/awards.php, student/dashboard.php,
//  teacher/awards.php, admin/awards.php and api/awards.php.
//  Include AFTER config/conn.php and session_start().
//
//  Every query here uses prepared statements and is wrapped so a
//  missing table (migration not run yet) or a DB hiccup degrades to
//  an empty result instead of a fatal error on the page.
// ============================================================

if (!function_exists('awards_icon_paths')) {

    /** Original line icons for award emblems (same stroke style as the sidebar icons). */
    function awards_icon_paths(): array
    {
        return [
            'medal'   => '<circle cx="12" cy="9" r="6"/><path d="M8.5 13.9 7 22l5-3 5 3-1.5-8.1"/><path d="M10 9l1.5 1.5L14.5 7.5"/>',
            'trophy'  => '<path d="M8 21h8"/><path d="M12 17v4"/><path d="M7 4h10v5a5 5 0 0 1-10 0V4z"/><path d="M17 5h3v2a3 3 0 0 1-3 3"/><path d="M7 5H4v2a3 3 0 0 0 3 3"/>',
            'star'    => '<polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>',
            'book'    => '<path d="M2 4h6a4 4 0 0 1 4 4v13a3 3 0 0 0-3-3H2z"/><path d="M22 4h-6a4 4 0 0 0-4 4v13a3 3 0 0 1 3-3h7z"/>',
            'code'    => '<polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/><line x1="14" y1="4" x2="10" y2="20"/>',
            'sigma'   => '<path d="M18 5V4H6l6 8-6 8h12v-1"/>',
            'flask'   => '<path d="M9 3h6"/><path d="M10 3v6L4.6 18.4A1.8 1.8 0 0 0 6.2 21h11.6a1.8 1.8 0 0 0 1.6-2.6L14 9V3"/><path d="M7.5 15h9"/>',
            'growth'  => '<polyline points="22 7 13.5 15.5 8.5 10.5 2 17"/><polyline points="16 7 22 7 22 13"/>',
            'target'  => '<circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="5"/><circle cx="12" cy="12" r="1"/>',
            'message' => '<path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/><line x1="8" y1="9" x2="16" y2="9"/><line x1="8" y1="13" x2="13" y2="13"/>',
            'flag'    => '<path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"/><line x1="4" y1="22" x2="4" y2="15"/>',
            'check'   => '<path d="M22 11.1V12a10 10 0 1 1-5.9-9.1"/><polyline points="22 4 12 14 9 11"/>',
            'globe'   => '<circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>',
            'pen'     => '<path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4z"/>',
        ];
    }

    /** Human labels for the icon picker in Admin. */
    function awards_icon_labels(): array
    {
        return [
            'medal' => 'Medal', 'trophy' => 'Trophy', 'star' => 'Star', 'book' => 'Open book',
            'code' => 'Code', 'sigma' => 'Mathematics', 'flask' => 'Science', 'growth' => 'Growth',
            'target' => 'Target', 'message' => 'Participation', 'flag' => 'Leadership',
            'check' => 'Completion', 'globe' => 'Social studies', 'pen' => 'Writing',
        ];
    }

    /** Emblem colour tones: key => [label, strong colour, tint]. Navy / maroon / gold follow the school palette. */
    function awards_tones(): array
    {
        return [
            'navy'   => ['Navy',   '#003087', '#e8eefb'],
            'maroon' => ['Maroon', '#7a1f35', '#f8e9ed'],
            'gold'   => ['Gold',   '#8a6700', '#fbf3dc'],
            'green'  => ['Green',  '#0b7a52', '#e3f5ec'],
            'teal'   => ['Teal',   '#0f6e75', '#e1f3f4'],
            'slate'  => ['Slate',  '#475569', '#eef1f5'],
            'purple' => ['Purple', '#5b2a86', '#efe8f7'],
        ];
    }

    function awards_categories(): array
    {
        return ['Academic Excellence', 'Subject Mastery', 'Improvement', 'Consistency', 'Participation', 'Leadership', 'Other'];
    }

    function awards_icon_svg(string $key, string $class = 'aw-ico'): string
    {
        $p = awards_icon_paths();
        $path = $p[$key] ?? $p['medal'];
        return '<svg class="' . htmlspecialchars($class) . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' . $path . '</svg>';
    }

    /** Inline CSS custom properties for one award's tone. */
    function awards_tone_style(?string $tone): string
    {
        $t = awards_tones();
        $row = $t[$tone ?? ''] ?? $t['navy'];
        return '--aw-fg:' . $row[1] . ';--aw-bg:' . $row[2] . ';';
    }

    /** The ribbon-tab emblem that identifies an award. $size: 'sm' | 'md' | 'lg'. */
    function awards_emblem(?string $icon, ?string $tone, string $size = 'md'): string
    {
        return '<span class="aw-emblem aw-emblem-' . htmlspecialchars($size) . '" style="' . awards_tone_style($tone) . '" aria-hidden="true">'
            . awards_icon_svg($icon ?? 'medal') . '</span>';
    }

    function awards_format_date(?string $dt, string $fmt = 'F j, Y'): string
    {
        if (!$dt) return '—';
        $ts = strtotime($dt);
        return $ts ? date($fmt, $ts) : '—';
    }

    /** True once migration_awards.sql has been imported. Never throws. */
    function awards_tables_ready(mysqli $conn): bool
    {
        static $ready = null;
        if ($ready !== null) return $ready;
        try {
            $res = $conn->query("SELECT COUNT(*) AS n FROM information_schema.tables
                                 WHERE table_schema = DATABASE() AND table_name IN ('award_types','student_awards')");
            $ready = $res ? ((int) ($res->fetch_assoc()['n'] ?? 0) === 2) : false;
        } catch (Throwable $e) {
            $ready = false;
        }
        return $ready;
    }

    /** Per-session CSRF token for the awards API. */
    function awards_csrf_token(): string
    {
        if (empty($_SESSION['awards_csrf']) || !is_string($_SESSION['awards_csrf'])) {
            $_SESSION['awards_csrf'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['awards_csrf'];
    }

    /**
     * Run a prepared SELECT and return all rows. Returns [] on any failure
     * (missing table, bad connection) so pages never crash on it.
     */
    function awards_select(mysqli $conn, string $sql, string $types = '', array $params = []): array
    {
        try {
            $stmt = $conn->prepare($sql);
            if (!$stmt) return [];
            if ($types !== '') $stmt->bind_param($types, ...$params);
            if (!$stmt->execute()) { $stmt->close(); return []; }
            $res = $stmt->get_result();
            $rows = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
            $stmt->close();
            return $rows;
        } catch (Throwable $e) {
            error_log('[awards] query failed: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Active awards of ONE student (always the caller-supplied, server-side
     * authenticated id — never a request parameter), newest first.
     * $opts: limit (int), course_id (int), school_year (string)
     */
    function awards_fetch_for_student(mysqli $conn, int $studentId, array $opts = []): array
    {
        if ($studentId <= 0 || !awards_tables_ready($conn)) return [];
        $sql = "SELECT sa.id, sa.reason, sa.awarded_at, sa.school_year, sa.course_id,
                       t.name AS award_name, t.description AS award_description, t.category, t.icon, t.tone,
                       c.course_code, c.course_name,
                       tu.first_name AS teacher_first, tu.last_name AS teacher_last
                FROM student_awards sa
                JOIN award_types t ON t.id = sa.award_type_id
                JOIN courses c ON c.id = sa.course_id
                LEFT JOIN users tu ON tu.id = sa.teacher_id
                WHERE sa.student_id = ? AND sa.status = 'Active'";
        $types = 'i';
        $params = [$studentId];
        if (!empty($opts['course_id'])) {
            $sql .= " AND sa.course_id = ?";
            $types .= 'i';
            $params[] = (int) $opts['course_id'];
        }
        if (!empty($opts['school_year'])) {
            $sql .= " AND sa.school_year = ?";
            $types .= 's';
            $params[] = (string) $opts['school_year'];
        }
        $sql .= " ORDER BY sa.awarded_at DESC, sa.id DESC";
        if (!empty($opts['limit'])) {
            $sql .= " LIMIT " . max(1, (int) $opts['limit']);
        }
        return awards_select($conn, $sql, $types, $params);
    }

    function awards_count_for_student(mysqli $conn, int $studentId): int
    {
        if ($studentId <= 0 || !awards_tables_ready($conn)) return 0;
        $rows = awards_select($conn, "SELECT COUNT(*) AS n FROM student_awards WHERE student_id = ? AND status = 'Active'", 'i', [$studentId]);
        return (int) ($rows[0]['n'] ?? 0);
    }

    function awards_teacher_name(array $row): string
    {
        $n = trim(($row['teacher_first'] ?? '') . ' ' . ($row['teacher_last'] ?? ''));
        return $n !== '' ? $n : 'Former teacher';
    }

    /**
     * Server-side authorization: may this teacher award this student in this
     * subject? Same rule as the Gradebook, Attendance and My Classes: the
     * teacher handles the subject for the student's section (teacher_assignments),
     * and the student is an active Student currently Enrolled in that subject.
     */
    function awards_teacher_can_award(mysqli $conn, int $teacherId, int $studentId, int $courseId): bool
    {
        if ($teacherId <= 0 || $studentId <= 0 || $courseId <= 0) return false;
        $rows = awards_select(
            $conn,
            "SELECT 1 AS ok
             FROM teacher_assignments ta
             JOIN enrollments e ON e.course_id = ta.course_id AND e.status = 'Enrolled'
             JOIN users u ON u.id = e.student_id AND u.role = 'Student' AND u.status = 'Active' AND u.section_dept = ta.section
             WHERE ta.teacher_id = ? AND e.student_id = ? AND ta.course_id = ?
             LIMIT 1",
            'iii',
            [$teacherId, $studentId, $courseId]
        );
        return !empty($rows);
    }

    /** Students a teacher may award, one row per (student, subject). */
    function awards_teacher_roster(mysqli $conn, int $teacherId): array
    {
        return awards_select(
            $conn,
            "SELECT DISTINCT u.id AS student_id, u.school_id, u.first_name, u.last_name, u.section_dept,
                             c.id AS course_id, c.course_code, c.course_name
             FROM teacher_assignments ta
             JOIN courses c ON c.id = ta.course_id
             JOIN enrollments e ON e.course_id = ta.course_id AND e.status = 'Enrolled'
             JOIN users u ON u.id = e.student_id AND u.role = 'Student' AND u.status = 'Active' AND u.section_dept = ta.section
             WHERE ta.teacher_id = ?
             ORDER BY u.last_name, u.first_name, c.course_code",
            'i',
            [$teacherId]
        );
    }

    /** School year to stamp on an award: the teacher's assignment for that subject (latest first). */
    function awards_school_year_for(mysqli $conn, int $teacherId, int $courseId): ?string
    {
        $rows = awards_select(
            $conn,
            "SELECT ta.school_year FROM teacher_assignments ta
             WHERE ta.teacher_id = ? AND ta.course_id = ?
             ORDER BY ta.school_year DESC, ta.id DESC LIMIT 1",
            'ii',
            [$teacherId, $courseId]
        );
        if (!empty($rows[0]['school_year'])) return $rows[0]['school_year'];
        $rows = awards_select($conn, "SELECT school_year FROM courses WHERE id = ? LIMIT 1", 'i', [$courseId]);
        return $rows[0]['school_year'] ?? null;
    }

    /**
     * Emblem styles shared by every role. Uses only its own custom
     * properties so it looks the same inside the Student, Teacher and
     * Admin layouts (and in dark mode).
     */
    function awards_emblem_css(): string
    {
        return <<<CSS
<style>
  .aw-emblem {
    --aw-w: 44px; --aw-h: 56px;
    position: relative; flex-shrink: 0;
    width: var(--aw-w); height: var(--aw-h);
    background: var(--aw-fg, #003087); color: #fff;
    display: inline-flex; align-items: flex-start; justify-content: center;
    padding-top: calc(var(--aw-h) * .2);
    clip-path: polygon(0 0, 100% 0, 100% 100%, 50% 80%, 0 100%);
  }
  .aw-emblem::before {
    content: ""; position: absolute; inset: 3px 3px auto 3px; height: calc(100% - 10px);
    border: 1px solid rgba(255,255,255,.38); border-bottom: none;
    clip-path: polygon(0 0, 100% 0, 100% 100%, 50% 80%, 0 100%);
    pointer-events: none;
  }
  .aw-emblem .aw-ico { width: calc(var(--aw-w) * .47); height: calc(var(--aw-w) * .47); position: relative; }
  .aw-emblem-sm { --aw-w: 32px; --aw-h: 41px; }
  .aw-emblem-sm::before { inset: 2px 2px auto 2px; height: calc(100% - 7px); }
  .aw-emblem-lg { --aw-w: 60px; --aw-h: 76px; }
</style>
CSS;
    }
}
