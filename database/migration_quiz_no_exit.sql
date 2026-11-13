-- ============================================================
--  Arandia College eLMS — Online quizzes: no exit, no resume
--  File: database/migration_quiz_no_exit.sql
--
--  Adds quiz_attempts.last_active_at. While a student takes an online
--  quiz, every answer is saved and the attempt is marked active. If the
--  student leaves (closes the window, refreshes, loses the connection)
--  the attempt is submitted with the answers given — only correct answers
--  score. See includes/quiz_attempts.php.
--
--  Optional: the system also adds this column by itself the first time
--  it is needed. Safe to run more than once.
-- ============================================================
ALTER TABLE `quiz_attempts`
  ADD COLUMN IF NOT EXISTS `last_active_at` DATETIME NULL DEFAULT NULL AFTER `started_at`;
