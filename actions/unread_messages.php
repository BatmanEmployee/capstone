<?php
session_start();
include "../config/database.php";

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['count' => 0]);
    exit();
}

$user_id = (int) $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT COUNT(*) AS c FROM messages WHERE receiver_id = ? AND is_read = 0");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$stmt->close();

echo json_encode(['count' => (int) $row['c']]);
