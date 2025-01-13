<?php
// Start session
session_start();

// Include database connection
require_once '../config/pdo.php';

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve the JSON body
    $data = json_decode(file_get_contents('php://input'), true);
    $newBudget = $data['budget'] ?? null;

    if ($newBudget !== null && is_numeric($newBudget) && $newBudget >= 0) {
        try {
            // Update the budget amount in the database
            $query = "UPDATE budget_amount SET amount = :amount WHERE id = 1";
            $stmt = $pdo->prepare($query);
            $stmt->execute([':amount' => $newBudget]);

            // Respond with success
            echo json_encode(['success' => true]);
        } catch (PDOException $e) {
            // Log the error (optional) and respond with failure
            error_log($e->getMessage());
            echo json_encode(['success' => false, 'error' => 'Database error']);
        }
    } else {
        // Respond with validation error
        echo json_encode(['success' => false, 'error' => 'Invalid budget value']);
    }
} else {
    // Respond with method not allowed
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
}
