<?php
// Start session
session_start();

// Include database connection
require_once '../config/pdo.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'User not logged in']);
    exit();
}

// Check if the POST request contains the necessary data
$data = json_decode(file_get_contents('php://input'), true);
$sectorId = $data['sector_id'] ?? null;
$sectorBudget = $data['budget'] ?? null;

if ($sectorId === null || $sectorBudget === null) {
    echo json_encode(['success' => false, 'error' => 'Invalid input']);
    exit();
}

try {
    // Fetch the current total budget
    $query = "SELECT amount FROM budget_amount WHERE id = 1";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $totalBudget = $stmt->fetch(PDO::FETCH_ASSOC)['amount'] ?? 0;

    // Fetch the current budget for the sector
    $query = "SELECT budget FROM sector WHERE id = :sector_id";
    $stmt = $pdo->prepare($query);
    $stmt->execute([':sector_id' => $sectorId]);
    $currentSectorBudget = $stmt->fetch(PDO::FETCH_ASSOC)['budget'] ?? 0;

    // Calculate the budget difference
    $budgetDifference = $sectorBudget - $currentSectorBudget;
    $newTotalBudget = $totalBudget - $budgetDifference;

    // Ensure the new total budget is not negative
    if ($newTotalBudget < 0) {
        echo json_encode(['success' => false, 'error' => 'Insufficient budget']);
        exit();
    }

    // Update the sector's budget
    $query = "UPDATE sector SET budget = :budget WHERE id = :sector_id";
    $stmt = $pdo->prepare($query);
    $stmt->execute([
        ':budget' => $sectorBudget,
        ':sector_id' => $sectorId,
    ]);

    // Update the total budget
    $query = "UPDATE budget_amount SET amount = :amount WHERE id = 1";
    $stmt = $pdo->prepare($query);
    $stmt->execute([':amount' => $newTotalBudget]);

    // Respond with success and the new total budget
    echo json_encode(['success' => true, 'new_total_budget' => $newTotalBudget]);
} catch (PDOException $e) {
    // Log the error and respond with failure
    error_log($e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Database error']);
}
