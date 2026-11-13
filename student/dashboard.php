<?php
// ============================================================
//  Arandia College eLMS — Student: Dashboard
//  File: student/dashboard.php
//  Data comes from student/_init.php (same rules as the teacher side).
// ============================================================
require __DIR__ . '/_init.php';
require_once __DIR__ . '/../includes/progress.php';
require_once __DIR__ . '/../includes/awards.php';
require_once __DIR__ . '/awards_components.php';

// Calendar events — assignment due dates + quiz open/close windows, keyed by date
$calendarEvents = [];
foreach ($myAssignments as $a) {
    if (empty($a['due_date']))
        continue;
    $d = date('Y-m-d', strtotime($a['due_date']));
    $calendarEvents[$d][] = [
        'type' => 'assignment',
        'label' => $a['title'],
        'course' => $a['course_code'],
        'time' => date('g:i A', strtotime($a['due_date'])),
    ];
}
foreach ($myQuizzes as $q) {
    if (!empty($q['close_at'])) {
        $d = date('Y-m-d', strtotime($q['close_at']));
        $calendarEvents[$d][] = [
            'type' => 'quiz',
            'label' => $q['title'] . ' (closes)',
            'course' => $q['course_code'],
            'time' => date('g:i A', strtotime($q['close_at'])),
        ];
    }
    if (!empty($q['open_at'])) {
        $d = date('Y-m-d', strtotime($q['open_at']));
        $calendarEvents[$d][] = [
            'type' => 'quiz',
            'label' => $q['title'] . ' (opens)',
            'course' => $q['course_code'],
            'time' => date('g:i A', strtotime($q['open_at'])),
        ];
    }
}

// Upcoming list — next 5 events from today onward, flattened and sorted
$upcomingEvents = [];
$todayStr = date('Y-m-d');
foreach ($calendarEvents as $d => $items) {
    if ($d >= $todayStr) {
        foreach ($items as $it) {
            $it['date'] = $d;
            $upcomingEvents[] = $it;
        }
    }
}
usort($upcomingEvents, fn($a, $b) => strcmp($a['date'], $b['date']));
$upcomingEvents = array_slice($upcomingEvents, 0, 5);

// Split out assignments (shown in their own tracker card) from everything else
// (quiz windows), so the same due date isn't listed twice on the page.
$upcomingAssignments = array_slice(
    array_values(array_filter($upcomingEvents, fn($e) => $e['type'] === 'assignment')),
    0,
    4
);
$upcomingNonAssignments = array_values(array_filter($upcomingEvents, fn($e) => $e['type'] !== 'assignment'));

// Carousel — dashboard banner slides managed by the admin (admin/banners.php)
$carouselItems = [];
$res = $conn->query(
    "SELECT id, image_path, title, caption, link_url
     FROM dashboard_banners
     WHERE status = 'Active'
     ORDER BY sort_order ASC, id ASC"
);
// $res is false if the dashboard_banners migration hasn't been run yet —
// fail safe to "no carousel" instead of a fatal error.
if ($res) {
    while ($r = $res->fetch_assoc())
        $carouselItems[] = $r;
}

// Attendance rate — Present/Late count toward attendance, Excused is neutral
$attTotal = 0;
$attGood = 0;
$res = $conn->query("SELECT status, COUNT(*) AS cnt FROM attendance WHERE student_id = $user_id GROUP BY status");
while ($res && $r = $res->fetch_assoc()) {
    if ($r['status'] === 'Excused')
        continue;
    $attTotal += (int) $r['cnt'];
    if ($r['status'] === 'Present' || $r['status'] === 'Late')
        $attGood += (int) $r['cnt'];
}
$attendanceRate = $attTotal > 0 ? round($attGood / $attTotal * 100) : null;

// Overall average grade — average of the subject totals from the gradebook
// (the same totals the teacher sees; missing work counts as 0)
$myGradebook = studentGradebook($conn, $user_id, $mySubjects);
$avg = studentGeneralAverage($myGradebook);
$averageGrade = $avg === null ? null : (int) round($avg);

// Due this week — from the calendar events already built
$weekStart = date('Y-m-d');
$weekEnd = date('Y-m-d', strtotime('+6 days'));
$dueThisWeek = 0;
foreach ($calendarEvents as $d => $items) {
    if ($d >= $weekStart && $d <= $weekEnd)
        $dueThisWeek += count($items);
}

// Per-subject module completion, for the progress bars
$subjectProgress = [];
foreach ($mySubjects as $s) {
    $code = $s['course_code'];
    $total = count($modulesByCourse[$code] ?? []);
    $done = 0;
    foreach ($modulesByCourse[$code] ?? [] as $m) {
        if (isset($myProgress[$m['id']]))
            $done++;
    }
    $subjectProgress[$code] = ['done' => $done, 'total' => $total, 'pct' => $total > 0 ? round($done / $total * 100) : 0];
}

