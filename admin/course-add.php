<?php
$active_page = 'courses';
require 'includes/auth-check.php';
require '../config/database.php';
require 'includes/upload-helper.php';

$errors = [];
$name = $category = $description = $duration = $level = $fee = $learning_outcomes = '';
$instructor_id = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $duration = trim($_POST['duration'] ?? '');
    $level = trim($_POST['level'] ?? '');
    $fee = trim($_POST['fee'] ?? '');
    $learning_outcomes = trim($_POST['learning_outcomes'] ?? '');
    $instructor_id = trim($_POST['instructor_id'] ?? '');

    if ($name === '')                      $errors[] = 'Course name is required.';
    if ($category === '')                  $errors[] = 'Category is required.';
    if ($description === '')               $errors[] = 'Description is required.';
    if ($duration === '')                  $errors[] = 'Duration is required.';
    if ($level === '')                     $errors[] = 'Level is required.';
    if ($fee === '' || !is_numeric($fee))  $errors[] = 'A valid fee is required.';

    $upload = handleImageUpload('image', 'courses');
    if ($upload['error']) {
        $errors[] = $upload['error'];
    }

    if (empty($errors)) {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9]+/', '-', $name), '-'));
        $imagePath = $upload['success'] ? $upload['path'] : '';
        $instructorIdValue = $instructor_id !== '' ? (int) $instructor_id : null;
        $learningOutcomesValue = $learning_outcomes !== '' ? $learning_outcomes : null;

        $stmt = $conn->prepare('
            INSERT INTO courses (name, slug, category, description, duration, level, fee, image, instructor_id, learning_outcomes)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ');
        $stmt->bind_param('ssssssdsis', $name, $slug, $category, $description, $duration, $level, $fee, $imagePath, $instructorIdValue, $learningOutcomesValue);
        $stmt->execute();

        header('Location: courses.php?added=1');
        exit();
    }
}

$categories = ['Technology', 'Design', 'Business', 'Data', 'Digital Skills', 'Professional Development'];
$levels = ['Beginner', 'Intermediate', 'Advanced', 'Beginner to Advanced'];
$instructors = $conn->query("SELECT id, name FROM instructors ORDER BY name ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Course | El Gato's Academy Admin</title>

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
                    <h1>Add Course</h1>
                    <p>Create a new course for the academy.</p>
                </div>
                <a href="courses.php" class="btn btn-secondary">Back to Courses</a>
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
                <form method="POST" action="course-add.php" enctype="multipart/form-data">

                    <div class="form-group">
                        <label for="name">Course Name</label>
                        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>" required>
                    </div>

                    <div class="form-grid-2">
                        <div class="form-group">
                            <label for="category">Category</label>
                            <select id="category" name="category" required>
                                <option value="">Select category</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo $cat; ?>" <?php echo $category === $cat ? 'selected' : ''; ?>><?php echo $cat; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="level">Level</label>
                            <select id="level" name="level" required>
                                <option value="">Select level</option>
                                <?php foreach ($levels as $lvl): ?>
                                    <option value="<?php echo $lvl; ?>" <?php echo $level === $lvl ? 'selected' : ''; ?>><?php echo $lvl; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-grid-2">
                        <div class="form-group">
                            <label for="duration">Duration</label>
                            <input type="text" id="duration" name="duration" value="<?php echo htmlspecialchars($duration); ?>" placeholder="e.g. 6 Months" required>
                        </div>
                        <div class="form-group">
                            <label for="fee">Fee (&#8358;)</label>
                            <input type="number" id="fee" name="fee" value="<?php echo htmlspecialchars($fee); ?>" placeholder="e.g. 250000" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="instructor_id">Instructor</label>
                        <select id="instructor_id" name="instructor_id">
                            <option value="">Not assigned yet</option>
                            <?php while ($inst = $instructors->fetch_assoc()): ?>
                                <option value="<?php echo $inst['id']; ?>" <?php echo (string) $instructor_id === (string) $inst['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($inst['name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                        <?php if ($instructors->num_rows === 0): ?>
                            <span class="text-muted-small">No instructors added yet &mdash; add one under Instructors first.</span>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" rows="4" required><?php echo htmlspecialchars($description); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="learning_outcomes">What You'll Learn</label>
                        <textarea id="learning_outcomes" name="learning_outcomes" rows="5" placeholder="One line per point, e.g.&#10;Build responsive and user-friendly websites&#10;Create dynamic web applications with PHP"><?php echo htmlspecialchars($learning_outcomes); ?></textarea>
                        <span class="text-muted-small">One point per line &mdash; shown as a bullet list on the course page.</span>
                    </div>

                    <div class="form-group">
                        <label for="image">Course Image</label>
                        <input type="file" id="image" name="image" accept="image/*">
                    </div>

                    <button type="submit" class="btn btn-primary" style="width:100%;">Save Course</button>
                </form>
            </div>
        </main>
    </div>

</body>
</html>
