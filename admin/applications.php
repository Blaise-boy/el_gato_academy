<?php
$active_page = 'applications';
require 'includes/auth-check.php';
require '../config/database.php';

$successMessage = '';
$errorMessage = '';

// Handle approve / reject actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['application_id'], $_POST['action'])) {
    $applicationId = (int) $_POST['application_id'];
    $action = $_POST['action'];

    $appStmt = $conn->prepare('SELECT * FROM applications WHERE id = ?');
    $appStmt->bind_param('i', $applicationId);
    $appStmt->execute();
    $application = $appStmt->get_result()->fetch_assoc();

    if (!$application) {
        $errorMessage = 'Application not found.';
    } elseif ($action === 'reject') {
        $update = $conn->prepare("UPDATE applications SET status = 'rejected' WHERE id = ?");
        $update->bind_param('i', $applicationId);
        $update->execute();
        $successMessage = 'Application rejected.';
    } elseif ($action === 'approve') {
        $checkStmt = $conn->prepare('SELECT id FROM students WHERE email = ?');
        $checkStmt->bind_param('s', $application['email']);
        $checkStmt->execute();

        if ($checkStmt->get_result()->num_rows > 0) {
            $errorMessage = 'A student account already exists for this email.';
        } else {
            $studentCount = $conn->query('SELECT COUNT(*) AS cnt FROM students')->fetch_assoc()['cnt'];
            $regNumber = 'EGA-' . date('Y') . '-' . str_pad($studentCount + 1, 4, '0', STR_PAD_LEFT);

            // Use the password the applicant chose at signup if they set one.
            // Only fall back to a generated reg-number password for applications that predate that field.
            $usingOwnPassword = !empty($application['password']);
            $hashedPassword = $usingOwnPassword ? $application['password'] : password_hash($regNumber, PASSWORD_DEFAULT);

            $insert = $conn->prepare('
                INSERT INTO students (reg_number, first_name, last_name, email, password, phone, application_id, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, "active")
            ');
            $insert->bind_param(
                'ssssssi',
                $regNumber,
                $application['first_name'],
                $application['last_name'],
                $application['email'],
                $hashedPassword,
                $application['phone'],
                $applicationId
            );
            $insert->execute();

            $update = $conn->prepare("UPDATE applications SET status = 'approved' WHERE id = ?");
            $update->bind_param('i', $applicationId);
            $update->execute();

            if ($usingOwnPassword) {
                $successMessage = "Approved — Student ID: $regNumber. They log in with the password they created at signup.";
            } else {
                $successMessage = "Approved — Student ID: $regNumber, initial password: $regNumber. Share these with the student; they should change the password after first login.";
            }
        }
    }
}

$applications = $conn->query('SELECT * FROM applications ORDER BY created_at DESC');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Applications | El Gato's Academy Admin</title>

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
                <h1>Applications</h1>
                <p>Review submitted applications and approve them into student accounts.</p>
            </div>

            <?php if ($successMessage): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($successMessage); ?></div>
            <?php endif; ?>
            <?php if ($errorMessage): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($errorMessage); ?></div>
            <?php endif; ?>

            <div class="table-card full-width">
                <?php if ($applications->num_rows === 0): ?>
                    <p class="empty-state">No applications submitted yet.</p>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Course</th>
                                <th>Date Applied</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $applications->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['email']); ?></td>
                                <td><?php echo htmlspecialchars($row['course']); ?></td>
                                <td><?php echo date('M j, Y', strtotime($row['created_at'])); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo htmlspecialchars($row['status']); ?>">
                                        <?php echo ucfirst($row['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($row['status'] === 'pending'): ?>
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="application_id" value="<?php echo $row['id']; ?>">
                                            <input type="hidden" name="action" value="approve">
                                            <button type="submit" class="btn-action btn-approve">Approve</button>
                                        </form>
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="application_id" value="<?php echo $row['id']; ?>">
                                            <input type="hidden" name="action" value="reject">
                                            <button type="submit" class="btn-action btn-reject">Reject</button>
                                        </form>
                                    <?php else: ?>
                                        <span class="text-muted-small">&mdash;</span>
                                    <?php endif; ?>
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
