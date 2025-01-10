<?php
session_start();

// Redirect to login page if the user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch user details from session
$user_name = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'User';

// Include database connection
include('src/config/database.php');

// Fetch user settings from the database
$sqlUser = "SELECT * FROM end_users WHERE id = ?";
$stmtUser = $conn->prepare($sqlUser);
$stmtUser->bind_param("i", $_SESSION['user_id']);
$stmtUser->execute();
$resultUser = $stmtUser->get_result();
$userData = $resultUser->fetch_assoc();

// Close the database connection
$stmtUser->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Settings</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link rel="stylesheet" href="src/css/dashboard.css">
    <style>
        body {
            background-color: #f8f9fa;
        }

        .card {
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .card-header {
            font-size: 1.2rem;
            font-weight: bold;
        }

        .form-control:focus {
            box-shadow: 0 0 5px rgba(0, 123, 255, 0.5);
        }

        .btn-primary {
            background: linear-gradient(90deg, #007bff, #0056b3);
            border: none;
        }

        .btn-primary:hover {
            background: linear-gradient(90deg, #0056b3, #004085);
        }

        .success-alert,
        .error-alert {
            display: none;
        }
    </style>
</head>

<body>
    <div class="d-flex">
        <!-- Sidebar -->
        <?php include 'sidebar.php'; ?>

        <!-- Main Content -->
        <div class="content flex-grow-1 animate__animated animate__fadeIn">
            <div class="container">
                <div class="header-card">
                <h2 class="text-center mb-4">User Settings</h2>
                </div>
                <!-- Feedback Alerts -->
                <div class="alert alert-success success-alert" id="success-alert" role="alert">
                    <i class="fas fa-check-circle"></i> Settings updated successfully!
                </div>
                <div class="alert alert-danger error-alert" id="error-alert" role="alert">
                    <i class="fas fa-exclamation-circle"></i> Something went wrong. Please try again.
                </div>

                <div class="row">
                    <!-- User Information Form -->
                    <div class="col-md-8 offset-md-2">
                        <div class="card">
                            <div class="card-header bg-primary text-white">
                                <i class="fas fa-user-cog me-2"></i> Account Details
                            </div>
                            <div class="card-body">
                                <form id="settings-form" action="update_settings.php" method="POST">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="first_name" class="form-label">First Name</label>
                                            <input type="text" class="form-control" id="first_name" name="first_name" value="<?= htmlspecialchars($userData['first_name']) ?>" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="last_name" class="form-label">Last Name</label>
                                            <input type="text" class="form-control" id="last_name" name="last_name" value="<?= htmlspecialchars($userData['last_name']) ?>" required>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email</label>
                                        <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($userData['email']) ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="password" class="form-label">New Password</label>
                                        <input type="password" class="form-control" id="password" name="password" placeholder="••••••••">
                                        <small class="text-muted">Leave blank to keep your current password.</small>
                                    </div>
                                    <div class="mb-3">
                                        <label for="status" class="form-label">Account Status</label>
                                        <select class="form-select" id="status" name="status">
                                            <option value="activate" <?= $userData['status'] == 'activate' ? 'selected' : '' ?>>Activate</option>
                                            <option value="disabled" <?= $userData['status'] == 'disabled' ? 'selected' : '' ?>>Disabled</option>
                                        </select>
                                    </div>
                                    <div class="mb-3 form-check">
                                        <input class="form-check-input" type="checkbox" id="remember_me" name="remember_me" <?= $userData['remember_me'] ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="remember_me">Remember Me</label>
                                    </div>
                                    <div class="text-center">
                                        <button type="submit" class="btn btn-primary">Save Changes</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <!-- End of User Information Form -->
                </div>
            </div>
        </div>
        <!-- End of Main Content -->
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Example feedback alert handling
        const successAlert = document.getElementById('success-alert');
        const errorAlert = document.getElementById('error-alert');

        <?php if (isset($_SESSION['update_success']) && $_SESSION['update_success']): ?>
            successAlert.style.display = 'block';
            setTimeout(() => successAlert.style.display = 'none', 3000);
            <?php unset($_SESSION['update_success']); ?>
        <?php elseif (isset($_SESSION['update_error']) && $_SESSION['update_error']): ?>
            errorAlert.style.display = 'block';
            setTimeout(() => errorAlert.style.display = 'none', 3000);
            <?php unset($_SESSION['update_error']); ?>
        <?php endif; ?>
    </script>
</body>

</html>