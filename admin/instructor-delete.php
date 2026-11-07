<?php
require 'includes/auth-check.php';
require '../config/database.php';
require 'includes/upload-helper.php';

$id = $_GET['id'] ?? null;
if (!$id || !is_numeric($id)) {
    header('Location: instructors.php');
    exit();
}

$stmt = $conn->prepare('SELECT photo FROM instructors WHERE id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
$instructor = $stmt->get_result()->fetch_assoc();

if ($instructor) {
    deleteImageFile($instructor['photo']);

    $delete = $conn->prepare('DELETE FROM instructors WHERE id = ?');
    $delete->bind_param('i', $id);
    $delete->execute();
}

header('Location: instructors.php?deleted=1');
exit();
