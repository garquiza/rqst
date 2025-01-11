<?php
require_once '../config/pdo.php'; // Include your PDO configuration

if (isset($_GET['id'])) {
    $categoryId = $_GET['id'];

    try {
        // Prepare the delete query
        $sql = "DELETE FROM categories WHERE category_id = :category_id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':category_id', $categoryId, PDO::PARAM_INT);
        $result = $stmt->execute();

        // Check if the deletion was successful
        if ($result) {
            echo json_encode(['status' => 'success', 'message' => 'Category deleted successfully.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to delete the category.']);
        }
    } catch (Exception $e) {
        // Handle any errors
        echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request.']);
}
