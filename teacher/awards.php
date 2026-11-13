<?php
// ============================================================
//  Arandia College eLMS — Teacher: Awards
//  File: teacher/awards.php
//
//  Give awards to students in the teacher's own subjects, and view /
//  edit / revoke the awards this teacher has given. The lists on this
//  page are only a convenience — every action is re-authorized on the
//  server in api/awards.php.
// ============================================================
session_start();
require_once '../config/conn.php';
require_once '../includes/awards.php';

require_once __DIR__ . '/../includes/role_guard.php';
requireTeacherPage();

$first_name = $_SESSION['first_name'];
$last_name = $_SESSION['last_name'];
$user_id = (int) $_SESSION['user_id'];

$uInfo = awards_select($conn, "SELECT section_dept FROM users WHERE id = ? LIMIT 1", 'i', [$user_id]);
$dept = $uInfo[0]['section_dept'] ?? '';

// ── Values the shared Teacher header/topbar/footer expect ─────────────
$mySubjects = awards_select(
    $conn,
    "SELECT ta.id AS assign_id, ta.section, ta.school_year, ta.semester,
            c.id AS course_id, c.course_code, c.course_name, c.description
     FROM teacher_assignments ta
     JOIN courses c ON c.id = ta.course_id
     WHERE ta.teacher_id = ?
     ORDER BY ta.school_year DESC, c.course_code, ta.section",
    'i',
    [$user_id]
);
$courseIdList = array_values(array_unique(array_map('intval', array_column($mySubjects, 'course_id'))));
$inCourses = $courseIdList ? implode(',', $courseIdList) : '';   // integers only

$myAnnouncements = awards_select(
    $conn,
    "SELECT a.id, a.title, a.course_id, a.posted_at AS posted_at_raw,
            DATE_FORMAT(a.posted_at,'%b %d, %Y %h:%i %p') AS posted_at,
            CONCAT(u.first_name,' ',u.last_name) AS author, c.course_code
     FROM announcements a
     LEFT JOIN courses c ON c.id = a.course_id
     LEFT JOIN users u ON u.id = a.author_id
     WHERE a.author_id = $user_id OR (u.role IN ('Admin','Head Admin') AND a.target_audience IN ('Everyone','Teachers'))
     ORDER BY a.posted_at DESC LIMIT 20"
);
$pendingSubmissions = $inCourses === '' ? [] : awards_select(
    $conn,
    "SELECT s.id AS submission_id, s.submitted_at, a.id AS assignment_id, a.title,
            u.first_name, u.last_name, c.course_code
     FROM submissions s
     JOIN assignments a ON a.id = s.assignment_id
     JOIN users u ON u.id = s.student_id
     JOIN courses c ON c.id = a.course_id
     WHERE a.course_id IN ($inCourses) AND s.score IS NULL
       AND EXISTS (SELECT 1 FROM teacher_assignments ta WHERE ta.teacher_id = $user_id AND ta.course_id = a.course_id AND ta.section = u.section_dept)
     ORDER BY s.submitted_at DESC LIMIT 20"
);

// ── Awards data ─────────────────────────────────────────────────────────
$awardsReady = awards_tables_ready($conn);

// Students this teacher may award, grouped per student with their shared subjects.
$roster = $awardsReady ? awards_teacher_roster($conn, $user_id) : [];
$studentsJS = [];
$coursesJS = [];
foreach ($roster as $r) {
    $sid = (int) $r['student_id'];
    $cid = (int) $r['course_id'];
    if (!isset($studentsJS[$sid])) {
        $studentsJS[$sid] = [
            'id' => $sid,
            'name' => $r['last_name'] . ', ' . $r['first_name'],
            'school_id' => $r['school_id'],
            'section' => $r['section_dept'] ?: '—',
            'courses' => [],
        ];
    }
    $studentsJS[$sid]['courses'][] = $cid;
    $coursesJS[$cid] = $r['course_name'] . ' (' . $r['course_code'] . ')';
}

$typeRows = $awardsReady ? awards_select($conn, "SELECT id, name, description, category, icon, tone, status FROM award_types ORDER BY name") : [];
$typesJS = [];
$activeTypeCount = 0;
foreach ($typeRows as $t) {
    if ($t['status'] === 'Active') $activeTypeCount++;
    $typesJS[(int) $t['id']] = [
        'id' => (int) $t['id'],
        'name' => $t['name'],
        'description' => $t['description'] ?? '',
        'category' => $t['category'],
        'active' => $t['status'] === 'Active',
        'emblem' => awards_emblem($t['icon'], $t['tone'], 'md'),
    ];
}

