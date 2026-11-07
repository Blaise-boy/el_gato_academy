<?php
require 'config/database.php';

$pageTitle = "Blog | El Gato's Academy";
$active_page = 'blog';

// Hero image for this page
$heroStmt = $conn->prepare("SELECT image FROM page_heroes WHERE page_key = 'blog'");
$heroStmt->execute();
$heroRow = $heroStmt->get_result()->fetch_assoc();
$heroImage = $heroRow['image'] ?? null;

// Filters
$search = trim($_GET['search'] ?? '');
$categoryFilter = trim($_GET['category'] ?? '');
$page = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 6;
$offset = ($page - 1) * $perPage;

$where = [];
$params = [];
$types = '';

if ($search !== '') {
    $where[] = '(title LIKE ? OR summary LIKE ?)';
    $likeTerm = '%' . $search . '%';
    $params[] = $likeTerm;
    $params[] = $likeTerm;
    $types .= 'ss';
}
if ($categoryFilter !== '') {
    $where[] = 'category = ?';
    $params[] = $categoryFilter;
    $types .= 's';
}
$whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

// Total count for pagination
$countStmt = $conn->prepare("SELECT COUNT(*) AS cnt FROM blog_posts $whereSql");
if ($types) {
    $countStmt->bind_param($types, ...$params);
}
$countStmt->execute();
$totalPosts = $countStmt->get_result()->fetch_assoc()['cnt'];
$totalPages = max(1, (int) ceil($totalPosts / $perPage));

// Fetch this page's posts
$postsStmt = $conn->prepare("SELECT * FROM blog_posts $whereSql ORDER BY created_at DESC LIMIT ? OFFSET ?");
$allParams = $params;
$allTypes = $types . 'ii';
$allParams[] = $perPage;
$allParams[] = $offset;
$postsStmt->bind_param($allTypes, ...$allParams);
$postsStmt->execute();
$posts = $postsStmt->get_result();

// Categories with real counts
$categories = $conn->query("SELECT category, COUNT(*) AS cnt FROM blog_posts GROUP BY category ORDER BY category");
$allCategoriesList = $conn->query("SELECT DISTINCT category FROM blog_posts ORDER BY category");

// Recent posts (sidebar) - independent of current filters
$recentPosts = $conn->query("SELECT * FROM blog_posts ORDER BY created_at DESC LIMIT 3");

function buildQuery($overrides = []) {
    $base = ['search' => $_GET['search'] ?? '', 'category' => $_GET['category'] ?? '', 'page' => $_GET['page'] ?? 1];
    $merged = array_merge($base, $overrides);
    $merged = array_filter($merged, fn($v) => $v !== '' && $v !== null);
    return 'blog.php?' . http_build_query($merged);
}

