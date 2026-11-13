<?php
// ============================================================
//  Arandia College eLMS — Scripts shared by every role
//  File: includes/footer_common.php  (included before each </body> in footer.php)
// ============================================================
require_once __DIR__ . '/settings.php';
?>
<script>
  // Back / Forward restored this page from the browser's memory (bfcache):
  // load it again from the server so the login check runs. After Sign out
  // this sends the user to the login page instead of showing old data.
  window.addEventListener('pageshow', function (e) { if (e.persisted) location.reload(); });

  // ── System settings on this page (Admin → Settings) ─────────────────────
  // Text marked data-elms-set="key" and the tab title follow the saved
  // settings; form fields marked data-elms-default="school_year|semester"
  // get the new default if the user hasn't changed them. includes/live.php
  // calls ElmsSettings.refresh() when another device saves the settings.
  window.ElmsSettings = window.ElmsSettings || (function () {
    let cur = <?= json_encode(elmsSettingsPublic(), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) ?>;
    let loading = null;
    function setField(el, oldVal, newVal) {
      if (el.value !== oldVal) return;                    // the user picked something else: keep it
      if (el.tagName === 'SELECT') {
        if (![...el.options].some(o => o.value === newVal)) return;
        [...el.options].forEach(o => { o.defaultSelected = o.value === newVal; });
        el.value = newVal;
      } else {
        el.defaultValue = newVal; el.value = newVal;
      }
    }
    function apply(s) {
      if (!s || typeof s !== 'object' || !s.system_name) return;
      const old = cur;
      // tab title: "Page — <system name>"
      if (old.system_name !== s.system_name) {
        const sep = ' — ' + old.system_name;
        if (document.title.endsWith(sep)) document.title = document.title.slice(0, -sep.length) + ' — ' + s.system_name;
      }
      document.querySelectorAll('[data-elms-set]').forEach(el => {
        const k = el.getAttribute('data-elms-set');
        if (typeof s[k] === 'string' && el.textContent !== s[k]) el.textContent = s[k];
      });
      document.querySelectorAll('[data-elms-default]').forEach(el => {
        const k = el.getAttribute('data-elms-default');
        if (typeof s[k] === 'string' && s[k] !== old[k]) setField(el, old[k], s[k]);
      });
      cur = s;
      window.dispatchEvent(new CustomEvent('elms:settings', { detail: s }));
    }
    function refresh() {
      if (loading) return loading;
      loading = fetch('../api/settings.php?action=get', { cache: 'no-store', credentials: 'same-origin' })
        .then(r => r.ok ? r.json() : null)
        .then(d => { if (d && d.success) apply(d.settings); })
        .catch(() => { })
        .finally(() => { loading = null; });
      return loading;
    }
    return { get: () => Object.assign({}, cur), apply, refresh };
  })();
  // Current term for lists built in the browser: { school_year: '2025-2026', semester: '1st', ... }
  window.elmsTermNow = () => window.ElmsSettings.get();

  // Admin / Admission pages: stop the browser's saved-login autofill from dropping
  // the sign-in email / password into search boxes and form fields (Chrome sees the
  // hidden "New password" field of the Edit Account window and treats the page like
  // a login form). Only fields the BROWSER filled are cleared (they match
  // :autofill); values the page sets itself or the user picks/types are kept.
  (function () {
    const role = <?= json_encode($_SESSION['role'] ?? '') ?>;
    if (!['Admin', 'Head Admin', 'Admission'].includes(role)) return;
    const touched = new WeakSet();   // fields the user focused (picking a suggestion there is on purpose)
    document.addEventListener('focusin', e => { if (e.isTrusted && e.target && e.target.tagName === 'INPUT') touched.add(e.target); }, true);
    const isAutofilled = el => { try { return el.matches(':autofill'); } catch (e) { } try { return el.matches(':-webkit-autofill'); } catch (e) { } return false; };
    function undo(el) {
      if (!el || el.tagName !== 'INPUT' || touched.has(el) || el.value === el.defaultValue) return;
      el.value = el.defaultValue;
      el.dispatchEvent(new Event('input', { bubbles: true }));
    }
    function sweep() { document.querySelectorAll('input').forEach(el => { if (isAutofilled(el)) undo(el); }); }
    // Chrome / Edge / Safari: an autofilled field starts this (empty) animation
    const st = document.createElement('style');
    st.textContent = '@keyframes elmsAutofillStart{from{}to{}} input:-webkit-autofill{animation-name:elmsAutofillStart;animation-duration:1ms}';
    document.head.appendChild(st);
    document.addEventListener('animationstart', e => { if (e.animationName === 'elmsAutofillStart') undo(e.target); }, true);
    [100, 500, 1500, 3000].forEach(ms => setTimeout(sweep, ms));
    addEventListener('pageshow', () => setTimeout(sweep, 50));
  })();

  // Phones: the sidebar becomes a sideways-scrolling strip — bring the current page's link into view.
  document.addEventListener('DOMContentLoaded', () => {
    const on = document.querySelector('.sidebar-nav .sidebar-link.active, .nav-menu .nav-link.active');
    const nav = on && on.closest('.sidebar-nav, .nav-menu');
    if (nav && nav.scrollWidth > nav.clientWidth) nav.scrollLeft = on.offsetLeft - (nav.clientWidth - on.offsetWidth) / 2;
    if (on) on.setAttribute('aria-current', 'page');
  });

  // Keeps "Online users" accurate for people who are reading without clicking.
  setInterval(() => { if (!document.hidden) fetch('../api/heartbeat.php', { cache: 'no-store' }).catch(() => {}); }, 120000);

  document.addEventListener('DOMContentLoaded', () => {
    const p = new URLSearchParams(location.search);
    // Teacher dashboard quick actions: open the matching form right away
    const action = p.get('action');
    // Wait one tick so the remembered class (below) is selected first
    setTimeout(() => {
      if (action === 'new-assignment' && typeof openAssignModal === 'function') openAssignModal();
      if (action === 'ai-quiz' && typeof openAiQuizModal === 'function') openAiQuizModal();
      if (action === 'new-quiz' && typeof openQuizModal === 'function') openQuizModal();
    }, 0);
    // Student Assignments / Quizzes: ?item=ID (from the Modules page) opens that row's subject and points to it
    const item = parseInt(p.get('item') || '', 10);
    if (item > 0 && <?= json_encode($_SESSION['role'] ?? '') ?> === 'Student') {
      const kind = p.get('kind') === 'quiz' ? 'quiz' : 'assignment';
      const row = document.querySelector('.section-panel .acc-body .acc-row[data-kind="' + kind + '"][data-id="' + item + '"]')
        || document.querySelector('.section-panel .acc-body .acc-row:not([data-kind])[data-id="' + item + '"]');
      const body = row && row.closest('.acc-body');
      if (body) {
        if (!body.classList.contains('open') && typeof toggleBlock === 'function' && body.id) toggleBlock(body.id);
        setTimeout(() => {
          row.scrollIntoView({ block: 'center', behavior: 'smooth' });
          if (row.animate && !matchMedia('(prefers-reduced-motion: reduce)').matches) {
            row.animate([{ boxShadow: 'inset 0 0 0 2px var(--primary, #003087)' }, { boxShadow: 'inset 0 0 0 2px transparent' }], { duration: 2600, easing: 'ease-out' });
          }
        }, 60);
      }
    }
    // Teacher search: prefill the student search box
    const q = p.get('search'), box = document.getElementById('stu-search');
    if (q && box) { box.value = q; if (typeof filterStudents === 'function') filterStudents(q); }
  });

  // Teacher pages: remember the class picked last, so Assignments / Quizzes / Grades /
  // Attendance / Modules open on the same subject. ?course=ID (from My Classes) wins.
  document.addEventListener('DOMContentLoaded', () => {
    if (<?= json_encode($_SESSION['role'] ?? '') ?> !== 'Teacher') return;
    const KEY = 'elms_teacher_course_<?= (int) ($_SESSION['user_id'] ?? 0) ?>';
    const fromUrl = new URLSearchParams(location.search).get('course');
    let saved = null;
    try { saved = localStorage.getItem(KEY); } catch (e) { }
    const remember = v => { if (v) try { localStorage.setItem(KEY, v); } catch (e) { } };

    ['assign-course-select', 'quiz-course-select', 'grade-course-select', 'att-course-select'].forEach(id => {
      const sel = document.getElementById(id);
      if (!sel) return;
      sel.addEventListener('change', () => remember(sel.value));
      const values = [...new Set([...sel.options].map(o => o.value).filter(Boolean))];
      const has = v => v && values.includes(v);
      let pick = has(fromUrl) ? fromUrl : (has(saved) ? saved : (values.length === 1 ? values[0] : null));
      if (pick && !sel.value) { sel.value = pick; remember(pick); sel.dispatchEvent(new Event('change')); }
    });

    const tm = document.getElementById('tm-course');
    if (tm) {
      const values = [...tm.options].map(o => o.value);
      if (!fromUrl && saved && values.includes(saved) && tm.value !== saved) { location.replace('modules.php?course=' + encodeURIComponent(saved)); return; }
      remember(tm.value);
    }
  });
</script>
<?php include __DIR__ . '/live.php'; ?>
