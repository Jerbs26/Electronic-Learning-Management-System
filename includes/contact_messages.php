<?php
// ============================================================
//  Arandia College eLMS — Contact Us messages
//  File: includes/contact_messages.php
//  Shared by api/contact.php (public form), api/contact_messages.php
//  (admin actions), admin/messages.php and the admin notification bell.
// ============================================================

if (!function_exists('ensureContactMessagesTable')) {
  /** Creates the table the first time it is needed (same as database/migration_contact_messages.sql). */
  function ensureContactMessagesTable(mysqli $conn): bool
  {
    static $ok = null;
    if ($ok !== null) return $ok;
    try {
      $ok = (bool) $conn->query(
        "CREATE TABLE IF NOT EXISTS contact_messages (
           id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
           first_name  VARCHAR(80)  NOT NULL,
           last_name   VARCHAR(80)  NOT NULL,
           email       VARCHAR(190) NOT NULL,
           role        VARCHAR(60)  DEFAULT NULL,
           subject     VARCHAR(200) NOT NULL,
           message     TEXT         NOT NULL,
           status      ENUM('New','Read') NOT NULL DEFAULT 'New',
           ip_address  VARCHAR(45)  DEFAULT NULL,
           read_by     INT UNSIGNED DEFAULT NULL,
           read_at     DATETIME     DEFAULT NULL,
           created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
           PRIMARY KEY (id),
           KEY idx_status (status),
           KEY idx_created (created_at),
           KEY idx_ip_created (ip_address, created_at)
         ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
      );
    } catch (Throwable $e) {
      error_log('[contact_messages] ' . $e->getMessage());
      $ok = false;
    }
    return $ok;
  }

  /** Number of messages no admin has opened yet. */
  function countNewContactMessages(mysqli $conn): int
  {
    if (!ensureContactMessagesTable($conn)) return 0;
    try {
      $r = $conn->query("SELECT COUNT(*) FROM contact_messages WHERE status = 'New'");
      return $r ? (int) ($r->fetch_row()[0] ?? 0) : 0;
    } catch (Throwable $e) {
      return 0;
    }
  }

  /**
   * Tell every open admin page (api/sync.php → includes/live.php) that the inbox changed,
   * so the Messages list and the sidebar badge update without a reload.
   * $kind: 'message.new' (a visitor sent a message) | 'message.changed' (read / unread / reply / delete).
   * Never throws: if sync_events is missing the pages simply update on their next load.
   */
  function pushContactSync(mysqli $conn, string $kind, int $refId = 0, string $title = '', ?int $actorId = null): void
  {
    try {
      $title = mb_substr($title, 0, 255);
      $ref   = $refId > 0 ? $refId : null;
      $st = $conn->prepare("INSERT INTO sync_events (kind, user_id, ref_id, audience, title) VALUES (?, ?, ?, 'admins', ?)");
      if (!$st) return;
      $st->bind_param('siis', $kind, $actorId, $ref, $title);
      $st->execute();
      $st->close();
    } catch (Throwable $e) {
      error_log('[contact_messages sync] ' . $e->getMessage());
    }
  }

  /** The inbox rows (newest unread first), same shape the Messages page and its live refresh use. */
  function contactMessagesList(mysqli $conn, int $limit = 300): array
  {
    $rows = [];
    if (!ensureContactMessagesTable($conn)) return $rows;
    // "Replied" info (added the first time; if it cannot be added the list still works)
    $repCol = 'NULL AS replied_at';
    try {
      $c = $conn->query("SHOW COLUMNS FROM contact_messages LIKE 'replied_at'");
      if ($c && $c->num_rows === 0) {
        $conn->query("ALTER TABLE contact_messages ADD COLUMN replied_by INT UNSIGNED NULL DEFAULT NULL, ADD COLUMN replied_at DATETIME NULL DEFAULT NULL");
        $c = $conn->query("SHOW COLUMNS FROM contact_messages LIKE 'replied_at'");
      }
      if ($c && $c->num_rows > 0) $repCol = "DATE_FORMAT(m.replied_at, '%b %d, %Y %h:%i %p') AS replied_at";
    } catch (Throwable $e) {
      error_log('[contact_messages replied] ' . $e->getMessage());
    }
    $limit = max(1, min(1000, $limit));
    try {
      $res = $conn->query(
        "SELECT m.id, m.first_name, m.last_name, m.email, m.role, m.subject, m.message, m.status,
                DATE_FORMAT(m.created_at, '%b %d, %Y %h:%i %p') AS sent_at,
                DATE_FORMAT(m.read_at, '%b %d, %Y %h:%i %p') AS read_at,
                " . $repCol . ",
                TRIM(CONCAT(COALESCE(u.first_name, ''), ' ', COALESCE(u.last_name, ''))) AS read_by_name
         FROM contact_messages m
         LEFT JOIN users u ON u.id = m.read_by
         ORDER BY (m.status = 'New') DESC, m.created_at DESC
         LIMIT " . $limit
      );
      while ($res && $r = $res->fetch_assoc()) $rows[] = $r;
    } catch (Throwable $e) {
      error_log('[contact_messages list] ' . $e->getMessage());
    }
    return $rows;
  }
}
