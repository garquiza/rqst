<?php
// Include your database connection file
require_once '../config/pdo.php';

// Get the item_no from the POST request
$item_no = isset($_POST['item_no']) ? $_POST['item_no'] : '';

// Validate the input
if (empty($item_no)) {
    echo json_encode(['success' => false, 'message' => 'Item number is required']);
    exit;
}

// Prepare the DELETE SQL query
$sql = "DELETE FROM items WHERE item_no = :item_no";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':item_no', $item_no, PDO::PARAM_STR);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Item successfully deleted']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to delete item']);
}
