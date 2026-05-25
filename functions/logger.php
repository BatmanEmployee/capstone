<?php
function logActivity($conn, $user_id, $action, $details = null) {
    $stmt = $conn->prepare(
        "INSERT INTO audit_logs (user_id, action, details) VALUES (?, ?, ?)"
    );
    $stmt->bind_param("iss", $user_id, $action, $details);
    $stmt->execute();
    $stmt->close();
}
