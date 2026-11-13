<?php
// ============================================================
//  Arandia College eLMS — Admin Panel
//  File: admin/modules.php   (sidebar: "Subjects")
//
//  modules.php            → every subject with its module count
//  modules.php?course=ID  → that subject's modules: add, edit,
//                           preview, reorder, delete
//  Modules belong to the subject, so every section taking it sees
//  the same modules. Needs Manage Modules (Head Admin always).
// ============================================================
if (session_status() === PHP_SESSION_NONE) session_start();
require_once '../config/conn.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['Admin', 'Head Admin'], true)) {
  header('Location: ../login.php');
  exit;
}
require_once '../config/permissions.php';
require_once '../includes/sections.php';
requireAdminPermission($conn, 'manage_modules');

$isHeadAdmin = isHeadAdmin();
$first_name = $_SESSION['first_name'];
$last_name  = $_SESSION['last_name'];
$toast   = $_GET['toast'] ?? '';
$isError = $_GET['error'] ?? '';
[$shsSections, $hsSections] = loadSectionLists($conn);

$TARGET = 2;   // modules every subject should have
// Add / edit subjects (api/admin_subjects.php) — Manage Subjects permission
$canSubjects = adminHasPermission($conn, 'manage_subjects');
// New subjects take the school year and semester set in Admin -> Settings (api/admin_subjects.php)
require_once '../includes/settings.php';
// Groups for the subject code: the code's first part decides where the subject is listed
$codeGroups = [['HS7-', 'Grade 7'], ['HS8-', 'Grade 8'], ['HS9-', 'Grade 9'], ['HS10-', 'Grade 10']];
$r = $conn->query("SELECT DISTINCT course_code FROM courses WHERE course_code REGEXP '^HS[0-9]+-'");
while ($r && $row = $r->fetch_assoc()) {
  if (preg_match('/^HS(\d+)-/', $row['course_code'], $gm) && !in_array('HS' . $gm[1] . '-', array_column($codeGroups, 0), true)) $codeGroups[] = ['HS' . $gm[1] . '-', 'Grade ' . $gm[1]];
}
usort($codeGroups, fn($a, $b) => (int) substr($a[0], 2) <=> (int) substr($b[0], 2));
$codeGroups = array_merge($codeGroups, [['SHS-', 'Senior High · Core and applied'], ['STEM-', 'Senior High · STEM'], ['ABM-', 'Senior High · ABM'],
                                        ['HUMSS-', 'Senior High · HUMSS'], ['GAS-', 'Senior High · GAS'], ['', 'Other']]);
$courseId = (int) ($_GET['course'] ?? 0);

/** Which group a subject belongs to, from its code. */
function subjectGroup(string $code): array
{
  if (preg_match('/^HS(\d+)-/', $code, $m)) return [(int) $m[1], 'Grade ' . $m[1]];
  $map = ['SHS' => [20, 'Senior High · Core and applied'], 'STEM' => [21, 'Senior High · STEM'], 'ABM' => [22, 'Senior High · ABM'],
          'HUMSS' => [23, 'Senior High · HUMSS'], 'GAS' => [24, 'Senior High · GAS']];
  $prefix = strtok($code, '-');
  return $map[$prefix] ?? [99, 'Other subjects'];
}

if (!$courseId) {
  // ── All subjects ─────────────────────────────────────────
  $view = 'grid';
  $groups = [];
  $res = $conn->query(
    "SELECT c.id, c.course_code, c.course_name, c.description, c.school_year, c.semester, COUNT(m.id) AS n
     FROM courses c LEFT JOIN modules m ON m.course_id = c.id
     WHERE c.status = 'Active'
     GROUP BY c.id ORDER BY c.course_code"
  );
  while ($res && $r = $res->fetch_assoc()) {
    [$order, $label] = subjectGroup($r['course_code']);
    $groups[$order]['label'] = $label;
    $groups[$order]['items'][] = $r;
  }
  ksort($groups);
  // Junior High subjects in the school's order: Filipino, English, Mathematics, Science, AP, EsP, MAPEH, TLE
  $hsOrder = ['FIL', 'ENGLISH', 'MATH', 'SCI', 'AP', 'ESP', 'MAPEH', 'TLE'];
  foreach ($groups as $k => &$g) {
    if ($k > 12) continue;
    usort($g['items'], function ($a, $b) use ($hsOrder) {
      $ra = array_search(substr(strrchr($a['course_code'], '-'), 1), $hsOrder, true);
      $rb = array_search(substr(strrchr($b['course_code'], '-'), 1), $hsOrder, true);
      return ($ra === false ? 99 : $ra) <=> ($rb === false ? 99 : $rb);
    });
  }
  unset($g);
  $totalSubjects = array_sum(array_map(fn($g) => count($g['items']), $groups));
  $complete = 0;
  foreach ($groups as $g) foreach ($g['items'] as $c) if ((int) $c['n'] >= $TARGET) $complete++;
  $pageTitle = 'Subjects';
} else {
  // ── One subject ──────────────────────────────────────────
  $view = 'detail';
  $st = $conn->prepare("SELECT id, course_code, course_name, description, school_year, semester FROM courses WHERE id = ?");
  $st->bind_param('i', $courseId);
  $st->execute();
  $course = $st->get_result()->fetch_assoc();
  $st->close();
  if (!$course) {
    header('Location: modules.php?toast=' . urlencode('That subject no longer exists.') . '&error=1');
    exit;
  }

  $modules = [];
  $st = $conn->prepare(
    "SELECT m.id, m.module_number, m.title, m.description, m.content, m.file_path,
            DATE_FORMAT(COALESCE(m.updated_at, m.created_at), '%b %d, %Y') AS changed_on,
            CONCAT(u.first_name, ' ', u.last_name) AS updated_by_name,
            (SELECT COUNT(*) FROM module_views v WHERE v.module_id = m.id) AS opened,
            (SELECT COUNT(*) FROM module_progress p WHERE p.module_id = m.id) AS done
     FROM modules m LEFT JOIN users u ON u.id = m.updated_by
     WHERE m.course_id = ? ORDER BY m.module_number"
  );
  $st->bind_param('i', $courseId);
  $st->execute();
  $res = $st->get_result();
  while ($r = $res->fetch_assoc()) $modules[] = $r;
  $st->close();

  $sections = [];
  $st = $conn->prepare("SELECT DISTINCT section FROM teacher_assignments WHERE course_id = ? ORDER BY section");
  $st->bind_param('i', $courseId);
  $st->execute();
  $res = $st->get_result();
  while ($r = $res->fetch_assoc()) $sections[] = $r['section'];
  $st->close();

  $enrolled = 0;
  $st = $conn->prepare("SELECT COUNT(*) AS n FROM enrollments WHERE course_id = ? AND status = 'Enrolled'");
  $st->bind_param('i', $courseId);
  $st->execute();
  $enrolled = (int) $st->get_result()->fetch_assoc()['n'];
  $st->close();

  $pageTitle = $course['course_name'];
  $breadcrumb = [['Subjects', 'modules.php'], [$course['course_name']]];
}

