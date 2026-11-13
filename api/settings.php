<?php
// ============================================================
//  Arandia College eLMS — System settings API
//  File: api/settings.php
//
//  GET  ?action=get   → current settings (any signed-in user; used by the
//                        live updater in includes/live.php)
//  POST ?action=save  → Head Admin only. JSON body: system_name,
//                        school_year, semester, school_level.
//                        Saves, logs it, and writes a 'settings.changed'
//                        sync event so every open page updates.
// ============================================================
session_start();
require_once '../config/conn.php';
require_once '../config/permissions.php';
require_once '../includes/settings.php';

header('Content-Type: application/json');
header('Cache-Control: no-store');

function settingsOut(int $code, array $body): void
{
    http_response_code($code);
    echo json_encode($body);
    exit;
}

if (empty($_SESSION['user_id'])) {
    settingsOut(401, ['success' => false, 'message' => 'Your session has ended. Please sign in again.', 'auth' => false]);
}
$uid = (int) $_SESSION['user_id'];
$action = $_GET['action'] ?? 'get';
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET' && $action === 'get') {
    session_write_close();
    settingsOut(200, ['success' => true, 'settings' => elmsSettingsPublic(elmsSettings($conn))]);
}

if ($method === 'POST' && $action === 'save') {
    if (!isHeadAdmin()) {
        settingsOut(403, ['success' => false, 'message' => 'Only the Head Admin can change the system settings.']);
    }
    $data = json_decode(file_get_contents('php://input'), true);
    if (!is_array($data)) settingsOut(400, ['success' => false, 'message' => 'Invalid request.']);

    // All four are required on save (the form always sends them)
    $in = [];
    foreach (['system_name', 'school_year', 'semester', 'school_level'] as $k) $in[$k] = $data[$k] ?? '';
    [$clean, $errors] = elmsValidateSettings($in);
    if ($errors) {
        settingsOut(422, ['success' => false, 'message' => reset($errors), 'errors' => $errors]);
    }

    try {
        $before = elmsSettings($conn, true);
        $changed = elmsSaveSettings($conn, $clean, $uid);
    } catch (Throwable $e) {
        error_log('[api/settings] ' . $e->getMessage());
        settingsOut(500, ['success' => false, 'message' => 'Could not save the settings. Please try again.']);
    }
    $now = elmsSettingsPublic(elmsSettings($conn));

    if ($changed) {
        // Tell every open page (all roles) — includes/live.php applies it in place
        try {
            $title = implode(',', $changed);
            $st = $conn->prepare("INSERT INTO sync_events (kind, audience, title) VALUES ('settings.changed', 'all', ?)");
            if ($st) {
                $st->bind_param('s', $title);
                $st->execute();
                $st->close();
            }
        } catch (Throwable $e) {
            error_log('[api/settings] sync event: ' . $e->getMessage());   // live updates off: pages still show it on next load
        }

        $labels = ['system_name' => 'System name', 'school_year' => 'School year', 'semester' => 'Semester', 'school_level' => 'School level'];
        $parts = [];
        foreach ($changed as $k) $parts[] = $labels[$k] . ': "' . $before[$k] . '" → "' . $clean[$k] . '"';
        logAdminActivity($conn, $uid, 'update_settings', 'settings', null, 'Changed system settings. ' . implode('; ', $parts) . '.');
    }

    settingsOut(200, [
        'success'  => true,
        'message'  => $changed ? 'Settings saved. Every open page updates in a few seconds.' : 'No changes to save.',
        'changed'  => $changed,
        'settings' => $now,
    ]);
}

settingsOut(400, ['success' => false, 'message' => 'Unknown action.']);