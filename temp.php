

<!doctype html>
<html>
  <head>
    <meta charset="utf-8">
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="YakuzaJS.js"></script>
  </head>
  <body>
    <div class="head">
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
            <a href="reunite.html">Reunite</a>
            <a href="About.html">About Us</a>
            <a href="Contact.html">Contact Us</a>
            <a href="login.php">Login</a>
          </div>
        </div>
      </div>
      <div class="message">
        <h1>Turning Clicks Into Connections</h1>
      </div>
      <div class="search">
        <input type="text" placeholder="Search for products..." />
        <button><i class="fa fa-search"></i></button>
      </div>
    </div>
    <!-- HTML -->
    <div class="services">
      <h2>OUR SERVICES</h2>
      <div class="service-buttons">
        <div class="service">
          <a href="buy.html">
            <h2>Buy</h2>
            <img src="PICTURES/1.jpg" alt="Buy">
          </a>
        </div>
        <div class="service">
          <a href="sell.php">
            <h2>SELL</h2>
            <img src="PICTURES/2.jpg" alt="Sell">
          </a>
        </div>
        <div class="service">
          <a href="reunite.html">
            <h2>Lost and Found</h2>
            <img src="PICTURES/3.jpg" alt="Lost and Found">
          </a>
        </div>
      </div>
    </div>

    <h1>Some of The products sold by students</h1>
    
<?php 
include 'yakuzaconnection.php';

$testing_prod = "SELECT p.*, u.store_name AS seller_name 
                 FROM products p 
                 JOIN users u ON p.user_id = u.id"; 
$tester = $conn->query($testing_prod);

if (!$tester) {
    die("Query failed: " . $conn->error);
}
?>


    <section class="random_products">
      <?php
      $result = $conn->query("SELECT * FROM products ORDER BY RAND() LIMIT 4");

      while ($row = $result->fetch_assoc()) { ?>
          <div class="item-card" 
              data-prodtype="<?php echo htmlspecialchars($row['prodType']); ?>" 
              data-id="<?php echo $row['id']; ?>"
              onclick="goToProduct(<?php echo $row['id']; ?>)">
              <img src="<?php echo htmlspecialchars($row['prodPic']); ?>" 
                  alt="<?php echo htmlspecialchars($row['prodName']); ?>" 
                  class="item-pic">
              <h3><?php echo htmlspecialchars($row['prodName']); ?></h3>
              <p><?php echo 'R ' . htmlspecialchars($row['prodPrice']); ?></p>

              <button class="filter-btn"
                      onclick="event.stopPropagation(); addToCart(
                          '<?php echo htmlspecialchars($row['prodName']); ?>',
                          '<?php echo htmlspecialchars($row['prodPrice']); ?>',
                          '<?php echo htmlspecialchars($row['prodPic']); ?>',
                          '<?php echo htmlspecialchars($row['id']); ?>')">
                  Add To Cart
              </button>
          </div>
      <?php } ?>
    </div>
    </section>

    <div class="banners">
      <img src="PICTURES/banner2.jpg" alt="Banner 1" id="Sale1" class = "banner">
    </div>

    <a href="buy_maz.php"><h1>See More Products</h1></a>
     
    <footer>
      <p>&copy; 2025 Campus Collective | Connecting Students Everywhere</p>
      <p>
        <a href="https://instagram.com" target="_blank"><i class="fab fa-instagram"></i></a> <!--target = "_blank" makes sure that the link starts on a new window/tab-->
        <a href="https://facebook.com" target="_blank"><i class="fab fa-facebook"></i></a> <!--target = "_blank" makes sure that the link starts on a new window/tab-->
        <a href="mailto:contact@campuscollective.com"><i class="fa fa-envelope"></i></a> 
      </p>
      <p>
        <button><a href="https://www.takealot.com/" target="_blank">Couldn't find what you were looking for?</a></button>
      </p>
      <!--THE NAVIGATION OBJECTS ARE SEEN IN THE JAVA SCRIPT FILE. THE WEBSITE USED IT USING THE FUNCTION THAT ARE RELATED TO THESE BUTTONS-->
      <p><button class = "check-online" onclick = connStatus();>Connectivity Issues?</button></p>
      <p><button class = "know-more" onclick= DevInfo();>Nerdy Fun Facts</button></p>
    </footer>
  </body>
</html>
