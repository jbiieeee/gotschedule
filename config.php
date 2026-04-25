<?php
// Database configuration
// Environment Detection & Database configuration
if ($_SERVER['REMOTE_ADDR'] == '127.0.0.1' || $_SERVER['REMOTE_ADDR'] == '::1') {
    // LOCALHOST (XAMPP)
    define('DB_SERVER', 'localhost');
    define('DB_USERNAME', 'root');
    define('DB_PASSWORD', '');
    define('DB_NAME', 'sign_up');
} else {
    // LIVE SERVER (InfinityFree)
    define('DB_SERVER', 'sql312.infinityfree.com');
    define('DB_USERNAME', 'if0_41749779');
    define('DB_PASSWORD', 'N1T4Ujr9Ahh');
    define('DB_NAME', 'if0_41749779_gotschedule');
}

// Attempt to connect to MySQL database
$conn = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Log a user action to the audit_logs table
 */
function logActivity($conn, $user_id, $action, $details = null) {
    $stmt = $conn->prepare("INSERT INTO audit_logs (user_id, action, details) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $user_id, $action, $details);
    $stmt->execute();
    $stmt->close();
}

/**
 * Strict authentication check
 */
function checkAuth() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: main.php");
        exit();
    }
}
?>
