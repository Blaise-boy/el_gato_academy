<?php
require 'config/database.php';

$id = $_GET['id'] ?? null;
if (!$id || !is_numeric($id)) {
    header('Location: instructors.php');
    exit();
}

$stmt = $conn->prepare('SELECT * FROM instructors WHERE id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
$instructor = $stmt->get_result()->fetch_assoc();

if (!$instructor) {
    header('Location: instructors.php');
    exit();
}

$pageTitle = htmlspecialchars($instructor['name']) . " | El Gato's Academy";
$active_page = 'instructors';

// Courses this instructor teaches
$coursesStmt = $conn->prepare('SELECT id, name, category, image, duration, level FROM courses WHERE instructor_id = ? ORDER BY name ASC');
$coursesStmt->bind_param('i', $id);
$coursesStmt->execute();
$instructorCourses = $coursesStmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Real stats: courses created + students taught (distinct students across all their courses)
$courseCount = count($instructorCourses);

$studentsStmt = $conn->prepare('
    SELECT COUNT(DISTINCT enrollments.student_id) AS students_taught
    FROM enrollments
    JOIN courses ON courses.id = enrollments.course_id
    WHERE courses.instructor_id = ?
');
$studentsStmt->bind_param('i', $id);
$studentsStmt->execute();
$studentsTaught = (int) ($studentsStmt->get_result()->fetch_assoc()['students_taught'] ?? 0);

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

$skillTags = [];
if (!empty($instructor['skills'])) {
    $skillTags = array_filter(array_map('trim', explode(',', $instructor['skills'])));
}

require 'includes/header.php';
require 'includes/nav.php';
?>

  <!-- HERO -->
  <section class="hero instructor-details-hero-section">

    <div class="hero-text-col hero-content">
      <p class="hero-eyebrow"><?php echo htmlspecialchars($instructor['position']); ?></p>
      <h1 style="font-size:40px;">
        <?php echo htmlspecialchars($instructor['name']); ?>
      </h1>
      <?php if ($instructor['specialization']): ?>
        <p><?php echo htmlspecialchars($instructor['specialization']); ?></p>
      <?php endif; ?>

      <div class="hero-buttons">
        <a href="instructors.php" class="btn btn-secondary">
          Back to Instructors
        </a>
      </div>
    </div>

    <div class="instructor-details-hero-panel">
      <div class="instructor-details-hero-blob"></div>
      <div class="instructor-details-hero-photo">
        <?php if ($instructor['photo']): ?>
          <img src="<?php echo htmlspecialchars($instructor['photo']); ?>" alt="<?php echo htmlspecialchars($instructor['name']); ?>">
        <?php else: ?>
          <div class="instructor-details-hero-fallback"><?php echo strtoupper(substr($instructor['name'], 0, 1)); ?></div>
        <?php endif; ?>
      </div>
    </div>

  </section>

  <!-- INSTRUCTOR DETAILS -->
  <section class="section instructor-details-section">
    <div class="container instructor-details-layout">

      <div class="instructor-details-main">

        <div class="instructor-details-card">
          <h2>About</h2>
          <p class="instructor-details-bio"><?php echo nl2br(htmlspecialchars($instructor['bio'])); ?></p>

          <?php if (!empty($skillTags)): ?>
            <h3 class="instructor-details-subheading">Expertise & Skills</h3>
            <div class="instructor-skill-tags">
              <?php foreach ($skillTags as $tag): ?>
                <span class="instructor-skill-tag"><?php echo htmlspecialchars($tag); ?></span>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>

        <div class="instructor-details-card">
          <h2>Courses Taught by <?php echo htmlspecialchars($instructor['name']); ?></h2>

          <?php if (empty($instructorCourses)): ?>
            <p class="empty-note">No courses assigned yet.</p>
          <?php else: ?>
            <div class="instructor-course-list">
              <?php foreach ($instructorCourses as $c): ?>
                <a href="course-details.php?id=<?php echo $c['id']; ?>" class="instructor-course-item">
                  <div class="instructor-course-icon">
                    <?php if ($c['image']): ?>
                      <img src="<?php echo htmlspecialchars($c['image']); ?>" alt="">
                    <?php else: ?>
                      <i class="fa-solid <?php echo courseIcon($c['category']); ?>"></i>
                    <?php endif; ?>
                  </div>
                  <div>
                    <h4><?php echo htmlspecialchars($c['name']); ?></h4>
                    <span class="instructor-course-meta">
                      <?php if ($c['duration']): ?><i class="fa-regular fa-clock"></i> <?php echo htmlspecialchars($c['duration']); ?><?php endif; ?>
                      <?php if ($c['level']): ?> &bull; <i class="fa-solid fa-signal"></i> <?php echo htmlspecialchars($c['level']); ?><?php endif; ?>
                    </span>
                  </div>
                </a>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>

      </div>

      <aside class="instructor-details-sidebar">

        <div class="sidebar-card">
          <h3>Instructor Stats</h3>
          <div class="instructor-stat-row">
            <span>Students Taught</span>
            <strong><?php echo $studentsTaught; ?></strong>
          </div>
          <div class="instructor-stat-row">
            <span>Courses Created</span>
            <strong><?php echo $courseCount; ?></strong>
          </div>
        </div>

        <?php if ($instructor['email'] || $instructor['phone']): ?>
        <div class="sidebar-card">
          <h3>Get in Touch</h3>
          <?php if ($instructor['email']): ?>
            <p class="instructor-contact-line"><i class="fa-solid fa-envelope"></i> <?php echo htmlspecialchars($instructor['email']); ?></p>
          <?php endif; ?>
          <?php if ($instructor['phone']): ?>
            <p class="instructor-contact-line"><i class="fa-solid fa-phone"></i> <?php echo htmlspecialchars($instructor['phone']); ?></p>
          <?php endif; ?>
          <a href="contact.php" class="btn btn-primary" style="width:100%; justify-content:center; margin-top:10px;">
            <i class="fa-solid fa-paper-plane"></i> Send Message
          </a>
        </div>
        <?php endif; ?>

        <?php
        // Social icons always show. An icon only becomes a real link if the admin typed a URL
        // for it; otherwise it does nothing when clicked (no link to any outside page).
        $socials = [
            ['linkedin_url', 'LinkedIn', 'fa-brands fa-linkedin'],
            ['twitter_url',  'Twitter',  'fa-brands fa-twitter'],
            ['github_url',   'GitHub',   'fa-brands fa-github'],
            ['website_url',  'Website',  'fa-solid fa-globe'],
        ];
        ?>
        <div class="sidebar-card">
          <h3>Connect</h3>
          <div class="instructor-social-links">
            <?php foreach ($socials as [$field, $label, $icon]): ?>
              <?php $url = trim($instructor[$field] ?? ''); ?>
              <?php if ($url !== ''): ?>
                <a href="<?php echo htmlspecialchars($url); ?>" target="_blank" rel="noopener" aria-label="<?php echo $label; ?>"><i class="<?php echo $icon; ?>"></i></a>
              <?php else: ?>
                <a href="#" onclick="return false;" aria-label="<?php echo $label; ?>"><i class="<?php echo $icon; ?>"></i></a>
              <?php endif; ?>
            <?php endforeach; ?>
          </div>
        </div>

      </aside>

    </div>
  </section>

<?php require 'includes/footer.php'; ?>
