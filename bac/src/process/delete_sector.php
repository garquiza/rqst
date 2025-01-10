<?php
require_once '../config/pdo.php';

$data = json_decode(file_get_contents("php://input"), true);
$sectorId = $data['id'];

if (!isset($sectorId)) {
    echo json_encode(['success' => false, 'message' => 'Sector ID is required.']);
    exit();
}

$query = "DELETE FROM sector WHERE id = :id";
$stmt = $pdo->prepare($query);
$stmt->bindParam(':id', $sectorId, PDO::PARAM_INT);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to delete sector.']);
}
