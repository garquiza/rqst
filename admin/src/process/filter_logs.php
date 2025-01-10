<?php
require_once '../config/database.php';

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);
$searchTerm = $input['searchTerm'] ?? '';
$sortOption = $input['sortOption'] ?? '';

// Base query
$query = "SELECT * FROM history_logs WHERE 1";

// Add search filtering
if (!empty($searchTerm)) {
    $query .= " AND (title LIKE '%$searchTerm%' OR description LIKE '%$searchTerm%' OR created_at LIKE '%$searchTerm%')";
}

// Add sorting
if ($sortOption === 'date_desc') {
    $query .= " ORDER BY created_at DESC";
} elseif ($sortOption === 'date_asc') {
    $query .= " ORDER BY created_at ASC";
} elseif ($sortOption === 'status_approved') {
    $query .= " AND description LIKE '%approved%' ORDER BY created_at DESC";
} elseif ($sortOption === 'status_rejected') {
    $query .= " AND description LIKE '%rejected%' ORDER BY created_at DESC";
} else {
    $query .= " ORDER BY created_at DESC";
}

// Execute query
$result = $conn->query($query);

// Generate logs dynamically
if ($result->num_rows > 0) {
    while ($log = $result->fetch_assoc()) {
        echo '<div class="card">';
        echo '<div class="card-body">';
        echo '<h5 class="card-title">' . htmlspecialchars($log['title']) . '</h5>';
        echo '<p class="card-text">' . htmlspecialchars($log['description']) . '</p>';
        echo '<p class="card-text"><small class="text-muted">Created at: ' . $log['created_at'] . '</small></p>';
        echo '</div>';
        echo '</div>';
    }
} else {
    echo '<div class="alert alert-warning" role="alert">No logs found.</div>';
}
