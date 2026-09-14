<?php
// database.php
// File establish secure database connection using PDO or PHP Data Objects
// Use of prepared statements to prevent SQL injection attacks

//DB Configuration

$host = "localhost"; // Hostname of the database server
$user ="root"; // The username for the Database Server
$pass = ""; // Password for the Database Server Real enviroment should be secured? What does it mean?
$db = "rminderdb"; //Database Name

// create new PDO Instance to connect database
try {
    $conn = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4",$user,$pass);
    // set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Use prepared statments globally to prevent SQL injection attacks
    $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

}catch(PDOException $e) {
    // secure error handling to prevent leaking sensitive information
    die("Connection failed: ". $e->getMessage());

}

// Session timeout - 1 hour of inactivity logs the user out
if (session_status() === PHP_SESSION_ACTIVE && isset($_SESSION['user_id'])) {
    $timeout = 3600; 
    if (isset($_SESSION['last_activity']) && (time() -$_SESSION['last_activity']) > $timeout){
        
    
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

require_once __DIR__ .'/../functions/csrf.php';
?>

