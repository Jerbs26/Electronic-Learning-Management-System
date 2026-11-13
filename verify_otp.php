<?php

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
elmsEnsureRoles($conn);

// E-mail settings come from config/secrets.php
require_once __DIR__ . '/includes/mailer.php';

// ── Rules for the code ───────────────────────────────────────
const OTP_MAX_FAILS       = 5;    // wrong codes allowed, then the sign-in is cancelled
const OTP_RESEND_COOLDOWN = 30;   // seconds between "Resend OTP"

/** Send the browser back to this page with a GET (Post/Redirect/Get). */
function otpRedirectSelf(string $error = '', string $success = ''): void
{
    $_SESSION['otp_flash'] = ['error' => $error, 'success' => $success];
    header('Location: verify_otp.php', true, 303);
    exit;
}

// ── Guard: must have a pending OTP session ───────────────────
if (empty($_SESSION['otp_pending_user_id'])) {
    header('Location: login.php', true, 303);
    exit;
}

// ── Already fully logged in? Redirect ───────────────────────
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
}

$userId = (int) $_SESSION['otp_pending_user_id'];
$emailHint = $_SESSION['otp_pending_email_hint'] ?? 'your registered email';

$error = '';
$success = '';
// Message from the last POST (shown once)
if (!empty($_SESSION['otp_flash']) && is_array($_SESSION['otp_flash'])) {
    $error   = (string) ($_SESSION['otp_flash']['error'] ?? '');
    $success = (string) ($_SESSION['otp_flash']['success'] ?? '');
}
unset($_SESSION['otp_flash']);

// ── Resend OTP handler ───────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['resend_otp'])) {
    $wait = OTP_RESEND_COOLDOWN - (time() - (int) ($_SESSION['otp_last_sent_at'] ?? 0));
    if ($wait > 0) {
        otpRedirectSelf('Please wait ' . $wait . ' second' . ($wait === 1 ? '' : 's') . ' before requesting a new code.');
    }
    // Fetch user email
    $stmt = $conn->prepare("SELECT email, first_name FROM users WHERE id = ? LIMIT 1");
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $resendUser = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($resendUser) {
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $otpHash = password_hash($otp, PASSWORD_DEFAULT);

        $upd = $conn->prepare(
            "INSERT INTO login_otps (user_id, otp_hash, expires_at, used)
       VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 10 MINUTE), 0)
       ON DUPLICATE KEY UPDATE otp_hash = VALUES(otp_hash),
         expires_at = DATE_ADD(NOW(), INTERVAL 10 MINUTE), used = 0"
        );
        $upd->bind_param('is', $userId, $otpHash);
        $upd->execute();
        $upd->close();

        // Settings come from config/secrets.php via includes/mailer.php
          {
            $mailSubject = $SYS_NAME . ' — Your New Login OTP';

            $name = htmlspecialchars($resendUser['first_name']);
            $mailBody = '
<!DOCTYPE html><html><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Your new login code</title></head>
<body style="margin:0;padding:0;background:#f4f7fc;font-family:Segoe UI,Helvetica,Arial,sans-serif;">
<div style="display:none;max-height:0;overflow:hidden;opacity:0;color:#f4f7fc;">Your code is ' . $otp . ' &mdash; it expires in 10 minutes.</div>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f4f7fc;"><tr><td align="center" style="padding:40px 14px;">
<table role="presentation" width="540" cellpadding="0" cellspacing="0" style="width:100%;max-width:540px;background:#ffffff;border-radius:6px;border-top:6px solid #003087;box-shadow:0 1px 3px rgba(0,48,135,0.12);">
<tr><td align="center" style="padding:38px 36px 8px;">
<div style="font-family:Segoe UI,Helvetica,Arial,sans-serif;font-size:12px;letter-spacing:3px;text-transform:uppercase;color:#003087;font-weight:700;">' . $SYS_NAME_H . '</div>
<div style="width:46px;height:3px;background:#003087;margin:12px auto 22px;"></div>
<div style="font-size:30px;line-height:1.25;color:#10204a;font-weight:700;">Your new login code</div>
<div style="font-family:Segoe UI,Helvetica,Arial,sans-serif;font-size:15px;line-height:1.7;color:#59627a;margin-top:14px;">Hi <strong style="color:#10204a;">' . $name . '</strong>, you asked for a fresh code. Use this one to finish signing in &mdash; the old code no longer works:</div>
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
<tr><td style="padding:22px 36px 30px;font-family:Segoe UI,Helvetica,Arial,sans-serif;font-size:13px;line-height:1.75;color:#7a8299;text-align:center;"><strong style="color:#10204a;">Keep it private.</strong> Never share this code with anyone.<br>Didn&rsquo;t request it? You can safely ignore this email.</td></tr>
<tr><td style="background:#10204a;padding:18px 30px;text-align:center;font-family:Segoe UI,Helvetica,Arial,sans-serif;font-size:11px;line-height:1.7;color:#9fb0dc;">' . $SYS_NAME_H . ' &middot; ' . $SYS_LEVEL_H . ' Learning Portal<br>This is an automated message &mdash; please do not reply.</td></tr>
</table></td></tr></table></body></html>';

            $mailText = "Hi {$name},\n\nYour new {$SYS_NAME} OTP is: {$otp}\n\nExpires in 10 minutes.\n\n— {$SYS_NAME}";
            $resendErr = elmsSendMail($resendUser['email'], $mailSubject, $mailBody, $mailText);
            if ($resendErr === '') {
                $_SESSION['otp_last_sent_at'] = time();
                otpRedirectSelf('', 'A new OTP has been sent to your email.');
            }
            otpRedirectSelf('We could not send a new code right now. Please try again in a few minutes.'
                . (elmsIsLocalRequest() ? ' (Setup: ' . $resendErr . ')' : ''));
          }
    }
    // Account no longer there: start over
    elmsCancelPendingOtp($conn);
    header('Location: login.php', true, 303);
    exit;
}

