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
require_once '../admin/src/config/pdo.php';

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
// Fetch project titles already present in the pmaf table
$pmafQuery = "SELECT project_title FROM pmaf";
$pmafResult = mysqli_query($conn, $pmafQuery);

$usedProjects = [];
if ($pmafResult) {
    while ($row = mysqli_fetch_assoc($pmafResult)) {
        $usedProjects[] = $row['project_title'];
    }
}

// Filter out the projects that are already used in pmaf
$availableProjects = array_diff($approvedProjects, $usedProjects);


// Fetch funds from the database
$query = "SELECT * FROM fund";
$stmt = $pdo->prepare($query);
$stmt->execute();
$funds = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fund_source = $_POST['fund_source'];
    $custom_fund = $_POST['custom_fund'] ?? '';
    
    // Use custom input if 'Others' is selected
    $selected_fund = ($fund_source === 'Others') ? $custom_fund : $fund_source;

    // Ensure that if 'Others' is selected, the custom fund is not empty
    if ($fund_source === 'Others' && empty($custom_fund)) {
        echo "<script>alert('Please specify a custom fund source.'); window.history.back();</script>";
        exit();
    }

    // Insert into PMAF database
    $insertQuery = "INSERT INTO pmaf (fund_source) VALUES (:fund_source)";
    $insertStmt = $pdo->prepare($insertQuery);
    $insertStmt->execute(['fund_source' => $selected_fund]);

    echo "<script>alert('Fund source saved successfully!'); window.location.href='pmf.php';</script>";
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
    <script>
        function toggleCustomInput(select) {
            const customInput = document.getElementById('custom-fund');
            if (select.value === 'Others') {
                customInput.style.display = 'block';
            } else {
                customInput.style.display = 'none';
            }
        }
    </script>
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
                                
                                <!-- Available projects -->
                                <?php foreach ($availableProjects as $projectTitle): ?>
                                    <option value="<?= htmlspecialchars($projectTitle) ?>"><?= htmlspecialchars($projectTitle) ?></option>
                                <?php endforeach; ?>

                                <!-- Used projects (disabled) -->
                                <?php foreach ($usedProjects as $projectTitle): ?>
                                    <option value="<?= htmlspecialchars($projectTitle) ?>" disabled><?= htmlspecialchars($projectTitle) ?> (Processed)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Funds Section -->
                        <div class="mb-4">
                            <label for="fund_source" class="form-label">Funds Availability</label>
                            <select name="fund_source" id="fund_source" class="form-select" onchange="toggleCustomInput(this)" required>
                                <option value="" disabled selected>Select a fund source</option>
                                <?php foreach ($funds as $fund): ?>
                                    <option value="<?php echo htmlspecialchars($fund['name']); ?>">
                                        <?php echo htmlspecialchars($fund['name']); ?>
                                    </option>

                                    <?php endforeach; ?>
                                    <option value="Others">Others</option>
                            </select>
                        </div>

                        <div class="mb-3" id="custom-fund" style="display: none;">
                            <label for="custom_fund" class="custom-fund">Specify Other Fund Source</label>
                            <input type="text" name="custom_fund" id="custom_fund" class="form-control" placeholder="Enter fund source">
                        </div>
                        
                        <!-- Total ABC -->
                        <!-- Total ABC Input -->
                        <div class="mb-4">
                            <label for="totalABC" class="form-label">Total Approved Budget for the Contract (ABC)</label>
                            <input type="number" id="totalABC" name="total_abc" class="form-control" placeholder="Enter Total ABC amount" min="0" step="any" required>
                        </div>

                                   
                        <!-- MOOE Section -->
                        <div class="mb-4">
                            <label for="co" class="form-label">Maintenance and Other Operating Expenses (MOOE)</label>
                            <input type="number" id="mooe" name="mooe" class="form-control" placeholder="Enter MOOE Amount" min="0" step="any">
                        </div>

                        <!-- CO -->
                        <div class="mb-4">
                            <label for="co" class="form-label">Contract Order Amount (CO)</label>
                            <input type="number" id="co" name="co" class="form-control" placeholder="Enter CO Amount" min="0" step="any">
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
        function toggleCustomInput(select) {
            const customInput = document.getElementById('custom-fund');
            if (select.value === 'Others') {
                customInput.style.display = 'block';
            } else {
                customInput.style.display = 'none';
            }
        }
        document.getElementById('pmafForm').addEventListener('submit', async function (e) {
            e.preventDefault();

            const formData = new FormData(this);
            
            for (let [key, value] of formData.entries()) {
                console.log(`${key}: ${value}`);
            }

            const fundSource = document.getElementById('fund_source').value;
            if (fundSource === 'Others') {
                const customFund = document.getElementById('custom_fund').value.trim();
                if (customFund) {
                    formData.set('fund_source', customFund); // Override the fund source with the custom value
                } else {
                    Swal.fire('Error!', 'Please specify a custom fund source.', 'error');
                    return; // Prevent form submission if custom fund is not provided
                }
            }
            const projectTitleValue = document.getElementById('projectTitle').value;
            const selectedModalities = Array.from(document.querySelectorAll('input[name="modality[]"]:checked')).map(el => el.value);
            const funds = Array.from(document.querySelectorAll('#fundList div')).map(el => el.textContent.trim().replace('X', ''));

            // Append data to FormData
            formData.append('project_title', projectTitleValue);
            formData.append('modality', JSON.stringify(selectedModalities));
            formData.append('funds', JSON.stringify(funds));
            formData.append('mooe', document.getElementById('mooe').value);
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

                            setTimeout(() => {
                                window.location.href = `rfq.php?project_title=${encodeURIComponent(projectTitleValue)}`;
                            }, 3000);  // 3-second delay (adjust the timing if needed)
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