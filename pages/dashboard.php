<?php
session_start();
include "../config/database.php";
include "../functions/system_events.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

generateSystemEvents($conn);

$user_id = (int) $_SESSION['user_id'];
$name = $_SESSION['name'];
$role = $_SESSION['role'];

$res = $conn->query("SELECT community_id FROM users WHERE id = $user_id");
$user = $res->fetch_assoc();
$community_id = (int) ($user['community_id'] ?? 0);

// WHERE clause for non-admin users
$whereClause = "
(
    user_id = $user_id 
    OR visibility = 'community'
    OR event_type = 'system'
)
AND (
    community_id = $community_id
    OR community_id IS NULL
)
";

// ==================== STATISTICS QUERIES ====================

if ($role == 'admin') {
    // Admin sees all stats
    $totalEvents = $conn->query("SELECT COUNT(*) as total FROM events")->fetch_assoc()['total'];
    $upcomingEvents = $conn->query("SELECT COUNT(*) as total FROM events WHERE event_date >= CURDATE()")->fetch_assoc()['total'];
    $totalUsers = $conn->query("SELECT COUNT(*) as total FROM users")->fetch_assoc()['total'];
    $totalDonations = $conn->query("SELECT COALESCE(SUM(amount), 0) as total FROM donations WHERE donation_type = 'cash'")->fetch_assoc()['total'];
    $activeEvents = $conn->query("SELECT * FROM events WHERE event_date >= CURDATE() ORDER BY event_date ASC LIMIT 5");
    
    // Admin specific stats
    $totalCommunities = $conn->query("SELECT COUNT(*) as total FROM communities")->fetch_assoc()['total'];
    $pendingAnnouncements = $conn->query("SELECT COUNT(*) as total FROM announcements WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)")->fetch_assoc()['total'];
    $pendingAppointments = (int) $conn->query("SELECT COUNT(*) as total FROM appointments WHERE status='pending'")->fetch_assoc()['total'];
    
} elseif ($role == 'imam') {
    // Imam sees community + system events
    $totalEvents = $conn->query("SELECT COUNT(*) as total FROM events WHERE $whereClause OR event_type = 'system'")->fetch_assoc()['total'];
    $upcomingEvents = $conn->query("SELECT COUNT(*) as total FROM events WHERE ($whereClause OR event_type = 'system') AND event_date >= CURDATE()")->fetch_assoc()['total'];
    $communityMembers = $conn->query("SELECT COUNT(*) as total FROM users WHERE community_id = $community_id OR community_id IS NULL")->fetch_assoc()['total'];
    $totalDonations = $conn->query("SELECT COALESCE(SUM(amount), 0) as total FROM donations WHERE community_id = $community_id OR community_id IS NULL")->fetch_assoc()['total'];
    $activeEvents = $conn->query("SELECT * FROM events WHERE ($whereClause OR event_type = 'system') AND event_date >= CURDATE() ORDER BY event_date ASC LIMIT 5");
    
    // Imam specific - prayer times, khutbah count
    $khutbahCount = $conn->query("SELECT COUNT(*) as total FROM events WHERE title LIKE '%khutbah%' OR title LIKE '%juma%' AND event_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)")->fetch_assoc()['total'];
    
} elseif ($role == 'leader') {
    // Leader sees community stats
    $totalEvents = $conn->query("SELECT COUNT(*) as total FROM events WHERE community_id = $community_id OR community_id IS NULL OR visibility = 'community'")->fetch_assoc()['total'];
    $upcomingEvents = $conn->query("SELECT COUNT(*) as total FROM events WHERE (community_id = $community_id OR community_id IS NULL OR visibility = 'community') AND event_date >= CURDATE()")->fetch_assoc()['total'];
    $communityMembers = $conn->query("SELECT COUNT(*) as total FROM users WHERE community_id = $community_id OR community_id IS NULL")->fetch_assoc()['total'];
    $volunteers = $conn->query("SELECT COUNT(*) as total FROM users WHERE role IN ('leader', 'imam') AND (community_id = $community_id OR community_id IS NULL)")->fetch_assoc()['total'];
    $activeEvents = $conn->query("SELECT * FROM events WHERE (community_id = $community_id OR community_id IS NULL OR visibility = 'community') AND event_date >= CURDATE() ORDER BY event_date ASC LIMIT 5");
    
} else {
    // Viewer sees personal + community events
    $totalEvents = $conn->query("SELECT COUNT(*) as total FROM events WHERE $whereClause")->fetch_assoc()['total'];
    $upcomingEvents = $conn->query("SELECT COUNT(*) as total FROM events WHERE $whereClause AND event_date >= CURDATE()")->fetch_assoc()['total'];
    $myEvents = $conn->query("SELECT COUNT(*) as total FROM events WHERE user_id = $user_id")->fetch_assoc()['total'];
    $registeredEvents = $conn->query("SELECT COUNT(*) as total FROM events WHERE $whereClause AND event_date >= CURDATE()")->fetch_assoc()['total'];
    $activeEvents = $conn->query("SELECT * FROM events WHERE $whereClause AND event_date >= CURDATE() ORDER BY event_date ASC LIMIT 5");
}

