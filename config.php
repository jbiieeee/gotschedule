<?php
// DEBUG MODE: ENABLED for deployment troubleshooting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Environment Detection & Database configuration
if ($_SERVER['HTTP_HOST'] == 'localhost' || $_SERVER['REMOTE_ADDR'] == '127.0.0.1' || $_SERVER['REMOTE_ADDR'] == '::1') {
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
try {
    mysqli_report(MYSQLI_REPORT_OFF); // Disable fatal exceptions to use our custom error box
    $conn = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
    
    if (!$conn) {
        throw new Exception(mysqli_connect_error());
    }
} catch (Exception $e) {
    die("<div style='font-family:sans-serif; padding:50px; text-align:center;'>
            <div style='display:inline-block; background:white; padding:40px; border-radius:20px; box-shadow:0 20px 50px rgba(0,0,0,0.1); border-top:5px solid #ef4444;'>
                <h1 style='color:#ef4444; margin-top:0;'>🔒 Database Access Denied</h1>
                <p style='color:#64748b;'>The server rejected our connection. This usually means the <b>Password</b> or <b>Hostname</b> is incorrect.</p>
                <div style='background:#f1f5f9; padding:15px; border-radius:10px; margin:20px 0; text-align:left;'>
                    <code style='color:#ef4444;'>Error Detail: " . $e->getMessage() . "</code>
                </div>
                <p style='font-size:0.9rem; color:#94a3b8;'>Check your config.php and verify the credentials in your InfinityFree dashboard.</p>
            </div>
         </div>");
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
