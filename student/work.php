<?php
// ============================================================
//  Arandia College eLMS — Student: one assignment or quiz
//  File: student/work.php?type=assignment|quiz&id=ID&course=COURSE_ID[&tab=submissions]
//  Opened from Modules (click a row). Same layout as the teacher's page:
//    Top:   Previous / module title / Continue (the module's lesson + work, in order)
//    Tabs:  Instructions · Submissions (my own work)
//    Side:  details card + my score, with Submit / Start quiz
//  Data comes from student/_init.php ($myAssignments / $myQuizzes / $myModules),
//  so a student only ever sees work given to their own section.
// ============================================================
require __DIR__ . '/_init.php';
require_once __DIR__ . '/../includes/upload_names.php';   // show original file names
$currentPage = 'modules.php';
$uid = (int) $user_id;

$type = ($_GET['type'] ?? '') === 'quiz' ? 'quiz' : 'assignment';
$isQuiz = $type === 'quiz';
$id = (int) ($_GET['id'] ?? 0);
$tab = ($_GET['tab'] ?? '') === 'submissions' ? 'submissions' : 'instructions';

$item = null;
foreach ($isQuiz ? $myQuizzes : $myAssignments as $r) if ((int) $r['id'] === $id) { $item = $r; break; }
if (!$item) { header('Location: modules.php' . (!empty($activeSubject) ? '?course=' . (int) $activeSubject['id'] : '')); exit; }
$cid = (int) $item['course_id'];

$num = fn($n) => rtrim(rtrim(number_format((float) $n, 2, '.', ''), '0'), '.');
$when = fn(?string $d) => $d ? date('M j, g:i a', strtotime($d)) : null;
$max = (float) $item['max_score'];
$now = time();

// ── Module + Previous / Continue (same order as the Modules page) ──
$module = null;
foreach ($myModules as $m) if ((int) $m['id'] === (int) ($item['module_id'] ?? 0) && (int) $m['course_id'] === $cid) { $module = $m; break; }
$modKey = $module ? (int) $module['id'] : 0;
$modIds = array_map(fn($m) => (int) $m['id'], array_filter($myModules, fn($m) => (int) $m['course_id'] === $cid));
$inThis = function (array $w) use ($cid, $modKey, $modIds): bool {
    if ((int) $w['course_id'] !== $cid) return false;
    $mid = (int) ($w['module_id'] ?? 0);
    return $modKey ? $mid === $modKey : !in_array($mid, $modIds, true);
};
$work = [];
foreach ($myAssignments as $a) if ($inThis($a)) $work[] = ['t' => 'assignment', 'id' => (int) $a['id'], 'due' => $a['due_date'], 'title' => (string) $a['title']];
foreach ($myQuizzes as $q) if ($inThis($q)) $work[] = ['t' => 'quiz', 'id' => (int) $q['id'], 'due' => $q['close_at'], 'title' => (string) $q['title']];
usort($work, fn($x, $y) => [$x['due'] === null, (string) $x['due'], $x['t']] <=> [$y['due'] === null, (string) $y['due'], $y['t']]);
$seq = [];      // links, in order
$seqInfo = [];  // [kind label, title] for the Previous / Next cards
if ($module) { $seq[] = 'modules.php?course=' . $cid . '&open=' . $modKey; $seqInfo[] = ['Lesson', (string) $module['title']]; }
$pos = null;
foreach ($work as $w) {
    if ($w['t'] === $type && $w['id'] === $id) $pos = count($seq);
    $seq[] = 'work.php?type=' . $w['t'] . '&id=' . $w['id'] . '&course=' . $cid;
    $seqInfo[] = [$w['t'] === 'quiz' ? 'Quiz' : 'Assignment', $w['title']];
}
$prev = $pos !== null && $pos > 0 ? $seq[$pos - 1] : null;
$next = $pos !== null && $pos < count($seq) - 1 ? $seq[$pos + 1] : null;
$prevInfo = $prev ? $seqInfo[$pos - 1] : null;
$nextInfo = $next ? $seqInfo[$pos + 1] : null;
$modTitle = $module ? (($module['module_number'] ? (int) $module['module_number'] . '. ' : '') . $module['title']) : 'Other work';

