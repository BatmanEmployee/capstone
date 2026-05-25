<?php
session_start();
include "../config/database.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../pages/login.php");
    exit();
}

if (!csrf_verify()) { csrf_abort('../pages/forum.php'); }

$user_id  = (int) $_SESSION['user_id'];
$res = $conn->query("SELECT community_id FROM users WHERE id = $user_id");
$u   = $res->fetch_assoc();
$community_id = (int) $u['community_id'];

$title = trim($_POST['title']);
$body  = trim($_POST['body']);

if ($title === '' || $body === '') {
    header("Location: ../pages/forum.php");
    exit();
}

$stmt = $conn->prepare(
    "INSERT INTO forum_threads (title, body, user_id, community_id) VALUES (?, ?, ?, ?)"
);
$stmt->bind_param("ssii", $title, $body, $user_id, $community_id);
$stmt->execute();
$new_id = $conn->insert_id;
$stmt->close();

header("Location: ../pages/forum_thread.php?id=" . $new_id);
exit();
