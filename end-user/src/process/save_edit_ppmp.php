<?php
session_start();
include('../config/database.php');

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

// Check if the required POST data is set
if (isset($_POST['ppmp_id'], $_POST['year'], $_POST['code'], $_POST['general_description'], $_POST['quantity_size'], $_POST['estimated_budget'], $_POST['schedule'])) {
    $ppmp_id = intval($_POST['ppmp_id']);
    $year = $_POST['year'];
    $code = $_POST['code'];
    $general_description = $_POST['general_description'];
    $quantity_size = $_POST['quantity_size'];
    $estimated_budget = $_POST['estimated_budget'];
    $schedule = json_encode($_POST['schedule']);

    // Update the ppmp_form data
    $updateQuery = "UPDATE ppmp_form SET year = ?, code = ?, general_description = ?, quantity_size = ?, estimated_budget = ?, schedule = ? WHERE ppmp_id = ?";
    $updateStmt = $conn->prepare($updateQuery);
    $updateStmt->bind_param("ssssssi", $year, $code, $general_description, $quantity_size, $estimated_budget, $schedule, $ppmp_id);

    if ($updateStmt->execute()) {
        // Update the status in ppmp_list to 'pending'
        $statusUpdateQuery = "UPDATE ppmp_list SET status = 'pending' WHERE ppmp_id = ?";
        $statusUpdateStmt = $conn->prepare($statusUpdateQuery);
        $statusUpdateStmt->bind_param("i", $ppmp_id);
        $statusUpdateStmt->execute();

        echo json_encode(['status' => 'success', 'message' => 'PPMP form updated successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error updating PPMP form: ' . $conn->error]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
}
