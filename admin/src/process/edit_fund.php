<?php
require_once '../config/pdo.php';

$data = json_decode(file_get_contents('php://input'), true);

// Validate input
if (
    isset($data['id'], $data['name'], $data['totalabc'], $data['amount'], $data['savings'])
    && is_numeric($data['totalabc']) && $data['totalabc'] > 0
    && is_numeric($data['amount']) && $data['amount'] > 0
) {

    $id = $data['id'];
    $name = htmlspecialchars($data['name']);
    $totalabc = (float)$data['totalabc'];
    $amount = (float)$data['amount'];
    $savings = (float)$data['savings'];

    try {
        $query = "UPDATE fund SET name = :name, totalabc = :totalabc, amount = :amount, savings = :savings WHERE id = :id";
        $stmt = $pdo->prepare($query);
        $stmt->execute([
            ':name' => $name,
            ':totalabc' => $totalabc,
            ':amount' => $amount,
            ':savings' => $savings,
            ':id' => $id
        ]);

        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid input data.']);
}
