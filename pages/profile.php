<?php
session_start();
include "../config/database.php";
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$user_id = (int) $_SESSION['user_id'];

$stmt = $conn->prepare("
    SELECT u.*, c.name AS community_name
    FROM users u
    LEFT JOIN communities c ON u.community_id = c.id
    WHERE u.id = ?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Stats
$ev_count   = (int) $conn->query("SELECT COUNT(*) c FROM events WHERE user_id=$user_id")->fetch_assoc()['c'];
$ann_count  = (int) $conn->query("SELECT COUNT(*) c FROM announcements WHERE user_id=$user_id")->fetch_assoc()['c'];
$don_count  = (int) $conn->query("SELECT COUNT(*) c FROM donations WHERE user_id=$user_id")->fetch_assoc()['c'];
$att_count  = (int) $conn->query("SELECT COUNT(*) c FROM attendance WHERE recorded_by=$user_id")->fetch_assoc()['c'];

$tab     = $_GET['tab']     ?? 'profile';
$success = $_GET['success'] ?? '';
$error   = $_GET['error']   ?? '';

$role_labels = [
    'admin'  => ['Admin',            '#a78bfa', 'rgba(167,139,250,.15)'],
    'imam'   => ['Imam',             '#fbbf24', 'rgba(251,191,36,.15)'],
    'leader' => ['Community Leader', '#60a5fa', 'rgba(96,165,250,.15)'],
    'viewer' => ['Resident',         '#4ade80', 'rgba(74,222,128,.15)'],
];
$rl = $role_labels[$user['role']] ?? ['Member', '#fff', 'rgba(255,255,255,.1)'];

include "../includes/header.php";
include "../includes/sidebar.php";
?>
<style>
.page-container { margin-left:260px; padding:90px 30px 40px; background:#121212; min-height:100vh; color:#fff; }

/* Hero */
.profile-hero {
    background: linear-gradient(135deg,#1e1e1e 0%,#2a1a2e 100%);
    border-radius:20px; padding:32px 36px;
    display:flex; align-items:center; gap:28px;
    border:1px solid rgba(255,255,255,.06);
    margin-bottom:26px; position:relative; overflow:hidden;
}
.profile-hero::before {
    content:''; position:absolute; top:-60px; right:-60px;
    width:200px; height:200px;
    background:radial-gradient(circle,rgba(255,0,170,.15),transparent 70%);
    pointer-events:none;
}
.hero-avatar {
    width:90px; height:90px; border-radius:50%;
    background:linear-gradient(135deg,#ff4dc4,#ff00aa);
    display:flex; align-items:center; justify-content:center;
    font-size:36px; font-weight:800; color:#fff;
    flex-shrink:0; box-shadow:0 0 0 4px rgba(255,0,170,.2);
}
.hero-name  { font-size:24px; font-weight:800; color:#fff; margin:0 0 4px; }
.hero-email { font-size:14px; color:rgba(255,255,255,.45); margin:0 0 12px; }
.hero-badges { display:flex; gap:8px; flex-wrap:wrap; }
.hero-badge {
    font-size:12px; font-weight:600; padding:4px 12px;
    border-radius:20px; letter-spacing:.3px;
}
.hero-stats { margin-left:auto; display:flex; gap:24px; text-align:center; }
.hero-stat-num { font-size:22px; font-weight:800; color:#fff; margin:0; }
.hero-stat-lbl { font-size:11px; color:rgba(255,255,255,.4); margin:3px 0 0; }

/* Tabs */
.tab-row { display:flex; gap:4px; margin-bottom:22px; }
.tab-btn {
    padding:10px 20px; border-radius:10px; border:none; cursor:pointer;
    font-size:14px; font-weight:600;
    background:rgba(255,255,255,.06); color:rgba(255,255,255,.5);
    transition:all .2s;
}
.tab-btn:hover  { background:rgba(255,255,255,.1); color:#fff; }
.tab-btn.active { background:linear-gradient(135deg,#ff4dc4,#ff00aa); color:#fff; box-shadow:0 4px 15px rgba(255,0,170,.3); }

/* Cards */
.card { background:#1e1e1e; border-radius:16px; padding:28px; border:1px solid rgba(255,255,255,.06); margin-bottom:20px; }
.card-title { font-size:16px; font-weight:700; color:#fff; margin:0 0 20px; padding-bottom:14px; border-bottom:1px solid rgba(255,255,255,.07); display:flex; align-items:center; gap:8px; }

/* Form */
.form-group { margin-bottom:18px; }
.form-group label { display:block; font-size:12px; font-weight:600; color:rgba(255,255,255,.4); margin-bottom:7px; text-transform:uppercase; letter-spacing:.5px; }
.form-group input {
    width:100%; padding:13px 16px;
    background:rgba(255,255,255,.07);
    border:1.5px solid rgba(255,255,255,.1);
    border-radius:10px; color:#fff; font-size:14px; outline:none;
    transition:border-color .2s, background .2s;
}
.form-group input::placeholder { color:rgba(255,255,255,.25); }
.form-group input:focus { border-color:#ff00aa; background:rgba(255,255,255,.1); }
.form-group input[readonly] {
    background:rgba(255,255,255,.04);
    color:rgba(255,255,255,.4);
    cursor:not-allowed;
}
.form-row { display:grid; grid-template-columns:1fr 1fr; gap:16px; }
.btn-primary {
    padding:13px 28px; background:linear-gradient(135deg,#ff4dc4,#ff00aa);
    color:#fff; border:none; border-radius:10px; font-size:14px; font-weight:700;
    cursor:pointer; transition:all .2s; box-shadow:0 4px 15px rgba(255,0,170,.25);
}
.btn-primary:hover { opacity:.9; transform:translateY(-1px); }

/* Alert */
.alert { padding:13px 18px; border-radius:10px; font-size:14px; font-weight:500; margin-bottom:20px; display:flex; align-items:center; gap:10px; }
.alert-success { background:rgba(74,222,128,.12); border:1px solid rgba(74,222,128,.25); color:#4ade80; }
.alert-error   { background:rgba(248,113,113,.12); border:1px solid rgba(248,113,113,.25); color:#f87171; }

/* Info rows */
.info-row { display:flex; align-items:center; justify-content:space-between; padding:14px 0; border-bottom:1px solid rgba(255,255,255,.06); }
.info-row:last-child { border-bottom:none; }
.info-label { font-size:13px; color:rgba(255,255,255,.45); }
.info-value { font-size:14px; font-weight:600; color:#fff; }

/* Password strength */
.pw-strength { height:4px; border-radius:2px; margin-top:6px; transition:all .3s; background:rgba(255,255,255,.1); }
.pw-hint { font-size:11px; color:rgba(255,255,255,.3); margin-top:5px; }

.tab-panel { display:none; }
.tab-panel.active { display:block; }
</style>

<div class="page-container">

    <!-- Hero -->
    <div class="profile-hero">
        <div class="hero-avatar"><?= strtoupper(substr($user['name'], 0, 1)) ?></div>
        <div>
            <h1 class="hero-name"><?= htmlspecialchars($user['name']) ?></h1>
            <p class="hero-email"><?= htmlspecialchars($user['email']) ?></p>
            <div class="hero-badges">
                <span class="hero-badge" style="background:<?= $rl[2] ?>;color:<?= $rl[1] ?>;">
                    <?= $rl[0] ?>
                </span>
                <?php if ($user['community_name']): ?>
                <span class="hero-badge" style="background:rgba(255,255,255,.08);color:rgba(255,255,255,.6);">
                    📍 <?= htmlspecialchars($user['community_name']) ?>
                </span>
                <?php endif; ?>
                <span class="hero-badge" style="background:rgba(74,222,128,.12);color:#4ade80;">
                    ● Active
                </span>
            </div>
        </div>
        <div class="hero-stats">
            <div>
                <p class="hero-stat-num"><?= $ev_count ?></p>
                <p class="hero-stat-lbl">Events</p>
            </div>
            <div>
                <p class="hero-stat-num"><?= $ann_count ?></p>
                <p class="hero-stat-lbl">Announcements</p>
            </div>
            <div>
                <p class="hero-stat-num"><?= $don_count ?></p>
                <p class="hero-stat-lbl">Donations</p>
            </div>
            <div>
                <p class="hero-stat-num"><?= $att_count ?></p>
                <p class="hero-stat-lbl">Attendance</p>
            </div>
        </div>
    </div>

    <!-- Tabs -->
    <div class="tab-row">
        <button class="tab-btn <?= $tab==='profile'  ? 'active' : '' ?>" onclick="switchTab('profile')">👤 Profile Info</button>
        <button class="tab-btn <?= $tab==='security' ? 'active' : '' ?>" onclick="switchTab('security')">🔒 Security</button>
        <button class="tab-btn <?= $tab==='account'  ? 'active' : '' ?>" onclick="switchTab('account')">ℹ️ Account Details</button>
    </div>

    <?php if ($success): ?>
    <div class="alert alert-success">✅ <?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
    <div class="alert alert-error">⚠️ <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <!-- Profile Info Tab -->
    <div id="tab-profile" class="tab-panel <?= $tab==='profile' ? 'active' : '' ?>">
        <div class="card">
            <h3 class="card-title">👤 Edit Profile Information</h3>
            <form method="POST" action="../actions/update_profile.php">
                <?= csrf_field() ?>
                <div class="form-row">
                    <div class="form-group">
                        <label>Full Name *</label>
                        <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Email Address *</label>
                        <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Role</label>
                        <input type="text" value="<?= $rl[0] ?>" readonly>
                    </div>
                    <div class="form-group">
                        <label>Community</label>
                        <input type="text" value="<?= htmlspecialchars($user['community_name'] ?? 'Not assigned') ?>" readonly>
                    </div>
                </div>
                <button type="submit" class="btn-primary">💾 Save Changes</button>
            </form>
        </div>
    </div>

    <!-- Security Tab -->
    <div id="tab-security" class="tab-panel <?= $tab==='security' ? 'active' : '' ?>">
        <div class="card">
            <h3 class="card-title">🔒 Change Password</h3>
            <form method="POST" action="../actions/change_password.php" style="max-width:480px;">
                <?= csrf_field() ?>
                <div class="form-group">
                    <label>Current Password</label>
                    <input type="password" name="current_password" placeholder="Enter your current password" required>
                </div>
                <div class="form-group">
                    <label>New Password</label>
                    <input type="password" name="new_password" id="newPw" placeholder="Minimum 8 characters" required oninput="checkStrength(this.value)">
                    <div class="pw-strength" id="pwStrength"></div>
                    <p class="pw-hint" id="pwHint">Enter a new password</p>
                </div>
                <div class="form-group">
                    <label>Confirm New Password</label>
                    <input type="password" name="confirm_password" placeholder="Repeat new password" required>
                </div>
                <button type="submit" class="btn-primary">🔐 Update Password</button>
            </form>
        </div>
        <div class="card">
            <h3 class="card-title">🛡️ Security Tips</h3>
            <div class="info-row">
                <span class="info-label">Use a strong, unique password</span>
                <span style="color:#4ade80;font-size:13px;">✅ Recommended</span>
            </div>
            <div class="info-row">
                <span class="info-label">Never share your login credentials</span>
                <span style="color:#4ade80;font-size:13px;">✅ Important</span>
            </div>
            <div class="info-row">
                <span class="info-label">Log out on shared computers</span>
                <span style="color:#4ade80;font-size:13px;">✅ Important</span>
            </div>
            <div class="info-row">
                <span class="info-label">Password hashing</span>
                <span class="info-value" style="color:#a78bfa;">bcrypt (secure)</span>
            </div>
        </div>
    </div>

    <!-- Account Details Tab -->
    <div id="tab-account" class="tab-panel <?= $tab==='account' ? 'active' : '' ?>">
        <div class="card">
            <h3 class="card-title">ℹ️ Account Information</h3>
            <div class="info-row">
                <span class="info-label">Account ID</span>
                <span class="info-value" style="color:rgba(255,255,255,.4);">#<?= $user['id'] ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Full Name</span>
                <span class="info-value"><?= htmlspecialchars($user['name']) ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Email Address</span>
                <span class="info-value"><?= htmlspecialchars($user['email']) ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Role</span>
                <span class="hero-badge" style="background:<?= $rl[2] ?>;color:<?= $rl[1] ?>;font-size:12px;"><?= $rl[0] ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Community</span>
                <span class="info-value"><?= htmlspecialchars($user['community_name'] ?? 'Not assigned') ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Account Status</span>
                <span class="hero-badge" style="background:rgba(74,222,128,.12);color:#4ade80;font-size:12px;">● Active</span>
            </div>
            <?php if (!empty($user['created_at'])): ?>
            <div class="info-row">
                <span class="info-label">Member Since</span>
                <span class="info-value"><?= date('F d, Y', strtotime($user['created_at'])) ?></span>
            </div>
            <?php endif; ?>
        </div>

        <div class="card">
            <h3 class="card-title">📊 Activity Summary</h3>
            <div class="info-row">
                <span class="info-label">Events Created</span>
                <span class="info-value"><?= $ev_count ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Announcements Posted</span>
                <span class="info-value"><?= $ann_count ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Donations Recorded</span>
                <span class="info-value"><?= $don_count ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Attendance Records</span>
                <span class="info-value"><?= $att_count ?></span>
            </div>
        </div>
    </div>
</div>

<script>
function switchTab(name) {
    document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.getElementById('tab-' + name).classList.add('active');
    event.currentTarget.classList.add('active');
}

function checkStrength(val) {
    var bar  = document.getElementById('pwStrength');
    var hint = document.getElementById('pwHint');
    if (!bar) return;
    var score = 0;
    if (val.length >= 8)  score++;
    if (/[A-Z]/.test(val)) score++;
    if (/[0-9]/.test(val)) score++;
    if (/[^A-Za-z0-9]/.test(val)) score++;

    var colors = ['#f87171','#fbbf24','#60a5fa','#4ade80'];
    var hints  = ['Weak — too short','Fair — add numbers or uppercase','Good — add symbols','Strong password'];
    bar.style.background = score === 0 ? 'rgba(255,255,255,.1)' : colors[score - 1];
    bar.style.width = (score * 25) + '%';
    hint.textContent = val.length === 0 ? 'Enter a new password' : hints[score - 1] || hints[3];
    hint.style.color = score === 0 ? 'rgba(255,255,255,.3)' : colors[score - 1];
}
</script>
<?php include "../includes/footer.php"; ?>
