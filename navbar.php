<?php
// session_start();
require_once __DIR__ . '/config/config.php';
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
	<div class="container-fluid">
		<a class="navbar-brand" href="<?= BASE_URL ?>index.php">Task Portal</a>
		<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
			<span class="navbar-toggler-icon"></span>
		</button>
		<div class="collapse navbar-collapse" id="navbarNav">
			<ul class="navbar-nav ms-auto">
				<?php if (isset($_SESSION['user_id'])): ?>
					<?php if ($_SESSION['is_admin']): ?>
						<!-- Admin Links -->
						<li class="nav-item">
							<a class="nav-link" href="<?= BASE_URL ?>admin/dashboard.php">Dashboard</a>
						</li>
						<li class="nav-item">
							<a class="nav-link" href="<?= BASE_URL ?>admin/create_user.php">Create User</a>
						</li>
						<li class="nav-item">
							<a class="nav-link" href="<?= BASE_URL ?>admin/download_report.php">Download Report</a>
						</li>
					<?php else: ?>
						<!-- User Links -->
						<li class="nav-item">
							<a class="nav-link" href="<?= BASE_URL ?>user/dashboard.php">Dashboard</a>
						</li>
						<li class="nav-item">
							<a class="nav-link" href="<?= BASE_URL ?>user/submit_task.php">Submit Task</a>
						</li>
						<li class="nav-item">
							<a class="nav-link" href="<?= BASE_URL ?>user/change_password.php">Change Password</a>
						</li>
					<?php endif; ?>
					<!-- Logout Link -->
					<li class="nav-item">
						<a class="nav-link" href="<?= BASE_URL ?>logout.php">Logout</a>
					</li>
				<?php else: ?>
					<!-- Login Links -->
					<li class="nav-item">
						<a class="nav-link" href="<?= BASE_URL ?>admin/login.php">Admin Login</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" href="<?= BASE_URL ?>user/login.php">User Login</a>
					</li>
				<?php endif; ?>
			</ul>
		</div>
	</div>
</nav>