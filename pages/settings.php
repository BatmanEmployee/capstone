<?php
session_start();
include "../config/database.php";
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$role = $_SESSION['role'];
include "../includes/header.php";
include "../includes/sidebar.php";
?>
<style>
.page-container { margin-left:260px; padding:90px 30px 40px; background:#121212; min-height:100vh; color:#fff; }
.page-title    { font-size:26px; font-weight:800; color:#fff; margin:0 0 4px; }
.page-subtitle { font-size:14px; color:rgba(255,255,255,.4); margin:0 0 28px; }

.settings-layout { display:grid; grid-template-columns:220px 1fr; gap:20px; align-items:start; }
@media(max-width:800px){ .settings-layout{ grid-template-columns:1fr; } }

/* Sidebar nav */
.settings-nav { background:#1e1e1e; border-radius:16px; padding:16px; border:1px solid rgba(255,255,255,.06); position:sticky; top:90px; }
.settings-nav-item {
    display:flex; align-items:center; gap:10px;
    padding:11px 14px; border-radius:10px; cursor:pointer;
    font-size:14px; color:rgba(255,255,255,.55);
    transition:all .2s; border:none; background:none;
    width:100%; text-align:left;
}
.settings-nav-item:hover  { background:rgba(255,255,255,.06); color:#fff; }
.settings-nav-item.active { background:rgba(255,0,170,.12); color:#ff4dc4; font-weight:600; }
.nav-divider { height:1px; background:rgba(255,255,255,.06); margin:10px 0; }

/* Cards */
.card { background:#1e1e1e; border-radius:16px; padding:26px; border:1px solid rgba(255,255,255,.06); margin-bottom:18px; }
.card-title { font-size:16px; font-weight:700; color:#fff; margin:0 0 18px; padding-bottom:14px; border-bottom:1px solid rgba(255,255,255,.07); display:flex; align-items:center; gap:9px; }

/* Toggle switch */
.setting-row { display:flex; align-items:center; justify-content:space-between; padding:14px 0; border-bottom:1px solid rgba(255,255,255,.05); }
.setting-row:last-child { border-bottom:none; }
.setting-info .setting-name { font-size:14px; font-weight:600; color:#fff; margin:0 0 3px; }
.setting-info .setting-desc { font-size:12px; color:rgba(255,255,255,.35); margin:0; }

.toggle { position:relative; width:44px; height:24px; flex-shrink:0; }
.toggle input { opacity:0; width:0; height:0; }
.toggle-slider {
    position:absolute; inset:0; background:rgba(255,255,255,.15);
    border-radius:12px; cursor:pointer; transition:.3s;
}
.toggle-slider::before {
    content:''; position:absolute;
    width:18px; height:18px; border-radius:50%;
    background:#fff; left:3px; top:3px; transition:.3s;
}
.toggle input:checked + .toggle-slider { background:linear-gradient(135deg,#ff4dc4,#ff00aa); }
.toggle input:checked + .toggle-slider::before { transform:translateX(20px); }

/* Color picker */
.color-options { display:flex; gap:10px; flex-wrap:wrap; margin-top:4px; }
.color-dot {
    width:32px; height:32px; border-radius:50%; cursor:pointer;
    border:3px solid transparent; transition:all .2s;
}
.color-dot.selected, .color-dot:hover { border-color:#fff; transform:scale(1.15); }

/* Tab panels */
.settings-panel { display:none; }
.settings-panel.active { display:block; }

/* Danger zone */
.danger-card { background:rgba(248,113,113,.07); border:1px solid rgba(248,113,113,.2); border-radius:16px; padding:26px; margin-bottom:18px; }
.danger-title { font-size:16px; font-weight:700; color:#f87171; margin:0 0 16px; }
.btn-danger { padding:11px 22px; background:rgba(248,113,113,.15); color:#f87171; border:1px solid rgba(248,113,113,.3); border-radius:10px; font-size:14px; font-weight:600; cursor:pointer; transition:all .2s; }
.btn-danger:hover { background:rgba(248,113,113,.25); }

.save-btn { padding:12px 26px; background:linear-gradient(135deg,#ff4dc4,#ff00aa); color:#fff; border:none; border-radius:10px; font-size:14px; font-weight:700; cursor:pointer; transition:all .2s; box-shadow:0 4px 15px rgba(255,0,170,.25); }
.save-btn:hover { opacity:.9; transform:translateY(-1px); }
</style>

<div class="page-container">
    <h1 class="page-title">⚙️ Settings</h1>
    <p class="page-subtitle">Manage your preferences and account configuration</p>

    <div class="settings-layout">

        <!-- Left Nav -->
        <div class="settings-nav">
            <button class="settings-nav-item active" onclick="showPanel('notifications', this)">🔔 Notifications</button>
            <button class="settings-nav-item" onclick="showPanel('appearance', this)">🎨 Appearance</button>
            <button class="settings-nav-item" onclick="showPanel('privacy', this)">🔒 Privacy</button>
            <div class="nav-divider"></div>
            <button class="settings-nav-item" onclick="showPanel('account', this)">👤 Account</button>
            <?php if ($role === 'admin'): ?>
            <button class="settings-nav-item" onclick="showPanel('system', this)">🛠 System</button>
            <?php endif; ?>
        </div>

        <!-- Right Panels -->
        <div>

            <!-- Notifications Panel -->
            <div id="panel-notifications" class="settings-panel active">
                <div class="card">
                    <h3 class="card-title">🔔 Notification Preferences</h3>

                    <div class="setting-row">
                        <div class="setting-info">
                            <p class="setting-name">Event Reminders</p>
                            <p class="setting-desc">Get notified about upcoming events in your community</p>
                        </div>
                        <label class="toggle">
                            <input type="checkbox" id="notif_events" checked onchange="savePref()">
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                    <div class="setting-row">
                        <div class="setting-info">
                            <p class="setting-name">Announcement Alerts</p>
                            <p class="setting-desc">Show alerts when new announcements are posted</p>
                        </div>
                        <label class="toggle">
                            <input type="checkbox" id="notif_announcements" checked onchange="savePref()">
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                    <div class="setting-row">
                        <div class="setting-info">
                            <p class="setting-name">Prayer Time Alerts</p>
                            <p class="setting-desc">Show daily Iftar, Taraweeh, and Suhoor notifications</p>
                        </div>
                        <label class="toggle">
                            <input type="checkbox" id="notif_prayer" checked onchange="savePref()">
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                    <div class="setting-row">
                        <div class="setting-info">
                            <p class="setting-name">Donation Updates</p>
                            <p class="setting-desc">Notify when a new donation is recorded</p>
                        </div>
                        <label class="toggle">
                            <input type="checkbox" id="notif_donations" onchange="savePref()">
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                    <div class="setting-row">
                        <div class="setting-info">
                            <p class="setting-name">Forum Activity</p>
                            <p class="setting-desc">Notify when someone replies to your forum posts</p>
                        </div>
                        <label class="toggle">
                            <input type="checkbox" id="notif_forum" checked onchange="savePref()">
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                </div>
                <div style="text-align:right;">
                    <button class="save-btn" onclick="saveAndToast()">💾 Save Preferences</button>
                </div>
            </div>

            <!-- Appearance Panel -->
            <div id="panel-appearance" class="settings-panel">
                <div class="card">
                    <h3 class="card-title">🎨 Appearance Settings</h3>
                    <div class="setting-row">
                        <div class="setting-info">
                            <p class="setting-name">Theme</p>
                            <p class="setting-desc">The system uses the MCAD dark theme</p>
                        </div>
                        <span style="font-size:13px;color:#a78bfa;font-weight:600;">🌙 Dark Mode</span>
                    </div>
                    <div class="setting-row">
                        <div class="setting-info">
                            <p class="setting-name">Accent Color</p>
                            <p class="setting-desc">Choose your preferred highlight color</p>
                        </div>
                        <div class="color-options">
                            <div class="color-dot selected" style="background:linear-gradient(135deg,#ff4dc4,#ff00aa);" onclick="setAccent('pink',this)" title="Pink (Default)"></div>
                            <div class="color-dot" style="background:linear-gradient(135deg,#60a5fa,#2563eb);" onclick="setAccent('blue',this)" title="Blue"></div>
                            <div class="color-dot" style="background:linear-gradient(135deg,#4ade80,#16a34a);" onclick="setAccent('green',this)" title="Green"></div>
                            <div class="color-dot" style="background:linear-gradient(135deg,#fbbf24,#d97706);" onclick="setAccent('amber',this)" title="Amber"></div>
                        </div>
                    </div>
                    <div class="setting-row">
                        <div class="setting-info">
                            <p class="setting-name">Compact Mode</p>
                            <p class="setting-desc">Reduce spacing for a denser layout</p>
                        </div>
                        <label class="toggle">
                            <input type="checkbox" id="compact_mode" onchange="savePref()">
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                    <div class="setting-row">
                        <div class="setting-info">
                            <p class="setting-name">Sidebar Always Open</p>
                            <p class="setting-desc">Keep the sidebar visible at all times</p>
                        </div>
                        <label class="toggle">
                            <input type="checkbox" id="sidebar_open" checked onchange="savePref()">
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                </div>
                <div style="text-align:right;">
                    <button class="save-btn" onclick="saveAndToast()">💾 Save Appearance</button>
                </div>
            </div>

            <!-- Privacy Panel -->
            <div id="panel-privacy" class="settings-panel">
                <div class="card">
                    <h3 class="card-title">🔒 Privacy Settings</h3>
                    <div class="setting-row">
                        <div class="setting-info">
                            <p class="setting-name">Show Profile to Community Members</p>
                            <p class="setting-desc">Other members can see your name and role</p>
                        </div>
                        <label class="toggle">
                            <input type="checkbox" id="priv_profile" checked onchange="savePref()">
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                    <div class="setting-row">
                        <div class="setting-info">
                            <p class="setting-name">Show Activity Status</p>
                            <p class="setting-desc">Display when you are active in the system</p>
                        </div>
                        <label class="toggle">
                            <input type="checkbox" id="priv_activity" onchange="savePref()">
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                    <div class="setting-row">
                        <div class="setting-info">
                            <p class="setting-name">Allow Forum Mentions</p>
                            <p class="setting-desc">Other users can tag you in forum discussions</p>
                        </div>
                        <label class="toggle">
                            <input type="checkbox" id="priv_mentions" checked onchange="savePref()">
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                </div>
                <div style="text-align:right;">
                    <button class="save-btn" onclick="saveAndToast()">💾 Save Privacy Settings</button>
                </div>
            </div>

            <!-- Account Panel -->
            <div id="panel-account" class="settings-panel">
                <div class="card">
                    <h3 class="card-title">👤 Account Actions</h3>
                    <div class="setting-row">
                        <div class="setting-info">
                            <p class="setting-name">Edit Profile</p>
                            <p class="setting-desc">Update your name, email, and password</p>
                        </div>
                        <a href="profile.php" style="padding:10px 18px;background:rgba(255,255,255,.07);color:#fff;border-radius:10px;text-decoration:none;font-size:13px;font-weight:600;">Go to Profile →</a>
                    </div>
                    <div class="setting-row">
                        <div class="setting-info">
                            <p class="setting-name">Session Management</p>
                            <p class="setting-desc">Your session expires when you log out</p>
                        </div>
                        <span style="color:#4ade80;font-size:13px;font-weight:600;">● Active</span>
                    </div>
                    <div class="setting-row">
                        <div class="setting-info">
                            <p class="setting-name">Export My Data</p>
                            <p class="setting-desc">Download a summary of your community activity</p>
                        </div>
                        <button onclick="alert('Data export will be available in a future update.')" style="padding:10px 18px;background:rgba(255,255,255,.07);color:#fff;border:1px solid rgba(255,255,255,.1);border-radius:10px;font-size:13px;font-weight:600;cursor:pointer;">Export</button>
                    </div>
                </div>
                <div class="danger-card">
                    <h3 class="danger-title">⚠️ Danger Zone</h3>
                    <div class="setting-row" style="border:none;padding-top:0;">
                        <div class="setting-info">
                            <p class="setting-name" style="color:#f87171;">Log Out of All Sessions</p>
                            <p class="setting-desc">This will immediately end your current session</p>
                        </div>
                        <a href="../actions/logout.php" class="btn-danger">Logout Now</a>
                    </div>
                </div>
            </div>

            <?php if ($role === 'admin'): ?>
            <!-- System Panel (Admin only) -->
            <div id="panel-system" class="settings-panel">
                <div class="card">
                    <h3 class="card-title">🛠 System Settings</h3>
                    <div class="setting-row">
                        <div class="setting-info">
                            <p class="setting-name">Auto-generate Prayer Events</p>
                            <p class="setting-desc">Automatically create daily Iftar, Taraweeh, and Suhoor events</p>
                        </div>
                        <label class="toggle">
                            <input type="checkbox" id="sys_prayer_auto" checked onchange="savePref()">
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                    <div class="setting-row">
                        <div class="setting-info">
                            <p class="setting-name">Allow Self-Registration</p>
                            <p class="setting-desc">Community members can register themselves</p>
                        </div>
                        <label class="toggle">
                            <input type="checkbox" id="sys_self_register" checked onchange="savePref()">
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                    <div class="setting-row">
                        <div class="setting-info">
                            <p class="setting-name">User Management</p>
                            <p class="setting-desc">Activate, suspend, and manage all user accounts</p>
                        </div>
                        <a href="users.php" style="padding:10px 18px;background:rgba(255,255,255,.07);color:#fff;border-radius:10px;text-decoration:none;font-size:13px;font-weight:600;">Manage Users →</a>
                    </div>
                </div>
                <div style="text-align:right;">
                    <button class="save-btn" onclick="saveAndToast()">💾 Save System Settings</button>
                </div>
            </div>
            <?php endif; ?>

        </div>
    </div>
</div>

<!-- Toast notification -->
<div id="toast" style="
    position:fixed; bottom:30px; right:30px;
    background:#282828; border:1px solid rgba(74,222,128,.3);
    color:#4ade80; padding:14px 22px; border-radius:12px;
    font-size:14px; font-weight:600;
    box-shadow:0 8px 30px rgba(0,0,0,.4);
    transform:translateY(80px); opacity:0;
    transition:all .35s cubic-bezier(.4,0,.2,1);
    z-index:9999; display:flex; align-items:center; gap:8px;
">✅ Settings saved</div>

<script>
function showPanel(name, btn) {
    document.querySelectorAll('.settings-panel').forEach(p => p.classList.remove('active'));
    document.querySelectorAll('.settings-nav-item').forEach(b => b.classList.remove('active'));
    document.getElementById('panel-' + name).classList.add('active');
    btn.classList.add('active');
}

// Load preferences from localStorage
function loadPrefs() {
    var prefs = JSON.parse(localStorage.getItem('mcad_prefs') || '{}');
    Object.keys(prefs).forEach(function(id) {
        var el = document.getElementById(id);
        if (el && el.type === 'checkbox') el.checked = prefs[id];
    });
    if (prefs.accent) {
        document.querySelectorAll('.color-dot').forEach(d => d.classList.remove('selected'));
        var dot = document.querySelector('[data-accent="' + prefs.accent + '"]');
        if (dot) dot.classList.add('selected');
    }
}

function savePref() {
    var ids = ['notif_events','notif_announcements','notif_prayer','notif_donations','notif_forum',
               'compact_mode','sidebar_open','priv_profile','priv_activity','priv_mentions',
               'sys_prayer_auto','sys_self_register'];
    var prefs = JSON.parse(localStorage.getItem('mcad_prefs') || '{}');
    ids.forEach(function(id) {
        var el = document.getElementById(id);
        if (el) prefs[id] = el.checked;
    });
    localStorage.setItem('mcad_prefs', JSON.stringify(prefs));
}

function setAccent(color, el) {
    document.querySelectorAll('.color-dot').forEach(d => d.classList.remove('selected'));
    el.classList.add('selected');
    var prefs = JSON.parse(localStorage.getItem('mcad_prefs') || '{}');
    prefs.accent = color;
    localStorage.setItem('mcad_prefs', JSON.stringify(prefs));
}

function saveAndToast() {
    savePref();
    var toast = document.getElementById('toast');
    toast.style.transform = 'translateY(0)';
    toast.style.opacity   = '1';
    setTimeout(function() {
        toast.style.transform = 'translateY(80px)';
        toast.style.opacity   = '0';
    }, 2500);
}

loadPrefs();
</script>
<?php include "../includes/footer.php"; ?>
