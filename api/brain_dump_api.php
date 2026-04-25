<?php
require_once '../includes/config.php';
checkAuth();

$user_id = $_SESSION['user_id'];

if (isset($_GET['fetch'])) {
    $stmt = $conn->prepare("SELECT content FROM brain_dump WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        echo $row['content'];
    } else {
        echo "";
    }
    exit();
}

if (isset($_POST['save'])) {
    $content = $_POST['content'];
    $stmt = $conn->prepare("INSERT INTO brain_dump (user_id, content) VALUES (?, ?) ON DUPLICATE KEY UPDATE content = VALUES(content)");
    $stmt->bind_param("is", $user_id, $content);
    $stmt->execute();
    echo "Success";
    exit();
}
?>
