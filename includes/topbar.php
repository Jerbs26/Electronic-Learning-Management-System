<?php
// ============================================================
//  Arandia College eLMS
//  File: includes/topbar.php  (do not rename or move this file)
// ============================================================
?>
<?php $__role = $_SESSION['role'] ?? ''; ?>
<?php
// Topbar avatar: show the user's uploaded profile picture (Student/Teacher
// only) instead of just their initial, and keep it in sync everywhere the
// topbar renders — not just on the profile page itself.
$__topbarPic = null;
if (($__role === 'Student' || $__role === 'Teacher') && isset($conn, $user_id) && $conn && $user_id) {
    $__picRes = $conn->query("SELECT profile_picture FROM users WHERE id=" . (int) $user_id . " LIMIT 1");
    if ($__picRes) {
        $__picRow = $__picRes->fetch_assoc();
        $__topbarPic = $__picRow['profile_picture'] ?? null;
    }
}

if (!function_exists('topbar_avatar_html')) {
    // Renders either the uploaded photo (as an <img>, same '../uploads/...'
    // path convention used on the Profile page) or the user's initial as a
    // safe fallback if there is no picture or the file fails to load. A
    // small chevron badge is appended so it reads as an openable menu.
    function topbar_avatar_html($firstName, $picPath) {
        $letter = topbar_initial($firstName);
        $caret = '<span class="user-btn-caret" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg></span>';
        if ($picPath) {
            $src = htmlspecialchars('../' . $picPath);
            return '<img src="' . $src . '" alt="" style="width:100%;height:100%;border-radius:50%;object-fit:cover;display:block" onerror="this.replaceWith(document.createTextNode(\'' . $letter . '\'))">' . $caret;
        }
        return $letter . $caret;
    }
}
if (!function_exists('topbar_initial')) {
    function topbar_initial($firstName) {
        return htmlspecialchars(strtoupper(substr((string) $firstName, 0, 1)) ?: '?');
    }
}
?>
<style>
  /* Dropdown-chevron badge on the topbar account avatar (Student/Teacher),
     signalling that it opens a menu. Background/border are self-contained
     so this reads correctly whether the topbar itself is light or dark. */
  .user-btn { position: relative; }
  .user-btn-caret {
    position: absolute;
    bottom: -2px;
    right: -2px;
    width: 15px;
    height: 15px;
    border-radius: 50%;
    background: var(--ui-surface, #ffffff);
    color: var(--ui-link, #003087);
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 0 0 2px rgba(0,0,0,0.15), 0 1px 3px rgba(0,0,0,0.3);
    pointer-events: none;
  }
  .user-btn-caret svg { width: 8px; height: 8px; display: block; }
</style>
<?php if ($__role === 'Admission'): ?>
<?php
  // ── Admission top bar: student search · profile menu (no admin bell) ──
  $__tbName = trim(($first_name ?? '') . ' ' . ($last_name ?? ''));
  $__tbInit = strtoupper(substr((string) ($first_name ?? ''), 0, 1) . substr((string) ($last_name ?? ''), 0, 1)) ?: '?';
?>
  <div class="app-body">
    <header class="topbar admin-topbar">
      <button type="button" class="nav-toggle" data-nav-open aria-label="Open menu" aria-controls="appSidebar" aria-expanded="false"><svg viewBox="0 0 24 24" aria-hidden="true"><line x1="4" y1="7" x2="20" y2="7"/><line x1="4" y1="12" x2="20" y2="12"/><line x1="4" y1="17" x2="20" y2="17"/></svg></button>
      <div class="tb-right">
        <form class="tb-search" id="tbSearch" action="students.php" method="get" role="search">
          <span class="tb-search-ico"><?= a_icon('search') ?></span>
          <input type="search" id="tbSearchInput" name="search" placeholder="Search students by name, LRN or email…" autocomplete="off" aria-label="Search students">
          <kbd class="tb-kbd">/</kbd>
        </form>

        <div class="tb-wrap">
          <button class="tb-profile" type="button" onclick="tbToggle('tbProfile', event)" aria-haspopup="true">
            <span class="tb-avatar"><?= htmlspecialchars($__tbInit) ?></span>
            <span class="tb-profile-name"><?= htmlspecialchars($__tbName) ?></span>
            <span class="tb-chev"><?= a_icon('chevron-down') ?></span>
          </button>
          <div class="tb-dd tb-panel tb-menu-panel user-menu" id="tbProfile">
            <div class="user-dd-head">
              <div class="user-dd-name"><?= htmlspecialchars($__tbName) ?></div>
              <div class="user-dd-role">Admission Officer</div>
            </div>
            <div class="user-dd-list">
            <button type="button" class="tb-dd-item tb-dark-toggle" onclick="adminToggleDark(event)" role="switch" aria-checked="false" id="tbDarkToggle">
              <?= a_icon('moon') ?> <span class="tb-dd-t">Dark Mode</span><span class="dm-switch" aria-hidden="true"><span class="dm-switch-knob"></span></span>
            </button>
            <div class="user-dd-divider"></div>
            <a class="tb-dd-item danger" href="../logout.php" onclick="return confirmSignOut(event)"><?= a_icon('logout') ?> <span class="tb-dd-t">Sign Out</span></a>
            </div>
          </div>
        </div>
      </div>
    </header>

<script>
  function adminSyncDark() {
    const on = document.documentElement.getAttribute('data-theme') === 'dark';
    const t = document.getElementById('tbDarkToggle');
    if (t) t.setAttribute('aria-checked', on ? 'true' : 'false');
  }
  function adminToggleDark(e) {
    if (e) e.stopPropagation();
    const root = document.documentElement;
    const on = root.getAttribute('data-theme') !== 'dark';
    if (on) root.setAttribute('data-theme', 'dark'); else root.removeAttribute('data-theme');
    try { localStorage.setItem('elms_theme', on ? 'dark' : 'light'); } catch (err) { }
    adminSyncDark();
  }
  adminSyncDark();
  window.addEventListener('storage', e => {
    if (e.key !== 'elms_theme') return;
    if (e.newValue === 'dark') document.documentElement.setAttribute('data-theme', 'dark');
    else document.documentElement.removeAttribute('data-theme');
    adminSyncDark();
  });
  function tbToggle(id, e) {
    if (e) e.stopPropagation();
    const el = document.getElementById(id);
    if (el) el.classList.toggle('show');
  }
  document.addEventListener('click', e => {
    if (!e.target.closest('.admin-topbar .tb-wrap')) document.querySelectorAll('.admin-topbar .tb-panel').forEach(p => p.classList.remove('show'));
  });
  document.addEventListener('keydown', e => {
    const tag = (e.target.tagName || '').toLowerCase();
    if (e.key === '/' && !['input', 'textarea', 'select'].includes(tag) && !e.target.isContentEditable) {
      e.preventDefault(); const i = document.getElementById('tbSearchInput'); if (i) i.focus();
    }
    if (e.key === 'Escape') document.querySelectorAll('.admin-topbar .tb-dd').forEach(p => p.classList.remove('show'));
  });
</script>

<?php elseif ($__role === 'Admin' || $__role === 'Head Admin'): ?>
<?php
  // ── Admin top bar: search · needs-attention bell · profile menu ──
  require_once __DIR__ . '/admin_attention.php';
  $__tbCan = function (...$keys) use ($conn) {
    if (isHeadAdmin()) return true;
    foreach ($keys as $k) if (adminHasPermission($conn, $k)) return true;
    return false;
  };
  $__tbAttention = adminAttentionItems($conn, $__tbCan);
  $__tbName = trim(($first_name ?? '') . ' ' . ($last_name ?? ''));
  $__tbInit = strtoupper(substr((string) ($first_name ?? ''), 0, 1) . substr((string) ($last_name ?? ''), 0, 1)) ?: '?';
?>
  <div class="app-body">
    <header class="topbar admin-topbar">
      <!-- Phones/tablets: opens the sidebar drawer (assets/js/elms-nav.js) -->
      <button type="button" class="nav-toggle" data-nav-open aria-label="Open menu" aria-controls="appSidebar" aria-expanded="false"><svg viewBox="0 0 24 24" aria-hidden="true"><line x1="4" y1="7" x2="20" y2="7"/><line x1="4" y1="12" x2="20" y2="12"/><line x1="4" y1="17" x2="20" y2="17"/></svg></button>
      <div class="tb-right">
        <div class="tb-search" id="tbSearch">
          <span class="tb-search-ico"><?= a_icon('search') ?></span>
          <input type="search" id="tbSearchInput" placeholder="Search people, sections, subjects…" autocomplete="off"
                 aria-label="Search" aria-controls="tbSearchResults" oninput="tbSearch(this.value)" onkeydown="tbSearchKey(event)">
          <kbd class="tb-kbd">/</kbd>
          <div class="tb-dd tb-results" id="tbSearchResults" role="listbox"></div>
        </div>

        <div class="tb-wrap">
          <button class="tb-icon-btn" type="button" onclick="tbToggle('tbBell', event)" aria-label="Notifications" aria-haspopup="true">
            <?= a_icon('bell') ?>
            <?php if ($__tbAttention): ?><span class="tb-badge" id="tbBellBadge"><?= count($__tbAttention) ?></span><?php endif; ?>
          </button>
          <div class="tb-dd tb-panel" id="tbBell">
            <div class="tb-dd-h tb-dd-h-row">
              <span>Needs attention</span>
              <?php if ($__tbAttention): ?><button type="button" class="tb-mark-all" onclick="tbBellMarkAll(event)">Mark all as read</button><?php endif; ?>
            </div>
            <?php if (!$__tbAttention): ?>
              <div class="tb-dd-empty">All caught up. Nothing needs your attention.</div>
            <?php else: foreach ($__tbAttention as [$tt, $ts, $th, $tone]): ?>
              <a class="tb-dd-item tb-note" href="<?= htmlspecialchars($th) ?>" data-note="<?= htmlspecialchars(md5($tt . '|' . $ts)) ?>">
                <span class="tb-dot <?= $tone ?>"></span>
                <span><span class="tb-dd-t"><?= htmlspecialchars($tt) ?></span><span class="tb-dd-s"><?= htmlspecialchars($ts) ?></span></span>
              </a>
            <?php endforeach; endif; ?>
          </div>
        </div>

        <div class="tb-wrap">
          <button class="tb-profile" type="button" onclick="tbToggle('tbProfile', event)" aria-haspopup="true">
            <span class="tb-avatar"><?= htmlspecialchars($__tbInit) ?></span>
            <span class="tb-profile-name"><?= htmlspecialchars($__tbName) ?></span>
            <span class="tb-chev"><?= a_icon('chevron-down') ?></span>
          </button>
          <div class="tb-dd tb-panel tb-menu-panel user-menu" id="tbProfile">
            <div class="user-dd-head">
              <div class="user-dd-name"><?= htmlspecialchars($__tbName) ?></div>
              <div class="user-dd-role"><?= $__role === 'Head Admin' ? 'Head Administrator' : 'Administrator' ?></div>
            </div>
            <div class="user-dd-list">
            <a class="tb-dd-item" href="settings.php"><?= a_icon('settings') ?> <span class="tb-dd-t">Settings</span></a>
            <button type="button" class="tb-dd-item tb-dark-toggle" onclick="adminToggleDark(event)" role="switch" aria-checked="false" id="tbDarkToggle">
              <?= a_icon('moon') ?> <span class="tb-dd-t">Dark Mode</span><span class="dm-switch" aria-hidden="true"><span class="dm-switch-knob"></span></span>
            </button>
            <div class="user-dd-divider"></div>
            <a class="tb-dd-item danger" href="../logout.php" onclick="return confirmSignOut(event)"><?= a_icon('logout') ?> <span class="tb-dd-t">Sign Out</span></a>
            </div>
          </div>
        </div>
      </div>
    </header>

<script>
  // ── Bell: an item counts as unread until the admin opens it. Saved per admin
  // in this browser; an item comes back as new when its numbers change. ──
  (function () {
    var KEY = 'elms_admin_seen_<?= (int) ($_SESSION['user_id'] ?? 0) ?>';
    function seen() { try { return JSON.parse(localStorage.getItem(KEY) || '[]'); } catch (e) { return []; } }
    function save(list) { try { localStorage.setItem(KEY, JSON.stringify(list.slice(-200))); } catch (e) { } }
    function render() {
      var s = seen(), unread = 0;
      document.querySelectorAll('#tbBell .tb-note').forEach(function (a) {
        var read = s.indexOf(a.dataset.note) !== -1;
        a.classList.toggle('is-read', read);
        if (!read) unread++;
      });
      var b = document.getElementById('tbBellBadge');
      if (b) { b.textContent = unread > 99 ? '99+' : unread; b.style.display = unread ? '' : 'none'; }
    }
    window.tbBellMarkAll = function (e) {
      if (e) e.stopPropagation();
      var s = seen();
      document.querySelectorAll('#tbBell .tb-note').forEach(function (a) { if (s.indexOf(a.dataset.note) === -1) s.push(a.dataset.note); });
      save(s); render();
    };
    document.addEventListener('click', function (e) {
      var a = e.target.closest && e.target.closest('#tbBell .tb-note');
      if (!a) return;
      var s = seen();
      if (s.indexOf(a.dataset.note) === -1) { s.push(a.dataset.note); save(s); }
      render();
    });
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', render); else render();
  })();

  // Dark mode (same saved setting as the Teacher and Student sides)
  function adminSyncDark() {
    const on = document.documentElement.getAttribute('data-theme') === 'dark';
    const t = document.getElementById('tbDarkToggle');
    if (t) t.setAttribute('aria-checked', on ? 'true' : 'false');
  }
  function adminToggleDark(e) {
    if (e) e.stopPropagation();   // keep the menu open so the switch can be seen
    const root = document.documentElement;
    const on = root.getAttribute('data-theme') !== 'dark';
    if (on) root.setAttribute('data-theme', 'dark'); else root.removeAttribute('data-theme');
    try { localStorage.setItem('elms_theme', on ? 'dark' : 'light'); } catch (err) { }
    adminSyncDark();
  }
  adminSyncDark();
  // another tab changed it
  window.addEventListener('storage', e => {
    if (e.key !== 'elms_theme') return;
    if (e.newValue === 'dark') document.documentElement.setAttribute('data-theme', 'dark');
    else document.documentElement.removeAttribute('data-theme');
    adminSyncDark();
  });

  function tbToggle(id, e) {
    if (e) e.stopPropagation();
    document.querySelectorAll('.admin-topbar .tb-panel').forEach(p => { if (p.id !== id) p.classList.remove('show'); });
    document.getElementById(id).classList.toggle('show');
  }
  document.addEventListener('click', e => {
    if (!e.target.closest('.admin-topbar .tb-wrap')) document.querySelectorAll('.admin-topbar .tb-panel').forEach(p => p.classList.remove('show'));
    if (!e.target.closest('#tbSearch')) document.getElementById('tbSearchResults').classList.remove('show');
  });
  document.addEventListener('keydown', e => {
    const tag = (e.target.tagName || '').toLowerCase();
    if (e.key === '/' && !['input', 'textarea', 'select'].includes(tag) && !e.target.isContentEditable) {
      e.preventDefault(); document.getElementById('tbSearchInput').focus();
    }
    if (e.key === 'Escape') {
      document.querySelectorAll('.admin-topbar .tb-dd').forEach(p => p.classList.remove('show'));
    }
  });

  let tbTimer = null, tbIndex = -1;
  function tbEsc(s) { const d = document.createElement('div'); d.textContent = s ?? ''; return d.innerHTML; }
  function tbSearch(q) {
    clearTimeout(tbTimer);
    const box = document.getElementById('tbSearchResults');
    q = q.trim();
    if (q.length < 2) { box.classList.remove('show'); box.innerHTML = ''; return; }
    tbTimer = setTimeout(async () => {
      try {
        const res = await fetch('../api/admin_search.php?q=' + encodeURIComponent(q));
        const d = await res.json();
        tbIndex = -1;
        if (!d.results || !d.results.length) {
          box.innerHTML = `<div class="tb-dd-empty">No results for “${tbEsc(q)}”</div>`;
        } else {
          let html = '', group = '';
          d.results.forEach(r => {
            if (r.group !== group) { group = r.group; html += `<div class="tb-group">${tbEsc(group)}</div>`; }
            html += `<a class="tb-dd-item tb-result" role="option" href="${tbEsc(r.url)}"><span><span class="tb-dd-t">${tbEsc(r.title)}</span><span class="tb-dd-s">${tbEsc(r.sub)}</span></span></a>`;
          });
          box.innerHTML = html;
        }
        box.classList.add('show');
      } catch (e) { /* ignore */ }
    }, 220);
  }
  function tbSearchKey(e) {
    const items = [...document.querySelectorAll('#tbSearchResults .tb-result')];
    if (!items.length) return;
    if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
      e.preventDefault();
      tbIndex = (tbIndex + (e.key === 'ArrowDown' ? 1 : -1) + items.length) % items.length;
      items.forEach((it, i) => it.classList.toggle('hover', i === tbIndex));
    } else if (e.key === 'Enter') {
      e.preventDefault();
      location.href = items[Math.max(tbIndex, 0)].getAttribute('href');
    }
  }
</script>

<?php elseif ($__role === 'Teacher'): ?>



  <!-- MAIN APP BODY -->
  <div class="app-body">

    <!-- TOPBAR -->
    <header class="topbar">
      <!-- Phones/tablets: opens the sidebar drawer (assets/js/elms-nav.js) -->
      <button type="button" class="nav-toggle" data-nav-open aria-label="Open menu" aria-controls="appSidebar" aria-expanded="false"><svg viewBox="0 0 24 24" aria-hidden="true"><line x1="4" y1="7" x2="20" y2="7"/><line x1="4" y1="12" x2="20" y2="12"/><line x1="4" y1="17" x2="20" y2="17"/></svg></button>
      <div class="topbar-left" style="display:none;">SHS &amp; HS &nbsp;/&nbsp; <strong id="topbar-panel-name"><?= htmlspecialchars($pageTitle ?? '') ?></strong></div>
      <div class="topbar-right" style="margin-left:auto;">

        <!-- SEARCH (own students, subjects, assignments, quizzes) -->
        <div class="tt-search" id="ttSearch">
          <span class="tt-search-ico"><?= t_icon('search') ?></span>
          <input type="search" id="ttSearchInput" placeholder="Search students, assignments…" autocomplete="off"
                 aria-label="Search" oninput="ttSearch(this.value)" onkeydown="ttSearchKey(event)">
          <kbd class="tt-kbd">/</kbd>
          <div class="tt-results" id="ttSearchResults" role="listbox"></div>
        </div>

        <!-- NOTIFICATION BELL -->
        <div class="notif-wrap" id="notifWrap">
          <button class="notif-btn" id="notifBtn" onclick="toggleNotif(event)" title="Notifications" aria-label="Notifications">
            <?= t_icon('bell') ?>
            <span class="notif-badge hidden" id="notifBadge">0</span>
          </button>
          <div class="notif-dropdown" id="notifDropdown">
            <div class="notif-dd-head">
              <strong>Notifications</strong>
              <button class="notif-mark-all" onclick="markAllRead()">Mark all as read</button>
            </div>
            <div class="notif-list" id="notifList">
              <div class="notif-empty"><span><?= t_icon('bell-off') ?></span>No notifications yet.</div>
            </div>
            <div class="notif-dd-foot">
              <button onclick="location.href='announcements.php';closeNotif()">View all announcements →</button>
            </div>
          </div>
        </div>

        <!-- ACCOUNT / USER MENU (avatar opens Profile / Dark Mode / Sign Out) -->
        <div class="notif-wrap tt-user" id="userWrap" style="margin-left:1rem;">
          <button type="button" class="tt-who" onclick="toggleTeacherUserMenu(event)" aria-label="My Account">
            <span class="tt-who-name"><?= htmlspecialchars(trim(($first_name ?? '') . ' ' . ($last_name ?? ''))) ?></span>
          </button>
          <button class="btn-icon user-btn" id="userBtn" data-initial="<?= topbar_initial($first_name ?? '') ?>" onclick="toggleTeacherUserMenu(event)" title="My Account" aria-label="My Account">
            <?= topbar_avatar_html($first_name ?? '', $__topbarPic) ?>
          </button>
          <div class="notif-dropdown user-menu" id="teacherUserDropdown" style="width:230px;">
            <div class="user-dd-head">
              <div class="user-dd-name"><?= htmlspecialchars(trim(($first_name ?? '') . ' ' . ($last_name ?? ''))) ?></div>
              <div class="user-dd-role">Teacher</div>
            </div>
            <div class="user-dd-list">
              <button type="button" class="user-dd-item" onclick="location.href='profile.php'">
                <?= t_icon('user') ?> My Profile
              </button>
              <button type="button" class="user-dd-item" onclick="toggleDarkMode()" style="justify-content:space-between;">
                <span style="display:flex; align-items:center; gap:.6rem;">
                  <svg class="icon dm-icon-moon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path></svg>
                  <svg class="icon dm-icon-sun" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="5"></circle><line x1="12" y1="1" x2="12" y2="3"></line><line x1="12" y1="21" x2="12" y2="23"></line><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line><line x1="1" y1="12" x2="3" y2="12"></line><line x1="21" y1="12" x2="23" y2="12"></line><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line></svg>
                  Dark Mode
                </span>
                <span class="dm-switch"><span class="dm-switch-knob"></span></span>
              </button>
              <div class="user-dd-divider"></div>
              <button type="button" class="user-dd-item danger" onclick="confirmSignOut(event)">
                <?= t_icon('logout') ?> Sign Out
              </button>
            </div>
          </div>
        </div>
        <script>
        // Self-contained toggle for the Teacher account/user dropdown above.
        // Uses its own ids/handlers only, so it can't collide with the
        // notification bell's toggleNotif()/closeNotif() logic.
        function toggleTeacherUserMenu(e) {
          if (e) { e.stopPropagation(); e.preventDefault && e.preventDefault(); }
          var dd = document.getElementById('teacherUserDropdown');
          if (!dd) return;
          var isOpen = dd.classList.contains('open');
          dd.classList.toggle('open', !isOpen);
          if (!isOpen) {
            var notifDd = document.getElementById('notifDropdown');
            if (notifDd) notifDd.classList.remove('open');
          }
        }
        document.addEventListener('click', function (e) {
          var wrap = document.getElementById('userWrap');
          var dd = document.getElementById('teacherUserDropdown');
          if (wrap && dd && !wrap.contains(e.target)) {
            dd.classList.remove('open');
          }
        });
        (function () {
          var notifBtnEl = document.getElementById('notifBtn');
          if (notifBtnEl) {
            notifBtnEl.addEventListener('click', function () {
              var dd = document.getElementById('teacherUserDropdown');
              if (dd) dd.classList.remove('open');
            });
          }
        })();
        </script>
      </div>
    </header>
    <script>
    (function () {
      let timer = null, idx = -1;
      const esc = s => { const d = document.createElement('div'); d.textContent = s ?? ''; return d.innerHTML; };
      window.ttSearch = function (q) {
        clearTimeout(timer);
        const box = document.getElementById('ttSearchResults');
        q = q.trim();
        if (q.length < 2) { box.classList.remove('show'); box.innerHTML = ''; return; }
        timer = setTimeout(async () => {
          try {
            const d = await (await fetch('../api/teacher_search.php?q=' + encodeURIComponent(q))).json();
            idx = -1;
            if (!d.results || !d.results.length) box.innerHTML = `<div class="tt-empty">No results for “${esc(q)}”</div>`;
            else {
              let html = '', group = '';
              d.results.forEach(r => {
                if (r.group !== group) { group = r.group; html += `<div class="tt-group">${esc(group)}</div>`; }
                html += `<a class="tt-item" role="option" href="${esc(r.url)}"><span class="tt-item-t">${esc(r.title)}</span><span class="tt-item-s">${esc(r.sub)}</span></a>`;
              });
              box.innerHTML = html;
            }
            box.classList.add('show');
          } catch (e) { }
        }, 220);
      };
      window.ttSearchKey = function (e) {
        const items = [...document.querySelectorAll('#ttSearchResults .tt-item')];
        if (!items.length) return;
        if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
          e.preventDefault();
          idx = (idx + (e.key === 'ArrowDown' ? 1 : -1) + items.length) % items.length;
          items.forEach((it, i) => it.classList.toggle('hover', i === idx));
        } else if (e.key === 'Enter') { e.preventDefault(); location.href = items[Math.max(idx, 0)].getAttribute('href'); }
      };
      document.addEventListener('click', e => { if (!e.target.closest('#ttSearch')) document.getElementById('ttSearchResults').classList.remove('show'); });
      document.addEventListener('keydown', e => {
        const tag = (e.target.tagName || '').toLowerCase();
        if (e.key === '/' && !['input', 'textarea', 'select'].includes(tag) && !e.target.isContentEditable) { e.preventDefault(); document.getElementById('ttSearchInput').focus(); }
        if (e.key === 'Escape') document.getElementById('ttSearchResults').classList.remove('show');
      });
    })();
    </script>

    <!-- Notification data for notification bell: announcements + pending submissions -->
    <script>
    const _notifData = <?php
        $notifItems = [];

        foreach ($myAnnouncements as $a) {
            $notifItems[] = [
                'id'        => 'ann_' . (int)$a['id'],
                'type'      => 'announcement',
                'ref_id'    => (int)$a['id'],
                'title'     => $a['title'],
                'author'    => $a['author'] ?? 'Admin',
                'posted_at' => $a['posted_at'],
                'course'    => $a['course_code'] ?? null,
                'sort_at'   => $a['posted_at_raw'],
            ];
        }

        foreach ($pendingSubmissions as $s) {
            $notifItems[] = [
                'id'        => 'sub_' . (int)$s['submission_id'],
                'type'      => 'submission',
                'ref_id'    => (int)$s['assignment_id'],
                'title'     => $s['title'],
                'author'    => htmlspecialchars($s['last_name'] . ', ' . $s['first_name']),
                'posted_at' => $s['submitted_at'] ? date('M d, Y h:i A', strtotime($s['submitted_at'])) : '',
                'course'    => $s['course_code'] ?? null,
                'sort_at'   => $s['submitted_at'],
            ];
        }

        // Newest first, across both types
        usort($notifItems, fn($a, $b) => strtotime($b['sort_at'] ?? '') - strtotime($a['sort_at'] ?? ''));

        echo json_encode(array_values($notifItems));
    ?>;
    </script>

    
