<?php
// ============================================================
//  Arandia College eLMS — Login
//  File: login.php  |  Target: SHS & HS
// ============================================================
require_once __DIR__ . '/includes/session_safe.php';
elmsSessionStart();
require_once 'config/conn.php';

// ── System name / school level (Admin → Settings, includes/settings.php) ──
require_once __DIR__ . '/includes/settings.php';
$__sysSet     = elmsSettingsPublic(elmsSettings($conn));
$SYS_NAME     = $__sysSet['system_name'];                                   // plain text (subjects, text e-mails)
$SYS_NAME_H   = htmlspecialchars($SYS_NAME, ENT_QUOTES, 'UTF-8');           // for HTML
$SYS_LEVEL_H  = htmlspecialchars($__sysSet['school_level_label'], ENT_QUOTES, 'UTF-8');
require_once __DIR__ . '/includes/schema_guard.php';
elmsEnsureRoles($conn);   // Admission role works even if migration_admission_role.sql was not imported

// ── Resolve the school logo (self-healing) ─────────────────────
// Avoids a broken-image icon when the actual filename on the server
// differs in case or extension from what the markup expects.
$__logoSrc = null;
foreach (['jpg', 'jpeg', 'png', 'JPG', 'JPEG', 'PNG'] as $__logoExt) {
    if (is_file(__DIR__ . '/picture/logo.' . $__logoExt)) {
        $__logoSrc = 'picture/logo.' . $__logoExt;
        break;
    }
}

// E-mail (sign-in code, password reset): settings in config/secrets.php
require_once __DIR__ . '/includes/mailer.php';

// ── Redirect if already logged in ───────────────────────────
if (isset($_SESSION['user_id'])) {
  $r = $_SESSION['role'] ?? '';
  if ($r === 'Admin' || $r === 'Head Admin') {
    header('Location: admin/dashboard.php');
    exit;
  }
  if ($r === 'Teacher') {
    header('Location: teacher/dashboard.php');
    exit;
  }
  if ($r === 'Student') {
    header('Location: student/dashboard.php');
    exit;
  }
  if ($r === 'Admission') {
    header('Location: admission/dashboard.php');
    exit;
  }
  // Signed in with an account type this system doesn't know: sign out cleanly
  // so the login form works again (instead of a half signed-in session).
  $_SESSION = [];
  session_regenerate_id(true);
}

// ── Back on the login page = the half-finished sign-in is cancelled ──
// (pressed Back on the OTP page). The e-mailed code stops working, so
// Forward / reopening verify_otp.php can't finish that sign-in.
if (!empty($_SESSION['otp_pending_user_id'])) {
  elmsCancelPendingOtp($conn);
}

 $error   = '';
if (isset($_GET['kicked'])) {
  $error = 'Your account was signed in on another device, so you were signed out here. Sign in again to continue.';
}
// Message from the last sign-in attempt (Post/Redirect/Get, see the end of the
// handlers): Refresh and Back never re-send the sign-in form.
$flashLogin = '';
$flashRemember = null;
if (!empty($_SESSION['login_flash']) && is_array($_SESSION['login_flash'])) {
  if ($error === '') $error = (string) ($_SESSION['login_flash']['error'] ?? '');
  $flashLogin    = (string) ($_SESSION['login_flash']['login'] ?? '');
  $flashRemember = !empty($_SESSION['login_flash']['remember']);
}
unset($_SESSION['login_flash']);
if (isset($_GET['norole'])) {
  $error = 'This account has no account type set yet. Please ask the Head Admin to open Accounts → Edit and choose the account type.';
}
 $fpMsg   = '';   // forgot-password feedback
 $fpError = '';
 $lockSeconds = 0;   // > 0 while this email is locked (shown as a countdown)

// ── Failed sign-in limit ─────────────────────────────────────
// 3 wrong passwords for one email → that email is locked for 3 minutes.
// Kept in the database (not the session / a cookie) so clearing cookies or
// opening a private window does not reset it.
const LOGIN_MAX_ATTEMPTS = 3;
const LOGIN_LOCK_MINUTES = 3;

$conn->query("CREATE TABLE IF NOT EXISTS login_attempts (
    email        VARCHAR(190) NOT NULL PRIMARY KEY,
    attempts     INT NOT NULL DEFAULT 0,
    locked_until DATETIME NULL,
    last_attempt DATETIME NOT NULL
)");

