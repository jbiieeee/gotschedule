<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    exit(json_encode(['status' => 'error']));
}

$user_id = $_SESSION['user_id'];
$action = $_POST['action'] ?? $_GET['action'] ?? 'fetch';

if ($action == 'fetch') {
    $res = mysqli_query($conn, "SELECT * FROM habits WHERE user_id = $user_id ORDER BY created_at DESC");
    $habits = [];
    while ($row = mysqli_fetch_assoc($res)) {
        $row['history'] = json_decode($row['history'] ?? '[]', true);
        $habits[] = $row;
    }
    echo json_encode($habits);
} elseif ($action == 'toggle_day') {
    $id = $_POST['id'];
    $date = $_POST['date']; // Format YYYY-MM-DD
    
    $res = mysqli_query($conn, "SELECT history FROM habits WHERE id = $id AND user_id = $user_id");
    $row = mysqli_fetch_assoc($res);
    $history = json_decode($row['history'] ?? '[]', true);
    
    if (($key = array_search($date, $history)) !== false) {
        unset($history[$key]); // Remove if exists
    } else {
        $history[] = $date; // Add if not
    }
    
    $json_history = json_encode(array_values($history));
    $stmt = $conn->prepare("UPDATE habits SET history = ? WHERE id = ?");
    $stmt->bind_param("si", $json_history, $id);
    $stmt->execute();
    echo json_encode(['status' => 'success', 'history' => $history]);
} elseif ($action == 'add') {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $stmt = $conn->prepare("INSERT INTO habits (user_id, habit_name, history) VALUES (?, ?, '[]')");
    $stmt->bind_param("is", $user_id, $name);
    $stmt->execute();
    echo json_encode(['status' => 'success']);
} elseif ($action == 'delete') {
    $id = $_POST['id'];
    mysqli_query($conn, "DELETE FROM habits WHERE id = $id AND user_id = $user_id");
    echo json_encode(['status' => 'success']);
}
?>
