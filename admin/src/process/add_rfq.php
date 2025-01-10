<?php
// Include database connection
include '../config/database.php';  // Update with your actual DB connection file

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get RFQ data from the form
    $projectTitle = $_POST['project_title'];
    $prRequestNumber = $_POST['pr_request_number'];
    $endUser = $_POST['end_user'];
    $dateCreated = $_POST['date_created'];
    $deadlineSubmission = $_POST['deadline_submission'];
    $approvedBudget = $_POST['approved_budget'];
    $procurementMode = $_POST['procurement_mode'];

    // Get the RFQ items (dynamically added rows from JavaScript)
    $rfqItems = json_decode($_POST['rfq_items'], true);  // Decode the JSON string into an array

    // Begin transaction to handle multiple inserts
    $conn->begin_transaction();

    try {
        // Insert data into the rfq table
        $insertRfqQuery = "INSERT INTO rfq (project_title, pr_request_number, end_user, date_created, deadline_submission, approved_budget, procurement_mode)
                           VALUES (?, ?, ?, ?, ?, ?, ?)";

        if ($stmt = $conn->prepare($insertRfqQuery)) {
            $stmt->bind_param("sssssss", $projectTitle, $prRequestNumber, $endUser, $dateCreated, $deadlineSubmission, $approvedBudget, $procurementMode);

            if ($stmt->execute()) {
                // Get the last inserted rfq_id
                $rfqId = $stmt->insert_id;

                // Insert RFQ items into the rfq_items table
                $insertRfqItemsQuery = "INSERT INTO rfq_items (rfq_id, quantity, unit, general_name, tech_specification, unit_cost, bidder_offer_specification, quoted_unit_price)
                                        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

                // Prepare the statement for inserting RFQ items
                if ($stmt = $conn->prepare($insertRfqItemsQuery)) {
                    // Loop through the items and insert each one
                    foreach ($rfqItems as $item) {
                        $stmt->bind_param("iisssdss", $rfqId, $item['quantity'], $item['unit'], $item['general_name'], $item['tech_spec'], $item['unit_cost'], $item['bidder_offer_spec'], $item['quoted_unit_price']);
                        $stmt->execute();
                    }
                } else {
                    throw new Exception('Failed to prepare statement for RFQ items.');
                }

                // Commit transaction if everything is successful
                $conn->commit();

                // Success message with rfq_id
                echo json_encode([
                    'status' => 'success',
                    'message' => 'RFQ and items have been successfully saved!',
                    'data' => [
                        'rfq_id' => $rfqId  // Include rfq_id in the response
                    ]
                ]);
            } else {
                throw new Exception('Failed to insert RFQ.');
            }
        } else {
            throw new Exception('Failed to prepare statement for RFQ insertion.');
        }
    } catch (Exception $e) {
        // Rollback transaction if something goes wrong
        $conn->rollback();

        // Error response
        echo json_encode([
            'status' => 'error',
            'message' => $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid request method.'
    ]);
}

// Close database connection
$conn->close();
