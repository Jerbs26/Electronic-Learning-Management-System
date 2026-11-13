-- ============================================================
--  Arandia College eLMS — Remove attendance recorded for future dates
--  File: database/cleanup_future_attendance.sql
--  The old Attendance page allowed picking any date, so some records
--  were saved for days that haven't happened yet. The new page blocks
--  this. Check first, then delete.
-- ============================================================

-- 1) See what would be removed
SELECT a.date, c.course_code, COUNT(*) AS records
FROM attendance a JOIN courses c ON c.id = a.course_id
WHERE a.date > CURDATE()
GROUP BY a.date, c.course_code
ORDER BY a.date;

-- 2) Remove them
DELETE FROM attendance WHERE date > CURDATE();
