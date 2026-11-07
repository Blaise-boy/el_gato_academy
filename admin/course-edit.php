<?php
$active_page = 'courses';
require 'includes/auth-check.php';
require '../config/database.php';
require 'includes/upload-helper.php';

$id = $_GET['id'] ?? null;
if (!$id || !is_numeric($id)) {
    header('Location: courses.php');
    exit();
}

$stmt = $conn->prepare('SELECT * FROM courses WHERE id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
$course = $stmt->get_result()->fetch_assoc();

if (!$course) {
    header('Location: courses.php');
    exit();
}

$errors = [];
$name = $course['name'];
$category = $course['category'];
$description = $course['description'];
$duration = $course['duration'];
$level = $course['level'];
$fee = $course['fee'];
$learning_outcomes = $course['learning_outcomes'];
$instructor_id = $course['instructor_id'];

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
        $imagePath = $course['image'];

        if ($upload['success']) {
            deleteImageFile($course['image']);
            $imagePath = $upload['path'];
        }

        $instructorIdValue = $instructor_id !== '' ? (int) $instructor_id : null;
        $learningOutcomesValue = $learning_outcomes !== '' ? $learning_outcomes : null;

        $update = $conn->prepare('
            UPDATE courses
            SET name = ?, slug = ?, category = ?, description = ?, duration = ?, level = ?, fee = ?, image = ?, instructor_id = ?, learning_outcomes = ?
            WHERE id = ?
        ');
        $update->bind_param('ssssssdsisi', $name, $slug, $category, $description, $duration, $level, $fee, $imagePath, $instructorIdValue, $learningOutcomesValue, $id);
        $update->execute();

        // --- Sync curriculum modules ---
        $moduleIds = $_POST['module_id'] ?? [];
        $moduleNames = $_POST['module_name'] ?? [];
        $moduleOrders = $_POST['module_order'] ?? [];
        $moduleDeleted = $_POST['module_deleted'] ?? [];

        foreach ($moduleNames as $index => $modName) {
            $modName = trim($modName);
            $modOrder = (int) ($moduleOrders[$index] ?? 0);
            $modId = $moduleIds[$index] ?? '';
            $isDeleted = isset($moduleDeleted[$index]) && $moduleDeleted[$index] === '1';

            if ($modId !== '' && is_numeric($modId)) {
                // Existing module
                if ($isDeleted) {
                    $delStmt = $conn->prepare('DELETE FROM course_modules WHERE id = ? AND course_id = ?');
                    $delStmt->bind_param('ii', $modId, $id);
                    $delStmt->execute();
                } elseif ($modName !== '') {
                    $updStmt = $conn->prepare('UPDATE course_modules SET module_name = ?, module_order = ? WHERE id = ? AND course_id = ?');
                    $updStmt->bind_param('siii', $modName, $modOrder, $modId, $id);
                    $updStmt->execute();
                }
            } elseif (!$isDeleted && $modName !== '') {
                // New module
                $insStmt = $conn->prepare('INSERT INTO course_modules (course_id, module_name, module_order) VALUES (?, ?, ?)');
                $insStmt->bind_param('isi', $id, $modName, $modOrder);
                $insStmt->execute();
            }
        }

        header('Location: courses.php?updated=1');
        exit();
    }
}

$categories = ['Technology', 'Design', 'Business', 'Data', 'Digital Skills', 'Professional Development'];
$levels = ['Beginner', 'Intermediate', 'Advanced', 'Beginner to Advanced'];
$instructors = $conn->query("SELECT id, name FROM instructors ORDER BY name ASC");

