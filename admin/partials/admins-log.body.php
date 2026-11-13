<?php /* body partial — rendered inside a tabbed admin page */ ?>
<style>
  .log-detail { max-width: 460px !important; }
  .log-detail-head { display: flex; flex-direction: column; align-items: center; text-align: center; gap: .4rem; margin-bottom: 1rem; }
  .log-detail-name { font-family: 'Nunito', sans-serif; font-weight: 800; font-size: 1.05rem; overflow-wrap: anywhere; }
  .log-detail-list { display: grid; border: 1px solid var(--border-muted); border-radius: 12px; overflow: hidden; margin: 0; }
  .log-detail-row { display: grid; grid-template-columns: 6rem minmax(0, 1fr); gap: .75rem; padding: .65rem .9rem; border-bottom: 1px solid var(--border-muted); font-size: .84rem; }
  .log-detail-row:last-child { border-bottom: none; }
  .log-detail-row dt { font-size: .7rem; font-weight: 600; letter-spacing: .05em; text-transform: uppercase; color: var(--text-muted); padding-top: .12rem; }
  .log-detail-row dd { margin: 0; overflow-wrap: anywhere; white-space: pre-line; }

  /* Phones + small tablets: rows become cards (action, admin, details); tap for everything */
  @media (max-width: 760px) {
    .adm-page-header .page-title { font-size: 1.35rem; }
    .log-card .table-header { padding: 1rem; }
    .log-card .table-actions { width: 100%; display: grid; grid-template-columns: minmax(0, 1fr); gap: .5rem; }
    .log-card .table-actions .search-input { width: 100%; min-width: 0; height: 42px; }

    .log-card .table-scroll { overflow: visible; }
    .log-table, .log-table tbody { display: block; width: 100%; min-width: 0 !important; }
    .log-table thead { display: none; }
    .log-table tbody { display: grid; gap: .65rem; padding: .75rem; }
    .log-table tr {
      display: flex; flex-direction: column; align-items: flex-start; gap: .3rem;
      border: 1px solid var(--border); border-radius: 12px; padding: .8rem .9rem; background: var(--surface);
    }
    .log-table tr[data-type] { cursor: pointer; }
    .log-table tr[data-type]:active { background: var(--primary-light); }
    .log-table tr:hover td { background: transparent !important; }
    .log-table td { display: block; padding: 0; border: 0; min-width: 0; max-width: 100%; }
    .log-table td.log-c-action { order: 1; }
    .log-table td.log-c-admin  { order: 2; font-size: .9rem; font-weight: 600; overflow-wrap: anywhere; }
    .log-table td.log-c-details { order: 3; }
    .log-table td.log-c-when   { order: 4; font-size: .74rem; color: var(--text-muted); white-space: normal !important; }
    .log-table td.log-c-target { display: none; }
    .log-table td.log-c-details {
      display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
      font-size: .8rem !important; line-height: 1.45; overflow-wrap: anywhere;
    }
    .log-table td.log-empty { display: block; align-self: stretch; padding: 1.75rem 1rem !important; }
  }
  @media (max-width: 480px) {
    .log-detail-row { grid-template-columns: minmax(0, 1fr); gap: .15rem; }
  }

  /* Pagination */
  .pager { display: flex; align-items: center; justify-content: space-between; gap: .75rem; flex-wrap: wrap; padding: .85rem 1.25rem; border-top: 1px solid var(--border-muted); }
  .pager-info { font-size: .78rem; color: var(--text-muted); }
  .pager-btns { display: flex; align-items: center; gap: .3rem; flex-wrap: wrap; }
  .pg-btn { min-width: 34px; height: 34px; padding: 0 .6rem; border: 1px solid var(--border); border-radius: var(--radius); background: var(--surface); color: var(--text-secondary); font-family: 'Nunito', sans-serif; font-size: .78rem; font-weight: 700; cursor: pointer; }
  .pg-btn:hover:not(:disabled):not(.on) { border-color: var(--primary); color: var(--primary); }
  .pg-btn.on { background: var(--primary); border-color: var(--primary); color: #fff; cursor: default; }
  .pg-btn:disabled { opacity: .45; cursor: not-allowed; }
  .pg-btn:focus-visible { outline: 2px solid var(--primary); outline-offset: 2px; }
  .pg-gap { padding: 0 .2rem; color: var(--text-muted); font-size: .8rem; }
  .pg-cur { display: none; font-size: .78rem; font-weight: 600; color: var(--text-secondary); padding: 0 .4rem; }
  @media (max-width: 560px) {
    .pager { justify-content: center; padding: .85rem 1rem; }
    .pager-info { width: 100%; text-align: center; }
    .pg-num, .pg-gap { display: none; }
    .pg-cur { display: inline; }
  }
</style>
<div class="section-panel active">
  <?php render_admin_tabs(); ?>
  <div class="table-card log-card">
    <div class="table-header">
      <h3>Recent Admin Actions</h3>
      <div class="table-actions">
        <input class="search-input" type="text" id="log-search" placeholder="Search admin name..." oninput="filterLog()" autocomplete="off">
        <select class="search-input" id="log-filter-type" onchange="filterLog()">
          <option value="">All actions</option>
          <?php foreach ($actionCategoryLabels as $cls => $label): ?>
            <option value="<?= $cls ?>"><?= $label ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>
    <table class="log-table">
      <thead>
        <tr>
          <th>When</th>
          <th>Admin</th>
          <th>Action</th>
          <th>Target</th>
          <th>Details</th>
        </tr>
      </thead>
      <tbody id="log-tbody">
        <?php if (empty($logEntries)): ?>
          <tr><td colspan="5" class="log-empty" style="text-align:center;color:var(--ui-text-3, #bbb);padding:2rem;">No activity recorded yet.</td></tr>
        <?php else: foreach ($logEntries as $l):
          $cls = actionBadgeClass($l['action']); ?>
          <tr data-name="<?= htmlspecialchars(strtolower($l['admin_name'])) ?>" data-type="<?= $cls ?>">
            <td class="log-c-when" style="white-space:nowrap;"><?= htmlspecialchars($l['created_at']) ?></td>
            <td class="log-c-admin"><?= htmlspecialchars($l['admin_name']) ?><?php if ($l['admin_role'] !== ''): ?> <span class="badge-role role-<?= htmlspecialchars(strtolower(str_replace(' ', '', $l['admin_role']))) ?>"><?= htmlspecialchars($l['admin_role']) ?></span><?php endif; ?></td>
            <td class="log-c-action"><span class="action-badge <?= $cls ?>"><?= htmlspecialchars(str_replace('_', ' ', $l['action'])) ?></span></td>
            <td class="log-c-target"><?= htmlspecialchars(trim(($l['target_type'] ?? '') . ' #' . ($l['target_id'] ?? ''))) ?></td>
            <td class="log-c-details" style="color:var(--text-muted);font-size:.8rem;"><?= htmlspecialchars($l['details'] ?? '') ?></td>
          </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
    <div class="pager" id="log-pager"></div>
  </div>
</div>

<!-- LOG DETAILS POPUP (phones) -->
<div class="modal-overlay" id="logDetailModal" role="dialog" aria-modal="true" aria-labelledby="logd-title">
  <div class="modal log-detail">
    <div class="log-detail-head">
      <div id="logd-badge"></div>
      <div class="log-detail-name" id="logd-title"></div>
    </div>
    <dl class="log-detail-list" id="logd-list"></dl>
    <div class="modal-actions">
      <button class="btn btn-reset-form" type="button" onclick="closeModal('logDetailModal')">Close</button>
    </div>
  </div>
</div>

<script>
  // Phones: tap a log card to see the full entry.
  (function () {
    const modal = document.getElementById('logDetailModal');
    const mq = window.matchMedia('(max-width: 760px)');
    const esc = s => String(s == null ? '' : s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    const txt = (tr, c) => { const el = tr.querySelector('.' + c); return el ? el.textContent.trim() : ''; };
    document.addEventListener('click', e => {
      const tr = e.target.closest('#log-tbody tr[data-type]');
      if (!tr || !mq.matches) return;
      const badge = tr.querySelector('.log-c-action .action-badge');
      document.getElementById('logd-badge').innerHTML = badge ? badge.outerHTML : '';
      document.getElementById('logd-title').textContent = txt(tr, 'log-c-admin');
      const rows = [['When', txt(tr, 'log-c-when')], ['Target', txt(tr, 'log-c-target') || '—'], ['Details', txt(tr, 'log-c-details') || '—']];
      document.getElementById('logd-list').innerHTML = rows
        .map(r => '<div class="log-detail-row"><dt>' + esc(r[0]) + '</dt><dd>' + esc(r[1]) + '</dd></div>').join('');
      modal.classList.add('show');
    });
    modal.addEventListener('click', e => { if (e.target === modal) closeModal('logDetailModal'); });
    document.addEventListener('keydown', e => { if (e.key === 'Escape' && modal.classList.contains('show')) closeModal('logDetailModal'); });
  })();


  // Simple client-side pagination. Rows hidden by a search/filter carry data-f="1" and are skipped.
  function makePager(rowSel, pagerId, size) {
    const key = 'elms-pager:' + location.pathname + location.search + ':' + pagerId;
    let page = 1;
    try { page = parseInt(sessionStorage.getItem(key), 10) || 1; } catch (e) {}   // stay on the last page after a reload
    const el = document.getElementById(pagerId);
    function nums(cur, pages) {
      const out = [], want = new Set([1, pages, cur - 1, cur, cur + 1]);
      for (let i = 1; i <= pages; i++) {
        if (want.has(i)) out.push(i); else if (out[out.length - 1] !== '…') out.push('…');
      }
      return out;
    }
    function render() {
      const all = [...document.querySelectorAll(rowSel)];
      const rows = all.filter(tr => tr.dataset.f !== '1');
      all.forEach(tr => { if (tr.dataset.f === '1') tr.style.display = 'none'; });
      const total = rows.length, pages = Math.max(1, Math.ceil(total / size));
      page = Math.min(Math.max(1, page), pages);
      try { sessionStorage.setItem(key, String(page)); } catch (e) {}
      const from = (page - 1) * size, to = Math.min(total, from + size);
      rows.forEach((tr, i) => { tr.style.display = (i >= from && i < to) ? '' : 'none'; });
      if (!el) return;
      if (!total) { el.innerHTML = ''; el.style.display = 'none'; return; }
      el.style.display = '';
      let h = '<div class="pager-info">Showing ' + (from + 1) + '–' + to + ' of ' + total + '</div>';
      if (pages > 1) {
        h += '<div class="pager-btns">';
        h += '<button type="button" class="pg-btn" data-pg="' + (page - 1) + '"' + (page === 1 ? ' disabled' : '') + ' aria-label="Previous page">‹ Prev</button>';
        nums(page, pages).forEach(n => {
          h += n === '…' ? '<span class="pg-gap">…</span>'
            : '<button type="button" class="pg-btn pg-num' + (n === page ? ' on' : '') + '" data-pg="' + n + '"' + (n === page ? ' aria-current="page"' : '') + '>' + n + '</button>';
        });
        h += '<span class="pg-cur">Page ' + page + ' of ' + pages + '</span>';
        h += '<button type="button" class="pg-btn" data-pg="' + (page + 1) + '"' + (page === pages ? ' disabled' : '') + ' aria-label="Next page">Next ›</button>';
        h += '</div>';
      }
      el.innerHTML = h;
    }
    if (el) el.addEventListener('click', e => {
      const b = e.target.closest('[data-pg]');
      if (!b || b.disabled) return;
      page = Number(b.dataset.pg); render();
      // back to the very top of the page (whichever element is the scroller)
      document.querySelectorAll('.main-content').forEach(m => m.scrollTo({ top: 0, behavior: 'smooth' }));
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });
    return { render, reset() { page = 1; } };
  }

  const logPager = makePager('#log-tbody tr[data-name]', 'log-pager', 10);
  function filterLog(keepPage) {
    const q = document.getElementById('log-search').value.toLowerCase();
    const type = document.getElementById('log-filter-type').value;
    document.querySelectorAll('#log-tbody tr[data-name]').forEach(tr => {
      const matchQ = !q || tr.dataset.name.includes(q);
      const matchType = !type || tr.dataset.type === type;
      tr.dataset.f = (matchQ && matchType) ? '' : '1';
    });
    if (keepPage !== true) logPager.reset();
    logPager.render();
  }
  filterLog(true);
</script>