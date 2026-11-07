<?php
$active_page = 'my-courses';
require 'includes/auth-check.php';
require '../config/database.php';

$studentId = $_SESSION['student_id'];

$coursesStmt = $conn->prepare('
    SELECT enrollments.*, courses.name AS course_name, courses.category, courses.image, courses.duration, courses.level,
           certificates.certificate_code
    FROM enrollments
    JOIN courses ON courses.id = enrollments.course_id
    LEFT JOIN certificates ON certificates.student_id = enrollments.student_id AND certificates.course_id = enrollments.course_id
    WHERE enrollments.student_id = ?
    ORDER BY enrollments.enrolled_at DESC
');
$coursesStmt->bind_param('i', $studentId);
$coursesStmt->execute();
$myCourses = $coursesStmt->get_result()->fetch_all(MYSQLI_ASSOC);

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

function progressLabel($progress) {
    if ($progress >= 100) return 'Completed';
    if ($progress > 0) return 'In Progress';
    return 'Not Started';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Courses | El Gato's Academy</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600;700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../css/admin.css">
    <link rel="stylesheet" href="../css/student.css">
</head>
<body>

    <div class="admin-layout">
        <?php require 'includes/sidebar.php'; ?>

        <main class="admin-main">
            <div class="page-header-row">
                <div class="page-header">
                    <h1>My Courses</h1>
                    <p>Everything you're enrolled in, in one place.</p>
                </div>
            </div>

            <?php if (empty($myCourses)): ?>
                <div class="table-card full-width">
                    <p class="empty-state">You're not enrolled in any courses yet. <a href="../courses.php" style="color:var(--orange);">Browse courses</a> to get started.</p>
                </div>
            <?php else: ?>
                <div class="my-courses-grid">
                    <?php foreach ($myCourses as $c): ?>
                        <div class="my-course-card">
                            <div class="my-course-card-top">
                                <div class="my-course-icon">
                                    <?php if ($c['image']): ?>
                                        <img src="../<?php echo htmlspecialchars($c['image']); ?>" alt="">
                                    <?php else: ?>
                                        <i class="fa-solid <?php echo courseIcon($c['category']); ?>"></i>
                                    <?php endif; ?>
                                </div>
                                <span class="status-badge status-<?php echo $c['progress'] >= 100 ? 'completed' : 'active'; ?>"><?php echo progressLabel($c['progress']); ?></span>
                            </div>

                            <h3><?php echo htmlspecialchars($c['course_name']); ?></h3>
                            <p class="my-course-card-meta">
                                <i class="fa-regular fa-clock"></i> <?php echo htmlspecialchars($c['duration']); ?>
                                <span class="dot">&bull;</span>
                                <i class="fa-solid fa-signal"></i> <?php echo htmlspecialchars($c['level']); ?>
                            </p>

                            <div class="progress-track">
                                <div class="progress-fill" style="width: <?php echo (int) $c['progress']; ?>%;"></div>
                            </div>
                            <p class="my-course-card-percent"><?php echo (int) $c['progress']; ?>% complete</p>

                            <p class="my-course-card-date">Enrolled on <?php echo date('M j, Y', strtotime($c['enrolled_at'])); ?></p>

                            <a href="../course-details.php?id=<?php echo $c['course_id']; ?>" class="btn btn-secondary" style="width:100%; justify-content:center; margin-top:14px;">View Course</a>
                            <?php if ($c['certificate_code']): ?>
                                <a href="../certificate.php?code=<?php echo urlencode($c['certificate_code']); ?>" target="_blank" class="btn btn-primary" style="width:100%; justify-content:center; margin-top:8px;"><i class="fa-solid fa-award"></i> View Certificate</a>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </main>
    </div>

</body>
</html>
