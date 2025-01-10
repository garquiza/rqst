<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit();
}

require_once '../config/database.php';

// Check if ppmp_id is provided
if (isset($_GET['ppmp_id'])) {
    $ppmp_id = intval($_GET['ppmp_id']);

    $conn->begin_transaction();

    try {
        // Delete associated purchase requests
        $deletePRQuery = "DELETE FROM purchase_requests WHERE ppmp_id = ?";
        $stmt = $conn->prepare($deletePRQuery);
        $stmt->bind_param("i", $ppmp_id);
        $stmt->execute();

        // Delete associated ppmp forms
        $deletePPMPFormQuery = "DELETE FROM ppmp_form WHERE ppmp_id = ?";
        $stmt = $conn->prepare($deletePPMPFormQuery);
        $stmt->bind_param("i", $ppmp_id);
        $stmt->execute();

        // Delete the PPMP record
        $deletePPMPQuery = "DELETE FROM ppmp_list WHERE ppmp_id = ?";
        $stmt = $conn->prepare($deletePPMPQuery);
        $stmt->bind_param("i", $ppmp_id);
        $stmt->execute();

        $conn->commit();
        echo json_encode(['success' => true, 'message' => 'PPMP deleted successfully.']);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'Error deleting PPMP: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
}
