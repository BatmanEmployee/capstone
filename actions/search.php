<?php
session_start();
include "../config/database.php";

if (!isset($_SESSION['user_id'])) {
    echo json_encode([]);
    exit();
}

$q = trim($_GET['q'] ?? '');
if (strlen($q) < 2) {
    echo json_encode([]);
    exit();
}

$role = $_SESSION['role'];
$community_id = (int) ($_SESSION['community_id'] ?? 0);
$like = '%' . $q . '%';

$results = [];

// ── Events ──────────────────────────────────────────
if ($role === 'admin') {
    $stmt = $conn->prepare("SELECT id, title, event_date, status FROM events
            WHERE title LIKE ? OR venue LIKE ?
            ORDER BY event_date DESC LIMIT 5");
    $stmt->bind_param("ss", $like, $like);
} else {
    $stmt = $conn->prepare("SELECT id, title, event_date, status FROM events
            WHERE (title LIKE ? OR venue LIKE ?)
            AND (community_id = ? OR community_id IS NULL OR event_type = 'system')
            ORDER BY event_date DESC LIMIT 5");
    $stmt->bind_param("ssi", $like, $like, $community_id);
}
$stmt->execute();
$r = $stmt->get_result();
while ($row = $r->fetch_assoc()) {
    $results[] = [
        'type'  => 'event',
        'id'    => $row['id'],
        'title' => $row['title'],
        'meta'  => $row['event_date'] . ' · ' . ucfirst($row['status']),
        'url'   => 'events.php?id=' . $row['id'],
    ];
}
$stmt->close();

// ── Announcements ────────────────────────────────────
if ($role === 'admin') {
    $stmt = $conn->prepare("SELECT id, title, category, created_at FROM announcements
            WHERE title LIKE ? OR message LIKE ?
            ORDER BY created_at DESC LIMIT 5");
    $stmt->bind_param("ss", $like, $like);
} else {
    $stmt = $conn->prepare("SELECT id, title, category, created_at FROM announcements
            WHERE (title LIKE ? OR message LIKE ?)
            AND community_id = ?
            ORDER BY created_at DESC LIMIT 5");
    $stmt->bind_param("ssi", $like, $like, $community_id);
}
$stmt->execute();
$r = $stmt->get_result();
while ($row = $r->fetch_assoc()) {
    $cat = str_replace('_', ' ', ucwords($row['category'] ?? 'General', '_'));
    $results[] = [
        'type'  => 'announcement',
        'id'    => $row['id'],
        'title' => $row['title'],
        'meta'  => $cat . ' · ' . date('M j, Y', strtotime($row['created_at'])),
        'url'   => 'announcements.php',
    ];
}
$stmt->close();

// ── Forum Threads ────────────────────────────────────
if ($role !== 'viewer') {
    if ($role === 'admin') {
        $stmt = $conn->prepare("SELECT id, title, created_at FROM forum_threads
                WHERE title LIKE ? OR body LIKE ?
                ORDER BY created_at DESC LIMIT 5");
        $stmt->bind_param("ss", $like, $like);
    } else {
        $stmt = $conn->prepare("SELECT id, title, created_at FROM forum_threads
                WHERE (title LIKE ? OR body LIKE ?)
                AND (community_id = ? OR community_id IS NULL)
                ORDER BY created_at DESC LIMIT 5");
        $stmt->bind_param("ssi", $like, $like, $community_id);
    }
    $stmt->execute();
    $r = $stmt->get_result();
    while ($row = $r->fetch_assoc()) {
        $results[] = [
            'type'  => 'forum',
            'id'    => $row['id'],
            'title' => $row['title'],
            'meta'  => 'Forum · ' . date('M j, Y', strtotime($row['created_at'])),
            'url'   => 'forum_thread.php?id=' . $row['id'],
        ];
    }
    $stmt->close();
}

// ── Donations (admin/imam/leader only) ────────────────────────────────────
if (in_array($role, ['admin', 'imam', 'leader'])) {
    if ($role === 'admin') {
        $stmt = $conn->prepare("SELECT id, donor_name, donation_type, created_at FROM donations
                WHERE donor_name LIKE ? OR remarks LIKE ?
                ORDER BY created_at DESC LIMIT 5");
        $stmt->bind_param("ss", $like, $like);
    } else {
        $stmt = $conn->prepare("SELECT id, donor_name, donation_type, created_at FROM donations
                WHERE (donor_name LIKE ? OR remarks LIKE ?)
                AND community_id = ?
                ORDER BY created_at DESC LIMIT 5");
        $stmt->bind_param("ssi", $like, $like, $community_id);
    }
    $stmt->execute();
    $r = $stmt->get_result();
    while ($row = $r->fetch_assoc()) {
        $donor = $row['donor_name'] ?? 'Anonymous';
        $results[] = [
            'type'  => 'donation',
            'id'    => $row['id'],
            'title' => $donor . ' · ' . ucfirst(str_replace('_', ' ', $row['donation_type'])),
            'meta'  => 'Donation · ' . date('M j, Y', strtotime($row['created_at'])),
            'url'   => 'donations.php',
        ];
    }
    $stmt->close();
}

// ── Appointments (admin only) ────────────────────────────────────
if ($role === 'admin') {
    $stmt = $conn->prepare("SELECT id, reference_no, full_name, service_type, created_at FROM appointments
            WHERE full_name LIKE ? OR reference_no LIKE ?
            ORDER BY created_at DESC LIMIT 5");
    $stmt->bind_param("ss", $like, $like);
    $stmt->execute();
    $r = $stmt->get_result();
    while ($row = $r->fetch_assoc()) {
        $service = ucfirst(str_replace('_', ' ', $row['service_type']));
        $results[] = [
            'type'  => 'appointment',
            'id'    => $row['id'],
            'title' => $row['full_name'] . ' · ' . $row['reference_no'],
            'meta'  => $service . ' · ' . date('M j, Y', strtotime($row['created_at'])),
            'url'   => 'appointments.php',
        ];
    }
    $stmt->close();
}

// ── Quick Page Links ──────────────────────────────────────
$q_lower = strtolower($q);
$pages = [
    ['title' => 'Dashboard', 'type' => 'page', 'icon' => 'dashboard', 'url' => 'dashboard.php'],
    ['title' => 'Events', 'type' => 'page', 'icon' => 'calendar', 'url' => 'events.php'],
    ['title' => 'Forum', 'type' => 'page', 'icon' => 'forum', 'url' => 'forum.php'],
    ['title' => 'Donations', 'type' => 'page', 'icon' => 'donations', 'url' => 'donations.php'],
    ['title' => 'Announcements', 'type' => 'page', 'icon' => 'announce', 'url' => 'announcements.php'],
];
if ($role === 'admin') {
    $pages[] = ['title' => 'Users', 'type' => 'page', 'icon' => 'users', 'url' => 'users.php'];
    $pages[] = ['title' => 'Appointments', 'type' => 'page', 'icon' => 'appt', 'url' => 'appointments.php'];
}

foreach ($pages as $page) {
    if (strpos(strtolower($page['title']), $q_lower) === 0) {
        $results[] = [
            'type'  => 'page',
            'id'    => 0,
            'title' => $page['title'],
            'meta'  => 'Navigate to page',
            'url'   => $page['url'],
            'icon_name' => $page['icon'],
        ];
    }
}

header('Content-Type: application/json');
echo json_encode($results);
