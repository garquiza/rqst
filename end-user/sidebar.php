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

<body>
    <nav class="sidebar">
        <h5 class="text-center" ><i class="fas fa-cogs"></i> End-User Panel</h5>
        <ul>
            <!-- Dashboard Link -->
            <li>
                <a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            </li>

            <!-- Purchase Request Link -->
            <li>
                <a href="pr.php"><i class="fas fa-receipt"></i> Purchase Request</a>
            </li>

            <!-- PPMP Dropdown -->
            <li class="dropdown">
                <a href="#"><i class="fas fa-clipboard"></i> PPMP <i class="fas fa-chevron-down ms-auto"></i></a>
                <ul>
                    <li><a href="ppmp_list.php"><i class="fas fa-list"></i> View List</a></li>
                </ul>
            </li>

            <!-- Settings Link -->
            <li>
                <a href="settings.php"><i class="fas fa-cog"></i> Settings</a>
            </li>

            <!-- Logout Link -->
            <li>
                <a href="#" class="logout" onclick="confirmLogout()"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </li>
        </ul>
    </nav>

    <!-- SweetAlert Script -->
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