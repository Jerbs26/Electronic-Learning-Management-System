<?php
// ============================================================
//  Arandia College eLMS — Teacher: Modules
//  File: teacher/modules.php
// ============================================================
require __DIR__ . '/_init.php';
$currentPage = 'modules.php';
$pageTitle = 'Modules';

require __DIR__ . '/../includes/header.php';
?>

<?php
// ── Modules are written by the admin; teachers read them and follow their students' progress ──
$tmCourses = [];   // course_id => [label, sections[]]
foreach ($mySubjects as $s) {
    $cid = (int) $s['course_id'];
    if (!isset($tmCourses[$cid])) $tmCourses[$cid] = ['code' => $s['course_code'], 'name' => $s['course_name'], 'sections' => []];
    $tmCourses[$cid]['sections'][] = $s['section'];
}
$tmCid = (int) ($_GET['course'] ?? 0);
if (!isset($tmCourses[$tmCid])) $tmCid = $tmCourses ? array_key_first($tmCourses) : 0;
// Opened from a class (My Classes → Modules → Read): remember it for the back link
$tmFrom = null;
$tmFromId = (int) ($_GET['class'] ?? 0);
foreach ($mySubjects as $s) if ((int) $s['assign_id'] === $tmFromId && (int) $s['course_id'] === $tmCid) { $tmFrom = $s; break; }
$tmOpen = (int) ($_GET['module'] ?? 0);   // open this module's reader on load

