<?php
session_start();
include "../config/database.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: dashboard.php");
    exit();
}

// ── Filters ──
$filter_status  = $_GET['status']  ?? '';
$filter_service = $_GET['service'] ?? '';

$where = [];
if (in_array($filter_status, ['pending','approved','rejected','completed'])) {
    $where[] = "status = '" . $conn->real_escape_string($filter_status) . "'";
}
$allowed_svc = ['islamic_marriage','halal_certification','burial_assistance','scholarship'];
if (in_array($filter_service, $allowed_svc)) {
    $where[] = "service_type = '" . $conn->real_escape_string($filter_service) . "'";
}
$whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$appointments = [];
$res = $conn->query("
    SELECT * FROM appointments
    $whereSQL
    ORDER BY
        CASE status WHEN 'pending' THEN 0 WHEN 'approved' THEN 1
                    WHEN 'completed' THEN 2 ELSE 3 END,
        preferred_date ASC,
        created_at DESC
");
while ($r = $res->fetch_assoc()) $appointments[] = $r;

// Count summary
$pending   = (int) $conn->query("SELECT COUNT(*) c FROM appointments WHERE status='pending'")->fetch_assoc()['c'];
$approved  = (int) $conn->query("SELECT COUNT(*) c FROM appointments WHERE status='approved'")->fetch_assoc()['c'];
$completed = (int) $conn->query("SELECT COUNT(*) c FROM appointments WHERE status='completed'")->fetch_assoc()['c'];
$rejected  = (int) $conn->query("SELECT COUNT(*) c FROM appointments WHERE status='rejected'")->fetch_assoc()['c'];

$service_labels = [
    'islamic_marriage'   => 'Islamic Marriage & Certification',
    'halal_certification'=> 'Halal Certification',
    'burial_assistance'  => 'Burial Assistance',
    'scholarship'        => 'Scholarship & Financial Aid',
];

$time_labels = [
    '08:00:00'=>'8:00 AM','09:00:00'=>'9:00 AM','10:00:00'=>'10:00 AM','11:00:00'=>'11:00 AM',
    '13:00:00'=>'1:00 PM','14:00:00'=>'2:00 PM','15:00:00'=>'3:00 PM','16:00:00'=>'4:00 PM',
];

include "../includes/header.php";
include "../includes/sidebar.php";
?>
<style>
.page-container { margin-left:260px; padding:90px 30px 30px; background:#f8fafc; min-height:100vh; }
.page-title   { font-size:24px; font-weight:700; color:#1a1a2e; margin:0; }
.page-subtitle{ font-size:14px; color:#6c757d; margin:5px 0 25px; }

/* Stats row */
.stats-row { display:grid; grid-template-columns:repeat(4,1fr); gap:15px; margin-bottom:25px; }
.stat-card { background:white; border-radius:12px; padding:20px; text-align:center; box-shadow:0 2px 8px rgba(0,0,0,.05); }
.stat-num  { font-size:28px; font-weight:700; margin:0; }
.stat-lbl  { font-size:12px; color:#6c757d; margin:4px 0 0; }

/* Filter bar */
.filter-bar { display:flex; gap:10px; flex-wrap:wrap; margin-bottom:20px; align-items:center; }
.filter-bar select {
    padding:10px 14px; border:1.5px solid #e2e8f0; border-radius:10px;
    font-size:14px; background:white; cursor:pointer; color:#374151;
}
.filter-bar select:focus { outline:none; border-color:#ff00aa; }
.filter-btn {
    padding:10px 20px; background:linear-gradient(135deg,#ff4dc4,#ff00aa);
    color:white; border:none; border-radius:10px; font-size:14px; font-weight:600; cursor:pointer;
}
.reset-link { font-size:13px; color:#6c757d; text-decoration:none; }
.reset-link:hover { color:#ff00aa; }

/* Table */
.section-card { background:white; border-radius:16px; padding:25px; box-shadow:0 2px 10px rgba(0,0,0,.04); }
table { width:100%; border-collapse:collapse; font-size:14px; }
th { text-align:left; padding:12px 14px; font-size:11px; font-weight:700; color:#6c757d;
     text-transform:uppercase; letter-spacing:.5px; border-bottom:2px solid #e2e8f0; white-space:nowrap; }
td { padding:14px; border-bottom:1px solid #f3f4f6; vertical-align:top; }
tr:last-child td { border-bottom:none; }
tr:hover td { background:#fafafa; }

/* Status badges */
.badge { display:inline-block; font-size:11px; padding:4px 11px; border-radius:20px; font-weight:700; white-space:nowrap; }
.badge-pending   { background:#fef3c7; color:#d97706; }
.badge-approved  { background:#dcfce7; color:#16a34a; }
.badge-rejected  { background:#fee2e2; color:#dc2626; }
.badge-completed { background:#dbeafe; color:#2563eb; }

/* Service label */
.svc-tag { font-size:12px; color:#6c757d; background:#f3f4f6; padding:3px 8px; border-radius:6px; }

/* Action buttons */
.act-btn { font-size:12px; padding:7px 14px; border:none; border-radius:8px; cursor:pointer;
           font-weight:600; transition:all .2s; white-space:nowrap; }
.btn-approve  { background:#dcfce7; color:#16a34a; }
.btn-approve:hover  { background:#16a34a; color:white; }
.btn-reject   { background:#fee2e2; color:#dc2626; }
.btn-reject:hover   { background:#dc2626; color:white; }
.btn-complete { background:#dbeafe; color:#2563eb; }
.btn-complete:hover { background:#2563eb; color:white; }

/* Remarks inline */
.remarks-text { font-size:12px; color:#6c757d; margin-top:5px; }

/* Modal overlay */
.modal-overlay {
    display:none; position:fixed; inset:0; background:rgba(0,0,0,.5);
    z-index:9000; align-items:center; justify-content:center;
}
.modal-overlay.open { display:flex; }
.modal {
    background:white; border-radius:16px; padding:32px; width:100%; max-width:480px;
    box-shadow:0 20px 60px rgba(0,0,0,.2); margin:20px;
}
.modal h3 { font-size:18px; font-weight:700; color:#1a1a2e; margin:0 0 6px; }
.modal p  { font-size:14px; color:#6c757d; margin:0 0 20px; }
.modal label { display:block; font-size:13px; font-weight:600; color:#374151; margin-bottom:6px; }
.modal textarea {
    width:100%; padding:12px 14px; border:1.5px solid #e2e8f0; border-radius:10px;
    font-size:14px; min-height:90px; resize:vertical; box-sizing:border-box;
}
.modal textarea:focus { outline:none; border-color:#ff00aa; }
.modal-btns { display:flex; gap:10px; margin-top:16px; }
.modal-submit { flex:1; padding:12px; border:none; border-radius:10px; font-size:14px;
                font-weight:700; cursor:pointer; color:white; }
.modal-cancel { padding:12px 20px; border:1.5px solid #e2e8f0; background:white; color:#374151;
                border-radius:10px; font-size:14px; cursor:pointer; }
.empty-state { text-align:center; padding:60px 20px; color:#6c757d; }
.empty-state div { font-size:48px; margin-bottom:15px; }

@media(max-width:900px){
    .stats-row { grid-template-columns:repeat(2,1fr); }
    table { font-size:13px; }
}
</style>

<div class="page-container">
    <h1 class="page-title">📋 Appointments</h1>
    <p class="page-subtitle">Review and manage public appointment requests</p>

    <?php if (isset($_GET['msg'])): ?>
    <div style="padding:12px 16px;border-radius:10px;margin-bottom:20px;font-size:14px;
                background:#dcfce7;color:#16a34a;font-weight:600;">
        ✅ <?= htmlspecialchars($_GET['msg']) ?>
    </div>
    <?php endif; ?>

    <!-- Summary Stats -->
    <div class="stats-row">
        <div class="stat-card">
            <p class="stat-num" style="color:#d97706;"><?= $pending ?></p>
            <p class="stat-lbl">Pending</p>
        </div>
        <div class="stat-card">
            <p class="stat-num" style="color:#16a34a;"><?= $approved ?></p>
            <p class="stat-lbl">Approved</p>
        </div>
        <div class="stat-card">
            <p class="stat-num" style="color:#2563eb;"><?= $completed ?></p>
            <p class="stat-lbl">Completed</p>
        </div>
        <div class="stat-card">
            <p class="stat-num" style="color:#dc2626;"><?= $rejected ?></p>
            <p class="stat-lbl">Rejected</p>
        </div>
    </div>

    <!-- Filters -->
    <form method="GET" action="appointments.php">
        <div class="filter-bar">
            <select name="status">
                <option value="">All Statuses</option>
                <option value="pending"   <?= $filter_status==='pending'   ?'selected':'' ?>>⏳ Pending</option>
                <option value="approved"  <?= $filter_status==='approved'  ?'selected':'' ?>>✅ Approved</option>
                <option value="completed" <?= $filter_status==='completed' ?'selected':'' ?>>🔵 Completed</option>
                <option value="rejected"  <?= $filter_status==='rejected'  ?'selected':'' ?>>❌ Rejected</option>
            </select>
            <select name="service">
                <option value="">All Services</option>
                <option value="islamic_marriage"   <?= $filter_service==='islamic_marriage'   ?'selected':'' ?>>💍 Islamic Marriage</option>
                <option value="halal_certification"<?= $filter_service==='halal_certification'?'selected':'' ?>>✅ Halal Certification</option>
                <option value="burial_assistance"  <?= $filter_service==='burial_assistance'  ?'selected':'' ?>>🕊️ Burial Assistance</option>
                <option value="scholarship"        <?= $filter_service==='scholarship'        ?'selected':'' ?>>🎓 Scholarship</option>
            </select>
            <button type="submit" class="filter-btn">Filter</button>
            <a href="appointments.php" class="reset-link">Clear filters</a>
        </div>
    </form>

    <!-- Table -->
    <div class="section-card">
        <?php if (!empty($appointments)): ?>
        <table>
            <thead>
                <tr>
                    <th>Reference</th>
                    <th>Applicant</th>
                    <th>Service</th>
                    <th>Preferred Schedule</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($appointments as $a): ?>
            <tr>
                <td>
                    <strong style="font-size:13px;"><?= htmlspecialchars($a['reference_no']) ?></strong><br>
                    <span style="font-size:11px;color:#9ca3af;">
                        Submitted: <?= date('M d, Y', strtotime($a['created_at'])) ?>
                    </span>
                </td>
                <td>
                    <strong><?= htmlspecialchars($a['full_name']) ?></strong><br>
                    <span style="font-size:12px;color:#6c757d;">📞 <?= htmlspecialchars($a['contact']) ?></span>
                    <?php if ($a['email']): ?>
                    <br><span style="font-size:12px;color:#6c757d;">✉️ <?= htmlspecialchars($a['email']) ?></span>
                    <?php endif; ?>
                    <?php if ($a['purpose']): ?>
                    <p style="font-size:12px;color:#374151;margin-top:5px;max-width:220px;line-height:1.4;">
                        "<?= htmlspecialchars(substr($a['purpose'], 0, 90)) ?><?= strlen($a['purpose']) > 90 ? '...' : '' ?>"
                    </p>
                    <?php endif; ?>
                </td>
                <td>
                    <span class="svc-tag"><?= htmlspecialchars($service_labels[$a['service_type']] ?? $a['service_type']) ?></span>
                </td>
                <td>
                    <strong><?= date('M d, Y', strtotime($a['preferred_date'])) ?></strong><br>
                    <span style="font-size:12px;color:#6c757d;"><?= $time_labels[$a['preferred_time']] ?? $a['preferred_time'] ?></span>
                </td>
                <td>
                    <span class="badge badge-<?= $a['status'] ?>"><?= ucfirst($a['status']) ?></span>
                    <?php if ($a['admin_remarks']): ?>
                    <p class="remarks-text">📝 <?= htmlspecialchars($a['admin_remarks']) ?></p>
                    <?php endif; ?>
                </td>
                <td>
                    <div style="display:flex;flex-direction:column;gap:6px;">
                    <?php if ($a['status'] === 'pending'): ?>
                        <button class="act-btn btn-approve"
                                onclick="openModal('approve', <?= $a['id'] ?>, '<?= htmlspecialchars(addslashes($a['full_name'])) ?>')">
                            ✅ Approve
                        </button>
                        <button class="act-btn btn-reject"
                                onclick="openModal('reject', <?= $a['id'] ?>, '<?= htmlspecialchars(addslashes($a['full_name'])) ?>')">
                            ❌ Reject
                        </button>
                    <?php elseif ($a['status'] === 'approved'): ?>
                        <button class="act-btn btn-complete"
                                onclick="openModal('complete', <?= $a['id'] ?>, '<?= htmlspecialchars(addslashes($a['full_name'])) ?>')">
                            🔵 Mark Done
                        </button>
                    <?php else: ?>
                        <span style="font-size:12px;color:#9ca3af;">No actions</span>
                    <?php endif; ?>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <div class="empty-state">
            <div>📋</div>
            <p>No appointments found<?= ($filter_status || $filter_service) ? ' for this filter.' : ' yet.' ?></p>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Action Modal -->
<div class="modal-overlay" id="actionModal">
    <div class="modal">
        <h3 id="modal-title">Update Appointment</h3>
        <p id="modal-subtitle">You are updating the appointment for <strong id="modal-name"></strong>.</p>

        <form method="POST" action="../actions/update_appointment.php">
            <input type="hidden" name="appointment_id" id="modal-appt-id">
            <input type="hidden" name="new_status"      id="modal-new-status">

            <div id="remarks-block">
                <label for="modal-remarks">Remarks <span id="remarks-hint" style="font-weight:400;color:#6c757d;"></span></label>
                <textarea name="admin_remarks" id="modal-remarks"
                          placeholder="Add a note for your records..."></textarea>
            </div>

            <div class="modal-btns">
                <button type="button" class="modal-cancel" onclick="closeModal()">Cancel</button>
                <button type="submit" class="modal-submit" id="modal-submit-btn">Confirm</button>
            </div>
        </form>
    </div>
</div>

<script>
var statusColors = {
    approve:  { bg: '#16a34a', label: 'Approve Appointment',  hint: '(optional — e.g. confirmed date/time)',  btn: '✅ Approve' },
    reject:   { bg: '#dc2626', label: 'Reject Appointment',   hint: '(recommended — explain why)',             btn: '❌ Reject' },
    complete: { bg: '#2563eb', label: 'Mark as Completed',    hint: '(optional)',                              btn: '🔵 Mark Done' }
};

function openModal(action, id, name) {
    var cfg = statusColors[action];
    document.getElementById('modal-title').textContent    = cfg.label;
    document.getElementById('modal-name').textContent     = name;
    document.getElementById('modal-appt-id').value        = id;
    document.getElementById('modal-new-status').value     = action === 'approve' ? 'approved'
                                                         : action === 'reject'  ? 'rejected'
                                                         : 'completed';
    document.getElementById('remarks-hint').textContent   = cfg.hint;
    document.getElementById('modal-submit-btn').textContent = cfg.btn;
    document.getElementById('modal-submit-btn').style.background = cfg.bg;
    document.getElementById('modal-remarks').value        = '';
    document.getElementById('actionModal').classList.add('open');
}

function closeModal() {
    document.getElementById('actionModal').classList.remove('open');
}

// Close on backdrop click
document.getElementById('actionModal').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});
</script>

<?php include "../includes/footer.php"; ?>
