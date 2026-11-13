<?php
// ============================================================
//  Arandia College eLMS — Admin Panel
//  File: admin/settings.php  |  System settings (saved by the Head Admin)
// ============================================================
session_start();
require_once '../config/conn.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['Admin', 'Head Admin'], true)) {
  header('Location: ../login.php');
  exit;
}
require_once '../config/permissions.php';
$isHeadAdmin = isHeadAdmin();

$first_name = $_SESSION['first_name'];
$last_name = $_SESSION['last_name'];

$stats = ['Student' => 0, 'Teacher' => 0, 'Admin' => 0, 'Total' => 0];
$result = $conn->query("SELECT role, COUNT(*) AS cnt FROM users GROUP BY role");
while ($r = $result->fetch_assoc()) {
  $stats[$r['role']] = (int) $r['cnt'];
  $stats['Total'] += (int) $r['cnt'];
}

$recentAccounts = [];
$res = $conn->query(
  "SELECT first_name, last_name, username, role, status,
     DATE_FORMAT(created_at,'%b %d, %Y') AS created_at
   FROM users ORDER BY created_at DESC LIMIT 5"
);
while ($r = $res->fetch_assoc())
  $recentAccounts[] = $r;

$allAccounts = [];
$res = $conn->query(
  "SELECT id, school_id, username, role, first_name, last_name, middle_name,
     email, contact, section_dept, status,
     DATE_FORMAT(created_at,'%b %d, %Y') AS created_at
   FROM users ORDER BY id DESC"
);
while ($r = $res->fetch_assoc())
  $allAccounts[] = $r;

$toast = $_GET['toast'] ?? '';
$isError = $_GET['error'] ?? '';

// ── Announcements ─────────────────────────────────────────────────────────
$allAnnouncements = [];
$annRes = $conn->query(
  "SELECT a.id, a.title, a.body, a.course_id,
          DATE_FORMAT(a.posted_at,'%b %d, %Y %h:%i %p') AS posted_at,
          CONCAT(u.first_name,' ',u.last_name) AS author,
          c.course_code, c.course_name
   FROM announcements a
   JOIN users u ON u.id = a.author_id
   LEFT JOIN courses c ON c.id = a.course_id
   ORDER BY a.posted_at DESC
   LIMIT 50"
);
while ($r = $annRes->fetch_assoc())
  $allAnnouncements[] = $r;

// For Assign Subjects panel
$allTeachers = [];
$res = $conn->query("SELECT id, CONCAT(last_name,', ',first_name) AS name FROM users WHERE role='Teacher' AND status='Active' ORDER BY last_name");
while ($r = $res->fetch_assoc())
  $allTeachers[] = $r;

$allCourses = [];
$res = $conn->query("SELECT id, course_code, course_name FROM courses WHERE status='Active' ORDER BY course_code");
while ($r = $res->fetch_assoc())
  $allCourses[] = $r;

// For Enroll Students panel
$allStudents = [];
$res = $conn->query(
  "SELECT id, school_id, section_dept, CONCAT(last_name,', ',first_name) AS name
   FROM users WHERE role='Student' AND status='Active' ORDER BY last_name, first_name"
);
while ($r = $res->fetch_assoc())
  $allStudents[] = $r;

require_once __DIR__ . '/../includes/sections.php';
[$shsSections, $hsSections] = loadSectionLists($conn);

// ── System settings (includes/settings.php) ──────────────────────────────
require_once __DIR__ . '/../includes/settings.php';
$sys = elmsSettings($conn);
$canEditSettings = $isHeadAdmin;   // school-wide: only the Head Admin saves them

