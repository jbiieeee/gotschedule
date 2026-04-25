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
$view = isset($_GET['v']) ? $_GET['v'] : 'month';
$day_offset = isset($_GET['d']) ? (int)$_GET['d'] : 0;
$month = isset($_GET['m']) ? (int)$_GET['m'] : (int)date('m');
$year = isset($_GET['y']) ? (int)$_GET['y'] : (int)date('Y');

// Handle Month Navigation
if ($month > 12) { $month = 1; $year++; }
if ($month < 1) { $month = 12; $year--; }

// Current Reference Date
$ref_date = "$year-$month-01";
if ($view == 'week' || $view == 'day') {
    $ref_date = date('Y-m-d', strtotime(date('Y-m-d') . " +$day_offset days"));
}

$first_day_timestamp = strtotime("$year-$month-01");
$days_in_month = date('t', $first_day_timestamp);
$first_day_of_week = date('w', $first_day_timestamp);
$month_name = date('F', $first_day_timestamp);

// Fetch tasks based on view
$start_date = "$year-$month-01";
$end_date = "$year-$month-$days_in_month";

if ($view == 'week') {
    $start_date = date('Y-m-d', strtotime('sunday this week', strtotime($ref_date)));
    $end_date = date('Y-m-d', strtotime('saturday this week', strtotime($ref_date)));
} elseif ($view == 'day') {
    $start_date = $ref_date;
    $end_date = $ref_date;
}

$query = "SELECT * FROM tasks WHERE user_id = $user_id AND (due_date BETWEEN '$start_date' AND '$end_date' OR recurrence != 'none') AND deleted_at IS NULL ORDER BY due_date ASC, task_time ASC";
$result = mysqli_query($conn, $query);
$tasks = [];

