<?php
session_start();
$servername = "localhost";
$username   = "root";
$password   = "";
$database   = "auction";
$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $conn->query("DELETE FROM joined_bidders WHERE id=$id");
}

header("Location: ../admin/admindashboard.php");
exit();
