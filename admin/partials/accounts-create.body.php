<?php /* Accounts → Create account tab (body). One form for Student, Teacher, and Staff (role value 'Admin'). */
$caRoleLabels = ['Admin' => 'Staff'];   // display name only — the stored role stays 'Admin'
$caModuleLabels = ['admin' => 'Staff'];   // permission group shown as "Staff" (module key stays 'admin')
$caPermLabels = ['manage_admin_accounts' => 'Manage Staff Accounts'];   // display only — perm_key unchanged ?>
<style>
  .role-seg { display: inline-flex; width: fit-content !important; max-width: 100%; border: 1px solid var(--border); border-radius: var(--radius); overflow: hidden; background: var(--surface); }
  .role-seg button { display: inline-flex; align-items: center; gap: .45rem; padding: .6rem 1.15rem; font-size: .86rem; font-weight: 600;
                     border: 0; border-right: 1px solid var(--border); background: transparent; color: var(--text-secondary); cursor: pointer; font-family: inherit; }
  .role-seg button:last-child { border-right: 0; }
  .role-seg button:hover { color: var(--text-primary); background: var(--surface-hover); }
  .role-seg button.on { background: var(--primary-light); color: var(--primary); }
  .role-seg button:focus-visible { outline: 2px solid var(--primary); outline-offset: -2px; }
  .role-seg .ico { width: 16px; height: 16px; }
  .role-hint { font-size: .78rem; color: var(--text-muted); margin-top: .5rem; }
  @media (max-width: 480px) {
    /* 2 x 2 grid: four icon + label buttons do not fit on one row of a phone */
    .role-seg { display: grid; grid-template-columns: 1fr 1fr; width: 100% !important; }
    .role-seg button { justify-content: center; padding: .65rem .4rem; min-width: 0; white-space: nowrap; }
    .role-seg button:nth-child(2n) { border-right: 0; }
    .role-seg button:nth-child(-n+2) { border-bottom: 1px solid var(--border); }
    .role-seg button:last-child:nth-child(odd) { grid-column: 1 / -1; border-right: 0; }
  }
  .ca-hide { display: none !important; }
</style>

