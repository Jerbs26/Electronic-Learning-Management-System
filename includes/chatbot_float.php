<?php
// ============================================================
//  Arandia College eLMS — EduBot Floating Button (Teacher side)
//  File: includes/chatbot_float.php
//
//  Rendered once per page by includes/footer.php (Teacher branch).
//  NOT rendered on teacher/edubot.php — that page is the full chat.
//  Talks to the same endpoint as the full chat: ../api/chatbot.php
// ============================================================

// Render at most once per request (prevents duplicate buttons).
if (defined('EDUBOT_FLOAT_RENDERED')) return;
define('EDUBOT_FLOAT_RENDERED', true);

// Teacher side only, and never on the dedicated EduBot AI page.
if (($_SESSION['role'] ?? '') !== 'Teacher') return;
if (($currentPage ?? '') === 'edubot.php' || basename($_SERVER['SCRIPT_NAME'] ?? '') === 'edubot.php') return;

$ebfUserId    = (int) ($_SESSION['user_id'] ?? 0);
$ebfFirstName = $_SESSION['first_name'] ?? 'there';
$ebfPrompts   = ['Pending submissions', 'How to create a quiz', 'Attendance summary', 'My subjects'];
?>
<style>
    /* ── EduBot floating button (scoped: .ebf-*) ─────────────────── */
    .ebf-fab {
        position: fixed;
        right: max(1.5rem, env(safe-area-inset-right));
        bottom: max(1.5rem, env(safe-area-inset-bottom));
        width: 58px;
        height: 58px;
        border-radius: 50%;
        border: none;
        background: #003087;
        color: #fff;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 6px 20px rgba(0, 48, 135, .35), 0 2px 6px rgba(0, 0, 0, .15);
        z-index: 550; /* above page content/sidebar/topbar, below modals (600), profile panel (700), toast (999) */
        transition: transform .18s ease, box-shadow .18s ease, background .18s ease;
        -webkit-tap-highlight-color: transparent;
    }
    .ebf-fab:hover { transform: translateY(-2px); background: #0044cc; box-shadow: 0 10px 26px rgba(0, 48, 135, .4), 0 3px 8px rgba(0, 0, 0, .15); }
    .ebf-fab:focus-visible { outline: 3px solid #FFD700; outline-offset: 3px; }
    .ebf-fab svg { width: 28px; height: 28px; }
    .ebf-fab .ebf-ico-close { display: none; width: 24px; height: 24px; }
    .ebf-fab.is-open .ebf-ico-bot { display: none; }
    .ebf-fab.is-open .ebf-ico-close { display: block; }

    .ebf-badge {
        position: absolute; top: 0; right: 0;
        width: 14px; height: 14px; border-radius: 50%;
        background: #e74c3c; border: 2px solid #fff;
        display: none;
    }
    .ebf-badge.show { display: block; }

    /* ── Window ───────────────────────────────────────────────────── */
    .ebf-win {
        position: fixed;
        right: max(1.5rem, env(safe-area-inset-right));
        bottom: calc(max(1.5rem, env(safe-area-inset-bottom)) + 72px);
        width: 370px;
        height: 520px;
        max-height: calc(100vh - 120px);
        max-height: calc(100dvh - 120px);
        background: var(--ui-surface, #fff);
        border-radius: 16px;
        box-shadow: 0 16px 48px rgba(15, 23, 42, .22);
        border: 1px solid var(--ui-border, #e2e8f0);
        z-index: 560;
        display: none;
        flex-direction: column;
        overflow: hidden;
        font-family: 'Open Sans', sans-serif;
    }
    .ebf-win.open { display: flex; animation: ebfIn .18s ease-out; }
    @keyframes ebfIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: none; } }

    .ebf-head {
        background: #003087; color: #fff;
        padding: .85rem 1rem;
        display: flex; align-items: center; gap: .7rem; flex-shrink: 0;
    }
    .ebf-avatar {
        width: 36px; height: 36px; border-radius: 50%;
        background: rgba(255, 255, 255, .18);
        display: flex; align-items: center; justify-content: center; flex-shrink: 0;
    }
    .ebf-avatar svg { width: 20px; height: 20px; }
    .ebf-title { min-width: 0; }
    .ebf-title strong { display: block; font-family: 'Nunito', sans-serif; font-size: .95rem; font-weight: 800; line-height: 1.2; }
    .ebf-title span { font-size: .7rem; opacity: .8; display: flex; align-items: center; gap: .3rem; }
    .ebf-title span::before { content: ''; width: 7px; height: 7px; border-radius: 50%; background: #34d399; }
    .ebf-head-actions { margin-left: auto; display: flex; gap: .25rem; }
    .ebf-hbtn {
        width: 32px; height: 32px; border-radius: 8px; border: none;
        background: transparent; color: #fff; cursor: pointer; opacity: .85;
        display: flex; align-items: center; justify-content: center;
        text-decoration: none;
    }
    .ebf-hbtn:hover { opacity: 1; background: rgba(255, 255, 255, .14); }
    .ebf-hbtn svg { width: 17px; height: 17px; }

    .ebf-msgs {
        flex: 1; overflow-y: auto; padding: 1rem;
        display: flex; flex-direction: column; gap: .6rem;
        background: var(--ui-surface-2, #f8fafc);
        overscroll-behavior: contain;
    }
    .ebf-msg {
        max-width: 85%; padding: .6rem .85rem; border-radius: 14px;
        font-size: .82rem; line-height: 1.55; word-break: break-word;
    }
    .ebf-msg.bot { background: var(--ui-surface, #fff); color: var(--ui-text, #0f172a); border-radius: 4px 14px 14px 14px; box-shadow: 0 1px 3px rgba(0, 0, 0, .08); align-self: flex-start; }
    .ebf-msg.user { background: #003087; color: #fff; border-radius: 14px 4px 14px 14px; align-self: flex-end; }
    .ebf-msg.typing { color: var(--ui-text-3, #94a3b8); font-style: italic; }
    .ebf-msg code { background: rgba(0, 0, 0, .06); padding: .05rem .3rem; border-radius: 4px; font-size: .85em; }
    .ebf-time { font-size: .62rem; opacity: .55; margin-top: .2rem; text-align: right; }

    .ebf-quick { padding: .55rem .9rem; display: flex; flex-wrap: wrap; gap: .4rem; background: var(--ui-surface, #fff); border-top: 1px solid var(--ui-border-soft, #f1f5f9); flex-shrink: 0; }
    .ebf-quick.hidden { display: none; }
    .ebf-chip {
        border: 1px solid #c7d4ee; background: #eef3fc; color: #003087;
        font-size: .72rem; font-weight: 600; padding: .3rem .7rem; border-radius: 999px; cursor: pointer;
        font-family: inherit;
    }
    .ebf-chip:hover { background: #003087; color: #fff; border-color: #003087; }

    .ebf-input-row { display: flex; gap: .5rem; padding: .75rem .9rem; border-top: 1px solid var(--ui-border-soft, #f1f5f9); background: var(--ui-surface, #fff); flex-shrink: 0; }
    .ebf-input {
        flex: 1; min-width: 0; padding: .6rem .85rem; border: 1.5px solid var(--ui-border, #e2e8f0); border-radius: 10px;
        font-size: .85rem; font-family: inherit; outline: none; background: var(--ui-surface, #fff); color: var(--ui-text, #0f172a);
    }
    .ebf-input:focus { border-color: #003087; }
    .ebf-send {
        width: 40px; height: 40px; border-radius: 10px; border: none; background: #003087; color: #fff;
        cursor: pointer; display: flex; align-items: center; justify-content: center; flex-shrink: 0;
    }
    .ebf-send svg { width: 17px; height: 17px; }
    .ebf-send:disabled { opacity: .45; cursor: not-allowed; }

    /* Keep the toast and the last rows of a page clear of the button */
    body.has-ebf .toast { bottom: calc(max(1.5rem, env(safe-area-inset-bottom)) + 72px); }
    body.has-ebf .main-content { padding-bottom: 6rem; }

    /* ── Dark mode (follows the Teacher topbar toggle) ─────────────── */
    html[data-theme="dark"] .ebf-win { background: #1e293b; border-color: #334155; }
    html[data-theme="dark"] .ebf-msgs { background: #0f172a; }
    html[data-theme="dark"] .ebf-msg.bot { background: #1e293b; color: #e2e8f0; }
    html[data-theme="dark"] .ebf-quick,
    html[data-theme="dark"] .ebf-input-row { background: #1e293b; border-top-color: #334155; }
    html[data-theme="dark"] .ebf-input { background: #0f172a; border-color: #334155; color: #e2e8f0; }
    html[data-theme="dark"] .ebf-chip { background: #1e3a6e; border-color: #1e3a6e; color: #dbe6ff; }
    html[data-theme="dark"] .ebf-badge { border-color: #1e293b; }

    /* ── Tablet / mobile ──────────────────────────────────────────── */
    @media (max-width: 700px) {
        .ebf-fab { width: 54px; height: 54px; right: max(1rem, env(safe-area-inset-right)); bottom: max(1rem, env(safe-area-inset-bottom)); }
        .ebf-win { right: max(1rem, env(safe-area-inset-right)); bottom: calc(max(1rem, env(safe-area-inset-bottom)) + 66px); }
        body.has-ebf .toast { bottom: calc(max(1rem, env(safe-area-inset-bottom)) + 66px); right: 1rem; }
    }
    @media (max-width: 480px) {
        .ebf-win {
            left: .75rem; right: .75rem; width: auto;
            height: auto;
            max-height: calc(100vh - 100px);
            max-height: calc(100dvh - 100px);
            top: auto;
        }
        .ebf-input { font-size: 16px; } /* prevents iOS zoom-on-focus */
    }
    @media (prefers-reduced-motion: reduce) {
        .ebf-fab, .ebf-win.open { transition: none; animation: none; }
    }
</style>

<button type="button" class="ebf-fab" id="ebfFab" aria-label="Open EduBot AI assistant" aria-expanded="false" aria-controls="ebfWin" title="Ask EduBot">
    <svg class="ebf-ico-bot" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
        <rect x="4" y="8" width="16" height="11" rx="3"/>
        <path d="M12 4v4"/><circle cx="12" cy="3.5" r="1"/>
        <circle cx="9" cy="13.5" r="1.2" fill="currentColor" stroke="none"/><circle cx="15" cy="13.5" r="1.2" fill="currentColor" stroke="none"/>
        <path d="M2 12.5v2.5M22 12.5v2.5"/>
    </svg>
    <svg class="ebf-ico-close" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18"/></svg>
    <span class="ebf-badge" id="ebfBadge"></span>
</button>

<div class="ebf-win" id="ebfWin" role="dialog" aria-label="EduBot AI assistant" aria-hidden="true">
    <div class="ebf-head">
        <div class="ebf-avatar">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <rect x="4" y="8" width="16" height="11" rx="3"/><path d="M12 4v4"/>
                <circle cx="9" cy="13.5" r="1.2" fill="currentColor" stroke="none"/><circle cx="15" cy="13.5" r="1.2" fill="currentColor" stroke="none"/>
            </svg>
        </div>
        <div class="ebf-title">
            <strong>EduBot</strong>
            <span>AI Teaching Assistant</span>
        </div>
        <div class="ebf-head-actions">
            <button type="button" class="ebf-hbtn" id="ebfClear" title="Clear conversation" aria-label="Clear conversation">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18M8 6V4h8v2M6 6l1 14h10l1-14"/></svg>
            </button>
            <a class="ebf-hbtn" id="ebfExpand" href="edubot.php" title="Open full EduBot AI page" aria-label="Open full EduBot AI page">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h6v6M9 21H3v-6M21 3l-7 7M3 21l7-7"/></svg>
            </a>
            <button type="button" class="ebf-hbtn" id="ebfClose" title="Close" aria-label="Close EduBot">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><path d="M6 6l12 12M18 6L6 18"/></svg>
            </button>
        </div>
    </div>
    <div class="ebf-msgs" id="ebfMsgs" aria-live="polite"></div>
    <div class="ebf-quick" id="ebfQuick">
        <?php foreach ($ebfPrompts as $qp): ?>
            <button type="button" class="ebf-chip" data-prompt="<?= htmlspecialchars($qp, ENT_QUOTES) ?>"><?= htmlspecialchars($qp) ?></button>
        <?php endforeach; ?>
    </div>
    <div class="ebf-input-row">
        <input class="ebf-input" type="text" id="ebfInput" placeholder="Ask EduBot anything..." autocomplete="off" maxlength="1000">
        <button type="button" class="ebf-send" id="ebfSend" aria-label="Send">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 2L11 13M22 2l-7 20-4-9-9-4 20-7z"/></svg>
        </button>
    </div>
</div>

<script>
(function () {
    'use strict';
    // Guard against the script running twice on the same page.
    if (window.__EDUBOT_FLOAT__) return;
    window.__EDUBOT_FLOAT__ = true;

    var UID       = <?= json_encode($ebfUserId) ?>;
    var NAME      = <?= json_encode($ebfFirstName) ?>;
    var API_URL   = '../api/chatbot.php';
    var CHAT_KEY  = 'edubot_float_' + UID;                  // this tab's floating conversation
    var SEEN_KEY  = 'edubot_float_seen_' + UID;             // badge shown once per session
    var FULL_KEY  = 'edubot_history_' + UID + '_Teacher';   // same key the full EduBot page uses

    var fab   = document.getElementById('ebfFab');
    var win   = document.getElementById('ebfWin');
    var badge = document.getElementById('ebfBadge');
    var msgs  = document.getElementById('ebfMsgs');
    var quick = document.getElementById('ebfQuick');
    var input = document.getElementById('ebfInput');
    var send  = document.getElementById('ebfSend');
    if (!fab || !win) return;

    document.body.classList.add('has-ebf');

    var history = [];   // [{role, content, ts}]
    var busy = false;

    // ── storage (sessionStorage keeps the chat while moving between pages) ──
    function load() {
        try { history = JSON.parse(sessionStorage.getItem(CHAT_KEY) || '[]'); } catch (e) { history = []; }
        if (!Array.isArray(history)) history = [];
    }
    function save() {
        try { sessionStorage.setItem(CHAT_KEY, JSON.stringify(history.slice(-40))); } catch (e) { }
    }

    // ── rendering ──
    function esc(s) {
        return String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }
    function fmt(text) {
        return esc(text)
            .replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>')
            .replace(/\*(.+?)\*/g, '<em>$1</em>')
            .replace(/`([^`]+?)`/g, '<code>$1</code>')
            .replace(/\n/g, '<br>');
    }
    function clock(iso) {
        var d = iso ? new Date(iso) : new Date();
        var h = d.getHours(), m = d.getMinutes(), ap = h >= 12 ? 'PM' : 'AM';
        h = h % 12 || 12;
        return h + ':' + String(m).padStart(2, '0') + ' ' + ap;
    }
    function bubble(type, html, ts) {
        var div = document.createElement('div');
        div.className = 'ebf-msg ' + type;
        div.innerHTML = html + '<div class="ebf-time">' + clock(ts) + '</div>';
        msgs.appendChild(div);
        return div;
    }
    function scrollDown() { msgs.scrollTop = msgs.scrollHeight; }
    function render() {
        msgs.innerHTML = '';
        bubble('bot', 'Hi ' + esc(NAME) + '! I\'m <strong>EduBot</strong>, your AI teaching assistant. How can I help you today?');
        history.forEach(function (m) { bubble(m.role === 'user' ? 'user' : 'bot', fmt(m.content), m.ts); });
        quick.classList.toggle('hidden', history.length > 0);
        scrollDown();
    }

    // ── open / close ──
    function open() {
        win.classList.add('open');
        win.setAttribute('aria-hidden', 'false');
        fab.classList.add('is-open');
        fab.setAttribute('aria-expanded', 'true');
        fab.setAttribute('aria-label', 'Close EduBot AI assistant');
        badge.classList.remove('show');
        try { sessionStorage.setItem(SEEN_KEY, '1'); } catch (e) { }
        scrollDown();
        if (window.matchMedia('(min-width: 701px)').matches) input.focus();
    }
    function close() {
        win.classList.remove('open');
        win.setAttribute('aria-hidden', 'true');
        fab.classList.remove('is-open');
        fab.setAttribute('aria-expanded', 'false');
        fab.setAttribute('aria-label', 'Open EduBot AI assistant');
    }
    function toggle() { win.classList.contains('open') ? close() : open(); }

    // ── send ──
    async function ask(text) {
        text = (text || '').trim();
        if (!text || busy) return;
        busy = true;
        send.disabled = true;
        input.value = '';
        quick.classList.add('hidden');

        var now = new Date().toISOString();
        history.push({ role: 'user', content: text, ts: now });
        save();
        bubble('user', fmt(text), now);
        var typing = bubble('bot typing', 'EduBot is typing...');
        scrollDown();

        var reply;
        try {
            var res = await fetch(API_URL, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'same-origin',
                body: JSON.stringify({ messages: history.map(function (m) { return { role: m.role, content: m.content }; }) })
            });
            var data = await res.json();
            reply = data.success ? data.reply : '⚠️ ' + (data.message || 'Something went wrong.');
        } catch (e) {
            reply = '⚠️ Could not reach the AI service. Please check your connection.';
        }

        typing.remove();
        var ts = new Date().toISOString();
        history.push({ role: 'assistant', content: reply, ts: ts });
        save();
        bubble('bot', fmt(reply), ts);
        scrollDown();
        busy = false;
        send.disabled = false;
        if (win.classList.contains('open')) input.focus();
    }

    // ── hand the floating chat over to the full EduBot page ──
    function handOff() {
        if (!history.length) return;
        try {
            var all = JSON.parse(localStorage.getItem(FULL_KEY) || '[]');
            if (!Array.isArray(all)) all = [];
            var first = history.find(function (m) { return m.role === 'user'; });
            var title = first ? first.content.slice(0, 40) + (first.content.length > 40 ? '…' : '') : 'Quick Chat';
            var stamp = new Date().toISOString();
            all.unshift({
                id: Date.now().toString(36) + Math.random().toString(36).slice(2, 7),
                title: title,
                createdAt: history[0].ts || stamp,
                updatedAt: stamp,
                messages: history.map(function (m) { return { role: m.role, content: m.content, ts: m.ts || stamp }; })
            });
            localStorage.setItem(FULL_KEY, JSON.stringify(all));
            history = [];
            save();
        } catch (e) { /* storage unavailable: just navigate */ }
    }

    // ── events ──
    fab.addEventListener('click', toggle);
    document.getElementById('ebfClose').addEventListener('click', close);
    document.getElementById('ebfClear').addEventListener('click', function () {
        if (busy) return;
        history = [];
        save();
        render();
        input.focus();
    });
    document.getElementById('ebfExpand').addEventListener('click', handOff);
    send.addEventListener('click', function () { ask(input.value); });
    input.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); ask(input.value); }
    });
    quick.addEventListener('click', function (e) {
        var chip = e.target.closest('.ebf-chip');
        if (chip) ask(chip.getAttribute('data-prompt'));
    });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && win.classList.contains('open')) close();
    });

    // ── boot ──
    load();
    render();
    var seen = false;
    try { seen = sessionStorage.getItem(SEEN_KEY) === '1'; } catch (e) { }
    if (!seen) setTimeout(function () { if (!win.classList.contains('open')) badge.classList.add('show'); }, 2000);
})();
</script>
