<?php
session_start();
include "../config/database.php";
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$role = $_SESSION['role'];
include "../includes/header.php";
include "../includes/sidebar.php";
?>
<style>
.page-container { margin-left:260px; padding:90px 30px 40px; background:#121212; min-height:100vh; color:#fff; }
.page-header { margin-bottom:30px; }
.page-title    { font-size:26px; font-weight:800; color:#fff; margin:0 0 6px; }
.page-subtitle { font-size:14px; color:rgba(255,255,255,.4); margin:0; }

/* Search */
.help-search {
    margin-bottom:30px; position:relative;
}
.help-search input {
    width:100%; max-width:520px; padding:14px 20px 14px 46px;
    background:#1e1e1e; border:1.5px solid rgba(255,255,255,.1);
    border-radius:14px; color:#fff; font-size:15px; outline:none;
    transition:border-color .2s;
}
.help-search input::placeholder { color:rgba(255,255,255,.3); }
.help-search input:focus { border-color:#ff00aa; }
.help-search-icon { position:absolute; left:16px; top:50%; transform:translateY(-50%); font-size:17px; opacity:.5; }

/* Quick cards */
.quick-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:14px; margin-bottom:30px; }
@media(max-width:900px){ .quick-grid{ grid-template-columns:repeat(2,1fr); } }
.quick-card {
    background:#1e1e1e; border-radius:14px; padding:20px; text-align:center;
    border:1px solid rgba(255,255,255,.06); cursor:pointer;
    transition:all .2s;
    text-decoration:none; display:block;
}
.quick-card:hover { border-color:#ff00aa; background:rgba(255,0,170,.06); transform:translateY(-2px); }
.quick-card .qc-icon { font-size:28px; margin-bottom:10px; }
.quick-card .qc-title { font-size:14px; font-weight:700; color:#fff; margin:0 0 4px; }
.quick-card .qc-desc  { font-size:12px; color:rgba(255,255,255,.4); margin:0; }

/* Layout */
.help-layout { display:grid; grid-template-columns:220px 1fr; gap:20px; align-items:start; }
@media(max-width:800px){ .help-layout{ grid-template-columns:1fr; } }

/* Category nav */
.help-nav { background:#1e1e1e; border-radius:16px; padding:14px; border:1px solid rgba(255,255,255,.06); position:sticky; top:90px; }
.help-nav-item {
    display:flex; align-items:center; gap:10px;
    padding:10px 14px; border-radius:10px; cursor:pointer;
    font-size:13px; color:rgba(255,255,255,.55);
    transition:all .2s; border:none; background:none;
    width:100%; text-align:left;
}
.help-nav-item:hover  { background:rgba(255,255,255,.06); color:#fff; }
.help-nav-item.active { background:rgba(255,0,170,.12); color:#ff4dc4; font-weight:600; }

/* Accordion */
.faq-section { margin-bottom:20px; }
.faq-section-title { font-size:17px; font-weight:700; color:#fff; margin:0 0 14px; padding-bottom:12px; border-bottom:1px solid rgba(255,255,255,.07); display:flex; align-items:center; gap:8px; }

.faq-item { background:#1e1e1e; border-radius:12px; border:1px solid rgba(255,255,255,.06); margin-bottom:8px; overflow:hidden; }
.faq-question {
    width:100%; padding:16px 20px; background:none; border:none;
    text-align:left; color:#fff; font-size:14px; font-weight:600;
    cursor:pointer; display:flex; justify-content:space-between; align-items:center;
    gap:12px; transition:background .2s;
}
.faq-question:hover { background:rgba(255,255,255,.04); }
.faq-arrow { font-size:12px; color:rgba(255,255,255,.4); transition:transform .25s; flex-shrink:0; }
.faq-item.open .faq-arrow { transform:rotate(180deg); }
.faq-answer {
    max-height:0; overflow:hidden; transition:max-height .3s ease;
    padding:0 20px; color:rgba(255,255,255,.65); font-size:14px; line-height:1.7;
}
.faq-item.open .faq-answer { max-height:400px; padding:0 20px 16px; }

/* Role badge */
.role-tag { font-size:11px; padding:2px 8px; border-radius:6px; font-weight:600; margin-left:6px; }

/* Contact card */
.contact-card { background:linear-gradient(135deg,rgba(255,0,170,.1),rgba(167,139,250,.1)); border:1px solid rgba(255,0,170,.2); border-radius:16px; padding:28px; text-align:center; }
.contact-card h3 { font-size:18px; font-weight:700; color:#fff; margin:0 0 8px; }
.contact-card p  { color:rgba(255,255,255,.5); font-size:14px; margin:0 0 20px; }
.contact-info { display:flex; justify-content:center; gap:30px; flex-wrap:wrap; }
.contact-item { display:flex; align-items:center; gap:8px; color:rgba(255,255,255,.7); font-size:14px; }
</style>

<div class="page-container">

    <div class="page-header">
        <h1 class="page-title">❓ Help Center</h1>
        <p class="page-subtitle">Find answers and learn how to use the MCAD Event Management System</p>
    </div>

    <div class="help-search">
        <span class="help-search-icon">🔍</span>
        <input type="text" id="helpSearch" placeholder="Search help articles..." oninput="filterFAQ(this.value)">
    </div>

    <!-- Quick Links -->
    <div class="quick-grid">
        <a class="quick-card" onclick="jumpTo('getting-started')">
            <div class="qc-icon">🚀</div>
            <p class="qc-title">Getting Started</p>
            <p class="qc-desc">First steps and account setup</p>
        </a>
        <a class="quick-card" onclick="jumpTo('events')">
            <div class="qc-icon">📅</div>
            <p class="qc-title">Events & Programs</p>
            <p class="qc-desc">Create and manage community events</p>
        </a>
        <a class="quick-card" onclick="jumpTo('donations')">
            <div class="qc-icon">💰</div>
            <p class="qc-title">Donations</p>
            <p class="qc-desc">Record and track contributions</p>
        </a>
        <a class="quick-card" onclick="jumpTo('forum')">
            <div class="qc-icon">💬</div>
            <p class="qc-title">Community Forum</p>
            <p class="qc-desc">Discussions and community posts</p>
        </a>
    </div>

    <div class="help-layout">

        <!-- Category Nav -->
        <div class="help-nav">
            <button class="help-nav-item active" onclick="jumpTo('getting-started', this)">🚀 Getting Started</button>
            <button class="help-nav-item" onclick="jumpTo('events', this)">📅 Events</button>
            <button class="help-nav-item" onclick="jumpTo('announcements', this)">📢 Announcements</button>
            <button class="help-nav-item" onclick="jumpTo('attendance', this)">✅ Attendance</button>
            <button class="help-nav-item" onclick="jumpTo('donations', this)">💰 Donations</button>
            <button class="help-nav-item" onclick="jumpTo('forum', this)">💬 Forum</button>
            <button class="help-nav-item" onclick="jumpTo('reports', this)">📊 Reports</button>
            <button class="help-nav-item" onclick="jumpTo('roles', this)">👥 Roles & Access</button>
        </div>

        <!-- FAQ Content -->
        <div id="faq-container">

            <!-- Getting Started -->
            <div class="faq-section" id="section-getting-started">
                <h3 class="faq-section-title">🚀 Getting Started</h3>

                <div class="faq-item">
                    <button class="faq-question" onclick="toggleFAQ(this)">
                        What is the MCAD Event Management System?
                        <span class="faq-arrow">▼</span>
                    </button>
                    <div class="faq-answer">
                        The MCAD system is a web-based platform developed for the City Mayor's Office Muslim Concerns and Affairs Division in General Santos City. It helps community leaders, Imams, and barangay officials coordinate Ramadan programs, track attendance, manage donations, and communicate through a community forum — all in one place.
                    </div>
                </div>

                <div class="faq-item">
                    <button class="faq-question" onclick="toggleFAQ(this)">
                        How do I log in to the system?
                        <span class="faq-arrow">▼</span>
                    </button>
                    <div class="faq-answer">
                        Go to the main page at <strong>localhost/mcad/WEBBASEMANAGEMENTSYSTEM/</strong> and click <em>Sign In</em>. Enter your registered email address and password. If you don't have an account, click <em>Register</em> to create one. Your account will be assigned a role and community by an administrator.
                    </div>
                </div>

                <div class="faq-item">
                    <button class="faq-question" onclick="toggleFAQ(this)">
                        How do I update my name or email?
                        <span class="faq-arrow">▼</span>
                    </button>
                    <div class="faq-answer">
                        Click your name in the top-right corner → <strong>My Profile</strong>. On the Profile Info tab, update your name and email, then click <em>Save Changes</em>. Your name will update across the system immediately.
                    </div>
                </div>

                <div class="faq-item">
                    <button class="faq-question" onclick="toggleFAQ(this)">
                        How do I change my password?
                        <span class="faq-arrow">▼</span>
                    </button>
                    <div class="faq-answer">
                        Go to <strong>My Profile → Security tab</strong>. Enter your current password, then type and confirm your new password (minimum 8 characters). A strength indicator will show how secure your new password is. Click <em>Update Password</em> to save.
                    </div>
                </div>
            </div>

            <!-- Events -->
            <div class="faq-section" id="section-events">
                <h3 class="faq-section-title">📅 Events &amp; Programs</h3>

                <div class="faq-item">
                    <button class="faq-question" onclick="toggleFAQ(this)">
                        How do I create a new event?
                        <span class="faq-arrow">▼</span>
                    </button>
                    <div class="faq-answer">
                        Go to <strong>All Events</strong> in the sidebar and click the pink <em>+ Create Event</em> button. Fill in the title, description, date, time, venue, visibility (Personal or Community), and status. Click <em>Create Event</em> to publish it. <span class="role-tag" style="background:rgba(96,165,250,.15);color:#60a5fa;">Leader</span><span class="role-tag" style="background:rgba(251,191,36,.15);color:#fbbf24;">Imam</span><span class="role-tag" style="background:rgba(167,139,250,.15);color:#a78bfa;">Admin</span>
                    </div>
                </div>

                <div class="faq-item">
                    <button class="faq-question" onclick="toggleFAQ(this)">
                        What are the event status options?
                        <span class="faq-arrow">▼</span>
                    </button>
                    <div class="faq-answer">
                        Events have three statuses: <strong>Upcoming</strong> — scheduled for the future; <strong>Ongoing</strong> — currently happening; <strong>Completed</strong> — already finished. You can update the status by editing the event at any time.
                    </div>
                </div>

                <div class="faq-item">
                    <button class="faq-question" onclick="toggleFAQ(this)">
                        What are System Events?
                        <span class="faq-arrow">▼</span>
                    </button>
                    <div class="faq-answer">
                        System Events are automatically generated daily prayer-time events: <strong>Iftar</strong> (6:00 PM), <strong>Taraweeh</strong> (7:30 PM), and <strong>Suhoor</strong> (4:30 AM). They are visible to all communities and created automatically when you visit the Dashboard or Events page.
                    </div>
                </div>

                <div class="faq-item">
                    <button class="faq-question" onclick="toggleFAQ(this)">
                        How do I use the calendar view?
                        <span class="faq-arrow">▼</span>
                    </button>
                    <div class="faq-answer">
                        The calendar on the Events page shows the current month. Dates with events have a pink dot below them. Click any date to filter the event list and show only events for that day. Use the ‹ › arrows to navigate between months.
                    </div>
                </div>

                <div class="faq-item">
                    <button class="faq-question" onclick="toggleFAQ(this)">
                        Can I delete an event?
                        <span class="faq-arrow">▼</span>
                    </button>
                    <div class="faq-answer">
                        Yes. Each event card has a red <em>Delete</em> button. You will be asked to confirm before the event is permanently removed. Only Admins, Imams, and Community Leaders can delete events.
                    </div>
                </div>
            </div>

            <!-- Announcements -->
            <div class="faq-section" id="section-announcements">
                <h3 class="faq-section-title">📢 Announcements</h3>

                <div class="faq-item">
                    <button class="faq-question" onclick="toggleFAQ(this)">
                        Who can post announcements?
                        <span class="faq-arrow">▼</span>
                    </button>
                    <div class="faq-answer">
                        Only <strong>Admins</strong> and <strong>Imams</strong> can create official announcements. Community Leaders and Residents can view announcements but cannot post them.
                    </div>
                </div>

                <div class="faq-item">
                    <button class="faq-question" onclick="toggleFAQ(this)">
                        What are announcement categories?
                        <span class="faq-arrow">▼</span>
                    </button>
                    <div class="faq-answer">
                        Announcements are categorized into four types: <strong>Prayer Schedule</strong> — prayer times and mosque schedules; <strong>Event Reminder</strong> — upcoming program reminders; <strong>Charity Drive</strong> — donation campaigns; <strong>Community Advisory</strong> — general community notices.
                    </div>
                </div>

                <div class="faq-item">
                    <button class="faq-question" onclick="toggleFAQ(this)">
                        Are announcements community-specific?
                        <span class="faq-arrow">▼</span>
                    </button>
                    <div class="faq-answer">
                        Yes. Announcements are scoped to the community of the user who posted them. Admins can see announcements from all communities. Members of Lagao, Bula, or Uhaw will only see announcements from their own community.
                    </div>
                </div>
            </div>

            <!-- Attendance -->
            <div class="faq-section" id="section-attendance">
                <h3 class="faq-section-title">✅ Attendance Tracking</h3>

                <div class="faq-item">
                    <button class="faq-question" onclick="toggleFAQ(this)">
                        How do I record attendance for an event?
                        <span class="faq-arrow">▼</span>
                    </button>
                    <div class="faq-answer">
                        Go to <strong>Attendance</strong> in the sidebar. Select the event from the dropdown, then enter each attendee's name and mark their status as <em>Present</em> or <em>Absent</em>. Click <em>Add Attendee</em> to save each record.
                    </div>
                </div>

                <div class="faq-item">
                    <button class="faq-question" onclick="toggleFAQ(this)">
                        Can I view attendance for a specific event?
                        <span class="faq-arrow">▼</span>
                    </button>
                    <div class="faq-answer">
                        Yes. Use the event filter on the Attendance page to view all recorded attendance for a specific event. The page shows a summary of total present and absent counts, and lists each attendee with their status.
                    </div>
                </div>

                <div class="faq-item">
                    <button class="faq-question" onclick="toggleFAQ(this)">
                        Where do attendance statistics appear?
                        <span class="faq-arrow">▼</span>
                    </button>
                    <div class="faq-answer">
                        Attendance totals appear on the <strong>Dashboard</strong> (Total Attendance and Present counts) and on the <strong>Reports &amp; Analytics</strong> page, where you can see the latest 20 attendance records and print an attendance summary.
                    </div>
                </div>
            </div>

            <!-- Donations -->
            <div class="faq-section" id="section-donations">
                <h3 class="faq-section-title">💰 Donations &amp; Resources</h3>

                <div class="faq-item">
                    <button class="faq-question" onclick="toggleFAQ(this)">
                        What donation types can I record?
                        <span class="faq-arrow">▼</span>
                    </button>
                    <div class="faq-answer">
                        Three types: <strong>Cash</strong> — monetary donations with a peso amount; <strong>Food</strong> — food items with a quantity count; <strong>Supplies</strong> — non-food items with a quantity count. Select the type first and the form will show the correct field (amount or quantity).
                    </div>
                </div>

                <div class="faq-item">
                    <button class="faq-question" onclick="toggleFAQ(this)">
                        What is a Distribution record?
                        <span class="faq-arrow">▼</span>
                    </button>
                    <div class="faq-answer">
                        A Distribution record tracks when donated resources are given out to beneficiaries. Enter the beneficiary's name, the item distributed, the quantity, and the distribution date. This helps track how resources from donations are being used.
                    </div>
                </div>

                <div class="faq-item">
                    <button class="faq-question" onclick="toggleFAQ(this)">
                        Is the donor name required?
                        <span class="faq-arrow">▼</span>
                    </button>
                    <div class="faq-answer">
                        No. The donor name field is optional. If left blank, the donation will be recorded as from <em>Anonymous</em>. This respects donor privacy while still keeping complete financial records.
                    </div>
                </div>
            </div>

            <!-- Forum -->
            <div class="faq-section" id="section-forum">
                <h3 class="faq-section-title">💬 Community Forum</h3>

                <div class="faq-item">
                    <button class="faq-question" onclick="toggleFAQ(this)">
                        Who can post in the Community Forum?
                        <span class="faq-arrow">▼</span>
                    </button>
                    <div class="faq-answer">
                        All registered users — including Residents (Viewers) — can create forum threads and reply to existing discussions. The forum is community-scoped, so you will see posts from your own community.
                    </div>
                </div>

                <div class="faq-item">
                    <button class="faq-question" onclick="toggleFAQ(this)">
                        What can Admins and Imams do in the Forum?
                        <span class="faq-arrow">▼</span>
                    </button>
                    <div class="faq-answer">
                        Admins and Imams have moderation powers: they can <strong>pin</strong> important threads to keep them at the top, and <strong>delete</strong> inappropriate or outdated posts including all their replies.
                    </div>
                </div>

                <div class="faq-item">
                    <button class="faq-question" onclick="toggleFAQ(this)">
                        Can I delete my own forum post?
                        <span class="faq-arrow">▼</span>
                    </button>
                    <div class="faq-answer">
                        Only Admins and Imams can delete posts through the moderation panel. If you need a post removed, please contact your community Admin or Imam.
                    </div>
                </div>
            </div>

            <!-- Reports -->
            <div class="faq-section" id="section-reports">
                <h3 class="faq-section-title">📊 Reports &amp; Analytics</h3>

                <div class="faq-item">
                    <button class="faq-question" onclick="toggleFAQ(this)">
                        What reports are available?
                        <span class="faq-arrow">▼</span>
                    </button>
                    <div class="faq-answer">
                        The Reports page includes: <strong>Ramadan Event Schedule</strong> — full list of all community events; <strong>Attendance Summary</strong> — latest attendance records with present/absent status; <strong>Donation Records</strong> — complete list of all donations with amounts and dates.
                    </div>
                </div>

                <div class="faq-item">
                    <button class="faq-question" onclick="toggleFAQ(this)">
                        How do I print or save a report?
                        <span class="faq-arrow">▼</span>
                    </button>
                    <div class="faq-answer">
                        Click the pink <strong>Print / Save PDF</strong> button at the top of the Reports page. This opens your browser's print dialog. Select <em>Save as PDF</em> as the destination to download the report as a PDF file. The sidebar, header, and buttons are hidden in the print view for a clean output.
                    </div>
                </div>
            </div>

            <!-- Roles -->
            <div class="faq-section" id="section-roles">
                <h3 class="faq-section-title">👥 Roles &amp; Access Levels</h3>

                <div class="faq-item">
                    <button class="faq-question" onclick="toggleFAQ(this)">
                        What can an Admin do?
                        <span class="faq-arrow">▼</span>
                    </button>
                    <div class="faq-answer">
                        Admins have full system access: create/edit/delete events and announcements, manage all user accounts (activate, suspend), view all communities' data, manage donations and distributions, post announcements, moderate the forum, and generate all reports.
                    </div>
                </div>

                <div class="faq-item">
                    <button class="faq-question" onclick="toggleFAQ(this)">
                        What can an Imam do?
                        <span class="faq-arrow">▼</span>
                    </button>
                    <div class="faq-answer">
                        Imams can create and manage events, post official announcements (including prayer schedules), moderate the community forum (pin/delete posts), and record attendance. They cannot manage user accounts.
                    </div>
                </div>

                <div class="faq-item">
                    <button class="faq-question" onclick="toggleFAQ(this)">
                        What can a Community Leader do?
                        <span class="faq-arrow">▼</span>
                    </button>
                    <div class="faq-answer">
                        Community Leaders can create events, record attendance, manage donations and distributions, and participate in the forum. They cannot post official announcements or manage user accounts.
                    </div>
                </div>

                <div class="faq-item">
                    <button class="faq-question" onclick="toggleFAQ(this)">
                        What can a Resident (Viewer) do?
                        <span class="faq-arrow">▼</span>
                    </button>
                    <div class="faq-answer">
                        Residents have read access to view published events, announcements, and the prayer schedule. They can participate in the Community Forum by posting and replying to threads. They cannot create events, record donations, or modify any data.
                    </div>
                </div>
            </div>

            <!-- Contact -->
            <div class="contact-card">
                <h3>Still need help?</h3>
                <p>Contact the MCAD system administrator or your community leader for assistance.</p>
                <div class="contact-info">
                    <div class="contact-item">🏛️ City Mayor's Office — MCAD</div>
                    <div class="contact-item">📍 General Santos City</div>
                    <div class="contact-item">🕌 Muslim Concerns &amp; Affairs Division</div>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
function toggleFAQ(btn) {
    var item = btn.closest('.faq-item');
    item.classList.toggle('open');
}

function jumpTo(sectionId, navBtn) {
    document.querySelectorAll('.help-nav-item').forEach(b => b.classList.remove('active'));
    if (navBtn) navBtn.classList.add('active');
    var el = document.getElementById('section-' + sectionId);
    if (el) el.scrollIntoView({ behavior:'smooth', block:'start' });
}

function filterFAQ(query) {
    var q = query.toLowerCase().trim();
    document.querySelectorAll('.faq-item').forEach(function(item) {
        var text = item.textContent.toLowerCase();
        item.style.display = (!q || text.includes(q)) ? '' : 'none';
        if (q && text.includes(q)) item.classList.add('open');
    });
    document.querySelectorAll('.faq-section').forEach(function(sec) {
        var visible = Array.from(sec.querySelectorAll('.faq-item')).some(i => i.style.display !== 'none');
        sec.style.display = visible ? '' : 'none';
    });
}
</script>
<?php include "../includes/footer.php"; ?>
