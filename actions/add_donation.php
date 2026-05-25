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

// Donor name is optional — default to 'Anonymous'
$donor   = trim($_POST['donor_name'] ?? '') ?: 'Anonymous';
$allowed_types = ['cash', 'food', 'supplies'];
$type    = in_array($_POST['donation_type'] ?? '', $allowed_types)
         ? $_POST['donation_type'] : 'cash';
$amount  = is_numeric($_POST['amount']   ?? '') ? (float) $_POST['amount']   : null;
$qty     = is_numeric($_POST['quantity'] ?? '') ? (int)   $_POST['quantity'] : null;
$remarks = trim($_POST['remarks'] ?? '');

$ins = $conn->prepare(
    "INSERT INTO donations
     (donor_name, donation_type, amount, quantity, remarks, community_id, user_id)
     VALUES (?, ?, ?, ?, ?, ?, ?)"
);
$ins->bind_param("ssdisii", $donor, $type, $amount, $qty, $remarks, $community_id, $user_id);
$ins->execute();
$ins->close();

header("Location: ../pages/donations.php");
exit();
