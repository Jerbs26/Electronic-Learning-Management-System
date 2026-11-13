<?php
// ============================================================
//  Arandia College eLMS — Teacher: Grades (classic view) — retired
//  File: teacher/grades-classic.php
//  The classic view wrote scores to the old `grades` table, which the
//  Gradebook and the student Grades page no longer read, so scores typed
//  here never reached students. Its one unique feature, AI insights for
//  students below the passing average, now lives on teacher/grades.php
//  (reading the same totals as the Gradebook). Old links land there.
// ============================================================
session_start();
if (($_SESSION['role'] ?? '') !== 'Teacher') {
    header('Location: ../login.php');
    exit;
}
header('Location: grades.php', true, 301);
exit;
