<?php
// ============================================================
//  Arandia College eLMS — Gradebook (shared by teacher + student)
//  File: includes/gradebook.php
//
//  Items come from gradebook_items (kept in sync with assignments
//  and quizzes by database triggers — see migration_gradebook.sql).
//  Scores are read live from where they are recorded:
//    assignment → submissions.score
//    quiz       → best submitted quiz_attempts.score
//    manual     → gradebook_scores.score
//  So a grade saved anywhere shows up everywhere at once.
//
//  Cell states: graded · pending (submitted, waiting for a grade)
//               missing (past due, nothing submitted → counts as 0)
//               none    (not due yet / not submitted → not counted)
// ============================================================

if (!function_exists('gradebookBuild')) {

  function gbNum($v): string
  {
    $n = (float) $v;
    return floor($n) == $n ? (string) (int) $n : rtrim(rtrim(number_format($n, 2, '.', ''), '0'), '.');
  }

  /**
   * @param string $itemScope extra SQL condition on gradebook_items `gi` (and the
   *        joined assignment `a` / quiz `q`) — e.g. which teacher's items to show.
   *        Build it with includes/scope.php (scopeTeacherOwned / scopeStudentOwned).
   * @return array{items: array, cells: array, totals: array}
   */
  function gradebookBuild(mysqli $conn, int $courseId, array $studentIds, string $itemScope = '1=1'): array
  {
    $hasTable = (bool) $conn->query("SHOW TABLES LIKE 'gradebook_items'")->num_rows;
    $items = [];

    if ($hasTable) {
      $res = $conn->query(
        "SELECT gi.id, gi.source_type, gi.source_id, gi.title AS gi_title, gi.max_score AS gi_max, gi.created_at,
                a.title AS a_title, a.max_score AS a_max, a.due_date AS a_due,
                q.title AS q_title, q.max_score AS q_max, q.close_at AS q_due, q.status AS q_status
         FROM gradebook_items gi
         LEFT JOIN assignments a ON gi.source_type = 'assignment' AND a.id = gi.source_id
         LEFT JOIN quizzes q     ON gi.source_type = 'quiz'       AND q.id = gi.source_id
         WHERE gi.course_id = $courseId AND $itemScope
           AND (gi.source_type = 'manual' OR a.id IS NOT NULL OR (q.id IS NOT NULL AND q.status = 'Published'))
         ORDER BY gi.created_at, gi.id"
      );
      while ($res && $r = $res->fetch_assoc()) {
        $type = $r['source_type'];
        $items[] = [
          'id' => (int) $r['id'],
          'type' => $type,
          'source_id' => $r['source_id'] !== null ? (int) $r['source_id'] : null,
          'title' => $type === 'assignment' ? $r['a_title'] : ($type === 'quiz' ? $r['q_title'] : $r['gi_title']),
          'max' => (float) ($type === 'assignment' ? $r['a_max'] : ($type === 'quiz' ? $r['q_max'] : $r['gi_max'])),
          'due' => $type === 'assignment' ? $r['a_due'] : ($type === 'quiz' ? $r['q_due'] : null),
        ];
      }
    } else {
      // Before migration_gradebook.sql: build the columns straight from assignments and quizzes
      $res = $conn->query("SELECT id, title, max_score, due_date, created_at FROM assignments WHERE course_id = $courseId AND " . str_replace('gi.', 'assignments.', $itemScope));
      while ($res && $r = $res->fetch_assoc())
        $items[] = ['id' => 'a' . $r['id'], 'type' => 'assignment', 'source_id' => (int) $r['id'], 'title' => $r['title'], 'max' => (float) $r['max_score'], 'due' => $r['due_date'], '_c' => $r['created_at']];
      $res = $conn->query("SELECT id, title, max_score, close_at, created_at FROM quizzes WHERE course_id = $courseId AND status = 'Published' AND " . str_replace('gi.', 'quizzes.', $itemScope));
      while ($res && $r = $res->fetch_assoc())
        $items[] = ['id' => 'q' . $r['id'], 'type' => 'quiz', 'source_id' => (int) $r['id'], 'title' => $r['title'], 'max' => (float) $r['max_score'], 'due' => $r['close_at'], '_c' => $r['created_at']];
      usort($items, fn($x, $y) => strcmp($x['_c'], $y['_c']));
    }

    $sIn = $studentIds ? implode(',', array_map('intval', $studentIds)) : '0';
    $byType = ['assignment' => [], 'quiz' => [], 'manual' => []];
    foreach ($items as $it) $byType[$it['type']][$it['type'] === 'manual' ? $it['id'] : $it['source_id']] = $it['id'];

    $raw = [];   // [student][itemId] => ['score' => ?, 'submitted' => bool]
    if ($byType['assignment']) {
      $aIn = implode(',', array_keys($byType['assignment']));
      $hasAttempts = (bool) $conn->query("SHOW COLUMNS FROM submissions LIKE 'attempt_count'")->num_rows;
      $res = $conn->query("SELECT assignment_id, student_id, score, " . ($hasAttempts ? 'attempt_count' : '1 AS attempt_count') . " AS att
                           FROM submissions WHERE assignment_id IN ($aIn) AND student_id IN ($sIn)");
      while ($res && $r = $res->fetch_assoc()) {
        $raw[(int) $r['student_id']][$byType['assignment'][(int) $r['assignment_id']]] =
          ['score' => $r['score'], 'submitted' => (int) $r['att'] > 0];
      }
    }
    if ($byType['quiz']) {
      $qIn = implode(',', array_keys($byType['quiz']));
      $res = $conn->query("SELECT quiz_id, student_id, MAX(score) AS best, COUNT(*) AS n
                           FROM quiz_attempts WHERE quiz_id IN ($qIn) AND student_id IN ($sIn) AND status <> 'In Progress'
                           GROUP BY quiz_id, student_id");
      while ($res && $r = $res->fetch_assoc()) {
        $raw[(int) $r['student_id']][$byType['quiz'][(int) $r['quiz_id']]] = ['score' => $r['best'], 'submitted' => (int) $r['n'] > 0];
      }
    }
    if ($byType['manual']) {
      $mIn = implode(',', array_keys($byType['manual']));
      $res = $conn->query("SELECT item_id, student_id, score FROM gradebook_scores WHERE item_id IN ($mIn) AND student_id IN ($sIn)");
      while ($res && $r = $res->fetch_assoc()) {
        $raw[(int) $r['student_id']][(int) $r['item_id']] = ['score' => $r['score'], 'submitted' => false];
      }
    }

    $now = time();
    $cells = $totals = [];
    foreach ($studentIds as $sid) {
      $sid = (int) $sid;
      $got = $max = 0.0;
      $pending = 0;
      foreach ($items as $it) {
        $r = $raw[$sid][$it['id']] ?? null;
        if ($r && $r['score'] !== null) {
          $state = 'graded';
        } elseif ($r && $r['submitted']) {
          $state = 'pending';
        } elseif ($it['type'] !== 'manual' && $it['due'] && strtotime($it['due']) < $now) {
          $state = 'missing';
        } else {
          $state = 'none';
        }
        $cells[$sid][$it['id']] = ['state' => $state, 'score' => $state === 'graded' ? gbNum($r['score']) : null];
        if ($state === 'graded') { $got += (float) $r['score']; $max += $it['max']; }
        elseif ($state === 'missing') { $max += $it['max']; }
        elseif ($state === 'pending') { $pending++; }
      }
      $totals[$sid] = [
        'score' => gbNum($got), 'max' => gbNum($max), 'pending' => $pending,
        'percent' => $max > 0 ? round($got / $max * 100, 1) : null,
      ];
    }
    foreach ($items as &$it) { $it['max'] = gbNum($it['max']); unset($it['_c']); }
    unset($it);
    return ['items' => $items, 'cells' => $cells, 'totals' => $totals];
  }
}
