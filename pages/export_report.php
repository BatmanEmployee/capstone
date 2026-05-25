<?php
session_start();
include "../config/database.php";
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$user_id = (int) $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT community_id FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$u = $stmt->get_result()->fetch_assoc();
$stmt->close();
$community_id = (int) $u['community_id'];

// Counters
$total     = (int) $conn->query("SELECT COUNT(*) c FROM events WHERE community_id=$community_id OR community_id IS NULL")->fetch_assoc()['c'];
$upcoming  = (int) $conn->query("SELECT COUNT(*) c FROM events WHERE (community_id=$community_id OR community_id IS NULL) AND status='upcoming'")->fetch_assoc()['c'];
$ongoing   = (int) $conn->query("SELECT COUNT(*) c FROM events WHERE (community_id=$community_id OR community_id IS NULL) AND status='ongoing'")->fetch_assoc()['c'];
$completed = (int) $conn->query("SELECT COUNT(*) c FROM events WHERE (community_id=$community_id OR community_id IS NULL) AND status='completed'")->fetch_assoc()['c'];

$total_att  = (int) $conn->query("SELECT COUNT(*) c FROM attendance WHERE community_id=$community_id")->fetch_assoc()['c'];
$present_c  = (int) $conn->query("SELECT COUNT(*) c FROM attendance WHERE community_id=$community_id AND status='present'")->fetch_assoc()['c'];
$cash_total = (float) $conn->query("SELECT COALESCE(SUM(amount),0) s FROM donations WHERE community_id=$community_id AND donation_type='cash'")->fetch_assoc()['s'];
$dist_count = (int) $conn->query("SELECT COUNT(*) c FROM distributions WHERE community_id=$community_id")->fetch_assoc()['c'];

// Data
$eventsRes = $conn->query("SELECT * FROM events WHERE community_id=$community_id OR community_id IS NULL ORDER BY event_date ASC");
$eventsList = [];
while ($r = $eventsRes->fetch_assoc()) $eventsList[] = $r;

$attRes = $conn->query("SELECT a.*, e.title AS event_title FROM attendance a JOIN events e ON a.event_id=e.id WHERE a.community_id=$community_id ORDER BY a.created_at DESC LIMIT 30");
$attList = [];
while ($r = $attRes->fetch_assoc()) $attList[] = $r;

$donRes = $conn->query("SELECT * FROM donations WHERE community_id=$community_id ORDER BY created_at DESC");
$donList = [];
while ($r = $donRes->fetch_assoc()) $donList[] = $r;

$community_name = $conn->query("SELECT name FROM communities WHERE id=$community_id")->fetch_assoc()['name'] ?? 'All Communities';
?><!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>MCAD Community Report — <?= date('F Y') ?></title>
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }
@page { size: A4; margin: 15mm 15mm 20mm; }

body {
    font-family: 'Segoe UI', Arial, sans-serif;
    font-size: 11px;
    color: #1a1a2e;
    background: #fff;
}

