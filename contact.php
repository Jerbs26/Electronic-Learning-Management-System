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
    <title>Contact Us — Arandia College eLMS</title>
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
            color: var(--text-light);
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
            font-size: .95rem;
            color: rgba(255, 255, 255, .75);
            max-width: 520px;
            margin: 0 auto;
            line-height: 1.65;
            position: relative;
        }

        /* BODY */
        .contact-body {
            padding: 2.5rem clamp(24px, 3vw, 48px) 4.5rem;
            max-width: none;
            margin: 0;
            display: grid;
            grid-template-columns: minmax(0, 1.35fr) minmax(0, 1fr);
            gap: 2.5rem;
            align-items: start;
        }

        /* FORM SIDE */
        .form-card {
            background: white;
            border-radius: 20px;
            padding: 2.5rem 2rem;
            box-shadow: 0 4px 24px rgba(0, 0, 0, .07);
        }

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
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: .35rem;
        }

        .section-sub {
            font-size: .85rem;
            color: #666;
            line-height: 1.65;
            margin-bottom: 2rem;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: .4rem;
            margin-bottom: 1.2rem;
        }

        .form-row2 {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 1rem;
        }

        label {
            font-size: .78rem;
            font-weight: 700;
            color: #444;
            letter-spacing: .03em;
        }

        input,
        select,
        textarea {
            border: 1.5px solid #e0e0e0;
            border-radius: 9px;
            padding: .65rem 1rem;
            font-family: 'Inter', sans-serif;
            font-size: .88rem;
            color: var(--text-dark);
            outline: none;
            transition: border .2s;
            background: #fafafa;
        }

        input:focus,
        select:focus,
        textarea:focus {
            border-color: var(--primary);
            background: white;
        }

        textarea {
            resize: vertical;
            min-height: 140px;
        }

        .submit-btn {
            background: linear-gradient(90deg, var(--primary), var(--primary-light));
            color: white;
            border: none;
            padding: .85rem 2.2rem;
            border-radius: 10px;
            font-family: 'Space Grotesk', sans-serif;
            font-weight: 700;
            font-size: .95rem;
            cursor: pointer;
            transition: opacity .2s;
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            width: 100%;
            justify-content: center;
        }

        .submit-btn:hover {
            opacity: .88;
        }

        .submit-btn svg {
            width: 17px;
            height: 17px;
            flex-shrink: 0;
        }

        .success-msg {
            display: none;
            background: #e6fff4;
            border: 1.5px solid #00C97F;
            border-radius: 10px;
            padding: 1.25rem 1.5rem;
            color: #006644;
            font-weight: 600;
            font-size: .88rem;
            margin-top: 1rem;
        }

        /* INFO SIDE */
        .info-side {
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
        }

        .info-card {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, .07);
            border: 1px solid rgba(0, 0, 0, .05);
            display: flex;
            gap: 1rem;
            align-items: flex-start;
        }

        .ic-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background: rgba(11, 37, 69, 0.08);
            color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .ic-icon svg {
            width: 19px;
            height: 19px;
        }

        .ic-label {
            font-size: .7rem;
            font-weight: 700;
            letter-spacing: .1em;
            text-transform: uppercase;
            color: var(--primary);
            margin-bottom: .2rem;
        }

        .ic-val {
            font-family: 'Space Grotesk', sans-serif;
            font-size: .95rem;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: .15rem;
        }

        .ic-note {
            font-size: .78rem;
            color: #888;
            line-height: 1.5;
        }

        .map-placeholder {
            background: linear-gradient(135deg, #E8EDF5, #DCE4F0);
            border-radius: 16px;
            height: 180px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: .5rem;
            color: var(--primary);
        }

        .map-placeholder svg {
            width: 32px;
            height: 32px;
        }

        .map-placeholder p {
            font-family: 'Space Grotesk', sans-serif;
            font-weight: 700;
            font-size: .88rem;
        }

        .map-placeholder small {
            font-size: .75rem;
            color: #555;
        }

        .social-row {
            display: flex;
            gap: .75rem;
            flex-wrap: wrap;
        }

        .social-btn {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            padding: .55rem 1.1rem;
            border-radius: 9px;
            font-size: .8rem;
            font-weight: 700;
            text-decoration: none;
            transition: opacity .2s;
        }

        .social-btn:hover {
            opacity: .8;
        }

        .social-btn svg {
            width: 15px;
            height: 15px;
            flex-shrink: 0;
        }

        .fb {
            background: rgba(11, 37, 69, 0.1);
            color: var(--primary);
        }

        .gh {
            background: #f0f2f5;
            color: #333;
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
            .contact-body {
                grid-template-columns: minmax(0, 1fr);
            }

            .nav-links {
                display: none;
            }

            .form-row2 {
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

        .error-msg-c {
            display: none;
            background: #FEF2F2;
            border: 1px solid #FECACA;
            color: #991B1B;
            font-size: .875rem;
            font-weight: 500;
            padding: .8rem 1rem;
            border-radius: 10px;
            margin-bottom: 1rem;
        }

        .submit-btn:disabled {
            opacity: .7;
            cursor: wait;
        }

        .form-card {
            position: relative;
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
                <strong>Arandia College </strong>
            </div>
        </a>
        <ul class="nav-links">
            <li><a href="index.php">Home</a></li>
            <li><a href="index.php#features">Features</a></li>
            <li><a href="FAQ.php">FAQ</a></li>
            <li><a href="helpcenter.php">Help Center</a></li>
            <li><a href="contact.php" class="active" aria-current="page">Contact Us</a></li>
            <li><a href="helpdesk.php">Campus Helpdesk</a></li>
            <li><a href="login.php" class="btn-login">Log In</a></li>
        </ul>
    </nav>

    <div class="page-hero">
        <div class="page-hero-eyebrow">Get in Touch</div>
        <h1>Contact Us</h1>
        <p>Have a question, concern, or feedback? We'd love to hear from you. Send us a message and we'll respond as
            soon as we can.</p>
    </div>

    <div class="contact-body">

        <!-- FORM -->
        <div class="form-card">
            <div class="section-eyebrow">Message Us</div>
            <h2 class="section-title">Send a Message</h2>
            <p class="section-sub">Fill out the form below. Our team will get back to you within 1–2 business days.</p>

            <div class="form-row2">
                <div class="form-group">
                    <label>First Name *</label>
                    <input type="text" placeholder="Juan" id="c-fname" maxlength="50" autocomplete="given-name" autocapitalize="words" spellcheck="false">
                </div>
                <div class="form-group">
                    <label>Last Name *</label>
                    <input type="text" placeholder="Dela Cruz" id="c-lname" maxlength="50" autocomplete="family-name" autocapitalize="words" spellcheck="false">
                </div>
            </div>
            <div class="form-group">
                <label>Email Address *</label>
                <input type="email" placeholder="your@email.com" id="c-email" maxlength="190" autocomplete="email" inputmode="email" autocapitalize="none" spellcheck="false">
            </div>
            <div class="form-group">
                <label>Role</label>
                <select id="c-role">
                    <option value="">Select your role</option>
                    <option>Student</option>
                    <option>Teacher / Faculty</option>
                    <option>Parent / Guardian</option>
                    <option>Staff / Administrator</option>
                    <option>Other</option>
                </select>
            </div>
            <div class="form-group">
                <label>Subject *</label>
                <input type="text" placeholder="e.g. Problem with quiz submission" id="c-subject">
            </div>
            <div class="form-group">
                <label>Message *</label>
                <textarea placeholder="Write your message here..." id="c-message"></textarea>
            </div>
            <!-- Spam trap: hidden from people, bots tend to fill it in -->
            <div style="position:absolute;left:-9999px;width:1px;height:1px;overflow:hidden;" aria-hidden="true">
                <label>Website <input type="text" id="c-website" tabindex="-1" autocomplete="off"></label>
            </div>
            <div class="error-msg-c" id="c-error" role="alert"></div>
            <button class="submit-btn" id="c-submit" type="button" onclick="submitContact()"><svg viewBox="0 0 24 24"
                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="22" y1="2" x2="11" y2="13"></line>
                    <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                </svg>Send Message</button>
            <div class="success-msg" id="c-success">Your message has been sent! We will get back to you at the email
                address you provided within 1–2 business days. Thank you for reaching out.</div>
        </div>

        <!-- INFO -->
        <div class="info-side">

            <div class="info-card">
                <div class="ic-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round">
                        <rect x="2" y="4" width="20" height="16" rx="2"></rect>
                        <path d="M22 6l-10 7L2 6"></path>
                    </svg></div>
                <div>
                    <div class="ic-label">Email</div>
                    <div class="ic-val">info@arandiacollege.edu.ph</div>
                    <p class="ic-note">For general inquiries. Replies within 1–2 business days.</p>
                </div>
            </div>

            <div class="info-card">
                <div class="ic-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path
                            d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.362 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.338 1.85.573 2.81.7A2 2 0 0 1 22 16.92z">
                        </path>
                    </svg></div>
                <div>
                    <div class="ic-label">Phone</div>
                    <div class="ic-val">(02) 8123-4567</div>
                    <p class="ic-note">Mon–Fri, 8:00 AM – 5:00 PM. Saturday until noon.</p>
                </div>
            </div>

            <div class="info-card">
                <div class="ic-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                        <circle cx="12" cy="10" r="3"></circle>
                    </svg></div>
                <div>
                    <div class="ic-label">Address</div>
                    <div class="ic-val">Arandia College</div>
                    <p class="ic-note">123 Arandia Street, Taguig City, Metro Manila, Philippines 1100</p>
                </div>
            </div>

            <div class="map-placeholder">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                    stroke-linejoin="round">
                    <polygon points="1 6 1 22 8 18 16 22 23 18 23 2 16 6 8 2 1 6"></polygon>
                    <line x1="8" y1="2" x2="8" y2="18"></line>
                    <line x1="16" y1="6" x2="16" y2="22"></line>
                </svg>
                <p>Arandia College — Taguig City</p>
                <small>123 Arandia Street, Metro Manila</small>
            </div>

            <div>
                <p
                    style="font-size:.78rem;font-weight:700;color:#888;text-transform:uppercase;letter-spacing:.08em;margin-bottom:.65rem;">
                    Follow Us</p>
                <div class="social-row">
                    <a href="https://www.facebook.com/arandiacollege.official" target="_blank" rel="noopener"
                        class="social-btn fb"><svg viewBox="0 0 24 24" fill="currentColor">
                            <path
                                d="M22 12a10 10 0 1 0-11.56 9.87v-6.98H7.9V12h2.54V9.8c0-2.5 1.5-3.89 3.78-3.89 1.1 0 2.24.2 2.24.2v2.46h-1.26c-1.24 0-1.63.78-1.63 1.57V12h2.78l-.44 2.89h-2.34v6.98A10 10 0 0 0 22 12z">
                            </path>
                        </svg>Facebook Page</a>
                    <a href="helpdesk.php" class="social-btn gh"><svg viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="2" y="4" width="20" height="14" rx="2"></rect>
                            <line x1="8" y1="21" x2="16" y2="21"></line>
                            <line x1="12" y1="18" x2="12" y2="21"></line>
                        </svg>Helpdesk</a>
                </div>
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
        // ---- Name fields: letters only, every word starts with a capital letter ----
        const NAME_BAD = /[^\p{L}\p{M} '’.\-]/gu;
        const NAME_OK = /^\p{L}[\p{L}\p{M} '.\-]*$/u;
        function cleanName(v, final) {
            v = String(v).replace(/’/g, "'").replace(NAME_BAD, '').replace(/^[ '.\-]+/, '').replace(/ {2,}/g, ' ');
            v = v.toLowerCase().replace(/(^|[ '\-])(\p{L})/gu, (m, a, b) => a + b.toUpperCase());
            return final ? v.trim() : v;
        }
        function bindNameField(id) {
            const el = document.getElementById(id);
            if (!el) return;
            el.addEventListener('input', e => {
                if (e.isComposing) return;
                const pos = el.selectionStart ?? el.value.length;
                const cleaned = cleanName(el.value, false);
                if (cleaned !== el.value) {
                    const newPos = cleanName(el.value.slice(0, pos), false).length;
                    el.value = cleaned;
                    try { el.setSelectionRange(newPos, newPos); } catch (_) { }
                }
            });
            el.addEventListener('blur', () => { el.value = cleanName(el.value, true); });
        }
        bindNameField('c-fname');
        bindNameField('c-lname');

        function nameError(v, label) {
            if (!v) return label + ' is required.';
            if (!NAME_OK.test(v)) return label + ' can only contain letters (no numbers or symbols).';
            if ((v.match(/\p{L}/gu) || []).length < 2) return label + ' must have at least 2 letters.';
            return '';
        }

        async function submitContact() {
            const val = id => document.getElementById(id).value.trim();
            const errBox = document.getElementById('c-error');
            const okBox = document.getElementById('c-success');
            const btn = document.getElementById('c-submit');
            errBox.style.display = 'none';
            okBox.style.display = 'none';

            const payload = {
                first_name: val('c-fname'), last_name: val('c-lname'), email: val('c-email'),
                role: val('c-role'), subject: val('c-subject'), message: val('c-message'),
                website: val('c-website')
            };
            const showErr = msg => { errBox.textContent = msg; errBox.style.display = 'block'; errBox.scrollIntoView({ behavior: 'smooth', block: 'center' }); };

            // normalise names ("juan  DELA cruz" -> "Juan Dela Cruz") and e-mail (lower-case)
            payload.first_name = cleanName(payload.first_name, true);
            payload.last_name = cleanName(payload.last_name, true);
            payload.email = payload.email.toLowerCase();
            document.getElementById('c-fname').value = payload.first_name;
            document.getElementById('c-lname').value = payload.last_name;
            document.getElementById('c-email').value = payload.email;

            if (!payload.first_name || !payload.last_name || !payload.email || !payload.subject || !payload.message) {
                showErr('Please fill in all required fields.'); return;
            }
            const nErr = nameError(payload.first_name, 'First name') || nameError(payload.last_name, 'Last name');
            if (nErr) { showErr(nErr); return; }
            if (!/^[^\s@]+@([a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z]{2,24}$/.test(payload.email)) { showErr('Please enter a valid email address.'); return; }

            const label = btn.innerHTML;
            btn.disabled = true;
            btn.textContent = 'Sending...';
            try {
                const res = await fetch('api/contact.php', {
                    method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload)
                });
                const data = await res.json();
                if (data.success) {
                    ['c-fname', 'c-lname', 'c-email', 'c-subject', 'c-message'].forEach(id => document.getElementById(id).value = '');
                    document.getElementById('c-role').value = '';
                    okBox.textContent = data.message;
                    okBox.style.display = 'block';
                    okBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
                } else {
                    showErr(data.message || 'Could not send your message. Please try again.');
                }
            } catch (e) {
                showErr('Network error. Please check your connection and try again.');
            }
            btn.disabled = false;
            btn.innerHTML = label;
        }
    </script>
    <script src="assets/js/elms-public-nav.js?v=3" defer></script>
</body>

</html>