// ── My work on it ──
$details = [];
$action = null;   // [label, onclick] of the main button, or null
$note = null;     // why there is no button
$myScore = null;  // number or null
$scoreHidden = false;
$attempts = [];
if ($isQuiz) {
    $extra = $conn->query("SELECT show_results, show_correct_answers FROM quizzes WHERE id = $id")->fetch_assoc();
    $isFile = ($item['quiz_type'] ?? 'questions') === 'file';
    $res = $conn->query("SELECT id, status, score, file_path, remarks, started_at, submitted_at FROM quiz_attempts
                         WHERE quiz_id = $id AND student_id = $uid ORDER BY started_at DESC");
    while ($res && $r = $res->fetch_assoc()) $attempts[] = $r;
    $as = $item['attempt_status'] ?? null;
    $done = in_array($as, ['Submitted', 'Graded'], true);
    $inProg = $as === 'In Progress';
    $openTs = $item['open_at'] ? strtotime($item['open_at']) : null;
    $closeTs = $item['close_at'] ? strtotime($item['close_at']) : null;
    $scoreHidden = !$isFile && $extra && !(int) $extra['show_results'];
    if ($done && $item['my_score'] !== null && !$scoreHidden) $myScore = (float) $item['my_score'];

    // ── Answer review of my latest finished attempt (online quizzes) ──
    // "Show score" on → each question with my answer and right / wrong;
    // "Also reveal correct answers" on → the correct answer too.
    $review = [];
    $revealCorrect = $extra && (int) $extra['show_correct_answers'] === 1;
    $reviewAttempt = null;
    foreach ($attempts as $at) if (in_array($at['status'], ['Submitted', 'Graded'], true)) { $reviewAttempt = $at; break; }
    if (!$isFile && !$scoreHidden && $reviewAttempt) {
        $aid = (int) $reviewAttempt['id'];
        $res = $conn->query("SELECT qq.id, qq.question_text, qq.question_type, qq.points, qq.identification_answer,
                                    qr.choice_id, qr.answer_text, qr.is_correct, qr.points_earned, qr.needs_grading, qc.choice_text AS my_choice
                             FROM quiz_questions qq
                             LEFT JOIN quiz_responses qr ON qr.question_id = qq.id AND qr.attempt_id = $aid
                             LEFT JOIN quiz_choices qc ON qc.id = qr.choice_id
                             WHERE qq.quiz_id = $id ORDER BY qq.id");
        while ($res && $r = $res->fetch_assoc()) {
            $r['correct'] = null;
            if ($revealCorrect) {
                if ($r['question_type'] === 'essay') $r['correct'] = $r['identification_answer'];
                else {
                    $c = $conn->query("SELECT choice_text FROM quiz_choices WHERE question_id = " . (int) $r['id'] . " AND is_correct = 1 ORDER BY id LIMIT 1")->fetch_assoc();
                    $r['correct'] = $c['choice_text'] ?? null;
                }
            }
            $review[] = $r;
        }
    }

    $details[] = ['type', 'Type: ' . ($isFile ? 'File upload quiz' : 'Online quiz')];
    $details[] = ['score', 'Max score: ' . $num($max)];
    if (!$isFile) $details[] = ['list', 'Questions: ' . (int) $item['question_count']];
    $details[] = ['clock', 'Time limit: ' . ((int) $item['time_limit'] > 0 ? (int) $item['time_limit'] . ' min' : 'None')];
    if ($item['passing_score'] !== null) $details[] = ['flag', 'Passing: ' . $num($item['passing_score']) . '%'];
    $details[] = ['cal', 'Opens: ' . ($when($item['open_at']) ?? 'Right away')];
    $details[] = ['cal', 'Closes: ' . ($when($item['close_at']) ?? 'No closing date')];

    $js = fn($v) => htmlspecialchars(json_encode($v), ENT_QUOTES);
    if ($done) $note = ['ok', 'Completed'];
    elseif ($closeTs && $closeTs < $now && !$inProg) $note = ['x', 'The quiz is closed'];
    elseif ($openTs && $openTs > $now && !$inProg) $note = ['cal', 'Opens ' . $when($item['open_at'])];
    elseif ($isFile) $action = ['Upload answer', 'openSubmitModal(' . $id . ', ' . $js($item['title']) . ')'];
    elseif ($inProg) $note = ['clock', 'This quiz is open in another window'];   // no resume: leaving a quiz submits it
    else $action = ['Start quiz', 'startQuiz(' . $id . ', ' . $js($item['title']) . ', ' . (int) $item['time_limit'] . ')'];
} else {
    $extra = $conn->query("SELECT created_at FROM assignments WHERE id = $id")->fetch_assoc();
    $hasSub = !empty($item['submission_id']);
    $graded = $hasSub && $item['submission_score'] !== null;
    if ($graded) $myScore = (float) $item['submission_score'];
    $used = (int) $item['attempt_count'];
    $maxAtt = $item['max_attempts'] !== null ? (int) $item['max_attempts'] : null;
    $dueTs = $item['due_date'] ? strtotime($item['due_date']) : null;

    $details[] = ['type', 'Type: Dropbox'];
    $details[] = ['score', 'Max score: ' . $num($max)];
    if ($extra) $details[] = ['cal', 'Start: ' . $when($extra['created_at'])];
    $details[] = ['cal', 'Due: ' . ($when($item['due_date']) ?? 'No due date')];
    $details[] = ['list', 'Attempts: ' . $used . ($maxAtt !== null ? ' of ' . $maxAtt : ' (unlimited)')];

    $js = fn($v) => htmlspecialchars(json_encode($v), ENT_QUOTES);
    if ($maxAtt !== null && $used >= $maxAtt) $note = ['x', 'No more submissions are allowed'];
    elseif ($dueTs && $dueTs < $now) $note = ['x', 'No more submissions are allowed'];
    else $action = [$hasSub ? 'Resubmit' : 'Submit assignment', 'openAssignSubmitModal(' . $id . ', ' . $js($item['title']) . ')'];
}
if ($note) $details[] = $note;
elseif ($action) $details[] = ['ok', 'Accepting submissions'];
$pct = ($myScore !== null && $max > 0) ? max(0, min(100, $myScore / $max * 100)) : 0;
$nSubs = $isQuiz ? count(array_filter($attempts, fn($a) => $a['status'] !== 'In Progress')) : (!empty($item['submission_id']) ? 1 : 0);

$instr = $isQuiz ? ($item['description'] ?? '') : (($item['instructions'] ?? '') !== '' ? $item['instructions'] : ($item['description'] ?? ''));
$file = $item['file_path'] ?? '';

$ico = [
    'type'  => '<rect x="3" y="7" width="18" height="13" rx="2"/><path d="M8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><path d="M12 11v5M9.5 13.5 12 11l2.5 2.5"/>',
    'score' => '<path d="M5 20v-4M10 20v-8M15 20v-12M20 20V4"/>',
    'cal'   => '<rect x="3.5" y="5" width="17" height="15" rx="2"/><path d="M3.5 10h17M8 3v4M16 3v4"/>',
    'clock' => '<circle cx="12" cy="12" r="8.5"/><path d="M12 7.5V12l3 2"/>',
    'list'  => '<path d="M9 6h11M9 12h11M9 18h11M4.5 6h.01M4.5 12h.01M4.5 18h.01"/>',
    'flag'  => '<path d="M5 21V4M5 4h11l-2 4 2 4H5"/>',
    'x'     => '<path d="M6 6l12 12M18 6 6 18"/>',
    'ok'    => '<path d="M20 6 9 17l-5-5"/>',
];
$svg = fn(string $k, int $s = 16) => '<svg viewBox="0 0 24 24" width="' . $s . '" height="' . $s . '" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $ico[$k] . '</svg>';

// ── Page model (simple, student-friendly layout below) ────────
$ts = fn($d) => $d ? (strtotime((string) $d) ?: null) : null;
/** "in 3 days" / "2 hours ago" */
$rel = function (int $t) use ($now): string {
    $d = $t - $now; $a = abs($d);
    foreach ([[86400 * 30, 'month'], [86400 * 7, 'week'], [86400, 'day'], [3600, 'hour'], [60, 'minute']] as [$sec, $unit]) {
        if ($a >= $sec) { $n = (int) floor($a / $sec); $x = $n . ' ' . $unit . ($n === 1 ? '' : 's'); return $d > 0 ? 'in ' . $x : $x . ' ago'; }
    }
    return 'now';
};
$hasWork  = $isQuiz ? in_array($item['attempt_status'] ?? null, ['Submitted', 'Graded'], true) : !empty($item['submission_id']);
$isGraded = $myScore !== null;
$deadlineRaw = $isQuiz ? ($item['close_at'] ?? null) : ($item['due_date'] ?? null);
$deadline = $ts($deadlineRaw);
$late = !$isQuiz && ($item['submission_status'] ?? '') === 'Late';
$inProg = $isQuiz && ($item['attempt_status'] ?? null) === 'In Progress';
$notOpen = $isQuiz && !empty($openTs) && $openTs > $now;

// One status in plain words + the one next step for the student
if ($isGraded) {
    $status = ['ok', 'Graded'];
    $hasFb = !$isQuiz ? trim((string) ($item['submission_remarks'] ?? '')) !== '' : (bool) array_filter($attempts, fn($a) => trim((string) ($a['remarks'] ?? '')) !== '');
    $step = ['ok', 'Your ' . ($isQuiz ? 'quiz' : 'work') . ' has been graded. Check your score' . ($hasFb ? ' and your teacher’s feedback' : '') . '.'];
} elseif ($hasWork) {
    $status = ['wait', $late ? 'Submitted late' : 'Submitted'];
    $step = ['wait', $isQuiz && $scoreHidden ? 'Quiz finished. Your teacher has hidden the score for now.' : 'Your ' . ($isQuiz ? 'quiz' : 'work') . ' was submitted. Please wait for your teacher to grade it.'];
} elseif ($inProg) {
    $status = ['now', 'In progress'];
    $step = ['now', 'This quiz is open in another window. Finish it there — leaving it submits your answers.'];
} elseif ($notOpen) {
    $status = ['soon', 'Not open yet'];
    $step = ['soon', 'This quiz opens on ' . $when($item['open_at']) . '.'];
} elseif ($deadline && $deadline < $now) {
    $status = ['miss', $isQuiz ? 'Missed' : 'Missing'];
    $step = ['miss', 'The deadline has passed, so ' . ($isQuiz ? 'the quiz is closed.' : 'submissions are closed.') . ' Talk to your teacher if you need help.'];
} else {
    $status = ['now', $isQuiz ? 'Not taken yet' : 'Not submitted yet'];
    $step = ['now', ($isQuiz ? 'Take the quiz' : 'Submit your work') . ($deadline ? ' before ' . $when($deadlineRaw) . ' (' . $rel($deadline) . ')' : '') . '.'];
}

// Assignment info: [label, value]
$info = [];
if ($isQuiz) {
    $info[] = ['Type', ($item['quiz_type'] ?? '') === 'file' ? 'File upload quiz' : 'Online quiz'];
    $info[] = ['Max score', $num($max) . ' points'];
    if (($item['quiz_type'] ?? '') !== 'file') $info[] = ['Questions', (string) (int) $item['question_count']];
    $info[] = ['Time limit', (int) $item['time_limit'] > 0 ? (int) $item['time_limit'] . ' minutes' : 'No time limit'];
    if ($item['passing_score'] !== null) $info[] = ['Passing score', $num($item['passing_score']) . '%'];
    $info[] = ['Opens', $when($item['open_at']) ?? 'Right away'];
    $info[] = ['Closes', $when($item['close_at']) ?? 'No closing date'];
} else {
    $maxAtt = $item['max_attempts'] !== null ? (int) $item['max_attempts'] : null;      // NULL = unlimited (backend rule)
    $usedBackend = (int) $item['attempt_count'];                                          // what the backend counts
    $usedShown = max($usedBackend, $hasWork ? 1 : 0);                                     // older submissions saved a 0 count
    $info[] = ['Type', 'File upload (dropbox)'];
    $info[] = ['Max score', $num($max) . ' points'];
    if ($extra) $info[] = ['Posted', $when($extra['created_at'])];
    $info[] = ['Due', $when($item['due_date']) ?? 'No due date'];
    $info[] = ['Attempts allowed', $maxAtt === null ? 'Unlimited' : (string) $maxAtt];
    $info[] = ['Attempts used', (string) $usedShown];
    if ($maxAtt !== null) $info[] = ['Attempts left', (string) max(0, $maxAtt - $usedBackend)];
    $info[] = ['Late submissions', 'Not accepted after the due date'];
}
$passMark = $isQuiz && $item['passing_score'] !== null ? (float) $item['passing_score'] : null;
$fileExt = $file ? strtoupper(pathinfo($file, PATHINFO_EXTENSION) ?: 'FILE') : '';

$lastAttempt = null;   // latest finished quiz attempt
if ($isQuiz) foreach ($attempts as $at) if (!empty($at['submitted_at'])) { $lastAttempt = $at; break; }
// Summary strip: 4 facts a student checks first
$strip = $isQuiz ? [
    ['cal', 'Closes', $when($item['close_at']) ?? 'No closing date'],
    ['star', 'Points', $num($max)],
    ['clock', 'Time limit', (int) $item['time_limit'] > 0 ? (int) $item['time_limit'] . ' min' : 'None'],
] : [
    ['cal', 'Due', $when($item['due_date']) ?? 'No due date'],
    ['star', 'Points', $num($max)],
    ['redo', 'Attempts', $usedShown . ' of ' . ($maxAtt === null ? 'unlimited' : $maxAtt)],
];
// Small print under the strip (everything else from the details)
$extraInfo = [];
foreach ($info as [$l, $v]) {
    if (in_array($l, ['Type', 'Max score', 'Due', 'Closes', 'Time limit', 'Attempts allowed', 'Attempts used', 'Attempts left'], true)) continue;
    $extraInfo[] = [$l, $v];
}
array_unshift($extraInfo, [$info[0][0], $info[0][1]]);   // type first

$pageTitle = $item['title'];
require __DIR__ . '/../includes/header.php';
?>
<style>
    /* Student activity page — one column: title, summary strip, "What to do", result bar. Scoped to .sa */
    .sa { --sa-navy: #0b2e6f; --sa-navy-2: #123b8a; --sa-blue: #1e56c7; --sa-gold: #f5b700; --sa-line: rgba(15, 23, 42, .09); --sa-card: var(--ui-surface, #fff); --sa-soft: #f5f7fb;
          --sa-ok: #0f8a5f; --sa-ok-bg: #e4f5ee; --sa-wait: #a16207; --sa-wait-bg: #fdf3dc; --sa-miss: #b42318; --sa-miss-bg: #fdecea; --sa-now: #1d4ed8; --sa-now-bg: #e9f0ff; }
    html[data-theme="dark"] .sa { --sa-navy: #15336f; --sa-navy-2: #1d4185; --sa-blue: #8fb0ff; --sa-line: rgba(255, 255, 255, .09); --sa-soft: rgba(255, 255, 255, .04);
          --sa-ok: #4ade80; --sa-ok-bg: rgba(74, 222, 128, .12); --sa-wait: #fbbf24; --sa-wait-bg: rgba(251, 191, 36, .12); --sa-miss: #f87171; --sa-miss-bg: rgba(248, 113, 113, .12);
          --sa-now: #93b4ff; --sa-now-bg: rgba(147, 180, 255, .12); }
    .sa-wrap { width: 100%; }   /* full width of the content area */
    .sa-crumb { display: flex; flex-wrap: wrap; align-items: center; gap: .4rem; font-size: .82rem; font-weight: 600; color: var(--text-muted); }
    .sa-crumb a { color: inherit; text-decoration: none; }
    .sa-crumb a:hover { color: var(--sa-blue); text-decoration: underline; }
    .sa-crumb b { color: var(--sa-blue); }
    .sa-crumb svg { width: 12px; height: 12px; opacity: .6; }
    .sa-titlerow { display: flex; align-items: center; flex-wrap: wrap; gap: .4rem .75rem; margin: .35rem 0 1.1rem; }
    .sa-title { margin: 0; font-size: 1.6rem; font-weight: 800; letter-spacing: -.015em; line-height: 1.25; color: var(--text-main); overflow-wrap: anywhere; }
    .sa-pill { display: inline-flex; align-items: center; gap: .35rem; font-size: .76rem; font-weight: 800; padding: .25rem .7rem; border-radius: 100px; white-space: nowrap; }
    .sa-pill::before { content: ''; width: 7px; height: 7px; border-radius: 50%; background: currentColor; }
    .sa-pill.ok { background: var(--sa-ok-bg); color: var(--sa-ok); } .sa-pill.wait { background: var(--sa-wait-bg); color: var(--sa-wait); }
    .sa-pill.miss { background: var(--sa-miss-bg); color: var(--sa-miss); } .sa-pill.now, .sa-pill.soon { background: var(--sa-now-bg); color: var(--sa-now); }

    /* Summary strip */
    .sa-strip { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); background: var(--sa-card); border: 1px solid var(--sa-line); border-radius: 12px; overflow: hidden; }
    .sa-cell { display: flex; gap: .7rem; align-items: center; padding: .85rem 1rem; border-left: 1px solid var(--sa-line); min-width: 0; }
    .sa-cell:first-child { border-left: 0; }
    .sa-cell-ico { width: 32px; height: 32px; flex-shrink: 0; border-radius: 8px; display: grid; place-items: center; background: var(--sa-soft); color: var(--sa-blue); }
    .sa-cell-ico svg { width: 17px; height: 17px; }
    .sa-cell small { display: block; font-size: .76rem; font-weight: 500; color: var(--text-muted); margin-bottom: .1rem; }
    .sa-cell b { display: block; font-size: .95rem; font-weight: 700; color: var(--text-main); font-variant-numeric: tabular-nums; overflow-wrap: anywhere; }
    .sa-cell b.ok { color: var(--sa-ok); } .sa-cell b.wait { color: var(--sa-wait); } .sa-cell b.miss { color: var(--sa-miss); } .sa-cell b.now, .sa-cell b.soon { color: var(--sa-now); }
    .sa-fine { display: grid; grid-template-columns: repeat(auto-fit, minmax(min(200px, 100%), 1fr)); gap: .9rem 1.5rem; margin: 1.5rem 0 0; padding: 1rem 0 0; border-top: 1px solid var(--sa-line); }
    .sa-fine div { min-width: 0; }
    .sa-fine dt { font-size: .76rem; color: var(--text-muted); margin-bottom: .15rem; }
    .sa-fine dd { margin: 0; font-size: .88rem; font-weight: 600; color: var(--text-main); overflow-wrap: anywhere; }

    /* What to do */
    .sa-doc { margin-top: 1.1rem; background: var(--sa-card); border: 1px solid var(--sa-line); border-radius: 12px; padding: 1.5rem 1.75rem; }
    .sa-h { margin: 0 0 .75rem; font-size: 1.05rem; font-weight: 700; color: var(--text-main); }
    .wk-body { max-width: 75ch; font-size: .95rem; line-height: 1.75; color: var(--text-main); overflow-wrap: anywhere; }
    .wk-body p { margin: 0 0 .8rem; } .wk-body p:last-child { margin-bottom: 0; }
    .sa-file { display: inline-flex; align-items: center; gap: .8rem; margin-top: 1rem; padding: .55rem .9rem .55rem .55rem; border: 1px solid var(--sa-line); border-radius: 10px;
               text-decoration: none; color: inherit; max-width: 100%; }
    .sa-file:hover { border-color: var(--sa-blue); background: var(--sa-soft); }
    .sa-ext { width: 36px; height: 42px; flex-shrink: 0; border-radius: 6px; display: grid; place-items: center; font-size: .6rem; font-weight: 900; color: #fff; background: #b42318; }
    .sa-file-go { margin-left: .75rem; color: var(--text-muted); display: grid; } .sa-file-go svg { width: 16px; height: 16px; }
    .sa-file:hover .sa-file-go { color: var(--sa-blue); }
    .sa-file-n { min-width: 0; font-size: .88rem; font-weight: 700; color: var(--text-main); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .sa-file-n small { display: block; font-size: .74rem; font-weight: 500; color: var(--text-muted); }
    .wk-empty { color: var(--text-muted); font-size: .92rem; }

    /* Result bar */
    .sa-result { margin-top: 1rem; display: grid; grid-template-columns: auto minmax(0, 1fr) auto; gap: 1.25rem; align-items: center; padding: 1.25rem 1.5rem; border-radius: 16px;
                 background: linear-gradient(120deg, var(--sa-navy), var(--sa-navy-2)); color: #fff; }
    .sa-result.miss { background: linear-gradient(120deg, #7a1d16, #9b2a20); }
    .sa-ring { --p: 0; width: 76px; height: 76px; border-radius: 50%; display: grid; place-items: center; background: conic-gradient(var(--sa-gold) calc(var(--p) * 1%), rgba(255, 255, 255, .16) 0); }
    .sa-ring span { width: 60px; height: 60px; border-radius: 50%; display: grid; place-items: center; background: var(--sa-navy); font-size: 1.35rem; font-weight: 900; }
    .sa-result.miss .sa-ring span { background: #85221a; }
    .sa-ring svg { width: 26px; height: 26px; }
    .sa-r-t { font-size: 1.05rem; font-weight: 800; }
    .sa-r-s { font-size: .82rem; opacity: .8; margin-top: .2rem; overflow-wrap: anywhere; }
    .sa-r-s a { color: #fff; font-weight: 700; }
    .sa-fb { margin-top: .6rem; padding: .55rem .8rem; border-radius: 10px; background: rgba(255, 255, 255, .1); font-size: .88rem; white-space: pre-line; }
    .sa-fb b { display: block; font-size: .7rem; letter-spacing: .05em; text-transform: uppercase; opacity: .75; }
    .sa-btns { display: flex; flex-direction: column; gap: .45rem; }
    .sa-do { display: inline-flex; align-items: center; justify-content: center; gap: .4rem; min-height: 44px; padding: 0 1.25rem; border-radius: 11px; border: 0; cursor: pointer;
             font: inherit; font-size: .9rem; font-weight: 800; background: var(--sa-gold); color: #1c1600; text-decoration: none; white-space: nowrap; }
    .sa-do:hover { filter: brightness(.95); color: #1c1600; }
    .sa-do.alt { background: rgba(255, 255, 255, .12); color: #fff; }
    .sa-do svg { width: 16px; height: 16px; }
    .sa-do:focus-visible, .sa-pn a:focus-visible, .sa-more summary:focus-visible { outline: 2px solid var(--sa-gold); outline-offset: 2px; }

    /* My submission + the upload button, inside the instructions card */
    .sa-mine { margin-top: 1.5rem; padding: 1rem 1.1rem; border-radius: 10px; background: var(--sa-soft); border: 1px solid var(--sa-line); }
    .sa-mine-h { display: flex; align-items: center; justify-content: space-between; gap: .75rem; }
    .sa-mine-t { font-size: .92rem; font-weight: 700; color: var(--text-main); }
    .sa-tag { font-size: .72rem; font-weight: 700; padding: .15rem .6rem; border-radius: 100px; }
    .sa-tag.ok { background: var(--sa-ok-bg); color: var(--sa-ok); } .sa-tag.late { background: var(--sa-miss-bg); color: var(--sa-miss); } .sa-tag.none { background: var(--sa-card); color: var(--text-muted); border: 1px solid var(--sa-line); }
    .sa-doc-cols.two { display: grid; grid-template-columns: minmax(0, 1.6fr) minmax(300px, 1fr); gap: 2rem; align-items: start; }
    .sa-doc-main, .sa-doc-side { min-width: 0; }
    /* no instructions: no empty left column, the panels use the full card width */
    .sa-doc-cols.solo .sa-panels { display: grid; grid-template-columns: repeat(auto-fit, minmax(min(320px, 100%), 1fr)); gap: .9rem; }
    .sa-doc-cols.solo .sa-panels.one { grid-template-columns: minmax(0, 1fr); max-width: 560px; }
    .sa-doc-side { display: flex; flex-direction: column; gap: .9rem; }
    .sa-doc-side .sa-actions { margin-top: 0; }
    .sa-panels { display: flex; flex-direction: column; gap: .9rem; }
    .sa-panel { display: flex; flex-direction: column; gap: .75rem; min-width: 0; padding: 1rem 1.1rem; border: 1px solid var(--sa-line); border-radius: 10px; }
    .sa-panels .sa-mine { margin-top: 0; }
    .sa-panel .sa-mine-h { min-height: 24px; }
    .sa-panel .sa-file { display: flex; width: 100%; box-sizing: border-box; margin-top: 0; background: var(--sa-card); }
    .sa-panel .sa-file-n { flex: 1; }
    .sa-panel .sa-file-go { margin-left: auto; }
    .sa-panel .sa-mine-s, .sa-panel .sa-fb { margin-top: 0; }
    .sa-mine-s { display: flex; flex-wrap: wrap; gap: .25rem 1.25rem; font-size: .82rem; color: var(--text-muted); margin-top: .35rem; overflow-wrap: anywhere; }
    .sa-mine-s a { color: var(--sa-blue); font-weight: 700; }
    .sa-late { color: var(--sa-miss); }
    .sa-mine .sa-fb { background: var(--sa-card); border-left: 3px solid var(--sa-blue); color: var(--text-main); }
    .sa-mine .sa-fb b { color: var(--text-muted); opacity: 1; }
    .sa-actions { display: flex; flex-wrap: wrap; align-items: center; gap: .6rem 1rem; margin-top: 1.4rem; }
    .sa-actions .sa-do { background: var(--sa-blue); color: #fff; border-radius: 100px; min-height: 42px; padding: 0 1.3rem; }
    html[data-theme="dark"] .sa-actions .sa-do { background: #3b6fe0; }
    .sa-actions .sa-do:hover { filter: brightness(1.08); color: #fff; }
    .sa-actions .sa-do.alt { background: var(--sa-soft); color: var(--text-main); border: 1px solid var(--sa-line); }
    .sa-hint { font-size: .82rem; color: var(--text-muted); }

    /* Quiz attempts + answer review (collapsible, below the result) */
    .sa-more { margin-top: 1rem; background: var(--sa-card); border: 1px solid var(--sa-line); border-radius: 14px; }
    .sa-more summary { cursor: pointer; list-style: none; padding: 1rem 1.25rem; font-weight: 800; font-size: .92rem; color: var(--text-main); display: flex; justify-content: space-between; align-items: center; }
    .sa-more summary::-webkit-details-marker { display: none; }
    .sa-more summary::after { content: '▾'; color: var(--text-muted); transition: transform .2s; }
    .sa-more[open] summary::after { transform: rotate(180deg); }
    .sa-more-b { padding: 0 1.25rem 1.25rem; }
    .wk-sub { border: 1px solid var(--sa-line); border-radius: 12px; padding: .8rem 1rem; }
    .wk-sub + .wk-sub { margin-top: .6rem; }
    .wk-sub-h { display: flex; justify-content: space-between; align-items: center; gap: .75rem; flex-wrap: wrap; font-size: .9rem; color: var(--text-main); }
    .wk-sub-m { font-size: .8rem; color: var(--text-muted); margin-top: .25rem; }
    .wk-sub-m a { color: var(--sa-blue); font-weight: 700; }
    .wk-fb { margin-top: .6rem; padding: .55rem .8rem; border-radius: 10px; background: var(--sa-soft); font-size: .86rem; color: var(--text-main); white-space: pre-line; }
    .wk-fb b { display: block; font-size: .72rem; color: var(--text-muted); }
    .wk-pill { display: inline-block; font-size: .72rem; font-weight: 800; padding: .15rem .55rem; border-radius: 100px; }
    .wk-p-ok { background: var(--sa-ok-bg); color: var(--sa-ok); } .wk-p-warn { background: var(--sa-wait-bg); color: var(--sa-wait); } .wk-p-none { background: var(--sa-soft); color: var(--text-muted); }
    .wk-rev { margin-top: 1rem; border-top: 1px solid var(--sa-line); padding-top: 1rem; }
    .wk-rev-h { display: flex; justify-content: space-between; align-items: baseline; gap: .75rem; }
    .wk-rev-h b { font-size: .95rem; color: var(--text-main); } .wk-rev-h span { font-size: .8rem; color: var(--text-muted); font-weight: 700; }
    .wk-rev-note { font-size: .8rem; color: var(--text-muted); margin-top: .3rem; }
    .wk-rev-list { list-style: none; margin: .75rem 0 0; padding: 0; display: flex; flex-direction: column; gap: .55rem; }
    .wk-q { border: 1px solid var(--sa-line); border-left: 3px solid var(--sa-ok); border-radius: 10px; padding: .7rem .85rem; }
    .wk-q.no { border-left-color: var(--sa-miss); }
    .wk-q-t { display: flex; gap: .55rem; align-items: flex-start; font-size: .88rem; font-weight: 600; color: var(--text-main); overflow-wrap: anywhere; }
    .wk-q-t > span:nth-child(2) { flex: 1; min-width: 0; }
    .wk-q-mark { flex-shrink: 0; font-weight: 800; color: var(--sa-ok); } .wk-q.no .wk-q-mark { color: var(--sa-miss); }
    .wk-q-pts { flex-shrink: 0; font-size: .76rem; color: var(--text-muted); font-weight: 700; }
    .wk-q-a { font-size: .84rem; color: var(--text-main); margin: .35rem 0 0 1.35rem; overflow-wrap: anywhere; }
    .wk-q-a > span { color: var(--text-muted); font-weight: 600; } .wk-q-c { color: var(--sa-ok); }

    /* Previous / Next */
    .sa-pn { display: flex; justify-content: space-between; gap: 1rem; margin-bottom: .35rem; min-height: 40px; }
    .sa-pn a { display: inline-flex; align-items: center; gap: .35rem; min-height: 40px; max-width: 48%; font-size: .88rem; font-weight: 700; color: var(--sa-blue); text-decoration: none; }
    .sa-pn a span { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .sa-pn a:hover span { text-decoration: underline; }
    .sa-pn svg { width: 16px; height: 16px; flex-shrink: 0; }
    .sa-pn .nx { margin-left: auto; }

    @media (max-width: 900px) { .sa-doc-cols.two { grid-template-columns: minmax(0, 1fr); gap: 1.25rem; } }
    @media (max-width: 760px) {
        .sa-strip { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .sa-cell:nth-child(3) { border-left: 0; }
        .sa-cell:nth-child(n+3) { border-top: 1px solid var(--sa-line); }
        .sa-result { grid-template-columns: auto minmax(0, 1fr); }
        .sa-btns { grid-column: 1 / -1; }
    }
    @media (max-width: 480px) {
        .sa-title { font-size: 1.4rem; }
        .sa-doc { padding: 1.1rem 1.1rem; }
        .sa-result { padding: 1.1rem; gap: .9rem; }
        .sa-ring { width: 64px; height: 64px; } .sa-ring span { width: 50px; height: 50px; font-size: 1.1rem; }
        .sa-pn a { font-size: .82rem; }
    }
</style>

<?php
$I = [
    'cal'   => '<rect x="3.5" y="5" width="17" height="15" rx="2"/><path d="M3.5 10h17M8 3v4M16 3v4"/>',
    'star'  => '<path d="m12 3 2.7 5.6 6.1.9-4.4 4.3 1 6.1L12 17l-5.4 2.9 1-6.1L3.2 9.5l6.1-.9z"/>',
    'clock' => '<circle cx="12" cy="12" r="8.5"/><path d="M12 7.5V12l3 2"/>',
    'redo'  => '<path d="M20 11a8 8 0 1 0-2.3 5.7"/><path d="M20 4v7h-7"/>',
    'flag'  => '<path d="M5 21V4M5 4h11l-2 4 2 4H5"/>',
    'up'    => '<path d="M12 15V3"/><path d="m8 7 4-4 4 4"/><path d="M4 15v4a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-4"/>',
    'quiz'  => '<rect x="3.5" y="3.5" width="17" height="17" rx="3"/><path d="M9.6 9.3a2.5 2.5 0 0 1 4.8.9c0 1.7-2.4 2.2-2.4 3.6"/><path d="M12 17h.01"/>',
    'wait'  => '<circle cx="12" cy="12" r="8.5"/><path d="M12 7.5V12l3 2"/>',
    'x'     => '<path d="M6 6l12 12M18 6 6 18"/>',
    'ext'   => '<path d="M14 4h6v6M20 4l-9 9M18 14v5a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h5"/>',
    'left'  => '<path d="m15 6-6 6 6 6"/>', 'right' => '<path d="m9 6 6 6-6 6"/>', 'chev' => '<path d="m9 6 6 6-6 6"/>',
];
$i = fn(string $k) => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $I[$k] . '</svg>';

// Result bar text for this student
$subFile = $isQuiz ? ($lastAttempt['file_path'] ?? '') : ($item['submission_file'] ?? '');
$fbText = $isQuiz ? trim((string) ($reviewAttempt['remarks'] ?? ($attempts[0]['remarks'] ?? ''))) : trim((string) ($item['submission_remarks'] ?? ''));
if ($isGraded) {
    $rTitle = $num($myScore) . ' out of ' . $num($max) . ' points · ' . round($pct) . '%' . ($passMark !== null ? ($pct >= $passMark ? ' · Passed' : ' · Below passing') : '');
} elseif ($hasWork) {
    $rTitle = $isQuiz && $scoreHidden ? 'Quiz finished — your teacher has hidden the score' : 'Submitted — waiting for your teacher’s grade';
} elseif ($status[0] === 'miss') {
    $rTitle = $isQuiz ? 'You missed this quiz' : 'Not submitted — the deadline has passed';
} else {
    $rTitle = $step[1];
}
$rSub = [];
if ($hasWork && !$isQuiz) $rSub[] = 'Submitted ' . $item['submitted_at'];
if ($hasWork && $isQuiz && $lastAttempt) $rSub[] = 'Submitted ' . $when($lastAttempt['submitted_at']);
if ($status[0] === 'miss') $rSub[] = 'Talk to your teacher if you need help.';
if ($status[0] === 'soon' || $inProg) $rSub[] = $step[1];
?>
<div class="section-panel active wk sa">
  <div class="sa-wrap">
    <?php if ($prev || $next): ?>
        <nav class="sa-pn" aria-label="Previous and next in this module">
            <?php if ($prev): ?><a href="<?= htmlspecialchars($prev) ?>"><?= $i('left') ?><span><?= htmlspecialchars($prevInfo[0] . ': ' . $prevInfo[1]) ?></span></a><?php endif; ?>
            <?php if ($next): ?><a class="nx" href="<?= htmlspecialchars($next) ?>"><span>Next: <?= htmlspecialchars($nextInfo[1]) ?></span><?= $i('right') ?></a><?php endif; ?>
        </nav>
    <?php endif; ?>
    <div class="sa-titlerow">
        <h1 class="sa-title"><?= htmlspecialchars($item['title']) ?></h1>
    </div>

    <div class="sa-strip">
        <?php foreach ($strip as [$ic, $l, $v]): ?>
            <div class="sa-cell"><span class="sa-cell-ico"><?= $i($ic) ?></span><div style="min-width:0"><small><?= $l ?></small><b><?= htmlspecialchars((string) $v) ?></b></div></div>
        <?php endforeach; ?>
        <?php if ($isGraded): ?>
            <div class="sa-cell"><span class="sa-cell-ico"><?= $i('flag') ?></span><div style="min-width:0"><small>Score</small><b class="ok"><?= $num($myScore) ?> / <?= $num($max) ?></b></div></div>
        <?php else: ?>
            <div class="sa-cell"><span class="sa-cell-ico"><?= $i('flag') ?></span><div style="min-width:0"><small>Status</small><b class="<?= $status[0] ?>"><?= htmlspecialchars($status[1]) ?></b></div></div>
        <?php endif; ?>
    </div>

    <?php
    $extBg = fn(string $e): string => in_array($e, ['DOC', 'DOCX'], true) ? '#1d4ed8' : (in_array($e, ['PPT', 'PPTX'], true) ? '#c2410c' : (in_array($e, ['XLS', 'XLSX', 'CSV'], true) ? '#15803d' : (in_array($e, ['PNG', 'JPG', 'JPEG', 'GIF', 'WEBP'], true) ? '#6d28d9' : '#b42318')));
    $showMine = !$isQuiz && $hasWork;
    if ($showMine) {
        $subExt = $subFile ? strtoupper(pathinfo($subFile, PATHINFO_EXTENSION) ?: 'FILE') : '';
        $subOnDisk = $subFile && is_file(__DIR__ . '/../' . $subFile);
        $gradedOnly = !$subFile && (int) $item['attempt_count'] === 0;   // teacher saved a grade, nothing was uploaded
        $subDate = $when((string) $item['submitted_at']) ?? (string) $item['submitted_at'];
    }
    $panelCount = ($file ? 1 : 0) + ($showMine ? 1 : 0);
    $hasSide = $panelCount > 0 || $action || $notOpen || $inProg;   // right column: files, submission, button
    ?>
    <section class="sa-doc" id="wk-instructions" aria-labelledby="sa-todo">
        <h2 class="sa-h" id="sa-todo">What to do</h2>
        <?php $hasInstr = trim($instr) !== ''; ?>
        <div class="sa-doc-cols<?= $hasSide ? ($hasInstr ? ' two' : ' solo') : '' ?>">
        <?php if ($hasInstr): ?>
        <div class="sa-doc-main">
            <div class="wk-body"><?php foreach (preg_split('/\R{2,}/', trim($instr)) as $para): ?><p><?= nl2br(htmlspecialchars($para)) ?></p><?php endforeach; ?></div>
        </div>
        <?php elseif (!$hasSide): ?>
        <div class="sa-doc-main">
            <div class="wk-empty">Your teacher didn’t add instructions for this <?= $isQuiz ? 'quiz' : 'assignment' ?>.</div>
        </div>
        <?php endif; ?>
        <?php if ($hasSide): ?>
        <div class="sa-doc-side">
        <?php if ($panelCount): ?>
            <div class="sa-panels<?= $panelCount === 1 ? ' one' : '' ?>">
                <?php if ($file): ?>
                    <div class="sa-panel">
                        <div class="sa-mine-h"><div class="sa-mine-t">From your teacher</div></div>
                        <a class="sa-file" href="../<?= htmlspecialchars($file) ?>" target="_blank" rel="noopener">
                            <span class="sa-ext" style="background:<?= $extBg($fileExt) ?>"><?= htmlspecialchars(substr($fileExt, 0, 4)) ?></span>
                            <span class="sa-file-n"><?= htmlspecialchars(elmsFileLabel($file)) ?><small><?= htmlspecialchars($fileExt) ?> file</small></span>
                            <span class="sa-file-go"><?= $i('ext') ?></span>
                        </a>
                    </div>
                <?php endif; ?>

                <?php if ($showMine): ?>
                    <div class="sa-panel sa-mine" id="wk-submissions">
                        <div class="sa-mine-h">
                            <div class="sa-mine-t">Your submission</div>
                            <?php if ($gradedOnly): ?><span class="sa-tag none">No file submitted</span>
                            <?php else: ?><span class="sa-tag <?= $late ? 'late' : 'ok' ?>"><?= $late ? 'Late' : 'On time' ?></span><?php endif; ?>
                        </div>
                        <?php if ($subFile && $subOnDisk): ?>
                            <a class="sa-file" href="../<?= htmlspecialchars($subFile) ?>" target="_blank" rel="noopener">
                                <span class="sa-ext" style="background:<?= $extBg($subExt) ?>"><?= htmlspecialchars(substr($subExt, 0, 4)) ?></span>
                                <span class="sa-file-n"><?= htmlspecialchars(elmsFileLabel($subFile)) ?><small>Submitted <?= htmlspecialchars((string) $subDate) ?></small></span>
                                <span class="sa-file-go"><?= $i('ext') ?></span>
                            </a>
                        <?php elseif ($subFile): ?>
                            <div class="sa-mine-s"><span><?= htmlspecialchars(elmsFileLabel($subFile)) ?> was recorded for this submission, but the file is no longer on the server. Submit it again or tell your teacher.</span></div>
                        <?php else: ?>
                            <div class="sa-mine-s"><span>No file is attached to this submission.<?= $gradedOnly ? ' Your teacher saved a grade on ' . htmlspecialchars((string) $subDate) . '.' : '' ?></span></div>
                        <?php endif; ?>
                        <?php if ($fbText !== ''): ?><div class="sa-fb"><b>Teacher’s feedback</b><?= htmlspecialchars($fbText) ?></div><?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if ($action): ?>
            <div class="sa-actions">
                <button type="button" class="sa-do" onclick="<?= $action[1] ?>"><?= $i($isQuiz && ($item['quiz_type'] ?? '') !== 'file' ? 'quiz' : 'up') ?><?= htmlspecialchars($action[0]) ?></button>
                <?php if ($isQuiz && $file): ?><a class="sa-do alt" href="../<?= htmlspecialchars($file) ?>" target="_blank" rel="noopener">Download quiz file</a><?php endif; ?>
                <?php if ($isQuiz && ($item['quiz_type'] ?? '') !== 'file'): ?><span class="sa-hint">Once you start, you can’t leave the quiz — leaving submits your answers.</span>
                <?php elseif (!$hasWork && $deadline): ?><span class="sa-hint">Due <?= htmlspecialchars($when($deadlineRaw)) ?> (<?= $rel($deadline) ?>)</span><?php endif; ?>
            </div>
        <?php elseif ($notOpen || $inProg): ?>
            <div class="sa-actions"><span class="sa-hint"><?= htmlspecialchars($step[1]) ?></span></div>
        <?php endif; ?>
        </div>
        <?php endif; ?>
        </div>

        <?php if ($extraInfo): ?>
            <dl class="sa-fine">
                <?php foreach ($extraInfo as [$l, $v]): ?>
                    <div><dt><?= htmlspecialchars($l) ?></dt><dd><?= htmlspecialchars((string) $v) ?></dd></div>
                <?php endforeach; ?>
            </dl>
        <?php endif; ?>
    </section>

    <?php if ($isQuiz && $attempts): ?>
        <details class="sa-more"<?= $tab === 'submissions' ? ' open' : '' ?>>
            <summary>Your attempts<?= !empty($review) ? ' and answer review' : '' ?> (<?= count($attempts) ?>)</summary>
            <div class="sa-more-b">
                <?php foreach ($attempts as $k => $at):
                    $showScore = $at['score'] !== null && !$scoreHidden;
                    $pill = ($at['status'] === 'Graded' || ($at['status'] === 'Submitted' && $showScore)) ? ['Graded', 'wk-p-ok'] : ($at['status'] === 'Submitted' ? ['Submitted', 'wk-p-warn'] : ['In progress', 'wk-p-none']);
                ?>
                    <div class="wk-sub">
                        <div class="wk-sub-h"><b>Attempt <?= count($attempts) - $k ?><?= $showScore ? ' · ' . $num($at['score']) . '/' . $num($max) : '' ?></b><span class="wk-pill <?= $pill[1] ?>"><?= $pill[0] ?></span></div>
                        <div class="wk-sub-m">Started <?= htmlspecialchars((string) $when($at['started_at'])) ?><?= $at['submitted_at'] ? ' · submitted ' . htmlspecialchars((string) $when($at['submitted_at'])) : '' ?>
                            <?php if (!empty($at['file_path'])): ?> · <a href="../<?= htmlspecialchars($at['file_path']) ?>" target="_blank" rel="noopener"><?= htmlspecialchars(elmsFileLabel($at['file_path'])) ?></a><?php endif; ?></div>
                        <?php if (trim((string) ($at['remarks'] ?? '')) !== ''): ?><div class="wk-fb"><b>Teacher’s feedback</b><?= htmlspecialchars($at['remarks']) ?></div><?php endif; ?>
                    </div>
                <?php endforeach; ?>
                <?php if (!empty($review)):
                    $nRight = count(array_filter($review, fn($r) => (int) $r['is_correct'] === 1));
                    $nWait  = count(array_filter($review, fn($r) => $r['question_type'] === 'long_essay' && (int) ($r['needs_grading'] ?? 0) === 1)); ?>
                    <div class="wk-rev" id="wk-review">
                        <div class="wk-rev-h"><b>Review answers</b><span><?= $nRight ?> of <?= count($review) ?> correct<?= $nWait ? ' · ' . $nWait . ' essay' . ($nWait === 1 ? '' : 's') . ' waiting for your teacher' : '' ?></span></div>
                        <?php if (!$revealCorrect): ?><div class="wk-rev-note">Your teacher shows which answers are right or wrong, but not the correct answers.</div><?php endif; ?>
                        <ol class="wk-rev-list">
                            <?php foreach ($review as $r):
                                $ok = (int) $r['is_correct'] === 1;
                                $isLong = $r['question_type'] === 'long_essay';
                                $wait = $isLong && (int) ($r['needs_grading'] ?? 0) === 1;
                                $essayDone = $isLong && !$wait && trim((string) $r['answer_text']) !== '';
                                $mine = ($r['question_type'] === 'essay' || $isLong) ? trim((string) $r['answer_text']) : (string) $r['my_choice'];
                                $pmax = (float) $r['points'] > 0 ? $r['points'] : 1;
                            ?>
                                <li class="wk-q <?= $wait || $essayDone ? '' : ($ok ? 'ok' : 'no') ?>">
                                    <div class="wk-q-t"><span class="wk-q-mark" aria-label="<?= $wait ? 'Waiting for teacher' : ($essayDone ? 'Graded by teacher' : ($ok ? 'Correct' : 'Wrong')) ?>"><?= $wait ? '…' : ($essayDone ? '✎' : ($ok ? '✓' : '✗')) ?></span><span><?= nl2br(htmlspecialchars($r['question_text'])) ?></span><span class="wk-q-pts"><?= $wait ? '?' : $num($r['points_earned'] ?? 0) ?>/<?= $num($pmax) ?></span></div>
                                    <div class="wk-q-a"><span>Your answer:</span> <?= $mine !== '' ? ($isLong ? nl2br(htmlspecialchars($mine)) : htmlspecialchars($mine)) : '<i>No answer</i>' ?></div>
                                    <?php if ($wait): ?><div class="wk-q-a"><i>Essay — waiting for your teacher to grade it.</i></div><?php endif; ?>
                                    <?php if (!$ok && $revealCorrect && !$isLong): ?>
                                        <?php if ($r['correct'] !== null && $r['correct'] !== ''): ?>
                                            <div class="wk-q-a wk-q-c"><span>Correct answer:</span> <?= htmlspecialchars($r['correct']) ?></div>
                                        <?php else: ?>
                                            <div class="wk-q-a"><span>Correct answer:</span> <i>not set by your teacher</i></div>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </li>
                            <?php endforeach; ?>
                        </ol>
                    </div>
                <?php endif; ?>
            </div>
        </details>
    <?php endif; ?>

  </div>
</div>

<?php if (!$isQuiz): ?>
<!-- Submit assignment (same as the Assignments page) -->
<script src="../assets/js/elms-upload-rules.js"></script>
<div class="modal-overlay hidden" id="assignSubmitModal">
    <div class="modal">
        <div class="modal-header">
            <span>Submit Assignment</span>
            <button onclick="document.getElementById('assignSubmitModal').classList.add('hidden')" style="background:none;border:none;font-size:1.5rem;cursor:pointer;" aria-label="Close">&times;</button>
        </div>
        <div class="modal-body">
            <p id="assign-submit-name" style="margin-bottom:1rem; font-weight:600;"></p>
            <label style="display:block; margin-bottom:0.5rem; font-weight:500;" for="assign-submit-file">Choose File</label>
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
<?php endif; ?>

<script>
    (function () {
        const tabs = document.querySelectorAll('.wk-tab');
        // Keyboard: ← / → move between the tabs (Tab + Enter also works)
        tabs.forEach((t, k) => t.addEventListener('keydown', e => {
            if (e.key !== 'ArrowRight' && e.key !== 'ArrowLeft') return;
            e.preventDefault();
            const n = tabs[(k + (e.key === 'ArrowRight' ? 1 : tabs.length - 1)) % tabs.length];
            n.focus(); n.click();
        }));
        tabs.forEach(t => t.addEventListener('click', () => {
            tabs.forEach(x => { const on = x === t; x.classList.toggle('on', on); x.setAttribute('aria-selected', on ? 'true' : 'false'); document.getElementById('wk-' + x.dataset.tab).hidden = !on; });
            // Keep the tab in the link, so a live refresh of the page shows the same tab
            try { const u = new URL(location.href); if (t.dataset.tab === 'submissions') u.searchParams.set('tab', 'submissions'); else u.searchParams.delete('tab'); history.replaceState(null, '', u); } catch (e) { }
        }));
    })();
    // Quiz submitted here (footer.php → submitQuiz / submitQuizFile): show my attempt + answer review
    window.quizSubmittedHook = () => { const u = new URL(location.href); u.searchParams.set('tab', 'submissions'); u.hash = 'wk-review'; location.href = u; };
    let currentAssignmentId = null;
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
                // Show the submission right away
                const u = new URL(location.href); u.searchParams.set('tab', 'submissions'); location.href = u;
            } else {
                errEl.textContent = data.message || 'Upload failed.'; errEl.style.display = 'block';
            }
        } catch (e) {
            errEl.textContent = 'Network error. Please try again.'; errEl.style.display = 'block';
        } finally {
            btn.disabled = false; btn.innerText = originalText;
        }
    }
</script>
<?php require __DIR__ . '/../includes/footer.php'; ?>