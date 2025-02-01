<?php
session_start();

if (!isset($_SESSION['user_id'])) {
	$_SESSION['error_message'] = 'You must be logged in to access the dashboard.';
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

require_once '../config/Database.php';

try {
	$database = new Database();
	$db = $database->getConnection();
} catch (Exception $e) {
	$_SESSION['error_message'] = 'Database connection failed: ' . $e->getMessage();
	header("Location: login.php");
	exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>User Dashboard</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
	<?php
	if (isset($_SESSION['error_message'])) {
		echo "<div class='alert alert-danger'>" . $_SESSION['error_message'] . "</div>";
		unset($_SESSION['error_message']);
	}
	?>

	<?php include '../navbar.php'; ?>
	<div class="container mt-5">
		<h2>User Dashboard</h2>
		<div class="list-group">
			<a href="submit_task.php" class="list-group-item list-group-item-action">Submit Task</a>
			<a href="change_password.php" class="list-group-item list-group-item-action">Change Password</a>
			<a href="../logout.php" class="list-group-item list-group-item-action">Logout</a>
		</div>
	</div>

	<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
</body>

</html>