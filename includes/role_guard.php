<?php
// ============================================================
//  Arandia College eLMS — Role guard for role pages
//  File: includes/role_guard.php
//
//  requireRolePage('Teacher' | 'Student' | 'Admin' | 'Admission') — call at the top of
//  every role page (after config/conn.php). 'Admin' also accepts the Head
//  Admin. requireTeacherPage() / requireStudentPage() are shortcuts. Sends:
//    · nobody logged in              → login page
//    · a Student / Admin / Head Admin → their own dashboard (instead of
//                                        the login page, which would just
//                                        bounce them back)
//    · a teacher whose account was deactivated or deleted while logged
//      in → signed out, then the login page
//  APIs keep their own JSON checks; this is only for pages.
// ============================================================

if (!function_exists('requireTeacherPage')) {

    function roleHome(string $role): string
    {
        switch ($role) {
            case 'Teacher':    return '../teacher/dashboard.php';
            case 'Student':    return '../student/dashboard.php';
            case 'Admission':  return '../admission/dashboard.php';
            case 'Admin':
            case 'Head Admin': return '../admin/dashboard.php';
            default:           return '../login.php';
        }
    }

    function requireRolePage(string $need): void
    {
        global $conn;
        if (session_status() === PHP_SESSION_NONE) session_start();
        $allowed = $need === 'Admin' ? ['Admin', 'Head Admin'] : [$need];

        if (empty($_SESSION['user_id'])) {
            header('Location: ../login.php');
            exit;
        }
        $role = (string) ($_SESSION['role'] ?? '');
        if (!in_array($role, $allowed, true)) {
            header('Location: ' . roleHome($role));
            exit;
        }

        // Account still active, same role? (an admin may have deactivated it or
        // changed its role while this user was logged in)
        if ($conn instanceof mysqli) {
            $uid = (int) $_SESSION['user_id'];
            $st = $conn->prepare("SELECT status, role, section_dept FROM users WHERE id = ? LIMIT 1");
            $st->bind_param('i', $uid);
            $st->execute();
            $u = $st->get_result()->fetch_assoc();
            $st->close();
            if (!$u || $u['status'] !== 'Active' || $u['role'] !== $role) {
                $_SESSION = [];
                session_destroy();
                header('Location: ../login.php?expired=1');
                exit;
            }
        }
    }

    function requireTeacherPage(): void { requireRolePage('Teacher'); }
    function requireStudentPage(): void { requireRolePage('Student'); }

    /**
     * Same check for JSON APIs: answers 401/403 JSON and stops, instead of a redirect.
     * Returns the user id. $roles: allowed roles ('Admin' includes Head Admin).
     */
    function requireApiRole(array $roles): int
    {
        global $conn;
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (in_array('Admin', $roles, true)) $roles[] = 'Head Admin';
        $fail = function (int $code, string $msg) {
            if (!headers_sent()) { http_response_code($code); header('Content-Type: application/json'); }
            echo json_encode(['success' => false, 'message' => $msg, 'auth' => false]);
            exit;
        };
        if (empty($_SESSION['user_id'])) $fail(401, 'Your session has ended. Please sign in again.');
        if (!in_array($_SESSION['role'] ?? '', $roles, true)) $fail(403, 'You are not allowed to do this.');
        if ($conn instanceof mysqli) {
            $uid = (int) $_SESSION['user_id'];
            $st = $conn->prepare("SELECT status, role FROM users WHERE id = ? LIMIT 1");
            $st->bind_param('i', $uid);
            $st->execute();
            $u = $st->get_result()->fetch_assoc();
            $st->close();
            if (!$u || $u['status'] !== 'Active' || $u['role'] !== $_SESSION['role']) $fail(401, 'Your account is no longer active. Please sign in again.');
        }
        return (int) $_SESSION['user_id'];
    }
}
