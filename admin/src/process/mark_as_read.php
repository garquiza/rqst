<?php
session_start();

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
    exit();
}

// Include database connection
require_once '../config/database.php'; // Adjust path as needed

// Validate and sanitize the input
if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid notification ID.']);
    exit();
}

$notification_id = intval($_POST['id']);
$user_id = $_SESSION['user_id']; // Assuming the user ID is stored in session

// Check if user_id is set
if (!isset($user_id)) {
    echo json_encode(['status' => 'error', 'message' => 'User  not logged in.']);
    exit();
}

// Prepare and execute the query to mark the notification as read
$query = "UPDATE notifications SET is_read = TRUE WHERE id = ? AND user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $notification_id, $user_id);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo json_encode(['status' => 'success', 'message' => 'Notification marked as read.']);
    } else {
        // If no rows were affected, it means the notification ID was invalid for this user
        echo json_encode(['status' => 'error', 'message' => 'Notification not found or already marked as read.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Unable to mark the notification as read.']);
}

$stmt->close();
$conn->close();
