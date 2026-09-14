<?php
session_start();

// Include database configuration
include "../config/database.php";

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../pages/login.php");
    exit();
}

// CSRF protection
if (!csrf_verify()) {
    csrf_abort('../pages/profile.php');
}

// Get user ID from session
$user_id = (int) $_SESSION['user_id'];

// Sanitize and validate inputs
$current_password = trim($_POST['current_password']);
$new_password = trim($_POST['new_password']);
$confirm_password = trim($_POST['confirm_password']);

// Check if all fields are provided
if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
    header("Location: ../pages/profile.php?tab=security&error=All+password+fields+are+required");
    exit();
}

// Check if new passwords match
if ($new_password !== $confirm_password) {
    header("Location: ../pages/profile.php?tab=security&error=New+passwords+do+not+match");
    exit();
}

// Check if new password is at least 8 characters long
if (strlen($new_password) < 8) {
    header("Location: ../pages/profile.php?tab=security&error=Password+must+be+at+least+8+characters");
    exit();
}

// Prepare and execute the query to get the user's current password
$stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$stmt->close();

// Verify the current password
$valid = password_verify($current_password, $row['password']) || (md5($current_password) === $row['password']);

if (!$valid) {
    header("Location: ../pages/profile.php?tab=security&error=Current+password+is+incorrect");
    exit();
}

// Hash the new password
$new_password_hash = password_hash($new_password, PASSWORD_BCRYPT);

// Update the user's password in the database
$stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
$stmt->bind_param("si", $new_password_hash, $user_id);
if (!$stmt->execute()) {
    error_log("Error executing password update: " . $stmt->error);
    header("Location: ../pages/profile.php?tab=security&error=Failed+to+update+password");
    exit();
}

// Close the statement
$stmt->close();

// Redirect to the profile page with success message
header("Location: ../pages/profile.php?tab=security&success=Password+changed+successfully");
exit();

