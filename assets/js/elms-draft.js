/* ============================================================
   Arandia College eLMS — Assignment upload drafts
   File: assets/js/elms-draft.js

   Problem it solves: a student picks a file to submit, then the
   internet drops or the computer shuts down before "Upload File"
   finishes. The chosen file is now kept as a DRAFT on that same
   device (IndexedDB), so nothing is lost and nothing is submitted
   by accident. The student opens the assignment again, the draft
   is restored, and they press "Upload File" themselves.

   Rules:
   - A draft is only saved on the student's own device. It is NOT
     sent to the teacher until the student presses Upload File.
   - A draft is removed after a successful submission, when the
     student discards it, or when the due date has passed.
   - If the browser cannot store drafts (private mode, storage
     full), everything still works as before — only the draft
     feature is skipped, and the student is told.
   - No page ever crashes because of this file: every storage
     call is wrapped and failures are handled quietly.
   ============================================================ */
(function (w) {
    'use strict';

    var DB_NAME = 'elms_drafts';
    var STORE = 'drafts';
    var MAX_BYTES = 10 * 1024 * 1024;                       // same limit as api/assignments.php
    var ALLOWED = ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png', 'zip'];
    var DEFAULT_TTL = 14 * 24 * 60 * 60 * 1000;             // used when an assignment has no due date
    var UPLOAD_TIMEOUT = 5 * 60 * 1000;                     // give up on a stuck upload after 5 minutes

    /* ---------- IndexedDB helpers ---------- */
    function openDb() {
        return new Promise(function (resolve, reject) {
            if (!w.indexedDB) { reject(new Error('IndexedDB not available')); return; }
            var req;
            try { req = w.indexedDB.open(DB_NAME, 1); } catch (e) { reject(e); return; }
            req.onupgradeneeded = function () {
                if (!req.result.objectStoreNames.contains(STORE)) req.result.createObjectStore(STORE);
            };
            req.onsuccess = function () { resolve(req.result); };
            req.onerror = function () { reject(req.error || new Error('Could not open draft storage')); };
            req.onblocked = function () { reject(new Error('Draft storage is blocked')); };
        });
    }

    function withStore(mode, fn) {
        return openDb().then(function (db) {
            return new Promise(function (resolve, reject) {
                var out, tx, req;
                try { tx = db.transaction(STORE, mode); } catch (e) { db.close(); reject(e); return; }
                try { req = fn(tx.objectStore(STORE)); } catch (e) {
                    try { tx.abort(); } catch (_) { }
                    db.close(); reject(e); return;
                }
                if (req) req.onsuccess = function () { out = req.result; };
                tx.oncomplete = function () { db.close(); resolve(out); };
                tx.onabort = tx.onerror = function () { db.close(); reject(tx.error || new Error('Draft storage failed')); };
            });
        });
    }

    function keyOf(uid, id) { return 'assignment:' + uid + ':' + id; }

    function saveDraft(uid, id, file, expiresAt) {
        return withStore('readwrite', function (st) {
            return st.put({
                blob: file,
                name: file.name,
                type: file.type || '',
                size: file.size,
                lastModified: file.lastModified || Date.now(),
                savedAt: Date.now(),
                expiresAt: expiresAt || (Date.now() + DEFAULT_TTL)
            }, keyOf(uid, id));
        });
    }

    function dropDraft(uid, id) {
        return withStore('readwrite', function (st) { return st.delete(keyOf(uid, id)); })
            .then(function () { fire(); }, function () { /* nothing to remove / storage unavailable */ });
    }

    /** Resolves the saved draft record, or null (never rejects). Expired / broken drafts are removed. */
    function loadDraft(uid, id) {
        return withStore('readonly', function (st) { return st.get(keyOf(uid, id)); }).then(function (rec) {
            if (!rec) return null;
            var bad = !(rec.blob instanceof Blob) || !rec.name;
            var expired = rec.expiresAt && rec.expiresAt < Date.now();
            if (bad || expired) { dropDraft(uid, id); return null; }
            return rec;
        }, function () { return null; });
    }

    function toFile(rec) {
        try {
            return new File([rec.blob], rec.name, { type: rec.type || '', lastModified: rec.lastModified || Date.now() });
        } catch (e) {
            var b = rec.blob; try { b.name = rec.name; } catch (_) { }
            return b;
        }
    }

    /* ---------- small utilities ---------- */
    var listeners = [];
    function runListeners() { listeners.slice().forEach(function (fn) { try { fn(); } catch (e) { } }); }
    // Other tabs of the same browser hear about every save / discard / submit, so their notice updates at once
    var chan = null;
    try {
        if ('BroadcastChannel' in w) { chan = new BroadcastChannel('elms_drafts'); chan.onmessage = runListeners; }
    } catch (e) { chan = null; }
    function fire() { runListeners(); try { if (chan) chan.postMessage(1); } catch (e) { } }

    function fmtSize(n) {
        if (n >= 1048576) return (n / 1048576).toFixed(1).replace(/\.0$/, '') + ' MB';
        if (n >= 1024) return Math.round(n / 1024) + ' KB';
        return n + ' B';
    }
    function fmtTime(ms) {
        try { return new Date(ms).toLocaleString([], { month: 'short', day: 'numeric', hour: 'numeric', minute: '2-digit' }); }
        catch (e) { return ''; }
    }
    function checkFile(f) {
        var ext = (f.name.split('.').pop() || '').toLowerCase();
        if (f.name.indexOf('.') === -1 || ALLOWED.indexOf(ext) === -1)
            return 'This file type is not allowed. Use PDF, Word, PowerPoint, Excel, JPG, PNG or ZIP.';
        if (f.size > MAX_BYTES) return 'The file is too big. The maximum size is 10 MB.';
        if (f.size === 0) return 'This file is empty. Please choose another file.';
        return '';
    }

    /* ---------- the Submit Assignment modal ---------- */
    function initAssignment(cfg) {
        cfg = cfg || {};
        var uid = Number(cfg.uid) || 0;
        var dueOf = typeof cfg.dueOf === 'function' ? cfg.dueOf : function () { return null; };
        var onSuccess = typeof cfg.onSuccess === 'function' ? cfg.onSuccess : function () { location.reload(); };

        var currentId = null;      // assignment open in the modal
        var restored = null;       // draft file that could not be put into the <input>
        var busy = false;          // an upload / confirmation is in progress
        var connLost = false;

        function $(id) { return document.getElementById(id); }
        function expiryFor(id) { var d = dueOf(id); return d ? Number(d) : null; }

        function noteEl() {
            var el = $('assign-submit-draft');
            if (el) return el;
            var input = $('assign-submit-file');
            if (!input || !input.parentNode) return null;
            el = document.createElement('div');
            el.id = 'assign-submit-draft';
            el.setAttribute('role', 'status');
            el.style.cssText = 'font-size:.85rem;margin-top:.6rem;line-height:1.45;color:var(--text-muted);display:none;';
            var err = $('assign-submit-err');
            if (err && err.parentNode === input.parentNode) input.parentNode.insertBefore(el, err);
            else input.parentNode.appendChild(el);
            return el;
        }
        function setNote(text, opts) {
            var el = noteEl(); if (!el) return;
            el.textContent = '';
            if (!text) { el.style.display = 'none'; return; }
            var s = document.createElement('span'); s.textContent = text; el.appendChild(s);
            if (opts && opts.discard) {
                var b = document.createElement('button');
                b.type = 'button'; b.textContent = 'Discard draft';
                b.style.cssText = 'background:none;border:none;padding:0;margin-left:.6rem;cursor:pointer;font:inherit;font-weight:700;color:var(--danger);text-decoration:underline;';
                b.onclick = function () { discard(); };
                el.appendChild(b);
            }
            el.style.display = 'block';
        }
        function setErr(text) {
            var e = $('assign-submit-err'); if (!e) return;
            e.textContent = text || ''; e.style.display = text ? 'block' : 'none';
        }

        function discard() {
            if (currentId == null) return;
            var inp = $('assign-submit-file');
            if (inp) inp.value = '';
            restored = null;
            setNote('');
            dropDraft(uid, currentId);
        }

        function currentFile() {
            var inp = $('assign-submit-file');
            return (inp && inp.files && inp.files[0]) || restored || null;
        }

        /** Open the modal (called by the Submit / Resubmit buttons) */
        w.openAssignSubmitModal = function (id, title) {
            currentId = id; restored = null; connLost = false;
            var nm = $('assign-submit-name'); if (nm) nm.innerText = title;
            var inp = $('assign-submit-file'); if (inp) inp.value = '';
            setErr(''); setNote('');
            var modal = $('assignSubmitModal'); if (modal) modal.classList.remove('hidden');

            // A draft from an earlier try (lost signal, shutdown…) comes back here
            loadDraft(uid, id).then(function (rec) {
                var input = $('assign-submit-file');
                if (!rec || currentId !== id || !input || (input.files && input.files.length)) return;
                var f = toFile(rec);
                restored = f;
                try {
                    var dt = new DataTransfer(); dt.items.add(f); input.files = dt.files;
                    if (input.files && input.files.length) restored = null;   // the <input> now holds it
                } catch (e) { /* older browser: we keep it in `restored` */ }
            });
        };

        /** A file was chosen → save it as a draft right away */
        document.addEventListener('change', function (e) {
            var input = e.target;
            if (!input || input.id !== 'assign-submit-file') return;
            restored = null; setErr(''); connLost = false;
            var f = input.files && input.files[0];
            if (!f) { setNote(''); return; }
            var bad = checkFile(f);
            if (bad) { input.value = ''; setNote(''); setErr(bad); return; }
            var id = currentId; if (id == null) return;
            saveDraft(uid, id, f, expiryFor(id)).then(function () {
                if (currentId === id) setNote('Draft saved on this device. If your connection drops or your computer shuts down, your file is kept. Open this assignment again to continue.', { discard: true });
                fire();
            }, function () {
                if (currentId === id) setNote('Could not save a draft on this device. Keep this page open until the upload is finished.');
            });
        });

        /** "Upload File" */
        w.submitAssignmentFile = async function () {
            if (busy) return;
            setErr('');
            var id = currentId;
            if (id == null) { setErr('Something went wrong. Close this and try again.'); return; }
            var file = currentFile();
            if (!file) { setErr('Please choose a file.'); return; }
            var bad = checkFile(file);
            if (bad) { setErr(bad); return; }

            busy = true;
            var btn = $('assign-submit-btn');
            var originalText = btn ? btn.innerText : 'Upload File';
            var ctl = null, timer = null, onOffline = null, uploading = false;
            try {
                if (typeof w.elmsConfirmSubmit === 'function') {
                    var ok = await w.elmsConfirmSubmit({
                        title: 'Submit your assignment?',
                        message: 'Are you sure you want to submit “' + file.name + '”?'
                    });
                    if (!ok) return;
                }
                uploading = true;
                if (w.navigator && w.navigator.onLine === false) throw new Error('offline');

                var fd = new FormData();
                fd.append('assignment_id', id);
                fd.append('file', file, file.name);
                if (btn) { btn.disabled = true; btn.innerText = 'Uploading...'; }

                ctl = ('AbortController' in w) ? new AbortController() : null;
                if (ctl) {
                    onOffline = function () { try { ctl.abort(); } catch (e) { } };
                    w.addEventListener('offline', onOffline);
                    timer = setTimeout(function () { try { ctl.abort(); } catch (e) { } }, UPLOAD_TIMEOUT);
                }
                var res = await fetch('../api/assignments.php?action=submit', { method: 'POST', body: fd, signal: ctl ? ctl.signal : undefined });
                var data = null;
                try { data = await res.json(); } catch (e) { data = null; }

                if (data && data.success) {
                    await dropDraft(uid, id);
                    restored = null;
                    var modal = $('assignSubmitModal'); if (modal) modal.classList.add('hidden');
                    if (typeof w.notifyProgressChanged === 'function') w.notifyProgressChanged();
                    onSuccess();
                } else if (data) {
                    setErr(data.message || 'Upload failed.');            // draft stays saved
                } else {
                    throw new Error('bad response');
                }
            } catch (e) {
                if (!uploading) return;                                   // cancelled at the confirm step
                connLost = true;
                // Make sure the file is kept as a draft, then tell the student plainly
                var kept = true;
                try { await saveDraft(uid, id, file, expiryFor(id)); fire(); } catch (e2) { kept = false; }
                setErr(kept
                    ? 'Connection lost. Your file was not submitted, but it is saved as a draft on this device. When you are back online, press Upload File again.'
                    : 'Connection lost. Your file was not submitted. Please keep this page open and try again once you are back online.');
            } finally {
                if (timer) clearTimeout(timer);
                if (onOffline) w.removeEventListener('offline', onOffline);
                if (btn) { btn.disabled = false; btn.innerText = originalText; }
                busy = false;
            }
        };

        // Back online while the modal still shows the "connection lost" message
        w.addEventListener('online', function () {
            var modal = $('assignSubmitModal');
            if (connLost && modal && !modal.classList.contains('hidden')) {
                connLost = false;
                setErr('');
                setNote('You are back online. Press Upload File to submit your draft.', { discard: true });
            }
        });
    }

    /* ---------- "You have a saved draft" notice on the assignment page ---------- */
    function initBanner(cfg) {
        var el = document.getElementById(cfg.elId || 'sa-draft');
        var uid = Number(cfg.uid) || 0;
        if (!el) return;
        function render() {
            loadDraft(uid, cfg.id).then(function (rec) {
                el.textContent = '';
                if (!rec) { el.hidden = true; return; }
                var t = document.createElement('div'); t.className = 'sa-draft-t';
                var b = document.createElement('b'); b.textContent = 'Draft saved';
                var s = document.createElement('span');
                s.textContent = rec.name + ' · ' + fmtSize(rec.size) + ' · ' + fmtTime(rec.savedAt) + ' · not submitted yet';
                t.appendChild(b); t.appendChild(s);

                var row = document.createElement('div'); row.className = 'sa-draft-b';
                var go = document.createElement('button'); go.type = 'button'; go.className = 'sa-draft-go'; go.textContent = 'Continue';
                go.onclick = function () { if (typeof w.openAssignSubmitModal === 'function') w.openAssignSubmitModal(cfg.id, cfg.title); };
                var rm = document.createElement('button'); rm.type = 'button'; rm.className = 'sa-draft-rm'; rm.textContent = 'Discard';
                rm.onclick = function () { dropDraft(uid, cfg.id); };
                row.appendChild(go); row.appendChild(rm);

                el.appendChild(t); el.appendChild(row);
                el.hidden = false;
            });
        }
        listeners.push(render);
        render();
    }

    w.ElmsDraft = {
        initAssignment: initAssignment,
        initBanner: initBanner,
        drop: dropDraft
    };
})(window);