require 'includes/header.php';
require 'includes/nav.php';
?>

  <!-- HERO -->
  <section class="hero">

    <div class="hero-text-col hero-content">
      <h1>Our <span class="highlight">Blog.</span></h1>
      <p>
        Insights, tips, and resources to help you learn, grow, and stay ahead in
        the digital world.
      </p>

      <form method="GET" action="blog.php" class="hero-search-bar">
        <div class="hero-search-input">
          <input type="text" name="search" placeholder="Search blog posts..." value="<?php echo htmlspecialchars($search); ?>">
          <button type="submit"><i class="fa-solid fa-magnifying-glass"></i></button>
        </div>
        <select name="category" class="hero-category-select" onchange="this.form.submit()">
          <option value="">Categories</option>
          <?php
          $allCategoriesList->data_seek(0);
          while ($cat = $allCategoriesList->fetch_assoc()):
          ?>
            <option value="<?php echo htmlspecialchars($cat['category']); ?>" <?php echo $categoryFilter === $cat['category'] ? 'selected' : ''; ?>>
              <?php echo htmlspecialchars($cat['category']); ?>
            </option>
          <?php endwhile; ?>
        </select>
      </form>
    </div>

    <div class="blog-hero-panel">
      <div class="blog-hero-ring"></div>
      <?php if ($heroImage): ?>
        <img src="<?php echo htmlspecialchars($heroImage); ?>" alt="El Gato's Academy">
      <?php else: ?>
        <div class="blog-hero-placeholder">
          
        </div>
      <?php endif; ?>
    </div>

  </section>

  <!-- MAIN CONTENT -->
  <section class="section">
    <div class="container">
      <div class="blog-layout">

        <!-- Articles -->
        <div class="blog-main">
          <h2 style="margin-bottom:24px;">Latest Articles</h2>

          <?php if ($posts->num_rows === 0): ?>
            <p class="empty-note">No articles found<?php echo $search || $categoryFilter ? ' matching your filters' : ''; ?>.</p>
          <?php else: ?>
            <div class="blog-grid">
              <?php while ($post = $posts->fetch_assoc()): ?>
                <a href="blog-details.php?id=<?php echo $post['id']; ?>" class="blog-card">
                  <div class="blog-card-image">
                    <span class="blog-card-badge"><?php echo htmlspecialchars($post['category']); ?></span>
                    <?php if ($post['image']): ?>
                      <img src="<?php echo htmlspecialchars($post['image']); ?>" alt="<?php echo htmlspecialchars($post['title']); ?>">
                    <?php else: ?>
                      <div class="blog-card-image-placeholder"><i class="fa-solid fa-image"></i></div>
                    <?php endif; ?>
                  </div>
                  <div class="blog-card-body">
                    <div class="blog-meta"><?php echo date('M j, Y', strtotime($post['created_at'])); ?></div>
                    <h3><?php echo htmlspecialchars($post['title']); ?></h3>
                    <p class="blog-excerpt"><?php echo htmlspecialchars(mb_strimwidth($post['summary'], 0, 100, '...')); ?></p>
                    <span class="read-more">Read More </span>
                  </div>
                </a>
              <?php endwhile; ?>
            </div>

            <?php if ($totalPages > 1): ?>
              <div class="pagination">
                <?php if ($page > 1): ?>
                  <a href="<?php echo buildQuery(['page' => $page - 1]); ?>" class="page-btn"><i class="fa-solid fa-chevron-left"></i></a>
                <?php endif; ?>

                <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                  <a href="<?php echo buildQuery(['page' => $p]); ?>" class="page-btn <?php echo $p === $page ? 'active' : ''; ?>"><?php echo $p; ?></a>
                <?php endfor; ?>

                <?php if ($page < $totalPages): ?>
                  <a href="<?php echo buildQuery(['page' => $page + 1]); ?>" class="page-btn"><i class="fa-solid fa-chevron-right"></i></a>
                <?php endif; ?>
              </div>
            <?php endif; ?>
          <?php endif; ?>
        </div>

        <!-- Sidebar -->
        <aside class="blog-sidebar">

          <div class="sidebar-card">
            <h3>Categories</h3>
            <ul class="category-list">
              <li>
                <a href="blog.php" class="<?php echo $categoryFilter === '' ? 'active' : ''; ?>">
                  <span><i class="fa-solid fa-layer-group"></i> All Categories</span>
                  <span class="count"><?php echo $totalPosts; ?></span>
                </a>
              </li>
              <?php
              $categories->data_seek(0);
              while ($cat = $categories->fetch_assoc()):
              ?>
                <li>
                  <a href="blog.php?category=<?php echo urlencode($cat['category']); ?>" class="<?php echo $categoryFilter === $cat['category'] ? 'active' : ''; ?>">
                    <span><i class="fa-solid fa-tag"></i> <?php echo htmlspecialchars($cat['category']); ?></span>
                    <span class="count"><?php echo $cat['cnt']; ?></span>
                  </a>
                </li>
              <?php endwhile; ?>
            </ul>
          </div>

          <div class="sidebar-card">
            <h3>Recent Posts</h3>
            <?php if ($recentPosts->num_rows === 0): ?>
              <p class="empty-note">No posts yet.</p>
            <?php else: ?>
              <?php while ($rp = $recentPosts->fetch_assoc()): ?>
                <a href="blog-details.php?id=<?php echo $rp['id']; ?>" class="recent-post-item">
                  <?php if ($rp['image']): ?>
                    <img src="<?php echo htmlspecialchars($rp['image']); ?>" alt="">
                  <?php else: ?>
                    <div class="recent-post-thumb-placeholder"><i class="fa-solid fa-image"></i></div>
                  <?php endif; ?>
                  <div>
                    <h4><?php echo htmlspecialchars($rp['title']); ?></h4>
                    <span><?php echo date('M j, Y', strtotime($rp['created_at'])); ?></span>
                  </div>
                </a>
              <?php endwhile; ?>
            <?php endif; ?>
          </div>

          <div class="sidebar-card">
            <h3>Subscribe to Our Newsletter</h3>
            <p class="sidebar-newsletter-text">Get the latest articles, tips, and updates delivered to your inbox.</p>
            <form class="newsletter-form" action="forms/newsletter-process.php" method="POST">
              <input type="email" name="email" placeholder="Enter your email" required>
              <button type="submit"><i class="fa-solid fa-paper-plane"></i></button>
            </form>
            <?php if (isset($_GET['subscribed'])): ?>
              <p class="newsletter-success"><i class="fa-solid fa-check"></i> Thanks for subscribing!</p>
            <?php endif; ?>
          </div>

        </aside>

      </div>
    </div>
  </section>

  <!-- NEVER STOP LEARNING BANNER -->
  <section class="section" style="padding-top:0;">
    <div class="container">
      <div class="learning-banner">
        <div class="learning-banner-text">
          <div class="promo-icon" style="border-color:var(--orange); color:var(--orange);"><i class="fa-solid fa-book-open"></i></div>
          <div>
            <h3>Never Stop Learning</h3>
            <p>Explore our courses and take the next step toward your goals today.</p>
            <a href="courses.php" class="btn btn-primary">
              Explore Courses 
            </a>
          </div>
        </div>
        <div class="learning-banner-image">
          <img src="images/blog_image.png" alt="">
        </div>
      </div>
    </div>
  </section>

<?php require 'includes/footer.php'; ?>
