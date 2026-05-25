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

// Event counts
$total    = (int) $conn->query("SELECT COUNT(*) as c FROM events WHERE community_id=$community_id OR community_id IS NULL")->fetch_assoc()['c'];
$upcoming = (int) $conn->query("SELECT COUNT(*) as c FROM events WHERE (community_id=$community_id OR community_id IS NULL) AND status='upcoming'")->fetch_assoc()['c'];
$ongoing  = (int) $conn->query("SELECT COUNT(*) as c FROM events WHERE (community_id=$community_id OR community_id IS NULL) AND status='ongoing'")->fetch_assoc()['c'];
$completed= (int) $conn->query("SELECT COUNT(*) as c FROM events WHERE (community_id=$community_id OR community_id IS NULL) AND status='completed'")->fetch_assoc()['c'];

// Attendance counts
$total_attendance = (int) $conn->query("SELECT COUNT(*) as c FROM attendance WHERE community_id=$community_id")->fetch_assoc()['c'];
$present_count    = (int) $conn->query("SELECT COUNT(*) as c FROM attendance WHERE community_id=$community_id AND status='present'")->fetch_assoc()['c'];

// Donation summary
$cash_total = (float) $conn->query("SELECT COALESCE(SUM(amount),0) as s FROM donations WHERE community_id=$community_id AND donation_type='cash'")->fetch_assoc()['s'];
$food_count = (int)   $conn->query("SELECT COUNT(*) as c FROM donations WHERE community_id=$community_id AND donation_type='food'")->fetch_assoc()['c'];
$supply_count=(int)   $conn->query("SELECT COUNT(*) as c FROM donations WHERE community_id=$community_id AND donation_type='supplies'")->fetch_assoc()['c'];
$dist_count  =(int)   $conn->query("SELECT COUNT(*) as c FROM distributions WHERE community_id=$community_id")->fetch_assoc()['c'];

