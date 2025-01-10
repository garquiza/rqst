<?php
require_once '../config/pdo.php';

$response = ['status' => 'error', 'message' => 'Failed to add item'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $unit = trim($_POST['unit'] ?? '');
    $item_name = trim($_POST['item_name'] ?? '');
    $item_description = trim($_POST['item_description'] ?? '');
    $unit_cost = isset($_POST['unit_cost']) ? floatval($_POST['unit_cost']) : 0;

    if (empty($unit) || empty($item_name) || empty($item_description) || $unit_cost <= 0) {
        $response['message'] = 'Invalid input data';
    } else {
        try {
            // Generate unique item_no
            $item_no = 'IM-' . date('Y-m-d') . '-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);

            $sql = "INSERT INTO inventory (item_no, unit, item_name, item_description, unit_cost) 
                    VALUES (:item_no, :unit, :item_name, :item_description, :unit_cost)";
            $stmt = $pdo->prepare($sql);

            if ($stmt->execute([
                ':item_no' => $item_no,
                ':unit' => $unit,
                ':item_name' => $item_name,
                ':item_description' => $item_description,
                ':unit_cost' => $unit_cost
            ])) {
                $response = ['status' => 'success', 'message' => 'Item added successfully', 'item_no' => $item_no];
            } else {
                $response['message'] = 'Database query failed';
            }
        } catch (PDOException $e) {
            $response['message'] = 'Database error: ' . $e->getMessage();
        }
    }
}

header('Content-Type: application/json');
echo json_encode($response);
