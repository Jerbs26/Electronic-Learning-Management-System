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
    <title>Privacy Policy — Arandia College eLMS</title>
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

        /* LAYOUT */
        .policy-layout {
            display: grid;
            grid-template-columns: 260px minmax(0, 1fr);
            gap: 2.5rem;
            padding: 3rem 5% 5rem;
            max-width: none;
            margin: 0 auto;
            align-items: start;
        }

        /* SIDEBAR TOC */
        .toc {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, .07);
            position: sticky;
            top: 90px;
        }

        .toc h3 {
            font-family: 'Space Grotesk', sans-serif;
            font-size: .85rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 1rem;
            text-transform: uppercase;
            letter-spacing: .06em;
        }

        .toc a {
            display: block;
            font-size: .8rem;
            color: #666;
            text-decoration: none;
            padding: .4rem .75rem;
            border-radius: 7px;
            margin-bottom: .25rem;
            transition: background .2s, color .2s;
            border-left: 2px solid transparent;
        }

        .toc a:hover,
        .toc a.active {
            background: #E8EDF5;
            color: var(--primary);
            border-left-color: #0B2545;
        }

        .toc-updated {
            margin-top: 1.5rem;
            font-size: .72rem;
            color: #aaa;
            padding-top: 1rem;
            border-top: 1px solid #f0f2f5;
        }

        /* POLICY CONTENT */
        .policy-content {
            background: white;
            border-radius: 20px;
            padding: 3rem 2.5rem;
            box-shadow: 0 4px 24px rgba(0, 0, 0, .07);
        }

        .policy-section {
            padding-bottom: 2.5rem;
            margin-bottom: 2.5rem;
            border-bottom: 1px solid #f0f2f5;
        }

        .policy-section:last-of-type {
            border-bottom: none;
            padding-bottom: 0;
            margin-bottom: 0;
        }

        .ps-eyebrow {
            display: inline-block;
            background: rgba(11, 37, 69, 0.1);
            color: var(--primary);
            font-size: .68rem;
            font-weight: 700;
            letter-spacing: .1em;
            text-transform: uppercase;
            padding: .2rem .8rem;
            border-radius: 100px;
            margin-bottom: .65rem;
        }

        .ps-title {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 1rem;
        }

        .ps-text {
            font-size: .9rem;
            color: #555;
            line-height: 1.8;
            margin-bottom: .85rem;
        }

        .ps-text:last-child {
            margin-bottom: 0;
        }

        .ps-text a {
            color: #0B2545;
            font-weight: 600;
        }

        .ps-list {
            list-style: none;
            margin: .5rem 0 .85rem;
        }

        .ps-list li {
            display: flex;
            align-items: flex-start;
            gap: .65rem;
            font-size: .9rem;
            color: #555;
            line-height: 1.65;
            padding: .5rem 0;
            border-bottom: 1px solid #f7f7f7;
        }

        .ps-list li:last-child {
            border-bottom: none;
        }

        .ps-list li::before {
            content: '';
            width: 14px;
            height: 14px;
            flex-shrink: 0;
            margin-top: .2rem;
            background-color: var(--primary);
            -webkit-mask: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23000' stroke-width='3' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='20 6 9 17 4 12'/%3E%3C/svg%3E") center / contain no-repeat;
            mask: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23000' stroke-width='3' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='20 6 9 17 4 12'/%3E%3C/svg%3E") center / contain no-repeat;
        }

        .ps-highlight {
            background: #E8EDF5;
            border-left: 4px solid var(--primary);
            border-radius: 0 10px 10px 0;
            padding: 1rem 1.25rem;
            margin: 1rem 0;
            font-size: .88rem;
            color: var(--primary);
            font-weight: 600;
            line-height: 1.65;
        }

        .rights-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 1rem;
            margin-top: 1rem;
        }

        /* Icon and title on one line, vertically centered; description below */
        .right-item {
            background: #f8f9fe;
            border-radius: 10px;
            padding: 1rem;
            display: grid;
            grid-template-columns: auto minmax(0, 1fr);
            align-items: center;
            column-gap: .75rem;
            row-gap: .6rem;
        }

        .ri-icon {
            width: 32px;
            height: 32px;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 9px;
            background: rgba(11, 37, 69, 0.08);
            color: var(--primary);
        }

        .ri-icon svg {
            width: 17px;
            height: 17px;
        }

        .ri-title {
            font-family: 'Space Grotesk', sans-serif;
            font-size: .88rem;
            font-weight: 700;
            color: var(--text-dark);
            margin: 0;
        }

        .ri-desc {
            grid-column: 1 / -1;
            margin: 0;
            font-size: .78rem;
            color: #777;
            line-height: 1.5;
        }

        /* ACCEPTANCE BAR */
        .accept-bar {
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            border-radius: 16px;
            padding: 2rem 2rem;
            display: flex;
            align-items: center;
            gap: 2rem;
            flex-wrap: wrap;
            margin-top: 2rem;
        }

        .ab-text strong {
            display: block;
            font-family: 'Space Grotesk', sans-serif;
            font-size: 1rem;
            font-weight: 700;
            color: white;
            margin-bottom: .25rem;
        }

        .ab-text span {
            font-size: .82rem;
            color: rgba(255, 255, 255, .75);
        }

        .ab-btn {
            background: white;
            color: var(--primary);
            padding: .7rem 1.6rem;
            border-radius: 9px;
            font-weight: 700;
            font-size: .88rem;
            border: none;
            cursor: pointer;
            font-family: 'Space Grotesk', sans-serif;
            transition: opacity .2s;
            white-space: nowrap;
        }

        .ab-btn:hover {
            opacity: .88;
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

        @media(max-width:860px) {
            .policy-layout {
                grid-template-columns: minmax(0, 1fr);
            }

            .toc {
                position: static;
            }

            .rights-grid {
                grid-template-columns: minmax(0, 1fr);
            }

            .nav-links {
                display: none;
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
            <li><a href="FAQ.php">FAQ</a></li>
            <li><a href="helpcenter.php">Help Center</a></li>
            <li><a href="contact.php">Contact Us</a></li>
            <li><a href="helpdesk.php">Campus Helpdesk</a></li>
            <li><a href="login.php" class="btn-login">Log In</a></li>
        </ul>
    </nav>

    <div class="page-hero">
        <div class="page-hero-eyebrow">Legal</div>
        <h1>Privacy Policy</h1>
        <p>Effective Date: January 1, 2026 &nbsp;·&nbsp; Last Updated: June 1, 2026</p>
    </div>

    <div class="policy-layout">

        <!-- TOC SIDEBAR -->
        <div class="toc">
            <h3>Contents</h3>
            <a href="#section-intro">1. Introduction</a>
            <a href="#section-collect">2. Data We Collect</a>
            <a href="#section-use">3. How We Use Your Data</a>
            <a href="#section-share">4. Data Sharing</a>
            <a href="#section-security">5. Data Security</a>
            <a href="#section-rights">6. Your Rights</a>
            <a href="#section-cookies">7. Cookies</a>
            <a href="#section-minors">8. Minors & Students</a>
            <a href="#section-changes">9. Policy Changes</a>
            <a href="#section-contact">10. Contact</a>
            <div class="toc-updated">Last updated: June 1, 2026</div>
        </div>

        <!-- CONTENT -->
        <div class="policy-content">

            <div class="policy-section" id="section-intro">
                <h2 class="ps-title">Introduction</h2>
                <p class="ps-text">Arandia College ("the College," "we," "our," or "us") is committed to protecting the
                    privacy and security of your personal information. This Privacy Policy describes how the Arandia
                    College Electronic Learning Management System (eLMS) collects, uses, stores, and protects data about
                    students, teachers, and other users of our platform.</p>
                <p class="ps-text">By accessing and using the Arandia College eLMS, you agree to the terms of this
                    Privacy Policy. If you do not agree, please do not use the platform and contact your administrator
                    for assistance.</p>
                <div class="ps-highlight">This policy applies to all users of the Arandia College eLMS, including
                    students, faculty, staff, and administrators.</div>
            </div>

            <div class="policy-section" id="section-collect">
                <h2 class="ps-title">Data We Collect</h2>
                <p class="ps-text">We collect information necessary to provide educational services and maintain the
                    eLMS platform. The types of data we collect include:</p>
                <ul class="ps-list">
                    <li><strong>Account Information</strong> — Name, school ID number, email address, password
                        (encrypted), program, year level, and role (student, teacher, admin).</li>
                    <li><strong>Academic Data</strong> — Course enrollments, assignment submissions, quiz answers and
                        scores, module completion records, and grades.</li>
                    <li><strong>Activity Logs</strong> — Login timestamps, pages visited, time spent on modules, and
                        platform activity for academic monitoring purposes.</li>
                    <li><strong>Uploaded Files</strong> — Documents, images, and other files you submit as part of
                        assignments or course materials.</li>
                    <li><strong>Communication Data</strong> — Messages sent through the Virtual Classroom, discussion
                        board posts, and announcements.</li>
                    <li><strong>Device Information</strong> — Browser type, device type, operating system, and IP
                        address for security and troubleshooting purposes.</li>
                </ul>
            </div>

            <div class="policy-section" id="section-use">
                <h2 class="ps-title">How We Use Your Data</h2>
                <p class="ps-text">Your data is used solely for the purpose of delivering and improving educational
                    services. Specifically, we use your information to:</p>
                <ul class="ps-list">
                    <li>Manage your account and authenticate your access to the platform.</li>
                    <li>Provide access to course materials, assignments, quizzes, and grading tools.</li>
                    <li>Track academic progress and generate performance reports for students and instructors.</li>
                    <li>Send system notifications, including deadline reminders and grade updates.</li>
                    <li>Facilitate communication between students and faculty.</li>
                    <li>Investigate technical issues and improve platform performance and security.</li>
                    <li>Comply with Arandia College's academic policies and regulatory requirements.</li>
                </ul>
                <p class="ps-text">We do not use your personal data for advertising, marketing to third parties, or any
                    commercial purpose unrelated to your education.</p>
            </div>

            <div class="policy-section" id="section-share">
                <h2 class="ps-title">Data Sharing and Disclosure</h2>
                <p class="ps-text">Arandia College does not sell, rent, or trade your personal information. We may share
                    your data only in the following limited circumstances:</p>
                <ul class="ps-list">
                    <li><strong>Within the College</strong> — Authorized faculty, advisers, and administrators may
                        access your academic records as needed to fulfill their educational duties.</li>
                    <li><strong>Service Providers</strong> — We may work with trusted third-party technology providers
                        (such as hosting and email services) who are contractually bound to protect your data and use it
                        only as directed by the College.</li>
                    <li><strong>Legal Obligations</strong> — We may disclose information if required by law, court
                        order, or government authority.</li>
                    <li><strong>Emergency Situations</strong> — In cases where there is a risk to the safety of a
                        student or others, we may share relevant information with appropriate parties.</li>
                </ul>
                <div class="ps-highlight">We will never sell your personal data to advertisers or third-party marketers.
                </div>
            </div>

            <div class="policy-section" id="section-security">
                <h2 class="ps-title">Data Security</h2>
                <p class="ps-text">We take the security of your personal information seriously. Arandia College
                    implements appropriate technical and organizational measures to protect your data, including:</p>
                <ul class="ps-list">
                    <li>Encrypted password storage using industry-standard hashing algorithms.</li>
                    <li>Secure HTTPS connections for all data transmitted between your device and our servers.</li>
                    <li>Access controls that restrict data to authorized personnel only.</li>
                    <li>Regular security reviews and system updates.</li>
                    <li>Secure backups to prevent data loss.</li>
                </ul>
                <p class="ps-text">While we strive to protect your information, no system is entirely immune to security
                    risks. You are responsible for keeping your account credentials confidential and for immediately
                    reporting any unauthorized access to the <a href="helpdesk.php">Campus Helpdesk</a>.</p>
            </div>

            <div class="policy-section" id="section-rights">
                <h2 class="ps-title">Your Rights</h2>
                <p class="ps-text">As a user of the Arandia College eLMS, you have the following rights regarding your
                    personal data under applicable Philippine law, including the Data Privacy Act of 2012 (Republic Act
                    No. 10173):</p>
                <div class="rights-grid">
                    <div class="right-item"><span class="ri-icon"><svg viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg></span>
                        <div class="ri-title">Right to Access</div>
                        <p class="ri-desc">You may request a copy of the personal data we hold about you at any time.
                        </p>
                    </div>
                    <div class="right-item"><span class="ri-icon"><svg viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5z"></path>
                            </svg></span>
                        <div class="ri-title">Right to Correction</div>
                        <p class="ri-desc">You may ask us to correct any inaccurate or outdated information in your
                            records.</p>
                    </div>
                    <div class="right-item"><span class="ri-icon"><svg viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="3 6 5 6 21 6"></polyline>
                                <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"></path>
                                <path d="M10 11v6"></path>
                                <path d="M14 11v6"></path>
                                <path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"></path>
                            </svg></span>
                        <div class="ri-title">Right to Erasure</div>
                        <p class="ri-desc">You may request deletion of your data, subject to legal and academic
                            record-keeping requirements.</p>
                    </div>
                    <div class="right-item"><span class="ri-icon"><svg viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"></circle>
                                <line x1="4.9" y1="4.9" x2="19.1" y2="19.1"></line>
                            </svg></span>
                        <div class="ri-title">Right to Object</div>
                        <p class="ri-desc">You may object to the processing of your data in certain circumstances.</p>
                    </div>
                    <div class="right-item"><span class="ri-icon"><svg viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21 8L12 3 3 8l9 5 9-5z"></path>
                                <path d="M3 8v8l9 5 9-5V8"></path>
                                <path d="M12 13v8"></path>
                            </svg></span>
                        <div class="ri-title">Right to Portability</div>
                        <p class="ri-desc">You may request a copy of your data in a structured, machine-readable format.
                        </p>
                    </div>
                    <div class="right-item"><span class="ri-icon"><svg viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path
                                    d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z">
                                </path>
                                <line x1="12" y1="9" x2="12" y2="13"></line>
                                <line x1="12" y1="17" x2="12.01" y2="17"></line>
                            </svg></span>
                        <div class="ri-title">Right to Complain</div>
                        <p class="ri-desc">You may file a complaint with the National Privacy Commission if you believe
                            your rights have been violated.</p>
                    </div>
                </div>
                <p class="ps-text" style="margin-top:1.25rem;">To exercise any of these rights, contact our Data
                    Protection Officer at <strong>privacy@arandiacollege.edu.ph</strong>.</p>
            </div>

            <div class="policy-section" id="section-cookies">
                <h2 class="ps-title">Cookies and Tracking</h2>
                <p class="ps-text">The Arandia College eLMS uses essential cookies to maintain your login session and
                    ensure the platform functions correctly. These cookies are necessary for the system to work and do
                    not track you across other websites.</p>
                <p class="ps-text">We do not use advertising cookies, third-party tracking pixels, or analytics services
                    that share your data with external companies. You may configure your browser to block cookies, but
                    doing so may prevent you from logging in or using certain features of the platform.</p>
            </div>

            <div class="policy-section" id="section-minors">
                <h2 class="ps-title">Minors and Student Privacy</h2>
                <p class="ps-text">Arandia College is an educational institution and many of our students may be minors.
                    We are committed to handling student data with the utmost care and in compliance with the Data
                    Privacy Act of 2012 and the Family Code of the Philippines.</p>
                <p class="ps-text">Student data is collected solely for academic and educational purposes. Parents or
                    legal guardians of minor students may request access to their child's academic records by visiting
                    the Registrar's Office with valid identification.</p>
                <div class="ps-highlight">We do not knowingly allow minors to create accounts independently. All student
                    accounts on the eLMS are created through the official school enrollment process.</div>
            </div>

            <div class="policy-section" id="section-changes">
                <h2 class="ps-title">Changes to This Policy</h2>
                <p class="ps-text">Arandia College reserves the right to update or modify this Privacy Policy at any
                    time. Significant changes will be communicated to users through an in-platform announcement or email
                    notification at least seven (7) days before taking effect.</p>
                <p class="ps-text">Your continued use of the Arandia College eLMS after any changes to this policy
                    constitutes your acceptance of the revised terms. We encourage you to review this policy
                    periodically.</p>
            </div>

            <div class="policy-section" id="section-contact">
                <h2 class="ps-title">Contact Our Privacy Office</h2>
                <p class="ps-text">If you have questions, concerns, or requests related to this Privacy Policy or the
                    handling of your personal data, you may reach our Data Protection Officer through any of the
                    following:</p>
                <ul class="ps-list">
                    <li><strong>Email:</strong> privacy@arandiacollege.edu.ph</li>
                    <li><strong>Phone:</strong> (02) 8123-4567</li>
                    <li><strong>Office:</strong> Admin Building, Room 104, Arandia College, Quezon City</li>
                    <li><strong>Office Hours:</strong> Monday to Friday, 8:00 AM – 5:00 PM</li>
                </ul>
                <p class="ps-text">You may also reach out through our <a href="contact.php">Contact Us page</a> or
                    visit the <a href="helpdesk.php">Campus Helpdesk</a> for general eLMS concerns.</p>
            </div>

            <div class="accept-bar">
                <div class="ab-text">
                    <strong>You have read and understood this Privacy Policy.</strong>
                    <span>By using Arandia College eLMS, you consent to the terms described above.</span>
                </div>
                <button class="ab-btn" onclick="this.textContent='Acknowledged'; this.disabled=true;">I
                    Understand</button>
            </div>

        </div>
    </div>

    <footer>
        <span>&copy; 2026 Arandia College eLMS. All rights reserved. &nbsp;·&nbsp; In compliance with Republic Act No.
            10173 (Data Privacy Act of 2012)</span>
    </footer>

    <script>
        // Highlight active TOC section on scroll
        const sections = document.querySelectorAll('.policy-section');
        const tocLinks = document.querySelectorAll('.toc a');
        window.addEventListener('scroll', () => {
            let current = '';
            sections.forEach(s => {
                if (window.scrollY >= s.offsetTop - 120) current = s.id;
            });
            tocLinks.forEach(a => {
                a.classList.toggle('active', a.getAttribute('href') === '#' + current);
            });
        });
    </script>
    <script src="assets/js/elms-public-nav.js?v=3" defer></script>
</body>

</html>