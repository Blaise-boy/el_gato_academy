<?php
$active_page = 'enrollments';
require 'includes/auth-check.php';
require '../config/database.php';

$certMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['issue_certificate'])) {
    $studentId = (int) $_POST['student_id'];
    $courseId = (int) $_POST['course_id'];

    // Confirm this enrollment is actually complete and doesn't already have a certificate.
    $check = $conn->prepare('SELECT progress FROM enrollments WHERE student_id = ? AND course_id = ?');
    $check->bind_param('ii', $studentId, $courseId);
    $check->execute();
    $enrollment = $check->get_result()->fetch_assoc();

    $existing = $conn->prepare('SELECT id FROM certificates WHERE student_id = ? AND course_id = ?');
    $existing->bind_param('ii', $studentId, $courseId);
    $existing->execute();

    if (!$enrollment || (int) $enrollment['progress'] < 100) {
        $certMessage = 'Certificates can only be issued once progress reaches 100%.';
    } elseif ($existing->get_result()->num_rows > 0) {
        $certMessage = 'A certificate has already been issued for this enrollment.';
    } else {
        $certCount = $conn->query('SELECT COUNT(*) AS cnt FROM certificates')->fetch_assoc()['cnt'];
        $certCode = 'CERT-EGA-' . date('Y') . '-' . str_pad($certCount + 1, 4, '0', STR_PAD_LEFT);

        $insert = $conn->prepare('INSERT INTO certificates (student_id, course_id, certificate_code) VALUES (?, ?, ?)');
        $insert->bind_param('iis', $studentId, $courseId, $certCode);
        $insert->execute();

        $certMessage = "Certificate issued: $certCode";
    }
}

$enrollments = $conn->query("
    SELECT enrollments.*, students.first_name, students.last_name, students.reg_number, courses.name AS course_name,
           certificates.certificate_code
    FROM enrollments
    JOIN students ON students.id = enrollments.student_id
    JOIN courses ON courses.id = enrollments.course_id
    LEFT JOIN certificates ON certificates.student_id = enrollments.student_id AND certificates.course_id = enrollments.course_id
    ORDER BY enrollments.enrolled_at DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enrollments | El Gato's Academy Admin</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600;700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../css/admin.css">
</head>
<body>

    <div class="admin-layout">
        <?php require 'includes/sidebar.php'; ?>

        <main class="admin-main">
            <div class="page-header-row">
                <div class="page-header">
                    <h1>Enrollments</h1>
                    <p>Every student currently enrolled in a course.</p>
                </div>
                <a href="enrollment-add.php" class="btn btn-primary">
                    <i class="fa-solid fa-plus"></i> Enroll Student
                </a>
            </div>

            <?php if (isset($_GET['added'])): ?>
                <div class="alert alert-success">Student enrolled successfully.</div>
            <?php endif; ?>
            <?php if (isset($_GET['updated'])): ?>
                <div class="alert alert-success">Enrollment updated successfully.</div>
            <?php endif; ?>
            <?php if (isset($_GET['deleted'])): ?>
                <div class="alert alert-success">Enrollment removed successfully.</div>
            <?php endif; ?>
            <?php if ($certMessage): ?>
                <div class="alert <?php echo str_starts_with($certMessage, 'Certificate issued') ? 'alert-success' : 'alert-error'; ?>"><?php echo htmlspecialchars($certMessage); ?></div>
            <?php endif; ?>

            <div class="table-card full-width">
                <?php if ($enrollments->num_rows === 0): ?>
                    <p class="empty-state">No enrollments yet &mdash; enroll a student above.</p>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Reg Number</th>
                                <th>Student</th>
                                <th>Course</th>
                                <th>Progress</th>
                                <th>Hours Logged</th>
                                <th>Enrolled</th>
                                <th>Certificate</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $enrollments->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['reg_number']); ?></td>
                                <td><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['course_name']); ?></td>
                                <td><?php echo (int) $row['progress']; ?>%</td>
                                <td><?php echo number_format($row['hours_logged'], 2); ?> hrs</td>
                                <td><?php echo date('M j, Y', strtotime($row['enrolled_at'])); ?></td>
                                <td>
                                    <?php if ($row['certificate_code']): ?>
                                        <span class="status-badge status-completed"><?php echo htmlspecialchars($row['certificate_code']); ?></span>
                                    <?php elseif ((int) $row['progress'] >= 100): ?>
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="student_id" value="<?php echo $row['student_id']; ?>">
                                            <input type="hidden" name="course_id" value="<?php echo $row['course_id']; ?>">
                                            <input type="hidden" name="issue_certificate" value="1">
                                            <button type="submit" class="btn-action btn-approve">Issue Certificate</button>
                                        </form>
                                    <?php else: ?>
                                        <span class="text-muted-small">Not completed</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="enrollment-edit.php?id=<?php echo $row['id']; ?>" class="btn-action btn-approve">Edit</a>
                                    <a
                                        href="enrollment-delete.php?id=<?php echo $row['id']; ?>"
                                        class="btn-action btn-delete"
                                        onclick="return confirm('Remove this enrollment?');"
                                    >Remove</a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </main>
    </div>

</body>
</html>