// My Learning Progress + Recent Grades — computed by the same function the
// AJAX polling endpoint (api/dashboard_progress.php) calls, so the numbers
// shown on first load and the numbers the live-update JS fetches later can
// never disagree.
$progress = getStudentLearningProgress($conn, $user_id);

// Visual gradients for cards
$thumbGrads = [
    'linear-gradient(135deg,#003087,#0056d6)',
    'linear-gradient(135deg,#00875a,#00c97f)',
    'linear-gradient(135deg,#b38600,#ffd000)',
    'linear-gradient(135deg,#7b2d8b,#c850c0)',
    'linear-gradient(135deg,#c0392b,#e74c3c)',
    'linear-gradient(135deg,#0097a7,#26c6da)',
];
// Awards — recent recognitions for the logged-in student only (id from the
// session, never from the request). Empty list if the awards migration
// hasn't been run yet, so the dashboard never breaks because of it.
$dashAwardsLimit = 4;
$recentAwards = awards_fetch_for_student($conn, $user_id, ['limit' => $dashAwardsLimit]);
$awardsTotal = count($recentAwards) < $dashAwardsLimit ? count($recentAwards) : awards_count_for_student($conn, $user_id);

$currentPage = 'dashboard.php';
$pageTitle = 'Dashboard';
require __DIR__ . '/../includes/header.php';
?>

