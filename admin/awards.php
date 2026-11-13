<?php
// ============================================================
//  Arandia College eLMS — Admin: Award Management
//  File: admin/awards.php
//
//  Manage the award TYPES teachers can give, and review every award
//  that has been given. Page access: view_awards or manage_awards
//  (Head Admin always). Changes: manage_awards, re-checked in
//  api/awards.php.
// ============================================================
session_start();
require_once '../config/conn.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['Admin', 'Head Admin'], true)) {
    header('Location: ../login.php');
    exit;
}
require_once '../config/permissions.php';
requireAdminPermission($conn, ['view_awards', 'manage_awards']);
require_once '../includes/awards.php';

$isHeadAdmin = isHeadAdmin();
$canManage = adminHasPermission($conn, 'manage_awards');

$first_name = $_SESSION['first_name'];
$last_name = $_SESSION['last_name'];

// Variables the shared admin footer expects.
$toast = $_GET['toast'] ?? '';
$isError = $_GET['error'] ?? '';
require_once __DIR__ . '/../includes/sections.php';
[$shsSections, $hsSections] = loadSectionLists($conn);

$tab = ($_GET['tab'] ?? 'types') === 'given' ? 'given' : 'types';
$awardsReady = awards_tables_ready($conn);

$types = $awardsReady ? awards_select(
    $conn,
    "SELECT t.id, t.name, t.description, t.category, t.icon, t.tone, t.status,
            DATE_FORMAT(t.updated_at, '%b %d, %Y') AS updated,
            (SELECT COUNT(*) FROM student_awards sa WHERE sa.award_type_id = t.id AND sa.status = 'Active') AS times_given
     FROM award_types t
     ORDER BY t.status = 'Active' DESC, t.name ASC"
) : [];

$given = $awardsReady ? awards_select(
    $conn,
    "SELECT sa.id, sa.reason, sa.status, sa.awarded_at, sa.revoked_at, sa.school_year, sa.course_id,
            su.first_name AS s_first, su.last_name AS s_last, su.school_id, su.section_dept,
            tu.first_name AS teacher_first, tu.last_name AS teacher_last,
            c.course_code, c.course_name,
            t.name AS award_name, t.icon, t.tone
     FROM student_awards sa
     JOIN users su ON su.id = sa.student_id
     JOIN courses c ON c.id = sa.course_id
     JOIN award_types t ON t.id = sa.award_type_id
     LEFT JOIN users tu ON tu.id = sa.teacher_id
     ORDER BY sa.awarded_at DESC, sa.id DESC
     LIMIT 500"
) : [];

$typesJS = [];
foreach ($types as $t) {
    $typesJS[(int) $t['id']] = [
        'id' => (int) $t['id'], 'name' => $t['name'], 'description' => $t['description'] ?? '',
        'category' => $t['category'], 'icon' => $t['icon'], 'tone' => $t['tone'], 'status' => $t['status'],
        'given' => (int) $t['times_given'], 'updated' => $t['updated'],
    ];
}
$givenJS = [];
foreach ($given as $g) {
    $givenJS[(int) $g['id']] = [
        'id' => (int) $g['id'], 'award' => $g['award_name'], 'icon' => $g['icon'], 'tone' => $g['tone'],
        'student' => $g['s_last'] . ', ' . $g['s_first'], 'school_id' => $g['school_id'],
        'section' => $g['section_dept'] ?: '—',
        'subject' => $g['course_name'], 'code' => $g['course_code'], 'school_year' => $g['school_year'] ?: '—',
        'teacher' => awards_teacher_name($g), 'reason' => $g['reason'] ?: '—',
        'date' => awards_format_date($g['awarded_at'], 'F j, Y'),
        'revoked' => $g['revoked_at'] ? awards_format_date($g['revoked_at'], 'F j, Y') : '',
        'status' => $g['status'],
    ];
}
$emblemPreviews = [];
foreach (array_keys(awards_icon_paths()) as $ik) {
    foreach (array_keys(awards_tones()) as $tk) {
        $emblemPreviews[$ik . '|' . $tk] = awards_emblem($ik, $tk, 'lg');
    }
}
$activeTypes = count(array_filter($types, fn($t) => $t['status'] === 'Active'));
$activeGiven = count(array_filter($given, fn($g) => $g['status'] === 'Active'));
$givenSubjects = [];
foreach ($given as $g) $givenSubjects[(int) $g['course_id']] = $g['course_name'] . ' (' . $g['course_code'] . ')';
asort($givenSubjects);

