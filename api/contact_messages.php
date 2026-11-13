<?php
// ============================================================
//  Arandia College eLMS — Contact Us messages (admin actions)
//  File: api/contact_messages.php
//  POST ?action=mark    JSON {id, status: 'Read'|'New'}
//  POST ?action=reply   JSON {id, message}  → e-mails the reply to the sender
//  DELETE ?action=delete&id=
// ============================================================

session_start();
require_once '../config/conn.php';
require_once '../config/permissions.php';
require_once '../includes/contact_messages.php';
require_once '../includes/settings.php';
require_once '../includes/mailer.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['Admin', 'Head Admin'], true)) {
  http_response_code(empty($_SESSION['user_id']) ? 401 : 403);
  echo json_encode(['success' => false, 'message' => empty($_SESSION['user_id']) ? 'Your session has ended. Please sign in again.' : 'You are not allowed to do this.']);
  exit;
}

ensureContactMessagesTable($conn);
$actorId = (int) $_SESSION['user_id'];
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

// ── Live refresh (read-only): the current inbox, or just the unread number for the sidebar badge ──
if ($method === 'GET' && ($action === 'list' || $action === 'count')) {
  session_write_close();   // read-only: don't hold the session lock while polling
  header('Cache-Control: no-store');
  if ($action === 'count') { echo json_encode(['success' => true, 'unread' => countNewContactMessages($conn)]); exit; }
  $rows = contactMessagesList($conn);
  echo json_encode(['success' => true, 'messages' => $rows,
                    'unread' => countNewContactMessages($conn)], JSON_INVALID_UTF8_SUBSTITUTE);
  exit;
}

if ($method === 'POST' && $action === 'mark') {
  $data = json_decode(file_get_contents('php://input'), true) ?: [];
  $id = (int) ($data['id'] ?? 0);
  $status = ($data['status'] ?? '') === 'New' ? 'New' : 'Read';
  if (!$id) { echo json_encode(['success' => false, 'message' => 'Invalid ID.']); exit; }

  if ($status === 'Read') {
    $stmt = $conn->prepare("UPDATE contact_messages SET status = 'Read', read_by = ?, read_at = NOW() WHERE id = ?");
    $stmt->bind_param('ii', $actorId, $id);
  } else {
    $stmt = $conn->prepare("UPDATE contact_messages SET status = 'New', read_by = NULL, read_at = NULL WHERE id = ?");
    $stmt->bind_param('i', $id);
  }
  $stmt->execute();
  $stmt->close();
  pushContactSync($conn, 'message.changed', $id, '', $actorId);
  echo json_encode(['success' => true, 'unread' => countNewContactMessages($conn)]);
  exit;
}