// ── OTP verification handler ─────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verify_otp'])) {
    $digitsIn = $_POST['otp_digits'] ?? [];
    $enteredOtp = is_array($digitsIn)
        ? trim(implode('', array_map(fn($d) => is_string($d) ? $d : '', $digitsIn)))
        : '';

    if (strlen($enteredOtp) !== 6 || !ctype_digit($enteredOtp)) {
        $error = 'Please enter the complete 6-digit OTP.';
    } else {
        // Fetch OTP record
        $stmt = $conn->prepare(
            "SELECT otp_hash, expires_at, used, (expires_at < NOW()) AS is_expired FROM login_otps
       WHERE user_id = ? LIMIT 1"
        );
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $otpRecord = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$otpRecord) {
            $error = 'No OTP found. Please go back and log in again.';
        } elseif ($otpRecord['used']) {
            $error = 'This OTP has already been used. Please request a new one.';
        } elseif ((int) $otpRecord['is_expired'] === 1) {   // compared in MySQL, so PHP/MySQL time zones cannot disagree
            $error = 'Your OTP has expired. Please request a new one.';
        } elseif (!password_verify($enteredOtp, $otpRecord['otp_hash'])) {
            $fails = (int) ($_SESSION['otp_pending_fails'] ?? 0) + 1;
            $_SESSION['otp_pending_fails'] = $fails;
            if ($fails >= OTP_MAX_FAILS) {
                // Too many wrong codes: this sign-in is cancelled, sign in again
                elmsCancelPendingOtp($conn);
                $_SESSION['login_flash'] = ['error' => 'Too many wrong codes. Please sign in again to get a new code.'];
                header('Location: login.php', true, 303);
                exit;
            }
            $left = OTP_MAX_FAILS - $fails;
            $error = 'Invalid OTP. ' . $left . ' attempt' . ($left === 1 ? '' : 's') . ' left.';
        } else {
            // ✅ OTP is correct — mark as used
            $upd = $conn->prepare("UPDATE login_otps SET used = 1 WHERE user_id = ?");
            $upd->bind_param('i', $userId);
            $upd->execute();
            $upd->close();

            // ✅ Complete the login
            session_regenerate_id(true);
            $_SESSION['user_id'] = $userId;
            $_SESSION['username'] = $_SESSION['otp_pending_username'];
            $_SESSION['role'] = $_SESSION['otp_pending_role'];
            $_SESSION['first_name'] = $_SESSION['otp_pending_first_name'];
            $_SESSION['last_name'] = $_SESSION['otp_pending_last_name'];

            // ── One device per account: this device becomes the only one;
            //    any other device signed in to this account is signed out ──
            require_once __DIR__ . '/includes/single_session.php';
            ssClaimDevice($conn, (int) $userId);

            // ── New account? It must set its own password first ──
            require_once __DIR__ . '/includes/force_password.php';
            if (fpMustChange($conn, (int) $userId)) {
                $_SESSION['must_change_password'] = 1;
                // First sign-in: accept the Terms and Conditions before changing the password
                if (fpTermsNeeded($conn, (int) $userId)) $_SESSION['terms_pending'] = 1;
            }

            // ── Remember Me: set or clear (includes/remember_me.php) ─────
            require_once __DIR__ . '/includes/remember_me.php';
            if (!empty($_SESSION['otp_pending_remember'])) {
                setcookie('remember_login', $_SESSION['otp_pending_remember'], time() + (30 * 24 * 60 * 60), '/', '', false, true);
                rmForgetDevice($conn);   // email only: no device key is kept, the password is always asked
            } else {
                setcookie('remember_login', '', time() - 3600, '/');
                rmForgetDevice($conn);
            }

            // Clean up OTP session vars
            foreach (ELMS_OTP_PENDING_KEYS as $k) {
                unset($_SESSION[$k]);
            }

            $role = $_SESSION['role'];
            if (!empty($_SESSION['must_change_password'])) {
                header('Location: ' . (!empty($_SESSION['terms_pending']) ? 'terms.php' : 'change_password.php'), true, 303);
                exit;
            }
            if ($role === 'Admin' || $role === 'Head Admin') {
                header('Location: admin/dashboard.php', true, 303);
                exit;
            }
            if ($role === 'Teacher') {
                header('Location: teacher/dashboard.php', true, 303);
                exit;
            }
            if ($role === 'Student') {
                header('Location: student/dashboard.php', true, 303);
                exit;
            }
            if ($role === 'Admission') {
                header('Location: admission/dashboard.php', true, 303);
                exit;
            }
            // No known account type: don't leave a half signed-in session behind
            $_SESSION = [];
            session_regenerate_id(true);
            header('Location: login.php?norole=1', true, 303);
            exit;
        }
    }
    // Wrong / expired code: show the message through a GET, so Refresh and Back
    // never re-send the form ("Confirm Form Resubmission").
    otpRedirectSelf($error);
}

