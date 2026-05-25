<?php session_start(); include "config/database.php"; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MCAD — Event Management System</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #121212;
            color: #fff;
            display: flex;
            height: 100vh;
            overflow: hidden;
        }

        /* ── LEFT SIDEBAR ── */
        .sidebar {
            width: 240px;
            background: #000;
            display: flex;
            flex-direction: column;
            padding: 24px 0;
            flex-shrink: 0;
            height: 100vh;
        }

        .sidebar-logo {
            padding: 0 24px 28px;
            border-bottom: 1px solid rgba(255,255,255,.08);
        }

        .logo-badge {
            display: inline-block;
            background: linear-gradient(135deg, #ff4dc4, #ff00aa);
            color: white;
            font-size: 22px;
            font-weight: 900;
            letter-spacing: 1px;
            padding: 8px 16px;
            border-radius: 10px;
            margin-bottom: 8px;
        }

        .logo-sub {
            font-size: 11px;
            color: rgba(255,255,255,.45);
            letter-spacing: 1px;
            text-transform: uppercase;
            line-height: 1.4;
        }

        .sidebar-nav {
            padding: 20px 12px;
            flex: 1;
        }

        .nav-label {
            font-size: 11px;
            font-weight: 700;
            color: rgba(255,255,255,.35);
            text-transform: uppercase;
            letter-spacing: 1px;
            padding: 0 12px 10px;
            margin-top: 10px;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 13px 12px;
            border-radius: 8px;
            text-decoration: none;
            color: rgba(255,255,255,.65);
            font-size: 15px;
            font-weight: 500;
            transition: all .2s;
            cursor: pointer;
            border: none;
            background: none;
            width: 100%;
            text-align: left;
        }

        .nav-item:hover, .nav-item.active {
            background: rgba(255,255,255,.08);
            color: #fff;
        }

        .nav-item.active { color: #fff; font-weight: 700; }

        .nav-icon { width: 20px; height: 20px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .nav-icon svg { display: block; }

        .sidebar-footer {
            padding: 16px 24px;
            border-top: 1px solid rgba(255,255,255,.08);
        }

        .footer-note {
            font-size: 11px;
            color: rgba(255,255,255,.3);
            line-height: 1.5;
        }

        /* ── MAIN CONTENT ── */
        .main {
            flex: 1;
            overflow-y: auto;
            background: linear-gradient(180deg, #1a0a2e 0%, #121212 35%);
        }

        /* Scrollbar */
        .main::-webkit-scrollbar { width: 6px; }
        .main::-webkit-scrollbar-track { background: transparent; }
        .main::-webkit-scrollbar-thumb { background: rgba(255,255,255,.2); border-radius: 3px; }

        /* ── TOP BAR ── */
        .topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 20px 32px 0;
            position: sticky;
            top: 0;
            z-index: 10;
            background: linear-gradient(180deg, rgba(26,10,46,.95) 80%, transparent);
        }

        .topbar-actions { display: flex; gap: 10px; }

        .top-btn {
            padding: 10px 24px;
            border-radius: 25px;
            border: none;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
            transition: all .2s;
        }

        .top-btn-ghost {
            background: transparent;
            color: rgba(255,255,255,.7);
        }
        .top-btn-ghost:hover { color: #fff; }

        .top-btn-white {
            background: #fff;
            color: #000;
        }
        .top-btn-white:hover { background: #e0e0e0; transform: scale(1.03); }

        /* ── HERO ── */
        .hero {
            padding: 32px 32px 28px;
        }

        .hero-tag {
            font-size: 12px;
            font-weight: 700;
            color: #ff4dc4;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 12px;
        }

        .hero-title {
            font-size: 42px;
            font-weight: 900;
            line-height: 1.15;
            max-width: 600px;
            margin-bottom: 16px;
        }

        .hero-title span { color: #ff4dc4; }

        .hero-desc {
            font-size: 16px;
            color: rgba(255,255,255,.6);
            max-width: 520px;
            line-height: 1.7;
            margin-bottom: 28px;
        }

        .hero-cta {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 16px 32px;
            background: linear-gradient(135deg, #ff4dc4, #ff00aa);
            color: white;
            font-size: 16px;
            font-weight: 700;
            border-radius: 50px;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: all .2s;
            box-shadow: 0 6px 24px rgba(255,0,170,.35);
        }

        .hero-cta:hover {
            transform: scale(1.04);
            box-shadow: 0 10px 32px rgba(255,0,170,.5);
        }

        /* ── SECTION ── */
        .section {
            padding: 0 32px 32px;
        }

        .section-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 18px;
        }

        .section-title {
            font-size: 22px;
            font-weight: 800;
        }

        .section-more {
            font-size: 12px;
            font-weight: 700;
            color: rgba(255,255,255,.45);
            text-transform: uppercase;
            letter-spacing: 1px;
            text-decoration: none;
            transition: color .2s;
        }
        .section-more:hover { color: #fff; }

        /* ── SERVICE CARDS ── */
        .cards-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
        }

        .card {
            background: #1e1e1e;
            border-radius: 12px;
            padding: 20px;
            cursor: pointer;
            transition: background .2s, transform .2s;
            text-decoration: none;
            color: inherit;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .card:hover { background: #2a2a2a; transform: translateY(-3px); }

        .card-icon {
            width: 52px; height: 52px;
            border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
            transition: transform .25s ease;
        }

        .card:hover .card-icon { transform: scale(1.1) rotate(-3deg); }
        .card-icon svg { display: block; }

        .card-title { font-size: 15px; font-weight: 700; line-height: 1.3; }
        .card-desc  { font-size: 13px; color: rgba(255,255,255,.5); line-height: 1.5; }

        /* ── INFO STRIP (horizontal cards) ── */
        .info-strip {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 14px;
            margin-bottom: 32px;
        }

        .info-card {
            background: rgba(255,255,255,.05);
            border: 1px solid rgba(255,255,255,.08);
            border-radius: 12px;
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .info-card-icon {
            width: 44px; height: 44px; border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }

        .info-card-text { font-size: 13px; color: rgba(255,255,255,.65); line-height: 1.5; }
        .info-card-text strong { display: block; color: #fff; font-size: 15px; margin-bottom: 3px; }

        /* ── PANEL (view switcher) ── */
        .panel { display: none; }
        .panel.active { display: block; }

        /* ── SIGN IN PANEL ── */
        .auth-panel {
            max-width: 420px;
            margin: 0 auto;
            padding: 20px 32px 40px;
        }

        .auth-title { font-size: 28px; font-weight: 800; margin-bottom: 8px; }
        .auth-sub   { font-size: 15px; color: rgba(255,255,255,.5); margin-bottom: 28px; }

        .form-field { margin-bottom: 18px; }

        .form-field label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: rgba(255,255,255,.75);
            margin-bottom: 8px;
        }

        .form-field input {
            width: 100%;
            padding: 14px 18px;
            background: rgba(255,255,255,.07);
            border: 1.5px solid rgba(255,255,255,.12);
            border-radius: 10px;
            color: #fff;
            font-size: 15px;
            outline: none;
            transition: border-color .2s;
        }

        .form-field input:focus {
            border-color: #ff00aa;
            background: rgba(255,255,255,.1);
        }

        .form-field input::placeholder { color: rgba(255,255,255,.3); }

        .form-submit {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #ff4dc4, #ff00aa);
            color: white;
            font-size: 16px;
            font-weight: 700;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            transition: all .2s;
            margin-top: 6px;
            box-shadow: 0 4px 18px rgba(255,0,170,.3);
        }

        .form-submit:hover { opacity: .92; transform: translateY(-1px); }

        .auth-switch {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
            color: rgba(255,255,255,.45);
        }

        .auth-switch a {
            color: #ff4dc4;
            text-decoration: none;
            font-weight: 600;
        }

        .auth-switch a:hover { text-decoration: underline; }

        .form-error {
            background: rgba(220,38,38,.15);
            border: 1px solid rgba(220,38,38,.4);
            border-radius: 8px;
            padding: 12px 16px;
            font-size: 13px;
            color: #fca5a5;
            margin-bottom: 18px;
            display: none;
        }
    </style>
</head>
<body>

<!-- ── SIDEBAR ── -->
<div class="sidebar">
    <div class="sidebar-logo">
        <div class="logo-badge">MCAD</div>
        <p class="logo-sub">Muslim Concerns<br>and Affairs Division</p>
    </div>

    <div class="sidebar-nav">
        <p class="nav-label">Navigate</p>

        <button class="nav-item active" onclick="showPanel('home')">
            <span class="nav-icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg></span> Home
        </button>

        <a class="nav-item" href="pages/book_appointment.php">
            <span class="nav-icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><rect x="8" y="2" width="8" height="4" rx="1"/></svg></span> Book Appointment
        </a>

        <button class="nav-item" onclick="showPanel('signin')">
            <span class="nav-icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg></span> Sign In
        </button>

        <button class="nav-item" onclick="showPanel('register')">
            <span class="nav-icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><line x1="20" y1="8" x2="20" y2="14"/><line x1="23" y1="11" x2="17" y2="11"/></svg></span> Register
        </button>

        <!-- Commented out redundant individual service links since they are accessed via Book Appointment portal -->
        <!--
        <p class="nav-label" style="margin-top:20px;">Services</p>

        <a class="nav-item" href="pages/book_marriage.php">
            <span class="nav-icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg></span> Islamic Marriage
        </a>

        <a class="nav-item" href="pages/book_halal.php">
            <span class="nav-icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><polyline points="9 12 11 14 15 10"/></svg></span> Halal Certification
        </a>

        <a class="nav-item" href="pages/book_burial.php">
            <span class="nav-icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg></span> Burial Assistance
        </a>

        <a class="nav-item" href="pages/book_scholarship.php">
            <span class="nav-icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 10 12 5 2 10 12 15 22 10"/><polyline points="6 12 6 18"/><path d="M6 18c3 2 9 2 12 0v-6"/><line x1="22" y1="10" x2="22" y2="16"/></svg></span> Scholarship Aid
        </a>
        -->
    </div>

    <div class="sidebar-footer">
        <p class="footer-note">City Mayor's Office<br>General Santos City</p>
    </div>
</div>

<!-- ── MAIN ── -->
<div class="main" id="mainArea">

    <!-- TOP BAR -->
    <div class="topbar">
        <div></div>
        <div class="topbar-actions">
            <a href="#" class="top-btn top-btn-ghost" onclick="showPanel('register'); return false;">Register</a>
            <a href="#" class="top-btn top-btn-white" onclick="showPanel('signin'); return false;">Sign In</a>
        </div>
    </div>

    <!-- ══ HOME PANEL ══ -->
    <div id="panel-home" class="panel active">

        <!-- Hero -->
        <div class="hero">
            <p class="hero-tag">City Mayor's Office — MCAD</p>
            <h1 class="hero-title">
                Web-Based Event Management<br>System with
                <span>Community Forum</span>
            </h1>
            <p class="hero-desc">
                A centralized platform for managing Ramadan programs, community events,
                announcements, donations, and appointments for the Muslim community
                in General Santos City.
            </p>
            <a href="pages/book_appointment.php" class="hero-cta">
                📋 Book an Appointment
            </a>
        </div>

        <!-- Services -->
        <div class="section">
            <div class="section-header">
                <h2 class="section-title">Our Services</h2>
                <a href="pages/book_appointment.php" class="section-more">Book now →</a>
            </div>
            <div class="cards-grid">

                <a href="pages/book_marriage.php" class="card">
                    <div class="card-icon" style="background:rgba(255,77,196,.12);">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#ff4dc4" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                    </div>
                    <div>
                        <p class="card-title">Islamic Marriage &amp; Certification</p>
                        <p class="card-desc">Process or request your Islamic marriage certificate and registration documents.</p>
                    </div>
                </a>

                <a href="pages/book_halal.php" class="card">
                    <div class="card-icon" style="background:rgba(34,197,94,.12);">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#22c55e" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><polyline points="9 12 11 14 15 10"/></svg>
                    </div>
                    <div>
                        <p class="card-title">Halal Certification Assistance</p>
                        <p class="card-desc">Guidance and support for halal certification of your food, product, or business.</p>
                    </div>
                </a>

                <a href="pages/book_burial.php" class="card">
                    <div class="card-icon" style="background:rgba(139,92,246,.12);">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#a78bfa" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
                    </div>
                    <div>
                        <p class="card-title">Muslim Burial Assistance</p>
                        <p class="card-desc">Coordination and assistance for Islamic burial procedures and documentation.</p>
                    </div>
                </a>

                <a href="pages/book_scholarship.php" class="card">
                    <div class="card-icon" style="background:rgba(251,191,36,.12);">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#fbbf24" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 10 12 5 2 10 12 15 22 10"/><polyline points="6 12 6 18"/><path d="M6 18c3 2 9 2 12 0v-6"/><line x1="22" y1="10" x2="22" y2="16"/></svg>
                    </div>
                    <div>
                        <p class="card-title">Scholarship &amp; Financial Aid</p>
                        <p class="card-desc">Apply for Muslim community scholarships, education grants, or financial aid.</p>
                    </div>
                </a>

            </div>
        </div>

        <!-- Info strip -->
        <div class="section">
            <div class="info-strip">
                <div class="info-card">
                    <div class="info-card-icon" style="background:rgba(255,77,196,.12);">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#ff4dc4" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    </div>
                    <div class="info-card-text">
                        <strong>Office Hours</strong>
                        Monday – Friday<br>8:00 AM – 5:00 PM
                    </div>
                </div>
                <div class="info-card">
                    <div class="info-card-icon" style="background:rgba(79,172,254,.12);">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#4facfe" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                    </div>
                    <div class="info-card-text">
                        <strong>Location</strong>
                        City Mayor's Office<br>General Santos City
                    </div>
                </div>
                <div class="info-card">
                    <div class="info-card-icon" style="background:rgba(67,233,123,.12);">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#43e97b" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                    </div>
                    <div class="info-card-text">
                        <strong>Walk-ins Welcome</strong>
                        No appointment needed,<br>but booking reduces wait time.
                    </div>
                </div>
            </div>
        </div>

    </div><!-- /home panel -->

    <!-- ══ SIGN IN PANEL ══ -->
    <div id="panel-signin" class="panel">
        <div class="auth-panel">
            <h2 class="auth-title" style="margin-top:40px;">Welcome back</h2>
            <p class="auth-sub">Sign in to your MCAD account</p>

            <div class="form-error" id="signinError"></div>

            <form method="POST" action="actions/login_action.php" id="signinForm">
                <?= csrf_field() ?>
                <div class="form-field">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email"
                           placeholder="e.g. admin@email.com" required>
                </div>
                <div class="form-field">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password"
                           placeholder="Enter your password" required>
                </div>
                <button type="submit" class="form-submit">Sign In</button>
            </form>

            <p class="auth-switch">
                Don't have an account?
                <a href="#" onclick="showPanel('register'); return false;">Register here</a>
            </p>
            <p class="auth-switch" style="margin-top:10px;">
                <a href="#" onclick="showPanel('home'); return false;">← Back to Home</a>
            </p>
        </div>
    </div>

    <!-- ══ REGISTER PANEL ══ -->
    <div id="panel-register" class="panel">
        <div class="auth-panel">
            <h2 class="auth-title" style="margin-top:40px;">Create an Account</h2>
            <p class="auth-sub">Join the MCAD community platform</p>

            <form method="POST" action="actions/register_action.php">
                <?= csrf_field() ?>
                <div class="form-field">
                    <label for="reg_name">Full Name</label>
                    <input type="text" id="reg_name" name="name"
                           placeholder="e.g. Maria Santos" required>
                </div>
                <div class="form-field">
                    <label for="reg_email">Email Address</label>
                    <input type="email" id="reg_email" name="email"
                           placeholder="e.g. email@example.com" required>
                </div>
                <div class="form-field">
                    <label for="reg_password">Password <span style="color:rgba(255,255,255,.35);font-weight:400;">(min. 8 characters)</span></label>
                    <input type="password" id="reg_password" name="password"
                           placeholder="Create a strong password" required>
                </div>
                <div class="form-field">
                    <label for="reg_role">Role</label>
                    <input type="text" id="reg_role" name="role" value="viewer"
                           placeholder="viewer" readonly
                           style="opacity:.5;cursor:not-allowed;">
                </div>
                <button type="submit" class="form-submit">Create Account</button>
            </form>

            <p class="auth-switch">
                Already have an account?
                <a href="#" onclick="showPanel('signin'); return false;">Sign in</a>
            </p>
            <p class="auth-switch" style="margin-top:10px;">
                <a href="#" onclick="showPanel('home'); return false;">← Back to Home</a>
            </p>
        </div>
    </div>

</div><!-- /main -->

<script>
function showPanel(name) {
    // Hide all panels
    document.querySelectorAll('.panel').forEach(function(p) {
        p.classList.remove('active');
    });
    // Show target
    document.getElementById('panel-' + name).classList.add('active');

    // Update sidebar active state
    document.querySelectorAll('.nav-item').forEach(function(btn) {
        btn.classList.remove('active');
    });

    // Scroll main to top
    document.getElementById('mainArea').scrollTo({ top: 0, behavior: 'smooth' });
}

// Handle URL hash for direct panel access
(function() {
    var hash = window.location.hash.replace('#', '');
    if (['home','appointment','signin','register'].includes(hash)) {
        showPanel(hash);
    }
})();
</script>

</body>
</html>
