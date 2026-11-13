<?php
// ============================================================
//  Arandia College eLMS — Teacher: My Profile
//  File: teacher/profile.php
// ============================================================
require __DIR__ . '/_init.php';
$currentPage = 'profile.php';
$pageTitle = 'My Profile';

require __DIR__ . '/../includes/header.php';
?>

<div class="section-panel active">
                <div class="panel-header">
                    <div class="page-title">My Profile</div>
                </div>
                <?php include '../includes/profile_panel.php'; ?>
            </div>
<?php require __DIR__ . '/../includes/footer.php'; ?>