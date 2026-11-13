<?php
// ============================================================
//  Arandia College eLMS — Contact Us form (PUBLIC)
//  File: api/contact.php
//  POST JSON {first_name, last_name, email, role, subject, message, website}
//
//  Open to everyone: visitors do NOT need to be logged in.
//  Protections instead of a login: honeypot field, per-IP rate limit,
//  field validation and a real-mailbox (MX) check on the e-mail.
//  Admins read the messages in Admin → Messages (api/contact_messages.php).
// ============================================================

require_once '../config/conn.php';
require_once '../includes/contact_messages.php';
require_once '../includes/contact_validation.php';

header('Content-Type: application/json');

function contactOut(bool $ok, string $message, int $status = 200): void
{
    http_response_code($status);
    echo json_encode(['success' => $ok, 'message' => $message]);
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    contactOut(false, 'Invalid request.', 405);
}

$data = json_decode(file_get_contents('php://input'), true);
if (!is_array($data)) contactOut(false, 'Invalid request.', 400);

// Honeypot: real visitors never see/fill this hidden field. Pretend it worked.
if (trim((string) ($data['website'] ?? '')) !== '') {
    contactOut(true, 'Thank you! Your message has been sent. We will get back to you soon.');
}

$first   = contactNormalizeName((string) ($data['first_name'] ?? ''));
$last    = contactNormalizeName((string) ($data['last_name'] ?? ''));
$email   = contactNormalizeEmail((string) ($data['email'] ?? ''));
$role    = trim((string) ($data['role'] ?? ''));
$subject = trim(preg_replace('/\s+/u', ' ', (string) ($data['subject'] ?? '')));
$message = trim(str_replace("\r\n", "\n", (string) ($data['message'] ?? '')));

$allowedRoles = ['Student', 'Teacher / Faculty', 'Parent / Guardian', 'Staff / Administrator', 'Other'];
if ($role !== '' && !in_array($role, $allowedRoles, true)) $role = 'Other';

if ($first === '' || $last === '' || $email === '' || $subject === '' || $message === '') {
    contactOut(false, 'Please fill in all required fields.', 422);
}
if ($err = contactNameError($first, 'First name')) contactOut(false, $err, 422);
if ($err = contactNameError($last, 'Last name'))   contactOut(false, $err, 422);
if ($err = contactEmailError($email))              contactOut(false, $err, 422);
if (mb_strlen($subject) > 200)  contactOut(false, 'Subject is too long (200 characters at most).', 422);
if (mb_strlen($message) < 5)    contactOut(false, 'Please write a little more in your message.', 422);
if (mb_strlen($message) > 5000) contactOut(false, 'Your message is too long (5,000 characters at most).', 422);

if (!ensureContactMessagesTable($conn)) {
    contactOut(false, 'We could not save your message right now. Please try again later.', 500);
}

// Per-IP rate limit: at most 5 messages per hour
$ip = substr((string) ($_SERVER['REMOTE_ADDR'] ?? ''), 0, 45);
if ($ip !== '') {
    $st = $conn->prepare("SELECT COUNT(*) FROM contact_messages WHERE ip_address = ? AND created_at > (NOW() - INTERVAL 1 HOUR)");
    $st->bind_param('s', $ip);
    $st->execute();
    $recent = (int) ($st->get_result()->fetch_row()[0] ?? 0);
    $st->close();
    if ($recent >= 5) {
        contactOut(false, 'You have sent several messages already. Please wait a while before sending another.', 429);
    }
}

$roleVal = $role === '' ? null : $role;
$st = $conn->prepare("INSERT INTO contact_messages (first_name, last_name, email, role, subject, message, ip_address) VALUES (?, ?, ?, ?, ?, ?, ?)");
$st->bind_param('sssssss', $first, $last, $email, $roleVal, $subject, $message, $ip);
try {
    $saved = $st->execute();
} catch (Throwable $e) {
    error_log('[contact.php] ' . $e->getMessage());
    $saved = false;
}
$newId = (int) $conn->insert_id;
$st->close();

if (!$saved) contactOut(false, 'We could not save your message right now. Please try again later.', 500);

// Real-time: open admin pages (Messages list + sidebar badge) pick this up within seconds
pushContactSync($conn, 'message.new', $newId, $subject);

contactOut(true, 'Thank you! Your message has been sent. We will get back to you soon.');