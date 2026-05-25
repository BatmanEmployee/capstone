<?php
session_start();
include "../config/database.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../pages/dashboard.php");
    exit();
}

$id         = (int) ($_POST['appointment_id'] ?? 0);
$remarks    = trim($_POST['admin_remarks'] ?? '');

$allowed = ['approved', 'rejected', 'completed'];
$status  = in_array($_POST['new_status'] ?? '', $allowed) ? $_POST['new_status'] : '';

if ($id === 0 || $status === '') {
    header("Location: ../pages/appointments.php");
    exit();
}

$stmt = $conn->prepare(
    "UPDATE appointments SET status = ?, admin_remarks = ? WHERE id = ?"
);
$remarks_val = ($remarks === '') ? null : $remarks;
$stmt->bind_param("ssi", $status, $remarks_val, $id);
$stmt->execute();
$stmt->close();

$msg = match($status) {
    'approved'  => 'Appointment approved successfully.',
    'rejected'  => 'Appointment rejected.',
    'completed' => 'Appointment marked as completed.',
    default     => 'Appointment updated.',
};

header("Location: ../pages/appointments.php?msg=" . urlencode($msg));
exit();
