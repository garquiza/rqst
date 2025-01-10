<?php
session_start();

// Redirect to login page if the user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Include database connection
include('src/config/database.php');

// Fetch PPMP data from the database
$query = "SELECT ppmp_id, project_title, approver, date_created, status FROM ppmp_list";
$result = mysqli_query($conn, $query);

// Check if there are any results
if (!$result) {
    die("Error fetching data: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PPMP List</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="src/css/dashboard.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="src/css/ppmp_list.css">
</head>

<body>
    <?php include 'sidebar.php'; ?>

    <div class="content animate__animated animate__fadeIn">
        <div class="header-card mb-4">
            <h1 class="display-5 mb-2">PPMP List</h1>
            <p class="text-light">Manage and track the status of PPMPs.</p>
        </div>

        <div class="table-container">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <span class="total-number">Total Number: <?php echo mysqli_num_rows($result); ?></span>
                </div>
                <div class="status-dropdown">
                    <select class="form-select" id="status-filter" style="width: 200px;">
                        <option value="all">All</option>
                        <option value="approved">Approved</option>
                        <option value="pending">Pending</option>
                        <option value="rejected">Rejected</option>
                    </select>
                </div>
                <div>
                    <a href="create_ppmp.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Create PPMP
                    </a>
                </div>
            </div>

            <div class="search-container">
                <input type="text" class="form-control" id="search-bar" placeholder="Search by Title or Approver">
                <button class="btn btn-primary" onclick="performSearch()"><i class="fas fa-search"></i></button>
            </div>

            <table class="table table-striped table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Title</th>
                        <th>Approver</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="ppmp-table">
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <tr data-status="<?php echo $row['status']; ?>">
                            <td><?php echo htmlspecialchars($row['project_title']); ?></td>
                            <td><?php echo htmlspecialchars($row['approver']); ?></td>
                            <td><?php echo date("F j, Y", strtotime($row['date_created'])); ?></td>
                            <td>
                                <!-- Status Badge -->
                                <?php
                                $statusClass = '';
                                switch ($row['status']) {
                                    case 'approved':
                                        $statusClass = 'badge-approved';
                                        break;
                                    case 'pending':
                                        $statusClass = 'badge-pending text-dark';
                                        break;
                                    case 'rejected':
                                        $statusClass = 'badge-rejected';
                                        break;
                                }
                                ?>
                                <span class="badge <?php echo $statusClass; ?>"><?php echo ucfirst($row['status']); ?></span>
                            </td>
                            <td class="text-justify">
                                <div class="btn-group" role="group" aria-label="Actions">
                                <a href="src/process/download_excel_ppmp.php?ppmp_id=<?php echo $row['ppmp_id']; ?>"
                                        class="btn btn-outline-primary btn-sm"
                                        title="Download PPMP"
                                        style="margin-right: 5px;">
                                        <i class="fas fa-download"></i>
                                    </a>
                                    <a href="print_ppmp.php?ppmp_id=<?php echo $row['ppmp_id']; ?>" class="btn btn-outline-info btn-sm" title="Print PPMP" style="margin-right: 5px;">
                                        <i class="fas fa-print"></i>
                                    </a>
                                    <a href="update_ppmp.php?ppmp_id=<?php echo $row['ppmp_id']; ?>" class="btn btn-outline-warning btn-sm" title="Edit PPMP" style="margin-right: 5px;">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button class="btn btn-outline-danger btn-sm" title="Delete PPMP" onclick="confirmDelete('<?php echo $row['ppmp_id']; ?>')">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const filterDropdown = document.getElementById('status-filter');
        const tableRows = document.querySelectorAll('#ppmp-table tr');

        // Filter table rows based on the selected status
        filterDropdown.addEventListener('change', () => {
            const selectedStatus = filterDropdown.value;
            tableRows.forEach(row => {
                const status = row.getAttribute('data-status');
                row.style.display = (selectedStatus === 'all' || selectedStatus === status) ? '' : 'none';
            });
        });

        // Search functionality
        function performSearch() {
            const searchTerm = document.getElementById('search-bar').value.toLowerCase();
            tableRows.forEach(row => {
                const title = row.children[0].textContent.toLowerCase();
                const approver = row.children[1].textContent.toLowerCase();
                row.style.display = (title.includes(searchTerm) || approver.includes(searchTerm)) ? '' : 'none';
            });
        }
    </script>

    <script>
        function confirmDelete(ppmpId) {
            Swal.fire({
                title: 'Are you sure?',
                text: 'You will not be able to recover this PPMP!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!',
            }).then((result) => {
                if (result.isConfirmed) {
                    // Proceed with the deletion
                    deletePPMP(ppmpId);
                }
            });
        }

        function deletePPMP(ppmpId) {
            // Use AJAX to send the delete request to the server
            fetch('src/process/delete_ppmp.php?ppmp_id=' + ppmpId)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            title: 'Deleted!',
                            text: 'The PPMP has been deleted successfully.',
                            icon: 'success',
                            confirmButtonText: 'Okay',
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            title: 'Error!',
                            text: 'There was an issue deleting the PPMP.',
                            icon: 'error',
                            confirmButtonText: 'Okay',
                        });
                    }
                })
                .catch(error => {
                    Swal.fire({
                        title: 'Error!',
                        text: 'Something went wrong, please try again later.',
                        icon: 'error',
                        confirmButtonText: 'Okay',
                    });
                });
        }
    </script>

</body>

</html>