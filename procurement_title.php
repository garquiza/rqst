<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include database connection
require_once '../admin/src/config/database.php';

// Handle form submission for updating procurement title
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $page = $_POST['page'];
    $title = $_POST['title'];
    $subtitle = $_POST['subtitle'];

    $query = "UPDATE procurement_titles SET title = ?, subtitle = ? WHERE page = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('sss', $title, $subtitle, $page);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        $message = "Title updated successfully.";
    } else {
        $message = "No changes made.";
    }
}

// Fetch procurement titles
$query = "SELECT * FROM procurement_titles";
$result = $conn->query($query);
$titles = $result->fetch_all(MYSQLI_ASSOC);

// Mapping PHP file names to user-friendly labels
$page_titles = [
    'app.php' => 'Annual Procurement Plan',
    'ppmp_list.php' => 'PPMP List (Admin)',
    'pr.php' => 'Admin - Purchase Request List',
    'pmf.php' => 'Procurement Modality Approval Form',
    'rfq.php' => 'Request for Quotation (RFQ)',
    'aoq.php' => 'Abstract of Quotation',
    'reso.php' => 'Resolution Form',
    'noa.php' => 'Notice of Award',
    'ntp.php' => 'Notice to Proceed',
    'po.php' => 'Purchase Order (PO)'
];

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Procurement Title Settings</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <style>
        .content {
            margin-left: 250px;
            padding: 20px;
        }

        .form-container {
            max-width: 700px;
            margin: auto;
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .header-bar {
            background-color: #007bff;
            color: white;
            padding: 15px 20px;
            border-radius: 8px 8px 0 0;
            margin-bottom: -20px;
            box-shadow: 0 3px 5px rgba(0, 0, 0, 0.1);
        }

        .header-bar h3 {
            margin: 0;
        }
    </style>
</head>

<body>
    <div class="d-flex">
        <!-- Include Sidebar -->
        <?php include 'sidebar.php'; ?>

        <!-- Main Content -->
        <div class="content flex-grow-1">
            <div class="form-container">
                <!-- Header Bar -->
                <div class="header-bar">
                    <h3>Procurement Title Settings</h3>
                    <p class="mb-0">Manage procurement page titles and subtitles</p>
                </div>

                <!-- Form Content -->
                <div class="p-4">
                    <!-- Success/Error Messages -->
                    <?php if (isset($message)): ?>
                        <div class="alert alert-info">
                            <?= $message; ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="page" class="form-label">Page</label>
                            <select class="form-select" name="page" id="page">
                                <?php foreach ($titles as $row): ?>
                                    <option value="<?= $row['page']; ?>">
                                        <!-- Check if the page has a corresponding label in the mapping array -->
                                        <?= isset($page_titles[$row['page']]) ? $page_titles[$row['page']] : ucfirst(str_replace('_', ' ', $row['page'])); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="title" class="form-label">Title</label>
                            <input type="text" class="form-control" id="title" name="title" required>
                        </div>
                        <div class="mb-3">
                            <label for="subtitle" class="form-label">Subtitle</label>
                            <input type="text" class="form-control" id="subtitle" name="subtitle">
                        </div>
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">Update</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>