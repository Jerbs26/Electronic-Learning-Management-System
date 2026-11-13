<?php
// ============================================================
//  Arandia College eLMS — Change password (required for new accounts)
//  File: change_password.php
//
//  New accounts must choose their own password right after their first
//  sign-in (includes/force_password.php). Until then every other page sends
//  them here.
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
require_once __DIR__ . '/includes/force_password.php';
require_once __DIR__ . '/includes/password_policy.php';

if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
$uid  = (int) $_SESSION['user_id'];
$home = fpRoleHome((string) ($_SESSION['role'] ?? ''));

// Nothing to do (already changed, or an older account): go to the dashboard
if (empty($_SESSION['must_change_password'])) {
    header('Location: ' . $home);
    exit;
}

// First sign-in: the Terms and Conditions must be accepted before the password can be changed
if (!empty($_SESSION['terms_pending'])) {
    header('Location: terms.php');
    exit;
}

// ── Resolve the school logo (same self-healing lookup as login.php) ──
$__logoSrc = null;
foreach (['png', 'jpg', 'jpeg', 'PNG', 'JPG', 'JPEG'] as $__logoExt) {
    if (is_file(__DIR__ . '/picture/logo.' . $__logoExt)) {
        $__logoSrc = 'picture/logo.' . $__logoExt;
        break;
    }
}

