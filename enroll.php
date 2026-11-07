<?php
session_start();
require 'config/database.php';

if (empty($_SESSION['student_id'])) {
    header('Location: login.php');
    exit();
}

$courseId = $_POST['course_id'] ?? null;
if (!$courseId || !is_numeric($courseId)) {
    header('Location: courses.php');
    exit();
}

// Confirm the course actually exists.
$courseCheck = $conn->prepare('SELECT id FROM courses WHERE id = ?');
$courseCheck->bind_param('i', $courseId);
$courseCheck->execute();
if ($courseCheck->get_result()->num_rows === 0) {
    header('Location: courses.php');
    exit();
}

// Don't create a duplicate enrollment.
$dupCheck = $conn->prepare('SELECT id FROM enrollments WHERE student_id = ? AND course_id = ?');
$dupCheck->bind_param('ii', $_SESSION['student_id'], $courseId);
$dupCheck->execute();

if ($dupCheck->get_result()->num_rows === 0) {
    $insert = $conn->prepare('INSERT INTO enrollments (student_id, course_id) VALUES (?, ?)');
    $insert->bind_param('ii', $_SESSION['student_id'], $courseId);
    $insert->execute();
}

header('Location: course-details.php?id=' . (int) $courseId . '&enrolled=1');
exit();
