<?php
if (!isset($_SESSION)) {
    session_start();
}

include "../config/database.php";

$community_name = "No Community";
$community_id = $_SESSION['community_id'] ?? 0;

if (isset($_SESSION['community_id'])) {
    $cid = (int) $_SESSION['community_id'];
    $result = $conn->query("SELECT name FROM communities WHERE id = $cid");
    if ($result && $row = $result->fetch_assoc()) {
        $community_name = $row['name'];
    }
}

// Fetch notifications
$notifications = $conn->query("
    SELECT * FROM events
    WHERE community_id = $community_id OR community_id IS NULL
    ORDER BY created_at DESC
    LIMIT 5
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>MCAD — Event Management System</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
    * {
        box-sizing: border-box;
    }
    
    body {
        margin: 0;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background: #121212;
        color: #fff;
    }

    /* TOP HEADER - Fixed with sidebar offset */
    .top-header {
        position: fixed;
        top: 0;
        left: 260px;
        right: 0;
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 15px 30px;
        background: rgba(18,18,18,.95);
        border-bottom: 1px solid rgba(255,255,255,.07);
        z-index: 90;
        height: 70px;
        backdrop-filter: blur(10px);
    }

    /* SEARCH */
    .search-container {
        position: relative;
    }

    .search-box {
        padding: 12px 20px 12px 40px;
        width: 320px;
        border-radius: 25px;
        border: 1px solid rgba(255,255,255,.12);
        outline: none;
        background: rgba(255,255,255,.08);
        color: #fff;
        font-size: 14px;
        transition: all 0.3s ease;
    }

    .search-box::placeholder { color: rgba(255,255,255,.35); }

    .search-box:focus {
        border-color: #ff00aa;
        background: rgba(255,255,255,.12);
        box-shadow: 0 0 0 3px rgba(255,0,170,0.15);
    }

    .search-icon {
        position: absolute;
        left: 14px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 15px;
        pointer-events: none;
        opacity: .5;
    }

    .search-dropdown {
        position: absolute;
        top: calc(100% + 8px);
        left: 0;
        width: 360px;
        background: #282828;
        border: 1px solid rgba(255,255,255,.1);
        border-radius: 14px;
        box-shadow: 0 10px 40px rgba(0,0,0,.5);
        display: none;
        z-index: 9999;
        overflow: hidden;
    }

    .search-dropdown.open { display: block; }

    .sd-item {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        padding: 12px 16px;
        text-decoration: none;
        color: #fff;
        transition: background .15s;
        border-bottom: 1px solid rgba(255,255,255,.06);
        cursor: pointer;
    }
    .sd-item:last-child { border-bottom: none; }
    .sd-item:hover { background: rgba(255,0,170,.08); }

    .sd-icon {
        width: 32px; height: 32px;
        border-radius: 8px;
        display: flex; align-items: center; justify-content: center;
        font-size: 15px;
        flex-shrink: 0;
    }
    .sd-icon.event        { background: rgba(167,139,250,.2); }
    .sd-icon.announcement { background: rgba(251,191,36,.2); }
    .sd-icon.forum        { background: rgba(74,222,128,.2); }
    .sd-icon.donation     { background: rgba(59,130,246,.2); }
    .sd-icon.appointment  { background: rgba(236,72,153,.2); }
    .sd-icon.page         { background: rgba(168,85,247,.2); }

    .sd-title { font-size: 13px; font-weight: 600; color: #fff; margin: 0 0 2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 270px; }
    .sd-meta  { font-size: 11px; color: rgba(255,255,255,.4); margin: 0; }

    .sd-empty { padding: 20px; text-align: center; color: rgba(255,255,255,.3); font-size: 13px; }
    .sd-loading { padding: 16px; text-align: center; color: rgba(255,255,255,.3); font-size: 13px; }

    /* RIGHT SIDE */
    .header-right {
        display: flex;
        align-items: center;
        gap: 20px;
    }

    .icon-btn {
        width: 42px;
        height: 42px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        background: rgba(255,255,255,.08);
        border: none;
        font-size: 20px;
        position: relative;
        transition: all 0.3s ease;
    }

    .icon-btn:hover {
        background: rgba(255,0,170,.2);
    }

    .notification-badge {
        position: absolute;
        top: -2px;
        right: -2px;
        width: 18px;
        height: 18px;
        background: #ff00aa;
        color: white;
        border-radius: 50%;
        font-size: 10px;
        font-weight: 600;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    /* PROFILE */
    .profile {
        display: flex;
        align-items: center;
        gap: 12px;
        cursor: pointer;
        padding: 8px 15px;
        border-radius: 12px;
        transition: background 0.3s ease;
        position: relative;
    }

    .profile:hover {
        background: rgba(255,255,255,.08);
    }

    .profile-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: linear-gradient(135deg, #ff4dc4 0%, #ff00aa 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 600;
        font-size: 16px;
    }

    .profile-info {
        display: flex;
        flex-direction: column;
        line-height: 1.3;
    }

    .profile-name {
        font-size: 14px;
        font-weight: 600;
        color: #fff;
    }

    .profile-role {
        font-size: 12px;
        color: rgba(255,255,255,.5);
    }

    .dropdown-arrow {
        font-size: 10px;
        color: rgba(255,255,255,.4);
        margin-left: 5px;
    }

    /* DROPDOWN MENU */
    .dropdown-menu {
        position: absolute;
        top: 60px;
        right: 0;
        background: #282828;
        border: 1px solid rgba(255,255,255,.1);
        border-radius: 12px;
        width: 180px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.5);
        display: none;
        flex-direction: column;
        overflow: hidden;
        z-index: 1000;
    }

    .dropdown-menu.show {
        display: flex;
    }

    .dropdown-menu a {
        padding: 12px 16px;
        text-decoration: none;
        color: rgba(255,255,255,.75);
        font-size: 14px;
        display: flex;
        align-items: center;
        gap: 10px;
        transition: background 0.2s ease;
    }

    .dropdown-menu a:hover {
        background: rgba(255,255,255,.08);
        color: #fff;
    }

    .dropdown-menu a.logout {
        color: #ff6b6b;
        border-top: 1px solid rgba(255,255,255,.08);
    }

    /* NOTIFICATION POPUP */
    .notification-popup {
        position: absolute;
        top: 60px;
        right: 80px;
        width: 320px;
        background: #282828;
        border: 1px solid rgba(255,255,255,.1);
        border-radius: 16px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.5);
        display: none;
        max-height: 400px;
        overflow: hidden;
        z-index: 1000;
    }

    .notification-popup.show {
        display: block;
    }

    .notification-header {
        padding: 15px 20px;
        border-bottom: 1px solid rgba(255,255,255,.08);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .notification-header h4 {
        margin: 0;
        font-size: 14px;
        font-weight: 600;
        color: #fff;
    }

    .notification-list {
        max-height: 300px;
        overflow-y: auto;
    }

    .notification-item {
        padding: 14px 18px;
        border-bottom: 1px solid rgba(255,255,255,.06);
        transition: background 0.2s ease;
        cursor: pointer;
    }

    .notification-item:hover {
        background: rgba(255,255,255,.06);
    }

    .notification-item:last-child {
        border-bottom: none;
    }

    .notification-item.read .unread-dot { display: none; }
    .notification-item.read .notification-title { color: rgba(255,255,255,.5); font-weight:400; }

    .notification-title {
        font-weight: 600;
        font-size: 13px;
        color: #fff;
        margin: 0 0 5px 0;
    }

    .notification-meta {
        font-size: 12px;
        color: rgba(255,255,255,.4);
        margin: 0;
    }

    .notification-empty {
        padding: 30px;
        text-align: center;
        color: rgba(255,255,255,.35);
    }

    /* LAYOUT */
    .layout {
        display: flex;
        min-height: 100vh;
    }
    </style>
</head>

<body>

<div class="top-header">
    <!-- LEFT - Search -->
    <div class="search-container">
        <span class="search-icon">🔍</span>
        <input type="text" id="globalSearch" class="search-box"
               placeholder="Search events, forum, donations..."
               autocomplete="off">
        <div id="searchDropdown" class="search-dropdown"></div>
    </div>

    <!-- RIGHT - Icons & Profile -->
    <div class="header-right">
        <?php
        $notif_count = ($notifications && $notifications->num_rows > 0) ? $notifications->num_rows : 0;
        $notif_rows  = [];
        if ($notifications) {
            $notifications->data_seek(0);
            while ($nr = $notifications->fetch_assoc()) $notif_rows[] = $nr;
        }
        ?>
        <!-- Notification Bell -->
        <button class="icon-btn" id="notifBtn" onclick="toggleNotifications()">
            🔔
            <?php if ($notif_count > 0): ?>
            <span class="notification-badge" id="notifBadge"><?= $notif_count ?></span>
            <?php endif; ?>
        </button>

        <!-- Notification Popup -->
        <div id="notificationPopup" class="notification-popup">
            <div class="notification-header">
                <h4>Notifications</h4>
                <a href="#" id="markAllRead" onclick="markAllRead(event)" style="font-size:12px;color:#ff00aa;text-decoration:none;">Mark all read</a>
            </div>
            <div class="notification-list" id="notifList">
                <?php if (!empty($notif_rows)): ?>
                    <?php foreach ($notif_rows as $n): ?>
                    <div class="notification-item unread" data-id="<?= $n['id'] ?>">
                        <div style="display:flex;align-items:flex-start;gap:10px;">
                            <div style="width:8px;height:8px;border-radius:50%;background:#ff00aa;flex-shrink:0;margin-top:4px;" class="unread-dot"></div>
                            <div style="flex:1;">
                                <p class="notification-title"><?= htmlspecialchars($n['title']) ?></p>
                                <p class="notification-meta">
                                    📅 <?= date('M d', strtotime($n['event_date'])) ?>
                                    <?php if ($n['event_time']): ?> at <?= date('g:i A', strtotime($n['event_time'])) ?><?php endif; ?>
                                    · <?= ucfirst($n['status']) ?>
                                </p>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="notification-empty">
                        <div style="font-size:32px;margin-bottom:8px;">🔔</div>
                        <p style="margin:0;font-size:13px;">No new notifications</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Messages -->
        <button class="icon-btn">💬</button>

        <!-- Profile -->
        <div class="profile" onclick="toggleProfileDropdown()">
            <div class="profile-avatar">
                <?= strtoupper(substr($_SESSION['name'] ?? 'U', 0, 1)) ?>
            </div>
            <div class="profile-info">
                <span class="profile-name"><?= htmlspecialchars($_SESSION['name'] ?? 'User') ?></span>
                <span class="profile-role"><?= ucfirst($_SESSION['role'] ?? 'viewer') ?></span>
            </div>
            <span class="dropdown-arrow">▼</span>
            
            <!-- Dropdown Menu -->
            <div id="profileDropdown" class="dropdown-menu">
                <a href="profile.php">👤 My Profile</a>
                <a href="settings.php">⚙️ Settings</a>
                <a href="help.php">❓ Help Center</a>
                <a href="../actions/logout.php" class="logout">🚪 Logout</a>
            </div>
        </div>
    </div>
</div>

<div class="layout">

<script>
// Toggle Profile Dropdown
function toggleProfileDropdown() {
    const menu = document.getElementById("profileDropdown");
    menu.classList.toggle("show");
    
    // Close notification popup if open
    document.getElementById("notificationPopup").classList.remove("show");
}

// Toggle Notifications
function toggleNotifications() {
    const popup = document.getElementById("notificationPopup");
    popup.classList.toggle("show");

    // Close profile dropdown if open
    document.getElementById("profileDropdown").classList.remove("show");
}

// Mark individual item as read on click
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.notification-item').forEach(function(item) {
        item.addEventListener('click', function() {
            if (!item.classList.contains('read')) {
                item.classList.add('read');
                updateBadge();
            }
        });
    });
});

