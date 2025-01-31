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

			if ($userData && password_verify($password, $userData['password'])) {
				// echo "Entered Password: " . $password . "<br>";
				// echo "Entered Hashed Password: " . password_hash($password, PASSWORD_BCRYPT) . "<br>";
				// echo "Stored Hashed Password: " . $userData['password'] . "<br>";
				return $userData;
			} 
			// else {
			// 	echo "User not found.<br>";
			// }

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
