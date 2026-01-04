<?php
// Start session FIRST - before any output
session_start();

// Include your DB connection
require("yakuzaconnection.php");

// Check if user is logged in
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

// Get user ID from session
$user_id = $_SESSION['id'];

// Fetch user data including store info
$user_sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($user_sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_result = $stmt->get_result();

if ($user_result && $user_result->num_rows > 0) {
    $user_data = $user_result->fetch_assoc();
} else {
    // Redirect to login if user not found
    header("Location: login.php");
    exit();
}

// Handle store update if form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['store_name'])) {
    $store_name = trim($_POST['store_name']);
    $store_description = trim($_POST['store_description']);
    $store_pic = $user_data['store_pic']; // default to current picture

    // Handle uploaded store picture
    if (isset($_FILES['store_pic']) && $_FILES['store_pic']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/'; // make sure this folder exists and is writable
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

        $tmp_name = $_FILES['store_pic']['tmp_name'];
        $filename = basename($_FILES['store_pic']['name']);
        $target_file = $upload_dir . time() . '_' . $filename;

        if (move_uploaded_file($tmp_name, $target_file)) {
            $store_pic = $target_file; // use the new uploaded file
        } else {
            $error_message = "Failed to upload store picture.";
        }
    }

    // Update the database
    $update_sql = "UPDATE users SET store_name = ?, store_description = ?, store_pic = ? WHERE id = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("sssi", $store_name, $store_description, $store_pic, $user_id);

    if ($stmt->execute()) {
        // Update local user data
        $user_data['store_name'] = $store_name;
        $user_data['store_description'] = $store_description;
        $user_data['store_pic'] = $store_pic;
        $success_message = "Store details updated successfully!";
    } else {
        $error_message = "Error updating store details: " . $stmt->error;
    }
}

