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

$categories = [];
$sql = "SELECT * FROM categories ORDER BY created_at DESC LIMIT :offset, :itemsPerPage";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
$stmt->bindParam(':itemsPerPage', $itemsPerPage, PDO::PARAM_INT);
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

$totalItems = $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();
$totalPages = ceil($totalItems / $itemsPerPage);
// Fetch the last inserted item number and generate the next one
$itemNo = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if it's a POST request for adding an item
    $sql = "SELECT item_no FROM items ORDER BY item_id DESC LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();

    // Get the latest item number
    $lastItemNo = $stmt->fetchColumn();

    if ($lastItemNo) {
        // Extract the numeric part and increment
        $nextItemNo = 'Item-' . (intval(substr($lastItemNo, 5)) + 1);
        echo json_encode(['next_item_no' => $nextItemNo]);
    } else {
        // If no items are in the table, start from Item-1
        echo json_encode(['next_item_no' => 'Item-1']);
    }
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Category Management</title>

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
                    <h1 class="display-4 fw-bold">Category Management</h1>
                    <p class="lead">Here is a list of all the categories in your system.</p>
                    <hr class="my-4 bg-light">
                    <p class="mb-0">You can update, add, or delete categories from this list.</p>
                </div>
            </div>

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">Category List</h4>

                    <form class="d-flex" style="max-width: 300px;">
                        <input type="text" class="form-control" id="searchInput" placeholder="Search categories..." onkeyup="searchCategories()">
                        <button type="submit" class="btn btn-outline-secondary ms-2" style="border-radius: 0; background-color: #f8f9fa;">
                            <i class="fas fa-search"></i>
                        </button>
                    </form>

                    <div>
                        <!-- Refresh Table Button -->
                        <button class="btn btn-dark ms-3" style="padding: 0.5rem 1rem;" onclick="window.location.reload();">
                            <i class="fas fa-sync-alt"></i> Refresh Table
                        </button>

                        <!-- Add New Category Button -->
                        <button class="btn btn-success ms-3" style="padding: 0.5rem 1rem;" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                            <i class="fas fa-plus"></i> Add New Category
                        </button>

                    </div>

                </div>
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Category No.</th>
                            <th>Category Name</th>
                            <th>Description</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="categoryTableBody">
                        <?php
                        if ($categories):
                            foreach ($categories as $category): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($category['category_no']); ?></td>
                                    <td><?php echo htmlspecialchars($category['category_name']); ?></td>
                                    <td><?php echo htmlspecialchars($category['description']); ?></td>
                                    <td>
                                        <!-- Add Item Button -->
                                        <button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#addItemModal" onclick="addItem(<?php echo $category['category_id']; ?>)">
                                            <i class="fas fa-plus"></i> Item
                                        </button>

                                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#viewItemsModal" onclick="viewItems(<?php echo $category['category_id']; ?>)">
                                            <i class="fas fa-eye"></i> Items
                                        </button>

                                        <!-- Edit Category Button -->
                                        <a href="#" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editCategoryModal" onclick="editCategory(<?php echo $category['category_id']; ?>)">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>


                                        <!-- Delete Category Button -->
                                        <a href="#" class="btn btn-danger btn-sm" onclick="deleteCategory(<?php echo $category['category_id']; ?>)">
                                            <i class="fas fa-trash-alt"></i> Delete
                                        </a>


                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center">No categories found.</td>
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

    <div class="modal fade" id="addCategoryModal" tabindex="-1" aria-labelledby="addCategoryModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addCategoryModalLabel">Add New Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addCategoryForm">
                        <div class="mb-3">
                            <label for="category_name" class="form-label">Category Name</label>
                            <input type="text" class="form-control" id="add_category_name" name="category_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="add_description" name="description" rows="3" required></textarea>
                        </div>
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">Add Category</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editCategoryModal" tabindex="-1" aria-labelledby="editCategoryModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editCategoryModalLabel">Edit Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editCategoryForm">
                        <input type="hidden" id="category_id" name="category_id">
                        <div class="mb-3">
                            <label for="category_name" class="form-label">Category Name</label>
                            <input type="text" class="form-control" id="edit_category_name" name="category_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="edit_description" name="description" rows="3" required></textarea>
                        </div>
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- View Items Modal -->
    <div class="modal fade" id="viewItemsModal" tabindex="-1" aria-labelledby="viewItemsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewItemsModalLabel">View Items</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Table for displaying the items -->
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th scope="col">Item No.</th>
                                <th scope="col">Item Name</th>
                                <th scope="col">Unit of Measurement</th>
                                <th scope="col">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="viewItemsList">
                            <!-- Items will be populated here dynamically -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>



    <!-- Add Item Modal -->
    <div class=" modal fade" id="addItemModal" tabindex="-1" aria-labelledby="addItemModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addItemModalLabel">Add New Item</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addItemForm">
                        <input type="hidden" id="new_category_id" name="new_category_id" readonly>

                        <div class="mb-3">
                            <label for="item_no" class="form-label">Item No.</label>
                            <input type="text" class="form-control" id="item_no" name="item_no" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="item_name" class="form-label">Item Name</label>
                            <input type="text" class="form-control" id="item_name" name="item_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="unit_of_measurement" class="form-label">Unit of Measurement</label>
                            <input type="text" class="form-control" id="unit_of_measurement" name="unit_of_measurement" required>
                        </div>
                        <div class="d-flex justify-content-end">
                            <button type="submit" id="submitAddItem" class="btn btn-primary">Add Item</button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- Edit Item Modal -->
    <div class="modal fade" id="editItemModal" tabindex="-1" aria-labelledby="editItemModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editItemModalLabel">Edit Item</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editItemForm">
                        <input type="hidden" id="edit_item_id" name="edit_item_id">

                        <div class="mb-3">
                            <label for="edit_item_no" class="form-label">Item No.</label>
                            <input type="text" class="form-control" id="edit_item_no" name="edit_item_no" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="edit_item_name" class="form-label">Item Name</label>
                            <input type="text" class="form-control" id="edit_item_name" name="edit_item_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_unit_of_measurement" class="form-label">Unit of Measurement</label>
                            <input type="text" class="form-control" id="edit_unit_of_measurement" name="edit_unit_of_measurement" required>
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
        // JavaScript to handle the Add Item modal
        function addItem(categoryId) {
            // Set the category_id in the hidden input field
            document.getElementById("new_category_id").value = categoryId;

            // Fetch the next available item number
            fetch("../admin/src/process/get_next_item_no.php")
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Set the generated item number in the input field
                        document.getElementById("item_no").value = data.item_no;
                    } else {
                        console.error("Error fetching item number:", data.message);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: data.message,
                        });
                    }
                })
                .catch(error => {
                    console.error("Error fetching item number:", error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: "There was an error fetching the item number.",
                    });
                });
        }

        // Handle the form submission via fetch (AJAX)
        document.getElementById("addItemForm").addEventListener("submit", function(event) {
            event.preventDefault(); // Prevent the default form submission

            // Get the form values
            const itemNo = document.getElementById("item_no").value;
            const itemName = document.getElementById("item_name").value;
            const unitOfMeasurement = document.getElementById("unit_of_measurement").value;
            const categoryId = document.getElementById("new_category_id").value;

            // Validate the inputs
            if (!itemNo || !itemName || !unitOfMeasurement || !categoryId) {
                Swal.fire({
                    icon: 'error',
                    title: 'Validation Error',
                    text: 'All fields are required!',
                });
                return;
            }

            // Prepare the form data
            const formData = new FormData();
            formData.append("item_no", itemNo);
            formData.append("item_name", itemName);
            formData.append("unit_of_measurement", unitOfMeasurement);
            formData.append("new_category_id", categoryId);

            // Send the data to the server using fetch
            fetch("../admin/src/process/add_item.php", {
                    method: "POST",
                    body: formData
                })
                .then(response => response.json()) // Expecting JSON response
                .then(data => {
                    if (data.success) {
                        // Show success message with SweetAlert
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: 'Item added successfully!',
                        }).then(() => {
                            // Refresh the page or reload the items list here if needed
                            location.reload(); // This will reload the page
                            // Optionally, close the modal here
                            const addItemModal = new bootstrap.Modal(document.getElementById("addItemModal"));
                            addItemModal.hide();
                        });
                    } else {
                        console.error("Error adding item:", data.message);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Error adding item: ' + data.message,
                        });
                    }
                })
                .catch(error => {
                    console.error("Error in request:", error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Request Error',
                        text: "There was an error processing your request.",
                    });
                });
        });


        function searchCategories() {
            let input = document.getElementById('searchInput').value.toLowerCase();
            let table = document.getElementById('categoryTableBody');
            let rows = table.getElementsByTagName('tr');

            for (let i = 0; i < rows.length; i++) {
                let cells = rows[i].getElementsByTagName('td');
                let categoryName = cells[1].textContent.toLowerCase();
                let description = cells[2].textContent.toLowerCase();

                if (categoryName.includes(input) || description.includes(input)) {
                    rows[i].style.display = '';
                } else {
                    rows[i].style.display = 'none';
                }
            }
        }

        function deleteCategory(categoryId) {
            Swal.fire({
                title: 'Are you sure?',
                text: 'You won\'t be able to revert this!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'No, keep it'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Send request to delete category
                    fetch('../admin/src/process/delete_category.php?id=' + categoryId, {
                            method: 'GET'
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.status === 'success') {
                                Swal.fire(
                                    'Deleted!',
                                    data.message, // Show success message from response
                                    'success'
                                ).then(() => {
                                    // Reload the page after a successful delete
                                    location.reload();
                                });
                            } else {
                                Swal.fire(
                                    'Error!',
                                    data.message, // Show error message from response
                                    'error'
                                );
                            }
                        })
                        .catch(error => {
                            Swal.fire(
                                'Error!',
                                'There was a problem deleting the category.',
                                'error'
                            );
                        });
                }
            });
        }


        // Function to handle viewing item details and setting the category ID dynamically
        // Fetch and display items for the given category
        // Fetch and display items for the given category
        function viewItems(categoryId) {
            // Show the modal
            const viewItemsModal = new bootstrap.Modal(document.getElementById("viewItemsModal"));
            viewItemsModal.show();

            // Fetch the items for the given category
            fetch(`../admin/src/process/get_category_details.php?category_id=${categoryId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        let itemsHtml = ''; // Initialize empty HTML string for items

                        data.items.forEach(item => {
                            itemsHtml += `
                        <tr>
                            <td>${item.item_no}</td>
                            <td>${item.item_name}</td>
                            <td>${item.unit_of_measurement}</td>
                            <td>
                            <button class="btn btn-warning btn-sm" onclick="editItem('${item.item_no}')">
    <i class="fas fa-edit"></i> Edit
</button>
<button class="btn btn-danger btn-sm" onclick="deleteItem('${item.item_no}')">
    <i class="fas fa-trash-alt"></i> Delete
</button>

                            </td>
                        </tr>
                    `;
                        });

                        // Insert the generated rows into the table body
                        document.getElementById("viewItemsList").innerHTML = itemsHtml;
                    } else {
                        document.getElementById("viewItemsList").innerHTML = "<tr><td colspan='4'>No items found for this category.</td></tr>";
                    }
                })
                .catch(error => {
                    console.error("Error fetching items:", error);
                    document.getElementById("viewItemsList").innerHTML = "<tr><td colspan='4'>There was an error fetching the items.</td></tr>";
                });
            // Reload the page when the "View Items" modal is closed
            const viewItemsModalElement = document.getElementById('viewItemsModal');
            viewItemsModalElement.addEventListener('hidden.bs.modal', function() {
                location.reload(); // Reload the page when the modal is closed
            });

        }



        function editCategory(categoryId) {
            // Fetch the category details from the server
            fetch(`../admin/src/process/get_category.php?id=${categoryId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        const category = data.category;
                        // Populate the edit form fields with category details
                        document.getElementById('category_id').value = category.category_id;
                        document.getElementById('edit_category_name').value = category.category_name;
                        document.getElementById('edit_category_description').value = category.description; // Ensure this is populated
                    } else {
                        Swal.fire('Error', 'Failed to fetch category data', 'error');
                    }
                });
        }

        // Handle form submission for editing the category
        const editForm = document.getElementById('editCategoryForm');
        editForm.addEventListener('submit', function(event) {
            event.preventDefault();

            const formData = new FormData(editForm);

            // Send the form data to the server for updating the category
            fetch('../admin/src/process/edit_category.php', {
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
                            // Close the modal
                            const modal = bootstrap.Modal.getInstance(document.getElementById('editCategoryModal'));
                            if (modal) {
                                modal.hide();
                            }
                            location.reload(); // Reload the page after success
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


        const form = document.getElementById('addCategoryForm');
        form.addEventListener('submit', function(event) {
            event.preventDefault();
            const formData = new FormData(form);

            fetch('../admin/src/process/add_category.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Category added successfully',
                            showConfirmButton: true,
                            confirmButtonText: 'OK',
                            timer: 2000
                        }).then(() => {
                            // Close the modal
                            const modal = bootstrap.Modal.getInstance(document.getElementById('addCategoryModal'));
                            if (modal) {
                                modal.hide();
                            }

                            // Select the table and tbody to append the new row to the bottom
                            const table = document.getElementById('categoryTable'); // Replace with your table's ID
                            const tbody = table.querySelector('tbody');

                            // Create a new row with the data returned from the PHP script
                            const newRow = document.createElement('tr');
                            newRow.innerHTML = `
                        <td>${data.category_no}</td>
                        <td>${data.category_name}</td>
                        <td>${data.description}</td>
                        <td>${new Date().toLocaleString()}</td>
                    `;

                            // Append the new row to the bottom of the tbody
                            tbody.appendChild(newRow);

                            // Reload the page after a slight delay
                            setTimeout(() => {
                                window.location.reload(); // Ensure this happens after row insertion
                            }, 1000); // Delay to ensure smooth appending
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error adding category',
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
        // Function to open the "Edit Item" modal with item details
        function editItem(itemNo) {
            // Fetch the item details by item_no
            fetch(`../admin/src/process/get_item_details.php?item_no=${itemNo}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Populate the form fields with the fetched item data
                        document.getElementById("edit_item_id").value = data.item_id;
                        document.getElementById("edit_item_no").value = data.item_no; // Item No. is readonly
                        document.getElementById("edit_item_name").value = data.item_name;
                        document.getElementById("edit_unit_of_measurement").value = data.unit_of_measurement;

                        // Show the Edit Item Modal
                        const editItemModal = new bootstrap.Modal(document.getElementById("editItemModal"));
                        editItemModal.show();
                    } else {
                        alert("Error fetching item details.");
                    }
                })
                .catch(error => {
                    console.error("Error fetching item details:", error);
                    alert("There was an error fetching the item details.");
                });
        }

        // Handle the form submission for editing an item
        document.getElementById("editItemForm").addEventListener("submit", function(event) {
            event.preventDefault(); // Prevent default form submission

            const itemId = document.getElementById("edit_item_id").value;
            const itemName = document.getElementById("edit_item_name").value;
            const unitOfMeasurement = document.getElementById("edit_unit_of_measurement").value;

            // Prepare form data to send to the server
            const formData = new FormData();
            formData.append("item_id", itemId);
            formData.append("item_name", itemName);
            formData.append("unit_of_measurement", unitOfMeasurement);

            // Send the updated data to the server using fetch
            fetch("../admin/src/process/edit_item.php", {
                    method: "POST",
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Close the modal
                        const editItemModal = new bootstrap.Modal(document.getElementById("editItemModal"));
                        editItemModal.hide();

                        // Reload the page to reflect the changes
                        location.reload();
                    } else {
                        alert("Error saving changes: " + data.message);
                    }
                })
                .catch(error => {
                    console.error("Error updating item:", error);
                    alert("There was an error saving the changes.");
                });
        });

        function deleteItem(itemNo) {
            // Use Swal for confirmation before deleting
            Swal.fire({
                title: 'Are you sure?',
                text: "Do you really want to delete this item?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'No, cancel!',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    // Send the request to delete the item using fetch
                    fetch("../admin/src/process/delete_item.php", {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/x-www-form-urlencoded",
                            },
                            body: `item_no=${encodeURIComponent(itemNo)}`
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                // Show success message using Swal
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Deleted!',
                                    text: 'The item has been deleted successfully.',
                                });

                                // Reload the page after successful deletion
                                setTimeout(function() {
                                    location.reload(); // Reload the page to reflect changes
                                }, 1500); // Wait 1.5 seconds before reloading

                            } else {
                                // Show error message using Swal
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error!',
                                    text: data.message || 'Something went wrong.',
                                });
                            }
                        })
                        .catch(error => {
                            // Show error message using Swal
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: 'There was an error with the request.',
                            });
                        });
                }
            });
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</body>

</html>