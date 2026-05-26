<?php
session_start();
include "../config/database.php";
include "../functions/csrf.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id      = (int) $_SESSION['user_id'];
$role         = $_SESSION['role'];
$community_id = (int) ($_SESSION['community_id'] ?? 0);

// ─── Role-based: who can THIS user message? ───────────────────
// viewer  → can only message admin/imam/leader (not other viewers)
// leader  → same community + all admins/imams
// imam    → same community + all admins/imams/leaders
// admin   → everyone
function buildContactsQuery($role, $community_id) {
    if ($role === 'admin') {
        return "SELECT id, name, role, community_id FROM users WHERE id != ? ORDER BY name";
    }
    if ($role === 'imam') {
        return "SELECT id, name, role, community_id FROM users
                WHERE id != ?
                AND (community_id = $community_id OR role IN ('admin','imam','leader'))
                ORDER BY name";
    }
    if ($role === 'leader') {
        return "SELECT id, name, role, community_id FROM users
                WHERE id != ?
                AND (community_id = $community_id OR role IN ('admin','imam'))
                ORDER BY name";
    }
    // viewer
    return "SELECT id, name, role, community_id FROM users
            WHERE id != ? AND role IN ('admin','imam','leader')
            ORDER BY name";
}

$stmt = $conn->prepare(buildContactsQuery($role, $community_id));
$stmt->bind_param("i", $user_id);
$stmt->execute();
$contacts = $stmt->get_result();
$contactList = [];
while ($row = $contacts->fetch_assoc()) {
    $contactList[] = $row;
}
$stmt->close();

