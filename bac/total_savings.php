<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

require_once '../admin/src/config/pdo.php';

// Sum the savings
$query = "SELECT SUM(savings) AS total_savings FROM fund";
$stmt = $pdo->prepare($query);
$stmt->execute();
$totalSavings = $stmt->fetch(PDO::FETCH_ASSOC)['total_savings'];

// Fetch funds from the database
$query = "SELECT * FROM fund";
$stmt = $pdo->prepare($query);
$stmt->execute();
$funds = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Total Savings</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="src/css/pr.css">
    <style>
        body {
            font-family: Arial, sans-serif;
        }

        .content {
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

        .add-fund-btn {
            float: right;
        }
    </style>
</head>

<body>
    <div class="d-flex">

        <?php include 'sidebar.php'; ?>

        <div class="content flex-grow-1 animate__animated animate__fadeIn">
            <div class="header-card">
                <h1 class="mb-2">Total Savings</h1>
                <p class="mb-0">See Savings.</p>
            </div>

            <!-- Total Savings Section -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card border-primary">
                        <div class="card-body">
                            <h5 class="card-title">Total Savings</h5>
                            <p class="card-text text-success fw-bold fs-4">₱ <?php echo number_format($totalSavings, 2); ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="table-section">
                <h5 class="mb-3 d-flex justify-content-between align-items-center">
                    Total Savings
                </h5>

                <!-- Table -->
                <table class="table table-striped table-hover table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>Fund Sources</th>
                            <th>Total ABC</th>
                            <th>Amount</th>
                            <th>Savings</th>
                        </tr>
                    </thead>
                    <tbody id="fund-table">
                        <?php foreach ($funds as $fund): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($fund['name']); ?></td>
                                <td>₱ <?php echo number_format($fund['totalabc'], 2); ?></td>
                                <td>₱ <?php echo number_format($fund['amount'], 2); ?></td>
                                <td>₱ <?php echo number_format($fund['savings'], 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <!-- Pie Chart Section -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Savings Distribution (Pie Chart)</h5>
                            <canvas id="pieChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Bar Chart Section -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Fund Amounts (Bar Chart)</h5>
                            <canvas id="barChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
            <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
            <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

            <script>
                document.addEventListener("DOMContentLoaded", function() {
                    // Pie Chart for Savings Distribution
                    const pieChartCtx = document.getElementById('pieChart').getContext('2d');
                    const pieChart = new Chart(pieChartCtx, {
                        type: 'pie',
                        data: {
                            labels: <?php echo json_encode(array_column($funds, 'name')); ?>,
                            datasets: [{
                                label: 'Savings Distribution',
                                data: <?php echo json_encode(array_column($funds, 'savings')); ?>,
                                backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#FF5733', '#33FF57'],
                                hoverOffset: 4
                            }]
                        },
                        options: {
                            responsive: true,
                            plugins: {
                                legend: {
                                    position: 'top',
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(tooltipItem) {
                                            return '₱ ' + tooltipItem.raw.toFixed(2);
                                        }
                                    }
                                }
                            }
                        }
                    });

                    // Bar Chart for Fund Amounts
                    const barChartCtx = document.getElementById('barChart').getContext('2d');
                    const barChart = new Chart(barChartCtx, {
                        type: 'bar',
                        data: {
                            labels: <?php echo json_encode(array_column($funds, 'name')); ?>,
                            datasets: [{
                                label: 'Fund Amount',
                                data: <?php echo json_encode(array_column($funds, 'amount')); ?>,
                                backgroundColor: '#007bff',
                                borderColor: '#0056b3',
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true,
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        callback: function(value) {
                                            return '₱ ' + value.toFixed(2);
                                        }
                                    }
                                }
                            },
                            plugins: {
                                legend: {
                                    display: false
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(tooltipItem) {
                                            return '₱ ' + tooltipItem.raw.toFixed(2);
                                        }
                                    }
                                }
                            }
                        }
                    });



                    // Delete Fund Button
                    document.querySelectorAll('.fa-trash').forEach(function(deleteIcon) {
                        deleteIcon.addEventListener('click', function() {
                            const fundId = this.getAttribute('data-id');

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
                                    fetch('src/process/delete_fund.php', {
                                            method: 'POST',
                                            headers: {
                                                'Content-Type': 'application/json',
                                            },
                                            body: JSON.stringify({
                                                id: fundId
                                            }),
                                        })
                                        .then(response => response.json())
                                        .then(data => {
                                            if (data.success) {
                                                Swal.fire('Deleted!', 'The fund has been deleted.', 'success').then(() => {
                                                    location.reload();
                                                });
                                            } else {
                                                Swal.fire('Error!', data.message || 'Failed to delete fund.', 'error');
                                            }
                                        })
                                        .catch(() => {
                                            Swal.fire('Error!', 'Something went wrong. Please try again later.', 'error');
                                        });
                                }
                            });
                        });
                    });

                    // Edit functionality
                    document.querySelectorAll('.edit-btn').forEach(function(editIcon) {
                        editIcon.addEventListener('click', function() {
                            const fundId = this.getAttribute('data-id');
                            const fundName = this.getAttribute('data-name');
                            const fundTotalabc = this.getAttribute('data-totalabc');
                            const fundAmount = this.getAttribute('data-amount');

                            // Modal for editing fund
                            Swal.fire({
                                title: 'Edit Fund',
                                html: `
                                <input type="text" id="edit-fund-name" class="swal2-input" value="${fundName}" placeholder="Fund Name">
                                <input type="number" step="0.01" id="edit-fund-totalabc" class="swal2-input" value="${fundTotalabc}" placeholder="Total ABC" oninput="calculateSavings()">
                                <input type="number" step="0.01" id="edit-fund-amount" class="swal2-input" value="${fundAmount}" placeholder="Amount" oninput="calculateSavings()">
                                `,
                                showCancelButton: true,
                                confirmButtonText: 'Update',
                                preConfirm: () => {
                                    const name = document.getElementById('edit-fund-name').value.trim();
                                    const totalabc = document.getElementById('edit-fund-totalabc').value.trim();
                                    const amount = document.getElementById('edit-fund-amount').value.trim();
                                    const savings = (parseFloat(totalabc) - parseFloat(amount)).toFixed(2); // Calculate savings dynamically

                                    // Validate inputs
                                    if (!name || !totalabc || !amount || isNaN(totalabc) || isNaN(amount) || parseFloat(totalabc) <= 0 || parseFloat(amount) <= 0 || parseFloat(savings) < 0) {
                                        Swal.showValidationMessage('Please provide valid fund name, total ABC, and amount!');
                                        return false;
                                    }

                                    return {
                                        id: fundId,
                                        name,
                                        totalabc,
                                        amount,
                                        savings
                                    };
                                }
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    const {
                                        id,
                                        name,
                                        totalabc,
                                        amount,
                                        savings
                                    } = result.value;

                                    // Send AJAX request to update the fund
                                    fetch('src/process/edit_fund.php', {
                                            method: 'POST',
                                            headers: {
                                                'Content-Type': 'application/json',
                                            },
                                            body: JSON.stringify({
                                                id,
                                                name,
                                                totalabc,
                                                amount,
                                                savings
                                            }),
                                        })
                                        .then(response => response.json())
                                        .then(data => {
                                            if (data.success) {
                                                Swal.fire('Success!', 'Fund has been updated.', 'success').then(() => {
                                                    location.reload();
                                                });
                                            } else {
                                                Swal.fire('Error!', data.message || 'Failed to update fund.', 'error');
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