<?php
session_start();
require_once '../config/Database.php';
require_once '../classes/Auth.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
	if ($_SESSION['is_admin']) {
		header("Location: dashboard.php");
	} else {
		header("Location: ../user/dashboard.php");
	}
	exit;
}

// Initialize error message
$error_message = '';

// Database connection
try {
	$database = new Database();
	$db = $database->getConnection();
} catch (PDOException $e) {
	error_log("Database connection error: " . $e->getMessage());
	$error_message = "Unable to connect to the database. Please try again later.";
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$email = trim($_POST['email']);
	$password = trim($_POST['password']);

	// Validate inputs
	if (empty($email) || empty($password)) {
		$error_message = "Please fill in all fields.";
	} else {
		try {
			$auth = new Auth($db);
			$user = $auth->login($email, $password);

			if ($user && $user['is_admin'] == 1) {
				// Login successful
				$_SESSION['user_id'] = $user['id'];
				$_SESSION['is_admin'] = true;
				header("Location: dashboard.php");
				exit;
			} else {
				$error_message = "Invalid credentials or not an admin.";
			}
		} catch (Exception $e) {
			error_log("Login error: " . $e->getMessage());
			$error_message = "An error occurred during login. Please try again.";
		}
	}
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Admin Login</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
	<style>
		body {
			background-color: #f4f7fa;
			font-family: Arial, sans-serif;
		}

		.login-container {
			max-width: 400px;
			margin: 0 auto;
			padding: 30px;
			background-color: #fff;
			border-radius: 8px;
			box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
		}

		h2 {
			text-align: center;
			margin-bottom: 30px;
		}

		.form-control {
			border-radius: 5px;
		}

		.btn-primary {
			width: 100%;
			border-radius: 5px;
			padding: 10px;
		}

		.alert {
			margin-bottom: 20px;
		}
	</style>
</head>

<body>
	<?php include '../navbar.php'; ?>
	<div class="container">
		<div class="login-container">
			<h2>Admin Login</h2>

			<?php if (!empty($error_message)): ?>
				<div class="alert alert-danger"><?= htmlspecialchars($error_message) ?></div>
			<?php endif; ?>

			<form method="POST">
				<div class="mb-3">
					<label for="email" class="form-label">Email</label>
					<input type="email" class="form-control" id="email" name="email" required placeholder="Enter your email">
				</div>
				<div class="mb-3">
					<label for="password" class="form-label">Password</label>
					<input type="password" class="form-control" id="password" name="password" required placeholder="Enter your password">
				</div>
				<button type="submit" class="btn btn-primary">Login</button>
			</form>
		</div>
	</div>

	<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
</body>

</html>