<?php
require_once '../includes/config.php';

if (!isset($_SESSION['user_id'])) {
    exit(json_encode(['status' => 'error', 'message' => 'Unauthorized']));
}

$current_user_id = $_SESSION['user_id'];
$query = isset($_GET['q']) ? trim($_GET['q']) : '';

if (empty($query)) {
    echo json_encode([]);
    exit();
}

// User requested ONLY full name matches (Security/Privacy)
$stmt = $conn->prepare("SELECT id, user_name FROM users WHERE user_name = ? AND id != ? LIMIT 1");
$stmt->bind_param("si", $query, $current_user_id);
$stmt->execute();
$result = $stmt->get_result();

$users = [];
while ($row = $result->fetch_assoc()) {
    $users[] = $row;
}

header('Content-Type: application/json');
echo json_encode($users);
?>
