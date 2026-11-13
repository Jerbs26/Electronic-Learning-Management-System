<?php
// ============================================================
//  Arandia College eLMS — AI Quiz Generator API
//  File: api/ai_quiz.php
//  Powered by: Groq API — generates draft quiz questions from the
//  lesson of the module picked in the form (plus an optional focus
//  the teacher types in), ready to drop into the Question Builder.
// ============================================================
session_start();
require_once __DIR__ . '/../includes/quiz_attempts.php';   // qaIdAnswerTooLong
require_once '../config/conn.php';
require_once '../includes/module_links.php';
require_once '../includes/ai_guard.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Teacher') {
    http_response_code(empty($_SESSION['user_id']) ? 401 : 403); echo json_encode(['success' => false, 'message' => empty($_SESSION['user_id']) ? 'Your session has ended. Please sign in again.' : 'You are not allowed to do this.']); exit;
}

$apiKey = defined('GROQ_API_KEY') ? GROQ_API_KEY : getenv('GROQ_API_KEY');
if (empty($apiKey)) {
    echo json_encode(['success' => false, 'message' => 'The AI helper is not available right now. Please contact the admin.']); exit;
}

$body    = json_decode(file_get_contents('php://input'), true);
if (!is_array($body)) $body = [];
$topic   = aiStr($body, 'topic');
$count   = (int) aiStr($body, 'count');
$types   = is_array($body['types'] ?? null) ? $body['types'] : []; // subset of ['multiple_choice','true_false','essay','long_essay'] ('essay' = Identification, 'long_essay' = Essay)
if (($tooLong = aiTopicTooLong($topic)) !== '') {
    echo json_encode(['success' => false, 'message' => $tooLong]); exit;
}

// The module picked in the form: the questions come from its lesson
$lesson = mlLessonForAi($conn, (int) $_SESSION['user_id'], (int) ($body['course_id'] ?? 0), $body['module_id'] ?? '');

// No lesson → nothing to base it on: never generate (the teacher's text is only a focus)
$aiErr = mlAiBlockReason($lesson, $body['course_id'] ?? 0, $body['module_id'] ?? '');
if ($aiErr !== null) {
    echo json_encode(['success' => false, 'message' => $aiErr]); exit;
}
if ($count < 1 || $count > 50) {
    echo json_encode(['success' => false, 'message' => 'Choose between 1 and 50 questions.']); exit;
}
$types = array_values(array_intersect(array_filter($types, 'is_string'), ['multiple_choice', 'true_false', 'essay', 'long_essay']));
if (empty($types)) {
    echo json_encode(['success' => false, 'message' => 'Pick at least one question type.']); exit;
}

// ── Per-user limit (one count per "Generate", even if it needs several AI calls) ──
$wait = aiRateCheck($conn, (int) $_SESSION['user_id'], 'ai_quiz');
if ($wait > 0) {
    http_response_code(429);
    echo json_encode(['success' => false, 'message' => aiRateMessage($wait, 'requests'), 'retry_after' => $wait]); exit;
}

$typeLabels = [
    'multiple_choice' => 'multiple choice (exactly 4 choices, exactly 1 correct)',
    'true_false'      => 'true/false (choices must be exactly "True" and "False")',
    'essay'           => 'identification (question_type "identification": the accepted_answer is ONE word or a short name/term of at most 3 words, never a sentence or phrase with a verb; no choices)',
    'long_essay'      => 'essay (question_type "essay": an open-ended question the student answers in a few sentences or paragraphs; the teacher grades it by hand, so give a short grading_guide instead of an answer, no choices)',
];
$allowedTypesDesc = implode('; ', array_map(fn($t) => $typeLabels[$t], $types));

