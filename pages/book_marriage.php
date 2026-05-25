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
    <title>Islamic Marriage & Certification Booking — MCAD</title>
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

        /* Calendar picker styles for dark theme */
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
    <h2>💍 Islamic Marriage &amp; Certification</h2>
    <p>Schedule your solemnization or request marriage documentation with our verified division imams.</p>
</div>

<div class="page-wrap">

    <?php if ($error): ?>
    <div class="error-box">⚠️ <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="notice">
        <strong>📌 Note for Marrying Couples:</strong>
        Please prepare physical copies of birth certificates, Certificate of No Marriage (CENOMAR), and parental consent (if applicable) for submission during your interview or ceremony.
    </div>

    <form method="POST" action="../actions/submit_appointment.php" id="marriageForm">
        <?= csrf_field() ?>
        <input type="hidden" name="service_type" value="islamic_marriage">

        <!-- SECTION 1: Applicant Information -->
        <div class="form-section">
            <span class="step-label">Step 1 of 3</span>
            <h3>👤 Applicant Information</h3>

            <div class="field">
                <label for="full_name">Your Full Name <span class="req">*</span></label>
                <input type="text" id="full_name" name="full_name"
                       placeholder="e.g. Ahmad Al-Mansour" required
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
                           placeholder="e.g. ahmad@example.com"
                           value="<?= htmlspecialchars($post_data['email'] ?? '') ?>">
                </div>
            </div>
        </div>

        <!-- SECTION 2: Bride and Groom Details -->
        <div class="form-section">
            <span class="step-label">Step 2 of 3</span>
            <h3>💍 Groom &amp; Bride Details</h3>

            <div class="field">
                <label for="groom_name">Groom's Full Name <span class="req">*</span></label>
                <input type="text" id="groom_name" name="groom_name"
                       placeholder="Enter groom's complete name" required
                       value="<?= htmlspecialchars($post_data['groom_name'] ?? '') ?>">
            </div>

            <div class="two-col">
                <div class="field">
                    <label for="groom_dob">Groom's Date of Birth <span class="req">*</span></label>
                    <input type="date" id="groom_dob" name="groom_dob" required
                           value="<?= htmlspecialchars($post_data['groom_dob'] ?? '') ?>">
                </div>
                <div class="field">
                    <label for="groom_nationality">Groom's Nationality <span class="req">*</span></label>
                    <input type="text" id="groom_nationality" name="groom_nationality" placeholder="e.g. Filipino" required
                           value="<?= htmlspecialchars($post_data['groom_nationality'] ?? 'Filipino') ?>">
                </div>
            </div>

            <div class="field" style="margin-top: 15px; border-top: 1px dashed rgba(255,255,255,.08); padding-top: 20px;">
                <label for="bride_name">Bride's Full Name <span class="req">*</span></label>
                <input type="text" id="bride_name" name="bride_name"
                       placeholder="Enter bride's complete name" required
                       value="<?= htmlspecialchars($post_data['bride_name'] ?? '') ?>">
            </div>

            <div class="two-col">
                <div class="field">
                    <label for="bride_dob">Bride's Date of Birth <span class="req">*</span></label>
                    <input type="date" id="bride_dob" name="bride_dob" required
                           value="<?= htmlspecialchars($post_data['bride_dob'] ?? '') ?>">
                </div>
                <div class="field">
                    <label for="bride_nationality">Bride's Nationality <span class="req">*</span></label>
                    <input type="text" id="bride_nationality" name="bride_nationality" placeholder="e.g. Filipino" required
                           value="<?= htmlspecialchars($post_data['bride_nationality'] ?? 'Filipino') ?>">
                </div>
            </div>
        </div>

        <!-- SECTION 3: Ceremony & Schedule -->
        <div class="form-section">
            <span class="step-label">Step 3 of 3</span>
            <h3>📅 Schedule &amp; Solemnization</h3>

            <div class="two-col">
                <div class="field">
                    <label for="preferred_date">Preferred Date <span class="req">*</span></label>
                    <input type="date" id="preferred_date" name="preferred_date"
                           required min="<?= date('Y-m-d', strtotime('+1 day')) ?>"
                           value="<?= htmlspecialchars($post_data['preferred_date'] ?? '') ?>">
                </div>
                <div class="field">
                    <label for="preferred_time">Preferred Time <span class="req">*</span></label>
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
                <label for="solemnizing_imam">Requested Solemnizing Imam <span class="hint">(optional)</span></label>
                <select id="solemnizing_imam" name="solemnizing_imam">
                    <option value="To Be Assigned" <?= ($post_data['solemnizing_imam'] ?? '') === 'To Be Assigned' ? 'selected' : '' ?>>To Be Assigned by MCAD</option>
                    <option value="Imam Ali" <?= ($post_data['solemnizing_imam'] ?? '') === 'Imam Ali' ? 'selected' : '' ?>>Imam Ali</option>
                    <option value="Imam Yusuf" <?= ($post_data['solemnizing_imam'] ?? '') === 'Imam Yusuf' ? 'selected' : '' ?>>Imam Yusuf</option>
                    <option value="Imam Bilal" <?= ($post_data['solemnizing_imam'] ?? '') === 'Imam Bilal' ? 'selected' : '' ?>>Imam Bilal</option>
                </select>
            </div>

            <div class="field">
                <label for="witnesses">List of Witnesses <span class="hint">(optional, names separated by commas)</span></label>
                <textarea id="witnesses" name="witnesses" placeholder="e.g. Witness Name A, Witness Name B"><?= htmlspecialchars($post_data['witnesses'] ?? '') ?></textarea>
            </div>

            <div class="field">
                <label for="purpose">Special Requests / Additional Details <span class="hint">(optional)</span></label>
                <textarea id="purpose" name="purpose" placeholder="e.g. Requesting solemnization at our residence, or need help retrieving certified records."><?= htmlspecialchars($post_data['purpose'] ?? '') ?></textarea>
            </div>
        </div>

        <!-- SUBMIT -->
        <div class="submit-wrap">
            <button type="submit" class="submit-btn">
                Submit Marriage Appointment Request →
            </button>
        </div>

        <div class="back-row">
            <a href="book_appointment.php">← View other MCAD services</a>
        </div>
    </form>
</div>

</body>
</html>
