<?php
// ============================================================
//  Arandia College eLMS — Sections & section subjects API
//  File: api/sections.php
//
//  Used by admin/subjects-enrollment.php. Every action is a POST
//  with a JSON body and is checked against the admin permissions:
//    create_section / update_section / delete_section → manage_classes
//    add_subject / set_teacher / remove_subject       → assign_teachers
//    enroll_all / set_student_subjects                → enroll_students
//  Head Admin passes every check. Every change is written to the
//  admin activity log.
// ============================================================
session_start();
require_once '../config/conn.php';
require_once '../config/permissions.php';
require_once '../includes/sections.php';
require_once '../includes/auto_enroll.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['Admin', 'Head Admin'], true)) {
    http_response_code(403);
    http_response_code(empty($_SESSION['user_id']) ? 401 : 403); echo json_encode(['success' => false, 'message' => empty($_SESSION['user_id']) ? 'Your session has ended. Please sign in again.' : 'You are not allowed to do this.']);
    exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Use POST.']);
    exit;
}
if (!sectionsTableExists($conn)) {
    echo json_encode(['success' => false, 'message' => 'Import database/migration_sections_subjects.sql first.']);
    exit;
}

$actorId = (int) $_SESSION['user_id'];
$action  = $_GET['action'] ?? '';
$data    = json_decode(file_get_contents('php://input'), true) ?: [];

function out(bool $ok, string $msg, array $extra = []): void
{
    echo json_encode(array_merge(['success' => $ok, 'message' => $msg], $extra));
    exit;
}

/** Fetch one row with a prepared statement. */
function row(mysqli $conn, string $sql, string $types = '', array $params = []): ?array
{
    $st = $conn->prepare($sql);
    if ($types) $st->bind_param($types, ...$params);
    $st->execute();
    $r = $st->get_result()->fetch_assoc();
    $st->close();
    return $r ?: null;
}

function sectionByName(mysqli $conn, string $name): ?array
{
    return row($conn, "SELECT id, name, level, status FROM sections WHERE name = ? LIMIT 1", 's', [$name]);
}

/**
 * Checks a section name and works out its level from the grade:
 *   "Grade 7" … "Grade 10" (optionally "Grade 8 - Rizal")  → HS
 *   "Grade 11 - STEM-A", "Grade 12 - ABM-B" (section needed) → SHS
 * A name that does not start with "Grade N" is only accepted unchanged ($keepName),
 * so older sections can still be edited (status) without renaming them.
 * Returns [name, level] or stops with an error message.
 */
function sectionNameLevel(string $name, string $levelIn, ?string $keepName = null): array
{
    $name = trim(preg_replace('/\s+/u', ' ', $name));
    if ($name === '' || mb_strlen($name) > 100) out(false, 'Enter a section name (up to 100 characters).');
    if (preg_match('/^Grade (\d+)(?: - (.+))?$/u', $name, $m)) {
        $grade = (int) $m[1];
        $part  = $m[2] ?? '';
        if ($grade < 7 || $grade > 12) out(false, 'Pick a grade from Grade 7 to Grade 12.');
        if ($part !== '' && !preg_match('/^[\p{L}\p{N}][\p{L}\p{N} .\'&()\/-]*$/u', $part)) {
            out(false, "Use letters, numbers, spaces and - . ' & ( ) / only in the section.");
        }
        if ($grade >= 11 && $part === '') out(false, 'Enter the strand and section, e.g. STEM-A.');
        return [$name, $grade <= 10 ? 'HS' : 'SHS'];
    }
    if ($keepName !== null && $name === $keepName) return [$name, $levelIn === 'SHS' ? 'SHS' : 'HS'];
    out(false, 'Pick a grade (Grade 7 to Grade 12) for the section.');
}

/**
 * Live update for open admin pages (Sections, Accounts → Create account, Dashboard …):
 * a section was added, renamed, set Active / Inactive or deleted. Quiet (no bell).
 * Renames also reach teachers and students through the users / teacher_assignments
 * triggers (account.changed, class.changed). Never stops the save itself.
 */
