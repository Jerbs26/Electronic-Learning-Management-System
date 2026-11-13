<?php
// ============================================================
//  Arandia College eLMS — Teacher: Attendance
//  File: teacher/attendance.php
// ============================================================
require __DIR__ . '/_init.php';
$currentPage = 'attendance.php';
$pageTitle = 'Attendance';

$classOptions = [];
foreach ($mySubjects as $s) {
    $cid = (int) $s['course_id'];
    $classOptions[$cid] = $classOptions[$cid] ?? ['label' => $s['course_code'] . ' — ' . $s['course_name'], 'sections' => []];
    $classOptions[$cid]['sections'][] = $s['section'];
}
require_once __DIR__ . '/../includes/class_picker.php';
require __DIR__ . '/../includes/header.php';
?>
<style>
    .at { --a-soft: #eef3ff; --a-strong: #003087; --a-line: rgba(15, 23, 42, .08); }
    html[data-theme="dark"] .at { --a-soft: rgba(143, 176, 255, .12); --a-strong: #c7d7ff; --a-line: rgba(255, 255, 255, .08); }
    .at-head { display: flex; justify-content: space-between; align-items: flex-end; gap: 1rem; flex-wrap: wrap; margin-bottom: 1rem; }
    .at-title { font-family: 'Nunito', sans-serif; font-size: 1.6rem; font-weight: 900; color: var(--text-dark); }
    .at-sub { font-size: .86rem; color: var(--text-light); margin-top: .25rem; }
    .at-bar { display: flex; gap: .75rem; align-items: flex-end; flex-wrap: wrap; background: var(--white); border-radius: 16px; box-shadow: 0 2px 14px rgba(0, 0, 0, .06); padding: 1rem 1.25rem; margin-bottom: 1rem; }
    .at-bar .form-grp { flex: 1; min-width: 260px; margin: 0; }
    .at-nav { display: flex; align-items: center; gap: .35rem; }
    .at-nav button { height: 42px; min-width: 42px; padding: 0 .9rem; border-radius: 10px; border: 1px solid var(--a-line); background: var(--white); color: var(--text-dark); font: inherit; font-size: .88rem; font-weight: 700; cursor: pointer; }
    .at-nav button:hover:not(:disabled) { background: var(--bg-light); }
    .at-nav button:disabled { opacity: .4; cursor: default; }
    .at-nav .at-day { min-width: 190px; position: relative; }
    .at-nav input[type=date] { position: absolute; inset: 0; opacity: 0; cursor: pointer; }
    .at-tabs { display: flex; gap: .25rem; border-bottom: 1px solid var(--a-line); margin-bottom: 1rem; }
    .at-tab { padding: .65rem 1rem; font: inherit; font-size: .9rem; font-weight: 800; color: var(--text-light); background: none; border: 0; border-bottom: 2px solid transparent; margin-bottom: -1px; cursor: pointer; }
    .at-tab.on { color: var(--a-strong); border-bottom-color: var(--a-strong); }
    .at-card { background: var(--white); border-radius: 16px; box-shadow: 0 2px 14px rgba(0, 0, 0, .06); overflow: hidden; }
    .at-top { display: flex; justify-content: space-between; align-items: center; gap: 1rem; padding: .85rem 1.25rem; border-bottom: 1px solid var(--a-line); flex-wrap: wrap; }
    .at-state { font-size: .85rem; font-weight: 700; color: var(--text-light); }
    .at-state.saved { color: #00875a; }
    .at-btn { display: inline-flex; align-items: center; gap: .35rem; padding: .55rem 1rem; border-radius: 10px; border: 1px solid var(--a-line); background: var(--white); color: var(--text-dark); font: inherit; font-size: .84rem; font-weight: 800; cursor: pointer; text-decoration: none; }
    .at-btn:hover { background: var(--bg-light); }
    .at-btn.primary { background: #003087; border-color: #003087; color: #fff; }
    .at-btn.primary:hover { background: #0044cc; }
    .at-btn:disabled { opacity: .5; cursor: default; }
    .at-row { display: grid; grid-template-columns: minmax(0, 1fr) auto minmax(140px, 220px); gap: 1rem; align-items: center; padding: .75rem 1.25rem; border-bottom: 1px solid var(--a-line); outline: none; }
    .at-row.focus { background: var(--bg-light); }
    .at-name { font-weight: 700; color: var(--text-dark); }
    .at-lrn { font-size: .74rem; color: var(--text-light); }
    .at-av { width: 36px; height: 36px; border-radius: 50%; background: var(--a-soft); color: var(--a-strong); font-weight: 800; font-size: .74rem; display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .at-seg { display: inline-flex; border: 1px solid var(--a-line); border-radius: 10px; overflow: hidden; }
    .at-seg button { padding: .5rem .85rem; font: inherit; font-size: .82rem; font-weight: 700; color: var(--text-light); background: var(--white); border: 0; border-right: 1px solid var(--a-line); cursor: pointer; }
    .at-seg button:last-child { border-right: 0; }
    .at-seg button:hover { color: var(--text-dark); background: var(--bg-light); }
    .at-seg button.on.Present { background: #e6f6ef; color: #00875a; }
    .at-seg button.on.Absent  { background: #fdecec; color: #c0392b; }
    .at-seg button.on.Late    { background: #fff5d9; color: #b38600; }
    .at-seg button.on.Excused { background: var(--a-soft); color: var(--a-strong); }
    html[data-theme="dark"] .at-seg button.on.Present { background: rgba(0, 135, 90, .2); color: #6ee7b7; }
    html[data-theme="dark"] .at-seg button.on.Absent  { background: rgba(192, 57, 43, .2); color: #fca5a5; }
    html[data-theme="dark"] .at-seg button.on.Late    { background: rgba(179, 134, 0, .2); color: #fcd34d; }
    .at-remark { width: 100%; height: 36px; border: 1px solid var(--a-line); border-radius: 8px; padding: 0 .65rem; font: inherit; font-size: .82rem; background: var(--bg-light); color: var(--text-dark); box-sizing: border-box; }
    .at-save { position: sticky; bottom: 0; display: flex; justify-content: space-between; align-items: center; gap: 1rem; flex-wrap: wrap; padding: .85rem 1.25rem; background: var(--bg-light); border-top: 1px solid var(--a-line); }
    .at-counts { display: flex; gap: 1rem; flex-wrap: wrap; font-size: .82rem; color: var(--text-light); }
    .at-counts b { color: var(--text-dark); }
    .at-dot { display: inline-block; width: 8px; height: 8px; border-radius: 50%; margin-right: .3rem; }
    .at-left { color: #b38600; font-weight: 700; }
    .at-hint { font-size: .74rem; color: var(--text-light); margin-top: .5rem; }
    .at-hint kbd { border: 1px solid var(--a-line); border-radius: 4px; padding: 0 .3rem; font: inherit; font-size: .72rem; }
    .at-empty { text-align: center; padding: 3rem 1rem; color: var(--text-light); }
    .at-empty strong { display: block; color: var(--text-dark); font-size: 1rem; margin-bottom: .25rem; }
    .at-grid { border-collapse: separate; border-spacing: 0; width: 100%; font-size: .82rem; }
    .at-grid th, .at-grid td { padding: .7rem .6rem; border-bottom: 1px solid var(--a-line); text-align: center; white-space: nowrap; }
    .at-grid thead th { background: var(--bg-light); font-size: .72rem; font-weight: 800; color: var(--text-light); }
    .at-grid .stu { position: sticky; left: 0; background: var(--white); text-align: left; padding-left: 1.25rem; min-width: 190px; box-shadow: 1px 0 0 var(--a-line); }
    .at-grid thead .stu { background: var(--bg-light); }
    .at-grid .rate { position: sticky; right: 0; background: var(--white); box-shadow: -1px 0 0 var(--a-line); min-width: 80px; font-weight: 800; }
    .at-grid thead .rate { background: var(--bg-light); }
    .at-day-h { cursor: pointer; }
    .at-day-h:hover { color: var(--a-strong); }
    .at-grid td.at-go { cursor: pointer; transition: background .12s; }
    .at-grid td.at-go:hover { background: var(--a-soft); }
    .at-past { display: flex; align-items: center; gap: .5rem; padding: .7rem 1.25rem; background: #fff8e1; color: #8a6d00; border-bottom: 1px solid #f3e3a5; font-size: .84rem; font-weight: 700; }
    .at-c { display: inline-block; width: 24px; height: 24px; line-height: 24px; border-radius: 6px; font-weight: 800; font-size: .72rem; text-align: center; }
    .at-c.P { background: #e6f6ef; color: #00875a; } .at-c.A { background: #fdecec; color: #c0392b; }
    .at-c.L { background: #fff5d9; color: #b38600; } .at-c.E { background: var(--a-soft); color: var(--a-strong); }
    .at-h { font-family: 'Nunito', sans-serif; font-size: 1.05rem; font-weight: 900; color: var(--text-dark); }
    .at-wd { display: block; font-size: .66rem; font-weight: 600; color: var(--text-light); text-transform: none; }
    .at-future { color: var(--text-light); opacity: .6; }
    .at-none { color: var(--text-light); }
    .at-grid td .at-c { width: 30px; height: 30px; line-height: 30px; font-size: .88rem; }
    .at-legend { display: flex; flex-wrap: wrap; align-items: center; gap: 1.1rem; padding: .85rem 1.25rem; border-top: 1px solid var(--a-line); font-size: .82rem; color: var(--text-dark); }
    .at-legend > span { display: inline-flex; align-items: center; gap: .4rem; }
    .at-flag { display: block; font-size: .7rem; font-weight: 700; color: #c0392b; }
    .at-low { color: #c0392b; }
    /* History table: grouped headers, status totals, weekly and overall attendance */
    .at-grid thead tr.at-grp th { padding: .55rem .6rem .25rem; font-size: .66rem; font-weight: 800; letter-spacing: .06em; text-transform: uppercase; color: var(--text-light); border-bottom: 0; }
    .at-grid thead tr.at-sub th { padding-top: .25rem; }
    .at-grid .sep { box-shadow: inset 1px 0 0 var(--a-line); }
    .at-sh { display: inline-flex; align-items: center; gap: .35rem; }
    .at-sh i { width: 8px; height: 8px; border-radius: 50%; display: inline-block; }
    .at-num { font-variant-numeric: tabular-nums; font-weight: 700; color: var(--text-dark); min-width: 64px; }
    .at-num.z { color: var(--text-light); font-weight: 500; }   /* no opacity: pinned cells must stay solid */
    .at-num.bad { color: #c0392b; }
    .at-num.warn { color: #b38600; }
    html[data-theme="dark"] .at-num.bad { color: #ff8a80; }
    html[data-theme="dark"] .at-num.warn { color: #f2c66d; }
    .at-pct { font-variant-numeric: tabular-nums; font-weight: 800; min-width: 76px; }
    .at-pct small { display: block; font-size: .66rem; font-weight: 600; color: var(--text-light); }
    /* The history table scrolls inside its card. width:0 + min-width:100% keeps the wide table from
       stretching the page (the layout's flex main area would otherwise grow and hide the right side). */
    .at-scroll { width: 0; min-width: 100%; overflow-x: auto; overflow-y: hidden; scrollbar-width: thin; scrollbar-color: rgba(100, 116, 139, .55) transparent; }
    .at-scroll::-webkit-scrollbar { height: 10px; }
    .at-scroll::-webkit-scrollbar-track { background: var(--a-line); border-radius: 10px; margin: 0 .5rem; }
    .at-scroll::-webkit-scrollbar-thumb { background: rgba(100, 116, 139, .55); border-radius: 10px; border: 2px solid transparent; background-clip: padding-box; }
    .at-scroll::-webkit-scrollbar-thumb:hover { background-color: rgba(100, 116, 139, .8); }
    .at-scroll-hint { display: none; font-size: .72rem; color: var(--text-light); margin-top: .2rem; }
    .at-scroll-hint.show { display: block; }
    /* All-days history grid: fixed column widths so the sticky right-hand columns line up exactly */
    .at-grid.all { --w-stu: 200px; --w-day: 50px; --w-cnt: 76px; --w-ov: 96px; width: 100%; }
    .at-grid.all th, .at-grid.all td { box-sizing: border-box; padding: .55rem .25rem; }
    .at-grid.all .stu { width: var(--w-stu); min-width: var(--w-stu); max-width: var(--w-stu); padding-left: 1.25rem; z-index: 3; }
    .at-grid.all .day { width: var(--w-day); min-width: var(--w-day); max-width: var(--w-day); }
    .at-grid.all .day.wk { box-shadow: inset 1px 0 0 var(--a-line); }
    .at-grid.all th.day { line-height: 1.15; font-size: .8rem; }
    .at-grid.all .day.today { background: var(--a-soft); }
    .at-grid.all th.mo { text-align: left; padding-left: .6rem; box-shadow: inset 1px 0 0 var(--a-line); }
    .at-grid.all th.mo span { position: sticky; left: calc(var(--w-stu) + .6rem); }   /* month name stays in view while scrolling */
    .at-grid.all .stu .at-name { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .at-grid.all .fill { width: auto; padding: 0; }
    .at-grid.all .cnt, .at-grid.all .ov, .at-grid.all .tot-h { position: sticky; z-index: 2; background: var(--white); }
    .at-grid.all thead .cnt, .at-grid.all thead .ov, .at-grid.all thead .tot-h { background: var(--bg-light); }
    .at-grid.all .cnt { width: var(--w-cnt); min-width: var(--w-cnt); max-width: var(--w-cnt); }
    .at-grid.all .ov { right: 0; width: var(--w-ov); min-width: var(--w-ov); max-width: var(--w-ov); box-shadow: inset 1px 0 0 var(--a-line); }
    .at-grid.all .c3 { right: var(--w-ov); }
    .at-grid.all .c2 { right: calc(var(--w-ov) + var(--w-cnt)); }
    .at-grid.all .c1 { right: calc(var(--w-ov) + 2 * var(--w-cnt)); }
    .at-grid.all .c0 { right: calc(var(--w-ov) + 3 * var(--w-cnt)); box-shadow: -6px 0 8px -6px rgba(0, 0, 0, .35), inset 1px 0 0 var(--a-line); }
    .at-grid.all .tot-h { right: var(--w-ov); box-shadow: -6px 0 8px -6px rgba(0, 0, 0, .35), inset 1px 0 0 var(--a-line); }
    .at-grid.all .stu { box-shadow: 6px 0 8px -6px rgba(0, 0, 0, .35), inset -1px 0 0 var(--a-line); }
    /* Phones: only Overall stays pinned on the right, so the days still have room */
    @media (max-width: 760px) {
        .at-grid.all { --w-stu: 130px; }
        .at-grid.all .cnt, .at-grid.all .tot-h { position: static; box-shadow: none; }
        .at-grid.all .c0, .at-grid.all .tot-h { box-shadow: inset 1px 0 0 var(--a-line); }
    }
    .at-live { display: inline-flex; align-items: center; gap: .3rem; margin-top: .2rem; font-size: .7rem; color: var(--text-light); }
    .at-state + .at-live { margin-left: .7rem; }
    .at-live:empty { display: none; }
    .at-live i { width: 7px; height: 7px; border-radius: 50%; background: #00875a; display: inline-block; }
    .at-live.off i { background: #b38600; }
    .at-note { flex-basis: 100%; font-size: .76rem; color: var(--text-light); line-height: 1.5; }
    @media (max-width: 860px) { .at-row { grid-template-columns: minmax(0, 1fr); gap: .5rem; } .at-seg { width: 100%; } .at-seg button { flex: 1; padding: .6rem .3rem; } }
</style>

<div class="section-panel active at">
    <?php renderClassBack($mySubjects); ?>
    <div class="cp-row">
        <div class="cp-row-left">
            <div><div class="cp-row-title">Attendance</div></div>
            <?php renderClassPicker($conn, $mySubjects, (int) $user_id, 'att-course-select', 'att'); ?>
        </div>
        <div class="cp-row-actions">
        <div class="at-nav" id="at-day-nav">
            <button type="button" onclick="atShiftDay(-1)" aria-label="Previous day">‹</button>
            <button type="button" class="at-day" id="at-day-label">Today<input type="date" id="at-date" aria-label="Pick a date" onchange="atSetDay(this.value)"></button>
            <button type="button" id="at-next" onclick="atShiftDay(1)" aria-label="Next day">›</button>
        </div>
        <div class="at-nav" id="at-month-nav" style="display:none;">
            <button type="button" onclick="atShiftWeek(-1)" aria-label="Previous week">‹</button>
            <button type="button" class="at-day" id="at-month-label" style="cursor:default;"></button>
            <button type="button" id="at-mnext" onclick="atShiftWeek(1)" aria-label="Next week">›</button>
        </div>
        </div>
    </div>
    <select id="att-course-select" onchange="atChangeClass()" aria-hidden="true" tabindex="-1" class="cp-hidden-select">
        <option value=""></option>
        <?php foreach ($classOptions as $cid => $o): ?><option value="<?= $cid ?>"></option><?php endforeach; ?>
    </select>

    <div class="at-tabs" role="tablist">
        <button class="at-tab on" id="tab-take" onclick="atTab('take')">Take attendance</button>
        <button class="at-tab" id="tab-hist" onclick="atTab('hist')">History</button>
    </div>

    <div id="at-take"><div class="at-card"><div class="at-empty"><strong>Choose a class</strong>Use the class button next to the title to pick a section and subject.</div></div></div>
    <div id="at-hist" style="display:none;"></div>
</div>

<script>
(() => {
    const STATUSES = ['Present', 'Absent', 'Late', 'Excused'];
    const KEYS = { p: 'Present', a: 'Absent', l: 'Late', e: 'Excused' };
    const LETTER = { Present: 'P', Absent: 'A', Late: 'L', Excused: 'E' };
    const pad = n => String(n).padStart(2, '0');
    const iso = d => `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`;
    const esc = s => { const x = document.createElement('div'); x.textContent = s ?? ''; return x.innerHTML; };
    const TODAY = iso(new Date());
    const isWeekend = d => { const w = new Date(d + 'T00:00:00').getDay(); return w === 0 || w === 6; };
    const addDays = (d, n) => { const x = new Date(d + 'T00:00:00'); x.setDate(x.getDate() + n); return iso(x); };
    const prevWeekday = d => { while (isWeekend(d)) d = addDays(d, -1); return d; };
    const mondayOf = d => { const w = new Date(d + 'T00:00:00').getDay() || 7; return addDays(d, 1 - w); };
    const LAST_SCHOOL_DAY = prevWeekday(TODAY);          // today, or last Friday on weekends
    let day = LAST_SCHOOL_DAY, week = mondayOf(TODAY), tab = 'take';
    let students = [], marks = {}, remarks = {}, dirty = false, focusIdx = 0, loadedOnce = false, saveLabel = 'Save attendance';
    // Live updates: both tabs re-read the server every LIVE_MS while the page is visible, right away when
    // the page comes back into view, and at once when attendance is saved in another tab of this browser.
    // Nothing the teacher is marking or typing is ever replaced (unsaved changes pause the Take tab).
    const LIVE_MS = 15000;
    let daySig = '', histSig = '', daySeq = 0, histSeq = 0, saving = false, lastLive = 0, chan = null, dayKey = '';
    try { if ('BroadcastChannel' in window) chan = new BroadcastChannel('elms_attendance'); } catch (e) { chan = null; }
    const $ = id => document.getElementById(id);
    const cid = () => $('att-course-select').value;
    const secQ = () => '&section=' + encodeURIComponent(window.classSection ? classSection() : '');

    function dayLabel(d) {
        const dt = new Date(d + 'T00:00:00');
        const txt = dt.toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric' });
        return d === TODAY ? `${txt} · Today` : txt;
    }
    function syncDayNav() {
        $('at-date').value = day; $('at-date').max = LAST_SCHOOL_DAY;
        $('at-day-label').firstChild.textContent = dayLabel(day);
        $('at-next').disabled = day >= LAST_SCHOOL_DAY;
    }
    window.atShiftDay = n => { let d = addDays(day, n); while (isWeekend(d)) d = addDays(d, n); atSetDay(d); };
    window.atSetDay = v => {
        if (!v) return;
        if (v > LAST_SCHOOL_DAY) v = LAST_SCHOOL_DAY;
        if (isWeekend(v)) v = prevWeekday(v);          // Monday to Friday only
        if (dirty && !confirm('You have unsaved attendance for this day. Leave without saving?')) { syncDayNav(); return; }
        day = v; syncDayNav(); loadDay();
    };
    window.atShiftWeek = n => {
        const next = addDays(week, 7 * n);
        if (next > mondayOf(TODAY)) return;
        week = next; loadHistory();
    };
    window.atTab = t => {
        tab = t;
        $('tab-take').classList.toggle('on', t === 'take'); $('tab-hist').classList.toggle('on', t === 'hist');
        $('at-take').style.display = t === 'take' ? '' : 'none'; $('at-hist').style.display = t === 'hist' ? '' : 'none';
        $('at-day-nav').style.display = t === 'take' ? '' : 'none'; $('at-month-nav').style.display = 'none';
        // Remember the tab in the address (#history) so a reload — by the teacher or by the site's
        // live updater — opens the same tab again instead of jumping back to Take attendance.
        try { history.replaceState(history.state, '', location.pathname + location.search + (t === 'hist' ? '#history' : '')); } catch (e) { }
        if (t === 'hist') loadHistory();
        else if (cid() && dayKey !== `${cid()}|${secQ()}|${day}`) loadDay();   // class or day changed while on History
    };
    window.atChangeClass = () => {
        if (dirty && !confirm('You have unsaved attendance. Switch class without saving?')) return;
        dirty = false; tab === 'take' ? loadDay() : loadHistory();
    };

    async function getJSON(url, opts) {
        const res = await fetch(url, opts), raw = await res.text();
        try { return JSON.parse(raw); } catch (e) { console.error(raw); throw new Error(`Server error ${res.status}`); }
    }

    // ── Take attendance ─────────────────────────────────────
    // Is the teacher in the middle of something on the Take tab? Then a live refresh must wait.
    const takeBusy = () => {
        if (dirty || saving) return true;
        const a = document.activeElement;
        return !!(a && $('at-take').contains(a) && a.matches('input, textarea, select'));
    };
    async function loadDay(opts) {
        const silent = !!(opts && opts.silent);
        loadedOnce = true;
        const wrap = $('at-take');
        if (!cid()) { ++daySeq; daySig = ''; wrap.innerHTML = '<div class="at-card"><div class="at-empty"><strong>Choose a class</strong>Use the class button next to the title to pick a section and subject.</div></div>'; return; }
        const key = `${cid()}|${secQ()}|${day}`, my = ++daySeq;
        dayKey = key;
        if (!silent) { daySig = ''; wrap.innerHTML = '<div class="at-card"><div class="at-empty">Loading…</div></div>'; }
        try {
            const d = await getJSON(`../api/attendance.php?action=list&course_id=${cid()}&date=${day}${secQ()}`, { cache: 'no-store' });
            if (my !== daySeq) return;                                 // a newer request already answered
            if (!d.success) throw new Error(d.message);
            if (silent && takeBusy()) return;                          // the teacher started marking meanwhile
            const sig = key + JSON.stringify([d.students, d.attendance, d.saved_at, d.saved_by]);
            lastLive = Date.now();
            if (silent && sig === daySig) { liveState(true); return; } // nothing changed
            daySig = sig;
            const keepFocus = silent ? focusIdx : 0;
            const hadFocus = silent && !!document.querySelector('.at-row.focus');
            students = d.students; marks = {}; remarks = {}; dirty = false;
            focusIdx = Math.min(keepFocus, Math.max(0, students.length - 1));
            students.forEach(s => { const a = d.attendance[s.id]; marks[s.id] = a ? a.status : null; remarks[s.id] = a ? a.remark : ''; });
            renderDay(d.saved_at, d.saved_by);
            if (hadFocus) document.querySelectorAll('.at-row').forEach((r, i) => r.classList.toggle('focus', i === focusIdx)); // no scrolling
            liveState(true);
        } catch (e) {
            if (my !== daySeq) return;
            if (silent && daySig) { liveState(false, e.message); return; } // keep what is on screen
            daySig = '';
            wrap.innerHTML = `<div class="at-card"><div class="at-empty"><strong>Could not load attendance</strong><span style="color:#c0392b">${esc(e.message)}</span></div></div>`;
        }
    }
    function renderDay(savedAt, savedBy) {
        const wrap = $('at-take');
        if (!students.length) { wrap.innerHTML = '<div class="at-card"><div class="at-empty"><strong>No students yet</strong>Students show up here once they are enrolled in this class.</div></div>'; return; }
        const state = savedAt ? `✓ Saved ${day === TODAY ? 'today' : dayLabel(day)} at ${savedAt}${savedBy ? ' by ' + esc(savedBy) : ''}` : 'Not saved yet';
        saveLabel = savedAt ? 'Update attendance' : 'Save attendance';
        const past = day < TODAY
            ? `<div class="at-past">✎ ${savedAt ? 'Editing attendance for ' + dayLabel(day) + '. Saving will replace the record you saved earlier.' : 'No attendance was recorded for ' + dayLabel(day) + ' yet. You can still take it now.'}</div>`
            : '';
        wrap.innerHTML = `<div class="at-card">${past}
            <div class="at-top"><div><span class="at-state ${savedAt ? 'saved' : ''}" id="at-state">${state}</span><span class="at-live" data-live></span></div>
                <button class="at-btn" type="button" onclick="atAll('Present')">✓ Mark all present</button></div>
            <div id="at-rows">${students.map((s, i) => `
                <div class="at-row" data-i="${i}" tabindex="-1">
                    <div style="display:flex;align-items:center;gap:.7rem;min-width:0;">
                        <span class="at-av">${esc(((s.first_name[0] || '') + (s.last_name[0] || '')).toUpperCase())}</span>
                        <div style="min-width:0;"><div class="at-name">${esc(s.last_name)}, ${esc(s.first_name)}</div><div class="at-lrn">LRN ${esc(s.school_id)}</div></div>
                    </div>
                    <div class="at-seg" role="radiogroup" aria-label="Status for ${esc(s.first_name)}">
                        ${STATUSES.map(st => `<button type="button" role="radio" class="${st}${marks[s.id] === st ? ' on' : ''}" aria-checked="${marks[s.id] === st}" data-sid="${s.id}" data-st="${st}">${st}</button>`).join('')}
                    </div>
                    <input class="at-remark" placeholder="Remark (optional)" maxlength="255" value="${esc(remarks[s.id])}" data-sid="${s.id}">
                </div>`).join('')}</div>
            <div class="at-save"><div class="at-counts" id="at-counts"></div><button class="at-btn primary" id="at-save-btn" type="button" onclick="atSave(this)">${saveLabel}</button></div>
        </div>
        <div class="at-hint"><kbd>↑</kbd><kbd>↓</kbd> move between students · <kbd>P</kbd> <kbd>A</kbd> <kbd>L</kbd> <kbd>E</kbd> set status · <kbd>Ctrl</kbd>+<kbd>Enter</kbd> save</div>`;
        $('at-rows').addEventListener('click', e => {
            const b = e.target.closest('button[data-st]'); if (!b) return;
            setMark(Number(b.dataset.sid), b.dataset.st);
            focusIdx = Number(b.closest('.at-row').dataset.i); paintFocus();
        });
        $('at-rows').addEventListener('input', e => { if (e.target.classList.contains('at-remark')) { remarks[e.target.dataset.sid] = e.target.value; markDirty(); } });
        updateCounts();
    }
    function setMark(sid, st) {
        marks[sid] = st; markDirty();
        document.querySelectorAll(`button[data-sid="${sid}"]`).forEach(b => {
            const on = b.dataset.st === st; b.classList.toggle('on', on); b.setAttribute('aria-checked', on);
        });
        updateCounts();
    }
    function markDirty() { dirty = true; const s = $('at-state'); if (s) { s.textContent = 'Unsaved changes'; s.className = 'at-state'; } }
    window.atAll = st => { students.forEach(s => setMark(s.id, st)); };
    function updateCounts() {
        const c = { Present: 0, Absent: 0, Late: 0, Excused: 0 };
        let left = 0;
        students.forEach(s => marks[s.id] ? c[marks[s.id]]++ : left++);
        const colors = { Present: '#00875a', Absent: '#c0392b', Late: '#b38600', Excused: '#003087' };
        $('at-counts').innerHTML = STATUSES.map(st => `<span><i class="at-dot" style="background:${colors[st]}"></i><b>${c[st]}</b> ${st.toLowerCase()}</span>`).join('')
            + (left ? `<span class="at-left">${left} not marked</span>` : '');
        $('at-save-btn').disabled = left > 0;
        $('at-save-btn').title = left ? 'Mark every student first' : '';
    }
    window.atSave = async btn => {
        if (students.some(s => !marks[s.id])) return;
        btn.disabled = true; btn.textContent = 'Saving…';
        saving = true;
        try {
            const d = await getJSON('../api/attendance.php?action=save', {
                method: 'POST', headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ course_id: Number(cid()), section: window.classSection ? classSection() : '', date: day, records: students.map(s => ({ student_id: s.id, status: marks[s.id], remark: remarks[s.id] || '' })) })
            });
            if (!d.success) throw new Error(d.message);
            dirty = false; saving = false;
            if (typeof showToast === 'function') showToast('Attendance saved');
            try { if (chan) chan.postMessage({ t: 'saved', course: cid(), date: day, at: Date.now() }); } catch (err) { }
            histSig = '';                                              // History must show the new marks
            loadDay();
        } catch (e) { alert(e.message || 'Could not save.'); btn.disabled = false; btn.textContent = saveLabel; }
        finally { saving = false; }
    };
    function paintFocus() {
        document.querySelectorAll('.at-row').forEach((r, i) => r.classList.toggle('focus', i === focusIdx));
        const r = document.querySelector(`.at-row[data-i="${focusIdx}"]`); if (r) r.scrollIntoView({ block: 'nearest' });
    }
    document.addEventListener('keydown', e => {
        if (tab !== 'take' || !students.length) return;
        const tag = (e.target.tagName || '').toLowerCase();
        if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') { e.preventDefault(); const b = $('at-save-btn'); if (b && !b.disabled) atSave(b); return; }
        if (['input', 'textarea', 'select'].includes(tag)) return;
        if (e.key === 'ArrowDown') { e.preventDefault(); focusIdx = Math.min(students.length - 1, focusIdx + 1); paintFocus(); }
        else if (e.key === 'ArrowUp') { e.preventDefault(); focusIdx = Math.max(0, focusIdx - 1); paintFocus(); }
        else if (KEYS[e.key.toLowerCase()] && !e.ctrlKey && !e.metaKey && !e.altKey) {
            e.preventDefault(); setMark(students[focusIdx].id, KEYS[e.key.toLowerCase()]);
            if (focusIdx < students.length - 1) focusIdx++; paintFocus();
        }
    });
    window.addEventListener('beforeunload', e => { if (dirty) { e.preventDefault(); e.returnValue = ''; } });

    // ── History ─────────────────────────────────────────────
    // ── History: every recorded school day, scrolling sideways ─────
    // Student stays on the left; the four totals and Overall stay on the right; the days scroll in
    // between and open at the most recent day. Scroll left to go back in time.
    async function loadHistory(opts) {
        const silent = !!(opts && opts.silent);
        const wrap = $('at-hist');
        $('at-month-nav').style.display = 'none';                     // all days are shown: no week picker
        if (!cid()) { ++histSeq; histSig = ''; wrap.innerHTML = '<div class="at-card"><div class="at-empty"><strong>Choose a class</strong>Use the class button next to the title to pick a section and subject.</div></div>'; return; }
        const key = `${cid()}|${secQ()}|all`, my = ++histSeq;
        if (!silent) { histSig = ''; wrap.innerHTML = '<div class="at-card"><div class="at-empty">Loading…</div></div>'; }
        try {
            const d = await getJSON(`../api/attendance.php?action=history&range=all&course_id=${cid()}${secQ()}`, { cache: 'no-store' });
            if (my !== histSeq) return;                                // a newer request already answered
            if (!d.success) throw new Error(d.message);
            const sig = key + JSON.stringify([d.days, d.students, d.course, d.sections]);
            lastLive = Date.now();
            if (silent && sig === histSig) { liveState(true); return; } // nothing changed: leave the table alone
            histSig = sig;
            const old = wrap.querySelector('.at-scroll');
            const firstPaint = !old;                                   // no table on screen yet (first load or after "Loading…")
            const atEnd = !old || old.scrollLeft + old.clientWidth >= old.scrollWidth - 4;
            const oldX = old ? old.scrollLeft : 0;

            const MARK = { Present: ['✓', 'P'], Absent: ['✕', 'A'], Late: ['L', 'L'], Excused: ['E', 'E'] };
            const CNT = STATUSES;                                      // Present, Absent, Late, Excused
            const DOT = { Present: '#00875a', Absent: '#c0392b', Late: '#b38600', Excused: '#5b7bd5' };
            // Only dates on which attendance was recorded for this class
            const days = (d.days || []).filter(x => x <= TODAY && d.students.some(s => s.records && s.records[x]));
            const dt = x => new Date(x + 'T00:00:00');

            // Month groups for the top header row
            const months = [];
            days.forEach(x => {
                const m = x.slice(0, 7);
                if (!months.length || months[months.length - 1].m !== m) months.push({ m, n: 0, label: dt(x).toLocaleDateString('en-US', { month: 'long', year: 'numeric' }) });
                months[months.length - 1].n++;
            });
            const wk = (x, i) => i > 0 && (mondayOf(x) !== mondayOf(days[i - 1]) || x.slice(0, 7) !== days[i - 1].slice(0, 7)) ? ' wk' : '';   // line at each new week

            const head = `<tr class="at-grp"><th class="stu" rowspan="2">Student</th>`
                + months.map(m => `<th colspan="${m.n}" class="mo"><span>${esc(m.label)}</span></th>`).join('')
                + `<th class="fill" rowspan="2"></th>`
                + `<th colspan="${CNT.length}" class="tot-h" title="Every recorded day for this subject up to today">Totals to date</th>`
                + `<th class="ov" rowspan="2">Overall<span class="at-wd">to date</span></th></tr>`
                + `<tr class="at-sub">${days.map((x, i) => {
                    const t = dt(x);
                    return `<th class="day at-day-h${wk(x, i)}${x === TODAY ? ' today' : ''}" title="Open ${t.toLocaleDateString('en-US', { weekday: 'long', month: 'long', day: 'numeric' })} to edit it" onclick="atOpenDay('${x}')">${t.getDate()}<span class="at-wd">${t.toLocaleDateString('en-US', { weekday: 'short' })}</span></th>`;
                }).join('')}${CNT.map((k, i) => `<th class="cnt c${i}"><span class="at-sh"><i style="background:${DOT[k]}"></i>${k}</span></th>`).join('')}</tr>`;

            const body = d.students.length ? d.students.map(s => {
                const tot = s.total || {}, recs = s.records || {};
                const sum = CNT.reduce((n, k) => n + Number(tot[k] || 0), 0);
                const overall = sum ? Math.round(Number(tot.Present || 0) / sum * 100) : null;
                return `<tr>
                <td class="stu"><div class="at-name" title="${esc(s.name)}">${esc(s.name)}</div></td>
                ${days.map((x, i) => {
                    const r = recs[x];
                    const cls = `day at-go${wk(x, i)}${x === TODAY ? ' today' : ''}`;
                    const tip = `${dt(x).toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric' })}: ${r ? r.s : 'not recorded'}${r && r.r ? ' — ' + r.r : ''}`;
                    if (r && MARK[r.s]) return `<td class="${cls}" title="${esc(tip)}" onclick="atOpenDay('${x}')"><span class="at-c ${MARK[r.s][1]}">${MARK[r.s][0]}</span></td>`;
                    return `<td class="${cls}" title="${esc(tip)}" onclick="atOpenDay('${x}')"><span class="at-none">·</span></td>`;
                }).join('')}
                <td class="fill"></td>
                ${CNT.map((k, i) => {
                    const t = Number(tot[k] || 0);
                    const tone = !t ? ' z' : (k === 'Absent' ? ' bad' : (k === 'Late' ? ' warn' : ''));
                    return `<td class="cnt c${i} at-num${tone}" title="${esc(s.name)} — ${k}: ${t}">${t}</td>`;
                }).join('')}
                <td class="ov at-pct ${overall !== null && overall < 80 ? 'at-low' : ''}">${overall === null ? '—' : overall + '%'}<small>${sum} day${sum === 1 ? '' : 's'}</small></td></tr>`;
            }).join('')
                : `<tr><td class="stu"><div class="at-empty" style="padding:1.5rem 0;text-align:left"><strong>No students yet</strong></div></td>${days.map((x, i) => `<td class="day${wk(x, i)}"></td>`).join('')}<td class="fill"></td>${CNT.map((k, i) => `<td class="cnt c${i}"></td>`).join('')}<td class="ov"></td></tr>`;

            const sub = [d.course, ...(d.sections || [])].filter(Boolean).join(' • ');
            const fmt = x => dt(x).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
            const span = days.length ? `${days.length > 1 ? fmt(days[0]) + ' – ' + fmt(days[days.length - 1]) : fmt(days[0])} · ${days.length} day${days.length === 1 ? '' : 's'} recorded` : '';
            if (!days.length) {
                wrap.innerHTML = `<div class="at-card">
                    <div class="at-top"><div><div class="at-h">Attendance History</div><div class="at-lrn" style="font-size:.84rem;">${esc([d.course, ...(d.sections || [])].filter(Boolean).join(' • '))}</div><span class="at-live" data-live></span></div></div>
                    <div class="at-empty"><strong>No attendance recorded yet</strong>Dates appear here once attendance is saved for this class.<br><button type="button" class="at-btn primary" style="margin-top:.9rem" onclick="atTab('take')">Take attendance</button></div></div>`;
                liveState(true);
                return;
            }
            wrap.innerHTML = `<div class="at-card">
                <div class="at-top"><div><div class="at-h">Attendance History</div><div class="at-lrn" style="font-size:.84rem;">${esc(sub)}${span ? ' · ' + esc(span) : ''}</div><span class="at-live" data-live></span><span class="at-scroll-hint" id="at-scroll-hint">← Scroll left to see earlier days</span></div>
                    <a class="at-btn" href="../api/attendance.php?action=history&range=all&course_id=${cid()}&format=csv${secQ()}">Export CSV</a></div>
                <div class="at-scroll"><table class="at-grid all"><thead>${head}</thead><tbody>${body}</tbody></table></div>
                <div class="at-legend"><b>Legend</b>
                    <span><span class="at-c P">✓</span> Present</span><span><span class="at-c A">✕</span> Absent</span>
                    <span><span class="at-c L">L</span> Late</span><span><span class="at-c E">E</span> Excused</span>
                    <span class="at-note">Only dates with recorded attendance are shown. Totals and Overall count every recorded day; Overall is days Present ÷ days recorded (Late and Excused are not counted as Present). Click a date or a cell to edit that day.</span></div>
            </div>`;
            const sc = wrap.querySelector('.at-scroll');
            if (sc) sc.scrollLeft = (firstPaint || atEnd) ? sc.scrollWidth : oldX;   // open at the latest day; keep the place on a live refresh
            scrollHint();
            liveState(true);
        } catch (e) {
            if (my !== histSeq) return;
            if (silent && histSig) { liveState(false, e.message); return; } // keep the table on screen
            histSig = '';
            wrap.innerHTML = `<div class="at-card"><div class="at-empty"><strong>Could not load history</strong><span style="color:#c0392b">${esc(e.message)}</span></div></div>`;
        }
    }

    // Show the "scroll sideways" hint only when the table is wider than its card
    function scrollHint() {
        const sc = document.querySelector('#at-hist .at-scroll'), h = $('at-scroll-hint');
        if (sc && h) h.classList.toggle('show', sc.scrollWidth > sc.clientWidth + 1);
    }
    window.addEventListener('resize', scrollHint);

    // ── Live updates ─────────────────────────────────────────
    function liveState(ok, why) {
        const t = lastLive ? new Date(lastLive).toLocaleTimeString([], { hour: 'numeric', minute: '2-digit', second: '2-digit' }) : '';
        document.querySelectorAll('[data-live]').forEach(el => {
            el.classList.toggle('off', !ok);
            el.innerHTML = `<i></i>${esc(ok ? `Live · updated ${t}` : (why && /session|sign in/i.test(why) ? why : `Reconnecting…${t ? ' · last update ' + t : ''}`))}`;
        });
    }
    function liveTick() {
        if (document.hidden || !cid()) return;
        if (tab === 'hist') loadHistory({ silent: true });
        else if (!takeBusy()) loadDay({ silent: true });
    }
    setInterval(liveTick, LIVE_MS);
    document.addEventListener('visibilitychange', () => { if (!document.hidden && Date.now() - lastLive > 2000) liveTick(); });
    window.addEventListener('online', liveTick);
    if (chan) chan.onmessage = () => { histSig = ''; liveTick(); };
    window.atOpenDay = d => { if (d > TODAY) return; atTab('take'); atSetDay(d); };

    syncDayNav();
    document.addEventListener('DOMContentLoaded', () => {
        // The site's live updater (includes/live.php) reloads a teacher page when attendance, enrollment or
        // class data changes, unless the page refreshes that data itself. This page does, so it refreshes
        // in place: no reload, the open tab and the History scroll position stay as they are.
        if (window.ElmsLive && typeof ElmsLive.onRefresh === 'function') {
            ElmsLive.onRefresh(['attendance', 'enrollment', 'class', 'subject'], () => { histSig = ''; liveTick(); });
        }
        setTimeout(() => {
            if (location.hash === '#history') atTab('hist');
            else if (cid() && !loadedOnce) loadDay();
        }, 0);
    });
})();
</script>

<?php require __DIR__ . '/../includes/footer.php'; ?>