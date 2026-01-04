<?php
// admin_dashboard.php
session_start();
include 'yakuzaconnection.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Fetch counts for dashboard overview
$user_count_query = "SELECT COUNT(*) as count FROM users";
$user_count_result = $conn->query($user_count_query);
$user_count = $user_count_result->fetch_assoc()['count'];

$product_count_query = "SELECT COUNT(*) as count FROM products WHERE status != 'deleted'";
$product_count_result = $conn->query($product_count_query);
$product_count = $product_count_result->fetch_assoc()['count'];

$reunite_count_query = "SELECT COUNT(*) as count FROM reunite";
$reunite_count_result = $conn->query($reunite_count_query);
$reunite_count = $reunite_count_result->fetch_assoc()['count'];

$contact_count_query = "SELECT COUNT(*) as count FROM contact";
$contact_count_result = $conn->query($contact_count_query);
$contact_count = $contact_count_result->fetch_assoc()['count'];

$order_count_query = "SELECT COUNT(*) as count FROM orders";
$order_count_result = $conn->query($order_count_query);
$order_count = $order_count_result->fetch_assoc()['count'];
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Admin Dashboard - Campus Collective</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="header">
        <div class="top">
            <h1 class="message">Campus Collective - Admin Dashboard</h1>
            <div class="icons">
                <a href="logout.php"><i class="fa fa-sign-out"></i> Logout</a>
            </div>
        </div>
        <div class="nav">
            <div class="logo">
                <img src="PICTURES/LOGO FINAL.png" alt="logo">
            </div>
            <div class="navs">
                <a href="admin_dashboard.php">Dashboard</a>
                <a href="manage_users.php">Manage Users</a>
                <a href="manage_products.php">Manage Products</a>
                <a href="manage_reunite.php">Manage Reunite</a>
                <a href="manage_contacts.php">Manage Contacts</a>
                <a href="manage_orders.php">Manage Orders</a>
                <a href="home.php">Back to Home</a>
            </div>
        </div>
    </div>

    <section class="dashboard-overview">
        <h2>Welcome, Admin!</h2>
        <div class="stats">
            <div class="stat-card">
                <h3>Total Users</h3>
                <p><?php echo $user_count; ?></p>
            </div>
            <div class="stat-card">
                <h3>Active Products</h3>
                <p><?php echo $product_count; ?></p>
            </div>
            <div class="stat-card">
                <h3>Reunite Entries</h3>
                <p><?php echo $reunite_count; ?></p>
            </div>
            <div class="stat-card">
                <h3>Contact Messages</h3>
                <p><?php echo $contact_count; ?></p>
            </div>
            <div class="stat-card">
                <h3>Total Orders</h3>
                <p><?php echo $order_count; ?></p>
            </div>
        </div>
    </section>

    <footer>
        <p>&copy; 2025 Campus Collective | Admin Panel</p>
    </footer>
</body>
</html>