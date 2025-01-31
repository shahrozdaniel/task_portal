<?php
class Task
{
	private $conn;
	private $table = "tasks";

	public $id;
	public $user_id;
	public $start_time;
	public $stop_time;
	public $notes;
	public $description;

	public function __construct($db)
	{
		$this->conn = $db;
	}

	// Create a new task
	public function create()
	{
		$query = "INSERT INTO " . $this->table . " SET user_id=:user_id, start_time=:start_time, stop_time=:stop_time, notes=:notes, description=:description";
		$stmt = $this->conn->prepare($query);

		$stmt->bindParam(":user_id", $this->user_id);
		$stmt->bindParam(":start_time", $this->start_time);
		$stmt->bindParam(":stop_time", $this->stop_time);
		$stmt->bindParam(":notes", $this->notes);
		$stmt->bindParam(":description", $this->description);

		return $stmt->execute();
	}

	// Get all tasks for a user
	public function getTasksByUser()
	{
		$query = "SELECT * FROM " . $this->table . " WHERE user_id = ?";
		$stmt = $this->conn->prepare($query);
		$stmt->bindParam(1, $this->user_id);
		$stmt->execute();
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	// Get all tasks for admin report
	public function getAllTasks()
	{
		$query = "SELECT t.id, t.start_time, t.stop_time, t.notes, t.description, u.first_name, u.last_name, u.email 
              FROM tasks t
              LEFT JOIN users u ON t.user_id = u.id";

		$stmt = $this->conn->prepare($query);
		$stmt->execute();

		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}
}
