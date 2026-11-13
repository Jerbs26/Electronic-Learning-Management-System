<?php
// ============================================================
//  Arandia College eLMS — Attendance API
//  File: api/attendance.php
//  Actions: list, save, export
// ============================================================

error_reporting(E_ALL);
ini_set('display_errors', 0);

// ── Disable mysqli strict exceptions so failed prepares return
//    false instead of throwing, letting our guards work properly
mysqli_report(MYSQLI_REPORT_OFF);

// ── Global error handler — always return JSON on PHP errors ──
set_error_handler(function (int $errno, string $errstr, string $errfile, int $errline): bool {
    if (!headers_sent()) {
        header('Content-Type: application/json; charset=utf-8');
    }
    echo json_encode([
        'success' => false,
        'message' => "PHP Error [$errno]: $errstr on line $errline",
    ]);
    exit;
});

// ── Global exception handler ─────────────────────────────────
set_exception_handler(function (Throwable $e): void {
    if (!headers_sent()) {
        header('Content-Type: application/json; charset=utf-8');
    }
    echo json_encode([
        'success' => false,
        'message' => 'Exception: ' . $e->getMessage() . ' on line ' . $e->getLine(),
    ]);
    exit;
});

// ── Catch fatal errors (out-of-memory, parse errors, etc.) ───
register_shutdown_function(function (): void {
    $err = error_get_last();
    if ($err && in_array($err['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        if (!headers_sent()) {
            header('Content-Type: application/json; charset=utf-8');
        }
        echo json_encode([
            'success' => false,
            'message' => 'Fatal error: ' . $err['message'] . ' on line ' . $err['line'],
        ]);
    }
});

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/conn.php';

// ── Auth check ────────────────────────────────────────────────
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Teacher') {
    header('Content-Type: application/json; charset=utf-8');
    http_response_code(empty($_SESSION['user_id']) ? 401 : 403); echo json_encode(['success' => false, 'message' => empty($_SESSION['user_id']) ? 'Your session has ended. Please sign in again.' : 'You are not allowed to do this.']);
    exit;
}

$teacher_id = (int) $_SESSION['user_id'];
$action     = trim($_GET['action'] ?? '');

// ── Helper: respond with JSON error and exit ──────────────────
function jsonError(string $msg): void
{
    if (!headers_sent()) {
        header('Content-Type: application/json; charset=utf-8');
    }
    echo json_encode(['success' => false, 'message' => $msg]);
    exit;
}

// ── Validate teacher is assigned to this course ───────────────
function validateCourseOwnership(mysqli $conn, int $teacher_id, int $course_id): bool
{
    $stmt = $conn->prepare(
        "SELECT 1 FROM teacher_assignments
         WHERE teacher_id = ? AND course_id = ? LIMIT 1"
    );
    if (!$stmt) return false;
    $stmt->bind_param('ii', $teacher_id, $course_id);
    $stmt->execute();
    $count = $stmt->get_result()->num_rows;
    $stmt->close();
    return $count > 0;
}

// ═══════════════════════════════════════════════════════════════
//  LIST  —  students + existing attendance for a course & date
// ═══════════════════════════════════════════════════════════════

/** Students in the sections where this teacher handles the subject (same rule as Grades and My Classes). */
function teacherClassStudents(mysqli $conn, int $teacher_id, int $course_id, string $section = ''): array
{
    $st = $conn->prepare(
        "SELECT DISTINCT u.id, COALESCE(u.school_id,'') AS school_id, COALESCE(u.first_name,'') AS first_name,
                COALESCE(u.last_name,'') AS last_name, COALESCE(u.section_dept,'') AS section_dept
         FROM enrollments e
         JOIN users u ON u.id = e.student_id
         JOIN teacher_assignments ta ON ta.course_id = e.course_id AND ta.section = u.section_dept AND ta.teacher_id = ?
         WHERE e.course_id = ? AND e.status = 'Enrolled' AND u.role = 'Student' AND u.status = 'Active'
           AND (? = '' OR u.section_dept = ?)
         ORDER BY u.section_dept, u.last_name, u.first_name"
    );
    if (!$st) jsonError('DB prepare error (class): ' . $conn->error);
    $st->bind_param('iiss', $teacher_id, $course_id, $section, $section);
    $st->execute();
    $rows = $st->get_result()->fetch_all(MYSQLI_ASSOC);
    $st->close();
    return $rows;
}

