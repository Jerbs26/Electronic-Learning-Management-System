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
    <title>Help Center — Arandia College eLMS</title>
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
            padding: 4.5rem 5% 5rem;
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
            font-size: 2.5rem;
            font-weight: 700;
            color: white;
            letter-spacing: -.02em;
            margin-bottom: .6rem;
            position: relative;
        }

        .page-hero p {
            font-size: .95rem;
            color: rgba(255, 255, 255, .75);
            max-width: 500px;
            margin: 0 auto 2rem;
            line-height: 1.65;
            position: relative;
        }

        /* HERO SEARCH */
        .hero-search {
            display: flex;
            align-items: center;
            gap: .75rem;
            background: white;
            border-radius: 14px;
            padding: .75rem 1.4rem;
            width: 100%;
            max-width: 720px;
            margin: 0 auto;
            box-shadow: 0 8px 32px rgba(0, 0, 0, .2);
            position: relative;
        }

        .hero-search input {
            border: none;
            outline: none;
            font-family: 'Inter', sans-serif;
            font-size: .95rem;
            color: var(--text-dark);
            width: 100%;
        }

        .hero-search svg {
            width: 20px;
            height: 20px;
            stroke: #aaa;
            flex-shrink: 0;
        }

        /* BODY */
        .hc-body {
            padding: 2.5rem clamp(24px, 3vw, 48px) 4.5rem;
            max-width: none;
            margin: 0;
        }

        /* QUICK LINKS */
        .quick-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 1.25rem;
            margin-bottom: 3.5rem;
        }

        .quick-card {
            background: white;
            border-radius: 16px;
            padding: 2rem 1.5rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, .07);
            border: 1px solid rgba(0, 0, 0, .05);
            text-align: center;
            text-decoration: none;
            color: inherit;
            transition: transform .3s, box-shadow .3s;
            display: block;
            position: relative;
            overflow: hidden;
        }

        .quick-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--grad);
            transform: scaleX(0);
            transition: transform .3s;
            transform-origin: left;
        }

        .quick-card:hover::before {
            transform: scaleX(1);
        }

        .quick-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 40px rgba(0, 0, 0, .12);
        }

        @media (min-width: 1500px) {
            .quick-grid {
                grid-template-columns: repeat(6, minmax(0, 1fr));
            }
        }

        .qc-1 {
            --grad: linear-gradient(90deg, #0B2545, #123A6B);
            --icon-color: #0B2545;
        }

        .qc-2 {
            --grad: linear-gradient(90deg, #B7861A, #FFC53D);
            --icon-color: #B7861A;
        }

        .qc-3 {
            --grad: linear-gradient(90deg, #0E7C7B, #123A6B);
            --icon-color: #0E7C7B;
        }

        .qc-4 {
            --grad: linear-gradient(90deg, #3D5A80, #0B2545);
            --icon-color: #3D5A80;
        }

        .qc-5 {
            --grad: linear-gradient(90deg, #FFC53D, #B7861A);
            --icon-color: #B7861A;
        }

        .qc-6 {
            --grad: linear-gradient(90deg, #0B2545, #3D5A80);
            --icon-color: #0B2545;
        }

        .qc-icon {
            width: 42px;
            height: 42px;
            margin: 0 auto .85rem;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            background: rgba(0, 0, 0, .04);
            color: var(--icon-color, var(--primary));
        }

        .qc-icon svg {
            width: 22px;
            height: 22px;
        }

        .qc-title {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 1rem;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: .35rem;
        }

        .qc-desc {
            font-size: .78rem;
            color: #888;
            line-height: 1.55;
        }

        .qc-arrow {
            display: inline-block;
            margin-top: .75rem;
            font-size: .78rem;
            font-weight: 700;
            color: var(--primary);
        }

        /* GUIDE SECTIONS */
        .section-eyebrow {
            display: inline-block;
            background: rgba(11, 37, 69, 0.1);
            color: var(--primary);
            font-size: .72rem;
            font-weight: 700;
            letter-spacing: .1em;
            text-transform: uppercase;
            padding: .3rem 1rem;
            border-radius: 100px;
            margin-bottom: .75rem;
        }

        .section-title {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 1.6rem;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: .4rem;
        }

        .section-sub {
            font-size: .88rem;
            color: #666;
            line-height: 1.65;
            margin-bottom: 2rem;
        }

        .guide-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(min(100%, 420px), 1fr));
            gap: 1.25rem;
            margin-bottom: 3.5rem;
        }

        .guide-card {
            background: white;
            border-radius: 14px;
            padding: 1.5rem 1.5rem;
            box-shadow: 0 2px 12px rgba(0, 0, 0, .05);
            border: 1px solid rgba(0, 0, 0, .05);
            display: flex;
            gap: 1rem;
            align-items: flex-start;
        }

        .gc-num {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            color: white;
            font-family: 'Space Grotesk', sans-serif;
            font-weight: 700;
            font-size: .9rem;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .gc-title {
            font-family: 'Space Grotesk', sans-serif;
            font-size: .95rem;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: .3rem;
        }

        .gc-desc {
            font-size: .82rem;
            color: #666;
            line-height: 1.6;
        }

        /* CTA STRIP */
        .cta-strip {
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            border-radius: 20px;
            padding: 3rem 2.5rem;
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 2rem;
            align-items: center;
        }

        .cta-strip h3 {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 1.5rem;
            font-weight: 700;
            color: white;
            margin-bottom: .4rem;
        }

        .cta-strip p {
            color: rgba(255, 255, 255, .75);
            font-size: .88rem;
        }

        .cta-btns {
            display: flex;
            gap: .75rem;
            flex-wrap: wrap;
        }

        .cta-btn {
            padding: .75rem 1.5rem;
            border-radius: 10px;
            font-weight: 700;
            font-size: .88rem;
            text-decoration: none;
            transition: opacity .2s;
            white-space: nowrap;
            display: inline-flex;
            align-items: center;
            gap: .5rem;
        }

        .cta-btn svg {
            width: 17px;
            height: 17px;
            flex-shrink: 0;
        }

        .cta-btn-w {
            background: white;
            color: var(--primary);
        }

        .cta-btn-g {
            background: rgba(255, 255, 255, .15);
            color: white;
            border: 1px solid rgba(255, 255, 255, .35);
        }

        .cta-btn:hover {
            opacity: .85;
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

        @media(max-width:900px) {
            .quick-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .guide-grid {
                grid-template-columns: minmax(0, 1fr);
            }

            .cta-strip {
                grid-template-columns: minmax(0, 1fr);
            }

            .nav-links {
                display: none;
            }
        }

        @media(max-width:600px) {
            .quick-grid {
                grid-template-columns: minmax(0, 1fr);
            }
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
            <li><a href="index.php#features">Features</a></li>
            <li><a href="FAQ.php">FAQ</a></li>
            <li><a href="helpcenter.php" class="active" aria-current="page">Help Center</a></li>
            <li><a href="contact.php">Contact Us</a></li>
            <li><a href="helpdesk.php">Campus Helpdesk</a></li>
            <li><a href="login.php" class="btn-login">Log In</a></li>
        </ul>
    </nav>

    <div class="page-hero">
        <div class="page-hero-eyebrow">Help Center</div>
        <h1>How Can We Help You?</h1>
        <p>Browse guides, tutorials, and resources to get the most out of the Arandia College eLMS platform.</p>
        <div class="hero-search">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                stroke-linejoin="round">
                <circle cx="11" cy="11" r="8"></circle>
                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
            </svg>
            <input type="text" placeholder="Search for guides, tutorials, or topics..." id="hcSearch">
        </div>
    </div>

    <div class="hc-body">

        <!-- QUICK LINKS -->
        <div class="quick-grid" style="margin-top:.5rem;">
            <a href="FAQ.php" class="quick-card qc-1">
                <span class="qc-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 2-3 4"></path>
                        <line x1="12" y1="17" x2="12.01" y2="17"></line>
                    </svg></span>
                <div class="qc-title">Frequently Asked Questions</div>
                <p class="qc-desc">Quick answers to the most common student and teacher questions.</p>
                <div class="qc-arrow">Browse FAQ →</div>
            </a>
            <a href="helpdesk.php" class="quick-card qc-2">
                <span class="qc-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round">
                        <rect x="2" y="4" width="20" height="14" rx="2"></rect>
                        <line x1="8" y1="21" x2="16" y2="21"></line>
                        <line x1="12" y1="18" x2="12" y2="21"></line>
                    </svg></span>
                <div class="qc-title">Campus Helpdesk</div>
                <p class="qc-desc">Quick fixes for common eLMS problems and who to contact when you need more help.</p>
                <div class="qc-arrow">Go to Helpdesk →</div>
            </a>
            <a href="contact.php" class="quick-card qc-3">
                <span class="qc-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round">
                        <rect x="2" y="4" width="20" height="16" rx="2"></rect>
                        <path d="M22 6l-10 7L2 6"></path>
                    </svg></span>
                <div class="qc-title">Contact Us</div>
                <p class="qc-desc">Send us a message and our team will respond within 1–2 business days.</p>
                <div class="qc-arrow">Send a Message →</div>
            </a>
            <a href="#student-guide" class="quick-card qc-4">
                <span class="qc-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 10L12 5 2 10l10 5 10-5z"></path>
                        <path d="M6 12v5c0 1.66 2.69 3 6 3s6-1.34 6-3v-5"></path>
                    </svg></span>
                <div class="qc-title">Student Guide</div>
                <p class="qc-desc">Step-by-step instructions for students on using the platform.</p>
                <div class="qc-arrow">Read Guide →</div>
            </a>
            <a href="#teacher-guide" class="quick-card qc-5">
                <span class="qc-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="4" width="18" height="12" rx="1"></rect>
                        <line x1="3" y1="20" x2="21" y2="20"></line>
                        <line x1="8" y1="20" x2="8" y2="16"></line>
                    </svg></span>
                <div class="qc-title">Teacher Guide</div>
                <p class="qc-desc">Learn how to set up courses, post modules, and grade students.</p>
                <div class="qc-arrow">Read Guide →</div>
            </a>
            <a href="privacy.php" class="quick-card qc-6">
                <span class="qc-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="11" width="18" height="11" rx="2"></rect>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                    </svg></span>
                <div class="qc-title">Privacy Policy</div>
                <p class="qc-desc">Understand how we collect, use, and protect your personal data.</p>
                <div class="qc-arrow">Read Policy →</div>
            </a>
        </div>

        <!-- STUDENT GUIDE -->
        <div id="student-guide">
            <div class="section-eyebrow">For Students</div>
            <h2 class="section-title">Student Step-by-Step Guide</h2>
            <p class="section-sub">New to Arandia College eLMS? Follow these steps to get started with your digital
                learning.</p>
            <div class="guide-grid">
                <div class="guide-card">
                    <div class="gc-num">1</div>
                    <div>
                        <div class="gc-title">Create Your Account</div>
                        <p class="gc-desc">Go to the Login page and click "Create Account." Enter your school ID or
                            registered email and set a secure password.</p>
                    </div>
                </div>
                <div class="guide-card">
                    <div class="gc-num">2</div>
                    <div>
                        <div class="gc-title">Log In and Update Profile</div>
                        <p class="gc-desc">After logging in, go to your profile settings and complete your information,
                            including your program and year level.</p>
                    </div>
                </div>
                <div class="guide-card">
                    <div class="gc-num">3</div>
                    <div>
                        <div class="gc-title">Enroll in Your Subjects</div>
                        <p class="gc-desc">Go to "Courses" and click "Enroll" on your assigned subjects. Use the
                            enrollment key provided by your teacher if required.</p>
                    </div>
                </div>
                <div class="guide-card">
                    <div class="gc-num">4</div>
                    <div>
                        <div class="gc-title">Access Learning Materials</div>
                        <p class="gc-desc">Inside each course, go to "Modules" to read uploaded lessons and
                            files shared by your instructor.</p>
                    </div>
                </div>
                <div class="guide-card">
                    <div class="gc-num">5</div>
                    <div>
                        <div class="gc-title">Submit Assignments</div>
                        <p class="gc-desc">Go to "Assignments," pick the task, attach your file or type your answer, and
                            hit "Submit" before the deadline.</p>
                    </div>
                </div>
                <div class="guide-card">
                    <div class="gc-num">6</div>
                    <div>
                        <div class="gc-title">Take Quizzes and Exams</div>
                        <p class="gc-desc">Open "Quizzes" inside your course. Read the instructions carefully, answer
                            all questions, and submit when done.</p>
                    </div>
                </div>
                <div class="guide-card">
                    <div class="gc-num">7</div>
                    <div>
                        <div class="gc-title">Track Your Progress</div>
                        <p class="gc-desc">Visit "My Grades" to see your scores, completion rate, and overall standing
                            per subject anytime.</p>
                    </div>
                </div>
                <div class="guide-card">
                    <div class="gc-num">8</div>
                    <div>
                        <div class="gc-title">Communicate with Teachers</div>
                        <p class="gc-desc">Use the Virtual Classroom to post questions in discussion boards or send a
                            message directly to your instructor.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- TEACHER GUIDE -->
        <div id="teacher-guide">
            <div class="section-eyebrow">For Teachers</div>
            <h2 class="section-title">Teacher & Faculty Guide</h2>
            <p class="section-sub">Set up your courses and manage your classes effectively using the eLMS tools.</p>
            <div class="guide-grid">
                <div class="guide-card">
                    <div class="gc-num">1</div>
                    <div>
                        <div class="gc-title">Set Up Your Course</div>
                        <p class="gc-desc">Log in and go to "My Courses." Click "Create Course," fill in the subject
                            details, and set it to visible for students.</p>
                    </div>
                </div>
                <div class="guide-card">
                    <div class="gc-num">2</div>
                    <div>
                        <div class="gc-title">Upload Learning Modules</div>
                        <p class="gc-desc">Inside your course, go to "Modules" and upload PDF, PPTX, or Word files
                            for your students.</p>
                    </div>
                </div>
                <div class="guide-card">
                    <div class="gc-num">3</div>
                    <div>
                        <div class="gc-title">Create Assignments</div>
                        <p class="gc-desc">Use the "Assignments" feature to post tasks with instructions, file type
                            restrictions, and a clear deadline.</p>
                    </div>
                </div>
                <div class="guide-card">
                    <div class="gc-num">4</div>
                    <div>
                        <div class="gc-title">Build Quizzes and Exams</div>
                        <p class="gc-desc">Create online quizzes with multiple choice, true/false, and essay questions.
                            Set time limits and attempt rules.</p>
                    </div>
                </div>
                <div class="guide-card">
                    <div class="gc-num">5</div>
                    <div>
                        <div class="gc-title">Grade Student Work</div>
                        <p class="gc-desc">Go to the "Submissions" section of each assignment to review and grade
                            student work and leave feedback.</p>
                    </div>
                </div>
                <div class="guide-card">
                    <div class="gc-num">6</div>
                    <div>
                        <div class="gc-title">Post Announcements</div>
                        <p class="gc-desc">Keep your students informed by posting announcements inside the Virtual
                            Classroom. Students will be notified automatically.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- CTA -->
        <div class="cta-strip">
            <div>
                <h3>Still need help?</h3>
                <p>Check the Helpdesk for quick fixes, or send our support team a message.</p>
            </div>
            <div class="cta-btns">
                <a href="helpdesk.php" class="cta-btn cta-btn-w"><svg viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="2" y="4" width="20" height="14" rx="2"></rect>
                        <line x1="8" y1="21" x2="16" y2="21"></line>
                        <line x1="12" y1="18" x2="12" y2="21"></line>
                    </svg>Helpdesk</a>
                <a href="contact.php" class="cta-btn cta-btn-g"><svg viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="2" y="4" width="20" height="16" rx="2"></rect>
                        <path d="M22 6l-10 7L2 6"></path>
                    </svg>Contact Us</a>
            </div>
        </div>

    </div>

    <footer>
        <span>&copy; 2026 Arandia College eLMS. All rights reserved. &nbsp;&middot;&nbsp;
            <a href="privacy.php" style="color:inherit;">Privacy Policy</a> &nbsp;&middot;&nbsp;
            <a href="FAQ.php" style="color:inherit;">FAQ</a> &nbsp;&middot;&nbsp;
            <a href="helpdesk.php" style="color:inherit;">Campus Helpdesk</a></span>
    </footer>

    <script>
        document.getElementById('hcSearch').addEventListener('input', function () {
            const val = this.value.toLowerCase();
            if (!val) {
                document.querySelectorAll('.guide-card, .quick-card').forEach(c => c.style.opacity = '1');
                return;
            }
            document.querySelectorAll('.guide-card').forEach(c => {
                c.style.opacity = c.textContent.toLowerCase().includes(val) ? '1' : '.3';
            });
        });
    </script>
    <script src="assets/js/elms-public-nav.js?v=3" defer></script>
</body>

</html>