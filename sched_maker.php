<?php
require_once 'includes/config.php';

// Authentication check
if (!isset($_SESSION['user_id'])) {
    header("Location: main.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];

// Fetch User Tasks for Scheduling
$query = "SELECT * FROM tasks WHERE user_id = $user_id AND status != 'completed' ORDER BY due_date ASC, task_time ASC";
$result = mysqli_query($conn, $query);
$tasks = [];
while ($row = mysqli_fetch_assoc($result)) {
    $tasks[] = $row;
}

// Logic to group tasks into daily slots
$schedule = [];
foreach ($tasks as $task) {
    if (!isset($schedule[$task['due_date']])) {
        $schedule[$task['due_date']] = [];
    }
    $schedule[$task['due_date']][] = $task;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Schedule Maker | GotSchedule</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/css/global.css">
    <link rel="stylesheet" href="assets/css/task.css">
</head>
<body class="dashboard-body">

    <!-- Sidebar -->
    <?php include 'includes/sidebar.php'; ?>

    <!-- Main Content -->
    <main class="main-content">
        <header class="d-flex justify-content-between align-items-center mb-5 reveal-animation">
            <div>
                <h2 class="fw-bold mb-1 logo-font">Magic Scheduler</h2>
                <p class="text-muted fs-7 mb-0">Transforming your tasks into a coherent, high-performance plan.</p>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-outline-primary rounded-pill px-4" onclick="window.print()">
                    <i class="bi bi-printer me-2"></i> Print Plan
                </button>
            </div>
        </header>

        <div class="row g-4 reveal-animation">
            <?php if (empty($schedule)): ?>
                <div class="col-12 text-center py-5 opacity-50">
                    <i class="bi bi-magic display-1 mb-3 text-primary"></i>
                    <h5 class="fw-bold">No pending tasks to schedule.</h5>
                    <p class="fs-7 text-muted">Take a break or add new goals to see your magic plan!</p>
                </div>
            <?php else: ?>
                <?php foreach($schedule as $date => $day_tasks): ?>
                    <div class="col-12 mb-4">
                        <div class="glass-panel p-4 h-100 shadow-sm border border-dark border-opacity-10">
                            <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom border-dark border-opacity-5">
                                <h4 class="fw-bold mb-0 text-primary">
                                    <i class="bi bi-calendar-check me-2"></i>
                                    <?= date('l, F d', strtotime($date)) ?>
                                </h4>
                                <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-3 py-2 fs-8 fw-bold border border-primary border-opacity-10">
                                    <?= count($day_tasks) ?> Appointments
                                </span>
                            </div>

                            <div class="timeline-container ps-4 border-start border-dark border-opacity-10 position-relative">
                                <?php foreach($day_tasks as $index => $task): ?>
                                    <div class="timeline-item mb-4 position-relative">
                                        <!-- Point marker -->
                                        <div class="position-absolute start-0 translate-middle rounded-circle bg-primary" style="left: -24px !important; width: 14px; height: 14px; margin-top: 8px; box-shadow: 0 0 12px rgba(79, 70, 229, 0.4); border: 3px solid white;"></div>
                                        
                                        <div class="row align-items-center">
                                            <div class="col-md-2">
                                                <div class="fw-bold fs-7 text-dark"><?= date('h:i A', strtotime($task['task_time'])) ?></div>
                                            </div>
                                            <div class="col-md-10">
                                                <div class="p-3 bg-white shadow-sm rounded-4 transition-all hover-scale-slide d-flex justify-content-between align-items-center border border-dark border-opacity-5">
                                                    <div>
                                                        <div class="fw-bold fs-7 text-dark"><?= htmlspecialchars($task['task_name']) ?></div>
                                                        <div class="fs-9 text-muted fw-semibold text-uppercase tracking-wider">Standard Priority</div>
                                                    </div>
                                                    <i class="bi bi-arrow-right-short text-primary fs-3"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>

    <style>
        .hover-scale-slide { transition: var(--transition); }
        .timeline-item:hover .hover-scale-slide {
            transform: translateX(8px);
            border-color: var(--primary) !important;
        }
        @media print {
            .sidebar-modern, .btn, .sidebar-toggle { display: none !important; }
            .main-content { margin-left: 0 !important; width: 100% !important; padding: 0 !important; }
            .glass-panel { border: 1px solid #eee !important; box-shadow: none !important; background: white !important; }
            .text-primary { color: #4f46e5 !important; }
        }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
