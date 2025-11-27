<?php
session_start();
$servername = "localhost";
$username   = "root";
$password   = "";
$database   = "auction";
$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$id = intval($_GET['id']);
$category = $conn->query("SELECT * FROM joined_bidders WHERE id=$id")->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $newName = $conn->real_escape_string($_POST['bidder_name']);
    $conn->query("UPDATE joined_bidders SET bidder_name='$newName' WHERE id=$id");
    header("Location: ../admin/admindashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Category</title>
</head>
<body>
    <h2>Edit Category</h2>
    <form method="POST">
        <label>Category Name:</label>
        <input type="text" name="bidder_name" value="<?= $category['bidder_name'] ?>" required>
        <button type="submit">Update</button>
    </form>
    <a href="../admin/admindashboard.php">Cancel</a>
</body>
</html>
