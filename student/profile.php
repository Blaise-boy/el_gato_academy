<?php
$active_page = 'profile';
require 'includes/auth-check.php';
require '../config/database.php';
require 'includes/upload-helper.php';

$studentId = $_SESSION['student_id'];

$stmt = $conn->prepare('SELECT * FROM students WHERE id = ?');
$stmt->bind_param('i', $studentId);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();

if (!$student) {
    header('Location: ../login.php');
    exit();
}

$errors = [];
$success = '';

// --- Update profile details (name, phone, photo) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');

    if ($first_name === '') $errors[] = 'First name is required.';
    if ($last_name === '')  $errors[] = 'Last name is required.';

    $upload = handleImageUpload('photo', 'students');
    if ($upload['error']) {
        $errors[] = $upload['error'];
    }

    if (empty($errors)) {
        $photoPath = $student['photo'];
        if ($upload['success']) {
            deleteImageFile($student['photo']);
            $photoPath = $upload['path'];
        }

        $update = $conn->prepare('UPDATE students SET first_name = ?, last_name = ?, phone = ?, photo = ? WHERE id = ?');
        $update->bind_param('ssssi', $first_name, $last_name, $phone, $photoPath, $studentId);
        $update->execute();

        header('Location: profile.php?updated=1');
        exit();
    }
}

// --- Change password ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if (!password_verify($currentPassword, $student['password'])) {
        $errors[] = 'Your current password is incorrect.';
    } elseif (strlen($newPassword) < 8) {
        $errors[] = 'New password must be at least 8 characters.';
    } elseif ($newPassword !== $confirmPassword) {
        $errors[] = 'New passwords do not match.';
    } else {
        $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
        $update = $conn->prepare('UPDATE students SET password = ? WHERE id = ?');
        $update->bind_param('si', $hashed, $studentId);
        $update->execute();

        header('Location: profile.php?password_updated=1');
        exit();
    }
}

$updated = isset($_GET['updated']);
$passwordUpdated = isset($_GET['password_updated']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile | El Gato's Academy</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600;700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../css/admin.css">
    <link rel="stylesheet" href="../css/student.css">
</head>
<body>

    <div class="admin-layout">
        <?php require 'includes/sidebar.php'; ?>

        <main class="admin-main">
            <div class="page-header-row">
                <div class="page-header">
                    <h1>My Profile</h1>
                    <p>Update your details and photo.</p>
                </div>
            </div>

            <?php if ($updated): ?>
                <div class="alert alert-success">Your profile has been updated.</div>
            <?php endif; ?>
            <?php if ($passwordUpdated): ?>
                <div class="alert alert-success">Your password has been changed.</div>
            <?php endif; ?>
            <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <ul class="alert-list">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <div class="profile-columns">

                <div class="form-card">
                    <h3 style="color:var(--white); font-family:'Poppins',sans-serif; font-size:16px; margin:0 0 20px;">Profile Details</h3>

                    <form method="POST" action="profile.php" enctype="multipart/form-data">
                        <input type="hidden" name="update_profile" value="1">

                        <div class="form-group" style="text-align:center;">
                            <div class="student-profile-photo" style="width:90px; height:90px; margin-bottom:12px;">
                                <?php if (!empty($student['photo'])): ?>
                                    <img src="../<?php echo htmlspecialchars($student['photo']); ?>" alt="" style="width:90px;height:90px;">
                                <?php else: ?>
                                    <div class="student-photo-fallback" style="width:90px;height:90px; font-size:30px;"><?php echo strtoupper(substr($student['first_name'], 0, 1)); ?></div>
                                <?php endif; ?>
                            </div>
                            <label for="photo" style="display:block; text-align:center;">Profile Photo</label>
                            <input type="file" id="photo" name="photo" accept="image/*">
                        </div>

                        <div class="form-grid-2">
                            <div class="form-group">
                                <label for="first_name">First Name</label>
                                <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($student['first_name']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="last_name">Last Name</label>
                                <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($student['last_name']); ?>" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="phone">Phone Number</label>
                            <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($student['phone'] ?? ''); ?>" placeholder="e.g. 08012345678">
                        </div>

                        <div class="form-group">
                            <label>Email Address</label>
                            <input type="text" value="<?php echo htmlspecialchars($student['email']); ?>" disabled>
                            <span class="text-muted-small">Contact the school to change the email on your account.</span>
                        </div>

                        <div class="form-group">
                            <label>Student ID</label>
                            <input type="text" value="<?php echo htmlspecialchars($student['reg_number']); ?>" disabled>
                        </div>

                        <button type="submit" class="btn btn-primary" style="width:100%;">Save Changes</button>
                    </form>
                </div>

                <div class="form-card">
                    <h3 style="color:var(--white); font-family:'Poppins',sans-serif; font-size:16px; margin:0 0 20px;">Change Password</h3>

                    <form method="POST" action="profile.php">
                        <input type="hidden" name="change_password" value="1">

                        <div class="form-group">
                            <label for="current_password">Current Password</label>
                            <div class="password-input-wrap">
                                <input type="password" id="current_password" name="current_password" required>
                                <button type="button" class="password-toggle-btn" onclick="toggleStudentPassword(this)"><i class="fa-solid fa-eye"></i></button>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="new_password">New Password</label>
                            <div class="password-input-wrap">
                                <input type="password" id="new_password" name="new_password" required>
                                <button type="button" class="password-toggle-btn" onclick="toggleStudentPassword(this)"><i class="fa-solid fa-eye"></i></button>
                            </div>
                            <span class="text-muted-small">At least 8 characters.</span>
                        </div>
                        <div class="form-group">
                            <label for="confirm_password">Confirm New Password</label>
                            <div class="password-input-wrap">
                                <input type="password" id="confirm_password" name="confirm_password" required>
                                <button type="button" class="password-toggle-btn" onclick="toggleStudentPassword(this)"><i class="fa-solid fa-eye"></i></button>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-secondary" style="width:100%;">Update Password</button>
                    </form>
                </div>

            </div>
        </main>
    </div>

    <script>
        function toggleStudentPassword(btn) {
            const field = btn.closest('.password-input-wrap').querySelector('input');
            const icon = btn.querySelector('i');
            if (field.type === 'password') {
                field.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                field.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        }
    </script>

</body>
</html>
