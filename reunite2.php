<?php
// Include your DB connection file
require("yakuzaconnection.php");

// Run only when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect form data safely
    $firstName = $_POST['firstName'] ?? null;
    $lastName = $_POST['lastName'] ?? null;
    $email = $_POST['email'] ?? null;
    $phone = $_POST['phone'] ?? null;
    $message = $_POST['message'] ?? null;
    $itemStatus = $_POST['itemStatus'] ?? null;

    // Validate itemStatus
    if (!in_array($itemStatus, ['Lost', 'Found'])) {
        echo "❌ Invalid item status selected.";
        exit;
    }

    // Generate unique ID for the record
    $id = rand(1, 10000); // Note: Consider using auto-increment instead

    // Handle optional image upload
    $itemImage = "";
    if (isset($_FILES['itemImage']) && $_FILES['itemImage']['error'] == 0) {
        $targetDir = __DIR__ . "/Uploads/"; // Ensure uploads/ exists
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true); // Create uploads/ if missing
        }

        // Unique filename to avoid overwriting
        $fileName = time() . "_" . basename($_FILES["itemImage"]["name"]);
        $targetFilePath = $targetDir . $fileName;

        if (move_uploaded_file($_FILES["itemImage"]["tmp_name"], $targetFilePath)) {
            $itemImage = "Uploads/" . $fileName; // Save relative path in DB
        } else {
            echo "⚠️ Error uploading image.<br>";
            exit;
        }
    }

    // Prepare and execute SQL statement
    $sql = "INSERT INTO reunite (id, firstName, lastName, email, phone, message, itemImage, itemStatus) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        echo "❌ Error preparing statement: " . $conn->error;
        exit;
    }

    // Bind parameters
    $stmt->bind_param("isssssss", $id, $firstName, $lastName, $email, $phone, $message, $itemImage, $itemStatus);

    // Execute the statement
    if ($stmt->execute()) {
        echo "✅ Lost & Found inquiry submitted successfully!";
        header('Location: reunite.php');
        exit;
    } else {
        echo "❌ Error: " . $stmt->error;
    }

    // Close statement and connection
    $stmt->close();
    $conn->close();
} else {
    echo "Form not submitted!";
}
?>