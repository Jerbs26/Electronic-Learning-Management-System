<?php
// ============================================================
//  Arandia College eLMS — Page transition (Home ⇄ Sign in)
//  File: includes/page_transition.php
//  Include inside <head> of index.php and login.php.
//  • Chrome / Edge / Safari 18.2+: native cross-document View Transitions
//  • Other browsers (Firefox): a slower fade-out / fade-in fallback
//  • Turned off when the user prefers reduced motion
// ============================================================
?>
<style>
  :root { --pt-duration: .8s; }   /* ← i-adjust dito: .5s medyo mabagal, .8s sobrang smooth */

  @view-transition { navigation: auto; }

  @keyframes pt-out { to   { opacity: 0; } }
  @keyframes pt-in  { from { opacity: 0; transform: translateY(6px); } }

  ::view-transition-old(root) { animation: pt-out var(--pt-duration) cubic-bezier(.4, 0, .2, 1) both; }
  ::view-transition-new(root) { animation: pt-in var(--pt-duration) cubic-bezier(.22, .7, .25, 1) both; }

  /* Fallback (no View Transitions support) */
  html.pt-fallback body { animation: pt-in var(--pt-duration) cubic-bezier(.22, .7, .25, 1) backwards; }
  html.pt-fallback.pt-leaving body { animation: pt-out var(--pt-duration) cubic-bezier(.4, 0, .2, 1) both; }

  @media (prefers-reduced-motion: reduce) {
    @view-transition { navigation: none; }
    ::view-transition-old(root), ::view-transition-new(root),
    html.pt-fallback body, html.pt-fallback.pt-leaving body { animation: none; }
  }
</style>
<script>
  (function () {
    // Browsers with cross-document View Transitions handle everything in CSS.
    if ('onpagereveal' in window) return;
    if (window.matchMedia && matchMedia('(prefers-reduced-motion: reduce)').matches) return;

    var root = document.documentElement;
    root.classList.add('pt-fallback');
    var PAGES = /(^|\/)(index\.php|login\.php)?$/;   // only Home ⇄ Sign in

    document.addEventListener('click', function (e) {
      if (e.defaultPrevented || e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
      var a = e.target.closest && e.target.closest('a[href]');
      if (!a || a.target && a.target !== '_self' || a.hasAttribute('download')) return;
      var url = new URL(a.href, location.href);
      if (url.origin !== location.origin || !PAGES.test(url.pathname)) return;
      if (url.pathname === location.pathname && url.hash) return;   // in-page anchor
      e.preventDefault();
      root.classList.add('pt-leaving');
      setTimeout(function () { location.href = url.href; }, 300);   // dati 140 → 300ms para matapos ang fade-out
    });

    // Coming back with the Back button (page restored from cache): show it again
    window.addEventListener('pageshow', function (e) {
      if (e.persisted) root.classList.remove('pt-leaving');
    });
  })();
</script>