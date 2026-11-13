<?php
// ============================================================
//  Arandia College eLMS — Student attendance data
//  File: includes/student_attendance.php
//
//  One function, used by both student/attendance.php (first page
//  load) and api/student_attendance.php (live refresh), so the
//  numbers on screen and the numbers from polling always match.
//
//  Rate rule (same as the dashboard): Present and Late count as
//  attended, Excused is left out of the total, Absent counts against.
//  Read-only. Never throws: on any database problem it returns the
//  empty structure with 'ok' => false.
// ============================================================

if (!function_exists('getStudentAttendance')) {

    function getStudentAttendance(mysqli $conn, int $studentId): array
    {
        $out = [
            'ok' => true,
            'summary' => ['present' => 0, 'late' => 0, 'absent' => 0, 'excused' => 0, 'total' => 0, 'rate' => null],
            'subjects' => [],
            'records' => [],
            'signature' => '0',
            'last_recorded' => null,
        ];
        if ($studentId <= 0) {
            $out['ok'] = false;
            return $out;
        }

        try {
            // Subjects the student is currently enrolled in (shown even with no records yet).
            $subjects = [];
            $stmt = $conn->prepare(
                "SELECT c.id, c.course_code, c.course_name
                 FROM enrollments e
                 JOIN courses c ON c.id = e.course_id
                 WHERE e.student_id = ? AND e.status = 'Enrolled'
                 ORDER BY c.course_name"
            );
            if (!$stmt) throw new RuntimeException('prepare failed');
            $stmt->bind_param('i', $studentId);
            $stmt->execute();
            $res = $stmt->get_result();
            while ($r = $res->fetch_assoc()) {
                $cid = (int) $r['id'];
                $subjects[$cid] = [
                    'course_id' => $cid,
                    'code' => (string) $r['course_code'],
                    'name' => (string) $r['course_name'],
                    'present' => 0, 'late' => 0, 'absent' => 0, 'excused' => 0, 'total' => 0, 'rate' => null,
                ];
            }
            $stmt->close();

            // Every attendance record for THIS student only.
            $stmt = $conn->prepare(
                "SELECT a.id, a.course_id, a.`date` AS att_date, a.status,
                        COALESCE(a.remark, '') AS remark, a.updated_at,
                        c.course_code, c.course_name
                 FROM attendance a
                 JOIN courses c ON c.id = a.course_id
                 WHERE a.student_id = ?
                 ORDER BY a.`date` DESC, c.course_name ASC, a.id DESC"
            );
            if (!$stmt) throw new RuntimeException('prepare failed');
            $stmt->bind_param('i', $studentId);
            $stmt->execute();
            $res = $stmt->get_result();

            $sigParts = [];
            $latest = null;
            $valid = ['Present' => 'present', 'Late' => 'late', 'Absent' => 'absent', 'Excused' => 'excused'];

            while ($r = $res->fetch_assoc()) {
                $status = (string) $r['status'];
                if (!isset($valid[$status])) continue;
                $key = $valid[$status];
                $cid = (int) $r['course_id'];

                // A record for a subject the student has since dropped still counts.
                if (!isset($subjects[$cid])) {
                    $subjects[$cid] = [
                        'course_id' => $cid,
                        'code' => (string) $r['course_code'],
                        'name' => (string) $r['course_name'],
                        'present' => 0, 'late' => 0, 'absent' => 0, 'excused' => 0, 'total' => 0, 'rate' => null,
                    ];
                }
                $subjects[$cid][$key]++;
                $subjects[$cid]['total']++;
                $out['summary'][$key]++;
                $out['summary']['total']++;

                $ts = strtotime((string) $r['att_date']);
                $out['records'][] = [
                    'id' => (int) $r['id'],
                    'course_id' => $cid,
                    'code' => (string) $r['course_code'],
                    'name' => (string) $r['course_name'],
                    'date' => $ts ? date('Y-m-d', $ts) : '',
                    'date_label' => $ts ? date('D, M j, Y', $ts) : '—',
                    'month' => $ts ? date('Y-m', $ts) : '',
                    'status' => $status,
                    'remark' => (string) $r['remark'],
                ];

                $sigParts[] = $r['id'] . ':' . $status . ':' . $r['remark'] . ':' . $r['updated_at'];
                if ($latest === null || (string) $r['updated_at'] > $latest) $latest = (string) $r['updated_at'];
            }
            $stmt->close();

            $rateOf = function (array $row): ?int {
                $counted = $row['present'] + $row['late'] + $row['absent'];
                return $counted > 0 ? (int) round(($row['present'] + $row['late']) / $counted * 100) : null;
            };
            foreach ($subjects as &$s) $s['rate'] = $rateOf($s);
            unset($s);
            $out['summary']['rate'] = $rateOf($out['summary']);

            $out['subjects'] = array_values($subjects);
            // Changes whenever a record is added, edited or removed — the page
            // only re-renders when this changes.
            sort($sigParts);
            $out['signature'] = sha1(implode('|', $sigParts));
            $out['last_recorded'] = $latest ? date('M j, Y g:i A', strtotime($latest)) : null;
        } catch (Throwable $e) {
            error_log('[student_attendance] ' . $e->getMessage());
            $out['ok'] = false;
        }

        return $out;
    }
}
