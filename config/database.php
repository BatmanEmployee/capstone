<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "rminderdb";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

require_once __DIR__ . '/../functions/csrf.php';

// Session timeout — 1 hour of inactivity logs the user out
if (session_status() === PHP_SESSION_ACTIVE && isset($_SESSION['user_id'])) {
    $timeout = 3600;
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout) {
        session_unset();
        session_destroy();
        $redirect = strpos($_SERVER['PHP_SELF'], 'pages/') !== false
            ? 'login.php?timeout=1'
            : 'pages/login.php?timeout=1';
        header("Location: $redirect");
        exit();
    }
    $_SESSION['last_activity'] = time();
}
?>