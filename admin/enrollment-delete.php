<?php
require 'includes/auth-check.php';
require '../config/database.php';

$id = $_GET['id'] ?? null;
if (!$id || !is_numeric($id)) {
    header('Location: enrollments.php');
    exit();
}

$delete = $conn->prepare('DELETE FROM enrollments WHERE id = ?');
$delete->bind_param('i', $id);
$delete->execute();

header('Location: enrollments.php?deleted=1');
exit();