/** Seconds left on this email's lock (0 = not locked). Clears an expired lock. */
function loginLockSeconds(mysqli $conn, string $email): int
{
    $st = $conn->prepare("SELECT GREATEST(TIMESTAMPDIFF(SECOND, NOW(), locked_until), 0) AS secs, locked_until
                          FROM login_attempts WHERE email = ? LIMIT 1");
    $st->bind_param('s', $email);
    $st->execute();
    $r = $st->get_result()->fetch_assoc();
    $st->close();
    if (!$r || $r['locked_until'] === null) return 0;
    if ((int) $r['secs'] > 0) return (int) $r['secs'];
    loginClearAttempts($conn, $email);   // lock is over: start again from 3 tries
    return 0;
}

/** Count one wrong password. Returns [attemptsLeft, lockSeconds]. */
function loginRecordFailure(mysqli $conn, string $email): array
{
    $st = $conn->prepare("INSERT INTO login_attempts (email, attempts, last_attempt) VALUES (?, 1, NOW())
                          ON DUPLICATE KEY UPDATE attempts = attempts + 1, last_attempt = NOW()");
    $st->bind_param('s', $email);
    $st->execute();
    $st->close();

    $st = $conn->prepare("SELECT attempts FROM login_attempts WHERE email = ?");
    $st->bind_param('s', $email);
    $st->execute();
    $n = (int) ($st->get_result()->fetch_assoc()['attempts'] ?? 0);
    $st->close();

    if ($n >= LOGIN_MAX_ATTEMPTS) {
        $mins = LOGIN_LOCK_MINUTES;
        $st = $conn->prepare("UPDATE login_attempts SET locked_until = DATE_ADD(NOW(), INTERVAL ? MINUTE) WHERE email = ?");
        $st->bind_param('is', $mins, $email);
        $st->execute();
        $st->close();
        return [0, $mins * 60];
    }
    return [LOGIN_MAX_ATTEMPTS - $n, 0];
}

function loginClearAttempts(mysqli $conn, string $email): void
{
    $st = $conn->prepare("DELETE FROM login_attempts WHERE email = ?");
    $st->bind_param('s', $email);
    $st->execute();
    $st->close();
}

function loginLockMessage(int $secs): string
{
    $m = (int) ceil($secs / 60);
    return 'Too many failed sign-in attempts. Please try again in ' . $m . ' minute' . ($m === 1 ? '' : 's') . '.';
}

/** Wrong email or password: count it and build the message. */
function loginFailMessage(mysqli $conn, string $email, int &$lockSeconds): string
{
    [$left, $lock] = loginRecordFailure($conn, $email);
    if ($lock > 0) { $lockSeconds = $lock; return loginLockMessage($lock); }
    return 'Invalid email or password. ' . $left . ' attempt' . ($left === 1 ? '' : 's') . ' left.';
}

// ── Remember Me: read cookie on page load ────────────────────
$rememberedLogin = '';
if (!empty($_COOKIE['remember_login'])) {
  $rememberedLogin = htmlspecialchars(strip_tags($_COOKIE['remember_login']));
  // Sign-in is by email only now: drop an LRN / username remembered from before
  if (!filter_var($_COOKIE['remember_login'], FILTER_VALIDATE_EMAIL)) $rememberedLogin = '';
}
// "Remember me" saves the EMAIL only (cookie "remember_login"). The password is never
// saved and this device is never signed in automatically, so a shared / computer-shop
// PC can't open the account. A device key from an older version is removed here and
// no longer works (includes/remember_me.php).
require_once __DIR__ . '/includes/remember_me.php';
rmForgetDevice($conn);
$rememberedUser = null;

// ── Forgot-password handler ──────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['forgot_password'])) {
  $fpEmail = trim($_POST['fp_email'] ?? '');

  if (empty($fpEmail)) {
    $fpError = 'Please enter your email address.';
  } elseif (!filter_var($fpEmail, FILTER_VALIDATE_EMAIL)) {
    $fpError = 'Please enter a valid email address.';
  } else {
    // Check if email exists
    $stmt = $conn->prepare("SELECT id, first_name FROM users WHERE email = ? AND status = 'Active' LIMIT 1");
    $stmt->bind_param('s', $fpEmail);
    $stmt->execute();
    $fpUser = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($fpUser) {
      $fpMailErr = 'Could not create the reset link.';   // replaced below once the e-mail is sent
      // Generate a secure token
      $token = bin2hex(random_bytes(32));

      // ── Store token in DB (Using DATE_ADD for Timezone fix) ─────────
      $ins = $conn->prepare(
        "INSERT INTO password_resets (user_id, token, expires_at, used)
         VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 20 MINUTE), 0) 
         ON DUPLICATE KEY UPDATE token = VALUES(token), expires_at = DATE_ADD(NOW(), INTERVAL 20 MINUTE), used = 0"
      );

      // Bind params: 'i' for integer user_id, 's' for string token
      $ins->bind_param('is', $fpUser['id'], $token);

      // Execute the query first
      if ($ins->execute()) {
        
        // ── Generate dynamic reset link ────────────────────────
        $resetLink = elmsBaseUrl() . "/reset_password.php?token=" . urlencode($token);
        $name = htmlspecialchars($fpUser['first_name']);

        // ── Send reset e-mail ─────────────────────────────────
        // Settings come from config/secrets.php via includes/mailer.php
        {
          $mailSubject = $SYS_NAME . ' - Password Reset';

          $mailBody = '
<!DOCTYPE html><html><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Reset your password</title></head>
<body style="margin:0;padding:0;background:#f4f7fc;font-family:Segoe UI,Helvetica,Arial,sans-serif;">
<div style="display:none;max-height:0;overflow:hidden;opacity:0;color:#f4f7fc;">Reset your password &mdash; this link is valid for 20 minutes.</div>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f4f7fc;"><tr><td align="center" style="padding:40px 14px;">
<table role="presentation" width="540" cellpadding="0" cellspacing="0" style="width:100%;max-width:540px;background:#ffffff;border-radius:6px;border-top:6px solid #003087;box-shadow:0 1px 3px rgba(0,48,135,0.12);">
<tr><td align="center" style="padding:38px 36px 8px;">
<div style="font-family:Segoe UI,Helvetica,Arial,sans-serif;font-size:12px;letter-spacing:3px;text-transform:uppercase;color:#003087;font-weight:700;">' . $SYS_NAME_H . '</div>
<div style="width:46px;height:3px;background:#003087;margin:12px auto 22px;"></div>
<div style="font-family:Segoe UI,Helvetica,Arial,sans-serif;font-size:30px;line-height:1.25;color:#10204a;font-weight:700;">Reset your password</div>
<div style="font-family:Segoe UI,Helvetica,Arial,sans-serif;font-size:15px;line-height:1.7;color:#59627a;margin-top:14px;">Hi <strong style="color:#10204a;">' . $name . '</strong>, we received a request to reset the password of your <strong style="color:#10204a;">' . $SYS_NAME_H . '</strong> account. Press the button below to choose a new one.</div>
</td></tr>
<tr><td align="center" style="padding:26px 36px 8px;">
<table role="presentation" cellpadding="0" cellspacing="0"><tr><td align="center" bgcolor="#003087" style="background:#003087;border-radius:8px;border-bottom:4px solid #001a4d;">
<a href="' . htmlspecialchars($resetLink) . '" style="display:inline-block;padding:16px 40px;font-family:Segoe UI,Helvetica,Arial,sans-serif;font-size:16px;font-weight:700;color:#ffffff;text-decoration:none;">Reset My Password &rarr;</a>
</td></tr></table>
<div style="font-family:Segoe UI,Helvetica,Arial,sans-serif;font-size:13px;color:#59627a;margin-top:16px;">This link is valid for <strong>20 minutes</strong> &middot; single use only</div>
</td></tr>
<tr><td style="padding:24px 36px 6px;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-top:1px solid #dbe4f5;"><tr><td style="padding:18px 0 0;font-family:Segoe UI,Helvetica,Arial,sans-serif;font-size:12px;line-height:1.6;color:#7a8299;text-align:center;">Button not working? <a href="' . htmlspecialchars($resetLink) . '" style="color:#003087;font-weight:700;text-decoration:underline;">Click here to open the reset page</a>.</td></tr></table>
</td></tr>
<tr><td style="padding:22px 36px 30px;font-family:Segoe UI,Helvetica,Arial,sans-serif;font-size:13px;line-height:1.75;color:#7a8299;text-align:center;"><strong style="color:#10204a;">Keep it private.</strong> Never share this link &mdash; ' . $SYS_NAME_H . ' will never ask for your password by email.<br>Didn&rsquo;t request this? You can safely ignore this email &mdash; your password will not change.</td></tr>
<tr><td style="background:#10204a;padding:18px 30px;text-align:center;font-family:Segoe UI,Helvetica,Arial,sans-serif;font-size:11px;line-height:1.7;color:#9fb0dc;">' . $SYS_NAME_H . ' &middot; ' . $SYS_LEVEL_H . ' Learning Portal<br>This is an automated message &mdash; please do not reply.</td></tr>
</table></td></tr></table></body></html>';

          $mailText = "Hi {$name},\n\nReset your {$SYS_NAME} password using the link below (valid for 20 minutes):\n\n{$resetLink}\n\nIf the button above does not work, you can copy and paste the link above into your browser.\n\n— {$SYS_NAME}";

          $fpMailErr = elmsSendMail($fpEmail, $mailSubject, $mailBody, $mailText);
        }
      }
      
      // Close the statement HERE, after the email logic
      $ins->close();
    }

    // Show success if email was found, or a clear error if not registered
    if ($fpUser && !empty($fpMailErr)) {
      // Never say "sent" when it wasn't
      $fpError = 'We could not send the reset e-mail right now. Please try again in a few minutes or contact the school.'
        . (elmsIsLocalRequest() ? ' (Setup: ' . $fpMailErr . ')' : '');
    } elseif ($fpUser) {
      $fpMsg = 'A password reset link has been sent. Please check your inbox (and spam folder).';
    } else {
      $fpError = 'This email address is not registered in our system.';
    }
  }

  // Sent by the modal's own script (fetch): answer with JSON so the page does not
  // reload and the modal stays exactly where it is. Without JS the normal
  // full-page POST above still works.
  if (strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest') {
    while (ob_get_level() > 0) { ob_end_clean(); }
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    echo json_encode([
      'ok'      => $fpMsg !== '',
      'message' => $fpMsg !== '' ? $fpMsg : ($fpError !== '' ? $fpError : 'Something went wrong. Please try again.'),
    ]);
    exit;
  }
}

