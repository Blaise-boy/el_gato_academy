<?php
require 'config/database.php';

$pageTitle = "Courses | El Gato's Academy";
$active_page = 'courses';

// Hero image for this page
$heroStmt = $conn->prepare("SELECT image FROM page_heroes WHERE page_key = 'courses'");
$heroStmt->execute();
$heroRow = $heroStmt->get_result()->fetch_assoc();
$heroImage = $heroRow['image'] ?? null;

// Filters
$categoryFilter = trim($_GET['category'] ?? '');
$search = trim($_GET['search'] ?? '');
$sort = $_GET['sort'] ?? 'popular';

$allowedSorts = [
    'popular'    => 'enrollment_count DESC',
    'newest'     => 'courses.created_at DESC',
    'price_low'  => 'courses.fee ASC',
    'price_high' => 'courses.fee DESC',
    'name'       => 'courses.name ASC',
];
$orderBy = $allowedSorts[$sort] ?? $allowedSorts['popular'];

$sql = "
    SELECT courses.*,
           COUNT(DISTINCT enrollments.id) AS enrollment_count,
           COUNT(DISTINCT course_modules.id) AS module_count
    FROM courses
    LEFT JOIN enrollments ON enrollments.course_id = courses.id
    LEFT JOIN course_modules ON course_modules.course_id = courses.id
";

$where = [];
$types = '';
$params = [];

if ($categoryFilter !== '') {
    $where[] = 'courses.category = ?';
    $types .= 's';
    $params[] = $categoryFilter;
}

if ($search !== '') {
    $where[] = '(courses.name LIKE ? OR courses.description LIKE ? OR courses.category LIKE ?)';
    $types .= 'sss';
    $like = '%' . $search . '%';
    array_push($params, $like, $like, $like);
}

if ($where) {
    $sql .= ' WHERE ' . implode(' AND ', $where) . ' ';
}

$sql .= " GROUP BY courses.id ORDER BY $orderBy";

$stmt = $conn->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$courses = $stmt->get_result();

// Category pills - built from whatever categories actually exist
$categories = $conn->query("SELECT DISTINCT category FROM courses ORDER BY category");

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

