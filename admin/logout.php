<?php
session_start();

// Only clear this admin's own session data — leave any other active
// session (e.g. a student logged in in the same browser) untouched.
unset($_SESSION['admin_id']);
unset($_SESSION['admin_name']);

header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

header('Location: login.php');
exit();
