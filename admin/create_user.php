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

// Handle form submission for creating user
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $user_type = $_POST['user_type'];

    // Determine which table to insert into based on user type
    $table = '';
    if ($user_type === 'bac') {
        $table = 'bac_users';
    } elseif ($user_type === 'budget') {
        $table = 'budget_users';
    } elseif ($user_type === 'end') {
        $table = 'end_users';
    }

    // Insert into the appropriate table
    if ($table) {
        $sql = "INSERT INTO $table (first_name, last_name, email, password) VALUES (?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        if ($stmt->execute([$first_name, $last_name, $email, $password])) {
            $_SESSION['message'] = "User created successfully!";
            header('Location: user_list.php');
            exit();
        } else {
            $error = "There was an error creating the user.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create User</title>
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
                <h1 class="text-center mb-4">Create New User</h1>
            </div>

            <div class="row">
                <!-- BAC User Card -->
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <div class="card-body text-center">
                            <i class="fas fa-user-shield fa-3x mb-3"></i>
                            <h5 class="card-title">Create BAC User</h5>
                            <p class="card-text">Create a user for BAC.</p>
                            <a href="create_bac_user.php" class="btn btn-primary">Create BAC User</a>
                        </div>
                    </div>
                </div>

                <!-- Budget User Card -->
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <div class="card-body text-center">
                            <i class="fas fa-wallet fa-3x mb-3"></i>
                            <h5 class="card-title">Create Budget User</h5>
                            <p class="card-text">Create a user for the Budget team.</p>
                            <a href="create_budget_user.php" class="btn btn-primary">Create Budget User</a>
                        </div>
                    </div>
                </div>

                <!-- End User Card -->
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <div class="card-body text-center">
                            <i class="fas fa-user-tie fa-3x mb-3"></i>
                            <h5 class="card-title">Create End User</h5>
                            <p class="card-text">Create a user for the End team.</p>
                            <a href="create_end_user.php" class="btn btn-primary">Create End User</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>