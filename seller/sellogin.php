<?php
session_start();

// Database connection
$servername = "localhost";
$username   = "root";        // your DB username
$password   = "";            // your DB password
$dbname     = "auction";     // your DB name

$conn = new mysqli($servername, $username, $password, $dbname);

// Check DB connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Process login form
if (isset($_POST['submit'])) {
    $email    = $conn->real_escape_string($_POST['username']); 
    $password = $_POST['password'];

    if($email=='admin@gmail.com' && $password=='admin123')
    {
        header("Location: ../admin/admindashboard.php");
    }

    // Fetch user from database
    $sql = "SELECT * FROM users WHERE email='$email' LIMIT 1";
    $result = $conn->query($sql);

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();

        // Verify password
        if (password_verify($password, $row['password'])) {
            // Create session variables
            $_SESSION['seller_id']    = $row['id'];
            $_SESSION['seller_email'] = $row['email'];
            $_SESSION['seller_name']  = $row['fname'] . " " . $row['lname'];

            // Redirect to seller dashboard
            header("Location: ../html/selling.html");
            exit();
        } else {
            echo "<script>alert('Invalid password!'); window.history.back();</script>";
        }
    } else {
        echo "<script>alert('No account found with this email!'); window.history.back();</script>";
    }
}

$conn->close();
?>
