<?php
include 'includes/config.php'; // Make sure this path is correct
include 'includes/functions.php';

header('Content-Type: application/json');

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    // Use the mysqli connection from config.php
    if (complete_task($conn, $id)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to complete task']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'No task ID provided']);
}