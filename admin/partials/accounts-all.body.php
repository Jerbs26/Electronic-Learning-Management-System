<?php /* body partial — rendered inside a tabbed admin page */ ?>
<style>
  /* ── All accounts: toolbar, table, pager (scoped to .acc-card) ── */
  .acc-card .table-header { align-items: center; }
  .acc-card .table-actions { flex: 1 1 auto; justify-content: flex-end; min-width: 0; }
  .acc-card .acc-search { flex: 1 1 200px; max-width: 260px; min-width: 0; }
  .acc-card .acc-filter { flex: 0 1 auto; min-width: 0; max-width: 220px; }
  .acc-card .table-actions > .search-input,
  .acc-card .table-actions > .btn { height: 38px; }

  .acc-table td.acc-email { overflow-wrap: anywhere; word-break: break-word; }
  .acc-table td.acc-name strong { font-weight: 700; }
  .acc-table .acc-sub, .acc-table .acc-chev { display: none; }
  .acc-table .acc-actions { display: flex; align-items: center; gap: .4rem; flex-wrap: nowrap; }
  .acc-table tr.acc-empty td { text-align: center; color: var(--text-muted, #94a3b8); padding: 2rem 1rem; }

  .acc-pager {
    display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap;
    gap: .75rem 1rem; padding: .9rem 1.5rem; border-top: 1px solid var(--border-muted, #eef1f5);
    font-size: .8rem; color: var(--text-secondary, #64748b);
  }
  .acc-pager[hidden] { display: none; }
  .acc-pager-info { display: flex; align-items: center; gap: .6rem; flex-wrap: wrap; }
  .acc-pager-info select { padding: .3rem .5rem; height: 32px; }
  .acc-pages { display: flex; align-items: center; gap: .25rem; flex-wrap: wrap; }
  .acc-pg {
    min-width: 32px; height: 32px; padding: 0 .55rem; border-radius: 8px;
    border: 1px solid var(--border, #e2e8f0); background: var(--surface, #fff);
    color: var(--text-primary, #0f172a); font: 600 .78rem/1 'Nunito', sans-serif;
    cursor: pointer; display: inline-flex; align-items: center; justify-content: center;
    transition: background-color .15s, border-color .15s, color .15s;
  }
  .acc-pg:hover:not(:disabled):not(.on) { border-color: var(--primary, #003087); color: var(--primary, #003087); }
  .acc-pg.on { background: var(--primary, #003087); border-color: var(--primary, #003087); color: #fff; cursor: default; }
  .acc-pg:disabled { opacity: .45; cursor: not-allowed; }
  .acc-pg:focus-visible { outline: 2px solid var(--primary, #003087); outline-offset: 2px; }
  .acc-gap { padding: 0 .2rem; color: var(--text-muted, #94a3b8); }

  /* Laptops / small desktops: tighter cells so the table fits beside the sidebar */
  @media (max-width: 1400px) {
    .acc-table th, .acc-table td { padding-left: .75rem; padding-right: .75rem; }
  }
  @media (max-width: 1200px) {
    .acc-card .table-actions { justify-content: flex-start; }
    .acc-card .acc-search { max-width: none; }
  }

  /* Tablets + phones: toolbar stacks, rows become cards */
  @media (max-width: 860px) {
    .acc-card .table-header { padding: 1rem; }
    .acc-card .table-header h3 { width: 100%; }
    .acc-card .table-actions { width: 100%; display: grid; grid-template-columns: 1fr 1fr; gap: .5rem; }
    .acc-card .acc-search { grid-column: 1 / -1; max-width: none; width: 100%; }
    .acc-card .acc-filter { max-width: none; width: 100%; }
    .acc-card .acc-create { grid-column: 1 / -1; justify-content: center; width: 100%; }

    .acc-card .table-scroll { overflow-x: visible; }
    .acc-table, .acc-table tbody { display: block; width: 100%; min-width: 0 !important; }
    .acc-table thead { display: none; }
    .acc-table tbody { padding: .75rem; display: grid; gap: .75rem; }
    .acc-table tr.acc-row {
      display: grid; grid-template-columns: 1fr; gap: .1rem;
      border: 1px solid var(--border, #e2e8f0); border-radius: 12px;
      padding: .75rem .9rem; background: var(--surface, #fff);
    }
    .acc-table tr.acc-row[style*="display: none"] { display: none !important; }
    .acc-table tr.acc-row td {
      display: grid; grid-template-columns: 7.5rem minmax(0, 1fr); gap: .5rem; align-items: center;
      padding: .35rem 0; border: 0; font-size: .82rem; min-width: 0;
    }
    .acc-table tr.acc-row td::before {
      content: attr(data-label); font-size: .68rem; font-weight: 600; letter-spacing: .05em;
      text-transform: uppercase; color: var(--text-muted, #94a3b8);
    }
    .acc-table tr.acc-row td > span { justify-self: start; }
    .acc-table tr.acc-row td.acc-name {
      display: block; font-size: .95rem; padding: 0 0 .45rem; margin-bottom: .2rem;
      border-bottom: 1px solid var(--border-muted, #eef1f5);
    }
    .acc-table tr.acc-row td.acc-name::before { display: none; }

    /* Cards show only the name; tapping a card opens the details popup */
    .acc-table tr.acc-row { cursor: pointer; -webkit-tap-highlight-color: transparent; transition: border-color .15s, box-shadow .15s; }
    .acc-table tr.acc-row:hover, .acc-table tr.acc-row:active { border-color: var(--primary, #003087); box-shadow: 0 4px 14px rgba(0, 48, 135, .08); }
    .acc-table tr.acc-row:focus-visible { outline: 2px solid var(--primary, #003087); outline-offset: 2px; }
    .acc-table tr.acc-row td.acc-name {
      display: flex; align-items: center; gap: .6rem; user-select: none;
      border-bottom: 0; margin-bottom: 0; padding: 0;
    }
    .acc-table .acc-name-main { flex: 1 1 auto; min-width: 0; display: flex; flex-direction: column; gap: .3rem; }
    .acc-table .acc-name-text { overflow-wrap: anywhere; }
    .acc-table .acc-sub { display: flex; align-items: center; flex-wrap: wrap; gap: .4rem; font-size: .76rem; font-weight: 400; color: var(--text-secondary, #64748b); }
    .acc-table .acc-chev {
      display: block; width: 20px; height: 20px; flex-shrink: 0; fill: none; stroke: var(--text-muted, #94a3b8);
      stroke-width: 2.2; stroke-linecap: round; stroke-linejoin: round; transform: rotate(-90deg);
    }
    .acc-table tr.acc-row td:not(.acc-name) { display: none !important; }
    .acc-table tr.acc-row td, .acc-table tr.acc-row:hover td { background: transparent !important; }
    .acc-table tr.acc-row td.acc-act { display: block; padding-top: .6rem; }
    .acc-table tr.acc-row td.acc-act::before { display: none; }
    .acc-table .acc-actions .btn { flex: 1 1 0; justify-content: center; padding-top: .45rem; padding-bottom: .45rem; }
    .acc-table tr.acc-empty { display: block; }
    .acc-table tr.acc-empty td { display: block; border: 0; }

    .acc-pager { padding: .85rem 1rem; justify-content: center; }
    .acc-pager-info { justify-content: center; width: 100%; }
    .acc-pages { justify-content: center; width: 100%; }
  }

  @media (min-width: 600px) and (max-width: 860px) {
    .acc-table tbody { grid-template-columns: repeat(2, minmax(0, 1fr)); align-items: start; }
    .acc-table tr.acc-row td { grid-template-columns: 6.5rem minmax(0, 1fr); }
    .acc-table tr.acc-empty { grid-column: 1 / -1; }
  }

  @media (max-width: 400px) {
    .acc-card .table-actions { grid-template-columns: 1fr; }
    .acc-table tr.acc-row td { grid-template-columns: 6.25rem minmax(0, 1fr); }
    .acc-pg { min-width: 30px; height: 30px; }
  }

  html[data-theme="dark"] .acc-table tr.acc-row { background: transparent; }

  /* ── Account details popup (small screens) ── */
  #accInfoModal .modal { max-width: 440px; width: 100%; padding: 0; overflow: hidden; max-height: calc(100dvh - 2rem); display: flex; flex-direction: column; }
  .acc-info-head { display: flex; align-items: flex-start; gap: .75rem; padding: 1.1rem 1.15rem .9rem; border-bottom: 1px solid var(--border-muted, #eef1f5); }
  .acc-info-avatar {
    width: 44px; height: 44px; border-radius: 50%; flex-shrink: 0; display: flex; align-items: center; justify-content: center;
    background: var(--primary-light, #e8eefb); color: var(--primary, #003087); font: 800 .95rem/1 'Nunito', sans-serif;
  }
  .acc-info-title { flex: 1 1 auto; min-width: 0; }
  .acc-info-name { font: 800 1.02rem/1.3 'Nunito', sans-serif; color: var(--text-primary, #0f172a); overflow-wrap: anywhere; }
  .acc-info-badges { display: flex; flex-wrap: wrap; gap: .35rem; margin-top: .35rem; }
  .acc-info-x {
    width: 34px; height: 34px; flex-shrink: 0; border: 0; border-radius: 50%; background: transparent; cursor: pointer;
    color: var(--text-muted, #94a3b8); display: flex; align-items: center; justify-content: center; margin: -.25rem -.35rem 0 0;
  }
  .acc-info-x:hover { background: var(--bg, #f1f5f9); color: var(--text-primary, #0f172a); }
  .acc-info-x svg { width: 18px; height: 18px; fill: none; stroke: currentColor; stroke-width: 2.2; stroke-linecap: round; }
  .acc-info-body { padding: .4rem 1.15rem; overflow-y: auto; }
  .acc-info-row { display: grid; grid-template-columns: 7.25rem minmax(0, 1fr); gap: .6rem; align-items: center; padding: .6rem 0; border-bottom: 1px solid var(--border-muted, #eef1f5); font-size: .85rem; color: var(--text-primary, #0f172a); }
  .acc-info-row:last-child { border-bottom: 0; }
  .acc-info-row dt { font-size: .68rem; font-weight: 600; letter-spacing: .05em; text-transform: uppercase; color: var(--text-muted, #94a3b8); }
  .acc-info-row dd { margin: 0; overflow-wrap: anywhere; }
  .acc-info-row dd > span { display: inline-flex; }
  .acc-info-foot { display: flex; gap: .5rem; padding: .9rem 1.15rem 1.1rem; border-top: 1px solid var(--border-muted, #eef1f5); }
  .acc-info-foot .acc-actions { display: flex; gap: .5rem; width: 100%; }
  .acc-info-foot .btn { flex: 1 1 0; justify-content: center; padding: .6rem 1rem; font-size: .84rem; }
  .acc-info-foot:empty { display: none; }
  #accInfoModal { align-items: center; justify-content: center; padding: 1rem; }
  #accInfoModal .modal { margin: auto; border-radius: 16px; }
</style>

<div class="section-panel active">
        <?php render_admin_tabs(); ?>
        <div class="table-card acc-card">
          <div class="table-header">
            <h3>User Accounts</h3>
            <div class="table-actions">
              <input class="search-input acc-search" type="search" id="search-input" placeholder="Search name, email, ID..."
                oninput="filterTable()" autocomplete="off" aria-label="Search accounts">
              <select class="search-input acc-filter" id="filter-role" onchange="filterTable()" aria-label="Filter by role">
                <option value="">All Roles</option>
                <option value="Student">Student</option>
                <option value="Teacher">Teacher</option>
                <option value="Admission">Admission</option>
                <option value="Admin">Admin</option>
              </select>
              <select class="search-input acc-filter" id="filter-section" onchange="filterTable()" aria-label="Filter by section">
                <option value="">All Sections</option>
                <optgroup label="── Senior High School ──">
                  <?php foreach ($shsSections as $s): ?><option value="<?= htmlspecialchars($s) ?>"><?= htmlspecialchars($s) ?></option><?php endforeach; ?>
                </optgroup>
                <optgroup label="── High School ──">
                  <?php foreach ($hsSections as $s): ?><option value="<?= htmlspecialchars($s) ?>"><?= htmlspecialchars($s) ?></option><?php endforeach; ?>
                </optgroup>
              </select>
              <button class="btn btn-primary acc-create" onclick="location.href='accounts.php?tab=create'"><?= a_icon('plus') ?> Create Account</button>
            </div>
          </div>
          <table class="acc-table">
            <thead>
              <tr>
                <th>Name</th>
                <th>Email</th>
                <th>LRN / Emp. ID</th>
                <th>Role</th>
                <th>Section / Grade</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody id="accounts-tbody">
              <?php if (empty($allAccounts)): ?>
                <tr class="acc-empty">
                  <td colspan="7">No accounts yet.</td>
                </tr>
              <?php else:
                foreach ($allAccounts as $a): ?>
                  <tr class="acc-row" data-name="<?= htmlspecialchars(strtolower($a['last_name'] . ' ' . $a['first_name'])) ?>"
                    data-username="<?= htmlspecialchars(strtolower((string) ($a['email'] ?? ''))) ?>" data-schoolid="<?= htmlspecialchars(strtolower($a['school_id'])) ?>"
                    data-role="<?= htmlspecialchars($a['role']) ?>"
                    data-section="<?= htmlspecialchars($a['section_dept'] ?? '') ?>">
                    <td class="acc-name" data-label="Name">
                      <span class="acc-name-main">
                        <span class="acc-name-text"><strong><?= htmlspecialchars($a['last_name'] . ', ' . $a['first_name']) ?></strong><?= $a['middle_name'] ? ' ' . htmlspecialchars($a['middle_name'][0]) . '.' : '' ?></span>
                        <span class="acc-sub">
                          <span class="badge-role role-<?= strtolower(str_replace(' ', '', $a['role'])) ?>"><?= htmlspecialchars($a['role']) ?></span>
                          <?php if (!empty($a['section_dept'])): ?><span class="acc-sub-sec"><?= htmlspecialchars($a['section_dept']) ?></span><?php endif; ?>
                        </span>
                      </span>
                      <svg class="acc-chev" viewBox="0 0 24 24" aria-hidden="true"><polyline points="6 9 12 15 18 9"/></svg>
                    </td>
                    <td class="acc-email" data-label="Email"><?= htmlspecialchars($a['email'] ?: '—') ?></td>
                    <td data-label="LRN / Emp. ID"><?= htmlspecialchars($a['school_id']) ?></td>
                    <td data-label="Role"><span class="badge-role role-<?= strtolower(str_replace(' ', '', $a['role'])) ?>"><?= htmlspecialchars($a['role']) ?></span></td>
                    <td data-label="Section / Grade"><?= htmlspecialchars($a['section_dept'] ?: '—') ?></td>
                    <td data-label="Status"><span class="badge-status status-<?= strtolower($a['status']) ?>"><?= htmlspecialchars($a['status']) ?></span></td>
                    <td class="acc-act" data-label="Actions">
                      <div class="acc-actions">
                      <?php
                        $__rowCanManage = $isHeadAdmin
                          || ($a['role'] === 'Student' && adminHasPermission($conn, 'manage_students'))
                          || ($a['role'] === 'Teacher' && adminHasPermission($conn, 'manage_teachers'));
                      ?>
                      <?php if ($__rowCanManage): ?>
                        <button class="btn btn-edit btn-sm" data-user="<?= htmlspecialchars(json_encode($a), ENT_QUOTES) ?>" onclick="openEdit(JSON.parse(this.dataset.user))">Edit</button>
                        <button class="btn btn-danger btn-sm" onclick="openDelete(<?= (int) $a['id'] ?>)">Delete</button>
                      <?php else: ?>
                        <span style="color:var(--text-muted);font-size:.78rem;">View only</span>
                      <?php endif; ?>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; endif; ?>
            </tbody>
          </table>
          <div class="acc-pager" id="acc-pager" hidden>
            <div class="acc-pager-info">
              <span id="acc-pager-count" aria-live="polite"></span>
            </div>
            <nav class="acc-pages" id="acc-pages" aria-label="Accounts pages"></nav>
          </div>
        </div>
      </div>

<div class="modal-overlay" id="accInfoModal" role="dialog" aria-modal="true" aria-labelledby="accInfoName">
  <div class="modal">
    <div class="acc-info-head">
      <div class="acc-info-avatar" id="accInfoAvatar"></div>
      <div class="acc-info-title">
        <div class="acc-info-name" id="accInfoName"></div>
        <div class="acc-info-badges" id="accInfoBadges"></div>
      </div>
      <button type="button" class="acc-info-x" id="accInfoClose" aria-label="Close"><svg viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>
    </div>
    <dl class="acc-info-body" id="accInfoBody"></dl>
    <div class="acc-info-foot" id="accInfoFoot"></div>
  </div>
</div>
<script>
  // ── All accounts: filter + pagination (client side) ─────────────
  (function () {
    const tbody = document.getElementById('accounts-tbody');
    const pager = document.getElementById('acc-pager');
    const pagesEl = document.getElementById('acc-pages');
    const countEl = document.getElementById('acc-pager-count');
    if (!tbody || !pager) return;

    const size = 10;   // fixed rows per page
    let page = 1;
    try { const p = parseInt(new URLSearchParams(location.search).get('page'), 10); if (p > 0) page = p; } catch (e) {}

    const rows = () => Array.from(tbody.querySelectorAll('tr.acc-row'));
    let matched = [];

    function noMatchRow(show) {
      let r = tbody.querySelector('tr.acc-nomatch');
      if (show && !r) {
        r = document.createElement('tr');
        r.className = 'acc-empty acc-nomatch';
        r.innerHTML = '<td colspan="7">No accounts match your search or filters.</td>';
        tbody.appendChild(r);
      }
      if (r) r.style.display = show ? '' : 'none';
    }

    function btn(label, target, opts) {
      const b = document.createElement('button');
      b.type = 'button';
      b.className = 'acc-pg' + (opts && opts.on ? ' on' : '');
      b.innerHTML = label;
      if (opts && opts.aria) b.setAttribute('aria-label', opts.aria);
      if (opts && opts.on) b.setAttribute('aria-current', 'page');
      if (opts && opts.disabled) b.disabled = true;
      else if (!(opts && opts.on)) b.addEventListener('click', () => go(target, true));
      return b;
    }

    function pageList(cur, total) {
      // 1 … 4 5 6 … 20 (fewer on phones)
      const span = window.matchMedia('(max-width: 480px)').matches ? 1 : 2;
      const out = [];
      for (let i = 1; i <= total; i++) {
        if (i === 1 || i === total || (i >= cur - span && i <= cur + span)) out.push(i);
        else if (out[out.length - 1] !== '…') out.push('…');
      }
      return out;
    }

    function render() {
      const total = matched.length;
      const pages = Math.max(1, Math.ceil(total / size));
      if (page > pages) page = pages;
      if (page < 1) page = 1;
      const start = (page - 1) * size, end = start + size;

      const visible = new Set(matched.slice(start, end));
      rows().forEach(tr => { tr.style.display = visible.has(tr) ? '' : 'none'; });
      noMatchRow(rows().length > 0 && total === 0);

      pager.hidden = rows().length === 0;
      countEl.textContent = total
        ? 'Showing ' + (start + 1) + '–' + Math.min(end, total) + ' of ' + total + (total === rows().length ? '' : ' (filtered from ' + rows().length + ')')
        : 'No results';

      pagesEl.innerHTML = '';
      if (pages > 1) {
        pagesEl.appendChild(btn('&lsaquo;', page - 1, { aria: 'Previous page', disabled: page === 1 }));
        pageList(page, pages).forEach(n => {
          if (n === '…') { const s = document.createElement('span'); s.className = 'acc-gap'; s.textContent = '…'; pagesEl.appendChild(s); }
          else pagesEl.appendChild(btn(String(n), n, { on: n === page, aria: 'Page ' + n }));
        });
        pagesEl.appendChild(btn('&rsaquo;', page + 1, { aria: 'Next page', disabled: page === pages }));
      }
    }

    function syncUrl() {
      try {
        const u = new URL(location.href);
        if (page > 1) u.searchParams.set('page', page); else u.searchParams.delete('page');
        history.replaceState(history.state, '', u);
      } catch (e) {}
    }

    function go(p, scroll) {
      page = p;
      render();
      syncUrl();
      if (scroll) {
        const card = document.querySelector('.acc-card');
        if (card && card.getBoundingClientRect().top < 0) card.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }
    }

    function filter(keepPage) {
      const q = (document.getElementById('search-input').value || '').trim().toLowerCase();
      const role = document.getElementById('filter-role').value;
      const section = document.getElementById('filter-section').value;
      matched = rows().filter(tr => {
        const mQ = !q || (tr.dataset.name || '').includes(q) || (tr.dataset.username || '').includes(q) || (tr.dataset.schoolid || '').includes(q);
        const mR = !role || tr.dataset.role === role;
        const mS = !section || tr.dataset.section === section;
        return mQ && mR && mS;
      });
      if (!keepPage) page = 1;
      render();
      syncUrl();
    }

    let mqTimer;
    window.addEventListener('resize', () => { clearTimeout(mqTimer); mqTimer = setTimeout(render, 150); });

    window.AccountsPager = { filter: () => filter(false), go };

    // ── Small screens: tap a card to open the account details popup ──
    const cardMq = window.matchMedia('(max-width: 860px)');
    const modal = document.getElementById('accInfoModal');
    const mBody = document.getElementById('accInfoBody');
    const mFoot = document.getElementById('accInfoFoot');
    let lastRow = null;

    function setupCards() {
      const on = cardMq.matches;
      rows().forEach(tr => {
        if (on) { tr.setAttribute('role', 'button'); tr.setAttribute('tabindex', '0'); tr.setAttribute('aria-haspopup', 'dialog'); }
        else { tr.removeAttribute('role'); tr.removeAttribute('tabindex'); tr.removeAttribute('aria-haspopup'); }
      });
      if (!on) closeInfo();
    }

    function openInfo(tr) {
      const nameEl = tr.querySelector('.acc-name-text');
      const name = nameEl ? nameEl.textContent.trim() : '';
      document.getElementById('accInfoName').textContent = name;
      const parts = name.split(',');
      const ini = ((parts[1] || '').trim().charAt(0) + (parts[0] || '').trim().charAt(0)).toUpperCase();
      document.getElementById('accInfoAvatar').textContent = ini || '?';

      const badges = document.getElementById('accInfoBadges');
      badges.innerHTML = '';
      ['td[data-label="Role"] .badge-role', 'td[data-label="Status"] .badge-status'].forEach(sel => {
        const b = tr.querySelector(sel); if (b) badges.appendChild(b.cloneNode(true));
      });

      mBody.innerHTML = '';
      tr.querySelectorAll('td[data-label]').forEach(td => {
        if (td.classList.contains('acc-name') || td.classList.contains('acc-act')) return;
        const row = document.createElement('div');
        row.className = 'acc-info-row';
        const dt = document.createElement('dt'); dt.textContent = td.dataset.label;
        const dd = document.createElement('dd'); dd.innerHTML = td.innerHTML.trim() || '—';
        row.append(dt, dd);
        mBody.appendChild(row);
      });

      const act = tr.querySelector('td.acc-act .acc-actions');
      mFoot.innerHTML = act ? act.outerHTML : '';

      lastRow = tr;
      modal.classList.add('show');
      document.documentElement.style.overflow = 'hidden';
      setTimeout(() => document.getElementById('accInfoClose').focus(), 30);
    }

    function closeInfo() {
      if (!modal.classList.contains('show')) return;
      modal.classList.remove('show');
      document.documentElement.style.overflow = '';
      if (lastRow && cardMq.matches) lastRow.focus({ preventScroll: true });
    }

    tbody.addEventListener('click', e => {
      if (!cardMq.matches) return;
      if (e.target.closest('button, a, input, select')) return;
      const tr = e.target.closest('tr.acc-row');
      if (tr) openInfo(tr);
    });
    tbody.addEventListener('keydown', e => {
      if (!cardMq.matches || (e.key !== 'Enter' && e.key !== ' ')) return;
      const tr = e.target.closest('tr.acc-row');
      if (tr && e.target === tr) { e.preventDefault(); openInfo(tr); }
    });
    document.getElementById('accInfoClose').addEventListener('click', closeInfo);
    modal.addEventListener('click', e => { if (e.target === modal) closeInfo(); });
    document.addEventListener('keydown', e => { if (e.key === 'Escape') closeInfo(); });
    // Edit / Delete inside the popup: close it first, then the button's own action runs
    mFoot.addEventListener('click', e => { if (e.target.closest('button')) { lastRow = null; closeInfo(); } }, true);

    (cardMq.addEventListener ? cardMq.addEventListener('change', setupCards) : cardMq.addListener(setupCards));
    setupCards();

    // Opened from the top-bar search: prefill the account search box
    const q = new URLSearchParams(location.search).get('search');
    const box = document.getElementById('search-input');
    if (q && box) box.value = q;
    filter(true);
  })();
</script>