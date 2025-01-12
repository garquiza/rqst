<?php
session_start();
include('../config/database.php');

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

// Check if the required POST data is set
if (
    isset(
        $_POST['ppmp_id'],
        $_POST['year'],
        $_POST['code'],
        $_POST['general_description'],
        $_POST['quantity_size'],
        $_POST['estimated_budget'],
        $_POST['schedule'],
        $_POST['category'],
        $_POST['unit_measurement'],
        $_POST['unit_cost'],
        $_POST['mode_of_procurement']
    )
) {
    $ppmp_id = intval($_POST['ppmp_id']);
    $year = $_POST['year'];
    $code = $_POST['code'];
    $general_description = $_POST['general_description'];
    $quantity_size = $_POST['quantity_size'];
    $estimated_budget = $_POST['estimated_budget'];
    $schedule = json_encode($_POST['schedule']);
    $categories = $_POST['category'];
    $items = $_POST['general_description'];
    $unit_measurements = $_POST['unit_measurement'];
    $quantities = $_POST['quantity_size'];
    $unit_costs = $_POST['unit_cost'];
    $mode_of_procurement = $_POST['mode_of_procurement'];

    // Begin a transaction
    $conn->begin_transaction();

    try {
        // Update PPMP Form
        $updateQuery = "UPDATE ppmp_form 
                        SET year = ?, code = ?, general_description = ?, quantity_size = ?, 
                            estimated_budget = ?, schedule = ?, mode_of_procurement = ? 
                        WHERE ppmp_id = ?";
        $updateStmt = $conn->prepare($updateQuery);
        $updateStmt->bind_param(
            "sssssssi",
            $year,
            $code,
            $general_description,
            $quantity_size,
            $estimated_budget,
            $schedule,
            $mode_of_procurement,
            $ppmp_id
        );
        $updateStmt->execute();

        // Update PPMP List status
        $statusUpdateQuery = "UPDATE ppmp_list SET status = 'pending' WHERE ppmp_id = ?";
        $statusUpdateStmt = $conn->prepare($statusUpdateQuery);
        $statusUpdateStmt->bind_param("i", $ppmp_id);
        $statusUpdateStmt->execute();

        // Delete existing items for this PPMP
        $deleteItemsQuery = "DELETE FROM ppmp_items WHERE ppmp_id = ?";
        $deleteItemsStmt = $conn->prepare($deleteItemsQuery);
        $deleteItemsStmt->bind_param("i", $ppmp_id);
        $deleteItemsStmt->execute();

        // Insert new items into PPMP Items table
        $insertItemQuery = "INSERT INTO ppmp_items (ppmp_id, category, item_description, unit_measurement, quantity, unit_cost) 
                            VALUES (?, ?, ?, ?, ?, ?)";
        $insertItemStmt = $conn->prepare($insertItemQuery);

        foreach ($categories as $index => $category) {
            $item = $items[$index];
            $unit_measurement = $unit_measurements[$index];
            $quantity = $quantities[$index];
            $unit_cost = $unit_costs[$index];

            $insertItemStmt->bind_param("isssii", $ppmp_id, $category, $item, $unit_measurement, $quantity, $unit_cost);
            $insertItemStmt->execute();
        }

        // Commit transaction
        $conn->commit();

        echo json_encode(['status' => 'success', 'message' => 'PPMP form updated successfully']);
    } catch (Exception $e) {
        // Rollback transaction in case of error
        $conn->rollback();
        echo json_encode(['status' => 'error', 'message' => 'Error updating PPMP form: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
}
