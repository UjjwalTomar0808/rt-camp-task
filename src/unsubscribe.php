<?php
require_once 'functions.php';

// TODO: Implement the unsubscription logic.

$unsubscribed = false;
if (isset($_GET['email'])) {
	$unsubscribed = unsubscribeEmail($_GET['email']);
}
?>

<!DOCTYPE html>
<html>
<head>
	<!-- Implement Header ! -->
</head>
<body>
	<!-- Do not modify the ID of the heading -->
	<h2 id="unsubscription-heading">Unsubscribe from Task Updates</h2>
	<!-- Implemention body -->
	<p>
		<?= $unsubscribed ? "✅ You have been unsubscribed successfully." : "❌ Unsubscription failed." ?>
	</p>
</body>
</html>
