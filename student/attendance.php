<?php
// ============================================================
//  Arandia College eLMS — Student: My Attendance
//  File: student/attendance.php
//
//  Read-only view of the LOGGED-IN student's attendance: overall
//  rate, per-subject breakdown, absences, and full history. Updates
//  live (polls api/student_attendance.php) so a teacher's changes
//  appear without reloading. The student id always comes from the
//  session.
// ============================================================
require __DIR__ . '/_init.php';   // login check + header data (same rules as every student page)
require_once __DIR__ . '/../includes/student_attendance.php';

// ── Attendance (same function the live API uses) ────────────────────────
$attendance = getStudentAttendance($conn, $user_id);

$currentPage = 'attendance.php';
$pageTitle = 'My Attendance';
require __DIR__ . '/../includes/header.php';
?>

<style>
  .sa-head { display: flex; justify-content: space-between; align-items: flex-end; gap: 1rem; flex-wrap: wrap; margin-bottom: 1.25rem; }
  .sa-sub { font-size: .9rem; color: var(--text-muted); margin-top: .25rem; }
  .sa-live { display: inline-flex; align-items: center; gap: .5rem; font-size: .78rem; color: var(--text-muted); }
  .sa-dot { width: 8px; height: 8px; border-radius: 50%; background: #10b981; box-shadow: 0 0 0 0 rgba(16,185,129,.5); }
  .sa-live.is-live .sa-dot { animation: saPulse 2s infinite; }
  .sa-live.is-off .sa-dot { background: #94a3b8; }
  .sa-live.is-err .sa-dot { background: #f59e0b; }
  @keyframes saPulse { 0% { box-shadow: 0 0 0 0 rgba(16,185,129,.45); } 70% { box-shadow: 0 0 0 7px rgba(16,185,129,0); } 100% { box-shadow: 0 0 0 0 rgba(16,185,129,0); } }
  @media (prefers-reduced-motion: reduce) { .sa-live.is-live .sa-dot { animation: none; } }
  .sa-refresh { background: none; border: 1px solid var(--border); color: var(--text-main); border-radius: 6px; padding: .3rem .7rem; font: inherit; font-size: .78rem; font-weight: 600; cursor: pointer; }
  .sa-refresh:hover { border-color: var(--primary); color: var(--primary); }
  .sa-refresh:disabled { opacity: .6; cursor: default; }

  .sa-banner { display: none; align-items: flex-start; gap: .75rem; padding: .8rem 1rem; border-radius: 10px; font-size: .86rem; margin-bottom: 1rem; border: 1px solid; }
  .sa-banner.show { display: flex; }
  .sa-banner.info { background: #eef2ff; border-color: #c7d2fe; color: #1e3a8a; }
  .sa-banner.warn { background: #fef2f2; border-color: #fecaca; color: #991b1b; }
  .sa-banner button { margin-left: auto; background: none; border: none; color: inherit; font-weight: 700; cursor: pointer; font-size: .8rem; flex-shrink: 0; }
  html[data-theme="dark"] .sa-banner.info { background: rgba(99,102,241,.12); border-color: rgba(99,102,241,.35); color: #c7d2fe; }
  html[data-theme="dark"] .sa-banner.warn { background: rgba(239,68,68,.12); border-color: rgba(239,68,68,.35); color: #fecaca; }

  .sa-stats { display: grid; grid-template-columns: minmax(0, 1.4fr) repeat(4, minmax(0, 1fr)); gap: 1rem; margin-bottom: 1.5rem; }
  .sa-stat { background: var(--bg-card); border: 1px solid var(--border); border-radius: var(--radius); box-shadow: var(--shadow); padding: 1rem 1.1rem; min-width: 0; }
  .sa-stat-label { font-size: .75rem; font-weight: 600; color: var(--text-muted); }
  .sa-stat-value { font-family: 'Nunito', sans-serif; font-size: 1.7rem; font-weight: 800; line-height: 1.15; color: var(--text-main); margin-top: .15rem; }
  .sa-stat-note { font-size: .72rem; color: var(--text-muted); margin-top: .1rem; }
  .sa-stat.is-absent { border-left: 4px solid #dc2626; }
  .sa-stat.is-absent .sa-stat-value { color: #dc2626; }
  .sa-track { height: 6px; border-radius: 100px; background: var(--border); overflow: hidden; margin-top: .55rem; }
  .sa-fill { height: 100%; border-radius: 100px; transition: width .4s; }

  .sa-split { display: grid; grid-template-columns: minmax(0, 1.7fr) minmax(260px, 1fr); gap: 1.5rem; align-items: start; margin-bottom: 1.5rem; }
  .sa-split > .card, #sa-root > .card { min-width: 0; }
  .sa-card-title { font-size: 1.05rem; font-weight: 700; color: var(--text-main); margin-bottom: .9rem; display: flex; justify-content: space-between; align-items: center; gap: .5rem; flex-wrap: wrap; }
  .sa-table-wrap { overflow-x: auto; }
  .sa-table { width: 100%; border-collapse: collapse; min-width: 0; }
  .sa-table th { text-align: left; font-size: .7rem; font-weight: 700; color: var(--text-muted); text-transform: none; padding: .55rem .6rem; background: transparent; border-bottom: 1px solid var(--border); white-space: nowrap; }
  .sa-table td { padding: .7rem .6rem; font-size: .85rem; border-bottom: 1px solid var(--border); color: var(--text-main); vertical-align: middle; }
  .sa-table tr:last-child td { border-bottom: none; }
  .sa-table tr:hover td { background: transparent; }
  .sa-num { text-align: center; }
  .sa-subj strong { display: block; font-weight: 600; }
  .sa-subj span { font-size: .72rem; color: var(--text-muted); }
  .sa-rate { display: flex; align-items: center; gap: .5rem; min-width: 110px; }
  .sa-rate .sa-track { flex: 1; margin: 0; }
  .sa-rate b { font-size: .8rem; min-width: 2.6em; text-align: right; }

  .sa-badge { display: inline-block; font-size: .72rem; font-weight: 700; padding: .18rem .6rem; border-radius: 100px; white-space: nowrap; }
  .sa-Present { background: #d1fae5; color: #047857; }
  .sa-Late    { background: #fef3c7; color: #b45309; }
  .sa-Absent  { background: #fee2e2; color: #b91c1c; }
  .sa-Excused { background: #ede9fe; color: #6d28d9; }

  .sa-abs-item { display: flex; gap: .75rem; padding: .7rem 0; align-items: flex-start; }
  .sa-abs-item + .sa-abs-item { border-top: 1px solid var(--border); }
  .sa-abs-date { flex-shrink: 0; width: 46px; text-align: center; border: 1px solid #fecaca; border-radius: 8px; overflow: hidden; background: var(--bg-card); }
  .sa-abs-date b { display: block; background: #dc2626; color: #fff; font-size: .62rem; padding: .1rem 0; letter-spacing: .02em; }
  .sa-abs-date span { display: block; font-family: 'Nunito', sans-serif; font-weight: 800; font-size: 1.05rem; color: var(--text-main); padding: .15rem 0; }
  .sa-abs-body { min-width: 0; }
  .sa-abs-body strong { display: block; font-size: .86rem; color: var(--text-main); }
  .sa-abs-body span { font-size: .75rem; color: var(--text-muted); overflow-wrap: anywhere; }

  .sa-filters { display: flex; gap: .5rem; flex-wrap: wrap; }
  .sa-filters select { background: var(--bg-card); color: var(--text-main); font-size: .82rem; padding: .45rem .7rem; min-width: 0; }
  .sa-remark { font-size: .78rem; color: var(--text-muted); overflow-wrap: anywhere; }
  .sa-empty { text-align: center; padding: 2rem 1rem; color: var(--text-muted); font-size: .88rem; }
  .sa-empty strong { display: block; color: var(--text-main); font-size: .95rem; margin-bottom: .25rem; }
  .sa-more { display: block; margin: .9rem auto 0; }
  @keyframes saFlash { from { background: rgba(0,48,135,.10); } to { background: transparent; } }
  .sa-flash td { animation: saFlash 3s ease-out; }
  @media (prefers-reduced-motion: reduce) { .sa-flash td { animation: none; } }

  @media (max-width: 1100px) {
    .sa-stats { grid-template-columns: repeat(4, minmax(0, 1fr)); }
    .sa-stat.is-rate { grid-column: 1 / -1; }
  }
  @media (max-width: 900px) { .sa-split { grid-template-columns: minmax(0, 1fr); } }
  @media (max-width: 600px) {
    .sa-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .sa-filters { width: 100%; }
    .sa-filters select { flex: 1 1 130px; }
    /* History table becomes stacked rows */
    #sa-history-table thead { display: none; }
    #sa-history-table, #sa-history-table tbody, #sa-history-table tr, #sa-history-table td { display: block; width: 100%; }
    #sa-history-table tr { padding: .7rem 0; border-bottom: 1px solid var(--border); }
    #sa-history-table td { border: none; padding: .15rem 0; }
    /* Subject table: keep it compact */
    .sa-hide-sm { display: none; }
    .sa-rate { min-width: 0; }
    .sa-rate .sa-track { display: none; }
    .sa-table td, .sa-table th { padding-left: .35rem; padding-right: .35rem; }
    #sa-root .card { padding: 1.1rem; }
  }
</style>

<div class="section-panel active" id="sa-root">
  <div class="sa-head">
    <div>
      <h2 class="page-title">My Attendance</h2>
    </div>
  </div>

  <div class="sa-banner info" id="sa-update-banner" role="status" aria-live="polite"><span id="sa-update-text"></span><button type="button" onclick="saHide('sa-update-banner')">Dismiss</button></div>
  <div class="sa-banner warn" id="sa-warn-banner" role="alert"><span id="sa-warn-text"></span></div>
  <div class="sa-banner warn" id="sa-error-banner" role="alert"><span id="sa-error-text"></span></div>

  <div class="sa-stats">
    <div class="sa-stat is-rate">
      <div class="sa-stat-label">Attendance Rate</div>
      <div class="sa-stat-value" id="sa-rate">—</div>
      <div class="sa-track"><div class="sa-fill" id="sa-rate-bar" style="width:0%"></div></div>
      <div class="sa-stat-note" id="sa-rate-note">Present and late count as attended. Excused days are not counted.</div>
    </div>
    <div class="sa-stat"><div class="sa-stat-label">Present</div><div class="sa-stat-value" id="sa-present">0</div></div>
    <div class="sa-stat"><div class="sa-stat-label">Late</div><div class="sa-stat-value" id="sa-late">0</div></div>
    <div class="sa-stat is-absent"><div class="sa-stat-label">Absences</div><div class="sa-stat-value" id="sa-absent">0</div></div>
    <div class="sa-stat"><div class="sa-stat-label">Excused</div><div class="sa-stat-value" id="sa-excused">0</div></div>
  </div>

  <div class="sa-split">
    <div class="card">
      <div class="sa-card-title">By Subject</div>
      <div class="sa-table-wrap" id="sa-subjects"></div>
    </div>
    <div class="card">
      <div class="sa-card-title">Recent Absences <span class="sa-badge sa-Absent" id="sa-abs-count">0</span></div>
      <div id="sa-absences"></div>
    </div>
  </div>

  <div class="card">
    <div class="sa-card-title">
      <span>Attendance History</span>
      <div class="sa-filters">
        <select class="form-control" id="sa-f-subject" aria-label="Filter by subject" onchange="saRenderHistory(true)"<?= !empty($activeSubject) ? ' style="display:none" aria-hidden="true"' : '' ?>><option value="">All subjects</option></select>
        <select class="form-control" id="sa-f-status" aria-label="Filter by status" onchange="saRenderHistory(true)">
          <option value="">All statuses</option>
          <option value="Absent">Absent</option>
          <option value="Late">Late</option>
          <option value="Present">Present</option>
          <option value="Excused">Excused</option>
        </select>
        <select class="form-control" id="sa-f-month" aria-label="Filter by month" onchange="saRenderHistory(true)"><option value="">All months</option></select>
      </div>
    </div>
    <div id="sa-history"></div>
  </div>
  <noscript><div class="card" style="margin-top:1rem;">Please enable JavaScript to view your attendance.</div></noscript>
</div>

<script>
(function () {
  'use strict';

  const SA_API = '../api/student_attendance.php';
  const POLL_MS = 15000;          // normal refresh interval
  const MAX_BACKOFF_MS = 60000;   // slowest retry after errors
  const PAGE_SIZE = 25;

  // One subject open (?course=ID, includes/student_subject_nav.php): show only its records.
  const SA_COURSE = <?= !empty($activeSubject) ? (int) $activeSubject['id'] : 0 ?>;
  function saScope(d) {
    if (!SA_COURSE || !d || !d.ok) return d;
    const records = (d.records || []).filter(r => Number(r.course_id) === SA_COURSE);
    const s = { present: 0, late: 0, absent: 0, excused: 0, total: 0, rate: null };
    records.forEach(r => { const k = String(r.status).toLowerCase(); if (k in s) { s[k]++; s.total++; } });
    const counted = s.present + s.late + s.absent;
    s.rate = counted > 0 ? Math.round((s.present + s.late) / counted * 100) : null;
    return Object.assign({}, d, { summary: s, records: records,
      subjects: (d.subjects || []).filter(x => Number(x.course_id) === SA_COURSE) });
  }

  let saData = saScope(<?= json_encode($attendance, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>);
  let saSig = saData && saData.ok ? saData.signature : '';
  let saShown = PAGE_SIZE;
  let saFlashKeys = new Set();
  let saTimer = null;
  let saDelay = POLL_MS;
  let saBusy = false;
  let saAgain = false;   // a change was announced while a refresh was running: refresh again right after it
  let saStopped = false;

  const $ = id => document.getElementById(id);
  const esc = s => String(s == null ? '' : s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#39;');
  const rateColor = r => r === null ? 'var(--border)' : (r >= 90 ? '#059669' : (r >= 75 ? '#d97706' : '#dc2626'));
  const recKey = r => r.id + ':' + r.status + ':' + r.remark;
  const monthLabel = ym => {
    const p = String(ym).split('-');
    if (p.length !== 2) return ym;
    const d = new Date(Number(p[0]), Number(p[1]) - 1, 1);
    return isNaN(d) ? ym : d.toLocaleDateString(undefined, { month: 'long', year: 'numeric' });
  };

  window.saHide = function (id) { const el = $(id); if (el) el.classList.remove('show'); };
  function saShowBanner(id, textId, msg) { const el = $(id); if (!el) return; $(textId).textContent = msg; el.classList.add('show'); }

  function setLive(state, text) {
    const el = $('sa-live');
    if (!el) return;
    el.classList.remove('is-live', 'is-off', 'is-err');
    el.classList.add(state);
    $('sa-live-text').textContent = text;
  }

  // ── Rendering ───────────────────────────────────────────────
  function renderSummary(d) {
    const s = d.summary;
    $('sa-rate').textContent = s.rate === null ? '—' : s.rate + '%';
    $('sa-rate').style.color = s.rate === null ? '' : rateColor(s.rate);
    $('sa-rate-bar').style.width = (s.rate === null ? 0 : s.rate) + '%';
    $('sa-rate-bar').style.background = rateColor(s.rate);
    $('sa-present').textContent = s.present;
    $('sa-late').textContent = s.late;
    $('sa-absent').textContent = s.absent;
    $('sa-excused').textContent = s.excused;
    $('sa-abs-count').textContent = s.absent;
    $('sa-rate-note').textContent = s.total === 0
      ? 'No attendance has been recorded for you yet.'
      : 'Based on ' + s.total + ' record' + (s.total === 1 ? '' : 's') + '. Present and late count as attended; excused days are not counted.';

    const counted = s.present + s.late + s.absent;
    if (s.rate !== null && s.rate < 80 && counted >= 3) {
      saShowBanner('sa-warn-banner', 'sa-warn-text',
        'Your attendance rate is ' + s.rate + '% with ' + s.absent + ' absence' + (s.absent === 1 ? '' : 's') +
        '. If any absence was for a valid reason, talk to your teacher about having it marked as excused.');
    } else {
      saHide('sa-warn-banner');
    }
  }

  function renderSubjects(d) {
    const wrap = $('sa-subjects');
    if (!d.subjects.length) {
      wrap.innerHTML = '<div class="sa-empty"><strong>No subjects yet</strong>You are not enrolled in any subject.</div>';
      return;
    }
    wrap.innerHTML = '<table class="sa-table"><thead><tr>' +
      '<th>Subject</th><th class="sa-num">Present</th><th class="sa-num">Late</th><th class="sa-num">Absent</th><th class="sa-num sa-hide-sm">Excused</th><th>Rate</th>' +
      '</tr></thead><tbody>' +
      d.subjects.map(s =>
        '<tr><td class="sa-subj"><strong>' + esc(s.name) + '</strong><span>' + esc(s.code) + '</span></td>' +
        '<td class="sa-num">' + s.present + '</td>' +
        '<td class="sa-num">' + s.late + '</td>' +
        '<td class="sa-num"' + (s.absent > 0 ? ' style="color:#dc2626;font-weight:700"' : '') + '>' + s.absent + '</td>' +
        '<td class="sa-num sa-hide-sm">' + s.excused + '</td>' +
        '<td>' + (s.rate === null
          ? '<span class="sa-remark" title="No records yet">—</span>'
          : '<div class="sa-rate"><div class="sa-track"><div class="sa-fill" style="width:' + s.rate + '%;background:' + rateColor(s.rate) + '"></div></div><b style="color:' + rateColor(s.rate) + '">' + s.rate + '%</b></div>') +
        '</td></tr>'
      ).join('') + '</tbody></table>';
  }

  function renderAbsences(d) {
    const list = d.records.filter(r => r.status === 'Absent').slice(0, 6);
    const wrap = $('sa-absences');
    if (!list.length) {
      wrap.innerHTML = '<div class="sa-empty"><strong>No absences</strong>' + (d.summary.total ? 'Keep it up!' : 'Nothing recorded yet.') + '</div>';
      return;
    }
    wrap.innerHTML = list.map(r => {
      const dt = new Date(r.date + 'T00:00:00');
      const mon = isNaN(dt) ? '' : dt.toLocaleDateString(undefined, { month: 'short' }).toUpperCase();
      const day = isNaN(dt) ? '' : dt.getDate();
      return '<div class="sa-abs-item"><div class="sa-abs-date" aria-hidden="true"><b>' + esc(mon) + '</b><span>' + esc(day) + '</span></div>' +
        '<div class="sa-abs-body"><strong>' + esc(r.name) + '</strong><span>' + esc(r.date_label) + (r.remark ? ' — ' + esc(r.remark) : '') + '</span></div></div>';
    }).join('');
  }

  function fillSelect(id, options, keepValue) {
    const sel = $(id);
    const current = keepValue ? sel.value : '';
    const first = sel.options[0].outerHTML;
    sel.innerHTML = first + options.map(o => '<option value="' + esc(o.value) + '">' + esc(o.label) + '</option>').join('');
    sel.value = options.some(o => String(o.value) === current) ? current : '';
  }

  function renderFilters(d) {
    const subj = new Map();
    const months = new Set();
    d.records.forEach(r => { subj.set(String(r.course_id), r.name); if (r.month) months.add(r.month); });
    fillSelect('sa-f-subject', [...subj].map(([v, l]) => ({ value: v, label: l })).sort((a, b) => a.label.localeCompare(b.label)), true);
    fillSelect('sa-f-month', [...months].sort().reverse().map(m => ({ value: m, label: monthLabel(m) })), true);
  }

  window.saRenderHistory = function (resetPaging) {
    if (resetPaging) saShown = PAGE_SIZE;
    const d = saData;
    const wrap = $('sa-history');
    if (!d.records.length) {
      wrap.innerHTML = '<div class="sa-empty"><strong>No attendance records yet</strong>Your attendance will appear here once your teachers record it.</div>';
      return;
    }
    const fs = $('sa-f-subject').value, fst = $('sa-f-status').value, fm = $('sa-f-month').value;
    const rows = d.records.filter(r =>
      (!fs || String(r.course_id) === fs) && (!fst || r.status === fst) && (!fm || r.month === fm));
    if (!rows.length) {
      wrap.innerHTML = '<div class="sa-empty"><strong>No records match these filters</strong>Try a different subject, status, or month.</div>';
      return;
    }
    const page = rows.slice(0, saShown);
    wrap.innerHTML = '<div class="sa-table-wrap"><table class="sa-table" id="sa-history-table"><thead><tr><th>Date</th><th>Subject</th><th>Status</th><th>Teacher\u2019s remark</th></tr></thead><tbody>' +
      page.map(r =>
        '<tr' + (saFlashKeys.has(recKey(r)) ? ' class="sa-flash"' : '') + '>' +
        '<td style="white-space:nowrap">' + esc(r.date_label) + '</td>' +
        '<td class="sa-subj"><strong>' + esc(r.name) + '</strong><span>' + esc(r.code) + '</span></td>' +
        '<td><span class="sa-badge sa-' + esc(r.status) + '">' + esc(r.status) + '</span></td>' +
        '<td class="sa-remark">' + (r.remark ? esc(r.remark) : '—') + '</td></tr>'
      ).join('') + '</tbody></table></div>' +
      (rows.length > saShown
        ? '<button type="button" class="btn btn-outline btn-sm sa-more" onclick="saShowMore()">Show more (' + (rows.length - saShown) + ' left)</button>'
        : '') +
      '<div class="sa-remark" style="text-align:center;margin-top:.6rem">Showing ' + page.length + ' of ' + rows.length + ' record' + (rows.length === 1 ? '' : 's') + '</div>';
  };
  window.saShowMore = function () { saShown += PAGE_SIZE; saRenderHistory(false); };

  function renderAll() {
    if (!saData || !saData.ok) {
      saShowBanner('sa-error-banner', 'sa-error-text', 'Unable to load your attendance right now. The page will keep trying.');
      return;
    }
    saHide('sa-error-banner');
    renderSummary(saData);
    renderSubjects(saData);
    renderAbsences(saData);
    renderFilters(saData);
    saRenderHistory(false);
  }

  // ── Live refresh ───────────────────────────────────────────
  function describeChanges(oldData, newData) {
    const oldKeys = new Set((oldData && oldData.records ? oldData.records : []).map(recKey));
    const changed = newData.records.filter(r => !oldKeys.has(recKey(r)));
    saFlashKeys = new Set(changed.map(recKey));
    if (!changed.length) return 'Your attendance records were updated.';
    const abs = changed.filter(r => r.status === 'Absent');
    let msg = changed.length + ' attendance record' + (changed.length === 1 ? ' was' : 's were') + ' added or updated.';
    if (abs.length) msg += ' Marked absent: ' + abs.slice(0, 3).map(r => r.name + ' (' + r.date_label + ')').join(', ') + (abs.length > 3 ? ', and more.' : '.');
    return msg;
  }

  function schedule(ms) {
    clearTimeout(saTimer);
    if (!saStopped) saTimer = setTimeout(() => saRefresh(false), ms);
  }

  window.saRefresh = async function (manual) {
    if (saStopped) return;
    if (saBusy) { saAgain = true; return; }                          // never drop a teacher's save
    if (!manual && document.hidden) { schedule(POLL_MS); return; }  // don't poll a background tab
    saBusy = true;
    const btn = $('sa-refresh-btn');
    if (manual && btn) { btn.disabled = true; btn.textContent = 'Refreshing...'; }

    const ctrl = ('AbortController' in window) ? new AbortController() : null;
    const killer = ctrl ? setTimeout(() => ctrl.abort(), 10000) : null;
    try {
      const res = await fetch(SA_API + '?since=' + encodeURIComponent(saSig || ''), {
        credentials: 'same-origin', cache: 'no-store', headers: { 'Accept': 'application/json' },
        signal: ctrl ? ctrl.signal : undefined
      });
      if (res.status === 401 || res.status === 403) {
        saStopped = true;
        setLive('is-off', 'Signed out');
        saShowBanner('sa-error-banner', 'sa-error-text', 'Your session has ended. Please sign in again to see live updates.');
        return;
      }
      const json = await res.json();
      if (!json || !json.success) throw new Error('bad response');

      if (json.changed && json.data && json.data.ok) {
        const fresh = saScope(json.data);
        const msg = describeChanges(saData, fresh);
        const hadData = !!saSig;
        saData = fresh;
        saSig = json.signature;
        renderAll();
        // A change in another subject is not news on this subject's page
        if (hadData && (!SA_COURSE || saFlashKeys.size)) saShowBanner('sa-update-banner', 'sa-update-text', msg);
        setTimeout(() => { saFlashKeys = new Set(); }, 3500);
      }
      saHide('sa-error-banner');
      saDelay = POLL_MS;
      setLive('is-live', 'Live \u00B7 updated ' + new Date().toLocaleTimeString([], { hour: 'numeric', minute: '2-digit', second: '2-digit' }));
    } catch (e) {
      saDelay = Math.min(saDelay * 2, MAX_BACKOFF_MS);
      setLive('is-err', 'Reconnecting...');
    } finally {
      if (killer) clearTimeout(killer);
      saBusy = false;
      if (manual && btn) { btn.disabled = false; btn.textContent = 'Refresh'; }
      if (saAgain) { saAgain = false; schedule(300); } else schedule(saDelay);
    }
  };

  document.addEventListener('visibilitychange', () => {
    if (!document.hidden && !saStopped) saRefresh(false);   // catch up right away when the tab is reopened
  });
  window.addEventListener('online', () => { if (!saStopped) saRefresh(false); });

  renderAll();
  schedule(POLL_MS);
})();
</script>

<?php require __DIR__ . '/../includes/footer.php'; ?>