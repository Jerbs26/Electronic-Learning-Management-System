<?php
// ============================================================
//  Arandia College eLMS — Admin Panel
//  File: admin/messages.php?tab=inbox  |  Any admin
//  Messages from the public Contact Us page
// ============================================================
if (session_status() === PHP_SESSION_NONE) session_start();
require_once '../config/conn.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['Admin', 'Head Admin'], true)) {
  header('Location: ../login.php');
  exit;
}
require_once '../config/permissions.php';
require_once __DIR__ . '/../../includes/contact_messages.php';

$first_name = $_SESSION['first_name'];
$last_name = $_SESSION['last_name'];
	
require_once __DIR__ . '/../../includes/sections.php';
[$shsSections, $hsSections] = loadSectionLists($conn);

$contactMessages = contactMessagesList($conn);
$contactNewCount = count(array_filter($contactMessages, fn($m) => $m['status'] === 'New'));

$panel = 'messages';