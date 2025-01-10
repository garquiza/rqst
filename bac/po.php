<?php
// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Include database connection
require_once '../bac/src/config/database.php';

// Fetch project titles for the dropdown
$query = "SELECT noa_id, project_title, philgeps_reference, designation FROM notice_of_award";
$result = $conn->query($query);
$projects = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $projects[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Purchase Order</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="src/css/pr.css">
    <style>
        .content {
            margin-left: 250px;
            padding: 20px;
        }

        .header-card {
            margin-bottom: 30px;
        }

        .form-container {
            border: 1px solid #e0e0e0;
            padding: 20px;
            border-radius: 5px;
            background-color: #f8f9fa;
        }

        .form-control {
            border: 1px solid #ced4da;
        }

        .label-bold {
            font-weight: bold;
        }

        .btn-submit {
            background-color: #007bff;
            color: white;
        }

        .btn-submit:hover {
            background-color: #0056b3;
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
                <h1 class="mb-4">Purchase Order (PO)</h1>
            </div>

            <div class="form-container">
                <form action="#" method="POST">
                    <!-- Project Title -->
                    <div class="mb-3">
                        <label for="project_title" class="form-label label-bold">Project Title:</label>
                        <select class="form-control" id="project_title" name="project_title">
                            <option value="" selected>Select a Project</option>
                            <?php foreach ($projects as $project): ?>
                                <option value="<?= $project['noa_id']; ?>"
                                    data-philgeps="<?= $project['philgeps_reference']; ?>">
                                    <?= htmlspecialchars($project['project_title']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- PHILGEPS Reference No. -->
                    <div class="mb-3">
                        <label for="philgeps_ref" class="form-label label-bold">PHILGEPS Reference No.:</label>
                        <input type="text" class="form-control" id="philgeps_ref" name="philgeps_ref" placeholder="Auto-Filled" readonly>
                    </div>

                    <!-- Location of the Project -->
                    <div class="mb-3">
                        <label for="project_location" class="form-label label-bold">Location of the Project:</label>
                        <input type="text" class="form-control" id="project_location" name="project_location" placeholder="Enter Project Location">
                    </div>

                    <!-- Supplier and Mode of Procurement -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="supplier" class="form-label label-bold">Supplier:</label>
                            <input type="text" class="form-control" id="supplier" name="supplier" placeholder="Enter Supplier Name">
                        </div>
                        <div class="col-md-6">
                            <label for="mode_of_procurement" class="form-label label-bold">Mode of Procurement:</label>
                            <input type="text" class="form-control" id="mode_of_procurement" name="mode_of_procurement" placeholder="Enter Mode of Procurement">
                        </div>
                    </div>

                    <!-- Address -->
                    <div class="mb-3">
                        <label for="address" class="form-label label-bold">Address:</label>
                        <input type="text" class="form-control" id="address" name="address" placeholder="Enter Address">
                    </div>

                    <!-- City, Telephone No., TIN -->
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="city" class="form-label label-bold">City:</label>
                            <input type="text" class="form-control" id="city" name="city" placeholder="Enter City">
                        </div>
                        <div class="col-md-4">
                            <label for="telephone" class="form-label label-bold">Telephone No.:</label>
                            <input type="text" class="form-control" id="telephone" name="telephone" placeholder="Enter Telephone No.">
                        </div>
                        <div class="col-md-4">
                            <label for="tin" class="form-label label-bold">TIN:</label>
                            <input type="text" class="form-control" id="tin" name="tin" placeholder="Enter TIN">
                        </div>
                    </div>

                    <!-- Place of Delivery and Delivery Terms -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="place_of_delivery" class="form-label label-bold">Place of Delivery</label>
                            <input type="text" class="form-control" id="place_of_delivery" name="place_of_delivery" placeholder="Enter Place of Delivery">
                        </div>
                        <div class="col-md-6">
                            <label for="delivery_terms" class="form-label label-bold">Delivery Terms</label>
                            <input type="text" class="form-control" id="delivery_terms" name="delivery_terms" placeholder="Enter Delivery Terms">
                        </div>
                    </div>

                    <!-- Date of Delivery and Payment Terms -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="date_of_delivery" class="form-label label-bold">Date of Delivery</label>
                            <input type="date" class="form-control" id="date_of_delivery" name="date_of_delivery">
                        </div>
                        <div class="col-md-6">
                            <label for="payment_terms" class="form-label label-bold">Payment Terms</label>
                            <input type="text" class="form-control" id="payment_terms" name="payment_terms" placeholder="Enter Payment Terms">
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="mb-3">
                        <button type="submit" class="btn btn-primary">Submit Purchase Order Then Proceed</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.getElementById('project_title').addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const philgepsRef = selectedOption.getAttribute('data-philgeps');

            // Auto-fill the PHILGEPS Reference No. only
            document.getElementById('philgeps_ref').value = philgepsRef || '';
        });
    </script>
    <script>
        document.querySelector('.btn-primary').addEventListener('click', function(e) {
            e.preventDefault();

            // Validate required fields
            const requiredFields = [
                'project_title',
                'supplier',
                'mode_of_procurement',
                'address',
                'city',
                'telephone',
                'tin',
                'place_of_delivery',
                'delivery_terms',
                'date_of_delivery',
                'payment_terms'
            ];

            let isValid = true;
            let emptyFields = [];

            requiredFields.forEach(field => {
                const input = document.getElementById(field);
                if (!input.value.trim()) {
                    isValid = false;
                    emptyFields.push(input.placeholder || input.name);
                }
            });

            if (!isValid) {
                Swal.fire({
                    title: 'Validation Error',
                    text: `Please fill in the following fields: ${emptyFields.join(', ')}`,
                    icon: 'error'
                });
                return; // Stop the submission process
            }

            // Proceed with submission confirmation
            Swal.fire({
                title: 'Confirm Submission',
                text: "Are you sure you want to submit the purchase order?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, Submit!'
            }).then((result) => {
                if (result.isConfirmed) {
                    const formData = new FormData(document.querySelector('form'));

                    fetch('src/process/add_po.php', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.status === 'success') {
                                Swal.fire({
                                    title: 'Success!',
                                    text: 'Purchase Order has been submitted.',
                                    icon: 'success'
                                }).then(() => {
                                    // Redirect to po_next.php with the project ID and title
                                    window.location.href = `po_next.php?id=${data.noa_id}&title=${encodeURIComponent(data.project_title)}`;
                                });
                            } else {
                                Swal.fire('Failed!', data.message, 'error');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            Swal.fire('Error!', 'Something went wrong during submission.', 'error');
                        });
                }
            });
        });
    </script>
</body>

</html>