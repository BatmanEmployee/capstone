<?php
session_start();
include "../config/database.php";

// CSRF Security Protection
if (!csrf_verify()) {
    $referrer = $_SERVER['HTTP_REFERER'] ?? '../pages/book_appointment.php';
    csrf_abort($referrer);
}

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

// ── Collect standard fields ──
$full_name  = trim($_POST['full_name']  ?? '');
$contact    = trim($_POST['contact']    ?? '');
$email      = trim($_POST['email']      ?? '');
$service    = $_POST['service_type']    ?? '';
$pref_date  = $_POST['preferred_date']  ?? '';
$pref_time  = $_POST['preferred_time']  ?? '';
$purpose    = trim($_POST['purpose']    ?? '');

$errors = [];

// ── Standard Validations ──
if ($full_name === '') {
    $errors[] = "Please enter your full name.";
}
if ($contact === '') {
    $errors[] = "Please enter your contact number.";
}
if (!in_array($service, $allowed_services)) {
    $errors[] = "Please select a valid service.";
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

// ── Service-Specific Fields & Validations ──
$purpose_val = null;

if (empty($errors)) {
    if ($service === 'islamic_marriage') {
        $groom_name        = trim($_POST['groom_name'] ?? '');
        $groom_dob         = trim($_POST['groom_dob'] ?? '');
        $groom_nationality = trim($_POST['groom_nationality'] ?? '');
        $bride_name        = trim($_POST['bride_name'] ?? '');
        $bride_dob         = trim($_POST['bride_dob'] ?? '');
        $bride_nationality = trim($_POST['bride_nationality'] ?? '');
        $solemnizing_imam  = trim($_POST['solemnizing_imam'] ?? '');
        $witnesses         = trim($_POST['witnesses'] ?? '');

        if ($groom_name === '') $errors[] = "Groom's name is required.";
        if ($groom_dob === '')  $errors[] = "Groom's date of birth is required.";
        if ($groom_nationality === '') $errors[] = "Groom's nationality is required.";
        if ($bride_name === '') $errors[] = "Bride's name is required.";
        if ($bride_dob === '')  $errors[] = "Bride's date of birth is required.";
        if ($bride_nationality === '') $errors[] = "Bride's nationality is required.";

        if (empty($errors)) {
            $purpose_lines = [];
            $purpose_lines[] = "💍 Groom: " . $groom_name . " (DOB: " . $groom_dob . ", " . $groom_nationality . ")";
            $purpose_lines[] = "Bride: " . $bride_name . " (DOB: " . $bride_dob . ", " . $bride_nationality . ")";
            $purpose_lines[] = "Solemnizing Imam: " . ($solemnizing_imam !== '' ? $solemnizing_imam : 'To Be Assigned');
            if ($witnesses !== '') $purpose_lines[] = "Witnesses: " . $witnesses;
            if ($purpose !== '')   $purpose_lines[] = "Notes: " . $purpose;
            $purpose_val = implode(" | ", $purpose_lines);
        }

    } elseif ($service === 'halal_certification') {
        $business_name    = trim($_POST['business_name'] ?? '');
        $business_type    = trim($_POST['business_type'] ?? '');
        $business_address = trim($_POST['business_address'] ?? '');
        $product_desc     = trim($_POST['product_desc'] ?? '');

        if ($business_name === '')    $errors[] = "Business name is required.";
        if ($business_type === '')    $errors[] = "Business type selection is required.";
        if ($business_address === '') $errors[] = "Business physical address is required.";
        if ($product_desc === '')     $errors[] = "Product/service description is required.";

        if (empty($errors)) {
            $purpose_lines = [];
            $purpose_lines[] = "✅ Business: " . $business_name . " (" . $business_type . ")";
            $purpose_lines[] = "Address: " . $business_address;
            $purpose_lines[] = "Products: " . $product_desc;
            if ($purpose !== '') $purpose_lines[] = "Assistance Notes: " . $purpose;
            $purpose_val = implode(" | ", $purpose_lines);
        }

    } elseif ($service === 'burial_assistance') {
        $deceased_name     = trim($_POST['deceased_name'] ?? '');
        $date_of_death     = trim($_POST['date_of_death'] ?? '');
        $place_of_death    = trim($_POST['place_of_death'] ?? '');
        $relationship      = trim($_POST['relationship'] ?? '');
        $cemetery_location = trim($_POST['cemetery_location'] ?? '');

        if ($deceased_name === '')     $errors[] = "Deceased name is required.";
        if ($date_of_death === '')     $errors[] = "Date of death is required.";
        if ($place_of_death === '')    $errors[] = "Place of death is required.";
        if ($relationship === '')      $errors[] = "Relationship selection is required.";
        if ($cemetery_location === '') $errors[] = "Cemetery location is required.";

        if (empty($errors)) {
            $purpose_lines = [];
            $purpose_lines[] = "🕊️ Deceased: " . $deceased_name . " (DOD: " . $date_of_death . " at " . $place_of_death . ")";
            $purpose_lines[] = "Informant: " . $relationship;
            $purpose_lines[] = "Burial Site: " . $cemetery_location;
            if ($purpose !== '') $purpose_lines[] = "Req: " . $purpose;
            $purpose_val = implode(" | ", $purpose_lines);
        }

    } elseif ($service === 'scholarship') {
        $school_name         = trim($_POST['school_name'] ?? '');
        $course_level        = trim($_POST['course_level'] ?? '');
        $assistance_type     = trim($_POST['assistance_type'] ?? '');
        $income_range        = trim($_POST['income_range'] ?? '');
        $academic_background = trim($_POST['academic_background'] ?? '');

        if ($school_name === '')     $errors[] = "School/university name is required.";
        if ($course_level === '')    $errors[] = "Course or grade level is required.";
        if ($assistance_type === '') $errors[] = "Assistance type selection is required.";
        if ($income_range === '')    $errors[] = "Estimated monthly income is required.";

        if (empty($errors)) {
            $purpose_lines = [];
            $purpose_lines[] = "🎓 Student: " . $school_name . " (" . $course_level . ")";
            $purpose_lines[] = "Aid Type: " . $assistance_type . " | Income: " . $income_range;
            if ($academic_background !== '') $purpose_lines[] = "Merit/BG: " . $academic_background;
            if ($purpose !== '')             $purpose_lines[] = "Statement: " . $purpose;
            $purpose_val = implode(" | ", $purpose_lines);
        }
    }
}

// Map redirect targets depending on service type
$redirects = [
    'islamic_marriage'    => '../pages/book_marriage.php',
    'halal_certification' => '../pages/book_halal.php',
    'burial_assistance'   => '../pages/book_burial.php',
    'scholarship'         => '../pages/book_scholarship.php',
];
$redirect = $redirects[$service] ?? '../pages/book_appointment.php';

if (!empty($errors)) {
    $_SESSION['appt_error'] = implode(' ', $errors);
    // Preserve POST data so the form refills
    $_SESSION['appt_post'] = $_POST;
    header("Location: " . $redirect);
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
    header("Location: " . $redirect);
}
exit();
