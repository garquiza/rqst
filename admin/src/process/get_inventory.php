<?php
require_once '../config/pdo.php';

if (isset($_GET['id'])) {
    $inventoryId = $_GET['id'];

    $sql = "SELECT * FROM inventory WHERE inventory_id = :inventory_id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':inventory_id', $inventoryId, PDO::PARAM_INT);
    $stmt->execute();
    $item = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($item) {
        echo json_encode(['status' => 'success', 'item' => $item]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Item not found']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
}
