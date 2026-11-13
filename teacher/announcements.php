<?php
// ============================================================
//  Arandia College eLMS — Teacher: Announcements
//  File: teacher/announcements.php
//  Write: one announcement to one or more of the teacher's subjects
//         (api/announcements.php?action=create, one call per subject).
//  Read:  "Sent by you" (can delete) and "From the school" (admin posts
//         for Everyone / Teachers, read-only). Both come from
//         $myAnnouncements in _init.php.
// ============================================================
require __DIR__ . '/_init.php';
$currentPage = 'announcements.php';
$pageTitle = 'Announcements';

// One checkbox per subject (a subject taught in two sections is posted once)
$annSubjects = [];
foreach ($mySubjects as $s) {
    $cid = (int) $s['course_id'];
    $annSubjects[$cid] = $annSubjects[$cid] ?? ['code' => $s['course_code'], 'name' => $s['course_name'], 'sections' => []];
    $annSubjects[$cid]['sections'][] = $s['section'];
}
$mine = $school = [];
foreach ($myAnnouncements as $a) {
    if ((int) ($a['author_id'] ?? 0) === (int) $user_id) $mine[] = $a; else $school[] = $a;
}

require __DIR__ . '/../includes/header.php';
?>
<style>
  .an { --n-soft: #eef3ff; --n-strong: #003087; --n-line: rgba(15, 23, 42, .08); }
  html[data-theme="dark"] .an { --n-soft: rgba(143, 176, 255, .12); --n-strong: #c7d7ff; --n-line: rgba(255, 255, 255, .08); }
  .an-head { margin-bottom: 1.1rem; }
  .an-title { font-family: 'Nunito', sans-serif; font-size: 1.6rem; font-weight: 900; color: var(--text-dark); line-height: 1.2; }
  .an-sub { font-size: .88rem; color: var(--text-light); margin-top: .3rem; }
  .an-grid { display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 340px); gap: 1.25rem; align-items: start; }
  .an-card { background: var(--white); border-radius: 16px; box-shadow: 0 2px 14px rgba(0, 0, 0, .06); padding: 1.2rem 1.3rem; }
  .an-card h2 { font-family: 'Nunito', sans-serif; font-size: 1rem; font-weight: 900; color: var(--text-dark); margin: 0 0 .9rem; }
  .an-subjects { border: 1px solid var(--n-line); border-radius: 10px; padding: .5rem .8rem; max-height: 180px; overflow-y: auto; background: var(--white); }
  .an-subjects label { display: flex; align-items: center; gap: .55rem; font-size: .84rem; color: var(--text-dark); padding: .3rem 0; cursor: pointer; }
  .an-subjects label.all { font-weight: 800; border-bottom: 1px solid var(--n-line); padding-bottom: .45rem; margin-bottom: .25rem; }
  .an-subjects small { color: var(--text-light); }
  .an-hint { font-size: .76rem; color: var(--text-light); margin-top: .35rem; }
  .an-card .form-grp { margin-bottom: 1rem; }
  .an-err { background: rgba(192, 57, 43, .08); color: #c0392b; font-size: .8rem; font-weight: 600; padding: .6rem .9rem; border-radius: 8px; display: none; margin-bottom: .8rem; }
  .an-actions { display: flex; justify-content: flex-end; }
  .an-listhead { display: flex; justify-content: space-between; align-items: center; gap: .75rem; flex-wrap: wrap; margin: 1.5rem 0 .8rem; }
  .an-listhead h2 { margin: 0; font-family: 'Nunito', sans-serif; font-size: 1.05rem; font-weight: 900; color: var(--text-dark); }
  .an-listhead select { height: 40px; border-radius: 10px; border: 1px solid var(--n-line); background: var(--white); color: var(--text-dark); font: inherit; font-size: .85rem; padding: 0 .8rem; }
  .an .ann-item { background: var(--white); border-radius: 14px; box-shadow: 0 2px 12px rgba(0, 0, 0, .05); padding: 1rem 1.2rem; margin-bottom: .7rem; border-left: 3px solid var(--n-strong); }
  .an .ann-item-title { font-weight: 800; color: var(--text-dark); font-size: .95rem; }
  .an .ann-item-body { font-size: .86rem; color: var(--text-dark); opacity: .85; margin: .3rem 0 .6rem; white-space: pre-line; overflow-wrap: anywhere; }
  .an .ann-item-meta { display: flex; align-items: center; gap: .6rem; flex-wrap: wrap; font-size: .76rem; color: var(--text-light); }
  .an .ann-item-meta button { background: none; border: 0; color: #c0392b; font: inherit; font-weight: 700; cursor: pointer; padding: .2rem .3rem; margin-left: auto; border-radius: 6px; }
  .an .ann-item-meta button:hover { background: rgba(192, 57, 43, .08); }
  .an .ann-item-meta .an-edit { color: var(--n-strong); }
  .an .ann-item-meta .an-edit:hover { background: var(--n-soft); }
  .an .ann-item-meta .an-edit + button { margin-left: 0; }
  .an-school { padding: .8rem 0; border-top: 1px solid var(--n-line); }
  .an-school:first-of-type { border-top: 0; padding-top: 0; }
  .an-school b { display: block; font-size: .88rem; color: var(--text-dark); }
  .an-school p { font-size: .82rem; color: var(--text-dark); opacity: .8; margin: .25rem 0; white-space: pre-line; overflow-wrap: anywhere; }
  .an-school span { font-size: .74rem; color: var(--text-light); }
  .an-empty { text-align: center; padding: 2rem 1rem; color: var(--text-light); font-size: .86rem; }
  .an-empty strong { display: block; color: var(--text-dark); margin-bottom: .2rem; }
  @media (max-width: 1000px) { .an-grid { grid-template-columns: minmax(0, 1fr); } }
</style>

<div class="section-panel active an">
  <div class="an-head">
    <div class="an-title">Announcements</div>
  </div>

  <div class="an-grid">
    <div>
      <?php if (empty($annSubjects)): ?>
        <div class="an-card"><div class="an-empty"><strong>No subjects yet</strong>You can post once the admin assigns you a subject.</div></div>
      <?php else: ?>
        <section class="an-card" aria-labelledby="an-new">
          <h2 id="an-new">New announcement</h2>
          <div id="ann-err" class="an-err" role="alert"></div>
          <div class="form-grp">
            <span class="form-lbl" id="an-to">Send to *</span>
            <div class="an-subjects" role="group" aria-labelledby="an-to">
              <?php if (count($annSubjects) > 1): ?>
                <label class="all"><input type="checkbox" id="ann-select-all" onchange="toggleAllSubjects(this)"> All my subjects</label>
              <?php else: ?>
                <input type="checkbox" id="ann-select-all" hidden>
              <?php endif; ?>
              <?php foreach ($annSubjects as $cid => $s): ?>
                <label><input type="checkbox" class="ann-subject-cb" value="<?= $cid ?>" <?= count($annSubjects) === 1 ? 'checked' : '' ?>
                    data-label="<?= htmlspecialchars($s['code'] . ' — ' . $s['name']) ?>">
                  <span><?= htmlspecialchars($s['name']) ?> <small><?= htmlspecialchars($s['code'] . ' · ' . implode(', ', array_unique($s['sections']))) ?></small></span></label>
              <?php endforeach; ?>
            </div>
            <div class="an-hint">Every student enrolled in the subject will see it.</div>
          </div>
          <div class="form-grp">
            <label class="form-lbl" for="ann-title">Title *</label>
            <input class="finput" type="text" id="ann-title" maxlength="200" placeholder="e.g. Quiz reminder — Chapter 3">
          </div>
          <div class="form-grp">
            <label class="form-lbl" for="ann-body">Message *</label>
            <textarea class="finput" id="ann-body" rows="4" placeholder="Type your announcement here."></textarea>
          </div>
          <div class="an-actions"><button class="btn btn-green" onclick="sendAnnouncement()" id="ann-send-btn">Send Announcement</button></div>
        </section>
      <?php endif; ?>

      <div class="an-listhead">
        <h2>Sent by you</h2>
        <?php if (count($annSubjects) > 1): ?>
          <label class="sr-only" for="ann-filter-course">Filter by subject</label>
          <select id="ann-filter-course" onchange="filterAnnouncements()">
            <option value="">All subjects</option>
            <?php foreach ($annSubjects as $cid => $s): ?><option value="<?= $cid ?>"><?= htmlspecialchars($s['name']) ?></option><?php endforeach; ?>
          </select>
        <?php endif; ?>
      </div>
      <div id="ann-list">
        <?php if (!$mine): ?>
          <div class="card-panel"><div class="an-card an-empty"><strong>Nothing sent yet</strong>Your announcements show up here.</div></div>
        <?php else: foreach ($mine as $a): ?>
          <article class="ann-item" data-id="<?= (int) $a['id'] ?>" data-course="<?= (int) $a['course_id'] ?>">
            <div class="ann-item-title"><?= htmlspecialchars($a['title']) ?></div>
            <div class="ann-item-body"><?= htmlspecialchars($a['body']) ?></div>
            <div class="ann-item-meta">
              <span class="badge badge-blue"><?= $a['course_id'] ? htmlspecialchars($a['course_code'] . ' — ' . $a['course_name']) : 'All students' ?></span>
              <span><?= htmlspecialchars($a['posted_at']) ?></span>
              <button type="button" class="an-edit" onclick="openAnnEdit(this)" aria-label="Edit announcement: <?= htmlspecialchars($a['title']) ?>">Edit</button>
              <button type="button" onclick="deleteAnnouncement(<?= (int) $a['id'] ?>, this)" aria-label="Delete announcement: <?= htmlspecialchars($a['title']) ?>">Delete</button>
            </div>
          </article>
        <?php endforeach; endif; ?>
      </div>
    </div>

    <aside class="an-card" aria-labelledby="an-school-h">
      <h2 id="an-school-h">From the school</h2>
      <?php if (!$school): ?>
        <div class="an-empty" style="padding:1rem 0">No school announcements right now.</div>
      <?php else: foreach ($school as $a): ?>
        <div class="an-school">
          <b><?= htmlspecialchars($a['title']) ?></b>
          <p><?= htmlspecialchars($a['body']) ?></p>
          <span><?= htmlspecialchars(($a['author'] ?: 'Administrator') . ' · ' . $a['posted_at']) ?></span>
        </div>
      <?php endforeach; endif; ?>
    </aside>
  </div>
</div>

<!-- EDIT ANNOUNCEMENT MODAL (moved to <body> by the script below so it always covers the whole page) -->
<div class="modal-overlay" id="annEditModal">
  <div class="modal" style="max-width:520px">
    <div class="modal-head">
      <h2>Edit announcement</h2>
      <button class="modal-close" aria-label="Close" onclick="closeModal('annEditModal')"><?= t_icon('x') ?></button>
    </div>
    <div class="modal-body">
      <div class="modal-err" id="ann-edit-err"></div>
      <input type="hidden" id="ann-edit-id">
      <div class="form-grp">
        <label class="form-lbl" for="ann-edit-title">Title *</label>
        <input class="finput" type="text" id="ann-edit-title" maxlength="200">
      </div>
      <div class="form-grp">
        <label class="form-lbl" for="ann-edit-body">Message *</label>
        <textarea class="finput" id="ann-edit-body" rows="5"></textarea>
      </div>
      <div class="an-hint">The subject and the date it was posted stay the same.</div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-gray" onclick="closeModal('annEditModal')">Cancel</button>
      <button class="btn btn-green" id="ann-edit-save-btn" onclick="submitAnnEdit()">Save changes</button>
    </div>
  </div>
</div>
<script>
  (function () {
    var m = document.getElementById('annEditModal');
    if (m && m.parentNode !== document.body) document.body.appendChild(m);

    // Announcements posted from the form above are added to the list without a page reload
    // (includes/footer.php): give those new cards their Edit button too.
    var list = document.getElementById('ann-list');
    if (!list || !window.MutationObserver) return;
    function decorate(item) {
      if (!item || item.nodeType !== 1 || !item.classList.contains('ann-item')) return;
      if (item.querySelector('.an-edit')) return;
      var del = item.querySelector('.ann-item-meta button');
      var found = del ? /deleteAnnouncement\(\s*(\d+)/.exec(del.getAttribute('onclick') || '') : null;
      if (!del || !found) return;
      item.setAttribute('data-id', found[1]);
      var b = document.createElement('button');
      b.type = 'button'; b.className = 'an-edit'; b.textContent = 'Edit';
      b.setAttribute('onclick', 'openAnnEdit(this)');
      del.parentNode.insertBefore(b, del);
    }
    new MutationObserver(function (muts) {
      muts.forEach(function (mu) { mu.addedNodes.forEach(decorate); });
    }).observe(list, { childList: true });
  })();

  function annById(id) { return document.getElementById(id); }

  function openAnnEdit(btn) {
    var item = btn && btn.closest ? btn.closest('.ann-item') : null;
    if (!item) return;
    var t = item.querySelector('.ann-item-title'), b = item.querySelector('.ann-item-body');
    annById('ann-edit-id').value = item.getAttribute('data-id') || '';
    annById('ann-edit-title').value = t ? t.textContent : '';
    annById('ann-edit-body').value = b ? b.textContent : '';
    annById('ann-edit-err').style.display = 'none';
    openModal('annEditModal');
  }

  async function submitAnnEdit() {
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

    var btn = annById('ann-edit-save-btn');
    if (btn.disabled) return;
    btn.disabled = true; btn.textContent = 'Saving...';
    try {
      var res = await fetch('../api/announcements.php?action=update', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: id, title: title, body: body })
      });
      var data = await res.json();
      if (data.success) {
        var item = document.querySelector('.ann-item[data-id="' + id + '"]');
        if (item) {
          var t = item.querySelector('.ann-item-title'), b = item.querySelector('.ann-item-body');
          if (t) t.textContent = title;
          if (b) b.textContent = body;
          item.querySelectorAll('.ann-item-meta button').forEach(function (x) {
            var l = x.getAttribute('aria-label') || '';
            x.setAttribute('aria-label', l.replace(/:.*$/, ': ' + title));
          });
        }
        closeModal('annEditModal');
        showToast('Announcement updated!');
      } else {
        fail(data.message || 'Could not save the changes.');
      }
    } catch (e) {
      fail('Network error. Please try again.');
    }
    btn.disabled = false; btn.textContent = 'Save changes';
  }
</script>
<?php require __DIR__ . '/../includes/footer.php'; ?>