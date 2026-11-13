<?php
// ============================================================
//  Arandia College eLMS — Student: Quizzes (moved)
//  File: student/quizzes.php
//  Quizzes are now on the Assignments page together with the
//  assignments. Old links (notifications, search, bookmarks) still
//  work: they are forwarded there with the same ?course (and ?item,
//  which then points at the quiz).
// ============================================================
require __DIR__ . '/_init.php';

$keep = [];
$course = filter_var($_GET['course'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$item = filter_var($_GET['item'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if ($course) $keep['course'] = $course;
if ($item) { $keep['item'] = $item; $keep['kind'] = 'quiz'; }
header('Location: assignments.php' . ($keep ? '?' . http_build_query($keep) : '') . '#quizzes', true, 302);
exit;
