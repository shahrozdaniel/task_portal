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
						return $userData;
					}
				} else {
					if (password_verify($password, $userData['password'])) {
						return $userData;
					}
				}
			}

			// // If password verification fails
			// echo 'Wrong password';
			return false;
		} catch (Exception $e) {
			error_log("Auth login error: " . $e->getMessage());
			throw new Exception("An error occurred during login.");
		}
	}

	// Check if user needs to change password
	public function needsPasswordChange($last_password_change)
	{
		try {
			$diff = strtotime(date("Y-m-d H:i:s")) - strtotime($last_password_change);
			return ($diff > 30 * 24 * 60 * 60); // 30 days in seconds
		} catch (Exception $e) {
			error_log("Auth password change check error: " . $e->getMessage());
			throw new Exception("An error occurred while checking password change.");
		}
	}
}
