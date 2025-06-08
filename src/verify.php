<?php
require_once 'functions.php';

// TODO: Implement verification logic.

$verified = false;
if (isset($_GET['email'], $_GET['code'])) {
	$verified = verifySubscription($_GET['email'], $_GET['code']);
}
?>

<!DOCTYPE html>
<html>
<head>
	<!-- Implement Header ! -->
</head>
<body>
	<!-- Do not modify the ID of the heading -->
	<h2 id="verification-heading">Subscription Verification</h2>
	<!-- Implemention body -->
	<p>
		<?= $verified ? "✅ Your email has been verified successfully!" : "❌ Verification failed or expired." ?>
	</p>
</body>
</html>
