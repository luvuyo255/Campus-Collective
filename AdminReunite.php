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

// Handle reunite actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_reunite_id'])) {
        $delete_id = intval($_POST['delete_reunite_id']);
        $stmt = $conn->prepare("DELETE FROM reunite WHERE id = ?");
        $stmt->bind_param("i", $delete_id);
        $stmt->execute();
    }
    header("Location: AdminReunite.php");
    exit();
}

// Fetch all reunite entries
$reunite = $conn->query("SELECT * FROM reunite ORDER BY id DESC");

// Stats
$total_reunite = $conn->query("SELECT COUNT(*) AS c FROM reunite")->fetch_assoc()['c'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Reunite | Campus Collective</title>
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
            <a href="AdminOrders.php" class="sidebar-link"><i class="fas fa-shopping-cart"></i> Orders</a>
            <a href="AdminReunite.php" class="sidebar-link active"><i class="fas fa-users"></i> Reunite</a>
            <a href="AdminContact.php" class="sidebar-link"><i class="fas fa-envelope"></i> Contact</a>
            <a href="logout.php" class="sidebar-link"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>

        <!-- Main Content -->
        <div class="admin-main">
            <h1 class="main-title">Reunite Management</h1>

            <!-- Reunite Stats -->
            <div class="stats-grid">
                <div class="stat-card">
                    <h3 class="stat-title">Total Entries</h3>
                    <h2 class="stat-value"><?php echo $total_reunite; ?></h2>
                </div>
            </div>

            <!-- Reunite Table -->
            <div class="section">
                <h2 class="section-title"><i class="fas fa-users"></i> Lost & Found Entries</h2>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr class="table-header">
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Message</th>
                                <th>Item Image</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($r = $reunite->fetch_assoc()): ?>
                            <tr class="table-row">
                                <td><?php echo $r['id']; ?></td>
                                <td><?php echo htmlspecialchars($r['firstName'].' '.$r['lastName']); ?></td>
                                <td><?php echo htmlspecialchars($r['email']); ?></td>
                                <td><?php echo htmlspecialchars($r['phone']); ?></td>
                                <td><?php echo htmlspecialchars($r['message']); ?></td>
                                <td>
                                    <?php if ($r['itemImage']): ?>
                                        <img src="<?php echo htmlspecialchars($r['itemImage']); ?>" class="table-image" alt="Item Image">
                                    <?php else: ?>
                                        <span class="text-gray">No Image</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="action-buttons" style="justify-content: start; gap: 8px;">
                                        <form method="post" class="inline">
                                            <input type="hidden" name="delete_reunite_id" value="<?php echo $r['id']; ?>">
                                            <button type="submit" onclick="return confirm('Are you sure you want to delete this entry?');" class="action-btn" style="padding: 6px 12px; font-size: 12px; background: #dc3545;">Delete</button>
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