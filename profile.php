<?php
require_once 'db.php';
session_start();

$username = $_GET['username'] ?? null;

if (!$username) {
    header("Location: index.php");
    exit();
}

$username = mysqli_real_escape_string($conn, $username);

$user_sql = "SELECT * FROM users WHERE username = '$username'";
$user_result = mysqli_query($conn, $user_sql);
$seller = mysqli_fetch_assoc($user_result);

if (!$seller) {
    header("Location: index.php");
    exit();
}

$products_sql = "SELECT * FROM products WHERE seller_id = '{$seller['id']}' ORDER BY id DESC";
$products_result = mysqli_query($conn, $products_sql);

// Handle Report Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_report'])) {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }
    $reporter_id = $_SESSION['user_id'];
    $reported_id = $seller['id'];
    $reason = mysqli_real_escape_string($conn, $_POST['reason']);
    $details = mysqli_real_escape_string($conn, $_POST['details']);

    $report_sql = "INSERT INTO reports (reporter_id, reported_id, reason, details) 
                   VALUES ('$reporter_id', '$reported_id', '$reason', '$details')";
    mysqli_query($conn, $report_sql);
    $report_success = "Report submitted successfully.";
}
?>

<!DOCTYPE html>
<html>

<head>
    <title><?= htmlspecialchars($seller['username']) ?>'s Store - MOSH</title>
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


    <div class="store-header">
        <div class="store-info">
            <p class="modal-eyebrow">Seller Profile</p>
            <h1 class="store-name"><?= htmlspecialchars($seller['username']) ?>'s Store</h1>
            <?php if (!empty($seller['location'])): ?>
                <div class="store-location"><?= htmlspecialchars($seller['location']) ?></div>
            <?php endif; ?>
            <br><br>
            <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] != $seller['id']): ?>
                <button class="btn-report" onclick="document.getElementById('reportModal').style.display='flex'">Report User</button>
            <?php endif; ?>
            <?php if (isset($report_success)): ?>
                <p style="color: #3db87a; margin-top: 10px;"><?= $report_success ?></p>
            <?php endif; ?>
        </div>
        
        <?php
        $total_products = mysqli_num_rows($products_result);
        $total_stock = 0;
        $products_result->data_seek(0);
        while ($product = mysqli_fetch_assoc($products_result)) {
            $total_stock += $product['stock'];
        }
        $products_result->data_seek(0);
        ?>

        <div class="store-stats">
            <div class="stat">
                <div class="stat-number"><?= $total_products ?></div>
                <div class="stat-label">Products</div>
            </div>
            <div class="stat">
                <div class="stat-number"><?= $total_stock ?></div>
                <div class="stat-label">Items in stock</div>
            </div>
        </div>
    </div>


    <div id="reportModal" class="modal" style="display: none;">
        <div class="form-container">
            <button class="close-btn" type="button" onclick="document.getElementById('reportModal').style.display='none'">&times;</button>
            <p class="modal-eyebrow">Report User</p>
            <form method="POST">
                <div class="form-fields">
                    <label>Reason for report</label>
                    <select name="reason" required>
                        <option value="Inappropriate Content">Inappropriate Content</option>
                        <option value="Scam/Fraud">Scam/Fraud</option>
                        <option value="Harassment">Harassment</option>
                        <option value="Other">Other</option>
                    </select>
                    <label>Additional Details</label>
                    <textarea name="details" rows="4" placeholder="Please provide more information..." required></textarea>
                </div>
                <div class="modal-footer">
                    <button type="submit" name="submit_report" class="btn-report">Submit Report</button>
                    <div class="divider"></div>
                    <button type="button" class="btn-cancel" onclick="document.getElementById('reportModal').style.display='none'">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <section class="product-grid">
        <?php if (mysqli_num_rows($products_result) > 0): ?>
            <?php while ($product = mysqli_fetch_assoc($products_result)): ?>
                <a href="product-detail.php?id=<?= $product['id'] ?>" class="product-card">
                    <div class="photo-container">
                        <img src="uploads/<?= $product['image'] ?>" alt="<?= $product['name'] ?>">
                    </div>
                    <div class="info-container">
                        <div class="card-meta">
                            <span class="product-category"><?= $product["category"] ?></span>
                            <span><?= $product["stock"] ?> left</span>
                        </div>
                        <h2><?= htmlspecialchars($product['name']) ?></h2>
                        <p class="card-description"><?= htmlspecialchars($product['description']) ?></p>
                    </div>
                    <div class="card-price-bar">
                        <span class="price-currency">R</span>
                        <span class="price-amount"><?= $product['price'] ?></span>
                    </div>
                </a>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="no-products">
                <p>This seller hasn't listed any products yet.</p>
                <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $seller['id']): ?>
                    <p><a href="dashboard.php">Click here</a> to add your first product!</p>
                <?php endif; ?>
            </div>
        <?php endif; ?>
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

        function closeForm() {
            document.getElementById("reportModal").style.display = "none";
        }
    </script>

</body>

</html>