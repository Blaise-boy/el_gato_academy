<?php
require 'config/database.php';
require 'includes/rich-text.php';

$id = $_GET['id'] ?? null;
if (!$id || !is_numeric($id)) {
    header('Location: blog.php');
    exit();
}

$stmt = $conn->prepare('SELECT * FROM blog_posts WHERE id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
$post = $stmt->get_result()->fetch_assoc();

if (!$post) {
    header('Location: blog.php');
    exit();
}

$pageTitle = htmlspecialchars($post['title']) . " | El Gato's Academy";
$pageDescription = mb_strimwidth($post['summary'], 0, 150, '...');
$active_page = 'blog';

$readTime = max(1, (int) ceil(str_word_count(strip_tags($post['content'])) / 200));

// Sidebar data — same as blog.php, independent of this post
$categories = $conn->query("SELECT category, COUNT(*) AS cnt FROM blog_posts GROUP BY category ORDER BY category");
$totalPosts = $conn->query("SELECT COUNT(*) AS cnt FROM blog_posts")->fetch_assoc()['cnt'];
$recentPosts = $conn->query("SELECT * FROM blog_posts WHERE id != $id ORDER BY created_at DESC LIMIT 3");

require 'includes/header.php';
require 'includes/nav.php';
?>

  <section class="section" style="padding-top:40px;">
    <div class="container">
      <div class="blog-layout">

        <!-- ARTICLE -->
        <div class="blog-main">

          <span class="blog-category"><?php echo htmlspecialchars(strtoupper($post['category'])); ?></span>
          <h1 class="blog-details-title"><?php echo htmlspecialchars($post['title']); ?></h1>
          <div class="blog-meta" style="margin-bottom:20px;">
            <i class="fa-regular fa-calendar"></i> <?php echo date('M j, Y', strtotime($post['created_at'])); ?>
            <span class="dot">&bull;</span>
            <i class="fa-regular fa-clock"></i> <?php echo $readTime; ?> min read
          </div>

          <?php if ($post['image']): ?>
            <img src="<?php echo htmlspecialchars($post['image']); ?>" alt="<?php echo htmlspecialchars($post['title']); ?>" class="blog-details-image">
          <?php else: ?>
            <div class="blog-card-image-placeholder" style="height:320px; border-radius:12px; margin-bottom:28px;"><i class="fa-solid fa-image"></i></div>
          <?php endif; ?>

          <div class="blog-content-body">
            <?php echo render_blog_content($post['content']); ?>
          </div>

          <div class="sidebar-card" style="margin-top:32px;">
            <h3>Share this Article</h3>
            <div class="share-row">
              <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode('https://elgatosacademy.ng/blog-details.php?id=' . $post['id']); ?>" target="_blank" aria-label="Share on Facebook"><i class="fa-brands fa-facebook-f"></i></a>
              <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode('https://elgatosacademy.ng/blog-details.php?id=' . $post['id']); ?>&text=<?php echo urlencode($post['title']); ?>" target="_blank" aria-label="Share on Twitter"><i class="fa-brands fa-twitter"></i></a>
              <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?php echo urlencode('https://elgatosacademy.ng/blog-details.php?id=' . $post['id']); ?>" target="_blank" aria-label="Share on LinkedIn"><i class="fa-brands fa-linkedin-in"></i></a>
              <button type="button" class="share-copy-btn" onclick="copyArticleLink(this)" aria-label="Copy link"><i class="fa-solid fa-link"></i></button>
            </div>
          </div>

        </div>

        <!-- SIDEBAR -->
        <aside class="blog-sidebar">

          <div class="sidebar-card">
            <h3>Search</h3>
            <form method="GET" action="blog.php" class="newsletter-form">
              <input type="text" name="search" placeholder="Search blog...">
              <button type="submit"><i class="fa-solid fa-magnifying-glass"></i></button>
            </form>
          </div>

          <div class="sidebar-card">
            <h3>Categories</h3>
            <ul class="category-list">
              <li>
                <a href="blog.php">
                  <span><i class="fa-solid fa-layer-group"></i> All Categories</span>
                  <span class="count"><?php echo $totalPosts; ?></span>
                </a>
              </li>
              <?php while ($cat = $categories->fetch_assoc()): ?>
                <li>
                  <a href="blog.php?category=<?php echo urlencode($cat['category']); ?>">
                    <span><i class="fa-solid fa-tag"></i> <?php echo htmlspecialchars($cat['category']); ?></span>
                    <span class="count"><?php echo $cat['cnt']; ?></span>
                  </a>
                </li>
              <?php endwhile; ?>
            </ul>
          </div>

          <div class="sidebar-card">
            <h3>Latest Posts</h3>
            <?php if ($recentPosts->num_rows === 0): ?>
              <p class="empty-note">No other posts yet.</p>
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

  <script>
    function copyArticleLink(btn) {
      navigator.clipboard.writeText(window.location.href).then(() => {
        const icon = btn.querySelector('i');
        icon.classList.replace('fa-link', 'fa-check');
        setTimeout(() => icon.classList.replace('fa-check', 'fa-link'), 1500);
      });
    }
  </script>

<?php require 'includes/footer.php'; ?>
