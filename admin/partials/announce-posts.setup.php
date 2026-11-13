<?php
// ============================================================
//  Arandia College eLMS — Admin Panel
//  File: admin.php  |  Target: SHS & HS
// ============================================================
if (session_status() === PHP_SESSION_NONE) session_start();
require_once '../config/conn.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['Admin', 'Head Admin'], true)) {
  header('Location: ../login.php');
  exit;
}
require_once '../config/permissions.php';
$isHeadAdmin = isHeadAdmin();
$canPublish = $isHeadAdmin || adminHasPermission($conn, 'publish_announcements');

$first_name = $_SESSION['first_name'];
$last_name = $_SESSION['last_name'];

$toast = $_GET['toast'] ?? '';
$isError = $_GET['error'] ?? '';

// ── Announcements ─────────────────────────────────────────────────────────
$allAnnouncements = [];
$annRes = $conn->query(
  "SELECT a.id, a.title, a.body, a.course_id, a.target_audience, a.author_id, u.role AS author_role,
          DATE_FORMAT(a.posted_at,'%b %d, %Y %h:%i %p') AS posted_at,
          CONCAT(u.first_name,' ',u.last_name) AS author,
          c.course_code, c.course_name
   FROM announcements a
   JOIN users u ON u.id = a.author_id
   LEFT JOIN courses c ON c.id = a.course_id
   ORDER BY a.posted_at DESC
   LIMIT 500"
);
while ($r = $annRes->fetch_assoc())
  $allAnnouncements[] = $r;

$allCourses = [];
$res = $conn->query("SELECT id, course_code, course_name FROM courses WHERE status='Active' ORDER BY course_code");
while ($r = $res->fetch_assoc())
  $allCourses[] = $r;

require_once __DIR__ . '/../../includes/sections.php';
[$shsSections, $hsSections] = loadSectionLists($conn);
$panel = 'announcements';