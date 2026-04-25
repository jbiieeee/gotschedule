<?php
require_once 'includes/config.php';

// Authentication check
if (!isset($_SESSION['user_id'])) {
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
    }
}

// Fetch User Settings
$query = "SELECT * FROM users WHERE id = $user_id";
$res = mysqli_query($conn, $query);
$user = mysqli_fetch_assoc($res);

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_profile'])) {
        $first = mysqli_real_escape_string($conn, $_POST['first_name']);
        $middle = mysqli_real_escape_string($conn, $_POST['middle_name']);
        $last = mysqli_real_escape_string($conn, $_POST['last_name']);
        $start = $_POST['active_hours_start'];
        $end = $_POST['active_hours_end'];
        
        $stmt = $conn->prepare("UPDATE users SET first_name = ?, middle_name = ?, last_name = ?, active_hours_start = ?, active_hours_end = ? WHERE id = ?");
        $stmt->bind_param("sssssi", $first, $middle, $last, $start, $end, $user_id);
        
        if ($stmt->execute()) {
            $_SESSION['user_name'] = trim("$first $last");
            sendResponse('success', 'Profile updated successfully!');
        } else {
            sendResponse('error', 'Update failed.');
        }
        $stmt->close();
    } elseif (isset($_POST['update_password'])) {
        $current = $_POST['current_password'];
        $new_pass = $_POST['new_password'];
        
        // Verify current
        if (password_verify($current, $user['password'])) {
            $hashed = password_hash($new_pass, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->bind_param("si", $hashed, $user_id);
            if ($stmt->execute()) {
                sendResponse('success', 'Password changed successfully!');
            } else {
                sendResponse('error', 'Database error.');
            }
        } else {
            sendResponse('error', 'Incorrect current password.');
        }
    }
}

$current_page = 'settings.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings | GotSchedule</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/css/global.css">
    <link rel="stylesheet" href="assets/css/task.css">
    <style>
        .settings-card { min-height: 500px; }
        .nav-pills-settings .nav-link { 
            color: #64748b; 
            font-weight: 600; 
            padding: 12px 24px; 
            border-radius: 12px;
            transition: all 0.3s ease;
        }
        .nav-pills-settings .nav-link.active { background: var(--primary); color: white; box-shadow: 0 10px 15px -3px rgba(99, 102, 241, 0.3); }
        .nav-pills-settings .nav-link:hover:not(.active) { background: rgba(99, 102, 241, 0.05); color: var(--primary); }
        .google-sync-card {
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            border: 1px solid rgba(0,0,0,0.05);
        }
        .sync-status-pulse {
            width: 10px; height: 10px;
            background: #10b981;
            border-radius: 50%;
            display: inline-block;
            margin-right: 8px;
            box-shadow: 0 0 0 rgba(16, 185, 129, 0.4);
            animation: pulse-green 2s infinite;
        }
        @keyframes pulse-green {
            0% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.4); }
            70% { box-shadow: 0 0 0 10px rgba(16, 185, 129, 0); }
            100% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
        }
    </style>
