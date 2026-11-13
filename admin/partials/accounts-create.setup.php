<?php
// ============================================================
//  Accounts → Create account tab (setup)
//  Replaces the old create-account.php AND create-admin.php.
//  Student / Teacher need manage_students / manage_teachers;
//  the Admin option only exists for the Head Admin.
// ============================================================
$isHeadAdmin = isHeadAdmin();
requireAdminPermission($conn, ['manage_students', 'manage_teachers']);

$first_name = $_SESSION['first_name'];
$last_name = $_SESSION['last_name'];

$createRoles = [];
if ($isHeadAdmin || adminHasPermission($conn, 'manage_students')) $createRoles['Student'] = 'graduation';
if ($isHeadAdmin || adminHasPermission($conn, 'manage_teachers')) $createRoles['Teacher'] = 'presentation';
// Admission accounts can no longer be created here (existing ones still work and can be edited).
if ($isHeadAdmin) $createRoles['Admin'] = 'shield';

$initialRole = $_GET['role'] ?? '';
if (!isset($createRoles[$initialRole])) $initialRole = array_key_first($createRoles);

$permCatalog = [];
$permTotal = 0;
if ($isHeadAdmin) {
  $permCatalog = fetchPermissionCatalog($conn);
  foreach ($permCatalog as $perms) $permTotal += count($perms);
}
$moduleIcons = [
  'students' => 'graduation', 'teachers' => 'presentation', 'subjects' => 'book',
  'classes' => 'users', 'sections' => 'users', 'modules' => 'folder', 'enrollment' => 'user-check', 'reports' => 'clipboard',
  'announcements' => 'bell', 'admin' => 'shield', 'awards' => 'award', 'banners' => 'image',
];

require_once __DIR__ . '/../../includes/sections.php';
[$shsSections, $hsSections] = loadSectionLists($conn);

$panel = 'create';