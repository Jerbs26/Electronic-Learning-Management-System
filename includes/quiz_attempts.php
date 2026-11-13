<?php
// ============================================================
//  Arandia College eLMS — Online quiz attempts (no exit, no resume)
//  File: includes/quiz_attempts.php
//
//  Rule: once a student starts an online quiz they cannot leave it and come
//  back later. If they leave (close the window, go back, refresh, close the
//  tab, lose the connection…) the attempt is submitted with the answers they
//  already gave, and the score is only the correct ones.
//
//  How:
//   · every answer is saved to the server as it is picked (api/student_quiz.php
//     ?action=save), which also marks the attempt as "still active";
//   · when the page is left, the browser submits right away (sendBeacon);
//   · if that could not happen (browser crashed, no internet), an attempt with
//     no activity for QA_STALE_SECONDS — or past its time limit — is submitted
//     by the server the next time the student opens any page / the quiz.
//  Grading always happens on the server (qaGrade), never in the browser.
// ============================================================

if (!defined('QA_STALE_SECONDS')) define('QA_STALE_SECONDS', 90);

if (!function_exists('qaEnsureSchema')) {

    /** quiz_attempts.last_active_at (database/migration_quiz_no_exit.sql) — added automatically if missing. */
    function qaEnsureSchema(mysqli $conn): bool
    {
        static $ok = null;
        if ($ok !== null) return $ok;
        qaEnsureEssaySchema($conn);
        try {
            $r = $conn->query("SHOW COLUMNS FROM quiz_attempts LIKE 'last_active_at'");
            if ($r && $r->num_rows) return $ok = true;
            $ok = (bool) $conn->query("ALTER TABLE quiz_attempts ADD COLUMN last_active_at DATETIME NULL DEFAULT NULL AFTER started_at");
        } catch (Throwable $e) {
            error_log('[quiz_attempts] ' . $e->getMessage());
            $ok = false;
        }
        return $ok;
    }

    /**
     * Essay questions (database/migration_quiz_essay.sql) — added automatically if missing:
     *  · quiz_questions.question_type gets 'long_essay' (shown as "Essay"; the old
     *    'essay' value is the auto-checked Identification type and stays as it is)
     *  · quiz_responses.answer_text becomes TEXT (essays are longer than 500 characters)
     *  · quiz_responses.needs_grading = 1 while the teacher has not graded that essay yet
     */
    function qaEnsureEssaySchema(mysqli $conn): bool
    {
        static $ok = null;
        if ($ok !== null) return $ok;
        try {
            $col = $conn->query("SHOW COLUMNS FROM quiz_questions LIKE 'question_type'")->fetch_assoc();
            if ($col && strpos((string) $col['Type'], 'long_essay') === false) {
                $conn->query("ALTER TABLE quiz_questions MODIFY question_type ENUM('multiple_choice','true_false','essay','long_essay') NOT NULL DEFAULT 'multiple_choice'");
            }
            $col = $conn->query("SHOW COLUMNS FROM quiz_responses LIKE 'answer_text'")->fetch_assoc();
            if ($col && stripos((string) $col['Type'], 'varchar') !== false) {
                $conn->query("ALTER TABLE quiz_responses MODIFY answer_text TEXT NULL DEFAULT NULL");
            }
            $r = $conn->query("SHOW COLUMNS FROM quiz_responses LIKE 'needs_grading'");
            if (!$r || !$r->num_rows) {
                $conn->query("ALTER TABLE quiz_responses ADD COLUMN needs_grading TINYINT(1) NOT NULL DEFAULT 0 AFTER points_earned");
            }
            $ok = true;
        } catch (Throwable $e) {
            error_log('[quiz_attempts] essay schema: ' . $e->getMessage());
            $ok = false;
        }
        return $ok;
    }

    /**
     * Identification answers are compared without caring about UPPER / lower case,
     * accent marks (é = e, ñ = n), extra spaces, non-breaking or invisible spaces:
     * "MANILA", " manila " and "Manila" all match "Manila";
     * "Jose Rizal" matches "José Rizal"; "Penafrancia" matches "Peñafrancia".
     */
    /** Identification answers are a word or a short name, never a sentence. */
    if (!defined('QA_ID_MAX_WORDS')) define('QA_ID_MAX_WORDS', 4);
    if (!defined('QA_ID_MAX_CHARS')) define('QA_ID_MAX_CHARS', 60);
    function qaIdAnswerTooLong($s): bool
    {
        $s = trim((string) $s);
        $words = preg_split('/\s+/u', $s, -1, PREG_SPLIT_NO_EMPTY);
        return count($words) > QA_ID_MAX_WORDS || mb_strlen($s) > QA_ID_MAX_CHARS;
    }

    function qaNormalizeAnswer($s): string
    {
        $s = (string) $s;
        if (class_exists('Normalizer')) $s = Normalizer::normalize($s, Normalizer::FORM_C) ?: $s;   // é typed two ways = same é
        $s = preg_replace('/[\x{200B}-\x{200D}\x{FEFF}]/u', '', $s) ?? $s;   // invisible characters
        $s = preg_replace('/[\s\x{00A0}]+/u', ' ', $s) ?? $s;                  // any run of spaces → one space
        $s = trim($s);
        $s = function_exists('mb_strtolower') ? mb_strtolower($s, 'UTF-8') : strtolower($s);
        // Remove accent marks: split each letter from its mark, then drop the marks
        if (class_exists('Normalizer')) {
            $d = Normalizer::normalize($s, Normalizer::FORM_D);
            if ($d !== false) $s = preg_replace('/\p{Mn}+/u', '', $d) ?? $s;
        } else {   // server without the intl extension: the common letters by hand
            $s = strtr($s, [
                'á' => 'a', 'à' => 'a', 'â' => 'a', 'ä' => 'a', 'ã' => 'a', 'å' => 'a',
                'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e',
                'í' => 'i', 'ì' => 'i', 'î' => 'i', 'ï' => 'i',
                'ó' => 'o', 'ò' => 'o', 'ô' => 'o', 'ö' => 'o', 'õ' => 'o',
                'ú' => 'u', 'ù' => 'u', 'û' => 'u', 'ü' => 'u',
                'ñ' => 'n', 'ç' => 'c', 'ý' => 'y', 'ÿ' => 'y',
            ]);
        }
        return $s;
    }

    /**
     * Final score of an attempt from its stored per-question points, scaled to the quiz max_score.
     * Used after the teacher grades the essays.
     */
    function qaRecomputeScore(mysqli $conn, int $attemptId): ?float
    {
        $row = $conn->query(
            "SELECT q.max_score,
                    (SELECT SUM(IF(qq.points > 0, qq.points, 1)) FROM quiz_questions qq WHERE qq.quiz_id = q.id) AS max_pts,
                    (SELECT COALESCE(SUM(qr.points_earned), 0) FROM quiz_responses qr WHERE qr.attempt_id = qa.id) AS earned
             FROM quiz_attempts qa JOIN quizzes q ON q.id = qa.quiz_id WHERE qa.id = $attemptId"
        )->fetch_assoc();
        if (!$row) return null;
        $maxPts = (float) $row['max_pts'];
        return $maxPts > 0 ? round((float) $row['earned'] / $maxPts * (float) $row['max_score'], 2) : 0.0;
    }

    /**
     * Grade $answers for one attempt and store them (quiz_responses).
     * $answers: [question_id => ['choice_id' => n] | ['text' => '...']]
     * Unanswered questions are stored as wrong (0 points).
     * Essay questions ('long_essay') are NOT auto-graded: the answer is stored with
     * 0 points and needs_grading = 1 until the teacher grades it (a blank essay is
     * simply 0 points, nothing to grade).
     * Returns [earned points, total points, breakdown, essays waiting for the teacher].
     */
    function qaGrade(mysqli $conn, int $attemptId, int $quizId, array $answers): array
    {
        qaEnsureEssaySchema($conn);
        $questions = [];
        $qRes = $conn->query("SELECT id, question_type, points, identification_answer FROM quiz_questions WHERE quiz_id = $quizId");
        while ($qRes && $q = $qRes->fetch_assoc()) $questions[(int) $q['id']] = $q;

        $earned = 0.0; $maxPts = 0.0; $breakdown = []; $pending = 0;
        $rs = $conn->prepare(
            "INSERT INTO quiz_responses (attempt_id, question_id, choice_id, answer_text, is_correct, points_earned, needs_grading)
             VALUES (?, ?, ?, ?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE choice_id=VALUES(choice_id), answer_text=VALUES(answer_text),
                is_correct=VALUES(is_correct), points_earned=VALUES(points_earned), needs_grading=VALUES(needs_grading)"
        );
        $normalize = 'qaNormalizeAnswer';
        foreach ($questions as $qId => $q) {
            // Older quizzes saved questions with 0 points: each counts as 1 point
            $pts = (float) $q['points'] > 0 ? (float) $q['points'] : 1.0;
            $maxPts += $pts;
            $ans = $answers[$qId] ?? $answers[(string) $qId] ?? null;
            $ok = false; $got = 0.0; $choiceId = null; $text = null; $needs = 0;
            if ($q['question_type'] === 'long_essay') {
                // Teacher grades this one by hand
                $text = trim((string) (is_array($ans) ? ($ans['text'] ?? '') : ($ans ?? '')));
                if ($text === '') $text = null;
                else { $needs = 1; $pending++; }
            } elseif ($q['question_type'] === 'essay') {
                $text = trim((string) (is_array($ans) ? ($ans['text'] ?? '') : ($ans ?? '')));
                if ($text !== '' && $normalize($text) === $normalize($q['identification_answer'])) { $ok = true; $got = $pts; }
                if ($text === '') $text = null;
            } else {
                $choiceId = (int) (is_array($ans) ? ($ans['choice_id'] ?? 0) : ($ans ?? 0));
                if ($choiceId > 0) {
                    $c = $conn->query("SELECT is_correct FROM quiz_choices WHERE id = $choiceId AND question_id = $qId LIMIT 1")->fetch_assoc();
                    if (!$c) $choiceId = null;   // not a choice of this question
                    elseif ((int) $c['is_correct'] === 1) { $ok = true; $got = $pts; }
                } else {
                    $choiceId = null;
                }
            }
            $earned += $got;
            $breakdown[] = ['question_id' => $qId, 'is_correct' => $ok] + ($needs ? ['pending' => true] : []);
            $okInt = $ok ? 1 : 0;
            $rs->bind_param('iiisidi', $attemptId, $qId, $choiceId, $text, $okInt, $got, $needs);
            $rs->execute();
        }
        $rs->close();
        return [$earned, $maxPts, $breakdown, $pending];
    }

    /** Answers already saved for an attempt, in the same shape the browser sends. */
    function qaSavedAnswers(mysqli $conn, int $attemptId): array
    {
        $out = [];
        $res = $conn->query("SELECT question_id, choice_id, answer_text FROM quiz_responses WHERE attempt_id = $attemptId");
        while ($res && $r = $res->fetch_assoc()) {
            if ($r['choice_id'] !== null) $out[(int) $r['question_id']] = ['choice_id' => (int) $r['choice_id']];
            elseif ($r['answer_text'] !== null) $out[(int) $r['question_id']] = ['text' => $r['answer_text']];
        }
        return $out;
    }

    /**
     * Submit an In Progress attempt. $answers = null → use what was saved so far.
     * $att needs: id, quiz_id, max_score, passing_score.
     * Returns [score, percentage, passed|null, breakdown, pending] or null if it was already submitted.
     * When essays wait for the teacher ($pending > 0) the attempt stays 'Submitted' with
     * score NULL (not graded yet), and score/percentage returned are for the auto-checked part only.
     */
    function qaFinalize(mysqli $conn, array $att, ?array $answers = null): ?array
    {
        $id = (int) $att['id'];
        // Claim it first, so two requests (beacon + button) can never both submit it
        $conn->query("UPDATE quiz_attempts SET status = 'Submitted', submitted_at = NOW(), finished_at = NOW() WHERE id = $id AND status = 'In Progress'");
        if ($conn->affected_rows < 1) return null;
        if ($answers === null) $answers = qaSavedAnswers($conn, $id);
        [$earned, $maxPts, $breakdown, $pending] = qaGrade($conn, $id, (int) $att['quiz_id'], $answers);
        $max = (float) $att['max_score'];
        $score = $maxPts > 0 ? round($earned / $maxPts * $max, 2) : 0;
        $pct = $max > 0 ? round($score / $max * 100, 1) : 0;
        if ($pending > 0) {
            // Essays still to be graded by the teacher: no final score yet
            $conn->query("UPDATE quiz_attempts SET score = NULL WHERE id = $id");
            return [$score, $pct, null, $breakdown, $pending, $earned, $maxPts];
        }
        $passed = $att['passing_score'] !== null ? ($pct >= (float) $att['passing_score']) : null;
        $conn->query("UPDATE quiz_attempts SET score = $score WHERE id = $id");
        return [$score, $pct, $passed, $breakdown, 0, $earned, $maxPts];
    }

    /**
     * Submit this student's abandoned attempts: no activity for QA_STALE_SECONDS,
     * or past the quiz time limit (+1 minute), or the quiz has closed.
     * Returns how many were submitted.
     */
    function qaCloseAbandoned(mysqli $conn, int $studentId): int
    {
        if (!qaEnsureSchema($conn)) return 0;
        $stale = (int) QA_STALE_SECONDS;
        $res = $conn->query(
            "SELECT qa.id, qa.quiz_id, q.max_score, q.passing_score
             FROM quiz_attempts qa JOIN quizzes q ON q.id = qa.quiz_id
             WHERE qa.student_id = $studentId AND qa.status = 'In Progress'
               AND (COALESCE(qa.last_active_at, qa.started_at) < NOW() - INTERVAL $stale SECOND
                    OR (q.time_limit > 0 AND qa.started_at < NOW() - INTERVAL (q.time_limit + 1) MINUTE)
                    OR (q.close_at IS NOT NULL AND q.close_at < NOW()))"
        );
        $n = 0;
        while ($res && $a = $res->fetch_assoc()) {
            try { if (qaFinalize($conn, $a) !== null) $n++; }
            catch (Throwable $e) { error_log('[quiz_attempts] finalize ' . $a['id'] . ': ' . $e->getMessage()); }
        }
        return $n;
    }
}