// ─── Get chat groups this user belongs to ─────────────────────
$stmt = $conn->prepare("
    SELECT cg.id, cg.name, cg.description, cg.community_id
    FROM chat_groups cg
    JOIN chat_group_members cgm ON cgm.group_id = cg.id
    WHERE cgm.user_id = ?
    ORDER BY cg.name
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$groups = $stmt->get_result();
$groupList = [];
while ($row = $groups->fetch_assoc()) {
    $groupList[] = $row;
}
$stmt->close();

// ─── Get last-message preview + unread count per DM contact ──
$stmt = $conn->prepare("
    SELECT
        CASE WHEN sender_id = ? THEN receiver_id ELSE sender_id END AS contact_id,
        MAX(created_at) AS last_at,
        SUM(CASE WHEN receiver_id = ? AND is_read = 0 THEN 1 ELSE 0 END) AS unread
    FROM messages
    WHERE sender_id = ? OR receiver_id = ?
    GROUP BY contact_id
");
$stmt->bind_param("iiii", $user_id, $user_id, $user_id, $user_id);
$stmt->execute();
$convoMeta = [];
$r = $stmt->get_result();
while ($row = $r->fetch_assoc()) {
    $convoMeta[(int)$row['contact_id']] = $row;
}
$stmt->close();

include "../includes/header.php";
include "../includes/sidebar.php";
?>

<style>
.msg-container {
    margin-left: 260px;
    padding: 90px 30px 30px;
    background: #121212;
    min-height: 100vh;
}

.msg-header {
    margin-bottom: 20px;
}
.msg-header h1 {
    font-size: 28px; color: #fff; margin: 0 0 6px;
}
.msg-header p { color: rgba(255,255,255,.5); margin: 0; font-size: 14px; }

.msg-layout {
    display: grid;
    grid-template-columns: 320px 1fr;
    gap: 20px;
    height: calc(100vh - 200px);
    min-height: 500px;
}

.msg-sidebar {
    background: #1e1e1e;
    border-radius: 16px;
    overflow: hidden;
    display: flex;
    flex-direction: column;
}

.msg-tabs {
    display: flex;
    border-bottom: 1px solid rgba(255,255,255,.08);
}
.msg-tab {
    flex: 1;
    padding: 16px;
    text-align: center;
    cursor: pointer;
    color: rgba(255,255,255,.5);
    font-size: 14px;
    font-weight: 600;
    transition: all .2s;
    border-bottom: 2px solid transparent;
}
.msg-tab.active {
    color: #ff00aa;
    border-bottom-color: #ff00aa;
}
.msg-tab:hover { background: rgba(255,255,255,.03); }

.msg-list {
    flex: 1;
    overflow-y: auto;
}
.msg-list-section { display: none; }
.msg-list-section.active { display: block; }

.contact-item, .group-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 14px 16px;
    cursor: pointer;
    border-bottom: 1px solid rgba(255,255,255,.04);
    transition: background .15s;
    color: #fff;
    text-decoration: none;
}
.contact-item:hover, .group-item:hover { background: rgba(255,0,170,.06); }
.contact-item.active, .group-item.active { background: rgba(255,0,170,.12); }

.contact-avatar {
    width: 42px; height: 42px;
    border-radius: 50%;
    background: linear-gradient(135deg, #ff4dc4 0%, #ff00aa 100%);
    display: flex; align-items: center; justify-content: center;
    color: #fff; font-weight: 700; font-size: 16px;
    flex-shrink: 0;
}
.group-item .contact-avatar { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }

.contact-info { flex: 1; min-width: 0; }
.contact-name {
    color: #fff; font-weight: 600; font-size: 14px;
    margin: 0 0 2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.contact-role {
    color: rgba(255,255,255,.4); font-size: 11px;
    text-transform: uppercase; margin: 0;
}

.contact-badge {
    background: #ff00aa; color: #fff;
    border-radius: 12px; padding: 2px 8px;
    font-size: 11px; font-weight: 700;
    min-width: 22px; text-align: center;
}

/* ─── Chat Panel ──────────────────────────── */
.msg-chat {
    background: #1e1e1e;
    border-radius: 16px;
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

.chat-empty {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    color: rgba(255,255,255,.3);
}
.chat-empty .emoji { font-size: 64px; margin-bottom: 12px; }

.chat-header {
    padding: 18px 20px;
    border-bottom: 1px solid rgba(255,255,255,.08);
    display: flex;
    align-items: center;
    gap: 12px;
}
.chat-header h3 { color: #fff; margin: 0; font-size: 16px; }
.chat-header p { color: rgba(255,255,255,.4); margin: 2px 0 0; font-size: 12px; }

.chat-body {
    flex: 1;
    padding: 20px;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.bubble {
    max-width: 70%;
    padding: 10px 14px;
    border-radius: 16px;
    color: #fff;
    font-size: 14px;
    line-height: 1.4;
    word-wrap: break-word;
}
.bubble.them {
    background: rgba(255,255,255,.08);
    align-self: flex-start;
    border-bottom-left-radius: 4px;
}
.bubble.me {
    background: linear-gradient(135deg, #ff4dc4 0%, #ff00aa 100%);
    align-self: flex-end;
    border-bottom-right-radius: 4px;
}
.bubble-meta {
    font-size: 10px;
    color: rgba(255,255,255,.4);
    margin-top: 4px;
}
.bubble.them .bubble-sender {
    color: #ff4dc4; font-size: 11px; font-weight: 600; margin-bottom: 4px;
}

.chat-input {
    padding: 16px 20px;
    border-top: 1px solid rgba(255,255,255,.08);
    display: flex;
    gap: 10px;
}
.chat-input input {
    flex: 1;
    background: rgba(255,255,255,.06);
    border: 1px solid rgba(255,255,255,.08);
    border-radius: 12px;
    padding: 12px 16px;
    color: #fff;
    font-size: 14px;
    outline: none;
}
.chat-input input:focus { border-color: #ff00aa; }
.chat-input button {
    background: linear-gradient(135deg, #ff4dc4 0%, #ff00aa 100%);
    border: none;
    color: #fff;
    padding: 0 20px;
    border-radius: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: opacity .2s;
}
.chat-input button:hover { opacity: .85; }
.chat-input button:disabled { opacity: .4; cursor: not-allowed; }

@media (max-width: 900px) {
    .msg-layout { grid-template-columns: 1fr; }
    .msg-sidebar { height: 250px; }
}
</style>

<div class="msg-container">
    <div class="msg-header">
        <h1>💬 Messages</h1>
        <p>Connect with users and chat in your community groups</p>
    </div>

    <div class="msg-layout">
        <!-- Left Sidebar: Tabs + Contact/Group list -->
        <div class="msg-sidebar">
            <div class="msg-tabs">
                <div class="msg-tab active" data-tab="dm">Direct (<?= count($contactList) ?>)</div>
                <div class="msg-tab" data-tab="groups">Groups (<?= count($groupList) ?>)</div>
            </div>

            <div class="msg-list">
                <!-- DM Tab -->
                <div class="msg-list-section active" data-section="dm">
                    <?php if (empty($contactList)): ?>
                        <div style="padding:30px;text-align:center;color:rgba(255,255,255,.3);font-size:13px;">No contacts available</div>
                    <?php else: ?>
                        <?php foreach ($contactList as $c):
                            $meta = $convoMeta[$c['id']] ?? null;
                            $unread = (int)($meta['unread'] ?? 0);
                            $initial = strtoupper(substr($c['name'], 0, 1));
                        ?>
                            <div class="contact-item" data-user-id="<?= $c['id'] ?>" data-user-name="<?= htmlspecialchars($c['name']) ?>" data-user-role="<?= htmlspecialchars($c['role']) ?>">
                                <div class="contact-avatar"><?= $initial ?></div>
                                <div class="contact-info">
                                    <p class="contact-name"><?= htmlspecialchars($c['name']) ?></p>
                                    <p class="contact-role"><?= htmlspecialchars($c['role']) ?></p>
                                </div>
                                <?php if ($unread > 0): ?>
                                    <span class="contact-badge"><?= $unread ?></span>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Groups Tab -->
                <div class="msg-list-section" data-section="groups">
                    <?php if (empty($groupList)): ?>
                        <div style="padding:30px;text-align:center;color:rgba(255,255,255,.3);font-size:13px;">You're not in any groups yet</div>
                    <?php else: ?>
                        <?php foreach ($groupList as $g):
                            $initial = strtoupper(substr($g['name'], 0, 1));
                        ?>
                            <div class="group-item" data-group-id="<?= $g['id'] ?>" data-group-name="<?= htmlspecialchars($g['name']) ?>">
                                <div class="contact-avatar"><?= $initial ?></div>
                                <div class="contact-info">
                                    <p class="contact-name"><?= htmlspecialchars($g['name']) ?></p>
                                    <p class="contact-role">Group</p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Right Panel: Chat Area -->
        <div class="msg-chat" id="chatPanel">
            <div class="chat-empty" id="chatEmpty">
                <div class="emoji">💬</div>
                <p>Select a contact or group to start chatting</p>
            </div>

            <div class="chat-header" id="chatHeader" style="display:none;">
                <div class="contact-avatar" id="chatAvatar">?</div>
                <div>
                    <h3 id="chatTitle">—</h3>
                    <p id="chatSubtitle">—</p>
                </div>
            </div>

            <div class="chat-body" id="chatBody" style="display:none;"></div>

            <form class="chat-input" id="chatForm" style="display:none;" onsubmit="return sendMessage(event)">
                <input type="text" id="messageInput" placeholder="Type a message..." maxlength="2000" autocomplete="off" required>
                <button type="submit" id="sendBtn">Send</button>
            </form>
        </div>
    </div>
</div>

<script>
const CSRF_TOKEN = '<?= htmlspecialchars(csrf_token(), ENT_QUOTES) ?>';
const MY_USER_ID = <?= $user_id ?>;

let currentTarget = null; // { type: 'dm'|'group', id, name, role }
let pollTimer = null;
let lastMessageId = 0;

// Tab switching
document.querySelectorAll('.msg-tab').forEach(tab => {
    tab.addEventListener('click', () => {
        document.querySelectorAll('.msg-tab').forEach(t => t.classList.remove('active'));
        document.querySelectorAll('.msg-list-section').forEach(s => s.classList.remove('active'));
        tab.classList.add('active');
        document.querySelector('.msg-list-section[data-section="' + tab.dataset.tab + '"]').classList.add('active');
    });
});

// Open DM
document.querySelectorAll('.contact-item').forEach(item => {
    item.addEventListener('click', () => {
        document.querySelectorAll('.contact-item, .group-item').forEach(i => i.classList.remove('active'));
        item.classList.add('active');
        currentTarget = {
            type: 'dm',
            id: item.dataset.userId,
            name: item.dataset.userName,
            role: item.dataset.userRole
        };
        openChat();
        // Clear unread badge for this contact
        const badge = item.querySelector('.contact-badge');
        if (badge) badge.remove();
    });
});

// Open Group
document.querySelectorAll('.group-item').forEach(item => {
    item.addEventListener('click', () => {
        document.querySelectorAll('.contact-item, .group-item').forEach(i => i.classList.remove('active'));
        item.classList.add('active');
        currentTarget = {
            type: 'group',
            id: item.dataset.groupId,
            name: item.dataset.groupName,
            role: 'Group'
        };
        openChat();
    });
});

function openChat() {
    document.getElementById('chatEmpty').style.display = 'none';
    document.getElementById('chatHeader').style.display = 'flex';
    document.getElementById('chatBody').style.display = 'flex';
    document.getElementById('chatForm').style.display = 'flex';

    document.getElementById('chatAvatar').textContent = currentTarget.name.charAt(0).toUpperCase();
    document.getElementById('chatTitle').textContent = currentTarget.name;
    document.getElementById('chatSubtitle').textContent = currentTarget.role;

    lastMessageId = 0;
    document.getElementById('chatBody').innerHTML = '<p style="color:rgba(255,255,255,.3);text-align:center;">Loading…</p>';
    fetchMessages();

    if (pollTimer) clearInterval(pollTimer);
    pollTimer = setInterval(fetchMessages, 3000); // poll every 3s
}

function fetchMessages() {
    if (!currentTarget) return;
    const url = '../actions/fetch_messages.php?type=' + currentTarget.type + '&id=' + currentTarget.id;
    fetch(url)
        .then(r => r.json())
        .then(data => {
            if (!data.ok) return;
            renderMessages(data.messages);
        })
        .catch(() => {});
}

function renderMessages(messages) {
    const body = document.getElementById('chatBody');
    if (messages.length === 0) {
        body.innerHTML = '<p style="color:rgba(255,255,255,.3);text-align:center;margin:auto;">No messages yet. Say hello! 👋</p>';
        return;
    }

    // Only re-render if new messages
    const newestId = messages[messages.length - 1].id;
    if (newestId === lastMessageId) return;
    lastMessageId = newestId;

    body.innerHTML = messages.map(m => {
        const isMe = parseInt(m.sender_id) === MY_USER_ID;
        const time = new Date(m.created_at.replace(' ', 'T')).toLocaleString([], {
            month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit'
        });
        const senderLabel = (currentTarget.type === 'group' && !isMe)
            ? '<div class="bubble-sender">' + escapeHtml(m.sender_name || 'Unknown') + '</div>' : '';
        return '<div class="bubble ' + (isMe ? 'me' : 'them') + '">' +
                senderLabel +
                escapeHtml(m.message) +
                '<div class="bubble-meta">' + time + '</div>' +
               '</div>';
    }).join('');
    body.scrollTop = body.scrollHeight;
}

function sendMessage(e) {
    e.preventDefault();
    if (!currentTarget) return false;
    const input = document.getElementById('messageInput');
    const text = input.value.trim();
    if (!text) return false;

    const btn = document.getElementById('sendBtn');
    btn.disabled = true;

    const formData = new FormData();
    formData.append('csrf_token', CSRF_TOKEN);
    formData.append('type', currentTarget.type);
    formData.append('target_id', currentTarget.id);
    formData.append('message', text);

    fetch('../actions/send_message.php', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => {
            btn.disabled = false;
            if (data.ok) {
                input.value = '';
                fetchMessages();
            } else {
                alert(data.error || 'Failed to send message');
            }
        })
        .catch(() => {
            btn.disabled = false;
            alert('Network error');
        });

    return false;
}

function escapeHtml(s) {
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
</script>

<?php include "../includes/footer.php"; ?>
