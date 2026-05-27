<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit();
}

$user_id = $_SESSION['user_id']; // ID of the logged-in user

$total_products = 0;
$total_stock = 0;

if ($_SESSION['role'] === 'seller') {
  // Fetch statistics for the dashboard header
  $stats_res = mysqli_query($conn, "SELECT COUNT(*) as total_items, SUM(stock) as total_stock FROM products WHERE seller_id = '$user_id'");
  $stats = mysqli_fetch_assoc($stats_res);
  $total_products = $stats['total_items'] ?? 0;
  $total_stock = $stats['total_stock'] ?? 0;
}

// Handle Role Upgrade
if (isset($_POST['become_seller'])) {
  mysqli_query($conn, "UPDATE users SET role = 'seller' WHERE id = '$user_id'");
  $_SESSION['role'] = 'seller';
  header("Location: dashboard.php");
  exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_SESSION['role'] === 'seller') {
  $name        = mysqli_real_escape_string($conn, $_POST['name']); //mysqli_real_escape_string() makes sure quotes or special characters in the input dont break the SQL query
  $description = mysqli_real_escape_string($conn, $_POST['description']);
  $price       = $_POST['price'];
  $category    = $_POST['category'];
  $stock       = $_POST['stock'];
  $product_id  = isset($_POST['product_id']) ? $_POST['product_id'] : null;
  $image_name  = "";
  $img_sql     = "";

  if (!empty($_FILES['image']['name'])) {
    $file = $_FILES['image'];
    $allowed = ['jpg', 'jpeg', 'png', 'gif'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if (in_array($ext, $allowed) && $file['error'] === 0) {
      // Check if the file is actually an image
      $check = getimagesize($file['tmp_name']);
      if ($check !== false) {
        $image_name = bin2hex(random_bytes(8)) . "_" . time() . "." . $ext;
        if (move_uploaded_file($file['tmp_name'], 'uploads/' . $image_name)) {
          $img_sql = ", image = '$image_name'";
        }
      }
    }
  }

  if ($product_id) {
    $sql = "UPDATE products SET name='$name', description='$description', price='$price', category='$category', stock='$stock' $img_sql 
                WHERE id='$product_id' AND seller_id='$user_id'";
  } else {
    $sql = "INSERT INTO products (seller_id, name, description, price, category, image, stock) 
                VALUES ('$user_id', '$name', '$description', '$price', '$category', '$image_name', '$stock')";
  }

  mysqli_query($conn, $sql);
  header("Location: dashboard.php");
  exit();
}

$edit_data = null;
if (isset($_GET['action']) && $_GET['action'] == 'edit') {
  $id = $_GET['id'];
  $res = mysqli_query($conn, "SELECT * FROM products WHERE id='$id' AND seller_id='$user_id'");
  $edit_data = mysqli_fetch_assoc($res);
}

if (isset($_GET['action']) && $_GET['action'] == 'ship') {
  $order_id = mysqli_real_escape_string($conn, $_GET['id']);
  //Check if this order actually contains a product belonging to this seller
  $check_query = "SELECT 1 FROM order_items oi JOIN products p ON oi.product_id = p.id 
                  WHERE oi.order_id = '$order_id' AND p.seller_id = '$user_id'";
  $check_res = mysqli_query($conn, $check_query);
  if (mysqli_num_rows($check_res) > 0) {
    mysqli_query($conn, "UPDATE orders SET status = 'shipped' WHERE id = '$order_id'");
  }
  header("Location: dashboard.php");
  exit();
}

if (isset($_GET['action']) && $_GET['action'] == 'cancel_order') {
  $order_id = mysqli_real_escape_string($conn, $_GET['id']);

  // Security check: is user the buyer OR is user the seller of this order?
  $check_sql = "SELECT o.status, o.buyer_id, (SELECT 1 FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = o.id AND p.seller_id = '$user_id' LIMIT 1) as is_seller 
               FROM orders o WHERE o.id = '$order_id'";
  $check_res = mysqli_query($conn, $check_sql);
  $order_data = mysqli_fetch_assoc($check_res);

  if ($order_data && $order_data['status'] == 'pending' && ($order_data['buyer_id'] == $user_id || $order_data['is_seller'])) {
    // 1. Return stock to products
    $items_res = mysqli_query($conn, "SELECT product_id, quantity FROM order_items WHERE order_id = '$order_id'");
    while ($item = mysqli_fetch_assoc($items_res)) {
      mysqli_query($conn, "UPDATE products SET stock = stock + {$item['quantity']} WHERE id = '{$item['product_id']}'");
    }
    // 2. Update order status
    mysqli_query($conn, "UPDATE orders SET status = 'cancelled' WHERE id = '$order_id'");
  }
  header("Location: dashboard.php");
  exit();
}

if (isset($_GET['action']) && $_GET['action'] == 'delete') {
  $id = $_GET['id'];
  mysqli_query($conn, "DELETE FROM products WHERE id='$id' AND seller_id='$user_id'");
  header("Location: dashboard.php");
  exit();
}
?>

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
      <a href="products.php" class="logo"><img src="logo.png" alt="MOSH"><span>ZA</span></a>
    </div>
    <button class="nav-toggle" id="nav-toggle" aria-label="Toggle navigation">
      <span class="bar"></span>
      <span class="bar"></span>
      <span class="bar"></span>
    </button>
    <div class="nav-links" id="nav-links">
      <?php if ($_SESSION['role'] === 'admin'): ?>
        <a href="admin.php" class="nav-btn">Admin</a>
      <?php else: ?>
        <a href="products.php" class="nav-btn">Browse</a>
        <div class="divider"></div>
        <a href="cart.php" class="nav-btn">Cart</a>
        <div class="divider"></div>
        <a href="logout.php" class="nav-btn">Logout</a>
      <?php endif; ?>
    </div>
  </nav>

  <div class="store-header dashboard-store-header" style="<?= $_SESSION['role'] === 'user' ? 'border-bottom: none;' : '' ?>">
    <div class="dashboard-store-info-wrapper">
      <p class="modal-eyebrow dashboard-eyebrow">User Portal</p>
      <h1 class="store-name dashboard-store-name"><?= htmlspecialchars($_SESSION['username']) ?></h1>
      <div class="store-location"><?= htmlspecialchars($_SESSION['location']) ?></div>
    </div>
    <?php if ($_SESSION['role'] === 'seller'): ?>
      <div class="store-stats dashboard-store-stats">
        <div class="stat">
          <div class="stat-number"><?= $total_products ?></div>
          <div class="stat-label">Active Listings</div>
        </div>
        <div class="stat">
          <div class="stat-number"><?= $total_stock ?></div>
          <div class="stat-label">Stock Inventory</div>
        </div>
      </div>
    <?php endif; ?>
  </div>

  <?php if ($_SESSION['role'] === 'buyer'): ?>
    <div class="dashboard-content-section" style="text-align: center; background: #0a0a0a; padding: 40px; border-radius: 8px; border: 1px solid #a855f733;">
      <h2 class="hero-title" style="margin-bottom: 10px;">Start Selling Today</h2>
      <p style="color: #888; margin-bottom: 20px;">Upgrade your account to list products and reach customers across South Africa.</p>
      <form method="POST">
        <button type="submit" name="become_seller" class="btn-submit" style="max-width: 200px; margin: 0 auto; display: block;">
          Become a Seller
        </button>
      </form>
    </div>
  <?php endif; ?>

  <?php if ($_SESSION['role'] === 'seller'): ?>
    <div class="dashboard-content-section">
      <h2 class="hero-title dashboard-section-title">Incoming Orders</h2>
      <?php
      $orders_sql = "SELECT o.*, u.username as buyer_name, p.name as product_name, oi.quantity 
                   FROM orders o 
                   JOIN users u ON o.buyer_id = u.id 
                   JOIN order_items oi ON o.id = oi.order_id 
                   JOIN products p ON oi.product_id = p.id 
                   WHERE p.seller_id = '$user_id' AND o.status = 'pending'
                   ORDER BY o.id DESC";
      $orders_res = mysqli_query($conn, $orders_sql);

      if (mysqli_num_rows($orders_res) > 0): ?>
        <div class="scroll-view dashboard-scroll-card">
          <?php while ($order = mysqli_fetch_assoc($orders_res)): ?>
            <div class="dashboard-order-item">
              <div>
                <p class="dashboard-order-meta">Order #<?= $order['id'] ?> - Item: <?= htmlspecialchars($order['product_name']) ?> (x<?= $order['quantity'] ?>)</p>
                <p class="dashboard-order-product">Buyer: <strong>@<?= htmlspecialchars($order['buyer_name']) ?></strong></p>
                <p class="dashboard-order-address">Ship to: <?= htmlspecialchars($order['address']) ?></p>
              </div>
              <div style="display: flex; gap: 10px;">
                <a href="dashboard.php?action=ship&id=<?= $order['id'] ?>" class="btn-submit dashboard-ship-btn"
                  onclick="return confirm('Mark this order as shipped?')">Ship Item</a>
                <a href="dashboard.php?action=cancel_order&id=<?= $order['id'] ?>" class="btn-cancel dashboard-cancel-btn"
                  onclick="return confirm('Are you sure you want to cancel this incoming order?')">Cancel</a>
              </div>
            </div>
          <?php endwhile; ?>
        </div>
      <?php else: ?>
        <div class="cart-empty" style="padding: 20px;">
          <p class="dashboard-empty-state">No pending orders to fulfill.</p>
        </div>
      <?php endif; ?>
    </div>
  <?php endif; ?>

  <div class="dashboard-content-section">
    <h2 class="hero-title dashboard-section-title">My Purchases</h2>
    <?php
    $purchases_sql = "SELECT o.id, o.total_amount, o.status, p.name as product_name, oi.quantity 
                      FROM orders o 
                      JOIN order_items oi ON o.id = oi.order_id 
                      JOIN products p ON oi.product_id = p.id 
                      WHERE o.buyer_id = '$user_id'
                      ORDER BY o.id DESC";
    $purchases_res = mysqli_query($conn, $purchases_sql);

    if (mysqli_num_rows($purchases_res) > 0): ?>
      <div class="scroll-view dashboard-scroll-card">
        <?php while ($purchase = mysqli_fetch_assoc($purchases_res)): ?>
          <div class="dashboard-order-item">
            <div>
              <p class="dashboard-order-meta">Order #<?= $purchase['id'] ?></p>
              <p class="dashboard-order-product">Item: <strong><?= htmlspecialchars($purchase['product_name']) ?></strong></p>
              <p class="dashboard-order-address">Paid: R <?= $purchase['total_amount'] ?></p>
            </div>
            <div style="display: flex; flex-direction: column; align-items: flex-end; gap: 8px;">
              <span class="product-category <?= $purchase['status'] == 'shipped' ? 'shipped' : '' ?>">
                <?= strtoupper($purchase['status']) ?>
              </span>
              <?php if ($purchase['status'] == 'pending'): ?>
                <a href="dashboard.php?action=cancel_order&id=<?= $purchase['id'] ?>" class="dashboard-cancel-btn"
                  style="font-size: 11px; padding: 4px 8px;" onclick="return confirm('Cancel your purchase?')">Cancel Order</a>
              <?php endif; ?>
            </div>
          </div>
        <?php endwhile; ?>
      </div>
    <?php else: ?>
      <div class="cart-empty" style="padding: 20px;">
        <p class="dashboard-empty-state">You haven't made any purchases yet.</p>
        <br>
        <a href="products.php" class="browse-link">Start Shopping</a>
      </div>
    <?php endif; ?>
  </div>

  <?php if ($_SESSION['role'] === 'seller'): ?>
    <div class="hero-links">
      <label class="hero-title">Inventory Management</label>
      <button class="add-product-btn" onclick="document.getElementById('productModal').style.display='flex'"> + Add Product</button>
      <a href="products.php" class="browse-link">Browse Products</a>
    </div>
  <?php endif; ?>


  <?php if ($edit_data): ?>
    <div id="edit-modal" class="modal" style="display: flex;">
      <div class="form-container">
        <a href="dashboard.php" class="close-btn">&times;</a>
        <p class="modal-eyebrow">Edit listing</p>

        <form method="POST" action="dashboard.php" enctype="multipart/form-data">
          <input type="hidden" name="product_id" value="<?= $edit_data['id'] ?>">

          <div class="form-fields">
            <label>Product name</label>
            <input type="text" name="name" value="<?= htmlspecialchars($edit_data['name']) ?>" required>

            <label>Description</label>
            <textarea name="description" required><?= htmlspecialchars($edit_data['description']) ?></textarea>

            <div class="form-grid">
              <input type="number" name="price" step="0.01" value="<?= $edit_data['price'] ?>" required>
              <input type="number" name="stock" value="<?= $edit_data['stock'] ?>" required>
            </div>

            <label>Category</label>
            <select name="category">
              <option value="electronics" <?= $edit_data['category'] == 'electronics' ? 'selected' : '' ?>>Electronics</option>
              <option value="fashion" <?= $edit_data['category'] == 'fashion' ? 'selected' : '' ?>>Fashion</option>
              <option value="handmade" <?= $edit_data['category'] == 'handmade' ? 'selected' : '' ?>>Handmade</option>
              <option value="home" <?= $edit_data['category'] == 'home' ? 'selected' : '' ?>>Home & Garden</option>
              <option value="books" <?= $edit_data['category'] == 'books' ? 'selected' : '' ?>>Books & Media</option>
              <option value="sports" <?= $edit_data['category'] == 'sports' ? 'selected' : '' ?>>Sports</option>
              <option value="food" <?= $edit_data['category'] == 'food' ? 'selected' : '' ?>>Food & Baking</option>
            </select>

            <label>Image (Leave blank to keep current)</label>
            <input type="file" name="image">
          </div>

          <div class="modal-footer">
            <button type="submit" class="btn-submit">Update Product</button>
          </div>
        </form>
      </div>
    </div>
  <?php endif; ?>


  <div id="productModal" class="modal" style="display: none;">
    <div class="form-container">
      <button class="close-btn" type="button" onclick="closeForm()">&times;</button>
      <div style="margin-bottom: 1.5rem;">
        <p class="modal-eyebrow">New listing</p>
      </div>
      <form method="POST" action="dashboard.php" enctype="multipart/form-data">
        <div class="form-fields">
          <div>
            <label>Product name</label>
            <input type="text" name="name" placeholder="e.g. Handmade silver ring" required>
          </div>
          <div>
            <label>Description</label>
            <textarea name="description" rows="3" placeholder="Tell buyers what makes it special..." required></textarea>
          </div>
          <div class="form-grid">
            <div>
              <label>Price (R)</label>
              <input type="number" name="price" step="0.01" min="0" placeholder="0.00" required>
            </div>
            <div>
              <label>Stock</label>
              <input type="number" name="stock" min="1" value="1" required>
            </div>
          </div>
          <div>
            <label>Category</label>
            <select name="category" required>
              <option value="electronics">Electronics</option>
              <option value="fashion">Fashion</option>
              <option value="handmade">Handmade</option>
              <option value="home">Home & Garden</option>
              <option value="books">Books & Media</option>
              <option value="sports">Sports</option>
              <option value="food">Food & Baking</option>
            </select>
          </div>
          <div>
            <label>Image</label>
            <input type="file" name="image" accept="image/*">
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn-submit">List product</button>
          <div class="divider"></div>
          <button type="button" class="btn-cancel" onclick="closeForm()">Cancel</button>
        </div>
      </form>
    </div>
  </div>


  <section class="product-grid">
    <?php if ($_SESSION['role'] === 'seller'): ?>
      <?php
      $sql = "SELECT * FROM products WHERE seller_id = '$user_id'";
      $result = mysqli_query($conn, $sql);

      if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
          echo '
            <div class="product-card">
                <div class="photo-container">
                    <img src="uploads/' . $row["image"] . '" alt="' . $row["name"] . '">
                </div>
                <div class="info-container">
                    <div class="card-meta">
                        <span class="product-category">' . $row["category"] . '</span>
                        <span class="stock-ok">' . $row["stock"] . ' left</span>
                    </div>
                    <h2>' . $row["name"] . '</h2>
                    <p>' . $row["description"] . '</p>
                </div>
                <div class="card-price-bar">
                    <span class="price-currency">R</span>
                    <span class="price-amount">' . $row["price"] . '</span>
                </div>
                <div class="card-actions">
                    <a href="dashboard.php?action=edit&id=' . $row["id"] . '" class="card-edit-btn">Edit</a>
                    <a href="dashboard.php?action=delete&id=' . $row["id"] . '" class="card-delete-btn" 
                      onclick="return confirm(\'Are you sure?\')">Delete</a>
                </div>
            </div>
            ';
        }
      } else {
        echo "<p>You have no listings yet</p>";
      }
      ?>
    <?php endif; ?>
  </section>


  <footer>
    <div class="footer">
      <p>© 2026 MOSH</p>
    </div>
  </footer>

</body>
<script>
  document.getElementById('nav-toggle').addEventListener('click', function() {
    document.getElementById('nav-links').classList.toggle('active');
  });

  function closeForm() {
    document.getElementById("productModal").style.display = "none";
  }
</script>

</html>