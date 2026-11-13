<?php
// ============================================================
//  Arandia College eLMS — Admin Panel
//  File: admin/announcements.php
//  Tabs: Announcements · Dashboard banners
//  Replaces: announcements.php (old), banners.php
// ============================================================
$ADMIN_PAGE_FILE  = 'announcements.php';
$ADMIN_PAGE_TITLE = 'Announcements';
$ADMIN_TABS = [
  'posts'   => ['label' => 'Announcements',     'partial' => 'announce-posts',   'perms' => null],
  'banners' => ['label' => 'Dashboard banners', 'partial' => 'announce-banners', 'perms' => ['manage_banners']],
];
require __DIR__ . '/partials/_tabbed_page.php';
