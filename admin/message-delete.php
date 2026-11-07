<?php
require 'includes/auth-check.php';
require '../config/database.php';

$id = $_GET['id'] ?? null;
if (!$id || !is_numeric($id)) {
    header('Location: messages.php');
    exit();
}

$delete = $conn->prepare('DELETE FROM contact_messages WHERE id = ?');
$delete->bind_param('i', $id);
$delete->execute();

header('Location: messages.php?deleted=1');
exit();
