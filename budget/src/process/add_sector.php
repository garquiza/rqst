<?php
// Include database connection
require_once '../config/pdo.php';

// Set response header for JSON
header('Content-Type: application/json');

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the JSON data
    $data = json_decode(file_get_contents('php://input'), true);

    // Validate input
    if (isset($data['name'], $data['budget']) && !empty(trim($data['name'])) && is_numeric($data['budget'])) {
        $name = trim($data['name']);
        $budget = floatval($data['budget']);

        try {
            // Prepare the SQL statement
            $stmt = $pdo->prepare("INSERT INTO sector (name, budget) VALUES (:name, :budget)");
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':budget', $budget);

            // Execute the query
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Sector added successfully.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to add sector.']);
            }
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid input data.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
