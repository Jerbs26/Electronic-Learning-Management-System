<?php
// ============================================================
//  Arandia College eLMS — Student: Assignments
//  File: student/assignments.php
//  Everything the teacher gave in one card per subject: assignments and
//  quizzes together (the old Quizzes page, student/quizzes.php, forwards here).
//  Data comes from student/_init.php (same rules as the teacher side).
// ============================================================
require __DIR__ . '/_init.php';
$currentPage = 'assignments.php';
$pageTitle = 'Assignments';
require __DIR__ . '/../includes/header.php';
$openAll = !empty($activeSubject);   // one subject open: show its work right away

// ── Display helpers (closures, so they can never be redeclared) ──
// Date: drop the year when it's the current year
$saDate = fn($ts) => date(date('Y', $ts) === date('Y') ? 'M j, g:i A' : 'M j, Y g:i A', $ts);
// Number: 95.00 → 95, 92.50 → 92.5
$saNum = fn($n) => rtrim(rtrim(number_format((float) $n, 2, '.', ''), '0'), '.');

// ── Everything the teacher gave, one card per subject: assignments + quizzes together,
//    in the order the teacher posted them — the newest activity is at the bottom ──
$workByCourse = [];
foreach ($myAssignments as $a) $workByCourse[$a['course_code']][] = ['kind' => 'assignment', 'due' => $a['due_date'], 'row' => $a,
    'posted' => (string) ($a['posted_on'] ?? ''), 'done' => !empty($a['submission_id'])];
foreach ($myQuizzes as $q) $workByCourse[$q['course_code']][] = ['kind' => 'quiz', 'due' => $q['close_at'], 'row' => $q,
    'posted' => (string) ($q['posted_on'] ?? ''), 'done' => in_array($q['attempt_status'], ['Submitted', 'Graded'], true)];
foreach ($workByCourse as &$__list) {
    // oldest → newest by posting time (same time: assignments before quizzes, then by id)
    usort($__list, fn($x, $y) => [$x['posted'], $x['kind'], (int) $x['row']['id']] <=> [$y['posted'], $y['kind'], (int) $y['row']['id']]);
}
unset($__list);
// same subject order as before (by subject name)
uasort($workByCourse, fn($x, $y) => strcasecmp($x[0]['row']['course_name'], $y[0]['row']['course_name']));

