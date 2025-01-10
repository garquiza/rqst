<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

require_once '../admin/src/config/pdo.php';

if (isset($_GET['id'])) {
    $user_id = (int)$_GET['id'];
    $sql = "SELECT * FROM bac_users WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id' => $user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo "User not found.";
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $status = $_POST['status'];
    $permissions = isset($_POST['permissions']) ? implode(',', $_POST['permissions']) : '';

    $sql = "UPDATE bac_users SET first_name = :first_name, last_name = :last_name, email = :email, status = :status, permission_access = :permissions WHERE id = :id";
    $stmt = $pdo->prepare($sql);

    if ($stmt->execute(['first_name' => $first_name, 'last_name' => $last_name, 'email' => $email, 'status' => $status, 'permissions' => $permissions, 'id' => $user_id])) {
        $_SESSION['message'] = "User updated successfully!";
        header('Location: manage_bac_user.php');
        exit();
    } else {
        $error = "There was an error updating the user.";
    }
}

$available_permissions = ['APP', 'PPMP', 'PR', 'PMAF', 'RFQ', 'AOQ', 'RESO', 'NOA', 'NTP', 'PO', 'PMR'];
$user_permissions = explode(',', $user['permission_access']);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit BAC User</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="src/css/pr.css">
    <style>
        body {
            background-color: #f8f9fa;
        }

        .card {
            margin-top: 20px;
        }

        .btn-primary {
            width: 100%;
        }

        .btn-secondary {
            width: 100%;
            margin-top: 10px;
        }

        .form-check {
            margin-bottom: 10px;
        }

        .alert {
            margin-bottom: 20px;
        }
    </style>
</head>

<body>

    <div class="d-flex">
        <!-- Include Sidebar -->
        <?php include 'sidebar.php'; ?>

        <div class="content flex-grow-1 animate__animated animate__fadeIn">
            <div class="container">
                <div class="header-card lp-4">
                    <h1 class="my-4">Edit BAC User</h1>
                </div>
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-header bg-light">
                        <strong>Update User Information</strong>
                    </div>
                    <div class="card-body">
                        <form method="POST">

                            <input type="hidden" name="id" value="<?php echo $user_id; ?>">

                            <!-- First Name and Last Name -->
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="first_name" class="form-label">First Name</label>
                                    <input type="text" class="form-control" id="first_name" name="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="last_name" class="form-label">Last Name</label>
                                    <input type="text" class="form-control" id="last_name" name="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                                </div>
                            </div>

                            <!-- Email -->
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                            </div>

                            <!-- Status -->
                            <div class="mb-3">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select" id="status" name="status" required>
                                    <option value="activate" <?php echo $user['status'] === 'activate' ? 'selected' : ''; ?>>Activate</option>
                                    <option value="disabled" <?php echo $user['status'] === 'disabled' ? 'selected' : ''; ?>>Disabled</option>
                                </select>
                            </div>

                            <!-- Permissions -->
                            <div class="mb-3">
                                <label class="form-label">Permission Access</label>
                                <div class="row">
                                    <?php foreach ($available_permissions as $permission): ?>
                                        <div class="col-md-3 form-check">
                                            <input class="form-check-input" type="checkbox" id="permission_<?php echo $permission; ?>" name="permissions[]" value="<?php echo $permission; ?>" <?php echo in_array($permission, $user_permissions) ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="permission_<?php echo $permission; ?>">
                                                <?php echo $permission; ?>
                                            </label>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <!-- Submit and Cancel Buttons -->
                            <button type="submit" class="btn btn-primary mt-3">Update User</button>
                            <a href="manage_bac_user.php" class="btn btn-secondary mt-2">Cancel</a>

                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Bootstrap Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');

            form.addEventListener('submit', function(event) {
                event.preventDefault(); // Prevent the default form submission

                Swal.fire({
                    title: 'Are you sure?',
                    text: "Do you want to update this user's information?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, update it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // If confirmed, submit the form via AJAX
                        const formData = new FormData(form);
                        fetch('src/process/edit_bac_user.php', {
                                method: 'POST',
                                body: formData
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    Swal.fire(
                                        'Updated!',
                                        'User  information has been updated.',
                                        'success'
                                    ).then(() => {
                                        window.location.href = 'manage_bac_user.php'; // Redirect after success
                                    });
                                } else {
                                    Swal.fire(
                                        'Error!',
                                        data.message,
                                        'error'
                                    );
                                }
                            })
                            .catch(error => {
                                Swal.fire(
                                    'Error!',
                                    'There was an error updating the user.',
                                    'error'
                                );
                            });
                    }
                });
            });
        });
    </script>
</body>

</html>