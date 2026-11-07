<?php
$active_page = 'instructors';
require 'includes/auth-check.php';
require '../config/database.php';

$instructors = $conn->query("SELECT * FROM instructors ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instructors | El Gato's Academy Admin</title>

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
                    <h1>Instructors</h1>
                    <p>Manage the instructors shown on the public site.</p>
                </div>
                <a href="instructor-add.php" class="btn btn-primary">
                    <i class="fa-solid fa-plus"></i> Add Instructor
                </a>
            </div>

            <?php if (isset($_GET['added'])): ?>
                <div class="alert alert-success">Instructor added successfully.</div>
            <?php endif; ?>
            <?php if (isset($_GET['updated'])): ?>
                <div class="alert alert-success">Instructor updated successfully.</div>
            <?php endif; ?>
            <?php if (isset($_GET['deleted'])): ?>
                <div class="alert alert-success">Instructor deleted successfully.</div>
            <?php endif; ?>

            <div class="table-card full-width">
                <?php if ($instructors->num_rows === 0): ?>
                    <p class="empty-state">No instructors yet &mdash; add your first one above.</p>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Photo</th>
                                <th>Name</th>
                                <th>Position</th>
                                <th>Specialization</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $instructors->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <?php if ($row['photo']): ?>
                                        <img class="admin-thumb admin-thumb-round" src="../<?php echo htmlspecialchars($row['photo']); ?>" alt="">
                                    <?php else: ?>
                                        <span class="text-muted-small">No photo</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($row['name']); ?></td>
                                <td><?php echo htmlspecialchars($row['position']); ?></td>
                                <td><?php echo htmlspecialchars($row['specialization']); ?></td>
                                <td>
                                    <a href="instructor-edit.php?id=<?php echo $row['id']; ?>" class="btn-action btn-approve">Edit</a>
                                    <a
                                        href="instructor-delete.php?id=<?php echo $row['id']; ?>"
                                        class="btn-action btn-delete"
                                        onclick="return confirm('Delete this instructor? This cannot be undone.');"
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
