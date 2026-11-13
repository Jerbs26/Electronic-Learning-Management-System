<?php
// ============================================================
//  Arandia College eLMS — Live updates + notifications
//  File: api/sync.php
//
//  The database writes a row to `sync_events` (by triggers, see
//  database/migration_integration.sql) whenever something that other
//  people care about changes: an assignment or quiz is published, a
//  student submits, a grade or attendance is saved, an announcement is
//  posted, an admin enrolls a student or assigns a class, and so on.
//  Every page polls this endpoint (includes/footer_common.php) and only
//  receives the events its user is allowed to see — the same rules the
//  pages use (includes/scope.php). Nothing private is sent: other users
//  only get a short message and ids, never scores or file paths.
//
//    GET  ?action=poll&after=ID       new events since ID  (+ last id)
//    GET  ?action=notifications       latest 20 notifications + unread count
//    POST ?action=mark_read           mark everything up to now as read
// ============================================================
session_start();
require_once '../config/conn.php';
require_once '../includes/role_guard.php';
header('Content-Type: application/json');
header('Cache-Control: no-store');

$uid = requireApiRole(['Admin', 'Teacher', 'Student', 'Admission']);
$role = $_SESSION['role'];
$action = $_GET['action'] ?? 'poll';

if (!$conn->query("SHOW TABLES LIKE 'sync_events'")->num_rows) {
    echo json_encode(['success' => true, 'enabled' => false, 'events' => [], 'last' => 0]);
    exit;
}
// Session data is not needed after this point; release the lock so polling
// never slows down the user's other requests.
session_write_close();

// Keep the feed small: 30 days is plenty for live updates and the bell
if (mt_rand(1, 200) === 1) $conn->query("DELETE FROM sync_events WHERE created_at < NOW() - INTERVAL 30 DAY");

/** SQL condition: events this user may see. Mirrors includes/scope.php. */
function syncScope(mysqli $conn, string $role, int $uid): string
{
    $studentKinds = "'submission.new','quiz.submitted','grade.updated','attendance.updated','enrollment.changed','module.progress','award.changed'";
    if ($role === 'Student') {
        $me = $conn->query("SELECT section_dept FROM users WHERE id = $uid")->fetch_assoc();
        $sec = $conn->real_escape_string((string) ($me['section_dept'] ?? ''));
        return "(se.user_id = $uid
              OR (se.user_id IS NULL AND se.audience IN ('all','students') AND (
                    (se.course_id IS NULL AND se.owner_id IS NULL)
                 OR (se.course_id IN (SELECT course_id FROM enrollments WHERE student_id = $uid AND status = 'Enrolled')
                     AND (se.owner_id IS NULL OR EXISTS (SELECT 1 FROM teacher_assignments ta
                          WHERE ta.teacher_id = se.owner_id AND ta.course_id = se.course_id AND ta.section = '$sec')))))
              OR (se.kind = 'class.changed' AND se.section = '$sec'
                  AND se.course_id IN (SELECT course_id FROM enrollments WHERE student_id = $uid AND status = 'Enrolled')))";
    }
    if ($role === 'Teacher') {
        return "(se.user_id = $uid
              OR (se.kind IN ($studentKinds) AND se.kind <> 'award.changed'
                  AND EXISTS (SELECT 1 FROM teacher_assignments ta WHERE ta.teacher_id = $uid AND ta.course_id = se.course_id AND ta.section = se.section)
                  AND (se.owner_id IS NULL OR se.owner_id = $uid OR se.kind IN ('enrollment.changed','module.progress','attendance.updated')))
              OR (se.kind NOT IN ($studentKinds) AND se.kind NOT IN ('class.changed','account.changed','banner.changed')
                  AND se.audience IN ('all','teachers') AND (
                    (se.course_id IS NULL AND se.owner_id IS NULL)
                 OR (se.course_id IN (SELECT course_id FROM teacher_assignments WHERE teacher_id = $uid)
                     AND (se.owner_id IS NULL OR se.owner_id = $uid)))))";
    }
    if ($role === 'Admission') {
        // Admission office: only its own events + student accounts / enrollment changes
        return "(se.user_id = $uid OR se.kind IN ('account.changed','enrollment.changed','class.changed','subject.changed','section.changed'))";
    }
    // Admin / Head Admin: school-level changes (accounts, enrollment, classes, modules, posts, banners)
    return "(se.user_id = $uid OR se.audience = 'admins'
             OR se.kind IN ('account.changed','enrollment.changed','class.changed','module.changed','banner.changed','subject.changed')
             OR (se.kind IN ('announcement.new','announcement.deleted') AND se.owner_id IS NULL))";
}

