<?php
require 'config/database.php';

$pageTitle = "Instructors | El Gato's Academy";
$active_page = 'instructors';

// Hero image for this page
$heroStmt = $conn->prepare("SELECT image FROM page_heroes WHERE page_key = 'instructors'");
$heroStmt->execute();
$heroRow = $heroStmt->get_result()->fetch_assoc();
$heroImage = $heroRow['image'] ?? null;

// Instructors, with a real count of courses each one teaches
$instructors = $conn->query("
    SELECT instructors.*, COUNT(courses.id) AS course_count
    FROM instructors
    LEFT JOIN courses ON courses.instructor_id = instructors.id
    GROUP BY instructors.id
    ORDER BY instructors.name ASC
");

require 'includes/header.php';
require 'includes/nav.php';
?>

  <!-- HERO -->
  <section class="hero">

    <div class="hero-text-col hero-content">
      <p class="hero-eyebrow">Meet Our Team</p>
      <h1 style="font-size:40px;">
        Learn From <span class="highlight">Industry Experts.</span>
      </h1>
      <p>
        Our instructors bring real-world experience into every course, so you
        learn skills that actually translate to the job.
      </p>

      <div class="hero-buttons">
        <a href="#instructor-list" class="btn btn-secondary">
          Meet the Team 
        </a>
      </div>
    </div>

    <div class="instructors-hero-panel">
      <div class="instructors-hero-ring"></div>
      <?php if ($heroImage): ?>
        <img src="<?php echo htmlspecialchars($heroImage); ?>" alt="El Gato's Academy">
      <?php else: ?>
        <div class="instructors-hero-placeholder"></div>
      <?php endif; ?>
    </div>

  </section>

  <!-- INSTRUCTOR GRID -->
  <section class="section" id="instructor-list">
    <div class="container">

      <?php if ($instructors->num_rows === 0): ?>
        <p class="empty-note">Instructors will appear here once they've been added.</p>
      <?php else: ?>
        <div class="instructor-grid">
          <?php while ($inst = $instructors->fetch_assoc()): ?>
            <a href="instructor-details.php?id=<?php echo $inst['id']; ?>" class="instructor-card">
              <div class="instructor-card-photo">
                <?php if ($inst['photo']): ?>
                  <img src="<?php echo htmlspecialchars($inst['photo']); ?>" alt="<?php echo htmlspecialchars($inst['name']); ?>">
                <?php else: ?>
                  <div class="instructor-card-photo-fallback"><?php echo strtoupper(substr($inst['name'], 0, 1)); ?></div>
                <?php endif; ?>
              </div>
              <div class="instructor-card-body">
                <h3><?php echo htmlspecialchars($inst['name']); ?></h3>
                <span class="instructor-card-position"><?php echo htmlspecialchars($inst['position']); ?></span>
                <?php if ($inst['specialization']): ?>
                  <p class="instructor-card-specialization"><i class="fa-solid fa-bolt"></i> <?php echo htmlspecialchars($inst['specialization']); ?></p>
                <?php endif; ?>
                <?php if ($inst['bio']): ?>
                  <p class="instructor-card-bio"><?php echo htmlspecialchars(mb_strimwidth($inst['bio'], 0, 110, '...')); ?></p>
                <?php endif; ?>
                <div class="instructor-card-footer">
                  <i class="fa-solid fa-book"></i> <?php echo (int) $inst['course_count']; ?> <?php echo $inst['course_count'] == 1 ? 'Course' : 'Courses'; ?>
                </div>
              </div>
            </a>
          <?php endwhile; ?>
        </div>
      <?php endif; ?>

    </div>
  </section>

<?php require 'includes/footer.php'; ?>
