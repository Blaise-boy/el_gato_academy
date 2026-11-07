<?php
$active_page = 'students';
require 'includes/auth-check.php';
require '../config/database.php';

$successMessage = '';
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['student_id'], $_POST['action'])) {
    $studentId = (int) $_POST['student_id'];
    $action = $_POST['action'];

    if ($action === 'toggle_status') {
        $current = $conn->prepare('SELECT status FROM students WHERE id = ?');
        $current->bind_param('i', $studentId);
        $current->execute();
        $row = $current->get_result()->fetch_assoc();

        if ($row) {
            $newStatus = $row['status'] === 'active' ? 'suspended' : 'active';
            $update = $conn->prepare('UPDATE students SET status = ? WHERE id = ?');
            $update->bind_param('si', $newStatus, $studentId);
            $update->execute();
            $successMessage = 'Student status updated to ' . ucfirst($newStatus) . '.';
        } else {
            $errorMessage = 'Student not found.';
        }
    } elseif ($action === 'delete') {
        $delete = $conn->prepare('DELETE FROM students WHERE id = ?');
        $delete->bind_param('i', $studentId);
        $delete->execute();
        $successMessage = 'Student account deleted.';
    }
}

$students = $conn->query("
    SELECT students.*, COUNT(enrollments.id) AS course_count
    FROM students
    LEFT JOIN enrollments ON enrollments.student_id = students.id
    GROUP BY students.id
    ORDER BY students.created_at DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Students | El Gato's Academy Admin</title>

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
                <h1>Students</h1>
                <p>All registered student accounts — <?php echo $students->num_rows; ?> total.</p>
            </div>

            <?php if ($successMessage): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($successMessage); ?></div>
            <?php endif; ?>
            <?php if ($errorMessage): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($errorMessage); ?></div>
            <?php endif; ?>

            <div class="table-card full-width">
                <?php if ($students->num_rows === 0): ?>
                    <p class="empty-state">No students yet — approve an application to create the first account.</p>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Reg Number</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Courses</th>
                                <th>Status</th>
                                <th>Joined</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $students->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['reg_number']); ?></td>
                                <td><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['email']); ?></td>
                                <td><?php echo htmlspecialchars($row['phone']); ?></td>
                                <td><?php echo $row['course_count']; ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo htmlspecialchars($row['status']); ?>">
                                        <?php echo ucfirst($row['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('M j, Y', strtotime($row['created_at'])); ?></td>
                                <td>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="student_id" value="<?php echo $row['id']; ?>">
                                        <input type="hidden" name="action" value="toggle_status">
                                        <button type="submit" class="btn-action <?php echo $row['status'] === 'active' ? 'btn-reject' : 'btn-approve'; ?>">
                                            <?php echo $row['status'] === 'active' ? 'Suspend' : 'Activate'; ?>
                                        </button>
                                    </form>
                                    <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this student account? This cannot be undone.');">
                                        <input type="hidden" name="student_id" value="<?php echo $row['id']; ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <button type="submit" class="btn-action btn-delete">Delete</button>
                                    </form>
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