/* Print toolbar (hidden when printing) */
.toolbar {
    position: fixed; top: 0; left: 0; right: 0;
    background: #1a1a2e; padding: 12px 24px;
    display: flex; justify-content: space-between; align-items: center;
    z-index: 100; print-color-adjust: exact;
}
.toolbar h2 { color: #fff; font-size: 15px; }
.toolbar-btns { display: flex; gap: 10px; }
.btn-pdf {
    padding: 9px 22px;
    background: linear-gradient(135deg,#ff4dc4,#ff00aa);
    color: #fff; border: none; border-radius: 8px;
    font-size: 13px; font-weight: 700; cursor: pointer;
}
.btn-back {
    padding: 9px 18px;
    background: rgba(255,255,255,.15); color: #fff;
    border: 1px solid rgba(255,255,255,.3); border-radius: 8px;
    font-size: 13px; cursor: pointer; text-decoration: none;
    display: flex; align-items: center;
}
@media print { .toolbar { display: none !important; } }

/* Report body */
.report-body { padding: 20px 24px; margin-top: 50px; }
@media print { .report-body { margin-top: 0; padding: 0; } }

/* Cover header */
.cover {
    text-align: center; padding: 24px 0 20px;
    border-bottom: 2px solid #ff00aa; margin-bottom: 22px;
}
.cover-logo { font-size: 28px; margin-bottom: 6px; }
.cover h1 { font-size: 18px; font-weight: 800; color: #1a1a2e; margin: 4px 0; }
.cover .sub { font-size: 11px; color: #6c757d; margin-top: 2px; }
.cover .meta { margin-top: 10px; font-size: 11px; color: #374151; }
.cover .meta span { margin: 0 10px; }

/* Summary stats */
.stats-grid {
    display: grid; grid-template-columns: repeat(4, 1fr);
    gap: 10px; margin-bottom: 22px;
}
.stat-box {
    border: 1px solid #e2e8f0; border-radius: 8px;
    padding: 12px; text-align: center;
}
.stat-num { font-size: 20px; font-weight: 800; margin-bottom: 3px; }
.stat-lbl { font-size: 10px; color: #6c757d; }

/* Section */
.section { margin-bottom: 24px; break-inside: avoid; }
.section-header {
    background: linear-gradient(135deg,#ff4dc4,#ff00aa);
    color: #fff; padding: 8px 14px; border-radius: 6px 6px 0 0;
    font-size: 12px; font-weight: 700;
}
.section-sub-header {
    display: grid; grid-template-columns: repeat(4,1fr);
    gap: 8px; background: #f8fafc; padding: 10px 14px;
    border-left: 1px solid #e2e8f0; border-right: 1px solid #e2e8f0;
    margin-bottom: 0;
}
.sub-stat { text-align: center; }
.sub-num { font-size: 15px; font-weight: 700; }
.sub-lbl { font-size: 9px; color: #6c757d; }

/* Tables */
table {
    width: 100%; border-collapse: collapse;
    border: 1px solid #e2e8f0; border-radius: 0 0 6px 6px; overflow: hidden;
}
th {
    background: #f8fafc; padding: 8px 10px;
    font-size: 10px; font-weight: 700; color: #374151;
    text-align: left; text-transform: uppercase; letter-spacing: .4px;
    border-bottom: 1px solid #e2e8f0;
}
td {
    padding: 8px 10px; border-bottom: 1px solid #f3f4f6;
    font-size: 11px; color: #1a1a2e;
}
tr:last-child td { border-bottom: none; }
tr:nth-child(even) td { background: #fafafa; }

.badge {
    font-size: 9px; padding: 2px 7px; border-radius: 8px;
    font-weight: 700; display: inline-block;
}
.badge-upcoming  { background: #fef3c7; color: #d97706; }
.badge-ongoing   { background: #dbeafe; color: #2563eb; }
.badge-completed { background: #dcfce7; color: #16a34a; }
.badge-present   { background: #dcfce7; color: #16a34a; }
.badge-absent    { background: #fee2e2; color: #dc2626; }

.empty-row td { text-align: center; color: #9ca3af; padding: 18px; }

/* Footer */
.report-footer {
    margin-top: 30px; padding-top: 12px;
    border-top: 1px solid #e2e8f0; text-align: center;
    font-size: 10px; color: #9ca3af;
}
</style>
</head>
<body>

<!-- Toolbar (screen only) -->
<div class="toolbar">
    <h2>📊 MCAD Community Report — Preview</h2>
    <div class="toolbar-btns">
        <a href="reports.php" class="btn-back">← Back</a>
        <button class="btn-pdf" onclick="window.print()">🖨 Download PDF</button>
    </div>
</div>

<!-- Report Content -->
<div class="report-body">

    <!-- Cover -->
    <div class="cover">
        <div class="cover-logo">🕌</div>
        <h1>MCAD Community Program Report</h1>
        <p class="sub">City Mayor's Office — Muslim Concerns and Affairs Division, General Santos City</p>
        <div class="meta">
            <span>📍 Community: <strong><?= htmlspecialchars($community_name) ?></strong></span>
            <span>📅 Generated: <strong><?= date('F d, Y') ?></strong></span>
            <span>🕐 <?= date('h:i A') ?></span>
        </div>
    </div>

    <!-- Executive Summary -->
    <div class="stats-grid">
        <div class="stat-box">
            <p class="stat-num" style="color:#ff00aa;"><?= $total ?></p>
            <p class="stat-lbl">Total Events</p>
        </div>
        <div class="stat-box">
            <p class="stat-num" style="color:#16a34a;"><?= $total_att ?></p>
            <p class="stat-lbl">Attendance Records</p>
        </div>
        <div class="stat-box">
            <p class="stat-num" style="color:#0284c7;">₱<?= number_format($cash_total, 2) ?></p>
            <p class="stat-lbl">Cash Donations</p>
        </div>
        <div class="stat-box">
            <p class="stat-num" style="color:#7c3aed;"><?= $dist_count ?></p>
            <p class="stat-lbl">Distributions</p>
        </div>
    </div>

    <!-- Event Schedule -->
    <div class="section">
        <div class="section-header">📅 Ramadan Event Schedule (<?= count($eventsList) ?> events)</div>
        <div class="section-sub-header">
            <div class="sub-stat"><span class="sub-num"><?= $total ?></span><p class="sub-lbl">Total</p></div>
            <div class="sub-stat"><span class="sub-num" style="color:#d97706;"><?= $upcoming ?></span><p class="sub-lbl">Upcoming</p></div>
            <div class="sub-stat"><span class="sub-num" style="color:#2563eb;"><?= $ongoing ?></span><p class="sub-lbl">Ongoing</p></div>
            <div class="sub-stat"><span class="sub-num" style="color:#16a34a;"><?= $completed ?></span><p class="sub-lbl">Completed</p></div>
        </div>
        <table>
            <thead>
                <tr>
                    <th>#</th><th>Event Title</th><th>Date</th><th>Time</th><th>Venue</th><th>Type</th><th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($eventsList)): ?>
                <?php foreach ($eventsList as $i => $e): ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td><?= htmlspecialchars($e['title']) ?></td>
                    <td><?= date('M d, Y', strtotime($e['event_date'])) ?></td>
                    <td><?= $e['event_time'] ? date('g:i A', strtotime($e['event_time'])) : '—' ?></td>
                    <td><?= htmlspecialchars($e['venue'] ?? '—') ?></td>
                    <td><?= ucfirst($e['event_type'] ?? 'personal') ?></td>
                    <td><span class="badge badge-<?= $e['status'] ?>"><?= ucfirst($e['status']) ?></span></td>
                </tr>
                <?php endforeach; ?>
                <?php else: ?>
                <tr class="empty-row"><td colspan="7">No events recorded.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Attendance -->
    <div class="section">
        <div class="section-header">✅ Attendance Summary (Latest 30 Records)</div>
        <div class="section-sub-header">
            <div class="sub-stat"><span class="sub-num"><?= $total_att ?></span><p class="sub-lbl">Total Records</p></div>
            <div class="sub-stat"><span class="sub-num" style="color:#16a34a;"><?= $present_c ?></span><p class="sub-lbl">Present</p></div>
            <div class="sub-stat"><span class="sub-num" style="color:#dc2626;"><?= $total_att - $present_c ?></span><p class="sub-lbl">Absent</p></div>
            <div class="sub-stat"><span class="sub-num" style="color:#0284c7;"><?= $total_att > 0 ? round(($present_c/$total_att)*100) : 0 ?>%</span><p class="sub-lbl">Attendance Rate</p></div>
        </div>
        <table>
            <thead>
                <tr><th>#</th><th>Event</th><th>Attendee Name</th><th>Status</th><th>Date Recorded</th></tr>
            </thead>
            <tbody>
                <?php if (!empty($attList)): ?>
                <?php foreach ($attList as $i => $a): ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td><?= htmlspecialchars($a['event_title']) ?></td>
                    <td><?= htmlspecialchars($a['name']) ?></td>
                    <td><span class="badge badge-<?= $a['status'] ?>"><?= ucfirst($a['status']) ?></span></td>
                    <td><?= date('M d, Y', strtotime($a['created_at'])) ?></td>
                </tr>
                <?php endforeach; ?>
                <?php else: ?>
                <tr class="empty-row"><td colspan="5">No attendance records yet.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Donations -->
    <div class="section">
        <div class="section-header">💰 Donation &amp; Distribution Records</div>
        <table>
            <thead>
                <tr><th>#</th><th>Donor</th><th>Type</th><th>Amount / Qty</th><th>Remarks</th><th>Date</th></tr>
            </thead>
            <tbody>
                <?php if (!empty($donList)): ?>
                <?php foreach ($donList as $i => $d): ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td><?= htmlspecialchars($d['donor_name'] ?: 'Anonymous') ?></td>
                    <td><?= ucfirst($d['donation_type']) ?></td>
                    <td><?= $d['donation_type'] === 'cash' ? '₱' . number_format((float)$d['amount'],2) : $d['quantity'] . ' unit(s)' ?></td>
                    <td><?= htmlspecialchars($d['remarks'] ?? '—') ?></td>
                    <td><?= date('M d, Y', strtotime($d['created_at'])) ?></td>
                </tr>
                <?php endforeach; ?>
                <?php else: ?>
                <tr class="empty-row"><td colspan="6">No donation records yet.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Footer -->
    <div class="report-footer">
        <p>This report was generated by the MCAD Web-Based Event Management System &bull; <?= date('F d, Y \a\t h:i A') ?></p>
        <p>City Mayor's Office — Muslim Concerns and Affairs Division, General Santos City &bull; CONFIDENTIAL</p>
    </div>

</div>
</body>
</html>
