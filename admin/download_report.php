<?php
session_start();
require_once '../config/Database.php';
require_once '../classes/Task.php';

if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
	header("Location: login.php");
	exit;
}

$database = new Database();
$db = $database->getConnection();
$task = new Task($db);

$tasks = $task->getAllTasks();

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="tasks_report.csv"');

$output = fopen('php://output', 'w');
// fputcsv($output, ['Sr. No', 'User Name', 'User Email', 'Start Time', 'Stop Time', 'Notes', 'Description']);
fputcsv($output, ['Start Time', 'Stop Time', 'Notes', 'Description']);

$sr_no = 1;
foreach ($tasks as $task) {
	// $user_name = $task['first_name'] . ' ' . $task['last_name'];

	fputcsv($output, [
		// $sr_no++,
		// $user_name,
		// $task['email'],
		$task['start_time'],
		$task['stop_time'],
		$task['notes'],
		$task['description']
	]);
}

fclose($output);
exit;