// ── One state model for assignments AND quizzes, so both read the same way ──
//    state → tile colour · label → pill text · group → filter tab (todo | done | missed)
$saIn = function ($ts) {   // "in 5 hr" style hint, used only for work due within 2 days
    $d = $ts - time();
    if ($d < 3600) return 'in ' . max(1, (int) ceil($d / 60)) . ' min';
    if ($d < 86400) return 'in ' . (int) floor($d / 3600) . ' hr';
    return 'tomorrow';
};
$saClassify = function (array $w) use ($saNum) {
    $r = $w['row'];
    if ($w['kind'] === 'assignment') {
        $hasSub = !empty($r['submission_id']);
        $max = (float) $r['max_score'];
        $graded = $r['submission_status'] === 'Graded' && $r['submission_score'] !== null;
        $late = $r['submission_status'] === 'Late';
        $dueTs = $r['due_date'] ? strtotime($r['due_date']) : false;
        $passed = $dueTs !== false && $dueTs < time();
        $pct = ($graded && $max > 0) ? (int) round(((float) $r['submission_score'] / $max) * 100) : null;
        $used = (int) $r['attempt_count'];
        $cap = $r['max_attempts'] !== null ? (int) $r['max_attempts'] : null;
        if ($graded)      [$st, $lb, $bd] = [($pct === null || $pct >= 75) ? 'graded-good' : 'graded-low', 'Graded', ($pct === null || $pct >= 75) ? 'badge-green' : 'badge-yellow'];
        elseif ($hasSub)  [$st, $lb, $bd] = $late ? ['late', 'Late', 'badge-yellow'] : ['submitted', 'Submitted', 'badge-blue'];
        elseif ($passed)  [$st, $lb, $bd] = ['missed', 'Missed', 'badge-red'];
        else              [$st, $lb, $bd] = ['todo', 'To do', 'badge-gray'];
        return ['state' => $st, 'label' => $lb, 'badge' => $bd, 'pct' => $pct,
            'group' => $st === 'missed' ? 'missed' : (in_array($st, ['todo'], true) ? 'todo' : 'done'),
            'score' => $graded ? $saNum($r['submission_score']) . '/' . $saNum($max) : '',
            'dueTs' => $dueTs, 'closed' => $passed, 'max' => $max, 'hasSub' => $hasSub,
            'exhausted' => $cap !== null && $used >= $cap,
            'attempts' => $cap !== null ? "Attempts $used/$cap" : ($used > 0 ? $used . ' attempt' . ($used === 1 ? '' : 's') : '')];
    }
    $done = in_array($r['attempt_status'], ['Submitted', 'Graded'], true);
    $prog = $r['attempt_status'] === 'In Progress';
    $pct = ($done && $r['max_score'] > 0 && $r['my_score'] !== null) ? (int) round(($r['my_score'] / $r['max_score']) * 100) : null;
    $pass = $r['passing_score'] !== null ? (float) $r['passing_score'] : 75;
    $openTs = !empty($r['open_at']) ? strtotime($r['open_at']) : false;
    $closeTs = !empty($r['close_at']) ? strtotime($r['close_at']) : false;
    $notYet = $openTs !== false && $openTs > time() && !$done && !$prog;
    $closed = $closeTs !== false && $closeTs < time() && !$done && !$prog;
    if ($done && $pct !== null) [$st, $lb, $bd] = [$pct >= $pass ? 'graded-good' : 'graded-low', 'Graded', $pct >= $pass ? 'badge-green' : 'badge-yellow'];
    elseif ($done)              [$st, $lb, $bd] = ['submitted', $r['attempt_status'] === 'Graded' ? 'Graded' : 'Submitted', 'badge-blue'];
    elseif ($prog)              [$st, $lb, $bd] = ['progress', 'In progress', 'badge-blue'];
    elseif ($closed)            [$st, $lb, $bd] = ['missed', 'Missed', 'badge-red'];
    elseif ($notYet)            [$st, $lb, $bd] = ['upcoming', 'Upcoming', 'badge-gray'];
    else                        [$st, $lb, $bd] = ['todo', 'To do', 'badge-gray'];
    return ['state' => $st, 'label' => $lb, 'badge' => $bd, 'pct' => $pct,
        'group' => $st === 'missed' ? 'missed' : ($done ? 'done' : 'todo'),
        'score' => $pct !== null ? $saNum($r['my_score']) . '/' . $saNum($r['max_score']) : '',
        'openTs' => $openTs, 'dueTs' => $closeTs, 'closed' => $closed, 'notYet' => $notYet,
        'done' => $done, 'prog' => $prog, 'isFile' => ($r['quiz_type'] ?? 'questions') === 'file'];
};
$saCount = ['all' => 0, 'todo' => 0, 'done' => 0, 'missed' => 0];
foreach ($workByCourse as &$__list) {
    foreach ($__list as &$__w) {
        $__w['ui'] = $saClassify($__w);
        $saCount['all']++;
        $saCount[$__w['ui']['group']]++;
    }
    unset($__w);
}
unset($__list);
$saPctAll = $saCount['all'] > 0 ? (int) round($saCount['done'] / $saCount['all'] * 100) : 0;
$saNA = 0; $saNQ = 0;
foreach ($workByCourse as $__list) foreach ($__list as $__w) { if ($__w['kind'] === 'assignment') $saNA++; else $saNQ++; }

// ── Small render helpers (closures, so a live refresh can never redeclare them) ──
$saIcon = fn($kind) => $kind === 'quiz'
    ? '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="1"/><path d="M9 12h6M9 16h4"/></svg>'
    : '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>';
$saDownloadIcon = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>';
// Due-date segment: amber when due within 2 days, red once missed
$saDueSeg = function ($ts, $group) use ($saDate, $saIn) {
    if ($ts === false) return ['No due date', ''];
    $txt = 'Due ' . $saDate($ts);
    $d = $ts - time();
    if ($group === 'todo' && $d > 0 && $d <= 172800) return [$txt . ' (' . $saIn($ts) . ')', 'is-soon'];
    return [$txt, $group === 'missed' ? 'is-late' : ''];
};
// Meta line: [text, class] segments, separated by a dot in CSS
$saMeta = function (array $segs) {
    $o = '';
    foreach ($segs as $s) if ($s[0] !== '') $o .= '<span class="sa-seg' . ($s[1] ? ' ' . $s[1] : '') . '">' . htmlspecialchars($s[0]) . '</span>';
    return $o;
};