<div class="section-panel active">
  <?php render_admin_tabs(); ?>

  <div class="form-card">
    <div class="form-grid">
      <div class="form-error" id="ca-error"></div>

      <div class="form-group full" style="grid-column:1/-1;">
        <label class="form-label">Account type</label>
        <div class="role-seg" role="radiogroup" aria-label="Account type">
          <?php foreach ($createRoles as $role => $icon): ?>
            <button type="button" role="radio" data-role="<?= $role ?>"
                    class="<?= $role === $initialRole ? 'on' : '' ?>"
                    aria-checked="<?= $role === $initialRole ? 'true' : 'false' ?>"
                    onclick="caSetRole('<?= $role ?>')"><?= a_icon($icon) ?> <?= htmlspecialchars($caRoleLabels[$role] ?? $role) ?></button>
          <?php endforeach; ?>
        </div>
        <div class="role-hint" id="ca-role-hint"></div>
      </div>

      <hr class="form-divider">
      <div class="form-group">
        <label class="form-label">First Name *</label>
        <input class="form-input" type="text" id="ca-firstname" placeholder="e.g. Juan" maxlength="100" autocomplete="off" oninput="caCapName(this)" onblur="caCapName(this,true)">
      </div>
      <div class="form-group">
        <label class="form-label">Last Name *</label>
        <input class="form-input" type="text" id="ca-lastname" placeholder="e.g. Dela Cruz" maxlength="100" autocomplete="off" oninput="caCapName(this)" onblur="caCapName(this,true)">
      </div>
      <div class="form-group">
        <label class="form-label">Middle Name <span style="color:var(--ui-text-3, #aaa);font-weight:400;">(optional)</span></label>
        <input class="form-input" type="text" id="ca-middlename" placeholder="e.g. Santos" maxlength="100" autocomplete="off" oninput="caCapName(this)" onblur="caCapName(this,true)">
      </div>

      <hr class="form-divider">
      <div class="form-group" data-for="Student Teacher">
        <label class="form-label" id="ca-schoolid-label">LRN *</label>
        <input class="form-input" type="text" id="ca-schoolid" placeholder="e.g. 123456789012" inputmode="numeric" maxlength="12" autocomplete="off" oninput="this.value=this.value.replace(/[^0-9]/g,'')">
      </div>
      <div class="form-group">
        <label class="form-label">Email Address * <span style="color:var(--ui-text-3, #aaa);font-weight:400;">(used to sign in)</span></label>
        <input class="form-input" type="email" id="ca-email" placeholder="e.g. juan@arandia.edu.ph" autocomplete="off">
      </div>
      <div class="form-group">
        <label class="form-label">Contact Number *</label>
        <input class="form-input" type="text" id="ca-contact" placeholder="e.g. 09XXXXXXXXX" inputmode="numeric" maxlength="11" oninput="this.value=this.value.replace(/[^0-9]/g,'')">
      </div>
      <div class="form-group" data-for="Student Teacher">
        <label class="form-label" id="ca-section-label">Section / Grade Level</label>
        <select class="form-input" id="ca-section">
          <option value="">— Select Section —</option>
          <optgroup label="── Senior High School ──">
            <?php foreach ($shsSections as $s): ?>
              <option value="<?= htmlspecialchars($s) ?>"><?= htmlspecialchars($s) ?></option>
            <?php endforeach; ?>
          </optgroup>
          <optgroup label="── High School ──">
            <?php foreach ($hsSections as $s): ?>
              <option value="<?= htmlspecialchars($s) ?>"><?= htmlspecialchars($s) ?></option>
            <?php endforeach; ?>
          </optgroup>
        </select>
        <div id="ca-section-hint" style="font-size:.76rem;color:var(--ui-text-3, #8a94a6);margin-top:.35rem;">The student is enrolled automatically in all the subjects of this section / grade level.</div>
      </div>

      <hr class="form-divider">
      <div class="form-group" data-for="Student Teacher">
        <label class="form-label">Password <span style="color:var(--ui-text-3, #aaa);font-weight:400;">(automatic)</span></label>
        <input class="form-input" type="text" id="ca-autopw" readonly tabindex="-1" placeholder="Fills in from the surname + ID" autocomplete="off">
        <div style="font-size:.76rem;color:var(--ui-text-3, #8a94a6);margin-top:.35rem;">Surname + LRN / Employee ID, in lowercase with no spaces (e.g. macarubbo1233431). The user can change it later in My Profile.</div>
      </div>
      <div class="form-group" data-for="Admin">
        <label class="form-label">Password * <span style="color:var(--ui-text-3, #aaa);font-weight:400;">(min. 6 characters)</span></label>
        <input class="form-input" type="password" id="ca-password" placeholder="Create a password" autocomplete="new-password">
      </div>
      <div class="form-group" data-for="Admin">
        <label class="form-label">Confirm Password *</label>
        <input class="form-input" type="password" id="ca-confirm" placeholder="Re-enter password" autocomplete="new-password">
      </div>
      <div class="form-group">
        <label class="form-label">Status</label>
        <select class="form-input" id="ca-status">
          <option value="Active">Active</option>
          <option value="Inactive">Inactive</option>
        </select>
      </div>

      <?php if (isset($createRoles['Admin'])): ?>
        <div data-for="Admin" style="grid-column:1/-1;">
          <hr class="form-divider">
          <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:.75rem;flex-wrap:wrap;gap:.5rem;">
            <div style="font-size:.88rem;font-weight:700;color:var(--text-primary);"><?= a_icon('shield') ?> Staff Permissions</div>
            <span class="perm-counter" id="ca-perm-counter">0 of <?= $permTotal ?> selected</span>
          </div>
          <div style="font-size:.78rem;color:var(--text-muted);margin-bottom:1rem;">
            Choose what this staff member can see and do. Nothing is granted by default — you can change this anytime in Admins and Logs.
          </div>
          <?php foreach ($permCatalog as $module => $perms): ?>
            <div class="perm-module">
              <div class="perm-module-head">
                <div class="perm-module-title"><?= a_icon($moduleIcons[$module] ?? 'shield') ?> <?= htmlspecialchars($caModuleLabels[$module] ?? ucfirst($module)) ?></div>
                <div class="perm-module-toggles">
                  <a onclick="caToggleModule('<?= htmlspecialchars($module) ?>', true)">Select all</a>
                  <a onclick="caToggleModule('<?= htmlspecialchars($module) ?>', false)">Clear</a>
                </div>
              </div>
              <div class="perm-checklist">
                <?php foreach ($perms as $p): ?>
                  <label>
                    <input type="checkbox" class="ca-perm" data-module="<?= htmlspecialchars($module) ?>" value="<?= htmlspecialchars($p['perm_key']) ?>" onchange="caUpdatePermCounter()">
                    <?= htmlspecialchars($caPermLabels[$p['perm_key']] ?? $p['label']) ?>
                  </label>
                <?php endforeach; ?>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <div class="form-actions">
        <button class="btn btn-reset-form" onclick="caReset()">Reset</button>
        <button class="btn btn-primary" id="ca-submit" onclick="caSubmit(this)"><?= a_icon('check') ?> <span>Create account</span></button>
      </div>
    </div>
  </div>
