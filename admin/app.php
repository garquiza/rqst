<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

require_once '../admin/src/config/database.php';

$yearFilter = isset($_GET['year']) ? $_GET['year'] : '';

// Pagination
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$query = "SELECT 
            ppmp_list.ppmp_id, 
            ppmp_list.project_title, 
            ppmp_form.code, 
            end_users.sector_id, 
            sector.name AS pmo_end_user, 
            app.early_procurement_activity, 
            ppmp_form.mode_of_procurement, 
            app.advertisement_posting_ib_rei, 
            app.submission_opening_bids, 
            app.notice_of_award, 
            app.contract_signing, 
            app.source_of_funds, 
            app.total, 
            app.mooe, 
            app.co, 
            app.remarks
          FROM 
            ppmp_list
          LEFT JOIN app ON ppmp_list.ppmp_id = app.ppmp_id
          LEFT JOIN ppmp_form ON ppmp_list.ppmp_id = ppmp_form.ppmp_id
          LEFT JOIN end_users ON ppmp_list.user_id = end_users.id
          LEFT JOIN sector ON end_users.sector_id = sector.id 
          WHERE ppmp_list.status = 'approved'";

if ($yearFilter) {
    $query .= " AND YEAR(ppmp_list.date_created) = '$yearFilter'";
}

$query .= " ORDER BY ppmp_list.ppmp_id ASC LIMIT $limit OFFSET $offset";


$result = mysqli_query($conn, $query);

if (!$result) {
    die("Error fetching data: " . mysqli_error($conn));
}

$currentYear = date('Y');

$current_page = 'app.php';
// Fetch procurement titles
$titleQuery = "SELECT * FROM procurement_titles";
$titleResult = mysqli_query($conn, $titleQuery);
$titles = [];
if ($titleResult) {
    while ($titleRow = mysqli_fetch_assoc($titleResult)) {
        $titles[$titleRow['page']] = $titleRow;
    }
}

// Fetch unique years for the status-dropdown
$yearQuery = "SELECT DISTINCT YEAR(date_created) AS year FROM ppmp_form ORDER BY year DESC";
$yearResult = mysqli_query($conn, $yearQuery);
$years = [];
if ($yearResult) {
    while ($yearRow = mysqli_fetch_assoc($yearResult)) {
        $years[] = $yearRow['year'];
    }
}

// Get total number of rows for pagination
$countQuery = "SELECT COUNT(*) AS total FROM ppmp_list";
if ($yearFilter) {
    $countQuery .= " WHERE YEAR(date_created) = '$yearFilter'";
}
$countResult = mysqli_query($conn, $countQuery);
$countRow = mysqli_fetch_assoc($countResult);
$totalRows = $countRow['total'];
$totalPages = ceil($totalRows / $limit);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - APP</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../admin/src/css/dashboard.css">
    <link rel="stylesheet" href="../admin/src/css/pr.css">
    <link rel="stylesheet" href="src/css/ppmp_list.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .pagination {
            justify-content: center;
            display: flex;
        }
    </style>
</head>

