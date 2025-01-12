<?php
session_start();

// Redirect to login page if the user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Include database connection
include('src/config/database.php');
$accessQuery = "SELECT start_date, end_date FROM access_dates WHERE id = 1";  // Renaming the query variable
$accessResult = mysqli_query($conn, $accessQuery);
$accessDates = mysqli_fetch_assoc($accessResult);

// Check if access dates exist
if (!$accessDates) {
    die("Error fetching access dates: " . mysqli_error($conn));
}

// Convert dates for JavaScript comparison
$startDate = $accessDates['start_date'];
$endDate = $accessDates['end_date'];


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

// Fetch the ppmp_form_id from the ppmp_form table
$ppmpFormQuery = "SELECT ppmp_form_id FROM ppmp_form LIMIT 1"; // Fetch one row or adjust your query to fetch multiple
$ppmpFormResult = mysqli_query($conn, $ppmpFormQuery);

// Check if the query was successful and fetch the ppmp_form_id
if ($ppmpFormResult && mysqli_num_rows($ppmpFormResult) > 0) {
    $ppmpFormRow = mysqli_fetch_assoc($ppmpFormResult);
    $ppmpFormId = $ppmpFormRow['ppmp_form_id'];  // Declare the ppmp_form_id variable here
} else {
    $ppmpFormId = null; // Handle the case if no records are found
}


// Fetch the value of `updates_enabled` from the `settings` table
$updatesQuery = "SELECT updates_enabled FROM settings WHERE id = 1";
$updatesResult = mysqli_query($conn, $updatesQuery);
$updatesEnabled = mysqli_fetch_assoc($updatesResult)['updates_enabled'];
// Fetch PPMP data from the database, including ppmp_form_id by joining ppmp_list and ppmp_form
// Fetch PPMP data along with associated ppmp_form_id
$query = "
SELECT 
    ppmp_list.ppmp_id, 
    ppmp_list.project_title, 
    ppmp_list.approver, 
    ppmp_list.date_created, 
    ppmp_list.status, 
    ppmp_form.ppmp_form_id 
FROM ppmp_list
LEFT JOIN ppmp_form ON ppmp_list.ppmp_id = ppmp_form.ppmp_id
";
$result = mysqli_query($conn, $query);

// Check if query succeeded
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
            <!-- Display the Access Dates -->
            <div class="alert alert-info mt-3">
                <strong>Access Dates:</strong>
                <?php
                // Display the fetched access dates
                echo "From <strong>" . date("F j, Y", strtotime($startDate)) . "</strong> to <strong>" . date("F j, Y", strtotime($endDate)) . "</strong>";
                ?>
            </div>
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
                <div>
                    <a href="create_ppmp.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Create PPMP
                    </a>
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
                                    <?php if (!empty($row['ppmp_form_id'])): ?>
                                        <a href="update_ppmp.php?ppmp_form_id=<?php echo $row['ppmp_form_id']; ?>"
                                            class="btn btn-outline-warning btn-sm edit-btn"
                                            title="Edit PPMP">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">No Form Available</span>
                                    <?php endif; ?>

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
        // Pass PHP variables to JavaScript with renamed variables to avoid conflict
        const phpAccessStartDate = '<?php echo $startDate; ?>';
        const phpAccessEndDate = '<?php echo $endDate; ?>';

        document.addEventListener('DOMContentLoaded', function() {
            // Get today's date
            const today = new Date();

            // Parse the access dates from PHP (using renamed variables)
            const accessStart = new Date(phpAccessStartDate);
            const accessEnd = new Date(phpAccessEndDate);

            // Get the "Create PPMP" button element
            const createButton = document.querySelector('a[href="create_ppmp.php"]');

            // Event listener for when the "Create PPMP" button is clicked
            createButton.addEventListener('click', function(event) {
                // If today is outside the access dates
                if (today < accessStart || today > accessEnd) {
                    event.preventDefault(); // Prevent navigation to the "Create PPMP" page

                    // Show the "Access Denied" popup
                    Swal.fire({
                        title: 'Access Denied!',
                        text: 'You can only create PPMPs within the allowed access dates.',
                        icon: 'error',
                        confirmButtonText: 'Okay'
                    }).then(() => {
                        // After closing the popup, disable the "Create PPMP" button
                        createButton.style.pointerEvents = 'none'; // Disable button
                        createButton.style.opacity = '0.5'; // Make it appear disabled
                    });
                }
            });
        });
        const updatesEnabled = <?php echo $updatesEnabled; ?>;

        // Disable "Edit" buttons if updates_enabled is 0
        if (updatesEnabled === 0) {
            const editButtons = document.querySelectorAll('.edit-btn');
            editButtons.forEach(button => {
                button.classList.add('disabled');
                button.setAttribute('disabled', 'true');
            });
        }
    </script>
</body>

</html>