<div class="section-panel active">
    <div class="page-header">
        <h2 class="page-title">Welcome back, <?= htmlspecialchars($first_name) ?>!</h2>
    </div>

    <div class="dash-split">
        <div style="display:flex; flex-direction:column; gap:1.5rem;">
            <div class="welcome-card" style="border-radius:16px; overflow:hidden;">
                <div class="welcome-text">
                    <h2>Ready to learn?</h2>
                    <p>You have <?= $statQuizDue ?> quizzes due this week. Keep up the good work!</p>
                    <div style="display:flex; gap:0.5rem;">
                        <span class="badge"
                            style="background:rgba(255,255,255,0.2); color:white;"><?= htmlspecialchars($level) ?></span>
                        <?php if ($section): ?><span class="badge"
                                style="background:rgba(255,255,255,0.2); color:white;"><?= htmlspecialchars($section) ?></span><?php endif; ?>
                    </div>
                </div>
                <svg style="width:100px; height:100px; opacity:0.8; color:white;" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 10v6M2 10v6"></path>
                    <path d="M20 20a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v12z"></path>
                    <path d="M10 4h4"></path>
                    <path d="M8 4v4"></path>
                    <path d="M16 4v4"></path>
                </svg>
            </div>

            <?php if (!empty($carouselItems)): ?>
                <div class="dash-carousel" id="dash-carousel" data-live-keep>
                    <div class="dash-carousel-track" id="dash-carousel-track">
                        <?php foreach ($carouselItems as $ci):
                            $slideTag = $ci['link_url'] ? 'a' : 'div';
                            ?>
                            <div class="dash-carousel-slide">
                                <<?= $slideTag ?><?php if ($ci['link_url']): ?> href="<?= htmlspecialchars($ci['link_url']) ?>"
                                        target="_blank" rel="noopener"<?php endif; ?>>
                                    <img src="../<?= htmlspecialchars($ci['image_path']) ?>"
                                        alt="<?= htmlspecialchars($ci['title'] ?? '') ?>">
                                    <?php if ($ci['title'] || $ci['caption']): ?>
                                        <div class="dash-carousel-caption">
                                            <?php if ($ci['title']): ?>
                                                <div style="font-weight:700;"><?= htmlspecialchars($ci['title']) ?></div><?php endif; ?>
                                            <?php if ($ci['caption']): ?>
                                                <div style="font-size:.72rem;opacity:.85;"><?= htmlspecialchars($ci['caption']) ?></div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                </<?= $slideTag ?>>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <?php if (count($carouselItems) > 1): ?>
                        <button class="dash-carousel-arrow left" onclick="carouselShift(-1)"
                            aria-label="Previous">&#8249;</button>
                        <button class="dash-carousel-arrow right" onclick="carouselShift(1)" aria-label="Next">&#8250;</button>
                        <div class="dash-carousel-dots" id="dash-carousel-dots">
                            <?php foreach ($carouselItems as $i => $ci): ?>
                                <span class="dash-carousel-dot<?= $i === 0 ? ' active' : '' ?>"
                                    onclick="carouselGoTo(<?= $i ?>)"></span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <div class="card">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem;">
                    <h3 style="font-size:1.1rem; font-weight:700;">Assignments Due</h3>
                    <button onclick="location.href='assignments.php'"
                        style="border:none; background:none; color:var(--primary); font-weight:600; cursor:pointer;">View
                        All</button>
                </div>
                <?php if (empty($upcomingAssignments)): ?>
                    <p style="color:var(--text-muted);">No upcoming assignments. You're all caught up!</p>
                <?php else: ?>
                    <?php foreach ($upcomingAssignments as $ua): ?>
                        <div class="list-item">
                            <div style="width:8px; height:8px; border-radius:50%; flex-shrink:0; background:#e67e22;"></div>
                            <div style="flex:1;">
                                <div style="font-weight:600; font-size:0.9rem;"><?= htmlspecialchars($ua['label']) ?></div>
                                <div style="font-size:0.75rem; color:var(--text-muted);"><?= htmlspecialchars($ua['course']) ?>
                                    · Due <?= date('M j', strtotime($ua['date'])) ?>, <?= htmlspecialchars($ua['time']) ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div class="card">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem;">
                    <h3 style="font-size:1.1rem; font-weight:700;">My Subjects</h3>
                    <button onclick="location.href='my-subjects.php'"
                        style="border:none; background:none; color:var(--primary); font-weight:600; cursor:pointer;">View
                        All</button>
                </div>
                <?php if (empty($mySubjects)): ?>
                    <p style="color:var(--text-muted);">No enrolled subjects.</p>
                <?php else: ?>
                    <div class="dash-tile-grid">
                        <?php foreach (array_slice($mySubjects, 0, 4) as $s):
                            $prog = $subjectProgress[$s['course_code']] ?? ['done' => 0, 'total' => 0, 'pct' => 0]; ?>
                            <div class="list-item" style="align-items:flex-start;">
                                <div
                                    style="background:var(--primary-light); padding:8px; border-radius:8px; color:var(--primary);">
                                    <svg class="icon" viewBox="0 0 24 24">
                                        <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path>
                                        <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path>
                                    </svg>
                                </div>
                                <div style="flex:1;min-width:0;">
                                    <div style="display:flex;flex-wrap:wrap;justify-content:space-between;gap:.15rem .5rem;">
                                        <div style="font-weight:600;min-width:0;overflow-wrap:break-word;">
                                            <?= htmlspecialchars($s['course_name']) ?></div>
                                        <span
                                            style="font-size:.72rem;color:var(--text-muted);white-space:nowrap;"><?= $prog['done'] ?>/<?= $prog['total'] ?>
                                            modules</span>
                                    </div>
                                    <div style="font-size:0.8rem; color:var(--text-muted); margin-bottom:.35rem;">
                                        <?= htmlspecialchars($s['course_code']) ?>
                                    </div>
                                    <div class="subj-progress-track">
                                        <div class="subj-progress-fill" style="width:<?= $prog['pct'] ?>%;"></div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="card" id="dash-awards-card">
                <div
                    style="display:flex; justify-content:space-between; align-items:center; gap:.75rem; margin-bottom:.25rem; flex-wrap:wrap;">
                    <h3 style="font-size:1.1rem; font-weight:700;">My Awards<?php if ($awardsTotal > 0): ?> <span
                                class="badge badge-blue"
                                style="margin-left:.35rem; vertical-align:middle;"><?= (int) $awardsTotal ?></span><?php endif; ?>
                    </h3>
                    <?php if ($awardsTotal > 0): ?>
                        <button onclick="location.href='awards.php'"
                            style="border:none; background:none; color:var(--primary); font-weight:600; cursor:pointer;">View
                            All Awards</button>
                    <?php endif; ?>
                </div>
                <?php if (empty($recentAwards)): ?>
                    <?= student_awards_empty() ?>
                <?php else: ?>
                    <div class="aw-list">
                        <?php foreach ($recentAwards as $ra)
                            echo student_award_row($ra); ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="dash-right-col"
            style="display:flex; flex-direction:column; gap:1.5rem; position:sticky; top:1.5rem;">
            <div class="card cal-mini">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:.75rem;">
                    <h3 style="font-size:.95rem; font-weight:700;">Calendar</h3>
                    <div style="display:flex; align-items:center; gap:.35rem;">
                        <button class="cal-nav-btn" onclick="calShiftMonth(-1)"
                            aria-label="Previous month">&#8249;</button>
                        <button class="cal-nav-btn" onclick="calShiftMonth(1)" aria-label="Next month">&#8250;</button>
                    </div>
                </div>
                <div id="cal-month-label" data-live-keep
                    style="font-size:.75rem; font-weight:700; color:var(--text-main); margin-bottom:.5rem;"></div>
                <div class="cal-weekdays">
                    <span>S</span><span>M</span><span>T</span><span>W</span><span>T</span><span>F</span><span>S</span>
                </div>
                <div id="cal-grid" class="cal-grid" data-live-keep style="position:relative;"></div>
                <script type="application/json"
                    id="cal-events-json"><?= json_encode($calendarEvents ?: new stdClass(), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?></script>
                <div
                    style="display:flex; flex-direction:column; gap:.3rem; margin-top:.75rem; font-size:.68rem; color:var(--text-muted);">
                    <span><span class="cal-dot cal-dot-assign"></span> Assignment due</span>
                    <span><span class="cal-dot cal-dot-quiz"></span> Quiz window</span>
                </div>
            </div>

            <div class="card">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem;">
                    <h3 style="font-size:1.1rem; font-weight:700;">Updates &amp; Upcoming</h3>
                    <button onclick="closeNotifAndGoToPanel()"
                        style="border:none; background:none; color:var(--primary); font-weight:600; cursor:pointer;">View
                        All</button>
                </div>
                <?php if (empty($announcements) && empty($upcomingNonAssignments)): ?>
                    <p style="color:var(--text-muted);">Nothing new right now.</p>
                <?php else: ?>
                    <?php foreach (array_slice($upcomingNonAssignments, 0, 2) as $ev): ?>
                        <div class="list-item">
                            <div
                                style="width:8px; height:8px; border-radius:50%; flex-shrink:0; background:<?= $ev['type'] === 'assignment' ? '#e67e22' : 'var(--primary)' ?>;">
                            </div>
                            <div style="flex:1;">
                                <div style="font-weight:600; font-size:0.9rem;"><?= htmlspecialchars($ev['label']) ?></div>
                                <div style="font-size:0.75rem; color:var(--text-muted);"><?= htmlspecialchars($ev['course']) ?>
                                    · <?= date('M j', strtotime($ev['date'])) ?>, <?= htmlspecialchars($ev['time']) ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <?php foreach (array_slice($announcements, 0, 2) as $a): ?>
                        <div class="list-item" onclick="goToAnnouncement(<?= $a['id'] ?>)" style="cursor:pointer;">
                            <div style="width:8px; height:8px; background:var(--primary); border-radius:50%;"></div>
                            <div style="flex:1;">
                                <div style="font-weight:600; font-size:0.9rem;"><?= htmlspecialchars($a['title']) ?>
                                </div>
                                <div style="font-size:0.75rem; color:var(--text-muted);"><?= htmlspecialchars($a['author']) ?> ·
                                    <?= htmlspecialchars($a['posted_at']) ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; endif; ?>
            </div>

            <div class="card" id="learning-progress-card">
                <h3 style="font-size:1.1rem; font-weight:700; margin-bottom:1rem;">My Learning Progress</h3>
                <div style="display:flex; justify-content:space-between; align-items:baseline; margin-bottom:.4rem;">
                    <span style="font-size:.85rem; color:var(--text-muted);">Overall Progress</span>
                    <span id="progress-overall-pct"
                        style="font-size:1.3rem; font-weight:700; color:var(--primary);"><?= $progress['overall_progress'] ?>%</span>
                </div>
                <div class="subj-progress-track" style="margin-bottom:.9rem;">
                    <div id="progress-bar-fill" class="subj-progress-fill"
                        style="width:<?= $progress['overall_progress'] ?>%;"></div>
                </div>
                <div class="progress-stat-row">
                    <span class="progress-stat-label">Modules Completed</span>
                    <span id="progress-modules" class="progress-stat-value"><?= $progress['modules_done'] ?> /
                        <?= $progress['modules_total'] ?></span>
                </div>
                <div class="progress-stat-row">
                    <span class="progress-stat-label">Assignments Submitted</span>
                    <span id="progress-assignments"
                        class="progress-stat-value"><?= $progress['assignments_submitted'] ?> /
                        <?= $progress['assignments_total'] ?></span>
                </div>
                <div class="progress-stat-row">
                    <span class="progress-stat-label">Quizzes Completed</span>
                    <span id="progress-quizzes" class="progress-stat-value"><?= $progress['quizzes_done'] ?> /
                        <?= $progress['quizzes_total'] ?></span>
                </div>
            </div>

            <div class="card">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem;">
                    <h3 style="font-size:1.1rem; font-weight:700;">Recent Grades</h3>
                    <button onclick="location.href='grades.php'"
                        style="border:none; background:none; color:var(--primary); font-weight:600; cursor:pointer;">View
                        All</button>
                </div>
                <p id="recent-grades-empty"
                    style="color:var(--text-muted); <?= empty($progress['subject_grades']) ? '' : 'display:none;' ?>">No
                    grades available yet.</p>
                <div id="recent-grades-list">
                    <?php foreach (array_slice($progress['subject_grades'], 0, 5) as $sg):
                        $gc = $sg['percent'] >= 75 ? '#059669' : ($sg['percent'] >= 50 ? '#d97706' : '#dc2626'); ?>
                        <div class="progress-stat-row">
                            <span class="progress-stat-label"
                                style="color:var(--text-main); font-weight:600;"><?= htmlspecialchars($sg['course_name']) ?></span>
                            <span class="progress-stat-value" style="color:<?= $gc ?>;"><?= $sg['percent'] ?>%</span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="card" id="dash-glance-card">
                <h3 style="font-size:1.1rem; font-weight:700; margin-bottom:1rem;">At a Glance</h3>
                <div class="glance-grid">
                    <button type="button" class="glance-tile glance-link" onclick="location.href='attendance.php'">
                        <span class="glance-label">Attendance</span>
                        <span class="glance-value"><?= $attendanceRate !== null ? $attendanceRate . '%' : '—' ?></span>
                        <?php if ($attendanceRate !== null): ?>
                            <div class="subj-progress-track" style="margin-top:.4rem;">
                                <div class="subj-progress-fill"
                                    style="width:<?= (int) $attendanceRate ?>%; background:<?= $attendanceRate >= 90 ? '#059669' : ($attendanceRate >= 75 ? '#d97706' : '#dc2626') ?>;">
                                </div>
                            </div>
                        <?php else: ?>
                            <span class="glance-note">No records yet</span>
                        <?php endif; ?>
                    </button>
                    <div class="glance-tile">
                        <span class="glance-label">Average Grade</span>
                        <span class="glance-value" <?php if ($averageGrade !== null): ?>
                                style="color:<?= $averageGrade >= 75 ? '#059669' : ($averageGrade >= 50 ? '#d97706' : '#dc2626') ?>;"
                            <?php endif; ?>><?= $averageGrade !== null ? $averageGrade . '%' : '—' ?></span>
                        <span
                            class="glance-note"><?= $averageGrade !== null ? 'All graded work' : 'No grades yet' ?></span>
                    </div>
                    <button type="button" class="glance-tile glance-link" onclick="location.href='assignments.php'">
                        <span class="glance-label">Due This Week</span>
                        <span class="glance-value"><?= (int) $dueThisWeek ?></span>
                        <span class="glance-note">Assignments &amp; quizzes</span>
                    </button>
                    <button type="button" class="glance-tile glance-link" onclick="location.href='awards.php'">
                        <span class="glance-label">Awards Earned</span>
                        <span class="glance-value"><?= (int) $awardsTotal ?></span>
                        <span class="glance-note">View all awards</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<?= student_awards_css() ?>
<style>
    .cal-nav-btn {
        width: 24px;
        height: 24px;
        border-radius: 6px;
        border: 1px solid var(--border);
        background: var(--bg-card);
        color: var(--text-main);
        font-size: 1rem;
        line-height: 1;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .cal-nav-btn:hover {
        background: var(--primary-light);
        color: var(--primary);
    }

    .cal-mini {
        padding: 1.1rem;
    }

    .progress-stat-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: .5rem 0;
        font-size: .85rem;
    }

    .progress-stat-row+.progress-stat-row {
        border-top: 1px solid var(--border);
    }

    .progress-stat-label {
        color: var(--text-muted);
    }

    .progress-stat-value {
        font-weight: 600;
        color: var(--text-main);
    }

    .cal-weekdays {
        display: grid;
        grid-template-columns: repeat(7, minmax(0, 1fr));
        text-align: center;
        font-size: .6rem;
        font-weight: 700;
        color: var(--text-muted);
        margin-bottom: .25rem;
    }

    .cal-grid {
        display: grid;
        grid-template-columns: repeat(7, minmax(0, 1fr));
        gap: 1px;
    }

    .cal-day {
        aspect-ratio: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
        font-size: .68rem;
        cursor: pointer;
        position: relative;
        color: var(--text-main);
        transition: background .15s;
    }

    .cal-day:hover {
        background: var(--primary-light);
    }

    .cal-day.cal-outside {
        color: #cbd5e1;
        cursor: default;
    }

    .cal-day.cal-outside:hover {
        background: none;
    }

    .cal-day.cal-today {
        border: 1.5px solid var(--primary);
        font-weight: 800;
    }

    .cal-day.cal-selected {
        background: var(--primary);
        color: white;
    }

    .cal-day-dots {
        display: flex;
        gap: 2px;
        margin-top: 1px;
        height: 4px;
    }

    .cal-dot {
        width: 4px;
        height: 4px;
        border-radius: 50%;
        display: inline-block;
    }

    .cal-dot-assign {
        background: #e67e22;
    }

    .cal-dot-quiz {
        background: #003087;
    }

    .cal-day.cal-selected .cal-dot-assign,
    .cal-day.cal-selected .cal-dot-quiz {
        background: var(--ui-surface, white);
    }

    .cal-event-row {
        display: flex;
        align-items: flex-start;
        gap: .4rem;
        padding: .3rem 0;
        font-size: .7rem;
    }

    .cal-event-row+.cal-event-row {
        border-top: 1px solid var(--border);
    }

    .cal-popover {
        position: absolute;
        z-index: 20;
        background: #1e293b;
        color: #fff;
        border-radius: 10px;
        padding: .6rem .75rem;
        font-size: .68rem;
        box-shadow: 0 10px 28px rgba(0, 0, 0, .28);
        width: 170px;
        top: 0;
        right: 0;
    }

    .cal-popover-close {
        position: absolute;
        top: .3rem;
        right: .5rem;
        cursor: pointer;
        opacity: .6;
        font-size: .8rem;
    }

    .cal-popover .cal-dot {
        flex-shrink: 0;
        margin-top: 3px;
    }

    /* Per-subject progress bars */
    .subj-progress-track {
        height: 6px;
        border-radius: 100px;
        background: var(--border);
        overflow: hidden;
    }

    .subj-progress-fill {
        height: 100%;
        background: var(--primary);
        border-radius: 100px;
        transition: width .3s;
    }

    /* Dashboard image carousel ("swing pictures") */
    .dash-carousel {
        position: relative;
        border-radius: var(--radius);
        overflow: hidden;
        margin-bottom: 1.5rem;
        aspect-ratio: 1600 / 700;
        max-height: 420px;
        background: #0f172a;
    }

    .dash-carousel-track {
        display: flex;
        height: 100%;
        transition: transform .45s ease;
    }

    .dash-carousel-slide {
        min-width: 100%;
        height: 100%;
        position: relative;
    }

    .dash-carousel-slide a {
        display: block;
        width: 100%;
        height: 100%;
        text-decoration: none;
        color: inherit;
    }

    .dash-carousel-slide img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .dash-carousel-caption {
        position: absolute;
        left: 0;
        right: 0;
        bottom: 0;
        padding: 1rem 1.25rem .85rem;
        z-index: 2;
        background: linear-gradient(to top, rgba(0, 0, 0, .72), transparent);
        color: #fff;
        font-size: .92rem;
    }

    .dash-carousel-arrow {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        width: 30px;
        height: 30px;
        border-radius: 50%;
        border: none;
        background: rgba(255, 255, 255, .85);
        color: #1e293b;
        font-size: 1.1rem;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .dash-carousel-arrow.left {
        left: .75rem;
    }

    .dash-carousel-arrow.right {
        right: .75rem;
    }

    .dash-carousel-arrow:hover {
        background: var(--ui-surface, #fff);
    }

    .dash-carousel-dots {
        position: absolute;
        bottom: .6rem;
        left: 50%;
        transform: translateX(-50%);
        display: flex;
        gap: .35rem;
    }

    .dash-carousel-dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: rgba(255, 255, 255, .5);
        cursor: pointer;
    }

    .dash-carousel-dot.active {
        background: #fff;
        width: 16px;
        border-radius: 100px;
    }

    /* Wider screens: subjects and awards sit side by side as tiles
       instead of one long stacked list, so the left column is shorter. */
    .dash-tile-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: .75rem;
    }

    .dash-tile-grid .list-item {
        border: 1px solid var(--border);
        border-radius: 10px;
        padding: .85rem;
    }

    #dash-awards-card .aw-list {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: .75rem;
        margin-top: .75rem;
    }

    @media (max-width: 640px) {

        .dash-tile-grid,
        #dash-awards-card .aw-list {
            grid-template-columns: minmax(0, 1fr);
        }
    }

    #dash-awards-card .aw-row {
        border: 1px solid var(--border);
        border-radius: 4px 4px 10px 10px;
        padding: 0 .85rem .85rem;
    }

    #dash-awards-card .aw-row+.aw-row {
        border-top: 1px solid var(--border);
    }

    #dash-awards-card .aw-row-body {
        padding-top: .75rem;
    }

    /* At a Glance */
    .glance-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: .6rem;
    }

    .glance-tile {
        display: flex;
        flex-direction: column;
        gap: .15rem;
        padding: .75rem;
        border-radius: 10px;
        background: var(--bg-body);
        border: 1px solid var(--border);
        text-align: left;
        font: inherit;
        color: inherit;
        min-width: 0;
    }

    .glance-link {
        cursor: pointer;
        transition: border-color .15s;
    }

    .glance-link:hover {
        border-color: var(--primary);
    }

    .glance-link:focus-visible {
        outline: 2px solid var(--primary);
        outline-offset: 2px;
    }

    .glance-label {
        font-size: .72rem;
        font-weight: 600;
        color: var(--text-muted);
    }

    .glance-value {
        font-family: 'Nunito', sans-serif;
        font-size: 1.35rem;
        font-weight: 800;
        color: var(--text-main);
        line-height: 1.2;
    }

    .glance-note {
        font-size: .7rem;
        color: var(--text-muted);
    }

    .dash-split {
        display: grid;
        grid-template-columns: minmax(0, 2.3fr) minmax(280px, 1fr);
        gap: 1.5rem;
        align-items: start;
    }

    @media (max-width: 1200px) {
        .dash-split {
            grid-template-columns: minmax(0, 1.8fr) minmax(260px, 1fr);
        }
    }

    @media (max-width: 900px) {
        .dash-carousel {
            max-height: 200px;
        }

        .dash-split {
            grid-template-columns: minmax(0, 1fr);
        }

        .dash-right-col {
            position: static !important;
        }
    }
