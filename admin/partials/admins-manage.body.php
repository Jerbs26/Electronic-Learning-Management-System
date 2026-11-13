<?php /* body partial — rendered inside a tabbed admin page */ ?>
<style>
  .adm-aligned { table-layout: fixed; width: 100%; }
  .adm-aligned td { overflow-wrap: anywhere; vertical-align: middle; }
  .adm-aligned .adm-act { white-space: nowrap; }
  .adm-aligned .adm-act .btn { display: inline-flex; align-items: center; gap: .3rem; vertical-align: middle; }
  .adm-aligned .adm-act .btn + .btn { margin-left: .4rem; }
  @media (max-width: 1100px) { .adm-aligned .adm-act { white-space: normal; } .adm-aligned .adm-act .btn { margin: .15rem .4rem .15rem 0; } .adm-aligned .adm-act .btn + .btn { margin-left: 0; } }
  @media (max-width: 900px) and (min-width: 761px) { .adm-aligned { table-layout: auto; } table.adm-aligned { min-width: 680px; } .adm-aligned td { overflow-wrap: break-word; } }  /* tablets: scroll sideways; phones get cards (below) */

  .adm-row { }
  .adm-detail { max-width: 440px !important; }
  .adm-detail-list { display: grid; border: 1px solid var(--border-muted); border-radius: 12px; overflow: hidden; margin: 0; }
  .adm-detail-row { display: grid; grid-template-columns: 7rem minmax(0, 1fr); gap: .75rem; padding: .65rem .9rem; border-bottom: 1px solid var(--border-muted); font-size: .84rem; }
  .adm-detail-row:last-child { border-bottom: none; }
  .adm-detail-row dt { font-size: .7rem; font-weight: 600; letter-spacing: .05em; text-transform: uppercase; color: var(--text-muted); padding-top: .12rem; }
  .adm-detail-row dd { margin: 0; overflow-wrap: anywhere; }

  /* Phones + small tablets: rows become cards (name + email); tap a card for the rest */
  @media (max-width: 760px) {
    .adm-page-header .page-title { font-size: 1.35rem; }
    .adm-cards-card .table-header { padding: 1rem; }
    .adm-cards-card .table-actions { width: 100%; display: grid; grid-template-columns: minmax(0, 1fr); gap: .5rem; }
    .adm-cards-card .table-actions .search-input { width: 100%; min-width: 0; height: 42px; }
    .adm-cards-card .table-actions .btn { width: 100%; justify-content: center; height: 42px; }

    .adm-cards-card .table-scroll { overflow: visible; }
    table.adm-aligned { min-width: 0 !important; table-layout: auto; }
    .adm-aligned, .adm-aligned tbody { display: block; width: 100%; }
    .adm-aligned colgroup, .adm-aligned thead { display: none; }
    .adm-aligned tbody { display: grid; gap: .65rem; padding: .75rem; }
    .adm-aligned tr {
      display: grid; grid-template-columns: minmax(0, 1fr); gap: .15rem;
      border: 1px solid var(--border); border-radius: 12px; padding: .8rem .9rem; background: var(--surface);
    }
    .adm-aligned tr[data-id] { cursor: pointer; }
    .adm-aligned tr[data-id]:active { background: var(--primary-light); }
    .adm-aligned tr:hover td { background: transparent !important; }
    .adm-aligned td { display: block; padding: 0; border: 0; min-width: 0; }
    .adm-aligned td.adm-c-name { font-size: .95rem; }
    .adm-aligned td.adm-c-email { font-size: .82rem; color: var(--text-muted); overflow-wrap: anywhere; }
    /* everything else lives in the popup */
    .adm-aligned td.adm-c-more, .adm-aligned td.adm-act { display: none; }
    .adm-aligned td.adm-empty { display: block; padding: 1.75rem 1rem !important; }
    .adm-detail .modal-actions { flex-wrap: wrap; }
    .adm-detail .modal-actions .btn { flex: 1 1 auto; justify-content: center; }
    #permsModal .modal { padding: 1.15rem; }
    #permsModal .perm-checklist { gap: .55rem 1rem; }
  }
  @media (max-width: 480px) {
    .adm-detail-row { grid-template-columns: minmax(0, 1fr); gap: .15rem; }
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


  <div class="table-card adm-cards-card">
    <div class="table-header">
      <h3>Regular Admin Accounts</h3>
      <div class="table-actions">
        <input class="search-input" type="search" id="admin-search" placeholder="Search name or email..." oninput="filterAdmins()" autocomplete="off">
        <button class="btn btn-primary" onclick="location.href='accounts.php?tab=create&role=Admin'"><?= a_icon('plus') ?> New Admin Account</button>
      </div>
    </div>
    <table class="adm-aligned">
      <colgroup><col style="width:19%"><col style="width:25%"><col style="width:10%"><col style="width:10%"><col style="width:12%"><col style="width:24%"></colgroup>
      <thead>
        <tr>
          <th>Name</th>
          <th>Email</th>
          <th>Permissions</th>
          <th>Status</th>
          <th>Created</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody id="admins-tbody">
        <?php if (empty($regularAdmins)): ?>
          <tr><td colspan="6" class="adm-empty" style="text-align:center;color:var(--ui-text-3, #bbb);padding:2rem;">No Regular Admin accounts yet.</td></tr>
        <?php else: foreach ($regularAdmins as $a):
          $cnt = $permCounts[(int) $a['id']] ?? 0; ?>
          <tr data-name="<?= htmlspecialchars(strtolower($a['last_name'] . ' ' . $a['first_name'])) ?>" data-username="<?= htmlspecialchars(strtolower((string) ($a['email'] ?? ''))) ?>" data-id="<?= (int)$a['id'] ?>">
            <td class="adm-c-name" data-label="Name"><strong><?= htmlspecialchars($a['last_name'] . ', ' . $a['first_name']) ?></strong></td>
            <td class="adm-c-email" data-label="Email"><?= htmlspecialchars($a['email'] ?? '—') ?></td>
            <td class="adm-c-more" data-label="Permissions"><span class="count-chip<?= $cnt === 0 ? ' zero' : '' ?>" id="perm-count-<?= (int)$a['id'] ?>"><?= $cnt ?> / <?= $permTotal ?></span></td>
            <td class="adm-c-more" data-label="Status"><span class="badge-status status-<?= strtolower($a['status']) ?>" id="status-badge-<?= (int)$a['id'] ?>"><?= $a['status'] ?></span></td>
            <td class="adm-c-more" data-label="Created"><?= htmlspecialchars($a['created_at']) ?></td>
            <td class="adm-act">
              <button class="btn btn-edit btn-sm" onclick="openPermsModal(<?= (int)$a['id'] ?>, <?= htmlspecialchars(json_encode($a['first_name'].' '.$a['last_name']), ENT_QUOTES) ?>)"><?= a_icon('shield') ?> Permissions</button>
              <button class="btn <?= $a['status'] === 'Active' ? 'btn-warning' : 'btn-primary' ?> btn-sm adm-toggle-btn" id="toggle-btn-<?= (int)$a['id'] ?>" onclick="toggleAdminStatus(<?= (int)$a['id'] ?>, this)"><?= $a['status'] === 'Active' ? 'Deactivate' : 'Activate' ?></button>
            </td>
          </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
    <div class="pager" id="admins-pager"></div>
  </div>
</div>

<!-- ADMIN DETAILS POPUP (phones) -->
<div class="modal-overlay" id="admDetailModal" role="dialog" aria-modal="true" aria-labelledby="admd-title">
  <div class="modal adm-detail">
    <div class="modal-title" id="admd-title"></div>
    <dl class="adm-detail-list" id="admd-list"></dl>
    <div class="modal-actions">
      <button class="btn btn-edit" type="button" id="admd-perms"><?= a_icon('shield') ?> Permissions</button>
      <button class="btn btn-warning" type="button" id="admd-toggle">Deactivate</button>
      <button class="btn btn-reset-form" type="button" onclick="closeModal('admDetailModal')">Close</button>
    </div>
  </div>
</div>

<!-- PERMISSIONS MODAL -->
<div class="modal-overlay" id="permsModal">
  <div class="modal" style="max-width:640px;">
    <div class="modal-title"><?= a_icon('shield') ?> Permissions — <span id="perms-modal-name"></span></div>
    <input type="hidden" id="perms-admin-id">
    <div style="display:flex;justify-content:flex-end;margin-bottom:.4rem;">
      <span class="perm-counter" id="modal-perm-counter">0 of <?= $permTotal ?> selected</span>
    </div>
    <div id="perms-grid" style="max-height:50vh;overflow-y:auto;margin-bottom:1rem;">
      <?php foreach ($permCatalog as $module => $perms): ?>
        <div class="perm-module">
          <div class="perm-module-head">
            <div class="perm-module-title"><?= a_icon($moduleIcons[$module] ?? 'shield') ?> <?= htmlspecialchars(ucfirst($module)) ?></div>
            <div class="perm-module-toggles">
              <a onclick="toggleModalModule('<?= htmlspecialchars($module) ?>', true)">Select all</a>
              <a onclick="toggleModalModule('<?= htmlspecialchars($module) ?>', false)">Clear</a>
            </div>
          </div>
          <div class="perm-checklist">
            <?php foreach ($perms as $p): ?>
              <label>
                <input type="checkbox" class="modal-perm-checkbox" data-module="<?= htmlspecialchars($module) ?>" value="<?= htmlspecialchars($p['perm_key']) ?>" onchange="updateModalPermCounter()">
                <?= htmlspecialchars($p['label']) ?>
              </label>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
    <div class="modal-actions">
      <button class="btn btn-reset-form" onclick="closeModal('permsModal')">Cancel</button>
      <button class="btn btn-primary" onclick="savePerms(this)">Save Permissions</button>
    </div>
  </div>
</div>

<script>
  // Phones: tap an admin card to see the full details + actions.
  // Reads the row at click time, so it keeps working after live row updates.
  (function () {
    const modal = document.getElementById('admDetailModal');
    const mq = window.matchMedia('(max-width: 760px)');
    const esc = s => String(s == null ? '' : s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    document.addEventListener('click', e => {
      const tr = e.target.closest('#admins-tbody tr[data-id]');
      if (!tr || !mq.matches || e.target.closest('button, a, input, select')) return;
      const cells = [...tr.querySelectorAll('td[data-label]')];
      const name = cells.find(c => c.dataset.label === 'Name');
      document.getElementById('admd-title').textContent = name ? name.textContent.trim() : 'Admin';
      document.getElementById('admd-list').innerHTML = cells.filter(c => c.dataset.label !== 'Name')
        .map(c => '<div class="adm-detail-row"><dt>' + esc(c.dataset.label) + '</dt><dd>' + c.innerHTML.trim() + '</dd></div>').join('');
      document.getElementById('admd-perms').onclick = () => { closeModal('admDetailModal'); tr.querySelector('.btn-edit')?.click(); };
      const rowToggle = tr.querySelector('.adm-toggle-btn');
      const popToggle = document.getElementById('admd-toggle');
      popToggle.textContent = rowToggle ? rowToggle.textContent.trim() : 'Deactivate';
      popToggle.className = 'btn ' + (rowToggle && rowToggle.classList.contains('btn-primary') ? 'btn-primary' : 'btn-warning');
      popToggle.onclick = () => { closeModal('admDetailModal'); rowToggle?.click(); };
      modal.classList.add('show');
    });
    modal.addEventListener('click', e => { if (e.target === modal) closeModal('admDetailModal'); });
    document.addEventListener('keydown', e => { if (e.key === 'Escape' && modal.classList.contains('show')) closeModal('admDetailModal'); });
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

  const admPager = makePager('#admins-tbody tr[data-id]', 'admins-pager', 10);
  function filterAdmins(keepPage) {
    const q = document.getElementById('admin-search').value.toLowerCase();
    document.querySelectorAll('#admins-tbody tr[data-id]').forEach(tr => {
      const match = !q || tr.dataset.name.includes(q) || tr.dataset.username.includes(q);
      tr.dataset.f = match ? '' : '1';
    });
    if (keepPage !== true) admPager.reset();
    admPager.render();
  }
  filterAdmins(true);

  // Real time: admin rows update in place
  document.addEventListener('DOMContentLoaded', () => {
    if (!window.ElmsLive || !ElmsLive.swapParts) return;
    ElmsLive.onRefresh(['account'], async () => {
      const { changed } = await ElmsLive.swapParts(['#admins-tbody']);
      if (changed) filterAdmins(true);
    });
  });

  function toggleModalModule(module, checked) {
    document.querySelectorAll(`.modal-perm-checkbox[data-module="${module}"]`).forEach(cb => cb.checked = checked);
    updateModalPermCounter();
  }
  function updateModalPermCounter() {
    const all = document.querySelectorAll('.modal-perm-checkbox');
    const checked = document.querySelectorAll('.modal-perm-checkbox:checked');
    document.getElementById('modal-perm-counter').textContent = `${checked.length} of ${all.length} selected`;
  }

  async function openPermsModal(adminId, name) {
    document.getElementById('perms-admin-id').value = adminId;
    document.getElementById('perms-modal-name').textContent = name;
    document.querySelectorAll('.modal-perm-checkbox').forEach(cb => cb.checked = false);
    try {
      const res = await fetch(`../api/accounts.php?action=get_permissions&admin_id=${adminId}`);
      const data = await res.json();
      if (data.success) {
        const granted = new Set(data.granted);
        document.querySelectorAll('.modal-perm-checkbox').forEach(cb => { cb.checked = granted.has(cb.value); });
      }
    } catch (e) { /* leave all unchecked on error */ }
    updateModalPermCounter();
    document.getElementById('permsModal').classList.add('show');
  }
  async function savePerms(btn) {
    const adminId = document.getElementById('perms-admin-id').value;
    const permissions = {};
    let grantedCount = 0;
    document.querySelectorAll('.modal-perm-checkbox').forEach(cb => { permissions[cb.value] = cb.checked; if (cb.checked) grantedCount++; });
    if (btn) setBtnLoading(btn, 'Saving...');
    try {
      const res = await fetch('../api/accounts.php?action=save_permissions', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ admin_id: Number(adminId), permissions })
      });
      const data = await res.json();
      if (data.success) {
        closeModal('permsModal');
        showToast('Permissions updated.');
        const chip = document.getElementById(`perm-count-${adminId}`);
        if (chip) {
          chip.textContent = `${grantedCount} / <?= $permTotal ?>`;
          chip.classList.toggle('zero', grantedCount === 0);
        }
      } else showToast(data.message, true);
    } catch (e) { showToast('Network error. Please try again.', true); }
    if (btn) clearBtnLoading(btn);
  }

  async function toggleAdminStatus(adminId, btn) {
    if (btn) setBtnLoading(btn, 'Updating...');
    try {
      const res = await fetch('../api/accounts.php?action=toggle_status', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: adminId })
      });
      const data = await res.json();
      if (data.success) {
        const badge = document.getElementById(`status-badge-${adminId}`);
        badge.textContent = data.status;
        badge.className = 'badge-status status-' + data.status.toLowerCase();
        showToast(data.message);
        if (btn) {
          const isActive = data.status === 'Active';
          const label = isActive ? 'Deactivate' : 'Activate';
          btn.dataset.originalHtml = label;          // so clearBtnLoading restores the new label
          btn.classList.toggle('btn-warning', isActive);
          btn.classList.toggle('btn-primary', !isActive);
        }
      } else showToast(data.message, true);
    } catch (e) { showToast('Network error. Please try again.', true); }
    if (btn) clearBtnLoading(btn);
  }
</script>