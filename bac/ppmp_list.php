<?php
// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Include database connection
require_once '../bac/src/config/database.php';

$currentYear = date("Y");
$nextYear = $currentYear + 1; // Get next year
$yearQuery = "
    SELECT DISTINCT YEAR(date_created) AS year FROM ppmp_list
    UNION
    SELECT DISTINCT date_bound AS year FROM ppmp_list
    ORDER BY year DESC
";
$yearResult = mysqli_query($conn, $yearQuery);
$years = [];
if ($yearResult) {
    while ($yearRow = mysqli_fetch_assoc($yearResult)) {
        $years[] = $yearRow['year'];
    }
}

$query = "
    SELECT 
        pl.ppmp_id, 
        pl.project_title, 
        pl.approver, 
        pl.date_created, 
        pl.date_bound, 
        pl.status,
        pf.schedule
    FROM 
        ppmp_list pl
    LEFT JOIN 
        ppmp_form pf 
    ON 
        pl.ppmp_id = pf.ppmp_id
";
$result = mysqli_query($conn, $query);
if (!$result) {
    die("Error fetching data: " . mysqli_error($conn));
}

// Check number of rows returned
if (mysqli_num_rows($result) == 0) {
    echo "<p>No PPMP entries found.</p>";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BAC - PPMP List</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../admin/src/css/dashboard.css">
    <link rel="stylesheet" href="../bac/src/css/pr.css">
    <link rel="stylesheet" href="src/css/ppmp_list.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <?php include '../bac/sidebar.php'; ?>

    <div class="content animate__animated animate__fadeIn">
        <div class="header-card mb-4">
            <h1 class="display-5 mb-2">PPMP List (BAC)</h1>
            <p class="text-light">Manage, track, and approve PPMPs as an BAC administrator.</p>
        </div>

        <div class="table-container">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <span class="total-number">Total Number: <?php echo mysqli_num_rows($result); ?></span>
                </div>

                <div class="year-dropdown">
                    <select class="form-select" id="year-filter" style="width: 200px;">
                        <option value="all">All</option>
                        <!-- Dropdown year logic -->
                        <option value="<?php echo $nextYear; ?>" <?php echo ($nextYear == date("Y") + 1) ? 'selected' : ''; ?>>
                            <?php echo $nextYear; ?>
                        </option>
                    </select>
                </div>

                <div class="status-dropdown">
                    <select class="form-select" id="status-filter" style="width: 200px;">
                        <option value="all">All</option>
                        <option value="approved">Approved</option>
                        <option value="pending">Pending</option>
                        <option value="rejected">Rejected</option>
                    </select>
                </div>
            </div>

            <div class="search-container mb-4">
                <input type="text" class="form-control" id="search-bar" placeholder="Search by Title or Approver">
            </div>

            <table class="table table-striped table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Title</th>
                        <th>Approver</th>
                        <th>Date Created</th>
                        <th>For Next Year</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="ppmp-table">
                    <?php
                    $previousYear = date("Y") - 1; // Define the previous year
                    while ($row = mysqli_fetch_assoc($result)):
                        // Decode schedule from JSON and format it
                        $schedule = json_decode($row['schedule'], true); // Decode JSON to array
                        $formattedSchedule = [];

                        // Parse and format schedule dates
                        if ($schedule) {
                            foreach ($schedule as $monthYear) {
                                $formattedSchedule[] = $monthYear; // Assume values are already in "Month YYYY" format
                            }
                        }

                        // Join all schedule entries for display
                        $displaySchedule = implode(", ", $formattedSchedule);

                        // Determine the appropriate status class
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

                        // Determine the year logic for filtering and button behavior
                        $createdYear = date("Y", strtotime($row['date_created']));
                        $boundYear = $row['date_bound'] ? $row['date_bound'] : $createdYear + 1; // Automatically set to the next year if 'date_bound' is null

                        // Check if the project has been approved or rejected
                        $approverStatus = ucfirst($row['status']);  // Default to current status
                    ?>
                        <tr
                            data-status="<?php echo $row['status']; ?>"
                            data-created-year="<?php echo $createdYear; ?>"
                            data-bound-year="<?php echo $boundYear; ?>">
                            <td><?php echo htmlspecialchars($row['project_title']); ?></td>
                            <td><?php echo htmlspecialchars($row['approver']); ?></td>
                            <td><?php echo date("F j, Y", strtotime($row['date_created'])); ?></td>
                            <td><?php echo $displaySchedule ?: "N/A"; ?></td> <!-- Display "For Year" -->
                            <td>
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

                                    <button class="btn btn-outline-info btn-sm" title="Print PPMP" style="margin-right: 5px;" onclick="printPPMP('<?php echo $row['ppmp_id']; ?>')">
                                        <i class="fas fa-print"></i>
                                    </button>

                                    <a href="../bac/edit_ppmp.php?ppmp_id=<?php echo $row['ppmp_id']; ?>" class="btn btn-outline-warning btn-sm" title="Update PPMP" style="margin-right: 5px;">
                                        <i class="fas fa-edit"></i>
                                    </a>

                                    <button class="btn btn-outline-danger btn-sm" title="Delete PPMP" onclick="confirmDelete('<?php echo $row['ppmp_id']; ?>')">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>

                                    <button class="btn btn-outline-success btn-sm" title="Approve PPMP" onclick="confirmApprove('<?php echo $row['ppmp_id']; ?>')">
                                        <i class="fas fa-check-circle"></i> Approve
                                    </button>

                                    <button class="btn btn-outline-danger btn-sm" title="Reject PPMP" onclick="confirmReject('<?php echo $row['ppmp_id']; ?>')">
                                        <i class="fas fa-times-circle"></i> Reject
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

        filterDropdown.addEventListener('change', () => {
            const selectedStatus = filterDropdown.value;
            tableRows.forEach(row => {
                const status = row.getAttribute('data-status');
                row.style.display = (selectedStatus === 'all' || selectedStatus === status) ? '' : 'none';
            });
        });

        function performSearch() {
            const searchTerm = document.getElementById('search-bar').value.toLowerCase();
            tableRows.forEach(row => {
                const title = row.children[0].textContent.toLowerCase();
                const approver = row.children[1].textContent.toLowerCase();
                row.style.display = (title.includes(searchTerm) || approver.includes(searchTerm)) ? '' : 'none';
            });
        }
        // Attach the performSearch function to the search bar's input event
        document.getElementById('search-bar').addEventListener('input', performSearch);

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
                    deletePPMP(ppmpId);
                }
            });
        }

        function deletePPMP(ppmpId) {
            fetch('../bac/src/process/delete_ppmp.php?ppmp_id=' + ppmpId)
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

        function confirmApprove(ppmp_id) {
            Swal.fire({
                title: 'Are you sure?',
                text: "You are about to approve this PPMP!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, approve it!',
                cancelButtonText: 'No, cancel!',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    // Redirect to approve the PPMP
                    window.location.href = '../bac/src/process/update_ppmp.php?approve=true&ppmp_id=' + ppmp_id;
                }
            });
        }

        function confirmReject(ppmp_id) {
            Swal.fire({
                title: 'Are you sure?',
                text: "You are about to reject this PPMP!",
                icon: 'error',
                showCancelButton: true,
                confirmButtonText: 'Yes, reject it!',
                cancelButtonText: 'No, cancel!',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    // Redirect to reject the PPMP
                    window.location.href = '../bac/src/process/update_ppmp.php?reject=true&ppmp_id=' + ppmp_id;
                }
            });
        }

        document.getElementById('year-filter').addEventListener('change', () => {
            const selectedYear = document.getElementById('year-filter').value;
            const tableRows = document.querySelectorAll('#ppmp-table tr');

            tableRows.forEach(row => {
                const createdYear = row.getAttribute('data-created-year');
                const boundYear = row.getAttribute('data-bound-year');
                row.style.display = (selectedYear === 'all' || selectedYear === createdYear || selectedYear === boundYear) ? '' : 'none';
            });
        });
    </script>
</body>

</html>