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
$community_id = (int) ($u['community_id'] ?? 0);

// Fetch threads (community-scoped or global pinned)
$threads = [];
$stmt = $conn->prepare("
    SELECT t.*, u.name AS author,
           (SELECT COUNT(*) FROM forum_replies r WHERE r.thread_id = t.id) AS reply_count
    FROM forum_threads t
    JOIN users u ON t.user_id = u.id
    WHERE t.community_id = ? OR t.community_id IS NULL OR t.is_pinned = 1
    ORDER BY t.is_pinned DESC, t.created_at DESC
");
$stmt->bind_param("i", $community_id);
$stmt->execute();
$tRes = $stmt->get_result();
while ($r = $tRes->fetch_assoc()) {
    $threads[] = $r;
}
$stmt->close();

include "../includes/header.php";
include "../includes/sidebar.php";
?>
<style>
.page-container { margin-left:260px; padding:90px 30px 30px; background:#121212; min-height:100vh; }
.page-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:25px; }
.page-title  { font-size:24px; font-weight:700; color:#fff; margin:0; }
.page-subtitle { font-size:14px; color:#9ca3af; margin:5px 0 0 0; }
.content-grid { display:grid; grid-template-columns:1fr 340px; gap:25px; }
@media(max-width:1100px){ .content-grid{grid-template-columns:1fr;} }
.section-card { background:#1e1e1e; border-radius:16px; padding:25px; box-shadow:0 2px 10px rgba(0,0,0,.3); margin-bottom:20px; }
.section-title { font-size:18px; font-weight:600; color:#fff; margin:0 0 20px 0; }
.thread-item { padding:16px; border-bottom:1px solid #2a2a2a; cursor:pointer; transition:background .2s; }
.thread-item:last-child { border-bottom:none; }
.thread-item:hover { background:#2a2a2a; border-radius:10px; }
.thread-title { font-size:15px; font-weight:600; color:#e5e7eb; margin:0 0 5px; text-decoration:none; }
.thread-title:hover { color:#ff00aa; }
.thread-meta  { font-size:12px; color:#6b7280; }
.pin-badge { background:rgba(251,191,36,.15); color:#fbbf24; font-size:10px; padding:2px 8px; border-radius:10px; font-weight:600; margin-left:8px; }
.mod-btns { display:flex; gap:6px; margin-top:8px; }
.mod-btn { font-size:11px; padding:4px 10px; border:none; border-radius:6px; cursor:pointer; font-weight:600; }
.mod-pin   { background:rgba(251,191,36,.15); color:#fbbf24; }
.mod-delete{ background:rgba(220,38,38,.15); color:#f87171; }
.form-group { margin-bottom:14px; }
.form-group label { display:block; font-size:13px; font-weight:600; color:#9ca3af; margin-bottom:5px; }
.form-group input, .form-group textarea {
    width:100%; padding:11px 14px; border:1.5px solid #333; background:#242424; color:#fff;
    border-radius:10px; font-size:14px; box-sizing:border-box; transition:border-color .2s; font-family:inherit;
}
.form-group input::placeholder, .form-group textarea::placeholder { color:#555; }
.form-group textarea { min-height:100px; resize:vertical; }
.form-group input:focus, .form-group textarea:focus { outline:none; border-color:#ff00aa; background:#2a2a2a; }
.submit-btn {
    width:100%; padding:13px; background:linear-gradient(135deg,#ff4dc4,#ff00aa);
    color:white; border:none; border-radius:10px; font-size:14px;
    font-weight:600; cursor:pointer; transition:transform .2s,box-shadow .2s;
}
.submit-btn:hover { transform:translateY(-2px); box-shadow:0 8px 20px rgba(255,0,170,.3); }
.empty-state { text-align:center; padding:50px 20px; color:#6b7280; }
.empty-state div { font-size:48px; margin-bottom:15px; }
.reply-count { background:rgba(2,132,199,.15); color:#38bdf8; font-size:11px; padding:2px 8px; border-radius:10px; font-weight:600; }
</style>

<div class="page-container">
    <div class="page-header">
        <div>
            <h1 class="page-title">💬 Community Forum</h1>
            <p class="page-subtitle">Discuss, coordinate, and share with your community</p>
        </div>
    </div>

    <div class="content-grid">
        <!-- Threads List -->
        <div>
            <div class="section-card">
                <h3 class="section-title">Discussion Threads</h3>

                <?php if (!empty($threads)): ?>
                    <?php foreach ($threads as $t): ?>
                    <div class="thread-item">
                        <div style="display:flex; align-items:center; flex-wrap:wrap; gap:6px;">
                            <a href="forum_thread.php?id=<?= $t['id'] ?>" class="thread-title">
                                <?= htmlspecialchars($t['title']) ?>
                            </a>
                            <?php if ($t['is_pinned']): ?>
                                <span class="pin-badge">📌 Pinned</span>
                            <?php endif; ?>
                            <span class="reply-count">💬 <?= $t['reply_count'] ?> replies</span>
                        </div>
                        <div class="thread-meta">
                            Posted by <strong><?= htmlspecialchars($t['author']) ?></strong>
                            &bull; <?= date('M d, Y h:i A', strtotime($t['created_at'])) ?>
                        </div>
                        <p style="font-size:13px;color:#4b5563;margin:6px 0 0;">
                            <?= htmlspecialchars(substr($t['body'], 0, 120)) ?><?= strlen($t['body']) > 120 ? '...' : '' ?>
                        </p>

                        <?php if (in_array($role, ['admin','imam'])): ?>
                        <div class="mod-btns">
                            <form method="POST" action="../actions/moderate_thread.php" style="display:inline;">
                                <?= csrf_field() ?>
                                <input type="hidden" name="thread_id" value="<?= $t['id'] ?>">
                                <input type="hidden" name="action" value="pin">
                                <button class="mod-btn mod-pin" type="submit">
                                    <?= $t['is_pinned'] ? '📌 Unpin' : '📌 Pin' ?>
                                </button>
                            </form>
                            <form method="POST" action="../actions/moderate_thread.php" style="display:inline;"
                                  onsubmit="return confirm('Delete this thread and all its replies?')">
                                <?= csrf_field() ?>
                                <input type="hidden" name="thread_id" value="<?= $t['id'] ?>">
                                <input type="hidden" name="action" value="delete">
                                <button class="mod-btn mod-delete" type="submit">🗑 Delete</button>
                            </form>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <div>💬</div>
                        <p>No threads yet. Be the first to start a discussion!</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- New Thread Form -->
        <div>
            <div class="section-card">
                <h3 class="section-title">✏️ Start a Discussion</h3>
                <form method="POST" action="../actions/add_thread.php">
                    <?= csrf_field() ?>
                    <div class="form-group">
                        <label>Title</label>
                        <input type="text" name="title" placeholder="What is this about?" required maxlength="255">
                    </div>
                    <div class="form-group">
                        <label>Message</label>
                        <textarea name="body" placeholder="Share your thoughts..." required></textarea>
                    </div>
                    <button type="submit" class="submit-btn">Post Thread</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php include "../includes/footer.php"; ?>
