<?php
// ============================================================
//  Arandia College eLMS — Tabbed admin page engine
//  File: admin/partials/_tabbed_page.php
//
//  A wrapper page (accounts.php, subjects-enrollment.php, ...) sets:
//    $ADMIN_PAGE_FILE  — its own filename, used for sidebar highlight + tab links
//    $ADMIN_PAGE_TITLE — title shown above the tabs
//    $ADMIN_TABS       — [key => ['label', 'partial', 'perms']]
//        perms: null   = any admin
//               'head' = Head Admin only
//               [...]  = any one of these permission keys
//  then requires this file. Only the selected tab's partial runs,
//  so each tab keeps its own server-side permission guard.
// ============================================================

if (session_status() === PHP_SESSION_NONE) session_start();
require_once '../config/conn.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['Admin', 'Head Admin'], true)) {
  header('Location: ../login.php');
  exit;
}
require_once '../config/permissions.php';
require_once __DIR__ . '/../../includes/schema_guard.php';
elmsEnsureRoles($conn);   // Admission role + repair of accounts saved with an empty role

function adminTabAllowed(mysqli $conn, $perms): bool
{
  if (isHeadAdmin()) return true;
  if ($perms === null) return true;
  if ($perms === 'head') return false;
  foreach ((array) $perms as $p) {
    if (adminHasPermission($conn, $p)) return true;
  }
  return false;
}

$ADMIN_VISIBLE_TABS = [];
foreach ($ADMIN_TABS as $key => $def) {
  if (adminTabAllowed($conn, $def['perms'])) $ADMIN_VISIBLE_TABS[$key] = $def;
}
if (!$ADMIN_VISIBLE_TABS) {
  header('Location: dashboard.php?toast=' . urlencode('Access denied — you do not have permission to view this page.') . '&error=1');
  exit;
}

$ADMIN_TAB = $_GET['tab'] ?? '';
if (!isset($ADMIN_VISIBLE_TABS[$ADMIN_TAB])) {
  // A tab that does not exist (or is not allowed for this admin) in the address
  // bar: open the plain page instead of silently showing another tab under that URL.
  if (isset($_GET['tab']) && ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'GET' && !headers_sent()) {
    header('Location: ' . $ADMIN_PAGE_FILE, true, 302);
    exit;
  }
  $ADMIN_TAB = array_key_first($ADMIN_VISIBLE_TABS);
}

/** Prints the page title + tab bar. Called by each tab body in place of its old page-header. */
function render_admin_tabs(): void
{
  global $ADMIN_PAGE_FILE, $ADMIN_PAGE_TITLE, $ADMIN_VISIBLE_TABS, $ADMIN_TAB;
  ?>
  <div class="page-header adm-page-header">
    <div class="page-title"><?= htmlspecialchars($ADMIN_PAGE_TITLE) ?></div>
  </div>
  <?php if (count($ADMIN_VISIBLE_TABS) > 1): ?>
    <nav class="adm-tabs" aria-label="<?= htmlspecialchars($ADMIN_PAGE_TITLE) ?> sections">
      <?php foreach ($ADMIN_VISIBLE_TABS as $key => $def): ?>
        <a class="adm-tab<?= $key === $ADMIN_TAB ? ' on' : '' ?>"
           href="<?= htmlspecialchars($ADMIN_PAGE_FILE . '?tab=' . urlencode($key)) ?>"
           <?= $key === $ADMIN_TAB ? 'aria-current="page"' : '' ?>><?= htmlspecialchars($def['label']) ?></a>
      <?php endforeach; ?>
    </nav>
  <?php endif;
}

// Toast message passed in the URL (read by includes/footer.php)
$toast   = $_GET['toast'] ?? '';
$isError = $_GET['error'] ?? '';

// Tab-specific setup (its own guard, queries, $panel for footer.php)
require __DIR__ . '/' . $ADMIN_VISIBLE_TABS[$ADMIN_TAB]['partial'] . '.setup.php';

// The wrapper owns the sidebar highlight and the browser/topbar title
$currentPage = $ADMIN_PAGE_FILE;
$pageTitle   = $ADMIN_PAGE_TITLE;

require __DIR__ . '/../../includes/header.php';
?>
<style>
  .adm-page-header { margin-bottom: .5rem; }
  .adm-tabs { display: flex; gap: .25rem; border-bottom: 1px solid var(--border); margin-bottom: 1.5rem; overflow-x: auto; overflow-y: hidden; scrollbar-width: none; }
  .adm-tabs::-webkit-scrollbar { display: none; }
  .adm-tab { padding: .65rem 1.1rem; font-size: .86rem; font-weight: 600; color: var(--text-secondary); text-decoration: none;
             border-bottom: 2px solid transparent; margin-bottom: -1px; white-space: nowrap; transition: color .15s, border-color .15s; }
  .adm-tab:hover { color: var(--text-primary); }
  .adm-tab.on { color: var(--primary); border-bottom-color: var(--primary); }
  .adm-tab:focus-visible { outline: 2px solid var(--primary); outline-offset: 2px; border-radius: 4px; }
</style>
<?php
require __DIR__ . '/' . $ADMIN_VISIBLE_TABS[$ADMIN_TAB]['partial'] . '.body.php';
require __DIR__ . '/../../includes/footer.php';