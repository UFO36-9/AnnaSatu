<?php
$conn = new mysqli("localhost:3307", "root", "", "foodmandu_old");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>

