<?php
// $active_page should be set by the including page before this file is required.
$active_page = $active_page ?? '';

function student_nav_active($page, $active) {
    return $page === $active ? 'active' : '';
}
?>
<button class="mobile-sidebar-toggle" onclick="toggleAdminSidebar()" aria-label="Open menu">
    <i class="fa-solid fa-bars"></i>
</button>
<div class="sidebar-overlay" onclick="toggleAdminSidebar()"></div>

<aside class="admin-sidebar" id="adminSidebar">
    <div class="sidebar-top-group">
        <div class="sidebar-logo">
            <img src="../images/logo.png" alt="El Gato's Academy">
        </div>

        <nav class="sidebar-nav">
            <a href="dashboard.php" class="nav-link <?php echo student_nav_active('dashboard', $active_page); ?>">
                <i class="fa-solid fa-grip"></i> Dashboard
            </a>
            <a href="my-courses.php" class="nav-link <?php echo student_nav_active('my-courses', $active_page); ?>">
                <i class="fa-solid fa-book-open"></i> My Courses
            </a>
            <a href="profile.php" class="nav-link <?php echo student_nav_active('profile', $active_page); ?>">
                <i class="fa-solid fa-user"></i> Profile
            </a>
            <a href="../contact.php" class="nav-link <?php echo student_nav_active('contact', $active_page); ?>">
                <i class="fa-solid fa-envelope"></i> Contact School
            </a>
        </nav>
    </div>

    <div class="sidebar-footer">
        <div class="admin-mini-profile">
            <div class="admin-avatar">
                <?php echo strtoupper(substr($_SESSION['student_name'] ?? 'S', 0, 1)); ?>
            </div>
            <div>
                <p class="admin-name"><?php echo htmlspecialchars($_SESSION['student_name'] ?? 'Student'); ?></p>
                <span class="admin-role">Student</span>
            </div>
        </div>
        <a href="logout.php" class="nav-link logout-link">
            <i class="fa-solid fa-right-from-bracket"></i> Logout
        </a>
    </div>
</aside>

<script>
    function toggleAdminSidebar() {
        document.querySelector('.admin-sidebar').classList.toggle('open');
        document.querySelector('.sidebar-overlay').classList.toggle('open');
    }

    // If the browser restores this page from its back/forward cache (e.g. after logging out
    // and pressing Back), reload it so the login check runs again.
    window.addEventListener('pageshow', function (e) {
        if (e.persisted) {
            window.location.reload();
        }
    });
</script>
