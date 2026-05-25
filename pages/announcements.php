<?php
session_start();
include "../config/database.php";

date_default_timezone_set('Asia/Manila');

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = (int) $_SESSION['user_id'];
$role    = $_SESSION['role'];

$stmt = $conn->prepare("SELECT community_id FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$u = $stmt->get_result()->fetch_assoc();
$stmt->close();
$community_id = (int) $u['community_id'];

$announcements = [];
$stmt = $conn->prepare("
    SELECT a.*, u.name
    FROM announcements a
    LEFT JOIN users u ON a.user_id = u.id
    WHERE a.title != ''
    AND (a.community_id = ? OR a.community_id IS NULL OR a.community_id = 0)
    ORDER BY a.created_at DESC
");
$stmt->bind_param("i", $community_id);
$stmt->execute();
$aRes = $stmt->get_result();
while ($r = $aRes->fetch_assoc()) {
    $announcements[] = $r;
}
$stmt->close();

$cat_labels = [
    'prayer_schedule'   => ['label' => 'Prayer Schedule',     'color' => '#4ade80', 'bg' => 'rgba(22,163,74,.2)'],
    'event_reminder'    => ['label' => 'Event Reminder',      'color' => '#fbbf24', 'bg' => 'rgba(217,119,6,.2)'],
    'charity_drive'     => ['label' => 'Charity Drive',       'color' => '#38bdf8', 'bg' => 'rgba(2,132,199,.2)'],
    'community_advisory'=> ['label' => 'Community Advisory',  'color' => '#c084fc', 'bg' => 'rgba(124,58,237,.2)'],
];

include "../includes/header.php";
include "../includes/sidebar.php";
?>
<style>
.page-container { margin-left:260px; padding:90px 30px 30px; background:#121212; min-height:100vh; }
.page-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:25px; }
.page-title  { font-size:24px; font-weight:700; color:#fff; margin:0; }
.page-subtitle { font-size:14px; color:#9ca3af; margin:5px 0 0; }
.content-grid { display:grid; grid-template-columns:1fr 360px; gap:25px; }
@media(max-width:1100px){ .content-grid{grid-template-columns:1fr;} }
.section-card { background:#1e1e1e; border-radius:16px; padding:25px; box-shadow:0 2px 10px rgba(0,0,0,.3); margin-bottom:20px; }
.section-title { font-size:18px; font-weight:600; color:#fff; margin:0 0 20px; }
.ann-item { padding:18px 0; border-bottom:1px solid #2a2a2a; }
.ann-item:last-child { border-bottom:none; }
.ann-title { font-size:15px; font-weight:600; color:#e5e7eb; margin:0 0 4px; }
.ann-body  { font-size:14px; color:#d1d5db; line-height:1.6; margin:6px 0; }
.ann-meta  { font-size:12px; color:#6b7280; }
.cat-badge { font-size:11px; padding:3px 10px; border-radius:12px; font-weight:600; display:inline-block; margin-bottom:6px; }
.form-group { margin-bottom:14px; }
.form-group label { display:block; font-size:13px; font-weight:600; color:#9ca3af; margin-bottom:5px; }
.form-group input, .form-group textarea, .form-group select {
    width:100%; padding:11px 14px; border:1.5px solid #333; background:#242424; color:#fff;
    border-radius:10px; font-size:14px; box-sizing:border-box; transition:border-color .2s; font-family:inherit;
}
.form-group input::placeholder, .form-group textarea::placeholder { color:#555; }
.form-group select option { background:#242424; color:#fff; }
.form-group textarea { min-height:100px; resize:vertical; }
.form-group input:focus,.form-group textarea:focus,.form-group select:focus { outline:none; border-color:#ff00aa; background:#2a2a2a; }
.submit-btn { width:100%; padding:13px; background:linear-gradient(135deg,#ff4dc4,#ff00aa); color:white; border:none; border-radius:10px; font-size:14px; font-weight:600; cursor:pointer; transition:transform .2s,box-shadow .2s; }
.submit-btn:hover { transform:translateY(-2px); box-shadow:0 8px 20px rgba(255,0,170,.3); }
.empty-state { text-align:center; padding:50px 20px; color:#6b7280; }
.empty-state div { font-size:48px; margin-bottom:15px; }
</style>

<div class="page-container">
    <div class="page-header">
        <div>
            <h1 class="page-title">📢 Announcements</h1>
            <p class="page-subtitle">Community announcements and advisories</p>
        </div>
    </div>

    <div class="content-grid">
        <!-- Announcements list -->
        <div>
            <div class="section-card">
                <h3 class="section-title">Latest Announcements (<?= count($announcements) ?>)</h3>

                <?php if (!empty($announcements)): ?>
                    <?php foreach ($announcements as $row):
                        $cat = $row['category'] ?? 'event_reminder';
                        $cl  = $cat_labels[$cat] ?? $cat_labels['event_reminder'];
                    ?>
                    <div class="ann-item">
                        <span class="cat-badge" style="background:<?= $cl['bg'] ?>;color:<?= $cl['color'] ?>;">
                            <?= $cl['label'] ?>
                        </span>
                        <p class="ann-title"><?= htmlspecialchars($row['title']) ?></p>
                        <p class="ann-body"><?= nl2br(htmlspecialchars($row['message'])) ?></p>
                        <div class="ann-meta">
                            Posted by <strong><?= htmlspecialchars($row['name'] ?? 'Unknown') ?></strong>
                            &bull; <?= date('M d, Y h:i A', strtotime($row['created_at'])) ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <div>📢</div>
                        <p>No announcements yet.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Create form (admin/imam only) -->
        <?php if ($role === 'admin' || $role === 'imam'): ?>
        <div>
            <div class="section-card">
                <h3 class="section-title">📝 Post Announcement</h3>
                <form method="POST" action="../actions/add_announcement_action.php">
                    <?= csrf_field() ?>
                    <div class="form-group">
                        <label>Category</label>
                        <select name="category">
                            <option value="prayer_schedule">🕌 Prayer Schedule</option>
                            <option value="event_reminder" selected>📅 Event Reminder</option>
                            <option value="charity_drive">💝 Charity Drive</option>
                            <option value="community_advisory">📋 Community Advisory</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Title</label>
                        <input type="text" name="title" placeholder="Announcement title" required>
                    </div>
                    <div class="form-group">
                        <label>Message</label>
                        <textarea name="message" placeholder="Write your announcement here..." required></textarea>
                    </div>
                    <button type="submit" class="submit-btn">Post Announcement</button>
                </form>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php include "../includes/footer.php"; ?>
