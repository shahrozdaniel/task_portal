<?php
session_start();
require_once '../config/Database.php';
require_once '../classes/User.php';

if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
	header("Location: login.php");
	exit;
}

$database = new Database();
$db = $database->getConnection();

if ($db === null) {
	die("Error: Unable to connect to the database.");
}

$user = new User($db);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	// Form validation
	$errors = [];
	$first_name = $_POST['first_name'] ?? '';
	$last_name = $_POST['last_name'] ?? '';
	$email = $_POST['email'] ?? '';
	$phone = $_POST['phone'] ?? '';
	$password = $_POST['password'] ?? '';

	if (empty($first_name)) $errors[] = 'First name is required.';
	if (empty($last_name)) $errors[] = 'Last name is required.';
	if (empty($email)) $errors[] = 'Email is required.';
	if (empty($phone)) $errors[] = 'Phone is required.';
	if (empty($password)) $errors[] = 'Password is required.';

	// Check for email uniqueness
	$user->email = $email;
	if ($user->getUserByEmail()) {
		$errors[] = 'Email already exists.';
	}

	if (empty($errors)) {
		try {
			$user->first_name = $first_name;
			$user->last_name = $last_name;
			$user->phone = $phone;
			$user->password = password_hash($password, PASSWORD_DEFAULT);
			$user->is_admin = 0;

			if ($user->create()) {
				echo "<div class='alert alert-success'>User created successfully!</div>";
			} else {
				echo "<div class='alert alert-danger'>Failed to create user.</div>";
			}
		} catch (Exception $e) {
			echo "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
		}
	} else {
		// Display errors
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
	<title>Create User</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
	<style>
		body {
			background-color: #f4f7fa;
		}

		.container {
			max-width: 600px;
			margin-top: 30px;
		}

		.form-control {
			border-radius: 5px;
		}

		.btn-primary {
			width: 100%;
			border-radius: 5px;
			padding: 10px;
		}

		.form-check-input {
			margin-left: 10px;
		}
	</style>
</head>

<body>
	<?php include '../navbar.php'; ?>
	<div class="container">
		<h2>Create User</h2>
		<form method="POST">
			<?php if (!empty($errors)): ?>
				<div class="alert alert-danger"><?= implode('<br>', $errors) ?></div>
			<?php endif; ?>

			<div class="mb-3">
				<label for="first_name" class="form-label">First Name</label>
				<input type="text" class="form-control" id="first_name" name="first_name" required>
			</div>
			<div class="mb-3">
				<label for="last_name" class="form-label">Last Name</label>
				<input type="text" class="form-control" id="last_name" name="last_name" required>
			</div>
			<div class="mb-3">
				<label for="email" class="form-label">Email</label>
				<input type="email" class="form-control" id="email" name="email" required>
			</div>
			<div class="mb-3">
				<label for="phone" class="form-label">Phone</label>
				<input type="text" class="form-control" id="phone" name="phone" required>
			</div>
			<div class="mb-3">
				<label for="password" class="form-label">Password</label>
				<input type="text" class="form-control" id="password" name="password" value="" required>
			</div>
			<div class="mb-3 form-check">
				<input type="checkbox" class="form-check-input" id="generate_password" onchange="togglePasswordGeneration()">
				<label class="form-check-label" for="generate_password">Auto-generate strong password</label>
			</div>

			<button type="submit" class="btn btn-primary">Create User</button>
		</form>
	</div>

	<script>
		// Toggle password generation
		function togglePasswordGeneration() {
			const passwordField = document.getElementById('password');
			const generatePasswordCheckbox = document.getElementById('generate_password');

			if (generatePasswordCheckbox.checked) {
				const generatedPassword = generateStrongPassword();
				passwordField.value = generatedPassword;
				passwordField.readOnly = false;
			} else {
				passwordField.value = '';
				passwordField.readOnly = true;
			}
		}

		// Generate a strong password
		function generateStrongPassword() {
			const chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_+';
			let password = '';
			for (let i = 0; i < 12; i++) {
				const randomIndex = Math.floor(Math.random() * chars.length);
				password += chars.charAt(randomIndex);
			}
			return password;
		}
	</script>
</body>

</html>