<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$order_id = $_GET['order_id'];
$sql = "SELECT * FROM orders WHERE id = '$order_id' AND buyer_id = '" . $_SESSION['user_id'] . "'";
$result = mysqli_query($conn, $sql);
$order = mysqli_fetch_assoc($result);
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
            <a href="index.php" class="logo"><img src="logo.png" alt="MOSH"><span>ZA</span></a>
        </div>
        <button class="nav-toggle" id="nav-toggle" aria-label="Toggle navigation">
            <span class="bar"></span>
            <span class="bar"></span>
            <span class="bar"></span>
        </button>
        <div class="nav-links" id="nav-links">
            <a href="products.php" class="nav-btn">Browse</a>
            <div class="divider"></div>
            <a href="dashboard.php" class="nav-btn">Profile</a>
            <div class="divider"></div>
            <a href="logout.php" class="nav-btn">Logout</a>
        </div>
    </nav>

    <div class="confirmation-wrapper">
        <h1>Order Confirmed!</h1>
        <p>Order <strong>#<?= $order_id ?></strong></p>
        <p>Delivering to: <strong><?= $order['address'] ?></strong></p>
        <p>Total: <strong>R <?= $order['total_amount'] ?></strong></p>
        <div class="confirmation-status"><?= $order['status'] ?></div>
        <div class="confirmation-hold">
            Your payment is on hold until the seller confirms delivery.
        </div>
        <div class="confirmation-actions">
            <a href="dashboard.php" class="btn-1">View your orders</a>
            <a href="products.php" class="btn-1">Keep browsing</a>
        </div>
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