<?php
// $active_page should be set by the including page before this file is required.
$active_page = $active_page ?? '';

function nav_active($page, $active) {
    return $page === $active ? 'active' : '';
}
?>
<button class="mobile-sidebar-toggle" onclick="toggleAdminSidebar()" aria-label="Open menu">
    <i class="fa-solid fa-bars"></i>
</button>
<div class="sidebar-overlay" onclick="toggleAdminSidebar()"></div>

<aside class="admin-sidebar" id="adminSidebar">
    <div class="sidebar-logo">
        <img src="../images/logo.png" alt="El Gato's Academy">
    </div>

    <nav class="sidebar-nav">
        <a href="dashboard.php" class="nav-link <?php echo nav_active('dashboard', $active_page); ?>">
            <i class="fa-solid fa-grip"></i> Dashboard
        </a>
        <a href="applications.php" class="nav-link <?php echo nav_active('applications', $active_page); ?>">
            <i class="fa-solid fa-file-lines"></i> Applications
        </a>
        <a href="students.php" class="nav-link <?php echo nav_active('students', $active_page); ?>">
            <i class="fa-solid fa-user-graduate"></i> Students
        </a>
        <a href="courses.php" class="nav-link <?php echo nav_active('courses', $active_page); ?>">
            <i class="fa-solid fa-book"></i> Courses
        </a>
        <a href="instructors.php" class="nav-link <?php echo nav_active('instructors', $active_page); ?>">
            <i class="fa-solid fa-chalkboard-user"></i> Instructors
        </a>
        <a href="enrollments.php" class="nav-link <?php echo nav_active('enrollments', $active_page); ?>">
            <i class="fa-solid fa-graduation-cap"></i> Enrollments
        </a>
        <a href="certificates.php" class="nav-link <?php echo nav_active('certificates', $active_page); ?>">
            <i class="fa-solid fa-award"></i> Certificates
        </a>
        <a href="blog.php" class="nav-link <?php echo nav_active('blog', $active_page); ?>">
            <i class="fa-solid fa-newspaper"></i> Blog
        </a>
        <a href="heroes.php" class="nav-link <?php echo nav_active('heroes', $active_page); ?>">
            <i class="fa-solid fa-panorama"></i> Hero Images
        </a>
        <a href="messages.php" class="nav-link <?php echo nav_active('messages', $active_page); ?>">
            <i class="fa-solid fa-envelope"></i> Messages
        </a>
    </nav>

    <div class="sidebar-footer">
        <div class="admin-mini-profile">
            <div class="admin-avatar">
                <?php echo strtoupper(substr($_SESSION['admin_name'] ?? 'A', 0, 1)); ?>
            </div>
            <div>
                <p class="admin-name"><?php echo htmlspecialchars($_SESSION['admin_name'] ?? 'Admin'); ?></p>
                <span class="admin-role">Super Admin</span>
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
