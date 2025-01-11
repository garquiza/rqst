<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

require_once '../admin/src/config/database.php';

// Fetch the access dates
$accessDateQuery = "SELECT start_date, end_date FROM access_dates WHERE id = 1";
$accessDateResult = mysqli_query($conn, $accessDateQuery);
$access_dates = null;

if ($accessDateResult && mysqli_num_rows($accessDateResult) > 0) {
    // Fetch the access dates from the database
    $access_dates = mysqli_fetch_assoc($accessDateResult);
} else {
    // Handle case where access dates are not found (optional fallback)
    $access_dates = null;
}

// Get the current and next year
$currentYear = date("Y");
$nextYear = $currentYear + 1; // Get next year

// Fetch distinct years for dropdown
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

// Fetch the PPMP list
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
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - PPMP List</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../admin/src/css/dashboard.css">
    <link rel="stylesheet" href="../admin/src/css/pr.css">
    <link rel="stylesheet" href="src/css/ppmp_list.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <?php include '../admin/sidebar.php'; ?>

    <div class="content animate__animated animate__fadeIn">
        <div class="header-card mb-4">
            <h1 class="display-5 mb-2">PPMP List (Admin)</h1>
            <p class="text-light">Manage, track, and approve PPMPs as an administrator.</p>
            <strong>
                <p class="text-light">
                    Access Date:
                    <?php
                    // Assuming $access_dates is fetched and contains 'start_date' and 'end_date'
                    echo date("F j, Y", strtotime($access_dates['start_date'])) . " - " . date("F j, Y", strtotime($access_dates['end_date']));
                    ?>
                </p>
            </strong>
            <style>
                .custom-border-btn {
                    border: 1px solid rgb(18, 44, 65);
                    background-color: rgb(138, 33, 40);
                    transition: transform 0.3s ease, border-color 0.3s ease;
                    /* Smooth transition */
                    /* Blue border */
                }

                .custom-border-btn:hover {
                    transform: scale(1.1);
                    background-color: rgb(138, 33, 40);
                    /* Make the button 10% bigger */
                }
            </style>

            <button class="btn btn-primary custom-border-btn" data-bs-toggle="modal" data-bs-target="#accessDateModal">Set Access Dates</button>
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

                                <a href="src/process/download_excel_ppmp.php?ppmp_id=<?php echo $row['ppmp_id']; ?>" class="btn btn-outline-primary btn-sm" title="Download PPMP" style="margin-right: 5px;">
                                    <i class="fas fa-download"></i>
                                </a>

                                <?php if ($createdYear == $previousYear): ?>
                                    <button class="btn btn-outline-warning btn-sm" title="Update PPMP" disabled>
                                        <i class="fas fa-edit"></i>
                                    </button>

                                <?php else: ?>
                                    <a href="../admin/edit_ppmp.php?ppmp_id=<?php echo $row['ppmp_id']; ?>" class="btn btn-outline-warning btn-sm" title="Update PPMP" style="margin-right: 5px;">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                <?php endif; ?>

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
    <!-- Modal for Access Dates -->
    <div class="modal fade" id="accessDateModal" tabindex="-1" aria-labelledby="accessDateModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="accessDateModalLabel">Set Access Dates</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="accessDateForm">
                        <div class="mb-3">
                            <label for="startDate" class="form-label">Start Date</label>
                            <input type="date" class="form-control" id="startDate" required>
                        </div>
                        <div class="mb-3">
                            <label for="endDate" class="form-label">End Date</label>
                            <input type="date" class="form-control" id="endDate" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Save Dates</button>
                    </form>
                </div>
            </div>
        </div>
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

        // Function to perform search
        function performSearch() {
            const searchTerm = document.getElementById('search-bar').value.toLowerCase();
            const tableRows = document.querySelectorAll('#ppmp-table tr');

            tableRows.forEach(row => {
                const title = row.children[0].textContent.toLowerCase();
                const approver = row.children[1].textContent.toLowerCase();
                // Check if search term matches title or approver
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
            fetch('../admin/src/process/delete_ppmp.php?ppmp_id=' + ppmpId)
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
                    window.location.href = '../admin/src/process/update_ppmp.php?approve=true&ppmp_id=' + ppmp_id;
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
                    window.location.href = '../admin/src/process/update_ppmp.php?reject=true&ppmp_id=' + ppmp_id;
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

        document.getElementById('accessDateForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const startDate = document.getElementById('startDate').value;
            const endDate = document.getElementById('endDate').value;

            console.log("Start Date: ", startDate); // Check the values before sending
            console.log("End Date: ", endDate);

            fetch('../admin/src/process/update_access_dates.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        start_date: startDate,
                        end_date: endDate
                    })
                })
                .then(response => {
                    console.log('Response from server:', response); // Log the response
                    return response.json(); // Return JSON response to be processed
                })
                .then(data => {
                    console.log('Response data:', data); // Check the response data

                    if (data.success) {
                        Swal.fire('Success', 'Access dates updated successfully', 'success')
                            .then(() => {
                                location.reload(); // Reload the page after "OK" is clicked
                            });

                        // Check if element exists before updating it
                        const accessDatesElement = document.getElementById('access-dates');
                        if (accessDatesElement) {
                            accessDatesElement.textContent = `Access Date: ${startDate} - ${endDate}`;
                        } else {
                            console.error("Element with id 'access-dates' not found.");
                        }

                        // Close modal
                        const modal = bootstrap.Modal.getInstance(document.getElementById('accessDateModal'));
                        modal.hide();
                    } else {
                        Swal.fire('Error', 'Failed to update access dates: ' + data.error, 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire('Error', 'Something went wrong, please try again later.', 'error');
                });
        });
    </script>
</body>

</html>