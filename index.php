<?php session_start();
require_once 'db.php'; ?>
<!DOCTYPE html>
<html>

<head>
  <title>MOSH</title>
  <link rel="stylesheet" href="style.css">
  <link rel="icon" type="image/png" href="favicon.png">
  <link href="https://fonts.googleapis.com/css2?family=Anton&family=Kanit:wght@300;400;600&display=swap" rel="stylesheet">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body>
  <nav class="navbar">
    <div class="nav-left">
      <a href="index.php" class="logo"><img src="logo.png" alt="MOSH"><span>ZA</span></a>
    </div>
    <button class="nav-toggle" id="nav-toggle" aria-label="Toggle navigation">
      <span class="bar"></span>
      <span class="bar"></span>
      <span class="bar"></span>
    </button>
    <div class="nav-links" id="nav-links">
      <?php if (!isset($_SESSION['username'])): ?>
        <a href="login.php" class="nav-btn">Login</a>
        <div class="divider"></div>
        <a href="register.php" class="nav-btn primary">Register</a>
      <?php elseif ($_SESSION['role'] === 'admin'): ?>
        <a href="admin.php" class="nav-btn">Admin</a>
        <div class="divider"></div>
        <a href="logout.php" class="nav-btn">Logout</a>
      <?php else: ?>
        <a href="dashboard.php" class="nav-btn">Profile</a>
        <div class="divider"></div>
        <a href="logout.php" class="nav-btn">Logout</a>
      <?php endif; ?>
    </div>
  </nav>

  <section class="hero-image hero-main">
    <div class="hero-text">
      <h1>Welcome to <img src="MOSH.png" alt="MOSH" class="hero-logo"></h1>
      <p>Everything. Anyone. MOSH.</p>
      <div class="hero-links">
        <a href="products.php" class="btn-1">Browse</a>
      </div>
    </div>
  </section>

  <section class="how-to-use-section" style="max-width: 1200px; margin: 0 auto; padding: 40px 20px;">
      <div style="background: #0d0d0d; border: 5px solid #0a0a0a; padding: 40px; border-radius: 5px; text-align: center;">
          <h2 style="color: #a855f7; margin-bottom: 30px; text-transform: uppercase; letter-spacing: 2px;">How to navigate MOSH ZA</h2>
          
          <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 30px; text-align: left; margin-bottom: 40px;">
              <div>
                  <h3 style="color: #fff; margin-bottom: 12px; font-size: 18px;">1. Create an Account</h3>
                  <p style="color: #888; font-size: 14px; line-height: 1.6;">Register as a Buyer to browse and 'purchase' items, or sign up as a Seller to list your own mock products and manage a digital storefront.</p>
              </div>
              <div>
                  <h3 style="color: #fff; margin-bottom: 12px; font-size: 18px;">2. Explore & Cart</h3>
                  <p style="color: #888; font-size: 14px; line-height: 1.6;">Browse the catalog, filter by location or category, and add items to your cart. You can go through the entire simulated checkout process for free.</p>
              </div>
              <div>
                  <h3 style="color: #fff; margin-bottom: 12px; font-size: 18px;">3. Seller Dashboard</h3>
                  <p style="color: #888; font-size: 14px; line-height: 1.6;">Sellers can use their dedicated portal to add new listings, monitor stock levels, and mark incoming simulated orders as 'shipped'.</p>
              </div>
          </div>

          <div style="border-top: 1px solid #1a1a1a; padding-top: 30px; background: rgba(224, 80, 80, 0.05); border-radius: 4px; padding: 20px;">
              <p style="color: #e05050; font-weight: 700; font-size: 15px; text-transform: uppercase; letter-spacing: 2px; margin-bottom: 10px;">
                  Strictly a Mock Platform
              </p>
              <p style="color: #888; font-size: 13px; line-height: 1.6; max-width: 800px; margin: 0 auto;">
                  This website is for <strong>demonstration and educational purposes only</strong>. No real products are available for sale, 
                  no actual payments are processed, and no physical items will be delivered. 
                  Please do not provide real credit card details or sensitive personal information during the simulated checkout.
              </p>
          </div>
      </div>
  </section>

  <section class="product-grid">
    <?php
    $sql = "SELECT products.*, users.username, users.location FROM products JOIN users ON products.seller_id = users.id ORDER BY created_at DESC LIMIT 5";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) > 0) {
      while ($row = mysqli_fetch_assoc($result)) {
        echo '
            <a href="product-detail.php?id=' . $row["id"] . '" class="product-card">
                <div class="photo-container">
                    <img src="uploads/' . htmlspecialchars($row["image"]) . '" alt="' . htmlspecialchars($row["name"]) . '">
                </div>
                <div class="info-container">
                <div class="card-meta">
                    <span class="product-category">' . htmlspecialchars($row["category"]) . '</span>
                    <div class="seller-info">
                        <span class="product-seller">@' . htmlspecialchars($row["username"]) . '</span>
                        <span class="card-location">' . htmlspecialchars($row["location"]) . '</span>
                    </div>
                </div>
                <h2>' . htmlspecialchars($row["name"]) . '</h2>
                <p class="card-description">' . htmlspecialchars($row["description"]) . '</p>
                </div>
                <div class="card-price-bar">
                    <span class="price-currency">R</span>
                    <span class="price-amount">' . $row["price"] . '</span>
                </div>
            </a>
            ';
      }
    } else {
      echo "<p>No products are currently listed.</p>";
    }
    ?>
  </section>

  <footer>
    <div class="footer">
      <p>© 2026 MOSH</p>
    </div>
  </footer>

  <script>
    document.getElementById('nav-toggle').addEventListener('click', function() {
      document.getElementById('nav-links').classList.toggle('active');
    });
  </script>
</body>

</html>