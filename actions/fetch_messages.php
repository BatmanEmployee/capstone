<?php
session_start();
include "../config/database.php";

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['ok' => false, 'error' => 'Not authenticated']);
    exit();
}

$user_id   = (int) $_SESSION['user_id'];
$type      = $_GET['type'] ?? '';
$target_id = (int) ($_GET['id'] ?? 0);

if ($target_id <= 0) {
    echo json_encode(['ok' => false, 'error' => 'Invalid target']);
    exit();
}

// ─── DM conversation ────────────────────────────────────────
if ($type === 'dm') {
    $stmt = $conn->prepare("
        SELECT m.id, m.sender_id, m.receiver_id, m.message, m.created_at, u.name AS sender_name
        FROM messages m
        LEFT JOIN users u ON u.id = m.sender_id
        WHERE (m.sender_id = ? AND m.receiver_id = ?)
           OR (m.sender_id = ? AND m.receiver_id = ?)
        ORDER BY m.created_at ASC
        LIMIT 200
    ");
    $stmt->bind_param("iiii", $user_id, $target_id, $target_id, $user_id);
    $stmt->execute();
    $r = $stmt->get_result();
    $messages = [];
    while ($row = $r->fetch_assoc()) {
        $messages[] = $row;
    }
    $stmt->close();

    // Mark received messages as read
    $stmt = $conn->prepare("UPDATE messages SET is_read = 1 WHERE sender_id = ? AND receiver_id = ? AND is_read = 0");
    $stmt->bind_param("ii", $target_id, $user_id);
    $stmt->execute();
    $stmt->close();

    echo json_encode(['ok' => true, 'messages' => $messages]);
    exit();
}

// ─── Group conversation ─────────────────────────────────────
if ($type === 'group') {
    // Verify membership
    $stmt = $conn->prepare("SELECT 1 FROM chat_group_members WHERE group_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $target_id, $user_id);
    $stmt->execute();
    $member = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$member) {
        echo json_encode(['ok' => false, 'error' => 'Not a member of this group']);
        exit();
    }

    $stmt = $conn->prepare("
        SELECT cgm.id, cgm.sender_id, cgm.message, cgm.created_at, u.name AS sender_name
        FROM chat_group_messages cgm
        LEFT JOIN users u ON u.id = cgm.sender_id
        WHERE cgm.group_id = ?
        ORDER BY cgm.created_at ASC
        LIMIT 200
    ");
    $stmt->bind_param("i", $target_id);
    $stmt->execute();
    $r = $stmt->get_result();
    $messages = [];
    while ($row = $r->fetch_assoc()) {
        $messages[] = $row;
    }
    $stmt->close();

    echo json_encode(['ok' => true, 'messages' => $messages]);
    exit();
}

echo json_encode(['ok' => false, 'error' => 'Unknown type']);
