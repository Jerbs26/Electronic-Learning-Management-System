<?php
// ============================================================
//  Arandia College eLMS — Teacher Dashboard
//  File: teacher/dashboard.php
// ============================================================
require __DIR__ . '/_init.php';

//  ── New dashboard data ─────────────────────────────────────
// "My students" = students enrolled in one of my subjects AND in the section I teach it to.
$tid = (int) $user_id;
$classCount = count($mySubjects);
$mySectionsList = array_values(array_unique(array_column($mySubjects, 'section')));
$courseStudents = [];   // course_id => [student_id => true]
$res = $conn->query(
    "SELECT DISTINCT e.course_id, e.student_id
     FROM enrollments e
     JOIN users u ON u.id = e.student_id AND u.role = 'Student' AND u.status = 'Active'
     JOIN teacher_assignments ta ON ta.course_id = e.course_id AND ta.section = u.section_dept AND ta.teacher_id = $tid
     WHERE e.status = 'Enrolled'"
);
while ($res && $r = $res->fetch_assoc())
    $courseStudents[(int) $r['course_id']][(int) $r['student_id']] = true;
$myStudentIds = [];
foreach ($courseStudents as $ids)
    $myStudentIds += $ids;
$myStudentCount = count($myStudentIds);
$myCourseIds = array_map('intval', array_unique(array_column($mySubjects, 'course_id')));
$cIn = $myCourseIds ? implode(',', $myCourseIds) : '0';
$sIn = $myStudentIds ? implode(',', array_keys($myStudentIds)) : '0';

// To grade: submitted, not yet scored
// (same number as the bell and the Assignments list — computed once in _init.php)
$toGrade = $statPending;

// Attendance today: how many of my students already have a record today in my subjects
$today = date('Y-m-d');
$attToday = (int) $conn->query(
    "SELECT COUNT(DISTINCT student_id) FROM attendance
     WHERE date = '$today' AND course_id IN ($cIn) AND student_id IN ($sIn)"
)->fetch_row()[0];

// Recent activity: submissions, quiz attempts, finished modules (my students, my subjects)
$activity = [];
$res = $conn->query(
    "SELECT u.id AS sid, a.id AS aid, u.first_name, u.last_name, a.title, c.course_name, s.submitted_at AS at, s.score, a.max_score
     FROM submissions s JOIN assignments a ON a.id = s.assignment_id
     JOIN users u ON u.id = s.student_id JOIN courses c ON c.id = a.course_id
     WHERE a.course_id IN ($cIn) AND s.student_id IN ($sIn)
     ORDER BY s.submitted_at DESC LIMIT 8"
);
while ($res && $r = $res->fetch_assoc()) {
    $graded = $r['score'] !== null;
    $activity[] = [
        'who' => $r['first_name'] . ' ' . $r['last_name'],
        'what' => 'submitted ' . $r['title'],
        'course' => $r['course_name'],
        'at' => $r['at'],
        'tag' => $graded ? 'Graded' : 'To grade',
        'tone' => $graded ? 'ok' : 'warn',
        'href' => 'grade.php?id=' . (int) $r['aid'] . '&student=' . (int) $r['sid']
    ];
}
$res = $conn->query(
    "SELECT u.first_name, u.last_name, u.section_dept, q.course_id, q.title, c.course_name, COALESCE(qa.submitted_at, qa.finished_at) AS at, qa.score, q.max_score
     FROM quiz_attempts qa JOIN quizzes q ON q.id = qa.quiz_id
     JOIN users u ON u.id = qa.student_id JOIN courses c ON c.id = q.course_id
     WHERE q.course_id IN ($cIn) AND qa.student_id IN ($sIn) AND qa.status <> 'In Progress'
     ORDER BY at DESC LIMIT 8"
);
while ($res && $r = $res->fetch_assoc()) {
    $activity[] = [
        'who' => $r['first_name'] . ' ' . $r['last_name'],
        'what' => 'took ' . $r['title'],
        'course' => $r['course_name'],
        'at' => $r['at'],
        'tag' => $r['score'] !== null ? 'Score ' . rtrim(rtrim($r['score'], '0'), '.') . '/' . rtrim(rtrim($r['max_score'], '0'), '.') : 'Submitted',
        'tone' => 'info',
        'href' => 'assignments.php?course=' . (int) $r['course_id'] . '&section=' . rawurlencode((string) $r['section_dept']) . '#quizzes'
    ];
}
$hasModNum = (bool) $conn->query("SHOW COLUMNS FROM modules LIKE 'module_number'")->num_rows;
$res = $conn->query(
    "SELECT u.first_name, u.last_name, m.id AS mid, m.course_id, m.title, " . ($hasModNum ? 'm.module_number' : 'NULL') . " AS num, c.course_name, mp.completed_at AS at
     FROM module_progress mp JOIN modules m ON m.id = mp.module_id
     JOIN users u ON u.id = mp.student_id JOIN courses c ON c.id = m.course_id
     WHERE m.course_id IN ($cIn) AND mp.student_id IN ($sIn)
     ORDER BY mp.completed_at DESC LIMIT 8"
);
while ($res && $r = $res->fetch_assoc()) {
    $activity[] = [
        'who' => $r['first_name'] . ' ' . $r['last_name'],
        'what' => 'finished ' . ($r['num'] ? 'Module ' . $r['num'] : $r['title']),
        'course' => $r['course_name'],
        'at' => $r['at'],
        'tag' => 'Done',
        'tone' => 'ok',
        'href' => 'modules.php?course=' . (int) $r['course_id'] . '&module=' . (int) $r['mid']
    ];
}
usort($activity, fn($a, $b) => strtotime($b['at'] ?? '') <=> strtotime($a['at'] ?? ''));
$activity = array_slice($activity, 0, 6);

