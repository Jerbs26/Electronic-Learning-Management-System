<?php
// ============================================================
//  Arandia College eLMS
//  File: includes/dt_picker.php
//  Shared date + time picker (calendar with a time slider inside the popup).
//  Required once from footer.php (teacher branch), so every <input type="datetime-local">
//  on teacher pages (assignment due date, quiz open / close) uses it.
//  Opt an input out with data-no-slider; set its default time with data-default-time="HH:MM".
// ============================================================
?>
<style>
    /* accent colour = the page's blue (same as the Publish / primary buttons) */
    .dtp, .dtp-field { --dtp-accent: #003087; --dtp-accent-hover: #0044cc; --dtp-accent-rgb: 0, 48, 135; --dtp-on-accent: #fff; }
    html[data-theme="dark"] .dtp, html[data-theme="dark"] .dtp-field { --dtp-accent: #8fb0ff; --dtp-accent-hover: #b3c9ff; --dtp-accent-rgb: 143, 176, 255; --dtp-on-accent: #0b1b4d; }
    .dtp-field { display: flex; align-items: center; justify-content: space-between; gap: .6rem; width: 100%; box-sizing: border-box; cursor: pointer; text-align: left; user-select: none; }
    .dtp-field:focus-visible { outline: 2px solid var(--dtp-accent); outline-offset: 1px; }
    .dtp-field.err { border-color: #c0392b !important; }
    .dtp-field .dtp-ph { color: var(--text-light, #94a3b8); }
    .dtp-field svg { width: 18px; height: 18px; flex: none; color: var(--text-light, #64748b); }

    .dtp { --dtp-bg: var(--white, #fff); --dtp-text: var(--text-dark, #0f172a); --dtp-mute: var(--text-light, #64748b); --dtp-line: rgba(15, 23, 42, .1);
        position: fixed; z-index: 100000; display: none; width: 328px; max-width: calc(100vw - 16px); box-sizing: border-box;
        background: var(--dtp-bg); color: var(--dtp-text); border: 1px solid var(--dtp-line); border-radius: 14px;
        box-shadow: 0 18px 44px rgba(15, 23, 42, .22); padding: .8rem .85rem .7rem; font-family: inherit; }
    html[data-theme="dark"] .dtp { --dtp-line: rgba(255, 255, 255, .12); }
    .dtp.show { display: block; }
    .dtp-nav { display: flex; align-items: center; gap: .35rem; margin-bottom: .55rem; }
    .dtp-nav select { flex: 1; min-width: 0; font: inherit; font-size: .82rem; font-weight: 700; color: var(--dtp-text); background: transparent; border: 1px solid var(--dtp-line); border-radius: 8px; padding: .3rem .4rem; }
    .dtp-nav select option { color: #0f172a; background: #fff; }
    .dtp-nav button { width: 30px; height: 30px; flex: none; border: 1px solid var(--dtp-line); border-radius: 8px; background: transparent; color: var(--dtp-text); cursor: pointer; font-size: .95rem; line-height: 1; }
    .dtp-nav button:hover { background: rgba(var(--dtp-accent-rgb), .12); }
    .dtp-dow, .dtp-grid { display: grid; grid-template-columns: repeat(7, 1fr); gap: 2px; text-align: center; }
    .dtp-dow span { font-size: .68rem; font-weight: 800; color: var(--dtp-mute); padding: .25rem 0; }
    .dtp-grid button { aspect-ratio: 1 / 1; border: 0; border-radius: 50%; background: transparent; font: inherit; font-size: .82rem; font-weight: 600; color: var(--dtp-text); cursor: pointer; padding: 0; }
    .dtp-grid button:hover:not(:disabled) { background: rgba(var(--dtp-accent-rgb), .14); }
    .dtp-grid button.other { color: var(--dtp-mute); opacity: .55; }
    .dtp-grid button.today { box-shadow: inset 0 0 0 1.5px var(--dtp-accent); }
    .dtp-grid button.sel { background: var(--dtp-accent); color: var(--dtp-on-accent); opacity: 1; box-shadow: none; }
    .dtp-grid button:disabled { opacity: .25; cursor: not-allowed; }
    .dtp-time { margin-top: .6rem; padding-top: .6rem; border-top: 1px solid var(--dtp-line); }
    .dtp-time-h { font-size: .68rem; font-weight: 800; letter-spacing: .07em; text-transform: uppercase; color: var(--dtp-mute); margin-bottom: .2rem; }
    .dtp-foot { display: flex; align-items: center; justify-content: space-between; margin-top: .55rem; }
    .dtp-foot button { border: 0; background: none; font: inherit; font-size: .82rem; font-weight: 700; color: var(--dtp-accent); cursor: pointer; padding: .3rem .5rem; border-radius: 8px; }
    .dtp-foot button:hover { background: rgba(var(--dtp-accent-rgb), .12); }
    .dtp-foot .dtp-done { background: var(--dtp-accent); color: var(--dtp-on-accent); padding: .35rem .9rem; }
    .dtp-foot .dtp-done:hover { background: var(--dtp-accent-hover); }

    .tslider { display: flex; align-items: center; gap: .8rem; width: 100%; box-sizing: border-box; }
    .tslider-track { position: relative; flex: 1; height: 24px; display: flex; align-items: center; }
    .tslider-track::before { /* hourly tick dots, drawn under the thumb */
        content: ""; position: absolute; left: 11px; right: 11px; top: 50%; height: 6px; transform: translateY(-50%);
        background: radial-gradient(circle, rgba(255, 255, 255, .9) 1.3px, transparent 1.8px) 0 50% / calc(100% / 24) 6px repeat-x;
        pointer-events: none; z-index: 1;
    }
    .tslider input[type=range] {
        --p: 0%; -webkit-appearance: none; appearance: none; width: 100%; height: 6px; border-radius: 100px;
        outline: none; margin: 0; padding: 0; border: 0; position: relative; z-index: 0; cursor: pointer;
        background: linear-gradient(to right, var(--dtp-accent) var(--p), rgba(var(--dtp-accent-rgb), .25) var(--p));
    }
    .tslider input[type=range]::-webkit-slider-runnable-track { background: transparent; height: 6px; }
    .tslider input[type=range]::-webkit-slider-thumb {
        -webkit-appearance: none; appearance: none; width: 22px; height: 22px; border-radius: 50%; background: var(--dtp-accent);
        box-shadow: 0 2px 6px rgba(0, 0, 0, .25); cursor: pointer; position: relative; z-index: 2; margin-top: -8px;
    }
    .tslider input[type=range]::-moz-range-track { background: transparent; height: 6px; }
    .tslider input[type=range]::-moz-range-thumb {
        width: 22px; height: 22px; border: 0; border-radius: 50%; background: var(--dtp-accent);
        box-shadow: 0 2px 6px rgba(0, 0, 0, .25); cursor: pointer;
    }
    .tslider input[type=range]:focus-visible::-webkit-slider-thumb { box-shadow: 0 0 0 4px rgba(var(--dtp-accent-rgb), .3); }
    .tslider input[type=range]:focus-visible::-moz-range-thumb { box-shadow: 0 0 0 4px rgba(var(--dtp-accent-rgb), .3); }
    .tslider-val { width: 94px; flex: none; box-sizing: border-box; text-align: center; font: inherit; font-size: .86rem; font-weight: 800; color: var(--dtp-text, var(--text-dark)); background: transparent; border: 1px solid var(--dtp-line, rgba(15, 23, 42, .12)); border-radius: 8px; padding: .32rem .4rem; cursor: text; }
    .tslider-val:hover { border-color: var(--dtp-accent); }
    .tslider-val:focus { outline: none; border-color: var(--dtp-accent); box-shadow: 0 0 0 3px rgba(var(--dtp-accent-rgb), .2); }
    .tslider-val.bad { border-color: #c0392b; box-shadow: 0 0 0 3px rgba(192, 57, 43, .18); }
</style>
<script>
// ------------------------------------------------------------
//  Due date picker with the TIME SLIDER INSIDE the date popup.
//
//  The browser's own datetime-local popup can't be edited, so every
//  <input type="datetime-local"> is replaced by a field that opens our own popup:
//  calendar on top, time slider below. The original input stays in the DOM (hidden)
//  and keeps "YYYY-MM-DDTHH:MM", so form submit, FormData and code that sets/reads
//  .value (e.g. when editing an assignment) work as before.
//  Add data-no-slider to an input to leave it alone.
// ------------------------------------------------------------
(function () {
    const STEP = 5, DEFAULT_MIN = 1439; // snap every 5 min; default 11:59 PM (override per input with data-default-time="HH:MM")
    const MONTHS = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
    const DOW = ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'];
    const CAL_ICON = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>';

    const nativeDesc = Object.getOwnPropertyDescriptor(HTMLInputElement.prototype, 'value');
    const pad = n => String(n).padStart(2, '0');
    const ymd = (y, m, d) => y + '-' + pad(m + 1) + '-' + pad(d);
    const fmt24 = m => pad(Math.floor(m / 60)) + ':' + pad(m % 60);
    const fmt12 = m => { const h = Math.floor(m / 60); return ((h % 12) || 12) + ':' + pad(m % 60) + ' ' + (h < 12 ? 'AM' : 'PM'); };
    function split(v) {
        const m = /^(\d{4}-\d{2}-\d{2})T(\d{1,2}):(\d{2})/.exec(v || '');
        return m ? { date: m[1], min: Math.min(1439, (+m[2]) * 60 + (+m[3])) } : null;
    }
    function prettyDate(s) {
        const p = /^(\d{4})-(\d{2})-(\d{2})$/.exec(s);
        return p ? MONTHS[+p[2] - 1].slice(0, 3) + ' ' + (+p[3]) + ', ' + p[1] : '';
    }

    // Typed time -> minutes since midnight, or null if invalid.
    // Accepts "2:45 PM", "2:45pm", "245pm", "14:45", "1445", "9", "9 am".
    // With no AM/PM and an hour of 1-12, keeps the current AM/PM (curMin = current minutes).
    function parseTyped(str, curMin) {
        const s = String(str).trim().toLowerCase().replace(/\./g, '');
        const m = /^(\d{1,2})(?::(\d{2}))?\s*(am|pm|a|p)?$/.exec(s) || /^(\d{1,2})(\d{2})\s*(am|pm|a|p)?$/.exec(s);
        if (!m) return null;
        let h = +m[1];
        const mi = m[2] ? +m[2] : 0;
        const ap = m[3] ? m[3][0] : '';
        if (mi > 59) return null;
        if (ap) {
            if (h < 1 || h > 12) return null;
            h = (h % 12) + (ap === 'p' ? 12 : 0);
        } else if (h >= 1 && h <= 12 && !/^0\d/.test(s)) {
            h = (h % 12) + (curMin >= 720 ? 12 : 0);
        } else if (h > 23) return null;
        return h * 60 + mi;
    }

    let openInst = null;
    function closeOpen() { if (openInst) openInst.close(); }

    function enhance(inp) {
        if (inp.dataset.dtp || inp.hasAttribute('data-no-slider')) return;
        inp.dataset.dtp = '1';

        const required = inp.required;
        if (required) inp.required = false; // hidden inputs can't be validated by the browser
        const minDate = inp.min ? inp.min.slice(0, 10) : '';
        const maxDate = inp.max ? inp.max.slice(0, 10) : '';

        const dm = /^(\d{1,2}):(\d{2})$/.exec(inp.dataset.defaultTime || '');
        const defMin = dm ? Math.min(1439, (+dm[1]) * 60 + (+dm[2])) : DEFAULT_MIN;

        let sel = '', minutes = defMin;
        const now = new Date();
        let viewY = now.getFullYear(), viewM = now.getMonth();

        // ----- the clickable field (looks like the old input) -----
        const field = document.createElement('div');
        field.className = (inp.className + ' dtp-field').trim();
        field.style.cssText = inp.style.cssText;
        field.tabIndex = 0;
        field.setAttribute('role', 'button');
        field.setAttribute('aria-haspopup', 'dialog');
        const text = document.createElement('span');
        field.appendChild(text);
        field.insertAdjacentHTML('beforeend', CAL_ICON);

        // ----- the popup -----
        const pop = document.createElement('div');
        pop.className = 'dtp';
        pop.setAttribute('role', 'dialog');
        pop.innerHTML =
            '<div class="dtp-nav">' +
                '<button type="button" class="dtp-prev" aria-label="Previous month">&#8249;</button>' +
                '<select class="dtp-month" aria-label="Month"></select>' +
                '<select class="dtp-year" aria-label="Year"></select>' +
                '<button type="button" class="dtp-next" aria-label="Next month">&#8250;</button>' +
            '</div>' +
            '<div class="dtp-dow">' + DOW.map(d => '<span>' + d + '</span>').join('') + '</div>' +
            '<div class="dtp-grid"></div>' +
            '<div class="dtp-time"><div class="dtp-time-h">Time <span style="text-transform:none;letter-spacing:0;font-weight:600">(drag or type)</span></div>' +
                '<div class="tslider"><div class="tslider-track"><input type="range" min="0" max="1439" step="1" aria-label="Due time"></div><input type="text" class="tslider-val" autocomplete="off" spellcheck="false" aria-label="Edit time" title="Type an exact time, e.g. 2:45 PM or 14:45"></div></div>' +
            '<div class="dtp-foot"><button type="button" class="dtp-clear">Clear</button><button type="button" class="dtp-today">Today</button><button type="button" class="dtp-done">Done</button></div>';
        document.body.appendChild(pop);

        const $ = s => pop.querySelector(s);
        const monthSel = $('.dtp-month'), yearSel = $('.dtp-year'), grid = $('.dtp-grid');
        const range = $('.tslider input'), tval = $('.tslider-val');
        monthSel.innerHTML = MONTHS.map((m, i) => '<option value="' + i + '">' + m + '</option>').join('');

        function paintText() {
            if (sel) { text.className = ''; text.textContent = prettyDate(sel) + ', ' + fmt12(minutes); }
            else { text.className = 'dtp-ph'; text.textContent = 'Select due date & time'; }
        }
        function paintSlider() {
            range.value = minutes;
            range.style.setProperty('--p', (minutes / 1439 * 100) + '%');
            tval.value = fmt12(minutes);
            tval.classList.remove('bad');
        }
        function paintCalendar() {
            const y0 = now.getFullYear();
            const from = Math.min(y0 - 1, viewY), to = Math.max(y0 + 5, viewY);
            let opts = '';
            for (let y = from; y <= to; y++) opts += '<option value="' + y + '">' + y + '</option>';
            yearSel.innerHTML = opts;
            yearSel.value = viewY; monthSel.value = viewM;

            const first = new Date(viewY, viewM, 1).getDay();
            const dim = new Date(viewY, viewM + 1, 0).getDate();
            const prevDim = new Date(viewY, viewM, 0).getDate();
            const todayStr = ymd(now.getFullYear(), now.getMonth(), now.getDate());
            let html = '';
            for (let i = 0; i < 42; i++) {
                let y = viewY, m = viewM, d = i - first + 1, other = false;
                if (d < 1) { m--; d += prevDim; other = true; if (m < 0) { m = 11; y--; } }
                else if (d > dim) { d -= dim; m++; other = true; if (m > 11) { m = 0; y++; } }
                const s = ymd(y, m, d);
                const dis = (minDate && s < minDate) || (maxDate && s > maxDate);
                html += '<button type="button" data-d="' + s + '" class="' + (other ? 'other ' : '') + (s === todayStr ? 'today ' : '') + (s === sel ? 'sel' : '') + '"' + (dis ? ' disabled' : '') + '>' + d + '</button>';
            }
            grid.innerHTML = html;
        }
        function commit(fire) {
            nativeDesc.set.call(inp, sel ? sel + 'T' + fmt24(minutes) : '');
            paintText();
            field.classList.remove('err');
            if (fire) {
                inp.dispatchEvent(new Event('input', { bubbles: true }));
                inp.dispatchEvent(new Event('change', { bubbles: true }));
            }
        }
        function pull() { // real input -> UI
            const p = split(nativeDesc.get.call(inp));
            if (p) { sel = p.date; minutes = p.min; viewY = +p.date.slice(0, 4); viewM = +p.date.slice(5, 7) - 1; }
            else { sel = ''; minutes = defMin; }
            paintText(); paintSlider(); paintCalendar();
        }
        function selectDate(s) {
            sel = s; viewY = +s.slice(0, 4); viewM = +s.slice(5, 7) - 1;
            commit(true); paintCalendar();
        }
        function place() {
            const r = field.getBoundingClientRect();
            const w = pop.offsetWidth, h = pop.offsetHeight;
            let left = Math.min(Math.max(8, r.left), window.innerWidth - w - 8);
            let top = r.bottom + 6;
            if (top + h > window.innerHeight - 8 && r.top - h - 6 > 8) top = r.top - h - 6;
            else if (top + h > window.innerHeight - 8) top = Math.max(8, window.innerHeight - h - 8);
            pop.style.left = left + 'px'; pop.style.top = top + 'px';
        }

        const inst = {
            open() {
                if (openInst && openInst !== inst) openInst.close();
                openInst = inst;
                pop.classList.add('show'); place();
                if (!sel) { viewY = now.getFullYear(); viewM = now.getMonth(); paintCalendar(); }
            },
            close() { pop.classList.remove('show'); if (openInst === inst) openInst = null; },
            isOpen() { return pop.classList.contains('show'); },
            contains(n) { return pop.contains(n) || field.contains(n); },
            place
        };
        inst.el = pop;
        instances.push(inst);

        // ----- events -----
        field.addEventListener('click', () => inst.isOpen() ? inst.close() : inst.open());
        field.addEventListener('keydown', e => {
            if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); inst.isOpen() ? inst.close() : inst.open(); }
            if (e.key === 'Escape') inst.close();
        });
        pop.addEventListener('keydown', e => { if (e.key === 'Escape') { inst.close(); field.focus(); e.stopPropagation(); } });

        grid.addEventListener('click', e => {
            const b = e.target.closest('button[data-d]');
            if (b && !b.disabled) selectDate(b.dataset.d);
        });
        $('.dtp-prev').addEventListener('click', () => { viewM--; if (viewM < 0) { viewM = 11; viewY--; } paintCalendar(); });
        $('.dtp-next').addEventListener('click', () => { viewM++; if (viewM > 11) { viewM = 0; viewY++; } paintCalendar(); });
        monthSel.addEventListener('change', () => { viewM = +monthSel.value; paintCalendar(); });
        yearSel.addEventListener('change', () => { viewY = +yearSel.value; paintCalendar(); });

        range.addEventListener('input', () => {
            const m = +range.value;
            minutes = m >= 1435 ? 1439 : Math.round(m / STEP) * STEP;
            paintSlider();
            commit(!!sel);
        });
        // ----- edit the time by typing -----
        function applyTyped() {
            const m = parseTyped(tval.value, minutes);
            if (m === null) { tval.classList.add('bad'); return false; }
            minutes = m;
            paintSlider();
            commit(!!sel);
            return true;
        }
        tval.addEventListener('focus', () => tval.select());
        tval.addEventListener('input', () => tval.classList.remove('bad'));
        tval.addEventListener('keydown', e => {
            if (e.key === 'Enter') { e.preventDefault(); if (applyTyped()) tval.select(); }
            else if (e.key === 'ArrowUp' || e.key === 'ArrowDown') { // +/- 1 min (Shift = 15 min)
                e.preventDefault();
                const d = (e.key === 'ArrowUp' ? 1 : -1) * (e.shiftKey ? 15 : 1);
                minutes = Math.min(1439, Math.max(0, minutes + d));
                paintSlider(); commit(!!sel); tval.select();
            }
            else if (e.key === 'Escape') { paintSlider(); } // revert, then the popup's own Esc closes it
        });
        tval.addEventListener('blur', () => { if (!applyTyped()) paintSlider(); });

        $('.dtp-today').addEventListener('click', () => selectDate(ymd(now.getFullYear(), now.getMonth(), now.getDate())));
        $('.dtp-clear').addEventListener('click', () => { sel = ''; minutes = defMin; commit(true); paintSlider(); paintCalendar(); });
        $('.dtp-done').addEventListener('click', () => { inst.close(); field.focus(); });

        // code doing input.value = "2026-10-22T14:30" (editing) or "" (reset) -> update UI
        Object.defineProperty(inp, 'value', {
            configurable: true,
            get() { return nativeDesc.get.call(this); },
            set(v) { nativeDesc.set.call(this, v); pull(); }
        });
        if (inp.form) {
            inp.form.addEventListener('reset', () => setTimeout(pull, 0));
            if (required) inp.form.addEventListener('submit', e => {
                if (!nativeDesc.get.call(inp) && field.offsetParent !== null) {
                    e.preventDefault(); e.stopImmediatePropagation();
                    field.classList.add('err'); inst.open();
                }
            }, true);
        }

        inp.style.display = 'none';
        inp.insertAdjacentElement('afterend', field);
        pull();
    }

    const instances = [];

    // close on outside click; keep the popup attached to its field while scrolling / resizing
    document.addEventListener('mousedown', e => { if (openInst && !openInst.contains(e.target)) openInst.close(); });
    window.addEventListener('resize', () => { if (openInst) openInst.place(); });
    window.addEventListener('scroll', () => { if (openInst) openInst.place(); }, true);

    function scan(root) { (root || document).querySelectorAll('input[type="datetime-local"]').forEach(enhance); }
    function start() {
        scan();
        // modals rendered later by footer.php scripts
        new MutationObserver(muts => {
            for (const mu of muts) for (const n of mu.addedNodes) {
                if (n.nodeType !== 1 || (n.classList && n.classList.contains('dtp'))) continue;
                if (n.matches && n.matches('input[type="datetime-local"]')) enhance(n);
                else if (n.querySelectorAll) scan(n);
            }
        }).observe(document.body, { childList: true, subtree: true });
    }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', start);
    else start();
})();
</script>