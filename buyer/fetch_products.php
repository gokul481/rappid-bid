<?php
session_start();

// Database connection
$servername = "localhost";
$username   = "root";
$password   = "";
$database   = "auction";
$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// Fetch all products
$sql = "SELECT * FROM products WHERE approval_status ='approved'ORDER BY id DESC";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $id       = htmlspecialchars($row['id']);
        $name     = htmlspecialchars($row['p_name']);
        $category = htmlspecialchars($row['category']);
        $desc     = htmlspecialchars($row['description']);
        $rate     = htmlspecialchars($row['rate']);
        $t_from   = htmlspecialchars($row['t_from']);
        $t_to     = htmlspecialchars($row['t_to']);
        $date     = htmlspecialchars($row['date']);
        $image    = !empty($row['image1']) ? "../uploads/" . $row['image1'] : "no_image.png";

        echo "<tr data-category='{$category}'>
                <td>{$id}</td>
                <td><img src='{$image}' width='60'></td>
                <td>{$category}</td>
                <td>{$name}</td>
                <td>{$desc}</td>
                <td>{$rate}</td>
                <td>{$t_from} - {$t_to}</td>
                <td>{$date}</td>
                <td><a href='../seller/product.php?id={$id}'>View</a></td>
              </tr>";
    }
} else {
    echo "<tr><td colspan='9'>No products found.</td></tr>";
}
?>
