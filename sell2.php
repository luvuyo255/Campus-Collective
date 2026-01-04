<?php
// Include your DB connection
require("yakuzaconnection.php");

// Start session
session_start();

// Run only if the form was submitted with POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if user is logged in
    if (!isset($_SESSION['id'])) {
        header("Location: login.php");
        exit();
    }

    $user_id = $_SESSION['id'];

    // Safely get POST values
    $prodName     = $_POST['prod-name'] ?? null;
    $prodType     = $_POST['prod-type'] ?? null;
    $prodDesc     = $_POST['prod-desc'] ?? null;
    $prodPrice    = $_POST['prod-price'] ?? null;
    $prodQuant    = $_POST['prod-quantity'] ?? 1;
    $prodCondition = $_POST['prod-condition'] ?? null;
    $availability = $_POST['service-availability'] ?? null; // NEW FIELD

    // Handle file upload (optional)
    $prodPic = "";
    if (isset($_FILES['prod-pic']) && $_FILES['prod-pic']['error'] == 0) {
        $targetDir = "PICTURES/";
        $fileName = basename($_FILES["prod-pic"]["name"]);
        $targetFilePath = $targetDir . $fileName;

        if (move_uploaded_file($_FILES["prod-pic"]["tmp_name"], $targetFilePath)) {
            $prodPic = $targetFilePath;
        } else {
            echo "⚠️ Error uploading image.<br>";
        }
    }

    // Insert product with availability
    $sql = "INSERT INTO products (user_id, prodName, prodType, prodDesc, prodPrice, prodPic, quantity, availability, prodCondition) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "isssdisss", 
        $user_id, $prodName, $prodType, $prodDesc, $prodPrice, $prodPic, $prodQuant, $availability, $prodCondition
    );

    if ($stmt->execute()) {
        echo "✅ Product listed successfully!";
        header("Location: dashboard.php");
        exit();
    } else {
        echo "❌ Error: " . $stmt->error;
    }

    $conn->close();
} else {
    echo "Form not submitted!";
}
?>
