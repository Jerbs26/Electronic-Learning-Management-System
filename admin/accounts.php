<?php
// ============================================================
//  Arandia College eLMS — Admin Panel
//  File: admin/accounts.php
//  Tabs: All accounts · Create account (Student / Teacher / Admin)
//  Replaces: all-accounts.php, create-account.php, create-admin.php
// ============================================================
$ADMIN_PAGE_FILE  = 'accounts.php';
$ADMIN_PAGE_TITLE = 'Accounts';
$ADMIN_TABS = [
  'all'    => ['label' => 'All accounts',   'partial' => 'accounts-all',    'perms' => ['view_students', 'view_teachers', 'manage_admin_accounts']],
  'create' => ['label' => 'Create account', 'partial' => 'accounts-create', 'perms' => ['manage_students', 'manage_teachers']],
];
require __DIR__ . '/partials/_tabbed_page.php';
