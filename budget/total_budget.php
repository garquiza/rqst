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
                        </tr>
                    </thead>
                    <tbody id="sector-table">
                        <?php foreach ($sectors as $sector): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($sector['name']); ?></td>
                                <td>₱ <?php echo number_format($sector['budget'], 2); ?></td>
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

            budgetAmountElement.addEventListener("click", function() {
                Swal.fire({
                    title: "Update Budget Amount",
                    input: "number",
                    inputAttributes: {
                        step: "0.01",
                        min: "0",
                        placeholder: "Enter new budget amount"
                    },
                    showCancelButton: true,
                    confirmButtonText: "Update",
                    preConfirm: (value) => {
                        if (!value || isNaN(value) || parseFloat(value) <= 0) {
                            Swal.showValidationMessage("Please enter a valid budget amount!");
                            return false;
                        }
                        return value;
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        const newBudget = result.value;

                        // Send AJAX request to update the budget
                        fetch('src/process/update_budget.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                },
                                body: JSON.stringify({
                                    budget: newBudget
                                }),
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    Swal.fire("Success!", "Budget amount has been updated.", "success").then(() => {
                                        // Update the text on the page with the new budget amount
                                        budgetAmountElement.textContent = `₱ ${parseFloat(newBudget).toLocaleString(undefined, {
                                minimumFractionDigits: 2
                            })}`;
                                        // Reload the page to show updated budget
                                        location.reload();
                                    });
                                } else {
                                    Swal.fire("Error!", data.message || "Failed to update budget.", "error");
                                }
                            })
                            .catch(() => {
                                Swal.fire("Error!", "Something went wrong. Please try again later.", "error");
                            });
                    }
                });
            });
            // Search Functionality
            document.getElementById('search-bar').addEventListener('input', function() {
                const searchQuery = this.value.toLowerCase();
                const rows = document.querySelectorAll('#sector-table tr');

                rows.forEach(row => {
                    const sectorName = row.cells[1].textContent.toLowerCase();
                    if (sectorName.includes(searchQuery)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            });


        });
    </script>
</body>

</html>