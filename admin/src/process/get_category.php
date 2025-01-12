<?php
require_once '../config/pdo.php'; // Update the path to match your project structure

header('Content-Type: application/json');

if (isset($_GET['id'])) {
    $categoryId = $_GET['id'];

    try {
        // Query to fetch the category based on category_id
        $sql = "SELECT * FROM categories WHERE category_id = :category_id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':category_id', $categoryId, PDO::PARAM_INT);
        $stmt->execute();

        $category = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($category) {
            echo json_encode(['status' => 'success', 'category' => $category]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Category not found']);
        }
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
}
