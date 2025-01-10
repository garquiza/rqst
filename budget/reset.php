<?php 
session_start();
require_once('./src/config/database.php'); 

if(!isset($_GET['secret'])){
  header("location: ./login.php");
  exit;
}

$secret = $_GET['secret'];
 
$sql = "SELECT email FROM resets WHERE code = ? AND role = 3 ORDER BY timestamp DESC LIMIT 1"; 

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $secret);

if($stmt->execute()){
  $stmt->store_result();
  if($stmt->num_rows > 0){
    $stmt->bind_result($email);
    $stmt->fetch();
  }else{
    header("location: ./login.php");
    exit;
  }
}

if(isset($_POST['password'])){
  $password = $_POST['password'];
  $confirm_password = $_POST['confirm_password'];

  if($password != $confirm_password){
    $_SESSION['password_not_matched'] = true;
    header("location: ./reset.php?secret=".$secret);
    exit;
  }

  $sql = "SELECT * FROM budget_users WHERE email = ?"; 

  $stmt = $conn->prepare($sql);
  $stmt->bind_param("s", $email);

  if($stmt->execute()){
    $stmt->store_result();
    if($stmt->num_rows > 0){
      $stmt->close();

      $hash_pass = password_hash($password, PASSWORD_BCRYPT);

      $sql = "UPDATE budget_users SET password = ? WHERE email = ?";  

      $stmt = $conn->prepare($sql);
      $stmt->bind_param("ss", $hash_pass, $email);

      if($stmt->execute()){
        $_SESSION['reset_success'] = true;
      }
    }else{
      header("location: ./login.php");
      exit;
    }
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Reset Password</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-gray-100 h-screen flex items-center justify-center">
  <div class="w-full max-w-md bg-white shadow-md rounded-lg p-6">
    <h2 class="text-2xl font-bold text-gray-800 text-center mb-6">Reset Password</h2>
    <p class="text-gray-600 text-center mb-4">
      Enter a new password to reset your account.
    </p>
    <form action="" method="POST">
      <div class="mb-4">
        <label for="password" class="block text-sm font-medium text-gray-700 mb-2">New Password</label>
        <input 
          type="password" 
          id="password" 
          name="password" 
          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" 
          placeholder="Enter your new password" 
          required 
        />
      </div>
      <div class="mb-4">
        <label for="confirm-password" class="block text-sm font-medium text-gray-700 mb-2">Confirm Password</label>
        <input 
          type="password" 
          id="confirm-password" 
          name="confirm_password" 
          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" 
          placeholder="Confirm your new password" 
          required 
        />
      </div>
      <button 
        type="submit" 
        class="w-full bg-black text-white py-2 px-4 rounded-lg hover:bg-gray-800 focus:outline-none focus:ring focus:ring-gray-500">
        Reset Password
      </button>
    </form>
    
  </div>
</body>
<?php
    if (isset($_SESSION['password_not_matched']) && $_SESSION['password_not_matched']) {
        echo "
        <script>
        Swal.fire({
            icon: 'error',
            title: 'Password not Matched',
            text: 'Please check your passsword and confirm password.',
            confirmButtonText: 'OK'
        });
        </script>
        ";
        unset($_SESSION['password_not_matched']);
    } else if(isset($_SESSION['reset_success']) && $_SESSION['reset_success']) {
      echo "
        <script>
        Swal.fire({
            icon: 'success',
            title: 'Password Reset Success',
            text: 'You may now login using your new password.',
            confirmButtonText: 'OK'
        }).then((result) => {
            window.location.href = './login.php';
        });
        </script>
        ";
        unset($_SESSION['reset_success']);
    }
?>
</html>