<?php
session_start();
include "../config/database.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../pages/login.php");
    exit();
}

if (!csrf_verify()) { csrf_abort('../pages/announcements.php'); }

if ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'imam') {
    header("Location: ../pages/announcements.php");
    exit();
}

$user_id = (int) $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT community_id FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$u = $stmt->get_result()->fetch_assoc();
$stmt->close();
$community_id = (int) $u['community_id'];

$title   = trim($_POST['title']   ?? '');
$message = trim($_POST['message'] ?? '');

$allowed_cats = ['prayer_schedule','event_reminder','charity_drive','community_advisory'];
$category = in_array($_POST['category'] ?? '', $allowed_cats)
          ? $_POST['category']
          : 'event_reminder';

$ins = $conn->prepare(
    "INSERT INTO announcements (title, message, user_id, community_id, category)
     VALUES (?, ?, ?, ?, ?)"
);
$ins->bind_param("ssiis", $title, $message, $user_id, $community_id, $category);
$ins->execute();
$ins->close();

header("Location: ../pages/announcements.php");
exit();