while ($row = mysqli_fetch_assoc($result)) {
    if ($row['recurrence'] === 'none') {
        if ($row['due_date'] >= $start_date && $row['due_date'] <= $end_date) {
            $tasks[$row['due_date']][] = $row;
        }
    } else {
        // Recurrence Engine - Dynamic Projection
        $current = new DateTime($row['due_date']);
        $end_limit = new DateTime($end_date);
        $interval = '';
        
        switch ($row['recurrence']) {
            case 'daily': $interval = 'P1D'; break;
            case 'weekly': $interval = 'P1W'; break;
            case 'monthly': $interval = 'P1M'; break;
        }
        
        if ($interval) {
            $period = new DatePeriod($current, new DateInterval($interval), $end_limit->modify('+1 day'));
            foreach ($period as $date) {
                $date_str = $date->format('Y-m-d');
                if ($date_str >= $start_date && $date_str <= $end_date) {
                    $tasks[$date_str][] = $row;
                }
            }
        }
    }
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
                <h2 class="fw-black mb-1 logo-font">Dynamic Calendar</h2>
                <div class="btn-group rounded-pill p-1 bg-surface shadow-sm border border-dark border-opacity-5 mt-2">
                    <a href="?v=month" class="btn btn-sm px-4 rounded-pill <?= $view == 'month' ? 'btn-primary' : 'text-muted' ?> fw-bold">Month</a>
                    <a href="?v=week" class="btn btn-sm px-4 rounded-pill <?= $view == 'week' ? 'btn-primary' : 'text-muted' ?> fw-bold">Week</a>
                    <a href="?v=day" class="btn btn-sm px-4 rounded-pill <?= $view == 'day' ? 'btn-primary' : 'text-muted' ?> fw-bold">Day</a>
                </div>
            </div>
            <div class="calendar-nav d-flex align-items-center gap-3">
                <a href="calendar.php" class="btn btn-icon bg-surface border border-dark border-opacity-10 rounded-circle text-muted transition-all hover-scale" title="Today">
                    <i class="bi bi-calendar-event"></i>
                </a>
                <div class="d-flex align-items-center gap-2">
                    <a href="?v=<?= $view ?>&m=<?= $prev_month ?>&y=<?= $prev_year ?>&d=<?= $day_offset - ($view == 'week' ? 7 : ($view == 'day' ? 1 : 0)) ?>" class="btn btn-icon bg-surface border border-dark border-opacity-10 rounded-circle text-muted transition-all hover-scale">
                        <i class="bi bi-chevron-left"></i>
                    </a>
                    <div class="px-4 py-2 bg-surface rounded-pill fs-7 fw-bold border border-dark border-opacity-5">
                        <?php 
                        if ($view == 'month') echo $month_name . " " . $year;
                        elseif ($view == 'week') echo "Week of " . date('M d', strtotime($start_date));
                        else echo date('M d, Y', strtotime($start_date));
                        ?>
                    </div>
                    <a href="?v=<?= $view ?>&m=<?= $next_month ?>&y=<?= $next_year ?>&d=<?= $day_offset + ($view == 'week' ? 7 : ($view == 'day' ? 1 : 0)) ?>" class="btn btn-icon border border-dark border-opacity-10 rounded-circle text-muted transition-all hover-scale">
                        <i class="bi bi-chevron-right"></i>
                    </a>
                </div>
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

                <div class="calendar-full view-<?= $view ?>">
                    <?php 
                    if ($view == 'month'):
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
                            <div class="day-box <?= $is_today ? 'is-today' : '' ?>" onclick="if(typeof openTaskModal === 'function') openTaskModal('<?= $current_date_str ?>')">
                                <div class="d-flex justify-content-between align-items-start mb-1">
                                    <span class="day-num"><?= $day; ?></span>
                                    <?php if(count($day_tasks) > 0): ?>
                                        <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill p-1" style="font-size: 0.6rem;"><?= count($day_tasks) ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="tasks-container hide-scroll" style="height: 60px; overflow-y: auto;">
                                    <?php foreach($day_tasks as $task): ?>
                                        <div class="task-mini-pill text-truncate shadow-sm <?= $task['status'] === 'completed' ? 'completed opacity-50' : ''; ?>" 
                                             style="font-size: 0.65rem; padding: 2px 6px; margin-bottom: 2px"
                                             title="<?= htmlspecialchars($task['task_name']); ?>">
                                            <?= htmlspecialchars($task['task_name']); ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <div class="add-indicator d-none fs-9 text-muted text-center">+ Add Task</div>
                            </div>
                        <?php endfor; ?>
                    <?php else: 
                        // Week or Day view
                        $current = new DateTime($start_date);
                        $end = new DateTime($end_date);
                        $end->modify('+1 day');
                        $interval = new DateInterval('P1D');
                        $period = new DatePeriod($current, $interval, $end);

                        foreach ($period as $date):
                            $date_str = $date->format('Y-m-d');
                            $is_today = (date('Y-m-d') == $date_str);
                            $day_tasks = isset($tasks[$date_str]) ? $tasks[$date_str] : [];
                        ?>
                            <div class="day-box <?= $is_today ? 'is-today' : '' ?>">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="day-num fs-6"><?= $date->format('D, M d') ?></span>
                                </div>
                                <div class="tasks-container">
                                    <?php foreach($day_tasks as $task): ?>
                                        <div class="glass-panel p-3 mb-2 border-dark border-opacity-5">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <span class="fs-8 fw-bold text-primary"><?= date('h:i A', strtotime($task['task_time'])) ?></span>
                                                <span class="badge bg-primary bg-opacity-10 text-primary fs-9 rounded-pill"><?= $task['priority'] ?></span>
                                            </div>
                                            <div class="fw-bold fs-7 text-dark"><?= htmlspecialchars($task['task_name']) ?></div>
                                        </div>
                                    <?php endforeach; ?>
                                    <?php if(empty($day_tasks)): ?>
                                        <div class="text-center py-4 text-muted fs-8 opacity-50">No tasks scheduled</div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <?php include 'task_modal.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="notifications.js"></script>
    <script src="task.js"></script>
</body>
</html>
