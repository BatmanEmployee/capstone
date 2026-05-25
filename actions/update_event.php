<?php
session_start();
include "../config/database.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../pages/login.php");
    exit();
}

if (!csrf_verify()) { csrf_abort('../pages/events.php'); }

$id          = (int) ($_POST['id']          ?? 0);
$title       = trim($_POST['title']          ?? '');
$description = trim($_POST['description']    ?? '');
$date        = $_POST['event_date']           ?? '';
$time        = $_POST['event_time']           ?? '';
$venue       = trim($_POST['venue']           ?? '');

$allowed_status = ['upcoming', 'ongoing', 'completed'];
$status = in_array($_POST['status'] ?? '', $allowed_status)
        ? $_POST['status']
        : 'upcoming';

$stmt = $conn->prepare(
    "UPDATE events
     SET title=?, description=?, event_date=?, event_time=?, venue=?, status=?
     WHERE id=?"
);
$stmt->bind_param("ssssssi", $title, $description, $date, $time, $venue, $status, $id);
$stmt->execute();
$stmt->close();

header("Location: ../pages/events.php");
exit();