$tmModules = $tmStudents = [];
$tmHasNum = (bool) $conn->query("SHOW COLUMNS FROM modules LIKE 'module_number'")->num_rows;
$tmHasViews = (bool) $conn->query("SHOW TABLES LIKE 'module_views'")->num_rows;
if ($tmCid) {
    $order = $tmHasNum ? 'module_number' : 'week_number, created_at';
    $numCol = $tmHasNum ? 'module_number' : 'NULL';
    $st = $conn->prepare("SELECT id, $numCol AS module_number, title, description, " . ($tmHasNum ? 'content' : "'' AS content") . ", file_path
                          FROM modules WHERE course_id = ? AND published = 1 ORDER BY $order, id");
    $st->bind_param('i', $tmCid);
    $st->execute();
    $res = $st->get_result();
    while ($r = $res->fetch_assoc()) { $r['opened'] = 0; $r['done'] = 0; $tmModules[(int) $r['id']] = $r; }
    $st->close();

    // Only this teacher's students: enrolled in the subject AND in one of the teacher's sections for it
    $secs = array_values(array_unique($tmCourses[$tmCid]['sections']));
    $ph = implode(',', array_fill(0, count($secs), '?'));
    $st = $conn->prepare("SELECT u.id, u.first_name, u.last_name, u.section_dept
                          FROM users u JOIN enrollments e ON e.student_id = u.id AND e.course_id = ? AND e.status = 'Enrolled'
                          WHERE u.role = 'Student' AND u.status = 'Active' AND u.section_dept IN ($ph)
                          ORDER BY u.section_dept, u.last_name, u.first_name");
    $st->bind_param('i' . str_repeat('s', count($secs)), $tmCid, ...$secs);
    $st->execute();
    $res = $st->get_result();
    while ($r = $res->fetch_assoc()) { $r['opened'] = 0; $r['done'] = 0; $tmStudents[(int) $r['id']] = $r; }
    $st->close();

    if ($tmModules && $tmStudents) {
        $mIds = implode(',', array_keys($tmModules));
        $sIds = implode(',', array_keys($tmStudents));
        $sets = ['done' => "SELECT student_id, module_id FROM module_progress WHERE module_id IN ($mIds) AND student_id IN ($sIds)"];
        if ($tmHasViews) $sets['opened'] = "SELECT student_id, module_id FROM module_views WHERE module_id IN ($mIds) AND student_id IN ($sIds)";
        foreach ($sets as $key => $sql) {
            $res = $conn->query($sql);
            while ($res && $r = $res->fetch_assoc()) {
                $tmModules[(int) $r['module_id']][$key]++;
                $tmStudents[(int) $r['student_id']][$key]++;
            }
        }
    }
}
$tmTotal = count($tmModules);
?>
<style>
    .tm-back { display: inline-block; font-size: .84rem; font-weight: 700; color: var(--text-light); text-decoration: none; margin-bottom: .5rem; }
    .tm-back:hover { color: var(--primary, #003087); }
    .tm-bar { display: flex; gap: .75rem; align-items: flex-end; flex-wrap: wrap; margin-bottom: 1.1rem; }
    .tm-note { font-size: .82rem; color: var(--ui-text-2, #64748b); margin-bottom: 1rem; }
    .tm-list { display: flex; flex-direction: column; gap: .55rem; margin-bottom: 1.5rem; }
    .tm-row { display: grid; grid-template-columns: 40px minmax(0, 1fr) auto; gap: .9rem; align-items: center; background: var(--white);
              border-radius: 14px; padding: .85rem 1.1rem; box-shadow: 0 2px 12px rgba(0, 0, 0, .06); }
    .tm-num { width: 36px; height: 36px; border-radius: 50%; background: #e8f0ff; color: #003087; font-weight: 800; display: flex; align-items: center; justify-content: center; }
    .tm-title { font-weight: 700; color: var(--text-dark); font-size: .9rem; }
    .tm-desc { font-size: .78rem; color: var(--ui-text-3, #94a3b8); margin-top: .1rem; }
    .tm-stats { font-size: .74rem; color: var(--ui-text-2, #64748b); margin-top: .3rem; }
    .tm-card { background: var(--white); border-radius: 16px; box-shadow: 0 2px 14px rgba(0, 0, 0, .07); overflow: hidden; }
    .tm-card h3 { font-family: 'Nunito', sans-serif; font-size: .95rem; font-weight: 800; color: var(--text-dark); padding: 1rem 1.4rem; border-bottom: 1px solid var(--border, #f0f2f5); margin: 0; }
    .tm-card table { width: 100%; border-collapse: collapse; font-size: .85rem; }
    .tm-card th { text-align: left; font-size: .68rem; font-weight: 800; letter-spacing: .06em; text-transform: uppercase; color: #94a3b8; background: var(--bg-light); padding: .65rem 1.4rem; }
    .tm-card td { text-align: left; padding: .75rem 1.4rem; border-top: 1px solid var(--border, #f0f2f5); color: var(--text-dark); }
    .tm-meter { background: var(--ui-surface-3, #f0f2f5); border-radius: 100px; height: 8px; overflow: hidden; min-width: 120px; }
    .tm-meter div { height: 8px; background: #003087; border-radius: 100px; }
    .tm-overlay { position: fixed; inset: 0; background: rgba(15, 23, 42, .55); z-index: 600; display: none; align-items: center; justify-content: center; padding: 1rem; }
    .tm-overlay.show { display: flex; }
    .tm-reader { background: var(--white); width: 100%; max-width: 760px; max-height: 90vh; border-radius: 16px; display: flex; flex-direction: column; overflow: hidden; }
    .tm-reader-head { padding: 1.1rem 1.4rem; border-bottom: 1px solid #eef1f5; display: flex; justify-content: space-between; gap: 1rem; }
    .tm-reader-body { padding: 1.25rem 1.4rem; overflow-y: auto; }
    .lesson { color: var(--text-dark); line-height: 1.75; font-size: .95rem; }
    .lesson h3 { font-size: 1rem; font-weight: 800; margin: 1.1rem 0 .4rem; }
    .lesson p { margin: 0 0 .75rem; }
    .lesson ul, .lesson ol { margin: 0 0 .85rem 1.3rem; padding: 0; }
    .lesson li { margin-bottom: .3rem; }
    .lesson table { border-collapse: collapse; margin: .4rem 0 .9rem; font-size: .88rem; max-width: 100%; display: block; overflow-x: auto; }
    .lesson th, .lesson td { border: 1px solid var(--ui-border, #e2e8f0); padding: .4rem .75rem; text-align: left; }
    .lesson th { background: var(--ui-surface-2, #f8fafc); font-weight: 700; }
    .lesson blockquote { background: var(--ui-surface-2, #f8fafc); border-left: 3px solid var(--primary); border-radius: 0 8px 8px 0; margin: .5rem 0 .9rem; padding: .6rem 1rem; color: inherit; }
    .lesson blockquote p:last-child { margin-bottom: 0; }
    .lesson details { background: var(--ui-surface-3, #f1f5f9); border-radius: 10px; padding: .6rem 1rem; margin: .5rem 0 .9rem; }
    .lesson summary { cursor: pointer; font-weight: 700; color: var(--primary); }
    .lesson details[open] summary { margin-bottom: .5rem; }
</style>

<div class="section-panel active">
    <?php if ($tmFrom): ?>
        <a class="tm-back" href="classes.php?class=<?= (int) $tmFrom['assign_id'] ?>&tab=modules">← <?= htmlspecialchars($tmFrom['course_name'] . ' · ' . $tmFrom['section']) ?></a>
    <?php else: ?>
        <a class="tm-back" href="classes.php">← My Classes</a>
    <?php endif; ?>
    <div class="panel-header">
        <div class="page-title">Modules</div>
    </div>
    <?php if (!$tmCourses): ?>
        <div class="card-panel"><div class="card-panel-body">
            <div class="empty-state"><span class="empty-icon"><?= t_icon('book') ?></span>No subjects assigned yet.</div>
        </div></div>
    <?php else: ?>
        <div class="tm-bar">
            <div class="form-grp" style="min-width:280px;flex:1;max-width:520px;">
                <label class="form-lbl" for="tm-course">Subject</label>
                <select class="finput" id="tm-course" onchange="location.href='modules.php?course=' + this.value">
                    <?php foreach ($tmCourses as $cid => $c): ?>
                        <option value="<?= $cid ?>" <?= $cid === $tmCid ? 'selected' : '' ?>><?= htmlspecialchars($c['name'] . ' (' . implode(', ', array_unique($c['sections'])) . ')') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <?php if (!$tmModules): ?>
            <div class="card-panel"><div class="card-panel-body">
                <div class="empty-state"><span class="empty-icon"><?= t_icon('folder') ?></span>No modules for this subject yet. The admin will add them.</div>
            </div></div>
        <?php else: ?>
            <div class="tm-list">
                <?php foreach ($tmModules as $m): ?>
                    <div class="tm-row">
                        <div class="tm-num"><?= $m['module_number'] ? (int) $m['module_number'] : '•' ?></div>
                        <div style="min-width:0;">
                            <div class="tm-title"><?= htmlspecialchars($m['title']) ?></div>
                            <?php if ($m['description']): ?><div class="tm-desc"><?= htmlspecialchars($m['description']) ?></div><?php endif; ?>
                            <div class="tm-stats">Your students: opened by <?= $m['opened'] ?> · done by <?= $m['done'] ?> of <?= count($tmStudents) ?></div>
                        </div>
                        <button class="btn btn-blue btn-sm" onclick="tmRead(<?= (int) $m['id'] ?>)">Read</button>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="tm-card">
                <h3>Student progress</h3>
                <?php if (!$tmStudents): ?>
                    <div style="padding:2rem;text-align:center;color:var(--ui-text-3, #94a3b8);">No students from your sections are enrolled in this subject yet.</div>
                <?php else: ?>
                    <div style="overflow-x:auto;">
                        <table width="100%">
                            <thead><tr><th>Student</th><th>Section</th><th>Opened</th><th>Done</th><th>Progress</th></tr></thead>
                            <tbody>
                                <?php foreach ($tmStudents as $s): $pct = $tmTotal ? round($s['done'] / $tmTotal * 100) : 0; ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($s['last_name'] . ', ' . $s['first_name']) ?></strong></td>
                                        <td><?= htmlspecialchars($s['section_dept'] ?? '—') ?></td>
                                        <td><?= $s['opened'] ?> / <?= $tmTotal ?></td>
                                        <td><?= $s['done'] ?> / <?= $tmTotal ?></td>
                                        <td><div class="tm-meter"><div style="width:<?= $pct ?>%"></div></div>
                                            <span style="font-size:.72rem;font-weight:700;color:var(--ui-link, #003087)"><?= $pct ?>%</span></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<div class="tm-overlay" id="tm-reader" role="dialog" aria-modal="true" aria-labelledby="tm-title" onclick="if (event.target === this) this.classList.remove('show')">
    <div class="tm-reader">
        <div class="tm-reader-head">
            <div>
                <div style="font-size:.75rem;font-weight:700;color:var(--ui-link, #003087);" id="tm-kicker"></div>
                <div style="font-size:1.15rem;font-weight:800;color:var(--ui-text, #0f172a);" id="tm-title"></div>
            </div>
            <button class="btn btn-gray btn-sm" id="tm-close" onclick="document.getElementById('tm-reader').classList.remove('show')">Close</button>
        </div>
        <div class="tm-reader-body">
            <div class="lesson" id="tm-content"></div>
            <div id="tm-file" style="margin-top:1rem;"></div>
        </div>
    </div>
</div>
<script>
    const TM_MODULES = <?= json_encode(array_map(fn($m) => [
        'num' => $m['module_number'] ? (int) $m['module_number'] : null, 'title' => $m['title'],
        'description' => $m['description'] ?? '', 'content' => $m['content'] ?? '', 'file' => $m['file_path'] ?? '',
    ], $tmModules), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    const TM_COURSE = <?= json_encode($tmCid ? $tmCourses[$tmCid]['name'] : '', JSON_HEX_TAG | JSON_HEX_AMP) ?>;
    function tmRead(id) {
        const m = TM_MODULES[id];
        if (!m) return;
        const esc = s => { const d = document.createElement('div'); d.textContent = s ?? ''; return d.innerHTML; };
        document.getElementById('tm-kicker').textContent = TM_COURSE + (m.num ? ` · Module ${m.num}` : '');
        document.getElementById('tm-title').textContent = m.title;
        document.getElementById('tm-content').innerHTML = m.content || (m.description ? `<p>${esc(m.description)}</p>` : '');
        document.getElementById('tm-file').innerHTML = m.file ? `<a class="btn btn-blue btn-sm" target="_blank" rel="noopener" href="../${encodeURI(m.file)}">Open attached file</a>` : '';
        document.getElementById('tm-reader').classList.add('show');
        document.getElementById('tm-close').focus();
    }
    <?php if ($tmOpen && isset($tmModules[$tmOpen])): ?>
    document.addEventListener('DOMContentLoaded', () => tmRead(<?= $tmOpen ?>));
    <?php endif; ?>
    document.addEventListener('keydown', e => { if (e.key === 'Escape') document.getElementById('tm-reader').classList.remove('show'); });
</script>
<?php require __DIR__ . '/../includes/footer.php'; ?>