</style>

<script>
    let CAL_EVENTS = <?= json_encode($calendarEvents ?: new stdClass(), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    // Live updates (includes/live.php) swap the page in place; the calendar is
    // drawn by this script, so it redraws itself from the fresh due dates.
    window.elmsAfterLiveUpdate = function (doc) {
        const el = doc && doc.getElementById('cal-events-json');
        if (!el) return;
        let fresh;
        try { fresh = JSON.parse(el.textContent) || {}; } catch (e) { return; }
        if (JSON.stringify(fresh) === JSON.stringify(CAL_EVENTS)) return;   // same dates: keep an open day as it is
        CAL_EVENTS = fresh;
        renderCalendar();
    };
    const CAL_MONTH_NAMES = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
    let calViewDate = new Date();
    calViewDate.setDate(1);

    function calPad(n) { return String(n).padStart(2, '0'); }
    function calKey(y, m, d) { return `${y}-${calPad(m + 1)}-${calPad(d)}`; }

    function calShiftMonth(delta) {
        calViewDate.setDate(1);   // on the 31st, +1 month would otherwise skip a month (e.g. Oct 31 → Dec 1)
        calViewDate.setMonth(calViewDate.getMonth() + delta);
        renderCalendar();
    }

    function renderCalendar() {
        const y = calViewDate.getFullYear();
        const m = calViewDate.getMonth();
        document.getElementById('cal-month-label').textContent = `${CAL_MONTH_NAMES[m]} ${y}`;

        const grid = document.getElementById('cal-grid');
        grid.innerHTML = '';

        const firstWeekday = new Date(y, m, 1).getDay();
        const daysInMonth = new Date(y, m + 1, 0).getDate();
        const daysInPrevMonth = new Date(y, m, 0).getDate();
        const todayKey = calKey(new Date().getFullYear(), new Date().getMonth(), new Date().getDate());

        const cells = [];
        for (let i = firstWeekday - 1; i >= 0; i--) cells.push({ day: daysInPrevMonth - i, outside: true });
        for (let d = 1; d <= daysInMonth; d++) cells.push({ day: d, outside: false });
        // Fill the last week with the next month's first days (1, 2, 3…)
        let nextDay = 1;
        while (cells.length % 7 !== 0) cells.push({ day: nextDay++, outside: true });

        cells.forEach(cell => {
            const div = document.createElement('div');
            div.className = 'cal-day' + (cell.outside ? ' cal-outside' : '');
            const key = cell.outside ? null : calKey(y, m, cell.day);
            if (key === todayKey) div.classList.add('cal-today');

            const num = document.createElement('span');
            num.textContent = cell.day;
            div.appendChild(num);

            if (key && CAL_EVENTS[key]) {
                const dots = document.createElement('div');
                dots.className = 'cal-day-dots';
                const types = new Set(CAL_EVENTS[key].map(e => e.type));
                if (types.has('assignment')) { const dot = document.createElement('span'); dot.className = 'cal-dot cal-dot-assign'; dots.appendChild(dot); }
                if (types.has('quiz')) { const dot = document.createElement('span'); dot.className = 'cal-dot cal-dot-quiz'; dots.appendChild(dot); }
                div.appendChild(dots);
                div.onclick = () => selectCalDay(key, div);
            }
            grid.appendChild(div);
        });
    }

    function selectCalDay(key, el) {
        document.querySelectorAll('.cal-day.cal-selected').forEach(d => d.classList.remove('cal-selected'));
        const grid = document.getElementById('cal-grid');
        const existing = grid.querySelector('.cal-popover');
        if (existing) existing.remove();
        // Clicking the already-selected day just closes the popover.
        if (el.dataset.wasSelected === '1') { el.dataset.wasSelected = '0'; return; }
        grid.querySelectorAll('.cal-day').forEach(d => d.dataset.wasSelected = '0');
        el.classList.add('cal-selected');
        el.dataset.wasSelected = '1';

        const items = CAL_EVENTS[key] || [];
        const dateLabel = new Date(key + 'T00:00:00').toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
        const pop = document.createElement('div');
        pop.className = 'cal-popover';
        let html = `<span class="cal-popover-close" onclick="event.stopPropagation(); this.closest('.cal-popover').remove(); document.querySelectorAll('.cal-day.cal-selected').forEach(d=>{d.classList.remove('cal-selected'); d.dataset.wasSelected='0';})">&times;</span>`;
        html += `<div style="font-weight:700; margin-bottom:.3rem;">${dateLabel}</div>`;
        items.forEach(it => {
            const dotClass = it.type === 'assignment' ? 'cal-dot-assign' : 'cal-dot-quiz';
            html += `<div class="cal-event-row"><span class="cal-dot ${dotClass}"></span><div><div style="font-weight:600;">${escHtmlCal(it.label)}</div><div style="color:#cbd5e1; font-size:.65rem;">${escHtmlCal(it.course)} · ${escHtmlCal(it.time)}</div></div></div>`;
        });
        pop.innerHTML = html;
        grid.appendChild(pop);
    }

    // ── Image carousel ("swing pictures") ──────────────────────────────
    let carouselIndex = 0;
    const carouselTrack = document.getElementById('dash-carousel-track');
    const carouselSlideCount = carouselTrack ? carouselTrack.children.length : 0;
    let carouselTimer = null;

    function carouselRender() {
        if (!carouselTrack) return;
        carouselTrack.style.transform = `translateX(-${carouselIndex * 100}%)`;
        document.querySelectorAll('.dash-carousel-dot').forEach((d, i) => d.classList.toggle('active', i === carouselIndex));
    }
    function carouselGoTo(i) {
        carouselIndex = (i + carouselSlideCount) % carouselSlideCount;
        carouselRender();
        carouselRestartTimer();
    }
    function carouselShift(delta) { carouselGoTo(carouselIndex + delta); }
    function carouselRestartTimer() {
        if (carouselTimer) clearInterval(carouselTimer);
        if (carouselSlideCount > 1) carouselTimer = setInterval(() => carouselShift(1), 5000);
    }
    if (carouselSlideCount > 1) carouselRestartTimer();

    function escHtmlCal(s) {
        const d = document.createElement('div');
        d.textContent = String(s || '');
        return d.innerHTML;
    }

    renderCalendar();

    // ── Live-updating Learning Progress / Recent Grades ────────────────
    // Polls periodically so grading done elsewhere (e.g. by a teacher) shows
    // up without a manual reload, and also refreshes instantly if another
    // open tab reports a module/assignment/quiz was just completed.
    const PROGRESS_POLL_MS = 30000;

    async function refreshLearningProgress() {
        try {
            const res = await fetch('../api/dashboard_progress.php');
            const json = await res.json();
            if (json && json.success) renderLearningProgress(json.data);
        } catch (e) {
            // Silent — the next poll (or next instant signal) will retry.
        }
    }

    function escHtmlDash(s) {
        const d = document.createElement('div');
        d.textContent = String(s || '');
        return d.innerHTML;
    }

    function renderLearningProgress(d) {
        const pctEl = document.getElementById('progress-overall-pct');
        const fillEl = document.getElementById('progress-bar-fill');
        const modEl = document.getElementById('progress-modules');
        const assignEl = document.getElementById('progress-assignments');
        const quizEl = document.getElementById('progress-quizzes');
        if (pctEl) pctEl.textContent = d.overall_progress + '%';
        if (fillEl) fillEl.style.width = d.overall_progress + '%';
        if (modEl) modEl.textContent = d.modules_done + ' / ' + d.modules_total;
        if (assignEl) assignEl.textContent = d.assignments_submitted + ' / ' + d.assignments_total;
        if (quizEl) quizEl.textContent = d.quizzes_done + ' / ' + d.quizzes_total;

        const list = document.getElementById('recent-grades-list');
        const empty = document.getElementById('recent-grades-empty');
        if (!list || !empty) return;
        const grades = d.subject_grades || [];
        if (!grades.length) {
            list.innerHTML = '';
            empty.style.display = '';
            return;
        }
        empty.style.display = 'none';
        list.innerHTML = grades.slice(0, 5).map(sg => {
            const color = sg.percent >= 75 ? '#059669' : (sg.percent >= 50 ? '#d97706' : '#dc2626');
            return '<div class="progress-stat-row">'
                + '<span class="progress-stat-label" style="color:var(--text-main); font-weight:600;">' + escHtmlDash(sg.course_name) + '</span>'
                + '<span class="progress-stat-value" style="color:' + color + ';">' + sg.percent + '%</span>'
                + '</div>';
        }).join('');
    }

    setInterval(refreshLearningProgress, PROGRESS_POLL_MS);

    // Instant refresh when a module/assignment/quiz is completed in another
    // open tab — see notifyProgressChanged() in includes/footer.php.
    window.addEventListener('storage', (e) => {
        if (e.key === 'elms_progress_ping') refreshLearningProgress();
    });

    // Also refresh whenever the student switches back to this tab, in case
    // something changed while it was in the background.
    document.addEventListener('visibilitychange', () => {
        if (document.visibilityState === 'visible') refreshLearningProgress();
    });
</script>

<?php require __DIR__ . '/../includes/footer.php'; ?>