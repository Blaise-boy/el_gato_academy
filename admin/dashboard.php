<?php
$active_page = 'dashboard';
require 'includes/auth-check.php';
require '../config/database.php';

// Stat cards
$totalStudents = $conn->query("SELECT COUNT(*) AS cnt FROM students")->fetch_assoc()['cnt'];
$totalCourses = $conn->query("SELECT COUNT(*) AS cnt FROM courses")->fetch_assoc()['cnt'];
$totalInstructors = $conn->query("SELECT COUNT(*) AS cnt FROM instructors")->fetch_assoc()['cnt'];
$totalEnrollments = $conn->query("SELECT COUNT(*) AS cnt FROM enrollments")->fetch_assoc()['cnt'];
$pendingApplications = $conn->query("SELECT COUNT(*) AS cnt FROM applications WHERE status = 'pending'")->fetch_assoc()['cnt'];

// Enrollment overview - last 30 days, grouped by day
$enrollmentTrend = [];
$trendResult = $conn->query("
    SELECT DATE(enrolled_at) AS day, COUNT(*) AS cnt
    FROM enrollments
    WHERE enrolled_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
    GROUP BY DATE(enrolled_at)
    ORDER BY day ASC
");
while ($row = $trendResult->fetch_assoc()) {
    $enrollmentTrend[] = $row;
}

// Student distribution by course
$distribution = [];
$distResult = $conn->query("
    SELECT courses.name AS course_name, COUNT(enrollments.id) AS cnt
    FROM courses
    LEFT JOIN enrollments ON enrollments.course_id = courses.id
    GROUP BY courses.id
    ORDER BY cnt DESC
");
while ($row = $distResult->fetch_assoc()) {
    $distribution[] = $row;
}

// Recent enrollments
$recentEnrollments = $conn->query("
    SELECT students.first_name, students.last_name, courses.name AS course_name,
           enrollments.enrolled_at, enrollments.progress
    FROM enrollments
    JOIN students ON students.id = enrollments.student_id
    JOIN courses ON courses.id = enrollments.course_id
    ORDER BY enrollments.enrolled_at DESC
    LIMIT 5
");

// Top courses by enrollment count
$topCourses = $conn->query("
    SELECT courses.name AS course_name, COUNT(enrollments.id) AS student_count
    FROM courses
    LEFT JOIN enrollments ON enrollments.course_id = courses.id
    GROUP BY courses.id
    ORDER BY student_count DESC
    LIMIT 5
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | El Gato's Academy Admin</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600;700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../css/admin.css">
</head>
<body>

    <div class="admin-layout">
        <?php require 'includes/sidebar.php'; ?>

        <main class="admin-main">
            <div class="page-header">
                <h1>Dashboard Overview</h1>
                <p>Welcome back, <?php echo htmlspecialchars($_SESSION['admin_name']); ?>. Here's what's happening at El Gato's Academy.</p>
            </div>

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon"><i class="fa-solid fa-user-graduate"></i></div>
                    <div>
                        <p class="stat-label">Total Students</p>
                        <h3 class="stat-value"><?php echo $totalStudents; ?></h3>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon"><i class="fa-solid fa-book"></i></div>
                    <div>
                        <p class="stat-label">Total Courses</p>
                        <h3 class="stat-value"><?php echo $totalCourses; ?></h3>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon"><i class="fa-solid fa-chalkboard-user"></i></div>
                    <div>
                        <p class="stat-label">Total Instructors</p>
                        <h3 class="stat-value"><?php echo $totalInstructors; ?></h3>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon"><i class="fa-solid fa-graduation-cap"></i></div>
                    <div>
                        <p class="stat-label">Total Enrollments</p>
                        <h3 class="stat-value"><?php echo $totalEnrollments; ?></h3>
                    </div>
                </div>
            </div>

            <div class="charts-grid">
                <div class="chart-card">
                    <h3>Enrollment Overview <span class="chart-subtitle">Last 30 days</span></h3>
                    <div class="chart-canvas-wrap chart-canvas-line">
                        <canvas id="enrollmentChart"></canvas>
                    </div>
                </div>

                <div class="chart-card donut-card">
                    <h3>Student Distribution by Course</h3>
                    <div class="chart-canvas-wrap chart-canvas-donut">
                        <canvas id="distributionChart"></canvas>
                    </div>
                    <ul class="legend-list">
                        <?php
                        $colors = ['#F97316', '#FB923C', '#3B82F6', '#8B5CF6', '#10B981', '#6B7280', '#EC4899', '#EAB308'];
                        $i = 0;
                        foreach ($distribution as $d):
                            $color = $colors[$i % count($colors)];
                            $i++;
                        ?>
                        <li>
                            <span><span class="legend-dot" style="background:<?php echo $color; ?>"></span><?php echo htmlspecialchars($d['course_name']); ?></span>
                            <span><?php echo $d['cnt']; ?></span>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>

            <div class="tables-grid">
                <div class="table-card">
                    <h3>Recent Enrollments</h3>
                    <table>
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Course</th>
                                <th>Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $recentEnrollments->fetch_assoc()):
                                $isCompleted = $row['progress'] >= 100;
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['course_name']); ?></td>
                                <td><?php echo date('M j, Y', strtotime($row['enrolled_at'])); ?></td>
                                <td>
                                    <span class="status-badge <?php echo $isCompleted ? 'status-completed' : 'status-active'; ?>">
                                        <?php echo $isCompleted ? 'Completed' : 'Active'; ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>

                <div class="list-card">
                    <h3>Top Courses</h3>
                    <ol class="top-course-list">
                        <?php while ($row = $topCourses->fetch_assoc()): ?>
                        <li>
                            <span class="course-rank-name"><?php echo htmlspecialchars($row['course_name']); ?></span>
                            <span class="course-rank-count"><?php echo $row['student_count']; ?> Students</span>
                        </li>
                        <?php endwhile; ?>
                    </ol>
                </div>

                <div class="pending-card">
                    <h3>Pending Items</h3>
                    <div class="pending-row">
                        <span><i class="fa-solid fa-file-lines"></i> Pending Applications</span>
                        <span class="pending-badge"><?php echo $pendingApplications; ?></span>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    <script>
        const enrollmentLabels = <?php echo json_encode(array_column($enrollmentTrend, 'day')); ?>;
        const enrollmentData = <?php echo json_encode(array_column($enrollmentTrend, 'cnt')); ?>;

        new Chart(document.getElementById('enrollmentChart'), {
            type: 'line',
            data: {
                labels: enrollmentLabels,
                datasets: [{
                    label: 'Enrollments',
                    data: enrollmentData,
                    borderColor: '#F97316',
                    backgroundColor: 'rgba(249,115,22,0.15)',
                    fill: true,
                    tension: 0.4,
                    pointRadius: 3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { ticks: { color: '#9CA3AF' }, grid: { color: '#2a2a2a' } },
                    y: { ticks: { color: '#9CA3AF' }, grid: { color: '#2a2a2a' }, beginAtZero: true }
                }
            }
        });

        const distLabels = <?php echo json_encode(array_column($distribution, 'course_name')); ?>;
        const distData = <?php echo json_encode(array_column($distribution, 'cnt')); ?>;

        new Chart(document.getElementById('distributionChart'), {
            type: 'doughnut',
            data: {
                labels: distLabels,
                datasets: [{
                    data: distData,
                    backgroundColor: ['#F97316', '#FB923C', '#3B82F6', '#8B5CF6', '#10B981', '#6B7280', '#EC4899', '#EAB308'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: { legend: { display: false } }
            }
        });
    </script>

</body>
</html>
