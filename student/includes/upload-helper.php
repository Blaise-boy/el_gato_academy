<?php
/**
 * Validates and moves an uploaded image into images/{subfolder}/, returning
 * a web-relative path (e.g. "images/courses/xyz.jpg") on success.
 *
 * $fieldName - the <input type="file" name="..."> field to read
 * $subfolder - nested folder under images/ to save into (e.g. "courses")
 *
 * Returns ['success' => bool, 'path' => string|null, 'error' => string|null]
 */
function handleImageUpload($fieldName, $subfolder) {

    if (!isset($_FILES[$fieldName]) || $_FILES[$fieldName]['error'] === UPLOAD_ERR_NO_FILE) {
        return ['success' => false, 'path' => null, 'error' => null]; // nothing submitted
    }

    if ($_FILES[$fieldName]['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'path' => null, 'error' => 'There was a problem uploading the image.'];
    }

    $allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    if (!in_array($_FILES[$fieldName]['type'], $allowedTypes)) {
        return ['success' => false, 'path' => null, 'error' => 'Please upload a JPG, PNG, WEBP, or GIF image.'];
    }

    if ($_FILES[$fieldName]['size'] > 5 * 1024 * 1024) {
        return ['success' => false, 'path' => null, 'error' => 'Image must be smaller than 5MB.'];
    }

    $targetDir = '../images/' . $subfolder . '/';
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }

    $extension = pathinfo($_FILES[$fieldName]['name'], PATHINFO_EXTENSION);
    $newFilename = uniqid($subfolder . '_') . '.' . $extension;
    $destination = $targetDir . $newFilename;

    if (!move_uploaded_file($_FILES[$fieldName]['tmp_name'], $destination)) {
        return ['success' => false, 'path' => null, 'error' => 'Could not save the uploaded image.'];
    }

    return ['success' => true, 'path' => 'images/' . $subfolder . '/' . $newFilename, 'error' => null];
}

/**
 * Deletes a previously uploaded image file, given its stored
 * "images/subfolder/xyz.jpg" web-relative path. Safe to call even
 * if the file doesn't exist.
 */
function deleteImageFile($webPath) {
    if (!$webPath) {
        return;
    }
    $fullPath = '../' . $webPath;
    if (file_exists($fullPath)) {
        @unlink($fullPath);
    }
}
