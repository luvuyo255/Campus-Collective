<!doctype html> 
<html> 
  <head> 
    <meta charset="utf-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Campus Collective</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
      .error-msg {
        color: red;
        text-align: center;
        margin-top: 5px;
        margin-bottom: 10px;
        font-weight: bold;
        font-size: 14px;
      }
    </style>
  </head>
  
  <body class="body"> 
    <div class="login">
      <h1>Login</h1>
      <form action="login2.php" method="post">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
        <div>
          <input type="email" name="email" placeholder="Email Address" required>
          <?php
          $error = $_GET['error'] ?? '';
          if ($error === 'invalid_email') {
              echo '<div class="error-msg">Invalid email format.</div>';
          } elseif ($error === 'user_not_found') {
              echo '<div class="error-msg">No account found with that email.</div>';
          } elseif ($error === 'empty_fields') {
              echo '<div class="error-msg">Please fill in all fields.</div>';
          }
          ?>
        </div>
        <div>
          <input type="password" name="password" placeholder="Password" required>
          <?php
          if ($error === 'invalid_password') {
              echo '<div class="error-msg">Incorrect password. Please try again.</div>';
          } elseif ($error === 'invalid_request') {
              echo '<div class="error-msg">Invalid request method.</div>';
          }
          ?>
        </div>
        <button type="submit">Log In</button>
        <h3><a href="forgotPassword.php">Forgot your password?</a></h3>
      </form>

      <p>Don't have an account? <a href="createAccount.html">Create one</a></p>
      <br>
      <br>
      <a href="home.php" class="home-btn">Back to Home</a>
    </div>
  </body>
</html>