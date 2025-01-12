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

// Fetch approved projects from the database
$query = "SELECT project_title FROM ppmp_list WHERE status = 'approved'";
$result = mysqli_query($conn, $query);

$approvedProjects = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $approvedProjects[] = $row['project_title'];
    }
}
$current_page = 'pmf.php';

// Fetch procurement titles 
$titleQuery = "SELECT * FROM procurement_titles";
$titleResult = mysqli_query($conn, $titleQuery);
$titles = [];

if ($titleResult) {

    while ($titleRow = mysqli_fetch_assoc($titleResult)) {

        $titles[$titleRow['page']] = $titleRow;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Procurement Modality Approval Form</title>

    <!-- CDN Links -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="src/css/pmaf.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>

    <div class="d-flex">
        <!-- Include Sidebar -->
        <?php include 'sidebar.php'; ?>

        <!-- Main Content -->
        <div class="content flex-grow-1 p-4 animate__animated animate__fadeIn">
            <div class="card shadow-lg">
                <div class="card-header bg-primary text-white">
                    <h2><?= isset($titles[$current_page]) ? $titles[$current_page]['title'] : 'Procurement Modality Approval Form'; ?></h2>
                </div>

                <div class="card-body">
                    <!-- Procurement Form -->
                    <form id="pmafForm">
                        <!-- Modalities -->
                        <div class="mb-4">
                            <h5 class="fw-bold">Select Procurement Modalities</h5>
                            <div class="row">
                                <?php
                                $modalities = [
                                    "Public Bidding",
                                    "Emergency Cases (Section 53.2)",
                                    "Highly Technical Consultants (Section 53.7)",
                                    "Direct Contracting (Section 50)",
                                    "Take-over of Contracts (Section 53.3)",
                                    "Small Value Procurement (Section 53.9)",
                                    "Shopping (Section 52.1.a)",
                                    "Shopping (Section 52.1.b)",
                                    "Adjacent / Contiguous (Section 53.4)",
                                    "Agency-to-Agency (Section 53.5)",
                                    "Lease of Real Property and Venue (Section 53.10)",
                                    "Direct Retail Purchase of POL Products, Tickets, Online Subscriptions (Section 53.14)",
                                    "Two – Failed Biddings (Section 53.1)",
                                    "Scientific, Scholarly, or Artistic work (Section 53.6)",
                                    "Others"
                                ];

                                foreach ($modalities as $index => $modality) {
                                    echo '<div class="col-md-4 mb-3">
                                            <label>
                                                <input type="checkbox" name="modality[]" value="' . $modality . '" class="form-check-input">
                                                ' . $modality . '
                                            </label>
                                        </div>';
                                    if (($index + 1) % 3 === 0) {
                                        echo '</div><div class="row">';
                                    }
                                }
                                ?>
                            </div>
                        </div>

                        <!-- Project Title -->
                        <div class="mb-4">
                            <label for="projectTitle" class="form-label fw-bold">Project Title</label>

                            <!-- Dropdown to select project title -->
                            <select id="projectTitle" name="project_title" class="form-select" required>
                                <option value="">-- Select Project Title --</option>
                                <?php foreach ($approvedProjects as $projectTitle): ?>
                                    <option value="<?= $projectTitle ?>"><?= $projectTitle ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Funds Section -->
                        <div class="mb-4">
                            <h5>Funds Availability</h5>
                            <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#otherFundsModal" onclick="addFund()">
                                <i class="fas fa-plus"></i> Add Fund
                            </button>
                            <div id="fundList" class="row mt-3"></div>
                        </div>

                        <!-- MOOE Section -->
                        <div class="mb-4">
                            <h5>Maintenance of Operating Expenses (MOOE)</h5>
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="addMooe()">
                                <i class="fas fa-plus"></i> Add MOOE
                            </button>
                            <div id="mooeList" class="row mt-3"></div>
                        </div>

                        <!-- CO -->
                        <div class="mb-4">
                            <label for="co" class="form-label fw-bold">Contract Order Amount (CO)</label>
                            <input type="number" id="co" name="co" class="form-control" placeholder="Enter CO amount" min="0" step="any">
                        </div>

                        <!-- Hidden Inputs to Store Dynamic Data -->
                        <input type="hidden" name="funds" id="funds">
                        <input type="hidden" name="mooe" id="mooe">

                        <!-- Submit Button -->
                        <div class="text-end mt-4">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-save"></i> Save
                            </button>
                        </div>

                    </form>

                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        function addFund() {
            Swal.fire({
                title: 'Add Fund',
                input: 'text',
                inputPlaceholder: 'Enter fund name',
                confirmButtonText: 'Add',
                showCancelButton: true
            }).then((result) => {
                if (result.isConfirmed) {
                    const fundList = document.getElementById('fundList');
                    const div = document.createElement('div');
                    div.classList.add('col-md-4', 'mb-3');
                    div.innerHTML = `<div class="d-flex justify-content-between align-items-center">
                ${result.value}
                <button class="btn btn-danger btn-sm mt-2" onclick="this.parentElement.parentElement.remove(); updateFunds();">Delete</button>
            </div>`;
                    fundList.appendChild(div);
                    updateFunds();
                }
            });
        }

        function updateFunds() {
            const funds = Array.from(document.querySelectorAll('#fundList .d-flex')).map(el =>
                el.firstChild.textContent.trim()
            );
            document.getElementById('funds').value = JSON.stringify(funds);
        }

        function addMooe() {
            Swal.fire({
                title: 'Add MOOE',
                input: 'text',
                inputPlaceholder: 'Enter MOOE name',
                confirmButtonText: 'Add',
                showCancelButton: true
            }).then((result) => {
                if (result.isConfirmed) {
                    const mooeList = document.getElementById('mooeList');
                    const div = document.createElement('div');
                    div.classList.add('col-md-4', 'mb-3');
                    div.innerHTML = `<div class="d-flex justify-content-between align-items-center">
                ${result.value}
                <button class="btn btn-danger btn-sm mt-2" onclick="this.parentElement.parentElement.remove(); updateMooe();">Delete</button>
            </div>`;
                    mooeList.appendChild(div);
                    updateMooe();
                }
            });
        }

        function updateMooe() {
            const mooe = Array.from(document.querySelectorAll('#mooeList .d-flex')).map(el =>
                el.firstChild.textContent.trim()
            );
            document.getElementById('mooe').value = JSON.stringify(mooe);
        }

        document.getElementById('pmafForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            const formData = new FormData(this);

            const projectTitleValue = document.getElementById('projectTitle').value;
            const selectedModalities = Array.from(document.querySelectorAll('input[name="modality[]"]:checked')).map(el => el.value);
            const funds = Array.from(document.querySelectorAll('#fundList div')).map(el => el.textContent.trim().replace('X', ''));
            const mooeItems = Array.from(document.querySelectorAll('#mooeList div')).map(el => el.textContent.trim());

            // Append data to FormData
            formData.append('project_title', projectTitleValue);
            formData.append('modality', JSON.stringify(selectedModalities));
            formData.append('funds', JSON.stringify(funds));
            formData.append('mooe', JSON.stringify(mooeItems));
            formData.append('co', document.getElementById('co').value);

            Swal.fire({
                title: 'Submitting...',
                text: 'Please wait while your request is being processed.',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            try {
                const response = await fetch('src/process/add_pmaf.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.status === 'success') {
                    Swal.fire({
                        title: 'Success!',
                        text: result.message,
                        icon: 'success',
                        showCancelButton: true,
                        confirmButtonText: 'Proceed to RFQ',
                        cancelButtonText: 'Download PDF'
                    }).then((choice) => {
                        if (choice.isConfirmed) {
                            // Redirect to the RFQ page
                            window.location.href = `rfq.php?project_title=${encodeURIComponent(projectTitleValue)}`;
                        } else if (choice.dismiss === Swal.DismissReason.cancel) {
                            // Trigger PDF download
                            window.location.href = `src/process/download_pdf_pmf.php?project_title=${encodeURIComponent(projectTitleValue)}`;
                        }
                    });
                } else {
                    Swal.fire('Failed!', result.message, 'error');
                }
            } catch (error) {
                Swal.fire('Error!', 'Unexpected server error.', 'error');
                console.error('Form Error:', error);
            }
        });
    </script>

</body>

</html>