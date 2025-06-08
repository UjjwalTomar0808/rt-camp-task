<?php

/**
 * Adds a new task to the task list
 * 
 * @param string $task_name The name of the task to add.
 * @return bool True on success, false on failure.
 */
function addTask( string $task_name ): bool {
	$file  = __DIR__ . '/tasks.txt';
	$tasks = getAllTasks();
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
	return file_put_contents($file, json_encode($tasks, JSON_PRETTY_PRINT)) !== false;
}

/**
 * Retrieves all tasks from the tasks.txt file
 * 
 * @return array Array of tasks. -- Format [ id, name, completed ]
 */
function getAllTasks(): array {
	$file = __DIR__ . '/tasks.txt';
	if (!file_exists($file)) return [];
	return json_decode(file_get_contents($file), true) ?: [];
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
	$tasks = getAllTasks();
	foreach ($tasks as &$task) {
		if ($task['id'] === $task_id) {
			$task['completed'] = $is_completed;
			return file_put_contents($file, json_encode($tasks, JSON_PRETTY_PRINT)) !== false;
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
	$tasks = array_filter(getAllTasks(), fn($task) => $task['id'] !== $task_id);
	return file_put_contents($file, json_encode(array_values($tasks), JSON_PRETTY_PRINT)) !== false;
}

/**
 * Generates a 6-digit verification code
 * 
 * @return string The generated verification code.
 */
function generateVerificationCode(): string {
	return str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
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
	$file = __DIR__ . '/pending_subscriptions.txt';
	$code = generateVerificationCode();
	$pending = file_exists($file) ? json_decode(file_get_contents($file), true) : [];
	$pending[$email] = ['code' => $code];
	file_put_contents($file, json_encode($pending, JSON_PRETTY_PRINT));

	$link = 'http://' . $_SERVER['HTTP_HOST'] . '/src/verify.php?email=' . urlencode($email) . '&code=' . $code;
	$subject = 'Verify subscription to Task Planner';
	$body = "<p>Click the link below to verify your subscription to Task Planner:</p>
<p><a id=\"verification-link\" href=\"$link\">Verify Subscription</a></p>";
	$headers = "MIME-Version: 1.0\r\n";
	$headers .= "Content-type:text/html;charset=UTF-8\r\n";
	$headers .= "From: no-reply@example.com";

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

	$pending = file_exists($pending_file) ? json_decode(file_get_contents($pending_file), true) : [];
	if (!isset($pending[$email]) || $pending[$email]['code'] !== $code) {
		return false;
	}
	unset($pending[$email]);
	file_put_contents($pending_file, json_encode($pending, JSON_PRETTY_PRINT));

	$subscribers = file_exists($subscribers_file) ? file($subscribers_file, FILE_IGNORE_NEW_LINES) : [];
	if (!in_array($email, $subscribers)) {
		$subscribers[] = $email;
		file_put_contents($subscribers_file, implode("\n", $subscribers) . "\n");
	}
	return true;
}

/**
 * Unsubscribes an email from the subscribers list
 * 
 * @param string $email The email address to unsubscribe.
 * @return bool True on success, false on failure.
 */
function unsubscribeEmail( string $email ): bool {
	$subscribers_file = __DIR__ . '/subscribers.txt';
	$subscribers = file_exists($subscribers_file) ? file($subscribers_file, FILE_IGNORE_NEW_LINES) : [];
	$subscribers = array_filter($subscribers, fn($e) => trim($e) !== trim($email));
	return file_put_contents($subscribers_file, implode("\n", $subscribers) . "\n") !== false;
}

/**
 * Sends task reminders to all subscribers
 * Internally calls  sendTaskEmail() for each subscriber
 */
function sendTaskReminders(): void {
	$subscribers_file = __DIR__ . '/subscribers.txt';
	$emails = file_exists($subscribers_file) ? file($subscribers_file, FILE_IGNORE_NEW_LINES) : [];
	$tasks = getAllTasks();
	$pending = array_filter($tasks, fn($task) => !$task['completed']);
	foreach ($emails as $email) {
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
<p><a id=\"unsubscribe-link\" href=\"$unsubscribe_link\">Unsubscribe from notifications</a></p>";

	$headers = "MIME-Version: 1.0\r\n";
	$headers .= "Content-type:text/html;charset=UTF-8\r\n";
	$headers .= "From: no-reply@example.com";

	return mail($email, $subject, $body, $headers);
}
