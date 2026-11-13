<?php
// ============================================================
//  Arandia College eLMS — Student: Grades
//  File: student/grades.php
//  Data comes from student/_init.php (same rules as the teacher side).
// ============================================================
require __DIR__ . '/_init.php';
$currentPage = 'grades.php';
$pageTitle = 'My Grades';
// Same gradebook the teacher uses (includes/gradebook.php), for this student only
$myGradebook = studentGradebook($conn, $user_id, $mySubjects);
$generalAverage = studentGeneralAverage($myGradebook);
require __DIR__ . '/../includes/header.php';

// ── Labels (unknown values fall back safely instead of throwing warnings) ──
$grStates = ['graded' => 'Graded', 'pending' => 'Pending', 'missing' => 'Missing', 'none' => 'Not yet due'];
$grTypes = ['assignment' => 'Assignment', 'quiz' => 'Quiz', 'manual' => 'Other'];

// ── Counts for the summary card ──
$grCount = ['graded' => 0, 'pending' => 0, 'missing' => 0, 'none' => 0];
foreach ($myGradebook ?: [] as $g) {
    foreach ($g['items'] ?? [] as $it) {
        $st = $g['cells'][$it['id']]['state'] ?? 'none';
        if (!isset($grCount[$st])) $st = 'none';
        $grCount[$st]++;
    }
}
$grOneSubject = is_array($myGradebook) && count($myGradebook) === 1;
?>
<style>
    /* Summary card */
    .gr-hero { background: var(--primary); color: #fff; padding: 1.4rem 1.75rem; border-radius: var(--ui-radius-lg, 14px); margin-bottom: 1rem;
               display: flex; align-items: center; justify-content: space-between; gap: 1.5rem; flex-wrap: wrap; }
    .gr-hero-main { min-width: 0; flex: 1 1 280px; }
    .gr-hero small { display: block; font-size: .72rem; opacity: .85; text-transform: uppercase; font-weight: 700; letter-spacing: .06em; }
    .gr-hero b { display: block; font-size: 2.4rem; line-height: 1.15; font-weight: 800; font-family: 'Nunito', sans-serif; margin-top: .2rem; }
    .gr-hero p { margin: .35rem 0 0; font-size: .82rem; opacity: .85; max-width: 440px; line-height: 1.5; }
    .gr-stats { display: flex; gap: .6rem; flex-wrap: wrap; }
    .gr-stat { background: rgba(255,255,255,.12); border-radius: 10px; padding: .6rem .9rem; min-width: 92px; }
    .gr-stat span { display: block; font-size: .7rem; opacity: .85; font-weight: 600; white-space: nowrap; }
    .gr-stat strong { display: block; font-size: 1.25rem; font-weight: 800; font-family: 'Nunito', sans-serif; line-height: 1.3; }

    /* Legend */
    .gr-legend { display: flex; flex-wrap: wrap; gap: .4rem 1.1rem; font-size: .78rem; color: var(--text-muted); margin: 0 0 1rem; }
    .gr-legend span { display: inline-flex; align-items: center; gap: .4rem; }
    .gr-dot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }

    /* Status pill (one style for every state) */
    .gr-state { display: inline-block; font-size: .74rem; font-weight: 700; padding: .2rem .6rem; border-radius: 100px; white-space: nowrap; }
    .gr-state.graded, .gr-dot.graded { background: rgba(0, 135, 90, .12); color: #00875a; }
    .gr-state.pending, .gr-dot.pending { background: rgba(179, 134, 0, .14); color: #b38600; }
    .gr-state.missing, .gr-dot.missing { background: rgba(192, 57, 43, .12); color: #c0392b; }
    .gr-state.none, .gr-dot.none { background: var(--bg-light, #f1f5f9); color: var(--text-muted); }
    .gr-dot.graded { background: #00875a; } .gr-dot.pending { background: #b38600; } .gr-dot.missing { background: #c0392b; } .gr-dot.none { background: #94a3b8; }
    html[data-theme="dark"] .gr-state.graded { background: rgba(74,222,128,.14); color: #4ade80; }
    html[data-theme="dark"] .gr-state.pending { background: rgba(250,204,21,.14); color: #facc15; }
    html[data-theme="dark"] .gr-state.missing { background: rgba(248,113,113,.14); color: #f87171; }
    html[data-theme="dark"] .gr-state.none { background: rgba(148,163,184,.14); color: #94a3b8; }

    /* Subject header score */
    .gr-total { text-align: right; flex-shrink: 0; }
    .gr-total b { display: block; font-weight: 800; font-family: 'Nunito', sans-serif; font-size: 1.05rem; }
    .gr-total small { display: block; font-size: .72rem; font-weight: 600; color: var(--text-muted); }

    /* Table: fixed columns so every subject lines up */
    .gr-table { width: 100%; table-layout: fixed; border-collapse: collapse; font-size: .9rem; }
    .gr-table th { text-align: left; font-size: .68rem; font-weight: 800; letter-spacing: .05em; text-transform: uppercase; color: var(--text-muted); white-space: nowrap; }
    .gr-table th, .gr-table td { padding: .75rem 1.25rem; vertical-align: middle; }
    .gr-table td:first-child { font-weight: 600; overflow-wrap: anywhere; }
    .gr-table .gr-c-type { width: 22%; color: var(--text-muted); }
    .gr-table .gr-c-score { width: 20%; white-space: nowrap; }
    .gr-table .gr-c-status { width: 20%; }
    .gr-score { font-weight: 700; }
    .gr-max { color: var(--text-muted); }
    .gr-empty { padding: 1.25rem; text-align: center; color: var(--text-muted); }

    @media (max-width: 600px) {
        .gr-hero { padding: 1.2rem 1.25rem; }
        .gr-hero b { font-size: 2rem; }
        .gr-stat { flex: 1 1 0; min-width: 0; }
        .gr-table th, .gr-table td { padding: .6rem .6rem; }
        .gr-table .gr-c-type { display: none; }
        .gr-table .gr-c-score { width: 90px; }
        .gr-table .gr-c-status { width: 105px; }
    }
</style>
<div class="section-panel active">
    <div class="page-header"><h2 class="page-title">My Grades</h2></div>

    <?php if (!$myGradebook): ?>
        <div class="card"><p style="padding:1.5rem; color:var(--text-muted);">No subjects yet, so there are no grades to show.</p></div>
    <?php else: ?>
        <div class="gr-hero">
            <div class="gr-hero-main">
                <small><?= !empty($activeSubject) ? 'Grade in this subject' : 'General average' ?></small>
                <b><?= $generalAverage === null ? '—' : htmlspecialchars((string) $generalAverage) . '%' ?></b>
                <p><?= !empty($activeSubject)
                    ? 'Your score over the work that counts so far in ' . htmlspecialchars($activeSubject['course_name']) . '. Same number your teacher sees.'
                    : 'Average of your subject totals, counting only the work due so far. Same number your teacher sees.' ?></p>
            </div>
            <div class="gr-stats">
                <div class="gr-stat"><span>Graded</span><strong><?= $grCount['graded'] ?></strong></div>
                <div class="gr-stat"><span>Pending</span><strong><?= $grCount['pending'] ?></strong></div>
                <div class="gr-stat"><span>Missing</span><strong><?= $grCount['missing'] ?></strong></div>
            </div>
        </div>

        <div class="gr-legend">
            <span><i class="gr-dot pending"></i>Pending: submitted, waiting for your teacher</span>
            <span><i class="gr-dot missing"></i>Missing: past due, counts as 0</span>
            <span><i class="gr-dot none"></i>Not yet due: not counted yet</span>
        </div>

        <?php $gi = 0; foreach ($myGradebook as $cid => $g):
            $gbid = 'gb' . $gi;
            $grad = !empty($thumbGrads) ? $thumbGrads[$gi % count($thumbGrads)] : 'var(--primary)';
            $gi++;
            $t = $g['total'] ?? null;
            $pct = ($t && $t['percent'] !== null) ? $t['percent'] : null;
            $hasMax = $t && isset($t['max']) && (float) $t['max'] > 0;
            $items = $g['items'] ?? [];
        ?>
            <div class="accordion" style="margin-bottom:1rem;" id="g-<?= (int) $cid ?>">
                <div class="acc-head" onclick="toggleBlock('<?= $gbid ?>')" role="button" tabindex="0" aria-controls="<?= $gbid ?>"
                     onkeydown="if (event.key === 'Enter' || event.key === ' ') { event.preventDefault(); toggleBlock('<?= $gbid ?>'); }">
                    <div class="acc-left">
                        <div class="acc-icon" style="background:<?= $grad ?>">
                            <svg class="icon" viewBox="0 0 24 24"><line x1="18" y1="20" x2="18" y2="10"></line><line x1="12" y1="20" x2="12" y2="4"></line><line x1="6" y1="20" x2="6" y2="14"></line></svg>
                        </div>
                        <div>
                            <div class="acc-title"><?= htmlspecialchars(($g['code'] ?? '') . ' - ' . ($g['name'] ?? '')) ?></div>
                            <div class="acc-meta"><?= !empty($g['teacher']) ? htmlspecialchars($g['teacher']) : 'No teacher assigned yet' ?><?= ($t && !empty($t['pending'])) ? ' · ' . (int) $t['pending'] . ' waiting for a grade' : '' ?></div>
                        </div>
                    </div>
                    <div class="gr-total">
                        <b><?= $pct === null ? '—' : htmlspecialchars((string) $pct) . '%' ?></b>
                        <?php if ($hasMax): ?><small><?= htmlspecialchars($t['score'] . ' / ' . $t['max']) ?></small><?php endif; ?>
                    </div>
                </div>
                <div class="acc-body<?= $grOneSubject ? ' open' : '' ?>" id="<?= $gbid ?>">
                    <?php if (!$items): ?>
                        <div class="gr-empty">No graded work in this subject yet.</div>
                    <?php else: ?>
                        <div class="table-wrap">
                        <table class="gr-table">
                            <thead><tr><th>Item</th><th class="gr-c-type">Type</th><th class="gr-c-score">Score</th><th class="gr-c-status">Status</th></tr></thead>
                            <tbody>
                                <?php foreach ($items as $it):
                                    $c = $g['cells'][$it['id']] ?? ['state' => 'none', 'score' => null];
                                    $state = isset($grStates[$c['state'] ?? '']) ? $c['state'] : 'none';
                                    $type = $grTypes[$it['type'] ?? ''] ?? 'Other';
                                    $scoreTxt = $state === 'graded' ? (string) ($c['score'] ?? '0') : ($state === 'missing' ? '0' : '—');
                                ?>
                                    <tr>
                                        <td><?= htmlspecialchars((string) ($it['title'] ?? '')) ?></td>
                                        <td class="gr-c-type"><?= $type ?></td>
                                        <td class="gr-c-score"><span class="gr-score"><?= htmlspecialchars($scoreTxt) ?></span> <span class="gr-max">/ <?= htmlspecialchars((string) ($it['max'] ?? '')) ?></span></td>
                                        <td class="gr-c-status"><span class="gr-state <?= $state ?>"><?= $grStates[$state] ?></span></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>