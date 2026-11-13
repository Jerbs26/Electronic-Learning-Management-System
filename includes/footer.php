<?php
// ============================================================
//  Arandia College eLMS
//  File: includes/footer.php  (do not rename or move this file)
// ============================================================
?>
<?php $__role = $_SESSION['role'] ?? ''; ?>
<?php if ($__role === 'Admin' || $__role === 'Head Admin' || $__role === 'Admission'): ?>
    <?php if (!function_exists('a_icon')) {
        function a_icon($n)
        {
            return '';
        }
        function a_icon_map()
        {
            return [];
        }
    } ?>
    </main>



    <div class="footer-bar">© 2026 Arandia College eLMS — SHS &amp; HS <?= $__role === 'Admission' ? 'Admission Office' : 'Admin Panel' ?></div>
    </div>

    <!-- EDIT MODAL -->
    <div class="modal-overlay" id="editModal">
        <div class="modal" style="max-width:600px;">
            <div class="modal-title"><?= a_icon('edit') ?> Edit Account</div>
            <input type="hidden" id="edit-id">
            <div class="form-grid">
                <div class="form-group"><label class="form-label">First Name *</label><input class="form-input"
                        id="edit-firstname"></div>
                <div class="form-group"><label class="form-label">Last Name *</label><input class="form-input"
                        id="edit-lastname"></div>
                <div class="form-group"><label class="form-label">Middle Name</label><input class="form-input"
                        id="edit-middlename"></div>
                <div class="form-group" id="edit-role-group">
                    <label class="form-label" for="edit-role">Role *</label>
                    <select class="form-input" id="edit-role" onchange="editRoleUi()">
                        <option value="Student">Student</option>
                        <option value="Teacher">Teacher</option>
                        <?php if (function_exists('isHeadAdmin') && isHeadAdmin()): ?><option value="Admission">Admission</option><option value="Admin">Admin</option><?php endif; ?>
                    </select>
                </div>
                <div class="form-group"><label class="form-label" id="edit-schoolid-label" for="edit-schoolid">LRN *</label><input class="form-input"
                        id="edit-schoolid"></div>
                <div class="form-group"><label class="form-label">Email</label><input class="form-input" autocomplete="off" id="edit-email"
                        type="email"></div>
                <div class="form-group"><label class="form-label">Contact</label><input class="form-input"
                        id="edit-contact">
                </div>
                <div class="form-group" id="edit-section-group">
                    <label class="form-label" id="edit-section-label" for="edit-section">Section / Grade Level</label>
                    <select class="form-input" id="edit-section">
                        <option value="">— Select —</option>
                        <optgroup label="── Senior High School ──">
                            <?php require_once __DIR__ . '/sections.php'; if (!isset($shsSections, $hsSections)) { [$shsSections, $hsSections] = loadSectionLists($conn); } ?>
                            <?php foreach ($shsSections as $s): ?>
                                <option value="<?= htmlspecialchars($s) ?>"><?= htmlspecialchars($s) ?></option><?php endforeach; ?>
                        </optgroup>
                        <optgroup label="── High School ──">
                            <?php foreach ($hsSections as $s): ?>
                                <option value="<?= htmlspecialchars($s) ?>"><?= htmlspecialchars($s) ?></option><?php endforeach; ?>
                        </optgroup>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select class="form-input" id="edit-status">
                        <option value="Active">Active</option>
                        <option value="Inactive">Inactive</option>
                    </select>
                </div>
                <div class="form-group full">
                    <label class="form-label">New Password <span style="color:var(--ui-text-3, #aaa);font-weight:400;">(leave blank to keep
                            current)</span></label>
                    <input class="form-input" id="edit-password" type="password" placeholder="Enter new password" autocomplete="new-password">
                </div>
            </div>
            <div class="modal-actions">
                <button class="btn btn-reset-form" onclick="closeModal('editModal')">Cancel</button>
                <button class="btn btn-primary" onclick="submitEdit(this)">Save Changes</button>
            </div>
        </div>
    </div>

    <!-- DELETE MODAL -->
    <div class="modal-overlay" id="deleteModal">
        <div class="modal">
            <div class="modal-title"><?= a_icon('trash') ?> Delete Account</div>
            <p style="font-size:0.88rem;color:var(--ui-text-2, #555);line-height:1.6;">Are you sure you want to delete this account? This
                action
                cannot be undone.</p>
            <input type="hidden" id="delete-id">
            <div class="modal-actions">
                <button class="btn btn-reset-form" onclick="closeModal('deleteModal')">Cancel</button>
                <button class="btn btn-danger" onclick="submitDelete(this)">Delete Account</button>
            </div>
        </div>
    </div>

    <div class="toast" id="toast"></div>

    <!-- Styled confirmation modal — replaces the native browser confirm() popup -->
    <div class="modal-overlay" id="confirmModal">
        <div class="modal" style="max-width: 400px;">
            <div class="modal-title" id="confirm-title">Please confirm</div>
            <p id="confirm-message"
                style="color: var(--text-secondary, #64748b); font-size: .88rem; line-height: 1.5; margin: .25rem 0 1.5rem;">
            </p>
            <div class="modal-actions">
                <button class="btn btn-reset-form" id="confirm-cancel-btn">Cancel</button>
                <button class="btn btn-danger" id="confirm-ok-btn">Yes, continue</button>
            </div>
        </div>
    </div>

    <script>
        // ── Core shared helpers, isolated in their own script block ──────
        // Deliberately kept free of any PHP-interpolated database content
        // (names, titles, etc.) so a stray quote or special character
        // somewhere else on the page can never break these — every button
        // on every admin page depends on them.
        function showToast(msg, isError = false) {
            const t = document.getElementById('toast');
            if (!t) return;
            const iconCls = isError ? 'err' : 'ok';
            const iconGlyph = isError ? '\u2715' : '\u2713';
            t.innerHTML = '<span class="toast-icon ' + iconCls + '"></span><span></span>';
            t.querySelector('.toast-icon').textContent = iconGlyph;
            t.querySelector('span:last-child').textContent = String(msg || '');
            clearTimeout(window.__toastTimer);
            t.classList.add('show');
            window.__toastTimer = setTimeout(() => t.classList.remove('show'), 3500);
        }
        function closeModal(id) {
            const el = document.getElementById(id);
            if (el) el.classList.remove('show');
        }
        function setBtnLoading(btn, loadingText) {
            if (!btn || btn.dataset.loading === '1') return;
            btn.dataset.loading = '1';
            btn.dataset.originalHtml = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<span class="btn-spinner"></span>' + (loadingText || 'Please wait...');
        }
        function clearBtnLoading(btn) {
            if (!btn || btn.dataset.loading !== '1') return;
            btn.disabled = false;
            btn.innerHTML = btn.dataset.originalHtml;
            delete btn.dataset.originalHtml;
            delete btn.dataset.loading;
        }

        // Styled replacement for window.confirm(). Usage: if (!await confirmAction('Remove this enrollment?')) return;
        // opts: { title, okText, danger } — danger defaults to true (red button), pass danger:false for a neutral action.
        function confirmAction(message, opts = {}) {
            return new Promise(resolve => {
                const overlay = document.getElementById('confirmModal');
                if (!overlay) { resolve(window.confirm(message)); return; }
                document.getElementById('confirm-title').textContent = opts.title || 'Please confirm';
                document.getElementById('confirm-message').textContent = message;
                const okBtn = document.getElementById('confirm-ok-btn');
                const cancelBtn = document.getElementById('confirm-cancel-btn');
                okBtn.textContent = opts.okText || 'Yes, continue';
                okBtn.className = 'btn ' + (opts.danger === false ? 'btn-primary' : 'btn-danger');
                // opts.hideCancel: a notice with one button (OK)
                cancelBtn.style.display = opts.hideCancel ? 'none' : '';
                overlay.classList.add('show');
                function done(result) {
                    overlay.classList.remove('show');
                    cancelBtn.style.display = '';
                    okBtn.removeEventListener('click', onOk);
                    cancelBtn.removeEventListener('click', onCancel);
                    resolve(result);
                }
                function onOk() { done(true); }
                function onCancel() { done(false); }
                okBtn.addEventListener('click', onOk);
                cancelBtn.addEventListener('click', onCancel);
            });
        }
    </script>

    <script>
        const A_ICONS = <?= json_encode(a_icon_map(), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
        const aIco = n => (A_ICONS && A_ICONS[n]) || '';
        const panelNames = {
            dashboard: 'Dashboard',
            create: 'Create Account',
            accounts: 'All Accounts',
            assign: 'Assign Subjects',
            enroll: 'Enroll Students',
            announcements: 'Announcements',
            settings: 'Settings'
        };
        function showPanel(name, btn) {
            document.querySelectorAll('.section-panel').forEach(p => p.classList.remove('active'));
            document.querySelectorAll('.sidebar-link').forEach(l => l.classList.remove('active'));
            document.getElementById('panel-' + name).classList.add('active');
            if (btn) btn.classList.add('active');
            const el = document.getElementById('topbar-panel-name');
            if (el) el.textContent = panelNames[name] || name;
        }
        function filterTable() {
            const q = document.getElementById('search-input').value.toLowerCase();
            const role = document.getElementById('filter-role').value;
            const section = document.getElementById('filter-section').value;
            document.querySelectorAll('#accounts-tbody tr').forEach(tr => {
                const mQ = !q || tr.dataset.name?.includes(q) || tr.dataset.username?.includes(q) || tr.dataset.schoolid?.includes(q);
                const mR = !role || tr.dataset.role === role;
                const mS = !section || tr.dataset.section === section;
                tr.style.display = (mQ && mR && mS) ? '' : 'none';
            });
        }
        function resetForm() {
            ['f-firstname', 'f-lastname', 'f-middlename', 'f-schoolid', 'f-username',
                'f-email', 'f-contact', 'f-password', 'f-confirm'].forEach(id => document.getElementById(id).value = '');
            document.getElementById('f-role').value = '';
            document.getElementById('f-section').value = '';
            document.getElementById('f-status').value = 'Active';
            document.getElementById('form-error').style.display = 'none';
        }
        async function submitCreate(btn) {
            if (!await confirmAction('Are you sure you want to create this account?', { okText: 'Create account', danger: false })) return;
            const err = document.getElementById('form-error');
            err.style.display = 'none';
            const payload = {
                first_name: document.getElementById('f-firstname').value.trim(),
                last_name: document.getElementById('f-lastname').value.trim(),
                middle_name: document.getElementById('f-middlename').value.trim(),
                role: document.getElementById('f-role').value,
                school_id: document.getElementById('f-schoolid').value.trim(),
                username: document.getElementById('f-username')?.value.trim() || '',
                email: document.getElementById('f-email').value.trim(),
                contact: document.getElementById('f-contact').value.trim(),
                password: document.getElementById('f-password').value,
                confirm_password: document.getElementById('f-confirm').value,
                section_dept: document.getElementById('f-section').value,
                status: document.getElementById('f-status').value,
            };
            if (btn) setBtnLoading(btn, 'Creating...');
            try {
                const res = await fetch('../api/accounts.php?action=create', { method: 'POST', body: JSON.stringify(payload) });
                const data = await res.json();
                if (data.success) { showToast('Account created successfully!'); resetForm(); setTimeout(() => location.reload(), 1500); }
                else { err.textContent = data.message; err.style.display = 'block'; if (btn) clearBtnLoading(btn); }
            } catch (e) { err.textContent = 'Network error. Please try again.'; err.style.display = 'block'; if (btn) clearBtnLoading(btn); }
        }
        // opts.lockRole: the role can't be changed here (e.g. Admins and Logs → Admission Accounts)
        function openEdit(a, opts = {}) {
            document.getElementById('edit-role-group').style.display = opts.lockRole ? 'none' : '';
            console.log('openEdit called, a.id =', a.id, 'parsed =', parseInt(a.id, 10));
            document.getElementById('edit-id').value = parseInt(a.id, 10) || 0;
            document.getElementById('edit-firstname').value = a.first_name;
            document.getElementById('edit-lastname').value = a.last_name;
            document.getElementById('edit-middlename').value = a.middle_name || '';
            document.getElementById('edit-role').value = a.role;
            document.getElementById('edit-schoolid').value = a.school_id;
            document.getElementById('edit-email').value = a.email || '';
            document.getElementById('edit-contact').value = a.contact || '';
            document.getElementById('edit-section').value = a.section_dept || '';
            document.getElementById('edit-status').value = a.status;
            document.getElementById('edit-password').value = '';
            editRoleUi();
            document.getElementById('editModal').classList.add('show');
        }
        // Fields follow the account type: students have an LRN and a section;
        // teachers an Employee ID and a department / grade level;
        // Admission and Admin staff an Employee ID and no section.
        function editRoleUi() {
            const role = document.getElementById('edit-role').value;
            const staff = role === 'Admission' || role === 'Admin' || role === 'Head Admin';
            document.getElementById('edit-schoolid-label').textContent = role === 'Student' ? 'LRN *' : 'Employee ID *';
            document.getElementById('edit-section-group').style.display = staff ? 'none' : '';
            document.getElementById('edit-section-label').textContent = role === 'Teacher' ? 'Department / Grade Level' : 'Section / Grade Level';
            if (staff) document.getElementById('edit-section').value = '';
        }
        async function submitEdit(btn) {
            const id = Number(document.getElementById('edit-id').value);
            if (!id || isNaN(id)) { showToast('Could not read account ID. Please try again.', true); return; }
            const payload = {
                id: id,
                first_name: document.getElementById('edit-firstname').value.trim(),
                last_name: document.getElementById('edit-lastname').value.trim(),
                middle_name: document.getElementById('edit-middlename').value.trim(),
                role: document.getElementById('edit-role').value,
                school_id: document.getElementById('edit-schoolid').value.trim(),
                email: document.getElementById('edit-email').value.trim(),
                contact: document.getElementById('edit-contact').value.trim(),
                section_dept: ['Admission', 'Admin', 'Head Admin'].includes(document.getElementById('edit-role').value) ? '' : document.getElementById('edit-section').value,
                status: document.getElementById('edit-status').value,
                password: document.getElementById('edit-password').value,
            };
            if (btn) setBtnLoading(btn, 'Saving...');
            try {
                const res = await fetch('../api/accounts.php?action=update', { method: 'PUT', body: JSON.stringify(payload) });
                const data = await res.json();
                if (data.success) { closeModal('editModal'); showToast('Account updated!'); setTimeout(() => location.reload(), 1200); }
                else { showToast(data.message, true); if (btn) clearBtnLoading(btn); }
            } catch (e) { showToast('Network error. Please try again.', true); if (btn) clearBtnLoading(btn); }
        }
        function openDelete(id) { document.getElementById('delete-id').value = id; document.getElementById('deleteModal').classList.add('show'); }
        async function submitDelete(btn) {
            const id = document.getElementById('delete-id').value;
            if (btn) setBtnLoading(btn, 'Deleting...');
            try {
                const res = await fetch(`../api/accounts.php?action=delete&id=${id}`, { method: 'DELETE' });
                const data = await res.json();
                if (data.success) { closeModal('deleteModal'); showToast('Account deleted.'); setTimeout(() => location.reload(), 1200); }
                else { showToast(data.message, true); if (btn) clearBtnLoading(btn); }
            } catch (e) { showToast('Network error. Please try again.', true); if (btn) clearBtnLoading(btn); }
        }
        <?php if ($toast): ?>showToast(<?= json_encode($toast, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>, <?= $isError ? 'true' : 'false' ?>); <?php endif; ?>

        // ── Assign Subjects ───────────────────────────────────────────────────
        let allAssignments = [];

        async function loadAssignments() {
            try {
                const res = await fetch('../api/teacher_assignments.php?action=list');
                const data = await res.json();
                if (data.success) {
                    allAssignments = data.data;
                    renderAssignTable(allAssignments);
                }
            } catch (e) { }
        }

        async function loadTeacherAssignments() {
            const tid = document.getElementById('a-teacher').value;
            const name = document.getElementById('a-teacher').selectedOptions[0]?.text || '';
            const wrap = document.getElementById('assign-list-wrap');
            const nameEl = document.getElementById('assign-teacher-name');

            if (!tid) {
                nameEl.textContent = '';
                wrap.innerHTML = '<div class="empty-assign"><span>' + aIco('presentation') + '</span>Select a teacher to view their assignments.</div>';
                return;
            }
            nameEl.textContent = '— ' + name;
            wrap.innerHTML = '<div style="padding:1rem;color:var(--ui-text-3, #aaa);font-size:0.82rem;">Loading...</div>';
            try {
                const res = await fetch(`../api/teacher_assignments.php?action=list&teacher_id=${tid}`);
                const data = await res.json();
                if (!data.success || data.data.length === 0) {
                    wrap.innerHTML = '<div class="empty-assign"><span>' + aIco('inbox') + '</span>No assignments yet for this teacher.</div>';
                    return;
                }
                wrap.innerHTML = data.data.map(a => `
          <div class="assign-list-item">
            <div class="assign-item-info">
              <div class="assign-item-name">${a.course_code} — ${a.course_name}</div>
              <div class="assign-item-meta">${a.section} &nbsp;·&nbsp; ${a.school_year} ${a.semester} Sem</div>
            </div>
            <button class="btn btn-danger btn-sm" onclick="removeAssignment(${a.id})">Remove</button>
          </div>`).join('');
                // Also refresh global table
                allAssignments = allAssignments.filter(x => x.teacher_id != tid).concat(data.data);
                renderAssignTable(allAssignments);
            } catch (e) {
                wrap.innerHTML = '<div class="empty-assign"><span>' + aIco('alert') + '</span>Error loading assignments.</div>';
            }
        }

        function renderAssignTable(rows) {
            const tbody = document.getElementById('assign-all-tbody');
            if (!rows.length) {
                tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;color:var(--ui-text-3, #bbb);padding:2rem;">No assignments yet.</td></tr>';
                return;
            }
            tbody.innerHTML = rows.map(a => `
        <tr data-teacher="${a.teacher_name.toLowerCase()}" data-info="${(a.course_name + ' ' + a.section).toLowerCase()}">
          <td><strong>${a.teacher_name}</strong></td>
          <td>${a.course_code} — ${a.course_name}</td>
          <td>${a.section}</td>
          <td>${a.school_year}</td>
          <td>${a.semester} Sem</td>
          <td><button class="btn btn-danger btn-sm" onclick="removeAssignment(${a.id})">Remove</button></td>
        </tr>`).join('');
        }

        function filterAssignTable() {
            const q = document.getElementById('assign-search').value.toLowerCase();
            const t = document.getElementById('assign-filter-teacher').value.toLowerCase();
            document.querySelectorAll('#assign-all-tbody tr').forEach(tr => {
                const mQ = !q || tr.dataset.info?.includes(q) || tr.dataset.teacher?.includes(q);
                const mT = !t || tr.dataset.teacher?.includes(t);
                tr.style.display = (mQ && mT) ? '' : 'none';
            });
        }

        function updateAssignCount() {
            const n = document.querySelectorAll('.a-chk:checked').length;
            document.getElementById('a-count').textContent = n + ' subject' + (n !== 1 ? 's' : '') + ' selected';
        }
        function selectAssignGroup(prefix) {
            document.querySelectorAll('.a-' + prefix).forEach(cb => cb.checked = true);
            updateAssignCount();
        }
        function clearAssignSelection() {
            document.querySelectorAll('.a-chk').forEach(cb => cb.checked = false);
            updateAssignCount();
        }

        async function submitAssign() {
            const errEl = document.getElementById('a-error');
            errEl.style.display = 'none';
            const teacher_id = document.getElementById('a-teacher').value;
            const course_ids = [...document.querySelectorAll('.a-chk:checked')].map(cb => parseInt(cb.value));
            const section = document.getElementById('a-section').value;
            const school_year = document.getElementById('a-sy').value.trim();
            const semester = document.getElementById('a-sem').value;

            if (!teacher_id || !course_ids.length || !section) {
                errEl.textContent = 'Teacher, at least one subject, and section are required.';
                errEl.style.display = 'block'; return;
            }
            // Submit one assignment per checked subject
            let saved = 0, errors = [];
            for (const course_id of course_ids) {
                try {
                    const res = await fetch('../api/teacher_assignments.php?action=assign', {
                        method: 'POST',
                        body: JSON.stringify({ teacher_id, course_id, section, school_year, semester })
                    });
                    const data = await res.json();
                    if (data.success) saved++;
                    else errors.push(data.message);
                } catch (e) { errors.push('Network error'); }
            }
            if (saved > 0) {
                showToast(saved + ' assignment' + (saved !== 1 ? 's' : '') + ' saved!');
                clearAssignSelection();
                document.getElementById('a-section').value = '';
                loadTeacherAssignments();
                loadAssignments();
            }
            if (errors.length) {
                errEl.textContent = errors.join('; ');
                errEl.style.display = 'block';
            }
        }

        async function removeAssignment(id) {
            if (!await confirmAction('Remove this teacher-subject assignment?', { okText: 'Remove' })) return;
            try {
                const res = await fetch(`../api/teacher_assignments.php?action=remove&id=${id}`, { method: 'DELETE' });
                const data = await res.json();
                if (data.success) {
                    showToast('Assignment removed.');
                    allAssignments = allAssignments.filter(a => a.id != id);
                    renderAssignTable(allAssignments);
                    loadTeacherAssignments();
                } else showToast(data.message, true);
            } catch (e) { showToast('Network error.', true); }
        }

        // Auto-load assignments table on page load if assign panel is active
        <?php if (($panel ?? '') === 'assign'): ?>loadAssignments(); <?php endif; ?>
        <?php if (($panel ?? '') === 'enroll'): ?>loadAllEnrollments(); <?php endif; ?>

        // ── Enroll Students ───────────────────────────────────────────────────
        let allEnrollments = [];

        // Track checkbox counts
        document.querySelectorAll('.enr-chk').forEach(cb => {
            cb.addEventListener('change', updateEnrCount);
        });
        function updateEnrCount() {
            const n = document.querySelectorAll('.enr-chk:checked').length;
            document.getElementById('enr-count').textContent = n + ' subject' + (n !== 1 ? 's' : '') + ' selected';
        }
        function selectGroup(prefix) {
            document.querySelectorAll('.enr-' + prefix).forEach(cb => cb.checked = true);
            updateEnrCount();
        }
        function clearEnrSelection() {
            document.querySelectorAll('.enr-chk').forEach(cb => cb.checked = false);
            updateEnrCount();
        }

        async function loadAllEnrollments() {
            try {
                const res = await fetch('../api/enrollments.php?action=list');
                const data = await res.json();
                if (data.success) { allEnrollments = data.data; renderEnrTable(allEnrollments); }
            } catch (e) { }
        }

        async function loadStudentEnrollments() {
            const sid = document.getElementById('enr-student').value;
            const selEl = document.getElementById('enr-student');
            const name = selEl.selectedOptions[0]?.text || '';
            const wrap = document.getElementById('enr-list-wrap');
            const nameEl = document.getElementById('enr-student-name');

            if (!sid) {
                nameEl.textContent = '';
                wrap.innerHTML = '<div class="empty-assign"><span>' + aIco('graduation') + '</span>Select a student to view their enrolled subjects.</div>';
                return;
            }
            nameEl.textContent = '— ' + name.split(' (')[0];
            wrap.innerHTML = '<div style="padding:1rem;color:var(--ui-text-3, #aaa);font-size:0.82rem;">Loading...</div>';

            // Pre-check boxes for already enrolled subjects
            document.querySelectorAll('.enr-chk').forEach(cb => cb.checked = false);

            try {
                const res = await fetch(`../api/enrollments.php?action=list&student_id=${sid}`);
                const data = await res.json();

                if (!data.success || !data.data.length) {
                    wrap.innerHTML = '<div class="empty-assign"><span>' + aIco('inbox') + '</span>No subjects enrolled yet.</div>';
                    return;
                }

                // Mark enrolled checkboxes
                data.data.forEach(e => {
                    const cb = document.querySelector(`.enr-chk[value="${e.course_id}"]`);
                    if (cb) cb.checked = true;
                });
                updateEnrCount();

                wrap.innerHTML = data.data.map(e => `
          <div class="assign-list-item">
            <div class="assign-item-info">
              <div class="assign-item-name">${e.course_code} — ${e.course_name}</div>
              <div class="assign-item-meta">Enrolled: ${e.enrolled_at} &nbsp;·&nbsp;
                <span style="color:${e.enroll_status === 'Enrolled' ? '#00875a' : '#c0392b'}">${e.enroll_status}</span>
              </div>
            </div>
            <button class="btn btn-danger btn-sm" onclick="removeEnrollment(${e.id})">Remove</button>
          </div>`).join('');

                // Refresh global table
                allEnrollments = allEnrollments.filter(x => x.student_id != sid).concat(data.data);
                renderEnrTable(allEnrollments);
            } catch (e) {
                wrap.innerHTML = '<div class="empty-assign"><span>' + aIco('alert') + '</span>Error loading enrollments.</div>';
            }
        }

        function renderEnrTable(rows) {
            const tbody = document.getElementById('enr-all-tbody');
            if (!rows.length) {
                tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;color:var(--ui-text-3, #bbb);padding:2rem;">No enrollments yet.</td></tr>';
                return;
            }
            tbody.innerHTML = rows.map(e => `
        <tr data-student="${e.student_name.toLowerCase()}" data-info="${(e.course_name + ' ' + e.course_code).toLowerCase()}">
          <td><strong>${e.student_name}</strong></td>
          <td>${e.school_id}</td>
          <td>${e.section_dept || '—'}</td>
          <td>${e.course_name}</td>
          <td><span class="badge-role role-student">${e.course_code}</span></td>
          <td>${e.enrolled_at}</td>
          <td><button class="btn btn-danger btn-sm" onclick="removeEnrollment(${e.id})">Remove</button></td>
        </tr>`).join('');
        }

        function filterEnrTable() {
            const q = document.getElementById('enr-search').value.toLowerCase();
            const s = document.getElementById('enr-filter-student').value.toLowerCase();
            document.querySelectorAll('#enr-all-tbody tr').forEach(tr => {
                const mQ = !q || tr.dataset.info?.includes(q) || tr.dataset.student?.includes(q);
                const mS = !s || tr.dataset.student?.includes(s);
                tr.style.display = (mQ && mS) ? '' : 'none';
            });
        }

        async function submitEnrollment() {
            const errEl = document.getElementById('enr-error');
            errEl.style.display = 'none';
            const student_id = document.getElementById('enr-student').value;
            const course_ids = [...document.querySelectorAll('.enr-chk:checked')].map(cb => parseInt(cb.value));

            if (!student_id) {
                errEl.textContent = 'Please select a student.'; errEl.style.display = 'block'; return;
            }
            if (!course_ids.length) {
                errEl.textContent = 'Please select at least one subject.'; errEl.style.display = 'block'; return;
            }

            try {
                const res = await fetch('../api/enrollments.php?action=enroll', {
                    method: 'POST', body: JSON.stringify({ student_id, course_ids })
                });
                const data = await res.json();
                if (data.success) {
                    showToast(data.message);
                    loadStudentEnrollments();
                    loadAllEnrollments();
                } else {
                    errEl.textContent = data.message; errEl.style.display = 'block';
                }
            } catch (e) { errEl.textContent = 'Network error.'; errEl.style.display = 'block'; }
        }

        async function removeEnrollment(id) {
            if (!await confirmAction('Remove this enrollment? The student will lose access to this subject.', { okText: 'Remove enrollment' })) return;
            try {
                const res = await fetch(`../api/enrollments.php?action=remove&id=${id}`, { method: 'DELETE' });
                const data = await res.json();
                if (data.success) {
                    showToast('Enrollment removed.');
                    allEnrollments = allEnrollments.filter(e => e.id != id);
                    renderEnrTable(allEnrollments);
                    loadStudentEnrollments();
                } else showToast(data.message, true);
            } catch (e) { showToast('Network error.', true); }
        }

        // ── Announcements ────────────────────────────────────────────────────
        function escHtml(s) {
            return String(s || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
        }

        async function sendAnnouncement() {
            const errEl = document.getElementById('ann-err');
            errEl.style.display = 'none';
            const title = document.getElementById('ann-title').value.trim();
            const body = document.getElementById('ann-body').value.trim();
            const courseId = document.getElementById('ann-course').value;
            const audienceEl = document.getElementById('ann-audience');
            const audience = audienceEl ? audienceEl.value : 'Everyone';

            if (!title) { errEl.textContent = 'Title is required.'; errEl.style.display = 'block'; return; }
            if (!body) { errEl.textContent = 'Message is required.'; errEl.style.display = 'block'; return; }

            const btn = document.getElementById('ann-send-btn');
            setBtnLoading(btn, 'Posting...');

            try {
                const fd = new FormData();
                fd.append('title', title);
                fd.append('body', body);
                fd.append('course_id', courseId || '');
                fd.append('target_audience', audience);

                const res = await fetch('../api/announcements.php?action=create', { method: 'POST', body: fd });
                const data = await res.json();

                if (data.success) {
                    showToast('Announcement posted!');
                    setTimeout(() => location.reload(), 900);
                } else {
                    errEl.textContent = (data.message || 'Failed to post.');
                    errEl.style.display = 'block';
                    clearBtnLoading(btn);
                }
            } catch (e) {
                errEl.textContent = 'Network error. Please try again.';
                errEl.style.display = 'block';
                clearBtnLoading(btn);
            }
        }

        async function deleteAnn(id, btn) {
            if (!await confirmAction('Delete this announcement? Students will no longer see it.', { okText: 'Delete' })) return;
            try {
                const res = await fetch(`../api/announcements.php?action=delete&id=${id}`, { method: 'DELETE' });
                const data = await res.json();
                if (data.success) {
                    btn.closest('.ann-item').remove();
                    showToast('Announcement deleted.');
                } else {
                    showToast(data.message, true);
                }
            } catch (e) {
                showToast('Network error.', true);
            }
        }

        function filterAnn(q) {
            q = q.toLowerCase();
            document.querySelectorAll('.ann-item').forEach(item => {
                item.style.display = item.textContent.toLowerCase().includes(q) ? '' : 'none';
            });
        }
    </script>
    <?php require __DIR__ . '/footer_common.php'; ?>
</body>

    </html><?php elseif ($__role === 'Teacher'): ?>
    </main>


    <div class="footer-bar">© 2026 Arandia College eLMS — SHS &amp; HS Teacher Portal</div>
    </div><!-- end app-body -->
    <div class="toast" id="toast"></div>


    <!--  MODULE MODAL  -->
    <div class="modal-overlay" id="modModal">
        <div class="modal">
            <div class="modal-head">
                <h2 id="mod-modal-title">Add Module</h2>
                <button class="modal-close" aria-label="Close" onclick="closeModal('modModal')"><?= t_icon('x') ?></button>
            </div>
            <div class="modal-body">
                <div class="modal-err" id="mod-err"></div>
                <input type="hidden" id="mod-edit-id">
                <div class="form-grp"><label class="form-lbl">Subject *</label><select class="finput"
                        id="mod-course"></select></div>
                <div class="form-grp"><label class="form-lbl">Module Title *</label><input class="finput" type="text"
                        id="mod-title" placeholder="e.g. Week 1 — Introduction"></div>
                <div class="form-grp"><label class="form-lbl">Description</label><textarea class="finput" id="mod-desc"
                        rows="3" placeholder="Short description..."></textarea></div>
                <div class="form-2col">
                    <div class="form-grp"><label class="form-lbl">Week Number</label><input class="finput" type="number"
                            id="mod-week" min="1" max="20" placeholder="e.g. 1"></div>
                    <div class="form-grp"
                        style="flex-direction:row;align-items:center;gap:.5rem;padding-top:1.2rem;justify-content:flex-start">
                        <input type="checkbox" id="mod-published" style="width:18px;height:18px;accent-color:#003087">
                        <label class="form-lbl" for="mod-published" style="margin:0">Publish immediately</label>
                    </div>
                </div>
                <div class="form-grp">
                    <label class="form-lbl">Attach / Replace File <span style="color:var(--ui-text-3, #aaa);font-weight:400">(leave blank
                            to keep existing)</span></label>
                    <input class="finput" type="file" id="mod-file"
                        accept=".pdf,.jpg,.jpeg,.png">
                </div>
                <div id="mod-current-file" style="display:none;font-size:.75rem;color:var(--ui-text-3, #aaa);margin-top:-.4rem">
                    Current file: <a id="mod-current-file-link" href="#" target="_blank"
                        style="color:var(--ui-link, #003087);font-weight:700">View</a>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-gray" onclick="closeModal('modModal')">Cancel</button>
                <button class="btn btn-green" onclick="submitModule()"> Save Module</button>
            </div>
        </div>
    </div>

    <!--  ASSIGNMENT MODAL  -->
    <div class="modal-overlay" id="assignModal">
        <div class="modal">
            <div class="modal-head">
                <h2 id="assign-modal-title">Create Assignment</h2>
                <button class="modal-close" aria-label="Close"
                    onclick="closeModal('assignModal')"><?= t_icon('x') ?></button>
            </div>
            <div class="modal-body">
                <div class="modal-err" id="assign-err"></div>
                <input type="hidden" id="assign-edit-id">
                <div class="form-grp"><label class="form-lbl">Class *</label><select class="finput"
                        id="assign-course" onchange="mlLoad('assign')"></select></div>
                <style>.ai-src { display: flex; gap: .6rem; align-items: flex-start; padding: .7rem .9rem; margin-bottom: 1rem; border-radius: 10px; background: var(--ui-surface-2, #f4f7fc); border: 1px solid var(--ui-border-soft, #e6ebf2); font-size: .8rem; color: var(--text-light, #64748b); line-height: 1.45; } .ai-src b { display: block; color: var(--text-dark, #1a1a2e); font-size: .86rem; } .ai-src svg { flex-shrink: 0; margin-top: .1rem; color: #7b2d8b; } .ai-src.is-warn { background: #fff7e6; border-color: #f5d48a; color: #7a5b12; } .ai-src.is-warn svg { color: #b7791f; } [data-theme="dark"] .ai-src.is-warn, body.dark .ai-src.is-warn { background: rgba(245,180,60,.12); border-color: rgba(245,180,60,.35); color: #f3d38b; } [data-theme="dark"] .ai-src.is-warn b, body.dark .ai-src.is-warn b { color: #fde7b0; }
                .ml-ai-line { display: flex; gap: .75rem; align-items: flex-start; } .ml-ai-mod { flex: 1 1 auto; min-width: 0; } .ml-ai-mod .finput { width: 100%; } .ml-ai-btn { flex: 0 0 auto; width: auto; min-width: 0; } .ml-ai-btn .form-lbl { visibility: hidden; } .ml-ai-btn .btn { width: auto; height: 38px; padding: 0 1rem; white-space: nowrap; display: inline-flex; align-items: center; } .ml-ai-line > .ml-ai-mod[style*="none"] + .ml-ai-btn { margin-left: auto; } @media (max-width: 480px) { .ml-ai-line { flex-wrap: wrap; } .ml-ai-mod { flex: 1 1 100%; } .ml-ai-btn .form-lbl { display: none; } .ml-ai-btn { margin-left: 0 !important; } }
                .ml-locked { padding: .62rem .9rem; border: 1.5px solid var(--ui-border, #e0e4ee); border-radius: 10px; background: var(--ui-surface-2, #f8f9fc); color: var(--ui-text, #1a1a2e); font-weight: 400; font-size: .9rem; overflow-wrap: anywhere; }
                .ml-hint { font-size: .74rem; color: var(--text-light, #64748b); margin-top: .3rem; line-height: 1.4; }</style>
                <div class="ml-ai-line">
                <div class="form-grp ml-ai-mod" id="assign-module-wrap"><label class="form-lbl" for="assign-module">Module</label><select class="finput"
                        id="assign-module"><option value="auto">Pick automatically</option></select>
                    <div class="ml-hint" id="assign-module-hint"></div></div>
                    <div class="form-grp ml-ai-btn" id="assign-ai-row"><label class="form-lbl">&nbsp;</label>
                        <button type="button" class="btn btn-purple" onclick="openAiAssignModal()" title="Let AI draft it, then review and edit">Draft with AI</button></div>
                </div>
                <div class="form-grp"><label class="form-lbl">Assignment Title *</label><input class="finput" type="text"
                        id="assign-title" placeholder="e.g. Research Paper — Chapter 1"></div>
                <div class="form-grp"><label class="form-lbl">Instructions / Description</label><textarea class="finput"
                        id="assign-desc" rows="3" placeholder="Detailed instructions..."></textarea></div>
                <div class="form-2col">
                    <div class="form-grp"><label class="form-lbl">Due Date</label><input class="finput"
                            type="datetime-local" id="assign-due"></div>
                    <div class="form-grp"><label class="form-lbl">Points</label><input class="finput" type="number"
                            id="assign-score" value="100" min="1"></div>
                </div>
                <div class="form-grp">
                    <label class="form-lbl" for="assign-max-attempts">Max Attempts <span style="color:var(--ui-text-3, #aaa);font-weight:400">(leave blank for unlimited)</span></label>
                    <input class="finput" type="number" id="assign-max-attempts" placeholder="e.g. 3" min="1">
                </div>
                <div class="form-grp">
                    <label class="form-lbl">Attach / Replace File <span style="color:var(--ui-text-3, #aaa);font-weight:400">(optional — PDF, JPG, or PNG; up to 20 MB)</span></label>
                    <input class="finput" type="file" id="assign-file" accept=".pdf,.jpg,.jpeg,.png">
                </div>
                <div id="assign-current-file" style="display:none;font-size:.75rem;color:var(--ui-text-3, #aaa);margin-top:-.4rem">
                    Current file: <a id="assign-current-file-link" href="#" target="_blank"
                        style="color:var(--ui-link, #003087);font-weight:700">View</a>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-gray" onclick="closeModal('assignModal')">Cancel</button>
                <button class="btn btn-green" id="assign-save-btn" onclick="submitAssignment()">Publish assignment</button>
            </div>
        </div>
    </div>

    <!--  SUBMISSIONS / GRADING MODAL  -->
    <div class="modal-overlay" id="submissionsModal">
        <div class="modal" style="max-width:760px">
            <div class="modal-head">
                <h2 id="submissions-title">Submissions</h2>
                <button class="modal-close" aria-label="Close"
                    onclick="closeModal('submissionsModal')"><?= t_icon('x') ?></button>
            </div>
            <div class="modal-body" id="submissions-body" style="padding:0">Loading...</div>
        </div>
    </div>

    <!--  QUIZ MODAL  -->
    <!-- STEP 2: Quiz Info + Question Builder -->
    <div class="modal-overlay" id="quizModal">
        <div class="modal" style="max-width:800px">
            <div class="modal-head">
                <h2 id="quiz-modal-title">Create Quiz</h2>
                <button class="modal-close" aria-label="Close" onclick="closeModal('quizModal')"><?= t_icon('x') ?></button>
            </div>
            <div class="modal-body">
                <div class="modal-err" id="quiz-err"></div>
                <input type="hidden" id="quiz-edit-id">
                <input type="hidden" id="quiz-question-count">
                <input type="hidden" id="quiz-points-per-question">
                <div class="form-grp"><label class="form-lbl">Subject *</label><select class="finput"
                        id="quiz-course" onchange="mlLoad('quiz')"></select></div>
                <div class="ml-ai-line">
                <div class="form-grp ml-ai-mod" id="quiz-module-wrap"><label class="form-lbl" for="quiz-module">Module</label><select class="finput"
                        id="quiz-module"><option value="auto">Pick automatically</option></select>
                    <div class="ml-hint" id="quiz-module-hint"></div></div>
                    <div class="form-grp ml-ai-btn" id="quiz-ai-row"><label class="form-lbl">&nbsp;</label>
                        <button type="button" class="btn btn-purple" onclick="openAiQuizModal()" title="Let AI draft it, then review and edit">Draft with AI</button></div>
                </div>
                <div class="form-grp"><label class="form-lbl">Quiz Title *</label><input class="finput" type="text"
                        id="quiz-title" placeholder="e.g. Chapter 1 Quiz"></div>
                <div class="form-grp"><label class="form-lbl" for="quiz-desc">Instructions / Description *</label><textarea class="finput"
                        id="quiz-desc" rows="2" maxlength="5000" placeholder="e.g. Answer all questions. No notes allowed."></textarea></div>
                <div class="form-2col">
                    <div class="form-grp"><label class="form-lbl">Time Limit (minutes)</label><input class="finput"
                            type="number" id="quiz-time" min="1" placeholder="blank = unlimited"></div>
                    <div class="form-grp"><label class="form-lbl" for="quiz-maxscore">Max Score *</label><input class="finput" type="text"
                            inputmode="numeric" pattern="[0-9]*" maxlength="5" autocomplete="off" id="quiz-maxscore" value="100" placeholder="e.g. 100"
                            oninput="quizDigits(this, 5)"></div>
                    <div class="form-grp"><label class="form-lbl" for="quiz-passing">Passing Score (%) *</label><input class="finput"
                            type="text" inputmode="numeric" pattern="[0-9]*" maxlength="3" autocomplete="off" id="quiz-passing" placeholder="e.g. 75"
                            oninput="quizDigits(this, 3)"></div>
                    <div class="form-grp"><label class="form-lbl" for="quiz-open">Open At *</label><input class="finput" type="datetime-local"
                            id="quiz-open"></div>
                    <div class="form-grp"><label class="form-lbl" for="quiz-close">Close At *</label><input class="finput"
                            type="datetime-local" id="quiz-close"></div>
                </div>
                <div style="display:flex;gap:1.5rem;flex-wrap:wrap;margin-bottom:.5rem">
                    <label
                        style="display:flex;align-items:center;gap:.4rem;cursor:pointer;font-size:.83rem;font-weight:600">
                        <input type="checkbox" id="quiz-show-results" checked onchange="toggleShowCorrect()"
                            style="accent-color:#003087">
                        Show score/result immediately after submit
                    </label>
                    <label style="display:flex;align-items:center;gap:.4rem;cursor:pointer;font-size:.83rem;font-weight:600"
                        id="quiz-show-correct-wrap">
                        <input type="checkbox" id="quiz-show-correct" style="accent-color:#003087">
                        Also reveal correct answers
                    </label>
                </div>

                <div style="border-top:1px solid var(--ui-border-soft, #f0f2f5);padding-top:1rem;margin-top:.25rem">
                    <div style="display:flex;align-items:center;gap:1.25rem;margin-bottom:1rem;flex-wrap:wrap">
                        <span style="font-family:'Nunito',sans-serif;font-weight:800;font-size:.88rem;color:var(--ui-text, #1a1a2e)">Quiz
                            Type:</span>
                        <label
                            style="display:flex;align-items:center;gap:.4rem;cursor:pointer;font-size:.83rem;font-weight:600">
                            <input type="radio" name="quiz-mode" value="questions" checked onchange="toggleQuizMode()"
                                style="accent-color:#003087">
                            Question Builder (auto-graded)
                        </label>
                        <label
                            style="display:flex;align-items:center;gap:.4rem;cursor:pointer;font-size:.83rem;font-weight:600">
                            <input type="radio" name="quiz-mode" value="file" onchange="toggleQuizMode()"
                                style="accent-color:#003087">
                            File Upload (teacher grades)
                        </label>
                    </div>

                    <div id="quiz-questions-section">
                        <div
                            style="display:flex;align-items:center;justify-content:space-between;margin-bottom:.75rem;flex-wrap:wrap;gap:.5rem">
                            <span id="q-progress-label" style="font-size:.8rem;font-weight:700;color:var(--ui-link, #003087)">Questions: 0/0
                                completed</span>
                            <div style="display:flex;gap:.4rem">
                                <button class="btn btn-blue btn-sm" onclick="addQuestion()">+ Add Question</button>
                            </div>
                        </div>
                        <div id="questions-wrap"></div>
                    </div>

                    <div id="quiz-file-section" style="display:none">
                        <div style="background:var(--ui-surface-2, #f8f9fc);border-radius:12px;padding:1.25rem;border:1.5px dashed var(--ui-border, #e0e4ee)">
                            <div style="font-size:.84rem;font-weight:700;color:var(--ui-text, #1a1a2e);margin-bottom:.35rem"> Upload
                                Quiz File</div>
                            <div style="font-size:.75rem;color:var(--ui-text-3, #888);margin-bottom:.9rem">Students download this file,
                                complete it, then upload their work. You manually enter scores in <strong>Grade
                                    Submissions</strong>.</div>
                            <div class="form-grp" style="margin-bottom:.5rem">
                                <label class="form-lbl">Quiz File (PDF, JPG, or PNG; up to 20 MB) *</label>
                                <input class="finput" type="file" id="quiz-file"
                                    accept=".pdf,.jpg,.jpeg,.png">
                            </div>
                            <div id="quiz-current-file"
                                style="display:none;font-size:.75rem;color:var(--ui-text-3, #aaa);margin-bottom:.5rem">
                                Current: <a id="quiz-current-file-link" href="#" target="_blank"
                                    style="color:var(--ui-link, #003087);font-weight:700">View file</a>
                            </div>
                            <div
                                style="padding:.6rem .9rem;background:#fffbe6;border-radius:8px;font-size:.75rem;color:#b38600;font-weight:600">
                                <?= t_icon('alert') ?> No auto-grading — you must enter scores manually in Results &amp;
                                Grading.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-gray" onclick="closeModal('quizModal')">Cancel</button>
                <button class="btn btn-gray" onclick="submitQuiz(false)">Save as Draft</button>
                <button class="btn btn-green" onclick="submitQuiz(true)"> Publish Quiz</button>
            </div>
        </div>
    </div>

    <!-- AI Assignment Generator -->
    <div class="modal-overlay" id="aiAssignModal">
        <div class="modal" style="max-width:520px">
            <div class="modal-head">
                <h2> Generate Assignment with AI</h2>
                <button class="modal-close" aria-label="Close"
                    onclick="closeModal('aiAssignModal')"><?= t_icon('x') ?></button>
            </div>
            <div class="modal-body">
                <div class="modal-err" id="ai-assign-err"></div>
                <div class="ai-src" id="ai-assign-src" hidden></div>
                <div class="form-grp">
                    <label class="form-lbl" id="ai-assign-topic-lbl">Topic / Learning Goal *</label>
                    <textarea class="finput" id="ai-assign-topic" rows="4"
                        placeholder="e.g. Research paper on the causes of the Philippine Revolution, 1000-1500 words, with citations."></textarea>
                </div>
                <div class="form-grp">
                    <label class="form-lbl">Type of Work</label>
                    <select class="finput" id="ai-assign-kind">
                        <option value="essay">Essay / Research Paper</option>
                        <option value="worksheet">Worksheet / Problem Set</option>
                        <option value="project">Project / Output</option>
                        <option value="reflection">Reflection Paper</option>
                        <option value="lab report">Lab Report</option>
                    </select>
                </div>
                <div
                    style="padding:.6rem .9rem;background:#f1e8ff;border-radius:8px;font-size:.75rem;color:#7b2d8b;font-weight:600">
                    This will fill in the Title, Instructions, and a suggested Max Score below — review and edit before
                    saving.
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-gray" onclick="closeModal('aiAssignModal')">Cancel</button>
                <button class="btn btn-purple" id="ai-assign-generate-btn" onclick="generateAiAssignment()">
                    Generate</button>
            </div>
        </div>
    </div>

    <!-- AI Quiz Generator -->
    <div class="modal-overlay" id="aiQuizModal">
        <div class="modal" style="max-width:560px">
            <div class="modal-head">
                <h2> Generate Questions with AI</h2>
                <button class="modal-close" aria-label="Close"
                    onclick="closeModal('aiQuizModal')"><?= t_icon('x') ?></button>
            </div>
            <div class="modal-body">
                <div class="modal-err" id="ai-quiz-err"></div>
                <div class="ai-src" id="ai-quiz-src" hidden></div>
                <div class="form-grp">
                    <label class="form-lbl" id="ai-quiz-topic-lbl">Topic / Content *</label>
                    <textarea class="finput" id="ai-quiz-topic" rows="5"
                        placeholder="e.g. Photosynthesis — light-dependent and light-independent reactions, chlorophyll, inputs and outputs. Or paste a short outline / notes here."></textarea>
                </div>
                <div class="form-2col">
                    <div class="form-grp"><label class="form-lbl">Number of Questions</label>
                        <input class="finput" type="number" id="ai-quiz-count" min="1" max="50" value="10">
                    </div>
                </div>
                <div class="form-grp">
                    <label class="form-lbl">Question Types (choose at least one)</label>
                    <div style="display:flex;gap:1.25rem;flex-wrap:wrap;margin-top:.4rem">
                        <label
                            style="display:flex;align-items:center;gap:.4rem;cursor:pointer;font-size:.83rem;font-weight:600">
                            <input type="checkbox" class="ai-quiz-type" value="multiple_choice" checked
                                style="accent-color:#003087"> Multiple Choice
                        </label>
                        <label
                            style="display:flex;align-items:center;gap:.4rem;cursor:pointer;font-size:.83rem;font-weight:600">
                            <input type="checkbox" class="ai-quiz-type" value="true_false" style="accent-color:#003087">
                            True / False
                        </label>
                        <label
                            style="display:flex;align-items:center;gap:.4rem;cursor:pointer;font-size:.83rem;font-weight:600">
                            <input type="checkbox" class="ai-quiz-type" value="essay" style="accent-color:#003087">
                            Identification
                        </label>
                        <label
                            style="display:flex;align-items:center;gap:.4rem;cursor:pointer;font-size:.83rem;font-weight:600"
                            title="Open-ended question — you grade it yourself after students submit">
                            <input type="checkbox" class="ai-quiz-type" value="long_essay" style="accent-color:#003087">
                            Essay (teacher grades)
                        </label>
                    </div>
                </div>
                <div
                    style="padding:.6rem .9rem;background:#f1e8ff;border-radius:8px;font-size:.75rem;color:#7b2d8b;font-weight:600">
                    This will replace any questions currently in the builder with the AI's draft — review and edit before
                    publishing.
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-gray" onclick="closeModal('aiQuizModal')">Cancel</button>
                <button class="btn btn-purple" id="ai-quiz-generate-btn" onclick="generateAiQuiz()"> Generate</button>
            </div>
        </div>
    </div>

    <!--  RESULTS & GRADING MODAL  -->
    <div class="modal-overlay" id="resultsModal">
        <div class="modal" style="max-width:760px">
            <div class="modal-head">
                <h2 id="results-title">Results &amp; Grading</h2>
                <button class="modal-close" aria-label="Close"
                    onclick="closeModal('resultsModal')"><?= t_icon('x') ?></button>
            </div>
            <div class="modal-body" id="results-body" style="padding:0">Loading...</div>
        </div>
    </div>

    <script>
        // Dark mode — persisted in localStorage, applied on next load by the
        // small inline script at the top of includes/header.php (so there's
        // no flash of the light theme before this file even loads).
        function toggleDarkMode() {
            const root = document.documentElement;
            const isDark = root.getAttribute('data-theme') === 'dark';
            if (isDark) root.removeAttribute('data-theme');
            else root.setAttribute('data-theme', 'dark');
            try { localStorage.setItem('elms_theme', isDark ? 'light' : 'dark'); } catch (e) { /* localStorage unavailable — the toggle still works for this page view */ }
        }

        /*  ICONS (SVG, same set as sidebar)  */
        const T_ICONS = <?= json_encode(t_icon_map(), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
        function tico(n) { return T_ICONS[n] || ''; }

        /*  UTILITIES  */
        function showToast(msg, err = false) { const t = document.getElementById('toast'); t.textContent = msg; t.style.background = err ? '#c0392b' : '#003087'; t.classList.add('show'); setTimeout(() => t.classList.remove('show'), 3500); }
        function closeModal(id) { document.getElementById(id).classList.remove('show'); }
        function openModal(id) { document.getElementById(id).classList.add('show'); }
        function showPanel(name, btn) { document.querySelectorAll('.section-panel').forEach(p => p.classList.remove('active')); document.querySelectorAll('.sidebar-link').forEach(l => l.classList.remove('active')); document.getElementById('panel-' + name)?.classList.add('active'); if (btn) btn.classList.add('active'); const el = document.getElementById('topbar-panel-name'); if (el && btn) el.textContent = btn.textContent.trim(); }
        function escHtml(s) { return String(s || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;'); }
        function emptyCard(icon, msg) { return `<div style="background:var(--ui-surface, white);border-radius:16px;box-shadow:0 2px 14px rgba(0,0,0,.07);padding:3rem;text-align:center;color:var(--ui-text-3, #bbb)"><div style="font-size:2.5rem;margin-bottom:.5rem">${icon}</div><div>${msg}</div></div>`; }
        function loadingCard() { return '<div style="padding:2rem;text-align:center;color:var(--ui-text-3, #aaa)">Loading...</div>'; }

        /*  ACCORDION  */
        function toggleBlock(id) { const b = document.getElementById(id); const c = document.getElementById('chev-' + id); const o = b.classList.toggle('open'); c.classList.toggle('open', o); }
        function toggleStu(id) { toggleBlock(id); }
        function filterAcc(blockSel, rowSel, q, filterSelId) {
            q = (q || '').toLowerCase();
            const fv = filterSelId ? (document.getElementById(filterSelId)?.value || '') : '';
            document.querySelectorAll(blockSel).forEach(block => {
                const ba = block.dataset.yr || block.dataset.sec || '';
                if (fv && ba !== fv) { block.style.display = 'none'; return; }
                block.style.display = ''; let any = false;
                block.querySelectorAll(rowSel).forEach(row => {
                    const mQ = !q || (row.dataset.title || '').includes(q) || (row.dataset.yr || '').toLowerCase().includes(q);
                    row.style.display = mQ ? '' : 'none'; if (mQ) any = true;
                });
                if (q && !any) block.style.display = 'none';
                else if (q && any) { block.querySelector('.acc-body,.stu-body')?.classList.add('open'); block.querySelector('.acc-chevron,.stu-chevron')?.classList.add('open'); }
            });
        }
        function filterStudents(q) {
            q = (q || '').toLowerCase(); const sf = document.getElementById('stu-filter-sec')?.value || '';
            document.querySelectorAll('.stu-block').forEach(block => {
                const bs = block.dataset.sec || '';
                if (sf && bs !== sf.toLowerCase()) { block.style.display = 'none'; return; }
                block.style.display = ''; let any = false;
                block.querySelectorAll('.stu-row').forEach(row => { const mQ = !q || (row.dataset.name || '').includes(q); row.style.display = mQ ? '' : 'none'; if (mQ) any = true; });
                if (q) { if (any) { block.querySelector('.stu-body')?.classList.add('open'); block.querySelector('.stu-chevron')?.classList.add('open'); } else block.style.display = 'none'; }
            });
        }

        /*  ANNOUNCEMENTS  */
        // Toggle all subject checkboxes
        function toggleAllSubjects(masterCb) {
            document.querySelectorAll('.ann-subject-cb').forEach(cb => cb.checked = masterCb.checked);
        }

        async function sendAnnouncement() {
            const errEl = document.getElementById('ann-err');
            errEl.style.display = 'none';

            // Collect checked subjects
            const checked = [...document.querySelectorAll('.ann-subject-cb:checked')];
            const title = document.getElementById('ann-title').value.trim();
            const body = document.getElementById('ann-body').value.trim();

            if (checked.length === 0) { errEl.textContent = 'Please select at least one subject.'; errEl.style.display = 'block'; return; }
            if (!title) { errEl.textContent = 'Title is required.'; errEl.style.display = 'block'; return; }
            if (!body) { errEl.textContent = 'Message cannot be empty.'; errEl.style.display = 'block'; return; }

            const btn = document.getElementById('ann-send-btn');
            btn.disabled = true;
            btn.textContent = checked.length > 1 ? `Sending to ${checked.length} subjects…` : 'Sending…';

            const annList = document.getElementById('ann-list');
            let successCount = 0;
            let errors = [];

            // Send one API call per selected subject
            for (const cb of checked) {
                const courseId = cb.value;
                const courseLabel = cb.dataset.label;
                try {
                    const res = await fetch('../api/announcements.php?action=create', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ course_id: courseId, title, body })
                    });
                    const data = await res.json();
                    if (data.success) {
                        successCount++;
                        // Prepend to list
                        const emptyEl = annList.querySelector('.card-panel');
                        if (emptyEl) emptyEl.remove();
                        const div = document.createElement('div');
                        div.className = 'ann-item';
                        div.dataset.course = courseId;
                        div.innerHTML = `
                            <div class="ann-item-title">${escHtml(title)}</div>
                            <div class="ann-item-body">${escHtml(body)}</div>
                            <div class="ann-item-meta">
                                <span class="badge badge-blue">${escHtml(courseLabel)}</span>
                                <span> Just now</span>
                                <button onclick="deleteAnnouncement(${data.id}, this)"
                                    style="background:none;border:none;color:#c0392b;font-size:.72rem;font-weight:700;cursor:pointer;padding:0"> Delete</button>
                            </div>`;
                        annList.insertBefore(div, annList.firstChild);
                    } else {
                        errors.push(courseLabel + ': ' + data.message);
                    }
                } catch (e) {
                    errors.push(courseLabel + ': Network error');
                }
            }

            // Reset form
            document.getElementById('ann-title').value = '';
            document.getElementById('ann-body').value = '';
            document.querySelectorAll('.ann-subject-cb').forEach(cb => cb.checked = false);
            document.getElementById('ann-select-all').checked = false;

            if (successCount > 0) {
                showToast(successCount > 1
                    ? `Announcement sent to ${successCount} subjects!`
                    : 'Announcement sent!');
            }
            if (errors.length > 0) {
                errEl.textContent = 'Some failed: ' + errors.join('; ');
                errEl.style.display = 'block';
            }

            btn.disabled = false; btn.textContent = 'Send Announcement';
        }

        async function deleteAnnouncement(id, btn) {
            if (!confirm('Delete this announcement? Students will no longer see it.')) return;
            try {
                const res = await fetch(`../api/announcements.php?action=delete&id=${id}`, { method: 'DELETE' });
                const data = await res.json();
                if (data.success) {
                    btn.closest('.ann-item').remove();
                    showToast('Announcement deleted.');
                    if (!document.querySelector('#ann-list .ann-item')) {
                        document.getElementById('ann-list').innerHTML = `
                            <div class="card-panel"><div class="card-panel-body">
                                <div class="empty-state"><span class="empty-icon"></span>No announcements sent yet.</div>
                            </div></div>`;
                    }
                } else {
                    showToast('Could not delete.', true);
                }
            } catch (e) {
                showToast('Network error.', true);
            }
        }

        function filterAnnouncements() {
            const cid = document.getElementById('ann-filter-course').value;
            document.querySelectorAll('#ann-list .ann-item').forEach(item => {
                item.style.display = (!cid || item.dataset.course == cid) ? '' : 'none';
            });
        }

        /*  SUBJECTS  */
        const mySubjectsJS = <?= json_encode(array_values(array_unique(array_map(function ($s) {
            return ['id' => $s['course_id'], 'label' => $s['course_code'] . ' — ' . $s['course_name'] . ' (' . $s['section'] . ')'];
        }, $mySubjects), SORT_REGULAR))) ?>;
        function populateCourseSelect(selId) { const sel = document.getElementById(selId); sel.innerHTML = '<option value="">— Choose Subject —</option>'; mySubjectsJS.forEach(s => { const o = document.createElement('option'); o.value = s.id; o.textContent = s.label; sel.appendChild(o); }); }

        /*  MODULES  */
        function moduleRow(m) {
            return `<div style="display:grid;grid-template-columns:1fr 65px 80px 80px 165px;gap:.5rem;align-items:center;padding:.85rem 1.25rem;border-bottom:1px solid var(--ui-border-soft, #f8f9fc);transition:background .15s" onmouseover="this.style.background='#f6f9ff'" onmouseout="this.style.background=''">
        <div style="display:flex;align-items:center;gap:.7rem">
            <div style="width:32px;height:32px;border-radius:8px;background:${m.published == '1' ? '#e8f0ff' : '#f5f5f5'};display:flex;align-items:center;justify-content:center;font-size:.95rem;flex-shrink:0">${m.published == '1' ? tico('folder') : tico('quiz')}</div>
            <div><div style="font-size:.84rem;font-weight:700;color:var(--ui-text, #1a1a2e)">${escHtml(m.title)}</div>${m.description ? `<div style="font-size:.7rem;color:var(--ui-text-3, #aaa);margin-top:.04rem">${escHtml(m.description)}</div>` : ''}</div>
        </div>
        <div style="font-size:.75rem;color:var(--ui-text-2, #666);text-align:center">${m.week_number ? 'Wk ' + m.week_number : '—'}</div>
        <div style="font-size:.72rem;color:var(--ui-text-3, #aaa);text-align:center">${m.created_at}</div>
        <div style="text-align:center"><span class="pub-badge ${m.published == '1' ? 'pub-yes' : 'pub-no'}">${m.published == '1' ? 'Published' : 'Draft'}</span></div>
        <div style="display:flex;justify-content:flex-end;gap:.3rem;flex-wrap:wrap">
            ${m.file_path ? `<a href="../${m.file_path}" target="_blank" class="btn btn-blue btn-sm"> View</a>` : ''}
            <button class="btn btn-gray btn-sm" onclick='editModule(${JSON.stringify(m)})'> Edit</button>
            <button class="btn btn-gray btn-sm" onclick="togglePublish(${m.id})">${m.published == '1' ? 'Unpublish' : 'Publish'}</button>
            <button class="btn btn-red btn-sm" title="Delete" onclick="deleteModule(${m.id})">${tico('trash')}</button>
        </div>
    </div>`;
        }

        async function loadModules() {
            const cid = document.getElementById('mod-course-select').value;
            const wrap = document.getElementById('mod-list-wrap');
            const pBtn = document.getElementById('view-progress-btn');
            if (!cid) { wrap.innerHTML = emptyCard(tico('folder'), 'Select a subject to view modules.'); if (pBtn) pBtn.style.display = 'none'; return; }
            wrap.innerHTML = loadingCard();
            const res = await fetch(`../api/modules.php?action=list&course_id=${cid}`);
            const data = await res.json();
            if (!data.success || !data.data.length) { wrap.innerHTML = emptyCard('', 'No modules yet. Click <strong>+ Add Module</strong>.'); if (pBtn) pBtn.style.display = 'none'; return; }
            if (pBtn) pBtn.style.display = '';
            wrap.innerHTML = `<div style="background:var(--ui-surface, white);border-radius:16px;box-shadow:0 2px 14px rgba(0,0,0,.07);overflow:hidden">
        <div style="padding:1.1rem 1.5rem;border-bottom:1px solid var(--ui-border-soft, #f0f2f5);font-family:'Nunito',sans-serif;font-size:.95rem;font-weight:800;color:var(--ui-text, #1a1a2e)">Modules (${data.data.length})</div>
        <div style="display:grid;grid-template-columns:1fr 65px 80px 80px 165px;gap:.5rem;padding:.5rem 1.25rem;font-size:.65rem;font-weight:800;letter-spacing:.07em;text-transform:uppercase;color:var(--ui-text-3, #bbb);background:var(--ui-surface-2, #f8f9fc);border-bottom:1px solid var(--ui-border-soft, #f0f2f5)">
            <span>Module</span><span style="text-align:center">Week</span><span style="text-align:center">Posted</span><span style="text-align:center">Status</span><span style="text-align:right">Actions</span>
        </div>
        ${data.data.map(moduleRow).join('')}
    </div>`;
        }

        function openModModal() {
            document.getElementById('mod-modal-title').textContent = 'Add Module';
            document.getElementById('mod-edit-id').value = '';
            populateCourseSelect('mod-course');
            const sel = document.getElementById('mod-course-select').value;
            if (sel) document.getElementById('mod-course').value = sel;
            ['mod-title', 'mod-desc', 'mod-week'].forEach(id => document.getElementById(id).value = '');
            document.getElementById('mod-published').checked = true;
            document.getElementById('mod-file').value = '';
            document.getElementById('mod-current-file').style.display = 'none';
            document.getElementById('mod-err').style.display = 'none';
            openModal('modModal');
        }

        function editModule(m) {
            document.getElementById('mod-modal-title').textContent = ' Edit Module';
            document.getElementById('mod-edit-id').value = m.id;
            populateCourseSelect('mod-course');
            document.getElementById('mod-course').value = m.course_id;
            document.getElementById('mod-title').value = m.title || '';
            document.getElementById('mod-desc').value = m.description || '';
            document.getElementById('mod-week').value = m.week_number || '';
            document.getElementById('mod-published').checked = m.published == '1';
            document.getElementById('mod-file').value = '';
            if (m.file_path) { document.getElementById('mod-current-file').style.display = ''; document.getElementById('mod-current-file-link').href = '../' + m.file_path; }
            else { document.getElementById('mod-current-file').style.display = 'none'; }
            document.getElementById('mod-err').style.display = 'none';
            openModal('modModal');
        }

        async function submitModule() {
            const err = document.getElementById('mod-err'); err.style.display = 'none';
            const editId = document.getElementById('mod-edit-id').value;
            const fd = new FormData();
            if (editId) fd.append('id', editId);
            fd.append('course_id', document.getElementById('mod-course').value);
            fd.append('title', document.getElementById('mod-title').value.trim());
            fd.append('description', document.getElementById('mod-desc').value.trim());
            fd.append('week_number', document.getElementById('mod-week').value);
            if (document.getElementById('mod-published').checked) fd.append('published', '1');
            const file = document.getElementById('mod-file').files[0]; if (file) fd.append('file', file);
            if (!fd.get('course_id') || !fd.get('title')) { err.textContent = 'Subject and title required.'; err.style.display = 'block'; return; }
            const res = await fetch(`../api/modules.php?action=${editId ? 'update' : 'create'}`, { method: 'POST', body: fd });
            const data = await res.json();
            if (data.success) { closeModal('modModal'); showToast(editId ? 'Module updated!' : 'Module added!'); loadModules(); }
            else { err.textContent = '' + data.message; err.style.display = 'block'; }
        }

        async function togglePublish(id) { const fd = new FormData(); fd.append('id', id); await fetch('../api/modules.php?action=toggle', { method: 'POST', body: fd }); showToast('Module updated!'); loadModules(); }
        async function deleteModule(id) { if (!confirm('Delete this module?')) return; await fetch(`../api/modules.php?action=delete&id=${id}`, { method: 'DELETE' }); showToast(' Module deleted.'); loadModules(); }

        async function loadModuleProgress() {
            const cid = document.getElementById('mod-course-select').value; if (!cid) return;
            document.getElementById('mod-progress-wrap').style.display = '';
            const wrap = document.getElementById('mod-progress-table'); wrap.innerHTML = loadingCard();
            const res = await fetch(`../api/module_progress.php?action=course_progress&course_id=${cid}`);
            const data = await res.json();
            if (!data.success || !data.data.length) { wrap.innerHTML = '<div style="padding:2rem;text-align:center;color:var(--ui-text-3, #bbb)">No students enrolled yet.</div>'; return; }
            wrap.innerHTML = `<table width="100%"><thead><tr><th>Student</th><th>Section</th><th>Progress</th><th>Done / Total</th></tr></thead><tbody>
        ${data.data.map(s => `<tr>
            <td><strong>${escHtml(s.last_name + ', ' + s.first_name)}</strong></td>
            <td>${escHtml(s.section_dept || '—')}</td>
            <td style="min-width:140px"><div style="background:var(--ui-surface-3, #f0f2f5);border-radius:100px;height:8px;overflow:hidden"><div style="height:8px;border-radius:100px;background:#003087;width:${s.percent}%;transition:width .4s"></div></div>
            <span style="font-size:.72rem;font-weight:700;color:var(--ui-link, #003087)">${s.percent}%</span></td>
            <td><strong>${s.done_count}</strong> / ${s.total}</td>
        </tr>`).join('')}</tbody></table>`;
        }

        /*  ASSIGNMENTS  */
        /*  ASSIGNMENTS LIST (teacher)  */
        let asgData = [], asgFilter = 'all';
        const asgNum = v => { const n = Number(v); return Number.isInteger(n) ? String(n) : String(+n.toFixed(2)); };
        function asgDue(a) {
            if (!a.due_date) return { label: 'No due date', note: 'Open anytime', tone: 'muted', ts: Infinity };
            const d = new Date(a.due_date.replace(' ', 'T')), now = new Date();
            const label = d.toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric' }) + ' · ' + d.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit' });
            // Calendar days between today and the due date (Oct 5 → Oct 7 = 2 days)
            const days = Math.round((new Date(d.getFullYear(), d.getMonth(), d.getDate()) - new Date(now.getFullYear(), now.getMonth(), now.getDate())) / 86400000);
            if (d < now) return { label, note: 'Past due', tone: 'bad', ts: d.getTime(), past: true };
            if (days <= 0) return { label, note: 'Due today', tone: 'warn', ts: d.getTime() };
            if (days === 1) return { label, note: 'Due tomorrow', tone: 'warn', ts: d.getTime() };
            return { label, note: `Due in ${days} days`, tone: days <= 3 ? 'warn' : 'muted', ts: d.getTime() };
        }
        function asgBucket(a) {
            const due = asgDue(a);
            return { needs: Number(a.pending_grades) > 0, upcoming: !due.past && a.due_date, past: !!due.past };
        }
        function assignRow(a) {
            const due = asgDue(a), total = Number(a.class_total || 0), sub = Number(a.class_submitted || 0), pend = Number(a.pending_grades || 0);
            const pct = total ? Math.round(sub / total * 100) : 0;
            let action;
            if (pend > 0) action = `<a class="asg-btn primary" href="grade.php?id=${a.id}${asgSecQ()}">Grade · ${pend}</a>`;
            else if (sub > 0) action = `<span class="asg-pill ok">All graded</span>`;
            else action = `<span class="asg-pill muted">No submissions</span>`;
            return `<div class="asg-row">
                <div class="asg-main">
                    <a class="asg-title" href="grade.php?id=${a.id}${asgSecQ()}">${escHtml(a.title)}</a>
                    ${a.description ? `<div class="asg-desc">${escHtml(a.description)}</div>` : ''}
                    ${a.file_path ? `<a class="asg-ref" href="../${a.file_path}" target="_blank" rel="noopener">${tico('clipboard')} Instructions file</a>` : ''}
                </div>
                <div class="asg-due"><b>${due.label}</b><span class="t-${due.tone}">${due.note}</span></div>
                <div class="asg-subs"><span>${sub} of ${total} submitted</span><div class="asg-bar"><i style="width:${pct}%"></i></div></div>
                <div class="asg-pts">${asgNum(a.max_score)} pts</div>
                <div class="asg-act">${action}</div>
                <div class="asg-more">
                    <button class="asg-dots" type="button" aria-label="More actions" onclick="asgMenu(event, ${a.id})">⋯</button>
                    <div class="asg-menu" id="asg-menu-${a.id}">
                        <a href="grade.php?id=${a.id}${asgSecQ()}">Open grader</a>
                        <button type="button" onclick='editAssignment(${JSON.stringify(a).replace(/'/g, "&#39;")})'>Edit</button>
                        <button type="button" class="danger" onclick="deleteAssignment(${a.id})">Delete</button>
                    </div>
                </div>
            </div>`;
        }
        function asgSecQ() { const s = window.classSection ? classSection() : ''; return s ? '&section=' + encodeURIComponent(s) : ''; }
        function asgMenu(e, id) {
            e.stopPropagation();
            document.querySelectorAll('.asg-menu.show').forEach(m => { if (m.id !== 'asg-menu-' + id) m.classList.remove('show'); });
            document.getElementById('asg-menu-' + id).classList.toggle('show');
        }
        document.addEventListener('click', () => document.querySelectorAll('.asg-menu.show').forEach(m => m.classList.remove('show')));
        function asgSetFilter(f) { asgFilter = f; renderAssignList(); }
        function renderAssignList() {
            const wrap = document.getElementById('assign-list-wrap');
            const counts = { all: asgData.length, needs: 0, upcoming: 0, past: 0 };
            asgData.forEach(a => { const b = asgBucket(a); if (b.needs) counts.needs++; if (b.upcoming) counts.upcoming++; if (b.past) counts.past++; });
            const rows = asgData.filter(a => asgFilter === 'all' || asgBucket(a)[asgFilter])
                .sort((x, y) => (Number(y.pending_grades) > 0) - (Number(x.pending_grades) > 0) || asgDue(x).ts - asgDue(y).ts);
            const chip = (k, label) => `<button type="button" class="asg-chip${asgFilter === k ? ' on' : ''}" onclick="asgSetFilter('${k}')">${label} · ${counts[k]}</button>`;
            wrap.innerHTML = `<div class="asg-chips">${chip('all', 'All')}${chip('needs', 'Needs grading')}${chip('upcoming', 'Upcoming')}${chip('past', 'Past due')}</div>
                <div class="asg-card">${rows.length ? `<div class="asg-head"><span>Assignment</span><span>Due</span><span>Submissions</span><span>Points</span><span></span><span></span></div>` + rows.map(assignRow).join('')
                    : `<div class="asg-none">Nothing here.</div>`}</div>`;
        }

        async function loadAssignments() {
            const cid = document.getElementById('assign-course-select').value;
            const wrap = document.getElementById('assign-list-wrap');
            if (!cid) { wrap.innerHTML = emptyCard(tico('clipboard'), 'Select a subject to view assignments.'); return; }
            wrap.innerHTML = loadingCard();
            try {
                const res = await fetch(`../api/assignments.php?action=list&course_id=${cid}&section=${encodeURIComponent(window.classSection ? classSection() : '')}`);
                const raw = await res.text();
                let data;
                try { data = JSON.parse(raw); }
                catch (parseErr) {
                    console.error('Assignments API returned something that is not JSON:', raw);
                    const hint = raw.replace(/<[^>]+>/g, ' ').replace(/\s+/g, ' ').trim().slice(0, 220);
                    wrap.innerHTML = emptyCard('', `Could not load assignments (server error ${res.status}).<br><small style="color:#c0392b">${escHtml(hint)}</small>`);
                    return;
                }
                if (!data.success) { wrap.innerHTML = emptyCard('', escHtml(data.message || 'Could not load assignments.')); return; }
                if (!data.data.length) {
                    wrap.innerHTML = `<div class="asg-card"><div class="asg-none"><strong>No assignments yet</strong>Create the first one for this class.</div></div>`;
                    return;
                }
                asgData = data.data;
                renderAssignList();
            } catch (e) {
                console.error(e);
                wrap.innerHTML = emptyCard('', `Could not load assignments.<br><small style="color:#c0392b">${escHtml(e.message || String(e))}</small>`);
            }
        }

        // The module picked in the Create Assignment / Create Quiz form. The AI drafts
        // from that module's lesson (api/ai_*.php → mlLessonForAi); the teacher's text
        // is then only an optional focus. No module → the topic is required.
        // The AI never drafts without a lesson to base it on (also checked on the server).
        // Returns { course_id, module_id, label } or { block: 'why' }.
        function aiModuleSrc(kind) {
            const wrap = document.getElementById(kind + '-module-wrap');
            const sel = document.getElementById(kind + '-module');
            const course = document.getElementById(kind + '-course')?.value || '';
            if (!course) return { block: 'Choose the subject in the form first. The AI writes from the lesson of the module you pick.' };
            if (!sel || !wrap || wrap.style.display === 'none') return { block: 'Loading the modules of this subject… If this stays, close this and choose a module in the form.' };
            if (sel.value === '0' || sel.value === '') return { block: 'Choose a module in the form first. The AI writes from that module\'s lesson.' };
            const opt = sel.options[sel.selectedIndex];
            const label = opt?.dataset.mod || '';
            if (!label) return { block: 'This subject has no modules yet. Add a module and its lesson in Modules first.' };
            if (opt.dataset.lesson !== '1') return { block: label + ' has no lesson yet, so the AI has nothing to base it on. Add the lesson in Modules first, or choose another module.' };
            return { course_id: course, module_id: sel.value, label };
        }
        const AI_SRC_ICON = '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 19.5V5a2 2 0 0 1 2-2h12v16H6a2 2 0 0 0-2 2z"/><path d="M8 7h6"/></svg>';
        const AI_WARN_ICON = '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0z"/><path d="M12 9v4M12 17h.01"/></svg>';
        function aiShowSrc(kind, pre) {
            const src = aiModuleSrc(kind);
            const box = document.getElementById(`ai-${pre}-src`);
            const lbl = document.getElementById(`ai-${pre}-topic-lbl`);
            const ta = document.getElementById(`ai-${pre}-topic`);
            const btn = document.getElementById(`ai-${pre}-generate-btn`);
            box.classList.toggle('is-warn', !!src.block);
            if (src.block) {
                box.innerHTML = AI_WARN_ICON + '<span><b>Nothing to base it on</b></span>';
                box.querySelector('span').append(src.block);
            } else {
                box.innerHTML = AI_SRC_ICON + '<span><b></b>The AI writes from this module\'s lesson. Change the module in the form to use another lesson.</span>';
                box.querySelector('b').textContent = 'Based on: ' + src.label;
            }
            box.hidden = false;
            lbl.textContent = 'Focus (optional)';
            ta.placeholder = pre === 'quiz' ? 'e.g. Tanka at Haiku lang · mas mahirap na tanong · application questions' : 'e.g. Focus on the Haiku · group work · 300-500 words';
            if (btn && btn.dataset.loading !== '1') btn.disabled = !!src.block;
            return src;
        }
        // Modules finished loading while the AI window is open → update it
        function aiRefreshSrc(kind) {
            const modal = document.getElementById(kind === 'quiz' ? 'aiQuizModal' : 'aiAssignModal');
            if (modal && modal.classList.contains('show')) aiShowSrc(kind, kind);
        }

        function openAiAssignModal() {
            document.getElementById('ai-assign-err').style.display = 'none';
            aiShowSrc('assign', 'assign');
            openModal('aiAssignModal');
        }

        async function generateAiAssignment() {
            const err = document.getElementById('ai-assign-err'); err.style.display = 'none';
            const topic = document.getElementById('ai-assign-topic').value.trim();
            const kind = document.getElementById('ai-assign-kind').value;
            const src = aiModuleSrc('assign');
            if (src.block) { err.textContent = src.block; err.style.display = 'block'; return; }

            const btn = document.getElementById('ai-assign-generate-btn');
            setBtnLoadingTeacher(btn, 'Generating...');

            try {
                const res = await fetch('../api/ai_assignment.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ topic, kind, course_id: src.course_id, module_id: src.module_id })
                });
                const data = await res.json();
                if (!data.success) {
                    err.textContent = data.message || 'Could not generate an assignment.'; err.style.display = 'block';
                    clearBtnLoadingTeacher(btn);
                    return;
                }
                document.getElementById('assign-title').value = data.title || '';
                document.getElementById('assign-desc').value = data.instructions || '';
                if (data.suggested_max_score) document.getElementById('assign-score').value = data.suggested_max_score;
                closeModal('aiAssignModal');
                showToast('Draft generated — review before saving!');
            } catch (e) {
                err.textContent = 'Network error. Please try again.'; err.style.display = 'block';
            }
            clearBtnLoadingTeacher(btn);
        }

        // ── Module of an assignment / quiz (api/module_links.php) ──
        // New work: "Pick automatically" = the module the class is on now
        // (or the one in the link, e.g. from My Classes → Modules → Add).
        // Edited work: its saved module.
        let mlReq = { assign: 0, quiz: 0 };
        function mlLockCourse(kind, on) {
            const sel = document.getElementById(kind + '-course'); if (!sel) return;
            let box = sel.parentElement.querySelector('.ml-locked-course');
            if (on && sel.value && sel.selectedOptions[0]) {
                if (!box) { box = document.createElement('div'); box.className = 'ml-locked ml-locked-course'; sel.insertAdjacentElement('afterend', box); }
                box.textContent = sel.selectedOptions[0].textContent;
                box.style.display = '';
                sel.style.display = 'none';
            } else {
                sel.style.display = '';
                if (box) box.style.display = 'none';
            }
        }
        async function mlLoad(kind, selected, lock) {
            const sel = document.getElementById(kind + '-module');
            const hint = document.getElementById(kind + '-module-hint');
            const wrap = document.getElementById(kind + '-module-wrap');
            const cid = document.getElementById(kind + '-course').value;
            if (!sel) return;
            const keep = selected !== undefined ? selected : sel.value;
            const req = ++mlReq[kind];
            sel.innerHTML = '<option value="auto">Pick automatically</option>';
            sel.style.display = '';
            const lockEl = wrap.querySelector('.ml-locked'); if (lockEl) lockEl.style.display = 'none';
            hint.textContent = '';
            // Hidden while loading: a form saved meanwhile sends no module (new work → placed
            // automatically by the server, edited work keeps its module)
            wrap.style.display = 'none';
            if (!cid || keep === 'keep') return;
            try {
                const d = await (await fetch('../api/module_links.php?course_id=' + encodeURIComponent(cid), { cache: 'no-store' })).json();
                if (req !== mlReq[kind]) return;   // the subject changed again meanwhile
                if (!d.success || !d.enabled) { wrap.style.display = 'none'; return; }
                wrap.style.display = '';
                const autoMod = (d.modules || []).find(m => m.id === d.auto);
                sel.innerHTML = '';
                const add = (v, t) => { const o = document.createElement('option'); o.value = v; o.textContent = t; sel.appendChild(o); return o; };
                const mName = m => (m.num ? `Module ${m.num} · ` : '') + m.title;
                const noLes = ' (no lesson yet)';
                let o = add('auto', autoMod ? `Pick automatically (${mName(autoMod)}${autoMod.has_lesson ? '' : noLes})` : 'Pick automatically');
                o.dataset.mod = autoMod ? mName(autoMod) : ''; o.dataset.lesson = autoMod && autoMod.has_lesson ? '1' : '0';
                (d.modules || []).forEach(m => { o = add(String(m.id), mName(m) + (m.has_lesson ? '' : noLes)); o.dataset.mod = mName(m); o.dataset.lesson = m.has_lesson ? '1' : '0'; });
                const want = keep === null || keep === undefined || keep === '' ? 'auto' : String(keep);
                sel.value = [...sel.options].some(o => o.value === want) ? want : 'auto';
                // Opened from a module card (+ Assignment / + Quiz): the module is already known,
                // so show it as a fixed label instead of asking again. The value is still submitted.
                if (lock && want !== 'auto' && want !== '0' && sel.value === want) {
                    let box = wrap.querySelector('.ml-locked');
                    if (!box) { box = document.createElement('div'); box.className = 'ml-locked'; sel.insertAdjacentElement('afterend', box); }
                    box.textContent = sel.selectedOptions[0].dataset.mod || sel.selectedOptions[0].textContent;
                    box.style.display = '';
                    sel.style.display = 'none';
                }
                hint.textContent = d.modules && d.modules.length ? 'Students see it under this module on their Modules page.' : 'This subject has no modules yet.';
                if (String(keep) === '0') wrap.style.display = 'none';   // older work without a module: picker hidden, nothing sent, stays as is
            } catch (e) { if (req === mlReq[kind]) wrap.style.display = 'none'; }
            if (req === mlReq[kind]) aiRefreshSrc(kind);
        }
        // Opened from My Classes → Modules ("+ Assignment" / "+ Quiz" on a module card):
        // { course, module } of that card. The form opens on the same page and the page stays.
        let mlCtx = null;
        function mlCourseFor(listSelId) {
            if (mlCtx && mlCtx.course) return String(mlCtx.course);
            return document.getElementById(listSelId)?.value || '';
        }
        // After saving: refresh whatever list this page shows (Assignments / Quizzes page,
        // or the module cards on My Classes → Modules). Never leaves the page.
        function mlAfterSave(kind) {
            mlCtx = null;
            if (kind === 'assign' && typeof loadAssignments === 'function' && document.getElementById('assign-course-select')) loadAssignments();
            if (kind === 'quiz' && typeof loadQuizzes === 'function' && document.getElementById('quiz-course-select')) loadQuizzes();
            if (typeof window.mlcRefresh === 'function') window.mlcRefresh();
        }
        function mlPreset() {   // module of the card the form was opened from, else ?module=ID in the link (used once)
            if (mlCtx && mlCtx.module) return String(mlCtx.module);
            const p = new URLSearchParams(location.search);
            const v = p.get('module');
            if (v !== null) {
                p.delete('module'); p.delete('action');
                try { history.replaceState(null, '', location.pathname + (p.toString() ? '?' + p : '') + location.hash); } catch (e) { }
            }
            return v && /^\d+$/.test(v) ? v : 'auto';
        }

        function openAssignModal() {
            document.getElementById('assign-modal-title').textContent = 'Create assignment';
            document.getElementById('assign-save-btn').textContent = 'Publish assignment';
            document.getElementById('assign-ai-row').style.display = '';
            document.getElementById('assign-edit-id').value = '';
            populateCourseSelect('assign-course');
            const sel = mlCourseFor('assign-course-select');
            if (sel) document.getElementById('assign-course').value = sel;
            mlLockCourse('assign', !!sel);   // the page already is on a class (sidebar / module card): no need to ask again
            mlLoad('assign', mlPreset(), !!(mlCtx && mlCtx.module));
            ['assign-title', 'assign-desc', 'assign-due', 'assign-max-attempts'].forEach(id => document.getElementById(id).value = '');
            document.getElementById('assign-score').value = '100';
            document.getElementById('assign-file').value = '';
            document.getElementById('assign-current-file').style.display = 'none';
            document.getElementById('assign-err').style.display = 'none';
            openModal('assignModal');
        }

        function editAssignment(a) {
            document.getElementById('assign-modal-title').textContent = 'Edit assignment';
            document.getElementById('assign-save-btn').textContent = 'Save changes';
            document.getElementById('assign-edit-id').value = a.id;
            populateCourseSelect('assign-course');
            document.getElementById('assign-course').value = a.course_id;
            mlLockCourse('assign', false);
            mlLoad('assign', a.module_id === undefined ? 'keep' : (a.module_id === null ? '0' : String(a.module_id)));
            document.getElementById('assign-title').value = a.title || '';
            document.getElementById('assign-desc').value = a.description || '';
            document.getElementById('assign-due').value = a.due_date ? a.due_date.replace(' ', 'T').substring(0, 16) : '';
            document.getElementById('assign-score').value = a.max_score || 100;
            document.getElementById('assign-max-attempts').value = a.max_attempts || '';
            document.getElementById('assign-ai-row').style.display = 'none';
            document.getElementById('assign-file').value = '';
            if (a.file_path) { document.getElementById('assign-current-file').style.display = ''; document.getElementById('assign-current-file-link').href = '../' + a.file_path; }
            else { document.getElementById('assign-current-file').style.display = 'none'; }
            document.getElementById('assign-err').style.display = 'none';
            openModal('assignModal');
        }

        async function submitAssignment() {
            const err = document.getElementById('assign-err'); err.style.display = 'none';
            const editId = document.getElementById('assign-edit-id').value;
            const fd = new FormData();
            if (editId) fd.append('id', editId);
            fd.append('course_id', document.getElementById('assign-course').value);
            fd.append('title', document.getElementById('assign-title').value.trim());
            fd.append('description', document.getElementById('assign-desc').value.trim());
            fd.append('due_date', document.getElementById('assign-due').value);
            fd.append('max_score', document.getElementById('assign-score').value || 100);
            fd.append('max_attempts', document.getElementById('assign-max-attempts').value.trim());
            if (document.getElementById('assign-module-wrap').style.display !== 'none') fd.append('module_id', document.getElementById('assign-module').value || 'auto');
            const file = document.getElementById('assign-file').files[0]; if (file) fd.append('file', file);
            if (!fd.get('course_id') || !fd.get('title')) { err.textContent = 'Subject and title required.'; err.style.display = 'block'; return; }
            const res = await fetch(`../api/assignments.php?action=${editId ? 'update' : 'create'}`, { method: 'POST', body: fd });
            const data = await res.json();
            if (data.success) { closeModal('assignModal'); showToast(editId ? 'Assignment updated · Gradebook updated' : 'Assignment published · added to the Gradebook'); mlAfterSave('assign'); }
            else { err.textContent = '' + data.message; err.style.display = 'block'; }
        }

        async function deleteAssignment(id) { if (!confirm('Delete this assignment and all submissions?')) return; await fetch(`../api/assignments.php?action=delete&id=${id}`, { method: 'DELETE' }); showToast(' Assignment deleted.'); loadAssignments(); }

        async function viewSubmissions(assignId, title, maxScore) {
            document.getElementById('submissions-title').textContent = title + ' — Submissions';
            document.getElementById('submissions-body').innerHTML = loadingCard();
            openModal('submissionsModal');
            const res = await fetch(`../api/assignments.php?action=submissions&id=${assignId}`);
            const data = await res.json();
            if (!data.success || !data.data.length) { document.getElementById('submissions-body').innerHTML = '<div style="padding:3rem;text-align:center;color:var(--ui-text-3, #bbb)"><div style="font-size:2.5rem;margin-bottom:.5rem"></div>No submissions yet.</div>'; return; }
            document.getElementById('submissions-body').innerHTML = `
        <div style="padding:.5rem 0">
            <div style="display:grid;grid-template-columns:1fr 80px 80px 120px 130px;gap:.5rem;padding:.5rem 1.25rem;font-size:.65rem;font-weight:800;letter-spacing:.07em;text-transform:uppercase;color:var(--ui-text-3, #bbb);background:var(--ui-surface-2, #f8f9fc);border-bottom:1px solid var(--ui-border-soft, #f0f2f5)">
                <span>Student</span><span style="text-align:center">Date</span><span style="text-align:center">File</span><span style="text-align:center">Score / ${maxScore}</span><span style="text-align:center">Remarks</span>
            </div>
            ${data.data.map(s => `
            <div style="display:grid;grid-template-columns:1fr 80px 80px 120px 130px;gap:.5rem;align-items:center;padding:.85rem 1.25rem;border-bottom:1px solid var(--ui-border-soft, #f8f9fc)">
                <div><div style="font-size:.84rem;font-weight:700;color:var(--ui-text, #1a1a2e)">${escHtml(s.last_name + ', ' + s.first_name)}</div><div style="font-size:.7rem;color:var(--ui-text-3, #aaa)">${escHtml(s.section_dept || '')}</div></div>
                <div style="font-size:.72rem;color:var(--ui-text-2, #666);text-align:center">${s.submitted_at ? s.submitted_at.substring(0, 10) : '—'}</div>
                <div style="text-align:center">${s.file_path ? `<a href="../${s.file_path}" target="_blank" class="btn btn-blue btn-sm"> View</a>` : '<span style="color:#ccc;font-size:.75rem">—</span>'}</div>
                <div style="text-align:center">
                    <input type="number" min="0" max="${maxScore}" step="0.5" value="${s.score ?? ''}" placeholder="—"
                        data-submission-id="${s.submission_id}" data-student-id="${s.student_id}" data-assign-id="${assignId}"
                        style="width:72px;padding:.3rem .5rem;border:1.5px solid var(--ui-border, #e0e4ee);border-radius:7px;font-size:.82rem;text-align:center;font-family:'Nunito',sans-serif;font-weight:700;outline:none"
                        onfocus="this.style.borderColor='#003087'" onblur="this.style.borderColor='#e0e4ee'">
                </div>
                <div style="text-align:center">
                    <input type="text" value="${escHtml(s.remarks || '')}" placeholder="Remarks" data-remarks-for="${s.submission_id}"
                        style="width:100%;padding:.3rem .5rem;border:1.5px solid var(--ui-border, #e0e4ee);border-radius:7px;font-size:.78rem;outline:none"
                        onfocus="this.style.borderColor='#003087'" onblur="this.style.borderColor='#e0e4ee'">
                </div>
            </div>`).join('')}
        </div>
        <div style="padding:1rem 1.25rem;background:var(--ui-surface-2, #f8f9fc);border-top:1px solid var(--ui-border-soft, #f0f2f5);display:flex;justify-content:flex-end;gap:.75rem">
            <button class="btn btn-gray btn-sm" onclick="closeModal('submissionsModal')">Close</button>
            <button class="btn btn-green" onclick="saveSubmissionGrades(${assignId})"> Save All Grades</button>
        </div>`;
        }

        async function saveSubmissionGrades(assignId) {
            const inputs = document.querySelectorAll('#submissions-body input[data-submission-id]');
            const grades = [];
            inputs.forEach(inp => { const ri = document.querySelector(`input[data-remarks-for="${inp.dataset.submissionId}"]`); grades.push({ submission_id: inp.dataset.submissionId, student_id: inp.dataset.studentId, assign_id: inp.dataset.assignId, score: inp.value, remarks: ri ? ri.value : '' }); });
            const res = await fetch('../api/assignments.php?action=grade', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ grades }) });
            const data = await res.json();
            if (data.success) { showToast('Grades saved!'); closeModal('submissionsModal'); loadAssignments(); }
            else showToast('Could not save grades.', true);
        }

        /*  QUIZZES  */
        function toggleQuizMode() {
            const mode = document.querySelector('input[name="quiz-mode"]:checked').value;
            document.getElementById('quiz-questions-section').style.display = mode === 'questions' ? '' : 'none';
            document.getElementById('quiz-file-section').style.display = mode === 'file' ? '' : 'none';
            const aiRow = document.getElementById('quiz-ai-row');
            if (aiRow) aiRow.style.display = (mode === 'file' || aiRow.dataset.editing === '1') ? 'none' : '';
        }

        function quizRow(q) {
            const isFile = q.quiz_type === 'file';
            const isDraft = q.status === 'Draft';
            const typeBadge = isFile ? `<span class="badge badge-blue" style="font-size:.63rem"> File</span>` : `<span class="badge badge-green" style="font-size:.63rem">Auto</span>`;
            const statusBadge = isDraft ? `<span class="badge badge-gray" style="font-size:.63rem;margin-left:.3rem">Draft</span>` : `<span class="badge badge-green" style="font-size:.63rem;margin-left:.3rem">Published</span>`;
            return `<div style="display:grid;grid-template-columns:1fr 65px 70px 65px 75px 165px;gap:.5rem;align-items:center;padding:.85rem 1.25rem;border-bottom:1px solid var(--ui-border-soft, #f8f9fc);transition:background .15s" onmouseover="this.style.background='#f6f9ff'" onmouseout="this.style.background=''">
        <div style="display:flex;align-items:center;gap:.7rem">
            <div style="width:32px;height:32px;border-radius:8px;background:#e8f0ff;display:flex;align-items:center;justify-content:center;font-size:.95rem;flex-shrink:0">${isFile ? tico('paperclip') : tico('quiz')}</div>
            <div><div style="font-size:.84rem;font-weight:700;color:var(--ui-text, #1a1a2e)">${escHtml(q.title)}${!isFile ? statusBadge : ''}</div>
            ${q.description ? `<div style="font-size:.7rem;color:var(--ui-text-3, #aaa);margin-top:.04rem">${escHtml(q.description.substring(0, 50))}${q.description.length > 50 ? '…' : ''}</div>` : ''}
            ${isFile && q.file_path ? `<a href="../${q.file_path}" target="_blank" style="font-size:.7rem;color:var(--ui-link, #003087);font-weight:700"> Quiz file</a>` : ''}
            ${q.close_at ? `<div style="font-size:.67rem;color:#c0392b;font-weight:700"> Closes: ${q.close_at}</div>` : ''}</div>
        </div>
        <div style="text-align:center">${typeBadge}</div>
        <div style="font-size:.75rem;color:var(--ui-text-2, #666);text-align:center">${isFile ? '—' : tico('quiz') + ' ' + q.question_count + (q.question_count ? '/' + (q.question_count) : '')}</div>
        <div style="font-size:.75rem;color:var(--ui-text-2, #666);text-align:center">${q.time_limit ? tico('clock') + ' ' + q.time_limit + 'm' : '—'}</div>
        <div style="text-align:center"><span class="badge badge-blue">${tico('users')} ${q.attempt_count}</span></div>
        <div style="display:flex;justify-content:flex-end;gap:.3rem;flex-wrap:wrap">
            <button class="btn btn-green btn-sm" onclick="viewResults(${q.id},'${escHtml(q.title)}',${q.max_score},'${q.quiz_type || 'questions'}')">${isFile ? 'Grade' : 'Results'}</button>
            <button class="btn btn-gray btn-sm" title="Edit" onclick="editQuiz(${q.id})">${isDraft ? 'Continue' : tico('edit')}</button>
            <button class="btn btn-red btn-sm" title="Delete" onclick="deleteQuiz(${q.id})">${tico('trash')}</button>
        </div>
    </div>`;
        }

        async function loadQuizzes() {
            const cid = document.getElementById('quiz-course-select').value;
            const wrap = document.getElementById('quiz-list-wrap');
            if (!cid) { wrap.innerHTML = emptyCard(tico('quiz'), 'Select a subject to view quizzes.'); return; }
            wrap.innerHTML = loadingCard();
            const res = await fetch(`../api/quizzes.php?action=list&course_id=${cid}`);
            const data = await res.json();
            if (!data.success || !data.data.length) { wrap.innerHTML = emptyCard('', 'No quizzes yet. Click <strong>+ Create Quiz</strong>.'); return; }
            wrap.innerHTML = `<div style="background:var(--ui-surface, white);border-radius:16px;box-shadow:0 2px 14px rgba(0,0,0,.07);overflow:hidden">
        <div class="ql-head" style="padding:1.1rem 1.5rem;border-bottom:1px solid var(--ui-border-soft, #f0f2f5);font-family:'Nunito',sans-serif;font-size:.95rem;font-weight:800;color:var(--ui-text, #1a1a2e)">Quizzes (${data.data.length})</div>
        <div style="display:grid;grid-template-columns:1fr 65px 70px 65px 75px 165px;gap:.5rem;padding:.5rem 1.25rem;font-size:.65rem;font-weight:800;letter-spacing:.07em;text-transform:uppercase;color:var(--ui-text-3, #bbb);background:var(--ui-surface-2, #f8f9fc);border-bottom:1px solid var(--ui-border-soft, #f0f2f5)">
            <span>Quiz</span><span style="text-align:center">Type</span><span style="text-align:center">Qs</span><span style="text-align:center">Time</span><span style="text-align:center">Attempts</span><span style="text-align:right">Actions</span>
        </div>
        ${data.data.map(quizRow).join('')}
    </div>`;
        }

        let questionCount = 0;
        // Create Quiz opens the form directly with one blank question (1 point);
        // teachers add more questions as they go.
        function openQuizModal() {
            openQuizInfoStep(1, 1);
            document.getElementById('quiz-question-count').value = '';
            document.getElementById('quiz-maxscore').value = 100;
            updateQuestionProgress();
        }

        // ── STEP 2: Quiz Info + auto-generated Question Builder slots ──
        function openQuizInfoStep(qCount, ppq) {
            document.getElementById('quiz-modal-title').textContent = 'Create Quiz';
            document.getElementById('quiz-ai-row').dataset.editing = '';
            document.getElementById('quiz-edit-id').value = '';
            document.getElementById('quiz-question-count').value = qCount;
            document.getElementById('quiz-points-per-question').value = ppq;
            populateCourseSelect('quiz-course');
            const sel = mlCourseFor('quiz-course-select');
            if (sel) document.getElementById('quiz-course').value = sel;
            mlLockCourse('quiz', !!sel);   // the page already is on a class (sidebar / module card): no need to ask again
            quizLockPickers(false);
            mlLoad('quiz', mlPreset(), !!(mlCtx && mlCtx.module));
            ['quiz-title', 'quiz-desc', 'quiz-time', 'quiz-open', 'quiz-close', 'quiz-passing'].forEach(id => document.getElementById(id).value = '');
            document.getElementById('quiz-maxscore').value = Math.max(1, Math.round(qCount * ppq));
            delete document.getElementById('quiz-maxscore').dataset.orig;
            delete document.getElementById('quiz-passing').dataset.orig;
            document.getElementById('quiz-show-results').checked = true;
            document.getElementById('quiz-show-correct').checked = false;
            toggleShowCorrect();
            document.getElementById('quiz-err').style.display = 'none';
            document.getElementById('questions-wrap').innerHTML = '';
            document.getElementById('quiz-file').value = '';
            document.getElementById('quiz-current-file').style.display = 'none';
            document.querySelector('input[name="quiz-mode"][value="questions"]').checked = true;
            toggleQuizMode();
            questionCount = 0;
            for (let i = 0; i < qCount; i++) addQuestion(ppq);
            updateQuestionProgress();
            openModal('quizModal');
        }

        function openAiQuizModal() {
            // From the dashboard shortcut the Create Quiz form isn't open: open it first,
            // so the subject and module (the lesson the AI uses) can be chosen
            if (!document.getElementById('quizModal').classList.contains('show')) openQuizInfoStep(10, 1);
            document.getElementById('ai-quiz-err').style.display = 'none';
            aiShowSrc('quiz', 'quiz');
            const targetCount = Number(document.getElementById('quiz-question-count').value) || 10;
            document.getElementById('ai-quiz-count').value = targetCount;
            openModal('aiQuizModal');
        }

        async function generateAiQuiz() {
            const err = document.getElementById('ai-quiz-err'); err.style.display = 'none';
            const topic = document.getElementById('ai-quiz-topic').value.trim();
            const count = Number(document.getElementById('ai-quiz-count').value) || 0;
            const types = Array.from(document.querySelectorAll('.ai-quiz-type:checked')).map(c => c.value);

            const src = aiModuleSrc('quiz');
            if (src.block) { err.textContent = src.block; err.style.display = 'block'; return; }
            if (count < 1 || count > 50) { err.textContent = 'Choose between 1 and 50 questions.'; err.style.display = 'block'; return; }
            if (!types.length) { err.textContent = 'Pick at least one question type.'; err.style.display = 'block'; return; }

            const btn = document.getElementById('ai-quiz-generate-btn');
            setBtnLoadingTeacher(btn, 'Generating...');

            try {
                const res = await fetch('../api/ai_quiz.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ topic, count, types, course_id: src.course_id, module_id: src.module_id })
                });
                const data = await res.json();
                if (!data.success) {
                    err.textContent = data.message || 'Could not generate questions.'; err.style.display = 'block';
                    clearBtnLoadingTeacher(btn);
                    return;
                }

                // Opened straight from "Create quiz with AI" (dashboard → ?action=ai-quiz):
                // the Create Quiz form isn't open yet, so the questions would land in a hidden
                // builder and never be seen or saved. Set up and open the form first.
                const freshForm = !document.getElementById('quizModal').classList.contains('show');
                if (freshForm) openQuizInfoStep(data.questions.length, 1);

                const ppq = Number(document.getElementById('quiz-points-per-question').value) || 1;
                document.getElementById('questions-wrap').innerHTML = '';
                questionCount = 0;
                data.questions.forEach(q => addQuestion(ppq, q));
                document.getElementById('quiz-question-count').value = data.questions.length;
                if (freshForm) document.getElementById('quiz-maxscore').value = Math.max(1, Math.round(data.questions.length * ppq));
                updateQuestionProgress();

                closeModal('aiQuizModal');
                if (data.received < data.requested) showToast(`Only ${data.received} of ${data.requested} questions could be generated — add the rest manually or generate again.`, true);
                else showToast(`Generated ${data.received} question(s)! Review them before publishing.`);
            } catch (e) {
                err.textContent = 'Network error. Please try again.'; err.style.display = 'block';
            }
            clearBtnLoadingTeacher(btn);
        }

        function setBtnLoadingTeacher(btn, text) {
            if (!btn || btn.dataset.loading === '1') return;
            btn.dataset.loading = '1';
            btn.dataset.originalHtml = btn.innerHTML;
            btn.disabled = true;
            btn.textContent = text;
        }
        function clearBtnLoadingTeacher(btn) {
            if (!btn || btn.dataset.loading !== '1') return;
            btn.disabled = false;
            btn.innerHTML = btn.dataset.originalHtml;
            delete btn.dataset.originalHtml;
            delete btn.dataset.loading;
        }

        function toggleShowCorrect() {
            const shown = document.getElementById('quiz-show-results').checked;
            document.getElementById('quiz-show-correct-wrap').style.display = shown ? '' : 'none';
            if (!shown) document.getElementById('quiz-show-correct').checked = false;
        }

        async function editQuiz(quizId) {
            const res = await fetch(`../api/quizzes.php?action=get&id=${quizId}`);
            const data = await res.json();
            if (!data.success) { showToast('Could not load quiz.', true); return; }
            const q = data.data;

            document.getElementById('quiz-modal-title').textContent = ' Edit Quiz';
            document.getElementById('quiz-ai-row').dataset.editing = '1';
            document.getElementById('quiz-edit-id').value = q.id;
            document.getElementById('quiz-question-count').value = q.question_count || '';
            document.getElementById('quiz-points-per-question').value = q.points_per_question || '';
            populateCourseSelect('quiz-course');
            document.getElementById('quiz-course').value = q.course_id;
            // Editing: the subject and the module cannot be changed (shown as fixed labels)
            mlLockCourse('quiz', true);
            quizLockPickers(true);
            mlLoad('quiz', q.module_id === undefined ? 'keep' : (q.module_id === null ? '0' : String(q.module_id)), true);
            document.getElementById('quiz-title').value = q.title || '';
            document.getElementById('quiz-desc').value = q.description || '';
            document.getElementById('quiz-time').value = q.time_limit || '';
            // 100.00 -> "100"; an older decimal (12.5) is shown as it is and may stay unchanged
            const numTxt = v => (v === null || v === undefined || v === '' || !isFinite(Number(v))) ? '' : String(Number(v));
            document.getElementById('quiz-maxscore').value = numTxt(q.max_score);
            document.getElementById('quiz-passing').value = numTxt(q.passing_score);
            document.getElementById('quiz-maxscore').dataset.orig = numTxt(q.max_score);
            document.getElementById('quiz-passing').dataset.orig = numTxt(q.passing_score);
            document.getElementById('quiz-open').value = q.open_at ? q.open_at.replace(' ', 'T').substring(0, 16) : '';
            document.getElementById('quiz-close').value = q.close_at ? q.close_at.replace(' ', 'T').substring(0, 16) : '';
            document.getElementById('quiz-show-results').checked = !!Number(q.show_results ?? 1);
            document.getElementById('quiz-show-correct').checked = !!Number(q.show_correct_answers ?? 0);
            toggleShowCorrect();
            document.getElementById('quiz-err').style.display = 'none';
            const isFile = q.quiz_type === 'file';
            document.querySelector(`input[name="quiz-mode"][value="${isFile ? 'file' : 'questions'}"]`).checked = true;
            toggleQuizMode();
            document.getElementById('quiz-file').value = '';
            if (isFile && q.file_path) { document.getElementById('quiz-current-file').style.display = ''; document.getElementById('quiz-current-file-link').href = '../' + q.file_path; }
            else { document.getElementById('quiz-current-file').style.display = 'none'; }

            document.getElementById('questions-wrap').innerHTML = '';
            questionCount = 0;
            if (!isFile) {
                (q.questions || []).forEach(eq => addQuestion(eq.points, eq));
                if (q.status === 'Draft' && document.getElementById('quiz-question-count').value) {
                    const target = Number(document.getElementById('quiz-question-count').value);
                    for (let i = questionCount; i < target; i++) addQuestion(q.points_per_question || 1);
                }
            }
            updateQuestionProgress();
            openModal('quizModal');
        }

        // The error box sits at the top of the (long, scrolled) form — bring it into view.
        function showQuizErr(msg) {
            const err = document.getElementById('quiz-err');
            err.textContent = msg; err.style.display = 'block';
            err.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }

        // Max score / passing score boxes: digits only (letters, signs and decimal points are removed)
        function quizDigits(el, max) {
            const v = el.value, c = v.replace(/\D+/g, '').slice(0, max);
            if (c === v) return;
            const pos = Math.max(0, (el.selectionStart || 0) - (v.length - c.length));
            el.value = c;
            try { el.setSelectionRange(pos, pos); } catch (e) { }
        }
        // Edit Quiz: subject and module are fixed. Create Quiz: they can be picked again.
        function quizLockPickers(on) {
            ['quiz-course', 'quiz-module'].forEach(id => { const el = document.getElementById(id); if (el) el.disabled = !!on; });
        }
        // Required settings, same rules as api/quizzes.php. Returns [message, field id] or null.
        function quizCheckRequired() {
            const v = id => document.getElementById(id).value.trim();
            const maxEl = document.getElementById('quiz-maxscore'), passEl = document.getElementById('quiz-passing');
            const editing = !!document.getElementById('quiz-edit-id').value;
            const max = v('quiz-maxscore'), pass = v('quiz-passing'), open = v('quiz-open'), close = v('quiz-close');
            if (!v('quiz-desc')) return ['Instructions are required.', 'quiz-desc'];
            if (!max) return ['Max score is required.', 'quiz-maxscore'];
            const keepMax = editing && maxEl.dataset.orig && max === maxEl.dataset.orig;
            if (!keepMax && (!/^\d{1,5}$/.test(max) || Number(max) < 1 || Number(max) > 10000)) return ['Max score must be a whole number from 1 to 10000 (no decimals).', 'quiz-maxscore'];
            if (!pass) return ['Passing score is required.', 'quiz-passing'];
            const keepPass = editing && passEl.dataset.orig && pass === passEl.dataset.orig;
            if (!keepPass && (!/^\d{1,3}$/.test(pass) || Number(pass) < 1 || Number(pass) > 100)) return ['Passing score must be a whole number from 1 to 100 (%).', 'quiz-passing'];
            if (!open) return ['Open at is required.', 'quiz-open'];
            if (!close) return ['Close at is required.', 'quiz-close'];
            const o = new Date(open).getTime(), c = new Date(close).getTime();
            if (!isFinite(o)) return ['Open at is not a valid date.', 'quiz-open'];
            if (!isFinite(c)) return ['Close at is not a valid date.', 'quiz-close'];
            if (c <= o) return ['The closing time must be after the opening time.', 'quiz-close'];
            return null;
        }

        let quizSaving = false;
        async function submitQuiz(publish) {
            if (quizSaving) return;                                   // no double save
            quizSaving = true;
            try { await quizSubmitInner(publish); }
            catch (e) { showQuizErr('Network error. Please try again.'); }
            finally { quizSaving = false; }
        }
        async function quizJson(res) {
            const raw = await res.text();
            try { return JSON.parse(raw); } catch (e) { return { success: false, message: `Server error (${res.status}). Please try again.` }; }
        }
        async function quizSubmitInner(publish) {
            const err = document.getElementById('quiz-err'); err.style.display = 'none';
            const editId = document.getElementById('quiz-edit-id').value;
            const course_id = document.getElementById('quiz-course').value;
            const title = document.getElementById('quiz-title').value.trim();
            const mode = document.querySelector('input[name="quiz-mode"]:checked').value;
            if (!course_id || !title) { showQuizErr(!course_id ? 'Please choose a subject.' : 'Please enter a quiz title.'); document.getElementById(!course_id ? 'quiz-course' : 'quiz-title')?.focus(); return; }
            const reqErr = quizCheckRequired();
            if (reqErr) { showQuizErr(reqErr[0]); document.getElementById(reqErr[1])?.focus({ preventScroll: true }); return; }
            // a module is only picked when creating; an edited quiz keeps its own
            const sendModule = !editId && document.getElementById('quiz-module-wrap').style.display !== 'none';

            if (mode === 'file') {
                const fd = new FormData();
                if (editId) fd.append('id', editId);
                fd.append('course_id', course_id); fd.append('title', title);
                fd.append('description', document.getElementById('quiz-desc').value.trim());
                fd.append('time_limit', document.getElementById('quiz-time').value || '');
                fd.append('max_score', document.getElementById('quiz-maxscore').value.trim());
                fd.append('passing_score', document.getElementById('quiz-passing').value.trim());
                fd.append('open_at', document.getElementById('quiz-open').value);
                fd.append('close_at', document.getElementById('quiz-close').value);
                fd.append('quiz_type', 'file');
                if (sendModule) fd.append('module_id', document.getElementById('quiz-module').value || 'auto');
                const file = document.getElementById('quiz-file').files[0];
                if (file) fd.append('file', file);
                else if (!editId) { showQuizErr('Please upload a quiz file.'); return; }
                const res = await fetch(`../api/quizzes.php?action=${editId ? 'update' : 'create'}`, { method: 'POST', body: fd });
                const data = await quizJson(res);
                if (data.success) { closeModal('quizModal'); showToast(editId ? 'Quiz updated!' : 'Quiz created!'); mlAfterSave('quiz'); }
                else { showQuizErr(data.message || 'Could not save the quiz.'); }
                return;
            }

            const questions = [];
            document.querySelectorAll('.q-block').forEach(block => {
                const n = block.id.replace('q-block-', '');
                const qt = document.getElementById(`q-text-${n}`)?.value.trim(); if (!qt) return;
                const qtype = document.getElementById(`q-type-${n}`)?.value;
                const pts = document.getElementById(`q-pts-${n}`)?.value;
                if (qtype === 'essay' || qtype === 'long_essay') {
                    const idAns = document.getElementById(`q-idans-${n}`)?.value.trim() || '';
                    questions.push({ question_text: qt, question_type: qtype, points: pts, identification_answer: idAns });
                } else {
                    const choices = [];
                    const cv = block.querySelector(`input[name="correct-${n}"]:checked`)?.value ?? '0';
                    block.querySelectorAll(`#q-choices-${n} .choice-row`).forEach((row, idx) => { const txt = row.querySelector('input[type=text]')?.value.trim(); if (txt) choices.push({ choice_text: txt, is_correct: idx == cv ? 1 : 0 }); });
                    questions.push({ question_text: qt, question_type: qtype, points: pts, choices });
                }
            });

            if (publish) {
                const badPts = questions.findIndex(q => !(Number(q.points) > 0));
                if (badPts !== -1) { showQuizErr(`Question ${badPts + 1} needs points (more than 0).`); return; }
                // Identification = a word or short name (max 4 words), never a sentence
                const longId = questions.findIndex(q => q.question_type === 'essay' && (q.identification_answer.split(/\s+/).filter(Boolean).length > 4 || q.identification_answer.length > 60));
                if (longId !== -1) { showQuizErr(`Question ${longId + 1} (Identification): the answer must be a word or short name (up to 4 words), not a sentence. Use Essay for longer answers.`); return; }
            }

            const payload = {
                course_id, title,
                description: document.getElementById('quiz-desc').value.trim(),
                time_limit: document.getElementById('quiz-time').value || null,
                max_score: document.getElementById('quiz-maxscore').value.trim(),
                passing_score: document.getElementById('quiz-passing').value.trim(),
                open_at: document.getElementById('quiz-open').value,
                close_at: document.getElementById('quiz-close').value,
                quiz_type: 'questions',
                question_count: document.getElementById('quiz-question-count').value || null,
                points_per_question: document.getElementById('quiz-points-per-question').value || null,
                show_results: document.getElementById('quiz-show-results').checked ? 1 : 0,
                show_correct_answers: document.getElementById('quiz-show-correct').checked ? 1 : 0,
                status: publish ? 'Published' : 'Draft',
                questions
            };
            if (editId) payload.id = editId;
            if (sendModule) payload.module_id = document.getElementById('quiz-module').value || 'auto';
            const res = await fetch(`../api/quizzes.php?action=${editId ? 'update' : 'create'}`, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload) });
            const data = await quizJson(res);
            if (data.success) { closeModal('quizModal'); showToast(data.message || (publish ? 'Quiz published!' : 'Saved as draft.')); mlAfterSave('quiz'); }
            else { showQuizErr(data.message || 'Could not save the quiz.'); }
        }

        async function viewResults(quizId, title, maxScore, quizType) {
            window.__lastQuizMax = maxScore;
            document.getElementById('results-title').textContent = title + (quizType === 'file' ? ' — Grade Submissions' : ' — Results');
            document.getElementById('results-body').innerHTML = loadingCard();
            openModal('resultsModal');
            const res = await fetch(`../api/quizzes.php?action=results&id=${quizId}`);
            const data = await res.json();
            if (!data.success || !data.data.length) { document.getElementById('results-body').innerHTML = '<div style="padding:3rem;text-align:center;color:var(--ui-text-3, #bbb)"><div style="font-size:2.5rem;margin-bottom:.5rem"></div>No submissions yet.</div>'; return; }
            const isFile = quizType === 'file';
            const cols = isFile ? '1fr 90px 90px 120px 130px' : '1fr 75px 85px 70px 70px 90px';
            document.getElementById('results-body').innerHTML = `
        <div style="padding:.5rem 0">
            <div style="display:grid;grid-template-columns:${cols};gap:.5rem;padding:.5rem 1.25rem;font-size:.65rem;font-weight:800;letter-spacing:.07em;text-transform:uppercase;color:var(--ui-text-3, #bbb);background:var(--ui-surface-2, #f8f9fc);border-bottom:1px solid var(--ui-border-soft, #f0f2f5)">
                <span>Student</span>
                ${isFile ? `<span style="text-align:center">Submitted</span><span style="text-align:center">File</span><span style="text-align:center">Score / ${maxScore}</span><span style="text-align:center">Remarks</span>` : `<span style="text-align:center">School ID</span><span style="text-align:center">Score</span><span style="text-align:center">%</span><span style="text-align:center">Status</span><span style="text-align:center">Answers</span>`}
            </div>
            ${data.data.map(r => isFile ? `
            <div style="display:grid;grid-template-columns:${cols};gap:.5rem;align-items:center;padding:.85rem 1.25rem;border-bottom:1px solid var(--ui-border-soft, #f8f9fc)">
                <div><div style="font-size:.84rem;font-weight:700;color:var(--ui-text, #1a1a2e)">${escHtml(r.last_name + ', ' + r.first_name)}</div><div style="font-size:.7rem;color:var(--ui-text-3, #aaa)">${escHtml(r.section_dept || '')}</div></div>
                <div style="font-size:.72rem;color:var(--ui-text-2, #666);text-align:center">${r.submitted_at ? r.submitted_at.substring(0, 10) : '—'}</div>
                <div style="text-align:center">${r.file_path ? `<a href="../${r.file_path}" target="_blank" class="btn btn-blue btn-sm"> View</a>` : '<span style="color:#ccc;font-size:.75rem">—</span>'}</div>
                <div style="text-align:center">
                    <input type="number" min="0" max="${maxScore}" step="0.5" value="${r.score ?? ''}" placeholder="—"
                        data-attempt-id="${r.attempt_id}" data-student-id="${r.student_id}" data-quiz-id="${quizId}"
                        style="width:72px;padding:.3rem .5rem;border:1.5px solid var(--ui-border, #e0e4ee);border-radius:7px;font-size:.82rem;text-align:center;font-family:'Nunito',sans-serif;font-weight:700;outline:none"
                        onfocus="this.style.borderColor='#003087'" onblur="this.style.borderColor='#e0e4ee'">
                </div>
                <div style="text-align:center">
                    <input type="text" value="${escHtml(r.remarks || '')}" placeholder="Remarks" data-remarks-for-attempt="${r.attempt_id}"
                        style="width:100%;padding:.3rem .5rem;border:1.5px solid var(--ui-border, #e0e4ee);border-radius:7px;font-size:.78rem;outline:none"
                        onfocus="this.style.borderColor='#003087'" onblur="this.style.borderColor='#e0e4ee'">
                </div>
            </div>` : `
            <div style="display:grid;grid-template-columns:${cols};gap:.5rem;align-items:center;padding:.85rem 1.25rem;border-bottom:1px solid var(--ui-border-soft, #f8f9fc)">
                <div><div style="font-size:.84rem;font-weight:700;color:var(--ui-text, #1a1a2e)">${escHtml(r.last_name + ', ' + r.first_name)}</div><div style="font-size:.7rem;color:var(--ui-text-3, #aaa)">${escHtml(r.section_dept || '')}</div></div>
                <div style="font-size:.75rem;color:var(--ui-text-2, #666);text-align:center">${escHtml(r.school_id || '—')}</div>
                <div style="text-align:center">${r.score !== null ? `<strong style="color:${(r.score / maxScore) >= .75 ? '#003087' : (r.score / maxScore) >= .5 ? '#b38600' : '#c0392b'};font-size:.9rem">${r.score}</strong><span style="color:var(--ui-text-3, #aaa);font-size:.78rem">/${maxScore}</span>` : '<span style="color:#ccc">—</span>'}</div>
                <div style="text-align:center;font-size:.78rem;color:var(--ui-text-2, #666)">${r.percentage !== null ? r.percentage + '%' : '—'}</div>
                <div style="text-align:center">${Number(r.essays_to_grade) > 0 ? '<span class="badge badge-yellow" style="font-size:.63rem" title="Essay answers waiting for your grade">Essay to grade</span>' : r.passed === true ? '<span class="badge badge-green" style="font-size:.63rem">Passed</span>' : r.passed === false ? '<span class="badge badge-red" style="font-size:.63rem">Failed</span>' : '<span style="color:#ccc;font-size:.72rem">' + (r.finished_at ? 'Done' : 'In progress') + '</span>'}</div>
                <div style="text-align:center">${r.attempt_id ? `<button class="btn ${Number(r.essays_to_grade) > 0 ? 'btn-blue' : 'btn-gray'} btn-sm" data-name="${escHtml(r.last_name + ', ' + r.first_name)}" onclick="viewAnswers(${r.attempt_id}, this.dataset.name, ${quizId})">${Number(r.essays_to_grade) > 0 ? 'Grade' : 'View'}</button>` : '—'}</div>
            </div>`).join('')}
        </div>
        ${isFile ? `<div style="padding:1rem 1.25rem;background:var(--ui-surface-2, #f8f9fc);border-top:1px solid var(--ui-border-soft, #f0f2f5);display:flex;justify-content:flex-end;gap:.75rem">
            <button class="btn btn-gray btn-sm" onclick="closeModal('resultsModal')">Close</button>
            <button class="btn btn-green" onclick="saveQuizGrades(${quizId})"> Save All Grades</button>
        </div>` : ''}`;
        }

        // "Back to results" puts the results list (and its title) back exactly as it was.
        // Kept in a variable: putting the list's HTML inside an onclick="…" attribute broke
        // it (its quotes ended the attribute and the rest showed up as text with "\n").
        let resultsPrev = null;
        function backToResults() {
            if (!resultsPrev) return;
            document.getElementById('results-title').textContent = resultsPrev.title;
            document.getElementById('results-body').innerHTML = resultsPrev.body;
            resultsScrollTop();
            resultsPrev = null;
        }

        function resultsScrollTop() {
            const body = document.getElementById('results-body');
            body.scrollTop = 0;
            const box = body.closest('.modal');
            if (box) box.scrollTop = 0;
        }

        async function viewAnswers(attemptId, studentName, quizId) {
            const body = document.getElementById('results-body');
            resultsPrev = { title: document.getElementById('results-title').textContent, body: body.innerHTML };
            document.getElementById('results-title').textContent = 'Answers — ' + (studentName || 'Student');
            body.innerHTML = loadingCard();
            const backBtn = '<div style="text-align:right;margin-top:.5rem"><button type="button" class="btn btn-gray btn-sm" onclick="backToResults()">&larr; Back to results</button></div>';
            let data;
            try {
                const res = await fetch(`../api/quizzes.php?action=attempt_detail&attempt_id=${encodeURIComponent(attemptId)}`);
                data = await res.json();
            } catch (e) { data = null; }
            if (!data || !data.success || !Array.isArray(data.data) || !data.data.length) {
                body.innerHTML = '<div style="padding:2rem 1.25rem;text-align:center;color:var(--ui-text-3, #bbb)">' + (data && data.message ? escHtml(data.message) : 'No answers found.') + '</div><div style="padding:0 1.25rem 1rem">' + backBtn + '</div>';
                return;
            }
            const essays = data.data.filter(q => q.question_type === 'long_essay');
            const ptsOf = q => Number(q.points) > 0 ? Number(q.points) : 1;
            const fmtN = v => String(Number(v));
            const essayBadge = q => {
                if (q.answer_text === null || q.answer_text === undefined || String(q.answer_text).trim() === '') return `<span class="badge badge-red" style="font-size:.6rem;margin-left:.4rem">No answer · ${fmtN(q.points_earned || 0)}/${fmtN(ptsOf(q))}</span>`;
                return q.needs_grading == 1
                    ? `<span class="badge badge-yellow" style="font-size:.6rem;margin-left:.4rem">Essay · to grade · ?/${fmtN(ptsOf(q))}</span>`
                    : `<span class="badge badge-blue" style="font-size:.6rem;margin-left:.4rem">Essay · graded · ${fmtN(q.points_earned)}/${fmtN(ptsOf(q))}</span>`;
            };
            const essayBlock = q => `
                <div style="font-size:.8rem;color:var(--ui-text-2, #666);margin-bottom:.35rem">Student's essay:</div>
                <div style="white-space:pre-wrap;font-size:.84rem;line-height:1.55;color:var(--ui-text, #1a1a2e);background:var(--ui-surface-2, #f8f9fc);border:1px solid var(--ui-border-soft, #e6e9f0);border-radius:8px;padding:.65rem .8rem;max-height:320px;overflow:auto">${q.answer_text ? escHtml(q.answer_text) : '<i style="color:#aaa">No answer</i>'}</div>
                ${q.identification_answer ? `<div style="font-size:.74rem;color:var(--ui-text-3, #999);margin-top:.35rem">Your grading guide: ${escHtml(q.identification_answer)}</div>` : ''}
                <div style="display:flex;align-items:center;gap:.5rem;margin-top:.55rem">
                    <label style="font-size:.78rem;font-weight:700;color:var(--ui-text, #1a1a2e)" for="essay-pts-${q.question_id}">Points</label>
                    <input type="number" id="essay-pts-${q.question_id}" data-essay-q="${q.question_id}" min="0" max="${ptsOf(q)}" step="0.5"
                        value="${q.needs_grading == 1 ? '' : fmtN(q.points_earned || 0)}" placeholder="0–${fmtN(ptsOf(q))}"
                        style="width:80px;padding:.3rem .5rem;border:1.5px solid var(--ui-border, #e0e4ee);border-radius:7px;font-size:.84rem;text-align:center;font-weight:700">
                    <span style="font-size:.78rem;color:var(--ui-text-3, #999)">/ ${fmtN(ptsOf(q))}</span>
                </div>`;
            body.innerHTML = `<div style="padding:1rem 1.25rem">
                ${essays.length ? `<div style="font-size:.78rem;line-height:1.45;background:#fff8e1;border:1px solid #f3d27a;color:#7a5a00;border-radius:8px;padding:.6rem .8rem;margin-bottom:1rem">
                    Multiple choice, True/False and Identification are checked automatically. <b>Essay questions are graded by you</b> — enter the points, then press <b>Save essay grades</b>. The final score is computed once every essay has points.</div>` : ''}
                ${data.data.map((q, i) => `
                    <div style="margin-bottom:1.1rem;padding-bottom:1rem;border-bottom:1px solid var(--ui-border-soft, #f0f2f5)">
                        <div style="font-weight:700;font-size:.85rem;color:var(--ui-text, #1a1a2e);margin-bottom:.4rem">${i + 1}. ${escHtml(q.question_text)}
                            ${q.question_type === 'long_essay' ? essayBadge(q) : `<span class="badge ${q.is_correct == 1 ? 'badge-green' : 'badge-red'}" style="font-size:.6rem;margin-left:.4rem">${q.is_correct == 1 ? 'Correct' : 'Incorrect'} · ${q.points_earned ?? 0}/${q.points}</span>`}
                        </div>
                        ${q.question_type === 'long_essay' ? essayBlock(q) : q.question_type === 'essay'
                ? `<div style="font-size:.8rem;color:var(--ui-text-2, #666)">Student answered: <strong>${escHtml(q.answer_text || '—')}</strong></div>
                               <div style="font-size:.75rem;color:var(--ui-text-3, #aaa)">Accepted answer: ${escHtml(q.identification_answer || '—')}</div>`
                : (q.choices || []).map(c => `<div style="font-size:.8rem;padding:.25rem .5rem;border-radius:6px;margin-bottom:.15rem;background:${c.id == q.choice_id ? (c.is_correct == 1 ? '#d1fae5' : '#fee2e2') : (c.is_correct == 1 ? '#f0fdf4' : 'transparent')};color:${c.id == q.choice_id ? '#1a1a2e' : '#666'}">${c.id == q.choice_id ? '→ ' : ''}${escHtml(c.choice_text)}${c.is_correct == 1 ? ' (correct)' : ''}</div>`).join('')
            }
                    </div>`).join('')}
                ${essays.length ? `<div style="display:flex;justify-content:flex-end;gap:.5rem;align-items:center;margin-bottom:.5rem">
                    <span id="essay-grade-msg" style="font-size:.78rem;color:var(--ui-text-2, #666)"></span>
                    <button type="button" class="btn btn-green btn-sm" id="essay-grade-btn" onclick="saveEssayGrades(${Number(attemptId)}, ${Number(quizId) || 0})">Save essay grades</button>
                </div>` : ''}
                ${backBtn}
            </div>`;
            resultsScrollTop();
        }

        async function saveEssayGrades(attemptId, quizId) {
            const scores = {};
            let bad = null;
            document.querySelectorAll('#results-body input[data-essay-q]').forEach(inp => {
                const v = inp.value.trim();
                if (v === '') return;   // left blank = still to grade
                const n = Number(v), max = Number(inp.max);
                if (!isFinite(n) || n < 0 || n > max) { bad = bad || inp; return; }
                scores[inp.dataset.essayQ] = n;
            });
            const msg = document.getElementById('essay-grade-msg');
            if (bad) { msg.style.color = '#c0392b'; msg.textContent = `Points must be from 0 to ${bad.max}.`; bad.focus(); return; }
            if (!Object.keys(scores).length) { msg.style.color = '#c0392b'; msg.textContent = 'Enter points for at least one essay.'; return; }
            const btn = document.getElementById('essay-grade-btn');
            btn.disabled = true; btn.textContent = 'Saving...';
            let data;
            try {
                const res = await fetch('../api/quizzes.php?action=grade_essays', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ attempt_id: attemptId, scores }) });
                data = await res.json();
            } catch (e) { data = { success: false, message: 'Could not reach the server.' }; }
            btn.disabled = false; btn.textContent = 'Save essay grades';
            if (!data.success) { msg.style.color = '#c0392b'; msg.textContent = data.message || 'Could not save.'; return; }
            showToast(data.message || 'Essay grades saved.');
            // Reload the results list so the score and status are up to date
            const title = (resultsPrev && resultsPrev.title) || '';
            resultsPrev = null;
            if (quizId && typeof viewResults === 'function') {
                const m = title.match(/^(.*) — Results$/);
                const maxScore = window.__lastQuizMax || '';
                viewResults(quizId, m ? m[1] : title, maxScore, 'questions');
            }
            if (typeof loadQuizzes === 'function') try { loadQuizzes(); } catch (e) { }
        }

        async function saveQuizGrades(quizId) {
            const inputs = document.querySelectorAll('#results-body input[data-attempt-id]');
            const grades = [];
            inputs.forEach(inp => { const ri = document.querySelector(`input[data-remarks-for-attempt="${inp.dataset.attemptId}"]`); grades.push({ attempt_id: inp.dataset.attemptId, student_id: inp.dataset.studentId, quiz_id: quizId, score: inp.value, remarks: ri ? ri.value : '' }); });
            const res = await fetch('../api/quizzes.php?action=grade', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ grades }) });
            const data = await res.json();
            if (data.success) { showToast('Grades saved!'); closeModal('resultsModal'); loadQuizzes(); }
            else showToast('Could not save grades.', true);
        }

        async function deleteQuiz(id) { if (!confirm('Delete this quiz and all submissions?')) return; await fetch(`../api/quizzes.php?action=delete&id=${id}`, { method: 'DELETE' }); showToast(' Quiz deleted.'); loadQuizzes(); }

        /*  QUESTION BUILDER  */
        function addQuestion(presetPoints, existing) {
            questionCount++; const n = questionCount;
            const div = document.createElement('div'); div.className = 'q-block'; div.id = `q-block-${n}`;
            const qtext = existing ? (existing.question_text || '') : '';
            const qtype = existing ? (existing.question_type || 'multiple_choice') : 'multiple_choice';
            const pts = (existing && existing.points != null && existing.points !== '') ? existing.points : (presetPoints || 1);
            div.innerHTML = `
        <div class="q-block-head"><span class="q-block-num">Question ${n}</span><button class="btn btn-red btn-sm" onclick="document.getElementById('q-block-${n}').remove(); updateQuestionProgress();">Remove</button></div>
        <div class="form-grp" style="margin-bottom:.6rem"><input class="finput" type="text" id="q-text-${n}" placeholder="Enter question here..." value="${escHtml(qtext)}" oninput="updateQuestionProgress()"></div>
        <div style="display:flex;gap:.75rem;margin-bottom:.75rem;align-items:center">
            <select class="finput" id="q-type-${n}" style="width:auto" onchange="toggleType(${n})">
                <option value="multiple_choice">Multiple Choice</option>
                <option value="true_false">True / False</option>
                <option value="essay">Identification</option>
                <option value="long_essay">Essay (teacher grades)</option>
            </select>
            <input class="finput" type="number" id="q-pts-${n}" value="${pts}" min="0.5" step="0.5" style="width:80px" oninput="updateQuestionProgress()">
            <span class="form-lbl" style="margin:0">pts</span>
        </div>
        <div id="q-choices-${n}"></div>
        <button class="add-choice-btn" id="add-choice-btn-${n}" onclick="addChoice(${n})">+ Add Choice</button>`;
            document.getElementById('questions-wrap').appendChild(div);
            document.getElementById(`q-type-${n}`).value = qtype;
            renderChoiceArea(n, qtype, existing);
            updateQuestionProgress();
        }

        function renderChoiceArea(n, type, existing) {
            const wrap = document.getElementById(`q-choices-${n}`);
            const ab = document.getElementById(`add-choice-btn-${n}`);
            if (type === 'long_essay') {
                const guide = existing ? (existing.identification_answer || '') : '';
                wrap.innerHTML = `<div class="q-essay-note" style="font-size:.78rem;line-height:1.45;color:var(--ui-text-2, #555);background:var(--ui-surface-2, #f4f7ff);border:1px dashed var(--ui-border, #c9d6f0);border-radius:8px;padding:.6rem .75rem;margin-bottom:.6rem">
                        Students type a long answer. <b>This question is not auto-graded</b> — you give the points yourself after they submit (Results → View). The quiz score stays pending until you do.
                    </div>
                    <div class="form-grp"><label class="form-lbl">Grading guide (optional — only you see this)</label><input class="finput" type="text" id="q-idans-${n}" maxlength="255" placeholder="e.g. 2 pts thesis, 2 pts examples, 1 pt grammar" value="${escHtml(guide)}"></div>`;
                ab.style.display = 'none';
            } else if (type === 'essay') {
                const idAns = existing ? (existing.identification_answer || '') : '';
                wrap.innerHTML = `<div class="form-grp"><label class="form-lbl">Accepted Answer * <span style="font-weight:500;text-transform:none;letter-spacing:0;color:var(--ui-text-3, #999)">— one word or short name, not a sentence</span></label><input class="finput" type="text" id="q-idans-${n}" maxlength="60" placeholder="e.g. Photosynthesis · José Rizal" value="${escHtml(idAns)}" oninput="updateQuestionProgress()"></div>`;
                ab.style.display = 'none';
            } else if (type === 'true_false') {
                const cIdx = (existing && existing.choices) ? existing.choices.findIndex(c => Number(c.is_correct) === 1) : -1;
                wrap.innerHTML = `<div class="choice-row"><input type="radio" name="correct-${n}" value="0" ${cIdx === 0 ? 'checked' : ''} onchange="updateQuestionProgress()"><input type="text" value="True" readonly></div><div class="choice-row"><input type="radio" name="correct-${n}" value="1" ${cIdx === 1 ? 'checked' : ''} onchange="updateQuestionProgress()"><input type="text" value="False" readonly></div>`;
                ab.style.display = 'none';
            } else {
                const ch = (existing && existing.choices && existing.choices.length >= 2) ? existing.choices : [{ choice_text: '', is_correct: 0 }, { choice_text: '', is_correct: 0 }];
                wrap.innerHTML = ch.map((c, idx) => `<div class="choice-row"><input type="radio" name="correct-${n}" value="${idx}" title="Mark correct" ${Number(c.is_correct) === 1 ? 'checked' : ''} onchange="updateQuestionProgress()"><input type="text" placeholder="Choice ${String.fromCharCode(65 + idx)}" value="${escHtml(c.choice_text || '')}" oninput="updateQuestionProgress()"></div>`).join('');
                ab.style.display = '';
            }
        }

        function toggleType(n) {
            const type = document.getElementById(`q-type-${n}`).value;
            renderChoiceArea(n, type, null);
            updateQuestionProgress();
        }

        function addChoice(n) {
            const wrap = document.getElementById(`q-choices-${n}`); const idx = wrap.querySelectorAll('.choice-row').length;
            const row = document.createElement('div'); row.className = 'choice-row';
            row.innerHTML = `<input type="radio" name="correct-${n}" value="${idx}" title="Mark correct" onchange="updateQuestionProgress()"><input type="text" placeholder="Choice ${String.fromCharCode(65 + idx)}" oninput="updateQuestionProgress()">`;
            wrap.appendChild(row);
            updateQuestionProgress();
        }

        function updateQuestionProgress() {
            const blocks = document.querySelectorAll('.q-block');
            const targetEl = document.getElementById('quiz-question-count');
            const target = (targetEl && Number(targetEl.value)) || blocks.length;
            let completed = 0;
            blocks.forEach(block => {
                const n = block.id.replace('q-block-', '');
                const text = document.getElementById(`q-text-${n}`)?.value.trim();
                const type = document.getElementById(`q-type-${n}`)?.value;
                if (!text) return;
                if (type === 'long_essay') {
                    completed++;   // only needs the question text
                } else if (type === 'essay') {
                    const ans = document.getElementById(`q-idans-${n}`)?.value.trim();
                    if (ans) completed++;
                } else {
                    const filled = Array.from(block.querySelectorAll(`#q-choices-${n} .choice-row input[type=text]`)).filter(i => i.value.trim()).length;
                    const hasCorrect = !!block.querySelector(`input[name="correct-${n}"]:checked`);
                    if (filled >= 2 && hasCorrect) completed++;
                }
            });
            const label = document.getElementById('q-progress-label');
            if (label) label.textContent = `Questions: ${completed}/${target} completed`;
        }

        /*  GRADES — the classic grades panel was removed; see teacher/grades.php (Gradebook + insights)  */

        /*  ATTENDANCE  */
        const attStatusColors = { Present: '#00875a', Absent: '#c0392b', Late: '#b38600', Excused: '#7b2d8b' };
        const attStatusBg = { Present: '#e6fff4', Absent: '#fff0ec', Late: '#fffbe6', Excused: '#f3e8ff' };
        const attStatusEmoji = { Present: tico('check'), Absent: tico('x'), Late: tico('clock'), Excused: tico('quiz') };

        // Track selected status per student in a plain object — reliable across all browsers
        const attSelected = {};

        async function loadAttendance() {
            const cid = document.getElementById('att-course-select').value;
            const date = document.getElementById('att-date').value;
            const wrap = document.getElementById('att-table-wrap');

            if (!cid) {
                wrap.innerHTML = emptyCard(tico('check-square'), 'Select a subject and date to take attendance.');
                ['att-total', 'att-present', 'att-absent', 'att-late', 'att-excused'].forEach(id => document.getElementById(id).textContent = '—');
                return;
            }

            wrap.innerHTML = loadingCard();

            try {
                const res = await fetch(`../api/attendance.php?action=list&course_id=${cid}&date=${date}`);

                // Guard: parse error response even if it's not JSON
                let data;
                try { data = await res.json(); }
                catch (_) {
                    // Clone and read raw text to expose exact server output
                    const raw = await fetch(`../api/attendance.php?action=list&course_id=${cid}&date=${date}`).then(r => r.text());
                    console.error('RAW SERVER RESPONSE:', raw);
                    wrap.innerHTML = emptyCard(tico('alert'), 'Server error: ' + raw.substring(0, 300));
                    return;
                }

                if (!data.success) { wrap.innerHTML = emptyCard(tico('alert'), data.message || 'Failed to load attendance.'); return; }

                const students = data.students || [];
                const existing = data.attendance || {};

                if (!students.length) { wrap.innerHTML = emptyCard(tico('users'), 'No students enrolled in this subject.'); return; }

                // Reset selection state
                Object.keys(attSelected).forEach(k => delete attSelected[k]);

                let html = `<div style="background:var(--ui-surface, white);border-radius:16px;box-shadow:0 2px 14px rgba(0,0,0,.07);overflow:hidden">
                    <div style="padding:1.1rem 1.5rem;border-bottom:1px solid var(--ui-border-soft, #f0f2f5);display:flex;align-items:center;justify-content:space-between">
                        <h3 style="font-family:'Nunito',sans-serif;font-size:.95rem;font-weight:800;color:var(--ui-text, #1a1a2e)">Attendance — ${escHtml(date)}</h3>
                        <span class="badge badge-blue">${students.length} students</span>
                    </div>
                    <div style="display:grid;grid-template-columns:50px 1fr 130px 160px 120px;gap:.5rem;padding:.5rem 1.25rem;font-size:.65rem;font-weight:800;letter-spacing:.07em;text-transform:uppercase;color:var(--ui-text-3, #bbb);background:var(--ui-surface-2, #f8f9fc);border-bottom:1px solid var(--ui-border-soft, #f0f2f5)">
                        <span>#</span><span>Student Name</span><span style="text-align:center">LRN / School ID</span><span style="text-align:center">Status</span><span style="text-align:center">Remark</span>
                    </div>`;

                students.forEach((s, i) => {
                    const status = existing[s.id]?.status || 'Present';
                    const remark = existing[s.id]?.remark || '';
                    attSelected[s.id] = status; // store in state object
                    const initials = (s.first_name?.[0] || '').toUpperCase() + (s.last_name?.[0] || '').toUpperCase();

                    html += `<div style="display:grid;grid-template-columns:50px 1fr 130px 160px 120px;gap:.5rem;align-items:center;padding:.75rem 1.25rem;border-bottom:1px solid var(--ui-border-soft, #f8f9fc);transition:background .15s" onmouseover="this.style.background='#f6f9ff'" onmouseout="this.style.background=''">
                        <div style="font-family:'Nunito',sans-serif;font-weight:800;font-size:.8rem;color:var(--ui-text-3, #aaa)">${i + 1}</div>
                        <div style="display:flex;align-items:center;gap:.65rem">
                            <div id="att-avatar-${s.id}" style="width:30px;height:30px;border-radius:50%;background:${attStatusColors[status]};display:flex;align-items:center;justify-content:center;font-size:.8rem;font-weight:800;color:white;flex-shrink:0">${escHtml(initials)}</div>
                            <div style="font-size:.84rem;font-weight:700;color:var(--ui-text, #1a1a2e)">${escHtml(s.last_name + ', ' + s.first_name)}</div>
                        </div>
                        <div style="font-size:.78rem;color:var(--ui-text-2, #666);text-align:center">${escHtml(s.school_id || '—')}</div>
                        <div style="display:flex;gap:.3rem;justify-content:center;flex-wrap:wrap">
                            ${['Present', 'Absent', 'Late', 'Excused'].map(st => `
                                <button type="button" class="att-btn" data-student="${s.id}" data-status="${st}"
                                    onclick="setAttStatus(${s.id},'${st}')"
                                    style="background:${status === st ? attStatusBg[st] : '#f5f5f5'};color:${status === st ? attStatusColors[st] : '#999'};border:1.5px solid ${status === st ? attStatusColors[st] : '#e0e4ee'};font-size:.68rem;padding:.22rem .42rem;border-radius:7px;cursor:pointer;font-weight:700;font-family:'Nunito',sans-serif;transition:all .15s;white-space:nowrap">
                                    ${attStatusEmoji[st]} ${st}
                                </button>
                            `).join('')}
                        </div>
                        <div style="text-align:center">
                            <input type="text" id="att-remark-${s.id}" value="${escHtml(remark)}" placeholder="—"
                                style="width:92%;padding:.3rem .5rem;border:1.5px solid var(--ui-border, #e0e4ee);border-radius:7px;font-size:.75rem;text-align:center;outline:none;font-family:'Open Sans',sans-serif"
                                onfocus="this.style.borderColor='#003087'" onblur="this.style.borderColor='#e0e4ee'">
                        </div>
                    </div>`;
                });

                html += '</div>';
                wrap.innerHTML = html;

                // Use state object for accurate counts (no border-color string comparison)
                updateAttCounts();
                document.getElementById('att-total').textContent = students.length;

            } catch (err) {
                wrap.innerHTML = emptyCard(tico('alert'), 'Error loading attendance: ' + err.message);
                console.error('loadAttendance error:', err);
            }
        }

        function setAttStatus(studentId, status) {
            // Update state object — single source of truth
            attSelected[studentId] = status;

            // Update button styles
            document.querySelectorAll(`.att-btn[data-student="${studentId}"]`).forEach(btn => {
                const st = btn.dataset.status;
                const active = (st === status);
                btn.style.background = active ? attStatusBg[st] : '#f5f5f5';
                btn.style.color = active ? attStatusColors[st] : '#999';
                btn.style.borderColor = active ? attStatusColors[st] : '#e0e4ee';
            });

            // Update avatar color to reflect current status
            const avatar = document.getElementById(`att-avatar-${studentId}`);
            if (avatar) avatar.style.background = attStatusColors[status] || '#555';

            updateAttCounts();
        }

        function updateAttCounts() {
            // Count directly from the state object — reliable, no DOM color comparison
            let p = 0, a = 0, l = 0, e = 0;
            Object.values(attSelected).forEach(st => {
                if (st === 'Present') p++;
                else if (st === 'Absent') a++;
                else if (st === 'Late') l++;
                else if (st === 'Excused') e++;
            });
            document.getElementById('att-present').textContent = p;
            document.getElementById('att-absent').textContent = a;
            document.getElementById('att-late').textContent = l;
            document.getElementById('att-excused').textContent = e;
            // Also update total in case called before load completes
            const total = Object.keys(attSelected).length;
            if (total > 0) document.getElementById('att-total').textContent = total;
        }

        function markAll(status) {
            if (!Object.keys(attSelected).length) {
                showToast('Load a subject first before marking attendance.', true);
                return;
            }
            Object.keys(attSelected).forEach(sid => setAttStatus(parseInt(sid), status));
            showToast(`All students marked as ${status}`);
        }

        async function saveAttendance() {
            const cid = document.getElementById('att-course-select').value;
            const date = document.getElementById('att-date').value;
            if (!cid) { showToast('Select a subject first.', true); return; }
            if (!date) { showToast('Select a date first.', true); return; }

            if (!Object.keys(attSelected).length) {
                showToast('No attendance data to save. Load students first.', true);
                return;
            }

            const records = Object.entries(attSelected).map(([sid, status]) => {
                const remarkEl = document.getElementById(`att-remark-${sid}`);
                return {
                    student_id: parseInt(sid),
                    status,
                    remark: remarkEl ? remarkEl.value.trim() : ''
                };
            });

            // Disable save button to prevent double-submit
            const saveBtn = document.querySelector('[onclick="saveAttendance()"]');
            if (saveBtn) { saveBtn.disabled = true; saveBtn.textContent = '[...] Saving…'; }

            try {
                const res = await fetch('../api/attendance.php?action=save', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ course_id: parseInt(cid), date, records })
                });
                const data = await res.json();
                if (data.success) showToast('Attendance saved successfully!');
                else showToast('' + (data.message || 'Failed to save attendance.'), true);
            } catch (err) {
                showToast('Error saving attendance.', true);
                console.error('saveAttendance error:', err);
            } finally {
                if (saveBtn) { saveBtn.disabled = false; saveBtn.textContent = ' Save Attendance'; }
            }
        }

        function exportAttendance() {
            const cid = document.getElementById('att-course-select').value;
            const date = document.getElementById('att-date').value;
            if (!cid) { showToast('Select a subject first.', true); return; }
            window.open(`../api/attendance.php?action=export&course_id=${cid}&date=${date}`, '_blank');
        }

        //  Notification Bell 
        (function initNotif() {
            const STORAGE_KEY = 'elms_read_notifs_teacher';
            function getRead() {
                try { return new Set(JSON.parse(localStorage.getItem(STORAGE_KEY) || '[]')); } catch { return new Set(); }
            }
            function saveRead(set) {
                try { localStorage.setItem(STORAGE_KEY, JSON.stringify([...set])); } catch { }
            }

            function renderNotifs() {
                const read = getRead();
                const list = document.getElementById('notifList');
                const badge = document.getElementById('notifBadge');
                if (!_notifData || _notifData.length === 0) {
                    list.innerHTML = '<div class="notif-empty"><span>' + tico('bell-off') + '</span>No notifications yet.</div>';
                    return;
                }
                let unread = 0;
                let html = '';
                _notifData.forEach(n => {
                    const isUnread = !read.has(n.id);
                    if (isUnread) unread++;

                    let label;
                    if (n.type === 'submission') {
                        label = `<span style="color:#b38600;font-weight:700">${tico('inbox')}</span> `;
                    } else if (n.course) {
                        label = `<span style="color:var(--ui-link, #003087);font-weight:700">[${escHtml(n.course)}]</span> `;
                    } else {
                        label = '<span style="color:#1e7e34;font-weight:700">[School-wide]</span> ';
                    }

                    const titleText = n.type === 'submission'
                        ? `${escHtml(n.author)} submitted "${escHtml(n.title)}"`
                        : escHtml(n.title);

                    const metaText = n.type === 'submission'
                        ? `${escHtml(n.course || '')} · ${escHtml(n.posted_at)}`
                        : `${escHtml(n.author)} · ${escHtml(n.posted_at)}`;

                    html += `<div class="notif-item${isUnread ? ' unread' : ''}" onclick="notifClick('${n.id}', '${n.type}', ${n.ref_id})">
                        <div class="notif-dot"></div>
                        <div>
                            <div class="notif-item-title">${label}${titleText}</div>
                            <div class="notif-item-meta">${metaText}</div>
                        </div>
                    </div>`;
                });
                list.innerHTML = html;
                // The number on the bell comes from the live bell (includes/live.php),
                // which replaces this list once the server answers.
            }

            window.toggleNotif = function (e) {
                e.stopPropagation();
                document.getElementById('notifDropdown').classList.toggle('open');
            };
            window.closeNotif = function () {
                document.getElementById('notifDropdown').classList.remove('open');
            };
            window.notifClick = function (id, type, refId) {
                const read = getRead();
                read.add(id);
                saveRead(read);
                renderNotifs();
                if (type === 'submission') {
                    location.href = 'assignments.php';
                } else {
                    location.href = 'announcements.php';
                }
                closeNotif();
            };
            window.markAllRead = function () {
                const read = getRead();
                _notifData.forEach(n => read.add(n.id));
                saveRead(read);
                renderNotifs();
            };

            document.addEventListener('click', function (e) {
                const wrap = document.getElementById('notifWrap');
                if (wrap && !wrap.contains(e.target)) closeNotif();
            });

            renderNotifs();
        })();
    </script>
    <?php
    // EduBot floating button: every Teacher page except the dedicated EduBot AI page.
    if (($currentPage ?? '') !== 'edubot.php') {
        include __DIR__ . '/chatbot_float.php';
    }
    ?>
    <?php require __DIR__ . '/footer_common.php'; ?>
