<?php
// ============================================================
//  Arandia College eLMS — Teacher: All students
//  File: teacher/my-students.php[?search=NAME][&section=SECTION]
//  Every student the teacher handles, across all classes, in one
//  searchable list. Same rule as everywhere else on the teacher side:
//  enrolled in one of the teacher's subjects AND in a section the
//  teacher handles that subject for (built in _init.php).
//  Reached from: Dashboard "Students" card,
//  and the top-bar search (person results open here pre-filtered).
// ============================================================
require __DIR__ . '/_init.php';
$currentPage = 'my-students.php';   // sidebar highlights "My Classes"
$pageTitle = 'All students';

$hasPresence = (bool) $conn->query("SHOW COLUMNS FROM users LIKE 'last_seen_at'")->num_rows;
$seen = [];
if ($hasPresence && $studentMap) {
    $ids = implode(',', array_map('intval', array_keys($studentMap)));
    $res = $conn->query("SELECT id, last_seen_at FROM users WHERE id IN ($ids)");
    while ($res && $r = $res->fetch_assoc()) $seen[(int) $r['id']] = $r['last_seen_at'];
}
function msAgo(?string $dt): string
{
    if (!$dt) return 'Never';
    $s = time() - strtotime($dt);
    if ($s < 300) return 'Online';
    if ($s < 3600) return floor($s / 60) . ' min ago';
    if ($s < 86400) return floor($s / 3600) . ' hr' . (floor($s / 3600) == 1 ? '' : 's') . ' ago';
    if ($s < 604800) return floor($s / 86400) . ' day' . (floor($s / 86400) == 1 ? '' : 's') . ' ago';
    return date('M j', strtotime($dt));
}

$students = array_values($studentMap);
usort($students, fn($a, $b) => [$a['section_dept'], $a['last_name'], $a['first_name']] <=> [$b['section_dept'], $b['last_name'], $b['first_name']]);
$sections = array_keys($studentsBySection);
$q = trim((string) ($_GET['search'] ?? ''));
$secPick = trim((string) ($_GET['section'] ?? ''));
if (!in_array($secPick, $sections, true)) $secPick = '';