// ── Normal login handler ─────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['forgot_password'])) {
  $login = is_string($_POST['login'] ?? null) ? trim($_POST['login']) : '';
  $password = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';

  if (empty($login) || empty($password)) {
    $error = 'Please fill in all fields.';
  } elseif (!filter_var($login, FILTER_VALIDATE_EMAIL)) {
    $error = 'Please sign in with your email address.';
  } elseif (($lockSeconds = loginLockSeconds($conn, strtolower($login))) > 0) {
    $error = loginLockMessage($lockSeconds);
  } else {
    // Sign in with the email only. Several older accounts may share one email,
    // so the one whose password matches is used.
    $stmt = $conn->prepare(
      "SELECT id, username, email, password, role, first_name, last_name, status
             FROM users WHERE LOWER(email) = LOWER(?)
             ORDER BY (status = 'Active') DESC, id ASC LIMIT 10"
    );
    $stmt->bind_param('s', $login);
    $stmt->execute();
    $cands = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    $user = null;
    foreach ($cands as $c) {
      $remOk = !empty($_SESSION['rm_standin']) && hash_equals($_SESSION['rm_standin'], $password)
        && $rememberedUser && (int) $rememberedUser['id'] === (int) $c['id'];
      if ($remOk || password_verify($password, (string) $c['password'])) { $user = $c; break; }
    }
    if (!$user && $cands) $user = $cands[0];   // wrong password: handled below like before

    if (!$user) {
      $error = loginFailMessage($conn, strtolower($login), $lockSeconds);
    } elseif ($user['status'] === 'Inactive') {
      $error = 'Your account is inactive. Please contact the admin.';
    } elseif (!in_array((string) $user['role'], ELMS_ROLES, true)
              && password_verify($password, (string) $user['password'])) {
      // Right password, but the account has no account type (e.g. saved before the
      // Admission role existed in the database). Don't send a code that leads nowhere.
      $error = 'This account has no account type set yet. Please ask the Head Admin to open Accounts → Edit and choose the account type.';
    } else {
      // "Remember me": the stand-in password from this page + this device's key
      $isRemembered = !empty($_SESSION['rm_standin'])
        && hash_equals($_SESSION['rm_standin'], $password)
        && $rememberedUser && (int) $rememberedUser['id'] === (int) $user['id'];

      // Hashed passwords only (plain-text fallback removed: a copied hash must never work as a password)
      $isHash = !$isRemembered && password_verify($password, (string) $user['password']);

      if ($isHash || $isRemembered) {
        unset($_SESSION['rm_standin']);
        loginClearAttempts($conn, strtolower($login));
        // Upgrade the hash if PHP's default algorithm/cost has changed
        if ($isHash && password_needs_rehash((string) $user['password'], PASSWORD_DEFAULT)) {
          $hash = password_hash($password, PASSWORD_DEFAULT);
          $upd = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
          $upd->bind_param('si', $hash, $user['id']);
          $upd->execute();
          $upd->close();
        }

        // ── All roles (Head Admin, Admin, Teacher, Student): Generate & store OTP
        //    (the sign-in finishes in verify_otp.php)
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $otpHash = password_hash($otp, PASSWORD_DEFAULT);

        $otpStmt = $conn->prepare(
          "INSERT INTO login_otps (user_id, otp_hash, expires_at, used)
           VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 10 MINUTE), 0)
           ON DUPLICATE KEY UPDATE otp_hash = VALUES(otp_hash),
             expires_at = DATE_ADD(NOW(), INTERVAL 10 MINUTE), used = 0"
        );
        $otpStmt->bind_param('is', $user['id'], $otpHash);
        $otpStmt->execute();
        $otpStmt->close();

        // ── Send OTP via email ────────────────────────────────
        $otpSent = false;
        // Settings come from config/secrets.php via includes/mailer.php
        {
          $mailSubject = $SYS_NAME . ' — Your Login OTP';

          $otpName = htmlspecialchars($user['first_name']);
          $mailBody = '
<!DOCTYPE html><html><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Your login code</title></head>
<body style="margin:0;padding:0;background:#f4f7fc;font-family:Segoe UI,Helvetica,Arial,sans-serif;">
<div style="display:none;max-height:0;overflow:hidden;opacity:0;color:#f4f7fc;">Your code is ' . $otp . ' &mdash; it expires in 10 minutes.</div>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f4f7fc;"><tr><td align="center" style="padding:40px 14px;">
<table role="presentation" width="540" cellpadding="0" cellspacing="0" style="width:100%;max-width:540px;background:#ffffff;border-radius:6px;border-top:6px solid #003087;box-shadow:0 1px 3px rgba(0,48,135,0.12);">
<tr><td align="center" style="padding:38px 36px 8px;">
<div style="font-family:Segoe UI,Helvetica,Arial,sans-serif;font-size:12px;letter-spacing:3px;text-transform:uppercase;color:#003087;font-weight:700;">' . $SYS_NAME_H . '</div>
<div style="width:46px;height:3px;background:#003087;margin:12px auto 22px;"></div>
<div style="font-family:Segoe UI,Helvetica,Arial,sans-serif;font-size:30px;line-height:1.25;color:#10204a;font-weight:700;">Your login code</div>
<div style="font-family:Segoe UI,Helvetica,Arial,sans-serif;font-size:15px;line-height:1.7;color:#59627a;margin-top:14px;">Hi <strong style="color:#10204a;">' . $otpName . '</strong>, someone just signed in to your <strong style="color:#10204a;">' . $SYS_NAME_H . '</strong> account. Enter this code to finish logging in:</div>
</td></tr>
<tr><td align="center" style="padding:22px 36px 6px;">
<table role="presentation" cellpadding="0" cellspacing="0" style="width:100%;border-top:1px solid #dbe4f5;border-bottom:1px solid #dbe4f5;"><tr><td align="center" style="padding:24px 8px 22px;">
<div style="font-family:Consolas,Menlo,Courier New,monospace;font-size:50px;line-height:1.1;font-weight:700;letter-spacing:14px;color:#003087;margin-left:14px;">' . $otp . '</div>
<div style="font-family:Segoe UI,Helvetica,Arial,sans-serif;font-size:13px;color:#59627a;margin-top:12px;">Valid for <strong>10 minutes</strong> &middot; single use only</div>
</td></tr></table>
</td></tr>
<tr><td style="padding:22px 30px 6px;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0"><tr>
<td width="33%" align="center" valign="top" style="padding:0 6px;font-family:Segoe UI,Helvetica,Arial,sans-serif;font-size:12px;line-height:1.5;color:#59627a;"><div style="width:26px;height:26px;line-height:26px;border-radius:50%;background:#003087;color:#fff;font-weight:700;font-size:13px;margin:0 auto 8px;">1</div>Return to the login page</td>
<td width="33%" align="center" valign="top" style="padding:0 6px;font-family:Segoe UI,Helvetica,Arial,sans-serif;font-size:12px;line-height:1.5;color:#59627a;"><div style="width:26px;height:26px;line-height:26px;border-radius:50%;background:#003087;color:#fff;font-weight:700;font-size:13px;margin:0 auto 8px;">2</div>Type the 6-digit code</td>
<td width="33%" align="center" valign="top" style="padding:0 6px;font-family:Segoe UI,Helvetica,Arial,sans-serif;font-size:12px;line-height:1.5;color:#59627a;"><div style="width:26px;height:26px;line-height:26px;border-radius:50%;background:#003087;color:#ffffff;font-weight:700;font-size:13px;margin:0 auto 8px;">3</div>Press Verify OTP</td>
</tr></table></td></tr>
<tr><td style="padding:22px 36px 30px;font-family:Segoe UI,Helvetica,Arial,sans-serif;font-size:13px;line-height:1.75;color:#7a8299;text-align:center;"><strong style="color:#10204a;">Keep it private.</strong> Never share this code &mdash; ' . $SYS_NAME_H . ' staff will never ask for it.<br>Wasn&rsquo;t you? Change your password right away.</td></tr>
<tr><td style="background:#10204a;padding:18px 30px;text-align:center;font-family:Segoe UI,Helvetica,Arial,sans-serif;font-size:11px;line-height:1.7;color:#9fb0dc;">' . $SYS_NAME_H . ' &middot; ' . $SYS_LEVEL_H . ' Learning Portal<br>This is an automated message &mdash; please do not reply.</td></tr>
</table></td></tr></table></body></html>';

          $mailText = "Hi {$otpName},\n\nYour {$SYS_NAME} login OTP is: {$otp}\n\nThis code expires in 10 minutes. Do not share it with anyone.\n\n— {$SYS_NAME}";
          $otpMailErr = elmsSendMail($user['email'], $mailSubject, $mailBody, $mailText);
          $otpSent = ($otpMailErr === '');
        }

        if ($otpSent) {
          // Store pending login data in session (no full login yet)
          $_SESSION['otp_pending_fails']     = 0;
          $_SESSION['otp_last_sent_at']      = time();
          $_SESSION['otp_pending_user_id']   = $user['id'];
          $_SESSION['otp_pending_username']   = $user['username'];
          $_SESSION['otp_pending_role']       = $user['role'];
          $_SESSION['otp_pending_first_name'] = $user['first_name'];
          $_SESSION['otp_pending_last_name']  = $user['last_name'];
          $_SESSION['otp_pending_remember']   = !empty($_POST['remember']) ? $login : '';
          $_SESSION['otp_pending_email_hint'] = substr($user['email'], 0, 3)
            . str_repeat('*', max(0, strpos($user['email'], '@') - 3))
            . substr($user['email'], strpos($user['email'], '@'));

          header('Location: verify_otp.php', true, 303);
          exit;
        } else {
          $error = 'We could not send your sign-in code by e-mail. Please try again in a few minutes or contact the school.'
            . (elmsIsLocalRequest() ? ' (Setup: ' . $otpMailErr . ')' : '');
        }
      } else {
        $error = loginFailMessage($conn, strtolower($login), $lockSeconds);
      }
    }
  }
}

