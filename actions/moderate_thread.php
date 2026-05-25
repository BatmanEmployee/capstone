<?php
session_start();
include "../config/database.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../pages/login.php");
    exit();
}

if (!csrf_verify()) { csrf_abort('../pages/forum.php'); }

$role = $_SESSION['role'];
if (!in_array($role, ['admin', 'imam'])) {
    header("Location: ../pages/forum.php");
    exit();
}

$action    = $_POST['action']    ?? '';
$thread_id = (int) ($_POST['thread_id'] ?? 0);

if ($action === 'pin' && $thread_id > 0) {
    $conn->query("UPDATE forum_threads SET is_pinned = NOT is_pinned WHERE id = $thread_id");
} elseif ($action === 'delete' && $thread_id > 0) {
    $conn->query("DELETE FROM forum_replies WHERE thread_id = $thread_id");
    $conn->query("DELETE FROM forum_threads WHERE id = $thread_id");
}

header("Location: ../pages/forum.php");
exit();
