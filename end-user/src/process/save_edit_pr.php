<?php
session_start();
include('../config/database.php'); // Adjust the path as necessary

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the form data
    $pr_number = $_POST['pr_number'];
    $approver = $_POST['approver'];
    $status = 'Pending'; // Reset status to Pending
    $pr_process_status = $_POST['pr_process_status'];

    // Prepare the update query
    $update_query = "UPDATE purchase_requests SET approver = ?, status = ?, pr_process_status = ? WHERE pr_number = ?";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bind_param("ssss", $approver, $status, $pr_process_status, $pr_number);

    if ($update_stmt->execute()) {
        // Success response
        echo json_encode(['status' => 'success', 'message' => 'Purchase request updated successfully.']);
    } else {
        // Error response
        echo json_encode(['status' => 'error', 'message' => 'Error updating purchase request: ' . $conn->error]);
    }

    $update_stmt->close();
}
$conn->close();
