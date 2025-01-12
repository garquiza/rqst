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
$general_description = $_POST['general_description']; // This is an array now
$quantity_size = $_POST['quantity_size']; // This is an array now
$estimated_budget = $_POST['estimated_budget'];
$schedule = json_encode($_POST['schedule']);  // Convert the schedule array to JSON
$mode_of_procurement = $_POST['mode_of_procurement'];  // New field
$unit_measurement = $_POST['unit_measurement']; // This is an array now
$unit_cost = $_POST['unit_cost']; // This is an array now

// Get the user ID from the session
$user_id = $_SESSION['user_id'];

try {
    // Insert data into ppmp_list table
    $query = "INSERT INTO ppmp_list (project_title, user_id) VALUES (?, ?)";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$project_title, $user_id]);

    // Get the last inserted ppmp_id
    $ppmp_id = $pdo->lastInsertId();

    // Fetch item names for the given item_ids in the general description
    foreach ($general_description as &$item_id) {
        $query = "SELECT item_name FROM items WHERE item_id = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$item_id]);
        $item = $stmt->fetch(PDO::FETCH_ASSOC);

        // Replace item_id with item_name
        if ($item) {
            $item_id = $item['item_name'];
        }
    }

    // Encode the arrays as JSON for a single row insertion
    $general_description_json = json_encode($general_description);
    $quantity_size_json = json_encode($quantity_size);
    $unit_measurement_json = json_encode($unit_measurement);
    $unit_cost_json = json_encode($unit_cost);

    // Insert data into ppmp_form table with JSON fields
    $query = "INSERT INTO ppmp_form (year, code, general_description, quantity_size, estimated_budget, schedule, ppmp_id, mode_of_procurement, unit_measurement, unit_cost) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($query);
    $stmt->execute([
        $year,
        $code,
        $general_description_json,
        $quantity_size_json,
        $estimated_budget,
        $schedule,
        $ppmp_id,
        $mode_of_procurement,
        $unit_measurement_json,
        $unit_cost_json
    ]);

    // Success: Create a notification
    $notification_title = "New PPMP Created";
    $notification_message = "A new PPMP titled '{$project_title}' has been created by {$user_name}.";

    // Insert notification into notifications table
    $notification_query = "INSERT INTO notifications (title, message, user_id) VALUES (?, ?, ?)";
    $notification_stmt = $pdo->prepare($notification_query);
    $notification_stmt->execute([$notification_title, $notification_message, $user_id]);

    echo json_encode(['status' => 'success', 'message' => 'PPMP created successfully and notification sent.']);
} catch (PDOException $e) {
    // Output the PDO error message for debugging
    echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
}
