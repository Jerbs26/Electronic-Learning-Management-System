<?php
// ============================================================
//  Arandia College eLMS — Admin Panel
//  File: admin/banners.php
//  Manage the sliding picture banner on the Student dashboard.
// ============================================================
if (session_status() === PHP_SESSION_NONE) session_start();
require_once '../config/conn.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['Admin', 'Head Admin'], true)) {
  header('Location: ../login.php');
  exit;
}
require_once '../config/permissions.php';
requireAdminPermission($conn, 'manage_banners');
$isHeadAdmin = isHeadAdmin();

$first_name = $_SESSION['first_name'];
$last_name = $_SESSION['last_name'];

require_once __DIR__ . '/../../includes/sections.php';
[$shsSections, $hsSections] = loadSectionLists($conn);

// ── This page's own data: every banner slide ────────────────────────────
$banners = [];
$res = $conn->query("SELECT id, image_path, title, caption, link_url, sort_order, status FROM dashboard_banners ORDER BY sort_order ASC, id ASC");
if ($res) { while ($r = $res->fetch_assoc()) $banners[] = $r; }

$panel = 'banners';
