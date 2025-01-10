<?php
// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Include database connection
require_once '../admin/src/config/pdo.php';

// Fetch the user's data for editing
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $sql = "SELECT * FROM end_users WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id' => $id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo "User  not found!";
        exit();
    }
} else {
    header('Location: manage_end_user.php');
    exit();
}

// Handle form submission to update user
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $status = $_POST['status'];

    // Update query
    $update_sql = "UPDATE end_users 
                   SET first_name = :first_name, last_name = :last_name, email = :email, status = :status, updated_at = NOW()
                   WHERE id = :id";

    $update_stmt = $pdo->prepare($update_sql);

    $update_stmt->execute([
        'first_name' => $first_name,
        'last_name' => $last_name,
        'email' => $email,
        'status' => $status,
        'id' => $id
    ]);

    header('Location: manage_end_users.php?success=updated');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Edit End User</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="src/css/pr.css">
    <style>
        .content {
            margin-left: 250px;
            padding: 20px;
        }
    </style>
</head>

<body>
    <div class="d-flex">
        <!-- Include Sidebar -->
        <?php include 'sidebar.php'; ?>

        <!-- Main Content -->
        <div class="content flex-grow-1 animate__animated animate__fadeIn">
            <div class="header-card">
                <h1 class="mb-4">Edit End User</h1>
                <p class="mb-0">Update the details of the selected user.</p>
            </div>

            <div class="card mt-4">
                <div class="card-body">
                    <form id="editUser Form" method="POST">
                        <input type="hidden" name="id" value="<?= htmlspecialchars($user['id']) ?>">
                        <div class="mb-3">
                            <label for="first_name" class="form-label">First Name</label>
                            <input type="text" class="form-control" id="first_name" name="first_name" value="<?= htmlspecialchars($user['first_name']) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="last_name" class="form-label">Last Name</label>
                            <input type="text" class="form-control" id="last_name" name="last_name" value="<?= htmlspecialchars($user['last_name']) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-control" id="status" name="status" required>
                                <option value="activate" <?= $user['status'] === 'activate' ? 'selected' : '' ?>>Activate</option>
                                <option value="disabled" <?= $user['status'] === 'disabled' ? 'selected' : '' ?>> Disabled</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Update</button>
                        <a href="manage_end_user.php" class="btn btn-secondary">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.getElementById('editUser Form').addEventListener('submit', function(e) {
            e.preventDefault(); // Prevent the default form submission

            // Show confirmation dialog
            Swal.fire({
                title: 'Are you sure?',
                text: "Do you want to update this user?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, update it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    // If confirmed, submit the form via AJAX
                    const formData = new FormData(this);
                    fetch('src/process/edit_enduser.php?id=<?= $user['id'] ?>', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire(
                                    'Updated!',
                                    'The user has been updated.',
                                    'success'
                                ).then(() => {
                                    // Redirect to the manage end users page
                                    window.location.href = 'manage_end_user.php';
                                });
                            } else {
                                Swal.fire(
                                    'Error!',
                                    'There was a problem updating the user.',
                                    'error'
                                );
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            Swal.fire(
                                'Error!',
                                'There was a problem with the request.',
                                'error'
                            );
                        });
                }
            });
        });
    </script>
</body>

</html>