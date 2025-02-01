<?php
session_start();
require_once '../config/Database.php';
require_once '../classes/Auth.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
	if ($_SESSION['is_admin']) {
		header("Location: ../admin/dashboard.php");
	} else {
		header("Location: dashboard.php");
	}
	exit;
}

$database = new Database();
$db = $database->getConnection();

if ($db === null) {
	die("Error: Unable to connect to the database.");
}

$auth = new Auth($db);
$error_message = ''; // Initialize error message

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	// Input validation
	$email = trim($_POST['email'] ?? '');
	$password = trim($_POST['password'] ?? '');

	if (empty($email) || empty($password)) {
		$error_message = 'Please fill in all fields.';
	} else {
		try {
			// Attempt login
			$user = $auth->login($email, $password);

			if ($user && $user['is_admin'] == 0) {
				$_SESSION['user_id'] = $user['id'];
				$_SESSION['is_admin'] = false;
				$_SESSION['login_timestamp'] = time(); // Save login time

				// Check if password change is required
				if ($auth->needsPasswordChange($user['last_password_change'])) {
					header("Location: change_password.php");
					exit;
				} else {
					header("Location: dashboard.php");
					exit;
				}
			} else {
				$error_message = "Invalid credentials or not a user.";
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
	<title>User Login</title>
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
			<h2>User Login</h2>

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