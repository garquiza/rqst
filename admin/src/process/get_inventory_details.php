<?php
// Include database connection
require_once '../config/pdo.php';

// Get the inventory ID from the query parameter
if (isset($_GET['id'])) {
    $inventoryId = $_GET['id'];

    // Fetch item details from the database
    $sql = "SELECT * FROM inventory WHERE inventory_id = :inventory_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['inventory_id' => $inventoryId]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($item) {
        echo json_encode(['status' => 'success', 'item' => $item]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Item not found']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
}