if ($action === 'list') {
    header('Content-Type: application/json; charset=utf-8');

    $course_id = (int) ($_GET['course_id'] ?? 0);
    $date      = trim($_GET['date'] ?? date('Y-m-d'));

    if (!$course_id) jsonError('Missing course_id.');

    if (!validateCourseOwnership($conn, $teacher_id, $course_id))
        jsonError('You do not teach this subject.');

    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date))
        jsonError('Invalid date format. Expected YYYY-MM-DD.');

    // ── Fetch enrolled, active students ──────────────────────
    $students = array_map(fn($r) => [
        'id' => (int) $r['id'], 'school_id' => $r['school_id'], 'first_name' => $r['first_name'],
        'last_name' => $r['last_name'], 'section_dept' => $r['section_dept'],
    ], teacherClassStudents($conn, $teacher_id, $course_id, trim((string) ($_GET['section'] ?? ($input['section'] ?? '')))));

    // ── Fetch existing attendance for that date ───────────────
    // `date` is backticked — DATE is a reserved word in MySQL
    $stmt = $conn->prepare(
        "SELECT student_id,
                status,
                COALESCE(remark, '') AS remark
         FROM   attendance
         WHERE  course_id = ?
           AND  `date`    = ?"
    );
    if (!$stmt) jsonError('DB prepare error (attendance): ' . $conn->error);

    $stmt->bind_param('is', $course_id, $date);
    if (!$stmt->execute()) jsonError('DB execute error (attendance): ' . $stmt->error);

    $result     = $stmt->get_result();
    $attendance = [];
    while ($row = $result->fetch_assoc()) {
        $attendance[(int) $row['student_id']] = [
            'status' => $row['status'],
            'remark' => $row['remark'],
        ];
    }
    $stmt->close();

    // When was this day last saved, and by whom
    $ss = $conn->prepare("SELECT MAX(a.updated_at) AS at, MAX(CONCAT(u.first_name, ' ', u.last_name)) AS who
                          FROM attendance a LEFT JOIN users u ON u.id = a.recorded_by
                          WHERE a.course_id = ? AND a.`date` = ?");
    $ss->bind_param('is', $course_id, $date);
    $ss->execute();
    $saved = $ss->get_result()->fetch_assoc();
    $ss->close();

    echo json_encode([
        'success'    => true,
        'students'   => $students,
        'attendance' => $attendance,
        'saved_at'   => $saved['at'] ? date('g:i A', strtotime($saved['at'])) : null,
        'saved_by'   => $saved['who'],
        'today'      => date('Y-m-d'),
    ]);
    exit;
}

// ═══════════════════════════════════════════════════════════════
//  SAVE  —  upsert attendance records
// ═══════════════════════════════════════════════════════════════
if ($action === 'save') {
    header('Content-Type: application/json; charset=utf-8');

    $raw   = file_get_contents('php://input');
    $input = json_decode($raw, true);
    if (!is_array($input)) jsonError('Invalid JSON body.');

    $course_id = (int)  ($input['course_id'] ?? 0);
    $date      = trim($input['date']         ?? '');
    $records   = $input['records']           ?? [];

    if (!$course_id) jsonError('Missing course_id.');
    if (!validateCourseOwnership($conn, $teacher_id, $course_id))
        jsonError('You do not teach this subject.');
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date))
        jsonError('Invalid date format. Expected YYYY-MM-DD.');
    if (!is_array($records) || empty($records))
        jsonError('No records to save.');
    if ($date > date('Y-m-d'))
        jsonError('You cannot take attendance for a future date.');
    if ((int) date('N', strtotime($date)) >= 6)
        jsonError('Attendance is taken Monday to Friday only.');

    $validStatuses = ['Present', 'Absent', 'Late', 'Excused'];

    // Only students in this teacher's own sections for the subject
    $enrolledIds = [];
    foreach (teacherClassStudents($conn, $teacher_id, $course_id, trim((string) ($_GET['section'] ?? ($input['section'] ?? '')))) as $r) $enrolledIds[(int) $r['id']] = true;

    // Prepare upsert ONCE — reuse for every record.
    // `date` backticked (reserved word). updated_at handled by column DEFAULT.
    $upsert = $conn->prepare(
        "INSERT INTO attendance
             (student_id, course_id, `date`, status, remark, recorded_by)
         VALUES (?, ?, ?, ?, ?, ?)
         ON DUPLICATE KEY UPDATE
             status      = VALUES(status),
             remark      = VALUES(remark),
             recorded_by = VALUES(recorded_by)"
    );
    if (!$upsert) jsonError('DB prepare error (upsert): ' . $conn->error);

    $saved = 0;
    foreach ($records as $rec) {
        if (!is_array($rec)) continue;

        $student_id = (int)  ($rec['student_id'] ?? 0);
        $status     =        trim($rec['status'] ?? 'Present');
        $remark     =        trim($rec['remark'] ?? '');

        if (!$student_id)                              continue;
        if (!in_array($status, $validStatuses, true))  continue;
        if (!isset($enrolledIds[$student_id]))          continue;

        $upsert->bind_param(
            'iisssi',
            $student_id, $course_id, $date, $status, $remark, $teacher_id
        );
        if ($upsert->execute()) $saved++;
    }
    $upsert->close();

    echo json_encode([
        'success' => true,
        'message' => "Attendance saved. {$saved} record(s) updated.",
        'saved'   => $saved,
    ]);
    exit;
}

