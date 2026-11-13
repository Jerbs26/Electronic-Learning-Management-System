<?php
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
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Campus Helpdesk — Arandia College eLMS</title>
    <link rel="icon" type="image/png" href="picture/logo.png">
    <link
        href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&family=Inter:wght@400;500;600;700&display=swap"
        rel="stylesheet">
    <style>
        :root {
            --primary: #0B2545;
            --primary-dark: #061A33;
            --primary-light: #123A6B;
            --accent: #FFC53D;
            --text-dark: #142433;
            --text-light: #5B6B7C;
            --bg-light: #F4F6FA;
            --white: #ffffff;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg-light);
            color: var(--text-dark);
        }

        .topbar {
            background: var(--primary-dark);
            color: rgba(255, 255, 255, 0.8);
            font-size: .78rem;
            padding: .4rem 5%;
            display: flex;
            justify-content: flex-end;
            gap: 1.5rem;
        }

        .topbar a {
            color: rgba(255, 255, 255, .8);
            text-decoration: none;
        }

        .topbar a:hover {
            color: var(--accent);
        }

        nav {
            background: white;
            box-shadow: 0 2px 12px rgba(0, 0, 0, .1);
            position: sticky;
            top: 0;
            z-index: 200;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 5%;
            height: 80px;
            backdrop-filter: blur(12px);
            background: rgba(255, 255, 255, 0.95);
        }

        .nav-brand {
            display: flex;
            align-items: center;
            gap: .85rem;
            text-decoration: none;
        }

        .nav-logo-svg {
            width: 50px;
            height: 50px;
            object-fit: contain;
        }

        .nav-brand-text strong {
            display: block;
            font-family: 'Space Grotesk', sans-serif;
            font-size: 1rem;
            font-weight: 700;
            color: var(--primary);
        }

        .nav-brand-text span {
            font-size: .72rem;
            color: #888;
        }

        .nav-links {
            display: flex;
            align-items: center;
            list-style: none;
            gap: .25rem;
        }

        .nav-links a {
            font-size: .85rem;
            font-weight: 600;
            color: #444;
            text-decoration: none;
            padding: .5rem .9rem;
            border-radius: 6px;
            transition: background .2s, color .2s;
        }

        .nav-links a:hover {
            background: #E8EDF5;
            color: var(--primary);
        }

        .btn-login {
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            color: white !important;
            padding: 0.6rem 1.8rem !important;
            border-radius: 50px !important;
            font-weight: 700 !important;
            box-shadow: 0 4px 14px rgba(11, 37, 69, 0.3);
            transition: background-color 0.2s, border-color 0.2s, color 0.2s, box-shadow 0.2s, opacity 0.2s, transform 0.2s !important;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(11, 37, 69, 0.4) !important;
            background: var(--primary-dark) !important;
        }

        /* HERO */
        .page-hero {
            background: linear-gradient(135deg, #0B2545 0%, var(--primary-light) 100%);
            padding: 4rem 5% 3rem;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .page-hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.04'%3E%3Ccircle cx='30' cy='30' r='20'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }

        .page-hero-eyebrow {
            display: inline-block;
            background: rgba(255, 255, 255, .15);
            color: rgba(255, 255, 255, .9);
            font-size: .72rem;
            font-weight: 700;
            letter-spacing: .1em;
            text-transform: uppercase;
            padding: .3rem 1rem;
            border-radius: 100px;
            margin-bottom: .75rem;
            position: relative;
        }

        .page-hero h1 {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 2.4rem;
            font-weight: 700;
            color: white;
            letter-spacing: -.02em;
            margin-bottom: .5rem;
            position: relative;
        }

        .page-hero p {
            font-size: .9rem;
            color: rgba(255, 255, 255, .7);
            position: relative;
        }


        /* ---------- HELPDESK PAGE ---------- */
        .hd-wrap {
            max-width: none;
            margin: 0;
            padding: 2.25rem clamp(24px, 3vw, 48px) 4.5rem;
        }

        .hd-strip {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 1rem;
        }

        .hd-stat {
            background: #fff;
            border: 1px solid #E0E4EE;
            border-radius: 14px;
            padding: 1.1rem 1.25rem;
            display: flex;
            gap: .9rem;
            align-items: center;
            box-shadow: 0 6px 20px rgba(11, 37, 69, .07);
        }

        .hd-stat .ic {
            width: 42px;
            height: 42px;
            border-radius: 10px;
            background: #E8EDF5;
            color: #0B2545;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .hd-stat .ic svg {
            width: 20px;
            height: 20px;
        }

        .hd-stat strong {
            display: block;
            font-family: 'Space Grotesk', sans-serif;
            font-size: .98rem;
            color: #142433;
        }

        .hd-stat span {
            font-size: .82rem;
            color: #5B6B7C;
        }

        .hd-h2 {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 1.45rem;
            color: #142433;
            margin: 2.75rem 0 .4rem;
        }

        .hd-sub {
            color: #5B6B7C;
            font-size: .95rem;
            margin-bottom: 1.25rem;
            max-width: 680px;
            line-height: 1.6;
        }

        .hd-topics {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
        }

        .hd-topic {
            background: #fff;
            border: 1px solid #E0E4EE;
            border-radius: 14px;
            padding: 1.25rem;
            text-decoration: none;
            color: inherit;
            display: block;
            transition: border-color .2s, box-shadow .2s, transform .2s;
        }

        .hd-topic:hover {
            border-color: #0B2545;
            box-shadow: 0 8px 24px rgba(11, 37, 69, .10);
            transform: translateY(-2px);
        }

        .hd-topic h3 {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 1rem;
            color: #142433;
            margin-bottom: .35rem;
        }

        .hd-topic p {
            font-size: .86rem;
            line-height: 1.55;
            color: #5B6B7C;
        }

        .hd-topic .go {
            display: inline-block;
            margin-top: .7rem;
            font-size: .82rem;
            font-weight: 600;
            color: #0B2545;
        }































        .hd-issues {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 1rem;
        }

        .hd-issue {
            background: #fff;
            border: 1px solid #E0E4EE;
            border-radius: 14px;
            padding: 1.15rem 1.25rem;
        }

        .hd-issue h4 {
            font-family: 'Space Grotesk', sans-serif;
            font-size: .95rem;
            color: #142433;
            margin-bottom: .4rem;
        }

        .hd-issue p {
            font-size: .85rem;
            line-height: 1.6;
            color: #5B6B7C;
        }

        .hd-issue a {
            color: #0B2545;
            font-weight: 600;
        }

        .hd-cta {
            margin-top: 2.5rem;
            background: #0B2545;
            color: #fff;
            border-radius: 16px;
            padding: 1.75rem;
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 1.5rem;
            align-items: center;
        }

        .hd-cta h3 {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 1.2rem;
            margin-bottom: .4rem;
        }

        .hd-cta p {
            font-size: .9rem;
            line-height: 1.6;
            color: #C3CDDD;
            max-width: 560px;
        }

        .hd-cta-list {
            list-style: none;
            margin-top: 1rem;
        }

        .hd-cta-list li {
            display: flex;
            gap: 1rem;
            font-size: .85rem;
            padding: .3rem 0;
        }

        .hd-cta-list li span:first-child {
            color: #8FA0BA;
            min-width: 52px;
        }

        .hd-cta-btns {
            display: flex;
            flex-direction: column;
            gap: .6rem;
            min-width: 170px;
        }

        .hd-btn {
            text-align: center;
            text-decoration: none;
            font-family: 'Space Grotesk', sans-serif;
            font-weight: 600;
            font-size: .92rem;
            padding: .8rem 1.2rem;
            border-radius: 12px;
        }

        .hd-btn-primary {
            background: #FFC53D;
            color: #142433;
        }

        .hd-btn-primary:hover {
            background: #FFD166;
        }

        .hd-btn-ghost {
            border: 1px solid rgba(255, 255, 255, .3);
            color: #fff;
        }

        .hd-btn-ghost:hover {
            background: rgba(255, 255, 255, .08);
        }

        @media (max-width: 760px) {
            .hd-cta {
                grid-template-columns: minmax(0, 1fr);
            }
        }

        .hd-note {
            margin-top: 2.25rem;
            font-size: .85rem;
            color: #5B6B7C;
            line-height: 1.6;
        }

        .hd-note a {
            color: #0B2545;
            font-weight: 600;
        }

        @media (max-width: 900px) {
            .hd-strip {
                grid-template-columns: minmax(0, 1fr);
            }

            .hd-issues {
                grid-template-columns: minmax(0, 1fr);
            }
        }


        footer {
            background: #0f172a;
            color: #94a3b8;
            padding: 2rem 5%;
            text-align: center;
            font-size: .78rem;
        }

        footer span {
            color: #64748b;
        }

    </style>
    <link rel="stylesheet" href="assets/css/elms-public.css?v=3">
    <style>
        /* ============================================================
       Match index.php — palette, nav width/size, hero, scrollbar
       ============================================================ */
        body {
            background: #F4F6FA;
            color: #142433;
            -webkit-text-size-adjust: 100%;
        }

        h1,
        h2,
        h3,
        h4 {
            letter-spacing: -0.015em;
        }

        .topbar,
        body>nav {
            padding-left: max(20px, calc((100% - 1240px) / 2));
            padding-right: max(20px, calc((100% - 1240px) / 2));
        }

        /* Desktop: logo and Log In sit nearer the left/right edges (not full-bleed) */
        @media (min-width: 901px) {
            body>nav {
                padding-left: clamp(24px, 3vw, 48px);
                padding-right: clamp(24px, 3vw, 48px);
            }
        }

        .topbar {
            background: #061A33;
            font-size: .8125rem;
            gap: 1.25rem;
            padding-top: .45rem;
            padding-bottom: .45rem;
        }

        .topbar a {
            color: #C3CDDD;
        }

        .topbar a:hover {
            color: #FFC53D;
        }

        body>nav {
            height: 64px;
            background: rgba(255, 255, 255, .92);
            border-bottom: 1px solid #E0E4EE;
            box-shadow: none;
        }

        .nav-brand {
            gap: .7rem;
        }

        .nav-logo-svg {
            width: 40px !important;
            height: 40px !important;
        }

        .nav-brand-text strong {
            font-size: 1.02rem;
            font-weight: 700;
            color: #142433;
            letter-spacing: -0.015em;
        }

        .nav-brand-text strong::after {
            display: block;
            font-family: 'Inter', sans-serif;
            font-size: .75rem;
            font-weight: 500;
            color: #5B6B7C;
            letter-spacing: 0;
            margin-top: 1px;
        }

        .nav-links a {
            font-family: 'Inter', sans-serif;
            font-weight: 500;
            font-size: .925rem;
            color: #5B6B7C;
            border-radius: 10px;
        }

        .nav-links a:hover {
            background: #E8EDF5;
            color: #142433;
        }

        .nav-links a.active {
            font-weight: 700;
            color: #142433;
        }

        .btn-login {
            background: #0B2545 !important;
            border-radius: 12px !important;
            padding: .7rem 1.25rem !important;
            font-family: 'Space Grotesk', sans-serif !important;
            box-shadow: 0 4px 14px rgba(11, 37, 69, .25) !important;
        }

        .btn-login:hover {
            background: #123A6B !important;
            transform: none !important;
        }

        /* Hero band: same navy gradient + glow as the index hero */
        .page-hero {
            background:
                radial-gradient(900px 420px at 85% -10%, rgba(255, 197, 61, .12), transparent 60%),
                radial-gradient(700px 500px at -10% 110%, rgba(14, 124, 123, .35), transparent 60%),
                linear-gradient(160deg, #0B2545, #123A6B) !important;
        }

        .page-hero::before {
            display: none;
        }

        .page-hero-eyebrow {
            background: rgba(255, 197, 61, .12) !important;
            color: #FFC53D !important;
            border: 1px solid rgba(255, 197, 61, .3);
        }

        .page-hero h1 {
            color: #fff;
        }

        .page-hero h1 span,
        .page-hero em {
            color: #FFC53D;
        }

        footer {
            background: #0f172a;
        }

        @media (max-width: 640px) {
            body>nav {
                height: 60px;
            }

            .nav-logo-svg {
                width: 36px !important;
                height: 36px !important;
            }

            .nav-brand-text strong::after {
                display: none;
            }
        }

        /* Thin scrollbar */
        html {
            scrollbar-width: thin;
            scrollbar-color: rgba(11, 37, 69, .45) transparent;
        }

        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        ::-webkit-scrollbar-track {
            background: transparent;
        }

        ::-webkit-scrollbar-thumb {
            background: rgba(11, 37, 69, .45);
            border-radius: 999px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: rgba(11, 37, 69, .7);
        }
    </style>
</head>
<body>
    <nav>
        <a href="index.php" class="nav-brand">
            <?php if ($__logoSrc): ?>
                <img src="<?= htmlspecialchars($__logoSrc) ?>" alt="Arandia College Logo" class="nav-logo-svg">
            <?php else: ?>
                <div class="nav-logo-svg"
                    style="display:flex;align-items:center;justify-content:center;background:#0B2545;color:#fff;font-weight:700;border-radius:8px;">
                    AC</div>
            <?php endif; ?>
            <div class="nav-brand-text">
                <strong>Arandia College</strong>
            </div>
        </a>
        <ul class="nav-links">
            <li><a href="index.php">Home</a></li>
            <li><a href="FAQ.php">FAQ</a></li>
            <li><a href="helpcenter.php">Help Center</a></li>
            <li><a href="contact.php">Contact Us</a></li>
            <li><a href="helpdesk.php" class="active" aria-current="page">Campus Helpdesk</a></li>
            <li><a href="login.php" class="btn-login">Log In</a></li>
        </ul>
    </nav>

    <div class="page-hero">
        <div class="page-hero-eyebrow">Support</div>
        <h1>Campus Helpdesk</h1>
        <p>Having trouble with your account, a module, or a submission? Start here for quick fixes and
            who to contact.</p>
    </div>

    <div class="hd-wrap">
        <div class="hd-strip">
            <div class="hd-stat">
                <div class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <polyline points="12 6 12 12 16 14"></polyline>
                    </svg></div>
                <div><strong>Mon to Fri, 8:00 AM - 5:00 PM</strong><span>Saturday until noon</span></div>
            </div>
            <div class="hd-stat">
                <div class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.96.36 1.9.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.91.34 1.85.57 2.81.7A2 2 0 0 1 22 16.92z">
                        </path>
                    </svg></div>
                <div><strong>(02) 8123-4567</strong><span>Phone support during office hours</span></div>
            </div>
            <div class="hd-stat">
                <div class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 4h16a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2z"></path>
                        <polyline points="22,6 12,13 2,6"></polyline>
                    </svg></div>
                <div><strong>1 to 2 business days</strong><span>Typical reply time for messages</span></div>
            </div>
        </div>

        <h2 class="hd-h2">Try a quick fix first</h2>
        <p class="hd-sub">Most eLMS concerns are answered in the guides below. If yours is not, contact the team.</p>
        <div class="hd-topics">
            <a href="login.php" class="hd-topic">
                <h3>Cannot log in</h3>
                <p>Use the forgot password option on the login page to receive a reset link by email.</p>
                <span class="go">Go to Log In</span>
            </a>
            <a href="FAQ.php" class="hd-topic">
                <h3>Common questions</h3>
                <p>Answers about accounts, grades, assignments, quizzes, and technical problems.</p>
                <span class="go">Open the FAQ</span>
            </a>
            <a href="helpcenter.php" class="hd-topic">
                <h3>Step-by-step guides</h3>
                <p>Student and teacher walkthroughs for using modules, submissions, and grading.</p>
                <span class="go">Open the Help Center</span>
            </a>
            <a href="privacy.php" class="hd-topic">
                <h3>Privacy concerns</h3>
                <p>Read how your data is handled, or report a suspected unauthorized access to your account.</p>
                <span class="go">Read the Privacy Policy</span>
            </a>
        </div>

        <h2 class="hd-h2">Common issues</h2>
        <p class="hd-sub">Quick answers to the problems we see most often.</p>
        <div class="hd-issues">
            <div class="hd-issue">
                <h4>I forgot my password</h4>
                <p>Open the <a href="login.php">login page</a>, choose the forgot password option, and enter your
                    registered email. A reset link will be sent to you.</p>
            </div>
            <div class="hd-issue">
                <h4>I cannot find my subjects or modules</h4>
                <p>Your subjects appear after the Registrar enrolls you. If they are missing, contact us with your
                    section and school ID.</p>
            </div>
            <div class="hd-issue">
                <h4>My file will not upload</h4>
                <p>Check that the file type is allowed for the activity and that your connection is stable. Then try
                    again, or contact us with the file name and the error shown.</p>
            </div>
            <div class="hd-issue">
                <h4>I think someone used my account</h4>
                <p>Change your password right away, then <a href="contact.php">contact us</a>. See the
                    <a href="privacy.php">Privacy Policy</a> for how your data is protected.</p>
            </div>
        </div>

        <div class="hd-cta">
            <div>
                <h3>Still need help?</h3>
                <p>If the guides above do not solve it, send our team a message. Tell us the page you were on and any
                    error you saw.</p>
                <ul class="hd-cta-list">
                    <li><span>Email</span><span>info@arandiacollege.edu.ph</span></li>
                    <li><span>Phone</span><span>(02) 8123-4567</span></li>
                    <li><span>Hours</span><span>Mon to Fri, 8:00 AM - 5:00 PM. Saturday until noon.</span></li>
                </ul>
            </div>
            <div class="hd-cta-btns">
                <a href="contact.php" class="hd-btn hd-btn-primary">Contact Us</a>
                <a href="FAQ.php" class="hd-btn hd-btn-ghost">Read the FAQ</a>
            </div>
        </div>

        <p class="hd-note">Messages you send are handled as described in our <a href="privacy.php">Privacy Policy</a>.</p>
    </div>

    <footer>
        <span>&copy; 2026 Arandia College eLMS. All rights reserved. &nbsp;&middot;&nbsp;
            <a href="privacy.php" style="color:inherit;">Privacy Policy</a> &nbsp;&middot;&nbsp;
            <a href="FAQ.php" style="color:inherit;">FAQ</a> &nbsp;&middot;&nbsp;
            <a href="contact.php" style="color:inherit;">Contact Us</a></span>
    </footer>

        <script src="assets/js/elms-public-nav.js?v=3" defer></script>
</body>
</html>