$csrf = awards_csrf_token();
$jsonFlags = JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT;

$panel = 'awards';
$currentPage = 'awards.php';
$pageTitle = 'Award Management';
require __DIR__ . '/../includes/header.php';
?>

<?= awards_emblem_css() ?>
<style>
  .awa-tabs { display: flex; gap: .25rem; border-bottom: 1px solid var(--border); margin-bottom: 1.25rem; overflow-x: auto; }
  .awa-tab { padding: .6rem 1rem; font-size: .84rem; font-weight: 700; color: var(--text-secondary); text-decoration: none; border-bottom: 2px solid transparent; margin-bottom: -1px; white-space: nowrap; }
  .awa-tab.on { color: var(--primary); border-bottom-color: var(--primary); }
  .awa-tab:focus-visible { outline: 2px solid var(--primary); outline-offset: 2px; }
  .awa-count { display: inline-block; font-size: .68rem; background: var(--primary-light); color: var(--primary); border-radius: 100px; padding: .05rem .5rem; margin-left: .3rem; }
  .awa-wrap { overflow-x: auto; }
  .awa-name { display: flex; gap: .75rem; align-items: center; min-width: 220px; }
  .awa-name .aw-emblem { margin-top: -.8rem; margin-bottom: -.8rem; }
  .awa-name strong { display: block; font-weight: 700; }
  .awa-sub { font-size: .72rem; color: var(--text-muted); }
  .awa-desc { max-width: 38ch; font-size: .78rem; color: var(--text-secondary); }
  .awa-reason { max-width: 36ch; font-size: .78rem; color: var(--text-secondary); overflow-wrap: anywhere; }
  .awa-empty { text-align: center; color: var(--text-muted); padding: 2.5rem 1rem !important; }
  .awa-notice { background: var(--warning-light); color: var(--warning); border-radius: var(--radius); padding: .9rem 1.1rem; font-size: .84rem; font-weight: 600; margin-bottom: 1.25rem; }
  .status-revoked { background: var(--ui-surface-2, #f6f8fa); color: var(--text-muted); }

  /* Type editor */
  .awa-editor { display: grid; grid-template-columns: 1fr 150px; gap: 1.25rem; }
  .awa-preview { border: 1px solid var(--border); border-radius: var(--radius); padding: 0 .9rem 1rem; text-align: center; align-self: start; }
  .awa-preview-name { font-family: 'Nunito', sans-serif; font-weight: 800; font-size: .88rem; margin-top: .6rem; overflow-wrap: anywhere; }
  .awa-preview-cat { font-size: .72rem; color: var(--text-muted); }
  .awa-icon-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(42px, 1fr)); gap: .4rem; }
  .awa-icon-opt, .awa-tone-opt { position: relative; }
  .awa-icon-opt input, .awa-tone-opt input { position: absolute; opacity: 0; width: 1px; height: 1px; }
  .awa-icon-opt span { display: flex; align-items: center; justify-content: center; height: 40px; border: 1px solid var(--border); border-radius: 8px; color: var(--text-secondary); cursor: pointer; }
  .awa-icon-opt span .aw-ico { width: 20px; height: 20px; }
  .awa-icon-opt input:checked + span { border-color: var(--primary); color: var(--primary); background: var(--primary-light); }
  .awa-icon-opt input:focus-visible + span, .awa-tone-opt input:focus-visible + span { box-shadow: 0 0 0 3px rgba(0,48,135,.3); }
  .awa-tone-row { display: flex; gap: .5rem; flex-wrap: wrap; }
  .awa-tone-opt span { display: block; width: 30px; height: 30px; border-radius: 50%; cursor: pointer; border: 3px solid #fff; box-shadow: 0 0 0 1px var(--border); }
  .awa-tone-opt input:checked + span { box-shadow: 0 0 0 2px var(--text-primary); }
  .awa-modal { max-width: 640px !important; }
  .awa-cell-actions { white-space: nowrap; }
  .awa-row { cursor: pointer; }
  .awa-row:focus-visible { outline: 2px solid var(--primary); outline-offset: -2px; }
  .awa-row:hover td { background: var(--primary-light) !important; }

  /* Details popup */
  .awa-detail { max-width: 460px !important; }
  .awa-detail-head { display: flex; flex-direction: column; align-items: center; text-align: center; gap: .35rem; margin-bottom: 1rem; }
  .awa-detail-name { font-family: 'Nunito', sans-serif; font-weight: 800; font-size: 1.15rem; overflow-wrap: anywhere; }
  .awa-detail-list { display: grid; gap: 0; border: 1px solid var(--border-muted); border-radius: 12px; overflow: hidden; margin: 0; }
  .awa-detail-row { display: grid; grid-template-columns: 7rem minmax(0, 1fr); gap: .75rem; padding: .65rem .9rem; border-bottom: 1px solid var(--border-muted); font-size: .84rem; }
  .awa-detail-row:last-child { border-bottom: none; }
  .awa-detail-row dt { font-size: .7rem; font-weight: 600; letter-spacing: .05em; text-transform: uppercase; color: var(--text-muted); padding-top: .12rem; }
  .awa-detail-row dd { margin: 0; overflow-wrap: anywhere; white-space: pre-line; }
  @media (max-width: 480px) {
    .awa-detail-row { grid-template-columns: minmax(0, 1fr); gap: .15rem; }
    .awa-detail .modal-actions { flex-wrap: wrap; }
    .awa-detail .modal-actions .btn { flex: 1; justify-content: center; }
  }
  .awa-nowrap { white-space: nowrap; }

  /* ── Tablets: tighter header, filters wrap ── */
  @media (max-width: 900px) {
    .awa-desc, .awa-reason { max-width: 28ch; }
  }

  /* ── Phones + small tablets: table rows become cards ── */
  @media (max-width: 760px) {
    .page-header { align-items: stretch; margin-bottom: 1rem; }
    .page-header .page-title { font-size: 1.35rem; }
    .page-header > .btn { width: 100%; justify-content: center; height: 42px; }

    .awa-tabs { margin-bottom: 1rem; scrollbar-width: none; }
    .awa-tabs::-webkit-scrollbar { display: none; }
    .awa-tab { padding: .6rem .8rem; }

    .table-card .table-header { padding: 1rem; }
    .table-card .table-actions { width: 100%; display: grid; grid-template-columns: minmax(0, 1fr); gap: .5rem; }
    .table-card .table-actions .search-input { width: 100%; min-width: 0; height: 42px; }

    .awa-wrap, .awa-wrap.table-scroll, .awa-wrap .table-scroll { overflow: visible; }
    .awa-table, .awa-table tbody { display: block; width: 100%; min-width: 0 !important; }
    .awa-table thead { display: none; }
    .awa-table tbody { display: grid; gap: .65rem; padding: .75rem; }
    .awa-table tr {
      display: grid; grid-template-columns: minmax(0, 1fr); gap: .1rem;
      border: 1px solid var(--border); border-radius: 12px; padding: .8rem .9rem; background: var(--surface);
    }
    .awa-table tr:hover td { background: transparent !important; }
    .awa-table td { display: block; padding: 0; border: 0; min-width: 0; }
    .awa-table td[data-label] {
      display: grid; grid-template-columns: 5.5rem minmax(0, 1fr); gap: .5rem; align-items: center;
      padding: .3rem 0; font-size: .83rem; overflow-wrap: anywhere;
    }
    .awa-table td[data-label]::before {
      content: attr(data-label); font-size: .68rem; font-weight: 600; letter-spacing: .05em;
      text-transform: uppercase; color: var(--text-muted);
    }
    .awa-table td[data-label="Reason"] { align-items: start; }
    .awa-table td[data-label="Reason"] .awa-reason, .awa-table .awa-desc { max-width: none; }
    .awa-table td[data-label] .badge-status { justify-self: start; }

    /* Card head: emblem + name */
    .awa-table td.awa-cell-main { padding: 0 0 .55rem; margin-bottom: .2rem; border-bottom: 1px solid var(--border-muted); }
    .awa-name { min-width: 0; }
    .awa-name .aw-emblem { margin-top: 0; margin-bottom: 0; }
    .awa-name > div { min-width: 0; overflow-wrap: anywhere; }
    .awa-table td.awa-cell-desc { padding: .1rem 0 .4rem; }

    /* Phone cards show only the award + its description; tap the card for everything else */
    .awa-table td[data-label], .awa-table td.awa-cell-actions { display: none; }
    .awa-table td.awa-cell-reason { display: block; padding: .1rem 0 .1rem; }
    .awa-table td.awa-cell-reason::before { content: none; }
    .awa-table td.awa-cell-main { margin-bottom: .35rem; }
    .awa-table td.awa-cell-desc, .awa-table td.awa-cell-reason { padding: 0; }
    .awa-table .awa-desc, .awa-table .awa-reason {
      display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;
      font-size: .82rem; line-height: 1.45;
    }
    .awa-table tr.awa-row { cursor: pointer; }
    .awa-table tr.awa-row:active { background: var(--primary-light); }

    .awa-table tr#awa-no-match { display: block; }
    .awa-table td.awa-empty { display: block; padding: 1.75rem 1rem !important; }

    .awa-modal { max-height: calc(100dvh - 1.5rem); overflow-y: auto; }
  }
  ::-webkit-scrollbar-button { display: none; width: 0; height: 0; }
  #awa-subject, #awa-status, #at-category, #at-status {
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='6' viewBox='0 0 10 6'%3E%3Cpath d='M1 1l4 4 4-4' stroke='%23667085' stroke-width='1.5' fill='none' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right .8rem center;
    padding-right: 2rem;
  }
  @media (max-width: 640px) {
    .awa-editor { grid-template-columns: minmax(0, 1fr); }
    .awa-preview { order: -1; }
  }
