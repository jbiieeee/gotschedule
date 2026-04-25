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
        $description = mysqli_real_escape_string($conn, $_POST['description'] ?? '');
        $due_date = $_POST['due_date'];
        $task_time = $_POST['task_time'];
        $end_time = $_POST['end_time'] ?? date("H:i", strtotime($task_time . " +1 hour"));
        $priority = $_POST['priority'] ?? 'medium';
        $category = mysqli_real_escape_string($conn, $_POST['category'] ?? 'Personal');
        $recurrence = $_POST['recurrence'] ?? 'none';

        // Time-Slot Validation
        $check_query = "SELECT id FROM tasks WHERE user_id = ? AND due_date = ? AND deleted_at IS NULL AND (
            (task_time <= ? AND end_time > ?) OR
            (task_time < ? AND end_time >= ?) OR
            (? <= task_time AND ? > task_time)
        )";
        $check_stmt = $conn->prepare($check_query);
        $check_stmt->bind_param("isssssss", $user_id, $due_date, $task_time, $task_time, $end_time, $end_time, $task_time, $end_time);
        $check_stmt->execute();
        $check_stmt->store_result();

        if ($check_stmt->num_rows > 0) {
            $check_stmt->close();
            sendResponse('error', 'Conflict: This time slot is already occupied.');
        } else {
            $check_stmt->close();
            $stmt = $conn->prepare("INSERT INTO tasks (user_id, task_name, description, due_date, task_time, end_time, priority, category, recurrence) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("issssssss", $user_id, $task_name, $description, $due_date, $task_time, $end_time, $priority, $category, $recurrence);
            
            if ($stmt->execute()) {
                $task_id = $stmt->insert_id;
                $stmt->close();

                logActivity($conn, $user_id, 'TASK_CREATE', "Created task: $task_name");

                // Save collaborators
                if (!empty($_POST['collaborators'])) {
                    $collab_ids = explode(',', $_POST['collaborators']);
                    $collab_stmt = $conn->prepare("INSERT INTO task_collaborators (task_id, user_id) VALUES (?, ?)");
                    foreach ($collab_ids as $cid) {
                        $cid = (int)$cid;
                        $collab_stmt->bind_param("ii", $task_id, $cid);
                        $collab_stmt->execute();
                    }
                    $collab_stmt->close();
                }

                sendResponse('success', 'Task saved successfully!', ['id' => $task_id]);
            } else {
                sendResponse('error', 'Failed to save task');
            }
        }
    } elseif (isset($_POST['toggle_task'])) {
        $task_id = $_POST['task_id'];
        $new_status = $_POST['current_status'] === 'pending' ? 'completed' : 'pending';
        
        $stmt = $conn->prepare("UPDATE tasks SET status = ? WHERE id = ? AND user_id = ?");
        $stmt->bind_param("sii", $new_status, $task_id, $user_id);
        
        if ($stmt->execute()) {
            $stmt->close();
            logActivity($conn, $user_id, 'TASK_TOGGLE', "Task #$task_id marked as $new_status");
            sendResponse('success', 'Task updated!', ['status' => $new_status]);
        } else {
            sendResponse('error', 'Failed to update task');
        }
    } elseif (isset($_POST['delete_task'])) {
        $task_id = $_POST['task_id'];
        // Soft Delete
        $stmt = $conn->prepare("UPDATE tasks SET deleted_at = NOW() WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $task_id, $user_id);
        
        if ($stmt->execute()) {
            $stmt->close();
            logActivity($conn, $user_id, 'TASK_DELETE', "Task #$task_id moved to trash");
            sendResponse('success', 'Task moved to trash');
        } else {
            sendResponse('error', 'Failed to delete task');
        }
    }
}

// Fetch tasks (Exclude soft-deleted)
$tasks = mysqli_query($conn, "SELECT * FROM tasks WHERE user_id = $user_id AND deleted_at IS NULL ORDER BY due_date ASC, task_time ASC");

