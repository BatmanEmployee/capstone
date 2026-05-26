<?php
if (!isset($_SESSION)) {
    session_start();
}

$role = $_SESSION['role'] ?? 'viewer';
$current_page = basename($_SERVER['PHP_SELF'], '.php');

function isActive($page, $current) {
    return $page === $current ? 'active' : '';
}
?>

<style>
.sidebar {
    width: 260px;
    background: #000;
    height: 100vh;
    padding: 0;
    display: flex;
    flex-direction: column;
    position: fixed;
    left: 0;
    top: 0;
    z-index: 100;
    box-shadow: 4px 0 20px rgba(0,0,0,0.4);
}

.sidebar-header {
    padding: 25px 20px;
    background: linear-gradient(135deg, #ff4dc4 0%, #ff00aa 100%);
    text-align: center;
}

.sidebar-header h2 {
    margin: 0;
    color: white;
    font-size: 24px;
    font-weight: 700;
    letter-spacing: 1px;
}

.sidebar-header small {
    color: rgba(255,255,255,0.8);
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 2px;
}

.user-info {
    padding: 20px;
    background: rgba(255,255,255,0.04);
    border-bottom: 1px solid rgba(255,255,255,0.07);
    text-align: center;
}

.user-avatar {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: linear-gradient(135deg, #ff4dc4 0%, #ff00aa 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 10px;
    font-size: 24px;
    color: white;
    font-weight: bold;
}

.user-info .name {
    color: #fff;
    font-size: 14px;
    font-weight: 600;
    margin: 0;
}

.user-info .role {
    color: #ff4dc4;
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 1px;
    margin: 5px 0 0;
}

.sidebar-menu {
    flex: 1;
    overflow-y: auto;
    padding: 15px 0;
}

.menu-section {
    margin-bottom: 20px;
}

.menu-title {
    color: rgba(255,255,255,.3);
    font-size: 10px;
    text-transform: uppercase;
    letter-spacing: 1.5px;
    padding: 0 20px 10px;
    margin: 0;
    font-weight: 700;
}

.sidebar a {
    display: flex;
    align-items: center;
    color: rgba(255,255,255,.6);
    padding: 12px 20px;
    text-decoration: none;
    transition: all 0.2s ease;
    margin: 2px 10px;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 500;
}

.sidebar a:hover {
    background: rgba(255,255,255,.08);
    color: #fff;
}

.sidebar a.active {
    background: linear-gradient(135deg, #ff4dc4 0%, #ff00aa 100%);
    color: white;
    box-shadow: 0 4px 15px rgba(255,0,170,0.3);
    font-weight: 700;
}

.sidebar a i {
    width: 24px;
    margin-right: 12px;
    font-size: 16px;
}

.sidebar-footer {
    padding: 15px;
    border-top: 1px solid rgba(255,255,255,0.07);
}

.sidebar-footer a {
    background: rgba(220,53,69,0.2);
    color: #ff6b6b;
    justify-content: center;
}

.sidebar-footer a:hover {
    background: #dc3545;
    color: white;
}

/* Role badges */
.role-badge {
    display: inline-block;
    padding: 3px 10px;
    border-radius: 12px;
    font-size: 10px;
    font-weight: 600;
    text-transform: uppercase;
}

.role-admin { background: #dc3545; color: white; }
.role-imam { background: #28a745; color: white; }
.role-leader { background: #007bff; color: white; }
.role-viewer { background: #6c757d; color: white; }
</style>

<div class="sidebar">
    <div class="sidebar-header">
        <h2>MCAD</h2>
        <small>City Mayor's Office</small>
    </div>
    
    <div class="user-info">
        <div class="user-avatar"><?= strtoupper(substr($_SESSION['name'] ?? 'U', 0, 1)) ?></div>
        <p class="name"><?= htmlspecialchars($_SESSION['name'] ?? 'User') ?></p>
        <p class="role"><?= ucfirst($role) ?></p>
    </div>
    
    <div class="sidebar-menu">
        <!-- Dashboard - All Roles -->
        <div class="menu-section">
            <p class="menu-title">Main</p>
            <a href="dashboard.php" class="<?= isActive('dashboard', $current_page) ?>">
                <i>📊</i> Dashboard
            </a>
        </div>
        
        <?php if ($role === 'admin'): ?>
        <!-- Admin Menu -->
        <div class="menu-section">
            <p class="menu-title">Management</p>
            <a href="appointments.php" class="<?= isActive('appointments', $current_page) ?>"
               style="<?= isActive('appointments', $current_page) ? '' : 'position:relative;' ?>">
                <i>📋</i> Appointments
                <?php
                // Show pending count badge
                if (isset($conn)) {
                    $pCount = $conn->query("SELECT COUNT(*) c FROM appointments WHERE status='pending'")->fetch_assoc()['c'];
                    if ($pCount > 0) {
                        echo "<span style='margin-left:auto;background:#ff00aa;color:white;font-size:10px;
                              padding:2px 7px;border-radius:10px;font-weight:700;'>{$pCount}</span>";
                    }
                }
                ?>
            </a>
            <a href="events.php" class="<?= isActive('events', $current_page) ?>">
                <i>📅</i> All Events
            </a>
            <a href="attendance.php" class="<?= isActive('attendance', $current_page) ?>">
                <i>✅</i> Attendance
            </a>
            <a href="announcements.php" class="<?= isActive('announcements', $current_page) ?>">
                <i>📢</i> Announcements
            </a>
            <a href="donations.php" class="<?= isActive('donations', $current_page) ?>">
                <i>💰</i> Donations
            </a>
            <a href="forum.php" class="<?= isActive('forum', $current_page) ?>">
                <i>💬</i> Community Forum
            </a>
            <a href="messages.php" class="<?= isActive('messages', $current_page) ?>">
                <i>✉️</i> Messages
            </a>
            <a href="reports.php" class="<?= isActive('reports', $current_page) ?>">
                <i>📈</i> Reports & Analytics
            </a>
        </div>
        <div class="menu-section">
            <p class="menu-title">System</p>
            <a href="users.php" class="<?= isActive('users', $current_page) ?>">
                <i>👥</i> User Management
            </a>
            <a href="communities.php" class="<?= isActive('communities', $current_page) ?>">
                <i>🏘️</i> Communities
            </a>
        </div>

        <?php elseif ($role === 'imam'): ?>
        <!-- Imam Menu -->
        <div class="menu-section">
            <p class="menu-title">Religious Affairs</p>
            <a href="events.php" class="<?= isActive('events', $current_page) ?>">
                <i>📅</i> Events & Programs
            </a>
            <a href="attendance.php" class="<?= isActive('attendance', $current_page) ?>">
                <i>✅</i> Attendance
            </a>
            <a href="announcements.php" class="<?= isActive('announcements', $current_page) ?>">
                <i>📢</i> Islamic Announcements
            </a>
        </div>
        <div class="menu-section">
            <p class="menu-title">Community</p>
            <a href="donations.php" class="<?= isActive('donations', $current_page) ?>">
                <i>💰</i> Zakat & Donations
            </a>
            <a href="forum.php" class="<?= isActive('forum', $current_page) ?>">
                <i>💬</i> Community Forum
            </a>
            <a href="messages.php" class="<?= isActive('messages', $current_page) ?>">
                <i>✉️</i> Messages
            </a>
            <a href="reports.php" class="<?= isActive('reports', $current_page) ?>">
                <i>📊</i> Reports
            </a>
        </div>

        <?php elseif ($role === 'leader'): ?>
        <!-- Leader Menu -->
        <div class="menu-section">
            <p class="menu-title">Leadership</p>
            <a href="events.php" class="<?= isActive('events', $current_page) ?>">
                <i>📅</i> Manage Events
            </a>
            <a href="attendance.php" class="<?= isActive('attendance', $current_page) ?>">
                <i>✅</i> Attendance
            </a>
            <a href="announcements.php" class="<?= isActive('announcements', $current_page) ?>">
                <i>📢</i> Post Announcements
            </a>
        </div>
        <div class="menu-section">
            <p class="menu-title">Activities</p>
            <a href="donations.php" class="<?= isActive('donations', $current_page) ?>">
                <i>💰</i> Track Donations
            </a>
            <a href="forum.php" class="<?= isActive('forum', $current_page) ?>">
                <i>💬</i> Community Forum
            </a>
            <a href="messages.php" class="<?= isActive('messages', $current_page) ?>">
                <i>✉️</i> Messages
            </a>
            <a href="reports.php" class="<?= isActive('reports', $current_page) ?>">
                <i>📊</i> Activity Reports
            </a>
        </div>

        <?php else: ?>
        <!-- Viewer Menu -->
        <div class="menu-section">
            <p class="menu-title">Explore</p>
            <a href="events.php" class="<?= isActive('events', $current_page) ?>">
                <i>📅</i> Browse Events
            </a>
            <a href="announcements.php" class="<?= isActive('announcements', $current_page) ?>">
                <i>📢</i> Announcements
            </a>
            <a href="donations.php" class="<?= isActive('donations', $current_page) ?>">
                <i>💝</i> Make Donation
            </a>
            <a href="forum.php" class="<?= isActive('forum', $current_page) ?>">
                <i>💬</i> Community Forum
            </a>
            <a href="messages.php" class="<?= isActive('messages', $current_page) ?>">
                <i>✉️</i> Messages
            </a>
        </div>
        <?php endif; ?>
    </div>
    
    <div class="sidebar-footer">
        <a href="../actions/logout.php">
            <i>🚪</i> Logout
        </a>
    </div>
</div>