<?php
// ============================================================
//  Arandia College eLMS — Assignments API
//  File: api/assignments.php
// ============================================================
session_start();
require_once '../config/conn.php';
require_once __DIR__ . '/../includes/upload_names.php';   // keep the original file name
require_once '../includes/scope.php';
require_once '../includes/module_links.php';   // which module an assignment belongs to

header('Content-Type: application/json');
// Make any failed query throw, so the error handler in config/conn.php reports the exact
// SQL problem (e.g. a missing column) instead of a vague "fetch_assoc() on bool".
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['Teacher', 'Student'], true)) {
    http_response_code(empty($_SESSION['user_id']) ? 401 : 403); echo json_encode(['success' => false, 'message' => empty($_SESSION['user_id']) ? 'Your session has ended. Please sign in again.' : 'You are not allowed to do this.']); exit;
}

$role       = $_SESSION['role'];
$user_id    = (int) $_SESSION['user_id'];
$teacher_id = $user_id; // kept for the teacher-only actions below, unchanged
$action     = $_GET['action'] ?? '';

// Teacher-only actions — everything except the two Student ones at the bottom.
$studentActions = ['my_list', 'submit'];
if (!in_array($action, $studentActions, true) && $role !== 'Teacher') {
    http_response_code(empty($_SESSION['user_id']) ? 401 : 403); echo json_encode(['success' => false, 'message' => empty($_SESSION['user_id']) ? 'Your session has ended. Please sign in again.' : 'You are not allowed to do this.']); exit;
}

function teacherOwnsCourse($conn, $teacher_id, $course_id) {
    $s = $conn->prepare("SELECT id FROM teacher_assignments WHERE teacher_id=? AND course_id=? LIMIT 1");
    $s->bind_param('ii', $teacher_id, $course_id); $s->execute();
    return (bool)$s->get_result()->fetch_assoc();
}

/**
 * The assignment's course_id if the logged-in teacher may manage it, otherwise 0:
 * they teach the subject AND made the assignment (or it has no owner yet).
 * Another teacher's assignment in a shared subject is not theirs.
 */
