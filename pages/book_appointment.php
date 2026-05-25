<?php
session_start();
$error = '';
if (isset($_SESSION['appt_error'])) {
    $error = $_SESSION['appt_error'];
    unset($_SESSION['appt_error']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book an Appointment — MCAD</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #f5f5f5;
            color: #1a1a2e;
        }

        /* ── TOP BAR ── */
        .top-bar {
            background: linear-gradient(135deg, #ff4dc4, #ff00aa);
            padding: 18px 30px;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .top-bar a {
            color: white;
            text-decoration: none;
            font-size: 15px;
            opacity: .85;
        }

        .top-bar a:hover { opacity: 1; }

        .top-bar h1 {
            color: white;
            font-size: 20px;
            font-weight: 700;
        }

        /* ── HERO BANNER ── */
        .hero {
            background: #1a1a2e;
            color: white;
            text-align: center;
            padding: 40px 20px 35px;
        }

        .hero .office-name {
            font-size: 13px;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: #ff4dc4;
            margin-bottom: 10px;
        }

        .hero h2 {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 10px;
            line-height: 1.3;
        }

        .hero p {
            font-size: 16px;
            color: rgba(255,255,255,.7);
            max-width: 560px;
            margin: 0 auto;
            line-height: 1.6;
        }

        /* ── MAIN FORM WRAPPER ── */
        .page-wrap {
            max-width: 680px;
            margin: 40px auto 60px;
            padding: 0 20px;
        }

        /* ── STEP LABEL ── */
        .step-label {
            font-size: 13px;
            font-weight: 700;
            color: #ff00aa;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 6px;
        }

        /* ── SECTION ── */
        .form-section {
            background: white;
            border-radius: 16px;
            padding: 30px;
            margin-bottom: 20px;
            box-shadow: 0 2px 12px rgba(0,0,0,.06);
        }

        .form-section h3 {
            font-size: 20px;
            font-weight: 700;
            color: #1a1a2e;
            margin-bottom: 22px;
            padding-bottom: 14px;
            border-bottom: 2px solid #f3f4f6;
        }

        /* ── FIELD ── */
        .field {
            margin-bottom: 22px;
        }

        .field label {
            display: block;
            font-size: 16px;
            font-weight: 600;
            color: #1a1a2e;
            margin-bottom: 8px;
        }

        .field .hint {
            font-size: 13px;
            color: #6c757d;
            font-weight: 400;
            margin-left: 6px;
        }

        .field input,
        .field select,
        .field textarea {
            width: 100%;
            padding: 16px 18px;
            font-size: 16px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            color: #1a1a2e;
            background: #fafafa;
            transition: border-color .2s, background .2s;
            appearance: none;
            -webkit-appearance: none;
        }

        .field input:focus,
        .field select:focus,
        .field textarea:focus {
            outline: none;
            border-color: #ff00aa;
            background: #fff;
        }

        .field textarea {
            min-height: 110px;
            resize: vertical;
            line-height: 1.6;
        }

        .field select {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%236c757d' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 16px center;
            padding-right: 44px;
            cursor: pointer;
        }

        .two-col {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        @media (max-width: 540px) {
            .two-col { grid-template-columns: 1fr; }
            .hero h2 { font-size: 22px; }
        }

        /* ── SERVICE CARDS ── */
        .service-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-top: 4px;
        }

        @media (max-width: 540px) {
            .service-grid { grid-template-columns: 1fr; }
        }

        .service-card {
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            padding: 18px 16px;
            cursor: pointer;
            transition: all .2s;
            display: flex;
            align-items: flex-start;
            gap: 12px;
            background: #fafafa;
        }

        .service-card:hover {
            border-color: #ff4dc4;
            background: #fff0fa;
        }

        .service-card.selected {
            border-color: #ff00aa;
            background: #fff0fa;
            box-shadow: 0 0 0 3px rgba(255,0,170,.12);
        }

        .service-card input[type="radio"] {
            width: 20px;
            height: 20px;
            min-width: 20px;
            accent-color: #ff00aa;
            margin-top: 2px;
            cursor: pointer;
        }

        .service-card .svc-info .svc-name {
            font-size: 15px;
            font-weight: 700;
            color: #1a1a2e;
            line-height: 1.3;
        }

        .service-card .svc-info .svc-desc {
            font-size: 13px;
            color: #6c757d;
            margin-top: 3px;
            line-height: 1.4;
        }

        /* ── REQUIRED STAR ── */
        .req { color: #e53e3e; margin-left: 3px; }

        /* ── NOTICE BOX ── */
        .notice {
            background: #fffbeb;
            border: 1.5px solid #fde68a;
            border-radius: 12px;
            padding: 16px 20px;
            font-size: 15px;
            color: #92400e;
            line-height: 1.6;
            margin-bottom: 20px;
        }

        .notice strong { display: block; margin-bottom: 4px; font-size: 16px; }

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
            transition: transform .2s, box-shadow .2s;
            box-shadow: 0 4px 15px rgba(255,0,170,.25);
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(255,0,170,.35);
        }

        .submit-btn:active { transform: translateY(0); }

        /* ── BACK LINK ── */
        .back-row {
            text-align: center;
            margin-top: 18px;
        }

        .back-row a {
            color: #6c757d;
            font-size: 15px;
            text-decoration: none;
        }

        .back-row a:hover { color: #ff00aa; }

        /* ── ERROR ── */
        .error-box {
            background: #fee2e2;
            border: 1.5px solid #fca5a5;
            border-radius: 12px;
            padding: 14px 18px;
            font-size: 15px;
            color: #b91c1c;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

<!-- TOP BAR -->
<div class="top-bar">
    <a href="../index.php">← Back to Home</a>
    <h1>MCAD — City Mayor's Office</h1>
</div>

<!-- HERO -->
<div class="hero">
    <p class="office-name">Muslim Concerns and Affairs Division</p>
    <h2>Book an Appointment</h2>
    <p>Fill in the form below and our staff will confirm your appointment as soon as possible.</p>
</div>

<div class="page-wrap">

    <?php if ($error): ?>
    <div class="error-box">⚠️ <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="notice">
        <strong>📌 Before you proceed:</strong>
        Walk-ins are also welcome at the MCAD office during office hours (Monday–Friday, 8:00 AM – 5:00 PM).
        Booking an appointment helps us serve you better and reduces your waiting time.
    </div>

    <form method="POST" action="../actions/submit_appointment.php" id="apptForm">

        <!-- SECTION 1: Personal Information -->
        <div class="form-section">
            <p class="step-label">Step 1 of 3</p>
            <h3>👤 Your Personal Information</h3>

            <div class="field">
                <label for="full_name">Full Name <span class="req">*</span></label>
                <input type="text" id="full_name" name="full_name"
                       placeholder="e.g. Maria Santos"
                       required autocomplete="name"
                       value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>">
            </div>

            <div class="two-col">
                <div class="field">
                    <label for="contact">Contact Number <span class="req">*</span></label>
                    <input type="tel" id="contact" name="contact"
                           placeholder="e.g. 09171234567"
                           required autocomplete="tel"
                           value="<?= htmlspecialchars($_POST['contact'] ?? '') ?>">
                </div>
                <div class="field">
                    <label for="email">
                        Email Address
                        <span class="hint">(optional)</span>
                    </label>
                    <input type="email" id="email" name="email"
                           placeholder="e.g. email@example.com"
                           autocomplete="email"
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                </div>
            </div>
        </div>

        <!-- SECTION 2: Service -->
        <div class="form-section">
            <p class="step-label">Step 2 of 3</p>
            <h3>🏢 Choose a Service <span class="req">*</span></h3>

            <div class="service-grid" id="serviceGrid">

                <label class="service-card" id="card_islamic_marriage">
                    <input type="radio" name="service_type" value="islamic_marriage" required>
                    <div class="svc-info">
                        <p class="svc-name">💍 Islamic Marriage &amp; Certification</p>
                        <p class="svc-desc">Process or request your Islamic marriage certificate or registration documents.</p>
                    </div>
                </label>

                <label class="service-card" id="card_halal_certification">
                    <input type="radio" name="service_type" value="halal_certification">
                    <div class="svc-info">
                        <p class="svc-name">✅ Halal Certification Assistance</p>
                        <p class="svc-desc">Get guidance and support for halal certification of your food, product, or business.</p>
                    </div>
                </label>

                <label class="service-card" id="card_burial_assistance">
                    <input type="radio" name="service_type" value="burial_assistance">
                    <div class="svc-info">
                        <p class="svc-name">🕊️ Muslim Burial Assistance</p>
                        <p class="svc-desc">Request coordination and assistance for Islamic burial procedures and documentation.</p>
                    </div>
                </label>

                <label class="service-card" id="card_scholarship">
                    <input type="radio" name="service_type" value="scholarship">
                    <div class="svc-info">
                        <p class="svc-name">🎓 Scholarship &amp; Financial Assistance</p>
                        <p class="svc-desc">Apply for Muslim community scholarships, education grants, or financial aid programs.</p>
                    </div>
                </label>

            </div>
        </div>

        <!-- SECTION 3: Schedule & Purpose -->
        <div class="form-section">
            <p class="step-label">Step 3 of 3</p>
            <h3>📅 Your Preferred Schedule</h3>

            <div class="two-col">
                <div class="field">
                    <label for="preferred_date">Preferred Date <span class="req">*</span></label>
                    <input type="date" id="preferred_date" name="preferred_date"
                           required min="<?= date('Y-m-d', strtotime('+1 day')) ?>"
                           value="<?= htmlspecialchars($_POST['preferred_date'] ?? '') ?>">
                </div>
                <div class="field">
                    <label for="preferred_time">Preferred Time <span class="req">*</span></label>
                    <select id="preferred_time" name="preferred_time" required>
                        <option value="" disabled selected>-- Select a time --</option>
                        <option value="08:00">8:00 AM</option>
                        <option value="09:00">9:00 AM</option>
                        <option value="10:00">10:00 AM</option>
                        <option value="11:00">11:00 AM</option>
                        <option value="13:00">1:00 PM</option>
                        <option value="14:00">2:00 PM</option>
                        <option value="15:00">3:00 PM</option>
                        <option value="16:00">4:00 PM</option>
                    </select>
                </div>
            </div>

            <div class="field">
                <label for="purpose">
                    Brief Description <span class="hint">(optional but helpful)</span>
                </label>
                <textarea id="purpose" name="purpose"
                          placeholder="Briefly describe your concern or what you need help with. This helps our staff prepare for your visit."><?= htmlspecialchars($_POST['purpose'] ?? '') ?></textarea>
            </div>
        </div>

        <!-- SUBMIT -->
        <div class="submit-wrap">
            <button type="submit" class="submit-btn">
                Submit Appointment Request →
            </button>
        </div>

        <div class="back-row">
            <a href="../index.php">← Go back to home</a>
        </div>

    </form>
</div>

<script>
// Highlight selected service card
document.querySelectorAll('.service-card input[type="radio"]').forEach(function(radio) {
    radio.addEventListener('change', function() {
        document.querySelectorAll('.service-card').forEach(function(c) {
            c.classList.remove('selected');
        });
        this.closest('.service-card').classList.add('selected');
    });
});
</script>

</body>
</html>
