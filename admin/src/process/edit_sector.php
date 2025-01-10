<?php
// Include database connection
require_once '../config/pdo.php';

// Get input data
$data = json_decode(file_get_contents("php://input"));

if (isset($data->id) && isset($data->name)) {
    $sectorId = $data->id;
    $sectorName = $data->name;

    // Prepare the update query
    $query = "UPDATE sector SET name = :name WHERE id = :id";
    $stmt = $pdo->prepare($query);

    // Bind parameters
    $stmt->bindParam(':name', $sectorName);
    $stmt->bindParam(':id', $sectorId);

    // Execute the query
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update sector']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid input data']);
}
