<?php /* body partial — rendered inside a tabbed admin page */ ?>
<style>
    .msg-row {
        cursor: pointer;
    }

    .msg-row.is-new td {
        font-weight: 600;
    }

    .msg-row.is-new .msg-subject::before {
        content: '';
        display: inline-block;
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: var(--primary);
        margin-right: .5rem;
        vertical-align: middle;
    }

    .msg-preview {
        color: var(--text-muted);
        font-weight: 400 !important;
        font-size: .8rem;
        display: block;
        max-width: 420px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .msg-count {
        font-size: .8rem;
        color: var(--text-muted);
        font-weight: 500;
        margin-left: .4rem;
    }

    .msg-modal {
        position: fixed;
        inset: 0;
        z-index: 9000;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 1rem;
        background: rgba(15, 23, 42, .5);
    }

    .msg-modal.open {
        display: flex;
    }

    .msg-box {
        background: var(--bg-card, #fff);
        color: var(--text-primary);
        width: 100%;
        max-width: 620px;
        max-height: calc(100vh - 2rem);
        overflow-y: auto;
        border-radius: 16px;
        padding: 1.5rem 1.5rem 1.25rem;
        box-shadow: 0 20px 60px rgba(0, 0, 0, .25);
        position: relative;
    }

    .msg-close {
        position: absolute;
        top: .9rem;
        right: .9rem;
        width: 34px;
        height: 34px;
        border-radius: 50%;
        border: 0;
        background: var(--bg-body, #f1f5f9);
        color: var(--text-muted);
        font-size: 1.2rem;
        cursor: pointer;
    }

    .msg-box h3 {
        font-size: 1.15rem;
        margin: 0 2.5rem .35rem 0;
    }

    .msg-meta {
        font-size: .82rem;
        color: var(--text-muted);
        margin-bottom: 1rem;
        line-height: 1.6;
    }

    .msg-meta b {
        color: var(--text-primary);
        font-weight: 600;
    }

    .msg-body {
        white-space: pre-wrap;
        word-wrap: break-word;
        line-height: 1.65;
        font-size: .92rem;
        background: var(--bg-body, #f8fafc);
        border: 1px solid var(--border);
        border-radius: 12px;
        padding: 1rem;
    }

    .msg-actions {
        display: flex;
        flex-wrap: wrap;
        gap: .5rem;
        margin-top: 1.1rem;
    }

    @media (max-width: 640px) {
        .msg-hide-sm {
            display: none;
        }

        .msg-preview {
            max-width: 200px;
        }
    }
    .msg-reply {
        margin-top: 1rem;
        padding: 1rem;
        border: 1px solid var(--border);
        border-radius: 12px;
        background: #fff;
    }

    .msg-reply[hidden] {
        display: none;
    }

    .msg-reply label {
        display: block;
        font-size: .82rem;
        font-weight: 600;
        margin-bottom: .4rem;
    }

    .msg-reply textarea {
        width: 100%;
        min-height: 120px;
        resize: vertical;
        padding: .7rem .8rem;
        border: 1.5px solid var(--border);
        border-radius: 10px;
        font: inherit;
        font-size: .92rem;
        line-height: 1.55;
        background: var(--bg-body, #f8fafc);
        color: inherit;
    }

    .msg-reply textarea:focus {
        outline: none;
        border-color: var(--primary);
        background: #fff;
    }

    .msg-reply-row {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: .5rem;
        margin-top: .7rem;
    }

    .msg-reply-alt {
        margin-left: auto;
        font-size: .78rem;
        color: var(--primary);
    }

    .msg-replied {
        margin-top: .8rem;
        font-size: .82rem;
        font-weight: 600;
        color: #166534;
    }

    .msg-replied:empty {
        display: none;
    }

    /* ── Pagination ── */
    .msg-pager { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: .75rem 1rem; padding: .9rem 1.5rem;
                 border-top: 1px solid var(--border-muted, #eef1f5); font-size: .8rem; color: var(--text-secondary, #64748b); }
    .msg-pager[hidden] { display: none; }
    .msg-pages { display: flex; align-items: center; gap: .25rem; flex-wrap: wrap; }
    .msg-pg { min-width: 32px; height: 32px; padding: 0 .55rem; border-radius: 8px; border: 1px solid var(--border, #e2e8f0); background: var(--surface, #fff);
              color: var(--text-primary, #0f172a); font: 600 .78rem/1 'Nunito', sans-serif; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; }
    .msg-pg:hover:not([disabled]):not(.active) { border-color: var(--primary); color: var(--primary); }
    .msg-pg.active { background: var(--primary); border-color: var(--primary); color: #fff; cursor: default; }
    .msg-pg[disabled] { opacity: .4; cursor: default; }
    .msg-pg-gap { padding: 0 .25rem; color: var(--text-muted, #94a3b8); }
    .msg-nomatch { text-align: center; padding: 2rem; color: var(--text-muted, #94a3b8); font-size: .84rem; }
    .msg-nomatch[hidden] { display: none; }
    @media (max-width: 760px) { .msg-pager { justify-content: center; padding: .85rem 1rem; } }

    /* ── Responsive inbox ── */
    .msg-av, .msg-sent-short { display: none; }
    .msg-table td.msg-from .msg-from-text { display: block; min-width: 0; }
    .msg-table td.msg-sent { font-size: .8rem; color: var(--text-secondary); }
    .msg-head .table-actions { flex: 1 1 auto; justify-content: flex-end; }
    .msg-head .msg-search { flex: 0 1 280px; min-width: 0; }

    @media (max-width: 1000px) {
        .msg-table .msg-role { display: none; }
    }

    @media (max-width: 760px) {
        .msg-head { padding: 1rem; }
        .msg-head h3 { width: 100%; }
        .msg-head .table-actions { width: 100%; display: grid; grid-template-columns: minmax(0, 1fr) auto; gap: .5rem; }
        .msg-head .msg-search, .msg-head .msg-filter { width: 100%; height: 40px; flex: none; }

        .msg-table, .msg-table tbody { display: block; width: 100%; min-width: 0 !important; }
        .msg-table thead { display: none; }
        .msg-table tr.msg-row {
            display: grid; grid-template-columns: 40px minmax(0, 1fr) auto; column-gap: .75rem; row-gap: .1rem;
            padding: .85rem 1rem; border-bottom: 1px solid var(--border-muted); -webkit-tap-highlight-color: transparent;
        }
        .msg-table tr.msg-row:active { background: var(--primary-light); }
        .msg-table tr.msg-row td { display: block; padding: 0; border: 0; min-width: 0; background: transparent !important; }
        .msg-table td.msg-from { display: contents !important; }
        .msg-av {
            display: flex; grid-row: 1 / span 2; grid-column: 1; align-self: start;
            width: 40px; height: 40px; border-radius: 50%; align-items: center; justify-content: center;
            background: var(--primary-light); color: var(--primary); font: 800 .82rem/1 'Nunito', sans-serif;
        }
        .msg-row.is-new .msg-av { background: var(--primary); color: #fff; }
        .msg-table .msg-from-text { grid-column: 2; grid-row: 1; align-self: center; overflow: hidden; }
        .msg-table .msg-name { display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; font-size: .9rem; color: var(--text-primary); }
        .msg-table .msg-email { display: none; }
        .msg-table td.msg-sent { grid-column: 3; grid-row: 1; align-self: center; font-size: .74rem; }
        .msg-row.is-new td.msg-sent { color: var(--primary); }
        .msg-sent-full { display: none; }
        .msg-sent-short { display: inline; }
        .msg-table td.msg-subj { grid-column: 2 / span 2; grid-row: 2; }
        .msg-table td.msg-subj .msg-subject { display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; font-size: .85rem; color: var(--text-primary); }
        .msg-table td.msg-subj .msg-preview { max-width: none; font-size: .8rem; margin-top: .1rem; }
        .msg-row.is-new td.msg-subj .msg-subject::before { display: none; }
        .msg-table tr.msg-row td.msg-stat, .msg-table tr.msg-row td.msg-role { display: none; }
        .msg-table tr.msg-empty, .msg-table tr.msg-empty td { display: block; }

        /* Message popup */
        .msg-modal { padding: .75rem; }
        .msg-box { padding: 1.15rem 1.1rem 1rem; max-height: calc(100dvh - 1.5rem); border-radius: 14px; }
        .msg-box h3 { font-size: 1.02rem; overflow-wrap: anywhere; }
        .msg-meta { font-size: .8rem; overflow-wrap: anywhere; }
        .msg-body { font-size: .88rem; padding: .85rem; }
        .msg-actions { display: grid; grid-template-columns: 1fr 1fr; }
        .msg-actions #msg-m-reply { grid-column: 1 / -1; }
        .msg-actions .btn, .msg-reply-row .btn { justify-content: center; padding-top: .6rem; padding-bottom: .6rem; font-size: .82rem; }
        .msg-reply { padding: .85rem; }
        .msg-reply-row .btn { flex: 1 1 0; }
        .msg-reply-alt { margin: .35rem 0 0; width: 100%; text-align: center; }
    }

    html[data-theme="dark"] .msg-reply, html[data-theme="dark"] .msg-reply textarea:focus { background: transparent; }
    /* New message that just arrived live — soft highlight that fades out */
    @keyframes msgArrive { from { background: var(--primary-light, #e8effc); } to { background: transparent; } }
    .msg-row.msg-flash td { animation: msgArrive 2.4s ease-out; }
    @media (prefers-reduced-motion: reduce) { .msg-row.msg-flash td { animation: none; } }
</style>

<div class="section-panel active">
    <?php render_admin_tabs(); ?>
    <div class="table-card">
        <div class="table-header msg-head">
            <h3>Contact Us messages<span class="msg-count"
                    id="msg-new-label"><?= $contactNewCount ? $contactNewCount . ' new' : '' ?></span></h3>
            <div class="table-actions">
                <input class="search-input msg-search" type="search" id="msg-search" placeholder="Search name, email, subject..."
                    oninput="filterMessages(true)" autocomplete="off">
                <select class="search-input msg-filter" id="msg-filter" onchange="filterMessages(true)">
                    <option value="">All messages</option>
                    <option value="new">New only</option>
                    <option value="read">Read</option>
                </select>
            </div>
        </div>
        <table class="msg-table">
            <thead>
                <tr>
                    <th>From</th>
                    <th>Subject</th>
                    <th class="msg-hide-sm">Role</th>
                    <th>Sent</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody id="msg-tbody">
                <?php if (empty($contactMessages)): ?>
                    <tr class="msg-empty">
                        <td colspan="5" style="text-align:center;color:var(--ui-text-3, #bbb);padding:2rem;">No messages yet. Messages sent
                            from the Contact Us page will appear here.</td>
                    </tr>
                <?php else:
                    foreach ($contactMessages as $m):
                        $isNew = $m['status'] === 'New';
                        $search = strtolower($m['first_name'] . ' ' . $m['last_name'] . ' ' . $m['email'] . ' ' . $m['subject']); ?>
                        <tr class="msg-row<?= $isNew ? ' is-new' : '' ?>" data-id="<?= (int) $m['id'] ?>"
                            data-search="<?= htmlspecialchars($search) ?>" data-status="<?= $isNew ? 'new' : 'read' ?>"
                            onclick="openMessage(<?= (int) $m['id'] ?>)" tabindex="0"
                            onkeydown="if(event.key==='Enter')openMessage(<?= (int) $m['id'] ?>)">
                            <?php $__ini = strtoupper(mb_substr(trim($m['first_name']), 0, 1) . mb_substr(trim($m['last_name']), 0, 1)); ?>
                            <td class="msg-from"><span class="msg-av" aria-hidden="true"><?= htmlspecialchars($__ini ?: '?') ?></span><span class="msg-from-text"><span class="msg-name"><?= htmlspecialchars($m['first_name'] . ' ' . $m['last_name']) ?></span>
                                <span class="msg-preview msg-email"><?= htmlspecialchars($m['email']) ?></span></span>
                            </td>
                            <td class="msg-subj"><span class="msg-subject"><?= htmlspecialchars($m['subject']) ?></span>
                                <span class="msg-preview"><?= htmlspecialchars(mb_substr($m['message'], 0, 120)) ?></span>
                            </td>
                            <td class="msg-hide-sm msg-role"><?= htmlspecialchars($m['role'] ?: '—') ?></td>
                            <td class="msg-sent" style="white-space:nowrap;"><span class="msg-sent-full"><?= htmlspecialchars($m['sent_at']) ?></span><span class="msg-sent-short"><?= htmlspecialchars(preg_replace('/,\s*\d{4}.*$/', '', $m['sent_at'])) ?></span></td>
                            <td class="msg-stat"><span class="badge-status <?= $isNew ? 'status-active' : 'status-inactive' ?>"
                                    id="msg-status-<?= (int) $m['id'] ?>"><?= $isNew ? 'New' : 'Read' ?></span></td>
                        </tr>
                    <?php endforeach; endif; ?>
            </tbody>
        </table>
        <div class="msg-nomatch" id="msg-nomatch" hidden>No messages match your search.</div>
        <div class="msg-pager" id="msg-pager" hidden>
            <span id="msg-pager-count" aria-live="polite"></span>
            <nav class="msg-pages" id="msg-pages" aria-label="Message pages"></nav>
        </div>
    </div>
</div>

<div class="msg-modal" id="msg-modal" role="dialog" aria-modal="true" aria-labelledby="msg-m-subject"
    onclick="if(event.target===this)closeMessage()">
    <div class="msg-box">
        <button class="msg-close" type="button" onclick="closeMessage()" aria-label="Close">&times;</button>
        <h3 id="msg-m-subject"></h3>
        <div class="msg-meta" id="msg-m-meta"></div>
        <div class="msg-body" id="msg-m-body"></div>
        <div class="msg-replied" id="msg-replied"></div>
        <div class="msg-reply" id="msg-reply-box" hidden>
            <label for="msg-reply-text">Your reply to <span id="msg-reply-to"></span></label>
            <textarea id="msg-reply-text" maxlength="5000" placeholder="Type your reply here..."></textarea>
            <div class="msg-reply-row">
                <button class="btn btn-primary btn-sm" type="button" id="msg-reply-send" onclick="sendReply(this)">Send reply</button>
                <button class="btn btn-sm" type="button" onclick="toggleReply(false)">Cancel</button>
                <a class="msg-reply-alt" id="msg-m-mailto" href="#">Open in my email app instead</a>
            </div>
        </div>
        <div class="msg-actions">
            <button class="btn btn-primary btn-sm" type="button" id="msg-m-reply" onclick="toggleReply()">Reply by email</button>
            <button class="btn btn-warning btn-sm" type="button" id="msg-m-unread" onclick="markUnread(this)">Mark as
                unread</button>
            <button class="btn btn-danger btn-sm" type="button" onclick="deleteMessage(this)">Delete</button>
        </div>
    </div>
</div>

<script>
    const MSGS = <?= json_encode(array_column($contactMessages, null, 'id'), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?: '{}' ?>;
    let msgOpenId = null;

    function esc(s) { const d = document.createElement('div'); d.textContent = s ?? ''; return d.innerHTML; }

    // Search + status filter + pagination. filterMessages(true) = the admin changed the search/filter → back to page 1;
    // without the flag (live refresh, delete) the current page is kept.
    const MSG_PER_PAGE = 5;
    let msgPage = 1;

    function msgPgBtn(label, target, o) {
        o = o || {};
        const b = document.createElement('button');
        b.type = 'button'; b.className = 'msg-pg' + (o.active ? ' active' : ''); b.innerHTML = label;
        if (o.aria) b.setAttribute('aria-label', o.aria);
        if (o.active) b.setAttribute('aria-current', 'page');
        if (o.disabled) b.disabled = true; else if (!o.active) b.onclick = () => { msgPage = target; filterMessages(); document.getElementById('msg-tbody').scrollIntoView({ block: 'nearest' }); };
        return b;
    }

    function filterMessages(resetPage) {
        const q = document.getElementById('msg-search').value.toLowerCase();
        const f = document.getElementById('msg-filter').value;
        if (resetPage) msgPage = 1;
        const rows = Array.from(document.querySelectorAll('#msg-tbody tr[data-id]'));
        const hit = rows.filter(tr => (!q || tr.dataset.search.includes(q)) && (!f || tr.dataset.status === f));
        const total = hit.length, pages = Math.max(1, Math.ceil(total / MSG_PER_PAGE));
        if (msgPage > pages) msgPage = pages;
        const from = (msgPage - 1) * MSG_PER_PAGE, to = from + MSG_PER_PAGE;
        rows.forEach(tr => { tr.style.display = 'none'; });
        hit.forEach((tr, i) => { if (i >= from && i < to) tr.style.display = ''; });

        document.getElementById('msg-nomatch').hidden = !(rows.length && total === 0);
        document.getElementById('msg-pager').hidden = total === 0;   // "Showing" is always visible; page buttons only with more than one page
        document.getElementById('msg-pager-count').textContent = total ? 'Showing ' + (from + 1) + '\u2013' + Math.min(to, total) + ' of ' + total : '';
        const nav = document.getElementById('msg-pages');
        nav.innerHTML = '';
        if (total <= MSG_PER_PAGE) return;
        nav.appendChild(msgPgBtn('&lsaquo;', msgPage - 1, { disabled: msgPage === 1, aria: 'Previous page' }));
        let last = 0;
        for (let n = 1; n <= pages; n++) {
            if (n === 1 || n === pages || Math.abs(n - msgPage) <= 1) {
                if (n - last > 1) { const g = document.createElement('span'); g.className = 'msg-pg-gap'; g.textContent = '\u2026'; nav.appendChild(g); }
                nav.appendChild(msgPgBtn(String(n), n, { active: n === msgPage, aria: 'Page ' + n }));
                last = n;
            }
        }
        nav.appendChild(msgPgBtn('&rsaquo;', msgPage + 1, { disabled: msgPage === pages, aria: 'Next page' }));
    }

    function updateNewCount(n) {
        document.getElementById('msg-new-label').textContent = n ? n + ' new' : '';
        document.querySelectorAll('[data-msg-badge]').forEach(b => { b.textContent = n; b.style.display = n ? 'inline-flex' : 'none'; });
    }

    function setRowStatus(id, status) {
        const tr = document.querySelector(`tr[data-id="${id}"]`);
        const badge = document.getElementById('msg-status-' + id);
        if (!tr || !badge) return;
        const isNew = status === 'New';
        tr.classList.toggle('is-new', isNew);
        tr.dataset.status = isNew ? 'new' : 'read';
        badge.textContent = status;
        badge.className = 'badge-status ' + (isNew ? 'status-active' : 'status-inactive');
        MSGS[id].status = status;
    }

    async function markStatus(id, status) {
        const res = await fetch('../api/contact_messages.php?action=mark', {
            method: 'POST', headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id, status })
        });
        const data = await res.json();
        if (data.success) { setRowStatus(id, status); updateNewCount(data.unread); }
        return data;
    }

    function openMessage(id) {
        const m = MSGS[id];
        if (!m) return;
        msgOpenId = id;
        document.getElementById('msg-m-subject').textContent = m.subject;
        document.getElementById('msg-m-meta').innerHTML =
            '<b>' + esc(m.first_name + ' ' + m.last_name) + '</b> &lt;' + esc(m.email) + '&gt;' +
            (m.role ? ' · ' + esc(m.role) : '') + '<br>Sent ' + esc(m.sent_at);
        document.getElementById('msg-m-body').textContent = m.message;
        document.getElementById('msg-m-mailto').href = 'mailto:' + m.email +
            '?subject=' + encodeURIComponent('Re: ' + m.subject);
        document.getElementById('msg-reply-to').textContent = m.first_name + ' ' + m.last_name + ' <' + m.email + '>';
        document.getElementById('msg-reply-text').value = '';
        toggleReply(false);
        showReplied(m);
        document.getElementById('msg-modal').classList.add('open');
        if (m.status === 'New') markStatus(id, 'Read').catch(() => { });
    }

    // ── Reply by e-mail from inside the system ──
    function showReplied(m) {
        document.getElementById('msg-replied').textContent = m && m.replied_at ? '\u2713 You replied on ' + m.replied_at : '';
    }

    function toggleReply(show) {
        const box = document.getElementById('msg-reply-box');
        const open = typeof show === 'boolean' ? show : box.hidden;
        box.hidden = !open;
        if (open) document.getElementById('msg-reply-text').focus();
    }

    async function sendReply(btn) {
        if (!msgOpenId) return;
        const ta = document.getElementById('msg-reply-text');
        const text = ta.value.trim();
        if (!text) { showToast('Please type your reply first.', true); ta.focus(); return; }
        const id = msgOpenId;
        setBtnLoading(btn, 'Sending...');
        try {
            const res = await fetch('../api/contact_messages.php?action=reply', {
                method: 'POST', headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id, message: text })
            });
            const data = await res.json();
            if (data.success) {
                showToast('Reply sent.');
                if (MSGS[id]) { MSGS[id].replied_at = data.replied_at; setRowStatus(id, 'Read'); }
                updateNewCount(data.unread);
                ta.value = '';
                toggleReply(false);
                showReplied(MSGS[id]);
            } else showToast(data.message || 'Could not send the reply.', true);
        } catch (e) { showToast('Network error.', true); }
        clearBtnLoading(btn);
    }

    function closeMessage() {
        document.getElementById('msg-modal').classList.remove('open');
        msgOpenId = null;
    }
    document.addEventListener('keydown', e => { if (e.key === 'Escape') closeMessage(); });

    async function markUnread(btn) {
        if (!msgOpenId) return;
        setBtnLoading(btn, 'Saving...');
        try {
            const data = await markStatus(msgOpenId, 'New');
            if (data.success) { showToast('Marked as unread.'); closeMessage(); }
            else showToast(data.message, true);
        } catch (e) { showToast('Network error.', true); }
        clearBtnLoading(btn);
    }

    async function deleteMessage(btn) {
        if (!msgOpenId) return;
        if (!await confirmAction('Delete this message? This cannot be undone.', { okText: 'Delete' })) return;
        const id = msgOpenId;
        setBtnLoading(btn, 'Deleting...');
        try {
            const res = await fetch(`../api/contact_messages.php?action=delete&id=${id}`, { method: 'DELETE' });
            const data = await res.json();
            if (data.success) {
                showToast('Message deleted.');
                closeMessage();
                document.querySelector(`tr[data-id="${id}"]`)?.remove();
                delete MSGS[id];
                updateNewCount(data.unread);
                filterMessages();
            } else showToast(data.message, true);
        } catch (e) { showToast('Network error.', true); }
        clearBtnLoading(btn);
    }

    // ── Real-time inbox ──────────────────────────────────────────
    // A new Contact Us message (or a change made by another admin) arrives through the live poller
    // (includes/live.php → api/sync.php, ~5 s). The list is redrawn in place: no reload, the open
    // message window, the search box and the filter are left alone.
    const escA = s => esc(s).replace(/"/g, '&quot;').replace(/'/g, '&#39;');

    function msgRowHtml(m) {
        const isNew = m.status === 'New';
        const name = ((m.first_name || '') + ' ' + (m.last_name || '')).trim();
        const ini = ((m.first_name || '').trim().charAt(0) + (m.last_name || '').trim().charAt(0)).toUpperCase() || '?';
        const search = (name + ' ' + (m.email || '') + ' ' + (m.subject || '')).toLowerCase();
        const short = (m.sent_at || '').replace(/,\s*\d{4}.*$/, '');
        const preview = Array.from(m.message || '').slice(0, 120).join('');
        const id = parseInt(m.id, 10);
        return '<tr class="msg-row' + (isNew ? ' is-new' : '') + '" data-id="' + id + '" data-search="' + escA(search) + '" data-status="' + (isNew ? 'new' : 'read') + '"' +
            ' onclick="openMessage(' + id + ')" tabindex="0" onkeydown="if(event.key===\'Enter\')openMessage(' + id + ')">' +
            '<td class="msg-from"><span class="msg-av" aria-hidden="true">' + esc(ini) + '</span><span class="msg-from-text"><span class="msg-name">' + esc(name) + '</span>' +
            '<span class="msg-preview msg-email">' + esc(m.email) + '</span></span></td>' +
            '<td class="msg-subj"><span class="msg-subject">' + esc(m.subject) + '</span><span class="msg-preview">' + esc(preview) + '</span></td>' +
            '<td class="msg-hide-sm msg-role">' + esc(m.role || '\u2014') + '</td>' +
            '<td class="msg-sent" style="white-space:nowrap;"><span class="msg-sent-full">' + esc(m.sent_at) + '</span><span class="msg-sent-short">' + esc(short) + '</span></td>' +
            '<td class="msg-stat"><span class="badge-status ' + (isNew ? 'status-active' : 'status-inactive') + '" id="msg-status-' + id + '">' + (isNew ? 'New' : 'Read') + '</span></td></tr>';
    }

    let msgRefreshing = false, msgRefreshAgain = false;
    async function refreshMessages(events) {
        // my own click (read / reply / delete) already updated this page
        if (events && events.length && events.every(ev => ev.self)) return;
        if (msgRefreshing) { msgRefreshAgain = true; return; }
        msgRefreshing = true;
        try {
            const res = await fetch('../api/contact_messages.php?action=list', { cache: 'no-store', credentials: 'same-origin' });
            if (res.status === 401 || res.status === 403) { location.href = '../login.php'; return; }
            const data = await res.json();
            if (!data.success) return;

            const known = new Set(Object.keys(MSGS));
            Object.keys(MSGS).forEach(k => delete MSGS[k]);
            data.messages.forEach(m => { MSGS[m.id] = m; });

            const tb = document.getElementById('msg-tbody');
            tb.innerHTML = data.messages.length
                ? data.messages.map(msgRowHtml).join('')
                : '<tr class="msg-empty"><td colspan="5" style="text-align:center;color:var(--ui-text-3, #bbb);padding:2rem;">No messages yet. Messages sent from the Contact Us page will appear here.</td></tr>';
            if (known.size) data.messages.forEach(m => {
                if (!known.has(String(m.id))) document.querySelector('tr[data-id="' + parseInt(m.id, 10) + '"]')?.classList.add('msg-flash');
            });

            filterMessages();                 // keep the search / filter the admin typed
            updateNewCount(data.unread);      // "N new" label + sidebar badge
            if (msgOpenId && !MSGS[msgOpenId]) closeMessage();   // deleted by another admin while open
        } catch (e) {
            console.warn('[messages] live refresh failed', e);
        } finally {
            msgRefreshing = false;
            if (msgRefreshAgain) { msgRefreshAgain = false; refreshMessages(); }
        }
    }

    addEventListener('DOMContentLoaded', () => {
        if (window.ElmsLive) {
            ElmsLive.onRefresh(['message'], ev => refreshMessages(ev));
        } else {
            // live feed not installed (database/migration_integration.sql): plain 10 s refresh while the tab is visible
            setInterval(() => { if (!document.hidden) refreshMessages(); }, 10000);
        }
    });

    filterMessages();   // first page on load

    // Opened from the notification bell with ?open=ID
    (function () {
        const id = new URLSearchParams(location.search).get('open');
        if (id && MSGS[id]) openMessage(id);
    })();
</script>