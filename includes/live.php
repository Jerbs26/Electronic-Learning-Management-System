<?php
// NO POP-UPS: no toasts ("Your account was updated", "New grade…") and no
// "New updates are available / Refresh" bar. Notifications stay in the bell;
// a page that needs a reload reloads by itself as soon as the user is not
// typing and has no window open (a page with unsaved typing is never
// reloaded, so nothing typed is lost). A session that ended goes straight
// to the login page.
if (empty($_SESSION['user_id'])) return;
$__liveLast = 0;
try {
    $__r = $conn->query("SHOW TABLES LIKE 'sync_events'");
    if ($__r && $__r->num_rows) $__liveLast = (int) $conn->query("SELECT COALESCE(MAX(id), 0) FROM sync_events")->fetch_row()[0];
    else return;   // migration_integration.sql not imported yet — no live updates, nothing breaks
} catch (Throwable $e) { return; }
?>
<style>
  .sr-only { position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px; overflow: hidden; clip: rect(0, 0, 0, 0); white-space: nowrap; border: 0; }
  .lv-stack { position: fixed; left: 50%; transform: translateX(-50%); bottom: 18px; z-index: 2000; display: flex; flex-direction: column; gap: .45rem; width: min(420px, calc(100vw - 32px)); pointer-events: none; }
  .lv-toast { pointer-events: auto; display: flex; align-items: center; gap: .7rem; background: #0f172a; color: #fff; border-radius: 12px; padding: .7rem .9rem; font: 600 .84rem/1.35 Inter, system-ui, sans-serif; box-shadow: 0 10px 30px rgba(15, 23, 42, .25); animation: lvIn .18s ease-out; }
  .lv-toast a { color: #c7d7ff; font-weight: 800; white-space: nowrap; text-decoration: none; margin-left: auto; }
  .lv-toast a:hover { text-decoration: underline; }
  .lv-toast button { background: none; border: 0; color: #94a3b8; font-size: 1.1rem; line-height: 1; cursor: pointer; padding: .1rem .2rem; }
  .lv-bar { position: fixed; top: 10px; left: 50%; transform: translateX(-50%); z-index: 2000; background: #003087; color: #fff; border-radius: 100px; padding: .45rem .55rem .45rem 1rem; font: 700 .82rem Inter, system-ui, sans-serif; box-shadow: 0 8px 24px rgba(0, 48, 135, .3); display: none; align-items: center; gap: .6rem; }
  .lv-bar.show { display: flex; }
  .lv-bar button { background: var(--ui-surface, #fff); color: var(--ui-link, #003087); border: 0; border-radius: 100px; padding: .35rem .8rem; font: inherit; cursor: pointer; }
  .lv-bar.warn { background: #b45309; }
/* student bell list */
  .stu-notif-list .notif-item { padding: .8rem 1rem; border-bottom: 1px solid var(--border, #f1f5f9); align-items: flex-start; }
  .stu-notif-list .notif-item:hover { background: var(--bg-light, #f8fafc); }
  .stu-notif-list .notif-dot { width: 8px; height: 8px; border-radius: 50%; margin-top: .4rem; flex-shrink: 0; background: transparent; }
  .stu-notif-list .notif-item.unread .notif-dot { background: var(--primary, #003087); }
  .stu-notif-list .notif-item-title { font-size: .86rem; font-weight: 600; }
  .stu-notif-list .notif-item.unread .notif-item-title { font-weight: 800; }
  .stu-notif-list .notif-item-meta { font-size: .75rem; color: var(--text-muted, #64748b); margin-top: .15rem; }
  @keyframes lvIn { from { opacity: 0; transform: translateY(6px); } to { opacity: 1; transform: none; } }
  @media (prefers-reduced-motion: reduce) { .lv-toast { animation: none; } }
</style>
<div class="lv-stack" id="lvStack" role="status" aria-live="polite"></div>
<script>
(function () {
  if (window.ElmsLive) return;   // never start two pollers on one page
  const API = '../api/sync.php';
  const ROLE = <?= json_encode($_SESSION['role'] ?? '') ?>;
  const PAGE = (location.pathname.split('/').pop() || 'dashboard.php').replace('.php', '');
  let last = <?= (int) $__liveLast ?>, timer = null, kickTimer = null, failures = 0, stopped = false, busy = false;

  // ── This account was just signed in on another device ─────────────────────
  // The server has already ended this session (includes/single_session.php), so nothing here can
  // be used any more. Instead of jumping to the sign-in page without a word, say why first and
  // go to the sign-in page when the person presses OK.
  let kickedShown = false;
  function showKickedNotice() {
    stopped = true;
    clearTimeout(timer); clearTimeout(kickTimer);
    if (kickedShown) return;
    kickedShown = true;
    const go = () => { location.href = '../login.php?kicked=1'; };
    try {
      const o = document.createElement('div');
      o.setAttribute('role', 'alertdialog');
      o.setAttribute('aria-modal', 'true');
      o.setAttribute('aria-labelledby', 'elms-kick-title');
      o.style.cssText = 'position:fixed;inset:0;z-index:2147483000;background:rgba(15,23,42,.6);display:flex;align-items:center;justify-content:center;padding:1rem;font-family:inherit';
      o.innerHTML =
        '<div style="background:#fff;color:#1a1a2e;border-radius:16px;max-width:420px;width:100%;padding:1.6rem 1.5rem 1.4rem;text-align:center;box-shadow:0 20px 60px rgba(0,0,0,.35)">' +
          '<div style="width:46px;height:46px;border-radius:50%;background:#FFF4E0;color:#B7791F;display:flex;align-items:center;justify-content:center;margin:0 auto .8rem">' +
            '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 9v4"/><path d="M12 17h.01"/><path d="M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0z"/></svg>' +
          '</div>' +
          '<div id="elms-kick-title" style="font-size:1.15rem;font-weight:800;margin-bottom:.5rem">Signed in on another device</div>' +
          '<p style="margin:0 0 1.2rem;font-size:.92rem;line-height:1.55;color:#475569">Your account was just signed in on another device, so you will be signed out here.</p>' +
          '<button type="button" id="elms-kick-ok" style="width:100%;padding:.8rem 1rem;border:0;border-radius:12px;background:#0B2545;color:#fff;font-weight:700;font-size:.95rem;font-family:inherit;cursor:pointer">OK</button>' +
        '</div>';
      document.body.appendChild(o);
      const ok = document.getElementById('elms-kick-ok');
      ok.addEventListener('click', go);
      ok.focus();
      // Enter / Esc also mean OK; Tab stays on the button so nothing behind the notice can be used
      document.addEventListener('keydown', e => {
        if (e.key === 'Escape' || e.key === 'Enter') { e.preventDefault(); go(); }
        else if (e.key === 'Tab') { e.preventDefault(); ok.focus(); }
      }, true);
    } catch (e) { go(); }
  }
  // Any request the page makes (its own data loading included) that the server answers with
  // "this account signed in on another device" shows the same notice — so a teacher is never
  // logged out silently just because a page action happened to be the first to notice.
  if (typeof window.fetch === 'function' && !window.__elmsKickWrap) {
    window.__elmsKickWrap = true;
    const nativeFetch = window.fetch;
    window.fetch = function () {
      return nativeFetch.apply(this, arguments).then(function (r) {
        try { if (r && r.headers && r.headers.get('X-Elms-Kicked') === '1') showKickedNotice(); } catch (e) { }
        return r;
      });
    };
  }
  // Teachers' pages poll every 15 s (see schedule()); this light check every 5 s makes the notice
  // appear as fast as it does for students. Slower while the tab is in the background.
  function kickWatch() {
    clearTimeout(kickTimer);
    if (kickedShown) return;
    kickTimer = setTimeout(async function () {
      try { await fetch('../api/heartbeat.php', { cache: 'no-store', credentials: 'same-origin' }); } catch (e) { /* offline — try again */ }
      kickWatch();
    }, document.hidden ? 30000 : 5000);
  }
  const seen = new Set();
  let dirty = false;                                  // user typed / changed something on this page
  const refreshers = [];                              // page data loaders: {topics, fn}

  // Which pages care about which kind of change
  const TOPICS = {
    // (modules / classes: the Modules pages list each module's assignments and quizzes)
    // ('work' = the page of one assignment / quiz: details, results and submissions)
    'assignment': ['assignments', 'grade', 'grades', 'dashboard', 'my-subjects', 'classes', 'modules', 'work'],
    'quiz': ['quizzes', 'assignments', 'grades', 'dashboard', 'my-subjects', 'classes', 'modules', 'work'],
    'submission': ['assignments', 'grade', 'grades', 'dashboard', 'classes', 'modules', 'work'],
    'grade': ['grades', 'grade', 'assignments', 'dashboard', 'my-subjects', 'quizzes', 'classes', 'modules', 'work'],
    'attendance': ['attendance', 'dashboard'],
    'announcement': ['announcements', 'dashboard'],
    'enrollment': ['classes', 'my-students', 'my-subjects', 'dashboard', 'grades', 'attendance', 'assignments', 'quizzes', 'modules', 'subjects-enrollment', 'accounts', 'students', 'enroll'],
    'class': ['classes', 'my-students', 'my-subjects', 'dashboard', 'grades', 'attendance', 'assignments', 'quizzes', 'modules', 'subjects-enrollment', 'students', 'enroll'],
    'module': ['modules', 'classes', 'dashboard', 'my-subjects', 'work'],
    // subject added / renamed: everywhere a subject name or the subject list shows
    'subject': ['modules', 'subjects-enrollment', 'dashboard', 'classes', 'my-subjects', 'my-students', 'assignments', 'quizzes', 'grades', 'attendance', 'announcements', 'awards', 'students', 'enroll', 'work'],
    // section added / renamed / deleted (admin + admission pages that list sections)
    'section': ['subjects-enrollment', 'accounts', 'dashboard', 'students', 'enroll', 'admins'],
    'award': ['awards', 'dashboard'],
    'account': ['accounts', 'dashboard', 'admins', 'students'],
    'banner': ['dashboard', 'announcements'],
    // Contact Us inbox: the Messages page refreshes its own list (see messages-inbox.body.php)
    'message': ['messages'],
  };
  const affects = ev => (TOPICS[ev.kind.split('.')[0]] || []).includes(PAGE);

  // Track unsaved input (search boxes don't count)
  const isSearch = el => el && (el.type === 'search' || /search|filter|(^|-)q$/i.test(el.id || '') || el.closest('[role="search"]'));
  document.addEventListener('input', e => { if (!isSearch(e.target)) dirty = true; }, true);
  document.addEventListener('change', e => { if (!isSearch(e.target) && !e.target.classList.contains('cp-hidden-select')) dirty = true; }, true);

  function modalOpen() {   // any window / overlay actually visible right now
    const visible = el => { const s = getComputedStyle(el); return s.display !== 'none' && s.visibility !== 'hidden' && el.offsetWidth > 0; };
    return [...document.querySelectorAll('.modal-overlay, .modal-bg, .gb-ov, .tm-overlay, [role="dialog"], dialog')].some(visible)
      || !!document.querySelector('.notif-dropdown.open, .notif-panel.open, .notif-panel.show');
  }
  function typing() {
    const a = document.activeElement;
    return a && /^(INPUT|TEXTAREA|SELECT)$/.test(a.tagName) && !isSearch(a) || (a && a.isContentEditable);
  }

  // ── Toasts ──
  const shownToasts = [];
  function toast(msg, link) {
    return;   // pop-ups turned off: notifications are in the bell only
    shownToasts.push([msg, link]);
    const stack = document.getElementById('lvStack'); if (!stack) return;
    const t = document.createElement('div'); t.className = 'lv-toast';
    const span = document.createElement('span'); span.textContent = msg; t.appendChild(span);
    if (link) { const a = document.createElement('a'); a.href = link; a.textContent = 'Open'; t.appendChild(a); }
    const x = document.createElement('button'); x.type = 'button'; x.setAttribute('aria-label', 'Dismiss'); x.textContent = '×'; x.onclick = () => t.remove(); t.appendChild(x);
    stack.appendChild(t);
    while (stack.children.length > 3) stack.firstChild.remove();
    setTimeout(() => t.remove(), 7000);
  }
  function bar(text, warn, onClick) {
    return;   // the "New updates are available / Refresh" bar is turned off
    const b = document.getElementById('lvBar'); if (!b) return;
    document.getElementById('lvBarText').textContent = text;
    b.classList.toggle('warn', !!warn); b.classList.add('show');
    document.getElementById('lvBarBtn').onclick = onClick || (() => location.reload());
  }

  // ── Page refresh ──
  let refreshTimer = null, pendingReload = false;
  function refreshPage(events) {
    clearTimeout(refreshTimer);
    refreshTimer = setTimeout(async () => {
      const mine = refreshers.filter(r => events.some(ev => r.topics.includes(ev.kind.split('.')[0])));
      if (ROLE === 'Student') {
        // Dashboard banners are a running slideshow: those need a fresh page
        if (PAGE === 'dashboard' && events.some(ev => ev.kind.startsWith('banner.'))) { pendingReload = true; tryReload(); return; }
        for (const r of mine) { try { await r.fn(events); } catch (e) { console.warn('[live] refresh failed', e); } }
        Soft.request();
        return;
      }
      if (mine.length) { for (const r of mine) { try { await r.fn(events); } catch (e) { console.warn('[live] refresh failed', e); } } return; }
      pendingReload = true; tryReload();
    }, 600);
  }
  function tryReload() {
    if (!pendingReload) return;
    if (!dirty && !typing() && !modalOpen() && !document.hidden) {
      try {
        sessionStorage.setItem('elms_live_scroll', JSON.stringify({ p: location.pathname + location.search, y: scrollY }));
        // keep the toasts that were just shown so they survive the reload
        sessionStorage.setItem('elms_live_toasts', JSON.stringify(shownToasts.slice(-3)));
      } catch (e) { }
      location.reload();
    } else {
      bar('New updates are available.', false);
    }
  }
  try {   // keep the scroll position after a live reload
    const s = JSON.parse(sessionStorage.getItem('elms_live_scroll') || 'null');
    if (s && s.p === location.pathname + location.search) addEventListener('load', () => scrollTo(0, s.y));
    sessionStorage.removeItem('elms_live_scroll');
    const carried = JSON.parse(sessionStorage.getItem('elms_live_toasts') || '[]');
    sessionStorage.removeItem('elms_live_toasts');
    if (carried.length) addEventListener('DOMContentLoaded', () => carried.forEach(([m, l]) => toast(m, l)));
  } catch (e) { }

  // ── Bell (teacher + student; admin keeps its "Needs attention" bell) ──
  const Bell = {
    items: [], unread: 0, localRead: new Set(),
    key: 'elms_notif_clicked_<?= (int) $_SESSION['user_id'] ?>',
    saveLocal() { try { localStorage.setItem(this.key, JSON.stringify([...this.localRead].slice(-300))); } catch (e) { } },
    async load() {
      try { this.localRead = new Set(JSON.parse(localStorage.getItem(this.key) || '[]')); } catch (e) { }
      try {
        const r = await fetch(API + '?action=notifications', { cache: 'no-store' });
        const d = await r.json(); if (!d.success) return;
        this.items = d.items || []; this.unread = d.unread || 0; this.render();
      } catch (e) { /* offline — keep what we have */ }
    },
    // Unread = the server's count minus the ones opened one by one on this device
    count() {
      const openedHere = this.items.filter(n => n.unread && this.localRead.has(n.id)).length;
      return Math.max(0, this.unread - openedHere);
    },
    renderBadge() {
      const n = this.count(), badge = document.getElementById('notifBadge');
      if (badge) {
        badge.textContent = n > 99 ? '99+' : n;
        badge.classList.toggle('hidden', !n);
        badge.setAttribute('aria-label', n + ' unread notification' + (n === 1 ? '' : 's'));
      }
    },
    render() {
      this.renderBadge();
      const list = document.getElementById('notifList');
      if (!list) return;
      if (!this.items.length) { list.innerHTML = '<div class="notif-empty" style="padding:1.25rem;text-align:center;color:var(--text-muted,#64748b);font-size:.86rem;">No notifications yet.</div>'; return; }
      list.innerHTML = '';
      this.items.forEach(n => {
        const unread = n.unread && !this.localRead.has(n.id);
        const a = document.createElement('a');
        a.className = 'notif-item' + (unread ? ' unread' : ''); a.href = n.link || '#';
        a.style.cssText = 'display:flex;gap:.6rem;text-decoration:none;color:inherit;';
        a.innerHTML = '<div class="notif-dot"></div><div><div class="notif-item-title"></div><div class="notif-item-meta"></div></div>';
        a.querySelector('.notif-item-title').textContent = n.msg;
        a.querySelector('.notif-item-meta').textContent = new Date(n.at.replace(' ', 'T')).toLocaleString([], { month: 'short', day: 'numeric', hour: 'numeric', minute: '2-digit' });
        // Opening one notification marks only that one as read: the number goes down by 1
        a.addEventListener('click', (e) => {
          if (!this.localRead.has(n.id)) {
            this.localRead.add(n.id); this.saveLocal();
            a.classList.remove('unread'); this.renderBadge();
          }
          if (!n.link) e.preventDefault();
        });
        list.appendChild(a);
      });
    },
    async markAll() {
      this.unread = 0; this.items.forEach(n => n.unread = false);
      this.localRead.clear(); this.saveLocal();
      this.render();
      try { await fetch(API + '?action=mark_read&upto=' + last, { method: 'POST' }); } catch (e) { }
    },
  };
  if (ROLE === 'Teacher' || ROLE === 'Student') {
    window.markAllRead = () => Bell.markAll();
    window.ElmsBell = Bell;
    addEventListener('DOMContentLoaded', () => Bell.load());
  }

  // ── Students: in-place page updates ──
  const Soft = (function () {
    // Parts of the page that are swapped in. The attendance page draws its own
    // content (and has its own live loader), so only its subject menu is swapped.
    const TARGETS = PAGE === 'attendance' ? ['#cnav'] : ['.section-panel', '#cnav'];
    const KEEP_CLASSES = ['open', 'show', 'cal-selected', 'is-open', 'sa-flash'];
    let wanted = false, running = false, retry = null, force = false;

    const keyOf = n => {
      if (!n || n.nodeType !== 1) return null;
      if (n.id) return '#' + n.id;
      const d = n.dataset || {};
      const k = d.key || d.id || d.code;
      return k ? n.nodeName + '|' + k : null;
    };
    // Never run scripts that come with the fetched page
    function clean(node) {
      if (node.nodeType === 1 && node.tagName === 'SCRIPT') return document.createComment('script');
      const c = document.importNode(node, true);
      if (c.nodeType === 1) c.querySelectorAll('script').forEach(s => s.replaceWith(document.createComment('script')));
      return c;
    }
    function syncAttrs(o, n) {
      for (const a of [...o.attributes]) {
        if (a.name !== 'class' && a.name !== 'style' && !n.hasAttribute(a.name)) o.removeAttribute(a.name);
      }
      for (const a of [...n.attributes]) {
        if (a.name === 'class' || a.name === 'style') continue;
        if (o.getAttribute(a.name) !== a.value) o.setAttribute(a.name, a.value);
      }
      // classes: the server's, plus open/selected state the page added
      const cls = (n.getAttribute('class') || '').split(/\s+/).filter(Boolean);
      KEEP_CLASSES.forEach(c => { if (o.classList.contains(c) && !cls.includes(c)) cls.push(c); });
      const want = cls.join(' ');
      if ((o.getAttribute('class') || '') !== want) { if (want) o.setAttribute('class', want); else o.removeAttribute('class'); }
      // style: the server's, but keep a rotation the page set (open-list arrows)
      const tf = o.style.transform;
      const ns = n.getAttribute('style');
      if ((o.getAttribute('style') || '') !== (ns || '')) { if (ns !== null) o.setAttribute('style', ns); else o.removeAttribute('style'); }
      if (tf && !o.style.transform) o.style.transform = tf;
    }
    function morph(o, n) {
      if (o.nodeType === 3 || o.nodeType === 8) { if (o.nodeValue !== n.nodeValue) o.nodeValue = n.nodeValue; return; }
      if (o.nodeType !== 1) return;
      if (o.tagName === 'SCRIPT' || o.hasAttribute('data-live-keep')) return;   // page-drawn parts stay as they are
      syncAttrs(o, n);
      if (o.tagName === 'TEXTAREA') return;                                       // never touch typed text
      const oldKids = [...o.childNodes], newKids = [...n.childNodes];
      const byKey = new Map();
      oldKids.forEach(c => { const k = keyOf(c); if (k && !byKey.has(k)) byKey.set(k, c); });
      const used = new Set(), result = [];
      let cursor = 0;
      for (const nc of newKids) {
        let match = null;
        const k = keyOf(nc);
        if (k) {
          const c = byKey.get(k);
          if (c && !used.has(c) && c.nodeName === nc.nodeName) match = c;
        } else {
          for (let i = cursor; i < oldKids.length; i++) {
            const c = oldKids[i];
            if (used.has(c) || keyOf(c)) continue;
            if (c.nodeType === nc.nodeType && c.nodeName === nc.nodeName) { match = c; cursor = i + 1; break; }
          }
        }
        if (match) { used.add(match); morph(match, nc); result.push(match); }
        else result.push(clean(nc));
      }
      oldKids.forEach(c => { if (!used.has(c)) c.remove(); });
      result.forEach((node, i) => { if (o.childNodes[i] !== node) o.insertBefore(node, o.childNodes[i] || null); });
    }

    // Is now a bad moment? (a window is open, or the student is typing in the page)
    function blocked() {
      if (document.hidden || modalOpen()) return true;
      const a = document.activeElement;
      if (a && a !== document.body && /^(INPUT|TEXTAREA|SELECT)$/.test(a.tagName) && !isSearch(a)
          && TARGETS.some(s => { const t = document.querySelector(s); return t && t.contains(a); })) return true;
      // a form in the page with unsaved changes (e.g. Profile)
      for (const s of TARGETS) {
        const t = document.querySelector(s); if (!t) continue;
        for (const f of t.querySelectorAll('input, textarea')) {
          if (isSearch(f) || f.type === 'hidden') continue;
          if (f.type === 'file' ? f.files && f.files.length : (f.type === 'checkbox' || f.type === 'radio') ? f.checked !== f.defaultChecked : f.value !== f.defaultValue) return true;
        }
      }
      return false;
    }
    function afterSwap(doc) {
      // re-apply filters the student set
      ['filterMod', 'filterQuiz', 'filterAssign'].forEach(fn => { try { if (typeof window[fn] === 'function') window[fn](); } catch (e) { } });
      const sj = document.getElementById('sj-q'); if (sj && sj.value) sj.dispatchEvent(new Event('input'));
      if (typeof window.elmsAfterLiveUpdate === 'function') { try { window.elmsAfterLiveUpdate(doc); } catch (e) { console.warn('[live]', e); } }
    }
    async function run() {
      if (running) return;
      if (!force && blocked()) { clearTimeout(retry); retry = setTimeout(run, 1500); return; }
      wanted = false; force = false; running = true;
      try {
        const r = await fetch(location.href, { cache: 'no-store', credentials: 'same-origin', headers: { 'X-Elms-Live': '1' } });
        if (r.headers.get('X-Elms-Kicked') === '1' || /kicked=1/.test(r.url)) { showKickedNotice(); return; }
        if (r.status === 401 || r.status === 403 || /\/login\.php/.test(r.url)) { stopped = true; location.href = '../login.php'; return; }
        if (!r.ok) throw new Error('HTTP ' + r.status);
        const doc = new DOMParser().parseFromString(await r.text(), 'text/html');
        // the page changed shape (e.g. removed from this subject): load it fresh
        if (TARGETS.some(s => !!document.querySelector(s) !== !!doc.querySelector(s))) { pendingReload = true; tryReload(); return; }
        if (blocked()) { wanted = true; return; }   // a window opened while loading — try again after
        TARGETS.forEach(s => { const o = document.querySelector(s), n = doc.querySelector(s); if (o && n) morph(o, n); });
        afterSwap(doc);
      } catch (e) {
        console.warn('[live] in-place update failed', e);
        wanted = true;
      } finally {
        running = false;
        if (wanted && !stopped) { clearTimeout(retry); retry = setTimeout(run, 1500); }
      }
    }
    return {
      request(now) { wanted = true; if (now) force = true; if (running) return; clearTimeout(retry); retry = setTimeout(run, now ? 0 : 150); },
    };
  })();

  // ── Admin sidebar: unread count on "Messages" ──
  async function msgBadgeRefresh() {
    const badges = document.querySelectorAll('[data-msg-badge]');
    if (!badges.length) return;
    try {
      const r = await fetch('../api/contact_messages.php?action=count', { cache: 'no-store', credentials: 'same-origin' });
      const d = await r.json(); if (!d.success) return;
      badges.forEach(b => { b.textContent = d.unread; b.style.display = d.unread ? 'inline-flex' : 'none'; });
    } catch (e) { /* offline — keep what we have */ }
  }

  // ── Poll loop ──
  function schedule() {
    clearTimeout(timer); if (stopped) return;
    const base = document.hidden ? 60000 : (ROLE === 'Teacher' ? 15000 : 5000);   // admins, admission, students: ~5 s
    const wait = failures ? Math.min(60000, base * Math.pow(2, failures - 1)) : base;
    timer = setTimeout(poll, wait);
  }
  async function poll() {
    if (stopped || busy) return; busy = true;
    try {
      const r = await fetch(API + '?action=poll&after=' + last, { cache: 'no-store', credentials: 'same-origin' });
      if (r.headers.get('X-Elms-Kicked') === '1') { showKickedNotice(); return; }
      if (r.status === 401 || r.status === 403) {
        stopped = true;
        location.href = '../login.php';   // session ended / account deactivated: sign in again
        return;
      }
      const d = await r.json();
      failures = 0;
      if (!d.success || !d.enabled) { stopped = !d.enabled; return; }
      const fresh = (d.events || []).filter(ev => !seen.has(ev.id));
      fresh.forEach(ev => seen.add(ev.id));
      last = Math.max(last, d.last || 0);
      if (!fresh.length) return;
      window.dispatchEvent(new CustomEvent('elms:sync', { detail: fresh }));
      // Contact Us inbox changed: keep the sidebar "Messages" badge right on every admin page
      if (fresh.some(ev => ev.kind.startsWith('message.'))) msgBadgeRefresh();
      // System settings saved (Admin → Settings): update names / school year on this page in place
      if (fresh.some(ev => ev.kind === 'settings.changed') && window.ElmsSettings) window.ElmsSettings.refresh();
      const notes = fresh.filter(ev => ev.notify);
      notes.slice(-2).forEach(ev => toast(ev.msg, ev.link));
      if (notes.length > 2) toast((notes.length - 2) + ' more update' + (notes.length > 3 ? 's' : ''), null);
      if (typeof d.unread === 'number' && (ROLE === 'Teacher' || ROLE === 'Student')) Bell.load();
      if (fresh.some(ev => ev.kind === 'account.changed' && ev.self)) { pendingReload = true; tryReload(); return; }
      const relevant = ROLE === 'Student' ? fresh : fresh.filter(affects);
      if (relevant.length) refreshPage(relevant);
    } catch (e) {
      failures++;
      if (failures === 3) bar('Connection lost. Updates will resume when you are back online.', true, () => { failures = 0; poll(); });
    } finally {
      busy = false; schedule();
    }
  }
  addEventListener('online', () => { failures = 0; document.getElementById('lvBar')?.classList.remove('show'); poll(); });
  document.addEventListener('visibilitychange', () => { if (!document.hidden) { tryReload(); poll(); if (ROLE === 'Teacher') kickWatch(); } else schedule(); });
  addEventListener('focusout', () => setTimeout(tryReload, 300));

  /** Fetch this page again and swap the given parts (CSS selectors) in place — no reload. */
  async function swapParts(selectors) {
    const r = await fetch(location.href, { cache: 'no-store', credentials: 'same-origin' });
    if (!r.ok || /\/login\.php/.test(r.url)) throw new Error('page fetch ' + r.status);
    const doc = new DOMParser().parseFromString(await r.text(), 'text/html');
    let n = 0;
    [].concat(selectors).forEach(sel => {
      const fresh = doc.querySelector(sel), cur = document.querySelector(sel);
      if (fresh && cur && cur.innerHTML !== fresh.innerHTML) { cur.innerHTML = fresh.innerHTML; n++; }
    });
    return { doc, changed: n };
  }

  window.ElmsLive = {
    swapParts,
    /** Pages call this to refresh only their data: topics like ['assignment','submission'] */
    onRefresh(topics, fn) { refreshers.push({ topics: [].concat(topics), fn }); },
    markClean() { dirty = false; },
    /** Students: fetch this page again and swap in what changed (no reload) */
    refreshNow() { if (ROLE === 'Student') Soft.request(true); else location.reload(); },
    poll,
    state: () => ({ last, dirty, typing: !!typing(), modal: modalOpen(), failures, stopped }),
  };

  // Built-in refreshers for pages that already load their data with JavaScript
  addEventListener('DOMContentLoaded', () => {
    const has = n => typeof window[n] === 'function';
    if (ROLE === 'Teacher') {
      if (PAGE === 'assignments' && has('loadAssignments')) ElmsLive.onRefresh(['assignment', 'submission', 'grade', 'enrollment', 'class'], () => loadAssignments());
      if (PAGE === 'quizzes' && has('loadQuizzes')) ElmsLive.onRefresh(['quiz', 'grade', 'enrollment', 'class'], () => loadQuizzes());
      // Assignments page also lists the class's quizzes (teacher/assignments.php)
      if (PAGE === 'assignments' && has('loadQuizzes') && document.getElementById('quiz-list-wrap')) ElmsLive.onRefresh(['quiz', 'grade', 'enrollment', 'class'], () => loadQuizzes());
      // My Classes → Modules: the module cards refresh in place
      if (PAGE === 'classes' && has('mlcRefresh') && document.getElementById('mlc-list')) ElmsLive.onRefresh(['assignment', 'quiz', 'submission', 'grade', 'module'], () => mlcRefresh());
      if (PAGE === 'grades' && has('gbLoad')) ElmsLive.onRefresh(['assignment', 'quiz', 'submission', 'grade', 'enrollment', 'class'], () => gbLoad());
    }
    if (ROLE === 'Student' && PAGE === 'attendance' && has('saRefresh')) {
      // the attendance page draws itself from api/student_attendance.php
      ElmsLive.onRefresh(['attendance', 'enrollment', 'class'], () => saRefresh(false));
    }
  });

  if (ROLE === 'Teacher') kickWatch();
  schedule();
})();
</script>