// ── Keep the lock countdown when the user leaves and comes back ──
// The lock lives in login_attempts (database). The session only remembers
// WHICH email got locked, so a plain visit to this page shows the same
// remaining time instead of a fresh form.
$lockedEmail = '';
$isLoginPost = ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['forgot_password']));
if ($isLoginPost && $lockSeconds > 0 && isset($login)) {
  $_SESSION['login_lock_email'] = strtolower($login);
} elseif (!$isLoginPost && !empty($_SESSION['login_lock_email'])) {
  $secsLeft = loginLockSeconds($conn, (string) $_SESSION['login_lock_email']);
  if ($secsLeft > 0) {
    $lockSeconds = $secsLeft;
    $lockedEmail = (string) $_SESSION['login_lock_email'];
    if ($error === '') $error = loginLockMessage($secsLeft);
  } else {
    unset($_SESSION['login_lock_email']);   // lock is over
  }
}

// ── Post/Redirect/Get for the sign-in form ───────────────────
// A failed sign-in used to answer the POST directly, so Refresh asked to
// "resend the form" and Back showed "Confirm Form Resubmission". Now the
// message is kept in the session and the browser is sent to a plain GET.
if ($isLoginPost) {
  $_SESSION['login_flash'] = [
    'error'    => $error !== '' ? $error : 'Something went wrong. Please try again.',
    'login'    => $login ?? '',
    'remember' => !empty($_POST['remember']),
  ];
  header('Location: login.php', true, 303);
  exit;
}
?>
<?php
// Stand-in for the password box when this device is remembered (never the real password)
$rmStandin = '';
if ($rememberedUser && ($_POST['login'] ?? $rememberedLogin) !== '') {
  $rmStandin = $_SESSION['rm_standin'] ?? '';
  if ($rmStandin === '') {
    $rmStandin = bin2hex(random_bytes(6));   // 12 characters, shown as dots
    $_SESSION['rm_standin'] = $rmStandin;
  }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <meta name="theme-color" content="#061A33">
  <meta name="color-scheme" content="light">
  <meta name="format-detection" content="telephone=no">
  <title>Sign In — <?= $SYS_NAME_H ?></title>
  <?php if ($__logoSrc): ?>
    <link rel="icon" href="<?= htmlspecialchars($__logoSrc) ?>">
    <link rel="apple-touch-icon" href="<?= htmlspecialchars($__logoSrc) ?>">
  <?php endif; ?>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
    /* ============================================================
       Arandia College eLMS — Sign in
       Same palette, fonts and header as index.php
       ============================================================ */
    :root {
      --navy: #0B2545;
      --navy-deep: #061A33;
      --navy-light: #123A6B;
      --gold: #FFC53D;
      --gold-dark: #B7861A;
      --teal: #0E7C7B;
      --ink: #142433;
      --muted: #5B6B7C;
      --line: #E0E4EE;
      --bg: #F4F6FA;
      --surface: #FFFFFF;
      --tint: #E8EDF5;
      --safe-l: env(safe-area-inset-left, 0px);
      --safe-r: env(safe-area-inset-right, 0px);
      --safe-b: env(safe-area-inset-bottom, 0px);
    }

    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    html {
      -webkit-text-size-adjust: 100%;
      text-size-adjust: 100%;
      scrollbar-width: thin;
      scrollbar-color: rgba(11, 37, 69, .45) transparent;
    }
    ::-webkit-scrollbar { width: 6px; height: 6px; }
    ::-webkit-scrollbar-track { background: transparent; }
    ::-webkit-scrollbar-thumb { background: rgba(11, 37, 69, .45); border-radius: 999px; }
    ::-webkit-scrollbar-thumb:hover { background: rgba(11, 37, 69, .7); }

    body {
      font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
      font-size: 16px;
      line-height: 1.6;
      color: var(--ink);
      background: var(--bg);
      min-height: 100vh;
      min-height: 100dvh;
      display: flex;
      flex-direction: column;
      overflow-x: clip;
      -webkit-font-smoothing: antialiased;
      -webkit-tap-highlight-color: transparent;
    }

    a { color: inherit; }
    :focus-visible { outline: 2px solid var(--navy); outline-offset: 3px; border-radius: 6px; }

    .side-pad {
      padding-left: max(20px, calc((100% - 1240px) / 2), calc(16px + var(--safe-l)));
      padding-right: max(20px, calc((100% - 1240px) / 2), calc(16px + var(--safe-r)));
    }

    /* ─── TOPBAR + NAV (same as index) ─── */
    .topbar {
      background: var(--navy-deep);
      display: flex;
      justify-content: flex-end;
      gap: 1.25rem;
      padding-top: .45rem;
      padding-bottom: .45rem;
      font-size: .8125rem;
    }
    .topbar a { color: #C3CDDD; text-decoration: none; }
    .topbar a:hover { color: var(--gold); }

    .site-nav {
      position: sticky;
      top: 0;
      z-index: 100;
      height: 64px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 1rem;
      background: rgba(255, 255, 255, .92);
      -webkit-backdrop-filter: saturate(160%) blur(14px);
      backdrop-filter: saturate(160%) blur(14px);
      border-bottom: 1px solid var(--line);
    }

    /* Desktop: logo and Back to Home sit nearer the left/right edges (not full-bleed); top bar lines up with them */
    @media (min-width: 861px) {
      .topbar.side-pad,
      .site-nav.side-pad {
        padding-left: calc(clamp(24px, 3vw, 48px) + var(--safe-l));
        padding-right: calc(clamp(24px, 3vw, 48px) + var(--safe-r));
      }
    }

    .brand { display: flex; align-items: center; gap: .7rem; text-decoration: none; min-width: 0; }
    .brand-logo { width: 40px; height: 40px; object-fit: contain; flex-shrink: 0; }
    .brand-fallback { display: flex; align-items: center; justify-content: center; background: var(--navy); color: #fff; font-weight: 700; border-radius: 10px; font-size: .9rem; }
    .brand-text { line-height: 1.15; min-width: 0; }
    .brand-name { display: block; font-family: 'Space Grotesk', sans-serif; font-size: 1.02rem; font-weight: 700; letter-spacing: -0.015em; color: var(--ink); white-space: nowrap; }
    .brand-sub { display: block; font-size: .75rem; color: var(--muted); white-space: nowrap; }

    .back-link {
      display: inline-flex;
      align-items: center;
      gap: .4rem;
      min-height: 42px;
      padding: 0 1rem;
      border-radius: 12px;
      border: 1px solid var(--navy);
      background: var(--navy);
      color: #fff;
      font: 600 .9rem/1 'Inter', sans-serif;
      text-decoration: none;
      box-shadow: 0 2px 8px rgba(11, 37, 69, .18);
      transition: background-color .2s, border-color .2s, box-shadow .2s;
      white-space: nowrap;
    }
    .back-link svg { width: 16px; height: 16px; }
    @media (hover: hover) { .back-link:hover { background: var(--navy-light); border-color: var(--navy-light); color: #fff; box-shadow: 0 4px 12px rgba(11, 37, 69, .25); } }

    /* ─── MAIN ─── */
    .auth {
      flex: 1;
      display: flex;
      align-items: center;
      justify-content: center;
      padding-top: 3rem;
      padding-bottom: 3rem;
      background:
        radial-gradient(900px 500px at 100% 0%, rgba(255, 197, 61, .08), transparent 60%),
        radial-gradient(800px 600px at 0% 100%, rgba(14, 124, 123, .08), transparent 60%),
        var(--bg);
    }

    .auth-card {
      width: 100%;
      max-width: 980px;
      display: grid;
      grid-template-columns: 1fr 1fr;
      background: var(--surface);
      border: 1px solid var(--line);
      border-radius: 24px;
      overflow: hidden;
      box-shadow: 0 1px 2px rgba(20, 36, 51, .04), 0 24px 60px rgba(11, 37, 69, .10);
    }

    /* Left — brand panel */
    .auth-brand {
      position: relative;
      overflow: hidden;
      isolation: isolate;
      color: #EEF2F8;
      padding: 3rem 2.75rem;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      gap: 2.5rem;
      background:
        radial-gradient(600px 320px at 100% -10%, rgba(255, 197, 61, .14), transparent 60%),
        radial-gradient(520px 420px at -10% 110%, rgba(14, 124, 123, .40), transparent 60%),
        linear-gradient(160deg, var(--navy), var(--navy-light));
    }

    .auth-brand-bg {
      position: absolute;
      right: -90px;
      bottom: -90px;
      width: 360px;
      height: auto;
      opacity: .12;
      filter: blur(3px);
      pointer-events: none;
      user-select: none;
      z-index: -1;
    }

    .ab-top { display: flex; align-items: center; gap: .85rem; }
    .ab-logo { width: 56px; height: 56px; object-fit: contain; background: #fff; border-radius: 16px; padding: 5px; flex-shrink: 0; }
    .ab-top strong { display: block; font-family: 'Space Grotesk', sans-serif; font-size: 1.15rem; color: #fff; line-height: 1.2; }
    .ab-top span { font-size: .8rem; color: #B7C2D3; }

    .eyebrow {
      display: inline-flex;
      align-items: center;
      gap: .5rem;
      font-size: .75rem;
      font-weight: 600;
      letter-spacing: .08em;
      text-transform: uppercase;
      color: var(--gold);
      margin-bottom: .85rem;
    }
    .eyebrow::before { content: ''; width: 20px; height: 2px; background: currentColor; border-radius: 2px; }

    .ab-mid h2 {
      font-family: 'Space Grotesk', sans-serif;
      font-size: clamp(1.75rem, 1.2rem + 1.6vw, 2.35rem);
      font-weight: 700;
      line-height: 1.15;
      letter-spacing: -0.02em;
      color: #fff;
      margin-bottom: .85rem;
    }
    .ab-mid h2 span { color: var(--gold); }
    .ab-mid p { color: #C3CDDD; font-size: .975rem; max-width: 22rem; }

    .ab-list { list-style: none; display: grid; gap: .7rem; }
    .ab-list li { display: flex; align-items: center; gap: .75rem; font-size: .9rem; color: #DCE3EE; }
    .ab-list .ic {
      width: 32px; height: 32px; flex-shrink: 0;
      border-radius: 10px;
      background: rgba(255, 255, 255, .08);
      border: 1px solid rgba(255, 255, 255, .12);
      display: flex; align-items: center; justify-content: center;
      color: var(--gold);
    }
    .ab-list .ic svg { width: 16px; height: 16px; }

    /* Right — form */
    .auth-form {
      padding: 3rem 2.75rem;
      display: flex;
      flex-direction: column;
      justify-content: center;
    }

    .auth-form h1 {
      font-family: 'Space Grotesk', sans-serif;
      font-size: 1.85rem;
      font-weight: 700;
      letter-spacing: -0.02em;
      line-height: 1.2;
      margin-bottom: .35rem;
    }
    .auth-form .lead { color: var(--muted); font-size: .95rem; margin-bottom: 1.75rem; }

    .alert {
      display: flex;
      align-items: flex-start;
      gap: .6rem;
      font-size: .9rem;
      font-weight: 500;
      padding: .8rem 1rem;
      border-radius: 12px;
      margin-bottom: 1.25rem;
      line-height: 1.45;
    }
    .alert svg { width: 18px; height: 18px; flex-shrink: 0; margin-top: 1px; }
    .alert-error { background: #FEF2F2; border: 1px solid #FECACA; color: #991B1B; }
    .alert-success { background: #F0FDF4; border: 1px solid #BBF7D0; color: #166534; }

    .field { margin-bottom: 1.1rem; }
    .field label { display: block; font-size: .85rem; font-weight: 600; color: var(--ink); margin-bottom: .45rem; }

    .input-wrap { position: relative; }
    .input-wrap .lead-ic {
      position: absolute;
      left: .95rem;
      top: 50%;
      transform: translateY(-50%);
      width: 18px; height: 18px;
      color: #8A97A8;
      pointer-events: none;
    }

    .input {
      width: 100%;
      height: 50px;
      padding: 0 1rem 0 2.75rem;
      border: 1.5px solid var(--line);
      border-radius: 12px;
      background: #F8FAFC;
      font: 400 16px/1 'Inter', sans-serif; /* 16px: iOS won't zoom on focus */
      color: var(--ink);
      transition: border-color .2s, box-shadow .2s, background .2s;
      -webkit-appearance: none;
      appearance: none;
    }
    .input::placeholder { color: #9AA6B5; }
    .input:focus { outline: none; border-color: var(--navy); background: #fff; box-shadow: 0 0 0 4px rgba(11, 37, 69, .08); }
    .input-wrap:focus-within .lead-ic { color: var(--navy); }
    .password-wrap .input { padding-right: 3.25rem; }

    .toggle-pw {
      position: absolute;
      right: .35rem;
      top: 50%;
      transform: translateY(-50%);
      width: 42px; height: 42px;
      border: 0;
      background: none;
      border-radius: 10px;
      color: #8A97A8;
      cursor: pointer;
      display: flex; align-items: center; justify-content: center;
      touch-action: manipulation;
    }
    .toggle-pw:hover { color: var(--navy); background: var(--tint); }
    .toggle-pw svg { width: 20px; height: 20px; }
    .toggle-pw .eye-open { display: none; }
    .toggle-pw.is-visible .eye-slash { display: none; }
    .toggle-pw.is-visible .eye-open { display: block; }

    .form-row {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 1rem;
      margin: .25rem 0 1.5rem;
      flex-wrap: wrap;
    }

    .remember-me { display: inline-flex; align-items: center; gap: .5rem; font-size: .9rem; color: var(--muted); cursor: pointer; min-height: 32px; }
    .remember-me input { width: 18px; height: 18px; accent-color: var(--navy); cursor: pointer; }

    .link-btn {
      background: none; border: 0; padding: .25rem 0;
      font: 600 .9rem 'Inter', sans-serif;
      color: var(--navy);
      cursor: pointer;
    }
    .link-btn:hover { text-decoration: underline; }

    .btn {
      width: 100%;
      height: 52px;
      display: inline-flex; align-items: center; justify-content: center; gap: .5rem;
      border: 0;
      border-radius: 12px;
      background: var(--navy);
      color: #fff;
      font: 700 1rem/1 'Space Grotesk', sans-serif;
      cursor: pointer;
      box-shadow: 0 4px 14px rgba(11, 37, 69, .25);
      transition: background .2s, box-shadow .2s;
      touch-action: manipulation;
    }
    .btn svg { width: 18px; height: 18px; }
    .btn:hover { background: var(--navy-light); box-shadow: 0 6px 20px rgba(11, 37, 69, .3); }
    .btn:disabled { opacity: .6; cursor: not-allowed; box-shadow: none; }

    .help-note {
      margin-top: 1.5rem;
      padding-top: 1.25rem;
      border-top: 1px solid var(--line);
      text-align: center;
      font-size: .9rem;
      color: var(--muted);
    }
    .help-note a { color: var(--navy); font-weight: 600; text-decoration: none; }
    .help-note a:hover { text-decoration: underline; }

    .page-footer {
      text-align: center;
      font-size: .8rem;
      color: #A9B5C8;
      background: #0f172a;
      padding: .9rem 20px calc(.9rem + var(--safe-b));
    }

    /* ─── FORGOT-PASSWORD MODAL ─── */
    .modal-overlay {
      position: fixed;
      inset: 0;
      z-index: 9999;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 1rem;
      background: rgba(6, 26, 51, .55);
      -webkit-backdrop-filter: blur(6px);
      backdrop-filter: blur(6px);
      opacity: 0;
      pointer-events: none;
      transition: opacity .25s;
    }
    .modal-overlay.open { opacity: 1; pointer-events: all; }

    .modal-box {
      position: relative;
      width: 100%;
      max-width: 440px;
      max-height: calc(100dvh - 2rem);
      overflow-y: auto;
      background: #fff;
      border-radius: 20px;
      padding: 2.25rem 2rem 1.75rem;
      box-shadow: 0 24px 60px rgba(6, 26, 51, .3);
      transform: translateY(16px) scale(.98);
      transition: transform .28s cubic-bezier(.22, 1, .36, 1);
    }
    .modal-overlay.open .modal-box { transform: none; }

    .modal-title { font-family: 'Space Grotesk', sans-serif; font-size: 1.4rem; font-weight: 700; margin-bottom: .35rem; }
    .modal-desc { font-size: .925rem; color: var(--muted); margin-bottom: 1.5rem; }

    .resend-note { text-align: center; font-size: .8rem; color: var(--muted); margin-top: .75rem; display: none; }

    .back-to-login {
      display: block;
      width: 100%;
      margin-top: 1rem;
      padding: .6rem;
      background: none;
      border: 0;
      font: 600 .9rem 'Inter', sans-serif;
      color: var(--navy);
      cursor: pointer;
    }
    .back-to-login:hover { text-decoration: underline; }

    /* ─── RESPONSIVE ─── */
    @media (max-width: 860px) {
      .auth-card { grid-template-columns: 1fr; max-width: 520px; }
      .auth-brand { padding: 2rem 1.75rem; gap: 1.25rem; }
      .ab-list { display: none; }
      .ab-mid p { max-width: none; }
      .auth-brand-bg { width: 240px; right: -60px; bottom: -70px; }
      .auth-form { padding: 2rem 1.75rem 2.25rem; }
    }

    @media (max-width: 640px) {
      .topbar { display: none; }
      .site-nav { height: 60px; }
      .brand-logo { width: 36px; height: 36px; }
      .brand-sub { display: none; }
      .back-link span { display: none; }
      .back-link { width: 44px; padding: 0; justify-content: center; }

      .auth { padding: 0; align-items: stretch; }
      .auth-card { border-radius: 0; border: 0; box-shadow: none; max-width: none; }
      .auth-brand { padding: 1.75rem max(20px, calc(16px + var(--safe-l))) 2rem; }
      .ab-top { display: none; }
      .ab-mid h2 { font-size: 1.65rem; margin-bottom: .5rem; }
      .ab-mid p { font-size: .9rem; }
      .auth-form {
        padding: 1.75rem max(20px, calc(16px + var(--safe-l))) 2.5rem;
        border-radius: 20px 20px 0 0;
        margin-top: -16px;
        position: relative;
        background: var(--surface);
      }
      .auth-form h1 { font-size: 1.5rem; }
      .auth-form .lead { margin-bottom: 1.35rem; }

      .modal-overlay { align-items: flex-end; padding: 0; }
      .modal-box {
        max-width: none;
        border-radius: 20px 20px 0 0;
        padding: 2rem 20px calc(1.5rem + var(--safe-b));
        max-height: 92dvh;
        transform: translateY(100%);
      }
    }

    @media (prefers-reduced-motion: reduce) {
      *, *::before, *::after { transition: none !important; }
    }
    /* Brand panel: logo + name + one line, centered */
    .auth-brand { justify-content: center; }
    .ab-center { text-align: center; display: flex; flex-direction: column; align-items: center; }
    .ab-center .ab-logo { width: 96px; height: 96px; border-radius: 24px; padding: 8px; margin-bottom: 1.25rem; box-shadow: 0 10px 30px rgba(0, 0, 0, .25); }
    .ab-center h2 { font-family: 'Space Grotesk', sans-serif; font-size: clamp(1.75rem, 1.2rem + 1.6vw, 2.25rem); font-weight: 700; letter-spacing: -0.02em; line-height: 1.15; color: #fff; }
    .ab-divider { width: 44px; height: 3px; border-radius: 2px; background: var(--gold); margin: 1rem auto; }
    .ab-center p { color: #C3CDDD; font-size: 1rem; max-width: 20rem; }
    @media (max-width: 640px) {
      .ab-center .ab-logo { width: 64px; height: 64px; border-radius: 18px; padding: 6px; margin-bottom: .85rem; }
      .ab-center h2 { font-size: 1.5rem; }
      .ab-divider { margin: .7rem auto; }
      .ab-center p { font-size: .9rem; }
    }
  </style>
  <?php include __DIR__ . '/includes/page_transition.php'; ?>
<?php include __DIR__ . '/includes/pwa_head.php'; ?>
</head>

<body>

  <div class="topbar side-pad">
    <a href="helpdesk.php">Campus Helpdesk</a>
    <a href="FAQ.php">FAQ</a>
    <a href="contact.php">Contact Us</a>
  </div>

  <nav class="site-nav side-pad">
    <a href="index.php" class="brand">
      <?php if ($__logoSrc): ?>
        <img src="<?= htmlspecialchars($__logoSrc) ?>" alt="Arandia College logo" class="brand-logo" width="40" height="40">
      <?php else: ?>
        <div class="brand-logo brand-fallback">AC</div>
      <?php endif; ?>
      <span class="brand-text">
        <span class="brand-name">Arandia College</span>
      </span>
    </a>
    <a href="index.php" class="back-link" aria-label="Back to Home">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
      <span>Back to Home</span>
    </a>
  </nav>

  <!-- ══════════════════════════════════════════════════════════
       Forgot-Password Modal
  ══════════════════════════════════════════════════════════ -->
  <div class="modal-overlay" id="fpModal" role="dialog" aria-modal="true" aria-labelledby="fpTitle">
    <div class="modal-box">
      <div class="modal-title" id="fpTitle">Forgot your password?</div>
      <p class="modal-desc">Enter the email linked to your account and we'll send you a reset link.</p>

      <div id="fpAlert" aria-live="polite">
      <?php if ($fpMsg): ?>
        <div class="alert alert-success"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg> <span><?= htmlspecialchars($fpMsg) ?></span></div>
      <?php endif; ?>
      <?php if ($fpError): ?>
        <div class="alert alert-error"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg> <span><?= htmlspecialchars($fpError) ?></span></div>
      <?php endif; ?>
      </div>

      <form method="POST" action="login.php#fpModal" id="fpForm">
        <input type="hidden" name="forgot_password" value="1">
        <div class="field">
          <label for="fp_email">Email address</label>
          <div class="input-wrap">
            <svg class="lead-ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
            <input class="input" type="email" id="fp_email" name="fp_email" placeholder="Email"
              value="<?= htmlspecialchars($_POST['fp_email'] ?? '') ?>" autocomplete="email" inputmode="email" required>
          </div>
        </div>
        <button type="submit" class="btn" id="fpSubmitBtn">Send Reset Link →</button>
        <p class="resend-note" id="fpResendNote"></p>
      </form>

      <button class="back-to-login" type="button" onclick="backToSignIn()">← Back to Sign In</button>
    </div>
  </div>

  <!-- ══════════════════════════════════════════════════════════
       Sign in
  ══════════════════════════════════════════════════════════ -->
  <main class="auth side-pad">
    <div class="auth-card">

      <aside class="auth-brand">
        <?php if ($__logoSrc): ?>
          <img src="<?= htmlspecialchars($__logoSrc) ?>" alt="" class="auth-brand-bg" aria-hidden="true">
        <?php endif; ?>

        <div class="ab-center">
          <?php if ($__logoSrc): ?>
            <img src="<?= htmlspecialchars($__logoSrc) ?>" alt="Arandia College logo" class="ab-logo">
          <?php endif; ?>
          <h2>Arandia College</h2>
          <div class="ab-divider"></div>
          <p>eLMS for Senior High School &amp; High School students and teachers.</p>
        </div>
      </aside>

      <section class="auth-form">
        <h1>Sign in</h1>
        <p class="lead">Use your school email address.</p>

        <?php if ($error): ?>
          <div class="alert alert-error" role="alert"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg> <span><?= htmlspecialchars($error) ?></span></div>
        <?php endif; ?>

        <form method="POST" action="login.php">
          <div class="field">
            <label for="login">Email</label>
            <div class="input-wrap">
              <svg class="lead-ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
              <input class="input" type="email" id="login" name="login" placeholder="Email"
                value="<?= htmlspecialchars($flashLogin !== '' ? $flashLogin : ($lockedEmail !== '' ? $lockedEmail : $rememberedLogin)) ?>" autocomplete="username" autocapitalize="none" autocorrect="off" spellcheck="false" required>
            </div>
          </div>

          <div class="field">
            <label for="password">Password</label>
            <div class="input-wrap password-wrap">
              <svg class="lead-ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
              <input class="input" type="password" name="password" id="password" placeholder="Enter your password"
                autocomplete="current-password" required<?= $rmStandin !== '' ? ' value="' . htmlspecialchars($rmStandin) . '" data-remembered="1"' : '' ?>>
              <button type="button" class="toggle-pw" id="togglePwBtn" onclick="togglePassword()" aria-label="Show password">
                <svg class="eye-open" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                <svg class="eye-slash" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
              </button>
            </div>
          </div>

          <div class="form-row">
            <label class="remember-me"><input type="checkbox" name="remember" <?= ($flashRemember ?? (bool) $rememberedLogin) ? 'checked' : '' ?>> Remember me</label>
            <button type="button" class="link-btn" onclick="openModal()">Forgot password?</button>
          </div>

          <button type="submit" class="btn" id="signInBtn"<?= $lockSeconds > 0 ? ' disabled data-lock="' . (int) $lockSeconds . '"' : '' ?>>
            Sign In
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
          </button>
        </form>

        <p class="help-note">Having trouble signing in? <a href="contact.php">Contact the Admin.</a></p>
      </section>

    </div>
  </main>

  <footer class="page-footer">© <?= date('Y') ?> <?= $SYS_NAME_H ?> — All rights reserved.</footer>

  <script>
    // Back / Forward showed this page from the browser's memory: load it again so
    // the server cancels a half-finished OTP sign-in and shows the current state.
    window.addEventListener('pageshow', function (e) { if (e.persisted) location.reload(); });

    // ── Toggle password visibility ──────────────────────────
    // ── Remember me: the filled password box holds a stand-in, not the real
    // password. Typing a new one, or changing the username, clears it.
    (function () {
      const pw = document.getElementById('password');
      const login = document.getElementById('login');
      if (!pw || !pw.dataset.remembered) return;
      const standin = pw.value;
      const startLogin = login ? login.value : '';
      function forget() { if (pw.dataset.remembered) { delete pw.dataset.remembered; pw.value = ''; } }
      pw.addEventListener('focus', function () { if (pw.dataset.remembered) pw.select(); });
      pw.addEventListener('keydown', function (e) {
        if (!pw.dataset.remembered) return;
        if (e.key.length === 1 || e.key === 'Backspace' || e.key === 'Delete') forget();
      });
      pw.addEventListener('input', function () { if (pw.dataset.remembered && pw.value !== standin) delete pw.dataset.remembered; });
      if (login) login.addEventListener('input', function () { if (login.value !== startLogin) forget(); });
    })();

    function togglePassword() {
      const pw  = document.getElementById('password');
      const btn = document.getElementById('togglePwBtn');
      // Remembered device: the box holds a stand-in, not the real password, so there is
      // nothing to show. Clear it so the user can type the real one (then the eye works).
      if (pw.dataset.remembered) {
        delete pw.dataset.remembered;
        pw.value = '';
        pw.placeholder = 'Type your password to show it';
        pw.type = 'text';
        btn.classList.add('is-visible');
        btn.setAttribute('aria-label', 'Hide password');
        pw.focus();
        return;
      }
      const showing = pw.type === 'text';
      pw.type = showing ? 'password' : 'text';
      btn.classList.toggle('is-visible', !showing);
      btn.setAttribute('aria-label', showing ? 'Show password' : 'Hide password');
    }

    // ── Locked after 3 failed attempts: count down, then allow signing in again ──
    (function () {
      const btn = document.getElementById('signInBtn');
      let left = btn ? parseInt(btn.dataset.lock || '0', 10) : 0;
      if (!left) return;
      const label = btn.firstChild;   // the "Sign In" text node
      const alertText = document.querySelector('.auth-form .alert-error span');
      const endsAt = Date.now() + left * 1000;   // fixed end time: stays correct even if the tab sleeps
      function fmt(s) { return Math.floor(s / 60) + ':' + String(s % 60).padStart(2, '0'); }
      function tick() {
        left = Math.max(0, Math.ceil((endsAt - Date.now()) / 1000));
        if (left <= 0) {
          clearInterval(t);
          btn.disabled = false;
          label.textContent = ' Sign In ';
          if (alertText) alertText.textContent = 'You can try signing in again.';
          return;
        }
        label.textContent = ' Try again in ' + fmt(left) + ' ';
      }
      tick();
      const t = setInterval(tick, 1000);
    })();

    // ── Forgot-password modal ───────────────────────────────
    // • Submits with fetch(): the page never reloads, so the modal cannot flash/disappear.
    // • Once the link is sent, the modal can ONLY be closed with "← Back to Sign In"
    //   (Esc and clicking the dark area are ignored in that state).
    // • "Back to Sign In" resets it (message, email, errors) so the next open is clean.
    const modal       = document.getElementById('fpModal');
    const fpForm      = document.getElementById('fpForm');
    const fpEmail     = document.getElementById('fp_email');
    const fpBtn       = document.getElementById('fpSubmitBtn');
    const fpAlert     = document.getElementById('fpAlert');
    const resendNote  = document.getElementById('fpResendNote');

    const COOLDOWN    = 60;                 // seconds between reset e-mails
    const STORAGE_KEY = 'fpLastSent';
    const ICON_OK  = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>';
    const ICON_ERR = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>';

    let fpSent    = <?= $fpMsg ? 'true' : 'false' ?>;   // a reset link was sent in this modal session
    let fpSending = false;
    let fpTimer   = null;
    let fpAbort   = null;

    function showAlert(type, message) {
      fpAlert.innerHTML = '<div class="alert alert-' + type + '" role="' + (type === 'error' ? 'alert' : 'status') + '">'
        + (type === 'error' ? ICON_ERR : ICON_OK) + ' <span></span></div>';
      fpAlert.querySelector('span').textContent = message;   // textContent: never inject HTML
    }
    function clearAlert() { fpAlert.innerHTML = ''; }

    function idleLabel() { return fpSent ? 'Resend Link →' : 'Send Reset Link →'; }

    const OPEN_KEY = 'fpModalOpen';   // remembers "modal is open" so a refresh keeps it open

    function openModal() {
      modal.classList.add('open');
      document.body.style.overflow = 'hidden';
      try { sessionStorage.setItem(OPEN_KEY, '1'); } catch (e) {}
      setTimeout(function () { if (modal.classList.contains('open')) fpEmail.focus(); }, 150);
    }

    function closeModal() {
      modal.classList.remove('open');
      document.body.style.overflow = '';
      try { sessionStorage.removeItem(OPEN_KEY); } catch (e) {}
    }

    // The ONLY way to close this modal: "← Back to Sign In".
    // (Clicking the dark area, Esc, or anything else does nothing.)
    function backToSignIn() {
      if (fpAbort) { fpAbort.abort(); fpAbort = null; }
      fpSending = false;
      fpSent = false;
      clearAlert();
      fpForm.reset();
      fpEmail.value = '';
      if (remainingSeconds() > 0) { startCooldown(remainingSeconds()); }
      else { stopCooldown(); fpBtn.disabled = false; fpBtn.textContent = idleLabel(); }
      closeModal();
      const signIn = document.getElementById('login');
      if (signIn) signIn.focus({ preventScroll: true });
    }

    // Clicks on the dark area stay inside the modal flow: ignored on purpose.
    // Esc is swallowed so no other script/browser handler can dismiss it either.
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && modal.classList.contains('open')) e.preventDefault();
    });

    // ── Resend cooldown (60s, survives page reloads) ────────
    function getLast() {
      try { return parseInt(localStorage.getItem(STORAGE_KEY) || '0', 10) || 0; } catch (e) { return 0; }
    }
    function remainingSeconds() {
      const elapsed = Math.floor((Date.now() - getLast()) / 1000);
      return Math.min(COOLDOWN, Math.max(0, COOLDOWN - elapsed));   // min(): ignores a clock set back in time
    }
    function stopCooldown() {
      clearInterval(fpTimer);
      fpTimer = null;
      resendNote.style.display = 'none';
    }
    function startCooldown(seconds) {
      stopCooldown();
      resendNote.style.display = 'block';
      fpBtn.disabled = true;
      const endsAt = Date.now() + seconds * 1000;   // fixed end time: stays correct even if the tab sleeps
      function tick() {
        const left = Math.max(0, Math.ceil((endsAt - Date.now()) / 1000));
        if (left <= 0) {
          stopCooldown();
          fpBtn.disabled = false;
          fpBtn.textContent = idleLabel();
          return;
        }
        fpBtn.textContent = 'Resend in ' + left + 's';
        resendNote.textContent = 'You can request another link in ' + left + 's.';
      }
      tick();
      if (fpTimer === null && Date.now() < endsAt) fpTimer = setInterval(tick, 1000);
    }
    function markSent() {
      try { localStorage.setItem(STORAGE_KEY, Date.now().toString()); } catch (e) {}
      fpSent = true;
      startCooldown(COOLDOWN);
    }

    // ── Submit without reloading the page ───────────────────
    fpForm.addEventListener('submit', async function (e) {
      e.preventDefault();
      if (fpSending || remainingSeconds() > 0) return;

      const email = fpEmail.value.trim();
      if (!email) { showAlert('error', 'Please enter your email address.'); fpEmail.focus(); return; }
      if (!fpEmail.checkValidity()) { fpEmail.reportValidity(); return; }

      fpSending = true;
      fpBtn.disabled = true;
      fpBtn.textContent = 'Sending…';
      clearAlert();
      fpAbort = ('AbortController' in window) ? new AbortController() : null;

      try {
        const res = await fetch('login.php', {
          method: 'POST',
          headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
          body: new FormData(fpForm),
          credentials: 'same-origin',
          signal: fpAbort ? fpAbort.signal : undefined
        });
        const data = await res.json();
        fpSending = false;
        fpAbort = null;
        if (data && data.ok) {
          showAlert('success', data.message);
          markSent();                       // cooldown starts only when an e-mail really went out
        } else {
          showAlert('error', (data && data.message) || 'Something went wrong. Please try again.');
          fpBtn.disabled = false;
          fpBtn.textContent = idleLabel();
        }
      } catch (err) {
        if (err && err.name === 'AbortError') return;   // user pressed Back to Sign In
        fpSending = false;
        fpAbort = null;
        showAlert('error', 'Could not reach the server. Please check your connection and try again.');
        fpBtn.disabled = false;
        fpBtn.textContent = idleLabel();
      }
    });

    // ── Initial state ───────────────────────────────────────
    if (fpSent) {
      // Fallback path (page was submitted without JS): keep the cooldown in sync.
      if (remainingSeconds() === 0) markSent();
    }
    if (remainingSeconds() > 0) startCooldown(remainingSeconds());
    else fpBtn.textContent = idleLabel();

    // Refresh (F5) while the modal is open → keep it open. Any other visit starts closed.
    (function () {
      let flagged = false, kind = '';
      try { flagged = sessionStorage.getItem(OPEN_KEY) === '1'; } catch (e) {}
      try { kind = (performance.getEntriesByType('navigation')[0] || {}).type || ''; } catch (e) {}
      if (flagged && (kind === 'reload' || kind === 'back_forward')) window.addEventListener('DOMContentLoaded', openModal);
      else { try { sessionStorage.removeItem(OPEN_KEY); } catch (e) {} }
    })();

    // Auto-open when the server rendered a message (non-JS fallback)
    <?php if ($fpMsg || $fpError): ?>
      window.addEventListener('DOMContentLoaded', openModal);
    <?php endif; ?>
  </script>
</body>

</html>