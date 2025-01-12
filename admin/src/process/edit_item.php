<?php
// Include your database connection file
require_once '../config/pdo.php';

// Get the updated item data from the form
$itemId = isset($_POST['item_id']) ? $_POST['item_id'] : '';
$itemName = isset($_POST['item_name']) ? $_POST['item_name'] : '';
$unitOfMeasurement = isset($_POST['unit_of_measurement']) ? $_POST['unit_of_measurement'] : '';

// Validate the data (you can add more validation if needed)
if (empty($itemId) || empty($itemName) || empty($unitOfMeasurement)) {
    echo json_encode(['success' => false, 'message' => 'All fields are required']);
    exit;
}

// Update the item in the database
$sql = "UPDATE items SET item_name = :item_name, unit_of_measurement = :unit_of_measurement WHERE item_id = :item_id";

$stmt = $pdo->prepare($sql);
$stmt->bindParam(':item_name', $itemName, PDO::PARAM_STR);
$stmt->bindParam(':unit_of_measurement', $unitOfMeasurement, PDO::PARAM_STR);
$stmt->bindParam(':item_id', $itemId, PDO::PARAM_INT);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update item']);
}
