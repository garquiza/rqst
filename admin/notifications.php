<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Include database connection
require_once '../admin/src/config/database.php'; // Replace with your actual path

// Fetch notifications for the logged-in user
$user_id = $_SESSION['user_id'];
$query = "SELECT id, title, message, created_at, is_read FROM notifications WHERE user_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Count notifications
$total_notifications = $result->num_rows;
$new_notifications = 0;
$read_notifications = 0;

$notifications = [];
while ($row = $result->fetch_assoc()) {
    $notifications[] = $row;
    if (!$row['is_read']) {
        $new_notifications++;
    } else {
        $read_notifications++;
    }
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="src/css/pr.css">
    <link rel="stylesheet" href="src/css/notif.css">
    <style>

    </style>
</head>

<body>
    <div class="d-flex">
        <!-- Include Sidebar -->
        <?php include 'sidebar.php'; ?>

        <!-- Main Content -->
        <div class="content flex-grow-1 p-4 animate__animated animate__fadeInUp">
            <div class="header-card">
                <h2 class="mb-3">Notifications</h2>
            </div>
            <!-- Summary Cards -->
            <div class="row row-cols-1 row-cols-md-3 g-3 mb-4">
                <div class="col">
                    <div class="card text-center border-0 shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title text-primary">New Notifications</h5>
                            <p class="card-text display-4 text-primary fw-bold"> <?php echo $new_notifications; ?> </p>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="card text-center border-0 shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title text-primary">Read Notifications</h5>
                            <p class="card-text display-4 fw-bold"> <?php echo $read_notifications; ?> </p>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="card text-center border-0 shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title text-primary">Total Notifications</h5>
                            <p class="card-text display-4 fw-bold"> <?php echo $total_notifications; ?> </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Notification Cards -->
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Recent Notifications</h5>
                    <div>
                        <?php if (empty($notifications)): ?>
                            <div class="notification-card text-center">
                                No notifications available.
                            </div>
                        <?php else: ?>
                            <?php foreach ($notifications as $notification): ?>
                                <div class="notification-card">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="notification-header">
                                            <?php echo htmlspecialchars($notification['title']); ?>
                                        </span>
                                        <?php if (!$notification['is_read']): ?>
                                            <span class="badge badge-new">New</span>
                                            <button class="btn btn-link mark-as-read" data-id="<?php echo $notification['id']; ?>">Mark as Read</button>
                                        <?php endif; ?>
                                    </div>
                                    <p class="notification-body">
                                        <?php echo htmlspecialchars($notification['message']); ?>
                                    </p>
                                    <span class="notification-time">
                                        <?php
                                        $timeAgo = time() - strtotime($notification['created_at']);
                                        if ($timeAgo < 60) {
                                            echo $timeAgo . ' seconds ago';
                                        } elseif ($timeAgo < 3600) {
                                            echo floor($timeAgo / 60) . ' minutes ago';
                                        } elseif ($timeAgo < 86400) {
                                            echo floor($timeAgo / 3600) . ' hours ago';
                                        } else {
                                            echo floor($timeAgo / 86400) . ' days ago';
                                        }
                                        ?>
                                    </span>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const markAsReadButtons = document.querySelectorAll('.mark-as-read');

            markAsReadButtons.forEach(button => {
                button.addEventListener('click', () => {
                    const notificationId = button.getAttribute('data-id');

                    // Show confirmation dialog
                    showConfirmationDialog(notificationId, button);
                });
            });
        });

        function showConfirmationDialog(notificationId, button) {
            Swal.fire({
                title: 'Are you sure?',
                text: "Mark this notification as read?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, mark as read!'
            }).then((result) => {
                if (result.isConfirmed) {
                    markNotificationAsRead(notificationId, button);
                }
            });
        }

        function markNotificationAsRead(notificationId, button) {
            // Send AJAX request
            fetch('src/process/mark_as_read.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `id=${encodeURIComponent(notificationId)}`
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json(); // Parse JSON response
                })
                .then(data => {
                    console.log('Response from server:', data); // Log the response for debugging
                    handleResponse(data, button);
                })
                .catch(error => {
                    Swal.fire(
                        'Error!',
                        'Something went wrong. Please try again later.',
                        'error'
                    );
                    console.error('Error:', error); // Log the error for debugging
                });
        }

        function handleResponse(data, button) {
            if (data.status === 'success') {
                Swal.fire(
                    'Marked!',
                    data.message,
                    'success'
                ).then(() => {
                    // Update UI: Remove badge and button
                    button.remove(); // Remove the "Mark as Read" button
                    const badge = button.parentElement.querySelector('.badge-new');
                    if (badge) badge.remove();
                });
            } else {
                // Handle specific error messages
                Swal.fire(
                    'Error!',
                    data.message || 'An unexpected error occurred.',
                    'error'
                );
            }
        }
    </script>

</body>

</html>