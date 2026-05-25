<?php
session_start();
include "../config/database.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../pages/login.php");
    exit();
}

if (!csrf_verify()) { csrf_abort('../pages/forum.php'); }

$user_id   = (int) $_SESSION['user_id'];
$thread_id = (int) $_POST['thread_id'];
$body      = trim($_POST['body']);

if ($body === '' || $thread_id === 0) {
    header("Location: ../pages/forum_thread.php?id=" . $thread_id);
    exit();
}

$stmt = $conn->prepare(
    "INSERT INTO forum_replies (thread_id, user_id, body) VALUES (?, ?, ?)"
);
$stmt->bind_param("iis", $thread_id, $user_id, $body);
$stmt->execute();
$stmt->close();

header("Location: ../pages/forum_thread.php?id=" . $thread_id);
exit();
