<?php
require_once '../config/pdo.php';

if (isset($_POST['category_id'], $_POST['category_name'], $_POST['description'])) {
    // Retrieve and sanitize input
    $categoryId = $_POST['category_id'];
    $categoryName = $_POST['category_name'];
    $description = $_POST['description'];

    // Log the incoming POST data for debugging purposes
    error_log('Received POST Data: ' . print_r($_POST, true));  // Log all POST data

    // SQL query to update category details
    $sql = "UPDATE categories SET category_name = :category_name, description = :description WHERE category_id = :category_id";

    try {
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute([
            ':category_name' => $categoryName,
            ':description' => $description,
            ':category_id' => $categoryId
        ]);

        if ($result) {
            echo json_encode(['status' => 'success', 'message' => 'Category updated successfully']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to update category']);
        }
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request: Missing required fields']);
}
