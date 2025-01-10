<?php
// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User  not logged in.']);
    exit();
}

// Include database connection
require_once '../config/pdo.php';

// Get the input data
$data = json_decode(file_get_contents("php://input"), true);
$userId = isset($data['id']) ? intval($data['id']) : 0;

// Validate user ID
if ($userId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid user ID.']);
    exit();
}

// Prepare the delete statement
$sql = "DELETE FROM budget_users WHERE id = :id";
$stmt = $pdo->prepare($sql);

try {
    // Execute the delete statement
    $stmt->execute(['id' => $userId]);

    // Check if any row was deleted
    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'User  deleted successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'User  not found or already deleted.']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
