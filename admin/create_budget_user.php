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
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Budget User</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Link to user.css -->
    <link rel="stylesheet" href="src/css/user.css">
    <!-- Include SweetAlert2 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <?php include 'sidebar.php'; ?>
    <div class="content animate__animated animate__fadeIn">
        <div class="container">
            <div class="card shadow-lg p-4">
                <div class="header-card text-center">
                    <h1 class="mb-0">Create Budget User</h1>
                </div>
                <div class="card-body">
                    <form id="create-budget-user-form" method="POST" novalidate>
                        <div class="mb-3">
                            <label for="first_name" class="form-label">First Name</label>
                            <input type="text" class="form-control" id="first_name" name="first_name" placeholder="Enter first name" required pattern="[A-Za-z]+" title="First name must contain only letters.">
                            <div class="invalid-feedback">
                                Please provide a valid first name.
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="last_name" class="form-label">Last Name</label>
                            <input type="text" class="form-control" id="last_name" name="last_name" placeholder="Enter last name" required pattern="[A-Za-z]+" title="Last name must contain only letters.">
                            <div class="invalid-feedback">
                                Please provide a valid last name.
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" placeholder="Enter email" required>
                            <div class="invalid-feedback">
                                Please enter a valid email address.
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" placeholder="Enter password" required minlength="6" title="Password must be at least 6 characters long.">
                            <div class="invalid-feedback">
                                Password must be at least 6 characters long.
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">Create Budget User</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Form submission handling
        document.getElementById('create-budget-user-form').addEventListener('submit', function(e) {
            // Prevent form submission if it is invalid
            if (!this.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
            }

            // Add Bootstrap's validation classes
            this.classList.add('was-validated');

            // If the form is valid, proceed with AJAX submission
            if (this.checkValidity()) {
                const formData = new FormData(this); // Collect form data

                // Send form data via AJAX
                fetch('src/process/add_budget_user.php', {
                        method: 'POST',
                        body: formData,
                    })
                    .then(response => {
                        console.log(response); // Log the response
                        return response.json();
                    })
                    .then(data => {
                        console.log(data); // Log the data received
                        if (data.success) {
                            // Handle success
                            Swal.fire({
                                title: 'Success!',
                                text: data.message,
                                icon: 'success',
                                showCancelButton: true,
                                confirmButtonText: 'Go to User List',
                                cancelButtonText: 'Create Another User'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    window.location.href = 'user_management.php';
                                } else if (result.dismiss === Swal.DismissReason.cancel) {
                                    window.location.href = 'create_budget_user.php';
                                }
                            });
                        } else {
                            // Handle failure
                            Swal.fire({
                                title: 'Error!',
                                text: data.message || 'There was an error creating the user.',
                                icon: 'error',
                                confirmButtonText: 'OK'
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error); // Log any errors
                        Swal.fire({
                            title: 'Error!',
                            text: 'There was an unexpected error.',
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    });
            }
        });
    </script>
</body>

</html>