/** Short, role-appropriate text + link + whether it belongs in the bell. */
function syncDescribe(array $e, string $role, int $uid): array
{
    $t = $e['title'] !== null ? $e['title'] : '';
    $c = $e['course_name'] ?? '';
    $who = trim(($e['first_name'] ?? '') . ' ' . ($e['last_name'] ?? ''));
    $msg = ''; $link = null; $notify = false;
    switch ($e['kind']) {
        case 'assignment.new':
            $msg = "New assignment: $t" . ($c ? " · $c" : ''); $notify = $role === 'Student';
            $link = $role === 'Student' ? 'assignments.php' : 'assignments.php'; break;
        case 'assignment.updated': $msg = "Assignment updated: $t"; $notify = $role === 'Student'; $link = 'assignments.php'; break;
        case 'assignment.deleted': $msg = "Assignment removed: $t"; $link = 'assignments.php'; break;
        case 'quiz.new': $msg = "New quiz: $t" . ($c ? " · $c" : ''); $notify = $role === 'Student'; $link = 'assignments.php#quizzes'; break;
        case 'quiz.updated': $msg = "Quiz updated: $t"; $notify = $role === 'Student'; $link = 'assignments.php#quizzes'; break;
        case 'quiz.deleted': $msg = "Quiz removed: $t"; $link = 'assignments.php#quizzes'; break;
        case 'quiz.draft': $msg = "Quiz saved as draft: $t"; $link = 'assignments.php#quizzes'; break;
        case 'quiz.content': $msg = "Quiz questions updated: $t"; $link = 'assignments.php#quizzes'; break;
        case 'submission.new':
            if ($role === 'Teacher') { $msg = "$who submitted $t"; $notify = true; $link = 'grade.php?id=' . (int) $e['ref_id'] . '&student=' . (int) $e['user_id']; }
            else { $msg = "Your work for $t was submitted"; $link = 'assignments.php'; }
            break;
        case 'quiz.submitted':
            if ($role === 'Teacher') { $msg = "$who finished $t"; $notify = true; $link = 'assignments.php?course=' . (int) $e['course_id'] . '&section=' . rawurlencode((string) $e['section']) . '#quizzes'; }
            else { $msg = "Your quiz $t was submitted"; $link = 'assignments.php#quizzes'; }
            break;
        case 'grade.updated':
            if ($role === 'Student') { $msg = "Your grade for $t was updated" . ($c ? " · $c" : ''); $notify = true; $link = 'grades.php#g-' . (int) $e['course_id']; }
            else { $msg = "Grade saved for $who · $t"; $link = 'grades.php?course=' . (int) $e['course_id'] . '&section=' . rawurlencode((string) $e['section']); }
            break;
        case 'attendance.updated':
            if ($role === 'Student') { $msg = "Attendance updated" . ($c ? " · $c" : '') . ($t ? " ($t)" : ''); $notify = true; $link = 'attendance.php'; }
            else { $msg = "Attendance saved · $c"; $link = 'attendance.php'; }
            break;
        case 'announcement.new':
            $msg = 'New announcement: ' . $t; $notify = !($role === 'Teacher' && (int) $e['owner_id'] === $uid) && $role !== 'Admin' && $role !== 'Head Admin';
            $link = 'announcements.php'; break;
        case 'announcement.deleted': $msg = 'Announcement removed: ' . $t; $link = 'announcements.php'; break;
        case 'enrollment.changed':
            if ($role === 'Student') { $msg = 'Your subjects changed' . ($c ? " · $c" : ''); $notify = true; $link = 'my-subjects.php'; }
            elseif ($role === 'Teacher') { $msg = "Class list changed · $c · " . $e['section']; $notify = true; $link = 'classes.php'; }
            else { $msg = 'Enrollment changed'; $link = 'subjects-enrollment.php'; }
            break;
        case 'class.changed':
            if ($role === 'Teacher') { $msg = implode(' · ', array_filter(['Your classes changed', $c, (string) $e['section']], 'strlen')); $notify = true; $link = 'classes.php'; }
            elseif ($role === 'Student') { $msg = "Teacher assignment changed · $c"; $notify = true; $link = 'my-subjects.php'; }
            else { $msg = 'Class assignment changed'; $link = 'subjects-enrollment.php'; }
            break;
        case 'module.changed': $msg = 'Modules updated' . ($c ? " · $c" : ''); $notify = $role === 'Student'; $link = 'modules.php'; break;
        case 'subject.changed':
            // A subject was added or renamed (api/admin_subjects.php). Quiet: pages refresh, no bell.
            $msg = 'Subject updated: ' . ($c ?: $t);
            $link = $role === 'Student' ? 'my-subjects.php' : ($role === 'Teacher' ? 'classes.php' : ((int) $e['course_id'] > 0 ? 'modules.php?course=' . (int) $e['course_id'] : 'modules.php'));
            break;
        case 'section.changed':
            // A section was added, renamed, set Active / Inactive or deleted (api/sections.php). Quiet: pages refresh, no bell.
            $msg = 'Sections updated' . ($t !== '' ? ": $t" : '');
            $link = 'subjects-enrollment.php'; break;
        case 'assignment.moved':
        case 'quiz.moved':
            // The teacher put the work under another module (includes/module_links.php). Quiet: Modules pages refresh.
            $msg = ($e['kind'] === 'quiz.moved' ? 'Quiz' : 'Assignment') . " moved to another module: $t";
            $link = $role === 'Teacher' ? 'classes.php' : 'modules.php'; break;
        case 'module.progress': $msg = "$who finished a module · $c"; $link = 'classes.php'; break;
        case 'award.changed':
            $msg = $role === 'Student' ? 'You have a new or updated award' . ($c ? " · $c" : '') : 'Award updated';
            $notify = $role === 'Student'; $link = 'awards.php'; break;
        case 'account.changed':
            $msg = (int) $e['user_id'] === $uid ? 'Your account was updated' : 'An account was updated';
            $notify = (int) $e['user_id'] === $uid; $link = $role === 'Admin' || $role === 'Head Admin' ? 'accounts.php' : 'profile.php'; break;
        case 'banner.changed': $msg = 'Dashboard banners changed'; $link = 'dashboard.php'; break;
        // Contact Us inbox (api/contact.php, api/contact_messages.php). Admins only (audience 'admins'); quiet: the
        // Messages page and the sidebar badge update in place.
        case 'message.new': $msg = 'New message: ' . $t; $link = 'messages.php?open=' . (int) $e['ref_id']; break;
        case 'message.changed': $msg = 'Messages updated'; $link = 'messages.php'; break;
        // Admin → Settings saved (api/settings.php). Quiet: open pages update the name / school year in place.
        case 'settings.changed': $msg = 'System settings updated'; $link = null; break;
        default: $msg = 'Updated';
    }
    // Student: open the subject the event is about (student/_init.php checks
    // that it is one of the student's own subjects; otherwise all subjects show)
    if ($role === 'Student' && $link && (int) ($e['course_id'] ?? 0) > 0) {
        $page = strtok($link, '?#');
        if (in_array($page, ['modules.php', 'assignments.php', 'quizzes.php', 'grades.php', 'attendance.php'], true)) {
            $hash = strpos($link, '#') !== false ? substr($link, strpos($link, '#')) : '';   // e.g. #quizzes
            $link = $page . '?course=' . (int) $e['course_id'] . $hash;
        }
    }
    // Admission office only has Dashboard / Enroll Student / Students pages
    if ($role === 'Admission') { $link = 'students.php'; $notify = false; }
    return ['msg' => $msg, 'link' => $link, 'notify' => $notify];
}

