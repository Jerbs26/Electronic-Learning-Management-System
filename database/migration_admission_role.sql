-- ============================================================
--  Arandia College eLMS — Admission role
--  File: database/migration_admission_role.sql
--
--  Adds the "Admission" account type. Admission accounts can ONLY
--  enroll students (create Student accounts with a section — the
--  student is enrolled in that section's subjects automatically —
--  and view the student list). They sign in like everyone else
--  (email + password + emailed code) and land on admission/dashboard.php.
--
--  Only the Head Admin creates / edits / deactivates Admission
--  accounts: Accounts → Create account → Admission, or
--  Admins and Logs → New Admission Account.
--
--  Safe to run on an existing elmsdb (keeps every row). Running it
--  twice does no harm.
-- ============================================================

ALTER TABLE `users`
  MODIFY `role` ENUM('Head Admin','Admin','Admission','Teacher','Student') NOT NULL DEFAULT 'Student';
