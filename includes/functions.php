<?php
// includes/functions.php

function get_all_tasks($conn) {
    try {
        $sql = "SELECT * FROM tasks ORDER BY 
                CASE priority 
                    WHEN 'high' THEN 1 
                    WHEN 'medium' THEN 2 
                    WHEN 'low' THEN 3 
                END, created_at DESC";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    } catch (Exception $e) {
        error_log("Error getting tasks: " . $e->getMessage());
        return [];
    }
}

function count_tasks($conn, $completed = false) {
    try {
        $status = $completed ? 1 : 0;
        $sql = "SELECT COUNT(*) as count FROM tasks WHERE is_completed = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $status);
        $stmt->execute();
        $result = $stmt->get_result();
        return (int)$result->fetch_assoc()['count'];
    } catch (Exception $e) {
        error_log("Error counting tasks: " . $e->getMessage());
        return 0;
    }
}

function add_task($conn, $task_text, $priority = 'medium') {
    try {
        // Validate priority
        $allowed_priorities = ['low', 'medium', 'high'];
        $priority = in_array(strtolower($priority), $allowed_priorities) ? strtolower($priority) : 'medium';
        
        $sql = "INSERT INTO tasks (task_text, priority) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $task_text, $priority);
        $stmt->execute();
        return $conn->insert_id; // Return the new task ID
    } catch (Exception $e) {
        error_log("Error adding task: " . $e->getMessage());
        return false;
    }
}

function complete_task($conn, $task_id) {
    try {
        $task_id = (int)$task_id;
        $sql = "UPDATE tasks SET is_completed = 1, completed_at = NOW() WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $task_id);
        $stmt->execute();
        return $stmt->affected_rows > 0; // Return true if a row was actually updated
    } catch (Exception $e) {
        error_log("Error completing task: " . $e->getMessage());
        return false;
    }
}

function delete_task($conn, $task_id) {
    try {
        $task_id = (int)$task_id;
        $sql = "DELETE FROM tasks WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $task_id);
        $stmt->execute();
        return $stmt->affected_rows > 0; // Return true if a row was actually deleted
    } catch (Exception $e) {
        error_log("Error deleting task: " . $e->getMessage());
        return false;
    }
}