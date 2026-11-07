<?php
$active_page = 'instructors';
require 'includes/auth-check.php';
require '../config/database.php';
require 'includes/upload-helper.php';

$id = $_GET['id'] ?? null;
if (!$id || !is_numeric($id)) {
    header('Location: instructors.php');
    exit();
}

$stmt = $conn->prepare('SELECT * FROM instructors WHERE id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
$instructor = $stmt->get_result()->fetch_assoc();

if (!$instructor) {
    header('Location: instructors.php');
    exit();
}

$errors = [];
$name = $instructor['name'];
$position = $instructor['position'];
$specialization = $instructor['specialization'];
$bio = $instructor['bio'];
$email = $instructor['email'];
$phone = $instructor['phone'];
$experience = $instructor['experience'];
$skills = $instructor['skills'];
$linkedin_url = $instructor['linkedin_url'];
$twitter_url = $instructor['twitter_url'];
$github_url = $instructor['github_url'];
$website_url = $instructor['website_url'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $position = trim($_POST['position'] ?? '');
    $specialization = trim($_POST['specialization'] ?? '');
    $bio = trim($_POST['bio'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $experience = trim($_POST['experience'] ?? '');
    $skills = trim($_POST['skills'] ?? '');
    $linkedin_url = trim($_POST['linkedin_url'] ?? '');
    $twitter_url = trim($_POST['twitter_url'] ?? '');
    $github_url = trim($_POST['github_url'] ?? '');
    $website_url = trim($_POST['website_url'] ?? '');

    if ($name === '')           $errors[] = 'Name is required.';
    if ($position === '')       $errors[] = 'Position is required.';
    if ($specialization === '') $errors[] = 'Specialization is required.';
    if ($bio === '')            $errors[] = 'Bio is required.';

    $upload = handleImageUpload('photo', 'instructors');
    if ($upload['error']) {
        $errors[] = $upload['error'];
    }

    if (empty($errors)) {
        $photoPath = $instructor['photo'];

        if ($upload['success']) {
            deleteImageFile($instructor['photo']);
            $photoPath = $upload['path'];
        }

        $update = $conn->prepare('
            UPDATE instructors
            SET name = ?, position = ?, specialization = ?, bio = ?, photo = ?,
                email = ?, phone = ?, experience = ?, skills = ?, linkedin_url = ?, twitter_url = ?, github_url = ?, website_url = ?
            WHERE id = ?
        ');
        $update->bind_param(
            'sssssssssssssi',
            $name, $position, $specialization, $bio, $photoPath,
            $email, $phone, $experience, $skills, $linkedin_url, $twitter_url, $github_url, $website_url,
            $id
        );
        $update->execute();

        header('Location: instructors.php?updated=1');
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Instructor | El Gato's Academy Admin</title>

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
                    <h1>Edit Instructor</h1>
                    <p>Update details for <?php echo htmlspecialchars($instructor['name']); ?>.</p>
                </div>
                <a href="instructors.php" class="btn btn-secondary">Back to Instructors</a>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <ul class="alert-list">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <div class="form-card full-width">
                <form method="POST" action="instructor-edit.php?id=<?php echo $instructor['id']; ?>" enctype="multipart/form-data">

                    <div class="form-group">
                        <label for="name">Full Name</label>
                        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="position">Position</label>
                        <input type="text" id="position" name="position" value="<?php echo htmlspecialchars($position); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="specialization">Specialization</label>
                        <input type="text" id="specialization" name="specialization" value="<?php echo htmlspecialchars($specialization); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="bio">Short Biography</label>
                        <textarea id="bio" name="bio" rows="4" required><?php echo htmlspecialchars($bio); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="photo">Photograph</label>
                        <?php if ($instructor['photo']): ?>
                            <img class="admin-thumb admin-thumb-round" src="../<?php echo htmlspecialchars($instructor['photo']); ?>" alt="" style="display:block; margin-bottom:10px;">
                        <?php endif; ?>
                        <input type="file" id="photo" name="photo" accept="image/*">
                        <span class="text-muted-small">Leave blank to keep the current photo.</span>
                    </div>

                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email ?? ''); ?>" placeholder="instructor@elgatosacademy.com">
                    </div>

                    <div class="form-group">
                        <label for="phone">Phone</label>
                        <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($phone ?? ''); ?>" placeholder="e.g. +234 800 000 0000">
                    </div>

                    <div class="form-group">
                        <label for="experience">Experience</label>
                        <input type="text" id="experience" name="experience" value="<?php echo htmlspecialchars($experience ?? ''); ?>" placeholder="e.g. 5+ years">
                    </div>

                    <div class="form-group">
                        <label for="skills">Expertise & Skills</label>
                        <input type="text" id="skills" name="skills" value="<?php echo htmlspecialchars($skills ?? ''); ?>" placeholder="Comma-separated, e.g. PHP, MySQL, JavaScript, REST APIs">
                        <span class="text-muted-small">Separate each skill with a comma — they'll show as tags on the instructor's page.</span>
                    </div>

                    <div class="form-group">
                        <label for="linkedin_url">LinkedIn URL</label>
                        <input type="url" id="linkedin_url" name="linkedin_url" value="<?php echo htmlspecialchars($linkedin_url ?? ''); ?>" placeholder="https://linkedin.com/in/...">
                    </div>

                    <div class="form-group">
                        <label for="twitter_url">Twitter / X URL</label>
                        <input type="url" id="twitter_url" name="twitter_url" value="<?php echo htmlspecialchars($twitter_url ?? ''); ?>" placeholder="https://x.com/...">
                    </div>

                    <div class="form-group">
                        <label for="github_url">GitHub URL</label>
                        <input type="url" id="github_url" name="github_url" value="<?php echo htmlspecialchars($github_url ?? ''); ?>" placeholder="https://github.com/...">
                    </div>

                    <div class="form-group">
                        <label for="website_url">Website URL</label>
                        <input type="url" id="website_url" name="website_url" value="<?php echo htmlspecialchars($website_url ?? ''); ?>" placeholder="https://...">
                    </div>

                    <button type="submit" class="btn btn-primary" style="width:100%;">Update Instructor</button>
                </form>
            </div>
        </main>
    </div>

</body>
</html>
