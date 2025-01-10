<?php
// Start session
session_start();

// Include database connection
require_once '../config/database.php';

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the form data
    $philgeps_ref_no = $_POST['philgeps_ref'] ?? '';
    $project_location = $_POST['project_location'] ?? '';
    $supplier = $_POST['supplier'] ?? '';
    $mode_of_procurement = $_POST['mode_of_procurement'] ?? '';
    $address = $_POST['address'] ?? '';
    $city = $_POST['city'] ?? '';
    $telephone_no = $_POST['telephone'] ?? '';
    $tin = $_POST['tin'] ?? '';
    $place_of_delivery = $_POST['place_of_delivery'] ?? '';
    $delivery_terms = $_POST['delivery_terms'] ?? '';
    $date_of_delivery = $_POST['date_of_delivery'] ?? '';
    $payment_terms = $_POST['payment_terms'] ?? '';
    $noa_id = $_POST['project_title'] ?? ''; // This should be the selected project ID

    // Fetch the project title for the selected noa_id
    $query = "SELECT project_title FROM notice_of_award WHERE noa_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $noa_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $project_name = '';

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $project_name = $row['project_title'];
    }

    // Insert the purchase order into the database
    $insert_query = "INSERT INTO purchase_orders (philgeps_ref_no, project_location, supplier, mode_of_procurement, address, city, telephone_no, tin, place_of_delivery, delivery_terms, date_of_delivery, payment_terms, project_name, noa_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $insert_stmt = $conn->prepare($insert_query);
    $insert_stmt->bind_param("sssssssssssssi", $philgeps_ref_no, $project_location, $supplier, $mode_of_procurement, $address, $city, $telephone_no, $tin, $place_of_delivery, $delivery_terms, $date_of_delivery, $payment_terms, $project_name, $noa_id);

    if ($insert_stmt->execute()) {
        // Success
        $response = [
            'status' => 'success',
            'message' => 'Purchase Order has been submitted.',
            'noa_id' => $noa_id, // Include the project ID
            'project_title' => $project_name // Include the project title
        ];
    } else {
        // Error during insertion
        $response = [
            'status' => 'error',
            'message' => 'Failed to submit Purchase Order. Please try again.'
        ];
    }

    // Close the statement
    $insert_stmt->close();
    $stmt->close();
    $conn->close();

    // Return response as JSON
    header('Content-Type: application/json');
    echo json_encode($response);
}
