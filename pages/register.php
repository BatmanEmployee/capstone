<?php
session_start();
include "../config/database.php";

$communities = [];
$cRes = $conn->query("SELECT id, name FROM communities ORDER BY name ASC");
while ($c = $cRes->fetch_assoc()) { $communities[] = $c; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Create Account — RMinder</title>
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body {
    font-family: 'Segoe UI', Arial, sans-serif;
    background: #0a0a0a;
    color: #fff;
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 30px 20px;
}

/* Background glow */
body::before {
    content: '';
    position: fixed;
    top: -200px;
    right: -200px;
    width: 500px;
    height: 500px;
    background: radial-gradient(circle, rgba(255,0,170,.12) 0%, transparent 70%);
    pointer-events: none;
}

.register-card {
    background: #1a1a1a;
    border-radius: 24px;
    padding: 42px 44px;
    width: 100%;
    max-width: 560px;
    box-shadow: 0 24px 80px rgba(0,0,0,.6), 0 0 0 1px rgba(255,255,255,.05);
    animation: fadeUp .4s ease;
}

@keyframes fadeUp {
    from { opacity: 0; transform: translateY(20px); }
    to   { opacity: 1; transform: translateY(0); }
}

/* Header */
.logo-row {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 30px;
}
.logo-icon {
    width: 38px;
    height: 38px;
    background: linear-gradient(135deg, #ff4dc4, #ff00aa);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    flex-shrink: 0;
}
.logo-name { font-size: 18px; font-weight: 700; letter-spacing: -.3px; }

h1 { font-size: 24px; font-weight: 700; margin-bottom: 4px; }
.subtitle { font-size: 13px; color: #6b7280; margin-bottom: 28px; line-height: 1.5; }

/* Alerts */
.alert {
    padding: 12px 16px;
    border-radius: 10px;
    margin-bottom: 20px;
    font-size: 13px;
    display: flex;
    align-items: flex-start;
    gap: 8px;
    animation: fadeIn .3s ease;
}
@keyframes fadeIn { from { opacity:0; transform:translateY(-6px); } to { opacity:1; transform:translateY(0); } }
.alert-error   { background: rgba(239,68,68,.1);  border: 1px solid rgba(239,68,68,.3);  color: #fca5a5; }
.alert-success { background: rgba(34,197,94,.1);  border: 1px solid rgba(34,197,94,.3);  color: #86efac; }

/* Form rows */
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
.form-group { margin-bottom: 16px; }
.form-group label {
    display: block;
    font-size: 11px;
    font-weight: 600;
    color: #9ca3af;
    margin-bottom: 7px;
    text-transform: uppercase;
    letter-spacing: .6px;
}

.form-group input,
.form-group select {
    width: 100%;
    padding: 13px 16px;
    background: #242424;
    border: 1.5px solid #333;
    border-radius: 12px;
    font-size: 14px;
    color: #fff;
    transition: border-color .2s, box-shadow .2s, background .2s;
    outline: none;
    font-family: inherit;
}
.form-group input::placeholder { color: #555; }
.form-group input:focus,
.form-group select:focus {
    border-color: #ff00aa;
    background: #2a2a2a;
    box-shadow: 0 0 0 3px rgba(255,0,170,.12);
}
.form-group select { cursor: pointer; }
.form-group select option { background: #242424; }

/* Password field */
.password-wrap { position: relative; }
.password-wrap input { padding-right: 48px; }
.toggle-pw {
    position: absolute;
    right: 14px;
    top: 50%;
    transform: translateY(-50%);
    cursor: pointer;
    color: #555;
    font-size: 15px;
    background: none;
    border: none;
    padding: 4px;
    line-height: 1;
    transition: color .2s;
}
.toggle-pw:hover { color: #ff00aa; }

/* Password strength */
.strength-bar {
    height: 4px;
    border-radius: 4px;
    background: #333;
    margin-top: 8px;
    overflow: hidden;
}
.strength-fill {
    height: 100%;
    border-radius: 4px;
    transition: width .35s ease, background .35s ease;
    width: 0;
}
.strength-label { font-size: 11px; color: #555; margin-top: 5px; font-weight: 500; }

/* Role selection */
.role-section-label {
    font-size: 11px;
    font-weight: 600;
    color: #9ca3af;
    text-transform: uppercase;
    letter-spacing: .6px;
    margin-bottom: 10px;
}
.role-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 10px;
    margin-bottom: 20px;
}
.role-card {
    background: #242424;
    border: 1.5px solid #333;
    border-radius: 14px;
    padding: 16px 10px 14px;
    text-align: center;
    cursor: pointer;
    transition: border-color .2s, background .2s, transform .15s, box-shadow .2s;
    user-select: none;
    position: relative;
}
.role-card:hover {
    border-color: #ff4dc4;
    background: #2a2a2a;
    transform: translateY(-2px);
}
.role-card.selected {
    border-color: #ff00aa;
    background: rgba(255,0,170,.07);
    box-shadow: 0 0 0 1px rgba(255,0,170,.2), 0 4px 20px rgba(255,0,170,.1);
    transform: translateY(-2px);
}
.role-card input[type=radio] { display: none; }
.role-check {
    position: absolute;
    top: 8px;
    right: 8px;
    width: 16px;
    height: 16px;
    background: #ff00aa;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 9px;
    opacity: 0;
    transition: opacity .2s;
}
.role-card.selected .role-check { opacity: 1; }
.role-icon { font-size: 26px; margin-bottom: 8px; line-height: 1; display: block; }
.role-name { font-size: 12px; font-weight: 700; color: #e5e7eb; margin-bottom: 4px; }
.role-desc { font-size: 10px; color: #6b7280; line-height: 1.45; }
.role-card.selected .role-name { color: #ff4dc4; }
.role-card.selected .role-desc { color: #9ca3af; }

/* Submit */
.submit-btn {
    width: 100%;
    padding: 14px;
    background: linear-gradient(135deg, #ff4dc4, #ff00aa);
    color: white;
    border: none;
    border-radius: 12px;
    font-size: 15px;
    font-weight: 700;
    cursor: pointer;
    transition: opacity .2s, transform .2s, box-shadow .2s;
    font-family: inherit;
    letter-spacing: .2px;
}
.submit-btn:hover {
    opacity: .92;
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(255,0,170,.3);
}
.submit-btn:active { transform: translateY(0); }
.submit-btn:disabled { opacity: .5; cursor: not-allowed; transform: none; }

/* Bottom links */
.bottom-links {
    text-align: center;
    margin-top: 22px;
    font-size: 13px;
    color: #6b7280;
}
.bottom-links a {
    color: #ff4dc4;
    text-decoration: none;
    font-weight: 600;
    transition: color .2s;
}
.bottom-links a:hover { color: #ff00aa; }
.dot { margin: 0 8px; color: #444; }

@media (max-width: 560px) {
    .register-card { padding: 30px 24px; }
    .form-row { grid-template-columns: 1fr; gap: 0; }
    .role-grid { gap: 8px; }
    .role-card { padding: 12px 6px; }
    .role-icon { font-size: 22px; }
    .role-name { font-size: 11px; }
}
</style>
</head>
<body>

<div class="register-card">

    <!-- Logo -->
    <div class="logo-row">
        <div class="logo-icon">☪</div>
        <span class="logo-name">RMinder</span>
    </div>

    <h1>Create Account</h1>
    <p class="subtitle">Join the MCAD community management platform.<br>Select your role to get the right access level.</p>

    <!-- Flash Messages -->
    <?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-error">⚠️ <?= htmlspecialchars($_SESSION['error']) ?></div>
    <?php unset($_SESSION['error']); endif; ?>

    <?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success">✅ <?= htmlspecialchars($_SESSION['success']) ?></div>
    <?php unset($_SESSION['success']); endif; ?>

    <form method="POST" action="../actions/register_action.php" id="registerForm" novalidate>
        <?= csrf_field() ?>

        <!-- Name + Email -->
        <div class="form-row">
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="name" placeholder="e.g. Ahmad Sali" required autocomplete="name">
            </div>
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" placeholder="you@email.com" required autocomplete="email">
            </div>
        </div>

        <!-- Password -->
        <div class="form-group">
            <label>Password</label>
            <div class="password-wrap">
                <input type="password" name="password" id="pwField"
                       placeholder="Minimum 8 characters" required
                       oninput="checkStrength(this.value)" autocomplete="new-password">
                <button type="button" class="toggle-pw" onclick="togglePw(this)" title="Show/hide password">
                    👁
                </button>
            </div>
            <div class="strength-bar"><div class="strength-fill" id="strengthFill"></div></div>
            <div class="strength-label" id="strengthLabel">Enter a password to check strength</div>
        </div>

        <!-- Community -->
        <div class="form-group">
            <label>Community</label>
            <select name="community_id" required>
                <option value="">— Select your community —</option>
                <?php foreach ($communities as $c): ?>
                    <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Role Selection -->
        <div class="role-section-label">Select Your Role</div>
        <div class="role-grid">

            <label class="role-card selected" id="card-viewer" onclick="selectRole('viewer', this)">
                <input type="radio" name="role" value="viewer" checked>
                <div class="role-check">✓</div>
                <span class="role-icon">👤</span>
                <div class="role-name">Member</div>
                <div class="role-desc">View events, announcements &amp; schedules</div>
            </label>

            <label class="role-card" id="card-leader" onclick="selectRole('leader', this)">
                <input type="radio" name="role" value="leader">
                <div class="role-check">✓</div>
                <span class="role-icon">🏛️</span>
                <div class="role-name">Community Leader</div>
                <div class="role-desc">Manage activities, attendance &amp; donations</div>
            </label>

            <label class="role-card" id="card-imam" onclick="selectRole('imam', this)">
                <input type="radio" name="role" value="imam">
                <div class="role-check">✓</div>
                <span class="role-icon">☪️</span>
                <div class="role-name">Imam</div>
                <div class="role-desc">Post prayer schedules &amp; announcements</div>
            </label>

        </div>

        <button type="submit" class="submit-btn" id="submitBtn">
            Create Account
        </button>
    </form>

    <div class="bottom-links">
        Already have an account? <a href="login.php">Sign in</a>
        <span class="dot">·</span>
        <a href="../index.php">Back to home</a>
    </div>

</div>

<script>
function selectRole(role, card) {
    document.querySelectorAll('.role-card').forEach(c => {
        c.classList.remove('selected');
        c.querySelector('input[type=radio]').checked = false;
    });
    card.classList.add('selected');
    card.querySelector('input[type=radio]').checked = true;
}

function togglePw(btn) {
    const f = document.getElementById('pwField');
    if (f.type === 'password') {
        f.type = 'text';
        btn.textContent = '🙈';
    } else {
        f.type = 'password';
        btn.textContent = '👁';
    }
}

function checkStrength(val) {
    const fill   = document.getElementById('strengthFill');
    const label  = document.getElementById('strengthLabel');
    if (!val) {
        fill.style.width = '0';
        label.textContent = 'Enter a password to check strength';
        label.style.color = '#555';
        return;
    }
    let score = 0;
    if (val.length >= 8)          score++;
    if (val.length >= 12)         score++;
    if (/[A-Z]/.test(val))        score++;
    if (/[0-9]/.test(val))        score++;
    if (/[^A-Za-z0-9]/.test(val)) score++;

    const levels = [
        { w: '20%',  c: '#ef4444', t: 'Very Weak — add more characters' },
        { w: '40%',  c: '#f97316', t: 'Weak — try adding numbers' },
        { w: '60%',  c: '#eab308', t: 'Fair — add uppercase or symbols' },
        { w: '80%',  c: '#84cc16', t: 'Good — almost there!' },
        { w: '100%', c: '#22c55e', t: 'Strong password' },
    ];
    const idx = Math.min(score, 4);
    fill.style.width      = levels[idx].w;
    fill.style.background = levels[idx].c;
    label.textContent     = levels[idx].t;
    label.style.color     = levels[idx].c;
}

document.getElementById('registerForm').addEventListener('submit', function(e) {
    const name  = this.querySelector('[name=name]').value.trim();
    const email = this.querySelector('[name=email]').value.trim();
    const pw    = this.querySelector('[name=password]').value;
    const comm  = this.querySelector('[name=community_id]').value;

    if (!name || !email || !pw || !comm) {
        e.preventDefault();
        alert('Please fill in all fields before continuing.');
        return;
    }
    if (pw.length < 8) {
        e.preventDefault();
        alert('Password must be at least 8 characters.');
        return;
    }

    const btn = document.getElementById('submitBtn');
    btn.textContent = 'Creating Account...';
    btn.disabled = true;
});
</script>
</body>
</html>