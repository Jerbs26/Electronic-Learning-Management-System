<?php
// ============================================================
//  Arandia College eLMS — Reset Password
//  File: reset_password.php
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

// ── Resolve the school logo (same self-healing lookup as login.php) ──
$__logoSrc = null;
foreach (['png', 'jpg', 'jpeg', 'PNG', 'JPG', 'JPEG'] as $__logoExt) {
    if (is_file(__DIR__ . '/picture/logo.' . $__logoExt)) {
        $__logoSrc = 'picture/logo.' . $__logoExt;
        break;
    }
}

 $error = '';
 $success = '';
 $tokenValid = false;
 $user_id = null;

// ── 1. Check if Token is present in URL ─────────────────────
 $token = $_GET['token'] ?? '';

if (empty($token)) {
    $error = "Invalid request. No token provided.";
} else {
    // ── 2. Validate Token in Database ─────────────────────────
    // Join with users table to check if account is active
    $stmt = $conn->prepare("
        SELECT pr.user_id 
        FROM password_resets pr
        JOIN users u ON pr.user_id = u.id
        WHERE pr.token = ? 
          AND pr.used = 0 
          AND pr.expires_at > NOW()
          AND u.status = 'Active'
        LIMIT 1
    ");
    $stmt->bind_param('s', $token);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $user_id = $row['user_id'];
        $tokenValid = true;
    } else {
        $error = "This reset link is invalid, has expired, or has already been used. 
                  <br><a href='login.php'>Click here to request a new one.</a>";
    }
    $stmt->close();
}

