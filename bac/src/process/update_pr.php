<?php
// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

// Include database connection
require_once '../bac/src/config/database.php';

// Check if required parameters are set
if (isset($_POST['pr_id']) && isset($_POST['status'])) {
    $pr_id = intval($_POST['pr_id']);
    $status = $_POST['status'];
    $approver_id = $_SESSION['user_id'];  // The currently logged-in user's ID

    // Log incoming parameters
    error_log("Incoming Request - PR ID: $pr_id, Status: $status, Approver ID: $approver_id");

    // Fetch the approver's full name from the admin_users table
    $approver_query = "SELECT CONCAT(first_name, ' ', last_name) AS full_name FROM admin_users WHERE id = ?";
    $stmt_approver = $conn->prepare($approver_query);

    if (!$stmt_approver) {
        echo json_encode(['success' => false, 'message' => 'Database query preparation failed']);
        exit();
    }

    $stmt_approver->bind_param('i', $approver_id);
    $stmt_approver->execute();
    $approver_result = $stmt_approver->get_result();
    $approver_data = $approver_result->fetch_assoc();

    if (!$approver_data) {
        echo json_encode(['success' => false, 'message' => 'Approver not found']);
        exit();
    }

    $approver_name = $approver_data['full_name'];

    // Update query to change the status and approver
    $update_query = "UPDATE purchase_requests SET status = ?, approver = ? WHERE pr_id = ?";
    $stmt = $conn->prepare($update_query);

    if (!$stmt) {
        error_log("Update query preparation failed: " . $conn->error);
        echo json_encode(['success' => false, 'message' => 'Database query preparation failed']);
        exit();
    }

    $stmt->bind_param('ssi', $status, $approver_name, $pr_id);

    // Execute the update
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            // Log success
            error_log("PR ID $pr_id updated to '$status' by $approver_name");

            // Insert into history_logs
            $title = "Purchase Request Updated";
            $description = "Purchase Request ID $pr_id has been updated to status '$status' by $approver_name.";
            $log_query = "INSERT INTO history_logs (title, description) VALUES (?, ?)";
            $stmt_log = $conn->prepare($log_query);

            if (!$stmt_log) {
                error_log("History log query preparation failed: " . $conn->error);
                echo json_encode(['success' => false, 'message' => 'Failed to prepare history log query']);
                exit();
            }

            $stmt_log->bind_param('ss', $title, $description);
            $stmt_log->execute();

            echo json_encode(['success' => true, 'message' => 'Status updated successfully and logged']);
        } else {
            error_log("No rows updated for PR ID: $pr_id");
            echo json_encode(['success' => false, 'message' => 'No rows updated. Please check the PR ID']);
        }
    } else {
        error_log("Update query execution failed: " . $stmt->error);
        echo json_encode(['success' => false, 'message' => 'Failed to execute update query']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
}

?>