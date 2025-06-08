<?php
require_once 'functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	if (isset($_POST['task-name'])) {
		addTask(trim($_POST['task-name']));
	}
	if (isset($_POST['delete-id'])) {
		deleteTask($_POST['delete-id']);
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
		subscribeEmail(trim($_POST['email']));
	}
}

$tasks = getAllTasks();
?>
<!DOCTYPE html>
<html>
<head>
	<!-- Implement Header !-->
</head>
<body>

	<!-- Add Task Form -->
	<form method="POST" action="">
		<input type="text" name="task-name" id="task-name" placeholder="Enter new task" required>
		<button type="submit" id="add-task">Add Task</button>
	</form>

	<!-- Tasks List -->
	<ul class="tasks-list">
		<?php foreach ($tasks as $task): ?>
			<li class="task-item <?= $task['completed'] ? 'completed' : '' ?>">
				<form method="POST" style="display:inline;">
					<input type="hidden" name="toggle-id" value="<?= $task['id'] ?>">
					<input type="checkbox" class="task-status" onchange="this.form.submit()" <?= $task['completed'] ? 'checked' : '' ?>>
				</form>
				<?= htmlspecialchars($task['name']) ?>
				<form method="POST" style="display:inline;">
					<input type="hidden" name="delete-id" value="<?= $task['id'] ?>">
					<button class="delete-task">Delete</button>
				</form>
			</li>
		<?php endforeach; ?>
	</ul>

	<!-- Subscription Form -->
	<form method="POST" action="">
		<input type="email" name="email" required />
		<button id="submit-email">Submit</button>
	</form>

</body>
</html>
