<?php
// ============================================================
//  Arandia College eLMS — Presence heartbeat
//  File: api/heartbeat.php
//  Pinged every 2 minutes by open pages so a user who is only reading
//  (no clicks) still shows as online. config/conn.php does the update.
// ============================================================
session_start();
require_once '../config/conn.php';
header('Content-Type: application/json');
$__ok = !empty($_SESSION['user_id']);
session_write_close();   // read-only: release the session file at once (Windows locks it)
echo json_encode(['ok' => $__ok]);
