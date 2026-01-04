<!doctype html>
<html>
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick-theme.min.css">
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
            <a href="reunite.php">Reunite</a>
            <a href="About.html">About Us</a>
            <a href="Contact.php">Contact Us</a>
            <a href="login.php">Login</a>
          </div>
        </div>
      </div>
      <div class="message">
        <h1>Turning Clicks Into Connections</h1>
      </div>
      <!-- Search Bar -->
      <form method="GET" action="buy_maz.php" class="search">
          <input type="text" name="q" placeholder="Search for products..." />
          <button type="submit"><i class="fa fa-search"></i></button>
      </form>
      <!-- End Search Bar -->
    </div>
    <!-- HTML -->
    <div class="services">
      <h2>OUR SERVICES</h2>
      <div class="service-buttons">
        <div class="service">
          <a href="buy_maz.php">
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
          <a href="reunite.php">
            <h2>Lost and Found</h2>
            <img src="PICTURES/3.jpg" alt="Lost and Found">
          </a>
        </div>
      </div>
    </div>
    
<?php 
include 'yakuzaconnection.php';

// Only get active products not deleted which means the status has to be active, not sold or deleted. 
$testing_prod = "SELECT p.*, u.store_name AS seller_name 
                 FROM products p 
                 JOIN users u ON p.user_id = u.id
                 WHERE p.status = 'active'";
$tester = $conn->query($testing_prod);

if (!$tester) {
    die("Query failed: " . $conn->error);
}
?>

    <section class="random_products">
      <?php
      $products = [];
      while ($row = $tester->fetch_assoc()) {
          $products[] = $row;
      }
      shuffle($products);
      $products = array_slice($products, 0, 4);

      foreach ($products as $row) { ?>
          <!-- your product display code here -->
      <?php } ?>
    </section>

    <!-- Featured Products Carousel -->
    <section class="featured-carousel">
      <h2>Featured Listings</h2>
      <div class="carousel">
        <?php
        foreach ($products as $row) { ?>
          <div class="carousel-item">
            <a href="prod_details.php?id=<?php echo $row['id']; ?>">
              <img src="<?php echo htmlspecialchars($row['prodPic']); ?>" alt="<?php echo htmlspecialchars($row['prodName']); ?>">
            </a>
            <h3><?php echo htmlspecialchars($row['prodName']); ?></h3>
            <p><?php echo 'R ' . htmlspecialchars($row['prodPrice']); ?></p>
          </div>
        <?php } ?>
      </div>
      <a href="buy_maz.php"><h1>See More Listings</h1></a>
    </section>

    <!-- Testimonials Section -->
    <section class="testimonials">
      <h2>What Our Students Say</h2>
      <div class="testimonial-container">
        <div class="testimonial">
          <p>"Campus Collective helped me sell my old textbooks and buy affordable gear for my dorm. It's a lifesaver!"</p>
          <h4>- Sarah M., Rhodes University</h4>
        </div>
        <div class="testimonial">
          <p>"I found my lost calculator thanks to the Reunite feature. This platform is amazing for students!"</p>
          <h4>- James K., Rhodes University</h4>
        </div>
        <div class="testimonial">
          <p>"The community here is so supportive. I got great deals and connected with other students easily."</p>
          <h4>- Lerato P., Rhodes University</h4>
        </div>
      </div>
    </section>

    <div class="banners">
      <img src="PICTURES/banner2.jpg" alt="Banner 1" id="Sale1" class = "banner">
    </div>
  
<!--  Incomplete Newsletter Section - to be finished later
    !-- Newsletter Signup Form ->
    <section class="newsletter">
      <h2>Join Our Community</h2>
      <p>Stay updated with the latest deals and student tips!</p>
      <form method="POST" action="subscribe.php" class="newsletter-form">
        <input type="email" name="email" placeholder="Enter your email address" required>
        <button type="submit">Subscribe</button>
      </form>
    </section>
        -->

    <footer>
      <p>&copy; 2025 Campus Collective | Connecting Students Everywhere</p>
      <p>
        <a href="https://instagram.com" target="_blank"><i class="fab fa-instagram"></i></a>
        <a href="https://facebook.com" target="_blank"><i class="fab fa-facebook"></i></a>
        <a href="mailto:contact@campuscollective.com"><i class="fa fa-envelope"></i></a> 
      </p>
      <p>FRAUD HOTLINE: +27 78 966 4584 OR +27 81 534 6678 </p>
      <p>
        <button><a href="https://www.takealot.com/" target="_blank">Couldn't find what you were looking for?</a></button>
      </p>
      <p><button class="check-online" onclick="connStatus();">Connectivity Issues?</button></p>
      <p><button class="know-more" onclick="DevInfo();">Nerdy Fun Facts</button></p>
    </footer>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.min.js"></script>
    <script src="YakuzaJSh.js"></script> 
  </body>
</html>