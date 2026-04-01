<?php
require_once 'config.php';

// Authentication check
if (!isset($_SESSION['user_id'])) {
    header("Location: main.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Handle Add Note
if (isset($_POST['add_note'])) {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $content = mysqli_real_escape_string($conn, $_POST['content']);
    $color = mysqli_real_escape_string($conn, $_POST['color_tag']);
    
    $query = "INSERT INTO notes (user_id, title, content, color_tag) VALUES ($user_id, '$title', '$content', '$color')";
    mysqli_query($conn, $query);
    header("Location: notes.php");
    exit();
}

// Handle Delete Note
if (isset($_GET['delete'])) {
    $note_id = (int)$_GET['delete'];
    $query = "DELETE FROM notes WHERE id = $note_id AND user_id = $user_id";
    mysqli_query($conn, $query);
    header("Location: notes.php");
    exit();
}

// Handle Edit Note
if (isset($_POST['edit_note'])) {
    $note_id = (int)$_POST['note_id'];
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $content = mysqli_real_escape_string($conn, $_POST['content']);
    $color = mysqli_real_escape_string($conn, $_POST['color_tag']);
    
    $query = "UPDATE notes SET title='$title', content='$content', color_tag='$color' WHERE id=$note_id AND user_id=$user_id";
    mysqli_query($conn, $query);
    header("Location: notes.php");
    exit();
}
?>
