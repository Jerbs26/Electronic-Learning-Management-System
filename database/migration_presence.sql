-- ============================================================
--  Arandia College eLMS — Online users
--  File: database/migration_presence.sql
--  Adds users.last_seen_at. The app updates it at most once a minute
--  while a user is logged in and active; "online" = seen in the last
--  5 minutes. Run once.
-- ============================================================
ALTER TABLE `users`
  ADD COLUMN `last_seen_at` datetime NULL DEFAULT NULL AFTER `created_at`,
  ADD KEY `idx_users_last_seen` (`last_seen_at`);
