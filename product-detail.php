<?php require_once 'db.php';
session_start();

$id = $_GET['id'] ?? null;

if (!$id) {
    header("Location: index.php");
    exit();
}

$stmt = $conn->prepare("SELECT products.*, users.username FROM products JOIN users ON products.seller_id = users.id WHERE products.id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();
$stmt->close();

if (!$product) {
    header("Location: index.php");
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
            <?php if (!isset($_SESSION['username'])): ?>
                <a href="login.php" class="nav-btn">Login</a>
                <div class="divider"></div>
                <a href="register.php" class="nav-btn primary">Register</a>
            <?php else: ?>
                <a href="products.php" class="nav-btn">Browse</a>
                <div class="divider"></div>
                <a href="cart.php" class="nav-btn">Cart</a>
                <div class="divider"></div>
                <a href="dashboard.php" class="nav-btn">Profile</a>
                <div class="divider"></div>
                <a href="logout.php" class="nav-btn">Logout</a>
            <?php endif; ?>
        </div>
    </nav>


    <a href="javascript:history.back()" class="back-link">Back</a>

    <section class="product-detail">
        <div class="product-img">
            <img src="uploads/<?= $product['image'] ?>" alt="<?= $product['name'] ?>">
        </div>

        <div class="product-text">
            <div class="product-header">
                <h1><?= htmlspecialchars($product['name']) ?></h1>
                <a href="profile.php?username=<?= urlencode($product['username']) ?>"
                    class="product-seller-link">
                    Seller: @<?= htmlspecialchars($product['username']) ?>
                </a>
            </div>
            <span class="product-category"><?= $product['category'] ?></span>
            <?php if (!empty($product['location'])): ?>
                <p class="product-location"><?= htmlspecialchars($product['location']) ?></p>
            <?php endif; ?>
            <p class="product-description"><?= htmlspecialchars($product['description']) ?></p>
            <div class="product-price-stock">
                <div class="product-price-block">
                    <span class="price-currency">R</span>
                    <span class="price-amount"><?= $product['price'] ?></span>
                </div>
                <div>
                    <?php if ($product['stock'] <= 0): ?>
                        <span class="stock-none">Out of stock</span>
                    <?php elseif ($product['stock'] <= 3): ?>
                        <span class="stock-low">Only <?= $product['stock'] ?> left!</span>
                    <?php else: ?>
                        <span class="stock-ok"><?= $product['stock'] ?> in stock</span>
                    <?php endif; ?>
                </div>
            </div>
            <form method="POST" action="cart.php" class="cart-form">
                <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                <div class="quantity-row">
                    <label>Quantity</label>
                    <input type="number" name="quantity" value="1" min="1" max="<?= $product['stock'] ?>" <?= $product['stock'] <= 0 ? 'disabled' : '' ?>>
                </div>
                <button type="submit" name="add_to_cart" class="checkout-btn"
                    <?= $product['stock'] <= 0 ? 'disabled' : '' ?>>
                    <?= $product['stock'] <= 0 ? 'Out of Stock' : 'Add to Cart' ?>
                </button>
            </form>

        </div>
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