$activePercent = $totalEvents > 0 ? round(($upcomingEvents / $totalEvents) * 100) : 0;

// ==================== ANNOUNCEMENTS ====================
if ($role == 'admin') {
    $latestAnnouncement = $conn->query("SELECT a.*, u.name FROM announcements a LEFT JOIN users u ON a.user_id = u.id ORDER BY a.created_at DESC LIMIT 1");
    $recentAnnouncements = $conn->query("SELECT a.*, u.name FROM announcements a LEFT JOIN users u ON a.user_id = u.id ORDER BY a.created_at DESC LIMIT 3");
} else {
    $latestAnnouncement = $conn->query("SELECT a.*, u.name FROM announcements a LEFT JOIN users u ON a.user_id = u.id WHERE a.community_id = $community_id OR a.community_id = 0 ORDER BY a.created_at DESC LIMIT 1");
    $recentAnnouncements = $conn->query("SELECT a.*, u.name FROM announcements a LEFT JOIN users u ON a.user_id = u.id WHERE a.community_id = $community_id OR a.community_id = 0 ORDER BY a.created_at DESC LIMIT 3");
}

// ==================== RECENT ACTIVITY ====================
$recentActivity = $conn->query("
    (SELECT 'event' as type, title as description, created_at, user_id FROM events WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) ORDER BY created_at DESC LIMIT 3)
    UNION ALL
    (SELECT 'announcement' as type, title as description, created_at, user_id FROM announcements WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) ORDER BY created_at DESC LIMIT 3)
    ORDER BY created_at DESC
    LIMIT 5
");

include "../includes/header.php";
include "../includes/sidebar.php";
?>

<style>
/* Dashboard Layout */
.dashboard-container {
    margin-left: 260px;
    padding: 90px 30px 30px;
    background: #121212;
    min-height: 100vh;
}

/* Welcome Section */
.welcome-section {
    background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
    border-radius: 20px;
    padding: 30px;
    margin-bottom: 25px;
    color: white;
    position: relative;
    overflow: hidden;
}

.welcome-section::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -10%;
    width: 300px;
    height: 300px;
    background: linear-gradient(135deg, #ff4dc4 0%, #ff00aa 100%);
    border-radius: 50%;
    opacity: 0.3;
}

.welcome-content {
    position: relative;
    z-index: 1;
}

.welcome-title {
    font-size: 28px;
    font-weight: 700;
    margin: 0 0 10px 0;
}

.welcome-subtitle {
    font-size: 14px;
    color: #a0aec0;
    margin: 0;
}

.role-badge-dashboard {
    display: inline-block;
    padding: 6px 16px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    margin-top: 15px;
    background: linear-gradient(135deg, #ff4dc4 0%, #ff00aa 100%);
    color: white;
}

/* Stats Grid */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 25px;
}

.stat-card {
    background: #1e1e1e;
    border-radius: 16px;
    padding: 25px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.3);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    position: relative;
    overflow: hidden;
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.4);
    background: #242424;
}

