<?php
// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Include database connection (mysqli connection)
require_once '../admin/src/config/database.php';

// Fetch sectors from the database
try {
    // Ensure the connection is established
    if ($conn) {
        $query = "SELECT name FROM sector";
        $result = $conn->query($query);

        if ($result) {
            $sectors = $result->fetch_all(MYSQLI_ASSOC);
        } else {
            die("Error fetching sectors: " . $conn->error);
        }
    } else {
        die("Database connection failed.");
    }
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create End User</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="src/css/user.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <?php include 'sidebar.php'; ?>

    <div class="content animate__animated animate__fadeIn">
        <div class="container">
            <div class="card shadow-lg p-4">
                <h1 class="header-card mb-4">Create End User</h1>

                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>

                <form id="create-user-form" action="create_end_user.php" method="POST" novalidate>
                    <div class="mb-3">
                        <label for="first_name" class="form-label">First Name</label>
                        <input type="text" class="form-control" id="first_name" name="first_name" placeholder="Enter First Name" required>
                        <div class="invalid-feedback">Please provide a first name.</div>
                    </div>

                    <div class="mb-3">
                        <label for="last_name" class="form-label">Last Name</label>
                        <input type="text" class="form-control" id="last_name" name="last_name" placeholder="Enter Last Name" required>
                        <div class="invalid-feedback">Please provide a last name.</div>
                    </div>

                    <div class="mb-3">
                        <label for="sector" class="form-label">Sector</label>
                        <select class="form-control" id="sector" name="sector" required>
                            <option value="" disabled selected>Select a sector</option>
                            <?php foreach ($sectors as $sector): ?>
                                <option value="<?= htmlspecialchars($sector['name']) ?>">
                                    <?= htmlspecialchars($sector['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback">Please select a sector.</div>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" placeholder="Enter Email Address" required>
                        <div class="invalid-feedback">Please provide a valid email address.</div>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" placeholder="Enter Password" required minlength="6">
                        <div class="invalid-feedback">Password must be at least 6 characters long.</div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 mt-3">Create End User</button>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('create-user-form').addEventListener('submit', function(e) {
            e.preventDefault(); // Prevent form submission until validation

            if (!this.checkValidity()) {
                this.classList.add('was-validated');
                return; // Stop if form validation fails
            }

            const formData = new FormData(this);

            fetch('src/process/add_end_user.php', {
                    method: 'POST',
                    body: formData,
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            title: 'Success!',
                            text: 'End user created successfully!',
                            icon: 'success',
                            showCancelButton: true,
                            confirmButtonText: 'Go to User List',
                            cancelButtonText: 'Create Another User'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.location.href = 'user_management.php';
                            } else if (result.dismiss === Swal.DismissReason.cancel) {
                                window.location.href = 'create_end_user.php';
                            }
                        });
                    } else {
                        Swal.fire({
                            title: 'Error!',
                            text: data.message || 'Failed to create the user.',
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    }
                })
                .catch(error => {
                    Swal.fire({
                        title: 'Error!',
                        text: 'Unexpected error occurred.',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                });
        });
    </script>
</body>

</html>