<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="src/css/register.css">
</head>

<body class="animate__animated animate__fadeInRight">
    <div class="container">
        <div class="form-section">
            <div class="h3">
                <span>ReQuest: Automated Purchase Request System</span>
            </div>
            <p>Create a new account</p>
            <form id="signUpForm" class="needs-validation" novalidate>
                <div class="mb-3">
                    <label for="firstName" class="form-label">FIRST NAME</label>
                    <input type="text" class="form-control" id="firstName" name="first_name" placeholder="Firstname" required>
                    <div class="invalid-feedback">Please provide your first name.</div>
                </div>
                <div class="mb-3">
                    <label for="lastName" class="form-label">LAST NAME</label>
                    <input type="text" class="form-control" id="lastName" name="last_name" placeholder="Lastname" required>
                    <div class="invalid-feedback">Please provide your last name.</div>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">EMAIL</label>
                    <input type="email" class="form-control" id="email" name="email" placeholder="your-email@gmail.com" required>
                    <div class="invalid-feedback">Please provide a valid email.</div>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">PASSWORD</label>
                    <div class="input-group">
                        <input type="password" class="form-control" id="password" name="password" placeholder="your-password" required>
                        <button class="btn btn-outline-secondary" type="button" id="togglePassword" aria-label="Toggle Password Visibility">
                            <i class="fas fa-eye" id="passwordIcon"></i>
                        </button>
                        <div class="invalid-feedback">Password must be at least 6 characters.</div>
                    </div>
                </div>
                <button type="submit" class="btn btn-signup">SIGN UP</button>
            </form>
        </div>
        <div class="image-section animate__animated animate__fadeInRight">
            <div class="image-placeholder">
                <i class="fas fa-image"></i>
            </div>
        </div>
    </div>

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

        document.getElementById('signUpForm').addEventListener('submit', function(e) {
            e.preventDefault(); // Prevent default form submission

            const formData = new FormData(this);

            // Send data to the server via fetch
            fetch('src/process/process_register.php', {
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
                            // Redirect to login page or dashboard
                            window.location.href = 'login.php';
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