<?php
// delete_sector.php

// Include database connection
require_once '../config/pdo.php';

// Get the posted data (sector ID)
$data = json_decode(file_get_contents('php://input'), true);

// Check if the 'id' is present
if (isset($data['id'])) {
    $sectorId = $data['id'];

    try {
        // Start transaction
        $pdo->beginTransaction();

        // First, get the total budget amount
        $query = "SELECT amount FROM budget_amount ORDER BY created_at DESC LIMIT 1";
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        $totalBudget = $stmt->fetch(PDO::FETCH_ASSOC)['amount'];

        // Get the total number of sectors before deletion
        $query = "SELECT COUNT(*) AS sector_count FROM sector";
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        $sectorCount = $stmt->fetch(PDO::FETCH_ASSOC)['sector_count'];

        // Check if there is more than one sector (to prevent division by zero)
        if ($sectorCount > 1) {
            // Delete the sector
            $deleteQuery = "DELETE FROM sector WHERE id = :id";
            $deleteStmt = $pdo->prepare($deleteQuery);
            $deleteStmt->bindParam(':id', $sectorId, PDO::PARAM_INT);
            $deleteStmt->execute();

            // Recalculate the budget per remaining sector
            $remainingSectors = $sectorCount - 1;
            $budgetPerSector = $totalBudget / $remainingSectors;

            // Update the remaining sectors with the new budget allocation
            $updateQuery = "UPDATE sector SET budget = :budget";
            $updateStmt = $pdo->prepare($updateQuery);
            $updateStmt->bindParam(':budget', $budgetPerSector, PDO::PARAM_STR);
            $updateStmt->execute();

            // Commit the transaction
            $pdo->commit();

            echo json_encode(['success' => true]);
        } else {
            // If there is only one sector, return an error
            echo json_encode(['success' => false, 'message' => 'Cannot delete the last sector.']);
        }
    } catch (PDOException $e) {
        // If there is an error, rollback the transaction
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
} else {
    // If data is invalid, send an error response
    echo json_encode(['success' => false, 'message' => 'Invalid sector ID.']);
}
