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

// Fetch end users from the database
$sql = "SELECT * FROM end_users";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$end_users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Pagination setup
$items_per_page = 20;
$total_users = count($end_users);
$total_pages = ceil($total_users / $items_per_page);
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($current_page - 1) * $items_per_page;
$paginated_users = array_slice($end_users, $offset, $items_per_page);

// Fetch the toggle state from the database
$query = "SELECT updates_enabled FROM settings WHERE id = 1";
$stmt = $pdo->prepare($query);
$stmt->execute();
$settings = $stmt->fetch(PDO::FETCH_ASSOC);
$toggleState = $settings['updates_enabled'] ?? 0; // Default to 0 if not found

// Handle POST request to update the toggle status
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the raw POST data
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    // Ensure the expected data exists
    if (isset($data['enableUpdates'])) {
        $enableUpdates = $data['enableUpdates'] ? 1 : 0;

        try {
            // Update the settings table to reflect the status of the toggle where ID=1
            $query = "UPDATE settings SET updates_enabled = :status WHERE id = 1";
            $stmt = $pdo->prepare($query);
            $stmt->execute([':status' => $enableUpdates]);

            // Return success response
            echo json_encode(['status' => 'success', 'message' => 'Status updated successfully']);
        } catch (PDOException $e) {
            // Return any error that occurs during the update
            echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
        }
    } else {
        // Return error response if 'enableUpdates' is missing
        echo json_encode(['status' => 'error', 'message' => 'Missing enableUpdates parameter']);
    }
    exit(); // Exit the script after processing the POST request
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage End Users</title>
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

        /* Custom style for toggle switch */
        .form-check-input {
            width: 60px;
            height: 34px;
            background-color: rgb(97, 128, 173);
            border-radius: 20px;
            position: relative;
            transition: background-color 0.3s ease;
        }

        .form-check-input:checked {
            background-color: #28a745 !important;
        }

        .form-check-input {
            transform: scale(1.5);
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
                <h1 class="mb-4">Manage End Users</h1>
                <p class="mb-0">Below is the list of End users.</p>
                <!-- Toggle Switch for Activating/Deactivating Updates -->
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="toggle-updates" onchange="toggleUpdates(this)" <?php echo $toggleState ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="toggle-updates">
                        Enable/Disable Update for PPMP
                    </label>
                </div>

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
                            <th>Sector</th>
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
                                    <td><?php echo htmlspecialchars($user['sector']); ?></td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td>
                                        <?php if ($user['status'] === 'activate'): ?>
                                            <span class="badge bg-success">Activated</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Disabled</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="edit_end_user.php?id=<?php echo $user['id']; ?>" class="btn btn-warning btn-sm" title="Edit">
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
                                <td colspan="6" class="text-center">No End users found.</td>
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
                    fetch('src/process/delete_end_user.php', {
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
        // JavaScript function to toggle updates
        function toggleUpdates(switchElement) {
            const isEnabled = switchElement.checked; // Get the current state of the switch (enabled/disabled)

            // Send an AJAX request to the server
            fetch('', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        enableUpdates: isEnabled // Send the status of the switch
                    }),
                })
                .then(response => {
                    // Check if the response is OK (status 200)
                    if (!response.ok) {
                        throw new Error(`HTTP error! Status: ${response.status}`);
                    }
                    return response.json(); // Parse the response as JSON
                })
                .then(data => {
                    // Check if the server returned success
                    if (data.status === 'success') {
                        Swal.fire({
                            title: 'Success!',
                            text: `Updates have been ${isEnabled ? 'enabled' : 'disabled'}.`,
                            icon: 'success',
                            confirmButtonText: 'OK'
                        });
                    } else {
                        // Server returned an error message
                        console.error('Server Error:', data.message);
                        Swal.fire({
                            title: 'Error!',
                            text: `Error updating status: ${data.message}`,
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    }
                })
                .catch(error => {
                    // Catch any errors during the fetch process
                    console.error('Fetch Error:', error);
                    Swal.fire({
                        title: 'Failed!',
                        text: 'Failed to update status. Check console for details.',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                });
        }
    </script>
</body>

</html>