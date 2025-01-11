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

// Get the user_id of the logged-in admin
$user_id = $_SESSION['user_id']; // Get user_id from the session

// Get the first_name and last_name of the logged-in admin from the admin_users table
$query = "SELECT first_name, last_name FROM bac_users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($first_name, $last_name); // Get the first_name and last_name
$stmt->fetch();
$stmt->close();

// If the first_name or last_name is not found, terminate
if (empty($first_name) || empty($last_name)) {
    die("User information not found.");
}

// Combine first_name and last_name as approver name
$approver_name = $first_name . ' ' . $last_name;

// Check if the form is submitted for update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the form data
    $ppmp_id = intval($_POST['ppmp_id']);
    $project_title = trim($_POST['project_title']);
    $status = trim($_POST['status']); // Status could be 'approved', 'pending', 'rejected'

    // Validate input
    if (empty($project_title) || !in_array($status, ['approved', 'pending', 'rejected'])) {
        die("Invalid input.");
    }

    // Prepare the update query for project details, status, and approver name
    $query = "UPDATE ppmp_list SET project_title = ?, approver = ?, status = ?, updated_at = NOW() WHERE ppmp_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sssi", $project_title, $approver_name, $status, $ppmp_id);

    // Execute the query
    if ($stmt->execute()) {
        // If the status is 'approved' or 'rejected', you could send a confirmation or do additional logic here
        header('Location: /rqst/bac/ppmp_list.php');
    } else {
        die("Error updating PPMP: " . $stmt->error);
    }
} elseif (isset($_GET['approve']) && isset($_GET['ppmp_id'])) {
    // If 'approve' action is triggered
    $ppmp_id = intval($_GET['ppmp_id']);

    // Update status to 'approved' and set the approver column to the logged-in user's name
    $query = "UPDATE ppmp_list SET status = 'approved', approver = ?, updated_at = NOW() WHERE ppmp_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("si", $approver_name, $ppmp_id);

    if ($stmt->execute()) {
        header('Location: /rqst/bac/ppmp_list.php'); // Redirect to PPMP list page
    } else {
        die("Error updating PPMP status: " . $stmt->error);
    }
} elseif (isset($_GET['reject']) && isset($_GET['ppmp_id'])) {
    // If 'reject' action is triggered
    $ppmp_id = intval($_GET['ppmp_id']);

    // Update status to 'rejected' and set the approver column to the logged-in user's name
    $query = "UPDATE ppmp_list SET status = 'rejected', approver = ?, updated_at = NOW() WHERE ppmp_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("si", $approver_name, $ppmp_id);

    if ($stmt->execute()) {
        header('Location: /rqst/bac/ppmp_list.php'); // Redirect to PPMP list page
    } else {
        die("Error updating PPMP status: " . $stmt->error);
    }
} else {
    die("Invalid request.");
}
