<?php
$host     = "localhost";
$port     = 8889;
$dbname   = "mosh_db";
$username = "root";
$password = "root";

$conn = new mysqli($host, $username, $password, $dbname, $port);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>