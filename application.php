<?php
session_start();
require 'config/database.php';

if (!empty($_SESSION['student_id'])) {
    header('Location: student/dashboard.php');
    exit();
}

$errors = [];
$full_name = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $agree = isset($_POST['agree']);

    if ($full_name === '')                     $errors[] = 'Full name is required.';
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'A valid email address is required.';
    if (strlen($password) < 8)                  $errors[] = 'Password must be at least 8 characters.';
    if (!preg_match('/[A-Z]/', $password))       $errors[] = 'Password must contain at least one uppercase letter.';
    if (!preg_match('/[0-9]/', $password))       $errors[] = 'Password must contain at least one number.';
    if (!preg_match('/[\W_]/', $password))       $errors[] = 'Password must contain at least one special character.';
    if ($password !== $confirm_password)         $errors[] = 'Passwords do not match.';
    if (!$agree)                                 $errors[] = 'You must agree to the Terms of Service and Privacy Policy.';

    if (empty($errors)) {
        // Make sure this email hasn't already applied or enrolled.
        $check = $conn->prepare('SELECT id FROM applications WHERE email = ? UNION SELECT id FROM students WHERE email = ?');
        $check->bind_param('ss', $email, $email);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            $errors[] = 'An account with this email already exists or has a pending application.';
        }
    }

    if (empty($errors)) {
        $nameParts = preg_split('/\s+/', $full_name, 2);
        $first_name = $nameParts[0];
        $last_name = $nameParts[1] ?? '';
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare('
            INSERT INTO applications (first_name, last_name, email, password, status)
            VALUES (?, ?, ?, ?, ?)
        ');
        $status = 'pending';
        $stmt->bind_param('sssss', $first_name, $last_name, $email, $hashedPassword, $status);
        $stmt->execute();

        header('Location: application.php?submitted=1');
        exit();
    }
}

$submitted = isset($_GET['submitted']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Your Account | El Gato's Academy</title>
    <meta name="description" content="Sign up to get started with El Gato's Academy.">

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
            <div class="auth-toggle-pill">Have an account? <a href="login.php">Login</a></div>

            <div class="auth-form-wrap">

                <?php if ($submitted): ?>
                    <h1>Application Submitted!</h1>
                    <p>Thanks for signing up. Our admissions team will review your application and activate your account. You'll be notified by email once it's approved.</p>
                    <a href="index.php" class="btn btn-primary" style="display:inline-block; margin-top:10px;">Back to Homepage</a>
                <?php else: ?>
                    <h1>Create Your Account</h1>
                    <p>Sign up to get started with El Gato's Academy</p>

                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-error" style="margin-bottom: 18px;">
                            <ul class="alert-list">
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo htmlspecialchars($error); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="application.php" novalidate>
                        <div class="auth-form-group">
                            <label for="full_name">Full Name</label>
                            <div class="auth-input-wrap">
                                <i class="fa-solid fa-user auth-input-icon"></i>
                                <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($full_name); ?>" placeholder="Enter your full name" required>
                            </div>
                        </div>

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
                                <input type="password" id="password" name="password" placeholder="Create a password" oninput="checkPasswordRules(this.value)" required>
                                <button type="button" class="auth-toggle-visibility" onclick="togglePassword('password', this)"><i class="fa-solid fa-eye"></i></button>
                            </div>
                        </div>

                        <div class="auth-form-group">
                            <label for="confirm_password">Confirm Password</label>
                            <div class="auth-input-wrap">
                                <i class="fa-solid fa-lock auth-input-icon"></i>
                                <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm your password" required>
                                <button type="button" class="auth-toggle-visibility" onclick="togglePassword('confirm_password', this)"><i class="fa-solid fa-eye"></i></button>
                            </div>
                        </div>

                        <div class="auth-password-rules">
                            <p class="title">Password must contain:</p>
                            <span class="rule" id="rule-length"><i class="fa-solid fa-circle-check"></i> At least 8 characters</span>
                            <span class="rule" id="rule-number"><i class="fa-solid fa-circle-check"></i> One number</span>
                            <span class="rule" id="rule-upper"><i class="fa-solid fa-circle-check"></i> One uppercase letter</span>
                            <span class="rule" id="rule-special"><i class="fa-solid fa-circle-check"></i> One special character</span>
                        </div>

                        <div class="auth-checkbox-row">
                            <input type="checkbox" id="agree" name="agree" required>
                            <label for="agree">I agree to the Terms of Service and Privacy Policy</label>
                        </div>

                        <button type="submit" class="auth-btn-submit">Create Account</button>
                    </form>

                    <p class="auth-bottom-link">Have an account? <a href="login.php">Login</a></p>
                <?php endif; ?>
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

        function checkPasswordRules(value) {
            setRule('rule-length', value.length >= 8);
            setRule('rule-number', /[0-9]/.test(value));
            setRule('rule-upper', /[A-Z]/.test(value));
            setRule('rule-special', /[\W_]/.test(value));
        }

        function setRule(id, met) {
            const el = document.getElementById(id);
            el.classList.toggle('met', met);
        }
    </script>

</body>
</html>
