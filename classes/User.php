<?php
class User
{
	private $conn;
	private $table = "users";

	public $id;
	public $first_name;
	public $last_name;
	public $email;
	public $phone;
	public $password;
	public $last_login;
	public $last_password_change;
	public $is_admin;

	public function __construct($db)
	{
		$this->conn = $db;
	}

	// Create a new user
	public function create()
	{
		try {
			$query = "INSERT INTO " . $this->table . " 
					  SET first_name=:first_name, last_name=:last_name, email=:email, 
						  phone=:phone, password=:password, is_admin=:is_admin";
			$stmt = $this->conn->prepare($query);

			$stmt->bindParam(":first_name", $this->first_name);
			$stmt->bindParam(":last_name", $this->last_name);
			$stmt->bindParam(":email", $this->email);
			$stmt->bindParam(":phone", $this->phone);
			$stmt->bindParam(":password", $this->password);
			$stmt->bindParam(":is_admin", $this->is_admin);

			return $stmt->execute();
		} catch (Exception $e) {
			error_log("User creation error: " . $e->getMessage());
			throw new Exception("An error occurred while creating the user.");
		}
	}

	// Get user by email
	public function getUserByEmail()
	{
		try {
			$query = "SELECT * FROM " . $this->table . " WHERE email = ? LIMIT 1";
			$stmt = $this->conn->prepare($query);
			$stmt->bindParam(1, $this->email);
			$stmt->execute();
			return $stmt->fetch(PDO::FETCH_ASSOC);
		} catch (Exception $e) {
			error_log("User fetch error: " . $e->getMessage());
			throw new Exception("An error occurred while fetching the user.");
		}
	}

	// Get user by ID
	public function getUserById($id)
	{
		try {
			$query = "SELECT * FROM " . $this->table . " WHERE id = :id LIMIT 1";
			$stmt = $this->conn->prepare($query);
			$stmt->bindParam(":id", $id, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->fetch(PDO::FETCH_ASSOC);
		} catch (Exception $e) {
			error_log("User fetch error by ID: " . $e->getMessage());
			throw new Exception("An error occurred while fetching the user.");
		}
	}

	// Update user password
	public function updatePassword($newPassword)
	{
		try {
			// Check if the password is already hashed
			if (!password_get_info($newPassword)['algo']) {
				$newPassword = password_hash($newPassword, PASSWORD_DEFAULT);
			}

			$query = "UPDATE " . $this->table . " 
                  SET password = :password, last_password_change = NOW() 
                  WHERE id = :id";
			$stmt = $this->conn->prepare($query);

			$stmt->bindParam(":password", $newPassword);
			$stmt->bindParam(":id", $this->id);

			return $stmt->execute();
		} catch (Exception $e) {
			error_log("Password update error: " . $e->getMessage());
			throw new Exception("An error occurred while updating the password.");
		}
	}



	// Update last password change timestamp
	public function updateLastPasswordChange()
	{
		try {
			$query = "UPDATE " . $this->table . " SET last_password_change = NOW() WHERE id = :id";
			$stmt = $this->conn->prepare($query);
			$stmt->bindParam(":id", $this->id, PDO::PARAM_INT);
			return $stmt->execute();
		} catch (Exception $e) {
			error_log("Last password change update error: " . $e->getMessage());
			throw new Exception("An error occurred while updating the last password change time.");
		}
	}
}
