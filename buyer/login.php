<?php
session_start();

// Database connection
$servername = "localhost";
$username   = "root";    // your DB username
$password   = "";        // your DB password
$dbname     = "auction"; // your DB name

$conn = new mysqli($servername, $username, $password, $dbname);

// Check DB connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Process login form
if (isset($_POST['submit'])) {
    $email    = $conn->real_escape_string($_POST['username']); 
    $password = $_POST['password'];

  if($email=='admin@gmail.com'&& $password=='admin123')
    {
        header("Location: admin/admindashboard.php");
    }

    // Fetch user from database
    $sql = "SELECT * FROM users WHERE email=? LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();

        // Verify password
        if (password_verify($password, $row['password'])) {
            // ✅ Store consistent session variables
            $_SESSION['id']    = $row['id'];
            $_SESSION['email'] = $row['email'];
            $_SESSION['name']  = $row['fname'] . " " . $row['lname'];

            // Redirect to dashboard (buying.php)
            header("Location: buyer/buying.php");
            exit();
        } else {
            echo "<script>alert('❌ Invalid password!'); window.history.back();</script>";
        }
    } else {
        echo "<script>alert('❌ No account found with this email!'); window.history.back();</script>";
    }
}
$conn->close();
?>
