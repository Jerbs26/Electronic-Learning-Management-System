<?php
// ============================================================
//  Arandia College eLMS — Teacher: EduBot AI
//  File: teacher/edubot.php
// ============================================================
require __DIR__ . '/_init.php';
$currentPage = 'edubot.php';
$pageTitle = 'EduBot AI';

require __DIR__ . '/../includes/header.php';
?>

<?php
// This page IS the full EduBot chat, so no floating button here.
$chatbotShowFab = false;
include '../includes/chatbot.php';
?>

<?php require __DIR__ . '/../includes/footer.php'; ?>
