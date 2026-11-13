<?php
// ============================================================
//  Arandia College eLMS — Admin Panel
//  File: admin/dashboard.php
//  Stat cards · needs attention ·
//  recent accounts · online users · activity log (Head Admin)
// ============================================================
session_start();
require_once '../config/conn.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['Admin', 'Head Admin'], true)) {
  header('Location: ../login.php');
  exit;
}
require_once '../config/permissions.php';
require_once '../includes/sections.php';

$isHeadAdmin = isHeadAdmin();
$can = function (...$keys) use ($conn): bool {
  if (isHeadAdmin()) return true;
  foreach ($keys as $k) if (adminHasPermission($conn, $k)) return true;
  return false;
};

$first_name = $_SESSION['first_name'];
$last_name  = $_SESSION['last_name'];
$toast   = $_GET['toast'] ?? '';
$isError = $_GET['error'] ?? '';
[$shsSections, $hsSections] = loadSectionLists($conn);

function tableExists(mysqli $conn, string $t): bool
{
  $r = $conn->query("SHOW TABLES LIKE '" . $conn->real_escape_string($t) . "'");
  return $r && $r->num_rows > 0;
}
function columnExists(mysqli $conn, string $t, string $c): bool
{
  $r = $conn->query("SHOW COLUMNS FROM `$t` LIKE '" . $conn->real_escape_string($c) . "'");
  return $r && $r->num_rows > 0;
}
function scalar(mysqli $conn, string $sql): int
{
  try {
    $r = $conn->query($sql);
    return $r ? (int) ($r->fetch_row()[0] ?? 0) : 0;
  } catch (Throwable $e) {
    error_log('[dashboard] ' . $e->getMessage());
    return 0;
  }
}
function timeAgo(?string $dt): string
{
  if (!$dt) return '';
  $s = time() - strtotime($dt);
  if ($s < 60) return 'just now';
  if ($s < 3600) return floor($s / 60) . ' min ago';
  if ($s < 86400) return floor($s / 3600) . ' hr' . (floor($s / 3600) == 1 ? '' : 's') . ' ago';
  if ($s < 172800) return 'yesterday';
  if ($s < 604800) return floor($s / 86400) . ' days ago';
  return date('M j, Y', strtotime($dt));
}
function initials(string $first, string $last): string
{
  $sub = function_exists('mb_substr') ? 'mb_substr' : 'substr';
  return strtoupper($sub(trim($first), 0, 1) . $sub(trim($last), 0, 1)) ?: '?';
}

$hasModules  = tableExists($conn, 'modules');
$hasPresence = columnExists($conn, 'users', 'last_seen_at');

// ── Stat cards ───────────────────────────────────────────────
$roleCounts = ['Student' => 0, 'Teacher' => 0, 'Admin' => 0, 'Head Admin' => 0, 'Admission' => 0];
$res = $conn->query("SELECT role, COUNT(*) AS n FROM users WHERE status = 'Active' GROUP BY role");
while ($res && $r = $res->fetch_assoc()) $roleCounts[$r['role']] = (int) $r['n'];

$stats = [
  ['Students', $roleCounts['Student'], 'graduation', 'accounts.php'],
  ['Teachers', $roleCounts['Teacher'], 'presentation', 'accounts.php'],
  ['Admins',   $roleCounts['Admin'] + $roleCounts['Head Admin'] + $roleCounts['Admission'], 'shield', $isHeadAdmin ? 'admins.php' : null],   // staff: admins + admission
  ['Subjects', scalar($conn, "SELECT COUNT(*) FROM courses WHERE status = 'Active'"), 'book', 'subjects-enrollment.php'],
  ['Sections', count(fetchSections($conn)), 'users', 'subjects-enrollment.php'],
];
if ($hasModules) $stats[] = ['Modules', scalar($conn, "SELECT COUNT(*) FROM modules"), 'folder', $can('manage_modules') ? 'modules.php' : null];

// ── Needs attention (shared with the top-bar bell) ─────────
require_once '../includes/admin_attention.php';
$attention = adminAttentionItems($conn, $can);

// ── Recent accounts ──────────────────────────────────────────
$recent = [];
$res = $conn->query("SELECT first_name, last_name, role, created_at FROM users ORDER BY created_at DESC, id DESC LIMIT 6");
while ($res && $r = $res->fetch_assoc()) $recent[] = $r;

