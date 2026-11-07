<?php
$active_page = 'certificates';
require 'includes/auth-check.php';
require '../config/database.php';

$certificates = $conn->query("
    SELECT certificates.*, students.first_name, students.last_name, students.reg_number, courses.name AS course_name
    FROM certificates
    JOIN students ON students.id = certificates.student_id
    JOIN courses ON courses.id = certificates.course_id
    ORDER BY certificates.issued_at DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificates | El Gato's Academy Admin</title>

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
                    <h1>Certificates</h1>
                    <p>Every certificate issued so far. Issue new ones from the Enrollments page once a course hits 100%.</p>
                </div>
            </div>

            <div class="table-card full-width">
                <?php if ($certificates->num_rows === 0): ?>
                    <p class="empty-state">No certificates issued yet.</p>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Certificate Code</th>
                                <th>Student</th>
                                <th>Reg Number</th>
                                <th>Course</th>
                                <th>Issued On</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $certificates->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['certificate_code']); ?></td>
                                <td><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['reg_number']); ?></td>
                                <td><?php echo htmlspecialchars($row['course_name']); ?></td>
                                <td><?php echo date('M j, Y', strtotime($row['issued_at'])); ?></td>
                                <td>
                                    <a href="../certificate.php?code=<?php echo urlencode($row['certificate_code']); ?>" class="btn-action btn-approve" target="_blank">View</a>
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
