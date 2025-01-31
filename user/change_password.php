<?php
session_start();
require_once '../config/Database.php';
require_once '../classes/User.php';

if (!isset($_SESSION['user_id'])) {
	header("Location: login.php");
	exit;
}

$database = new Database();
$db = $database->getConnection();

if ($db === null) {
	die("Error: Unable to connect to the database.");
}

$user = new User($db);
$user->id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$errors = [];
	$new_password = $_POST['new_password'] ?? '';

	// Input validation
	if (empty($new_password)) {
		$errors[] = 'New password is required.';
	}

	if (strlen($new_password) < 8) {
		$errors[] = 'Password must be at least 8 characters long.';
	}

	// If no errors, proceed with password update
	if (empty($errors)) {
		try {
			// Hash the new password before saving it
			// $user->password = password_hash($new_password, PASSWORD_DEFAULT);
			$hashedPassword = password_hash($new_password, PASSWORD_DEFAULT);

			if ($user->updatePassword($hashedPassword)) {
				echo "<div class='alert alert-success'>Password updated successfully!</div>";
				header("Location: dashboard.php");
				exit;
			} else {
				echo "<div class='alert alert-danger'>Failed to update password.</div>";
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
	<title>Change Password</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
	<?php include '../navbar.php'; ?>
	<div class="container mt-5">
		<h2>Change Password</h2>
		<form method="POST">
			<div class="mb-3">
				<label for="new_password" class="form-label">New Password</label>
				<input type="password" class="form-control" id="new_password" name="new_password" required>
			</div>
			<button type="submit" class="btn btn-primary">Change Password</button>
		</form>
	</div>
</body>

</html>