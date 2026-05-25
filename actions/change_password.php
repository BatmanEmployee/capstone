<?php
session_start();
include "../config/database.php";
if (!isset($_SESSION['user_id'])) { header("Location: ../pages/login.php"); exit(); }

if (!csrf_verify()) { csrf_abort('../pages/profile.php'); }

$user_id = (int) $_SESSION['user_id'];
$current = $_POST['current_password'] ?? '';
$new     = $_POST['new_password'] ?? '';
$confirm = $_POST['confirm_password'] ?? '';

if (empty($current) || empty($new) || empty($confirm)) {
    header("Location: ../pages/profile.php?tab=security&error=All+password+fields+are+required"); exit();
}
if ($new !== $confirm) {
    header("Location: ../pages/profile.php?tab=security&error=New+passwords+do+not+match"); exit();
}
if (strlen($new) < 8) {
    header("Location: ../pages/profile.php?tab=security&error=Password+must+be+at+least+8+characters"); exit();
}

$stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$stmt->close();

$valid = password_verify($current, $row['password']) || (md5($current) === $row['password']);
if (!$valid) {
    header("Location: ../pages/profile.php?tab=security&error=Current+password+is+incorrect"); exit();
}

$hash = password_hash($new, PASSWORD_BCRYPT);
$stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
$stmt->bind_param("si", $hash, $user_id);
$stmt->execute();
$stmt->close();

header("Location: ../pages/profile.php?tab=security&success=Password+changed+successfully");
exit();
