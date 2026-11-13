<?php
// ============================================================
//  Arandia College eLMS — Student Quiz API
//  File: api/student_quiz.php
// ============================================================
session_start();
require_once '../config/conn.php';
require_once '../includes/scope.php';
require_once '../includes/quiz_attempts.php';
require_once __DIR__ . '/../includes/upload_names.php';   // keep the original file name

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Student') {
    http_response_code(empty($_SESSION['user_id']) ? 401 : 403); echo json_encode(['success' => false, 'message' => empty($_SESSION['user_id']) ? 'Your session has ended. Please sign in again.' : 'You are not allowed to do this.']); exit;
}

$student_id = (int)$_SESSION['user_id'];
$action     = $_GET['action'] ?? '';
qaEnsureSchema($conn);   // quiz_attempts.last_active_at

// Quizzes this student may take: published, in an Enrolled subject, from their section's teacher.
$quizScope = "q.status = 'Published' AND " . scopeStudentOwned($conn, 'q', 'quizzes', $student_id) . "
              AND EXISTS (SELECT 1 FROM enrollments e WHERE e.course_id = q.course_id AND e.student_id = $student_id AND e.status = 'Enrolled')";

/** Null when the quiz is open now, otherwise the reason it isn't. $graceMin lets an answer sent just after closing still count. */
function quizWindowError(array $q, int $graceMin = 0): ?string {
    $now = time();
    if (!empty($q['open_at']) && strtotime($q['open_at']) > $now) return 'This quiz is not open yet. It opens ' . date('M j, g:i A', strtotime($q['open_at'])) . '.';
    if (!empty($q['close_at']) && strtotime($q['close_at']) + $graceMin * 60 < $now) return 'This quiz is already closed.';
    return null;
}

// ── GET: list quizzes available to student ────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'list') {
    $rows = [];
    $res  = $conn->query(
        "SELECT q.id, q.title, q.description, q.time_limit, q.max_score, q.passing_score,
                q.open_at, q.close_at, q.quiz_type, q.file_path, q.show_results, q.show_correct_answers,
                c.course_name, c.course_code,
                (SELECT COUNT(*) FROM quiz_questions WHERE quiz_id = q.id) AS question_count,
                (SELECT id FROM quiz_attempts
                 WHERE quiz_id = q.id AND student_id = $student_id
                 ORDER BY FIELD(status,'Graded','Submitted','In Progress') ASC, started_at DESC LIMIT 1) AS attempt_id,
                (SELECT status FROM quiz_attempts
                 WHERE quiz_id = q.id AND student_id = $student_id
                 ORDER BY FIELD(status,'Graded','Submitted','In Progress') ASC, started_at DESC LIMIT 1) AS attempt_status,
                (SELECT score FROM quiz_attempts
                 WHERE quiz_id = q.id AND student_id = $student_id
                 ORDER BY FIELD(status,'Graded','Submitted','In Progress') ASC, started_at DESC LIMIT 1) AS my_score
         FROM quizzes q
         JOIN courses c ON c.id = q.course_id
         WHERE $quizScope
         ORDER BY q.created_at DESC"
    );
    while ($r = $res->fetch_assoc()) $rows[] = $r;
    echo json_encode(['success' => true, 'data' => $rows]); exit;
}

// ── GET: get quiz questions for taking ────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'start') {
    $quiz_id = (int)($_GET['id'] ?? 0);

    $chk = $conn->query(
        "SELECT q.id, q.title, q.time_limit, q.max_score, q.open_at, q.close_at,
                c.course_name
         FROM quizzes q
         JOIN courses c ON c.id = q.course_id
         WHERE q.id = $quiz_id AND $quizScope LIMIT 1"
    );
    $quiz = $chk->fetch_assoc();
    if (!$quiz) { echo json_encode(['success'=>false,'message'=>'Quiz not found or not enrolled.']); exit; }
    if ($err = quizWindowError($quiz)) { echo json_encode(['success'=>false,'message'=>$err]); exit; }

    // A quiz left earlier (closed window, lost connection) is submitted now with the
    // answers saved so far — there is no "resume" (includes/quiz_attempts.php)
    qaCloseAbandoned($conn, $student_id);

    // Check if already submitted — priority: Graded > Submitted > In Progress
    $prev = $conn->query(
        "SELECT id, status, score FROM quiz_attempts
         WHERE quiz_id = $quiz_id AND student_id = $student_id
         ORDER BY FIELD(status,'Graded','Submitted','In Progress') ASC, started_at DESC LIMIT 1"
    )->fetch_assoc();

    if ($prev && ($prev['status'] === 'Submitted' || $prev['status'] === 'Graded')) {
        echo json_encode(['success'=>false,'message'=>'You already took this quiz. It was submitted with the answers you gave.','already'=>true,'score'=>$prev['score'],'attempt_id'=>$prev['id']]); exit;
    }
    // Still active in another window: one attempt at a time, no second copy
    if ($prev && $prev['status'] === 'In Progress') {
        echo json_encode(['success'=>false,'message'=>'This quiz is already open in another window. Finish it there — leaving it submits your answers.']); exit;
    }

    $conn->query("INSERT INTO quiz_attempts (quiz_id, student_id, last_active_at) VALUES ($quiz_id, $student_id, NOW())");
    $attempt_id = $conn->insert_id;

    // Fetch questions WITHOUT is_correct
    $questions = [];
    $qRes = $conn->query("SELECT id, question_text, question_type, points FROM quiz_questions WHERE quiz_id = $quiz_id ORDER BY id");
    while ($q = $qRes->fetch_assoc()) {
        $choices = [];
        $cRes = $conn->query("SELECT id, choice_text FROM quiz_choices WHERE question_id = {$q['id']} ORDER BY id");
        while ($c = $cRes->fetch_assoc()) $choices[] = $c;
        $q['choices'] = $choices;
        $questions[]  = $q;
    }

    $quiz['questions']  = $questions;
    $quiz['attempt_id'] = $attempt_id;
    echo json_encode(['success' => true, 'data' => $quiz]); exit;
}

