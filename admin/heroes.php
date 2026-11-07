<?php
$active_page = 'heroes';
require 'includes/auth-check.php';
require '../config/database.php';
require 'includes/upload-helper.php';

// The public pages that have a hero banner. Key = page_heroes.page_key.
$pages = [
    'home'       => 'Homepage',
    'about'      => 'About',
    'courses'    => 'Courses',
    'instructors'=> 'Instructors',
    'admissions' => 'Admissions',
    'blog'       => 'Blog',
    'contact'    => 'Contact',
];

$successMessage = '';
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['page_key'])) {
    $pageKey = $_POST['page_key'];
    $action = $_POST['action'] ?? 'upload';

    if (!array_key_exists($pageKey, $pages)) {
        $errorMessage = 'Unknown page.';
    } elseif ($action === 'delete') {
        $existing = $conn->prepare('SELECT * FROM page_heroes WHERE page_key = ?');
        $existing->bind_param('s', $pageKey);
        $existing->execute();
        $existingRow = $existing->get_result()->fetch_assoc();

        if ($existingRow) {
            deleteImageFile($existingRow['image']);
            $delete = $conn->prepare('DELETE FROM page_heroes WHERE page_key = ?');
            $delete->bind_param('s', $pageKey);
            $delete->execute();
            $successMessage = $pages[$pageKey] . ' hero image removed. The page will fall back to its background CSS.';
        } else {
            $errorMessage = 'There\'s no uploaded image to remove for this page.';
        }
    } else {
        $upload = handleImageUpload('image', 'heroes');

        if ($upload['error']) {
            $errorMessage = $upload['error'];
        } elseif (!$upload['success']) {
            $errorMessage = 'Please choose an image to upload.';
        } else {
            $existing = $conn->prepare('SELECT * FROM page_heroes WHERE page_key = ?');
            $existing->bind_param('s', $pageKey);
            $existing->execute();
            $existingRow = $existing->get_result()->fetch_assoc();

            if ($existingRow) {
                deleteImageFile($existingRow['image']);
                $update = $conn->prepare('UPDATE page_heroes SET image = ? WHERE page_key = ?');
                $update->bind_param('ss', $upload['path'], $pageKey);
                $update->execute();
            } else {
                $insert = $conn->prepare('INSERT INTO page_heroes (page_key, image) VALUES (?, ?)');
                $insert->bind_param('ss', $pageKey, $upload['path']);
                $insert->execute();
            }

            $successMessage = $pages[$pageKey] . ' hero image updated.';
        }
    }
}

// Load current hero image per page
$currentHeroes = [];
$result = $conn->query('SELECT page_key, image FROM page_heroes');
while ($row = $result->fetch_assoc()) {
    $currentHeroes[$row['page_key']] = $row['image'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hero Images | El Gato's Academy Admin</title>

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
                <h1>Hero Images</h1>
                <p>Swap the banner image shown at the top of any public page &mdash; no code needed.</p>
            </div>

            <?php if ($successMessage): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($successMessage); ?></div>
            <?php endif; ?>
            <?php if ($errorMessage): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($errorMessage); ?></div>
            <?php endif; ?>

            <div class="hero-manager-grid">
                <?php foreach ($pages as $key => $label): ?>
                <div class="hero-card">
                    <div class="hero-card-preview">
                        <?php if (!empty($currentHeroes[$key])): ?>
                            <img src="../<?php echo htmlspecialchars($currentHeroes[$key]); ?>" alt="<?php echo htmlspecialchars($label); ?> hero">
                        <?php else: ?>
                            <div class="hero-card-placeholder">
                                <i class="fa-solid fa-image"></i>
                                <span>No image set</span>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="hero-card-body">
                        <h3><?php echo htmlspecialchars($label); ?></h3>
                        <form method="POST" enctype="multipart/form-data" class="hero-card-form">
                            <input type="hidden" name="page_key" value="<?php echo $key; ?>">
                            <input type="hidden" name="action" value="upload">
                            <input type="file" name="image" accept="image/*" required>
                            <button type="submit" class="btn-action btn-approve">
                                <?php echo !empty($currentHeroes[$key]) ? 'Replace' : 'Upload'; ?>
                            </button>
                        </form>
                        <?php if (!empty($currentHeroes[$key])): ?>
                            <form method="POST" class="hero-card-form" style="margin-top:8px;" onsubmit="return confirm('Remove the hero image for <?php echo htmlspecialchars($label); ?>?');">
                                <input type="hidden" name="page_key" value="<?php echo $key; ?>">
                                <input type="hidden" name="action" value="delete">
                                <button type="submit" class="btn-action btn-delete">Delete Image</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </main>
    </div>

</body>
</html>
