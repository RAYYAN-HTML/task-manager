<?php
include 'includes/config.php';
include 'includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $task_text = trim($_POST['task']);
    $priority = isset($_POST['priority']) ? $_POST['priority'] : 'medium';
    
    if (!empty($task_text)) {
        add_task($conn, $task_text, $priority);
        session_start();
        $_SESSION['task_added'] = true;
    }
}

header('Location: index.php');
exit();