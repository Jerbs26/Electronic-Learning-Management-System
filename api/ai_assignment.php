<?php
// ============================================================
//  Arandia College eLMS — AI Assignment Generator API
//  File: api/ai_assignment.php
//  Powered by: Groq API — drafts a Title, Instructions, and a
//  suggested Max Score from the lesson of the module picked in the
//  form (plus an optional focus the teacher types in).
// ============================================================
session_start();
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

$body  = json_decode(file_get_contents('php://input'), true);
if (!is_array($body)) $body = [];
$topic = aiStr($body, 'topic');
$kind  = aiStr($body, 'kind') ?: 'essay';
if (($tooLong = aiTopicTooLong($topic)) !== '') {
    echo json_encode(['success' => false, 'message' => $tooLong]); exit;
}

// The module picked in the form: its lesson is what the assignment is about
$lesson = mlLessonForAi($conn, (int) $_SESSION['user_id'], (int) ($body['course_id'] ?? 0), $body['module_id'] ?? '');

// No lesson → nothing to base it on: never generate (the teacher's text is only a focus)
$aiErr = mlAiBlockReason($lesson, $body['course_id'] ?? 0, $body['module_id'] ?? '');
if ($aiErr !== null) {
    echo json_encode(['success' => false, 'message' => $aiErr]); exit;
}
$allowedKinds = ['essay', 'worksheet', 'project', 'reflection', 'lab report'];
if (!in_array($kind, $allowedKinds, true)) $kind = 'essay';

// ── Per-user limit ──
$wait = aiRateCheck($conn, (int) $_SESSION['user_id'], 'ai_assignment');
if ($wait > 0) {
    http_response_code(429);
    echo json_encode(['success' => false, 'message' => aiRateMessage($wait, 'requests'), 'retry_after' => $wait]); exit;
}

$systemPrompt = <<<PROMPT
You are an assignment-writing assistant for a Philippine Senior High School / High School teacher.
Draft ONE assignment of type "{$kind}" appropriate for SHS/HS students.
When a module lesson is given, the assignment MUST be about that lesson: use its concepts, terms and
examples, and do not bring in topics the lesson does not cover. Write in the same language as the
lesson (e.g. a lesson in Filipino gets a title and instructions in Filipino). If the teacher also
gives a focus, apply it within the lesson. Instructions should be clear, specific, and actionable (what to do, what to
submit, any format/length requirement) — 3 to 8 sentences, written as instructions TO the student.
Also suggest a reasonable max score (a whole number, typically 50-100) for work of this scope.

Respond with ONLY a single JSON object, no markdown fences, no commentary, in exactly this shape:
{"title":"...", "instructions":"...", "suggested_max_score": 100}
PROMPT;

$userPrompt = ($lesson ? "Module lesson to base the assignment on:\n{$lesson['text']}\n\n" : '')
    . ($topic !== '' ? "Teacher's focus / learning goal:\n{$topic}\n\n" : '')
    . "Type of work: {$kind}";

$payload = json_encode([
    'model'           => 'openai/gpt-oss-120b',
    'messages'        => [
        ['role' => 'system', 'content' => $systemPrompt],
        ['role' => 'user', 'content' => $userPrompt],
    ],
    'max_tokens'      => 1024,
    'temperature'     => 0.6,
    'response_format' => ['type' => 'json_object'],
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
    CURLOPT_TIMEOUT        => 30,
    CURLOPT_SSL_VERIFYPEER => false,
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlErr  = curl_error($ch);
curl_close($ch);

// Raw AI-service errors go to the log only (includes/ai_guard.php)
if ($curlErr || !$response || $httpCode !== 200) {
    if ($httpCode === 429) http_response_code(429);
    echo json_encode(['success' => false, 'message' => aiFriendlyError((int) $httpCode, $response === false ? null : $response, (string) $curlErr, 'The AI helper')]); exit;
}

$data = json_decode($response, true);
$raw = is_array($data) ? (string) ($data['choices'][0]['message']['content'] ?? '') : '';
if ($raw === '') {
    echo json_encode(['success' => false, 'message' => "The AI didn't return anything. Please try again."]); exit;
}

$parsed = json_decode($raw, true);
if (!is_array($parsed) || empty($parsed['title'])) {
    if (preg_match('/\{.*\}/s', $raw, $m)) {
        $parsed = json_decode($m[0], true);
    }
}
if (!is_array($parsed) || empty($parsed['title']) || empty($parsed['instructions'])) {
    echo json_encode(['success' => false, 'message' => "The AI's response wasn't in the expected format. Please try again."]); exit;
}

$title = trim((string) $parsed['title']);
$instructions = trim((string) $parsed['instructions']);
$suggestedScore = (int) ($parsed['suggested_max_score'] ?? 100);
if ($suggestedScore < 1 || $suggestedScore > 1000) $suggestedScore = 100;

echo json_encode([
    'success' => true,
    'title' => $title,
    'instructions' => $instructions,
    'suggested_max_score' => $suggestedScore,
    'module' => $lesson ? $lesson['title'] : null,
]);