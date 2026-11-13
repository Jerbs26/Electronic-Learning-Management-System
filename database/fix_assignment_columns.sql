-- ============================================================
--  Arandia College eLMS — Make sure the assignment columns exist
--  File: database/fix_assignment_columns.sql
--
--  The Assignments page and the speed grader use these columns.
--  Older copies of the database may not have all of them.
--  Safe to run any number of times: a column that already exists
--  is skipped (IF NOT EXISTS, supported by XAMPP's MariaDB).
-- ============================================================

ALTER TABLE `assignments`
  ADD COLUMN IF NOT EXISTS `instructions` text DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `description`  text DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `file_path`    varchar(300) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `due_date`     datetime DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `max_score`    decimal(6,2) NOT NULL DEFAULT 100.00,
  ADD COLUMN IF NOT EXISTS `max_attempts` int(10) UNSIGNED DEFAULT NULL COMMENT 'Max submissions allowed per student; NULL = unlimited',
  ADD COLUMN IF NOT EXISTS `created_at`   datetime NOT NULL DEFAULT current_timestamp();

ALTER TABLE `submissions`
  ADD COLUMN IF NOT EXISTS `file_path`     varchar(300) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `remarks`       text DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `score`         decimal(6,2) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `submitted_at`  datetime NOT NULL DEFAULT current_timestamp(),
  ADD COLUMN IF NOT EXISTS `graded_at`     datetime DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `status`        enum('Submitted','Graded','Late') NOT NULL DEFAULT 'Submitted',
  ADD COLUMN IF NOT EXISTS `attempt_count` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'How many times this student has submitted this assignment';

-- Rows that already have a file count as one real submission
UPDATE `submissions` SET `attempt_count` = 1 WHERE `attempt_count` = 0 AND `file_path` IS NOT NULL;
