<?php
session_start();

// Redirect to login page if the user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Include database connection
include('../config/database.php');

// Check if PR number is passed in the URL
if (isset($_GET['pr_number'])) {
    $pr_number = mysqli_real_escape_string($conn, $_GET['pr_number']);

    // Check if the PR exists in the purchase_requests table
    $check_query = "SELECT pr_id FROM purchase_requests WHERE pr_number = '$pr_number'";
    $check_result = mysqli_query($conn, $check_query);

    if (mysqli_num_rows($check_result) > 0) {
        // Fetch PR ID from the result
        $row = mysqli_fetch_assoc($check_result);
        $pr_id = $row['pr_id'];

        // Begin transaction
        mysqli_begin_transaction($conn);

        try {
            // Delete purchase request items related to this PR (cascade delete is already set in foreign key)
            $delete_items_query = "DELETE FROM purchase_request_items WHERE pr_id = $pr_id";
            if (!mysqli_query($conn, $delete_items_query)) {
                throw new Exception("Error deleting purchase request items.");
            }

            // Delete the purchase request itself
            $delete_pr_query = "DELETE FROM purchase_requests WHERE pr_number = '$pr_number'";
            if (!mysqli_query($conn, $delete_pr_query)) {
                throw new Exception("Error deleting purchase request.");
            }

            // Commit the transaction
            mysqli_commit($conn);

            // Send success response
            echo json_encode(['status' => 'success', 'message' => 'Purchase Request deleted successfully.']);
            exit();
        } catch (Exception $e) {
            // Rollback the transaction in case of error
            mysqli_rollback($conn);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
            exit();
        }
    } else {
        // If PR does not exist
        echo json_encode(['status' => 'error', 'message' => 'Purchase Request not found.']);
        exit();
    }
} else {
    // If PR number is not passed
    echo json_encode(['status' => 'error', 'message' => 'Invalid request.']);
    exit();
}