function sectionSyncEvent(mysqli $conn, string $title): void
{
    try {
        $t = $conn->query("SHOW TABLES LIKE 'sync_events'");
        if (!$t || !$t->num_rows) return;
        $title = mb_substr($title, 0, 255);
        // section stays NULL: sync_events.section is a foreign key to sections.name, and a
        // deleted section's name would be refused (the event would be lost)
        $st = $conn->prepare("INSERT INTO sync_events (kind, course_id, section, owner_id, user_id, ref_id, audience, title) VALUES ('section.changed', NULL, NULL, NULL, NULL, NULL, 'admins', ?)");
        $st->bind_param('s', $title);
        $st->execute();
        $st->close();
    } catch (Throwable $e) {
        error_log('[sections.php] live event: ' . $e->getMessage());
    }
}

/**
 * A rename reaches users.section_dept and teacher_assignments.section through the foreign
 * keys (ON UPDATE CASCADE) — and the database runs no triggers for cascaded changes. So the
 * live updates the triggers would have sent are written here: every account in the section
 * (account.changed: their own pages refresh) and every teacher of it (class.changed: the
 * teachers' class pages refresh). Never stops the save itself.
 */
function sectionRenameEvents(mysqli $conn, string $newName): void
{
    try {
        $t = $conn->query("SHOW TABLES LIKE 'sync_events'");
        if (!$t || !$t->num_rows) return;
        $st = $conn->prepare("INSERT INTO sync_events (kind, course_id, section, owner_id, user_id, ref_id, audience, title)
                              SELECT 'account.changed', NULL, section_dept, NULL, id, NULL, 'all', NULL FROM users WHERE section_dept = ?");
        $st->bind_param('s', $newName);
        $st->execute();
        $st->close();
        // one per teacher (not one per class row): section left NULL so students don't get a bell
        // for every subject — their own account.changed above already refreshes their pages
        $st = $conn->prepare("INSERT INTO sync_events (kind, course_id, section, owner_id, user_id, ref_id, audience, title)
                              SELECT 'class.changed', MIN(course_id), NULL, NULL, teacher_id, NULL, 'all', NULL
                              FROM teacher_assignments WHERE section = ? AND teacher_id IS NOT NULL GROUP BY teacher_id");
        $st->bind_param('s', $newName);
        $st->execute();
        $st->close();
    } catch (Throwable $e) {
        error_log('[sections.php] rename live events: ' . $e->getMessage());
    }
}

function validTeacher(mysqli $conn, ?int $teacherId): bool
{
    if ($teacherId === null) return true;
    return (bool) row($conn, "SELECT id FROM users WHERE id = ? AND role = 'Teacher' AND status = 'Active' LIMIT 1", 'i', [$teacherId]);
}

/** Course ids taught in a section (any term). */
function sectionCourseIds(mysqli $conn, string $section): array
{
    $st = $conn->prepare("SELECT DISTINCT course_id FROM teacher_assignments WHERE section = ?");
    $st->bind_param('s', $section);
    $st->execute();
    $res = $st->get_result();
    $ids = [];
    while ($r = $res->fetch_assoc()) $ids[] = (int) $r['course_id'];
    $st->close();
    return $ids;
}

// ════════════════════════════════════════════════════════════
//  SECTIONS  (manage_classes)
// ════════════════════════════════════════════════════════════
if ($action === 'create_section') {
    requireApiPermission($conn, 'manage_classes');
    [$name, $level] = sectionNameLevel((string) ($data['name'] ?? ''), (string) ($data['level'] ?? ''));
    if (sectionByName($conn, $name)) out(false, 'A section with that name already exists.');

    // Listed right after the other sections of the same grade (Grade 7 - B after Grade 7 - A, before Grade 8)
    $gradeBase = preg_match('/^Grade \d+/', $name, $gm) ? $gm[0] : $name;
    $same = row($conn, "SELECT MAX(sort_order) AS n FROM sections WHERE level = ? AND (name = ? OR name LIKE CONCAT(?, ' - %'))", 'sss', [$level, $gradeBase, $gradeBase]);
    if ($same && $same['n'] !== null) $sort = (int) $same['n'] + 1;
    else {
        $next = row($conn, "SELECT COALESCE(MAX(sort_order), 0) + 10 AS n FROM sections WHERE level = ?", 's', [$level]);
        $sort = (int) $next['n'];
    }
    $st = $conn->prepare("INSERT INTO sections (name, level, sort_order) VALUES (?, ?, ?)");
    $st->bind_param('ssi', $name, $level, $sort);
    $st->execute();
    $id = $st->insert_id;
    $st->close();
    logAdminActivity($conn, $actorId, 'create_section', 'section', $id, "Created section '$name'.");
    sectionSyncEvent($conn, $name);
    out(true, 'Section created.', ['id' => $id]);
}

if ($action === 'update_section') {
    requireApiPermission($conn, 'manage_classes');
    $id     = (int) ($data['id'] ?? 0);
    $status = ($data['status'] ?? '') === 'Inactive' ? 'Inactive' : 'Active';
    $cur = row($conn, "SELECT id, name, level FROM sections WHERE id = ?", 'i', [$id]);
    if (!$cur) out(false, 'Section not found.');
    [$name, $level] = sectionNameLevel((string) ($data['name'] ?? ''), (string) $cur['level'], $cur['name']);
    $clash = sectionByName($conn, $name);
    if ($clash && (int) $clash['id'] !== $id) out(false, 'A section with that name already exists.');

    // A rename also updates every student/teacher and subject that uses the old name
    $conn->begin_transaction();
    try {
        $st = $conn->prepare("UPDATE sections SET name = ?, level = ?, status = ? WHERE id = ?");
        $st->bind_param('sssi', $name, $level, $status, $id);
        $st->execute();
        $st->close();
        if ($name !== $cur['name']) {
            $st = $conn->prepare("UPDATE users SET section_dept = ? WHERE section_dept = ?");
            $st->bind_param('ss', $name, $cur['name']);
            $st->execute();
            $st->close();
            $st = $conn->prepare("UPDATE teacher_assignments SET section = ? WHERE section = ?");
            $st->bind_param('ss', $name, $cur['name']);
            $st->execute();
            $st->close();
        }
        $conn->commit();
    } catch (Throwable $e) {
        $conn->rollback();
        error_log('[sections.php] update_section: ' . $e->getMessage());
        out(false, 'Could not save the section. Try again.');
    }
    $detail = $name !== $cur['name'] ? "Renamed '{$cur['name']}' to '$name'." : "Updated section '$name'.";
    logAdminActivity($conn, $actorId, 'update_section', 'section', $id, $detail);
    sectionSyncEvent($conn, $name);
    if ($name !== $cur['name']) sectionRenameEvents($conn, $name);
    out(true, 'Section saved.', ['name' => $name]);
}

if ($action === 'delete_section') {
    requireApiPermission($conn, 'manage_classes');
    $id  = (int) ($data['id'] ?? 0);
    $cur = row($conn, "SELECT id, name FROM sections WHERE id = ?", 'i', [$id]);
    if (!$cur) out(false, 'Section not found.');
    $used = row($conn,
        "SELECT (SELECT COUNT(*) FROM users WHERE section_dept = ?) AS people,
                (SELECT COUNT(*) FROM teacher_assignments WHERE section = ?) AS subjects",
        'ss', [$cur['name'], $cur['name']]);
    if ($used['people'] > 0 || $used['subjects'] > 0) {
        out(false, "This section still has {$used['people']} account(s) and {$used['subjects']} subject(s). Move or remove them first, or set the section to Inactive.");
    }
    $st = $conn->prepare("DELETE FROM sections WHERE id = ?");
    $st->bind_param('i', $id);
    $st->execute();
    $st->close();
    logAdminActivity($conn, $actorId, 'delete_section', 'section', $id, "Deleted section '{$cur['name']}'.");
    sectionSyncEvent($conn, (string) $cur['name']);
    out(true, 'Section deleted.');
}

// ════════════════════════════════════════════════════════════
//  SUBJECTS IN A SECTION  (assign_teachers)
// ════════════════════════════════════════════════════════════
if ($action === 'add_subject') {
    requireApiPermission($conn, 'assign_teachers');
    $section   = trim($data['section'] ?? '');
    $courseIds = array_values(array_unique(array_filter(array_map('intval', (array) ($data['course_ids'] ?? [])))));
    $teacherId = !empty($data['teacher_id']) ? (int) $data['teacher_id'] : null;
    $sy        = trim($data['school_year'] ?? '');
    $sem       = in_array($data['semester'] ?? '', ['1st', '2nd', 'Summer'], true) ? $data['semester'] : '1st';

    if (!sectionByName($conn, $section)) out(false, 'Section not found.');
    if (!$courseIds) out(false, 'Pick at least one subject.');
    if (!preg_match('/^\d{4}-\d{4}$/', $sy)) out(false, 'School year must look like 2025-2026.');
    if (!validTeacher($conn, $teacherId)) out(false, 'Pick an active teacher.');

    $added = $skipped = 0;
    $st = $conn->prepare(
        "INSERT INTO teacher_assignments (teacher_id, course_id, section, school_year, semester)
         SELECT ?, c.id, ?, ?, ? FROM courses c
         WHERE c.id = ? AND NOT EXISTS (
           SELECT 1 FROM teacher_assignments t
           WHERE t.course_id = c.id AND t.section = ? AND t.school_year = ? AND t.semester = ?)"
    );
    $enrolled = 0;
    foreach ($courseIds as $cid) {
        $st->bind_param('isssisss', $teacherId, $section, $sy, $sem, $cid, $section, $sy, $sem);
        $st->execute();
        if ($st->affected_rows > 0) $added++; else $skipped++;
        // The section's students take this subject automatically (includes/auto_enroll.php)
        $enrolled += aeSubjectAdded($conn, $section, $cid);
    }
    $st->close();
    logAdminActivity($conn, $actorId, 'add_section_subject', 'section', null, "$section: added $added subject(s) for $sy $sem." . ($enrolled ? " $enrolled enrollment(s) added." : ''));
    $msg = "$added subject(s) added.";
    if ($skipped) $msg .= " $skipped already in this section.";
    if ($enrolled) $msg .= " Students of $section were enrolled automatically.";
    out(true, $msg);
}

if ($action === 'set_teacher') {
    requireApiPermission($conn, 'assign_teachers');
    $id        = (int) ($data['id'] ?? 0);
    $teacherId = !empty($data['teacher_id']) ? (int) $data['teacher_id'] : null;
    $cur = row($conn, "SELECT ta.id, ta.section, c.course_code FROM teacher_assignments ta JOIN courses c ON c.id = ta.course_id WHERE ta.id = ?", 'i', [$id]);
    if (!$cur) out(false, 'Subject not found.');
    if (!validTeacher($conn, $teacherId)) out(false, 'Pick an active teacher.');

    $st = $conn->prepare("UPDATE teacher_assignments SET teacher_id = ? WHERE id = ?");
    $st->bind_param('ii', $teacherId, $id);
    $st->execute();
    $st->close();
    $who = $teacherId ? "teacher #$teacherId" : 'no teacher';
    logAdminActivity($conn, $actorId, 'assign_teacher', 'teacher_assignment', $id, "{$cur['section']} · {$cur['course_code']} → $who.");
    out(true, $teacherId ? 'Teacher assigned.' : 'Teacher removed from this subject.');
}

if ($action === 'remove_subject') {
    requireApiPermission($conn, 'assign_teachers');
    $id  = (int) ($data['id'] ?? 0);
    $cur = row($conn, "SELECT ta.id, ta.section, ta.course_id, c.course_code FROM teacher_assignments ta JOIN courses c ON c.id = ta.course_id WHERE ta.id = ?", 'i', [$id]);
    if (!$cur) out(false, 'Subject not found.');
    $st = $conn->prepare("DELETE FROM teacher_assignments WHERE id = ?");
    $st->bind_param('i', $id);
    $st->execute();
    $st->close();
    // The section's students no longer take it (kept as Dropped, never deleted)
    $dropped = aeSubjectRemoved($conn, (string) $cur['section'], (int) $cur['course_id']);
    logAdminActivity($conn, $actorId, 'remove_section_subject', 'teacher_assignment', $id, "{$cur['section']}: removed {$cur['course_code']}." . ($dropped ? " $dropped enrollment(s) marked Dropped." : ''));
    out(true, 'Subject removed from this section.');
}

// ════════════════════════════════════════════════════════════
//  ENROLLMENT  (enroll_students)
// ════════════════════════════════════════════════════════════
if ($action === 'enroll_all') {
    requireApiPermission($conn, 'enroll_students');
    $section = trim($data['section'] ?? '');
    if (!sectionByName($conn, $section)) out(false, 'Section not found.');

    $st = $conn->prepare(
        "INSERT INTO enrollments (student_id, course_id)
         SELECT DISTINCT u.id, ta.course_id
         FROM users u
         JOIN teacher_assignments ta ON ta.section = u.section_dept
         WHERE u.section_dept = ? AND u.role = 'Student' AND u.status = 'Active'
           AND NOT EXISTS (SELECT 1 FROM enrollments e WHERE e.student_id = u.id AND e.course_id = ta.course_id)"
    );
    $st->bind_param('s', $section);
    $st->execute();
    $added = $st->affected_rows;
    $st->close();
    logAdminActivity($conn, $actorId, 'enroll_section', 'section', null, "$section: $added enrollment(s) added.");
    out(true, $added ? "$added enrollment(s) added." : 'Everyone in this section is already enrolled in all its subjects.');
}

if ($action === 'set_student_subjects') {
    requireApiPermission($conn, 'enroll_students');
    $studentId = (int) ($data['student_id'] ?? 0);
    $section   = trim($data['section'] ?? '');
    $wanted    = array_map('intval', (array) ($data['course_ids'] ?? []));

    if (!sectionByName($conn, $section)) out(false, 'Section not found.');
    $stu = row($conn, "SELECT id, section_dept FROM users WHERE id = ? AND role = 'Student' LIMIT 1", 'i', [$studentId]);
    if (!$stu || $stu['section_dept'] !== $section) out(false, 'Student not found in this section.');

    $sectionCourses = sectionCourseIds($conn, $section);
    $wanted = array_values(array_intersect($wanted, $sectionCourses)); // only this section's subjects

    $added = $removed = 0;
    $conn->begin_transaction();
    try {
        $ins = $conn->prepare(
            "INSERT INTO enrollments (student_id, course_id) SELECT ?, ? FROM DUAL
             WHERE NOT EXISTS (SELECT 1 FROM enrollments WHERE student_id = ? AND course_id = ?)"
        );
        $reactivate = $conn->prepare("UPDATE enrollments SET status = 'Enrolled' WHERE student_id = ? AND course_id = ? AND status <> 'Enrolled'");
        $del = $conn->prepare("DELETE FROM enrollments WHERE student_id = ? AND course_id = ?");
        foreach ($sectionCourses as $cid) {
            if (in_array($cid, $wanted, true)) {
                $ins->bind_param('iiii', $studentId, $cid, $studentId, $cid);
                $ins->execute();
                $added += $ins->affected_rows;
                $reactivate->bind_param('ii', $studentId, $cid);
                $reactivate->execute();
            } else {
                $del->bind_param('ii', $studentId, $cid);
                $del->execute();
                $removed += $del->affected_rows;
            }
        }
        $ins->close();
        $reactivate->close();
        $del->close();
        $conn->commit();
    } catch (Throwable $e) {
        $conn->rollback();
        error_log('[sections.php] set_student_subjects: ' . $e->getMessage());
        out(false, 'Could not save enrollment. Try again.');
    }
    logAdminActivity($conn, $actorId, 'enroll_student', 'user', $studentId, "$section: +$added / -$removed subject(s).");
    out(true, 'Enrollment saved.');
}

// Enrollments in subjects the student's section doesn't take: no teacher handles them, so
// the student sees a subject nobody teaches and no teacher sees the student. Marked
// Dropped (kept for the record, hidden from the student) — never deleted.
if ($action === 'drop_outside_enrollments') {
    requireApiPermission($conn, 'enroll_students');
    $st = $conn->prepare(
        "UPDATE enrollments e JOIN users u ON u.id = e.student_id
         SET e.status = 'Dropped'
         WHERE e.status = 'Enrolled' AND u.role = 'Student'
           AND NOT EXISTS (SELECT 1 FROM teacher_assignments ta WHERE ta.course_id = e.course_id AND ta.section = u.section_dept)"
    );
    $st->execute();
    $n = $st->affected_rows;
    logAdminActivity($conn, $actorId, 'drop_outside_enrollments', 'enrollment', null, "Dropped $n enrollment(s) in subjects outside the student's section.");
    out(true, $n ? "$n enrollment(s) marked Dropped." : 'Nothing to fix.');
}

out(false, 'Invalid request.');
