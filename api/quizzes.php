<?php
    // ============================================================
    //  Arandia College eLMS — Quizzes API
    //  File: api/quizzes.php
    // ============================================================
    session_start();
    require_once '../config/conn.php';
require_once __DIR__ . '/../includes/upload_names.php';   // keep the original file name
    require_once '../includes/scope.php';

    header('Content-Type: application/json');

    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Teacher') {
        http_response_code(empty($_SESSION['user_id']) ? 401 : 403); echo json_encode(['success' => false, 'message' => empty($_SESSION['user_id']) ? 'Your session has ended. Please sign in again.' : 'You are not allowed to do this.']);
        exit;
    }

    $teacher_id = (int) $_SESSION['user_id'];
    $action = $_GET['action'] ?? '';

    function teacherOwnsCourse($conn, $teacher_id, $course_id)
    {
        $s = $conn->prepare("SELECT id FROM teacher_assignments WHERE teacher_id=? AND course_id=? LIMIT 1");
        $s->bind_param('ii', $teacher_id, $course_id);
        $s->execute();
        return (bool) $s->get_result()->fetch_assoc();
    }

    /** The quiz's course_id when the logged-in teacher teaches that subject, otherwise 0. */
    require_once __DIR__ . '/../includes/module_links.php';   // which module a quiz belongs to
    require_once __DIR__ . '/../includes/quiz_attempts.php';  // essay questions (teacher-graded)
    qaEnsureEssaySchema($conn);
    /** module_id sent with the form (multipart or JSON); null when not sent */
    function quizPostedModule()
    {
        if (array_key_exists('module_id', $_POST)) return $_POST['module_id'];
        static $json = null;
        if ($json === null) $json = json_decode(file_get_contents('php://input'), true) ?: [];
        return array_key_exists('module_id', $json) ? (string) $json['module_id'] : null;
    }

    function teacherQuizCourse($conn, $teacher_id, $quiz_id)
    {
        // Own quiz (or one with no owner yet) in a subject this teacher teaches
        $own = scopeTeacherOwned($conn, 'q', 'quizzes', (int) $teacher_id);
        $s = $conn->prepare("SELECT q.course_id FROM quizzes q
                             JOIN teacher_assignments ta ON ta.course_id = q.course_id AND ta.teacher_id = ?
                             WHERE q.id = ? AND $own LIMIT 1");
        $s->bind_param('ii', $teacher_id, $quiz_id);
        $s->execute();
        $r = $s->get_result()->fetch_assoc();
        return $r ? (int) $r['course_id'] : 0;
    }

    /** Title, score and the open / close window. Returns an error message or null. */
    function validateQuizInput($title, $max_score, $open_at, $close_at)
    {
        if (trim((string) $title) === '') return 'Title is required.';
        if (mb_strlen($title) > 200) return 'Title is too long (200 characters max).';
        if ((float) $max_score <= 0) return 'Maximum score must be more than 0.';
        foreach (['Opens' => $open_at, 'Closes' => $close_at] as $label => $v) {
            if ($v && strtotime($v) === false) return "$label date is not a valid date.";
        }
        if ($open_at && $close_at && strtotime($close_at) <= strtotime($open_at)) return 'The closing time must be after the opening time.';
        return null;
    }

    /** A JSON / form value as trimmed text ("" for arrays, true/false and null). 100.0 -> "100". */
    function quizStr($v): string
    {
        if (is_int($v)) return (string) $v;
        if (is_float($v)) return floor($v) == $v ? (string) (int) $v : (string) $v;
        return is_string($v) ? trim($v) : '';
    }

    /**
     * Required quiz settings: instructions, max score (whole number), passing score (%),
     * open at and close at. $old = the quiz's saved values when editing: an older decimal
     * max / passing score may stay as it is. On success fills $out and returns null.
     */
    function quizRequired($description, $maxRaw, $passRaw, $openRaw, $closeRaw, ?array $old, ?array &$out)
    {
        $description = quizStr($description);
        $maxRaw = quizStr($maxRaw);
        $passRaw = quizStr($passRaw);
        $open = quizStr($openRaw);
        $close = quizStr($closeRaw);
        if ($description === '') return 'Instructions are required.';
        if (mb_strlen($description) > 5000) return 'Instructions are too long (5,000 characters max).';

        $keepMax = $old && is_numeric($maxRaw) && (float) $maxRaw == (float) $old['max_score'];
        if ($maxRaw === '') return 'Max score is required.';
        if (!$keepMax && (!preg_match('/^\d{1,5}$/', $maxRaw) || (int) $maxRaw < 1 || (int) $maxRaw > 10000)) {
            return 'Max score must be a whole number from 1 to 10000 (no decimals).';
        }
        $max = $keepMax ? (float) $old['max_score'] : (float) (int) $maxRaw;

        $keepPass = $old && $old['passing_score'] !== null && is_numeric($passRaw) && (float) $passRaw == (float) $old['passing_score'];
        if ($passRaw === '') return 'Passing score is required.';
        if (!$keepPass && (!preg_match('/^\d{1,3}$/', $passRaw) || (int) $passRaw < 1 || (int) $passRaw > 100)) {
            return 'Passing score must be a whole number from 1 to 100 (%).';
        }
        $pass = $keepPass ? (float) $old['passing_score'] : (float) (int) $passRaw;

        if ($open === '') return 'Open at is required.';
        if ($close === '') return 'Close at is required.';
        $o = strtotime($open);
        $c = strtotime($close);
        if ($o === false) return 'Open at is not a valid date.';
        if ($c === false) return 'Close at is not a valid date.';
        if ($c <= $o) return 'The closing time must be after the opening time.';

        $out = ['description' => $description, 'max_score' => $max, 'passing_score' => $pass,
                'open_at' => date('Y-m-d H:i:s', $o), 'close_at' => date('Y-m-d H:i:s', $c)];
        return null;
    }

    /** Saved values of a quiz (for quizRequired when editing). */
    function quizOld(mysqli $conn, int $id): ?array
    {
        $st = $conn->prepare("SELECT max_score, passing_score, title, description, time_limit, open_at, close_at, status FROM quizzes WHERE id = ?");
        $st->bind_param('i', $id);
        $st->execute();
        $r = $st->get_result()->fetch_assoc();
        $st->close();
        return $r ?: null;
    }

    /**
     * The database trigger (trg_gb_quiz_update) sends "quiz.updated" when the title, dates,
     * max score, time limit or instructions change — not when only the passing score does.
     * Students see the passing score, so send the live update for that case here.
     */
    function quizPassingEvent(mysqli $conn, int $id, ?array $old): void
    {
        if (!$old) return;
        try {
            $t = $conn->query("SHOW TABLES LIKE 'sync_events'");
            if (!$t || !$t->num_rows) return;
            $owner = scopeHasOwner($conn, 'quizzes') ? 'teacher_id' : 'NULL';
            $st = $conn->prepare("SELECT course_id, $owner AS owner_id, title, description, time_limit, max_score, passing_score, open_at, close_at, status FROM quizzes WHERE id = ?");
            $st->bind_param('i', $id);
            $st->execute();
            $new = $st->get_result()->fetch_assoc();
            $st->close();
            if (!$new || $new['status'] !== 'Published' || $old['status'] !== 'Published') return;   // drafts: students don't see them
            $same = fn($a, $b) => ($a === null && $b === null) || ($a !== null && $b !== null && (string) $a === (string) $b);
            $num = fn($a, $b) => ($a === null && $b === null) || ($a !== null && $b !== null && (float) $a == (float) $b);
            if ($num($old['passing_score'], $new['passing_score'])) return;                          // passing score did not change
            $triggerFired = !($same($old['title'], $new['title']) && $same($old['description'], $new['description'])
                && $num($old['time_limit'], $new['time_limit']) && $num($old['max_score'], $new['max_score'])
                && $same($old['open_at'], $new['open_at']) && $same($old['close_at'], $new['close_at']));
            if ($triggerFired) return;                                                               // the trigger already sent it
            $cid = (int) $new['course_id'];
            $own = $new['owner_id'] !== null ? (int) $new['owner_id'] : null;
            $title = mb_substr((string) $new['title'], 0, 255);
            $st = $conn->prepare("INSERT INTO sync_events (kind, course_id, section, owner_id, user_id, ref_id, audience, title) VALUES ('quiz.updated', ?, NULL, ?, NULL, ?, 'all', ?)");
            $st->bind_param('iiis', $cid, $own, $id, $title);
            $st->execute();
            $st->close();
        } catch (Throwable $e) {
            error_log('[quizzes] live event: ' . $e->getMessage());   // the quiz itself is already saved
        }
    }

    function quizFail(string $msg): void
    {
        echo json_encode(['success' => false, 'message' => $msg]);
        exit;
    }

    function denyQuiz()
    {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'This quiz is not in one of your subjects.']);
        exit;
    }

    // Returns an error message if the question set isn't complete enough to
    // publish, or null if it's good to go. Never blocks saving as a Draft.
    function validateQuestionsForPublish($questions, $question_count)
    {
        if (empty($questions)) {
            return 'Add at least one question before publishing.';
        }
        if ($question_count && count($questions) !== (int) $question_count) {
            return "This quiz needs exactly $question_count questions — you have " . count($questions) . '. Finish the remaining slots or publish later.';
        }
        foreach ($questions as $i => $q) {
            $n = $i + 1;
            if (trim($q['question_text'] ?? '') === '') {
                return "Question $n is missing its text.";
            }
            if ((float) ($q['points'] ?? 0) <= 0) {
                return "Question $n needs points (more than 0).";
            }
            $qtype = $q['question_type'] ?? 'multiple_choice';
            if ($qtype === 'long_essay') {
                // Essay: no answer key — the teacher grades it after the student submits
            } elseif ($qtype === 'essay') {
                if (trim($q['identification_answer'] ?? '') === '') {
                    return "Question $n (Identification) needs an accepted answer.";
                }
                if (qaIdAnswerTooLong($q['identification_answer'])) {
                    return "Question $n (Identification): the answer must be a word or short name (up to " . QA_ID_MAX_WORDS . " words), not a sentence. Use Essay for longer answers.";
                }
            } else {
                $choices = $q['choices'] ?? [];
                $hasCorrect = false;
                $filled = 0;
                foreach ($choices as $c) {
                    if (trim($c['choice_text'] ?? '') !== '') {
                        $filled++;
                        if (!empty($c['is_correct'])) $hasCorrect = true;
                    }
                }
                if ($filled < 2) {
                    return "Question $n needs at least 2 choices.";
                }
                if (!$hasCorrect) {
                    return "Question $n has no correct answer marked.";
                }
            }
        }
        return null;
    }

    // Replaces every question (and its choices) belonging to a quiz with
    // the given set — used by both create and update/save-draft.
    function saveQuizQuestions($conn, $quiz_id, $questions)
    {
        $conn->query("DELETE qc FROM quiz_choices qc JOIN quiz_questions qq ON qq.id=qc.question_id WHERE qq.quiz_id=$quiz_id");
        $conn->query("DELETE FROM quiz_questions WHERE quiz_id=$quiz_id");

        foreach ($questions as $q) {
            $qtext = trim($q['question_text'] ?? '');
            if (!$qtext) continue;
            $qtype = $q['question_type'] ?? 'multiple_choice';
            if (!in_array($qtype, ['multiple_choice', 'true_false', 'essay', 'long_essay'], true)) $qtype = 'multiple_choice';
            $pts = (float) ($q['points'] ?? 1);
            // Identification: the accepted answer · Essay: optional grading guide (only the teacher sees it)
            $idAnswer = ($qtype === 'essay' || $qtype === 'long_essay') ? mb_substr(trim($q['identification_answer'] ?? ''), 0, 255) : null;
            if ($qtype === 'long_essay' && $idAnswer === '') $idAnswer = null;

            $qs = $conn->prepare("INSERT INTO quiz_questions (quiz_id, question_text, question_type, points, identification_answer) VALUES (?,?,?,?,?)");
            $qs->bind_param('issds', $quiz_id, $qtext, $qtype, $pts, $idAnswer);
            $qs->execute();
            $q_id = $conn->insert_id;

            if ($qtype !== 'essay' && $qtype !== 'long_essay') {
                foreach ($q['choices'] ?? [] as $c) {
                    $ctext = trim($c['choice_text'] ?? '');
                    $is_cor = (int) ($c['is_correct'] ?? 0);
                    if (!$ctext) continue;
                    $cs = $conn->prepare("INSERT INTO quiz_choices (question_id, choice_text, is_correct) VALUES (?,?,?)");
                    $cs->bind_param('isi', $q_id, $ctext, $is_cor);
                    $cs->execute();
                }
            }
        }
    }

    // ── GET: list quizzes for a course ────────────────────────────────────────
    if ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'list') {
        $course_id = (int) ($_GET['course_id'] ?? 0);
        if (!$course_id) {
            echo json_encode(['success' => false, 'message' => 'Course required.']);
            exit;
        }
        if (!teacherOwnsCourse($conn, $teacher_id, $course_id)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Not assigned to this course.']);
            exit;
        }
        $rows = [];
        $res = $conn->query(
            "SELECT q.id, q.course_id, q.title, q.description, q.time_limit, q.max_score, q.passing_score,
                    q.open_at, q.close_at, q.quiz_type, q.file_path, q.status, q.show_results, q.show_correct_answers,
                    q.points_per_question,
                    DATE_FORMAT(q.created_at,'%b %d, %Y') AS created_at,
                    (SELECT COUNT(*) FROM quiz_questions WHERE quiz_id=q.id) AS question_count,
                    (SELECT COUNT(*) FROM quiz_attempts WHERE quiz_id=q.id) AS attempt_count
            FROM quizzes q
            WHERE q.course_id = $course_id AND " . scopeTeacherOwned($conn, 'q', 'quizzes', $teacher_id) . "
            ORDER BY q.created_at DESC"
        );
        while ($r = $res->fetch_assoc())
            $rows[] = $r;
        echo json_encode(['success' => true, 'data' => $rows]);
        exit;
    }

    // ── GET: get quiz with questions ──────────────────────────────────────────
    if ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'get') {
        $quiz_id = (int) ($_GET['id'] ?? 0);
        if (!teacherQuizCourse($conn, $teacher_id, $quiz_id)) denyQuiz();
        $quiz = $conn->query("SELECT * FROM quizzes WHERE id=$quiz_id")->fetch_assoc();
        if (!$quiz) {
            echo json_encode(['success' => false, 'message' => 'Not found.']);
            exit;
        }
        $questions = [];
        $qRes = $conn->query("SELECT * FROM quiz_questions WHERE quiz_id=$quiz_id ORDER BY id");
        while ($q = $qRes->fetch_assoc()) {
            $choices = [];
            $cRes = $conn->query("SELECT * FROM quiz_choices WHERE question_id={$q['id']} ORDER BY id");
            while ($c = $cRes->fetch_assoc())
                $choices[] = $c;
            $q['choices'] = $choices;
            $questions[] = $q;
        }
        $quiz['questions'] = $questions;
        echo json_encode(['success' => true, 'data' => $quiz]);
        exit;
    }

    // ── POST (multipart or JSON): create quiz ─────────────────────────────────
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'create') {
        $isMultipart = !empty($_POST);

        if ($isMultipart) {
            $course_id = (int) ($_POST['course_id'] ?? 0);
            $title = trim($_POST['title'] ?? '');
            $time_limit = ($_POST['time_limit'] ?? '') !== '' ? (int) $_POST['time_limit'] : null;
            $quiz_type = 'file';
            if ($err = quizRequired($_POST['description'] ?? '', $_POST['max_score'] ?? '', $_POST['passing_score'] ?? '', $_POST['open_at'] ?? '', $_POST['close_at'] ?? '', null, $req)) quizFail($err);
            ['description' => $description, 'max_score' => $max_score, 'passing_score' => $passing_score, 'open_at' => $open_at, 'close_at' => $close_at] = $req;

            if (!$course_id || !$title) {
                echo json_encode(['success' => false, 'message' => 'Course and title required.']);
                exit;
            }
            if ($err = validateQuizInput($title, $max_score, $open_at, $close_at)) {
                echo json_encode(['success' => false, 'message' => $err]);
                exit;
            }
            if (!teacherOwnsCourse($conn, $teacher_id, $course_id)) {
                echo json_encode(['success' => false, 'message' => 'Not assigned to this course.']);
                exit;
            }

            $file_path = null;
            if (!empty($_FILES['file']['name'])) {
                $upload_dir = '../uploads/quizzes/';
                if (!is_dir($upload_dir))
                    mkdir($upload_dir, 0775, true);
                $fileErr = elmsCheckTeacherFile($_FILES['file'], $ext);
                if ($fileErr) {
                    echo json_encode(['success' => false, 'message' => $fileErr]);
                    exit;
                }
                $filename = elmsUploadName('quiz_', $_FILES['file']['name'], $ext);
                if (!move_uploaded_file($_FILES['file']['tmp_name'], $upload_dir . $filename)) {
                    echo json_encode(['success' => false, 'message' => 'File upload failed.']);
                    exit;
                }
                $file_path = 'uploads/quizzes/' . $filename;
            }

            $stmt = $conn->prepare(
                "INSERT INTO quizzes (course_id, title, description, time_limit, max_score, passing_score, open_at, close_at, quiz_type, file_path)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
            );
            $stmt->bind_param('issiddssss', $course_id, $title, $description, $time_limit, $max_score, $passing_score, $open_at, $close_at, $quiz_type, $file_path);
            if ($stmt->execute()) {
                $newId = (int) $conn->insert_id;
                if (scopeHasOwner($conn, 'quizzes')) $conn->query("UPDATE quizzes SET teacher_id = $teacher_id WHERE id = $newId");
                mlApply($conn, 'quizzes', $newId, $course_id, quizPostedModule(), true, $teacher_id);
                echo json_encode(['success' => true, 'message' => 'Quiz created.', 'id' => $newId]);
            } else {
                error_log('[quizzes.php] ' . $conn->error); echo json_encode(['success' => false, 'message' => 'Could not save. Please try again.']);
            }
            exit;
        }

        // JSON (question builder)
        $body = json_decode(file_get_contents('php://input'), true);
        if (!is_array($body)) quizFail('Invalid request.');
        $course_id = (int) ($body['course_id'] ?? 0);
        $title = quizStr($body['title'] ?? '');
        $time_limit = !empty($body['time_limit']) ? (int) $body['time_limit'] : null;
        if ($err = quizRequired($body['description'] ?? '', $body['max_score'] ?? '', $body['passing_score'] ?? '', $body['open_at'] ?? '', $body['close_at'] ?? '', null, $req)) quizFail($err);
        ['description' => $description, 'max_score' => $max_score, 'passing_score' => $passing_score, 'open_at' => $open_at, 'close_at' => $close_at] = $req;
        $quiz_type = 'questions';
        $question_count = !empty($body['question_count']) ? (int) $body['question_count'] : null;
        $points_per_question = !empty($body['points_per_question']) ? (float) $body['points_per_question'] : null;
        $show_results = !empty($body['show_results']) ? 1 : 0;
        $show_correct_answers = !empty($body['show_correct_answers']) ? 1 : 0;
        $questions = $body['questions'] ?? [];
        $wantPublish = ($body['status'] ?? 'Draft') === 'Published';

        if (!$course_id || !$title) {
            echo json_encode(['success' => false, 'message' => 'Course and title required.']);
            exit;
        }
        if ($err = validateQuizInput($title, $max_score, $open_at, $close_at)) {
            echo json_encode(['success' => false, 'message' => $err]);
            exit;
        }
        if (!teacherOwnsCourse($conn, $teacher_id, $course_id)) {
            echo json_encode(['success' => false, 'message' => 'Not assigned to this course.']);
            exit;
        }
        // Double click / retry: same quiz title from this teacher in the last 30 seconds
        $dupQ = $conn->prepare("SELECT id, status FROM quizzes WHERE course_id = ? AND title = ? AND created_at >= NOW() - INTERVAL 30 SECOND LIMIT 1");
        $dupQ->bind_param('is', $course_id, $title);
        $dupQ->execute();
        if ($d = $dupQ->get_result()->fetch_assoc()) {
            echo json_encode(['success' => true, 'message' => 'Quiz already saved.', 'id' => (int) $d['id'], 'status' => $d['status'], 'duplicate' => true]);
            exit;
        }

        // Validate every question slot is properly filled in before allowing Publish.
        // (A Draft can be saved with empty/incomplete slots — the teacher finishes later.)
        $publishError = validateQuestionsForPublish($questions, $question_count);
        $status = ($wantPublish && !$publishError) ? 'Published' : 'Draft';
        if ($wantPublish && $publishError) {
            echo json_encode(['success' => false, 'message' => $publishError, 'saved_as_draft' => false]);
            exit;
        }

        $conn->begin_transaction();
        try {
            $stmt = $conn->prepare(
                "INSERT INTO quizzes (course_id, title, description, time_limit, max_score, passing_score,
                    open_at, close_at, quiz_type, status, question_count, points_per_question, show_results, show_correct_answers)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
            );
            $stmt->bind_param(
                'issiddssssidii',
                $course_id, $title, $description, $time_limit, $max_score, $passing_score,
                $open_at, $close_at, $quiz_type, $status, $question_count, $points_per_question,
                $show_results, $show_correct_answers
            );
            $stmt->execute();
            $quiz_id = $conn->insert_id;
            if (scopeHasOwner($conn, 'quizzes')) $conn->query("UPDATE quizzes SET teacher_id = $teacher_id WHERE id = $quiz_id");

            saveQuizQuestions($conn, $quiz_id, $questions);

            $conn->commit();
            mlApply($conn, 'quizzes', (int) $quiz_id, $course_id, quizPostedModule(), true, $teacher_id);
            echo json_encode(['success' => true, 'message' => $status === 'Published' ? 'Quiz published!' : 'Saved as draft.', 'id' => $quiz_id, 'status' => $status]);
        } catch (Exception $e) {
            $conn->rollback();
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    // ── POST: update quiz (settings only) ────────────────────────────────────
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'update') {
        $isMultipart = !empty($_POST);

        $id = (int) (($_POST['id'] ?? 0) ?: (json_decode(file_get_contents('php://input'), true)['id'] ?? 0));
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'Quiz ID required.']);
            exit;
        }
        if (!teacherQuizCourse($conn, $teacher_id, $id)) denyQuiz();

        if ($isMultipart) {
            $title = trim($_POST['title'] ?? '');
            $time_limit = ($_POST['time_limit'] ?? '') !== '' ? (int) $_POST['time_limit'] : null;
            if ($err = quizRequired($_POST['description'] ?? '', $_POST['max_score'] ?? '', $_POST['passing_score'] ?? '', $_POST['open_at'] ?? '', $_POST['close_at'] ?? '', ($oldQ = quizOld($conn, $id)), $req)) quizFail($err);
            ['description' => $description, 'max_score' => $max_score, 'passing_score' => $passing_score, 'open_at' => $open_at, 'close_at' => $close_at] = $req;
            if ($err = validateQuizInput($title, $max_score, $open_at, $close_at)) {
                echo json_encode(['success' => false, 'message' => $err]);
                exit;
            }

            $file_path = null;
            if (!empty($_FILES['file']['name'])) {
                $upload_dir = '../uploads/quizzes/';
                if (!is_dir($upload_dir))
                    mkdir($upload_dir, 0775, true);
                $fileErr = elmsCheckTeacherFile($_FILES['file'], $ext);
                if ($fileErr) {
                    echo json_encode(['success' => false, 'message' => $fileErr]);
                    exit;
                }
                $old = $conn->query("SELECT file_path FROM quizzes WHERE id=$id")->fetch_assoc();
                if ($old && $old['file_path'] && file_exists('../' . $old['file_path']))
                    unlink('../' . $old['file_path']);
                $filename = elmsUploadName('quiz_', $_FILES['file']['name'], $ext);
                move_uploaded_file($_FILES['file']['tmp_name'], $upload_dir . $filename);
                $file_path = 'uploads/quizzes/' . $filename;
            }

            if ($file_path) {
                $stmt = $conn->prepare("UPDATE quizzes SET title=?, description=?, time_limit=?, max_score=?, passing_score=?, open_at=?, close_at=?, file_path=? WHERE id=?");
                $stmt->bind_param('ssiddsssi', $title, $description, $time_limit, $max_score, $passing_score, $open_at, $close_at, $file_path, $id);
            } else {
                $stmt = $conn->prepare("UPDATE quizzes SET title=?, description=?, time_limit=?, max_score=?, passing_score=?, open_at=?, close_at=? WHERE id=?");
                $stmt->bind_param('ssiddssi', $title, $description, $time_limit, $max_score, $passing_score, $open_at, $close_at, $id);
            }
            $stmt->execute();
            // Subject and module cannot be changed when editing: the quiz keeps both (no mlApply here)
            quizPassingEvent($conn, $id, $oldQ);
            echo json_encode(['success' => true, 'message' => 'Quiz updated.']);
            exit;
        }

        // JSON update (also used to edit a Draft's questions, or publish it)
        $body = json_decode(file_get_contents('php://input'), true);
        if (!is_array($body)) quizFail('Invalid request.');
        $title = quizStr($body['title'] ?? '');
        $time_limit = !empty($body['time_limit']) ? (int) $body['time_limit'] : null;
        if ($err = quizRequired($body['description'] ?? '', $body['max_score'] ?? '', $body['passing_score'] ?? '', $body['open_at'] ?? '', $body['close_at'] ?? '', ($oldQ = quizOld($conn, $id)), $req)) quizFail($err);
        ['description' => $description, 'max_score' => $max_score, 'passing_score' => $passing_score, 'open_at' => $open_at, 'close_at' => $close_at] = $req;
        if ($err = validateQuizInput($title, $max_score, $open_at, $close_at)) {
            echo json_encode(['success' => false, 'message' => $err]);
            exit;
        }
        $question_count = !empty($body['question_count']) ? (int) $body['question_count'] : null;
        $points_per_question = !empty($body['points_per_question']) ? (float) $body['points_per_question'] : null;
        $show_results = !empty($body['show_results']) ? 1 : 0;
        $show_correct_answers = !empty($body['show_correct_answers']) ? 1 : 0;
        $questions = $body['questions'] ?? null; // null = don't touch questions (settings-only edit)
        $wantPublish = ($body['status'] ?? '') === 'Published';

        $status = null;
        if ($questions !== null) {
            $publishError = $wantPublish ? validateQuestionsForPublish($questions, $question_count) : null;
            if ($wantPublish && $publishError) {
                echo json_encode(['success' => false, 'message' => $publishError]);
                exit;
            }
            $status = $wantPublish ? 'Published' : 'Draft';
        } elseif ($wantPublish) {
            // Publishing without resending questions — validate against what's already saved.
            $existing = [];
            $eRes = $conn->query("SELECT id, question_text, question_type FROM quiz_questions WHERE quiz_id=$id");
            while ($eq = $eRes->fetch_assoc()) {
                $eq['choices'] = [];
                $cRes = $conn->query("SELECT choice_text, is_correct FROM quiz_choices WHERE question_id={$eq['id']}");
                while ($ec = $cRes->fetch_assoc()) $eq['choices'][] = $ec;
                if ($eq['question_type'] === 'essay') {
                    $idRes = $conn->query("SELECT identification_answer FROM quiz_questions WHERE id={$eq['id']}")->fetch_assoc();
                    $eq['identification_answer'] = $idRes['identification_answer'] ?? '';
                }
                $existing[] = $eq;
            }
            $publishError = validateQuestionsForPublish($existing, $question_count);
            if ($publishError) {
                echo json_encode(['success' => false, 'message' => $publishError]);
                exit;
            }
            $status = 'Published';
        }

        $conn->begin_transaction();
        try {
            if ($status !== null) {
                $stmt = $conn->prepare(
                    "UPDATE quizzes SET title=?, description=?, time_limit=?, max_score=?, passing_score=?,
                        open_at=?, close_at=?, status=?, question_count=?, points_per_question=?,
                        show_results=?, show_correct_answers=? WHERE id=?"
                );
                $stmt->bind_param(
                    'ssiddsssidiii',
                    $title, $description, $time_limit, $max_score, $passing_score,
                    $open_at, $close_at, $status, $question_count, $points_per_question,
                    $show_results, $show_correct_answers, $id
                );
            } else {
                $stmt = $conn->prepare(
                    "UPDATE quizzes SET title=?, description=?, time_limit=?, max_score=?, passing_score=?,
                        open_at=?, close_at=?, question_count=?, points_per_question=?,
                        show_results=?, show_correct_answers=? WHERE id=?"
                );
                $stmt->bind_param(
                    'ssiddssidiii',
                    $title, $description, $time_limit, $max_score, $passing_score,
                    $open_at, $close_at, $question_count, $points_per_question,
                    $show_results, $show_correct_answers, $id
                );
            }
            $stmt->execute();

            if ($questions !== null) {
                saveQuizQuestions($conn, $id, $questions);
                // Questions have no database trigger: tell open student pages (live
                // updates, includes/live.php) that this published quiz changed.
                try {
                    if ($conn->query("SHOW TABLES LIKE 'sync_events'")->num_rows) {
                        $conn->query("INSERT INTO sync_events (kind, course_id, section, owner_id, user_id, ref_id, audience, title)
                                      SELECT 'quiz.content', course_id, NULL, " . (scopeHasOwner($conn, 'quizzes') ? 'teacher_id' : 'NULL') . ", NULL, id, 'all', title
                                      FROM quizzes WHERE id = $id AND status = 'Published'");
                    }
                } catch (Throwable $e) {
                    error_log('[quizzes] live event: ' . $e->getMessage());
                }
            }

            $conn->commit();
            // Subject and module cannot be changed when editing: the quiz keeps both (no mlApply here)
            quizPassingEvent($conn, $id, $oldQ);
            echo json_encode(['success' => true, 'message' => $status === 'Published' ? 'Quiz published!' : 'Quiz updated.']);
        } catch (Exception $e) {
            $conn->rollback();
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    // ── DELETE: remove quiz ───────────────────────────────────────────────────
    if ($_SERVER['REQUEST_METHOD'] === 'DELETE' && $action === 'delete') {
        $id = (int) ($_GET['id'] ?? 0);
        if (!teacherQuizCourse($conn, $teacher_id, $id)) denyQuiz();
        $old = $conn->query("SELECT file_path FROM quizzes WHERE id=$id")->fetch_assoc();
        if ($old && $old['file_path'] && file_exists('../' . $old['file_path']))
            unlink('../' . $old['file_path']);
        $conn->query("DELETE qc FROM quiz_choices qc JOIN quiz_questions qq ON qq.id=qc.question_id WHERE qq.quiz_id=$id");
        $conn->query("DELETE FROM quiz_questions WHERE quiz_id=$id");
        $conn->query("DELETE FROM quiz_attempts WHERE quiz_id=$id");
        $conn->query("DELETE FROM quizzes WHERE id=$id");
        echo json_encode(['success' => true, 'message' => 'Quiz deleted.']);
        exit;
    }

    // ── GET: results for a quiz ───────────────────────────────────────────────
    if ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'results') {
        $quiz_id = (int) ($_GET['id'] ?? 0);
        $quizCourse = teacherQuizCourse($conn, $teacher_id, $quiz_id);
        if (!$quizCourse) denyQuiz();
        $rows = [];
        $res = $conn->query(
            "SELECT qa.id AS attempt_id, qa.student_id, qa.score, qa.remarks,
                    qa.started_at, qa.finished_at, qa.submitted_at, qa.status,
                    qa.file_path AS student_file_path,
                    u.first_name, u.last_name, u.school_id, u.section_dept,
                    q.max_score, q.quiz_type, q.passing_score,
                    (SELECT COUNT(*) FROM quiz_responses qr WHERE qr.attempt_id = qa.id AND qr.needs_grading = 1) AS essays_to_grade
            FROM quiz_attempts qa
            JOIN users u ON u.id = qa.student_id
            JOIN quizzes q ON q.id = qa.quiz_id
            WHERE qa.quiz_id = $quiz_id
              AND EXISTS (SELECT 1 FROM teacher_assignments ta
                          WHERE ta.teacher_id = $teacher_id AND ta.course_id = q.course_id AND ta.section = u.section_dept)
            ORDER BY qa.submitted_at DESC"
        );
        while ($r = $res->fetch_assoc()) {
            // Rename to file_path so JS can use r.file_path as before
            $r['file_path'] = $r['student_file_path'];
            unset($r['student_file_path']);
            if ($r['score'] !== null && (float) $r['max_score'] > 0) {
                $r['percentage'] = round((float) $r['score'] / (float) $r['max_score'] * 100, 1);
                $r['passed'] = $r['passing_score'] !== null ? ($r['percentage'] >= (float) $r['passing_score']) : null;
            } else {
                $r['percentage'] = null;
                $r['passed'] = null;
            }
            $rows[] = $r;
        }
        echo json_encode(['success' => true, 'data' => $rows]);
        exit;
    }

    // ── GET: one student's per-question answers for a quiz ───────────────────
    if ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'attempt_detail') {
        $attempt_id = (int) ($_GET['attempt_id'] ?? 0);
        $ownAttempt = $conn->query("SELECT qa.quiz_id FROM quiz_attempts qa WHERE qa.id = $attempt_id")->fetch_assoc();
        if (!$ownAttempt || !teacherQuizCourse($conn, $teacher_id, (int) $ownAttempt['quiz_id'])) denyQuiz();
        $rows = [];
        $res = $conn->query(
            "SELECT qq.id AS question_id, qq.question_text, qq.question_type, qq.points, qq.identification_answer,
                    qr.answer_text, qr.choice_id, qr.is_correct, qr.points_earned, qr.needs_grading,
                    qc.choice_text AS chosen_choice_text
             FROM quiz_questions qq
             LEFT JOIN quiz_responses qr ON qr.question_id = qq.id AND qr.attempt_id = $attempt_id
             LEFT JOIN quiz_choices qc ON qc.id = qr.choice_id
             WHERE qq.quiz_id = (SELECT quiz_id FROM quiz_attempts WHERE id = $attempt_id)
             ORDER BY qq.id"
        );
        while ($r = $res->fetch_assoc()) {
            if ($r['question_type'] !== 'essay' && $r['question_type'] !== 'long_essay') {
                $choices = [];
                $cRes = $conn->query("SELECT id, choice_text, is_correct FROM quiz_choices WHERE question_id = {$r['question_id']} ORDER BY id");
                while ($c = $cRes->fetch_assoc()) $choices[] = $c;
                $r['choices'] = $choices;
            }
            $rows[] = $r;
        }
        echo json_encode(['success' => true, 'data' => $rows]);
        exit;
    }

    // ── POST: teacher grades the essay answers of one attempt ────────────────
    //  Body: { attempt_id, scores: { question_id: points }, remarks? }
    //  Once every essay of the attempt is graded, the final score is computed
    //  (auto-checked points + essay points, scaled to max_score) and the attempt is 'Graded'.
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'grade_essays') {
        $body = json_decode(file_get_contents('php://input'), true) ?: [];
        $aid = (int) ($body['attempt_id'] ?? 0);
        $att = $conn->query("SELECT qa.id, qa.quiz_id, qa.status, q.max_score, q.passing_score FROM quiz_attempts qa JOIN quizzes q ON q.id = qa.quiz_id WHERE qa.id = $aid")->fetch_assoc();
        if (!$att || !teacherQuizCourse($conn, $teacher_id, (int) $att['quiz_id'])) denyQuiz();
        if ($att['status'] === 'In Progress') quizFail('The student is still taking this quiz.');
        $scores = is_array($body['scores'] ?? null) ? $body['scores'] : [];
        $qid = (int) $att['quiz_id'];
        $conn->begin_transaction();
        try {
            foreach ($scores as $questionId => $val) {
                $questionId = (int) $questionId;
                if ($val === '' || $val === null) continue;   // left blank: still to grade
                $q = $conn->query("SELECT points FROM quiz_questions WHERE id = $questionId AND quiz_id = $qid AND question_type = 'long_essay'")->fetch_assoc();
                if (!$q) continue;
                $max = (float) $q['points'] > 0 ? (float) $q['points'] : 1.0;
                $pts = (float) $val;
                if (!is_numeric($val) || $pts < 0 || $pts > $max) {
                    $conn->rollback();
                    quizFail("Essay points must be between 0 and " . rtrim(rtrim(number_format($max, 2, '.', ''), '0'), '.') . '.');
                }
                $ok = $pts >= $max ? 1 : 0;
                $up = $conn->prepare("UPDATE quiz_responses SET points_earned = ?, is_correct = ?, needs_grading = 0 WHERE attempt_id = ? AND question_id = ?");
                $up->bind_param('diii', $pts, $ok, $aid, $questionId);
                $up->execute();
                if ($up->affected_rows < 1 && !$conn->query("SELECT 1 FROM quiz_responses WHERE attempt_id = $aid AND question_id = $questionId")->num_rows) {
                    // no saved row (answer left blank) — store the given points anyway
                    $ins = $conn->prepare("INSERT INTO quiz_responses (attempt_id, question_id, is_correct, points_earned, needs_grading) VALUES (?, ?, ?, ?, 0)");
                    $ins->bind_param('iiid', $aid, $questionId, $ok, $pts);
                    $ins->execute();
                }
            }
            if (array_key_exists('remarks', $body)) {
                $rm = trim((string) $body['remarks']);
                $st = $conn->prepare("UPDATE quiz_attempts SET remarks = ? WHERE id = ?");
                $st->bind_param('si', $rm, $aid);
                $st->execute();
            }
            $left = (int) $conn->query("SELECT COUNT(*) AS n FROM quiz_responses WHERE attempt_id = $aid AND needs_grading = 1")->fetch_assoc()['n'];
            $score = null;
            if ($left === 0) {
                $score = qaRecomputeScore($conn, $aid);
                $conn->query("UPDATE quiz_attempts SET score = $score, status = 'Graded', finished_at = COALESCE(finished_at, NOW()) WHERE id = $aid");
            }
            $conn->commit();
        } catch (Throwable $e) {
            $conn->rollback();
            error_log('[quizzes grade_essays] ' . $e->getMessage());
            quizFail('Could not save the essay grades.');
        }
        echo json_encode([
            'success' => true,
            'remaining' => $left,
            'score' => $score,
            'message' => $left === 0 ? "Essays graded. Final score: $score / " . (float) $att['max_score'] : "Saved. $left essay" . ($left === 1 ? '' : 's') . ' still to grade.',
        ]);
        exit;
    }

    // ── POST: save grades for quiz attempts (file-type) ───────────────────────
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'grade') {
        $body = json_decode(file_get_contents('php://input'), true);
        $grades = $body['grades'] ?? [];
        $skipped = 0;
        foreach ($grades as $g) {
            $aid = (int) $g['attempt_id'];
            // Only attempts on this teacher's own quizzes, and scores within 0..max
            $own = $conn->query("SELECT q.max_score FROM quiz_attempts qa JOIN quizzes q ON q.id = qa.quiz_id
                                 JOIN teacher_assignments ta ON ta.course_id = q.course_id AND ta.teacher_id = " . (int) $_SESSION['user_id'] . "
                                 WHERE qa.id = $aid LIMIT 1")->fetch_assoc();
            if (!$own) { $skipped++; continue; }
            $score = (isset($g['score']) && $g['score'] !== '' && $g['score'] !== null) ? (float) $g['score'] : null;
            if ($score !== null && ($score < 0 || $score > (float) $own['max_score'])) { $skipped++; continue; }
            $remarks = $conn->real_escape_string($g['remarks'] ?? '');
            if ($score !== null) {
                $conn->query("UPDATE quiz_attempts SET score=$score, remarks='$remarks', status='Graded', finished_at=COALESCE(finished_at,NOW()) WHERE id=$aid");
            } else {
                $conn->query("UPDATE quiz_attempts SET remarks='$remarks' WHERE id=$aid");
            }
        }
        echo json_encode(['success' => true, 'message' => $skipped ? "Grades saved. $skipped skipped (not your quiz, or score out of range)." : 'Grades saved.']);
        exit;
    }

    echo json_encode(['success' => false, 'message' => 'Invalid request.']);