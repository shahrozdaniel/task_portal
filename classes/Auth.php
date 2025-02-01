<?php
require_once 'User.php';

class Auth
{
	private $conn;

	public function __construct($db)
	{
		$this->conn = $db;
	}

	// Login user
	public function login($email, $password)
	{
		try {
			$user = new User($this->conn);
			$user->email = $email;
			$userData = $user->getUserByEmail();

			if ($userData) {
				if (strlen($userData['password']) == 32) {
					if (md5($password) === $userData['password']) {
						$hashedPassword = password_hash($password, PASSWORD_DEFAULT);
						$user->password = $hashedPassword;
						$user->updatePassword($hashedPassword);
					}
				} elseif (!password_verify($password, $userData['password'])) {
					return false;
				}

				// Update last login timestamp
				$this->updateLastLogin($userData['id']);

				return $userData;
			}

			return false;
		} catch (Exception $e) {
			error_log("Auth login error: " . $e->getMessage());
			throw new Exception("An error occurred during login.");
		}
	}

	// Function to update last_login timestamp
	public function updateLastLogin($userId)
	{
		try {
			$query = "UPDATE users SET last_login = NOW() WHERE id = :id";
			$stmt = $this->conn->prepare($query);
			$stmt->bindParam(":id", $userId);
			$stmt->execute();
		} catch (Exception $e) {
			error_log("Update last login error: " . $e->getMessage());
		}
	}


	// Check if user needs to change password
	public function needsPasswordChange($last_password_change)
	{
		try {
			$loginTime = $_SESSION['login_timestamp'] ?? 0;

			// If last_password_change is null, force a password change
			if (empty($last_password_change)) {
				return true;
			}

			// Convert timestamps to Unix time
			$lastChangeTime = strtotime($last_password_change);
			$currentTime = time();

			// Check if the password hasn't been changed since login
			if ($lastChangeTime < $loginTime) {
				return true;
			}

			// Enforce password change if older than 30 days
			return ($currentTime - $lastChangeTime) > (30 * 24 * 60 * 60);
		} catch (Exception $e) {
			error_log("Auth password change check error: " . $e->getMessage());
			throw new Exception("An error occurred while checking password change.");
		}
	}
}
