<?php
// ============================================================
//  Arandia College eLMS — Grades API (classic view) — retired
//  File: api/grades.php
//  The classic grades page (teacher/grades-classic.php) is gone. Its
//  `save` action wrote to the old `grades` table that the Gradebook and
//  the student Grades page don't read, and `list` / `export` showed
//  students from every section of a subject. Use:
//    api/gradebook.php     list / export / manual items and scores
//    api/assignments.php   grade_one (assignment scores)
//    api/quizzes.php       grade (file-quiz scores)
//  Existing rows in `grades` are kept untouched.
// ============================================================
session_start();
http_response_code(410);
header('Content-Type: application/json');
echo json_encode([
    'success' => false,
    'message' => 'The classic grades view was replaced by the Gradebook. Open Grades from the sidebar.',
]);
