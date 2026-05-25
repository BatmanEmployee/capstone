<?php
session_start();
include "../config/database.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$thread_id = (int) ($_GET['id'] ?? 0);
if ($thread_id === 0) {
    header("Location: forum.php");
    exit();
}

$user_id = (int) $_SESSION['user_id'];
$role    = $_SESSION['role'];

// Fetch thread
$stmt = $conn->prepare("
    SELECT t.*, u.name AS author
    FROM forum_threads t
    JOIN users u ON t.user_id = u.id
    WHERE t.id = ?
");
$stmt->bind_param("i", $thread_id);
$stmt->execute();
$tRes = $stmt->get_result();
if ($tRes->num_rows === 0) {
    $stmt->close();
    header("Location: forum.php");
    exit();
}
$thread = $tRes->fetch_assoc();
$stmt->close();

// Fetch replies
$replies = [];
$stmt = $conn->prepare("
    SELECT r.*, u.name AS author, u.role AS author_role
    FROM forum_replies r
    JOIN users u ON r.user_id = u.id
    WHERE r.thread_id = ?
    ORDER BY r.created_at ASC
");
$stmt->bind_param("i", $thread_id);
$stmt->execute();
$rRes = $stmt->get_result();
while ($r = $rRes->fetch_assoc()) {
    $replies[] = $r;
}
$stmt->close();

include "../includes/header.php";
include "../includes/sidebar.php";
?>
<style>
.page-container { margin-left:260px; padding:90px 30px 30px; background:#121212; min-height:100vh; }
.back-link { display:inline-flex; align-items:center; gap:6px; color:#9ca3af; text-decoration:none; font-size:14px; margin-bottom:20px; }
.back-link:hover { color:#ff00aa; }
.thread-card { background:#1e1e1e; border-radius:16px; padding:30px; box-shadow:0 2px 10px rgba(0,0,0,.3); margin-bottom:20px; }
.thread-title { font-size:22px; font-weight:700; color:#fff; margin:0 0 10px; }
.thread-meta  { font-size:13px; color:#9ca3af; margin-bottom:20px; }
.thread-body  { font-size:15px; color:#d1d5db; line-height:1.7; white-space:pre-wrap; }
.pin-badge { background:rgba(251,191,36,.15); color:#fbbf24; font-size:10px; padding:2px 8px; border-radius:10px; font-weight:600; }
.replies-section { margin-top:10px; }
.reply-card { background:#1e1e1e; border-radius:12px; padding:20px; box-shadow:0 2px 8px rgba(0,0,0,.3); margin-bottom:12px; }
.reply-author { font-weight:600; font-size:14px; color:#e5e7eb; }
.reply-role { background:rgba(2,132,199,.15); color:#38bdf8; font-size:10px; padding:2px 8px; border-radius:10px; font-weight:600; margin-left:6px; }
.reply-body { font-size:14px; color:#d1d5db; margin:8px 0 0; line-height:1.6; white-space:pre-wrap; }
.reply-time { font-size:11px; color:#6b7280; margin-top:6px; }
.reply-form-card { background:#1e1e1e; border-radius:16px; padding:25px; box-shadow:0 2px 10px rgba(0,0,0,.3); }
.form-group { margin-bottom:14px; }
.form-group label { display:block; font-size:13px; font-weight:600; color:#9ca3af; margin-bottom:5px; }
.form-group textarea { width:100%; padding:12px 14px; border:1.5px solid #333; background:#242424; color:#fff; border-radius:10px; font-size:14px; min-height:120px; resize:vertical; box-sizing:border-box; font-family:inherit; }
.form-group textarea::placeholder { color:#555; }
.form-group textarea:focus { outline:none; border-color:#ff00aa; background:#2a2a2a; }
.submit-btn { width:100%; padding:13px; background:linear-gradient(135deg,#ff4dc4,#ff00aa); color:white; border:none; border-radius:10px; font-size:14px; font-weight:600; cursor:pointer; transition:transform .2s,box-shadow .2s; }
.submit-btn:hover { transform:translateY(-2px); box-shadow:0 8px 20px rgba(255,0,170,.3); }
.count-label { font-size:15px; font-weight:600; color:#e5e7eb; margin-bottom:15px; }
</style>

<div class="page-container">
    <a href="forum.php" class="back-link">← Back to Forum</a>

    <!-- Thread -->
    <div class="thread-card">
        <h1 class="thread-title">
            <?= htmlspecialchars($thread['title']) ?>
            <?php if ($thread['is_pinned']): ?>
                <span class="pin-badge">📌 Pinned</span>
            <?php endif; ?>
        </h1>
        <div class="thread-meta">
            Posted by <strong><?= htmlspecialchars($thread['author']) ?></strong>
            &bull; <?= date('F d, Y h:i A', strtotime($thread['created_at'])) ?>
        </div>
        <div class="thread-body"><?= htmlspecialchars($thread['body']) ?></div>
    </div>

    <!-- Replies -->
    <div class="replies-section">
        <p class="count-label">💬 <?= count($replies) ?> <?= count($replies) === 1 ? 'Reply' : 'Replies' ?></p>

        <?php foreach ($replies as $r): ?>
        <div class="reply-card">
            <div>
                <span class="reply-author"><?= htmlspecialchars($r['author']) ?></span>
                <span class="reply-role"><?= ucfirst($r['author_role']) ?></span>
            </div>
            <p class="reply-body"><?= htmlspecialchars($r['body']) ?></p>
            <p class="reply-time"><?= date('M d, Y h:i A', strtotime($r['created_at'])) ?></p>
        </div>
        <?php endforeach; ?>

        <!-- Reply Form -->
        <div class="reply-form-card">
            <h3 style="font-size:16px;font-weight:600;color:#fff;margin:0 0 15px;">Write a Reply</h3>
            <form method="POST" action="../actions/add_reply.php">
                <?= csrf_field() ?>
                <input type="hidden" name="thread_id" value="<?= $thread_id ?>">
                <div class="form-group">
                    <label>Your Reply</label>
                    <textarea name="body" placeholder="Share your thoughts..." required></textarea>
                </div>
                <button type="submit" class="submit-btn">Post Reply</button>
            </form>
        </div>
    </div>
</div>
<?php include "../includes/footer.php"; ?>
