<aside class="sidebar-modern reveal-animation">
    <div class="sidebar-logo px-4 py-5">
        <h1 class="logo-font fs-3">GotSchedule</h1>
    </div>
    
    <ul class="nav flex-column px-3">
        <li class="nav-item">
            <a class="nav-link-modern <?= $current_page == 'task.php' ? 'active' : '' ?>" href="task.php">
                <i class="bi bi-grid-fill me-3 fs-5"></i> <span>Dashboard</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link-modern <?= $current_page == 'calendar.php' ? 'active' : '' ?>" href="calendar.php">
                <i class="bi bi-calendar3 me-3 fs-5"></i> <span>Calendar</span>
            </a>
        </li>
        <?php if (in_array($_SESSION['role'] ?? 'student', ['admin', 'adviser'])): ?>
            <li class="nav-item">
                <a class="nav-link-modern <?= $current_page == 'messages.php' ? 'active' : '' ?>" href="messages.php">
                    <i class="bi bi-chat-dots-fill me-3 fs-5"></i> <span>Collaboration</span>
                </a>
            </li>
        <?php endif; ?>
        <?php if (($_SESSION['role'] ?? 'student') === 'admin'): ?>
            <li class="nav-item">
                <a class="nav-link-modern <?= $current_page == 'admin.php' ? 'active' : '' ?>" href="admin.php">
                    <i class="bi bi-cpu-fill me-3 fs-5"></i> <span>System Analytics</span>
                </a>
            </li>
        <?php endif; ?>
        <li class="nav-item">
            <a class="nav-link-modern <?= $current_page == 'notes.php' ? 'active' : '' ?>" href="notes.php">
                <i class="bi bi-journal-text me-3 fs-5"></i> <span>My Notes</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link-modern <?= $current_page == 'sched_maker.php' ? 'active' : '' ?>" href="sched_maker.php">
                <i class="bi bi-magic me-3 fs-5"></i> <span>Schedule Maker</span>
            </a>
        </li>
    </ul>

    <div class="sidebar-footer mt-auto p-4">
        <div class="user-profile-card d-flex align-items-center gap-3 mb-4 p-3 rounded-4 bg-white bg-opacity-40 border border-dark border-opacity-5">
            <div class="avatar-md rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold shadow-sm">
                <?= substr($_SESSION['user_name'] ?? 'U', 0, 1) ?>
            </div>
            <div class="user-info overflow-hidden">
                <div class="text-truncate fw-bold fs-7 text-dark"><?= htmlspecialchars($_SESSION['user_name'] ?? 'User') ?></div>
                <div class="text-truncate fs-8 text-muted fw-bold text-uppercase opacity-75"><?= htmlspecialchars($_SESSION['role'] ?? 'Student') ?></div>
            </div>
        </div>
        <a class="nav-link-modern text-danger hover-bg-danger-light" href="logout.php">
            <i class="bi bi-box-arrow-right me-3 fs-5"></i> <span>Logout</span>
        </a>
    </div>

    <!-- Mobile Toggle Button -->
    <button class="d-lg-none sidebar-toggle" onclick="toggleSidebar()">
        <i class="bi bi-list"></i>
    </button>
</aside>

<style>
    .sidebar-modern {
        width: 280px;
        height: 100vh;
        position: fixed;
        left: 0;
        top: 0;
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(20px);
        border-right: 1px solid var(--border);
        display: flex;
        flex-direction: column;
        z-index: 1040; /* Adjusted to be below Bootstrap Modals (1050) */
        transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 10px 0 30px -15px rgba(0, 0, 0, 0.05);
    }
    .sidebar-toggle {
        position: fixed;
        top: 1.5rem;
        left: 1.5rem;
        background: var(--surface);
        border: 1px solid var(--border);
        width: 45px;
        height: 45px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--text-primary);
        box-shadow: var(--shadow-md);
        z-index: 1001;
    }
    @media (max-width: 991px) {
        .sidebar-modern { transform: translateX(-100%); }
        .sidebar-modern.show { transform: translateX(0); }
    }
</style>

<script>
    function toggleSidebar() {
        document.querySelector('.sidebar-modern').classList.toggle('show');
    }
</script>
