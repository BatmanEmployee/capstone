<?php
session_start();
include "../config/database.php";
if (!isset($_SESSION['user_id'])) { header("Location: ../pages/login.php"); exit(); }

if (!csrf_verify()) { csrf_abort('../pages/profile.php'); }

$user_id = (int) $_SESSION['user_id'];
$name    = trim($_POST['name'] ?? '');
$email   = trim($_POST['email'] ?? '');

if (empty($name) || empty($email)) {
    header("Location: ../pages/profile.php?error=Name+and+email+are+required"); exit();
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header("Location: ../pages/profile.php?error=Invalid+email+address"); exit();
}

$stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
$stmt->bind_param("si", $email, $user_id);
$stmt->execute();
if ($stmt->get_result()->num_rows > 0) {
    header("Location: ../pages/profile.php?error=Email+already+used+by+another+account"); exit();
}
$stmt->close();

$stmt = $conn->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
$stmt->bind_param("ssi", $name, $email, $user_id);
$stmt->execute();
$stmt->close();

$_SESSION['name']  = $name;
$_SESSION['email'] = $email;

header("Location: ../pages/profile.php?success=Profile+updated+successfully");
exit();