// Any other POST (old form, missing fields): just show the page again
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    otpRedirectSelf();
}

// ── Real time left, from the database (so Refresh doesn't restart the timer) ──
$otpSecondsLeft = 0;
$st = $conn->prepare("SELECT GREATEST(TIMESTAMPDIFF(SECOND, NOW(), expires_at), 0) AS secs, used
                      FROM login_otps WHERE user_id = ? LIMIT 1");
$st->bind_param('i', $userId);
$st->execute();
$otpRow = $st->get_result()->fetch_assoc();
$st->close();
if ($otpRow && !(int) $otpRow['used']) {
    $otpSecondsLeft = (int) $otpRow['secs'];
}
$resendWait = max(0, OTP_RESEND_COOLDOWN - (time() - (int) ($_SESSION['otp_last_sent_at'] ?? 0)));
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify OTP — <?= $SYS_NAME_H ?></title>
    <link rel="icon" type="image/png" href="picture/logo.png">
    <link
        href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&family=Inter:wght@400;500;600;700&family=Space+Grotesk:wght@600;700&display=swap"
        rel="stylesheet">
    <style>
        :root {
            --primary: #003087;
            --primary-dark: #001a4d;
            --primary-light: #0044cc;
            --accent: #FFD700;
            --text-dark: #0f172a;
            --text-light: #64748b;
            --bg-light: #f8fafc;
            --white: #ffffff;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, .05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, .1), 0 2px 4px -1px rgba(0, 0, 0, .06);
            --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, .1), 0 10px 10px -5px rgba(0, 0, 0, .04);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0
        }

        html {
            scroll-behavior: smooth
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg-light);
            color: var(--text-dark);
            line-height: 1.5;
            overflow-x: hidden;
            min-height: 100vh;
            display: flex;
            flex-direction: column
        }

        /* TOPBAR */
        .topbar {
            background: #061A33;   /* same as login.php */
            color: #C3CDDD;
            font-size: .75rem;
            padding: .5rem 5%;
            display: flex;
            justify-content: flex-end;
            gap: 1rem;
            font-weight: 500
        }

        .topbar a {
            color: #C3CDDD;
            text-decoration: none;
            transition: color .2s
        }

        .topbar a:hover {
            color: #FFC53D
        }

        /* NAV */
        nav {
            background: rgba(255, 255, 255, .95);
            backdrop-filter: blur(12px);
            box-shadow: var(--shadow-sm);
            position: sticky;
            top: 0;
            z-index: 1000;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 5%;
            height: 80px;
            border-bottom: 1px solid rgba(0, 0, 0, .05)
        }

        .nav-brand {
            display: flex;
            align-items: center;
            gap: 1rem;
            text-decoration: none
        }

        .nav-logo {
            width: 50px;
            height: 50px;
            object-fit: contain
        }

        .nav-brand-text {
            line-height: 1.1
        }

        .nav-brand-text strong {
            display: block;
            font-family: 'Nunito', sans-serif;
            font-size: 1.1rem;
            font-weight: 900;
            color: var(--primary);
            letter-spacing: -.5px
        }

        .nav-brand-text span {
            display: block;
            font-size: .75rem;
            color: var(--text-light);
            font-weight: 500
        }

        /* Buttons — same style as login.php */
        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            min-height: 42px;
            padding: 0 1rem;
            border-radius: 12px;
            border: 1px solid #0B2545;
            background: #0B2545;
            color: #fff;
            font: 600 .9rem/1 'Inter', sans-serif;
            text-decoration: none;
            box-shadow: 0 2px 8px rgba(11, 37, 69, .18);
            transition: background-color .2s, border-color .2s, box-shadow .2s;
            white-space: nowrap
        }

        .back-btn svg { width: 16px; height: 16px }

        .back-btn:hover {
            background: #123A6B;
            border-color: #123A6B;
            color: #fff;
            box-shadow: 0 4px 12px rgba(11, 37, 69, .25)
        }

        /* WRAPPER */
        .otp-wrapper {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2.5rem 1rem;
            background: linear-gradient(135deg, #e8f0ff 0%, #f0f2f5 50%, #fffbe6 100%)
        }

        .otp-card {
            background: var(--white);
            border-radius: 20px;
            box-shadow: var(--shadow-xl);
            padding: 2.5rem 2rem;
            width: 100%;
            max-width: 440px;
            text-align: center
        }

        .otp-icon {
            font-size: 3rem;
            margin-bottom: .75rem
        }

        .otp-title {
            font-family: 'Nunito', sans-serif;
            font-size: 1.6rem;
            font-weight: 900;
            color: var(--primary);
            margin-bottom: .4rem
        }

        .otp-desc {
            font-size: .9rem;
            color: var(--text-light);
            margin-bottom: .25rem
        }

        .otp-email {
            font-size: .85rem;
            color: var(--primary);
            font-weight: 600;
            margin-bottom: 1.75rem;
            word-break: break-all
        }

        /* OTP Input Boxes */
        .otp-inputs {
            display: flex;
            gap: .6rem;
            justify-content: center;
            margin-bottom: 1.5rem
        }

        .otp-inputs input {
            width: 48px;
            height: 58px;
            text-align: center;
            font-size: 1.5rem;
            font-weight: 700;
            font-family: 'Nunito', sans-serif;
            border: 2px solid #d1d9f0;
            border-radius: 10px;
            outline: none;
            transition: border-color .2s, box-shadow .2s;
            color: var(--primary);
            background: #f9faff
        }

        .otp-inputs input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(0, 48, 135, .15)
        }

        .otp-inputs input.filled {
            border-color: var(--primary-light);
            background: #eef3ff
        }

        /* Buttons */
        .btn-verify {
            width: 100%;
            height: 52px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: .5rem;
            border: 0;
            border-radius: 12px;
            background: #0B2545;
            color: #fff;
            font: 700 1rem/1 'Space Grotesk', sans-serif;
            cursor: pointer;
            box-shadow: 0 4px 14px rgba(11, 37, 69, .25);
            transition: background .2s, box-shadow .2s;
            touch-action: manipulation;
            margin-bottom: 1rem
        }

        .btn-verify svg { width: 18px; height: 18px }

        .btn-verify:hover {
            background: #123A6B;
            box-shadow: 0 6px 20px rgba(11, 37, 69, .3)
        }

        .btn-verify:disabled {
            opacity: .6;
            cursor: not-allowed;
            box-shadow: none
        }

        .resend-row {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: .5rem;
            font-size: .85rem;
            color: var(--text-light)
        }

        .btn-resend {
            background: none;
            border: 0;
            padding: .25rem 0;
            font: 600 .9rem 'Inter', sans-serif;
            color: #0B2545;
            cursor: pointer
        }

        .btn-resend:hover:not(:disabled) { text-decoration: underline }

        .btn-resend:disabled {
            color: #9AA6B5;
            cursor: not-allowed
        }



        /* Alerts */
        .alert {
            padding: .8rem 1rem;
            border-radius: 10px;
            font-size: .875rem;
            margin-bottom: 1rem;
            text-align: left
        }

        .alert-error {
            background: #fff0f0;
            color: #c0392b;
            border: 1px solid #fbd5d5
        }

        .alert-success {
            background: #f0fff4;
            color: #1a7f4b;
            border: 1px solid #b7f0d1
        }

        /* Timer */
        .otp-timer {
            font-size: .8rem;
            color: var(--text-light);
            margin-bottom: 1.25rem
        }

        .otp-timer span {
            font-weight: 700;
            color: var(--primary)
        }

        /* Footer */
        .page-footer {
            text-align: center;
            padding: 1.25rem;
            font-size: .75rem;
            color: var(--text-light);
            border-top: 1px solid #e2e8f0;
            background: var(--white)
        }

        @media(max-width:480px) {
            .otp-inputs input {
                width: 40px;
                height: 50px;
                font-size: 1.2rem
            }

            .otp-card {
                padding: 2rem 1.25rem
            }
        }
    </style>
