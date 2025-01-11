<?php
include('../config/database.php');

if (isset($_GET['category_id'])) {
    $categoryId = $_GET['category_id'];

    // Query to fetch items based on category
    $query = "SELECT * FROM items WHERE category_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $categoryId);
    $stmt->execute();
    $result = $stmt->get_result();
    $items = [];

    while ($row = $result->fetch_assoc()) {
        $items[] = $row;
    }

    if ($items) {
        echo json_encode(['status' => 'success', 'items' => $items]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No items found for this category.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Category ID is required.']);
}
