<?php
session_start();
require_once '../config/pdo.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = (int)$_POST['id']; // Make sure to include the user ID in the form
    $status = $_POST['status'];
    $permissions = isset($_POST['permissions']) ? implode(',', $_POST['permissions']) : '';

    $sql = "UPDATE bac_users SET status = :status, permission_access = :permissions WHERE id = :id";
    $stmt = $pdo->prepare($sql);

    if ($stmt->execute(['status' => $status, 'permissions' => $permissions, 'id' => $user_id])) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'There was an error updating the user.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