<?php elseif ($__role === 'Student'): ?>



    <!-- MAIN APP -->
    <div class="app-body">
        <header class="topbar">
            <!-- Phones/tablets: opens the sidebar drawer (assets/js/elms-nav.js) -->
            <button type="button" class="nav-toggle" data-nav-open aria-label="Open menu" aria-controls="appSidebar" aria-expanded="false"><svg viewBox="0 0 24 24" aria-hidden="true"><line x1="4" y1="7" x2="20" y2="7"/><line x1="4" y1="12" x2="20" y2="12"/><line x1="4" y1="17" x2="20" y2="17"/></svg></button>
            <div class="breadcrumb" style="display:none;">Student / <strong id="topbar-title"><?= htmlspecialchars($pageTitle ?? '') ?></strong></div>

            <div class="topbar-actions" id="topbarArea">
                <!-- Search (own subjects, modules, assignments, quizzes, announcements) -->
                <div class="st-search" id="stSearch">
                    <span class="st-search-ico">
                        <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                    </span>
                    <input type="search" id="stSearchInput" placeholder="Search subjects, assignments…" autocomplete="off"
                           aria-label="Search" oninput="stSearch(this.value)" onkeydown="stSearchKey(event)">
                    <kbd class="st-kbd">/</kbd>
                    <div class="st-results" id="stSearchResults" role="listbox"></div>
                </div>

                <!-- Notification Bell -->
                <button class="btn-icon" onclick="toggleNotif(event)" title="Notifications" aria-label="Notifications" style="position:relative;">
                    <svg class="icon" viewBox="0 0 24 24">
                        <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                        <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                    </svg>
                    <!-- unread count comes from api/sync.php (includes/live.php) -->
                    <span class="notif-badge hidden" id="notifBadge">0</span>
                </button>

                <!-- Account: name, then avatar on the right (both open the user menu below) -->
                <div class="st-user" style="margin-left:1rem;">
                    <button type="button" class="st-who" onclick="toggleUserMenu(event)" aria-label="My Account">
                        <span class="st-who-name"><?= htmlspecialchars(trim(($first_name ?? '') . ' ' . ($last_name ?? ''))) ?></span>
                    </button>
                    <button class="btn-icon user-btn" id="userBtn" data-initial="<?= topbar_initial($first_name ?? '') ?>" onclick="toggleUserMenu(event)" title="My Account" aria-label="My Account">
                        <?= topbar_avatar_html($first_name ?? '', $__topbarPic) ?>
                    </button>
                </div>
            </div>
        </header>
        <script>
        // Student top-bar search. Own ids/handlers only (stSearch*), so it can't
        // collide with the bell / account menu code in footer.php.
        (function () {
            var timer = null, idx = -1, seq = 0;
            function box() { return document.getElementById('stSearchResults'); }
            function wrap() { return document.getElementById('stSearch'); }
            function closeSearch() {
                var b = box(), w = wrap();
                if (b) b.classList.remove('show');
                if (w) w.classList.remove('st-open');
            }
            function items() { return Array.prototype.slice.call(document.querySelectorAll('#stSearchResults .st-item')); }
            function el(tag, cls, text) {
                var n = document.createElement(tag);
                if (cls) n.className = cls;
                if (text !== undefined) n.textContent = text;
                return n;
            }
            window.stSearch = function (q) {
                clearTimeout(timer);
                var b = box();
                if (!b) return;
                q = String(q || '').trim();
                if (q.length < 2) { seq++; b.classList.remove('show'); b.innerHTML = ''; return; }
                var mine = ++seq;
                timer = setTimeout(function () {
                    fetch('../api/student_search.php?q=' + encodeURIComponent(q), { credentials: 'same-origin' })
                        .then(function (r) { return r.json(); })
                        .then(function (d) {
                            if (mine !== seq) return;           // a newer search is already running
                            var b2 = box();
                            if (!b2) return;
                            idx = -1;
                            b2.innerHTML = '';
                            var rows = (d && d.results) ? d.results : [];
                            if (!rows.length) {
                                b2.appendChild(el('div', 'st-empty', 'No results for “' + q + '”'));
                            } else {
                                var group = '';
                                rows.forEach(function (r) {
                                    if (r.group !== group) { group = r.group; b2.appendChild(el('div', 'st-group', group)); }
                                    var a = el('a', 'st-item');
                                    a.setAttribute('role', 'option');
                                    a.href = r.url;
                                    a.appendChild(el('span', 'st-item-t', r.title));
                                    a.appendChild(el('span', 'st-item-s', r.sub));
                                    b2.appendChild(a);
                                });
                            }
                            b2.classList.add('show');
                        })
                        .catch(function () { /* offline or server error: keep the box as it is */ });
                }, 220);
            };
            window.stSearchKey = function (e) {
                var list = items();
                if (!list.length) return;
                if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
                    e.preventDefault();
                    idx = (idx + (e.key === 'ArrowDown' ? 1 : -1) + list.length) % list.length;
                    list.forEach(function (it, i) { it.classList.toggle('hover', i === idx); });
                    list[idx].scrollIntoView({ block: 'nearest' });
                } else if (e.key === 'Enter') {
                    e.preventDefault();
                    var target = list[Math.max(idx, 0)];
                    if (target) location.href = target.getAttribute('href');
                }
            };
            // Phones: .st-open widens the field over the top bar (see header.php CSS)
            document.addEventListener('focusin', function (e) {
                var w = wrap();
                if (w && e.target.closest && e.target.closest('#stSearch')) w.classList.add('st-open');
            });
            // Small delay so tapping a result still registers before the box closes
            document.addEventListener('focusout', function (e) {
                if (!(e.target.closest && e.target.closest('#stSearch'))) return;
                setTimeout(function () {
                    var w = wrap();
                    if (w && !w.contains(document.activeElement)) closeSearch();
                }, 200);
            });
            document.addEventListener('click', function (e) {
                if (!e.target.closest('#stSearch')) closeSearch();
            });
            document.addEventListener('keydown', function (e) {
                var tag = (e.target.tagName || '').toLowerCase();
                var typing = ['input', 'textarea', 'select'].indexOf(tag) !== -1 || e.target.isContentEditable;
                var inp = document.getElementById('stSearchInput');
                if (e.key === '/' && !typing && !e.ctrlKey && !e.metaKey && !e.altKey && inp) {
                    e.preventDefault();
                    inp.focus();
                }
                if (e.key === 'Escape') {
                    closeSearch();
                    if (inp && document.activeElement === inp) inp.blur();
                }
            });
        })();
        </script>

        <!-- Notification Dropdown -->
        <div id="notifDropdown" class="notif-panel"
            style="width:320px; max-height:400px;"
            onclick="event.stopPropagation()">
            <div class="card" style="padding:0; box-shadow: 0 10px 25px rgba(0,0,0,0.1);">
                <div style="padding:1rem; border-bottom:1px solid var(--border); font-weight:700; display:flex; justify-content:space-between; align-items:center; gap:.5rem;">
                    <span>Notifications</span>
                    <button type="button" onclick="markAllRead && markAllRead()" style="background:none;border:0;color:var(--primary);font:inherit;font-size:.78rem;font-weight:700;cursor:pointer;">Mark all as read</button>
                </div>
                <div style="max-height:300px; overflow-y:auto;" id="notifList" class="stu-notif-list">
                    <?php if (empty($announcements)): ?>
                        <div style="padding:1.5rem; text-align:center; color:var(--text-muted); font-size:0.9rem;">No new
                            updates.</div>
                    <?php else: ?>
                        <?php foreach (array_slice($announcements, 0, 5) as $a): ?>
                            <div style="padding:1rem; border-bottom:1px solid var(--ui-border-soft, #f1f5f9); cursor:pointer;"
                                onclick="goToAnnouncement(<?= $a['id'] ?>)">
                                <div style="font-weight:600; font-size:0.9rem; margin-bottom:0.25rem;">
                                    <?= htmlspecialchars($a['title']) ?></div>
                                <div style="font-size:0.8rem; color:var(--text-muted);"><?= $a['author'] ?> ·
                                    <?= $a['posted_at'] ?></div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <div style="padding:0.75rem; text-align:center; border-top:1px solid var(--ui-border-soft, #f1f5f9);">
                    <button onclick="closeNotifAndGoToPanel()" class="btn btn-outline btn-sm" style="width:100%">View
                        All Announcements</button>
                </div>
            </div>
        </div>

        <!-- User Menu Dropdown (New) -->
        <div id="userDropdown" class="notif-panel user-menu"
            style="width:230px;"
            onclick="event.stopPropagation()">
            <?php // Name + section: shown only on smaller screens, where the top bar hides them ?>
            <div class="user-dd-head">
                <div class="user-dd-name"><?= htmlspecialchars($first_name . ' ' . $last_name) ?></div>
                <div class="user-dd-role"><?= htmlspecialchars(trim(($level ?? '') . (!empty($section) ? ' • ' . $section : ''))) ?></div>
            </div>
            <div class="user-dd-list">
                <button type="button" class="user-dd-item" onclick="location.href='profile.php'; toggleUserMenu(event);">
                    <svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                    My Profile
                </button>
                <button type="button" class="user-dd-item" onclick="toggleDarkMode()" style="justify-content:space-between;">
                    <span style="display:flex; align-items:center; gap:.6rem;">
                        <svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path></svg>
                        Dark Mode
                    </span>
                    <span class="dm-switch"><span class="dm-switch-knob"></span></span>
                </button>
                <div class="user-dd-divider"></div>
                <button type="button" class="user-dd-item danger" onclick="confirmSignOut(event)">
                    <svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                    Sign Out
                </button>
            </div>
        </div>

        