?>
<style>
    /* ═════════════════════════════════════════════════════════
       Assignments list — built only from the shared --ui-* tokens,
       so it follows light / dark mode and matches the other pages.
       Layout of every row:  [type tile + title/meta] [status] [score] [action]
       ═════════════════════════════════════════════════════════ */

    /* Summary under the page title */
    .sa-summary { display: flex; align-items: center; flex-wrap: wrap; gap: .5rem 1rem; margin-top: .35rem; }
    .sa-sum { font-size: .88rem; color: var(--ui-text-2); }
    .sa-sum strong { color: var(--ui-text); font-weight: 700; }
    .sa-progress { width: 160px; height: 6px; border-radius: 999px; background: var(--ui-surface-3); overflow: hidden; }
    .sa-progress > span { display: block; height: 100%; border-radius: inherit; background: var(--ui-link); }

    /* Toolbar: status filter + subject picker */
    .sa-toolbar { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: .75rem 1rem; margin: 0 0 1.25rem; }
    .sa-tabs { display: inline-flex; gap: 2px; padding: 3px; background: var(--ui-surface); border: 1px solid var(--ui-border); border-radius: 10px; }
    .sa-tab { display: inline-flex; align-items: center; gap: .45rem; min-height: 32px; padding: 0 .8rem; border: 0; border-radius: 8px; background: transparent; color: var(--ui-text-2); font: 700 .8rem/1 var(--ui-font-head); white-space: nowrap; flex: 0 0 auto; cursor: pointer; transition: background .12s, color .12s; }
    .sa-tab:hover { background: var(--ui-surface-2); color: var(--ui-text); }
    .sa-tab.is-active { background: var(--ui-primary-soft); color: var(--ui-link); }
    .sa-tab:focus-visible { outline: 2px solid var(--ui-link); outline-offset: 1px; }
    .sa-count { min-width: 1.35rem; padding: .12rem .4rem; border-radius: 999px; background: var(--ui-surface-3); color: var(--ui-text-2); font-size: .7rem; font-weight: 700; text-align: center; font-variant-numeric: tabular-nums; }
    .sa-tab.is-active .sa-count { background: var(--ui-surface); color: var(--ui-link); }
    .sa-course { min-width: 200px; min-height: 40px; }
    .sa-empty { padding: 2.5rem 1rem; text-align: center; color: var(--ui-text-2); }
    .sa-empty[hidden] { display: none; }

    .sa-flat > .acc-body { border-top: 0; background: transparent; }

    /* Row — fixed columns so status, score and action line up on every row */
    .acc-row.sa-row {
        --t-bg: var(--ui-surface-3); --t-fg: var(--ui-text-2);
        display: grid; grid-template-columns: minmax(0, 1fr) 124px 76px 152px;
        align-items: center; column-gap: 1rem;
        min-height: 76px; padding: .9rem 1.25rem;
        background: var(--ui-surface); border-bottom: 1px solid var(--ui-border-soft);
        transition: background .12s;
    }
    .acc-row.sa-row:last-child { border-bottom: 0; }
    .acc-row.sa-row[data-href] { cursor: pointer; }
    .acc-row.sa-row[data-href]:hover { background: var(--ui-surface-2); }

    /* State colours drive the tile; the glyph inside always shows the type */
    .sa-row.st-graded-good { --t-bg: var(--ui-success-soft); --t-fg: var(--ui-success); }
    .sa-row.st-graded-low, .sa-row.st-late { --t-bg: var(--ui-warning-soft); --t-fg: var(--ui-warning); }
    .sa-row.st-submitted, .sa-row.st-progress { --t-bg: var(--ui-primary-soft); --t-fg: var(--ui-link); }
    .sa-row.st-missed { --t-bg: var(--ui-danger-soft); --t-fg: var(--ui-danger); }

    .sa-main { display: flex; align-items: center; gap: .9rem; min-width: 0; }
    .sa-ic { width: 38px; height: 38px; flex: 0 0 38px; border-radius: 10px; display: flex; align-items: center; justify-content: center; background: var(--t-bg); color: var(--t-fg); }
    .sa-ic svg { width: 18px; height: 18px; }
    .sa-text { min-width: 0; }
    .sa-title { font-family: var(--ui-font-head); font-weight: 700; font-size: .95rem; line-height: 1.3; }
    .sa-link { display: block; color: var(--ui-text); text-decoration: none; overflow-wrap: anywhere; }
    .sa-link:hover { color: var(--ui-link); }
    .sa-link:focus-visible { outline: 2px solid var(--ui-link); outline-offset: 2px; border-radius: 4px; }

    .sa-meta { display: flex; flex-wrap: wrap; margin-top: .2rem; font-size: .78rem; line-height: 1.4; color: var(--ui-text-2); }
    .sa-seg:not(:last-child)::after { content: "·"; margin: 0 .45rem; color: var(--ui-text-3); }
    .sa-seg.is-type { font-weight: 600; color: var(--ui-text); }
    .sa-seg.is-soon { font-weight: 600; color: #92400e; }
    .sa-seg.is-late { font-weight: 600; color: var(--ui-danger); }
    html[data-theme="dark"] .sa-seg.is-soon { color: var(--ui-warning); }

    /* Status pill: same size and shape for every state */
    .sa-state { display: contents; }
    .sa-row .badge { display: inline-flex; align-items: center; gap: .4rem; justify-self: start; padding: .22rem .65rem; border-radius: 999px; font-size: .72rem; font-weight: 700; line-height: 1.3; white-space: nowrap; }
    .sa-row .badge::before { content: ""; width: 6px; height: 6px; border-radius: 50%; background: currentColor; }
    /* a little more contrast than the global pastel pills (dark mode keeps its own colours) */
    .sa-row .badge-green { color: #047857; }
    .sa-row .badge-yellow { color: #92400e; }
    .sa-row .badge-red { color: #b91c1c; }

    .sa-score { justify-self: end; font-family: var(--ui-font-head); font-weight: 800; font-size: .92rem; color: var(--ui-text); font-variant-numeric: tabular-nums; white-space: nowrap; }

    /* Actions: buttons only, one slot, one width */
    .sa-row .quiz-actions { display: flex; gap: .4rem; justify-content: flex-end; align-items: center; }
    .sa-row .quiz-actions .btn { min-width: 88px; text-decoration: none; }
    .sa-row .quiz-actions .btn.sa-iconbtn { min-width: 0; width: 30px; padding: 0; }
    .sa-row .quiz-actions .btn.sa-iconbtn svg { width: 15px; height: 15px; }

    @media (max-width: 760px) {
        .acc-row.sa-row { grid-template-columns: minmax(0, 1fr) auto; row-gap: .65rem; }
        .sa-main { grid-column: 1 / -1; }
        .sa-state { display: flex; align-items: center; gap: .75rem; }
        .sa-score { justify-self: start; }
        .sa-toolbar { align-items: stretch; }
        .sa-toolbar { min-width: 0; }
        .sa-tabs { width: 100%; max-width: 100%; box-sizing: border-box; overflow-x: auto; scrollbar-width: none; }
        .sa-course { width: 100%; }
    }
</style>

<div class="section-panel active">
    <div class="page-header">
        <h2 class="page-title">Assignments</h2>
    </div>

    <?php if ($workByCourse): ?>
    <div class="sa-toolbar">
        <div class="sa-tabs" role="group" aria-label="Filter by status">
            <?php foreach (['all' => 'All', 'todo' => 'To do', 'done' => 'Completed', 'missed' => 'Missed'] as $fk => $fl): ?>
                <button type="button" class="sa-tab<?= $fk === 'all' ? ' is-active' : '' ?>" data-f="<?= $fk ?>" aria-pressed="<?= $fk === 'all' ? 'true' : 'false' ?>" onclick="saSetFilter('<?= $fk ?>')"><?= $fl ?> <span class="sa-count"><?= $saCount[$fk] ?></span></button>
            <?php endforeach; ?>
        </div>
        <select id="assign-filter-course" class="form-control sa-course" aria-label="Filter by subject" onchange="filterAssign()"<?= !empty($activeSubject) ? ' style="display:none" aria-hidden="true"' : '' ?>>
            <option value="">All subjects</option>
            <?php foreach ($mySubjects as $s): ?>
                <option value="<?= htmlspecialchars($s['course_code']) ?>"><?= htmlspecialchars($s['course_code']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <?php endif; ?>

    <?php if (!$workByCourse): ?>
        <div class="card sa-empty">
            No assignments or quizzes posted yet<?= !empty($activeSubject) ? ' in ' . htmlspecialchars($activeSubject['course_name']) : '' ?>.
        </div>
    <?php endif; ?>

    <?php $ai = 0;
    foreach ($workByCourse as $courseCode => $items):
        $done = count(array_filter($items, fn($w) => $w['ui']['group'] === 'done'));
        $total = count($items);
        $pct = $total > 0 ? round($done / $total * 100) : 0;
        $grad = !empty($thumbGrads) ? $thumbGrads[$ai % count($thumbGrads)] : 'var(--primary)';
        $bid = 'ab' . $ai;
        $ai++;
        $nA = count(array_filter($items, fn($w) => $w['kind'] === 'assignment'));
        $nQ = $total - $nA;
        ?>
        <div class="accordion<?= $openAll ? ' sa-flat' : '' ?>" data-key="work-<?= htmlspecialchars($courseCode) ?>" data-code="<?= htmlspecialchars($courseCode) ?>" data-total="<?= $total ?>" data-done="<?= $done ?>">
            <?php if (!$openAll): // all subjects: one card per subject with its name ?>
            <div class="acc-head" onclick="toggleBlock('<?= $bid ?>')">
                <div class="acc-left">
                    <div class="acc-icon" style="background:<?= $grad ?>">
                        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                            <polyline points="14 2 14 8 20 8"></polyline>
                            <line x1="16" y1="13" x2="8" y2="13"></line>
                            <line x1="16" y1="17" x2="8" y2="17"></line>
                        </svg>
                    </div>
                    <div>
                        <div class="acc-title"><?= htmlspecialchars($items[0]['row']['course_name']) ?></div>
                        <div class="acc-meta"><span class="quiz-count"><?= $done ?></span>/<?= $total ?> completed
                            · <?= $nA ?> assignment<?= $nA === 1 ? '' : 's' ?> · <?= $nQ ?> quiz<?= $nQ === 1 ? '' : 'zes' ?></div>
                    </div>
                </div>
                <div style="display:flex; align-items:center; gap:1rem;">
                    <span class="quiz-pct" style="font-weight:700; color:var(--primary);"><?= $pct ?>%</span>
                    <svg class="icon" style="transition:0.3s;" id="chev-<?= $bid ?>" viewBox="0 0 24 24">
                        <polyline points="6 9 12 15 18 9"></polyline>
                    </svg>
                </div>
            </div>
            <?php endif; ?>
            <div class="acc-body<?= $openAll ? ' open' : '' ?>" id="<?= $bid ?>">
                <?php foreach ($items as $w):
                    $ui = $w['ui'];
                    $isQuiz = $w['kind'] === 'quiz';
                    $r = $w['row'];
                    $href = 'work.php?type=' . $w['kind'] . '&amp;id=' . (int) $r['id'] . '&amp;course=' . (int) $r['course_id'];
                    $jsTitle = htmlspecialchars(addslashes($r['title']));

                    // Meta line — same order on both kinds: type · size · (attempts / time) · dates
                    if (!$isQuiz) {
                        $segs = [['Assignment', 'is-type'], [$saNum($ui['max']) . ' pts', ''], [$ui['attempts'], ''], $saDueSeg($ui['dueTs'], $ui['group'])];
                    } else {
                        $qn = (int) $r['question_count'];
                        $segs = [['Quiz', 'is-type'],
                            [$ui['isFile'] ? 'File upload' : $qn . ' question' . ($qn === 1 ? '' : 's'), ''],
                            [(int) $r['time_limit'] > 0 ? (int) $r['time_limit'] . ' min' : 'No time limit', '']];
                        if ($ui['notYet']) $segs[] = ['Opens ' . $saDate($ui['openTs']), ''];
                        $segs[] = $saDueSeg($ui['dueTs'], $ui['group']);
                    }
                ?>
                    <div class="acc-row sa-row st-<?= $ui['state'] ?>" data-kind="<?= $w['kind'] ?>" data-id="<?= (int) $r['id'] ?>" data-group="<?= $ui['group'] ?>"
                        data-title="<?= strtolower(htmlspecialchars($r['title'])) ?>" data-href="<?= $href ?>"<?= $isQuiz ? ' data-status="' . htmlspecialchars($r['attempt_status'] ?? 'Not Started') . '"' : '' ?>>
                        <div class="sa-main">
                            <div class="quiz-icon-wrap sa-ic"><?= $saIcon($w['kind']) ?></div>
                            <div class="sa-text">
                                <a class="sa-title sa-link" href="<?= $href ?>"><?= htmlspecialchars($r['title']) ?></a>
                                <div class="sa-meta"><?= $saMeta($segs) ?></div>
                            </div>
                        </div>
                        <div class="sa-state">
                            <span class="quiz-score badge <?= $ui['badge'] ?>"><?= htmlspecialchars($ui['label']) ?></span>
                            <span class="sa-score"<?= $ui['pct'] !== null ? ' title="' . $ui['pct'] . '%"' : '' ?>><?= htmlspecialchars($ui['score']) ?></span>
                        </div>
                        <div class="quiz-actions">
                            <?php if (!$isQuiz): ?>
                                <?php if ($r['file_path']): ?>
                                    <a href="../<?= htmlspecialchars($r['file_path']) ?>" target="_blank" rel="noopener" class="btn btn-outline btn-sm sa-iconbtn" title="Download instructions" aria-label="Download instructions"><?= $saDownloadIcon ?></a>
                                <?php endif; ?>
                                <?php if ($ui['exhausted'] || $ui['closed']): ?>
                                    <a href="<?= $href ?>" class="btn btn-outline btn-sm"<?= $ui['exhausted'] ? ' title="No attempts left"' : '' ?>>View</a>
                                <?php else: ?>
                                    <button class="btn btn-primary btn-sm" onclick="openAssignSubmitModal(<?= (int) $r['id'] ?>,'<?= $jsTitle ?>')"><?= $ui['hasSub'] ? 'Resubmit' : 'Submit' ?></button>
                                <?php endif; ?>
                            <?php else: ?>
                                <?php if ($ui['closed'] || $ui['notYet'] || $ui['done']): ?>
                                    <a href="<?= $href ?>" class="btn btn-outline btn-sm">View</a>
                                <?php elseif ($ui['isFile']): ?>
                                    <?php if (!empty($r['file_path'])): ?>
                                        <a href="../<?= htmlspecialchars($r['file_path']) ?>" target="_blank" rel="noopener" class="btn btn-outline btn-sm sa-iconbtn" title="Download quiz file" aria-label="Download quiz file"><?= $saDownloadIcon ?></a>
                                    <?php endif; ?>
                                    <button class="btn btn-primary btn-sm" onclick="openSubmitModal(<?= (int) $r['id'] ?>,'<?= $jsTitle ?>')">Upload</button>
                                <?php elseif ($ui['prog']): // no resume: a quiz left open is submitted (includes/quiz_attempts.php) ?>
                                    <button class="btn btn-outline btn-sm" disabled title="This quiz is open in another window">In progress</button>
                                <?php else: ?>
                                    <button class="btn btn-primary btn-sm" onclick="startQuiz(<?= (int) $r['id'] ?>,'<?= $jsTitle ?>',<?= (int) $r['time_limit'] ?>)">Start</button>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endforeach; ?>

    <?php if ($workByCourse): ?>
        <div class="card sa-empty" id="sa-empty" hidden>Nothing matches this filter.</div>
    <?php endif; ?>
</div>

<!-- Submit assignment modal -->
<script src="../assets/js/elms-upload-rules.js"></script>
<div class="modal-overlay hidden" id="assignSubmitModal">
    <div class="modal">
        <div class="modal-header">
            <span>Submit Assignment</span>
            <button type="button" aria-label="Close" onclick="document.getElementById('assignSubmitModal').classList.add('hidden')"
                style="background:none;border:none;font-size:1.5rem;cursor:pointer;color:inherit;">&times;</button>
        </div>
        <div class="modal-body">
            <p id="assign-submit-name" style="margin-bottom:1rem; font-weight:600;"></p>
            <label style="display:block; margin-bottom:0.5rem; font-weight:500;">Choose File</label>
            <input type="file" id="assign-submit-file" class="form-control" style="width:100%;" accept=".pdf,.docx,.pptx,.xlsx,.jpg,.jpeg,.png,.mp4">
            <div style="font-size:0.78rem; opacity:0.7; margin-top:0.4rem;">PDF, DOCX, PPTX, XLSX &middot; JPG, PNG (max 20 MB) &middot; MP4 (max 100 MB)</div>
            <div id="assign-submit-err" style="color:var(--danger); font-size:0.85rem; margin-top:0.5rem; display:none;"></div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-outline" onclick="document.getElementById('assignSubmitModal').classList.add('hidden')">Cancel</button>
            <button class="btn btn-primary" id="assign-submit-btn" onclick="submitAssignmentFile()">Upload File</button>
        </div>
    </div>
</div>

<script>
    // Click anywhere on a row (not on its buttons / links) → open that assignment or quiz
    document.addEventListener('click', e => {
        const row = e.target.closest('.acc-row.sa-row[data-href]');
        if (!row || e.target.closest('a, button, input, label')) return;
        location.href = row.dataset.href;
    });
    let currentAssignmentId = null;

    // Status filter (All / To do / Completed / Missed). Kept on window so a live refresh keeps it.
    window.saFilter = window.saFilter || 'all';
    function saSetFilter(f) { window.saFilter = f; filterAssign(); }

    function filterAssign() {
        const q = (document.getElementById('assign-search')?.value || '').toLowerCase();
        const courseFilter = document.getElementById('assign-filter-course')?.value || '';
        const status = window.saFilter || 'all';
        const cards = document.querySelectorAll('.accordion[data-code]');
        let shown = 0;
        cards.forEach(acc => {
            const code = acc.dataset.code;
            let anyVisible = false;
            acc.querySelectorAll('.acc-row').forEach(row => {
                const matchQ = !q || (row.dataset.title || '').includes(q);
                const matchCourse = !courseFilter || courseFilter === code;
                const matchStatus = status === 'all' || row.dataset.group === status;
                const show = matchQ && matchCourse && matchStatus;
                row.style.display = show ? '' : 'none';
                if (show) { anyVisible = true; shown++; }
            });
            acc.style.display = anyVisible ? '' : 'none';
        });
        document.querySelectorAll('.sa-tab').forEach(btn => {
            const on = btn.dataset.f === status;
            btn.classList.toggle('is-active', on);
            btn.setAttribute('aria-pressed', on ? 'true' : 'false');
        });
        const empty = document.getElementById('sa-empty');
        if (empty) empty.hidden = shown > 0 || cards.length === 0;
    }

    function openAssignSubmitModal(id, title) {
        currentAssignmentId = id;
        document.getElementById('assign-submit-name').innerText = title;
        document.getElementById('assign-submit-file').value = '';
        document.getElementById('assign-submit-err').style.display = 'none';
        document.getElementById('assignSubmitModal').classList.remove('hidden');
    }

    async function submitAssignmentFile() {
        const fileInput = document.getElementById('assign-submit-file');
        const errEl = document.getElementById('assign-submit-err');
        errEl.style.display = 'none';
        if (!currentAssignmentId) { errEl.textContent = 'Something went wrong. Close this and try again.'; errEl.style.display = 'block'; return; }
        if (fileInput.files.length === 0) { errEl.textContent = 'Please choose a file.'; errEl.style.display = 'block'; return; }
        { const bad = ElmsUpload.check(fileInput.files[0]); if (bad) { errEl.textContent = bad; errEl.style.display = 'block'; return; } }

        const fd = new FormData();
        fd.append('assignment_id', currentAssignmentId);
        fd.append('file', fileInput.files[0]);

        const btn = document.getElementById('assign-submit-btn');
        const originalText = btn.innerText;
        btn.disabled = true; btn.innerText = 'Uploading...';

        try {
            const res = await fetch('../api/assignments.php?action=submit', { method: 'POST', body: fd });
            const data = await res.json();
            if (data.success) {
                document.getElementById('assignSubmitModal').classList.add('hidden');
                if (typeof notifyProgressChanged === 'function') notifyProgressChanged();
                // show the new status right away (in place; full reload as a fallback)
                setTimeout(() => { if (window.ElmsLive && ElmsLive.refreshNow) ElmsLive.refreshNow(); else location.reload(); }, 300);
            } else {
                errEl.textContent = data.message || 'Upload failed.';
                errEl.style.display = 'block';
            }
        } catch (e) {
            errEl.textContent = 'Network error. Please try again.';
            errEl.style.display = 'block';
        } finally {
            btn.disabled = false; btn.innerText = originalText;
        }
    }
</script>

<?php require __DIR__ . '/../includes/footer.php'; ?>