<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

require_once '../config/pdo.php';

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $inventoryId = $_GET['id'];

    try {
        $sql = "DELETE FROM inventory WHERE inventory_id = :inventory_id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':inventory_id', $inventoryId, PDO::PARAM_INT);

        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            echo json_encode(['status' => 'success', 'message' => 'Item deleted successfully.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Item not found.']);
        }
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Error deleting item: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid ID.']);
}
