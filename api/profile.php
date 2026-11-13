<?php
// ============================================================
//  Arandia College eLMS — Profile API
//  File: api/profile.php
//  Accessible by: Student, Teacher
// ============================================================
session_start();
require_once '../config/conn.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(empty($_SESSION['user_id']) ? 401 : 403); echo json_encode(['success' => false, 'message' => empty($_SESSION['user_id']) ? 'Your session has ended. Please sign in again.' : 'You are not allowed to do this.']);
    exit;
}

$user_id = (int) $_SESSION['user_id'];
$action = $_GET['action'] ?? '';

// ── GET: fetch profile info ────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'get') {
    $row = $conn->query(
        "SELECT id, school_id, username, role, first_name, last_name, middle_name,
                email, contact, section_dept, profile_picture, status,
                DATE_FORMAT(created_at,'%b %d, %Y') AS member_since
         FROM users WHERE id = $user_id LIMIT 1"
    )->fetch_assoc();

    if (!$row) {
        echo json_encode(['success' => false, 'message' => 'User not found.']);
        exit;
    }
    echo json_encode(['success' => true, 'data' => $row]);
    exit;
}

// ── POST: update profile info ─────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'update') {
    $body = json_decode(file_get_contents('php://input'), true);
    $fname = trim($body['first_name'] ?? '');
    $lname = trim($body['last_name'] ?? '');
    $mname = trim($body['middle_name'] ?? '');
    $email = trim($body['email'] ?? '');
    $contact = trim($body['contact'] ?? '');

    // Students cannot change their name: it is set by the admin or the admission office.
    // Enforced here on the server, so an edited page or a crafted request cannot bypass it.
    $me = $conn->query("SELECT role, first_name, last_name, middle_name FROM users WHERE id = $user_id LIMIT 1")->fetch_assoc();
    if (($me['role'] ?? '') === 'Student') {
        $fname = (string) $me['first_name'];
        $lname = (string) $me['last_name'];
        $mname = (string) ($me['middle_name'] ?? '');
    }

    if (!$fname || !$lname) {
        echo json_encode(['success' => false, 'message' => 'First and last name required.']);
        exit;
    }

    // Check email uniqueness (exclude self) — only when the email is being changed,
    // so accounts that already share an address can still save the rest of their profile.
    $curEmail = (string) ($conn->query("SELECT email FROM users WHERE id = " . (int) $user_id)->fetch_row()[0] ?? '');
    if ($email && strcasecmp($email, $curEmail) !== 0) {
        $chk = $conn->prepare("SELECT id FROM users WHERE email=? AND id!=? LIMIT 1");
        $chk->bind_param('si', $email, $user_id);
        $chk->execute();
        if ($chk->get_result()->fetch_assoc()) {
            echo json_encode(['success' => false, 'message' => 'Email already used by another account.']);
            exit;
        }
    }

    $stmt = $conn->prepare(
        "UPDATE users SET first_name=?, last_name=?, middle_name=?, email=?, contact=? WHERE id=?"
    );
    $stmt->bind_param('sssssi', $fname, $lname, $mname, $email, $contact, $user_id);

    if ($stmt->execute()) {
        // Update session name
        $_SESSION['first_name'] = $fname;
        $_SESSION['last_name'] = $lname;
        echo json_encode(['success' => true, 'message' => 'Profile updated successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error.']);
    }
    exit;
}

