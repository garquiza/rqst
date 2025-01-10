<?php
require_once '../config/pdo.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    // Validate input
    if (isset($data['name'], $data['totalabc'], $data['amount']) && !empty(trim($data['name'])) && is_numeric($data['totalabc']) && is_numeric($data['amount'])) {
        $name = trim($data['name']);
        $totalabc = floatval($data['totalabc']);
        $amount = floatval($data['amount']);
        $savings = $totalabc - $amount; // Calculate savings

        try {
            $stmt = $pdo->prepare("INSERT INTO fund (name, totalabc, amount, savings) VALUES (:name, :totalabc, :amount, :savings)");
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':totalabc', $totalabc);
            $stmt->bindParam(':amount', $amount);
            $stmt->bindParam(':savings', $savings);

            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Fund source added successfully.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to add fund source.']);
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