<?php endif; ?>

<!-- ============================================================
     Shared Sign Out confirmation modal.
     Rendered once per page (this file is included once), used by
     the Admin sidebar link, the Teacher topbar button, and the
     Student user-menu button above. Only relies on the
     `.modal-overlay` / `.modal` CSS, which every role's
     header.php defines, so it renders consistently everywhere.
     ============================================================ -->
<div class="modal-overlay hidden" id="signoutConfirmOverlay" onclick="if (event.target === this) closeSignoutConfirm();">
  <div class="modal" style="max-width:380px; padding:1.75rem; text-align:center;">
    <div style="width:52px; height:52px; border-radius:50%; background:rgba(192,57,43,0.12); display:flex; align-items:center; justify-content:center; margin:0 auto 1rem;">
      <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#c0392b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
        <polyline points="16 17 21 12 16 7"></polyline>
        <line x1="21" y1="12" x2="9" y2="12"></line>
      </svg>
    </div>
    <h3 style="margin:0 0 .5rem; font-size:1.1rem; font-weight:700; color:var(--ui-text, #0f172a); font-family:inherit;">Sign Out</h3>
    <p style="margin:0 0 1.5rem; font-size:.9rem; color:var(--ui-text-2, #64748b); font-family:inherit;">Are you sure you want to sign out?</p>
    <div style="display:flex; gap:.75rem; justify-content:center;">
      <button type="button" onclick="closeSignoutConfirm()" style="flex:1; padding:.65rem 1rem; border-radius:8px; border:1px solid var(--ui-border, #e2e8f0); background:var(--ui-surface, #fff); color:var(--ui-text-2, #334155); font-weight:600; cursor:pointer; font-family:inherit; font-size:.9rem;">Cancel</button>
      <button type="button" onclick="doSignOut()" style="flex:1; padding:.65rem 1rem; border-radius:8px; border:none; background:#c0392b; color:#fff; font-weight:600; cursor:pointer; font-family:inherit; font-size:.9rem;">Sign Out</button>
    </div>
  </div>
