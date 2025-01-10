<?php
// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Include database connection
require_once '../config/database.php';

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the form data
    $ppmp_id = intval($_POST['ppmp_id']);
    $project_title = trim($_POST['project_title']);
    $approver = trim($_POST['approver']);
    $status = trim($_POST['status']);

    // Validate input
    if (empty($project_title) || empty($approver) || !in_array($status, ['approved', 'pending', 'rejected'])) {
        die("Invalid input.");
    }

    // Prepare the update query
    $query = "UPDATE ppmp_list SET project_title = ?, approver = ?, status = ?, updated_at = NOW() WHERE ppmp_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sssi", $project_title, $approver, $status, $ppmp_id);

    // Execute the query
    if ($stmt->execute()) {
        // Redirect to the PPMP list page with a success message
        header('Location: /rqst/admin/ppmp_list.php');
    } else {
        die("Error updating PPMP: " . $stmt->error);
    }
} else {
    die("Invalid request.");
}
