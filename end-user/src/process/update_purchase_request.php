<?php
session_start();
include('../config/database.php'); // Adjust the path as necessary

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the form data
    $pr_number = $_POST['pr_number'];
    $approver = $_POST['approver'];
    $status = 'Pending'; // Reset status to Pending
    $pr_process_status = $_POST['pr_process_status'];

    // Prepare the update query for purchase request
    $update_query = "UPDATE purchase_requests SET approver = ?, status = ?, pr_process_status = ? WHERE pr_number = ?";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bind_param("ssss", $approver, $status, $pr_process_status, $pr_number);

    if ($update_stmt->execute()) {
        // Process the updated unit cost values
        if (isset($_POST['unit_cost'])) {
            foreach ($_POST['unit_cost'] as $inventory_id => $unit_cost) {
                // Ensure the unit_cost is a valid number and non-negative
                if (is_numeric($unit_cost) && $unit_cost >= 0) {
                    // Prepare the query to update the unit cost
                    $update_cost_query = "UPDATE purchase_request_items SET unit_cost = ? WHERE inventory_id = ? AND pr_number = ?";
                    $update_cost_stmt = $conn->prepare($update_cost_query);
                    $update_cost_stmt->bind_param("dii", $unit_cost, $inventory_id, $pr_number);

                    if (!$update_cost_stmt->execute()) {
                        // If update failed, return an error
                        echo json_encode(['status' => 'error', 'message' => 'Error updating unit cost for inventory_id ' . $inventory_id . ': ' . $conn->error]);
                        exit; // Exit to avoid further execution if an error occurs
                    }

                    $update_cost_stmt->close();
                } else {
                    // If unit_cost is not valid, skip it or handle it as an error
                    echo json_encode(['status' => 'error', 'message' => 'Invalid unit cost value for inventory_id ' . $inventory_id]);
                    exit;
                }
            }
        }

        // Return success message if all updates were successful
        echo json_encode(['status' => 'success', 'message' => 'Purchase request and unit costs updated successfully.']);
    } else {
        // Error response if the purchase request update failed
        echo json_encode(['status' => 'error', 'message' => 'Error updating purchase request: ' . $conn->error]);
    }

    $update_stmt->close();
}
$conn->close();
?>
