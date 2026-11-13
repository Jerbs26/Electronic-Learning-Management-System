<?php
// ============================================================
//  Arandia College eLMS — Student: My Awards
//  File: student/awards.php
//
//  Read-only list of every active award the LOGGED-IN student has
//  received. The student id always comes from the session; any
//  student_id in the URL is ignored.
// ============================================================
require __DIR__ . '/_init.php';   // login check + header data (same rules as every student page)
require_once __DIR__ . '/../includes/awards.php';
require_once __DIR__ . '/awards_components.php';

// ── This student's awards ──────────────────────────────────────────────
$awardsReady = awards_tables_ready($conn);
$allAwards = $awardsReady ? awards_fetch_for_student($conn, $user_id) : [];

// Filter options come only from this student's own awards.
$subjectOptions = [];
$yearOptions = [];
foreach ($allAwards as $a) {
    $subjectOptions[(int) $a['course_id']] = $a['course_name'] . ' (' . $a['course_code'] . ')';
    if (!empty($a['school_year'])) $yearOptions[$a['school_year']] = $a['school_year'];
}
asort($subjectOptions);
krsort($yearOptions);

$filterCourse = filter_var($_GET['subject'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: 0;
if (!isset($subjectOptions[$filterCourse])) $filterCourse = 0;
$filterYear = is_string($_GET['year'] ?? null) ? trim($_GET['year']) : '';
if (!isset($yearOptions[$filterYear])) $filterYear = '';

$shown = array_values(array_filter($allAwards, function ($a) use ($filterCourse, $filterYear) {
    if ($filterCourse && (int) $a['course_id'] !== $filterCourse) return false;
    if ($filterYear !== '' && ($a['school_year'] ?? '') !== $filterYear) return false;
    return true;
}));

$totalAwards = count($allAwards);
$subjectCount = count($subjectOptions);

$currentPage = 'awards.php';
$pageTitle = 'My Awards';
require __DIR__ . '/../includes/header.php';
?>

<?= student_awards_css() ?>
<style>
  .awp-head { display: flex; justify-content: space-between; align-items: flex-end; gap: 1rem; flex-wrap: wrap; margin-bottom: 1.5rem; }
  .awp-summary { font-size: .9rem; color: var(--text-muted); margin-top: .25rem; }
  .awp-filters { display: flex; gap: .75rem; flex-wrap: wrap; align-items: flex-end; margin-bottom: 1.5rem; }
  .awp-filters label { display: flex; flex-direction: column; gap: .3rem; font-size: .75rem; font-weight: 600; color: var(--text-muted); min-width: 0; flex: 1 1 200px; max-width: 320px; }
  .awp-filters select { width: 100%; background: var(--bg-card); color: var(--text-main); }
  .awp-filter-actions { display: flex; gap: .5rem; }
  .awp-count { font-size: .82rem; color: var(--text-muted); margin-bottom: .75rem; }
  @media (max-width: 600px) {
    .awp-filters label { max-width: none; flex-basis: 100%; }
  }
</style>

<div class="section-panel active">
    <div class="page-header"><h2 class="page-title">My Awards</h2></div>

    <?php if ($totalAwards === 0): ?>
        <div class="card"><?= student_awards_empty() ?></div>
    <?php else: ?>
        <form class="awp-filters" method="get" action="awards.php">
            <label>Subject
                <select class="form-control" name="subject" onchange="this.form.submit()">
                    <option value="">All subjects</option>
                    <?php foreach ($subjectOptions as $cid => $label): ?>
                        <option value="<?= (int) $cid ?>" <?= $filterCourse === (int) $cid ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <?php if (!empty($yearOptions)): ?>
            <label>Academic year
                <select class="form-control" name="year" onchange="this.form.submit()">
                    <option value="">All years</option>
                    <?php foreach ($yearOptions as $y): ?>
                        <option value="<?= htmlspecialchars($y) ?>" <?= $filterYear === $y ? 'selected' : '' ?>><?= htmlspecialchars($y) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <?php endif; ?>
            <div class="awp-filter-actions">
                <noscript><button type="submit" class="btn btn-primary btn-sm">Apply</button></noscript>
                <?php if ($filterCourse || $filterYear !== ''): ?>
                    <a href="awards.php" class="btn btn-outline btn-sm">Clear filters</a>
                <?php endif; ?>
            </div>
        </form>

        <?php if (empty($shown)): ?>
            <div class="card">
                <div class="aw-empty">
                    <div class="aw-empty-title">No awards match these filters</div>
                    <p class="aw-empty-text">Try a different subject or academic year.</p>
                </div>
            </div>
        <?php else: ?>
            <?php if ($filterCourse || $filterYear !== ''): ?>
                <div class="awp-count">Showing <?= count($shown) ?> of <?= $totalAwards ?> awards</div>
            <?php endif; ?>
            <div class="aw-grid">
                <?php foreach ($shown as $a) echo student_award_card($a); ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
