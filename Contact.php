<?php
require("yakuzaconnection.php");

$success_message = "";
$error_message = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fname = trim($_POST['name']);
    $email = trim($_POST['email']);
    $Message = trim($_POST['Message']);
    $id = rand(1, 10000);

    if (!empty($email) && !empty($Message)) {
        $stmt = $conn->prepare("INSERT INTO contact (id, name, Email, Message) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $id, $fname, $email, $Message);

        if ($stmt->execute()) {
            $success_message = "Your message has been sent successfully!";
        } else {
            $error_message = "Failed to send your message. Please try again.";
        }
        $stmt->close();
    } else {
        $error_message = "Email and message are required!";
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .alert {
            padding: 10px 15px;
            margin: 15px 0;
            border-radius: 5px;
            font-weight: bold;
        }
        .alert-success {
            background-color: #d4edda;
            color: #155724;
        }
        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="top">
            <h1 class="message">Campus Collective</h1>
            <div class="icons">
                <a href="dashboard.php"><i class="fa fa-user"></i></a>
                <a href="cart.html"><i class="fa fa-shopping-cart"></i></a>
            </div>
        </div>
        <div class="nav">
            <div class="logo">
                <img src="PICTURES/LOGO FINAL.png" alt="logo">
            </div>
            <div class="navs">
                <a href="home.php">Home</a>
                <a href="buy_maz.php">Shop</a>
                <a href="sell.php">Sell</a>
                <a href="reunite.php">Reunite</a>
                <a href="About.html">About Us</a>
                <a href="Contact.php">Contact Us</a>
                <a href="login.php">Login</a>
            </div>
        </div>
    </div>

    <div class="contact-header">
        <h1>Contact Us</h1> 
        <p>Have a question, spotted something, or need a hand? We're here for you! Whether it's feedback about the app,
        help with buying and selling, or reuniting lost items with their owners, don't hesitate to reach out. 
        Your input makes our student community stronger and keeps the marketplace safe, simple, and fun for everyone.</p>
    </div>

    <div class="contact-middle">
        <div class="contact-form">
            <h2>Send Your Message</h2>
            <p>Any inquiries, complaints, let us know</p>

            <!-- Alerts -->
            <?php if (!empty($success_message)): ?>
                <div class="alert alert-success">
                    <?php echo htmlspecialchars($success_message); ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($error_message)): ?>
                <div class="alert alert-error">
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>

            <!-- Contact Form -->
            <form action="" method="post">
                Name:<br>
                <input type="text" name="name" placeholder="Your Name"><br><br>

                Email:<br>
                <input type="email" name="email" placeholder="Email" required><br><br>

                Your Message:<br>
                <textarea name="Message" rows="5" cols="30" placeholder="Please enter your message" required></textarea><br><br>

                <button type="submit">Submit</button>
            </form>
        </div>

        <div class="contact-info">
            <h2><i class="fa fa-envelope"></i>Email Us</h2>
            <a href="mailto:support@campuscollective.com">support@campuscollective.com</a>

            <h2><i class="fa fa-phone"></i>Call Us</h2>
            <p>Phone: 078 678 1234</p>
        </div>
    </div>

    <div class="contact-end"> 
        <h2>Enjoy Our App</h2> 
        <p>We love your feedback and your commitment to our page, don't forget to visit our lost and found page. Help and reunite!</p>
        <button><a href="reunite.php">Lost and Found</a></button>
    </div>

    <footer>
        <p>&copy; 2025 Campus Collective | Connecting Students Everywhere</p>
        <p>
            <a href="https://instagram.com" target="_blank"><i class="fab fa-instagram"></i></a>
            <a href="https://facebook.com" target="_blank"><i class="fab fa-facebook"></i></a>
            <a href="mailto:contact@campuscollective.com"><i class="fa fa-envelope"></i></a>
        </p>
    </footer>
</body>
</html>
