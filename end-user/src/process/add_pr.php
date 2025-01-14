<?php
session_start();

// Redirect to login page if the user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch user details from session
$user_name = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'User';

// Include database connection
include('../config/database.php');

// Process the form when submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Capture form data
    $ppmp_id = isset($_POST['ppmp_id']) ? $_POST['ppmp_id'] : '';
    $department = isset($_POST['department']) ? $_POST['department'] : '';
    $section = isset($_POST['section']) ? $_POST['section'] : '';
    $general_item = isset($_POST['general_item']) ? $_POST['general_item'] : '';
    $quantity = isset($_POST['quantity']) ? $_POST['quantity'] : 0;
    $unit_cost = isset($_POST['unit_cost']) ? $_POST['unit_cost'] : 0;
    $purpose = isset($_POST['purpose']) ? $_POST['purpose'] : '';

    // Calculate the total cost
    $total_cost = $quantity * $unit_cost;

    // Prepare the SQL query to insert the data
    $query = "INSERT INTO purchase_request_items (pr_id, department, section, general_item, quantity, unit_cost, total_cost, purpose)
              VALUES ('$ppmp_id', '$department', '$section', '$general_item', '$quantity', '$unit_cost', '$total_cost', '$purpose')";

    // Execute the query
    if (mysqli_query($conn, $query)) {
        // On success, return a success response
        echo json_encode(['success' => true, 'message' => 'Purchase Request Created Successfully!']);
    } else {
        // On failure, return an error response
        echo json_encode(['success' => false, 'message' => 'Failed to create Purchase Request!']);
    }
}
