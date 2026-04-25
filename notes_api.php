<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    exit(json_encode(['status' => 'error', 'message' => 'Unauthorized']));
}

$user_id = $_SESSION['user_id'];
$action = $_POST['action'] ?? $_GET['action'] ?? 'fetch';

if ($action == 'fetch') {
    $res = mysqli_query($conn, "SELECT * FROM notes WHERE user_id = $user_id ORDER BY is_pinned DESC, created_at DESC");
    $notes = [];
    while ($row = mysqli_fetch_assoc($res)) $notes[] = $row;
    echo json_encode($notes);
} elseif ($action == 'save') {
    $id = $_POST['id'] ?? null;
    $title = mysqli_real_escape_string($conn, $_POST['title'] ?? 'New Note');
    $content = mysqli_real_escape_string($conn, $_POST['content'] ?? '');
    $is_pinned = $_POST['is_pinned'] ?? 0;
    $is_checklist = $_POST['is_checklist'] ?? 0;
    
    if ($id) {
        $stmt = $conn->prepare("UPDATE notes SET title = ?, content = ?, is_pinned = ?, is_checklist = ? WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ssiiii", $title, $content, $is_pinned, $is_checklist, $id, $user_id);
    } else {
        $stmt = $conn->prepare("INSERT INTO notes (user_id, title, content, is_pinned, is_checklist) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issii", $user_id, $title, $content, $is_pinned, $is_checklist);
    }
    
    if ($stmt->execute()) echo json_encode(['status' => 'success']);
    else echo json_encode(['status' => 'error']);
} elseif ($action == 'toggle_pin') {
    $id = $_POST['id'];
    mysqli_query($conn, "UPDATE notes SET is_pinned = 1 - is_pinned WHERE id = $id AND user_id = $user_id");
    echo json_encode(['status' => 'success']);
} elseif ($action == 'toggle_checklist') {
    $id = $_POST['id'];
    mysqli_query($conn, "UPDATE notes SET is_checklist = 1 - is_checklist WHERE id = $id AND user_id = $user_id");
    echo json_encode(['status' => 'success']);
} elseif ($action == 'share') {
    $id = $_POST['id'];
    $key = bin2hex(random_bytes(8));
    mysqli_query($conn, "UPDATE notes SET is_shared = 1, collab_key = '$key' WHERE id = $id AND user_id = $user_id");
    echo json_encode(['status' => 'success', 'collab_key' => $key]);
} elseif ($action == 'delete') {
    $id = $_POST['id'];
    mysqli_query($conn, "DELETE FROM notes WHERE id = $id AND user_id = $user_id");
    echo json_encode(['status' => 'success']);
}
?>