// Due this week: assignments (due_date) and quizzes (close_at) in the next 7 days
$dueSoon = [];
$from = date('Y-m-d H:i:s');
$to = date('Y-m-d 23:59:59', strtotime('+7 days'));
$res = $conn->query(
    "SELECT a.id, a.title, a.course_id, a.due_date AS due, c.course_name,
            (SELECT COUNT(DISTINCT s.student_id) FROM submissions s WHERE s.assignment_id = a.id AND s.student_id IN ($sIn)) AS done
     FROM assignments a JOIN courses c ON c.id = a.course_id
     WHERE a.course_id IN ($cIn) AND a.due_date BETWEEN '$from' AND '$to' AND " . scopeTeacherOwned($conn, 'a', 'assignments', $tid) . ""
);
while ($res && $r = $res->fetch_assoc())
    $dueSoon[] = $r + ['kind' => 'Assignment', 'verb' => 'submitted', 'href' => 'assignments.php'];
$res = $conn->query(
    "SELECT q.id, q.title, q.course_id, q.close_at AS due, c.course_name,
            (SELECT COUNT(DISTINCT qa.student_id) FROM quiz_attempts qa WHERE qa.quiz_id = q.id AND qa.status <> 'In Progress' AND qa.student_id IN ($sIn)) AS done
     FROM quizzes q JOIN courses c ON c.id = q.course_id
     WHERE q.course_id IN ($cIn) AND q.status = 'Published' AND q.close_at BETWEEN '$from' AND '$to' AND " . scopeTeacherOwned($conn, 'q', 'quizzes', $tid) . ""
);
while ($res && $r = $res->fetch_assoc())
    $dueSoon[] = $r + ['kind' => 'Quiz', 'verb' => 'taken', 'href' => 'assignments.php#quizzes'];
usort($dueSoon, fn($a, $b) => strtotime($a['due']) <=> strtotime($b['due']));
$dueSoon = array_slice($dueSoon, 0, 6);

