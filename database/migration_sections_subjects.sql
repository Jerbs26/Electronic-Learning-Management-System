-- ============================================================
--  Arandia College eLMS — Sections + section-based Subjects page
--  File: database/migration_sections_subjects.sql
--
--  BACK UP elmsdb FIRST (phpMyAdmin > Export).
--  Then: phpMyAdmin > elmsdb > Import > this file.
--
--  What it does:
--   1. Creates the `sections` table and fills it with the 16 standard
--      sections plus any section name already used in your data.
--   2. teacher_assignments: a subject in a section can now exist without
--      a teacher (teacher_id NULL = "needs a teacher"). Rows pointing to
--      deleted teachers are kept as unassigned instead of breaking.
--      One teacher per subject per section per term (new unique key).
--   3. enrollments: removes rows for deleted students/courses, blocks
--      duplicate enrollments, and adds foreign keys so deleting a user
--      or course cleans up after itself.
--   4. login_otps: removes rows for deleted users, adds a foreign key.
--   5. Relabels the unused view_classes / manage_classes permissions as
--      View Sections / Manage Sections (they now control section setup).
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;

-- ── 1. sections ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `sections` (
  `id`         int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`       varchar(100) NOT NULL,
  `level`      enum('SHS','HS') NOT NULL DEFAULT 'HS',
  `sort_order` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `status`     enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_section_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `sections` (`name`, `level`, `sort_order`) VALUES
('Grade 7', 'HS', 10), ('Grade 8', 'HS', 20), ('Grade 9', 'HS', 30), ('Grade 10', 'HS', 40),
('Grade 11 - ABM-A', 'SHS', 110), ('Grade 11 - ABM-B', 'SHS', 120),
('Grade 11 - GAS-A', 'SHS', 130), ('Grade 11 - GAS-B', 'SHS', 140),
('Grade 11 - HUMSS-A', 'SHS', 150), ('Grade 11 - HUMSS-B', 'SHS', 160),
('Grade 12 - ABM-A', 'SHS', 210), ('Grade 12 - ABM-B', 'SHS', 220),
('Grade 12 - GAS-A', 'SHS', 230), ('Grade 12 - GAS-B', 'SHS', 240),
('Grade 12 - HUMSS-A', 'SHS', 250), ('Grade 12 - HUMSS-B', 'SHS', 260);

-- Any other section name already in use (so nothing disappears)
INSERT IGNORE INTO `sections` (`name`, `level`, `sort_order`)
SELECT DISTINCT TRIM(s.name),
       IF(TRIM(s.name) LIKE 'Grade 11%' OR TRIM(s.name) LIKE 'Grade 12%', 'SHS', 'HS'),
       900
FROM (
  SELECT section_dept AS name FROM users WHERE role IN ('Student','Teacher')
  UNION SELECT section FROM teacher_assignments
) s
WHERE s.name IS NOT NULL AND TRIM(s.name) <> '';

-- ── 2. teacher_assignments ─────────────────────────────────
ALTER TABLE `teacher_assignments` MODIFY `teacher_id` int(10) UNSIGNED NULL DEFAULT NULL;

-- Rows whose teacher no longer exists (or is no longer a Teacher)
-- become "needs a teacher" instead of pointing at nobody
UPDATE `teacher_assignments` ta
LEFT JOIN `users` u ON u.id = ta.teacher_id AND u.role = 'Teacher'
SET ta.teacher_id = NULL
WHERE ta.teacher_id IS NOT NULL AND u.id IS NULL;

-- Remove rows for deleted courses
DELETE ta FROM `teacher_assignments` ta
LEFT JOIN `courses` c ON c.id = ta.course_id
WHERE c.id IS NULL;

-- One row per subject + section + term: keep the row that has a teacher
-- (newest first), drop the rest
DELETE ta FROM `teacher_assignments` ta
JOIN `teacher_assignments` keep
  ON keep.course_id = ta.course_id AND keep.section = ta.section
 AND keep.school_year = ta.school_year AND keep.semester = ta.semester
 AND keep.id <> ta.id
 AND ( (keep.teacher_id IS NOT NULL AND ta.teacher_id IS NULL)
    OR ((keep.teacher_id IS NULL) = (ta.teacher_id IS NULL) AND keep.id > ta.id) );

ALTER TABLE `teacher_assignments` DROP INDEX `uq_assignment`;
ALTER TABLE `teacher_assignments`
  ADD UNIQUE KEY `uq_section_subject` (`course_id`, `section`, `school_year`, `semester`),
  ADD KEY `idx_ta_teacher` (`teacher_id`),
  ADD KEY `idx_ta_section` (`section`),
  ADD CONSTRAINT `fk_ta_teacher` FOREIGN KEY (`teacher_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_ta_course`  FOREIGN KEY (`course_id`)  REFERENCES `courses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- ── 3. enrollments ─────────────────────────────────────────
DELETE e FROM `enrollments` e LEFT JOIN `users` u ON u.id = e.student_id WHERE u.id IS NULL;
DELETE e FROM `enrollments` e LEFT JOIN `courses` c ON c.id = e.course_id WHERE c.id IS NULL;
DELETE e FROM `enrollments` e
JOIN `enrollments` keep ON keep.student_id = e.student_id AND keep.course_id = e.course_id AND keep.id < e.id;

ALTER TABLE `enrollments`
  ADD UNIQUE KEY `uq_enroll` (`student_id`, `course_id`),
  ADD CONSTRAINT `fk_enr_student` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_enr_course`  FOREIGN KEY (`course_id`)  REFERENCES `courses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- ── 4. login_otps ──────────────────────────────────────────
DELETE o FROM `login_otps` o LEFT JOIN `users` u ON u.id = o.user_id WHERE u.id IS NULL;
ALTER TABLE `login_otps`
  ADD CONSTRAINT `fk_otp_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

-- ── 5. permission labels ───────────────────────────────────
UPDATE `permissions` SET `label` = 'View Sections',   `module` = 'sections' WHERE `perm_key` = 'view_classes';
UPDATE `permissions` SET `label` = 'Manage Sections', `module` = 'sections' WHERE `perm_key` = 'manage_classes';

SET FOREIGN_KEY_CHECKS = 1;
