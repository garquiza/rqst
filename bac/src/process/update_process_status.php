<?php
session_start();
require_once '../config/database.php'; // Adjust the path as necessary

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if (isset($_GET['pr_id']) && isset($_GET['status'])) {
    $pr_id = (int)$_GET['pr_id'];
    $status = mysqli_real_escape_string($conn, $_GET['status']);

    // Update the process status
    $update_query = "UPDATE purchase_requests SET pr_process_status = '$status' WHERE pr_id = $pr_id";
    if (mysqli_query($conn, $update_query)) {
        echo json_encode(['success' => true, 'message' => 'Process status updated successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update process status.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
}