</head>
<body class="dashboard-body">
    <script src="assets/js/notifications.js"></script>
    <?php include 'includes/sidebar.php'; ?>

    <main class="main-content">
        <header class="mb-5 reveal-animation">
            <div class="d-flex align-items-center gap-3">
                <div class="bg-primary bg-opacity-10 p-3 rounded-4">
                    <i class="bi bi-gear-wide-connected text-primary fs-3"></i>
                </div>
                <div>
                    <h2 class="fw-black mb-1 logo-font">Settings & Preferences</h2>
                    <p class="text-muted fs-7 mb-0">Control your account, security, and integrations.</p>
                </div>
            </div>
        </header>

        <div class="row g-4 justify-content-center">
            <div class="col-lg-3">
                <div class="glass-panel p-3 reveal-animation">
                    <div class="nav flex-column nav-pills nav-pills-settings" id="v-pills-tab" role="tablist">
                        <button class="nav-link active text-start mb-2" id="profile-tab" data-bs-toggle="pill" data-bs-target="#profile-view" type="button" role="tab">
                            <i class="bi bi-person-circle me-3"></i> Profile
                        </button>
                        <button class="nav-link text-start mb-2" id="security-tab" data-bs-toggle="pill" data-bs-target="#security-view" type="button" role="tab">
                            <i class="bi bi-shield-lock me-3"></i> Security
                        </button>
                        <button class="nav-link text-start mb-2" id="sync-tab" data-bs-toggle="pill" data-bs-target="#sync-view" type="button" role="tab">
                            <i class="bi bi-google me-3"></i> Integrations
                        </button>
                        <button class="nav-link text-start text-danger" onclick="window.location.href='logout.php'">
                            <i class="bi bi-box-arrow-right me-3"></i> Sign Out
                        </button>
                    </div>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="glass-panel p-5 settings-card reveal-animation" style="animation-delay: 0.1s;">
                    <div class="tab-content" id="v-pills-tabContent">
                        
                        <!-- Profile Tab -->
                        <div class="tab-pane fade show active" id="profile-view" role="tabpanel">
                            <h4 class="fw-black mb-4">Edit Profile</h4>
                            <form id="profile-form">
                                <div class="mb-4 text-center">
                                    <div class="position-relative d-inline-block">
                                        <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center text-white fw-bold fs-2 shadow-lg" style="width: 100px; height: 100px;">
                                            <?= strtoupper(substr($user_name, 0, 1)) ?>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-white rounded-circle position-absolute bottom-0 end-0 border shadow-sm">
                                            <i class="bi bi-camera"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="row g-3 mb-4">
                                    <div class="col-md-4">
                                        <label class="form-label fs-8 fw-bold text-muted text-uppercase">First Name</label>
                                        <input type="text" name="first_name" class="form-control" value="<?= htmlspecialchars($user['first_name'] ?? '') ?>" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fs-8 fw-bold text-muted text-uppercase">Middle Name</label>
                                        <input type="text" name="middle_name" class="form-control" value="<?= htmlspecialchars($user['middle_name'] ?? '') ?>">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fs-8 fw-bold text-muted text-uppercase">Last Name</label>
                                        <input type="text" name="last_name" class="form-control" value="<?= htmlspecialchars($user['last_name'] ?? '') ?>" required>
                                    </div>
                                </div>
                                <div class="row g-3 mb-4">
                                    <div class="col-md-12">
                                        <label class="form-label fs-8 fw-bold text-muted text-uppercase">Email Address</label>
                                        <input type="email" class="form-control bg-light opacity-75" value="<?= htmlspecialchars($user['email'] ?? '') ?>" disabled title="Email cannot be changed">
                                    </div>
                                </div>
                                <div class="mb-5">
                                    <label class="form-label fs-8 fw-bold text-muted text-uppercase d-block mb-3">Focus Window (Active Hours)</label>
                                    <div class="row g-3">
                                        <div class="col-6">
                                            <div class="input-group">
                                                <span class="input-group-text bg-transparent border-end-0 text-muted fs-8">START</span>
                                                <input type="time" name="active_hours_start" class="form-control border-start-0" value="<?= $user['active_hours_start'] ?>">
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="input-group">
                                                <span class="input-group-text bg-transparent border-end-0 text-muted fs-8">END</span>
                                                <input type="time" name="active_hours_end" class="form-control border-start-0" value="<?= $user['active_hours_end'] ?>">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <button type="submit" name="update_profile" class="btn btn-primary rounded-pill px-5 fw-bold shadow-sm">Update Profile</button>
                            </form>
                        </div>

                        <!-- Security Tab -->
                        <div class="tab-pane fade" id="security-view" role="tabpanel">
                            <h4 class="fw-black mb-4">Security Settings</h4>
                            <p class="text-muted fs-7 mb-5">Change your password frequently to keep your account secure.</p>
                            
                            <form id="password-form">
                                <div class="mb-4">
                                    <label class="form-label fs-8 fw-bold text-muted text-uppercase">Current Password</label>
                                    <input type="password" name="current_password" class="form-control" placeholder="••••••••" required>
                                </div>
                                <div class="mb-4">
                                    <label class="form-label fs-8 fw-bold text-muted text-uppercase">New Password</label>
                                    <input type="password" name="new_password" class="form-control" placeholder="••••••••" required>
                                </div>
                                <div class="mb-5">
                                    <label class="form-label fs-8 fw-bold text-muted text-uppercase">Confirm New Password</label>
                                    <input type="password" name="confirm_password" class="form-control" placeholder="••••••••" required>
                                </div>
                                <button type="submit" name="update_password" class="btn btn-dark rounded-pill px-5 fw-bold shadow-sm hover-scale">Reset Password</button>
                            </form>
                        </div>

                        <!-- Integrations Tab -->
                        <div class="tab-pane fade" id="sync-view" role="tabpanel">
                            <h4 class="fw-black mb-4">Calendar Integrations</h4>
                            <div class="google-sync-card p-4 rounded-4 text-center border-dark border-opacity-10 mb-4">
                                <i class="bi bi-google text-danger fs-1 mb-3 d-block"></i>
                                <h5 class="fw-bold">Google Calendar</h5>
                                <p class="text-muted fs-7 mb-4">Sync your GotSchedule tasks automatically with your Google Calendar in real-time.</p>
                                
                                <div id="sync-status" class="sync-status-container mb-4">
                                    <div class="badge bg-light text-muted border px-3 py-2 rounded-pill fs-8">
                                        <i class="bi bi-link-45deg me-2"></i> Not Connected
                                    </div>
                                </div>

                                <button id="link-google-btn" class="btn btn-white border rounded-pill px-5 py-2 fw-black shadow-sm transition-all hover-scale">
                                    <img src="https://www.gstatic.com/images/branding/product/1x/gsa_512dp.png" width="20" class="me-2">
                                    Link your Google Account
                                </button>
                            </div>

                            <div class="achievement-card p-4 rounded-4 bg-primary bg-opacity-5 border border-primary border-opacity-10">
                                <div class="d-flex gap-3">
                                    <i class="bi bi-info-circle-fill text-primary fs-4"></i>
                                    <div>
                                        <h6 class="fw-bold mb-1">Real-time Synchronization</h6>
                                        <p class="fs-8 text-muted mb-0">Once linked, any task added to GotSchedule will appear on your linked Google account instantly. We use secure OAuth2 to ensure your data stays private.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Real-time Settings Logic
        document.addEventListener('DOMContentLoaded', () => {
            // Profile Update
            const profileForm = document.getElementById('profile-form');
            profileForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                const formData = new FormData(profileForm);
                formData.append('update_profile', '1');
                formData.append('ajax', '1');

                const btn = profileForm.querySelector('button[type="submit"]');
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Updating...';

                try {
                    const response = await fetch('settings.php', { method: 'POST', body: formData });
                    const result = await response.json();
                    if(result.status === 'success') {
                        window.notifier.success('Success', result.message);
                        setTimeout(() => window.location.reload(), 1000);
                    } else {
                        window.notifier.error('Error', result.message);
                    }
                } catch(error) { window.notifier.error('Oops', 'Network error.'); }
                btn.disabled = false;
                btn.innerHTML = 'Update Profile';
            });

            // Password Reset
            const passForm = document.getElementById('password-form');
            passForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                if(passForm.new_password.value !== passForm.confirm_password.value) {
                    window.notifier.error('Mismatch', 'Passwords do not match.');
                    return;
                }

                const formData = new FormData(passForm);
                formData.append('update_password', '1');
                formData.append('ajax', '1');

                try {
                    const response = await fetch('settings.php', { method: 'POST', body: formData });
                    const result = await response.json();
                    if(result.status === 'success') {
                        window.notifier.success('Secure', result.message);
                        passForm.reset();
                    } else {
                        window.notifier.error('Error', result.message);
                    }
                } catch(error) { window.notifier.error('Oops', 'Network error.'); }
            });

            // Mock Google Calendar Linking "Real-time"
            const linkBtn = document.getElementById('link-google-btn');
            linkBtn.addEventListener('click', () => {
                window.notifier.info('Connecting...', 'Redirecting to Google OAuth...');
                linkBtn.disabled = true;
                
                setTimeout(() => {
                    const status = document.getElementById('sync-status');
                    status.innerHTML = `
                        <div class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-10 px-3 py-2 rounded-pill fs-8">
                            <span class="sync-status-pulse"></span>
                            Linked as ${'<?= $user["email"] ?>'}
                        </div>
                    `;
                    linkBtn.innerHTML = '<i class="bi bi-check2-all me-2"></i> Successfully Linked';
                    linkBtn.classList.replace('btn-white', 'btn-success');
                    window.notifier.success('Connected', 'Google Calendar is now synced in real-time!');
                }, 2000);
            });
        });
    </script>
</body>
</html>
