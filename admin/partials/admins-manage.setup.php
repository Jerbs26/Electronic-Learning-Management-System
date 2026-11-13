<?php
// ============================================================
//  Arandia College eLMS — Admin Panel
//  File: admin/manage-admins.php  |  Head Admin only
//  Create, edit-permission, deactivate, and reset-password for
//  Regular Admin accounts.
// ============================================================
if (session_status() === PHP_SESSION_NONE) session_start();
require_once '../config/conn.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['Admin', 'Head Admin'], true)) {
  header('Location: ../login.php');
  exit;
}
require_once '../config/permissions.php';
$isHeadAdmin = isHeadAdmin();

if (!$isHeadAdmin) {
  header('Location: dashboard.php?toast=' . urlencode('Only the Head Admin can manage admin accounts.') . '&error=1');
  exit;
}

$first_name = $_SESSION['first_name'];
$last_name = $_SESSION['last_name'];

require_once __DIR__ . '/../../includes/sections.php';
[$shsSections, $hsSections] = loadSectionLists($conn);

// ── This page's own data: every Regular Admin account ──────────────────────
$regularAdmins = [];
$res = $conn->query(
  "SELECT id, school_id, username, first_name, last_name, email, status,
          DATE_FORMAT(created_at,'%b %d, %Y') AS created_at
   FROM users WHERE role = 'Admin' ORDER BY last_name, first_name"
);
while ($r = $res->fetch_assoc())
  $regularAdmins[] = $r;

// Permission count per admin, so the table doesn't require opening the modal to know.
$permCounts = [];
$pcRes = $conn->query("SELECT admin_id, COUNT(*) AS cnt FROM admin_permissions WHERE granted = 1 GROUP BY admin_id");
while ($r = $pcRes->fetch_assoc()) $permCounts[(int) $r['admin_id']] = (int) $r['cnt'];

$permCatalog = fetchPermissionCatalog($conn);
$permTotal = 0;
foreach ($permCatalog as $perms) $permTotal += count($perms);
$moduleIcons = [
  'students' => 'graduation', 'teachers' => 'presentation', 'subjects' => 'book',
  'classes' => 'users', 'sections' => 'users', 'modules' => 'folder', 'enrollment' => 'user-check', 'reports' => 'clipboard',
  'announcements' => 'bell', 'admin' => 'shield', 'awards' => 'award',
];

$panel = 'manage-admins';