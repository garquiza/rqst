<?php
// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Include database connection (adjust path as needed)
require_once '../admin/src/config/database.php'; // Update with your actual path

// Handle form submission for managing user (if applicable)
// This section can be expanded based on your management logic
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Logic for managing users can be added here
    // For example, updating user details, deleting users, etc.
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage User</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Link to user.css -->
    <link rel="stylesheet" href="src/css/user.css">
</head>

<body>
    <?php include 'sidebar.php'; ?>
    <div class="content animate__animated animate__fadeIn">
        <div class="container">
            <div class="header-card">
                <h1 class="text-center mb-4">Manage Users</h1>
            </div>

            <div class="row">
                <!-- BAC User Card -->
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <div class="card-body text-center">
                            <i class="fas fa-user-shield fa-3x mb-3"></i>
                            <h5 class="card-title">Manage BAC Users</h5>
                            <p class="card-text">View and manage BAC users.</p>
                            <a href="manage_bac_user.php" class="btn btn-primary">Manage BAC Users</a>
                        </div>
                    </div>
                </div>

                <!-- Budget User Card -->
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <div class="card-body text-center">
                            <i class="fas fa-wallet fa-3x mb-3"></i>
                            <h5 class="card-title">Manage Budget Users</h5>
                            <p class="card-text">View and manage Budget team users.</p>
                            <a href="manage_budget_user.php" class="btn btn-primary">Manage Budget Users</a>
                        </div>
                    </div>
                </div>

                <!-- End User Card -->
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <div class="card-body text-center">
                            <i class="fas fa-user-tie fa-3x mb-3"></i>
                            <h5 class="card-title">Manage End Users</h5>
                            <p class="card-text">View and manage End team users.</p>
                            <a href="manage_end_user.php" class="btn btn-primary">Manage End Users</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>