<?php
session_start();

// Redirect to login page if the user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch user details from session
$user_name = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'User ';

include('../config/pdo.php');

// Get POST data
$year = $_POST['year'];
$code = $_POST['code'];
$project_title = $_POST['project_title'];
$general_description = $_POST['general_description'];
$quantity_size = $_POST['quantity_size'];
$estimated_budget = $_POST['estimated_budget'];
$schedule = json_encode($_POST['schedule']);  // Convert the schedule array to JSON

// Get the user ID from the session
$user_id = $_SESSION['user_id'];

// Insert data into ppmp_list table
$query = "INSERT INTO ppmp_list (project_title, user_id) VALUES (?, ?)";
$stmt = $pdo->prepare($query);
$stmt->execute([$project_title, $user_id]);

// Get the last inserted ppmp_id
$ppmp_id = $pdo->lastInsertId();

// Insert data into ppmp_form table
$query = "INSERT INTO ppmp_form (year, code, general_description, quantity_size, estimated_budget, schedule, ppmp_id) 
          VALUES (?, ?, ?, ?, ?, ?, ?)";
$stmt = $pdo->prepare($query);

if ($stmt->execute([$year, $code, $general_description, $quantity_size, $estimated_budget, $schedule, $ppmp_id])) {
    // Success: Create a notification
    $notification_title = "New PPMP Created";
    $notification_message = "A new PPMP titled '{$project_title}' has been created by {$user_name}.";

    // Insert notification into notifications table
    $notification_query = "INSERT INTO notifications (title, message, user_id) VALUES (?, ?, ?)";
    $notification_stmt = $pdo->prepare($notification_query);
    $notification_stmt->execute([$notification_title, $notification_message, $user_id]);

    echo json_encode(['status' => 'success', 'message' => 'PPMP created successfully and notification sent.']);
} else {
    // Error
    echo json_encode(['status' => 'error', 'message' => 'Failed to create PPMP']);
}
