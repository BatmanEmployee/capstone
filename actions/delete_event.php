<?php
session_start();
include "../config/database.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../pages/login.php"); exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../pages/events.php"); exit();
}

if (!csrf_verify()) { csrf_abort('../pages/events.php'); }

$role = $_SESSION['role'];
if (!in_array($role, ['admin', 'imam', 'leader'])) {
    header("Location: ../pages/events.php"); exit();
}

$id = (int) ($_POST['id'] ?? 0);
if ($id <= 0) { header("Location: ../pages/events.php"); exit(); }

$stmt = $conn->prepare("DELETE FROM events WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->close();

header("Location: ../pages/events.php");
exit();
