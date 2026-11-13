<?php
// ============================================================
//  Arandia College eLMS — Profile Panel Include
//  File: includes/profile_panel.php
//  Usage: include this file inside the section-panel div
//  Requires: $user_id, $conn already set in parent
// ============================================================

$profile = $conn->query(
  "SELECT id, school_id, username, role, first_name, last_name, middle_name,
            email, contact, section_dept, profile_picture, status,
            DATE_FORMAT(created_at,'%b %d, %Y') AS member_since
     FROM users WHERE id = $user_id LIMIT 1"
)->fetch_assoc();

// Defensive: never let a missing/failed row crash the page.
if (!$profile) {
  $profile = [
    'id' => $user_id, 'school_id' => '', 'username' => '', 'role' => 'Student',
    'first_name' => '', 'last_name' => '', 'middle_name' => '', 'email' => '',
    'contact' => '', 'section_dept' => '', 'profile_picture' => null,
    'status' => 'Active', 'member_since' => '—',
  ];
}

$pic = $profile['profile_picture'] ?? null;
$initials = strtoupper((($profile['first_name'] ?? '')[0] ?? '') . (($profile['last_name'] ?? '')[0] ?? '')) ?: '?';
$nameLocked = ($profile['role'] === 'Student');   // students cannot change their name (set by admin)
$roleColor = $profile['role'] === 'Teacher' ? '#00875a' : '#003087';
$roleBg = $profile['role'] === 'Teacher' ? '#e6fff4' : '#e8f0ff';

// Small inline icon set (feather-style, matches the existing password-eye icons in this file).
function pf_icon($name, $size = 18) {
  $paths = [
    'id-card'  => '<rect x="2" y="5" width="20" height="14" rx="2"/><line x1="6" y1="15" x2="10" y2="15"/><circle cx="8" cy="10.5" r="1.5"/><line x1="13" y1="9" x2="19" y2="9"/><line x1="13" y1="12" x2="19" y2="12"/>',
    'user'     => '<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>',
    'school'   => '<path d="M12 3 2 8l10 5 10-5-10-5Z"/><path d="M6 10.5V16c0 1 2.7 3 6 3s6-2 6-3v-5.5"/>',
    'mail'     => '<rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 6-10 7L2 6"/>',
    'phone'    => '<path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92Z"/>',
    'calendar' => '<rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>',
    'check-circle' => '<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>',
    'x-circle' => '<circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/>',
    'camera'   => '<path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2Z"/><circle cx="12" cy="13" r="4"/>',
    'edit'     => '<path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4Z"/>',
    'lock'     => '<rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>',
    'upload'   => '<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/>',
    'x'        => '<line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>',
    'check'    => '<polyline points="20 6 9 17 4 12"/>',
    'alert'    => '<path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>',
  ];
  $d = $paths[$name] ?? '';
  return '<svg width="' . (int)$size . '" height="' . (int)$size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $d . '</svg>';
}
?>

