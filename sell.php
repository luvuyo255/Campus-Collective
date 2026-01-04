<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sell Products</title>
  <link rel="stylesheet" href="styles.css">
  <link rel="stylesheet" href="myreunite.css"> <!-- added so it shares form styling -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  
</head>
<body>
  <!-- Header -->
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
            <a href="Contact.html">Contact Us</a>
            <a href="login.php">Login</a>
          </div>
        </div>
      </div>

  <!-- Product Form -->
  <div class="form-container">
    <h2>Enter Listing Details</h2>
    <p>Fill in the details below to showcase your listing on RU MarketPlace.</p>

    <form id="sell-form" action="sell2.php" method="POST" enctype="multipart/form-data">

      <!-- Listing Name -->
      <div class="form-row">
        <input type="text" id="prod-name" name="prod-name" maxlength = "40" placeholder="Listing Name *" required>
      </div>

      <!-- Listing Category -->
      <div class="form-row">
        <select id="prod-type" name="prod-type" required>
          <option value="" selected disabled>Select Category *</option>
          <option value ="Appliances">Appliances</option>
          <option value ="Beauty">Beauty</option>
          <option value="Books & Stationery">Books & Stationery</option>
          <option value="Clothing">Clothing</option>
          <option value="Electronics & Gaming">Electronics & Gaming</option>
          <option value="Service">Service</option>
          <option value="Snacks">Snacks</option>
          <option value="Other">Other</option>
        </select>
      </div>

      <!-- Product Condition --> 
       <div class="form-row" id="prod-condition-row">
         <select id="prod-condition" name="prod-condition">
            <option value="" selected disabled>Product Condition *</option>
            <option value ="New">New</option>
            <option value ="Used">Used</option>
        </select>  
       </div>

      <!-- Listing Description -->
      <div class="form-row">
        <textarea id="prod-desc" name="prod-desc" rows="5" placeholder="Listing Description *" required></textarea>
      </div>

      <!-- Listing Price -->
      <div class="form-row">
        <input type="number" id="prod-price" name="prod-price" placeholder="Price (R) *" min="1" required>
      </div>

      <!-- Service  Availability -->
      <div class="form-row" id="service-availability-row">
        <select id="service-availability" name="service-availability">
          <option value="" selected disabled>Service Availability *</option>
          <option value="Available">Available</option>
          <option value="Unavailable">Unavailable</option>
        </select>
      </div>

      <!--Product quantity (MAZ PUT THIS HERE, DO NOT REMOVE NTSHONGWANA PLEASE)-->
       <div class="form-row" id="prod-quantity-row">
        <input type="number" id="prod-quantity" name="prod-quantity" placeholder="Product Quantity *" min="1">
      </div>
      <br>

      <!-- Listing Image -->
      <div class="form-row-picture">
        <label for="prod-pic">Upload Listing Image (optional)</label>
        <input type="file" id="prod-pic" name="prod-pic" accept="image/*">
      </div>
      <br>

      <!-- Checkbox -->
      <label class="checkbox">
        <input type="checkbox" required>
        <span class="checkmark"></span>
        I confirm the above details are accurate and ready for listing
      </label>

      <!-- Submit -->
      <button type="submit">Showcase Listing</button>
    </form>
  </div>
  <script src="YakuzaJS.js"></script>
  <!-- Footer -->
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