</div>
<script>
// Shared across Admin, Teacher, and Student layouts.
function confirmSignOut(e) {
  if (e) {
    if (e.preventDefault) e.preventDefault();
    if (e.stopPropagation) e.stopPropagation();
  }
  // Close any open notif/user dropdowns first (only present on some roles,
  // so guard with a null check to avoid errors on Admin/Teacher pages).
  var notifDd = document.getElementById('notifDropdown');
  if (notifDd) notifDd.style.display = 'none';
  var userDd = document.getElementById('userDropdown');
  if (userDd) userDd.style.display = 'none';

  var overlay = document.getElementById('signoutConfirmOverlay');
  if (overlay) {
    overlay.classList.remove('hidden');
    overlay.classList.add('show');
  }
  return false;
}

function closeSignoutConfirm() {
  var overlay = document.getElementById('signoutConfirmOverlay');
  if (overlay) {
    overlay.classList.add('hidden');
    overlay.classList.remove('show');
  }
}

function doSignOut() {
  window.location.href = '../logout.php';
}

// Called from the Profile page right after a successful photo upload, so
// the topbar avatar updates instantly without a full page reload. Safe to
// call on any page/role: it does nothing if there's no #userBtn (Admin).
function updateTopbarAvatar(url) {
  var btn = document.getElementById('userBtn');
  if (!btn || !url) return;
  // Preload before swapping, so a failed load never leaves the button blank —
  // it just keeps showing whatever it was already showing.
  var preload = new Image();
  preload.onload = function () {
    var img = document.createElement('img');
    img.src = url;
    img.alt = '';
    img.style.cssText = 'width:100%;height:100%;border-radius:50%;object-fit:cover;display:block';
    var caret = document.createElement('span');
    caret.className = 'user-btn-caret';
    caret.setAttribute('aria-hidden', 'true');
    caret.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>';
    btn.innerHTML = '';
    btn.appendChild(img);
    btn.appendChild(caret);
  };
  preload.onerror = function () {
    // Leave the current avatar (photo or initial) as-is.
  };
  preload.src = url;
}
</script>