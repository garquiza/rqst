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
        $success = true; // Flag for SweetAlert to show
    } else {
        $message = "No changes made.";
        $success = false;
    }
}

// Fetch procurement titles from database
$query = "SELECT * FROM procurement_titles";
$result = $conn->query($query);
$titles = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Procurement Title Settings</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.6.5/dist/sweetalert2.min.css"> <!-- SweetAlert2 CSS -->
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
                    <!-- Form Content -->
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="title" class="form-label">Title</label>
                            <select class="form-select" name="page" id="page" onchange="updateSubtitleAndTitle(this)">
                                <option value="">Select a title</option>
                                <?php foreach ($titles as $row): ?>
                                    <option value="<?= $row['page']; ?>"
                                        data-title="<?= htmlspecialchars($row['title']); ?>"
                                        data-subtitle="<?= htmlspecialchars($row['subtitle']); ?>"
                                        <?= isset($_POST['page']) && $_POST['page'] == $row['page'] ? 'selected' : ''; ?>>
                                        <?= ucfirst(str_replace('_', ' ', $row['page'])); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="title" class="form-label">Title (Editable)</label>
                            <input type="text" class="form-control" id="editable-title" name="title" value="" required>
                        </div>

                        <div class="mb-3">
                            <label for="subtitle" class="form-label">Subtitle</label>
                            <input type="text" class="form-control" id="subtitle" name="subtitle" value="">
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.6.5/dist/sweetalert2.min.js"></script> <!-- SweetAlert2 JS -->

    <script>
        // Function to update both title and subtitle fields based on the selected option
        function updateSubtitleAndTitle(selectElement) {
            const selectedOption = selectElement.options[selectElement.selectedIndex];
            const title = selectedOption.getAttribute('data-title');
            const subtitle = selectedOption.getAttribute('data-subtitle');

            // Set the title and subtitle fields
            document.getElementById('editable-title').value = title;
            document.getElementById('subtitle').value = subtitle;
        }

        // Trigger SweetAlert if success flag is set
        <?php if (isset($success)): ?>
            Swal.fire({
                icon: '<?= $success ? "success" : "error" ?>',
                title: '<?= $success ? "Success" : "Error" ?>',
                text: '<?= $message; ?>',
                confirmButtonText: 'OK'
            });
        <?php endif; ?>
    </script>
</body>

</html>