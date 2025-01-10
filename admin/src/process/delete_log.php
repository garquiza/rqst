<?php
// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User  not logged in']);
    exit();
}

// Include database connection
require_once '../config/database.php';

// Get the log ID from the request
$data = json_decode(file_get_contents("php://input"), true);
if (isset($data['id'])) {
    $log_id = intval($data['id']);

    // Prepare the delete query
    $delete_query = "DELETE FROM history_logs WHERE id = ?";
    $stmt = $conn->prepare($delete_query);

    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Database query preparation failed']);
        exit();
    }

    $stmt->bind_param('i', $log_id);

    // Execute the delete
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Log deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete log']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
}
