<?php
session_start();
include "../config/database.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: dashboard.php");
    exit();
}

// Handle status toggle from this page
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_user_id'])) {
    if (!csrf_verify()) { header("Location: users.php?msg=invalid"); exit(); }
    $tid = (int) $_POST['toggle_user_id'];
    $new_status = ($_POST['current_status'] === 'active') ? 'suspended' : 'active';
    $upd = $conn->prepare("UPDATE users SET status = ? WHERE id = ?");
    $upd->bind_param("si", $new_status, $tid);
    $upd->execute();
    $upd->close();
    header("Location: users.php?msg=updated");
    exit();
}

// Fetch all users
$users = [];
$uRes = $conn->query("
    SELECT u.*, c.name AS community_name
    FROM users u
    LEFT JOIN communities c ON u.community_id = c.id
    ORDER BY u.id ASC
");
while ($r = $uRes->fetch_assoc()) {
    $users[] = $r;
}

include "../includes/header.php";
include "../includes/sidebar.php";
?>
<style>
.page-container { margin-left:260px; padding:90px 30px 30px; background:#121212; min-height:100vh; }
.page-title  { font-size:24px; font-weight:700; color:#fff; margin:0 0 5px; }
.page-subtitle { font-size:14px; color:#9ca3af; margin:0 0 25px; }
.stats-row { display:grid; grid-template-columns:repeat(4,1fr); gap:15px; margin-bottom:25px; }
.stat-card { background:#1e1e1e; border-radius:12px; padding:20px; text-align:center; box-shadow:0 2px 8px rgba(0,0,0,.3); }
.stat-number { font-size:26px; font-weight:700; color:#fff; margin:0; }
.stat-label  { font-size:12px; color:#9ca3af; margin:4px 0 0; }
.section-card { background:#1e1e1e; border-radius:16px; padding:25px; box-shadow:0 2px 10px rgba(0,0,0,.3); }
table { width:100%; border-collapse:collapse; }
th { text-align:left; padding:12px 15px; font-size:12px; font-weight:600; color:#6b7280; text-transform:uppercase; border-bottom:2px solid #2a2a2a; }
td { padding:14px 15px; border-bottom:1px solid #2a2a2a; font-size:14px; vertical-align:middle; color:#d1d5db; }
tr:hover td { background:#252525; }
.avatar { width:36px; height:36px; border-radius:50%; background:linear-gradient(135deg,#ff4dc4,#ff00aa); display:flex; align-items:center; justify-content:center; color:white; font-weight:700; font-size:14px; flex-shrink:0; }
.role-badge { font-size:11px; padding:3px 10px; border-radius:12px; font-weight:600; }
.role-admin  { background:rgba(220,38,38,.2); color:#f87171; }
.role-imam   { background:rgba(22,163,74,.2); color:#4ade80; }
.role-leader { background:rgba(37,99,235,.2); color:#60a5fa; }
.role-viewer { background:rgba(107,114,128,.2); color:#9ca3af; }
.status-badge { font-size:11px; padding:3px 10px; border-radius:12px; font-weight:600; }
.status-active    { background:rgba(22,163,74,.2); color:#4ade80; }
.status-suspended { background:rgba(220,38,38,.2); color:#f87171; }
.status-pending   { background:rgba(217,119,6,.2); color:#fbbf24; }
.action-form { display:inline; }
.toggle-btn { font-size:12px; padding:6px 14px; border:none; border-radius:8px; cursor:pointer; font-weight:600; transition:all .2s; }
.btn-suspend { background:rgba(220,38,38,.15); color:#f87171; }
.btn-suspend:hover { background:#dc2626; color:white; }
.btn-activate { background:rgba(22,163,74,.15); color:#4ade80; }
.btn-activate:hover { background:#16a34a; color:white; }
.alert { padding:12px 16px; border-radius:10px; margin-bottom:20px; font-size:14px; background:rgba(22,163,74,.15); color:#4ade80; }
</style>

<div class="page-container">
    <h1 class="page-title">👥 User Management</h1>
    <p class="page-subtitle">Manage system accounts — activate or suspend access</p>

    <?php if (isset($_GET['msg']) && $_GET['msg'] === 'updated'): ?>
    <div class="alert">✅ User status updated successfully.</div>
    <?php endif; ?>

    <!-- Stats -->
    <?php
    $total     = count($users);
    $active    = count(array_filter($users, fn($u) => ($u['status'] ?? 'active') === 'active'));
    $suspended = count(array_filter($users, fn($u) => ($u['status'] ?? '') === 'suspended'));
    $admins    = count(array_filter($users, fn($u) => $u['role'] === 'admin'));
    ?>
    <div class="stats-row">
        <div class="stat-card">
            <p class="stat-number"><?= $total ?></p>
            <p class="stat-label">Total Users</p>
        </div>
        <div class="stat-card">
            <p class="stat-number" style="color:#16a34a;"><?= $active ?></p>
            <p class="stat-label">Active</p>
        </div>
        <div class="stat-card">
            <p class="stat-number" style="color:#dc2626;"><?= $suspended ?></p>
            <p class="stat-label">Suspended</p>
        </div>
        <div class="stat-card">
            <p class="stat-number" style="color:#ff00aa;"><?= $admins ?></p>
            <p class="stat-label">Admins</p>
        </div>
    </div>

    <div class="section-card">
        <table>
            <thead>
                <tr>
                    <th>User</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Community</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u):
                    $status = $u['status'] ?? 'active';
                ?>
                <tr>
                    <td>
                        <div style="display:flex;align-items:center;gap:10px;">
                            <div class="avatar"><?= strtoupper(substr($u['name'], 0, 1)) ?></div>
                            <span style="font-weight:600;"><?= htmlspecialchars($u['name']) ?></span>
                        </div>
                    </td>
                    <td><?= htmlspecialchars($u['email']) ?></td>
                    <td>
                        <span class="role-badge role-<?= $u['role'] ?>">
                            <?= ucfirst($u['role']) ?>
                        </span>
                    </td>
                    <td><?= htmlspecialchars($u['community_name'] ?? '—') ?></td>
                    <td>
                        <span class="status-badge status-<?= $status ?>">
                            <?= ucfirst($status) ?>
                        </span>
                    </td>
                    <td>
                        <?php if ($u['id'] !== (int) $_SESSION['user_id']): ?>
                        <form method="POST" action="users.php" class="action-form"
                              onsubmit="return confirm('<?= $status === 'active' ? 'Suspend' : 'Activate' ?> this user?')">
                            <?= csrf_field() ?>
                            <input type="hidden" name="toggle_user_id"  value="<?= $u['id'] ?>">
                            <input type="hidden" name="current_status"  value="<?= $status ?>">
                            <?php if ($status === 'active'): ?>
                                <button class="toggle-btn btn-suspend" type="submit">🚫 Suspend</button>
                            <?php else: ?>
                                <button class="toggle-btn btn-activate" type="submit">✅ Activate</button>
                            <?php endif; ?>
                        </form>
                        <?php else: ?>
                            <span style="color:#9ca3af;font-size:12px;">You</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php include "../includes/footer.php"; ?>
