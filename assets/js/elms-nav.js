/* ============================================================
   Arandia College eLMS — Responsive navigation + layout helpers
   File: assets/js/elms-nav.js
   Loaded (defer) by includes/header.php on the Admin, Teacher and
   Student pages. Styles: assets/css/elms-responsive.css.

   · Phones and tablets (< 992px): the sidebar — and the class/subject
     menu beside it, when one is open — slides in as a drawer from the
     menu button in the top bar. Close it with the X, the backdrop,
     the Escape key, or by picking a page.
   · Wide tables get their own sideways scroll box, so a wide table
     never makes the whole page scroll sideways.
   ============================================================ */
(function () {
  'use strict';

  var DRAWER_MQ = window.matchMedia('(max-width: 991.98px)');
  var root = document.documentElement;
  var lastFocus = null;
  var hideTimer = null;

  function $(sel) { return document.querySelector(sel); }
  function sidebar() { return document.getElementById('appSidebar'); }
  function classMenu() { var c = $('.cnav'); return c && !c.hidden ? c : null; }
  function backdrop() { return $('.nav-backdrop'); }
  function toggles() { return document.querySelectorAll('[data-nav-open]'); }
  function isOpen() { return root.classList.contains('nav-open'); }

  function setExpanded(v) {
    toggles().forEach(function (b) { b.setAttribute('aria-expanded', v ? 'true' : 'false'); });
  }

  function openNav() {
    if (!DRAWER_MQ.matches || isOpen() || !sidebar()) return;
    lastFocus = document.activeElement;
    var bd = backdrop();
    clearTimeout(hideTimer);
    if (bd) bd.hidden = false;
    // next frame, so the backdrop fades in instead of popping
    requestAnimationFrame(function () {
      root.classList.add('nav-open');
      setExpanded(true);
      var first = sidebar().querySelector('.nav-close');
      if (first) setTimeout(function () { first.focus({ preventScroll: true }); }, 60);
    });
  }

  function closeNav(restoreFocus) {
    if (!isOpen()) return;
    root.classList.remove('nav-open');
    setExpanded(false);
    var bd = backdrop();
    clearTimeout(hideTimer);
    hideTimer = setTimeout(function () { if (bd && !isOpen()) bd.hidden = true; }, 280);
    if (restoreFocus !== false) {
      var t = (lastFocus && document.contains(lastFocus)) ? lastFocus : toggles()[0];
      if (t && t.focus) t.focus({ preventScroll: true });
    }
  }

  // Open / close buttons and backdrop (event delegation: works for markup
  // that live updates put back in place, too)
  document.addEventListener('click', function (e) {
    var t = e.target;
    if (!t || !t.closest) return;
    if (t.closest('[data-nav-open]')) { e.preventDefault(); openNav(); return; }
    if (t.closest('[data-nav-close]')) { e.preventDefault(); closeNav(); return; }
    // Picking a page in the drawer closes it (the page then loads)
    if (isOpen()) {
      var link = t.closest('#appSidebar a[href], #appSidebar .sidebar-link, #appSidebar .nav-link, .cnav a[href]');
      if (link) closeNav(false);
    }
  });

  document.addEventListener('keydown', function (e) {
    if (!isOpen()) return;
    if (e.key === 'Escape') {
      // let the class switcher inside the drawer close itself first
      var m = $('#cnav-menu');
      if (m && !m.hidden) return;
      closeNav();
      return;
    }
    if (e.key === 'Tab') {
      // keep keyboard focus inside the open drawer
      var scope = [sidebar(), classMenu()].filter(Boolean);
      var items = [];
      scope.forEach(function (el) {
        items = items.concat(Array.prototype.slice.call(el.querySelectorAll(
          'a[href], button:not([disabled]), input:not([disabled]), select, textarea, [tabindex]:not([tabindex="-1"])'
        )).filter(function (n) { return n.offsetParent !== null || n.classList.contains('nav-close'); }));
      });
      if (!items.length) return;
      var first = items[0], last = items[items.length - 1];
      var inside = scope.some(function (el) { return el.contains(document.activeElement); });
      if (e.shiftKey && (document.activeElement === first || !inside)) { e.preventDefault(); last.focus(); }
      else if (!e.shiftKey && (document.activeElement === last || !inside)) { e.preventDefault(); first.focus(); }
    }
  });

  // Back to desktop width → the drawer state no longer applies
  function onMq() { if (!DRAWER_MQ.matches) closeNav(false); }
  if (DRAWER_MQ.addEventListener) DRAWER_MQ.addEventListener('change', onMq);
  else if (DRAWER_MQ.addListener) DRAWER_MQ.addListener(onMq);

  // Back/forward cache: never come back to a page with the drawer open
  window.addEventListener('pageshow', function () { closeNav(false); var bd = backdrop(); if (bd) bd.hidden = true; });

  // ── Wide tables: own sideways scroll ───────────────────────────
  function scrollParent(el, stop) {
    for (var a = el.parentElement; a && a !== stop; a = a.parentElement) {
      var ox = getComputedStyle(a).overflowX;
      if (ox === 'auto' || ox === 'scroll') return a;
    }
    return null;
  }
  function wrapTables(scope) {
    var main = $('main');
    if (!main) return;
    (scope || main).querySelectorAll('table').forEach(function (tb) {
      if (tb.closest('.table-scroll') || tb.dataset.noScrollWrap !== undefined) return;
      if (!main.contains(tb) || scrollParent(tb, main)) return;
      var w = document.createElement('div');
      w.className = 'table-scroll';
      tb.parentNode.insertBefore(w, tb);
      w.appendChild(tb);
    });
  }

  function init() {
    wrapTables();
    // Live updates (includes/live.php) and tabs swap page parts in place
    var main = $('main');
    if (main && 'MutationObserver' in window) {
      var pending = false;
      new MutationObserver(function () {
        if (pending) return;
        pending = true;
        requestAnimationFrame(function () { pending = false; wrapTables(); });
      }).observe(main, { childList: true, subtree: true });
    }
  }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
  else init();

  // Small API for page scripts
  window.elmsNav = { open: openNav, close: closeNav, isOpen: isOpen };
})();
