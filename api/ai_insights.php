<?php
// ============================================================
//  Arandia College eLMS — AI Insights API (Teacher only)
//  File: api/ai_insights.php
//  Actions:
//    GET  ?action=at_risk&course_id=X   → students below threshold
//    POST ?action=suggest_plan          → AI action plan for 1 student
//  Scores come from the Gradebook (includes/gradebook.php), the same
//  numbers the teacher sees on teacher/grades.php. Only students in the
//  sections where the teacher handles the subject are included.
// ============================================================
session_start();
require_once '../config/conn.php';
require_once '../includes/AiInsights.php';
require_once '../includes/gradebook.php';
require_once '../includes/scope.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Teacher') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Session expired. Please refresh the page and log in again.']);
    exit;
}

const AT_RISK_THRESHOLD = 75.0; // average % below this is flagged

$teacher_id = (int) $_SESSION['user_id'];
$action = $_GET['action'] ?? '';

function aiTeacherOwnsCourse(mysqli $conn, int $teacher_id, int $course_id): bool
{
    $stmt = $conn->prepare("SELECT id FROM teacher_assignments WHERE teacher_id = ? AND course_id = ? LIMIT 1");
    $stmt->bind_param('ii', $teacher_id, $course_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return (bool) $row;
}

/**
 * Graded work per student, read from the Gradebook (includes/gradebook.php) so the
 * numbers here are exactly what the teacher sees on the Grades page:
 * graded items count at their score, past-due missing work counts as 0,
 * pending / not-yet-due work is not counted.
 * Returns [student_id => ['items' => [['label','percent','missing']], 'average' => float|null]].
 */
function getGradedItemsByStudent(mysqli $conn, int $course_id, array $studentIds): array
{
    $result = [];
    if (empty($studentIds))
        return $result;
    global $teacher_id;
    $gb = gradebookBuild($conn, $course_id, array_map('intval', $studentIds), scopeTeacherOwned($conn, 'gi', 'gradebook_items', (int) $teacher_id));
    foreach ($studentIds as $sid) {
        $sid = (int) $sid;
        $items = [];
        foreach ($gb['items'] as $it) {
            $cell = $gb['cells'][$sid][$it['id']] ?? null;
            $max = (float) $it['max'] > 0 ? (float) $it['max'] : 100;
            if ($cell && $cell['state'] === 'graded') {
                $items[] = ['label' => $it['title'], 'percent' => round(min(100, max(0, ((float) $cell['score'] / $max) * 100)), 1), 'missing' => false];
            } elseif ($cell && $cell['state'] === 'missing') {
                $items[] = ['label' => $it['title'] . ' (missing)', 'percent' => 0.0, 'missing' => true];
            }
        }
        $result[$sid] = ['items' => $items, 'average' => $gb['totals'][$sid]['percent'] ?? null];
    }
    return $result;
}

/** Active students in the sections where this teacher handles the subject (same rule as the Gradebook). */
function aiClassStudents(mysqli $conn, int $teacher_id, int $course_id, string $section = ''): array
{
    $st = $conn->prepare(
        "SELECT DISTINCT u.id, u.first_name, u.last_name, u.section_dept
         FROM users u
         JOIN enrollments e ON e.student_id = u.id AND e.course_id = ? AND e.status = 'Enrolled'
         JOIN teacher_assignments ta ON ta.course_id = e.course_id AND ta.section = u.section_dept AND ta.teacher_id = ?
         WHERE u.role = 'Student' AND u.status = 'Active' AND (? = '' OR u.section_dept = ?)
         ORDER BY u.last_name, u.first_name"
    );
    $st->bind_param('iiss', $course_id, $teacher_id, $section, $section);
    $st->execute();
    $rows = $st->get_result()->fetch_all(MYSQLI_ASSOC);
    $st->close();
    return $rows;
}

// ── GET: list at-risk students for a subject ────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'at_risk') {
    $course_id = (int) ($_GET['course_id'] ?? 0);
    if (!$course_id || !aiTeacherOwnsCourse($conn, $teacher_id, $course_id)) {
        echo json_encode(['success' => false, 'message' => 'Not assigned to this subject.']);
        exit;
    }

    $students = aiClassStudents($conn, $teacher_id, $course_id, trim((string) ($_GET['section'] ?? '')));

    $itemsByStudent = getGradedItemsByStudent($conn, $course_id, array_column($students, 'id'));

    $out = [];
    foreach ($students as $s) {
        $info = $itemsByStudent[(int) $s['id']] ?? ['items' => [], 'average' => null];
        $items = $info['items'];
        if (!$items || $info['average'] === null)
            continue; // no graded or missing work yet — nothing to flag
        $average = (float) $info['average'];
        if ($average < AT_RISK_THRESHOLD) {
            $out[] = [
                'id' => (int) $s['id'],
                'name' => $s['last_name'] . ', ' . $s['first_name'],
                'first_name' => $s['first_name'],
                'last_name' => $s['last_name'],
                'section_dept' => $s['section_dept'],
                'average' => $average,
                'graded_count' => count($items),
                'missing_count' => count(array_filter($items, fn($i) => $i['missing'])),
            ];
        }
    }
    usort($out, fn($a, $b) => $a['average'] <=> $b['average']);

    echo json_encode(['success' => true, 'data' => $out, 'threshold' => AT_RISK_THRESHOLD]);
    exit;
}

