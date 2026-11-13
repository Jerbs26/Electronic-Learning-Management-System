<?php
// ============================================================
//  Arandia College eLMS — Dashboard Banners API
//  File: api/banners.php
// ============================================================

session_start();
require_once '../config/conn.php';
require_once '../config/permissions.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['Admin', 'Head Admin'], true)) {
    http_response_code(403);
    http_response_code(empty($_SESSION['user_id']) ? 401 : 403); echo json_encode(['success' => false, 'message' => empty($_SESSION['user_id']) ? 'Your session has ended. Please sign in again.' : 'You are not allowed to do this.']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';
$actorId = (int) $_SESSION['user_id'];

// Every mutating action requires the manage_banners permission (Head Admin bypasses).
if (in_array($action, ['create', 'update', 'delete', 'move'], true)) {
    requireApiPermission($conn, 'manage_banners');
} else {
    requireApiPermission($conn, ['manage_banners', 'publish_announcements']);
}

// ── GET: list all banners (management view — includes Inactive) ─────────
if ($method === 'GET' && $action === 'list') {
    $rows = [];
    $res = $conn->query("SELECT id, image_path, title, caption, link_url, sort_order, status FROM dashboard_banners ORDER BY sort_order ASC, id ASC");
    if ($res) {
        while ($r = $res->fetch_assoc()) $rows[] = $r;
    }
    echo json_encode(['success' => true, 'data' => $rows]);
    exit;
}

// ── POST: create a new banner slide ───────────────────────────────────
if ($method === 'POST' && $action === 'create') {
    if (empty($_FILES['image']['name'])) {
        echo json_encode(['success' => false, 'message' => 'An image is required.']);
        exit;
    }
    $upload_dir = '../uploads/banners/';
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0775, true);
    $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
    if (!in_array($ext, $allowed)) {
        echo json_encode(['success' => false, 'message' => 'Image must be JPG, PNG, WEBP, or GIF.']);
        exit;
    }
    if ($_FILES['image']['size'] > 5 * 1024 * 1024) {
        echo json_encode(['success' => false, 'message' => 'Image must be 5MB or smaller.']);
        exit;
    }
    $filename = uniqid('banner_') . '.' . $ext;
    $dest = $upload_dir . $filename;
    if (!move_uploaded_file($_FILES['image']['tmp_name'], $dest)) {
        echo json_encode(['success' => false, 'message' => 'Image upload failed.']);
        exit;
    }
    $image_path = 'uploads/banners/' . $filename;

    $title = trim($_POST['title'] ?? '') ?: null;
    $caption = trim($_POST['caption'] ?? '') ?: null;
    $link_url = trim($_POST['link_url'] ?? '') ?: null;
    // Shown as a link on every student dashboard: no javascript: or other schemes
    if ($link_url !== null && !preg_match('#^(https?://|/|\.\./|[a-z0-9_-]+\.php)#i', $link_url)) {
        echo json_encode(['success' => false, 'message' => 'The link must start with http://, https:// or point to a page in the eLMS.']); exit;
    }
    $status = in_array($_POST['status'] ?? '', ['Active', 'Inactive']) ? $_POST['status'] : 'Active';

    $maxRes = $conn->query("SELECT COALESCE(MAX(sort_order),0) + 10 AS next_order FROM dashboard_banners");
    $sort_order = (int) ($maxRes->fetch_assoc()['next_order'] ?? 10);

    $stmt = $conn->prepare(
        "INSERT INTO dashboard_banners (image_path, title, caption, link_url, sort_order, status, created_by)
         VALUES (?, ?, ?, ?, ?, ?, ?)"
    );
    $stmt->bind_param('ssssisi', $image_path, $title, $caption, $link_url, $sort_order, $status, $actorId);
    if ($stmt->execute()) {
        $newId = $conn->insert_id;
        $stmt->close();
        logAdminActivity($conn, $actorId, 'create_banner', 'banner', $newId, $title ? "Added banner '$title'." : 'Added a banner slide.');
        echo json_encode(['success' => true, 'message' => 'Banner added.', 'id' => $newId]);
    } else {
        error_log('[banners.php] ' . $conn->error); echo json_encode(['success' => false, 'message' => 'Could not save. Please try again.']);
    }
    exit;
}

