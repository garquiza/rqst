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

// Retrieve the ID and title from the URL
$noa_id = $_GET['id'] ?? null;
$project_title = $_GET['title'] ?? null;

// Optionally, you can fetch more details about the project using the ID
if ($noa_id) {
    $query = "SELECT * FROM notice_of_award WHERE noa_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $noa_id);
    $stmt->execute();
    $project_details = $stmt->get_result()->fetch_assoc();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BAC - Add Stock to Purchase Order</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="src/css/pr.css">
    <style>
        .content {
            margin-left: 250px;
            padding: 20px;
        }

        .header-card {
            margin-bottom: 30px;
        }

        .form-container {
            border: 1px solid #e0e0e0;
            padding: 20px;
            border-radius: 5px;
            background-color: #f8f9fa;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .form-control {
            border: 1px solid #ced4da;
        }

        .label-bold {
            font-weight: bold;
        }

        .btn-submit {
            background-color: #007bff;
            color: white;
        }

        .btn-submit:hover {
            background-color: #0056b3;
        }

        .remove-btn {
            cursor: pointer;
            color: red;
        }

        .stock-item {
            border: 1px solid #ced4da;
            border-radius: 5px;
            padding: 10px;
            margin-bottom: 10px;
            background-color: #ffffff;
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
                <h1 class="mb-4">Add Stock to Purchase Order for: <strong><?= htmlspecialchars($project_title); ?></strong></h1>
            </div>

            <div class="form-container">
                <form id="stockForm">
                    <input type="hidden" name="noa_id" value="<?= htmlspecialchars($noa_id); ?>"> <!-- Hidden field for project ID -->
                    <div id="stockItems">
                        <div class="row mb-3 stock-item">
                            <div class="col-md-2">
                                <label for="unit" class="form-label label-bold">Unit:</label>
                                <input type="text" class="form-control" name="unit[]" placeholder="Enter Unit">
                            </div>
                            <div class="col-md-2">
                                <label for="quantity" class="form-label label-bold">Quantity:</label>
                                <input type="number" class="form-control" name="quantity[]" placeholder="Enter Quantity">
                            </div>
                            <div class="col-md-8">
                                <label for="description" class="form-label label-bold">Description:</label>
                                <input type="text" class="form-control" name="description[]" placeholder="Enter Description">
                            </div>
                            <div class="col-md-2 mt-2">
                                <label for="unit_cost" class="form-label label-bold">Unit Cost:</label>
                                <input type="number" class="form-control" name="unit_cost[]" placeholder="Enter Unit Cost" step="0.01">
                            </div>
                            <div class="col-md-2 mt-2">
                                <label for="amount" class="form-label label-bold">Amount:</label>
                                <input type="number" class="form-control" name="amount[]" placeholder="Amount" step="0.01" readonly>
                            </div>
                            <div class="col-md-1 d-flex align-items-end mt-2">
                                <button type="button" class="btn btn-danger remove-btn"> Remove </button>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <button type="button" class="btn btn-primary" id="addStockBtn">
                            <i class="fas fa-plus"></i> Add Stock
                        </button>
                    </div>

                    <div class="mb-3">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-check"></i> Submit Purchase Order
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.getElementById('addStockBtn').addEventListener('click', function() {
            const stockItemsContainer = document.getElementById('stockItems');
            const newStockItem = document.createElement('div');
            newStockItem.classList.add('row', 'mb-3', 'stock-item');
            newStockItem.innerHTML = `
                <div class="col-md-2">
                    <label for="unit" class="form-label label-bold">Unit:</label>
                    <input type="text" class="form-control" name="unit[]" placeholder="Enter Unit">
                </div>
                <div class="col-md-2">
                    <label for="quantity" class="form-label label-bold">Quantity:</label>
                    <input type="number" class="form-control" name="quantity[]" placeholder="Enter Quantity">
                </div>
                <div class="col-md-8">
                    <label for="description" class="form-label label-bold">Description:</label>
                    <input type="text" class="form-control" name="description[]" placeholder="Enter Description">
                </div>
                <div class="col-md-2 mt-2">
                    <label for="unit_cost" class="form-label label-bold">Unit Cost:</label>
                    <input type="number" class="form-control" name="unit_cost[]" placeholder="Enter Unit Cost" step="0.01">
                </div>
                <div class="col-md-2 mt-2">
                    <label for="amount" class="form-label label-bold">Amount:</label>
                    <input type="number" class="form-control" name="amount[]" placeholder="Amount" step="0.01" readonly>
                </div>
                <div class="col-md-1 d-flex align-items-end mt-2">
                    <button type="button" class="btn btn-danger remove-btn"> Remove </button>
                </div>
            `;
            stockItemsContainer.appendChild(newStockItem);
        });

        // Event delegation for removing stock items
        document.getElementById('stockItems').addEventListener('click', function(e) {
            if (e.target.classList.contains('remove-btn')) {
                e.target.closest('.stock-item').remove();
            }
        });

        // Calculate amount when unit cost or quantity changes
        document.getElementById('stockItems').addEventListener('input', function(e) {
            if (e.target.name === 'unit_cost[]' || e.target.name === 'quantity[]') {
                const stockItem = e.target.closest('.stock-item');
                const unitCost = parseFloat(stockItem.querySelector('input[name="unit_cost[]"]').value) || 0;
                const quantity = parseFloat(stockItem.querySelector('input[name="quantity[]"]').value) || 0;
                const amountField = stockItem.querySelector('input[name="amount[]"]');
                amountField.value = (unitCost * quantity).toFixed(2);
            }
        });

        // Handle form submission
        document.getElementById('stockForm').addEventListener('submit', function(e) {
            e.preventDefault();

            Swal.fire({
                title: 'Confirm Submission',
                text: "Are you sure you want to submit the stock items?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, Submit!',
                showDenyButton: true,
                denyButtonText: 'Download'
            }).then((result) => {
                if (result.isConfirmed) {
                    const formData = new FormData(this);

                    fetch('src/process/add_stock.php', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.status === 'success') {
                                Swal.fire({
                                    title: 'Success!',
                                    text: 'Stock items have been submitted.',
                                    icon: 'success'
                                }).then(() => {
                                    window.location.href = 'pp.php'; // Redirect to the PMR page
                                });
                            } else {
                                Swal.fire('Failed!', data.message, 'error');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            Swal.fire('Error!', 'Something went wrong during submission.', 'error');
                        });
                } else if (result.isDenied) {
                    // Logic to download the stock items
                    const formData = new FormData(this);
                    // Assuming you have a download endpoint set up
                    fetch('src/process/download_stock.php', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => {
                            if (response.ok) {
                                return response.blob(); // Get the response as a blob
                            }
                            throw new Error('Download failed');
                        })
                        .then(blob => {
                            const url = window.URL.createObjectURL(blob);
                            const a = document.createElement('a');
                            a.href = url;
                            a.download = 'stock_items.xlsx'; // Set the desired file name
                            document.body.appendChild(a);
                            a.click();
                            a.remove();
                            Swal.fire('Download started!', 'Your stock items are being downloaded.', 'success');
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            Swal.fire('Error!', 'Something went wrong during the download.', 'error');
                        });
                }
            });
        });
    </script>
</body>

</html>