function tdAgo(?string $dt): string
{
    if (!$dt)
        return '';
    $s = time() - strtotime($dt);
    if ($s < 60)
        return 'just now';
    if ($s < 3600)
        return floor($s / 60) . ' min ago';
    if ($s < 86400)
        return floor($s / 3600) . ' hr' . (floor($s / 3600) == 1 ? '' : 's') . ' ago';
    if ($s < 172800)
        return 'yesterday';
    if ($s < 604800)
        return floor($s / 86400) . ' days ago';
    return date('M j', strtotime($dt));
}
function tdInitials(string $name): string
{
    $parts = preg_split('/\s+/', trim($name));
    return strtoupper(substr($parts[0] ?? '', 0, 1) . substr(end($parts) ?: '', 0, 1));
}
$hour = (int) date('G');
$greeting = $hour < 12 ? 'Good morning' : ($hour < 18 ? 'Good afternoon' : 'Good evening');

$currentPage = 'dashboard.php';
$pageTitle = 'Dashboard';
require __DIR__ . '/../includes/header.php';
?>
<style>
    .section-panel {
        --td-soft: #eef3ff;
        --td-strong: #003087;
    }

    html[data-theme="dark"] .section-panel {
        --td-soft: rgba(143, 176, 255, .12);
        --td-strong: #c7d7ff;
    }

    .td-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1.25rem;
        flex-wrap: wrap;
        margin-bottom: 1.4rem;
    }

    .td-title {
        font-family: 'Nunito', sans-serif;
        font-size: 1.6rem;
        font-weight: 900;
        color: var(--text-dark);
        line-height: 1.2;
    }

    .td-sub {
        font-size: .9rem;
        color: var(--text-light);
        margin-top: .3rem;
    }

    .td-stats {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 1rem;
        margin-bottom: 1.25rem;
    }

    .td-card {
        background: var(--white);
        border-radius: 16px;
        box-shadow: 0 2px 14px rgba(0, 0, 0, .06);
        padding: 1.1rem 1.25rem;
    }

    .td-stat {
        display: flex;
        align-items: center;
        gap: .9rem;
        text-decoration: none;
        color: inherit;
    }

    .td-ico {
        width: 52px;
        height: 52px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .td-ico .ico,
    .td-ico svg {
        width: 24px;
        height: 24px;
    }

    .td-ico.blue {
        background: #e8f0ff;
        color: #003087;
    }

    .td-ico.teal {
        background: #e0f7fa;
        color: #0097a7;
    }

    .td-ico.amber {
        background: #fff7e0;
        color: #b38600;
    }

    .td-ico.red {
        background: #fdecec;
        color: #c0392b;
    }

    html[data-theme="dark"] .td-ico {
        background: rgba(255, 255, 255, .06);
    }

    html[data-theme="dark"] .td-ico.blue {
        color: #8fb0ff;
    }

    .td-stat-l {
        font-size: .82rem;
        color: var(--text-light);
        font-weight: 600;
    }

    .td-stat-v {
        font-family: 'Nunito', sans-serif;
        font-size: 1.75rem;
        font-weight: 900;
        color: var(--text-dark);
        line-height: 1.15;
    }

    .td-stat-s {
        font-size: .74rem;
        font-weight: 600;
        margin-top: .55rem;
        color: var(--text-light);
    }

    .td-stat-s.warn {
        color: #b38600;
    }

    .td-stat-s.ok {
        color: #00875a;
    }

    .td-two {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 1rem;
    }

    .td-card-h {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: .6rem;
    }

    .td-card-t {
        font-family: 'Nunito', sans-serif;
        font-size: 1.05rem;
        font-weight: 800;
        color: var(--text-dark);
    }

    .td-link {
        font-size: .8rem;
        font-weight: 700;
        color: var(--primary);
        text-decoration: none;
    }

    html[data-theme="dark"] .td-link {
        color: #8fb0ff;
    }

    .td-row {
        display: flex;
        align-items: center;
        gap: .8rem;
        padding: .75rem 0;
        border-top: 1px solid rgba(15, 23, 42, .06);
        text-decoration: none;
        color: inherit;
    }

    html[data-theme="dark"] .td-row {
        border-top-color: rgba(255, 255, 255, .06);
    }

    .td-row:first-of-type {
        border-top: 0;
    }

    .td-av {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        background: var(--td-soft);
        color: var(--td-strong);
        font-weight: 800;
        font-size: .76rem;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .td-row-t {
        font-size: .88rem;
        font-weight: 600;
        color: var(--text-dark);
    }

    .td-row-s {
        font-size: .76rem;
        color: var(--text-light);
        margin-top: .1rem;
    }

    .td-tag {
        margin-left: auto;
        font-size: .72rem;
        font-weight: 700;
        padding: .2rem .65rem;
        border-radius: 100px;
        white-space: nowrap;
    }

    .td-tag.ok {
        background: rgba(0, 135, 90, .12);
        color: #00875a;
    }

    .td-tag.warn {
        background: rgba(179, 134, 0, .14);
        color: #b38600;
    }

    .td-tag.info {
        background: var(--td-soft);
        color: var(--td-strong);
    }

    .td-tag.muted {
        background: rgba(100, 116, 139, .12);
        color: var(--text-light);
    }

    .td-date {
        width: 52px;
        text-align: center;
        border-radius: 10px;
        background: var(--td-soft);
        color: var(--td-strong);
        padding: .35rem 0;
        flex-shrink: 0;
        line-height: 1.15;
    }

    .td-date b {
        display: block;
        font-family: 'Nunito', sans-serif;
        font-size: 1.1rem;
        font-weight: 900;
    }

    .td-date span {
        font-size: .66rem;
        font-weight: 700;
        text-transform: uppercase;
    }

    .td-empty {
        text-align: center;
        padding: 2rem 1rem;
        color: var(--text-light);
        font-size: .86rem;
    }

    .td-empty strong {
        display: block;
        color: var(--text-dark);
        margin-bottom: .2rem;
    }

    @media (max-width: 1100px) {
        .td-stats {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 860px) {
        .td-two {
            grid-template-columns: minmax(0, 1fr);
        }
    }

    .td-stat>div:last-child {
        min-width: 0;
    }

    @media (max-width: 480px) {
        .td-stats {
            gap: .75rem;
        }

        .td-card.td-stat {
            padding: .9rem;
        }

        .td-stat {
            flex-direction: column;
            align-items: flex-start;
            gap: .55rem;
        }

        .td-ico {
            width: 40px;
            height: 40px;
            border-radius: 11px;
        }

        .td-ico .ico,
        .td-ico svg {
            width: 20px;
            height: 20px;
        }

        .td-stat-v {
            font-size: 1.45rem;
        }

        .td-stat-s {
            margin-top: .3rem;
        }
    }
</style>

<div class="section-panel active">
    <div class="td-head">
        <div>
            <div class="td-title"><?= $greeting ?>, <?= htmlspecialchars($first_name) ?></div>
        </div>
    </div>

    <div class="td-stats">
        <a class="td-card td-stat" href="classes.php">
            <div class="td-ico blue"><?= t_icon('book') ?></div>
            <div>
                <div class="td-stat-l">My classes</div>
                <div class="td-stat-v"><?= $classCount ?></div>
                <div class="td-stat-s"><?= $statSubjects ?>
                    subject<?= $statSubjects == 1 ? '' : 's' ?><?= $mySectionsList ? ' · ' . htmlspecialchars(implode(', ', array_slice($mySectionsList, 0, 2))) . (count($mySectionsList) > 2 ? '…' : '') : '' ?>
                </div>
            </div>
        </a>
        <a class="td-card td-stat" href="my-students.php">
            <div class="td-ico teal"><?= t_icon('users') ?></div>
            <div>
                <div class="td-stat-l">Students</div>
                <div class="td-stat-v"><?= $myStudentCount ?></div>
                <div class="td-stat-s">In <?= count($mySectionsList) ?>
                    section<?= count($mySectionsList) == 1 ? '' : 's' ?></div>
            </div>
        </a>
        <a class="td-card td-stat" href="assignments.php">
            <div class="td-ico amber"><?= t_icon('clipboard') ?></div>
            <div>
                <div class="td-stat-l">To grade</div>
                <div class="td-stat-v"><?= $toGrade ?></div>
                <div class="td-stat-s <?= $toGrade ? 'warn' : 'ok' ?>">
                    <?= $toGrade ? 'Submission' . ($toGrade == 1 ? '' : 's') . ' waiting' : 'All caught up' ?></div>
            </div>
        </a>
        <a class="td-card td-stat" href="attendance.php">
            <div class="td-ico red"><?= t_icon('check-square') ?></div>
            <div>
                <div class="td-stat-l">Attendance today</div>
                <div class="td-stat-v">
                    <?= $myStudentCount ? $attToday . '<span style="font-size:1rem;color:var(--text-light);">/' . $myStudentCount . '</span>' : '—' ?>
                </div>
                <div class="td-stat-s <?= $attToday && $attToday >= $myStudentCount ? 'ok' : '' ?>">
                    <?= !$attToday ? 'Not taken yet' : ($attToday >= $myStudentCount ? 'Complete' : 'In progress') ?>
                </div>
            </div>
        </a>
    </div>

    <div class="td-two">
        <div class="td-card">
            <div class="td-card-h">
                <div class="td-card-t">Recent activity</div><a class="td-link" href="classes.php">View classes</a>
            </div>
            <?php if (!$activity): ?>
                <div class="td-empty"><strong>No activity yet</strong>Submissions, quiz results, and finished modules from
                    your students will show up here.</div>
            <?php else:
                foreach ($activity as $a): ?>
                    <a class="td-row" href="<?= $a['href'] ?>">
                        <div class="td-av"><?= htmlspecialchars(tdInitials($a['who'])) ?></div>
                        <div style="min-width:0;">
                            <div class="td-row-t"><?= htmlspecialchars($a['who'] . ' ' . $a['what']) ?></div>
                            <div class="td-row-s"><?= htmlspecialchars($a['course'] . ' · ' . tdAgo($a['at'])) ?></div>
                        </div>
                        <span class="td-tag <?= $a['tone'] ?>"><?= htmlspecialchars($a['tag']) ?></span>
                    </a>
                <?php endforeach; endif; ?>
        </div>

        <div class="td-card">
            <div class="td-card-h">
                <div class="td-card-t">Due this week</div><a class="td-link" href="assignments.php">View assignments</a>
            </div>
            <?php if (!$dueSoon): ?>
                <div class="td-empty"><strong>Nothing due this week</strong>Assignments and quizzes with a deadline in the
                    next 7 days will show up here.</div>
            <?php else:
                foreach ($dueSoon as $d):
                    $total = count($courseStudents[(int) $d['course_id']] ?? []); ?>
                    <a class="td-row" href="<?= $d['href'] ?>">
                        <div class="td-date">
                            <span><?= date('M', strtotime($d['due'])) ?></span><b><?= date('j', strtotime($d['due'])) ?></b><span><?= date('D', strtotime($d['due'])) ?></span>
                        </div>
                        <div style="min-width:0;">
                            <div class="td-row-t"><?= htmlspecialchars($d['title']) ?></div>
                            <div class="td-row-s">
                                <?= htmlspecialchars($d['course_name'] . ' · ' . $d['kind'] . ' · ' . date('g:i A', strtotime($d['due']))) ?>
                            </div>
                        </div>
                        <span class="td-tag muted"><?= (int) $d['done'] ?>/<?= $total ?>         <?= $d['verb'] ?></span>
                    </a>
                <?php endforeach; endif; ?>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>