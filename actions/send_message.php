<?php
session_start();
include "../config/database.php";
include "../functions/csrf.php";

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['ok' => false, 'error' => 'Not authenticated']);
    exit();
}

if (!csrf_verify()) {
    echo json_encode(['ok' => false, 'error' => 'Invalid CSRF token']);
    exit();
}

$user_id      = (int) $_SESSION['user_id'];
$role         = $_SESSION['role'];
$community_id = (int) ($_SESSION['community_id'] ?? 0);

$type      = $_POST['type'] ?? '';
$target_id = (int) ($_POST['target_id'] ?? 0);
$message   = trim($_POST['message'] ?? '');

if ($message === '') {
    echo json_encode(['ok' => false, 'error' => 'Message cannot be empty']);
    exit();
}
if (strlen($message) > 2000) {
    echo json_encode(['ok' => false, 'error' => 'Message too long (max 2000 chars)']);
    exit();
}
if ($target_id <= 0) {
    echo json_encode(['ok' => false, 'error' => 'Invalid target']);
    exit();
}

// ─── DM ─────────────────────────────────────────────────────
if ($type === 'dm') {
    // Verify the recipient exists and the sender is allowed to message them
    $stmt = $conn->prepare("SELECT id, role, community_id FROM users WHERE id = ?");
    $stmt->bind_param("i", $target_id);
    $stmt->execute();
    $recipient = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$recipient) {
        echo json_encode(['ok' => false, 'error' => 'Recipient not found']);
        exit();
    }

    if (!canMessage($role, $community_id, $recipient['role'], (int)$recipient['community_id'])) {
        echo json_encode(['ok' => false, 'error' => 'You do not have permission to message this user']);
        exit();
    }

    $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $user_id, $target_id, $message);
    $stmt->execute();
    $stmt->close();

    echo json_encode(['ok' => true]);
    exit();
}

// ─── Group ───────────────────────────────────────────────────
if ($type === 'group') {
    // Verify the user is a member of this group
    $stmt = $conn->prepare("SELECT 1 FROM chat_group_members WHERE group_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $target_id, $user_id);
    $stmt->execute();
    $member = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$member) {
        echo json_encode(['ok' => false, 'error' => 'You are not a member of this group']);
        exit();
    }

    $stmt = $conn->prepare("INSERT INTO chat_group_messages (group_id, sender_id, message) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $target_id, $user_id, $message);
    $stmt->execute();
    $stmt->close();

    echo json_encode(['ok' => true]);
    exit();
}

echo json_encode(['ok' => false, 'error' => 'Unknown message type']);

// ─── Role-based access control ───────────────────────────────
function canMessage($senderRole, $senderCid, $recipRole, $recipCid) {
    // admin can message anyone
    if ($senderRole === 'admin') return true;
    // imam → same community + all admins/imams/leaders
    if ($senderRole === 'imam') {
        return $senderCid === $recipCid || in_array($recipRole, ['admin','imam','leader']);
    }
    // leader → same community + all admins/imams
    if ($senderRole === 'leader') {
        return $senderCid === $recipCid || in_array($recipRole, ['admin','imam']);
    }
    // viewer → only admins/imams/leaders
    return in_array($recipRole, ['admin','imam','leader']);
}
