<?php
// ============================================================
//  Arandia College eLMS — Admin Panel
//  File: admin/activity-log.php  |  Head Admin only
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
  header('Location: dashboard.php?toast=' . urlencode('Only the Head Admin can view the activity log.') . '&error=1');
  exit;
}

$first_name = $_SESSION['first_name'];
$last_name = $_SESSION['last_name'];

require_once __DIR__ . '/../../includes/sections.php';
[$shsSections, $hsSections] = loadSectionLists($conn);

// ── This page's own data: the audit trail ───────────────────────────────
$logEntries = [];
$res = $conn->query(
  "SELECT l.id, l.action, l.target_type, l.target_id, l.details,
          DATE_FORMAT(l.created_at,'%b %d, %Y %h:%i %p') AS created_at,
          COALESCE(CONCAT(u.first_name,' ',u.last_name), 'Deleted account') AS admin_name, COALESCE(u.role, '') AS admin_role
   FROM admin_activity_log l
   LEFT JOIN users u ON u.id = l.admin_id   -- kept after the admin's account is deleted (admin_id is then empty)
   ORDER BY l.created_at DESC
   LIMIT 200"
);
while ($r = $res->fetch_assoc()) $logEntries[] = $r;

function actionBadgeClass(string $action): string
{
  if (str_contains($action, 'permission')) return 'action-perm';
  if (str_contains($action, 'publish')) return 'action-publish';
  if (str_starts_with($action, 'delete') || str_starts_with($action, 'remove')) return 'action-delete';
  if (str_starts_with($action, 'create') || str_starts_with($action, 'assign') || str_starts_with($action, 'enroll')) return 'action-create';
  return 'action-update';
}
$actionCategoryLabels = [
  'action-create'  => 'Create',
  'action-update'  => 'Update',
  'action-delete'  => 'Delete',
  'action-perm'    => 'Permissions',
  'action-publish' => 'Publish',
];

$panel = 'activity-log';
