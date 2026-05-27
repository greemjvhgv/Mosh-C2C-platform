<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$buyer_id = $_SESSION['user_id'];

$full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
$st_address = mysqli_real_escape_string($conn, $_POST['address']);
$city = mysqli_real_escape_string($conn, $_POST['city']);
$zip = mysqli_real_escape_string($conn, $_POST['postal_code']);
$phone = mysqli_real_escape_string($conn, $_POST['phone']);
$address  = "$full_name, $st_address, $city, $zip (Tel: $phone)";

$total = 0;
foreach ($_SESSION['cart'] as $product_id => $quantity) {
    $sql = "SELECT price FROM products WHERE id = '$product_id'";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);
    $total += $row['price'] * $quantity;
}

$sql = "INSERT INTO orders (buyer_id, total_amount, address, status) 
        VALUES ('$buyer_id', '$total', '$address', 'pending')";
mysqli_query($conn, $sql);
$order_id = mysqli_insert_id($conn);

foreach ($_SESSION['cart'] as $product_id => $quantity) {
    $sql = "SELECT price FROM products WHERE id = '$product_id'";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);
    
    $sql = "INSERT INTO order_items (order_id, product_id, quantity, price) 
            VALUES ('$order_id', '$product_id', '$quantity', '" . $row['price'] . "')";
    mysqli_query($conn, $sql);

    // reduce stock
    $sql = "UPDATE products SET stock = stock - $quantity WHERE id = '$product_id'";
    mysqli_query($conn, $sql);
}

$_SESSION['cart'] = [];

header("Location: order-confirmation.php?order_id=" . $order_id);
exit();
?>