</div>

<script>
  let caRole = <?= json_encode($initialRole) ?>;
  const caHints = {
    Student: 'Students can view their subjects, modules, quizzes, and grades.',
    Teacher: 'Teachers manage the subjects assigned to them.',
    Admin: 'Staff can only access the modules you grant below.',
  };
  const caLabels = { Admin: 'Staff' };   // display name only — the role sent to the API stays 'Admin'
  const caLabel = role => caLabels[role] || role;

  let caPrevRole = caRole;

  function caSetRole(role) {
    // Switching account type starts a fresh form, so nothing typed for a Student
    // carries over to a Teacher or Staff (clicking the SAME type keeps what was typed).
    if (role !== caPrevRole) caReset();
    caPrevRole = role;
    caRole = role;
    document.querySelectorAll('.role-seg button').forEach(b => {
      const on = b.dataset.role === role;
      b.classList.toggle('on', on);
      b.setAttribute('aria-checked', on ? 'true' : 'false');
    });
    document.querySelectorAll('[data-for]').forEach(el => {
      el.classList.toggle('ca-hide', !el.dataset.for.split(' ').includes(role));
    });
    document.getElementById('ca-schoolid-label').textContent = role === 'Teacher' ? 'Employee ID *' : 'LRN *';
    document.getElementById('ca-schoolid').placeholder = role === 'Teacher' ? 'e.g. 2026001' : 'e.g. 123456789012';
    // LRN = exactly 12 digits; Employee ID keeps a looser limit
    const sid = document.getElementById('ca-schoolid');
    sid.maxLength = role === 'Student' ? 12 : 30;
    if (role === 'Student' && sid.value.length > 12) sid.value = sid.value.slice(0, 12);
    document.getElementById('ca-section-label').textContent = role === 'Teacher' ? 'Department / Grade Level' : 'Section / Grade Level';
    document.getElementById('ca-section-hint').style.display = role === 'Student' ? '' : 'none';
    document.getElementById('ca-role-hint').textContent = caHints[role] || '';
    document.querySelector('#ca-submit span').textContent = 'Create ' + caLabel(role).toLowerCase() + ' account';
    document.getElementById('ca-error').style.display = 'none';
  }

  // Automatic password = surname + LRN / school ID (same rule as accountDefaultPassword() in api/accounts.php)
  function autoPw(last, id) {
    const s = (last || '').normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase().replace(/[^a-z0-9]/g, '');
    return s + (id || '').trim();
  }
  function caAutoPw() {
    const last = document.getElementById('ca-lastname').value, id = document.getElementById('ca-schoolid').value;
    return last.trim() && id.trim() ? autoPw(last, id) : '';
  }
  function caUpdateAutoPw() { document.getElementById('ca-autopw').value = caAutoPw(); }

  // Capital first letter of every word while typing ("juan dela cruz" -> "Juan Dela Cruz").
  // Only the first letter is raised, so names like "McDonald" are left alone.
  // Names take letters only (plus space . ' -): numbers and symbols are removed as the user types.
  const CA_NAME_BAD = /[^\p{L}\p{M} '’.\-]/gu;
  const CA_NAME_OK = /^\p{L}[\p{L}\p{M} '’.\-]*$/u;
  function caNameClean(str, tidy) {
    let v = String(str).replace(CA_NAME_BAD, '').replace(/^[ '’.\-]+/, '').replace(/ {2,}/g, ' ');
    v = v.replace(/(^|[ \-'’.])(\p{Ll})/gu, (m, a, b) => a + b.toUpperCase());
    return tidy ? v.trim() : v;
  }
  function caCapName(el, tidy) {
    const v = caNameClean(el.value, tidy);
    if (v !== el.value) {
      const pos = el.selectionStart ?? el.value.length;
      const newPos = caNameClean(el.value.slice(0, pos), false).length;
      el.value = v;
      try { el.setSelectionRange(newPos, newPos); } catch (e) { /* ignore */ }
    }
  }
  function caNameError(v, label, required) {
    if (!v) return required ? label + ' is required.' : '';
    if (!CA_NAME_OK.test(v)) return label + ' can only contain letters (no numbers or symbols).';
    if (required && (v.match(/\p{L}/gu) || []).length < 2) return label + ' must have at least 2 letters.';
    return '';
  }

  function caToggleModule(module, checked) {
    document.querySelectorAll(`.ca-perm[data-module="${module}"]`).forEach(cb => cb.checked = checked);
    caUpdatePermCounter();
  }

  function caUpdatePermCounter() {
    const el = document.getElementById('ca-perm-counter');
    if (!el) return;
    el.textContent = `${document.querySelectorAll('.ca-perm:checked').length} of ${document.querySelectorAll('.ca-perm').length} selected`;
  }

  function caReset() {
    ['ca-firstname', 'ca-lastname', 'ca-middlename', 'ca-schoolid', 'ca-email', 'ca-contact', 'ca-password', 'ca-confirm', 'ca-section']
      .forEach(id => document.getElementById(id).value = '');
    document.getElementById('ca-status').value = 'Active';
    document.querySelectorAll('.ca-perm').forEach(cb => cb.checked = false);
    document.getElementById('ca-error').style.display = 'none';
    caUpdatePermCounter();
    caUpdateAutoPw();
  }

  async function caSubmit(btn) {
    const isAdmin = caRole === 'Admin';
    const err = document.getElementById('ca-error');
    err.style.display = 'none';

    const payload = {
      first_name: document.getElementById('ca-firstname').value.trim(),
      last_name: document.getElementById('ca-lastname').value.trim(),
      middle_name: document.getElementById('ca-middlename').value.trim(),
      email: document.getElementById('ca-email').value.trim(),
      contact: document.getElementById('ca-contact').value.trim(),
      password: isAdmin ? document.getElementById('ca-password').value : caAutoPw(),   // Student / Teacher: surname + LRN
      confirm_password: isAdmin ? document.getElementById('ca-confirm').value : caAutoPw(),
      status: document.getElementById('ca-status').value,
    };
    ['first_name', 'last_name', 'middle_name'].forEach(k => { payload[k] = caNameClean(payload[k], true); });
    document.getElementById('ca-firstname').value = payload.first_name;
    document.getElementById('ca-lastname').value = payload.last_name;
    document.getElementById('ca-middlename').value = payload.middle_name;
    const fail = msg => { err.textContent = msg; err.style.display = 'block'; err.scrollIntoView({ block: 'center', behavior: 'smooth' }); };
    const nameErr = caNameError(payload.first_name, 'First name', true) || caNameError(payload.last_name, 'Last name', true) || caNameError(payload.middle_name, 'Middle name', false);
    if (nameErr) return fail(nameErr);
    if (caRole === 'Student' && !/^\d{12}$/.test(document.getElementById('ca-schoolid').value.trim())) return fail('LRN must be exactly 12 digits.');
    if (['Student', 'Teacher', 'Admin'].includes(caRole)) {
      if (!payload.contact) return fail('Contact number is required.');
      if (!/^09\d{9}$/.test(payload.contact)) return fail('Enter a valid 11-digit contact number (e.g. 09XXXXXXXXX).');
    }
    if (isAdmin) {
      payload.permissions = {};
      document.querySelectorAll('.ca-perm:checked').forEach(cb => payload.permissions[cb.value] = true);
    } else {
      payload.role = caRole;
      payload.school_id = document.getElementById('ca-schoolid').value.trim();
      payload.section_dept = document.getElementById('ca-section').value;
    }

    if (!await confirmAction(`Create this ${caLabel(caRole).toLowerCase()} account?`, { okText: 'Create account', danger: false })) return;

    if (btn) setBtnLoading(btn, 'Creating...');
    try {
      const res = await fetch('../api/accounts.php?action=' + (isAdmin ? 'create_admin' : 'create'), { method: 'POST', body: JSON.stringify(payload) });
      const data = await res.json();
      if (data.success) {
        showToast(`${caLabel(caRole)} account created`);
        setTimeout(() => location.href = isAdmin ? 'admins.php' : 'accounts.php?tab=create&role=' + encodeURIComponent(caRole), 1200);
      } else {
        err.textContent = data.message; err.style.display = 'block';
        if (btn) clearBtnLoading(btn);
      }
    } catch (e) {
      err.textContent = 'Network error. Try again.'; err.style.display = 'block';
      if (btn) clearBtnLoading(btn);
    }
  }

  ['ca-lastname', 'ca-schoolid'].forEach(id => document.getElementById(id).addEventListener('input', caUpdateAutoPw));
  caSetRole(caRole);
  caUpdateAutoPw();
</script>