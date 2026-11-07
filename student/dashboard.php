<?php
$active_page = 'dashboard';
require 'includes/auth-check.php';
require '../config/database.php';

$studentId = $_SESSION['student_id'];

$stmt = $conn->prepare('SELECT * FROM students WHERE id = ?');
$stmt->bind_param('i', $studentId);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();

if (!$student) {
    header('Location: ../login.php');
    exit();
}

// Stat cards — all real counts/sums for this student.
$statsStmt = $conn->prepare('
    SELECT COUNT(*) AS enrolled_courses, COALESCE(SUM(hours_logged), 0) AS total_hours
    FROM enrollments WHERE student_id = ?
');
$statsStmt->bind_param('i', $studentId);
$statsStmt->execute();
$stats = $statsStmt->get_result()->fetch_assoc();

$certStmt = $conn->prepare('SELECT COUNT(*) AS total FROM certificates WHERE student_id = ?');
$certStmt->bind_param('i', $studentId);
$certStmt->execute();
$certCount = $certStmt->get_result()->fetch_assoc()['total'];

// My Courses — real enrollments, most recent first.
$coursesStmt = $conn->prepare('
    SELECT enrollments.*, courses.name AS course_name, courses.category, courses.image
    FROM enrollments
    JOIN courses ON courses.id = enrollments.course_id
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
    <title>My Dashboard | El Gato's Academy</title>

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
                    <h1>Welcome back, <span class="accent-text"><?php echo htmlspecialchars($student['first_name']); ?></span></h1>
                    <p>Keep learning, keep growing. You're doing great!</p>
                </div>
            </div>

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon"><i class="fa-solid fa-book"></i></div>
                    <div>
                        <p class="stat-label">Enrolled Courses</p>
                        <p class="stat-value"><?php echo (int) $stats['enrolled_courses']; ?></p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon"><i class="fa-regular fa-clock"></i></div>
                    <div>
                        <p class="stat-label">Hours Logged</p>
                        <p class="stat-value"><?php echo rtrim(rtrim(number_format($stats['total_hours'], 1), '0'), '.'); ?>h</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon"><i class="fa-solid fa-award"></i></div>
                    <div>
                        <p class="stat-label">Certificates Earned</p>
                        <p class="stat-value"><?php echo (int) $certCount; ?></p>
                    </div>
                </div>
            </div>

            <div class="charts-grid">

                <div class="table-card">
                    <h3>My Courses</h3>
                    <?php if (empty($myCourses)): ?>
                        <p class="empty-state">You're not enrolled in any courses yet. <a href="../courses.php" style="color:var(--orange);">Browse courses</a> to get started.</p>
                    <?php else: ?>
                        <?php foreach ($myCourses as $c): ?>
                            <div class="my-course-row">
                                <div class="my-course-icon">
                                    <?php if ($c['image']): ?>
                                        <img src="../<?php echo htmlspecialchars($c['image']); ?>" alt="">
                                    <?php else: ?>
                                        <i class="fa-solid <?php echo courseIcon($c['category']); ?>"></i>
                                    <?php endif; ?>
                                </div>
                                <div class="my-course-info">
                                    <strong><?php echo htmlspecialchars($c['course_name']); ?></strong>
                                    <span>Enrolled on <?php echo date('M j, Y', strtotime($c['enrolled_at'])); ?></span>
                                </div>
                                <div class="my-course-progress">
                                    <span class="status-badge status-<?php echo $c['progress'] >= 100 ? 'completed' : 'active'; ?>"><?php echo progressLabel($c['progress']); ?></span>
                                    <div class="progress-track">
                                        <div class="progress-fill" style="width: <?php echo (int) $c['progress']; ?>%;"></div>
                                    </div>
                                    <span class="progress-percent"><?php echo (int) $c['progress']; ?>%</span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <div class="list-card">
                    <h3>Student Profile</h3>
                    <div class="student-profile-photo">
                        <?php if (!empty($student['photo'])): ?>
                            <img src="../<?php echo htmlspecialchars($student['photo']); ?>" alt="">
                        <?php else: ?>
                            <div class="student-photo-fallback"><?php echo strtoupper(substr($student['first_name'], 0, 1)); ?></div>
                        <?php endif; ?>
                    </div>
                    <p class="student-profile-name"><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></p>

                    <div class="student-detail-row">
                        <i class="fa-solid fa-envelope"></i>
                        <div><span>Email</span><strong><?php echo htmlspecialchars($student['email']); ?></strong></div>
                    </div>
                    <div class="student-detail-row">
                        <i class="fa-solid fa-id-card"></i>
                        <div><span>Student ID</span><strong><?php echo htmlspecialchars($student['reg_number']); ?></strong></div>
                    </div>
                    <div class="student-detail-row">
                        <i class="fa-solid fa-circle-check"></i>
                        <div><span>Status</span><strong class="status-badge status-<?php echo $student['status'] === 'active' ? 'active' : 'suspended'; ?>"><?php echo htmlspecialchars(ucfirst($student['status'])); ?></strong></div>
                    </div>
                </div>

            </div>
        </main>
    </div>

</body>
</html>