function teacherAssignmentCourse($conn, $teacher_id, $assignment_id) {
    $own = scopeTeacherOwned($conn, 'a', 'assignments', (int) $teacher_id);
    $s = $conn->prepare("SELECT a.course_id FROM assignments a
                         JOIN teacher_assignments ta ON ta.course_id = a.course_id AND ta.teacher_id = ?
                         WHERE a.id = ? AND $own LIMIT 1");
    $s->bind_param('ii', $teacher_id, $assignment_id); $s->execute();
    $r = $s->get_result()->fetch_assoc();
    return $r ? (int) $r['course_id'] : 0;
}

/** Shared checks for the create / update form. Returns an error message or null. */
function validateAssignmentInput(string $title, float $max_score, string $due_date): ?string {
    if ($title === '') return 'Title is required.';
    if (mb_strlen($title) > 200) return 'Title is too long (200 characters max).';
    if ($max_score <= 0 || $max_score > 10000) return 'Maximum score must be more than 0.';
    if ($due_date !== '' && strtotime($due_date) === false) return 'Due date is not a valid date.';
    return null;
}

/** Active students in the sections where this teacher handles the subject, enrolled in it. */
function teacherClassStudentIds($conn, $teacher_id, $course_id, $section = '') {
    $s = $conn->prepare("SELECT DISTINCT u.id FROM users u
                         JOIN enrollments e ON e.student_id = u.id AND e.course_id = ? AND e.status = 'Enrolled'
                         JOIN teacher_assignments ta ON ta.course_id = e.course_id AND ta.section = u.section_dept AND ta.teacher_id = ?
                         WHERE u.role = 'Student' AND u.status = 'Active' AND (? = '' OR u.section_dept = ?)");
    $section = (string) $section;
    $s->bind_param('iiss', $course_id, $teacher_id, $section, $section); $s->execute();
    return array_map('intval', array_column($s->get_result()->fetch_all(MYSQLI_ASSOC), 'id'));
}

function denyNotYours() {
    echo json_encode(['success' => false, 'message' => 'This assignment is not in one of your subjects.']); exit;
}

function studentEnrolledInCourse($conn, $student_id, $course_id) {
    $s = $conn->prepare("SELECT id FROM enrollments WHERE student_id=? AND course_id=? AND status='Enrolled' LIMIT 1");
    $s->bind_param('ii', $student_id, $course_id); $s->execute();
    return (bool)$s->get_result()->fetch_assoc();
}

// ── GET: list assignments for a course ────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'list') {
    $course_id = (int)($_GET['course_id'] ?? 0);
    if (!$course_id) { echo json_encode(['success'=>false,'message'=>'Course required.']); exit; }
    if (!teacherOwnsCourse($conn, $teacher_id, $course_id)) { echo json_encode(['success'=>false,'message'=>'Not your subject.']); exit; }
    $classIds = teacherClassStudentIds($conn, $teacher_id, $course_id, trim($_GET['section'] ?? ''));
    $classTotal = count($classIds);
    $inClass = $classIds ? implode(',', $classIds) : '0';
    $rows = [];
    $res  = $conn->query(
        "SELECT a.id, a.course_id, a.title, a.instructions, a.description, a.due_date, a.max_score,
                a.max_attempts, a.file_path, DATE_FORMAT(a.created_at,'%b %d, %Y') AS created_at,
                " . (mlEnsure($conn) ? 'a.module_id' : 'NULL AS module_id') . ",
                (SELECT COUNT(*) FROM submissions s WHERE s.assignment_id = a.id) AS submission_count,
                (SELECT COUNT(*) FROM submissions s WHERE s.assignment_id = a.id AND s.score IS NULL AND s.attempt_count > 0 AND s.student_id IN ($inClass)) AS pending_grades,
                (SELECT COUNT(*) FROM submissions s WHERE s.assignment_id = a.id AND s.student_id IN ($inClass) AND s.attempt_count > 0) AS class_submitted,
                (SELECT COUNT(*) FROM submissions s WHERE s.assignment_id = a.id AND s.student_id IN ($inClass) AND s.score IS NOT NULL) AS class_graded,
                $classTotal AS class_total
         FROM assignments a
         WHERE a.course_id = $course_id AND " . scopeTeacherOwned($conn, 'a', 'assignments', $teacher_id) . "
         ORDER BY a.created_at DESC"
    );
    while ($r = $res->fetch_assoc()) $rows[] = $r;
    echo json_encode(['success'=>true,'data'=>$rows]); exit;
}

// ── POST: create assignment ───────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'create') {
    $course_id    = (int)($_POST['course_id']    ?? 0);
    $title        = trim($_POST['title']         ?? '');
    $description  = trim($_POST['description']   ?? '');
    $due_date     = trim($_POST['due_date']       ?? '');
    $max_score    = (float)($_POST['max_score']  ?? 100);
    $max_attempts_raw = trim($_POST['max_attempts'] ?? '');
    $max_attempts = ($max_attempts_raw !== '' && (int)$max_attempts_raw > 0) ? (int)$max_attempts_raw : null;

    if (!$course_id || !$title) {
        echo json_encode(['success'=>false,'message'=>'Course and title required.']); exit;
    }
    if ($err = validateAssignmentInput($title, $max_score, $due_date)) {
        echo json_encode(['success'=>false,'message'=>$err]); exit;
    }
    if (!teacherOwnsCourse($conn, $teacher_id, $course_id)) {
        echo json_encode(['success'=>false,'message'=>'Not assigned to this course.']); exit;
    }
    // Double click / retry: the same assignment from the same teacher in the last 30 seconds is the same request.
    $dup = $conn->prepare("SELECT id FROM assignments WHERE course_id = ? AND title = ? AND created_at >= NOW() - INTERVAL 30 SECOND"
                          . (scopeHasOwner($conn, 'assignments') ? " AND teacher_id = $teacher_id" : '') . " LIMIT 1");
    $dup->bind_param('is', $course_id, $title); $dup->execute();
    if ($d = $dup->get_result()->fetch_assoc()) {
        echo json_encode(['success'=>true,'message'=>'Assignment already created.','id'=>(int) $d['id'],'duplicate'=>true]); exit;
    }

    $file_path = null;
    if (!empty($_FILES['file']['name'])) {
        $upload_dir = '../uploads/assignments/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0775, true);
        $fileErr = elmsCheckTeacherFile($_FILES['file'], $ext);
        if ($fileErr) {
            echo json_encode(['success'=>false,'message'=>$fileErr]); exit;
        }
        $filename = elmsUploadName('assign_', $_FILES['file']['name'], $ext);
        if (!move_uploaded_file($_FILES['file']['tmp_name'], $upload_dir.$filename)) {
            echo json_encode(['success'=>false,'message'=>'File upload failed.']); exit;
        }
        $file_path = 'uploads/assignments/'.$filename;
    }

    $due = $due_date ?: null;
    if (scopeHasOwner($conn, 'assignments')) {
        $stmt = $conn->prepare(
            "INSERT INTO assignments (course_id, teacher_id, title, instructions, description, due_date, max_score, max_attempts, file_path)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->bind_param('iissssdis', $course_id, $teacher_id, $title, $description, $description, $due, $max_score, $max_attempts, $file_path);
    } else {
        $stmt = $conn->prepare(
            "INSERT INTO assignments (course_id, title, instructions, description, due_date, max_score, max_attempts, file_path)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->bind_param('issssdis', $course_id, $title, $description, $description, $due, $max_score, $max_attempts, $file_path);
    }
    if ($stmt->execute()) {
        $newId = (int) $conn->insert_id;
        mlApply($conn, 'assignments', $newId, $course_id, $_POST['module_id'] ?? null, true, $teacher_id);
        echo json_encode(['success'=>true,'message'=>'Assignment created.','id'=>$newId]);
    } else {
        error_log('[assignments.php] ' . $conn->error); echo json_encode(['success' => false, 'message' => 'Could not save. Please try again.']);
    }
    exit;
}

// ── POST: update assignment ───────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'update') {
    $id          = (int)($_POST['id']          ?? 0);
    $title       = trim($_POST['title']        ?? '');
    $description = trim($_POST['description']  ?? '');
    $due_date    = trim($_POST['due_date']      ?? '');
    $max_score   = (float)($_POST['max_score'] ?? 100);
    $max_attempts_raw = trim($_POST['max_attempts'] ?? '');
    $max_attempts = ($max_attempts_raw !== '' && (int)$max_attempts_raw > 0) ? (int)$max_attempts_raw : null;

    if (!$id || !$title) {
        echo json_encode(['success'=>false,'message'=>'ID and title required.']); exit;
    }
    if ($err = validateAssignmentInput($title, $max_score, $due_date)) {
        echo json_encode(['success'=>false,'message'=>$err]); exit;
    }
    if (!teacherAssignmentCourse($conn, $teacher_id, $id)) denyNotYours();

    $file_path = null;
    if (!empty($_FILES['file']['name'])) {
        $upload_dir = '../uploads/assignments/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0775, true);
        $fileErr = elmsCheckTeacherFile($_FILES['file'], $ext);
        if ($fileErr) {
            echo json_encode(['success'=>false,'message'=>$fileErr]); exit;
        }
        // Delete old file
        $oldRes = $conn->query("SELECT file_path FROM assignments WHERE id=$id");
        $old = $oldRes ? $oldRes->fetch_assoc() : null;
        if ($old && $old['file_path'] && file_exists('../'.$old['file_path'])) unlink('../'.$old['file_path']);
        $filename = elmsUploadName('assign_', $_FILES['file']['name'], $ext);
        if (!move_uploaded_file($_FILES['file']['tmp_name'], $upload_dir.$filename)) {
            echo json_encode(['success'=>false,'message'=>'File upload failed.']); exit;
        }
        $file_path = 'uploads/assignments/'.$filename;
    }

    $due = $due_date ?: null;
    if ($file_path) {
        $stmt = $conn->prepare("UPDATE assignments SET title=?, instructions=?, description=?, due_date=?, max_score=?, max_attempts=?, file_path=? WHERE id=?");
        $stmt->bind_param('ssssdisi', $title, $description, $description, $due, $max_score, $max_attempts, $file_path, $id);
    } else {
        $stmt = $conn->prepare("UPDATE assignments SET title=?, instructions=?, description=?, due_date=?, max_score=?, max_attempts=? WHERE id=?");
        $stmt->bind_param('ssssdii', $title, $description, $description, $due, $max_score, $max_attempts, $id);
    }
    if ($stmt->execute()) {
        mlApply($conn, 'assignments', $id, teacherAssignmentCourse($conn, $teacher_id, $id), $_POST['module_id'] ?? null, false, $teacher_id);
        echo json_encode(['success'=>true,'message'=>'Assignment updated.']);
    } else {
        error_log('[assignments.php] ' . $conn->error); echo json_encode(['success' => false, 'message' => 'Could not save. Please try again.']);
    }
    exit;
}

// ── DELETE: remove assignment ─────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'DELETE' && $action === 'delete') {
    $id = (int)($_GET['id'] ?? 0);
    if (!teacherAssignmentCourse($conn, $teacher_id, $id)) denyNotYours();
    $oldRes = $conn->query("SELECT file_path FROM assignments WHERE id=$id");
    $old = $oldRes ? $oldRes->fetch_assoc() : null;
    if ($old && $old['file_path'] && file_exists('../'.$old['file_path'])) unlink('../'.$old['file_path']);
    $conn->query("DELETE FROM submissions WHERE assignment_id=$id");
    $conn->query("DELETE FROM assignments WHERE id=$id");
    echo json_encode(['success'=>true,'message'=>'Assignment deleted.']); exit;
}

