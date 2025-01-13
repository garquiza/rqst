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
    $fundSource = isset($_POST['fund_source']) ? mysqli_real_escape_string($conn, $_POST['fund_source']) : ''; // This is for the 'fund' column
    $mooe = isset($_POST['mooe']) && is_numeric($_POST['mooe']) ? (float)$_POST['mooe'] : 0;
    $coAmount = isset($_POST['co']) && is_numeric($_POST['co']) ? (float)$_POST['co'] : 0;
    $totalABC = isset($_POST['total_abc']) && is_numeric($_POST['total_abc']) ? (float)$_POST['total_abc'] : 0;

    // Validate required fields
    if (empty($projectTitle) || empty($modality) || empty($fundSource)) {
        $response['message'] = 'Project title, modalities, and fund source are required.';
        echo json_encode($response);
        exit();
    }

    // Start transaction
    mysqli_begin_transaction($conn);
    try {
        // Prepare and execute the insert query
        $stmt = $conn->prepare(
            "INSERT INTO pmaf (modality, project_title, fund, total_abc, mooe_items, co_amount, submitted_at) 
            VALUES (?, ?, ?, ?, ?, ?, NOW())"
        );

        if (!$stmt) {
            throw new Exception("Failed to prepare statement: " . $conn->error);
        }

        // Bind parameters (fixing data types)
        $stmt->bind_param("sssddd", $modality, $projectTitle, $fundSource, $totalABC, $mooe, $coAmount);

        // Execute the query
        if (!$stmt->execute()) {
            error_log("Database execution error: " . $stmt->error);
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
?>
