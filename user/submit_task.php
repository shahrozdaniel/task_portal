<?php
session_start();
require_once '../config/Database.php';
require_once '../classes/Task.php';

if (!isset($_SESSION['user_id'])) {
	header("Location: login.php");
	exit;
}

// Error handling for session
if (!isset($_SESSION['is_admin'])) {
	$_SESSION['error_message'] = 'Session is not initialized. Please log in again.';
	header("Location: login.php");
	exit;
}

if ($_SESSION['is_admin']) {
	$_SESSION['error_message'] = 'You do not have permission to access this page.';
	header("Location: login.php");
	exit;
}

$database = new Database();
$db = $database->getConnection();

if ($db === null) {
	die("Error: Unable to connect to the database.");
}

$task = new Task($db);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$errors = [];

	// Validate input
	$start_times = $_POST['start_time'] ?? [];
	$stop_times = $_POST['stop_time'] ?? [];
	$notes = $_POST['notes'] ?? [];
	$descriptions = $_POST['description'] ?? [];

	// Loop through each task and validate
	for ($i = 0; $i < count($start_times); $i++) {
		$start_time = $start_times[$i];
		$stop_time = $stop_times[$i];
		$note = $notes[$i];
		$description = $descriptions[$i];

		// Check if start time and stop time are provided
		if (empty($start_time)) {
			$errors[] = 'Start time is required for task ' . ($i + 1) . '.';
		}

		if (empty($stop_time)) {
			$errors[] = 'Stop time is required for task ' . ($i + 1) . '.';
		}

		// Check if stop time is greater than start time
		if (!empty($start_time) && !empty($stop_time) && strtotime($stop_time) <= strtotime($start_time)) {
			$errors[] = 'Stop time must be greater than start time for task ' . ($i + 1) . '.';
		}
	}

	// If no errors, proceed with task creation
	if (empty($errors)) {
		try {
			for ($i = 0; $i < count($start_times); $i++) {
				// Set task properties
				$task->user_id = $_SESSION['user_id'];
				$task->start_time = $start_times[$i];
				$task->stop_time = $stop_times[$i];
				$task->notes = $notes[$i];
				$task->description = $descriptions[$i];

				if (!$task->create()) {
					$_SESSION['error'] = "Failed to submit task " . ($i + 1) . ". Please try again later.";
					header("Location: submit_task.php");
					exit;
				}
			}
			$_SESSION['success'] = "Tasks submitted successfully!";
			header("Location: submit_task.php"); // Redirect to the same page
			exit;
		} catch (Exception $e) {
			$_SESSION['error'] = "An error occurred: " . $e->getMessage();
			header("Location: submit_task.php");
			exit;
		}
	} else {
		$_SESSION['errors'] = $errors;
		header("Location: submit_task.php");
		exit;
	}
}

// Display success or error messages from session
$successMessage = $_SESSION['success'] ?? '';
$errorMessage = $_SESSION['error'] ?? '';
$errors = $_SESSION['errors'] ?? [];

// Clear session messages after displaying them
unset($_SESSION['success']);
unset($_SESSION['error']);
unset($_SESSION['errors']);
?>

<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Create Task</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
	<style>
		.task-group {
			margin-bottom: 20px;
			padding: 15px;
			border: 1px solid #ddd;
			border-radius: 5px;
		}
	</style>
</head>

<body>
	<?php include '../navbar.php'; ?>
	<div class="container mt-5">
		<h2 class="mb-4">Create Task</h2>
		<div class="card shadow-sm">
			<div class="card-body">
				<!-- Display success or error messages -->
				<?php if (!empty($successMessage)) : ?>
					<div class="alert alert-success"><?php echo $successMessage; ?></div>
				<?php endif; ?>

				<?php if (!empty($errorMessage)) : ?>
					<div class="alert alert-danger"><?php echo $errorMessage; ?></div>
				<?php endif; ?>

				<?php if (!empty($errors)) : ?>
					<?php foreach ($errors as $error) : ?>
						<div class="alert alert-danger"><?php echo $error; ?></div>
					<?php endforeach; ?>
				<?php endif; ?>

				<form method="POST" id="taskForm">
					<div id="taskContainer">
						<div class="task-group">
							<div class="mb-3">
								<label for="start_time[]" class="form-label">Start Time</label>
								<input type="datetime-local" name="start_time[]" class="form-control" required>
							</div>

							<div class="mb-3">
								<label for="stop_time[]" class="form-label">Stop Time</label>
								<input type="datetime-local" name="stop_time[]" class="form-control" required>
							</div>

							<div class="mb-3">
								<label for="notes[]" class="form-label">Notes</label>
								<textarea name="notes[]" placeholder="Add any additional notes here..." class="form-control" rows="4"></textarea>
							</div>

							<div class="mb-3">
								<label for="description[]" class="form-label">Description</label>
								<textarea name="description[]" placeholder="Provide a description of the task" class="form-control" rows="4"></textarea>
							</div>
						</div>
					</div>

					<button type="button" class="btn btn-secondary" onclick="addTask()">Add Another Task</button>
					<button type="submit" class="btn btn-primary">Submit Tasks</button>
				</form>
			</div>
		</div>
	</div>

	<!-- Bootstrap JS and dependencies -->
	<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>

	<script>
		function addTask() {
			const taskContainer = document.getElementById('taskContainer');
			const newTaskGroup = document.createElement('div');
			newTaskGroup.className = 'task-group';
			newTaskGroup.innerHTML = `
			<div class="mb-3">
				<label for="start_time[]" class="form-label">Start Time</label>
				<input type="datetime-local" name="start_time[]" class="form-control" required>
			</div>

			<div class="mb-3">
				<label for="stop_time[]" class="form-label">Stop Time</label>
				<input type="datetime-local" name="stop_time[]" class="form-control" required>
			</div>

			<div class="mb-3">
				<label for="notes[]" class="form-label">Notes</label>
				<textarea name="notes[]" placeholder="Add any additional notes here..." class="form-control" rows="4"></textarea>
			</div>

			<div class="mb-3">
				<label for="description[]" class="form-label">Description</label>
				<textarea name="description[]" placeholder="Provide a description of the task" class="form-control" rows="4"></textarea>
			</div>

			<button type="button" class="btn btn-danger btn-sm" onclick="removeTask(this)">Remove</button>
		`;
			taskContainer.appendChild(newTaskGroup);
		}

		function removeTask(button) {
			button.parentElement.remove();
		}
	</script>

</body>

</html>