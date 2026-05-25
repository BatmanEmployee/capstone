<?php
session_start();
include "../config/database.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../pages/login.php");
    exit();
}

if (!csrf_verify()) { csrf_abort('../pages/events.php'); }

$user_id = (int) $_SESSION['user_id'];
$role    = $_SESSION['role'];

$title       = trim($_POST['title']       ?? '');
$description = trim($_POST['description'] ?? '');
$date        = $_POST['event_date']        ?? '';
$time        = $_POST['event_time']        ?? '';
$venue       = trim($_POST['venue']        ?? '');

$stmt = $conn->prepare("SELECT community_id FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$u = $stmt->get_result()->fetch_assoc();
$stmt->close();

$community_id = (int) $u['community_id'];
$event_type   = 'personal';
$visibility   = 'personal';

if (in_array($role, ['imam', 'leader', 'admin']) && isset($_POST['visibility'])) {
    $v = $_POST['visibility'];
    $visibility = ($v === 'community') ? 'community' : 'personal';
}

$ins = $conn->prepare(
    "INSERT INTO events
     (title, description, event_date, event_time, venue, user_id, event_type, visibility, community_id)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
);
$ins->bind_param(
    "sssssissi",
    $title, $description, $date, $time, $venue,
    $user_id, $event_type, $visibility, $community_id
);

if ($ins->execute()) {
    $ins->close();
    $_SESSION['success'] = "Event created successfully.";
    header("Location: ../pages/events.php");
} else {
    $ins->close();
    $_SESSION['error'] = "Failed to create event. Please try again.";
    header("Location: ../pages/add_event.php");
}
exit();
