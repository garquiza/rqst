<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'User  not logged in']);
    exit();
}

// Include database connection
include('../config/database.php');

// Check if the required POST data is received
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['department'], $_POST['section'], $_POST['inventory_item'], $_POST['quantity'], $_POST['purpose'], $_POST['ppmp_id'])) {
    // Get the form data
    $department = mysqli_real_escape_string($conn, $_POST['department']);
    $section = mysqli_real_escape_string($conn, $_POST['section']);
    $inventory_item = (int) $_POST['inventory_item'];
    $quantity = (int) $_POST['quantity'];
    $purpose = mysqli_real_escape_string($conn, $_POST['purpose']);
    $ppmp_id = (int) $_POST['ppmp_id'];
    $end_user_id = $_SESSION['user_id']; // Assuming user_id is stored in the session

    // Generate a unique purchase request number (PR-YYYY-MM-001++)
    $pr_number = 'PR-' . date('Y-m') . '-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);

    // Default pr_process_status
    $pr_process_status = 'Not Applicable';

    // Insert the purchase request into the purchase_requests table
    $query = "INSERT INTO purchase_requests (pr_number, end_user_id, ppmp_id, pr_process_status) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("siis", $pr_number, $end_user_id, $ppmp_id, $pr_process_status);

    if ($stmt->execute()) {
        $pr_id = $stmt->insert_id; // Get the inserted pr_id

        // Insert the purchase request item into the purchase_request_items table
        $itemQuery = "INSERT INTO purchase_request_items (pr_id, department, section, inventory_id, quantity, unit_cost, purpose) 
                      SELECT ?, ?, ?, ?, ?, unit_cost, ? FROM inventory WHERE inventory_id = ?";
        $itemStmt = $conn->prepare($itemQuery);
        $itemStmt->bind_param("issdisi", $pr_id, $department, $section, $inventory_item, $quantity, $purpose, $inventory_item);

        if ($itemStmt->execute()) {
            // Success - Create a notification
            $notification_title = "New Purchase Request Submitted";
            $notification_message = "A new purchase request (PR Number: {$pr_number}) has been submitted by {$_SESSION['user_name']}.";

            // Insert notification into notifications table
            $notification_query = "INSERT INTO notifications (title, message, user_id) VALUES (?, ?, ?)";
            $notification_stmt = $conn->prepare($notification_query);
            $notification_stmt->bind_param("ssi", $notification_title, $notification_message, $end_user_id);
            $notification_stmt->execute();

            // Return JSON response
            echo json_encode([
                'status' => 'success',
                'message' => 'Purchase request submitted successfully and notification sent.'
            ]);
        } else {
            // Item insert failed
            echo json_encode([
                'status' => 'error',
                'message' => 'Failed to add item to the purchase request'
            ]);
        }
    } else {
        // Purchase request insert failed
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to create purchase request'
        ]);
    }

    $stmt->close();
    $itemStmt->close();
    $notification_stmt->close();
    $conn->close();
} else {
    // Missing form data
    echo json_encode([
        'status' => 'error',
        'message' => 'Missing required form data'
    ]);
}