if (empty($_SESSION['cp_csrf'])) $_SESSION['cp_csrf'] = bin2hex(random_bytes(16));
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new     = (string) ($_POST['new_password'] ?? '');
    $confirm = (string) ($_POST['confirm_password'] ?? '');

    $st = $conn->prepare('SELECT password, first_name, last_name, username, email, school_id FROM users WHERE id = ? LIMIT 1');
    $st->bind_param('i', $uid);
    $st->execute();
    $row = $st->get_result()->fetch_assoc();
    $st->close();

    if (!hash_equals($_SESSION['cp_csrf'], (string) ($_POST['csrf'] ?? ''))) {
        $error = 'Your form expired. Please try again.';
    } elseif (!$row) {
        $error = 'Account not found. Please sign in again.';
    } elseif ($new === '' || $confirm === '') {
        $error = 'Please fill in both password fields.';
    } elseif ($new !== $confirm) {
        $error = 'Passwords do not match.';
    } elseif (password_verify($new, $row['password'])) {
        $error = 'Choose a new password — it must be different from the one you were given.';
    } else {
        $personal = [$row['first_name'] ?? '', $row['last_name'] ?? '', $row['username'] ?? '', $row['school_id'] ?? '',
                     strstr((string) ($row['email'] ?? ''), '@', true) ?: ''];
        $policyErr = pw_policy_error($new, $personal);
        if ($policyErr !== null) {
            $error = $policyErr;
        } else {
            $hash = password_hash($new, PASSWORD_DEFAULT);
            $up = $conn->prepare('UPDATE users SET password = ?, must_change_password = 0 WHERE id = ?');
            $up->bind_param('si', $hash, $uid);
            if ($up->execute()) {
                $up->close();
                // New password: any "Remember me" device of this account must sign in again
                require_once __DIR__ . '/includes/remember_me.php';
                rmForgetUser($conn, $uid);
                unset($_SESSION['must_change_password'], $_SESSION['cp_csrf']);
                header('Location: ' . $home);
                exit;
            }
            $up->close();
            $error = 'Could not save your new password. Please try again.';
        }
    }
}
$first = (string) ($_SESSION['first_name'] ?? '');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password — <?= $SYS_NAME_H ?></title>
    <?php if ($__logoSrc): ?><link rel="icon" href="<?= htmlspecialchars($__logoSrc) ?>"><?php endif; ?>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --navy: #0B2545;
            --navy-light: #123A6B;
            --ink: #142433;
            --muted: #5B6B7C;
            --line: #E0E4EE;
            --bg: #F4F6FA;
            --tint: #E8EDF5;
        }
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0 }
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
            -webkit-font-smoothing: antialiased;
            -webkit-tap-highlight-color: transparent;
        }
        /* Header: school logo + name (same look as the login page header) */
        .site-nav {
            height: 64px;
            padding-left: max(20px, calc((100% - 1240px) / 2), calc(16px + env(safe-area-inset-left, 0px)));
            padding-right: max(20px, calc((100% - 1240px) / 2), calc(16px + env(safe-area-inset-right, 0px)));
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            background: #fff;
            border-bottom: 1px solid var(--line);
            flex-shrink: 0;
        }
        .brand { display: flex; align-items: center; gap: .7rem; text-decoration: none; min-width: 0; }
        .brand-logo { width: 40px; height: 40px; object-fit: contain; flex-shrink: 0; }
        .brand-fallback { display: flex; align-items: center; justify-content: center; background: var(--navy); color: #fff; font-weight: 700; border-radius: 10px; font-size: .9rem; }
        .brand-name { font-family: 'Space Grotesk', sans-serif; font-size: 1.02rem; font-weight: 700; letter-spacing: -0.015em; color: var(--ink); white-space: nowrap; }
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
        @media (hover: hover) { .back-link:hover { background: var(--navy-light); border-color: var(--navy-light); box-shadow: 0 4px 12px rgba(11, 37, 69, .25); } }
        .page-main { flex: 1; display: flex; align-items: center; justify-content: center; padding: 2rem 1rem; }
        .reset-container {
            background: #fff;
            width: 100%;
            max-width: 560px;
            padding: 3rem 3rem 2.75rem;
            border-radius: 20px;
            border: 1px solid var(--line);
            box-shadow: 0 24px 60px rgba(6, 26, 51, .12);
            text-align: left;
        }
        .title {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--ink);
            margin-bottom: .4rem;
        }
        .subtitle { font-size: 1rem; color: var(--muted); margin-bottom: 2rem; }
        .form-group { margin-bottom: 1.35rem; text-align: left; }
        .label { display: block; font-size: .85rem; font-weight: 600; color: var(--ink); margin-bottom: .45rem; }
        .input {
            width: 100%;
            height: 56px;
            padding: 0 1rem;
            border: 1.5px solid var(--line);
            border-radius: 12px;
            background: #F8FAFC;
            font: 400 16px/1 'Inter', sans-serif;
            color: var(--ink);
            outline: none;
            transition: border-color .2s, box-shadow .2s, background .2s;
            -webkit-appearance: none;
            appearance: none;
        }
        .input::placeholder { color: #9AA6B5; }
        .input:focus { border-color: var(--navy); background: #fff; box-shadow: 0 0 0 4px rgba(11, 37, 69, .08); }
        .btn {
            width: 100%;
            height: 58px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: .5rem;
            margin-top: 1.25rem;
            border: 0;
            border-radius: 12px;
            background: var(--navy);
            color: #fff;
            font: 700 1rem/1 'Space Grotesk', sans-serif;
            text-decoration: none;
            cursor: pointer;
            box-shadow: 0 4px 14px rgba(11, 37, 69, .25);
            transition: background .2s, box-shadow .2s;
            touch-action: manipulation;
        }
        .btn:hover { background: var(--navy-light); box-shadow: 0 6px 20px rgba(11, 37, 69, .3); }
        .btn:disabled { opacity: .6; cursor: not-allowed; box-shadow: none; }
        .btn:focus-visible, .pw-toggle:focus-visible { outline: 2px solid var(--navy); outline-offset: 3px; }


        .alert-error {
            background: #fff0ec;
            border: 1px solid #ffcfbf;
            color: #c0392b;
            font-size: .85rem;
            padding: .8rem;
            border-radius: 10px;
            margin-bottom: 1.25rem;
            text-align: left;
        }
        .alert-error a { color: var(--navy); font-weight: 600; }

        .success-box { padding: .5rem 0; text-align: center; }
        .success-icon { width: 64px; height: 64px; margin: 0 auto 1rem; display: block; color: #1a7a47; }
        .success-text { color: var(--muted); font-size: .95rem; margin-bottom: 1.5rem; }

        .pw-wrap { position: relative; }
        .pw-wrap .input { padding-right: 3.25rem; }
        .pw-toggle {
            position: absolute;
            top: 50%;
            right: .35rem;
            transform: translateY(-50%);
            width: 42px;
            height: 42px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: none;
            border: 0;
            border-radius: 10px;
            color: #8A97A8;
            cursor: pointer;
            touch-action: manipulation;
            transition: color .15s, background-color .15s;
        }
        .pw-toggle:hover { color: var(--navy); background: var(--tint); }
        .pw-toggle svg { width: 20px; height: 20px; display: block; }
        /* icon shows the current state: crossed eye = hidden, open eye = visible */
        .pw-toggle .eye-on { display: none; }
        .pw-toggle[aria-pressed="true"] .eye-off { display: none; }
        .pw-toggle[aria-pressed="true"] .eye-on { display: block; }
        @media (max-width: 480px) { .back-link { width: 42px; padding: 0; justify-content: center; } .back-link span { display: none; } }
        @media (max-width: 600px) { .reset-container { padding: 2rem 1.5rem 1.75rem; } .title { font-size: 1.5rem; } }
        @media (prefers-reduced-motion: reduce) { * { transition-duration: 1ms !important; } }

        .strength-hint { font-size: .75rem; color: #8A97A8; margin-top: 4px; }

        .rules { list-style: none; margin: .6rem 0 0; display: grid; gap: .3rem; font-size: .8rem; color: #8A97A8; }
        .rules li { display: flex; align-items: center; gap: .45rem; }
        .rules li::before { content: ""; width: 14px; height: 14px; border-radius: 50%; border: 1.5px solid #C5CDD8; flex-shrink: 0; box-sizing: border-box; }
        .rules li.ok { color: #1a7a47; }
        .rules li.ok::before { border-color: #1a7a47; background: #1a7a47 url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='white' stroke-width='4' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='20 6 9 17 4 12'/%3E%3C/svg%3E") center/9px no-repeat; }
        .match-hint { font-size: .78rem; margin-top: .4rem; min-height: 1.1em; }
        .match-hint.bad { color: #c0392b; }
        .match-hint.ok { color: #1a7a47; }
        .notice { background: var(--tint); border-radius: 12px; padding: .85rem 1rem; font-size: .88rem; color: var(--navy); margin-bottom: 1.5rem; }
        /* ── Redesigned layout: brand panel + form (matches the sign-in / reset pages) ── */
        .page-main { padding: 2.25rem 1rem; background: radial-gradient(900px 500px at 100% 0%, rgba(0, 48, 135, .07), transparent 60%), radial-gradient(800px 600px at 0% 100%, rgba(18, 58, 107, .06), transparent 60%), var(--bg); }
        .reset-container { max-width: 960px; padding: 0; display: grid; grid-template-columns: 1fr 1.2fr; overflow: hidden; border-radius: 24px; box-shadow: 0 1px 2px rgba(20, 36, 51, .04), 0 24px 60px rgba(11, 37, 69, .12); }
        .cp-brand { position: relative; isolation: isolate; overflow: hidden; color: #EEF2F8; padding: 2.75rem 2.25rem; display: flex; flex-direction: column; justify-content: center; align-items: center; text-align: center; background: radial-gradient(520px 320px at 100% -10%, rgba(120, 160, 255, .22), transparent 60%), radial-gradient(480px 420px at -10% 110%, rgba(0, 85, 204, .35), transparent 60%), linear-gradient(160deg, var(--navy), var(--navy-light)); }
        .cp-brand-bg { position: absolute; right: -80px; bottom: -80px; width: 300px; opacity: .1; filter: blur(3px); z-index: -1; pointer-events: none; }
        .cp-ico { width: 84px; height: 84px; border-radius: 24px; background: rgba(255, 255, 255, .1); border: 1px solid rgba(255, 255, 255, .2); display: flex; align-items: center; justify-content: center; color: #fff; margin-bottom: 1.2rem; box-shadow: 0 10px 30px rgba(0, 0, 0, .2); }
        .cp-ico svg { width: 40px; height: 40px; }
        .cp-brand h2 { font-family: 'Space Grotesk', sans-serif; font-size: 1.65rem; line-height: 1.2; font-weight: 700; letter-spacing: -.02em; color: #fff; }
        .cp-bar { width: 44px; height: 3px; border-radius: 2px; background: #8FB2FF; margin: .9rem auto; }
        .cp-brand p { color: #C3CDDD; font-size: .95rem; max-width: 18rem; }
        .cp-tips { list-style: none; margin: 1.6rem 0 0; display: grid; gap: .75rem; text-align: left; }
        .cp-tips li { display: flex; align-items: center; gap: .7rem; font-size: .88rem; color: #DCE3EE; }
        .cp-tips .tic { width: 28px; height: 28px; flex-shrink: 0; border-radius: 9px; background: rgba(255, 255, 255, .08); border: 1px solid rgba(255, 255, 255, .14); display: flex; align-items: center; justify-content: center; color: #BFD3FF; }
        .cp-tips .tic svg { width: 14px; height: 14px; }
        .cp-body { padding: 2.75rem 2.75rem 2.4rem; min-width: 0; display: flex; flex-direction: column; justify-content: center; }
        .cp-steps { display: flex; align-items: center; gap: .55rem; font-size: .78rem; font-weight: 600; color: var(--muted); margin-bottom: 1.1rem; }
        .cp-steps .st { display: inline-flex; align-items: center; gap: .4rem; }
        .cp-steps .dot { width: 22px; height: 22px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: .72rem; background: var(--tint); color: var(--muted); }
        .cp-steps .done .dot { background: #17A34A; color: #fff; }
        .cp-steps .on { color: #003087; }
        .cp-steps .on .dot { background: #003087; color: #fff; }
        .cp-steps .ln { flex: 0 0 26px; height: 2px; background: var(--line); border-radius: 2px; }
        .cp-body .title { font-size: 1.65rem; letter-spacing: -.02em; }
        .cp-body .subtitle { font-size: .95rem; margin-bottom: 1.5rem; }
        .input { height: 52px; }
        .rules { grid-template-columns: 1fr 1fr; gap: .35rem .9rem; margin-top: .75rem; }
        .btn { height: 54px; }
        @media (max-width: 760px) {
            .reset-container { grid-template-columns: 1fr; max-width: 520px; }
            .cp-brand { padding: 1.6rem 1.5rem; }
            .cp-tips { display: none; }
            .cp-ico { width: 60px; height: 60px; border-radius: 18px; margin-bottom: .7rem; }
            .cp-ico svg { width: 30px; height: 30px; }
            .cp-brand h2 { font-size: 1.35rem; }
            .cp-bar { margin: .6rem auto; }
            .cp-body { padding: 1.6rem 1.4rem 1.75rem; }
            .rules { grid-template-columns: 1fr; }
        }
    </style>
</head>

<body>

    <header class="site-nav">
        <a href="<?= htmlspecialchars($home) ?>" class="brand" aria-label="Arandia College">
            <?php if ($__logoSrc): ?>
                <img src="<?= htmlspecialchars($__logoSrc) ?>" alt="Arandia College logo" class="brand-logo" width="40" height="40">
            <?php else: ?>
                <div class="brand-logo brand-fallback">AC</div>
            <?php endif; ?>
            <span class="brand-name">Arandia College</span>
        </a>
        <a href="logout.php" class="back-link" aria-label="Sign out">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
            <span>Sign out</span>
        </a>
    </header>

    <main class="page-main">
    <div class="reset-container">
        <aside class="cp-brand">
            <?php if ($__logoSrc): ?><img src="<?= htmlspecialchars($__logoSrc) ?>" alt="" class="cp-brand-bg" aria-hidden="true"><?php endif; ?>
            <div class="cp-ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3l7 3v5c0 4.5-3 8.2-7 10-4-1.8-7-5.5-7-10V6l7-3z"/><circle cx="12" cy="11" r="2"/><path d="M12 13v3"/></svg></div>
            <h2>Almost there!</h2>
            <div class="cp-bar"></div>
            <p>Choose a password that only you know to protect your <?= $SYS_NAME_H ?> account.</p>
            <ul class="cp-tips">
                <li><span class="tic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg></span>Only you should know it</li>
                <li><span class="tic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg></span>Different from the one you were given</li>
                <li><span class="tic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg></span>No name, email or LRN in it</li>
            </ul>
        </aside>
        <div class="cp-body">
            <div class="cp-steps" aria-label="Step 2 of 2">
                <span class="st done"><span class="dot">&#10003;</span> Terms</span>
                <span class="ln"></span>
                <span class="st on"><span class="dot">2</span> Set your password</span>
            </div>
        <h2 class="title">Set your own password</h2>
        <p class="subtitle"><?= $first !== '' ? 'Welcome, ' . htmlspecialchars($first) . '! ' : 'Welcome! ' ?>Before you continue, replace the password you were given with one only you know.</p>

        <?php if ($error): ?>
            <div class="alert-error" role="alert"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="change_password.php" autocomplete="off" novalidate>
            <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['cp_csrf']) ?>">
            <div class="form-group">
                <label class="label" for="new_password">New password</label>
                <div class="pw-wrap">
                    <input type="password" id="new_password" name="new_password" class="input" required maxlength="64" autocomplete="new-password" placeholder="Enter new password" autofocus>
                        <button type="button" class="pw-toggle" aria-label="Show password" aria-pressed="false" data-pw-toggle>
                            <svg class="eye-on" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7S1 12 1 12z"/><circle cx="12" cy="12" r="3"/></svg>
                            <svg class="eye-off" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M17.94 17.94A10.9 10.9 0 0 1 12 19C5 19 1 12 1 12a18.5 18.5 0 0 1 5.06-5.94"/><path d="M9.9 4.24A10.9 10.9 0 0 1 12 5c7 0 11 7 11 7a18.5 18.5 0 0 1-2.16 3.19"/><path d="M14.12 14.12a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                        </button>
                </div>
                <ul class="rules" id="pw-rules">
                    <li data-r="len">At least 8 characters</li>
                    <li data-r="up">An uppercase letter (A-Z)</li>
                    <li data-r="low">A lowercase letter (a-z)</li>
                    <li data-r="num">A number (0-9)</li>
                    <li data-r="sp">A special character (e.g. ! @ # $ %)</li>
                    <li data-r="ws">No spaces</li>
                </ul>
            </div>

            <div class="form-group">
                <label class="label" for="confirm_password">Confirm new password</label>
                <div class="pw-wrap">
                    <input type="password" id="confirm_password" name="confirm_password" class="input" required maxlength="64" autocomplete="new-password" placeholder="Type it again">
                        <button type="button" class="pw-toggle" aria-label="Show password" aria-pressed="false" data-pw-toggle>
                            <svg class="eye-on" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7S1 12 1 12z"/><circle cx="12" cy="12" r="3"/></svg>
                            <svg class="eye-off" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M17.94 17.94A10.9 10.9 0 0 1 12 19C5 19 1 12 1 12a18.5 18.5 0 0 1 5.06-5.94"/><path d="M9.9 4.24A10.9 10.9 0 0 1 12 5c7 0 11 7 11 7a18.5 18.5 0 0 1-2.16 3.19"/><path d="M14.12 14.12a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                        </button>
                </div>
                <p class="match-hint" id="match-hint" aria-live="polite"></p>
            </div>
            <p class="strength-hint">Don't use your name, username, email or LRN / school ID.</p>

            <button type="submit" class="btn">Save and continue</button>
        </form>
        </div>
    </div>
    </main>

    <script>
        // Show / hide password (eye button) — each button controls the field beside it
        document.querySelectorAll('[data-pw-toggle]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var input = btn.parentNode.querySelector('input');
                var show = input.type === 'password';
                input.type = show ? 'text' : 'password';
                btn.setAttribute('aria-pressed', show ? 'true' : 'false');
                btn.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
                input.focus();
            });
        });
        // Live checklist (the server checks the same rules again)
        var pw = document.getElementById('new_password'), cf = document.getElementById('confirm_password');
        var hint = document.getElementById('match-hint');
        var tests = {
            len: function (v) { return v.length >= 8; },
            up: function (v) { return /[A-Z]/.test(v); },
            low: function (v) { return /[a-z]/.test(v); },
            num: function (v) { return /\d/.test(v); },
            sp: function (v) { return /[^A-Za-z0-9\s]/.test(v); },
            ws: function (v) { return v.length > 0 && !/\s/.test(v); }
        };
        function check() {
            var v = pw.value;
            document.querySelectorAll('#pw-rules li').forEach(function (li) {
                li.classList.toggle('ok', tests[li.dataset.r](v));
            });
            if (!cf.value) { hint.textContent = ''; hint.className = 'match-hint'; }
            else if (cf.value === v) { hint.textContent = 'Passwords match.'; hint.className = 'match-hint ok'; }
            else { hint.textContent = 'Passwords do not match.'; hint.className = 'match-hint bad'; }
        }
        pw.addEventListener('input', check);
        cf.addEventListener('input', check);
        // One click only: a second submit while the first is sent is ignored
        var f = document.querySelector('form');
        f.addEventListener('submit', function (e) {
            if (f.dataset.sending) { e.preventDefault(); return; }
            f.dataset.sending = '1';
            var b = f.querySelector('button[type="submit"]');
            if (b) { b.setAttribute('aria-busy', 'true'); b.textContent = 'Saving…'; }
        });
        window.addEventListener('pageshow', function (e) { if (e.persisted) { delete f.dataset.sending; } });
    </script>
</body>
</html>