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
$user_name = $_SESSION['user_name'];
$is_ajax = isset($_POST['ajax']) || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest');

function sendResponse($status, $message, $data = []) {
    global $is_ajax;
    if ($is_ajax) {
        header('Content-Type: application/json');
        echo json_encode(['status' => $status, 'message' => $message, 'data' => $data]);
        exit();
    } else {
        // For non-AJAX, we'll just continue to fetch tasks and render
        return;
    }
}

// Handle task operations
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_task'])) {
        $task_name = mysqli_real_escape_string($conn, $_POST['task_name']);
        $due_date = $_POST['due_date'];
        $task_time = $_POST['task_time'];

        $stmt = $conn->prepare("INSERT INTO tasks (user_id, task_name, due_date, task_time) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $user_id, $task_name, $due_date, $task_time);
        
        if ($stmt->execute()) {
            $task_id = $stmt->insert_id;
            $stmt->close();
            sendResponse('success', 'Task added successfully!', ['id' => $task_id]);
        } else {
            sendResponse('error', 'Failed to add task');
        }
    } elseif (isset($_POST['toggle_task'])) {
        $task_id = $_POST['task_id'];
        $new_status = $_POST['current_status'] === 'pending' ? 'completed' : 'pending';
        
        $stmt = $conn->prepare("UPDATE tasks SET status = ? WHERE id = ? AND user_id = ?");
        $stmt->bind_param("sii", $new_status, $task_id, $user_id);
        
        if ($stmt->execute()) {
            $stmt->close();
            sendResponse('success', 'Task updated!', ['status' => $new_status]);
        } else {
            sendResponse('error', 'Failed to update task');
        }
    } elseif (isset($_POST['delete_task'])) {
        $task_id = $_POST['task_id'];
        $stmt = $conn->prepare("DELETE FROM tasks WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $task_id, $user_id);
        
        if ($stmt->execute()) {
            $stmt->close();
            sendResponse('success', 'Task deleted!');
        } else {
            sendResponse('error', 'Failed to delete task');
        }
    }
}

