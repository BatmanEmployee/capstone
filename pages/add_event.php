<?php
session_start();
include "../config/database.php";
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
$role = $_SESSION['role'];
if ($role === 'viewer') { header("Location: dashboard.php"); exit(); }
$today = date('Y-m-d');
include "../includes/header.php";
include "../includes/sidebar.php";
?>
<style>
.page-container { margin-left:260px; padding:90px 30px 40px; background:#121212; min-height:100vh; }
.page-title    { font-size:26px; font-weight:800; color:#fff; margin:0 0 4px; }
.page-subtitle { font-size:14px; color:rgba(255,255,255,.4); margin:0 0 30px; }
.form-card {
    background:#1e1e1e;
    border-radius:18px;
    padding:32px;
    max-width:640px;
    box-shadow:0 4px 24px rgba(0,0,0,.3);
    border:1px solid rgba(255,255,255,.06);
}
.form-group { margin-bottom:18px; }
.form-group label { display:block; font-size:13px; font-weight:600; color:rgba(255,255,255,.55); margin-bottom:8px; text-transform:uppercase; letter-spacing:.5px; }
.form-group input,
.form-group textarea,
.form-group select {
    width:100%; padding:13px 16px;
    background:rgba(255,255,255,.07);
    border:1.5px solid rgba(255,255,255,.1);
    border-radius:10px; color:#fff;
    font-size:15px; outline:none;
    transition:border-color .2s, background .2s;
}
.form-group input::placeholder,
.form-group textarea::placeholder { color:rgba(255,255,255,.25); }
.form-group input:focus,
.form-group textarea:focus,
.form-group select:focus { border-color:#ff00aa; background:rgba(255,255,255,.1); }
.form-group select option { background:#282828; color:#fff; }
.form-group textarea { min-height:100px; resize:vertical; line-height:1.6; }
.form-row { display:grid; grid-template-columns:1fr 1fr; gap:16px; }
.submit-btn {
    width:100%; padding:15px;
    background:linear-gradient(135deg,#ff4dc4,#ff00aa);
    color:#fff; border:none; border-radius:12px;
    font-size:16px; font-weight:700; cursor:pointer;
    transition:all .2s; box-shadow:0 4px 18px rgba(255,0,170,.3);
    margin-top:8px;
}
.submit-btn:hover { opacity:.92; transform:translateY(-2px); box-shadow:0 8px 24px rgba(255,0,170,.4); }
.back-link { display:inline-flex; align-items:center; gap:6px; color:rgba(255,255,255,.4); text-decoration:none; font-size:14px; margin-bottom:22px; transition:color .2s; }
.back-link:hover { color:#ff4dc4; }
</style>

<div class="page-container">
    <a href="events.php" class="back-link">← Back to Events</a>
    <h1 class="page-title">➕ Create New Event</h1>
    <p class="page-subtitle">Fill in the details below to add a new community event</p>

    <div class="form-card">
        <form method="POST" action="../actions/add_event_action.php">
            <?= csrf_field() ?>

            <div class="form-group">
                <label>Event Title *</label>
                <input type="text" name="title" placeholder="e.g. Ramadan Iftar Program" required>
            </div>

            <div class="form-group">
                <label>Description</label>
                <textarea name="description" placeholder="Brief description of the event..."></textarea>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Date *</label>
                    <input type="date" name="event_date" required min="<?= $today ?>">
                </div>
                <div class="form-group">
                    <label>Time *</label>
                    <input type="time" name="event_time" required>
                </div>
            </div>

            <div class="form-group">
                <label>Venue *</label>
                <input type="text" name="venue" placeholder="e.g. Lagao Mosque" required>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Visibility</label>
                    <select name="visibility">
                        <option value="personal">👤 Personal</option>
                        <?php if ($role !== 'viewer'): ?>
                        <option value="community">👥 Community</option>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select name="status">
                        <option value="upcoming">⏳ Upcoming</option>
                        <option value="ongoing">🔵 Ongoing</option>
                        <option value="completed">✅ Completed</option>
                    </select>
                </div>
            </div>

            <button type="submit" class="submit-btn">Create Event</button>
        </form>
    </div>
</div>
<?php include "../includes/footer.php"; ?>
