<?php
require_once '../admin/src/config/pdo.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['id']) && isset($data['status'])) {
    $userId = $data['id'];
    $status = $data['status'];

    try {
        $query = "UPDATE end_users SET status = :status WHERE id = :id";
        $stmt = $pdo->prepare($query);
        $stmt->execute([':status' => $status, ':id' => $userId]);

        echo json_encode(['status' => 'success', 'message' => 'User status updated successfully.']);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid input.']);
}