$panel = 'modules';
$currentPage = 'modules.php';
require __DIR__ . '/../includes/header.php';
?>
<style>
  .mx-head { display: flex; align-items: flex-end; justify-content: space-between; gap: 1rem; flex-wrap: wrap; margin-bottom: 1.25rem; }
  .mx-head .page-title { margin: 0; }
  .mx-sub { font-size: .84rem; color: var(--text-secondary); margin-top: .35rem; }
  .mx-actions { display: flex; gap: .5rem; align-items: center; flex-wrap: wrap; }
  .mx-group { font-size: .8rem; font-weight: 700; color: var(--text-secondary); margin: 1.75rem 0 .7rem; }
  .mx-group.first { margin-top: .25rem; }
  .mx-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: .75rem; }
  .mx-card { display: flex; justify-content: space-between; align-items: center; gap: .75rem; background: var(--surface); border: 1px solid var(--border);
             border-radius: 12px; padding: .85rem 1rem; text-decoration: none; color: inherit; transition: border-color .15s, box-shadow .15s; }
  .mx-card:hover { border-color: var(--primary); box-shadow: 0 4px 18px rgba(0, 48, 135, .08); }
  .mx-card:focus-visible { outline: 2px solid var(--primary); outline-offset: 2px; }
  .mx-name { font-weight: 700; font-size: .9rem; color: var(--text-primary); }
  .mx-code { font-size: .74rem; color: var(--text-secondary); margin-top: .15rem; }
  .mx-item { position: relative; min-width: 0; }
  .mx-item.can-edit .mx-card { padding-right: 3.1rem; height: 100%; }
  .mx-card-edit { position: absolute; top: 50%; right: .55rem; transform: translateY(-50%); width: 34px; height: 34px; padding: 0;
                  display: inline-flex; align-items: center; justify-content: center; border-radius: 8px; cursor: pointer;
                  border: 1px solid var(--border); background: var(--surface); color: var(--text-secondary); }
  .mx-card-edit:hover { color: var(--primary); border-color: var(--primary); }
  .mx-card-edit:focus-visible { outline: 2px solid var(--primary); outline-offset: 2px; }
  .mx-card-edit .ico { width: 15px; height: 15px; }
  .sj-code-row { display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); gap: .75rem; }
  .sj-code-wrap { display: flex; align-items: stretch; }
  .sj-prefix { display: inline-flex; align-items: center; padding: 0 .65rem; border: 1px solid var(--border); border-right: 0; border-radius: 8px 0 0 8px;
               background: var(--surface-hover); color: var(--text-secondary); font-weight: 700; font-size: .84rem; white-space: nowrap; }
  .sj-prefix:empty { display: none; }
  .sj-prefix:not(:empty) + .form-input { border-top-left-radius: 0; border-bottom-left-radius: 0; }
  .sj-hint { font-size: .76rem; color: var(--text-muted); margin-top: .35rem; }
  #sj-code[readonly] { background: var(--surface-hover); color: var(--text-secondary); font-weight: 700; font-size: .84rem; cursor: default; }   /* same look as the HS7- prefix */
  .sj-prefix:not(:empty) + #sj-code[readonly] { border-left: 0; padding-left: 0; }   /* prefix and code read as one: HS7-ENGLISH */
  #sj-code[readonly]:focus { outline: none; box-shadow: none; border-color: var(--border); }
  .sj-hint.warn { color: var(--danger, #dc2626); font-weight: 600; }
  .sj-term { font-size: .8rem; color: var(--text-secondary); background: var(--surface-hover); border: 1px solid var(--border); border-radius: 8px; padding: .55rem .75rem; margin-bottom: .5rem; }
  .sj-term strong { color: var(--text-primary); font-weight: 400; }
  @media (max-width: 560px) { .sj-code-row { grid-template-columns: minmax(0, 1fr); } }
  .mx-pill { font-size: .72rem; font-weight: 700; border-radius: 100px; padding: .2rem .6rem; white-space: nowrap; }
  .mx-pill.ok { background: var(--success-light); color: var(--success); }
  .mx-pill.warn { background: var(--warning-light); color: var(--warning); }
  .mx-back { display: inline-flex; align-items: center; gap: .45rem; font-size: .95rem; font-weight: 600; color: var(--text-secondary); text-decoration: none;
             padding: .45rem .75rem; margin: 0 0 .6rem -.75rem; border-radius: 10px; min-height: 40px; transition: background .15s, color .15s; }
  .mx-back .back-arrow { font-size: 1.35em; line-height: 1; margin-top: -.1em; }
  .mx-back:hover { color: var(--primary); background: var(--surface-hover); }
  .mx-back:focus-visible { outline: 2px solid var(--primary); outline-offset: 2px; }
  .mx-list { display: flex; flex-direction: column; gap: .6rem; }
  .mx-row { display: grid; grid-template-columns: 44px minmax(0, 1fr) auto; gap: 1rem; align-items: center; background: var(--surface);
            border: 1px solid var(--border); border-radius: 14px; padding: .9rem 1.1rem; }
  .mx-num { width: 40px; height: 40px; border-radius: 50%; background: var(--primary-light); color: var(--primary); font-weight: 800;
            display: flex; align-items: center; justify-content: center; font-size: .95rem; }
  .mx-title { font-weight: 700; color: var(--text-primary); font-size: .95rem; }
  .mx-desc { font-size: .82rem; color: var(--text-secondary); margin-top: .15rem; overflow: hidden; text-overflow: ellipsis; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; }
  .mx-meta { display: flex; gap: .9rem; flex-wrap: wrap; font-size: .75rem; color: var(--text-muted); margin-top: .4rem; }
  .mx-meta .warn { color: var(--warning); font-weight: 600; }
  .mx-btns { display: flex; gap: .3rem; align-items: center; }
  .mx-icon { padding: .4rem .5rem; }
  .mx-icon .ico { width: 15px; height: 15px; }
  .mx-icon[disabled] { opacity: .35; cursor: default; }
  .mx-empty { text-align: center; padding: 3rem 1rem; color: var(--text-secondary); background: var(--surface); border-radius: 14px; border: 1px dashed var(--border); }
  .mx-empty strong { display: block; color: var(--text-primary); margin-bottom: .3rem; }
  .mx-target { background: var(--warning-light); color: var(--warning); border-radius: 12px; padding: .8rem 1.1rem; font-size: .85rem; margin-bottom: 1rem; }
  .mx-toolbar { display: flex; gap: .25rem; flex-wrap: wrap; border: 1px solid var(--border); border-bottom: 0; border-radius: 10px 10px 0 0; padding: .35rem; background: var(--surface-hover); }
  .mx-toolbar button { border: 0; background: transparent; border-radius: 6px; padding: .3rem .6rem; font-size: .8rem; font-weight: 700; color: var(--text-secondary); cursor: pointer; font-family: inherit; }
  .mx-toolbar button:hover { background: var(--primary-light); color: var(--primary); }
  .mx-editor { min-height: 240px; max-height: 45vh; overflow-y: auto; border: 1px solid var(--border); border-radius: 0 0 10px 10px; padding: .85rem 1rem; outline: none; background: var(--surface); }
  .mx-editor:focus { border-color: var(--primary); }
  .lesson { color: var(--text-primary); line-height: 1.7; font-size: .93rem; }
  .lesson h3 { font-size: 1rem; font-weight: 800; margin: 1.1rem 0 .4rem; color: var(--text-primary); }
  .lesson h4 { font-size: .92rem; font-weight: 700; margin: .9rem 0 .3rem; }
  .lesson p { margin: 0 0 .7rem; }
  .lesson ul, .lesson ol { margin: 0 0 .8rem 1.25rem; padding: 0; }
  .lesson li { margin-bottom: .3rem; }
  .lesson blockquote { border-left: 3px solid var(--primary); margin: .6rem 0; padding: .2rem .9rem; color: var(--text-secondary); border-radius: 0; }
    .lesson table { border-collapse: collapse; margin: .4rem 0 .9rem; font-size: .88rem; max-width: 100%; display: block; overflow-x: auto; }
    .lesson th, .lesson td { border: 1px solid var(--ui-border, #e2e8f0); padding: .4rem .75rem; text-align: left; }
    .lesson th { background: var(--ui-surface-2, #f8fafc); font-weight: 700; }
    .lesson blockquote { background: var(--ui-surface-2, #f8fafc); border-left: 3px solid var(--primary); border-radius: 0 8px 8px 0; margin: .5rem 0 .9rem; padding: .6rem 1rem; color: inherit; }
    .lesson blockquote p:last-child { margin-bottom: 0; }
    .lesson details { background: var(--ui-surface-3, #f1f5f9); border-radius: 10px; padding: .6rem 1rem; margin: .5rem 0 .9rem; }
    .lesson summary { cursor: pointer; font-weight: 700; color: var(--primary); }
    .lesson details[open] summary { margin-bottom: .5rem; }
  .mx-file { display: inline-flex; align-items: center; gap: .4rem; font-size: .84rem; font-weight: 600; color: var(--primary); text-decoration: none; }
  @media (max-width: 700px) { .mx-row { grid-template-columns: 36px minmax(0, 1fr); } .mx-btns { grid-column: 1 / -1; justify-content: flex-end; } }
</style>

<div class="section-panel active">
<?php if ($view === 'grid'): ?>

  <div class="mx-head">
    <div>
      <div class="page-title">Subjects</div>
    </div>
    <div class="mx-actions">
      <input class="search-input" type="text" id="mx-filter" placeholder="Search subject..." oninput="mxFilter()" aria-label="Search subject" autocomplete="off">
      <?php if ($canSubjects): ?><button type="button" class="btn btn-primary" onclick="sjOpen()"><?= a_icon('plus') ?> Add subject</button><?php endif; ?>
    </div>
  </div>

  <?php $first = true; foreach ($groups as $g): ?>
    <div class="mx-group<?= $first ? ' first' : '' ?>"><?= htmlspecialchars($g['label']) ?></div>
    <?php $first = false; ?>
    <div class="mx-grid" data-grid>
      <?php foreach ($g['items'] as $c): $n = (int) $c['n']; ?>
        <div class="mx-item<?= $canSubjects ? ' can-edit' : '' ?>" data-course="<?= (int) $c['id'] ?>" data-text="<?= htmlspecialchars(mb_strtolower($c['course_name'] . ' ' . $c['course_code'])) ?>">
          <a class="mx-card" href="modules.php?course=<?= (int) $c['id'] ?>">
            <div style="min-width:0;">
              <div class="mx-name"><?= htmlspecialchars($c['course_name']) ?></div>
              <div class="mx-code"><?= htmlspecialchars($c['course_code']) ?></div>
            </div>
            <span class="mx-pill <?= $n >= $TARGET ? 'ok' : 'warn' ?>"><?= $n ?> module<?= $n === 1 ? '' : 's' ?></span>
          </a>
          <?php if ($canSubjects): ?>
            <button type="button" class="mx-card-edit" onclick="sjOpen(<?= (int) $c['id'] ?>)" title="Edit subject" aria-label="Edit <?= htmlspecialchars($c['course_name']) ?>"><?= a_icon('edit') ?></button>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endforeach; ?>

<?php else: ?>

  <a class="mx-back" href="modules.php"><span class="back-arrow" aria-hidden="true">&larr;</span> All subjects</a>
  <div class="mx-head">
    <div>
      <div class="page-title"><?= htmlspecialchars($course['course_name']) ?></div>
    </div>
    <div class="mx-actions">
      <?php if ($canSubjects): ?>
        <button type="button" class="btn btn-edit" onclick="sjOpen(<?= (int) $course['id'] ?>)"><?= a_icon('edit') ?> Edit subject</button>
      <?php endif; ?>
      <button class="btn btn-primary" onclick="mxOpenEditor()"><?= a_icon('plus') ?> Add module</button>
    </div>
  </div>

  <?php if (count($modules) < $TARGET): ?>
    <div class="mx-target">This subject has <?= count($modules) ?> of the <?= $TARGET ?> modules every subject should have.</div>
  <?php endif; ?>

  <?php if (!$modules): ?>
    <div class="mx-empty"><strong>No modules yet</strong>Add the first lesson students will read for this subject.</div>
  <?php else: ?>
    <div class="mx-list">
      <?php foreach ($modules as $i => $m): $hasText = trim(strip_tags((string) $m['content'])) !== ''; ?>
        <div class="mx-row">
          <div class="mx-num"><?= (int) $m['module_number'] ?></div>
          <div style="min-width:0;">
            <div class="mx-title"><?= htmlspecialchars($m['title']) ?></div>
            <?php if ($m['description']): ?><div class="mx-desc"><?= htmlspecialchars($m['description']) ?></div><?php endif; ?>
            <div class="mx-meta">
              <span>Opened by <?= (int) $m['opened'] ?> · Done by <?= (int) $m['done'] ?></span>
              <?php if (!$hasText && !$m['file_path']): ?><span class="warn">No lesson content</span>
              <?php elseif (!$hasText): ?><span>File only</span><?php endif; ?>
              <span><?= $m['updated_by_name'] ? 'Updated by ' . htmlspecialchars($m['updated_by_name']) . ' · ' . $m['changed_on'] : 'Starter lesson' ?></span>
            </div>
          </div>
          <div class="mx-btns">
            <button class="btn btn-reset-form btn-sm" onclick="mxPreview(<?= (int) $m['id'] ?>)">Preview</button>
            <button class="btn btn-edit btn-sm" onclick="mxOpenEditor(<?= (int) $m['id'] ?>)">Edit</button>
            <button class="btn btn-reset-form btn-sm mx-icon" title="Move up" aria-label="Move up" <?= $i === 0 ? 'disabled' : '' ?> onclick="mxMove(<?= (int) $m['id'] ?>, 'up')">&uarr;</button>
            <button class="btn btn-reset-form btn-sm mx-icon" title="Move down" aria-label="Move down" <?= $i === count($modules) - 1 ? 'disabled' : '' ?> onclick="mxMove(<?= (int) $m['id'] ?>, 'down')">&darr;</button>
            <button class="btn btn-danger btn-sm mx-icon" title="Delete" aria-label="Delete module" onclick="mxDelete(<?= (int) $m['id'] ?>)"><?= a_icon('trash') ?></button>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

<?php endif; ?>
</div>

<?php if ($view === 'detail'): ?>
<div class="modal-overlay" id="editorModal">
  <div class="modal" style="max-width:760px;max-height:90vh;overflow-y:auto;">
    <div class="modal-title" id="ed-title">Add module</div>
    <input type="hidden" id="ed-id">
    <div class="form-error" id="ed-error"></div>
    <div class="form-group" style="margin-bottom:.9rem;">
      <label class="form-label" for="ed-name">Title *</label>
      <input class="form-input" id="ed-name" maxlength="200" placeholder="e.g. Functions and Their Graphs">
    </div>
    <div class="form-group" style="margin-bottom:.9rem;">
      <label class="form-label" for="ed-desc">Short description <span style="color:var(--ui-text-3, #aaa);font-weight:400;">(shown in the module list)</span></label>
      <input class="form-input" id="ed-desc" maxlength="500" placeholder="One sentence on what students will learn">
    </div>
    <label class="form-label">Lesson *</label>
    <div class="mx-toolbar" role="toolbar" aria-label="Formatting">
      <button type="button" onclick="mxFmt('formatBlock','H3')">Heading</button>
      <button type="button" onclick="mxFmt('formatBlock','P')">Text</button>
      <button type="button" onclick="mxFmt('bold')"><strong>B</strong></button>
      <button type="button" onclick="mxFmt('italic')"><em>I</em></button>
      <button type="button" onclick="mxFmt('insertUnorderedList')">&bull; List</button>
      <button type="button" onclick="mxFmt('insertOrderedList')">1. List</button>
    </div>
    <div class="mx-editor lesson" id="ed-content" contenteditable="true" aria-label="Lesson content"></div>
    <div class="form-group" style="margin-top:.9rem;">
      <label class="form-label" for="ed-file">Attachment <span style="color:var(--ui-text-3, #aaa);font-weight:400;">(optional — PDF, JPG, or PNG only; up to 200 MB)</span></label>
      <input class="form-input" type="file" id="ed-file" accept=".pdf,.jpg,.jpeg,.png">
      <div id="ed-current-file" style="display:none;margin-top:.5rem;font-size:.82rem;">
        Current file: <a id="ed-file-link" class="mx-file" target="_blank" rel="noopener">View</a>
        <label style="margin-left:.75rem;"><input type="checkbox" id="ed-remove-file"> Remove file</label>
      </div>
    </div>
    <div class="modal-actions">
      <button class="btn btn-reset-form" onclick="closeModal('editorModal')">Cancel</button>
      <button class="btn btn-primary" onclick="mxSave(this)">Save module</button>
    </div>
  </div>
</div>

<div class="modal-overlay" id="previewModal">
  <div class="modal" style="max-width:760px;max-height:90vh;overflow-y:auto;">
    <div style="font-size:.78rem;font-weight:700;color:var(--text-secondary);" id="pv-kicker"></div>
    <div class="modal-title" id="pv-title" style="margin-bottom:.75rem;"></div>
    <div class="lesson" id="pv-body"></div>
    <div id="pv-file" style="margin-top:.75rem;"></div>
    <div class="modal-actions">
      <button class="btn btn-primary" onclick="closeModal('previewModal')">Close</button>
    </div>
  </div>
</div>

<script>
  const MX_COURSE = <?= (int) $courseId ?>;
  const MX_COURSE_NAME = <?= json_encode($course['course_name'], JSON_HEX_TAG | JSON_HEX_AMP) ?>;
  const MX_MODULES = <?= json_encode(array_column(array_map(fn($m) => [
    'id' => (int) $m['id'], 'num' => (int) $m['module_number'], 'title' => $m['title'],
    'description' => $m['description'] ?? '', 'content' => $m['content'] ?? '', 'file' => $m['file_path'] ?? '',
  ], $modules), null, 'id'), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;

  function mxErr(msg) { const e = document.getElementById('ed-error'); e.textContent = msg; e.style.display = msg ? 'block' : 'none'; }
  function mxFmt(cmd, val) { document.getElementById('ed-content').focus(); document.execCommand(cmd, false, val || null); }

  function mxOpenEditor(id) {
    const m = id ? MX_MODULES[id] : null;
    mxErr('');
    document.getElementById('ed-title').textContent = m ? `Edit Module ${m.num}` : 'Add module';
    document.getElementById('ed-id').value = m ? m.id : '';
    document.getElementById('ed-name').value = m ? m.title : '';
    document.getElementById('ed-desc').value = m ? m.description : '';
    document.getElementById('ed-content').innerHTML = m ? m.content : '<p></p>';
    document.getElementById('ed-file').value = '';
    document.getElementById('ed-remove-file').checked = false;
    const cur = document.getElementById('ed-current-file');
    if (m && m.file) { cur.style.display = ''; document.getElementById('ed-file-link').href = '../' + m.file; }
    else cur.style.display = 'none';
    document.getElementById('editorModal').classList.add('show');
    document.getElementById('ed-name').focus();
  }

  async function mxSave(btn) {
    const fd = new FormData();
    fd.append('id', document.getElementById('ed-id').value || '0');
    fd.append('course_id', MX_COURSE);
    fd.append('title', document.getElementById('ed-name').value.trim());
    fd.append('description', document.getElementById('ed-desc').value.trim());
    fd.append('content', document.getElementById('ed-content').innerHTML);
    const file = document.getElementById('ed-file').files[0];
    if (file) fd.append('file', file);
    if (document.getElementById('ed-remove-file').checked) fd.append('remove_file', '1');
    if (!fd.get('title')) return mxErr('Enter a module title.');
    const hasText = document.getElementById('ed-content').innerText.trim() !== '';
    const keepsFile = document.getElementById('ed-current-file').style.display !== 'none' && !document.getElementById('ed-remove-file').checked;
    if (!hasText && !file && !keepsFile) return mxErr('Add lesson content or attach a file — students need something to read.');
    setBtnLoading(btn, 'Saving...');
    try {
      const res = await fetch('../api/admin_modules.php?action=save', { method: 'POST', body: fd });
      const d = await res.json();
      if (!d.success) { clearBtnLoading(btn); return mxErr(d.message); }
      showToast(d.message);
      setTimeout(() => location.reload(), 600);
    } catch (e) { clearBtnLoading(btn); mxErr('Network error. Try again.'); }
  }

  function mxPreview(id) {
    const m = MX_MODULES[id];
    document.getElementById('pv-kicker').textContent = `${MX_COURSE_NAME} · Module ${m.num}`;
    document.getElementById('pv-title').textContent = m.title;
    document.getElementById('pv-body').innerHTML = m.content || '<p style="color:var(--text-muted)">No lesson text — this module uses the attached file.</p>';
    document.getElementById('pv-file').innerHTML = m.file ? `<a class="mx-file" target="_blank" rel="noopener" href="../${encodeURI(m.file)}">Open attached file</a>` : '';
    document.getElementById('previewModal').classList.add('show');
  }

  async function mxPost(action, data) {
    const fd = new FormData();
    Object.entries(data).forEach(([k, v]) => fd.append(k, v));
    const res = await fetch('../api/admin_modules.php?action=' + action, { method: 'POST', body: fd });
    return res.json();
  }
  async function mxMove(id, dir) {
    try {
      const d = await mxPost('move', { id, dir });
      d.success ? location.reload() : showToast(d.message, true);
    } catch (e) { showToast('Network error. Try again.', true); }
  }
  async function mxDelete(id) {
    const m = MX_MODULES[id];
    if (!await confirmAction(`Delete Module ${m.num}: "${m.title}"? Students' progress on this module will also be removed.`, { okText: 'Delete module' })) return;
    try {
      const d = await mxPost('delete', { id });
      if (!d.success) return showToast(d.message, true);
      showToast(d.message);
      setTimeout(() => location.reload(), 600);
    } catch (e) { showToast('Network error. Try again.', true); }
  }
</script>
<?php else: ?>
<style>
  .mx-item.is-new .mx-card { border-color: var(--success); box-shadow: 0 0 0 3px var(--success-light); }
</style>
<script>
  // Just added a subject (api/admin_subjects.php): show it on the list instead of opening it
  (function () {
    const added = new URLSearchParams(location.search).get('added');
    if (!added) return;
    try { history.replaceState(null, '', 'modules.php'); } catch (e) { }   // a refresh won't highlight it again
    const card = document.querySelector('.mx-item[data-course="' + parseInt(added, 10) + '"]');
    if (!card) return;
    card.classList.add('is-new');
    card.scrollIntoView({ block: 'center' });
    setTimeout(() => card.classList.remove('is-new'), 6000);
  })();
  function mxFilter() {
    const q = document.getElementById('mx-filter').value.toLowerCase().trim();
    document.querySelectorAll('[data-grid]').forEach(grid => {
      let shown = 0;
      grid.querySelectorAll('.mx-item').forEach(c => { const m = c.dataset.text.includes(q); c.style.display = m ? '' : 'none'; if (m) shown++; });
      grid.style.display = shown ? '' : 'none';
      grid.previousElementSibling.style.display = shown ? '' : 'none';
    });
  }
</script>
<?php endif; ?>

<?php if ($canSubjects):
  // Subjects the form can edit on this page (grid: all listed; detail: this one)
  $sjData = [];
  if ($view === 'grid') { foreach ($groups as $g) foreach ($g['items'] as $c) $sjData[(int) $c['id']] = $c; }
  else $sjData[(int) $course['id']] = $course;
  $sjData = array_map(fn($c) => ['id' => (int) $c['id'], 'code' => $c['course_code'], 'name' => $c['course_name'],
                                 'description' => (string) ($c['description'] ?? ''), 'school_year' => $c['school_year'], 'semester' => $c['semester']], $sjData);
  // Every subject code already in use (all subjects, active or not) -> the code it belongs to
  $sjCodes = [];
  $r = $conn->query("SELECT id, course_code, course_name FROM courses");
  while ($r && $row = $r->fetch_assoc()) $sjCodes[strtoupper($row['course_code'])] = ['id' => (int) $row['id'], 'name' => $row['course_name']];
?>
<div class="modal-overlay" id="subjectModal" role="dialog" aria-modal="true" aria-labelledby="sj-title">
  <div class="modal" style="max-width:600px;">
    <div class="modal-title" id="sj-title">Add subject</div>
    <input type="hidden" id="sj-id" value="0">
    <div class="form-error" id="sj-error"></div>
    <div class="form-group" style="margin-bottom:.9rem;">
      <label class="form-label" for="sj-name">Subject name *</label>
      <input class="form-input" id="sj-name" maxlength="150" placeholder="e.g. Information and Communications Technology" oninput="sjAutoCode()">
    </div>
    <div class="sj-code-row" style="margin-bottom:.35rem;">
      <div class="form-group">
        <label class="form-label" for="sj-group">Listed under *</label>
        <select class="form-input" id="sj-group" onchange="sjGroupChanged()">
          <?php foreach ($codeGroups as [$pfx, $label]): ?>
            <option value="<?= htmlspecialchars($pfx) ?>"><?= htmlspecialchars($label) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label" for="sj-code">Subject code <span style="color:var(--ui-text-3, #aaa);font-weight:400;">(automatic)</span></label>
        <div class="sj-code-wrap"><span class="sj-prefix" id="sj-prefix"></span><input class="form-input" id="sj-code" maxlength="20" placeholder="Fills in from the name" readonly tabindex="-1" aria-readonly="true" spellcheck="false" style="flex:1;min-width:0;"></div>
      </div>
    </div>
    <div class="sj-hint" id="sj-code-hint" style="margin-bottom:.9rem;"></div>
    <div class="form-group" style="margin-bottom:.9rem;">
      <label class="form-label" for="sj-desc">Description <span style="color:var(--ui-text-3, #aaa);font-weight:400;">(optional)</span></label>
      <textarea class="form-input" id="sj-desc" rows="3" maxlength="2000" placeholder="What students learn in this subject"></textarea>
    </div>
    <div class="sj-term" id="sj-term"><strong><?= elmsSetSpan('term_label') ?></strong> &nbsp;·&nbsp; set automatically from Settings</div>
    <div class="sj-hint" id="sj-edit-note" style="display:none;">Changes show right away on every page that lists this subject — Subjects, Sections, and the teachers' and students' pages.</div>
    <div class="modal-actions">
      <button type="button" class="btn btn-danger" id="sj-delete" onclick="sjDelete(Number(document.getElementById('sj-id').value), this)" style="margin-right:auto;display:none;"><?= a_icon('trash') ?> Delete</button>
      <button type="button" class="btn btn-reset-form" onclick="closeModal('subjectModal')">Cancel</button>
      <button type="button" class="btn btn-primary" id="sj-save" onclick="sjSave(this)">Save subject</button>
    </div>
  </div>
</div>
<script>
  const SJ_SUBJECTS = <?= json_encode($sjData, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
  const SJ_GROUPS = <?= json_encode(array_values(array_filter(array_column($codeGroups, 0))), JSON_HEX_TAG) ?>;
  const SJ_CODES = <?= json_encode((object) $sjCodes, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;   // every code in use -> {id, name}
  // The code cannot be typed: it is made from the subject name and "Listed under".
  // Add: filled in as the name is typed. Edit: kept as it is until the name or group changes.
  const SJ_GROUP_LABEL = <?= json_encode(array_column($codeGroups, 1, 0), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;

  function sjErr(msg) { const e = document.getElementById('sj-error'); e.textContent = msg; e.style.display = msg ? 'block' : 'none'; }
  function sjFullCode() {
    const pfx = document.getElementById('sj-group').value;
    let rest = document.getElementById('sj-code').value.toUpperCase().replace(/\s+/g, '');
    if (pfx && rest.startsWith(pfx)) rest = rest.slice(pfx.length);   // typed the prefix too
    return pfx + rest;
  }
  /** The subject that already uses this code (not the one being edited), or null. */
  function sjCodeOwner(full) {
    const o = SJ_CODES[full];
    const id = Number(document.getElementById('sj-id').value) || 0;
    return o && o.id !== id ? o : null;
  }
  function sjCodeChanged() {
    const pfx = document.getElementById('sj-group').value;
    document.getElementById('sj-prefix').textContent = pfx;
    const full = sjFullCode();
    const hint = document.getElementById('sj-code-hint');
    const owner = (full && full !== pfx) ? sjCodeOwner(full) : null;
    hint.classList.toggle('warn', !!owner);
    if (owner) hint.textContent = `${full} is already used by "${owner.name}". Use a different code.`;
    else if (full && full !== pfx) hint.textContent = `Full code: ${full} · listed under ${SJ_GROUP_LABEL[pfx] || 'Other subjects'} · made from the subject name`;
    else hint.textContent = 'Type the subject name and the code fills in by itself.';
  }

  // ── Automatic subject code (Add subject) ──────────────────
  // Same style as the existing codes: HS7-FIL, HS8-MATH, STEM-BIO1, ABM-FABM2, SHS-MIL.
  const SJ_KNOWN = {
    'filipino': 'FIL', 'english': 'ENGLISH', 'mathematics': 'MATH', 'math': 'MATH', 'science': 'SCI',
    'araling panlipunan': 'AP', 'esp': 'ESP', 'edukasyon sa pagpapakatao': 'ESP', 'mapeh': 'MAPEH',
    'tle': 'TLE', 'technology and livelihood education': 'TLE', 'ict': 'ICT',
    'information and communications technology': 'ICT', 'information and communication technology': 'ICT',
    'physical education': 'PE', 'physical education and health': 'PEH',
  };
  const SJ_SKIP = new Set(['and', 'of', 'the', 'in', 'for', 'to', 'a', 'an', 'on', 'with', 'at', 'sa', 'ng', 'mga']);
  /** "Information and Communications Technology" -> "ICT", "Filipino 7" (Grade 7) -> "FIL", "General Biology 1" -> "GB1" */
  function sjAbbrev(name, pfx) {
    const clean = name.normalize('NFD').replace(/[\u0300-\u036f]/g, '').replace(/&/g, ' and ').toLowerCase();
    let words = clean.split(/[^a-z0-9]+/).filter(Boolean);
    if (!/[a-z]/.test(clean)) return '';   // no letters yet (empty, or only a number): nothing to make a code from
    // a number at the end: the grade itself (Filipino 7 under Grade 7) is dropped, any other number is kept (Biology 1)
    let num = '';
    if (words.length > 1 && /^\d+$/.test(words[words.length - 1])) {
      num = words.pop();
      const g = pfx.match(/^HS(\d+)-$/);
      if (g && Number(g[1]) === Number(num)) num = '';
    }
    if (!words.length) return num;
    const key = words.join(' ');
    let abbr;
    if (SJ_KNOWN[key]) abbr = SJ_KNOWN[key];
    else {
      const main = words.filter(w => !SJ_SKIP.has(w));
      const use = main.length ? main : words;
      if (use.length >= 2) abbr = use.map(w => w[0]).join('').slice(0, 8);
      else abbr = use[0].length <= 7 ? use[0] : use[0].slice(0, 4);
    }
    return (abbr + num).toUpperCase().replace(/[^A-Z0-9]/g, '');
  }
  /** The first free code: HS7-ICT, then HS7-ICT2, HS7-ICT3 ... (20 characters at most) */
  function sjFreeCode(pfx, abbr) {
    if (!abbr) return '';
    const room = 20 - pfx.length;
    if (room < 1) return '';
    let base = abbr.slice(0, room);
    if (!sjCodeOwner(pfx + base)) return base;
    for (let n = 2; n < 1000; n++) {
      const tail = (/\d$/.test(abbr) ? '-' : '') + n;   // BIO1 -> BIO1-2, not BIO12
      const b = abbr.slice(0, Math.max(1, room - tail.length)) + tail;
      if (!sjCodeOwner(pfx + b)) return b;
    }
    return base;   // practically never: the server still refuses a duplicate
  }
  function sjAutoCode() {
    const pfx = document.getElementById('sj-group').value;
    document.getElementById('sj-code').value = sjFreeCode(pfx, sjAbbrev(document.getElementById('sj-name').value, pfx));
    sjCodeChanged();
  }
  function sjGroupChanged() {
    sjAutoCode();
  }
  function sjOpen(id) {
    const s = id ? SJ_SUBJECTS[id] : null;
    if (id && !s) { showToast('That subject is not on this page any more. Refresh the page.', true); return; }
    sjErr('');
    document.getElementById('sj-title').textContent = s ? 'Edit subject' : 'Add subject';
    document.getElementById('sj-id').value = s ? s.id : 0;
    document.getElementById('sj-name').value = s ? s.name : '';
    document.getElementById('sj-desc').value = s ? s.description : '';
    document.getElementById('sj-term').style.display = s ? 'none' : '';   // new subjects: school year from Settings
    // split the code into its group + the rest
    let pfx = '', rest = s ? s.code : '';
    if (s) { for (const g of SJ_GROUPS) if (s.code.startsWith(g) && g.length > pfx.length) pfx = g; rest = s.code.slice(pfx.length); }
    else { const first = document.getElementById('sj-group').options[0]; pfx = first ? first.value : ''; }
    document.getElementById('sj-group').value = pfx;
    document.getElementById('sj-code').value = rest;
    document.getElementById('sj-edit-note').style.display = s ? '' : 'none';
    document.getElementById('sj-delete').style.display = s ? '' : 'none';
    sjCodeChanged();
    document.getElementById('subjectModal').classList.add('show');
    setTimeout(() => document.getElementById('sj-name').focus(), 30);
  }
  async function sjSave(btn) {
    if (btn.disabled) return;   // no double save
    const id = Number(document.getElementById('sj-id').value) || 0;
    const name = document.getElementById('sj-name').value.trim();
    const code = sjFullCode();
    const pfx = document.getElementById('sj-group').value;
    if (!name) return sjErr('Enter the subject name.');
    if (!code || code === pfx) return sjErr('The subject name needs at least one letter so the code can be made.');
    if (code.length > 20 || !/^[A-Z0-9]+(-[A-Z0-9]+)*$/.test(code)) return sjErr('Use letters, numbers and dashes only in the code (up to 20 characters), e.g. HS7-ICT.');
    const owner = sjCodeOwner(code);
    if (owner) return sjErr(`The code ${code} is already used by "${owner.name}". Use a different code.`);
    const fd = new FormData();
    fd.append('id', id);
    fd.append('course_code', code);
    fd.append('course_name', name);
    fd.append('description', document.getElementById('sj-desc').value.trim());
    fd.append('code_auto', '1');   // made from the name: if someone took it meanwhile, the server moves it to the next free number
    setBtnLoading(btn, 'Saving...');
    try {
      const res = await fetch('../api/admin_subjects.php?action=save', { method: 'POST', body: fd });
      let d; try { d = await res.json(); } catch (e) { d = { success: false, message: 'Server error. Please try again.' }; }
      if (!d.success) { clearBtnLoading(btn); return sjErr(d.message || 'Could not save the subject.'); }
      closeModal('subjectModal');
      showToast(d.message);
      // Stay on this page: new subject → back on the subject list with the new card
      // highlighted; edit → the same page again with the new name/code.
      setTimeout(() => { if (!id && d.id) location.href = 'modules.php?added=' + d.id; else location.reload(); }, 700);
    } catch (e) { clearBtnLoading(btn); sjErr('Network error. Check your connection and try again.'); }
  }
  // Delete: first ask the server whether anything still uses the subject
  async function sjDelete(id, btn) {
    if (!id || (btn && btn.disabled)) return;
    const post = async (action) => {
      const fd = new FormData(); fd.append('id', id);
      const res = await fetch('../api/admin_subjects.php?action=' + action, { method: 'POST', body: fd });
      try { return await res.json(); } catch (e) { return { success: false, message: 'Server error. Please try again.' }; }
    };
    let u;
    try { u = await post('usage'); } catch (e) { return showToast('Network error. Try again.', true); }
    if (!u.success) return showToast(u.message || 'Could not check the subject.', true);
    if (!u.can_delete) {
      closeModal('subjectModal');
      await confirmAction(u.reason, { title: `Can't delete ${u.name}`, okText: 'OK', hideCancel: true, danger: false });
      return;
    }
    closeModal('subjectModal');
    const extra = u.modules ? ` Its ${u.modules} module${u.modules === 1 ? '' : 's'} and attached files will be deleted too.` : '';
    if (!await confirmAction(`Delete "${u.name}" (${u.code})?${extra} This cannot be undone.`, { title: 'Delete subject', okText: 'Delete subject' })) return;
    if (btn) setBtnLoading(btn, 'Deleting...');
    try {
      const d = await post('delete');
      if (btn) clearBtnLoading(btn);
      if (!d.success) return showToast(d.message || 'Could not delete the subject.', true);
      showToast(d.message);
      setTimeout(() => { location.href = 'modules.php'; }, 700);
    } catch (e) { if (btn) clearBtnLoading(btn); showToast('Network error. Try again.', true); }
  }
  document.getElementById('subjectModal').addEventListener('keydown', e => {
    if (e.key === 'Enter' && e.target.tagName === 'INPUT') { e.preventDefault(); sjSave(document.getElementById('sj-save')); }
  });
</script>
<?php endif; ?>

<?php require __DIR__ . '/../includes/footer.php'; ?>