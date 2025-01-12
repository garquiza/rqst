<?php
// Include your database connection file
require_once '../config/pdo.php';

// Get the category_id from the request
$category_id = isset($_GET['category_id']) ? intval($_GET['category_id']) : 0;

// Fetch the item details from the database based on category_id
$sql = "SELECT i.item_no, i.item_name, i.unit_of_measurement, c.category_name, i.category_id
        FROM items i
        JOIN categories c ON i.category_id = c.category_id
        WHERE c.category_id = :category_id"; // Use category_id here

$stmt = $pdo->prepare($sql);
$stmt->bindParam(':category_id', $category_id, PDO::PARAM_INT);
$stmt->execute();

// Fetch all item details for the given category
$itemDetails = $stmt->fetchAll(PDO::FETCH_ASSOC); // Changed to fetchAll to get all items

// Return the data as a JSON response
if ($itemDetails) {
    echo json_encode(['success' => true, 'items' => $itemDetails]);
} else {
    echo json_encode(['success' => false, 'message' => 'No items found for this category']);
}
