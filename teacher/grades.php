<?php
// ============================================================
//  Arandia College eLMS — Teacher: Grades (Gradebook + insights)
//  File: teacher/grades.php?course=ID&section=NAME
//  One table for the class: every assignment, quiz and manual item
//  against every student, with totals. Scores are read live through
//  api/gradebook.php (includes/gradebook.php), so a grade saved in the
//  speed grader (teacher/grade.php) shows up here at once.
//    · Assignment cells open the speed grader for that student.
//    · Manual items (added here) are typed straight into the table.
//    · "Needs support" lists students below the passing average and can
//      ask AI for an action plan (api/ai_insights.php).
// ============================================================
require __DIR__ . '/_init.php';
$currentPage = 'grades.php';
$pageTitle = 'Grades';

require_once __DIR__ . '/../includes/class_picker.php';
require __DIR__ . '/../includes/header.php';
?>

<style>
    .gb {
        --gb-line: rgba(15, 23, 42, .08);
        --gb-soft: #eef3ff;
        --gb-strong: #003087;
        --gb-ok: #0f7b4a;
        --gb-okbg: #e6f6ee;
        --gb-bad: #b42318;
        --gb-badbg: #fdecea;
        --gb-warn: #8a5a00;
        --gb-warnbg: #fff4de;
    }

    html[data-theme="dark"] .gb {
        --gb-line: rgba(255, 255, 255, .1);
        --gb-soft: rgba(143, 176, 255, .12);
        --gb-strong: #c7d7ff;
        --gb-ok: #5fd6a0;
        --gb-okbg: rgba(95, 214, 160, .12);
        --gb-bad: #ff8a80;
        --gb-badbg: rgba(255, 138, 128, .12);
        --gb-warn: #f2c66d;
        --gb-warnbg: rgba(242, 198, 109, .12);
    }

    .gb-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: .8rem; margin-bottom: 1rem; }
    .gb-stat { background: var(--white); border-radius: 14px; padding: .85rem 1.1rem; box-shadow: 0 2px 14px rgba(0, 0, 0, .05); }
    .gb-stat b { display: block; font-size: 1.5rem; font-weight: 900; color: var(--text-dark); line-height: 1.15; }
    .gb-stat span { font-size: .7rem; font-weight: 800; color: var(--text-light); text-transform: uppercase; letter-spacing: .05em; }

    .gb-toolbar { display: flex; gap: .6rem; flex-wrap: wrap; align-items: center; margin-bottom: .8rem; }
    .gb-search { flex: 1 1 220px; max-width: 340px; height: 40px; border: 1px solid var(--gb-line); border-radius: 10px; padding: 0 .9rem;
                 font: inherit; font-size: .88rem; background: var(--white); color: var(--text-dark); }
    .gb-search:focus, .gb-in:focus { outline: none; border-color: var(--gb-strong); box-shadow: 0 0 0 3px var(--gb-soft); }
    .gb-sort { display: flex; gap: .4rem; }
    .gb-pill { border: 1px solid var(--gb-line); background: var(--white); border-radius: 100px; padding: .35rem .85rem; font: inherit; font-size: .78rem;
               font-weight: 700; color: var(--text-light); cursor: pointer; }
    .gb-pill.on { background: var(--gb-soft); color: var(--gb-strong); border-color: transparent; }
    .gb-grow { flex: 1; }

    .gb-card { background: var(--white); border-radius: 16px; box-shadow: 0 2px 14px rgba(0, 0, 0, .06); overflow: hidden; margin-bottom: 1.1rem; }
    /* width:0 + min-width:100% — the wide table scrolls inside its card instead of stretching the page.
       (The layout's main area is a flex item, so wide content would otherwise widen it past the screen,
       cut off the right side and push the pinned Total column into the middle of the table.) */
    .gb { min-width: 0; max-width: 100%; }
    .gb-scroll { overflow: auto; max-height: calc(100vh - 340px); min-height: 140px; width: 0; min-width: 100%; -webkit-overflow-scrolling: touch; }
    .gb-table { border-collapse: separate; border-spacing: 0; width: max-content; min-width: 100%; }
    .gb-table th, .gb-table td { padding: .6rem .8rem; border-bottom: 1px solid var(--gb-line); white-space: nowrap; background: var(--white); }
    .gb-table thead th { position: sticky; top: 0; z-index: 3; vertical-align: middle; }
    /* The site's shared table style (elms-ui.css: .elms-app main th) makes header cells transparent.
       Pinned headers must be solid, or the scrolling columns show through them ("Total" overlap). */
    .elms-app main .gb-table th { background: var(--white); }
    .gb-table thead th.gb-it { vertical-align: top; }
    .gb-table tbody td { vertical-align: middle; }
    .gb-table tbody tr:hover td { background: var(--gb-soft); }
    .gb-stu { position: sticky; left: 0; z-index: 2; text-align: left; min-width: 190px; max-width: 250px; border-right: 1px solid var(--gb-line); }
    .gb-table thead .gb-stu { z-index: 4; }
    .gb-tot { position: sticky; right: 0; z-index: 2; text-align: center; min-width: 112px; border-left: 1px solid var(--gb-line); }
    .gb-table thead .gb-tot { z-index: 4; }
    .gb-stu b { display: block; font-size: .88rem; color: var(--text-dark); overflow: hidden; text-overflow: ellipsis; }
    .gb-stu small { font-size: .7rem; color: var(--text-light); }
    .gb-it { min-width: 130px; max-width: 180px; text-align: center; position: relative; }
    .gb-it-title { font-size: .82rem; font-weight: 800; color: var(--text-dark); overflow: hidden; text-overflow: ellipsis; max-width: 160px; margin: 0 auto; }
    .gb-it-meta { font-size: .68rem; color: var(--text-light); margin-top: .15rem; }
    .gb-type { display: inline-block; border-radius: 100px; padding: .05rem .45rem; background: var(--gb-soft); color: var(--gb-strong); font-weight: 800; font-size: .62rem; letter-spacing: .03em; text-transform: uppercase; }
    .gb-c { text-align: center; }
    .gb-sc { font-size: .95rem; font-weight: 800; color: var(--text-dark); line-height: 1; }
    .gb-dash { color: var(--text-light); }
    .gb-chip { display: inline-block; border-radius: 100px; padding: .15rem .6rem; font-size: .7rem; font-weight: 800; }
    .gb-chip.warn { background: var(--gb-warnbg); color: var(--gb-warn); }
    .gb-chip.bad { background: var(--gb-badbg); color: var(--gb-bad); }
    .gb-link { text-decoration: none; color: inherit; display: inline-block; padding: .15rem .35rem; border-radius: 8px; }
    .gb-link:hover { background: var(--gb-soft); }
    .gb-in { width: 68px; height: 34px; text-align: center; font: inherit; font-size: .95rem; font-weight: 800; border: 1px solid var(--gb-line);
             border-radius: 8px; background: transparent; color: var(--text-dark); }
    .gb-in.bad { border-color: var(--gb-bad); }
    .gb-in.saving { opacity: .55; }
    .gb-tot-score { font-size: .8rem; font-weight: 800; color: var(--text-dark); }
    .gb-tot-pct { display: inline-block; margin-top: .15rem; border-radius: 100px; padding: .1rem .55rem; font-size: .74rem; font-weight: 900; color: var(--text-light); }
    .gb-tot-pct.ok { background: var(--gb-okbg); color: var(--gb-ok); }
    .gb-tot-pct.bad { background: var(--gb-badbg); color: var(--gb-bad); }
    .gb-tot-note { font-size: .66rem; color: var(--gb-warn); font-weight: 700; margin-top: .1rem; }

    .gb-empty { padding: 3rem 1.5rem; text-align: center; color: var(--text-light); }
    .gb-empty strong { display: block; color: var(--text-dark); font-size: 1rem; margin-bottom: .3rem; }
    .gb-empty .btn { margin-top: .9rem; }

    .gb-risk-head { display: flex; align-items: center; gap: .7rem; padding: 1rem 1.3rem; border-bottom: 1px solid var(--gb-line); }
    .gb-risk-head .ico { width: 20px; height: 20px; color: var(--gb-strong); }
    .gb-risk-head strong { display: block; font-size: .95rem; color: var(--text-dark); }
    .gb-risk-head small { font-size: .74rem; color: var(--text-light); }
    .gb-risk-row { display: flex; align-items: center; gap: 1rem; padding: .75rem 1.3rem; border-bottom: 1px solid var(--gb-line); }
    .gb-risk-row:last-child { border-bottom: 0; }
    .gb-risk-who { flex: 1; min-width: 0; }
    .gb-risk-who b { display: block; font-size: .88rem; color: var(--text-dark); }
    .gb-risk-who small { font-size: .72rem; color: var(--text-light); }
    .gb-risk-avg { font-weight: 900; color: var(--gb-bad); font-size: .95rem; }
    .gb-risk-ok { padding: 1rem 1.3rem; font-size: .85rem; color: var(--text-light); }

    /* ── Performance analytics ─────────────────────────────── */
    .gb { --gb-c1: #2a6fdb; --gb-c2: #e8702a; }
    html[data-theme="dark"] .gb { --gb-c1: #4d8ef0; --gb-c2: #dd6b2a; }
    .gb-new { display: inline-block; vertical-align: middle; margin-left: .35rem; border-radius: 100px; padding: .05rem .45rem; background: var(--gb-soft);
              color: var(--gb-strong); font-size: .6rem; font-weight: 800; letter-spacing: .03em; text-transform: none; }
    .gb-ana { padding: 1rem 1.3rem 1.2rem; }
    .gb-ana-head { display: flex; align-items: flex-start; justify-content: space-between; gap: .8rem; flex-wrap: wrap; }
    .gb-ana-title { display: flex; align-items: center; gap: .45rem; font-size: .95rem; font-weight: 800; color: var(--text-dark); }
    .gb-ana-title .ico { width: 18px; height: 18px; color: var(--gb-strong); }
    .gb-ana-meta { text-align: right; font-size: .76rem; color: var(--text-light); line-height: 1.45; }
    .gb-ana-meta b { color: var(--text-dark); font-weight: 700; }
    .gb-ana-live { display: inline-flex; align-items: center; gap: .3rem; font-size: .7rem; }
    .gb-ana-live i { width: 7px; height: 7px; border-radius: 50%; background: var(--gb-ok); display: inline-block; }
    .gb-ana-live.off i { background: var(--gb-warn); }
    .gb-ana-desc { margin: .35rem 0 .7rem; font-size: .8rem; color: var(--text-light); line-height: 1.5; }
    .gb-ana-legend { display: flex; gap: 1rem; flex-wrap: wrap; font-size: .76rem; color: var(--text-dark); margin-bottom: .6rem; }
    .gb-ana-legend span { display: inline-flex; align-items: center; gap: .35rem; }
    .gb-ana-legend i { width: 10px; height: 10px; border-radius: 3px; display: inline-block; }
    .gb-ana-chart { display: flex; }
    .gb-ana-y { position: relative; width: 40px; height: 180px; flex: 0 0 40px; }
    .gb-ana-y span { position: absolute; right: 8px; transform: translateY(50%); font-size: .68rem; color: var(--text-light); }
    .gb-ana-scroll { flex: 1 1 0; width: 0; min-width: 0; overflow-x: auto; -webkit-overflow-scrolling: touch; }
    .gb-ana-inner { min-width: 100%; }
    .gb-ana-plot { position: relative; height: 180px; display: flex; }
    .gb-ana-grid { position: absolute; left: 0; right: 0; height: 0; border-top: 1px solid var(--gb-line); pointer-events: none; }
    .gb-ana-grid.base { border-top-color: var(--text-light); opacity: .45; }
    .gb-ana-g { position: relative; flex: 1 1 0; min-width: 76px; display: flex; align-items: flex-end; justify-content: center; gap: 2px;
                height: 100%; cursor: default; border-radius: 8px; outline: none; }
    .gb-ana-g:hover, .gb-ana-g:focus-visible { background: var(--gb-soft); }
    .gb-ana-bar { width: 22px; border-radius: 4px 4px 0 0; transition: height .35s ease; }
    .gb-ana-bar.c1 { background: var(--gb-c1); }
    .gb-ana-bar.c2 { background: var(--gb-c2); }
    .gb-ana-labels { display: flex; }
    .gb-ana-labels div { flex: 1 1 0; min-width: 76px; padding: .4rem .3rem 0; text-align: center; font-size: .72rem; color: var(--text-light);
                         overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .gb-ana-tiles { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: .6rem; margin-top: 1rem; }
    .gb-ana-tile { background: var(--gb-soft); border-radius: 12px; padding: .7rem .9rem; min-width: 0; }
    .gb-ana-tile span { display: block; font-size: .74rem; color: var(--text-light); }
    .gb-ana-tile b { display: block; font-size: 1.25rem; font-weight: 800; color: var(--text-dark); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .gb-ana-tile b.bad { color: var(--gb-bad); }
    .gb-ana-tile b.ok { color: var(--gb-ok); }
    .gb-ana-tile small { display: block; font-size: .7rem; color: var(--text-light); }
    .gb-ana-tip { position: fixed; z-index: 2000; pointer-events: none; max-width: 260px; background: var(--white); color: var(--text-dark);
                  border: 1px solid var(--gb-line); border-radius: 10px; box-shadow: 0 8px 24px rgba(0, 0, 0, .18); padding: .55rem .7rem; font-size: .76rem; line-height: 1.5; }
    .gb-ana-tip[hidden] { display: none; }
    .gb-ana-tip b { display: block; font-size: .8rem; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .gb-ana-tip i { display: inline-block; width: 8px; height: 8px; border-radius: 2px; margin-right: .35rem; }
    .gb-ana-tip small { color: var(--text-light); }
    .gb-sr { position: absolute; width: 1px; height: 1px; overflow: hidden; clip: rect(0 0 0 0); white-space: nowrap; }
    @media (prefers-reduced-motion: reduce) { .gb-ana-bar { transition: none; } }

    .gb-plan-text { white-space: pre-wrap; line-height: 1.6; font-size: .92rem; color: var(--text-dark); }
    .gb-plan-weak { margin-top: .9rem; font-size: .8rem; color: var(--text-light); }
    .gb-plan-weak b { color: var(--text-dark); }

    /* A 16px field font stops iOS Safari from zooming the page when a box gets focus */
    @media (max-width: 991.98px), (hover: none) and (pointer: coarse) {
        .gb-search, .gb-in, .gb-sc { font-size: 16px; }
    }

    @media (max-width: 640px) {
        .gb-stu { min-width: 118px; max-width: 140px; padding-left: .6rem; padding-right: .5rem; }
        .gb-stu b { font-size: .82rem; white-space: normal; line-height: 1.25; }
        .gb-stu small { display: block; overflow: hidden; text-overflow: ellipsis; }
        .gb-tot { min-width: 84px; padding-left: .4rem; padding-right: .4rem; }
        .gb-it { min-width: 108px; }
        .gb-it-title { max-width: 120px; }
        .gb-it-meta { white-space: normal; line-height: 1.5; }
        .gb-table th, .gb-table td { padding: .55rem .5rem; }
        .gb-scroll { max-height: none; }
        .gb-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: .6rem; }
        .gb-stat { padding: .75rem .9rem; }
        .gb-stat b { font-size: 1.3rem; }
        .gb-sort { flex-wrap: wrap; }
        .gb-search { max-width: none; }
        .gb-risk-row { flex-wrap: wrap; gap: .5rem .8rem; padding: .7rem 1rem; }
        .gb-risk-who { flex-basis: 100%; }
        .gb-ana { padding: .9rem 1rem 1rem; }
        .gb-ana-meta { text-align: left; }
        .gb-ana-y { width: 34px; flex-basis: 34px; }
        .gb-ana-tiles { grid-template-columns: minmax(0, 1fr); }
        .gb-ana-tip { max-width: calc(100vw - 24px); }
    }
    @media (max-width: 380px) {
        /* very small phones: only the Student column stays pinned, so the scores have room */
        .gb-table thead th.gb-tot, .gb-table td.gb-tot { position: static; }
    }
    @media (max-width: 360px) {
        .gb-stats { grid-template-columns: minmax(0, 1fr); }
    }
</style>

<div class="section-panel active gb">
    <?php renderClassBack($mySubjects); ?>
    <?php if (empty($mySubjects)): ?>
        <div class="panel-header">
            <div class="page-title">Grades</div>
        </div>
        <div class="card-panel">
            <div class="card-panel-body">
                <div class="empty-state"><span class="empty-icon"><?= t_icon('book') ?></span>No subjects assigned yet.</div>
            </div>
        </div>
    <?php else: ?>
        <div class="cp-row">
            <div class="cp-row-left">
                <div>
                    <div class="cp-row-title">Grades</div>
                </div>
                <?php renderClassPicker($conn, $mySubjects, (int) $user_id, 'grades-course-select', 'grd'); ?>
            </div>
            <div class="cp-row-actions">
                <a class="btn btn-gray" id="gb-export" href="#" onclick="return gbExport(event)">Export CSV</a>
            </div>
        </div>
        <select id="grades-course-select" onchange="gbLoad()" aria-hidden="true" tabindex="-1" class="cp-hidden-select">
            <option value=""></option>
            <?php foreach (array_unique(array_column($mySubjects, 'course_id')) as $__cid): ?>
                <option value="<?= (int) $__cid ?>"></option><?php endforeach; ?>
        </select>

        <div class="gb-stats" id="gb-stats"></div>

        <div class="gb-toolbar">
            <input type="search" class="gb-search" id="gb-q" placeholder="Search a student…" autocomplete="off" aria-label="Search a student">
            <div class="gb-sort" role="group" aria-label="Sort students">
                <button type="button" class="gb-pill on" data-sort="name">A–Z</button>
                <button type="button" class="gb-pill" data-sort="low">Lowest first</button>
            </div>
        </div>

        <div class="gb-card" id="gb-body">
            <div class="gb-empty">Select a subject above to view its grades.</div>
        </div>

        <div class="gb-card" id="gb-ana" hidden>
            <div class="gb-ana">
                <div class="gb-ana-head">
                    <div class="gb-ana-title"><?= t_icon('chart') ?>Performance analytics<span class="gb-new">New</span></div>
                    <div class="gb-ana-meta">
                        <b id="gb-ana-class"></b><br>
                        <span class="gb-ana-live" id="gb-ana-live" aria-live="polite"><i></i><span>Live</span></span>
                    </div>
                </div>
                <div class="gb-ana-desc">One pair of bars per student, highest average first. Completion is the share of this class's work the student submitted. Average is the student's gradebook total: missing work counts as 0 and work still to grade is skipped.</div>
                <div class="gb-ana-legend" aria-hidden="true">
                    <span><i style="background:var(--gb-c1)"></i>Completion %</span>
                    <span><i style="background:var(--gb-c2)"></i>Average score %</span>
                </div>
                <div id="gb-ana-chart"></div>
                <div class="gb-ana-tiles" id="gb-ana-tiles"></div>
            </div>
        </div>
        <div class="gb-ana-tip" id="gb-ana-tip" role="tooltip" hidden></div>

        <div class="gb-card" id="gb-risk" hidden>
            <div class="gb-risk-head">
                <?= t_icon('sparkles') ?>
                <div><strong>Needs support</strong><small id="gb-risk-sub"></small></div>
            </div>
            <div id="gb-risk-list"></div>
        </div>

        <!-- AI action plan -->
        <div class="modal-overlay" id="gbPlanModal">
            <div class="modal" style="max-width:560px">
                <div class="modal-head">
                    <h2 id="gb-plan-name">Action plan</h2>
                    <button class="modal-close" aria-label="Close" onclick="closeModal('gbPlanModal')"><?= t_icon('x') ?></button>
                </div>
                <div class="modal-body">
                    <div class="gb-plan-text" id="gb-plan-text"></div>
                    <div class="gb-plan-weak" id="gb-plan-weak"></div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-gray" onclick="closeModal('gbPlanModal')">Close</button>
                </div>
            </div>
        </div>

        <script>
            const GB_TYPE = { assignment: 'Assignment', quiz: 'Quiz', manual: 'Manual' };
            let GB = null;            // { key, course, section, items, students, cells, totals, manual }
            let GB_RISK = [];
            let gbPass = 75, gbQuery = '', gbSort = 'name', gbReq = 0, gbPlanSeq = 0, gbDeferred = false;
            // Live refresh: the class is re-read every GB_LIVE_MS while the tab is visible, when the tab
            // comes back into view, and right away when a grade is saved in another tab (speed grader).
            const GB_LIVE_MS = 15000;
            const GB_SUBJ = <?= json_encode((object) array_column($mySubjects, 'course_name', 'course_id'), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
            let gbSig = '', gbInFlight = false, gbSaving = 0, gbLastLoad = 0, gbChan = null;
            try { if ('BroadcastChannel' in window) gbChan = new BroadcastChannel('elms_grades'); } catch (e) { gbChan = null; }
            const gbPing = () => { try { if (gbChan) gbChan.postMessage({ t: 'grades', at: Date.now() }); } catch (e) { } };

            const $gb = id => document.getElementById(id);
            const gbEsc = s => String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
            const gbCourse = () => ($gb('grades-course-select') || {}).value || '';
            const gbSection = () => (window.classSection ? window.classSection() : '') || '';
            const gbOpen = id => { const m = $gb(id); if (m) m.classList.add('show'); };
            const gbMsg = (title, text) => `<div class="gb-empty"><strong>${gbEsc(title)}</strong>${text ? gbEsc(text) : ''}</div>`;

            // Same rules as the speed grader: whole numbers only. A decimal point or comma is kept in the box
            // on purpose so the teacher sees why it is refused (nothing gets glued together).
            const gbClean = v => String(v).replace(/[^\d.,]/g, '');
            function gbScoreErr(raw, max) {
                if (raw === '') return '';
                if (!/^\d+$/.test(raw)) return 'Whole numbers only, no decimals (e.g. 87).';
                if (Number(raw) > Number(max)) return `Enter a score from 0 to ${max}.`;
                return '';
            }

            async function gbJson(res) {
                const raw = await res.text();
                try { return JSON.parse(raw); }
                catch (e) { return { success: false, message: `Server error (${res.status}). Please try again.` }; }
            }

            // ── Load the class ─────────────────────────────────────────
            function gbEditing() {
                const a = document.activeElement;
                return !!(a && a.classList && a.classList.contains('gb-in') && a.dataset.dirty === '1');
            }

            // opts.silent = a background (live) refresh: no "Loading…" flash, and a failed refresh keeps
            // the grades already on screen instead of replacing them with an error.
            async function gbLoad(opts) {
                const silent = !!(opts && opts.silent);
                const cid = gbCourse(), body = $gb('gb-body');
                if (!body) return;
                if (!cid) { ++gbReq; gbInFlight = false; GB = null; gbSig = ''; body.innerHTML = gbMsg('Select a subject above to view its grades.'); gbStats(); gbAna(); return; }
                if (gbEditing()) { gbDeferred = true; return; }      // never wipe what the teacher is typing
                const section = gbSection(), key = cid + '|' + section, my = ++gbReq;
                const fresh = !GB || GB.key !== key;
                if (fresh) {
                    GB = null; gbSig = ''; GB_RISK = []; $gb('gb-risk').hidden = true;
                    if (!silent || !body.querySelector('.gb-table')) body.innerHTML = gbMsg('Loading grades…');
                    gbStats(); gbAna();
                }
                gbInFlight = true;
                try {
                    const res = await fetch(`../api/gradebook.php?action=list&course_id=${encodeURIComponent(cid)}&section=${encodeURIComponent(section)}`, { cache: 'no-store' });
                    const d = await gbJson(res);
                    if (my !== gbReq) return;                         // a newer request already answered
                    if (!d.success) {
                        if (silent && GB) { gbLive(false, d.message); return; }
                        GB = null; gbSig = ''; body.innerHTML = gbMsg('Could not load grades.', d.message || ''); gbStats(); gbAna(); return;
                    }
                    if (gbEditing()) { gbDeferred = true; return; }   // the teacher started typing while this was loading
                    gbLastLoad = Date.now();
                    const next = { key, course: cid, section, items: d.items || [], students: d.students || [], cells: d.cells || {}, totals: d.totals || {}, manual: !!d.manual_enabled };
                    const sig = JSON.stringify([next.items, next.students, next.cells, next.totals, next.manual]);
                    gbLive(true);
                    if (GB && GB.key === key && sig === gbSig) return; // nothing changed: keep the page as it is
                    GB = next; gbSig = sig;
                    gbPaint();
                    gbLoadRisk();
                } catch (e) {
                    if (my !== gbReq) return;
                    if (silent && GB) { gbLive(false); return; }
                    body.innerHTML = gbMsg('Could not load grades.', 'Check your connection and try again.');
                } finally {
                    if (my === gbReq) gbInFlight = false;
                }
            }

            function gbLive(ok, why) {
                const el = $gb('gb-ana-live');
                if (!el) return;
                el.classList.toggle('off', !ok);
                const t = gbLastLoad ? new Date(gbLastLoad).toLocaleTimeString([], { hour: 'numeric', minute: '2-digit', second: '2-digit' }) : '';
                el.querySelector('span').textContent = ok ? `Live · updated ${t}`
                    : (why ? `Not updating: ${why}` : `Reconnecting…${t ? ' · last update ' + t : ''}`);
            }

            function gbTick() {
                if (document.hidden || gbInFlight || gbSaving || !gbCourse()) return;
                gbLoad({ silent: true });
            }

            // ── Draw ───────────────────────────────────────────────────
            function gbKeepFocus(fn) {
                const a = document.activeElement;
                const keep = a && a.classList && a.classList.contains('gb-in')
                    ? { i: a.dataset.item, s: a.dataset.student, v: a.value, d: a.dataset.dirty, p: a.selectionStart } : null;
                fn();
                if (!keep) return;
                const el = [...document.querySelectorAll('.gb-in')].find(x => x.dataset.item === keep.i && x.dataset.student === keep.s);
                if (!el) return;
                if (keep.d === '1') { el.value = keep.v; el.dataset.dirty = '1'; }
                el.focus();
                try { el.setSelectionRange(keep.p, keep.p); } catch (e) { }
            }

            function gbPaint() { gbPaintTable(); gbStats(); gbAna(); }
            function gbPaintTable() {
                if (!GB) return;
                const old = $gb('gb-body').querySelector('.gb-scroll');
                const pos = old ? [old.scrollLeft, old.scrollTop] : null;   // a live refresh must not jump the table
                gbKeepFocus(() => { $gb('gb-body').innerHTML = gbTable(); });
                const sc = $gb('gb-body').querySelector('.gb-scroll');
                if (sc && pos) { sc.scrollLeft = pos[0]; sc.scrollTop = pos[1]; }
            }

            function gbVisible() {
                const q = gbQuery.trim().toLowerCase();
                let list = GB.students.filter(s => !q || (s.name + ' ' + (s.lrn || '')).toLowerCase().includes(q));
                if (gbSort === 'low') {
                    const pct = s => { const t = GB.totals[s.id]; return t && t.percent !== null && t.percent !== undefined ? Number(t.percent) : Infinity; };
                    list = list.slice().sort((a, b) => pct(a) - pct(b));
                }
                return list;
            }

            function gbCell(s, it) {
                const c = (GB.cells[s.id] || {})[it.id] || { state: 'none', score: null };
                if (it.type === 'manual') {
                    const v = c.state === 'graded' ? String(c.score) : '';
                    return `<td class="gb-c"><input class="gb-in" type="text" inputmode="numeric" autocomplete="off" data-item="${gbEsc(it.id)}" data-student="${s.id}" data-orig="${gbEsc(v)}" data-max="${gbEsc(it.max)}" value="${gbEsc(v)}" aria-label="${gbEsc(s.name + ' — ' + it.title)}"></td>`;
                }
                let inner;
                if (c.state === 'graded') inner = `<span class="gb-sc">${gbEsc(c.score)}</span>`;
                else if (c.state === 'pending') inner = '<span class="gb-chip warn">To grade</span>';
                else if (c.state === 'missing') inner = '<span class="gb-chip bad">Missing</span>';
                else inner = '<span class="gb-dash">—</span>';
                if (it.type === 'assignment' && it.source_id) {
                    inner = `<a class="gb-link" href="grade.php?id=${it.source_id}&student=${s.id}&section=${encodeURIComponent(GB.section)}" title="Open in the grader">${inner}</a>`;
                }
                return `<td class="gb-c">${inner}</td>`;
            }

            function gbTable() {
                if (!GB.students.length) return gbMsg('No students yet', 'Nobody is enrolled in this class.');
                if (!GB.items.length) {
                    return `<div class="gb-empty"><strong>Nothing to grade yet</strong>Assignments and quizzes for this class will appear here.</div>`;
                }
                const rows = gbVisible();
                let h = '<div class="gb-scroll"><table class="gb-table"><thead><tr><th class="gb-stu">Student</th>';
                GB.items.forEach(it => {
                    h += `<th class="gb-it"><div class="gb-it-title" title="${gbEsc(it.title)}">${gbEsc(it.title)}</div>`
                        + `<div class="gb-it-meta"><span class="gb-type">${GB_TYPE[it.type] || ''}</span> · ${gbEsc(it.max)} pts</div>`
                        + '</th>';
                });
                h += '<th class="gb-tot">Total</th></tr></thead><tbody>';
                if (!rows.length) h += `<tr><td colspan="${GB.items.length + 2}" class="gb-empty" style="padding:1.5rem">No student matches your search.</td></tr>`;
                rows.forEach(s => {
                    h += `<tr><td class="gb-stu"><b>${gbEsc(s.name)}</b><small>${gbEsc([s.lrn, s.section].filter(Boolean).join(' · '))}</small></td>`;
                    GB.items.forEach(it => { h += gbCell(s, it); });
                    const t = GB.totals[s.id] || {};
                    const pct = t.percent === null || t.percent === undefined ? null : Number(t.percent);
                    const cls = pct === null ? '' : (pct < gbPass ? 'bad' : 'ok');
                    h += `<td class="gb-tot"><div class="gb-tot-score">${gbEsc(t.score ?? 0)} / ${gbEsc(t.max ?? 0)}</div>`
                        + `<div class="gb-tot-pct ${cls}">${pct === null ? '—' : gbEsc(pct) + '%'}</div>`
                        + (t.pending ? `<div class="gb-tot-note">${Number(t.pending)} to grade</div>` : '') + '</td></tr>';
                });
                return h + '</tbody></table></div>';
            }

            function gbStats() {
                const el = $gb('gb-stats');
                if (!GB) { el.innerHTML = ''; return; }
                const pcts = GB.students.map(s => (GB.totals[s.id] || {}).percent).filter(p => p !== null && p !== undefined).map(Number);
                const avg = pcts.length ? (pcts.reduce((a, b) => a + b, 0) / pcts.length).toFixed(1).replace(/\.0$/, '') + '%' : '—';
                const toGrade = GB.students.reduce((n, s) => n + Number((GB.totals[s.id] || {}).pending || 0), 0);
                const a = gbAnaData();
                const comp = a.cells ? gbPct(a.submitted / a.cells * 100) : '—';
                const box = (v, l, isNew) => `<div class="gb-stat"><b>${gbEsc(v)}</b><span>${gbEsc(l)}${isNew ? '<em class="gb-new" style="font-style:normal">New</em>' : ''}</span></div>`;
                el.innerHTML = box(GB.students.length, 'Students') + box(avg, 'Class average') + box(comp, 'Completion rate', true)
                    + box(GB.items.length, 'Graded items') + box(toGrade, 'To grade');
            }

            // ── Performance analytics ──────────────────────────────────
            // Everything is worked out from the same cells the table shows, so the chart, the tiles
            // and the table can never disagree, and they all change together on every live refresh.
            const gbPct = v => (Math.round(v * 10) / 10).toFixed(1).replace(/\.0$/, '') + '%';
            // Chart label: "Dela Cruz, Juan" -> "Dela Cruz, J." (the full name is in the tooltip)
            const gbShortName = n => { const [last, first] = String(n || '').split(', '); return first ? `${last}, ${first.trim().charAt(0)}.` : String(n || ''); };

            function gbAnaData() {
                const n = GB ? GB.students.length : 0;
                const rows = (GB ? GB.items : []).map(it => {
                    const max = Number(it.max);
                    let submitted = 0, graded = 0, missing = 0, counted = 0, sum = 0;
                    GB.students.forEach(s => {
                        const c = (GB.cells[s.id] || {})[it.id];
                        const st = c ? c.state : 'none';
                        if (st === 'graded') {
                            submitted++; graded++;
                            if (max > 0 && c.score !== null && isFinite(Number(c.score))) { sum += Math.min(100, Math.max(0, Number(c.score) / max * 100)); counted++; }
                        } else if (st === 'pending') submitted++;
                        else if (st === 'missing') { missing++; counted++; }           // missing counts as 0
                    });
                    return { it, submitted, graded, pending: submitted - graded, missing, counted,
                             comp: n ? submitted / n * 100 : 0, avg: counted ? sum / counted : null };
                });
                const sum = k => rows.reduce((t, r) => t + r[k], 0);
                let low = null;
                rows.forEach(r => { if (!low || r.comp < low.comp) low = r; });   // ties: the earlier item
                const withAvg = GB ? GB.students.filter(s => { const p = (GB.totals[s.id] || {}).percent; return p !== null && p !== undefined; }) : [];
                const risk = withAvg.filter(s => Number(GB.totals[s.id].percent) < gbPass).length;
                // Per student (the chart): same cells and the same total % as the table
                const items = GB ? GB.items : [];
                const stu = (GB ? GB.students : []).map(s => {
                    let submitted = 0, graded = 0, missing = 0;
                    items.forEach(it => {
                        const st = ((GB.cells[s.id] || {})[it.id] || {}).state || 'none';
                        if (st === 'graded') { submitted++; graded++; }
                        else if (st === 'pending') submitted++;
                        else if (st === 'missing') missing++;
                    });
                    const t = GB.totals[s.id] || {};
                    const avg = t.percent === null || t.percent === undefined || !isFinite(Number(t.percent)) ? null : Number(t.percent);
                    return { s, submitted, graded, pending: submitted - graded, missing, items: items.length,
                             comp: items.length ? submitted / items.length * 100 : 0, avg };
                });
                // highest average first (no average yet: last); ties by completion, then by name, so the order never jumps
                stu.sort((x, y) => (y.avg ?? -1) - (x.avg ?? -1) || y.comp - x.comp || String(x.s.name).localeCompare(String(y.s.name)));
                const rated = stu.filter(r => r.avg !== null);
                const top = rated.length ? rated[0] : null;
                const focus = rated.length ? rated[rated.length - 1] : null;
                return { n, rows, stu, top, focus, cells: n * rows.length, submitted: sum('submitted'), missing: sum('missing'), low, risk, rated: withAvg.length };
            }

            function gbAna() {
                const card = $gb('gb-ana');
                gbTipHide();
                if (!GB || !GB.students.length || !GB.items.length) { card.hidden = true; return; }
                const a = gbAnaData();
                $gb('gb-ana-class').textContent = (GB_SUBJ[GB.course] || 'Subject') + ' · ' + (GB.section || 'All sections');

                const old = $gb('gb-ana-chart').querySelector('.gb-ana-scroll');
                const left = old ? old.scrollLeft : 0;
                const act = document.activeElement;
                const focusI = act && act.classList && act.classList.contains('gb-ana-g') && $gb('gb-ana-chart').contains(act) ? act.dataset.i : null;
                let y = '', grid = '';
                [0, 25, 50, 75, 100].forEach(p => {
                    y += `<span style="bottom:${p}%">${p}%</span>`;
                    grid += `<div class="gb-ana-grid${p === 0 ? ' base' : ''}" style="bottom:${p}%"></div>`;
                });
                const h = v => v > 0 ? `max(2px, ${Math.min(100, v).toFixed(2)}%)` : '0';
                let groups = '', labels = '';
                a.stu.forEach((r, i) => {
                    const avgTxt = r.avg === null ? 'no score counted yet' : gbPct(r.avg);
                    const aria = `${r.s.name}${r.s.section ? ' (' + r.s.section + ')' : ''}: completion ${gbPct(r.comp)}, ${r.submitted} of ${r.items} submitted; average score ${avgTxt}; ${r.missing} missing`;
                    groups += `<div class="gb-ana-g" tabindex="0" role="img" data-i="${i}" aria-label="${gbEsc(aria)}">`
                        + `<div class="gb-ana-bar c1" style="height:${h(r.comp)}"></div>`
                        + `<div class="gb-ana-bar c2" style="height:${h(r.avg || 0)}"></div></div>`;
                    labels += `<div title="${gbEsc(r.s.name)}">${gbEsc(gbShortName(r.s.name))}</div>`;
                });
                const minW = a.stu.length * 76;
                $gb('gb-ana-chart').innerHTML = `<div class="gb-ana-chart"><div class="gb-ana-y" aria-hidden="true">${y}</div>`
                    + `<div class="gb-ana-scroll"><div class="gb-ana-inner" style="min-width:${minW}px">`
                    + `<div class="gb-ana-plot">${grid}${groups}</div><div class="gb-ana-labels" aria-hidden="true">${labels}</div></div></div></div>`;
                const sc = $gb('gb-ana-chart').querySelector('.gb-ana-scroll');
                if (sc) { sc.scrollLeft = left; sc.addEventListener('scroll', gbTipHide, { passive: true }); }
                if (focusI !== null) { const g = $gb('gb-ana-chart').querySelector(`.gb-ana-g[data-i="${focusI}"]`); if (g) g.focus({ preventScroll: true }); }

                const tile = (label, value, sub, tone) => `<div class="gb-ana-tile"><span>${gbEsc(label)}</span><b class="${tone || ''}" title="${gbEsc(value)}">${gbEsc(value)}</b>${sub ? `<small>${gbEsc(sub)}</small>` : ''}</div>`;
                $gb('gb-ana-tiles').innerHTML =
                    tile('Top performer', a.top ? a.top.s.name : '—', a.top ? `${gbPct(a.top.avg)} average · ${gbPct(a.top.comp)} completed` : 'No graded or missing work yet', a.top && a.top.avg >= gbPass ? 'ok' : '')
                    + tile('Needs the most focus', a.focus && a.focus !== a.top ? a.focus.s.name : '—',
                           a.focus && a.focus !== a.top ? `${gbPct(a.focus.avg)} average · ${a.focus.missing} missing` : (a.top ? 'Only one student has a grade so far' : 'No graded or missing work yet'),
                           a.focus && a.focus !== a.top && a.focus.avg < gbPass ? 'bad' : '')
                    + tile('Students at risk', a.rated ? `${a.risk} of ${a.rated}` : '0', a.rated ? `Average below ${gbPass}%` : 'No graded or missing work yet', a.risk > 0 ? 'bad' : '')
                    + tile('Missing submissions', `${a.missing} of ${a.cells}`, a.cells ? gbPct(a.missing / a.cells * 100) + ' of all work' : '', '');
                card.hidden = false;
            }

            let gbTipG = null;
            function gbTipShow(g) {
                const tip = $gb('gb-ana-tip');
                if (!GB || !g || !g.isConnected) { gbTipHide(); return; }
                const a = gbAnaData(), r = a.stu[Number(g.dataset.i)];
                if (!r) { gbTipHide(); return; }
                gbTipG = g;
                const status = r.avg === null ? 'No grade yet' : (r.avg >= gbPass ? 'Passing' : `Below ${gbPass}% · needs focus`);
                tip.innerHTML = `<b>${gbEsc(r.s.name)}</b><small>${gbEsc(r.s.section || '')}${r.s.lrn ? ' · ' + gbEsc(r.s.lrn) : ''}</small><br>`
                    + `<i style="background:var(--gb-c1)"></i>Completion <strong>${gbPct(r.comp)}</strong> <small>(${r.submitted} of ${r.items} submitted)</small><br>`
                    + `<i style="background:var(--gb-c2)"></i>Average <strong>${r.avg === null ? '—' : gbPct(r.avg)}</strong> <small>(${gbEsc(status)})</small><br>`
                    + `<small>${r.missing} missing${r.pending ? ` · ${r.pending} to grade` : ''}</small>`;
                tip.hidden = false;
                const b = g.getBoundingClientRect(), t = tip.getBoundingClientRect();
                let x = b.left + b.width / 2 - t.width / 2, y = b.top - t.height - 8;
                x = Math.max(8, Math.min(x, window.innerWidth - t.width - 8));
                if (y < 8) y = Math.min(b.bottom + 8, window.innerHeight - t.height - 8);
                tip.style.left = x + 'px'; tip.style.top = y + 'px';
            }
            function gbTipHide() { gbTipG = null; const tip = $gb('gb-ana-tip'); if (tip) tip.hidden = true; }
            // Page scroll / resize: keep the tooltip on its bars (Tab can scroll the page), or hide it if they left the screen
            function gbTipMove() {
                if (!gbTipG) return;
                const b = gbTipG.getBoundingClientRect();
                if (!gbTipG.isConnected || b.bottom < 0 || b.top > window.innerHeight) gbTipHide(); else gbTipShow(gbTipG);
            }

            function gbExport(e) {
                const cid = gbCourse();
                if (!cid) { e.preventDefault(); return false; }
                $gb('gb-export').href = `../api/gradebook.php?action=export&course_id=${encodeURIComponent(cid)}&section=${encodeURIComponent(gbSection())}`;
                return true;
            }

            // ── Needs support (AI insights) ───────────────────────────
            async function gbLoadRisk() {
                const card = $gb('gb-risk'), list = $gb('gb-risk-list');
                if (!GB) return;
                const key = GB.key;
                try {
                    const res = await fetch(`../api/ai_insights.php?action=at_risk&course_id=${encodeURIComponent(GB.course)}&section=${encodeURIComponent(GB.section)}`);
                    const d = await gbJson(res);
                    if (!GB || GB.key !== key) return;
                    if (!d.success) { card.hidden = true; return; }
                    const pass = Number(d.threshold) || 75;
                    GB_RISK = d.data || [];
                    $gb('gb-risk-sub').textContent = `Average below ${pass}%`;
                    list.innerHTML = GB_RISK.length ? GB_RISK.map(r =>
                        `<div class="gb-risk-row"><div class="gb-risk-who"><b>${gbEsc(r.name)}</b><small>${gbEsc(r.section_dept || '')} · ${Number(r.graded_count)} graded · ${Number(r.missing_count)} missing</small></div>`
                        + `<div class="gb-risk-avg">${gbEsc(r.average)}%</div>`
                        + `<button type="button" class="btn btn-purple btn-sm" data-plan="${Number(r.id)}">AI plan</button></div>`).join('')
                        : `<div class="gb-risk-ok">Nobody is below ${pass}% right now.</div>`;
                    card.hidden = false;
                    if (pass !== gbPass) { gbPass = pass; gbPaintTable(); gbStats(); gbAna(); }
                } catch (e) { card.hidden = true; }
            }

            async function gbPlan(sid) {
                const r = GB_RISK.find(x => Number(x.id) === sid);
                if (!r || !GB) return;
                const my = ++gbPlanSeq;
                $gb('gb-plan-name').textContent = r.name;
                $gb('gb-plan-text').textContent = 'Thinking about a plan…';
                $gb('gb-plan-weak').textContent = '';
                gbOpen('gbPlanModal');
                try {
                    const res = await fetch('../api/ai_insights.php?action=suggest_plan', {
                        method: 'POST', headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ student_id: sid, course_id: Number(GB.course) })
                    });
                    const d = await gbJson(res);
                    if (my !== gbPlanSeq) return;
                    if (!d.success) { $gb('gb-plan-text').textContent = d.message || 'Could not get a plan. Try again.'; return; }
                    $gb('gb-plan-text').textContent = d.plan || '';
                    const weak = d.weak_items || [];
                    const w = $gb('gb-plan-weak');
                    w.textContent = '';
                    if (weak.length) {
                        const b = document.createElement('b'); b.textContent = 'Lowest scores: ';
                        w.appendChild(b);
                        w.appendChild(document.createTextNode(weak.map(x => `${x.label} (${x.percent}%)`).join(', ')));
                    }
                } catch (e) {
                    if (my === gbPlanSeq) $gb('gb-plan-text').textContent = 'Network error. Try again.';
                }
            }

            // ── Type a score into a manual item ────────────────────────
            async function gbSave(el) {
                const orig = el.dataset.orig;
                const raw = gbClean(el.value.trim());
                el.dataset.dirty = '0';
                el.classList.remove('bad');
                if (raw === orig) { el.value = orig; return; }
                if (raw !== '') {
                    const m = gbScoreErr(raw, el.dataset.max);
                    if (m) { el.classList.add('bad'); showToast(m, true); el.value = orig; return; }
                }
                const val = raw === '' ? '' : String(Number(raw));
                if (val === orig) { el.value = orig; return; }
                el.classList.add('saving');
                gbSaving++;
                try {
                    const res = await fetch('../api/gradebook.php?action=save_score', {
                        method: 'POST', headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ item_id: Number(el.dataset.item), student_id: Number(el.dataset.student), score: val })
                    });
                    const d = await gbJson(res);
                    if (!d.success) { el.classList.add('bad'); el.value = orig; showToast(d.message || 'Could not save the score.', true); return; }
                    el.dataset.orig = val; el.value = val;
                    showToast(d.message || 'Score saved.');
                    gbPing();
                    gbLoad();                                         // totals and averages come from the server
                } catch (e) {
                    el.value = orig; showToast('Network error. The score was not saved.', true);
                } finally {
                    gbSaving = Math.max(0, gbSaving - 1);
                    el.classList.remove('saving');
                    if (gbDeferred && !gbEditing()) { gbDeferred = false; gbLoad(); }
                }
            }

            // ── Wiring ─────────────────────────────────────────────────
            (function () {
                const body = $gb('gb-body');
                body.addEventListener('input', e => {
                    const el = e.target;
                    if (!el.classList || !el.classList.contains('gb-in')) return;
                    const c = gbClean(el.value);
                    if (c !== el.value) el.value = c;
                    el.dataset.dirty = '1'; el.classList.remove('bad');
                });
                body.addEventListener('change', e => { if (e.target.classList && e.target.classList.contains('gb-in')) gbSave(e.target); });
                body.addEventListener('keydown', e => {
                    const el = e.target;
                    if (!el.classList || !el.classList.contains('gb-in')) return;
                    if (e.key === 'Escape') { el.value = el.dataset.orig; el.dataset.dirty = '0'; el.classList.remove('bad'); el.blur(); }
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        const col = [...body.querySelectorAll('.gb-in')].filter(x => x.dataset.item === el.dataset.item);
                        const next = col[col.indexOf(el) + 1];
                        if (next) next.focus(); else el.blur();      // leaving the box saves it
                    }
                });
                body.addEventListener('focusout', () => { if (gbDeferred) setTimeout(() => { if (!gbEditing()) { gbDeferred = false; gbLoad(); } }, 0); });
                $gb('gb-risk-list').addEventListener('click', e => {
                    const b = e.target.closest('[data-plan]');
                    if (b) gbPlan(Number(b.dataset.plan));
                });
                $gb('gb-q').addEventListener('input', e => { gbQuery = e.target.value; gbPaintTable(); });
                document.querySelectorAll('.gb-pill').forEach(p => p.addEventListener('click', () => {
                    gbSort = p.dataset.sort;
                    document.querySelectorAll('.gb-pill').forEach(x => x.classList.toggle('on', x === p));
                    gbPaintTable();
                }));
                $gb('gbPlanModal').addEventListener('click', e => { if (e.target === e.currentTarget) closeModal('gbPlanModal'); });
                document.addEventListener('keydown', e => { if (e.key === 'Escape') { closeModal('gbPlanModal'); gbTipHide(); } });

                // Chart tooltip: mouse, keyboard (Tab) and touch (tap)
                const chart = $gb('gb-ana-chart');
                const grp = e => e.target.closest && e.target.closest('.gb-ana-g');
                chart.addEventListener('mouseover', e => { const g = grp(e); if (g) gbTipShow(g); else gbTipHide(); });
                chart.addEventListener('mouseleave', gbTipHide);
                chart.addEventListener('focusin', e => { const g = grp(e); if (g) gbTipShow(g); });
                chart.addEventListener('focusout', gbTipHide);
                chart.addEventListener('click', e => { const g = grp(e); if (g) gbTipShow(g); });
                window.addEventListener('scroll', gbTipMove, { passive: true });
                window.addEventListener('resize', gbTipMove);

                // Real time
                setInterval(gbTick, GB_LIVE_MS);
                document.addEventListener('visibilitychange', () => { if (!document.hidden && Date.now() - gbLastLoad > 2000) gbTick(); });
                if (gbChan) gbChan.onmessage = () => { gbTick(); if (gbInFlight || gbSaving) setTimeout(gbTick, 1500); };
            })();
        </script>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>