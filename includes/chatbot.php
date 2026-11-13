<?php
// ============================================================
//  Arandia College eLMS — EduBot AI Chatbot Include
//  File: includes/chatbot.php
//  HISTORY: Dropdown overlay from the header History button.
//           Fits the existing single-column panel layout.
// ============================================================

if (!isset($role))
    $role = $_SESSION['role'] ?? 'Student';
if (!isset($user_id))
    $user_id = (int) ($_SESSION['user_id'] ?? 0);

$botColor = $role === 'Teacher' ? '#003087' : (in_array($role, ['Admin','Head Admin'], true) ? '#7b2cbf' : '#003087');
$botColorLt = $role === 'Teacher' ? '#e6fff4' : (in_array($role, ['Admin','Head Admin'], true) ? '#f5eeff' : '#e8f0ff');
$botName = 'EduBot';
$storageKey = "edubot_history_{$user_id}_{$role}";
// Set $chatbotShowFab = false before including to hide the built-in floating button
// (used on the dedicated EduBot AI page, where the full chat is already shown).
$chatbotShowFab = $chatbotShowFab ?? true;
?>
<style>
    /* ── FAB ──────────────────────────────────────────────────── */
    .chat-fab {
        position: fixed;
        bottom: 1.75rem;
        right: 1.75rem;
        width: 56px;
        height: 56px;
        border-radius: 50%;
        background: <?= $botColor ?>;
        color: white;
        border: none;
        cursor: pointer;
        font-size: 1.5rem;
        box-shadow: 0 4px 20px rgba(0, 0, 0, .25);
        z-index: 800;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: transform .2s, box-shadow .2s
    }

    .chat-fab:hover {
        transform: scale(1.1);
        box-shadow: 0 6px 28px rgba(0, 0, 0, .3)
    }

    .chat-fab-badge {
        position: absolute;
        top: -3px;
        right: -3px;
        background: #e74c3c;
        color: white;
        font-size: .6rem;
        font-weight: 800;
        width: 18px;
        height: 18px;
        border-radius: 50%;
        display: none;
        align-items: center;
        justify-content: center;
        border: 2px solid white
    }

    .chat-fab-badge.show {
        display: flex
    }

    /* ── FLOATING WINDOW ─────────────────────────────────────── */
    .chat-window {
        position: fixed;
        bottom: 5.5rem;
        right: 1.75rem;
        width: 370px;
        max-height: 520px;
        background: var(--ui-surface, white);
        border-radius: 20px;
        box-shadow: 0 12px 40px rgba(0, 0, 0, .18);
        z-index: 800;
        display: none;
        flex-direction: column;
        overflow: hidden
    }

    .chat-window.open {
        display: flex
    }

    .chat-win-head {
        background: <?= $botColor ?>;
        color: white;
        padding: 1rem 1.25rem;
        display: flex;
        align-items: center;
        gap: .75rem
    }

    .chat-bot-avatar {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: rgba(255, 255, 255, .25);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        flex-shrink: 0
    }

    .chat-bot-info strong {
        display: block;
        font-family: 'Nunito', sans-serif;
        font-size: .92rem;
        font-weight: 900
    }

    .chat-bot-info span {
        font-size: .7rem;
        opacity: .8
    }

    .chat-win-close {
        margin-left: auto;
        background: none;
        border: none;
        color: white;
        font-size: 1.2rem;
        cursor: pointer;
        opacity: .8
    }

    .chat-win-close:hover {
        opacity: 1
    }

    .chat-expand-btn {
        background: none;
        border: none;
        color: white;
        font-size: 1rem;
        cursor: pointer;
        opacity: .8;
        margin-right: .25rem
    }

    .chat-expand-btn:hover {
        opacity: 1
    }

    .chat-messages {
        flex: 1;
        overflow-y: auto;
        padding: 1rem;
        display: flex;
        flex-direction: column;
        gap: .65rem;
        background: var(--ui-surface-2, #f8f9fc)
    }

    /* ── MESSAGES ────────────────────────────────────────────── */
    .chat-msg {
        max-width: 82%;
        padding: .65rem .9rem;
        border-radius: 14px;
        font-size: .82rem;
        line-height: 1.55;
        word-break: break-word
    }

    .chat-msg.bot {
        background: var(--ui-surface, white);
        color: var(--ui-text, #1a1a2e);
        border-radius: 4px 14px 14px 14px;
        box-shadow: 0 1px 4px rgba(0, 0, 0, .08);
        align-self: flex-start
    }

    .chat-msg.user {
        background: <?= $botColor ?>;
        color: white;
        border-radius: 14px 4px 14px 14px;
        align-self: flex-end
    }

    .chat-msg.typing {
        background: var(--ui-surface, white);
        color: var(--ui-text-3, #aaa);
        font-style: italic;
        align-self: flex-start;
        box-shadow: 0 1px 4px rgba(0, 0, 0, .08)
    }

    .chat-msg-time {
        font-size: .62rem;
        opacity: .55;
        margin-top: .2rem;
        text-align: right
    }

    /* ── DATE DIVIDER ────────────────────────────────────────── */
    .chat-date-divider {
        text-align: center;
        font-size: .68rem;
        color: var(--ui-text-3, #aaa);
        font-weight: 600;
        margin: .4rem 0;
        display: flex;
        align-items: center;
        gap: .5rem
    }

    .chat-date-divider::before,
    .chat-date-divider::after {
        content: '';
        flex: 1;
        height: 1px;
        background: #e8eaf0
    }

    /* ── QUICK PROMPTS ───────────────────────────────────────── */
    .chat-quick {
        padding: .6rem 1rem;
        display: flex;
        gap: .4rem;
        flex-wrap: wrap;
        border-top: 1px solid var(--ui-border-soft, #f0f2f5);
        background: var(--ui-surface, white)
    }

    .chat-quick-btn {
        font-size: .72rem;
        font-weight: 600;
        padding: .3rem .7rem;
        border-radius: 100px;
        border: 1.5px solid
            <?= $botColor ?>
        ;
        color: <?= $botColor ?>;
        background: <?= $botColorLt ?>;
        cursor: pointer;
        transition: background-color .2s, border-color .2s, color .2s, box-shadow .2s, opacity .2s, transform .2s;
        white-space: nowrap
    }

    .chat-quick-btn:hover {
        background: <?= $botColor ?>;
        color: white
    }

    .chat-input-wrap {
        padding: .75rem 1rem;
        border-top: 1px solid var(--ui-border-soft, #f0f2f5);
        display: flex;
        gap: .5rem;
        background: var(--ui-surface, white)
    }

    .chat-input {
        flex: 1;
        padding: .55rem .85rem;
        border: 1.5px solid var(--ui-border, #e0e4ee);
        border-radius: 100px;
        font-size: .83rem;
        outline: none;
        font-family: 'Open Sans', sans-serif;
        transition: border-color .2s
    }

    .chat-input:focus {
        border-color: <?= $botColor ?>
    }

    .chat-send-btn {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: <?= $botColor ?>;
        color: white;
        border: none;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
        flex-shrink: 0
    }

    .chat-send-btn:hover {
        opacity: .85
    }

    .chat-send-btn:disabled {
        opacity: .4;
        cursor: not-allowed
    }

    /* ── FULL PANEL ───────────────────────────────────────────── */
    .chat-panel-wrap {
        display: flex;
        flex-direction: column;
        height: calc(100vh - 220px);
        min-height: 400px;
        background: var(--ui-surface, white);
        border-radius: 16px;
        box-shadow: 0 2px 16px rgba(0, 0, 0, .07);
        overflow: visible;
        position: relative
    }

    /* ── PANEL HEADER ────────────────────────────────────────── */
    .chat-panel-head {
        background: <?= $botColor ?>;
        color: white;
        padding: 1rem 1.5rem;
        display: flex;
        align-items: center;
        gap: .6rem;
        border-radius: 16px 16px 0 0;
        flex-shrink: 0;
        position: relative;
        z-index: 10;
        flex-wrap: wrap
    }

    .chat-panel-head strong {
        font-family: 'Nunito', sans-serif;
        font-size: 1rem;
        font-weight: 900
    }

    .chat-panel-head span {
        font-size: .75rem;
        opacity: .8
    }

    .chat-hist-btn {
        background: rgba(255, 255, 255, .15);
        border: 1.5px solid rgba(255, 255, 255, .4);
        color: white;
        font-size: .73rem;
        font-weight: 700;
        padding: .3rem .85rem;
        border-radius: 100px;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: .35rem;
        transition: background-color .2s, border-color .2s, color .2s, box-shadow .2s, opacity .2s, transform .2s
    }

    .chat-hist-btn:hover,
    .chat-hist-btn.active {
        background: rgba(255, 255, 255, .3)
    }

    .chat-hist-count {
        background: rgba(255, 255, 255, .3);
        border-radius: 100px;
        padding: .05rem .45rem;
        font-size: .65rem;
        font-weight: 800;
        min-width: 16px;
        text-align: center
    }

    .chat-new-btn {
        background: rgba(255, 255, 255, .15);
        border: 1.5px solid rgba(255, 255, 255, .4);
        color: white;
        font-size: .73rem;
        font-weight: 700;
        padding: .3rem .85rem;
        border-radius: 100px;
        cursor: pointer;
        transition: background-color .2s, border-color .2s, color .2s, box-shadow .2s, opacity .2s, transform .2s
    }

    .chat-new-btn:hover {
        background: rgba(255, 255, 255, .25)
    }

    .chat-clear-btn {
        background: none;
        border: 1.5px solid rgba(255, 255, 255, .4);
        color: white;
        font-size: .72rem;
        font-weight: 700;
        padding: .25rem .75rem;
        border-radius: 100px;
        cursor: pointer;
        margin-left: auto
    }

    .chat-clear-btn:hover {
        background: rgba(255, 255, 255, .2)
    }

    .chat-session-title {
        font-size: .8rem;
        color: rgba(255, 255, 255, .9);
        background: none;
        border: none;
        outline: none;
        font-family: 'Open Sans', sans-serif;
        cursor: text;
        border-bottom: 1px dashed rgba(255, 255, 255, .4);
        min-width: 60px;
        max-width: 180px
    }

    .chat-session-title:focus {
        border-bottom-color: white;
        color: white
    }

    /* ── HISTORY DROPDOWN ────────────────────────────────────── */
    .chat-history-dropdown {
        position: absolute;
        top: calc(100% + 6px);
        left: 0;
        width: 310px;
        max-height: 400px;
        background: var(--ui-surface, white);
        border-radius: 14px;
        box-shadow: 0 8px 32px rgba(0, 0, 0, .18);
        border: 1px solid var(--ui-border, #e8eaf0);
        display: none;
        flex-direction: column;
        z-index: 500;
        overflow: hidden
    }

    .chat-history-dropdown.open {
        display: flex;
        animation: hdrop .15s ease
    }

    @keyframes hdrop {
        from {
            opacity: 0;
            transform: translateY(-6px)
        }

        to {
            opacity: 1;
            transform: translateY(0)
        }
    }

    .chat-hd-head {
        padding: .75rem 1rem;
        background: <?= $botColor ?>;
        color: white;
        font-size: .78rem;
        font-weight: 800;
        font-family: 'Nunito', sans-serif;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-shrink: 0
    }

    .chat-hd-head button {
        background: none;
        border: 1px solid rgba(255, 255, 255, .4);
        color: white;
        font-size: .65rem;
        font-weight: 700;
        padding: .2rem .6rem;
        border-radius: 100px;
        cursor: pointer;
        margin-left: .35rem
    }

    .chat-hd-head button:hover {
        background: rgba(255, 255, 255, .2)
    }

    .chat-hd-search {
        padding: .6rem .75rem;
        border-bottom: 1px solid var(--ui-border-soft, #f0f2f5);
        flex-shrink: 0
    }

    .chat-hd-search input {
        width: 100%;
        padding: .4rem .75rem;
        border: 1.5px solid var(--ui-border, #e0e4ee);
        border-radius: 100px;
        font-size: .78rem;
        outline: none;
        font-family: 'Open Sans', sans-serif;
        box-sizing: border-box
    }

    .chat-hd-search input:focus {
        border-color: <?= $botColor ?>
    }

    .chat-hd-list {
        flex: 1;
        overflow-y: auto;
        padding: .5rem;
        display: flex;
        flex-direction: column;
        gap: .2rem
    }

    .chat-hd-group-label {
        font-size: .62rem;
        font-weight: 800;
        color: var(--ui-text-3, #aaa);
        text-transform: uppercase;
        letter-spacing: .06em;
        padding: .4rem .6rem .1rem
    }

    .chat-hd-item {
        padding: .55rem .8rem;
        border-radius: 10px;
        cursor: pointer;
        border: 1.5px solid transparent;
        transition: background-color .15s, border-color .15s, color .15s, box-shadow .15s, opacity .15s, transform .15s;
        display: flex;
        align-items: flex-start;
        gap: .5rem
    }

    .chat-hd-item:hover {
        background: <?= $botColorLt ?>;
        border-color: <?= $botColor ?>
    }

    .chat-hd-item.active {
        background: <?= $botColorLt ?>;
        border-color: <?= $botColor ?>
    }

    .chat-hd-item-body {
        flex: 1;
        min-width: 0
    }

    .chat-hd-item-title {
        font-size: .78rem;
        font-weight: 700;
        color: var(--ui-text, #1a1a2e);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis
    }

    .chat-hd-item-meta {
        font-size: .65rem;
        color: var(--ui-text-3, #999);
        margin-top: .1rem
    }

    .chat-hd-item-del {
        color: #ddd;
        font-size: .8rem;
        cursor: pointer;
        padding: .1rem .25rem;
        border-radius: 5px;
        flex-shrink: 0;
        margin-top: .05rem
    }

    .chat-hd-item-del:hover {
        color: #e74c3c;
        background: #fff0f0
    }

    .chat-hd-empty {
        text-align: center;
        color: var(--ui-text-3, #bbb);
        font-size: .75rem;
        padding: 2rem 1rem;
        line-height: 1.7
    }

    /* ── MESSAGES + INPUTS ───────────────────────────────────── */
    .chat-panel-messages {
        flex: 1;
        overflow-y: auto;
        padding: 1.25rem;
        display: flex;
        flex-direction: column;
        gap: .65rem;
        background: var(--ui-surface-2, #f8f9fc)
    }

    .chat-panel-quick {
        padding: .75rem 1.25rem;
        display: flex;
        gap: .4rem;
        flex-wrap: wrap;
        border-top: 1px solid var(--ui-border-soft, #f0f2f5);
        background: var(--ui-surface, white);
        flex-shrink: 0
    }

    .chat-panel-input {
        padding: 1rem 1.25rem;
        border-top: 1px solid var(--ui-border-soft, #f0f2f5);
        display: flex;
        gap: .5rem;
        background: var(--ui-surface, white);
        flex-shrink: 0;
        border-radius: 0 0 16px 16px
    }

    .chat-panel-input textarea {
        flex: 1;
        padding: .65rem .9rem;
        border: 1.5px solid var(--ui-border, #e0e4ee);
        border-radius: 12px;
        font-size: .83rem;
        outline: none;
        font-family: 'Open Sans', sans-serif;
        resize: none;
        height: 70px;
        transition: border-color .2s;
        line-height: 1.4
    }

    .chat-panel-input textarea:focus {
        border-color: <?= $botColor ?>
    }

    .chat-panel-send {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        background: <?= $botColor ?>;
        color: white;
        border: none;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
        flex-shrink: 0;
        align-self: flex-end
    }

    .chat-panel-send:hover {
        opacity: .85
    }

    .chat-panel-send:disabled {
        opacity: .4;
        cursor: not-allowed
    }
</style>

<?php if ($chatbotShowFab): ?>
<!-- ── FAB ──────────────────────────────────────────────────────── -->
<button class="chat-fab" id="chatFab" onclick="toggleChatWindow()" title="Ask EduBot">
    🤖
    <span class="chat-fab-badge" id="chatBadge">1</span>
</button>

<!-- ── FLOATING WINDOW ──────────────────────────────────────────── -->
<div class="chat-window" id="chatWindow">
    <div class="chat-win-head">
        <div class="chat-bot-avatar">🤖</div>
        <div class="chat-bot-info">
            <strong><?= $botName ?></strong>
            <span>AI Study Assistant · Online</span>
        </div>
        <button class="chat-expand-btn" onclick="goToFullChat()" title="Open full chat">⛶</button>
        <button class="chat-win-close" onclick="toggleChatWindow()">✕</button>
    </div>
    <div class="chat-messages" id="chatMessages">
        <div class="chat-msg bot">
            Hi <?= htmlspecialchars($_SESSION['first_name'] ?? 'there') ?>! 👋 I'm <strong><?= $botName ?></strong>,
            your AI study assistant. How can I help you today?
            <div class="chat-msg-time"><?= date('h:i A') ?></div>
        </div>
    </div>
    <div class="chat-quick" id="chatQuickWin">
        <?php
        $quickPrompts = $role === 'Student'
            ? ['Explain a topic', 'Help with Math', 'Science question', 'What is ABM?']
            : ($role === 'Teacher'
                ? ['Pending submissions', 'How to create a quiz', 'Attendance summary', 'My subjects']
                : ['System help', 'User management', 'Report issue', 'Best practices']);
        foreach ($quickPrompts as $qp): ?>
            <button class="chat-quick-btn"
                onclick="sendQuick('<?= htmlspecialchars(addslashes($qp)) ?>')"><?= htmlspecialchars($qp) ?></button>
        <?php endforeach; ?>
    </div>
    <div class="chat-input-wrap">
        <input class="chat-input" type="text" id="chatInputWin" placeholder="Ask anything..." maxlength="1000"
            onkeydown="if(event.key==='Enter')sendChatWin()">
        <button class="chat-send-btn" id="chatSendWin" onclick="sendChatWin()">➤</button>
    </div>
</div>

<?php endif; ?>

<!-- ── FULL CHAT PANEL — redesigned look (overrides the base styles above) ── -->
<style>
    /* Page fit: use the whole screen on the EduBot page */
    body.edubot-page .main-content { padding: .75rem 1rem .75rem; }

    #panel-chatbot .chat-panel-wrap {
        height: calc(100vh - 90px);
        min-height: 440px;
        background: var(--ui-surface, #fff);
        border-radius: 18px;
        border: 1px solid #e6eaf2;
        box-shadow: 0 10px 40px rgba(15, 23, 42, .08);
        overflow: visible;
        font-family: 'Open Sans', sans-serif;
    }

    /* ── Header ─────────────────────────────────────────────── */
    #panel-chatbot .chat-panel-head {
        background: linear-gradient(120deg, #001a4d 0%, <?= $botColor ?> 55%, #0a4fd1 100%);
        padding: .8rem 1.25rem;
        gap: 1rem;
        flex-wrap: nowrap;
        border-radius: 18px 18px 0 0;
        min-height: 60px;
        overflow: visible;
    }
    #panel-chatbot .chat-panel-head::after {
        content: ''; position: absolute; inset: 0; border-radius: inherit; pointer-events: none;
        background: radial-gradient(circle at 92% -40%, rgba(255,255,255,.18), transparent 45%);
    }
    #panel-chatbot .ebx-brand { display: flex; align-items: center; gap: .75rem; flex-shrink: 0; position: relative; z-index: 1; }
    #panel-chatbot .ebx-avatar {
        width: 42px; height: 42px; border-radius: 12px; position: relative;
        background: rgba(255,255,255,.14); border: 1px solid rgba(255,255,255,.22);
        display: flex; align-items: center; justify-content: center;
    }
    #panel-chatbot .ebx-avatar svg { width: 24px; height: 24px; }
    #panel-chatbot .ebx-online {
        position: absolute; right: -3px; bottom: -3px; width: 12px; height: 12px; border-radius: 50%;
        background: #34d399; border: 2px solid #002b7a;
    }
    #panel-chatbot .ebx-brand-text strong {
        display: block; font-family: 'Nunito', sans-serif; font-size: 1.05rem; font-weight: 900; line-height: 1.2;
    }
    #panel-chatbot .ebx-brand-text strong em { font-style: normal; font-weight: 700; opacity: .75; }
    #panel-chatbot .ebx-brand-text span { font-size: .72rem; opacity: .75; }

    #panel-chatbot .ebx-spacer { flex: 1; }
    #panel-chatbot .ebx-title-wrap {
        flex: 1; min-width: 0; display: flex; align-items: center; justify-content: center; gap: .4rem;
        position: relative; z-index: 1; color: rgba(255,255,255,.6);
    }
    #panel-chatbot .ebx-title-wrap svg { width: 14px; height: 14px; flex-shrink: 0; }
    #panel-chatbot .chat-session-title {
        width: 100%; max-width: 340px; min-width: 0;
        font-family: 'Nunito', sans-serif; font-size: .9rem; font-weight: 700; color: #fff;
        background: transparent; border: 1px solid transparent; border-radius: 8px;
        padding: .35rem .6rem; text-overflow: ellipsis; transition: background .15s, border-color .15s;
    }
    #panel-chatbot .chat-session-title:hover { background: rgba(255,255,255,.08); }
    #panel-chatbot .chat-session-title:focus { background: rgba(255,255,255,.12); border-color: rgba(255,255,255,.35); }

    #panel-chatbot .ebx-actions { display: flex; align-items: center; gap: .45rem; flex-shrink: 0; position: relative; z-index: 1; }
    #panel-chatbot .chat-hist-btn,
    #panel-chatbot .chat-clear-btn,
    #panel-chatbot .chat-new-btn {
        display: inline-flex; align-items: center; gap: .4rem; margin: 0;
        height: 36px; padding: 0 .85rem; border-radius: 10px;
        font-family: 'Nunito', sans-serif; font-size: .8rem; font-weight: 800;
        cursor: pointer; transition: background .15s, transform .15s, box-shadow .15s;
    }
    #panel-chatbot .chat-hist-btn svg,
    #panel-chatbot .chat-clear-btn svg,
    #panel-chatbot .chat-new-btn svg { width: 16px; height: 16px; flex-shrink: 0; }
    #panel-chatbot .chat-hist-btn,
    #panel-chatbot .chat-clear-btn {
        background: rgba(255,255,255,.1); border: 1px solid rgba(255,255,255,.22); color: #fff;
    }
    #panel-chatbot .chat-hist-btn:hover, #panel-chatbot .chat-hist-btn.active,
    #panel-chatbot .chat-clear-btn:hover { background: rgba(255,255,255,.2); }
    #panel-chatbot .chat-hist-count { background: #FFD700; color: #001a4d; padding: .05rem .45rem; font-size: .68rem; }
    #panel-chatbot .chat-new-btn {
        background: #fff; color: <?= $botColor ?>; border: 1px solid #fff;
        box-shadow: 0 4px 14px rgba(0,0,0,.18);
    }
    #panel-chatbot .chat-new-btn:hover { transform: translateY(-1px); box-shadow: 0 6px 18px rgba(0,0,0,.22); }

    /* History dropdown: align under the action buttons */
    #panel-chatbot .chat-history-dropdown {
        left: auto; right: 1rem; top: calc(100% + 8px);
        width: 340px; max-width: calc(100vw - 2rem); max-height: min(460px, 70vh);
        border-radius: 14px; border-color: #e6eaf2; box-shadow: 0 18px 50px rgba(15,23,42,.2);
    }
    #panel-chatbot .chat-hd-head { background: var(--ui-surface, #fff); color: var(--ui-text, #0f172a); border-bottom: 1px solid #eef1f6; font-size: .85rem; padding: .85rem 1rem; }
    #panel-chatbot .chat-hd-head button { border-color: var(--ui-border, #e2e8f0); color: var(--ui-text-2, #475569); border-radius: 8px; padding: .25rem .6rem; }
    #panel-chatbot .chat-hd-head button:hover { background: var(--ui-surface-3, #f1f5f9); }
    #panel-chatbot .chat-hd-search input { border-radius: 10px; padding: .5rem .8rem; background: var(--ui-surface-2, #f8fafc); }
    #panel-chatbot .chat-hd-item { border-radius: 10px; border-width: 1px; }
    #panel-chatbot .chat-hd-item:hover { background: #f1f5fb; border-color: transparent; }
    #panel-chatbot .chat-hd-item.active { background: #eaf0fc; border-color: #c7d4ee; }

    /* ── Body / messages ───────────────────────────────────── */
    #panel-chatbot .ebx-body {
        flex: 1; min-height: 0; position: relative; display: flex; flex-direction: column;
        border-bottom: 1px solid #eef1f6;
        background:
            radial-gradient(circle at 1px 1px, rgba(0,48,135,.06) 1px, transparent 0) 0 0 / 22px 22px,
            linear-gradient(180deg, #f7f9fd, #f2f5fb);
    }
    #panel-chatbot .chat-panel-messages {
        flex: 1; min-height: 0; background: transparent;
        padding: 1.25rem max(1.5rem, calc((100% - 1400px) / 2));
        gap: 1rem; scroll-behavior: smooth;
    }
    #panel-chatbot .chat-msg { font-size: .9rem; line-height: 1.65; padding: .8rem 1.05rem; max-width: min(78%, 980px); }
    #panel-chatbot .chat-msg.bot {
        position: relative; margin-left: 44px; background: var(--ui-surface, #fff); color: var(--ui-text, #0f172a);
        border: 1px solid #e8edf5; border-radius: 4px 16px 16px 16px; box-shadow: 0 2px 8px rgba(15,23,42,.04);
    }
    #panel-chatbot .chat-msg.bot::before {
        content: ''; position: absolute; left: -44px; top: 0; width: 32px; height: 32px; border-radius: 10px;
        background: <?= $botColor ?> url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='white' stroke-width='1.8' stroke-linecap='round'%3E%3Crect x='4' y='8' width='16' height='11' rx='3'/%3E%3Cpath d='M12 4v4'/%3E%3Ccircle cx='9' cy='13.5' r='1' fill='white'/%3E%3Ccircle cx='15' cy='13.5' r='1' fill='white'/%3E%3C/svg%3E") center / 18px no-repeat;
        box-shadow: 0 3px 10px rgba(0,48,135,.25);
    }
    #panel-chatbot .chat-msg.user {
        background: linear-gradient(135deg, <?= $botColor ?>, #0a4fd1); color: #fff;
        border-radius: 16px 4px 16px 16px; box-shadow: 0 4px 14px rgba(0,48,135,.22);
    }
    #panel-chatbot .chat-msg.typing { color: var(--ui-text-2, #64748b); font-style: normal; }
    #panel-chatbot .chat-msg-time { font-size: .66rem; opacity: .5; }
    .chat-msg .chat-code { background: #0f172a; color: #e2e8f0; padding: .75rem .9rem; border-radius: 10px; overflow-x: auto; font-size: .8rem; line-height: 1.5; margin: .4rem 0; white-space: pre; }
    .chat-msg .chat-inline-code { background: rgba(0, 0, 0, .06); padding: .1rem .3rem; border-radius: 4px; font-size: .85em; }
    .chat-msg .chat-h { display: inline-block; margin-top: .3rem; font-family: 'Nunito', sans-serif; }
    #panel-chatbot .chat-date-divider { font-size: .7rem; color: var(--ui-text-3, #94a3b8); }
    #panel-chatbot .chat-date-divider::before,
    #panel-chatbot .chat-date-divider::after { background: #e2e8f0; }

    /* ── Empty state ───────────────────────────────────────── */
    #panel-chatbot .ebx-empty { display: none; flex: 1; min-height: 0; overflow-y: auto; }
    #panel-chatbot .chat-panel-wrap.is-empty .ebx-empty { display: flex; }
    #panel-chatbot .chat-panel-wrap.is-empty .chat-panel-messages,
    #panel-chatbot .chat-panel-wrap.is-empty .chat-panel-quick { display: none; }
    #panel-chatbot .ebx-empty-inner { margin: auto; width: min(1400px, calc(100% - 3rem)); padding: 1.25rem 0; text-align: center; }
    #panel-chatbot .ebx-hero-icon {
        width: 72px; height: 72px; margin: 0 auto 1.1rem; border-radius: 22px; color: #fff;
        background: linear-gradient(135deg, #001a4d, <?= $botColor ?> 55%, #0a4fd1);
        display: flex; align-items: center; justify-content: center;
        box-shadow: 0 12px 30px rgba(0,48,135,.3);
    }
    #panel-chatbot .ebx-hero-icon svg { width: 32px; height: 32px; }
    #panel-chatbot .ebx-hero-icon { width: 60px; height: 60px; border-radius: 18px; margin-bottom: .85rem; }
    #panel-chatbot .ebx-hello { font-family: 'Nunito', sans-serif; font-size: 1.65rem; font-weight: 900; color: var(--ui-text, #0f172a); margin: 0 0 .3rem; }
    #panel-chatbot .ebx-sub { color: var(--ui-text-2, #64748b); font-size: .92rem; margin: 0 0 1.4rem; }
    #panel-chatbot .ebx-cards { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: .75rem; text-align: left; }
    #panel-chatbot .ebx-card {
        display: flex; align-items: flex-start; gap: .75rem; padding: .95rem 1rem;
        background: var(--ui-surface, #fff); border: 1px solid #e6eaf2; border-radius: 14px; cursor: pointer;
        font-family: inherit; text-align: left; transition: transform .15s, box-shadow .15s, border-color .15s;
    }
    #panel-chatbot .ebx-card:hover { transform: translateY(-2px); border-color: #b9c9ea; box-shadow: 0 10px 24px rgba(0,48,135,.1); }
    #panel-chatbot .ebx-card-ico {
        width: 36px; height: 36px; border-radius: 10px; flex-shrink: 0;
        background: #eaf0fc; color: <?= $botColor ?>; display: flex; align-items: center; justify-content: center;
    }
    #panel-chatbot .ebx-card-ico svg { width: 18px; height: 18px; }
    #panel-chatbot .ebx-card-text strong { display: block; font-family: 'Nunito', sans-serif; font-size: .88rem; font-weight: 800; color: var(--ui-text, #0f172a); line-height: 1.3; }
    #panel-chatbot .ebx-card-text small { display: block; font-size: .75rem; color: var(--ui-text-2, #64748b); margin-top: .2rem; }

    /* ── Quick chips (one compact scrollable row) ──────────── */
    /* Same column as the composer: chips line up with its left and right edges */
    #panel-chatbot .chat-panel-quick {
        width: min(1400px, calc(100% - 3rem)); margin: 0 auto; box-sizing: border-box;
        flex-wrap: nowrap; overflow-x: auto; gap: .45rem; padding: .75rem 0 0;
        background: transparent; border-top: none; scrollbar-width: none;
    }
    #panel-chatbot .chat-panel-quick::-webkit-scrollbar { display: none; }
    /* chips stretch to fill the row so both ends line up with the message box */
    #panel-chatbot .chat-quick-btn {
        white-space: nowrap; flex: 1 0 auto; text-align: center; border-radius: 999px; padding: .35rem .85rem;
        font-size: .74rem; font-weight: 700; background: #f1f5fb; border: 1px solid #dbe3f1; color: <?= $botColor ?>;
    }
    #panel-chatbot .chat-quick-btn:hover { background: <?= $botColor ?>; border-color: <?= $botColor ?>; color: #fff; }

    /* ── Composer ──────────────────────────────────────────── */
    #panel-chatbot .chat-panel-input {
        flex-direction: column; gap: .4rem; background: var(--ui-surface, #fff); border-top: none;
        padding: .65rem 0 .55rem; width: min(1400px, calc(100% - 3rem)); margin: 0 auto; box-sizing: border-box;
    }
    #panel-chatbot .ebx-composer {
        display: flex; align-items: flex-end; gap: .5rem; padding: .45rem .45rem .45rem 1rem;
        background: var(--ui-surface-2, #f8fafc); border: 1.5px solid var(--ui-border, #e2e8f0); border-radius: 16px;
        transition: border-color .15s, box-shadow .15s, background .15s;
    }
    #panel-chatbot .ebx-composer:focus-within { background: #fff; border-color: <?= $botColor ?>; box-shadow: 0 0 0 4px rgba(0,48,135,.08); }
    #panel-chatbot .chat-panel-input textarea {
        flex: 1; height: auto; min-height: 24px; max-height: 180px; padding: .45rem 0;
        border: none; border-radius: 0; background: transparent; font-size: .92rem; line-height: 1.5; color: var(--ui-text, #0f172a);
    }
    #panel-chatbot .chat-panel-input textarea:focus { border: none; }
    #panel-chatbot .chat-panel-send {
        width: 42px; height: 42px; border-radius: 12px; align-self: flex-end;
        background: linear-gradient(135deg, <?= $botColor ?>, #0a4fd1); box-shadow: 0 4px 12px rgba(0,48,135,.25);
        transition: transform .15s, opacity .15s;
    }
    #panel-chatbot .chat-panel-send svg { width: 18px; height: 18px; }
    #panel-chatbot .chat-panel-send:hover { opacity: 1; transform: translateY(-1px); }
    #panel-chatbot .ebx-hint { font-size: .68rem; color: var(--ui-text-3, #94a3b8); text-align: center; }
    #panel-chatbot .ebx-hint kbd {
        font-family: inherit; font-size: .64rem; background: #f1f5f9; border: 1px solid #e2e8f0;
        border-bottom-width: 2px; border-radius: 4px; padding: 0 .3rem; color: #64748b;
    }

    /* ── Dark mode ─────────────────────────────────────────── */
    html[data-theme="dark"] #panel-chatbot .chat-panel-wrap { background: #1e293b; border-color: #334155; }
    html[data-theme="dark"] #panel-chatbot .ebx-body {
        background: radial-gradient(circle at 1px 1px, rgba(255,255,255,.05) 1px, transparent 0) 0 0 / 22px 22px, #0f172a;
    }
    html[data-theme="dark"] #panel-chatbot .chat-msg.bot { background: #1e293b; border-color: #334155; color: #e2e8f0; }
    html[data-theme="dark"] #panel-chatbot .ebx-hello { color: #f1f5f9; }
    html[data-theme="dark"] #panel-chatbot .ebx-card { background: #1e293b; border-color: #334155; }
    html[data-theme="dark"] #panel-chatbot .ebx-card-text strong { color: #e2e8f0; }
    html[data-theme="dark"] #panel-chatbot .ebx-card-ico { background: #1e3a6e; color: #bcd0ff; }
    html[data-theme="dark"] #panel-chatbot .chat-panel-input { background: transparent; }
    html[data-theme="dark"] #panel-chatbot .ebx-body { border-bottom-color: #334155; }
    html[data-theme="dark"] #panel-chatbot .chat-quick-btn { background: #1e3a6e; border-color: #1e3a6e; color: #dbe6ff; }
    html[data-theme="dark"] #panel-chatbot .ebx-composer { background: #0f172a; border-color: #334155; }
    html[data-theme="dark"] #panel-chatbot .chat-panel-input textarea { color: #e2e8f0; }
    html[data-theme="dark"] #panel-chatbot .ebx-hint kbd { background: #0f172a; border-color: #334155; color: #94a3b8; }

    /* ── Responsive ────────────────────────────────────────── */
    @media (max-width: 1000px) {
        #panel-chatbot .ebx-brand-text span { display: none; }
        #panel-chatbot .ebx-brand-text strong em { display: none; }
    }
    @media (max-width: 760px) {
        body.edubot-page .main-content { padding: .6rem; }
        #panel-chatbot .chat-panel-head { flex-wrap: wrap; gap: .6rem; padding: .7rem .85rem; }
        #panel-chatbot .ebx-title-wrap { order: 3; flex-basis: 100%; justify-content: flex-start; }
        #panel-chatbot .chat-session-title { max-width: none; }
        #panel-chatbot .ebx-actions { margin-left: auto; }
        #panel-chatbot .ebx-lbl { display: none; }
        #panel-chatbot .chat-hist-btn, #panel-chatbot .chat-clear-btn, #panel-chatbot .chat-new-btn { padding: 0 .6rem; }
        #panel-chatbot .chat-msg { max-width: 88%; font-size: .86rem; }
        #panel-chatbot .chat-msg.bot { margin-left: 38px; }
        #panel-chatbot .chat-msg.bot::before { left: -38px; width: 28px; height: 28px; background-size: 16px; }
        #panel-chatbot .ebx-hello { font-size: 1.35rem; }
        #panel-chatbot .ebx-cards { grid-template-columns: minmax(0, 1fr); }
        #panel-chatbot .ebx-hint { display: none; }
        #panel-chatbot .chat-panel-quick,
        #panel-chatbot .chat-panel-input { width: calc(100% - 1.5rem); }
        #panel-chatbot .chat-panel-input textarea { font-size: 16px; }
    }
</style>

<!-- ── FULL CHAT PANEL (redesigned) ─────────────────────────────── -->
<?php
$fullPrompts = $role === 'Student'
    ? ['Explain photosynthesis', 'What is a polynomial?', 'Help me with Statistics', 'What strand should I choose?', 'Summarize Philippine history']
    : ($role === 'Teacher'
        ? ['Show my pending submissions', 'How do I create a quiz?', 'Which students have the most absences?', 'Summarize my subjects and sections', 'How do I post an announcement?']
        : ['Best practices for school admin?', 'How to manage student records?', 'Tips for data privacy', 'Create an announcement template', 'School year planning tips']);

// Starter cards shown on an empty chat: [title, short description]
$cardSets = [
    'Teacher' => [
        ['Show my pending submissions', 'Assignments still waiting to be graded'],
        ['How do I create a quiz?', 'Step-by-step for the Quizzes page'],
        ['Which students have the most absences?', 'Based on your attendance records'],
        ['Summarize my subjects and sections', 'Your current teaching load'],
        ['How do I post an announcement?', 'Send to one or all of your subjects'],
    ],
    'Student' => [
        ['Explain photosynthesis', 'Step-by-step, in simple terms'],
        ['What is a polynomial?', 'Definition with worked examples'],
        ['Help me with Statistics', 'Mean, median, mode and more'],
        ['What strand should I choose?', 'Compare SHS strands and careers'],
        ['Summarize Philippine history', 'Key eras and events'],
    ],
];
$promptCards = $cardSets[$role] ?? array_map(fn($p) => [$p, ''], $fullPrompts);
$cardIcons = [
    '<path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>',
    '<path d="M9 6h11M9 12h11M9 18h11"/><circle cx="4.5" cy="6" r="1"/><circle cx="4.5" cy="12" r="1"/><circle cx="4.5" cy="18" r="1"/>',
    '<circle cx="9" cy="8" r="3.5"/><path d="M2.5 20a6.5 6.5 0 0 1 13 0"/><path d="M16 4.5a3.5 3.5 0 0 1 0 7M18.5 20a6.5 6.5 0 0 0-3-5.5"/>',
    '<path d="M2 4h7a3 3 0 0 1 3 3v13a2 2 0 0 0-2-2H2z"/><path d="M22 4h-7a3 3 0 0 0-3 3v13a2 2 0 0 1 2-2h8z"/>',
    '<path d="M3 11v2a1 1 0 0 0 1 1h2l5 4V6L6 10H4a1 1 0 0 0-1 1z"/><path d="M15 9a3 3 0 0 1 0 6M18 6a7 7 0 0 1 0 12"/>',
];
?>
<div id="panel-chatbot" class="section-panel active">
    <div class="chat-panel-wrap is-empty" id="chatPanelWrap">

        <!-- HEADER -->
        <div class="chat-panel-head">
            <div class="ebx-brand">
                <div class="ebx-avatar">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <rect x="4" y="8" width="16" height="11" rx="3"/><path d="M12 4v4"/><circle cx="12" cy="3.5" r="1"/>
                        <circle cx="9" cy="13.5" r="1.2" fill="currentColor" stroke="none"/><circle cx="15" cy="13.5" r="1.2" fill="currentColor" stroke="none"/>
                    </svg>
                    <span class="ebx-online"></span>
                </div>
                <div class="ebx-brand-text">
                    <strong><?= $botName ?> <em>AI Assistant</em></strong>
                </div>
            </div>

            <div class="ebx-spacer"></div>

            <div class="ebx-actions">
                <!-- History button -->
                <button type="button" class="chat-hist-btn" id="histToggleBtn" onclick="toggleHistoryDropdown()">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 12a9 9 0 1 0 3-6.7L3 8"/><path d="M3 3v5h5"/><path d="M12 7v5l3 2"/></svg>
                    <span class="ebx-lbl">History</span> <span class="chat-hist-count" id="histCount">0</span>
                </button>

                <!-- New Chat -->
                <button type="button" class="chat-new-btn" onclick="startNewSession()">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
                    <span class="ebx-lbl">New Chat</span>
                </button>
            </div>

            <!-- HISTORY DROPDOWN (child of header so it inherits z-index stacking) -->
            <div class="chat-history-dropdown" id="historyDropdown">
                <div class="chat-hd-head">
                    Chat History
                    <div>
                        <button type="button" onclick="clearAllHistory()">Clear All</button>
                        <button type="button" onclick="closeHistoryDropdown()" aria-label="Close">✕</button>
                    </div>
                </div>
                <div class="chat-hd-search">
                    <input type="text" id="histSearchInput" placeholder="Search chats…"
                        oninput="renderHistoryList(this.value)">
                </div>
                <div class="chat-hd-list" id="historyList"></div>
            </div>
        </div>

        <!-- BODY -->
        <div class="ebx-body">
            <!-- Empty state: shown until the current chat has messages -->
            <div class="ebx-empty" id="chatEmptyState">
                <div class="ebx-empty-inner">
                    <div class="ebx-hero-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <rect x="4" y="8" width="16" height="11" rx="3"/><path d="M12 4v4"/><circle cx="12" cy="3.5" r="1"/>
                            <circle cx="9" cy="13.5" r="1.2" fill="currentColor" stroke="none"/><circle cx="15" cy="13.5" r="1.2" fill="currentColor" stroke="none"/>
                            <path d="M2 12.5v2.5M22 12.5v2.5"/>
                        </svg>
                    </div>
                    <h2 class="ebx-hello"><span id="ebxGreeting">Hello</span>, <?= htmlspecialchars($_SESSION['first_name'] ?? 'there') ?>!</h2>
                    <p class="ebx-sub">How can I help you today? Pick a starter below or type your own question.</p>
                    <div class="ebx-cards">
                        <?php foreach ($promptCards as $i => [$cTitle, $cDesc]): ?>
                            <button type="button" class="ebx-card"
                                onclick="sendFullQuick('<?= htmlspecialchars(addslashes($cTitle)) ?>')">
                                <span class="ebx-card-ico">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><?= $cardIcons[$i % count($cardIcons)] ?></svg>
                                </span>
                                <span class="ebx-card-text">
                                    <strong><?= htmlspecialchars($cTitle) ?></strong>
                                    <?php if ($cDesc !== ''): ?><small><?= htmlspecialchars($cDesc) ?></small><?php endif; ?>
                                </span>
                            </button>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- MESSAGES -->
            <div class="chat-panel-messages" id="chatPanelMessages"></div>
        </div>

        <!-- QUICK PROMPTS (compact, shown once a chat is going) -->
        <div class="chat-panel-quick">
            <?php foreach ($fullPrompts as $fp): ?>
                <button type="button" class="chat-quick-btn"
                    onclick="sendFullQuick('<?= htmlspecialchars(addslashes($fp)) ?>')"><?= htmlspecialchars($fp) ?></button>
            <?php endforeach; ?>
        </div>

        <!-- INPUT -->
        <div class="chat-panel-input">
            <div class="ebx-composer">
                <textarea id="chatPanelInput" rows="1" maxlength="1000"
                    placeholder="Message EduBot…"
                    onkeydown="handlePanelKey(event)" oninput="ebxAutoGrow(this)"></textarea>
                <button type="button" class="chat-panel-send" id="chatPanelSend" onclick="sendFullChat()" aria-label="Send message">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 2L11 13M22 2l-7 20-4-9-9-4 20-7z"/></svg>
                </button>
            </div>
            <div class="ebx-hint"><kbd>Enter</kbd> to send · <kbd>Shift</kbd> + <kbd>Enter</kbd> for a new line · EduBot can make mistakes, so double-check important info.</div>
        </div>
    </div>
</div>

<script>
    // ════════════════════════════════════════════════════════════════
    //  EduBot — persistent history via localStorage + dropdown UI
    // ════════════════════════════════════════════════════════════════

    const STORAGE_KEY = <?= json_encode($storageKey) ?>;
    const BOT_NAME = <?= json_encode($botName) ?>;
    const USER_NAME = <?= json_encode(htmlspecialchars($_SESSION['first_name'] ?? 'there')) ?>;

    let winHistory = [];
    let winBusy = false;
    let fullBusy = false;
    let allSessions = [];
    let currentSession = null;

    // ── Persistence ──────────────────────────────────────────────────
    function loadSessions() {
        try { allSessions = JSON.parse(localStorage.getItem(STORAGE_KEY) || '[]'); }
        catch (e) { allSessions = []; }
    }
    function saveSessions() {
        try { localStorage.setItem(STORAGE_KEY, JSON.stringify(allSessions)); }
        catch (e) { console.warn('EduBot localStorage error', e); }
    }
    function makeId() {
        return Date.now().toString(36) + Math.random().toString(36).slice(2, 7);
    }

    // ── Session CRUD ─────────────────────────────────────────────────
    function createSession(title) {
        const s = {
            id: makeId(), title: title || 'New Chat',
            createdAt: new Date().toISOString(),
            updatedAt: new Date().toISOString(),
            messages: []
        };
        allSessions.unshift(s);
        saveSessions();
        return s;
    }
    function startNewSession() {
        closeHistoryDropdown();
        currentSession = createSession('New Chat');
        updateHistCount();
        renderHistoryList();
        renderFullMessages();
        setSessionTitleField('New Chat');
    }
    function switchSession(id) {
        const s = allSessions.find(x => x.id === id);
        if (!s) return;
        currentSession = s;
        closeHistoryDropdown();
        renderHistoryList();
        renderFullMessages();
        setSessionTitleField(currentSession.title);
    }
    function deleteSession(id, e) {
        e && e.stopPropagation();
        allSessions = allSessions.filter(x => x.id !== id);
        saveSessions();
        if (currentSession && currentSession.id === id)
            currentSession = allSessions[0] || createSession('New Chat');
        updateHistCount();
        renderHistoryList();
        renderFullMessages();
        setSessionTitleField(currentSession.title);
    }
    function clearCurrentSession() {
        if (!currentSession) return;
        currentSession.messages = [];
        currentSession.updatedAt = new Date().toISOString();
        saveSessions();
        renderFullMessages();
    }
    function clearAllHistory() {
        if (!confirm('Delete ALL chat history? This cannot be undone.')) return;
        allSessions = [];
        saveSessions();
        currentSession = createSession('New Chat');
        updateHistCount();
        renderHistoryList();
        renderFullMessages();
        setSessionTitleField('New Chat');
        closeHistoryDropdown();
    }
    function renameCurrentSession(title) {
        if (!currentSession || !title.trim()) return;
        currentSession.title = title.trim();
        currentSession.updatedAt = new Date().toISOString();
        saveSessions();
        renderHistoryList();
    }
    function autoTitleSession(text) {
        if (!currentSession || currentSession.title !== 'New Chat') return;
        currentSession.title = text.length > 38 ? text.slice(0, 38) + '…' : text;
        setSessionTitleField(currentSession.title);
        saveSessions();
        renderHistoryList();
    }

    // ── Dropdown ─────────────────────────────────────────────────────
    function toggleHistoryDropdown() {
        const dd = document.getElementById('historyDropdown');
        dd.classList.contains('open') ? closeHistoryDropdown() : openHistoryDropdown();
    }
    function openHistoryDropdown() {
        const dd = document.getElementById('historyDropdown');
        dd.classList.add('open');
        document.getElementById('histToggleBtn').classList.add('active');
        document.getElementById('histSearchInput').value = '';
        renderHistoryList();
        setTimeout(() => document.addEventListener('click', outsideHistClose), 10);
    }
    function closeHistoryDropdown() {
        document.getElementById('historyDropdown').classList.remove('open');
        document.getElementById('histToggleBtn').classList.remove('active');
        document.removeEventListener('click', outsideHistClose);
    }
    function outsideHistClose(e) {
        const dd = document.getElementById('historyDropdown');
        const btn = document.getElementById('histToggleBtn');
        if (!dd.contains(e.target) && !btn.contains(e.target)) closeHistoryDropdown();
    }
    function updateHistCount() {
        document.getElementById('histCount').textContent = allSessions.length;
    }

    // ── Render history list ───────────────────────────────────────────
    function renderHistoryList(filter) {
        const list = document.getElementById('historyList');
        list.innerHTML = '';

        const q = (filter || '').toLowerCase().trim();
        const sessions = q
            ? allSessions.filter(s =>
                s.title.toLowerCase().includes(q) ||
                s.messages.some(m => m.content.toLowerCase().includes(q)))
            : allSessions;

        if (sessions.length === 0) {
            const e = document.createElement('div');
            e.className = 'chat-hd-empty';
            e.innerHTML = q
                ? `No chats matching "<strong>${escHtml(q)}</strong>"`
                : 'No saved chats yet.<br>Start a conversation!';
            list.appendChild(e);
            return;
        }

        // Group by relative day
        const groups = {};
        sessions.forEach(s => {
            const d = relativeDay(s.updatedAt);
            (groups[d] = groups[d] || []).push(s);
        });

        Object.entries(groups).forEach(([day, items]) => {
            const lbl = document.createElement('div');
            lbl.className = 'chat-hd-group-label';
            lbl.textContent = day;
            list.appendChild(lbl);

            items.forEach(s => {
                const row = document.createElement('div');
                row.className = 'chat-hd-item' +
                    (currentSession && s.id === currentSession.id ? ' active' : '');
                row.innerHTML =
                    `<div class="chat-hd-item-body">
                    <div class="chat-hd-item-title">${escHtml(s.title)}</div>
                    <div class="chat-hd-item-meta">
                        ${fmtTime(s.updatedAt)} · ${s.messages.length} msg${s.messages.length !== 1 ? 's' : ''}
                    </div>
                 </div>
                 <span class="chat-hd-item-del"
                       onclick="deleteSession('${s.id}',event)" title="Delete">✕</span>`;
                row.onclick = () => switchSession(s.id);
                list.appendChild(row);
            });
        });
    }

    // ── Render full panel messages ────────────────────────────────────
    function renderFullMessages() {
        const el = document.getElementById('chatPanelMessages');
        el.innerHTML = '';
        appendMsgEl(el, 'bot',
            <?= json_encode($role === 'Teacher'
                ? "I can help with your subjects, students, modules, assignments, quizzes, announcements, attendance, grades and awards in this eLMS. What do you need?"
                : "I can help you with your subjects, explain concepts, assist with homework, or answer any school-related questions. What would you like to know?") ?>
                .replace(/^/, `Hello ${USER_NAME}! 👋 I'm <strong>${BOT_NAME}</strong>. `),
            timeNow(), true);

        syncEmptyState();
        if (!currentSession || currentSession.messages.length === 0) {
            scrollChat('chatPanelMessages');
            return;
        }

        let lastDate = null;
        currentSession.messages.forEach(msg => {
            const d = relativeDay(msg.ts || new Date().toISOString());
            if (d !== lastDate) {
                const div = document.createElement('div');
                div.className = 'chat-date-divider';
                div.textContent = d;
                el.appendChild(div);
                lastDate = d;
            }
            // Saved messages are plain text: always escape + format, never inject as raw HTML.
            appendMsgEl(el, msg.role === 'user' ? 'user' : 'bot', msg.content, fmtTime(msg.ts));
        });
        scrollChat('chatPanelMessages');
    }

    // ── Send — full panel ─────────────────────────────────────────────
    async function sendFullChat() {
        if (fullBusy || !currentSession) return;
        const input = document.getElementById('chatPanelInput');
        const text = input.value.trim();
        if (!text) return;
        input.value = '';
        fullBusy = true;
        document.getElementById('chatPanelSend').disabled = true;

        ebxAutoGrow(input);
        const ts = new Date().toISOString();
        currentSession.messages.push({ role: 'user', content: text, ts });
        saveSessions();
        syncEmptyState();
        autoTitleSession(text);
        updateHistCount();

        appendMsgEl(document.getElementById('chatPanelMessages'), 'user', text, timeNow());
        const typing = appendTyping('chatPanelMessages');

        const apiMsgs = currentSession.messages
            .filter(m => m.role === 'user' || m.role === 'assistant')
            .map(m => ({ role: m.role, content: m.content }));

        const reply = await callBot(apiMsgs);
        typing.remove();

        currentSession.messages.push({ role: 'assistant', content: reply, ts: new Date().toISOString() });
        currentSession.updatedAt = new Date().toISOString();
        saveSessions();

        fullBusy = false;
        document.getElementById('chatPanelSend').disabled = false;
        appendMsgEl(document.getElementById('chatPanelMessages'), 'bot', reply, timeNow());
        scrollChat('chatPanelMessages');
    }
    function sendFullQuick(text) { document.getElementById('chatPanelInput').value = text; sendFullChat(); }
    function handlePanelKey(e) { if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendFullChat(); } }

    // ── Send — floating window (ephemeral) ───────────────────────────
    async function sendChatWin() {
        if (winBusy) return;
        const input = document.getElementById('chatInputWin');
        const text = input.value.trim();
        if (!text) return;
        input.value = '';
        winBusy = true;
        document.getElementById('chatSendWin').disabled = true;
        document.getElementById('chatQuickWin').style.display = 'none';
        appendMsg('chatMessages', 'user', text);
        winHistory.push({ role: 'user', content: text });
        const typing = appendTyping('chatMessages');
        const reply = await callBot(winHistory);
        typing.remove();
        winBusy = false;
        document.getElementById('chatSendWin').disabled = false;
        appendMsg('chatMessages', 'bot', reply);
        winHistory.push({ role: 'assistant', content: reply });
        scrollChat('chatMessages');
    }
    function sendQuick(text) { document.getElementById('chatInputWin').value = text; sendChatWin(); }

    // ── Toggle / navigate ─────────────────────────────────────────────
    function toggleChatWindow() {
        const win = document.getElementById('chatWindow');
        const badge = document.getElementById('chatBadge');
        win.classList.toggle('open');
        badge.classList.remove('show');
        if (win.classList.contains('open')) scrollChat('chatMessages');
    }
    function goToFullChat() {
        document.getElementById('chatWindow').classList.remove('open');
        if (typeof showPanel === 'function') {
            const btn = [...document.querySelectorAll('.sidebar-link')]
                .find(l => l.textContent.includes('EduBot') || l.textContent.includes('AI'));
            showPanel('chatbot', btn || null);
        }
    }

    // ── API ───────────────────────────────────────────────────────────
    async function callBot(history) {
        try {
            const res = await fetch('../api/chatbot.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ messages: history })
            });
            const data = await res.json();
            return data.success ? data.reply : '⚠️ ' + (data.message || 'Something went wrong.');
        } catch (e) {
            return '⚠️ Could not reach the AI service. Please check your connection.';
        }
    }

    // ── Full-page layout helpers (redesign) ──────────────────────────
    // The title field was removed from the header; chat titles still show in History.
    function setSessionTitleField(v) {
        const el = document.getElementById('sessionTitleInput');
        if (el) el.value = v;
    }
    function syncEmptyState() {
        const wrap = document.getElementById('chatPanelWrap');
        if (wrap) wrap.classList.toggle('is-empty', !currentSession || currentSession.messages.length === 0);
    }
    function ebxAutoGrow(el) {
        if (!el) return;
        el.style.height = 'auto';
        el.style.height = Math.min(el.scrollHeight, 180) + 'px';
    }
    function ebxFitPanel() {
        const wrap = document.getElementById('chatPanelWrap');
        if (!wrap) return;
        const top = wrap.getBoundingClientRect().top + window.scrollY;
        const vh = window.innerHeight;
        // Phones: the sidebar stacks above the page, so give the chat a full screen of its own.
        const h = top > vh * 0.5 ? vh - 16 : vh - top - 16;
        wrap.style.height = Math.max(440, h) + 'px';
    }
    function ebxGreeting() {
        const el = document.getElementById('ebxGreeting');
        if (!el) return;
        const h = new Date().getHours();
        el.textContent = h < 12 ? 'Good morning' : (h < 18 ? 'Good afternoon' : 'Good evening');
    }

    // ── DOM helpers ───────────────────────────────────────────────────
    function appendMsg(containerId, type, text) {
        appendMsgEl(document.getElementById(containerId), type, text, timeNow());
    }
    function appendMsgEl(container, type, html, time, raw) {
        const div = document.createElement('div');
        div.className = 'chat-msg ' + type;
        div.innerHTML = (raw ? html : formatMd(html)) + `<div class="chat-msg-time">${time}</div>`;
        container.appendChild(div);
        return div;
    }
    function appendTyping(containerId) {
        const el = document.getElementById(containerId);
        const div = document.createElement('div');
        div.className = 'chat-msg typing';
        div.innerHTML = `🤖 ${BOT_NAME} is typing<span id="typingDots">...</span>`;
        el.appendChild(div);
        scrollChat(containerId);
        let d = 0;
        div._interval = setInterval(() => {
            const dots = document.getElementById('typingDots');
            if (dots) dots.textContent = ['...', '.. ', '. ', ' . '][d++ % 4];
        }, 400);
        return { remove: () => { clearInterval(div._interval); div.remove(); } };
    }
    // Safe Markdown: the text is HTML-escaped FIRST, so replies can never
    // inject real HTML/scripts into the page. Then a small subset of
    // Markdown is applied (code blocks, headings, lists, bold, italic, code).
    function formatMd(text) {
        const blocks = [];
        let t = escHtml(String(text ?? ''));
        // fenced code blocks -> placeholder (kept literal, not formatted)
        t = t.replace(/```[\w-]*\n?([\s\S]*?)```/g, (m, code) => {
            blocks.push('<pre class="chat-code"><code>' + code.replace(/\n$/, '') + '</code></pre>');
            return '\u0000' + (blocks.length - 1) + '\u0000';
        });
        t = t
            .replace(/^#{1,6}\s+(.+)$/gm, '<strong class="chat-h">$1</strong>')
            .replace(/^\s*[-*]\s+(.+)$/gm, '• $1')
            .replace(/^\s*-{3,}\s*$/gm, '')
            .replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>')
            .replace(/(^|[^*])\*(?!\s)(.+?)\*/g, '$1<em>$2</em>')
            .replace(/`([^`\n]+?)`/g, '<code class="chat-inline-code">$1</code>')
            .replace(/\n/g, '<br>');
        return t.replace(/\u0000(\d+)\u0000/g, (m, i) => blocks[+i]);
    }
    function escHtml(s) {
        return s.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }
    function scrollChat(id) {
        const el = document.getElementById(id);
        if (el) setTimeout(() => el.scrollTop = el.scrollHeight, 50);
    }
    function timeNow() {
        const d = new Date(); let h = d.getHours(), m = d.getMinutes(), ap = h >= 12 ? 'PM' : 'AM';
        h = h % 12 || 12; return h + ':' + String(m).padStart(2, '0') + ' ' + ap;
    }
    function fmtTime(iso) {
        if (!iso) return '';
        try {
            const d = new Date(iso); let h = d.getHours(), m = d.getMinutes(), ap = h >= 12 ? 'PM' : 'AM';
            h = h % 12 || 12; return h + ':' + String(m).padStart(2, '0') + ' ' + ap;
        } catch (e) { return ''; }
    }
    function relativeDay(iso) {
        if (!iso) return 'Unknown';
        try {
            const d = new Date(iso), now = new Date();
            const diff = Math.floor((now - d) / 86400000);
            if (diff === 0) return 'Today';
            if (diff === 1) return 'Yesterday';
            if (diff < 7) return d.toLocaleDateString('en-PH', { weekday: 'long' });
            return d.toLocaleDateString('en-PH', { month: 'short', day: 'numeric', year: 'numeric' });
        } catch (e) { return 'Earlier'; }
    }

    // ── Boot ──────────────────────────────────────────────────────────
    (function init() {
        if (document.getElementById('chatPanelWrap')) {
            document.body.classList.add('edubot-page');
            ebxGreeting();
            ebxFitPanel();
            window.addEventListener('resize', ebxFitPanel);
        }
        loadSessions();
        currentSession = allSessions.length > 0 ? allSessions[0] : createSession('New Chat');
        updateHistCount();
        renderHistoryList();
        renderFullMessages();
        setSessionTitleField(currentSession.title);
        setTimeout(() => document.getElementById('chatBadge')?.classList.add('show'), 2000);
    })();
</script>