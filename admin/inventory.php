<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

require_once '../admin/src/config/pdo.php';

$itemsPerPage = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $itemsPerPage;

$inventoryItems = [];
$sql = "SELECT * FROM inventory ORDER BY created_at DESC LIMIT :offset, :itemsPerPage";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
$stmt->bindParam(':itemsPerPage', $itemsPerPage, PDO::PARAM_INT);
$stmt->execute();
$inventoryItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

$totalItems = $pdo->query("SELECT COUNT(*) FROM inventory")->fetchColumn();
$totalPages = ceil($totalItems / $itemsPerPage);
?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Management</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">

    <style>
        body {
            background-color: #f8f9fa;
        }

        .content {
            margin-left: 250px;
            padding: 20px;
        }

        .card {
            border: none;
            border-radius: 0.75rem;
            background-color: #ffffff;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .card-title {
            font-size: 1.25rem;
            font-weight: 600;
        }

        .card-text {
            color: #6c757d;
        }

        .chart-container {
            max-height: 400px;
        }

        .table th,
        .table td {
            vertical-align: middle;
        }
    </style>
</head>

<body>
    <div class="d-flex">
        <?php include 'sidebar.php'; ?>

        <div class="content flex-grow-1 p-4 animate__animated animate__fadeIn">
            <div class="jumbotron jumbotron-fluid bg-light text-dark py-5 mb-2">
                <div class="container">
                    <h1 class="display-4 fw-bold">Inventory Management</h1>
                    <p class="lead">Here is a list of all the materials in your inventory.</p>
                    <hr class="my-4 bg-light">
                    <p class="mb-0">You can update, add, or delete materials from this list.</p>
                </div>
            </div>

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">Inventory List</h4>

                    <form class="d-flex" style="max-width: 300px;">
                        <input type="text" class="form-control" id="searchInput" placeholder="Search items..." onkeyup="searchItems()">
                        <button type="submit" class="btn btn-outline-secondary ms-2" style="border-radius: 0; background-color: #f8f9fa;">
                            <i class="fas fa-search"></i>
                        </button>
                    </form>

                    <div>
                        <button class="btn btn-dark ms-3" style="padding: 0.5rem 1rem;" onclick="window.location.reload();">
                            <i class="fas fa-sync-alt"></i> Refresh Table
                        </button>

                        <button class="btn btn-success ms-3" style="padding: 0.5rem 1rem;" data-bs-toggle="modal" data-bs-target="#addInventoryModal">
                            <i class="fas fa-plus"></i> Add New Item
                        </button>
                    </div>
                </div>
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Item No.</th>
                            <th>Unit</th>
                            <th>Item Name</th>
                            <th>Description</th>
                            <th>Unit Cost</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="inventoryTableBody">
                        <?php
                        if ($inventoryItems):
                            foreach ($inventoryItems as $item): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($item['item_no']); ?></td>
                                    <td><?php echo htmlspecialchars($item['unit']); ?></td>
                                    <td><?php echo htmlspecialchars($item['item_name']); ?></td>
                                    <td><?php echo htmlspecialchars($item['item_description']); ?></td>
                                    <td><?php echo number_format($item['unit_cost'], 2); ?></td>
                                    <td>
                                        <a href="#" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editInventoryModal" onclick="editInventory(<?php echo $item['inventory_id']; ?>)">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <a href="#" class="btn btn-danger btn-sm" onclick="deleteInventory(<?php echo $item['inventory_id']; ?>)">
                                            <i class="fas fa-trash-alt"></i> Delete
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center">No inventory items found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>

                <div class="card-footer">
                    <nav aria-label="Page navigation">
                        <ul class="pagination justify-content-center">
                            <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $page - 1; ?>" aria-label="Previous">
                                    <span aria-hidden="true">&laquo;</span>
                                </a>
                            </li>
                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>
                            <li class="page-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $page + 1; ?>" aria-label="Next">
                                    <span aria-hidden="true">&raquo;</span>
                                </a>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>
    </div>

    <div class="modal fade" id="addInventoryModal" tabindex="-1" aria-labelledby="addInventoryModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addInventoryModalLabel">Add New Inventory Item</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addInventoryForm">
                        <div class="mb-3">
                            <label for="unit" class="form-label">Unit of Measurement</label>
                            <input type="text" class="form-control" id="add_unit" name="unit" required>
                        </div>
                        <div class="mb-3">
                            <label for="item_name" class="form-label">Item Name</label>
                            <input type="text" class="form-control" id="add_item_name" name="item_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="item_description" class="form-label">Item Description</label>
                            <textarea class="form-control" id="add_item_description" name="item_description" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="unit_cost" class="form-label">Unit Cost</label>
                            <input type="number" class="form-control" id="add_unit_cost" name="unit_cost" step="0.01" required>
                        </div>
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">Add Item</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>


    <div class="modal fade" id="editInventoryModal" tabindex="-1" aria-labelledby="editInventoryModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editInventoryModalLabel">Edit Inventory Item</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editInventoryForm">
                        <input type="hidden" id="inventory_id" name="inventory_id">
                        <div class="mb-3">
                            <label for="unit" class="form-label">Unit of Measurement</label>
                            <input type="text" class="form-control" id="edit_unit" name="unit" required>
                        </div>
                        <div class="mb-3">
                            <label for="item_name" class="form-label">Item Name</label>
                            <input type="text" class="form-control" id="edit_item_name" name="item_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="item_description" class="form-label">Item Description</label>
                            <textarea class="form-control" id="edit_item_description" name="item_description" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="unit_cost" class="form-label">Unit Cost</label>
                            <input type="number" class="form-control" id="edit_unit_cost" name="unit_cost" step="0.01" required>
                        </div>
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function searchItems() {
            let input = document.getElementById('searchInput').value.toLowerCase();
            let table = document.getElementById('inventoryTableBody');
            let rows = table.getElementsByTagName('tr');

            for (let i = 0; i < rows.length; i++) {
                let cells = rows[i].getElementsByTagName('td');
                let itemName = cells[1].textContent.toLowerCase();
                let itemDescription = cells[2].textContent.toLowerCase();

                if (itemName.includes(input) || itemDescription.includes(input)) {
                    rows[i].style.display = '';
                } else {
                    rows[i].style.display = 'none';
                }
            }
        }

        function deleteInventory(inventoryId) {
            Swal.fire({
                title: 'Are you sure?',
                text: 'You won\'t be able to revert this!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'No, keep it'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch('../admin/src/process/delete_inventory.php?id=' + inventoryId, {
                            method: 'GET'
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.status === 'success') {
                                Swal.fire(
                                    'Deleted!',
                                    data.message,
                                    'success'
                                ).then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire(
                                    'Error!',
                                    data.message,
                                    'error'
                                );
                            }
                        })
                        .catch(error => {
                            Swal.fire(
                                'Error!',
                                'There was a problem deleting the item.',
                                'error'
                            );
                        });
                }
            });
        }

        function editInventory(inventoryId) {
            fetch(`../admin/src/process/get_inventory.php?id=${inventoryId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        const item = data.item;
                        document.getElementById('inventory_id').value = item.inventory_id;
                        document.getElementById('edit_unit').value = item.unit;
                        document.getElementById('edit_item_name').value = item.item_name;
                        document.getElementById('edit_item_description').value = item.item_description;
                        document.getElementById('edit_unit_cost').value = item.unit_cost;
                    } else {
                        Swal.fire('Error', 'Failed to fetch item data', 'error');
                    }
                });
        }

        const editForm = document.getElementById('editInventoryForm');
        editForm.addEventListener('submit', function(event) {
            event.preventDefault();

            const formData = new FormData(editForm);
            fetch('../admin/src/process/edit_inventory.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: data.message,
                            showConfirmButton: false,
                            timer: 1000
                        }).then(() => {
                            $('#editInventoryModal').modal('hide');
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Failed!',
                            text: data.message
                        });
                    }
                })
                .catch(error => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Something went wrong. Please try again later.'
                    });
                });
        });

        const form = document.getElementById('addInventoryForm');
        form.addEventListener('submit', function(event) {
            event.preventDefault();
            const formData = new FormData(form);

            fetch('../admin/src/process/add_inventory.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Item added successfully',
                            showConfirmButton: true,
                            confirmButtonText: 'OK',
                            timer: 2000
                        });
                        // window.location.reload();
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error adding item',
                            text: data.message || 'Please try again later.',
                            showConfirmButton: true
                        });
                    }
                })
                .catch(error => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'An unexpected error occurred. Please try again.',
                        showConfirmButton: true
                    });
                });
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</body>

</html>