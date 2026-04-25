<?php
require_once 'config.php';

$message = '';
$messageType = '';
$is_ajax = isset($_POST['ajax']) || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest');

if (isset($_POST['register'])) {
    // Sanitize inputs
    $firstName = mysqli_real_escape_string($conn, $_POST['first_name']);
    $middleName = mysqli_real_escape_string($conn, $_POST['middle_name']);
    $lastName = mysqli_real_escape_string($conn, $_POST['last_name']);
    $contactNumber = mysqli_real_escape_string($conn, $_POST['contact_number']);
    $country = mysqli_real_escape_string($conn, $_POST['country']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm-password'];

    $error = '';
    
    // Validation
    if ($password !== $confirmPassword) {
        $error = "Passwords do not match.";
    } else {
        // Check if email already exists
        $checkEmail = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $checkEmail->bind_param("s", $email);
        $checkEmail->execute();
        $checkEmail->store_result();
        
        if ($checkEmail->num_rows > 0) {
            $error = "An account with this email already exists.";
        } else {
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $conn->prepare("INSERT INTO users (first_name, middle_name, last_name, contact_number, country, email, password) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssss", $firstName, $middleName, $lastName, $contactNumber, $country, $email, $hashedPassword);

            if ($stmt->execute()) {
                if ($is_ajax) {
                    header('Content-Type: application/json');
                    echo json_encode(['status' => 'success', 'message' => 'Registration successful! Redirecting to login...', 'redirect' => 'main.php']);
                    exit();
                }
                $message = "Registration successful! You can now <a href='main.php' class='alert-link'>log in</a>.";
                $messageType = "success";
            } else {
                $error = "Database error: " . $stmt->error;
            }
            $stmt->close();
        }
        $checkEmail->close();
    }

    if ($error && $is_ajax) {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => $error]);
        exit();
    }
    
    if ($error) {
        $message = $error;
        $messageType = "danger";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account | GotSchedule</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="global.css">
    <link rel="stylesheet" href="sign.css">
</head>
<body class="d-flex flex-column min-vh-100">

    <header class="app-header">
        <nav class="container d-flex justify-content-between align-items-center py-3">
            <div class="logo">
                <a href="main.php" class="text-decoration-none">
                    <h1 class="logo-font mb-0 fs-3">GotSchedule</h1>
                </a>
            </div>
            <div class="nav-links d-none d-md-flex gap-3">
                <a href="main.php" class="nav-link-modern">Home</a>
                <a href="about.html" class="nav-link-modern">About</a>
                <a href="main.php" class="nav-link-modern">Login</a>
            </div>
        </nav>
    </header>

    <main class="sign-up-container">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10 col-xl-8">
                    <div class="glass-panel auth-card reveal-animation">
                        <div class="text-center mb-5">
                            <h2 class="auth-title logo-font">Create Your Account</h2>
                            <p class="auth-subtitle">Join thousands of users organizing their lives with GotSchedule.</p>
                        </div>

                        <?php if ($message): ?>
                            <div class="alert alert-<?= $messageType; ?> alert-dismissible fade show border-0 shadow-sm mb-4" role="alert" style="background: rgba(var(--<?= $messageType == 'danger' ? 'accent' : 'primary' ?>-rgb), 0.1); color: var(--text-primary);">
                                <?= $message; ?>
                                <button type="button" class="btn-close shadow-none" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>

                        <form id="signup-form" method="POST" action="sign.php" class="row g-4">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label">First Name</label>
                                    <input type="text" class="form-control" name="first_name" placeholder="John" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label">Middle Name</label>
                                    <input type="text" class="form-control" name="middle_name" placeholder="Quincy">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label">Last Name</label>
                                    <input type="text" class="form-control" name="last_name" placeholder="Doe" required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Contact Number</label>
                                    <input type="text" class="form-control" name="contact_number" placeholder="09123456789" required pattern="\d{11}" maxlength="11">
                                    <div class="form-text fs-8 text-muted mt-2">Enter 11-digit mobile number</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Country</label>
                                    <select class="form-select" name="country" required>
                                        <option value="" selected disabled>Choose your country</option>
                                        <option value="US">United States</option>
                                        <option value="PH">Philippines</option>
                                        <option value="GB">United Kingdom</option>
                                        <option value="SG">Singapore</option>
                                        <option value="CA">Canada</option>
                                        <option value="AU">Australia</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="form-group">
                                    <label class="form-label">Email Address</label>
                                    <input type="email" class="form-control" name="email" placeholder="john.doe@example.com" required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Password</label>
                                    <div class="position-relative">
                                        <input type="password" id="password" class="form-control pe-5" name="password" placeholder="••••••••" required minlength="8">
                                        <button type="button" class="btn-password-toggle" data-target="password">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Confirm Password</label>
                                    <div class="position-relative">
                                        <input type="password" id="confirm-password" class="form-control pe-5" name="confirm-password" placeholder="••••••••" required>
                                        <button type="button" class="btn-password-toggle" data-target="confirm-password">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </div>
                                    <div id="pw-error" class="text-danger fs-8 mt-2 hidden">Passwords do not match</div>
                                </div>
                            </div>

                            <div class="col-12 mt-2">
                                <div class="form-check custom-check">
                                    <input class="form-check-input" type="checkbox" id="terms" required>
                                    <label class="form-check-label fs-7 text-muted" for="terms">
                                        I agree to the <a href="terms.html" class="text-primary text-decoration-none fw-semibold">Terms and Conditions</a> and Privacy Policy.
                                    </label>
                                </div>
                            </div>

                            <div class="col-12 mt-5">
                                <button type="submit" name="register" class="btn-modern w-100 py-3 fs-5">
                                    Create My Account
                                </button>
                            </div>
                        </form>

                        <div class="text-center mt-5 pt-3 border-top border-secondary border-opacity-10">
                            <p class="text-muted mb-0">
                                Already have an account? <a href="main.php" class="text-primary text-decoration-none fw-bold">Sign In</a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <footer class="py-4 border-top border-secondary border-opacity-10 mt-auto">
        <div class="container text-center text-muted fs-7">
            &copy; 2026 GotSchedule. Empowering productivity worldwide.
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="notifications.js"></script>
    <script src="sign.js"></script>
</body>
</html>

