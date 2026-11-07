<?php
// Newsletter signup is intentionally not stored anywhere yet — there's no
// subscribers table in the schema. This just gives the form a real,
// working submission so it behaves like an active feature on the front end.
// If a newsletter_subscribers table gets added later, save $_POST['email'] here.

$referer = $_SERVER['HTTP_REFERER'] ?? '../index.php';
$separator = (strpos($referer, '?') !== false) ? '&' : '?';

header('Location: ' . $referer . $separator . 'subscribed=1');
exit();
