<?php /* body partial — rendered inside a tabbed admin page */ ?>
<style>
  .ann-pager { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: .75rem 1rem; padding: .9rem 1.5rem;
               border-top: 1px solid var(--border-muted, #eef1f5); font-size: .8rem; color: var(--text-secondary, #64748b); }
  .ann-pager[hidden] { display: none; }
  .ann-pages { display: flex; align-items: center; gap: .25rem; flex-wrap: wrap; }
  .ann-pg { min-width: 32px; height: 32px; padding: 0 .55rem; border-radius: 8px; border: 1px solid var(--border, #e2e8f0); background: var(--surface, #fff);
            color: var(--text-primary, #0f172a); font: 600 .78rem/1 'Nunito', sans-serif; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; }
  .ann-pg:hover:not([disabled]):not(.active) { border-color: var(--primary); color: var(--primary); }
  .ann-pg.active { background: var(--primary); border-color: var(--primary); color: #fff; cursor: default; }
  .ann-pg[disabled] { opacity: .4; cursor: default; }
  .ann-pg-gap { padding: 0 .25rem; color: var(--text-muted, #94a3b8); }
  .ann-nomatch { text-align: center; padding: 2rem; color: var(--text-muted, #94a3b8); font-size: .84rem; }
  @media (max-width: 700px) { .ann-pager { justify-content: center; padding: .85rem 1rem; } }
</style>
<div class="section-panel active">
        <?php render_admin_tabs(); ?>

        <!-- Compose Form -->
        <?php if ($canPublish): ?>
        <div class="form-card" style="margin-bottom:1.5rem">
          <div style="font-size:.88rem;font-weight:700;color:var(--text-primary);margin-bottom:1rem"><?= a_icon('bell') ?> New Announcement</div>
          <div class="form-grid">
            <div class="form-group full">
              <label class="form-label">Title *</label>
              <input class="form-input" type="text" id="ann-title" placeholder="e.g. Holiday Notice — March 25">
            </div>
            <div class="form-group full">
              <label class="form-label">Message *</label>
              <textarea class="form-input" id="ann-body" rows="4"
                placeholder="Type your announcement here. Leave course blank to broadcast to everyone."
                style="resize:vertical"></textarea>
            </div>
            <div class="form-group">
              <label class="form-label">Target Subject <span style="color:var(--ui-text-3, #aaa);font-weight:400">(optional — blank = school-wide)</span></label>
              <select class="form-input" id="ann-course">
                <option value="">— School-wide (All Students) —</option>
                <?php foreach ($allCourses as $c): ?>
                  <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['course_code'] . ' — ' . $c['course_name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="form-group">
              <label class="form-label">Target Audience *</label>
              <select class="form-input" id="ann-audience">
                <option value="Everyone">Everyone</option>
                <option value="Students">Students only</option>
                <option value="Teachers">Teachers only</option>
              </select>
            </div>
            <div id="ann-err"
              style="display:none;background:var(--danger-light);border:1px solid rgba(218,54,51,.25);color:var(--danger);font-size:.78rem;font-weight:600;padding:.6rem .9rem;border-radius:var(--radius)"></div>
            <div class="form-actions">
              <button class="btn btn-primary" onclick="sendAnnouncement()" id="ann-send-btn"><?= a_icon('bell') ?> Post Announcement</button>
            </div>
          </div>
        </div>
        <?php else: ?>
        <div class="form-card" style="margin-bottom:1.5rem;color:var(--text-muted);font-size:.84rem;">
          You have view-only access to announcements. Ask the Head Admin for the "Publish Announcements" permission to post.
        </div>
        <?php endif; ?>

        <!-- Announcements List -->
        <div class="table-card">
          <div class="table-header">
            <h3>Posted Announcements</h3>
            <div class="table-actions">
              <input class="search-input" type="text" placeholder="Search..." oninput="AnnPager.filter(this.value)" autocomplete="off">
            </div>
          </div>
          <div id="ann-list-wrap">
            <?php if (empty($allAnnouncements)): ?>
              <div style="text-align:center;padding:2rem;color:var(--ui-text-3, #bbb);font-size:.84rem">
                No announcements posted yet.
              </div>
            <?php else:
              foreach ($allAnnouncements as $a): ?>
                <div class="ann-item" data-id="<?= (int) $a['id'] ?>"
                  data-title="<?= htmlspecialchars((string) $a['title'], ENT_QUOTES) ?>"
                  data-body="<?= htmlspecialchars((string) $a['body'], ENT_QUOTES) ?>"
                  data-course="<?= (int) ($a['course_id'] ?? 0) ?>"
                  data-course-label="<?= htmlspecialchars(trim(($a['course_code'] ?? '') . ' — ' . ($a['course_name'] ?? '')), ENT_QUOTES) ?>"
                  data-audience="<?= htmlspecialchars((string) ($a['target_audience'] ?? 'Everyone'), ENT_QUOTES) ?>"
                  data-author-role="<?= htmlspecialchars((string) ($a['author_role'] ?? ''), ENT_QUOTES) ?>"
                  style="padding:1rem 1.25rem;border-bottom:1px solid var(--border-muted);display:flex;flex-direction:column;gap:.4rem">
                  <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;flex-wrap:wrap">
                    <div>
                      <div style="font-weight:700;font-size:.88rem;color:var(--text-primary)"><?= htmlspecialchars($a['title']) ?></div>
                      <div style="font-size:.78rem;color:var(--text-muted);margin-top:.15rem">
                        <?= a_icon('calendar') ?> <?= $a['posted_at'] ?>
                        &nbsp;·&nbsp; <?= a_icon('user') ?> <?= htmlspecialchars($a['author']) ?>
                        <?php if ($a['course_code']): ?>
                          &nbsp;·&nbsp; <span class="badge-role role-student"><?= htmlspecialchars($a['course_code']) ?></span>
                        <?php else: ?>
                          &nbsp;·&nbsp; <span style="font-size:.68rem;background:var(--primary-light);color:var(--primary);padding:.15rem .5rem;border-radius:100px;font-weight:700">School-wide</span>
                        <?php endif; ?>
                      </div>
                    </div>
                    <?php if ($canPublish): ?>
                    <div style="display:flex;gap:.5rem;flex-wrap:wrap">
                      <button class="btn btn-primary btn-sm" onclick="openAnnEdit(this)"><?= a_icon('edit') ?> Edit</button>
                      <button class="btn btn-danger btn-sm" onclick="deleteAnn(<?= (int) $a['id'] ?>,this)"><?= a_icon('trash') ?> Delete</button>
                    </div>
                    <?php endif; ?>
                  </div>
                  <div style="font-size:.83rem;color:var(--text-secondary);line-height:1.6;white-space:pre-wrap"><?= htmlspecialchars($a['body']) ?></div>
                </div>
              <?php endforeach; endif; ?>
            <div class="ann-nomatch" id="ann-nomatch" hidden>No announcements match your search.</div>
          </div>
          <div class="ann-pager" id="ann-pager" hidden>
            <span id="ann-pager-count" aria-live="polite"></span>
            <nav class="ann-pages" id="ann-pages" aria-label="Announcement pages"></nav>
          </div>
        </div>
      </div>
<script>
  // Posted announcements: search + pagination (client side)
  window.AnnPager = (function () {
    var PER_PAGE = 5, page = 1, query = '';
    var wrap = document.getElementById('ann-list-wrap');
    var pager = document.getElementById('ann-pager');
    var pagesEl = document.getElementById('ann-pages');
    var countEl = document.getElementById('ann-pager-count');
    var noMatch = document.getElementById('ann-nomatch');
    if (!wrap || !pager) return { filter: function () {} };

    function items() { return Array.prototype.slice.call(wrap.querySelectorAll('.ann-item')); }
    function matches() {
      var q = query;
      return items().filter(function (el) { return !q || el.textContent.toLowerCase().indexOf(q) !== -1; });
    }
    function btn(label, target, opts) {
      opts = opts || {};
      var b = document.createElement('button');
      b.type = 'button'; b.className = 'ann-pg' + (opts.active ? ' active' : ''); b.innerHTML = label;
      if (opts.aria) b.setAttribute('aria-label', opts.aria);
      if (opts.active) b.setAttribute('aria-current', 'page');
      if (opts.disabled) b.disabled = true; else if (!opts.active) b.onclick = function () { go(target); };
      return b;
    }
    function render() {
      var all = items(), hit = matches(), total = hit.length;
      var pages = Math.max(1, Math.ceil(total / PER_PAGE));
      if (page > pages) page = pages;
      var from = (page - 1) * PER_PAGE, to = from + PER_PAGE;
      all.forEach(function (el) { el.style.display = 'none'; });
      hit.forEach(function (el, i) { if (i >= from && i < to) el.style.display = ''; });
      noMatch.hidden = !(all.length && total === 0);
      pager.hidden = total === 0;   // the "Showing" line is always visible; page buttons only when there is more than one page
      countEl.textContent = total ? 'Showing ' + (from + 1) + '–' + Math.min(to, total) + ' of ' + total : '';
      pagesEl.innerHTML = '';
      if (total <= PER_PAGE) return;
      pagesEl.appendChild(btn('&lsaquo;', page - 1, { disabled: page === 1, aria: 'Previous page' }));
      var last = 0;
      for (var n = 1; n <= pages; n++) {
        if (n === 1 || n === pages || Math.abs(n - page) <= 1) {
          if (n - last > 1) { var g = document.createElement('span'); g.className = 'ann-pg-gap'; g.textContent = '…'; pagesEl.appendChild(g); }
          pagesEl.appendChild(btn(String(n), n, { active: n === page, aria: 'Page ' + n }));
          last = n;
        }
      }
      pagesEl.appendChild(btn('&rsaquo;', page + 1, { disabled: page === pages, aria: 'Next page' }));
    }
    function go(n) {
      page = n; render();
      var top = document.getElementById('ann-list-wrap');
      if (top && top.getBoundingClientRect().top < 0) top.scrollIntoView({ block: 'start' });
    }
    function filter(q) { query = (q || '').toLowerCase().trim(); page = 1; render(); }
    render();
    return { filter: filter, go: go };
  })();
</script>

<?php if ($canPublish): ?>
<!-- EDIT ANNOUNCEMENT MODAL (moved to <body> by the script below so it always covers the whole page) -->
<div class="modal-overlay" id="annEditModal">
  <div class="modal" style="max-width:560px;">
    <div class="modal-title"><?= a_icon('edit') ?> Edit Announcement</div>
    <input type="hidden" id="ann-edit-id">
    <div class="form-grid">
      <div class="form-group full">
        <label class="form-label" for="ann-edit-title">Title *</label>
        <input class="form-input" type="text" id="ann-edit-title" maxlength="200">
      </div>
      <div class="form-group full">
        <label class="form-label" for="ann-edit-body">Message *</label>
        <textarea class="form-input" id="ann-edit-body" rows="5" style="resize:vertical"></textarea>
      </div>
      <div class="form-group" id="ann-edit-course-group">
        <label class="form-label" for="ann-edit-course">Target Subject</label>
        <select class="form-input" id="ann-edit-course">
          <option value="">— School-wide (All Students) —</option>
          <?php foreach ($allCourses as $c): ?>
            <option value="<?= (int) $c['id'] ?>"><?= htmlspecialchars($c['course_code'] . ' — ' . $c['course_name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group" id="ann-edit-audience-group">
        <label class="form-label" for="ann-edit-audience">Target Audience</label>
        <select class="form-input" id="ann-edit-audience">
          <option value="Everyone">Everyone</option>
          <option value="Students">Students only</option>
          <option value="Teachers">Teachers only</option>
        </select>
      </div>
      <div class="form-group full" id="ann-edit-note" style="display:none;font-size:.78rem;color:var(--text-muted)">
        This post was written by a teacher, so only its title and message can be edited.
      </div>
      <div id="ann-edit-err"
        style="display:none;background:var(--danger-light);border:1px solid rgba(218,54,51,.25);color:var(--danger);font-size:.78rem;font-weight:600;padding:.6rem .9rem;border-radius:var(--radius)"></div>
    </div>
    <div class="modal-actions">
      <button class="btn btn-reset-form" onclick="closeModal('annEditModal')">Cancel</button>
      <button class="btn btn-primary" id="ann-edit-save-btn" onclick="submitAnnEdit(this)">Save Changes</button>
    </div>
  </div>
</div>
<script>
  (function () {
    var m = document.getElementById('annEditModal');
    if (m && m.parentNode !== document.body) document.body.appendChild(m);
  })();

  function annById(id) { return document.getElementById(id); }

  function openAnnEdit(btn) {
    var item = btn && btn.closest ? btn.closest('.ann-item') : null;
    if (!item) return;
    var d = item.dataset;
    var textOnly = d.authorRole === 'Teacher';   // a teacher's post: text only

    annById('ann-edit-id').value = d.id || '';
    annById('ann-edit-title').value = d.title || '';
    annById('ann-edit-body').value = d.body || '';

    var cs = annById('ann-edit-course');
    // A subject that is no longer Active is not in the list: add it, so saving does not
    // silently turn the post into a school-wide one.
    cs.querySelectorAll('option[data-temp]').forEach(function (o) { o.remove(); });
    var cid = d.course && d.course !== '0' ? d.course : '';
    var has = false;
    for (var i = 0; i < cs.options.length; i++) { if (cs.options[i].value === cid) { has = true; break; } }
    if (cid && !has) {
      var o = document.createElement('option');
      o.value = cid; o.textContent = d.courseLabel || ('Subject #' + cid); o.setAttribute('data-temp', '1');
      cs.appendChild(o);
    }
    cs.value = cid;
    annById('ann-edit-audience').value = ['Everyone', 'Students', 'Teachers'].indexOf(d.audience) !== -1 ? d.audience : 'Everyone';

    annById('ann-edit-course-group').style.display = textOnly ? 'none' : '';
    annById('ann-edit-audience-group').style.display = textOnly ? 'none' : '';
    annById('ann-edit-note').style.display = textOnly ? '' : 'none';
    annById('ann-edit-err').style.display = 'none';
    annById('annEditModal').classList.add('show');
  }

  async function submitAnnEdit(btn) {
    var err = annById('ann-edit-err');
    err.style.display = 'none';
    var id = Number(annById('ann-edit-id').value);
    var title = annById('ann-edit-title').value.trim();
    var body = annById('ann-edit-body').value.trim();

    function fail(msg) { err.textContent = msg; err.style.display = 'block'; }
    if (!id) return fail('Could not read the announcement. Please close this and try again.');
    if (!title) return fail('Title is required.');
    if (!body) return fail('Message is required.');
    if (title.length > 200) return fail('Title is too long (200 characters max).');
    if (body.length > 5000) return fail('Message is too long (5,000 characters max).');

    var payload = { id: id, title: title, body: body };
    if (annById('ann-edit-course-group').style.display !== 'none') {
      payload.course_id = annById('ann-edit-course').value;
      payload.target_audience = annById('ann-edit-audience').value;
    }

    if (btn) setBtnLoading(btn, 'Saving...');
    try {
      var res = await fetch('../api/announcements.php?action=update', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      });
      var data = await res.json();
      if (data.success) {
        closeModal('annEditModal');
        showToast('Announcement updated!');
        setTimeout(function () { location.reload(); }, 900);
      } else {
        fail(data.message || 'Could not save the changes.');
        if (btn) clearBtnLoading(btn);
      }
    } catch (e) {
      fail('Network error. Please try again.');
      if (btn) clearBtnLoading(btn);
    }
  }
</script>
<?php endif; ?>