<style>
  .profile-wrap {
    display: grid;
    grid-template-columns: 320px 1fr;
    gap: 1.5rem;
    margin-bottom: 1.5rem;
    max-width: 100%;
  }

  .profile-card {
    background: var(--ui-surface, #fff);
    border: 1px solid var(--ui-border, #e2e8f0);
    border-radius: var(--ui-radius-lg, 14px);
    box-shadow: var(--ui-shadow-sm, 0 2px 16px rgba(0, 0, 0, 0.08));
    padding: 2rem;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 1rem;
    min-width: 0;
  }

  .avatar-wrap {
    position: relative;
    width: 110px;
    height: 110px
  }

  .avatar-img {
    width: 110px;
    height: 110px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid var(--ui-border, #e8eaf0);
    overflow: hidden;
    color: transparent;
    font-size: 0;
  }

  .avatar-initials {
    width: 110px;
    height: 110px;
    border-radius: 50%;
    background: linear-gradient(135deg, <?= $roleColor ?>, <?= $roleColor ?>aa);
    color: white;
    font-family: 'Nunito', sans-serif;
    font-size: 2.2rem;
    font-weight: 900;
    display: flex;
    align-items: center;
    justify-content: center
  }

  .avatar-edit-btn {
    position: absolute;
    bottom: 2px;
    right: 2px;
    width: 32px;
    height: 32px;
    background: <?= $roleColor ?>;
    color: white;
    border-radius: 50%;
    border: 2px solid var(--ui-surface, #fff);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: transform .2s
  }

  .avatar-edit-btn:hover { transform: scale(1.08) }
  .avatar-edit-btn:focus-visible { outline: 2px solid <?= $roleColor ?>; outline-offset: 2px }

  .profile-name {
    font-family: 'Nunito', sans-serif;
    font-size: 1.15rem;
    font-weight: 900;
    color: var(--ui-text, #1a1a2e);
    text-align: center;
    overflow-wrap: anywhere;
  }

  .profile-role {
    font-size: 0.72rem;
    font-weight: 800;
    padding: 0.2rem 0.75rem;
    border-radius: 100px;
    background: <?= $roleBg ?>;
    color: <?= $roleColor ?>
  }

  .profile-info-row {
    width: 100%;
    display: flex;
    align-items: center;
    gap: 0.6rem;
    padding: 0.5rem 0;
    border-bottom: 1px solid var(--ui-border-soft, #f8f9fc);
    font-size: 0.82rem;
    min-width: 0;
  }

  .profile-info-row:last-child { border-bottom: none }

  .profile-info-icon {
    width: 28px;
    height: 28px;
    background: var(--ui-surface-2, #f0f2f5);
    color: <?= $roleColor ?>;
    border-radius: 7px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0
  }

  .profile-info-text { min-width: 0; overflow-wrap: anywhere; }
  .profile-info-label { font-size: 0.68rem; font-weight: 700; color: var(--ui-text-2, #888); display: block }
  .profile-info-val { font-weight: 600; color: var(--ui-text, #1a1a2e) }
  .profile-info-status { display: inline-flex; align-items: center; gap: .3rem; font-weight: 700; }
  .profile-info-status svg { flex-shrink: 0 }
  .status-active { color: #00875a }
  .status-inactive { color: #c0392b }

  .edit-card {
    background: var(--ui-surface, #fff);
    border: 1px solid var(--ui-border, #e2e8f0);
    border-radius: var(--ui-radius-lg, 14px);
    box-shadow: var(--ui-shadow-sm, 0 2px 16px rgba(0, 0, 0, 0.08));
    padding: 2rem;
    min-width: 0;
  }

  .edit-card h3 {
    font-family: 'Nunito', sans-serif;
    font-size: 1rem;
    font-weight: 900;
    color: var(--ui-text, #1a1a2e);
    margin-bottom: 1.25rem;
    padding-bottom: 0.75rem;
    border-bottom: 1px solid var(--ui-border-soft, #f0f2f5);
    display: flex;
    align-items: center;
    gap: .5rem;
  }
  .edit-card h3 svg { color: <?= $roleColor ?> }

  .pf-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 1rem }
  .pf-grp { display: flex; flex-direction: column; gap: 0.35rem; min-width: 0; }
  .pf-grp.full { grid-column: 1/-1 }
  .pf-lbl { font-size: 0.75rem; font-weight: 700; color: var(--ui-text-2, #555) }
  .pf-lbl .pf-optional { color: var(--ui-text-3, #aaa); font-weight: 400 }

  .pf-input {
    padding: 0.6rem 0.9rem;
    border: 1.5px solid var(--ui-border, #e0e4ee);
    border-radius: 9px;
    font-family: 'Open Sans', sans-serif;
    font-size: 0.85rem;
    background: var(--ui-surface-2, #fafbff);
    outline: none;
    transition: border-color .2s, box-shadow .2s;
    color: var(--ui-text, #1a1a2e);
    width: 100%;
  }

  .pf-input:focus-visible {
    border-color: <?= $roleColor ?>;
    box-shadow: 0 0 0 3px <?= $roleColor ?>2e;
    background: white
  }

  .pf-input:disabled { background: var(--ui-surface-3, #f5f5f5); color: var(--ui-text-2, #888); cursor: not-allowed }
  .pf-input.pf-invalid { border-color: #c0392b; background: #fff8f7 }
  .pf-input.pf-invalid:focus-visible { box-shadow: 0 0 0 3px #c0392b26 }

  .pf-field-err {
    display: none;
    font-size: 0.72rem;
    font-weight: 600;
    color: #c0392b;
    align-items: center;
    gap: .3rem;
  }
  .pf-field-err.show { display: flex }
  .pf-field-err svg { flex-shrink: 0 }

  .pf-actions {
    grid-column: 1/-1;
    display: flex;
    gap: 0.75rem;
    justify-content: flex-end;
    margin-top: 0.5rem;
    flex-wrap: wrap;
  }

  .pf-btn {
    font-family: 'Nunito', sans-serif;
    font-size: 0.82rem;
    font-weight: 700;
    padding: 0.6rem 1.25rem;
    border-radius: 9px;
    border: none;
    cursor: pointer;
    transition: background-color .2s, border-color .2s, color .2s, box-shadow .2s, opacity .2s, transform .2s;
    display: inline-flex;
    align-items: center;
    gap: .4rem;
  }

  .pf-btn:disabled { opacity: .6; cursor: not-allowed }
  .pf-btn:focus-visible { outline: 2px solid <?= $roleColor ?>; outline-offset: 2px }
  .pf-btn-save { background: <?= $roleColor ?>; color: white }
  .pf-btn-save:hover:not(:disabled) { opacity: 0.88 }
  .pf-btn-cancel { background: var(--ui-surface-3, #f0f2f5); color: var(--ui-text, #555) }
  .pf-btn-cancel:hover:not(:disabled) { background: var(--ui-border, #e0e4ee) }

  .pf-spinner {
    width: 14px; height: 14px;
    border: 2px solid rgba(255,255,255,.5);
    border-top-color: white;
    border-radius: 50%;
    animation: pf-spin .7s linear infinite;
  }
  @keyframes pf-spin { to { transform: rotate(360deg) } }

  .pf-banner {
    display: none;
    align-items: center;
    gap: .5rem;
    font-size: 0.78rem;
    font-weight: 600;
    padding: 0.6rem 0.9rem;
    border-radius: 8px;
    grid-column: 1/-1;
  }
  .pf-banner.show { display: flex }
  .pf-banner svg { flex-shrink: 0 }
  .pf-banner-success { background: #e6fff4; border: 1px solid #b7f5d8; color: #00875a }
  .pf-banner-error { background: #fff0ec; border: 1px solid #ffcfbf; color: #c0392b }

  .pic-upload-overlay {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.5);
    z-index: 700;
    align-items: center;
    justify-content: center;
    padding: 1rem;
  }
  .pic-upload-overlay.show { display: flex }

  .pic-upload-box {
    background: var(--ui-surface, #fff);
    border-radius: 18px;
    padding: 2rem;
    width: 100%;
    max-width: 420px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.25);
    text-align: center
  }

  .pic-upload-box h3 {
    font-family: 'Nunito', sans-serif;
    font-size: 1rem;
    font-weight: 900;
    margin-bottom: 1rem;
    color: var(--ui-text, #1a1a2e);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: .5rem;
  }

  .pic-preview {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid var(--ui-border, #e8eaf0);
    margin: 0 auto 1rem;
    display: block
  }

  .pic-drop-zone {
    border: 2px dashed var(--ui-border, #e0e4ee);
    border-radius: 12px;
    padding: 1.5rem;
    cursor: pointer;
    transition: background-color .2s, border-color .2s, color .2s, box-shadow .2s, opacity .2s, transform .2s;
    margin-bottom: 1rem;
  }
  .pic-drop-zone:hover, .pic-drop-zone:focus-visible {
    border-color: <?= $roleColor ?>;
    background: var(--ui-surface-2, #f8f9fc);
    outline: none;
  }
  .pic-drop-zone svg { color: var(--ui-text-3, #aaa) }
  .pic-drop-text { font-size: 0.82rem; color: var(--ui-text-3, #aaa); margin-top: 0.4rem }
  .pic-actions { display: flex; gap: 0.75rem; justify-content: center }

  .sr-only {
    position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px;
    overflow: hidden; clip: rect(0,0,0,0); white-space: nowrap; border: 0;
  }

  /* ── Password toggle button (matches login.php) ── */
  .toggle-pw {
    position: absolute;
    right: 0.6rem;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    cursor: pointer;
    padding: 0.35rem;
    color: var(--ui-text-3, #94a3b8);
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 6px;
    transition: color 0.2s, background 0.2s;
    outline: none;
  }
  .toggle-pw:hover { color: var(--ui-link, #003087); background: rgba(0,48,135,0.07); }
  .toggle-pw:focus-visible { box-shadow: 0 0 0 3px rgba(0,48,135,0.15); }
  .toggle-pw svg { width: 18px; height: 18px; display: block; }
  .toggle-pw .eye-open { display: none; }
  .toggle-pw.is-visible .eye-slash  { display: none; }
  .toggle-pw.is-visible .eye-open { display: block; }

  @media (max-width: 1024px) {
    .profile-wrap { grid-template-columns: minmax(0, 1fr) }
  }

  @media (max-width: 640px) {
    .pf-grid { grid-template-columns: minmax(0, 1fr) }
    .profile-card, .edit-card { padding: 1.25rem }
    .pf-actions { flex-direction: column-reverse }
    .pf-actions .pf-btn { width: 100%; justify-content: center }
  }
  /* Dark mode (assets/css/elms-ui.css tokens) */
  html[data-theme="dark"] .profile-role { background: rgba(143, 176, 255, .14); color: <?= $profile['role'] === 'Teacher' ? '#34d399' : '#c7d7ff' ?>; }
  html[data-theme="dark"] .profile-info-icon { color: <?= $profile['role'] === 'Teacher' ? '#34d399' : '#8fb0ff' ?>; }
  html[data-theme="dark"] .edit-card h3 svg { color: <?= $profile['role'] === 'Teacher' ? '#34d399' : '#8fb0ff' ?>; }
  html[data-theme="dark"] .pf-banner-success { background: rgba(52, 211, 153, .12); border-color: rgba(52, 211, 153, .3); color: #34d399; }
  html[data-theme="dark"] .pf-banner-error { background: rgba(248, 113, 113, .12); border-color: rgba(248, 113, 113, .3); color: #f87171; }
  html[data-theme="dark"] .pf-input.pf-invalid { background: rgba(248, 113, 113, .08); }
  html[data-theme="dark"] .toggle-pw:hover { color: #8fb0ff; background: rgba(143, 176, 255, .12); }
</style>

<div class="profile-wrap">
  <!-- LEFT: Profile card -->
  <div class="profile-card">
    <!-- Avatar -->
    <div class="avatar-wrap">
      <?php if ($pic): ?>
        <img src="<?= '../' . htmlspecialchars($pic) ?>" alt="Profile photo of <?= htmlspecialchars(trim($profile['first_name'] . ' ' . $profile['last_name'])) ?>" class="avatar-img" id="avatar-display" onerror="this.replaceWith(Object.assign(document.createElement('div'),{className:'avatar-initials',id:'avatar-display',textContent:'<?= addslashes($initials) ?>'}))">
      <?php else: ?>
        <div class="avatar-initials" id="avatar-display"><?= htmlspecialchars($initials) ?></div>
      <?php endif; ?>
      <button type="button" class="avatar-edit-btn" onclick="openPicModal()" aria-label="Change profile photo"><?= pf_icon('camera', 16) ?></button>
    </div>

    <div class="profile-name">
      <?= htmlspecialchars(trim($profile['last_name'] . ', ' . $profile['first_name'] . ($profile['middle_name'] ? ' ' . $profile['middle_name'][0] . '.' : ''))) ?: '—' ?>
    </div>
    <span class="profile-role"><?= htmlspecialchars($profile['role']) ?></span>

    <!-- Info rows -->
    <div style="width:100%;margin-top:0.5rem">
      <div class="profile-info-row">
        <div class="profile-info-icon"><?= pf_icon('id-card', 16) ?></div>
        <div class="profile-info-text"><span class="profile-info-label">LRN / School ID</span><span
            class="profile-info-val" id="disp-school-id"><?= htmlspecialchars($profile['school_id'] ?: '—') ?></span></div>
      </div>
      <?php if ($profile['section_dept']): ?>
        <div class="profile-info-row">
          <div class="profile-info-icon"><?= pf_icon('school', 16) ?></div>
          <div class="profile-info-text"><span
              class="profile-info-label"><?= $profile['role'] === 'Teacher' ? 'Department' : 'Section / Grade' ?></span><span
              class="profile-info-val"><?= htmlspecialchars($profile['section_dept']) ?></span></div>
        </div>
      <?php endif; ?>
      <div class="profile-info-row">
        <div class="profile-info-icon"><?= pf_icon('mail', 16) ?></div>
        <div class="profile-info-text"><span class="profile-info-label">Email</span><span
            class="profile-info-val" id="disp-email"><?= htmlspecialchars($profile['email'] ?: '—') ?></span></div>
      </div>
      <div class="profile-info-row">
        <div class="profile-info-icon"><?= pf_icon('phone', 16) ?></div>
        <div class="profile-info-text"><span class="profile-info-label">Contact</span><span
            class="profile-info-val" id="disp-contact"><?= htmlspecialchars($profile['contact'] ?: '—') ?></span></div>
      </div>
      <div class="profile-info-row">
        <div class="profile-info-icon"><?= pf_icon('calendar', 16) ?></div>
        <div class="profile-info-text"><span class="profile-info-label">Member Since</span><span
            class="profile-info-val"><?= htmlspecialchars($profile['member_since']) ?></span></div>
      </div>
      <div class="profile-info-row">
        <div class="profile-info-icon"><?= pf_icon($profile['status'] === 'Active' ? 'check-circle' : 'x-circle', 16) ?></div>
        <div class="profile-info-text"><span class="profile-info-label">Status</span>
          <span class="profile-info-val profile-info-status <?= $profile['status'] === 'Active' ? 'status-active' : 'status-inactive' ?>">
            <?= htmlspecialchars($profile['status']) ?>
          </span>
        </div>
      </div>
    </div>
  </div>

  <!-- RIGHT: Edit form -->
  <div class="edit-card">
    <h3><?= pf_icon('edit', 18) ?> Edit Profile</h3>
    <form id="pf-form" novalidate>
      <div class="pf-grid">
        <div class="pf-banner pf-banner-success" id="pf-success" role="status"></div>
        <div class="pf-banner pf-banner-error" id="pf-error" role="alert"></div>

        <div class="pf-grp">
          <label class="pf-lbl" for="pf-fname">First Name <?= $nameLocked ? '<span class="pf-optional">(set by admin)</span>' : '*' ?></label>
          <input class="pf-input" type="text" id="pf-fname" name="first_name" required
            aria-describedby="err-fname" value="<?= htmlspecialchars($profile['first_name']) ?>" <?= $nameLocked ? 'disabled readonly' : '' ?>>
          <span class="pf-field-err" id="err-fname" role="alert"><?= pf_icon('alert', 13) ?><span></span></span>
        </div>
        <div class="pf-grp">
          <label class="pf-lbl" for="pf-lname">Last Name <?= $nameLocked ? '<span class="pf-optional">(set by admin)</span>' : '*' ?></label>
          <input class="pf-input" type="text" id="pf-lname" name="last_name" required
            aria-describedby="err-lname" value="<?= htmlspecialchars($profile['last_name']) ?>" <?= $nameLocked ? 'disabled readonly' : '' ?>>
          <span class="pf-field-err" id="err-lname" role="alert"><?= pf_icon('alert', 13) ?><span></span></span>
        </div>
        <div class="pf-grp">
          <label class="pf-lbl" for="pf-mname">Middle Name <?= $nameLocked ? '<span class="pf-optional">(set by admin)</span>' : '' ?></label>
          <input class="pf-input" type="text" id="pf-mname" name="middle_name"
            value="<?= htmlspecialchars($profile['middle_name'] ?? '') ?>" <?= $nameLocked ? 'disabled readonly' : '' ?>>
        </div>
        <div class="pf-grp">
          <label class="pf-lbl" for="pf-schoolid">LRN / School ID <span class="pf-optional">(cannot change)</span></label>
          <input class="pf-input" type="text" id="pf-schoolid" value="<?= htmlspecialchars($profile['school_id']) ?>" disabled readonly>
        </div>
        <div class="pf-grp">
          <label class="pf-lbl" for="pf-section"><?= $profile['role'] === 'Teacher' ? 'Department' : 'Section / Grade' ?> <span class="pf-optional">(set by admin)</span></label>
          <input class="pf-input" type="text" id="pf-section" value="<?= htmlspecialchars($profile['section_dept'] ?? '—') ?>" disabled readonly>
        </div>
        <div class="pf-grp">
          <label class="pf-lbl" for="pf-email">Email Address</label>
          <input class="pf-input" type="email" id="pf-email" name="email" aria-describedby="err-email"
            value="<?= htmlspecialchars($profile['email'] ?? '') ?>" placeholder="your@email.com">
          <span class="pf-field-err" id="err-email" role="alert"><?= pf_icon('alert', 13) ?><span></span></span>
        </div>
        <div class="pf-grp">
          <label class="pf-lbl" for="pf-contact">Contact Number</label>
          <input class="pf-input" type="tel" id="pf-contact" name="contact" aria-describedby="err-contact"
            value="<?= htmlspecialchars($profile['contact'] ?? '') ?>" placeholder="09XXXXXXXXX" maxlength="11"
            inputmode="numeric" oninput="this.value=this.value.replace(/\D/g,'').slice(0,11)">
          <span class="pf-field-err" id="err-contact" role="alert"><?= pf_icon('alert', 13) ?><span></span></span>
        </div>

        <!-- Divider -->
        <div class="pf-grp full" style="border-top:1.5px solid var(--ui-border-soft, #f0f2f5);margin-top:.5rem;padding-top:1.25rem">
          <div style="font-family:'Nunito',sans-serif;font-size:.95rem;font-weight:900;color:var(--ui-text, #1a1a2e);margin-bottom:.1rem;display:flex;align-items:center;gap:.4rem">
            <?= pf_icon('lock', 16) ?> Change Password
          </div>
          <div style="font-size:.75rem;color:var(--ui-text-3, #aaa)">Leave blank if you do not want to change your password.</div>
        </div>
        <div class="pf-banner pf-banner-success" id="pw-success" role="status"></div>
        <div class="pf-banner pf-banner-error" id="pw-error" role="alert"></div>

        <div class="pf-grp full">
          <label class="pf-lbl" for="pw-current">Current Password</label>
          <div style="position:relative">
            <input class="pf-input" type="password" id="pw-current" name="current_password" autocomplete="current-password"
              placeholder="Enter current password" style="padding-right:2.5rem" aria-describedby="err-pw-current">
            <button type="button" class="toggle-pw" onclick="togglePw('pw-current',this)" aria-label="Show password">
              <svg class="eye-open" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
              </svg>
              <svg class="eye-slash" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/>
                <path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/>
                <line x1="1" y1="1" x2="23" y2="23"/>
              </svg>
            </button>
          </div>
          <span class="pf-field-err" id="err-pw-current" role="alert"><?= pf_icon('alert', 13) ?><span></span></span>
        </div>

        <div class="pf-grp">
          <label class="pf-lbl" for="pw-new">New Password</label>
          <div style="position:relative">
            <input class="pf-input" type="password" id="pw-new" name="new_password" autocomplete="new-password"
              placeholder="At least 8 characters" style="padding-right:2.5rem" aria-describedby="err-pw-new">
            <button type="button" class="toggle-pw" onclick="togglePw('pw-new',this)" aria-label="Show password">
              <svg class="eye-open" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
              </svg>
              <svg class="eye-slash" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/>
                <path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/>
                <line x1="1" y1="1" x2="23" y2="23"/>
              </svg>
            </button>
          </div>
          <span class="pf-field-err" id="err-pw-new" role="alert"><?= pf_icon('alert', 13) ?><span></span></span>
        </div>

        <div class="pf-grp">
          <label class="pf-lbl" for="pw-confirm">Confirm New Password</label>
          <div style="position:relative">
            <input class="pf-input" type="password" id="pw-confirm" name="confirm_password" autocomplete="new-password"
              placeholder="Re-enter new password" style="padding-right:2.5rem" aria-describedby="err-pw-confirm">
            <button type="button" class="toggle-pw" onclick="togglePw('pw-confirm',this)" aria-label="Show password">
              <svg class="eye-open" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
              </svg>
              <svg class="eye-slash" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/>
                <path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/>
                <line x1="1" y1="1" x2="23" y2="23"/>
              </svg>
            </button>
          </div>
          <span class="pf-field-err" id="err-pw-confirm" role="alert"><?= pf_icon('alert', 13) ?><span></span></span>
        </div>

        <div class="pf-actions">
          <button type="button" class="pf-btn pf-btn-cancel" id="pf-reset-btn"><?= pf_icon('x', 15) ?> Reset</button>
          <button type="submit" class="pf-btn pf-btn-save" id="pf-save-btn"><?= pf_icon('check', 15) ?> <span id="pf-save-label">Save Changes</span></button>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- Profile picture upload modal -->
<div class="pic-upload-overlay" id="picModal" role="dialog" aria-modal="true" aria-labelledby="pic-modal-title">
  <div class="pic-upload-box">
    <h3 id="pic-modal-title"><?= pf_icon('camera', 18) ?> Change Profile Picture</h3>
    <img src="<?= $pic ? '../' . htmlspecialchars($pic) : '' ?>" id="pic-preview-img" style="<?= $pic ? '' : 'display:none' ?>"
      class="pic-preview" alt="Preview of selected profile photo">
    <div class="pic-drop-zone" tabindex="0" role="button" aria-label="Click or drag and drop to choose a photo"
      onclick="document.getElementById('pic-file-input').click()"
      onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();document.getElementById('pic-file-input').click();}">
      <div style="display:flex;justify-content:center"><?= pf_icon('upload', 28) ?></div>
      <div class="pic-drop-text">Click to browse or drag & drop<br><span style="font-size:0.72rem">JPG, PNG, WEBP —
          max 2MB</span></div>
    </div>
    <input type="file" id="pic-file-input" accept=".jpg,.jpeg,.png,.webp" style="display:none" onchange="previewPic(this)">
    <div id="pic-err" class="pf-field-err" role="alert" style="justify-content:center;margin-bottom:0.75rem"><?= pf_icon('alert', 13) ?><span></span></div>
    <div class="pic-actions">
      <button type="button" class="pf-btn pf-btn-cancel" onclick="closePicModal()"><?= pf_icon('x', 15) ?> Cancel</button>
      <button type="button" class="pf-btn pf-btn-save" id="pic-upload-btn" onclick="uploadPic()" style="display:none"><?= pf_icon('check', 15) ?> <span id="pic-upload-label">Upload</span></button>
    </div>
  </div>
</div>

<script>
(function () {
  // ── Original values (used by Reset + unsaved-changes detection) ─────────
  const pfOrig = {
    fname: <?= json_encode($profile['first_name'] ?? '') ?>,
    lname: <?= json_encode($profile['last_name'] ?? '') ?>,
    mname: <?= json_encode($profile['middle_name'] ?? '') ?>,
    email: <?= json_encode($profile['email'] ?? '') ?>,
    contact: <?= json_encode($profile['contact'] ?? '') ?>,
  };

  const $ = (id) => document.getElementById(id);
  const form = $('pf-form');
  let isSaving = false;

  function showBanner(el, msg) {
    if (!el) return;
    el.textContent = msg;
    el.classList.add('show');
  }
  function hideBanner(el) { if (el) { el.classList.remove('show'); el.textContent = ''; } }
  function hideAllBanners() {
    ['pf-success', 'pf-error', 'pw-success', 'pw-error'].forEach(id => hideBanner($(id)));
  }

  function setFieldError(inputId, errId, msg) {
    const input = $(inputId), err = $(errId);
    if (input) input.classList.toggle('pf-invalid', !!msg);
    if (input) input.setAttribute('aria-invalid', msg ? 'true' : 'false');
    if (err) {
      const span = err.querySelector('span');
      if (span) span.textContent = msg || '';
      err.classList.toggle('show', !!msg);
    }
  }
  function clearFieldErrors() {
    ['fname', 'lname', 'email', 'contact', 'pw-current', 'pw-new', 'pw-confirm'].forEach(k => {
      const inputId = k.startsWith('pw-') ? k : 'pf-' + k;
      setFieldError(inputId, 'err-' + k, '');
    });
  }

  function currentValues() {
    return {
      first_name: ($('pf-fname')?.value || '').trim(),
      last_name: ($('pf-lname')?.value || '').trim(),
      middle_name: ($('pf-mname')?.value || '').trim(),
      email: ($('pf-email')?.value || '').trim(),
      contact: ($('pf-contact')?.value || '').trim(),
    };
  }

  function isDirty() {
    const v = currentValues();
    const pw = ($('pw-current')?.value || '') + ($('pw-new')?.value || '') + ($('pw-confirm')?.value || '');
    return v.first_name !== (pfOrig.fname || '') ||
      v.last_name !== (pfOrig.lname || '') ||
      v.middle_name !== (pfOrig.mname || '') ||
      v.email !== (pfOrig.email || '') ||
      v.contact !== (pfOrig.contact || '') ||
      pw.length > 0;
  }

  // Warn on navigating away with unsaved changes — but only when there really are changes.
  window.addEventListener('beforeunload', function (e) {
    if (isDirty()) { e.preventDefault(); e.returnValue = ''; }
  });

  function isValidEmail(v) { return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v); }
  function isValidContact(v) { return /^\d{7,11}$/.test(v); }

  function validateProfileFields() {
    const v = currentValues();
    let ok = true;
    if (!v.first_name) { setFieldError('pf-fname', 'err-fname', 'First name is required.'); ok = false; }
    if (!v.last_name) { setFieldError('pf-lname', 'err-lname', 'Last name is required.'); ok = false; }
    if (v.email && !isValidEmail(v.email)) { setFieldError('pf-email', 'err-email', 'Enter a valid email address.'); ok = false; }
    if (v.contact && !isValidContact(v.contact)) { setFieldError('pf-contact', 'err-contact', 'Enter a valid contact number.'); ok = false; }
    return ok;
  }

  function validatePasswordFields() {
    const current = $('pw-current').value, newPw = $('pw-new').value, confirm = $('pw-confirm').value;
    if (!current && !newPw && !confirm) return { attempt: false, ok: true };

    let ok = true;
    if (!current) { setFieldError('pw-current', 'err-pw-current', 'Current password is required.'); ok = false; }
    if (!newPw) { setFieldError('pw-new', 'err-pw-new', 'New password is required.'); ok = false; }
    else if (newPw.length < 8) { setFieldError('pw-new', 'err-pw-new', 'Must be at least 8 characters.'); ok = false; }
    if (!confirm) { setFieldError('pw-confirm', 'err-pw-confirm', 'Please confirm your new password.'); ok = false; }
    else if (newPw && confirm !== newPw) { setFieldError('pw-confirm', 'err-pw-confirm', 'Passwords do not match.'); ok = false; }
    return { attempt: true, ok, current, newPw, confirm };
  }

  function setSaving(saving) {
    isSaving = saving;
    const btn = $('pf-save-btn'), label = $('pf-save-label'), resetBtn = $('pf-reset-btn');
    if (btn) btn.disabled = saving;
    if (resetBtn) resetBtn.disabled = saving;
    if (btn) {
      btn.innerHTML = saving
        ? '<span class="pf-spinner" aria-hidden="true"></span> <span>Saving…</span>'
        : '<?= addslashes(pf_icon("check", 15)) ?> <span id="pf-save-label">Save Changes</span>';
    }
  }

  async function safeJson(res) {
    let data;
    try { data = await res.json(); } catch (e) { return null; }
    return data;
  }

  form.addEventListener('submit', async function (e) {
    e.preventDefault();
    if (isSaving) return; // guard against double submit
    hideAllBanners();
    clearFieldErrors();

    const profileOk = validateProfileFields();
    const pwCheck = validatePasswordFields();
    if (!profileOk || !pwCheck.ok) return;

    setSaving(true);
    const payload = currentValues();

    try {
      const res = await fetch('../api/profile.php?action=update', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      });
      const data = await safeJson(res);
      if (!res.ok || !data) {
        showBanner($('pf-error'), 'Something went wrong. Please try again.');
        setSaving(false);
        return;
      }
      if (!data.success) {
        showBanner($('pf-error'), data.message || 'Unable to update profile.');
        setSaving(false);
        return;
      }
      showBanner($('pf-success'), data.message || 'Profile updated successfully.');

      // Reflect saved values without a full page reload.
      pfOrig.fname = payload.first_name;
      pfOrig.lname = payload.last_name;
      pfOrig.mname = payload.middle_name;
      pfOrig.email = payload.email;
      pfOrig.contact = payload.contact;
      const nameEl = document.querySelector('.profile-name');
      if (nameEl) nameEl.textContent = [payload.last_name, payload.first_name].filter(Boolean).join(', ') || '—';
      const emailDisp = $('disp-email'); if (emailDisp) emailDisp.textContent = payload.email || '—';
      const contactDisp = $('disp-contact'); if (contactDisp) contactDisp.textContent = payload.contact || '—';
    } catch (err) {
      showBanner($('pf-error'), 'Network error. Please check your connection and try again.');
      setSaving(false);
      return;
    }

    // ── Password change (only if the user actually filled the fields) ──────
    if (pwCheck.attempt) {
      try {
        const res2 = await fetch('../api/profile.php?action=change_password', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ current_password: pwCheck.current, new_password: pwCheck.newPw })
        });
        const data2 = await safeJson(res2);
        if (!res2.ok || !data2) {
          showBanner($('pw-error'), 'Something went wrong changing your password. Please try again.');
        } else if (data2.success) {
          showBanner($('pw-success'), 'Password changed successfully.');
          ['pw-current', 'pw-new', 'pw-confirm'].forEach(id => { $(id).value = ''; });
        } else {
          showBanner($('pw-error'), data2.message || 'Unable to change password.');
        }
      } catch (err) {
        showBanner($('pw-error'), 'Network error. Please check your connection and try again.');
      }
    }

    setSaving(false);
  });

  $('pf-reset-btn').addEventListener('click', function () {
    if (isSaving) return;
    if (isDirty() && !confirm('Discard your unsaved changes?')) return;
    $('pf-fname').value = pfOrig.fname;
    $('pf-lname').value = pfOrig.lname;
    $('pf-mname').value = pfOrig.mname;
    $('pf-email').value = pfOrig.email;
    $('pf-contact').value = pfOrig.contact;
    ['pw-current', 'pw-new', 'pw-confirm'].forEach(id => { $(id).value = ''; });
    hideAllBanners();
    clearFieldErrors();
  });

  // ── Picture upload ────────────────────────────────────────────────────
  window.openPicModal = function () {
    document.getElementById('picModal').classList.add('show');
  };
  window.closePicModal = function () {
    document.getElementById('picModal').classList.remove('show');
  };

  window.previewPic = function (input) {
    const errEl = $('pic-err');
    errEl.classList.remove('show');
    const file = input.files && input.files[0];
    if (!file) return;
    if (!/\.(jpe?g|png|webp)$/i.test(file.name)) {
      errEl.querySelector('span').textContent = 'Profile picture must be JPG, PNG or WEBP.';
      errEl.classList.add('show');
      input.value = '';
      return;
    }
    if (file.size > 2 * 1024 * 1024) {
      errEl.querySelector('span').textContent = 'File too large. Max 2MB.';
      errEl.classList.add('show');
      input.value = '';
      return;
    }
    const reader = new FileReader();
    reader.onload = function (e) {
      const img = $('pic-preview-img');
      img.src = e.target.result;
      img.style.display = 'block';
      $('pic-upload-btn').style.display = '';
    };
    reader.onerror = function () {
      errEl.querySelector('span').textContent = 'Could not read that file. Try another one.';
      errEl.classList.add('show');
    };
    reader.readAsDataURL(file);
  };

  window.uploadPic = async function () {
    const errEl = $('pic-err');
    const btn = $('pic-upload-btn');
    const label = $('pic-upload-label');
    const fileInput = $('pic-file-input');
    const file = fileInput.files && fileInput.files[0];
    if (!file || btn.disabled) return;

    btn.disabled = true;
    if (label) label.textContent = 'Uploading…';
    errEl.classList.remove('show');

    const fd = new FormData();
    fd.append('picture', file);

    try {
      const res = await fetch('../api/profile.php?action=upload_pic', { method: 'POST', body: fd });
      const data = await safeJson(res);
      if (!res.ok || !data) {
        errEl.querySelector('span').textContent = 'Upload failed. Please try again.';
        errEl.classList.add('show');
      } else if (data.success && data.path) {
        closePicModal();
        // The API returns a project-root-relative path (e.g. "uploads/profiles/x.jpg");
        // this page lives one folder down (student/ or teacher/), so it needs a
        // "../" prefix to resolve correctly. Missing this was the cause of the
        // broken-image icon right after upload.
        const url = '../' + data.path + '?t=' + Date.now();
        const fallbackInitial = (($('pf-fname').value.trim()[0] || '') + ($('pf-lname').value.trim()[0] || '')).toUpperCase() || '?';
        const avatarEl = $('avatar-display');
        if (avatarEl && avatarEl.tagName === 'IMG') {
          avatarEl.onerror = function () {
            this.replaceWith(Object.assign(document.createElement('div'), {
              className: 'avatar-initials', id: 'avatar-display', textContent: fallbackInitial
            }));
          };
          avatarEl.src = url;
        } else if (avatarEl) {
          const img = document.createElement('img');
          img.className = 'avatar-img'; img.id = 'avatar-display'; img.alt = 'Profile photo';
          img.onerror = function () {
            this.replaceWith(Object.assign(document.createElement('div'), {
              className: 'avatar-initials', id: 'avatar-display', textContent: fallbackInitial
            }));
          };
          img.src = url;
          avatarEl.replaceWith(img);
        }
        // Keep the topbar avatar (top-right) in sync too, for both Student and Teacher.
        if (typeof updateTopbarAvatar === 'function') {
          updateTopbarAvatar(url);
        }
        fileInput.value = '';
      } else {
        errEl.querySelector('span').textContent = (data && data.message) || 'Upload failed.';
        errEl.classList.add('show');
      }
    } catch (e) {
      errEl.querySelector('span').textContent = 'Network error. Please try again.';
      errEl.classList.add('show');
    }
    btn.disabled = false;
    if (label) label.textContent = 'Upload';
  };

  // ── Password visibility toggle ──────────────────────────────────────────
  window.togglePw = function (id, btn) {
    const inp = $(id);
    const showing = inp.type === 'text';
    inp.type = showing ? 'password' : 'text';
    btn.classList.toggle('is-visible', !showing);
    btn.setAttribute('aria-label', showing ? 'Show password' : 'Hide password');
  };

  // ── Drag & drop for picture modal ───────────────────────────────────────
  const dropZone = document.querySelector('.pic-drop-zone');
  if (dropZone) {
    dropZone.addEventListener('dragover', e => { e.preventDefault(); dropZone.style.borderColor = '<?= $roleColor ?>'; });
    dropZone.addEventListener('dragleave', () => dropZone.style.borderColor = '');
    dropZone.addEventListener('drop', e => {
      e.preventDefault();
      dropZone.style.borderColor = '';
      const file = e.dataTransfer.files && e.dataTransfer.files[0];
      if (file) {
        const dt = new DataTransfer();
        dt.items.add(file);
        $('pic-file-input').files = dt.files;
        previewPic($('pic-file-input'));
      }
    });
  }
})();
</script>