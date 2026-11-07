<?php
require 'config/database.php';

$pageTitle = "Contact Us | El Gato's Academy";
$pageDescription = "Get in touch with El Gato's Academy — we're here to help.";
$active_page = 'contact';

$heroStmt = $conn->prepare("SELECT image FROM page_heroes WHERE page_key = 'contact'");
$heroStmt->execute();
$heroRow = $heroStmt->get_result()->fetch_assoc();
$heroImage = $heroRow['image'] ?? null;

$errors = [];
$full_name = $email = $phone = $subject = $message = '';
$submitted = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if ($full_name === '') $errors[] = 'Full name is required.';
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'A valid email address is required.';
    if ($subject === '') $errors[] = 'Subject is required.';
    if ($message === '') $errors[] = 'Message is required.';

    if (empty($errors)) {
        $stmt = $conn->prepare('INSERT INTO contact_messages (full_name, email, phone, subject, message) VALUES (?, ?, ?, ?, ?)');
        $stmt->bind_param('sssss', $full_name, $email, $phone, $subject, $message);
        $stmt->execute();

        header('Location: contact.php?sent=1');
        exit();
    }
}

$sent = isset($_GET['sent']);

require 'includes/header.php';
require 'includes/nav.php';
?>

  <!-- HERO -->
  <section class="hero">

    <div class="hero-text-col hero-content">
      <p class="hero-eyebrow">Get In Touch</p>
      <h1 style="font-size:40px;">
        We'd Love to <span class="highlight">Hear From You.</span>
      </h1>
      <p>
        Have a question about a course, your application, or anything else?
        Send us a message and our team will get back to you.
      </p>
    </div>

    <div class="contact-hero-panel">
      <div class="contact-hero-ring"></div>
      <?php if ($heroImage): ?>
        <img src="<?php echo htmlspecialchars($heroImage); ?>" alt="El Gato's Academy">
      <?php else: ?>
        <div class="contact-hero-placeholder"></div>
      <?php endif; ?>
    </div>

  </section>

  <!-- CONTACT INFO + FORM -->
  <section class="section contact-section">
    <div class="container">
      <div class="contact-layout">

        <!-- Contact info -->
        <div class="contact-info-col">
          <h2>Contact Information</h2>
          <p class="contact-info-intro">Reach out any way that's convenient for you. We usually respond within one business day.</p>

          <div class="contact-info-item">
            <div class="contact-info-icon"><i class="fa-solid fa-location-dot"></i></div>
            <div>
              <h4>Our Address</h4>
              <p>123 Learning Street, Yaba, Lagos, Nigeria.</p>
            </div>
          </div>

          <div class="contact-info-item">
            <div class="contact-info-icon"><i class="fa-solid fa-phone"></i></div>
            <div>
              <h4>Phone Number</h4>
              <p>+234 901 234 5678</p>
            </div>
          </div>

          <div class="contact-info-item">
            <div class="contact-info-icon"><i class="fa-solid fa-envelope"></i></div>
            <div>
              <h4>Email Address</h4>
              <p>info@elgatosacademy.ng</p>
            </div>
          </div>

          <div class="contact-info-item">
            <div class="contact-info-icon"><i class="fa-regular fa-clock"></i></div>
            <div>
              <h4>Office Hours</h4>
              <p>Monday &ndash; Friday, 9:00 AM &ndash; 5:00 PM</p>
            </div>
          </div>
        </div>

        <!-- Contact form -->
        <div class="contact-form-col">
          <div class="contact-form-card">
            <h2>Send Us a Message</h2>

            <?php if ($sent): ?>
              <div class="alert-success-box">
                <i class="fa-solid fa-circle-check"></i>
                Your message has been sent. We'll get back to you soon.
              </div>
            <?php else: ?>

              <?php if (!empty($errors)): ?>
                <div class="alert-error-box">
                  <ul>
                    <?php foreach ($errors as $error): ?>
                      <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                  </ul>
                </div>
              <?php endif; ?>

              <form method="POST" action="contact.php">
                <div class="contact-form-grid-2">
                  <div class="form-field">
                    <label for="full_name">Full Name</label>
                    <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($full_name); ?>" required>
                  </div>
                  <div class="form-field">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                  </div>
                </div>

                <div class="contact-form-grid-2">
                  <div class="form-field">
                    <label for="phone">Phone Number <span class="optional-label">(optional)</span></label>
                    <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($phone); ?>">
                  </div>
                  <div class="form-field">
                    <label for="subject">Subject</label>
                    <input type="text" id="subject" name="subject" value="<?php echo htmlspecialchars($subject); ?>" required>
                  </div>
                </div>

                <div class="form-field">
                  <label for="message">Message</label>
                  <textarea id="message" name="message" rows="6" required><?php echo htmlspecialchars($message); ?></textarea>
                </div>

                <button type="submit" class="btn btn-primary" style="width:100%; justify-content:center;">
                  Send Message <i class="fa-solid fa-paper-plane"></i>
                </button>
              </form>

            <?php endif; ?>
          </div>
        </div>

      </div>
    </div>
  </section>

<?php require 'includes/footer.php'; ?>