</style>

<div class="section-panel active">
  <div class="page-header">
    <div class="page-header-text">
      <div class="page-title">Award Management</div>
    </div>
    <?php if ($awardsReady && $canManage && $tab === 'types'): ?>
      <button class="btn btn-primary" type="button" onclick="openTypeModal()"><?= a_icon('plus') ?> Add Award Type</button>
    <?php endif; ?>
  </div>

  <?php if (!$awardsReady): ?>
    <div class="awa-notice">The awards tables were not found. Import <strong>database/migration_awards.sql</strong> into the elmsdb database to enable this feature.</div>
  <?php else: ?>

  <nav class="awa-tabs" aria-label="Award management sections">
    <a class="awa-tab <?= $tab === 'types' ? 'on' : '' ?>" href="awards.php?tab=types" <?= $tab === 'types' ? 'aria-current="page"' : '' ?>>Award Types<span class="awa-count"><?= count($types) ?></span></a>
    <a class="awa-tab <?= $tab === 'given' ? 'on' : '' ?>" href="awards.php?tab=given" <?= $tab === 'given' ? 'aria-current="page"' : '' ?>>Awarded Achievements<span class="awa-count"><?= $activeGiven ?></span></a>
  </nav>

  <?php if ($tab === 'types'): ?>
    <div class="table-card">
      <div class="table-header">
        <h3><?= $activeTypes ?> active of <?= count($types) ?> award types</h3>
        <?php if (!$canManage): ?><span class="awa-sub">View only — you do not have permission to change award types.</span><?php endif; ?>
      </div>
      <div class="awa-wrap">
        <table class="awa-table awa-types">
          <thead><tr><th>Award</th><th>Description</th><th>Given</th><th>Status</th><th>Updated</th><?php if ($canManage): ?><th>Actions</th><?php endif; ?></tr></thead>
          <tbody>
            <?php if (empty($types)): ?>
              <tr><td colspan="<?= $canManage ? 6 : 5 ?>" class="awa-empty">No award types available.</td></tr>
            <?php else: foreach ($types as $t): $tid = (int) $t['id']; ?>
              <tr class="awa-row" data-detail="type" data-id="<?= $tid ?>" tabindex="0" role="button" aria-label="View details of <?= htmlspecialchars($t['name']) ?>">
                <td class="awa-cell-main"><div class="awa-name"><?= awards_emblem($t['icon'], $t['tone'], 'sm') ?><div><strong><?= htmlspecialchars($t['name']) ?></strong><span class="awa-sub"><?= htmlspecialchars($t['category']) ?></span></div></div></td>
                <td class="awa-cell-desc"><div class="awa-desc"><?= htmlspecialchars($t['description'] ?: '—') ?></div></td>
                <td data-label="Given"><?= (int) $t['times_given'] ?></td>
                <td data-label="Status"><span class="badge-status status-<?= strtolower($t['status']) ?>"><?= htmlspecialchars($t['status']) ?></span></td>
                <td data-label="Updated"><?= htmlspecialchars($t['updated']) ?></td>
                <?php if ($canManage): ?>
                <td class="awa-cell-actions">
                  <button class="btn btn-edit btn-sm" type="button" onclick="openTypeModal(<?= $tid ?>)"><?= a_icon('edit') ?> Edit</button>
                  <?php if ($t['status'] === 'Active'): ?>
                    <button class="btn btn-warning btn-sm" type="button" onclick="setTypeStatus(<?= $tid ?>, 'Inactive', this)">Deactivate</button>
                  <?php else: ?>
                    <button class="btn btn-primary btn-sm" type="button" onclick="setTypeStatus(<?= $tid ?>, 'Active', this)">Activate</button>
                  <?php endif; ?>
                </td>
                <?php endif; ?>
              </tr>
            <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>
    </div>
    <p class="awa-sub" style="margin-top:.75rem">Inactive award types can no longer be given, but awards already given with them stay visible to students.</p>

  <?php else: ?>
    <div class="table-card">
      <div class="table-header">
        <h3>Awarded Achievements</h3>
        <?php if (!empty($given)): ?>
        <div class="table-actions">
          <input class="search-input" id="awa-search" type="search" placeholder="Search student, teacher, award" oninput="filterGiven()" aria-label="Search awarded achievements" autocomplete="off">
          <select class="search-input" id="awa-subject" onchange="filterGiven()" aria-label="Filter by subject">
            <option value="">All subjects</option>
            <?php foreach ($givenSubjects as $cid => $label): ?><option value="<?= (int) $cid ?>"><?= htmlspecialchars($label) ?></option><?php endforeach; ?>
          </select>
          <select class="search-input" id="awa-status" onchange="filterGiven()" aria-label="Filter by status">
            <option value="">All statuses</option><option value="Active">Active</option><option value="Revoked">Revoked</option>
          </select>
        </div>
        <?php endif; ?>
      </div>
      <div class="awa-wrap">
        <table class="awa-table awa-given">
          <thead><tr><th>Award</th><th>Student</th><th>Subject</th><th>Teacher</th><th>Reason</th><th>Date</th><th>Status</th></tr></thead>
          <tbody id="awa-given-body">
            <?php if (empty($given)): ?>
              <tr><td colspan="7" class="awa-empty">No awards have been given yet.</td></tr>
            <?php else: foreach ($given as $g):
              $search = strtolower($g['s_first'] . ' ' . $g['s_last'] . ' ' . $g['school_id'] . ' ' . $g['award_name'] . ' ' . awards_teacher_name($g));
            ?>
              <tr class="awa-row" data-detail="given" data-id="<?= (int) $g['id'] ?>" tabindex="0" role="button" aria-label="View details of this award" data-course="<?= (int) $g['course_id'] ?>" data-status="<?= htmlspecialchars($g['status']) ?>" data-search="<?= htmlspecialchars($search) ?>">
                <td class="awa-cell-main"><div class="awa-name"><?= awards_emblem($g['icon'], $g['tone'], 'sm') ?><strong><?= htmlspecialchars($g['award_name']) ?></strong></div></td>
                <td data-label="Student"><strong><?= htmlspecialchars($g['s_last'] . ', ' . $g['s_first']) ?></strong><div class="awa-sub"><?= htmlspecialchars($g['school_id']) ?> &middot; <?= htmlspecialchars($g['section_dept'] ?: '—') ?></div></td>
                <td data-label="Subject"><?= htmlspecialchars($g['course_name']) ?><div class="awa-sub"><?= htmlspecialchars($g['course_code']) ?><?= $g['school_year'] ? ' &middot; ' . htmlspecialchars($g['school_year']) : '' ?></div></td>
                <td data-label="Teacher"><?= htmlspecialchars(awards_teacher_name($g)) ?></td>
                <td class="awa-cell-reason" data-label="Reason"><div class="awa-reason"><?= htmlspecialchars($g['reason']) ?></div></td>
                <td class="awa-nowrap" data-label="Date"><?= htmlspecialchars(awards_format_date($g['awarded_at'], 'M j, Y')) ?></td>
                <td data-label="Status"><span class="badge-status status-<?= strtolower($g['status']) ?>"><?= htmlspecialchars($g['status']) ?></span></td>
              </tr>
            <?php endforeach; endif; ?>
            <tr id="awa-no-match" style="display:none"><td colspan="7" class="awa-empty">No awards match your filters.</td></tr>
          </tbody>
        </table>
      </div>
    </div>
  <?php endif; ?>
  <?php endif; ?>
