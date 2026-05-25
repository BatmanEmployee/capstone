<?php
session_start();
include "../config/database.php";
include "../functions/system_events.php";

date_default_timezone_set('Asia/Manila');

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

generateSystemEvents($conn);

$user_id      = (int) $_SESSION['user_id'];
$role         = $_SESSION['role'];
$community_id = (int) ($_SESSION['community_id'] ?? 0);
$today        = date("Y-m-d");

// Build query — admin sees all, others see community + system
if ($role === 'admin') {
    $eventsRes = $conn->query("SELECT * FROM events ORDER BY event_date ASC, event_time ASC");
} else {
    $eventsRes = $conn->query("
        SELECT * FROM events
        WHERE event_type = 'system'
           OR community_id IS NULL
           OR community_id = $community_id
           OR user_id = $user_id
        ORDER BY event_date ASC, event_time ASC
    ");
}

$events     = [];
$eventDates = []; // for calendar dots

while ($row = $eventsRes->fetch_assoc()) {
    $events[]                      = $row;
    $eventDates[$row['event_date']][] = $row['title'];
}

// Stats
$totalEvents    = count($events);
$todayEvents    = count(array_filter($events, fn($e) => $e['event_date'] === $today));
$upcomingCount  = count(array_filter($events, fn($e) => $e['event_date'] > $today));
$systemCount    = count(array_filter($events, fn($e) => $e['event_type'] === 'system'));

// Pass event dates to JS as JSON
$eventDatesJson = json_encode(array_keys($eventDates));

include "../includes/header.php";
include "../includes/sidebar.php";
?>
<style>
.page-container {
    margin-left: 260px;
    padding: 90px 30px 40px;
    background: #121212;
    min-height: 100vh;
    color: #fff;
}

/* ── PAGE HEADER ── */
.page-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 28px;
}
.page-title    { font-size: 26px; font-weight: 800; margin: 0 0 4px; color: #fff; }
.page-subtitle { font-size: 14px; color: rgba(255,255,255,.45); margin: 0; }

.add-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 12px 22px;
    background: linear-gradient(135deg, #ff4dc4, #ff00aa);
    color: white;
    border: none;
    border-radius: 12px;
    font-size: 14px;
    font-weight: 700;
    cursor: pointer;
    transition: transform .2s, box-shadow .2s;
    box-shadow: 0 4px 15px rgba(255,0,170,.3);
    white-space: nowrap;
}
.add-btn:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(255,0,170,.4); }

