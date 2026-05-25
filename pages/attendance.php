<?php
session_start();
include "../config/database.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id  = (int) $_SESSION['user_id'];
$role     = $_SESSION['role'];

$stmt = $conn->prepare("SELECT community_id FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$u = $stmt->get_result()->fetch_assoc();
$stmt->close();
$community_id = (int) $u['community_id'];

// Fetch community events for filter
$stmt = $conn->prepare("
    SELECT id, title, event_date FROM events
    WHERE (community_id = ? OR community_id IS NULL)
    ORDER BY event_date DESC
    LIMIT 50
");
$stmt->bind_param("i", $community_id);
$stmt->execute();
$eventsForFilter = [];
$eventsRes = $stmt->get_result();
while ($r = $eventsRes->fetch_assoc()) {
    $eventsForFilter[] = $r;
}
$stmt->close();

$selected_event_id = isset($_GET['event_id']) ? (int) $_GET['event_id'] : 0;

// Fetch attendance records
$attendance = [];
if ($selected_event_id > 0) {
    $stmt = $conn->prepare("
        SELECT a.*, e.title AS event_title
        FROM attendance a
        JOIN events e ON a.event_id = e.id
        WHERE a.event_id = ?
        ORDER BY a.created_at DESC
    ");
    $stmt->bind_param("i", $selected_event_id);
    $stmt->execute();
    $attRes = $stmt->get_result();
    while ($r = $attRes->fetch_assoc()) {
        $attendance[] = $r;
    }
    $stmt->close();
    $event_title = !empty($attendance) ? $attendance[0]['event_title'] : 'Selected Event';
} else {
    $event_title = 'All Events';
}

$present_count = count(array_filter($attendance, fn($r) => $r['status'] === 'present'));
$absent_count  = count(array_filter($attendance, fn($r) => $r['status'] === 'absent'));

include "../includes/header.php";
include "../includes/sidebar.php";
?>
<style>
.page-container { margin-left: 260px; padding: 90px 30px 30px; background: #121212; min-height: 100vh; }
.page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
.page-title { font-size: 24px; font-weight: 700; color: #fff; margin: 0; }
.page-subtitle { font-size: 14px; color: #9ca3af; margin: 5px 0 0 0; }
.content-grid { display: grid; grid-template-columns: 1fr 340px; gap: 25px; }
@media(max-width:1100px){ .content-grid { grid-template-columns: 1fr; } }
.section-card { background: #1e1e1e; border-radius: 16px; padding: 25px; box-shadow: 0 2px 10px rgba(0,0,0,.3); margin-bottom: 20px; }
.section-title { font-size: 18px; font-weight: 600; color: #fff; margin: 0 0 20px 0; }
.stats-row { display: grid; grid-template-columns: repeat(3,1fr); gap: 15px; margin-bottom: 25px; }
.stat-card { background: #1e1e1e; border-radius: 12px; padding: 20px; text-align: center; box-shadow: 0 2px 8px rgba(0,0,0,.3); }
.stat-number { font-size: 28px; font-weight: 700; color: #fff; margin: 0; }
.stat-label { font-size: 12px; color: #9ca3af; margin: 5px 0 0 0; }
.stat-card.present .stat-number { color: #4ade80; }
.stat-card.absent .stat-number { color: #f87171; }
table { width: 100%; border-collapse: collapse; }
th { text-align: left; padding: 12px 15px; font-size: 12px; font-weight: 600; color: #6b7280; text-transform: uppercase; border-bottom: 1px solid #2a2a2a; }
td { padding: 13px 15px; border-bottom: 1px solid #2a2a2a; font-size: 14px; color: #d1d5db; }
tr:hover td { background: #252525; }
.badge { font-size: 11px; padding: 4px 10px; border-radius: 20px; font-weight: 600; }
.badge-present { background: rgba(22,163,74,.2); color: #4ade80; }
.badge-absent  { background: rgba(220,38,38,.2); color: #f87171; }
.form-group { margin-bottom: 14px; }
.form-group label { display: block; font-size: 13px; font-weight: 600; color: #9ca3af; margin-bottom: 5px; }
.form-group input, .form-group select {
    width: 100%; padding: 11px 14px; border: 1.5px solid #333; background: #242424; color: #fff;
    border-radius: 10px; font-size: 14px; box-sizing: border-box; transition: border-color .2s; font-family: inherit;
}
.form-group input::placeholder { color: #555; }
.form-group select option { background: #242424; color: #fff; }
.form-group input:focus, .form-group select:focus { outline: none; border-color: #ff00aa; background: #2a2a2a; }
.submit-btn {
    width: 100%; padding: 13px; background: linear-gradient(135deg,#ff4dc4,#ff00aa);
    color: white; border: none; border-radius: 10px; font-size: 14px;
    font-weight: 600; cursor: pointer; transition: transform .2s, box-shadow .2s;
}
.submit-btn:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(255,0,170,.3); }
.filter-bar { display: flex; gap: 10px; align-items: center; margin-bottom: 20px; }
.filter-bar select { padding: 10px 14px; border: 1.5px solid #333; background: #242424; color: #fff; border-radius: 10px; font-size: 14px; }
.print-btn { margin-left: auto; padding: 10px 20px; background: #2a2a2a; color: #e5e7eb; border: 1px solid #333; border-radius: 10px; cursor: pointer; font-size: 13px; font-weight: 600; }
.print-btn:hover { background: #333; }
.empty-state { text-align: center; padding: 50px 20px; color: #6b7280; }
.empty-state div { font-size: 48px; margin-bottom: 15px; }
@media print {
    .sidebar, .page-header .filter-bar .print-btn, header, .section-card:last-child { display: none !important; }
    .page-container { margin-left: 0 !important; padding: 20px !important; }
}
</style>

<div class="page-container">
    <div class="page-header">
        <div>
            <h1 class="page-title">✅ Attendance Tracking</h1>
            <p class="page-subtitle">Record and monitor event participation</p>
        </div>
    </div>

    <!-- Stats -->
    <div class="stats-row">
        <div class="stat-card">
            <p class="stat-number"><?= count($attendance) ?></p>
            <p class="stat-label">Total Records</p>
        </div>
        <div class="stat-card present">
            <p class="stat-number"><?= $present_count ?></p>
            <p class="stat-label">Present</p>
        </div>
        <div class="stat-card absent">
            <p class="stat-number"><?= $absent_count ?></p>
            <p class="stat-label">Absent</p>
        </div>
    </div>

    <div class="content-grid">
        <!-- Left: Attendance List -->
        <div>
            <div class="section-card">
                <!-- Filter bar -->
                <form method="GET" action="attendance.php">
                    <div class="filter-bar">
                        <select name="event_id" onchange="this.form.submit()">
                            <option value="0">-- Select Event --</option>
                            <?php foreach ($eventsForFilter as $ev): ?>
                            <option value="<?= $ev['id'] ?>" <?= $selected_event_id == $ev['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($ev['title']) ?> (<?= $ev['event_date'] ?>)
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <button type="button" class="print-btn" onclick="window.print()">🖨 Print</button>
                    </div>
                </form>

                <h3 class="section-title">
                    <?= $selected_event_id > 0 ? htmlspecialchars($event_title) : 'Attendance Records' ?>
                </h3>

                <?php if (!empty($attendance)): ?>
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Status</th>
                            <th>Recorded At</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($attendance as $i => $row): ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td><?= htmlspecialchars($row['name']) ?></td>
                            <td>
                                <span class="badge badge-<?= $row['status'] ?>">
                                    <?= ucfirst($row['status']) ?>
                                </span>
                            </td>
                            <td><?= date('M d, Y h:i A', strtotime($row['created_at'])) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <div class="empty-state">
                    <div>📋</div>
                    <p>No attendance records<?= $selected_event_id > 0 ? ' for this event' : '. Select an event above.' ?></p>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Right: Add Attendance Form -->
        <?php if (in_array($role, ['admin', 'imam', 'leader'])): ?>
        <div>
            <div class="section-card">
                <h3 class="section-title">➕ Record Attendance</h3>
                <form method="POST" action="../actions/add_attendance.php">
                    <?= csrf_field() ?>
                    <div class="form-group">
                        <label>Event</label>
                        <select name="event_id" required>
                            <option value="">-- Select Event --</option>
                            <?php foreach ($eventsForFilter as $ev): ?>
                            <option value="<?= $ev['id'] ?>" <?= $selected_event_id == $ev['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($ev['title']) ?> (<?= $ev['event_date'] ?>)
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Attendee Name</label>
                        <input type="text" name="attendee_name" placeholder="Full name" required>
                    </div>
                    <div class="form-group">
                        <label>Status</label>
                        <select name="att_status">
                            <option value="present">✅ Present</option>
                            <option value="absent">❌ Absent</option>
                        </select>
                    </div>
                    <button type="submit" class="submit-btn">Save Attendance</button>
                </form>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php include "../includes/footer.php"; ?>
