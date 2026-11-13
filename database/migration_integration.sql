-- ============================================================
--  Arandia College eLMS — Admin / Teacher / Student integration
--  File: database/migration_integration.sql
--
--  Run AFTER: migration_sections_subjects, migration_modules,
--  migration_gradebook, migration_presence, migration_awards_keys.
--  Back up elmsdb first. Safe to run more than once.
--
--  What it does (nothing is deleted; no existing value is changed except
--  filling the new owner columns):
--   1. Ownership: assignments, quizzes and gradebook items remember which
--      teacher made them (teacher_id). Two teachers can share a subject in
--      different sections; each teacher's work now only reaches the
--      students in their own sections. Existing rows are filled in when
--      exactly one teacher handles the subject; otherwise they stay NULL
--      and keep showing to every section (old behaviour).
--   2. Foreign keys that were missing (submissions, quizzes, quiz attempts,
--      questions, choices, responses, awards, module progress, password
--      resets). Each one is only added when no orphan rows exist; the
--      final SELECT lists any that were skipped.
--   3. sync_events: a small change feed written by triggers. The app reads
--      it (api/sync.php) to update Admin, Teacher and Student pages live,
--      filtered by the same rules as the pages themselves.
--   4. Triggers: the gradebook triggers are recreated (same names) so they
--      also copy teacher_id and write sync events. If you ever re-import
--      migration_gradebook.sql, import this file again afterwards.
-- ============================================================

DROP TEMPORARY TABLE IF EXISTS _mig_report;
CREATE TEMPORARY TABLE _mig_report (item VARCHAR(120), result VARCHAR(120));

-- ── 1. Ownership columns ────────────────────────────────────

