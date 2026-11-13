<?php
// ============================================================
//  Arandia College eLMS — Terms and Conditions (first sign-in)
//  File: terms.php
//
//  A new account must accept these terms BEFORE it can set its own
//  password (includes/force_password.php sends it here first). The time of
//  acceptance is saved in users.terms_accepted_at, so each account is asked
//  only once. Declining signs the user out.
//
//  To change the wording, edit the sections in the "terms-box" below.
// ============================================================
require_once __DIR__ . '/includes/session_safe.php';
elmsSessionStart();
require_once 'config/conn.php';

// ── System name / school level (Admin → Settings, includes/settings.php) ──
require_once __DIR__ . '/includes/settings.php';
$__sysSet     = elmsSettingsPublic(elmsSettings($conn));
$SYS_NAME     = $__sysSet['system_name'];
$SYS_NAME_H   = htmlspecialchars($SYS_NAME, ENT_QUOTES, 'UTF-8');
$SYS_LEVEL_H  = htmlspecialchars($__sysSet['school_level_label'], ENT_QUOTES, 'UTF-8');
require_once __DIR__ . '/includes/force_password.php';

if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
$uid  = (int) $_SESSION['user_id'];
$home = fpRoleHome((string) ($_SESSION['role'] ?? ''));

// Not a first sign-in (nothing to change / already accepted): move on
if (empty($_SESSION['must_change_password'])) {
    header('Location: ' . $home);
    exit;
}
if (empty($_SESSION['terms_pending'])) {
    header('Location: change_password.php');
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

if (empty($_SESSION['terms_csrf'])) $_SESSION['terms_csrf'] = bin2hex(random_bytes(16));
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['terms_csrf'], (string) ($_POST['csrf'] ?? ''))) {
        $error = 'Your form expired. Please try again.';
    } elseif (empty($_POST['agree'])) {
        $error = 'Please tick the box to confirm that you agree.';
    } elseif (!fpAcceptTerms($conn, $uid)) {
        $error = 'Could not save your answer. Please try again.';
    } else {
        unset($_SESSION['terms_pending'], $_SESSION['terms_csrf']);
        header('Location: change_password.php', true, 303);
        exit;
    }
}
$first = (string) ($_SESSION['first_name'] ?? '');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terms and Conditions — <?= $SYS_NAME_H ?></title>
    <?php if ($__logoSrc): ?><link rel="icon" href="<?= htmlspecialchars($__logoSrc) ?>"><?php endif; ?>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --navy: #0B2545; --navy-light: #123A6B; --blue: #003087; --ink: #142433; --muted: #5B6B7C; --line: #E0E4EE; --bg: #F4F6FA; --tint: #E8EDF5; }
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0 }
        body { font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; font-size: 16px; line-height: 1.6; color: var(--ink); background: var(--bg); min-height: 100vh; min-height: 100dvh; display: flex; flex-direction: column; -webkit-font-smoothing: antialiased; }
        .site-nav { height: 64px; padding: 0 max(20px, calc((100% - 1240px) / 2)); display: flex; align-items: center; justify-content: space-between; gap: 1rem; background: #fff; border-bottom: 1px solid var(--line); flex-shrink: 0; }
        .brand { display: flex; align-items: center; gap: .7rem; text-decoration: none; color: var(--ink); min-width: 0; }
        .brand-logo { width: 40px; height: 40px; object-fit: contain; flex-shrink: 0; }
        .brand-fallback { display: flex; align-items: center; justify-content: center; background: var(--navy); color: #fff; font-weight: 700; border-radius: 10px; font-size: .9rem; }
        .brand-name { font-family: 'Space Grotesk', sans-serif; font-weight: 700; font-size: 1.02rem; white-space: nowrap; }
        .back-link { display: inline-flex; align-items: center; gap: .4rem; min-height: 42px; padding: 0 1rem; border-radius: 12px; background: var(--navy); color: #fff; font: 600 .9rem/1 'Inter', sans-serif; text-decoration: none; white-space: nowrap; }
        .back-link svg { width: 16px; height: 16px; }
        .page-main { flex: 1; display: flex; justify-content: center; align-items: flex-start; padding: 2.25rem 1rem; background: radial-gradient(900px 500px at 100% 0%, rgba(0, 48, 135, .06), transparent 60%), var(--bg); }
        .card { width: 100%; max-width: 780px; background: #fff; border: 1px solid var(--line); border-radius: 24px; padding: 2.25rem 2.5rem 2rem; box-shadow: 0 1px 2px rgba(20, 36, 51, .04), 0 24px 60px rgba(11, 37, 69, .10); }
        .steps { display: flex; align-items: center; gap: .6rem; font-size: .8rem; font-weight: 600; color: var(--muted); margin-bottom: 1.25rem; }
        .steps .step { display: inline-flex; align-items: center; gap: .45rem; }
        .steps .dot { width: 24px; height: 24px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: .75rem; background: var(--tint); color: var(--muted); }
        .steps .on { color: var(--blue); }
        .steps .on .dot { background: var(--blue); color: #fff; }
        .steps .bar { flex: 0 0 28px; height: 2px; background: var(--line); border-radius: 2px; }
        h1 { font-family: 'Space Grotesk', sans-serif; font-size: 1.75rem; letter-spacing: -.02em; line-height: 1.2; }
        .lead { color: var(--muted); font-size: .95rem; margin: .4rem 0 1.25rem; }
        .terms-box { height: min(46vh, 420px); min-height: 240px; overflow-y: auto; padding: 1.25rem 1.4rem; background: #F8FAFD; border: 1.5px solid var(--line); border-radius: 16px; font-size: .92rem; scrollbar-width: thin; }
        .terms-box:focus-visible { outline: 2px solid var(--blue); outline-offset: 2px; }
        .terms-box h2 { font-family: 'Space Grotesk', sans-serif; font-size: 1rem; color: var(--blue); margin: 1.15rem 0 .35rem; }
        .terms-box h2:first-child { margin-top: 0; }
        .terms-box p { color: #33445A; margin-bottom: .5rem; }
        .terms-box ul { margin: .2rem 0 .6rem 1.15rem; color: #33445A; }
        .terms-box li { margin-bottom: .25rem; }
        .terms-end { margin-top: 1.1rem; padding-top: .9rem; border-top: 1px dashed #C7D2E6; font-size: .85rem; color: var(--muted); }
        .scroll-hint { display: flex; align-items: center; gap: .5rem; font-size: .82rem; color: var(--muted); margin: .7rem 0 1rem; min-height: 1.4em; }
        .scroll-hint.done { color: #166534; font-weight: 600; }
        .alert { background: #FEF2F2; border: 1px solid #FECACA; color: #991B1B; border-radius: 12px; padding: .75rem 1rem; font-size: .9rem; font-weight: 500; margin-bottom: 1rem; }
        .agree { display: flex; align-items: flex-start; gap: .7rem; padding: .85rem 1rem; border: 1.5px solid var(--line); border-radius: 14px; cursor: pointer; font-size: .93rem; }
        .agree input { width: 20px; height: 20px; margin-top: 2px; accent-color: var(--blue); flex-shrink: 0; cursor: pointer; }
        .agree.locked { opacity: .55; cursor: not-allowed; }
        .agree.locked input { cursor: not-allowed; }
        .actions { display: flex; gap: .75rem; margin-top: 1.1rem; }
        .btn { flex: 1; height: 52px; border: 0; border-radius: 12px; background: var(--navy); color: #fff; font: 700 1rem/1 'Space Grotesk', sans-serif; cursor: pointer; box-shadow: 0 4px 14px rgba(11, 37, 69, .25); }
        .btn:hover:not(:disabled) { background: var(--navy-light); }
        .btn:disabled { opacity: .45; cursor: not-allowed; box-shadow: none; }
        .btn-ghost { flex: 0 0 auto; display: inline-flex; align-items: center; justify-content: center; padding: 0 1.4rem; height: 52px; border-radius: 12px; border: 1.5px solid var(--line); background: #fff; color: var(--muted); font: 600 .95rem 'Inter', sans-serif; text-decoration: none; }
        .btn-ghost:hover { border-color: #B6C2D6; color: var(--ink); }
        @media (max-width: 640px) {
            .site-nav { padding: 0 16px; } .back-link span { display: none; } .back-link { width: 44px; padding: 0; justify-content: center; }
            .page-main { padding: 0; } .card { border-radius: 0; border: 0; box-shadow: none; padding: 1.5rem 1.1rem 2rem; }
            h1 { font-size: 1.45rem; } .terms-box { height: 52vh; padding: 1rem; }
            .actions { flex-direction: column-reverse; } .btn-ghost { width: 100%; }
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
        <div class="card">
            <div class="steps" aria-label="Step 1 of 2">
                <span class="step on"><span class="dot">1</span> Terms and Conditions</span>
                <span class="bar"></span>
                <span class="step"><span class="dot">2</span> Set your password</span>
            </div>

            <h1>Welcome<?= $first !== '' ? ', ' . htmlspecialchars($first) : '' ?>!</h1>
            <p class="lead">Before you start using <?= $SYS_NAME_H ?>, please read and accept the Terms and Conditions below.</p>

            <?php if ($error): ?>
                <div class="alert" role="alert"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <div class="terms-box" id="termsBox" tabindex="0" role="region" aria-label="Terms and Conditions">
                <h2>1. Acceptance of these terms</h2>
                <p>By using <?= $SYS_NAME_H ?> (the &ldquo;system&rdquo;), you agree to these Terms and Conditions. If you do not agree, please do not use the system and sign out. The system is for the students, teachers and staff of Arandia College only.</p>

                <h2>2. Your account</h2>
                <ul>
                    <li>Your account is personal. Do not share your password or your e-mailed sign-in code with anyone, not even a classmate, teacher or staff member.</li>
                    <li>You are responsible for everything done with your account. If you think someone else knows your password, change it right away and tell the school.</li>
                    <li>An account can be signed in on one device at a time. Signing in on another device signs out the first one.</li>
                    <li>Keep your e-mail address correct. Sign-in codes and password reset links are sent to it.</li>
                </ul>

                <h2>3. Acceptable use</h2>
                <p>When using the system you agree not to:</p>
                <ul>
                    <li>use another person&rsquo;s account, or let someone use yours;</li>
                    <li>try to break, bypass or test the security of the system, or access data that is not yours;</li>
                    <li>upload files that contain viruses, or content that is offensive, threatening, discriminatory or illegal;</li>
                    <li>bully, harass or embarrass others in announcements, messages or submissions;</li>
                    <li>copy, sell or share lessons, quizzes and other school materials outside the school without permission.</li>
                </ul>

                <h2>4. Academic honesty</h2>
                <p>Assignments, quizzes and exams must be your own work unless your teacher says otherwise. Copying, sharing answers, or using someone else&rsquo;s work or an AI tool when it is not allowed is cheating and may lead to school discipline. Quiz activity (such as leaving a quiz page) may be recorded.</p>

                <h2>5. Your submissions</h2>
                <p>The work you submit stays yours. By submitting it, you allow the school to store it, show it to your teachers and administrators, and use it for grading, records and school reports.</p>

                <h2>6. Data privacy</h2>
                <p>Arandia College collects and uses your personal information in line with the Data Privacy Act of 2012 (Republic Act No. 10173). In this system that means your name, learner reference number or school ID, e-mail address, contact number, section or department, grades, submissions, attendance and activity records.</p>
                <ul>
                    <li>Your information is used only for school purposes, such as enrolment, teaching, grading, communication and keeping the system secure.</li>
                    <li>It can be seen only by you and by the teachers and staff who need it for their work.</li>
                    <li>Sign-in times, online status and other activity are recorded to protect accounts and to help the school.</li>
                    <li>Messages you type into the EduBot assistant may be processed by an outside AI service to produce a reply. Do not enter passwords or sensitive personal information there.</li>
                    <li>You may ask the school to see or correct your information. Please contact the school administrator.</li>
                </ul>

                <h2>7. Suspension</h2>
                <p>The school may limit or deactivate an account that breaks these terms or puts the system or other users at risk. Serious cases may also be handled under the school&rsquo;s student or employee rules.</p>

                <h2>8. Changes to these terms</h2>
                <p>The school may update these terms from time to time. When they change in an important way, you may be asked to read and accept them again.</p>

                <h2>9. Questions</h2>
                <p>If you have questions about these terms or about your information, use the Contact page or ask the school administrator.</p>

                <p class="terms-end">&mdash; End of the Terms and Conditions &mdash;</p>
            </div>

            <div class="scroll-hint" id="scrollHint" aria-live="polite">Please scroll to the end of the terms to continue.</div>

            <form method="POST" action="terms.php" id="termsForm">
                <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['terms_csrf']) ?>">
                <label class="agree" id="agreeRow">
                    <input type="checkbox" name="agree" value="1" id="agree">
                    <span>I have read and agree to the Terms and Conditions of <?= $SYS_NAME_H ?>.</span>
                </label>
                <div class="actions">
                    <a href="logout.php" class="btn-ghost">Decline &amp; sign out</a>
                    <button type="submit" class="btn" id="continueBtn">I agree &mdash; continue</button>
                </div>
            </form>
        </div>
    </main>

    <script>
        // The checkbox unlocks once the whole text has been scrolled. Without JavaScript
        // everything stays usable (the server still needs the box to be ticked).
        (function () {
            var box = document.getElementById('termsBox'), cb = document.getElementById('agree'),
                row = document.getElementById('agreeRow'), btn = document.getElementById('continueBtn'),
                hint = document.getElementById('scrollHint');
            if (!box || !cb || !btn) return;
            cb.disabled = true; row.classList.add('locked'); btn.disabled = true;
            function unlock() {
                if (!cb.disabled) return;
                cb.disabled = false; row.classList.remove('locked');
                hint.textContent = 'Thank you for reading. Tick the box below to continue.';
                hint.classList.add('done');
            }
            function check() { if (box.scrollTop + box.clientHeight >= box.scrollHeight - 8) unlock(); }
            box.addEventListener('scroll', check, { passive: true });
            window.addEventListener('resize', check);
            check();
            cb.addEventListener('change', function () { btn.disabled = !cb.checked; });
        })();
    </script>
</body>

</html>