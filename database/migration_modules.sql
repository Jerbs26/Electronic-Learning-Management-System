-- ============================================================
--  Arandia College eLMS — Admin-managed modules
--  File: database/migration_modules.sql
--
--  BACK UP elmsdb FIRST. Import AFTER migration_sections_subjects.sql.
--  Run once.
--
--  What it does:
--   1. modules: adds module_number (order inside a subject), content (the
--      lesson students read in the page), and who/when last updated it.
--      Existing modules are kept and numbered 6, 7, ... so slots 1-5 are
--      free for the starter lessons. Every module is visible (published = 1).
--   2. module_views: records when a student opens a module ("Opened").
--      module_progress keeps meaning "Done".
--   3. Foreign keys so deleting a subject, module, or student cleans up.
--   4. New admin permission: Manage Modules.
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;

-- ── 1. modules ─────────────────────────────────────────────
ALTER TABLE `modules`
  ADD COLUMN `module_number` smallint(5) UNSIGNED NULL AFTER `course_id`,
  ADD COLUMN `content` mediumtext NULL AFTER `description`,
  ADD COLUMN `updated_by` int(10) UNSIGNED NULL AFTER `published`,
  ADD COLUMN `updated_at` datetime NULL DEFAULT NULL ON UPDATE current_timestamp() AFTER `created_at`;

DELETE m FROM `modules` m LEFT JOIN `courses` c ON c.id = m.course_id WHERE c.id IS NULL;

-- Existing modules go after the 5 starter slots, in the order they were created
UPDATE `modules` m
JOIN (
  SELECT id, 5 + ROW_NUMBER() OVER (PARTITION BY course_id ORDER BY created_at, id) AS n
  FROM `modules`
) x ON x.id = m.id
SET m.module_number = x.n;

UPDATE `modules` SET `published` = 1;
ALTER TABLE `modules` ALTER `published` SET DEFAULT 1;

ALTER TABLE `modules`
  MODIFY `module_number` smallint(5) UNSIGNED NOT NULL,
  ADD UNIQUE KEY `uq_course_module` (`course_id`, `module_number`),
  ADD CONSTRAINT `fk_mod_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_mod_updated_by` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

-- ── 2. module_views ("Opened") ─────────────────────────────
CREATE TABLE IF NOT EXISTS `module_views` (
  `student_id`      int(10) UNSIGNED NOT NULL,
  `module_id`       int(10) UNSIGNED NOT NULL,
  `first_opened_at` datetime NOT NULL DEFAULT current_timestamp(),
  `last_opened_at`  datetime NOT NULL DEFAULT current_timestamp(),
  `open_count`      int(10) UNSIGNED NOT NULL DEFAULT 1,
  PRIMARY KEY (`student_id`, `module_id`),
  KEY `idx_mv_module` (`module_id`),
  CONSTRAINT `fk_mv_student` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_mv_module`  FOREIGN KEY (`module_id`)  REFERENCES `modules` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- A module someone already finished counts as opened too
INSERT IGNORE INTO `module_views` (student_id, module_id, first_opened_at, last_opened_at)
SELECT student_id, module_id, completed_at, completed_at FROM `module_progress`;

-- ── 3. module_progress ("Done") clean-up + keys ────────────
DELETE mp FROM `module_progress` mp LEFT JOIN `modules` m ON m.id = mp.module_id WHERE m.id IS NULL;
DELETE mp FROM `module_progress` mp LEFT JOIN `users` u ON u.id = mp.student_id WHERE u.id IS NULL;
ALTER TABLE `module_progress`
  ADD CONSTRAINT `fk_mp_student` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_mp_module`  FOREIGN KEY (`module_id`)  REFERENCES `modules` (`id`) ON DELETE CASCADE;

-- ── 4. permission ──────────────────────────────────────────
INSERT IGNORE INTO `permissions` (`perm_key`, `label`, `type`, `module`, `sort_order`)
VALUES ('manage_modules', 'Manage Modules', 'manage', 'modules', 35);

SET FOREIGN_KEY_CHECKS = 1;