$modulesResult = $conn->prepare('SELECT * FROM course_modules WHERE course_id = ? ORDER BY module_order ASC, id ASC');
$modulesResult->bind_param('i', $id);
$modulesResult->execute();
$modules = $modulesResult->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Course | El Gato's Academy Admin</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600;700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../css/admin.css">
    <style>
        .module-row {
            display: grid;
            grid-template-columns: 1fr 100px auto;
            gap: 10px;
            margin-bottom: 10px;
            align-items: center;
        }
        @media (max-width: 600px) {
            .module-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

    <div class="admin-layout">
        <?php require 'includes/sidebar.php'; ?>

        <main class="admin-main">
            <div class="page-header-row">
                <div class="page-header">
                    <h1>Edit Course</h1>
                    <p>Update details for <?php echo htmlspecialchars($course['name']); ?>.</p>
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
                <form method="POST" action="course-edit.php?id=<?php echo $course['id']; ?>" enctype="multipart/form-data">

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
                            <input type="text" id="duration" name="duration" value="<?php echo htmlspecialchars($duration); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="fee">Fee (&#8358;)</label>
                            <input type="number" id="fee" name="fee" value="<?php echo htmlspecialchars($fee); ?>" required>
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
                        <textarea id="learning_outcomes" name="learning_outcomes" rows="5" placeholder="One line per point"><?php echo htmlspecialchars($learning_outcomes); ?></textarea>
                        <span class="text-muted-small">One point per line &mdash; shown as a bullet list on the course page.</span>
                    </div>

                    <div class="form-group">
                        <label>Curriculum Modules</label>
                        <span class="text-muted-small" style="display:block; margin-bottom:10px;">The topics covered in this course, in teaching order &mdash; shown on the course page's Curriculum list.</span>

                        <div id="module-rows">
                            <?php foreach ($modules as $i => $mod): ?>
                                <div class="module-row" data-row>
                                    <input type="hidden" name="module_id[]" value="<?php echo $mod['id']; ?>">
                                    <input type="hidden" name="module_deleted[]" value="0" class="module-deleted-flag">
                                    <input type="text" name="module_name[]" value="<?php echo htmlspecialchars($mod['module_name']); ?>" placeholder="e.g. HTML" class="module-name-input">
                                    <input type="number" name="module_order[]" value="<?php echo (int) $mod['module_order']; ?>" placeholder="Order" class="module-order-input">
                                    <button type="button" class="btn btn-secondary module-remove-btn" onclick="removeModuleRow(this)">Remove</button>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <button type="button" class="btn btn-secondary" onclick="addModuleRow()">+ Add Module</button>
                    </div>

                    <div class="form-group">
                        <label for="image">Course Image</label>
                        <?php if ($course['image']): ?>
                            <img class="admin-thumb" src="../<?php echo htmlspecialchars($course['image']); ?>" alt="" style="display:block; margin-bottom:10px;">
                        <?php endif; ?>
                        <input type="file" id="image" name="image" accept="image/*">
                        <span class="text-muted-small">Leave blank to keep the current image.</span>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width:100%;">Update Course</button>
                </form>
            </div>
        </main>
    </div>

    <script>
        function addModuleRow() {
            const wrap = document.getElementById('module-rows');
            const row = document.createElement('div');
            row.className = 'module-row';
            row.setAttribute('data-row', '');
            row.innerHTML = `
                <input type="hidden" name="module_id[]" value="">
                <input type="hidden" name="module_deleted[]" value="0" class="module-deleted-flag">
                <input type="text" name="module_name[]" value="" placeholder="e.g. HTML" class="module-name-input">
                <input type="number" name="module_order[]" value="${wrap.children.length + 1}" placeholder="Order" class="module-order-input">
                <button type="button" class="btn btn-secondary module-remove-btn" onclick="removeModuleRow(this)">Remove</button>
            `;
            wrap.appendChild(row);
        }

        function removeModuleRow(btn) {
            const row = btn.closest('[data-row]');
            const idField = row.querySelector('input[name="module_id[]"]');
            if (idField && idField.value !== '') {
                // Existing module in the database — mark for deletion, keep in form, hide visually.
                row.querySelector('.module-deleted-flag').value = '1';
                row.style.display = 'none';
            } else {
                // Brand new row that was never saved — just remove it.
                row.remove();
            }
        }
    </script>

</body>
</html>
