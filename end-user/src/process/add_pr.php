<?php
session_start();

// Redirect to login page if the user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch user details from session
$user_name = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'User';

// Include database connection
include('../config/database.php');

// Process the form when submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Capture form data
    $ppmp_id = isset($_POST['ppmp_id']) ? $_POST['ppmp_id'] : '';
    $department = isset($_POST['department']) ? $_POST['department'] : '';
    $section = isset($_POST['section']) ? $_POST['section'] : '';
    $general_item = isset($_POST['general_item']) ? $_POST['general_item'] : '';
    $quantity = isset($_POST['quantity']) ? $_POST['quantity'] : 0;
    $unit_cost = isset($_POST['unit_cost']) ? $_POST['unit_cost'] : 0;
    $purpose = isset($_POST['purpose']) ? $_POST['purpose'] : '';

    // Calculate the total cost
    $total_cost = $quantity * $unit_cost;

    // Generate a unique purchase request number (PR-YYYY-MM-001++)
    $pr_number = 'PR-' . date('Y-m') . '-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);

    // Default pr_process_status
    $pr_process_status = 'Not Applicable';

    // Insert the purchase request into the purchase_requests table
    $query = "INSERT INTO purchase_requests (pr_number, end_user_id, ppmp_id, pr_process_status) 
              VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("siis", $pr_number, $_SESSION['user_id'], $ppmp_id, $pr_process_status);

    if ($stmt->execute()) {
        $pr_id = $stmt->insert_id; // Get the inserted pr_id

        // Insert the purchase request item into the purchase_request_items table
        $itemQuery = "INSERT INTO purchase_request_items (pr_id, department, section, general_item, quantity, unit_cost, total_cost, purpose) 
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $itemStmt = $conn->prepare($itemQuery);
        $itemStmt->bind_param("isssdiis", $pr_id, $department, $section, $general_item, $quantity, $unit_cost, $total_cost, $purpose);

        if ($itemStmt->execute()) {
            // Success - Create a notification
            $notification_title = "New Purchase Request Submitted";
            $notification_message = "A new purchase request (PR Number: {$pr_number}) has been submitted by {$_SESSION['user_name']}.";

            // Insert notification into notifications table
            $notification_query = "INSERT INTO notifications (title, message, user_id) VALUES (?, ?, ?)";
            $notification_stmt = $conn->prepare($notification_query);
            $notification_stmt->bind_param("ssi", $notification_title, $notification_message, $_SESSION['user_id']);
            $notification_stmt->execute();

            // Return JSON response
            echo json_encode([
                'success' => true,
                'message' => 'Purchase Request submitted successfully and notification sent.'
            ]);
        } else {
            // Item insert failed
            echo json_encode([
                'success' => false,
                'message' => 'Failed to add item to the purchase request.'
            ]);
        }
    } else {
        // Purchase request insert failed
        echo json_encode([
            'success' => false,
            'message' => 'Failed to create purchase request.'
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