/* ── STAT CARDS ── */
.stats-row {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 14px;
    margin-bottom: 24px;
}
.stat-card {
    background: #1e1e1e;
    border-radius: 14px;
    padding: 20px 22px;
    display: flex;
    align-items: center;
    gap: 14px;
    transition: background .2s;
}
.stat-card:hover { background: #242424; }
.stat-icon-wrap {
    width: 46px; height: 46px;
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 20px;
    flex-shrink: 0;
}
.stat-num   { font-size: 26px; font-weight: 800; color: #fff; margin: 0; line-height: 1; }
.stat-lbl   { font-size: 12px; color: rgba(255,255,255,.4); margin: 4px 0 0; }

/* ── MAIN GRID ── */
.content-grid {
    display: grid;
    grid-template-columns: 1fr 320px;
    gap: 20px;
}
@media (max-width: 1100px) { .content-grid { grid-template-columns: 1fr; } }

/* ── SECTION CARD ── */
.section-card {
    background: #1e1e1e;
    border-radius: 16px;
    padding: 24px;
    margin-bottom: 20px;
}

/* ── FILTER TABS ── */
.filter-tabs {
    display: flex;
    gap: 8px;
    margin-bottom: 20px;
    flex-wrap: wrap;
}
.filter-tab {
    padding: 8px 18px;
    border-radius: 20px;
    border: 1.5px solid rgba(255,255,255,.1);
    background: transparent;
    color: rgba(255,255,255,.5);
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: all .2s;
}
.filter-tab:hover { border-color: #ff00aa; color: #fff; }
.filter-tab.active {
    background: linear-gradient(135deg, #ff4dc4, #ff00aa);
    border-color: transparent;
    color: #fff;
}

/* ── EVENT CARDS ── */
.event-card {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 16px;
    border-radius: 12px;
    background: rgba(255,255,255,.04);
    margin-bottom: 10px;
    transition: background .2s;
    cursor: pointer;
}
.event-card:hover { background: rgba(255,255,255,.08); }

.event-date-box {
    width: 52px; height: 56px;
    background: linear-gradient(135deg, #ff4dc4, #ff00aa);
    border-radius: 12px;
    display: flex; flex-direction: column;
    align-items: center; justify-content: center;
    flex-shrink: 0;
    color: white;
}
.event-date-box .day   { font-size: 20px; font-weight: 800; line-height: 1; }
.event-date-box .month { font-size: 10px; font-weight: 600; text-transform: uppercase; opacity: .9; }

.event-info    { flex: 1; min-width: 0; }
.event-name    { font-size: 15px; font-weight: 700; color: #fff; margin: 0 0 4px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.event-meta    { font-size: 12px; color: rgba(255,255,255,.4); margin: 0; }
.event-badges  { display: flex; gap: 6px; align-items: center; flex-shrink: 0; flex-wrap: wrap; }

.badge {
    font-size: 11px; padding: 4px 10px;
    border-radius: 12px; font-weight: 600;
}
.badge-system    { background: rgba(255,0,170,.2); color: #ff4dc4; }
.badge-community { background: rgba(56,189,248,.15); color: #38bdf8; }
.badge-personal  { background: rgba(255,255,255,.08); color: rgba(255,255,255,.5); }

.status-badge {
    font-size: 11px; padding: 4px 10px;
    border-radius: 12px; font-weight: 600;
}
.s-today     { background: rgba(22,163,74,.25); color: #4ade80; }
.s-upcoming  { background: rgba(217,119,6,.25);  color: #fbbf24; }
.s-ongoing   { background: rgba(37,99,235,.25);  color: #60a5fa; }
.s-completed { background: rgba(255,255,255,.08); color: rgba(255,255,255,.4); }

.action-btns { display: flex; gap: 6px; }
.action-btn {
    padding: 6px 12px;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 600;
    border: none;
    cursor: pointer;
    text-decoration: none;
    transition: all .2s;
    display: inline-block;
}
.btn-edit   { background: rgba(56,189,248,.15); color: #38bdf8; }
.btn-edit:hover   { background: #38bdf8; color: #000; }
.btn-delete { background: rgba(239,68,68,.15); color: #f87171; }
.btn-delete:hover { background: #ef4444; color: #fff; }

/* ── EMPTY STATE ── */
.empty-state { text-align: center; padding: 50px 20px; color: rgba(255,255,255,.3); }
.empty-state .icon { font-size: 52px; margin-bottom: 12px; }

/* ── CALENDAR ── */
.calendar-widget {
    background: linear-gradient(135deg, #1a0a2e, #16213e);
    border-radius: 16px;
    padding: 22px;
    margin-bottom: 20px;
}
.cal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 18px;
}
.cal-month { font-size: 17px; font-weight: 700; color: #fff; }
.cal-nav-btn {
    background: rgba(255,255,255,.1);
    border: none; color: white;
    width: 30px; height: 30px;
    border-radius: 8px;
    cursor: pointer; font-size: 15px;
    transition: background .2s;
}
.cal-nav-btn:hover { background: rgba(255,0,170,.4); }
.cal-grid {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 3px;
    text-align: center;
}
.cal-label { font-size: 10px; color: rgba(255,255,255,.35); padding: 6px 0; font-weight: 700; letter-spacing: .5px; }
.cal-day {
    aspect-ratio: 1;
    display: flex; flex-direction: column;
    align-items: center; justify-content: center;
    font-size: 12px;
    border-radius: 6px;
    cursor: pointer;
    transition: background .15s;
    position: relative;
    color: rgba(255,255,255,.75);
}
.cal-day:hover   { background: rgba(255,255,255,.1); }
.cal-day.today   { background: linear-gradient(135deg, #ff4dc4, #ff00aa); color: white; font-weight: 700; }
.cal-day.other   { color: rgba(255,255,255,.2); }
.cal-day.has-event::after {
    content: '';
    width: 4px; height: 4px;
    background: #ff4dc4;
    border-radius: 50%;
    position: absolute;
    bottom: 3px;
}
.cal-day.today.has-event::after { background: white; }

/* ── CREATE MODAL ── */
.modal-overlay {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,.7);
    z-index: 1000;
    align-items: center;
    justify-content: center;
    backdrop-filter: blur(4px);
}
.modal-overlay.open { display: flex; }
.modal {
    background: #1e1e1e;
    border-radius: 20px;
    padding: 32px;
    width: 500px;
    max-width: 95vw;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 20px 60px rgba(0,0,0,.6);
    border: 1px solid rgba(255,255,255,.08);
    animation: slideUp .25s ease;
}
@keyframes slideUp { from { transform: translateY(30px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }

.modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; }
.modal-title  { font-size: 20px; font-weight: 800; color: #fff; margin: 0; }
.modal-close  {
    background: rgba(255,255,255,.08); border: none;
    color: rgba(255,255,255,.6); width: 32px; height: 32px;
    border-radius: 8px; cursor: pointer; font-size: 18px;
    transition: all .2s;
}
.modal-close:hover { background: #ef4444; color: white; }

.form-group { margin-bottom: 16px; }
.form-group label { display: block; font-size: 13px; font-weight: 600; color: rgba(255,255,255,.6); margin-bottom: 7px; }
.form-group input,
.form-group textarea,
.form-group select {
    width: 100%;
    padding: 12px 14px;
    background: rgba(255,255,255,.07);
    border: 1.5px solid rgba(255,255,255,.1);
    border-radius: 10px;
    color: #fff;
    font-size: 14px;
    outline: none;
    transition: border-color .2s;
}
.form-group input::placeholder,
.form-group textarea::placeholder { color: rgba(255,255,255,.25); }
.form-group input:focus,
.form-group textarea:focus,
.form-group select:focus { border-color: #ff00aa; background: rgba(255,255,255,.1); }
.form-group select option { background: #282828; color: #fff; }
.form-group textarea { min-height: 80px; resize: vertical; }
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }

.submit-btn {
    width: 100%; padding: 14px;
    background: linear-gradient(135deg, #ff4dc4, #ff00aa);
    color: white; border: none;
    border-radius: 12px; font-size: 15px; font-weight: 700;
    cursor: pointer; transition: all .2s;
    box-shadow: 0 4px 15px rgba(255,0,170,.3);
    margin-top: 6px;
}
.submit-btn:hover { opacity: .92; transform: translateY(-1px); }

/* ── TOAST ── */
.toast {
    position: fixed; bottom: 28px; right: 28px;
    background: #1e1e1e; border: 1px solid rgba(255,255,255,.1);
    color: #fff; padding: 14px 20px;
    border-radius: 12px; font-size: 14px; font-weight: 600;
    box-shadow: 0 8px 30px rgba(0,0,0,.4);
    z-index: 9999;
    display: none;
    animation: fadeIn .3s ease;
}
@keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
</style>

<!-- ── CREATE EVENT MODAL ── -->
<?php if ($role !== 'viewer'): ?>
<div class="modal-overlay" id="createModal">
    <div class="modal">
        <div class="modal-header">
            <h3 class="modal-title">➕ Create New Event</h3>
            <button class="modal-close" onclick="closeModal()">×</button>
        </div>
        <form method="POST" action="../actions/add_event_action.php">
            <?= csrf_field() ?>
            <div class="form-group">
                <label>Event Title *</label>
                <input type="text" name="title" placeholder="e.g. Ramadan Iftar Program" required>
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" placeholder="Brief description of the event..."></textarea>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Date *</label>
                    <input type="date" name="event_date" required min="<?= $today ?>">
                </div>
                <div class="form-group">
                    <label>Time *</label>
                    <input type="time" name="event_time" required>
                </div>
            </div>
            <div class="form-group">
                <label>Venue *</label>
                <input type="text" name="venue" placeholder="e.g. Lagao Mosque" required>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Visibility</label>
                    <select name="visibility">
                        <option value="personal">👤 Personal</option>
                        <?php if ($role !== 'viewer'): ?>
                        <option value="community">👥 Community</option>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select name="status">
                        <option value="upcoming">Upcoming</option>
                        <option value="ongoing">Ongoing</option>
                        <option value="completed">Completed</option>
                    </select>
                </div>
            </div>
            <button type="submit" class="submit-btn">Create Event</button>
        </form>
    </div>
</div>
<?php endif; ?>

<!-- ── TOAST ── -->
<div class="toast" id="toast"></div>

<div class="page-container">

    <!-- Page Header -->
    <div class="page-header">
        <div>
            <h1 class="page-title">📅 Events & Programs</h1>
            <p class="page-subtitle">Manage and track your community events</p>
        </div>
        <?php if ($role !== 'viewer'): ?>
        <button class="add-btn" onclick="openModal()">➕ Create Event</button>
        <?php endif; ?>
    </div>

    <!-- Stats -->
    <div class="stats-row">
        <div class="stat-card">
            <div class="stat-icon-wrap" style="background:rgba(255,77,196,.15);">📅</div>
            <div>
                <p class="stat-num"><?= $totalEvents ?></p>
                <p class="stat-lbl">Total Events</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon-wrap" style="background:rgba(74,222,128,.15);">🗓️</div>
            <div>
                <p class="stat-num" style="color:#4ade80;"><?= $todayEvents ?></p>
                <p class="stat-lbl">Today</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon-wrap" style="background:rgba(251,191,36,.15);">⏳</div>
            <div>
                <p class="stat-num" style="color:#fbbf24;"><?= $upcomingCount ?></p>
                <p class="stat-lbl">Upcoming</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon-wrap" style="background:rgba(96,165,250,.15);">🕌</div>
            <div>
                <p class="stat-num" style="color:#60a5fa;"><?= $systemCount ?></p>
                <p class="stat-lbl">System Events</p>
            </div>
        </div>
    </div>

    <div class="content-grid">

        <!-- Left: Event List -->
        <div>
            <div class="section-card">
                <!-- Filter Tabs -->
                <div class="filter-tabs">
                    <button class="filter-tab active" onclick="filterEvents('all', this)">All</button>
                    <button class="filter-tab" onclick="filterEvents('today', this)">Today</button>
                    <button class="filter-tab" onclick="filterEvents('upcoming', this)">Upcoming</button>
                    <button class="filter-tab" onclick="filterEvents('system', this)">System</button>
                    <button class="filter-tab" onclick="filterEvents('community', this)">Community</button>
                </div>

                <!-- Events -->
                <div id="eventsList">
                <?php if (!empty($events)): ?>
                    <?php foreach ($events as $row):
                        $eDate     = strtotime($row['event_date']);
                        $dbStatus  = $row['status'] ?? 'upcoming';
                        $isToday   = $row['event_date'] === $today;
                        $isPast    = $row['event_date'] < $today;

                        if ($dbStatus === 'ongoing') {
                            $sc = 's-ongoing';   $st = 'Ongoing';
                        } elseif ($dbStatus === 'completed' || ($isPast && $dbStatus !== 'ongoing')) {
                            $sc = 's-completed'; $st = 'Completed';
                        } elseif ($isToday) {
                            $sc = 's-today';     $st = 'Today';
                        } else {
                            $sc = 's-upcoming';  $st = 'Upcoming';
                        }

                        $isSystem = $row['event_type'] === 'system';
                        $isCommunity = $row['visibility'] === 'community';

                        if ($isSystem) {
                            $bc = 'badge-system'; $bt = 'System'; $icon = '🕌';
                        } elseif ($isCommunity) {
                            $bc = 'badge-community'; $bt = 'Community'; $icon = '👥';
                        } else {
                            $bc = 'badge-personal'; $bt = 'Personal'; $icon = '👤';
                        }

                        $dataType = $isSystem ? 'system' : ($isCommunity ? 'community' : 'personal');
                    ?>
                    <div class="event-card"
                         data-date="<?= $row['event_date'] ?>"
                         data-type="<?= $dataType ?>"
                         data-status="<?= $isToday ? 'today' : ($isPast ? 'completed' : 'upcoming') ?>">

                        <div class="event-date-box">
                            <span class="day"><?= date('d', $eDate) ?></span>
                            <span class="month"><?= date('M', $eDate) ?></span>
                        </div>

                        <div class="event-info">
                            <p class="event-name"><?= htmlspecialchars($row['title']) ?></p>
                            <p class="event-meta">
                                <?= date('l', $eDate) ?> · <?= date('g:i A', strtotime($row['event_time'])) ?> · <?= htmlspecialchars($row['venue']) ?>
                            </p>
                        </div>

                        <div class="event-badges">
                            <span class="badge <?= $bc ?>"><?= $bt ?></span>
                            <span class="status-badge <?= $sc ?>"><?= $st ?></span>
                            <?php if ($role !== 'viewer' && ($row['user_id'] == $user_id || $role === 'admin')): ?>
                            <div class="action-btns">
                                <a href="edit_event.php?id=<?= $row['id'] ?>" class="action-btn btn-edit">Edit</a>
                                <button type="button" class="action-btn btn-delete"
                                   onclick="confirmDelete(<?= $row['id'] ?>, '<?= htmlspecialchars($row['title'], ENT_QUOTES) ?>')">Delete</button>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <div class="icon">📭</div>
                        <p style="font-size:16px;font-weight:600;color:rgba(255,255,255,.5);">No events found</p>
                        <p style="font-size:13px;">Create your first event to get started.</p>
                    </div>
                <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Right: Calendar + Legend -->
        <div>
            <!-- Calendar -->
            <div class="calendar-widget">
                <div class="cal-header">
                    <button class="cal-nav-btn" onclick="changeMonth(-1)">‹</button>
                    <h3 class="cal-month" id="calMonthLabel"></h3>
                    <button class="cal-nav-btn" onclick="changeMonth(1)">›</button>
                </div>
                <div class="cal-grid" id="calGrid">
                    <div class="cal-label">Su</div>
                    <div class="cal-label">Mo</div>
                    <div class="cal-label">Tu</div>
                    <div class="cal-label">We</div>
                    <div class="cal-label">Th</div>
                    <div class="cal-label">Fr</div>
                    <div class="cal-label">Sa</div>
                </div>
            </div>

            <!-- Legend -->
            <div class="section-card">
                <h3 style="font-size:15px;font-weight:700;color:#fff;margin:0 0 16px;">Legend</h3>
                <div style="display:flex;flex-direction:column;gap:10px;">
                    <div style="display:flex;align-items:center;gap:10px;font-size:13px;color:rgba(255,255,255,.6);">
                        <span class="badge badge-system">System</span> Auto-generated daily prayer events
                    </div>
                    <div style="display:flex;align-items:center;gap:10px;font-size:13px;color:rgba(255,255,255,.6);">
                        <span class="badge badge-community">Community</span> Visible to all community members
                    </div>
                    <div style="display:flex;align-items:center;gap:10px;font-size:13px;color:rgba(255,255,255,.6);">
                        <span class="badge badge-personal">Personal</span> Created by you only
                    </div>
                    <hr style="border-color:rgba(255,255,255,.07);margin:4px 0;">
                    <div style="display:flex;align-items:center;gap:10px;font-size:13px;color:rgba(255,255,255,.6);">
                        <span class="status-badge s-today">Today</span> Happening today
                    </div>
                    <div style="display:flex;align-items:center;gap:10px;font-size:13px;color:rgba(255,255,255,.6);">
                        <span class="status-badge s-upcoming">Upcoming</span> Scheduled in the future
                    </div>
                    <div style="display:flex;align-items:center;gap:10px;font-size:13px;color:rgba(255,255,255,.6);">
                        <span class="status-badge s-ongoing">Ongoing</span> Currently running
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
// ── Event dates from PHP ──────────────────────────────
var eventDates = <?= $eventDatesJson ?>;
var todayStr   = '<?= $today ?>';

// ── Calendar ─────────────────────────────────────────
var calYear, calMonth;
(function() {
    var now = new Date();
    calYear  = now.getFullYear();
    calMonth = now.getMonth(); // 0-based
})();

var monthNames = ['January','February','March','April','May','June',
                  'July','August','September','October','November','December'];

function buildCalendar() {
    var label = document.getElementById('calMonthLabel');
    label.textContent = monthNames[calMonth] + ' ' + calYear;

    var grid  = document.getElementById('calGrid');
    // Remove old day cells (keep the 7 label headers)
    var cells = grid.querySelectorAll('.cal-day');
    cells.forEach(function(c) { c.remove(); });

    var firstDay    = new Date(calYear, calMonth, 1).getDay(); // 0=Sun
    var daysInMonth = new Date(calYear, calMonth + 1, 0).getDate();
    var prevDays    = new Date(calYear, calMonth, 0).getDate();

    // Prev month fillers
    for (var i = firstDay - 1; i >= 0; i--) {
        var d = document.createElement('div');
        d.className = 'cal-day other';
        d.textContent = prevDays - i;
        grid.appendChild(d);
    }

    // Current month
    for (var day = 1; day <= daysInMonth; day++) {
        var mm    = String(calMonth + 1).padStart(2, '0');
        var dd    = String(day).padStart(2, '0');
        var dateS = calYear + '-' + mm + '-' + dd;

        var d = document.createElement('div');
        d.className = 'cal-day';
        d.textContent = day;

        if (dateS === todayStr)              d.classList.add('today');
        if (eventDates.indexOf(dateS) >= 0) d.classList.add('has-event');

        (function(ds) {
            d.addEventListener('click', function() { filterByDate(ds); });
        })(dateS);

        grid.appendChild(d);
    }

    // Next month fillers
    var total  = firstDay + daysInMonth;
    var remain = (7 - (total % 7)) % 7;
    for (var j = 1; j <= remain; j++) {
        var d = document.createElement('div');
        d.className = 'cal-day other';
        d.textContent = j;
        grid.appendChild(d);
    }
}

function changeMonth(dir) {
    calMonth += dir;
    if (calMonth > 11) { calMonth = 0;  calYear++; }
    if (calMonth < 0)  { calMonth = 11; calYear--; }
    buildCalendar();
}

// ── Filter ───────────────────────────────────────────
function filterEvents(type, btn) {
    document.querySelectorAll('.filter-tab').forEach(function(t) { t.classList.remove('active'); });
    btn.classList.add('active');

    document.querySelectorAll('.event-card').forEach(function(card) {
        var show = false;
        if (type === 'all')       show = true;
        if (type === 'today')     show = card.dataset.status === 'today';
        if (type === 'upcoming')  show = card.dataset.status === 'upcoming';
        if (type === 'system')    show = card.dataset.type   === 'system';
        if (type === 'community') show = card.dataset.type   === 'community';
        card.style.display = show ? '' : 'none';
    });

    checkEmpty();
}

function filterByDate(dateStr) {
    // Reset tab highlights
    document.querySelectorAll('.filter-tab').forEach(function(t) { t.classList.remove('active'); });

    document.querySelectorAll('.event-card').forEach(function(card) {
        card.style.display = card.dataset.date === dateStr ? '' : 'none';
    });

    checkEmpty();
    showToast('Showing events for ' + dateStr);
}

function checkEmpty() {
    var visible = document.querySelectorAll('.event-card:not([style*="none"])').length;
    var existing = document.getElementById('noResultsMsg');
    if (existing) existing.remove();
    if (visible === 0) {
        var msg = document.createElement('div');
        msg.id = 'noResultsMsg';
        msg.className = 'empty-state';
        msg.innerHTML = '<div class="icon">🔍</div><p style="color:rgba(255,255,255,.4);font-size:14px;">No events match this filter.</p>';
        document.getElementById('eventsList').appendChild(msg);
    }
}

// ── Modal ────────────────────────────────────────────
function openModal() {
    document.getElementById('createModal').classList.add('open');
    document.body.style.overflow = 'hidden';
}
function closeModal() {
    document.getElementById('createModal').classList.remove('open');
    document.body.style.overflow = '';
}
document.getElementById('createModal')?.addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});

// ── Delete confirm (POST with CSRF) ──────────────────
function confirmDelete(id, title) {
    if (!confirm('Delete "' + title + '"? This cannot be undone.')) return;
    var form = document.createElement('form');
    form.method = 'POST';
    form.action = '../actions/delete_event.php';
    var idField = document.createElement('input');
    idField.type = 'hidden'; idField.name = 'id'; idField.value = id;
    var csrfField = document.createElement('input');
    csrfField.type = 'hidden'; csrfField.name = 'csrf_token';
    csrfField.value = document.querySelector('input[name="csrf_token"]').value;
    form.appendChild(idField); form.appendChild(csrfField);
    document.body.appendChild(form); form.submit();
}

// ── Toast ────────────────────────────────────────────
function showToast(msg) {
    var t = document.getElementById('toast');
    t.textContent = msg;
    t.style.display = 'block';
    clearTimeout(window._toastTimer);
    window._toastTimer = setTimeout(function() { t.style.display = 'none'; }, 2500);
}

// ── Init ─────────────────────────────────────────────
buildCalendar();

<?php if (isset($_GET['msg'])): ?>
showToast('<?= htmlspecialchars($_GET['msg']) ?>');
<?php endif; ?>
</script>

<?php include "../includes/footer.php"; ?>
