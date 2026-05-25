<?php
session_start();
include "../config/database.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../pages/login.php");
    exit();
}

if (!csrf_verify()) { csrf_abort('../pages/donations.php'); }

$user_id = (int) $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT community_id FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$u = $stmt->get_result()->fetch_assoc();
$stmt->close();
$community_id = (int) $u['community_id'];

$beneficiary = trim($_POST['beneficiary']  ?? '');
$item        = trim($_POST['item_name']    ?? '');
$qty         = (int) ($_POST['quantity']   ?? 0);
$date        = $_POST['distributed_at']    ?? date('Y-m-d');

$ins = $conn->prepare(
    "INSERT INTO distributions
     (beneficiary, item_name, quantity, distributed_at, user_id, community_id)
     VALUES (?, ?, ?, ?, ?, ?)"
);
$ins->bind_param("ssissi", $beneficiary, $item, $qty, $date, $user_id, $community_id);
$ins->execute();
$ins->close();

header("Location: ../pages/donations.php");
exit();
