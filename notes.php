<?php
require_once 'includes/config.php';

// Authentication check
if (!isset($_SESSION['user_id'])) {
    header("Location: main.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];

// Fetch User Notes
$query = "SELECT * FROM notes WHERE user_id = $user_id ORDER BY created_at DESC";
$result = mysqli_query($conn, $query);
$notes = [];
while ($row = mysqli_fetch_assoc($result)) {
    $notes[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Notes | GotSchedule</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/css/global.css">
    <link rel="stylesheet" href="assets/css/task.css">
</head>
<body class="dashboard-body">
    <script src="assets/js/notifications.js"></script>

    <!-- Sidebar -->
    <?php include 'includes/sidebar.php'; ?>

    <!-- Main Content -->
    <main class="main-content">
        <header class="d-flex justify-content-between align-items-center mb-5 reveal-animation">
            <div>
                <h2 class="fw-bold mb-1 logo-font">My Notes</h2>
                <p class="text-muted fs-7 mb-0">Capture ideas, reminders, and creative sparks.</p>
            </div>
            <button class="btn btn-primary rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#addNoteModal">
                <i class="bi bi-plus-lg me-2"></i> New Note
            </button>
        </header>

        <!-- Notes Grid -->
        <div class="row g-4 reveal-animation">
            <?php if (empty($notes)): ?>
                <div class="col-12 text-center py-5 opacity-25">
                    <i class="bi bi-journal-text display-1 mb-3"></i>
                    <h5>No notes yet. Start capturing your thoughts!</h5>
                </div>
            <?php else: ?>
                <?php foreach($notes as $note): ?>
                    <div class="col-md-6 col-lg-4 col-xl-3">
                        <div class="glass-panel note-card h-100 p-4" style="border-left: 6px solid <?= $note['color_tag'] ?>;">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <h5 class="fw-bold mb-0 text-truncate text-dark" title="<?= htmlspecialchars($note['title']) ?>">
                                    <?= htmlspecialchars($note['title']) ?>
                                </h5>
                                <div class="dropdown">
                                    <button class="btn btn-link text-muted p-0" data-bs-toggle="dropdown">
                                        <i class="bi bi-three-dots-vertical"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end bg-white shadow-lg border border-dark border-opacity-10">
                                        <li><a class="dropdown-item text-dark fw-semibold" href="#" data-bs-toggle="modal" data-bs-target="#editNoteModal<?= $note['id'] ?>">Edit</a></li>
                                        <li><a class="dropdown-item text-danger fw-semibold" href="notes_action.php?delete=<?= $note['id'] ?>" onclick="return confirm('Delete this note?')">Delete</a></li>
                                    </ul>
                                </div>
                            </div>
                            <p class="fs-7 text-muted mb-4 note-content hide-scroll">
                                <?= nl2br(htmlspecialchars($note['content'])) ?>
                            </p>
                            <div class="mt-auto pt-3 border-top border-dark border-opacity-5 fs-9 text-muted d-flex justify-content-between align-items-center">
                                <span><?= date('M d, Y', strtotime($note['created_at'])) ?></span>
                                <i class="bi bi-pin-angle-fill opacity-25"></i>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>

    <!-- Modals Section (Outside Main Content to avoid stacking context issues) -->
    <?php foreach($notes as $note): ?>
        <!-- Edit Note Modal -->
        <div class="modal fade" id="editNoteModal<?= $note['id'] ?>" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content glass-panel border-dark border-opacity-10 p-0 overflow-hidden shadow-lg">
                    <form action="notes_action.php" method="POST">
                        <div class="modal-header border-bottom border-dark border-opacity-5 p-4">
                            <h5 class="modal-title fw-bold text-dark">Edit Note</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-4 text-dark">
                            <input type="hidden" name="note_id" value="<?= $note['id'] ?>">
                            <div class="mb-3">
                                <label class="form-label">Title</label>
                                <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($note['title']) ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Content</label>
                                <textarea name="content" class="form-control" rows="5" required><?= htmlspecialchars($note['content']) ?></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Color Tag</label>
                                <div class="d-flex gap-2">
                                    <?php $colors = ['#6366f1', '#10b981', '#f43f5e', '#f59e0b', '#8b5cf6']; ?>
                                    <?php foreach($colors as $color): ?>
                                        <input type="radio" name="color_tag" value="<?= $color ?>" class="btn-check" id="colorEdit<?= $note['id'] . $color ?>" <?= $note['color_tag'] == $color ? 'checked' : '' ?>>
                                        <label class="btn btn-sm rounded-circle p-2 shadow-sm" for="colorEdit<?= $note['id'] . $color ?>" style="background-color: <?= $color ?>; width: 30px; height: 30px; cursor: pointer;"></label>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer border-top border-dark border-opacity-5 p-4">
                            <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" name="edit_note" class="btn btn-primary rounded-pill px-4 shadow-sm">Update Note</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    <?php endforeach; ?>

    <!-- Add Note Modal -->
    <div class="modal fade" id="addNoteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content glass-panel border-dark border-opacity-10 p-0 overflow-hidden shadow-lg">
                <form action="notes_action.php" method="POST">
                    <div class="modal-header border-bottom border-dark border-opacity-5 p-4">
                        <h5 class="modal-title fw-bold text-dark">New Inspiration</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4 text-dark">
                        <div class="mb-3">
                            <label class="form-label">Title</label>
                            <input type="text" name="title" class="form-control" placeholder="What's on your mind?" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Content</label>
                            <textarea name="content" class="form-control" rows="5" placeholder="Details go here..." required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Color Tag</label>
                            <div class="d-flex gap-2">
                                <?php $colors = ['#6366f1', '#10b981', '#f43f5e', '#f59e0b', '#8b5cf6']; ?>
                                <?php foreach($colors as $color): ?>
                                    <input type="radio" name="color_tag" value="<?= $color ?>" class="btn-check" id="colorAdd<?= $color ?>" <?= $color == '#6366f1' ? 'checked' : '' ?>>
                                    <label class="btn btn-sm rounded-circle p-2 shadow-sm" for="colorAdd<?= $color ?>" style="background-color: <?= $color ?>; width: 30px; height: 30px; cursor: pointer;"></label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-top border-dark border-opacity-5 p-4">
                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="add_note" class="btn btn-primary rounded-pill px-4 shadow-sm">Save Note</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <style>
        .note-card {
            display: flex;
            flex-direction: column;
            transition: var(--transition);
            background: rgba(255, 255, 255, 0.7) !important;
        }
        .note-card:hover { transform: translateY(-5px); background: rgba(255, 255, 255, 0.95) !important; box-shadow: 0 15px 30px -10px rgba(0, 0, 0, 0.1); }
        .note-content { max-height: 150px; overflow-y: auto; line-height: 1.6; color: var(--text-muted) !important; }
        .fs-9 { font-size: 0.65rem; }
        .text-dark { color: #1e293b !important; }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/notes.js"></script>
</body>
</html>
