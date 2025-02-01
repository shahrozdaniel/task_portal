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

$errors = [];
$successMessage = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	// Form validation
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
				$successMessage = "User created successfully!";
				header("refresh:2;url=dashboard.php"); // Redirect after 2 seconds
			} else {
				$errors[] = 'Failed to create user.';
			}
		} catch (Exception $e) {
			$errors[] = "Error: " . $e->getMessage();
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
</head>

<body>
	<?php include '../navbar.php'; ?>
	<div class="container mt-5">
		<h2 class="mb-4">Create User</h2>
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
		</div>
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

	<!-- Bootstrap JS and dependencies -->
	<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
</body>

</html>