<?php
session_start();

// Guard: only reachable after a successful submission
if (!isset($_SESSION['appt_confirmed'])) {
    header("Location: book_appointment.php");
    exit();
}

$data = $_SESSION['appt_confirmed'];
unset($_SESSION['appt_confirmed']); // consume so browser-back can't re-show stale data

$service_labels = [
    'islamic_marriage'   => 'Islamic Marriage & Certification',
    'halal_certification'=> 'Halal Certification Assistance',
    'burial_assistance'  => 'Muslim Burial Assistance',
    'scholarship'        => 'Scholarship & Financial Assistance',
];

$service_label = $service_labels[$data['service_type']] ?? $data['service_type'];

$time_labels = [
    '08:00'=>'8:00 AM','09:00'=>'9:00 AM','10:00'=>'10:00 AM','11:00'=>'11:00 AM',
    '13:00'=>'1:00 PM','14:00'=>'2:00 PM','15:00'=>'3:00 PM','16:00'=>'4:00 PM',
];
$time_label = $time_labels[$data['preferred_time']] ?? $data['preferred_time'];
$date_label = date('F d, Y', strtotime($data['preferred_date']));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appointment Confirmed — MCAD</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #f5f5f5;
            color: #1a1a2e;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .top-bar {
            background: linear-gradient(135deg, #ff4dc4, #ff00aa);
            padding: 18px 30px;
        }

        .top-bar h1 { color: white; font-size: 20px; font-weight: 700; }

        .page-wrap {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
        }

        .card {
            background: white;
            border-radius: 20px;
            padding: 50px 40px;
            max-width: 580px;
            width: 100%;
            box-shadow: 0 4px 24px rgba(0,0,0,.08);
            text-align: center;
        }

        .check-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #ff4dc4, #ff00aa);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 38px;
            margin: 0 auto 24px;
        }

        .card h2 {
            font-size: 26px;
            font-weight: 700;
            color: #1a1a2e;
            margin-bottom: 10px;
        }

        .card .subtitle {
            font-size: 16px;
            color: #6c757d;
            line-height: 1.6;
            margin-bottom: 30px;
        }

        /* Reference number box */
        .ref-box {
            background: #fff0fa;
            border: 2px dashed #ff4dc4;
            border-radius: 14px;
            padding: 20px 24px;
            margin-bottom: 28px;
        }

        .ref-box .ref-label {
            font-size: 13px;
            font-weight: 600;
            color: #ff00aa;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 6px;
        }

        .ref-box .ref-number {
            font-size: 28px;
            font-weight: 700;
            color: #1a1a2e;
            letter-spacing: 2px;
        }

        .ref-box .ref-hint {
            font-size: 13px;
            color: #6c757d;
            margin-top: 6px;
        }

        /* Details table */
        .details-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
            margin-bottom: 28px;
        }

        .details-table tr {
            border-bottom: 1px solid #f3f4f6;
        }

        .details-table tr:last-child {
            border-bottom: none;
        }

        .details-table td {
            padding: 13px 6px;
            font-size: 15px;
        }

        .details-table td:first-child {
            color: #6c757d;
            width: 42%;
            font-weight: 600;
        }

        .details-table td:last-child {
            color: #1a1a2e;
            font-weight: 500;
        }

        /* Notice */
        .notice {
            background: #fffbeb;
            border: 1.5px solid #fde68a;
            border-radius: 12px;
            padding: 16px 20px;
            font-size: 15px;
            color: #92400e;
            line-height: 1.6;
            text-align: left;
            margin-bottom: 28px;
        }

        /* Buttons */
        .btn-row {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .btn {
            flex: 1;
            padding: 16px;
            font-size: 16px;
            font-weight: 700;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            text-align: center;
            text-decoration: none;
            display: block;
            transition: transform .2s, opacity .2s;
            min-width: 140px;
        }

        .btn:hover { transform: translateY(-2px); opacity: .9; }

        .btn-print {
            background: #1a1a2e;
            color: white;
        }

        .btn-home {
            background: linear-gradient(135deg, #ff4dc4, #ff00aa);
            color: white;
        }

        .btn-new {
            background: #f3f4f6;
            color: #1a1a2e;
        }

        @media (max-width: 480px) {
            .card { padding: 36px 22px; }
            .card h2 { font-size: 22px; }
            .ref-box .ref-number { font-size: 22px; }
            .btn-row { flex-direction: column; }
        }

        @media print {
            .btn-row, .top-bar { display: none !important; }
            body { background: white; }
            .page-wrap { padding: 0; }
            .card { box-shadow: none; border: 1px solid #ddd; }
        }
    </style>
</head>
<body>

<div class="top-bar">
    <h1>MCAD — City Mayor's Office</h1>
</div>

<div class="page-wrap">
    <div class="card">

        <div class="check-icon">✅</div>

        <h2>Appointment Submitted!</h2>
        <p class="subtitle">
            Thank you, <strong><?= htmlspecialchars($data['full_name']) ?></strong>.<br>
            Your appointment request has been received. Please keep your reference number safe.
        </p>

        <!-- Reference Number -->
        <div class="ref-box">
            <p class="ref-label">Your Reference Number</p>
            <p class="ref-number"><?= htmlspecialchars($data['reference_no']) ?></p>
            <p class="ref-hint">Use this number when following up with the MCAD office.</p>
        </div>

        <!-- Appointment Details -->
        <table class="details-table">
            <tr>
                <td>Service</td>
                <td><?= htmlspecialchars($service_label) ?></td>
            </tr>
            <tr>
                <td>Preferred Date</td>
                <td><?= $date_label ?></td>
            </tr>
            <tr>
                <td>Preferred Time</td>
                <td><?= $time_label ?></td>
            </tr>
            <tr>
                <td>Status</td>
                <td>
                    <span style="background:#fef3c7;color:#d97706;padding:4px 12px;border-radius:20px;font-size:13px;font-weight:700;">
                        ⏳ Pending Confirmation
                    </span>
                </td>
            </tr>
        </table>

        <!-- Notice -->
        <div class="notice">
            📞 <strong>What happens next?</strong><br>
            MCAD staff will review your request and contact you at the number you provided to confirm your appointment date and time.
            If you have questions, please call the MCAD office directly and provide your reference number.
        </div>

        <!-- Buttons -->
        <div class="btn-row">
            <button class="btn btn-print" onclick="window.print()">🖨️ Print / Save</button>
            <a href="book_appointment.php" class="btn btn-new">📋 New Appointment</a>
            <a href="../index.php" class="btn btn-home">🏠 Home</a>
        </div>

    </div>
</div>

</body>
</html>
