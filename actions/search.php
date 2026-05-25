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
$like = '%' . $conn->real_escape_string($q) . '%';

$results = [];

// ── Events ──────────────────────────────────────────
if ($role === 'admin') {
    $sql = "SELECT id, title, event_date, status FROM events
            WHERE title LIKE '$like' OR venue LIKE '$like'
            ORDER BY event_date DESC LIMIT 5";
} else {
    $sql = "SELECT id, title, event_date, status FROM events
            WHERE (title LIKE '$like' OR venue LIKE '$like')
            AND (community_id = $community_id OR community_id IS NULL OR event_type = 'system')
            ORDER BY event_date DESC LIMIT 5";
}
$r = $conn->query($sql);
while ($row = $r->fetch_assoc()) {
    $results[] = [
        'type'  => 'event',
        'id'    => $row['id'],
        'title' => $row['title'],
        'meta'  => $row['event_date'] . ' · ' . ucfirst($row['status']),
        'url'   => 'events.php',
    ];
}

// ── Announcements ────────────────────────────────────
if ($role === 'admin') {
    $sql = "SELECT id, title, category, created_at FROM announcements
            WHERE title LIKE '$like' OR message LIKE '$like'
            ORDER BY created_at DESC LIMIT 5";
} else {
    $sql = "SELECT id, title, category, created_at FROM announcements
            WHERE (title LIKE '$like' OR message LIKE '$like')
            AND (community_id = $community_id OR community_id = 0)
            ORDER BY created_at DESC LIMIT 5";
}
$r = $conn->query($sql);
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

header('Content-Type: application/json');
echo json_encode($results);