// ═══════════════════════════════════════════════════════════════
//  HISTORY  →  one month: every day with records, per-student totals
//  GET ?action=history&course_id=&month=YYYY-MM   (&format=csv to download)
// ═══════════════════════════════════════════════════════════════
if ($action === 'history') {
    // One school week, Monday to Friday. ?week=YYYY-MM-DD (any day of that week; default: this week)
    $course_id = (int) ($_GET['course_id'] ?? 0);
    if (!$course_id) jsonError('Missing course_id.');
    if (!validateCourseOwnership($conn, $teacher_id, $course_id)) jsonError('You do not teach this subject.');
    $any = trim($_GET['week'] ?? date('Y-m-d'));
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $any)) jsonError('Invalid week. Expected YYYY-MM-DD.');
    $n = (int) date('N', strtotime($any));
    $monday = date('Y-m-d', strtotime($any . ' -' . ($n - 1) . ' days'));
    $days = [];
    for ($i = 0; $i < 5; $i++) $days[] = date('Y-m-d', strtotime("$monday +$i day"));
    $friday = end($days);

    $students = teacherClassStudents($conn, $teacher_id, $course_id, is_string($_GET['section'] ?? null) ? trim($_GET['section']) : '');
    $ids = $students ? implode(',', array_map(fn($s) => (int) $s['id'], $students)) : '0';

    // ?range=all → every school day from the first recorded day of this class up to today
    // (Monday to Friday, plus any other day that has a record). Capped at about one school year.
    $rangeAll = ($_GET['range'] ?? '') === 'all';
    if ($rangeAll) {
        $last = date('Y-m-d');
        while ((int) date('N', strtotime($last)) > 5) $last = date('Y-m-d', strtotime("$last -1 day"));
        $st = $conn->prepare("SELECT MIN(`date`) FROM attendance WHERE course_id = ? AND `date` <= ? AND student_id IN ($ids)");
        $st->bind_param('is', $course_id, $last);
        $st->execute();
        $first = $st->get_result()->fetch_row()[0] ?? null;
        $st->close();
        $floor = date('Y-m-d', strtotime('-400 days'));
        if (!$first || $first > $last) $first = $last;
        if ($first < $floor) $first = $floor;
        $first = date('Y-m-d', strtotime($first . ' -' . ((int) date('N', strtotime($first)) - 1) . ' days')); // from that Monday
        if ($first > $last) $first = $last;
        $days = [];
        for ($d = $first; $d <= $last; $d = date('Y-m-d', strtotime("$d +1 day"))) {
            if ((int) date('N', strtotime($d)) <= 5) $days[] = $d;
        }
        $monday = $first;
        $friday = $last;
    }

    $st = $conn->prepare("SELECT student_id, `date`, status, COALESCE(remark,'') AS remark FROM attendance
                          WHERE course_id = ? AND `date` BETWEEN ? AND ? AND student_id IN ($ids)");
    $st->bind_param('iss', $course_id, $monday, $friday);
    $st->execute();
    $res = $st->get_result();
    $records = [];
    $extra = [];
    while ($r = $res->fetch_assoc()) {
        $records[(int) $r['student_id']][$r['date']] = ['s' => $r['status'], 'r' => $r['remark']];
        if (!in_array($r['date'], $days, true)) $extra[$r['date']] = true;   // e.g. a Saturday make-up class
    }
    $st->close();
    if ($extra) { $days = array_merge($days, array_keys($extra)); sort($days); }
    if ($rangeAll) {
        // Only the dates that have attendance recorded for this class (no empty columns)
        $taken = [];
        foreach ($records as $recs) foreach ($recs as $d => $_) $taken[$d] = true;
        $days = array_values(array_filter($days, fn($d) => isset($taken[$d])));
    }

    // Running totals per student for this subject: every day recorded up to today (all weeks)
    $STATUSES = ['Present', 'Absent', 'Late', 'Excused'];
    $zero = array_fill_keys($STATUSES, 0);
    $totals = [];
    $today = date('Y-m-d');
    $st = $conn->prepare("SELECT student_id, status, COUNT(*) AS n FROM attendance
                          WHERE course_id = ? AND `date` <= ? AND student_id IN ($ids)
                          GROUP BY student_id, status");
    $st->bind_param('is', $course_id, $today);
    $st->execute();
    $res = $st->get_result();
    while ($r = $res->fetch_assoc()) {
        if (!in_array($r['status'], $STATUSES, true)) continue;
        $totals[(int) $r['student_id']] ??= $zero;
        $totals[(int) $r['student_id']][$r['status']] += (int) $r['n'];
    }
    $st->close();

    // Attendance % = days Present ÷ days recorded that week (Late, Excused and Absent are not "Present")
    $rows = [];
    foreach ($students as $s) {
        $sid = (int) $s['id'];
        $recs = $records[$sid] ?? [];
        $week = $zero;
        foreach ($recs as $x) if (isset($week[$x['s']])) $week[$x['s']]++;
        $present = $week['Present'];
        $rows[] = [
            'id' => $sid, 'name' => $s['first_name'] . ' ' . $s['last_name'], 'lrn' => $s['school_id'], 'section' => $s['section_dept'],
            'records' => $recs, 'present' => $present, 'recorded' => count($recs),
            'rate' => $recs ? round($present / count($recs) * 100) : null,
            'week' => $week,                       // counts for the week shown
            'total' => $totals[$sid] ?? $zero,     // counts for every recorded day so far
        ];
    }
    $course = $conn->query("SELECT course_code, course_name FROM courses WHERE id = $course_id")->fetch_assoc();
    $sections = array_values(array_unique(array_column($students, 'section_dept')));

    if (($_GET['format'] ?? '') === 'csv') {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="attendance_' . preg_replace('/[^A-Za-z0-9_-]/', '_', $course['course_code'] ?? 'class') . ($rangeAll ? '_all_to_' . $friday : '_week_' . $monday) . '.csv"');
        $out = fopen('php://output', 'w');
        fputs($out, "\xEF\xBB\xBF");
        $mark = ['Present' => '✓', 'Absent' => '✕', 'Late' => 'L', 'Excused' => 'E'];
        // All days: the week columns would repeat the totals, so only the totals are written
        fputcsv($out, array_merge(['Student', 'LRN'], array_map(fn($d) => date($rangeAll ? 'D M j, Y' : 'D M j', strtotime($d)), $days),
            $rangeAll ? [] : array_merge(array_map(fn($x) => "$x (week)", $STATUSES), ['Attendance (week)']),
            array_map(fn($x) => "$x (to date)", $STATUSES), ['Attendance (to date)']));
        foreach ($rows as $r) {
            $line = [$r['name'], $r['lrn']];
            foreach ($days as $d) $line[] = isset($r['records'][$d]) ? ($mark[$r['records'][$d]['s']] ?? $r['records'][$d]['s']) : '';
            if (!$rangeAll) {
                foreach ($STATUSES as $x) $line[] = $r['week'][$x];
                $line[] = $r['rate'] === null ? '' : $r['rate'] . '%';
            }
            foreach ($STATUSES as $x) $line[] = $r['total'][$x];
            $all = array_sum($r['total']);
            $line[] = $all ? round($r['total']['Present'] / $all * 100) . '%' : '';
            fputcsv($out, $line);
        }
        fclose($out);
        exit;
    }
    foreach ($rows as &$r) if (!$r['records']) $r['records'] = new stdClass();
    unset($r);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => true, 'days' => array_values($days), 'students' => $rows, 'today' => date('Y-m-d'), 'range' => $rangeAll ? 'all' : 'week',
                      'course' => $course['course_name'] ?? '', 'sections' => $sections]);
    exit;
}


