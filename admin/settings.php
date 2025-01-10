<?php
// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Include database connection
require_once '../admin/src/config/database.php';

// Fetch admin details
$user_id = $_SESSION['user_id'];
$query = "SELECT first_name, last_name, email FROM admin_users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$admin = $result->fetch_assoc();

// Handle form submission for updating personal information
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Update query
    $update_query = "UPDATE admin_users SET first_name = ?, last_name = ?, email = ?" .
        ($password ? ", password = ?" : "") .
        " WHERE id = ?";
    $stmt = $conn->prepare($update_query);

    if ($password) {
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);
        $stmt->bind_param("ssssi", $first_name, $last_name, $email, $hashed_password, $user_id);
    } else {
        $stmt->bind_param("sssi", $first_name, $last_name, $email, $user_id);
    }

    if ($stmt->execute()) {
        $_SESSION['success'] = "Your information has been updated successfully.";
    } else {
        $_SESSION['error'] = "Error updating your information.";
    }

    header("Location: settings.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Personal Settings</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <style>
        .content {
            margin-left: 250px;
            padding: 20px;
        }

        .form-container {
            max-width: 700px;
            margin: auto;
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .header-bar {
            background-color: #007bff;
            color: white;
            padding: 15px 20px;
            border-radius: 8px 8px 0 0;
            margin-bottom: -20px;
            box-shadow: 0 3px 5px rgba(0, 0, 0, 0.1);
        }

        .header-bar h3 {
            margin: 0;
        }
    </style>
</head>

<body>
    <div class="d-flex">
        <!-- Include Sidebar -->
        <?php include 'sidebar.php'; ?>

        <!-- Main Content -->
        <div class="content flex-grow-1">
            <div class="form-container">
                <!-- Header Bar -->
                <div class="header-bar">
                    <h3>Personal Settings</h3>
                    <p class="mb-0">Manage your personal account information</p>
                </div>

                <!-- Form Content -->
                <div class="p-4">
                    <!-- Success/Error Messages -->
                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="alert alert-success">
                            <?= $_SESSION['success'];
                            unset($_SESSION['success']); ?>
                        </div>
                    <?php elseif (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger">
                            <?= $_SESSION['error'];
                            unset($_SESSION['error']); ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="firstName" class="form-label">First Name</label>
                            <input type="text" class="form-control" id="firstName" name="first_name" value="<?= htmlspecialchars($admin['first_name']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="lastName" class="form-label">Last Name</label>
                            <input type="text" class="form-control" id="lastName" name="last_name" value="<?= htmlspecialchars($admin['last_name']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($admin['email']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">New Password <small class="text-muted">(Optional)</small></label>
                            <input type="password" class="form-control" id="password" name="password" placeholder="Leave blank to keep current password">
                        </div>
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>