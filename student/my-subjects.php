<?php
// ============================================================
//  Arandia College eLMS — Student: My Subjects
//  File: student/my-subjects.php
//  Data comes from student/_init.php (same rules as the teacher side).
// ============================================================
require_once __DIR__ . '/../includes/settings.php';   // current school year / semester (Admin → Settings)
require __DIR__ . '/_init.php';
$currentPage = 'my-subjects.php';
$pageTitle = 'My Subjects';
// Grade total and module progress per subject (same numbers as Grades and Modules)
$myGradebook = studentGradebook($conn, $user_id, $mySubjects);
require __DIR__ . '/../includes/header.php';
?>
<style>
    .sj-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(min(290px, 100%), 1fr)); gap: 1rem; }
    /* Same card as the Teacher's My Classes: the whole card opens the subject */
    .sj-card { display: flex; flex-direction: column; background: var(--bg-card, #fff); border: 1px solid var(--border); border-radius: 16px;
               box-shadow: 0 2px 14px rgba(0, 0, 0, .05); padding: 1.2rem 1.3rem; text-decoration: none; color: inherit;
               transition: border-color .15s, transform .15s, box-shadow .15s; }
    .sj-card[hidden] { display: none; }
    .sj-card:hover { border-color: var(--primary); transform: translateY(-1px); box-shadow: 0 6px 20px rgba(0, 48, 135, .08); }
    .sj-card:focus-visible { outline: 2px solid var(--primary); outline-offset: 2px; }
    .sj-code { font-size: .72rem; font-weight: 800; letter-spacing: .04em; color: var(--primary); text-transform: uppercase; }
    html[data-theme="dark"] .sj-code { color: #8fb0ff; }
    .sj-name { font-family: 'Nunito', sans-serif; font-size: 1.1rem; font-weight: 800; color: var(--text-main); margin-top: .2rem; line-height: 1.25; }
    .sj-teacher { font-size: .82rem; color: var(--text-muted); margin: .15rem 0 1rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .sj-meta { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: .75rem; margin-top: auto; padding-top: .9rem; border-top: 1px solid var(--border); font-size: .76rem; color: var(--text-muted); }
    .sj-meta b { display: block; font-family: 'Nunito', sans-serif; font-size: 1.05rem; color: var(--text-main); line-height: 1.3; }
    .sj-meta b.warn { color: #b38600; }
    .sj-bar { height: 6px; border-radius: 100px; background: var(--border); overflow: hidden; margin-top: .9rem; }
    .sj-bar span { display: block; height: 100%; background: var(--primary); border-radius: 100px; }
    html[data-theme="dark"] .sj-bar span { background: #8fb0ff; }
    .sj-bar-l { display: flex; justify-content: space-between; font-size: .72rem; color: var(--text-muted); margin-top: .35rem; }
    .sj-empty { padding: 2.5rem 1rem; text-align: center; color: var(--text-muted); }
    .sj-search { width: 260px; max-width: 100%; }
</style>
<div class="section-panel active">
    <div class="page-header" style="display:flex;justify-content:space-between;align-items:center;gap:1rem;flex-wrap:wrap;">
        <h2 class="page-title">My Subjects</h2>
        <?php if ($mySubjects): ?>
            <label class="sr-only" for="sj-q">Search subjects</label>
            <input id="sj-q" type="search" class="form-control sj-search" placeholder="Search subjects or teachers" autocomplete="off">
        <?php endif; ?>
    </div>
    <?php if (!$mySubjects): ?>
        <div class="card"><div class="sj-empty"><strong>No subjects yet</strong><br>Your subjects appear here once the admin enrolls you.</div></div>
    <?php else: ?>
        <div class="sj-grid" id="sj-grid">
            <?php foreach ($mySubjects as $s):
                $cid = (int) $s['id']; $code = $s['course_code'];
                $mods = $modulesByCourse[$code] ?? [];
                $done = count(array_filter($mods, fn($m) => isset($myProgress[$m['id']])));
                $pct = $mods ? round($done / count($mods) * 100) : 0;
                $tot = $myGradebook[$cid]['total'] ?? null;
                $asgOpen = count(array_filter($assignmentsByCourse[$code] ?? [], fn($a) => (int) $a['attempt_count'] === 0 && (!$a['due_date'] || strtotime($a['due_date']) > time())));
            ?>
                <a class="sj-card" href="modules.php?course=<?= $cid ?>" data-q="<?= htmlspecialchars(strtolower($s['course_name'] . ' ' . $code . ' ' . ($s['teacher_name'] ?? ''))) ?>">
                    <div class="sj-code"><?= htmlspecialchars($code) ?></div>
                    <div class="sj-name"><?= htmlspecialchars($s['course_name']) ?></div>
                    <div class="sj-teacher"><?= $s['teacher_name'] ? htmlspecialchars($s['teacher_name']) : 'No teacher yet' ?> · <?= elmsSetSpan('term_short') /* current term from Admin → Settings */ ?></div>
                    <div class="sj-meta">
                        <div><b><?= $done ?>/<?= count($mods) ?></b>modules done</div>
                        <div><b class="<?= $asgOpen ? 'warn' : '' ?>"><?= $asgOpen ?></b>to submit</div>
                        <div><b><?= ($tot && $tot['percent'] !== null) ? htmlspecialchars($tot['percent']) . '%' : '—' ?></b>grade so far</div>
                    </div>
                    <div class="sj-bar" role="progressbar" aria-label="Module progress" aria-valuenow="<?= $pct ?>" aria-valuemin="0" aria-valuemax="100"><span style="width:<?= $pct ?>%"></span></div>
                    <div class="sj-bar-l"><span>Module progress</span><span><?= $pct ?>%</span></div>
                </a>
            <?php endforeach; ?>
        </div>
        <div class="card sj-empty" id="sj-none" hidden>No subject matches your search.</div>
    <?php endif; ?>
</div>
<script>
    (function () {
        const q = document.getElementById('sj-q'); if (!q) return;
        q.addEventListener('input', () => {
            // read the cards each time: live updates can add or change them
            const cards = [...document.querySelectorAll('#sj-grid .sj-card')];
            const t = q.value.trim().toLowerCase(); let n = 0;
            cards.forEach(c => { const ok = !t || c.dataset.q.includes(t); c.hidden = !ok; if (ok) n++; });
            document.getElementById('sj-none').hidden = n > 0;
        });
    })();
</script>
<?php require __DIR__ . '/../includes/footer.php'; ?>