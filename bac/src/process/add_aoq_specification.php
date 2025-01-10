<?php
// Include database connection
require_once '../config/database.php'; // Ensure this path is correct

// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['message' => 'Unauthorized']);
    exit();
}

// Get the AOQ ID from the POST request
$aoq_id = $_POST['aoq'];

// Prepare to insert specifications
$specifications = $_POST['specification'];
$quantities = $_POST['quantity'];
$units = $_POST['unit'];

// Prepare to insert bidders
$company_names = $_POST['company_name'];
$bidders_specifications = $_POST['bidders_specification'];
$bidders_quantities = $_POST['bidders_quantity'];
$unit_prices = $_POST['unit_price'];
$total_prices = $_POST['total_price'];

// Insert specifications into tup_specifications table
foreach ($specifications as $index => $specification) {
    $quantity = $quantities[$index];
    $unit = $units[$index];

    $stmt = $conn->prepare("INSERT INTO tup_specifications (aoq_id, tup_specification, quantity, unit) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isds", $aoq_id, $specification, $quantity, $unit);

    if (!$stmt->execute()) {
        http_response_code(500);
        echo json_encode(['message' => 'Error inserting specification: ' . $stmt->error]);
        exit();
    }
}

// Insert bidders into company_details table
foreach ($company_names as $index => $company_name) {
    $bidders_specification = $bidders_specifications[$index];
    $bidders_quantity = $bidders_quantities[$index];
    $unit_price = $unit_prices[$index];
    $total_price = $total_prices[$index];

    $stmt = $conn->prepare("INSERT INTO company_details (aoq_id, company_name, bidders_specification, unit_price, total_price) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issdd", $aoq_id, $company_name, $bidders_specification, $unit_price, $total_price);

    if (!$stmt->execute()) {
        http_response_code(500);
        echo json_encode(['message' => 'Error inserting bidder: ' . $stmt->error]);
        exit();
    }
}

// If everything is successful
echo json_encode(['message' => 'AOQ specifications added successfully.']);
