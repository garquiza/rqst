<?php
// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Include database connection
require_once '../admin/src/config/pdo.php';

// Fetch budget users from the database
$sql = "SELECT * FROM budget_users";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$budget_users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Pagination setup
$items_per_page = 20;
$total_users = count($budget_users);
$total_pages = ceil($total_users / $items_per_page);
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($current_page - 1) * $items_per_page;
$paginated_users = array_slice($budget_users, $offset, $items_per_page);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Budget Users</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="src/css/pr.css">
    <style>
        .content {
            margin-left: 250px;
            padding: 20px;
        }

        .search-bar {
            margin-bottom: 20px;
        }

        .pagination {
            justify-content: center;
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
                <h1 class="mb-4">Manage Budget Users</h1>
                <p class="mb-0">Below is the list of Budget users.</p>
            </div>

            <!-- Search Bar -->
            <div class="search-bar">
                <input type="text" id="search" class="form-control" placeholder="Search by name or email" onkeyup="filterUsers()">
            </div>

            <div class="table-responsive">
                <table class="table table-striped" id="userTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>First Name</th>
                            <th>Last Name</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($paginated_users) > 0): ?>
                            <?php foreach ($paginated_users as $user): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($user['id']); ?></td>
                                    <td><?php echo htmlspecialchars($user['first_name']); ?></td>
                                    <td><?php echo htmlspecialchars($user['last_name']); ?></td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td>
                                        <?php if ($user['status'] === 'activate'): ?>
                                            <span class="badge bg-success">Activated</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Disabled</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <button class="btn btn-outline-success btn-sm" title="Activate" onclick="updateStatus(<?php echo $user['id']; ?>, 'activate');">
                                            <i class="fa fa-check"></i>
                                        </button>
                                        <button class="btn btn-outline-secondary btn-sm" title="Disable" onclick="updateStatus(<?php echo $user['id']; ?>, 'disabled');">
                                            <i class="fa fa-times"></i>
                                        </button>
                                        <a href="edit_bac_user.php?id=<?php echo $user['id']; ?>" class="btn btn-warning btn-sm" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="#" class="btn btn-danger btn-sm" title="Delete" onclick="confirmDelete(<?php echo $user['id']; ?>); return false;">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center">No Budget users found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <nav aria-label="Page navigation">
                <ul class="pagination">
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?php echo $i === $current_page ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function filterUsers() {
            const input = document.getElementById('search');
            const filter = input.value.toLowerCase();
            const table = document.getElementById('userTable');
            const tr = table.getElementsByTagName('tr');

            for (let i = 1; i < tr.length; i++) { // Start from 1 to skip the header row
                const td = tr[i].getElementsByTagName('td');
                let found = false;

                for (let j = 1; j < td.length - 1; j++) { // Skip ID and Actions columns
                    if (td[j].textContent.toLowerCase().indexOf(filter) > -1) {
                        found = true;
                        break;
                    }
                }

                tr[i].style.display = found ? '' : 'none'; // Show or hide the row
            }
        }

        function confirmDelete(userId) {
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Send AJAX request to delete the user
                    fetch('src/process/delete_budget.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({
                                id: userId
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire(
                                    'Deleted!',
                                    'The user has been deleted.',
                                    'success'
                                ).then(() => {
                                    // Reload the page to see the changes
                                    location.reload();
                                });
                            } else {
                                Swal.fire(
                                    'Error!',
                                    data.message || 'There was a problem deleting the user.',
                                    'error'
                                );
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            Swal.fire(
                                'Error!',
                                'There was a problem with the request.',
                                'error'
                            );
                        });
                }
            });
        }

        function updateStatus(userId, status) {
            fetch('update_budget_status.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        id: userId,
                        status: status
                    }),
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        Swal.fire({
                            title: 'Success!',
                            text: `User status updated to ${status}.`,
                            icon: 'success',
                            confirmButtonText: 'Okay',
                        }).then(() => {
                            location.reload(); // Refresh the page to reflect the changes
                        });
                    } else {
                        Swal.fire({
                            title: 'Error!',
                            text: data.message || 'Failed to update status.',
                            icon: 'error',
                            confirmButtonText: 'Okay',
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        title: 'Error!',
                        text: 'An unexpected error occurred.',
                        icon: 'error',
                        confirmButtonText: 'Okay',
                    });
                });
        }
    </script>
    <script>
        function filterUsers() {
            const input = document.getElementById('search');
            const filter = input.value.toLowerCase();
            const table = document.getElementById('userTable');
            const tr = table.getElementsByTagName('tr');

            for (let i = 1; i < tr.length; i++) { // Start from 1 to skip the header row
                const td = tr[i].getElementsByTagName('td');
                let found = false;

                for (let j = 1; j < td.length - 1; j++) { // Skip ID and Actions columns
                    if (td[j].textContent.toLowerCase().indexOf(filter) > -1) {
                        found = true;
                        break;
                    }
                }

                tr[i].style.display = found ? '' : 'none'; // Show or hide the row
            }
        }
    </script>
</body>

</html>