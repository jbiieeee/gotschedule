<aside class="sidebar-modern reveal-animation">
    <div class="sidebar-logo px-4 py-5">
        <h1 class="logo-font fs-3">GotSchedule</h1>
    </div>
    
    <ul class="nav flex-column px-3">
        <li class="nav-item">
            <a class="nav-link-modern <?= $current_page == 'task.php' ? 'active' : '' ?>" href="task.php">
                <i class="bi bi-grid-fill me-3 fs-5 text-primary"></i> <span>Dashboard</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link-modern <?= $current_page == 'calendar.php' ? 'active' : '' ?>" href="calendar.php">
                <i class="bi bi-calendar3 me-3 fs-5 text-success"></i> <span>Calendar</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link-modern <?= $current_page == 'collaboration.php' ? 'active' : '' ?>" href="collaboration.php">
                <i class="bi bi-people-fill me-3 fs-5 text-success"></i> <span>Collaboration Hub</span>
            </a>
        </li>
        <?php if (in_array($_SESSION['role'] ?? 'student', ['admin', 'adviser'])): ?>
            <li class="nav-item">
                <a class="nav-link-modern <?= $current_page == 'messages.php' ? 'active' : '' ?>" href="messages.php">
                    <i class="bi bi-chat-dots-fill me-3 fs-5 text-info"></i> <span>Collaboration</span>
                </a>
            </li>
        <?php endif; ?>
        <?php if (($_SESSION['role'] ?? 'student') === 'admin'): ?>
            <li class="nav-item">
                <a class="nav-link-modern <?= $current_page == 'admin.php' ? 'active' : '' ?>" href="admin.php">
                    <i class="bi bi-cpu-fill me-3 fs-5 text-warning"></i> <span>System Analytics</span>
                </a>
            </li>
        <?php endif; ?>
        <li class="nav-item">
            <a class="nav-link-modern <?= $current_page == 'notes.php' ? 'active' : '' ?>" href="notes.php">
                <i class="bi bi-journal-text me-3 fs-5 text-danger"></i> <span>My Notes</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link-modern <?= $current_page == 'sched_maker.php' ? 'active' : '' ?>" href="sched_maker.php">
                <i class="bi bi-magic me-3 fs-5" style="color: #a855f7;"></i> <span>Schedule Maker</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link-modern <?= $current_page == 'settings.php' ? 'active' : '' ?>" href="settings.php">
                <i class="bi bi-gear-fill me-3 fs-5 text-secondary"></i> <span>Settings</span>
            </a>
        </li>
    </ul>

    <div class="sidebar-footer mt-auto p-4">
        <div class="user-profile-card d-flex align-items-center gap-3 mb-4 p-3 rounded-4 bg-primary bg-opacity-5 border border-dark border-opacity-5">
            <div class="avatar-md rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold shadow-sm" style="width: 40px; height: 40px; flex-shrink: 0;">
                <?= substr($_SESSION['user_name'] ?? 'U', 0, 1) ?>
            </div>
            <div class="user-info overflow-hidden">
                <div class="text-truncate fw-bold fs-7 text-dark"><?= htmlspecialchars($_SESSION['user_name'] ?? 'User') ?></div>
                <div class="text-truncate fs-8 text-muted fw-bold text-uppercase opacity-75"><?= htmlspecialchars($_SESSION['role'] ?? 'Student') ?></div>
            </div>
        </div>
        <div class="d-flex align-items-center justify-content-between mb-4 px-3 py-2 bg-primary bg-opacity-5 rounded-4 border border-dark border-opacity-5">
            <span class="fs-8 fw-bold text-muted text-uppercase letter-spacing-1">Midnight Mode</span>
            <div class="form-check form-switch m-0 p-0 d-flex align-items-center">
                <input class="form-check-input cursor-pointer" type="checkbox" id="dark-mode-toggle" style="width: 40px; height: 20px;">
            </div>
        </div>

        <a class="nav-link-modern text-danger hover-bg-danger-light" href="logout.php">
            <i class="bi bi-box-arrow-right me-3 fs-5"></i> <span>Logout</span>
        </a>
    </div>

    <!-- Mobile Overlay (Inside aside for isolation) -->
    <div class="sidebar-overlay d-lg-none" onclick="toggleSidebar()"></div>
</aside>

<!-- Mobile Toggle Button (Outside aside) -->
<button class="d-lg-none sidebar-toggle" id="sidebar-trigger" onclick="toggleSidebar()">
    <i class="bi bi-list"></i>
</button>

<style>
    .sidebar-modern {
        width: 280px;
        height: 100vh;
        position: fixed;
        left: 0;
        top: 0;
        background: var(--surface);
        backdrop-filter: blur(20px);
        border-right: 1px solid var(--border);
        display: flex;
        flex-direction: column;
        z-index: 1045;
        transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 10px 0 30px -15px rgba(0, 0, 0, 0.05);
    }
    .sidebar-toggle {
        position: fixed;
        top: 1rem;
        left: 1rem;
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
        z-index: 1040;
    }
    .sidebar-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        background: rgba(0,0,0,0.5);
        backdrop-filter: blur(4px);
        z-index: 1044;
        display: none;
        opacity: 0;
        transition: opacity 0.3s ease;
    }
    .sidebar-overlay.show {
        display: block;
        opacity: 1;
    }

    @media (max-width: 991px) {
        .sidebar-modern { transform: translateX(-100%); }
        .sidebar-modern.show { transform: translateX(0); }
    }
</style>

<script>
    // Theme Persistance
    (function() {
        const html = document.documentElement;
        const toggle = document.getElementById('dark-mode-toggle');
        const savedTheme = localStorage.getItem('theme') || 'light';
        html.setAttribute('data-theme', savedTheme);
        if (toggle) {
            if (savedTheme === 'dark') toggle.checked = true;
            toggle.addEventListener('change', () => {
                const theme = toggle.checked ? 'dark' : 'light';
                html.setAttribute('data-theme', theme);
                localStorage.setItem('theme', theme);
                window.dispatchEvent(new CustomEvent('themeChanged', { detail: { theme } }));
            });
        }
    })();

    // Sidebar Logic
    function toggleSidebar() {
        const sidebar = document.querySelector('.sidebar-modern');
        const overlay = document.querySelector('.sidebar-overlay');
        const triggerIcon = document.querySelector('#sidebar-trigger i');

        sidebar.classList.toggle('show');
        overlay.classList.toggle('show');

        if (sidebar.classList.contains('show')) {
            triggerIcon.classList.replace('bi-list', 'bi-x-lg');
        } else {
            triggerIcon.classList.replace('bi-x-lg', 'bi-list');
        }
    }
</script>
