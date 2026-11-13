<?php
// ============================================================
//  Arandia College eLMS — Teacher: Assignments
//  File: teacher/assignments.php
//  All class work in one place: the class's assignments, then its quizzes.
//  (The old Quizzes page, teacher/quizzes.php, now forwards here.)
// ============================================================
require __DIR__ . '/_init.php';
$currentPage = 'assignments.php';
$pageTitle = 'Assignments';

require_once __DIR__ . '/../includes/class_picker.php';
require __DIR__ . '/../includes/header.php';
?>

<style>
    .section-panel {
        --a-soft: #eef3ff;
        --a-strong: #003087;
        --a-line: rgba(15, 23, 42, .07);
    }

    html[data-theme="dark"] .section-panel {
        --a-soft: rgba(143, 176, 255, .12);
        --a-strong: #c7d7ff;
        --a-line: rgba(255, 255, 255, .08);
    }

    .asg-chips {
        display: flex;
        gap: .45rem;
        flex-wrap: wrap;
        margin-bottom: .9rem;
    }

    .asg-chip {
        border: 1px solid var(--a-line);
        background: var(--white);
        border-radius: 100px;
        padding: .35rem .85rem;
        font: inherit;
        font-size: .8rem;
        font-weight: 700;
        color: var(--text-light);
        cursor: pointer;
    }

    .asg-chip.on {
        background: var(--a-soft);
        color: var(--a-strong);
        border-color: transparent;
    }

    .asg-card {
        background: var(--white);
        border-radius: 16px;
        box-shadow: 0 2px 14px rgba(0, 0, 0, .06);
    }

    .asg-head,
    .asg-row {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 170px 160px 70px 130px 36px;
        gap: 1rem;
        align-items: center;
        padding: .9rem 1.3rem;
    }

    .asg-head {
        font-size: .66rem;
        font-weight: 800;
        letter-spacing: .07em;
        text-transform: uppercase;
        color: var(--text-light);
        background: var(--bg-light);
        padding-top: .6rem;
        padding-bottom: .6rem;
        border-radius: 16px 16px 0 0;
    }

    .asg-row {
        border-top: 1px solid var(--a-line);
    }

    .asg-title {
        font-size: .92rem;
        font-weight: 800;
        color: var(--text-dark);
        text-decoration: none;
    }

    .asg-title:hover {
        color: var(--a-strong);
        text-decoration: underline;
    }

    .asg-desc {
        font-size: .78rem;
        color: var(--text-light);
        margin-top: .15rem;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .asg-ref {
        display: inline-flex;
        align-items: center;
        gap: .25rem;
        font-size: .74rem;
        font-weight: 700;
        color: var(--a-strong);
        margin-top: .25rem;
        text-decoration: none;
    }

    .asg-ref svg,
    .asg-ref .ico {
        width: 13px;
        height: 13px;
    }

    .asg-due b {
        display: block;
        font-size: .82rem;
        color: var(--text-dark);
        font-weight: 700;
    }

    .asg-due span {
        font-size: .74rem;
        font-weight: 700;
    }

    .t-warn {
        color: #b38600;
    }

    .t-bad {
        color: #c0392b;
    }

    .t-muted {
        color: var(--text-light);
    }

    .asg-subs span {
        font-size: .78rem;
        color: var(--text-light);
    }

    .asg-bar {
        height: 6px;
        border-radius: 100px;
        background: var(--a-soft);
        overflow: hidden;
        margin-top: .35rem;
    }

    .asg-bar i {
        display: block;
        height: 100%;
        background: #003087;
        border-radius: 100px;
    }

    html[data-theme="dark"] .asg-bar i {
        background: #8fb0ff;
    }

    .asg-pts {
        font-size: .84rem;
        font-weight: 700;
        color: var(--text-dark);
    }

    .asg-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: .45rem .85rem;
        border-radius: 10px;
        font: inherit;
        font-size: .8rem;
        font-weight: 800;
        text-decoration: none;
        cursor: pointer;
        border: 1px solid var(--a-line);
        background: var(--white);
        color: var(--text-dark);
    }

    .asg-btn.primary {
        background: #003087;
        border-color: #003087;
        color: #fff;
    }

    .asg-btn.primary:hover {
        background: #0044cc;
    }

    .asg-pill {
        display: inline-block;
        font-size: .72rem;
        font-weight: 700;
        padding: .25rem .7rem;
        border-radius: 100px;
        white-space: nowrap;
    }

    .asg-pill.ok {
        background: rgba(0, 135, 90, .12);
        color: #00875a;
    }

    .asg-pill.muted {
        background: rgba(100, 116, 139, .12);
        color: var(--text-light);
    }

    .asg-more {
        position: relative;
    }

    .asg-dots {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        border: 1px solid transparent;
        background: none;
        color: var(--text-light);
        font-size: 1.1rem;
        cursor: pointer;
        line-height: 1;
    }

    .asg-dots:hover {
        border-color: var(--a-line);
        color: var(--text-dark);
    }

    .asg-menu {
        display: none;
        position: absolute;
        right: 0;
        top: calc(100% + 4px);
        min-width: 160px;
        background: var(--white);
        border: 1px solid var(--a-line);
        border-radius: 12px;
        box-shadow: 0 12px 28px rgba(15, 23, 42, .14);
        padding: .3rem 0;
        z-index: 50;
    }

    .asg-menu.show {
        display: block;
    }

    .asg-menu a,
    .asg-menu button {
        display: block;
        width: 100%;
        text-align: left;
        padding: .5rem 1rem;
        font: inherit;
        font-size: .84rem;
        font-weight: 600;
        color: var(--text-dark);
        background: none;
        border: 0;
        text-decoration: none;
        cursor: pointer;
    }

    .asg-menu a:hover,
    .asg-menu button:hover {
        background: var(--bg-light);
    }

    .asg-menu .danger {
        color: #c0392b;
    }

    .asg-sec-h {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .75rem;
        margin: 1.4rem 0 .7rem;
    }

    #assignments-list {
        margin-top: .4rem;
    }

    .asg-sec-h span {
        display: inline-flex;
        align-items: center;
        gap: .45rem;
        font-family: 'Nunito', sans-serif;
        font-size: 1.02rem;
        font-weight: 900;
        color: var(--text-dark);
    }

    .asg-sec-h svg,
    .asg-sec-h .ico {
        width: 18px;
        height: 18px;
        color: var(--a-strong);
    }

    #quiz-list-wrap .ql-head {
        display: none;
    }

    /* ---------- Quiz list (rows built by quizRow() in includes/footer.php) ---------- */
    .ql-grid {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 65px 70px 65px 75px 185px;
        gap: .5rem;
        align-items: center;
        padding: .85rem 1.25rem;
    }

    .ql-thead {
        padding-top: .5rem;
        padding-bottom: .5rem;
        font-size: .65rem;
        font-weight: 800;
        letter-spacing: .07em;
        text-transform: uppercase;
        color: var(--ui-text-3, #bbb);
        background: var(--ui-surface-2, #f8f9fc);
        border-bottom: 1px solid var(--ui-border-soft, #f0f2f5);
    }

    .ql-row {
        border-bottom: 1px solid var(--ui-border-soft, #f8f9fc);
        transition: background .15s;
    }

    .ql-row:last-child {
        border-bottom: 0;
    }

    .ql-row:hover {
        background: var(--a-soft);
    }

    .ql-main {
        display: flex;
        align-items: center;
        gap: .7rem;
        min-width: 0;
    }

    .ql-ico {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        background: var(--a-soft);
        color: var(--a-strong);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: .95rem;
        flex-shrink: 0;
    }

    .ql-info {
        min-width: 0;
    }

    .ql-title {
        font-size: .84rem;
        font-weight: 700;
        color: var(--ui-text, #1a1a2e);
        overflow-wrap: anywhere;
    }

    .ql-desc {
        font-size: .7rem;
        color: var(--ui-text-3, #aaa);
        margin-top: .04rem;
    }

    .ql-file {
        font-size: .7rem;
        color: var(--ui-link, #003087);
        font-weight: 700;
    }

    .ql-close {
        font-size: .67rem;
        color: #c0392b;
        font-weight: 700;
    }

    .ql-c {
        text-align: center;
        font-size: .75rem;
        color: var(--ui-text-2, #666);
    }

    .ql-actions {
        display: flex;
        justify-content: flex-end;
        gap: .3rem;
        flex-wrap: wrap;
    }

    /* Tablet: drop the fixed widths a little so Actions never gets pushed off */
    @media (max-width: 1100px) and (min-width: 701px) {
        .ql-grid {
            grid-template-columns: minmax(0, 1fr) 56px 56px 56px 66px 185px;
        }
    }

    /* Phone: each quiz becomes a card — title on top, stats in a row, buttons below */
    @media (max-width: 700px) {
        .ql-thead {
            display: none;
        }

        .ql-row {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: .55rem .5rem;
            padding: 1rem;
        }

        .ql-main {
            flex: 1 1 100%;
            align-items: flex-start;
        }

        .ql-title {
            font-size: .95rem;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: .3rem;
        }

        .ql-title .badge {
            margin-left: 0 !important;
        }

        .ql-desc,
        .ql-close {
            font-size: .76rem;
            margin-top: .2rem;
        }

        .ql-c {
            text-align: left;
            display: inline-flex;
            align-items: center;
            gap: .25rem;
            padding-left: calc(32px + .7rem);
        }

        .ql-c ~ .ql-c {
            padding-left: 0;
        }

        .ql-c+.ql-c::before {
            content: "·";
            color: var(--ui-text-3, #bbb);
            margin-right: .35rem;
        }

        .ql-meta {
            white-space: nowrap;
        }

        .ql-nil {
            display: none !important;
        }

        .ql-actions {
            flex: 1 1 100%;
            justify-content: stretch;
            gap: .4rem;
            padding-top: .2rem;
        }

        .ql-actions .btn {
            flex: 1 1 0;
            justify-content: center;
            min-height: 38px;
        }
    }

    /* the "Quizzes" heading above already names the list */
    .asg-none {
        text-align: center;
        padding: 2.5rem 1rem;
        color: var(--text-light);
        font-size: .88rem;
    }

    .asg-none strong {
        display: block;
        color: var(--text-dark);
        font-size: 1rem;
        margin-bottom: .2rem;
    }

    @media (max-width: 1100px) {
        .asg-head {
            display: none;
        }

        .asg-row {
            grid-template-columns: minmax(0, 1fr) auto 36px;
        }

        .asg-due,
        .asg-subs {
            grid-column: 1;
        }

        .asg-pts {
            display: none;
        }
    }

    /* ---------- Due date picker: calendar + time slider in ONE popup ---------- */
</style>
<div class="section-panel active">
    <?php renderClassBack($mySubjects); ?>
    <?php if (empty($mySubjects)): ?>
        <div class="panel-header">
            <div class="page-title">Assignments</div>
        </div>
        <div class="card-panel">
            <div class="card-panel-body">
                <div class="empty-state"><span class="empty-icon"><?= t_icon('book') ?></span>No subjects assigned yet.
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="cp-row">
            <div class="cp-row-left">
                <div>
                    <div class="cp-row-title">Assignments</div>
                </div>
                <?php renderClassPicker($conn, $mySubjects, (int) $user_id, 'assign-course-select', 'asg'); ?>
            </div>
            <div class="cp-row-actions">
                <button class="btn btn-gray" onclick="openQuizModal()">+ Create quiz</button>
                <button class="btn btn-green" onclick="openAssignModal()">+ Create assignment</button>
            </div>
        </div>
        <select id="assign-course-select" onchange="loadAssignments(); asgSyncQuizzes()" aria-hidden="true" tabindex="-1"
            class="cp-hidden-select">
            <option value=""></option>
            <?php foreach (array_unique(array_column($mySubjects, 'course_id')) as $__cid): ?>
                <option value="<?= (int) $__cid ?>"></option><?php endforeach; ?>
        </select>

        <!-- The quiz list follows the class picked above (footer.php loadQuizzes reads this select) -->
        <select id="quiz-course-select" onchange="loadQuizzes()" aria-hidden="true" tabindex="-1" class="cp-hidden-select">
            <option value=""></option>
            <?php foreach (array_unique(array_column($mySubjects, 'course_id')) as $__cid): ?>
                <option value="<?= (int) $__cid ?>"></option><?php endforeach; ?>
        </select>

        <div id="assignments-list"></div>
        <div id="assign-list-wrap">
            <div
                style="background:var(--ui-surface, white);border-radius:16px;box-shadow:0 2px 14px rgba(0,0,0,.07);overflow:hidden;margin-bottom:1.1rem">
                <div style="padding:3rem;text-align:center;color:var(--ui-text-3, #bbb)">
                    <div style="font-size:2.5rem;margin-bottom:.5rem"><?= t_icon('clipboard') ?></div>
                    <div>Select a subject above to view its assignments.</div>
                </div>
            </div>
        </div>

        <div class="asg-sec-h" id="quizzes"><span>Quizzes</span></div>
        <div id="quiz-list-wrap">
            <div class="asg-card">
                <div class="asg-none">Select a subject above to view its quizzes.</div>
            </div>
        </div>
        <script>
            // One class picker for both lists: the quizzes follow the assignments' class
            function asgSyncQuizzes() {
                const a = document.getElementById('assign-course-select'), q = document.getElementById('quiz-course-select');
                if (!a || !q || q.value === a.value) return;
                q.value = a.value;
                q.dispatchEvent(new Event('change'));
            }
        </script>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>