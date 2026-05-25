<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select a Service — MCAD Appointment Portal</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Segoe UI', -apple-system, BlinkMacSystemFont, Roboto, sans-serif;
            background: #121212;
            color: #fff;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
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
            padding: 60px 20px 40px;
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
            font-size: 36px;
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

        /* ── MAIN CONTENT WRAPPER ── */
        .page-wrap {
            max-width: 900px;
            width: 100%;
            margin: 40px auto 60px;
            padding: 0 24px;
            flex: 1;
        }

        /* ── NOTICE BOX ── */
        .notice {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255,255,255,.08);
            border-radius: 16px;
            padding: 24px;
            font-size: 15px;
            color: rgba(255,255,255,.75);
            line-height: 1.6;
            margin-bottom: 35px;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .notice-icon {
            font-size: 28px;
            flex-shrink: 0;
        }

        .notice strong { display: block; margin-bottom: 4px; font-size: 16px; color: #fff; }

        /* ── PORTAL GRID ── */
        .services-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        @media (max-width: 680px) {
            .services-grid { grid-template-columns: 1fr; }
            .hero h2 { font-size: 28px; }
            .notice { flex-direction: column; text-align: center; }
        }

        /* ── PREMIUM CARDS ── */
        .service-card {
            background: #1e1e1e;
            border: 1px solid rgba(255, 255, 255, 0.06);
            border-radius: 20px;
            padding: 30px;
            text-decoration: none;
            color: inherit;
            display: flex;
            flex-direction: column;
            gap: 20px;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            cursor: pointer;
        }

        .service-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: radial-gradient(circle at top right, rgba(255, 0, 170, 0.08), transparent 60%);
            opacity: 0;
            transition: opacity 0.3s;
        }

        .service-card:hover {
            transform: translateY(-4px);
            border-color: rgba(255, 77, 196, 0.4);
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.3), 0 0 0 1px rgba(255, 77, 196, 0.1);
        }

        .service-card:hover::before {
            opacity: 1;
        }

        .card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .card-icon {
            width: 60px;
            height: 60px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 26px;
            transition: transform 0.3s;
        }

        .service-card:hover .card-icon {
            transform: scale(1.1) rotate(-3deg);
        }

        .arrow-indicator {
            font-size: 18px;
            color: rgba(255,255,255,.3);
            transition: transform 0.25s, color 0.25s;
        }

        .service-card:hover .arrow-indicator {
            transform: translateX(4px);
            color: #ff4dc4;
        }

        .card-body h3 {
            font-size: 19px;
            font-weight: 800;
            color: #fff;
            margin-bottom: 8px;
            letter-spacing: .25px;
        }

        .card-body p {
            font-size: 14.5px;
            color: rgba(255, 255, 255, 0.6);
            line-height: 1.55;
        }

        /* ── FOOTER ROW ── */
        .footer-row {
            text-align: center;
            margin-top: 30px;
        }

        .footer-row a {
            color: rgba(255,255,255,.4);
            text-decoration: none;
            font-size: 14px;
            transition: color .2s;
        }

        .footer-row a:hover {
            color: #ff00aa;
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
    <h2>📋 MCAD Appointment Portal</h2>
    <p>Select a specialized program below to view specific requirements and schedule an appointment.</p>
</div>

<div class="page-wrap">

    <div class="notice">
        <span class="notice-icon">📌</span>
        <div>
            <strong>Walk-ins Welcome:</strong>
            You are always welcome to visit the MCAD office during standard hours (Monday–Friday, 8:00 AM – 5:00 PM). Booking online helps prepare our team and reduces your in-office waiting time.
        </div>
    </div>

    <!-- SERVICES GRID -->
    <div class="services-grid">

        <!-- Islamic Marriage -->
        <a href="book_marriage.php" class="service-card">
            <div class="card-header">
                <div class="card-icon" style="background: rgba(255, 77, 196, 0.12); color: #ff4dc4;">💍</div>
                <div class="arrow-indicator">→</div>
            </div>
            <div class="card-body">
                <h3>Islamic Marriage &amp; Certification</h3>
                <p>Register an upcoming Islamic marriage, schedule a solemnization ceremony, or request physical certified certificates.</p>
            </div>
        </a>

        <!-- Halal Certification -->
        <a href="book_halal.php" class="service-card">
            <div class="card-header">
                <div class="card-icon" style="background: rgba(34, 197, 94, 0.12); color: #22c55e;">✅</div>
                <div class="arrow-indicator">→</div>
            </div>
            <div class="card-body">
                <h3>Halal Certification Assistance</h3>
                <p>Apply for regulatory consultations, pre-audit kitchen layout assessments, and official accreditor endorsements.</p>
            </div>
        </a>

        <!-- Burial Assistance -->
        <a href="book_burial.php" class="service-card">
            <div class="card-header">
                <div class="card-icon" style="background: rgba(239, 68, 68, 0.12); color: #ef4444;">🕊️</div>
                <div class="arrow-indicator">→</div>
            </div>
            <div class="card-body">
                <h3>Muslim Burial Assistance</h3>
                <p>Coordinate urgent burial slots, washing teams, kafan (shroud) supply, or transport logistics for deceased relatives.</p>
            </div>
        </a>

        <!-- Scholarship -->
        <a href="book_scholarship.php" class="service-card">
            <div class="card-header">
                <div class="card-icon" style="background: rgba(251, 191, 36, 0.12); color: #fbbf24;">🎓</div>
                <div class="arrow-indicator">→</div>
            </div>
            <div class="card-body">
                <h3>Scholarship &amp; Financial Aid</h3>
                <p>Apply for city-sponsored education grants, submit reports or grade sheets, or book academic scholarship interviews.</p>
            </div>
        </a>

    </div>

    <div class="footer-row">
        <a href="../index.php">← Back to Muslim Concerns and Affairs Division Home</a>
    </div>
</div>

</body>
</html>