</div>

<?php if ($awardsReady): ?>
<!-- AWARD DETAILS POPUP -->
<div class="modal-overlay" id="awardDetailModal" role="dialog" aria-modal="true" aria-labelledby="awd-title">
  <div class="modal awa-detail">
    <div class="awa-detail-head">
      <div id="awd-emblem"></div>
      <div class="awa-detail-name" id="awd-title"></div>
      <div id="awd-badge"></div>
    </div>
    <dl class="awa-detail-list" id="awd-list"></dl>
    <div class="modal-actions">
      <?php if ($canManage): ?>
      <button class="btn btn-edit" type="button" id="awd-edit-btn" style="display:none"><?= a_icon('edit') ?> Edit</button>
      <button class="btn btn-warning" type="button" id="awd-status-btn" style="display:none"></button>
      <?php endif; ?>
      <button class="btn btn-reset-form" type="button" onclick="closeModal('awardDetailModal')">Close</button>
    </div>
  </div>
</div>
<script>
  (function () {
    const TYPES = <?= json_encode((object) $typesJS, $jsonFlags) ?>;
    const GIVEN = <?= json_encode((object) $givenJS, $jsonFlags) ?>;
    const EMBLEMS = <?= json_encode($emblemPreviews, $jsonFlags) ?>;
    const esc = s => String(s == null ? '' : s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    const modal = document.getElementById('awardDetailModal');
    const editBtn = document.getElementById('awd-edit-btn');
    const statusBtn = document.getElementById('awd-status-btn');

    function rows(list) {
      return list.map(r => '<div class="awa-detail-row"><dt>' + esc(r[0]) + '</dt><dd>' + (r[2] ? r[1] : esc(r[1])) + '</dd></div>').join('');
    }
    function badge(status) { return '<span class="badge-status status-' + esc(status.toLowerCase()) + '">' + esc(status) + '</span>'; }

    function show(kind, id) {
      const list = document.getElementById('awd-list');
      if (kind === 'type') {
        const t = TYPES[id]; if (!t) return;
        document.getElementById('awd-emblem').innerHTML = EMBLEMS[t.icon + '|' + t.tone] || '';
        document.getElementById('awd-title').textContent = t.name;
        document.getElementById('awd-badge').innerHTML = badge(t.status);
        list.innerHTML = rows([
          ['Category', t.category], ['Description', t.description || '—'],
          ['Times given', String(t.given)], ['Last updated', t.updated]
        ]);
        if (editBtn) { editBtn.style.display = ''; editBtn.onclick = () => { closeModal('awardDetailModal'); openTypeModal(t.id); }; }
        if (statusBtn) {
          const next = t.status === 'Active' ? 'Inactive' : 'Active';
          statusBtn.style.display = '';
          statusBtn.textContent = next === 'Inactive' ? 'Deactivate' : 'Activate';
          statusBtn.className = 'btn ' + (next === 'Inactive' ? 'btn-warning' : 'btn-primary');
          statusBtn.onclick = () => { closeModal('awardDetailModal'); setTypeStatus(t.id, next, statusBtn); };
        }
      } else {
        const g = GIVEN[id]; if (!g) return;
        document.getElementById('awd-emblem').innerHTML = EMBLEMS[g.icon + '|' + g.tone] || '';
        document.getElementById('awd-title').textContent = g.award;
        document.getElementById('awd-badge').innerHTML = badge(g.status);
        const r = [
          ['Student', g.student], ['Student ID', g.school_id], ['Section', g.section],
          ['Subject', g.subject + ' (' + g.code + ')'], ['School year', g.school_year],
          ['Awarded by', g.teacher], ['Reason', g.reason], ['Date awarded', g.date]
        ];
        if (g.revoked) r.push(['Revoked on', g.revoked]);
        list.innerHTML = rows(r);
        if (editBtn) editBtn.style.display = 'none';
        if (statusBtn) statusBtn.style.display = 'none';
      }
      modal.classList.add('show');
    }

    function trigger(e) {
      const tr = e.target.closest('tr.awa-row');
      if (!tr || e.target.closest('button, a, input, select, textarea')) return;
      show(tr.dataset.detail, Number(tr.dataset.id));
    }
    document.addEventListener('click', trigger);
    document.addEventListener('keydown', e => {
      if (e.key === 'Escape' && modal.classList.contains('show')) closeModal('awardDetailModal');
      if ((e.key === 'Enter' || e.key === ' ') && e.target.matches && e.target.matches('tr.awa-row')) { e.preventDefault(); trigger(e); }
    });
    modal.addEventListener('click', e => { if (e.target === modal) closeModal('awardDetailModal'); });
  })();
</script>
<?php endif; ?>

<?php if ($awardsReady && $canManage): ?>
<!-- ADD / EDIT AWARD TYPE MODAL -->
<div class="modal-overlay" id="awardTypeModal" role="dialog" aria-modal="true" aria-labelledby="awt-modal-title">
  <div class="modal awa-modal">
    <div class="modal-title" id="awt-modal-title">Add Award Type</div>
    <input type="hidden" id="at-id">
    <div class="awa-editor">
      <div class="form-grid" style="grid-template-columns:minmax(0, 1fr)">
        <div class="form-error" id="at-err" role="alert"></div>
        <div class="form-group">
          <label class="form-label" for="at-name">Award Name *</label>
          <input class="form-input" id="at-name" maxlength="120" placeholder="e.g. Java Programming Excellence" oninput="updateTypePreview()">
        </div>
        <div class="form-group">
          <label class="form-label" for="at-category">Category *</label>
          <select class="form-input" id="at-category" onchange="updateTypePreview()">
            <?php foreach (awards_categories() as $c): ?><option value="<?= htmlspecialchars($c) ?>"><?= htmlspecialchars($c) ?></option><?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label" for="at-desc">Description</label>
          <textarea class="form-input" id="at-desc" rows="2" maxlength="500" placeholder="e.g. Recognizes outstanding performance in Java Programming."></textarea>
        </div>
        <div class="form-group">
          <span class="form-label" id="at-icon-label">Icon *</span>
          <div class="awa-icon-grid" role="radiogroup" aria-labelledby="at-icon-label">
            <?php foreach (awards_icon_labels() as $ik => $il): ?>
              <label class="awa-icon-opt" title="<?= htmlspecialchars($il) ?>"><input type="radio" name="at-icon" value="<?= $ik ?>" onchange="updateTypePreview()" aria-label="<?= htmlspecialchars($il) ?>"><span><?= awards_icon_svg($ik) ?></span></label>
            <?php endforeach; ?>
          </div>
        </div>
        <div class="form-group">
          <span class="form-label" id="at-tone-label">Colour *</span>
          <div class="awa-tone-row" role="radiogroup" aria-labelledby="at-tone-label">
            <?php foreach (awards_tones() as $tk => $tv): ?>
              <label class="awa-tone-opt" title="<?= htmlspecialchars($tv[0]) ?>"><input type="radio" name="at-tone" value="<?= $tk ?>" onchange="updateTypePreview()" aria-label="<?= htmlspecialchars($tv[0]) ?>"><span style="background:<?= $tv[1] ?>"></span></label>
            <?php endforeach; ?>
          </div>
        </div>
        <div class="form-group">
          <label class="form-label" for="at-status">Status</label>
          <select class="form-input" id="at-status"><option value="Active">Active</option><option value="Inactive">Inactive</option></select>
        </div>
      </div>
      <div class="awa-preview" aria-live="polite">
        <div id="at-preview-emblem"></div>
        <div class="awa-preview-name" id="at-preview-name">Award name</div>
        <div class="awa-preview-cat" id="at-preview-cat"></div>
      </div>
    </div>
    <div class="modal-actions">
      <button class="btn btn-reset-form" type="button" onclick="closeModal('awardTypeModal')">Cancel</button>
      <button class="btn btn-primary" type="button" id="at-save-btn" onclick="saveType(this)">Save Award Type</button>
    </div>
  </div>
</div>

<script>
  const AWA_CSRF = <?= json_encode($csrf) ?>;
  const AWA_TYPES = <?= json_encode((object) $typesJS, $jsonFlags) ?>;
  const AWA_EMBLEMS = <?= json_encode($emblemPreviews, $jsonFlags) ?>;

  async function awaPost(action, payload) {
    const res = await fetch('../api/awards.php?action=' + encodeURIComponent(action), {
      method: 'POST', credentials: 'same-origin',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': AWA_CSRF },
      body: JSON.stringify(payload)
    });
    try { return await res.json(); } catch (e) { return { success: false, message: 'Unable to save your changes. Please try again.' }; }
  }
  function awaRadio(name) { const el = document.querySelector('input[name="' + name + '"]:checked'); return el ? el.value : ''; }
  function awaSetRadio(name, value) { document.querySelectorAll('input[name="' + name + '"]').forEach(r => r.checked = r.value === value); }
  function awaErr(msg) { const e = document.getElementById('at-err'); e.textContent = msg; e.style.display = msg ? 'block' : 'none'; }

  function openTypeModal(id) {
    const t = id ? AWA_TYPES[id] : null;
    document.getElementById('awt-modal-title').textContent = t ? 'Edit Award Type' : 'Add Award Type';
    document.getElementById('at-id').value = t ? t.id : '';
    document.getElementById('at-name').value = t ? t.name : '';
    document.getElementById('at-desc').value = t ? t.description : '';
    const cat = document.getElementById('at-category');
    if (t && ![...cat.options].some(o => o.value === t.category)) cat.add(new Option(t.category, t.category));
    cat.value = t ? t.category : cat.options[0].value;
    awaSetRadio('at-icon', t ? t.icon : 'medal');
    awaSetRadio('at-tone', t ? t.tone : 'navy');
    document.getElementById('at-status').value = t ? t.status : 'Active';
    awaErr('');
    updateTypePreview();
    document.getElementById('awardTypeModal').classList.add('show');
    setTimeout(() => document.getElementById('at-name').focus(), 50);
  }
  function updateTypePreview() {
    const key = (awaRadio('at-icon') || 'medal') + '|' + (awaRadio('at-tone') || 'navy');
    document.getElementById('at-preview-emblem').innerHTML = AWA_EMBLEMS[key] || '';
    document.getElementById('at-preview-name').textContent = document.getElementById('at-name').value.trim() || 'Award name';
    document.getElementById('at-preview-cat').textContent = document.getElementById('at-category').value;
  }
  async function saveType(btn) {
    if (btn.disabled) return;
    awaErr('');
    const payload = {
      id: Number(document.getElementById('at-id').value) || null,
      name: document.getElementById('at-name').value.trim(),
      description: document.getElementById('at-desc').value.trim(),
      category: document.getElementById('at-category').value,
      icon: awaRadio('at-icon'), tone: awaRadio('at-tone'),
      status: document.getElementById('at-status').value
    };
    if (payload.name.length < 3) return awaErr('Please enter an award name (at least 3 characters).');
    if (!payload.icon || !payload.tone) return awaErr('Please choose an icon and a colour.');
    setBtnLoading(btn, 'Saving...');
    try {
      const data = await awaPost('type_save', payload);
      if (data.success) { showToast(data.message || 'Saved.'); setTimeout(() => location.replace('awards.php?tab=types'), 700); return; }
      awaErr(data.message || 'Unable to save your changes. Please try again.');
    } catch (e) { awaErr('Unable to save your changes. Please check your connection and try again.'); }
    clearBtnLoading(btn);
  }
  async function setTypeStatus(id, status, btn) {
    if (status === 'Inactive' && !await confirmAction('Deactivate this award type? Teachers will no longer be able to give it. Awards already given stay visible.', { okText: 'Deactivate' })) return;
    setBtnLoading(btn, 'Saving...');
    try {
      const data = await awaPost('type_status', { id, status });
      if (data.success) { showToast(data.message); setTimeout(() => location.replace('awards.php?tab=types'), 600); return; }
      showToast(data.message || 'Unable to save your changes.', true);
    } catch (e) { showToast('Network error. Please try again.', true); }
    clearBtnLoading(btn);
  }
</script>
<?php endif; ?>

<?php if ($awardsReady && $tab === 'given' && !empty($given)): ?>
<script>
  function filterGiven() {
    const q = document.getElementById('awa-search').value.toLowerCase().trim();
    const c = document.getElementById('awa-subject').value;
    const s = document.getElementById('awa-status').value;
    let shown = 0;
    document.querySelectorAll('#awa-given-body tr[data-search]').forEach(tr => {
      const ok = (!q || tr.dataset.search.includes(q)) && (!c || tr.dataset.course === c) && (!s || tr.dataset.status === s);
      tr.style.display = ok ? '' : 'none';
      if (ok) shown++;
    });
    document.getElementById('awa-no-match').style.display = shown ? 'none' : '';
  }
</script>
<?php endif; ?>

<?php require __DIR__ . '/../includes/footer.php'; ?>