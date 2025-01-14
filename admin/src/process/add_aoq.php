<?php
// Start session
session_start();

// Include database connection
require_once '../config/database.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in.']);
    exit();
}

// Get the form data
$rfq_id = isset($_POST['project']) ? intval($_POST['project']) : 0;
$project_location = isset($_POST['project_location']) ? trim($_POST['project_location']) : '';
$implementing_office = isset($_POST['implementing_office']) ? trim($_POST['implementing_office']) : '';
$approved_budget = isset($_POST['approved_budget']) ? floatval($_POST['approved_budget']) : '';
$prepared_by = isset($_POST['prepared_by']) ? trim($_POST['prepared_by']) : '';
$verified_by = isset($_POST['verified_by']) ? trim($_POST['verified_by']) : '';

// Validate the input data
if (empty($rfq_id) || empty($project_location) || empty($implementing_office) || $approved_budget <= 0 || empty($prepared_by) || empty($verified_by)) {
    echo json_encode(['success' => false, 'message' => 'Please fill in all required fields.']);
    exit();
}

// Fetch the project title from the rfq table
$sql_project_title = "SELECT project_title FROM rfq WHERE rfq_id = ?";
$stmt_project = $conn->prepare($sql_project_title);
$stmt_project->bind_param("i", $rfq_id);
$stmt_project->execute();
$result_project = $stmt_project->get_result();

if ($result_project->num_rows > 0) {
    $project = $result_project->fetch_assoc();
    $project_title = $project['project_title'];
} else {
    echo json_encode(['success' => false, 'message' => 'Project not found.']);
    exit();
}

// Prepare the SQL statement to insert into abstract_of_quotation
$sql = "INSERT INTO abstract_of_quotation (rfq_id, project_title, project_location, implementing_office, approved_budget, prepared_by, verified_by) 
        VALUES (?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);

if ($stmt) {
    // Bind parameters
    // 'issdsss' means:
    // i: integer for rfq_id,
    // s: string for project_title, project_location, implementing_office, prepared_by, verified_by,
    // d: double for approved_budget
    $stmt->bind_param("isssdss", $rfq_id, $project_title, $project_location, $implementing_office, $approved_budget, $prepared_by, $verified_by);

    // Execute the statement
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Data saved successfully.']);
    } else {
        // Log the error for debugging
        error_log("Error executing statement: " . $stmt->error);
        echo json_encode(['success' => false, 'message' => 'Error saving data: ' . $stmt->error]);
    }

    // Close the statement
    $stmt->close();
} else {
    // Log the error for debugging
    error_log("Error preparing statement: " . $conn->error);
    echo json_encode(['success' => false, 'message' => 'Error preparing statement: ' . $conn->error]);
}

// Close the database connection
$conn->close();
