<?php
// ============================================================
//  Arandia College eLMS — Admin Panel
//  File: admin.php  |  Target: SHS & HS
// ============================================================
if (session_status() === PHP_SESSION_NONE) session_start();
require_once '../config/conn.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['Admin', 'Head Admin'], true)) {
  header('Location: ../login.php');
  exit;
}
require_once '../config/permissions.php';
requireAdminPermission($conn, ['view_students', 'view_teachers', 'manage_admin_accounts']);
$isHeadAdmin = isHeadAdmin();

$first_name = $_SESSION['first_name'];
$last_name = $_SESSION['last_name'];

$allAccounts = [];
$res = $conn->query(
  "SELECT id, school_id, username, role, first_name, last_name, middle_name,
     email, contact, section_dept, status,
     DATE_FORMAT(created_at,'%b %d, %Y') AS created_at
   FROM users ORDER BY id DESC"
);
while ($r = $res->fetch_assoc())
  $allAccounts[] = $r;

$toast = $_GET['toast'] ?? '';
$isError = $_GET['error'] ?? '';

require_once __DIR__ . '/../../includes/sections.php';
[$shsSections, $hsSections] = loadSectionLists($conn);
$panel = 'accounts';