require 'includes/header.php';
require 'includes/nav.php';
?>

  <!-- HERO -->
  <section class="hero">

    <div class="hero-text-col hero-content">
      <p class="hero-eyebrow">Our Courses</p>
      <h1 style="font-size:40px;">
        Practical Skills.<br>Real-World <span class="highlight">Success.</span>
      </h1>
      <p>
        Explore our expert-led courses designed to give you in-demand skills and
        hands-on experience to advance your career and build the future you want.
      </p>

      <div class="hero-buttons">
        <a href="#course-list" class="btn btn-secondary">
          Browse All Courses 
        </a>
      </div>
    </div>

    <div class="courses-hero-panel">
      <div class="courses-hero-ring"></div>
      <?php if ($heroImage): ?>
        <img src="<?php echo htmlspecialchars($heroImage); ?>" alt="El Gato's Academy">
      <?php else: ?>
        <div class="courses-hero-placeholder">
          <i class="fa-solid fa-image"></i>
          <span>Hero image not set yet</span>
        </div>
      <?php endif; ?>
    </div>

  </section>

  <!-- FILTER + COURSE GRID -->
  <section class="section" id="course-list">
    <div class="container">

      <?php if ($search !== ''): ?>
        <p class="search-result-note">
          Showing results for <strong>&ldquo;<?php echo htmlspecialchars($search); ?>&rdquo;</strong>
          &mdash; <?php echo $courses->num_rows; ?> course<?php echo $courses->num_rows === 1 ? '' : 's'; ?> found
        </p>
      <?php endif; ?>

      <form method="GET" action="courses.php#course-list" class="category-filter-bar" id="filterForm">
        <?php if ($search !== ''): ?>
          <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">
        <?php endif; ?>
        <div class="filter-field">
          <label for="categorySelect"><i class="fa-solid fa-layer-group"></i> Category</label>
          <select name="category" id="categorySelect" class="sort-select" onchange="document.getElementById('filterForm').submit();">
            <option value="">All Courses</option>
            <?php while ($cat = $categories->fetch_assoc()): ?>
              <option value="<?php echo htmlspecialchars($cat['category']); ?>" <?php echo $categoryFilter === $cat['category'] ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($cat['category']); ?>
              </option>
            <?php endwhile; ?>
          </select>
        </div>

        <div class="filter-field">
          <label for="sortSelect"><i class="fa-solid fa-arrow-down-wide-short"></i> Sort</label>
          <select name="sort" id="sortSelect" class="sort-select" onchange="document.getElementById('filterForm').submit();">
            <option value="popular" <?php echo $sort === 'popular' ? 'selected' : ''; ?>>Popular</option>
            <option value="newest" <?php echo $sort === 'newest' ? 'selected' : ''; ?>>Newest</option>
            <option value="price_low" <?php echo $sort === 'price_low' ? 'selected' : ''; ?>>Price: Low to High</option>
            <option value="price_high" <?php echo $sort === 'price_high' ? 'selected' : ''; ?>>Price: High to Low</option>
            <option value="name" <?php echo $sort === 'name' ? 'selected' : ''; ?>>Name: A-Z</option>
          </select>
        </div>

        <?php if ($categoryFilter !== '' || $sort !== 'popular' || $search !== ''): ?>
          <a href="courses.php#course-list" class="filter-reset"><i class="fa-solid fa-rotate-left"></i> Reset</a>
        <?php endif; ?>
      </form>

      <?php if ($courses->num_rows === 0): ?>
        <p class="empty-note">
          <?php if ($search !== ''): ?>
            No courses match &ldquo;<?php echo htmlspecialchars($search); ?>&rdquo;. Try a different keyword.
          <?php else: ?>
            No courses found<?php echo $categoryFilter ? ' in this category' : ''; ?> yet.
          <?php endif; ?>
        </p>
      <?php else: ?>
        <div class="course-grid">
          <?php $i = 0; while ($course = $courses->fetch_assoc()): $i++; ?>
            <a href="course-details.php?id=<?php echo $course['id']; ?>" class="course-card">
              <div class="course-card-icon <?php echo $i % 2 === 0 ? 'alt' : ''; ?>">
                <span class="course-card-badge"><?php echo htmlspecialchars($course['category']); ?></span>
                <?php if ($course['image']): ?>
                  <img src="<?php echo htmlspecialchars($course['image']); ?>" alt="<?php echo htmlspecialchars($course['name']); ?>">
                <?php else: ?>
                  <i class="fa-solid <?php echo courseIcon($course['category']); ?>"></i>
                <?php endif; ?>
              </div>
              <div class="course-card-body">
                <h3><?php echo htmlspecialchars($course['name']); ?></h3>
                <p><?php echo htmlspecialchars(mb_strimwidth($course['description'], 0, 90, '...')); ?></p>
                <div class="course-meta-row">
                  <span><?php echo $course['module_count']; ?> Modules</span>
                  <span class="dot">&bull;</span>
                  <span><?php echo htmlspecialchars($course['level']); ?></span>
                </div>
              </div>
            </a>
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

  <!-- WHY LEARN WITH US -->
  <section class="section why-choose">
    <div class="container">
      <h2>Why Learn With El Gato's Academy?</h2>
      <div class="underline"></div>

      <div class="why-grid">
        <div class="why-item">
          <div class="why-icon"><i class="fa-solid fa-book-open"></i></div>
          <div>
            <h4>Hands-on Learning</h4>
            <p>Work on real projects and practical exercises.</p>
          </div>
        </div>
        <div class="why-item">
          <div class="why-icon"><i class="fa-solid fa-user-group"></i></div>
          <div>
            <h4>Expert Instructors</h4>
            <p>Learn from industry professionals with real-world experience.</p>
          </div>
        </div>
        <div class="why-item">
          <div class="why-icon"><i class="fa-solid fa-circle-play"></i></div>
          <div>
            <h4>Lifetime Access</h4>
            <p>Access your courses anytime, anywhere.</p>
          </div>
        </div>
        <div class="why-item">
          <div class="why-icon"><i class="fa-solid fa-award"></i></div>
          <div>
            <h4>Certificates</h4>
            <p>Earn certificates to showcase your skills and boost your career.</p>
          </div>
        </div>
      </div>
    </div>
  </section>

<?php require 'includes/footer.php'; ?>
