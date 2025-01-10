<?php
// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'User  not logged in.']);
    exit();
}

// Include database connection (adjust the path if necessary)
require_once '../config/database.php';

// Get the JSON data from the request
$data = json_decode(file_get_contents("php://input"), true);

// Validate the input data
if (isset($data['fund_source'], $data['total_abc'], $data['amount'], $data['savings'])) {
    $fund_source = $data['fund_source'];
    $total_abc = $data['total_abc'];
    $amount = $data['amount'];
    $savings = $data['savings'];

    // Prepare the SQL statement
    $stmt = $conn->prepare("INSERT INTO fund_sources (fund_source, total_abc, amount, savings) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sddd", $fund_source, $total_abc, $amount, $savings);

    // Execute the statement
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Fund source added successfully.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to add fund source.']);
    }

    // Close the statement
    $stmt->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid input data.']);
}

// Close the database connection
$conn->close();