// ── POST: update a banner's text/status (no re-upload) ───────────────
if ($method === 'POST' && $action === 'update') {
    $data = json_decode(file_get_contents('php://input'), true);
    $id = (int) ($data['id'] ?? 0);
    if (!$id) { echo json_encode(['success' => false, 'message' => 'Invalid ID.']); exit; }

    // Only change the fields that were sent. The Show/Hide button sends just
    // {id, status}; before, that wiped the banner's title, caption and link.
    $cur = $conn->query("SELECT title, caption, link_url, status FROM dashboard_banners WHERE id = $id")->fetch_assoc();
    if (!$cur) { echo json_encode(['success' => false, 'message' => 'Banner not found.']); exit; }
    $pick = fn(string $k) => array_key_exists($k, $data) ? (trim((string) $data[$k]) ?: null) : $cur[$k];
    $title = $pick('title');
    $caption = $pick('caption');
    $link_url = $pick('link_url');
    if ($link_url !== null && !preg_match('#^(https?://|/|\.\./|[a-z0-9_-]+\.php)#i', $link_url)) {
        echo json_encode(['success' => false, 'message' => 'The link must start with http://, https:// or point to a page in the eLMS.']); exit;
    }
    $status = in_array($data['status'] ?? '', ['Active', 'Inactive'], true) ? $data['status'] : $cur['status'];

    $stmt = $conn->prepare("UPDATE dashboard_banners SET title=?, caption=?, link_url=?, status=? WHERE id=?");
    $stmt->bind_param('ssssi', $title, $caption, $link_url, $status, $id);
    if ($stmt->execute()) {
        $stmt->close();
        logAdminActivity($conn, $actorId, 'update_banner', 'banner', $id, null);
        echo json_encode(['success' => true, 'message' => 'Banner updated.']);
    } else {
        error_log('[banners.php] ' . $conn->error); echo json_encode(['success' => false, 'message' => 'Could not save. Please try again.']);
    }
    exit;
}

// ── POST: move a banner up/down (swap sort_order with its neighbor) ──
if ($method === 'POST' && $action === 'move') {
    $data = json_decode(file_get_contents('php://input'), true);
    $id = (int) ($data['id'] ?? 0);
    $dir = $data['direction'] ?? '';
    if (!$id || !in_array($dir, ['up', 'down'], true)) {
        echo json_encode(['success' => false, 'message' => 'Invalid request.']);
        exit;
    }

    $curRes = $conn->prepare("SELECT sort_order FROM dashboard_banners WHERE id = ?");
    $curRes->bind_param('i', $id);
    $curRes->execute();
    $cur = $curRes->get_result()->fetch_assoc();
    $curRes->close();
    if (!$cur) { echo json_encode(['success' => false, 'message' => 'Banner not found.']); exit; }

    $op = $dir === 'up' ? '<' : '>';
    $ord = $dir === 'up' ? 'DESC' : 'ASC';
    $neighborStmt = $conn->prepare("SELECT id, sort_order FROM dashboard_banners WHERE sort_order $op ? ORDER BY sort_order $ord LIMIT 1");
    $neighborStmt->bind_param('i', $cur['sort_order']);
    $neighborStmt->execute();
    $neighbor = $neighborStmt->get_result()->fetch_assoc();
    $neighborStmt->close();

    if (!$neighbor) {
        echo json_encode(['success' => true, 'message' => 'Already at the edge.']);
        exit;
    }

    $swap = $conn->prepare("UPDATE dashboard_banners SET sort_order = ? WHERE id = ?");
    $swap->bind_param('ii', $neighbor['sort_order'], $id);
    $swap->execute();
    $swap->bind_param('ii', $cur['sort_order'], $neighbor['id']);
    $swap->execute();
    $swap->close();

    echo json_encode(['success' => true, 'message' => 'Reordered.']);
    exit;
}

// ── DELETE: remove a banner slide ─────────────────────────────────────
if ($method === 'DELETE' && $action === 'delete') {
    $id = (int) ($_GET['id'] ?? 0);
    if (!$id) { echo json_encode(['success' => false, 'message' => 'Invalid ID.']); exit; }

    $imgRes = $conn->prepare("SELECT image_path, title FROM dashboard_banners WHERE id = ?");
    $imgRes->bind_param('i', $id);
    $imgRes->execute();
    $row = $imgRes->get_result()->fetch_assoc();
    $imgRes->close();
    if (!$row) { echo json_encode(['success' => false, 'message' => 'Banner not found.']); exit; }

    $stmt = $conn->prepare("DELETE FROM dashboard_banners WHERE id = ?");
    $stmt->bind_param('i', $id);
    if ($stmt->execute()) {
        $stmt->close();
        if (!empty($row['image_path']) && is_file('../' . $row['image_path'])) {
            @unlink('../' . $row['image_path']);
        }
        logAdminActivity($conn, $actorId, 'delete_banner', 'banner', $id, $row['title'] ? "Removed banner '{$row['title']}'." : null);
        echo json_encode(['success' => true, 'message' => 'Banner removed.']);
    } else {
        error_log('[banners.php] ' . $conn->error); echo json_encode(['success' => false, 'message' => 'Could not save. Please try again.']);
    }
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid request.']);
