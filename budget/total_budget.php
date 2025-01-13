<?php
// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Include database connection (adjust the path if necessary)
require_once '../budget/src/config/pdo.php';

// Fetch the latest budget amount from the budget_amount table
$query = "SELECT amount FROM budget_amount WHERE id = 1";
$stmt = $pdo->prepare($query);
$stmt->execute();
$latestBudget = $stmt->fetch(PDO::FETCH_ASSOC)['amount'] ?? 0;

// Fetch sectors from the database
$query = "SELECT * FROM sector";
$stmt = $pdo->prepare($query);
$stmt->execute();
$sectors = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch the value of 'id = 3' from the settings table
$query_settings = "SELECT updates_enabled FROM settings WHERE id = 3"; // Fetch updates_enabled for id = 3
$stmt_settings = $pdo->prepare($query_settings);
$stmt_settings->execute();
$settings_3 = $stmt_settings->fetch(PDO::FETCH_ASSOC); // Store result in a variable without conflict
$updatesEnabled = $settings_3['updates_enabled'] ?? 0; // Get the value of updates_enabled (0 or 1)
?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Total Budget Cost</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
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
    </style>
</head>

<body>
    <div class="d-flex">
        <!-- Include Sidebar -->
        <?php include 'sidebar.php'; ?>

        <!-- Main Content -->
        <div class="content flex-grow-1">
            <div class="header-card">
                <h1 class="mb-2">Total Budget Cost</h1>
                <p class="mb-0">Manage and allocate the budget effectively.</p>
                <div style="border: 2px solid #007bff; padding: 10px; margin-top: 10px; background-color: #e9f7fe; color: #007bff;">
                    <p class="mb-0">
                        Budget update is <?php echo $updatesEnabled == 1 ? 'Enabled' : 'Disabled'; ?>.
                    </p>
                </div>
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
                        <button id="update-total-budget" class="btn btn-primary" <?php echo $updatesEnabled == 0 ? 'disabled' : ''; ?>>Update Total Budget</button>
                    </div>
                </div>
            </div>

            <!-- Budget Per Sector Section -->
            <div class="table-section">
                <h5 class="mb-3 d-flex justify-content-between align-items-center">
                    Budget Per Sector
                </h5>

                <!-- Table -->
                <table class="table table-striped table-hover table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>Sector Name</th>
                            <th>Budget</th>
                            <th>Update</th>
                        </tr>
                    </thead>
                    <tbody id="sector-table">
                        <?php foreach ($sectors as $sector): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($sector['name']); ?></td>
                                <td>
                                    <input type="number" class="form-control sector-budget"
                                        data-sector-id="<?php echo $sector['id']; ?>"
                                        value="<?php echo number_format($sector['budget'], 2); ?>"
                                        step="0.01" min="0">
                                </td>
                                <td> <button class="btn btn-primary update-budget" data-sector-id="<?php echo $sector['id']; ?>" <?php echo $updatesEnabled == 0 ? 'disabled' : ''; ?>>
                                        Update
                                    </button></td>
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
            const updateTotalBudgetButton = document.getElementById("update-total-budget");
            let totalBudget = <?php echo $latestBudget; ?>;

            // Display the initial budget
            budgetAmountElement.textContent = `₱ ${totalBudget.toFixed(2)}`;

            // Update specific sector's budget when the "Update" button is clicked
            document.querySelectorAll('.update-budget').forEach(button => {
                button.addEventListener('click', function() {
                    const sectorId = this.getAttribute('data-sector-id');
                    const sectorInput = document.querySelector(`input[data-sector-id="${sectorId}"]`);
                    const newSectorBudget = parseFloat(sectorInput.value) || 0;

                    // Check if the remaining budget allows this update
                    const currentSectorBudget = parseFloat(sectorInput.getAttribute('data-original-budget')) || 0;
                    const budgetDifference = newSectorBudget - currentSectorBudget;
                    const remainingBudget = totalBudget - budgetDifference;

                    if (remainingBudget < 0) {
                        Swal.fire("Error!", "Not enough budget remaining to allocate this amount.", "error");
                        sectorInput.value = currentSectorBudget.toFixed(2); // Reset to the original value
                        return;
                    }

                    // Send AJAX request to update the sector's budget
                    fetch('src/process/update_sector_budget.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({
                                sector_id: sectorId,
                                budget: newSectorBudget,
                            }),
                        })
                        .then(response => response.json())
                        .then(data => {
                            console.log(data); // Debugging log
                            if (data.success) {
                                Swal.fire("Success!", "Sector budget updated.", "success").then(() => {
                                    // Update the remaining budget
                                    totalBudget = data.new_total_budget; // Use the new total budget returned from the PHP script
                                    console.log("Updated Total Budget:", totalBudget); // Debugging log
                                    budgetAmountElement.textContent = `₱ ${totalBudget.toFixed(2)}`;
                                    sectorInput.setAttribute('data-original-budget', newSectorBudget.toFixed(2));
                                });
                            } else {
                                Swal.fire("Error!", data.error || "Failed to update sector budget.", "error");
                            }
                        })
                        .catch(() => {
                            Swal.fire("Error!", "Something went wrong. Please try again later.", "error");
                        });

                });
            });

            updateTotalBudgetButton.addEventListener('click', function() {
                Swal.fire({
                    title: "Update Total Budget Amount",
                    input: "number",
                    inputAttributes: {
                        step: "0.01",
                        min: "0",
                        placeholder: "Enter new budget amount",
                    },
                    showCancelButton: true,
                    confirmButtonText: "Update",
                    preConfirm: (value) => {
                        if (!value || isNaN(value) || parseFloat(value) <= 0) {
                            Swal.showValidationMessage("Please enter a valid budget amount!");
                            return false;
                        }
                        return value;
                    },
                }).then((result) => {
                    if (result.isConfirmed) {
                        const newBudget = parseFloat(result.value);

                        // Send AJAX request to update the total budget
                        fetch('src/process/update_total_budget.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                },
                                body: JSON.stringify({
                                    budget: newBudget,
                                }),
                            })
                            .then(response => response.json())
                            .then(data => {
                                console.log(data); // Debugging log
                                if (data.success) {
                                    Swal.fire("Success!", "Total budget updated.", "success").then(() => {
                                        totalBudget = newBudget;
                                        budgetAmountElement.textContent = `₱ ${totalBudget.toLocaleString(undefined, {
                                    minimumFractionDigits: 2,
                                })}`;
                                    });
                                } else {
                                    Swal.fire("Error!", data.message || "Failed to update total budget.", "error");
                                }
                            })
                            .catch(() => {
                                Swal.fire("Error!", "Something went wrong. Please try again later.", "error");
                            });
                    }
                });
            });

        });
    </script>
</body>

</html>