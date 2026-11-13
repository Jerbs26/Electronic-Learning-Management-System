/* ============================================================
   Arandia College eLMS — Motion & interaction helpers
   File: assets/js/elms-motion.js
   Loaded (defer) on every Admin / Teacher / Student / Admission page.
   Styles: assets/css/elms-motion.css

   What it does (nothing here changes what a button does):
   1. Double-submit guard
        · native <form> posts: a second submit while the first is on its way is ignored
        · fetch(): an identical POST (same URL + same text body) that is still in flight
          shares the first request, so a fast double-click saves once
   2. Dialogs (.modal-overlay, any role)
        · Escape closes the top dialog (through its own Close / Cancel button)
        · focus moves into the dialog and returns to the button that opened it
        · Tab stays inside the dialog; the page behind does not scroll
        · role="dialog" / aria-modal / aria-labelledby added where missing
   One observer, a few listeners, no timers left running.
   ============================================================ */
(function () {
  'use strict';
  if (window.__elmsMotion) return;
  window.__elmsMotion = true;

  var root = document.documentElement;

  /* ── 1a. Native form double-submit guard ─────────────────────── */
  var BUSY_MS = 12000;   // safety release (file downloads keep the page open)

  function release(form) {
    if (!form || !form.__elmsBusy) return;
    form.__elmsBusy = false;
    clearTimeout(form.__elmsTimer);
    form.querySelectorAll('.is-busy').forEach(function (b) {
      b.classList.remove('is-busy');
      b.removeAttribute('aria-busy');
    });
  }

  document.addEventListener('submit', function (e) {
    var form = e.target;
    if (!form || form.tagName !== 'FORM') return;
    if (e.defaultPrevented) return;          // handled by the page's own script (ajax etc.)
    if (form.__elmsBusy) { e.preventDefault(); return; }
    form.__elmsBusy = true;
    var btn = e.submitter || form.querySelector('button[type="submit"], input[type="submit"], button:not([type])');
    // never `disabled`: a disabled submit button would drop its name/value from the post
    if (btn && btn.classList) { btn.classList.add('is-busy'); btn.setAttribute('aria-busy', 'true'); }
    form.__elmsTimer = setTimeout(function () { release(form); }, BUSY_MS);
  }, false);

  // Back/forward cache: a restored page must not stay "busy"
  window.addEventListener('pageshow', function (e) {
    if (!e.persisted) return;
    document.querySelectorAll('form').forEach(release);
  });

  /* ── 1b. Identical in-flight POSTs share one request ─────────── */
  if (typeof window.fetch === 'function' && typeof Response === 'function') {
    var nativeFetch = window.fetch.bind(window);
    var inflight = Object.create(null);

    window.fetch = function (input, init) {
      try {
        var method = String((init && init.method) || (input && input.method) || 'GET').toUpperCase();
        var body = init && init.body;
        var isText = typeof body === 'string' || (typeof URLSearchParams !== 'undefined' && body instanceof URLSearchParams);
        if (method !== 'POST' || !isText || (init && init.signal)) return nativeFetch(input, init);

        var url = typeof input === 'string' ? input : (input && input.url) || '';
        var key = url + '\n' + String(body);
        var running = inflight[key];
        if (running) return running.then(function (r) { return r.clone(); });

        var p = nativeFetch(input, init);
        var shared = p.then(function (r) { return r; });
        inflight[key] = shared;
        var done = function () { if (inflight[key] === shared) delete inflight[key]; };
        shared.then(done, done);
        // the first caller gets a clone too, so later callers can still clone the original
        return shared.then(function (r) { return r.clone(); });
      } catch (err) {
        return nativeFetch(input, init);
      }
    };
  }

  /* ── 2. Dialogs ──────────────────────────────────────────────── */
  var FOCUSABLE = 'a[href], button:not([disabled]), input:not([disabled]):not([type="hidden"]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])';
  var opener = new WeakMap();   // overlay → element that had focus when it opened
  var wasOpen = new WeakMap();  // overlay → last known visibility
  var uid = 0;

  function overlays() { return document.querySelectorAll('.modal-overlay'); }
  function isVisible(el) {
    if (el.hidden || el.classList.contains('hidden')) return false;
    var cs = getComputedStyle(el);
    return cs.display !== 'none' && cs.visibility !== 'hidden';
  }
  function openOverlays() { return Array.prototype.filter.call(overlays(), isVisible); }

  function prepare(ov) {
    var m = ov.querySelector('.modal');
    if (!m) return;
    if (!m.hasAttribute('role')) m.setAttribute('role', 'dialog');
    if (!m.hasAttribute('aria-modal')) m.setAttribute('aria-modal', 'true');
    if (!m.hasAttribute('aria-label') && !m.hasAttribute('aria-labelledby')) {
      var t = m.querySelector('.modal-title, .modal-head h2, .modal-header, h2, h3');
      if (t) {
        if (!t.id) t.id = 'elms-dlg-' + (++uid);
        m.setAttribute('aria-labelledby', t.id);
      }
    }
    if (!m.hasAttribute('tabindex')) m.setAttribute('tabindex', '-1');
  }

  function scrollbarGap() { return Math.max(0, window.innerWidth - root.clientWidth); }

  function syncLock() {
    var any = openOverlays().length > 0;
    if (any === root.classList.contains('m-modal-open')) return;
    if (any) {
      var gap = scrollbarGap();                       // keep the page from jumping sideways
      if (gap) root.style.paddingRight = gap + 'px';
      root.classList.add('m-modal-open');
    } else {
      root.classList.remove('m-modal-open');
      root.style.paddingRight = '';
    }
  }

  function check(ov) {
    var vis = isVisible(ov);
    var was = wasOpen.get(ov) === true;
    if (vis === was) return;
    wasOpen.set(ov, vis);
    if (vis) {
      prepare(ov);
      var from = document.activeElement;
      if (from && from !== document.body && !ov.contains(from)) opener.set(ov, from);
      // after the page's own script had its chance to focus a field
      requestAnimationFrame(function () {
        if (!isVisible(ov) || ov.contains(document.activeElement)) return;
        var m = ov.querySelector('.modal');
        if (m) m.focus({ preventScroll: true });
      });
    } else {
      var back = opener.get(ov);
      opener.delete(ov);
      if (back && document.contains(back) && (document.activeElement === document.body || ov.contains(document.activeElement))) {
        try { back.focus({ preventScroll: true }); } catch (e) { }
      }
    }
    syncLock();
  }

  var queued = false;
  function scan() {
    if (queued) return;
    queued = true;
    requestAnimationFrame(function () {
      queued = false;
      overlays().forEach(check);
      syncLock();
    });
  }

  function closeTop() {
    var list = openOverlays();
    if (!list.length) return false;
    var ov = list[list.length - 1];
    if (ov.hasAttribute('data-no-esc')) return false;   // opt-out for dialogs that must not be dismissed
    var btn = ov.querySelector('.modal-close, [data-modal-close], [aria-label="Close"], [aria-label="close"]');
    if (!btn) {
      var cands = ov.querySelectorAll('.modal-actions button, .modal-footer button, .modal-foot button, .modal button');
      for (var i = 0; i < cands.length; i++) {
        if (/^\s*(cancel|close|back|done|no)\b/i.test(cands[i].textContent || '')) { btn = cands[i]; break; }
      }
    }
    if (!btn || btn.disabled) return false;
    btn.click();
    return true;
  }

  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
      if (e.defaultPrevented) return;
      closeTop();
      return;
    }
    if (e.key !== 'Tab') return;
    var list = openOverlays();
    if (!list.length) return;
    var ov = list[list.length - 1];
    var items = Array.prototype.filter.call(ov.querySelectorAll(FOCUSABLE), function (n) { return n.offsetParent !== null; });
    if (!items.length) return;
    var first = items[0], last = items[items.length - 1], a = document.activeElement;
    var inside = ov.contains(a);
    if (e.shiftKey && (a === first || !inside || a === ov.querySelector('.modal'))) { e.preventDefault(); last.focus(); }
    else if (!e.shiftKey && (a === last || !inside)) { e.preventDefault(); first.focus(); }
  }, false);

  function init() {
    overlays().forEach(prepare);
    if ('MutationObserver' in window) {
      // class / hidden / style changes on dialogs, and dialogs added later
      new MutationObserver(function (list) {
        for (var i = 0; i < list.length; i++) {
          var r = list[i];
          if (r.type === 'attributes') {
            if (r.target.classList && r.target.classList.contains('modal-overlay')) { scan(); return; }
          } else {
            for (var j = 0; j < r.addedNodes.length; j++) {
              var n = r.addedNodes[j];
              if (n.nodeType === 1 && ((n.classList && n.classList.contains('modal-overlay')) || (n.querySelector && n.querySelector('.modal-overlay')))) { scan(); return; }
            }
          }
        }
      }).observe(document.body, { attributes: true, attributeFilter: ['class', 'style', 'hidden'], childList: true, subtree: true });
    }
    scan();
  }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
  else init();

  window.addEventListener('pageshow', function (e) { if (e.persisted) scan(); });
})();