// ── POST: upload profile picture ──────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'upload_pic') {
    if (empty($_FILES['picture']['name'])) {
        echo json_encode(['success' => false, 'message' => 'No file uploaded.']);
        exit;
    }

    // ── Profile picture rules: JPG, PNG, WEBP — max 2MB ──────────────────
    $picFile = $_FILES['picture'];
    $ext     = strtolower(pathinfo((string) $picFile['name'], PATHINFO_EXTENSION));
    $picErr  = '';
    if ($picFile['error'] === UPLOAD_ERR_INI_SIZE || $picFile['error'] === UPLOAD_ERR_FORM_SIZE) {
        $picErr = 'File too large. Max 2MB.';
    } elseif ($picFile['error'] !== UPLOAD_ERR_OK) {
        $picErr = 'Upload failed. Please try again.';
    } elseif (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true)) {
        $picErr = 'Profile picture must be JPG, PNG or WEBP.';
    } elseif ($picFile['size'] > 2 * 1024 * 1024) {
        $picErr = 'File too large. Max 2MB.';
    } else {
        // check the real content matches the extension
        $fh   = @fopen($picFile['tmp_name'], 'rb');
        $head = $fh ? (string) fread($fh, 16) : '';
        if ($fh) fclose($fh);
        $magicOk = false;
        if ($ext === 'jpg' || $ext === 'jpeg')  $magicOk = strncmp($head, "\xFF\xD8\xFF", 3) === 0;
        elseif ($ext === 'png')                 $magicOk = strncmp($head, "\x89PNG\r\n\x1A\n", 8) === 0;
        else                                    $magicOk = strncmp($head, 'RIFF', 4) === 0 && substr($head, 8, 4) === 'WEBP';
        if (!is_uploaded_file($picFile['tmp_name']) || !$magicOk) {
            $picErr = 'This file is not a valid ' . strtoupper($ext) . ' image.';
        }
    }
    if ($picErr !== '') {
        echo json_encode(['success' => false, 'message' => $picErr]);
        exit;
    }

    $upload_dir = '../uploads/profiles/';
    if (!is_dir($upload_dir))
        mkdir($upload_dir, 0775, true);

    // Delete old picture
    $old = $conn->query("SELECT profile_picture FROM users WHERE id=$user_id LIMIT 1")->fetch_assoc();
    if ($old && $old['profile_picture'] && file_exists('../' . $old['profile_picture'])) {
        unlink('../' . $old['profile_picture']);
    }

    $filename = 'user_' . $user_id . '_' . time() . '.' . $ext;
    $dest = $upload_dir . $filename;
    if (!move_uploaded_file($_FILES['picture']['tmp_name'], $dest)) {
        echo json_encode(['success' => false, 'message' => 'Upload failed.']);
        exit;
    }

    $path = 'uploads/profiles/' . $filename;
    $conn->query("UPDATE users SET profile_picture='$path' WHERE id=$user_id");

    echo json_encode(['success' => true, 'message' => 'Profile picture updated.', 'path' => $path]);
    exit;
}


// ── POST: change password ─────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'change_password') {
    $body = json_decode(file_get_contents('php://input'), true);

    $current_password = trim($body['current_password'] ?? '');
    $new_password     = trim($body['new_password'] ?? '');

    if (!$current_password || !$new_password) {
        echo json_encode(['success' => false, 'message' => 'All fields are required.']);
        exit;
    }
    if (strlen($new_password) < 8) {
        echo json_encode(['success' => false, 'message' => 'New password must be at least 8 characters.']);
        exit;
    }

    // Fetch current hashed password
    $stmt = $conn->prepare("SELECT password FROM users WHERE id = ? LIMIT 1");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$row) {
        echo json_encode(['success' => false, 'message' => 'User not found.']);
        exit;
    }

    // Verify current password
    if (!password_verify($current_password, $row['password'])) {
        echo json_encode(['success' => false, 'message' => 'Current password is incorrect.']);
        exit;
    }

    // Prevent reuse of same password
    if (password_verify($new_password, $row['password'])) {
        echo json_encode(['success' => false, 'message' => 'New password must be different from your current password.']);
        exit;
    }

    // Hash and save new password
    $hashed = password_hash($new_password, PASSWORD_DEFAULT);
    $stmt2  = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
    $stmt2->bind_param('si', $hashed, $user_id);

    if ($stmt2->execute()) {
        // New password: other "Remember me" devices must sign in again
        require_once __DIR__ . '/../includes/remember_me.php';
        rmForgetUser($conn, (int) $user_id);
        echo json_encode(['success' => true, 'message' => 'Password changed successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error. Please try again.']);
    }
    $stmt2->close();
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid request.']);