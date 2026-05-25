<?php
session_start();
include "../config/database.php";

if (!csrf_verify()) {
    $_SESSION['error'] = "Invalid request. Please try again.";
    header("Location: ../pages/login.php"); exit();
}

// Brute force protection — max 5 attempts, 5-minute lockout
$max_attempts = 5;
$lockout_time = 300; // 5 minutes in seconds

if (isset($_SESSION['login_attempts']) && $_SESSION['login_attempts'] >= $max_attempts) {
    $elapsed = time() - ($_SESSION['login_last_attempt'] ?? 0);
    if ($elapsed < $lockout_time) {
        $wait = $lockout_time - $elapsed;
        $_SESSION['error'] = "Too many failed attempts. Please wait " . ceil($wait / 60) . " minute(s) before trying again.";
        header("Location: ../pages/login.php"); exit();
    } else {
        // Lockout expired — reset counter
        $_SESSION['login_attempts'] = 0;
    }
}

$email = trim($_POST['email'] ?? '');
$raw   = $_POST['password'] ?? '';

if ($email === '' || $raw === '') {
    $_SESSION['error'] = "Email and password are required.";
    header("Location: ../pages/login.php");
    exit();
}

$stmt = $conn->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();

    // Check if account is suspended
    if (isset($user['status']) && $user['status'] === 'suspended') {
        $_SESSION['error'] = "Your account has been suspended. Please contact the administrator.";
        header("Location: ../pages/login.php");
        exit();
    }

    // Accept bcrypt-hashed passwords (new) or MD5 (legacy demo accounts)
    $valid = password_verify($raw, $user['password'])
          || $user['password'] === md5($raw);

    if ($valid) {
        // Successful login — clear brute force counter, set activity timestamp
        $_SESSION['login_attempts']  = 0;
        $_SESSION['user_id']         = $user['id'];
        $_SESSION['name']            = $user['name'];
        $_SESSION['role']            = $user['role'];
        $_SESSION['community_id']    = $user['community_id'];
        $_SESSION['last_activity']   = time();
        header("Location: ../pages/dashboard.php");
        exit();
    }
}

// Track failed attempt
$_SESSION['login_attempts']     = ($_SESSION['login_attempts'] ?? 0) + 1;
$_SESSION['login_last_attempt'] = time();

$remaining = $max_attempts - $_SESSION['login_attempts'];
if ($remaining > 0) {
    $_SESSION['error'] = "Invalid email or password. $remaining attempt(s) remaining before lockout.";
} else {
    $_SESSION['error'] = "Too many failed attempts. You are locked out for 5 minutes.";
}
header("Location: ../pages/login.php");
exit();
