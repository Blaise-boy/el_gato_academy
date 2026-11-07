<?php
session_start();
require 'config/database.php';

$id = $_GET['id'] ?? null;
if (!$id || !is_numeric($id)) {
    header('Location: courses.php');
    exit();
}

$stmt = $conn->prepare('
    SELECT courses.*, instructors.name AS instructor_name, instructors.position AS instructor_position,
           instructors.bio AS instructor_bio, instructors.photo AS instructor_photo
    FROM courses
    LEFT JOIN instructors ON instructors.id = courses.instructor_id
    WHERE courses.id = ?
');
$stmt->bind_param('i', $id);
$stmt->execute();
$course = $stmt->get_result()->fetch_assoc();

if (!$course) {
    header('Location: courses.php');
    exit();
}

$pageTitle = htmlspecialchars($course['name']) . " | El Gato's Academy";
$pageDescription = mb_strimwidth($course['description'], 0, 150, '...');
$active_page = 'courses';

// Real enrolled-student count
$countStmt = $conn->prepare('SELECT COUNT(*) AS total FROM enrollments WHERE course_id = ?');
$countStmt->bind_param('i', $id);
$countStmt->execute();
$enrolledCount = $countStmt->get_result()->fetch_assoc()['total'];

// Curriculum modules, in teaching order
$modStmt = $conn->prepare('SELECT * FROM course_modules WHERE course_id = ? ORDER BY module_order ASC, id ASC');
$modStmt->bind_param('i', $id);
$modStmt->execute();
$modules = $modStmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Is the current visitor logged in as a student?
$isLoggedIn = !empty($_SESSION['student_id']);
$alreadyEnrolled = false;
if ($isLoggedIn) {
    $checkEnroll = $conn->prepare('SELECT id FROM enrollments WHERE student_id = ? AND course_id = ?');
    $checkEnroll->bind_param('ii', $_SESSION['student_id'], $id);
    $checkEnroll->execute();
    $alreadyEnrolled = $checkEnroll->get_result()->num_rows > 0;
}

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

// Tools & Technologies — matched from the real module names already entered for this course.
function techIcon($moduleName) {
    $name = strtolower($moduleName);
    $map = [
        'html'       => ['label' => 'HTML',       'icon' => 'fa-html5',    'brand' => true],
        'css'        => ['label' => 'CSS',         'icon' => 'fa-css3-alt', 'brand' => true],
        'javascript' => ['label' => 'JavaScript',  'icon' => 'fa-js',       'brand' => true],
        'js'         => ['label' => 'JavaScript',  'icon' => 'fa-js',       'brand' => true],
        'php'        => ['label' => 'PHP',         'icon' => 'fa-php',      'brand' => true],
        'mysql'      => ['label' => 'MySQL',       'icon' => 'fa-database', 'brand' => false],
        'sql'        => ['label' => 'SQL',         'icon' => 'fa-database', 'brand' => false],
        'python'     => ['label' => 'Python',      'icon' => 'fa-python',   'brand' => true],
        'react'      => ['label' => 'React',       'icon' => 'fa-react',    'brand' => true],
        'node'       => ['label' => 'Node.js',     'icon' => 'fa-node-js',  'brand' => true],
        'wordpress'  => ['label' => 'WordPress',   'icon' => 'fa-wordpress','brand' => true],
        'figma'      => ['label' => 'Figma',       'icon' => 'fa-figma',    'brand' => true],
        'excel'      => ['label' => 'Excel',       'icon' => 'fa-file-excel', 'brand' => false],
    ];
    foreach ($map as $keyword => $data) {
        if (str_contains($name, $keyword)) {
            return $data;
        }
    }
    return null;
}

$tools = [];
$seenLabels = [];
foreach ($modules as $mod) {
    $tech = techIcon($mod['module_name']);
    if ($tech && !in_array($tech['label'], $seenLabels)) {
        $tools[] = $tech;
        $seenLabels[] = $tech['label'];
    }
}

$learningPoints = [];
if (!empty($course['learning_outcomes'])) {
    $learningPoints = array_filter(array_map('trim', explode("\n", $course['learning_outcomes'])));
}

require 'includes/header.php';
require 'includes/nav.php';
?>

  <section class="section" style="padding-top:40px;">
    <div class="container">

      <div class="course-details-grid">

        <!-- MAIN COLUMN -->
        <div class="course-details-main">

          <div class="course-details-top">
            <div class="course-details-icon <?php echo $course['instructor_id'] % 2 === 0 ? 'alt' : ''; ?>">
              <?php if ($course['image']): ?>
                <img src="<?php echo htmlspecialchars($course['image']); ?>" alt="<?php echo htmlspecialchars($course['name']); ?>">
              <?php else: ?>
                <i class="fa-solid <?php echo courseIcon($course['category']); ?>"></i>
              <?php endif; ?>
            </div>
            <div class="course-details-heading">
              <span class="course-details-category"><?php echo htmlspecialchars(strtoupper($course['category'])); ?></span>
              <h1><?php echo htmlspecialchars($course['name']); ?></h1>
              <p><?php echo htmlspecialchars($course['description']); ?></p>
              <div class="course-details-meta">
                <span><i class="fa-solid fa-user-group"></i> <?php echo (int) $enrolledCount; ?> Students Enrolled</span>
              </div>
            </div>
          </div>

          <?php if (!empty($learningPoints)): ?>
            <div class="course-details-block">
              <h2>What You'll Learn</h2>
              <ul class="learn-list">
                <?php foreach ($learningPoints as $point): ?>
                    <li><i class="fa-solid fa-circle-check"></i> <?php echo htmlspecialchars($point); ?></li>
                <?php endforeach; ?>
              </ul>
            </div>
          <?php endif; ?>

          <?php if (!empty($tools)): ?>
            <div class="course-details-block">
              <h2>Tools &amp; Technologies</h2>
              <div class="tool-grid">
                <?php foreach ($tools as $tool): ?>
                  <div class="tool-chip">
                    <i class="fa-<?php echo $tool['brand'] ? 'brands' : 'solid'; ?> <?php echo $tool['icon']; ?>"></i>
                    <span><?php echo htmlspecialchars($tool['label']); ?></span>
                  </div>
                <?php endforeach; ?>
              </div>
            </div>
          <?php endif; ?>

          <div class="course-details-block">
            <h2>Curriculum</h2>
            <?php if (empty($modules)): ?>
              <p class="empty-note">Curriculum details for this course are coming soon.</p>
            <?php else: ?>
              <div class="curriculum-list">
                <?php foreach ($modules as $i => $mod): ?>
                  <div class="curriculum-item">
                    <span class="curriculum-number"><?php echo $i + 1; ?>.</span>
                    <span class="curriculum-name"><?php echo htmlspecialchars($mod['module_name']); ?></span>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </div>

        </div>

        <!-- SIDEBAR -->
        <div class="course-details-sidebar">

          <div class="sidebar-card">
            <p class="sidebar-fee-label">Course Fee</p>
            <p class="sidebar-fee">&#8358;<?php echo number_format($course['fee'], 0); ?></p>

            <?php if (!$isLoggedIn): ?>
              <a href="login.php" class="btn btn-primary" style="width:100%; justify-content:center;">Enroll Now <i class="fa-solid fa-arrow-right"></i></a>
              <p class="text-muted-small" style="margin-top:10px; text-align:center;">Login to enroll in this course.</p>
            <?php elseif ($alreadyEnrolled): ?>
              <a href="student/dashboard.php" class="btn btn-secondary" style="width:100%; justify-content:center;">Go to My Courses</a>
            <?php else: ?>
              <form method="POST" action="enroll.php">
                <input type="hidden" name="course_id" value="<?php echo $course['id']; ?>">
                <button type="submit" class="btn btn-primary" style="width:100%; justify-content:center;">Enroll Now <i class="fa-solid fa-arrow-right"></i></button>
              </form>
            <?php endif; ?>

            <div class="sidebar-stats">
              <div class="sidebar-stat">
                <i class="fa-regular fa-clock"></i>
                <div><span>Duration</span><strong><?php echo htmlspecialchars($course['duration']); ?></strong></div>
              </div>
              <div class="sidebar-stat">
                <i class="fa-solid fa-signal"></i>
                <div><span>Level</span><strong><?php echo htmlspecialchars($course['level']); ?></strong></div>
              </div>
              <div class="sidebar-stat">
                <i class="fa-solid fa-award"></i>
                <div><span>Certificate</span><strong>Yes, on Completion</strong></div>
              </div>
            </div>
          </div>

          <?php if ($course['instructor_name']): ?>
            <div class="sidebar-card">
              <h3>Instructor</h3>
              <div class="sidebar-instructor">
                <?php if ($course['instructor_photo']): ?>
                  <img src="<?php echo htmlspecialchars($course['instructor_photo']); ?>" alt="<?php echo htmlspecialchars($course['instructor_name']); ?>">
                <?php else: ?>
                  <div class="sidebar-instructor-fallback"><i class="fa-solid fa-user"></i></div>
                <?php endif; ?>
                <div>
                  <strong><?php echo htmlspecialchars($course['instructor_name']); ?></strong>
                  <span><?php echo htmlspecialchars($course['instructor_position']); ?></span>
                </div>
              </div>
              <?php if ($course['instructor_bio']): ?>
                <p class="sidebar-instructor-bio"><?php echo htmlspecialchars($course['instructor_bio']); ?></p>
              <?php endif; ?>
            </div>
          <?php endif; ?>

          <div class="sidebar-card">
            <h3>Share this Course</h3>
            <div class="share-row">
              <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode('https://elgatosacademy.ng/course-details.php?id=' . $course['id']); ?>" target="_blank" aria-label="Share on Facebook"><i class="fa-brands fa-facebook-f"></i></a>
              <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode('https://elgatosacademy.ng/course-details.php?id=' . $course['id']); ?>&text=<?php echo urlencode($course['name']); ?>" target="_blank" aria-label="Share on Twitter"><i class="fa-brands fa-twitter"></i></a>
              <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?php echo urlencode('https://elgatosacademy.ng/course-details.php?id=' . $course['id']); ?>" target="_blank" aria-label="Share on LinkedIn"><i class="fa-brands fa-linkedin-in"></i></a>
              <button type="button" class="share-copy-btn" onclick="copyCourseLink(this)" aria-label="Copy link"><i class="fa-solid fa-link"></i></button>
            </div>
          </div>

        </div>

      </div>
    </div>
  </section>

  <script>
    function copyCourseLink(btn) {
      navigator.clipboard.writeText(window.location.href).then(() => {
        const icon = btn.querySelector('i');
        icon.classList.replace('fa-link', 'fa-check');
        setTimeout(() => icon.classList.replace('fa-check', 'fa-link'), 1500);
      });
    }
  </script>

<?php require 'includes/footer.php'; ?>