function updateBadge() {
    var unread = document.querySelectorAll('.notification-item.unread:not(.read)').length;
    var badge  = document.getElementById('notifBadge');
    if (badge) {
        if (unread === 0) {
            badge.style.display = 'none';
        } else {
            badge.textContent   = unread;
            badge.style.display = 'flex';
        }
    }
}

function markAllRead(e) {
    e.preventDefault();
    document.querySelectorAll('.notification-item').forEach(function(item) {
        item.classList.add('read');
    });
    var badge = document.getElementById('notifBadge');
    if (badge) badge.style.display = 'none';

    var link = document.getElementById('markAllRead');
    if (link) { link.textContent = '✓ All read'; link.style.color = 'rgba(255,255,255,.3)'; }
}

// Close dropdowns when clicking outside
window.onclick = function(e) {
    if (!e.target.closest('.profile')) {
        document.getElementById("profileDropdown").classList.remove("show");
    }
    if (!e.target.closest('.icon-btn')) {
        document.getElementById("notificationPopup").classList.remove("show");
    }
    if (!e.target.closest('.search-container')) {
        document.getElementById("searchDropdown").classList.remove("open");
    }
}

// ── Live Search ──────────────────────────────────────
(function() {
    var input    = document.getElementById('globalSearch');
    var dropdown = document.getElementById('searchDropdown');
    var timer    = null;

    var icons = {
        event: '📅',
        announcement: '📢',
        forum: '💬',
        donation: '❤️',
        appointment: '📝',
        page: '📄',
        dashboard: '📊',
        calendar: '📅',
        donations: '🤝',
        announce: '📣',
        users: '👥',
        appt: '✅'
    };
    var labels = {
        event: 'event',
        announcement: 'announcement',
        forum: 'forum thread',
        donation: 'donation',
        appointment: 'appointment',
        page: 'page'
    };

    function render(results) {
        if (!results.length) {
            dropdown.innerHTML = '<p class="sd-empty">No results found.</p>';
        } else {
            dropdown.innerHTML = results.map(function(r) {
                var icon = icons[r.icon_name] || icons[r.type] || '🔍';
                return '<a class="sd-item" href="' + r.url + '">' +
                    '<div class="sd-icon ' + r.type + '">' + icon + '</div>' +
                    '<div>' +
                        '<p class="sd-title">' + escHtml(r.title) + '</p>' +
                        '<p class="sd-meta">' + escHtml(r.meta) + '</p>' +
                    '</div>' +
                '</a>';
            }).join('');
        }
        dropdown.classList.add('open');
    }

    function escHtml(s) {
        return s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
    }

    function ucFirst(s) {
        return s.charAt(0).toUpperCase() + s.slice(1);
    }

    input.addEventListener('input', function() {
        var q = input.value.trim();
        clearTimeout(timer);

        if (q.length < 2) {
            dropdown.classList.remove('open');
            return;
        }

        dropdown.innerHTML = '<p class="sd-loading">Searching…</p>';
        dropdown.classList.add('open');

        timer = setTimeout(function() {
            fetch('../actions/search.php?q=' + encodeURIComponent(q))
                .then(function(res) { return res.json(); })
                .then(render)
                .catch(function() {
                    dropdown.innerHTML = '<p class="sd-empty">Search unavailable.</p>';
                });
        }, 280);
    });

    input.addEventListener('focus', function() {
        if (input.value.trim().length >= 2 && dropdown.innerHTML !== '') {
            dropdown.classList.add('open');
        }
    });
})();
</script>