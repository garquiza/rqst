<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.6.0/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link rel="stylesheet" href="src/css/login.css">
</head>

<body>
    <div class="container animate__animated animate__fadeInUp">
        <div class="form-section">
            <div class="h3">
                <span>ReQuest: Automated Purchase Request System</span>
            </div>
            <p>Sign in to continue</p>
            <form id="signInForm">
                <div class="mb-3">
                    <label for="email" class="form-label">EMAIL</label>
                    <input type="email" class="form-control" id="email" placeholder="your-email@gmail.com" required>
                    <div id="emailError" class="error-message"></div>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">PASSWORD</label>
                    <div class="input-group">
                        <input type="password" class="form-control" id="password" placeholder="your-password" required>
                        <button class="btn btn-outline-secondary" type="button" id="togglePassword" aria-label="Toggle Password Visibility">
                            <i class="fas fa-eye" id="passwordIcon"></i>
                        </button>
                    </div>
                    <div id="passwordError" class="error-message"></div>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="rememberMe">
                        <label class="form-check-label" for="rememberMe">Remember me</label>
                    </div>
                    <a href="../admin/forgot.php">Forgot Password?</a>
                </div>
                <button type="submit" class="btn btn-signin">SIGN IN</button>
            </form>

            <div class="row mt-4">
                <div class="col text-center">
                    <a href="../admin/login.php" class="btn btn-outline-dark w-100 active  ">Admin User</a>
                </div>
                <div class="col text-center">
                    <a href="../bac/login.php" class="btn btn-outline-dark w-100">BAC User</a>
                </div>
                <div class="col text-center">
                    <a href="../budget/login.php" class="btn btn-outline-dark w-100">Budget User</a>
                </div>
                <div class="col text-center">
                    <a href="../end-user/login.php" class="btn btn-outline-dark w-100">End User</a>
                </div>
            </div>
        </div>
        <div class="image-section">
            <div class="image-placeholder">
                <i class="fas fa-image"></i>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.6.0/dist/sweetalert2.all.min.js"></script>
    <script>
        // Password visibility toggle
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');
        const passwordIcon = document.getElementById('passwordIcon');

        togglePassword.addEventListener('click', () => {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            passwordIcon.classList.toggle('fa-eye');
            passwordIcon.classList.toggle('fa-eye-slash');
        });

        // Handle login form submission
        document.getElementById('signInForm').addEventListener('submit', function(e) {
            e.preventDefault(); // Prevent default form submission

            // Get form values directly from input fields
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            const rememberMe = document.getElementById('rememberMe').checked;

            // Prepare data to send
            const formData = new FormData();
            formData.append('email', email);
            formData.append('password', password);
            formData.append('rememberMe', rememberMe);

            // Send data to the server via fetch
            fetch('src/process/process_login.php', {
                    method: 'POST',
                    body: formData,
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: data.message,
                        }).then(() => {
                            // Redirect to dashboard or the intended page
                            window.location.href = 'dashboard.php'; // Replace with your desired redirect
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: data.message,
                        });
                    }
                })
                .catch(error => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'An error occurred while processing your request.',
                    });
                    console.error('Error:', error);
                });
        });
    </script>

</body>

</html>