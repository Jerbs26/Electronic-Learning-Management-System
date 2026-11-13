<?php
// ============================================================
//  Arandia College eLMS — Admin Panel
//  File: admin/messages.php
//  Messages sent from the public Contact Us page (contact.php)
// ============================================================
$ADMIN_PAGE_FILE  = 'messages.php';
$ADMIN_PAGE_TITLE = 'Messages';
$ADMIN_TABS = [
  'inbox' => ['label' => 'Contact Us inbox', 'partial' => 'messages-inbox', 'perms' => null],
];
require __DIR__ . '/partials/_tabbed_page.php';