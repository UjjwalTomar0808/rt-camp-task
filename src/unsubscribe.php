<?php
require_once 'functions.php';

// TODO: Implement the unsubscription logic.

$success = false;
if (isset($_GET['email'])) {
	$success = unsubscribeEmail($_GET['email']);
}
?>

<!DOCTYPE html>
<html>
<head>
	<!-- Implement Header ! -->
</head>
<body>
	<h2 id="unsubscription-heading">Unsubscribe from Task Updates</h2>
	<p><?= $success ? "You have been unsubscribed successfully." : "Unsubscription failed or email not found." ?></p>
</body>
</html>
