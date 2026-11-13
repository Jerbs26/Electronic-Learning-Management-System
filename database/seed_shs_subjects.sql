-- ============================================================
--  Arandia College eLMS — Senior High subjects per strand
--  File: database/seed_shs_subjects.sql
--
--  Import AFTER database/migration_sections_subjects.sql.
--  Safe to run more than once: it never creates duplicates.
--
--  1. Adds the STEM sections (Grade 11/12 - STEM-A and STEM-B).
--  2. Makes sure every subject in the curriculum below exists in `courses`.
--     Subjects that already exist keep their id (so existing enrollments,
--     modules and quizzes stay attached); only their name/code is tidied.
--     A subject shared by several strands (e.g. General Mathematics) is ONE
--     course used by all of them.
--  3. Adds each strand's subjects to every section of that strand and grade
--     (Grade 11 - ABM-A and ABM-B both get the Grade 11 ABM list), with no
--     teacher yet. Subjects already in a section are left alone.
-- ============================================================

-- ── 1. STEM sections ───────────────────────────────────────
INSERT IGNORE INTO sections (name, level, sort_order) VALUES
('Grade 11 - STEM-A', 'SHS', 170), ('Grade 11 - STEM-B', 'SHS', 180),
('Grade 12 - STEM-A', 'SHS', 270), ('Grade 12 - STEM-B', 'SHS', 280);

-- ── 2. Courses ─────────────────────────────────────────────
-- Tidy existing ones (matched by code AND old name, so re-running is harmless)
UPDATE courses SET course_name = 'Oral Communication'                                     WHERE course_code = 'SHS-ORALCOM';
UPDATE courses SET course_name = 'Reading and Writing'                                    WHERE course_code = 'SHS-RECOM';
UPDATE courses SET course_name = 'Fundamentals of Accountancy, Business and Management'   WHERE course_code = 'ABM-FABM1';
UPDATE courses SET course_name = 'Fundamentals of Accountancy, Business and Management 2' WHERE course_code = 'ABM-FABM2';
UPDATE courses SET course_code = 'SHS-PR',          course_name = 'Practical Research'      WHERE course_code = 'GAS-RESEARCH';
UPDATE courses SET course_code = 'SHS-CREATIVEW',   course_name = 'Creative Writing'        WHERE course_code = 'HUMSS-CPAR';
UPDATE courses SET course_code = 'HUMSS-APPSOCSCI', course_name = 'Applied Social Sciences' WHERE course_code = 'GAS-SOCSCI' AND course_name = 'Applied Social Sciences';

DROP TEMPORARY TABLE IF EXISTS tmp_shs_subjects;
CREATE TEMPORARY TABLE tmp_shs_subjects (code VARCHAR(20), name VARCHAR(150))
  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT INTO tmp_shs_subjects VALUES
-- shared
('SHS-GENMATH',     'General Mathematics'),
('SHS-STATS',       'Statistics and Probability'),
('SHS-EARTH',       'Earth and Life Science'),
('SHS-PHYSSCI',     'Physical Science'),
('SHS-ORALCOM',     'Oral Communication'),
('SHS-RECOM',       'Reading and Writing'),
('SHS-EMPTECH',     'Empowerment Technologies'),
('SHS-APPECON',     'Applied Economics'),
('SHS-CREATIVEW',   'Creative Writing'),
('SHS-PR',          'Practical Research'),
('SHS-PE',          'Physical Education'),
('SHS-WORKIMM',     'Work Immersion'),
-- STEM
('STEM-PRECAL',     'Pre-Calculus'),
('STEM-BIO1',       'General Biology 1'),
('STEM-CHEM1',      'General Chemistry 1'),
('STEM-BIO2',       'General Biology 2'),
('STEM-CHEM2',      'General Chemistry 2'),
('STEM-PHYSICS',    'General Physics'),
('STEM-BASICCAL',   'Basic Calculus'),
-- ABM
('ABM-BUSMATH',     'Business Mathematics'),
('ABM-FABM1',       'Fundamentals of Accountancy, Business and Management'),
('ABM-FABM2',       'Fundamentals of Accountancy, Business and Management 2'),
('ABM-ORGMGT',      'Organization and Management'),
('ABM-MARKETING',   'Principles of Marketing'),
('ABM-BUSFIN',      'Business Finance'),
('ABM-BUSETHICS',   'Business Ethics'),
-- HUMSS
('HUMSS-INTRO',     'Introduction to World Religions'),
('HUMSS-DISAS',     'Disciplines and Ideas in Social Sciences'),
('HUMSS-PPG',       'Philippine Politics and Governance'),
('HUMSS-CNF',       'Creative Nonfiction'),
('HUMSS-TNCT',      'Trends, Networks and Critical Thinking'),
('HUMSS-APPSOCSCI', 'Applied Social Sciences'),
('HUMSS-CESC',      'Community Engagement'),
-- GAS
('GAS-HUMANITIES',  'Humanities'),
('GAS-SOCIALSCI',   'Social Sciences');

INSERT INTO courses (course_code, course_name, description, school_year, semester, status)
SELECT s.code, s.name, NULL, '2025-2026', '1st', 'Active'
FROM tmp_shs_subjects s
WHERE NOT EXISTS (SELECT 1 FROM courses c WHERE c.course_code = s.code);

UPDATE courses c JOIN tmp_shs_subjects s ON s.code = c.course_code SET c.status = 'Active';