// Fetch next upcoming task
$next_task_query = "SELECT * FROM tasks WHERE user_id = $user_id AND deleted_at IS NULL AND status = 'pending' AND (due_date > CURRENT_DATE OR (due_date = CURRENT_DATE AND task_time > CURRENT_TIME)) ORDER BY due_date ASC, task_time ASC LIMIT 1";
$next_task_res = mysqli_query($conn, $next_task_query);
$next_task = mysqli_fetch_assoc($next_task_res);
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
        <header class="mb-5 reveal-animation text-center">
            <div class="row justify-content-center g-4">
                <div class="col-lg-8">
                    <h2 class="fw-black mb-1 logo-font display-5">Command Center</h2>
                    <p class="text-muted fs-6 mb-4">Welcome back, <span class="text-primary fw-bold"><?= explode(' ', $user_name)[0] ?></span>. Let's conquer the day.</p>
                    
                    <div class="d-flex flex-wrap justify-content-center align-items-center gap-3">
                        <div class="sync-status d-flex align-items-center gap-2 px-3 py-2 bg-white bg-opacity-5 rounded-pill border border-dark border-opacity-5">
                            <span class="sync-status-pulse"></span>
                            <span class="fs-8 fw-bold text-muted text-uppercase letter-spacing-1">Synced</span>
                        </div>
                        <div class="search-wrapper position-relative">
                            <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted opacity-50"></i>
                            <input type="text" id="task-search" class="form-control ps-5 rounded-pill border-dark border-opacity-5" placeholder="Search tasks..." style="min-width: 300px;">
                        </div>
                        <button class="btn-modern shadow-lg px-4 py-2" data-bs-toggle="modal" data-bs-target="#taskModal">
                            <i class="bi bi-plus-lg me-2"></i> Create Task
                        </button>
                    </div>
                </div>
            </div>

            <?php if ($next_task): ?>
            <div class="mt-4 p-4 rounded-4 glass-panel border-primary border-opacity-10 d-flex flex-column flex-sm-row align-items-center justify-content-center gap-4 overflow-hidden position-relative mx-auto" style="max-width: 900px;">
                <div class="position-absolute top-0 end-0 h-100 p-4 opacity-10 d-none d-lg-block">
                    <i class="bi bi-rocket-takeoff-fill" style="font-size: 5rem;"></i>
                </div>
                <div class="d-flex align-items-center gap-4 position-relative">
                    <div class="bg-primary text-white p-3 rounded-circle shadow-lg pulse-blue">
                        <i class="bi bi-lightning-fill fs-4"></i>
                    </div>
                    <div>
                        <h6 class="text-primary fw-black text-uppercase fs-9 mb-1 letter-spacing-1">Current Focus</h6>
                        <h4 class="fw-bold mb-0 text-dark"><?= htmlspecialchars($next_task['task_name']) ?></h4>
                        <div class="d-flex align-items-center gap-3 mt-2 text-muted fs-8">
                            <span><i class="bi bi-clock me-2"></i>Starts at <?= date("h:i A", strtotime($next_task['task_time'])) ?></span>
                            <span><i class="bi bi-geo-alt me-2"></i><?= htmlspecialchars($next_task['category']) ?></span>
                            <?php 
                            $target = new DateTime($next_task['due_date']);
                            $today = new DateTime();
                            $diff = $today->diff($target)->format("%a");
                            if ($diff > 0): ?>
                                <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-2">
                                    <i class="bi bi-hourglass-split me-1"></i> <?= $diff ?> days left
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="position-relative">
                    <button class="btn btn-primary rounded-pill px-4 shadow-sm fw-bold">Jump To Task</button>
                </div>
            </div>
            <?php endif; ?>
        </header>

        <!-- Task List and UI remains the same... -->

        <div class="row g-4">
            <!-- Task List Section -->
            <div class="col-lg-8">
                <div class="glass-panel h-100 reveal-animation">
                    <div class="d-flex justify-content-between align-items-center mb-5">
                        <div class="d-flex align-items-center gap-3">
                            <h4 class="mb-0 fw-bold">Recent Tasks</h4>
                            <div id="batch-actions-bar" class="d-none animate__animated animate__fadeIn">
                                <span class="badge bg-primary px-3 py-2 rounded-pill shadow-sm me-2 selected-count">0 Selected</span>
                                <button class="btn btn-sm btn-white rounded-pill px-3 py-1 border-danger text-danger hover-bg-danger" onclick="batchTaskAction('delete')">
                                    <i class="bi bi-trash3 me-1"></i> Batch Delete
                                </button>
                                <button class="btn btn-sm btn-white rounded-pill px-3 py-1" onclick="batchTaskAction('complete')">
                                    <i class="bi bi-check-all me-1"></i> Mark Done
                                </button>
                            </div>
                        </div>
                        <div class="task-filters d-flex gap-2">
                            <span class="badge bg-primary px-3 py-2 rounded-pill cursor-pointer shadow-sm">All</span>
                            <span class="badge bg-white shadow-sm text-muted px-3 py-2 rounded-pill cursor-pointer hover-bg-light">Planned</span>
                        </div>
                    </div>

                    <div class="task-list scroll-shadow-container" id="task-list">
                        <?php if (mysqli_num_rows($tasks) > 0): ?>
                            <?php while ($task = mysqli_fetch_assoc($tasks)): ?>
                                <div class="task-card mb-3 p-4 glass-panel border border-dark border-opacity-10 d-flex justify-content-between align-items-center transition-all <?= $task['status'] === 'completed' ? 'completed' : ''; ?> task-item" data-category="<?= $task['category'] ?>" data-name="<?= strtolower($task['task_name']) ?>">
                                    <div class="d-flex align-items-center gap-4">
                                        <div class="form-check m-0">
                                            <input class="form-check-input task-checkbox cursor-pointer" type="checkbox" value="<?= $task['id'] ?>" style="width: 20px; height: 20px;">
                                        </div>
                                        <form method="POST" class="m-0">
                                            <input type="hidden" name="task_id" value="<?= $task['id']; ?>">
                                            <input type="hidden" name="current_status" value="<?= $task['status']; ?>">
                                            <button type="submit" name="toggle_task" class="btn btn-sm p-0 text-primary fs-3 border-0 bg-transparent transition-all hover-scale">
                                                <i class="bi bi-<?= $task['status'] === 'completed' ? 'check-circle-fill' : 'circle'; ?>"></i>
                                            </button>
                                        </form>
                                        <div>
                                            <div class="d-flex align-items-center gap-2 mb-1">
                                                <h6 class="mb-0 fw-bold fs-6 text-dark"><?= htmlspecialchars($task['task_name']); ?></h6>
                                                <span class="badge bg-opacity-10 px-2 py-1 fs-9 rounded-pill priority-badge priority-badge-<?= $task['priority'] ?>">
                                                    <?= strtoupper($task['priority']) ?>
                                                </span>
                                            </div>
                                            <div class="d-flex align-items-center gap-3 text-muted fs-8">
                                                <span><i class="bi bi-calendar-event me-2"></i><?= date("M d, Y", strtotime($task['due_date'])); ?></span>
                                                <span class="opacity-50">|</span>
                                                <span><i class="bi bi-clock me-2"></i><?= date("h:i A", strtotime($task['task_time'])); ?> - <?= date("h:i A", strtotime($task['end_time'])); ?></span>
                                                <span class="opacity-50">|</span>
                                                <span class="text-primary fw-bold text-uppercase"><?= htmlspecialchars($task['category']) ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="dropdown">
                                            <button class="btn btn-icon bg-light rounded-circle border-0" data-bs-toggle="dropdown">
                                                <i class="bi bi-three-dots"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-4 p-2">
                                                <li><button class="dropdown-item rounded-3 fw-bold py-2 duplicate-btn" data-task='<?= json_encode($task) ?>'><i class="bi bi-copy me-2"></i> Duplicate</button></li>
                                                <li><hr class="dropdown-divider"></li>
                                                <li>
                                                    <form method="POST" class="m-0">
                                                        <input type="hidden" name="task_id" value="<?= $task['id']; ?>">
                                                        <button type="submit" name="delete_task" class="dropdown-item rounded-3 fw-bold py-2 text-danger"><i class="bi bi-trash3 me-2"></i> Delete</button>
                                                    </form>
                                                </li>
                                            </ul>
                                        </div>
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
 
            <!-- Sidebar Widgets Section -->
            <div class="col-lg-4">
                <!-- Productivity Score Card (NEW) -->
                <div class="glass-panel mb-4 reveal-animation overflow-hidden position-relative" style="animation-delay: 0.1s; background: #0f172a; color: white;">
                    <div class="position-absolute top-0 end-0 p-4 opacity-10">
                        <i class="bi bi-graph-up-arrow" style="font-size: 8rem; transform: rotate(-10deg);"></i>
                    </div>
                    <div class="position-relative">
                        <h6 class="text-white-50 fs-9 fw-black text-uppercase mb-4 letter-spacing-1">Productivity Analysis</h6>
                        <?php 
                        $total_tasks = mysqli_num_rows($tasks);
                        $completed_tasks = 0;
                        mysqli_data_seek($tasks, 0);
                        while($row = mysqli_fetch_assoc($tasks)) if($row['status'] == 'completed') $completed_tasks++;
                        $percent = $total_tasks > 0 ? round(($completed_tasks / $total_tasks) * 100) : 0;
                        ?>
                        <div class="d-flex align-items-end gap-3 mb-4">
                            <h1 class="fw-black mb-0 display-4"><?= $percent ?>%</h1>
                            <div class="mb-2">
                                <span class="badge bg-success border-0 rounded-pill px-2 py-1 fs-9"><i class="bi bi-caret-up-fill me-1"></i>12%</span>
                            </div>
                        </div>
                        
                        <div class="progress mb-4 bg-white bg-opacity-10" style="height: 6px; border-radius: 3px;">
                            <div class="progress-bar bg-primary shadow-lg" role="progressbar" style="width: <?= $percent ?>%; border-radius: 3px;"></div>
                        </div>
                        
                        <div class="row g-2">
                            <div class="col-6">
                                <div class="p-2 rounded-3 bg-white bg-opacity-5 border border-white border-opacity-5">
                                    <div class="text-white-50 fs-9 fw-bold">COMPLETED</div>
                                    <div class="fw-bold mt-1"><?= $completed_tasks ?> Tasks</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-2 rounded-3 bg-white bg-opacity-5 border border-white border-opacity-5">
                                    <div class="text-white-50 fs-9 fw-bold">REMAINING</div>
                                    <div class="fw-bold mt-1"><?= $total_tasks - $completed_tasks ?> Tasks</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="glass-panel mb-4 reveal-animation" style="animation-delay: 0.2s;">
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

                <div class="glass-panel mb-4 reveal-animation" style="animation-delay: 0.15s; background: #f0fdf4; border: 1px solid #bbfcce;">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0 fw-bold text-success-emphasis"><i class="bi bi-calendar-check me-2"></i>Habit Tracker</h5>
                        <button class="btn btn-sm btn-success bg-success bg-opacity-10 text-success border-0 rounded-circle" onclick="addHabitPrompt()">
                            <i class="bi bi-plus-lg"></i>
                        </button>
                    </div>
                    <div id="habits-container" class="hide-scroll" style="max-height: 250px; overflow-y: auto;">
                        <div class="text-center py-3 opacity-50 fs-9">Start a streak...</div>
                    </div>
                </div>

                <div class="glass-panel mb-4 reveal-animation" style="animation-delay: 0.2s; background: #fffbeb; border: 1px solid #fde68a;">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h5 class="mb-0 fw-bold text-warning-emphasis"><i class="bi bi-sticky-fill me-2"></i>Brain Dump</h5>
                        <span id="dump-status" class="fs-9 text-muted opacity-50">Auto-saves</span>
                    </div>
                    <p class="fs-9 text-muted mb-2">Clears automatically every 24 hours.</p>
                    <textarea id="brain-dump-area" class="fs-8 text-dark border-0 bg-transparent w-100 hide-scroll" style="height: 120px; outline: none; resize: none;" placeholder="Dump your thoughts here..."></textarea>
                </div>

                <div class="glass-panel reveal-animation" style="animation-delay: 0.3s;">
                    <h5 class="mb-4 fw-bold text-dark">Daily Achievements</h5>
                    <div class="achievement-list">
                        <div class="d-flex align-items-center gap-3 p-3 rounded-4 bg-success bg-opacity-5 border border-success border-opacity-10 mb-2">
                            <div class="bg-success text-white px-2 py-1 rounded-2 fs-9"><i class="bi bi-trophy"></i></div>
                            <div class="fs-8 text-dark fw-semibold">Task Finisher</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include 'task_modal.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="task.js"></script>
</body>
</html>