<body>
    <div class="d-flex">
        <?php include '../admin/sidebar.php'; ?>

        <div class="content flex-grow-1" style="margin-left: 250px; padding: 20px;">
            <div class="header-card">
                <h1 class="mb-4"><?= isset($titles[$current_page]) ? $titles[$current_page]['title'] : 'Annual Procurement Plan'; ?></h1>
                <p class="mb-0"><?= isset($titles[$current_page]) ? $titles[$current_page]['subtitle'] : 'Description'; ?></p>
            </div>

            <div class="table-container">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <span class="total-number">Current Year: <?php echo $currentYear; ?></span>
                    </div>
                    <div>
                        <a href="../admin/src/process/download_excel_app.php" class="btn btn-outline-primary btn-sm" title="Download Table" style="margin-right: 5px;">
                            <i class="fas fa-download"></i> Download
                        </a>
                    </div>
                    <div class="status-dropdown">
                        <select class="form-select" id="status-filter" style="width: 200px;">
                            <option value="all">All</option>
                            <?php foreach ($years as $year): ?>
                                <option value="<?php echo $year; ?>" <?php echo ($yearFilter == $year) ? 'selected' : ''; ?>><?php echo $year; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="search-container mb-4">
                    <input type="text" class="form-control" id="search-bar" placeholder="Search by Title or Approver">
                </div>

                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>CODE (PAP)</th>
                                <th>Procurement Project</th>
                                <th>PMO/End-user</th>
                                <th>Is this an Early Procurement Activity? (Yes/No)</th>
                                <th>Mode of Procurement</th>
                                <th colspan="4" class="text-center">Schedule for Each Procurement Activity</th>
                                <th>Source of Funds</th>
                                <th colspan="3" class="text-center">Estimated Budget (PHP)</th>
                                <th>REMARKS (brief description of Project)</th>
                                <th>Action</th>
                            </tr>
                            <tr>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <!-- Sub-columns for Schedule for Each Procurement Activity -->
                                <th>Advertisement/Posting of IB/REI</th>
                                <th>Submission/Opening of Bids</th>
                                <th>Notice of Award</th>
                                <th>Contract Signing</th>
                                <th></th>
                                <!-- Sub-columns for Estimated Budget (PHP) -->
                                <th>Total</th>
                                <th>MOOE</th>
                                <th>CO</th>
                                <th></th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="ppmp-table">
                            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['code']); ?></td>
                                    <td><?php echo htmlspecialchars($row['project_title']); ?></td>
                                    <td><?php echo htmlspecialchars($row['pmo_end_user']); ?></td>
                                    <td><?php echo htmlspecialchars($row['early_procurement_activity']); ?></td>
                                    <td><?php echo htmlspecialchars($row['mode_of_procurement']); ?></td>
                                    <td><?php echo htmlspecialchars($row['advertisement_posting_ib_rei']); ?></td>
                                    <td><?php echo htmlspecialchars($row['submission_opening_bids']); ?></td>
                                    <td><?php echo htmlspecialchars($row['notice_of_award']); ?></td>
                                    <td><?php echo htmlspecialchars($row['contract_signing']); ?></td>
                                    <td><?php echo htmlspecialchars($row['source_of_funds']); ?></td>
                                    <td><?php echo htmlspecialchars($row['total']); ?></td>
                                    <td><?php echo htmlspecialchars($row['mooe']); ?></td>
                                    <td><?php echo htmlspecialchars($row['co']); ?></td>
                                    <td><?php echo htmlspecialchars($row['remarks']); ?></td>
                                    <td class="text-justify">
                                        <div class="btn-group" role="group" aria-label="Actions">
                                            <a href="../admin/edit_app.php?ppmp_id=<?php echo $row['ppmp_id']; ?>" class="btn btn-outline-warning btn-sm" title="Update PPMP" style="margin-right: 5px;">
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

                <!-- Pagination -->
                <nav aria-label="Page navigation">
                    <ul class="pagination">
                        <li class="page-item <?php echo ($page == 1) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $page - 1; ?>&year=<?php echo $yearFilter; ?>" aria-label="Previous">
                                <span aria-hidden="true">&laquo;</span>
                            </a>
                        </li>
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?>&year=<?php echo $yearFilter; ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>
                        <li class="page-item <?php echo ($page == $totalPages) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $page + 1; ?>&year=<?php echo $yearFilter; ?>" aria-label="Next">
                                <span aria-hidden="true">&raquo;</span>
                            </a>
                        </li>
                    </ul>
                </nav>

            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
        <script>
            document.getElementById('search-bar').addEventListener('input', function() {
                let searchTerm = this.value.toLowerCase();
                let rows = document.querySelectorAll('#ppmp-table tr');

                rows.forEach(row => {
                    let title = row.cells[1].textContent.toLowerCase();
                    if (title.includes(searchTerm)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            });

            document.getElementById('status-filter').addEventListener('change', function() {
                let selectedYear = this.value;
                let url = new URL(window.location.href);
                if (selectedYear !== 'all') {
                    url.searchParams.set('year', selectedYear);
                } else {
                    url.searchParams.delete('year');
                }
                window.location.href = url;
            });

            document.addEventListener('DOMContentLoaded', () => {
                const dateColumns = [5, 6, 7, 8]; // Column indices for the date fields (0-based indexing)

                const tableRows = document.querySelectorAll('#ppmp-table tr');

                tableRows.forEach(row => {
                    dateColumns.forEach(index => {
                        const cell = row.cells[index];
                        if (cell) {
                            const originalDate = cell.textContent.trim();
                            const formattedDate = formatToMMDDYYYY(originalDate);
                            if (formattedDate) {
                                cell.textContent = formattedDate;
                            }
                        }
                    });
                });
            });

            function formatToMMDDYYYY(dateStr) {
                const date = new Date(dateStr);
                if (!isNaN(date)) {
                    const month = String(date.getMonth() + 1).padStart(2, '0');
                    const day = String(date.getDate()).padStart(2, '0');
                    const year = date.getFullYear();
                    return `${month}/${day}/${year}`;
                }
                return null; // Return null if the date is invalid
            }

            // function performSearch() {
            //     const searchTerm = document.getElementById('search-bar').value.toLowerCase();
            //     tableRows.forEach(row => {
            //         const title = row.children[0].textContent.toLowerCase();
            //         const approver = row.children[1].textContent.toLowerCase();
            //         row.style.display = (title.includes(searchTerm) || approver.includes(searchTerm)) ? '' : 'none';
            //     });
            // }

            function confirmDelete(ppmpId) {
                Swal.fire({
                    title: 'Are you sure?',
                    text: 'You will not be able to recover this APP!',
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
                fetch('../admin/src/process/delete_ppmp.php?ppmp_id=' + ppmpId) // NOTE NOTE NOTE
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                title: 'Deleted!',
                                text: 'The APP row has been deleted successfully.',
                                icon: 'success',
                                confirmButtonText: 'Okay',
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                title: 'Error!',
                                text: 'There was an issue deleting the APP.',
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