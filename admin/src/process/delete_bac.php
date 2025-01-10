<?php
require_once '../config/pdo.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the input data
    $data = json_decode(file_get_contents('php://input'), true);
    $userId = $data['id'] ?? null;

    if ($userId) {
        $sql = "DELETE FROM bac_users WHERE id = :id";
        $stmt = $pdo->prepare($sql);

        if ($stmt->execute(['id' => $userId])) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete the user.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid user ID.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
