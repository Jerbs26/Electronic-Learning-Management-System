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

// ── Banner carousel — same slides the admin manages in admin/banners.php ──
// Uses the dashboard_banners table (Active only, admin's order), the same
// source as the student dashboard. The landing page is public, so a database
// problem must never break it: on any failure the carousel just hides.
$heroBanners = [];
// System name from Admin → Settings (includes/settings.php); the default if the database is unreachable
require_once __DIR__ . '/includes/settings.php';
$__sysName = elmsSettingsDefaults()['system_name'];
try {
    mysqli_report(MYSQLI_REPORT_OFF);
    $__db = @new mysqli('localhost', 'u961483968_elmsdb', 'u961483968_Kelmsdb', 'u961483968_elmsdb'); // same credentials as config/conn.php
    if (!$__db->connect_error) {
        $__db->set_charset('utf8mb4');
        $__res = $__db->query(
            "SELECT id, image_path, title, caption, link_url
             FROM dashboard_banners
             WHERE status = 'Active'
             ORDER BY sort_order ASC, id ASC"
        );
        if ($__res) {
            while ($__r = $__res->fetch_assoc()) {
                if (!empty($__r['image_path']) && is_file(__DIR__ . '/' . $__r['image_path'])) {
                    $heroBanners[] = $__r;
                }
            }
        }
        $__sysName = elmsSettings($__db)['system_name'];
        $__db->close();
    }
} catch (Throwable $e) {
    $heroBanners = [];
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <!-- viewport-fit=cover lets the page use the full iPhone screen; safe-area padding below keeps content off the notch / home bar -->
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <meta name="theme-color" content="#061A33">
  <meta name="color-scheme" content="light">
  <meta name="apple-mobile-web-app-title" content="Arandia eLMS">
  <meta name="format-detection" content="telephone=no">
  <title><?= htmlspecialchars($__sysName) ?> — Electronic Learning Management System</title>
  <?php if ($__logoSrc): ?>
    <link rel="icon" href="<?= htmlspecialchars($__logoSrc) ?>">
    <link rel="apple-touch-icon" href="<?= htmlspecialchars($__logoSrc) ?>">
  <?php endif; ?>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
    /* ============================================================
       Palette — low-glare, muted. Off-white instead of pure white,
       softened navy instead of saturated blue, gold used sparingly.
       ============================================================ */
    :root {
      --bg: #F4F6FA;
      --surface: #FFFFFF;
      --surface-2: #F9FAFC;
      --ink: #142433;
      --muted: #5B6B7C;
      --line: #E0E4EE;
      --navy: #0B2545;
      --navy-deep: #061A33;
      --navy-tint: #E8EDF5;
      --gold: #B7861A;
      --gold-tint: #FFF7E0;
      --on-navy: #F3F5F8;
      --on-navy-muted: #B7C2D3;
      --radius: 16px;
      --shadow: 0 1px 2px rgba(20, 30, 45, .04), 0 4px 16px rgba(20, 30, 45, .05);
      --nav-h: 64px;
      --safe-l: env(safe-area-inset-left, 0px);
      --safe-r: env(safe-area-inset-right, 0px);
      --safe-b: env(safe-area-inset-bottom, 0px);
      --gutter: max(20px, calc(16px + var(--safe-l)));
      --gutter-r: max(20px, calc(16px + var(--safe-r)));
    }

    

    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    html {
      -webkit-text-size-adjust: 100%;
      text-size-adjust: 100%;
      scroll-behavior: smooth;
      scroll-padding-top: calc(var(--nav-h) + 12px);
    }

    @media (prefers-reduced-motion: reduce) {
      html { scroll-behavior: auto; }
      *, *::before, *::after { transition: none !important; animation: none !important; }
    }

    body {
      font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
      font-size: 16px; /* 16px+ keeps iOS from zooming and is easier to read */
      line-height: 1.65;
      color: var(--ink);
      background: var(--bg);
      overflow-x: clip;
      -webkit-font-smoothing: antialiased;
      -webkit-tap-highlight-color: transparent;
    }

    body.menu-open { overflow: hidden; }

    img { max-width: 100%; display: block; }
    a { color: inherit; }

    h1, h2, h3, .brand-name {
      font-family: 'Space Grotesk', 'Inter', sans-serif;
      letter-spacing: -0.015em;
      line-height: 1.2;
    }

    .wrap {
      width: 100%;
      max-width: 1280px;
      margin: 0 auto;
      padding-left: var(--gutter);
      padding-right: var(--gutter-r);
    }

    :focus-visible { outline: 2px solid var(--navy); outline-offset: 3px; border-radius: 6px; }

    /* ─── BUTTONS ─── */
    .btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: .5rem;
      min-height: 48px;
      padding: 0 1.5rem;
      border-radius: 12px;
      font: 700 1rem/1 'Space Grotesk', sans-serif;
      text-decoration: none;
      border: 1px solid transparent;
      cursor: pointer;
      transition: background .2s, border-color .2s, color .2s;
      touch-action: manipulation;
    }

    .btn svg { width: 18px; height: 18px; flex-shrink: 0; }

    .btn-primary { background: var(--navy); color: #fff; box-shadow: 0 4px 14px rgba(11, 37, 69, .25); }
    

    .btn-outline { background: var(--surface); color: var(--ink); border-color: var(--line); }

    @media (hover: hover) {
      .btn-primary:hover { background: #123A6B; }
      .btn-outline:hover { border-color: var(--navy); color: var(--navy); }
    }
    

    /* ─── TOPBAR ─── */
    .topbar {
      background: var(--surface-2);
      border-bottom: 1px solid var(--line);
      font-size: .8125rem;
    }

    .topbar .wrap {
      display: flex;
      justify-content: flex-end;
      gap: 1.25rem;
      padding-top: .45rem;
      padding-bottom: .45rem;
    }

    .topbar a { color: var(--muted); text-decoration: none; }
    @media (hover: hover) { .topbar a:hover { color: var(--navy); } }

    /* ─── NAV ─── */
    .site-nav {
      position: sticky;
      top: 0;
      z-index: 1000;
      background: color-mix(in srgb, var(--bg) 88%, transparent);
      -webkit-backdrop-filter: saturate(160%) blur(14px);
      backdrop-filter: saturate(160%) blur(14px);
      border-bottom: 1px solid var(--line);
    }

    @supports not (background: color-mix(in srgb, red 50%, blue)) {
      .site-nav { background: var(--bg); }
    }

    .site-nav .wrap {
      height: var(--nav-h);
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 1rem;
    }

    /* Desktop: logo and Sign In sit nearer the left/right edges (not full-bleed); top bar lines up with them */
    @media (min-width: 861px) {
      .topbar .wrap,
      .site-nav .wrap {
        max-width: none;
        padding-left: calc(clamp(24px, 3vw, 48px) + var(--safe-l));
        padding-right: calc(clamp(24px, 3vw, 48px) + var(--safe-r));
      }
    }

    .brand {
      display: flex;
      align-items: center;
      gap: .7rem;
      text-decoration: none;
      min-width: 0;
    }

    .brand-logo {
      width: 40px;
      height: 40px;
      object-fit: contain;
      flex-shrink: 0;
    }

    .brand-fallback {
      display: flex;
      align-items: center;
      justify-content: center;
      background: var(--navy);
      color: #fff;
      font-weight: 700;
      border-radius: 10px;
      font-size: .9rem;
    }

    .brand-text { line-height: 1.15; min-width: 0; }
    .brand-name { display: block; font-size: 1.02rem; font-weight: 700; color: var(--ink); white-space: nowrap; }
    .brand-sub { display: block; font-size: .75rem; color: var(--muted); white-space: nowrap; }

    .nav-links {
      display: flex;
      align-items: center;
      list-style: none;
      gap: .25rem;
    }

    .nav-links a:not(.btn) {
      display: inline-flex;
      align-items: center;
      min-height: 40px;
      padding: 0 .9rem;
      border-radius: 10px;
      font-size: .925rem;
      font-weight: 500;
      color: var(--muted);
      text-decoration: none;
      transition: color .2s, background .2s;
    }

    @media (hover: hover) {
      .nav-links a:not(.btn):hover { color: var(--ink); background: var(--navy-tint); }
    }

    .nav-links .btn { min-height: 42px; padding: 0 1.25rem; font-size: .925rem; margin-left: .5rem; }

    /* Hamburger */
    .menu-toggle {
      display: none;
      width: 44px;
      height: 44px;
      border-radius: 12px;
      border: 1px solid var(--line);
      background: var(--surface);
      color: var(--ink);
      align-items: center;
      justify-content: center;
      cursor: pointer;
      flex-shrink: 0;
      touch-action: manipulation;
    }

    .menu-toggle svg { width: 22px; height: 22px; }
    .menu-toggle .ic-close { display: none; }

    /* Mobile menu: slide-in sidebar (same look as the Admin/Teacher/Student drawer) */
    .menu-backdrop {
      position: fixed; inset: 0; z-index: 1100;   /* above the sticky top bar */
      background: rgba(15, 23, 42, .52);
      opacity: 0; visibility: hidden;
      transition: opacity .26s ease, visibility 0s linear .26s;
      -webkit-tap-highlight-color: transparent;
    }
    .menu-backdrop.open { opacity: 1; visibility: visible; transition: opacity .26s ease; }

    .mobile-menu {
      position: fixed;
      top: 0; bottom: 0; left: 0;
      z-index: 1101;
      width: min(300px, calc(100vw - 56px));
      height: 100vh; height: 100dvh;
      background: #101d34;
      color: #fff;
      display: flex;
      flex-direction: column;
      padding: 0 0 calc(1rem + var(--safe-b));
      overflow-y: auto;
      overscroll-behavior: contain;
      -webkit-overflow-scrolling: touch;
      box-shadow: 8px 0 32px rgba(0, 0, 0, .28);
      transform: translateX(-100%);
      visibility: hidden;
      transition: transform .26s cubic-bezier(.4, 0, .2, 1), visibility 0s linear .26s;
    }
    .mobile-menu.open {
      transform: none;
      visibility: visible;
      transition: transform .26s cubic-bezier(.4, 0, .2, 1), visibility 0s;
    }
    /* Opens from the same side as the menu button (set by the script below) */
    .mobile-menu.from-right {
      left: auto; right: 0;
      transform: translateX(100%);
      box-shadow: -8px 0 32px rgba(0, 0, 0, .28);
      padding-right: var(--safe-r, 0px);
    }
    .mobile-menu.from-right.open { transform: none; }
    @media (prefers-reduced-motion: reduce) { .mobile-menu, .menu-backdrop { transition: none; } }

    .mm-head {
      display: flex; align-items: center; gap: .75rem;
      min-height: 76px; padding: 1rem 1rem 1rem 1.25rem;
      border-bottom: 1px solid rgba(255, 255, 255, .08);
    }
    .mm-head img, .mm-head .brand-fallback { width: 44px; height: 44px; flex-shrink: 0; object-fit: contain; }
    .mm-head strong { flex: 1; font-size: 1rem; font-weight: 800; color: #fff; line-height: 1.25; }
    .mm-close {
      width: 40px; height: 40px; flex-shrink: 0;
      display: inline-flex; align-items: center; justify-content: center;
      border: 1px solid rgba(255, 255, 255, .16); border-radius: 10px;
      background: transparent; color: #fff; cursor: pointer;
      -webkit-tap-highlight-color: transparent;
    }
    .mm-close svg { width: 20px; height: 20px; }
    .mm-close:focus-visible, .mobile-menu a:focus-visible { outline: 2px solid #8fb0ff; outline-offset: 2px; }

    .mm-nav { display: flex; flex-direction: column; gap: .25rem; padding: 1rem .75rem 0; }
    .mobile-menu a:not(.btn) {
      display: flex;
      align-items: center;
      min-height: 44px;
      padding: .6rem .85rem;
      border-radius: 10px;
      font-size: .95rem;
      font-weight: 600;
      color: #93a0bb;
      text-decoration: none;
      transition: background .15s, color .15s;
    }
    .mobile-menu a:not(.btn):hover,
    .mobile-menu a:not(.btn):active { background: rgba(255, 255, 255, .06); color: #fff; }
    .mobile-menu a.is-current { background: #fff; color: #003087; font-weight: 700; }

    .mobile-menu .menu-label {
      font-size: .7rem;
      font-weight: 800;
      text-transform: uppercase;
      letter-spacing: .1em;
      color: #5c6b85;
      margin: 1rem .85rem .25rem;
    }

    .mobile-menu .btn { width: calc(100% - 1.5rem); margin: 1.25rem .75rem 0; }

    /* ─── HERO ─── */
    .hero {
      padding: 4.5rem 0 4rem;
      border-bottom: 1px solid var(--line);
    }

    .hero-grid {
      display: grid;
      grid-template-columns: 1.1fr .9fr;
      gap: 3.5rem;
      align-items: center;
    }

    .eyebrow {
      display: inline-flex;
      align-items: center;
      gap: .5rem;
      font-size: .78rem;
      font-weight: 600;
      letter-spacing: .08em;
      text-transform: uppercase;
      color: var(--gold);
      margin-bottom: 1rem;
    }

    .eyebrow::before {
      content: '';
      width: 20px;
      height: 2px;
      background: currentColor;
      border-radius: 2px;
    }

    .hero h1 {
      font-size: clamp(2.1rem, 1.4rem + 3vw, 3.25rem);
      font-weight: 700;
      color: var(--ink);
      margin-bottom: 1.1rem;
      text-wrap: balance;
    }

    .hero h1 span { color: var(--navy); }

    .hero-sub {
      font-size: 1.075rem;
      color: var(--muted);
      max-width: 34rem;
      margin-bottom: 2rem;
    }

    .hero-btns { display: flex; flex-wrap: wrap; gap: .75rem; }

    /* Banner carousel (slides come from admin/banners.php) */
    .hero-grid { grid-template-columns: minmax(0, .8fr) minmax(0, 1.5fr); gap: 2.5rem; }
    .hero h1 { font-size: clamp(2.1rem, 1.3rem + 2.4vw, 3rem); }

    .hero-carousel {
      position: relative;
      width: 100%;
      border-radius: 18px;
      overflow: hidden;
      background: #061A33;
      box-shadow: 0 18px 40px rgba(6, 26, 51, .35);
      isolation: isolate;
      touch-action: pan-y;
      -webkit-user-select: none;
      user-select: none;
    }

    .hc-track { display: flex; transition: transform .5s cubic-bezier(.22, 1, .36, 1); will-change: transform; }
    .hc-track.dragging { transition: none; }
    .hc-slide { flex: 0 0 100%; position: relative; }
    .hc-inner { display: block; width: 100%; height: 100%; color: inherit; text-decoration: none; }
    .hc-media { aspect-ratio: 1600 / 700; background: #061A33; }
    .hc-media img { width: 100%; height: 100%; object-fit: cover; -webkit-user-drag: none; }

    .hc-caption {
      position: absolute;
      left: 0; right: 0; bottom: 0;
      padding: 2.5rem 1.25rem 1.75rem;
      background: linear-gradient(to top, rgba(6, 26, 51, .85), rgba(6, 26, 51, 0));
      color: #fff;
      display: grid;
      gap: .15rem;
    }

    .hc-caption strong { font-family: 'Space Grotesk', sans-serif; font-size: 1.3rem; font-weight: 700; line-height: 1.25; }
    .hc-caption span { font-size: 1rem; color: rgba(255, 255, 255, .85); }

    .hc-arrow {
      position: absolute;
      top: 50%;
      transform: translateY(-50%);
      width: 40px; height: 40px;
      border-radius: 50%;
      border: 0;
      background: rgba(255, 255, 255, .9);
      color: #0B2545;
      font-size: 1.5rem;
      line-height: 1;
      padding-bottom: 3px;
      display: flex; align-items: center; justify-content: center;
      cursor: pointer;
      z-index: 2;
      box-shadow: 0 2px 8px rgba(0, 0, 0, .2);
      transition: background .2s;
      touch-action: manipulation;
    }
    .hc-arrow.left { left: .75rem; }
    .hc-arrow.right { right: .75rem; }
    @media (hover: hover) { .hc-arrow:hover { background: #fff; } }

    .hc-dots {
      position: absolute;
      left: 50%; bottom: .6rem;
      transform: translateX(-50%);
      display: flex; gap: .2rem;
      z-index: 2;
    }

    /* 24px tap area, small visible dot */
    .hc-dot {
      width: 24px; height: 24px;
      border: 0; background: none; padding: 0;
      cursor: pointer;
      display: flex; align-items: center; justify-content: center;
    }
    .hc-dot::before {
      content: '';
      width: 7px; height: 7px;
      border-radius: 999px;
      background: rgba(255, 255, 255, .55);
      transition: width .25s, background .25s;
    }
    .hc-dot.active::before { width: 20px; background: #FFC53D; }

    .hc-empty {
      aspect-ratio: 1600 / 700;
      display: flex; flex-direction: column; align-items: center; justify-content: center;
      gap: .25rem; text-align: center; color: #fff; padding: 1.5rem;
      background: linear-gradient(160deg, #123A6B, #061A33);
      border: 1px solid rgba(255, 255, 255, .12);
    }
    .hc-empty-logo { width: 72px; height: 72px; object-fit: contain; margin-bottom: .5rem; }
    .hc-empty strong { font-family: 'Space Grotesk', sans-serif; font-size: 1.2rem; }
    .hc-empty span { font-size: .85rem; color: rgba(255, 255, 255, .7); }

    /* ─── SECTIONS ─── */
    section.block { padding: 5rem 0; }
    section.block.alt { background: var(--surface-2); border-top: 1px solid var(--line); border-bottom: 1px solid var(--line); }

    .sec-head { max-width: 40rem; margin-bottom: 2.75rem; }
    .sec-head.center { margin-left: auto; margin-right: auto; text-align: center; }
    .sec-head.center .eyebrow::after { content: ''; width: 20px; height: 2px; background: currentColor; border-radius: 2px; }

    .sec-head h2 {
      font-size: clamp(1.6rem, 1.2rem + 1.6vw, 2.2rem);
      font-weight: 700;
      margin-bottom: .75rem;
      text-wrap: balance;
    }

    .sec-head p { color: var(--muted); font-size: 1.025rem; }

    /* Mission & Vision */
    .mv-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 1.25rem; }

    .mv-card {
      background: var(--surface);
      border: 1px solid var(--line);
      border-radius: var(--radius);
      padding: 2rem;
      position: relative;
    }

    .mv-card::before {
      content: '';
      position: absolute;
      left: 2rem;
      top: 0;
      width: 40px;
      height: 3px;
      border-radius: 0 0 3px 3px;
      background: var(--navy);
    }


    .mv-label {
      font-size: .75rem;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: .08em;
      color: var(--muted);
      margin-bottom: .5rem;
    }

    .mv-card h3 { font-size: 1.3rem; font-weight: 700; margin-bottom: .75rem; }
    .mv-card p { color: var(--muted); }

    /* Features */
    .features {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
      gap: 1rem;
    }

    .feature {
      background: var(--surface);
      border: 1px solid var(--line);
      border-radius: var(--radius);
      padding: 1.5rem;
      transition: border-color .2s;
    }

    @media (hover: hover) { .feature:hover { border-color: color-mix(in srgb, var(--navy) 35%, var(--line)); } }

    .f-ic {
      width: 44px;
      height: 44px;
      border-radius: 12px;
      background: var(--navy-tint);
      color: var(--navy);
      display: flex;
      align-items: center;
      justify-content: center;
      margin-bottom: 1rem;
    }

    .f-ic svg { width: 22px; height: 22px; }

    .feature h3 { font-size: 1.05rem; font-weight: 700; margin-bottom: .35rem; }
    .feature p { font-size: .95rem; color: var(--muted); line-height: 1.6; }

    /* Steps */
    .steps {
      list-style: none;
      display: grid;
      grid-template-columns: repeat(4, minmax(0, 1fr));
      gap: 1.25rem;
      counter-reset: step;
    }

    .step {
      position: relative;
      padding-top: 3.5rem;
    }

    .step::before {
      counter-increment: step;
      content: counter(step);
      position: absolute;
      top: 0;
      left: 0;
      width: 40px;
      height: 40px;
      border-radius: 50%;
      background: var(--surface);
      border: 1.5px solid var(--navy);
      color: var(--navy);
      font: 700 1rem/37px 'Space Grotesk', sans-serif;
      text-align: center;
      z-index: 1;
    }

    .step::after {
      content: '';
      position: absolute;
      top: 20px;
      left: 52px;
      right: -8px;
      height: 1px;
      background: var(--line);
    }

    .step:last-child::after { display: none; }

    .step h3 { font-size: 1.05rem; font-weight: 700; margin-bottom: .35rem; }
    .step p { font-size: .95rem; color: var(--muted); }

    /* CTA */
    .cta {
      background: var(--navy-deep);
      color: var(--on-navy);
      border-radius: 20px;
      padding: 2.5rem;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 1.5rem;
      flex-wrap: wrap;
    }

    .cta h2 { font-size: 1.5rem; font-weight: 700; margin-bottom: .35rem; }
    .cta p { color: var(--on-navy-muted); }
    .cta .btn { background: var(--on-navy); color: #182D50; }
    @media (hover: hover) { .cta .btn:hover { background: #fff; } }
    

    /* ─── FOOTER ─── */
    footer {
      border-top: 1px solid var(--line);
      background: var(--surface-2);
      padding: 3.5rem 0 calc(1.5rem + var(--safe-b));
      font-size: .925rem;
      color: var(--muted);
    }

    .footer-top {
      display: grid;
      grid-template-columns: 2fr 1fr 1fr 1fr;
      gap: 2.5rem;
      padding-bottom: 2.5rem;
      border-bottom: 1px solid var(--line);
    }

    .footer-brand { display: flex; gap: 1rem; align-items: flex-start; }
    .footer-brand .brand-logo { width: 48px; height: 48px; }
    .footer-brand strong { display: block; color: var(--ink); font-family: 'Space Grotesk', sans-serif; font-size: 1.05rem; }
    .footer-brand em { display: block; font-style: normal; font-size: .75rem; letter-spacing: .06em; text-transform: uppercase; color: var(--gold); font-weight: 600; margin: .1rem 0 .6rem; }
    .footer-brand p { max-width: 22rem; line-height: 1.6; }

    .footer-col h4 { font-size: .85rem; font-weight: 600; color: var(--ink); margin-bottom: .9rem; }
    .footer-col ul { list-style: none; display: grid; gap: .55rem; }
    .footer-col a { text-decoration: none; }
    @media (hover: hover) { .footer-col a:hover { color: var(--navy); } }

    .footer-bottom { padding-top: 1.5rem; text-align: center; font-size: .85rem; }

    /* ─── RESPONSIVE ─── */
    @media (max-width: 1024px) {
      .hero-grid { grid-template-columns: 1fr; gap: 2.5rem; }
      .steps { grid-template-columns: repeat(2, minmax(0, 1fr)); row-gap: 2rem; }
      .step::after { display: none; }
      .footer-top { grid-template-columns: repeat(3, minmax(0, 1fr)); }
      .footer-brand { grid-column: 1 / -1; }
    }

    @media (max-width: 860px) {
      .nav-links, .topbar { display: none; }
      .menu-toggle { display: inline-flex; }
    }

    @media (max-width: 640px) {
      :root { --nav-h: 60px; }

      .brand-logo { width: 36px; height: 36px; }
      .brand-sub { display: none; }

      .hero { padding: 2.5rem 0 2.75rem; }
      .hero-sub { font-size: 1rem; margin-bottom: 1.5rem; }
      .hero-btns { flex-direction: column; }
      .hero-btns .btn { width: 100%; }
      .hero-carousel { border-radius: 14px; }
      /* Phone: caption sits under the picture so it never covers the poster; swipe replaces arrows */
      .hero-carousel { background: #0B2545; }
      .hc-inner { display: flex; flex-direction: column; }
      .hero-carousel { margin-inline: -10px; width: auto; }
      /* Taller on phones: crops only the blurred sides so the poster shows bigger */
      .hc-media { aspect-ratio: 4 / 3; }
      .hc-empty { aspect-ratio: 4 / 3; }
      .hc-caption { position: static; flex: 1; align-content: start; background: #0B2545; padding: .8rem 1rem 2.1rem; }
      .hc-caption strong { font-size: .98rem; }
      .hc-caption span { font-size: .82rem; }
      .hc-arrow { display: none; }
      .hc-dots { bottom: .35rem; }


      section.block { padding: 3.25rem 0; }
      .sec-head { margin-bottom: 1.75rem; }

      .mv-grid { grid-template-columns: 1fr; }
      .mv-card { padding: 1.5rem; }
      .mv-card::before { left: 1.5rem; }

      /* Features become a readable list: icon left, text right */
      .features { grid-template-columns: 1fr; gap: .75rem; }
      .feature { display: flex; gap: 1rem; padding: 1.1rem; align-items: flex-start; }
      .f-ic { margin-bottom: 0; flex-shrink: 0; width: 42px; height: 42px; }

      /* Steps become a vertical timeline */
      .steps { grid-template-columns: 1fr; gap: 0; }
      .step { padding: 0 0 1.75rem 3.5rem; }
      .step:last-child { padding-bottom: 0; }
      .step::after { display: block; top: 44px; bottom: 4px; left: 20px; right: auto; width: 1px; height: auto; }
      .step:last-child::after { display: none; }

      .cta { padding: 1.75rem 1.5rem; border-radius: 16px; }
      .cta .btn { width: 100%; }

      .footer-top { grid-template-columns: 1fr 1fr; gap: 2rem 1.25rem; }
      .footer-col:last-child { grid-column: 1 / -1; }
    }

    /* ============================================================
       COLOR PASS — navy hero, gold accents, tinted icons, navy footer
       ============================================================ */
    :root {
      --hero-a: #0B2545;
      --hero-b: #123A6B;
      --gold-soft: #FFC53D;
    }

    .topbar { background: #061A33; border-bottom: 0; }
    .topbar a { color: #C3CDDD; }
    @media (hover: hover) { .topbar a:hover { color: var(--gold-soft); } }

    .site-nav { background: color-mix(in srgb, var(--surface) 92%, transparent); }

    .hero {
      background:
        radial-gradient(900px 420px at 85% -10%, rgba(255, 197, 61, .12), transparent 60%),
        radial-gradient(700px 500px at -10% 110%, rgba(14, 124, 123, .35), transparent 60%),
        linear-gradient(160deg, var(--hero-a), var(--hero-b));
      border-bottom: 0;
      color: #EEF2F8;
    }
    .hero .eyebrow { color: var(--gold-soft); }
    .hero h1 { color: #FFFFFF; }
    .hero h1 span { color: var(--gold-soft); }
    .hero-sub { color: #C3CDDD; }
    .hero .btn-primary { background: var(--gold-soft); color: #0B2545; }
    @media (hover: hover) { .hero .btn-primary:hover { background: #FFD269; } }
    .mobile-menu .btn-primary { background: var(--gold-soft); color: #0B2545; border-color: transparent; box-shadow: none; }
    @media (hover: hover) { .mobile-menu .btn-primary:hover { background: #FFD269; } }
    .hero .btn-outline { background: rgba(255,255,255,.08); color: #fff; border-color: rgba(255,255,255,.28); }
    @media (hover: hover) { .hero .btn-outline:hover { background: rgba(255,255,255,.14); color: #fff; border-color: rgba(255,255,255,.45); } }



    section.block.alt { background: #FFFFFF; border-color: #E0E4EE; }

    .mv-card { overflow: hidden; }
    .mv-card::before { left: 0; right: 0; width: auto; height: 4px; border-radius: 0; background: linear-gradient(90deg, #0B2545, #123A6B); }
    .mv-card .mv-label { display: inline-block; padding: .25rem .7rem; border-radius: 999px; background: #E8EDF5; color: #0B2545; }

    /* Icon colors from the login palette: navy / teal / gold */
    .feature:nth-child(3n+1) .f-ic { background: #E8EDF5; color: #0B2545; }
    .feature:nth-child(3n+2) .f-ic { background: #E3F3F2; color: #0E7C7B; }
    .feature:nth-child(3n) .f-ic { background: #FFF7E0; color: #B7861A; }

    .step::before { background: var(--navy); color: #fff; border: 3px solid var(--gold-soft); line-height: 34px; }

    .cta { background: linear-gradient(135deg, var(--hero-a), var(--hero-b)); }
    .cta .btn { background: var(--gold-soft); color: #0B2545; }
    @media (hover: hover) { .cta .btn:hover { background: #FFD269; } }

    footer { background: #0f172a; border-top: 0; color: #A9B5C8; }
    .footer-brand strong, .footer-col h4 { color: #FFFFFF; }
    .footer-brand em { color: var(--gold-soft); }
    .footer-brand .brand-logo { background: #fff; border-radius: 12px; padding: 4px; }
    .footer-top { border-color: rgba(255,255,255,.1); }
    @media (hover: hover) { .footer-col a:hover { color: var(--gold-soft); } }

    

    /* ── Thin scrollbar (visible but slim) ── */
    html {
      scrollbar-width: thin;                              /* Firefox, Chrome 121+, Edge */
      scrollbar-color: rgba(11, 37, 69, .45) transparent; /* thumb, track */
    }
    ::-webkit-scrollbar { width: 6px; height: 6px; }       /* Safari, older Chrome */
    ::-webkit-scrollbar-track { background: transparent; }
    ::-webkit-scrollbar-thumb { background: rgba(11, 37, 69, .45); border-radius: 999px; }
    ::-webkit-scrollbar-thumb:hover { background: rgba(11, 37, 69, .7); }
    /* ── Big blurred logo behind Mission & Vision ── */
    .mv-section { position: relative; overflow: hidden; isolation: isolate; }
    .mv-section > .wrap { position: relative; z-index: 1; }
    .mv-bg-logo {
      position: absolute;
      top: 50%;
      left: 50%;
      width: min(640px, 90vw);
      height: auto;
      transform: translate(-50%, -50%);
      opacity: .14;
      filter: blur(4px);
      pointer-events: none;
      user-select: none;
      z-index: 0;
    }
    /* Cards slightly see-through so the logo shows behind them too */
    .mv-section .mv-card { background: rgba(255, 255, 255, .82); -webkit-backdrop-filter: blur(2px); backdrop-filter: blur(2px); }
    @media (max-width: 640px) {
      .mv-bg-logo { width: 120vw; opacity: .12; filter: blur(3px); }
    }
  </style>
  <?php include __DIR__ . '/includes/page_transition.php'; ?>
  <?php include __DIR__ . '/includes/pwa_head.php'; ?>
</head>

<body>
  <!-- TOPBAR (desktop) -->
  <div class="topbar">
    <div class="wrap">
      <a href="helpdesk.php">Campus Helpdesk</a>
      <a href="FAQ.php">FAQ</a>
      <a href="contact.php">Contact Us</a>
    </div>
  </div>

  <!-- NAV -->
  <nav class="site-nav" id="siteNav">
    <div class="wrap">
      <a href="#top" class="brand">
        <?php if ($__logoSrc): ?>
          <img src="<?= htmlspecialchars($__logoSrc) ?>" alt="Arandia College logo" class="brand-logo" width="40" height="40">
        <?php else: ?>
          <div class="brand-logo brand-fallback">AC</div>
        <?php endif; ?>
        <span class="brand-text">
          <span class="brand-name">Arandia College</span>
        </span>
      </a>

      <ul class="nav-links">
        <li><a href="#mv">Mission &amp; Vision</a></li>
        <li><a href="#features">Features</a></li>
        <li><a href="#how">How It Works</a></li>
        <li><a href="login.php" class="btn btn-primary">Sign In</a></li>
      </ul>

      <button class="menu-toggle" id="menuToggle" type="button" aria-label="Open menu" aria-expanded="false" aria-controls="mobileMenu">
        <svg class="ic-menu" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="4" y1="7" x2="20" y2="7"/><line x1="4" y1="12" x2="20" y2="12"/><line x1="4" y1="17" x2="20" y2="17"/></svg>
        <svg class="ic-close" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="6" y1="6" x2="18" y2="18"/><line x1="18" y1="6" x2="6" y2="18"/></svg>
      </button>
    </div>
  </nav>

  <!-- MOBILE MENU (slide-in sidebar) -->
  <div class="menu-backdrop" id="menuBackdrop" aria-hidden="true"></div>
  <aside class="mobile-menu" id="mobileMenu" aria-hidden="true" aria-label="Menu">
    <div class="mm-head">
      <?php if ($__logoSrc): ?>
        <img src="<?= htmlspecialchars($__logoSrc) ?>" alt="" width="44" height="44">
      <?php else: ?>
        <div class="brand-logo brand-fallback">AC</div>
      <?php endif; ?>
      <strong>Arandia College</strong>
      <button class="mm-close" id="menuClose" type="button" aria-label="Close menu">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><line x1="6" y1="6" x2="18" y2="18"/><line x1="18" y1="6" x2="6" y2="18"/></svg>
      </button>
    </div>
    <nav class="mm-nav">
      <a href="#top" class="is-current">Home</a>
      <a href="#mv">Mission &amp; Vision</a>
      <a href="#features">Features</a>
      <a href="#how">How It Works</a>
      <div class="menu-label">Support</div>
      <a href="helpdesk.php">Campus Helpdesk</a>
      <a href="FAQ.php">FAQ</a>
      <a href="contact.php">Contact Us</a>
    </nav>
    <a href="login.php" class="btn btn-primary">Sign In</a>
  </aside>

  <main id="top">
    <!-- HERO -->
    <section class="hero">
      <div class="wrap">
        <div class="hero-grid">
          <div>
            <h1>Your learning, <span>your future.</span></h1>
            <p class="hero-sub">Access lessons, submit assignments, and track your academic progress — all in one place, anytime and anywhere.</p>
            <div class="hero-btns">
              <a href="login.php" class="btn btn-primary">
                Sign In
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
              </a>
              <a href="#features" class="btn btn-outline">Explore Features</a>
            </div>
          </div>

          <?php if (!empty($heroBanners)): ?>
          <div class="hero-carousel" id="heroCarousel" aria-roledescription="carousel" aria-label="Announcements">
            <div class="hc-track" id="hcTrack">
              <?php foreach ($heroBanners as $i => $b):
                $tag = $b['link_url'] ? 'a' : 'div'; ?>
                <div class="hc-slide" role="group" aria-roledescription="slide" aria-label="<?= ($i + 1) . ' of ' . count($heroBanners) ?>">
                  <<?= $tag ?> class="hc-inner"<?php if ($b['link_url']): ?> href="<?= htmlspecialchars($b['link_url']) ?>" target="_blank" rel="noopener"<?php endif; ?>>
                    <div class="hc-media"><img src="<?= htmlspecialchars($b['image_path']) ?>" alt="<?= htmlspecialchars($b['title'] ?? 'Announcement') ?>"<?= $i > 0 ? ' loading="lazy"' : '' ?> draggable="false"></div>
                    <?php if ($b['title'] || $b['caption']): ?>
                      <div class="hc-caption">
                        <?php if ($b['title']): ?><strong><?= htmlspecialchars($b['title']) ?></strong><?php endif; ?>
                        <?php if ($b['caption']): ?><span><?= htmlspecialchars($b['caption']) ?></span><?php endif; ?>
                      </div>
                    <?php endif; ?>
                  </<?= $tag ?>>
                </div>
              <?php endforeach; ?>
            </div>
            <?php if (count($heroBanners) > 1): ?>
              <button class="hc-arrow left" type="button" data-dir="-1" aria-label="Previous slide">&#8249;</button>
              <button class="hc-arrow right" type="button" data-dir="1" aria-label="Next slide">&#8250;</button>
              <div class="hc-dots">
                <?php foreach ($heroBanners as $i => $b): ?>
                  <button type="button" class="hc-dot<?= $i === 0 ? ' active' : '' ?>" data-i="<?= $i ?>" aria-label="Go to slide <?= $i + 1 ?>"></button>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </div>
          <?php else: ?>
          <div class="hero-carousel hc-empty">
            <?php if ($__logoSrc): ?><img src="<?= htmlspecialchars($__logoSrc) ?>" alt="" class="hc-empty-logo"><?php endif; ?>
            <strong>Arandia College</strong>
            <span>Electronic Learning Management System</span>
          </div>
          <?php endif; ?>
        </div>
      </div>
    </section>

    <!-- MISSION & VISION -->
    <section class="block mv-section" id="mv">
      <?php if ($__logoSrc): ?>
        <img src="<?= htmlspecialchars($__logoSrc) ?>" alt="" class="mv-bg-logo" aria-hidden="true" loading="lazy">
      <?php endif; ?>
      <div class="wrap">
        <div class="sec-head">
          <h2>Mission &amp; Vision</h2>
          <p>The guiding principles and aspirations that define Arandia College and its commitment to excellence in education.</p>
        </div>
        <div class="mv-grid">
          <article class="mv-card mission">
            <div class="mv-label">Mission</div>
            <h3>Our Mission</h3>
            <p>Provide learners necessary skills to be critical and creative thinkers for global competitiveness.</p>
          </article>
          <article class="mv-card">
            <div class="mv-label">Vision</div>
            <h3>Our Vision</h3>
            <p>This institution is dedicated in pursuit of quality education and skills of learners for future endeavors in this ever-changing world.</p>
          </article>
          
        </div>
      </div>
    </section>

    <!-- FEATURES -->
    <section class="block alt" id="features">
      <div class="wrap">
        <div class="sec-head">
          <h2>Everything you need in one place</h2>
          <p><?= htmlspecialchars($__sysName) ?> brings together the tools students and teachers need to make digital learning effective and accessible.</p>
        </div>

        <div class="features">
          <div class="feature">
            <div class="f-ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg></div>
            <div><h3>Course Modules</h3><p>Access organized lessons and reading materials uploaded by your instructors anytime.</p></div>
          </div>
          <div class="feature">
            <div class="f-ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="8" y="2" width="8" height="4" rx="1"/><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><line x1="9" y1="12" x2="15" y2="12"/><line x1="9" y1="16" x2="15" y2="16"/></svg></div>
            <div><h3>Assignments</h3><p>Submit tasks and written work online. Track deadlines and never miss a requirement.</p></div>
          </div>
          <div class="feature">
            <div class="f-ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><polyline points="9 15 11 17 15 13"/></svg></div>
            <div><h3>Quizzes</h3><p>Take online quizzes with instant scoring and detailed performance feedback.</p></div>
          </div>
          <div class="feature">
            <div class="f-ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/><line x1="2" y1="20" x2="22" y2="20"/></svg></div>
            <div><h3>Progress Reports</h3><p>View your grades, completion rates, and academic performance at a glance.</p></div>
          </div>
          <div class="feature">
            <div class="f-ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="5" y="2" width="14" height="20" rx="2"/><line x1="12" y1="18" x2="12.01" y2="18" stroke-width="2.5"/></svg></div>
            <div><h3>Mobile Access</h3><p>Use the platform on your smartphone or tablet — learning on-the-go made easy.</p></div>
          </div>
          <div class="feature">
            <div class="f-ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg></div>
            <div><h3>Notifications</h3><p>Get alerts for upcoming deadlines, new modules, quiz results, and instructor announcements.</p></div>
          </div>
          <div class="feature">
            <div class="f-ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="6"/><path d="M15.477 12.89L17 22l-5-3-5 3 1.523-9.11"/></svg></div>
            <div><h3>Achievements</h3><p>Earn badges and track milestones as you complete courses and hit academic goals.</p></div>
          </div>
        </div>
      </div>
    </section>

    <!-- HOW IT WORKS -->
    <section class="block" id="how">
      <div class="wrap">
        <div class="sec-head">
          <h2>How it works</h2>
          <p>Start using <?= htmlspecialchars($__sysName) ?> in four simple steps and begin your digital learning journey today.</p>
        </div>

        <ol class="steps">
          <li class="step"><h3>Create your account</h3><p>Register using your school ID or email address provided by Arandia College.</p></li>
          <li class="step"><h3>Enroll in courses</h3><p>Browse available subjects and enroll in your assigned or elective courses.</p></li>
          <li class="step"><h3>Access lessons</h3><p>View uploaded modules and reading materials prepared by your teachers.</p></li>
          <li class="step"><h3>Complete &amp; succeed</h3><p>Submit assignments, take quizzes, and track your grades and progress over time.</p></li>
        </ol>

        <div class="cta" style="margin-top:3.5rem">
          <div>
            <h2>Ready to start learning?</h2>
            <p>Sign in with your Arandia College account to continue.</p>
          </div>
          <a href="login.php" class="btn">Sign In</a>
        </div>
      </div>
    </section>
  </main>

  <!-- FOOTER -->
  <footer>
    <div class="wrap">
      <div class="footer-top">
        <div class="footer-brand">
          <?php if ($__logoSrc): ?>
            <img src="<?= htmlspecialchars($__logoSrc) ?>" alt="Arandia College logo" class="brand-logo" width="48" height="48" loading="lazy">
          <?php else: ?>
            <div class="brand-logo brand-fallback">AC</div>
          <?php endif; ?>
          <div>
            <strong>Arandia College</strong>
            <em>Est. 1999</em>
            <p>Electronic Learning Management System — a digital platform supporting online and blended education for Arandia College students and teachers.</p>
          </div>
        </div>

        <div class="footer-col">
          <h4>For Students</h4>
          <ul>
            <li>View Courses</li>
            <li>My Assignments</li>
            <li>Take a Quiz</li>
            <li>My Grades</li>
          </ul>
        </div>

        <div class="footer-col">
          <h4>For Teachers</h4>
          <ul>
            <li>Upload Module</li>
            <li>Create Assessment</li>
            <li>Student Reports</li>
            <li>Class Management</li>
          </ul>
        </div>

        <div class="footer-col">
          <h4>Support</h4>
          <ul>
            <li><a href="helpcenter.php">Help Center</a></li>
            <li><a href="FAQ.php">FAQ</a></li>
            <li><a href="contact.php">Contact Us</a></li>
            <li><a href="privacy.php">Privacy Policy</a></li>
          </ul>
        </div>
      </div>
      <div class="footer-bottom">&copy; <?= date('Y') ?> Arandia College. All rights reserved.</div>
    </div>
  </footer>

  <script>
    (function () {
      var toggle = document.getElementById('menuToggle');
      var menu = document.getElementById('mobileMenu');
      var backdrop = document.getElementById('menuBackdrop');
      var closeBtn = document.getElementById('menuClose');

      // The menu slides in from the same side of the screen as the menu button
      function placeOnButtonSide() {
        var r = toggle.getBoundingClientRect();
        var right = (r.left + r.width / 2) > (window.innerWidth / 2);
        if (menu.classList.contains('from-right') === right) return;
        menu.style.transition = 'none';          // switch sides without a visible slide
        menu.classList.toggle('from-right', right);
        void menu.offsetWidth;
        menu.style.transition = '';
        void menu.offsetWidth;
      }

      function setOpen(open) {
        if (open) placeOnButtonSide();
        menu.classList.toggle('open', open);
        backdrop.classList.toggle('open', open);
        toggle.classList.toggle('open', open);
        document.body.classList.toggle('menu-open', open);
        toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        toggle.setAttribute('aria-label', open ? 'Close menu' : 'Open menu');
        menu.setAttribute('aria-hidden', open ? 'false' : 'true');
        if (open) setTimeout(function () { closeBtn.focus({ preventScroll: true }); }, 60);
      }

      toggle.addEventListener('click', function () {
        setOpen(!menu.classList.contains('open'));
      });
      closeBtn.addEventListener('click', function () { setOpen(false); toggle.focus(); });
      backdrop.addEventListener('click', function () { setOpen(false); });

      menu.querySelectorAll('a').forEach(function (link) {
        link.addEventListener('click', function () { setOpen(false); });
      });

      document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && menu.classList.contains('open')) { setOpen(false); toggle.focus(); }
      });

      window.addEventListener('resize', function () {
        if (window.innerWidth > 860) setOpen(false);
      });
    })();

    // ── Banner carousel: arrows, dots, autoplay, swipe (iPhone) ──
    (function () {
      var root = document.getElementById('heroCarousel');
      var track = document.getElementById('hcTrack');
      if (!root || !track) return;
      var count = track.children.length;
      if (count < 2) return;

      var dots = root.querySelectorAll('.hc-dot');
      var index = 0, timer = null;
      var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

      function render() {
        track.style.transform = 'translate3d(' + (-index * 100) + '%,0,0)';
        dots.forEach(function (d, i) { d.classList.toggle('active', i === index); });
      }
      function go(i) { index = (i + count) % count; render(); restart(); }
      function restart() {
        clearInterval(timer);
        if (!reduce && !document.hidden) timer = setInterval(function () { index = (index + 1) % count; render(); }, 5000);
      }

      root.querySelectorAll('.hc-arrow').forEach(function (b) {
        b.addEventListener('click', function () { go(index + Number(b.dataset.dir)); });
      });
      dots.forEach(function (d) {
        d.addEventListener('click', function () { go(Number(d.dataset.i)); });
      });

      // Swipe
      var startX = 0, startY = 0, dx = 0, dragging = false, horizontal = null;
      root.addEventListener('touchstart', function (e) {
        startX = e.touches[0].clientX; startY = e.touches[0].clientY;
        dx = 0; dragging = true; horizontal = null;
        clearInterval(timer);
      }, { passive: true });
      root.addEventListener('touchmove', function (e) {
        if (!dragging) return;
        var mx = e.touches[0].clientX - startX, my = e.touches[0].clientY - startY;
        if (horizontal === null && (Math.abs(mx) > 6 || Math.abs(my) > 6)) horizontal = Math.abs(mx) > Math.abs(my);
        if (!horizontal) return;
        dx = mx;
        track.classList.add('dragging');
        track.style.transform = 'translate3d(calc(' + (-index * 100) + '% + ' + dx + 'px),0,0)';
      }, { passive: true });
      root.addEventListener('touchend', function () {
        if (!dragging) return;
        dragging = false;
        track.classList.remove('dragging');
        if (horizontal && Math.abs(dx) > root.offsetWidth * 0.18) go(index + (dx < 0 ? 1 : -1));
        else { render(); restart(); }
      });
      // Don't follow a slide link right after a swipe
      root.addEventListener('click', function (e) {
        if (Math.abs(dx) > 10 && e.target.closest('a')) { e.preventDefault(); dx = 0; }
      }, true);

      root.addEventListener('mouseenter', function () { clearInterval(timer); });
      root.addEventListener('mouseleave', restart);
      document.addEventListener('visibilitychange', restart);
      restart();
    })();
  </script>
</body>

</html>