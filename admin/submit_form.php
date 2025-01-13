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
$ppmp_form_id = isset($_POST['ppmp_form_id']) ? $_POST['ppmp_form_id'] : null; // Check if ppmp_form_id exists
$year = $_POST['year'];
$code = $_POST['code'];
$estimated_budget = $_POST['estimated_budget'];
$mode_of_procurement = $_POST['mode_of_procurement'];  // New field
$schedule = json_encode($_POST['schedule']);  // Convert the schedule array to JSON
$general_description = $_POST['general_description']; // This is an array now
$quantity_size = $_POST['quantity_size']; // This is an array now
$unit_measurement = $_POST['unit_measurement']; // This is an array now
$unit_cost = $_POST['unit_cost']; // This is an array now

// Get the user ID from the session
$user_id = $_SESSION['user_id'];

try {
    if ($ppmp_form_id) {
        // Update the existing PPMP form if ppmp_form_id is provided
        $query = "UPDATE ppmp_form 
                  SET year = ?, code = ?, general_description = ?, quantity_size = ?, estimated_budget = ?, schedule = ?, mode_of_procurement = ?, unit_measurement = ?, unit_cost = ? 
                  WHERE ppmp_form_id = ? AND ppmp_id IN (SELECT ppmp_id FROM ppmp_list WHERE user_id = ?)";
        $stmt = $pdo->prepare($query);
        $stmt->execute([
            $year,
            $code,
            json_encode($general_description),
            json_encode($quantity_size),
            $estimated_budget,
            $schedule,
            $mode_of_procurement,
            json_encode($unit_measurement),
            json_encode($unit_cost),
            $ppmp_form_id,
            $user_id
        ]);

        echo json_encode(['status' => 'success', 'message' => 'PPMP updated successfully.']);
    } else {
        // Handle the case where the ppmp_form_id is not provided for update (optional)
    }
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
}
