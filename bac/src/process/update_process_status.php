<?php
session_start();
require_once '../config/database.php'; // Adjust the path as necessary

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

// Get pr_id and status from the GET parameters
if (isset($_GET['pr_id']) && isset($_GET['status'])) {
    $pr_id = (int)$_GET['pr_id'];
    $status = mysqli_real_escape_string($conn, $_GET['status']); // The status (Approved or Rejected)

    // Update the purchase request's process status and status in the database
    $update_query = "UPDATE purchase_requests SET pr_process_status = ?, status = ? WHERE pr_id = ?";
    $stmt = $conn->prepare($update_query);

    if ($stmt) {
        // Bind the parameters to prevent SQL injection
        $stmt->bind_param('ssi', $status, $status, $pr_id);

        // Execute the query
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => "PR status successfully updated to $status."]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update PR status.']);
        }

        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to prepare the query.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
}
?>