// Events list
$eventsRes = $conn->query("
    SELECT * FROM events
    WHERE community_id=$community_id OR community_id IS NULL
    ORDER BY event_date ASC
");
$eventsList = [];
while ($r = $eventsRes->fetch_assoc()) $eventsList[] = $r;

// Attendance list (latest 20)
$attRes = $conn->query("
    SELECT a.*, e.title AS event_title
    FROM attendance a
    JOIN events e ON a.event_id = e.id
    WHERE a.community_id = $community_id
    ORDER BY a.created_at DESC
    LIMIT 20
");
$attList = [];
while ($r = $attRes->fetch_assoc()) $attList[] = $r;

// Donations list
$donRes = $conn->query("SELECT * FROM donations WHERE community_id=$community_id ORDER BY created_at DESC");
$donList = [];
while ($r = $donRes->fetch_assoc()) $donList[] = $r;

include "../includes/header.php";
include "../includes/sidebar.php";
?>
<style>
.page-container { margin-left:260px; padding:90px 30px 40px; background:#121212; min-height:100vh; color:#fff; }
.page-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:26px; }
.page-title  { font-size:26px; font-weight:800; color:#fff; margin:0; }
.page-subtitle { font-size:14px; color:rgba(255,255,255,.4); margin:5px 0 0; }
.print-btn { padding:12px 24px; background:linear-gradient(135deg,#ff4dc4,#ff00aa); color:#fff; border:none; border-radius:12px; font-size:14px; font-weight:600; cursor:pointer; box-shadow:0 4px 15px rgba(255,0,170,.25); transition:all .2s; }
.print-btn:hover { opacity:.9; transform:translateY(-1px); }
.section-card { background:#1e1e1e; border-radius:16px; padding:25px; border:1px solid rgba(255,255,255,.05); margin-bottom:25px; }
.section-title { font-size:17px; font-weight:700; color:#fff; margin:0 0 18px; padding-bottom:12px; border-bottom:1px solid rgba(255,255,255,.07); }
.stats-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:15px; margin-bottom:25px; }
.stat-box { background:#1e1e1e; border-radius:14px; padding:20px; text-align:center; border:1px solid rgba(255,255,255,.05); }
.stat-num   { font-size:26px; font-weight:800; color:#fff; margin:0; }
.stat-lbl   { font-size:12px; color:rgba(255,255,255,.4); margin:6px 0 0; }
table { width:100%; border-collapse:collapse; font-size:13px; }
th { text-align:left; padding:10px 12px; background:rgba(255,255,255,.04); font-weight:600; color:rgba(255,255,255,.45); text-transform:uppercase; font-size:11px; letter-spacing:.5px; border-bottom:1px solid rgba(255,255,255,.07); }
td { padding:11px 12px; border-bottom:1px solid rgba(255,255,255,.05); color:#fff; }
tr:last-child td { border-bottom:none; }
tr:hover td { background:rgba(255,255,255,.03); }
.badge { font-size:10px; padding:3px 9px; border-radius:10px; font-weight:600; }
.badge-upcoming  { background:rgba(251,191,36,.15); color:#fbbf24; }
.badge-ongoing   { background:rgba(96,165,250,.15); color:#60a5fa; }
.badge-completed { background:rgba(74,222,128,.15); color:#4ade80; }
.badge-present   { background:rgba(74,222,128,.15); color:#4ade80; }
.badge-absent    { background:rgba(248,113,113,.15); color:#f87171; }

@media print {
    .sidebar, header, .print-btn, .page-header button { display:none !important; }
    .page-container { margin-left:0 !important; padding:20px !important; background:#fff !important; color:#000 !important; }
    .section-card { background:#fff !important; box-shadow:none !important; border:1px solid #ddd !important; margin-bottom:15px; break-inside:avoid; }
    .stat-box { background:#f9f9f9 !important; border:1px solid #ddd !important; }
    .section-title { color:#000 !important; border-color:#ddd !important; }
    .stat-num, .stat-lbl, .page-title, .page-subtitle, td { color:#000 !important; }
    th { color:#555 !important; background:#f4f4f4 !important; }
    td { border-color:#eee !important; }
    body { font-size:12px; background:#fff !important; }
    .print-header { display:block !important; }
}
.print-header { display:none; text-align:center; margin-bottom:20px; }
.print-header h1 { font-size:18px; color:#1a1a2e; }
.print-header p  { font-size:12px; color:#6c757d; }
</style>

<div class="page-container">
    <!-- Print header (only visible when printing) -->
    <div class="print-header">
        <h1>MCAD — Community Program Report</h1>
        <p>Generated: <?= date('F d, Y h:i A') ?></p>
        <hr>
    </div>

    <div class="page-header">
        <div>
            <h1 class="page-title">📊 Reports & Analytics</h1>
            <p class="page-subtitle">Generated: <?= date('F d, Y') ?></p>
        </div>
        <div style="display:flex;gap:10px;">
            <a href="export_report.php" class="print-btn" style="text-decoration:none;display:inline-flex;align-items:center;gap:6px;">📄 Export PDF</a>
            <button class="print-btn" onclick="window.print()" style="background:rgba(255,255,255,.1);box-shadow:none;border:1px solid rgba(255,255,255,.2);">🖨 Quick Print</button>
        </div>
    </div>

    <!-- Event Summary -->
    <div class="stats-grid">
        <div class="stat-box">
            <p class="stat-num"><?= $total ?></p>
            <p class="stat-lbl">Total Events</p>
        </div>
        <div class="stat-box">
            <p class="stat-num" style="color:#d97706;"><?= $upcoming ?></p>
            <p class="stat-lbl">Upcoming</p>
        </div>
        <div class="stat-box">
            <p class="stat-num" style="color:#2563eb;"><?= $ongoing ?></p>
            <p class="stat-lbl">Ongoing</p>
        </div>
        <div class="stat-box">
            <p class="stat-num" style="color:#16a34a;"><?= $completed ?></p>
            <p class="stat-lbl">Completed</p>
        </div>
    </div>

    <!-- Attendance Summary -->
    <div class="stats-grid">
        <div class="stat-box">
            <p class="stat-num"><?= $total_attendance ?></p>
            <p class="stat-lbl">Attendance Records</p>
        </div>
        <div class="stat-box">
            <p class="stat-num" style="color:#16a34a;"><?= $present_count ?></p>
            <p class="stat-lbl">Present</p>
        </div>
        <div class="stat-box">
            <p class="stat-num" style="color:#dc2626;"><?= $total_attendance - $present_count ?></p>
            <p class="stat-lbl">Absent</p>
        </div>
        <div class="stat-box">
            <p class="stat-num" style="color:#ff00aa;">₱<?= number_format($cash_total, 2) ?></p>
            <p class="stat-lbl">Total Cash Donations</p>
        </div>
    </div>

    <!-- Event Schedule Report -->
    <div class="section-card">
        <h3 class="section-title">📅 Ramadan Event Schedule</h3>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Title</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Venue</th>
                    <th>Type</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($eventsList as $i => $e): ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td><?= htmlspecialchars($e['title']) ?></td>
                    <td><?= date('M d, Y', strtotime($e['event_date'])) ?></td>
                    <td><?= $e['event_time'] ?></td>
                    <td><?= htmlspecialchars($e['venue']) ?></td>
                    <td><?= ucfirst($e['event_type']) ?></td>
                    <td><span class="badge badge-<?= $e['status'] ?>"><?= ucfirst($e['status']) ?></span></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Attendance Summary Report -->
    <div class="section-card">
        <h3 class="section-title">✅ Attendance Summary (Latest 20 Records)</h3>
        <?php if (!empty($attList)): ?>
        <table>
            <thead>
                <tr><th>#</th><th>Event</th><th>Attendee</th><th>Status</th><th>Recorded</th></tr>
            </thead>
            <tbody>
                <?php foreach ($attList as $i => $a): ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td><?= htmlspecialchars($a['event_title']) ?></td>
                    <td><?= htmlspecialchars($a['name']) ?></td>
                    <td><span class="badge badge-<?= $a['status'] ?>"><?= ucfirst($a['status']) ?></span></td>
                    <td><?= date('M d, Y', strtotime($a['created_at'])) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
            <p style="color:rgba(255,255,255,.25);text-align:center;padding:20px;">No attendance records yet.</p>
        <?php endif; ?>
    </div>

    <!-- Donation Report -->
    <div class="section-card">
        <h3 class="section-title">💰 Donation Records</h3>
        <?php if (!empty($donList)): ?>
        <table>
            <thead>
                <tr><th>#</th><th>Donor</th><th>Type</th><th>Amount / Qty</th><th>Remarks</th><th>Date</th></tr>
            </thead>
            <tbody>
                <?php foreach ($donList as $i => $d): ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td><?= htmlspecialchars($d['donor_name'] ?: 'Anonymous') ?></td>
                    <td><?= ucfirst($d['donation_type']) ?></td>
                    <td>
                        <?php if ($d['donation_type'] === 'cash'): ?>
                            ₱<?= number_format((float)$d['amount'], 2) ?>
                        <?php else: ?>
                            <?= $d['quantity'] ?> unit(s)
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($d['remarks'] ?? '—') ?></td>
                    <td><?= date('M d, Y', strtotime($d['created_at'])) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
            <p style="color:rgba(255,255,255,.25);text-align:center;padding:20px;">No donation records yet.</p>
        <?php endif; ?>
    </div>
</div>
<?php include "../includes/footer.php"; ?>
