<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) exit;

$user_id = $_SESSION['user_id'];
$content = mysqli_real_escape_string($conn, $_POST['content'] ?? '');

if (isset($_POST['save'])) {
    mysqli_query($conn, "UPDATE users SET brain_dump = '$content', brain_dump_updated_at = NOW() WHERE id = $user_id");
    echo "Saved";
} elseif (isset($_GET['fetch'])) {
    $res = mysqli_query($conn, "SELECT brain_dump, brain_dump_updated_at FROM users WHERE id = $user_id");
    $user = mysqli_fetch_assoc($res);
    
    // Auto-clear if > 24 hours
    $updated = strtotime($user['brain_dump_updated_at']);
    if (time() - $updated > 86400) {
        mysqli_query($conn, "UPDATE users SET brain_dump = '', brain_dump_updated_at = NOW() WHERE id = $user_id");
        echo "";
    } else {
        echo $user['brain_dump'] ?? '';
    }
}
?>
