<?php
$active_page = 'enrollments';
require 'includes/auth-check.php';
require '../config/database.php';

$id = $_GET['id'] ?? null;
if (!$id || !is_numeric($id)) {
    header('Location: enrollments.php');
    exit();
}

$stmt = $conn->prepare('
    SELECT enrollments.*, students.first_name, students.last_name, courses.name AS course_name
    FROM enrollments
    JOIN students ON students.id = enrollments.student_id
    JOIN courses ON courses.id = enrollments.course_id
    WHERE enrollments.id = ?
');
$stmt->bind_param('i', $id);
$stmt->execute();
$enrollment = $stmt->get_result()->fetch_assoc();

if (!$enrollment) {
    header('Location: enrollments.php');
    exit();
}

$errors = [];
$progress = $enrollment['progress'];
$hoursLogged = $enrollment['hours_logged'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $progress = trim($_POST['progress'] ?? '');
    $hoursLogged = trim($_POST['hours_logged'] ?? '');

    if ($progress === '' || !is_numeric($progress) || $progress < 0 || $progress > 100) {
        $errors[] = 'Progress must be a number between 0 and 100.';
    }
    if ($hoursLogged === '' || !is_numeric($hoursLogged) || $hoursLogged < 0) {
        $errors[] = 'Hours logged must be a positive number.';
    }

    if (empty($errors)) {
        $update = $conn->prepare('UPDATE enrollments SET progress = ?, hours_logged = ? WHERE id = ?');
        $update->bind_param('idi', $progress, $hoursLogged, $id);
        $update->execute();

        header('Location: enrollments.php?updated=1');
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Enrollment | El Gato's Academy Admin</title>

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
                    <h1>Edit Enrollment</h1>
                    <p>
                        <?php echo htmlspecialchars($enrollment['first_name'] . ' ' . $enrollment['last_name']); ?>
                        &mdash; <?php echo htmlspecialchars($enrollment['course_name']); ?>
                    </p>
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
                <form method="POST" action="enrollment-edit.php?id=<?php echo $enrollment['id']; ?>">

                    <div class="form-grid-2">
                        <div class="form-group">
                            <label for="progress">Progress (%)</label>
                            <input type="number" id="progress" name="progress" min="0" max="100" value="<?php echo htmlspecialchars($progress); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="hours_logged">Hours Logged</label>
                            <input type="number" id="hours_logged" name="hours_logged" step="0.5" min="0" value="<?php echo htmlspecialchars($hoursLogged); ?>" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width:100%;">Update Enrollment</button>
                </form>
            </div>
        </main>
    </div>

</body>
</html>