// ── Reply to the sender by e-mail (sent from the school's mail account) ──
if ($method === 'POST' && $action === 'reply') {
  $data = json_decode(file_get_contents('php://input'), true) ?: [];
  $id = (int) ($data['id'] ?? 0);
  $reply = trim(str_replace("\r\n", "\n", (string) ($data['message'] ?? '')));
  if (!$id) { echo json_encode(['success' => false, 'message' => 'Invalid ID.']); exit; }
  if ($reply === '') { echo json_encode(['success' => false, 'message' => 'Please type your reply first.']); exit; }
  if (mb_strlen($reply) > 5000) { echo json_encode(['success' => false, 'message' => 'Your reply is too long (5,000 characters at most).']); exit; }

  $stmt = $conn->prepare("SELECT first_name, last_name, email, subject, message FROM contact_messages WHERE id = ? LIMIT 1");
  $stmt->bind_param('i', $id);
  $stmt->execute();
  $row = $stmt->get_result()->fetch_assoc();
  $stmt->close();
  if (!$row) { echo json_encode(['success' => false, 'message' => 'Message not found.']); exit; }
  if (!filter_var($row['email'], FILTER_VALIDATE_EMAIL)) { echo json_encode(['success' => false, 'message' => 'This sender has no valid e-mail address.']); exit; }

  $sys   = elmsSettingsPublic(elmsSettings($conn));
  $sysN  = (string) $sys['system_name'];
  $sysH  = htmlspecialchars($sysN, ENT_QUOTES, 'UTF-8');
  $name  = htmlspecialchars((string) $row['first_name'], ENT_QUOTES, 'UTF-8');
  $replyH = nl2br(htmlspecialchars($reply, ENT_QUOTES, 'UTF-8'));
  $origH  = nl2br(htmlspecialchars((string) $row['message'], ENT_QUOTES, 'UTF-8'));
  $subjH  = htmlspecialchars((string) $row['subject'], ENT_QUOTES, 'UTF-8');
  $F = 'font-family:Segoe UI,Helvetica,Arial,sans-serif;';

  $html = '<!DOCTYPE html><html><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"></head>'
    . '<body style="margin:0;padding:0;background:#f4f7fc;' . $F . '">'
    . '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f4f7fc;"><tr><td align="center" style="padding:36px 14px;">'
    . '<table role="presentation" width="560" cellpadding="0" cellspacing="0" style="width:100%;max-width:560px;background:#ffffff;border-radius:6px;border-top:6px solid #003087;box-shadow:0 1px 3px rgba(0,48,135,0.12);">'
    . '<tr><td style="padding:30px 34px 6px;"><div style="' . $F . 'font-size:12px;letter-spacing:3px;text-transform:uppercase;color:#003087;font-weight:700;">' . $sysH . '</div>'
    . '<div style="width:46px;height:3px;background:#003087;margin:12px 0 20px;"></div>'
    . '<div style="' . $F . 'font-size:22px;line-height:1.3;color:#10204a;font-weight:700;">Re: ' . $subjH . '</div></td></tr>'
    . '<tr><td style="padding:12px 34px 4px;' . $F . 'font-size:15px;line-height:1.7;color:#33445a;">Hi <strong style="color:#10204a;">' . $name . '</strong>,<br><br>' . $replyH . '</td></tr>'
    . '<tr><td style="padding:22px 34px 8px;"><table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f4f7fc;border-left:4px solid #003087;border-radius:6px;"><tr><td style="padding:14px 16px;' . $F . 'font-size:13px;line-height:1.6;color:#59627a;">'
    . '<strong style="color:#10204a;">Your message</strong><br>' . $origH . '</td></tr></table></td></tr>'
    . '<tr><td style="padding:18px 34px 26px;' . $F . 'font-size:13px;line-height:1.7;color:#7a8299;">Thank you,<br><strong style="color:#10204a;">' . $sysH . '</strong></td></tr>'
    . '<tr><td style="background:#10204a;padding:16px 30px;text-align:center;' . $F . 'font-size:11px;line-height:1.7;color:#9fb0dc;">' . $sysH . '<br>This reply was sent from the school&rsquo;s system. Please contact the school if you need more help.</td></tr>'
    . '</table></td></tr></table></body></html>';

  $text = "Hi {$row['first_name']},\n\n{$reply}\n\n----- Your message -----\n{$row['message']}\n\n— {$sysN}";
  $subject = 'Re: ' . $row['subject'];

  $err = elmsSendMail((string) $row['email'], $subject, $html, $text);
  if ($err !== '') {
    error_log('[contact_messages reply] ' . $err);
    echo json_encode(['success' => false, 'message' => 'The reply could not be sent right now. Please try again in a few minutes.' . (elmsIsLocalRequest() ? ' (Setup: ' . $err . ')' : '')]);
    exit;
  }

  // Remember who replied and when (columns are added the first time)
  try {
    $c = $conn->query("SHOW COLUMNS FROM contact_messages LIKE 'replied_at'");
    if ($c && $c->num_rows === 0) {
      $conn->query("ALTER TABLE contact_messages ADD COLUMN replied_by INT UNSIGNED NULL DEFAULT NULL, ADD COLUMN replied_at DATETIME NULL DEFAULT NULL");
    }
    $stmt = $conn->prepare("UPDATE contact_messages SET replied_by = ?, replied_at = NOW(), status = 'Read', read_by = COALESCE(read_by, ?), read_at = COALESCE(read_at, NOW()) WHERE id = ?");
    $stmt->bind_param('iii', $actorId, $actorId, $id);
    $stmt->execute();
    $stmt->close();
  } catch (Throwable $e) {
    error_log('[contact_messages reply log] ' . $e->getMessage());
  }
  logAdminActivity($conn, $actorId, 'reply_contact_message', 'contact_message', $id, "Replied by e-mail to '{$row['subject']}' from {$row['email']}.");
  pushContactSync($conn, 'message.changed', $id, '', $actorId);
  echo json_encode(['success' => true, 'message' => 'Reply sent.', 'replied_at' => date('M d, Y h:i A'), 'unread' => countNewContactMessages($conn)]);
  exit;
}

if ($method === 'DELETE' && $action === 'delete') {
  $id = (int) ($_GET['id'] ?? 0);
  if (!$id) { echo json_encode(['success' => false, 'message' => 'Invalid ID.']); exit; }

  $stmt = $conn->prepare("SELECT subject, email FROM contact_messages WHERE id = ?");
  $stmt->bind_param('i', $id);
  $stmt->execute();
  $row = $stmt->get_result()->fetch_assoc();
  $stmt->close();
  if (!$row) { echo json_encode(['success' => false, 'message' => 'Message not found.']); exit; }

  $stmt = $conn->prepare("DELETE FROM contact_messages WHERE id = ?");
  $stmt->bind_param('i', $id);
  $stmt->execute();
  $stmt->close();
  logAdminActivity($conn, $actorId, 'delete_contact_message', 'contact_message', $id, "Deleted message '{$row['subject']}' from {$row['email']}.");
  pushContactSync($conn, 'message.changed', $id, '', $actorId);
  echo json_encode(['success' => true, 'message' => 'Message deleted.', 'unread' => countNewContactMessages($conn)]);
  exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid request.']);