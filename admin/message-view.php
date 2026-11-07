<?php
$active_page = 'messages';
require 'includes/auth-check.php';
require '../config/database.php';

$id = $_GET['id'] ?? null;
if (!$id || !is_numeric($id)) {
    header('Location: messages.php');
    exit();
}

$stmt = $conn->prepare('SELECT * FROM contact_messages WHERE id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
$message = $stmt->get_result()->fetch_assoc();

if (!$message) {
    header('Location: messages.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Message | El Gato's Academy Admin</title>

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
                    <h1><?php echo htmlspecialchars($message['subject']); ?></h1>
                    <p>From <?php echo htmlspecialchars($message['full_name']); ?> &mdash; <?php echo date('M j, Y \a\t g:i A', strtotime($message['created_at'])); ?></p>
                </div>
                <a href="messages.php" class="btn btn-secondary">Back to Messages</a>
            </div>

            <div class="form-card full-width">
                <div class="message-meta">
                    <div>
                        <span class="text-muted-small">Email</span>
                        <p><?php echo htmlspecialchars($message['email']); ?></p>
                    </div>
                    <div>
                        <span class="text-muted-small">Phone</span>
                        <p><?php echo htmlspecialchars($message['phone']); ?></p>
                    </div>
                </div>

                <div class="message-body">
                    <?php echo nl2br(htmlspecialchars($message['message'])); ?>
                </div>

                <a
                    href="message-delete.php?id=<?php echo $message['id']; ?>"
                    class="btn-action btn-delete"
                    onclick="return confirm('Delete this message?');"
                >Delete Message</a>
            </div>
        </main>
    </div>

</body>
</html>
