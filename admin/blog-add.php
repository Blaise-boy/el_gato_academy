<?php
$active_page = 'blog';
require 'includes/auth-check.php';
require '../config/database.php';
require '../includes/rich-text.php';
require 'includes/upload-helper.php';

$errors = [];
$title = $category = $summary = $content = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $summary = trim($_POST['summary'] ?? '');
    $content = sanitize_rich_html(trim($_POST['content'] ?? ''));

    if ($title === '')    $errors[] = 'Title is required.';
    if ($category === '') $errors[] = 'Category is required.';
    if ($summary === '')  $errors[] = 'Summary is required.';
    if (rich_text_is_empty($content)) $errors[] = 'Content is required.';

    $upload = handleImageUpload('image', 'blog');
    if ($upload['error']) {
        $errors[] = $upload['error'];
    }

    if (empty($errors)) {
        $imagePath = $upload['success'] ? $upload['path'] : '';

        $stmt = $conn->prepare('
            INSERT INTO blog_posts (title, category, image, summary, content)
            VALUES (?, ?, ?, ?, ?)
        ');
        $stmt->bind_param('sssss', $title, $category, $imagePath, $summary, $content);
        $stmt->execute();

        header('Location: blog.php?added=1');
        exit();
    }
}

$categories = ['Web Development', 'Career', 'Cybersecurity', 'Data Analysis', 'Digital Marketing', 'General'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Article | El Gato's Academy Admin</title>

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
                    <h1>Add Article</h1>
                    <p>Publish a new article to the blog.</p>
                </div>
                <a href="blog.php" class="btn btn-secondary">Back to Blog</a>
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
                <form method="POST" action="blog-add.php" enctype="multipart/form-data">

                    <div class="form-group">
                        <label for="title">Title</label>
                        <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($title); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="category">Category</label>
                        <select id="category" name="category" required>
                            <option value="">Select category</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat; ?>" <?php echo $category === $cat ? 'selected' : ''; ?>><?php echo $cat; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="summary">Summary</label>
                        <textarea id="summary" name="summary" rows="2" placeholder="Short teaser shown on the blog listing" required><?php echo htmlspecialchars($summary); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="content">Content</label>
                        <div class="rte">
                            <div class="rte-toolbar">
                                <button type="button" data-cmd="bold" title="Bold"><i class="fa-solid fa-bold"></i></button>
                                <button type="button" data-cmd="italic" title="Italic"><i class="fa-solid fa-italic"></i></button>
                                <button type="button" data-cmd="underline" title="Underline"><i class="fa-solid fa-underline"></i></button>
                                <button type="button" data-cmd="strikeThrough" title="Strikethrough"><i class="fa-solid fa-strikethrough"></i></button>
                                <span class="rte-sep"></span>
                                <button type="button" data-cmd="formatBlock" data-value="h2" title="Heading">H2</button>
                                <button type="button" data-cmd="formatBlock" data-value="h3" title="Sub-heading">H3</button>
                                <button type="button" data-cmd="formatBlock" data-value="p" title="Normal text"><i class="fa-solid fa-paragraph"></i></button>
                                <span class="rte-sep"></span>
                                <button type="button" data-cmd="insertUnorderedList" title="Bullet list"><i class="fa-solid fa-list-ul"></i></button>
                                <button type="button" data-cmd="insertOrderedList" title="Numbered list"><i class="fa-solid fa-list-ol"></i></button>
                                <button type="button" data-cmd="formatBlock" data-value="blockquote" title="Quote"><i class="fa-solid fa-quote-left"></i></button>
                                <span class="rte-sep"></span>
                                <button type="button" data-cmd="link" title="Add link"><i class="fa-solid fa-link"></i></button>
                                <button type="button" data-cmd="unlink" title="Remove link"><i class="fa-solid fa-link-slash"></i></button>
                                <button type="button" data-cmd="removeFormat" title="Clear formatting"><i class="fa-solid fa-eraser"></i></button>
                            </div>
                            <div class="rte-area" contenteditable="true" data-placeholder="Write your article here..."></div>
                            <textarea id="content" name="content" hidden><?php echo htmlspecialchars(blog_content_for_editor($content)); ?></textarea>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="image">Cover Image</label>
                        <input type="file" id="image" name="image" accept="image/*">
                    </div>

                    <button type="submit" class="btn btn-primary" style="width:100%;">Publish Article</button>
                </form>
            </div>
        </main>
    </div>

    <script src="../js/rich-editor.js"></script>
</body>
</html>