// ── GET: list submissions for an assignment ───────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'submissions') {
    $assign_id = (int)($_GET['id'] ?? 0);
    if (!teacherAssignmentCourse($conn, $teacher_id, $assign_id)) denyNotYours();
    $rows = [];
    $res  = $conn->query(
        "SELECT s.id AS submission_id, s.student_id, s.file_path, s.remarks, s.score,
                s.submitted_at, s.graded_at,
                u.first_name, u.last_name, u.school_id, u.section_dept
         FROM submissions s
         JOIN users u ON u.id = s.student_id
         WHERE s.assignment_id = $assign_id
         ORDER BY u.last_name, u.first_name"
    );
    while ($r = $res->fetch_assoc()) $rows[] = $r;
    echo json_encode(['success'=>true,'data'=>$rows]); exit;
}

// ── POST: save grades for submissions ─────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'grade') {
    $body   = json_decode(file_get_contents('php://input'), true);
    $grades = $body['grades'] ?? [];
    $saved  = 0;
    foreach ($grades as $g) {
        $sid     = (int)$g['submission_id'];
        // Only submissions to this teacher's own assignments
        $own = $conn->query("SELECT s.assignment_id, a.max_score FROM submissions s JOIN assignments a ON a.id = s.assignment_id WHERE s.id = $sid")->fetch_assoc();
        if (!$own || !teacherAssignmentCourse($conn, $teacher_id, (int) $own['assignment_id'])) continue;
        $rawScore = $g['score'] ?? '';
        if ($rawScore !== '' && $rawScore !== null && !is_numeric($rawScore)) continue;
        $score   = ($rawScore !== '' && $rawScore !== null) ? (float)$rawScore : null;
        if ($score !== null && ($score < 0 || $score > (float) $own['max_score'])) continue;
        $remarks = $conn->real_escape_string($g['remarks'] ?? '');
        if ($score !== null) {
            $conn->query("UPDATE submissions SET score=$score, remarks='$remarks', graded_at=NOW(), status='Graded' WHERE id=$sid");
        } else {
            $conn->query("UPDATE submissions SET remarks='$remarks' WHERE id=$sid");
        }
        $saved++;
    }
    echo json_encode(['success'=>true,'message'=>"$saved grade(s) saved."]); exit;
}

