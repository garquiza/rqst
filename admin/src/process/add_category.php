<?php
require_once '../config/pdo.php'; // Update this path if needed

// Enable error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

$response = [
    'status' => 'error',
    'message' => 'An unexpected error occurred.'
];

try {
    // Validate input
    if (!isset($_POST['category_name']) || !isset($_POST['description'])) {
        $response['message'] = 'Category name and description are required.';
        echo json_encode($response);
        exit;
    }

    // Sanitize input
    $category_name = trim($_POST['category_name']);
    $description = trim($_POST['description']);

    if (empty($category_name) || empty($description)) {
        $response['message'] = 'Category name and description cannot be empty.';
        echo json_encode($response);
        exit;
    }

    // Debugging: Log the received POST data
    error_log('POST Data: ' . print_r($_POST, true));

    // Generate a unique category number
    $categoryNoQuery = "SELECT MAX(category_id) AS last_id FROM categories";
    $lastIdStmt = $pdo->query($categoryNoQuery);
    $lastIdRow = $lastIdStmt->fetch(PDO::FETCH_ASSOC);

    $newId = isset($lastIdRow['last_id']) ? $lastIdRow['last_id'] + 1 : 1;
    $category_no = 'CA-' . str_pad($newId, 5, '0', STR_PAD_LEFT);

    // Insert data into the database
    $sql = "INSERT INTO categories (category_no, category_name, description, created_at) 
            VALUES (:category_no, :category_name, :description, NOW())";
    $stmt = $pdo->prepare($sql);

    // Check for SQL execution errors
    if (!$stmt->execute([
        ':category_no' => $category_no,
        ':category_name' => $category_name,
        ':description' => $description
    ])) {
        error_log('SQL Error: ' . implode(', ', $stmt->errorInfo()));
    }

    // If the insert was successful
    if ($stmt->rowCount() > 0) {
        $response['status'] = 'success';
        $response['message'] = 'Category added successfully.';
        $response['category_no'] = $category_no; // Include category number in the response
    } else {
        $response['message'] = 'Failed to insert the category.';
    }
} catch (PDOException $e) {
    $response['message'] = 'Database error: ' . $e->getMessage();
} catch (Exception $e) {
    $response['message'] = 'Server error: ' . $e->getMessage();
}

// Log the SQL query if available
if (isset($stmt)) {
    error_log('SQL Query: ' . $stmt->queryString);
}

echo json_encode($response);
