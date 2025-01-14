<?php
// Include database connection
include '../config/database.php';  // Update with your actual DB connection file

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get RFQ data from the form
    $projectTitle = isset($_POST['project_title']) ? $_POST['project_title'] : '';
    $prRequestNumber = isset($_POST['pr_request_number']) ? $_POST['pr_request_number'] : '';
    $endUser = isset($_POST['end_user']) ? $_POST['end_user'] : '';
    $dateCreated = isset($_POST['date_created']) ? $_POST['date_created'] : '';
    $deadlineSubmission = isset($_POST['deadline_submission']) ? $_POST['deadline_submission'] : '';
    $approvedBudget = isset($_POST['approved_budget']) ? $_POST['approved_budget'] : '';
    $procurementMode = isset($_POST['procurement_mode']) ? $_POST['procurement_mode'] : '';

    // Check if rfq_items is set and is a valid JSON string
    if (isset($_POST['rfq_items'])) {
        $rfqItems = json_decode($_POST['rfq_items'], true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Invalid RFQ items format.'
            ]);
            exit;
        }
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'RFQ items not provided.'
        ]);
        exit;
    }

    // Begin transaction to handle multiple inserts
    $conn->begin_transaction();

    try {
        // Insert data into the rfq table
        $insertRfqQuery = "INSERT INTO rfq (project_title, pr_request_number, end_user, date_created, deadline_submission, approved_budget, procurement_mode)
                           VALUES (?, ?, ?, ?, ?, ?, ?)";

        if ($stmt = $conn->prepare($insertRfqQuery)) {
            $stmt->bind_param("sssssds", $projectTitle, $prRequestNumber, $endUser, $dateCreated, $deadlineSubmission, $approvedBudget, $procurementMode);

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
                        $quantity = isset($item['quantity']) ? $item['quantity'] : 0;
                        $unit = isset($item['unit']) ? $item['unit'] : '';
                        $generalName = isset($item['general_name']) ? $item['general_name'] : '';
                        $techSpec = isset($item['tech_spec']) ? $item['tech_spec'] : '';
                        $unitCost = isset($item['unit_cost']) ? $item['unit_cost'] : 0.0;
                        $bidderOfferSpec = isset($item['bidder_offer_spec']) ? $item['bidder_offer_spec'] : '';
                        $quotedUnitPrice = isset($item['quoted_unit_price']) ? $item['quoted_unit_price'] : 0.0;

                        $stmt->bind_param("iisssdss", $rfqId, $quantity, $unit, $generalName, $techSpec, $unitCost, $bidderOfferSpec, $quotedUnitPrice);
                        if (!$stmt->execute()) {
                            throw new Exception("Failed to insert RFQ item: " . $conn->error);
                        }
                    }
                } else {
                    throw new Exception('Failed to prepare statement for RFQ items: ' . $conn->error);
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
                throw new Exception('Failed to insert RFQ: ' . $conn->error);
            }
        } else {
            throw new Exception('Failed to prepare statement for RFQ insertion: ' . $conn->error);
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
