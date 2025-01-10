<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php'); // Redirect to login if not authenticated
    exit;
}

// Include database connection
include '../budget/src/config/pdo.php';

// Fetch user details from the session
$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];
$user_email = $_SESSION['user_email'];

// Fetch total purchase requests
$stmt = $pdo->prepare("SELECT COUNT(*) FROM purchase_requests");
$stmt->execute();
$total_purchase_requests = $stmt->fetchColumn();

// Fetch total procured items
$stmt = $pdo->prepare("SELECT COUNT(*) FROM notice_of_award");
$stmt->execute();
$total_procured = $stmt->fetchColumn();

$totalRequests = 0;
$totalApproved = 0;
$totalRejected = 0;
$totalPending = 0;

$query = "SELECT status, COUNT(*) as count FROM purchase_requests GROUP BY status";
$stmt = $pdo->prepare($query);
$stmt->execute();
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($results as $row) {
    switch ($row['status']) {
        case 'Approved':
            $totalApproved = $row['count'];
            break;
        case 'Rejected':
            $totalRejected = $row['count'];
            break;
        case 'Pending':
            $totalPending = $row['count'];
            break;
    }
}

// Total requests is the sum of all statuses
$totalRequests = $totalApproved + $totalRejected + $totalPending;
// Fetch monthly purchase requests
$monthlyRequests = [];
$query = "SELECT MONTH(submitted_date) as month, COUNT(*) as count FROM purchase_requests GROUP BY MONTH(submitted_date)";
$stmt = $pdo->prepare($query);
$stmt->execute();
$monthlyResults = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Initialize the monthly requests array
for ($i = 1; $i <= 12; $i++) {
    $monthlyRequests[$i] = 0; // Default to 0 for each month
}

// Populate the monthly requests array
foreach ($monthlyResults as $row) {
    $monthlyRequests[$row['month']] = $row['count'];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - ReQuest</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link rel="stylesheet" href="src/css/pr.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .content {
            margin-left: 250px;
            padding: 20px;
        }

        .header-card {
            margin-bottom: 30px;
        }

        .card-section {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }

        .info-card {
            flex: 1;
            text-align: center;
            padding: 20px;
            background: #f8f9fa;
            border: 1px solid #ddd;
            margin: 0 10px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            cursor: pointer;
            transition: background 0.3s;
        }

        .info-card:hover {
            background: #e2e6ea;
        }

        .chart-container {
            margin: 0 auto;
            max-width: 600px;
        }

        canvas {
            width: 100% !important;
            height: auto !important;
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
                <h1 class="mb-4">Welcome, <?php echo htmlspecialchars($user_name); ?>!</h1>
                <p class="mb-0">You are logged in as: <?php echo htmlspecialchars($user_email); ?></p>
                <p class="mb-0">This is your dashboard where you can manage your purchase requests.</p>
            </div>

            <!-- Cards Section -->
            <div class="card-section">
                <div class="info-card" onclick="window.location.href='pr.php';">
                    <h2><strong><?php echo $total_purchase_requests; ?></strong></h2>
                    <p>PURCHASE REQUEST</p>
                </div>
                <div class="info-card" onclick="window.location.href='total_budget.php';">
                    <h2><strong>VIEW</strong></h2>
                    <p>TOTAL BUDGET COST</p>
                </div>
                <div class="info-card" onclick="window.location.href='total_savings.php';">
                    <h2><strong>VIEW</strong></h2>
                    <p>TOTAL SAVINGS</p>
                </div>
                <div class="info-card">
                    <h2><strong><?php echo $total_procured; ?></strong></h2>
                    <p>TOTAL PROCURED</p>
                </div>
            </div>

            <!-- Chart Section -->
            <div class="chart-container">
                <canvas id="pieChart"></canvas>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Pie Chart using Chart.js
        const ctx = document.getElementById('pieChart').getContext('2d');
        const pieChart = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: ['TOTAL PROCURED', 'TOTAL BUDGET COST', 'TOTAL SAVINGS'],
                datasets: [{
                    data: [<?php echo $total_procured; ?>, 30, 40], // Replace 30 and 40 with dynamic values if needed
                    backgroundColor: ['#1B2B54', '#72D1E1', '#3B5998'],
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                }
            }
        });
    </script>
</body>

</html>