function syncFetch(mysqli $conn, string $role, int $uid, string $extra, int $limit): array
{
    $scope = syncScope($conn, $role, $uid);
    // Names only for students the teacher is allowed to see (already enforced by $scope)
    $res = $conn->query(
        "SELECT se.id, se.kind, se.course_id, se.section, se.owner_id, se.user_id, se.ref_id, se.title, se.created_at,
                c.course_name, IF('$role' = 'Teacher' OR se.user_id = $uid, u.first_name, NULL) AS first_name,
                IF('$role' = 'Teacher' OR se.user_id = $uid, u.last_name, NULL) AS last_name
         FROM sync_events se
         LEFT JOIN courses c ON c.id = se.course_id
         LEFT JOIN users u ON u.id = se.user_id
         WHERE $extra AND ($scope OR se.kind = 'settings.changed')   -- system settings: every role
         ORDER BY se.id DESC LIMIT $limit"
    );
    $out = [];
    while ($res && $e = $res->fetch_assoc()) {
        $d = syncDescribe($e, $role, $uid);
        $out[] = ['id' => (int) $e['id'], 'kind' => $e['kind'], 'course_id' => $e['course_id'] !== null ? (int) $e['course_id'] : null,
                  'section' => $role === 'Student' ? null : $e['section'], 'ref_id' => $e['ref_id'] !== null ? (int) $e['ref_id'] : null,
                  'self' => (int) $e['user_id'] === $uid, 'at' => $e['created_at'],
                  'msg' => $d['msg'], 'link' => $d['link'], 'notify' => $d['notify']];
    }
    return $out;
}

