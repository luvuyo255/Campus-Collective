<?php
// For DB connection file
require("yakuzaconnection.php");

// Validate required fields
if (empty($_REQUEST['name']) || empty($_REQUEST['email']) || empty($_REQUEST['Message'])) {
    die("Error: All fields are required");
}

// Sanitize and validate input
$fname = trim($_REQUEST['name']);
$email = trim($_REQUEST['email']);
$Message = trim($_REQUEST['Message']);

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die("Error: Invalid email format");
}

// Validate name (letters and spaces only, 2-50 chars)
if (!preg_match("/^[a-zA-Z ]{2,50}$/", $fname)) {
    die("Error: Invalid name format");
}

// Validate message length
if (strlen($Message) > 500) {
    die("Error: Message too long (max 500 characters)");
}

$id = rand(1, 10000);

// Use prepared statements to prevent SQL injection
$sql = "INSERT INTO contact (id, name, Email, Message) VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    die("Error preparing statement: " . $conn->error);
}

// Bind parameters with their types (i=integer, s=string)
$stmt->bind_param("isss", $id, $fname, $email, $Message);

if ($stmt->execute()) {
    echo "New record created successfully";
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>