// Fetch tasks
$tasks = mysqli_query($conn, "SELECT * FROM tasks WHERE user_id = $user_id ORDER BY due_date ASC, task_time ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Tasks | GotSchedule</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="global.css">
    <link rel="stylesheet" href="task.css">
</head>
<body class="dashboard-body">
    <script src="notifications.js"></script>

    <!-- Sidebar -->
    <?php include 'sidebar.php'; ?>

    <!-- Main Content -->
    <main class="main-content">
        <header class="d-flex justify-content-between align-items-center mb-5 reveal-animation">
            <div>
                <h2 class="fw-bold mb-1 logo-font">Welcome back!</h2>
                <div class="d-flex align-items-center gap-2">
                    <span class="fs-7 text-muted"><?= date('l, F jS'); ?></span>
                    <span class="badge bg-primary-subtle text-primary border border-primary border-opacity-10 rounded-pill px-3 fw-semibold">Pro Dashboard</span>
                </div>
            </div>
            <button class="btn-modern shadow-lg" data-bs-toggle="modal" data-bs-target="#taskModal">
                <i class="bi bi-plus-lg"></i>
                <span class="d-none d-sm-inline">Create New Task</span>
            </button>
        </header>

        <!-- Task List and UI remains the same... -->

        <div class="row g-4">
            <!-- Task List Section -->
            <div class="col-lg-8">
                <div class="glass-panel h-100 reveal-animation">
                    <div class="d-flex justify-content-between align-items-center mb-5">
                        <h4 class="mb-0 fw-bold">Recent Tasks</h4>
                        <div class="task-filters d-flex gap-2">
                            <span class="badge bg-primary px-3 py-2 rounded-pill cursor-pointer shadow-sm">All</span>
                            <span class="badge bg-white shadow-sm text-muted px-3 py-2 rounded-pill cursor-pointer hover-bg-light">Planned</span>
                        </div>
                    </div>

                    <div class="task-list scroll-shadow-container" id="task-list">
                        <?php if (mysqli_num_rows($tasks) > 0): ?>
                            <?php while ($task = mysqli_fetch_assoc($tasks)): ?>
                                <div class="task-card mb-3 p-4 glass-panel border border-dark border-opacity-10 d-flex justify-content-between align-items-center transition-all <?= $task['status'] === 'completed' ? 'completed' : ''; ?>">
                                    <div class="d-flex align-items-center gap-4">
                                        <form method="POST" class="m-0">
                                            <input type="hidden" name="task_id" value="<?= $task['id']; ?>">
                                            <input type="hidden" name="current_status" value="<?= $task['status']; ?>">
                                            <button type="submit" name="toggle_task" class="btn btn-sm p-0 text-primary fs-3 border-0 bg-transparent transition-all hover-scale">
                                                <i class="bi bi-<?= $task['status'] === 'completed' ? 'check-circle-fill' : 'circle'; ?>"></i>
                                            </button>
                                        </form>
                                        <div>
                                            <h6 class="mb-1 fw-bold fs-6 text-dark"><?= htmlspecialchars($task['task_name']); ?></h6>
                                            <div class="d-flex align-items-center gap-3 text-muted fs-8">
                                                <span><i class="bi bi-calendar-event me-2"></i><?= date("M d, Y", strtotime($task['due_date'])); ?></span>
                                                <span class="opacity-50">|</span>
                                                <span><i class="bi bi-clock me-2"></i><?= date("h:i A", strtotime($task['task_time'])); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center gap-3">
                                        <span class="badge bg-<?= $task['status'] === 'completed' ? 'success' : 'primary'; ?>-subtle text-<?= $task['status'] === 'completed' ? 'success' : 'primary'; ?> rounded-pill px-3 py-2 fs-8 fw-bold border border-<?= $task['status'] === 'completed' ? 'success' : 'primary'; ?> border-opacity-20">
                                            <?= strtoupper($task['status']); ?>
                                        </span>
                                        <form method="POST" class="m-0">
                                            <input type="hidden" name="task_id" value="<?= $task['id']; ?>">
                                            <button type="submit" name="delete_task" class="btn btn-icon text-danger-emphasis bg-danger bg-opacity-10 border-0 rounded-circle transition-all hover-bg-danger">
                                                <i class="bi bi-trash3 fs-7"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="text-center py-5 opacity-50">
                                <i class="bi bi-wind fs-1 mb-3 d-block text-primary"></i>
                                <h5 class="fw-bold">No tasks found</h5>
                                <p class="fs-7">Your schedule is currently clear. Add a task to begin.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
 
            <!-- Calendar Side Section -->
            <div class="col-lg-4">
                <div class="glass-panel mb-4 reveal-animation" style="animation-delay: 0.1s;">
                    <h5 class="mb-4 fw-bold">Mini Calendar</h5>
                    <div id="calendar-widget" class="calendar-wrapper border border-dark border-opacity-10 p-3 rounded-4">
                        <!-- Calendar visual placeholder -->
                        <div class="text-center py-4 bg-light rounded-4 mb-3">
                            <i class="bi bi-calendar-check fs-2 text-primary opacity-50 mb-2 d-block"></i>
                            <div class="fw-bold fs-7 mb-1 text-dark"><?= date('F Y') ?></div>
                            <div class="fs-8 text-muted">Tasks properly synced</div>
                        </div>
                        <a href="calendar.php" class="btn btn-sm w-100 py-2 fs-8 fw-bold text-primary border border-primary border-opacity-20 rounded-3 hover-bg-primary">Open Full Calendar</a>
                    </div>
                </div>
                <div class="glass-panel reveal-animation" style="animation-delay: 0.2s;">
                    <h5 class="mb-4 fw-bold">Daily Completion</h5>
                    <?php 
                    $total_tasks = mysqli_num_rows($tasks);
                    $completed_tasks = 0;
                    mysqli_data_seek($tasks, 0); // Reset pointer
                    while($row = mysqli_fetch_assoc($tasks)) if($row['status'] == 'completed') $completed_tasks++;
                    $percent = $total_tasks > 0 ? round(($completed_tasks / $total_tasks) * 100) : 0;
                    ?>
                    <div class="progress mb-3 overflow-visible" style="height: 12px; background: rgba(0,0,0,0.05); border-radius: 10px;">
                        <div class="progress-bar bg-success rounded-pill shadow-sm transition-all" role="progressbar" style="width: <?= $percent ?>%; box-shadow: 0 0 15px rgba(16, 185, 129, 0.4);"></div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <p class="text-muted fs-8 mb-0"><?= $completed_tasks ?> of <?= $total_tasks ?> goals achieved</p>
                        <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-2 py-1 fs-8 fw-bold">
                            <?= $percent ?>%
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Modal for New Task (Bootstrap 5) - Placed at base for flawless interaction -->
    <div class="modal fade" id="taskModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content glass-panel border-dark border-opacity-10 p-0 overflow-hidden shadow-lg">
                <div class="modal-header border-bottom border-dark border-opacity-5 p-4">
                    <h5 class="modal-title fw-bold text-dark">Create New Task</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="task-form" method="POST" action="task.php">
                    <div class="modal-body p-4 text-dark">
                        <div class="mb-4">
                            <label class="form-label text-dark">Task Name</label>
                            <input type="text" name="task_name" class="form-control" placeholder="What needs to be done?" required>
                        </div>
                        <div class="row g-3 mb-2">
                            <div class="col-6">
                                <label class="form-label text-dark">Due Date</label>
                                <input type="date" name="due_date" class="form-control" value="<?= date('Y-m-d'); ?>" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label text-dark">Time</label>
                                <input type="time" name="task_time" class="form-control" value="<?= date('H:i'); ?>" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-top border-dark border-opacity-5 p-4">
                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="add_task" class="btn btn-primary rounded-pill px-4 shadow-sm">Save Task</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="task.js"></script>
</body>
</html>
