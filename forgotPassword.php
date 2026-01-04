<?php
require 'yakuzaconnection.php';

// Encrypt function (must match registration)
function maz_encrypts($securityA){ 
    $ban = trim($securityA);
    $banL = strtolower($ban);
    $band_enc = $banL . "@PAMC37**";
    return $band_enc;
}

$step = 1;
$email = '';
$securityQ = '';
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Step 1: User needs to submit email to get security question
    if (isset($_POST['email']) && !isset($_POST['security_answer'])) {
        $email = trim($_POST['email']); //retrieve email
        $stmt = $conn->prepare("SELECT securityQ FROM users WHERE email = ?"); //get security question
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->bind_result($securityQ);
        if ($stmt->fetch()) {
            $step = 2; // Show security question form if email is found in DB.
        } else {
            $error = "No account found with that email."; //when we do not find your email. 
        }
        $stmt->close();
    }
    // Step 2: User needs to submit answer and new password now. 
    elseif (isset($_POST['email'], $_POST['security_answer'], $_POST['new_password'], $_POST['confirm_password'])) { //if we have everything we need.
        //asign the variables
        $email = trim($_POST['email']);
        $security_answer = trim($_POST['security_answer']);
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        if ($new_password !== $confirm_password) { //a bit of security handling.
            $error = "Passwords do not match.";
            $step = 2;
            $securityQ = $_POST['securityQ'];
        } else {
            // Get security answer from DB
            $stmt = $conn->prepare("SELECT securityA, securityQ FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->bind_result($db_securityA, $securityQ);
            if ($stmt->fetch()) {
                $stmt->close();
                if (maz_encrypts($security_answer) === $db_securityA) {  //the one you enter needs to be the same as the one youve put in before. 
                    // Update password
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    $update = $conn->prepare("UPDATE users SET password=? WHERE email=?");
                    $update->bind_param("ss", $hashed_password, $email); //change the old password.
                    if ($update->execute()) {
                        $success = "Your password has been reset. <a href='login.php'>Log in</a>";
                        $step = 3; //security quesiton successfuly answered now respond to user. 
                    } else {
                        $error = "An error occurred. Please try again.";
                        $step = 2; //email found now enter the password and answer the security question
                    }
                    $update->close();
                } else {
                    $error = "Incorrect answer to your security question."; // have not reached step 3. 
                    $step = 2;
                }
            } else {
                $error = "No account found with that email.";
                $step = 1; //havent even passed step 1. 
            }
        }
    }
}
?>

<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Password Recovery | Campus Collective</title>
  <link rel="stylesheet" href="styles.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <script src="YakuzaJS.js"></script>
</head>
<body class="body">
  <div class="recover">
    <h1>Password Recovery</h1>
    <?php if ($error): ?>
      <div class="error-msg"><?php echo $error; ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
      <div class="success-msg"><?php echo $success; ?></div>
    <?php endif; ?>

    <?php if ($step === 1): ?> <!--Could not find an email-->
      <form method="post">
        <input type="email" name="email" placeholder="Enter your email" required>
        <button type="submit">Get Security Question</button>
      </form>
    <?php elseif ($step === 2): ?> <!--email found now we want you to answer the security question & enter new password.-->
      <form method="post" id="resetForm">
        <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">
        <input type="hidden" name="securityQ" value="<?php echo htmlspecialchars($securityQ); ?>">
        <p><strong>Your question is:</strong> <?php echo htmlspecialchars($securityQ); ?></p>
        <input type="text" name="security_answer" placeholder="Answer to your security question" required>
        <input type="password" id="password" name="new_password" placeholder="New Password" required>
        <div id="PwdStren"></div>
        <input type="password" name="confirm_password" id="confirm_password" placeholder="Confirm New Password" required>
        <div id="confpwd"></div>
        <button type="submit">Reset Password</button>
      </form>
      <script>
        // Use the same validation as registration
        document.addEventListener("DOMContentLoaded", function() {
          if (typeof passwordTime === "function") passwordTime();
          if (typeof passwordConfirm === "function") passwordConfirm();
        });
      </script>
    <?php endif; ?>

    <a href="login.php">Back to Login</a>
  </div>
</body>
</html>
