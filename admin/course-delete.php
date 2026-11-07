<?php
require 'includes/auth-check.php';
require '../config/database.php';
require 'includes/upload-helper.php';

$id = $_GET['id'] ?? null;
if (!$id || !is_numeric($id)) {
    header('Location: courses.php');
    exit();
}

$stmt = $conn->prepare('SELECT image FROM courses WHERE id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
$course = $stmt->get_result()->fetch_assoc();

if ($course) {
    deleteImageFile($course['image']);

    $delete = $conn->prepare('DELETE FROM courses WHERE id = ?');
    $delete->bind_param('i', $id);
    $delete->execute();
}

header('Location: courses.php?deleted=1');
exit();