$systemPrompt = <<<PROMPT
You are a quiz-writing assistant for a Philippine Senior High School / High School teacher.
Generate exactly the requested number of quiz questions at a level appropriate for SHS/HS students.
When a module lesson is given, every question MUST be answerable from that lesson: use its concepts,
terms, definitions and examples, and do not ask about anything the lesson does not cover. Write the
questions and choices in the same language as the lesson (e.g. a lesson in Filipino gets questions
in Filipino). If the teacher also gives a focus, apply it within the lesson. Use only these question types: {$allowedTypesDesc}.
Mix the allowed types naturally across the set unless only one type was allowed.

Respond with ONLY a single JSON object, no markdown fences, no commentary, in exactly this shape:
{"questions":[
  {"question_text":"...", "question_type":"multiple_choice", "choices":[
     {"choice_text":"...", "is_correct":true},
     {"choice_text":"...", "is_correct":false},
     {"choice_text":"...", "is_correct":false},
     {"choice_text":"...", "is_correct":false}
  ]},
  {"question_text":"...", "question_type":"true_false", "choices":[
     {"choice_text":"True", "is_correct":true},
     {"choice_text":"False", "is_correct":false}
  ]},
  {"question_text":"...", "question_type":"identification", "accepted_answer":"..."},
  {"question_text":"...", "question_type":"essay", "grading_guide":"what a good answer should include, under 200 characters"}
]}
Only include the shapes for the allowed question types.
Exactly one choice has "is_correct": true per multiple_choice/true_false question. Do not include
any field not shown above. Keep question_text concise and unambiguous.
PROMPT;

// ── One call to Groq. Returns [questions[], errorMessage|null] ─────────────
function groqQuizCall($apiKey, $systemPrompt, $userPrompt)
{
    $payload = json_encode([
        'model'            => 'openai/gpt-oss-120b',
        'messages'         => [
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => $userPrompt],
        ],
        // gpt-oss is a reasoning model: its thinking uses the same token budget,
        // so keep reasoning short and leave plenty of room for the JSON.
        'reasoning_effort' => 'low',
        'max_tokens'       => 8192,
        'temperature'      => 0.5,
        'response_format'  => ['type' => 'json_object'],
    ]);

    $ch = curl_init('https://api.groq.com/openai/v1/chat/completions');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $payload,
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey,
        ],
        CURLOPT_TIMEOUT        => 60,
        CURLOPT_SSL_VERIFYPEER => false,
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr  = curl_error($ch);
    curl_close($ch);

    // Raw AI-service errors go to the log only (includes/ai_guard.php)
    if ($curlErr || !$response || $httpCode !== 200) {
        return [[], aiFriendlyError((int) $httpCode, $response === false ? null : $response, (string) $curlErr, 'The AI helper'), (int) $httpCode];
    }
    $data = json_decode($response, true);

    $raw = is_array($data) ? (string) ($data['choices'][0]['message']['content'] ?? '') : '';
    if ($raw === '') return [[], "The AI didn't return anything. Please try again."];

    $parsed = json_decode($raw, true);
    if ((!is_array($parsed) || !isset($parsed['questions'])) && preg_match('/\{.*\}/s', $raw, $m)) {
        $parsed = json_decode($m[0], true);
    }
    if (!is_array($parsed) || empty($parsed['questions']) || !is_array($parsed['questions'])) {
        return [[], "The AI's response wasn't in the expected format. Please try again."];
    }
    return [$parsed['questions'], null];
}