// ── 3. Handle Password Update (POST) ────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $tokenValid) {
    $new_password = trim($_POST['new_password'] ?? '');
    $confirm_password = trim($_POST['confirm_password'] ?? '');

    // Basic Validation
    if (empty($new_password) || empty($confirm_password)) {
        $error = "Please fill in both password fields.";
    } elseif (strlen($new_password) < 6) {
        $error = "Password must be at least 6 characters long.";
    } elseif ($new_password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        // Hash the new password
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

        // Update Password in Users Table
        $updateStmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $updateStmt->bind_param('si', $hashed_password, $user_id);
        
        if ($updateStmt->execute()) {
            // New password: every "Remember me" device must sign in again
            require_once __DIR__ . '/includes/remember_me.php';
            rmForgetUser($conn, (int) $user_id);

            // Invalidate the token so it can't be used again
            $invalidateStmt = $conn->prepare("UPDATE password_resets SET used = 1 WHERE user_id = ?");
            $invalidateStmt->bind_param('i', $user_id);
            $invalidateStmt->execute();
            $invalidateStmt->close();

            // Set success flag
            $success = true;
            $tokenValid = false; // Hide form after success
        } else {
            $error = "An error occurred while updating your password. Please try again.";
        }
        $updateStmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password — <?= $SYS_NAME_H ?></title>
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
        /* ── Redesigned layout: brand panel + form (matches the sign-in page) ── */
        :root { --gold: #FFC53D; --navy-deep: #061A33; --teal: #0E7C7B; }
        .page-main { padding: 2.5rem 1rem; background: radial-gradient(900px 500px at 100% 0%, rgba(255,197,61,.08), transparent 60%), radial-gradient(800px 600px at 0% 100%, rgba(14,124,123,.08), transparent 60%), var(--bg); }
        .reset-container { max-width: 940px; padding: 0; display: grid; grid-template-columns: 1fr 1.1fr; overflow: hidden; border-radius: 24px; box-shadow: 0 1px 2px rgba(20,36,51,.04), 0 24px 60px rgba(11,37,69,.12); }
        .rp-brand { position: relative; isolation: isolate; overflow: hidden; color: #EEF2F8; padding: 2.75rem 2.25rem; display: flex; flex-direction: column; justify-content: center; align-items: center; text-align: center; gap: .4rem; background: radial-gradient(520px 300px at 100% -10%, rgba(255,197,61,.16), transparent 60%), radial-gradient(480px 400px at -10% 110%, rgba(14,124,123,.42), transparent 60%), linear-gradient(160deg, var(--navy), var(--navy-light)); }
        .rp-brand-bg { position: absolute; right: -80px; bottom: -80px; width: 300px; opacity: .1; filter: blur(3px); z-index: -1; pointer-events: none; }
        .rp-ico { width: 84px; height: 84px; border-radius: 24px; background: rgba(255,255,255,.1); border: 1px solid rgba(255,255,255,.18); display: flex; align-items: center; justify-content: center; color: var(--gold); margin-bottom: 1.1rem; box-shadow: 0 10px 30px rgba(0,0,0,.2); }
        .rp-ico svg { width: 40px; height: 40px; }
        .rp-brand h2 { font-family: 'Space Grotesk', sans-serif; font-size: 1.65rem; line-height: 1.2; font-weight: 700; letter-spacing: -.02em; color: #fff; }
        .rp-bar { width: 44px; height: 3px; border-radius: 2px; background: var(--gold); margin: .9rem auto; }
        .rp-brand p { color: #C3CDDD; font-size: .95rem; max-width: 18rem; }
        .rp-tips { list-style: none; margin-top: 1.5rem; display: grid; gap: .7rem; text-align: left; }
        .rp-tips li { display: flex; align-items: center; gap: .7rem; font-size: .88rem; color: #DCE3EE; }
        .rp-tips .tic { width: 28px; height: 28px; flex-shrink: 0; border-radius: 9px; background: rgba(255,255,255,.08); border: 1px solid rgba(255,255,255,.12); display: flex; align-items: center; justify-content: center; color: var(--gold); }
        .rp-tips .tic svg { width: 14px; height: 14px; }
        .rp-body { padding: 3rem 2.75rem 2.5rem; display: flex; flex-direction: column; justify-content: center; min-width: 0; }
        .rp-body .title { font-size: 1.7rem; letter-spacing: -.02em; }
        .rp-body .subtitle { margin-bottom: 1.6rem; font-size: .95rem; }
        .input { height: 52px; }
        .btn { height: 54px; margin-top: 1.5rem; }
        .req { list-style: none; display: grid; gap: .35rem; margin-top: .75rem; }
        .req li { display: flex; align-items: center; gap: .5rem; font-size: .82rem; color: #8A97A8; transition: color .2s; }
        .req li::before { content: ""; width: 16px; height: 16px; flex-shrink: 0; border-radius: 50%; border: 1.5px solid #C5CEDB; background: #fff; transition: all .2s; }
        .req li.ok { color: #166534; }
        .req li.ok::before { border-color: #17A34A; background: #17A34A url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='white' stroke-width='3.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='20 6 9 17 4 12'/%3E%3C/svg%3E") center/10px no-repeat; }
        .match-hint { font-size: .8rem; min-height: 1.2em; margin-top: .45rem; color: #8A97A8; }
        .match-hint.ok { color: #166534; font-weight: 600; }
        .match-hint.bad { color: #C0392B; font-weight: 600; }
        .input.is-ok { border-color: #17A34A; }
        .input.is-bad { border-color: #E5484D; }
        @media (max-width: 760px) {
            .reset-container { grid-template-columns: 1fr; max-width: 520px; }
            .rp-brand { padding: 1.75rem 1.5rem; }
            .rp-tips { display: none; }
            .rp-ico { width: 60px; height: 60px; border-radius: 18px; margin-bottom: .7rem; }
            .rp-ico svg { width: 30px; height: 30px; }
            .rp-brand h2 { font-size: 1.35rem; }
            .rp-bar { margin: .6rem auto; }
            .rp-body { padding: 1.75rem 1.5rem 1.75rem; }
        }
    </style>
</head>

<body>

    <header class="site-nav">
        <a href="index.php" class="brand" aria-label="Arandia College">
            <?php if ($__logoSrc): ?>
                <img src="<?= htmlspecialchars($__logoSrc) ?>" alt="Arandia College logo" class="brand-logo" width="40" height="40">
            <?php else: ?>
                <div class="brand-logo brand-fallback">AC</div>
            <?php endif; ?>
            <span class="brand-name">Arandia College</span>
        </a>
        <a href="login.php" class="back-link" aria-label="Back to Sign In">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
            <span>Back to Sign In</span>
        </a>
    </header>

    <main class="page-main">
    <div class="reset-container">
        <aside class="rp-brand">
            <?php if ($__logoSrc): ?><img src="<?= htmlspecialchars($__logoSrc) ?>" alt="" class="rp-brand-bg" aria-hidden="true"><?php endif; ?>
            <div class="rp-ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/><circle cx="12" cy="16.5" r="1.4"/></svg></div>
            <h2>Secure your account</h2>
            <div class="rp-bar"></div>
            <p>Choose a new password for your <?= $SYS_NAME_H ?> account.</p>
            <ul class="rp-tips">
                <li><span class="tic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg></span>Use at least 6 characters</li>
                <li><span class="tic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg></span>Mix letters and numbers</li>
                <li><span class="tic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg></span>Don&rsquo;t reuse an old password</li>
            </ul>
        </aside>
        <div class="rp-body">

        <?php if ($success): ?>
            <!-- ── Success State ──────────────────────────────────── -->
            <div class="success-box">
                <svg class="success-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><polyline points="7.5 12.5 10.5 15.5 16.5 9"/></svg>
                <h2 class="title">Password Changed!</h2>
                <p class="success-text">Your password has been successfully reset. You can now sign in with your new password.</p>
                <a href="login.php" class="btn">Go to Sign In</a>
            </div>

        <?php elseif ($tokenValid): ?>
            <!-- ── Reset Form ─────────────────────────────────────── -->
            <h2 class="title">Create New Password</h2>
            <p class="subtitle">Please enter your new password below.</p>

            <?php if ($error): ?>
                <div class="alert-error">
                    <?= $error ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="reset_password.php?token=<?= htmlspecialchars($token) ?>">
                <div class="form-group">
                    <label class="label" for="new_password">New Password</label>
                    <div class="pw-wrap">
                        <input type="password" id="new_password" name="new_password" class="input" required minlength="6" autocomplete="new-password" placeholder="Enter new password">
                        <button type="button" class="pw-toggle" aria-label="Show password" aria-pressed="false" data-pw-toggle>
                            <svg class="eye-on" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7S1 12 1 12z"/><circle cx="12" cy="12" r="3"/></svg>
                            <svg class="eye-off" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M17.94 17.94A10.9 10.9 0 0 1 12 19C5 19 1 12 1 12a18.5 18.5 0 0 1 5.06-5.94"/><path d="M9.9 4.24A10.9 10.9 0 0 1 12 5c7 0 11 7 11 7a18.5 18.5 0 0 1-2.16 3.19"/><path d="M14.12 14.12a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                        </button>
                    </div>
                    <ul class="req" id="req"><li data-r="len">At least 6 characters</li><li data-r="mix">Letters and numbers</li><li data-r="case">Upper &amp; lowercase letters, or a symbol</li></ul>
                </div>

                <div class="form-group">
                    <label class="label" for="confirm_password">Confirm Password</label>
                    <div class="pw-wrap">
                        <input type="password" id="confirm_password" name="confirm_password" class="input" required minlength="6" autocomplete="new-password" placeholder="Confirm new password">
                        <button type="button" class="pw-toggle" aria-label="Show password" aria-pressed="false" data-pw-toggle>
                            <svg class="eye-on" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7S1 12 1 12z"/><circle cx="12" cy="12" r="3"/></svg>
                            <svg class="eye-off" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M17.94 17.94A10.9 10.9 0 0 1 12 19C5 19 1 12 1 12a18.5 18.5 0 0 1 5.06-5.94"/><path d="M9.9 4.24A10.9 10.9 0 0 1 12 5c7 0 11 7 11 7a18.5 18.5 0 0 1-2.16 3.19"/><path d="M14.12 14.12a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                        </button>
                    </div>
                    <p class="match-hint" id="matchHint" aria-live="polite"></p>
                </div>

                <button type="submit" class="btn">Reset Password</button>
            </form>

        <?php else: ?>
            <!-- ── Error State (Invalid/Expired Token) ────────────── -->
            <h2 class="title">Oops!</h2>
            <div class="alert-error" style="text-align: center;">
                <?= $error ?>
            </div>
        <?php endif; ?>
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
        // One click only: a second submit while the first is sent is ignored
        var f = document.querySelector('form');
        if (f) f.addEventListener('submit', function (e) {
            if (f.dataset.sending) { e.preventDefault(); return; }
            f.dataset.sending = '1';
            var b = f.querySelector('button[type="submit"]');
            if (b) { b.setAttribute('aria-busy', 'true'); b.textContent = 'Resetting…'; }
        });
        window.addEventListener('pageshow', function (e) { if (e.persisted && f) { delete f.dataset.sending; } });
        // Password checklist and "passwords match" hint (display only; the server still checks everything)
        (function () {
            var pw = document.getElementById('new_password'), cf = document.getElementById('confirm_password');
            if (!pw || !cf) return;
            var hint = document.getElementById('matchHint'), items = {};
            document.querySelectorAll('#req li').forEach(function (li) { items[li.dataset.r] = li; });
            function update() {
                var v = pw.value, len = v.length >= 6, mix = /[A-Za-z]/.test(v) && /\d/.test(v),
                    cs = (/[a-z]/.test(v) && /[A-Z]/.test(v)) || /[^A-Za-z0-9]/.test(v);
                items.len.classList.toggle('ok', len); items.mix.classList.toggle('ok', mix); items.case.classList.toggle('ok', cs);
                pw.classList.toggle('is-ok', len && !!v);
                match();
            }
            function match() {
                if (!cf.value) { hint.textContent = ''; hint.className = 'match-hint'; cf.classList.remove('is-ok', 'is-bad'); return; }
                var ok = cf.value === pw.value;
                hint.textContent = ok ? 'Passwords match' : 'Passwords do not match yet';
                hint.className = 'match-hint ' + (ok ? 'ok' : 'bad');
                cf.classList.toggle('is-ok', ok); cf.classList.toggle('is-bad', !ok);
            }
            pw.addEventListener('input', update); cf.addEventListener('input', match); update();
        })();
    </script>
</body>
</html>