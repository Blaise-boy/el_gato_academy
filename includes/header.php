<?php
// Start the session here so every public page can tell if a student is logged in.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// $pageTitle and $pageDescription can be set by the including page before this file is required.
$pageTitle = $pageTitle ?? "El Gato's Academy";
$pageDescription = $pageDescription ?? "Practical, industry-relevant training designed to help you gain real skills, build confidence, and create opportunities that last.";
?>
<!DOCTYPE html>
<html lang="en">

<head>

  <meta charset="UTF-8">

  <meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
  >

  <title>
    <?php echo htmlspecialchars($pageTitle); ?>
  </title>

  <meta
    name="description"
    content="<?php echo htmlspecialchars($pageDescription); ?>"
  >

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600;700;800&family=Open+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

  <link
    rel="stylesheet"
    href="css/style.css"
  >

</head>

<body>