require __DIR__ . '/../includes/header.php';
?>
<style>
  .ms { --m-soft: #eef3ff; --m-strong: #003087; --m-line: rgba(15, 23, 42, .07); }
  html[data-theme="dark"] .ms { --m-soft: rgba(143, 176, 255, .12); --m-strong: #c7d7ff; --m-line: rgba(255, 255, 255, .07); }
  .ms-back { display: inline-block; font-size: .84rem; font-weight: 700; color: var(--text-light); text-decoration: none; margin-bottom: .5rem; }
  .ms-back:hover { color: var(--m-strong); }
  .ms-head { display: flex; justify-content: space-between; align-items: flex-end; gap: 1rem; flex-wrap: wrap; margin-bottom: 1.1rem; }
  .ms-title { font-family: 'Nunito', sans-serif; font-size: 1.6rem; font-weight: 900; color: var(--text-dark); line-height: 1.2; }
  .ms-sub { font-size: .88rem; color: var(--text-light); margin-top: .3rem; }
  .ms-bar { display: flex; gap: .6rem; flex-wrap: wrap; margin-bottom: 1rem; }
  .ms-bar input, .ms-bar select { height: 42px; border-radius: 10px; border: 1px solid var(--m-line); background: var(--white); color: var(--text-dark); font: inherit; font-size: .88rem; padding: 0 .9rem; }
  .ms-bar input { flex: 1; min-width: 220px; }
  .ms-bar input:focus, .ms-bar select:focus { outline: 2px solid var(--m-strong); outline-offset: 1px; }
  .ms-panel { background: var(--white); border-radius: 16px; box-shadow: 0 2px 14px rgba(0, 0, 0, .06); overflow: hidden; }
  .ms-panel table { width: 100%; border-collapse: collapse; font-size: .86rem; }
  .ms-panel th { text-align: left; font-size: .68rem; font-weight: 800; letter-spacing: .06em; text-transform: uppercase; color: var(--text-light); padding: .7rem 1.25rem; background: var(--bg-light); }
  .ms-panel td { padding: .8rem 1.25rem; border-top: 1px solid var(--m-line); color: var(--text-dark); vertical-align: middle; }
  .ms-av { width: 34px; height: 34px; border-radius: 50%; background: var(--m-soft); color: var(--m-strong); font-weight: 800; font-size: .74rem; display: inline-flex; align-items: center; justify-content: center; margin-right: .6rem; vertical-align: middle; flex-shrink: 0; }
  .ms-name { display: flex; align-items: center; font-weight: 700; }
  .ms-lrn { font-size: .74rem; color: var(--text-light); font-weight: 500; }
  .ms-chips { display: flex; flex-wrap: wrap; gap: .3rem; }
  .ms-chip { font-size: .74rem; font-weight: 700; padding: .2rem .6rem; border-radius: 100px; background: var(--m-soft); color: var(--m-strong); text-decoration: none; white-space: nowrap; }
  .ms-chip:hover { text-decoration: underline; }
  .ms-online { color: #00875a; font-weight: 700; }
  .ms-count { font-size: .8rem; color: var(--text-light); margin-bottom: .6rem; }
  .ms-empty { text-align: center; padding: 3rem 1rem; color: var(--text-light); }
  .ms-empty strong { display: block; color: var(--text-dark); margin-bottom: .25rem; }
  @media (max-width: 700px) {
    .ms-panel thead { display: none; }
    .ms-panel tr { display: block; padding: .8rem 1rem; border-top: 1px solid var(--m-line); }
    .ms-panel td { display: block; border: 0; padding: .2rem 0; }
    .ms-panel td[data-l]::before { content: attr(data-l) ' · '; font-size: .72rem; font-weight: 700; color: var(--text-light); }
  }
</style>

<div class="section-panel active ms">
  <a class="ms-back" href="classes.php">← My Classes</a>
  <div class="ms-head">
    <div>
      <div class="ms-title">All students</div>
    </div>
  </div>

  <?php if (!$students): ?>
    <div class="ms-panel"><div class="ms-empty"><strong>No students yet</strong>Students appear here once the admin enrolls them in a class you teach.</div></div>
  <?php else: ?>
    <div class="ms-bar" role="search">
      <label class="sr-only" for="ms-q">Search students</label>
      <input id="ms-q" type="search" placeholder="Search by name or LRN" value="<?= htmlspecialchars($q) ?>" autocomplete="off">
      <label class="sr-only" for="ms-sec">Section</label>
      <select id="ms-sec">
        <option value="">All sections (<?= count($students) ?>)</option>
        <?php foreach ($studentsBySection as $sec => $list): ?>
          <option value="<?= htmlspecialchars($sec) ?>" <?= $sec === $secPick ? 'selected' : '' ?>><?= htmlspecialchars($sec) ?> (<?= count($list) ?>)</option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="ms-count" id="ms-count" aria-live="polite"></div>
    <div class="ms-panel">
      <table>
        <thead><tr><th>Student</th><th>Section</th><th>Classes</th><?php if ($hasPresence): ?><th>Last active</th><?php endif; ?></tr></thead>
        <tbody id="ms-rows">
          <?php foreach ($students as $s):
            $sid = (int) $s['id']; $ago = msAgo($seen[$sid] ?? null);
            $classes = []; foreach ($s['classes'] as $c) $classes[$c['assign_id']] = $c['name']; ?>
            <tr data-name="<?= htmlspecialchars(strtolower($s['first_name'] . ' ' . $s['last_name'] . ' ' . $s['last_name'] . ', ' . $s['first_name'] . ' ' . $s['school_id'])) ?>"
                data-sec="<?= htmlspecialchars($s['section_dept']) ?>">
              <td><div class="ms-name"><span class="ms-av" aria-hidden="true"><?= htmlspecialchars(strtoupper(substr($s['first_name'], 0, 1) . substr($s['last_name'], 0, 1))) ?></span>
                <div><?= htmlspecialchars($s['last_name'] . ', ' . $s['first_name']) ?><div class="ms-lrn">LRN <?= htmlspecialchars($s['school_id'] ?: '—') ?></div></div></div></td>
              <td data-l="Section"><?= htmlspecialchars($s['section_dept'] ?: '—') ?></td>
              <td><div class="ms-chips">
                <?php foreach ($classes as $aid => $name): ?><a class="ms-chip" href="classes.php?class=<?= (int) $aid ?>"><?= htmlspecialchars($name) ?></a><?php endforeach; ?>
              </div></td>
              <?php if ($hasPresence): ?><td data-l="Last active" class="<?= $ago === 'Online' ? 'ms-online' : '' ?>"><?= htmlspecialchars($ago) ?></td><?php endif; ?>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <div class="ms-empty" id="ms-none" hidden><strong>No matches</strong>Try another name, or choose All sections.</div>
    </div>
  <?php endif; ?>
</div>

<script>
  (function () {
    const q = document.getElementById('ms-q'), sec = document.getElementById('ms-sec');
    if (!q) return;
    const rows = [...document.querySelectorAll('#ms-rows tr')];
    function apply() {
      const term = q.value.trim().toLowerCase(), s = sec.value;
      let shown = 0;
      rows.forEach(r => {
        const ok = (!term || r.dataset.name.includes(term)) && (!s || r.dataset.sec === s);
        r.hidden = !ok; if (ok) shown++;
      });
      document.getElementById('ms-none').hidden = shown > 0;
      document.getElementById('ms-count').textContent = `Showing ${shown} of ${rows.length} student${rows.length === 1 ? '' : 's'}`;
      // keep the URL in step so refresh / back keeps the filter
      const u = new URL(location.href);
      term ? u.searchParams.set('search', q.value.trim()) : u.searchParams.delete('search');
      s ? u.searchParams.set('section', s) : u.searchParams.delete('section');
      history.replaceState(null, '', u);
    }
    q.addEventListener('input', apply);
    sec.addEventListener('change', apply);
    apply();
  })();
</script>

<?php require __DIR__ . '/../includes/footer.php'; ?>
