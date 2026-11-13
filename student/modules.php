<?php
// ============================================================
//  Arandia College eLMS — Student: Modules
//  File: student/modules.php
//  Data comes from student/_init.php (same rules as the teacher side).
// ============================================================
require __DIR__ . '/_init.php';
$currentPage = 'modules.php';
$pageTitle = 'Modules';
require __DIR__ . '/../includes/header.php';
?>

<div class="section-panel active">
                <div class="page-header">
                    <h2 class="page-title">Modules</h2>
                </div>

                <div style="margin-bottom:1.5rem; gap:1rem; flex-wrap:wrap; display:<?= !empty($activeSubject) ? 'none' : 'flex' ?>;">
                    <select id="mod-filter-course" class="form-control" onchange="filterMod()"<?= !empty($activeSubject) ? ' style="display:none" aria-hidden="true"' : ' style="max-width:260px"' ?>>
                        <option value="">All Subjects</option>
                        <?php foreach ($mySubjects as $s): ?>
                            <option value="<?= htmlspecialchars($s['course_code']) ?>">
                                <?= htmlspecialchars($s['course_code']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

<?php
// ── One card per module: the lesson + the assignments and quizzes the teacher put under it ──
// (module_id on assignments / quizzes, see includes/module_links.php). Work without a
// module, or whose module was removed, is listed under "Other work".
$smCourses = [];   // course_id => ['name','code','mods'=>[mid=>module],'work'=>[mid|0=>[items]]]
foreach ($myModules as $m) {
    $c = (int) $m['course_id'];
    $smCourses[$c] ??= ['name' => $m['course_name'], 'code' => $m['course_code'], 'mods' => [], 'work' => []];
    $smCourses[$c]['mods'][(int) $m['id']] = $m;
}
$smAdd = function (array $item, string $type) use (&$smCourses) {
    $c = (int) $item['course_id'];
    $smCourses[$c] ??= ['name' => $item['course_name'], 'code' => $item['course_code'], 'mods' => [], 'work' => []];
    $mid = (int) ($item['module_id'] ?? 0);
    if (!isset($smCourses[$c]['mods'][$mid])) $mid = 0;
    $item['type'] = $type;
    $item['due'] = $type === 'quiz' ? $item['close_at'] : $item['due_date'];
    $smCourses[$c]['work'][$mid][] = $item;
};
foreach ($myAssignments as $a) $smAdd($a, 'assignment');
foreach ($myQuizzes as $q) $smAdd($q, 'quiz');
uasort($smCourses, fn($x, $y) => strcasecmp($x['name'], $y['name']));
foreach ($smCourses as &$sc) {
    foreach ($sc['work'] as &$list) usort($list, fn($x, $y) => [$x['due'] === null, (string) $x['due'], $x['type']] <=> [$y['due'] === null, (string) $y['due'], $y['type']]);
    unset($list);
}
unset($sc);

$smNum = fn($n) => rtrim(rtrim(number_format((float) $n, 2, '.', ''), '0'), '.');
$smDue = function (?string $d): string {
    $ts = $d ? strtotime($d) : false;
    if ($ts === false) return '—';
    return date(date('Y', $ts) === date('Y') ? 'M j' : 'M j, Y', $ts);
};
// Row icons + status marks (same look as the teacher's Modules tab)
$smSvg = fn(string $p) => '<svg class="smc-ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $p . '</svg>';
$smIco = [
    'lesson'     => $smSvg('<path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8z"/><path d="M14 3v5h5"/><path d="M9 13h6M9 17h6"/>'),
    'assignment' => $smSvg('<path d="M4 11v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8"/><path d="M12 15V3"/><path d="m8 7 4-4 4 4"/>'),
    'quiz'       => $smSvg('<rect x="3.5" y="3.5" width="17" height="17" rx="2.5"/><path d="M9.6 9.3a2.5 2.5 0 0 1 4.8.9c0 1.7-2.4 2.2-2.4 3.6"/><path d="M12 17h.01"/>'),
];
$smCheck = '<span class="smc-ok" title="Done"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg></span>';
$smNext = '<span class="smc-go" title="To do"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m9 6 6 6-6 6"/></svg></span>';
/** Submitted?, score text, status [label, badge class] of one assignment / quiz for this student. */
$smItem = function (array $w) use ($smNum): array {
    $now = time();
    if ($w['type'] === 'assignment') {
        $done = !empty($w['submission_id']);
        $graded = $done && $w['submission_score'] !== null;
        $score = $graded ? $smNum($w['submission_score']) . '/' . $smNum($w['max_score']) : null;
        if ($graded) $st = ['Graded', 'badge-green'];
        elseif ($done) $st = [($w['submission_status'] ?? '') === 'Late' ? 'Late' : 'Submitted', 'badge-yellow'];
        elseif (!empty($w['due_date']) && strtotime($w['due_date']) < $now) $st = ['Overdue', 'badge-red'];
        else $st = ['Not submitted', 'badge-gray'];
        return [$done, $score, $st];
    }
    $done = in_array($w['attempt_status'] ?? null, ['Submitted', 'Graded'], true);
    $score = $done && ($w['my_score'] ?? null) !== null ? $smNum($w['my_score']) . '/' . $smNum($w['max_score']) : null;
    $as = $w['attempt_status'] ?? null;
    if ($as === 'Graded') $st = ['Graded', 'badge-green'];
    elseif ($done) $st = ['Submitted', 'badge-yellow'];
    elseif ($as === 'In Progress') $st = ['In progress', 'badge-blue'];
    elseif (!empty($w['open_at']) && strtotime($w['open_at']) > $now) $st = ['Opens ' . date('M j', strtotime($w['open_at'])), 'badge-gray'];
    elseif (!empty($w['close_at']) && strtotime($w['close_at']) < $now) $st = ['Missed', 'badge-red'];
    else $st = ['Not started', 'badge-gray'];
    return [$done, $score, $st];
};
?>
                <?php if (!$smCourses): ?>
                    <div class="card" style="text-align:center; padding:3rem; color:var(--text-muted);">
                        No modules posted yet<?= !empty($activeSubject) ? ' in ' . htmlspecialchars($activeSubject['course_name']) : '' ?>.
                    </div>
                <?php endif; ?>

                <?php
                $si = 0;
                $onlyOne = count($smCourses) === 1;
                foreach ($smCourses as $courseId => $sc):
                    $mods = $sc['mods'];
                    // A module is complete when its lesson is done and all its work is submitted
                    $modState = [];
                    foreach ($mods as $mid => $m) {
                        $items = $sc['work'][$mid] ?? [];
                        $wDone = count(array_filter($items, fn($w) => $smItem($w)[0]));
                        $lesson = isset($myProgress[$mid]) ? 'done' : (isset($myOpened[$mid]) ? 'opened' : 'new');
                        $modState[$mid] = ['lesson' => $lesson, 'wdone' => $wDone, 'wtotal' => count($items)];
                    }
                    $cd = count(array_filter($modState, fn($x) => $x['lesson'] === 'done' && $x['wdone'] >= $x['wtotal']));
                    $ct = count($mods);
                    $cp = $ct > 0 ? round($cd / $ct * 100) : 0;
                    $grad = !empty($thumbGrads) ? $thumbGrads[$si % count($thumbGrads)] : 'var(--primary)';
                    $bid = 'mb' . $si;
                    $si++;
                    $groups = [];
                    foreach ($mods as $mid => $m) $groups[] = [$mid, $m];
                    if (!empty($sc['work'][0])) $groups[] = [0, null];
                    ?>
                    <div class="accordion sm-acc" data-code="<?= htmlspecialchars($sc['code']) ?>" data-total="<?= $ct ?>"
                        data-done="<?= $cd ?>">
                        <div class="acc-head">
                            <div class="acc-left">
                                <div class="acc-icon" style="background:<?= $grad ?>">
                                    <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2">
                                        <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                                        <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <div class="acc-title"><?= htmlspecialchars($sc['name']) ?></div>
                                </div>
                            </div>
                            <div style="display:flex; align-items:center; gap:1rem;">
                                <span class="mod-pct" style="font-weight:700; color:var(--primary);"><?= $cp ?>%</span>
                            </div>
                        </div>
                        <div class="acc-body open" id="<?= $bid ?>">
                            <?php foreach ($groups as [$mid, $m]):
                                $items = $sc['work'][$mid] ?? [];
                                $ms = null; $complete = false; $started = false;
                                if ($m) {
                                    $ms = $modState[$mid];
                                    $complete = $ms['lesson'] === 'done' && $ms['wdone'] >= $ms['wtotal'];
                                    $started = $ms['lesson'] !== 'new' || $ms['wdone'] > 0;
                                }
                                $key = $m ? (string) $mid : 'other-' . $courseId;
                                ?>
                                <div class="smc" id="smc-<?= $key ?>" data-key="smc-<?= $key ?>" data-id="<?= $m ? $mid : '' ?>"
                                    <?php if ($m): ?>data-status="<?= $ms['lesson'] ?>" data-wdone="<?= $ms['wdone'] ?>" data-wtotal="<?= $ms['wtotal'] ?>"<?php endif; ?>>
                                    <button type="button" class="smc-head" onclick="smcToggle('<?= $key ?>')" aria-expanded="false" aria-controls="smc-body-<?= $key ?>">
                                        <span class="sm-num <?= $m ? ($complete ? 'done' : ($started ? 'opened' : '')) : '' ?>"><?= $m ? ($m['module_number'] ? (int) $m['module_number'] : '•') : '+' ?></span>
                                        <span class="smc-h">
                                            <span class="smc-title"><?= $m ? htmlspecialchars($m['title']) : 'Other work' ?></span>
                                        </span>
                                        <?php if ($m): ?>
                                            <span class="smc-state badge <?= $complete ? 'badge-green' : ($started ? 'badge-blue' : 'badge-gray') ?>"><?= $complete ? 'Completed' : ($started ? 'In progress' : 'Not started') ?></span>
                                        <?php endif; ?>
                                        <?php $nSec = ($m ? 1 : 0) + count($items); ?>
                                        <span class="smc-sec"><?= $nSec ?> section<?= $nSec == 1 ? '' : 's' ?></span>
                                        <svg class="smc-chev" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><polyline points="6 9 12 15 18 9"></polyline></svg>
                                    </button>
                                    <div class="smc-body" id="smc-body-<?= $key ?>">
                                        <table class="smc-table">
                                            <colgroup><col><col class="c-sub"><col class="c-score"><col class="c-due"><col class="c-st"></colgroup>
                                            <thead><tr><th>Section</th><th>Submitted</th><th>Score</th><th>Due</th><th>Status</th></tr></thead>
                                            <tbody>
                                                <?php if ($m): $ls = $ms['lesson']; ?>
                                                    <tr class="smc-row smc-lesson" onclick="smGo(event, this)">
                                                        <td><a class="smc-item" href="#" onclick="event.preventDefault(); smOpen(<?= (int) $mid ?>)"><?= $smIco['lesson'] ?><span><?= htmlspecialchars($m['title']) ?></span></a></td>
                                                        <td data-l="Submitted"></td>
                                                        <td data-l="Score"></td>
                                                        <td data-l="Due"></td>
                                                        <td data-l="Status" class="smc-lst"><?= $ls === 'done' ? $smCheck : $smNext ?></td>
                                                    </tr>
                                                <?php endif; ?>
                                                <?php foreach ($items as $w):
                                                    [$wd, $score, $st] = $smItem($w);
                                                    $isQuiz = $w['type'] === 'quiz';
                                                    $href = 'work.php?type=' . ($isQuiz ? 'quiz' : 'assignment') . '&id=' . (int) $w['id'] . '&course=' . (int) $w['course_id'];
                                                    // Status: ✓ done · › to do · a short label when it needs attention
                                                    $label = $st[0];
                                                    if (in_array($label, ['Graded', 'Submitted'], true)) $stHtml = $smCheck;
                                                    elseif (in_array($label, ['Not submitted', 'Not started'], true)) $stHtml = $smNext;
                                                    else $stHtml = '<span class="badge ' . $st[1] . '">' . htmlspecialchars($label) . '</span>';
                                                    ?>
                                                    <tr class="smc-row" onclick="smGo(event, this)">
                                                        <td><a class="smc-item" href="<?= htmlspecialchars($href) ?>"><?= $smIco[$w['type']] ?><span><?= htmlspecialchars($w['title']) ?></span></a></td>
                                                        <td data-l="Submitted"><?= $wd ? $smCheck : '' ?></td>
                                                        <td data-l="Score"><?= $score !== null ? htmlspecialchars($score) : '<span class="smc-dim">—/' . $smNum($w['max_score']) . '</span>' ?></td>
                                                        <td data-l="Due"><?= $w['due'] ? htmlspecialchars($smDue($w['due'])) : '<span class="smc-dim">—</span>' ?></td>
                                                        <td data-l="Status"><?= $stHtml ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                                <?php if (!$items && !$m): ?>
                                                    <tr><td colspan="5" class="smc-none">Nothing here yet.</td></tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

<style>
    .sm-num { width: 30px; height: 30px; border-radius: 50%; flex-shrink: 0; display: flex; align-items: center; justify-content: center;
              font-size: .8rem; font-weight: 800; background: #e2e8f0; color: #475569; }
    .sm-num.opened { background: #e8f0ff; color: var(--primary); }
    .sm-num.done { background: #d1fae5; color: #047857; }
    /* Module cards: lesson + the assignments and quizzes under each module */
    /* Subject header = one card; each module below it = its own card (same as the teacher's Modules tab) */
    .accordion.sm-acc, .elms-app .accordion.sm-acc { background: transparent; border: 0; box-shadow: none; border-radius: 0; overflow: visible; margin-bottom: 1.5rem; }
    .sm-acc > .acc-head { background: var(--ui-surface, #fff); border: 1px solid var(--ui-border, var(--border)); border-radius: var(--ui-radius-lg, 14px); box-shadow: var(--ui-shadow-sm, 0 2px 14px rgba(0,0,0,.06)); }
    .sm-acc > .acc-head { cursor: default; }
    .sm-acc > .acc-body { background: transparent; border-top: 0; }
    .sm-acc > .acc-body.open { display: flex; flex-direction: column; gap: .75rem; padding-top: .75rem; }
    .smc { background: var(--ui-surface, #fff); border: 1px solid var(--ui-border, var(--border)); border-radius: var(--ui-radius-lg, 14px);
           box-shadow: var(--ui-shadow-sm, 0 2px 14px rgba(0,0,0,.06)); overflow: hidden; }
    .smc.open .smc-head { border-bottom: 1px solid var(--ui-border-soft, var(--border)); }
    .smc.open .smc-body { padding-top: .85rem; }
    .smc-head { width: 100%; display: flex; align-items: center; gap: .8rem; padding: .9rem 1.25rem; background: transparent; border: 0; cursor: pointer; text-align: left; font: inherit; color: inherit; }
    .smc-head:hover { background: var(--ui-surface-2, #f8fafc); }
    .smc-head:focus-visible { outline: 2px solid var(--primary); outline-offset: -2px; }
    .smc-head:focus:not(:focus-visible) { outline: none !important; box-shadow: none !important; }
    .smc-h { flex: 1; min-width: 0; display: flex; flex-direction: column; }
    .smc-title { font-weight: 700; color: var(--text-main); overflow-wrap: anywhere; }
    .smc-meta { font-size: .78rem; color: var(--text-muted); margin-top: .1rem; }
    .smc-state { flex-shrink: 0; white-space: nowrap; }
    .smc-chev { flex-shrink: 0; color: var(--text-muted); transition: transform .2s; }
    .smc.open .smc-chev { transform: rotate(180deg); }
    .smc-body { display: none; padding: 0 1rem .35rem; }
    .smc.open .smc-body { display: block; }
    .smc-sec { flex-shrink: 0; font-size: .8rem; font-weight: 700; color: var(--text-muted); white-space: nowrap; }
    /* Lesson + work rows: Section · Submitted · Score · Due · Status (same as the teacher's Modules tab) */
    .smc-table { width: 100%; table-layout: fixed; border-collapse: separate; border-spacing: 0; font-size: .86rem; }
    .smc-table col.c-sub { width: 100px; } .smc-table col.c-score { width: 100px; } .smc-table col.c-due { width: 92px; } .smc-table col.c-st { width: 112px; }
    .smc-table th { text-transform: none; letter-spacing: 0; font-size: .74rem; font-weight: 700; color: var(--text-main); background: var(--ui-surface-2, #f8fafc); padding: .55rem .8rem; text-align: center; white-space: nowrap; }
    .smc-table th:first-child { text-align: left; border-radius: 8px 0 0 8px; }
    .smc-table th:last-child { border-radius: 0 8px 8px 0; }
    .smc-table td { padding: .8rem; border-bottom: 1px solid var(--ui-border-soft, var(--border)); color: var(--text-main); vertical-align: middle; text-align: center; }
    .smc-table tbody tr:last-child td { border-bottom: 0; }
    .smc-table td:first-child { text-align: left; }
    .smc-row { cursor: pointer; }
    .smc-row:hover td { background: var(--ui-surface-2, #f8fafc); }
    .smc-item { display: flex; align-items: center; gap: .75rem; color: var(--text-main); text-decoration: none; font-weight: 600; overflow-wrap: anywhere; }
    .smc-item:focus-visible { outline: 2px solid var(--primary); outline-offset: 2px; border-radius: 4px; }
    .smc-ico { flex-shrink: 0; width: 20px; height: 20px; color: var(--text-main); }
    .smc-ok { color: #00a36c; } .smc-go { color: var(--text-main); }
    .smc-ok svg, .smc-go svg { width: 18px; height: 18px; vertical-align: middle; }
    .smc-table .badge { white-space: nowrap; }
    .smc-dim { color: var(--text-muted); }
    .smc-none { text-align: center !important; color: var(--text-muted) !important; font-size: .82rem; padding: 1.2rem .8rem !important; }
    html[data-theme="dark"] .smc-ok { color: #4ade80; }
    html[data-theme="dark"] .sm-num { background: rgba(203,213,225,.12); color: #cbd5e1; }
    html[data-theme="dark"] .sm-num.opened { background: rgba(143,176,255,.16); color: #c7d7ff; }
    html[data-theme="dark"] .sm-num.done { background: rgba(74,222,128,.16); color: #4ade80; }
    @media (max-width: 640px) {
        .smc-head { flex-wrap: wrap; padding: .85rem 1rem; }
        .smc-h { flex-basis: calc(100% - 80px); }
        .smc-state { margin-left: calc(30px + .8rem); }
        .smc-chev { margin-left: auto; }
        .smc-body { padding: 0 .4rem .3rem; }
        .smc-sec { display: none; }
        .smc-table thead, .smc-table colgroup { display: none; }
        .smc-table, .smc-table tbody, .smc-table tr, .smc-table td { display: block; width: 100%; }
        .smc-table tr { padding: .7rem .5rem; border-bottom: 1px solid var(--ui-border-soft, var(--border)); }
        .smc-table tbody tr:last-child { border-bottom: 0; }
        .smc-table td { border: 0 !important; padding: .12rem 0; text-align: left; }
        .smc-table td:first-child { margin-bottom: .3rem; }
        .smc-table td[data-l] { display: flex; justify-content: space-between; gap: .75rem; font-size: .82rem; padding-left: calc(20px + .75rem); }
        .smc-table td[data-l]::before { content: attr(data-l); color: var(--text-muted); font-weight: 600; }
        .smc-table td[data-l]:empty { display: none; }
    }
    .sm-desc { font-size: .78rem; color: var(--text-muted); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 520px; }
    .sm-overlay { position: fixed; inset: 0; background: rgba(15, 23, 42, .55); z-index: 600; display: none; align-items: center; justify-content: center; padding: 1rem; }
    .sm-overlay.show { display: flex; }
    .sm-reader { background: var(--ui-surface, #fff); width: 100%; max-width: 760px; max-height: 90vh; border-radius: 16px; display: flex; flex-direction: column; overflow: hidden; }
    .sm-reader-head { padding: 1.1rem 1.4rem; border-bottom: 1px solid var(--ui-border-soft, var(--border)); display: flex; justify-content: space-between; gap: 1rem; align-items: flex-start; }
    .sm-kicker { font-size: .75rem; font-weight: 700; color: var(--primary); }
    .sm-title { font-size: 1.15rem; font-weight: 800; color: var(--ui-text, #0f172a); margin-top: .15rem; }
    .sm-close { border: 0; background: transparent; font-size: 1.5rem; line-height: 1; cursor: pointer; color: var(--ui-text-2, #64748b); padding: .2rem .4rem; }
    .sm-body { padding: 1.25rem 1.4rem; overflow-y: auto; }
    .sm-foot { padding: .9rem 1.4rem; border-top: 1px solid var(--ui-border-soft, var(--border)); background: var(--ui-surface-2, #f8fafc); display: flex; justify-content: space-between; align-items: center; gap: .75rem; flex-wrap: wrap; }
    .sm-done-note { font-size: .82rem; font-weight: 700; color: #047857; }
    html[data-theme="dark"] .sm-done-note { color: #4ade80; }
    .lesson { color: var(--ui-text, #0f172a); line-height: 1.75; font-size: .95rem; }
    .lesson h3 { font-size: 1rem; font-weight: 800; margin: 1.1rem 0 .4rem; }
    .lesson h4 { font-size: .92rem; font-weight: 700; margin: .9rem 0 .3rem; }
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

<div class="sm-overlay" id="sm-reader" role="dialog" aria-modal="true" aria-labelledby="sm-title" onclick="if (event.target === this) smClose()">
    <div class="sm-reader">
        <div class="sm-reader-head">
            <div>
                <div class="sm-kicker" id="sm-kicker"></div>
                <div class="sm-title" id="sm-title">Loading…</div>
            </div>
            <button class="sm-close" onclick="smClose()" aria-label="Close">&times;</button>
        </div>
        <div class="sm-body">
            <div class="lesson" id="sm-content"></div>
            <div id="sm-file" style="margin-top:1rem;"></div>
        </div>
        <div class="sm-foot">
            <span id="sm-status"></span>
            <div style="display:flex; gap:.5rem;">
                <button class="btn btn-outline btn-sm" id="sm-next" style="display:none;">Next module</button>
                <button class="btn btn-primary btn-sm" id="sm-mark">Mark as done</button>
            </div>
        </div>
    </div>
</div>

<script>
    let smCurrent = null;

    // Module cards (one per module) — open / closed state survives live updates (class "open")
    function smcToggle(key) {
        const c = document.getElementById('smc-' + key);
        if (!c) return;
        const on = c.classList.toggle('open');
        c.querySelector('.smc-head').setAttribute('aria-expanded', on ? 'true' : 'false');
    }
    // Every activity the teacher gave must be visible to every enrolled student:
    // a module (or "Other work") that still has work to do is shown open — not only
    // the module the student is on now. Activities added later (live update) open
    // their module too. A module the student closes stays closed until new work arrives.
    const smSeenWork = {};   // card key -> how many activities it had last time
    function smPending(c) {
        const rows = [...c.querySelectorAll('tr.smc-row:not(.smc-lesson)')];
        return { total: rows.length, todo: rows.filter(r => !r.querySelector('td[data-l="Submitted"] .smc-ok')).length };
    }
    function smOpenCard(c) {
        if (!c || c.classList.contains('open')) return;
        c.classList.add('open');
        const h = c.querySelector('.smc-head'); if (h) h.setAttribute('aria-expanded', 'true');
    }
    function smOpenPending(firstVisit) {
        document.querySelectorAll('.smc[data-key]').forEach(c => {
            const key = c.dataset.key, w = smPending(c), prev = smSeenWork[key];
            smSeenWork[key] = w.total;
            const fresh = prev === undefined ? firstVisit : w.total > prev;   // first visit, or new work arrived
            if (fresh && w.todo > 0) smOpenCard(c);
        });
    }
    document.addEventListener('DOMContentLoaded', () => {
        // the module the student is on now (first one not completed) …
        document.querySelectorAll('.acc-body').forEach(body => {
            const cur = [...body.querySelectorAll('.smc[data-id]:not([data-id=""])')].find(c => !smcComplete(c));
            if (cur && body.classList.contains('open')) smOpenCard(cur);
        });
        // … plus every module with activities still to do
        smOpenPending(true);
    });
    // Real time: the page updates itself in place (includes/live.php) — open new work
    window.elmsAfterLiveUpdate = function () { smOpenPending(false); };
    // Click anywhere on a row: the lesson opens in the reader, work opens its page
    function smGo(e, row) { if (e.target.closest('a, button')) return; const a = row.querySelector('a.smc-item'); if (a) a.click(); }
    // ?open=ID (from Previous / Continue on an assignment or quiz page): open that lesson
    document.addEventListener('DOMContentLoaded', () => {
        const id = Number(new URLSearchParams(location.search).get('open') || 0);
        if (!id) return;
        const card = smRow(id);
        if (card && !card.classList.contains('open')) smcToggle(card.dataset.key.replace('smc-', ''));
        smOpen(id);
    });
    function smcComplete(c) { return c.dataset.status === 'done' && Number(c.dataset.wdone || 0) >= Number(c.dataset.wtotal || 0); }

    function smRow(id) { return document.querySelector(`.smc[data-id="${Number(id)}"]`); }

    // The lesson was opened / finished in the reader: update its card without a reload
    function smSetRow(id, state) {
        const card = smRow(id);
        if (!card) return;
        const prev = card.dataset.status;
        if (prev === 'done' || prev === state) return;
        const wasComplete = smcComplete(card);
        card.dataset.status = state;
        const lst = card.querySelector('.smc-lesson .smc-lst');
        if (lst && state === 'done') lst.innerHTML = <?= json_encode($smCheck) ?>;
        const complete = smcComplete(card);
        const num = card.querySelector('.sm-num');
        if (num) { num.classList.remove('opened', 'done'); num.classList.add(complete ? 'done' : 'opened'); }
        const st = card.querySelector('.smc-state');
        if (st) { st.className = 'smc-state badge ' + (complete ? 'badge-green' : 'badge-blue'); st.textContent = complete ? 'Completed' : 'In progress'; }
        if (complete && !wasComplete) {
            const acc = card.closest('.accordion');
            if (acc) {
                const done = Number(acc.dataset.done || 0) + 1, total = Number(acc.dataset.total || 1);
                acc.dataset.done = done;
                const c = acc.querySelector('.mod-count'); if (c) c.textContent = done;
                const p = acc.querySelector('.mod-pct'); if (p) p.textContent = Math.round(done / total * 100) + '%';
            }
        }
    }

    async function smOpen(id) {
        const box = document.getElementById('sm-reader');
        document.getElementById('sm-kicker').textContent = '';
        document.getElementById('sm-title').textContent = 'Loading…';
        document.getElementById('sm-content').innerHTML = '';
        document.getElementById('sm-file').innerHTML = '';
        document.getElementById('sm-status').innerHTML = '';
        document.getElementById('sm-next').style.display = 'none';
        document.getElementById('sm-mark').style.display = 'none';
        box.classList.add('show');
        try {
            const res = await fetch('../api/module_progress.php?action=open', {
                method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ module_id: id })
            });
            const d = await res.json();
            if (!d.success) { document.getElementById('sm-title').textContent = d.message || 'Could not open this module.'; return; }
            const m = smCurrent = d.data;
            document.getElementById('sm-kicker').textContent = (m.course_name || '') + (m.number ? ' · Module ' + m.number : '');
            document.getElementById('sm-title').textContent = m.title;
            document.getElementById('sm-content').innerHTML = m.content || (m.description ? `<p>${smEsc(m.description)}</p>` : '');
            if (m.file_path) {
                document.getElementById('sm-file').innerHTML =
                    `<a class="btn btn-outline btn-sm" target="_blank" rel="noopener" href="../${encodeURI(m.file_path)}">Open attached file</a>`;
            }
            smShowDone(m.done_on);
            const next = document.getElementById('sm-next');
            if (m.next_id) { next.style.display = ''; next.onclick = () => smOpen(m.next_id); }
            smSetRow(m.id, m.done_on ? 'done' : 'opened');
            if (typeof notifyProgressChanged === 'function') notifyProgressChanged();
        } catch (e) {
            document.getElementById('sm-title').textContent = 'Network error. Try again.';
        }
    }

    function smShowDone(doneOn) {
        const mark = document.getElementById('sm-mark');
        const status = document.getElementById('sm-status');
        if (doneOn) { mark.style.display = 'none'; status.innerHTML = `<span class="sm-done-note">Done · ${smEsc(doneOn)}</span>`; }
        else { mark.style.display = ''; mark.disabled = false; mark.textContent = 'Mark as done'; status.innerHTML = ''; }
    }

    document.getElementById('sm-mark').addEventListener('click', async function () {
        if (!smCurrent) return;
        this.disabled = true; this.textContent = 'Saving…';
        try {
            const res = await fetch('../api/module_progress.php?action=mark', {
                method: 'POST', headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ module_id: smCurrent.id, course_id: smCurrent.course_id })
            });
            const d = await res.json();
            if (!d.success) { this.disabled = false; this.textContent = 'Mark as done'; alert(d.message || 'Could not save.'); return; }
            smShowDone('today');
            smSetRow(smCurrent.id, 'done');
            if (typeof notifyProgressChanged === 'function') notifyProgressChanged();
        } catch (e) { this.disabled = false; this.textContent = 'Mark as done'; alert('Network error. Please try again.'); }
    });

    function smClose() { document.getElementById('sm-reader').classList.remove('show'); smCurrent = null; }
    document.addEventListener('keydown', e => { if (e.key === 'Escape') smClose(); });
    // Subject filter (only shown on "All subjects"); kept here so the dropdown never throws
    if (typeof window.filterMod !== 'function') {
        window.filterMod = function () {
            const code = document.getElementById('mod-filter-course')?.value || '';
            document.querySelectorAll('.accordion.sm-acc[data-code]').forEach(acc => {
                acc.style.display = !code || acc.dataset.code === code ? '' : 'none';
            });
        };
    }
    function smEsc(s) { const d = document.createElement('div'); d.textContent = s ?? ''; return d.innerHTML; }
</script>
<?php require __DIR__ . '/../includes/footer.php'; ?>