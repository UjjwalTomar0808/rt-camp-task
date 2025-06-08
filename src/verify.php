<?php
require_once 'functions.php';

$success = false;
if (isset($_GET['email']) && isset($_GET['code'])) {
	$success = verifySubscription($_GET['email'], $_GET['code']);
}
?>

<!DOCTYPE html>
<html>
<head>
	<!-- Implement Header ! -->
</head>
<body>
	<h2 id="verification-heading">Subscription Verification</h2>
	<p><?= $success ? "Subscription verified successfully!" : "Invalid or expired verification link." ?></p>
</body>
</html>
