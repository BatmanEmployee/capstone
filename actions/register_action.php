<?php
session_start();
include "../config/database.php";

if (!csrf_verify()) { csrf_abort('../pages/register.php'); }

$name         = trim($_POST['name']     ?? '');
$email        = trim($_POST['email']    ?? '');
$raw          = $_POST['password']      ?? '';
$role         = $_POST['role']          ?? 'viewer';
$community_id = (int) ($_POST['community_id'] ?? 0);

$allowed_roles = ['imam', 'leader', 'viewer'];

// --- Input validation ---
$errors = [];

if ($name === '') {
    $errors[] = "Name is required.";
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "A valid email address is required.";
}
if (strlen($raw) < 8) {
    $errors[] = "Password must be at least 8 characters.";
}
if (!in_array($role, $allowed_roles)) {
    $errors[] = "Invalid role selected.";
}

if (!empty($errors)) {
    $_SESSION['error'] = implode(' ', $errors);
    header("Location: ../pages/register.php");
    exit();
}

// Duplicate email check
$check = $conn->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
$check->bind_param("s", $email);
$check->execute();
$check->store_result();
if ($check->num_rows > 0) {
    $_SESSION['error'] = "An account with that email already exists.";
    header("Location: ../pages/register.php");
    exit();
}
$check->close();

// Hash with bcrypt
$password = password_hash($raw, PASSWORD_BCRYPT);

$stmt = $conn->prepare(
    "INSERT INTO users (name, email, password, role, community_id, status)
     VALUES (?, ?, ?, ?, ?, 'active')"
);
$stmt->bind_param("ssssi", $name, $email, $password, $role, $community_id);

if ($stmt->execute()) {
    $stmt->close();
    $_SESSION['success'] = "Account created! You can now log in.";
    header("Location: ../pages/login.php");
} else {
    $stmt->close();
    $_SESSION['error'] = "Registration failed. Please try again.";
    header("Location: ../pages/register.php");
}
exit();
