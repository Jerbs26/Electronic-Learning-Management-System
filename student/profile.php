<?php
// ============================================================
//  Arandia College eLMS — Student: My Profile
//  File: student/profile.php
//  Data comes from student/_init.php (same rules as the teacher side).
// ============================================================
require __DIR__ . '/_init.php';
$currentPage = 'profile.php';
$pageTitle = 'My Profile';
require __DIR__ . '/../includes/header.php';
?>

<div class="section-panel active">
                <div class="page-header">
                    <h2 class="page-title">My Profile</h2>
                </div>
                <?php // Same layout as the Teacher profile: the shared panel has the photo, name and details ?>
                <?php include '../includes/profile_panel.php'; ?>
            </div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
