<?php
session_start();
require_once '../config/Database.php';
require_once '../classes/Task.php';

if (!isset($_SESSION['user_id'])) {
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
	$start_time = $_POST['start_time'] ?? '';
	$stop_time = $_POST['stop_time'] ?? '';
	$notes = $_POST['notes'] ?? '';
	$description = $_POST['description'] ?? '';

	// Check if start time and stop time are provided
	if (empty($start_time)) {
		$errors[] = 'Start time is required.';
	}

	if (empty($stop_time)) {
		$errors[] = 'Stop time is required.';
	}

	// Check if stop time is greater than start time
	if (!empty($start_time) && !empty($stop_time) && strtotime($stop_time) <= strtotime($start_time)) {
		$errors[] = 'Stop time must be greater than start time.';
	}

	// If no errors, proceed with task creation
	if (empty($errors)) {
		try {
			// Set task properties
			$task->user_id = $_SESSION['user_id'];
			$task->start_time = $start_time;
			$task->stop_time = $stop_time;
			$task->notes = $notes;
			$task->description = $description;

			if ($task->create()) {
				echo "<div class='alert alert-success'>Task submitted successfully!</div>";
			} else {
				echo "<div class='alert alert-danger'>Failed to submit task. Please try again later.</div>";
			}
		} catch (Exception $e) {
			echo "<div class='alert alert-danger'>An error occurred: " . $e->getMessage() . "</div>";
		}
	} else {
		// Display validation errors
		foreach ($errors as $error) {
			echo "<div class='alert alert-danger'>$error</div>";
		}
	}
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Create Task</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
	<?php include '../navbar.php'; ?>
	<div class="container mt-5">
		<h2 class="mb-4">Create Task</h2>
		<div class="card shadow-sm">
			<div class="card-body">
				<form method="POST">
					<div class="mb-3">
						<label for="start_time" class="form-label">Start Time</label>
						<input type="datetime-local" name="start_time" class="form-control" required>
					</div>

					<div class="mb-3">
						<label for="stop_time" class="form-label">Stop Time</label>
						<input type="datetime-local" name="stop_time" class="form-control" required>
					</div>

					<div class="mb-3">
						<label for="notes" class="form-label">Notes</label>
						<textarea name="notes" placeholder="Add any additional notes here..." class="form-control" rows="4"></textarea>
					</div>

					<div class="mb-3">
						<label for="description" class="form-label">Description</label>
						<textarea name="description" placeholder="Provide a description of the task" class="form-control" rows="4"></textarea>
					</div>

					<button type="submit" class="btn btn-primary">Submit Task</button>
				</form>
			</div>
		</div>
	</div>

	<!-- Bootstrap JS and dependencies -->
	<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
</body>

</html>