// ═══════════════════════════════════════════════════════════════
//  EXPORT  —  download CSV
// ═══════════════════════════════════════════════════════════════
if ($action === 'export') {

    $course_id = (int) ($_GET['course_id'] ?? 0);
    $date      = trim($_GET['date'] ?? '');

    if (!$course_id) jsonError('Missing course_id.');
    if (!validateCourseOwnership($conn, $teacher_id, $course_id))
        jsonError('You do not teach this subject.');

    // ── Course info ───────────────────────────────────────────
    $cStmt = $conn->prepare(
        "SELECT course_code, course_name FROM courses WHERE id = ? LIMIT 1"
    );
    if (!$cStmt) jsonError('DB prepare error (course): ' . $conn->error);
    $cStmt->bind_param('i', $course_id);
    if (!$cStmt->execute()) jsonError('DB execute error (course): ' . $cStmt->error);
    $courseInfo = $cStmt->get_result()->fetch_assoc();
    $cStmt->close();

    // Guard against course not found
    if (!$courseInfo) jsonError('Course not found.');

    $safeCode = preg_replace('/[^A-Za-z0-9_\-]/', '_', $courseInfo['course_code']);
    $safeDate = preg_replace('/[^0-9\-]/', '',          $date ?: 'all');
    $filename = 'attendance_' . $safeCode . '_' . $safeDate . '.csv';

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Pragma: no-cache');
    header('Expires: 0');

    $out = fopen('php://output', 'w');
    fputs($out, "\xEF\xBB\xBF"); // UTF-8 BOM for Excel
    fputcsv($out, ['#', 'Student Name', 'School ID', 'Section', 'Date', 'Status', 'Remark']);

    // ── Enrolled students ─────────────────────────────────────
    $stmt = $conn->prepare(
        "SELECT u.id,
                COALESCE(u.school_id,    '') AS school_id,
                COALESCE(u.first_name,   '') AS first_name,
                COALESCE(u.last_name,    '') AS last_name,
                COALESCE(u.section_dept, '') AS section_dept
         FROM   enrollments e
         JOIN   users       u ON u.id = e.student_id
         JOIN   teacher_assignments ta ON ta.course_id = e.course_id AND ta.section = u.section_dept AND ta.teacher_id = ?
         WHERE  e.course_id = ?
           AND  e.status    = 'Enrolled'
           AND  u.role      = 'Student'
           AND  u.status    = 'Active'
         ORDER  BY u.section_dept, u.last_name, u.first_name"
    );
    if (!$stmt) { fclose($out); exit; }
    $stmt->bind_param('ii', $teacher_id, $course_id);
    $stmt->execute();
    $students = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    // ── Attendance records ────────────────────────────────────
    $attendance = [];
    $validDate  = ($date !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $date));

    if ($validDate) {
        $stmt = $conn->prepare(
            "SELECT student_id,
                    status,
                    COALESCE(remark, '') AS remark,
                    DATE_FORMAT(`date`, '%Y-%m-%d') AS att_date
             FROM   attendance
             WHERE  course_id = ? AND `date` = ?"
        );
        if (!$stmt) { fclose($out); exit; }
        $stmt->bind_param('is', $course_id, $date);
    } else {
        $stmt = $conn->prepare(
            "SELECT student_id,
                    status,
                    COALESCE(remark, '') AS remark,
                    DATE_FORMAT(`date`, '%Y-%m-%d') AS att_date
             FROM   attendance
             WHERE  course_id = ?
             ORDER  BY `date` ASC"
        );
        if (!$stmt) { fclose($out); exit; }
        $stmt->bind_param('i', $course_id);
    }
    $stmt->execute();
    $attResult = $stmt->get_result();
    while ($row = $attResult->fetch_assoc()) {
        // Use att_date (always clean YYYY-MM-DD string from DATE_FORMAT)
        $key              = ((int) $row['student_id']) . '_' . $row['att_date'];
        $attendance[$key] = $row;
    }
    $stmt->close();

    // ── Write CSV rows ────────────────────────────────────────
    $i = 1;
    foreach ($students as $s) {
        if ($validDate) {
            $key = ((int) $s['id']) . '_' . $date;
            $att = $attendance[$key] ?? null;
            fputcsv($out, [
                $i++,
                $s['last_name'] . ', ' . $s['first_name'],
                $s['school_id'],
                $s['section_dept'],
                $date,
                $att ? $att['status'] : 'N/A',
                $att ? $att['remark'] : '',
            ]);
        } else {
            $hasRecord = false;
            foreach ($attendance as $att) {
                if ((int) $att['student_id'] === (int) $s['id']) {
                    fputcsv($out, [
                        $i++,
                        $s['last_name'] . ', ' . $s['first_name'],
                        $s['school_id'],
                        $s['section_dept'],
                        $att['att_date'],
                        $att['status'],
                        $att['remark'],
                    ]);
                    $hasRecord = true;
                }
            }
            if (!$hasRecord) {
                fputcsv($out, [
                    $i++,
                    $s['last_name'] . ', ' . $s['first_name'],
                    $s['school_id'],
                    $s['section_dept'],
                    'N/A', 'N/A', '',
                ]);
            }
        }
    }

    fclose($out);
    exit;
}

// ── Unknown action ────────────────────────────────────────────
header('Content-Type: application/json; charset=utf-8');
echo json_encode([
    'success' => false,
    'message' => 'Unknown action: ' . htmlspecialchars($action, ENT_QUOTES, 'UTF-8'),
]);