// ── POST: submit quiz answers ─────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'submit') {
    $body       = json_decode(file_get_contents('php://input'), true);
    $attempt_id = (int)($body['attempt_id'] ?? 0);
    $answers    = $body['answers'] ?? [];

    $att = $conn->query(
        "SELECT qa.id, qa.quiz_id, qa.status, q.max_score, q.passing_score, q.show_results, q.show_correct_answers, q.open_at, q.close_at
         FROM quiz_attempts qa JOIN quizzes q ON q.id = qa.quiz_id
         WHERE qa.id = $attempt_id AND qa.student_id = $student_id AND $quizScope LIMIT 1"
    )->fetch_assoc();

    if (!$att) { echo json_encode(['success'=>false,'message'=>'Invalid attempt.']); exit; }
    // 5-minute grace so an answer sent right at closing time (slow network) still counts
    if ($err = quizWindowError($att, 5)) { echo json_encode(['success'=>false,'message'=>$err]); exit; }
    if ($att['status'] === 'Submitted' || $att['status'] === 'Graded') {
        echo json_encode(['success'=>false,'message'=>'Already submitted.']); exit;
    }

    // Grade on the SERVER (includes/quiz_attempts.php). Answers sent now win over
    // answers saved earlier; anything not answered counts as wrong.
    $answers = (is_array($answers) ? $answers : []) + qaSavedAnswers($conn, $attempt_id);
    $fin = qaFinalize($conn, $att + ['id' => $attempt_id], $answers);
    if ($fin === null) { echo json_encode(['success'=>false,'message'=>'Already submitted.']); exit; }
    [$scaled, $percentage, $passed, $breakdown, $pending, $autoEarned, $autoMax] = $fin;

    $response = ['success' => true, 'message' => 'Quiz submitted!'];
    if ($pending > 0) {
        // Essay answers wait for the teacher: no final score yet. Tell the student
        // what the auto-checked part gave (only if the teacher shows results).
        $response['pending_review'] = true;
        $response['pending_essays'] = $pending;
        if ($att['show_results']) {
            $response['auto_points'] = round($autoEarned, 2);
            $response['auto_points_max'] = round($autoMax - (float) $conn->query(
                "SELECT COALESCE(SUM(IF(points > 0, points, 1)), 0) AS p FROM quiz_questions WHERE quiz_id = " . (int) $att['quiz_id'] . " AND question_type = 'long_essay'"
            )->fetch_assoc()['p'], 2);
        }
    } elseif ($att['show_results']) {
        $response['score'] = $scaled;
        $response['max_score'] = $att['max_score'];
        $response['percentage'] = $percentage;
        $response['passed'] = $passed;
        $response['passing_score'] = $att['passing_score'] !== null ? (float) $att['passing_score'] : null;   // shown on the result card
        if ($att['show_correct_answers']) {
            $response['breakdown'] = $breakdown;
        }
    } else {
        $response['pending_review'] = true;
    }
    echo json_encode($response); exit;
}

