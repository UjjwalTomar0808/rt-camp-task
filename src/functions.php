<?php

/**
 * Adds a new task to the task list
 * 
 * @param string $task_name The name of the task to add.
 * @return bool True on success, false on failure.
 */
function addTask( string $task_name ): bool {
	$file  = __DIR__ . '/tasks.txt';
	$tasks = loadJsonWithComment($file);
	foreach ($tasks as $task) {
		if (strtolower($task['name']) === strtolower($task_name)) {
			return false;
		}
	}
	$tasks[] = [
		'id' => uniqid(),
		'name' => $task_name,
		'completed' => false
	];
	return saveJsonWithComment($file, $tasks);
}

/**
 * Retrieves all tasks from the tasks.txt file
 * 
 * @return array Array of tasks. -- Format [ id, name, completed ]
 */
function getAllTasks(): array {
	$file = __DIR__ . '/tasks.txt';
	return loadJsonWithComment($file);
}

/**
 * Marks a task as completed or uncompleted
 * 
 * @param string  $task_id The ID of the task to mark.
 * @param bool $is_completed True to mark as completed, false to mark as uncompleted.
 * @return bool True on success, false on failure
 */
function markTaskAsCompleted( string $task_id, bool $is_completed ): bool {
	$file  = __DIR__ . '/tasks.txt';
	$tasks = loadJsonWithComment($file);
	foreach ($tasks as &$task) {
		if ($task['id'] === $task_id) {
			$task['completed'] = $is_completed;
			return saveJsonWithComment($file, $tasks);
		}
	}
	return false;
}

/**
 * Deletes a task from the task list
 * 
 * @param string $task_id The ID of the task to delete.
 * @return bool True on success, false on failure.
 */
function deleteTask( string $task_id ): bool {
	$file  = __DIR__ . '/tasks.txt';
	$tasks = loadJsonWithComment($file);
	$tasks = array_filter($tasks, fn($task) => $task['id'] !== $task_id);
	return saveJsonWithComment($file, array_values($tasks));
}

/**
 * Generates a 6-digit verification code
 * 
 * @return string The generated verification code.
 */
function generateVerificationCode(): string {
	return str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
}

/**
 * Subscribe an email address to task notifications.
 *
 * Generates a verification code, stores the pending subscription,
 * and sends a verification email to the subscriber.
 *
 * @param string $email The email address to subscribe.
 * @return bool True if verification email sent successfully, false otherwise.
 */
function subscribeEmail( string $email ): bool {
	if (!filter_var($email, FILTER_VALIDATE_EMAIL)) return false;

	$file = __DIR__ . '/pending_subscriptions.txt';
	$code = generateVerificationCode();
	$pending = loadJsonWithComment($file);
	$pending[$email] = ['code' => $code];
	saveJsonWithComment($file, $pending);

	$link = 'http://' . $_SERVER['HTTP_HOST'] . '/src/verify.php?email=' . urlencode($email) . '&code=' . $code;
	$subject = 'Verify subscription to Task Planner';
	$body = "<p>Click the link below to verify your subscription to Task Planner:</p>
<p><a id='verification-link' href='$link'>Verify Subscription</a></p>";
	$headers = "MIME-Version: 1.0\r\nContent-type:text/html;charset=UTF-8\r\nFrom: no-reply@example.com";

	return mail($email, $subject, $body, $headers);
}

/**
 * Verifies an email subscription
 * 
 * @param string $email The email address to verify.
 * @param string $code The verification code.
 * @return bool True on success, false on failure.
 */
function verifySubscription( string $email, string $code ): bool {
	$pending_file     = __DIR__ . '/pending_subscriptions.txt';
	$subscribers_file = __DIR__ . '/subscribers.txt';

	$pending = loadJsonWithComment($pending_file);
	if (!isset($pending[$email]) || $pending[$email]['code'] !== $code) {
		return false;
	}
	unset($pending[$email]);
	saveJsonWithComment($pending_file, $pending);

	$subscribers = loadJsonWithComment($subscribers_file);
	$subscribers[$email] = true;
	return saveJsonWithComment($subscribers_file, $subscribers);
}

/**
 * Unsubscribes an email from the subscribers list
 * 
 * @param string $email The email address to unsubscribe.
 * @return bool True on success, false on failure.
 */
function unsubscribeEmail( string $email ): bool {
	$subscribers_file = __DIR__ . '/subscribers.txt';
	$subscribers = loadJsonWithComment($subscribers_file);
	if (isset($subscribers[$email])) {
		unset($subscribers[$email]);
		return saveJsonWithComment($subscribers_file, $subscribers);
	}
	return false;
}

/**
 * Sends task reminders to all subscribers
 * Internally calls  sendTaskEmail() for each subscriber
 */
function sendTaskReminders(): void {
	$subscribers_file = __DIR__ . '/subscribers.txt';
	$subscribers = loadJsonWithComment($subscribers_file);
	$tasks = getAllTasks();
	$pending = array_filter($tasks, fn($task) => !$task['completed']);
	foreach (array_keys($subscribers) as $email) {
		sendTaskEmail($email, $pending);
	}
}

/**
 * Sends a task reminder email to a subscriber with pending tasks.
 *
 * @param string $email The email address of the subscriber.
 * @param array $pending_tasks Array of pending tasks to include in the email.
 * @return bool True if email was sent successfully, false otherwise.
 */
function sendTaskEmail( string $email, array $pending_tasks ): bool {
	$subject = 'Task Planner - Pending Tasks Reminder';
	$list = '';
	foreach ($pending_tasks as $task) {
		$list .= "<li>" . htmlspecialchars($task['name']) . "</li>";
	}
	$unsubscribe_link = 'http://' . $_SERVER['HTTP_HOST'] . '/src/unsubscribe.php?email=' . urlencode($email);
	$body = "<h2>Pending Tasks Reminder</h2>
<p>Here are the current pending tasks:</p>
<ul>$list</ul>
<p><a id='unsubscribe-link' href='$unsubscribe_link'>Unsubscribe from notifications</a></p>";
	$headers = "MIME-Version: 1.0\r\nContent-type:text/html;charset=UTF-8\r\nFrom: no-reply@example.com";

	return mail($email, $subject, $body, $headers);
}

// JSON read/write helpers
function loadJsonWithComment(string $file): array {
	if (!file_exists($file)) return [];
	$lines = file($file, FILE_IGNORE_NEW_LINES);
	unset($lines[0]); // skip comment
	$json = implode("\n", $lines);
	return json_decode($json, true) ?? [];
}

function saveJsonWithComment(string $file, array $data): bool {
	$comment = "// Store the data in JSON format.";
	$json = json_encode($data, JSON_PRETTY_PRINT);
	return file_put_contents($file, $comment . "\n" . $json) !== false;
}