.stat-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 4px;
    height: 100%;
    background: linear-gradient(180deg, #ff4dc4 0%, #ff00aa 100%);
}

.stat-icon {
    width: 50px;
    height: 50px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    margin-bottom: 15px;
}

.stat-icon.pink { background: linear-gradient(135deg, #ff4dc4 0%, #ff00aa 100%); }
.stat-icon.blue { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
.stat-icon.green { background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); }
.stat-icon.orange { background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); }
.stat-icon.purple { background: linear-gradient(135deg, #a18cd1 0%, #fbc2eb 100%); }
.stat-icon.red { background: linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 100%); }

.stat-value {
    font-size: 32px;
    font-weight: 700;
    color: #fff;
    margin: 0;
}

.stat-label {
    font-size: 14px;
    color: rgba(255,255,255,.45);
    margin: 5px 0 0 0;
}

/* Dashboard Grid */
.dashboard-grid {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 25px;
}

@media (max-width: 1200px) {
    .dashboard-grid {
        grid-template-columns: 1fr;
    }
}

/* Section Cards */
.section-card {
    background: #1e1e1e;
    border-radius: 16px;
    padding: 25px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.3);
    margin-bottom: 20px;
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.section-title {
    font-size: 18px;
    font-weight: 700;
    color: #fff;
    margin: 0;
}

.view-all {
    color: #ff00aa;
    text-decoration: none;
    font-size: 13px;
    font-weight: 500;
}

.view-all:hover {
    text-decoration: underline;
}

/* Event Items */
.event-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.event-item {
    display: flex;
    align-items: center;
    padding: 15px;
    background: rgba(255,255,255,.04);
    border-radius: 12px;
    transition: background 0.3s ease;
}

.event-item:hover {
    background: rgba(255,255,255,.08);
}

.event-date {
    width: 60px;
    height: 60px;
    background: linear-gradient(135deg, #ff4dc4 0%, #ff00aa 100%);
    border-radius: 12px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    color: white;
    margin-right: 15px;
    flex-shrink: 0;
}

.event-date .day {
    font-size: 20px;
    font-weight: 700;
    line-height: 1;
}

.event-date .month {
    font-size: 11px;
    text-transform: uppercase;
    opacity: 0.9;
}

.event-info {
    flex: 1;
}

.event-title {
    font-weight: 600;
    color: #fff;
    margin: 0 0 5px 0;
    display: flex;
    align-items: center;
    gap: 8px;
}

.event-meta {
    font-size: 13px;
    color: rgba(255,255,255,.45);
    margin: 0;
}

.event-badge {
    font-size: 10px;
    padding: 3px 8px;
    border-radius: 10px;
    font-weight: 500;
}

.badge-system { background: rgba(255,0,170,.2); color: #ff4dc4; }
.badge-community { background: rgba(2,132,199,.2); color: #38bdf8; }
.badge-personal { background: rgba(255,255,255,.1); color: rgba(255,255,255,.5); }

.event-status {
    font-size: 12px;
    padding: 4px 10px;
    border-radius: 20px;
    font-weight: 600;
    white-space: nowrap;
}

.status-today { background: rgba(22,163,74,.25); color: #4ade80; }
.status-upcoming { background: rgba(217,119,6,.25); color: #fbbf24; }
.status-completed { background: rgba(255,255,255,.08); color: rgba(255,255,255,.4); }

/* Announcement Card */
.announcement-card {
    background: rgba(255,0,170,.07);
    border-left: 4px solid #ff00aa;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 15px;
}

.announcement-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 10px;
}

.announcement-title {
    font-weight: 600;
    color: #fff;
    margin: 0;
    font-size: 16px;
}

.announcement-author {
    font-size: 12px;
    color: rgba(255,255,255,.4);
    margin: 0;
}

.announcement-content {
    font-size: 14px;
    color: rgba(255,255,255,.6);
    line-height: 1.6;
    margin: 0;
}

/* Activity List */
.activity-list {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.activity-item {
    display: flex;
    align-items: flex-start;
    gap: 12px;
}

.activity-icon {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    flex-shrink: 0;
}

.activity-icon.event { background: rgba(79,172,254,.15); }
.activity-icon.announcement { background: rgba(255,77,196,.15); }
.activity-icon.donation { background: rgba(67,233,123,.15); }

.activity-content {
    flex: 1;
}

.activity-text {
    font-size: 13px;
    color: rgba(255,255,255,.7);
    margin: 0 0 3px 0;
}

.activity-time {
    font-size: 11px;
    color: rgba(255,255,255,.3);
    margin: 0;
}

/* Quick Actions */
.quick-actions {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 10px;
}

.quick-action-btn {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 20px;
    background: rgba(255,255,255,.06);
    border-radius: 12px;
    text-decoration: none;
    color: rgba(255,255,255,.7);
    transition: all 0.3s ease;
}

.quick-action-btn:hover {
    background: linear-gradient(135deg, #ff4dc4 0%, #ff00aa 100%);
    color: white;
    transform: translateY(-3px);
}

.quick-action-btn i {
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 8px;
}
.quick-action-btn i svg { display: block; }

.quick-action-btn span {
    font-size: 12px;
    font-weight: 500;
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 40px 20px;
    color: rgba(255,255,255,.3);
}

.empty-state i {
    font-size: 48px;
    margin-bottom: 15px;
    opacity: 0.3;
}

/* Responsive */
@media (max-width: 768px) {
    .dashboard-container {
        margin-left: 0;
        padding: 15px;
    }
    
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .welcome-title {
        font-size: 22px;
    }
}
</style>

<div class="dashboard-container">
    <!-- Welcome Section -->
    <div class="welcome-section">
        <div class="welcome-content">
            <h1 class="welcome-title">Welcome back, <?= htmlspecialchars($name) ?>!</h1>
            <p class="welcome-subtitle">
                <?php if ($role == 'admin'): ?>
                    Manage your MCAD system and monitor all activities
                <?php elseif ($role == 'imam'): ?>
                    Guide your community through spiritual programs and events
                <?php elseif ($role == 'leader'): ?>
                    Lead your community and organize meaningful activities
                <?php else: ?>
                    Stay connected with your community events and announcements
                <?php endif; ?>
            </p>
            <span class="role-badge-dashboard"><?= ucfirst($role) ?> Account</span>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="stats-grid">
        <?php if ($role == 'admin'): ?>
            <!-- Admin Stats -->
            <div class="stat-card">
                <div class="stat-icon pink"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg></div>
                <h3 class="stat-value"><?= $totalEvents ?></h3>
                <p class="stat-label">Total Events</p>
            </div>
            <div class="stat-card">
                <div class="stat-icon blue"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/><polyline points="9 16 11 18 15 14"/></svg></div>
                <h3 class="stat-value"><?= $upcomingEvents ?></h3>
                <p class="stat-label">Upcoming Events</p>
            </div>
            <div class="stat-card">
                <div class="stat-icon green"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg></div>
                <h3 class="stat-value"><?= $totalUsers ?></h3>
                <p class="stat-label">Total Users</p>
            </div>
            <div class="stat-card">
                <div class="stat-icon orange"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg></div>
                <h3 class="stat-value"><?= $totalCommunities ?></h3>
                <p class="stat-label">Communities</p>
            </div>
            <div class="stat-card">
                <div class="stat-icon purple"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg></div>
                <h3 class="stat-value">₱<?= number_format($totalDonations, 2) ?></h3>
                <p class="stat-label">Total Donations</p>
            </div>
            <div class="stat-card">
                <div class="stat-icon red"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"/><path d="M15.54 8.46a5 5 0 0 1 0 7.07"/></svg></div>
                <h3 class="stat-value"><?= $pendingAnnouncements ?></h3>
                <p class="stat-label">Recent Announcements</p>
            </div>
            <div class="stat-card" style="cursor:pointer;" onclick="location.href='appointments.php?status=pending'">
                <div class="stat-icon pink"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><rect x="8" y="2" width="8" height="4" rx="1"/><line x1="9" y1="12" x2="15" y2="12"/><line x1="9" y1="16" x2="13" y2="16"/></svg></div>
                <h3 class="stat-value" style="color:<?= $pendingAppointments > 0 ? '#ff00aa' : '#fff' ?>">
                    <?= $pendingAppointments ?>
                </h3>
                <p class="stat-label">Pending Appointments</p>
            </div>

        <?php elseif ($role == 'imam'): ?>
            <!-- Imam Stats -->
            <div class="stat-card">
                <div class="stat-icon pink"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg></div>
                <h3 class="stat-value"><?= $totalEvents ?></h3>
                <p class="stat-label">Religious Events</p>
            </div>
            <div class="stat-card">
                <div class="stat-icon blue"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/><polyline points="9 16 11 18 15 14"/></svg></div>
                <h3 class="stat-value"><?= $upcomingEvents ?></h3>
                <p class="stat-label">Upcoming Programs</p>
            </div>
            <div class="stat-card">
                <div class="stat-icon green"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg></div>
                <h3 class="stat-value"><?= $communityMembers ?></h3>
                <p class="stat-label">Community Members</p>
            </div>
            <div class="stat-card">
                <div class="stat-icon orange"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg></div>
                <h3 class="stat-value">₱<?= number_format($totalDonations, 2) ?></h3>
                <p class="stat-label">Zakat &amp; Donations</p>
            </div>
            <div class="stat-card">
                <div class="stat-icon purple"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg></div>
                <h3 class="stat-value"><?= $khutbahCount ?></h3>
                <p class="stat-label">Recent Khutbahs</p>
            </div>
            
        <?php elseif ($role == 'leader'): ?>
            <!-- Leader Stats -->
            <div class="stat-card">
                <div class="stat-icon pink"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg></div>
                <h3 class="stat-value"><?= $totalEvents ?></h3>
                <p class="stat-label">Community Events</p>
            </div>
            <div class="stat-card">
                <div class="stat-icon blue"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/><polyline points="9 16 11 18 15 14"/></svg></div>
                <h3 class="stat-value"><?= $upcomingEvents ?></h3>
                <p class="stat-label">Upcoming Activities</p>
            </div>
            <div class="stat-card">
                <div class="stat-icon green"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg></div>
                <h3 class="stat-value"><?= $communityMembers ?></h3>
                <p class="stat-label">Members</p>
            </div>
            <div class="stat-card">
                <div class="stat-icon orange"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/></svg></div>
                <h3 class="stat-value"><?= $volunteers ?></h3>
                <p class="stat-label">Active Volunteers</p>
            </div>
            
        <?php else: ?>
            <!-- Viewer Stats -->
            <div class="stat-card">
                <div class="stat-icon pink"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg></div>
                <h3 class="stat-value"><?= $totalEvents ?></h3>
                <p class="stat-label">Available Events</p>
            </div>
            <div class="stat-card">
                <div class="stat-icon blue"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/><polyline points="9 16 11 18 15 14"/></svg></div>
                <h3 class="stat-value"><?= $upcomingEvents ?></h3>
                <p class="stat-label">Coming Soon</p>
            </div>
            <div class="stat-card">
                <div class="stat-icon green"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg></div>
                <h3 class="stat-value"><?= $myEvents ?></h3>
                <p class="stat-label">My Events</p>
            </div>
            <div class="stat-card">
                <div class="stat-icon orange"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg></div>
                <h3 class="stat-value"><?= $registeredEvents ?></h3>
                <p class="stat-label">Registered</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Main Dashboard Grid -->
    <div class="dashboard-grid">
        <!-- Left Column -->
        <div class="left-column">
            <!-- Upcoming Events -->
            <div class="section-card">
                <div class="section-header">
                    <h3 class="section-title">Upcoming Events</h3>
                    <a href="events.php" class="view-all">View All →</a>
                </div>
                <div class="event-list">
                    <?php if ($activeEvents && $activeEvents->num_rows > 0): ?>
                        <?php while($row = $activeEvents->fetch_assoc()): 
                            $today = date("Y-m-d");
                            $eventDate = strtotime($row['event_date']);
                            
                            if ($row['event_date'] < $today) {
                                $statusClass = "status-completed";
                                $statusText = "Completed";
                            } elseif ($row['event_date'] == $today) {
                                $statusClass = "status-today";
                                $statusText = "Today";
                            } else {
                                $statusClass = "status-upcoming";
                                $statusText = "Upcoming";
                            }
                            
                            if ($row['event_type'] == 'system') {
                                $badgeClass = "badge-system";
                                $badgeText = "System";
                            } elseif ($row['visibility'] == 'community') {
                                $badgeClass = "badge-community";
                                $badgeText = "Community";
                            } else {
                                $badgeClass = "badge-personal";
                                $badgeText = "Personal";
                            }
                        ?>
                            <div class="event-item">
                                <div class="event-date">
                                    <span class="day"><?= date('d', $eventDate) ?></span>
                                    <span class="month"><?= date('M', $eventDate) ?></span>
                                </div>
                                <div class="event-info">
                                    <p class="event-title">
                                        <?= htmlspecialchars($row['title']) ?>
                                        <span class="event-badge <?= $badgeClass ?>"><?= $badgeText ?></span>
                                    </p>
                                    <p class="event-meta">
                                        <?= date('l', $eventDate) ?> • <?= $row['event_time'] ?> • <?= htmlspecialchars($row['venue']) ?>
                                    </p>
                                </div>
                                <span class="event-status <?= $statusClass ?>"><?= $statusText ?></span>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="empty-state">
                            <p>No upcoming events found.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Recent Announcements -->
            <div class="section-card">
                <div class="section-header">
                    <h3 class="section-title">Recent Announcements</h3>
                    <a href="announcements.php" class="view-all">View All →</a>
                </div>
                <?php if ($recentAnnouncements && $recentAnnouncements->num_rows > 0): ?>
                    <?php while($a = $recentAnnouncements->fetch_assoc()): ?>
                        <div class="announcement-card">
                            <div class="announcement-header">
                                <h4 class="announcement-title"><?= htmlspecialchars($a['title']) ?></h4>
                            </div>
                            <p class="announcement-author">
                                By <?= htmlspecialchars($a['name'] ?? 'Unknown') ?> • <?= date("M d, Y", strtotime($a['created_at'])) ?>
                            </p>
                            <p class="announcement-content">
                                <?= nl2br(htmlspecialchars(substr($a['message'], 0, 150))) ?><?= strlen($a['message']) > 150 ? '...' : '' ?>
                            </p>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <p>No announcements yet.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Right Column -->
        <div class="right-column">
            <!-- Role-Specific Quick Actions -->
            <div class="section-card">
                <div class="section-header">
                    <h3 class="section-title">Quick Actions</h3>
                </div>
                <div class="quick-actions">
                    <?php if ($role == 'admin'): ?>
                        <a href="add_event.php" class="quick-action-btn">
                            <i><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/><line x1="12" y1="14" x2="12" y2="20"/><line x1="9" y1="17" x2="15" y2="17"/></svg></i>
                            <span>Add Event</span>
                        </a>
                        <a href="announcements.php" class="quick-action-btn">
                            <i><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"/><path d="M15.54 8.46a5 5 0 0 1 0 7.07"/></svg></i>
                            <span>Announcement</span>
                        </a>
                        <a href="donations.php" class="quick-action-btn">
                            <i><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg></i>
                            <span>Record Donation</span>
                        </a>
                        <a href="reports.php" class="quick-action-btn">
                            <i><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg></i>
                            <span>View Reports</span>
                        </a>
                    <?php elseif ($role == 'imam'): ?>
                        <a href="add_event.php" class="quick-action-btn">
                            <i><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg></i>
                            <span>Add Program</span>
                        </a>
                        <a href="announcements.php" class="quick-action-btn">
                            <i><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"/><path d="M15.54 8.46a5 5 0 0 1 0 7.07"/></svg></i>
                            <span>Islamic Update</span>
                        </a>
                        <a href="forum.php" class="quick-action-btn">
                            <i><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg></i>
                            <span>Forum</span>
                        </a>
                        <a href="attendance.php" class="quick-action-btn">
                            <i><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><polyline points="16 11 18 13 22 9"/></svg></i>
                            <span>Attendance</span>
                        </a>
                    <?php elseif ($role == 'leader'): ?>
                        <a href="add_event.php" class="quick-action-btn">
                            <i><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg></i>
                            <span>Schedule Event</span>
                        </a>
                        <a href="announcements.php" class="quick-action-btn">
                            <i><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"/><path d="M15.54 8.46a5 5 0 0 1 0 7.07"/></svg></i>
                            <span>Post Update</span>
                        </a>
                        <a href="attendance.php" class="quick-action-btn">
                            <i><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><polyline points="16 11 18 13 22 9"/></svg></i>
                            <span>Attendance</span>
                        </a>
                        <a href="donations.php" class="quick-action-btn">
                            <i><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg></i>
                            <span>Donations</span>
                        </a>
                    <?php else: ?>
                        <a href="events.php" class="quick-action-btn">
                            <i><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg></i>
                            <span>Browse Events</span>
                        </a>
                        <a href="announcements.php" class="quick-action-btn">
                            <i><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"/><path d="M15.54 8.46a5 5 0 0 1 0 7.07"/></svg></i>
                            <span>Announcements</span>
                        </a>
                        <a href="donations.php" class="quick-action-btn">
                            <i>💝</i>
                            <span>Donate</span>
                        </a>
                        <a href="forum.php" class="quick-action-btn">
                            <i>💬</i>
                            <span>Forum</span>
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="section-card">
                <div class="section-header">
                    <h3 class="section-title">🔔 Recent Activity</h3>
                </div>
                <div class="activity-list">
                    <?php if ($recentActivity && $recentActivity->num_rows > 0): 
                        while($activity = $recentActivity->fetch_assoc()):
                            $icon = $activity['type'] == 'event' ? '📅' : '📢';
                            $class = $activity['type'] == 'event' ? 'event' : 'announcement';
                            $text = $activity['type'] == 'event' ? 'New event created' : 'New announcement posted';
                            $timeDiff = time() - strtotime($activity['created_at']);
                            if ($timeDiff < 3600) {
                                $timeText = round($timeDiff / 60) . ' minutes ago';
                            } elseif ($timeDiff < 86400) {
                                $timeText = round($timeDiff / 3600) . ' hours ago';
                            } else {
                                $timeText = round($timeDiff / 86400) . ' days ago';
                            }
                    ?>
                        <div class="activity-item">
                            <div class="activity-icon <?= $class ?>"><?= $icon ?></div>
                            <div class="activity-content">
                                <p class="activity-text"><?= $text ?>: <?= htmlspecialchars(substr($activity['description'], 0, 30)) ?>...</p>
                                <p class="activity-time"><?= $timeText ?></p>
                            </div>
                        </div>
                    <?php endwhile; else: ?>
                        <div class="empty-state">
                            <p>No recent activity</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Latest Announcement Highlight -->
            <?php if ($latestAnnouncement && $latestAnnouncement->num_rows > 0): 
                $latest = $latestAnnouncement->fetch_assoc(); ?>
            <div class="section-card" style="background: linear-gradient(135deg, #ff4dc4 0%, #ff00aa 100%); color: white;">
                <div class="section-header">
                    <h3 class="section-title" style="color: white;">📢 Latest Update</h3>
                </div>
                <h4 style="margin: 0 0 10px 0; font-size: 16px;"><?= htmlspecialchars($latest['title']) ?></h4>
                <p style="margin: 0; font-size: 13px; opacity: 0.9; line-height: 1.5;">
                    <?= nl2br(htmlspecialchars(substr($latest['message'], 0, 100))) ?><?= strlen($latest['message']) > 100 ? '...' : '' ?>
                </p>
                <p style="margin: 15px 0 0 0; font-size: 11px; opacity: 0.8;">
                    By <?= htmlspecialchars($latest['name'] ?? 'Unknown') ?> • <?= date("M d, Y", strtotime($latest['created_at'])) ?>
                </p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include "../includes/footer.php"; ?>

