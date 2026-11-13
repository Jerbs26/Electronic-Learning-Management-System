<?php
// ============================================================
//  Arandia College eLMS — Admin Panel
//  File: admin/admins.php  |  Head Admin only
//  Tabs: Admins (Regular Admins) · Activity log
//  Replaces: manage-admins.php, activity-log.php
// ============================================================
$ADMIN_PAGE_FILE  = 'admins.php';
$ADMIN_PAGE_TITLE = 'Admins and Logs';
$ADMIN_TABS = [
  'manage' => ['label' => 'Admins',       'partial' => 'admins-manage', 'perms' => 'head'],
  'log'    => ['label' => 'Activity log', 'partial' => 'admins-log',    'perms' => 'head'],
];
require __DIR__ . '/partials/_tabbed_page.php';