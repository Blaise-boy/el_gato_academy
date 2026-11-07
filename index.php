<?php
require 'config/database.php';

$pageTitle = "El Gato's Academy | Learn Today. Build Tomorrow.";
$active_page = 'home';

// Hero image for this page
$heroStmt = $conn->prepare("SELECT image FROM page_heroes WHERE page_key = 'home'");
$heroStmt->execute();
$heroRow = $heroStmt->get_result()->fetch_assoc();
$heroImage = $heroRow['image'] ?? null;

// Popular courses (latest 6), with a module count per course
$courses = $conn->query("
    SELECT courses.*, COUNT(course_modules.id) AS module_count
    FROM courses
    LEFT JOIN course_modules ON course_modules.course_id = courses.id
    GROUP BY courses.id
    ORDER BY courses.created_at DESC
    LIMIT 6
");

// Icon shown for a course when it has no uploaded image
function courseIcon($category) {
    $icons = [
        'Technology'               => 'fa-code',
        'Design'                   => 'fa-palette',
        'Business'                 => 'fa-briefcase',
        'Data'                     => 'fa-database',
        'Digital Skills'           => 'fa-laptop',
        'Professional Development' => 'fa-chart-line',
    ];
    return $icons[$category] ?? 'fa-book';
}

// Latest 3 blog posts
$posts = $conn->query("SELECT * FROM blog_posts ORDER BY created_at DESC LIMIT 3");

require 'includes/header.php';
require 'includes/nav.php';
?>

  <!-- HERO -->
  <section class="hero">

    <div class="hero-text-col hero-content">
      <p class="hero-eyebrow">Welcome to</p>
      <h1>Learn Today. <span class="highlight">Build</span> Tomorrow.</h1>
      <p>
        Practical, industry-relevant training designed to help you gain real skills,
        build confidence, and create opportunities that last.
      </p>

      <div class="hero-buttons">
        <a href="courses.php" class="btn btn-primary">
          Explore Courses 
        </a>
        <a href="about.php" class="btn btn-secondary">
          How It Works <i class="fa-solid fa-play"></i>
        </a>
      </div>

      <div class="hero-features">
        <div class="hero-feature"><i class="fa-solid fa-desktop"></i> Practical Training</div>
        <div class="hero-feature"><i class="fa-solid fa-user-group"></i> Expert Instructors</div>
        <div class="hero-feature"><i class="fa-solid fa-bullseye"></i> Career Focused</div>
        <div class="hero-feature"><i class="fa-solid fa-award"></i> Course Certificate</div>
      </div>
    </div>

    <div class="home-hero-panel">
      <div class="home-hero-ring"></div>
      <?php if ($heroImage): ?>
        <img src="<?php echo htmlspecialchars($heroImage); ?>" alt="El Gato's Academy">
      <?php else: ?>
        <div class="home-hero-placeholder">
          <i class="fa-solid fa-image"></i>
          <span>Hero image not set yet</span>
        </div>
      <?php endif; ?>
    </div>

  </section>

  <!-- POPULAR COURSES -->
  <section class="section">
    <div class="container">

      <div class="section-header">
        <h2><span class="bar"></span> Popular Courses</h2>
        <a href="courses.php" class="view-all">View all courses</a>
      </div>

      <?php if ($courses->num_rows === 0): ?>
        <p class="empty-note">Courses will appear here once they've been added.</p>
      <?php else: ?>
        <div class="course-grid">
          <?php $i = 0; while ($course = $courses->fetch_assoc()): $i++; ?>
            <div class="course-card">
              <div class="course-card-icon <?php echo $i % 2 === 0 ? 'alt' : ''; ?>">
                <?php if ($course['image']): ?>
                  <img src="<?php echo htmlspecialchars($course['image']); ?>" alt="<?php echo htmlspecialchars($course['name']); ?>">
                <?php else: ?>
                  <i class="fa-solid <?php echo courseIcon($course['category']); ?>"></i>
                <?php endif; ?>
              </div>
              <div class="course-card-body">
                <h3><?php echo htmlspecialchars($course['name']); ?></h3>
                <p><?php echo htmlspecialchars(mb_strimwidth($course['description'], 0, 70, '...')); ?></p>
                <div class="course-card-footer">
                  <span><?php echo $course['module_count']; ?> Modules</span>
                  <a href="course-details.php?id=<?php echo $course['id']; ?>">View</a>
                </div>
              </div>
            </div>
          <?php endwhile; ?>
        </div>
      <?php endif; ?>

    </div>
  </section>

  <!-- PROMO BANNER -->
  <section class="section" style="padding-top:0;">
    <div class="container">
      <div class="promo-banner">
        <div class="promo-banner-left">
          <div class="promo-icon"><i class="fa-solid fa-graduation-cap"></i></div>
          <div>
            <h3>Ready to upgrade your skills?</h3>
            <p>Join thousands of learners who are building their future with us.</p>
          </div>
        </div>
        <a href="application.php" class="btn btn-primary">
          Get Started Now 
        </a>
      </div>
    </div>
  </section>

  <!-- WHY CHOOSE US -->
  <section class="section why-choose">
    <div class="container">
      <h2>Why Choose El Gato's Academy?</h2>
      <div class="underline"></div>

      <div class="why-grid">
        <div class="why-item">
          <div class="why-icon"><i class="fa-solid fa-book-open"></i></div>
          <div>
            <h4>Learn at Your Pace</h4>
            <p>Access courses anytime, anywhere. Learn on your schedule.</p>
          </div>
        </div>
        <div class="why-item">
          <div class="why-icon"><i class="fa-solid fa-user-group"></i></div>
          <div>
            <h4>Learn from Experts</h4>
            <p>Our instructors are industry professionals with real-world experience.</p>
          </div>
        </div>
        <div class="why-item">
          <div class="why-icon"><i class="fa-solid fa-laptop-code"></i></div>
          <div>
            <h4>Hands-on Projects</h4>
            <p>Build practical projects and apply what you learn to real-world scenarios.</p>
          </div>
        </div>
        <div class="why-item">
          <div class="why-icon"><i class="fa-solid fa-award"></i></div>
          <div>
            <h4>Earn Certificates</h4>
            <p>Receive certificates to validate your skills and boost your career.</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- LATEST BLOG -->
  <section class="section">
    <div class="container">

      <div class="section-header">
        <h2>Latest from our Blog</h2>
        <a href="blog.php" class="view-all">View all articles </i></a>
      </div>

      <?php if ($posts->num_rows === 0): ?>
        <p class="empty-note">Articles will appear here once they've been published.</p>
      <?php else: ?>
        <div class="blog-grid">
          <?php while ($post = $posts->fetch_assoc()):
              $readTime = max(1, (int) ceil(str_word_count(strip_tags($post['content'])) / 200));
          ?>
            <a href="blog-details.php?id=<?php echo $post['id']; ?>" class="blog-card">
              <?php if ($post['image']): ?>
                <img src="<?php echo htmlspecialchars($post['image']); ?>" alt="<?php echo htmlspecialchars($post['title']); ?>">
              <?php else: ?>
                <div class="blog-card-image-placeholder"><i class="fa-solid fa-image"></i></div>
              <?php endif; ?>
              <div class="blog-card-body">
                <span class="blog-category"><?php echo htmlspecialchars($post['category']); ?></span>
                <h3><?php echo htmlspecialchars($post['title']); ?></h3>
                <div class="blog-meta">
                  <?php echo date('M j, Y', strtotime($post['created_at'])); ?> &bull; <?php echo $readTime; ?> min read
                </div>
              </div>
            </a>
          <?php endwhile; ?>
        </div>
      <?php endif; ?>

    </div>
  </section>

<?php require 'includes/footer.php'; ?>
