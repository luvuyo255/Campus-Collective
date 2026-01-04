<?php
// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

// Include database connection
require("yakuzaconnection.php");

// Get user ID from session
$user_id = $_SESSION['id'];

// Check if order ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: dashboard.php?error=invalid_id");
    exit();
}

$order_id = (int)$_GET['id'];

// Verify the order belongs to the user (as seller or buyer) and is a notification they can delete
$check_sql = "SELECT id FROM orders WHERE id = ? AND (seller_id = ? OR (buyer_id = ? AND status = 'accepted')) AND notify != 'deleted'";
$stmt = $conn->prepare($check_sql);
$stmt->bind_param("iii", $order_id, $user_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: dashboard.php?error=notification_not_found");
    exit();
}

// Update the notify field to 'deleted'
$update_sql = "UPDATE orders SET notify = 'deleted' WHERE id = ?";
$stmt = $conn->prepare($update_sql);
$stmt->bind_param("i", $order_id);

if ($stmt->execute()) {
    header("Location: dashboard.php?success=notification_deleted");
} else {
    header("Location: dashboard.php?error=delete_failed");
}

$stmt->close();
$conn->close();
?>