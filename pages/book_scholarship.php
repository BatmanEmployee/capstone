<?php
session_start();
include_once "../functions/csrf.php";

$error = '';
if (isset($_SESSION['appt_error'])) {
    $error = $_SESSION['appt_error'];
    unset($_SESSION['appt_error']);
}

// Retrieve preserved form fields on error
$post_data = $_SESSION['appt_post'] ?? [];
unset($_SESSION['appt_post']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scholarship &amp; Financial Assistance Booking — MCAD</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Segoe UI', -apple-system, BlinkMacSystemFont, Roboto, sans-serif;
            background: #121212;
            color: #fff;
            min-height: 100vh;
        }

        /* ── TOP BAR ── */
        .top-bar {
            background: linear-gradient(135deg, #ff4dc4, #ff00aa);
            padding: 18px 30px;
            display: flex;
            align-items: center;
            gap: 15px;
            box-shadow: 0 4px 20px rgba(255, 0, 170, 0.15);
        }

        .top-bar a {
            color: white;
            text-decoration: none;
            font-size: 15px;
            opacity: .85;
            font-weight: 600;
            transition: opacity .2s;
        }

        .top-bar a:hover { opacity: 1; }

        .top-bar h1 {
            color: white;
            font-size: 20px;
            font-weight: 800;
            letter-spacing: .5px;
        }

        /* ── HERO BANNER ── */
        .hero {
            background: linear-gradient(180deg, #1a0a2e 0%, #121212 100%);
            color: white;
            text-align: center;
            padding: 50px 20px 40px;
            border-bottom: 1px solid rgba(255,255,255,.05);
        }

        .hero .office-name {
            font-size: 12px;
            letter-spacing: 2.5px;
            text-transform: uppercase;
            color: #ff4dc4;
            margin-bottom: 12px;
            font-weight: 700;
        }

        .hero h2 {
            font-size: 32px;
            font-weight: 900;
            margin-bottom: 12px;
            line-height: 1.3;
            background: linear-gradient(to right, #fff, #ffb3e6);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .hero p {
            font-size: 16px;
            color: rgba(255,255,255,.65);
            max-width: 560px;
            margin: 0 auto;
            line-height: 1.6;
        }

        /* ── MAIN FORM WRAPPER ── */
        .page-wrap {
            max-width: 720px;
            margin: 40px auto 60px;
            padding: 0 20px;
        }

        /* ── STEP LABEL ── */
        .step-label {
            font-size: 12px;
            font-weight: 800;
            color: #ff4dc4;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            margin-bottom: 8px;
            display: inline-block;
        }

        /* ── SECTION ── */
        .form-section {
            background: #1e1e1e;
            border: 1px solid rgba(255,255,255,.07);
            border-radius: 18px;
            padding: 35px;
            margin-bottom: 24px;
            box-shadow: 0 10px 30px rgba(0,0,0,.2);
        }

        .form-section h3 {
            font-size: 20px;
            font-weight: 800;
            color: #fff;
            margin-bottom: 24px;
            padding-bottom: 14px;
            border-bottom: 1px solid rgba(255,255,255,.08);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* ── FIELD ── */
        .field {
            margin-bottom: 22px;
        }

        .field label {
            display: block;
            font-size: 15px;
            font-weight: 600;
            color: rgba(255,255,255,.85);
            margin-bottom: 8px;
        }

        .field .hint {
            font-size: 13px;
            color: rgba(255,255,255,.45);
            font-weight: 400;
            margin-left: 6px;
        }

        .field input,
        .field select,
        .field textarea {
            width: 100%;
            padding: 15px 18px;
            font-size: 15px;
            border: 1.5px solid rgba(255,255,255,.12);
            border-radius: 12px;
            color: #fff;
            background: rgba(255,255,255,.05);
            transition: all .2s;
            appearance: none;
            -webkit-appearance: none;
        }

        .field input:focus,
        .field select:focus,
        .field textarea:focus {
            outline: none;
            border-color: #ff00aa;
            background: rgba(255,255,255,.08);
            box-shadow: 0 0 0 3px rgba(255,0,170,.15);
        }

        .field textarea {
            min-height: 110px;
            resize: vertical;
            line-height: 1.6;
        }

        .field select {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%23ffffff' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 18px center;
            padding-right: 44px;
            cursor: pointer;
        }

        .field input[type="date"]::-webkit-calendar-picker-indicator {
            filter: invert(1);
            cursor: pointer;
        }

        .two-col {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        @media (max-width: 580px) {
            .two-col { grid-template-columns: 1fr; gap: 0; }
            .hero h2 { font-size: 26px; }
            .form-section { padding: 25px; }
        }

        /* ── REQUIRED STAR ── */
        .req { color: #ff4dc4; margin-left: 3px; }

        /* ── NOTICE BOX ── */
        .notice {
            background: rgba(251,191,36,.06);
            border: 1px solid rgba(251,191,36,.2);
            border-radius: 14px;
            padding: 20px;
            font-size: 15px;
            color: #fbbf24;
            line-height: 1.6;
            margin-bottom: 24px;
        }

        .notice strong { display: block; margin-bottom: 6px; font-size: 16px; color: #f59e0b; }

        /* ── SUBMIT BUTTON ── */
        .submit-wrap {
            text-align: center;
        }

        .submit-btn {
            display: inline-block;
            width: 100%;
            padding: 18px;
            font-size: 18px;
            font-weight: 700;
            background: linear-gradient(135deg, #ff4dc4, #ff00aa);
            color: white;
            border: none;
            border-radius: 14px;
            cursor: pointer;
            letter-spacing: .5px;
            transition: all .2s;
            box-shadow: 0 6px 20px rgba(255,0,170,.3);
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(255,0,170,.45);
        }

        .submit-btn:active { transform: translateY(0); }

        /* ── BACK LINK ── */
        .back-row {
            text-align: center;
            margin-top: 24px;
        }

        .back-row a {
            color: rgba(255,255,255,.5);
            font-size: 15px;
            text-decoration: none;
            transition: color .2s;
        }

        .back-row a:hover { color: #ff00aa; }

        /* ── ERROR ── */
        .error-box {
            background: rgba(239,68,68,.1);
            border: 1px solid rgba(239,68,68,.25);
            border-radius: 14px;
            padding: 16px 20px;
            font-size: 15px;
            color: #f87171;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
    </style>
</head>
<body>

<!-- TOP BAR -->
<div class="top-bar">
    <a href="book_appointment.php">← Back to Services</a>
    <h1>MCAD — City Mayor's Office</h1>
</div>

<!-- HERO -->
<div class="hero">
    <p class="office-name">Muslim Concerns and Affairs Division</p>
    <h2>🎓 Scholarship &amp; Financial Assistance</h2>
    <p>Schedule your interview or document submission for local Muslim community scholarship programs.</p>
</div>

<div class="page-wrap">

    <?php if ($error): ?>
    <div class="error-box">⚠️ <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="notice">
        <strong>📌 Scholarship Requirements:</strong>
        Please prepare your Certificate of Enrollment, recent report cards or transcript of records, and Barangay Certificate of Indigency to be presented during your interview.
    </div>

    <form method="POST" action="../actions/submit_appointment.php" id="scholarshipForm">
        <?= csrf_field() ?>
        <input type="hidden" name="service_type" value="scholarship">

        <!-- SECTION 1: Student Information -->
        <div class="form-section">
            <span class="step-label">Step 1 of 3</span>
            <h3>👤 Student / Applicant Information</h3>

            <div class="field">
                <label for="full_name">Your Full Name <span class="req">*</span></label>
                <input type="text" id="full_name" name="full_name"
                       placeholder="e.g. Fatima Az-Zahra" required
                       value="<?= htmlspecialchars($post_data['full_name'] ?? '') ?>">
            </div>

            <div class="two-col">
                <div class="field">
                    <label for="contact">Contact Number <span class="req">*</span></label>
                    <input type="tel" id="contact" name="contact"
                           placeholder="e.g. 09171234567" required
                           value="<?= htmlspecialchars($post_data['contact'] ?? '') ?>">
                </div>
                <div class="field">
                    <label for="email">Email Address <span class="hint">(optional)</span></label>
                    <input type="email" id="email" name="email"
                           placeholder="e.g. fatima.zahra@student.edu"
                           value="<?= htmlspecialchars($post_data['email'] ?? '') ?>">
                </div>
            </div>
        </div>

        <!-- SECTION 2: Academic & Financial Details -->
        <div class="form-section">
            <span class="step-label">Step 2 of 3</span>
            <h3>🎓 Academic &amp; Financial Information</h3>

            <div class="field">
                <label for="school_name">Name of School / University <span class="req">*</span></label>
                <input type="text" id="school_name" name="school_name"
                       placeholder="e.g. Mindanao State University — Gensan" required
                       value="<?= htmlspecialchars($post_data['school_name'] ?? '') ?>">
            </div>

            <div class="two-col">
                <div class="field">
                    <label for="course_level">Course / Year / Grade Level <span class="req">*</span></label>
                    <input type="text" id="course_level" name="course_level"
                           placeholder="e.g. BS Computer Science - 3rd Year" required
                           value="<?= htmlspecialchars($post_data['course_level'] ?? '') ?>">
                </div>
                <div class="field">
                    <label for="assistance_type">Type of Assistance Requested <span class="req">*</span></label>
                    <select id="assistance_type" name="assistance_type" required>
                        <option value="" disabled selected>-- Select Assistance Type --</option>
                        <option value="Tuition Financial Assistance" <?= ($post_data['assistance_type'] ?? '') === 'Tuition Financial Assistance' ? 'selected' : '' ?>>Tuition Financial Assistance</option>
                        <option value="Academic Scholarship" <?= ($post_data['assistance_type'] ?? '') === 'Academic Scholarship' ? 'selected' : '' ?>>Academic Scholarship</option>
                        <option value="Book & Allowance Support" <?= ($post_data['assistance_type'] ?? '') === 'Book & Allowance Support' ? 'selected' : '' ?>>Book &amp; Allowance Support</option>
                        <option value="Special Educational Grant" <?= ($post_data['assistance_type'] ?? '') === 'Special Educational Grant' ? 'selected' : '' ?>>Special Educational Grant</option>
                    </select>
                </div>
            </div>

            <div class="field">
                <label for="income_range">Estimated Family Monthly Income <span class="req">*</span></label>
                <select id="income_range" name="income_range" required>
                    <option value="" disabled selected>-- Select Income Range --</option>
                    <option value="Below ₱10,000" <?= ($post_data['income_range'] ?? '') === 'Below ₱10,000' ? 'selected' : '' ?>>Below ₱10,000 (Low Income / Indigent)</option>
                    <option value="₱10,000 - ₱20,000" <?= ($post_data['income_range'] ?? '') === '₱10,000 - ₱20,000' ? 'selected' : '' ?>>₱10,000 - ₱20,000</option>
                    <option value="₱20,000 - ₱40,000" <?= ($post_data['income_range'] ?? '') === '₱20,000 - ₱40,000' ? 'selected' : '' ?>>₱20,000 - ₱40,000</option>
                    <option value="Above ₱40,000" <?= ($post_data['income_range'] ?? '') === 'Above ₱40,000' ? 'selected' : '' ?>>Above ₱40,000</option>
                </select>
            </div>

            <div class="field">
                <label for="academic_background">Academic Merits, Honors, or Background details <span class="hint">(optional)</span></label>
                <textarea id="academic_background" name="academic_background"
                          placeholder="List any academic awards, GPA/GWA, or specific family financial backgrounds that support your application."><?= htmlspecialchars($post_data['academic_background'] ?? '') ?></textarea>
            </div>
        </div>

        <!-- SECTION 3: Interview Schedule -->
        <div class="form-section">
            <span class="step-label">Step 3 of 3</span>
            <h3>📅 Schedule Appointment</h3>

            <div class="two-col">
                <div class="field">
                    <label for="preferred_date">Preferred Date <span class="req">*</span></label>
                    <input type="date" id="preferred_date" name="preferred_date"
                           required min="<?= date('Y-m-d', strtotime('+1 day')) ?>"
                           value="<?= htmlspecialchars($post_data['preferred_date'] ?? '') ?>">
                </div>
                <div class="field">
                    <label for="preferred_time">Preferred Time Slot <span class="req">*</span></label>
                    <select id="preferred_time" name="preferred_time" required>
                        <option value="" disabled selected>-- Select a time --</option>
                        <option value="08:00" <?= ($post_data['preferred_time'] ?? '') === '08:00' ? 'selected' : '' ?>>8:00 AM</option>
                        <option value="09:00" <?= ($post_data['preferred_time'] ?? '') === '09:00' ? 'selected' : '' ?>>9:00 AM</option>
                        <option value="10:00" <?= ($post_data['preferred_time'] ?? '') === '10:00' ? 'selected' : '' ?>>10:00 AM</option>
                        <option value="11:00" <?= ($post_data['preferred_time'] ?? '') === '11:00' ? 'selected' : '' ?>>11:00 AM</option>
                        <option value="13:00" <?= ($post_data['preferred_time'] ?? '') === '13:00' ? 'selected' : '' ?>>1:00 PM</option>
                        <option value="14:00" <?= ($post_data['preferred_time'] ?? '') === '14:00' ? 'selected' : '' ?>>2:00 PM</option>
                        <option value="15:00" <?= ($post_data['preferred_time'] ?? '') === '15:00' ? 'selected' : '' ?>>3:00 PM</option>
                        <option value="16:00" <?= ($post_data['preferred_time'] ?? '') === '16:00' ? 'selected' : '' ?>>4:00 PM</option>
                    </select>
                </div>
            </div>

            <div class="field">
                <label for="purpose">Brief Personal Statement / Special Needs <span class="hint">(optional)</span></label>
                <textarea id="purpose" name="purpose" placeholder="Briefly write about why you are applying or any urgent academic requirements you might have."><?= htmlspecialchars($post_data['purpose'] ?? '') ?></textarea>
            </div>
        </div>

        <!-- SUBMIT -->
        <div class="submit-wrap">
            <button type="submit" class="submit-btn">
                Submit Scholarship Request →
            </button>
        </div>

        <div class="back-row">
            <a href="book_appointment.php">← View other MCAD services</a>
        </div>
    </form>
</div>

</body>
</html>
