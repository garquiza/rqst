<?php
// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Include database connection
require_once '../admin/src/config/database.php';

// Fetch history logs from the database
$logs_query = "SELECT * FROM history_logs ORDER BY created_at DESC";
$logs_result = $conn->query($logs_query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Transaction History</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="src/css/pr.css">
    <style>
        .content {
            margin-left: 250px;
            padding: 20px;
        }

        .card {
            margin-bottom: 20px;
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
                <h1 class="mb-4">History Logs</h1>
                <p class="mb-0">List of all History logs, PPMP, and PR.</p>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <input type="text" id="searchBar" class="form-control" placeholder="Search logs or status (e.g., approved, rejected)...">
                    </div>
                    <div class="col-md-4">
                        <select id="sortDropdown" class="form-select">
                            <option value="">Sort by...</option>
                            <option value="date_desc">Date: Newest First</option>
                            <option value="date_asc">Date: Oldest First</option>
                            <option value="status_approved">Status: Approved</option>
                            <option value="status_rejected">Status: Rejected</option>
                        </select>
                    </div>
                </div>
            </div>
            <div id="logsContainer"></div>

            <div id="logsContainer">
                <?php if ($logs_result->num_rows > 0): ?>
                    <?php while ($log = $logs_result->fetch_assoc()): ?>
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($log['title']); ?></h5>
                                <p class="card-text"><?php echo htmlspecialchars($log['description']); ?></p>
                                <p class="card-text"><small class="text-muted">Created at: <?php echo $log['created_at']; ?></small></p>
                                <button class="btn btn-danger btn-sm delete-log" data-id="<?php echo $log['id']; ?>">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="alert alert-warning" role="alert">
                        No history logs found.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Search functionality
        document.getElementById('searchBar').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const logs = document.querySelectorAll('.card');
            logs.forEach(log => {
                const title = log.querySelector('.card-title').textContent.toLowerCase();
                const description = log.querySelector('.card-text').textContent.toLowerCase();
                if (title.includes(searchTerm) || description.includes(searchTerm)) {
                    log.style.display = '';
                } else {
                    log.style.display = 'none';
                }
            });
        });

        // Delete log functionality
        document.querySelectorAll('.delete-log').forEach(button => {
            button.addEventListener('click', function() {
                const logId = this.getAttribute('data-id');
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Make an AJAX request to delete the log
                        fetch('src/process/delete_log.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                },
                                body: JSON.stringify({
                                    id: logId
                                }),
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    Swal.fire(
                                        'Deleted!',
                                        'Your log has been deleted.',
                                        'success'
                                    );
                                    // Remove the log card from the DOM
                                    button.closest('.card').remove();
                                } else {
                                    Swal.fire(
                                        'Error!',
                                        'There was a problem deleting the log.',
                                        'error'
                                    );
                                }
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                Swal.fire(
                                    'Error!',
                                    'There was a problem deleting the log.',
                                    'error'
                                );
                            });
                    }
                });
            });
        });

        document.getElementById('searchBar').addEventListener('input', filterLogs);
        document.getElementById('sortDropdown').addEventListener('change', filterLogs);

        function filterLogs() {
            const searchTerm = document.getElementById('searchBar').value.toLowerCase();
            const sortOption = document.getElementById('sortDropdown').value;

            // Fetch logs dynamically using AJAX
            fetch('src/process/filter_logs.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        searchTerm,
                        sortOption
                    }),
                })
                .then(response => response.text())
                .then(data => {
                    document.getElementById('logsContainer').innerHTML = data;
                })
                .catch(error => console.error('Error:', error));
        }
    </script>
</body>

</html>