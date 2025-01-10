<?php
// Include database connection
require_once '../config/pdo.php';

// Get the JSON data from the request
$data = json_decode(file_get_contents('php://input'), true);

// Validate the received data
if (isset($data['name']) && !empty($data['name'])) {
    $name = $data['name'];

    // Prepare the SQL query to insert the sector
    $query = "INSERT INTO sector (name) VALUES (:name)";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':name', $name);

    try {
        // Execute the query
        $stmt->execute();

        // Recalculate and distribute the budget among all sectors
        // Fetch total budget
        $query = "SELECT amount FROM budget_amount ORDER BY created_at DESC LIMIT 1";
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        $totalBudget = $stmt->fetch(PDO::FETCH_ASSOC)['amount'];

        // Fetch the total number of sectors
        $query = "SELECT COUNT(*) FROM sector";
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        $sectorCount = $stmt->fetchColumn();

        // Calculate the budget per sector
        $budgetPerSector = $totalBudget / $sectorCount;

        // Update all sectors with the new budget allocation
        $query = "UPDATE sector SET budget = :budget";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':budget', $budgetPerSector);
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