</body>

    </html><?php elseif ($__role === 'Student'): ?>
    </main>


    </div>

    <!-- Quiz Modal -->
    <!-- Online quiz: once started it cannot be closed; leaving submits it (includes/quiz_attempts.php) -->
    <style>
        .qz-lock { display: flex; gap: .6rem; align-items: flex-start; margin: .9rem 1.25rem 0; padding: .7rem .85rem; border-radius: 10px;
                   background: #fff7e0; color: #7a5200; font-size: .84rem; line-height: 1.45; border: 1px solid #f3d27a; }
        html[data-theme="dark"] .qz-lock { background: rgba(251, 191, 36, .1); color: #fcd34d; border-color: rgba(251, 191, 36, .3); }
        .qz-lock svg { width: 18px; height: 18px; flex-shrink: 0; margin-top: .05rem; }
        .qz-foot { display: flex; align-items: center; gap: .75rem; width: 100%; }
        .qz-count { margin-right: auto; font-size: .84rem; color: var(--text-muted, #64748b); }
        .qz-saved { font-size: .76rem; color: var(--text-muted, #64748b); }
        /* Title, warning and Submit always visible; only the questions scroll */
        #quizModal .modal { display: flex; flex-direction: column; max-height: calc(100vh - 2rem); max-height: calc(100dvh - 2rem); overflow: hidden; }
        #quizModal .modal-header, #quizModal .qz-lock, #quizModal .modal-footer { flex-shrink: 0; }
        #quizModal .modal-body { flex: 1 1 auto; min-height: 0; max-height: none; overflow-y: auto; }
        .qz-short { display: none; }
        /* One question at a time */
        .qz-q[hidden] { display: none !important; }
        .qz-step { font-size: .74rem; font-weight: 800; letter-spacing: .05em; text-transform: uppercase; color: var(--text-muted, #64748b); margin-bottom: .45rem; }
        .qz-qtext { font-weight: 700; margin-bottom: .75rem; line-height: 1.45; overflow-wrap: anywhere; }
        .qz-opt { display: flex; gap: .6rem; align-items: center; cursor: pointer; padding: .7rem .8rem; border: 1px solid var(--ui-border-soft, #e5e7eb); border-radius: 8px; overflow-wrap: anywhere; }
        .qz-opt:hover { border-color: var(--primary, #003087); }
        .qz-opt:has(input:checked) { border-color: var(--primary, #003087); background: var(--primary-light, #eef3ff); }
        .qz-opt input { flex-shrink: 0; }
        .qz-need { font-size: .76rem; color: var(--text-muted, #64748b); }
        /* Result after submitting: shown inside the quiz window instead of a browser pop-up */
        .qz-res { --qr-a: #2563eb; --qr-b: #7c3aed; --qr-soft: #eef2ff; --qr-ink: #1e3a8a;
                  position: relative; overflow: hidden; border-radius: 18px; border: 1px solid var(--ui-border-soft, #e5e7eb); background: var(--ui-surface, #fff); }
        .qz-res.pass { --qr-a: #059669; --qr-b: #10b981; --qr-soft: #ecfdf5; --qr-ink: #065f46; }
        .qz-res.fail { --qr-a: #e11d48; --qr-b: #f97316; --qr-soft: #fff1f2; --qr-ink: #9f1239; }
        .qz-res-top { position: relative; padding: 1.4rem 1.25rem 4.2rem; text-align: center; color: #fff;
                      background: linear-gradient(135deg, var(--qr-a), var(--qr-b)); }
        .qz-res-top::before, .qz-res-top::after { content: ''; position: absolute; border-radius: 50%; background: rgba(255,255,255,.12); pointer-events: none; }
        .qz-res-top::before { width: 180px; height: 180px; top: -90px; left: -50px; }
        .qz-res-top::after { width: 120px; height: 120px; bottom: -40px; right: -30px; }
        .qz-res-kicker { position: relative; font-size: .7rem; font-weight: 800; letter-spacing: .12em; text-transform: uppercase; opacity: .85; }
        .qz-res-h { position: relative; font-size: 1.45rem; font-weight: 900; margin-top: .25rem; line-height: 1.2; }
        .qz-res-msg { position: relative; font-size: .86rem; opacity: .92; margin-top: .3rem; max-width: 420px; margin-left: auto; margin-right: auto; }
        .qz-ring { position: relative; width: 150px; height: 150px; margin: -3.6rem auto 0; border-radius: 50%; background: var(--ui-surface, #fff);
                   box-shadow: 0 10px 30px rgba(15, 23, 42, .18); display: flex; align-items: center; justify-content: center; }
        .qz-ring svg { position: absolute; inset: 9px; width: 132px; height: 132px; transform: rotate(-90deg); }
        .qz-ring .trk { fill: none; stroke: var(--qr-soft); stroke-width: 11; }
        .qz-ring .val { fill: none; stroke: url(#qzGrad); stroke-width: 11; stroke-linecap: round; transition: stroke-dashoffset 1.2s cubic-bezier(.2,.8,.2,1); }
        .qz-ring .mark { stroke: var(--qr-ink); stroke-width: 3; stroke-linecap: round; opacity: .55; }
        .qz-ring-in { position: relative; text-align: center; line-height: 1.05; }
        .qz-ring-pct { font-size: 2.1rem; font-weight: 900; color: var(--qr-ink); }
        .qz-ring-pct small { font-size: 1rem; font-weight: 800; }
        .qz-ring-lbl { font-size: .62rem; font-weight: 800; letter-spacing: .1em; text-transform: uppercase; color: var(--text-muted, #64748b); margin-top: .2rem; }
        .qz-res-body { padding: .9rem 1.25rem 1.3rem; text-align: center; }
        .qz-res-score { font-size: 1.05rem; font-weight: 800; color: var(--ui-text, #0f172a); }
        .qz-res-score span { color: var(--text-muted, #64748b); font-weight: 700; }
        .qz-res-badge { display: inline-flex; align-items: center; gap: .35rem; margin-top: .55rem; border-radius: 100px; padding: .32rem .9rem; font-size: .78rem; font-weight: 800;
                        background: var(--qr-soft); color: var(--qr-ink); }
        .qz-res-badge svg { width: 14px; height: 14px; }
        .qz-res-stats { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: .55rem; margin-top: 1.1rem; }
        .qz-res-stat { border-radius: 12px; background: var(--ui-surface-2, #f8fafc); border: 1px solid var(--ui-border-soft, #eef2f7); padding: .6rem .4rem; min-width: 0; }
        .qz-res-stat b { display: block; font-size: 1rem; font-weight: 900; color: var(--ui-text, #0f172a); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .qz-res-stat span { display: block; font-size: .64rem; font-weight: 800; letter-spacing: .07em; text-transform: uppercase; color: var(--text-muted, #64748b); margin-top: .1rem; }
        .qz-res-tip { margin-top: 1rem; font-size: .8rem; color: var(--text-muted, #64748b); line-height: 1.5; }
        .qz-res-wait { width: 88px; height: 88px; margin: -3rem auto 0; border-radius: 50%; background: var(--ui-surface, #fff); box-shadow: 0 10px 30px rgba(15, 23, 42, .18);
                       display: flex; align-items: center; justify-content: center; color: var(--qr-a); position: relative; }
        .qz-res-wait svg { width: 40px; height: 40px; animation: qzTick 2.4s linear infinite; transform-origin: 50% 50%; }
        @keyframes qzTick { to { transform: rotate(360deg); } }
        .qz-confetti { position: absolute; inset: 0; pointer-events: none; overflow: hidden; z-index: 3; }
        .qz-confetti i { position: absolute; top: -12px; width: 8px; height: 13px; border-radius: 2px; opacity: 0; animation: qzFall 2.6s cubic-bezier(.25,.6,.4,1) forwards; }
        @keyframes qzFall { 0% { opacity: 1; transform: translate3d(0, 0, 0) rotate(0); } 100% { opacity: 0; transform: translate3d(var(--dx), 360px, 0) rotate(var(--rot)); } }
        .qz-pop { animation: qzPop .45s cubic-bezier(.2,1.4,.4,1) both; }
        @keyframes qzPop { from { opacity: 0; transform: scale(.92) translateY(8px); } to { opacity: 1; transform: none; } }
        html[data-theme="dark"] .qz-res { --qr-soft: rgba(255,255,255,.08); }
        html[data-theme="dark"] .qz-res .qz-ring-pct, html[data-theme="dark"] .qz-res .qz-res-badge { color: #e5ebf5; }
        html[data-theme="dark"] .qz-res .qz-ring .mark { stroke: #e5ebf5; }
        @media (max-width: 480px) { .qz-res-h { font-size: 1.2rem; } .qz-res-stats { gap: .4rem; } .qz-res-stat b { font-size: .9rem; } }
        @media (prefers-reduced-motion: reduce) {
            .qz-ring .val { transition: none; } .qz-res-wait svg, .qz-pop { animation: none; } .qz-confetti { display: none; }
        }
        @media (max-width: 560px) {
            .qz-lock { margin: .6rem .75rem 0; padding: .55rem .7rem; font-size: .8rem; }
            .qz-long { display: none; } .qz-short { display: inline; }
            #quizModal .modal-footer { padding: .75rem 1rem; }
            .qz-saved { display: none; }
        }
    </style>
    <div class="modal-overlay hidden" id="quizModal" role="dialog" aria-modal="true" aria-labelledby="quiz-modal-title">
        <div class="modal">
            <div class="modal-header">
                <span id="quiz-modal-title" style="font-size:1.1rem;">Quiz</span>
                <span id="quiz-timer" style="font-family:monospace; font-weight:700; color:var(--danger);"></span>
            </div>
            <div class="qz-lock" id="quiz-lock-note" style="display:none;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="4" y="11" width="16" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg>
                <span><b>Stay on this page until you submit.</b> <span class="qz-long">If you close or leave it, your quiz is submitted right away with the answers you have so far — only correct answers get points.</span><span class="qz-short">Leaving submits your answers.</span></span>
            </div>
            <div class="modal-body" id="quiz-modal-body">Loading...</div>
            <div class="modal-footer">
                <div class="qz-foot">
                    <span class="qz-count" id="quiz-count"></span>
                    <span class="qz-saved" id="quiz-saved"></span>
                    <button class="btn btn-outline" id="quiz-close-btn" onclick="closeQuizModal()" style="display:none;">Close</button>
                    <button class="btn btn-primary" id="quiz-next-btn" onclick="quizNext()" style="display:none;">Next</button>
                    <button class="btn btn-primary" id="quiz-submit-btn" onclick="submitQuiz()">Submit Quiz</button>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/elms-upload-rules.js"></script>
    <!-- File Upload Modal -->
    <div class="modal-overlay hidden" id="submitModal">
        <div class="modal">
            <div class="modal-header">
                <span>Submit Answer</span>
                <button onclick="document.getElementById('submitModal').classList.add('hidden')"
                    style="background:none;border:none;font-size:1.5rem;cursor:pointer;">&times;</button>
            </div>
            <div class="modal-body">
                <p id="submit-quiz-name" style="margin-bottom:1rem; font-weight:600;"></p>
                <label style="display:block; margin-bottom:0.5rem; font-weight:500;">Choose File</label>
                <input type="file" id="submit-file" class="form-control" style="width:100%;" accept=".pdf,.docx,.pptx,.xlsx,.jpg,.jpeg,.png,.mp4">
                <div style="font-size:0.78rem; opacity:0.7; margin-top:0.4rem;">PDF, DOCX, PPTX, XLSX &middot; JPG, PNG (max 20 MB) &middot; MP4 (max 100 MB)</div>
                <div id="submit-err" style="color:var(--danger); font-size:0.85rem; margin-top:0.5rem; display:none;">
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline"
                    onclick="document.getElementById('submitModal').classList.add('hidden')">Cancel</button>
                <button class="btn btn-primary" onclick="submitQuizFile()">Upload File</button>
            </div>
        </div>
    </div>

    <script>
        // Dark mode — persisted in localStorage, applied on next load by the
        // small inline script at the top of includes/header.php (so there's
        // no flash of the light theme before this file even loads).
        function toggleDarkMode() {
            const root = document.documentElement;
            const isDark = root.getAttribute('data-theme') === 'dark';
            if (isDark) root.removeAttribute('data-theme');
            else root.setAttribute('data-theme', 'dark');
            try { localStorage.setItem('elms_theme', isDark ? 'light' : 'dark'); } catch (e) { /* localStorage unavailable — the toggle still works for this page view */ }
        }

        // Tells any other open tab (e.g. the dashboard, if it's open elsewhere)
        // to refresh its Learning Progress / Recent Grades cards right away,
        // instead of waiting for its next poll. See the 'storage' listener in
        // student/dashboard.php.
        function notifyProgressChanged() {
            try { localStorage.setItem('elms_progress_ping', String(Date.now())); } catch (e) { /* storage may be unavailable — polling still covers it */ }
        }

        // Navigation Logic
        function showPanel(name, btn) {
            document.querySelectorAll('.section-panel').forEach(p => p.classList.remove('active'));
            document.querySelectorAll('.nav-link').forEach(l => l.classList.remove('active'));

            const panel = document.getElementById('panel-' + name);
            if (panel) panel.classList.add('active');
            if (btn) btn.classList.add('active');

            const title = btn ? btn.innerText.trim() : name.charAt(0).toUpperCase() + name.slice(1);
            document.getElementById('topbar-title').innerText = title;
        }

        // Accordion Logic
        function toggleBlock(id) {
            const el = document.getElementById(id);
            const chev = document.getElementById('chev-' + id);
            el.classList.toggle('open');
            if (chev) chev.style.transform = el.classList.contains('open') ? 'rotate(180deg)' : 'rotate(0deg)';
        }

        // Filtering
        function filterTbl(tbodyId, val) {
            val = val.toLowerCase();
            document.querySelectorAll('#' + tbodyId + ' tr').forEach(tr => {
                tr.style.display = tr.innerText.toLowerCase().includes(val) ? '' : 'none';
            });
        }

        function filterMod() {
            const q = (document.getElementById('mod-search')?.value || '').toLowerCase();
            const c = document.getElementById('mod-filter-course').value;
            document.querySelectorAll('.section-panel .accordion[data-code]').forEach(acc => {
                const code = acc.dataset.code;
                if (c && code !== c) { acc.style.display = 'none'; return; }
                acc.style.display = ''; let any = false;
                acc.querySelectorAll('.acc-row').forEach(row => {
                    const txt = row.dataset.title;
                    const show = !q || txt.includes(q);
                    row.style.display = show ? '' : 'none';
                    if (show) any = true;
                });
                if (q && !any) acc.style.display = 'none';
            });
        }

        function filterQuiz() {
            const q = (document.getElementById('quiz-search')?.value || '').toLowerCase();
            const c = document.getElementById('quiz-filter-course').value;
            document.querySelectorAll('.section-panel .accordion[data-code]').forEach(acc => {
                const code = acc.dataset.code;
                if (c && code !== c) { acc.style.display = 'none'; return; }
                acc.style.display = ''; let any = false;
                acc.querySelectorAll('.acc-row').forEach(row => {
                    const txt = row.dataset.title;
                    const show = !q || txt.includes(q);
                    row.style.display = show ? '' : 'none';
                    if (show) any = true;
                });
                if (q && !any) acc.style.display = 'none';
            });
        }

        // --- NOTIFICATION & USER MENU LOGIC ---

        function toggleNotif(e) {
            e.stopPropagation();
            const d = document.getElementById('notifDropdown');
            const ud = document.getElementById('userDropdown');
            // Close user menu if open
            if (ud.style.display === 'block') ud.style.display = 'none';

            d.style.display = d.style.display === 'block' ? 'none' : 'block';
            // The number goes down as notifications are opened (includes/live.php)
        }

        function toggleUserMenu(e) {
            e.stopPropagation();
            const d = document.getElementById('userDropdown');
            const nd = document.getElementById('notifDropdown');
            // Close notif menu if open
            if (nd.style.display === 'block') nd.style.display = 'none';

            d.style.display = d.style.display === 'block' ? 'none' : 'block';
        }

        function goToAnnouncement(id) {
            closeAllDropdowns();
            location.href = 'announcements.php';
            setTimeout(() => {
                const el = document.getElementById('ann-' + id);
                if (el) {
                    el.scrollIntoView({ behavior: "smooth", block: "center" });
                    el.style.background = '#eef2ff';
                    setTimeout(() => el.style.background = 'transparent', 1000);
                }
            }, 100);
        }

        function closeNotifAndGoToPanel() {
            closeAllDropdowns();
            location.href = 'announcements.php';
        }

        function closeAllDropdowns() {
            document.getElementById('notifDropdown').style.display = 'none';
            document.getElementById('userDropdown').style.display = 'none';
        }

        // --- END MENU LOGIC ---

        // --- MODULE LOGIC (No Refresh) ---

        function autoMarkDone(mid, cid) {
            const row = document.querySelector(`.acc-row[data-id="${mid}"]`);
            if (row && row.dataset.status !== 'done') {
                const iconContainer = row.querySelector('.status-icon');
                iconContainer.innerHTML = '<svg class="icon icon-sm" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"></polyline></svg>';
                iconContainer.style.background = '#d1fae5';
                iconContainer.style.color = '#059669';

                const badge = row.querySelector('.status-badge');
                badge.className = 'badge badge-green';
                badge.innerText = 'Done';

                const btnText = row.querySelector('.btn-text');
                if (btnText) btnText.innerText = 'View';

                row.dataset.status = 'done';

                const accordion = row.closest('.accordion');
                if (accordion) {
                    const countSpan = accordion.querySelector('.mod-count');
                    const pctSpan = accordion.querySelector('.mod-pct');
                    let currentDone = parseInt(accordion.dataset.done);
                    let total = parseInt(accordion.dataset.total);

                    let newDone = currentDone + 1;
                    let newPct = Math.round((newDone / total) * 100);

                    if (countSpan) countSpan.innerText = newDone;
                    pctSpan.innerText = newPct + '%';
                    accordion.dataset.done = newDone;
                }
            }

            fetch('../api/module_progress.php?action=mark', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ module_id: mid, course_id: cid })
            })
                .then(r => r.json())
                .then(data => { if (data && data.success) notifyProgressChanged(); })
                .catch(err => console.error(err));
        }

        // --- QUIZ LOGIC (No Refresh) ---

        let currentQuizId = null;     // attempt id while a quiz is open; quiz id for file uploads
        let currentQuizRowId = null;  // the quiz's row on the page
        let timerInterval = null;
        let answers = {};
        // ── No exit, no resume ──
        // quizActive = a started online quiz is on screen. While it is:
        //  · the window has no Cancel / close, and leaving the page asks first;
        //  · every answer is saved to the server (action=save) + a "still here" ping every 20 s;
        //  · if the page is left anyway, the quiz is submitted right away (sendBeacon) with the
        //    answers given — the server grades them (only correct answers score).
        let quizActive = false;
        let quizSaveTimer = null, quizBeat = null, quizTotalQ = 0;
        // One question at a time: quizQIds = question ids in order, quizIdx = the one on screen.
        // The student answers it, then presses Next (no going back). The last one has Submit.
        let quizQIds = [], quizIdx = 0, quizStartedAt = 0;

        function quizIsAnswered(qid) {
            const a = answers[qid];
            return !!(a && (a.choice_id || (typeof a.text === 'string' && a.text.trim() !== '')));
        }
        function quizShowCount() {
            const el = document.getElementById('quiz-count');
            if (el) el.textContent = quizTotalQ ? `Question ${Math.min(quizIdx + 1, quizTotalQ)} of ${quizTotalQ}` : '';
            if (!quizActive) return;
            const last = quizIdx >= quizTotalQ - 1;
            const done = !quizTotalQ || quizIsAnswered(quizQIds[quizIdx]);
            const next = document.getElementById('quiz-next-btn'), sb = document.getElementById('quiz-submit-btn');
            next.style.display = last ? 'none' : '';
            next.disabled = !done;
            next.title = done ? '' : 'Answer this question first';
            sb.style.display = last ? '' : 'none';
            if (sb.innerText !== 'Submitting...') sb.disabled = !done;
        }
        function quizShowQuestion(i) {
            quizIdx = Math.max(0, Math.min(i, quizTotalQ - 1));
            document.querySelectorAll('#quiz-modal-body .qz-q').forEach(el => { el.hidden = Number(el.dataset.i) !== quizIdx; });
            const body = document.getElementById('quiz-modal-body');
            if (body) body.scrollTop = 0;
            quizShowCount();
            const cur = document.querySelector(`#quiz-modal-body .qz-q[data-i="${quizIdx}"] :is(input, textarea)`);
            if (cur) setTimeout(() => { try { cur.focus({ preventScroll: true }); } catch (e) { } }, 30);
        }
        function quizNext() {
            if (!quizActive || quizIdx >= quizTotalQ - 1) return;
            if (!quizIsAnswered(quizQIds[quizIdx])) { quizShowCount(); return; }   // answer first
            quizSave();                                                            // keep the answer safe right away
            quizShowQuestion(quizIdx + 1);
        }
        function quizAnswer(qid, value) {
            answers[qid] = value;
            quizShowCount();
            clearTimeout(quizSaveTimer);
            quizSaveTimer = setTimeout(quizSave, 500);
        }
        async function quizSave() {
            if (!quizActive || !currentQuizId) return;
            try {
                const res = await fetch('../api/student_quiz.php?action=save', {
                    method: 'POST', headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ attempt_id: currentQuizId, answers })
                });
                const d = await res.json();
                if (d.success) { const s = document.getElementById('quiz-saved'); if (s) s.textContent = 'Saved'; return; }
                if (d.finished) { quizEnd(); alert(d.message || 'Your quiz was submitted.'); quizAfterSubmit(); }
            } catch (e) { const s = document.getElementById('quiz-saved'); if (s) s.textContent = 'Not saved — check your connection'; }
        }
        // After a successful submit the window stays open and shows the score inside it;
        // the page refreshes (quizAfterSubmit) only when the student presses Close.
        let quizResultData = null;
        function quizShowResult(data, timeUp) {
            quizActive = false;
            clearInterval(timerInterval); clearInterval(quizBeat); clearTimeout(quizSaveTimer);
            quizResultData = data || {};
            const fmt = v => (v === null || v === undefined || v === '') ? '—' : (isFinite(Number(v)) ? String(Number(v)) : String(v));
            const esc = v => { const d = document.createElement('div'); d.textContent = v == null ? '' : String(v); return d.innerHTML; };
            const hasScore = data && data.score !== undefined && data.score !== null && isFinite(Number(data.score));
            const ico = {
                check: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>',
                flag: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 22V4"/><path d="M4 4h12l-2 4 2 4H4"/></svg>',
                clock: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><polyline points="12 7 12 12 15 14"/></svg>',
            };
            // little facts for the stat tiles
            const answered = quizQIds.filter(quizIsAnswered).length;
            const secs = quizStartedAt ? Math.max(0, Math.round((Date.now() - quizStartedAt) / 1000)) : null;
            const took = secs === null ? '—' : (secs >= 3600 ? `${Math.floor(secs / 3600)}h ${Math.floor(secs % 3600 / 60)}m` : (secs >= 60 ? `${Math.floor(secs / 60)}m ${secs % 60}s` : `${secs}s`));
            const kicker = timeUp ? 'Time is up · quiz submitted' : 'Quiz submitted';
            let html;
            if (hasScore) {
                const pct = Math.max(0, Math.min(100, Number(data.percentage) || 0));
                const pass = data.passing_score === null || data.passing_score === undefined || !isFinite(Number(data.passing_score)) ? null : Number(data.passing_score);
                const passed = data.passed === true, failed = data.passed === false;
                const tone = passed ? 'pass' : (failed ? 'fail' : '');
                let head, msg;
                if (passed && pct >= 90) { head = 'Outstanding!'; msg = 'You nailed it. Keep up the great work!'; }
                else if (passed) { head = 'Nice work — you passed!'; msg = 'Solid effort. Review the ones you missed to aim even higher.'; }
                else if (failed && pass !== null && pct >= pass - 10) { head = 'So close!'; msg = `Just ${esc(fmt(Math.max(0, Math.ceil(pass - pct))))}% short of passing. A quick review will get you there.`; }
                else if (failed) { head = 'Keep going!'; msg = 'Every attempt teaches something. Go back to the module lessons and review.'; }
                else { head = 'All done!'; msg = 'Your quiz has been recorded.'; }
                const R = 52, C = 2 * Math.PI * R;
                const mark = pass !== null ? (() => {   // a small tick on the ring at the passing mark
                    const ang = (pass / 100) * 2 * Math.PI, x1 = 66 + (R - 9) * Math.cos(ang), y1 = 66 + (R - 9) * Math.sin(ang), x2 = 66 + (R + 9) * Math.cos(ang), y2 = 66 + (R + 9) * Math.sin(ang);
                    return `<line class="mark" x1="${x1.toFixed(1)}" y1="${y1.toFixed(1)}" x2="${x2.toFixed(1)}" y2="${y2.toFixed(1)}"/>`;
                })() : '';
                html = `<div class="qz-res qz-pop ${tone}" role="status" aria-live="polite">
                    <div class="qz-res-top">
                        <div class="qz-res-kicker">${esc(kicker)}</div>
                        <div class="qz-res-h">${esc(head)}</div>
                        <div class="qz-res-msg">${msg}</div>
                    </div>
                    <div class="qz-ring" aria-label="Score ${esc(fmt(data.percentage))} percent">
                        <svg viewBox="0 0 132 132" aria-hidden="true">
                            <defs><linearGradient id="qzGrad" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="var(--qr-a)"/><stop offset="1" stop-color="var(--qr-b)"/></linearGradient></defs>
                            <circle class="trk" cx="66" cy="66" r="${R}"/>
                            <circle class="val" id="qz-ring-val" cx="66" cy="66" r="${R}" stroke-dasharray="${C.toFixed(2)}" stroke-dashoffset="${C.toFixed(2)}"/>
                            ${mark}
                        </svg>
                        <div class="qz-ring-in"><div class="qz-ring-pct"><span id="qz-ring-num">0</span><small>%</small></div><div class="qz-ring-lbl">Your score</div></div>
                    </div>
                    <div class="qz-res-body">
                        <div class="qz-res-score">${esc(fmt(data.score))} <span>/ ${esc(fmt(data.max_score))} points</span></div>
                        ${passed ? `<div class="qz-res-badge">${ico.check} Passed</div>` : (failed ? `<div class="qz-res-badge">${ico.flag} Did not pass</div>` : '')}
                        <div class="qz-res-stats">
                            <div class="qz-res-stat"><b>${quizTotalQ ? `${answered}/${quizTotalQ}` : '—'}</b><span>Answered</span></div>
                            <div class="qz-res-stat"><b>${esc(took)}</b><span>Time taken</span></div>
                            <div class="qz-res-stat"><b>${pass !== null ? esc(fmt(pass)) + '%' : '—'}</b><span>To pass</span></div>
                        </div>
                    </div>
                    ${passed ? '<div class="qz-confetti" aria-hidden="true"></div>' : ''}
                </div>`;
                quizResultAnim = { pct, C };
            } else {
                html = `<div class="qz-res qz-pop" role="status" aria-live="polite">
                    <div class="qz-res-top">
                        <div class="qz-res-kicker">${esc(kicker)}</div>
                        <div class="qz-res-h">Answers sent!</div>
                        <div class="qz-res-msg">${data && data.pending_essays
                            ? `Your essay answer${data.pending_essays === 1 ? ' is' : 's are'} waiting for your teacher to grade. The other questions were checked automatically. Your final score shows up once your teacher grades the essay${data.pending_essays === 1 ? '' : 's'}.`
                            : 'Your teacher will check your answers. Your score shows up here once it is ready.'}</div>
                    </div>
                    <div class="qz-res-wait" aria-hidden="true">${ico.clock}</div>
                    <div class="qz-res-body">
                        <div class="qz-res-stats" style="grid-template-columns:repeat(${data && data.auto_points !== undefined ? 3 : 2},minmax(0,1fr))">
                            <div class="qz-res-stat"><b>${quizTotalQ ? `${answered}/${quizTotalQ}` : '—'}</b><span>Answered</span></div>
                            ${data && data.auto_points !== undefined ? `<div class="qz-res-stat"><b>${esc(fmt(data.auto_points))}/${esc(fmt(data.auto_points_max))}</b><span>Auto-checked pts</span></div>` : ''}
                            <div class="qz-res-stat"><b>${esc(took)}</b><span>Time taken</span></div>
                        </div>
                    </div>
                </div>`;
                quizResultAnim = null;
            }
            document.getElementById('quiz-modal-body').innerHTML = html;
            document.getElementById('quiz-modal-body').scrollTop = 0;
            document.getElementById('quiz-lock-note').style.display = 'none';
            document.getElementById('quiz-timer').innerText = '';
            document.getElementById('quiz-count').textContent = '';
            document.getElementById('quiz-saved').textContent = '';
            document.getElementById('quiz-next-btn').style.display = 'none';
            const sb = document.getElementById('quiz-submit-btn');
            sb.style.display = 'none'; sb.disabled = false; sb.innerText = 'Submit Quiz';
            const cb = document.getElementById('quiz-close-btn');
            cb.style.display = ''; cb.textContent = 'Close';
            setTimeout(() => { try { cb.focus(); } catch (e) { } }, 30);
            quizResultPlay();
        }
        // Ring fills up, the number counts up, and confetti when passed (skipped when "reduce motion" is on)
        let quizResultAnim = null;
        function quizResultPlay() {
            const a = quizResultAnim; quizResultAnim = null;
            if (!a) return;
            const ring = document.getElementById('qz-ring-val'), num = document.getElementById('qz-ring-num');
            const calm = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            const show = v => { if (num) num.textContent = String(Math.round(v)); };
            const target = a.C * (1 - a.pct / 100);
            if (calm || !window.requestAnimationFrame) { if (ring) ring.style.strokeDashoffset = target; show(a.pct); }
            else {
                requestAnimationFrame(() => requestAnimationFrame(() => { if (ring) ring.style.strokeDashoffset = target; }));
                const t0 = performance.now(), dur = 1200;
                const step = t => { const k = Math.min(1, (t - t0) / dur), e = 1 - Math.pow(1 - k, 3); show(a.pct * e); if (k < 1 && num && num.isConnected) requestAnimationFrame(step); };
                requestAnimationFrame(step);
            }
            const box = document.querySelector('#quiz-modal-body .qz-confetti');
            if (box && !calm) {
                const colors = ['#f59e0b', '#10b981', '#3b82f6', '#ec4899', '#8b5cf6', '#ef4444'];
                let h = '';
                for (let i = 0; i < 36; i++) {
                    const left = Math.random() * 100, dx = (Math.random() * 160 - 80).toFixed(0), rot = (Math.random() * 720 - 360).toFixed(0), delay = (Math.random() * 0.6).toFixed(2);
                    h += `<i style="left:${left.toFixed(1)}%;background:${colors[i % colors.length]};--dx:${dx}px;--rot:${rot}deg;animation-delay:${delay}s"></i>`;
                }
                box.innerHTML = h;
                setTimeout(() => { if (box.isConnected) box.remove(); }, 3600);
            }
        }

        function quizEnd() {
            quizActive = false;
            clearInterval(timerInterval); clearInterval(quizBeat); clearTimeout(quizSaveTimer);
            document.getElementById('quiz-next-btn').style.display = 'none';
            document.getElementById('quizModal').classList.add('hidden');
        }
        function quizAfterSubmit(data) {
            if (typeof notifyProgressChanged === 'function') notifyProgressChanged();
            if (typeof window.quizSubmittedHook === 'function') { window.quizSubmittedHook(data || {}); return; }
            if (window.ElmsLive && ElmsLive.refreshNow) ElmsLive.refreshNow(); else location.reload();
        }
        // Leaving the page while a quiz is open: ask first …
        window.addEventListener('beforeunload', e => {
            if (!quizActive) return;
            e.preventDefault();
            e.returnValue = 'Leaving submits your quiz with the answers you have so far.';
            return e.returnValue;
        });
        // … and if they leave anyway, submit it now with the answers given
        window.addEventListener('pagehide', () => {
            if (!quizActive || !currentQuizId) return;
            quizActive = false;
            const body = JSON.stringify({ attempt_id: currentQuizId, answers });
            // Remember it for the next page in this tab (refresh / back): that page makes sure it is submitted
            try { sessionStorage.setItem('elms_quiz_left', body); } catch (e) { }
            try {
                if (!navigator.sendBeacon || !navigator.sendBeacon('../api/student_quiz.php?action=submit', new Blob([body], { type: 'application/json' }))) {
                    fetch('../api/student_quiz.php?action=submit', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body, keepalive: true });
                }
            } catch (e) { /* the server submits it from the saved answers after a short while */ }
        });
        // Came back after leaving a quiz in this tab (refresh / back): make sure it is
        // submitted before showing anything, then show the result.
        (function () {
            let left = null;
            try { left = sessionStorage.getItem('elms_quiz_left'); sessionStorage.removeItem('elms_quiz_left'); } catch (e) { }
            if (!left) return;
            fetch('../api/student_quiz.php?action=submit', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: left })
                .then(r => r.json()).catch(() => ({}))
                .then(d => {
                    if (d && d.success) alert('You left the quiz, so it was submitted with the answers you gave.' + (d.score !== undefined && d.score !== null ? ` Score: ${d.score} / ${d.max_score} (${d.percentage}%).` : ''));
                    if (window.ElmsLive && ElmsLive.refreshNow) ElmsLive.refreshNow(); else location.reload();
                });
        })();
        // Switching away from the tab: save right away (phones may close a hidden tab)
        document.addEventListener('visibilitychange', () => { if (document.hidden && quizActive) quizSave(); });

        async function startQuiz(id, title, limit) {
            if (quizActive) return;   // one quiz at a time
            clearInterval(timerInterval);
            answers = {};             // answers from a quiz opened earlier must not be sent with this one
            quizResultData = null; quizResultAnim = null; quizStartedAt = 0;
            quizTotalQ = 0; quizQIds = []; quizIdx = 0;
            currentQuizRowId = id;
            document.getElementById('quiz-timer').innerText = '';
            document.getElementById('quiz-count').textContent = '';
            document.getElementById('quiz-saved').textContent = '';
            document.getElementById('quiz-lock-note').style.display = 'none';
            document.getElementById('quiz-close-btn').style.display = '';
            document.getElementById('quiz-submit-btn').style.display = 'none';
            document.getElementById('quiz-next-btn').style.display = 'none';
            document.getElementById('quizModal').classList.remove('hidden');
            document.getElementById('quiz-modal-title').innerText = title;
            document.getElementById('quiz-modal-body').innerHTML = 'Loading questions...';

            try {
                const res = await fetch(`../api/student_quiz.php?action=start&id=${id}`);
                const data = await res.json();

                if (!data.success) {
                    const p = document.createElement('p'); p.style.color = 'var(--danger, #c0392b)'; p.textContent = data.message || 'Could not open the quiz.';
                    const body = document.getElementById('quiz-modal-body'); body.innerHTML = ''; body.appendChild(p);
                    if (data.already) quizAfterSubmit();   // it was submitted when they left: show the result
                    return;
                }

                currentQuizId = data.data.attempt_id;
                const quiz = data.data;
                quizTotalQ = quiz.questions.length;
                const esc = v => { const d = document.createElement('div'); d.textContent = v == null ? '' : String(v); return d.innerHTML; };
                let html = '';

                quizQIds = quiz.questions.map(q => Number(q.id));
                quiz.questions.forEach((q, i) => {
                    const qid = Number(q.id);
                    html += `<div class="qz-q" data-i="${i}" data-q="${qid}"${i === 0 ? '' : ' hidden'}>
                        <div class="qz-step">Question ${i + 1} of ${quiz.questions.length}</div>
                        <div class="qz-qtext">${esc(q.question_text)}</div>`;
                    if (q.question_type === 'long_essay') {
                        html += `<div class="qz-essay-tag" style="display:inline-block;font-size:.7rem;font-weight:700;letter-spacing:.03em;color:#7a5a00;background:#fff8e1;border:1px solid #f3d27a;border-radius:999px;padding:.1rem .55rem;margin-bottom:.5rem">Essay · your teacher will grade this</div>
                                 <textarea id="q-${qid}" class="form-control" rows="8" maxlength="10000" placeholder="Write your answer here..." style="width:100%;min-height:180px;resize:vertical;line-height:1.5"
                                    oninput="quizAnswer(${qid}, { text: this.value })"></textarea>`;
                    } else if (q.question_type === 'essay') {
                        html += `<input type="text" id="q-${qid}" class="form-control" placeholder="Type one word or name..." autocomplete="off" maxlength="60"
                                    oninput="quizAnswer(${qid}, { text: this.value })" onkeydown="if (event.key === 'Enter') { event.preventDefault(); quizEnterKey(); }">`;
                    } else {
                        html += `<div style="display:flex; flex-direction:column; gap:0.5rem;" role="radiogroup" aria-label="Question ${i + 1}">`;
                        (q.choices || []).forEach(c => {
                            const cid = Number(c.id);
                            html += `<label class="qz-opt"><input type="radio" name="q_${qid}" value="${cid}" onchange="quizAnswer(${qid}, { choice_id: ${cid} })"> <span>${esc(c.choice_text)}</span></label>`;
                        });
                        html += `</div>`;
                    }
                    html += `<div class="qz-need" style="margin-top:.75rem;">${i === quiz.questions.length - 1 ? 'Last question — answer it, then press Submit Quiz.' : 'Answer this question, then press Next. You cannot go back.'}</div></div>`;
                });
                if (!quiz.questions.length) html = '<p>This quiz has no questions.</p>';

                document.getElementById('quiz-modal-body').innerHTML = html;
                // Locked in from here: no close button, leaving submits
                quizActive = true;
                document.getElementById('quiz-lock-note').style.display = '';
                document.getElementById('quiz-close-btn').style.display = 'none';
                const sb = document.getElementById('quiz-submit-btn');
                sb.disabled = false; sb.innerText = 'Submit Quiz';
                quizStartedAt = Date.now();
                quizShowQuestion(0);
                clearInterval(quizBeat);
                quizBeat = setInterval(quizSave, 20000);   // "still here" — an abandoned quiz is submitted by the server

                if (limit > 0) {
                    let time = limit * 60;
                    timerInterval = setInterval(() => {
                        time--;
                        const m = Math.floor(time / 60);
                        const s = time % 60;
                        document.getElementById('quiz-timer').innerText = `${m}:${s < 10 ? '0' + s : s}`;
                        if (time <= 0) { clearInterval(timerInterval); submitQuiz(true); }
                    }, 1000);
                }
            } catch (e) {
                document.getElementById('quiz-modal-body').innerHTML = 'Error loading quiz.';
            }
        }

        // Enter in a typed answer: Next (or Submit on the last question) once it is answered
        function quizEnterKey() {
            if (!quizActive || !quizIsAnswered(quizQIds[quizIdx])) return;
            if (quizIdx < quizTotalQ - 1) quizNext(); else submitQuiz();
        }

        async function submitQuiz(timeUp) {
            if (!quizActive) return;
            const sbtn = document.getElementById('quiz-submit-btn');
            if (sbtn && sbtn.innerText === 'Submitting...') return;   // no double submit
            // Submit is only on the last question, once it is answered (the timer may submit any time)
            if (!timeUp && quizTotalQ && (quizIdx < quizTotalQ - 1 || !quizIsAnswered(quizQIds[quizIdx]))) { quizShowCount(); return; }
            const unanswered = quizTotalQ - Object.keys(answers).filter(k => { const a = answers[k]; return a && ((a.choice_id) || (a.text && a.text.trim() !== '')); }).length;
            if (!timeUp && unanswered > 0 && !confirm(`You have ${unanswered} unanswered question${unanswered === 1 ? '' : 's'}. They will count as wrong. Submit now?`)) return;
            clearInterval(timerInterval);
            const btn = document.getElementById('quiz-submit-btn');
            btn.disabled = true; btn.innerText = 'Submitting...';

            try {
                const res = await fetch('../api/student_quiz.php?action=submit', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ attempt_id: currentQuizId, answers })
                });
                const data = await res.json();

                if (data.success) {
                    const hasScore = data.score !== undefined && data.score !== null;
                    const scorePct = hasScore && Number(data.max_score) > 0 ? Math.round(Number(data.score) / Number(data.max_score) * 100) : null;
                    quizShowResult(data, !!timeUp);   // the score is shown inside the quiz window

                    // quiz rows share the Assignments page with assignment rows: match the quiz one
                    const row = document.querySelector(`.acc-row[data-kind="quiz"][data-id="${currentQuizRowId}"]`) || document.querySelector(`.acc-row:not([data-kind])[data-id="${currentQuizRowId}"]`);
                    if (row) {
                        const iconWrap = row.querySelector('.quiz-icon-wrap');
                        if (iconWrap) {
                            iconWrap.innerHTML = '<svg class="icon icon-sm" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"></polyline></svg>';
                            iconWrap.style.background = '#d1fae5';
                            iconWrap.style.color = '#059669';
                        }
                        const badge = row.querySelector('.quiz-score');
                        if (badge) {
                            if (hasScore && scorePct !== null) { badge.className = 'badge ' + (scorePct >= 75 ? 'badge-green' : 'badge-yellow'); badge.innerText = scorePct + '%'; }
                            else { badge.className = 'badge badge-gray'; badge.innerText = 'Pending review'; }
                        }
                        const actions = row.querySelector('.quiz-actions');
                        if (actions) actions.innerHTML = '<button class="btn btn-outline btn-sm" disabled>Completed</button>';
                    }
                    // quizAfterSubmit(data) runs when the student closes the result (closeQuizModal)
                } else if (/already submitted/i.test(data.message || '')) {
                    quizEnd(); quizAfterSubmit();
                } else {
                    alert('Error: ' + (data.message || 'Could not submit'));
                    btn.disabled = false; btn.innerText = 'Submit Quiz'; quizShowCount();
                }
            } catch (e) {
                alert('Connection error. Your answers are saved — try Submit again.');
                btn.disabled = false; btn.innerText = 'Submit Quiz'; quizShowCount();
            }
        }

        // Only for a quiz that never started (error / already taken). A started quiz cannot be closed.
        function closeQuizModal() {
            if (quizActive) return;
            clearInterval(timerInterval);
            document.getElementById('quizModal').classList.add('hidden');
            if (quizResultData) {               // a result was on screen: now refresh the page / open the review
                const d = quizResultData; quizResultData = null;
                quizAfterSubmit(d);
            }
        }

        // --- FILE UPLOAD LOGIC (No Refresh) ---

        function openSubmitModal(id, title) {
            currentQuizId = id;
            document.getElementById('submit-quiz-name').innerText = title;
            document.getElementById('submit-file').value = '';
            document.getElementById('submitModal').classList.remove('hidden');
        }

        async function submitQuizFile() {
            const fileInput = document.getElementById('submit-file');
            if (fileInput.files.length === 0) return alert('Please select a file');
            { const bad = ElmsUpload.check(fileInput.files[0]); if (bad) return alert(bad); }

            const fd = new FormData();
            fd.append('quiz_id', currentQuizId);
            fd.append('file', fileInput.files[0]);

            const btn = document.querySelector('#submitModal .btn-primary');
            const originalText = btn.innerText;
            btn.disabled = true; btn.innerText = 'Uploading...';

            try {
                const res = await fetch('../api/student_quiz.php?action=submit_file', { method: 'POST', body: fd });
                const data = await res.json();

                if (data.success) {
                    document.getElementById('submitModal').classList.add('hidden');
                    notifyProgressChanged();

                    const row = document.querySelector(`.acc-row[data-id="${currentQuizId}"]`);
                    if (row) {
                        const badge = row.querySelector('.quiz-score');
                        badge.className = 'badge badge-yellow';
                        badge.innerText = 'Submitted';
                    }
                    alert('File uploaded successfully!');
                    if (typeof window.quizSubmittedHook === 'function') { window.quizSubmittedHook(data); return; }
                    if (window.ElmsLive && ElmsLive.refreshNow) ElmsLive.refreshNow();
                } else {
                    alert(data.message);
                }
            } catch (e) {
                alert('Upload failed.');
            } finally {
                btn.disabled = false; btn.innerText = originalText;
            }
        }

        // GLOBAL CLICK HANDLER (Updated to handle both dropdowns)
        window.onclick = function (e) {
            const nd = document.getElementById('notifDropdown');
            const ud = document.getElementById('userDropdown');

            const btnNotif = e.target.closest('.btn-icon');
            const isInsideNotif = e.target.closest('#notifDropdown');
            const isInsideUser = e.target.closest('#userDropdown');

            // If we didn't click inside a dropdown, and didn't click the bell/user button...
            if (!isInsideNotif && !isInsideUser && !btnNotif) {
                // Close both if they are open
                if (nd.style.display === 'block') nd.style.display = 'none';
                if (ud.style.display === 'block') ud.style.display = 'none';
            }
        }
    </script>
    <?php require __DIR__ . '/footer_common.php'; ?>
</body>

    </html><?php endif; ?>