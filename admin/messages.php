<?php
$active_page = 'messages';
require 'includes/auth-check.php';
require '../config/database.php';

$messages = $conn->query("SELECT * FROM contact_messages ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages | El Gato's Academy Admin</title>

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
                <h1>Messages</h1>
                <p>Submissions from the public Contact page &mdash; <?php echo $messages->num_rows; ?> total.</p>
            </div>

            <?php if (isset($_GET['deleted'])): ?>
                <div class="alert alert-success">Message deleted successfully.</div>
            <?php endif; ?>

            <div class="table-card full-width">
                <?php if ($messages->num_rows === 0): ?>
                    <p class="empty-state">No messages yet.</p>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Subject</th>
                                <th>Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $messages->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['email']); ?></td>
                                <td><?php echo htmlspecialchars($row['subject']); ?></td>
                                <td><?php echo date('M j, Y', strtotime($row['created_at'])); ?></td>
                                <td>
                                    <a href="message-view.php?id=<?php echo $row['id']; ?>" class="btn-action btn-approve">View</a>
                                    <a
                                        href="message-delete.php?id=<?php echo $row['id']; ?>"
                                        class="btn-action btn-delete"
                                        onclick="return confirm('Delete this message?');"
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
