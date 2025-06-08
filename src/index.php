<?php
require_once 'functions.php';

// TODO: Implement the task scheduler, email form and logic for email registration.

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	if (isset($_POST['task-name'])) {
		if (addTask(trim($_POST['task-name']))) {
			$message = "✅ Task added successfully.";
		} else {
			$message = "⚠️ Duplicate task or error.";
		}
	}

	if (isset($_POST['delete-id'])) {
		deleteTask($_POST['delete-id']);
		$message = "🗑️ Task deleted.";
	}

	if (isset($_POST['toggle-id'])) {
		$tasks = getAllTasks();
		foreach ($tasks as $task) {
			if ($task['id'] === $_POST['toggle-id']) {
				markTaskAsCompleted($task['id'], !$task['completed']);
				break;
			}
		}
	}

	if (isset($_POST['email'])) {
		$email = trim($_POST['email']);
		if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
			if (subscribeEmail($email)) {
				$message = "📧 Verification email sent.";
			} else {
				$message = "❌ Could not send email.";
			}
		} else {
			$message = "⚠️ Invalid email format.";
		}
	}
}

$tasks = getAllTasks();
?>
<!DOCTYPE html>
<html>

<head>
	<!-- Implement Header !-->
	<style>
		.completed { text-decoration: line-through; }
	</style>
</head>

<body>

	<?php if ($message): ?>
		<p><?= htmlspecialchars($message) ?></p>
	<?php endif; ?>

	<!-- Add Task Form -->
	<form method="POST" action="">
		<!-- Implement Form !-->
		<input type="text" name="task-name" id="task-name" placeholder="Enter new task" required>
		<button type="submit" id="add-task">Add Task</button>
	</form>

	<!-- Tasks List -->
	<ul class="tasks-list" id="tasks-list">
		<!-- Implement Tasks List (Your task item must have below
		provided elements you can modify there position, wrap them
		in another container, or add styles but they must contain
		specified classnames and input type )!-->
		<?php foreach ($tasks as $task): ?>
			<li class="task-item <?= $task['completed'] ? 'completed' : '' ?>">
				<form method="POST" style="display:inline;">
					<input type="hidden" name="toggle-id" value="<?= $task['id'] ?>">
					<input type="checkbox" class="task-status" onchange="this.form.submit()" <?= $task['completed'] ? 'checked' : '' ?>>
				</form>
				<?= htmlspecialchars($task['name']) ?>
				<form method="POST" style="display:inline;" onsubmit="return confirm('Delete this task?');">
					<input type="hidden" name="delete-id" value="<?= $task['id'] ?>">
					<button class="delete-task">Delete</button>
				</form>
			</li>
		<?php endforeach; ?>
	</ul>

	<!-- Subscription Form -->
	<form method="POST" action="">
		<!-- Implement Form !-->
		<input type="email" name="email" required />
		<button type="submit" id="submit-email">Subscribe</button>
	</form>

</body>

</html>
