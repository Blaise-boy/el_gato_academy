  <footer class="site-footer">

    <div class="container footer-grid">

      <div class="footer-about">
        <div class="logo footer-logo">
          <img src="images/logo.png" alt="El Gato's Academy">
        </div>
        <p>
          Empowering learners with practical skills to succeed in a changing world.
        </p>
        <div class="social-links">
          <a href="#" aria-label="Facebook"><i class="fa-brands fa-facebook-f"></i></a>
          <a href="#" aria-label="Twitter"><i class="fa-brands fa-twitter"></i></a>
          <a href="#" aria-label="LinkedIn"><i class="fa-brands fa-linkedin-in"></i></a>
          <a href="#" aria-label="YouTube"><i class="fa-brands fa-youtube"></i></a>
          <a href="#" aria-label="Instagram"><i class="fa-brands fa-instagram"></i></a>
        </div>
      </div>

      <div class="footer-links">
        <h3>Quick Links</h3>
        <a href="index.php">Home</a>
        <a href="courses.php">Courses</a>
        <a href="instructors.php">Instructors</a>
        <a href="blog.php">Blog</a>
        <a href="about.php">About Us</a>
        <a href="contact.php">Contact</a>
      </div>

      <div class="footer-links">
        <h3>Categories</h3>
        <a href="courses.php?category=Technology">Technology</a>
        <a href="courses.php?category=Data">Data</a>
        <a href="courses.php?category=Digital+Skills">Digital Skills</a>
        <a href="courses.php?category=Business">Business</a>
        <a href="courses.php?category=Design">Design</a>
      </div>

      <div class="footer-links">
        <h3>Support</h3>
        <a href="contact.php">Help Center</a>
        <a href="application.php">Admissions</a>
        <a href="contact.php">FAQs</a>
      </div>

      <div class="footer-newsletter">
        <h3>Newsletter</h3>
        <p>Subscribe to get updates on new courses and offers.</p>
        <form class="newsletter-form" action="forms/newsletter-process.php" method="POST">
          <input type="email" name="email" placeholder="Enter your email" required>
          <button type="submit"><i class="fa-solid fa-paper-plane"></i></button>
        </form>
        <?php if (isset($_GET['subscribed'])): ?>
          <p class="newsletter-success"><i class="fa-solid fa-check"></i> Thanks for subscribing!</p>
        <?php endif; ?>
      </div>

    </div>

    <div class="footer-bottom">
      <div class="container">
        <p>&copy; <?php echo date("Y"); ?> El Gato's Academy. All rights reserved.</p>
      </div>
    </div>

  </footer>

  <script src="js/script.js"></script>

</body>
</html>