// ── POST: save answers while the quiz is open (also: "still here") ───────
//  Body: { attempt_id, answers }. Nothing is final yet; if the student leaves,
//  these saved answers are what gets graded.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'save') {
    $body       = json_decode(file_get_contents('php://input'), true);
    $attempt_id = (int) ($body['attempt_id'] ?? 0);
    $answers    = is_array($body['answers'] ?? null) ? $body['answers'] : [];
    $att = $conn->query(
        "SELECT qa.id, qa.quiz_id, qa.status, qa.started_at, q.max_score, q.passing_score, q.time_limit, q.open_at, q.close_at
         FROM quiz_attempts qa JOIN quizzes q ON q.id = qa.quiz_id
         WHERE qa.id = $attempt_id AND qa.student_id = $student_id AND $quizScope LIMIT 1"
    )->fetch_assoc();
    if (!$att) { echo json_encode(['success' => false, 'message' => 'Invalid attempt.']); exit; }
    if ($att['status'] !== 'In Progress') { echo json_encode(['success' => false, 'finished' => true, 'message' => 'This quiz was already submitted.']); exit; }
    // Time is up (+1 minute grace) or the quiz closed: submit what was answered
    $timeUp = (int) $att['time_limit'] > 0 && strtotime($att['started_at']) + ((int) $att['time_limit'] + 1) * 60 < time();
    if ($timeUp || quizWindowError($att, 5)) {
        qaFinalize($conn, $att, $answers + qaSavedAnswers($conn, $attempt_id));
        echo json_encode(['success' => false, 'finished' => true, 'message' => 'Time is up. Your quiz was submitted.']); exit;
    }
    qaGrade($conn, $attempt_id, (int) $att['quiz_id'], $answers);
    $conn->query("UPDATE quiz_attempts SET last_active_at = NOW() WHERE id = $attempt_id");
    echo json_encode(['success' => true]); exit;
}

// ── POST: submit file for file-type quiz ──────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'submit_file') {
    $quiz_id = (int)($_POST['quiz_id'] ?? 0);
    if (!$quiz_id) { echo json_encode(['success'=>false,'message'=>'Quiz ID required.']); exit; }

    // A published file-type quiz this student may take (enrolled, own section's teacher), open now
    $quiz = $conn->query("SELECT q.* FROM quizzes q WHERE q.id=$quiz_id AND q.quiz_type='file' AND $quizScope")->fetch_assoc();
    if (!$quiz) { echo json_encode(['success'=>false,'message'=>'Quiz not found or you are not enrolled in it.']); exit; }
    if ($err = quizWindowError($quiz, 5)) { echo json_encode(['success'=>false,'message'=>$err]); exit; }

    // Check if already graded — do not allow resubmission if graded
    $graded = $conn->query(
        "SELECT id FROM quiz_attempts
         WHERE quiz_id=$quiz_id AND student_id=$student_id AND status='Graded' LIMIT 1"
    )->fetch_assoc();
    if ($graded) {
        echo json_encode(['success'=>false,'message'=>'This quiz has already been graded. Resubmission is not allowed.']); exit;
    }

    // Handle file upload
    if (empty($_FILES['file']['name'])) {
        echo json_encode(['success'=>false,'message'=>'Please select a file to upload.']); exit;
    }
    $upload_dir = '../uploads/quiz_submissions/';
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0775, true);
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
        echo json_encode(['success'=>false,'message'=>$uploadErr]); exit;
    }
    $filename = elmsUploadName('qsub_'.$student_id.'_'.$quiz_id.'_', $_FILES['file']['name'], $ext);
    if (!move_uploaded_file($_FILES['file']['tmp_name'], $upload_dir.$filename)) {
        echo json_encode(['success'=>false,'message'=>'File upload failed.']); exit;
    }
    $file_path = 'uploads/quiz_submissions/'.$filename;

    // Replace any earlier ungraded attempt with this one, in one transaction so a
    // failure never leaves the student with no attempt at all.
    $conn->begin_transaction();
    try {
        $old = $conn->query("SELECT file_path FROM quiz_attempts WHERE quiz_id=$quiz_id AND student_id=$student_id AND status != 'Graded'");
        $oldFiles = $old ? array_column($old->fetch_all(MYSQLI_ASSOC), 'file_path') : [];
        $conn->query("DELETE FROM quiz_attempts WHERE quiz_id=$quiz_id AND student_id=$student_id AND status != 'Graded'");
        $ins = $conn->prepare("INSERT INTO quiz_attempts (quiz_id, student_id, file_path, started_at, submitted_at, finished_at, status)
                               VALUES (?, ?, ?, NOW(), NOW(), NOW(), 'Submitted')");
        $ins->bind_param('iis', $quiz_id, $student_id, $file_path);
        $ins->execute();
        $conn->commit();
        foreach ($oldFiles as $of) if ($of && strpos($of, 'uploads/quiz_submissions/') === 0 && is_file('../' . $of)) @unlink('../' . $of);
    } catch (Throwable $e) {
        $conn->rollback();
        @unlink($upload_dir . $filename);
        error_log('[student_quiz submit_file] ' . $e->getMessage());
        echo json_encode(['success'=>false,'message'=>'Could not save your file. Please try again.']); exit;
    }

    echo json_encode(['success'=>true,'message'=>'File submitted successfully! Your teacher will review and grade it.']); exit;
}

echo json_encode(['success'=>false,'message'=>'Invalid request.']);