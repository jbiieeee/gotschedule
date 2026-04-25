<?php
require_once 'includes/config.php';

// Role-based Authentication check
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? 'student') !== 'admin') {
    header("Location: task.php"); // Non-admins are redirected to their dashboard
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];

// System Analytics Queries
$total_users_query = "SELECT COUNT(*) as total FROM users";
$total_users = mysqli_fetch_assoc(mysqli_query($conn, $total_users_query))['total'];

$total_tasks_query = "SELECT COUNT(*) as total FROM tasks";
$total_tasks = mysqli_fetch_assoc(mysqli_query($conn, $total_tasks_query))['total'];

$completed_tasks_query = "SELECT COUNT(*) as completed FROM tasks WHERE status = 'completed'";
$completed_tasks = mysqli_fetch_assoc(mysqli_query($conn, $completed_tasks_query))['completed'];

$completion_rate = $total_tasks > 0 ? round(($completed_tasks / $total_tasks) * 100) : 0;

// User roles distribution
$roles_query = "SELECT role, COUNT(*) as count FROM users GROUP BY role";
$roles_result = mysqli_query($conn, $roles_query);

// Recent Users
$recent_users_query = "SELECT first_name, last_name, email, role, created_at FROM users ORDER BY created_at DESC LIMIT 5";
$recent_users = mysqli_query($conn, $recent_users_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Command | GotSchedule Analytics</title>
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
                <h2 class="fw-bold mb-1 logo-font">System Intelligence</h2>
                <p class="text-muted fs-7 mb-0">Overview of global platform performance and user engagement.</p>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-icon border border-white border-opacity-10 rounded-circle text-muted"><i class="bi bi-download"></i></button>
                <button class="btn btn-icon border border-white border-opacity-10 rounded-circle text-muted"><i class="bi bi-gear-fill"></i></button>
            </div>
        </header>

        <!-- Stats Overview -->
        <div class="row g-4 mb-5 reveal-animation">
            <div class="col-md-3">
                <div class="glass-panel p-4 text-center border-primary border-opacity-10">
                    <div class="fs-8 text-muted mb-2 text-uppercase fw-bold">Global Users</div>
                    <div class="display-5 fw-bold text-primary mb-1"><?= $total_users ?></div>
                    <div class="fs-9 text-success"><i class="bi bi-graph-up-arrow me-1"></i> +12% this month</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="glass-panel p-4 text-center border-secondary border-opacity-10">
                    <div class="fs-8 text-muted mb-2 text-uppercase fw-bold">Active Tasks</div>
                    <div class="display-5 fw-bold text-secondary mb-1"><?= $total_tasks ?></div>
                    <div class="fs-9 text-muted">Across all user accounts</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="glass-panel p-4 text-center border-accent border-opacity-10">
                    <div class="fs-8 text-muted mb-2 text-uppercase fw-bold">Completion Rate</div>
                    <div class="display-5 fw-bold text-accent mb-1"><?= $completion_rate ?>%</div>
                    <div class="progress mt-2 mx-auto" style="height: 6px; width: 80%; background: rgba(255,255,255,0.05);">
                        <div class="progress-bar bg-accent" style="width: <?= $completion_rate ?>%"></div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="glass-panel p-4 text-center border-info border-opacity-10">
                    <div class="fs-8 text-muted mb-2 text-uppercase fw-bold">System Status</div>
                    <div class="display-5 fw-bold text-success mb-1">HEALTHY</div>
                    <div class="fs-9 text-muted">Latency: 24ms</div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- User Distribution -->
            <div class="col-lg-5">
                <div class="glass-panel p-4 h-100 reveal-animation" style="animation-delay: 0.1s;">
                    <h5 class="fw-bold mb-4">Role Distribution</h5>
                    <div class="role-list">
                        <?php while($role = mysqli_fetch_assoc($roles_result)): ?>
                            <div class="d-flex justify-content-between align-items-center mb-3 p-3 bg-white bg-opacity-5 rounded-4 transition-all hover-scale">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="avatar-xs bg-<?= $role['role'] === 'admin' ? 'accent' : ($role['role'] === 'adviser' ? 'primary' : 'secondary') ?> rounded-circle"></div>
                                    <span class="fw-semibold text-uppercase fs-8"><?= $role['role'] ?>s</span>
                                </div>
                                <span class="badge bg-white bg-opacity-10 rounded-pill px-3"><?= $role['count'] ?> users</span>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="col-lg-7">
                <div class="glass-panel p-4 h-100 reveal-animation" style="animation-delay: 0.2s;">
                    <h5 class="fw-bold mb-4">Recent Registrations</h5>
                    <div class="table-responsive">
                        <table class="table table-borderless text-white opacity-90">
                            <thead>
                                <tr class="fs-8 text-muted border-bottom border-white border-opacity-5">
                                    <th>USER</th>
                                    <th>ROLE</th>
                                    <th>JOIN DATE</th>
                                    <th>ACTION</th>
                                </tr>
                            </thead>
                            <tbody class="fs-7">
                                <?php while($u = mysqli_fetch_assoc($recent_users)): ?>
                                    <tr class="align-middle border-bottom border-white border-opacity-5 transition-all hover-bg-white">
                                        <td>
                                            <div class="fw-bold"><?= htmlspecialchars($u['first_name'] . ' ' . $u['last_name']) ?></div>
                                            <div class="fs-8 opacity-50"><?= htmlspecialchars($u['email']) ?></div>
                                        </td>
                                        <td><span class="badge bg-white bg-opacity-5 rounded-pill px-3 text-lowercase"><?= $u['role'] ?></span></td>
                                        <td><?= date('M d, Y', strtotime($u['created_at'])) ?></td>
                                        <td><button class="btn btn-sm text-primary p-0">Manage</button></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <style>
        .fs-9 { font-size: 0.65rem; }
        .avatar-xs { width: 10px; height: 10px; }
        .hover-bg-white:hover { background: rgba(255,255,255,0.03); }
        .table thead th { font-weight: 800; padding-bottom: 1.5rem; }
        .table tbody td { padding: 1.25rem 0.5rem; }
    </style>
</body>
</html>
