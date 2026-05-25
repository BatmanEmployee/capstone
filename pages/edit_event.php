<?php
session_start();
include "../config/database.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$id = (int) ($_GET['id'] ?? 0);
$stmt = $conn->prepare("SELECT * FROM events WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$row) {
    header("Location: events.php");
    exit();
}

include "../includes/header.php";
include "../includes/sidebar.php";
?>
<style>
.page-container { margin-left:260px; padding:90px 30px 30px; background:#121212; min-height:100vh; }
.form-card { background:#1e1e1e; border-radius:16px; padding:35px; box-shadow:0 2px 10px rgba(0,0,0,.3); max-width:680px; }
.form-title { font-size:22px; font-weight:700; color:#fff; margin:0 0 25px; }
.form-group { margin-bottom:16px; }
.form-group label { display:block; font-size:13px; font-weight:600; color:#9ca3af; margin-bottom:6px; }
.form-group input, .form-group textarea, .form-group select {
    width:100%; padding:12px 14px; border:1.5px solid #333; background:#242424; color:#fff;
    border-radius:10px; font-size:14px; box-sizing:border-box; transition:border-color .2s; font-family:inherit;
}
.form-group input::placeholder, .form-group textarea::placeholder { color:#555; }
.form-group select option { background:#242424; color:#fff; }
.form-group input:focus, .form-group textarea:focus, .form-group select:focus { outline:none; border-color:#ff00aa; background:#2a2a2a; }
.form-group textarea { min-height:90px; resize:vertical; }
.form-row { display:grid; grid-template-columns:1fr 1fr; gap:15px; }
.btn-row { display:flex; gap:12px; margin-top:10px; }
.submit-btn { flex:1; padding:13px; background:linear-gradient(135deg,#ff4dc4,#ff00aa); color:white; border:none; border-radius:10px; font-size:14px; font-weight:600; cursor:pointer; transition:transform .2s,box-shadow .2s; }
.submit-btn:hover { transform:translateY(-2px); box-shadow:0 8px 20px rgba(255,0,170,.3); }
.cancel-btn { padding:13px 20px; background:#2a2a2a; color:#9ca3af; border:1px solid #333; border-radius:10px; font-size:14px; font-weight:600; cursor:pointer; text-decoration:none; display:flex; align-items:center; }
.cancel-btn:hover { background:#333; color:#e5e7eb; }
</style>

<div class="page-container">
    <div class="form-card">
        <h2 class="form-title">✏️ Edit Event</h2>
        <form method="POST" action="../actions/update_event.php">
            <input type="hidden" name="id" value="<?= $row['id'] ?>">
            <?= csrf_field() ?>

            <div class="form-group">
                <label>Event Title</label>
                <input type="text" name="title" value="<?= htmlspecialchars($row['title']) ?>" required>
            </div>

            <div class="form-group">
                <label>Description</label>
                <textarea name="description"><?= htmlspecialchars($row['description']) ?></textarea>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Date</label>
                    <input type="date" name="event_date" value="<?= $row['event_date'] ?>" required>
                </div>
                <div class="form-group">
                    <label>Time</label>
                    <input type="time" name="event_time" value="<?= $row['event_time'] ?>" required>
                </div>
            </div>

            <div class="form-group">
                <label>Venue</label>
                <input type="text" name="venue" value="<?= htmlspecialchars($row['venue']) ?>" required>
            </div>

            <div class="form-group">
                <label>Status</label>
                <select name="status">
                    <option value="upcoming"  <?= $row['status'] === 'upcoming'  ? 'selected' : '' ?>>Upcoming</option>
                    <option value="ongoing"   <?= $row['status'] === 'ongoing'   ? 'selected' : '' ?>>Ongoing</option>
                    <option value="completed" <?= $row['status'] === 'completed' ? 'selected' : '' ?>>Completed</option>
                </select>
            </div>

            <div class="btn-row">
                <a href="events.php" class="cancel-btn">Cancel</a>
                <button type="submit" class="submit-btn">Update Event</button>
            </div>
        </form>
    </div>
</div>
<?php include "../includes/footer.php"; ?>
