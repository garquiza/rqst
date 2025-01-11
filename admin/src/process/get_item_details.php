<?php
// Include your database connection file
require_once '../config/pdo.php';

// Get the item_no from the request
$item_no = isset($_GET['item_no']) ? $_GET['item_no'] : '';

// Fetch the item details from the database based on item_no
$sql = "SELECT item_id, item_no, item_name, unit_of_measurement FROM items WHERE item_no = :item_no";

$stmt = $pdo->prepare($sql);
$stmt->bindParam(':item_no', $item_no, PDO::PARAM_STR);
$stmt->execute();

// Fetch the item details
$itemDetails = $stmt->fetch(PDO::FETCH_ASSOC);

// Return the data as a JSON response
if ($itemDetails) {
    echo json_encode(['success' => true, 'item_id' => $itemDetails['item_id'], 'item_no' => $itemDetails['item_no'], 'item_name' => $itemDetails['item_name'], 'unit_of_measurement' => $itemDetails['unit_of_measurement']]);
} else {
    echo json_encode(['success' => false, 'message' => 'Item not found']);
}
