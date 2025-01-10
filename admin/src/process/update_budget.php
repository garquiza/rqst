<?php
// update_budget.php

// Include database connection
require_once '../config/pdo.php';

// Get the posted data (budget)
$data = json_decode(file_get_contents('php://input'), true);

// Check if the 'budget' is present
if (isset($data['budget'])) {
    $newBudget = $data['budget'];

    // Prepare the query to update the first row of the budget_amount table (instead of inserting a new one)
    $query = "UPDATE budget_amount SET amount = :amount, created_at = NOW() WHERE id = (SELECT MIN(id) FROM budget_amount)";
    $stmt = $pdo->prepare($query);

    // Bind the new budget amount
    $stmt->bindParam(':amount', $newBudget, PDO::PARAM_STR);

    if ($stmt->execute()) {
        // Fetch the total number of sectors
        $query = "SELECT COUNT(*) FROM sector";
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        $sectorCount = $stmt->fetchColumn();

        // Calculate the budget per sector
        $budgetPerSector = $newBudget / $sectorCount;

        // Update all sectors with the new budget allocation
        $query = "UPDATE sector SET budget = :budget";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':budget', $budgetPerSector);
        $stmt->execute();

        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update the budget.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'No budget value provided.']);
}
