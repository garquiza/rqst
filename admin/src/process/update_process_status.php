<?php
session_start();
require_once '../config/database.php'; // Adjust the path as necessary

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

// Get the JSON payload from the request
$data = json_decode(file_get_contents("php://input"), true);

if (isset($data['pr_id']) && isset($data['status'])) {
    $pr_id = (int)$data['pr_id'];
    $status = mysqli_real_escape_string($conn, $data['status']);
    $approver_id = $_SESSION['user_id'];  // Fetch approver ID from the session

    // Check if a year filter is provided
    $year_filter = isset($data['year']) ? (int)$data['year'] : null;

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

    // Prepare the SQL query with placeholders to prevent SQL injection

    $update_query = "UPDATE purchase_requests SET pr_process_status = ?, status = ?, approver = ? WHERE pr_id = ?";

    // If year filter is provided, add it to the WHERE clause
    if ($year_filter) {
        $update_query .= " AND YEAR(request_date) = ?";

    }

    $stmt = $conn->prepare($update_query);

    if ($stmt) {
        // Bind the parameters
        if ($year_filter) {
            $stmt->bind_param('sssii', $status, $status, $approver_name, $pr_id, $year_filter);
        } else {
            $stmt->bind_param('sssi', $status, $status, $approver_name, $pr_id);
        }

        // Execute the query
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => "PR status successfully updated to $status."]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update process status, status, and approver.']);
        }

        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to prepare the query.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
}