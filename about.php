<?php
require 'config/database.php';

$pageTitle = "About Us | El Gato's Academy";
$active_page = 'about';

// Hero image for this page
$heroStmt = $conn->prepare("SELECT image FROM page_heroes WHERE page_key = 'about'");
$heroStmt->execute();
$heroRow = $heroStmt->get_result()->fetch_assoc();
$heroImage = $heroRow['image'] ?? null;

require 'includes/header.php';
require 'includes/nav.php';
?>

  <!-- HERO -->
  <section class="hero">

    <div class="hero-text-col hero-content">
      <p class="hero-eyebrow">About El Gato's Academy</p>
      <h1 style="font-size:38px;">
        Empowering learners worldwide with practical skills for <span class="highlight">real success</span>.
      </h1>
      <p>
        At El Gato's Academy, we believe education should be practical, affordable, and
        accessible to everyone. Our expert-led courses are designed to help you gain
        in-demand skills, advance your career, and build a better future.
      </p>

      <div class="hero-buttons">
        <a href="courses.php" class="btn btn-secondary">
          Explore Our Courses 
        </a>
      </div>
    </div>

    <div class="about-hero-panel">
      <div class="about-hero-ring"></div>
      <?php if ($heroImage): ?>
        <img src="<?php echo htmlspecialchars($heroImage); ?>" alt="El Gato's Academy">
      <?php else: ?>
        <div class="about-hero-placeholder">
          <i class="fa-solid fa-image"></i>
          <span>Hero image not set yet</span>
        </div>
      <?php endif; ?>
    </div>

  </section>

  <!-- STATS BAR -->
  <section class="section" style="padding-top:0;">
    <div class="container">
      <div class="stats-bar">
        <div class="stats-bar-item">
          <div class="stats-icon"><i class="fa-solid fa-graduation-cap"></i></div>
          <div>
            <h3>50K+</h3>
            <p>Students Worldwide</p>
          </div>
        </div>
        <div class="stats-bar-item">
          <div class="stats-icon"><i class="fa-solid fa-book-open"></i></div>
          <div>
            <h3>200+</h3>
            <p>Expert Instructors</p>
          </div>
        </div>
        <div class="stats-bar-item">
          <div class="stats-icon"><i class="fa-solid fa-circle-play"></i></div>
          <div>
            <h3>100+</h3>
            <p>High-Quality Courses</p>
          </div>
        </div>
        <div class="stats-bar-item">
          <div class="stats-icon"><i class="fa-solid fa-globe"></i></div>
          <div>
            <h3>10+</h3>
            <p>Countries Reached</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- MISSION / VISION / VALUES -->
  <section class="section" style="padding-top:0;">
    <div class="container">
      <div class="mvv-card">

        <div class="mvv-item">
          <div class="mvv-icon"><i class="fa-solid fa-bullseye"></i></div>
          <h3>Our Mission</h3>
          <p>
            To provide accessible, practical, and industry-relevant training that equips
            students and professionals with the digital and professional skills they need
            to succeed in education, employment, entrepreneurship, and the modern workplace.
          </p>
        </div>

        <div class="mvv-item">
          <div class="mvv-icon"><i class="fa-solid fa-eye"></i></div>
          <h3>Our Vision</h3>
          <p>
            To become a trusted learning institution that empowers individuals with
            practical skills, knowledge, and confidence to create opportunities and
            thrive in a rapidly changing world.
          </p>
        </div>

        <div class="mvv-item">
          <div class="mvv-icon"><i class="fa-solid fa-gem"></i></div>
          <h3>Our Values</h3>
          <ul class="mvv-values-list">
            <li><i class="fa-solid fa-check"></i> Quality Education</li>
            <li><i class="fa-solid fa-check"></i> Practical Learning</li>
            <li><i class="fa-solid fa-check"></i> Student Success</li>
            <li><i class="fa-solid fa-check"></i> Integrity &amp; Transparency</li>
            <li><i class="fa-solid fa-check"></i> Lifelong Learning</li>
          </ul>
        </div>

      </div>
    </div>
  </section>

  <!-- OUR STORY -->
  <section class="section" style="padding-top:0;">
    <div class="container">
      <div class="story-grid">

        <div class="story-content">
          <p class="story-eyebrow">Our Story</p>
          <h2>Built by educators.<br>Driven by impact.</h2>
          <p>
            El Gato's Academy was founded by a team of passionate educators and industry
            professionals who saw a gap between traditional education and real-world skills.
          </p>
          <p>
            We set out to build a platform where anyone, anywhere, can learn practical
            skills from experts, work on real projects, and grow confidently in their careers.
          </p>
          <p>
            Today, thousands of learners trust El Gato's Academy to help them learn, grow,
            and succeed.
          </p>
          <a href="contact.php" class="btn btn-secondary">
            Join Our Community 
          </a>
        </div>

        <div class="story-image">
          <img src="images/about/our-story.png" alt="El Gato's Academy space">
        </div>

      </div>
    </div>
  </section>

  <!-- WHAT MAKES US DIFFERENT -->
  <section class="section why-choose">
    <div class="container">
      <p class="hero-eyebrow" style="text-align:center;">Why Learn With Us?</p>
      <h2>What Makes Us Different?</h2>
      <div class="underline"></div>

      <div class="why-grid">
        <div class="why-item">
          <div class="why-icon"><i class="fa-solid fa-code"></i></div>
          <div>
            <h4>Practical &amp; Hands-on</h4>
            <p>Learn by doing with real projects and practical exercises.</p>
          </div>
        </div>
        <div class="why-item">
          <div class="why-icon"><i class="fa-solid fa-user"></i></div>
          <div>
            <h4>Expert Instructors</h4>
            <p>Learn from industry professionals with real-world experience.</p>
          </div>
        </div>
        <div class="why-item">
          <div class="why-icon"><i class="fa-solid fa-wallet"></i></div>
          <div>
            <h4>Affordable &amp; Accessible</h4>
            <p>High-quality education that won't break the bank. Learn anytime, anywhere.</p>
          </div>
        </div>
        <div class="why-item">
          <div class="why-icon"><i class="fa-solid fa-chart-line"></i></div>
          <div>
            <h4>Career Focused</h4>
            <p>Courses designed to help you get hired and grow in your career.</p>
          </div>
        </div>
        <div class="why-item">
          <div class="why-icon"><i class="fa-solid fa-award"></i></div>
          <div>
            <h4>Certificates</h4>
            <p>Earn certificates and showcase your skills to boost your career.</p>
          </div>
        </div>
      </div>
    </div>
  </section>

<?php require 'includes/footer.php'; ?>