-- ── 3. Curriculum: grade + strand → subjects (in display order) ──
DROP TEMPORARY TABLE IF EXISTS tmp_shs_curriculum;
CREATE TEMPORARY TABLE tmp_shs_curriculum (grade INT, strand VARCHAR(10), code VARCHAR(20), ord INT)
  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT INTO tmp_shs_curriculum VALUES
(11,'STEM','SHS-GENMATH',1),(11,'STEM','SHS-STATS',2),(11,'STEM','SHS-EARTH',3),(11,'STEM','SHS-PHYSSCI',4),
(11,'STEM','SHS-ORALCOM',5),(11,'STEM','SHS-RECOM',6),(11,'STEM','SHS-EMPTECH',7),(11,'STEM','STEM-PRECAL',8),
(11,'STEM','STEM-BIO1',9),(11,'STEM','STEM-CHEM1',10),

(11,'ABM','SHS-GENMATH',1),(11,'ABM','SHS-STATS',2),(11,'ABM','SHS-ORALCOM',3),(11,'ABM','SHS-RECOM',4),
(11,'ABM','SHS-EMPTECH',5),(11,'ABM','ABM-BUSMATH',6),(11,'ABM','ABM-FABM1',7),(11,'ABM','ABM-ORGMGT',8),
(11,'ABM','ABM-MARKETING',9),

(11,'HUMSS','SHS-GENMATH',1),(11,'HUMSS','SHS-STATS',2),(11,'HUMSS','SHS-ORALCOM',3),(11,'HUMSS','SHS-RECOM',4),
(11,'HUMSS','SHS-EMPTECH',5),(11,'HUMSS','SHS-CREATIVEW',6),(11,'HUMSS','HUMSS-INTRO',7),(11,'HUMSS','HUMSS-DISAS',8),
(11,'HUMSS','HUMSS-PPG',9),

(11,'GAS','SHS-GENMATH',1),(11,'GAS','SHS-STATS',2),(11,'GAS','SHS-ORALCOM',3),(11,'GAS','SHS-RECOM',4),
(11,'GAS','SHS-EMPTECH',5),(11,'GAS','GAS-HUMANITIES',6),(11,'GAS','GAS-SOCIALSCI',7),(11,'GAS','SHS-APPECON',8),

(12,'STEM','STEM-BIO2',1),(12,'STEM','STEM-CHEM2',2),(12,'STEM','STEM-PHYSICS',3),(12,'STEM','STEM-BASICCAL',4),
(12,'STEM','SHS-PR',5),(12,'STEM','SHS-PE',6),(12,'STEM','SHS-WORKIMM',7),

(12,'ABM','ABM-FABM2',1),(12,'ABM','SHS-APPECON',2),(12,'ABM','ABM-BUSFIN',3),(12,'ABM','ABM-BUSETHICS',4),
(12,'ABM','SHS-PR',5),(12,'ABM','SHS-PE',6),(12,'ABM','SHS-WORKIMM',7),

(12,'HUMSS','HUMSS-CNF',1),(12,'HUMSS','HUMSS-TNCT',2),(12,'HUMSS','HUMSS-APPSOCSCI',3),(12,'HUMSS','HUMSS-CESC',4),
(12,'HUMSS','SHS-PR',5),(12,'HUMSS','SHS-PE',6),(12,'HUMSS','SHS-WORKIMM',7),

(12,'GAS','SHS-APPECON',1),(12,'GAS','SHS-CREATIVEW',2),(12,'GAS','HUMSS-PPG',3),
(12,'GAS','SHS-PR',4),(12,'GAS','SHS-PE',5),(12,'GAS','SHS-WORKIMM',6);

-- Every section of that grade + strand: "Grade 11 - ABM-A", "Grade 11 - ABM-B", ...
SET @sy := COALESCE((SELECT MAX(school_year) FROM teacher_assignments), '2025-2026');

INSERT INTO teacher_assignments (teacher_id, course_id, section, school_year, semester)
SELECT NULL, c.id, sec.name, @sy, '1st'
FROM tmp_shs_curriculum cur
JOIN sections sec ON sec.name LIKE CONCAT('Grade ', cur.grade, ' - ', cur.strand, '-%')
JOIN courses c ON c.course_code = cur.code
WHERE NOT EXISTS (SELECT 1 FROM teacher_assignments ta WHERE ta.course_id = c.id AND ta.section = sec.name)
ORDER BY sec.sort_order, cur.ord;

-- ── Optional cleanup (remove the leading "-- " to run) ─────
-- Removes subjects that are NOT in the curriculum above from Grade 11/12
-- sections (e.g. 21st Century Literature or Personal Development that were
-- added to Grade 11 - HUMSS-A before). Students keep their enrollment records.
--
-- DELETE ta FROM teacher_assignments ta
-- JOIN courses c ON c.id = ta.course_id
-- JOIN sections sec ON sec.name = ta.section AND sec.level = 'SHS'
-- WHERE NOT EXISTS (
--   SELECT 1 FROM tmp_shs_curriculum cur
--   WHERE cur.code = c.course_code
--     AND sec.name LIKE CONCAT('Grade ', cur.grade, ' - ', cur.strand, '-%')
-- );

DROP TEMPORARY TABLE IF EXISTS tmp_shs_subjects;
DROP TEMPORARY TABLE IF EXISTS tmp_shs_curriculum;
