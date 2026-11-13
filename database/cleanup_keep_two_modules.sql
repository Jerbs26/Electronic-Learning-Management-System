-- ============================================================
--  Arandia College eLMS — Keep 2 modules per subject
--  File: database/cleanup_keep_two_modules.sql
--
--  Import AFTER seed_module_lessons.sql. BACK UP elmsdb FIRST.
--  Safe to run more than once.
--
--  1. Deletes the short starter lessons in Module 3, 4 and 5.
--     Only untouched starter lessons are removed (updated_by IS NULL);
--     a module an admin has edited is kept.
--  2. Renumbers what is left in each subject so there are no gaps
--     (e.g. old modules 6, 7, 8 become 3, 4, 5).
--  Students' Opened/Done records on deleted modules are removed with them.
-- ============================================================

DELETE FROM modules
WHERE module_number BETWEEN 3 AND 5
  AND updated_by IS NULL
  AND file_path IS NULL
  AND content LIKE '<p>%';

-- Renumber 1, 2, 3 ... per subject (via a temporary +1000 offset to avoid clashes)
UPDATE modules m
JOIN (
  SELECT id, ROW_NUMBER() OVER (PARTITION BY course_id ORDER BY module_number, id) AS n
  FROM modules
) x ON x.id = m.id
SET m.module_number = x.n + 1000;

UPDATE modules SET module_number = module_number - 1000 WHERE module_number > 1000;
