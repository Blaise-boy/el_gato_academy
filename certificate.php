<?php
require 'config/database.php';

$pageTitle = "Certificate | El Gato's Academy";
$active_page = '';

$code = $_GET['code'] ?? '';
if ($code === '') {
    header('Location: index.php');
    exit();
}

$stmt = $conn->prepare('
    SELECT certificates.*, students.first_name, students.last_name, courses.name AS course_name, courses.duration
    FROM certificates
    JOIN students ON students.id = certificates.student_id
    JOIN courses ON courses.id = certificates.course_id
    WHERE certificate_code = ?
');
$stmt->bind_param('s', $code);
$stmt->execute();
$certificate = $stmt->get_result()->fetch_assoc();

require 'includes/header.php';
require 'includes/nav.php';
?>

  <section class="section">
    <div class="container" style="max-width: 820px;">

      <?php if (!$certificate): ?>
        <div class="certificate-not-found">
          <i class="fa-solid fa-circle-xmark"></i>
          <h2>Certificate Not Found</h2>
          <p>We couldn't find a certificate with the code <strong><?php echo htmlspecialchars($code); ?></strong>. Double-check the code and try again.</p>
        </div>
      <?php else: ?>

        <div class="certificate-card" id="certificateCard">
          <div class="certificate-border">
            <div class="certificate-logo-row">
              <img src="images/logo.png" alt="El Gato's Academy">
            </div>

            <p class="certificate-eyebrow">Certificate of Completion</p>
            <p class="certificate-presented">This certificate is proudly presented to</p>

            <h1 class="certificate-name"><?php echo htmlspecialchars($certificate['first_name'] . ' ' . $certificate['last_name']); ?></h1>

            <p class="certificate-body-text">
                for successfully completing the <strong><?php echo htmlspecialchars($certificate['course_name']); ?></strong> course
                
                at El Gato's Academy.
            </p>

            <div class="certificate-footer-row">
              <div class="certificate-footer-item">
                <span>Date Issued</span>
                <strong><?php echo date('F j, Y', strtotime($certificate['issued_at'])); ?></strong>
              </div>
              <div class="certificate-footer-item">
                <span>Certificate Code</span>
                <strong><?php echo htmlspecialchars($certificate['certificate_code']); ?></strong>
              </div>
            </div>
          </div>
        </div>

        <div style="text-align:center; margin-top:24px;">
          <button onclick="window.print()" class="btn btn-primary"><i class="fa-solid fa-print"></i> Print Certificate</button>
        </div>

      <?php endif; ?>

    </div>
  </section>

<?php require 'includes/footer.php'; ?>
