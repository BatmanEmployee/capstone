<?php
session_start();
include "../config/database.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../pages/login.php");
    exit();
}

if (!csrf_verify()) { csrf_abort('../pages/attendance.php'); }

$allowed = ['admin', 'imam', 'leader'];
if (!in_array($_SESSION['role'], $allowed)) {
    header("Location: ../pages/attendance.php");
    exit();
}

$user_id     = (int) $_SESSION['user_id'];
$event_id    = (int) $_POST['event_id'];
$name        = trim($_POST['attendee_name']);
$att_status  = $_POST['att_status'] === 'absent' ? 'absent' : 'present';

$res = $conn->query("SELECT community_id FROM users WHERE id = $user_id");
$u   = $res->fetch_assoc();
$community_id = (int) $u['community_id'];

$stmt = $conn->prepare(
    "INSERT INTO attendance (event_id, name, status, recorded_by, community_id)
     VALUES (?, ?, ?, ?, ?)"
);
$stmt->bind_param("issii", $event_id, $name, $att_status, $user_id, $community_id);
$stmt->execute();
$stmt->close();

header("Location: ../pages/attendance.php?event_id=" . $event_id);
exit();
