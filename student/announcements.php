<?php
// ============================================================
//  Arandia College eLMS — Student: Announcements
//  File: student/announcements.php
//  Data comes from student/_init.php (same rules as the teacher side).
// ============================================================
require __DIR__ . '/_init.php';
$currentPage = 'announcements.php';
$pageTitle = 'Announcements';
require __DIR__ . '/../includes/header.php';
?>

<div class="section-panel active">
                <div class="page-header">
                    <h2 class="page-title">Announcements</h2>
                </div>
                <div class="card">
                    <?php if (empty($announcements)): ?>
                        <div style="padding:3rem; text-align:center; color:var(--text-muted);">No announcements found.</div>
                    <?php else:
                        foreach ($announcements as $a): ?>
                            <div style="padding:1.5rem; border-bottom:1px solid var(--border);" id="ann-<?= $a['id'] ?>">
                                <div
                                    style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:0.5rem;">
                                    <h3 style="font-size:1.1rem; font-weight:700;"><?= htmlspecialchars($a['title']) ?></h3>
                                    <?php if (!empty($a['course_code'])): ?><span
                                            class="badge badge-blue"><?= htmlspecialchars($a['course_code']) ?></span><?php endif; ?>
                                </div>
                                <div style="margin-bottom:1rem; line-height:1.6;"><?= nl2br(htmlspecialchars($a['body'])) ?>
                                </div>
                                <div style="font-size:0.8rem; color:var(--text-muted);">
                                    <span style="font-weight:600;"><?= htmlspecialchars($a['author']) ?></span> ·
                                    <?= $a['posted_at'] ?>
                                </div>
                            </div>
                        <?php endforeach; endif; ?>
                </div>
            </div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
