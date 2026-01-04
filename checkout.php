<?php
session_start();
require("yakuzaconnection.php");

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

$buyer_id = $_SESSION['id'];

// Get product_id from URL and validate
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($product_id <= 0) {
    header("Location: cart.html?error=invalid_product_id");
    exit();
}

// Get product and seller info
$sql = "SELECT p.*, u.id AS seller_id, u.full_name AS seller_name 
        FROM products p 
        JOIN users u ON p.user_id = u.id 
        WHERE p.id = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Query preparation failed: " . $conn->error);
}
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: cart.html?error=product_not_found");
    exit();
}

$product = $result->fetch_assoc();

// Handle checkout form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $message = trim($_POST['message'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $phone_number = trim($_POST['phone_number'] ?? '');
    
    $insert_sql = "INSERT INTO orders (buyer_id, seller_id, product_id, message, location, phone_number, status) 
                   VALUES (?, ?, ?, ?, ?, ?, 'pending')";
    $stmt = $conn->prepare($insert_sql);
    if (!$stmt) {
        die("Insert query preparation failed: " . $conn->error);
    }
    $stmt->bind_param("iiisss", $buyer_id, $product['seller_id'], $product_id, $message, $location, $phone_number);
    
    if ($stmt->execute()) {
        // Clear the specific cart item
        echo "<script>
                let cart = JSON.parse(localStorage.getItem('cart')) || [];
                cart = cart.filter(item => item.product_id !== $product_id);
                localStorage.setItem('cart', JSON.stringify(cart));
                </script>";
        $success_message = "Your request has been sent to the seller! They will contact you to arrange a face-to-face meeting for payment and delivery.";
    } else {
        $error = "Error placing order: " . $stmt->error;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Checkout | Campus Collective</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="checkout-container">
        <h1>Checkout</h1>
        <div class="product-info">
            <img src="<?= htmlspecialchars($product['prodPic']); ?>" 
                 alt="<?= htmlspecialchars($product['prodName']); ?>">
            <h2><?= htmlspecialchars($product['prodName']); ?></h2>
            <p>Price: R<?= htmlspecialchars($product['prodPrice']); ?></p>
            <p>Seller: <?= htmlspecialchars($product['seller_name']); ?></p>
        </div>
        
        <?php if (isset($success_message)) { ?>
            <p class="success"><?= htmlspecialchars($success_message); ?></p>
        <?php } ?>
        
        <?php if (isset($error)) { ?>
            <p class="error"><?= htmlspecialchars($error); ?></p>
        <?php } ?>
        <p class="checkout-instructions">Please provide accurate contact information below to ensure the seller can reach you. 
            Once your request is sent, the seller will contact you to arrange a convenient time and place on campus for a face-to-face meeting,
             where you can inspect the item and make payment directly to the seller (cash or agreed-upon method).</p>
        
        <form method="POST">
            <label for="location">Your Location (e.g., Campus Building, Nearby Landmark)</label>
            <input type="text" name="location" id="location" placeholder="Example: Library, Res Joe Slovo, " required>
            
            <label for="phone_number">Your Phone Number</label>
            <input type="tel" name="phone_number" id="phone_number" placeholder="Example: 072 345 6789"  required>
            
            <label for="message">Message to Seller (optional)</label>
            <textarea name="message" id="message" placeholder="Example : If product is still available please let me know."></textarea>
            
            <button type="submit" class="checkout-btn">Send Request to Seller</button>
        </form><br>
        <a href ="buy_maz.php"><button  class="checkout-btn"><strong>Back To Shop</strong></button></a>
    </div>
</body>
</html>