$panel = 'settings';
$currentPage = 'settings.php';
$pageTitle = 'Settings';
require __DIR__ . '/../includes/header.php';
?>
<style>
  .set-note { display: flex; gap: .6rem; align-items: flex-start; background: var(--a-soft, #eef3ff); color: var(--ui-link, #003087); border-radius: 12px; padding: .8rem 1rem; font-size: .84rem; margin-bottom: 1.1rem; }
  html[data-theme="dark"] .set-note { background: rgba(143, 176, 255, .12); color: #c7d7ff; }
  .set-hint { font-size: .76rem; color: var(--ui-text-3, #8a97a8); margin-top: .35rem; }
  .set-err { display: none; font-size: .78rem; font-weight: 600; color: #c0392b; margin-top: .35rem; }
  .form-group.has-err .form-input { border-color: #c0392b; }
  .form-group.has-err .set-err { display: block; }
  .set-meta { font-size: .78rem; color: var(--ui-text-3, #8a97a8); margin-right: auto; align-self: center; }
  .set-actions { display: flex; gap: .6rem; align-items: center; flex-wrap: wrap; }
  .form-input:disabled { opacity: .75; cursor: not-allowed; }
</style>

<div class="section-panel active">
        <div class="page-header">
          <div class="page-title">Settings</div>
        </div>
        <div class="form-card" style="max-width:520px;">
          <?php if (!$canEditSettings): ?>
            <div class="set-note" role="note">Only the Head Admin can change these settings.</div>
          <?php endif; ?>
          <form class="form-grid" id="settings-form" novalidate onsubmit="saveSettings(event)">
            <div class="form-group full" data-field="system_name">
              <label class="form-label" for="set-name">System Name</label>
              <input class="form-input" type="text" id="set-name" maxlength="60" autocomplete="off"
                     value="<?= htmlspecialchars($sys['system_name']) ?>" <?= $canEditSettings ? '' : 'disabled' ?>>
              <div class="set-hint">Shown in page titles, footers, sign-in pages and e-mails.</div>
              <div class="set-err" role="alert"></div>
            </div>
            <div class="form-group full" data-field="school_year">
              <label class="form-label" for="set-sy">School Year</label>
              <?php
                // Dropdown: a few school years around now (+ the saved one, if it is outside that range)
                $syNow = (int) date('Y') - ((int) date('n') < 6 ? 1 : 0);   // a school year starts around June
                $sySaved = elmsSchoolYearLabel($sys['school_year']);
                $syOpts = [];
                for ($y = $syNow - 3; $y <= $syNow + 2; $y++) $syOpts[] = $y . ' – ' . ($y + 1);
                if (!in_array($sySaved, $syOpts, true)) { $syOpts[] = $sySaved; sort($syOpts); }
              ?>
              <select class="form-input" id="set-sy" <?= $canEditSettings ? '' : 'disabled' ?>>
                <?php foreach ($syOpts as $o): ?>
                  <option value="<?= htmlspecialchars($o) ?>" <?= $o === $sySaved ? 'selected' : '' ?>><?= htmlspecialchars($o) ?></option>
                <?php endforeach; ?>
              </select>
              <div class="set-hint">Used as the default when adding subjects and classes. Existing ones are not changed.</div>
              <div class="set-err" role="alert"></div>
            </div>
            <div class="form-group full" data-field="semester">
              <label class="form-label" for="set-sem">Semester</label>
              <select class="form-input" id="set-sem" <?= $canEditSettings ? '' : 'disabled' ?>>
                <?php foreach (elmsSemesterOptions() as $v => $label): ?>
                  <option value="<?= htmlspecialchars($v) ?>" <?= $sys['semester'] === $v ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
                <?php endforeach; ?>
              </select>
              <div class="set-err" role="alert"></div>
            </div>
            <div class="form-group full" data-field="school_level">
              <label class="form-label" for="set-level">School Level</label>
              <select class="form-input" id="set-level" <?= $canEditSettings ? '' : 'disabled' ?>>
                <?php foreach (elmsLevelOptions() as $v => $label): ?>
                  <option value="<?= htmlspecialchars($v) ?>" <?= $sys['school_level'] === $v ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
                <?php endforeach; ?>
              </select>
              <div class="set-hint">The label shown in footers, sign-in pages and e-mails.</div>
              <div class="set-err" role="alert"></div>
            </div>
            <?php if ($canEditSettings): ?>
              <div class="form-actions set-actions">
                <span class="set-meta" id="set-meta" aria-live="polite"></span>
                <button type="button" class="btn btn-reset-form" id="set-reset" onclick="resetSettingsForm()" disabled>Undo changes</button>
                <button type="submit" class="btn btn-primary" id="set-save" disabled>Save Settings</button>
              </div>
            <?php endif; ?>
          </form>
        </div>
      </div>

<script>
  // Saved values (kept in sync after each save and when another admin saves)
  let SET_SAVED = <?= json_encode([
      'system_name' => $sys['system_name'],
      'school_year' => elmsSchoolYearLabel($sys['school_year']),
      'semester' => $sys['semester'],
      'school_level' => $sys['school_level'],
  ], JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) ?>;
  const SET_FIELDS = { system_name: 'set-name', school_year: 'set-sy', semester: 'set-sem', school_level: 'set-level' };

  function setFormValues() {
    const out = {};
    for (const k in SET_FIELDS) out[k] = document.getElementById(SET_FIELDS[k]).value;
    out.system_name = out.system_name.trim();
    return out;
  }
  function setIsDirty() {
    const v = setFormValues();
    return Object.keys(SET_FIELDS).some(k => String(v[k]).trim() !== String(SET_SAVED[k]).trim());
  }
  function setRefreshButtons() {
    const save = document.getElementById('set-save'), reset = document.getElementById('set-reset');
    if (!save) return;
    const dirty = setIsDirty();
    save.disabled = !dirty || save.dataset.busy === '1';
    reset.disabled = !dirty || save.dataset.busy === '1';
  }
  function setClearErrors() {
    document.querySelectorAll('#settings-form .form-group[data-field]').forEach(g => {
      g.classList.remove('has-err'); g.querySelector('.set-err').textContent = '';
    });
  }
  function setShowErrors(errors) {
    let first = null;
    for (const k in errors) {
      const g = document.querySelector(`#settings-form .form-group[data-field="${k}"]`);
      if (!g) continue;
      g.classList.add('has-err'); g.querySelector('.set-err').textContent = errors[k];
      first = first || document.getElementById(SET_FIELDS[k]);
    }
    if (first) first.focus();
  }
  // Same checks as the server (includes/settings.php), so mistakes show before saving
  function setValidate(v) {
    const e = {};
    if (!v.system_name) e.system_name = 'Enter the system name.';
    else if (v.system_name.length > 60) e.system_name = 'Keep the system name to 60 characters or fewer.';
    else if (/[<>]/.test(v.system_name)) e.system_name = 'The system name cannot contain < or >.';
    const m = /^\s*(\d{4})\s*(?:-|–|—|to)\s*(\d{4})\s*$/.exec(v.school_year);
    if (!m) e.school_year = 'Choose a school year.';
    else if (+m[2] !== +m[1] + 1) e.school_year = 'The second year must be one after the first (e.g. 2025 – 2026).';
    else if (+m[1] < 2000 || +m[1] > 2099) e.school_year = 'Enter a school year between 2000 and 2100.';
    return e;
  }
  function resetSettingsForm() {
    for (const k in SET_FIELDS) document.getElementById(SET_FIELDS[k]).value = SET_SAVED[k];
    setClearErrors(); setRefreshButtons();
  }
  async function saveSettings(ev) {
    if (ev) ev.preventDefault();
    const save = document.getElementById('set-save');
    if (!save || save.dataset.busy === '1' || !setIsDirty()) return;
    setClearErrors();
    const v = setFormValues();
    const errs = setValidate(v);
    if (Object.keys(errs).length) { setShowErrors(errs); return; }

    save.dataset.busy = '1'; save.textContent = 'Saving…'; setRefreshButtons();
    try {
      const r = await fetch('../api/settings.php?action=save', {
        method: 'POST', credentials: 'same-origin', cache: 'no-store',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(v)
      });
      let d = null; try { d = await r.json(); } catch (e) { }
      if (!d) throw new Error('HTTP ' + r.status);
      if (!d.success) {
        if (d.errors) setShowErrors(d.errors);
        showToast(d.message || 'Could not save the settings.', true);
        return;
      }
      SET_SAVED = { system_name: d.settings.system_name, school_year: d.settings.school_year_label, semester: d.settings.semester, school_level: d.settings.school_level };
      resetSettingsForm();   // show the tidied values (e.g. "2025-2026" → "2025 – 2026")
      // This page: apply at once (other open pages get it from the live updater)
      if (window.ElmsSettings) window.ElmsSettings.apply(d.settings);
      document.getElementById('set-meta').textContent = d.changed && d.changed.length ? 'Saved just now.' : '';
      showToast(d.message || 'Settings saved.');
    } catch (e) {
      showToast('Network error — the settings were not saved. Please try again.', true);
    } finally {
      delete save.dataset.busy; save.textContent = 'Save Settings'; setRefreshButtons();
    }
  }
  document.addEventListener('DOMContentLoaded', () => {
    const f = document.getElementById('settings-form');
    f.addEventListener('input', () => { setRefreshButtons(); });
    f.addEventListener('change', () => { setRefreshButtons(); });
    // Another admin saved while this page is open: show the new values,
    // unless this admin is in the middle of editing (then only the buttons update)
    window.addEventListener('elms:settings', e => {
      const s = e.detail;
      const hadEdits = setIsDirty();
      SET_SAVED = { system_name: s.system_name, school_year: s.school_year_label, semester: s.semester, school_level: s.school_level };
      if (!hadEdits) resetSettingsForm();
      setRefreshButtons();
    });
    setRefreshButtons();
  });
</script>
<?php require __DIR__ . '/../includes/footer.php'; ?>