<?php
// Include database connection
require_once '../config/database.php';

// Set content type to JSON
header('Content-Type: application/json');

if (isset($_GET['pr_id'])) {
    $pr_id = intval($_GET['pr_id']);

    try {
        // Prepare delete query
        $query = "DELETE FROM purchase_requests WHERE pr_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $pr_id);

        if ($stmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'The purchase request has been successfully deleted.'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Failed to delete the purchase request. Please try again later.'
            ]);
        }

        $stmt->close();
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'An error occurred: ' . $e->getMessage()
        ]);
    } finally {
        $conn->close();
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request. Purchase request ID is missing.'
    ]);
}
