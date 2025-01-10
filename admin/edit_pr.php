<?php
// Database connection
include '../admin/src/config/database.php';
include '../admin/src/config/editpr_php.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Purchase Request</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .content {
            margin-left: 250px;
            padding: 20px;
            font-size: 14px;
        }

        .table th,
        .table td {
            vertical-align: middle;
        }

        .table tfoot {
            font-weight: bold;
        }

        .form-label {
            font-weight: bold;
        }
    </style>
</head>

<body>
    <div class="d-flex">
        <!-- Include Sidebar -->
        <?php include 'sidebar.php'; ?>

        <!-- Main Content -->
        <div class="content flex-grow-1 p-4 animate__animated animate__fadeIn">
            <h1 class="mb-4">Edit Purchase Request</h1>

            <!-- Row 1: Department and PR No -->
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="department" class="form-label">Department:</label>
                    <p id="department" class="form-control"><?= htmlspecialchars($first_item['department']) ?></p>
                </div>
                <div class="col-md-6">
                    <label for="pr_no" class="form-label">PR No.:</label>
                    <p id="pr_no" class="form-control"><?= htmlspecialchars($purchase_request['pr_no']) ?></p>
                </div>
            </div>

            <!-- Row 2: Section and SAI No -->
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="section" class="form-label">Section:</label>
                    <p id="section" class="form-control"><?= htmlspecialchars($first_item['section']) ?></p>
                </div>
                <div class="col-md-6">
                    <label for="sai_no" class="form-label">SAI No.:</label>
                    <input type="text" id="sai_no" name="sai_no" class="form-control" placeholder="Enter SAI No." required>
                </div>
            </div>

            <!-- Include the items table -->
            <?php include 'purchase_request_items_table.php'; ?>

            <!-- Purpose -->
            <div class="mb-4">
                <label for="purpose" class="form-label">Purpose:</label>
                <p id="purpose" class="form-control"><?= htmlspecialchars($first_item['purpose']) ?></p>
            </div>

            <!-- Row 3: Requested By and Approved By -->
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="requested_by" class="form-label">Requested By:</label>
                    <p id="requested_by" class="form-control">
                        <?= htmlspecialchars($purchase_request['requested_by_first'] . ' ' . $purchase_request['requested_by_last']) ?>
                    </p>
                </div>
                <div class="col-md-6">
                    <label for="approved_by" class="form-label">Approved By:</label>
                    <p id="approved_by" class="form-control"><?= htmlspecialchars($purchase_request['approver'] ?? 'Pending') ?></p>
                </div>
            </div>

            <!-- Buttons Section -->
            <div class="d-flex justify-content-center gap-3 mt-4">
                <button type="button" class="btn btn-primary" onclick="updateApproval(<?= $purchase_request['pr_id'] ?>, '<?= htmlspecialchars($purchase_request['approver'] ?? '') ?>', '<?= htmlspecialchars($purchase_request['status'] ?? 'Pending') ?>')">
                    Edit Approval
                </button>
                <a href="pr.php" class="btn btn-secondary">Cancel</a>
            </div>

        </div>
    </div>

    <!-- Include Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../admin/src/js/edit_approval.js"></script>

</body>

</html>