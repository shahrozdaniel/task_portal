<?php
session_start();
require_once '../config/Database.php';
require_once '../classes/User.php';

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

$user = new User($db);
$user->id = $_SESSION['user_id'];

// Fetch user data
$userData = $user->getUserById($_SESSION['user_id']);
$currentPasswordHash = $userData['password'];

$errors = [];
$successMessage = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$new_password = trim($_POST['new_password'] ?? '');
	$confirm_password = trim($_POST['confirm_password'] ?? '');

	// Input validation
	if (empty($new_password)) {
		$errors[] = 'New password is required.';
	}

	if (strlen($new_password) < 8) {
		$errors[] = 'Password must be at least 8 characters long.';
	}

	if ($new_password !== $confirm_password) {
		$errors[] = 'Passwords do not match.';
	}

	if (password_verify($new_password, $currentPasswordHash)) {
		$errors[] = 'New password cannot be the same as the current password.';
	}

	// If no errors, proceed with password update
	if (empty($errors)) {
		try {
			$hashedPassword = password_hash($new_password, PASSWORD_DEFAULT);
			$user->id = $_SESSION['user_id'];

			if ($user->updatePassword($hashedPassword)) {
				$_SESSION['login_timestamp'] = time(); // Reset session timestamp
				$user->updateLastPasswordChange(); // Update last password change time

				$successMessage = "Password updated successfully!";
				header("refresh:2;url=dashboard.php"); // Redirect after 2 seconds
			} else {
				$errors[] = 'Failed to update password.';
			}
		} catch (Exception $e) {
			error_log("Password update error: " . $e->getMessage());
			$errors[] = "An error occurred. Please try again.";
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
		<h2 class="mb-4">Change Password</h2>
		<div class="card shadow-sm">
			<div class="card-body">
				<?php if (!empty($errors)): ?>
					<div class="alert alert-danger">
						<ul>
							<?php foreach ($errors as $error): ?>
								<li><?= htmlspecialchars($error) ?></li>
							<?php endforeach; ?>
						</ul>
					</div>
				<?php endif; ?>

				<?php if (!empty($successMessage)): ?>
					<div class="alert alert-success">
						<?= htmlspecialchars($successMessage) ?>
					</div>
				<?php endif; ?>

				<form method="POST">
					<div class="mb-3">
						<label for="new_password" class="form-label">New Password</label>
						<input type="password" name="new_password" class="form-control" required>
					</div>
					<div class="mb-3">
						<label for="confirm_password" class="form-label">Confirm Password</label>
						<input type="password" name="confirm_password" class="form-control" required>
					</div>
					<button type="submit" class="btn btn-primary">Change Password</button>
				</form>
			</div>
		</div>
	</div>

	<!-- Bootstrap JS and dependencies -->
	<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
</body>

</html>