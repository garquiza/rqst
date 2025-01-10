<?php
// Start session
session_start();

// Include database connection
require_once '../config/database.php';

// Check if the request is a POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $aoq_id = $_POST['aoq_id'];
    $supplier_name = $_POST['supplier_name'];
    $description = $_POST['description'];
    $total_amount_figures = $_POST['total_amount_figures'];
    $total_amount_words = $_POST['total_amount_words'];
    $whereas_statements = $_POST['whereas'];
    $it_is_hereby_statements = $_POST['it_is_hereby'];

    // Prepare and bind for the resolution table
    $stmt = $conn->prepare("INSERT INTO resolution (aoq_id, supplier_name, description, total_amount_figures, total_amount_words) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issss", $aoq_id, $supplier_name, $description, $total_amount_figures, $total_amount_words);

    // Execute the statement
    if ($stmt->execute()) {
        $resolution_id = $stmt->insert_id; // Get the last inserted resolution ID

        // Prepare and bind for the whereas_statements table
        $whereas_stmt = $conn->prepare("INSERT INTO whereas_statements (resolution_id, statement) VALUES (?, ?)");
        $whereas_stmt->bind_param("is", $resolution_id, $whereas_statement);

        // Insert each whereas statement
        foreach ($whereas_statements as $whereas_statement) {
            $whereas_stmt->execute();
        }

        // Prepare and bind for the it_is_hereby_statements table
        $it_is_hereby_stmt = $conn->prepare("INSERT INTO it_is_hereby_statements (resolution_id, statement) VALUES (?, ?)");
        $it_is_hereby_stmt->bind_param("is", $resolution_id, $it_is_hereby_statement);

        // Insert each it is hereby statement
        foreach ($it_is_hereby_statements as $it_is_hereby_statement) {
            $it_is_hereby_stmt->execute();
        }

        // Close statements
        $whereas_stmt->close();
        $it_is_hereby_stmt->close();
        $stmt->close();

        // Return success response
        echo json_encode(['status' => 'success', 'message' => 'Resolution submitted successfully.', 'reso_id' => $resolution_id]);
    } else {
        // Return error response
        echo json_encode(['status' => 'error', 'message' => 'Failed to submit resolution.']);
    }
} else {
    // Return error response for invalid request method
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}

// Close database connection
$conn->close();
