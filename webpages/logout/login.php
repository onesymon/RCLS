<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/rotary/includes/config.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
 

    if (isset($_POST['login'])) {
        if (empty($email) || empty($password)) {
            $error_message = "Email and password are required!";
        }  else {
                $sqlMember = "SELECT * FROM members WHERE email = '$email'";
                $resultMember = $conn->query($sqlMember);

                if ($resultMember && $resultMember->num_rows === 1) {
                    $row = $resultMember->fetch_assoc();
                    if (password_verify($password, $row['password'])) {
                        $_SESSION['user_id'] = $row['id'];
                        $_SESSION['email'] = $row['email'];
                        $_SESSION['role'] = $row['role'];
                        $_SESSION['fullname'] = $row['fullname'];
                        $_SESSION['photo'] = $row['photo'];
                        $_SESSION['member_id'] = $row['id'];
                        header("Location: /rotary/announcements/announcements.php");
                        exit();
                    }
                }

                $error_message = "Invalid email or password!";
            }
        }
    } 
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Rotary Club System - Login</title>
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;600&display=swap');
    @import url('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css');

    * { box-sizing: border-box; }

    html {
      margin: 0;
      padding: 0;
      font-family: 'Poppins', sans-serif;
      background: linear-gradient(135deg, #003974 0%, #0a5ca9 40%, #f3c13a 100%);
      min-height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
      color: #333;
      overflow-x: hidden;
      overflow-y: auto;
    }

    .login-container {
      background: #fff;
      width: 90vw;
      max-width: 420px;
      border-radius: 18px;
      padding: 40px 30px;
      box-shadow: 0 12px 24px rgba(3, 55, 108, 0.25),
                  0 20px 40px -10px rgba(243, 193, 58, 0.35);
      text-align: center;
      position: relative;
      z-index: 1;
    }

  .logo-wrapper {
  background: #f3c13a;
  width: 20vw;
  height: 20vw;
  max-width: 100px;
  max-height: 100px;
  min-width: 60px;
  min-height: 60px;
  border-radius: 50%;
  margin: 0 auto 20px;
  box-shadow: inset 0 0 20px #fff9cc, 0 6px 12px rgba(243, 193, 58, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
}

.logo-wrapper i {
  font-size: 3vw;
  max-font-size: 48px;
  min-font-size: 28px;
  color: #003974;
}
@media (max-width: 768px) {
  .logo-wrapper {
    width: 70px;
    height: 70px;
  }

  .logo-wrapper i {
    font-size: 34px;
  }
}


    h1 {
      font-size: 2rem;
      color: #003974;
      margin-bottom: 10px;
    }

    .subtitle {
      font-size: 1rem;
      color: #606060;
      margin-bottom: 25px;
    }

    form {
      display: flex;
      flex-direction: column;
    }

    label {
      text-align: left;
      font-weight: 600;
      margin-bottom: 5px;
      font-size: 0.9rem;
    }

    .input-group {
      position: relative;
      margin-bottom: 20px;
    }

    input[type="text"],
    input[type="password"] {
      width: 100%;
      padding: 12px 40px 12px 14px;
      border: 2px solid #ddd;
      border-radius: 12px;
      font-size: 1rem;
      background: #fff;
      color: #333;
    }

    input:focus {
      border-color: #f3c13a;
      outline: none;
      box-shadow: 0 0 12px 2px #f3c13a80;
    }

    .input-group i {
      position: absolute;
      right: 14px;
      top: 64%;
      transform: translateY(-50%);
      color: #bbb;
      font-size: 1.2rem;
    }

    button {
      background: linear-gradient(135deg, #f3c13a 0%, #f5d65e 100%);
      color: #003974;
      font-weight: 700;
      border: none;
      padding: 14px 0;
      border-radius: 14px;
      font-size: 1.1rem;
      cursor: pointer;
      transition: 0.3s;
    }

    button:hover {
      background-position: right center;
      transform: translateY(-2px);
      box-shadow: 0 8px 16px rgba(243,193,58,0.6);
    }

    .forgot-link {
      margin-top: 16px;
      font-size: 0.9rem;
      color: #0a5ca9;
      text-decoration: underline;
      cursor: pointer;
    }

    .error-message {
      background: #d94f51;
      color: #fff;
      padding: 12px;
      border-radius: 8px;
      margin-bottom: 16px;
      font-size: 0.95rem;
    }
  </style>
</head>
<body>
<main class="login-container">
  <div class="logo-wrapper"><i class="fa-solid fa-gear"></i></div>
  <h1>Rotary Club of Lipa South</h1>
  <p class="subtitle">Welcome back, please login to your account</p>

  <?php if (isset($error_message)): ?>
    <div class="error-message"><?= htmlspecialchars($error_message) ?></div>
  <?php endif; ?>

  <form method="POST">
    <div class="input-group">
      <label for="username">Email</label>
      <input type="text" id="username" name="username" required placeholder="Enter your email">
      <i class="fa-regular fa-user"></i>
    </div>
    <div class="input-group">
      <label for="password">Password</label>
      <input type="password" id="password" name="password" required placeholder="Enter your password">
      <i class="fa-solid fa-lock"></i>
    </div>
    <input type="hidden" name="login" value="1">
    <button type="submit">Login</button>
  </form>

  <div class="forgot-link" onclick="alert('Password recovery is not implemented.')">Forgot password?</div>
</main>
<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous" defer></script>
</body>
</html>

