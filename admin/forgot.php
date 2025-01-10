<?php
session_start();
require_once('../mailer.php');
require_once('./src/config/database.php'); 

if(isset($_POST['email'])){
    extract($_POST);
        
    $code = mt_rand(100000, 999999);

    $link = "http://localhost/rqst/admin/reset.php?secret=$code"; 

    $sql = "INSERT INTO resets (email, code, role) VALUES (?, ?, 1)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $email, $code);

    if($stmt->execute()){
        $message = "
            <html>
                <body>
                    <p>Dear User,</p>
                    <p>We received a request to reset the password for your account. To proceed, please click the link below:</p>
                    <a href='$link'>Reset Your Password</a>
                    <p>If you did not initiate this request, kindly disregard this email. Please note that the link will expire in 10 minutes.</p>
                    <p>Thank you<br>
                </body>
            </html>
        ";

        $mail->addAddress($email);
        $mail->Subject = 'Password Reset Request';
        $mail->Body = $message;

        if (!$mail->send()) {
            echo 'Message could not be sent.';
            echo 'Mailer Error: ' . $mail->ErrorInfo;
        } else {
            $_SESSION['reset_email_sent'] = true;
        }
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Forgot Password</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-gray-100 h-screen flex items-center justify-center">
  <div class="w-full max-w-sm bg-white shadow-md rounded-lg p-6">
    <h2 class="text-2xl font-bold text-gray-800 text-center mb-6">Forgot Password</h2>
    <p class="text-gray-600 text-center mb-4">
      Enter your email address and we’ll send you a link to reset your password.
    </p>
    <form action="" method="POST">
      <div class="mb-4">
        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
        <input 
          type="email" 
          id="email" 
          name="email" 
          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" 
          placeholder="Enter your email" 
          required 
        />
      </div>
      <button 
        type="submit" 
        class="w-full bg-black text-white py-2 px-4 rounded-lg hover:bg-gray-800 focus:outline-none focus:ring focus:ring-gray-500">
        Send Reset Link
      </button>
    </form>
    <div class="text-center mt-4">
      <a href="/login" class="text-blue-500 hover:underline">Back to Login</a>
    </div>
  </div>
</body>
<?php
    if (isset($_SESSION['reset_email_sent']) && $_SESSION['reset_email_sent']) {
        echo "
        <script>
        Swal.fire({
            icon: 'success',
            title: 'Password Reset Sent',
            text: 'Please check your email for further instructions.',
            confirmButtonText: 'OK'
        });
        </script>
        ";
        unset($_SESSION['reset_email_sent']);
    }
?>
</html>