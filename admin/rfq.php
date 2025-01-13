
<?php
// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Get project title from query parameter
$projectTitle = isset($_GET['project_title']) ? htmlspecialchars($_GET['project_title']) : 'No title provided';

// Include database connection
require_once '../admin/src/config/pdo.php';

// Fetch all approved projects from the database
$projectQuery = $pdo->prepare("SELECT DISTINCT project_title FROM ppmp_list");
$projectQuery->execute();
$approvedProjects = $projectQuery->fetchAll(PDO::FETCH_ASSOC);

// Fetch PRs for the project title from the database
// Fetch PRs for the project title from the database
$prQuery = $pdo->prepare("
    SELECT pr.pr_number, end_users.sector
    FROM purchase_requests AS pr
    INNER JOIN end_users ON pr.end_user_id = end_users.id
    INNER JOIN ppmp_list ON pr.ppmp_id = ppmp_list.ppmp_id
    WHERE ppmp_list.project_title = :projectTitle 
        AND pr.status = 'Approved'
        AND pr.pr_number NOT IN (SELECT pr_request_number FROM rfq)
");

$prQuery->execute(['projectTitle' => $projectTitle]);
$approvedPRs = $prQuery->fetchAll(PDO::FETCH_ASSOC);


// Fetch PR numbers already in the RFQ table
$rfqPRsQuery = $pdo->query("SELECT pr_request_number FROM rfq");
$rfqPRs = $rfqPRsQuery->fetchAll(PDO::FETCH_COLUMN);



// Fetch total ABC for the project title from the database
if (isset($_POST['project_title'])) {
    $project_title = $_POST['project_title'];
    $sql = "SELECT total_abc 
            FROM pmaf 
            WHERE project_title = ? 
            LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$project_title]);
    $total_abc = $stmt->fetchColumn(); // Get total_abc directly

    echo json_encode([
        'total_abc' => $total_abc ?: '0' // Return 0 if no value found
    ]);
}

?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RFQ - Procurement Request</title>

    <!-- CDN Links -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="src/css/pr.css">
</head>

