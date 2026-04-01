<?php
require_once 'config.php';

// Authentication check
if (!isset($_SESSION['user_id'])) {
    header("Location: main.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];

// Calendar Navigation Logic
$month = isset($_GET['m']) ? (int)$_GET['m'] : (int)date('m');
$year = isset($_GET['y']) ? (int)$_GET['y'] : (int)date('Y');

// Handle overflow/underflow
if ($month > 12) { $month = 1; $year++; }
if ($month < 1) { $month = 12; $year--; }

$first_day_timestamp = strtotime("$year-$month-01");
$days_in_month = date('t', $first_day_timestamp);
$first_day_of_week = date('w', $first_day_timestamp);
$month_name = date('F', $first_day_timestamp);

// Fetch tasks for the selected month
$start_date = "$year-$month-01";
$end_date = "$year-$month-$days_in_month";
$query = "SELECT * FROM tasks WHERE user_id = $user_id AND due_date BETWEEN '$start_date' AND '$end_date' ORDER BY due_date ASC";
$result = mysqli_query($conn, $query);
$tasks = [];
while ($row = mysqli_fetch_assoc($result)) {
    $tasks[$row['due_date']][] = $row;
}

// Navigation Helper
$prev_month = $month - 1;
$prev_year = $year;
$next_month = $month + 1;
$next_year = $year;
if ($prev_month < 1) { $prev_month = 12; $prev_year--; }
if ($next_month > 12) { $next_month = 1; $next_year++; }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dynamic Calendar | GotSchedule</title>
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
                <h2 class="fw-bold mb-1 logo-font">Dynamic Calendar</h2>
                <p class="text-muted fs-7 mb-0">Navigate and track your monthly objectives.</p>
            </div>
            <div class="calendar-nav d-flex align-items-center gap-3">
                <a href="?m=<?= $prev_month ?>&y=<?= $prev_year ?>" class="btn btn-icon border border-white border-opacity-10 rounded-circle text-muted transition-all hover-scale">
                    <i class="bi bi-chevron-left"></i>
                </a>
                <div class="px-4 py-2 bg-white bg-opacity-5 rounded-pill fs-7 fw-bold border border-white border-opacity-5">
                    <?= $month_name ?> <?= $year ?>
                </div>
                <a href="?m=<?= $next_month ?>&y=<?= $next_year ?>" class="btn btn-icon border border-white border-opacity-10 rounded-circle text-muted transition-all hover-scale">
                    <i class="bi bi-chevron-right"></i>
                </a>
            </div>
        </header>

        <div class="glass-panel p-4 reveal-animation">
            <!-- Unified Calendar Grid -->
            <div class="calendar-wrapper">
                <!-- Day labels synchronized with grid -->
                <div class="calendar-grid-header text-center mb-2 fw-bold text-primary fs-8">
                    <div>SUN</div>
                    <div>MON</div>
                    <div>TUE</div>
                    <div>WED</div>
                    <div>THU</div>
                    <div>FRI</div>
                    <div>SAT</div>
                </div>

                <div class="calendar-full">
                    <?php 
                    // Grid placeholders for days before the 1st
                    for($f=0; $f<$first_day_of_week; $f++): ?>
                        <div class="day-box empty"></div>
                    <?php endfor;

                    // Actual days in the month
                    for($day=1; $day<=$days_in_month; $day++): 
                        $current_date_str = sprintf('%04d-%02d-%02d', $year, $month, $day);
                        $is_today = (date('Y-m-d') == $current_date_str);
                        $day_tasks = isset($tasks[$current_date_str]) ? $tasks[$current_date_str] : [];
                    ?>
                        <div class="day-box <?= $is_today ? 'is-today' : '' ?>">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <span class="day-num"><?= $day; ?></span>
                                <?php if(count($day_tasks) > 0): ?>
                                    <span class="day-indicator"></span>
                                <?php endif; ?>
                            </div>
                            <div class="tasks-container hide-scroll">
                                <?php foreach($day_tasks as $task): ?>
                                    <div class="task-mini-pill text-truncate <?= $task['status'] === 'completed' ? 'completed' : ''; ?>" title="<?= htmlspecialchars($task['task_name']); ?>">
                                        <?= htmlspecialchars($task['task_name']); ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endfor; ?>
                </div>
            </div>
        </div>
    </main>

    <style>
        .calendar-grid-header {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            padding: 1rem 0;
            border-bottom: 1px solid var(--border);
        }
        .calendar-full {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 1px;
            background: var(--border); /* Grid lines */
            border: 1px solid var(--border);
            border-radius: var(--border-radius-lg);
            overflow: hidden;
        }
        .day-box {
            background: #ffffff;
            min-height: 120px;
            padding: 1rem;
            transition: var(--transition);
        }
        .day-box.empty { background: #f8fafc; }
        .day-box:hover:not(.empty) { background: #f1f5f9; z-index: 2; }
        .day-box.is-today { background: rgba(79, 70, 229, 0.05); }
        .day-box.is-today .day-num { color: var(--primary); font-weight: 800; }
        .day-indicator { width: 6px; height: 6px; background: var(--primary); border-radius: 50%; }
        
        .task-mini-pill {
            font-size: 0.7rem;
            padding: 2px 6px;
            margin-bottom: 2px;
            border-radius: 4px;
            background: rgba(79, 70, 229, 0.1);
            color: var(--primary);
            font-weight: 600;
        }
        .task-mini-pill.completed {
            text-decoration: line-through;
            opacity: 0.6;
        }
        .hide-scroll::-webkit-scrollbar { display: none; }
        
        @media (max-width: 768px) {
            .day-box { min-height: 80px; padding: 0.5rem; }
            .calendar-grid-header { font-size: 0.65rem; }
            .task-mini-pill { display: none; }
            .day-indicator { display: block !important; }
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
