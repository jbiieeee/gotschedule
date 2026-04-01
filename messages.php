<?php
require_once 'config.php';

// Authentication check
if (!isset($_SESSION['user_id'])) {
    header("Location: main.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];

// Handle Sending Message
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_message'])) {
    $receiver_id = (int)$_POST['receiver_id'];
    $message = mysqli_real_escape_string($conn, $_POST['message']);
    
    if (!empty($message)) {
        $query = "INSERT INTO messages (sender_id, receiver_id, message) VALUES ($user_id, $receiver_id, '$message')";
        mysqli_query($conn, $query);
    }
    header("Location: messages.php?chat_with=$receiver_id");
    exit();
}

// Fetch Users for Chat List
$users_query = "SELECT id, first_name, last_name, role FROM users WHERE id != $user_id";
$users_result = mysqli_query($conn, $users_query);

// Fetch Active Chat
$chat_with = isset($_GET['chat_with']) ? (int)$_GET['chat_with'] : null;
$messages = [];
if ($chat_with) {
    $msg_query = "SELECT * FROM messages 
                  WHERE (sender_id = $user_id AND receiver_id = $chat_with) 
                  OR (sender_id = $chat_with AND receiver_id = $user_id) 
                  ORDER BY created_at ASC";
    $msg_result = mysqli_query($conn, $msg_query);
    while ($row = mysqli_fetch_assoc($msg_result)) {
        $messages[] = $row;
    }
    
    // Mark as read
    $update_read = "UPDATE messages SET is_read = 1 WHERE sender_id = $chat_with AND receiver_id = $user_id";
    mysqli_query($conn, $update_read);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages | GotSchedule Collaboration</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="global.css">
    <link rel="stylesheet" href="task.css">
</head>
<body class="dashboard-body">

    <!-- Sidebar -->
    <?php include 'sidebar.php'; ?>

    <!-- Main Content -->
    <main class="main-content">
        <header class="d-flex justify-content-between align-items-center mb-5 reveal-animation">
            <div>
                <h2 class="fw-bold mb-1 logo-font">Collaboration Hub</h2>
                <p class="text-muted fs-7 mb-0">Connect with your team and stay synchronized.</p>
            </div>
        </header>

        <div class="row g-4 fill-height">
            <!-- Sidebar: User List -->
            <div class="col-lg-4">
                <div class="glass-panel h-100 p-4 reveal-animation">
                    <h5 class="mb-4 fw-bold">Contacts</h5>
                    <div class="user-list scroll-shadow-container" style="max-height: 500px;">
                        <?php while($u = mysqli_fetch_assoc($users_result)): ?>
                            <a href="?chat_with=<?= $u['id'] ?>" class="text-decoration-none">
                                <div class="user-item p-3 mb-2 rounded-4 transition-all <?= $chat_with == $u['id'] ? 'bg-primary bg-opacity-10 border border-primary border-opacity-20' : 'bg-white bg-opacity-5 hover-bg-white' ?>">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="avatar-sm bg-<?= $u['role'] === 'admin' ? 'accent' : ($u['role'] === 'adviser' ? 'primary' : 'secondary') ?> rounded-circle d-flex align-items-center justify-content-center text-white fw-bold">
                                            <?= substr($u['first_name'], 0, 1) ?>
                                        </div>
                                        <div class="flex-grow-1 overflow-hidden">
                                            <div class="fw-bold fs-7 text-truncate"><?= htmlspecialchars($u['first_name'] . ' ' . $u['last_name']) ?></div>
                                            <div class="fs-8 text-muted opacity-50"><?= ucfirst($u['role']) ?></div>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>

            <!-- Main Chat Area -->
            <div class="col-lg-8">
                <div class="glass-panel h-100 d-flex flex-column p-4 reveal-animation" style="animation-delay: 0.1s;">
                    <?php if ($chat_with): 
                        mysqli_data_seek($users_result, 0);
                        $target_user = null;
                        while($u = mysqli_fetch_assoc($users_result)) if($u['id'] == $chat_with) $target_user = $u;
                    ?>
                        <div class="chat-header pb-3 mb-3 border-bottom border-white border-opacity-10 d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 fw-bold"><?= htmlspecialchars($target_user['first_name'] . ' ' . $target_user['last_name']) ?></h5>
                            <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3 fs-8 fw-bold">Online</span>
                        </div>

                        <div class="chat-messages flex-grow-1 overflow-auto mb-4 p-2" id="chat-messages">
                            <?php foreach($messages as $msg): ?>
                                <div class="d-flex <?= $msg['sender_id'] == $user_id ? 'justify-content-end' : 'justify-content-start' ?> mb-3">
                                    <div class="message-pill px-4 py-2 <?= $msg['sender_id'] == $user_id ? 'bg-primary text-white rounded-start-4 rounded-end-4 rounded-bottom-0' : 'bg-white bg-opacity-10 text-white rounded-start-4 rounded-end-4 rounded-top-0' ?>" style="max-width: 80%;">
                                        <div class="fs-7"><?= htmlspecialchars($msg['message']) ?></div>
                                        <div class="fs-9 opacity-50 mt-1 text-end"><?= date('h:i A', strtotime($msg['created_at'])) ?></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <form method="POST" class="chat-input-area d-flex gap-2">
                            <input type="hidden" name="receiver_id" value="<?= $chat_with ?>">
                            <input type="text" name="message" class="form-control bg-white bg-opacity-5 border-white border-opacity-10 border-0 rounded-pill px-4" placeholder="Type a message..." required autocomplete="off">
                            <button type="submit" name="send_message" class="btn btn-primary rounded-circle px-3 py-2 transition-all hover-scale">
                                <i class="bi bi-send-fill"></i>
                            </button>
                        </form>
                    <?php else: ?>
                        <div class="h-100 d-flex flex-column align-items-center justify-content-center opacity-25">
                            <i class="bi bi-chat-dots fs-1 mb-3"></i>
                            <h5>Select a contact to start messaging</h5>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <style>
        .fs-9 { font-size: 0.65rem; }
        .message-pill { box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        .user-item:hover { background: rgba(255,255,255,0.08) !important; }
        .chat-messages::-webkit-scrollbar { width: 4px; }
        .chat-messages::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 10px; }
    </style>

    <script>
        // Scroll to bottom of chat
        const chatBox = document.getElementById('chat-messages');
        if (chatBox) chatBox.scrollTop = chatBox.scrollHeight;
    </script>
</body>
</html>
