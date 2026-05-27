<?php require_once 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (isset($_POST['add_to_cart'])) {
    $product_id = $_POST['product_id'];
    $quantity   = $_POST['quantity'];

    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    if (isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id] += $quantity;
    } else {
        $_SESSION['cart'][$product_id] = $quantity;
    }
}

if (isset($_GET['action']) && $_GET['action'] == 'remove') {
    unset($_SESSION['cart'][$_GET['id']]);
    header("Location: cart.php");
    exit();
}

?>

<!DOCTYPE html>
<html>

<head>
  <title>MOSH Cart</title>
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
            <?php if (!isset($_SESSION['username'])) : ?>
                <a href="login.php" class="nav-btn">Login</a>
                <a href="register.php" class="nav-btn primary">Register</a>
            <?php else: ?>
                <a href="products.php" class="nav-btn">Browse</a>
                <div class="divider"></div>
                <a href="dashboard.php" class="nav-btn">Profile</a>
                <div class="divider"></div>
                <a href="logout.php" class="nav-btn">Logout</a>
            <?php endif; ?>
        </div>
    </nav>

     <div class="store-header">
        <div class="store-info">
            <p class="modal-eyebrow">CART</p>
        </div>
    </div>

    <div class="cart-checkout-wrapper">
        <section class="cart-section">
            <section class="product-detail">
                <div>
                    <?php
                    if (empty($_SESSION['cart'])) {
                        echo '
                        <div class="cart-empty">
                        <p>Your cart is empty.</p><br>
                        <a href="products.php">Browse listings</a>
                        </div>';
                    } else {
                        $total = 0;
                        foreach ($_SESSION['cart'] as $product_id => $quantity) {
                            $sql = "SELECT products.*, users.username FROM products 
                        JOIN users ON products.seller_id = users.id 
                        WHERE products.id = '$product_id'";
                            $result = mysqli_query($conn, $sql);
                            $row = mysqli_fetch_assoc($result);

                            if ($row) {
                                $price = $row["price"];
                                $subtotal = $price * $quantity;
                                $total += $subtotal;
                                echo 
                                '<div class="cart-item">
                                <div class="photo-container">
                                    <img src="uploads/' . $row["image"] . '" alt="' . $row["name"] . '">
                                </div>
                                <div class="info-container">
                                    <div class="card-meta">
                                        <span class="product-category">' . $row["category"] . '</span>
                                        <span class="product-seller">@' . htmlspecialchars($row["username"]) . '</span>
                                        <span class="card-location">' . htmlspecialchars($row["location"]) . '</span>
                                    </div>
                                    <h2>' . htmlspecialchars($row["name"]) . '</h2>
                                    <p>Quantity: ' . $quantity . '</p>
                                    <p>' . htmlspecialchars($row["description"]) . '</p>
                                </div>
                                <div class="cart-item-actions">
                                    <div class="cart-item-price">
                                    <span class="price-currency">R</span>
                                    <span class="price-amount">' . $row["price"] . '</span>
                                    </div>
                                    <p class="cart-item-subtotal">Subtotal: R ' . ($row["price"] * $quantity) . '</p>
                                    <a href="cart.php?action=remove&id=' . $product_id . '" class="cart-remove-btn">Remove</a>
                                </div>
                            </div>';
                            }
                        }
                    }
                    ?>
                </div>
            </section>
        </section>


        <section class="checkout-section">
            <div>
                <form action="process_checkout.php" method="POST" class="checkout-form">
                    <h2>Delivery Address</h2>
                    <div class="form-group">
                        <label for="full_name">Full Name</label>
                        <input type="text" id="full_name" name="full_name" required>
                    </div>
                    <div class="form-group">
                        <label for="address">Street Address</label>
                        <input type="text" id="address" name="address" required>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="city">City</label>
                            <input type="text" id="city" name="city" required>
                        </div>
                        <div class="form-group">
                            <label for="postal_code">Postal Code</label>
                            <input type="text" id="postal_code" name="postal_code" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="tel" id="phone" name="phone" required>
                    </div>
                    <h2>Payment Details</h2>
                    <div class="form-group">
                        <label for="card_name">Name on Card</label>
                        <input type="text" id="card_name" name="card_name" required>
                    </div>
                    <div class="form-group">
                        <label for="card_number">Card Number</label>
                        <input type="text" id="card_number" name="card_number" placeholder="1234 5678 9012 3456" maxlength="19" required>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="expiry">Expiry Date</label>
                            <input type="text" id="expiry" name="expiry" placeholder="MM/YY" maxlength="5" required>
                        </div>
                        <div class="form-group">
                            <label for="cvv">CVV</label>
                            <input type="text" id="cvv" name="cvv" placeholder="123" maxlength="4" required>
                        </div>
                    </div>
                    <?php echo '<div class="cart-total"><strong>Total: R' . $total . '</strong></div>'; ?>

                    <button type="submit" class="checkout-btn">Place Order</button>
                </form>
            </div>
        </section>
    </div>

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