// ── POST: generate an AI recommendation for one student ─────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'suggest_plan') {
    $apiKey = defined('GROQ_API_KEY') ? GROQ_API_KEY : (getenv('GROQ_API_KEY') ?: '');
    if (empty($apiKey)) {
        echo json_encode(['success' => false, 'message' => 'AI insights are not available right now. Please contact the admin.']);
        exit;
    }

    $body = json_decode(file_get_contents('php://input'), true) ?? [];
    $student_id = (int) ($body['student_id'] ?? 0);
    $course_id = (int) ($body['course_id'] ?? 0);

    if (!$student_id || !$course_id || !aiTeacherOwnsCourse($conn, $teacher_id, $course_id)) {
        echo json_encode(['success' => false, 'message' => 'Not authorized.']);
        exit;
    }

    // The student must be in one of this teacher's sections for the subject.
    $stu = null;
    foreach (aiClassStudents($conn, $teacher_id, $course_id) as $row) {
        if ((int) $row['id'] === $student_id) { $stu = ['name' => $row['last_name'] . ', ' . $row['first_name']]; break; }
    }
    if (!$stu) {
        echo json_encode(['success' => false, 'message' => 'This student is not in your class for this subject.']);
        exit;
    }

    $stmt = $conn->prepare("SELECT course_code, course_name FROM courses WHERE id = ?");
    $stmt->bind_param('i', $course_id);
    $stmt->execute();
    $c = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    $courseName = $c ? trim($c['course_code'] . ' — ' . $c['course_name'], ' —') : 'this subject';

    $info = getGradedItemsByStudent($conn, $course_id, [$student_id])[$student_id] ?? ['items' => [], 'average' => null];
    $items = array_map(fn($i) => ['label' => $i['label'], 'percent' => $i['percent']], $info['items']);
    if (!$items || $info['average'] === null) {
        echo json_encode(['success' => false, 'message' => 'No graded work yet for this student.']);
        exit;
    }
    $average = (float) $info['average'];

    usort($items, fn($a, $b) => $a['percent'] <=> $b['percent']);
    $weakItems = array_values(array_slice(array_filter($items, fn($i) => $i['percent'] < 70), 0, 3));

    // ── Per-user limit (includes/ai_guard.php, loaded by AiInsights.php) ──
    $wait = aiRateCheck($conn, $teacher_id, 'ai_insights');
    if ($wait > 0) {
        http_response_code(429);
        echo json_encode(['success' => false, 'message' => aiRateMessage($wait, 'requests'), 'retry_after' => $wait]);
        exit;
    }

    $res = AiInsights::suggestActionPlan($apiKey, $stu['name'], $courseName, $average, $weakItems);
    if (isset($res['error'])) {
        echo json_encode(['success' => false, 'message' => $res['error']]);
        exit;
    }

    echo json_encode(['success' => true, 'plan' => $res['plan'], 'average' => $average, 'weak_items' => $weakItems]);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid request.']);
