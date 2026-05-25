<?php
session_start();
include "../config/database.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: dashboard.php");
    exit();
}

// Fetch communities with member counts
$communities = [];
$cRes = $conn->query("
    SELECT c.id, c.name,
           COUNT(u.id) AS total_members,
           SUM(CASE WHEN u.role = 'imam'   THEN 1 ELSE 0 END) AS imams,
           SUM(CASE WHEN u.role = 'leader' THEN 1 ELSE 0 END) AS leaders,
           SUM(CASE WHEN u.role = 'viewer' THEN 1 ELSE 0 END) AS viewers,
           SUM(CASE WHEN u.status = 'active' THEN 1 ELSE 0 END) AS active_members
    FROM communities c
    LEFT JOIN users u ON u.community_id = c.id
    GROUP BY c.id, c.name
    ORDER BY c.name ASC
");
while ($r = $cRes->fetch_assoc()) {
    $communities[] = $r;
}

// Fetch members grouped by community for the detail table
$members = [];
$mRes = $conn->query("
    SELECT u.id, u.name, u.email, u.role, u.status, c.name AS community_name, c.id AS community_id
    FROM users u
    LEFT JOIN communities c ON u.community_id = c.id
    WHERE u.community_id IS NOT NULL
    ORDER BY c.name ASC, u.name ASC
");
while ($r = $mRes->fetch_assoc()) {
    $members[$r['community_id']][] = $r;
}

// Total events per community
$eventCounts = [];
$eRes = $conn->query("
    SELECT community_id, COUNT(*) AS cnt FROM events
    WHERE community_id IS NOT NULL
    GROUP BY community_id
");
while ($r = $eRes->fetch_assoc()) {
    $eventCounts[$r['community_id']] = $r['cnt'];
}

include "../includes/header.php";
include "../includes/sidebar.php";
?>
<style>
.page-container { margin-left:260px; padding:90px 30px 30px; background:#f8fafc; min-height:100vh; }
.page-title    { font-size:24px; font-weight:700; color:#1a1a2e; margin:0 0 5px; }
.page-subtitle { font-size:14px; color:#6c757d; margin:0 0 25px; }

.summary-row { display:grid; grid-template-columns:repeat(3,1fr); gap:15px; margin-bottom:30px; }

.comm-card {
    background: white;
    border-radius: 16px;
    padding: 25px;
    box-shadow: 0 2px 10px rgba(0,0,0,.05);
    border-top: 4px solid #ff00aa;
    transition: transform .2s;
}
.comm-card:hover { transform: translateY(-3px); }

.comm-card h3 {
    font-size: 18px;
    font-weight: 700;
    color: #1a1a2e;
    margin: 0 0 15px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.comm-stat-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
    margin-bottom: 15px;
}

.comm-stat {
    background: #f8fafc;
    border-radius: 10px;
    padding: 12px;
    text-align: center;
}

.comm-stat .num  { font-size: 22px; font-weight: 700; color: #1a1a2e; margin: 0; }
.comm-stat .lbl  { font-size: 11px; color: #6c757d; margin: 3px 0 0; text-transform: uppercase; letter-spacing: .5px; }

.expand-btn {
    width: 100%;
    padding: 9px;
    border: 1.5px solid #e2e8f0;
    border-radius: 8px;
    background: white;
    font-size: 13px;
    font-weight: 600;
    color: #ff00aa;
    cursor: pointer;
    transition: all .2s;
}
.expand-btn:hover { background: #fff0fa; border-color: #ff00aa; }

.members-panel {
    display: none;
    margin-top: 15px;
    border-top: 1px solid #f3f4f6;
    padding-top: 15px;
}
.members-panel.open { display: block; }

.member-row {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px 0;
    border-bottom: 1px solid #f3f4f6;
    font-size: 13px;
}
.member-row:last-child { border-bottom: none; }

.mini-avatar {
    width: 30px; height: 30px;
    border-radius: 50%;
    background: linear-gradient(135deg, #ff4dc4, #ff00aa);
    display: flex; align-items: center; justify-content: center;
    color: white; font-weight: 700; font-size: 12px;
    flex-shrink: 0;
}

.role-badge { font-size: 10px; padding: 2px 8px; border-radius: 10px; font-weight: 600; }
.role-imam   { background: #dcfce7; color: #16a34a; }
.role-leader { background: #dbeafe; color: #2563eb; }
.role-viewer { background: #f3f4f6; color: #6b7280; }
.role-admin  { background: #fee2e2; color: #dc2626; }

.status-dot { width:7px; height:7px; border-radius:50%; display:inline-block; }
.dot-active    { background:#16a34a; }
.dot-suspended { background:#dc2626; }

.empty-msg { color: #9ca3af; font-size: 13px; text-align: center; padding: 15px 0; }

.total-banner {
    background: linear-gradient(135deg, #ff4dc4, #ff00aa);
    border-radius: 14px;
    padding: 20px 25px;
    display: flex;
    justify-content: space-around;
    margin-bottom: 30px;
    color: white;
    text-align: center;
}
.total-banner .tb-num  { font-size: 28px; font-weight: 700; margin: 0; }
.total-banner .tb-lbl  { font-size: 12px; opacity: .85; margin: 3px 0 0; text-transform: uppercase; letter-spacing: .5px; }
</style>

<div class="page-container">
    <h1 class="page-title">🏘️ Communities</h1>
    <p class="page-subtitle">Overview of registered barangay communities and their members</p>

    <?php
    $totalUsers   = array_sum(array_column($communities, 'total_members'));
    $totalActive  = array_sum(array_column($communities, 'active_members'));
    $totalEvents  = array_sum($eventCounts);
    ?>

    <!-- Banner totals -->
    <div class="total-banner">
        <div>
            <p class="tb-num"><?= count($communities) ?></p>
            <p class="tb-lbl">Communities</p>
        </div>
        <div>
            <p class="tb-num"><?= $totalUsers ?></p>
            <p class="tb-lbl">Total Members</p>
        </div>
        <div>
            <p class="tb-num"><?= $totalActive ?></p>
            <p class="tb-lbl">Active Members</p>
        </div>
        <div>
            <p class="tb-num"><?= $totalEvents ?></p>
            <p class="tb-lbl">Total Events</p>
        </div>
    </div>

    <!-- Community cards -->
    <div class="summary-row">
        <?php foreach ($communities as $c):
            $cMembers = $members[$c['id']] ?? [];
            $evCount  = $eventCounts[$c['id']] ?? 0;
        ?>
        <div class="comm-card">
            <h3>🏘️ <?= htmlspecialchars($c['name']) ?></h3>

            <div class="comm-stat-grid">
                <div class="comm-stat">
                    <p class="num"><?= $c['total_members'] ?></p>
                    <p class="lbl">Members</p>
                </div>
                <div class="comm-stat">
                    <p class="num" style="color:#16a34a;"><?= $c['active_members'] ?></p>
                    <p class="lbl">Active</p>
                </div>
                <div class="comm-stat">
                    <p class="num" style="color:#28a745;"><?= $c['imams'] ?></p>
                    <p class="lbl">Imams</p>
                </div>
                <div class="comm-stat">
                    <p class="num" style="color:#2563eb;"><?= $c['leaders'] ?></p>
                    <p class="lbl">Leaders</p>
                </div>
            </div>

            <div class="comm-stat" style="margin-bottom:12px;">
                <p class="num" style="font-size:18px;"><?= $evCount ?></p>
                <p class="lbl">Events Created</p>
            </div>

            <?php if (!empty($cMembers)): ?>
            <button class="expand-btn" onclick="togglePanel(this)">
                👥 View Members (<?= count($cMembers) ?>)
            </button>
            <div class="members-panel">
                <?php foreach ($cMembers as $m): ?>
                <div class="member-row">
                    <div class="mini-avatar"><?= strtoupper(substr($m['name'], 0, 1)) ?></div>
                    <div style="flex:1; min-width:0;">
                        <div style="font-weight:600; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                            <?= htmlspecialchars($m['name']) ?>
                        </div>
                        <div style="color:#9ca3af; font-size:11px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                            <?= htmlspecialchars($m['email']) ?>
                        </div>
                    </div>
                    <span class="role-badge role-<?= $m['role'] ?>"><?= ucfirst($m['role']) ?></span>
                    <span class="status-dot dot-<?= $m['status'] ?? 'active' ?>" title="<?= ucfirst($m['status'] ?? 'active') ?>"></span>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <p class="empty-msg">No members assigned yet.</p>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
function togglePanel(btn) {
    var panel = btn.nextElementSibling;
    var isOpen = panel.classList.contains('open');
    panel.classList.toggle('open');
    btn.textContent = isOpen
        ? btn.textContent.replace('▲', '').trim().replace('Hide', 'View Members')
        : btn.textContent.replace('View Members', 'Hide') + ' ▲';
}
</script>

<?php include "../includes/footer.php"; ?>