// ── POST: grade one student (speed grader) ────────────────────────────────
// Body: { assignment_id, student_id, score (number or ""), feedback }
// A student who never submitted can still be given a score (e.g. 0 for a
// missing requirement): a placeholder row is stored with attempt_count = 0.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'grade_one') {
    $body = json_decode(file_get_contents('php://input'), true) ?: [];
    $aid = (int) ($body['assignment_id'] ?? 0);
    $sid = (int) ($body['student_id'] ?? 0);
    $feedback = trim((string) ($body['feedback'] ?? ''));
    $rawScore = $body['score'] ?? '';
    $courseId = teacherAssignmentCourse($conn, $teacher_id, $aid);
    if (!$courseId) denyNotYours();
    if (!in_array($sid, teacherClassStudentIds($conn, $teacher_id, $courseId), true)) {
        echo json_encode(['success' => false, 'message' => 'This student is not in your class.']); exit;
    }
    $max = (float) $conn->query("SELECT max_score FROM assignments WHERE id = $aid")->fetch_row()[0];
    $score = null;
    if ($rawScore !== '' && $rawScore !== null) {
        $rawScore = is_scalar($rawScore) ? trim((string) $rawScore) : '';
        // Whole numbers only. (A decimal is tolerated further down only if it is the score already on file, untouched.)
        if (!preg_match('/^\d+(\.\d+)?$/', $rawScore)) { echo json_encode(['success' => false, 'message' => 'Score must be a whole number.']); exit; }
        $score = (float) $rawScore;
        if ($score < 0 || $score > $max) { echo json_encode(['success' => false, 'message' => 'Score must be between 0 and ' . rtrim(rtrim(number_format($max, 2, '.', ''), '0'), '.') . '.']); exit; }
    }
    if (strlen($feedback) > 2000) { echo json_encode(['success' => false, 'message' => 'Feedback is too long (2,000 characters max).']); exit; }

    $exists = $conn->prepare("SELECT id, score FROM submissions WHERE assignment_id = ? AND student_id = ? LIMIT 1");
    $exists->bind_param('ii', $aid, $sid); $exists->execute();
    $row = $exists->get_result()->fetch_assoc(); $exists->close();

    if ($score !== null && floor($score) != $score) {
        $prev = ($row && $row['score'] !== null) ? (float) $row['score'] : null;
        if ($prev === null || abs($prev - $score) > 0.005) { echo json_encode(['success' => false, 'message' => 'Score must be a whole number.']); exit; }
    }

    if ($row) {
        $st = $conn->prepare("UPDATE submissions SET score = ?, remarks = ?, graded_at = IF(? IS NULL, NULL, NOW()),
                              status = IF(? IS NULL, IF(attempt_count > 0, 'Submitted', status), 'Graded') WHERE id = ?");
        $id = (int) $row['id'];
        $st->bind_param('dsddi', $score, $feedback, $score, $score, $id);
    } else {
        if ($score === null && $feedback === '') { echo json_encode(['success' => true, 'message' => 'Nothing to save.']); exit; }
        $st = $conn->prepare("INSERT INTO submissions (assignment_id, student_id, file_path, remarks, score, graded_at, status, submitted_at, attempt_count)
                              VALUES (?, ?, NULL, ?, ?, NOW(), 'Graded', NOW(), 0)");
        $st->bind_param('iisd', $aid, $sid, $feedback, $score);
    }
    if (!$st->execute()) {
        error_log('[assignments grade_one] ' . $conn->error);
        echo json_encode(['success' => false, 'message' => 'Could not save the grade. Try again.']); exit;
    }
    echo json_encode(['success' => true, 'message' => $score === null ? 'Feedback saved.' : 'Grade saved.']); exit;
}

// ── GET: a student's own assignments across all enrolled courses ─────────
if ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'my_list') {
    if ($role !== 'Student') { http_response_code(empty($_SESSION['user_id']) ? 401 : 403); echo json_encode(['success' => false, 'message' => empty($_SESSION['user_id']) ? 'Your session has ended. Please sign in again.' : 'You are not allowed to do this.']); exit; }
    $rows = [];
    $res = $conn->query(
        "SELECT a.id, a.course_id, a.title, a.instructions, a.description, a.due_date, a.max_score,
                a.max_attempts, a.file_path,
                c.course_code, c.course_name,
                s.id AS submission_id, s.file_path AS submission_file, s.status AS submission_status,
                s.score AS submission_score, s.remarks AS submission_remarks, s.submitted_at,
                COALESCE(s.attempt_count, 0) AS attempt_count
         FROM assignments a
         JOIN courses c ON c.id = a.course_id
         JOIN enrollments e ON e.course_id = a.course_id AND e.student_id = $user_id AND e.status = 'Enrolled'
         LEFT JOIN submissions s ON s.assignment_id = a.id AND s.student_id = $user_id
         WHERE " . scopeStudentOwned($conn, 'a', 'assignments', $user_id) . "
         ORDER BY (a.due_date IS NULL), a.due_date ASC, a.created_at DESC"
    );
    if ($res) { while ($r = $res->fetch_assoc()) $rows[] = $r; }
    echo json_encode(['success' => true, 'data' => $rows]); exit;
}