SET @has := (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'assignments' AND column_name = 'teacher_id');
SET @sql := IF(@has = 0, 'ALTER TABLE `assignments` ADD COLUMN `teacher_id` int(10) UNSIGNED DEFAULT NULL COMMENT ''Teacher who created it; NULL = shared by every section'' AFTER `course_id`', 'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @has := (SELECT COUNT(*) FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = 'assignments' AND index_name = 'idx_assignments_teacher');
SET @sql := IF(@has = 0, 'ALTER TABLE `assignments` ADD KEY `idx_assignments_teacher` (`teacher_id`)', 'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @has := (SELECT COUNT(*) FROM information_schema.table_constraints WHERE table_schema = DATABASE() AND table_name = 'assignments' AND constraint_name = 'fk_assignments_teacher');
SET @orph := (SELECT COUNT(*) FROM `assignments` t LEFT JOIN `users` r ON r.`id` = t.`teacher_id` WHERE t.`teacher_id` IS NOT NULL AND r.`id` IS NULL);
SET @sql := IF(@has = 0 AND @orph = 0, 'ALTER TABLE `assignments` ADD CONSTRAINT `fk_assignments_teacher` FOREIGN KEY (`teacher_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE', 'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;
INSERT INTO _mig_report SELECT 'assignments.teacher_id -> users', IF(@has > 0, 'already there', IF(@orph = 0, 'added', CONCAT('SKIPPED: ', @orph, ' orphan row(s)')));

SET @has := (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'quizzes' AND column_name = 'teacher_id');
SET @sql := IF(@has = 0, 'ALTER TABLE `quizzes` ADD COLUMN `teacher_id` int(10) UNSIGNED DEFAULT NULL COMMENT ''Teacher who created it; NULL = shared by every section'' AFTER `course_id`', 'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @has := (SELECT COUNT(*) FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = 'quizzes' AND index_name = 'idx_quizzes_teacher');
SET @sql := IF(@has = 0, 'ALTER TABLE `quizzes` ADD KEY `idx_quizzes_teacher` (`teacher_id`)', 'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @has := (SELECT COUNT(*) FROM information_schema.table_constraints WHERE table_schema = DATABASE() AND table_name = 'quizzes' AND constraint_name = 'fk_quizzes_teacher');
SET @orph := (SELECT COUNT(*) FROM `quizzes` t LEFT JOIN `users` r ON r.`id` = t.`teacher_id` WHERE t.`teacher_id` IS NOT NULL AND r.`id` IS NULL);
SET @sql := IF(@has = 0 AND @orph = 0, 'ALTER TABLE `quizzes` ADD CONSTRAINT `fk_quizzes_teacher` FOREIGN KEY (`teacher_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE', 'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;
INSERT INTO _mig_report SELECT 'quizzes.teacher_id -> users', IF(@has > 0, 'already there', IF(@orph = 0, 'added', CONCAT('SKIPPED: ', @orph, ' orphan row(s)')));

SET @has := (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'gradebook_items' AND column_name = 'teacher_id');
SET @sql := IF(@has = 0, 'ALTER TABLE `gradebook_items` ADD COLUMN `teacher_id` int(10) UNSIGNED DEFAULT NULL COMMENT ''Teacher who created it; NULL = shared by every section'' AFTER `course_id`', 'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @has := (SELECT COUNT(*) FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = 'gradebook_items' AND index_name = 'idx_gradebook_items_teacher');
SET @sql := IF(@has = 0, 'ALTER TABLE `gradebook_items` ADD KEY `idx_gradebook_items_teacher` (`teacher_id`)', 'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @has := (SELECT COUNT(*) FROM information_schema.table_constraints WHERE table_schema = DATABASE() AND table_name = 'gradebook_items' AND constraint_name = 'fk_gradebook_items_teacher');
SET @orph := (SELECT COUNT(*) FROM `gradebook_items` t LEFT JOIN `users` r ON r.`id` = t.`teacher_id` WHERE t.`teacher_id` IS NOT NULL AND r.`id` IS NULL);
SET @sql := IF(@has = 0 AND @orph = 0, 'ALTER TABLE `gradebook_items` ADD CONSTRAINT `fk_gradebook_items_teacher` FOREIGN KEY (`teacher_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE', 'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;
INSERT INTO _mig_report SELECT 'gradebook_items.teacher_id -> users', IF(@has > 0, 'already there', IF(@orph = 0, 'added', CONCAT('SKIPPED: ', @orph, ' orphan row(s)')));

-- Fill owners where exactly one teacher handles the subject
UPDATE assignments a
JOIN (SELECT course_id, MIN(teacher_id) AS tid FROM teacher_assignments WHERE teacher_id IS NOT NULL
      GROUP BY course_id HAVING COUNT(DISTINCT teacher_id) = 1) one ON one.course_id = a.course_id
SET a.teacher_id = one.tid WHERE a.teacher_id IS NULL;
UPDATE quizzes q
JOIN (SELECT course_id, MIN(teacher_id) AS tid FROM teacher_assignments WHERE teacher_id IS NOT NULL
      GROUP BY course_id HAVING COUNT(DISTINCT teacher_id) = 1) one ON one.course_id = q.course_id
SET q.teacher_id = one.tid WHERE q.teacher_id IS NULL;
UPDATE gradebook_items gi JOIN assignments a ON gi.source_type = 'assignment' AND a.id = gi.source_id
SET gi.teacher_id = a.teacher_id WHERE gi.teacher_id IS NULL;
UPDATE gradebook_items gi JOIN quizzes q ON gi.source_type = 'quiz' AND q.id = gi.source_id
SET gi.teacher_id = q.teacher_id WHERE gi.teacher_id IS NULL;
UPDATE gradebook_items gi
JOIN (SELECT course_id, MIN(teacher_id) AS tid FROM teacher_assignments WHERE teacher_id IS NOT NULL
      GROUP BY course_id HAVING COUNT(DISTINCT teacher_id) = 1) one ON one.course_id = gi.course_id
SET gi.teacher_id = one.tid WHERE gi.teacher_id IS NULL AND gi.source_type = 'manual';


-- ── 2. Missing foreign keys ─────────────────────────────────

SET @has := (SELECT COUNT(*) FROM information_schema.table_constraints WHERE table_schema = DATABASE() AND table_name = 'submissions' AND constraint_name = 'fk_sub_assignment');
SET @orph := (SELECT COUNT(*) FROM `submissions` t LEFT JOIN `assignments` r ON r.`id` = t.`assignment_id` WHERE t.`assignment_id` IS NOT NULL AND r.`id` IS NULL);
SET @sql := IF(@has = 0 AND @orph = 0, 'ALTER TABLE `submissions` ADD CONSTRAINT `fk_sub_assignment` FOREIGN KEY (`assignment_id`) REFERENCES `assignments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE', 'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;
INSERT INTO _mig_report SELECT 'submissions.assignment_id -> assignments', IF(@has > 0, 'already there', IF(@orph = 0, 'added', CONCAT('SKIPPED: ', @orph, ' orphan row(s)')));

SET @has := (SELECT COUNT(*) FROM information_schema.table_constraints WHERE table_schema = DATABASE() AND table_name = 'submissions' AND constraint_name = 'fk_sub_student');
SET @orph := (SELECT COUNT(*) FROM `submissions` t LEFT JOIN `users` r ON r.`id` = t.`student_id` WHERE t.`student_id` IS NOT NULL AND r.`id` IS NULL);
SET @sql := IF(@has = 0 AND @orph = 0, 'ALTER TABLE `submissions` ADD CONSTRAINT `fk_sub_student` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE', 'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;
INSERT INTO _mig_report SELECT 'submissions.student_id -> users', IF(@has > 0, 'already there', IF(@orph = 0, 'added', CONCAT('SKIPPED: ', @orph, ' orphan row(s)')));

SET @has := (SELECT COUNT(*) FROM information_schema.table_constraints WHERE table_schema = DATABASE() AND table_name = 'quizzes' AND constraint_name = 'fk_quiz_course');
SET @orph := (SELECT COUNT(*) FROM `quizzes` t LEFT JOIN `courses` r ON r.`id` = t.`course_id` WHERE t.`course_id` IS NOT NULL AND r.`id` IS NULL);
SET @sql := IF(@has = 0 AND @orph = 0, 'ALTER TABLE `quizzes` ADD CONSTRAINT `fk_quiz_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE', 'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;
INSERT INTO _mig_report SELECT 'quizzes.course_id -> courses', IF(@has > 0, 'already there', IF(@orph = 0, 'added', CONCAT('SKIPPED: ', @orph, ' orphan row(s)')));

SET @has := (SELECT COUNT(*) FROM information_schema.table_constraints WHERE table_schema = DATABASE() AND table_name = 'quiz_attempts' AND constraint_name = 'fk_qa_quiz');
SET @orph := (SELECT COUNT(*) FROM `quiz_attempts` t LEFT JOIN `quizzes` r ON r.`id` = t.`quiz_id` WHERE t.`quiz_id` IS NOT NULL AND r.`id` IS NULL);
SET @sql := IF(@has = 0 AND @orph = 0, 'ALTER TABLE `quiz_attempts` ADD CONSTRAINT `fk_qa_quiz` FOREIGN KEY (`quiz_id`) REFERENCES `quizzes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE', 'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;
INSERT INTO _mig_report SELECT 'quiz_attempts.quiz_id -> quizzes', IF(@has > 0, 'already there', IF(@orph = 0, 'added', CONCAT('SKIPPED: ', @orph, ' orphan row(s)')));

SET @has := (SELECT COUNT(*) FROM information_schema.table_constraints WHERE table_schema = DATABASE() AND table_name = 'quiz_attempts' AND constraint_name = 'fk_qa_student');
SET @orph := (SELECT COUNT(*) FROM `quiz_attempts` t LEFT JOIN `users` r ON r.`id` = t.`student_id` WHERE t.`student_id` IS NOT NULL AND r.`id` IS NULL);
SET @sql := IF(@has = 0 AND @orph = 0, 'ALTER TABLE `quiz_attempts` ADD CONSTRAINT `fk_qa_student` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE', 'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;
INSERT INTO _mig_report SELECT 'quiz_attempts.student_id -> users', IF(@has > 0, 'already there', IF(@orph = 0, 'added', CONCAT('SKIPPED: ', @orph, ' orphan row(s)')));

SET @has := (SELECT COUNT(*) FROM information_schema.table_constraints WHERE table_schema = DATABASE() AND table_name = 'quiz_questions' AND constraint_name = 'fk_qq_quiz');
SET @orph := (SELECT COUNT(*) FROM `quiz_questions` t LEFT JOIN `quizzes` r ON r.`id` = t.`quiz_id` WHERE t.`quiz_id` IS NOT NULL AND r.`id` IS NULL);
SET @sql := IF(@has = 0 AND @orph = 0, 'ALTER TABLE `quiz_questions` ADD CONSTRAINT `fk_qq_quiz` FOREIGN KEY (`quiz_id`) REFERENCES `quizzes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE', 'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;
INSERT INTO _mig_report SELECT 'quiz_questions.quiz_id -> quizzes', IF(@has > 0, 'already there', IF(@orph = 0, 'added', CONCAT('SKIPPED: ', @orph, ' orphan row(s)')));

SET @has := (SELECT COUNT(*) FROM information_schema.table_constraints WHERE table_schema = DATABASE() AND table_name = 'quiz_choices' AND constraint_name = 'fk_qc_question');
SET @orph := (SELECT COUNT(*) FROM `quiz_choices` t LEFT JOIN `quiz_questions` r ON r.`id` = t.`question_id` WHERE t.`question_id` IS NOT NULL AND r.`id` IS NULL);
SET @sql := IF(@has = 0 AND @orph = 0, 'ALTER TABLE `quiz_choices` ADD CONSTRAINT `fk_qc_question` FOREIGN KEY (`question_id`) REFERENCES `quiz_questions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE', 'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;
INSERT INTO _mig_report SELECT 'quiz_choices.question_id -> quiz_questions', IF(@has > 0, 'already there', IF(@orph = 0, 'added', CONCAT('SKIPPED: ', @orph, ' orphan row(s)')));

SET @has := (SELECT COUNT(*) FROM information_schema.table_constraints WHERE table_schema = DATABASE() AND table_name = 'quiz_responses' AND constraint_name = 'fk_qr_attempt');
SET @orph := (SELECT COUNT(*) FROM `quiz_responses` t LEFT JOIN `quiz_attempts` r ON r.`id` = t.`attempt_id` WHERE t.`attempt_id` IS NOT NULL AND r.`id` IS NULL);
SET @sql := IF(@has = 0 AND @orph = 0, 'ALTER TABLE `quiz_responses` ADD CONSTRAINT `fk_qr_attempt` FOREIGN KEY (`attempt_id`) REFERENCES `quiz_attempts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE', 'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;
INSERT INTO _mig_report SELECT 'quiz_responses.attempt_id -> quiz_attempts', IF(@has > 0, 'already there', IF(@orph = 0, 'added', CONCAT('SKIPPED: ', @orph, ' orphan row(s)')));

SET @has := (SELECT COUNT(*) FROM information_schema.table_constraints WHERE table_schema = DATABASE() AND table_name = 'quiz_responses' AND constraint_name = 'fk_qr_question');
SET @orph := (SELECT COUNT(*) FROM `quiz_responses` t LEFT JOIN `quiz_questions` r ON r.`id` = t.`question_id` WHERE t.`question_id` IS NOT NULL AND r.`id` IS NULL);
SET @sql := IF(@has = 0 AND @orph = 0, 'ALTER TABLE `quiz_responses` ADD CONSTRAINT `fk_qr_question` FOREIGN KEY (`question_id`) REFERENCES `quiz_questions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE', 'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;
INSERT INTO _mig_report SELECT 'quiz_responses.question_id -> quiz_questions', IF(@has > 0, 'already there', IF(@orph = 0, 'added', CONCAT('SKIPPED: ', @orph, ' orphan row(s)')));

SET @has := (SELECT COUNT(*) FROM information_schema.table_constraints WHERE table_schema = DATABASE() AND table_name = 'quiz_responses' AND constraint_name = 'fk_qr_choice');
SET @orph := (SELECT COUNT(*) FROM `quiz_responses` t LEFT JOIN `quiz_choices` r ON r.`id` = t.`choice_id` WHERE t.`choice_id` IS NOT NULL AND r.`id` IS NULL);
SET @sql := IF(@has = 0 AND @orph = 0, 'ALTER TABLE `quiz_responses` ADD CONSTRAINT `fk_qr_choice` FOREIGN KEY (`choice_id`) REFERENCES `quiz_choices` (`id`) ON DELETE SET NULL ON UPDATE CASCADE', 'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;
INSERT INTO _mig_report SELECT 'quiz_responses.choice_id -> quiz_choices', IF(@has > 0, 'already there', IF(@orph = 0, 'added', CONCAT('SKIPPED: ', @orph, ' orphan row(s)')));

SET @has := (SELECT COUNT(*) FROM information_schema.table_constraints WHERE table_schema = DATABASE() AND table_name = 'student_awards' AND constraint_name = 'fk_sa_student');
SET @orph := (SELECT COUNT(*) FROM `student_awards` t LEFT JOIN `users` r ON r.`id` = t.`student_id` WHERE t.`student_id` IS NOT NULL AND r.`id` IS NULL);
SET @sql := IF(@has = 0 AND @orph = 0, 'ALTER TABLE `student_awards` ADD CONSTRAINT `fk_sa_student` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE', 'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;
INSERT INTO _mig_report SELECT 'student_awards.student_id -> users', IF(@has > 0, 'already there', IF(@orph = 0, 'added', CONCAT('SKIPPED: ', @orph, ' orphan row(s)')));

SET @has := (SELECT COUNT(*) FROM information_schema.table_constraints WHERE table_schema = DATABASE() AND table_name = 'student_awards' AND constraint_name = 'fk_sa_course');
SET @orph := (SELECT COUNT(*) FROM `student_awards` t LEFT JOIN `courses` r ON r.`id` = t.`course_id` WHERE t.`course_id` IS NOT NULL AND r.`id` IS NULL);
SET @sql := IF(@has = 0 AND @orph = 0, 'ALTER TABLE `student_awards` ADD CONSTRAINT `fk_sa_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE', 'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;
INSERT INTO _mig_report SELECT 'student_awards.course_id -> courses', IF(@has > 0, 'already there', IF(@orph = 0, 'added', CONCAT('SKIPPED: ', @orph, ' orphan row(s)')));

SET @has := (SELECT COUNT(*) FROM information_schema.table_constraints WHERE table_schema = DATABASE() AND table_name = 'student_awards' AND constraint_name = 'fk_sa_type');
SET @orph := (SELECT COUNT(*) FROM `student_awards` t LEFT JOIN `award_types` r ON r.`id` = t.`award_type_id` WHERE t.`award_type_id` IS NOT NULL AND r.`id` IS NULL);
SET @sql := IF(@has = 0 AND @orph = 0, 'ALTER TABLE `student_awards` ADD CONSTRAINT `fk_sa_type` FOREIGN KEY (`award_type_id`) REFERENCES `award_types` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE', 'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;
INSERT INTO _mig_report SELECT 'student_awards.award_type_id -> award_types', IF(@has > 0, 'already there', IF(@orph = 0, 'added', CONCAT('SKIPPED: ', @orph, ' orphan row(s)')));

SET @has := (SELECT COUNT(*) FROM information_schema.table_constraints WHERE table_schema = DATABASE() AND table_name = 'student_awards' AND constraint_name = 'fk_sa_teacher');
SET @orph := (SELECT COUNT(*) FROM `student_awards` t LEFT JOIN `users` r ON r.`id` = t.`teacher_id` WHERE t.`teacher_id` IS NOT NULL AND r.`id` IS NULL);
SET @sql := IF(@has = 0 AND @orph = 0, 'ALTER TABLE `student_awards` ADD CONSTRAINT `fk_sa_teacher` FOREIGN KEY (`teacher_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE', 'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;
INSERT INTO _mig_report SELECT 'student_awards.teacher_id -> users', IF(@has > 0, 'already there', IF(@orph = 0, 'added', CONCAT('SKIPPED: ', @orph, ' orphan row(s)')));

SET @has := (SELECT COUNT(*) FROM information_schema.table_constraints WHERE table_schema = DATABASE() AND table_name = 'module_progress' AND constraint_name = 'fk_mp_course');
SET @orph := (SELECT COUNT(*) FROM `module_progress` t LEFT JOIN `courses` r ON r.`id` = t.`course_id` WHERE t.`course_id` IS NOT NULL AND r.`id` IS NULL);
SET @sql := IF(@has = 0 AND @orph = 0, 'ALTER TABLE `module_progress` ADD CONSTRAINT `fk_mp_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE', 'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;
INSERT INTO _mig_report SELECT 'module_progress.course_id -> courses', IF(@has > 0, 'already there', IF(@orph = 0, 'added', CONCAT('SKIPPED: ', @orph, ' orphan row(s)')));

SET @has := (SELECT COUNT(*) FROM information_schema.table_constraints WHERE table_schema = DATABASE() AND table_name = 'password_resets' AND constraint_name = 'fk_pr_user');
SET @orph := (SELECT COUNT(*) FROM `password_resets` t LEFT JOIN `users` r ON r.`id` = t.`user_id` WHERE t.`user_id` IS NOT NULL AND r.`id` IS NULL);
SET @sql := IF(@has = 0 AND @orph = 0, 'ALTER TABLE `password_resets` ADD CONSTRAINT `fk_pr_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE', 'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;
INSERT INTO _mig_report SELECT 'password_resets.user_id -> users', IF(@has > 0, 'already there', IF(@orph = 0, 'added', CONCAT('SKIPPED: ', @orph, ' orphan row(s)')));

-- ── 3. Change feed for live updates ─────────────────────────
CREATE TABLE IF NOT EXISTS `sync_events` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `kind` varchar(40) NOT NULL COMMENT 'e.g. assignment.new, submission.new, grade.updated',
  `course_id` int(10) UNSIGNED DEFAULT NULL,
  `section` varchar(100) DEFAULT NULL COMMENT 'student section for student-specific events',
  `owner_id` int(10) UNSIGNED DEFAULT NULL COMMENT 'teacher who owns the item (NULL = shared)',
  `user_id` int(10) UNSIGNED DEFAULT NULL COMMENT 'the one user the event is about (student / teacher / account)',
  `ref_id` int(10) UNSIGNED DEFAULT NULL,
  `audience` enum('all','students','teachers','admins') NOT NULL DEFAULT 'all',
  `title` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_se_created` (`created_at`),
  KEY `idx_se_user` (`user_id`, `id`),
  KEY `idx_se_course` (`course_id`, `id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


SET @has := (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'users' AND column_name = 'notif_seen_id');
SET @sql := IF(@has = 0, 'ALTER TABLE `users` ADD COLUMN `notif_seen_id` bigint(20) UNSIGNED NOT NULL DEFAULT 0 COMMENT ''Last sync_events id this user marked as read'' AFTER `status`', 'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

-- First run only: fill the feed from what already happened, so notification
-- bells are not empty (real rows from the last 30 days only — announcements
-- and submissions; the feed keeps 30 days).
SET @empty := (SELECT COUNT(*) = 0 FROM sync_events);
INSERT INTO sync_events (kind, course_id, owner_id, ref_id, audience, title, created_at)
SELECT 'announcement.new', a.course_id, IF(u.role = 'Teacher', a.author_id, NULL), a.id,
       CASE a.target_audience WHEN 'Students' THEN 'students' WHEN 'Teachers' THEN 'teachers' ELSE 'all' END, a.title, a.posted_at
FROM announcements a JOIN users u ON u.id = a.author_id WHERE @empty = 1 AND a.posted_at >= NOW() - INTERVAL 30 DAY ORDER BY a.posted_at;
INSERT INTO sync_events (kind, course_id, section, owner_id, user_id, ref_id, audience, title, created_at)
SELECT 'submission.new', a.course_id, u.section_dept, a.teacher_id, s.student_id, a.id, 'teachers', a.title, s.submitted_at
FROM submissions s JOIN assignments a ON a.id = s.assignment_id JOIN users u ON u.id = s.student_id
WHERE @empty = 1 AND s.attempt_count > 0 AND s.submitted_at >= NOW() - INTERVAL 30 DAY ORDER BY s.submitted_at;
INSERT INTO sync_events (kind, course_id, section, owner_id, user_id, ref_id, audience, title, created_at)
SELECT 'grade.updated', a.course_id, u.section_dept, a.teacher_id, s.student_id, a.id, 'all', a.title, s.graded_at
FROM submissions s JOIN assignments a ON a.id = s.assignment_id JOIN users u ON u.id = s.student_id
WHERE @empty = 1 AND s.score IS NOT NULL AND s.graded_at >= NOW() - INTERVAL 30 DAY ORDER BY s.graded_at;


-- ── 4. Triggers ─────────────────────────────────────────────
DELIMITER $$

DROP TRIGGER IF EXISTS `trg_gb_assignment_insert`$$
CREATE TRIGGER `trg_gb_assignment_insert` AFTER INSERT ON `assignments` FOR EACH ROW
BEGIN
  INSERT IGNORE INTO gradebook_items (course_id, teacher_id, source_type, source_id, title, max_score, created_at)
  VALUES (NEW.course_id, NEW.teacher_id, 'assignment', NEW.id, NEW.title, COALESCE(NEW.max_score, 100), COALESCE(NEW.created_at, NOW()));
  INSERT INTO sync_events (kind, course_id, section, owner_id, user_id, ref_id, audience, title) VALUES ('assignment.new', NEW.course_id, NULL, NEW.teacher_id, NULL, NEW.id, 'all', NEW.title);
END$$

DROP TRIGGER IF EXISTS `trg_gb_assignment_update`$$
CREATE TRIGGER `trg_gb_assignment_update` AFTER UPDATE ON `assignments` FOR EACH ROW
BEGIN
  UPDATE gradebook_items SET course_id = NEW.course_id, teacher_id = NEW.teacher_id, title = NEW.title, max_score = COALESCE(NEW.max_score, 100)
  WHERE source_type = 'assignment' AND source_id = NEW.id;
  IF NOT (NEW.title <=> OLD.title AND NEW.due_date <=> OLD.due_date AND NEW.max_score <=> OLD.max_score
          AND NEW.description <=> OLD.description AND NEW.instructions <=> OLD.instructions
          AND NEW.file_path <=> OLD.file_path AND NEW.max_attempts <=> OLD.max_attempts) THEN
    INSERT INTO sync_events (kind, course_id, section, owner_id, user_id, ref_id, audience, title) VALUES ('assignment.updated', NEW.course_id, NULL, NEW.teacher_id, NULL, NEW.id, 'all', NEW.title);
  END IF;
END$$

DROP TRIGGER IF EXISTS `trg_gb_assignment_delete`$$
CREATE TRIGGER `trg_gb_assignment_delete` AFTER DELETE ON `assignments` FOR EACH ROW
BEGIN
  DELETE FROM gradebook_items WHERE source_type = 'assignment' AND source_id = OLD.id;
  INSERT INTO sync_events (kind, course_id, section, owner_id, user_id, ref_id, audience, title) VALUES ('assignment.deleted', OLD.course_id, NULL, OLD.teacher_id, NULL, OLD.id, 'all', OLD.title);
END$$

DROP TRIGGER IF EXISTS `trg_gb_quiz_insert`$$
CREATE TRIGGER `trg_gb_quiz_insert` AFTER INSERT ON `quizzes` FOR EACH ROW
BEGIN
  INSERT IGNORE INTO gradebook_items (course_id, teacher_id, source_type, source_id, title, max_score, created_at)
  VALUES (NEW.course_id, NEW.teacher_id, 'quiz', NEW.id, NEW.title, COALESCE(NEW.max_score, 100), COALESCE(NEW.created_at, NOW()));
  IF NEW.status = 'Published' THEN
    INSERT INTO sync_events (kind, course_id, section, owner_id, user_id, ref_id, audience, title) VALUES ('quiz.new', NEW.course_id, NULL, NEW.teacher_id, NULL, NEW.id, 'all', NEW.title);
  ELSE
    INSERT INTO sync_events (kind, course_id, section, owner_id, user_id, ref_id, audience, title) VALUES ('quiz.draft', NEW.course_id, NULL, NEW.teacher_id, NULL, NEW.id, 'teachers', NEW.title);
  END IF;
END$$

DROP TRIGGER IF EXISTS `trg_gb_quiz_update`$$
CREATE TRIGGER `trg_gb_quiz_update` AFTER UPDATE ON `quizzes` FOR EACH ROW
BEGIN
  UPDATE gradebook_items SET course_id = NEW.course_id, teacher_id = NEW.teacher_id, title = NEW.title, max_score = COALESCE(NEW.max_score, 100)
  WHERE source_type = 'quiz' AND source_id = NEW.id;
  IF NEW.status = 'Published' AND OLD.status <> 'Published' THEN
    INSERT INTO sync_events (kind, course_id, section, owner_id, user_id, ref_id, audience, title) VALUES ('quiz.new', NEW.course_id, NULL, NEW.teacher_id, NULL, NEW.id, 'all', NEW.title);
  ELSEIF NEW.status <> 'Published' AND OLD.status = 'Published' THEN
    INSERT INTO sync_events (kind, course_id, section, owner_id, user_id, ref_id, audience, title) VALUES ('quiz.deleted', NEW.course_id, NULL, NEW.teacher_id, NULL, NEW.id, 'all', NEW.title);
  ELSEIF NOT (NEW.title <=> OLD.title AND NEW.open_at <=> OLD.open_at AND NEW.close_at <=> OLD.close_at
              AND NEW.max_score <=> OLD.max_score AND NEW.time_limit <=> OLD.time_limit AND NEW.description <=> OLD.description) THEN
    INSERT INTO sync_events (kind, course_id, section, owner_id, user_id, ref_id, audience, title) VALUES ('quiz.updated', NEW.course_id, NULL, NEW.teacher_id, NULL, NEW.id, IF(NEW.status = 'Published', 'all', 'teachers'), NEW.title);
  END IF;
END$$

DROP TRIGGER IF EXISTS `trg_gb_quiz_delete`$$
CREATE TRIGGER `trg_gb_quiz_delete` AFTER DELETE ON `quizzes` FOR EACH ROW
BEGIN
  DELETE FROM gradebook_items WHERE source_type = 'quiz' AND source_id = OLD.id;
  INSERT INTO sync_events (kind, course_id, section, owner_id, user_id, ref_id, audience, title) VALUES ('quiz.deleted', OLD.course_id, NULL, OLD.teacher_id, NULL, OLD.id, IF(OLD.status = 'Published', 'all', 'teachers'), OLD.title);
END$$

DROP TRIGGER IF EXISTS `trg_sync_submission_insert`$$
CREATE TRIGGER `trg_sync_submission_insert` AFTER INSERT ON `submissions` FOR EACH ROW
BEGIN
  IF NEW.attempt_count > 0 THEN
    INSERT INTO sync_events (kind, course_id, section, owner_id, user_id, ref_id, audience, title) VALUES ('submission.new', (SELECT course_id FROM assignments WHERE id = NEW.assignment_id), (SELECT section_dept FROM users WHERE id = NEW.student_id), (SELECT teacher_id FROM assignments WHERE id = NEW.assignment_id), NEW.student_id, NEW.assignment_id, 'teachers', (SELECT title FROM assignments WHERE id = NEW.assignment_id));
  END IF;
  IF NEW.score IS NOT NULL OR (NEW.remarks IS NOT NULL AND NEW.remarks <> '') THEN
    INSERT INTO sync_events (kind, course_id, section, owner_id, user_id, ref_id, audience, title) VALUES ('grade.updated', (SELECT course_id FROM assignments WHERE id = NEW.assignment_id), (SELECT section_dept FROM users WHERE id = NEW.student_id), (SELECT teacher_id FROM assignments WHERE id = NEW.assignment_id), NEW.student_id, NEW.assignment_id, 'all', (SELECT title FROM assignments WHERE id = NEW.assignment_id));
  END IF;
END$$

DROP TRIGGER IF EXISTS `trg_sync_submission_update`$$
CREATE TRIGGER `trg_sync_submission_update` AFTER UPDATE ON `submissions` FOR EACH ROW
BEGIN
  IF NEW.attempt_count > OLD.attempt_count THEN
    INSERT INTO sync_events (kind, course_id, section, owner_id, user_id, ref_id, audience, title) VALUES ('submission.new', (SELECT course_id FROM assignments WHERE id = NEW.assignment_id), (SELECT section_dept FROM users WHERE id = NEW.student_id), (SELECT teacher_id FROM assignments WHERE id = NEW.assignment_id), NEW.student_id, NEW.assignment_id, 'teachers', (SELECT title FROM assignments WHERE id = NEW.assignment_id));
  ELSEIF NOT (NEW.score <=> OLD.score AND NEW.remarks <=> OLD.remarks) THEN
    INSERT INTO sync_events (kind, course_id, section, owner_id, user_id, ref_id, audience, title) VALUES ('grade.updated', (SELECT course_id FROM assignments WHERE id = NEW.assignment_id), (SELECT section_dept FROM users WHERE id = NEW.student_id), (SELECT teacher_id FROM assignments WHERE id = NEW.assignment_id), NEW.student_id, NEW.assignment_id, 'all', (SELECT title FROM assignments WHERE id = NEW.assignment_id));
  END IF;
END$$

DROP TRIGGER IF EXISTS `trg_sync_submission_delete`$$
CREATE TRIGGER `trg_sync_submission_delete` AFTER DELETE ON `submissions` FOR EACH ROW
BEGIN
  INSERT INTO sync_events (kind, course_id, section, owner_id, user_id, ref_id, audience, title) VALUES ('grade.updated', (SELECT course_id FROM assignments WHERE id = OLD.assignment_id), (SELECT section_dept FROM users WHERE id = OLD.student_id), (SELECT teacher_id FROM assignments WHERE id = OLD.assignment_id), OLD.student_id, OLD.assignment_id, 'all', (SELECT title FROM assignments WHERE id = OLD.assignment_id));
END$$

DROP TRIGGER IF EXISTS `trg_sync_attempt_insert`$$
CREATE TRIGGER `trg_sync_attempt_insert` AFTER INSERT ON `quiz_attempts` FOR EACH ROW
BEGIN
  IF NEW.status IN ('Submitted', 'Graded') THEN
    INSERT INTO sync_events (kind, course_id, section, owner_id, user_id, ref_id, audience, title) VALUES ('quiz.submitted', (SELECT course_id FROM quizzes WHERE id = NEW.quiz_id), (SELECT section_dept FROM users WHERE id = NEW.student_id), (SELECT teacher_id FROM quizzes WHERE id = NEW.quiz_id), NEW.student_id, NEW.quiz_id, 'teachers', (SELECT title FROM quizzes WHERE id = NEW.quiz_id));
  END IF;
END$$

DROP TRIGGER IF EXISTS `trg_sync_attempt_update`$$
CREATE TRIGGER `trg_sync_attempt_update` AFTER UPDATE ON `quiz_attempts` FOR EACH ROW
BEGIN
  IF NEW.status <> OLD.status AND OLD.status = 'In Progress' THEN
    INSERT INTO sync_events (kind, course_id, section, owner_id, user_id, ref_id, audience, title) VALUES ('quiz.submitted', (SELECT course_id FROM quizzes WHERE id = NEW.quiz_id), (SELECT section_dept FROM users WHERE id = NEW.student_id), (SELECT teacher_id FROM quizzes WHERE id = NEW.quiz_id), NEW.student_id, NEW.quiz_id, 'teachers', (SELECT title FROM quizzes WHERE id = NEW.quiz_id));
  END IF;
  IF NOT (NEW.score <=> OLD.score AND NEW.remarks <=> OLD.remarks) THEN
    INSERT INTO sync_events (kind, course_id, section, owner_id, user_id, ref_id, audience, title) VALUES ('grade.updated', (SELECT course_id FROM quizzes WHERE id = NEW.quiz_id), (SELECT section_dept FROM users WHERE id = NEW.student_id), (SELECT teacher_id FROM quizzes WHERE id = NEW.quiz_id), NEW.student_id, NEW.quiz_id, 'all', (SELECT title FROM quizzes WHERE id = NEW.quiz_id));
  END IF;
END$$

DROP TRIGGER IF EXISTS `trg_sync_gbscore_insert`$$
CREATE TRIGGER `trg_sync_gbscore_insert` AFTER INSERT ON `gradebook_scores` FOR EACH ROW
BEGIN
  INSERT INTO sync_events (kind, course_id, section, owner_id, user_id, ref_id, audience, title) VALUES ('grade.updated', (SELECT course_id FROM gradebook_items WHERE id = NEW.item_id), (SELECT section_dept FROM users WHERE id = NEW.student_id), (SELECT teacher_id FROM gradebook_items WHERE id = NEW.item_id), NEW.student_id, NEW.item_id, 'all', (SELECT title FROM gradebook_items WHERE id = NEW.item_id));
END$$

DROP TRIGGER IF EXISTS `trg_sync_gbscore_update`$$
CREATE TRIGGER `trg_sync_gbscore_update` AFTER UPDATE ON `gradebook_scores` FOR EACH ROW
BEGIN
  INSERT INTO sync_events (kind, course_id, section, owner_id, user_id, ref_id, audience, title) VALUES ('grade.updated', (SELECT course_id FROM gradebook_items WHERE id = NEW.item_id), (SELECT section_dept FROM users WHERE id = NEW.student_id), (SELECT teacher_id FROM gradebook_items WHERE id = NEW.item_id), NEW.student_id, NEW.item_id, 'all', (SELECT title FROM gradebook_items WHERE id = NEW.item_id));
END$$

DROP TRIGGER IF EXISTS `trg_sync_gbscore_delete`$$
CREATE TRIGGER `trg_sync_gbscore_delete` AFTER DELETE ON `gradebook_scores` FOR EACH ROW
BEGIN
  INSERT INTO sync_events (kind, course_id, section, owner_id, user_id, ref_id, audience, title) VALUES ('grade.updated', (SELECT course_id FROM gradebook_items WHERE id = OLD.item_id), (SELECT section_dept FROM users WHERE id = OLD.student_id), (SELECT teacher_id FROM gradebook_items WHERE id = OLD.item_id), OLD.student_id, OLD.item_id, 'all', (SELECT title FROM gradebook_items WHERE id = OLD.item_id));
END$$

DROP TRIGGER IF EXISTS `trg_sync_attendance_insert`$$
CREATE TRIGGER `trg_sync_attendance_insert` AFTER INSERT ON `attendance` FOR EACH ROW
BEGIN
  INSERT INTO sync_events (kind, course_id, section, owner_id, user_id, ref_id, audience, title) VALUES ('attendance.updated', NEW.course_id, (SELECT section_dept FROM users WHERE id = NEW.student_id), NULLIF(NEW.recorded_by, 0), NEW.student_id, NULL, 'all', DATE_FORMAT(NEW.date, '%Y-%m-%d'));
END$$

DROP TRIGGER IF EXISTS `trg_sync_attendance_update`$$
CREATE TRIGGER `trg_sync_attendance_update` AFTER UPDATE ON `attendance` FOR EACH ROW
BEGIN
  IF NOT (NEW.status <=> OLD.status AND NEW.remark <=> OLD.remark AND NEW.date <=> OLD.date) THEN
  INSERT INTO sync_events (kind, course_id, section, owner_id, user_id, ref_id, audience, title) VALUES ('attendance.updated', NEW.course_id, (SELECT section_dept FROM users WHERE id = NEW.student_id), NULLIF(NEW.recorded_by, 0), NEW.student_id, NULL, 'all', DATE_FORMAT(NEW.date, '%Y-%m-%d'));
  END IF;
END$$

DROP TRIGGER IF EXISTS `trg_sync_announcement_insert`$$
CREATE TRIGGER `trg_sync_announcement_insert` AFTER INSERT ON `announcements` FOR EACH ROW
BEGIN
  INSERT INTO sync_events (kind, course_id, section, owner_id, user_id, ref_id, audience, title) VALUES ('announcement.new', NEW.course_id, NULL, IF((SELECT role FROM users WHERE id = NEW.author_id) = 'Teacher', NEW.author_id, NULL), NULL, NEW.id, CASE NEW.target_audience WHEN 'Students' THEN 'students' WHEN 'Teachers' THEN 'teachers' ELSE 'all' END, NEW.title);
END$$

DROP TRIGGER IF EXISTS `trg_sync_announcement_delete`$$
CREATE TRIGGER `trg_sync_announcement_delete` AFTER DELETE ON `announcements` FOR EACH ROW
BEGIN
  INSERT INTO sync_events (kind, course_id, section, owner_id, user_id, ref_id, audience, title) VALUES ('announcement.deleted', OLD.course_id, NULL, IF((SELECT role FROM users WHERE id = OLD.author_id) = 'Teacher', OLD.author_id, NULL), NULL, OLD.id, CASE OLD.target_audience WHEN 'Students' THEN 'students' WHEN 'Teachers' THEN 'teachers' ELSE 'all' END, OLD.title);
END$$

DROP TRIGGER IF EXISTS `trg_sync_enrollment_insert`$$
CREATE TRIGGER `trg_sync_enrollment_insert` AFTER INSERT ON `enrollments` FOR EACH ROW
BEGIN
  INSERT INTO sync_events (kind, course_id, section, owner_id, user_id, ref_id, audience, title) VALUES ('enrollment.changed', NEW.course_id, (SELECT section_dept FROM users WHERE id = NEW.student_id), NULL, NEW.student_id, NULL, 'all', NULL);
END$$

DROP TRIGGER IF EXISTS `trg_sync_enrollment_update`$$
CREATE TRIGGER `trg_sync_enrollment_update` AFTER UPDATE ON `enrollments` FOR EACH ROW
BEGIN
  IF NOT (NEW.status <=> OLD.status AND NEW.course_id <=> OLD.course_id) THEN
    INSERT INTO sync_events (kind, course_id, section, owner_id, user_id, ref_id, audience, title) VALUES ('enrollment.changed', NEW.course_id, (SELECT section_dept FROM users WHERE id = NEW.student_id), NULL, NEW.student_id, NULL, 'all', NULL);
  END IF;
END$$

DROP TRIGGER IF EXISTS `trg_sync_enrollment_delete`$$
CREATE TRIGGER `trg_sync_enrollment_delete` AFTER DELETE ON `enrollments` FOR EACH ROW
BEGIN
  INSERT INTO sync_events (kind, course_id, section, owner_id, user_id, ref_id, audience, title) VALUES ('enrollment.changed', OLD.course_id, (SELECT section_dept FROM users WHERE id = OLD.student_id), NULL, OLD.student_id, NULL, 'all', NULL);
END$$

DROP TRIGGER IF EXISTS `trg_sync_class_insert`$$
CREATE TRIGGER `trg_sync_class_insert` AFTER INSERT ON `teacher_assignments` FOR EACH ROW
BEGIN
  INSERT INTO sync_events (kind, course_id, section, owner_id, user_id, ref_id, audience, title) VALUES ('class.changed', NEW.course_id, NEW.section, NULL, NEW.teacher_id, NEW.id, 'all', NULL);
END$$

DROP TRIGGER IF EXISTS `trg_sync_class_update`$$
CREATE TRIGGER `trg_sync_class_update` AFTER UPDATE ON `teacher_assignments` FOR EACH ROW
BEGIN
  INSERT INTO sync_events (kind, course_id, section, owner_id, user_id, ref_id, audience, title) VALUES ('class.changed', NEW.course_id, NEW.section, NULL, NEW.teacher_id, NEW.id, 'all', NULL);
  IF NOT (NEW.teacher_id <=> OLD.teacher_id AND NEW.section <=> OLD.section AND NEW.course_id <=> OLD.course_id) THEN
    INSERT INTO sync_events (kind, course_id, section, owner_id, user_id, ref_id, audience, title) VALUES ('class.changed', OLD.course_id, OLD.section, NULL, OLD.teacher_id, OLD.id, 'all', NULL);
  END IF;
END$$

DROP TRIGGER IF EXISTS `trg_sync_class_delete`$$
CREATE TRIGGER `trg_sync_class_delete` AFTER DELETE ON `teacher_assignments` FOR EACH ROW
BEGIN
  INSERT INTO sync_events (kind, course_id, section, owner_id, user_id, ref_id, audience, title) VALUES ('class.changed', OLD.course_id, OLD.section, NULL, OLD.teacher_id, OLD.id, 'all', NULL);
END$$

DROP TRIGGER IF EXISTS `trg_sync_module_insert`$$
CREATE TRIGGER `trg_sync_module_insert` AFTER INSERT ON `modules` FOR EACH ROW
BEGIN
  INSERT INTO sync_events (kind, course_id, section, owner_id, user_id, ref_id, audience, title) VALUES ('module.changed', NEW.course_id, NULL, NULL, NULL, NEW.id, 'all', NEW.title);
END$$

DROP TRIGGER IF EXISTS `trg_sync_module_update`$$
CREATE TRIGGER `trg_sync_module_update` AFTER UPDATE ON `modules` FOR EACH ROW
BEGIN
  INSERT INTO sync_events (kind, course_id, section, owner_id, user_id, ref_id, audience, title) VALUES ('module.changed', NEW.course_id, NULL, NULL, NULL, NEW.id, 'all', NEW.title);
END$$

DROP TRIGGER IF EXISTS `trg_sync_module_delete`$$
CREATE TRIGGER `trg_sync_module_delete` AFTER DELETE ON `modules` FOR EACH ROW
BEGIN
  INSERT INTO sync_events (kind, course_id, section, owner_id, user_id, ref_id, audience, title) VALUES ('module.changed', OLD.course_id, NULL, NULL, NULL, OLD.id, 'all', OLD.title);
END$$

DROP TRIGGER IF EXISTS `trg_sync_progress_insert`$$
CREATE TRIGGER `trg_sync_progress_insert` AFTER INSERT ON `module_progress` FOR EACH ROW
BEGIN
  INSERT INTO sync_events (kind, course_id, section, owner_id, user_id, ref_id, audience, title) VALUES ('module.progress', NEW.course_id, (SELECT section_dept FROM users WHERE id = NEW.student_id), NULL, NEW.student_id, NEW.module_id, 'teachers', NULL);
END$$

DROP TRIGGER IF EXISTS `trg_sync_award_insert`$$
CREATE TRIGGER `trg_sync_award_insert` AFTER INSERT ON `student_awards` FOR EACH ROW
BEGIN
  INSERT INTO sync_events (kind, course_id, section, owner_id, user_id, ref_id, audience, title) VALUES ('award.changed', NEW.course_id, (SELECT section_dept FROM users WHERE id = NEW.student_id), NEW.teacher_id, NEW.student_id, NEW.id, 'all', NULL);
END$$

DROP TRIGGER IF EXISTS `trg_sync_award_update`$$
CREATE TRIGGER `trg_sync_award_update` AFTER UPDATE ON `student_awards` FOR EACH ROW
BEGIN
  INSERT INTO sync_events (kind, course_id, section, owner_id, user_id, ref_id, audience, title) VALUES ('award.changed', NEW.course_id, (SELECT section_dept FROM users WHERE id = NEW.student_id), NEW.teacher_id, NEW.student_id, NEW.id, 'all', NULL);
END$$

DROP TRIGGER IF EXISTS `trg_sync_user_insert`$$
CREATE TRIGGER `trg_sync_user_insert` AFTER INSERT ON `users` FOR EACH ROW
BEGIN
  INSERT INTO sync_events (kind, course_id, section, owner_id, user_id, ref_id, audience, title) VALUES ('account.changed', NULL, NULL, NULL, NEW.id, NULL, 'admins', NULL);
END$$

DROP TRIGGER IF EXISTS `trg_sync_user_update`$$
CREATE TRIGGER `trg_sync_user_update` AFTER UPDATE ON `users` FOR EACH ROW
BEGIN
  IF NOT (NEW.status <=> OLD.status AND NEW.role <=> OLD.role AND NEW.section_dept <=> OLD.section_dept
          AND NEW.first_name <=> OLD.first_name AND NEW.last_name <=> OLD.last_name AND NEW.username <=> OLD.username) THEN
    INSERT INTO sync_events (kind, course_id, section, owner_id, user_id, ref_id, audience, title) VALUES ('account.changed', NULL, NEW.section_dept, NULL, NEW.id, NULL, 'all', NULL);
  END IF;
END$$

DROP TRIGGER IF EXISTS `trg_sync_user_delete`$$
CREATE TRIGGER `trg_sync_user_delete` AFTER DELETE ON `users` FOR EACH ROW
BEGIN
  INSERT INTO sync_events (kind, course_id, section, owner_id, user_id, ref_id, audience, title) VALUES ('account.changed', NULL, NULL, NULL, OLD.id, NULL, 'admins', NULL);
END$$

DROP TRIGGER IF EXISTS `trg_sync_banner_insert`$$
CREATE TRIGGER `trg_sync_banner_insert` AFTER INSERT ON `dashboard_banners` FOR EACH ROW
BEGIN
  INSERT INTO sync_events (kind, course_id, section, owner_id, user_id, ref_id, audience, title) VALUES ('banner.changed', NULL, NULL, NULL, NULL, NULL, 'all', NULL);
END$$

DROP TRIGGER IF EXISTS `trg_sync_banner_update`$$
CREATE TRIGGER `trg_sync_banner_update` AFTER UPDATE ON `dashboard_banners` FOR EACH ROW
BEGIN
  INSERT INTO sync_events (kind, course_id, section, owner_id, user_id, ref_id, audience, title) VALUES ('banner.changed', NULL, NULL, NULL, NULL, NULL, 'all', NULL);
END$$

DROP TRIGGER IF EXISTS `trg_sync_banner_delete`$$
CREATE TRIGGER `trg_sync_banner_delete` AFTER DELETE ON `dashboard_banners` FOR EACH ROW
BEGIN
  INSERT INTO sync_events (kind, course_id, section, owner_id, user_id, ref_id, audience, title) VALUES ('banner.changed', NULL, NULL, NULL, NULL, NULL, 'all', NULL);
END$$

DELIMITER ;


-- ── Result ──────────────────────────────────────────────────
SELECT item AS foreign_key, result FROM _mig_report;
SELECT 'assignments with owner' AS info, SUM(teacher_id IS NOT NULL) AS filled, COUNT(*) AS total FROM assignments
UNION ALL SELECT 'quizzes with owner', SUM(teacher_id IS NOT NULL), COUNT(*) FROM quizzes
UNION ALL SELECT 'gradebook items with owner', SUM(teacher_id IS NOT NULL), COUNT(*) FROM gradebook_items;
DROP TEMPORARY TABLE IF EXISTS _mig_report;