// ── Sanitize one AI question into the Question Builder shape (or null) ───
function cleanAiQuestion($q, $types)
{
    $qtext = trim((string) ($q['question_text'] ?? ''));
    $qtype = strtolower(trim((string) ($q['question_type'] ?? '')));
    // In the prompt "identification" = short answer (stored as 'essay') and "essay" = long
    // answer graded by the teacher (stored as 'long_essay'). The model sometimes uses its own names.
    $aliases = [
        'identification' => 'essay', 'short_answer' => 'essay', 'short answer' => 'essay',
        'essay' => 'long_essay', 'long_essay' => 'long_essay', 'long answer' => 'long_essay', 'long_answer' => 'long_essay', 'open_ended' => 'long_essay', 'open-ended' => 'long_essay',
        'multiple choice' => 'multiple_choice', 'mcq' => 'multiple_choice',
        'true/false' => 'true_false', 'true or false' => 'true_false', 'truefalse' => 'true_false',
    ];
    $qtype = $aliases[$qtype] ?? $qtype;
    // An "essay" sent with an accepted answer but no guide is really an identification question
    if ($qtype === 'long_essay' && !in_array('long_essay', $types, true) && in_array('essay', $types, true)
        && trim((string) ($q['accepted_answer'] ?? '')) !== '') $qtype = 'essay';
    if ($qtext === '' || !in_array($qtype, $types, true)) return null;

    if ($qtype === 'long_essay') {
        $guide = trim((string) ($q['grading_guide'] ?? $q['rubric'] ?? ''));
        return ['question_text' => $qtext, 'question_type' => 'long_essay', 'identification_answer' => mb_substr($guide, 0, 255)];
    }

    if ($qtype === 'essay') {
        $answer = trim((string) ($q['accepted_answer'] ?? $q['identification_answer'] ?? $q['answer'] ?? ''));
        if ($answer === '' || qaIdAnswerTooLong($answer)) return null;   // a word or short name only, never a sentence
        return ['question_text' => $qtext, 'question_type' => 'essay', 'identification_answer' => $answer];
    }

    $choices = [];
    $seenCorrect = false;
    foreach ((array) ($q['choices'] ?? []) as $c) {
        $ctext = trim((string) (is_array($c) ? ($c['choice_text'] ?? '') : $c));
        if ($ctext === '') continue;
        $isCorrect = is_array($c) && !empty($c['is_correct']) && !$seenCorrect;   // keep only the first correct one
        if ($isCorrect) $seenCorrect = true;
        $choices[] = ['choice_text' => $ctext, 'is_correct' => $isCorrect ? 1 : 0];
    }
    if (count($choices) < 2 || !$seenCorrect) return null;
    return ['question_text' => $qtext, 'question_type' => $qtype, 'choices' => $choices];
}

// ── Ask, then top up any missing questions (max 3 calls) ──────────────────
$clean   = [];
$seen    = [];
$lastErr = null;
for ($attempt = 0; $attempt < 3 && count($clean) < $count; $attempt++) {
    $need = $count - count($clean);
    $userPrompt = ($lesson ? "Module lesson to base the quiz on:\n{$lesson['text']}\n\n" : '')
        . ($topic !== '' ? ($lesson ? "Teacher's focus:\n" : "Topic / content to base the quiz on:\n") . "{$topic}\n\n" : '')
        . "Generate exactly {$need} questions.";
    if ($clean) {
        $userPrompt .= "\n\nDo NOT repeat or rephrase any of these existing questions:\n- "
            . implode("\n- ", array_column($clean, 'question_text'));
    }

    $res = groqQuizCall($apiKey, $systemPrompt, $userPrompt);
    [$items, $err] = $res;
    if ($err) {
        $lastErr = $err;
        if (($res[2] ?? 0) === 429) break;   // AI service is busy: asking again right away won't help
        continue;
    }

    foreach ($items as $q) {
        if (count($clean) >= $count) break;
        $cq = is_array($q) ? cleanAiQuestion($q, $types) : null;
        if (!$cq) continue;
        $key = mb_strtolower(preg_replace('/\W+/u', '', $cq['question_text']));
        if (isset($seen[$key])) continue;   // skip duplicates
        $seen[$key] = true;
        $clean[] = $cq;
    }
}

if (empty($clean)) {
    echo json_encode(['success' => false, 'message' => $lastErr ?: "The AI didn't return any usable questions. Try rephrasing the topic or asking for fewer questions."]); exit;
}

echo json_encode(['success' => true, 'questions' => $clean, 'requested' => $count, 'received' => count($clean), 'module' => $lesson ? $lesson['title'] : null]);