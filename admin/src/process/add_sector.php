<?php
// Include database connection
require_once '../config/pdo.php';

// Get the JSON data from the request
$data = json_decode(file_get_contents('php://input'), true);

// Validate the received data
if (isset($data['name']) && !empty($data['name'])) {
    $name = $data['name'];

    // Prepare the SQL query to insert the sector with budget set to 0
    $query = "INSERT INTO sector (name, budget) VALUES (:name, 0)";  // Set budget to 0
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':name', $name);

    try {
        // Execute the query
        $stmt->execute();

        // Send a success response
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        // If there is an error, send an error response
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
} else {
    // If data is invalid, send an error response
    echo json_encode(['success' => false, 'message' => 'No sector name provided.']);
}
