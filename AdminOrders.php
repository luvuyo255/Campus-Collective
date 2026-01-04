<?php
session_start();
require("yakuzaconnection.php");

// Only allow admins
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

// Get logged-in user info
$user_id = $_SESSION['id'];
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_result = $stmt->get_result();
$user = $user_result->fetch_assoc();

if ($user['role'] !== 'admin') {
    echo "<h2 class='alert alert-error text-center mt-8'>Access Denied: You are not an admin.</h2>";
    exit();
}

// Handle order actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_order_status'])) {
        $order_id = intval($_POST['order_id']);
        $new_status = $_POST['status'];
        $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $new_status, $order_id);
        $stmt->execute();
    } elseif (isset($_POST['delete_order_id'])) {
        $delete_id = intval($_POST['delete_order_id']);
        $stmt = $conn->prepare("DELETE FROM orders WHERE id = ?");
        $stmt->bind_param("i", $delete_id);
        $stmt->execute();
    }
    header("Location: AdminOrders.php");
    exit();
}

// Fetch all orders
$orders = $conn->query("
    SELECT o.*, u.firstname, u.lastname, p.prodName AS product_name
    FROM orders o 
    JOIN users u ON o.buyer_id = u.id
    JOIN products p ON o.product_id = p.id
    ORDER BY o.created_at DESC
");

// Stats
$total_orders = $conn->query("SELECT COUNT(*) AS c FROM orders")->fetch_assoc()['c'];
$pending_orders = $conn->query("SELECT COUNT(*) AS c FROM orders WHERE status='pending'")->fetch_assoc()['c'];
$completed_orders = $conn->query("SELECT COUNT(*) AS c FROM orders WHERE status='completed'")->fetch_assoc()['c'];
$rejected_orders = $conn->query("SELECT COUNT(*) AS c FROM orders WHERE status='rejected'")->fetch_assoc()['c'];
$revenue_today = $conn->query("
    SELECT SUM(p.prodPrice) as revenue 
    FROM orders o 
    JOIN products p ON o.product_id = p.id 
    WHERE DATE(o.created_at) = CURDATE() AND o.status='completed'
")->fetch_assoc()['revenue'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Orders | Campus Collective</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="admin.css">
</head>
<body>
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <div class="admin-sidebar">
            <h2 class="sidebar-title">Campus Admin</h2>
            <a href="AdminDashboard.php" class="sidebar-link"><i class="fas fa-home"></i> Dashboard</a>
            <a href="AdminUsers.php" class="sidebar-link"><i class="fas fa-users"></i> Users</a>
            <a href="AdminProducts.php" class="sidebar-link"><i class="fas fa-box"></i> Products</a>
            <a href="AdminOrders.php" class="sidebar-link active"><i class="fas fa-shopping-cart"></i> Orders</a>
            <a href="AdminReunite.php" class="sidebar-link"><i class="fas fa-users"></i> Reunite</a>
            <a href="AdminContact.php" class="sidebar-link"><i class="fas fa-envelope"></i> Contact</a>
            <a href="logout.php" class="sidebar-link"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>

        <!-- Main Content -->
        <div class="admin-main">
            <h1 class="main-title">Order Management</h1>

            <!-- Order Stats -->
            <div class="stats-grid">
                <div class="stat-card">
                    <h3 class="stat-title">Total Orders</h3>
                    <h2 class="stat-value"><?php echo $total_orders; ?></h2>
                </div>
                <div class="stat-card">
                    <h3 class="stat-title">Pending Orders</h3>
                    <h2 class="stat-value"><?php echo $pending_orders; ?></h2>
                </div>
                <div class="stat-card">
                    <h3 class="stat-title">Completed Orders</h3>
                    <h2 class="stat-value"><?php echo $completed_orders; ?></h2>
                </div>
                <div class="stat-card">
                    <h3 class="stat-title">Today's Revenue</h3>
                    <h2 class="stat-value">R<?php echo number_format($revenue_today, 2); ?></h2>
                </div>
            </div>

            <!-- Orders Table -->
            <div class="section">
                <h2 class="section-title"><i class="fas fa-shopping-cart"></i> All Orders</h2>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr class="table-header">
                                <th>ID</th>
                                <th>Buyer</th>
                                <th>Product</th>
                                <th>Message</th>
                                <th>Status</th>
                                <th>Location</th>
                                <th>Phone</th>
                                <th>Created At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($o = $orders->fetch_assoc()): ?>
                            <tr class="table-row">
                                <td><?php echo $o['id']; ?></td>
                                <td><?php echo htmlspecialchars($o['firstname'].' '.$o['lastname']); ?></td>
                                <td><?php echo htmlspecialchars($o['product_name']); ?></td>
                                <td><?php echo htmlspecialchars($o['message']); ?></td>
                                <td>
                                    <span class="<?php 
                                        echo ($o['status'] === 'completed') ? 'text-green' : 
                                             (($o['status'] === 'pending') ? 'text-warning' : 'text-red'); 
                                    ?>">
                                        <?php echo ucfirst($o['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($o['location']); ?></td>
                                <td><?php echo htmlspecialchars($o['phone_number']); ?></td>
                                <td><?php echo date('d M Y', strtotime($o['created_at'])); ?></td>
                                <td>
                                    <div class="action-buttons" style="justify-content: start; gap: 8px;">
                                        <form method="post" class="inline">
                                            <input type="hidden" name="order_id" value="<?php echo $o['id']; ?>">
                                            <select name="status" onchange="this.form.submit()" style="padding: 6px; border-radius: 4px; border: 1px solid #ddd;">
                                                <option value="pending" <?php echo $o['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                <option value="accepted" <?php echo $o['status'] === 'accepted' ? 'selected' : ''; ?>>Accepted</option>
                                                <option value="rejected" <?php echo $o['status'] === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                                                <option value="completed" <?php echo $o['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                            </select>
                                            <input type="hidden" name="update_order_status" value="1">
                                        </form>
                                        <form method="post" class="inline">
                                            <input type="hidden" name="delete_order_id" value="<?php echo $o['id']; ?>">
                                            <button type="submit" onclick="return confirm('Are you sure you want to delete this order?');" class="action-btn" style="padding: 6px 12px; font-size: 12px; background: #dc3545;">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
<?php $conn->close(); ?>