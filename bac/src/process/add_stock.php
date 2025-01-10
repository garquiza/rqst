<?php
// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Include database connection
require_once '../config/database.php';

// Check if the form data is received
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve the purchase order ID from the hidden input
    $noa_id = $_POST['noa_id'];

    // Prepare an array to hold the stock items
    $items = [];
    foreach ($_POST['unit'] as $index => $unit) {
        $quantity = $_POST['quantity'][$index];
        $description = $_POST['description'][$index];
        $unit_cost = $_POST['unit_cost'][$index];
        $amount = $_POST['amount'][$index];

        // Only add items that have a unit and quantity
        if (!empty($unit) && !empty($quantity)) {
            $items[] = [
                'unit' => $unit,
                'quantity' => $quantity,
                'description' => $description,
                'unit_cost' => $unit_cost,
                'amount' => $amount
            ];
        }
    }

    // Check if there are items to insert
    if (!empty($items)) {
        // Start a transaction
        $conn->begin_transaction();

        try {
            // Insert into purchase_orders if it doesn't exist
            $query = "INSERT INTO purchase_orders (noa_id) VALUES (?)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $noa_id);
            $stmt->execute();
            $purchase_order_id = $stmt->insert_id; // Get the last inserted ID

            // Prepare the insert statement for purchase_order_items
            $query = "INSERT INTO purchase_order_items (purchase_order_id, unit, quantity, description, unit_cost, amount) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($query);

            // Insert each item
            foreach ($items as $item) {
                $stmt->bind_param("issddd", $purchase_order_id, $item['unit'], $item['quantity'], $item['description'], $item['unit_cost'], $item['amount']);
                $stmt->execute();
            }

            // Commit the transaction
            $conn->commit();

            // Return success response
            echo json_encode(['status' => 'success']);
        } catch (Exception $e) {
            // Rollback the transaction in case of error
            $conn->rollback();
            echo json_encode(['status' => 'error', 'message' => 'Failed to add stock items: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No valid stock items to add.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}
