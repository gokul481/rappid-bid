<?php
session_start();


// Database connection
$servername = "localhost";
$username   = "root";
$password   = "";
$database   = "auction";
$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// Check if product ID is provided
if (!isset($_GET['id'])) {
    die("No auction selected.");
}

$product_id = intval($_GET['id']);

// Update the product status to 'rejected'
$sql = "UPDATE products SET approval_status='rejected' WHERE id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $product_id);

if ($stmt->execute()) {
    echo "<script>alert('Auction rejected successfully!'); window.location='../admin/admindashboard.php';</script>";
} else {
    echo "Error: " . $conn->error;
}

$conn->close();
?>