<body>

    <div class="d-flex">
        <!-- Include Sidebar -->
        <?php include 'sidebar.php'; ?>

        <!-- Main Content -->
        <div class="content flex-grow-1 animate__animated animate__fadeIn">
            <form method="POST" action="save_rfq.php" id="rfqForm">
                <div class="header-card">
                    <h1 class="mb-4">Request for Quotation (RFQ)</h1>
                </div>

                <!-- Project Title Card -->
                <div class="card section-card mb-2">
                    <div class="card-header bg-primary text-white">
                        <strong>Project Title</strong>
                    </div>
                    <div class="card-body">
                        <select id="projectTitle" name="project_title" class="form-select">
                            <option value="">-- Select a Project Title --</option>
                            <?php foreach ($approvedProjects as $project): ?>
                                <option value="<?php echo htmlspecialchars($project['project_title']); ?>"
                                    <?php echo ($projectTitle === $project['project_title']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($project['project_title']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- Row 1: PR Request Number and End-User -->
                <div class="card section-card mb-2">
                    <div class="card-header bg-light">
                        <strong>Project Details</strong>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="prRequestNumber" class="form-label">PR Request Number</label>
                                <select id="prRequestNumber" name="pr_request_number" class="form-select">
                                    <option value="">-- Select PR Request Number --</option>
                                    <?php foreach ($approvedPRs as $pr): ?>
                                        <option value="<?php echo $pr['pr_number']; ?>" 
                                            data-enduser="<?php echo $pr['sector']; ?>"
                                            <?php echo in_array($pr['pr_number'], $rfqPRs) ? 'disabled' : ''; ?>>
                                            <?php echo $pr['pr_number']; ?> 
                                            <?php echo in_array($pr['pr_number'], $rfqPRs) ? '(Already in RFQ)' : ''; ?>
                                        </option>
                                    <?php endforeach; ?>

                                </select>
                            </div>

                            <!-- End-User Field -->
                            <div class="col-md-6 mb-3">
                                <label for="endUser" class="form-label">End-User</label>
                                <input type="text" id="endUser" name="end_user" class="form-control" placeholder="End User Name" readonly>
                            </div>

                        </div>
                    </div>
                </div>

                <!-- Row 2: Date Created and Deadline of Submission -->
                <div class="card section-card mb-2">
                    <div class="card-header bg-light">
                        <strong>Timeline Information</strong>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="dateCreated" class="form-label">Date Created</label>
                                <input type="date" id="dateCreated" name="date_created" class="form-control">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="deadlineSubmission" class="form-label">Deadline of Submission</label>
                                <input type="date" id="deadlineSubmission" name="deadline_submission" class="form-control">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Row 3: Approved Budget and Mode of Procurement -->
                <div class="card section-card mb-2">
                    <div class="card-header bg-light">
                        <strong>Budget and Procurement Details</strong>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="total-abc" class="form-label">Approved Budget for the Contract (ABC)</label>
                                <input type="number" id="total-abc" name="total_abc" class="form-control" placeholder="Auto-filled Total ABC" readonly>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="procurementMode" class="form-label">Mode of Procurement</label>
                                <input type="text" id="procurementMode" name="procurement_mode" class="form-control" placeholder="Enter Procurement Mode">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Row 4: Current Process Table -->
                <div class="card section-card mb-2">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                        <strong>Current Process</strong>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addRowModal">
                            <i class="fas fa-plus"></i> Add Row
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover" id="processTable">
                                <thead class="table-light">
                                    <tr>
                                        <th>No.</th>
                                        <th>Quantity</th>
                                        <th>Unit</th>
                                        <th>General Name of the Item</th>
                                        <th>Required Technical Specification</th>
                                        <th>Unit Cost</th>
                                        <th>Bidder's Offer Specification</th>
                                        <th>Quoted Unit Price (per item)</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody id="processTableBody">
                                    <!-- Sample rows will be dynamically added here -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end my-4">
                    <button type="button" id="saveButton" class="btn btn-success me-2"><i class="fas fa-save"></i> Save and Proceed</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal to Add Row -->
    <div class="modal fade" id="addRowModal" tabindex="-1" aria-labelledby="addRowModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addRowModalLabel">Add New Row</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addRowForm">
                        <div class="mb-3">
                            <label for="quantity" class="form-label">Quantity</label>
                            <input type="number" id="quantity" name="quantity" class="form-control" placeholder="Enter Quantity" required>
                        </div>
                        <div class="mb-3">
                            <label for="unit" class="form-label">Unit</label>
                            <input type="text" id="unit" name="unit" class="form-control" placeholder="Enter Unit" required>
                        </div>
                        <div class="mb-3">
                            <label for="generalName" class="form-label">General Name of the Item</label>
                            <input type="text" id="generalName" name="general_name" class="form-control" placeholder="Enter General Name" required>
                        </div>
                        <div class="mb-3">
                            <label for="techSpec" class="form-label">Required Technical Specification</label>
                            <input type="text" id="techSpec" name="tech_spec" class="form-control" placeholder="Enter Technical Specification" required>
                        </div>
                        <div class="mb-3">
                            <label for="unitCost" class="form-label">Unit Cost</label>
                            <input type="number" id="unitCost" name="unit_cost" class="form-control" placeholder="Enter Unit Cost" required>
                        </div>
                        <div class="mb-3">
                            <label for="bidOfferSpec" class="form-label">Bidder's Offer Specification</label>
                            <input type="text" id="bidOfferSpec" name="bid_offer" class="form-control" placeholder="Enter Bidder's Offer Specification" required>
                        </div>
                        <button type="submit" class="btn btn-primary mt-3 w-100">Add Row</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
<!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // Handle dynamic project title change
        document.getElementById('projectTitle').addEventListener('change', function() {
            const selectedProject = this.value;
            if (selectedProject) {
                window.location.href = `?project_title=${encodeURIComponent(selectedProject)}`;
            }
        });


        document.getElementById('prRequestNumber').addEventListener('change', function () {
            var selectedOption = this.options[this.selectedIndex];
            var projectTitle = selectedOption.getAttribute('data-projecttitle'); // Fetch project title associated with PR

            // AJAX request to fetch total_abc based on projectTitle
            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'pmf.php', true); // Make sure this path is correct
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            
            xhr.onload = function () {
                if (xhr.status === 200) {
                    var response = JSON.parse(xhr.responseText);
                    var totalAbc = response.total_abc || '0'; // Use the fetched total_abc or fallback to '0'
                    document.getElementById('total-abc').value = totalAbc; // Update the total_abc field
                }
            };
            
            xhr.send('project_title=' + encodeURIComponent(projectTitle));
        });




    </script>
    <script>

        document.getElementById('prRequestNumber').addEventListener('change', function () {
            var selectedOption = this.options[this.selectedIndex];
            var endUserName = selectedOption.getAttribute('data-enduser');
            document.getElementById('endUser').value = endUserName ? endUserName : '';
        });

        let rowCount = 0;

        

        document.getElementById('addRowForm').addEventListener('submit', function(event) {
            event.preventDefault();

            const quantity = document.getElementById('quantity').value;
            const unit = document.getElementById('unit').value;
            const generalName = document.getElementById('generalName').value;
            const techSpec = document.getElementById('techSpec').value;
            const unitCost = document.getElementById('unitCost').value;
            const bidOfferSpec = document.getElementById('bidOfferSpec').value;

            rowCount++;

            const newRow = `
        <tr id="row-${rowCount}">
            <td>${rowCount}</td>
            <td>${quantity}</td>
            <td>${unit}</td>
            <td>${generalName}</td>
            <td>${techSpec}</td>
            <td>₱${unitCost}</td>
            <td>${bidOfferSpec}</td>
            <td>₱${unitCost}</td>
            <td>
                <button class="btn btn-danger btn-sm remove-row" data-row="row-${rowCount}">Remove</button>
            </td>
        </tr>
    `;

            document.getElementById('processTableBody').insertAdjacentHTML('beforeend', newRow);

            // Show success alert
            Swal.fire({
                icon: 'success',
                title: 'Row Added',
                text: 'The row has been successfully added!',
            });

            // Close the modal
            const addRowModalEl = document.getElementById('addRowModal');
            const addRowModal = bootstrap.Modal.getInstance(addRowModalEl);
            addRowModal.hide();

            // Clear the form
            this.reset();
        });

        // Event listener for removing rows
        document.getElementById('processTableBody').addEventListener('click', function(event) {
            if (event.target.classList.contains('remove-row')) {
                const rowId = event.target.getAttribute('data-row');
                const row = document.getElementById(rowId);

                if (row) {
                    row.remove();
                    Swal.fire({
                        icon: 'success',
                        title: 'Row Removed',
                        text: 'The selected row has been successfully removed!',
                    });
                }
            }
        });
    </script>
    <!-- JavaScript -->
    <script>
        document.getElementById('saveButton').addEventListener('click', async function(e) {
            e.preventDefault();

            const formData = new FormData(document.getElementById('rfqForm'));

            // Collect data from dynamically added rows in processTable
            const rows = [];
            const processTableBody = document.getElementById('processTableBody').getElementsByTagName('tr');

            for (let row of processTableBody) {
                const rowData = {
                    quantity: row.cells[1].innerText,
                    unit: row.cells[2].innerText,
                    general_name: row.cells[3].innerText,
                    tech_spec: row.cells[4].innerText,
                    unit_cost: row.cells[5].innerText.replace('₱', ''),
                    bidder_offer_spec: row.cells[6].innerText,
                    quoted_unit_price: row.cells[7].innerText.replace('₱', '')
                };
                rows.push(rowData);
            }

            // Append rows data to formData
            formData.append('rfq_items', JSON.stringify(rows));

            try {
                // Show loading popup
                Swal.fire({
                    title: 'Saving RFQ...',
                    text: 'Please wait while your RFQ is being saved.',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading(),
                });

                const response = await fetch('src/process/add_rfq.php', {
                    method: 'POST',
                    body: formData,
                });

                const result = await response.json();

                if (result.status === 'success') {
                    Swal.fire({
                        title: 'Success!',
                        text: result.message,
                        icon: 'success',
                        showCancelButton: true,
                        confirmButtonText: 'Go to AOQ',
                        cancelButtonText: 'Download PDF'
                    }).then((choice) => {
                        if (choice.isConfirmed) {
                            // Redirect to AOQ page
                            window.location.href = 'aoq.php'; // Change to your AOQ URL
                        } else if (choice.dismiss === Swal.DismissReason.cancel) {
                            // Trigger PDF download
                            window.location.href = `src/process/download_pdf_rfq.php?id=${result.data.rfq_id}`;
                            // Ensure your server-side script returns the correct PDF file
                        }
                    });
                } else {
                    Swal.fire({
                        title: 'Failed!',
                        text: result.message,
                        icon: 'error',
                    });
                }
            } catch (error) {
                Swal.fire({
                    title: 'Error!',
                    text: 'An unexpected error occurred while saving.',
                    icon: 'error',
                });
                console.error('Save Error:', error);
            }
        });
    </script>

</body>

</html>