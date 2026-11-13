<?php
require_once __DIR__ . '/includes/session_safe.php';
elmsSessionStart();
// Show the user as offline right away instead of waiting 5 minutes
if (!empty($_SESSION['user_id'])) {
    require_once 'config/conn.php';
    // Free the account for the next sign-in (includes/single_session.php)
    require_once __DIR__ . '/includes/single_session.php';
    ssReleaseDevice($conn);
    try {
        $st = $conn->prepare('UPDATE users SET last_seen_at = NULL WHERE id = ?');
        $uid = (int) $_SESSION['user_id'];
        $st->bind_param('i', $uid);
        $st->execute();
    } catch (Throwable $e) {
    }
}
elmsSessionEnd();   // also drops the cookie, so login.php never reopens the old session file
header('Location: index.php');
exit;