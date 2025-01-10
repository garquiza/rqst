<?php
// Include database connection
include('../config/database.php');

// Check if ppmp_id is provided in the request
if (isset($_GET['ppmp_id'])) {
    $ppmpId = $_GET['ppmp_id'];

    // Prepare to delete associated data in the ppmp_form table first
    $query = "DELETE FROM ppmp_form WHERE ppmp_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $ppmpId);

    if ($stmt->execute()) {
        // Now delete the entry in the ppmp_list table
        $query2 = "DELETE FROM ppmp_list WHERE ppmp_id = ?";
        $stmt2 = $conn->prepare($query2);
        $stmt2->bind_param("i", $ppmpId);

        if ($stmt2->execute()) {
            // Return success response
            echo json_encode(['success' => true, 'message' => 'PPMP and associated data deleted successfully.']);
        } else {
            // Error deleting ppmp_list
            echo json_encode(['success' => false, 'message' => 'Error deleting PPMP list entry.']);
        }
    } else {
        // Error deleting ppmp_form
        echo json_encode(['success' => false, 'message' => 'Error deleting associated PPMP form entries.']);
    }
} else {
    // No ppmp_id provided
    echo json_encode(['success' => false, 'message' => 'No PPMP ID provided.']);
}

// Close the database connection
$conn->close();
