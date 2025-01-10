<?php
require_once '../config/pdo.php';

if (isset($_POST['inventory_id'], $_POST['item_name'], $_POST['item_description'], $_POST['unit_cost'])) {
    $inventoryId = $_POST['inventory_id'];
    $itemName = $_POST['item_name'];
    $itemDescription = $_POST['item_description'];
    $unitCost = $_POST['unit_cost'];

    $sql = "UPDATE inventory SET item_name = :item_name, item_description = :item_description, unit_cost = :unit_cost WHERE inventory_id = :inventory_id";

    try {
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute([
            'item_name' => $itemName,
            'item_description' => $itemDescription,
            'unit_cost' => $unitCost,
            'inventory_id' => $inventoryId
        ]);

        if ($result) {
            echo json_encode(['status' => 'success', 'message' => 'Item updated successfully']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to update item']);
        }
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
}