$myAwards = $awardsReady ? awards_select(
    $conn,
    "SELECT sa.id, sa.student_id, sa.course_id, sa.award_type_id, sa.reason, sa.status,
            sa.awarded_at, sa.revoked_at, sa.school_year,
            u.first_name, u.last_name, u.school_id, u.section_dept,
            c.course_code, c.course_name,
            t.name AS award_name, t.category, t.icon, t.tone
     FROM student_awards sa
     JOIN users u ON u.id = sa.student_id
     JOIN courses c ON c.id = sa.course_id
     JOIN award_types t ON t.id = sa.award_type_id
     WHERE sa.teacher_id = ?
     ORDER BY sa.awarded_at DESC, sa.id DESC",
    'i',
    [$user_id]
) : [];

$awardsJS = [];
$statActive = 0; $statRevoked = 0; $statMonth = 0; $recognized = [];
$monthStart = date('Y-m-01 00:00:00');
foreach ($myAwards as $a) {
    if ($a['status'] === 'Active') {
        $statActive++;
        $recognized[(int) $a['student_id']] = true;
        if ($a['awarded_at'] >= $monthStart) $statMonth++;
    } else {
        $statRevoked++;
    }
    $awardsJS[(int) $a['id']] = [
        'id' => (int) $a['id'],
        'student' => $a['last_name'] . ', ' . $a['first_name'],
        'school_id' => $a['school_id'],
        'section' => $a['section_dept'] ?: '—',
        'subject' => $a['course_name'] . ' (' . $a['course_code'] . ')',
        'award_type_id' => (int) $a['award_type_id'],
        'award' => $a['award_name'],
        'category' => $a['category'],
        'reason' => $a['reason'],
        'status' => $a['status'],
        'date' => awards_format_date($a['awarded_at'], 'F j, Y g:i A'),
        'revoked' => $a['revoked_at'] ? awards_format_date($a['revoked_at'], 'F j, Y g:i A') : null,
        'emblem' => awards_emblem($a['icon'], $a['tone'], 'md'),
    ];
}
$filterSubjects = [];
foreach ($myAwards as $a) $filterSubjects[(int) $a['course_id']] = $a['course_name'] . ' (' . $a['course_code'] . ')';
asort($filterSubjects);

$csrf = awards_csrf_token();
$jsonFlags = JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT;

$currentPage = 'awards.php';
$pageTitle = 'Awards';
require __DIR__ . '/../includes/header.php';
?>