</head>

<body>

    <div class="topbar">
        <a href="helpdesk.php">Campus Helpdesk</a>
        <a href="FAQ.php">FAQ</a>
        <a href="contact.php">Contact Us</a>
    </div>

    <nav>
        <a href="index.php" class="nav-brand">
            <img src="picture/logo.png" alt="Logo" class="nav-logo" onerror="this.style.display='none'">
            <div class="nav-brand-text">
                <strong>Arandia College</strong>
            </div>
        </a>
    </nav>

    <div class="otp-wrapper">
        <div class="otp-card">

            <div class="otp-icon">📧</div>
            <div class="otp-title">Check Your Email</div>
            <p class="otp-desc">We sent a 6-digit code to</p>
            <p class="otp-email"><?= htmlspecialchars($emailHint) ?></p>

            <?php if ($error): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <!-- OTP Verify Form -->
            <form method="POST" action="verify_otp.php" id="otpForm">
                <input type="hidden" name="verify_otp" value="1">
                <div class="otp-inputs" id="otpInputs">
                    <input type="text" name="otp_digits[]" class="otp-digit" maxlength="1" inputmode="numeric"
                        pattern="[0-9]" autocomplete="one-time-code" required>
                    <input type="text" name="otp_digits[]" class="otp-digit" maxlength="1" inputmode="numeric"
                        pattern="[0-9]" required>
                    <input type="text" name="otp_digits[]" class="otp-digit" maxlength="1" inputmode="numeric"
                        pattern="[0-9]" required>
                    <input type="text" name="otp_digits[]" class="otp-digit" maxlength="1" inputmode="numeric"
                        pattern="[0-9]" required>
                    <input type="text" name="otp_digits[]" class="otp-digit" maxlength="1" inputmode="numeric"
                        pattern="[0-9]" required>
                    <input type="text" name="otp_digits[]" class="otp-digit" maxlength="1" inputmode="numeric"
                        pattern="[0-9]" required>
                </div>

                <div class="otp-timer">
                    Code expires in: <span id="timerDisplay"><?= sprintf('%02d:%02d', intdiv($otpSecondsLeft, 60), $otpSecondsLeft % 60) ?></span>
                </div>

                <button type="submit" class="btn-verify" id="verifyBtn">Verify OTP <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg></button>
            </form>

            <!-- Resend Form -->
            <div class="resend-row">
                <span>Didn't receive the code?</span>
                <form method="POST" action="verify_otp.php" style="display:inline">
                    <input type="hidden" name="resend_otp" value="1">
                    <button type="submit" class="btn-resend" id="resendBtn"<?= $resendWait > 0 ? ' disabled' : '' ?>>
                        <?php if ($resendWait > 0): ?>Resend OTP (<span id="resendTimer"><?= (int) $resendWait ?></span>s)<?php else: ?>Resend OTP<?php endif; ?>
                    </button>
                </form>
            </div>


        </div>
    </div>

    <div class="page-footer">© 2026 <?= $SYS_NAME_H ?> — <?= $SYS_LEVEL_H ?> Portal. All rights reserved.</div>

    <script>
        // ── OTP digit input auto-advance ──────────────────────────
        const digits = document.querySelectorAll('.otp-digit');

        digits.forEach((input, i) => {
            input.addEventListener('input', function () {
                this.value = this.value.replace(/\D/g, '').slice(0, 1);
                this.classList.toggle('filled', this.value !== '');
                if (this.value && i < digits.length - 1) digits[i + 1].focus();
            });

            input.addEventListener('keydown', function (e) {
                if (e.key === 'Backspace' && !this.value && i > 0) {
                    digits[i - 1].focus();
                    digits[i - 1].value = '';
                    digits[i - 1].classList.remove('filled');
                }
            });

            // Handle paste on first digit
            input.addEventListener('paste', function (e) {
                e.preventDefault();
                const pasted = (e.clipboardData || window.clipboardData).getData('text').replace(/\D/g, '').slice(0, 6);
                pasted.split('').forEach((ch, idx) => {
                    if (digits[idx]) {
                        digits[idx].value = ch;
                        digits[idx].classList.add('filled');
                    }
                });
                const next = Math.min(pasted.length, digits.length - 1);
                digits[next].focus();
            });
        });

        // Focus first input on load
        digits[0].focus();

        // Back / Forward showed this page from the browser's memory: ask the
        // server again (the sign-in may have been cancelled or finished).
        window.addEventListener('pageshow', function (e) { if (e.persisted) location.reload(); });

        // Don't send the code twice when the button is double-clicked
        document.getElementById('otpForm').addEventListener('submit', function () {
            const b = document.getElementById('verifyBtn');
            setTimeout(function () { b.disabled = true; }, 0);   // after the form is sent
        });

        // ── Expiry countdown (time left comes from the server, keeps going on Refresh) ──
        let totalSeconds = <?= (int) $otpSecondsLeft ?>;
        const timerDisplay = document.getElementById('timerDisplay');

        function updateTimer() {
            const m = Math.floor(totalSeconds / 60);
            const s = totalSeconds % 60;
            timerDisplay.textContent = `${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`;
            if (totalSeconds === 0) {
                timerDisplay.style.color = '#c0392b';
                timerDisplay.textContent = 'Expired';
                document.getElementById('verifyBtn').disabled = true;
            } else {
                totalSeconds--;
            }
        }
        updateTimer();
        setInterval(updateTimer, 1000);

        // ── Resend cooldown (time left comes from the server) ────
        let resendSeconds = <?= (int) $resendWait ?>;
        const resendBtn = document.getElementById('resendBtn');
        const resendTimer = document.getElementById('resendTimer');

        if (resendSeconds > 0 && resendTimer) {
            const resendInterval = setInterval(() => {
                resendSeconds--;
                resendTimer.textContent = resendSeconds;
                if (resendSeconds <= 0) {
                    clearInterval(resendInterval);
                    resendBtn.disabled = false;
                    resendBtn.innerHTML = 'Resend OTP';
                }
            }, 1000);
        }
    </script>

</body>

</html>