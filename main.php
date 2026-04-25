<?php
require_once 'includes/config.php';

$is_ajax = isset($_POST['ajax']) || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest');

// Handle login logic
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $email = mysqli_real_escape_string($conn, $_POST['login-email']);
    $password = $_POST['login-password'];

    $stmt = $conn->prepare("SELECT id, first_name, last_name, password, role, avatar_url FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    $error = '';
    if ($user = $result->fetch_assoc()) {
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['avatar'] = $user['avatar_url'];
            
            if ($is_ajax) {
                header('Content-Type: application/json');
                echo json_encode(['status' => 'success', 'message' => 'Welcome back, ' . $user['first_name'] . '!', 'redirect' => 'task.php']);
                exit();
            }
            header("Location: task.php");
            exit();
        } else {
            $error = "Incorrect password. Please try again.";
        }
    } else {
        $error = "No account found with that email.";
    }
    
    if ($is_ajax) {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => $error]);
        exit();
    }
    $loginError = $error;
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GotSchedule | Master Your Moment</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/css/global.css">
    <link rel="stylesheet" href="assets/css/main.css">
</head>
<body class="landing-body">

    <!-- Premium Navigation -->
    <header class="app-header position-fixed top-0 w-100 shadow-sm animate-fade-in" style="z-index: 1060; background: rgba(255,255,255,0.8); backdrop-filter: blur(10px);">
        <nav class="container d-flex justify-content-between align-items-center py-3">
            <div class="logo">
                <h1 class="logo-font mb-0 fs-3">GotSchedule</h1>
            </div>
            <div class="nav-links d-none d-md-flex gap-4">
                <a href="main.php" class="nav-link-modern active">Home</a>
                <a href="about.html" class="nav-link-modern">About Us</a>
                <a href="sign.php" class="btn btn-primary rounded-pill px-4 text-white hover-up">Join Free</a>
            </div>
        </nav>
    </header>

    <main class="landing-content" style="padding-top: 100px;">
        <!-- Hero Section -->
        <section class="hero-section py-5 overflow-hidden">
            <div class="container py-5">
                <div class="row align-items-center g-5">
                    <div class="col-lg-6 reveal-animation">
                        <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 border border-primary border-opacity-10 rounded-pill mb-4 fw-bold fs-8">SMART SCHEDULING</span>
                        <h1 class="display-3 fw-black mb-4 logo-font lh-1">Chaos Ends Here.<br><span class="text-primary italic">Clarity </span>Begins.</h1>
                        <p class="lead text-dark opacity-75 mb-5 fs-4">GotSchedule isn't just a calendar—it's your dedicated engine for focus, productivity, and peace of mind.</p>
                        
                        <div class="d-flex flex-wrap gap-3">
                            <a href="sign.php" class="btn btn-primary btn-lg rounded-pill px-5 py-3 shadow-lg hover-up">Get Started for Free</a>
                            <a href="#login-anchor" class="btn btn-outline-dark btn-lg rounded-pill px-5 py-3 hover-up">Member Login</a>
                        </div>
                    </div>
                    <div class="col-lg-6 reveal-animation" style="animation-delay: 0.2s;">
                        <div class="hero-image-wrapper p-2 bg-white rounded-5 shadow-2xl border border-dark border-opacity-10">
                            <img src="assets/img/hero_dashboard.png" alt="GotSchedule Dashboard Preview" class="img-fluid rounded-4 shadow-sm w-100 transition-all hover-scale">
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Feature Pillars - Explanatory Focus -->
        <section class="py-5 bg-white border-top border-bottom border-dark border-opacity-5">
            <div class="container py-5">
                <div class="text-center mb-5 pb-3 reveal-animation">
                    <h2 class="fw-black display-5 mb-3 text-dark">One Tools. Three Superpowers.</h2>
                    <p class="text-muted fs-5">Everything you need to navigate your day with confidence.</p>
                </div>
                
                <div class="row g-4 reveal-animation" style="animation-delay: 0.3s;">
                    <div class="col-md-4">
                        <div class="p-5 rounded-5 border border-dark border-opacity-10 h-100 hover-bg-primary-subtle transition-all">
                            <div class="bg-primary bg-opacity-10 text-primary rounded-4 d-flex align-items-center justify-content-center shadow-sm mb-4" style="width: 60px; height: 60px;">
                                <i class="bi bi-magic fs-2"></i>
                            </div>
                            <h4 class="fw-bold mb-3 text-dark">Magic Scheduler</h4>
                            <p class="text-muted fs-7">Don't just list tasks—build a flow. Our scheduler automatically transforms your to-dos into a high-performance timeline.</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-5 rounded-5 border border-dark border-opacity-10 h-100 hover-bg-success-subtle transition-all">
                            <div class="bg-success bg-opacity-10 text-success rounded-4 d-flex align-items-center justify-content-center shadow-sm mb-4" style="width: 60px; height: 60px;">
                                <i class="bi bi-journal-text fs-2"></i>
                            </div>
                            <h4 class="fw-bold mb-3 text-dark">Unified Notes</h4>
                            <p class="text-muted fs-7">A dedicated space for every spark of genius. Connect your thoughts directly to your daily goals without the clutter.</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-5 rounded-5 border border-dark border-opacity-10 h-100 hover-bg-info-subtle transition-all">
                            <div class="bg-info bg-opacity-10 text-info rounded-4 d-flex align-items-center justify-content-center shadow-sm mb-4" style="width: 60px; height: 60px;">
                                <i class="bi bi-grid-3x3-gap fs-2"></i>
                            </div>
                            <h4 class="fw-bold mb-3 text-dark">Visual Grid</h4>
                            <p class="text-dark opacity-75 fs-7">Maintain the big picture. Our integrated calendar and task grid ensure you're always aligned with your long-term milestones.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Dynamic Login Section -->
        <section id="login-anchor" class="py-5 reveal-animation" style="animation-delay: 0.4s;">
            <div class="container py-5">
                <div class="row justify-content-center">
                    <div class="col-md-6 col-lg-5 col-xl-4">
                        <div class="glass-panel shadow-2xl p-5 border-dark border-opacity-10">
                            <div class="text-center mb-5">
                                <h2 class="fw-black mb-2 text-dark">Welcome Back</h2>
                                <p class="text-muted">Return to your command center.</p>
                            </div>

                            <form id="login-form" method="POST" action="main.php">
                                <div class="mb-4">
                                    <label class="form-label text-dark">Email Address</label>
                                    <input type="email" class="form-control" name="login-email" placeholder="name@example.com" required>
                                </div>
                                <div class="mb-5">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <label class="form-label text-dark mb-0">Password</label>
                                        <a href="#" class="fs-8 text-primary text-decoration-none fw-bold">Forgot?</a>
                                    </div>
                                    <div class="position-relative">
                                        <input type="password" id="login-password" class="form-control pe-5" name="login-password" placeholder="••••••••" required>
                                        <button type="button" class="btn-password-toggle" data-target="login-password">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </div>
                                </div>
                                
                                <?php if (isset($loginError)): ?>
                                    <div class="alert alert-danger py-2 mb-4 fs-7 border-0 shadow-sm rounded-4" style="background: rgba(244, 63, 94, 0.1); color: #be123c;">
                                        <i class="bi bi-exclamation-circle me-2"></i><?= $loginError; ?>
                                    </div>
                                <?php endif; ?>

                                <button type="submit" name="login" class="btn btn-primary w-100 py-3 rounded-pill fs-5 shadow-lg mb-4 hover-up">
                                    Sign In
                                </button>
                            </form>

                            <div class="text-center pt-3 border-top border-dark border-opacity-10">
                                <p class="text-muted mb-0">
                                    New to GotSchedule? <a href="sign.php" class="text-primary text-decoration-none fw-black">Create Free Account</a>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer class="py-5 bg-white border-top border-dark border-opacity-10">
        <div class="container text-center">
            <div class="logo mb-4">
                <h1 class="logo-font mb-0 fs-3 text-dark">GotSchedule</h1>
            </div>
            <div class="links d-flex justify-content-center gap-4 mb-4">
                <a href="#" class="text-muted text-decoration-none fs-8 fw-semibold">Terms</a>
                <a href="#" class="text-muted text-decoration-none fs-8 fw-semibold">Privacy</a>
                <a href="about.html" class="text-muted text-decoration-none fs-8 fw-semibold">About Our Vision</a>
            </div>
            <p class="text-muted fs-9 mb-0">&copy; 2026 GotSchedule. Engineered for high performance.</p>
        </div>
    </footer>

    <style>
        .fw-black { font-weight: 900; }
        .shadow-2xl { box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.1); }
        .hover-up:hover { transform: translateY(-3px); transition: var(--transition); }
        .italic { font-style: italic; }
        .btn-primary { background: var(--primary) !important; border: none !important; }
        .hover-bg-primary-subtle:hover { background: rgba(79, 70, 229, 0.03); }
        .hover-bg-success-subtle:hover { background: rgba(16, 185, 129, 0.03); }
        .hover-bg-info-subtle:hover { background: rgba(14, 165, 233, 0.03); }
        .hero-image-wrapper { perspective: 1000px; }
        .hero-image-wrapper img { transform: rotateY(-5deg) rotateX(2deg); filter: drop-shadow(0 20px 30px rgba(0,0,0,0.15)); }
        .hero-image-wrapper img:hover { transform: rotateY(0) rotateX(0); }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/notifications.js"></script>
    <script src="assets/js/main.js"></script>
</body>
</html>