$last = (int) ($conn->query("SELECT COALESCE(MAX(id), 0) FROM sync_events")->fetch_row()[0]);
$seen = 0;
$sr = $conn->query("SHOW COLUMNS FROM users LIKE 'notif_seen_id'");
$hasSeen = $sr && $sr->num_rows;
if ($hasSeen) $seen = (int) $conn->query("SELECT notif_seen_id FROM users WHERE id = $uid")->fetch_row()[0];

if ($action === 'poll') {
    // after = the newest event id the page was rendered with (includes/live.php).
    // 0 is a real value — the feed was empty when the page loaded — so the first
    // event after that must still be delivered (it used to be skipped).
    $after = isset($_GET['after']) ? (int) $_GET['after'] : -1;
    if ($after < 0 || $after > $last) {   // no position given (or the feed was reset): just tell the client where "now" is
        echo json_encode(['success' => true, 'enabled' => true, 'events' => [], 'last' => $last]);
        exit;
    }
    $events = array_reverse(syncFetch($conn, $role, $uid, "se.id > $after", 100));
    $unread = null;
    if (array_filter($events, fn($e) => $e['notify'])) {
        $unread = count(array_filter(syncFetch($conn, $role, $uid, "se.id > $seen", 100), fn($e) => $e['notify']));
    }
    echo json_encode(['success' => true, 'enabled' => true, 'events' => $events, 'last' => $last, 'unread' => $unread]);
    exit;
}

if ($action === 'notifications') {
    $rows = array_values(array_filter(syncFetch($conn, $role, $uid, '1=1', 150), fn($e) => $e['notify']));
    $rows = array_slice($rows, 0, 20);
    foreach ($rows as &$r) $r['unread'] = $r['id'] > $seen;
    unset($r);
    echo json_encode(['success' => true, 'enabled' => true, 'items' => $rows,
                      // counted over the newest 500 unread events (the bell shows "99+" above 99);
                      // 100 was too few — with many quiet events the count could stop going up
                      'unread' => count(array_filter(syncFetch($conn, $role, $uid, "se.id > $seen", 500), fn($e) => $e['notify'])), 'last' => $last]);
    exit;
}

if ($action === 'mark_read' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($hasSeen) {
        $upTo = min($last, max(0, (int) ($_GET['upto'] ?? $last)));
        $st = $conn->prepare("UPDATE users SET notif_seen_id = GREATEST(notif_seen_id, ?) WHERE id = ?");
        $st->bind_param('ii', $upTo, $uid);
        $st->execute();
    }
    echo json_encode(['success' => true, 'unread' => 0]);
    exit;
}

http_response_code(400);
echo json_encode(['success' => false, 'message' => 'Unknown action.']);