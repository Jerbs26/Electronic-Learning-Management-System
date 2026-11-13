<?php /* body partial — rendered inside a tabbed admin page */ ?>
<style>
  /* ── Dashboard banners (scoped .bn-) ── */
  .bn-form-title { display: flex; align-items: center; gap: .45rem; font-size: .92rem; font-weight: 800; color: var(--text-primary); margin-bottom: 1rem; font-family: 'Nunito', sans-serif; }
  .bn-opt { color: var(--text-muted, #94a3b8); font-weight: 400; }

  /* Image picker */
  .bn-drop {
    position: relative; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: .35rem;
    min-height: 150px; padding: 1.25rem 1rem; text-align: center; cursor: pointer;
    border: 1.5px dashed var(--border, #cbd5e1); border-radius: 12px; background: var(--bg, #f8fafc);
    color: var(--text-secondary, #64748b); font-size: .84rem; transition: border-color .15s, background-color .15s;
  }
  .bn-drop:hover, .bn-drop.drag { border-color: var(--primary, #003087); background: var(--primary-light, #eef3fc); }
  .bn-drop:focus-within { outline: 2px solid var(--primary, #003087); outline-offset: 2px; }
  .bn-drop input[type=file] { position: absolute; inset: 0; width: 100%; height: 100%; opacity: 0; cursor: pointer; }
  .bn-drop .ico { width: 28px; height: 28px; color: var(--primary, #003087); stroke-width: 1.6; }
  .bn-drop strong { color: var(--primary, #003087); font-weight: 700; }
  .bn-drop small { font-size: .74rem; color: var(--text-muted, #94a3b8); }
  .bn-drop.has-img { padding: .5rem; min-height: 0; border-style: solid; }
  .bn-drop-empty { display: contents; }
  .bn-drop.has-img .bn-drop-empty { display: none; }
  .bn-preview { display: none; width: 100%; max-height: 240px; aspect-ratio: 16 / 7; object-fit: cover; border-radius: 8px; }
  .bn-drop.has-img .bn-preview { display: block; }
  .bn-file-name { display: none; margin-top: .45rem; font-size: .76rem; color: var(--text-secondary, #64748b); overflow-wrap: anywhere; }
  .bn-drop.has-img + .bn-file-name { display: block; }

  /* Slides table */
  .bn-table .bn-thumb { width: 96px; aspect-ratio: 16 / 9; object-fit: cover; border-radius: 8px; display: block; background: var(--bg, #f1f5f9); }
  .bn-table .bn-title strong { overflow-wrap: anywhere; }
  .bn-table .bn-caption { font-size: .76rem; color: var(--text-muted, #94a3b8); margin-top: .15rem; overflow-wrap: anywhere; }
  .bn-table .bn-btns { display: flex; gap: .35rem; align-items: center; flex-wrap: nowrap; }
  .bn-table .bn-move { min-width: 32px; justify-content: center; }
  .bn-table .bn-pos { display: none; }

  @media (max-width: 760px) {
    .bn-card-head h3 { display: flex; flex-direction: column; gap: .15rem; }
    .bn-table, .bn-table tbody { display: block; width: 100%; min-width: 0 !important; }
    .bn-table thead { display: none; }
    .bn-table tbody { display: grid; gap: .75rem; padding: .75rem; }
    .bn-table tr { display: grid; grid-template-columns: minmax(0, 1fr); border: 1px solid var(--border, #e2e8f0); border-radius: 12px; overflow: hidden; background: var(--surface, #fff); }
    .bn-table tr:hover td { background: transparent !important; }
    .bn-table td { display: block; padding: 0; border: 0; min-width: 0; }
    .bn-table td.bn-prev { position: relative; }
    .bn-table td.bn-prev .bn-thumb { width: 100%; aspect-ratio: 16 / 7; border-radius: 0; }
    .bn-table .bn-pos {
      display: inline-flex; position: absolute; top: .55rem; left: .55rem; padding: .15rem .55rem; border-radius: 100px;
      background: rgba(15, 23, 42, .72); color: #fff; font-size: .7rem; font-weight: 700;
    }
    .bn-table td.bn-stat { position: absolute; }
    .bn-table tr { position: relative; }
    .bn-table td.bn-stat { top: .55rem; right: .55rem; }
    .bn-table td.bn-stat .badge-status { background: #fff; box-shadow: 0 1px 4px rgba(0, 0, 0, .2); }
    .bn-table td.bn-stat .badge-status.status-active { color: #15803d; }
    .bn-table td.bn-stat .badge-status.status-inactive { color: #64748b; }
    .bn-table td.bn-title { padding: .75rem .9rem .2rem; font-size: .9rem; }
    .bn-table td.bn-order, .bn-table td.bn-act { padding: .5rem .9rem; }
    .bn-table td.bn-order { padding-bottom: 0; }
    .bn-table td.bn-order::before { content: 'Order'; display: block; font-size: .66rem; font-weight: 600; letter-spacing: .05em; text-transform: uppercase; color: var(--text-muted, #94a3b8); margin-bottom: .3rem; }
    .bn-table td.bn-act { padding-bottom: .85rem; }
    .bn-table .bn-btns .btn { flex: 1 1 0; justify-content: center; padding-top: .5rem; padding-bottom: .5rem; }
    .bn-table tr.bn-empty { display: block; border: 0; }
    .bn-table tr.bn-empty td { display: block; }
    .bn-form .form-group.full { grid-column: 1 / -1; }
  }
  @media (min-width: 560px) and (max-width: 760px) {
    .bn-table tbody { grid-template-columns: repeat(2, minmax(0, 1fr)); align-items: start; }
  }
  html[data-theme="dark"] .bn-table tr { background: transparent; }
</style>

<div class="section-panel active">
  <?php render_admin_tabs(); ?>

  <!-- Upload form -->
  <div class="form-card bn-form" style="margin-bottom:1.5rem;">
    <div class="bn-form-title"><?= a_icon('image') ?> Add a Slide</div>
    <div class="form-grid">
      <div class="form-group full">
        <label class="form-label" for="bn-image">Image *</label>
        <label class="bn-drop" id="bn-drop">
          <input type="file" id="bn-image" accept="image/png,image/jpeg,image/webp,image/gif" onchange="previewBannerImage(this)">
          <span class="bn-drop-empty">
            <?= a_icon('image') ?>
            <span><strong>Tap to choose an image</strong> or drag it here</span>
            <small>PNG, JPG, WEBP or GIF · wide images look best (16:7)</small>
          </span>
          <img id="bn-image-preview" class="bn-preview" alt="Selected image preview">
        </label>
        <div class="bn-file-name" id="bn-file-name"></div>
      </div>
      <div class="form-group">
        <label class="form-label" for="bn-title">Title <span class="bn-opt">(optional)</span></label>
        <input class="form-input" type="text" id="bn-title" placeholder="e.g. Free Tuition — SY 2026-2027">
      </div>
      <div class="form-group">
        <label class="form-label" for="bn-caption">Caption <span class="bn-opt">(optional)</span></label>
        <input class="form-input" type="text" id="bn-caption" placeholder="e.g. Enrollment ongoing. Limited slots only!">
      </div>
      <div class="form-group">
        <label class="form-label" for="bn-link">Link URL <span class="bn-opt">(optional — tapping the slide goes here)</span></label>
        <input class="form-input" type="url" inputmode="url" id="bn-link" placeholder="https://...">
      </div>
      <div class="form-group">
        <label class="form-label" for="bn-status">Status</label>
        <select class="form-input" id="bn-status">
          <option value="Active">Active</option>
          <option value="Inactive">Inactive</option>
        </select>
      </div>
      <div id="bn-err" style="display:none;background:var(--danger-light);border:1px solid rgba(218,54,51,.25);color:var(--danger);font-size:.78rem;font-weight:600;padding:.6rem .9rem;border-radius:var(--radius);grid-column:1/-1;"></div>
      <div class="form-actions">
        <button class="btn btn-primary" onclick="submitBanner(this)"><?= a_icon('plus') ?> Add Slide</button>
      </div>
    </div>
  </div>

  <div class="table-card">
    <div class="table-header bn-card-head"><h3>Slides <span style="font-weight:400;color:var(--text-muted);font-size:.78rem;">(top to bottom = slide order)</span></h3></div>
    <table class="bn-table">
      <thead>
        <tr><th>Preview</th><th>Title</th><th>Status</th><th>Order</th><th>Actions</th></tr>
      </thead>
      <tbody id="banners-tbody">
        <?php if (empty($banners)): ?>
          <tr class="bn-empty"><td colspan="5" style="text-align:center;color:var(--ui-text-3, #bbb);padding:2rem;">No slides yet — add one above.</td></tr>
        <?php else: foreach ($banners as $i => $b): ?>
          <tr data-id="<?= (int)$b['id'] ?>">
            <td class="bn-prev">
              <img class="bn-thumb" src="../<?= htmlspecialchars($b['image_path']) ?>" alt="<?= htmlspecialchars($b['title'] ?: 'Slide ' . ($i + 1)) ?>" loading="lazy">
              <span class="bn-pos">Slide <?= $i + 1 ?></span>
            </td>
            <td class="bn-title">
              <strong><?= htmlspecialchars($b['title'] ?: '(no title)') ?></strong>
              <?php if ($b['caption']): ?><div class="bn-caption"><?= htmlspecialchars($b['caption']) ?></div><?php endif; ?>
            </td>
            <td class="bn-stat"><span class="badge-status status-<?= strtolower($b['status']) ?>" id="bn-status-<?= (int)$b['id'] ?>"><?= htmlspecialchars($b['status']) ?></span></td>
            <td class="bn-order">
              <div class="bn-btns">
                <button class="btn btn-reset-form btn-sm bn-move" onclick="moveBanner(<?= (int)$b['id'] ?>,'up',this)" aria-label="Move up" <?= $i === 0 ? 'disabled' : '' ?>>&#8593;</button>
                <button class="btn btn-reset-form btn-sm bn-move" onclick="moveBanner(<?= (int)$b['id'] ?>,'down',this)" aria-label="Move down" <?= $i === count($banners) - 1 ? 'disabled' : '' ?>>&#8595;</button>
              </div>
            </td>
            <td class="bn-act">
              <div class="bn-btns">
                <button class="btn btn-warning btn-sm" onclick="toggleBannerStatus(<?= (int)$b['id'] ?>,'<?= htmlspecialchars($b['status']) ?>',this)">Toggle</button>
                <button class="btn btn-danger btn-sm" onclick="deleteBanner(<?= (int)$b['id'] ?>,this)"><?= a_icon('trash') ?> Delete</button>
              </div>
            </td>
          </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>

<script>
  function previewBannerImage(input) {
    const preview = document.getElementById('bn-image-preview');
    const drop = document.getElementById('bn-drop');
    const name = document.getElementById('bn-file-name');
    if (input.files && input.files[0]) {
      preview.src = URL.createObjectURL(input.files[0]);
      drop.classList.add('has-img');
      name.textContent = input.files[0].name + ' · tap the image to change it';
    } else {
      preview.removeAttribute('src');
      drop.classList.remove('has-img');
      name.textContent = '';
    }
  }
  (function () {
    const drop = document.getElementById('bn-drop');
    if (!drop) return;
    ['dragenter', 'dragover'].forEach(ev => drop.addEventListener(ev, e => { e.preventDefault(); drop.classList.add('drag'); }));
    ['dragleave', 'drop'].forEach(ev => drop.addEventListener(ev, () => drop.classList.remove('drag')));
  })();

  async function submitBanner(btn) {
    const err = document.getElementById('bn-err');
    err.style.display = 'none';
    const imageEl = document.getElementById('bn-image');
    if (!imageEl.files[0]) { err.textContent = 'Please choose an image.'; err.style.display = 'block'; return; }

    const fd = new FormData();
    fd.append('image', imageEl.files[0]);
    fd.append('title', document.getElementById('bn-title').value.trim());
    fd.append('caption', document.getElementById('bn-caption').value.trim());
    fd.append('link_url', document.getElementById('bn-link').value.trim());
    fd.append('status', document.getElementById('bn-status').value);

    setBtnLoading(btn, 'Adding...');
    try {
      const res = await fetch('../api/banners.php?action=create', { method: 'POST', body: fd });
      const data = await res.json();
      if (data.success) {
        showToast('Slide added.');
        setTimeout(() => location.reload(), 800);
      } else {
        err.textContent = data.message; err.style.display = 'block';
        clearBtnLoading(btn);
      }
    } catch (e) {
      err.textContent = 'Network error. Please try again.'; err.style.display = 'block';
      clearBtnLoading(btn);
    }
  }

  async function moveBanner(id, direction, btn) {
    setBtnLoading(btn, '');
    try {
      const res = await fetch('../api/banners.php?action=move', {
        method: 'POST', headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id, direction })
      });
      const data = await res.json();
      if (data.success) location.reload();
      else { showToast(data.message, true); clearBtnLoading(btn); }
    } catch (e) { showToast('Network error.', true); clearBtnLoading(btn); }
  }

  async function toggleBannerStatus(id, currentStatus, btn) {
    const newStatus = currentStatus === 'Active' ? 'Inactive' : 'Active';
    setBtnLoading(btn, 'Saving...');
    try {
      const res = await fetch('../api/banners.php?action=update', {
        method: 'POST', headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id, status: newStatus })
      });
      const data = await res.json();
      if (data.success) {
        const badge = document.getElementById(`bn-status-${id}`);
        badge.textContent = newStatus;
        badge.className = 'badge-status status-' + newStatus.toLowerCase();
        btn.setAttribute('onclick', `toggleBannerStatus(${id},'${newStatus}',this)`);
        showToast('Status updated.');
      } else showToast(data.message, true);
    } catch (e) { showToast('Network error.', true); }
    clearBtnLoading(btn);
  }

  async function deleteBanner(id, btn) {
    if (!await confirmAction('Remove this slide from the dashboard?', { okText: 'Remove' })) return;
    setBtnLoading(btn, 'Removing...');
    try {
      const res = await fetch(`../api/banners.php?action=delete&id=${id}`, { method: 'DELETE' });
      const data = await res.json();
      if (data.success) { showToast('Slide removed.'); document.querySelector(`tr[data-id="${id}"]`).remove(); }
      else { showToast(data.message, true); clearBtnLoading(btn); }
    } catch (e) { showToast('Network error.', true); clearBtnLoading(btn); }
  }
</script>