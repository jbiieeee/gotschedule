<?php
require_once 'includes/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: main.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];
$current_page = 'collaboration.php';

// Fetch Shared Notes (By user or shared with them via key)
$shared_query = "SELECT * FROM notes WHERE (user_id = $user_id AND is_shared = 1) OR (is_shared = 1 AND collab_key IN (SELECT collab_key FROM notes WHERE 1=0))"; // Placeholder for incoming shares
$res = mysqli_query($conn, $shared_query);

// Logic to join a shared note
if (isset($_POST['join_collab'])) {
    $key = mysqli_real_escape_string($conn, $_POST['collab_key']);
    $check = mysqli_query($conn, "SELECT * FROM notes WHERE collab_key = '$key' AND is_shared = 1");
    if (mysqli_num_rows($check) > 0) {
        $note = mysqli_fetch_assoc($check);
        $success = "Successfully linked to: " . $note['title'];
    } else {
        $error = "Invalid or inactive Collaboration Key.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Collaboration Hub | GotSchedule</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/css/global.css">
    <link rel="stylesheet" href="assets/css/task.css">
</head>
<body class="dashboard-body">
    <script src="assets/js/notifications.js"></script>
    <?php include 'includes/sidebar.php'; ?>

    <main class="main-content">
        <header class="mb-5 reveal-animation">
            <div class="d-flex align-items-center gap-3">
                <div class="bg-success bg-opacity-10 p-3 rounded-4">
                    <i class="bi bi-people-fill text-success fs-3"></i>
                </div>
                <div>
                    <h2 class="fw-black mb-1 logo-font">Collaboration Hub</h2>
                    <p class="text-muted fs-7 mb-0">Share notes and coordinate with your team in real-time.</p>
                </div>
            </div>
        </header>

        <div class="row g-4">
            <div class="col-lg-4">
                <div class="glass-panel p-4 reveal-animation">
                    <h5 class="fw-bold mb-4">Join Collaboration</h5>
                    <?php if(isset($success)) echo "<div class='alert alert-success fs-8'>$success</div>"; ?>
                    <?php if(isset($error)) echo "<div class='alert alert-danger fs-8'>$error</div>"; ?>
                    
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label fs-8 fw-bold text-muted">Enter Collab Key</label>
                            <input type="text" name="collab_key" class="form-control" placeholder="e.g. 5f3a9e..." required>
                        </div>
                        <button type="submit" name="join_collab" class="btn btn-primary w-100 rounded-pill fw-bold py-2 shadow-sm">Sync Collaborative Note</button>
                    </form>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="glass-panel p-4 reveal-animation" style="animation-delay: 0.1s;">
                    <h5 class="fw-bold mb-4">Shared Items</h5>
                    <div class="table-responsive">
                        <table class="table table-hover border-0">
                            <thead class="fs-8 text-muted text-uppercase fw-black">
                                <tr>
                                    <th>Item Name</th>
                                    <th>Status</th>
                                    <th>Collab Key</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody class="fs-7 align-middle">
                                <?php if(mysqli_num_rows($res) > 0): ?>
                                    <?php while($row = mysqli_fetch_assoc($res)): ?>
                                        <tr>
                                            <td class="fw-bold"><?= htmlspecialchars($row['title']) ?></td>
                                            <td><span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3">Live</span></td>
                                            <td><code class="bg-light p-1 rounded"><?= $row['collab_key'] ?></code></td>
                                            <td>
                                                <button class="btn btn-icon bg-light rounded-circle border-0"><i class="bi bi-eye"></i></button>
                                                <button class="btn btn-icon bg-light rounded-circle border-0"><i class="bi bi-pencil"></i></button>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr><td colspan="4" class="text-center py-5 text-muted opacity-50">No shared items found.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
