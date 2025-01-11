<?php
require_once '../config/database.php';

// Get JSON input
$data = json_decode(file_get_contents('php://input'), true);

// Check if start_date and end_date are provided
if (empty($data['start_date']) || empty($data['end_date'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid data received']);
    exit();
}

$startDate = $data['start_date'];
$endDate = $data['end_date'];

// Check if a record with id = 1 exists
$query = "SELECT * FROM access_dates WHERE id = 1";
$result = $conn->query($query);

if ($result->num_rows > 0) {
    // If the record exists, update the existing record
    $query = "UPDATE access_dates SET start_date = ?, end_date = ? WHERE id = 1";
    $stmt = $conn->prepare($query);

    if (!$stmt) {
        echo json_encode(['success' => false, 'error' => 'Failed to prepare statement']);
        exit();
    }

    $stmt->bind_param('ss', $startDate, $endDate);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to execute update']);
    }

    $stmt->close();
} else {
    // If the record does not exist, insert a new one
    $query = "INSERT INTO access_dates (id, start_date, end_date) VALUES (1, ?, ?)";
    $stmt = $conn->prepare($query);

    if (!$stmt) {
        echo json_encode(['success' => false, 'error' => 'Failed to prepare insert statement']);
        exit();
    }

    $stmt->bind_param('ss', $startDate, $endDate);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'New record inserted']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to execute insert']);
    }

    $stmt->close();
}

$conn->close();
