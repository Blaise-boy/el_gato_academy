<?php
session_start();
require 'config/database.php';

// Already logged in? send them to their dashboard.
if (!empty($_SESSION['student_id'])) {
    header('Location: student/dashboard.php');
    exit();
}

$errors = [];
$email = '';
$loggedOut = isset($_GET['logged_out']) && $_SERVER['REQUEST_METHOD'] !== 'POST';

// Don't let the browser keep an old copy of the login page.
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);

    if ($email === '' || $password === '') {
        $errors[] = 'Please enter both your email and password.';
    } else {
        $stmt = $conn->prepare('SELECT * FROM students WHERE email = ?');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $student = $stmt->get_result()->fetch_assoc();

        if (!$student || !password_verify($password, $student['password'])) {
            $errors[] = 'Incorrect email or password.';
        } elseif ($student['status'] !== 'active') {
            $errors[] = 'Your account is currently suspended. Please contact the academy.';
        } else {
            if ($remember) {
                // Extend the session cookie lifetime to 30 days.
                session_set_cookie_params(60 * 60 * 24 * 30);
                session_regenerate_id(true);
            }
            $_SESSION['student_id'] = $student['id'];
            $_SESSION['student_name'] = $student['first_name'] . ' ' . $student['last_name'];
            header('Location: student/dashboard.php');
            exit();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | El Gato's Academy</title>
    <meta name="description" content="Login to your El Gato's Academy student account.">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600;700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

    <div class="auth-page">

        <div class="auth-left">
            <div class="auth-left-content">
                <div class="auth-logo">
                    <a href="index.php"><img src="images/logo.png" alt="El Gato's Academy" class="auth-logo-img"></a>
                </div>

                <h1 class="auth-headline">Build Skills.<br><span class="accent">Shape Your Future.</span></h1>
                <p class="auth-subtext">Join thousands of learners gaining practical skills and real-world experience to advance their careers.</p>

                <div class="auth-feature-row">
                    <div class="auth-feature-icon"><i class="fa-solid fa-graduation-cap"></i></div>
                    <div class="auth-feature-text">
                        <strong>Expert Instructors</strong>
                        <p>Learn from industry professionals</p>
                    </div>
                </div>
                <div class="auth-feature-row">
                    <div class="auth-feature-icon"><i class="fa-solid fa-book-open"></i></div>
                    <div class="auth-feature-text">
                        <strong>Practical Learning</strong>
                        <p>Hands-on projects and real world skills</p>
                    </div>
                </div>
                <div class="auth-feature-row">
                    <div class="auth-feature-icon"><i class="fa-solid fa-award"></i></div>
                    <div class="auth-feature-text">
                        <strong>Career Advancement</strong>
                        <p>Build skills that employers value</p>
                    </div>
                </div>
            </div>

            <div class="auth-social-proof">
                <div class="auth-avatar-stack">
                    <span>EG</span>
                    <span>2K</span>
                    <span>+</span>
                </div>
                <p><strong>2,000+ students</strong><br>already learning with us</p>
            </div>
        </div>

        <div class="auth-right">
            <div class="auth-toggle-pill">Don't have an account? <a href="application.php">Sign Up</a></div>

            <div class="auth-form-wrap">
                <h1>Welcome Back!</h1>
                <p>Login to continue your learning journey with El Gato's Academy</p>

                <?php if ($loggedOut): ?>
                    <div class="auth-logout-notice">
                        <div class="auth-logout-text">
                            <i class="fa-solid fa-circle-check"></i>
                            <span>You have been logged out successfully.</span>
                        </div>
                        <a href="index.php" class="auth-back-site-btn">
                            <i class="fa-solid fa-arrow-left"></i> Back to Website
                        </a>
                    </div>
                <?php endif; ?>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-error" style="margin-bottom: 18px;">
                        <ul class="alert-list">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="POST" action="login.php" novalidate>
                    <div class="auth-form-group">
                        <label for="email">Email Address</label>
                        <div class="auth-input-wrap">
                            <i class="fa-solid fa-envelope auth-input-icon"></i>
                            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" placeholder="Enter your email address" required>
                        </div>
                    </div>

                    <div class="auth-form-group">
                        <label for="password">Password</label>
                        <div class="auth-input-wrap">
                            <i class="fa-solid fa-lock auth-input-icon"></i>
                            <input type="password" id="password" name="password" placeholder="Enter your password" required>
                            <button type="button" class="auth-toggle-visibility" onclick="togglePassword('password', this)"><i class="fa-solid fa-eye"></i></button>
                        </div>
                    </div>

                    <div class="auth-remember-row">
                        <label class="remember-check">
                            <input type="checkbox" name="remember">
                            Remember me
                        </label>
                    </div>

                    <button type="submit" class="auth-btn-submit">Login</button>
                </form>

                <div class="auth-security-note">
                    <i class="fa-solid fa-shield-halved"></i>
                    <span>Your data is protected with industry-standard security. We never share your information.</span>
                </div>

                <p class="auth-bottom-link">Don't have an account? <a href="application.php">Sign Up</a></p>
            </div>
        </div>
    </div>

    <script>
        function togglePassword(fieldId, btn) {
            const field = document.getElementById(fieldId);
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
