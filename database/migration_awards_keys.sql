-- ============================================================
--  Arandia College eLMS — Awards: restore keys
--  File: database/migration_awards_keys.sql
--
--  Problem: in the current elmsdb, `award_types` and `student_awards`
--  have no PRIMARY KEY and no AUTO_INCREMENT on `id`. With MySQL strict
--  mode (XAMPP default) every "Give Award" fails with
--  "Field 'id' doesn't have a default value", and new award types can't
--  be created either.
--
--  This file only ADDS keys and indexes. No row is deleted or changed
--  (an existing id of 0, if any, gets a normal new id). Safe to run more
--  than once: each step is skipped when it is already in place.
--  Back up elmsdb first (phpMyAdmin → Export).
-- ============================================================

-- Helper pattern: build the ALTER only when the key is missing.

-- ── award_types ───────────────────────────────────────────────
SET @has := (SELECT COUNT(*) FROM information_schema.table_constraints
             WHERE table_schema = DATABASE() AND table_name = 'award_types' AND constraint_type = 'PRIMARY KEY');
SET @sql := IF(@has = 0, 'ALTER TABLE `award_types` ADD PRIMARY KEY (`id`)', 'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

ALTER TABLE `award_types` MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

SET @has := (SELECT COUNT(*) FROM information_schema.statistics
             WHERE table_schema = DATABASE() AND table_name = 'award_types' AND index_name = 'idx_award_types_status');
SET @sql := IF(@has = 0, 'ALTER TABLE `award_types` ADD KEY `idx_award_types_status` (`status`)', 'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

-- ── student_awards ────────────────────────────────────────────
SET @has := (SELECT COUNT(*) FROM information_schema.table_constraints
             WHERE table_schema = DATABASE() AND table_name = 'student_awards' AND constraint_type = 'PRIMARY KEY');
SET @sql := IF(@has = 0, 'ALTER TABLE `student_awards` ADD PRIMARY KEY (`id`)', 'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

ALTER TABLE `student_awards` MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

-- The API relies on request_token being unique (stops double submits).
-- Only added when no duplicate tokens exist.
SET @has := (SELECT COUNT(*) FROM information_schema.statistics
             WHERE table_schema = DATABASE() AND table_name = 'student_awards' AND index_name = 'uq_award_request');
SET @dup := (SELECT COUNT(*) FROM (SELECT request_token FROM student_awards WHERE request_token IS NOT NULL
             GROUP BY request_token HAVING COUNT(*) > 1) d);
SET @sql := IF(@has = 0 AND @dup = 0, 'ALTER TABLE `student_awards` ADD UNIQUE KEY `uq_award_request` (`request_token`)', 'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @has := (SELECT COUNT(*) FROM information_schema.statistics
             WHERE table_schema = DATABASE() AND table_name = 'student_awards' AND index_name = 'idx_sa_student');
SET @sql := IF(@has = 0, 'ALTER TABLE `student_awards` ADD KEY `idx_sa_student` (`student_id`, `status`)', 'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @has := (SELECT COUNT(*) FROM information_schema.statistics
             WHERE table_schema = DATABASE() AND table_name = 'student_awards' AND index_name = 'idx_sa_teacher');
SET @sql := IF(@has = 0, 'ALTER TABLE `student_awards` ADD KEY `idx_sa_teacher` (`teacher_id`, `status`)', 'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @has := (SELECT COUNT(*) FROM information_schema.statistics
             WHERE table_schema = DATABASE() AND table_name = 'student_awards' AND index_name = 'idx_sa_course');
SET @sql := IF(@has = 0, 'ALTER TABLE `student_awards` ADD KEY `idx_sa_course` (`course_id`)', 'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @has := (SELECT COUNT(*) FROM information_schema.statistics
             WHERE table_schema = DATABASE() AND table_name = 'student_awards' AND index_name = 'idx_sa_type');
SET @sql := IF(@has = 0, 'ALTER TABLE `student_awards` ADD KEY `idx_sa_type` (`award_type_id`)', 'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

-- Show the result
SELECT 'award_types' AS tbl, COUNT(*) AS rows_kept FROM award_types
UNION ALL SELECT 'student_awards', COUNT(*) FROM student_awards;
