-- ============================================================
--  Arandia College eLMS — Junior High School subjects (Grade 7-10)
--  File: database/seed_hs_subjects.sql
--
--  Import AFTER database/migration_sections_subjects.sql.
--  Safe to run more than once: it never creates duplicates.
--
--  1. Makes sure every grade has the 8 core subjects, named
--     "<Subject> <Grade>" (e.g. Filipino 10), code HS<Grade>-<CODE>.
--  2. Adds those 8 subjects to the matching section (Grade 7 → Grade 7
--     section, etc.) with no teacher yet, so the Subjects page shows them
--     as "Needs a teacher". Subjects already in a section are left alone.
-- ============================================================

-- The 8 subjects, in display order
DROP TEMPORARY TABLE IF EXISTS tmp_hs_subjects;
CREATE TEMPORARY TABLE tmp_hs_subjects (code VARCHAR(10), name VARCHAR(60), ord INT)
  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT INTO tmp_hs_subjects VALUES
('FIL',     'Filipino',           1),
('ENGLISH', 'English',            2),
('MATH',    'Mathematics',        3),
('SCI',     'Science',            4),
('AP',      'Araling Panlipunan', 5),
('ESP',     'EsP',                6),
('MAPEH',   'MAPEH',              7),
('TLE',     'TLE',                8);

DROP TEMPORARY TABLE IF EXISTS tmp_hs_grades;
CREATE TEMPORARY TABLE tmp_hs_grades (grade INT);
INSERT INTO tmp_hs_grades VALUES (7), (8), (9), (10);

-- ── 1. Courses ─────────────────────────────────────────────
-- Existing ones (e.g. HS10-FIL) keep their id; only the name is normalised
UPDATE courses c
JOIN tmp_hs_grades g
JOIN tmp_hs_subjects s ON c.course_code = CONCAT('HS', g.grade, '-', s.code)
SET c.course_name = CONCAT(s.name, ' ', g.grade), c.status = 'Active';

INSERT INTO courses (course_code, course_name, description, school_year, semester, status)
SELECT CONCAT('HS', g.grade, '-', s.code), CONCAT(s.name, ' ', g.grade), NULL, '2025-2026', '1st', 'Active'
FROM tmp_hs_grades g
CROSS JOIN tmp_hs_subjects s
WHERE NOT EXISTS (SELECT 1 FROM courses c WHERE c.course_code = CONCAT('HS', g.grade, '-', s.code))
ORDER BY g.grade, s.ord;

-- ── 2. Put them in the matching Grade 7-10 sections ────────
SET @sy := COALESCE((SELECT MAX(school_year) FROM teacher_assignments), '2025-2026');

INSERT INTO teacher_assignments (teacher_id, course_id, section, school_year, semester)
SELECT NULL, c.id, sec.name, @sy, '1st'
FROM sections sec
JOIN tmp_hs_grades g ON sec.name = CONCAT('Grade ', g.grade)
JOIN tmp_hs_subjects s
JOIN courses c ON c.course_code = CONCAT('HS', g.grade, '-', s.code)
WHERE NOT EXISTS (
  SELECT 1 FROM teacher_assignments ta WHERE ta.course_id = c.id AND ta.section = sec.name
)
ORDER BY g.grade, s.ord;

DROP TEMPORARY TABLE IF EXISTS tmp_hs_subjects;
DROP TEMPORARY TABLE IF EXISTS tmp_hs_grades;

-- ── Optional cleanup (remove the leading "-- " to run) ─────
-- Your data has subjects from another grade inside Grade 7-10 sections
-- (e.g. English 8 and Filipino 8 inside Grade 10, SHS subjects inside Grade 8).
-- This removes any HS<other grade> or SHS subject from the Grade 7-10 sections.
-- Students keep their enrollment records.
--
-- DELETE ta FROM teacher_assignments ta
-- JOIN courses c ON c.id = ta.course_id
-- WHERE ta.section IN ('Grade 7', 'Grade 8', 'Grade 9', 'Grade 10')
--   AND c.course_code NOT LIKE CONCAT('HS', SUBSTRING(ta.section, 7), '-%');
