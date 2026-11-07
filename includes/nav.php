<?php
// $active_page should be set by the including page before nav.php is required.
$active_page = $active_page ?? '';

function nav_link_active($page, $active) {
    return $page === $active ? 'active' : '';
}

// Logged-in student (if any) - read fresh from the database so name/photo stay current.
$navStudent = null;
if (!empty($_SESSION['student_id']) && isset($conn)) {
    $navStmt = $conn->prepare("SELECT first_name, last_name, photo FROM students WHERE id = ? AND status = 'active'");
    $navStmt->bind_param('i', $_SESSION['student_id']);
    $navStmt->execute();
    $navStudent = $navStmt->get_result()->fetch_assoc() ?: null;
}
?>
<header class="site-header">

  <div class="container nav-container">

    <a href="index.php" class="logo">
      <img src="images/logo.png" alt="El Gato's Academy">
    </a>

    <nav class="main-nav" id="mainNav">

      <a href="index.php" class="<?php echo nav_link_active('home', $active_page); ?>">
        Home
      </a>

      <a href="courses.php" class="<?php echo nav_link_active('courses', $active_page); ?>">
        Courses
      </a>

      <a href="instructors.php" class="<?php echo nav_link_active('instructors', $active_page); ?>">
        Instructors
      </a>

      <a href="blog.php" class="<?php echo nav_link_active('blog', $active_page); ?>">
        Blog
      </a>

      <a href="about.php" class="<?php echo nav_link_active('about', $active_page); ?>">
        About Us
      </a>

      <a href="contact.php" class="<?php echo nav_link_active('contact', $active_page); ?>">
        Contact
      </a>

    </nav>

    <div class="nav-actions">

      <button type="button" class="icon-btn" id="searchToggle" aria-label="Search">
        <i class="fa-solid fa-magnifying-glass"></i>
      </button>

      <?php if ($navStudent): ?>

        <div class="nav-user" id="navUser">
          <button type="button" class="nav-user-btn" id="navUserBtn" aria-haspopup="true" aria-expanded="false">
            <span class="nav-user-avatar">
              <?php if (!empty($navStudent['photo'])): ?>
                <img src="<?php echo htmlspecialchars($navStudent['photo']); ?>" alt="">
              <?php else: ?>
                <?php echo strtoupper(substr($navStudent['first_name'], 0, 1)); ?>
              <?php endif; ?>
            </span>
            <span class="nav-user-name"><?php echo htmlspecialchars($navStudent['first_name']); ?></span>
            <i class="fa-solid fa-chevron-down nav-user-caret"></i>
          </button>

          <div class="nav-user-menu" id="navUserMenu">
            <div class="nav-user-menu-head">
              Signed in as<br>
              <strong><?php echo htmlspecialchars($navStudent['first_name'] . ' ' . $navStudent['last_name']); ?></strong>
            </div>
            <a href="student/dashboard.php"><i class="fa-solid fa-gauge"></i> Dashboard</a>
            <a href="student/my-courses.php"><i class="fa-solid fa-book-open"></i> My Courses</a>
            <a href="student/profile.php"><i class="fa-solid fa-user"></i> Profile</a>
            <a href="student/logout.php" class="nav-user-logout"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
          </div>
        </div>

      <?php else: ?>

        <a href="login.php" class="btn btn-outline">Login</a>
        <a href="application.php" class="btn btn-primary">Sign Up</a>

      <?php endif; ?>

      <button type="button" class="mobile-toggle" id="mobileToggle" aria-label="Menu">
        <i class="fa-solid fa-bars"></i>
      </button>

    </div>

  </div>

  <form action="courses.php#course-list" method="GET" class="site-search-bar" id="siteSearchBar">
    <div class="container">
      <input
        type="text"
        name="search"
        placeholder="Search courses..."
        autocomplete="off"
      >
      <button type="submit"><i class="fa-solid fa-magnifying-glass"></i></button>
    </div>
  </form>

</header>
