-- ============================================================
--  Arandia College eLMS — Gradebook
--  File: database/migration_gradebook.sql
--
--  BACK UP elmsdb FIRST. Run once (safe to re-run: tables use
--  IF NOT EXISTS and triggers are dropped and re-created).
--
--  ASSIGNMENT / QUIZ ──(trigger)──► GRADEBOOK ITEM
--  Student submits ► teacher grades ► submission/attempt score
--  ► the Gradebook and the student's Grades read that same score.
--
--   • gradebook_items  — one row per assignment, quiz, or manual item
--                        (e.g. "Midterm"). Created, renamed, re-scored
--                        and deleted automatically by the triggers below.
--   • gradebook_scores — scores for manual items only. Assignment and
--                        quiz scores stay on the submission / quiz attempt,
--                        so there is only ever one copy of each score.
-- ============================================================

CREATE TABLE IF NOT EXISTS `gradebook_items` (
  `id`          int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `course_id`   int(10) UNSIGNED NOT NULL,
  `source_type` enum('assignment','quiz','manual') NOT NULL,
  `source_id`   int(10) UNSIGNED DEFAULT NULL COMMENT 'assignments.id or quizzes.id; NULL for manual items',
  `title`       varchar(255) NOT NULL,
  `max_score`   decimal(7,2) NOT NULL DEFAULT 100.00,
  `created_at`  datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_gb_source` (`source_type`, `source_id`),
  KEY `idx_gb_course` (`course_id`),
  CONSTRAINT `fk_gb_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `gradebook_scores` (
  `item_id`    int(10) UNSIGNED NOT NULL,
  `student_id` int(10) UNSIGNED NOT NULL,
  `score`      decimal(7,2) DEFAULT NULL,
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`item_id`, `student_id`),
  CONSTRAINT `fk_gbs_item`    FOREIGN KEY (`item_id`)    REFERENCES `gradebook_items` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_gbs_student` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Existing assignments and quizzes become gradebook items
INSERT IGNORE INTO `gradebook_items` (course_id, source_type, source_id, title, max_score, created_at)
SELECT course_id, 'assignment', id, title, COALESCE(max_score, 100), created_at FROM `assignments`;
INSERT IGNORE INTO `gradebook_items` (course_id, source_type, source_id, title, max_score, created_at)
SELECT course_id, 'quiz', id, title, COALESCE(max_score, 100), created_at FROM `quizzes`;

-- ── Keep items in sync automatically ───────────────────────
DROP TRIGGER IF EXISTS `trg_gb_assignment_insert`;
CREATE TRIGGER `trg_gb_assignment_insert` AFTER INSERT ON `assignments` FOR EACH ROW
  INSERT IGNORE INTO `gradebook_items` (course_id, source_type, source_id, title, max_score, created_at)
  VALUES (NEW.course_id, 'assignment', NEW.id, NEW.title, COALESCE(NEW.max_score, 100), COALESCE(NEW.created_at, NOW()));

DROP TRIGGER IF EXISTS `trg_gb_assignment_update`;
CREATE TRIGGER `trg_gb_assignment_update` AFTER UPDATE ON `assignments` FOR EACH ROW
  UPDATE `gradebook_items` SET course_id = NEW.course_id, title = NEW.title, max_score = COALESCE(NEW.max_score, 100)
  WHERE source_type = 'assignment' AND source_id = NEW.id;

DROP TRIGGER IF EXISTS `trg_gb_assignment_delete`;
CREATE TRIGGER `trg_gb_assignment_delete` AFTER DELETE ON `assignments` FOR EACH ROW
  DELETE FROM `gradebook_items` WHERE source_type = 'assignment' AND source_id = OLD.id;

DROP TRIGGER IF EXISTS `trg_gb_quiz_insert`;
CREATE TRIGGER `trg_gb_quiz_insert` AFTER INSERT ON `quizzes` FOR EACH ROW
  INSERT IGNORE INTO `gradebook_items` (course_id, source_type, source_id, title, max_score, created_at)
  VALUES (NEW.course_id, 'quiz', NEW.id, NEW.title, COALESCE(NEW.max_score, 100), COALESCE(NEW.created_at, NOW()));

DROP TRIGGER IF EXISTS `trg_gb_quiz_update`;
CREATE TRIGGER `trg_gb_quiz_update` AFTER UPDATE ON `quizzes` FOR EACH ROW
  UPDATE `gradebook_items` SET course_id = NEW.course_id, title = NEW.title, max_score = COALESCE(NEW.max_score, 100)
  WHERE source_type = 'quiz' AND source_id = NEW.id;

DROP TRIGGER IF EXISTS `trg_gb_quiz_delete`;
CREATE TRIGGER `trg_gb_quiz_delete` AFTER DELETE ON `quizzes` FOR EACH ROW
  DELETE FROM `gradebook_items` WHERE source_type = 'quiz' AND source_id = OLD.id;