// ── Online users (seen in the last 5 minutes) ───────────────
$online = [];
$onlineCount = 0;
if ($hasPresence) {
  $cut = date('Y-m-d H:i:s', time() - 300);
  $st = $conn->prepare("SELECT first_name, last_name, role, section_dept, last_seen_at FROM users
                        WHERE last_seen_at >= ? AND status = 'Active' ORDER BY last_seen_at DESC LIMIT 8");
  $st->bind_param('s', $cut);
  $st->execute();
  $res = $st->get_result();
  while ($r = $res->fetch_assoc()) $online[] = $r;
  $st->close();
  $st = $conn->prepare("SELECT COUNT(*) FROM users WHERE last_seen_at >= ? AND status = 'Active'");
  $st->bind_param('s', $cut);
  $st->execute();
  $onlineCount = (int) $st->get_result()->fetch_row()[0];
  $st->close();
}

// ── Activity log (Head Admin only, like the log page) ───────
$log = [];
if ($isHeadAdmin && tableExists($conn, 'admin_activity_log')) {
  $res = $conn->query(
    "SELECT l.action, l.details, l.created_at, u.first_name, u.last_name
     FROM admin_activity_log l LEFT JOIN users u ON u.id = l.admin_id
     ORDER BY l.id DESC LIMIT 6"
  );
  while ($res && $r = $res->fetch_assoc()) $log[] = $r;
}

$hour = (int) date('G');
$greeting = $hour < 12 ? 'Good morning' : ($hour < 18 ? 'Good afternoon' : 'Good evening');
$canCreate = $can('manage_students', 'manage_teachers');
$roleClass = ['Student' => 'stu', 'Teacher' => 'tch', 'Admin' => 'adm', 'Head Admin' => 'adm', 'Admission' => 'adm'];

$panel = 'dashboard';
$currentPage = 'dashboard.php';
$pageTitle = 'Dashboard';
require __DIR__ . '/../includes/header.php';
?>
<style>
  .db-head { display: flex; align-items: flex-end; justify-content: space-between; gap: 1rem; flex-wrap: wrap; margin-bottom: 1.25rem; }
  .db-head .page-title { margin: 0; }
  .db-sub { font-size: .86rem; color: var(--text-secondary); margin-top: .3rem; }
  .db-grid { display: grid; grid-template-columns: minmax(0, 1fr) 320px; gap: 1rem; align-items: start; }
  .db-col { display: flex; flex-direction: column; gap: 1rem; min-width: 0; }
  .db-card { background: var(--surface); border: 1px solid var(--border); border-radius: 16px; padding: 1.1rem 1.25rem; }
  .db-card-h { display: flex; justify-content: space-between; align-items: center; gap: .75rem; margin-bottom: .85rem; }
  .db-card-t { font-size: .95rem; font-weight: 700; color: var(--text-primary); }
  .db-link { font-size: .8rem; font-weight: 600; color: var(--primary); text-decoration: none; }
  .db-link:hover { text-decoration: underline; }
  .db-stats { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 0; padding: .4rem .5rem; }
  .db-stat { display: flex; align-items: center; gap: .8rem; padding: .85rem .8rem; border-radius: 12px; text-decoration: none; color: inherit; }
  a.db-stat:hover { background: var(--surface-hover); }
  .db-ico { width: 40px; height: 40px; border-radius: 50%; background: var(--primary-light); color: var(--primary); display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
  .db-ico .ico { width: 18px; height: 18px; }
  .db-stat-l { font-size: .78rem; color: var(--text-secondary); }
  .db-stat-v { font-size: 1.35rem; font-weight: 800; color: var(--text-primary); line-height: 1.2; }
  .db-two { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 1rem; }
  .db-item { display: flex; align-items: center; gap: .7rem; padding: .7rem 0; border-top: 1px solid var(--border); text-decoration: none; color: inherit; }
  .db-item:first-of-type { border-top: 0; padding-top: .2rem; }
  a.db-item:hover .db-item-t { color: var(--primary); }
  .db-item-t { font-size: .86rem; font-weight: 600; color: var(--text-primary); }
  .db-item-s { font-size: .76rem; color: var(--text-secondary); margin-top: .1rem; }
  .db-dot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; background: var(--text-muted); }
  .db-dot.warn { background: var(--warning); }
  .db-av { width: 34px; height: 34px; border-radius: 50%; background: var(--primary-light); color: var(--primary); font-size: .72rem; font-weight: 800; display: flex; align-items: center; justify-content: center; flex-shrink: 0; position: relative; }
  .db-av.on::after { content: ''; position: absolute; right: -1px; bottom: -1px; width: 10px; height: 10px; border-radius: 50%; background: var(--success); border: 2px solid var(--surface); }
  .db-role { font-size: .68rem; font-weight: 700; border-radius: 100px; padding: .15rem .55rem; margin-left: auto; white-space: nowrap; }
  .db-role.stu { background: var(--success-light); color: var(--success); }
  .db-role.tch { background: var(--primary-light); color: var(--primary); }
  .db-role.adm { background: var(--border-muted); color: var(--text-secondary); }
  .db-count { font-size: .72rem; font-weight: 700; background: var(--success-light); color: var(--success); border-radius: 100px; padding: .15rem .6rem; }
  .db-none { font-size: .84rem; color: var(--text-muted); padding: .5rem 0; }
  .db-sub-h { font-size: .74rem; font-weight: 700; color: var(--text-muted); margin: .2rem 0 .3rem; }
  .db-sep { border: 0; border-top: 1px solid var(--border); margin: .9rem 0; }
  @media (max-width: 1150px) { .db-grid { grid-template-columns: minmax(0, 1fr); } }
  @media (max-width: 760px) { .db-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); } .db-two { grid-template-columns: minmax(0, 1fr); } }
