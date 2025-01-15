<?php
session_start();

// Redirect to login page if the user is not logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'User not logged in'
    ]);
    exit();
}

// Include database connection
include('../config/database.php');

// Process the form when submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Capture form data
    $pr_number = isset($_POST['pr_number']) ? $_POST['pr_number'] : '';
    $department = isset($_POST['department']) ? $_POST['department'] : '';
    $section = isset($_POST['section']) ? $_POST['section'] : '';
    $purpose = isset($_POST['purpose']) ? $_POST['purpose'] : '';

    // Check if all required data is available
    if (empty($pr_number) || empty($department) || empty($section) || empty($purpose)) {
        echo json_encode([
            'success' => false,
            'message' => 'Missing required data'
        ]);
        exit();
    }

    // Prepare the query to update the purchase_request_items table
    $query = "UPDATE purchase_request_items 
              SET department = ?, section = ?, purpose = ? 
              WHERE pr_id = (SELECT pr_id FROM purchase_requests WHERE pr_number = ? AND end_user_id = ?)";

    // Prepare the statement
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssssi", $department, $section, $purpose, $pr_number, $_SESSION['user_id']);

    if ($stmt->execute()) {
        // Success - Create a notification
        $notification_title = "Purchase Request Updated";
        $notification_message = "The purchase request (PR Number: {$pr_number}) has been updated by {$_SESSION['user_name']}.";

        // Insert notification into notifications table
        $notification_query = "INSERT INTO notifications (title, message, user_id) VALUES (?, ?, ?)";
        $notification_stmt = $conn->prepare($notification_query);
        $notification_stmt->bind_param("ssi", $notification_title, $notification_message, $_SESSION['user_id']);
        $notification_stmt->execute();

        // Return JSON response
        echo json_encode([
            'success' => true,
            'message' => 'Purchase Request updated successfully and notification sent.'
        ]);
    } else {
        // Update failed - Log error
        error_log($stmt->error);  // Log the error
        echo json_encode([
            'success' => false,
            'message' => 'Failed to update the purchase request.'
        ]);
    }

    // Close statements and connection
    $stmt->close();
    $notification_stmt->close();
    $conn->close();
} else {
    // Missing form data
    echo json_encode([
        'success' => false,
        'message' => 'Missing required form data'
    ]);
}
