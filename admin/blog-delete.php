<?php
require 'includes/auth-check.php';
require '../config/database.php';
require 'includes/upload-helper.php';

$id = $_GET['id'] ?? null;
if (!$id || !is_numeric($id)) {
    header('Location: blog.php');
    exit();
}

$stmt = $conn->prepare('SELECT image FROM blog_posts WHERE id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
$post = $stmt->get_result()->fetch_assoc();

if ($post) {
    deleteImageFile($post['image']);

    $delete = $conn->prepare('DELETE FROM blog_posts WHERE id = ?');
    $delete->bind_param('i', $id);
    $delete->execute();
}

header('Location: blog.php?deleted=1');
exit();
