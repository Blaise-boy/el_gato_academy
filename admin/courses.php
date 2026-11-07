<?php
$active_page = 'courses';
require 'includes/auth-check.php';
require '../config/database.php';

$courses = $conn->query("SELECT * FROM courses ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Courses | El Gato's Academy Admin</title>

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
                    <h1>Courses</h1>
                    <p>Manage every course offered by the academy.</p>
                </div>
                <a href="course-add.php" class="btn btn-primary">
                    <i class="fa-solid fa-plus"></i> Add Course
                </a>
            </div>

            <?php if (isset($_GET['added'])): ?>
                <div class="alert alert-success">Course added successfully.</div>
            <?php endif; ?>
            <?php if (isset($_GET['updated'])): ?>
                <div class="alert alert-success">Course updated successfully.</div>
            <?php endif; ?>
            <?php if (isset($_GET['deleted'])): ?>
                <div class="alert alert-success">Course deleted successfully.</div>
            <?php endif; ?>

            <div class="table-card full-width">
                <?php if ($courses->num_rows === 0): ?>
                    <p class="empty-state">No courses yet &mdash; add your first one above.</p>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Image</th>
                                <th>Name</th>
                                <th>Category</th>
                                <th>Duration</th>
                                <th>Level</th>
                                <th>Fee</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $courses->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <?php if ($row['image']): ?>
                                        <img class="admin-thumb" src="../<?php echo htmlspecialchars($row['image']); ?>" alt="">
                                    <?php else: ?>
                                        <span class="text-muted-small">No image</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($row['name']); ?></td>
                                <td><?php echo htmlspecialchars($row['category']); ?></td>
                                <td><?php echo htmlspecialchars($row['duration']); ?></td>
                                <td><?php echo htmlspecialchars($row['level']); ?></td>
                                <td>&#8358;<?php echo number_format($row['fee']); ?></td>
                                <td>
                                    <a href="course-edit.php?id=<?php echo $row['id']; ?>" class="btn-action btn-approve">Edit</a>
                                    <a
                                        href="course-delete.php?id=<?php echo $row['id']; ?>"
                                        class="btn-action btn-delete"
                                        onclick="return confirm('Delete this course? This cannot be undone.');"
                                    >Delete</a>
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
