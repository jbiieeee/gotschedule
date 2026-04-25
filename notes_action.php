<?php
require_once 'config.php';

// Authentication check
if (!isset($_SESSION['user_id'])) {
    if (isset($_POST['ajax']) || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest')) {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
        exit();
    }
    header("Location: main.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$is_ajax = isset($_POST['ajax']) || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest');

function sendResponse($status, $message, $data = []) {
    global $is_ajax;
    if ($is_ajax) {
        header('Content-Type: application/json');
        echo json_encode(['status' => $status, 'message' => $message, 'data' => $data]);
        exit();
    } else {
        header("Location: notes.php");
        exit();
    }
}

// Handle Add Note
if (isset($_POST['add_note'])) {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $content = mysqli_real_escape_string($conn, $_POST['content']);
    $color = mysqli_real_escape_string($conn, $_POST['color_tag']);
    
    $query = "INSERT INTO notes (user_id, title, content, color_tag) VALUES ($user_id, '$title', '$content', '$color')";
    if (mysqli_query($conn, $query)) {
        sendResponse('success', 'Note added successfully!', ['id' => mysqli_insert_id($conn)]);
    } else {
        sendResponse('error', 'Failed to add note: ' . mysqli_error($conn));
    }
}

// Handle Delete Note
if (isset($_GET['delete'])) {
    $note_id = (int)$_GET['delete'];
    $query = "DELETE FROM notes WHERE id = $note_id AND user_id = $user_id";
    if (mysqli_query($conn, $query)) {
        sendResponse('success', 'Note deleted successfully!');
    } else {
        sendResponse('error', 'Failed to delete note');
    }
}

// Handle Edit Note
if (isset($_POST['edit_note'])) {
    $note_id = (int)$_POST['note_id'];
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $content = mysqli_real_escape_string($conn, $_POST['content']);
    $color = mysqli_real_escape_string($conn, $_POST['color_tag']);
    
    $query = "UPDATE notes SET title='$title', content='$content', color_tag='$color' WHERE id=$note_id AND user_id=$user_id";
    if (mysqli_query($conn, $query)) {
        sendResponse('success', 'Note updated successfully!');
    } else {
        sendResponse('error', 'Failed to update note');
    }
}
?>
