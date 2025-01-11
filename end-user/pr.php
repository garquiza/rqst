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

// Count statuses
$status_query = "SELECT status, COUNT(*) as status_count FROM purchase_requests GROUP BY status";
$status_result = mysqli_query($conn, $status_query);

// Initialize counts
$pending_count = $approved_count = $rejected_count = 0;

while ($row = mysqli_fetch_assoc($status_result)) {
    if ($row['status'] == 'Pending') {
        $pending_count = $row['status_count'];
    } elseif ($row['status'] == 'Approved') {
        $approved_count = $row['status_count'];
    } elseif ($row['status'] == 'Rejected') {
        $rejected_count = $row['status_count'];
    }
}

// Check if PPMP List exists
$ppmp_check_query = "SELECT COUNT(*) as ppmp_count FROM ppmp_list WHERE status = 'approved'";
$ppmp_check_result = mysqli_query($conn, $ppmp_check_query);
$ppmp_count = mysqli_fetch_assoc($ppmp_check_result)['ppmp_count'];

// Pagination Logic
$limit = 10; // Max PRs per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Fetch purchase requests
$search = isset($_GET['search']) ? $_GET['search'] : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';

$query = "SELECT pr.*, pri.purpose 
          FROM purchase_requests pr
          LEFT JOIN purchase_request_items pri ON pr.pr_id = pri.pr_id
          WHERE (pr.pr_number LIKE '%$search%' OR pri.purpose LIKE '%$search%')";

if ($status_filter) {
    $query .= " AND pr.status = '$status_filter'";
}

$query .= " LIMIT $limit OFFSET $offset"; // Apply limit and offset for pagination
$result = mysqli_query($conn, $query);

// Count total records for pagination
$count_query = "SELECT COUNT(*) as total FROM purchase_requests pr
                LEFT JOIN purchase_request_items pri ON pr.pr_id = pri.pr_id
                WHERE (pr.pr_number LIKE '%$search%' OR pri.purpose LIKE '%$search%')";
