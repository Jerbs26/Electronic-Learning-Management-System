<?php
// ============================================================
//  Arandia College eLMS — Teacher: Quizzes (moved)
//  File: teacher/quizzes.php
//  Quizzes are now on the Assignments page together with the
//  assignments. Old links (notifications, bookmarks, dashboard
//  shortcuts like ?action=new-quiz / ?action=ai-quiz) still work:
//  they are forwarded there with the same ?course / ?section / ?action.
// ============================================================
require __DIR__ . '/_init.php';

$keep = array_intersect_key($_GET, array_flip(['course', 'section', 'class', 'action', 'module']));
$keep = array_filter($keep, fn($v) => is_string($v) && $v !== '');
header('Location: assignments.php' . ($keep ? '?' . http_build_query($keep) : '') . '#quizzes', true, 302);
exit;
