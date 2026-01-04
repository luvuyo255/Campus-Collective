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

// Handle user actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['promote_user_id'])) {
        $promote_id = intval($_POST['promote_user_id']);
        if ($promote_id !== $user_id) {
            $stmt = $conn->prepare("UPDATE users SET role = 'admin' WHERE id = ?");
            $stmt->bind_param("i", $promote_id);
            $stmt->execute();
        }
    } elseif (isset($_POST['demote_user_id'])) {
        $demote_id = intval($_POST['demote_user_id']);
        if ($demote_id !== $user_id) {
            $stmt = $conn->prepare("UPDATE users SET role = 'user' WHERE id = ?");
            $stmt->bind_param("i", $demote_id);
            $stmt->execute();
        }
    } elseif (isset($_POST['delete_user_id'])) {
        $delete_id = intval($_POST['delete_user_id']);
        if ($delete_id !== $user_id) {
            $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
            $stmt->bind_param("i", $delete_id);
            $stmt->execute();
        }
    }
    header("Location: AdminUsers.php");
    exit();
}

// Fetch all users
$users = $conn->query("SELECT id, firstname, lastname, email, role, created_at FROM users ORDER BY created_at DESC");

// Stats
$total_users = $conn->query("SELECT COUNT(*) AS c FROM users")->fetch_assoc()['c'];
$admin_users = $conn->query("SELECT COUNT(*) AS c FROM users WHERE role='admin'")->fetch_assoc()['c'];
$regular_users = $conn->query("SELECT COUNT(*) AS c FROM users WHERE role='user'")->fetch_assoc()['c'];
$new_users_today = $conn->query("SELECT COUNT(*) AS c FROM users WHERE DATE(created_at) = CURDATE()")->fetch_assoc()['c'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users | Campus Collective</title>
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
            <a href="AdminUsers.php" class="sidebar-link active"><i class="fas fa-users"></i> Users</a>
            <a href="AdminProducts.php" class="sidebar-link"><i class="fas fa-box"></i> Products</a>
            <a href="AdminOrders.php" class="sidebar-link"><i class="fas fa-shopping-cart"></i> Orders</a>
            <a href="AdminReunite.php" class="sidebar-link"><i class="fas fa-users"></i> Reunite</a>
            <a href="AdminContact.php" class="sidebar-link"><i class="fas fa-envelope"></i> Contact</a>
            <a href="logout.php" class="sidebar-link"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>

        <!-- Main Content -->
        <div class="admin-main">
            <h1 class="main-title">User Management</h1>

            <!-- User Stats -->
            <div class="stats-grid">
                <div class="stat-card">
                    <h3 class="stat-title">Total Users</h3>
                    <h2 class="stat-value"><?php echo $total_users; ?></h2>
                </div>
                <div class="stat-card">
                    <h3 class="stat-title">Admin Users</h3>
                    <h2 class="stat-value"><?php echo $admin_users; ?></h2>
                </div>
                <div class="stat-card">
                    <h3 class="stat-title">Regular Users</h3>
                    <h2 class="stat-value"><?php echo $regular_users; ?></h2>
                </div>
                <div class="stat-card">
                    <h3 class="stat-title">New Today</h3>
                    <h2 class="stat-value"><?php echo $new_users_today; ?></h2>
                </div>
            </div>

            <!-- Users Table -->
            <div class="section">
                <h2 class="section-title"><i class="fas fa-users"></i> All Users</h2>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr class="table-header">
                                <th>ID</th>
                                <th>Full Name</th>
                                <th>Email</th>
                                <th>Joined</th>
                                <th>Role</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($u = $users->fetch_assoc()): ?>
                            <tr class="table-row">
                                <td><?php echo $u['id']; ?></td>
                                <td><?php echo htmlspecialchars($u['firstname'].' '.$u['lastname']); ?></td>
                                <td><?php echo htmlspecialchars($u['email']); ?></td>
                                <td><?php echo date('d M Y', strtotime($u['created_at'])); ?></td>
                                <td>
                                    <span class="<?php echo ($u['role'] === 'admin') ? 'text-green' : 'text-gray'; ?>">
                                        <?php echo ucfirst($u['role']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-buttons" style="justify-content: start; gap: 8px;">
                                        <?php if ($u['role'] !== 'admin' && $u['id'] != $user_id): ?>
                                            <form method="post" class="inline">
                                                <input type="hidden" name="promote_user_id" value="<?php echo $u['id']; ?>">
                                                <button type="submit" onclick="return confirm('Promote this user to admin?');" class="action-btn" style="padding: 6px 12px; font-size: 12px;">Promote</button>
                                            </form>
                                        <?php elseif ($u['role'] === 'admin' && $u['id'] != $user_id): ?>
                                            <form method="post" class="inline">
                                                <input type="hidden" name="demote_user_id" value="<?php echo $u['id']; ?>">
                                                <button type="submit" onclick="return confirm('Demote this admin to regular user?');" class="action-btn" style="padding: 6px 12px; font-size: 12px; background: #ff9800;">Demote</button>
                                            </form>
                                        <?php elseif ($u['id'] == $user_id): ?>
                                            <span class="text-gray">You</span>
                                        <?php endif; ?>
                                        <?php if ($u['id'] != $user_id): ?>
                                            <form method="post" class="inline">
                                                <input type="hidden" name="delete_user_id" value="<?php echo $u['id']; ?>">
                                                <button type="submit" onclick="return confirm('Are you sure you want to delete this user? This action cannot be undone.');" class="action-btn" style="padding: 6px 12px; font-size: 12px; background: #dc3545;">Delete</button>
                                            </form>
                                        <?php endif; ?>
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