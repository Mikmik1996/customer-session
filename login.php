<?php session_start(); ?>
<!DOCTYPE html>
<html>
<head>
  <title>WiiJump Philippines</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background-image: url("images/bg.jpg");
      background-size: cover;
      background-position: center;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      margin: 0;
    }
    .login-box {
      background: rgba(0, 90, 200, 0.9);
      border: 2px solid #007BFF;
      box-shadow: 0 6px 12px rgba(0,0,0,0.3);
      padding: 40px;
      border-radius: 10px;
      width: 400px;
      text-align: center;
    }
    .login-box h2 {
      margin-bottom: 25px;
      color: #fff;
      font-size: 28px;
      font-weight: bold;
    }
    .login-box input {
      width: 85%;
      padding: 12px;
      margin: 12px auto;
      display: block;
      border: 1px solid #ccc;
      border-radius: 5px;
      text-align: center;
      background-color: #e6f0ff;
      color: #000;
    }
    .login-box input:focus {
      border-color: #007BFF;
      outline: none;
      background-color: #f5faff;
    }
    .login-box button {
      width: 90%;
      padding: 12px;
      background: #007BFF;
      color: #fff;
      border: none;
      border-radius: 5px;
      font-weight: bold;
      cursor: pointer;
      margin-top: 20px;
    }
    .login-box button:hover {
      background: #0056b3;
    }
    .error-message {
      background: #f8d7da;
      color: #721c24;
      padding: 10px;
      border-radius: 5px;
      margin-bottom: 15px;
      font-size: 14px;
      transition: opacity 1s ease;
    }
  </style>
</head>
<body>
  <div class="login-box">
    <h2>WiiJump Philippines</h2>

    <!-- ✅ Error message with fade-out -->
    <?php if (isset($_GET['error']) && $_GET['error'] == 1): ?>
      <div id="error-message" class="error-message">
        ❌ Incorrect username or password
      </div>
    <?php endif; ?>

    <form action="authenticate.php" method="POST">
      <input type="text" name="username" placeholder="Username" required>
      <input type="password" name="password" placeholder="Password" required>
      <button type="submit">Login</button>
    </form>
  </div>

  <!-- ✅ Fade-out script -->
  <script>
    document.addEventListener("DOMContentLoaded", function() {
      const errorBox = document.getElementById("error-message");
      if (errorBox) {
        setTimeout(() => {
          errorBox.style.opacity = "0";
          setTimeout(() => errorBox.remove(), 1000); // remove after fade
        }, 3000); // show for 3 seconds before fading
      }
    });
  </script>
</body>
</html>

