<?php
// ============================================================
//  Arandia College eLMS — AI Chatbot API
//  File: api/chatbot.php
//  Powered by: Groq API (free, ultra-fast)
//
//  Teacher accounts: every request goes through the centralized scope
//  guard in includes/edubot_teacher_scope.php (role-based, applies to all
//  Teacher accounts). Student/Admin behaviour is unchanged.
// ============================================================
session_start();
require_once '../config/conn.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']); exit;
}

if (!isset($_SESSION['user_id'])) {
    http_response_code(empty($_SESSION['user_id']) ? 401 : 403); echo json_encode(['success' => false, 'message' => empty($_SESSION['user_id']) ? 'Your session has ended. Please sign in again.' : 'You are not allowed to do this.']); exit;
}

$user_id   = (int)$_SESSION['user_id'];
$role      = $_SESSION['role'] ?? '';
$firstName = $_SESSION['first_name'] ?? 'User';
session_write_close(); // session is only read here; don't block other requests

$body = json_decode(file_get_contents('php://input'), true);
if (!is_array($body)) {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']); exit;
}

// ── Prompt limits: max length, last few messages, no fake roles, no old
//    error bubbles (includes/ai_guard.php) ─────────────────────────────────
require_once __DIR__ . '/../includes/ai_guard.php';
[$messages, $msgErr] = aiSanitizeMessages($body['messages'] ?? []);
if ($msgErr !== '') {
    echo json_encode(['success' => false, 'message' => $msgErr]); exit;
}

$apiKey = defined('GROQ_API_KEY') ? GROQ_API_KEY : getenv('GROQ_API_KEY');

// ═════════════════════════════════════════════════════════════════════════
//  TEACHER: verify → sanitize → scope check → own-data context → AI
// ═════════════════════════════════════════════════════════════════════════
if ($role === 'Teacher') {
    require_once __DIR__ . '/../includes/edubot_teacher_scope.php';

    if (!tb_verify_teacher($conn, $user_id)) {
        http_response_code(empty($_SESSION['user_id']) ? 401 : 403); echo json_encode(['success' => false, 'message' => empty($_SESSION['user_id']) ? 'Your session has ended. Please sign in again.' : 'You are not allowed to do this.']); exit;
    }

    $messages = tb_sanitize_messages($messages);
    if (!$messages) {
        echo json_encode(['success' => false, 'message' => 'Please type a message.']); exit;
    }

    $latest = end($messages)['content'];
    $scope  = tb_scope_check($latest);

    if ($scope === 'injection' || $scope === 'out') {
        // Rejected locally — never sent to the AI API.
        echo json_encode(['success' => true, 'reply' => TB_MSG_OUT_OF_SCOPE, 'scope' => 'rejected']); exit;
    }
    if ($scope === 'greeting') {
        echo json_encode(['success' => true, 'reply' => tb_greeting_reply($firstName), 'scope' => 'greeting']); exit;
    }

    if (empty($apiKey)) {
        echo json_encode(['success' => false, 'message' => 'EduBot is not available right now. Please contact the admin.']); exit;
    }

    $systemPrompt = tb_system_prompt($firstName, tb_build_context($conn, $user_id));
    $temperature  = 0.2;
} else {

if (empty($apiKey)) {
    echo json_encode(['success' => false, 'message' => 'EduBot is not available right now. Please contact the admin.']); exit;
}

if (empty($messages)) {
    echo json_encode(['success' => false, 'message' => 'No messages provided.']); exit;
}

// ── Build context about the user ──────────────────────────────────────────
$contextParts = ["The user's name is $firstName and their role is $role."];

if ($role === 'Student') {
    $subjects = [];
    $res = $conn->query(
        "SELECT c.course_name, c.course_code FROM enrollments e
         JOIN courses c ON c.id = e.course_id
         WHERE e.student_id = $user_id AND e.status = 'Enrolled'"
    );
    while ($r = $res->fetch_assoc()) $subjects[] = $r['course_code'] . ' — ' . $r['course_name'];
    if ($subjects) $contextParts[] = 'The student is enrolled in: ' . implode(', ', $subjects) . '.';

    $uInfo = $conn->query("SELECT section_dept FROM users WHERE id=$user_id LIMIT 1")->fetch_assoc();
    if (!empty($uInfo['section_dept'])) $contextParts[] = 'Their section/grade is: ' . $uInfo['section_dept'] . '.';

} elseif ($role === 'Teacher') {
    $subjects = [];
    $res = $conn->query(
        "SELECT DISTINCT c.course_name, c.course_code FROM teacher_assignments ta
         JOIN courses c ON c.id = ta.course_id
         WHERE ta.teacher_id = $user_id"
    );
    while ($r = $res->fetch_assoc()) $subjects[] = $r['course_code'] . ' — ' . $r['course_name'];
    if ($subjects) $contextParts[] = 'The teacher handles: ' . implode(', ', $subjects) . '.';
}

$systemPrompt = "You are EduBot, a friendly and helpful AI assistant for Arandia College's eLMS for Senior High School (SHS) and High School (HS) students and teachers in the Philippines. "
    . implode(' ', $contextParts)
    . " Help students with homework, explain concepts, and assist teachers with lesson planning. Answer in Filipino or English depending on the user. Keep responses clear and appropriate for high school level. Only answer education-related questions. Always be encouraging and positive. School context: Arandia College eLMS, School Year 2025-2026.";

$temperature = 0.7;
} // end non-Teacher branch

// Never talk about the AI model / company behind EduBot
$systemPrompt .= AI_IDENTITY_RULE;

// ── Per-user limit (only requests that really go to the AI count) ─────────
$wait = aiRateCheck($conn, $user_id, 'chatbot');
if ($wait > 0) {
    http_response_code(429);
    echo json_encode(['success' => false, 'message' => aiRateMessage($wait), 'retry_after' => $wait]); exit;
}

// ── Call Groq API ─────────────────────────────────────────────────────────
$payload = json_encode([
    'model'       => 'openai/gpt-oss-120b',
    'messages'    => array_merge(
        [['role' => 'system', 'content' => $systemPrompt]],
        $messages
    ),
    'max_tokens'  => 1024,
    'temperature' => $temperature,
    // gpt-oss "thinks" inside the same token budget: keep that short so the
    // answer is not cut off and fewer tokens count against the rate limit
    'reasoning_effort' => 'low',
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

// Errors: the AI service's own text (model, organization, limits, billing
// link) goes to the error log only — the user gets a short plain message.
if ($curlErr || !$response || $httpCode !== 200) {
    if ($httpCode === 429) http_response_code(429);
    echo json_encode(['success' => false, 'message' => aiFriendlyError((int) $httpCode, $response === false ? null : $response, (string) $curlErr)]); exit;
}

$data  = json_decode($response, true);
$reply = is_array($data) ? trim((string) ($data['choices'][0]['message']['content'] ?? '')) : '';

if ($reply === '') {
    echo json_encode(['success' => false, 'message' => "EduBot couldn't answer that right now. Please try again."]); exit;
}
$reply = aiScrubReply($reply);

if ($role === 'Teacher') {
    $reply = tb_filter_reply($reply); // block code/markup or off-scope output
}

echo json_encode(['success' => true, 'reply' => $reply]);
