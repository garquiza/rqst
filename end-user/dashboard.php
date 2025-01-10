<?php
session_start();

// Redirect to login page if the user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch user details from session
$user_name = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'User';

// Include database connection
include('src/config/database.php');

// Fetch total PR count, approved count, and rejected count from the database
$sqlTotal = "SELECT COUNT(*) AS total FROM purchase_requests WHERE end_user_id = ?";
$sqlApproved = "SELECT COUNT(*) AS approved FROM purchase_requests WHERE status = 'Approved' AND end_user_id = ?";
$sqlRejected = "SELECT COUNT(*) AS rejected FROM purchase_requests WHERE status = 'Rejected' AND end_user_id = ?";

// Prepare and execute the queries
$stmtTotal = $conn->prepare($sqlTotal);
$stmtTotal->bind_param("i", $_SESSION['user_id']);
$stmtTotal->execute();
$resultTotal = $stmtTotal->get_result();
$totalRequests = $resultTotal->fetch_assoc()['total'];

$stmtApproved = $conn->prepare($sqlApproved);
$stmtApproved->bind_param("i", $_SESSION['user_id']);
$stmtApproved->execute();
$resultApproved = $stmtApproved->get_result();
$approvedRequests = $resultApproved->fetch_assoc()['approved'];

$stmtRejected = $conn->prepare($sqlRejected);
$stmtRejected->bind_param("i", $_SESSION['user_id']);
$stmtRejected->execute();
$resultRejected = $stmtRejected->get_result();
$rejectedRequests = $resultRejected->fetch_assoc()['rejected'];

// Fetch the submitted PR dates and count for the calendar
$sqlPRDates = "SELECT DATE(submitted_date) AS pr_date, COUNT(*) AS pr_count FROM purchase_requests WHERE end_user_id = ? GROUP BY DATE(submitted_date)";
$stmtPRDates = $conn->prepare($sqlPRDates);
$stmtPRDates->bind_param("i", $_SESSION['user_id']);
$stmtPRDates->execute();
$resultPRDates = $stmtPRDates->get_result();

// Prepare the events for FullCalendar
$events = [];
while ($row = $resultPRDates->fetch_assoc()) {
    $events[] = [
        'title' => 'PRs: ' . $row['pr_count'],  // Number of PRs for that date
        'start' => $row['pr_date'],  // The date the PR was submitted
        'description' => $row['pr_count'] . ' Purchase Requests submitted.',  // Description for the event
    ];
}

// Fetch the monthly PR counts from the database
$sqlMonthlyRequests = "SELECT YEAR(submitted_date) AS year, MONTH(submitted_date) AS month, COUNT(*) AS pr_count
                       FROM purchase_requests 
                       WHERE end_user_id = ? 
                       GROUP BY YEAR(submitted_date), MONTH(submitted_date)
                       ORDER BY YEAR(submitted_date) DESC, MONTH(submitted_date) DESC";

$stmtMonthlyRequests = $conn->prepare($sqlMonthlyRequests);
$stmtMonthlyRequests->bind_param("i", $_SESSION['user_id']);
$stmtMonthlyRequests->execute();
$resultMonthlyRequests = $stmtMonthlyRequests->get_result();

// Prepare the data for the chart
$monthlyLabels = [];
$monthlyData = [];
while ($row = $resultMonthlyRequests->fetch_assoc()) {
    $monthYear = $row['month'] . '-' . $row['year'];  // Format: MM-YYYY
    $monthlyLabels[] = $monthYear;
    $monthlyData[] = $row['pr_count'];
}

// Close the prepared statement
$stmtMonthlyRequests->close();


// Close the database connection
$stmtTotal->close();
$stmtApproved->close();
$stmtRejected->close();
$stmtPRDates->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>End User Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <!-- FullCalendar CSS -->
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.2/main.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="src/css/dashboard.css">
</head>

<body>
    <div class="d-flex">
        <!-- Sidebar -->
        <?php include 'sidebar.php'; ?>

        <!-- Main Content -->
        <div class="content flex-grow-1 animate__animated animate__fadeIn">
            <div class="header-card">
                <h1 class="mb-2">Welcome, <?php echo htmlspecialchars($user_name); ?>!</h1>
                <p class="mb-0">Here’s a quick overview of your activities and requests.</p>
            </div>

            <!-- Summary Card for Purchase Requests -->
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="card p-3">
                        <div class="card-body">
                            <h5 class="card-title">Total Requests</h5>
                            <p class="card-text text-info"><?php echo $totalRequests; ?> total requests made.</p>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card p-3">
                        <div class="card-body">
                            <h5 class="card-title">Approved</h5>
                            <p class="card-text text-success"><?php echo $approvedRequests; ?> requests have been approved.</p>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card p-3">
                        <div class="card-body">
                            <h5 class="card-title">Rejected</h5>
                            <p class="card-text text-danger"><?php echo $rejectedRequests; ?> requests were declined.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Chart and Calendar Section -->
            <div class="row g-3 mt-4">
                <!-- Left Column: Chart -->
                <div class="col-md-6">
                    <div class="card p-3">
                        <div class="card-body">
                            <h5 class="card-title">Monthly Requests Overview</h5>
                            <div class="chart-container">
                                <canvas id="monthlyChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column: FullCalendar -->
                <div class="col-md-6">
                    <div class="calendar-container">
                        <div id="calendar"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- FullCalendar JS -->
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.2/main.min.js"></script>

    <script>
        const ctx = document.getElementById('monthlyChart').getContext('2d');
        const monthlyChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($monthlyLabels); ?>,  // Dynamic month-year labels
                datasets: [{
                    label: 'Requests',
                    data: <?php echo json_encode($monthlyData); ?>,  // Dynamic request count data
                    borderColor: 'rgba(0, 123, 255, 1)',
                    backgroundColor: 'rgba(0, 123, 255, 0.2)',
                    fill: true,
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
                                return 'Requests: ' + tooltipItem.raw;
                            }
                        }
                    }
                },
            },
        });

        // FullCalendar Initialization
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                events: <?php echo json_encode($events); ?>, // PHP events array passed to FullCalendar
                eventClick: function(info) {
                    alert('Event: ' + info.event.title + '\n' + 'Description: ' + info.event.extendedProps.description);
                }
            });
            calendar.render();
        });
    </script>
</body>

</html>