<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Improved Sidebar</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet">
    <style>
        /* Sidebar Styles */
        .sidebar {
            width: 250px;
            height: 100vh;
            background-color: #ffffff;
            border-right: 1px solid #e6e6e6;
            padding: 20px 15px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
            position: fixed;
            overflow-y: auto;
            transition: all 0.3s ease-in-out;
        }

        .sidebar h5 {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 20px;
            text-transform: uppercase;
            color: #333;
            border-bottom: 2px solid #e6e6e6;
            padding-bottom: 10px;
            text-align: left;
        }

        .sidebar ul {
            padding: 0;
            list-style: none;
            margin: 0;
        }

        .sidebar ul li {
            margin-bottom: 15px;
            position: relative;
        }

        .sidebar ul li a {
            text-decoration: none;
            color: #444;
            font-size: 15px;
            font-weight: 500;
            display: flex;
            align-items: center;
            padding: 10px 12px;
            border-radius: 8px;
            transition: all 0.3s ease-in-out;
        }

        .sidebar ul li a:hover {
            background-color: #f0f8ff;
            color: #007bff;
            transform: translateX(5px);
        }

        .sidebar ul li a i {
            margin-right: 10px;
        }

        .sidebar ul li ul {
            margin-top: 5px;
            margin-left: 15px;
            list-style: none;
            display: none;
            padding-left: 10px;
            animation: fadeIn 0.3s ease-in-out;
        }

        .sidebar ul li ul li a {
            font-size: 14px;
            color: #555;
            padding: 8px;
            transition: all 0.2s ease-in-out;
        }

        .sidebar ul li ul li a:hover {
            color: #007bff;
        }

        .sidebar ul li.active ul {
            display: block;
        }

        .sidebar .logout {
            margin-top: auto;
            font-weight: bold;
            color: #ff4d4d;
        }

        .user-info {
            text-align: center;
            margin-bottom: 20px;
        }

        .user-info h6 {
            font-size: 16px;
            font-weight: bold;
            margin: 5px 0;
        }

        .user-info p {
            font-size: 14px;
            color: #666;
            margin: 0;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>

<?php
// Simulating logged-in user info
$first_name = $_SESSION['first_name'];
$last_name = $_SESSION['last_name'];

// Get the current active page
$current_page = basename($_SERVER['PHP_SELF']);
?>

<body>

    <body>
        <nav class="sidebar">
            <h5 class="text-center"><i class="fas fa-cogs"></i> Admin Panel</h5>
            <div class="user-info">
                <h6><?= htmlspecialchars($first_name . ' ' . $last_name); ?></h6>
                <p>Admin</p>
            </div>
            <ul>
                <li class="<?= $current_page === 'dashboard.php' ? 'active' : '' ?>">
                    <a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                </li>
                <li class="<?= $current_page === 'notifications.php' ? 'active' : '' ?>">
                    <a href="notifications.php">
                        <i class="fa-solid fa-envelope"></i> Notification
                        <?= isset($new_notifications) ? $new_notifications : ''; ?>
                    </a>
                </li>

                <li class="dropdown <?= in_array($current_page, ['app.php', 'ppmp_list.php', 'pr.php', 'pp.php', 'pmf.php', 'po_next.php', 'aoq_next.php', 'rfq.php', 'aoq.php', 'reso.php', 'noa.php', 'ntp.php', 'po.php', 'pmr.php']) ? 'active' : '' ?>">
                    <a href="#"><i class="fas fa-box"></i> Procurement <i class="fas fa-chevron-down ms-auto"></i></a>
                    <ul>
                        <li><a href="app.php" class="<?= $current_page === 'app.php' ? 'active' : '' ?>"><i class="fas fa-file-alt"></i> APP</a></li>
                        <li><a href="ppmp_list.php" class="<?= $current_page === 'ppmp_list.php' ? 'active' : '' ?>"><i class="fas fa-clipboard"></i> PPMP</a></li>
                        <li><a href="pr.php" class="<?= $current_page === 'pr.php' ? 'active' : '' ?>"><i class="fas fa-receipt"></i> PR</a></li>
                        <li><a href="pmf.php" class="<?= $current_page === 'pmaf.php' ? 'active' : '' ?>"><i class="fas fa-calendar-check"></i> PMAF</a></li>
                        <li><a href="rfq.php" class="<?= $current_page === 'rfq.php' ? 'active' : '' ?>"><i class="fas fa-handshake"></i> RFQ</a></li>
                        <li><a href="aoq.php" class="<?= $current_page === 'aoq.php' ? 'active' : '' ?>"><i class="fas fa-table"></i> AOQ</a></li>
                        <li><a href="reso.php" class="<?= $current_page === 'reso.php' ? 'active' : '' ?>"><i class="fas fa-file-signature"></i> RESO</a></li>
                        <li><a href="noa.php" class="<?= $current_page === 'noa.php' ? 'active' : '' ?>"><i class="fas fa-award"></i> NOA</a></li>
                        <li><a href="ntp.php" class="<?= $current_page === 'ntp.php' ? 'active' : '' ?>"><i class="fas fa-paper-plane"></i> NTP</a></li>
                        <li><a href="po.php" class="<?= $current_page === 'po.php' ? 'active' : '' ?>"><i class="fas fa-file-contract"></i> PO</a></li>
                        <li><a href="pmr.php" class="<?= $current_page === 'pmr.php' ? 'active' : '' ?>"><i class="fas fa-chart-line"></i> PMR</a></li>
                    </ul>
                </li>
                <li class="dropdown <?= in_array($current_page, ['create_user.php', 'user_management.php', 'create_end_user.php', 'create_bac_user.php', 'create_budget_user.php', 'manage_end_user.php', 'manage_bac_user.php', 'manage_budget_user.php']) ? 'active' : '' ?>">
                    <a href="#"><i class="fas fa-user"></i> User <i class="fas fa-chevron-down ms-auto"></i></a>
                    <ul>
                        <li><a href="create_user.php" class="<?= $current_page === 'create_user.php' ? 'active' : '' ?>"><i class="fas fa-user-plus"></i> Create User</a></li>
                        <li><a href="user_management.php" class="<?= $current_page === 'user_management.php' ? 'active' : '' ?>"><i class="fas fa-users-cog"></i> User Management</a></li>
                    </ul>
                </li>
                <li class="<?= $current_page === 'history.php' ? 'active' : '' ?>">
                    <a href="history.php"><i class="fas fa-history"></i> History Logs</a>
                </li>
                <li class="<?= $current_page === 'inventory.php' ? 'active' : '' ?>">
                    <a href="inventory.php"><i class="fas fa-box"></i> Inventory</a>
                </li>
                <li class="<?= $current_page === 'settings.php' ? 'active' : '' ?>">
                    <a href="settings.php"><i class="fas fa-cog"></i> Settings</a>
                </li>
                <li>
                    <a href="#" class="logout" onclick="confirmLogout()"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </li>
            </ul>
        </nav>

        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
            // Dropdown functionality
            document.querySelectorAll('.dropdown > a').forEach((dropdown) => {
                dropdown.addEventListener('click', (e) => {
                    e.preventDefault();
                    const parent = dropdown.parentElement;
                    parent.classList.toggle('active');
                });
            });

            function confirmLogout() {
                Swal.fire({
                    title: 'Are you sure you want to logout?',
                    text: 'You will need to log in again to access the admin panel.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, logout',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Redirect to logout.php
                        window.location.href = 'logout.php';
                    }
                });
            }
        </script>
    </body>

</html>