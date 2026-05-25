<?php
session_start();
include "../config/database.php";
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$user_id      = (int) $_SESSION['user_id'];
$role         = $_SESSION['role'];
$community_id = (int) ($_SESSION['community_id'] ?? 0);

$summary = $conn->query("
    SELECT
        COALESCE(SUM(CASE WHEN donation_type='cash' THEN amount ELSE 0 END),0)     AS total_cash,
        COALESCE(SUM(CASE WHEN donation_type='food' THEN quantity ELSE 0 END),0)    AS total_food,
        COALESCE(SUM(CASE WHEN donation_type='supplies' THEN quantity ELSE 0 END),0) AS total_supplies
    FROM donations WHERE community_id = $community_id
")->fetch_assoc();

$distributed = $conn->query("
    SELECT COALESCE(SUM(quantity),0) AS total FROM distributions WHERE community_id = $community_id
")->fetch_assoc();

$donations     = $conn->query("SELECT * FROM donations     WHERE community_id=$community_id ORDER BY created_at DESC LIMIT 30");
$distributions = $conn->query("SELECT * FROM distributions WHERE community_id=$community_id ORDER BY created_at DESC LIMIT 20");

include "../includes/header.php";
include "../includes/sidebar.php";
?>
<style>
.page-container { margin-left:260px; padding:90px 30px 40px; background:#121212; min-height:100vh; color:#fff; }
.page-header { display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:26px; }
.page-title    { font-size:26px; font-weight:800; color:#fff; margin:0 0 4px; }
.page-subtitle { font-size:14px; color:rgba(255,255,255,.4); margin:0; }

/* Stats */
.stats-row { display:grid; grid-template-columns:repeat(4,1fr); gap:14px; margin-bottom:26px; }
.stat-card { background:#1e1e1e; border-radius:14px; padding:20px; display:flex; align-items:center; gap:14px; }
.stat-icon { width:46px; height:46px; border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:20px; flex-shrink:0; }
.stat-num { font-size:22px; font-weight:800; color:#fff; margin:0; line-height:1; }
.stat-lbl { font-size:12px; color:rgba(255,255,255,.4); margin:4px 0 0; }

/* Grid */
.main-grid { display:grid; grid-template-columns:1fr 1fr; gap:20px; }
@media(max-width:900px){ .main-grid{ grid-template-columns:1fr; } }

/* Cards */
.section-card { background:#1e1e1e; border-radius:16px; padding:24px; margin-bottom:20px; border:1px solid rgba(255,255,255,.05); }
.section-title { font-size:17px; font-weight:700; color:#fff; margin:0 0 20px; padding-bottom:14px; border-bottom:1px solid rgba(255,255,255,.07); }

/* Form */
.form-group { margin-bottom:15px; }
.form-group label { display:block; font-size:12px; font-weight:600; color:rgba(255,255,255,.45); margin-bottom:7px; text-transform:uppercase; letter-spacing:.5px; }
.form-group input,
.form-group select,
.form-group textarea {
    width:100%; padding:12px 14px;
    background:rgba(255,255,255,.07);
    border:1.5px solid rgba(255,255,255,.1);
    border-radius:10px; color:#fff; font-size:14px; outline:none;
    transition:border-color .2s;
}
.form-group input::placeholder,
.form-group textarea::placeholder { color:rgba(255,255,255,.25); }
.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus { border-color:#ff00aa; background:rgba(255,255,255,.1); }
.form-group select option { background:#282828; }
.form-group textarea { min-height:72px; resize:vertical; }
.form-row { display:grid; grid-template-columns:1fr 1fr; gap:12px; }

.submit-btn {
    width:100%; padding:13px;
    background:linear-gradient(135deg,#ff4dc4,#ff00aa);
    color:#fff; border:none; border-radius:10px;
    font-size:14px; font-weight:700; cursor:pointer;
    transition:all .2s; box-shadow:0 4px 15px rgba(255,0,170,.25);
    margin-top:4px;
}
.submit-btn:hover { opacity:.9; transform:translateY(-1px); }

/* History list */
.history-item {
    display:flex; align-items:flex-start; gap:12px;
    padding:13px 0; border-bottom:1px solid rgba(255,255,255,.05);
}
.history-item:last-child { border-bottom:none; }
.history-icon { width:38px; height:38px; border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:16px; flex-shrink:0; }
.h-cash     { background:rgba(74,222,128,.15); }
.h-food     { background:rgba(251,191,36,.15); }
.h-supplies { background:rgba(96,165,250,.15); }
.h-dist     { background:rgba(167,139,250,.15); }

.history-body { flex:1; min-width:0; }
.history-name { font-size:14px; font-weight:600; color:#fff; margin:0 0 3px; }
.history-meta { font-size:12px; color:rgba(255,255,255,.4); margin:0; }

.amount-badge {
    font-size:13px; font-weight:700; color:#4ade80;
    background:rgba(74,222,128,.12);
    padding:4px 10px; border-radius:8px; white-space:nowrap;
}
.empty-note { color:rgba(255,255,255,.25); font-size:13px; text-align:center; padding:24px 0; }
</style>

<div class="page-container">
    <div class="page-header">
        <div>
            <h1 class="page-title">💰 Donations & Resources</h1>
            <p class="page-subtitle">Track donations and distributions for your community</p>
        </div>
    </div>

    <!-- Stats -->
    <div class="stats-row">
        <div class="stat-card">
            <div class="stat-icon" style="background:rgba(74,222,128,.15);">💵</div>
            <div>
                <p class="stat-num" style="color:#4ade80;">₱<?= number_format($summary['total_cash'],2) ?></p>
                <p class="stat-lbl">Total Cash</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:rgba(251,191,36,.15);">🍱</div>
            <div>
                <p class="stat-num" style="color:#fbbf24;"><?= $summary['total_food'] ?></p>
                <p class="stat-lbl">Food Items</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:rgba(96,165,250,.15);">📦</div>
            <div>
                <p class="stat-num" style="color:#60a5fa;"><?= $summary['total_supplies'] ?></p>
                <p class="stat-lbl">Supplies</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:rgba(167,139,250,.15);">🤲</div>
            <div>
                <p class="stat-num" style="color:#a78bfa;"><?= $distributed['total'] ?></p>
                <p class="stat-lbl">Distributed</p>
            </div>
        </div>
    </div>

    <div class="main-grid">

        <!-- LEFT: Forms -->
        <div>
            <?php if ($role === 'admin' || $role === 'leader'): ?>
            <!-- Add Donation -->
            <div class="section-card">
                <h3 class="section-title">➕ Record Donation</h3>
                <form method="POST" action="../actions/add_donation.php">
                    <?= csrf_field() ?>
                    <div class="form-group">
                        <label>Donor Name</label>
                        <input type="text" name="donor_name" placeholder="Leave blank for Anonymous">
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Type *</label>
                            <select name="donation_type" required onchange="toggleFields(this.value)">
                                <option value="cash">💵 Cash</option>
                                <option value="food">🍱 Food</option>
                                <option value="supplies">📦 Supplies</option>
                            </select>
                        </div>
                        <div class="form-group" id="amountField">
                            <label>Amount (₱)</label>
                            <input type="number" name="amount" placeholder="0.00" step="0.01" min="0">
                        </div>
                    </div>
                    <div class="form-group" id="qtyField" style="display:none;">
                        <label>Quantity</label>
                        <input type="number" name="quantity" placeholder="0" min="0">
                    </div>
                    <div class="form-group">
                        <label>Remarks</label>
                        <textarea name="remarks" placeholder="Optional notes..."></textarea>
                    </div>
                    <button type="submit" class="submit-btn">💾 Save Donation</button>
                </form>
            </div>

            <!-- Record Distribution -->
            <div class="section-card">
                <h3 class="section-title">🤲 Record Distribution</h3>
                <form method="POST" action="../actions/add_distribution.php">
                    <?= csrf_field() ?>
                    <div class="form-group">
                        <label>Beneficiary *</label>
                        <input type="text" name="beneficiary" placeholder="Name of recipient" required>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Item Name *</label>
                            <input type="text" name="item_name" placeholder="e.g. Rice, Medicine" required>
                        </div>
                        <div class="form-group">
                            <label>Quantity *</label>
                            <input type="number" name="quantity" placeholder="0" required min="1">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Distribution Date *</label>
                        <input type="date" name="distributed_at" required value="<?= date('Y-m-d') ?>">
                    </div>
                    <button type="submit" class="submit-btn">💾 Save Distribution</button>
                </form>
            </div>
            <?php endif; ?>
        </div>

        <!-- RIGHT: History -->
        <div>
            <!-- Donation History -->
            <div class="section-card">
                <h3 class="section-title">📋 Donation History</h3>
                <?php if ($donations && $donations->num_rows > 0):
                    while ($d = $donations->fetch_assoc()):
                        $icon = $d['donation_type'] === 'cash' ? '💵' : ($d['donation_type'] === 'food' ? '🍱' : '📦');
                        $cls  = 'h-' . $d['donation_type'];
                        $val  = $d['donation_type'] === 'cash'
                                ? '₱' . number_format($d['amount'], 2)
                                : $d['quantity'] . ' pcs';
                ?>
                <div class="history-item">
                    <div class="history-icon <?= $cls ?>"><?= $icon ?></div>
                    <div class="history-body">
                        <p class="history-name"><?= htmlspecialchars($d['donor_name'] ?: 'Anonymous') ?></p>
                        <p class="history-meta"><?= ucfirst($d['donation_type']) ?> · <?= date('M d, Y', strtotime($d['created_at'])) ?></p>
                    </div>
                    <span class="amount-badge"><?= $val ?></span>
                </div>
                <?php endwhile; else: ?>
                <p class="empty-note">No donations recorded yet.</p>
                <?php endif; ?>
            </div>

            <!-- Distribution History -->
            <div class="section-card">
                <h3 class="section-title">🤲 Distribution History</h3>
                <?php if ($distributions && $distributions->num_rows > 0):
                    while ($d = $distributions->fetch_assoc()):
                ?>
                <div class="history-item">
                    <div class="history-icon h-dist">📤</div>
                    <div class="history-body">
                        <p class="history-name"><?= htmlspecialchars($d['beneficiary']) ?></p>
                        <p class="history-meta"><?= htmlspecialchars($d['item_name']) ?> · <?= date('M d, Y', strtotime($d['distributed_at'])) ?></p>
                    </div>
                    <span class="amount-badge" style="color:#a78bfa;background:rgba(167,139,250,.12);"><?= $d['quantity'] ?> pcs</span>
                </div>
                <?php endwhile; else: ?>
                <p class="empty-note">No distributions recorded yet.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
function toggleFields(type) {
    document.getElementById('amountField').style.display = type === 'cash' ? '' : 'none';
    document.getElementById('qtyField').style.display    = type !== 'cash' ? '' : 'none';
}
</script>

<?php include "../includes/footer.php"; ?>
