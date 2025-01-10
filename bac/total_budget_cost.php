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
                            ₱ <?php echo number_format($latestBudget, 2); ?>
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
    <script>
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
    </script>
</body>

</html>