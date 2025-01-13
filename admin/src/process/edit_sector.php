<?php
// Include database connection
require_once '../config/pdo.php';

// Get the JSON data from the request
$data = json_decode(file_get_contents('php://input'), true);

// Validate the received data
if (isset($data['id'], $data['name'], $data['budget']) && !empty($data['name']) && is_numeric($data['budget'])) {
    $id = $data['id'];
    $name = $data['name'];
    $budget = $data['budget'];

    // Fetch the current budget allocation for the sector
    $query = "SELECT budget FROM sector WHERE id = :id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $currentSectorBudget = $stmt->fetch(PDO::FETCH_ASSOC)['budget'];

    // Calculate the difference in the budget
    $budgetDifference = $budget - $currentSectorBudget;

    // Update the sector's name and budget
    $query = "UPDATE sector SET name = :name, budget = :budget WHERE id = :id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':id', $id);
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':budget', $budget);

    try {
        // Execute the query to update the sector
        $stmt->execute();

        // Update the total budget by subtracting the difference from the total budget
        $query = "SELECT amount FROM budget_amount ORDER BY created_at DESC LIMIT 1";
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        $totalBudget = $stmt->fetch(PDO::FETCH_ASSOC)['amount'];

        // Subtract the budget difference from the total budget
        $newTotalBudget = $totalBudget - $budgetDifference;

        // Update the total budget in the database
        $query = "UPDATE budget_amount SET amount = :newTotalBudget WHERE created_at = (SELECT MAX(created_at) FROM budget_amount)";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':newTotalBudget', $newTotalBudget);
        $stmt->execute();

        // Send a success response
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        // If there is an error, send an error response
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
} else {
    // If data is invalid, send an error response
    echo json_encode(['success' => false, 'message' => 'Invalid sector data.']);
}