</style>

<div class="section-panel active">
  <div class="db-head">
    <div>
      <div class="page-title">Dashboard</div>
    </div>
    <?php if ($canCreate): ?>
      <a class="btn btn-primary" href="accounts.php?tab=create"><?= a_icon('plus') ?> Create account</a>
    <?php endif; ?>
  </div>

  <div class="db-grid">
    <div class="db-col">
      <div class="db-card db-stats">
        <?php foreach ($stats as [$label, $value, $icon, $href]): ?>
          <<?= $href ? 'a href="' . $href . '"' : 'div' ?> class="db-stat">
            <div class="db-ico"><?= a_icon($icon) ?></div>
            <div><div class="db-stat-l"><?= $label ?></div><div class="db-stat-v"><?= number_format($value) ?></div></div>
          </<?= $href ? 'a' : 'div' ?>>
        <?php endforeach; ?>
      </div>

      <div class="<?= $log ? 'db-two' : '' ?>">
        <div class="db-card">
          <div class="db-card-h"><div class="db-card-t">Needs attention</div></div>
          <?php if (!$attention): ?>
            <div class="db-none">All caught up. Nothing needs your attention right now.</div>
          <?php else: foreach ($attention as [$title, $sub, $href, $tone]): ?>
            <a class="db-item" href="<?= $href ?>">
              <span class="db-dot <?= $tone ?>"></span>
              <div><div class="db-item-t"><?= htmlspecialchars($title) ?></div><div class="db-item-s"><?= htmlspecialchars($sub) ?></div></div>
            </a>
          <?php endforeach; endif; ?>
        </div>

        <?php if ($log): ?>
          <div class="db-card">
            <div class="db-card-h"><div class="db-card-t">Activity log</div><a class="db-link" href="admins.php?tab=log">See all</a></div>
            <?php foreach ($log as $l): ?>
              <div class="db-item">
                <div class="db-av"><?= htmlspecialchars(initials($l['first_name'] ?? '', $l['last_name'] ?? '')) ?></div>
                <div style="min-width:0;">
                  <div class="db-item-t"><?= htmlspecialchars(ucfirst(str_replace('_', ' ', $l['action']))) ?></div>
                  <div class="db-item-s" style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= htmlspecialchars(trim(($l['first_name'] ?? 'Someone') . ' · ' . timeAgo($l['created_at']))) ?></div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <?php $canSeePeople = $can('view_students', 'view_teachers', 'manage_admin_accounts'); ?>
    <div class="db-card">
      <div class="db-card-h">
        <div class="db-card-t">Users</div>
        <?php if ($can('view_students', 'view_teachers', 'manage_admin_accounts')): ?><a class="db-link" href="accounts.php">See all</a><?php endif; ?>
      </div>

      <div class="db-card-h" style="margin-bottom:.3rem;">
        <div class="db-sub-h" style="margin:0;">Online now</div>
        <?php if ($hasPresence): ?><span class="db-count"><?= $onlineCount ?> online</span><?php endif; ?>
      </div>
      <?php if (!$hasPresence): ?>
        <div class="db-none">Import <strong>database/migration_presence.sql</strong> to see who is online.</div>
      <?php elseif (!$canSeePeople): ?>
        <div class="db-none">You need an account-viewing permission to see who they are.</div>
      <?php elseif (!$online): ?>
        <div class="db-none">No one is online right now.</div>
      <?php else: foreach ($online as $u): ?>
        <div class="db-item">
          <div class="db-av on"><?= htmlspecialchars(initials($u['first_name'], $u['last_name'])) ?></div>
          <div style="min-width:0;">
            <div class="db-item-t"><?= htmlspecialchars($u['first_name'] . ' ' . $u['last_name']) ?></div>
            <div class="db-item-s"><?= htmlspecialchars($u['role'] === 'Student' && $u['section_dept'] ? $u['section_dept'] : 'Active ' . timeAgo($u['last_seen_at'])) ?></div>
          </div>
          <span class="db-role <?= $roleClass[$u['role']] ?? 'adm' ?>"><?= htmlspecialchars($u['role']) ?></span>
        </div>
      <?php endforeach; endif; ?>

      <?php if ($canSeePeople): ?>
      <hr class="db-sep">
      <div class="db-sub-h">Newest accounts</div>
      <?php foreach ($recent as $u): ?>
        <div class="db-item">
          <div class="db-av"><?= htmlspecialchars(initials($u['first_name'], $u['last_name'])) ?></div>
          <div style="min-width:0;">
            <div class="db-item-t"><?= htmlspecialchars($u['first_name'] . ' ' . $u['last_name']) ?></div>
            <div class="db-item-s">Joined <?= htmlspecialchars(timeAgo($u['created_at'])) ?></div>
          </div>
          <span class="db-role <?= $roleClass[$u['role']] ?? 'adm' ?>"><?= htmlspecialchars($u['role']) ?></span>
        </div>
      <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
