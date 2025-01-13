<?php
// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Include database connection (adjust the path if necessary)
require_once '../admin/src/config/pdo.php';

// Fetch the latest budget amount from the budget_amount table
$query = "SELECT amount FROM budget_amount ORDER BY created_at DESC LIMIT 1";
$stmt = $pdo->prepare($query);
$stmt->execute();
$latestBudget = $stmt->fetch(PDO::FETCH_ASSOC)['amount'] ?? 0;

// Fetch sectors from the database
$query = "SELECT * FROM sector";
$stmt = $pdo->prepare($query);
$stmt->execute();
$sectors = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Total Budget Cost</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="src/css/pr.css">
    <style>
        .content {
            margin-left: 250px;
            padding: 20px;
        }

        .header-card {
            background: linear-gradient(90deg, #007bff, #4bacef);
            color: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .table-section {
            margin-top: 20px;
        }

        .action-buttons i {
            font-size: 18px;
            cursor: pointer;
            margin-right: 5px;
        }

        .action-buttons i:hover {
            color: #007bff;
        }

        .add-sector-btn {
            float: right;
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
                <h1 class="mb-2">Total Budget Cost</h1>
                <p class="mb-0">Manage and allocate the budget effectively.</p>
            </div>

            <div class="col-md-6">
                <div class="card border-success">
                    <div class="card-body">
                        <h5 class="card-title">Budget Amount (From Budget Office)</h5>
                        <p class="card-text text-primary fw-bold fs-4">
                            <span id="budget-amount" style="cursor: pointer; text-decoration: underline;">
                                ₱ <?php echo number_format($latestBudget, 2); ?>
                            </span>
                        </p>
                    </div>
                </div>
            </div>
            <!-- Budget Per Sector Section with Search -->
            <div class="table-section">
                <h5 class="mb-3 d-flex justify-content-between align-items-center">
                    Budget Per Sector
                    <button class="btn btn-sm btn-primary add-sector-btn">
                        <i class="fas fa-plus"></i> Add Sector
                    </button>
                </h5>

                <!-- Search Bar -->
                <div class="mb-3">
                    <input type="text" id="search-bar" class="form-control" placeholder="Search sector by name...">
                </div>

                <!-- Table -->
                <table class="table table-striped table-hover table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>Sector Name</th>
                            <th>Budget</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="sector-table">
                        <?php foreach ($sectors as $sector): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($sector['name']); ?></td>
                                <td>₱ <?php echo number_format($sector['budget'], 2); ?></td>
                                <td class="action-buttons">
                                    <!-- Edit Icon -->
                                    <i class="fas fa-edit text-warning edit-btn" title="Edit" data-id="<?php echo $sector['id']; ?>" data-name="<?php echo htmlspecialchars($sector['name']); ?>" data-budget="<?php echo $sector['budget']; ?>"></i>
                                    <!-- Delete Icon with data-id for identifying the sector to delete -->
                                    <i class="fas fa-trash text-danger" title="Delete" data-id="<?php echo $sector['id']; ?>"></i>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const budgetAmountElement = document.getElementById("budget-amount");

            // Add Sector Button Event
            document.querySelector('.add-sector-btn').addEventListener('click', function() {
                Swal.fire({
                    title: 'Add New Sector',
                    html: `
            <input type="text" id="sector-name" class="swal2-input" placeholder="Sector Name">
        `,
                    showCancelButton: true,
                    confirmButtonText: 'Add',
                    preConfirm: () => {
                        const name = document.getElementById('sector-name').value.trim();

                        if (!name) {
                            Swal.showValidationMessage('Please provide a valid sector name!');
                            return false;
                        }

                        // Set budget to 0 for new sectors
                        return {
                            name,
                            budget: 0
                        };
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        const {
                            name,
                            budget
                        } = result.value;

                        // Send AJAX request to add the sector with budget set to 0
                        fetch('src/process/add_sector.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                },
                                body: JSON.stringify({
                                    name: name,
                                    budget: budget
                                }),
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    Swal.fire('Success!', 'Sector has been added.', 'success').then(() => {
                                        location.reload(); // Reload page to show the updated table
                                    });
                                } else {
                                    Swal.fire('Error!', data.message || 'Failed to add sector.', 'error');
                                }
                            })
                            .catch((error) => {
                                console.error(error);
                                Swal.fire('Error!', 'Something went wrong. Please try again later.', 'error');
                            });
                    }
                });
            });

            // Delete Sector Button Event
            document.querySelectorAll('.fa-trash').forEach(function(deleteIcon) {
                deleteIcon.addEventListener('click', function() {
                    const sectorId = this.getAttribute('data-id'); // Get sector ID from data-id attribute

                    Swal.fire({
                        title: 'Are you sure?',
                        text: "You won't be able to revert this!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Yes, delete it!',
                        cancelButtonText: 'Cancel'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Send AJAX request to delete the sector
                            fetch('src/process/delete_sector.php', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                    },
                                    body: JSON.stringify({
                                        id: sectorId
                                    }),
                                })
                                .then(response => response.json())
                                .then(data => {
                                    if (data.success) {
                                        Swal.fire('Deleted!', 'The sector has been deleted.', 'success').then(() => {
                                            location.reload(); // Reload page to show the updated table
                                        });
                                    } else {
                                        Swal.fire('Error!', data.message || 'Failed to delete sector.', 'error');
                                    }
                                })
                                .catch(() => {
                                    Swal.fire('Error!', 'Something went wrong. Please try again later.', 'error');
                                });
                        }
                    });
                });
            });

            // Search Functionality
            document.getElementById('search-bar').addEventListener('input', function() {
                const searchQuery = this.value.toLowerCase();
                const rows = document.querySelectorAll('#sector-table tr');

                rows.forEach(row => {
                    const sectorName = row.cells[0].textContent.toLowerCase();
                    if (sectorName.includes(searchQuery)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            });

            // Edit Functionality
            document.querySelectorAll('.edit-btn').forEach(function(editIcon) {
                editIcon.addEventListener('click', function() {
                    const sectorId = this.getAttribute('data-id'); // Get sector ID
                    const sectorName = this.getAttribute('data-name'); // Get sector name
                    const sectorBudget = this.getAttribute('data-budget'); // Get sector budget

                    // Show Swal modal for editing sector
                    Swal.fire({
                        title: 'Edit Sector',
                        html: `
                <input type="text" id="edit-sector-name" class="swal2-input" value="${sectorName}" placeholder="Sector Name">
                <input type="number" id="edit-sector-budget" class="swal2-input" value="${sectorBudget}" placeholder="Budget">
            `,
                        showCancelButton: true,
                        confirmButtonText: 'Update',
                        preConfirm: () => {
                            const name = document.getElementById('edit-sector-name').value.trim();
                            const budget = document.getElementById('edit-sector-budget').value.trim();

                            if (!name || !budget) {
                                Swal.showValidationMessage('Please provide valid inputs!');
                                return false;
                            }

                            return {
                                id: sectorId,
                                name: name,
                                budget: budget
                            };
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            const {
                                id,
                                name,
                                budget
                            } = result.value;

                            // Send AJAX request to update sector name and budget
                            fetch('src/process/edit_sector.php', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                    },
                                    body: JSON.stringify({
                                        id,
                                        name,
                                        budget
                                    }),
                                })
                                .then(response => response.json())
                                .then(data => {
                                    if (data.success) {
                                        Swal.fire('Success!', 'Sector details have been updated.', 'success').then(() => {
                                            location.reload(); // Reload page to reflect changes
                                        });
                                    } else {
                                        Swal.fire('Error!', data.message || 'Failed to update sector.', 'error');
                                    }
                                })
                                .catch(() => {
                                    Swal.fire('Error!', 'Something went wrong. Please try again later.', 'error');
                                });
                        }
                    });
                });
            });

        });
    </script>
</body>

</html>