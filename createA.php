<?php
// Include the DB connection
require 'yakuzaconnection.php';

function showPage($message, $type = 'error') {
    // $type: 'error' or 'success'
    ?>
    <!doctype html>
    <html>
    <head>
        <meta charset="utf-8">
        <title>Create Account | Campus Collective</title>
        <link rel="stylesheet" href="styles.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <script src="YakuzaJS.js"></script>

    </head>
    <body class="body">
        <div class="login">
            <h1>Create Account</h1>
            <div class="<?php echo $type === 'success' ? 'success-msg' : 'error-msg'; ?>">
                <?php echo $message; ?>
            </div>
            <a href="home.php" class="home-btn">Back to Home</a>
            <br><br>
            
        </div>
    </body>
    </html>
    <?php
    exit();
}
//fucntion to encrypt security answer.
function maz_encrypts($securityA){ 
    //the above comment will be removed before deployment. 
    $ban = trim($securityA);
    $banL = strtolower($ban);
    $band_enc = $banL . "@PAMC37**";
    return $band_enc;
}

// Only run if form submitted via POST.
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect input and remove extra spaces with trim
    $firstname = trim($_POST['firstname']);
    $lastname = trim($_POST['surname']); // keep your form name as 'surname'
    $full_name = $firstname . ' ' . $lastname;
    $email = trim($_POST['email']);
    $cellphone = trim($_POST['cellphone']); // optional, can store in sell_as
    $security_question = trim($_POST['security_question']);
    $raw_security_answer = trim($_POST['security_answer']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Check if passwords match
    if ($password !== $confirm_password) {
        showPage("Passwords do not match.");
    }

    // Validate email

    //the encrypted security answer.
    $enc_security_ans = maz_encrypts($raw_security_answer);


    // Hash password for security
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Prepare SQL statement to insert user
    $stmt = $conn->prepare(
        "INSERT INTO users (firstname, lastname, full_name, email, securityQ, securityA ,password) VALUES (?, ?, ?, ?, ?, ?, ?)"
    );
    $stmt->bind_param("sssssss", $firstname, $lastname, $full_name, $email, $security_question, $enc_security_ans, $hashed_password);

    // Execute SQL and check for success or duplicate
    if ($stmt->execute()) {
        showPage("Account created successfully. <a href='login.php'>Log in</a>", 'success');
    } else{
        showPage("Error: " . htmlspecialchars($stmt->error));
    }

    // Close statement and connection
    $stmt->close();
    $conn->close();

} else {
    // Redirect if page accessed directly
    header("Location: createAccount.html");
    exit();
}
?>
<script>
  document.addEventListener("DOMContentLoaded", function() {
    if (typeof passwordTime === "function") passwordTime();
    if (typeof passwordConfirm === "function") passwordConfirm();
  });
</script>
