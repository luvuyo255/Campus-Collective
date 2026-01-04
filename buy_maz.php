<!doctype html> 
<html> 
<head> 
    <meta charset="utf-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="piko.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="YakuzaJS.js"></script>
    <title>Campus Collective - Shop</title>
</head> 
<?php include 'yakuzaconnection.php' ;
?>
<body>
      <div class="header">
        <div class="top">
          <h1 class="message">Campus Collective</h1>
          <div class="icons">
            <a href="dashboard.php"><i class="fa fa-user"></i></a>
            <a href="cart.html"><i class="fa fa-shopping-cart"></i></a>
          </div>
        </div>
        <div class="nav">
          <div class="logo">
            <img src="PICTURES/LOGO FINAL.png" alt="logo">
          </div>
          <div class="navs">
            <a href="home.php">Home</a>
            <a href="buy_maz.php">Shop</a>
            <a href="sell.php">Sell</a>
            <a href="reunite.php">Reunite</a>
            <a href="About.html">About Us</a>
            <a href="Contact.php">Contact Us</a>
            <a href="login.php">Login</a>
          </div>
        </div>
      </div>
      
      <!-- Search Bar -->
<form method="GET" action="buy_maz.php" class="search">
    <input type="text" name="q" placeholder="Search for products..." value="<?php echo isset($_GET['q']) ? htmlspecialchars($_GET['q']) : ''; ?>" />
    <button type="submit"><i class="fa fa-search"></i></button>
</form>
<!-- End Search Bar -->

<!-- Catalog Container -->
<div class="catalog">

    <div class="filters">
        <button class="filter-btn">All</button>
        <button class="filter-btn">Books & Stationery</button>
        <button class="filter-btn">Electronics & Gaming</button>
        <button class="filter-btn">Appliances</button>
        <button class="filter-btn">Clothing</button>
        <button class="filter-btn">Snacks</button>
        <button class="filter-btn">Service</button>
        <button class="filter-btn">Beauty</button>
        <button class="filter-btn">Other</button>
    </div>

<?php 
include 'yakuzaconnection.php';

$search = '';
if (isset($_GET['q']) && trim($_GET['q']) !== '') {
    $search = trim($_GET['q']);
    $testing_prod = "SELECT p.*, u.store_name AS seller_name, u.id AS user_id
                     FROM products p 
                     JOIN users u ON p.user_id = u.id
                     WHERE p.status != 'deleted'
                     AND (p.prodName LIKE ? OR p.prodDesc LIKE ?)";
    $stmt = $conn->prepare($testing_prod);
    $like = '%' . $search . '%';
    $stmt->bind_param("ss", $like, $like);
    $stmt->execute();
    $tester = $stmt->get_result();
} else {
    $testing_prod = "SELECT p.*, u.store_name AS seller_name, u.id AS user_id
                     FROM products p 
                     JOIN users u ON p.user_id = u.id
                     WHERE p.status != 'deleted'";
    $tester = $conn->query($testing_prod);
}

if (!$tester) {
    die("Query failed: " . $conn->error);
}
?>

<section class="random_products">
<?php while ($rowtest = $tester->fetch_assoc()) { ?>
    <div class="item-card" 
         data-prodtype="<?php echo htmlspecialchars($rowtest['prodType']); ?>" 
         data-id="<?php echo $rowtest['id']; ?>"
         onclick="goToProduct(<?php echo $rowtest['id']; ?>)">
        <img src="<?php echo htmlspecialchars($rowtest['prodPic']); ?>" 
             alt="<?php echo htmlspecialchars($rowtest['prodName']); ?>" 
             class="item-pic">
        <h3 class="prodName"><?php echo htmlspecialchars($rowtest['prodName']); ?></h3>
        <p>
            <span class="seller-link" onclick="event.stopPropagation(); window.location.href='profile.php?id=<?php echo $rowtest['user_id']; ?>'">
                <?php echo htmlspecialchars($rowtest['seller_name']); ?>
            </span>
        </p>
        
        <p><?php echo 'R ' . htmlspecialchars($rowtest['prodPrice']); ?></p>
<!--         
        <div class="prod-desc">
            
        </div>
-->        
        <button class="buy-btn" 
                onclick="event.stopPropagation(); addToCart('<?php echo htmlspecialchars($rowtest['prodName']); ?>',
                                                          '<?php echo htmlspecialchars($rowtest['prodPrice']); ?>',
                                                          '<?php echo htmlspecialchars($rowtest['prodPic']); ?>',
                                                          '<?php echo htmlspecialchars($rowtest['seller_name']); ?>',
                                                          '<?php echo htmlspecialchars($rowtest['id']); ?>')">
            Add To Cart
        </button>
    </div>
<?php } ?>
</section>

<?php

?>
</section>

 <footer>
      <p>&copy; 2025 Campus Collective | Connecting Students Everywhere</p>
      <p>
        <a href="https://instagram.com" target="_blank"><i class="fab fa-instagram"></i></a>
        <a href="https://facebook.com" target="_blank"><i class="fab fa-facebook"></i></a>
        <a href="mailto:contact@campuscollective.com"><i class="fa fa-envelope"></i></a>
      </p>
    </footer>
  
 
</body>
</html>