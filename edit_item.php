<!doctype html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Edit Item | Campus Collective</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"> 
</head>
<body>
<div class="dashboard-container">
    <div class="dashboard-section" style="max-width:500px;margin:40px auto;">
        <h2 style="text-align:center;color:#4c0de0;margin-bottom:25px;">
            <i class="fas fa-edit"></i> Edit Item
        </h2>
        <?php
        include 'yakuzaconnection.php';
        session_start();
        $user_id = $_SESSION['id'] ?? 0;

        if (isset($_GET['id'])) {
            $item_id = $_GET['id'];
            $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
            $stmt->bind_param("i", $item_id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result && $result->num_rows > 0) {
                $item = $result->fetch_assoc();
            } else {
                echo "<div class='alert alert-error'>Item not found.</div>";
                exit;
            }
        } else {
            echo "<div class='alert alert-error'>Invalid request.</div>";
            exit;
        }

        $stmt = $conn->prepare("SELECT store_pic FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $storeResult = $stmt->get_result();
        $storeData = $storeResult->fetch_assoc() ?? [];
        ?>

        <form action="update_item.php" method="POST" class="contact-form" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?php echo $item['id']; ?>">
            <input type="hidden" id="currentCategory" value="<?php echo htmlspecialchars($item['prodType']); ?>">

            <!-- Product Name -->
            <div class="form-group">
                <label for="prodName">Product Name:</label>
                <input type="text" id="prodName" name="prodName" maxlength="40" value="<?php echo htmlspecialchars($item['prodName']); ?>" required>
            </div>

            <!-- Product Price -->
            <div class="form-group">
                <label for="prodPrice">Product Price:</label>
                <input type="text" id="prodPrice" name="prodPrice" value="<?php echo htmlspecialchars($item['prodPrice']); ?>" required>
            </div>

            <!-- Category -->
            <div class="form-group">
                <label for="prodType">Category:</label>
                <input type="text" id="prodType" name="prodType" value="<?php echo htmlspecialchars($item['prodType']); ?>" readonly>
            </div>

            <?php if ($item['prodType'] === 'Service'): ?>
                <!-- Availability -->
                <div class="form-group" id="availabilityRow">
                    <label for="serviceAvailability">Service Availability:</label>
                    <select id="serviceAvailability" name="serviceAvailability">
                        <option value="Available" <?php echo ($item['availability'] === 'Available') ? 'selected' : ''; ?>>Available</option>
                        <option value="Unavailable" <?php echo ($item['availability'] === 'Unavailable') ? 'selected' : ''; ?>>Unavailable</option>
                    </select>
                </div>
            <?php else: ?>
                <!-- Quantity -->
                <div class="form-group" id="quantityRow">
                    <label for="prodQuantity">Quantity:</label>
                    <input type="number" id="prodQuantity" name="prodQuantity" min="0" value="<?php echo htmlspecialchars($item['quantity'] ?? 0); ?>">
                </div>

                <!-- Product Condition -->
                <div class="form-group" id="conditionRow">
                    <label for="prodCondition">Product Condition:</label>
                    <input type="text" id="prodCondition" name="prodCondition" maxlength="40" value="<?php echo htmlspecialchars($item['condition'] ?? ''); ?>">
                </div>
            <?php endif; ?>


            <!-- Product Image -->
            <div class="form-group">
                <label for="prodPic">Product Image:</label>
                <?php if (!empty($item['prodPic'])): ?>
                    <div style="margin-bottom:10px;">
                        <img src="<?php echo htmlspecialchars($item['prodPic']); ?>" alt="Current Image" style="max-width:100%;max-height:150px;border-radius:8px;">
                    </div>
                <?php endif; ?>
                <input type="file" id="prodPic" name="prodPic">
                <input type="hidden" name="currentPic" value="<?php echo htmlspecialchars($item['prodPic']); ?>">
            </div>

            <!-- Store Picture -->
            <div class="form-group">
                <label for="storePic">Store Picture:</label>
                <?php if (!empty($storeData['store_pic'])): ?>
                    <div style="margin-bottom:10px;">
                        <img src="<?php echo htmlspecialchars($storeData['store_pic']); ?>" alt="Current Store Picture" style="max-width:100%; max-height:150px; border-radius:8px;">
                    </div>
                <?php endif; ?>
                <input type="file" id="storePic" name="storePic">
                <input type="hidden" name="currentStorePic" value="<?php echo htmlspecialchars($storeData['store_pic']); ?>">
            </div>

            <div class="form-actions">
                <a href="dashboard.php" class="cancel-btn" style="text-decoration:none;">Cancel</a>
                <button type="submit" class="save-btn">Update Item</button>
            </div>
        </form>
    </div>
</div>
<script src="YakuzaJS.js"></script>
</body>
</html>
