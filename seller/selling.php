<?php
session_start();

// Check if seller is logged in
if (!isset($_SESSION['seller_id'])) {
    header("Location: html/sellogin.html");
    exit();
}

// Database connection
$servername = "localhost";
$username   = "root";
$password   = "";
$dbname     = "auction";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $seller_id   = $_SESSION['seller_id'];
    $pname       = $conn->real_escape_string($_POST['pname']);
    $description = $conn->real_escape_string($_POST['description']);
    $rate        = $conn->real_escape_string($_POST['rate']);
    $date        = $conn->real_escape_string($_POST['date']);
    $t_from      = $conn->real_escape_string($_POST['t_from']);
    $t_to        = $conn->real_escape_string($_POST['t_to']);
    $category    = $conn->real_escape_string($_POST['category']);

    // File upload directory
    $uploadDir = "uploads/";
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // Upload images and save only filename in DB
    $image1 = $image2 = $image3 = null;

    if (!empty($_FILES['image1']['name'])) {
        $image1 = time() . "_1_" . basename($_FILES['image1']['name']);
        move_uploaded_file($_FILES['image1']['tmp_name'], $uploadDir . $image1);
    }
    if (!empty($_FILES['image2']['name'])) {
        $image2 = time() . "_2_" . basename($_FILES['image2']['name']);
        move_uploaded_file($_FILES['image2']['tmp_name'], $uploadDir . $image2);
    }
    if (!empty($_FILES['image3']['name'])) {
        $image3 = time() . "_3_" . basename($_FILES['image3']['name']);
        move_uploaded_file($_FILES['image3']['tmp_name'], $uploadDir . $image3);
    }

    // Insert into database
    $sql = "INSERT INTO products (seller_id, p_name, description, rate, date, t_from, t_to, category, image1, image2, image3) 
            VALUES ('$seller_id', '$pname', '$description', '$rate', '$date', '$t_from', '$t_to', '$category', 
                    '$image1', '$image2', '$image3')";

    if ($conn->query($sql) === TRUE) {
        echo "<script>alert('Product added successfully!'); window.location='html/selling.html';</script>";
    } else {
        echo "Error: " . $conn->error;
    }
}

$conn->close();
?>
