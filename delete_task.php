<?php
$conn = new mysqli("localhost", "root", "", "task_manager");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

if(isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $conn->query("DELETE FROM tasks WHERE id = $id");
}

header("Location: index.php");