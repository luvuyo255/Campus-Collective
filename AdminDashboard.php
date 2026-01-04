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

// Stats
$total_users = $conn->query("SELECT COUNT(*) AS c FROM users")->fetch_assoc()['c'];
$total_orders = $conn->query("SELECT COUNT(*) AS c FROM orders")->fetch_assoc()['c'] ?? 0;
$pending_orders = $conn->query("SELECT COUNT(*) AS c FROM orders WHERE status='pending'")->fetch_assoc()['c'] ?? 0;
$total_products = $conn->query("SELECT COUNT(*) AS c FROM products WHERE status!='deleted'")->fetch_assoc()['c'];
$total_reunite = $conn->query("SELECT COUNT(*) AS c FROM reunite")->fetch_assoc()['c'];
$total_contacts = $conn->query("SELECT COUNT(*) AS c FROM contact")->fetch_assoc()['c'];

// Additional stats
$active_listings = $conn->query("SELECT COUNT(*) AS c FROM products WHERE status='active'")->fetch_assoc()['c'] ?? 0;
$sold_products = $conn->query("SELECT COUNT(*) AS c FROM products WHERE status='sold'")->fetch_assoc()['c'] ?? 0;
$newest_user = $conn->query("SELECT firstname, lastname, created_at FROM users ORDER BY created_at DESC LIMIT 1")->fetch_assoc();
$popular_product = $conn->query("
    SELECT prodName, COUNT(*) as cnt 
    FROM orders o 
    JOIN products p ON o.product_id = p.id 
    GROUP BY prodName 
    ORDER BY cnt DESC LIMIT 1
")->fetch_assoc();

// Data for charts
$monthly_users = $conn->query("
    SELECT DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as count 
    FROM users 
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH) 
    GROUP BY month 
    ORDER BY month
");

$monthly_orders = $conn->query("
    SELECT DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as count 
    FROM orders 
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH) 
    GROUP BY month 
    ORDER BY month
");

$order_status_data = $conn->query("
    SELECT status, COUNT(*) as count 
    FROM orders 
    GROUP BY status
");

$product_status_data = $conn->query("
    SELECT status, COUNT(*) as count 
    FROM products 
    WHERE status != 'deleted'
    GROUP BY status
");

// Prepare chart data
$user_months = [];
$user_counts = [];
while ($row = $monthly_users->fetch_assoc()) {
    $user_months[] = date('M Y', strtotime($row['month']));
    $user_counts[] = $row['count'];
}

$order_months = [];
$order_counts = [];
while ($row = $monthly_orders->fetch_assoc()) {
    $order_months[] = date('M Y', strtotime($row['month']));
    $order_counts[] = $row['count'];
}

$order_status_labels = [];
$order_status_counts = [];
while ($row = $order_status_data->fetch_assoc()) {
    $order_status_labels[] = ucfirst($row['status']);
    $order_status_counts[] = $row['count'];
}

$product_status_labels = [];
$product_status_counts = [];
while ($row = $product_status_data->fetch_assoc()) {
    $product_status_labels[] = ucfirst($row['status']);
    $product_status_counts[] = $row['count'];
}

// Fetch data for tables
$users = $conn->query("SELECT id, firstname, lastname, role, email, created_at FROM users ORDER BY created_at DESC");
$orders = $conn->query("
    SELECT o.*, u.full_name AS buyer_name 
    FROM orders o 
    JOIN users u ON o.buyer_id = u.id
    ORDER BY o.created_at DESC
");
$reunite = $conn->query("SELECT * FROM reunite ORDER BY id DESC");
$contacts = $conn->query("SELECT * FROM contact ORDER BY id DESC");

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['promote_user_id'])) {
    $promote_id = intval($_POST['promote_user_id']);
    if ($promote_id !== $user_id) {
        $stmt = $conn->prepare("UPDATE users SET role = 'admin' WHERE id = ?");
        $stmt->bind_param("i", $promote_id);
        $stmt->execute();
    }
    header("Location: AdminDashboard.php");
    exit();
}



$recent_users_arr = [];
$res = $conn->query("SELECT firstname, lastname, email, created_at FROM users ORDER BY created_at DESC LIMIT 5");
while ($row = $res->fetch_assoc()) $recent_users_arr[] = $row;

$recent_orders_arr = [];
$res = $conn->query("SELECT o.id, u.full_name AS buyer, o.created_at, o.status FROM orders o JOIN users u ON o.buyer_id = u.id ORDER BY o.created_at DESC LIMIT 5");
while ($row = $res->fetch_assoc()) $recent_orders_arr[] = $row;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Campus Collective</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="admin.css">
</head>
<body>
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <div class="admin-sidebar">
            <h2 class="sidebar-title">Campus Admin</h2>
            <a href="AdminDashboard.php" class="sidebar-link active"><i class="fas fa-home"></i> Dashboard</a>
            <a href="AdminUsers.php" class="sidebar-link"><i class="fas fa-users"></i> Users</a>
            <a href="AdminProducts.php" class="sidebar-link"><i class="fas fa-box"></i> Products</a>
            <a href="AdminOrders.php" class="sidebar-link"><i class="fas fa-shopping-cart"></i> Orders</a>
            <a href="AdminReunite.php" class="sidebar-link"><i class="fas fa-users"></i> Reunite</a>
            <a href="AdminContact.php" class="sidebar-link"><i class="fas fa-envelope"></i> Contact</a>
            <a href="logout.php" class="sidebar-link"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>

        <!-- Main Content -->
        <div class="admin-main">
            <h1 class="main-title">Admin Dashboard</h1>

            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <h3 class="stat-title">Total Users</h3>
                    <h2 class="stat-value"><?php echo $total_users; ?></h2>
                </div>
                <div class="stat-card">
                    <h3 class="stat-title">Total Products</h3>
                    <h2 class="stat-value"><?php echo $total_products; ?></h2>
                </div>
                <div class="stat-card">
                    <h3 class="stat-title">Active Listings</h3>
                    <h2 class="stat-value"><?php echo $active_listings; ?></h2>
                </div>
                <div class="stat-card">
                    <h3 class="stat-title">Sold Products</h3>
                    <h2 class="stat-value"><?php echo $sold_products; ?></h2>
                </div>
                <div class="stat-card">
                    <h3 class="stat-title">Total Orders</h3>
                    <h2 class="stat-value"><?php echo $total_orders; ?></h2>
                </div>
                <div class="stat-card">
                    <h3 class="stat-title">Pending Orders</h3>
                    <h2 class="stat-value"><?php echo $pending_orders; ?></h2>
                </div>
                <div class="stat-card">
                    <h3 class="stat-title">Reunite Entries</h3>
                    <h2 class="stat-value"><?php echo $total_reunite; ?></h2>
                </div>
                <div class="stat-card">
                    <h3 class="stat-title">Contact Messages</h3>
                    <h2 class="stat-value"><?php echo $total_contacts; ?></h2>
                </div>
                <div class="stat-card">
                    <h3 class="stat-title">Newest User</h3>
                    <h2 class="stat-value"><?php echo htmlspecialchars($newest_user['firstname'].' '.$newest_user['lastname']); ?></h2>
                    <p class="stat-subtext">Joined: <?php echo date('d M Y', strtotime($newest_user['created_at'])); ?></p>
                </div>
                <div class="stat-card">
                    <h3 class="stat-title">Most Popular Product</h3>
                    <h2 class="stat-value"><?php echo $popular_product ? htmlspecialchars($popular_product['prodName']) : 'N/A'; ?></h2>
                    <p class="stat-subtext">Orders: <?php echo $popular_product ? $popular_product['cnt'] : '0'; ?></p>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="charts-section">
                <h3 class="section-title"><i class="fas fa-chart-line"></i> Analytics Overview</h3>
                <div class="charts-grid">
                    <div class="chart-card">
                        <h4 class="chart-title"><i class="fas fa-users"></i> User Growth</h4>
                        <div class="chart-container">
                            <canvas id="userGrowthChart"></canvas>
                        </div>
                    </div>
                    <div class="chart-card">
                        <h4 class="chart-title"><i class="fas fa-shopping-cart"></i> Order Trends</h4>
                        <div class="chart-container">
                            <canvas id="orderTrendsChart"></canvas>
                        </div>
                    </div>
                    <div class="chart-card">
                        <h4 class="chart-title"><i class="fas fa-tasks"></i> Order Status</h4>
                        <div class="chart-container">
                            <canvas id="orderStatusChart"></canvas>
                        </div>
                    </div>
                    <div class="chart-card">
                        <h4 class="chart-title"><i class="fas fa-box"></i> Product Status</h4>
                        <div class="chart-container">
                            <canvas id="productStatusChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="quick-actions">
                <h3 class="section-title"><i class="fas fa-bolt"></i> Quick Actions</h3>
                <div class="action-buttons">
                    <a href="export_users.php" class="action-btn"><i class="fas fa-file-export"></i> Export Users CSV</a>
                    <a href="export_products.php" class="action-btn"><i class="fas fa-file-export"></i> Export Products CSV</a>
                    <a href="export_orders.php" class="action-btn"><i class="fas fa-file-export"></i> Export Orders CSV</a>
                    
                </div>
            </div>

            <!-- Add Admin Modal -->
            <div id="addAdminModal" class="modal">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="modal-title">Add New Admin</h2>
                        <button class="close-modal">&times;</button>
                    </div>
                    <form method="post">
                        <div class="form-group">
                            <label class="form-label">Email</label>
                            <input type="email" name="admin_email" class="form-input" required>
                        </div>
                        <div class="form-actions">
                            <button type="button" class="cancel-btn" onclick="document.getElementById('addAdminModal').style.display='none'">Cancel</button>
                            <button type="submit" name="add_admin" class="save-btn">Add Admin</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="dashboard-section">
                <h3 class="section-title"><i class="fas fa-history"></i> Recent Activity</h3>
                <div class="activity-grid">
                    <div>
                        <h4 class="subsection-title">Recent User Registrations</h4>
                        <ul class="activity-list">
                            <?php foreach($recent_users_arr as $ru): ?>
                                <li class="activity-item">
                                    <?php echo htmlspecialchars($ru['firstname'].' '.$ru['lastname']); ?> 
                                    (<?php echo htmlspecialchars($ru['email']); ?>) - 
                                    <?php echo date('d M Y', strtotime($ru['created_at'])); ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <div>
                        <h4 class="subsection-title">Recent Orders</h4>
                        <ul class="activity-list">
                            <?php foreach($recent_orders_arr as $ro): ?>
                                <li class="activity-item">
                                    Order #<?php echo $ro['id']; ?> by <?php echo htmlspecialchars($ro['buyer']); ?> - 
                                    <?php echo ucfirst($ro['status']); ?> (<?php echo date('d M Y', strtotime($ro['created_at'])); ?>)
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Chart data from PHP
        const userMonths = <?php echo json_encode($user_months); ?>;
        const userCounts = <?php echo json_encode($user_counts); ?>;
        const orderMonths = <?php echo json_encode($order_months); ?>;
        const orderCounts = <?php echo json_encode($order_counts); ?>;
        const orderStatusLabels = <?php echo json_encode($order_status_labels); ?>;
        const orderStatusCounts = <?php echo json_encode($order_status_counts); ?>;
        const productStatusLabels = <?php echo json_encode($product_status_labels); ?>;
        const productStatusCounts = <?php echo json_encode($product_status_counts); ?>;

        // User Growth Chart
        const userGrowthCtx = document.getElementById('userGrowthChart').getContext('2d');
        new Chart(userGrowthCtx, {
            type: 'line',
            data: {
                labels: userMonths,
                datasets: [{
                    label: 'User Registrations',
                    data: userCounts,
                    borderColor: '#6b8ff3',
                    backgroundColor: 'rgba(107, 143, 243, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0,0,0,0.1)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });

        // Order Trends Chart
        const orderTrendsCtx = document.getElementById('orderTrendsChart').getContext('2d');
        new Chart(orderTrendsCtx, {
            type: 'bar',
            data: {
                labels: orderMonths,
                datasets: [{
                    label: 'Orders',
                    data: orderCounts,
                    backgroundColor: '#8a5dfc',
                    borderColor: '#6d3fdc',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0,0,0,0.1)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });

        // Order Status Chart
        const orderStatusCtx = document.getElementById('orderStatusChart').getContext('2d');
        new Chart(orderStatusCtx, {
            type: 'doughnut',
            data: {
                labels: orderStatusLabels,
                datasets: [{
                    data: orderStatusCounts,
                    backgroundColor: [
                        '#6b8ff3',
                        '#8a5dfc',
                        '#4CAF50',
                        '#ff9800',
                        '#dc3545'
                    ],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // Product Status Chart
        const productStatusCtx = document.getElementById('productStatusChart').getContext('2d');
        new Chart(productStatusCtx, {
            type: 'pie',
            data: {
                labels: productStatusLabels,
                datasets: [{
                    data: productStatusCounts,
                    backgroundColor: [
                        '#6b8ff3',
                        '#4CAF50',
                        '#ff9800',
                        '#dc3545'
                    ],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // Close modal on outside click
        window.onclick = function(event) {
            const modal = document.getElementById('addAdminModal');
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        };

        // Close modal with close button
        document.querySelector('.close-modal').addEventListener('click', function() {
            document.getElementById('addAdminModal').style.display = 'none';
        });
    </script>
</body>
</html>
<?php $conn->close(); ?>