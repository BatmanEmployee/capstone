<?php
session_start();
include "../config/database.php";

// Allowed values
$allowed_services = [
    'islamic_marriage',
    'halal_certification',
    'burial_assistance',
    'scholarship'
];

$allowed_times = [
    '08:00','09:00','10:00','11:00',
    '13:00','14:00','15:00','16:00'
];

// ── Collect & validate ──
$full_name  = trim($_POST['full_name']  ?? '');
$contact    = trim($_POST['contact']    ?? '');
$email      = trim($_POST['email']      ?? '');
$service    = $_POST['service_type']    ?? '';
$pref_date  = $_POST['preferred_date']  ?? '';
$pref_time  = $_POST['preferred_time']  ?? '';
$purpose    = trim($_POST['purpose']    ?? '');

$errors = [];

if ($full_name === '') {
    $errors[] = "Please enter your full name.";
}
if ($contact === '') {
    $errors[] = "Please enter your contact number.";
}
if (!in_array($service, $allowed_services)) {
    $errors[] = "Please select a service.";
}
if ($pref_date === '' || $pref_date < date('Y-m-d', strtotime('+1 day'))) {
    $errors[] = "Please choose a valid preferred date (must be at least tomorrow).";
}
if (!in_array($pref_time, $allowed_times)) {
    $errors[] = "Please select a valid preferred time.";
}
if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Please enter a valid email address, or leave it blank.";
}

if (!empty($errors)) {
    $_SESSION['appt_error'] = implode(' ', $errors);
    // Preserve POST data so the form refills
    $_SESSION['appt_post'] = $_POST;
    header("Location: ../pages/book_appointment.php");
    exit();
}

// ── Generate unique reference number ──
// Format: MCAD-YYYYMMDD-NNN  (NNN = count of today's appointments + 1)
$today_prefix = 'MCAD-' . date('Ymd') . '-';
$count_res = $conn->query("
    SELECT COUNT(*) AS c FROM appointments
    WHERE reference_no LIKE '" . $conn->real_escape_string($today_prefix) . "%'
");
$seq = (int)$count_res->fetch_assoc()['c'] + 1;
$reference_no = $today_prefix . str_pad($seq, 3, '0', STR_PAD_LEFT);

// Ensure uniqueness on collision
while (true) {
    $chk = $conn->prepare("SELECT id FROM appointments WHERE reference_no = ?");
    $chk->bind_param("s", $reference_no);
    $chk->execute();
    $chk->store_result();
    if ($chk->num_rows === 0) { $chk->close(); break; }
    $chk->close();
    $seq++;
    $reference_no = $today_prefix . str_pad($seq, 3, '0', STR_PAD_LEFT);
}

// Normalize email to NULL if empty
$email_val = ($email === '') ? null : $email;
$purpose_val = ($purpose === '') ? null : $purpose;

// ── Insert ──
$stmt = $conn->prepare("
    INSERT INTO appointments
    (reference_no, full_name, contact, email, service_type, preferred_date, preferred_time, purpose)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
");
$stmt->bind_param(
    "ssssssss",
    $reference_no, $full_name, $contact, $email_val,
    $service, $pref_date, $pref_time, $purpose_val
);

if ($stmt->execute()) {
    $stmt->close();
    // Pass reference to confirmation page via session (no sensitive data in URL)
    $_SESSION['appt_confirmed'] = [
        'reference_no'   => $reference_no,
        'full_name'      => $full_name,
        'service_type'   => $service,
        'preferred_date' => $pref_date,
        'preferred_time' => $pref_time,
    ];
    header("Location: ../pages/appointment_confirmation.php");
} else {
    $stmt->close();
    $_SESSION['appt_error'] = "Something went wrong. Please try again.";
    header("Location: ../pages/book_appointment.php");
}
exit();
