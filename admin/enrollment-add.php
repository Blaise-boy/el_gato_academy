<?php
$active_page = 'enrollments';
require 'includes/auth-check.php';
require '../config/database.php';

$errors = [];
$studentId = $courseId = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $studentId = $_POST['student_id'] ?? '';
    $courseId = $_POST['course_id'] ?? '';

    if ($studentId === '') $errors[] = 'Please select a student.';
    if ($courseId === '')  $errors[] = 'Please select a course.';

    if (empty($errors)) {
        $check = $conn->prepare('SELECT id FROM enrollments WHERE student_id = ? AND course_id = ?');
        $check->bind_param('ii', $studentId, $courseId);
        $check->execute();

        if ($check->get_result()->num_rows > 0) {
            $errors[] = 'This student is already enrolled in that course.';
        } else {
            $insert = $conn->prepare('
                INSERT INTO enrollments (student_id, course_id, progress, hours_logged)
                VALUES (?, ?, 0, 0.00)
            ');
            $insert->bind_param('ii', $studentId, $courseId);
            $insert->execute();

            header('Location: enrollments.php?added=1');
            exit();
        }
    }
}

$students = $conn->query("SELECT id, reg_number, first_name, last_name FROM students ORDER BY first_name ASC");
$courses = $conn->query("SELECT id, name FROM courses ORDER BY name ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enroll Student | El Gato's Academy Admin</title>

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
                    <h1>Enroll Student</h1>
                    <p>Place a student into a course.</p>
                </div>
                <a href="enrollments.php" class="btn btn-secondary">Back to Enrollments</a>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <ul class="alert-list">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <div class="form-card full-width">
                <?php if ($students->num_rows === 0 || $courses->num_rows === 0): ?>
                    <p class="empty-state">
                        You need at least one student and one course before you can create an enrollment.
                    </p>
                <?php else: ?>
                    <form method="POST" action="enrollment-add.php">

                        <div class="form-group">
                            <label for="student_id">Student</label>
                            <select id="student_id" name="student_id" required>
                                <option value="">Select a student</option>
                                <?php while ($s = $students->fetch_assoc()): ?>
                                    <option value="<?php echo $s['id']; ?>" <?php echo (string) $studentId === (string) $s['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($s['reg_number'] . ' — ' . $s['first_name'] . ' ' . $s['last_name']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="course_id">Course</label>
                            <select id="course_id" name="course_id" required>
                                <option value="">Select a course</option>
                                <?php while ($c = $courses->fetch_assoc()): ?>
                                    <option value="<?php echo $c['id']; ?>" <?php echo (string) $courseId === (string) $c['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($c['name']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary" style="width:100%;">Enroll Student</button>
                    </form>
                <?php endif; ?>
            </div>
        </main>
    </div>

</body>
</html>
