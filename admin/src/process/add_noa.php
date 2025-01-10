<?php
// Start session
session_start();

// Include database connection
require_once '../config/pdo.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'User not logged in.']);
    exit();
}

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get and sanitize input data
    $authorized_representative = trim($_POST['authorized_representative']);
    $designation = trim($_POST['designation']);
    $company_name = trim($_POST['company_name']);
    $project_title = trim($_POST['project_title']);
    $contract_amount_words = trim($_POST['contract_amount_words']);
    $contract_amount_figures = trim($_POST['contract_amount_figures']);
    $philgeps_reference = trim($_POST['philgeps_reference']);

    // Validate input data
    if (
        empty($authorized_representative) || empty($designation) || empty($company_name) ||
        empty($project_title) || empty($contract_amount_words) ||
        !is_numeric($contract_amount_figures) || $contract_amount_figures <= 0 ||
        empty($philgeps_reference)
    ) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Please fill in all fields correctly.']);
        exit();
    }

    // Prepare SQL statement
    $query = "INSERT INTO notice_of_award (authorized_representative, designation, company_name, 
              project_title, contract_amount_words, contract_amount_figures, philgeps_reference) 
              VALUES (:authorized_representative, :designation, :company_name, :project_title, 
              :contract_amount_words, :contract_amount_figures, :philgeps_reference)";

    $stmt = $pdo->prepare($query);

    // Bind parameters
    $stmt->bindParam(':authorized_representative', $authorized_representative);
    $stmt->bindParam(':designation', $designation);
    $stmt->bindParam(':company_name', $company_name);
    $stmt->bindParam(':project_title', $project_title);
    $stmt->bindParam(':contract_amount_words', $contract_amount_words);
    $stmt->bindParam(':contract_amount_figures', $contract_amount_figures);
    $stmt->bindParam(':philgeps_reference', $philgeps_reference);

    // Execute the statement
    try {
        if ($stmt->execute()) {
            // Get the last inserted ID
            $noa_id = $pdo->lastInsertId();
            if (!$noa_id) {
                throw new Exception('Failed to retrieve NOA ID.');
            }
            http_response_code(200);
            echo json_encode(['success' => true, 'message' => 'Notice of Award submitted successfully.', 'noa_id' => $noa_id]);
        } else {
            throw new Exception('Failed to submit Notice of Award.');
        }
    } catch (Exception $e) {
        error_log($e->getMessage(), 3, '/path/to/error_log.log');
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'An error occurred. Please try again later.']);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