<?= awards_emblem_css() ?>
<style>
  .awt-head { display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem; flex-wrap: wrap; margin-bottom: 1.25rem; }
  .awt-head .page-sub { margin-bottom: 0; }
  .awt-panel { background: var(--white); border-radius: 16px; box-shadow: 0 2px 16px rgba(0,0,0,.07); overflow: hidden; }
  .awt-panel-head { padding: 1rem 1.5rem; border-bottom: 1px solid rgba(15,23,42,.06); display: flex; align-items: center; justify-content: space-between; gap: .75rem; flex-wrap: wrap; }
  .awt-panel-head h3 { font-family: 'Nunito', sans-serif; font-size: .95rem; font-weight: 800; color: var(--text-dark); }
  .awt-filters { display: flex; gap: .5rem; flex-wrap: wrap; }
  .awt-filters .search-input, .awt-filters select { min-width: 0; background: var(--white); color: var(--text-dark); }
  .awt-table { width: 100%; border-collapse: collapse; }
  .awt-table th { text-align: left; padding: .6rem 1rem; font-size: .66rem; font-weight: 800; letter-spacing: .06em; text-transform: uppercase; color: #94a3b8; background: rgba(15,23,42,.025); border-bottom: 1px solid rgba(15,23,42,.06); white-space: nowrap; }
  .awt-table td { padding: .8rem 1rem; font-size: .82rem; color: var(--text-dark); border-bottom: 1px solid rgba(15,23,42,.05); vertical-align: middle; }
  .awt-table tr:last-child td { border-bottom: none; }
  .awt-table tr.is-revoked td { color: var(--text-light); }
  .awt-student strong { display: block; font-weight: 700; }
  .awt-student span, .awt-muted { font-size: .72rem; color: var(--text-light); }
  .awt-award { display: flex; align-items: center; gap: .6rem; min-width: 0; }
  .awt-award .aw-emblem { margin-top: -.8rem; }
  .awt-award strong { font-weight: 700; overflow-wrap: anywhere; }
  .awt-actions { display: flex; gap: .3rem; justify-content: flex-end; flex-wrap: nowrap; }
  .awt-empty { padding: 2.5rem 1.5rem; text-align: center; color: var(--text-light); font-size: .88rem; }
  .awt-empty strong { display: block; color: var(--text-dark); font-size: .95rem; margin-bottom: .25rem; }
  .awt-notice { background: #fffbe6; border: 1px solid #f5e3a3; color: #7a5c00; border-radius: 12px; padding: 1rem 1.25rem; font-size: .85rem; margin-bottom: 1.25rem; }

  /* Give Award modal */
  .awt-steps { display: flex; gap: .5rem; font-size: .75rem; font-weight: 700; color: var(--ui-text-3, #94a3b8); }
  .awt-steps span.on { color: var(--primary); }
  .awt-type-preview { display: flex; gap: .85rem; align-items: flex-start; background: var(--ui-surface-2, #f8fafc); border: 1px solid var(--ui-border, #e2e8f0); border-radius: 10px; padding: 0 1rem .85rem; min-height: 64px; }
  .awt-type-preview .awt-tp-text { padding-top: .75rem; font-size: .8rem; color: var(--ui-text-2, #475569); line-height: 1.5; }
  .awt-type-preview strong { display: block; color: var(--ui-text, #0f172a); font-size: .88rem; }
  .awt-review { border: 1px solid var(--ui-border, #e2e8f0); border-radius: 12px; overflow: hidden; }
  .awt-review-top { display: flex; gap: 1rem; align-items: flex-start; padding: 0 1.1rem .9rem; border-bottom: 1px solid #eef2f7; }
  .awt-review-top h4 { font-family: 'Nunito', sans-serif; font-weight: 900; font-size: 1.05rem; color: var(--ui-text, #0f172a); padding-top: .8rem; }
  .awt-review-top > div > span { font-size: .75rem; color: var(--ui-text-2, #64748b); }
  .awt-review dl { display: grid; grid-template-columns: 130px 1fr; gap: .55rem 1rem; padding: .9rem 1.1rem; font-size: .83rem; }
  .awt-review dt { color: var(--ui-text-2, #64748b); font-weight: 600; }
  .awt-review dd { color: var(--ui-text, #0f172a); overflow-wrap: anywhere; white-space: pre-line; }
  .awt-count { font-size: .7rem; color: var(--ui-text-3, #94a3b8); text-align: right; }
  .awt-hint { font-size: .72rem; color: var(--ui-text-3, #94a3b8); }
  .awt-status { display: inline-block; font-size: .66rem; font-weight: 800; padding: .18rem .6rem; border-radius: 100px; }
  .awt-status.active { background: #e3f5ec; color: #0b7a52; }
  .awt-status.revoked { background: var(--ui-surface-3, #f1f5f9); color: var(--ui-text-2, #64748b); }
  html[data-theme="dark"] .awt-type-preview, html[data-theme="dark"] .awt-review { background: transparent; border-color: rgba(255,255,255,.1); }

  @media (max-width: 760px) {
    .awt-table thead { display: none; }
    .awt-table, .awt-table tbody, .awt-table tr, .awt-table td { display: block; width: 100%; }
    .awt-table tr { padding: .85rem 1.1rem; border-bottom: 1px solid rgba(15,23,42,.06); }
    .awt-table td { border: none; padding: .25rem 0; display: flex; justify-content: space-between; gap: 1rem; text-align: right; }
    .awt-table td::before { content: attr(data-label); font-size: .7rem; font-weight: 700; color: var(--ui-text-3, #94a3b8); text-align: left; flex-shrink: 0; }
    .awt-table td.awt-cell-award { display: block; text-align: left; padding-bottom: .4rem; }
    .awt-table td.awt-cell-award::before { display: none; }
    .awt-actions { justify-content: flex-end; }
    .awt-review dl { grid-template-columns: minmax(0, 1fr); gap: .1rem; }
    .awt-review dd { margin-bottom: .45rem; }
    .awt-filters { width: 100%; }
    .awt-filters > * { flex: 1 1 140px; }
  }
</style>

<div class="section-panel active">
  <div class="awt-head">
    <div class="panel-header" style="margin-bottom:0">
      <div class="page-title">Awards</div>
    </div>
    <?php if ($awardsReady): ?>
      <button type="button" class="btn btn-primary" onclick="openGiveAward()" <?= empty($studentsJS) || $activeTypeCount === 0 ? 'disabled title="No eligible students or active award types"' : '' ?>>
        <?= t_icon('award') ?> Give Award
      </button>
    <?php endif; ?>
  </div>

  <?php if (!$awardsReady): ?>
    <div class="awt-notice">The awards feature is not set up yet. Please ask the administrator to import <strong>database/migration_awards.sql</strong>.</div>
  <?php else: ?>
    <?php if (empty($studentsJS)): ?>
      <div class="awt-notice">You can give awards to students enrolled in your assigned subjects. No enrolled students were found for your subjects yet.</div>
    <?php elseif ($activeTypeCount === 0): ?>
      <div class="awt-notice">There are no active award types yet. Please ask the administrator to add award types in Award Management.</div>
    <?php endif; ?>

    <div class="quick-stats">
      <div class="stat-card stat-card-1"><div class="stat-icon"><?= t_icon('award') ?></div><div class="stat-info"><div class="stat-num"><?= $statActive ?></div><div class="stat-label">Active Awards Given</div></div></div>
      <div class="stat-card stat-card-2"><div class="stat-icon"><?= t_icon('users') ?></div><div class="stat-info"><div class="stat-num"><?= count($recognized) ?></div><div class="stat-label">Students Recognized</div></div></div>
      <div class="stat-card stat-card-3"><div class="stat-icon"><?= t_icon('clock') ?></div><div class="stat-info"><div class="stat-num"><?= $statMonth ?></div><div class="stat-label">Given This Month</div></div></div>
      <div class="stat-card stat-card-4"><div class="stat-icon"><?= t_icon('x') ?></div><div class="stat-info"><div class="stat-num"><?= $statRevoked ?></div><div class="stat-label">Revoked</div></div></div>
    </div>

    <div class="awt-panel">
      <div class="awt-panel-head">
        <h3>Awards You Have Given</h3>
        <?php if (!empty($myAwards)): ?>
        <div class="awt-filters">
          <input class="search-input" id="awt-search" type="search" placeholder="Search student or award" oninput="filterAwardRows()" aria-label="Search awards">
          <select class="search-input" id="awt-filter-subject" onchange="filterAwardRows()" aria-label="Filter by subject">
            <option value="">All subjects</option>
            <?php foreach ($filterSubjects as $cid => $label): ?>
              <option value="<?= (int) $cid ?>"><?= htmlspecialchars($label) ?></option>
            <?php endforeach; ?>
          </select>
          <select class="search-input" id="awt-filter-status" onchange="filterAwardRows()" aria-label="Filter by status">
            <option value="">All statuses</option>
            <option value="Active">Active</option>
            <option value="Revoked">Revoked</option>
          </select>
        </div>
        <?php endif; ?>
      </div>

      <?php if (empty($myAwards)): ?>
        <div class="awt-empty"><strong>No awards have been given yet.</strong>Use the Give Award button to recognize a student.</div>
      <?php else: ?>
        <table class="awt-table">
          <thead>
            <tr><th>Award</th><th>Student Name</th><th>Student ID</th><th>Subject</th><th>Date Awarded</th><th>Status</th><th style="text-align:right">Actions</th></tr>
          </thead>
          <tbody id="awt-tbody">
            <?php foreach ($myAwards as $a):
              $id = (int) $a['id'];
              $search = strtolower($a['first_name'] . ' ' . $a['last_name'] . ' ' . $a['school_id'] . ' ' . $a['award_name']);
            ?>
            <tr data-id="<?= $id ?>" data-course="<?= (int) $a['course_id'] ?>" data-status="<?= htmlspecialchars($a['status']) ?>" data-search="<?= htmlspecialchars($search) ?>" class="<?= $a['status'] === 'Revoked' ? 'is-revoked' : '' ?>">
              <td class="awt-cell-award" data-label="Award"><div class="awt-award"><?= awards_emblem($a['icon'], $a['tone'], 'sm') ?><div><strong><?= htmlspecialchars($a['award_name']) ?></strong><div class="awt-muted"><?= htmlspecialchars($a['category']) ?></div></div></div></td>
              <td data-label="Student"><div class="awt-student"><strong><?= htmlspecialchars($a['last_name'] . ', ' . $a['first_name']) ?></strong><span><?= htmlspecialchars($a['section_dept'] ?: '—') ?></span></div></td>
              <td data-label="Student ID"><?= htmlspecialchars($a['school_id']) ?></td>
              <td data-label="Subject"><div><?= htmlspecialchars($a['course_name']) ?><div class="awt-muted"><?= htmlspecialchars($a['course_code']) ?></div></div></td>
              <td data-label="Date Awarded"><?= htmlspecialchars(awards_format_date($a['awarded_at'], 'M j, Y')) ?></td>
              <td data-label="Status"><span class="awt-status <?= $a['status'] === 'Active' ? 'active' : 'revoked' ?>"><?= htmlspecialchars($a['status']) ?></span></td>
              <td data-label="Actions">
                <div class="awt-actions">
                  <button type="button" class="btn btn-blue btn-sm" onclick="viewAward(<?= $id ?>)">View</button>
                  <?php if ($a['status'] === 'Active'): ?>
                    <button type="button" class="btn btn-gray btn-sm" onclick="openEditAward(<?= $id ?>)">Edit</button>
                    <button type="button" class="btn btn-red btn-sm" onclick="openRevokeAward(<?= $id ?>)">Revoke</button>
                  <?php endif; ?>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
        <div class="awt-empty" id="awt-no-match" style="display:none"><strong>No awards match your filters.</strong>Try a different search, subject, or status.</div>
      <?php endif; ?>
    </div>
  <?php endif; ?>
</div>

<?php if ($awardsReady): ?>
<!-- GIVE AWARD MODAL -->
<div class="modal-overlay" id="giveAwardModal" role="dialog" aria-modal="true" aria-labelledby="give-title">
  <div class="modal" style="max-width:600px">
    <div class="modal-head">
      <div>
        <h2 id="give-title">Give Award</h2>
        <div class="awt-steps"><span id="give-step-1-label" class="on">1. Details</span><span>/</span><span id="give-step-2-label">2. Review</span></div>
      </div>
      <button class="modal-close" type="button" aria-label="Close" onclick="closeModal('giveAwardModal')"><?= t_icon('x') ?></button>
    </div>

    <div id="give-step-1">
      <div class="modal-body">
        <div class="modal-err" id="give-err" role="alert"></div>
        <div class="form-grp">
          <label class="form-lbl" for="give-student">Student *</label>
          <select class="finput" id="give-student" onchange="onGiveStudentChange()">
            <option value="">— Select student —</option>
            <?php foreach ($studentsJS as $s): ?>
              <option value="<?= (int) $s['id'] ?>"><?= htmlspecialchars($s['name'] . ' — ' . $s['school_id'] . ' (' . $s['section'] . ')') ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-grp">
          <label class="form-lbl" for="give-course">Subject *</label>
          <select class="finput" id="give-course" disabled>
            <option value="">— Select a student first —</option>
          </select>
          <span class="awt-hint">Only subjects you teach that this student is enrolled in are listed.</span>
        </div>
        <div class="form-grp">
          <label class="form-lbl" for="give-type">Award *</label>
          <select class="finput" id="give-type" onchange="renderTypePreview('give-type','give-type-preview')">
            <option value="">— Select award —</option>
            <?php foreach ($typesJS as $t): if (!$t['active']) continue; ?>
              <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['name'] . ' — ' . $t['category']) ?></option>
            <?php endforeach; ?>
          </select>
          <div class="awt-type-preview" id="give-type-preview" style="display:none"></div>
        </div>
        <div class="form-grp">
          <label class="form-lbl" for="give-reason">Reason / Description *</label>
          <textarea class="finput" id="give-reason" rows="4" maxlength="500" placeholder="e.g. Scored the highest in the Java Programming midterm project and helped classmates debug their code." oninput="updateCount('give-reason','give-reason-count')"></textarea>
          <div class="awt-count" id="give-reason-count">0 / 500</div>
        </div>
        <div class="awt-hint">The date is recorded automatically when you give the award.</div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-gray" onclick="closeModal('giveAwardModal')">Cancel</button>
        <button type="button" class="btn btn-primary" onclick="reviewAward()">Review</button>
      </div>
    </div>

    <div id="give-step-2" style="display:none">
      <div class="modal-body">
        <div class="modal-err" id="give-err-2" role="alert"></div>
        <div class="awt-review" id="give-review"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-gray" id="give-back-btn" onclick="giveStep(1)">Back</button>
        <button type="button" class="btn btn-primary" id="give-submit-btn" onclick="submitAward(this)"><?= t_icon('award') ?> Give Award</button>
      </div>
    </div>
  </div>
</div>

<!-- VIEW AWARD MODAL -->
<div class="modal-overlay" id="viewAwardModal" role="dialog" aria-modal="true" aria-labelledby="view-title">
  <div class="modal" style="max-width:560px">
    <div class="modal-head">
      <h2 id="view-title">Award Details</h2>
      <button class="modal-close" type="button" aria-label="Close" onclick="closeModal('viewAwardModal')"><?= t_icon('x') ?></button>
    </div>
    <div class="modal-body"><div class="awt-review" id="view-body"></div></div>
    <div class="modal-footer"><button type="button" class="btn btn-gray" onclick="closeModal('viewAwardModal')">Close</button></div>
  </div>
</div>

<!-- EDIT AWARD MODAL -->
<div class="modal-overlay" id="editAwardModal" role="dialog" aria-modal="true" aria-labelledby="edit-title">
  <div class="modal" style="max-width:560px">
    <div class="modal-head">
      <h2 id="edit-title">Edit Award</h2>
      <button class="modal-close" type="button" aria-label="Close" onclick="closeModal('editAwardModal')"><?= t_icon('x') ?></button>
    </div>
    <div class="modal-body">
      <div class="modal-err" id="edit-err" role="alert"></div>
      <input type="hidden" id="edit-id">
      <div class="awt-hint" id="edit-who"></div>
      <div class="form-grp">
        <label class="form-lbl" for="edit-type">Award *</label>
        <select class="finput" id="edit-type" onchange="renderTypePreview('edit-type','edit-type-preview')"></select>
        <div class="awt-type-preview" id="edit-type-preview" style="display:none"></div>
      </div>
      <div class="form-grp">
        <label class="form-lbl" for="edit-reason">Reason / Description *</label>
        <textarea class="finput" id="edit-reason" rows="4" maxlength="500" oninput="updateCount('edit-reason','edit-reason-count')"></textarea>
        <div class="awt-count" id="edit-reason-count">0 / 500</div>
      </div>
    </div>
    <div class="modal-footer">
      <button type="button" class="btn btn-gray" onclick="closeModal('editAwardModal')">Cancel</button>
      <button type="button" class="btn btn-primary" onclick="submitEditAward(this)">Save Changes</button>
    </div>
  </div>
</div>

<!-- REVOKE CONFIRM MODAL -->
<div class="modal-overlay" id="revokeAwardModal" role="dialog" aria-modal="true" aria-labelledby="revoke-title">
  <div class="modal" style="max-width:440px">
    <div class="modal-head">
      <h2 id="revoke-title">Revoke Award</h2>
      <button class="modal-close" type="button" aria-label="Close" onclick="closeModal('revokeAwardModal')"><?= t_icon('x') ?></button>
    </div>
    <div class="modal-body">
      <div class="modal-err" id="revoke-err" role="alert"></div>
      <input type="hidden" id="revoke-id">
      <p style="font-size:.88rem;line-height:1.55;color:var(--text-dark)" id="revoke-text"></p>
      <p class="awt-hint">The record is kept for your history, marked as Revoked.</p>
    </div>
    <div class="modal-footer">
      <button type="button" class="btn btn-gray" onclick="closeModal('revokeAwardModal')">Cancel</button>
      <button type="button" class="btn btn-red" onclick="submitRevokeAward(this)">Revoke Award</button>
    </div>
  </div>
</div>

<script>
  const AW_CSRF = <?= json_encode($csrf) ?>;
  const AW_STUDENTS = <?= json_encode((object) $studentsJS, $jsonFlags) ?>;
  const AW_COURSES = <?= json_encode((object) $coursesJS, $jsonFlags) ?>;
  const AW_TYPES = <?= json_encode((object) $typesJS, $jsonFlags) ?>;
  const AW_AWARDS = <?= json_encode((object) $awardsJS, $jsonFlags) ?>;
  let awGiveToken = null;

  function awEsc(s) { return String(s ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#39;'); }
  function awNewToken() {
    if (window.crypto && crypto.randomUUID) return crypto.randomUUID();
    const b = new Uint8Array(16);
    (window.crypto || window.msCrypto).getRandomValues(b);
    return Array.from(b, x => x.toString(16).padStart(2, '0')).join('');
  }
  function awShowErr(id, msg) { const el = document.getElementById(id); if (!el) return; el.textContent = msg; el.style.display = msg ? 'block' : 'none'; }
  function awBusy(btn, on, text) {
    if (!btn) return;
    if (on) { btn.dataset.html = btn.innerHTML; btn.disabled = true; btn.textContent = text || 'Saving...'; }
    else { btn.disabled = false; if (btn.dataset.html) btn.innerHTML = btn.dataset.html; }
  }
  async function awPost(action, payload) {
    const res = await fetch('../api/awards.php?action=' + encodeURIComponent(action), {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': AW_CSRF },
      body: JSON.stringify(payload),
      credentials: 'same-origin'
    });
    let data = null;
    try { data = await res.json(); } catch (e) { data = null; }
    if (!data) return { success: false, message: 'Unable to save the award. Please try again.' };
    return data;
  }
  function updateCount(inputId, countId) {
    const v = document.getElementById(inputId).value;
    document.getElementById(countId).textContent = v.length + ' / 500';
  }
  function renderTypePreview(selectId, previewId) {
    const t = AW_TYPES[document.getElementById(selectId).value];
    const box = document.getElementById(previewId);
    if (!t) { box.style.display = 'none'; box.innerHTML = ''; return; }
    box.innerHTML = t.emblem + '<div class="awt-tp-text"><strong>' + awEsc(t.name) + '</strong>' + awEsc(t.category) + (t.description ? ' — ' + awEsc(t.description) : '') + '</div>';
    box.style.display = 'flex';
  }

  /* ── Give award ─────────────────────────────── */
  function openGiveAward() {
    awGiveToken = awNewToken();
    ['give-student', 'give-type', 'give-reason'].forEach(id => document.getElementById(id).value = '');
    onGiveStudentChange();
    renderTypePreview('give-type', 'give-type-preview');
    updateCount('give-reason', 'give-reason-count');
    awShowErr('give-err', ''); awShowErr('give-err-2', '');
    const sb = document.getElementById('give-submit-btn'); sb.disabled = false;
    giveStep(1);
    openModal('giveAwardModal');
    setTimeout(() => document.getElementById('give-student').focus(), 50);
  }
  function giveStep(n) {
    document.getElementById('give-step-1').style.display = n === 1 ? '' : 'none';
    document.getElementById('give-step-2').style.display = n === 2 ? '' : 'none';
    document.getElementById('give-step-1-label').classList.toggle('on', n === 1);
    document.getElementById('give-step-2-label').classList.toggle('on', n === 2);
  }
  function onGiveStudentChange() {
    const s = AW_STUDENTS[document.getElementById('give-student').value];
    const sel = document.getElementById('give-course');
    sel.innerHTML = '';
    if (!s) { sel.innerHTML = '<option value="">— Select a student first —</option>'; sel.disabled = true; return; }
    const ids = [...new Set(s.courses)];
    sel.innerHTML = '<option value="">— Select subject —</option>' + ids.map(id => '<option value="' + id + '">' + awEsc(AW_COURSES[id] || '') + '</option>').join('');
    sel.disabled = false;
    if (ids.length === 1) sel.value = String(ids[0]);
  }
  function reviewAward() {
    awShowErr('give-err', '');
    const sid = document.getElementById('give-student').value;
    const cid = document.getElementById('give-course').value;
    const tid = document.getElementById('give-type').value;
    const reason = document.getElementById('give-reason').value.trim();
    const s = AW_STUDENTS[sid], t = AW_TYPES[tid];
    if (!s) return awShowErr('give-err', 'Please select a student.');
    if (!cid || !s.courses.map(String).includes(cid)) return awShowErr('give-err', 'Please select a subject.');
    if (!t || !t.active) return awShowErr('give-err', 'Invalid award selected.');
    if (reason.length < 5) return awShowErr('give-err', 'Please enter a reason for this award (at least 5 characters).');
    const today = new Date().toLocaleDateString(undefined, { year: 'numeric', month: 'long', day: 'numeric' });
    document.getElementById('give-review').innerHTML =
      '<div class="awt-review-top">' + t.emblem + '<div><h4>' + awEsc(t.name) + '</h4><span>' + awEsc(t.category) + '</span></div></div>' +
      '<dl><dt>Student</dt><dd>' + awEsc(s.name) + ' (' + awEsc(s.school_id) + ')</dd>' +
      '<dt>Section</dt><dd>' + awEsc(s.section) + '</dd>' +
      '<dt>Subject</dt><dd>' + awEsc(AW_COURSES[cid] || '') + '</dd>' +
      '<dt>Reason</dt><dd>' + awEsc(reason) + '</dd>' +
      '<dt>Date</dt><dd>' + awEsc(today) + '</dd></dl>';
    awShowErr('give-err-2', '');
    giveStep(2);
  }
  async function submitAward(btn) {
    if (btn.disabled) return;
    awShowErr('give-err-2', '');
    const payload = {
      student_id: Number(document.getElementById('give-student').value),
      course_id: Number(document.getElementById('give-course').value),
      award_type_id: Number(document.getElementById('give-type').value),
      reason: document.getElementById('give-reason').value.trim(),
      request_token: awGiveToken
    };
    awBusy(btn, true, 'Giving award...');
    document.getElementById('give-back-btn').disabled = true;
    try {
      const data = await awPost('give', payload);
      if (data.success) {
        showToast(data.duplicate ? 'This award was already saved.' : 'Award given successfully.');
        closeModal('giveAwardModal');
        setTimeout(() => location.replace('awards.php'), 700);
        return; // keep the button disabled until the page reloads
      }
      awShowErr('give-err-2', data.message || 'Unable to save the award. Please try again.');
    } catch (e) {
      awShowErr('give-err-2', 'Unable to save the award. Please check your connection and try again.');
    }
    awBusy(btn, false);
    document.getElementById('give-back-btn').disabled = false;
  }

  /* ── View / edit / revoke ─────────────────────── */
  function viewAward(id) {
    const a = AW_AWARDS[id]; if (!a) return;
    document.getElementById('view-body').innerHTML =
      '<div class="awt-review-top">' + a.emblem + '<div><h4>' + awEsc(a.award) + '</h4><span>' + awEsc(a.category) + '</span></div></div>' +
      '<dl><dt>Student</dt><dd>' + awEsc(a.student) + ' (' + awEsc(a.school_id) + ')</dd>' +
      '<dt>Section</dt><dd>' + awEsc(a.section) + '</dd>' +
      '<dt>Subject</dt><dd>' + awEsc(a.subject) + '</dd>' +
      '<dt>Reason</dt><dd>' + awEsc(a.reason) + '</dd>' +
      '<dt>Date awarded</dt><dd>' + awEsc(a.date) + '</dd>' +
      '<dt>Status</dt><dd><span class="awt-status ' + (a.status === 'Active' ? 'active' : 'revoked') + '">' + awEsc(a.status) + '</span>' + (a.revoked ? ' on ' + awEsc(a.revoked) : '') + '</dd></dl>';
    openModal('viewAwardModal');
  }
  function openEditAward(id) {
    const a = AW_AWARDS[id]; if (!a || a.status !== 'Active') return;
    document.getElementById('edit-id').value = id;
    document.getElementById('edit-who').textContent = a.student + ' · ' + a.subject;
    const sel = document.getElementById('edit-type');
    sel.innerHTML = Object.values(AW_TYPES)
      .filter(t => t.active || t.id === a.award_type_id)
      .map(t => '<option value="' + t.id + '">' + awEsc(t.name + ' — ' + t.category) + (t.active ? '' : ' (inactive)') + '</option>').join('');
    sel.value = String(a.award_type_id);
    renderTypePreview('edit-type', 'edit-type-preview');
    document.getElementById('edit-reason').value = a.reason;
    updateCount('edit-reason', 'edit-reason-count');
    awShowErr('edit-err', '');
    openModal('editAwardModal');
  }
  async function submitEditAward(btn) {
    if (btn.disabled) return;
    awShowErr('edit-err', '');
    const payload = {
      id: Number(document.getElementById('edit-id').value),
      award_type_id: Number(document.getElementById('edit-type').value),
      reason: document.getElementById('edit-reason').value.trim()
    };
    if (payload.reason.length < 5) return awShowErr('edit-err', 'Please enter a reason for this award (at least 5 characters).');
    awBusy(btn, true, 'Saving...');
    try {
      const data = await awPost('update', payload);
      if (data.success) { showToast('Award updated.'); closeModal('editAwardModal'); setTimeout(() => location.replace('awards.php'), 600); return; }
      awShowErr('edit-err', data.message || 'Unable to save the award. Please try again.');
    } catch (e) { awShowErr('edit-err', 'Unable to save the award. Please check your connection and try again.'); }
    awBusy(btn, false);
  }
  function openRevokeAward(id) {
    const a = AW_AWARDS[id]; if (!a) return;
    document.getElementById('revoke-id').value = id;
    document.getElementById('revoke-text').textContent = 'Revoke "' + a.award + '" from ' + a.student + ' (' + a.subject + ')? It will no longer appear on the student\u2019s dashboard.';
    awShowErr('revoke-err', '');
    openModal('revokeAwardModal');
  }
  async function submitRevokeAward(btn) {
    if (btn.disabled) return;
    awBusy(btn, true, 'Revoking...');
    try {
      const data = await awPost('revoke', { id: Number(document.getElementById('revoke-id').value) });
      if (data.success) { showToast('Award revoked.'); closeModal('revokeAwardModal'); setTimeout(() => location.replace('awards.php'), 600); return; }
      awShowErr('revoke-err', data.message || 'Unable to save the award. Please try again.');
    } catch (e) { awShowErr('revoke-err', 'Unable to save the award. Please check your connection and try again.'); }
    awBusy(btn, false);
  }

  /* ── Table filters ─────────────────────────────── */
  function filterAwardRows() {
    const q = (document.getElementById('awt-search')?.value || '').toLowerCase().trim();
    const c = document.getElementById('awt-filter-subject')?.value || '';
    const st = document.getElementById('awt-filter-status')?.value || '';
    let shown = 0;
    document.querySelectorAll('#awt-tbody tr').forEach(tr => {
      const ok = (!q || tr.dataset.search.includes(q)) && (!c || tr.dataset.course === c) && (!st || tr.dataset.status === st);
      tr.style.display = ok ? '' : 'none';
      if (ok) shown++;
    });
    const nm = document.getElementById('awt-no-match');
    if (nm) nm.style.display = shown ? 'none' : 'block';
  }

  document.addEventListener('keydown', e => {
    if (e.key !== 'Escape') return;
    ['giveAwardModal', 'viewAwardModal', 'editAwardModal', 'revokeAwardModal'].forEach(id => {
      const m = document.getElementById(id); if (m && m.classList.contains('show')) closeModal(id);
    });
  });
</script>
<?php endif; ?>

<?php require __DIR__ . '/../includes/footer.php'; ?>