if ($status_filter) {
    $count_query .= " AND pr.status = '$status_filter'";
}
$count_result = mysqli_query($conn, $count_query);
$total_rows = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_rows / $limit);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase Request List</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="src/css/dashboard.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <div class="d-flex">
        <!-- Sidebar -->
        <?php include 'sidebar.php'; ?>

        <!-- Main Content -->
        <div class="content flex-grow-1 animate__animated animate__fadeIn">
            <div class="header-card mb-4">
                <h1 class="display-5 mb-2">Purchase Request List</h1>
                <p class="text-light">Manage and track the status of your purchase requests.</p>
            </div>

            <!-- Status Summary Cards -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card text-center bg-light">
                        <div class="card-body">
                            <h5 class="card-title">Pending</h5>
                            <h2 class="card-text text-warning"><?php echo $pending_count; ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-center bg-light">
                        <div class="card-body">
                            <h5 class="card-title">Approved</h5>
                            <h2 class="card-text text-success"><?php echo $approved_count; ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-center bg-light">
                        <div class="card-body">
                            <h5 class="card-title">Rejected</h5>
                            <h2 class="card-text text-danger"><?php echo $rejected_count; ?></h2>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Search and Filter Bar -->
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
                <!-- Search Bar -->
                <form method="get" class="d-flex mb-2">
                    <input type="text" name="search" class="form-control me-2" placeholder="Search by PR Number or Purpose" value="<?php echo htmlspecialchars($search); ?>">
                    <button type="submit" class="btn btn-primary">Search</button>
                </form>

                <!-- Status and Year Filter Dropdown -->
                <form method="get" class="d-flex mb-2">
                                    <select name="year" class="form-select me-2" onchange="this.form.submit()">
                    <option value="">All Years</option>
                    <?php
                    // Get the current year
                    $current_year = date('Y');

                    // Fetch all available years from the database
                    $years_query = "SELECT DISTINCT YEAR(submitted_date) as year FROM purchase_requests ORDER BY year DESC";
                    $years_result = mysqli_query($conn, $years_query);

                    // Populate the year dropdown
                    while ($row = mysqli_fetch_assoc($years_result)):
                    ?>
                        <option value="<?php echo $row['year']; ?>" <?php if (isset($_GET['year']) && $_GET['year'] == $row['year']) echo 'selected'; ?>>
                            <?php echo $row['year']; ?>
                        </option>
                    <?php endwhile; ?>
                </select>

                <!-- Status Filter Dropdown -->
                <form method="get" class="d-flex mb-2">
                    <select name="status" class="form-select me-2" onchange="this.form.submit()">
                        <option value="">All Statuses</option>
                        <option value="approved" <?php if ($status_filter == 'approved') echo 'selected'; ?>>Approved</option>
                        <option value="rejected" <?php if ($status_filter == 'rejected') echo 'selected'; ?>>Rejected</option>
                        <option value="pending" <?php if ($status_filter == 'pending') echo 'selected'; ?>>Pending</option>
                    </select>
                    <button type="submit" class="btn btn-outline-primary">Filter</button>
                </form>

                <div>
                    <?php if ($ppmp_count > 0): ?>
                        <!-- Enabled Add PR Button -->
                        <a href="create_pr.php" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Add Purchase Request
                        </a>
                    <?php else: ?>
                        <!-- Disabled Add PR Button -->
                        <button class="btn btn-primary" disabled>
                            <i class="fas fa-plus"></i> Wait for PPMP Approval.
                        </button>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Purchase Request Table -->
            <div class="table-responsive mt-4">
                <table class="table table-hover table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>PR Number</th>
                            <th>Purpose</th>
                            <th>Approver</th>
                            <th>Submitted Date</th>
                            <th>Status</th>
                            <th>PR Process</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['pr_number']); ?></td>
                                <td><?php echo htmlspecialchars($row['purpose']); ?></td>
                                <td><?php echo htmlspecialchars($row['approver']); ?></td>
                                <td><?php echo date("F j, Y", strtotime($row['submitted_date'])); ?></td>
                                <td>
                                    <?php
                                    // Show the status of the request with dynamic colors
                                    switch ($row['status']) {
                                        case 'Approved':
                                            echo '<span class="badge bg-success">Approved</span>';
                                            break;
                                        case 'Rejected':
                                            echo '<span class="badge bg-danger">Rejected</span>';
                                            break;
                                        case 'Pending':
                                            echo '<span class="badge bg-warning text-dark">Pending</span>';
                                            break;
                                        default:
                                            echo '<span class="badge bg-secondary">Unknown</span>';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <?php
                                    // Display the PR Process link or 'Not Applicable'
                                    if ($row['status'] == 'approved' || $row['status'] == 'rejected') {
                                        echo '<a href="pr_process.php?pr_number=' . $row['pr_number'] . '" class="btn btn-outline-info btn-sm">View Process</a>';
                                    } else {
                                        echo 'Not Applicable';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <a href="download_pr.php?pr_number=<?php echo $row['pr_number']; ?>" class="btn btn-outline-primary btn-sm" title="Download PR">
                                        <i class="fas fa-download"></i>
                                    </a>
                                    <a href="update_pr.php?pr_number=<?php echo $row['pr_number']; ?>" class="btn btn-outline-warning btn-sm" title="Update PR">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button class="btn btn-outline-danger btn-sm" title="Delete PR" onclick="confirmDelete('<?php echo $row['pr_number']; ?>')">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                    <button class="btn btn-outline-success btn-sm" title="Approve PR" onclick="changeStatus('<?php echo $row['pr_number']; ?>', 'approve')">
                                        <i class="fas fa-check"></i>
                                    </button>
                                    <button class="btn btn-outline-danger btn-sm" title="Reject PR" onclick="changeStatus('<?php echo $row['pr_number']; ?>', 'reject')">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <nav aria-label="Page navigation">
                <ul class="pagination justify-content-center">
                    <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo $status_filter; ?>">Previous</a>
                        </li>
                    <?php endif; ?>
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?php if ($i == $page) echo 'active'; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo $status_filter; ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>
                    <?php if ($page < $total_pages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo $status_filter; ?>">Next</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    function confirmDelete(prNumber) {
        Swal.fire({
            title: 'Are you sure?',
            text: "This action will delete the purchase request permanently.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                // Perform AJAX call to delete the PR
                $.ajax({
                    url: 'src/process/delete_pr.php',
                    type: 'POST',
                    data: { pr_number: prNumber },
                    success: function(response) {
                        Swal.fire(
                            'Deleted!',
                            'The purchase request has been deleted.',
                            'success'
                        ).then(() => location.reload());
                    },
                    error: function() {
                        Swal.fire('Error!', 'An error occurred while deleting the PR.', 'error');
                    }
                });
            }
        });
    }
     // Function to change the process status
function changeStatus(pr_number, action) {
    Swal.fire({
        title: `Are you sure you want to ${action} this PR?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: `Yes, ${action}!`,
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            // Send request to the server to update the status
            fetch('src/process/update_process_status.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ pr_number, action })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        title: `${action}d!`,
                        text: `The purchase request has been ${action}d.`,
                        icon: 'success'
                    }).then(() => location.reload()); // Reload to update the table
                } else {
                    Swal.fire('Error!', 'There was an issue updating the status.', 'error');
                }
            })
            .catch(error => {
                Swal.fire('Error!', 'There was an error with the request.', 'error');
            });
        }
    });
}
    </script>
</body>

</html>
