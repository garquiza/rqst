<?php
// Include database connection
require_once '../config/database.php';

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Initialize response array
    $response = [
        'status' => 'error',
        'message' => 'An unexpected error occurred.'
    ];

    // Get and sanitize input data
    $projectTitle = mysqli_real_escape_string($conn, $_POST['project_title']);
    $modality = isset($_POST['modality']) ? json_encode($_POST['modality'], JSON_UNESCAPED_UNICODE) : '[]';
    $funds = isset($_POST['funds']) && !empty($_POST['funds']) ? json_encode($_POST['funds'], JSON_UNESCAPED_UNICODE) : '[]';
    $mooe = isset($_POST['mooe']) && !empty($_POST['mooe']) ? json_encode($_POST['mooe'], JSON_UNESCAPED_UNICODE) : '[]';
    $coAmount = isset($_POST['co']) && is_numeric($_POST['co']) ? (float)$_POST['co'] : 0;

    // Validate required fields
    if (empty($projectTitle) || empty($modality)) {
        $response['message'] = 'Project title and modalities are required.';
        echo json_encode($response);
        exit();
    }

    // Start transaction
    mysqli_begin_transaction($conn);
    try {
        // Prepare and execute the insert query
        $stmt = $conn->prepare(
            "INSERT INTO pmaf (modality, project_title, fund, mooe_items, co_amount, submitted_at) 
            VALUES (?, (SELECT ppmp_id FROM ppmp_list WHERE project_title = ?), ?, ?, ?, NOW())"
        );

        if (!$stmt) {
            throw new Exception("Failed to prepare statement: " . $conn->error);
        }

        $stmt->bind_param("ssssd", $modality, $projectTitle, $funds, $mooe, $coAmount);

        if (!$stmt->execute()) {
            throw new Exception("Database execution error: " . $stmt->error);
        }

        // Commit the transaction
        mysqli_commit($conn);

        // Success response
        $response['status'] = 'success';
        $response['message'] = 'Procurement modality approval form submitted successfully!';
    } catch (Exception $e) {
        // Rollback the transaction in case of an error
        mysqli_rollback($conn);
        $response['message'] = $e->getMessage();
    } finally {
        // Close the statement and connection
        $stmt->close();
        $conn->close();
    }

    // Return the response as JSON
    echo json_encode($response);
    exit();
}

// If not a POST request, return an error
http_response_code(405);
echo json_encode(["status" => "error", "message" => "Invalid request method."]);
exit();