// ── POST: a student submits/resubmits their work for an assignment ───────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'submit') {
    if ($role !== 'Student') { http_response_code(empty($_SESSION['user_id']) ? 401 : 403); echo json_encode(['success' => false, 'message' => empty($_SESSION['user_id']) ? 'Your session has ended. Please sign in again.' : 'You are not allowed to do this.']); exit; }
    $assignment_id = (int) ($_POST['assignment_id'] ?? 0);
    if (!$assignment_id) { echo json_encode(['success' => false, 'message' => 'Assignment required.']); exit; }

    // Only an assignment this student can see (enrolled subject, and made by their section's teacher)
    $aRes = $conn->prepare("SELECT a.course_id, a.due_date, a.max_attempts FROM assignments a WHERE a.id = ? AND "
                           . scopeStudentOwned($conn, 'a', 'assignments', $user_id));
    $aRes->bind_param('i', $assignment_id);
    $aRes->execute();
    $assignment = $aRes->get_result()->fetch_assoc();
    $aRes->close();
    if (!$assignment) { echo json_encode(['success' => false, 'message' => 'Assignment not found.']); exit; }
    if (!studentEnrolledInCourse($conn, $user_id, $assignment['course_id'])) {
        echo json_encode(['success' => false, 'message' => 'You are not enrolled in this subject.']); exit;
    }
    if ($assignment['due_date'] && strtotime($assignment['due_date']) < time()) {
        echo json_encode(['success' => false, 'message' => 'The deadline for this assignment has passed. Submissions are closed.']); exit;
    }
    if (empty($_FILES['file']['name'])) {
        echo json_encode(['success' => false, 'message' => 'Please attach a file.']); exit;
    }

    // Resubmitting replaces the old file, if any — also where we read how
    // many attempts this student has already used.
    $oldRes = $conn->prepare("SELECT file_path, attempt_count FROM submissions WHERE assignment_id = ? AND student_id = ?");
    $oldRes->bind_param('ii', $assignment_id, $user_id);
    $oldRes->execute();
    $old = $oldRes->get_result()->fetch_assoc();
    $oldRes->close();

    $attemptsUsed = (int) ($old['attempt_count'] ?? 0);
    $maxAttempts = $assignment['max_attempts'] !== null ? (int) $assignment['max_attempts'] : null;
    if ($maxAttempts !== null && $attemptsUsed >= $maxAttempts) {
        echo json_encode(['success' => false, 'message' => "You've reached the maximum of $maxAttempts attempt(s) for this assignment."]); exit;
    }

    // ── Student upload rules ─────────────────────────────────────────────
    // Document (pdf, docx, pptx, xlsx) max 20MB · Image (jpg, jpeg, png) max 20MB · Video (mp4) max 100MB
    $upFile  = $_FILES['file'];
    $ext     = strtolower(pathinfo((string) $upFile['name'], PATHINFO_EXTENSION));
    $upRules = ['pdf' => 20, 'docx' => 20, 'pptx' => 20, 'xlsx' => 20, 'jpg' => 20, 'jpeg' => 20, 'png' => 20, 'mp4' => 100]; // max MB
    $upHelp  = 'Allowed: PDF, DOCX, PPTX, XLSX (max 20 MB) · JPG, PNG (max 20 MB) · MP4 (max 100 MB).';
    $uploadErr = '';
    if ($upFile['error'] === UPLOAD_ERR_INI_SIZE || $upFile['error'] === UPLOAD_ERR_FORM_SIZE) {
        $uploadErr = 'File is too large. ' . $upHelp;
    } elseif ($upFile['error'] !== UPLOAD_ERR_OK) {
        $uploadErr = 'Upload failed. Please try again.';
    } elseif (!isset($upRules[$ext])) {
        $uploadErr = 'File type not allowed. ' . $upHelp;
    } elseif ($upFile['size'] > $upRules[$ext] * 1024 * 1024) {
        $uploadErr = 'This file type can be up to ' . $upRules[$ext] . ' MB only.';
    } else {
        // check the real content matches the extension (e.g. an .exe renamed to .pdf is rejected)
        $fh   = @fopen($upFile['tmp_name'], 'rb');
        $head = $fh ? (string) fread($fh, 16) : '';
        if ($fh) fclose($fh);
        $magicOk = false;
        if ($ext === 'pdf')                              $magicOk = strncmp($head, '%PDF', 4) === 0;
        elseif ($ext === 'jpg' || $ext === 'jpeg')       $magicOk = strncmp($head, "\xFF\xD8\xFF", 3) === 0;
        elseif ($ext === 'png')                          $magicOk = strncmp($head, "\x89PNG\r\n\x1A\n", 8) === 0;
        elseif ($ext === 'mp4')                          $magicOk = substr($head, 4, 4) === 'ftyp';
        else                                             $magicOk = strncmp($head, 'PK', 2) === 0; // docx / pptx / xlsx are zip files
        if (!is_uploaded_file($upFile['tmp_name']) || !$magicOk) {
            $uploadErr = 'This file looks damaged or is not really a .' . strtoupper($ext) . ' file.';
        }
    }
    if ($uploadErr !== '') {
        echo json_encode(['success' => false, 'message' => $uploadErr]); exit;
    }

    $upload_dir = '../uploads/submissions/';
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0775, true);

    if ($old && $old['file_path'] && is_file('../' . $old['file_path'])) {
        @unlink('../' . $old['file_path']);
    }

    $filename = elmsUploadName('sub_', $_FILES['file']['name'], $ext);
    if (!move_uploaded_file($_FILES['file']['tmp_name'], $upload_dir . $filename)) {
        echo json_encode(['success' => false, 'message' => 'Upload failed.']); exit;
    }
    $file_path = 'uploads/submissions/' . $filename;

    // The deadline check above already rejects anything submitted past due,
    // so a submission that reaches this point is always on time.
    $status = 'Submitted';

    $stmt = $conn->prepare(
        "INSERT INTO submissions (assignment_id, student_id, file_path, status, submitted_at, attempt_count)
         VALUES (?, ?, ?, ?, NOW(), 1)
         ON DUPLICATE KEY UPDATE file_path = VALUES(file_path), status = VALUES(status), submitted_at = NOW(),
             score = NULL, graded_at = NULL, attempt_count = attempt_count + 1"
    );
    $stmt->bind_param('iiss', $assignment_id, $user_id, $file_path, $status);
    if ($stmt->execute()) {
        $attemptsLeftMsg = $maxAttempts !== null ? ' (' . ($attemptsUsed + 1) . ' of ' . $maxAttempts . ' attempts used)' : '';
        echo json_encode(['success' => true, 'message' => 'Submitted!' . $attemptsLeftMsg]);
    } else {
        error_log('[assignments.php] ' . $conn->error); echo json_encode(['success' => false, 'message' => 'Could not save. Please try again.']);
    }
    exit;
}

echo json_encode(['success'=>false,'message'=>'Invalid request.']);