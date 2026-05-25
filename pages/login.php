<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Sign In — RMinder</title>
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

body::before {
    content: '';
    position: fixed;
    top: -200px;
    left: -200px;
    width: 500px;
    height: 500px;
    background: radial-gradient(circle, rgba(255,0,170,.1) 0%, transparent 70%);
    pointer-events: none;
}

.login-card {
    background: #1a1a1a;
    border-radius: 24px;
    padding: 42px 44px;
    width: 100%;
    max-width: 420px;
    box-shadow: 0 24px 80px rgba(0,0,0,.6), 0 0 0 1px rgba(255,255,255,.05);
    animation: fadeUp .4s ease;
}

@keyframes fadeUp {
    from { opacity: 0; transform: translateY(20px); }
    to   { opacity: 1; transform: translateY(0); }
}

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
.subtitle { font-size: 13px; color: #6b7280; margin-bottom: 30px; }

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
.alert-info    { background: rgba(59,130,246,.1); border: 1px solid rgba(59,130,246,.3); color: #93c5fd; }

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
.form-group input {
    width: 100%;
    padding: 13px 16px;
    background: #242424;
    border: 1.5px solid #333;
    border-radius: 12px;
    font-size: 14px;
    color: #fff;
    outline: none;
    font-family: inherit;
    transition: border-color .2s, box-shadow .2s, background .2s;
}
.form-group input::placeholder { color: #555; }
.form-group input:focus {
    border-color: #ff00aa;
    background: #2a2a2a;
    box-shadow: 0 0 0 3px rgba(255,0,170,.12);
}

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
    margin-top: 4px;
}
.submit-btn:hover {
    opacity: .92;
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(255,0,170,.3);
}
.submit-btn:active { transform: translateY(0); }
.submit-btn:disabled { opacity: .5; cursor: not-allowed; transform: none; }

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

@media (max-width: 480px) {
    .login-card { padding: 30px 24px; }
}
</style>
</head>
<body>

<div class="login-card">

    <div class="logo-row">
        <div class="logo-icon">☪</div>
        <span class="logo-name">RMinder</span>
    </div>

    <h1>Welcome back</h1>
    <p class="subtitle">Sign in to continue to your community portal</p>

    <?php
    include_once "../config/database.php";
    if (isset($_GET['timeout'])): ?>
    <div class="alert alert-info">⏱ Your session expired. Please sign in again.</div>
    <?php endif;
    if (isset($_SESSION['error'])): ?>
    <div class="alert alert-error">⚠️ <?= htmlspecialchars($_SESSION['error']) ?></div>
    <?php unset($_SESSION['error']); endif;
    if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success">✅ <?= htmlspecialchars($_SESSION['success']) ?></div>
    <?php unset($_SESSION['success']); endif; ?>

    <form method="POST" action="../actions/login_action.php" id="loginForm">
        <?= csrf_field() ?>

        <div class="form-group">
            <label>Email Address</label>
            <input type="email" name="email" placeholder="you@email.com" required autocomplete="email">
        </div>

        <div class="form-group">
            <label>Password</label>
            <div class="password-wrap">
                <input type="password" name="password" id="pwField"
                       placeholder="Your password" required autocomplete="current-password">
                <button type="button" class="toggle-pw" onclick="togglePw(this)" title="Show/hide">👁</button>
            </div>
        </div>

        <button type="submit" class="submit-btn" id="submitBtn">Sign In</button>
    </form>

    <div class="bottom-links">
        New to RMinder? <a href="register.php">Create an account</a>
        <span class="dot">·</span>
        <a href="../index.php">Back to home</a>
    </div>

</div>

<script>
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

document.getElementById('loginForm').addEventListener('submit', function() {
    const btn = document.getElementById('submitBtn');
    btn.textContent = 'Signing in...';
    btn.disabled = true;
});
</script>
</body>
</html>