// Fetch user's products
$products_sql = "SELECT * FROM products WHERE user_id = ? and status != 'deleted'";
$stmt = $conn->prepare($products_sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$products_result = $stmt->get_result();

// Count user stats
$items_bought_sql = "SELECT COUNT(*) as count FROM orders WHERE buyer_id = ? and status != 'deleted'";
$stmt = $conn->prepare($items_bought_sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$items_bought_result = $stmt->get_result();
$items_bought = $items_bought_result->fetch_assoc()['count'] ?? 0;

$items_sold_sql = "SELECT COUNT(*) as count FROM products WHERE user_id = ? AND status = 'sold'";
$stmt = $conn->prepare($items_sold_sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$items_sold_result = $stmt->get_result();
$items_sold = $items_sold_result->fetch_assoc()['count'] ?? 0;

$active_listings = $products_result->num_rows;

// Format join date
$join_date = date('F Y', strtotime($user_data['created_at']));

// Set default store values if not set
$store_name = $user_data['store_name'] ?? 'My Campus Store';
$store_description = $user_data['store_description'] ?? 'Selling quality items for students';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Campus Collective</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="dashboard-container">
        <!-- Success/Error Messages -->
        <?php if (isset($_GET['success']) && $_GET['success'] == 'item_removed'): ?>
            <div class="alert alert-success">
                Listing removed successfully!
            </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['error']) && $_GET['error'] == 'remove_failed'): ?>
            <div class="alert alert-error">
                Failed to remove listing. Please try again.
            </div>
        <?php endif; ?>
        
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($success_message); ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($error_message)): ?>
            <div class="alert alert-error">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['success']) && $_GET['success'] == 'item_updated'): ?>
            <div class="alert alert-success">
                Listing updated successfully!
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['error']) && $_GET['error'] == 'update_failed'): ?>
            <div class="alert alert-error">
                Failed to update listing. Please try again.
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['success']) && $_GET['success'] == 'request_accepted'): ?>
            <div class="alert alert-success">
                Request accepted! The buyer will be notified.
            </div>
        <?php endif; ?>
        <?php if (isset($_GET['error']) && $_GET['error'] == 'accept_failed'): ?>
            <div class="alert alert-error">
                Failed to accept request. Please try again.
            </div>
        <?php endif; ?>
        <?php if (isset($_GET['error']) && $_GET['error'] == 'not_authorized'): ?>
            <div class="alert alert-error">
                You are not authorized to accept this request.
            </div>
        <?php endif; ?>
        <?php if (isset($_GET['error']) && $_GET['error'] == 'invalid_request'): ?>
            <div class="alert alert-error">
                Invalid request.
            </div>
        <?php endif; ?>

        <div class="dashboard-welcome">
            <h1>Welcome back, <span id="userFirstName"><?php echo htmlspecialchars($user_data['firstname']); ?></span>!</h1>
            <p>Manage your store and track your activity</p>
        </div>

        <div class="dashboard-section">
            <h2>Quick Actions</h2>
            <div class="action-buttons">
                <a href="sell.php" class="action-btn">
                    <i class="fas fa-plus-circle"></i>
                    <span>Add Listing to Sell</span>
                </a>
                <a href="buy_maz.php" class="action-btn">
                    <i class="fas fa-shopping-bag"></i>
                    <span>Browse Listings</span>
                </a>
                <a href="reunite.php" class="action-btn">
                    <i class="fas fa-search"></i>
                    <span>Lost & Found</span>
                </a>
                <a href="cart.html" class="action-btn">
                    <i class="fas fa-shopping-cart"></i>
                    <span>View Cart</span>
                </a>
                <a href="logout.php" class="action-btn">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>LOG OUT</span>
                </a>
            </div>
        </div>

        <div class="dashboard-content">
            <div class="user-sidebar">
                <div class="user-card">
                    <div class="user-avatar">
                        <?php if (!empty($user_data['store_pic'])): ?>
                            <img src="<?php echo htmlspecialchars($user_data['store_pic']); ?>" 
                                alt="Store Picture" 
                                style="width: 100px; height: 100px; object-fit: cover; border-radius: 50%; border: 2px solid #4c0de0;">
                        <?php else: ?>
                            <i class="fas fa-user-circle" style="font-size: 100px; color: #aaa;"></i>
                        <?php endif; ?>
                    </div>
                    <div class="user-info">
                        <h3 id="userFullName"><?php echo htmlspecialchars($user_data['full_name']); ?></h3>
                        <p id="userEmail"><?php echo htmlspecialchars($user_data['email']); ?></p>
                        <p>Member since: <span id="joinDate"><?php echo $join_date; ?></span></p>
                        <?php if (!empty($user_data['cellphone'])): ?>
                            <p>Phone: <?php echo htmlspecialchars($user_data['cellphone']); ?></p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="quick-stats">
                    <h4>Your Activity</h4>
                    <div class="stat-item">
                        <span class="stat-label">Orders Placed</span>
                        <span class="stat-value" id="itemsBought"><?php echo $items_bought; ?></span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">Orders Completed</span>
                        <span class="stat-value" id="itemsSold"><?php echo $items_sold; ?></span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">Active Listings</span>
                        <span class="stat-value" id="activeListings"><?php echo $active_listings; ?></span>
                    </div>
                </div>
            </div>
            <div class="dashboard-main">
                <div class="dashboard-section store-management">
                    <div class="store-header">
                        <div>
                            <h2>Your Store</h2>
                            <div class="store-name" id="storeName"><?php echo htmlspecialchars($store_name); ?></div>
                            <?php if (!empty($store_description)): ?>
                                <p style="color: black; margin-top: 5px;"><?php echo htmlspecialchars($store_description); ?></p>
                            <?php endif; ?>
                        </div>
                        <button class="edit-store-btn" id="editStoreBtn">
                            <i class="fas fa-edit"></i> Edit Store
                        </button>
                    </div>
                    
                    <div class="store-items" id="storeItems">
                        <?php
                        if ($products_result && $products_result->num_rows > 0) {
                            while($row = $products_result->fetch_assoc()) {
                                echo '
                                <div class="store-item" data-id="' . $row['id'] . '">
                                    <img src="' . ($row['prodPic'] ? htmlspecialchars($row['prodPic']) : 'PICTURES/placeholder.jpg') . '" alt="' . htmlspecialchars($row['prodName']) . '" class="item-image">
                                    <div class="item-details">
                                        <div class="item-title">' . htmlspecialchars($row['prodName']) . '</div>
                                        <div class="item-price">R' . htmlspecialchars($row['prodPrice']) . '</div>
                                        <div class="item-actions">
                                            <button class="remove-btn" onclick="removeItem(' . $row['id'] . ')">
                                                <i class="fas fa-trash"></i> Remove
                                            </button>
                                            <a href="edit_item.php?id=' . $row['id'] . '" class="edit-store-btn">
                                                <i class="fas fa-edit"></i> Edit Listing
                                            </a>
                                        </div>
                                    </div>
                                </div>';
                            }
                        } else {
                            echo '
                            <div class="no-items">
                                <i class="fas fa-box-open"></i>
                                <p>You haven\'t added any items to sell yet.</p>
                                <p>Click "Add Item to Sell" to get started!</p>
                            </div>';
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="dashboard-section notifications">
            <h2>Notifications</h2>
            <h3>New Purchase Requests</h3>
            <?php
            $notif_sql = "SELECT o.*, p.prodName, p.prodPic, u.full_name AS buyer_name 
                          FROM orders o 
                          JOIN products p ON o.product_id = p.id 
                          JOIN users u ON o.buyer_id = u.id 
                          WHERE o.seller_id = ? AND (o.status = 'pending' OR o.status = 'accepted') AND o.notify != 'deleted'
                          ORDER BY o.created_at DESC";
            $stmt = $conn->prepare($notif_sql);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $notif_result = $stmt->get_result();

            if ($notif_result->num_rows > 0) {
                while ($order = $notif_result->fetch_assoc()) {
                    echo "
                    <div class='notification' data-id='{$order['id']}'>
                        <div class='notification-content' style='display: flex; align-items: center; gap: 20px;'>
                            <img src='" . htmlspecialchars($order['prodPic'] ?: 'PICTURES/placeholder.jpg') . "' 
                                 alt='" . htmlspecialchars($order['prodName']) . "' 
                                 style='width: 80px; height: 80px; object-fit: cover; border-radius: 8px;'>
                            <div>
                                <p><strong>" . htmlspecialchars($order['buyer_name']) . "</strong> wants to buy <em>" . htmlspecialchars($order['prodName']) . "</em></p>
                                <p>Message: " . htmlspecialchars($order['message'] ?: 'No message') . "</p>
                                <p>Location: " . htmlspecialchars($order['location'] ?: 'Not provided') . "</p>
                                <p>Phone Number: " . htmlspecialchars($order['phone_number'] ?: 'Not provided') . "</p>
                            </div>
                        </div>
                        <div class='notif-actions'>
                            <form method=\"post\" action=\"accept_request.php\" style=\"display:inline;\">
                                <input type=\"hidden\" name=\"order_id\" value=\"{$order['id']}\">
                                <button type=\"submit\" class=\"accept-btn\" onclick=\"return confirm('Accept this request?');\">
                                    <i class=\"fas fa-check\"></i> Accept
                                </button>
                            </form>
                            <a href='delete_notification.php?id={$order['id']}' class='delete-btn' onclick='return confirm(\"Are you sure you want to delete this notification?\");'>
                                <i class='fas fa-trash'></i> Delete
                            </a>
                        </div>
                    </div>";
                }
            } else {
                echo "<p>No new purchase requests.</p>";
            }
            ?>
            <h3>Your Accepted Orders</h3>
            <?php
            $accepted_orders_sql = "SELECT o.*, p.prodName, p.prodPic, u.full_name AS seller_name 
                       FROM orders o 
                       JOIN products p ON o.product_id = p.id 
                       JOIN users u ON o.buyer_id = u.id 
                       WHERE o.buyer_id = ? AND (o.status = 'accepted' OR o.status = 'completed') AND o.notify != 'deleted'
                       ORDER BY o.created_at DESC";
            $stmt = $conn->prepare($accepted_orders_sql);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $accepted_orders_result = $stmt->get_result();

            if ($accepted_orders_result->num_rows > 0) {
                while ($order = $accepted_orders_result->fetch_assoc()) {
                    echo "
                    <div class='notification' data-id='{$order['id']}'>
                        <div class='notification-content' style='display: flex; align-items: center; gap: 20px;'>
                            <img src='" . htmlspecialchars($order['prodPic'] ?: 'PICTURES/placeholder.jpg') . "' 
                                 alt='" . htmlspecialchars($order['prodName']) . "' 
                                 style='width: 80px; height: 80px; object-fit: cover; border-radius: 8px;'>
                            <div>
                                <p>Your order for <em>" . htmlspecialchars($order['prodName']) . "</em> has been accepted by <strong>" . htmlspecialchars($order['seller_name']) . "</strong></p>
                                <p>Message: " . htmlspecialchars($order['message'] ?: 'No message') . "</p>
                                <p>Location: " . htmlspecialchars($order['location'] ?: 'Not provided') . "</p>
                                <p>Phone Number: " . htmlspecialchars($order['phone_number'] ?: 'Not provided') . "</p>
                            </div>
                        </div>
                        <div class='notif-actions'>
                            <a href='delete_notification.php?id={$order['id']}' class='delete-btn' onclick='return confirm(\"Are you sure you want to delete this notification?\");'>
                                <i class='fas fa-trash'></i> Delete
                            </a>
                        </div>
                    </div>";
                }
            } else {
                echo "<p>No accepted orders.</p>";
            }
            ?>
        </div>
    </div>

    <!-- Edit Store Modal -->
    <div class="modal" id="storeModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Edit Store Details</h3>
                <button class="close-modal">&times;</button>
            </div>
            <form id="storeForm" method="POST" action="" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="storeNameInput">Store Name</label>
                    <input type="text" id="storeNameInput" name="store_name" placeholder="Enter your store name" value="<?php echo htmlspecialchars($store_name); ?>" required>
                </div>
                <div class="form-group">
                    <label for="storeDescription">Store Description</label>
                    <textarea id="storeDescription" name="store_description" placeholder="Describe your store..."><?php echo htmlspecialchars($store_description); ?></textarea>
                </div>
                <div class="form-group">
                    <label for="storePicInput">Store Picture</label>
                    <input type="file" id="storePicInput" name="store_pic" accept="image/*">
                    <?php if (!empty($user_data['store_pic'])): ?>
                        <p>Current picture: <img src="<?php echo htmlspecialchars($user_data['store_pic']); ?>" alt="Store Pic" style="width:50px;height:50px;"></p>
                    <?php endif; ?>
                </div>
                <div class="form-actions">
                    <button type="button" class="cancel-btn">Cancel</button>
                    <button type="submit" class="save-btn">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
    <script>
        // User data from PHP
        const userData = {
            firstName: "<?php echo $user_data['firstname']; ?>",
            lastName: "<?php echo $user_data['lastname']; ?>",
            email: "<?php echo $user_data['email']; ?>",
            joinDate: "<?php echo $join_date; ?>",
            itemsBought: <?php echo $items_bought; ?>,
            itemsSold: <?php echo $items_sold; ?>,
            activeListings: <?php echo $active_listings; ?>
        };

        // Store data
        let storeData = {
            name: <?php echo json_encode($store_name); ?>,
            description: <?php echo json_encode($store_description); ?>
        };

        // DOM elements
        const storeItemsContainer = document.getElementById('storeItems');
        const storeNameElement = document.getElementById('storeName');
        const storeModal = document.getElementById('storeModal');
        const storeForm = document.getElementById('storeForm');

        // Initialize dashboard
        document.addEventListener('DOMContentLoaded', function() {
            // User information is already populated by PHP
            
            // Set store name
            storeNameElement.textContent = storeData.name;
            
            // Event listeners for modals
            document.getElementById('editStoreBtn').addEventListener('click', () => openModal(storeModal));
            
            // Close modals when clicking X or cancel
            document.querySelectorAll('.close-modal, .cancel-btn').forEach(button => {
                button.addEventListener('click', (e) => {
                    e.preventDefault();
                    closeAllModals();
                });
            });
            
            // Close modal when clicking outside
            window.addEventListener('click', (e) => {
                if (e.target.classList.contains('modal')) {
                    closeAllModals();
                }
            });
            
            // Form submissions
            storeForm.addEventListener('submit', handleStoreUpdate);
        });

        // Open modal
        function openModal(modal) {
            closeAllModals();
            modal.style.display = 'flex';
            
            // Pre-fill store form if opening store modal
            if (modal === storeModal) {
                document.getElementById('storeNameInput').value = storeData.name;
                document.getElementById('storeDescription').value = storeData.description;
            }
        }

        // Close all modals
        function closeAllModals() {
            document.querySelectorAll('.modal').forEach(modal => {
                modal.style.display = 'none';
            });
        }

        // Handle store update
        function handleStoreUpdate(e) {
            // Let the form submit normally to PHP
            // The PHP will handle the database update and page refresh
        }

        // Sell item
        function sellItem(itemId) {
            if (confirm("Are you sure you want to mark this item as sold?")) {
                window.location.href = 'mark_sold.php?id=' + itemId;
            }
        }

        // Remove item
        function removeItem(itemId) {
            if (confirm("Are you sure you want to remove this item from your store?")) {
                window.location.href = 'remove_item.php?id=' + itemId;
            }
        }
    </script>
</body>
</html>
<?php
// Close DB connection
$conn->close();
?>  
