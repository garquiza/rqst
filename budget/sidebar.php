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
    </style>
</head>

<body>
    <nav class="sidebar">
        <h5 class="text-center"><i class="fas fa-cogs"></i> BUDGET Panel</h5>
        <ul>
            <li class="<?= $current_page === 'dashboard.php' ? 'active' : '' ?>">
                <a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            </li>
            <li class="<?= $current_page === 'app.php' ? 'active' : '' ?>">
                <a href="app.php"><i class="fas fa-file-alt"></i> APP</a>
            </li>
            <li class="<?= $current_page === 'pr.php' ? 'active' : '' ?>">
                <a href="pr.php"><i class="fas fa-receipt"></i> PR</a>
            </li>
            <li class="<?= $current_page === 'pmr.php' ? 'active' : '' ?>">
                <a href="pmr.php"><i class="fas fa-chart-line"></i> PMR</a>
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
        function confirmLogout() {
            Swal.fire({
                title: 'Are you sure you want to logout?',
                text: 'You will need to log in again to access the Budget panel.',
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